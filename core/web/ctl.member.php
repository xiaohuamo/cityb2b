<?php

//会员

class ctl_member extends cmsPage
{

	function ctl_member() {
		parent::cmsPage();

		$this->setData( 'member', 'footer_menu' );//old version mobile
		$this->setData( 'dashboard', 'mobile_menu' ); //new version mobile

		$act = $GLOBALS['gbl_act'];
		$ignore_list = array('register','register_business','login','mobileonly_login','multiple_wx_login','logout','wx_redirect','wx_register','bind_wx');
		if ( !in_array($act, $ignore_list) && !$this->loginUser ) {
			$this->sheader( HTTP_ROOT_WWW.'member/login?returnUrl='.urlencode( $_SERVER['REQUEST_URI'] ) );
		}
		
		//将loginuser 中的电话信息 和 name 信息进行一个规范
		
	 // $this->loginUser['phone'] =str_replace(" ","",$this->loginUser['phone']);
		//var_dump($this->loginUser['phone']);exit;
	}
    function showcart1_action(){



        if(!$this->loginUser['person_last_name'] && !$this->loginUser['person_first_name']) { //如果根本没有用户信息

            if(strpos($this->loginUser['name'],' ') != false){  // 且找到用户名中包含空格

                $this->loginUser['name'] =str_replace ("'","",$this->loginUser['name']);
                $pos =strpos($this->loginUser['name'],' ');

                $this->loginUser['person_last_name']=substr($this->loginUser['name'],0,$pos);
                $this->loginUser['person_first_name']=substr($this->loginUser['name'],$pos+1);
                $this->loadModel('user')->update(array('person_last_name'=>$this->loginUser['person_last_name'],'person_first_name'=>$this->loginUser['person_first_name']),$this->loginUser['id']);

                $this->setData($this->loginUser,'loginUser');
            }

        }
        /**
         * listen var code_str to add cart on arrive shopping cart
         */

        //$this->form_response_msg((string)$this->lang->wx_panding_fail_later);
        $this->addCarts();

        /**
         * group pin and group buy can add cart with code only
         */

        $this->showcart_specialCheckout();


        $mdl_wj_user_temp_carts = $this->loadModel( 'wj_user_temp_carts' );
        $mdl_freshfood_disp_centre_suppliers=$this->loadModel('freshfood_disp_centre_suppliers');
        $mdl_user=$this->loadModel('user');

        $business_userid = (int)get2('business_userid');
        $id =$business_userid;

        if(!$business_userid){
            $business_userid=0;
        }else{
            $sql ="select DISTINCT business_id as businessUserId from cc_freshfood_disp_centre_suppliers where suppliers_id =$business_userid";

            $dispaitch_business = $mdl_freshfood_disp_centre_suppliers->getListBySql($sql);
            if($dispaitch_business ) {
                $dispaitch_business_id =$dispaitch_business[0]['businessUserId'];
                // var_dump($dispaitch_business_id);exit;
            }
            $this->setData('true','showSingleBusiness');
        }


        $factory_id = (int)get2('factory_id');
        if($factory_id) {
            $this->setData($factory_id,'factory_id');
        }

        /**
         * 购物车内容
         */

        $cartItems=$mdl_wj_user_temp_carts->getDetailedItem($this->loginUser['id'], $business_userid,$this->getLangStr());


        // 判断是否为多个商家组检出 ， cartitems的数量是独立商家的数量， 有些商家（如freshfood)是可以在统配中心集中出货，所以可以同时检出，
        // 因此 ，系统检查该数组中多个商家是否为统一出货，如果都是统一出货，则标记为单商家购物车检出，
        // 不需要弹出选择检出商家框。

        $uni_business =array();
        foreach ($cartItems as $key => $value) {
            $business_id= $cartItems[$key]['businessUserId'];
            $sql ="select DISTINCT business_id as businessUserId from cc_freshfood_disp_centre_suppliers where suppliers_id =$business_id";
            // var_dump($sql);
            $dispaitch_business = $mdl_freshfood_disp_centre_suppliers->getListBySql($sql);
            if($dispaitch_business ) {

                array_push($uni_business, $dispaitch_business[0]['businessUserId']);
                $cartItems[$key]['businessUserName'] = $mdl_user->getBusinessDisplayName($dispaitch_business[0]['businessUserId'],$this->getLangStr()).' ('. $cartItems[$key]['businessUserName'].')';
            }else{
                // 如果独立检出的商家 ，加入数组
                array_push($uni_business, $business_id);
            }
            //去掉重复的独立商家
            $uni_business = array_unique($uni_business);

        }

        //**  计算当前购物车中有多少个独立的checkout 商家结束



        //var_dump(sizeof($uni_business));exit;
        if(sizeof($uni_business)>1){
            $this->setData('true','mixedItemFromBusinesses');
        };

        //统配商家加入递送时间
        $this->loadModel('freshfood_disp_suppliers_schedule');
        if (in_array($business_userid, DispCenter::getSupplierList())) {
            $dispCenterUserSelectedDeliveryDate = $this->cookie->getCookie('DispCenterUserSelectedDeliveryDate');
            $parts = explode("@", $dispCenterUserSelectedDeliveryDate);
            $dateTimestamp = $parts[0];
            $timeType = $parts[1];

            $displayStr = date('D, F j, Y', $dateTimestamp);
            switch ($timeType) {
                case 'morning':
                    $displayStr .= " 8:00 am - 14:00 pm";
                    break;
                case 'afternoon':
                    $displayStr .= " 14:00 pm - 20:00 pm";
                    break;
                case 'anytime':
                    $displayStr .= " 8:00 am - 20:00 pm";
                    break;

            }
            $this->setData($dispCenterUserSelectedDeliveryDate, 'dispCenterUserSelectedDeliveryDate');
            $this->setData($displayStr, 'dispCenterUserSelectedDeliveryDate_display');
        }

        $processingBusinessUserId =$cartItems[0]['businessUserId'];
        $this->setData($processingBusinessUserId,'processingBusinessUserId');

        /**
         * 登录用户的送货地址
         */
        $mdl_wj_user_delivery_info= $this->loadModel('wj_user_delivery_info');
        $where =array(
            'userId'=>$this->loginUser['id'],
            'isDefaultAddress'=>1
        );
        $wj_user_delivery_info = $mdl_wj_user_delivery_info->getbyWhere($where);

        if(!$wj_user_delivery_info){

            $wj_user_delivery_info=array(
                'userId'=>$this->loginUser['id'],
                'first_name'=>$this->loginUser['person_first_name'],
                'last_name'=>$this->loginUser['person_last_name'],
                'address'=>$this->loginUser['googleMap'],
                'phone'=>$this->loginUser['phone'],
                'email'=>$this->loginUser['email'],
                'addrPost'=>$this->loginUser['addrPost'],
                'country'=>$this->loginUser['country'],
                'createTime'=>time()
            );
        }
        $this->setData( $wj_user_delivery_info, 'delivery_info' );
        // var_dump($wj_user_delivery_info);exit;
        $where =array(
            'userId'=>$this->loginUser['id'],
        );
        $wj_user_delivery_info_list = $mdl_wj_user_delivery_info->getList(null,$where);
        $this->setData( json_encode($wj_user_delivery_info_list),'delivery_info_list');

        // 获取该商家可用邮编

        // 如果是统配商家，获得统配商家的邮编列表
        if ($dispaitch_business_id) {
            //获得统配商家邮件列表
            $where =array(
                'business_userId'=>$dispaitch_business_id,
            );
        }else{
            // 获得单独商家邮编列表
            $where =array(
                'business_userId'=>$business_userid,
            );

        }


        $mdl_local_delivery_postcodes= $this->loadModel('local_delivery_postcodes');
        $local_delivery_postcodes_list = $mdl_local_delivery_postcodes->getList(null,$where);

        //$postcode_arr=array();

        foreach ($local_delivery_postcodes_list as $key => $value) {
            //array_push($postcode_arr,$value['postcode']);
            if($key==0) {

                $postcodes=$value['postcode'];
            }else{
                $postcodes.=','.$value['postcode'];
            }

        }

        $this->setData( $postcodes,'avaliable_postcodes');
        // $postcode_arr1=explode(',', $postcode_arr);
        // var_dump ($postcodes);exit;
        /**
         * 用户的可用钱包余额
         */
        $moneyBalance=$this->loadModel('recharge')->getBalanceOfUser($this->loginUser['id']);
        if($moneyBalance<0)$moneyBalance =0;
        $this->setData($moneyBalance,'moneyBalance');


        /**
         * 产品的预定费。 预定费需要基于产品设定,如果是统配中心，更换为统配中心的booking fee
         */

        if($dispaitch_business_id){

            $this->setData($this->loadModel('user')->getBookingFee($dispaitch_business_id),'bookingfee');
            $this->setData($this->loadModel('user')->getBookingFeeType($dispaitch_business_id),'bookingfeetype');

        }else{
            $this->setData($this->loadModel('user')->getBookingFee($processingBusinessUserId),'bookingfee');
            $this->setData($this->loadModel('user')->getBookingFeeType($processingBusinessUserId),'bookingfeetype');


        }


        /**
         * 检测是否有可用优惠吗。 由于Globalcode的存在。 所有产品都有可用优惠码
         */
        //$this->setData($this->loadModel( 'wj_promotion_code' )->promotionExist($this->loginUser['id'],$processingBusinessUserId),'promotion');


        $this->setData(true,'promotion');

        // 获取某个用户的可用优惠码，只拿通用的。

        $promotion_code_list = $this->loadModel( 'wj_promotion_code' )->getPromotionCodeList($this->loginUser['id'],$processingBusinessUserId);
        $this->setData($promotion_code_list,'promotion_code_list');
        //var_dump ($promotion_code_list);exit;


        /**
         * 商家信息
         */
        if($dispaitch_business_id){
            $this->setData($this->loadModel('user')->get($dispaitch_business_id),'businessUser');
        }else{
            $this->setData($this->loadModel('user')->get($processingBusinessUserId),'businessUser');
        }

        /**
         * 商家的递送规则
         * @var [type]
         */
        if($dispaitch_business_id){
            $business_delivery_info =$this->loadModel('user')->getBusinessDeliveryInfo($dispaitch_business_id,$this->getLangStr());
        }else{
            $business_delivery_info =$this->loadModel('user')->getBusinessDeliveryInfo($processingBusinessUserId,$this->getLangStr());
        }

        //var_dump($dispaitch_business_id);exit;
        /**
         * 全局控制递送方式显示与否的开关
         */
        // var_dump( $cartItems[0]);
        if($mdl_wj_user_temp_carts->allItemsAreEvoucher($cartItems[0]['items'])){
            //如果检出的全部产品为 evoucher, 购物车不需要显示递送方式
            $business_delivery_info['EvoucherOrrealproduct']='evoucher';

            // var_dump($business_delivery_info['EvoucherOrrealproduct']);exit;
        }else{
            $business_delivery_info['EvoucherOrrealproduct']='realproduct';

            //开启递送选项
            $business_delivery_info['deliver_enable']
                =($business_delivery_info['deliver_enable']&&$mdl_wj_user_temp_carts->itemsHasDeliverAvaliable($cartItems[0]['items']));

            //开启自取选项
            $business_delivery_info['pickup_enable']
                =($business_delivery_info['pickup_enable']&&$mdl_wj_user_temp_carts->itemsHasPickupAvaliable($cartItems[0]['items']));
        }


        $this->setData( $business_delivery_info, 'business_delivery_info' );


        /**
         * 一个商家只能有一套自取点。不支持自取的产品需要在用户选择自取的时候标红剔除。
         */
        if($dispaitch_business_id){
            $sql ="select id as sales_user_list,contactPersonNickName,googleMap,contactMobile from cc_user where user_belong_to_user= $dispaitch_business_id and role=5";
            //var_dump ($sql);exit;
            $staff_value_arr =$this->loadModel('user')->getListBySql($sql);
            //var_dump($staff_value_arr);exit;
            $staff_list = $this->loadModel('user')->findCommonStaff($staff_value_arr);
        }else{
            $staff_list = $this->loadModel('user')->findCommonStaff($cartItems[0]['items']);
        }

        $this->setData($staff_list,'staff_list');



        //***************************
        // 如果商家为生鲜商家，则 返回编辑直接跳回到生鲜商家的购买页面，二不是用默认退回
        $business_info =$this->loadModel('user')->get($cartItems[0]['businessUserId']) ;
        //var_dump($business_info);exit;
        if($business_info) {
            if($business_info['business_type_freshfood'] ==1) {
                $this->setData($business_info['id'],'freshfood');
            }
        }






        $this->loadModel('freshfood_disp_suppliers_schedule');
        $businessDispSchedule = DispCenter::getBusinessDispSchedule($id);
        $businessDispScheduleFilledWithContinueDates = DispCenter::getFollowingNDaysIncludeAvailableDeliver($businessDispSchedule);


        //获得订货调度的具体日期及星期几


        $businessDispScheduleFilledWithContinueDates=$this->getWeekDayandDateOfSchedue($businessDispScheduleFilledWithContinueDates);



        $this->setData(json_encode($businessDispScheduleFilledWithContinueDates), 'businessDispSchedule');
        $this->setData(in_array($id, DispCenter::getSupplierList()), 'isDispCenterBusiness');

        $this->setData(join(DispCenter::getPostcode(DispCenter::getDispCenterIdOfSupplier($id)), ','), 'postcodeSupported'); //使用统配商家邮编信息
        //  var_dump(json_encode($businessDispScheduleFilledWithContinueDates));exit;




        //获得当前店铺得 订货时间
        $mdl = $this->loadModel('freshfood_disp_suppliers_schedule');
        $where = [];
        $where['business_id'] = $id;
        $where['centre_business_id'] = DispCenter::getDispCenterIdOfSupplier($id);
        $list = $mdl->getList(null, $where);



        foreach ($list as $key =>$value) {
            $order_start_of_date_cn = $this->get_cn_weekdayName_from_en_weekdayName($value['order_start_of_date']);
            $order_cut_of_date_cn = $this->get_cn_weekdayName_from_en_weekdayName($value['order_cut_of_date']);
            $delivery_date_of_week_cn = $this->get_cn_weekdayName_from_en_weekdayName($value['delivery_date_of_week']);

            $list[$key]['order_start_of_date_cn'] = $order_start_of_date_cn ;
            $list[$key]['order_cut_of_date'] =$order_cut_of_date_cn  ;
            $list[$key]['delivery_date_of_week_cn'] = $delivery_date_of_week_cn ;
            $list[$key]['ordertime_cn'] = '从'.$order_start_of_date_cn.$value['order_start_of_time'] .' 到 '.$order_cut_of_date_cn.$value['order_cut_of_time'];
            $list[$key]['ordertime'] = 'From '.$value['order_start_of_date'].' '.$value['order_start_of_time'] .' To '.$value['order_cut_of_date'].' '.$value['order_cut_of_time'];

        }




        $this->setData(json_encode($list),'current_business_tuangou_time');



        /**
         * 预留的控制 输入身份证号提示的开关
         */
        $this->setData(false,'requireIdCard');

        /**
         * 是否有存储的信用卡可用
         */
        $this->setData($this->loadModel('user')->get_card_on_file($this->loginUser['id']),'card_on_file');

        /**
         * 默认使用上一次消费的支付方式
         */
        $this->setData($this->loadModel('order')->getLastOrderPaymentMethod($this->loginUser['id']),'lastOrderPaymentMethod');

        /**
         * 是否是第一次消费，第一次超过100无法使用信用卡支付
         */

        $this->setData((count($this->loadModel('order')->getOrderListOfCustomer($this->loginUser['id'])) === 0), 'isFirstTimeBuyer');

        $this->setData( json_encode($cartItems), 'json_data' );
        $this->setData( $cartItems, 'data' );

        $this->setData($this->parseUrl()->setPath('member/delete_cart_item')->set('id'),'deleteSingleUrl');

        $this->setData( (string)$this->lang->shopping_cart, 'pagename' );
        $this->setData( (string)$this->lang->shopping_cart.'- '.$this->site['pageTitle'], 'pageTitle' );

        $isCheckout =get2('checkout') ;

        if($isCheckout){
            $this->display_pc_mobile('placeorder/placeorder','placeorder/placeorder');
        }else{
            $this->display_pc_mobile('cart','cart');
        }


    }

	 function avatar_action() {
           if ( is_post() ) {
               $data['avatar'] = post('avatar');
               $mdl_user = $this->loadModel( 'user' );
               if ( $mdl_user->updateUserById( $data, $this->loginUser['id'] ) ) {
                   
                   $this->form_response(200,'保存头像成功',HTTP_ROOT_WWW."company/avatar");
               }
               else {
                   $this->form_response_msg('保存头像失败');
               }
           }
           else {
               $this->setData( '修改头像 - 商家中心 - '.$this->site['pageTitle'], 'pageTitle' );

               $this->setData( 'user_setting','menu' );
               $this->setData( 'avatar','submenu' );

               $this->display_pc_mobile('member/avatar','mobile/member/avatar');
           }
    }

    function send_sms_verify_code_action()
    {	
    	$to = $this->loginUser['phone'];

    	if(trim(get2('to')))$to = trim(get2('to'));

    	$code = $code = $this->createRnd();

    	$content = $this->lang->verify_code.$code;

		send_sms($to,$content);

    	$_SESSION['sms_verify_code']=$code;
    	$_SESSION['sms_verify_phone']=$to;

    	$data['status']=200;
    	$data['msg']=$code;

		echo json_encode($data);
    }

    function update_sms_verified_action()
    {	
    	$loginUserId = $this->loginUser['id'];

    	if($_SESSION['sms_verify_code']==trim(get2('code'))){
    		$mdl_user= $this->loadModel('user');

    		$data['phone_verified']='true';
    		$data['phone']=$_SESSION['sms_verify_phone'];
    		
    		$mdl_user->update($data,$loginUserId);

    		$mdl_user->setTrustLevel(1,$loginUserId);

    		//更新全部以后产品为审核状态
    		$this->loadModel('coupons')->updateByWhere(array('isApproved'=>1),array('createUserId' =>$loginUserId));

    		$this->form_response_msg('success');
    	}else{
    		$this->form_response_msg('error');
    	}
    }

     function update_email_action()
    {	
    	$email=trim(get2('email'));

    	$mdl_user= $this->loadModel('user');
    	
    	$data['email']=$email;

    	if($mdl_user->update($data,$this->loginUser['id'])){
    		$this->form_response(200,'success');
    	}else{
    		$this->form_response_msg('error');
    	}

    }


	function index_action() {

        $id = $this->loginUser['id'];

     //   $this->form_response_msg('login role is' .$this->trueLogin );
      //  var_dump(session('truelogin')); exit;
       // var_dump( $GLOBALS['KEY_']);
       // $str = $GLOBALS['KEY_'].'dnlop01'.$GLOBALS['_KEY'];
       // var_dump($str);
       // var_dump( $this->md5($str));
       // exit;
        if(session('truelogin')) { //如果为真是客户登陆则不转换身份呢
     //      var_dump(truelogin); exit;
        }else{
                $groupManager =$this->cookie->getCookie('groupManager');
                if($groupManager) { //如果当前有groupmanager登陆路过，则优先处理groupmanager
                    $this->groupMangerCheckAndSheader($groupManager, 'member/index');
                    return 1;
                }


                //如果不是groupmanager 检查是否为代理
                $agentId =$this->cookie->getCookie('agentcityb2b');

                if ($agentId !=$id) {
                    $mdl_user =$this->loadModel('user');

                    $user = $mdl_user->getUserById( $agentId );
                    $data = array(
                        'lastLoginIP'	=> ip(),
                        'lastLoginDate'	=> time(),
                        'loginCount'	=> $user['loginCount'] + 1
                    );

                    $mdl_user->updateUserById( $data, $user['id'] );

                    $this->session( 'member_user_id', $user['id'] );
                    $this->session( 'member_user_shell', $this->md5( $user['id'].$user['name'].$user['password'] ) );

                    $this->loginUser=$user;
                    $this->sheader(HTTP_ROOT_WWW.'company/index');

                 }


        }

        // 获取用户是否为组管理里用户
        $this->setData($this->loadModel('user_group')->getCountOfMembers($this->loginUser['id']),'CountOfGroupMembers');


		$this->setData( (string)$this->lang->member_center, 'pagename' );
		$this->setData( 'index', 'menu' );
		$this->setData( (string)$this->lang->my_account.' - '.$this->site['pageTitle'], 'pageTitle' );
		$displayName = $this->loadModel('user')->getUserDisplayName($this->loginUser['id']);
		$this->setData( $displayName, 'displayName' );

		$share_icon_link = ($this->loadModel('referral_rule_application')->getUserRuleList($this->loginUser['id'])>0)?HTTP_ROOT_WWW.'referal/manage_referral_rule_application':HTTP_ROOT_WWW.'referal/referrals?type=user';
		$this->setData($share_icon_link,'share_icon_link');
		$this->display_pc_mobile('mobile/member/index','mobile/member/index');
	}
	
	
	function mingxingdian_index_action() {

		$this->setData( '明星店设置-Ubous美食生活', 'pagename' );
		$this->setData( 'index', 'menu' );
		$this->setData( '明星店设置 - '.$this->site['pageTitle'], 'pageTitle' );
		
		$displayName = $this->loadModel('user')->getUserDisplayName($this->loginUser['id']);
		$this->setData( $displayName, 'displayName' );

		$share_icon_link = ($this->loadModel('referral_rule_application')->getUserRuleList($this->loginUser['id'])>0)?HTTP_ROOT_WWW.'referal/manage_referral_rule_application':HTTP_ROOT_WWW.'referal/referrals?type=user';
		$this->setData($share_icon_link,'share_icon_link');

		$this->display_pc_mobile('member/index','mobile/member/mingxingdian_index');
	}

	function showcart_specialCheckout(){

		//SPECIAL GROUP BUY CHECKOUT CODE
		$specialGroupBuyCheckoutCode=get2('specialGroupBuyCheckoutCode');

		//para not empty
		if (!$specialGroupBuyCheckoutCode) {
			$this->setData("ERROR",'specialCheckout');
			$this->setData("empty para ",'ERRORMSG');
			return;
		}

		$where['promotion_code']=$specialGroupBuyCheckoutCode;
		$where['is_expired']=0;
		$code=$this->loadModel('wj_promotion_code')->getByWhere($where);

		//code exist
		if (!$code) {
			$this->setData("ERROR",'specialCheckout');
			$this->setData("NO Code",'ERRORMSG');
			return;
		}

		$where=[];//reset var
		$where['id']=$code['coupon_id'];
		$where['status']=4;
		$where['isApproved']=1;
		$coupon=$this->loadModel('coupons')->getByWhere($where);

		//coupon exist
		if(!$coupon){
			$this->setData("ERROR",'specialCheckout');
			$this->setData("NO coupon",'ERRORMSG');
			return;
		}

		if($this->loadModel('group_pin')->isGroupPinCode($specialGroupBuyCheckoutCode)){
			/**
			 * Group Pin Checkout
			 */
			$specialGroupPinCheckoutSub=get2('specialGroupPinCheckoutSub');
			$specialGroupPinCheckoutGuigeId=get2('specialGroupPinCheckoutGuigeId');
			// $specialGroupPinCheckoutGuigeDes=get2('specialGroupPinCheckoutGuigeDes');
			$specialGroupPinCheckoutQty=get2('specialGroupPinCheckoutQty');
			$specialGroupPinCheckoutUserGroupId=get2('specialGroupPinCheckoutUserGroupId');

			
			$mdl_shop_guige_detials=loadModel('shop_guige_details');
			$specialGroupPinCheckoutGuigeDes='';
			foreach (explode(',', $specialGroupPinCheckoutGuigeId) as $id) {
				$specialGroupPinCheckoutGuigeDes .= $mdl_shop_guige_detials->getGuigeName($id)." ";
			}

			//add coupon to shoping cart
			$mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');

	        $mdl_wj_user_temp_carts->clearTempCart($this->loginUser['id']);
			
			if($specialGroupPinCheckoutSub&&$specialGroupPinCheckoutSub!=$coupon['id']){
				$sub_coupon=$this->loadModel('coupons_sub')->get($specialGroupPinCheckoutSub);

				$data = array(
		            'userId' => $this->loginUser['id'],
		            'createTime' => time(),
		            'main_coupon_id' => $coupon['id'],
		            'sub_coupon_id' => $sub_coupon['id'],
		            'sub_or_main' => 's',
		            'coupon_name' => $sub_coupon['title'],
		            'businessUserId' => $coupon['createUserId'],
		            'quantity' => $specialGroupPinCheckoutQty,
		            'single_amount' => $sub_coupon['customer_amount'],
		            'guige_des' => $specialGroupPinCheckoutGuigeDes,
		            'guige_ids' => $specialGroupPinCheckoutGuigeId
		        );
			}else{
				$data = array(
		            'userId' => $this->loginUser['id'],
		            'createTime' => time(),
		            'main_coupon_id' => $coupon['id'],
		            'sub_coupon_id' => $coupon['id'],
		            'sub_or_main' => 'm',
		            'coupon_name' => $coupon['title'],
		            'businessUserId' => $coupon['createUserId'],
		            'quantity' => $specialGroupPinCheckoutQty,
		            'single_amount' => $coupon['voucher_deal_amount'],
		            'guige_des' => $specialGroupPinCheckoutGuigeDes,
		            'guige_ids' => $specialGroupPinCheckoutGuigeId
		        );
			}
			

	        $mdl_wj_user_temp_carts->insert($data);

			$specialGroupBuyCheckoutCode=$code['promotion_code'];

			//去拼单 pass user group id to coupon_buy
			$this->setData($specialGroupPinCheckoutUserGroupId,'specialGroupPinCheckoutUserGroupId');

			//set front page js signal
			$this->setData($specialGroupBuyCheckoutCode,'specialCheckout');
			//front paga will 1.populate code  2. move to final checkout page 3. trigger checkcode js process.

			//SPECIAL GROUP BUY CHECKOUT CODE END


		}else{
			/**
			 * Group Buy Checkout
			 */
			
			$qty =$this->loadModel('group_buy')->getOrderQty($this->loginUser['id'],$specialGroupBuyCheckoutCode);
			//you are in the group of that reward, and get your joined qty
			if($qty<1){
				$this->setData("ERROR",'specialCheckout');
				$this->setData("Not in the group",'ERRORMSG');
				return;
			}


			//add coupon to shoping cart
			$mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');

	        $mdl_wj_user_temp_carts->clearTempCart($this->loginUser['id']);
			
			//guige
			$guige_des='';
			$guige_ids='undefined,undefined';
			if($this->loadModel('shop_guige')->couponHasGuige($coupon['id'])){
				$guigeSet=$this->loadModel('shop_stock')->getDefaultGuigeSet($coupon['id']);
				
				$guige_ids=join(',',$guigeSet);

				$guigeName1=$this->loadModel('shop_guige_details')->getGuigeName($guigeSet[0]);
				$guigeName2=$this->loadModel('shop_guige_details')->getGuigeName($guigeSet[1]);

				$guige_des=$guigeName1.' '.$guigeName2;
			}


		 	$data = array(
	            'userId' => $this->loginUser['id'],
	            'createTime' => time(),
	            'main_coupon_id' => $coupon['id'],
	            'sub_coupon_id' => $coupon['id'],
	            'sub_or_main' => 'm',
	            'coupon_name' => $coupon['title'],
	            'businessUserId' => $coupon['createUserId'],
	            'quantity' => $qty,
	            'single_amount' =>$coupon['voucher_deal_amount'],
	            'guige_des' => $guige_des,
	            'guige_ids' => $guige_ids
	        );

	        $mdl_wj_user_temp_carts->insert($data);

			$specialGroupBuyCheckoutCode=$code['promotion_code'];

			//set front page js signal
			$this->setData($specialGroupBuyCheckoutCode,'specialCheckout');
			//front paga will 1.populate code  2. move to final checkout page 3. trigger checkcode js process.

			//SPECIAL GROUP BUY CHECKOUT CODE END
		}

		
	}
  	
  	function delete_cart_item_action(){
  		//delete action
		$id = (int)get2('id');
		if ($id>0 ) {
			$mdl_wj_user_temp_carts = $this->loadModel( 'wj_user_temp_carts' );

			$item=$mdl_wj_user_temp_carts->get( $id );
			$this->loadModel('wj_show_seats')->unlockSeat($item['seat_id']);
			$mdl_wj_user_temp_carts->delete( $id );
		}

		$this->sheader($this->parseUrl()->setPath('member/showcart')->set('id'));

  	}

  	/**
  	 * same as query addcarts batch
  	 */
  	public function addCarts()
  	{	
  		$userId=$this->loginUser['id'];

        if(!$userId)return false;

        $code_str = trim(get2('code_str'));

        if(!$code_str)return false;

        $list = explode('#', $code_str);

        foreach ($list as $l) {
            $data = explode(',', $l);

            $main_coupon_id     = (isset($data[0]))?$data[0]:null;
            $quantity           = (isset($data[1]))?$data[1]:null;
            $sub_coupon_id      = (isset($data[2]))?$data[2]:null;
            $guige_ids          = (isset($data[4]))?$data[4]:null;

        }
       

        $coupon = $this->loadModel('coupons')->get($main_coupon_id);
      
        if($coupon['isInManagement']==1&&$userId!=UBONUSSHOPID){
            if($this->specialEventTimeCheck(false))return false;
            if($this->specialEventTotalQtyLimitCheck($userId,'add',false))return false;
        }

        if($this->freeProductPurcheseLimitCheck($userId,$main_coupon_id,'add',false))return false;

        $mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');

        foreach ($list as $l) {
            $data = explode(',', $l);

            $main_coupon_id     = (isset($data[0]))?$data[0]:null;
            $sub_coupon_id      = (isset($data[1]))?$data[1]:null;
            $quantity           = (isset($data[2]))?$data[2]:null;
            $guige_ids          = (isset($data[4]))?$data[4]:null;


            $process = new AddCartProcess();

            $process->owner($userId); 

            $process->qty($quantity)->add($main_coupon_id,$sub_coupon_id,$guige_ids);
        }
  	}
	function showcart_action(){
		
		
		$this->setData(get2('dy'),'dy');
		if(!$this->loginUser['person_last_name'] && !$this->loginUser['person_first_name']) { //如果根本没有用户信息
			
			if(strpos($this->loginUser['name'],' ') != false){  // 且找到用户名中包含空格 
			
			   $this->loginUser['name'] =str_replace ("'","",$this->loginUser['name']);
			   $pos =strpos($this->loginUser['name'],' ');
			   
			   $this->loginUser['person_last_name']=substr($this->loginUser['name'],0,$pos);
			   $this->loginUser['person_first_name']=substr($this->loginUser['name'],$pos+1);
		       $this->loadModel('user')->update(array('person_last_name'=>$this->loginUser['person_last_name'],'person_first_name'=>$this->loginUser['person_first_name']),$this->loginUser['id']);
			
			   $this->setData($this->loginUser,'loginUser');
			}
			
		}
		/**
		 * listen var code_str to add cart on arrive shopping cart
		 */
		 
		 //$this->form_response_msg((string)$this->lang->wx_panding_fail_later);
		$this->addCarts();

		/**
		 * group pin and group buy can add cart with code only
		 */
		
		$this->showcart_specialCheckout();


		$mdl_wj_user_temp_carts = $this->loadModel( 'wj_user_temp_carts' );
	    $mdl_freshfood_disp_centre_suppliers=$this->loadModel('freshfood_disp_centre_suppliers');
		$mdl_user=$this->loadModel('user');

		$business_userid = (int)get2('business_userid');
        $id =$business_userid; // 因为取配送日期需要这个id
		if(!$business_userid){
			$business_userid=0;
		}else{
			$sql ="select DISTINCT business_id as businessUserId from cc_freshfood_disp_centre_suppliers where suppliers_id =$business_userid";
		
			$dispaitch_business = $mdl_freshfood_disp_centre_suppliers->getListBySql($sql);
			if($dispaitch_business ) { 
			 $dispaitch_business_id =$dispaitch_business[0]['businessUserId'];
			// var_dump($dispaitch_business_id);exit;
			}
			$this->setData('true','showSingleBusiness');
		}


        $factory_id = (int)get2('factory_id');
		if($factory_id) {
            $this->setData($factory_id,'factory_id');
        }

		/**
		 * 购物车内容
		 */

		$cartItems=$mdl_wj_user_temp_carts->getDetailedItem($this->loginUser['id'], $business_userid,$this->getLangStr());
		
		
		// 判断是否为多个商家组检出 ， cartitems的数量是独立商家的数量， 有些商家（如freshfood)是可以在统配中心集中出货，所以可以同时检出， 
		// 因此 ，系统检查该数组中多个商家是否为统一出货，如果都是统一出货，则标记为单商家购物车检出，
		// 不需要弹出选择检出商家框。
		
		$uni_business =array();
		foreach ($cartItems as $key => $value) {
			 $business_id= $cartItems[$key]['businessUserId'];
			 $sql ="select DISTINCT business_id as businessUserId from cc_freshfood_disp_centre_suppliers where suppliers_id =$business_id";
			// var_dump($sql);
			 	$dispaitch_business = $mdl_freshfood_disp_centre_suppliers->getListBySql($sql);
				if($dispaitch_business ) {
					
					 array_push($uni_business, $dispaitch_business[0]['businessUserId']);
					 $cartItems[$key]['businessUserName'] = $mdl_user->getBusinessDisplayName($dispaitch_business[0]['businessUserId'],$this->getLangStr()).' ('. $cartItems[$key]['businessUserName'].')';
				}else{
					 // 如果独立检出的商家 ，加入数组
					 array_push($uni_business, $business_id);
				}
				//去掉重复的独立商家
				$uni_business = array_unique($uni_business);
			 
		}
		
		//**  计算当前购物车中有多少个独立的checkout 商家结束
		
		
		
	    //var_dump(sizeof($uni_business));exit;
		if(sizeof($uni_business)>1){
			$this->setData('true','mixedItemFromBusinesses');
		};

		//统配商家加入递送时间
		$this->loadModel('freshfood_disp_suppliers_schedule');
		if (in_array($business_userid, DispCenter::getSupplierList())) {
			$dispCenterUserSelectedDeliveryDate = $this->cookie->getCookie('DispCenterUserSelectedDeliveryDate');
//var_dump($dispCenterUserSelectedDeliveryDate);exit;
			$parts = explode("@", $dispCenterUserSelectedDeliveryDate);
			$dateTimestamp = $parts[0];
			$timeType = $parts[1];

			$displayStr = date('D, F j, Y', $dateTimestamp);
			switch ($timeType) {
				case 'morning':
					$displayStr .= " 8:00 am - 14:00 pm";
					break;
				case 'afternoon':
					$displayStr .= " 14:00 pm - 20:00 pm";
					break;
				case 'anytime':
				//	$displayStr .= " 8:00 am - 20:00 pm";
					break;
				
			}
			$this->setData($dispCenterUserSelectedDeliveryDate, 'dispCenterUserSelectedDeliveryDate');
			$this->setData($displayStr, 'dispCenterUserSelectedDeliveryDate_display');
		}
		
		$processingBusinessUserId =$cartItems[0]['businessUserId'];
		$this->setData($processingBusinessUserId,'processingBusinessUserId');


        //获取用户是否已经被商家approve ,如果 approve ,收货后2周内支付； 如果cod ,为 货到付款 cod Cash on Delivery .
        // 未approve 或没有账户， 收到款后安排支付。

        $mdl_user_factory = $this->loadModel('user_factory');
      //  var_dump($processingBusinessUserId);exit;
        $accountType = $mdl_user_factory->getAccountType($this->loginUser['id'],$processingBusinessUserId);
       // var_dump($accountType);exit;
        $this->setData($accountType,'accountType');
		/**
		 * 登录用户的送货地址
		 */
		$mdl_wj_user_delivery_info= $this->loadModel('wj_user_delivery_info');
		$where =array(
				'userId'=>$this->loginUser['id'],
				'isDefaultAddress'=>1
		);
		$wj_user_delivery_info = $mdl_wj_user_delivery_info->getbyWhere($where);
		$this->setData( $wj_user_delivery_info, 'delivery_info' );

		if(!$wj_user_delivery_info){
			
			$wj_user_delivery_info=array(
					'userId'=>$this->loginUser['id'],
                    'displayName'=>$this->loginUser['displayName'],
					'first_name'=>$this->loginUser['person_first_name'],
					'last_name'=>$this->loginUser['person_last_name'],
					'address'=>$this->loginUser['googleMap'],
					'phone'=>$this->loginUser['phone'],
					'email'=>$this->loginUser['email'],
					'addrPost'=>$this->loginUser['addrPost'],
					'country'=>$this->loginUser['country'],
					'createTime'=>time()
			);
		}

		$where =array(
				'userId'=>$this->loginUser['id'],
		);
		$wj_user_delivery_info_list = $mdl_wj_user_delivery_info->getList(null,$where);
       // var_dump($wj_user_delivery_info_list);exit;
		$this->setData( $wj_user_delivery_info_list,'delivery_info_list');
		
		// 获取该商家可用邮编
		
		// 如果是统配商家，获得统配商家的邮编列表
		if ($dispaitch_business_id) {
			//获得统配商家邮件列表
			$where =array(
				'business_userId'=>$dispaitch_business_id,
		);
		}else{
			// 获得单独商家邮编列表
			$where =array(
				'business_userId'=>$business_userid,
		);
			
		}
		
		
		$mdl_local_delivery_postcodes= $this->loadModel('local_delivery_postcodes');
		$local_delivery_postcodes_list = $mdl_local_delivery_postcodes->getList(null,$where);
		
		//$postcode_arr=array();
		
		foreach ($local_delivery_postcodes_list as $key => $value) {
			//array_push($postcode_arr,$value['postcode']);
			if($key==0) {
				
				$postcodes=$value['postcode'];
			}else{
				$postcodes.=','.$value['postcode'];
			}
			
		}
       
		$this->setData( $postcodes,'avaliable_postcodes');
		// $postcode_arr1=explode(',', $postcode_arr);
		// var_dump ($postcodes);exit;
		/**
		 * 用户的可用钱包余额
		 */
		$moneyBalance=$this->loadModel('recharge')->getBalanceOfUser($this->loginUser['id']);
		if($moneyBalance<0)$moneyBalance =0;
		$this->setData($moneyBalance,'moneyBalance');


		/**
		 * 产品的预定费。 预定费需要基于产品设定,如果是统配中心，更换为统配中心的booking fee
		 */
		 
		if($dispaitch_business_id){
			
			$this->setData($this->loadModel('user')->getBookingFee($dispaitch_business_id),'bookingfee');
			$this->setData($this->loadModel('user')->getBookingFeeType($dispaitch_business_id),'bookingfeetype');
			
		}else{
			$this->setData($this->loadModel('user')->getBookingFee($processingBusinessUserId),'bookingfee');
			$this->setData($this->loadModel('user')->getBookingFeeType($processingBusinessUserId),'bookingfeetype');
			
			
		}
		

		/**
		 * 检测是否有可用优惠吗。 由于Globalcode的存在。 所有产品都有可用优惠码
		 */
		//$this->setData($this->loadModel( 'wj_promotion_code' )->promotionExist($this->loginUser['id'],$processingBusinessUserId),'promotion');
		
		
		$this->setData(true,'promotion');
		
		// 获取某个用户的可用优惠码，只拿通用的。
		
		$promotion_code_list = $this->loadModel( 'wj_promotion_code' )->getPromotionCodeList($this->loginUser['id'],$processingBusinessUserId);
		$this->setData($promotion_code_list,'promotion_code_list');
		//var_dump ($promotion_code_list);exit;


		/**
		 * 商家信息
		 */
		 if($dispaitch_business_id){
			$this->setData($this->loadModel('user')->get($dispaitch_business_id),'businessUser');
		 }else{
			$this->setData($this->loadModel('user')->get($processingBusinessUserId),'businessUser');
		 }

		/**
		 * 商家的递送规则
		 * @var [type]
		 */
		 if($dispaitch_business_id){
			$business_delivery_info =$this->loadModel('user')->getBusinessDeliveryInfo($dispaitch_business_id,$this->getLangStr());
		 }else{
			$business_delivery_info =$this->loadModel('user')->getBusinessDeliveryInfo($processingBusinessUserId,$this->getLangStr());
		 }
		
		//var_dump($dispaitch_business_id);exit;
		/**
		 * 全局控制递送方式显示与否的开关
		 */
		// var_dump( $cartItems[0]);
		if($mdl_wj_user_temp_carts->allItemsAreEvoucher($cartItems[0]['items'])){
			//如果检出的全部产品为 evoucher, 购物车不需要显示递送方式
			$business_delivery_info['EvoucherOrrealproduct']='evoucher';
             
			// var_dump($business_delivery_info['EvoucherOrrealproduct']);exit;
		}else{
			$business_delivery_info['EvoucherOrrealproduct']='realproduct';

			//开启递送选项
			$business_delivery_info['deliver_enable']
			=($business_delivery_info['deliver_enable']&&$mdl_wj_user_temp_carts->itemsHasDeliverAvaliable($cartItems[0]['items']));

			//开启自取选项
			$business_delivery_info['pickup_enable']
			=($business_delivery_info['pickup_enable']&&$mdl_wj_user_temp_carts->itemsHasPickupAvaliable($cartItems[0]['items']));
		}
		
		
		$this->setData( $business_delivery_info, 'business_delivery_info' );


		/**
		 * 一个商家只能有一套自取点。不支持自取的产品需要在用户选择自取的时候标红剔除。
		 */
		if($dispaitch_business_id){ 
		    $sql ="select id as sales_user_list,contactPersonNickName,googleMap,contactMobile from cc_user where user_belong_to_user= $dispaitch_business_id and role=5";
			//var_dump ($sql);exit;
			$staff_value_arr =$this->loadModel('user')->getListBySql($sql);
			//var_dump($staff_value_arr);exit;
			$staff_list = $this->loadModel('user')->findCommonStaff($staff_value_arr);
		}else{
			$staff_list = $this->loadModel('user')->findCommonStaff($cartItems[0]['items']);
		}
		
		$this->setData($staff_list,'staff_list');
		
		
		
		//***************************
		// 如果商家为生鲜商家，则 返回编辑直接跳回到生鲜商家的购买页面，二不是用默认退回
		$business_info =$this->loadModel('user')->get($cartItems[0]['businessUserId']) ;
		//var_dump($business_info);exit;
		if($business_info) {
			if($business_info['business_type_freshfood'] ==1) {
				$this->setData($business_info['id'],'freshfood');
			}
		}
		
		
		
		
		

        $this->loadModel('freshfood_disp_suppliers_schedule');
        $businessDispSchedule = DispCenter::getBusinessDispSchedule($id);
        $businessDispScheduleFilledWithContinueDates = DispCenter::getFollowingNDaysIncludeAvailableDeliver($businessDispSchedule);

        //客户定制日期过滤
        $businessDispScheduleFilledWithContinueDates =$this->loadModel('user_factory')->filiterUserAvaliableDeliveryDate($businessDispScheduleFilledWithContinueDates,$this->loginUser['id'],$id);

        //获得订货调度的具体日期及星期几


        $businessDispScheduleFilledWithContinueDates=$this->getWeekDayandDateOfSchedue($businessDispScheduleFilledWithContinueDates);



        $this->setData(json_encode($businessDispScheduleFilledWithContinueDates), 'businessDispSchedule');
        $this->setData(in_array($id, DispCenter::getSupplierList()), 'isDispCenterBusiness');

        $this->setData(join(DispCenter::getPostcode(DispCenter::getDispCenterIdOfSupplier($id)), ','), 'postcodeSupported'); //使用统配商家邮编信息
     //  var_dump(json_encode($businessDispScheduleFilledWithContinueDates));exit;




        //获得当前店铺得 订货时间
        $mdl = $this->loadModel('freshfood_disp_suppliers_schedule');
        $where = [];
        $where['business_id'] = $id;
        $where['centre_business_id'] = DispCenter::getDispCenterIdOfSupplier($id);
        $list = $mdl->getList(null, $where);



        foreach ($list as $key =>$value) {
            $order_start_of_date_cn = $this->get_cn_weekdayName_from_en_weekdayName($value['order_start_of_date']);
            $order_cut_of_date_cn = $this->get_cn_weekdayName_from_en_weekdayName($value['order_cut_of_date']);
            $delivery_date_of_week_cn = $this->get_cn_weekdayName_from_en_weekdayName($value['delivery_date_of_week']);

            $list[$key]['order_start_of_date_cn'] = $order_start_of_date_cn ;
            $list[$key]['order_cut_of_date'] =$order_cut_of_date_cn  ;
            $list[$key]['delivery_date_of_week_cn'] = $delivery_date_of_week_cn ;
            $list[$key]['ordertime_cn'] = '从'.$order_start_of_date_cn.$value['order_start_of_time'] .' 到 '.$order_cut_of_date_cn.$value['order_cut_of_time'];
            $list[$key]['ordertime'] = 'From '.$value['order_start_of_date'].' '.$value['order_start_of_time'] .' To '.$value['order_cut_of_date'].' '.$value['order_cut_of_time'];

        }




        $this->setData(json_encode($list),'current_business_tuangou_time');


		
		
		
		
		
		
		
		


		/**
		 * 预留的控制 输入身份证号提示的开关
		 */
		$this->setData(false,'requireIdCard');
		
		/**
		 * 是否有存储的信用卡可用
		 */
		$this->setData($this->loadModel('user')->get_card_on_file($this->loginUser['id']),'card_on_file');

		/**
		 * 默认使用上一次消费的支付方式
		 */
		$this->setData($this->loadModel('order')->getLastOrderPaymentMethod($this->loginUser['id']),'lastOrderPaymentMethod');
		
		/**
		 * 是否是第一次消费，第一次超过100无法使用信用卡支付
		 */

		$this->setData((count($this->loadModel('order')->getOrderListOfCustomer($this->loginUser['id'])) === 0), 'isFirstTimeBuyer');

		$this->setData( $cartItems, 'data' );

		$this->setData($this->parseUrl()->setPath('member/delete_cart_item')->set('id'),'deleteSingleUrl');

		$this->setData( (string)$this->lang->shopping_cart, 'pagename' );
		$this->setData( (string)$this->lang->shopping_cart.'- '.$this->site['pageTitle'], 'pageTitle' );

		$this->display_pc_mobile('mobile/show_cart','mobile/show_cart');

	}


	
	function unbind_wx_action() {
		if ( is_post() ) {
			$mdl_user = $this->loadModel( 'user' );

			$data=array();
			$data['wx_openID']='';
			$data['IsTransform']=0;   //消息推送由小微将代收

			if ( $mdl_user->updateUserById( $data, $this->loginUser['id'] ) ) {
				$this->form_response(200,(string)$this->lang->wx_panding_success,'SELF');
			}else{
				$this->form_response_msg((string)$this->lang->wx_panding_fail_later);
			}
		}
	}

	function wx_manage_account_action(){
		$mdl_user=$this->loadModel('user');
		if ( is_post() ) {
			//reset all;
			$data['isDefaultWxBind']=false;
			$where['wx_openID']= $this->loginUser['wx_openID'];
			$mdl_user->updateByWhere($data,$where);

			if(post('defaultWxloginAccount')){
				$data['isDefaultWxBind']=true;
				$where['wx_openID']= $this->loginUser['wx_openID'];
				$where['name']=reset(post('defaultWxloginAccount'));
				$mdl_user->updateByWhere($data,$where);
			}
			$this->form_response_msg((string)$this->lang->update_success);
		}else {
			if($this->loginUser['wx_openID']){
				$where['wx_openID']= $this->loginUser['wx_openID'];
				$list=$mdl_user->getList(null,$where);
				$this->setData( $list, 'userList' );

				$default = $mdl_user->hasDefaultWxBind($this->loginUser['wx_openID']);
				$this->setData($default,'selected');
			}

			$this->setData(get2('side'),'side');
			$this->setData( 'wx_manage_account','menu' );
			$this->setData( 'wx_manage_account','submenu' );
			$this->setData( '管理微信账户'.$this->site['pageTitle'], 'pageTitle' );

			$this->display( 'member/wx_manage_account' );
		}

		
	}

	function card_on_file_action()
	{
		$card_number = $this->loadModel('user')->get_card_on_file($this->loginUser['id']);

		$this->setData($card_number,'card_number');
		
		$this->setData( 'user_setting','menu' );
		$this->setData( 'card_on_file', 'submenu' );
		$this->setData( 'Credit Card'.$this->site['pageTitle'], 'pageTitle' );

		$this->display('member/card_on_file');
	}

	function card_on_file_clear_action()
	{
		$this->loadModel('user')->clear_card($this->loginUser['id']);

		$this->sheader(HTTP_ROOT_WWW.'member/card_on_file');
	}
	
	function wx_redirect_action(){		
		$url =HTTP_ROOT_WX."member/wx_register?returnUrl=".urlencode($this->returnUrl);

		$query = array(
				'appid' => 'wx7a7df86983f3dc7f',
				'redirect_uri' =>$url,
				'response_type' => 'code',
				'scope' => 'snsapi_userinfo',
				'state' => 1
				);
			
		$query = http_build_query( $query );
		$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?'.$query.'#wechat_redirect';

		$this->sheader( $url );
	}

	function authorize_pcscan_login_action(){
		$authorizeId = get2('authorizeId');

		if ($authorizeId){
			$this->loadModel('wj_pc_weixin_login_temp_info')->notify($authorizeId,$this->loginUser['id']);

			$this->setData('success','result');
			$this->setData( 'Login Success - '.$this->site['pageTitle'], 'pageTitle' );
		}else{
			$this->setData('fail','result');
			$this->setData( 'Login Failed - '.$this->site['pageTitle'], 'pageTitle' );
		}
		$this->display('member/authorize_pcscan_login');
			
	}

	 protected function saveWxAvater($imageUrl){
	  	//头像下载
		require_once "wx/wxjssdk.php";
		$filepath = date( 'Y-m' );
		$this->file->createdir( 'data/upload/'.$filepath );
		$avatar = $filepath.'/'.date( 'YmdHis' ).$this->createRnd().'.jpg';
		file_put_contents( UPDATE_DIR.$avatar, https_request( $imageUrl ) );

		return $avatar;
	  }
/*
	function wx_register_action(){
	        if($this->loginUser){
				$this->sheader(HTTP_ROOT.$this->returnUrl);
	        }

	        if( !$this->wx_openID){
	        	$this->sheader(null,'Can not find OpenID code：'. $this->wx_auth_code);
	        }

			if (  !$this->loginUser && $this->wx_openID ) {
				//没有注册，并且来自微信，则显示选项：选项一，已注册用户，输入email进行绑定，选项二，没有注册用户，进入注册页面，注册后自动绑定，选项三，使用微信登录
				$this->setData( $this->wx_auth_code, 'code' );
				$this->setData( urlencode( $_SERVER['REQUEST_URI'] ), 'currentUrl' );
				
				$mdl_user = $this->loadModel( 'user' );

				$initPassowrd = $this->createRnd();

				$userObject = new User();
				$userObject->setOpenID($this->wx_openID);
				$userObject->setPassword($this->md5($initPassowrd));
				$userObject->setInitPassowrd($initPassowrd);


				require_once "wx/wxjssdk.php";
				$userinfo = getUserInfor( $this->wx_openID );
				if($userinfo!='unsuscribe'){
					$userObject->setAvater($this->saveWxAvater($userinfo["headimgurl"]));
					$userObject->setNickName($userinfo['nickname']);
				}


				$username='User';
				while($mdl_user->getCount( "name='$username'" ) > 0) {
					$randnumber =rand(1000,9099);
					$username .=$randnumber; // append 3 digit until a new one
				}
				
				$userObject->setName($username);
				
				$new_id=$mdl_user->insert($userObject->toDBArray());

				if ( $new_id ) {
					$openUser = $mdl_user->get(  $new_id );
			
					$mdl_referrals = $this->loadModel( 'referrals' );

					$ref = $mdl_referrals->getByWhere( array( 'userId' => $new_id ) );
					if ( $ref )
						$this->sheader( $this->parseUrl()->setPath('referal/index') );

					$info = array(
						'name' => $username,
						'createTime ' => time()
					);

					if ( $mdl_referrals->join($new_id, $info ) ) {
						$mdl_referrals->setApprove($new_id);
					}
					
					$this->session( 'wx_openID', $this->wx_openID );
					
					$this->session( 'member_user_id', $openUser['id'] );
					$this->session( 'member_user_shell', $this->md5( $openUser['id'].$openUser['name'].$openUser['password'] ) );

					$this->cookie->setCookie( 'remember_user_id', $openUser['id'], 60 * 60 * 24 * 365 );
					$this->cookie->setCookie( 'remember_user_shell', $this->md5( $openUser['id'].$openUser['name'].$openUser['password'] ), 60 * 60 * 24 * 365 );
					
					$this->setData($openUser,'openUser');

				
					//skip register user info display member/showcart
					if(strpos($this->returnUrl,'lottery/lottery')!==false||strpos($this->returnUrl,'member/showcart')!==false){
						$this->sheader(HTTP_ROOT.$this->returnUrl);
					}else{
						$this->display('mobile/member/wx_register');
					}
					
				}
				else {
					$this->sheader(null,'wechat login Failed !');
				}
			}
	}
*/
	public function wx_register_send_email_action()
	{	
		$email=post('email');

		if($this->loginUser&&$email){
			$currentUserId = $this->loginUser['id'];

			$this->loadModel('user')->update(array('email'=>$email),$currentUserId);

			$this->loadModel('system_mail_queue')->add($currentUserId,EmailType::CustomerRegistryNotification);
		}

		$this->sheader(HTTP_ROOT_WX.$this->returnUrl);


	}


	function multiple_wx_login_action(){

		if(is_post()){
			$name = trim(post( 'name' ));
			$pwd = trim(post( 'pwd' ));

			$mdl_user = $this->loadModel( 'user' );
			$user = $mdl_user->getUserByName( $name );
			if ( !$user ) $user = $mdl_user->getByWhere( array( 'email' => $name ) );

			if ( $user ) {
				if ( $pwd == $user['password'] ) {
					if ( ! $user['isApproved'] ) {
						echo 'Member can not login in .';
						exit;
					}
					if ( $user['isSuspended'] ) {
						echo 'Member suspended please contact Ubonus ';
						exit;
					}
				

					$data = array(
						'lastLoginIP'	=> ip(),
						'lastLoginDate'	=> time(),
						'loginCount'	=> $user['loginCount'] + 1
					);
						
					$mdl_user->updateUserById( $data, $user['id'] );

					$this->session( 'member_user_id', $user['id'] );
					$this->session( 'member_user_shell', $this->md5( $user['id'].$user['name'].$user['password'] ) );
                    $this->setCustomerTrueLogin($user['role']);
					$this->sheader(HTTP_ROOT_WX.$this->returnUrl);
				}
				else {
					echo (string)$this->lang->username_or_password_error;
					exit;
				}
			}
			else {
				echo (string)$this->lang->username_or_password_error;
				exit;
			}
		}else{
			//multiple user login
		/*
			if(get2('openId')){
				$where['wx_openID']=get2('openId');
				$accounts =$this->loadModel('user')->getList(array('id','name','password'),$where);

				$this->setData($accounts,'accounts');
				$this->display('member/multiple_wx_login');
			}
		*/
		}
		
	}
	function mobileonly_login_action (){
		if ( is_post ()) {
			$mobile = trim(post( 'mobile' ));
			$code = trim(post( 'code' ));
			$remember = (int)post( 'remember' );


			if ( empty($mobile) ) $this->form_response_msg((string)$this->lang->input_mobile_number);

			if ( empty($code) ) $this->form_response_msg((string)$this->lang->input_verify_code);

			$mdl_user = $this->loadModel( 'user' );
			$user = $mdl_user->getUserByName( $mobile );

			if ( !$user ) $this->form_response_msg((string)$this->lang->mobile_number_not_exist);

			if($mobile!=$_SESSION['sms_verification_mobile'] || $code!=$_SESSION['sms_verification_code'])
				$this->form_response_msg((string)$this->lang->remind_user_register_13);

			if ( !$user['isApproved'] ) $this->form_response_msg('Member is not approved by system ,can not login!');
			if ( $user['isSuspended'] ) $this->form_response_msg('Member is suspend ,please contact ubonus');

			$data = array(
				'lastLoginIP'	=> ip(),
				'lastLoginDate'	=> time(),
				'loginCount'	=> $user['loginCount'] + 1
			);

			$mdl_user->updateUserById( $data, $user['id'] );
            $this->setCustomerTrueLogin($user['role']);
			$this->session( 'member_user_id', $user['id'] );
			$this->session( 'member_user_shell', $this->md5( $user['id'].$user['name'].$user['password'] ) );

            $this->setCustomerTrueLogin($user['role']);

			$this->cookie->setCookie( 'remember', $remember, 60 * 60 * 24 * 365 );
			if ( $remember ) {
				$this->cookie->setCookie( 'remember_user_id', $user['id'], 60 * 60 * 24 * 365 );
				$this->cookie->setCookie( 'remember_user_shell', $this->md5( $user['id'].$user['name'].$user['password'] ), 60 * 60 * 24 * 365 );
			}
			$this->form_response(200,'',HTTP_ROOT.$this->returnUrl);
		}
	}
	

	function login_action() {
		if ( is_post() ) {

			$name = trim(post( 'name' ));
			$pwd = trim(post( 'pwd' ));
			$remember = (int)post( 'remember' );

			
			if ( empty($name) ) $this->form_response_msg((string)$this->lang->please_input_username);

			if ( empty($pwd) ) $this->form_response_msg((string)$this->lang->please_input_password);


			if(strlen($pwd)<=16) {
				$passwordByCustomMd5 = $this->md5( $pwd );
			}else{
				$passwordByCustomMd5 = $pwd;
			}

			$mdl_user = $this->loadModel( 'user' );
			$user = $mdl_user->getUserByName( $name );

			if ( !$user ) $this->form_response_msg((string)$this->lang->username_or_password_error);

			if ( $passwordByCustomMd5 != $user['password'] ) $this->form_response_msg((string)$this->lang->username_or_password_error);

			if ( ! $user['isApproved'] ) $this->form_response_msg('Member is not approved by system ,can not login!');
			
			if ( $user['isSuspended'] ) $this->form_response_msg('Member is suspend ,please contact ubonus');

			$data = array(
				'lastLoginIP'	=> ip(),
				'lastLoginDate'	=> time(),
				'loginCount'	=> $user['loginCount'] + 1
			);
			
		    
			// 判断当前用户是否为华姐报名,如果是则转向公司页面,并转向miss页面
			$mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');
			$sql22= "select * from cc_wj_customer_coupon where userId=".$user['id']. " and bonus_id=7176 ";
			$miss= $mdl_wj_customer_coupon->getListBySql($sql22);


			//end

			$mdl_user->updateUserById( $data, $user['id'] );

			$this->session( 'member_user_id', $user['id'] );
			$this->session( 'member_user_shell', $this->md5( $user['id'].$user['name'].$user['password'] ) );
           
			$this->cookie->setCookie( 'remember', $remember, 60 * 60 * 24 * 365 );

			if ( $remember ) {
				$this->cookie->setCookie( 'remember_user_id', $user['id'], 60 * 60 * 24 * 365 );
				$this->cookie->setCookie( 'remember_user_shell', $this->md5( $user['id'].$user['name'].$user['password'] ), 60 * 60 * 24 * 365 );
			}

			$redirect_uri = HTTP_ROOT_WWW;
            // if the value of role of loginuser equel 4 ,then set up truelogin =1 ,menns its a customer login from login page
            $this->setCustomerTrueLogin($user['role']);
          //  var_dump($this->returnUrl);exit;
			if(($this->returnUrl) && ($this->returnUrl !='/member/index')){
				$redirect_uri = HTTP_ROOT.$this->returnUrl;
			}elseif($user['role']==3 || $user['role']==6 || $user['role']==20 ){

					$redirect_uri = HTTP_ROOT_WWW.'company/index';

			}elseif($user['role']==4){


					$redirect_uri = HTTP_ROOT_WWW.'member/index';

				
			}elseif($user['role']==101){
				
					$redirect_uri = HTTP_ROOT_WWW.'factory/index?sales=1';
				
				
			}elseif($user['role']==6){
				
					$redirect_uri = HTTP_ROOT_WWW.'company/index';
				
				
			}
			
			$this->form_response(200,'',$redirect_uri);
			
			
		}
		else {
			if($this->loginUser){
				$this->sheader( HTTP_ROOT.$this->returnUrl );
			}

			$ua =$this->getUserDevice();
		/*	if ($ua=='wechat') {

					$new_url =HTTP_ROOT_WX."member/wx_register?returnUrl=".urlencode($this->returnUrl);
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
			}else{ */

				$newid =$this->loadModel('wj_pc_weixin_login_temp_info')->init();

				if ($newid){
					$this->setData(PcLoginQRCode($this->returnUrl,$newid),'loginQrcode');
					$this->setData($newid,'pc_login_id');
				}

				$this->setData( $this->lang->log_in.' - '.$ua. $this->site['pageTitle'], 'pageTitle' );
				$this->display( 'welcome/login' );
				
			/*} */
		}
	}

	function logout_action() {
		$this->session( 'member_user_id', null );
		$this->session( 'member_user_shell', null );
        $this->session( 'truelogin', null );
		$this->cookie->clearCookie( 'wx_openID' );
		$this->cookie->clearCookie( 'remember' );
		$this->cookie->clearCookie( 'remember_user_id' );
		$this->cookie->clearCookie( 'remember_user_shell' );
		$this->sheader( HTTP_ROOT_WWW );
	}
	function register_business_action(){

		$mdl_reg = $this->loadModel( 'reg' );
		$mdl_user = $this->loadModel( 'user' );

		if(is_post()){

			$mobile=trim(post('mobile'));
			$code =trim(post('code'));
			$agree = (int)post( 'agree' );

			$password = trim( post( 'password' ) );
			$passwordAgain = trim( post( 'passwordAgain' ) );

			$full_number = trim(post('full_number'));

			$email=trim(post('email'));
			$business_legal_name=trim(post('business_legal_name'));

			if (!$mobile ) 
				$this->form_response_msg((string)$this->lang->input_mobile_number);

			if($mdl_user->getCount( "name='$mobile'" ) > 0)
				$this->form_response_msg((string)$this->lang->remind_user_register_14);

			if ( $password != $passwordAgain ) 
				$this->form_response_msg((string)$this->lang->remind_user_register_7);
			
			if ( ! $mdl_reg->chkPassword( $password ) ) 
				$this->form_response_msg((string)$this->lang->remind_user_register_8);

			if($mobile!=$_SESSION['sms_verification_mobile'])
				$this->form_response_msg((string)$this->lang->remind_user_register_13);

			if($code!=$_SESSION['sms_verification_code'])
				$this->form_response_msg((string)$this->lang->remind_user_register_13);
			
			if ( ! $agree ) 
				$this->form_response_msg((string)$this->lang->remind_user_register_6);

			if ( ! $email ) 
				$this->form_response_msg((string)$this->lang->wrong_email);

			if ( ! $business_legal_name ) 
				$this->form_response_msg((string)$this->lang->input_company_name);

			$initPassowrd = $password;

			$userObject = new User();
			$userObject->setPassword($this->md5($initPassowrd));
			$userObject->setInitPassowrd($initPassowrd);


			$username=$mobile;
			
			$userObject->setName($username);

			$userObject->setBusinessMobile($mobile,true);
			$userObject->setRole(3);

			$userObject->setEmail($email);
			$userObject->setLegalName($business_legal_name);

			$mdl_user->begin();
			$mdl_user->addUser($userObject->toDBArray());

			$user = $mdl_user->getUserByName( $username );

			$mdl_user->setTrustLevel(1,$user['id']);
			
			
			//申请会员后自动成为明星店铺
			
			
			$mdl_referrals = $this->loadModel( 'referrals' );

			$ref = $mdl_referrals->getByWhere( array( 'userId' => $user['id'] ) );
			if ( $ref ) $this->sheader( $this->parseUrl()->setPath('referal/index') );
			
			

			$info = array(
				'name' => $business_legal_name,
				'email' => $email,
				'phone' => $mobile,
				'createTime ' => time()
			);

			if ( $mdl_referrals->join($user['id'], $info ) ) {
				$mdl_referrals->setApprove($user['id']);
				
			}
			else {
				
			}
			
			
			
			
			if ( $mdl_user->errno() ) {
				$mdl_user->rollback();
				$this->form_response_msg((string)$this->lang->remind_user_register_11);
			}
			else {
				$mdl_user->commit();

				$this->session( 'member_user_id', $user['id'] );
				$this->session( 'member_user_shell', $this->md5( $user['id'].$user['name'].$user['password'] ) );
				
				$this->form_response(200,(string)$this->lang->remind_user_register_12,HTTP_ROOT_WWW.'company/index');
			}

		}else{
			$this->setData(get2('plan'),"businessPlan");

			$this->setData( $this->lang->register_new_business.' - '.$this->site['pageTitle'], 'pageTitle' );

			$this->display('member/register_business_new');
		}
	}
	function register_action() {
		if ( is_post() ) {
			
			$mdl_reg = $this->loadModel( 'reg' );
			$mdl_user = $this->loadModel( 'user' );

			$returnUrl=$this->returnUrl;

			switch (trim( get2( 'type' ) )) {
				case 'normal':
					$name = trim( post( 'name' ) );
					$password = trim( post( 'password' ) );
					$passwordAgain = trim( post( 'passwordAgain' ) );
					$agree = (int)post( 'agree' );

					
					if ($mdl_reg->chkMail( $name ) ) 
						$this->form_response_msg((string)$this->lang->remind_user_register_3);

					if ($mdl_reg->chkPhone( '0' . substr($name, -9) ) )
						$this->form_response_msg((string)$this->lang->remind_user_register_4);

					if ( ! $mdl_reg->chkUsername( $name ) ) 
						$this->form_response_msg((string)$this->lang->remind_user_register_5);

					if ( ! $agree ) 
						$this->form_response_msg((string)$this->lang->remind_user_register_6);
					
					if ( $password != $passwordAgain ) 
						$this->form_response_msg((string)$this->lang->remind_user_register_7);
					
					if ( ! $mdl_reg->chkPassword( $password ) ) 
						$this->form_response_msg((string)$this->lang->remind_user_register_8);
					
					if ( $mdl_user->getCount( " name='$name'" ) > 0 ) 
						$this->form_response_msg((string)$this->lang->remind_user_register_2);
					
					//if(!verify_recaptcha(post('g-recaptcha-response')))
						//$this->form_response_msg((string)$this->lang->remind_user_register_9);


					$passwordByCustomMd5 = $this->md5( $password );

					$userObject = new User();
					$userObject->setName($name);
					$userObject->setPassword($passwordByCustomMd5);


					$mdl_user->begin();
					$mdl_user->addUser($userObject->toDBArray());
					$user = $mdl_user->getUserByName( $name );
					
					if ( $mdl_user->errno() ) {
						$mdl_user->rollback();
						$this->form_response_msg((string)$this->lang->remind_user_register_11);
					}
					else {
						$mdl_user->commit();
                          $this->add_referrals ($name,$email,$mobile,$user['id']);
                        $this->setCustomerTrueLogin($user['role']);
						$this->session( 'member_user_id', $user['id'] );
						$this->session( 'member_user_shell', $this->md5( $user['id'].$user['name'].$user['password'] ) );
						if($returnUrl){
							$this->form_response(200,(string)$this->lang->remind_user_register_12,HTTP_ROOT.$returnUrl);
						}else{
							$this->form_response(200,(string)$this->lang->remind_user_register_12,HTTP_ROOT_WWW.'member/index');
						}
					}


					break;
				
				case 'email':
					$email=trim(post('email'));
					$name =$email;
					$code =trim(post('code'));
					$agree = (int)post( 'agree' );

					$password = trim( post( 'password' ) );
					$passwordAgain = trim( post( 'passwordAgain' ) );

					if ( $mdl_user->getCount( " name='$email'" ) > 0 ) 
						$this->form_response_msg((string)$this->lang->remind_user_register_2);

					if ( $password != $passwordAgain ) 
						$this->form_response_msg((string)$this->lang->remind_user_register_7);
					
					if ( ! $mdl_reg->chkPassword( $password ) ) 
						$this->form_response_msg((string)$this->lang->remind_user_register_8);

					if($email!=$_SESSION['email_verification_email'])
						$this->form_response_msg((string)$this->lang->remind_user_register_13);

					if($code!=$_SESSION['email_verification_code'])
						$this->form_response_msg((string)$this->lang->remind_user_register_13);

					if ( ! $agree ) 
						$this->form_response_msg((string)$this->lang->remind_user_register_6);


					$initPassowrd = $password;

					$userObject = new User();
					$userObject->setPassword($this->md5($initPassowrd));
					$userObject->setInitPassowrd($initPassowrd);

					$userObject->setName($email);
					$userObject->setEmail($email);
					
					$mdl_user->begin();
					$mdl_user->addUser($userObject->toDBArray());

					$user = $mdl_user->getUserByName( $email );

					$mdl_user->setTrustLevel(1,$user['id']);
					
					if ( $mdl_user->errno() ) {
						$mdl_user->rollback();
						$this->form_response_msg((string)$this->lang->remind_user_register_11);
					}
					else {
						$mdl_user->commit();
                         $this->add_referrals ($name,$email,$mobile,$user['id']);
						$this->session( 'member_user_id', $user['id'] );
						$this->session( 'member_user_shell', $this->md5( $user['id'].$user['name'].$user['password'] ) );
                        $this->setCustomerTrueLogin($user['role']);
						
						$this->loadModel('system_mail_queue')->add($user['id'],EmailType::CustomerRegistryNotification);
						
						if($returnUrl){
							$this->form_response(200,(string)$this->lang->remind_user_register_12,HTTP_ROOT.$returnUrl);
						}else{
							$this->form_response(200,(string)$this->lang->remind_user_register_12,HTTP_ROOT_WWW.'member/index');
						}
					}

					break;

				case 'mobile':
					$mobile=trim(post('mobile'));
					$name =$mobile;
					$code =trim(post('code'));
					$agree = (int)post( 'agree' );

					$password = trim( post( 'password' ) );
					$passwordAgain = trim( post( 'passwordAgain' ) );

					$full_number = trim(post('full_number'));

					if($mdl_user->getCount( "name='$mobile'" ) > 0)
						$this->form_response_msg((string)$this->lang->remind_user_register_14);

					if ( $password != $passwordAgain ) 
						$this->form_response_msg((string)$this->lang->remind_user_register_7);
					
					if ( ! $mdl_reg->chkPassword( $password ) ) 
						$this->form_response_msg((string)$this->lang->remind_user_register_8);

					if($mobile!=$_SESSION['sms_verification_mobile'])
						$this->form_response_msg((string)$this->lang->remind_user_register_13);

					if($code!=$_SESSION['sms_verification_code'])
						$this->form_response_msg((string)$this->lang->remind_user_register_13);
	
					if ( ! $agree ) 
						$this->form_response_msg((string)$this->lang->remind_user_register_6);


					$initPassowrd = $password;

					$userObject = new User();
					$userObject->setPassword($this->md5($initPassowrd));
					$userObject->setInitPassowrd($initPassowrd);

					$username=$mobile;

					$userObject->setName($username);

					$userObject->setBusinessMobile($mobile,true);
					
					$mdl_user->begin();
					$mdl_user->addUser($userObject->toDBArray());

					$user = $mdl_user->getUserByName( $username );

					$mdl_user->setTrustLevel(1,$user['id']);
					
					if ( $mdl_user->errno() ) {
						$mdl_user->rollback();
						$this->form_response_msg((string)$this->lang->remind_user_register_11);
					}
					else {
						$mdl_user->commit();
                       
					     $this->add_referrals ($name,$email,$mobile,$user['id']);
						 
						$this->session( 'member_user_id', $user['id'] );
						$this->session( 'member_user_shell', $this->md5( $user['id'].$user['name'].$user['password'] ) );
                        $this->setCustomerTrueLogin($user['role']);
						
						if($returnUrl){
							$this->form_response(200,(string)$this->lang->remind_user_register_12,HTTP_ROOT.$returnUrl);
						}else{
							$this->form_response(200,(string)$this->lang->remind_user_register_12,HTTP_ROOT_WWW.'member/index');
						}
					}
					break;

				//仅手机和验证码注册
				case 'mobileonly':
					$mobile=trim(post('mobile'));
					$name =$mobile;
					$code =trim(post('code'));
					// $agree = (int)post( 'agree' );

					$full_number = trim(post('full_number'));

					if($mobile!=$_SESSION['sms_verification_mobile'])
						$this->form_response_msg((string)$this->lang->remind_user_register_13);

					if($code!=$_SESSION['sms_verification_code'])
						$this->form_response_msg((string)$this->lang->remind_user_register_13);
	
					// if ( ! $agree ) 
					// 	$this->form_response_msg((string)$this->lang->remind_user_register_6);

					if($mdl_user->getCount( "name='$mobile'" ) == 1) {
						//已经存在直接登陆
						$user = $mdl_user->getUserByName( $mobile );
						$this->session( 'member_user_id', $user['id'] );
						$this->session( 'member_user_shell', $this->md5( $user['id'].$user['name'].$user['password'] ) );
                        $this->setCustomerTrueLogin($user['role']);
						
						if($returnUrl){
							$this->form_response(200,(string)$this->lang->remind_user_register_12,HTTP_ROOT.$returnUrl);
						}else{
							$this->form_response(200,(string)$this->lang->remind_user_register_12,HTTP_ROOT_WWW.'member/index');
						}
					} elseif($mdl_user->getCount( "name='$mobile'" ) > 1) {
						$this->form_response_msg((string)$this->lang->remind_user_register_14);
					}

					if($mdl_user->getCount( "phone='$mobile'" ) == 1) {
						//找到账号唯一且手机号相同的账户
						$user = $mdl_user->getByWhere(['phone' => $mobile]);
						$this->session( 'member_user_id', $user['id'] );
						$this->session( 'member_user_shell', $this->md5( $user['id'].$user['name'].$user['password'] ) );
                        $this->setCustomerTrueLogin($user['role']);
						
						if($returnUrl){
							$this->form_response(200,(string)$this->lang->remind_user_register_12,HTTP_ROOT.$returnUrl);
						}else{
							$this->form_response(200,(string)$this->lang->remind_user_register_12,HTTP_ROOT_WWW.'member/index');
						}
					} elseif ($mdl_user->getCount( "phone='$mobile'" ) > 1) {
						//关联账号的时候一个手机号出现在多个用户商户里。无法关联页不能报错，只能让用户正常注册。
					}

					$initPassowrd = $this->createRnd();

					$userObject = new User();
					$userObject->setPassword($this->md5($initPassowrd));
					$userObject->setInitPassowrd($initPassowrd);

					$username=$mobile;

					$userObject->setName($username);

					$userObject->setBusinessMobile($mobile,true);
					
					$mdl_user->begin();
					$mdl_user->addUser($userObject->toDBArray());

					$user = $mdl_user->getUserByName( $username );

					$mdl_user->setTrustLevel(1,$user['id']);
					
					if ( $mdl_user->errno() ) {
						$mdl_user->rollback();
						$this->form_response_msg((string)$this->lang->remind_user_register_11);
					}
					else {
						$mdl_user->commit();
                       
					     $this->add_referrals ($name,$email,$mobile,$user['id']);
						 
						$this->session( 'member_user_id', $user['id'] );
						$this->session( 'member_user_shell', $this->md5( $user['id'].$user['name'].$user['password'] ) );
                        $this->setCustomerTrueLogin($user['role']);
						
						if($returnUrl){
							$this->form_response(200,(string)$this->lang->remind_user_register_12,HTTP_ROOT.$returnUrl);
						}else{
							$this->form_response(200,(string)$this->lang->remind_user_register_12,HTTP_ROOT_WWW.'member/index');
						}
					}
					break;


				default:
					# code...
					break;
			}			
		}
		else {
			// 下面生成一个微信登陆二维码
			// $newid 是生成的pc 机临时登陆序列号 ,这里要建一个新表
			$newid =$this->loadModel('wj_pc_weixin_login_temp_info')->init();
			
			if ($newid){
				$this->setData(PcLoginQRCode($this->returnUrl,$newid),'loginQrcode');
				$this->setData($newid,'pc_login_id');
			}

			$this->setData( $this->wx_openID, 'openID' );
			$this->setData( '会员注册 - '.$this->site['pageTitle'], 'pageTitle' );
			$this->display( 'welcome/register' );
		}
	}

	 function  add_referrals ($name,$email,$mobile,$userid) {
		    $mdl_referrals = $this->loadModel( 'referrals' );

			$ref = $mdl_referrals->getByWhere( array( 'userId' => $userid ) );
			if ( $ref ) $this->sheader( $this->parseUrl()->setPath('referal/index') );

			$info = array(
				'name' => $name,
				'email' => $email,
				'phone' => $mobile,
				'createTime ' => time()
			);

			if ( $mdl_referrals->join($userid, $info ) ) {
				$mdl_referrals->setApprove($userid);
				
			}
			else {
				
			}
		 
		 return 1;
		 
	 }
	
	function profile_action() {
		
		
		
		if ( is_post() ) {
			
			$email =  trim( post( 'email' ) );
			$nickname = trim( post( 'nickname' ) );
			$person_first_name = trim( post( 'person_first_name' ) );
			$person_last_name = trim( post( 'person_last_name' ) );
			$tel = trim( post( 'tel' ) );
			$phone = trim( post( 'phone' ) );

			$mdl_user = $this->loadModel( 'user' );
			$mdl_reg = $this->loadModel( 'reg' );

			if ( $email&&!$mdl_reg->chkMail( $email ) ) {
				if($this->getUserDevice()=='desktop'){
					$this->form_response_msg((string)$this->lang->wrong_email);
				}else{

					$this->sheader(null,(string)$this->lang->wrong_email);
				}
			}

			if ( $phone&&!$mdl_reg->chkPhone( $phone ) ) {
				if($this->getUserDevice()=='desktop'){
					$this->form_response_msg((string)$this->lang->only_10_digital_phone);
				}else{
					$this->sheader(null,(string)$this->lang->only_10_digital_phone);
				}
			}

			$data = array();
			$data['email'] = $email;
			$data['nickname'] = $nickname;
			$data['person_first_name'] = $person_first_name;
			$data['person_last_name'] = $person_last_name;
			$data['tel'] = $tel;
			$data['phone'] = $phone;

			if($this->loginUser['phone']!=$phone)$data['phone_verified']='false';

			if($this->getUserDevice()=='desktop'){
				if ( $mdl_user->updateUserById( $data, $this->loginUser['id'] ) ) {
					$this->form_response(200,(string)$this->lang->update_success,HTTP_ROOT_WWW."member/index");
				}
				else {
					$this->form_response_msg((string)$this->lang->update_failure);
				}
			}else{
				if ( $mdl_user->updateUserById( $data, $this->loginUser['id'] ) ) {
					$this->sheader(HTTP_ROOT_WWW."member/index");
				}
				else {
					
				}
			}
			
		}
		else {
			$this->setData( 'Personal setting', 'pagename' );
			$this->setData(get2('side'),'side');
			$this->setData( 'business_setting','menu' );
			$this->setData( 'profile_manager', 'submenu' );
			$this->setData( 'Personal setting - '.$this->site['pageTitle'], 'pageTitle' );
			$this->display_pc_mobile('member/profile','mobile/member/profile');
		}
	}

	function changepwd_action() {
		
		$menu_item = trim(get2( 'menu_item' ));
		$side = trim(get2( 'side' ));
		
		if ( is_post() ) {
			$oldpwd		= trim( post('oldpwd') );
			$password	= trim( post('password') );
			$password2	= trim( post('password2') );
            
			
			if ( $this->md5( $oldpwd ) != $this->loginUser['password'] ) {
				
				if($this->getUserDevice()=='desktop'){
					$this->form_response_msg((string)$this->lang->old_password_incorrect);
				}else{

					$this->sheader(null,(string)$this->lang->old_password_incorrect);
				}
				
			}

			$mdl_reg = $this->loadModel( 'reg' );
			if ( ! $mdl_reg->chkPassword( $password ) ) {
				
				if($this->getUserDevice()=='desktop'){
					$this->form_response_msg((string)$this->lang->password_requirement);
				}else{

					$this->sheader(null,(string)$this->lang->password_requirement);
				}
				
				
			}
			if ( $password != $password2 ) {
				
				if($this->getUserDevice()=='desktop'){
					$this->form_response_msg((string)$this->lang->remind_user_register_7);
				}else{

					$this->sheader(null,(string)$this->lang->remind_user_register_7);
				}
				
				
			}

			$passwordByCustomMd5 = $this->md5( $password );
			$data = array(
				'password' => $passwordByCustomMd5
			);
			$mdl_user = $this->loadModel('user');
			if ( $mdl_user->updateUserById( $data, $this->loginUser['id'] ) ) {
				$this->session( 'member_user_id', $this->loginUser['id'] );
				$this->session( 'member_user_shell', $this->md5( $this->loginUser['id'].$this->loginUser['name'].$passwordByCustomMd5 ) );
				
				
				
			if($this->getUserDevice()=='desktop'){
				$this->form_response_msg((string)$this->lang->update_success);
				
			}else{
				$this->sheader(HTTP_ROOT_WWW."member/index");
			}
				
				
			}
			else {
				
					if($this->getUserDevice()=='desktop'){
					$this->form_response_msg((string)$this->lang->update_failure);
						
					}else{
						$this->sheader(null,(string)$this->lang->update_failure);
					}
					
				
			}
		}
		else {
			
		
			if($side=='company') {
				$this->setData( 'basic_setting','menu' );
			}else{
				$this->setData( 'user_setting','menu' );
				
			}
			$this->setData( 'changepwd', 'submenu' );
			$this->setData( 'Change Password'.$this->site['pageTitle'], 'pageTitle' );
			
			 $this->display_pc_mobile('member/changepwd','mobile/member/changepwd');
		}
	}

	
	public function bind_wx_action(){
		$userId=get2('userid');
		$wxId=$this->wx_openID;

		if(!$userId){
			$this->sheader(null,'User ID missing ,please try again later');
		}

		if(!$wxId){
			$this->sheader(null,'wechat Open ID missing,please try again later');
		}

		if(!$this->loadModel( 'user' )->get($userId)){
			$this->sheader(null,'the user pending is not find !');
		}

		$this->setData($userId,'userId');
		$this->setData($wxId,'wxId');

		$data=array();
		$data['wx_openID']=$wxId;
		$data['IsTransform']=1;   //消息推送直接送达商家

		$this->loadModel( 'user' )->updateUserById( $data, $userId);

		$this->display('member/bind_wx_success');// only mobile will access this page
	}


	
	function myorders_action(){

		$mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');
        $mdl_order = $this->loadModel('order');
        $mdl_user = $this->loadModel('user');

        $orderBy='id desc';

        $where['userId']=$this->loginUser['id'];
        $userId =$this->loginUser['id'];
        /**
         * filter
         */
        $filter= get2('filter');
    	$filterList = ['all','unpaid','paid','waiting','send','pleaseRate','cancel'];
    	if(!in_array($filter, $filterList))$filter=reset($filterList);
    	$this->setData($filter,'filter');

    	if($filter=='all'){

        }elseif($filter=='unpaid'){
        	$where['status']=0;

        }elseif($filter=='paid'){
        	$where['status']=1;

        }elseif($filter=='waiting'){
        	$where['coupon_status']='c01';

        }elseif($filter=='send'){
        	$where['coupon_status']='b01';

        }elseif($filter=='pleaseRate'){
			$where['rated']='0';

        }elseif($filter=='cancel'){
        	$where['coupon_status']='d01';

        }
      

        $pageSql=$mdl_order->getListSql(null,$where,$orderBy);

        $pageUrl = $this->parseUrl()->set('page');
        $pageSize = 30;
        $maxPage = 10;
        $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);

        $data = $mdl_order->getListBySql($page['outSql']);
        $data =$mdl_order->getlistBySql("select id,business_userId,orderId,order_name,status,coupon_status,paytime,money,money_new from cc_order where userId =$userId order by id desc");


        /* 获取当前用户订货的供应商列表*/
        $supplier_list =$this->loadModel('order')->getCustomerSupplierList($this->loginUser['id']);
        $this->setData(json_encode($supplier_list),'supplier_list');
        //var_dump($supplier_list);exit;

        /**
         * Mobile page display items
         */
		   $hours = (time()-$data['createTime'])/(60*60);
		 $this->setData($hours,'hours');

        //var_dump($data);exit;
        $this->setData($page['pageStr'],'pager');
        $this->setData(json_encode($data),'order_data');
       // var_dump(json_encode($data));exit;
        $this->setData($data,'data');

        $this->setData($this->parseUrl()->set('filter')->set('page'),'listUrl');

        $this->setData( 'myorder', 'menu' );
		$this->setData( 'exchange', 'submenu' );

		$this->setData( 'My orders  - '.$this->site['pageTitle'], 'pageTitle' );

        $this->display_pc_mobile('mobile/member/my_orders','mobile/member/my_orders');
	}

    // 获得某个客户关于某个商家的购物清单，这个数据用在订货页面


	public function cancel_order_action()
	{	
			
		$orderId= get2('orderId');
		
		if ( $orderId > 0 ) {
			$this->cancel_customer_coupon('cancelByCustomer',$orderId);
		}

		$this->sheader(HTTP_ROOT_WWW.'member/exchange_detail?id='.$orderId );
	}

	function exchange_action() {
    		$this->myorders_action();
    		exit;
    		// some email still have this link.
	}

	// 显示当前用户申领的优惠券，返现券，代金券。
	function discount_voucher_action() {
		$mdl_exchange	= $this->loadModel('wj_customer_coupon');
		$side = trim(get2('menu'));
		$orderid =trim(get2('orderId'));
		$deleteId = (int)get2('deleteId');
		$type=trim(get2('type'));
		
		
		// 生成查询条件
		if ($type=='c01'){
			$where ="' and 	( a.coupon_status = 'c01')";
		}elseif ($type=='b01') {
			$where ="' and 	( a.coupon_status = 'b01' )";
		}else{
			$where ="' and 	(a.coupon_status ='a01' or a.coupon_status = 'b01' or a.coupon_status = 'd01' or a.coupon_status = 'c01' or a.coupon_status ='w01')";
		}
	
		// 如果$orderid 为真，那么说明该记录是从 支付成功后转过来的
		if($orderid){
			$display_sql = " select c.pic,b.orderId,b.money,b.paytime,b.status as is_paid,a.* from cc_wj_customer_coupon as a LEFT JOIN  cc_order as b  on b.orderId =a.order_id left join cc_coupons c  on c.id=a.bonus_id where a.order_id ='".$orderid ."' and a.userId ='".$this->loginUser['id']. $where . " and (a.bonus_type =2 or a.bonus_type=4) order by gen_date desc ";
		}else{
			$display_sql = " select c.pic,b.orderId,b.money,b.paytime,b.status as is_paid,a.* from cc_wj_customer_coupon as a LEFT JOIN  cc_order as b  on b.orderId =a.order_id left join cc_coupons c  on c.id=a.bonus_id where a.userId ='".$this->loginUser['id']. $where . " and (a.bonus_type =2 or a.bonus_type=4) order by gen_date desc ";
		}
		//echo $display_sql;exit;
		
		$pageUrl	= $this->parseUrl()->set( 'page' );
		$pageSize	= 10;
		$maxPage	= 10;
		$page		= $this->page($display_sql, $pageUrl, $pageSize, $maxPage);
		$data		= $mdl_exchange->getListBySql($display_sql);

		$mdl_user = $this->loadModel( 'user' );
		$mdl_coupons = $this->loadModel( 'coupons' );
		foreach ( $data as $key => $val ) {
			// 这里面开始获取这张产品商家的信息。可能是一个商家的子商家。并涉及到当客户与商家交互时，信息
			// 直接到相对的子公司中。所以这里判断 
			
			// 如果这张客户领取的产品 customer_coupon 的 商家编号和 子分部编号不同，并且子分部的号码不为0
			// 则说明，当前的产品是指向子公司的。
			
			if ( ($val['business_staff_userid']!=0) &&  ($val['business_id']!=$val['business_staff_userid']) ){
				$data[$key]['company'] = $mdl_user->get( $val['business_staff_userid'] );
			}else{
				$data[$key]['company'] = $mdl_user->get( $val['business_id'] );
			}
			
			$coupon = $mdl_coupons->get( $val['bonus_id'] );
			$data[$key]['product'] = array( 'url' => HTTP_ROOT_WWW.'coupon/'.$coupon['id'], 'name' => $coupon['title'] );
			$data[$key]['companyUserName'] = $data[$key]['company']['name'];
			if($coupon['bonus_type']==10){
				$data[$key]['bonus_type_name']='门票';
			}
		}

		$dataWithNewStructure=array();
		foreach ($data as $row) {
			$orderids=array_keys($dataWithNewStructure);

			if(in_array($row['order_id'], $orderids)){
				array_push($dataWithNewStructure[$row['order_id']], $row);
			}else{
				$dataWithNewStructure[$row['order_id']]=array($row);
			}
		}

		$this->setData($dataWithNewStructure, 'data');
		$this->setData($page['pageStr'], 'pager');
		$this->setData( $this->parseUrl()->set('deleteId')->set('id'), 'delUrl' );

		//print_r($dataWithNewStructure);exit;
         // if orderid which means from paying result.
         
		if($orderid) {
			$this->setData( '1', 'ispayingresult' );
		}
		$this->setData((string)$this->lang->voucher_publish, 'pagename' );
		$this->setData( 'myorder', 'menu' );
		$this->setData( 'exchange', 'submenu' );
		$this->setData( $side, 'menu_item' );
		$this->setData( (string)$this->lang->voucher_publish.' - '.(string)$this->lang->my_account.' - '.$this->site['pageTitle'], 'pageTitle' );
		
		$this->display_pc_mobile('mobile/member/discount_voucher','mobile/member/discount_voucher');
	}
	
	// 显示当前商家发布的在线折扣码。
	function online_discount_code_action() {

		$mdl_wj_promotion_code = $this->loadModel( 'wj_promotion_code' );
		$mdl_user =$this->loadModel('user');
		$mdl_coupons=$this->loadModel("coupons");
		$display_sql = "select a.* from cc_wj_promotion_code a  where a.is_for_public=1 order by a.id desc limit 30";
	  
		
		$pageUrl	= $this->parseUrl()->set( 'page' );
		$pageSize	= 10;
		$maxPage	= 10;
		$page		= $this->page($display_sql, $pageUrl, $pageSize, $maxPage);
		$data		= $mdl_wj_promotion_code->getListBySql($display_sql);
  		
  		// 获取折扣的商家信息，以及获取折扣码应用范围，关联全店还是关联某个产品
		
		foreach ( $data as $key => $val ) {
			$user =$mdl_user->get($val['user_id']);
				if($user){
					$data[$key]['businessName']=$user['businessName'];
					$data[$key]['pic']=$user['logo'];
				}
			if($val['coupon_id']=='-1'){
				$data[$key]['discount_range']=$this->lang->all_products;
				$data[$key]['link']='store/'.$val['user_id'];
				
			}else{
				$data_coupon = $mdl_coupons->get($val['coupon_id']);
				$data[$key]['discount_range']=$data_coupon['title'];
				$data[$key]['pic']=$data_coupon['pic'];
				$data[$key]['link']='coupon/'.$val['coupon_id'];
			}
		}
		
	
		$this->setData($data,'data');
		$this->setData( (string)$this->lang->promotion_code, 'pagename' );
		$this->setData( (string)$this->lang->promotion_code.' - '.(string)$this->lang->my_account.' - '.$this->site['pageTitle'], 'pageTitle' );
		
		$this->display_pc_mobile('mobile/member/online_discount_code','mobile/member/online_discount_code');
	
	}
	
	function exchange_detail_action() {

        $id = get2('id');
        $type = get2('type');
        $mdl_order = $this->loadModel('order');
        $data = $mdl_order->getByOrderId($id);
/*
        if($data['userId']!=$this->loginUser['id'])
        	$this->sheader(null,'越权访问');
*/
        $where = array(
            'order_id' => $id
        );
        $mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');
        $data1 = $mdl_wj_customer_coupon->getByWhere($where);
        $data['is_visible_for_business'] = $data1['is_visible_for_business'];
        $data['bonus_type'] = $data1['bonus_type'];

        $items = $mdl_wj_customer_coupon->getOrderItems($id);
        $this->setData($items,'items');

        $mdl_coupons = $this->loadModel('coupons');
        $where = array(
            'id' => $data1['bonus_id']
        );
        $data2 = $mdl_coupons->getByWhere($where);
		
		$business_delivery_info =$this->loadModel('user')->getBusinessDeliveryInfo($data2['createUserId'],$this->getLangStr());

         $supplier_info =$this->loadModel('wj_abn_application')->getBusinessInfo($data['business_userId']);
         //var_dump($supplier_info);exit;

         $this->setData($supplier_info,'supplier_info');

        if ($data2) {

         if($this->getLangStr()=='en'){
			
            $data['finePrint'] = $data2['finePrint_en'];
            $data['redeemProcedure'] = $data2['redeemProcedure_en'];
            $data['refund_policy'] = $data2['refund_policy_en'];
			 
		 }else{
			
            $data['finePrint'] = $data2['finePrint'];
            $data['redeemProcedure'] = $data2['redeemProcedure'];
            $data['refund_policy'] = $data2['refund_policy'];
			 
		 }
		  $data['delivery_description'] = $business_delivery_info['delivery_description'];
            $data['pickup_des'] = $business_delivery_info['pickup_des'];
            $data['offline_pay_des'] = $business_delivery_info['offline_pay_des'];
            
        }

        $data3 = $this->loadModel('user')->getUserById($data['business_staff_id']);
        if ($data3) {
            $data['pickupname'] = $data3['contactPersonNickName'];
            $data['pickupaddress'] = $data3['googleMap'];
            $data['pickupphone'] = $data3['contactMobile'];
        }


        $data4 = $this->loadModel('user')->getUserById($data['business_userId']);
        if ($data4) {
            $data['businessAddress'] = $data4['googleMap'];
            $data['businessContactPhone'] = $data4['contactMobile'];
        }


        // 获取订单商家的信息



        if (!$data) {
            $this->sheader(null, (string)$this->lang->no_data);
        }


       

        $this->setData($data, 'data');

        $moneyDetail=$mdl_order->getMoneyDetail($id);
        $this->setData($moneyDetail,'moneyDetail');

        $this->setData(redeemQRCode($data['redeem_code']), 'redeemQRCode');
        $this->setData($type, 'type');
        
        //group pin
        $mdl_group_pin = $this->loadModel('group_pin');
        $user_group_id = $mdl_group_pin->hasUserGroup($id);
        if($user_group_id){
            $userGroup=$this->loadModel('group_pin_user_group')->get($user_group_id);
            $this->setData($userGroup,'userGroup');
        }
		
       	
        $activity_log=$this->loadModel('wj_user_coupon_activity_log')->getList(null,array('orderId'=>$id));
        foreach ($activity_log as $k => $l) {
        	$activity_log[$k]['cn_description']=$mdl_coupons->actionlist_info($l['action_id']);
        }
        $this->setData($activity_log, 'log');

        $this->setData('myorder', 'menu');
        $this->setData('exchange', 'submenu');
        $this->setData('客户Ubonus券管理', 'pagename');
        $this->setData('客户Ubonus券管理 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
    
		$this->display_pc_mobile('member/exchange_detail','mobile/member/orderDetail');
	}
	
	function fav_action() {
		
		$mdl_fav = $this->loadModel( 'fav' );

		//remove
		$id = (int)get2( 'id' );
		if ( $id > 0 ) {
			$fav = $mdl_fav->get( $id );
			if ( ! $fav || $fav['userId'] != $this->loginUser['id'] ) {
				$this->sheader( null, (string)$this->lang->no_data );
			}
			$mdl_fav->delete( $id );
			$this->sheader( $this->parseUrl()->set( 'id' ) );
		}

		$type=get2('type');
		if(!$type)$type='coupon';

		$data = $mdl_fav->getWatchList($this->loginUser['id'],$type,$this->getLangStr());
	   foreach ( $data as $key => $val ) {
			 if($this->getLangStr() =='en' && $val['title_en']){
				$data[$key]['title'] = $val['title_en'];
			 }
		}

	
		$this->setData( $type, 'type' );
		$this->setData( $data, 'data' );

		$this->setData( 'fav', 'menu' );
		$this->setData( 'fav', 'submenu' );

		$this->setData( 'My Fav '.$this->site['pageTitle'], 'pageTitle' );
		$this->display_pc_mobile('member/fav','mobile/member/fav');
	}  

	
	function community_action() {
		//获取社区数据类型
		$type=trim(get2("type"));
		$this->setData($type,'type');
		
		// 根据类型获取相应的数据，包括抽奖，活动，拼团，红包，二手等
		switch ( $type ) {
			case 'lottery':
				$mdl_lottery_draw_bit_counts=$this->loadModel('lottery_draw_bit_counts');

				$where = array();
				$where['is_approved'] = 1;
				$where['status'] = 1;

				$heads	= $mdl_lottery_draw_bit_counts->getList( null, $where, "lottery_id desc" );
				$pagename='Ubonus社区-抽奖';
				$pagetitle="社区-个人中心";

				break;
			case 'events':
				
				break;
			case 'vote':
			    $heads = $this->loadModel('voting')->getVotingList();
			    $pagename='Ubonus社区-投票';
				$pagetitle="社区-个人中心";
				break;
			case 'redbag':
			    $pagename='Ubonus社区-红包';
				$pagetitle="社区-个人中心";
				break;
			case 'secondhands':
			    $pagename='Ubonus社区-二手';
				$pagetitle="社区-个人中心";
				break;
			case 'groupbuy':
                /*<{if $item.status==0}>
                <!-- Coming Soon -->即将上线
				<{elseif $item.status==1}>
				<!-- Running -->凑团进行中
				<{elseif $item.status==2}>
				<!-- Close -->关闭
				<{elseif $item.status==3}>
				<!-- Staging -->分级奖励达成
				<{elseif $item.status==4}>
				<!-- Finalizing -->最终奖励达成
				<{elseif $item.status==5}>
				<!-- Complete -->完成**/
               
                $sql = "SELECT g.id,g.name,g.status,g.max_user_group,c.pic FROM `cc_group_buy_status` as g left join cc_coupons as c on g.coupon_id=c.id";
				$heads = $this->loadModel('group_buy')->getListBySql($sql);

                break;
			default:
				
				break;
		}
		$this->setData( $heads,'heads');
		$this->setData( $pagename, 'pagename' );
		$this->setData( $type, 'current_submenu' );
		$this->setData( $pagetitle.$this->site['pageTitle'], 'pageTitle' );
				
		$this->display_pc_mobile('mobile/member/community','mobile/member/community');
	}
	
	
	function recharge_action() {

		//** 红包到期回退的检测和操作
		$this->loadModel('redbag')->checkExpired();

		$mdl_recharge = $this->loadModel( 'recharge' );


		/**
		 * Filter
		 */

		$filter=array(
			'all'       =>(string)$this->lang->wallet_filter1,
			'business'  =>(string)$this->lang->wallet_filter2,
			'user'      =>(string)$this->lang->wallet_filter3,
		);

		$filter = array_merge ($filter,$mdl_recharge->balanceProcessTypeArray($this->getLangStr()));

		$this->setData($filter,'filter');

	
		$type=trim(get2("type"));
		if(!$type)$type='user';
		$this->setData($type,'type');

		$where['userId']=$this->loginUser['id'];
		
		if($type=='all'){
		}elseif($type=='user'){
			$where[]= $mdl_recharge->getOrCounditionSqlStr('payment',$mdl_recharge->userDisplayRecord());
		}elseif($type=='business'){
			$where[]= $mdl_recharge->getOrCounditionSqlStr('payment',$mdl_recharge->businessDisplayRecord());
		}else{
			$where['payment']=$type;
		}
		
		/**
		 * Data Caculation
		 */
		
		$pageSql	= $mdl_recharge->getListSql( null, $where, 'createTime desc' );
		
		$pageUrl	= $this->parseUrl()->set('page');
		$pageSize	= 30;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $mdl_recharge->getListBySql($page['outSql']);

		
		$balance=$mdl_recharge->getBalance($this->loginUser['id'] );
		$this->setData($balance,'balance');

		$available_fund=$mdl_recharge->getBalanceOfUser($this->loginUser['id'] );
		$this->setData($available_fund,'available_fund');

		$pending_fund=$mdl_recharge->getTotalPending($this->loginUser['id'] );
		$this->setData($pending_fund,'pending_fund');

		$pending_fund_in = $mdl_recharge->getIncomingPending($this->loginUser['id']);
        $this->setData($pending_fund_in, 'pending_fund_in');

        $pending_fund_out = $mdl_recharge->getOutGoingPending($this->loginUser['id']);
        $this->setData($pending_fund_out, 'pending_fund_out');


		$this->setData( $data, 'data' );
		$this->setData( $page['pageStr'], 'pager' );

		$this->setData( 'Wallet', 'pagename' );
		$this->setData( 'recharge', 'menu' );
		
		$this->setData( 'recharge', 'submenu' );
		
		$this->setData( 'Wallet  - '.$this->site['pageTitle'], 'pageTitle' );
		$this->display_pc_mobile('member/recharge','mobile/member/recharge');
	}

	public function update_recharge_void_action(){
        $mdl_order = $this->loadModel( 'recharge' );

        $orderId=$_GET['orderId'];

        $order = $mdl_order->getByWhere(array('orderId'=>$orderId));

        if(!$orderId||!$order){
            $this->sheader( null, '找不到该订单' );
        }
        
        if($mdl_order->updataTransactionStatus($orderId,BalanceProcess::VOID)){
            $this->sheader( HTTP_ROOT_WWW.'company/account_balance' );
        }else{
            $this->sheader( null, '更新出错，请稍后再试' );
        }
        
    }
    public function withdraw_account_action()
    {
    	
		$redirect2 =trim(get2('redirect2'));
		
		$this->setData($redirect2,'redirect2');
		
		// 获取用户的账户信息
		$mdl_user_account_info = $this->loadModel( 'user_account_info' );
		$where =array(
		 'userId'=>$this->loginUser['id']
		);
		$user_account_info =$mdl_user_account_info->getByWhere($where);

		$user_account_existd =false;
		if($user_account_info) {
		    $user_account_existd=true;
			$this->setData($user_account_info,'user_account_info');
     	}else{
			$user_account_info['firstname'] =$this->loginUser['person_first_name'];
			$user_account_info['lastname'] =$this->loginUser['person_last_name'];
			$this->setData($user_account_info,'user_account_info');
		}

		if ( is_post() ) {
			
			$redirect2 =trim(post('redirect2'));
			
			
			$account_number = ( post( 'account_number' ) );	
			$account_name = ( post( 'account_name' ) );
			$firstname = ( post( 'firstname' ) );
			$lastname = ( post( 'lastname' ) );
			$bsb_number = ( post( 'bsb_number' ) );
			if(!$bsb_number) {
				
				$bsb_number='not filled';
			}
			
			if(!$account_name) {
				
				$account_name='not filled';
			}
			
			if(!$account_number) {
				
				$account_number='not filled';
			}

			
			
			
			
			/*
			if (strlen($account_name)<2 || strlen($account_name)>200 ) {
				$this->form_response_msg('账户名称长度必须在2-200之间');
			} */
			if (strlen($firstname)<2 || strlen($firstname)>50 ) {
				$this->form_response_msg('first name length between 2-50');
			}
			if (strlen($lastname)<2 || strlen($lastname)>50 ) {
				$this->form_response_msg('Lasy name length between 2-50');
			}
			/*
			if ( !is_numeric($bsb_number)) {
				$this->form_response_msg('BSB-number必须是数字，如果中间有空格或其它空格请去除！');
			}
			if ( strlen($bsb_number)>8 || strlen($bsb_number)<5) {
				$this->form_response_msg('BSB-number长度在5-8位之间！');
			}
			if ( !is_numeric($account_number)) {
				$this->form_response_msg('account-number必须是数字，如果中间有空格或其它字符请去除！');
			}
			if ( strlen($account_number)>11 || strlen($account_number)<5  ) {
				$this->form_response_msg('account-number长度必须在5位-11位之间！');
			}
			*/
		    // 将账户信息写入到用户账户信息中。
			$where =array(
			    'userId'=>$this->loginUser['id']
			);
			$data_user_info = array(
					'account_name' => $account_name,
					'firstname' =>$firstname,
					'lastname' => $lastname, 
					'bsb_number' =>$bsb_number,
					'account_number'=>$account_number,
                    'op_route_key'=>$op_route_key
				);
				//var_dump($data_user_info);exit;
			if($user_account_existd) {
				if($mdl_user_account_info->updateByWhere($data_user_info,$where)){
					if($redirect2) {
					$this->form_response(200,(string)$this->lang->submit_success,HTTP_ROOT_WWW .'member/withdraw');
					}else{
						$this->form_response_msg((string)$this->lang->submit_success);
					}
					$this->setData($data_user_info,'user_account_info');
				}else{
					$this->form_response_msg((string)$this->lang->submit_error);
				}
				
			}else{
				$data_user_info['userId']=$this->loginUser['id'];
				$data_user_info['createtime']=time();
				$data_user_info['ipaddress']=ip();
				if($mdl_user_account_info->insert($data_user_info )){
						$this->setData($data_user_info,'user_account_info');
						if($redirect2) {
						$this->form_response(200,(string)$this->lang->submit_success,HTTP_ROOT_WWW .'member/withdraw');
					}else{
						$this->form_response_msg((string)$this->lang->submit_success);
					}
				}else{
					$this->form_response_msg((string)$this->lang->submit_error);
					
				}
			}

			
		}else{
			if(get2('side')=='company'){
				$this->setData( 'balance_account', 'menu' );
				$this->setData( 'withdraw_account', 'submenu' );
			}else{
				$this->setData( 'recharge', 'menu' );
				$this->setData( 'withdraw', 'submenu' );
			}
			$this->setData( get2('side'), 'side' );

			$this->setData( 'Withdraw account setting - '.$this->site['pageTitle'], 'pageTitle' );

			$this->display('member/withdraw_account');
		}
    }
	function withdraw_action(){
		
		if ( ! $this->loginUser ) {
			$this->sheader( HTTP_ROOT_WWW.'member/login?returnUrl='.urlencode( $_SERVER['REQUEST_URI'] ) );
		}
		
		$mdl_recharge = $this->loadModel( 'recharge' );
		$total_recharge=$mdl_recharge->getBalanceOfUser($this->loginUser['id'] );

		$this->setData($total_recharge,'balance');
		$mdl_user= $this->loadModel('user');
		
		
		// 获取用户的账户信息
		$mdl_user_account_info = $this->loadModel( 'user_account_info' );
		$where =array(
		 'userId'=>$this->loginUser['id']
		);
		$user_account_info =$mdl_user_account_info->getByWhere($where);
		
		
		if($user_account_info) {
			$this->setData($user_account_info,'user_account_info');
     	}else{
			$this->sheader(HTTP_ROOT_WWW.'member/withdraw_account?redirect2=withdraw');			
		}
		
		
		if ( is_post() ) {
			$amount =(float)( post( 'amount' ) );
			
			if ( $amount < 0.5 ) $this->form_response_msg('取现金额'.$amount.'不能小于$0.5');
			if ( $amount > 10000 ) $this->form_response_msg('取现金额每次不能大于$10000');
			
			$mdl_recharge = $this->loadModel( 'recharge' );
			
			// 检查last30天取现次数是否超过5次，总额不能超过1000
			
			// 如果取现的金额大于账户的balance 则进行提示
			// 这里面有两种情况，如果是第一次取现，账户balance的金额需要大于取现金额+$5取现费。
			
			if($total_recharge < $amount){
				$this->form_response_msg((string)$this->lang->Max_amount_less_than_balance);
			}
			
			try{
				$orderId = '102'.date( 'YmdHis' ).$this->createRnd(3);

				$data = array(
					'orderId' => $orderId,
					'userId' => $this->loginUser['id'],
					'money' => 0-$amount,
					'payment' => BalanceProcess::TYPE_WITHDRAW,
					'status' => BalanceProcess::PENDING, 
					'createTime' => time(),
					'createIp' => ip(),
					'coupon_name'=>'客户手动取现'
				);
				$mdl_recharge->insert( $data );

				$mdl_user->updateNumOfWithdrawals($this->loginUser['id'],1);

				$this->form_response(200,(string)$this->lang->submit_success,HTTP_ROOT_WWW.'member/recharge');

			}catch(Exception $e){
				 $this->form_response_msg((string)$this->lang->submit_error);
			}
		}

		$this->setData( '取钱', 'pagename' );
		$this->setData( 'recharge', 'menu' );
		$this->setData( 'withdraw', 'submenu' );
		$this->setData( '取钱 - 个人中心 - '.$this->site['pageTitle'], 'pageTitle' );
		$this->display( 'member/withdraw' );
	}
	
	function recharge_add_action() {
	 	$this->setData( 'Recharge', 'pagename' );
		$this->setData( 'recharge', 'menu' );
		$this->setData( 'Recharge '.$this->site['pageTitle'], 'pageTitle' );
		$this->display( 'member/recharge_add' );
	}


	// 客户配送地址列表
	function delivery_address_action(){
      
		
		$id = (int)get2('id');
		$mdl_wj_user_delivery_info = $this->loadModel( 'wj_user_delivery_info' );

		if ( $id > 0 ) {
			$wj_user_delivery_info = $mdl_wj_user_delivery_info->get( $id );
			 // 此处要加入判断一下该Id 所对应的用户是不是当前登陆的用户
			if ( ! $wj_user_delivery_info || $wj_user_delivery_info['userId']!=$this->loginUser['id'] ) $this->sheader( null, '记录不存在' );
			$this->sheader( $this->parseUrl()->set( 'id' ) );
		}

		$pageSql	= $mdl_wj_user_delivery_info->getListSql( null, array( 'userId' => $this->loginUser['id'] ), 'id desc' );
		//echo $pageSql; exit;
		$pageUrl	= $this->parseUrl()->set('page');
		$pageSize	= 10;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $mdl_wj_user_delivery_info->getListBySql($page['outSql']);

		$this->setData( $data, 'data' );
		$this->setData( $page['pageStr'], 'pager' );
		$this->setData( $this->parseUrl()->setPath( 'memeber/delivery_address_edit' ), 'editUrl' );
		$this->setData( $this->parseUrl()->setPath( 'memeber/delivery_address_edit' ), 'showUrl' );

		$this->setData( '配送地址', 'pagename' );
		$this->setData( 'myorder', 'menu' );
		$this->setData( 'delivery_address', 'submenu' );
		$this->setData( $menu_item,'menu_item' );
		$this->setData( '配送地址 - 个人中心 - '.$this->site['pageTitle'], 'pageTitle' );
		$this->display_pc_mobile('member/delivery_address','mobile/member/delivery_address');
	}
	function delivery_address_delete_action()
	{	
		$id=(int)get2('id');
		$mdl_delivery_addres =$this->loadModel("wj_user_delivery_info");
		if($id)$mdl_delivery_addres ->delete($id);
		$this->sheader( HTTP_ROOT_WWW.'member/delivery_address' );
	}
	
	function delivery_address_edit_action(){
		$id=(int)get2('id');
		$mdl_delivery_addres =$this->loadModel("wj_user_delivery_info");
		

		if ( is_post() ) {
			$first_name =trim(post('first_name'));
			$last_name =trim(post('last_name'));
			$address = trim(post('address'));
			$phone =trim(post('phone'));
			$email =trim(post('email'));
			$id_number =trim(post('id_number'));

			$country = trim(post('country'));
			$isDefaultAddress = trim(post('isDefaultAddress'));
			//echo json_encode($title);exit;
			$data = array(
				'userId'=>$this->loginUser['id'],
				'createTime'=>time(),
				'first_name'=>$first_name,
				'last_name'=>$last_name,
				'address'=>$address,
				'phone'=>$phone,
				'isDefaultAddress'=>$isDefaultAddress,
				'email'=>$email,
				'id_number'=>$id_number,
				'country'=>$country
				);

			if($isDefaultAddress){
				//reset all other to none default
				$d['isDefaultAddress']=0;
				$w['userId']=$this->loginUser['id'];
				$mdl_delivery_addres->updateByWhere( $d,$w );
			}

			if($mdl_delivery_addres->get($id)){
				$mdl_delivery_addres->update( $data,$id );
			}else{
				$mdl_delivery_addres->insert( $data ); 
			}
				
			$this->sheader( HTTP_ROOT_WWW.'member/delivery_address' );
		}
		else {
			$data_delivery_address =$mdl_delivery_addres->get($id); 
			
			$this->setData( $data_delivery_address, 'data' );

			$this->setData( $lang->delivery.$this->lang->info, 'pagename' );
			$this->setData( 'myorder', 'menu' );
			$this->setData( 'delivery_address', 'submenu' );
			$this->setData( $this->lang->delivery.$this->lang->info .'-个人中心 '.$this->site['pageTitle'], 'pageTitle' );
			$this->display_pc_mobile('member/delivery_address_edit','mobile/member/delivery_address_edit');
		}
 

	}
	public function broadcast_control_action()
	{
		if(is_post()){
			$isbroadcasting = trim(post('isbroadcasting'));
			$broadcastType = trim(post('broadcastType'));
 			$title = trim(post('title'));

			$isbroadcasting=($isbroadcasting=='on')?$isbroadcasting:'off';

			$data['isbroadcasting']=$isbroadcasting;
			$data['broadcastType']=$broadcastType;
			$data['broadcastTitle']=$title;
			$where['id'] = $this->loginUser['id'];

			if($this->loadModel('user')->updateByWhere($data,$where)){
				$msg = ($isbroadcasting=='on')?'直播开始 '.$broadcastType:'直播关闭';

				$this->form_response_msg('保存成功！'.$msg);
			}else{
				$this->form_response_msg('网络问题，请稍后再试');
			}


		}else{
			$data = $this->loadModel('user')->getBroadcastInfo($this->loginUser['id']);
			$this->setData($data,'broadcastInfo');
			$this->setData('Ubonus直播管理','pageTitle');

			$this->display_pc_mobile('member/broadcast_control','mobile/member/broadcast_control');
		}
	}


	public function friend_assists_action()
	{	
		$EVENT_FINISH=true;

		/**
		 * Engine Model
		 */
	    $mdl_assist=$this->loadModel('assist');


	    /**
	     * 页面所属的用户
	     */
	    $baseUserId = get2('baseUserId');


		$type            ='';
		$pageTitle       ='';
		$pageDescription ='';
		$userData        ='';
		$ranking         ='';

	    if(!$baseUserId){
	    	/**
	    	 * 第一次进入，我要玩
	    	 */
	    	
	    	$type='new';

	    	$pageTitle=' 100份开门红年货大礼等你赢取 - Ubonus美食生活 - Ubonus美食生活-专注澳洲华人美食生活的电商平台';
	    	$pageDescription=' 100份开门红年货大礼等你赢取 - 神助攻活动 - Ubonus美食生活-专注澳洲华人美食生活的电商平台';
	    	
	    }else{
	    	
	    	if($baseUserId==$this->loginUser['id']){
	    		/**
		    	 * 开启自己的链接。 显示当前曾助攻
		    	 */
		    	
		    	$type='self';

	    		$userData = $mdl_assist->isAlreadyInGame($baseUserId );

	    		if(!$userData){
	    			if(!$EVENT_FINISH)$mdl_assist->playGame($baseUserId);
	    			$userData = $mdl_assist->isAlreadyInGame($baseUserId );
	    		}

	    		$ranking=$mdl_assist->getRanking($baseUserId);

	    		$userDisplayName = $this->loadModel('user')->getUserDisplayName($baseUserId);

	    		$this->setData($userDisplayName,'userDisplayName');

	    		$pageTitle='快来帮我助攻！  100份开门红年货大礼等你赢取 - Ubonus美食生活-专注澳洲华人美食生活的电商平台';
	    		$pageDescription='快来帮我助攻('.$userDisplayName.')！  100份开门红年货大礼等你赢取 - Ubonus美食生活-专注澳洲华人美食生活的电商平台';

	    	}else{
	    		/**
		    	 * 开启别人的链接。 给朋友增加一次助攻
		    	 */
		    	
		    	$type='friend';

	    		$userData = $mdl_assist->isAlreadyInGame($baseUserId);

	    		if(!$userData)$this->sheader(null,'该用户还没有参与活动');

	    		if(!$EVENT_FINISH)$mdl_assist->assist($baseUserId,$this->loginUser['id']);
	    		$userData = $mdl_assist->isAlreadyInGame($baseUserId );

	    		$ranking=$mdl_assist->getRanking($baseUserId);

	    		$userDisplayName = $this->loadModel('user')->getUserDisplayName($baseUserId);

	    		$pageTitle='快来帮我助攻！ D 100份开门红年货大礼等你赢取 - Ubonus美食生活-专注澳洲华人美食生活的电商平台';
	    		$pageDescription='快来帮'.$userDisplayName.'助攻！  100份开门红年货大礼等你赢取 - Ubonus美食生活-专注澳洲华人美食生活的电商平台';
	    	}
	    }

	    /**
	     *  coupon_promotion
	     */
	    $mdl_coupons= $this->loadModel('coupons');
	    $where= " id in (4819,4695,4833,4110,4661,2433,4102,4441,4649,4651,4766,4277,4657,4708) ";
	    $coupons = $mdl_coupons->getList(null,$where);
	    foreach ($coupons as $key =>$value) {
  			$mdl_coupons->caculatePriceAndPoint($coupons[$key]);
  		}
	    $this->setData($coupons,'coupons');

	   	/**
	   	 * wx share
	   	 */
		require_once "wx/wxjssdk.php";
        $jssdk = new WXjsSDK();
        $signPackage = $jssdk->GetSignPackage();
        $this->setData($signPackage,'signPackage');

        /**
         * leaderBoard
         */
	    $this->setData($mdl_assist->leaderboard(),'leaderboard');

	    /**
	     * 总参与玩家
	     */
	    $this->setData($mdl_assist->totalPlayer(),'totalPlayer');

		$this->setData($this->parseUrl()->set('baseUserId', $this->loginUser['id']),'askForAssistUrl');
		$this->setData($baseUserId,'baseUserId');

		$this->setData($type,'type');
	    $this->setData($pageTitle,'pageTitle');
	    $this->setData($pageDescription,'pageDescription');
	    $this->setData($userData,'userData');
	    $this->setData($ranking,'ranking');

	    $this->setData($EVENT_FINISH,'event_finish');


		$this->display('wechat/friend_assists/index');

		// Final winning user
		// select u.name,u.nickname, u.person_first_name, u.person_last_name, u.phone,u.email,u.tel from cc_friend_assist as fa left join cc_user as u on u.id = fa.user_id order by fa.total desc, fa.gen_date limit 20

	}


	public function double_eleven_assist_action()
	{
		$EVENT_FINISH=false;

		/**
		 * Engine Model
		 */
	    $mdl_assist=$this->loadModel('assist');
	    $mdl_assist->overwriteTable('#@_double_eleven_assist','cc_double_eleven_assist_log');

	    /**
	     * 页面所属的用户
	     */
	    $baseUserId = get2('baseUserId');


		$type            ='';
		$pageTitle       ='';
		$pageDescription ='';
		$userData        ='';
		$ranking         ='';

	    if(!$baseUserId){
	    	/**
	    	 * 第一次进入，我要玩
	    	 */
	    	
	    	$type='new';

	    	$pageTitle='第三季 中国新歌声 墨尔本海选 - Ubonus美食生活-专注澳洲华人美食生活的电商平台';
	    	$pageDescription='第三季 中国新歌声 墨尔本海选 - 神助攻活动 Ubonus美食生活-专注澳洲华人美食生活的电商平台';
	    	
	    }else{
	    	
	    	if($baseUserId==$this->loginUser['id']){
	    		/**
		    	 * 开启自己的链接。 显示当前曾助攻
		    	 */
		    	
		    	$type='self';

	    		$userData = $mdl_assist->isAlreadyInGame($baseUserId );

	    		if(!$userData){
	    			if(!$EVENT_FINISH)$mdl_assist->playGame($baseUserId);
	    			$userData = $mdl_assist->isAlreadyInGame($baseUserId );
	    		}

	    		$ranking=$mdl_assist->getRanking($baseUserId);

	    		$userDisplayName = $this->loadModel('user')->getUserDisplayName($baseUserId);

	    		$this->setData($userDisplayName,'userDisplayName');

	    		$pageTitle='第三季 中国新歌声 墨尔本海选 - Ubonus美食生活 - 澳洲华人第一电商平台';
	    		$pageDescription='快来帮我助攻('.$userDisplayName.')！第三季 中国新歌声 墨尔本海选 - 神助攻活动 - Ubonus美食生活 - 澳洲华人第一电商平台';

	    	}else{
	    		/**
		    	 * 开启别人的链接。 给朋友增加一次助攻
		    	 */
		    	
		    	$type='friend';

	    		$userData = $mdl_assist->isAlreadyInGame($baseUserId);

	    		if(!$userData)$this->sheader(null,'该用户还没有参与活动');

	    		if(!$EVENT_FINISH)$mdl_assist->assist($baseUserId,$this->loginUser['id']);
	    		$userData = $mdl_assist->isAlreadyInGame($baseUserId );

	    		$ranking=$mdl_assist->getRanking($baseUserId);

	    		$userDisplayName = $this->loadModel('user')->getUserDisplayName($baseUserId);

	    		$this->setData($userDisplayName,'userDisplayName');
	    		
	    		$pageTitle='快来帮我助攻！第三季 中国新歌声 墨尔本海选- Ubonus美食生活 - 澳洲华人第一电商平台';
	    		$pageDescription='快来帮'.$userDisplayName.'助攻！ 第三季 中国新歌声 墨尔本海选 - 神助攻活动 - Ubonus美食生活 - 澳洲华人第一电商平台';
	    	}
	    }

	    /**
	     *  coupon_promotion
	     */
	    $mdl_coupons= $this->loadModel('coupons');
	   
   	   $where= " id in (4819,4695,4833,4110,4661,2433,4102,4441,4649,4651,4766,4277,4657,4708) ";
	    $coupons = $mdl_coupons->getList(null,$where);
	    foreach ($coupons as $key =>$value) {
  			$mdl_coupons->caculatePriceAndPoint($coupons[$key]);
  		}
	    $this->setData($coupons,'coupons');

	   
	   	/**
	   	 * wx share
	   	 */
		require_once "wx/wxjssdk.php";
        $jssdk = new WXjsSDK();
        $signPackage = $jssdk->GetSignPackage();
        $this->setData($signPackage,'signPackage');

        /**
         * leaderBoard
         */
	    $this->setData($mdl_assist->leaderboard(),'leaderboard');

	    /**
	     * 总参与玩家
	     */
	    $this->setData($mdl_assist->totalPlayer(),'totalPlayer');

		$this->setData($this->parseUrl()->set('baseUserId', $this->loginUser['id']),'askForAssistUrl');
		$this->setData($baseUserId,'baseUserId');

		$this->setData($type,'type');
	    $this->setData($pageTitle,'pageTitle');
	    $this->setData($pageDescription,'pageDescription');
	    $this->setData($userData,'userData');
	    $this->setData($ranking,'ranking');

	    $this->setData($EVENT_FINISH,'event_finish');

		$this->display('wechat/double_eleven_assists/index');

		// Final winning user
		// select u.name,u.nickname, u.person_first_name, u.person_last_name, u.phone,u.email,u.tel from cc_friend_assist as fa left join cc_user as u on u.id = fa.user_id order by fa.total desc, fa.gen_date limit 20
		// 
		//select a.*,u.person_first_name,u.person_last_name,u.phone,u.email from (SELECT * FROM `cc_double_eleven_assist` order by total desc LIMIT 200) as a left join cc_user as u on a.user_id = u.id

		//user with order
		//select * from (SELECT * FROM `cc_double_eleven_assist` order by total desc LIMIT 200) as a left join cc_order as o on a.user_id = o.userId where o.business_userId=23989 and o.createTime > UNIX_TIMESTAMP('2017-11-1') and a.total > 0 and o.customer_delivery_option=1 order by a.total desc
	}
}