<?php

//会员

class ctl_redbag extends cmsPage
{
	
	function ctl_redbag() {
		parent::cmsPage();

		$act = $GLOBALS['gbl_act'];
		$ignore_list = array('redbag_show');
		if ( !in_array($act, $ignore_list) && !$this->loginUser ) {
			$this->sheader( HTTP_ROOT_WWW.'member/login?returnUrl='.urlencode( $_SERVER['REQUEST_URI'] ) );
		}
	
	}
	

	/**
	 * Create 
	 * @return [type] [description]
	 */
	function redbag_action() {
		
		//获取当前用户的账户余额
		
		$mdl_recharge = $this->loadModel( 'recharge' );
	
		$total_recharge=$mdl_recharge->getBalanceOfUser($this->loginUser['id'] );

		$this->setData($total_recharge,'balance');
	
		if ( is_post() ) {
			
			$min=0.01;//每个人最少能收到0.01元 
			
			// 如果是0 表示普通红包  1 表示随机红包
			$amount_ramdon_or_same = post( 'amount_ramdon_or_same' ) ;
			
			
			$name =  post( 'name' ) ;

			if(strlen($name)==0) $name="2018狗年澳元红包";
			if(strlen($name)>60){
				$this->sheader(HTTP_ROOT_WWW.'redbag/redbag?err_type=2');
			}
			if(strlen($name)<6){
				$this->sheader(HTTP_ROOT_WWW.'redbag/redbag?err_type=1');
			}
			

			$amount_total =  post( 'amount_total' ) ;

			if(strlen($amount_total)==0) $amount_total=1.88;	
			
			if($amount_total<0.10) $this->sheader(HTTP_ROOT_WWW.'redbag/redbag?err_type=6');
			
			if(!is_numeric($amount_total)){
				$this->sheader(HTTP_ROOT_WWW.'redbag/redbag?err_type=3');
			}
				
            if((int)($amount_total)>100000) {
				$this->sheader(HTTP_ROOT_WWW.'redbag/redbag?err_type=4');
			}	
			
			$amount_total  =floor($amount_total*100)/100;
			 
			if($total_recharge<$amount_total) {
				$this->sheader(HTTP_ROOT_WWW.'redbag/redbag?err_type=5');
			}
						
			
			$qty =  post( 'qty' ) ;
			if(strlen($qty)==0)  $qty=10;	
			if(!is_numeric($qty)) $qty=10;	
			if(strlen($qty)==0) $qty=10;		
			if((int)$qty>10000) $qty=8888;	
			

			$couponid=post('couponid');
			if(!$couponid){
				$couponid =0;
			}

			
			$mdl_redbag = $this->loadModel( 'redbag' );

			$mdl_redbag_details = $this->loadModel( 'redbag_details' );
			
            $data=array(
						'name'=>$name,
						'createUserID'=>$this->loginUser['id'],
						'userName'=>$this->loginUser['name'],
						'createtime'=>time(),
						'amount_total'=>$amount_total,
						'qty'=>$qty,
						'amount_ramdon_or_same'=>$amount_ramdon_or_same,
						'status'=>1,
						'couponid'=>$couponid
						);
			
			$insert_error =0;
			$redbag_amount = $amount_total;

			$mdl_redbag->begin();
			$mdl_redbag_details->begin();
			$mdl_recharge->begin();
			
			$new_id=$mdl_redbag->insert( $data );
			if ($new_id>0) {
				
				if ($amount_ramdon_or_same==1) {
				
				//插入随机红包开始
				for($i=1;$i<$qty;$i++){
				  $safe_total=($amount_total-($qty-$i)*$min)/($qty-$i);
				  $money=mt_rand($min*100,$safe_total*100)/100;
				  $amount_total=$amount_total-$money;
				  $arr['res'][$i] = array(
				  'redbag_id'=>$new_id,
				   'amount' =>$money,
				   'redbag_order_id'=>$i
				   );
				 $mdl_redbag_details->insert( $arr['res'][$i]);
				  
				  if($mdl_redbag_details->error()){	
                  		  $insert_error=1;
						  break;
					  }
				}

				$arr['res'][$qty] = array(
				 'redbag_id'=>$new_id,
				   'amount' =>$amount_total,
				   'redbag_order_id'=>$qty
				
				);
				$mdl_redbag_details->insert( $arr['res'][$qty]);
				  
			    if($mdl_redbag_details->error()){	
              		$insert_error=1;
				}
				  //插入随机红包结束
				}else{

				// 插入普通红包开始
				$money=floor(($amount_total/$qty)*100)/100;
			    
				$total_redbag_money =0;

				for($i=1;$i<$qty;$i++){
					$arr['res'][$i] = array(
					'redbag_id'=>$new_id,
					'amount' =>$money,
					'redbag_order_id'=>$i
					);

					$mdl_redbag_details->insert( $arr['res'][$i]);
					$total_redbag_money += $money;

					if($mdl_redbag_details->error()){	
						$insert_error=1;
					break;
					}
				}

				$arr['res'][$qty] = array(
				 'redbag_id'=>$new_id,
				   'amount' =>$amount_total-$total_redbag_money,
				   'redbag_order_id'=>$qty
				
				);
				$mdl_redbag_details->insert( $arr['res'][$qty]);
				  
				if($mdl_redbag_details->error()){	
                  	$insert_error=1;
				}
				// 插入普通红包结束
					
				}
				// 包好红包后，要将此次金额放入到支出栏中。
				$mdl_recharge = $this->loadModel( 'recharge' );

				$data_recharge = array(
					'orderId' => date( 'YmdHis' ).$this->createRnd(),
					'userId' => $this->loginUser['id'],
					'money' => $redbag_amount*(-1),
					'payment' => BalanceProcess::TYPE_REDBAG,
					'status' => 1,
					'createTime' => time(),
					'createIp' => ip(),
					'coupon_name'=>$name,
					'coupon_id'=>$new_id,
					'main_coupon_id'=>$new_id,
					'business_userId'=>$this->loginUser['id']
					);
			
			
				$rechargeid = $mdl_recharge->insert( $data_recharge );
			
				if(!$rechargeid){	
                  	$insert_error=1;
				}
			
				// 如果没有出现错误则总体提交
				if($insert_error==0){
					$mdl_redbag->commit();
					$mdl_redbag_details->commit();		
					$mdl_recharge->commit();
					
					$this->sheader(HTTP_ROOT_WWW.'redbag/redbag_show?uid='.$this->loginUser['id'].'&bag_id='.$new_id);
				}
				
			}
			else {
				//插入未成功回滚
				 $insert_error=1;
			}

			if($insert_error){
				$mdl_redbag->rollback();
				$mdl_redbag_details->rollback();
				$mdl_recharge->rollback();
				$this->sheader(HTTP_ROOT_WWW.'redbag/redbag?err_type=6');
			}

		}
		else {

			//获取包红包是否有错误
			$err_type=(int)get2("err_type");
			
			switch ($err_type)
			{
			case 1:
				$this->setData('红包名称长度必须大于6个字符或2个字','message');
				break;  
			case 2:
				$this->setData('红包名称长度必须小于60字符或20个字','message');
				break;
			case 3:
				$this->setData('红包金额必须为数字','message');
				break;			
		    case 4:
				$this->setData('红包不能大于$88888','message');
				break;	
	        case 5:
				$this->setData('你的账户金额不足！','message');
				break;	
	        case 6:
				$this->setData('最小金额为$0.10','message');
				break;				
			}

			// get coupons from the business user 
			// 可以将产品添加到红包上。
			$mdl_coupons= $this->loadModel('coupons');
			$where =array(
			  'createUserId'=>$this->loginUser['id'],
			  'isApproved'=>1,
			  'status'=>4
			);
			$sql = $mdl_coupons->getListSql( array('id', 'title' ), $where, " desc " );
			$data	= $mdl_coupons->getListBySql($sql);
			
			$this->setData($data,'data');
			
			$this->setData( '包红包', 'pagename' );
			$this->setData( 'redbag', 'menu' );
			$this->setData( 'redbag', 'submenu' );
			$this->setData( '包红包 - 个人中心 - '.$this->site['pageTitle'], 'pageTitle' );
			$this->display( 'redbag/redbag' );
		}
	}

	/**
	 * redbag single display
	 * @return [type] [description]
	 */
	function redbag_show_action() {
		
		$id=(int)get2('bag_id');
		$uid=(int)get2('uid');
		$resend=(int)get2('resend');
		
		if($uid){
			$this->setData($uid,'owner');
		}

		if($resend){
           $this->setData($resend,'resend');
		}
		
		$mdl_redbag = $this->loadModel( 'redbag' );
		$redbag=$mdl_redbag->get($id);
		$this->setData( $redbag, 'redbag' );
		
		// 检查该用户是否拆过红包
		if($this->loginUser['id']){
              $mdl_redbag_details =$this->loadModel("redbag_details");
			  $where =array(
			   'redbag_id'=>$id,
			   'userId'=>$this->loginUser['id']
			  );
			  $open_rec=$mdl_redbag_details->getByWhere($where);
			  if($open_rec){
				 $this->setData('1','openAlready'); 
			  }
		}
		 
		//wx share
		require_once "wx/wxjssdk.php";
        $jssdk = new WXjsSDK();
        $signPackage = $jssdk->GetSignPackage();
        $this->setData($signPackage,'signPackage');
	    
	    $this->setData( HTTP_ROOT_WX."redbag/redbag_show?uid=$uid&bag_id=$id", 'shareUrl' );
			
		$this->setData( '拆澳元红包', 'pagename' );
		$this->setData( 'redbag', 'menu' );
		$this->setData( 'redbag', 'submenu' );
		$this->setData( $redbag['name'].'-'.$this->site['pageTitle'], 'pageTitle' );
		$this->display( 'redbag/redbag_show' );
		
	}
	
	
	/**
	 * Redbag open page
	 * @return [type] [description]
	 */
	function redbag_list_action() {
	
		$id=(int)get2('bag_id');
		$type=	(int)get2('type');
		
		switch ($type){
			case 1:
				$this->setData('你还想抢，做梦，哈哈！','message');
				break;  
			case 2:
				$this->setData('又迟到！红包过期已撤回！','message');
				break;
			case 3:
				$this->setData('手慢了，红包拆完了！','message');
				break;			
		    case 4:
				$this->setData('红包过期被收回！','message');
				break;	
	        case 5:
				$this->setData('抢的人太多了，还没抢到，稍后再试','message');
				break;			
	        case 6:
				$this->setData('红包需要在微信中才能使用！','message');
				break;						
			default:
		}
	
		$mdl_redbag_details = $this->loadModel( 'redbag_details' );
		$mdl_redbag =$this->loadModel('redbag');
		
		$pageSql	=  " select a.*,b.avatar from cc_redbag_details a,cc_user b where a.userId=b.id and a.redbag_id=".$id." and a.userId>0  order by a.id desc limit 30";
		$data		= $mdl_redbag_details->getListBySql($pageSql);
		
		
	   foreach ( $data as $key => $val ) {
		   $avater_size = filesize('data/upload/'.$data[$key]['avatar']);
		   if($avater_size==0){
			   $data[$key]['avatar']=0;
		   }
		   
	   }
		
        $where =array(
		  'redbag_id'=>$id,
		  'userId'=>$this->loginUser['id']
		);
		$data1 =$mdl_redbag_details->getByWhere($where);
		
		
		$where_count =array(
		  'redbag_id'=>$id
		  	);
		
		$redbag_count=$mdl_redbag_details->getCount($where_count);
		$this->setData($redbag_count,'redbag_count');
		
		$redbag_coupon =$mdl_redbag->get($id);
		
		$this->setData($id,'redbag_id');
		
		if ($redbag_coupon){
			
			$coupon_id =$redbag_coupon['couponid'];
		}
		
		if($coupon_id){
			$mdl_coupons= $this->loadModel('coupons');
			
			$mdl_wj_customer_coupon = $this->loadModel( 'wj_customer_coupon' );
			$mdl_coupon_type = $this->loadModel('coupon_type');
			$mdl_user = $this->loadModel( 'user' );

			$sql=" select * from cc_coupons where id =".$coupon_id;
		
	     	$coupon		= $mdl_coupons->getListBySql($sql);
		
			foreach ( $coupon as $key => $val ) {
				$mdl_coupons->caculatePriceAndPoint($coupon[$key]);
			}

			$this->setData($coupon[0],'coupon');
			
		}
		
		$this->setData( $data, 'data' );
		$this->setData( $data1, 'data1' );
		
		$this->setData( '获得红包用户列表', 'pagename' );
		$this->setData( 'redbag', 'menu' );
		$this->setData( 'redbag', 'submenu' );
		$this->setData( '拆澳元红包 - '.$this->site['pageTitle'], 'pageTitle' );
		$this->display( 'redbag/redbag_list' );
	
	}
	
	
	/**
	 * Option Action
	 * @return [type] [description]
	 */
	function redbag_open_action() {
		$this->setData( '拆澳元红包', 'pagename' );
		$this->setData( 'redbag', 'menu' );
		$this->setData( 'redbag', 'submenu' );
		$this->setData( '拆澳元红包 - '.$this->site['pageTitle'], 'pageTitle' );
			 
		$id=(int)get2('redbag_id');
	
	    $mdl_redbag_details = $this->loadModel( 'redbag_details' );
		
		//获取当前红包记录
		$mdl_redbag = $this->loadModel( 'redbag' );
		$redbagMain=$mdl_redbag->get($id);
		
		// 检查红包是否过期
		if($redbagMain['status'] ==3) {
			$this->sheader(HTTP_ROOT_WWW.'redbag/redbag_list?type=2&bag_id='.$id);
			//$this->form_response(200,"红包过期已撤回！",HTTP_ROOT_WWW.'redbag/redbag_list?bag_id='.$id);
		}
		
		// 检查红包是否已被拆完
		if($redbagMain['status'] ==2) {
			$this->sheader(HTTP_ROOT_WWW.'redbag/redbag_list?type=3&bag_id='.$id);
			//$this->form_response(200,"手慢了，红包拆完了！",HTTP_ROOT_WWW.'redbag/redbag_list?bag_id='.$id);
		}
		
		// 检查是否已经拆过该红包
		if($this->openRedbagAlread($mdl_redbag_details,$id)) {
			$this->sheader(HTTP_ROOT_WWW.'redbag/redbag_list?type=1&bag_id='.$id);
			//$this->form_response(200,"还想拆，你已经拆过了。等下次吧！",HTTP_ROOT_WWW.'redbag/redbag_list?bag_id='.$id);
		}
		
		// 如果当前时间红包已经开了超过24小时，则收回红包
		
		
		
		$differ_time = (time()-$redbagMain['createtime'])/3600;
		//对于特定红包,收回时间根据情况定义. 146暂定是2018购年Ubonus美食生活红包,可以多次分发,到2018年2月19日失效. 如果时间大于下面的数字,说明到了19日
		if($id ==148) {
			if (time>1518958800 ) {
				
				$differ_time=337;
			}
			
			
		}
		
		
		if($differ_time>=336) {
			      $callbackRedbag =$this->loadModel('redbag')->redbag_callback($redbagMain,$id,$redbag_list);
				  $this->sheader(HTTP_ROOT_WWW.'redbag/redbag_list?type=4&bag_id='.$id);
                  //$this->form_response(200,"手慢了,红包过期被收回了，已经发出超过小时",HTTP_ROOT_WWW.'redbag/redbag_list?id='.$id);
		}
	
		$sql ="select * from cc_redbag_details  where redbag_id =".$id." and userId=0";
		$redbag_list = $mdl_redbag_details->getListBySql($sql);
		
		if (!$redbag_list) {
			$this->sheader(HTTP_ROOT_WWW.'redbag/redbag_list?type=3&bag_id='.$id);
			//$this->form_response(200,"手慢了，红包拆完了!",HTTP_ROOT_WWW.'redbag/redbag_list?bag_id='.$id);
		}


		$grap_success=0;
		$redbag_money=0;
		$details_id=0;
		
		
		$mdl_recharge = $this->loadModel( 'recharge' );
		$mdl_redbag_details->begin();
		$mdl_recharge->begin();
		
		foreach ( $redbag_list as $key => $val ) {
		  //尝试修改第一条，但是条件是当前还未被另外一个用户更改。
		   $details_id = $redbag_list[$key]['id'];
		   $whereA=array(
		       'id'=>$details_id,
			   'userId'=>0
		   );
		   $data_lock_redbag = array(
		     'userId'=>$this->loginUser['id'],
			 'userName'=>$this->loginUser['name'],
			 'createtime'=>time()
		   );
		   if(!$mdl_redbag_details->updateByWhere($data_lock_redbag,$whereA)) {
	           	continue;			
			   }else{
				   $grap_success =1;
				   $redbag_money = $redbag_list[$key]['amount'];
				   break;
			   }
		}
		
		
		// 如果未打开则开始拆红包
		 if ($grap_success==1) {

			$data_recharge = array(
				'orderId' => date( 'YmdHis' ).$this->createRnd(),
				'userId' => $this->loginUser['id'],
				'money' => $redbag_money,
				'payment' => BalanceProcess::TYPE_REDBAG,
				'status' => 1,
				'createTime' => time(),
				'createIp' => ip(),
				'coupon_name'=>'红包-2018.2.28日前使用，否则收回',
				'coupon_id'=>$id,
				'main_coupon_id'=>$id,
				'business_userId'=>$this->loginUser['id']
			);
			
			$rechargeid = $mdl_recharge->insert( $data_recharge );
			
			 if($rechargeid){
				 $mdl_redbag_details->commit();
				 $mdl_recharge->commit();
				 // 判断一下此时是否所有的红包都被抢光，如果枪光，则标记一下。 redbag  status=2 
				 if($this->allRedbagOpened($mdl_redbag_details,$id)){
					 $data_update = array (
					    'status'=>2
					 );
					 $mdl_redbag->update($data_update,$id);
				 }
				 
		 	 }else{
				  $mdl_redbag_details->rollback();
				 $mdl_recharge->rollback();
			 }
			
			 $this->sheader(HTTP_ROOT_WWW.'redbag/redbag_list?bag_id='.$id);
			// $this->form_response(200,"抢红包成功",HTTP_ROOT_WWW.'redbag/redbag_list?bag_id='.$id);
		 }else{
			 $this->sheader(HTTP_ROOT_WWW.'redbag/redbag_list?type=5&bag_id='.$id);
			//$this->form_response(200,"抢的人太多了，还没抢到，稍后再试",HTTP_ROOT_WWW.'redbag/redbag_show?bag_id='.$id);
			 
		 }
			
		$this->display( 'redbag/redbag_show' );
		
	}
	
	//检查红包是否已被当前用户拆过
	private function openRedbagAlread($mdl_redbag_details,$id){
		

		$where = array(
			'redbag_id'=>$id,
			'userId'=>$this->loginUser['id']
		);
		
		$redbag_list = $mdl_redbag_details->getList( null, $where, 'createtime desc ' , 1 );
		
		
		//对于允许多次打开的红包,检查当日是否被重复打开超过一次,比如下面编号的红包支持多次抽取
		if( $id ==148) {
			
			
			//获取当前用户最后一次打开红包的日期
			$current_date_number = $redbag_list[0]['createtime'];
			$last_open_date= date("Y-m-d",$current_date_number);
			
			//获取系统日期,如果当前日期超过2018年2月18日,则不能再开启.
			 if (time()> 1518958800) {
				 var_dump('红包已到期了');
				return 0;
			 }

			//如果相同则不能允许打开红包
			$today_date= date("Y-m-d",time());
			
			
			
			if($today_date == $last_open_date) {
				//var_dump('当前时间已抽' . $last_open_date );
				//var_dump($today_date );
				//exit; 
				return 1;
			}
			
			//var_dump('当前时间未抽'. $last_open_date );
			//var_dump($today_date );
			//exit; 
			
			return 0;
		} else{
	        //对于非多次打开红包,检查是否已经打开.
		
			
			if($redbag_list) {
			
				return 1;
			}else{
			
				return 0;
			}
		}
		
	}	
		
  //检查红包全部被拆完
	private function allRedbagOpened($mdl_redbag_details,$id){
		
		$where = array(
			'redbag_id'=>$id,
			'userId'=>0
		);
		if(!$mdl_redbag_details->getByWhere($where)) {
			
			return 1;
		}else{
			return 0;
		}
		
		
		
	}



}