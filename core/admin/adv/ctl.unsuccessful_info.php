<?php

/*
 @ctl_name = 客户订单管理@
*/

class ctl_unsuccessful_info extends adminPage
{

	function index_action () #act_name = 列表#
	{
		$sk = trim( get2( 'sk' ) );  //var_dump($sk);
		$success_buy = trim( get2( 'success_buy' ) );
      	$contacted = trim( get2( 'contacted' ) );
    
	
	   if ( empty( $contacted ) ) {
		   
		   $contacted =10;
	   }else{
		   if($contacted ==10) {}else 
		   
		   $where1=  " and contacted =$contacted";
		   
	   }
		 
		
	
	    $mdl_wj_temp_orderid_carts_for_yunying	= $this->loadModel('wj_temp_orderID_carts_for_yunying');
		$mdl_user	= $this->loadModel('user');
		

		$order	= " gen_date desc ";

        if(!$success_buy ) {
			$success_buy =0;
		}
		
		if ( ! empty( $sk ) ) $where = " and   phone LIKE '%$sk%'  ";
		
	
        
		//$pageSql="SELECT (select min(a.id)  from cc_order a where a.userId =ord.userId ) as first_id,cus.*,ord.id as idd ,ord.house_number,ord.coupon_status,ord.logistic_delivery_date,ord.logisitic_schedule_time,ord.payment,ord.money,ord.address,ord.status,ord.first_name,ord.last_name,ord.phone FROM cc_wj_customer_coupon AS cus LEFT JOIN cc_order AS ord ON cus.order_id=ord.orderId".$where." order by ".$order;
        
		$pageSql ="select  IFNULL ( c.userid ,0 )  as useridC, b.* ,a.*  from (SELECT id,userId,phone,name,`enter_paying_process`,`contacted`,`success_buy`,createTime ,err_num_cn,temp_arr, date_format(createTime,'%Y%m%d') as days  FROM `cc_wj_temp_orderid_carts_for_yunying` 
WHERE length(err_num_cn) >0  ".$where.$where1." and   createTime>unix_timestamp( subdate( now() , interval 7
day ))  and  orderId not in (select orderId from cc_order) ) a left join ( select userId as userIdb,max(id) idb , date_format(createTime,'%Y%m%d') as daysb  from  `cc_wj_temp_orderid_carts_for_yunying` group by userIdb,daysb ) b
on  a.userId =b.userIdb and a. days=b.daysb  
left join cc_order as  c on a.userId =c.userId  and UNIX_TIMESTAMP(a.createTime )<= c.createTime order by  idb desc,a.id desc";


		$pageUrl	= $this->parseUrl()->set( 'page' );
		$pageSize	= 60;
		$maxPage	= 50;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data2		= $mdl_wj_temp_orderid_carts_for_yunying->getListBySql($page['outSql']);

      
		// 如果追踪中有电话和姓名，可以把他们提前
		//var_dump($success_buy);
		$i=0;
		foreach ($data2 as $key => $value) {
			if($success_buy =='1') {
				if(!$value['useridC']) {
					$data[$i]=$value;
					$i++;
				}
				
			}elseif($success_buy =='2') {
				if($value['useridC']) {
					$data[$i]=$value;
					$i++;
				}
				
			}else{
				$data[$i]=$value;
				$i++;
			}
			
			
		}
		
		//var_dump($data);
		
		
		
		$data1=$data;
     	foreach ($data as $key => $value) {
			
			
			
			for($i=$key;$i<sizeof($data1);$i++) 
			{
				
				
			//var_dump('第一循环 第 '.$key. '个元素'. ' i is : '. $i. '编号：'. $data1[$i]['idb'].' 名字：' .$data1[$i]['name']. ' 电话' .$data1[$i]['phone'].'\n');
				if($value['idb']!=$data1[$i]['idb']) break;
				
				if($value['idb']==$data1[$i]['idb']) {
					//var_dump('主循环，idb'.$value['idb'].' '.'次循环'. $data1[$i]['idb'].' 名字：' .$data1[$i]['name']. ' 电话' .$data1[$i]['phone'].'\n');
						// var_dump('元素'.$key.'原名： ' . $data[$key]['name'] .' 现名'. $data1[$i]['name'] );
					 if ( $data1[$i]['name']) {
						
						 $data[$key]['name'] =  $data1[$i]['name'] ;
						 
					 }
					 
					 if ( $data1[$i]['phone']) {
						 $data[$key]['phone'] =  $data1[$i]['phone'] ;
						 
					 }
					 
				 }
				 
				 
				 
				
			}
			$data[$key]['temp_arr'] =unserialize(base64_decode($data[$key]['temp_arr']));
			$wholedata =$data[$key]['temp_arr'];
			$data[$key]['ids'] =$wholedata['ids'];
			$data[$key]['money'] =$wholedata['money'];
			$data[$key]['coupon_names'] =$wholedata['coupon_names'];
			$data[$key]['business_userId'] =$wholedata['business_userId'];
			$data[$key]['business_Name']=$mdl_user->getBusinessDisplayName($data[$key]['business_userId']);
			
		}
		//var_dump($data);
		

		$this->setData($data, 'data');
		$this->setData($success_buy, 'success_buy');
		$this->setData($contacted, 'contacted');
		$this->setData($sk, 'sk');
		
		$this->setData($page['pageStr'], 'pager');
		$this->setData($this->parseUrl()->set( 'act', 'detail' ), 'viewUrl');
		$this->setData($this->parseUrl()->set( 'sk' )->set( 'success_buy' )->set('page'), 'searchUrl');
		$this->setData($this->parseUrl()->set('deleteId')->set('id'), 'delUrl' );
		$this->setData( $search, 'search' );
		$this->setData($this->parseUrl(), 'refreshUrl' );

		
		$this->display();
	}


	public function update_action () #act_name = 审批#
	{
		
			$id = get2('id');
			$userId = get2('user_id');
			$status = get2('status');
			
			
			

			$where =array (
			  'id<='.$id,
			  'userId'=>$userId
			);
			
			$data =array(
			  'contacted'=>$status
			
			);
			//var_dump($data);
			//var_dump($where);
			//exit;
			$result=$this->loadModel('wj_temp_orderID_carts_for_yunying')->updateByWhere($data,$where);
			if($result){
				//success
				$this->sheader(HTTP_ROOT_WWW.'index.php?con=admin&ctl=adv/unsuccessful_info&act=index');
			}else{
				//fail
				echo 'fail';
			}
		
		
	}



	function detail_action () #act_name = 详情#
	{
		
		$id = trim(get2( 'id' ));
		$mdl_order	= $this->loadModel('order');
		$data = $mdl_order->getByOrderId($id);
		$type=get2('type');
		if ( ! $data ) {
			$this->sheader( null, '记录不存在' );
		}
		
		$mdl_coupons=$this->loadModel('coupons');
		 
        $activity_log=$this->loadModel('wj_user_coupon_activity_log')->getList(null,array('orderId'=>$id));
        foreach ($activity_log as $k => $l) {
            $activity_log[$k]['cn_description']=$mdl_coupons->actionlist_info($l['action_id']);
        }
        $this->setData($activity_log, 'log');


		$items	= $this->loadModel('wj_customer_coupon')->getList(null,array('order_id'=>$id));

		$this->setData($items, 'items');

		$this->setData($data, 'data');

       if ($type=='warning1') {
		   $this->setData($this->parseUrl()->set( 'ctl', 'adv/operation_monitor' )->set( 'act', 'warning1' ), 'listUrl');
		      
		   
	   }else if($type=='warning2'){
		     $this->setData($this->parseUrl()->set( 'ctl', 'adv/operation_monitor' )->set( 'act', 'warning2' ), 'listUrl');
		   
	   }
	   else{
		   $this->setData($this->parseUrl()->set( 'act' )->set('id'), 'listUrl');
		   
	   }
		

		$this->setData($this->parseUrl()->set( 'act','cancel')->set('id',$id), 'cancelUrl');
		

		$this->setData($this->parseUrl()->set( 'ctl','adv/hcash_record')->set( 'act','index')->set('keyword'), 'hcashUrl');

		$this->setData($this->parseUrl()->set( 'ctl','adv/company')->set( 'act','edit')->set('keyword'), 'businessUrl');

		$this->setData($this->loadModel('user')->getBusinessDisplayName($data['business_userId']),'businessName');
		

		$this->display();
	}

	function cancel_action () #act_name = 取消订单#
	{
	//	var_dump($this->parseUrl()->set( 'act','detail')->set('id',$id));exit;
		
		$id = trim(get2( 'id' ));
        
        if ( $id > 0 ) {
            $result=$this->cancel_customer_coupon('cancelBySystem',$id);
        }
          
        $this->sheader($this->parseUrl()->set( 'act','detail')->set('id',$id));

	}

}
?>