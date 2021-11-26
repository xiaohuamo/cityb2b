<?php

class ctl_factory extends cmsPage
{
    function ctl_factory()
    {

        parent::cmsPage();

        $this->setData('factory', 'footer_menu'); //old version mobile
        $this->setData('dashboard', 'mobile_menu'); //new version mobile

        $act = $GLOBALS['gbl_act'];

        if ($act == 'customer_coupon_approving' || $act == 'customer_order_detail') {

        } else {

            if (! $this->loginUser) {
                $this->sheader(HTTP_ROOT_WWW.'member/login?returnUrl='.urlencode($_SERVER['REQUEST_URI']));
            }
        }

        $this->setData($this->getBusinessType(), 'business_type');
    }
	
				
	public function login_as_customer2c_action() {
        $userId = trim(get2('user_id'));
        $mdl_user_factory2c = $this->loadModel('factory2c_list');
        $mdl_user = $this->loadModel( 'user' );
        if($mdl_user_factory2c->getFactory_2c_id($userId)){
            $user = $mdl_user->getUserById( $userId );
            $data = array(
                'lastLoginIP'	=> ip(),
                'lastLoginDate'	=> time(),
                'loginCount'	=> $user['loginCount'] + 1
            );

            $mdl_user->updateUserById( $data, $user['id'] );

            $this->session( 'member_user_id', $user['id'] );
            $this->session( 'member_user_shell', $this->md5( $user['id'].$user['name'].$user['password'] ) );
			//var_dump($this->loginUser);exit;

            $this->sheader(HTTP_ROOT_WWW . 'company/index');
        }
    }

    function index_action()
    {

        // 获取公司一段时间内的销售额,30天
        $mdl_order = $this->loadModel('order');
        $totalSales = $mdl_order->getTotalSales($this->loginUser['id'], 30);
        $this->setData($totalSales, 'totalSales');

        // 获取公司账户的余额
        $mdl_recharge = $this->loadModel('recharge');
        $balance = $mdl_recharge->getBalance($this->loginUser['id']);
        $this->setData($balance, 'balance');

        $this->setData($this->loadModel('user')->getBusinessDisplayName($this->loginUser['id']), 'businessDisplayName');

        $this->setData('工厂首页', 'pagename');
        $this->setData('index', 'menu');
        $this->setData('工厂中心 - '.$this->site['pageTitle'], 'pageTitle');
        $this->display_pc_mobile('factory/index', 'mobile/factory/index');
    }

    public function customer_orders_action($dataFomOtherMethod = [])
    {
        if ($dataFomOtherMethod['file_path'] && $dataFomOtherMethod['business_id']) {
            $filePath = $dataFomOtherMethod['file_path'];
            $this->loginUser['id'] = $dataFomOtherMethod['business_id'];
        }

        $mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');
        $mdl_order = $this->loadModel('order');
        $mdl_user = $this->loadModel('user');
		
		
		//get loginuser's factory userid 
		$FactoryId= $this->loadModel('user_factory')->getBusinessId($this->loginUser['id'],$this->loginUser['role']);
		

        /**
         * staff List
         */
        $where_staff = "((role = 5 and user_belong_to_user =".$FactoryId.") or id = ".$FactoryId.")";
        $list = $mdl_user->getList(null, $where_staff, 'createdDate asc');
        foreach ($list as $key => $value) {
            $list[$key]['displayName'] = $mdl_user->getBusinessDisplayName($value['id']);
        }
        $this->setData($list, 'staff_list');
        /**
         * payment Type List
         */

        /**
         * customer_delivery_option Type List
         */
        $currentBusinessId = trim(get2('currentBusinessId'));
        $this->setData($currentBusinessId, 'currentBusinessId');

        $sk = trim(get2('sk'));
        $status = trim(get2('status'));
        $st = trim(get2('startTime'));
        $et = trim(get2('endTime'));
        $payment = trim(get2('payment'));
        $customer_delivery_option = trim(get2('customer_delivery_option'));
        $staff = trim(get2('staff'));
        $ifpaid = trim(get2('ifpaid'));
        if (! $ifpaid) {
            $ifpaid = 'all';
        }

        $this->setData($sk, 'sk');
        $this->setData($status, 'status');
        $this->setData($ifpaid, 'ifpaid');
        $this->setData($st, 'st');
        $this->setData($et, 'et');
        $this->setData($payment, 'payment');
        $this->setData($customer_delivery_option, 'customer_delivery_option');
        $this->setData($staff, 'staff');

        // 如果该商家是统配中心商家，那么查询条件增加一个
        $mdl_freshfood_disp_centre_suppliers = $this->loadModel('freshfood_disp_centre_suppliers');
        $sql_tongpei = " select * from cc_freshfood_disp_centre_suppliers where business_id =".$this->loginUser['id'];
        $rec = $mdl_freshfood_disp_centre_suppliers->getListBySql($sql_tongpei);
        if ($rec) {
            $this->setData(1, 'dispatching_user');
        }
        if ($currentBusinessId && $currentBusinessId != 'all') {
            $sql = "SELECT cust.displayName,cust.name,o.* ,cust.ori_sum from cc_order as o left join (select order_id,business_id,sum(voucher_deal_amount*customer_buying_quantity) as ori_sum ,uu.nickname as displayName ,user.name  from cc_wj_customer_coupon tt left join  cc_user_factory uu  on tt.userId =uu.user_id  left join cc_user user on  tt.userId = user.id    group by order_id,business_id) cust on o.orderId=cust.order_id and cust.business_id =".$currentBusinessId." left join cc_wj_user_coupon_activity_log as l on o.orderId=l.orderId and o.coupon_status=l.action_id ";
        } else {
            $sql = "SELECT cust.displayName,cust.name,o.* ,cust.ori_sum from cc_order as o left join (select order_id,business_id,sum(voucher_deal_amount*customer_buying_quantity) as ori_sum ,uu.nickname as displayName,user.name  from cc_wj_customer_coupon tt left join  cc_user_factory uu  on tt.userId =uu.user_id  left join cc_user user on  tt.userId = user.id     group by order_id,business_id) cust on o.orderId=cust.order_id and cust.business_id =".$FactoryId." left join cc_wj_user_coupon_activity_log as l on o.orderId=l.orderId and o.coupon_status=l.action_id ";
        }
        $whereStr = " ( business_userId= ".$FactoryId;
        $whereStr .= "  or  o.orderId in (select DISTINCT c.order_id from cc_wj_customer_coupon c where business_id = ".$FactoryId.")";
        //plus 如果该用户是统配中心用户，其下所有商家的订单
        $whereStr .= " or  business_userId in (select business_id from  cc_dispatching_centre_customer_list where dispatching_centre_id =".$FactoryId.")";
        //如果该商家是集合店铺所有人，则所有其下店铺的订单
        $whereStr .= " or  business_userId in (select suppliers_id from  cc_freshfood_disp_centre_suppliers where business_id =".$FactoryId.")";
        // 如果该用户为授权用户，则其下所有订单均可以看到。
        $whereStr .= " or  business_userId in (select customer_id from  cc_authrise_manage_other_business_account where authorise_business_id =".$FactoryId.")";

        $whereStr .= ")";

        if (! empty($sk)) {
            $whereStr .= " and (o.redeem_code like  '%".$sk."%'";
            $whereStr .= " or o.last_name like  '%".$sk."%'";
            $whereStr .= " or o.phone like  '%".$sk."%'";
            $whereStr .= " or o.orderId like  '%".$sk."%'";
            $whereStr .= " or o.order_name like  '%".$sk."%'";
            $whereStr .= " or o.tracking_id like  '%".$sk."%'";
            $whereStr .= " or o.first_name like  '%".$sk."%'";
            $whereStr .= " or o.userId like  '%".$sk."%')";
            $where[] = $whereStr;
        }
        if (! empty($status)) {
            if ($status != 'all') {
                $whereStr .= " and o.coupon_status='$status' ";
            }
        }

        if (! empty($ifpaid)) {
            if ($ifpaid != 'all') {
                if ($ifpaid == 3) {
                    $whereStr .= " and o.status=0 ";
                } else {
                    $whereStr .= " and o.status='$ifpaid' ";
                }
            }
        } else {
            if ($ifpaid == 3) {
                $whereStr .= " and o.status=0 ";
            } else {
                $whereStr .= " and o.status='$ifpaid' ";
            }
        }

        if (! empty($payment)) {
            if ($payment != 'all') {
                $whereStr .= " and o.payment='$payment' ";
            }
        }

        if (! empty($currentBusinessId)) {
            if ($currentBusinessId != 'all') {
                $whereStr .= " and ( o.business_userId='$currentBusinessId' or cust.business_id =  '$currentBusinessId' ) ";
            }
        }

        if (! empty($customer_delivery_option)) {
            if ($customer_delivery_option != 'all') {
                $whereStr .= " and o.customer_delivery_option='$customer_delivery_option'";
            }
        }

        if (! empty($staff)) {
            if ($staff != 'all') {
                $whereStr .= " and o.business_staff_id = '$staff' ";
            }
        }

        /**
         * 全部订单删选已订单生成时间为基准
         */
        if ($status == 'all') {

            if (! empty($st)) {
                $st = strtotime($st." 00:00:00");
                $whereStr .= " and o.createTime>='$st'";
            }

            if (! empty($et)) {
                $et = strtotime($et." 23:59:59");
                $whereStr .= " and o.createTime<='$et'";
            }
        } else {

            if (! empty($st)) {
                $st = strtotime($st." 00:00:00");
                $whereStr .= " and l.gen_date>='$st'";
            }

            if (! empty($et)) {
                $et = strtotime($et." 23:59:59");
                $whereStr .= " and l.gen_date<='$et'";
            }
        }
		// 检查如果当前用户为销售员，则生成查询sql语句。
		
		if($this->loginUser['role']==101){
			$whereStr .= " and o.userId in (select user_id from cc_user_factory where factory_sales_id =".$this->loginUser['id'].")";
			
		}
		
		
		

        $pageSql = $sql." where ".$whereStr." order by createTime desc";
        if (trim(get2('output')) == 'pdf') {

            $where12 = [
                'userId' => $FactoryId,

            ];
            $user_abn = $this->loadModel('wj_abn_application')->getByWhere($where12);

            $data = $mdl_order->getListBySql($pageSql);

            foreach ($data as $key => $value) {

                $data[$key]['items'] = $mdl_wj_customer_coupon->getItemsInOrder($value['orderId'], $FactoryId);
            }

            $this->loadModel('invoice');
            $report = new OrderInfoReport();
            $report->setStarttime(date('Y-m-d H:i:s', $st))->setEndtime(date('Y-m-d H:i:s', $et))->title("商家报表-订单流水")->OrderData($data);
            $report->generatePDF();

            if ($filePath) {//如果是系统内部调用会直接在指定路径创建文件
                $report->outPutToFile($filePath);

                return $filePath;
            }

            $report->outPutToBrowser(substr($user_abn['untity_name'], 0, 8).'-'.date('Y-m-d', $st).'-'.date('Y-m-d', $et).'_SellingDetails.pdf');
            exit;
        } elseif (trim(get2('output')) == 'shippingLabel') {

            $fitInPage = (get2('fitInPage') == 'true') ? true : false;

            $pageSql = $sql." where ".$whereStr." order by o.address desc";

            $data = $mdl_order->getListBySql($pageSql);

            $lotteryUserList = [];
            foreach ($data as $key => $value) {
                $data[$key]['items'] = $mdl_wj_customer_coupon->getItemsInOrder($value['orderId']);

                $data[$key]['redeemQRCode'] = redeemQRCode($value['redeem_code']);

                if (trim(get2('with')) == 'lottery' && ! in_array($value['userId'], $lotteryUserList)) {
                    $data[$key]['hasLottery'] = $this->loadModel('lottery')->getUserRewardItems($value['userId'], $value['business_userId']);
                    array_push($lotteryUserList, $value['userId']);
                }
            }

            $this->loadModel('invoice');
            $report = new shippingLabel();
            $report->setStarttime(date('Y-m-d H:i:s', $st))->setEndtime(date('Y-m-d H:i:s', $et))->title("Shipping Label")->setReturnAddress($this->loginUser['googleMap'])->fitInPage($fitInPage)->OrderData($data);
            $report->generatePDF();

            if ($filePath) {//如果是系统内部调用会直接在指定路径创建文件
                $report->outPutToFile($filePath);

                return $filePath;
            }

            $report->outPutToBrowser("shippingLabel-".date('Y-m-d', $et));
            exit;
        } else {
            $pageUrl = $this->parseUrl()->set('page');
            $pageSize = 40;
            $maxPage = 10;
            $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);

            $data = $mdl_order->getListBySql($page['outSql']);
			//var_dump($page['outSql']);exit;
			 foreach ($data as $key => $value) {
                 $sql="select id,orderId from cc_order where business_userId=".$value['business_userId']. " and userId =".$value['userId']." and logistic_delivery_date =".$value['logistic_delivery_date']." order by id  " ;
				 $dulplicate_order_rec =$mdl_order->getListBySql($sql);
				 if(count( $dulplicate_order_rec)>1) {
					 if($dulplicate_order_rec[0]['orderId'] !=$value['orderId']) {
						  $data[$key]['original_orderId']=$dulplicate_order_rec[0]['orderId'];
						  $data[$key]['merge']=1;
					 }else{
						  $data[$key]['merge']=0;
						   $data[$key]['original_orderId']=$dulplicate_order_rec[0]['orderId'];
					 }
					 
				 }else{
					  $data[$key]['merge']=2;
				 }
				

               
            }
        }

        $this->setData($page['pageStr'], 'pager');

        $this->setData($data, 'data');

        $this->setData('online_center', 'menu');
        $this->setData('customer_coupon_process', 'submenu');

        $this->setData(HTTP_ROOT_WWW.'factory/customer_orders', 'searchUrl');

        $this->setData($this->parseUrl(), 'currentUrl');

        $this->setData('客户订单中心 - '.$this->site['pageTitle'], 'pageTitle');
		
		//date_default_timezone_set(Australia/Sydney);
		//$this->setData(date_default_timezone_get(), 'currentTimeZone'); 
		//$this->setData(date('H:i:s'), 'currentTime'); 
		 
		 

        $this->display_pc_mobile('factory/customer_orders', 'factory/customer_orders');

        return true;
    }
	public function  business_hour_setting_action() {
	  
	  //判断是否可以设置该用户的营业时间,该段程序稍后写。
	  
	  $customer_id =get2('user_id');
	  $mdl_user_factory =$this->loadModel('user_factory');
	  
	  $FactoryId = $mdl_user_factory->getBusinessId($this->loginUser['id'],$this->loginUser['role']);
	  
	  $where =array (
	    'factory_id' => $FactoryId,
		 'user_id' => $customer_id
	  
	  );
	  $customer_rec  = $mdl_user_factory->getByWhere($where);
	  if(!$customer_rec) {
		  $this->form_response_msg('no access!');
		  
	  }
	  
	  $user =  $this->loadModel('user')->get($customer_id);
	 // var_dump($user);exit;
	  $this->setData($user['trading_hours'],'trading_hours');
       $this->setData($user['trading_hours_desc'],'trading_hours_desc');
	  $this->setData($customer_id,'customer_id');
	  
	  //var_dump($this->loginUser['trading_hours']);exit;
	 
	    $this->setData('customer_list', 'submenu');
        $this->setData('customer_management', 'menu');
	   $this->display_pc_mobile('factory/business_hour_setting1', 'factory/business_hour_setting1');
	//  $this->display_pc_mobile('company/index1', 'company/index1');
}

	public function publish_action() {
		$this->setData('发布中心 - '.$this->site['pageTitle'], 'pageTitle');

        $this->display_pc_mobile('mobile/factory/publish', 'mobile/factory/publish');
		
		
	}
	
	public function packaging_action() {
		$this->setData('发布中心 - '.$this->site['pageTitle'], 'pageTitle');

        $this->display_pc_mobile('mobile/factory/packaging', 'mobile/factory/packaging');
		
		
	}

    public function customer_order_detail_action()
    {
        require_once(DOC_DIR.'static/4pxAPI.php');

        //商家查看的订单详情以及操作
        $orderId = trim(get2('id'));

        $mdl_order = $this->loadModel('order');
        $mdl_coupons = $this->loadModel('coupons');
        $mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');

        //订单信息
        $data = $mdl_order->getByOrderId($orderId);

        // 获得该商家的精确订单汇总 （可能上面的是含多个商家的汇总）
		
		// 销售员的用户role 为101

		  if($this->loginUser['role']==101) {
			  
			  $busi_id = $this->loginUser['user_belong_to_user'];
			  
		  }else{
			   $busi_id = $this->loginUser['id'];
		  }
		  
       
        $sql1 = "select sum(voucher_deal_amount*customer_buying_quantity) as ori_sum ,sum(adjust_subtotal_amount) as ajust_sum from cc_wj_customer_coupon  where order_id=$orderId and  business_id=$busi_id";
        $data1 = $mdl_wj_customer_coupon->getListBySql($sql1);
		
		// 获得订单商家名称
		
		$sql2 ="select concat(factory_user.nickname,'(',user.name ,')') as cust_name  from cc_user user ,cc_user_factory factory_user where user.id=factory_user.user_id and user.id=".$data['userId'];
         $data2 = $mdl_wj_customer_coupon->getListBySql($sql2);    
	   $data['money'] = $data1[0]['ori_sum'];
        $data['ajust_sum'] = $data1[0]['ajust_sum'];
		 $data['cust_name'] = $data2[0]['cust_name'];
		 
		 
		 // 获得该订单的销售员名称 如果有的话
		 
		 $sqlsalesman = "select b.name,b.contactPersonNickName  from cc_user_factory a left join cc_user b on a.factory_sales_id =b.id  where a.user_id =".$data['userId'];
		 $salesmanList = $mdl_order->getListBySql($sqlsalesman);
		 if($salesmanList) {
			 $salesmanName =$salesmanList[0]['contactPersonNickName'];
			 if(!$salesmanName){
				 
				  $salesmanName =$salesmanList[0]['name'];
			 }
			 
		 }
        $data['salesmanName'] =$salesmanName;
		//var_dump($data);exit;
        //coding end
        $this->setData($data, 'data');

        $moneyDetail = $mdl_order->getMoneyDetail($orderId);
        $this->setData($moneyDetail, 'moneyDetail');

        //订单详情
        $items = $mdl_wj_customer_coupon->getList(null, ['order_id' => $orderId]);

        // 获取当前订单和当前客户的关系
        // 返回结果：
        // status 1 : 当前用户为普通查看者，不是订单的商家
        // status 2 : 当前用户为当前订单唯一商家。
        // status 3 : 当前用户为当前订单的某一个商家（该情况存在于如果该订单为统配中心订单的情况） 。
        // status 4 : 当前用户为配货中心管理用户，拥有完整权限管理权和数据管理权
        // 每种状态对应相应的显示页面。

        // 如果当前用户就是订单中的商家，代表，其是通配中心商家。
        if ($data['business_userId'] ==$busi_id) {
            $display_status = 4;
        } else {
            $order_details_and_related = $this->get_order_details_and_related($items,$busi_id);
            $items = $order_details_and_related['data'];
            $display_status = $order_details_and_related['status'];
        }

        $this->setData($this->get_order_amend_reson_type_list(), 'order_amend_reson_type_list');

        // 获取该订单变更信息
        $mdl_order_amend = $this->loadModel('order_amend');
        $item_order_amend = $mdl_order_amend->getList(null, ['order_id' => $orderId]);
        foreach ($items as $key => $val) {

            $items[$key]['reason_type'] = '0';
            foreach ($item_order_amend as $key1 => $value) {

                if ($value['item_buying_id'] == $val['id']) {
                    $items[$key]['reason_type'] = $value['reason_type'];
                    $items[$key]['reason_type_desc'] = $this->get_order_amend_reason_type_desc($value['reason_type']);
                }
            }
        }
        $this->setData($items, 'items');
        //allow 4px
        $allowFourpx = true;
        foreach ($items as $item) {
            $coupon = $mdl_coupons->get($item['bonus_id']);
            if (! $coupon['fourpx_sku']) {
                $allowFourpx = false;//订单中有非云仓产品，无法一起云仓发货。
                break;
            }
        }
        $this->setData($allowFourpx, 'allowFourpx');

        //desc info
        $firstItemId = $items[0]['bonus_id'];

        $info = $mdl_coupons->get($firstItemId);
        $info['delivery_description'];
        $info['pickup_des'];
        $info['offline_pay_des'];

        //special info
        $info['visibleForBusiness'];//
        $info['bonusType'];//

        $this->setData($info, 'info');

        //pickup Loaction
        $puckupLocation = $this->loadModel('user')->getUserById($data['business_staff_id']);
        $pickupInfo['pickupname'] = $puckupLocation['contactPersonNickName'];
        $pickupInfo['pickupaddress'] = $puckupLocation['googleMap'];
        $pickupInfo['pickupphone'] = $puckupLocation['contactMobile'];

        $this->setData($pickupInfo, 'pickupInfo');

        //group pin
        $mdl_group_pin = $this->loadModel('group_pin');
        $user_group_id = $mdl_group_pin->hasUserGroup($orderId);
        if ($user_group_id) {
            $userGroup = $this->loadModel('group_pin_user_group')->get($user_group_id);
            $this->setData($userGroup, 'userGroup');
        }

        //log
        $activity_log = $this->loadModel('wj_user_coupon_activity_log')->getList(null, ['orderId' => $orderId]);
        foreach ($activity_log as $k => $l) {
            $activity_log[$k]['cn_description'] = $mdl_coupons->actionlist_info($l['action_id']);
        }
        $this->setData($activity_log, 'log');

        //system log
        $this->loadModel('order_operation_log')->order($orderId)->log();

        $days = (time() - $data['createTime']) / (24 * 60 * 60);

        $this->setData($mdl_order->isOrderEditable($orderId), 'editAble');
        $this->setData($days, 'days');

        $this->setData('online_center', 'menu');
        $this->setData('customer_coupon_process', 'submenu');

        $this->setData('订单详情 - 商家中心 - '.$this->site['pageTitle'], 'pageTitle');

        //根据状态转向不同的显示页面
        // status 1 : 当前用户为普通查看者，不是订单的商家
        // status 2 : 当前用户为当前订单唯一商家。
        // status 3 : 当前用户为当前订单的某一个商家（该情况存在于如果该订单为统配中心订单的情况） 。
        // status 4 : 当前用户为配货中心管理用户，拥有完整权限管理权和数据管理权
        if (in_array($display_status, [2, 3, 4])) {
			//var_dump('here');exit;
            $this->display('factory/customer_order_detail_full_control');
        } else {
            $this->display('factory/customer_order_detail_gen');
        }
    }

    public function update_customer_coupon_subtotal_action()
    {
        if (is_post()) {
            $mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');

            $id = post('id');

            $adjust_subtotal_amount = post('adjust_subtotal_amount');
            $sql = "select id,order_id,bonus_id,voucher_deal_amount,business_id,customer_buying_quantity,platform_commission_rate,platform_commission_base  from cc_wj_customer_coupon  where id=".$id;

            $list = $mdl_wj_customer_coupon->getListBySql($sql);
            if (! $list) {
                $this->form_response(600, '未发现产品', '未发现产品');
            } else {
                $uni_business_id = $list[0]['business_id'];
                $sql1 = "select business_id  from cc_freshfood_disp_centre_suppliers  where suppliers_id =".$uni_business_id;
                $tongpei_busi_rec = $mdl_wj_customer_coupon->getListBySql($sql1);
                if ($tongpei_busi_rec) {

                    if (($tongpei_busi_rec[0]['business_id'] != $this->loginUser['id']) && ($uni_business_id != $this->loginUser['id'])) {
                        $this->form_response(600, 'aa无授权', 'aa无授权');
                    }
                }

                $orderid = $list[0]['order_id'];
                $coupon_id = $list[0]['bonus_id'];

                if (! is_numeric($adjust_subtotal_amount)) {
                    $this->form_response(600, '请输入数字。', '不能高于');
                }
                $items_subtotal_old = $list[0]['voucher_deal_amount'] * $list[0]['customer_buying_quantity'];
                if ($adjust_subtotal_amount > $items_subtotal_old) {
                    $hint = $list[0]['voucher_deal_amount'] * $list[0]['customer_buying_quantity'].'(价格$'.(string) $list[0]['voucher_deal_amount'].'*数量'.(string) $list[0]['customer_buying_quantity'].')';
                    $this->form_response(600, '新的价格不能高于客户之前该产品购买总额：$'.$hint, '不能高于');
                }
                if ($adjust_subtotal_amount < 0) {

                    $this->form_response(600, '调整的价格不能小于0，最低为0');
                }
            }

            $mdl_order = $this->loadModel('order');
            $order_record = $mdl_order->getByWhere(['orderId' => $orderid]);

            // 下面操做的商家在非统配中心时，order_rec 里面的business_userid 就是和 uni_business_id 是一个，但是在统配的情况下，就出现了不一致，导致整个退款出现问题，所以，在这个阶段，将 该记录的 商家Id字段替换成
            // 当前操做的记录对应的商家字段 ， 对于非统配商家 没有影响，对于统配商家，则 将统配商家id 改成 对应的实际商家id ,然后保证退款的账户争取。下面这一行记录进行了替换。

            $order_record['business_userId'] = $uni_business_id;
            if (! $order_record) {
                $this->form_response(500, '未查到订单信息！');
            }
            // 准备好数据插入到 订单报损表种

            $mdl_order_amend = $this->loadModel('order_amend');

            $where_order_amend = [

                'item_buying_id' => $id,
            ];

            $data_order_amend = [];

            if ($mdl_order_amend->getByWhere($where_order_amend)) {
                //查找到
                $data_order_amend['new_sub_total'] = $adjust_subtotal_amount;

                // 这条语句 后期放到 begin he commit 里面
                $mdl_order_amend->updateByWhere($data_order_amend, $where_order_amend);
            } else {
                //未查找到 ，则新增
                $data_order_amend['item_buying_id'] = $id;
                $data_order_amend['order_id'] = $orderid;
                $data_order_amend['createTime'] = time();
                $data_order_amend['reason_type'] = '0';
                $data_order_amend['old_sub_total'] = $items_subtotal_old;
                $data_order_amend['new_sub_total'] = $adjust_subtotal_amount;
                $data_order_amend['createUserId'] = $uni_business_id;

                $mdl_order_amend->insert($data_order_amend);
            }

            // 查找 order_amend 是否已经存在该 item_id ,如果存在，则建立修改数据，否则创建新增数组

            $data = [];
            if (isset($adjust_subtotal_amount)) {
                $data['adjust_subtotal_amount'] = $adjust_subtotal_amount;
            }

            try {
                $mdl_recharge = $this->loadModel('recharge');
                // 开始一个事务 ，如果不成攻就一次性回滚
                $mdl_wj_customer_coupon->begin();

                //修改 订单 向表 将汇总数量改为填入的数量。
                if (! $mdl_wj_customer_coupon->update($data, $id)) {
                    $mdl_wj_customer_coupon->rollback;
                    $error_table = 'wj_customer_coupon';
                    $this->form_response(500, $error_table, '');
                }

                $sql = "select * from cc_wj_customer_coupon  where order_id=".$orderid;//. " and business_id=".$this->loginUser['id'];
                $wj_customer_coupon = $mdl_wj_customer_coupon->getListBySql($sql);

                //以下参数是整个计算的依据
                $new_items_total_money = 0;  //改变后该订单的货品总值
                $old_total_amount_this_order = 0;  //订单之前的货品总值
                $old_commission_total = 0;   // 改变之前commssion 总值
                $new_commsision_total = 0;   //改变之后该订单总值

                foreach ($wj_customer_coupon as $key => $value) {
                    if ($id == $wj_customer_coupon[$key]['id']) {
                        $new_items_total_money += $adjust_subtotal_amount;

                        if ($value['voucher_deal_amount'] > 0) { //对于免费产品 不计算
                            $new_commsision_total += $adjust_subtotal_amount * $value['platform_commission_rate'] + ($adjust_subtotal_amount / $value['voucher_deal_amount']) * $value['platform_commission_base'];
                        }
                    } else {
                        $new_items_total_money += $wj_customer_coupon[$key]['adjust_subtotal_amount'];
                        if ($value['voucher_deal_amount'] > 0) { //对于免费产品 不计算
                            $new_commsision_total += $value['adjust_subtotal_amount'] * $value['platform_commission_rate'] + ($value['adjust_subtotal_amount'] / $value['voucher_deal_amount']) * $value['platform_commission_base'];
                        }
                    }
                    $old_commission_total += ($value['voucher_deal_amount'] * $value['customer_buying_quantity']) * $value['platform_commission_rate'] + $value['customer_buying_quantity'] * $value['platform_commission_base'];
                    $old_total_amount_this_order += $value['voucher_deal_amount'] * $value['customer_buying_quantity'];
                }

                $amount_change = $old_total_amount_this_order - $new_items_total_money;

                //新的订单总额，不包含surcharge
                $new_items_total_money_withou_sur = $new_items_total_money + $order_record['delivery_fees'] + $order_record['booking_fees'];

                // 通过计算 比率，计算出新的surcharge ,将来这笔surcharge 要返到用户钱包。
                $surcharge_new = $new_items_total_money_withou_sur * ($order_record['surcharge'] / ($order_record['money'] - $order_record['surcharge']));

                // 根据新获得的订单，反推新的手续费
                // 将在订单order 的表中 重新计算 新的订单总额，并更新，重新计算surcharge ,并更新到cc_order种,cc_order增加两个字段

                $new_items_total_money = $new_items_total_money_withou_sur + $surcharge_new;

                $data_order = [
                    'money_new' => $new_items_total_money,
                    'surcharge_new' => $surcharge_new,

                ];
                $where = [
                    'orderId' => $orderid,
                ];
                //更新 order中的 moneynew ,surchargenew ,

                if (! $mdl_order->updateByWhere($data_order, $where)) {
                    $mdl_wj_customer_coupon->rollback;
                    $error_table = 'wj_customer_coupon';
                    $this->form_response(500, $error_table);
                }

                // commsiion 计算为： 按照订单总额的新值，计算变化率， 然后获取该订单commission分配数据，计算出修补commsion数据， 如果找到对应记录则修改，未找到则插入。
                // 目前这里有一个计算需要再精确，就是 money_new 是产品购买计算，并未包含 handing fee  delviery fee surcharge 等，而 money 是order 整个的汇总
                // 因此  money_new 的计算需要重新处理一下。
                // commission 不会分 handinigling fee ,和 surcharge ,但会分 delivery fee
                // 因此计算commsiion 的波动比率 的经计算如 -也就是 实际销售额的波动比率

                ///**** 此村非常复杂 ， 当但商家时 这里上面的计算不存在问题，但是当多商家时，(通配),给的数字是该商家的。所以重构

                $sql = "select * from cc_wj_customer_coupon  where order_id=".$orderid." and business_id=".$uni_business_id;
                $wj_customer_coupon = $mdl_wj_customer_coupon->getListBySql($sql);

                //以下参数是整个计算的依据
                $new_items_total_money = 0;  //改变后该订单的货品总值
                $old_total_amount_this_order = 0;  //订单之前的货品总值
                $old_commission_total = 0;   // 改变之前commssion 总值
                $new_commsision_total = 0;   //改变之后该订单总值
                $current_items_amount_change = $items_subtotal_old - $adjust_subtotal_amount; //当前销售记录 的金额变化

                foreach ($wj_customer_coupon as $key => $value) {
                    if ($id == $wj_customer_coupon[$key]['id']) {
                        $new_items_total_money += $adjust_subtotal_amount;

                        if ($value['voucher_deal_amount'] > 0) { //对于免费产品 不计算
                            $new_commsision_total += $adjust_subtotal_amount * $value['platform_commission_rate'] + ($adjust_subtotal_amount / $value['voucher_deal_amount']) * $value['platform_commission_base'];
                        }
                    } else {
                        $new_items_total_money += $wj_customer_coupon[$key]['adjust_subtotal_amount'];
                        if ($value['voucher_deal_amount'] > 0) { //对于免费产品 不计算
                            $new_commsision_total += $value['adjust_subtotal_amount'] * $value['platform_commission_rate'] + ($value['adjust_subtotal_amount'] / $value['voucher_deal_amount']) * $value['platform_commission_base'];
                        }
                    }

                    $old_commission_total += ($value['voucher_deal_amount'] * $value['customer_buying_quantity']) * $value['platform_commission_rate'] + $value['customer_buying_quantity'] * $value['platform_commission_base'];

                    $old_total_amount_this_order += $value['voucher_deal_amount'] * $value['customer_buying_quantity'];
                }

                // 该order 总的价格改变，包括其他订单改变导致的。这个改变做为计算其它的总体参考额度 。

                //  这一段计算该单产品 销售额变更后 commsiion 的变化数值。
                $amount_change_singlebusiness = $old_total_amount_this_order - $new_items_total_money;

                // 在多商家统配下，某一个被修改的产品当前商家，的总量变化
                $total_commission_change = $old_commission_total - $new_commsision_total;
                $recharge_record_list = $mdl_recharge->getListBySql("select * from cc_recharge where orderId =".$orderid);

                // 查看当前 借计项是否存在 Ubonus commission
                $this->generate_amand_data($mdl_recharge, $recharge_record_list, 'UBONUS_COMMISSION', $total_commission_change, null, null, $uni_business_id);

                // 处理 agent 运营代理商的 commssion ajust
                // 获得运营代理商的记录， 找到 commission 的值， 计算出比率，然后，在总的 commission_change 乘一个百分比，调用处理程序即可
                $rate_percentage = 0;
                $record_of_agent_commission = $this->findCertainItemRecharge($recharge_record_list, 'UBONUS_PAY_AGENT_COMMISSION', '-10001');
                if ($record_of_agent_commission) {
                    $agent_commission_money = (-1) * $record_of_agent_commission['money'];
                    // 找 总commission
                    $record_of_ubonus_commission = $this->findCertainItemRecharge($recharge_record_list, 'UBONUS_COMMISSION', '-10001');
                    if ($record_of_ubonus_commission) {
                        $ubonus_commisison = $record_of_ubonus_commission['money'];
                        $rate_percentage = $agent_commission_money / $ubonus_commisison;
                    }
                }

                $agent_commission_change_amount = $total_commission_change * $rate_percentage;
                $this->generate_amand_data($mdl_recharge, $recharge_record_list, 'UBONUS_PAY_AGENT_COMMISSION', (-1) * $agent_commission_change_amount);

                //累计balance 变更
                $this->generate_amand_data_transcationbalance($mdl_recharge, $recharge_record_list, $amount_change, $order_record, $amount_change_singlebusiness);

                //累计surcharge变更
                $surcharge_change_amount = $order_record['surcharge'] - $surcharge_new;

                // 如果是商家支付 surcharge 下面的程序是对的，如果是用户承担surcharge 那么需要将返回的钱 付给用户的钱包。
                //获得该订单是使用那种支付方式下单及该订单的surcharge是由谁来支付
                $mdl_user = $this->loadModel('user');
                $payment_type = $order_record['payment'];
                $current_user = $mdl_user->get($order_record['business_userId']);

                if ($payment_type == 'royalpay') {

                    if ($current_user['transactionFeeChargeFrom_royalpay'] == 'user') {
                        if ($uni_business_id) {

                            $this->generate_amand_data_TRANSACTION_FEE($mdl_recharge, $recharge_record_list, 'TRANSACTION_FEE_PLATFORM_CHARGE', $surcharge_change_amount, $order_record['userId']);
                        } else {
                            $this->generate_amand_data($mdl_recharge, $recharge_record_list, 'TRANSACTION_FEE_PLATFORM_CHARGE', $surcharge_change_amount, $coupon_id, $order_record['userId']);
                        }
                    } else {
                        if ($current_user['transactionFeeChargeFrom_royalpay'] == 'business') {

                            $this->generate_amand_data($mdl_recharge, $recharge_record_list, 'TRANSACTION_FEE_PLATFORM_CHARGE', $surcharge_change_amount);
                        }
                    }
                } else {
                    if ($payment_type == 'paypal') {
                        if ($current_user['transactionFeeChargeFrom_paypal'] == 'user') {
                            if ($uni_business_id) {
                                $this->generate_amand_data_TRANSACTION_FEE($mdl_recharge, $recharge_record_list, 'TRANSACTION_FEE_PLATFORM_CHARGE', $surcharge_change_amount, $order_record['userId']);
                            } else {
                                $this->generate_amand_data($mdl_recharge, $recharge_record_list, 'TRANSACTION_FEE_PLATFORM_CHARGE', $surcharge_change_amount, $coupon_id, $order_record['userId']);
                            }
                        } else {
                            if ($current_user['transactionFeeChargeFrom_paypal'] == 'business') {

                                $this->generate_amand_data($mdl_recharge, $recharge_record_list, 'TRANSACTION_FEE_PLATFORM_CHARGE', $surcharge_change_amount);
                            }
                        }
                    } else {
                        if ($payment_type == 'creditcard') {

                            if ($current_user['transactionFeeChargeFrom_creditcard'] == 'user') {

                                if ($uni_business_id) {
                                    $this->generate_amand_data_TRANSACTION_FEE($mdl_recharge, $recharge_record_list, 'TRANSACTION_FEE_PLATFORM_CHARGE', $surcharge_change_amount, $order_record['userId']);
                                } else {
                                    $this->generate_amand_data($mdl_recharge, $recharge_record_list, 'TRANSACTION_FEE_PLATFORM_CHARGE', $surcharge_change_amount, $coupon_id, $order_record['userId']);
                                }
                            } else {
                                if ($current_user['transactionFeeChargeFrom_creditcard'] == 'business') {
                                    $this->generate_amand_data($mdl_recharge, $recharge_record_list, 'TRANSACTION_FEE_PLATFORM_CHARGE', $surcharge_change_amount);
                                }
                            }
                        }
                    }
                }

                // 计算该产品 到推荐客户中的比率
                // 1 ： 在cc_charge中找到改产品， 通过coupon_id/orderid/CUSTOMER_REF_COMMISSION/大于0 / 三个条件  获得 commission 值，
                // 2 ： 然后 找到改产品的销售额 ， quantity *voucher_deal_amount
                // 3 : 计算commission比率 （因为有的用户commission 高，有的底，不是一致的。 this_coupon_customer_ref_commission_rate =
                // 4 ：  计算销售额变化导致分配到推荐人commission变化数额   change_amount * this_coupon_customer_ref_commission_rate
                // 5 :  调用 generrate_amand_data 进行增加或修改，但是，因为在一笔交易中可能有多比单项享受，我们只能对该条检录进行处理。因此需要再增加一个搜索条件
                //       就是 cc-recharge coupon_id/orderid/CUSTOMER_REF_COMMISSION_amend ，而不是之前的  /orderid/CUSTOMER_REF_COMMISSION_amend
                //         两个搜索项， 也就是可能存在多个 commision _ref_customer 退款 ，逐个对应保持只有一条。

                $current_coupon_customer_ref_record = $this->findCertainItemRecharge($recharge_record_list, 'CUSTOMER_REF_COMMISSION', 1, $coupon_id);
                if ($current_coupon_customer_ref_record) {
                    $commision_cust_ref = $current_coupon_customer_ref_record['money'];
                    $coupon_customer_ref_commission_rate = $commision_cust_ref / $items_subtotal_old;
                    $cust_ref_commission_change = $current_items_amount_change * $coupon_customer_ref_commission_rate;
                    $this->generate_amand_data($mdl_recharge, $recharge_record_list, 'CUSTOMER_REF_COMMISSION', (-1) * $cust_ref_commission_change, $coupon_id);
                }

                $mdl_wj_customer_coupon->commit();
                $this->form_response(200);
            } catch (Exception $e) {
                $mdl_wj_customer_coupon->rollback();
                $this->form_response(500, $e->getMessage());
            }
        }
    }

    public function update_customer_coupon_detail_action()
    {
        if (! is_post()) {
            return;
        }

        $mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');
        $mdl_order = $this->loadModel('order');

        $id = post('id');
        $data = [];
        $quantity = post('customer_buying_quantity');
        if (isset($quantity)) {
            $data['customer_buying_quantity'] = $quantity;
        }
        $amount = post('voucher_deal_amount');
        if (isset($amount)) {
            $data['voucher_deal_amount'] = $amount;
        }

        $customerCoupon = $mdl_wj_customer_coupon->get($id);
        if (! $customerCoupon) {
            $this->form_response(600, '未发现产品', '未发现产品');

            return;
        }

        //检查权限
		
		
        $uni_business_id = $customerCoupon['business_id'];
        $sql1 = "select business_id  from cc_freshfood_disp_centre_suppliers  where suppliers_id =".$uni_business_id;
        $tongpei_busi_rec = $mdl_wj_customer_coupon->getListBySql($sql1);
        if ($tongpei_busi_rec) {

            if (($tongpei_busi_rec[0]['business_id'] != $this->loginUser['id']) && ($uni_business_id != $this->loginUser['id'])) {
				
				// 销售员的用户role 为101

				  if($this->loginUser['role']==101) {
					  
					  $business_userid = $this->loginUser['user_belong_to_user'];
					  if (($tongpei_busi_rec[0]['business_id'] !=   $business_userid) && ($uni_business_id !=   $business_userid)) {
						  
						  $this->form_response(600, 'no access', 'no access');
						  
					  }
					 
				  }else{
					  
					   $this->form_response(600, 'no access', 'no access');
				  }
				  
				  
				  
               
            }
        }

        $customerCouponData = [
            'customer_buying_quantity' => $customerCoupon['customer_buying_quantity'],
            'voucher_deal_amount' => $customerCoupon['voucher_deal_amount'],
            'adjust_subtotal_amount' => $customerCoupon['adjust_subtotal_amount'],
        ];
        $orderPriceChange = 0;
        foreach ($data as $key => $value) {
            if (! is_numeric($value)) {
                $this->form_response(600, '请输入数字。');

                return;
            }
            if ($value < 0) {
                $this->form_response(600, '数字不能小于0');

                return;
            }

            $customerCouponData[$key] = $value;

            $newCustomerCouponPrice = $customerCouponData['customer_buying_quantity'] * $customerCouponData['voucher_deal_amount'];

            $orderPriceChange += $newCustomerCouponPrice - $customerCouponData['adjust_subtotal_amount'];
            $customerCouponData['adjust_subtotal_amount'] = round($newCustomerCouponPrice, 2);
            $mdl_wj_customer_coupon->update($customerCouponData, $id);

            if ($key == 'voucher_deal_amount') {
                $mdl_user_factory_menu_price = $this->loadModel('user_factory_menu_price');
                $mdl_user_factory_menu_price->insertOrUpdateUserFactoryPrice($customerCoupon['userId'], $customerCoupon['restaurant_menu_id'], $customerCouponData['voucher_deal_amount']);
            }
        }

        if ($orderPriceChange != 0) {
            $order = $mdl_order->get($customerCoupon['order_id']);

            $orderUpdateData = [
                'money' => round($order['money'] + $orderPriceChange, 2),
                'money_new' => round($order['money_new'] + $orderPriceChange, 2),
            ];
            $mdl_order->update($orderUpdateData, $order['id']);
        }

        $this->form_response(200, $customerCouponData['adjust_subtotal_amount']);
    }

    public function find_customer_abn_action()
    {
        $abn = trim(get2('abn'));
        $mdl_wj_abn_application = $this->loadModel('wj_abn_application');

        $uerAbn = $mdl_wj_abn_application->getByWhere([ 'ABNorACN' => $abn]);
        if($uerAbn) {
            $user = $this->loadModel('user')->getUserById($uerAbn['userId']);
            $result = [
                'user_id' => $uerAbn['userId'],
                'username' => $user['name'],
                'mobile' => $user['phone'],
                'nickname' => $uerAbn['untity_name'],
                'address' => $user['address'],
            ];
            $this->form_response(200, $result);
        } else {
            $this->form_response(201,false);
        }

        return;
    }
	
	
	
	
    public function edit_customer_action()
    {
        if (is_post()) {
            $userId = trim(post('user-id'));
            if($userId) {
                self::approve_user_action($userId, null,true);
                header("Location: ".  HTTP_ROOT_WWW."factory/customer_list");
            } else {
                $username = trim(post('username'));
                $mobile = trim(post('mobile'));
                $abn = str_replace(' ', '', trim(post('abn')));
				if(!$abn) $abn='00000000000';
                $addrAddress = trim(post('address'));
                $addrNumber = trim(post('addr_house_number'));
                $addrStreet = trim(post('addr_street'));
                $addrPost = trim(post('addr_post_code'));
                $addrSuburb = trim(post('addr_city'));
                $addrState = trim(post('addr_state'));
                $country = trim(post('addr_country'));
                $nickname = trim(post('nickname'));

                $address = [
                    'address' => $addrAddress,
                    'addrNumber' => $addrNumber,
                    'addrStreet' => $addrStreet,
                    'addrPost' => $addrPost,
                    'addrSuburb' => $addrSuburb,
                    'addrState' => $addrState,
                    'country' => $country,
                ];

                if (! $this->loadModel('reg')->chkABN($abn)) {
                    $this->setData($this->lang->remind_user_register_17, 'message');
                    $this->setData($username, 'username');
                    $this->setData($mobile, 'mobile');
                    $this->setData($abn, 'abn');
                    $this->setData($addrAddress, 'addrAddress');
                    $this->setData($addrNumber, 'addrNumber');
                    $this->setData($addrStreet, 'addrStreet');
                    $this->setData($addrSuburb, 'addrSuburb');
                    $this->setData($addrState, 'addrState');
                    $this->setData($addrPost, 'addrPost');
                    $this->setData($country, 'country');
                    $this->setData($nickname, 'nickname');
                } else {
                    $result = self::add_new_customer($username, $mobile, $address);

                    if($result['success']) {
                        $this->loadModel('wj_abn_application')->insert([
                            'userId' => $result['userId'],
                            'business_name' => $username,
                            'ABNorACN' => $abn,
                            'untity_name' => $nickname,
                            'createDate' => time(),
                            'isApproved' => 1,
                        ]);
                        self::approve_user_action($result['userId'], null, true);
                        header("Location: ".  HTTP_ROOT_WWW."factory/customer_list");
                    } else {
                        $this->setData($result['result'], 'message');
                    }
                }
            }
        }else{
			//get abn info
			
			
			
		}
        $this->setData('customer_management', 'menu');
        $this->setData('add_new_customer', 'submenu_top');
        $this->setData('add_new_customer', 'submenu');
        $this->display('factory/edit_customer');

        return;
    }

 public function edit_customer1_action()
    {
		  $userId = trim(get2('user_Id'));
		  $this->setData($userId,'userId');
        if (is_post()) {
			//var_dump('here');exit;
            $userId = trim(post('userId'));
			//var_dump('userid is'.$userId);exit;
            if($userId)  {
				//var_dump($userId);exit;
				
				
				//abn info 
				$abn = str_replace(' ', '', trim(post('abn')));
				if(!$abn) $abn='00000000000';
				
				$untity_name= trim(post('untity_name'));
							
             
				
				
				
				//user_info 
				$phone = trim(post('phone'));
				
				$tel = trim(post('tel'));
				
				$contactPersonFirstname = trim(post('contactPersonFirstname'));
				
				$contactPersonLastname = trim(post('contactPersonLastname'));
				
				$email = trim(post('email'));
				$username = trim(post('username'));
				
				
				$googleMap = trim(post('address'));
				
				//factory_info 
				
				$factory_code = trim(post('factory_code'));
				
				$factory_sales_id = trim(post('factory_sales_id'));
				
				$approved = trim(post('approved'));

               

                if (! $this->loadModel('reg')->chkABN($abn)) {
                    $this->setData($this->lang->remind_user_register_17, 'message');
                   
                } else {
                   
                   $data_user=array(
				   //''=>$,
				   'googleMap'=>$googleMap,
				   'email'=>$email,
				   'contactPersonFirstname'=>$contactPersonFirstname,
				   'contactPersonLastname'=>$contactPersonLastname,
				   'tel'=>$tel,
				   'phone'=>$phone,
				   'name'=>$username
				   
				   );
				   $mdl_user = $this->loadModel('user');
				   $mdl_user->update($data_user,$userId);
				   
				   
				    $data_user_factory=array(
				   //''=>$,
				   'approved'=>$approved,
				   'factory_sales_id'=>$factory_sales_id,
				   'nickname'=>$factory_code
				   
				   
				   );
				     $mdl_user_factory = $this->loadModel('user_factory');
					 $FactoryId = $mdl_user_factory->getBusinessId($this->loginUser['id'],$this->loginUser['role']);
				   
				   $where =array(
				    'factory_id'=>$FactoryId,
					'user_id'=>$userId
				   );
				 
				   $mdl_user_factory->updateByWhere($data_user_factory,$where);
				   
				   
				   $data_abn =array(
				    'untity_name'=>$untity_name,
					'ABNorACN'=>$abn
				   
				   );
				    $mdl_wj_abn_applicationy = $this->loadModel('wj_abn_application');
					$where =array(
				   		'userId'=>$userId
				   );
				    $mdl_wj_abn_applicationy->updateByWhere($data_abn,$where);
					
					 header("Location: ".  HTTP_ROOT_WWW."factory/customer_list");
					
                   /*
                    if($result['success']) {
                        $this->loadModel('wj_abn_application')->insert([
                            'userId' => $result['userId'],
                            'business_name' => $username,
                            'ABNorACN' => $abn,
                            'untity_name' => $nickname,
                            'createDate' => time(),
                            'isApproved' => 1,
                        ]);
                        self::approve_user_action($result['userId'], null, true);
                        header("Location: ".  HTTP_ROOT_WWW."factory/customer_list");
                    } else {
                        $this->setData($result['result'], 'message');
                    }
					*/
                }
            }else{
				
				 $this->form_response(201, 'no user info!'); 
				return;
			}
        }else{
			//get all customer information 
			
			// 检查当前客户是否为该b2b商家可以操作
			
			// 获得相关的信息
			$mdl_user_factory = $this->loadModel('user_factory');
			$FactoryId = $mdl_user_factory->getBusinessId($this->loginUser['id'],$this->loginUser['role']);
			//var_dump($FactoryId);exit;
			if($mdl_user_factory->isUserAuthorisedToOperate($userId, $FactoryId)){
				
			//	var_dump('已授权');exit;
				
			}else{
				var_dump('no access');exit;
				
			}
			// 从cc_user 获取 name ,address ,phone ,contact person ,mobile ,email
			// 从cc_factory_user 获取销售员，简称，是否approved
			// 从 abn-application cc_wj_abn_application userId  	business_name 	ABNorACN untity_name isApproved
			
			$user =$this->loadModel('user')->get($userId);
			$where =array(
			     'user_id' => $userId,
               'factory_id' => $FactoryId
			);
			$user_factory_info =$this->loadModel('user_factory')->getByWhere($where);
			$where =array(
			     'userId' => $userId               
			);
			
			$user_abn =$this->loadModel('wj_abn_application')->getByWhere($where);
			//var_dump($user);
			//var_dump($user_factory_info);
			//var_dump($user_abn);
			
			
			
			// get sales list ...
			//如果该用户本身为销售员，则前端不显示该信息，后端也不读取，也不处理。
			// 如果用户为owner 则 获得 user_belong_to_user =该用户，且用户role=101
			
			if($this->loginUser['role']==101) {
				
				
			}else {
				$where =array (
				 'user_belong_to_user'=>$this->loginUser['id'],
				 'role'=>101
				);
				$sales_list = $this->loadModel('user')->getList(null,$where);
				//var_dump($sales_list);exit;
				$this->setData($sales_list,'sales_list');
				
			}
			
			
			
			
			
			$this->setData($user,'user');
			$this->setData($user_factory_info,'user_factory_info');
			$this->setData($user_abn,'user_abn');
			//exit;
			
			
			
			
			
			
		}
        $this->setData('customer_list', 'submenu_top');
        $this->setData('customer_list', 'submenu');
        $this->setData('customer_management', 'menu');
        $this->display('factory/edit_customer1');

        return;
    }


    public function add_new_customer_action()
    {
        if (is_post()) {
            $userId = trim(post('user-id'));
            if($userId) {
                self::approve_user_action($userId, null,true);
                header("Location: ".  HTTP_ROOT_WWW."factory/customer_list");
            } else {
                $username = trim(post('username'));
                $mobile = trim(post('mobile'));
                $abn = str_replace(' ', '', trim(post('abn')));
				if(!$abn) $abn='00000000000';
                $addrAddress = trim(post('address'));
                $addrNumber = trim(post('addr_house_number'));
                $addrStreet = trim(post('addr_street'));
                $addrPost = trim(post('addr_post_code'));
                $addrSuburb = trim(post('addr_city'));
                $addrState = trim(post('addr_state'));
                $country = trim(post('addr_country'));
                $nickname = trim(post('nickname'));

                $address = [
                    'address' => $addrAddress,
                    'addrNumber' => $addrNumber,
                    'addrStreet' => $addrStreet,
                    'addrPost' => $addrPost,
                    'addrSuburb' => $addrSuburb,
                    'addrState' => $addrState,
                    'country' => $country,
                ];

                if (! $this->loadModel('reg')->chkABN($abn)) {
                    $this->setData($this->lang->remind_user_register_17, 'message');
                    $this->setData($username, 'username');
                    $this->setData($mobile, 'mobile');
                    $this->setData($abn, 'abn');
                    $this->setData($addrAddress, 'addrAddress');
                    $this->setData($addrNumber, 'addrNumber');
                    $this->setData($addrStreet, 'addrStreet');
                    $this->setData($addrSuburb, 'addrSuburb');
                    $this->setData($addrState, 'addrState');
                    $this->setData($addrPost, 'addrPost');
                    $this->setData($country, 'country');
                    $this->setData($nickname, 'nickname');
                } else {
                    $result = self::add_new_customer($username, $mobile, $address);

                    if($result['success']) {
                        $this->loadModel('wj_abn_application')->insert([
                            'userId' => $result['userId'],
                            'business_name' => $username,
                            'ABNorACN' => $abn,
                            'untity_name' => $nickname,
                            'createDate' => time(),
                            'isApproved' => 1,
                        ]);
                        self::approve_user_action($result['userId'], null, true);
                        header("Location: ".  HTTP_ROOT_WWW."factory/customer_list");
                    } else {
                        $this->setData($result['result'], 'message');
                    }
                }
            }
        }
        $this->setData('customer_management', 'menu');
        $this->setData('add_new_customer', 'submenu_top');
        $this->setData('add_new_customer', 'submenu');
        $this->display('factory/add_new_customer');

        return;
    }
	
	
	
	

    public function update_user_account_action() {
        $mdl_reg = $this->loadModel('reg');

        $abn = trim(post('abn'));
        $password = trim(post('password'));
        $passwordConfirm = trim(post('password-confirm'));
        $username = trim(post('username'));
        $user = $this->loadModel('user')->getUserByName($username);

        if ( $user && $user['id'] != $this->loginUser['id']) {
            $this->form_response(201, $this->lang->remind_user_register_2);
            return;
        }

        if (!$mdl_reg->chkABN($abn)) {
            $this->form_response(201, $this->lang->remind_user_register_17);
            return;
        }

        if ($password != $passwordConfirm) {
            $this->form_response(201, $this->lang->remind_user_register_7);
            return;
        }

        if (! $mdl_reg->chkPassword($password)) {
            $this->form_response(201, $this->lang->remind_user_register_8);
            return;
        }

        $this->loadModel('user')->updateByWhere([
            'password' => $this->md5($password),
            'init_password' =>$this->md5($password),
            'name' => $username,
            'phone' => trim(post('mobile')),
            'address' => trim(post('address')),
            'googleMap' => trim(post('address')),
            'addrNumber' => trim(post('addr_house_number')),
            'addrStreet' => trim(post('addr_street')),
            'addrPost' => trim(post('addr_post_code')),
            'addrSuburb' => trim(post('addr_city')),
            'addrState' => trim(post('addr_state')),
            'country' => trim(post('addr_country')),
        ], [
            'id' => $this->loginUser['id']
        ]);

        $this->loadModel('wj_abn_application')->updateByWhere([
            'ABNorACN' => $abn
        ], [
            'userId' => $this->loginUser['id']
        ]);
        $this->form_response(200, '');
        return;
    }

    public function add_new_customer($username, $mobile = '', $address = [])
    {
        $mdl_user = $this->loadModel('user');

        $result = [
            'success' => false,
            'result' => null,
        ];

        if ($mdl_user->getCount("name='$username'") >= 1) {
            $result['result'] = $this->lang->remind_user_register_2;
            return $result;
        }

        $password = 'default';

        $userObject = new User();
        $userObject->setPassword($password);
        $userObject->setInitPassowrd($password);

        $userObject->setName($username);

        $userObject->setBusinessMobile($mobile, true);
        $userObject->setAddress($address['address']);

        $mdl_user->begin();
        $mdl_user->addUser(array_merge($userObject->toDBArray(), $address));

        $user = $mdl_user->getUserByName($username);

        $mdl_user->setTrustLevel(1, $user['id']);

        if ($mdl_user->errno()) {
            $mdl_user->rollback();
            $result['result'] = $this->lang->remind_user_register_8;
        } else {
            $mdl_user->commit();
            $userId = $mdl_user->getUserByName($username)['id'];
            self::approve_user_action($userId);
            $result['result'] = $username;
            $result['success'] = true;
            $result['userId'] = $userId;
        }

        return $result;
    }

    public function approve_user_action($userId, $factoryId = null, $approve = 0) {
        $mdl_user_factory = $this->loadModel('user_factory');

        if(!$approve) {
            $approve = trim(get2('approve'));
        }

        if(!$factoryId){
            $factoryId = trim(get2('factory_id'));
        }

        if(!$factoryId){
			//如果该用户的 role =101 ，表示该用户为销售员，其不是工厂商家id ,需要找到工厂商家ID，并且要插入一个字段值告知系统，是那个销售员创建的商家。
			if($this->loginUser['role']==101) {
				$factoryId = $mdl_user_factory->getFactoryId($this->loginUser['id']);
				$salesManId =  $this->loginUser['id'];
				
			}else{
				$factoryId = $this->loginUser['id'];
			}
            
        }

        if(!$userId) {
            $userId = trim(get2('user_id'));

            $mdl_user_factory->updateApprove($userId, $factoryId, $approve,$salesManId);
            header("Location: ".  HTTP_ROOT_WWW."factory/customer_list");
        } else {
            $mdl_user_factory->updateApprove($userId, $factoryId, $approve,$salesManId);
        }

        return;
    }

    public function show_origin_price_action($userId, $showOriginPrice) {
        if(!$showOriginPrice) {
            $showOriginPrice = trim(get2('show_origin_price'));
        }

        if(!$userId) {
            $userId = trim(get2('user_id'));
        }

        $update = ['show_origin_price' => $showOriginPrice];

        $where = [
            'user_id' => $userId,
            'factory_id' => $this->loginUser['id']
        ];

        $this->loadModel('user_factory')->updateByWhere($update, $where);

        header("Location: ".  HTTP_ROOT_WWW."factory/customer_list");
    }

		public function update_business_code_action(){

			if(is_post()){
				
				$mdl_user_factory =$this->loadModel("user_factory");
				
				$id = post('id');
				
			   // 判断如果当前登陆用户和当前操作的记录不是所属关系拒绝操作。
			   
			   
				$factory_user = $mdl_user_factory->get($id);
				$FactoryId =$mdl_user_factory->getBusinessId($this->loginUser['id'],$this->loginUser['role']);
				
				if ($factory_user['factory_id'] != $FactoryId) {
					 $this->form_response(600,'no access','no access');
					
				}
			
			
				$data=array();

				$code = post('code');
				if($code)$data['nickname']=$code;
				
			

				try {
					$mdl_user_factory->update($data,$id);
				
					
					$this->form_response(200,'','');
				} catch (Exception $e) {
					$this->form_response(500, $e->getMessage(),'');
				}

			}else{
				//wrong protocol
			}
		}
	public	 function staff_sales_action()
    {
        $id = (int)get2('id');
        $mdl_user = $this->loadModel('user');
      
        $where = array('role' => 101, 'user_belong_to_user' => $this->loginUser['id']);
        $list = $mdl_user->getList(null, $where, 'createdDate asc');
        $this->setData($list, 'list');

        $this->setData('销售员管理', 'pagename');
        $this->setData('staff_sales', 'submenu');
        $this->setData('advanced_setting', 'menu');
        $this->setData('销售员管理 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
        $this->display('factory/staff_sales');
    }
 public   function staff_sales_edit_action()
    {   
        $mdl_user = $this->loadModel('user');
        $mdl_reg = $this->loadModel('reg');

        $id = (int)get2('id');

        $staff = $mdl_user->getByWhere(array('id' => $id, 'user_belong_to_user' => $this->loginUser['id']));

        if (is_post()) {
            /**
             * Location related data
             */
        

            /**
             * 管理员信息
             */
            $name = trim(post('username'));
            $email = trim(post('email'));

            $change_password = (int)post('change_password');
            $password = trim(post('password'));
            $password2 = trim(post('password2'));

            $person_first_name = trim(post('person_first_name'));
            $person_last_name = trim(post('person_last_name'));
            $tel = trim(post('tel'));
            $phone = trim(post('phone'));

           
            /**
             * 员工信息
             */
            $businessName = $this->loginUser['businessName'].'-分部';
            $contactPersonFirstname = trim(post('contactPersonFirstname'));
            $contactPersonLastname = trim(post('contactPersonLastname'));
            $contactPersonNickName = trim(post('contactPersonNickName'));
            $contactMobile = trim(post('contactMobile'));


            if ($staff) {
                $data = array(
                    // 'name' => $name,
                    'email' => $email,
                    'nickname' => $nickname,
                    'person_first_name' => $person_first_name,
                    'person_last_name' => $person_last_name,
                    'tel' => $tel,
                    'phone' => $phone,

                    'contactPersonFirstname'=>$contactPersonFirstname,
                    'contactPersonLastname'=>$contactPersonLastname,
                    'contactPersonNickName'=>$contactPersonNickName,
                    'contactMobile'=>$contactMobile

                  
                );

                if ($change_password) {
                    if (!$mdl_reg->chkPassword($password)) $this->form_response_msg('密码需要6-16个由a-z，A-Z，0-9以及下划线组成的字符串');

                    if ($password != $password2)$this->form_response_msg('确认密码与密码填写不一致');

                    $passwordByCustomMd5 = $this->md5($password);

                    $data['password'] = $passwordByCustomMd5;
                }
                

                if ($mdl_user->updateUserById($data, $staff['id'])) {

                    $this->form_response(200,'Saved successful',HTTP_ROOT_WWW.'factory/staff_sales');
                } else {
                    $this->form_response_msg('保存成功');
                }

            } else {
                if (empty($name) ) $this->form_response_msg('请填写用户名');

                if ($mdl_user->chkUserName($name) > 0)$this->form_response_msg('该用户名已经存在');

                if (!$mdl_reg->chkUserName($name))$this->form_response_msg((string)$this->lang->remind_user_register_5);

                if (empty($password)) $this->form_response_msg('请填写密码');

                if (!$mdl_reg->chkPassword($password)) $this->form_response_msg('密码需要6-16个由a-z，A-Z，0-9以及下划线组成的字符串');

                if ($password != $password2) $this->form_response_msg('确认密码与密码填写不一致');

               


                $passwordByCustomMd5 = $this->md5($password);
                

                $data = array(
                    'user_belong_to_user' => $this->loginUser['id'],
                    'isApproved' => 1,
                    'isAdmin' => 0,
                    'person_first_name' => $person_first_name,
                    'person_last_name' => $person_last_name,
                    'nickname' => $nickname,
                    'name' => $name,
                    'email' => $email,
                    'password' => $passwordByCustomMd5,
                    'phone' => $phone,
                    'tel' => $tel,

                    'businessName' => $businessName,

                    'contactPersonFirstname'=>$contactPersonFirstname,
                    'contactPersonLastname'=>$contactPersonLastname,
                    'contactPersonNickName'=>$contactPersonNickName,
                    'contactMobile'=>$contactMobile,

                    'cityId' => $cityId,
                    'role' => 101,
                    'groupid' => 1,
                    'createdDate' => time(),
                    'lastLoginIp' => ip(),
                    'lastLoginDate' => time(),
                    'loginCount' => 1
                   
                );

                $data['isBusinessReferalExist'] = 0;
                $data['referralId'] = 0;
                $data['businessRefPointPercent'] = 10;
                $data['customerRefPointPercent'] = 10;
                $data['trustLevel'] = 0;
                $data['visibleForBusiness'] = 0;
                $data['languageType'] = 'zh-en';
                $data['isSuspended'] = 0;
                $data['needReapprovedAfterEdit'] = 1;
                $data['isApproved'] = 1;

                if ($mdl_user->addUser($data)) $this->form_response(200,'保存成功',HTTP_ROOT_WWW.'factory/staff_sales');

            }

        } else {
            $this->setData($staff);
            $this->setData('销售员工管理', 'pagename');
            $this->setData('staff_sales', 'submenu');
            $this->setData('advanced_setting', 'menu');
            $this->setData('员工管理 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->display('factory/staff_sales_edit');
        }
    }


   public  function staff_sales_delete_action()
    {   
        $mdl_user = $this->loadModel('user');

        $id = (int)get2('id');

        if(!$id)$this->sheader(null, "员工ID缺失");


        $user = $mdl_user->get($id);

        if(!$user)$this->sheader(null, "没有找到该员工");

        
        if ($mdl_user->delete($id)) {
            if ($user['avatar']) {
                $this->file->deletefile(UPDATE_DIR . $user['avatar']);
            }
        }

        $this->sheader(HTTP_ROOT_WWW."factory/staff_sales");

    }

    public function customer_list_action() {
        $mdl_user_factory = $this->loadModel('user_factory');

        $search = trim(get2('search'));
		if($this->loginUser['role']==101) {
			 $factoryId =  $mdl_user_factory->getFactoryId($this->loginUser['id']);
			 $salesManId = $this->loginUser['id'];
		}else{
			 $factoryId =  $this->loginUser['id'];
			  $salesManId = 0;
		}
       //var_dump($salesManId );exit;

        $users = $mdl_user_factory->getUserFactoryList($factoryId, $search,$salesManId);
        foreach ($users as $key => $user) {
            $expiredAt =strtotime("+3 months", time());
            $link = self::customer_login_link($user['id'], $expiredAt);
            $users[$key]['login_link'] = $link;
        }

        $this->setData($search, 'search');
        $this->setData($users, 'users');
        $this->setData(date('d-m-Y', $expiredAt), 'expiredAt');
        $this->setData('customer_list', 'submenu_top');
        $this->setData('customer_list', 'submenu');
        $this->setData('customer_management', 'menu');
        $this->display('factory/customer_list');
    }

    public function login_as_customer_action() {
        $userId = trim(get2('user_id'));
        $mdl_user_factory = $this->loadModel('user_factory');
        $mdl_user = $this->loadModel( 'user' );
        if($mdl_user_factory->isUserApproved($userId, $this->loginUser['id'])){
            $user = $mdl_user->getUserById( $userId );
            $data = array(
                'lastLoginIP'	=> ip(),
                'lastLoginDate'	=> time(),
                'loginCount'	=> $user['loginCount'] + 1
            );

            $mdl_user->updateUserById( $data, $user['id'] );

            $this->session( 'member_user_id', $user['id'] );
            $this->session( 'member_user_shell', $this->md5( $user['id'].$user['name'].$user['password'] ) );

            $this->sheader(HTTP_ROOT_WWW . 'factorypage/' . $this->loginUser['id']);
        }
    }

    public function customer_login_link_action() {
        $userId = get2('user_id');
        $mdl_user_factory = $this->loadModel('user_factory');
        $user = $this->loadModel('user')->getUserById($userId);
        $this->setData($user, 'user');

        $approved = $mdl_user_factory->isUserApproved($userId, $this->loginUser['id']);
        $this->setData($approved, 'approved');
        if(!$approved) {
            $this->setData('该用户尚未审核，请先审核用户', 'message');
        }

        if(is_post()) {
            $expiredAt = trim(post('expired-at'));
            $link = self::customer_login_link($userId, $expiredAt);
            $this->setData($link, 'link');
        }

        $this->setData('customer_list', 'submenu_top');
        $this->setData('customer_list', 'submenu');
        $this->setData('customer_management', 'menu');
        $this->display('factory/customer_login_link');
    }

    public function customer_login_link($userId, $expired) {
        $mdl_user_factory = $this->loadModel('user_factory');
        $token = $mdl_user_factory->generateUserLoginToken($userId, $this->loginUser['id'], $expired);

        return HTTP_ROOT . "factorypage/user_link_login?user_id=$userId&factory_id=" . $this->loginUser['id'] . "&token=$token";
    }

    public function order_invoice_action(){
        $orderId = get2('order_id');
        $mel_user = $this->loadModel('user');
        $mdl_abn_application = $this->loadModel('wj_abn_application');
        $mdl_user_account_info = $this->loadModel('user_account_info');

        $order = $this->loadModel('order')->getByOrderId($orderId);
        $items = $this->loadModel('wj_customer_coupon')->getItemsInOrder_menu($orderId, $this->loginUser['id']);

        $user =$mel_user->getUserById($order['userId']);
        $userWhere = [
            'userId' => $order['userId'],
        ];
        $userABN = $mdl_abn_application->getByWhere($userWhere);
//var_dump($order['userId']);exit;

        $factory = $mel_user->getUserById($this->loginUser['id']);
        $factoryWhere = [
            'userId' => $this->loginUser['id'],
        ];
        $factoryAccount = $mdl_user_account_info->getByWhere($factoryWhere);
        $factoryABN = $mdl_abn_application->getByWhere($factoryWhere);
		
		// 获得该用户的简称
         $mdl_user_factory =$this->loadModel("user_factory");
		 $user_code_rec =$mdl_user_factory->getByWhere(array('user_id'=>$order['userId'],'factory_id'=>$this->loginUser['id']));
		 //var_dump($user_code_rec);exit;
		

        $this->loadModel('factory_invoice');
        $report = new OrderInvoice($order, $items);
		  if($this->loginUser['logo']) {
                $report->logoPath('data/upload/' . $this->loginUser['logo']);
            }
			
		
        $report->setUser_Code($user_code_rec);
		$report->setUser($user, $userABN);
        $report->setFactory($factory, $factoryABN, $factoryAccount);
        $report->generatePDF();
        $report->outPutToBrowser(  'Invoice-' . $order['orderId'] . '.pdf');
    }
}