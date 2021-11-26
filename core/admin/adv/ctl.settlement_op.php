<?php

/*
 @ctl_name = 运营商结算中心@
*/

class ctl_settlement_op extends adminPage
{

	public function index_action () #act_name = 列表#
	{	
		$type = trim(get2('type'));
		if(!$type)$type='01';
		$this->setData($type,'type');

		$mdl_settlement_log=$this->loadModel('settlement_log');
		$mdl_recharge	= $this->loadModel('recharge');
		$businessSettleableRecord=$mdl_recharge->businessSettleableRecord();

		$inStr_businessSettleableRecord = "'".join("','",$businessSettleableRecord)."'";
		$where_str ="r.payment in ($inStr_businessSettleableRecord) and o.coupon_status='$type'";


		$personalSettleableRecord=$mdl_recharge->personalSettleableRecord();

		$inStr_personalSettleableRecord = "'".join("','",$personalSettleableRecord)."'";
		$where_str1 =" r.payment in ($inStr_personalSettleableRecord) ";

		/**
		 * 所有商家列表
		 */
		$mdl_user = $this->loadModel('user');
		if($this->user['role']==6){
			
		    	$businessList = $mdl_user->getList('id,businessName,displayName,name,password',array('role'=>6,'user_belong_to_agent'=>$_SESSION['admin_user_id'],'settlement_type'=>$type));
			
		}else{
				$businessList = $mdl_user->getList('id,businessName,displayName,name,password',array('role'=>6,'settlement_type'=>$type));
			
		}
	

		foreach ($businessList as $key=>$b) {
			$bid = $b['id'];
			$lastSettlementPoint=$mdl_settlement_log->owner($bid)->lastSettlementPoint();

			/**
			 * 是否有结算点
			 */
			$lastSettlementDate= '2017-07-27';// 由于之前的数据没有详细分录，并入统计会造成数据出错，必须剔除
			if($lastSettlementPoint){
				$lastSettlementDate=$lastSettlementPoint['settle_to'];
			}

			$settlementTo=date('Y-m-d');

			/**
			 * 可结算金额
			 */
			$time1=strtotime($lastSettlementDate);
			$time2=strtotime($settlementTo);

	        $sql ="SELECT sum(r.money) as amount from cc_recharge as r left join cc_order as o on r.orderId = o.orderId left join cc_wj_user_coupon_activity_log as l on o.orderId = l.orderId where r.userId =$bid and o.business_userId =$bid and l.action_id=o.coupon_status and l.gen_date>=$time1 and l.gen_date<=$time2 and $where_str";

	        $data = $mdl_recharge->getListBySql($sql);

	        $settlementBalance= $data[0]['amount'];



	        $sql1 ="SELECT sum(r.money) as amount from cc_recharge as r where r.userId =$bid and r.createTime>=$time1 and r.createTime<=$time2 and $where_str1";

	        $data1 = $mdl_recharge->getListBySql($sql1);

	        $personalSettlementBalance= $data1[0]['amount'];


	        if( $settlementBalance>0){
	        	$businessList[$key]['settlementBalance']=$settlementBalance+$personalSettlementBalance;
	        	$businessList[$key]['lastSettlementDate']=$lastSettlementDate;
	        }else{
	        	unset($businessList[$key]);
	        }
		}

		$this->setData($settlementTo, 'settlementTo');
		$this->setData($businessList, 'businessList');

		$this->setData($this->parseUrl()->set('type'), 'indexUrl');
		$this->setData($this->parseUrl()->set('act','manual_settlement'), 'settleUrl');
		$this->setData($this->parseUrl()->set('act','settlement_log'), 'logUrl');
		$this->setData($this->parseUrl()->set(), 'refreshUrl');
		$this->display();
	}

	public function settlement_log_action () #act_name = 结算记录#
	{
		$mdl_settlement_log	= $this->loadModel('settlement_log');
		$order		= "gen_date desc";
		$pageSql	= $mdl_settlement_log->getListSql(null, $where, $order);
		$pageUrl	= $this->parseUrl()->set( 'page' );
		$pageSize	= 20;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $mdl_settlement_log->getListBySql($page['outSql']);

		$mdl_user = $this->loadModel( 'user' );
		foreach ( $data as $key => $val ) {
			$data[$key]['createUser'] = $mdl_user->getDisplayName( $val['user_id'] );
		}

		$this->setData($data, 'data');
		$this->setData($page['pageStr'], 'pager');
		$this->setData($this->parseUrl()->set( 'act', 'index' ), 'backUrl');
		$this->setData($this->parseUrl()->set(), 'refreshUrl');
		$this->display();
	}

	public function manual_settlement_action () #act_name = 手动触发结算#
    {
        $from    = trim(get2('from'));
        $to      = trim(get2('to'));
        $balance = trim(get2('balance'));
        $businessUserId = trim(get2('bid'));

        $status  = trim(get2('type'));

        if($from==$to)$this->sheader(null,'结算的时段不能小于1天。今日的订单需要明天才能结算');

        $mdl_settlement_log=$this->loadModel('settlement_log');
    
        $mdl_settlement_log
        ->owner($businessUserId)
        ->settleFrom($from)
        ->settleTo($to)
        ->settleOrderStatus($status)
        ->settleAmount($balance)
        ->operationType(mdl_settlement_log::OPERATION_TYPE_MANUAL)
        ->log();    # code...

        $balance = (float)$balance;
        $mdl_recharge = $this->loadModel( 'recharge' );

        /**
        * 如果是c01结算动作， 此次结算涉及到的所有订单记录，都需要从 PENDING  ==> SETTLE
        */
        if($status=='c01'){
            $order_in_str = " orderId in (".$mdl_settlement_log->getOrderList().") ";
            $mdl_recharge->updateByWhere(array('status'=>BalanceProcess::SETTLE), $order_in_str);
        }

        if($balance>0){
            //取现流程
            $orderId = '102'.date( 'YmdHis' ).$this->createRnd(3);
            $msg = "系统结算取现(From:$from To:$to $$balance)";

            $data = array(
                'orderId' => $orderId,
                'userId' => $businessUserId,
                'money' => 0-$balance,
                'payment' => BalanceProcess::TYPE_SYS_SETTLEMENT_WITHDRAW,
                'status' => BalanceProcess::PENDING, 
                'createTime' => time(),
                'createIp' => ip(),
                'coupon_name'=> $msg
            );
            $mdl_recharge->insert( $data );

            $this->sheader($this->parseUrl()->set('ctl','adv/withdraw')->set('act','view')->set('id',$orderId));

        }elseif($balance<0){
            //充值流程
            $orderId = '101'.date( 'YmdHis' ).$this->createRnd(3);

            $msg = "系统结算充值(From:$from To:$to $$balance)";

            $data = array(
                'orderId' => $orderId,
                'userId' => $businessUserId,
                'money' => 0-$balance,
                'payment' => BalanceProcess::TYPE_SYS_SETTLEMENT_RECHARGE,
                'status' => BalanceProcess::INIT, 
                'createTime' => time(),
                'createIp' => ip(),
                'coupon_name'=> $msg
            );

            $mdl_recharge->insert($data);

            $this->sheader($this->parseUrl()->set('ctl','adv/recharge'));
        }else{
            $this->sheader($this->parseUrl()->set());
        }

    }


}

?>