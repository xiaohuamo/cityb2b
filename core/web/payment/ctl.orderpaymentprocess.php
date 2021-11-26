<?php

class ctl_orderpaymentprocess extends cmsPage
{
	public function checkout_action()
	{
		
	
		if ( ! $this->loginUser ) {
			$this->form_response_msg($this->lang->log_in_please);
		}
		

	/**
		 * Check Customer Info
		 */
		$checkResponseCode = array();
		$checkResponseMsg = array();

		$delivery_option =trim(post('deliver_option'));

		if(!in_array($delivery_option, ['0','1','2'])){
			$checkResponseCode[]= '503';
			$checkResponseMsg[]= (string)$this->lang->please_choose.(string)$this->lang->deliver_option;
		}


		if(!in_array(post( 'payment' ), ['alipay','paypal','royalpay','offline','hcash','creditcard'])){
			$checkResponseCode[]= '502';
			$checkResponseMsg[]= (string)($this->lang->remind_payment);
		}


		if(!trim(post('delivery_first_name'))||!trim(post('delivery_last_name'))){
			$checkResponseCode[]='501';
			$checkResponseMsg[]=(string)$this->lang->remind_name.'';
		}

		if(!trim(post('delivery_phone'))){
			$checkResponseCode[]='501';
			$checkResponseMsg[]=(string)$this->lang->remind_phone;
		}
		
		if(!trim(post('delivery_email'))){
			//$checkResponseCode[]='501';
			//$checkResponseMsg[]=(string)$this->lang->remind_email;
		}

        $factory_id = (int)post('factory_id');
        if($factory_id) {
            $mdl_user_factory = $this->loadModel('user_factory');
            if(!$mdl_user_factory->isUserApproved($this->loginUser['id'], $factory_id)) {
                $mdl_user_factory->updateApprove($this->loginUser['id'], $factory_id, false);
            }
        }

     	$orderId=date( 'YmdHis' ).$this->createRnd();
		$phone00= trim(post('delivery_phone'));
		$user_name = trim(post('delivery_first_name').' '. post('delivery_last_name'));
		$arr_post_yunying =array(
			'orderId'=>$orderId,
			'phone'=>$phone00,
			'userId'=>$this->loginUser['id'],
			'name'=>$user_name
	);
		
			// 这里为运营提供第一个数据记录，就是一旦基础的输入信息检测通过，则写入数据库。 这个可能会产生不少信息，比如后面的信息一旦有问题，会重写，但是， 如果这个用户在这天下了单，就不去调查，如果该用户没有下成单，则需要进行追查。
		
		// 当前已经把用户信息存到表种， 如果一切正常到下面再把订单信息补进来， 如果 这个过程中间断掉了， 可能出现很多问题， 我这样，一会呢， 后面加一个错误号，这样，基础信息过后，就有错误号告诉客服原因。
		
		// 这个号， 字段上增加一个错误号的处理。
		
		$mdl_wj_temp_orderID_carts_for_yunying  =$this->loadModel('wj_temp_orderID_carts_for_yunying')->save_temp_data($arr_post_yunying,$orderId);;
		
		//$mdl_wj_temp_orderID_carts_for_yunying
		
		//$this->form_response(join($checkResponseCode,'#'),join($checkResponseMsg,','))
		
		if($delivery_option=='1'){
			/**
			 * deliver
			 */	
			
			// enforce customer info
			if(!trim(post('delivery_googleMap'))){
				$checkResponseCode[]= '501';
				$checkResponseMsg[]= (string)$this->lang->remind_address;
			}

		}elseif($delivery_option=='2'){
			/**
			 * pickup
			 */	
			
			if(!trim(post('business_staff_id'))){
				$checkResponseCode[]='503';
				$checkResponseMsg[]=(string)$this->lang->remind_pickup_address;
			}

			// enforce customer info
			// if(!trim(post('delivery_googleMap'))){
			// 	$this->form_response(501,'请在收货人地址中填写地址！');
			// }

			
		}elseif($delivery_option=='0'){
			/**
			 * evoucher
			 */	
			
			// enforce customer info
			// if(!trim(post('delivery_googleMap'))){
			// 	$this->form_response(501,'请在收货人地址中填写地址！');
			// }

			
			
		}

			$arr_post_yunying =array(
			'orderId'=>$orderId,
			'userId'=>$this->loginUser['id'],
			'name'=>$user_name
			);
			
		if(sizeof($checkResponseCode)>0) {
			
			
			//修改方面还有问题。。。。。 还没有调通
			$mdl_wj_temp_orderID_carts_for_yunying=$this->loadModel('wj_temp_orderID_carts_for_yunying')->update_temp_data($arr_post_yunying,join($checkResponseMsg,','),join($checkResponseMsg,','));
		
			
			$this->form_response(join($checkResponseCode,'#'),join($checkResponseMsg,','));


		}



		/**
		 * Check Item Status
		 */
		$quantityLimitReached=false;
		$errorMsg='Opps! ';
		

		$businessIdList = [];

		$mdl_coupons=$this->loadModel('coupons');

		$arr_quantity =post( 'quantity' );

		$coupon_arr= post( 'main_ids' );
		$multi_use=1;
        
		for ($i=0; $i <sizeof($coupon_arr) ; $i++) { 

			$coupon_id =$coupon_arr[$i];

			$curr_coupon=$mdl_coupons->get($coupon_id);
			
			if ($this->getLangstr()=='en') {
				if($curr_coupon['title_en']){
					
					$curr_coupon['title']=$curr_coupon['title_en'];
				}
				
			}
            
			// 如果当前的coupon 是多次使用的产品，则 会将订单置为多次可用。
			if ($curr_coupon['multi_use']>1){
				
				$multi_use =$curr_coupon['multi_use'];
			}
			
			
			$max_quantity =$curr_coupon['perCustomerLimitQuantity'];
            $min_quantity =$curr_coupon['perCustomerMinLimitQuantity'];

			if($max_quantity==0){
				$max_quantity=100000;
			}

			$purchese_qty = $arr_quantity[$i];

			if($purchese_qty>$max_quantity){
				$quantityLimitReached=true;
				$errorMsg.=$this->lang->buy_limit_desc1.$curr_coupon['title'].$this->lang->buy_limit_desc2.$purchese_qty.$this->lang->buy_limit_desc3.$max_quantity.$this->lang->buy_limit_desc4."<br>";
			}

            if($purchese_qty<$min_quantity){
                $quantityLimitReached=true;
                $errorMsg.=(string)$this->lang->buy_limit_desc1.$curr_coupon['title'].$this->lang->buy_limit_desc2.$purchese_qty.$this->lang->buy_limit_desc5.$min_quantity.$this->lang->buy_limit_desc4."<br>";
            }

            $alert = $mdl_coupons->checkIsPublish($curr_coupon);
			if($alert){
				$quantityLimitReached=true;
				$errorMsg.=$this->lang->buy_limit_desc1.$curr_coupon['title'].")".$alert.$this->lang->buy_limit_desc6."<br>";
			}

            $businessIdList[] = $curr_coupon['createUserId'];

			/**
			 * same check as query/add_cart
			 */
			if($curr_coupon['isInManagement']==1){
                $this->specialEventTimeCheck();
                $this->specialEventTotalQtyLimitCheck($this->loginUser['id'],'buy');
            }

            $this->freeProductPurcheseLimitCheck($this->loginUser['id'],$curr_coupon['id'],'buy');
		}

		if($quantityLimitReached){
			//修改方面还有问题。。。。。 还没有调通
			$mdl_wj_temp_orderID_carts_for_yunying=$this->loadModel('wj_temp_orderID_carts_for_yunying')->update_temp_data($arr_post_yunying,$errorMsg,$errorMsg);
			$this->form_response_msg($errorMsg);
		}

		/**
		 * 检查所有统配商家是否支持递送时间 
		 */
		$this->loadModel('freshfood_disp_suppliers_schedule');
		$dispCenterUserSelectedDeliveryDate = post('dispCenterUserSelectedDeliveryDate');
		$parts = explode("@", $dispCenterUserSelectedDeliveryDate);
		$dispCenterUserSelectedDeliveryDateTimestamp = count($parts)==2 ? $parts[0] : 0 ;
		foreach (array_intersect(array_unique($businessIdList), DispCenter::getSupplierList()) as $bid) {
			if (!DispCenter::isDeliverDateStillValid($dispCenterUserSelectedDeliveryDateTimestamp,$bid)) {
				//修改方面还有问题。。。。。 还没有调通
				$mdl_wj_temp_orderID_carts_for_yunying=$this->loadModel('wj_temp_orderID_carts_for_yunying')->update_temp_data($arr_post_yunying,$bid.'统配时间已经过期或失效，请返回产品页面重新下单或修改时间',$bid.'统配时间已经过期或失效，请返回产品页面重新下单或修改时间');
		 		$this->form_response_msg($bid.'统配时间已经过期或失效，请返回产品页面重新下单或修改时间');
			}
		};

		/**
		 * 检查所有统配商家订单是否超过上限
		 */
		if (DispCenter::isOverMaxDailyOrderLimit($dispCenterUserSelectedDeliveryDateTimestamp))
		{
			$messge ='抱歉，您选择的递送日期订单爆表，我们递送能力有限，请改个日子吧';
			$mdl_wj_temp_orderID_carts_for_yunying=$this->loadModel('wj_temp_orderID_carts_for_yunying')->update_temp_data($arr_post_yunying,$bid.'统配时间已经过期或失效，请返回产品页面重新下单或修改时间',$bid.'统配时间已经过期或失效，请返回产品页面重新下单或修改时间');
			$this->form_response_msg($message);
        }
			
		/**
		 * 合单运费计算。 同地址第二单免运费
		 */
		$combind_ordered = 0;
		$orderCombineTimeLimit = 2 * 24 * 3600; //2days
		$dispatch_business_id=$this->get_dispatching_centre_businessId($curr_coupon['createUserId']);

		$address =trim(post('delivery_googleMap'));

		if($address){
			$where['userId']=$this->loginUser['id'];
			$where['business_userId']=$dispatch_business_id;
			$where['coupon_status']='c01';
			$where['customer_delivery_option']='1';
			$where['address']=$address;
			$where[]=" createTime > " .(time() - $orderCombineTimeLimit);

			$result = $this->loadModel('order')->getList(['orderId','createTime'],$where);
			if(sizeof($result)>=1){
				$combind_ordered=1;
			};
		}
       
	   // 如果不是合单，则查看当前用户是否达到最低运费
		$actualTotal = floatval(post('total_amount_inc_delivery_fees'))+ floatval(post('confirmedMoneyAppliedAmount')) + floatval(post('promotion_total'));
	   	if (!$combind_ordered)  {
			if (array_intersect(array_unique($businessIdList), DispCenter::getSupplierList())) {  //表示当前商家是包含在通配中心供应商列表中，那么去找通配中心商家编号，并获得相应的起定金额
				$dispatch_business_id=$this->get_dispatching_centre_businessId($curr_coupon['createUserId']);
				
				$dispach_user = $this->loadModel('user')->get($dispatch_business_id);
				if($dispach_user['amount_for_minimum_delivery']) {
					if ($actualTotal < $dispach_user['amount_for_minimum_delivery'] && !$factory_id) {
						$mdl_wj_temp_orderID_carts_for_yunying=$this->loadModel('wj_temp_orderID_carts_for_yunying')->update_temp_data($arr_post_yunying,'Opps！小于最低起送金额$'.$dispach_user['amount_for_minimum_delivery'],'Opps！小于最低起送金额$'.$dispach_user['amount_for_minimum_delivery']);
		
						$this->form_response_msg('Opps！小于最低起送金额$'.$dispach_user['amount_for_minimum_delivery']);
					}
				}
			}else{ //如果非通配中心商家，直接获取当前商家的起送金额。
				$gen_business_userId=$curr_coupon['createUserId'];
				$general_business_user = $this->loadModel('user')->get($gen_business_userId);
				if($general_business_user['amount_for_minimum_delivery']) {
					if ($actualTotal < $general_business_user['amount_for_minimum_delivery'] && !$factory_id) {
						
						$mdl_wj_temp_orderID_carts_for_yunying=$this->loadModel('wj_temp_orderID_carts_for_yunying')->update_temp_data($arr_post_yunying,'Opps！小于最低起送金额$'.$general_business_user['amount_for_minimum_delivery'],'Opps！小于最低起送金额$'.$general_business_user['amount_for_minimum_delivery']);
		
						$this->form_response_msg('Opps！小于最低起送金额$'.$general_business_user['amount_for_minimum_delivery']);
					}
				}
			}
	   	}

	

		if(post( 'payment' )=='hcash'){
			// $hcashOrderId =post('hcashOrderId');
			// $hcashOrderTag =post('hcashOrderTag');
			$hcashRate =post('hcashRate');
			$hcashAmount =post('hcashAmount');

			if(!$hcashRate||!$hcashAmount){
				$this->form_response_msg("Hcash 核心数据缺失");
			}

			// if(!$hcashOrderId||!$hcashOrderTag){
			// 	$this->form_response_msg("Hcash 钱包转账信息缺失");
			// }
		}


		if(post( 'payment' )=='creditcard'){

			if(post('use_card_on_file')){
				$cardData=$this->loadModel('user')->get_card($this->loginUser['id']);

				$card_number =$cardData['card_number'];
				$card_expire_month =$cardData['card_expire_month'];
				$card_expire_year =$cardData['card_expire_year'];
				$card_security_code =$cardData['card_security_code'];

			}else{
				$card_number =post('card_number');
				$card_expire_month =post('card_expire_month');
				$card_expire_year =post('card_expire_year');
				$card_security_code =post('card_security_code');
			}
			
			if ($actualTotal > 50 && count($this->loadModel('order')->getOrderListOfCustomer($this->loginUser['id'])) <=1) {
				
				$mdl_wj_temp_orderID_carts_for_yunying=$this->loadModel('wj_temp_orderID_carts_for_yunying')->update_temp_data($arr_post_yunying,'"新用户尝试使用信用卡支付超过50刀，被拒绝"','"用户信用卡支付上限为$50"');
	
				$this->form_response(502,"用户信用卡支付上限为$50，请选择转账支付或其它支付方式！");
			}


			if(!$card_number&&post('total_amount_inc_delivery_fees')!=0)
			{
				$mdl_wj_temp_orderID_carts_for_yunying=$this->loadModel('wj_temp_orderID_carts_for_yunying')->update_temp_data($arr_post_yunying,'Card Number is Missing','Card Number is Missing');
				$this->form_response(502,"Card Number is Missing");
			}
		}

 		//  如果门牌号重写，则放重写的门牌号码
		$new_addr_house_number  =  post('Street_number');
		
		if(strlen(trim($new_addr_house_number))==0) {
			$new_addr_house_number = post('addr_house_number');
			// $this->form_response(502,'number is null replace original:'.$new_addr_house_number);
		}
		 //$this->form_response(502,'number is not null:'.$new_addr_house_number);
	

		$arr_post=array(
			'ids'=>	 post( 'main_ids' ),
			'sub_ids'=> post( 'sub_ids' ),
			'quantities'=>post( 'quantity' ),
			'sub_money'	=>post( 'single_amount' ),
            'original_amount'	=>post( 'original_amount' ),
            'commission_free'	=>post( 'commission_free' ),
			'coupon_names'	=>post( 'coupon_names' ),
			'money'=> post('total_amount_inc_delivery_fees'),
			'promotion_total' => post( 'promotion_total' ),
			'promotion_id' => post( 'promotion_id' ),
			'payment'=> post( 'payment' ),
			'business_userId'=> post( 'business_userId' ),
			'business_staff_id' =>post('business_staff_id'),
			'sub_or_main'=>post('sub_or_main'),
			'guige_des'=>post('guige_des'),
			'guige_ids'=>post('guige_ids'),
			'seat_id'=>post('seat_id'),
			'seat_des'=>post('seat_des'),
			'customer_delivery_option'=>$delivery_option, 
			'delivery_fees'=>post('delivery_fee'),
			'booking_fees'=>post('booking_total'),
			'first_name'=>post('delivery_first_name'),
			'last_name'=>post('delivery_last_name'),
			'phone'=>post('delivery_phone'),
			'address'=>post('delivery_googleMap'),

			'house_number'=>$new_addr_house_number,
			'street'=>post('addr_street'),
			'city'=>post('addr_city'),
			'state'=>post('addr_state'),
			'country'=>post('addr_country'),
			'postalcode'=>post('addr_post_code'),

			'email'=>post('delivery_email'),
			'id_number'=>post('id_number'),
			'message_to_business'=>post('message_to_business'),
			'confirmedMoneyAppliedAmount'=>post('confirmedMoneyAppliedAmount'),
			'surcharge'=>post('surcharge'),
			'specialGroupPinCheckoutUserGroupId'=>post('specialGroupPinCheckoutUserGroupId'),

			'dispCenterUserSelectedDeliveryDate'=>post('dispCenterUserSelectedDeliveryDate'),

			'menu_id'=>post('menu_id'),
			'sidedish_menu_id'=>post('sidedish_menu_id'),

			'hcashOrderId' =>post('hcashOrderId'),
			'hcashOrderTag' =>post('hcashOrderTag'),
			'hcashRate' =>post('hcashRate'),
			'hcashAmount' =>post('hcashAmount'),

			'card_number' =>$card_number,
			'card_expire_month' =>$card_expire_month,
			'card_expire_year' =>$card_expire_year,
			'card_security_code' =>$card_security_code,
			);
			

			/**
			 * Checkout Real Time Quantity
			 */
			$msg = '';
			
			$mdl_restaurant_menu = $this->loadModel('restaurant_menu');
			
			foreach ( $arr_post['ids'] as $key => $val ) {
				$notEnoughQty=false;


					// 座位销售
			  	if($coupon['bonusType']=='10') {
			  		$mdl_show_seats =$this->loadModel('wj_show_seats');

			  		$data=$mdl_show_seats->get($arr_post['seat_id'][$key]);
			  		if($data['sold']==1)$notEnoughQty=true;//  有可能重单，购买失败
			  		
			  	}else{
		    		// 定义变量接受客户的购买数量
					$customer_buy_quantities= $arr_post['quantities'][$key];
		  	    	if($customer_buy_quantities==0){continue;}
			  
			  	    $coupon = $this->loadModel('coupons')->get( $arr_post['ids'][$key]);

		  			//检查如果是子卡，则取子卡数据
		  			if($arr_post['sub_or_main'][$key]=='s'){
						$subCoupon = $this->loadModel('coupons_sub')->get( $arr_post['sub_ids'][$key] );

						$stock=$subCoupon['quantity'];
						if(!$stock){ 
						 $stock= 99999;
						}
				  		$pendingQty=$this->loadModel('wj_temp_orderID_carts')->getPendingQty($arr_post['sub_ids'][$key],'s');
					}else{
						if($this->loadModel('shop_guige')->couponHasGuige($coupon['id'])&&$coupon['bonusType']==9){

					  		$guige_ids_array=explode(',', $arr_post['guige_ids'][$key]);
						  	$guige1Id=$guige_ids_array[0];
						  	$guige2Id=$guige_ids_array[1];
						  	if($guige1Id=='null'||$guige1Id==null||$guige1Id==''||$guige1Id<0||$guige1Id=='undefined'){$guige1Id=-1;}
						  	if($guige2Id=='null'||$guige2Id==null||$guige2Id==''||$guige2Id<0||$guige2Id=='undefined'){$guige2Id=-1;}
						  	
						  	$stock=$this->loadModel('shop_stock')->getStock($coupon['id'],$guige1Id,$guige2Id);
					  		$pendingQty=$this->loadModel('wj_temp_orderID_carts')->getPendingQty($coupon['id'],'m');
				  		}else{
				  			$stock=$coupon['qty'];
				  			$pendingQty=$this->loadModel('wj_temp_orderID_carts')->getPendingQty($arr_post['ids'][$key],'m');
				  		}
					}

					if($stock-$pendingQty<$customer_buy_quantities)$notEnoughQty=true;
				}

			  	if($notEnoughQty)$msg.=" ".$coupon['title'];
				
				
				//检查如果是餐馆产品的购买数量，或是超市
				$msg="";
			  	if($arr_post['menu_id'][$key]) {
					$menu_rec = $mdl_restaurant_menu->get($arr_post['menu_id'][$key]);
					if($menu_rec['qty']<$arr_post['quantities'][$key]){
						
						$msg .=$arr_post['coupon_names'][$key]."数量超过最大库存： ".$menu_rec['qty']." 请调整数量！ ";
					}
					
					
					
				}

		  	}

		  	if($msg!='')$this->form_response_msg($msg."哦～");

		  	/**
			 * END
			 */
			

			//$orderId=date( 'YmdHis' ).$this->createRnd();
			$arr_post['userId']=$this->loginUser['id'];
			$arr_post['orderId']= $orderId;
			if ($multi_use>1) {
				$arr_post['multi_use']= $multi_use;
			}else{
				$arr_post['multi_use']= 1;
			}


			
			// 将该数据存入临时表中
			
			if($arr_post['money']==0){
				//如果最终结算金额为0，标定为线下支付。订单将在下一步直接支付成功
	  			$arr_post['payment']='offline';
	  		}
			
			$mdl_wj_temp_orderID_carts =$this->loadModel('wj_temp_orderID_carts')->save_temp_data($arr_post,$orderId);
			//var_dump($arr_post);exit;
			$arr_post_yunying['enter_paying_process']='1';
			$arr_post_yunying['arr_post']=$arr_post;
			$payment_type =$arr_post['payment'];
			$mdl_wj_temp_orderID_carts_for_yunying=$this->loadModel('wj_temp_orderID_carts_for_yunying')->update_temp_data($arr_post_yunying,'支付方式:'.$payment_type.(string)$this->lang->submission_success,(string)$this->lang->submission_success);
	
			$this->form_response(200,(string)$this->lang->submission_success,HTTP_ROOT_WWW.'payment/orderpaymentprocess/pay?orderId='.$orderId);


	}

	function pay_action(){
		$orderId = trim( get2( 'orderId' ) );
		$status = trim( get2( 'status' ) );
		if(!$orderId)$this->sheader(null,'No order ID');

		$arr_post=$this->loadModel('wj_temp_orderID_carts')->get_temp_data($orderId);

  		$order_data = $this->loadModel('order')->getByOrderId($orderId);

  		if(!$arr_post&&!$order_data){
  			$this->sheader(null,(string)$this->lang->no_data);
  		}
  		elseif(!$arr_post&&$order_data){
  			$this->pay_success_action($orderId);
  			exit;
  		}


  		if($arr_post['money']==0){

  			$this->finish_order($orderId);
  			$this->pay_success_action($orderId);

  		}elseif($arr_post['money']>0){
  			switch ( $arr_post['payment'] ) {
				case 'paypal':
						$mdl_busi_pay_setting = $this->loadModel('wj_busi_pay_setting_application');
		
						$payto=($mdl_busi_pay_setting->isPaymentSelfManage($arr_post['business_userId']))?$mdl_busi_pay_setting->getBusinessPaypalEmail( $arr_post['business_userId'] ):$this->payments['paypal']['config']['business'];
		
						foreach ( $arr_post['ids'] as $key => $val ) {
							$order_name =$order_names.' ' .$arr_post['coupon_names'][$key];
						}

						$this->paypal_form($payto,$orderId,$order_name,$arr_post['money']);
					break;
				case 'royalpay':
				case 'alipay':
						
						foreach ( $arr_post['ids'] as $key => $val ) {
							$order_name =$order_names.' ' .$arr_post['coupon_names'][$key];
						}

						$this->royalpay_form_action($orderId,$order_name,$arr_post['money'],$arr_post['payment']);
					break;

				case 'hcash':
						foreach ( $arr_post['ids'] as $key => $val ) {
							$order_name =$order_names.' ' .$arr_post['coupon_names'][$key];
						}

						$this->hcash_form_action($orderId,$order_name,$arr_post['hcashAmount']);
					break;
				case 'offline':
					$this->create_order($orderId);
					if($status==1) { //if the status ==1 means from b2b order ,and just marketd at paid .
						
						$this->update_order_paid($orderId);
					}
					
					$this->loadModel('system_notification_center')->notify(SystemNotification::NewOrder,$orderId);

					$this->offline_success_action($orderId);
					break;

				case 'creditcard':

						include_once CORE_DIR.'cbaAPI/commonWebAPI.php';

						$card = new CreditCard();
						$card->card_number=$arr_post['card_number'];
						$card->expiry_month=$arr_post['card_expire_month'];
						$card->expiry_year=$arr_post['card_expire_year'];
						$card->security_code=$arr_post['card_security_code'];

						$commonWebAPI = new CommonWebAPI();
						$result = $commonWebAPI->pay($orderId,$card,$arr_post['money']);
						
						$this->setData($result,'result');

						if($result['result']=='SUCCESS'){
							
							$this->finish_order($orderId);

							$this->setData($this->lang->paypal_success1,'heading');
							$this->setData($this->lang->paypal_success8,'sys_message');
							$this->setData("
								<i class='fa fa-check fa-5x' style ='color:#3aff33'></i>
								<br> ".$this->lang->sys_message_detail4,'sys_message_detail');

							$this->setData("<a href='".HTTP_ROOT_WWW."member/exchange_detail?id=".$orderId."'>".$this->lang->paypal_success9."</a>",'further_action');

							//save card info to file
							$cardData=array();
							$cardData['card_number']=$arr_post['card_number'];
							$cardData['card_expire_month']=$arr_post['card_expire_month'];
							$cardData['card_expire_year']=$arr_post['card_expire_year'];
							$cardData['card_security_code']=$arr_post['card_security_code'];

							$this->loadModel('user')->save_card($cardData,$arr_post['userId']);

						}elseif($result['result']=='PENDING'){
							$this->create_order($orderId);

							$this->update_order_pending($orderId);

							$this->setData($this->lang->creditcard.'Pending','heading');
							$this->setData($this->lang->creditcardcreditcard_is_processing,'sys_message');
							$this->setData("
								<i class='fa fa-check fa-5x' style ='color:#ff8e33'></i>
								<br>
								".$this->lang->sys_message_detail1,'sys_message_detail');
							$this->setData("<a href='".HTTP_ROOT_WWW."member/exchange_detail?id=".$orderId."'>".$this->lang->paypal_success9."</a>",'further_action');


						}elseif($result['result']=='FAILURE'){

							$this->setData($this->lang->creditcard_failure,'heading');
							$this->setData($this->lang->creditcard_invalid,'sys_message');
							$this->setData("
								<i class='fa fa-close fa-5x' style ='color:#f23030'></i>
								<br>
								".$this->lang->sys_message_detail2,'sys_message_detail');

							$this->setData("<a href='".HTTP_ROOT_WWW."member/showcart'>".$this->lang->back_to_shopping_cart."</a>",'further_action');

						}else{
							$this->setData($this->lang->creditcard_failure,'heading');
							$this->setData($this->lang->sys_message_detail3,'sys_message');
							$this->setData("
								<i class='fa fa-close fa-5x' style ='color:#767676'></i>
								<br>".$this->lang->sys_message_detail2
								,'sys_message_detail');
							
							$this->setData("<a href='".HTTP_ROOT_WWW."member/showcart'>".$this->lang->back_to_shopping_cart."</a>",'further_action');
						}


						if($this->getUserDevice()=='desktop'){
							$this->display( 'payment/creditcard/return' );
						}else{
							$this->display( 'payment/creditcard/return_mobile' );
						}

					break;

				default:
					$this->sheader(null,(string)$this->lang->unknow_payment_method.$arr_post['payment']);
			}
  		}
		
	}

	public function paypal_form($payto,$orderId,$order_name,$amount)
	{
		$action = $this->payments['paypal']['config']['sandmode'] ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
		$payto =$this->payments['paypal']['config']['sandmode'] ? 'ubonus-paypal-facilitator@ubonus.com.au' : $payto;

		$form = array();
		$form['cmd'] = '_xclick';
		$form['notify_url'] = HTTP_ROOT.'payment/orderpaymentprocess/paypal_notify?orderId='.$orderId;
		$form['return'] = HTTP_ROOT.'payment/orderpaymentprocess/paypal_success?orderId='.$orderId;
		
		$form['business'] =$payto;
		
		$form['item_name'] =$order_name.'-'.$orderId;
		$form['currency_code'] = 'AUD';
		$form['amount'] = $amount;
		$form['orderId'] = $orderId;
		$form['charset'] = 'utf-8';
		
		
		$this->setData( $action, 'action' );
		$this->setData( $form, 'form' );
		$this->display( 'payment/paypal/form' );
	}

	public function paypal_varify(){
			require_once('core/paypal/PaypalIPN.php');

			$ipn = new PaypalIPN();

			if ($this->payments['paypal']['config']['sandmode']) $ipn->useSandbox();

			return $ipn->verifyIPN();
	}

	public function paypal_notify_action()
	{	
		//txn_type
		if ( $this->paypal_varify()==true) {
				if ( ( $_POST['payment_status'] == 'Completed' )  ) {

					$orderId = $_GET['orderId'];

					$this->finish_order($orderId);

				}elseif($_POST['payment_status'] == 'Pending'){

					$orderId = $_GET['orderId'];

					$this->create_order($orderId);

					$this->update_order_pending($orderId);

					//possible email notification

				}elseif($_POST['payment_status'] == 'Refunded'){

					$orderId = $_GET['orderId'];

					//possible system action to cancel order
					
				}else{
					//Denied
				}
			filelog('SUCCESS#'.$_POST['payment_status'].'#'.$_GET['orderId'].'#'.date("F j, Y, g:i a"),'paypal.txt');

		}else{
			filelog('FAIL#'.serialize($_POST),'paypal.txt');
		}

	}

	public function paypal_success_action($id=null)
	{	
		$orderId = $_GET['orderId'];

		if($id)$orderId=$id;

		if($orderId){
			$this->create_order($orderId);
			$order = $this->loadModel('order')->getByWhere(array('orderId'=>$orderId));
		}else{

		}

		$this->setData( 'Paypal '.(string)$this->lang->paypal_success1.' － '.$this->site['pageTitle'], 'pageTitle' );

		if($order['status']=="0"){
			$sys_message_detail="
			<br>
			<p>".(string)$this->lang->paypal_success2."</p>
			<p><small>".(string)$this->lang->paypal_success3."</small> </p>
			<i class='fa fa-check fa-5x' style ='color:#767676'></i>";

		}elseif($order['status']=="1"){
			$sys_message_detail="
			<br>
			<p>".(string)$this->lang->paypal_success4."</p>
			<i class='fa fa-check fa-5x' style ='color:#3aff33'></i>";

		}elseif($order['status']=="2"){
			$sys_message_detail="
			<br>
			<p>".(string)$this->lang->paypal_success5."</p>
			<p><small>".(string)$this->lang->paypal_success6."</small> </p>
			<i class='fa fa-check fa-5x' style ='color:#ff8e33'></i>";
		}


		$this->setData((string)$this->lang->paypal_success7,'heading');
		$this->setData((string)$this->lang->paypal_success8,'sys_message');
		$this->setData($sys_message_detail,'sys_message_detail');
		
		$further_action="<a href='".HTTP_ROOT_WWW."member/exchange_detail?id=".$orderId."'>".(string)$this->lang->paypal_success9."</a>";
		$this->setData($further_action,'further_action');


		if($this->getUserDevice()=='desktop'){
			$this->display( 'payment/paypal/return' );
		}else{
			$this->display( 'payment/paypal/return_mobile' );
		}
		
	}

	public function royalpay_form_action($orderId,$order_name,$money,$channel='royalpay')		
	{
		require_once('core/royalpay/lib/RoyalPay.Api.php');

		if($this->getUserDevice()=='wechat'){

			$input = new RoyalPayUnifiedOrder();
			$input->setOrderId($orderId);
			$input->setDescription($order_name);
			$input->setPrice($money * 100);
			$input->setCurrency("AUD");
			$input->setNotifyUrl(HTTP_ROOT.'payment/orderpaymentprocess/royalpay_notify');
			$input->setOperator("Ubonus-System");

			//支付下单
			$result = RoyalPayApi::jsApiOrder($input);

			//跳转
			$inputObj = new RoyalPayJsApiRedirect();
			$inputObj->setDirectPay('true');
			$inputObj->setRedirect(urlencode(HTTP_ROOT.'payment/orderpaymentprocess/royalpay_success?orderId=' . strval($input->getOrderId())));

			$action = RoyalPayApi::getJsApiRedirectUrl($result['pay_url'], $inputObj);

			$this->sheader($action);
		} else {
			$input = new RoyalPayUnifiedOrder();
			$input->setOrderId($orderId);
			$input->setDescription($order_name);
			$input->setPrice($money * 100);
			$input->setCurrency("AUD");
			$input->setNotifyUrl(HTTP_ROOT.'payment/orderpaymentprocess/royalpay_notify');
			$input->setOperator("Ubonus-System");

			if($channel == 'alipay' && $this->getUserDevice() == 'mobile') {
				$input->setChannel('Alipay');

				//支付下单
				$result = RoyalPayApi::h5Order($input);

				//跳转
				$inputObj = new RoyalPayJsApiRedirect();
				$inputObj->setDirectPay('true');
				$inputObj->setRedirect(urlencode(HTTP_ROOT.'payment/orderpaymentprocess/royalpay_success?orderId=' . strval($input->getOrderId())));

				$action = RoyalPayApi::getJsApiRedirectUrl($result['pay_url'], $inputObj);
			} else {
				//支付下单
				$result = RoyalPayApi::qrOrder($input);

				//跳转
				$inputObj = new RoyalPayRedirect();
				$inputObj->setRedirect(urlencode(HTTP_ROOT.'payment/orderpaymentprocess/royalpay_success?orderId=' . strval($input->getOrderId())));

				$action= RoyalPayApi::getQRRedirectUrl($result['pay_url'], $inputObj);
			}

			$this->setData( $action, 'action' );
			$this->display( 'payment/royalpay/form' );
		}
	}

	public function royalpay_notify_action()
	{
		require_once('core/royalpay/lib/RoyalPay.Api.php');
		$response = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
		
		if ($this->royalpay_varify($response)) {//验证成功
		    
		    //商户订单号
		    $order_id = $response['partner_order_id'];
		    //RoyalPay订单号
		    $royal_order_id = $response['order_id'];
		    //订单金额，单位是最小货币单位
		    $order_amt = $response['total_fee'];
		    //支付金额，单位是最小货币单位
		    $pay_amt = $response['real_fee'];
		    //币种
		    $currency = $response['currency'];
		    //订单创建时间，格式为'yyyy-MM-dd HH:mm:ss'，澳洲东部时间
		    $create_time = $response['create_time'];
		    //订单支付时间，格式为'yyyy-MM-dd HH:mm:ss'，澳洲东部时间
		    $pay_time = $response['pay_time'];

		    filelog('SUCCESS#'.serialize($response),'royalpay.txt');

		    $this->finish_order($order_id);

		} else {//验证失败
			filelog('FAIL#'.$GLOBALS['HTTP_RAW_POST_DATA'],'royalpay.txt');
		}
	}

	public function royalpay_varify($response)
	{	
		require_once('core/royalpay/lib/RoyalPay.Api.php');
		$input = new RoyalPayDataBase();
		$input->setNonceStr($response['nonce_str']);
		$input->setTime($response['time']);
		$input->setSign();
		if ($input->getSign() == $response['sign']) {//验证成功
		   return true;
		} else {//验证失败
		   return false;
		}
	}

	public function royalpay_success_action($id=null)
	{
		$orderId = $_GET['orderId'];

		if($id)$orderId=$id;

		require_once('core/royalpay/lib/RoyalPay.Api.php');
	    $input = new RoyalPayOrderQuery();
	    $input->setOrderId($orderId);
	    $result=RoyalPayApi::orderQuery($input);
		

	    if($result['result_code']=='PAY_SUCCESS'){
	    	$this->create_order($orderId);

	    	$sys_message_detail="
	    	<p>".$result['result_code']."</p>
			<i class='fa fa-check fa-5x' style ='color:#fc3'></i>";

	    	$this->setData( 'RoyalPay Results － '.$this->site['pageTitle'], 'pageTitle' );

	    	$this->setData($this->lang->paypal_success7,'heading');
			$this->setData('Pay successful!','sys_message');
			$this->setData($sys_message_detail,'sys_message_detail');
			
			$further_action="<a href='".HTTP_ROOT_WWW."member/exchange_detail?id=".$orderId."'>".$this->lang->paypal_success9."</a>";

			$this->setData($further_action,'further_action');
	    }else if($result['result_code']=='PAYING'){

	    	$sys_message_detail="<p>".$result['result_code']."</p>
			<i class='fa fa-exclamation-triangle fa-5x' style ='color:#adadad'></i>";

	    	$this->setData( 'RoyalPay Results － '.$this->site['pageTitle'], 'pageTitle' );
	    	
	    	$this->setData('支付未完成','heading');
			$this->setData('Opps! RoyalPay paying failed!<br>'.(string)$this->lang->paypal_success3,'sys_message');
			$this->setData($sys_message_detail,'sys_message_detail');
			
			$further_action="<a href='".HTTP_ROOT_WWW."member/exchange_detail?id=".$orderId."'>".$this->lang->paypal_success9."</a>";

			$this->setData($further_action,'further_action');
	    }else{

	    	$sys_message_detail="<p>".$result['result_code']."</p>
			<i class='fa fa-close fa-5x' style ='color:#f23030'></i>";

	    	$this->setData( 'RoyalPay Results － '.$this->site['pageTitle'], 'pageTitle' );
	    	
	    	$this->setData('Paying failed','heading');
			$this->setData('Opps! RoyalPay paying failed!','sys_message');
			$this->setData($sys_message_detail,'sys_message_detail');
			

			$this->setData($further_action,'further_action');
	    }


		if($this->getUserDevice()=='desktop'){
			$this->display( 'payment/royalpay/return' );
		}else{
			$this->display( 'payment/royalpay/return_mobile' );
		}
		
	}

	public function offline_success_action($orderId=null)
	{
		if(!$orderId)$orderId=$_GET['orderId'];

		$this->setData( (string)$this->lang->offline_success_description1.'－ '.$this->site['pageTitle'], 'pageTitle' );
		$this->setData( $orderId, 'orderId' );
		if($this->getUserDevice()=='desktop'){
			$this->display( 'payment/offline_success' );
		}else{
			$this->display( 'payment/offline_success_mobile' );
		}
	}

	public function hcash_form_action($orderId,$order_name,$money)
	{	
		$this->setData($orderId,'orderId');
		$this->setData($order_name,'orderName');
		$this->setData($money,'money');

		if($this->getUserDevice()=='desktop'){
			$this->display( 'payment/hcash/form' );
		}else{
			$this->display( 'payment/hcash/form_mobile' );
		}

	}

	public function hcash_success_action($id=null)
	{
		
		if(is_post()){
			$hcashOrderId =post('hcashOrderId');
			$hcashOrderTag =post('hcashOrderTag');
			
			if(!$hcashOrderId||!$hcashOrderTag){
				$this->sheader(null,"Wallet info lost.");
			}

			$orderId = $_GET['orderId'];

			$this->create_order($orderId);
			

			$data['hcash_order_id']=$hcashOrderId;

			$data['hcash_order_tag']=$hcashOrderTag;

			$where['order_id']=$orderId;

			if($this->loadModel('hcash_record')->updateByWhere($data,$where)){

				$this->setData( ' 购买成功 － '.$this->site['pageTitle'], 'pageTitle' );
				$this->setData( $orderId, 'orderId' );
				if($this->getUserDevice()=='desktop'){
					$this->display( 'payment/hcash/hcash_success' );
				}else{
					$this->display( 'payment/hcash/hcash_success_mobile' );
				}
			}else{
				$this->sheader(null,'更新hcash_record出错，请稍后再试');
			}
		}else{
			$orderId = $id;

			$this->setData( ' 购买成功 － '.$this->site['pageTitle'], 'pageTitle' );
			$this->setData( $orderId, 'orderId' );
			if($this->getUserDevice()=='desktop'){
				$this->display( 'payment/hcash/hcash_success' );
			}else{
				$this->display( 'payment/hcash/hcash_success_mobile' );
			}
		}
		
		
	}

	function finish_order($orderId){

		$result = $this->create_order($orderId);

		$this->update_order_paid($orderId);

		if($result)$this->loadModel('system_notification_center')->notify(SystemNotification::NewOrder,$orderId);

	}

	private function create_order($orderId){
		$arr_post =$this->loadModel('wj_temp_orderID_carts')->get_temp_data($orderId);

		$order = $this->loadModel('order')->getByWhere(array('orderId'=>$orderId));

		if($arr_post&&!$order){
			//有临时数据并且订单没有生成
			$this->buy_voucher($arr_post,$orderId);
					
			$this->loadModel('wj_user_temp_carts')->removeAllItemOfBusiness($arr_post['userId'],$arr_post['business_userId']);

			$this->loadModel('wj_temp_orderID_carts')->delete_temp_data($orderId);

			//买一赠多
			if(isset($arr_post['giftedCouponOrderId'])){
				$giftedCouponOrderIds = explode(',' , $arr_post['giftedCouponOrderId']);
				foreach ($giftedCouponOrderIds as $giftedCouponOrderId) {
					$a_p =$this->loadModel('wj_temp_orderID_carts')->get_temp_data($giftedCouponOrderId);
					$o = $this->loadModel('order')->getByWhere(array('orderId'=>$giftedCouponOrderId));
					if($a_p&&!$o){
						$this->buy_voucher($a_p,$giftedCouponOrderId);
						$this->loadModel('wj_temp_orderID_carts')->delete_temp_data($giftedCouponOrderId);
						$this->update_order_paid($giftedCouponOrderId);
						$this->loadModel('system_notification_center')->notify(SystemNotification::NewOrder,$giftedCouponOrderId);
					}
				}
			}

			return true;
		}else{

			return false;
		}
		
	}

	private function update_order_paid($orderId){
		$mdl_order = $this->loadModel( 'order' );

		$where['orderId']=$orderId;
		$data['status']=1;
		$data['paytime']=time();
		$data['txn_id']=$_POST['txn_id'];
		$data['txn_result']=serialize( $_POST );

		return $mdl_order->updateByWhere($data,$where);
		
	}

	private function update_order_pending($orderId){
		$mdl_order = $this->loadModel( 'order' );

		$where['orderId']=$orderId;
		$data['status']=2;
		$data['paytime']=time();

		return $mdl_order->updateByWhere($data,$where);
		
	}

	public function pay_success_action($id=null)
	{	
		$orderId = $_GET['orderId'];

		if($id)$orderId=$id;

		if($orderId){
			$order = $this->loadModel('order')->getByWhere(array('orderId'=>$orderId));
		}

		$this->setData( (string)$this->lang->paypal_success1. '－ '.$this->site['pageTitle'], 'pageTitle' );

		
		$sys_message_detail="
		<br>
		<p>" .(string)$this->lang->paypal_success4."</p>
		<i class='fa fa-check fa-5x' style ='color:#3aff33'></i>";
		

		$this->setData($this->lang->paypal_success7,'heading');
		$this->setData($this->lang->paypal_success8,'sys_message');
		$this->setData($this->lang->paypal_success4,'sys_message_detail');
		
		$further_action="<a href='".HTTP_ROOT_WWW."member/exchange_detail?id=".$orderId."'>".$this->lang->paypal_success9."</a>";
		$this->setData($further_action,'further_action');


		if($this->getUserDevice()=='desktop'){
			$this->display( 'payment/paypal/return' );
		}else{
			$this->display( 'payment/paypal/return_mobile' );
		}
		
	}



}
