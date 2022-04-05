 <?php 
class ctl_test extends cmsPage
{

	public function index1_action(){
		$this->display('home');


	}

// 修改 cc_wj_customer_coupon 表种的 menu_id menu_cat_id 字段 

public function index3_action() {
	$_SERVER['DOCUMENT_ROOT']='C:/xampp/htdocs/cityb2b2';
	$_SERVER['REQUIST_URI']='www.ozsupply.com';
	$_SERVER['HTTP_HOST']='www.ozsupply.com';
	require_once('C:/xampp/htdocs/cityb2b2/core/include/config.inc.php');
	$mail_services =loadModel('system_mail_queue');
	$mail_services->run();

	$myfile = fopen("C:/xampp/htdocs/cityb2b2/log/mailQueueLog.txt", "a") or die("Unable to open file!");
	fwrite($myfile, "mail Queue working ".date('Y-m-d H:i:s')."\n");
	fclose($myfile);
}
 public function  test_write_menu_action()
   
   {
	   //当前处理的用户只有 217005 
	   $business_id =217005;
	   $sql =" select * from cc_wj_customer_coupon where business_id =217005" ;
       $mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');
	   
	   $list= $mdl_wj_customer_coupon->getListBySql($sql);
	  
	     foreach ($list as $key => $value) {
			 
			 $menu_name = $list[$key]['bonus_title'] ;
			 $menu_id = substr($menu_name,0,strrpos($menu_name,'-'));
			 $id=  $list[$key]['id'] ;
			 
			 $data =array(
				'menu_id'=>$menu_id
			  );
			
			
			$mdl_wj_customer_coupon->update($data,$id);
			
		 }
	  
	  
	  // var_dump ($list);exit;
	   
   }
  

  public function  test_singer_vote_action()
   
   {
	   $id = get2('id');
	   $txt='sfdsdf';
	   $mdl_voting_singer = $this->loadModel('voting_singer');
	   $mdl_voting_txt = $this->loadModel('voting_txt');
	   $mdl_voting_item = $this->loadModel('voting_item');
	   
	   $txt =$mdl_voting_txt->get($id);
	   $textstr=$txt['txt'];
	   str_replace("</p>","<br />",$textstr);

	   $voteArr = explode( '<br />', $textstr );
	   $newvoteArr =array();
	   $k=0;
	   $find=0 ;
	    foreach ($voteArr as $key => $value) {
			
			if ($find) {
				$find=0;
				continue;
				
			}
			$pos1 = stripos($voteArr[$key], '&nbsp;');
			if ($pos1){
				$newvoteArr[$k][0]=$voteArr[$key];
				$voreplayNum = trim($voteArr[$key+1]);
				if (is_numeric($voreplayNum)) {
					
					$newvoteArr[$k][1]=$voreplayNum;
				}else{
					
					$newvoteArr[$k][1]='000';
				}
				
				$find=1;
				$k++;
			}
			
		}
		
	
	  var_dump($newvoteArr);
	  
	  $fail_count =0;
	  $success_count=0;
	  // 开始插入数据
	  foreach ($newvoteArr as $key => $value) {
		   if (trim($newvoteArr[$key][0])=='') {
			   $newvoteArr[$key][0] =rand(100,9999);
			   
		   }
			$data =array(
				'nickname'=>$newvoteArr[$key][0],
				'singer_ID'=>$newvoteArr[$key][1],
				'createtime'=>time()
			);
			$playerId =(int)$newvoteArr[$key][1];
			if($mdl_voting_singer->insert($data)) {
				 $success_count++;
				 //向 voting_item数据 (歌手选票)
						 var_dump('<br>更新选手编号'.$playerId.'<br>');		 
				 if($mdl_voting_item->updateCount($playerId,1)){
					    var_dump('<br>更新选手选票成功'.$newvoteArr[$key][1].'<br>');
					 
				 }else{
					  var_dump('<br>更新选手选票失败'.$newvoteArr[$key][1].'<br>');
					 
				 }
				
			}else{
				 $fail_count ++;
				  var_dump('<br>更新选手选票失败!选手编号'.$playerId.'('.$newvoteArr[$key][0].')<br>');
				
			}
			
	   }
	   var_dump('<br>失败次数'.$fail_count.'<br>');
	   var_dump('<br>成功次数'.$success_count.'<br>');
	  exit;
   }

   public function  test_rest_action()
   
   {
	   
	    ini_set('memory_limit', '1024M');
	  // 定义初始信息
      $restaurantId=0;
	  $DishTypeId =0;
	  $menu_id=100; //100-600
	  $category_id=100; // 100 -5000 
	  $category_sort_id=100; // 100-600
	  $count=0;
	  
	  
	  $mdl_restaurant_category = $this->loadModel('restaurant_category');
	  $mdl_restaurant_menu = $this->loadModel('restaurant_menu');
	  $mdl_restaurant_promotion_manjian = $this->loadModel('restaurant_promotion_manjian');
	  $mdl_coupon = $this->loadModel('coupons');
	  $mdl_user = $this->loadModel('user');
	  $mdl_restaurant_info = $this->loadModel('restaurant_info');
	  $mdl_cursor = $this->loadModel('cursor');
	  $sql ="select * from cc_cursor where is_selected =1 order by ResturantID,DishTypeId,DishRandomNum";
	  $list=$mdl_cursor->getListBySql($sql);
      
	  if($list) {
	   foreach ($list as $key => $value) {
		 if($value['is_selected']) continue;
		  $business_name =str_replace('&nbsp;','',$value['Name']);
		    $business_name =str_replace("'",'',$value['Name']);
			  $business_name =str_replace("'",'',$value['Name']);
		  if ( $value['ResturantID']!=$restaurantId) {
			   //  正常为0, restauarantID , 进入再次循环时, restauarantID <>0 , 这是表示这个餐馆数据已经处理完 .则,统一置 is_selected=1
			   if($restaurantId) {
				   $data_cursor=array(
				   'is_selected'=>'1'
		    	 );
				 $where =array('ResturantID'=>$restaurantId);
				 
				 $mdl_cursor->updateByWhere($data_cursor,$where);
				 
				   
			   }
			  
			  // 如果类别ID>100 and 类别id<5000 则补到5000
			  $i=0;
			  while ($category_id>100 and $category_id<=5000 and $i<=10) {
				 $i++;
				  $data_category=array(
			     'restaurant_id'=>$newUserid,
				 'category_id'=>$category_id,
				 'category_sort_id'=>$category_sort_id,
				 'category_cn_name'=>'',
				 'category_en_name'=>'',
				 'createUserId'=>$newUserid,
				  'ref_restaurant_id'=>$restaurantId,
				 'ref_DishTypeId'=>$DishTypeId
			  );
			  
			
		      $category_id =$category_id +100;
			  $category_sort_id =$category_sort_id+10;
			  $new_categroy_id=$mdl_restaurant_category->insert($data_category); 
				  
			  }
			   $category_id=100; // 100 -5000 
	           $category_sort_id=100; // 100-600
	  
			 //  var_dump($category_id);exit;
			  // 如果>=5000 ,则再补10个
			  
			  
			  
			  
			  
			  // 如果式新餐馆
			  $restaurantId=$value['ResturantID'];
		
				// 创建餐馆用户信息 ,获得的用户ID,做为新餐馆的ID,
				$user_data=array(
				  'name'=>substr($business_name,0,50),
				  'address'=>$value['ShopAddress'],
				  'role'=>3,
				  'categoryId'=>',106121102,',
				  'password'=>md5($value['ResturantID'].'000'),
				  'nickname'=>$business_name,
				  'isApproved'=>1,
				  'createdDate'=>time(),
				  'displayName'=>substr($business_name,0,50),
				  'businessName'=>substr($business_name,0,50),
				  'cityId'=>',556,',
				  'google_location'=>$value['ShopAddress'],
				  'countryCode'=>'AU',
				  'addrState'=>'Victoria',
				  'businessRefPointPercent'=>10,
				  'customerRefPointPercent'=>10,
				  'trustLevel'=>1,
				  'ratingsource'=>'餐馆管理员',
				  'languageType'=>'zh-en',
				  'applcationStatus'=>5,
				  'visibleForBusiness'=>0,
				  'needReapprovedAfterEdit'=>1,
				  'supportpaypalpayment'=>'supportpaypalpayment',
				  'supportofflinepayment'=>'supportofflinepayment',
				  'supportroyalpaypayment'=>'supportroyalpaypayment',
				  'supportcreditcardpayment'=>'supportcreditcardpayment',
				  'paypalsurcharge'=>0.0295,
				  'royalpaysurcharge'=>0.0160,
				  'hcashsurcharge'=>0.0300,
				  'creditcardsurcharge'=>0.0150,
				  'transactionFeeChargeFrom_paypal'=>'user',
				  'transactionFeeChargeFrom_royalpay'=>'user',
				  'transactionFeeChargeFrom_hcash'=>'platform',
				  'transactionFeeChargeFrom_creditcard'=>'user',
				  'bookingfee'=>0,
				  'refund_policy'=>'<p>本产品购买2周内无条件退换</p>',
				  'IsTransform'=>1,
				  'settlement_type'=>'b01',
				  'notice'=>$business_name.'上线Ubonus美食生活,多重惊喜等着你!',
				  'platform_commission_rate'=>'0.10',
				  'platform_commission_base'=>'0',
				  'belong_to_sales_manager'=>0,
				  'business_type_shop'=>0,
				  'business_type_service'=>0,
				  'business_type_restaurant'=>1,
				  'business_type_media'=>0,
				  'pickup_avaliable'=>1,
				  'offline_pay_des'=>'请到店出示订单,并支付订单显示的总额(Please show your order and pay the total amount as displayed on the order!',
				  'isSuspended'=>0,
				  'abn'=>'199776'
				);	  
				//var_dump($user_data);exit;
				
				$newUserid=$mdl_user->insert($user_data);
					
			    // 如果生成了用户,则继续生成餐馆信息  // 创建线上餐厅信息
				if ($newUserid>0) {
					$data_restaurant_info=array(
					  'name'=>substr($business_name,0,50),
					  'is_approved'=>1,
					  'status'=>4,
					  'userID'=>$newUserid
					);
					//生成新的restaurantID
					$rest_id =$mdl_restaurant_info->insert($data_restaurant_info);
					
					//如果餐馆信息生成成功,则继续生成coupon (餐馆的coupon) 	  //创建餐厅coupon 信息
					if($rest_id) {
						$data_coupon=array(
						  'EvoucherOrrealproduct'=>'restaurant_menu',
						  'title'=>substr($business_name,0,50).'线上餐厅',
						  'businessName'=>substr($business_name,0,50),
						  'bonusType'=>7,
						  'expiredDay'=>0,
						  'categoryId'=>',106121102,',
						  'startTime'=>time(),
						  'endTime'=>1852380000,
						  'autoOffline'=>0,
						  'createUserId'=>$newUserid,
						  'createTime'=>time(),
						  'index'=>0,
						  'recommend'=>0,
						  'featured'=>0,
						  'isApproved'=>0,
						  'hits'=>0,
						  'buy'=>0,
						  'city'=>',556,',
						  'qty'=>0,
						  'perCustomerLimitQuantity'=>0,
						  'perCustomerMinLimitQuantity'=>0,
						  'finePrint'=>'<p><span style="font-size:14px">营业地点:&nbsp; <span style="background-color:transparent; color:rgb(51, 51, 51)">'.$value['ShopAddress'].'</span></span></p>',
						  'visibleForBusiness'=>1,
						  'voucher_original_amount'=>0,
						  'voucher_deal_amount'=>0,
						  'group_buying_id'=>0,
						  'group_buying_name'=>0,
						  'status'=>4,
						  'languageType'=>'zh-en',
						  'staff_region_limited'=>0,
						  'deliver_avaliable'=>0,
						  'flat_rates_to_local_city'=>0.00,
						  'flat_rates_national'=>0.00,
						  'flat_rates_international'=>0.00,
						  'delivery_description'=>'',
						  'pickup_des'=>'下单后请联系餐馆告知取货时间!',
						  'offline_pay_des'=>'请到店出示订单,并支付订单显示的总额(Please show your order and pay the total amount as displayed on the order!',
						  'pickup_avaliable'=>1,
						  'amount_for_free_delivery'=>-1.00,
						  'deliverFeeCalculationType'=>'per_coupon',
						  'refund_policy'=>'<p>本产品购买2周内无条件退换</p>',
						  'platform_commission_rate'=>0.05,
						  'platform_commission_base'=>0.00					
						
						);
						
						
						
						if($mdl_coupon->insert($data_coupon)) {
						//	var_dump('coupon insert success');exit;
							$data_manjian=array(
							   'restaurant_id'=>$newUserid,
							   'discount'=>12,
							   'createUserId'=>$newUserid
							);
							
							$aa= $mdl_restaurant_promotion_manjian->insert($data_manjian);
							//var_dump($aa. 'sdsd');exit;
							
						}
						
					}
					
				}
				
			 	  
		
	//	var_dump($data_manjian);exit;
			  
			  // 创建餐厅优惠信息
			  
		  }
		 // var_dump( $value['DishTypeId'].' '.$DishTypeId);exit;
		   if ( $value['DishTypeId']!=$DishTypeId) {
			   
			 //  var_dump( $value['DishTypeId'] . ' ' . $DishTypeId);exit;
			   
			   // 如果类别ID>100 and 类别id<600则补到600
			 // var_dump($menu_id);exit;
			 $i=0;
			  while ($menu_id>100 and $menu_id<=600  and $i<=10) {
				
				 $i++;
				  $data_menu=array(
				    'restaurant_id'=>$newUserid,
					'restaurant_category_id'=>$new_categroy_id,
					'menu_id'=>$new_categroy_id.$menu_id,
					'menu_cn_name'=>'',
					'price'=>0.00,
					'menu_pic'=>'',
					'createUserId'=>$newUserid
				 );
				 $mdl_restaurant_menu->insert($data_menu);
				 $menu_id =$menu_id+10;
			  
			  
		    
			
				  
			  }
			   
			   $menu_id=100;
			  // 如果新类别
			  $DishTypeId=$value['DishTypeId'];
			  $data_category=array(
			     'restaurant_id'=>$newUserid,
				 'category_id'=>$category_id,
				 'category_sort_id'=>$category_sort_id,
				 'category_cn_name'=>$value['DishTypeName'],
				 'category_en_name'=>'',
				 'createUserId'=>$newUserid,
				 'ref_restaurant_id'=>$value['ResturantID'],
				 'ref_DishTypeId'=>$DishTypeId
			  );
			  
			
		      $category_id =$category_id +100;
			  $category_sort_id =$category_sort_id+10;
			  $new_categroy_id=$mdl_restaurant_category->insert($data_category); 
			 
			// var_dump($new_categroy_id);exit;			  
		  }
		  
		   
			 if($new_categroy_id) {
				 $data_menu=array(
				    'restaurant_id'=>$newUserid,
					'restaurant_category_id'=>$new_categroy_id,
					'menu_id'=>$new_categroy_id.$menu_id,
					'menu_cn_name'=>$value['DishName'],
					'price'=>$value['DishPrice'],
					'menu_pic'=>'restaurant/'.$value['ShopRandomNum'].'/'.$value['DishRandomNum'].'.jpg',
					'createUserId'=>$newUserid
				 );
				 $menuID =$mdl_restaurant_menu->insert($data_menu);
				
				 $menu_id =$menu_id+10;
				 
			
				  
			 }	
			 
			 
		  
	   }
	  }
	   
	   
	   
	   
	   
	   
	   
	   
   }


	public function test_qty_statge_action()
	{	
		$max = 20000;
		$totalError=0;
		for ($i=0; $i <$max ; $i++) { 
           $data = $this->loadModel('stage_qty')->qtySegmentation($i);
           $total= array_sum(explode('#', $data));

			var_dump(($total==$i));
			echo "$i = $total ";
			echo $data;
			echo '<br>';

           if($total!=$i){
           	   $totalError++;
           }
        }
        $errorRate = $totalError/$max;
        echo "Error Rate $totalError/$max $errorRate";
	}

	public function test_qty_segmentation_process_action()
	{
		$this->loadModel('stage_qty')->qtySegmentationProcess();
	}

	public function test_qty_segmentation_next_action()
	{
		$this->loadModel('stage_qty')->nextStage();
	}

	public function ccategory_action()
	{
		$mdl= $this->loadModel('customizableCategory');
		$mdl->setUserId($this->loginUser['id']);

		for ($i=1; $i <10 ; $i++) { 
			$item = new CustomizableCategoryItem();
			$item->setName($i);
			$item->setOrder($i);
			$id = $mdl->addChild($item,$parentId);

			for ($n=1; $n <10 ; $n++) { 
				$item = new CustomizableCategoryItem();
				$item->setName($i.'.'.$n);
				$item->setOrder($n);
				$sid=$mdl->addChild($item,$id);

				for ($m=1; $m <10 ; $m++) { 
					$item = new CustomizableCategoryItem();
					$item->setName($i.'.'.$n.'.'.$m);
					$item->setOrder($m);
					$mdl->addChild($item,$sid);
				}
			}
		}
	}

	public function order_name_action()
	{	
		$mdl_order=$this->loadModel('order');
		$list = $mdl_order->getList('orderId');
		foreach ($list as $o) {
			echo  $mdl_order->generateOrderName($o['orderId']);
			echo '<br>';
		}
		 
	}

	public function add_to_queue_action()
	{
		# code...
	}

	public function run_queue_action()
	{
		$this->loadModel('system_mail_queue')->run();
	}

	public function test_db_transaction_action()
	{

	}

	public function test_wechat_message_action()
	{
		$orderId = get2('id');
		$mdl_wechat_message=$this->loadModel('wechat_message');
		$mdl_wechat_message->send($orderId,WechatMessageType::CustomerOrderNotification);
		$mdl_wechat_message->send($orderId,WechatMessageType::BusinessOrderNotification);
	}

	public function test_notification_center_action()
	{
		$this->loadModel('system_notification_center')->notify(SystemNotification::NewOrder,'20170806154425729700');
	}

	public function test_ref_action()
	{
		$mdl_referral_relation=$this->loadModel('referral_relation');

			$mdl_referral_relation->owner('23928');
			for ($i=0; $i <10 ; $i++) { 
				$mdl_referral_relation->addBusiness('3000'.$i);
			}

			for ($i=0; $i <10 ; $i++) { 
				$mdl_referral_relation->addUser('4000'.$i,'9000'.$i);
			}
		

		// $data1=$mdl_referral_relation->getBusinessList();

		// $data2=$mdl_referral_relation->getUserList();

		// $this->dump($data1);
		// $this->dump($data2);

		// $result1 =$mdl_referral_relation->getCouponRefUserId(30008,90008);
		// $result2 =$mdl_referral_relation->getBusinessRefUserId(20009);

		// $this->dump($result1);
		// $this->dump($result2);

	}

	public function test_find_ref_action()
	{	

		/**
		 * if A refer X to B
		 * when B buy X
		 * B need to find A
		 * 
		 * @param int $userId  B's Id
		 * @param int $couponId  X's Id
		 * @return A's Id
		 */

		$A_id=10001;
		$B_id=10002;
		$X_id=3123;

		$mdl_referral_relation=$this->loadModel('referral_relation');

		$mdl_referral_relation->owner($A_id)->addUser($B_id,$X_id);

		$result =$mdl_referral_relation->getCouponRefUserId($B_id,$X_id);

		var_dump($result); //should be $A_id


		$mdl_referral_relation->owner($A_id)->addBusiness($B_id);

		$result =$mdl_referral_relation->getBusinessRefUserId($B_id);

		var_dump($result); //should be $A_id


	}

	public function test_find_business_ref_action()
	{
		$mdl_referral_relation=$this->loadModel('referral_relation');
		$data=$mdl_referral_relation->owner('')->businessExist('2231220');
		var_dump($data);
	}

	public function test_remove_business_ref_action()
	{
		$mdl_referral_relation=$this->loadModel('referral_relation');
		$data=$mdl_referral_relation->owner('1234')->removeBusiness('22320');
		var_dump($data);
	}
	public function become_staff_of_event_coupon_action()
	{	
		$mdl_redeem_staff=$this->loadModel('redeem_staff');
		// 自动增加 ubonusshop 成为秒抢产品的兑付商家
		$sql = "SELECT DISTINCT user_id FROM cc_coupon_event_management WHERE status = 2";

		$list = $mdl_redeem_staff->getListBySql($sql);

		$userid = UBONUSSHOPID;

		foreach ($list as $l ) {
			$businessId=$l['user_id'];
		
			if(!$this->loadModel('redeem_staff')->existInCompany($userid, $businessId)){
	    		$this->loadModel('redeem_staff')->joinCompany($userid, $businessId);
	    		echo "ubonus shop 成为 $businessId 的兑付员工<br>";
			}
		}
		

	}

	

	public function test_new_message_send_action()
	{
		$id=$this->loadModel('messages')
		->from('100000')
		->to('200000')
		->subject('测试邮件')
		->content('测试邮件的内容可以说很长很馋给的 哈手机客户端看撒娇的发挥空间啊收到货额热切什么都哟哟akaskdjhaskjfh aklsdjfh kajlsdfh jhasdjkfhkj haskdjfhkljsdahf ')
		->related_coupon('1234')
		->related_message('4321')
		->en_subject('English subtitle')
		->orderId('10101010101')
		->send();

		$this->dump($this->loadModel('messages')->get($id));
	}

	public function settlement_log_action()
	{
		$mdl_settlement_log=$this->loadModel('settlement_log');

		for ($i=0; $i <50 ; $i++) { 
			$mdl_settlement_log
			->owner('23928')
			->settleFrom('2017-8-15')
			->settleTo('2017-8-25')
			->settleOrderStatus('c01')
			->settleAmount('101.23')
			->operationType('auto')
			->log();	# code...
		}
		

		$this->dump($mdl_settlement_log->lastSettlementPoint());
	}

	public function order_log_action()
	{
		$mdl_settlement_log=$this->loadModel('wj_user_coupon_activity_log');

		for ($i=0; $i <50 ; $i++) { 
			$mdl_settlement_log
			->orderId('239281231232412341234')
			->userId('1234')
			->userName('nihaoma')
			->actionId('c01')
			->log();	
		}
		

		$this->dump($mdl_settlement_log->getList());
	}


	public function usefulSQL()
	{
		// SELECT * FROM `cc_wj_user_coupon_activity_log` WHERE gen_date >(UNIX_TIMESTAMP()-3600*24*14) and action_id = 'b01'
		// 上两周 兑付的 订单
		// 
		
		
		//shipping label
		//
		//SELECT c.userId,c.order_id, c.bonus_title,c.customer_buying_quantity,o.first_name,o.last_name,o.address,o.phone,o.email,o.message_to_business ,o.coupon_status, if(o.status=1,'已支付','未支付'), if(o.customer_delivery_option=1,'递送','自取') FROM cc_wj_customer_coupon as c left join cc_order as o on o.orderId = c.order_id where c.gen_date >UNIX_TIMESTAMP('2017-08-19 00:00:00') order by c.order_id desc,c.userId
		//
		//
		//销量 统计
		//SELECT bonus_id,bonus_title,business_id,business_name,sum(customer_buying_quantity) as total FROM `cc_wj_customer_coupon` group by bonus_id,business_id order by total desc
	}

	/*田野测试wechat发送模版*/
	public  function test_customercancelorder_action()
    {//http://localhost/ubonus/test/test_customercancelorder?id=20170806154425729700
        $orderId = get2('id');
        $mdl_wechat_message=$this->loadModel('wechat_message');
        $mdl_wechat_message->send($orderId,WechatMessageType::CustomerCancelOrderNotification);
        $mdl_wechat_message->send($orderId,WechatMessageType::BusinessCancelOrderNotification);
    }

    public  function  test_businessdelivery_action()
    {// /test/test_businessdelivery?id=20170819080229152911
        $orderId = get2('id');
        $mdl_system_notification_center = $this->loadModel('system_notification_center');
        //wechat and email
	    $mdl_system_notification_center->notify(SystemNotification::BusinessDelivery, orderId);
    }

    public function  text_customerregistry_action()
    {//http://localhost/ubonus/test/text_customerregistry?id=26326
        $userId = get2('id');
        $mdl_wechat_message=$this->loadModel('wechat_message');
        $mdl_wechat_message->send($userId,WechatMessageType::CustomerRegistryNotification);
    }

    /*等chris结算完毕后进行测试wechat发送*/
    public function test_businessbalance()
    {

    }

    /*发送邮件通知测试*/

     public function email_test_welcome_action() {

		 $EMAIL_MODE =get2('mode');
		// $EMAIL_MODE=true;
         $template = $this->loadModel('system_mail_template');
         $system_mailer = $this->loadModel('system_mail');

         $to = 'ubonus100m@gmail.com';
         $customer_name ='xiao mo ';
         $title ="welcome to Cityb2b";
         $body  = $template->customerRegistryNotificationNew($this->loginUser,'222228');
         $system_mailer->title($title);
         $system_mailer->body($body);
         $system_mailer->to($to);

         if(!$EMAIL_MODE){
			 echo $body."<br/>";
         }else{
             $status=$system_mailer->send();
			 echo $status;
         }










     }

	 public function labelprint_action(){

		 $this->display('test/labelprint');
	 }


	 public function generate_sequence_number_action(){
		 $mdl_order = $this->loadModel('order');
		$new_seq_number = $mdl_order->generateLogisticSequence(319188,1647558000);

       var_dump($new_seq_number);

	 }
    public function test_all_mail_action()
    {   
        /**
         * 预览邮件
         */
        $PREVIEW_MODE=true;

        $systemId =get2('id');
        $template = $this->loadModel('system_mail_template');
        $system_mailer = $this->loadModel('system_mail');

    //    $systemId='20180501165248206434';

        $to = 'chriswangworking@gmail.com';//test email account;

        // case EmailType::CustomerOrderNotification:

            $orderName=$this->loadModel('order')->generateOrderName($systemId);

            $title = (string)$this->lang->email_order_confirmed ." -- $orderName --  cityb2b.com";
            $body  = $template->customerOrderNotification($systemId,$this->getLangStr());

            $system_mailer->title($title);
            $system_mailer->body($body);
            $system_mailer->to($to);

            if($PREVIEW_MODE){
                echo $body."<br/>";
            }else{
                $status=$system_mailer->send();
            }
        
        // case EmailType::BusinessOrderNotification:
            
            $title = (string)$this->lang->email_order_confirmed ." -- cityb2b.com";
            $body  = $template->businessOrderNotification($systemId);

            $system_mailer->title($title);
            $system_mailer->body($body);
            $system_mailer->to($to);

            if($PREVIEW_MODE){
                echo $body."<br/>";
            }else{
                $status=$system_mailer->send();
            }


        // case EmailType::CustomerCancelOrderNotification:

            $title = "用户取消订单通知 -- Ubonus美食生活 cityb2b.com";
            $body  = $template->customerCancelOrderNotification($systemId);

            $system_mailer->title($title);
            $system_mailer->body($body);
            $system_mailer->to($to);

            if($PREVIEW_MODE){
                echo $body."<br/>";
            }else{
                $status=$system_mailer->send();
            }

        // case EmailType::BusinessCancelOrderNotification:

            $title = "商家取消订单通知 -- Ubonus美食生活 cityb2b.com";
            $body  = $template->businessCancelOrderNotification($systemId);

            $system_mailer->title($title);
            $system_mailer->body($body);
            $system_mailer->to($to);

            if($PREVIEW_MODE){
                echo $body."<br/>";
            }else{
                $status=$system_mailer->send();
            }

        // case EmailType::CustomerRegistryNotification:

            $title = "新用户注册通知 -- Ubonus美食生活 cityb2b.com";
            $body  = $template->customerRegistryNotification($systemId);

            $system_mailer->title($title);
            $system_mailer->body($body);
            $system_mailer->to($to);

            if($PREVIEW_MODE){
                echo $body."<br/>";
            }else{
                $status=$system_mailer->send();
            }



        // case EmailType::BusinessDeliveryNotification:

            $title = "商家发货通知 -- Ubonus美食生活 cityb2b.com";
            $body  = $template->businessDeliveryNotification($systemId);

            $system_mailer->title($title);
            $system_mailer->body($body);
            $system_mailer->to($to);

            if($PREVIEW_MODE){
                echo $body."<br/>";
            }else{
                $status=$system_mailer->send();
            }

        // case EmailType::BusinessBalanceNotification:

            $title = "Ubonus结算通知 -- Ubonus美食生活 cityb2b.com";
            $body  = $template->businessBalanceNotification($systemId);

            $system_mailer->title($title);
            $system_mailer->body($body);
            $system_mailer->to($to);

            if($PREVIEW_MODE){
                echo $body."<br/>";
            }else{
                $status=$system_mailer->send();
            }

        // case EmailType::CustomerSubscribeNotification:

            $title = "产品订阅 -- Ubonus美食生活 cityb2b.com";
            $body  = $template->customerSubscribeNotification($systemId);


            $system_mailer->title($title);
            $system_mailer->body($body);
            $system_mailer->to($to);

            if($PREVIEW_MODE){
                echo $body."<br/>";
            }else{
                $status=$system_mailer->send();
            }

    }

    public function marketing_email_template_preview_action()
    {
    	echo $this->loadModel('system_mail_template')->customerSubscribeNotification();
    }

    public function test_dispute_create_action()
    {
    	$mdl=$this->loadModel('dispute_center');

    	$mdl->owner(23928)
    	->order('20170829144142212341')
    	->reason('werwioeyroskahfkjasdghkasjdfhopasdhfdsa')
    	->openNewCase();

    	$mdl->getCase();

    	$mdl->action;

    	while(!$mdl->action->isActionComplete()){
    		$prev=$mdl->action->getPrevStep();
    		$current=$mdl->action->getCurrentStep();
    		$next = $mdl->action->getNextStep();

    		echo "<h1>".$mdl->action->current_index."</h1>";

    		echo '<h4>prev</h4>';
    		var_dump($prev);
    		echo '<br>';

    		echo '<h4>current</h4>';
    		var_dump($current);
    		echo '<br>';

    		echo '<h4>next</h4>';
    		var_dump($next);
    		echo '<br>';
    		$mdl->action->completeCurrentStep();
    	}
    	

    	for ($i=0; $i <$mdl->action->action_length ; $i++) { 
    		$mdl->action->completeToStep($i);
	    	echo "<h1>".$mdl->action->current_index."</h1>";

	    	$prev=$mdl->action->getPrevStep();
			$current=$mdl->action->getCurrentStep();
			$next = $mdl->action->getNextStep();

			echo '<h4>prev</h4>';
			var_dump($prev);
			echo '<br>';

			echo '<h4>current</h4>';
			var_dump($current);
			echo '<br>';

			echo '<h4>next</h4>';
			var_dump($next);
			echo '<br>';
    	}
    	
    }

    public function test_order_operation_log_action()
    {
    	$id = $this->loadModel('order_operation_log')->order('12312489215739245798174')->process()->by(123456)->log();

    	$data = $this->loadModel('order_operation_log')->get($id);

    	$this->dump($data);

    	$id = $this->loadModel('order_operation_log')->order('12312489215739245798174')->view()->by(123456)->log();

    	$data = $this->loadModel('order_operation_log')->get($id);

    	$this->dump($data);

    	$id = $this->loadModel('order_operation_log')->order('12312489215739245798174')->by(123456)->log();

    	$data = $this->loadModel('order_operation_log')->get($id);

    	$this->dump($data);

    	$id = $this->loadModel('order_operation_log')->order('12312489215739245798174')->log();

    	$data = $this->loadModel('order_operation_log')->get($id);

    	$this->dump($data);
    }

    public function test_group_pin_action()
    {
    	$basegGroupId=11;
    	$baseCouponId=3489;
    	$testOrderId =date( 'YmdHis' ).$this->createRnd();

    	$userA = 10101;
    	$userB = 10102;

    	$mdl_group_pin = $this->loadModel('group_pin');

    	/**
    	 *  coupon 是否有拼团
    	 */
    	
    	if($mdl_group_pin->hasGroupPin($baseCouponId)){
    		echo 'Pass#<br>';
    	}else{
    		echo 'Error#<br>';
    	}

    	if($mdl_group_pin->hasGroupPin(123124)){
    		echo 'Error#<br>';
    	}else{
    		echo 'Pass#<br>';
    	}


    	/**
    	 * 用户A 一键拼单
    	 */
    	
    	$userGroupId = $mdl_group_pin->createAndJoinGroup($userA,$basegGroupId,$testOrderId);

    	//test
    	$list = $mdl_group_pin->getUserGroupList($basegGroupId);
    	$this->dump($list);

    	$list = $mdl_group_pin->getUserGroupUserList($userGroupId);
    	$this->dump($list);


    	/**
    	 * 用户B 去拼单
    	 */

    	$mdl_group_pin->joinGroup($userB,$userGroupId,$testOrderId);


    	//test
    	$list = $mdl_group_pin->getUserGroupList($basegGroupId);
    	$this->dump($list);

    	$list = $mdl_group_pin->getUserGroupUserList($userGroupId);
    	$this->dump($list);


    	/**
    	 * 是否过期
    	 */
    	if($mdl_group_pin->isUserGroupExpire(200)){
    		echo 'Expire#<br>';
    	}else{
    		echo 'Not Expire#<br>';
    	}


    	/**
    	 * create Group Until it is full
    	 */
    	
    	while(!$mdl_group_pin->isGroupFull($basegGroupId)){
    		$userGroupId=$mdl_group_pin->createUserGroup($basegGroupId);
    	}


    	/**
    	 * add user Until user Group is full
    	 */
    	
    	while(!$mdl_group_pin->isUserGroupFull($userGroupId)){
    		$mdl_group_pin->joinGroup($userB,$userGroupId,$testOrderId);
    	}

    	
    	if($mdl_group_pin->isGroupPinCode('moCiOi')){
    		echo 'Pass#<br>';
    	}else{
    		echo 'Error#<br>';
    	}

    	if(!$mdl_group_pin->isGroupPinCode('mo234iOi')){
    		echo 'Pass#<br>';
    	}else{
    		echo 'Error#<br>';
    	}



    }

    public function test_assist_action()
    {
    	$mdl_friend_assist= $this->loadModel('friend_assist');

    	$userA = 12345;
    	$userB = 55555;

    	echo $mdl_friend_assist->isAlreadyInGame($userA);
    	echo $mdl_friend_assist->playGame($userA);
    	echo $mdl_friend_assist->isAlreadyInGame($userA);
    	echo $mdl_friend_assist->playGame($userA);


    	if($mdl_friend_assist->isAlreadyAssist($userA,$userB)){
    		echo 'already';
    	}else{
    		echo 'not yet';
    	}
    	echo $mdl_friend_assist->assist($userA,$userB);


    	if($mdl_friend_assist->isAlreadyAssist($userA,$userB)){
    		echo 'already';
    	}else{
    		echo 'not yet';
    	}
    	echo $mdl_friend_assist->assist($userA,$userB);

    	echo $mdl_friend_assist->assistCountUpdate($userA);


    	for ($i=0; $i < 10; $i++) { 
    		$mdl_friend_assist->playGame($userA.$i);

    		for ($n=0; $n < 100; $n++) { 
    			$mdl_friend_assist->assist($userA.$i,$userB.$n);
    		}
    	}


    	$leaderboard =  $mdl_friend_assist->leaderboard();

    	$this->dump($leaderboard);


    }

	public function  cut_image_business_action() {

		$business_id =get2('id');

		$mdl_restaurant_menu = $this->loadModel('restaurant_menu');

		$sql ="select * from cc_restaurant_menu where restaurant_id = $business_id and (length(menu_cn_name)>0 or length(menu_en_name)>0 )";
		$data1 = $mdl_restaurant_menu->getListBySql($sql);
		$data =array();

		foreach ($data1 as $key=>$value) {
			$image =$value['menu_pic'];
			$newimage_100 = $this->cut_image( $image, 150, 150, $method = 'fill') ;
			$newimage_300 = $this->cut_image( $image, 300, 300, $method = 'fill') ;
			$data['menu_pic_100'] =$newimage_100;
			$data['menu_pic_300'] =$newimage_300;
			$mdl_restaurant_menu->update($data,$value['id']);


		}




	}

    public function index_action()
    {

//var_dump('aa');exit;

    }

    public function test_referral_rule_action()
    {	
    	
    	
    	$mdl_referral_rule = $this->loadModel(23928);

    	$mdl_referral_rule_application = $this->loadModel('referral_rule_application');


    	$ownerId = 23928;

    	$couponId = 3787;

    	$joinerA = 63391;
    	$joinerB = 23928;
    	$joinerC = 23925;

    	//create
    	$rule1= $mdl_referral_rule->setOwner($ownerId)
    					->setCoupon($couponId)
    					->setRate(0.5)
    					->create();

    	$this->dump($rule1);

    	$rule1 = $mdl_referral_rule->get($rule1['id']);

    	$this->dump($rule1);

    	//update
    	$mdl_referral_rule->updateRuleStatus(mdl_referral_rule::STATUS_DISABLE);

    	$rule1 = $mdl_referral_rule->get($rule1['id']);
    	echo ($rule1['status']==mdl_referral_rule::STATUS_DISABLE)?'PASS':'ERROR';



    	$mdl_referral_rule->updateRuleStatus(mdl_referral_rule::STATUS_LOCK);

    	$rule1 = $mdl_referral_rule->get($rule1['id']);
    	echo ($rule1['status']==mdl_referral_rule::STATUS_LOCK)?'PASS':'ERROR';



    	$mdl_referral_rule->updateRuleStatus(mdl_referral_rule::STATUS_ENABLE);

    	$rule1 = $mdl_referral_rule->get($rule1['id']);
    	echo ($rule1['status']==mdl_referral_rule::STATUS_ENABLE)?'PASS':'ERROR';


    	//Join
    	$mdl_referral_rule->applyRule($rule1['apply_code'],$joinerA);

    	echo "A shoule be in";
    	$this->dump($mdl_referral_rule_application->getUserListOfRule($rule1['id']));


    	$mdl_referral_rule->updateRuleStatus(mdl_referral_rule::STATUS_LOCK);
    	$mdl_referral_rule->applyRule($rule1['apply_code'],$joinerB);

    	echo "B shoule Not be in";
    	$this->dump($mdl_referral_rule_application->getUserListOfRule($rule1['id']));


    	$mdl_referral_rule->updateRuleStatus(mdl_referral_rule::STATUS_DISABLE);
    	$mdl_referral_rule->applyRule($rule1['apply_code'],$joinerC);

    	echo "C shoule Not be in";
    	$this->dump($mdl_referral_rule_application->getUserListOfRule($rule1['id']));


    	$mdl_referral_rule->updateRuleStatus(mdl_referral_rule::STATUS_ENABLE);
    	$mdl_referral_rule->applyRule($rule1['apply_code'],$joinerB);
    	$mdl_referral_rule->applyRule($rule1['apply_code'],$joinerC);

    	echo "A B C shoule All be in";
    	$this->dump($mdl_referral_rule_application->getUserListOfRule($rule1['id']));

    	$mdl_referral_rule->applyRule($rule1['apply_code'],$joinerA);
    	$mdl_referral_rule->applyRule($rule1['apply_code'],$joinerB);
    	$mdl_referral_rule->applyRule($rule1['apply_code'],$joinerC);

    	echo "A B C shoule All be in; No Repeat";
    	$this->dump($mdl_referral_rule_application->getUserListOfRule($rule1['id']));

    	//getUserRuleList
    	
    	$this->dump($mdl_referral_rule_application->getUserRuleList($joinerA));

    	$this->dump($mdl_referral_rule_application->getUserRuleList($joinerB));

    	$this->dump($mdl_referral_rule_application->getUserRuleList($joinerC));

    	//userHasAppliableRuleOnCoupon
    	

    	$this->dump($mdl_referral_rule_application->userHasAppliableRuleOnCoupon($joinerA,$couponId));

    	$this->dump($mdl_referral_rule_application->userHasAppliableRuleOnCoupon($joinerB,$couponId));

    	$this->dump($mdl_referral_rule_application->userHasAppliableRuleOnCoupon($joinerC,$couponId));

    }


    public function test_add_cart_action()
    {
    	$mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');

    	$process = new AddCartProcess();

    	$process->owner(23928); 

    	$process->qty(1)->add(3890);


    	$process = new AddCartProcess();

    	$process->owner(23928); 

    	$process->qty(1)->add(3890,731);



    	$process = new AddCartProcess();

    	$process->owner(23928); 

    	$process->qty(1)->add(3890,732);



    	$process = new AddCartProcess();

    	$process->owner(23928); 

    	$process->qty(1)->add(3489,0,"1132,1129");


    	$process = new AddCartProcess();

    	$process->owner(23928); 

    	$process->qty(1)->add(3489,0,"1132");


    	$process = new AddCartProcess();

    	$process->owner(23928); 

    	$process->qty(1)->add(3489,0,"1129");

    }

    public function product_label_action()
    {	

    	/**
    	 * 每一行打印是显示一页
    	 * 每一页中可以有 1 , 2  或者 4 个产品
    	 */
    	
    	
    	//Rachel List
    	/**
    	$list=[
    		[4437,2950],
    		[4542,4352],
    		[4412,4339],
    		[4324,4385],
    		[4411,4410],
    		[4322,4430],
    		[3907,3679,4357,4351],
    		[4368,4325,4326,4441],
    		[3789,4323,3908,4496],
    		[4468,4371,4368,4543],
    		[4389,4395,3780,4453],
    	];
    	*/
    

    	$list=[[4500],[4499],[4494],[4493],[4488],[4468],[4467],[4542],[4456],[4454],[4453],[4450],[4449],[4445],[4441],[4444],[4434],[4433],[4432],[4431],[4430],[4426],[4421],[4412],[4410],[4409],[4408],[4411],[4452],[4429],[4428],[4404],[4402],[4389],[4332],[4382],[4376],[4374],[4372],[4371],[4368],[4363],[4357],[4351],[4346],[4343],[4339],[4338],[4336],[4334],[4333],[4329],[4324],[4323],[4322],[4321],[4325],[4330],[4396],[4485],[4420],[3922],[3920],[4182],[4183],[3917],[3909],[3789],[3793],[3887],[3895],[3891],[2907],[4478],[4395]];

    	/**
    	 *  Generation Begin
    	 */
    	$mdl_coupon=$this->loadModel('coupons');

    	$htmlContent="";

    	foreach ($list as $key => $value) {
    		if(!is_array($value))continue;

    		$rowSize = sizeof($value);

    		foreach ($value as $k=>$v) {
    			$value[$k]=$mdl_coupon->get($v);
    			$url='http://www.ubonus.com.au/coupon/'.$v;
    			$value[$k]['qrCode']=generateQRCode($url);
    		}

    		$this->setData($value,'couponData');
    		
			$rowHtml=$this->fetch('product_label/row'.$rowSize);

			$this->setData($rowHtml,'rowHtml');

    		$htmlContent.=$this->fetch('product_label/page');
    	}

    	$this->setData($htmlContent,'htmlContent');

    	$this->display('product_label/label');
    }

    public function recover_lost_order_action()
    {
    	$orderId = get2('order_id');

    	$confirm=get2('confirm');

    	$mdl= $this->loadModel('order');

    	if($orderId){
    		//购物车检出记录中有
	    	$sql="SELECT * FROM cc_wj_temp_orderID_carts_backup where orderId = $orderId";

	    	$arr_post = $mdl->getListBySql($sql);

			$arr_post = unserialize(base64_decode($arr_post[0]['temp_arr']));

			$this->dump($arr_post);

			$order = $this->loadModel('order')->getByWhere(array('orderId'=>$orderId));

			if($arr_post&&!$order){
				//有临时数据并且订单没有生成
				echo '可以恢复';
				echo "<a href='".HTTP_ROOT_WWW."/test/recover_lost_order?confirm=confirm&order_id=$orderId'>恢复</a>";

				if($confirm=='confirm'){
					// $this->buy_voucher($arr_post,$orderId);
					// $this->sheader($this->parseUrl()->set('confirm'));
				}
				
			}else{
				echo '已经恢复';
			}
	    	
	    	//可以恢复
	    	
    	}else{
    		//所有未成交订单
    		$sql=" SELECT * FROM `cc_wj_temp_orderID_carts_backup` as b left join cc_order as o on b.orderId=o.orderId where o.id is NULL ";

    		$list = $mdl->getListBySql($sql);

    		$this->setData($list,'unsuccess_checkout_order');
    	}


    	$this->display('recover_lost_order');

    }

    public function redbag_recycle_action()
    {
    	$mdl_recharge= $this->loadModel('recharge');
    	$sql = "SELECT * FROM `cc_recharge`  WHERE payment='redbag' and coupon_name='红包-2018.2.28日前使用，否则收回' and createTime > UNIX_TIMESTAMP('2017-11-1')  ";

    	$list = $mdl_recharge->getListBySql($sql);

    	
    	foreach ($list as $key => $value) {
    		$value['userId'];

    		$sql = " SELECT sum(confirmedMoneyAppliedAmount) as useMoney FROM cc_order WHERE userId='".$value['userId']."' and createTime >UNIX_TIMESTAMP('2017-11-1')";

    		$list = $mdl_recharge->getListBySql($sql);
    		
    		foreach ($value as $k => $v) {
    			if(is_numeric($k))unset($value[$k]);
    		}
    		unset($value['id']);

    		$this->dump($list);
    		//echo $value['userId'];

    		$useMoney = $list[0][0];

    		if($useMoney>0){
    			echo "该用户使用过钱包支付。红包不收回";
    		}else{

    			$value['money']=$value['money']*-1;
    			$value['coupon_name']='逾期未使用，红包系统收回';
    			$value['createTime']=time();

    			$this->loadModel('recharge')->insert($value);
    			echo "逾期未使用，红包系统收回";
    		}
    	}
    }
    public function test_cbaapi_action()
    {	
    	include_once CORE_DIR.'cbaAPI/commonWebAPI.php';

		$testCreditCardGenerator=new TestCreditCardGenerator();
		$testCard = $testCreditCardGenerator->init();

		$commonWebAPI = new CommonWebAPI();
		$commonWebAPI->pay('10021',$testCard,1234.25);
    }

    public function image_action()
    {	
    	/**
    	 * 前段
    	 */
    	//FileReader Localdisplay
    	
    	//JsCrop get cut bound
    	
    	//upload and save
    	//
		$this->display('image');

    }

    public function save_image_action()
    {	

    	//Option
    	$allow_exts = array('jpg', 'jpeg', 'gif', 'png');

    	//上传图片
    	$photoObj = $_FILES['image'];

    	$filepath = date('Y-m');
        $this->file->createdir('data/upload/'.$filepath);

        $filename=date('YmdHis') . $this->createRnd();

        $photo = $this->file->upfile($allow_exts, $photoObj, UPDATE_DIR, $filepath.'/'.$filename);

        $nocut = post('nocut');

        $bounds = post('bounds');
        $bs=explode(',', $bounds);

        if($bs&&!$nocut)
			$this->file->cutByPosBound(UPDATE_DIR.$photo, UPDATE_DIR.$photo, array('x1' => $bs[0], 'y1' => $bs[1], 'x2' => $bs[2], 'y2' => $bs[3]));

		$resize = post('resize');
        $rs=explode(',', $resize);

        if($rs)
			$this->file->resize(UPDATE_DIR . $photo, UPDATE_DIR . $photo, $rs[0], $rs[1], true, false);
		
		//结果路径
		//echo "<img src='".UPLOAD_PATH.$photo."'><br/>".$photo;
		$this->cut_image($UPDATE_DIR . $photo,150,150,'cut');
		$this->cut_image($UPDATE_DIR . $photo,66,66,'cut');
		
		echo $photo;
    }
	
	
	public function gen_image_file_from_barcode_web_action(){
		
		$images='https://d1ralsognjng37.cloudfront.net/76b46ef7-0508-4907-9511-539b9d325886.jpeg';
		//$filepath = date('Y-m');
       // $this->file->createdir('data/upload/'.$filepath);
		
		
		$pic_arr = $this->gen_image_file_from_barcode_web($images,$filepath);
		var_dump($pic_arr);exit;
		
	}

    public function test_cipher_action()
    {	
    	$userId = 23928;

    	$mdl = $this->loadModel('user');

    	$cardData=array('5123450000000008','04','21','225');

    	$mdl->save_card($cardData,$userId);

    	$data = $mdl->get_card($userId);

    	var_dump($data);

    	
    }
	
	public function test_search_action() {
		
			 $this->display('mobile/example');
	}
	
public function scan_barcode_action(){
	
	 $this->display('html5-qrcode-master/examples/html5/index');
	
}

  public function  show_jd_action()
  {
	  
	  $this->display('www/index1');
	  
	  }

    public function clean_upload_action()
    {
    	/**
    	 * File from Database
    	 */
    	//user.avatar
    	//user.pic
    	//user.pics
    	//user.logo
    	
    	// coupon.pic
    	// coupon.pics


    	/**
    	 * File from folder
    	 */
    	
    	/**
    	 * Mark files that is not in the databse as rubbish
    	 */
    	
    	/**
    	 * remove those file
    	 */
    	$this->sheader(null);
    }

    public function test_email_action()
    {
    	echo $this->loadModel('system_mail_template')->customerOrderNotification('20200413073242230562  ','en',$this->lang);
    }

    public function sms_action()
    {
    	send_sms('61433813332','this is a test sms from ubonus');
    }

    public function test_validation_action()
    {
    	$m=$this->loadModel('reg');

    	
		// var_dump($m->chkMail());

		// var_dump($m->chkUrl());

		// var_dump($m->chkUsername());

		// var_dump($m->chkPassword());

		var_dump($m->chkPhone('0123456789')==true);
		var_dump($m->chkPhone('adsfasdf123456789')==false);
		var_dump($m->chkPhone('123456789asdfasdf')==false);
		var_dump($m->chkPhone('123asdfasdf456789')==false);
		var_dump($m->chkPhone('12asdfasdf345asdfasdf6789')==false);
		var_dump($m->chkPhone('asdfasdf')==false);

		// var_dump($m->chkColor());
    }

    public function promotion_voucher_center_action()
    {	
	
	   $storeList[0]='Box Hill Tea royale奶茶店';
	   
	   
    //	$storeList[0]='City 初茶 奶茶店';
    //	$storeList[1]='City 亲爱哒麻辣烫';
    	// $storeList[2]='Glen 初茶'; //Disable
    //	$storeList[3]='City Cha Co（茶咖）奶茶店';
    //	$storeList[4]='City 厕所串串';
    //	$storeList[5]='City 外婆小面';
    //	$storeList[6]='City 一牛生活火锅店';
    //	$storeList[7]='Box Hill 牛霸爸火锅';
    //	$storeList[8]='Box Hill 得蚨香麻辣小龙虾';
    //	$storeList[9]='Box Hill Tea royale奶茶店';

    	//$storeList[10]='Hawthorn 吃货村';
    	//$storeList[11]='BOX HILL 四川经典酒楼';
    	//$storeList[12]='Chadstone 潮牛';
    	//$storeList[13]='Chadstone 妈妈卤味厨房';
    	//$storeList[14]='CITY 麻辣诱惑';
    	//$storeList[15]='DOCKLANDS 港口海鲜大世界';
    	//$storeList[16]='DOCKLANDS The Conder ';
    	//$storeList[17]='DOCKLANDS  Waterfront海港城自助海鲜火锅';
    	//$storeList[18]='City 东京日料';
    	//$storeList[19]='Glen waverley 同德干锅鸭头';
    	//$storeList[20]='Box Hill 云故乡';
    	

    	$this->setData($storeList,'storeList');


    	$lastDay = time()-3600*24;

    	$sql = "SELECT storeId, count(id) as total FROM `promotion_voucher` where createtime >$lastDay group by storeId order by total desc";

    	$result = $this->loadModel('coupons')->getListBySql($sql);

    	$orderList=array();
    	foreach ($result as $key => $value) {
    		$orderList[$value['storeId']]=$value['total'];
    	}

    	foreach ($storeList as $key => $value) {
    		if(isset($orderList[$key]))continue;
    		$orderList[$key]='0';
    	}

    	unset($orderList[2]);//Disable 初茶

    	$this->setData($orderList,'orderList');

    	$this->setData($this->loadModel('info')->getListByClass('114',10,'ordinal'),'bannerData');


    	$this->setData('Cityb2b','pageTitle');
    	$this->display('promotion_voucher_center');
    }


    public function promotion_voucher_express_action()
    {	
    	$storeList[0]='Box Hill Tea royale奶茶店';
		/*
		$storeList[0]='City 初茶 奶茶店';
    	$storeList[1]='City 亲爱哒麻辣烫';
    	$storeList[2]='Glen 初茶'; //Disable
    	$storeList[3]='City Cha Co（茶咖）奶茶店';
    	$storeList[4]='City 厕所串串';
    	$storeList[5]='City 外婆小面';
    	$storeList[6]='City 一牛生活火锅店';
    	$storeList[7]='Box Hill 牛霸爸火锅';
    	$storeList[8]='Box Hill 得蚨香麻辣小龙虾';
    	$storeList[9]='Box Hill Tea royale奶茶店';

    	$storeList[10]='Hawthorn 吃货村';
    	$storeList[11]='BOX HILL 四川经典酒楼';
    	$storeList[12]='Chadstone 潮牛';
    	$storeList[13]='Chadstone 妈妈卤味厨房';
    	$storeList[14]='CITY 麻辣诱惑';
    	$storeList[15]='DOCKLANDS 港口海鲜大世界';
    	$storeList[16]='DOCKLANDS The Conder ';
    	$storeList[17]='DOCKLANDS  Waterfront海港城自助海鲜火锅';
    	$storeList[18]='City 东京日料';
    	$storeList[19]='Glen waverley 同德干锅鸭头';
    	$storeList[20]='Box Hill 云故乡';
*/
    	/**
    	 * 
    	 */
    	$storeId = get2('storeId');

    	$ua =$this->getUserDevice();

    	$openId=$this->wx_openID;

		if ($ua!='wechat') $this->sheader(null,'该功能只能在微信中使用');

		if(!$openId){
			$new_url =HTTP_ROOT_WX."test/promotion_voucher_express?storeId=".$storeId;
			$query = array(
				'appid' => 'wx8320e8511d65c1b4',
				'redirect_uri' =>$new_url,
				'response_type' => 'code',
				'scope' => 'snsapi_userinfo',
				'state' => 1
			);
		
			$query = http_build_query( $query );
			$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?'.$query.'#wechat_redirect';
			$this->sheader( $url );
		}else{
			$mdl_promotion_voucher=$this->loadModel('promotion_voucher');

			$user =$mdl_promotion_voucher->openId_exist($openId);

			if(!$user){
				$data['storeId']=$storeId;
				$data['openId']=$openId;
				$data['createtime']=time();

				require_once "wx/wxjssdk.php";
				$userinfo = getUserInfor( $openId );
				if($userinfo!='unsuscribe'){
					$data['firstname']=$userinfo['nickname'];
				}

	    		$id=$mdl_promotion_voucher->insert($data);

	    		$user = $mdl_promotion_voucher->get($id);
			}else{
				if($storeId!=$user['storeId']){
					$msg="本活动只能参加一次,谢谢参与！";
					$this->setData($msg,'errorMsg');
				}
			}
			
			$storeName=$storeList[$user['storeId']];
			$this->setData($storeName,'storeName');

			$this->setData($storeId,'storeId');

			if($storeId==0){
				$this->setData('images/promotion_voucher_qrcode0.jpeg','qrcode');
				$this->setData($storeName."-送奖励",'success_msg');
			}elseif($storeId==1){
				$this->setData('images/promotion_voucher_qrcode1.jpeg','qrcode');
				$this->setData($storeName."-送价值$4饮料",'success_msg');
			}elseif($storeId==2){
				$this->setData('images/promotion_voucher_qrcode2.jpeg','qrcode');
				$this->setData($storeName."-活动已经结束",'success_msg');
			}elseif($storeId==3){
				$this->setData('images/promotion_voucher_qrcode3.jpeg','qrcode');
				$this->setData($storeName."-送小杯咖啡",'success_msg');
			}elseif($storeId==4){
				$this->setData('images/promotion_voucher_qrcode3.jpeg','qrcode');
				$this->setData($storeName."-送饮料",'success_msg');
			}elseif($storeId==5){
				$this->setData('images/promotion_voucher_qrcode3.jpeg','qrcode');
				$this->setData($storeName."-送饮料",'success_msg');
			}elseif($storeId==6){
				$this->setData('images/promotion_voucher_qrcode3.jpeg','qrcode');
				$this->setData($storeName."-送饮料",'success_msg');
			}elseif($storeId==7){
				$this->setData('images/promotion_voucher_qrcode3.jpeg','qrcode');
				$this->setData($storeName."-送饮料",'success_msg');
			}elseif($storeId==8){
				$this->setData('images/promotion_voucher_qrcode3.jpeg','qrcode');
				$this->setData($storeName."-送价值4元的饮料",'success_msg');
			}elseif($storeId==9){
				$this->setData('images/promotion_voucher_qrcode3.jpeg','qrcode');
				$this->setData($storeName."-立减2刀",'success_msg');
			}elseif($storeId==10){
				$this->setData('images/promotion_voucher_qrcode3.jpeg','qrcode');
				$this->setData($storeName."-送饮料",'success_msg');
			}elseif($storeId==11){
				$this->setData('images/promotion_voucher_qrcode3.jpeg','qrcode');
				$this->setData($storeName."-送饮料",'success_msg');
			}elseif($storeId==12){
				$this->setData('images/promotion_voucher_qrcode3.jpeg','qrcode');
				$this->setData($storeName."-送饮料",'success_msg');
			}elseif($storeId==13){
				$this->setData('images/promotion_voucher_qrcode3.jpeg','qrcode');
				$this->setData($storeName."-送饮料",'success_msg');
			}elseif($storeId==14){
				$this->setData('images/promotion_voucher_qrcode3.jpeg','qrcode');
				$this->setData($storeName."-送饮料",'success_msg');
			}elseif($storeId==15){
				$this->setData('images/promotion_voucher_qrcode3.jpeg','qrcode');
				$this->setData($storeName."-送饮料",'success_msg');
			}elseif($storeId==16){
				$this->setData('images/promotion_voucher_qrcode3.jpeg','qrcode');
				$this->setData($storeName."-送饮料",'success_msg');
			}elseif($storeId==17){
				$this->setData('images/promotion_voucher_qrcode3.jpeg','qrcode');
				$this->setData($storeName."-送饮料",'success_msg');
			}elseif($storeId==18){
				$this->setData('images/promotion_voucher_qrcode3.jpeg','qrcode');
				$this->setData($storeName."-送饮料",'success_msg');
			}elseif($storeId==19){
				$this->setData('images/promotion_voucher_qrcode3.jpeg','qrcode');
				$this->setData($storeName."-送饮料",'success_msg');
			}elseif($storeId==20){
				$this->setData('images/promotion_voucher_qrcode3.jpeg','qrcode');
				$this->setData($storeName."-送饮料",'success_msg');
			}

			$exipre_limit =180;


			$expire_msg=date("Y年m月d日 h:i:s A",$user['createtime']+$exipre_limit);

			if(time()>$user['createtime']+$exipre_limit){
				$expire_msg.='<span style="color:#f30">已过期<span>';
			}
			$this->setData($expire_msg,'expire_msg');
			


			$this->setData($user,'user');

	    	$mdl_coupon= $this->loadModel('coupons');

	    	$sql ="SELECT c.coupon_summery_description,e.isStore,c.createUserId,c.id, c.title, c.pic, c.hits, c.voucher_deal_amount,c.voucher_original_amount,c.bonusType,c.categoryId,e.panaltype,c.city FROM cc_explosion e LEFT JOIN cc_coupons c ON c.id=e.couponid WHERE c.isapproved=1 and c.status=4 AND !(c.autoOffline=1 AND ('$currentTime'<c.startTime or '$currentTime'>c.endTime)) AND e.panaltype=20  ORDER  BY sort";

	    	$list = $mdl_coupon->getListBySql($sql);

	    	foreach ($list as $key => $value) {
	    		$mdl_coupon->caculatePriceAndPoint($list[$key]);
	    	}

	    	$this->setData($list,'list');

	    	$this->display('promotion_voucher_success');
		}
    }


    //DELETE t1 FROM promotion_voucher t1, promotion_voucher t2 WHERE t1.id < t2.id AND t1.openId = t2.openId;
    //SET @newid=0;UPDATE promotion_voucher SET id=(@newid:=@newid+1) ORDER BY id;
    //
    //SELECT * ,from_unixtime(createtime) FROM promotion_voucher GROUP BY openId ORDER by id
    //SET @newid=0;SELECT (@newid:=@newid+1),openId ,from_unixtime(createtime) FROM promotion_voucher where storeId=0 GROUP BY openId ORDER by id

    public function promotion_voucher_result_action()
    {
    	$storeId = get2(storeId);
    	$sql = "SELECT openId ,from_unixtime(createtime) as t FROM promotion_voucher where storeId=$storeId GROUP BY openId ORDER by createtime desc";
    	$result = $this->loadModel('coupons')->getListBySql($sql);
    	$this->setData($result,'list');
    	$this->display('promotion_voucher_result');
    }

    public function promotion_voucher_action()
    {	
    	
    	$ua =$this->getUserDevice();

    	$openId=$this->wx_openID;

		if ($ua!='wechat') $this->sheader(null,'该功能只能在微信中使用');

		if(!$openId){
			$new_url =HTTP_ROOT_WX."test/promotion_voucher";
			$query = array(
				'appid' => 'wx8320e8511d65c1b4',
				'redirect_uri' =>$new_url,
				'response_type' => 'code',
				'scope' => 'snsapi_userinfo',
				'state' => 1
			);
		
			$query = http_build_query( $query );
			$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?'.$query.'#wechat_redirect';
			$this->sheader( $url );
		}else{
			$openId;
			$mdl_promotion_voucher=$this->loadModel('promotion_voucher');

			$user =$mdl_promotion_voucher->openId_exist($openId);

			if($user){
				$this->sheader(HTTP_ROOT_WX.'test/promotion_voucher_success'.'?id='.$user['id'].'&hash='.md5($user['openId']));
			}else{
				$this->setData($openId,'openId');
				$this->display('promotion_voucher');
			}
		}
    }

	public function md5_action(){
		
		$init_pass =trim(get2('pass'));
		$md5_init_pass=md5($init_pass);
		var_dump($md5_init_pass);exit;
	}
	
	// 将coupon 的city 属性和 行业分类属性放到coupon的相应字段种
	// coupon cityName , coupon  categoryName 
	// city 只选择最底层的城市  Name 只选择最底层的城市 
	public function addcityandcategoryname_action(){
		
		$mdl_coupon =$this->loadModel("coupons");
		$mdl_infoclass=$this->loadModel('infoClass');
		$mdl_city=$this->loadModel('city');
		
		$sql = "select * from cc_coupons   ";
	    
		$coupon = $mdl_coupon->getListBySql($sql);
		
		
		
		if ($coupon) {
			
		 foreach($coupon as $key => $value1){
		
				// 获取该产品的类别信息
		$categoryInfo =explode(",",$value1['categoryId']);
	
		foreach ($categoryInfo as $key => $value) { 
			if($value) {
				$categoryname_record= $mdl_infoclass->get($value);
				$category_name .=$categoryname_record['name'].' ';
			}
	
		}
		//var_dump($category_name);
		//$category_name=implode(" ",$category_name);
		
		
		// 获取该产品的地区及城市信息
		$cityInfo =explode(",",$value1['city']);
	
		foreach ($cityInfo as $key => $value) { 
			if($value) {
				$cityname_record= $mdl_city->get($value);
				// 将国家地区去掉,并且先显示地区后显示城市
				if($cityname_record['city_level']) {
				  $city_name =$cityname_record['name'].' '.$city_name;
				}
			}
	
		}
			
		//var_dump($city_name);exit;	
			$data = array(
			  'categoryName'=>$category_name,
			  'cityName'=>$city_name
			);
			//var_dump($data);exit;
			$mdl_coupon->update($data,$value1['id']);
				 $category_name='';
			 $city_name='';
		}
		
		}
	}
	public function replace_restaurant_menu_picture_action() {
		
		/* 在已经存在的图片库中找菜单名称相同的图片 ,替换掉目前还没有图片的菜单. */
		/* 查询规则位100%匹配 */
		
		
		
		$restaurant_id = (int)get2( 'restaurant_id' );
		$match_rates=(int)get2( 'match_rates' );
		
		
		$source_restaurant_id =(int)get2( 'source_restaurant_id' );
		$mdl_restaurant_menu = $this->loadModel( 'restaurant_menu' );
		
		$sql ="select * from cc_restaurant_menu where restaurant_id =".$restaurant_id;
		$rest_record =$mdl_restaurant_menu->getListBySql($sql);
		if($rest_record) {
			
			foreach ($rest_record as $key => $value) { 
			  
			  
			    //判断图片文件的尺寸是否为0 
				$filepath_name= 'data/upload/'.$value['menu_pic'];
				$is_file =is_file($filepath_name);
				$menu_name=$value['menu_cn_name'];
			    if ($is_file) {
					var_dump('yes--'.$menu_name.' '.$filepath_name .'<br><br>');
				}else{
					
					$search_menu_name = trim($menu_name);
				
					if($search_menu_name) {
							var_dump('寻找匹配 '. $search_menu_name.' 图片.....<br>');
						if($source_restaurant_id){
							$sql1="select * from cc_restaurant_menu where restaurant_id=".$source_restaurant_id. " and  trim(menu_cn_name) = '".$search_menu_name."' union ";
					
							$sql1 .=" select * from cc_restaurant_menu where restaurant_id=".$source_restaurant_id. " and  trim(menu_cn_name) like '%".$search_menu_name."%'";
						}else{
							$sql1="select * from cc_restaurant_menu where trim(menu_cn_name)='".$search_menu_name."' union ";
							$sql1 .=" select * from cc_restaurant_menu where trim(menu_cn_name) like '%".$search_menu_name."%'  ";
						}
						
						$match_menu= $mdl_restaurant_menu->getListBySql($sql1);
						if($match_menu) {
							$count=count($match_menu);
							var_dump('共查找到'.$count.'个  相同的名称.....<br><br>');
							foreach ($match_menu as $key1 => $value1) { 
								$filepath_name1= 'data/upload/'.$value1['menu_pic'];
								$is_file1 =is_file($filepath_name1);
								
								if ($is_file1) {
									
									var_dump('终于找到啦.....<br><br><br><br>');
									// 开始替换
									$data=array(
									  'menu_pic'=>$value1['menu_pic'],
									  'menu_desc'=>$value['menu_desc']. ' 图片仅供参考',
									);
									if($mdl_restaurant_menu->update($data,$value['id'])){
										
										var_dump('成功修改.....<br><br><br><br>');
									}
									break;
								}else{
									//var_dump($filepath_name1.' 检查中,未找到!<BR>');
								}
							
							}
							
							// 判断一下这些名称的记录有没有图片? 有才可以启用.
							
						}else{
							// 是否进行模糊查找并替换?
							var_dump('暂时未寻找到匹配图片.....<br>');
						}
						}
					}
					
			
			
			
			}
			
		}else {
			
			var_dump('数据为空');exit;
		}
		
	}
	
	
    public function promotion_voucher_register_action()
    {
    	if(is_post()){
    		$firstname=trim(post('firstname'));
    		$lastname=trim(post('lastname'));
    		$email=trim(post('email'));
    		$mobile=trim(post('mobile'));
    		$openId=trim(post('openId'));

    		//首次用户自动注册
    		$mdl_user = $this->loadModel( 'user' );

    		try {
    			if(!$mdl_user->getByWhere(array('wx_openID'=>$openId))){
		    		$initPassowrd = $this->createRnd();

					$userObject = new User();
					$userObject->setOpenID($openId);
					$userObject->setPassword($this->md5($initPassowrd));
					$userObject->setInitPassowrd($initPassowrd);

					require_once "wx/wxjssdk.php";
					$userinfo = getUserInfor( $openId );
					if($userinfo!='unsuscribe'){
						$userObject->setAvater($this->saveWxAvater($userinfo["headimgurl"]));
						$userObject->setNickName($userinfo['nickname']);
					}

					$username='User';
					while($mdl_user->getCount( "name='$username'" ) > 0) {
						$randnumber =rand(100,999);
						$username .=$randnumber; // append 3 digit until a new one
					}
					
					$userObject->setName($username);

					$userObject->setFullName($firstname,$lastname);
					$userObject->setEmail($email);
					$userObject->setBusinessMobile($mobile);

					$new_id=$mdl_user->insert($userObject->toDBArray());

					//possible notify
				}
    		} catch (Exception $e) {
    			//if error do nothing.
    		}


    		$mdl_promotion_voucher=$this->loadModel('promotion_voucher');

    		$user =$mdl_promotion_voucher->openId_exist($openId);

    		if($user){
				$this->sheader(HTTP_ROOT_WX.'test/promotion_voucher_success'.'?id='.$user['id'].'&hash='.md5($user['openId']));
			}else{
				$data['firstname']=$firstname;
				$data['lastname']=$lastname;
				$data['email']=$email;
				$data['mobile']=$mobile;
				$data['openId']=$openId;
				$data['createtime']=time();


	    		$id = $mdl_promotion_voucher->insert($data);

	    		$user = $mdl_promotion_voucher->get($id);

	    		$this->sheader(HTTP_ROOT_WX.'test/promotion_voucher_success'.'?id='.$user['id'].'&hash='.md5($user['openId']));
			}
    	}
    }

	public function write_test_action(){
		$filename = DATA_DIR.'upload/htm/restaurant/menu_3333.htm';
		$file =new file();
		if(!is_file($filename)){
			if(!$file->createfile($filename,'xieru chenggong ' )) {
				var_dump('不能写入');exit;
			}else{
				var_dump('写入成功');exit;
			}
		}else{
			var_dump('文件存在不操作');exit;
		}
	 }

    public function promotion_voucher_success_action()
    {	

    	$id=get2('id');
    	$openId_hash = get2('hash');

    	$mdl_promotion_voucher=$this->loadModel('promotion_voucher');

    	$user = $mdl_promotion_voucher->get($id);

    	if(md5($user['openId'])!=$openId_hash)$user=null;

    	$this->setData($user,'user');


    	$mdl_coupon= $this->loadModel('coupons');

    	$where[] = " (id in (4943,4938,4937,4924)) ";

    	$sql ="SELECT c.coupon_summery_description,e.isStore,c.createUserId,c.id, c.title, c.pic, c.hits, c.voucher_deal_amount,c.voucher_original_amount,c.bonusType,c.categoryId,e.panaltype,c.city FROM cc_explosion e LEFT JOIN cc_coupons c ON c.id=e.couponid WHERE c.isapproved=1 and c.status=4 AND !(c.autoOffline=1 AND ('$currentTime'<c.startTime or '$currentTime'>c.endTime)) AND e.panaltype=20  ORDER  BY sort";

    	$list = $mdl_coupon->getListBySql($sql);

    	foreach ($list as $key => $value) {
    		$mdl_coupon->caculatePriceAndPoint($list[$key]);
    	}

    	$this->setData($list,'list');

    	$this->display('promotion_voucher_success');
    }

    public function poster_generate_action()
    {
    	$imageprocess = $this->loadModel('imageprocess');
    	$imageprocess->test();
    	$t = $imageprocess->loadTemplate();
    	var_dump($t);
    } 

    public function fourpx_action()
    {
    	require_once( DOC_DIR.'static/4pxAPI.php' );
    	$fourpx = new FourpxAPI();
    	// $fourpx->enableSandBox(true);
    	$res = $fourpx->getAusPostTrackingId('OC9175162006170003');
    	echo $res;
    }

    public function oproute_action()
    {
    	require_once( DOC_DIR.'static/OptimoRouteAPI.php');
    	$opRouteAPI = new OptimoRouteAPI();

    	require_once( DOC_DIR.'static/OptimoRoute.php');
    	$opRoute = new OptimoRoute();
    	
    	$testingDate = '2020-08-02';
    	echo "<pre>";
    	// Step1 sync up
    	// $opRoute->syncOrderOnDate($testingDate);

    	// Step2 trigger planning
    	// $data = $opRouteAPI->startPlanning($testingDate);
    	// $status = $opRouteAPI->getPlanningStatus($data->planningId);  
    	// //N - New R - Running C - Cancelled by the user F - Finished E - Error occurred
    	// var_dump($data, $status->status);

    	// Step3 sync down
    	//$opRoute->syncRoutesDownOnDeliverDate($testingDate);
    }
    
    public function phpinfo_action()
    {
    	phpinfo();
    }
	
	 public function proppie_action()
    {
    	$this->display('proppie/index');
    }
	
	 public function change_chaoshi_menu_to_english_action()
    {
    	$business_id = get2('business_id');
		$coupon_id=get2('coupon_id');
		
		if(!$business_id) {
			 var_dump( '商家号为空！');exit;
		}
		if(!$coupon_id) {
			var_dump( '产品号为空！');exit;
		}
		//打开销售记录
		$mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');
		
		//逐行替换，并置标志 将中文替换城 菜单编号+英文标题 中文标题在后面。 然后置一个标志 在sub_bouns_code中
		
		$update_sql ="update  `cc_wj_customer_coupon`  a 
			set `sub_bouns_id_code`=1,
			`bonus_title` = concat(

			(select concat('(',menu_id,')|',menu_en_name)  as group_idx 
			from cc_restaurant_menu where menu_cn_name=bonus_title and restaurant_id = " .$business_id.    " limit 1 )



			,' | ',`bonus_title` )

			WHERE `bonus_id`= " .$coupon_id.    " and business_id= " .$business_id.    " and `sub_bouns_id_code`=0";
		
		//var_dump($update_sql);exit;
		$mdl_wj_customer_coupon->getListBySql($update_sql);
		//提示成功
		
    }
	
	
	
	
		

	
	
	/**
     * 導出Excel數據表格
     * @param  array    $dataList     要導出的數組格式的數據
     * @param  array    $headList     導出的Excel數據第一列表頭
     * @param  string   $fileName     輸出Excel表格文件名
     * @param  string   $exportUrl    直接輸出到瀏覽器or輸出到指定路徑文件下
     * @return bool|false|string
     */
   
   // public function importCsv_action($fileName, $line=0, $offset=0){
	   public function test_import_csv_action(){
		   $line=0;
		    $offset=0;
        //set_time_limit(0);//防止超時
        //ini_set("memory_limit", "512M");//防止內存溢出
		$root_dir = $_SERVER['DOCUMENT_ROOT'];
		$filename = $root_dir.'/data/upload/htm/sample.csv';
		if (file_exists($filename)) {
			
			//var_dump('exist: '. $filename);
		}else{
			var_dump(' not exist');exit;
			
		}
		//var_dump($filename);exit;
		
		ini_set('track_errors', 1);
		 $handle = fopen($filename,'r');
			if ( !$handle ) {
			  var_dump( 'fopen failed. reason: '.$php_errormsg);exit;
			}
					
       
		
		//var_dump($handle);exit;
        if(!$handle){
            var_dump(  '文件打開失敗');
        }else{
			//   var_dump(  '文件打開成功');
		}

        $i = 0;
        $j = 0;
        $arr = [];
        while($data = fgetcsv($handle)){
			//var_dump($data);exit;
            //小於偏移量則不讀取,但$i仍然需要自增
            if($i < $offset && $offset){
                $i++;
                continue;
            }
            //大於讀取行數則退出
            if($i > $line && $line){
                break;
            }

            foreach ($data as $key => $value) {
				//var_dump($value);exit;
				
                $content = mb_convert_encoding($value, 'utf-8', 'gbk'); //iconv("gbk","utf-8//IGNORE",$value);//轉化編碼
                $arr[$j][] = $content;
            }
            $i++;
            $j++;
        }
   //   var_dump($arr[1]);
	  
	  $mdl_restaurant_menu= $this->loadModel('restaurant_menu');
	  $index=0;
	   foreach ($arr as $key => $value) {
		   if ($key==0 )continue;
		   $where = array(
		      'restaurant_id'=>'217005',
			  'menu_id'=>$value[1]
		   
		   );
		   
		   $data=array();
		   if($value[6]==0) {
			   $data['visible']=0;
		   }else{
			    $data['visible']=1;
		   }
		   $data['price']=$value[5];
		   $data['menu_en_name']=$value[3];
		   $data['qty']=$value[6];
		   $data['unit']=$value[4];
		   $data['menu_cn_name']=$value[2];
		   
		   //var_dump($data);exit;
		   $arr_no_find=array();
			if(!$mdl_restaurant_menu->getByWhere($where)){
				$arr_no_find[$index] =$value[1].'/'.$value[2].'/'.$value[3].'/'.$value[4].'/'.$value[5].'/'.$value[6].'/';
				$index +=1;
			 
			}
		   
		   if(!$mdl_restaurant_menu->updateByWhere($data,$where)){
			   var_dump('修改出错');exit;
		   }
		   if ( $arr_no_find)  var_dump($arr_no_find);
		  
		   
		   
	   }
	  
    }
	
	public function import_fresh_menu_action() {
		
		 $this->display('VegetableMarket/template_mobile/product');
		
		
	}

//导入fresh的菜单
	public function send_import_xls_action() {
		
	header("Content-type: text/html;charset=utf8_general_ci");
		   $array_menu_price =post('ProductList');
		  $array_menu_price=stripslashes($array_menu_price);

          $array_menu_price = json_decode($array_menu_price,true);

		   if($array_menu_price) {
			   
			     $result['message']='数据取出成功';
		   }else{
			     $result['message']='数据未取出';
			   
		   }
		   		  
			$mdl_restaurant_menu= $this->loadModel('restaurant_menu');
	       $index=0;	  
		   foreach ($array_menu_price as $key => $value) {
			  $where = array(
		      'restaurant_id'=>$this->loginUser['id'],
			  'menu_id'=>$value['CODE']
		   
		   );
		   
		   $data=array();
		   if($value['CurrentQuantity']==0) {
			   $data['visible']=0;
		   }else{
			    $data['visible']=1;
		   }
		   $data['price']=$value['NowPrice'];
		   $data['menu_en_name']=$value['ProductName2']."(".$value['Units'].")";
		   $data['qty']=$value['CurrentQuantity'];
		   $data['unit']=$value['Units'];
		   $data['menu_cn_name']=$value['CODE'].'-'.$value['ProductName1']."(".$value['ProductName2'].") ".$value['Units'];
		   
		   //var_dump($data);exit;
		  
			  $arry_no_find=array();
			  $index =0;
			 if(!$mdl_restaurant_menu->getByWhere($where)){
				$arr_no_find [index] =$value['CODE'].'/'.$value['ProductName1'].'/'.$value['ProductName2'].'/'.$value['Units'].'/'.$value['NowPrice'].'/'.$value['CurrentQuantity'].'/';
				$index ++;
			 
			}  
			  //$result['message']=$value['ProductName1'];
			   //$result['message']=$value['CODE'];
			  if(!$mdl_restaurant_menu->updateByWhere($data,$where)){
			     //$result['message']='数据未取出';
			   
		   }  
			   
			   
			 if ( $arr_no_find) {
				 $result['result']=json_encode($arr_no_find);
				 
			 }else{
				  $result['result']='数据全部处理完成';
				 
			 }  
			   
		   } 
			    // $result['message']='数据未取出';
			   
        //    $result['result']=$array_menu_price;
             
             echo json_encode($result);
	}
	
	public function order_address_split_action() {
		header("Content-type: text/html;charset=utf8_general_ci");
		   $arry_order_address_data =post('ProductList');
		  $arry_order_address_data=stripslashes($arry_order_address_data);

          $arry_order_address_data = json_decode($arry_order_address_data,true);

		   if($arry_order_address_data) {
			   
			     $result['message']='数据取出成功';
		   }else{
			     $result['message']='数据未取出';
			   
		   }
		   		  
			$mdl_order= $this->loadModel('order');
		   foreach ($arry_order_address_data as $key => $value) {
			  
			 if($phone_old ==$value['phone']) continue;
			  
			  $phone_old = $value['phone'];
			  if(strlen(trim($phone_old))==9)  {
				  $phone = '0'.$phone_old;
			  }
			  $where = array(
		      'business_userId'=>'217005',
			  'phone'=>$phone
		   
		   );
			   $data=array();
			  
			   $data['street']=$value['street'];
			   $data['city']=$value['city'];
			   $data['postalcode']=$value['postalcode'];
			   $data['address']=$value['street'].', '.$value['city']." VIC ".$value['postalcode'].", Australia";
			   $data['state']='VIC';
			   //21 Sonia St, Ringwood VIC 3134, Australia
			   //var_dump($data);exit;
			  if(!$mdl_order->updateByWhere($data,$where)){
			     //$result['message']='数据未取出';
			   
		   }  
		   } 
			    // $result['message']='数据未取出';
            $result['result']=$arry_order_address_data;
             
             echo json_encode($result);
	}

	public function disp_center_action()
	{
		$this->loadModel('freshfood_disp_suppliers_schedule');

		$blist = DispCenter::getDispCenterList();

		echo "<pre>";

		var_dump($blist);

		echo DispCenter::getDispCenterIdOfSupplier(217005);

		$date = DispCenter::getAvailableDeliverDateOfBusiness();

		var_dump($date);

		$date = DispCenter::getNextNAvailableDeliverDate();

		foreach ($date as $d) {
			echo date('l', $d);
			echo '<br/>';
		}

		$business = DispCenter::getAvailableBusinessForDeliverDate(time(),'anytime');

		var_dump($business);

		$businessDispSchedule = DispCenter::getBusinessDispSchedule('217093');
		var_dump($businessDispSchedule);
	}

	public function freshxapi_action()
	{
		require_once( DOC_DIR.'static/FreshXApi.php');
		echo "<pre>";
		$fx = new FreshXApi();
		$fx->login();

		// $fx->refreshTocken();

		// $list =  $fx->getProductList();
		// var_dump($list);

		$orderId =  $fx->createOrder();
		var_dump($orderId);
		$order = $fx->getOrder($orderId );
		var_dump($order);
		// $fx->updateOrder($orderId , []);
		// $fx->getOrder($orderId );
		
		// $orderList = $fx->getOrderList();
		// var_dump($orderList);

	}

	public function deliver_date_order_count_action()
	{
		$this->loadModel('freshfood_disp_suppliers_schedule');
		$data =loadModel('order')->getCount([
			'coupon_status' => 'c01',
			'logistic_delivery_date' => '1596664800'
		]);
		var_dump($data >= 1 , DispCenter::isOverMaxDailyOrderLimit('1596664800'));
	}
	
	
	public function test_write_yunying_date_action() {
		
		   $orderId= '20200822142442314239';
		//	$orderId=date( 'YmdHis' ).$this->createRnd();
		$phone00= '0425616988';
		$arr_post_yunying =array(
			'orderId'=>$orderId,
			'phone'=>$phone00,
			'userId'=>'21369'
	);
		
			// 这里为运营提供第一个数据记录，就是一旦基础的输入信息检测通过，则写入数据库。 这个可能会产生不少信息，比如后面的信息一旦有问题，会重写，但是， 如果这个用户在这天下了单，就不去调查，如果该用户没有下成单，则需要进行追查。
		
		// 当前已经把用户信息存到表种， 如果一切正常到下面再把订单信息补进来， 如果 这个过程中间断掉了， 可能出现很多问题， 我这样，一会呢， 后面加一个错误号，这样，基础信息过后，就有错误号告诉客服原因。
		
		// 这个号， 字段上增加一个错误号的处理。
		
		$mdl_wj_temp_orderID_carts_for_yunying  =$this->loadModel('wj_temp_orderID_carts_for_yunying')->save_temp_data($arr_post_yunying,$orderId);;
		
					
			
	}
	
	
	
	public function import_yeeyidata1_action() {
		
		header("Content-type: text/html;charset=utf8_general_ci");
		$newuser =post('newusers');
		$newuser=stripslashes($newuser);

		$newuser = json_decode($newuser,true);

		if($newuser) {
			
			$result['message']='数据取出成功';
		}else{
			$result['message']='数据未取出';
			
			
			
		}
		
		/*if($this->loginUser['id'] !=25201) {
				$result['message']='no access';
		
				echo json_encode($result);
			   exit;
		} */
		
		$mdl_user= $this->loadModel('user');
		
		//$mdl_user->begin();
		
        //清除之前的postcode数据
		$where = array(
			'business_userId'=>$this->loginUser['id']
		);
		
	
		
           //分别写入每行的新postcode数据
		   
	   $missing_count =0;
	   $insert_count =0;
		
		$data=array();
		
		$samecount =0;
		
		foreach ($newuser as $key => $value) {
			
			
			$name=$value['name'];
			$phone='0'.$value['phone'];
			
			
		
			$userObject = new User();
			
			$userObject->setNickName($name);
			
			$userObject->setName($phone);
			
			
			$password=rand(100000,999999);
			$passwordByCustomMd5 = $this->md5( $password );
			$userObject->setPassword($passwordByCustomMd5);
			
			
			$userObject->setPhone($phone);
			$where =array(
			'name ='.$phone.' or phone='.$phone
			
			);
			//if($mdl_user->getByWhere($where)) {
				if(0) {
				$samecount +=1;
				
			}else{
			
				$mdl_user->addUser($userObject->toDBArray());
				
				if ( $mdl_user->errno() ) {
					
					$missing_count+=1;
					
				}else{
					
					$insert_count +=1;
				}
			}
		}
		
		if ($missing_count) {
			//$mdl_user->rollback();
			$result['message']='数据处理未成功,未插入数据条数：'.$missing_count;
		} 
		
		
		if($insert_count>0 && !$missing_count) {
			//$mdl_user->commit();
			
			$result['message']='数据处理成功,插入数据条数：'.$insert_count;
		}
		echo json_encode($result);
		
	}
	
	
	
public function import_new_order_xiaochengxu_action() {
		
		header("Content-type: text/html;charset=utf8_general_ci");
		$order_import_data =post('newusers');
		$order_import_data=stripslashes($order_import_data);

		$order_import_data = json_decode($order_import_data,true);
		
		
		$delivery_date =post('delivery_date');
		
		

		if($order_import_data) {
			
			$result['message']='数据取出成功';
		}else{
			$result['message']='数据未取出';
			
			
			
		}
		
		if($this->loginUser['id'] !=25201) {
				$result['message']='no access';
		
				echo json_encode($result);
			   exit;
		} 
		
		$mdl_order_import= $this->loadModel('order_import');
		
		//$mdl_user->begin();
		
        //清除之前的postcode数据
	
		
	
		
        
		   
	  
		
		$data=array();
		
		
		
		foreach ($order_import_data as $key => $value) {
			
			//'h:i:s a m/d/Y'
			
			
			
			if(!$delivery_date) {
				
				$time1 = strtotime('2020-10-15');
				$delivert_date =$time1;
				
			}
			$aa=rand(1000,99999);
			
			$data['order_name'] =$value['RowID'].$aa;
			$data['orderId'] =$aa.$value['Phone'];
			$data['money'] =$value['Total'];
			$data['business_userId'] =318660;
			$data['first_name'] =$value['Contact'];
			$data['last_name'] ='';
			$data['address'] =$value['Address'];
			$data['phone'] ='0'.$value['Phone'];
			$data['state'] =$value['State'];
			$data['city'] =$value['City'];
			$data['logistic_delivery_date'] =$delivert_date;
			
			$sql = $this->getInsertSql($data,'order_import');
				$result['message']=$sql;
		
			//	echo json_encode($result);
			//   exit;
			   
			if($value['RowID']){
				$where =array(
				  'order_name'=>$value['RowID'].$aa,
				  'logistic_delivery_date'=>$delivert_date
				
		    	);
				if($mdl_order_import->getCount($where)>0) {
					$result['message']='重复插入';
					
				}else{
					$mdl_order_import->insert($data);
					
				}
				
				
				
			}
		
			
			
			
			
			
			
			
			
		}
		
		
		echo json_encode($result);
		
	}
	
		function getInsertSql ($array = null, $tableName = '')
	{
		$keyStr		= '';
		$valueStr	= '';
		$i			= 0;

		if (empty($tableName))
		{
			return false;
		}
		if (is_array($array))
		{
			foreach ($array as $key=>$value)
			{
				$i++;
				$keyStr 	.= ($i > 1 ? ',' : '').'`'.$key.'`';
				$valueStr	.= ($i > 1 ? ',' : '').'\''.$value.'\'';
				//$valueStr	.= ($i > 1 ? ',' : '').'\''.(!get_magic_quotes_gpc() ? addslashes($value):$value).'\'';
			}

			$sql = "insert into $tableName ($keyStr) values ($valueStr)";
			return $sql;
		}
		else
		{
			return false;
		}
	}
	


public function import_new_order_action(){
	
	 $this->display('test/import_new_order_xiaochengxu');
	
}

public function import_yeeyidata_action(){
	
	 $this->display('test/import_user');
	
}

public function abc_action(){
	$mdl = $this->loadModel('factory2c_list');
 	$subCustomerList =Factory2c_centre::getCustmerLists($this->loginUser['id']);
		$mdl_user =$this->loadModel('user');
	 foreach ($subCustomerList as $key => $value) {  
				 
					$mdl_user->update(array('store_update_time' =>time()),$value);
				var_dump($value);
				}
	
 
}
public function test01_action(){
	$mdl = $this->loadModel('factory2c_list');
	if(Factory2c_centre::getIfCurrentUserIsSalesChannal(217093)) {
		  var_dump('ok');exit;
	}else{	
		var_dump('not ok');exit;
	}
}

	

public function show_new_freshfood_action(){
	
	
	 $this->display('freshfood1/businessDetails');
	
}


public function test_get_address_latitude_action() {
	
	
	
	
    $address='15 Gum tree close croyon 3136';
	
	
	//获得客户地址
	$customer_address_latitude = $this->get_address_latitude($address,0);
	
	if(!$customer_address_latitude) 
	{
		var_dump('无法获得客户地址位置！');
	}
	
	//获得商家地址
	
	$business_address_latitude=$this->get_business_address_latitue(320476,'CBD, Melbourne VIC 3000');
	
	if(!$business_address_latitude) 
	{
		var_dump('无法获得商家地址位置！');
	}
	
	//var_dump($business_address_latitude);exit;
	
	
    $cust_lat_arr = explode(',', $customer_address_latitude);
	
	
	$busi_lat_arr = explode(',', $business_address_latitude);
	
	$distance =$this->calculateDistanceBetweenTwoPoints($cust_lat_arr[0], $cust_lat_arr[1], $busi_lat_arr[0], $busi_lat_arr[1],'KM',false,2);
	var_dump($distance);
	
}


public function get_address_latitude($address,$Isbusiness) {
	
	
	$prepAddr = str_replace(' ','+',$address);  
	
  	$geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&sensor=false&key=AIzaSyAbjX3k87CvBY7S1RH5dEcjCehRwbjXzi4');  
	
	$output= json_decode($geocode);  
	  
	$lat = $output->results[0]->geometry->location->lat;  
	$lng = $output->results[0]->geometry->location->lng; 
	if (!$lat || !$lng) {
		 return 0;
		
	}else{
		
		return ($lat.','.$lng);
	}
	

	
}






public function calculateDistanceBetweenTwoPoints($latitudeOne='', $longitudeOne='', $latitudeTwo='', $longitudeTwo='',$distanceUnit ='',$round=false,$decimalPoints='')
    {
        if (empty($decimalPoints)) 
        {
            $decimalPoints = '3';
        }
        if (empty($distanceUnit)) {
            $distanceUnit = 'KM';
        }
        $distanceUnit = strtolower($distanceUnit);
        $pointDifference = $longitudeOne - $longitudeTwo;
        $toSin = (sin(deg2rad($latitudeOne)) * sin(deg2rad($latitudeTwo))) + (cos(deg2rad($latitudeOne)) * cos(deg2rad($latitudeTwo)) * cos(deg2rad($pointDifference)));
        $toAcos = acos($toSin);
        $toRad2Deg = rad2deg($toAcos);

        $toMiles  =  $toRad2Deg * 60 * 1.1515;
        $toKilometers = $toMiles * 1.609344;
        $toNauticalMiles = $toMiles * 0.8684;
        $toMeters = $toKilometers * 1000;
        $toFeets = $toMiles * 5280;
        $toYards = $toFeets / 3;


              switch (strtoupper($distanceUnit)) 
              {
                  case 'ML'://miles
                         $toMiles  = ($round == true ? round($toMiles) : round($toMiles, $decimalPoints));
                         return $toMiles;
                      break;
                  case 'KM'://Kilometers
                        $toKilometers  = ($round == true ? round($toKilometers) : round($toKilometers, $decimalPoints));
                        return $toKilometers;
                      break;
                  case 'MT'://Meters
                        $toMeters  = ($round == true ? round($toMeters) : round($toMeters, $decimalPoints));
                        return $toMeters;
                      break;
                  case 'FT'://feets
                        $toFeets  = ($round == true ? round($toFeets) : round($toFeets, $decimalPoints));
                        return $toFeets;
                      break;
                  case 'YD'://yards
                        $toYards  = ($round == true ? round($toYards) : round($toYards, $decimalPoints));
                        return $toYards;
                      break;
                  case 'NM'://Nautical miles
                        $toNauticalMiles  = ($round == true ? round($toNauticalMiles) : round($toNauticalMiles, $decimalPoints));
                        return $toNauticalMiles;
                      break;
              }


    }
	
	
public function get_item_content_action() {
	
	
	
	$id=get2('id');
	
	$mdl =$this->loadModel('restaurant_menu');
	
	$rec = $mdl->get($id);
	
	$content = $rec['content'];
	
	$content =str_replace('white-space: pre-wrap;','',$content);
	
	
	
	
			
	
	echo $content;
	return 0;
}	
	
	

// 根据给定的商家，如果商家配置复杂运费计算，则获取运费计算方法，并根据客户和商家的距离， 最低其运费，计算用户最终的运费结果



public function get_business_address_latitue($businessId,$altAddress) {
	
	//如果商家替换了地址，或者不是以商家为中心，比如：以cbd为中心
	if ($altAddress) {
		//$business_address ='CBD, Melbourne VIC 3000';
		
		$business_lat = $this->get_address_latitude($altAddress,1);
		return  $business_lat;
	}
	
	
	
	$mdl_user =$this->loadModel('user');
	$business_user = $mdl_user->get($businessId);
	$business_lat=$business_user['google_location'];
	if ($business_lat){
			//如果已经存在不做处理
		}else{
			 $business_address =$business_user['googleMap'];
			 $business_lat = $this->get_address_latitude($business_address,1);
			}
	return $business_lat;
	
	
}


public function delete_noexit_pictur_action() {
	
	$id =get2('id');
	
	$mdl = $this->loadModel('restaurant_menu');
	$sql ="select * from cc_restaurant_menu where restaurant_id =$id";
	$data =$mdl->getListBySql($sql);
	
	foreach ($data as $key => $value) { 
	
	if ($value['id'] ==410924) {
		
		// var_dump('on find one :'.$fileName); exit;
		
	}else{}
	
	 if (!$value['menu_pic']) continue;
      $fileName = DATA_DIR. 'upload/'.$value['menu_pic'];
	
	  if(!is_file($fileName)){
		  //执行将该记录的图片字段的图片文字清空，因为不存在这张图片
		  // var_dump('on find one :'.$fileName);exit;
		  $dataEdit = array (
		   'menu_pic'=>''
		  );
	    $mdl->update($dataEdit,$value['id']);
		
	   var_dump('on find one :'.$fileName);
		  
		  
	  }else{
		   // var_dump('find one :'.$fileName);exit;
	  }
  
	}
	
	
	
	
	
}

public function gen_image_file_from_barcode_web1_action() {
	
	$url =get2('url');
	if($url) {
		$this->gen_image_file_from_barcode_web($url);
		
	}
	
	
}


public function get_product_image_info_action() {
	
$code = get2('code');

$api_key = 'qkal0xb17wczjsawgngde250k9o6ib';
$url = 'https://api.barcodelookup.com/v3/products?barcode='.$code.'&formatted=y&key=' . $api_key;

$ch = curl_init(); // Use only one cURL connection for multiple queries

$data = $this->get_data($url, $ch);

$response = array();
$response = json_decode($data);

echo '<strong>Barcode Number:</strong> ' . $response->products[0]->barcode_number . '<br><br>';

echo '<strong>Title:</strong> ' . $response->products[0]->title . '<br><br>';


echo '<strong>pic:</strong> ' . $response->products[0]->images[0] . '<br><br>';

echo '<strong>Entire Response:</strong><pre>';
print_r($response);
echo '</pre>';


}

function get_data($url, $ch) {
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}


public function test_barcode_match_action() {
	
	$mdl = $this->loadModel('standard_product_info');
	$isMatch = $mdl->get_same_barcode('41390008009');
	if($isMatch) {
		var_dump('find ');exit;
	}else{
		
		var_dump('no find ');exit;
	}
	
}


public function grab_stand_info_from_7xiaojie_action() {
	
	$mdl_restaurant_menu =$this->loadModel('restaurant_menu');
	$sql ="select * from cc_restaurant_menu where restaurant_id = 320951 and (length(menu_cn_name)>0 or length(menu_en_name)>0) and length(menu_pic)>0 ";
	$list = $mdl_restaurant_menu->getListBySql($sql);
	//var_dump(sizeof($list));exit;
	
	$root_dir = $_SERVER['DOCUMENT_ROOT'];
	$mdl_standard_product_info= $this->loadModel('standard_product_info');
	$existCount=0;
	$noexistCount=0;
	foreach ($list as $key => $value) {  
	
	 // 获得图片信息，检查图片是否存在
	 
	 $filename = $root_dir.'/data/upload/'.$value['menu_pic'];
		if (file_exists($filename)) {
			$existCount++;
		//	var_dump('exist: '. $filename);
		 if(!$value['menu_en_name']) {
			 $title = 	$value['menu_cn_name'];		 
		 }else{
			 $title = 	$value['menu_en_name']	;	 
			 
		 }
		  if(!$value['menu_en_desc']) {
			 $desc = 	$value['menu_desc'];		 
		 }else{
			 $desc = 	$value['menu_en_desc']	;	 
			 
		 }
		 
		 
		 
		 $insertData  =array (
								 'barcode_number'=>$value['barcode_number'],
								 'title'=>$title,
								 'title_cn'=>$value['menu_cn_name'],
								 'category'=>$value['restaurant_category_id'],
								 'description'=>$desc,
								 'description_cn'=>$value['menu_desc'],
								 'images1'=>$value['menu_pic'],
								 'imagesmore'=>$value['menu_pics'],
							     'grapType'=>2,
								 'source_business_id'=>320951
								
							  
							  );
							  
		$mdl_standard_product_info->insert($insertData);
							  
							  
		
		}else{
		//	var_dump(' not exist');
			$noexistCount++;
		}
	
	
	}
	//var_dump('图片存在数量：'.$existCount);
	//var_dump('图片不存在数量：'.$noexistCount);exit;
	
	
}

//barcode 自动去除最前面的0 ，进行比较
public function barcode_remove0_return_value_action() {
	$barcode_number =get2('id');
	  $mdl_standard_product_info= $this->loadModel('standard_product_info');
						
							//$this->form_response(500, $barcode_number,'');   
						  // 查找在产品库中是否存在，如果存在，则将图片路径新鲜，复制到该产品之下。 可能包含 主图和子图信息。
							
							$rec = $mdl_standard_product_info->ifFindBarcode($barcode_number);
							var_dump($rec);exit;
	
}


public function image_file_insert_cut_info_action(){
	
	
	$fileName ='standardpic/136605054-1.jpg';
	$letters_insert ="_66x66_fill";
	
	
	$aa = $this->image_file_insert_cut_info($fileName,$letters_insert);
	
	
	var_dump($aa);exit;
	
	
	
	
	
	
}

public function phpinfo(){
	phpinfo();



}


	 public function xero_test_send_invoice_action() {

		 //require_once DOC_DIR.'core/b2b_2_0/b2b/lib/Credentials.php';
		 //require_once DOC_DIR.'core/b2b_2_0/b2b/lib/Credentials_ubonus100mtest_latest.php';
		 require_once DOC_DIR.'core/b2b_2_0/b2b/lib/Database.php';
		 require_once DOC_DIR.'core/b2b_2_0/b2b/lib/MyApi001.php';




			 if(!$this->current_business['id']) {
				 var_dump('please login in again and do it again.');
			 }

			 $api = new MyApi($db);
			 $mdl_xero =$this->loadModel('xero') ;
			 $mdl_tokens =$this->loadModel('tokens') ;

			 $credentials =$mdl_tokens->getCredentials($this->current_business['id'],'xero') ;
			 if(!$credentials){
				 var_dump('Could not get the xero tokens ,please contact admin.');exit;
			 }


				 $orderId =get2('id');
				 $order_data = $mdl_xero->getOrderInvoiceData($orderId);
				// var_dump($order_data);exit;
				 $response_arr = $api->createInvoices($credentials,$order_data);
				 $custom_response= $mdl_xero->createXeroInvoiceInfo($response_arr,$orderId);
				 $response=json_encode($response_arr);
				 echo '<p>CREATE INVOICES</p>';
				 echo $order_data .'<br>';



			 echo $custom_response.'<br>';
			 if(!empty($response)) {
				 $parsed = json_decode($response, true);
				 if(is_array($parsed) && count($parsed) > 0) {
					 $json = json_encode(json_decode($response), JSON_PRETTY_PRINT);
					 echo '<pre>' . $json . '</pre>';

				 } else {
					 echo $response;
				 }
			 }



		// $this->display('factory/xero_test_blank');

	 }

public function getmoney_action() {
		   $orderId = get2('orderId');
		   $result = $this->loadModel('order')->getMoneyDetail1($orderId,$this->current_business['id']);
		   var_dump($result); exit;

}


public function xero_get_contacts_action(){

	$page = get2('page');
	if(!$page) $page=1;
	require_once DOC_DIR.'core/b2b_2_0/b2b/lib/Database.php';
	require_once DOC_DIR.'core/b2b_2_0/b2b/lib/MyApi001.php';
	if(!$this->current_business['id']) {
		var_dump('please login in again and do it again.');
	}
	$api = new MyApi($db);
	$mdl_xero =$this->loadModel('xero') ;
	$mdl_tokens =$this->loadModel('tokens') ;

	$credentials =$mdl_tokens->getCredentials($this->current_business['id'],'xero') ;
	if(!$credentials){
		var_dump('Could not get the xero tokens ,please contact admin.');exit;
	}


	$response_arr = $api->getContacts($credentials,$page);
//
	$custom_response=$mdl_xero->updateXeroContactId($response_arr,$this->current_business['id']);
	$response=json_encode($response_arr);
	echo '<p>GET CONTACTS</p>';
	echo $response;
	echo $custom_response;


}

public function xero_test_action() {

	//require_once DOC_DIR.'core/b2b_2_0/b2b/lib/Credentials.php';
	//require_once DOC_DIR.'core/b2b_2_0/b2b/lib/Credentials_ubonus100mtest_latest.php';
	require_once DOC_DIR.'core/b2b_2_0/b2b/lib/Database.php';
	require_once DOC_DIR.'core/b2b_2_0/b2b/lib/MyApi001.php';


	if (is_post()) {

		if(!$this->current_business['id']) {
			var_dump('please login in again and do it again.');
		}

		$api = new MyApi($db);
		$mdl_xero =$this->loadModel('xero') ;
		$mdl_tokens =$this->loadModel('tokens') ;

		$credentials =$mdl_tokens->getCredentials($this->current_business['id'],'xero') ;
        if(!$credentials){
			var_dump('Could not get the xero tokens ,please contact admin.');exit;
		}

		if(isset($_POST['btnGetContacts'])) {
			$response_arr = $api->getContacts($credentials);
//
			$custom_response=$mdl_xero->updateXeroContactId($response_arr,$this->current_business['id']);
			$response=json_encode($response_arr);
			echo '<p>GET CONTACTS</p>';
		}
		if(isset($_POST['btnCreateContacts'])) {
			$contactList =$mdl_xero->getContactListForCreateContactOnXero($this->current_business['id'],0,0,400);
			//var_dump($contactList);exit;
			$response_arr = $api->createContacts($credentials,$contactList);
			$custom_response= $mdl_xero->createXeroContactId($response_arr,$this->current_business['id']);
			$response=json_encode($response_arr);
			echo '<p>CREATE CONTACTS</p>';
		}
		if(isset($_POST['btnUpdateContact'])) {

			$response = $api->updateContact($credentials);
			echo '<p>UPDATE CONTACT</p>';
		}
		if(isset($_POST['btnGetItems'])) {
			$response_arr = $api->getItems($credentials);
			$custom_response= $mdl_xero->createXeroSyncItems($response_arr,$this->current_business['id']);
			$response=json_encode($response_arr);
			echo '<p>GET ITEMS</p>';
		}
		if(isset($_POST['btnCreateItems'])) {
			$itemList =$mdl_xero->getItemListForCreateItemOnXero($this->current_business['id'],0,0,400);
			//var_dump($itemList);exit;
			$response_arr = $api->createItems($credentials,$itemList);
			$custom_response= $mdl_xero->updateXeroItemCode($response_arr);
			$response=json_encode($response_arr);
			echo '<p>CREATE ITEMS</p>';
		}
		if(isset($_POST['btnUpdateItem'])) {
			$response = $api->updateItem($credentials);
			echo '<p>UPDATE ITEM</p>';
		}
		if(isset($_POST['btnGetInvoices'])) {
			$response = $api->getInvoices($credentials);
			echo '<p>GET INVOICES</p>';
		}
		if(isset($_POST['btnCreateInvoices'])) {
			$orderId ='20220315092259254651';
			$order_data = $mdl_xero->getOrderInvoiceData($orderId);
			//var_dump($order_data);exit;
			$response_arr = $api->createInvoices($credentials,$order_data);
			$custom_response= $mdl_xero->createXeroInvoiceInfo($response_arr,$orderId);
			$response=json_encode($response_arr);
			echo '<p>CREATE INVOICES</p>';
		}
		if(isset($_POST['btnUpdateInvoice'])) {
			$response = $api->updateInvoice($credentials);
			echo '<p>UPDATE INVOICE</p>';
		}

		echo $custom_response.'<br>';
		if(!empty($response)) {
			$parsed = json_decode($response, true);
			if(is_array($parsed) && count($parsed) > 0) {
				$json = json_encode(json_decode($response), JSON_PRETTY_PRINT);
				echo '<pre>' . $json . '</pre>';

			} else {
				echo $response;
			}
		}
	}


	$this->display('factory/xero_test');

}


		public  function  func_test_action()
		{
			$code='318987';
			$guigepos =strpos($code,'-');
			if($guigepos) {
				$itemid=  substr($code,0,$guigepos-1);
				$guigeId =substr($code,$guigepos+1);
			}else{
				$itemid =$code;
			}
          var_dump('itemid is '.$itemid .'and guige is '.$guigeId);exit;
		}


	}
 ?>
