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

        $customer_delivery_date = trim(get2('customer_delivery_date'));

        $this->setData($customer_delivery_date,'customer_delivery_date');

        $three_days_times = time()-259200*2;

        $sql_avaliable_date =" SELECT DISTINCT o.logistic_delivery_date from (select * from cc_order where (`business_userId` = ".
            $this->current_business['id'].") or (`business_userId` in (select DISTINCT business_id from cc_freshfood_disp_centre_suppliers where suppliers_id =".$this->current_business['id'].")) ) as o where o.logistic_delivery_date >".$three_days_times." order by logistic_delivery_date ";
        // var_dump($sql_avaliable_date);exit;

        $availableDates = $this->loadModel('freshfood_logistic_customers')->getAvaliableDateOfThisLogisiticCompany($this->current_business['id']);

        // $availableDates = $this->loadModel('order')->getListBySql( $sql_avaliable_date);
        $availableDates = array_map(function($d){
            return date('Y-m-d',$d['logistic_delivery_date']);
        }, $availableDates);
        $this->setData($availableDates, 'availableDates');












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
            $sql = "SELECT f.to_xero, if(f.account_type='COD',0,CAST(f.account_type AS SIGNED)*7 ) as payment_period,
            if(f.account_type='COD','COD',concat(convert(CAST(f.account_type AS SIGNED)*7 ,CHAR),'D')) as disp_accountType ,cust.displayName,cust.displayName as nickname,cust.name,o.* ,cust.ori_sum
                    from cc_order as o 
                    left join (select order_id,business_id,sum(voucher_deal_amount*customer_buying_quantity) as ori_sum ,uu.nickname as displayName ,user.name  from cc_wj_customer_coupon tt left join  cc_user_factory uu  on tt.userId =uu.user_id  left join cc_user user on  tt.userId = user.id    group by order_id,business_id) cust 
                        on o.orderId=cust.order_id and cust.business_id =".$currentBusinessId." 
                    left join cc_wj_user_coupon_activity_log as l on o.orderId=l.orderId and o.coupon_status=l.action_id 
                     left join cc_user_factory f   on o.userId =f.user_id and o.business_userId =f.factory_id ";
        } else {
            $sql = "SELECT  f.to_xero, if(f.account_type='COD',0,CAST(f.account_type AS SIGNED)*7 ) as payment_period,
            if(f.account_type='COD','COD',concat(convert(CAST(f.account_type AS SIGNED)*7 ,CHAR),'D')) as disp_accountType ,cust.displayName,cust.displayName as nickname,cust.name,o.* ,cust.ori_sum 
            from cc_order as o 
                left join (select order_id,business_id,sum(voucher_deal_amount*customer_buying_quantity) as ori_sum ,uu.nickname as displayName,user.name  from cc_wj_customer_coupon tt left join  cc_user_factory uu  on tt.userId =uu.user_id  left join cc_user user on  tt.userId = user.id     group by order_id,business_id) cust 
                    on o.orderId=cust.order_id and cust.business_id =".$FactoryId." 
                    left join cc_wj_user_coupon_activity_log as l on o.orderId=l.orderId and o.coupon_status=l.action_id  
                    left join cc_user_factory f   on o.userId =f.user_id and o.business_userId =f.factory_id ";
        }
        $whereStr = " ( business_userId= ".$FactoryId;
        $whereStr .= "  or  o.orderId in (select DISTINCT c.order_id from cc_wj_customer_coupon c where business_id = ".$FactoryId.")";
        //plus 如果该用户是统配中心用户，其下所有商家的订单
  /*      $whereStr .= " or  business_userId in (select business_id from  cc_dispatching_centre_customer_list where dispatching_centre_id =".$FactoryId.")";
        //如果该商家是集合店铺所有人，则所有其下店铺的订单
        $whereStr .= " or  business_userId in (select suppliers_id from  cc_freshfood_disp_centre_suppliers where business_id =".$FactoryId.")";
        // 如果该用户为授权用户，则其下所有订单均可以看到。
        $whereStr .= " or  business_userId in (select customer_id from  cc_authrise_manage_other_business_account where authorise_business_id =".$FactoryId.")";
*/
        $whereStr .= ")";

        if (! empty($sk)) {
            $whereStr .= " and ( o.last_name like  '%".$sk."%'";
            $whereStr .= " or o.phone like  '%".$sk."%'";
            $whereStr .= " or o.orderId like  '%".$sk."%'";
            $whereStr .= " or o.order_name like  '%".$sk."%'";
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

        if (!empty($customer_delivery_date)) {
            if ($customer_delivery_date != 'all') {
                $whereStr.= " and  DATE_FORMAT(from_unixtime(o.logistic_delivery_date),'%Y-%m-%d') = '$customer_delivery_date' ";
            }else{



            }
        }else {

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
		
		if($this->loginUser['role']==20){
            //如果该用户含有销售员角色，则过滤
            $rec =$this->loadModel('staff_roles')->getByWhere(array('staff_id'=>$this->loginUser['id']));
          //  var_dump($this->loginUser['id']); var_dump($rec);exit;
            if(substr_count($rec[roles],',5,')>0 || substr_count($rec[roles],',6,')>0 ) {
                $whereStr .= " and o.userId in (select user_id from cc_user_factory where factory_sales_id =".$this->loginUser['id'].")";
            }

		}
		
		
		

        $pageSql = $sql." where ".$whereStr." order by createTime desc";
        //var_dump($pageSql);exit;
        if (trim(get2('output')) == 'pdf') {

            $where12 = [
                'userId' => $FactoryId,

            ];
            $user_abn = $this->loadModel('wj_abn_application')->getByWhere($where12);

            $data = $mdl_order->getListBySql($pageSql);

            foreach ($data as $key => $value) {

                $data[$key]['items'] = $mdl_wj_customer_coupon->getItemsInOrder($value['orderId'], $FactoryId);
                $data[$key]['name'] =$this->getCustomerName($value);
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
                $data[$key]['name'] =$this->getCustomerName($value);
            }

            $this->loadModel('invoice');
            $report = new shippingLabel();
            $report->setStarttime(date('Y-m-d H:i:s', $st))->setEndtime(date('Y-m-d H:i:s', $et))->title("Shipping Label")->setReturnAddress($this->current_business['googleMap'])->fitInPage($fitInPage)->OrderData($data);
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
                 $data[$key]['name'] =$this->getCustomerName($value);

               
            }
        }

        $this->setData($page['pageStr'], 'pager');

        $this->setData($data, 'data');

        $this->setData('online_center', 'menu');
        $this->setData('customer_coupon_process', 'submenu');

        $this->setData(HTTP_ROOT_WWW.'factory/customer_orders', 'searchUrl');

        $this->setData($this->parseUrl(), 'currentUrl');

        $this->setData('Customer Orders - '.$this->site['pageTitle'], 'pageTitle');
		
		//date_default_timezone_set(Australia/Sydney);
		//$this->setData(date_default_timezone_get(), 'currentTimeZone'); 
		//$this->setData(date('H:i:s'), 'currentTime'); 
		 
		 

        $this->display_pc_mobile('factory/customer_orders', 'factory/customer_orders');
        return true;
    }


// 同步 xero的产品数据，将本地的产品编号同步到xero系统中
    public function update_xero_server_code_action(){

      //  $this->form_response_msg('no access')
        $id = (int)post('id');

        $returnArr =[];

        $mdl= $this->loadModel('xero_items_match');

        $xero_update_info = $mdl->get($id);

        if ($id < 0 || $xero_update_info['business_id']!=$this->current_business['id'] ) $this->form_response_msg('no access');

        //检查该商家是否可以管理其它店铺，如果授权即可以该商家权限进入系统。

        // require_once DOC_DIR.'core/b2b_2_0/b2b/lib/Credentials_ubonus100mtest_latest.php';
        // require_once DOC_DIR.'core/b2b_2_0/b2b/lib/Credentials.php';
        require_once DOC_DIR.'core/b2b_2_0/b2b/lib/Database.php';
        require_once DOC_DIR.'core/b2b_2_0/b2b/lib/MyApi001.php';

        $api = new MyApi($db);
        $mdl_xero =$this->loadModel('xero') ;
        $mdl_tokens =$this->loadModel('tokens') ;
        $credentials =$mdl_tokens->getCredentials($this->current_business['id'],'xero') ;

        if(!$credentials) {
            echo json_encode(array('error' => 'please refresh the page or login in again!'));
        }



          //    $arr = [
      //      "ItemID" => "40e4b881-cc7e-45bb-8c58-dd19ff4f5488", // from previous create items
      //      "Code" => "387593", // required
      //      "Name" => "Product 103-a"
     //   ];
          if($xero_update_info['guige_id']){
              $code =$xero_update_info['product_id'].'-'.$xero_update_info['guige_id'];
          }else{
              $code =$xero_update_info['product_id'];
          }
            $arr = [
              "ItemID" => $xero_update_info['xero_ItemID'], // from previous create items
              "Code" => $code
          ];
            $arr_json =json_encode($arr);

       //    var_dump($arr_json);exit;
        $response_arr = $api->updateItem($credentials,$arr_json);

     //  var_dump($response_arr);exit;
        if(!$response_arr) {
            echo json_encode(array('error' => 'no result return when update  item code ! '));
        }

        $custom_response= $mdl_xero->updateXeroItemCode1($response_arr);

        //有表示有错误
        if($custom_response){

            if(is_array($response_arr) && count($response_arr) > 0)
            {
                $updateArr =array(
                    'new_xero_ItemCode'=>$response_arr[0]['ItemID'],
                    'return_xero_code'=>$response_arr[0]['Code'],
                );

                if($mdl->update($updateArr,$id)){
                    //var_dump('success');exit;
                    $returnArr['return_xero_code'] = $response_arr[0]['Code'];
                    $returnArr['new_xero_ItemCode'] = $response_arr[0]['ItemID'];

                }
            }




        }


        if($custom_response) {
            $returnArr['updated'] = 1;
        }else{
            $returnArr['updated'] =0;
       }


        echo json_encode($returnArr);

    }

// send the order to xero


    public function cancel_xero_server_code_action(){

        //  $this->form_response_msg('no access')
        $id = (int)post('id');

        $returnArr =[];

        $mdl= $this->loadModel('xero_items_match');

        $xero_update_info = $mdl->get($id);

        if ($id < 0 || $xero_update_info['business_id']!=$this->current_business['id'] ) $this->form_response_msg('no access');

        //检查该商家是否可以管理其它店铺，如果授权即可以该商家权限进入系统。

        // require_once DOC_DIR.'core/b2b_2_0/b2b/lib/Credentials_ubonus100mtest_latest.php';
        // require_once DOC_DIR.'core/b2b_2_0/b2b/lib/Credentials.php';
        require_once DOC_DIR.'core/b2b_2_0/b2b/lib/Database.php';
        require_once DOC_DIR.'core/b2b_2_0/b2b/lib/MyApi001.php';

        $api = new MyApi($db);
        $mdl_xero =$this->loadModel('xero') ;
        $mdl_tokens =$this->loadModel('tokens') ;
        $credentials =$mdl_tokens->getCredentials($this->current_business['id'],'xero') ;

        if(!$credentials) {
            echo json_encode(array('error' => 'please refresh the page or login in again!'));
        }



        //    $arr = [
        //      "ItemID" => "40e4b881-cc7e-45bb-8c58-dd19ff4f5488", // from previous create items
        //      "Code" => "387593", // required
        //      "Name" => "Product 103-a"
        //   ];

        $arr = [
            "ItemID" => $xero_update_info['xero_ItemID'], // from previous create items
            "Code" => $xero_update_info['xero_code']
        ];
        $arr_json =json_encode($arr);

        //    var_dump($arr_json);exit;
        $response_arr = $api->updateItem($credentials,$arr_json);

        //  var_dump($response_arr);exit;
        if(!$response_arr) {
            echo json_encode(array('error' => 'no result return when update  item code ! '));
        }


            if(is_array($response_arr) && count($response_arr) > 0)
            {

                if( !empty($response_arr[0]['ItemID']) && !empty($response_arr[0]['Code']) && empty($response_arr[0]['ValidationErrors']) ) {

                    $updateArr =array(
                        'new_xero_ItemCode'=>'',
                        'return_xero_code'=>'',
                        'product_id'=>0,
                        'guige_id'=>0
             );

                    if($mdl->update($updateArr,$id)){
                        //var_dump('success');exit;
                        $returnArr['return_xero_code'] = '-';
                        $returnArr['new_xero_ItemCode'] = '-';
                        $returnArr['product_id'] = '-';
                        $returnArr['guige_id'] = '-';

                    }

                    $updateArr =array(
                        'xero_itemcode'=>''
                    );
                    if($xero_update_info['guige_id']) {
                         $this->loadModel('restaurant_menu_option')->update($updateArr,$xero_update_info['guige_id']);
                    }else{
                        $this->loadModel('restaurant_menu')->update($updateArr,$xero_update_info['product_id']);
                    }

                }


                $returnArr['updated'] = 1;

            }else{
                $returnArr['updated'] =0;
            }



        echo json_encode($returnArr);

    }

// send the order to xero

    public function xero_send_invoice_action(){





        $id = (int)get2('id');
        $createOrUpdate =trim(get2('createOrUpdate'));



        $mdl= $this->loadModel('order');

        $order_info = $mdl->get($id);

        if ($id < 0 || $order_info['business_userId']!=$this->current_business['id'] ) $this->form_response_msg('no access');

        //检查该商家是否可以管理其它店铺，如果授权即可以该商家权限进入系统。

       // require_once DOC_DIR.'core/b2b_2_0/b2b/lib/Credentials_ubonus100mtest_latest.php';
       // require_once DOC_DIR.'core/b2b_2_0/b2b/lib/Credentials.php';
        require_once DOC_DIR.'core/b2b_2_0/b2b/lib/Database.php';
        require_once DOC_DIR.'core/b2b_2_0/b2b/lib/MyApi001.php';

        $api = new MyApi($db);
        $mdl_xero =$this->loadModel('xero') ;
        $mdl_tokens =$this->loadModel('tokens') ;
        $credentials =$mdl_tokens->getCredentials($this->current_business['id'],'xero') ;

        if(!$credentials) {
            echo json_encode(array('error' => 'please refresh the page or login in again!'));
        }

        $orderId =$order_info['orderId'];
        $order_data = $mdl_xero->getOrderInvoiceData($orderId,$createOrUpdate);
        //var_dump($order_data);exit;
        if(!$order_data) {
            echo json_encode(array('error' => 'could not find the order Info!'));
        }

        if($createOrUpdate=='update'){
            $response_arr = $api->updateInvoice($credentials,$order_data);
        }else{
            $response_arr = $api->createInvoices($credentials,$order_data);
        }

        if(!$response_arr) {
            echo json_encode(array('error' => 'no result return when create invoice! '));
        }
        if($createOrUpdate !='update') {
            $custom_response = $mdl_xero->createXeroInvoiceInfo($response_arr, $orderId);
        }
        if($custom_response) {
            echo json_encode(array('error' => (string)$custom_response));
        }else{
            $data = array();
            if($createOrUpdate!='update'){
                $data['sent_to_xero'] = ($order_info['sent_to_xero'] == '0') ? '1' : '0';

                if ($mdl->update($data, $order_info['id'])) {
                    echo json_encode(array('sent_to_xero' => $data['sent_to_xero']));
                } else {
                    $this->form_response_msg('Please try again later');
                }
            }else{
                echo json_encode(array('sent_to_xero' => 1));
            }


        }




    }

    public function set_trading_hours_action(){
        //判断是否可以设置该用户的营业时间,该段程序稍后写。
        //  var_dump('here');exit;
        $customer_id =get2('user_id');


         //操作权限检查
        if(!$this->checkifLoginUserCanOperatedUserId($this->loginUser,$customer_id)){
			
			var_dump((string)$this->lang->no_access);
			exit;
		}


        $user =  $this->loadModel('user')->get($customer_id);
        // var_dump($user);exit;
        $this->setData($user['trading_hours'],'trading_hours');
        $this->setData($user['trading_hours_desc'],'trading_hours_desc');
        $this->setData($customer_id,'customer_id');

        //var_dump($this->loginUser['trading_hours']);exit;

        $this->setData('customer_list', 'submenu');
        $this->setData('customer_management', 'menu');

        $this->display_pc_mobile('factory/tradighours_mobile', 'factory/tradighours_mobile');
   }


	public function  business_hour_setting_action() {
	  
	  //判断是否可以设置该用户的营业时间,该段程序稍后写。
	//  var_dump('here');exit;
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
	   $this->display_pc_mobile('factory/tradighours_mobile', 'factory/tradighours_mobile');
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

		  if($this->loginUser['role']==20) {
			  
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
        $items = $mdl_wj_customer_coupon->getOrderItems($orderId);

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


            $this->display('factory/customer_order_detail_full_control');

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
        $quantity = post('new_customer_buying_quantity');
        if (isset($quantity)) {
            $data['new_customer_buying_quantity'] = $quantity;
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

				  if($this->loginUser['role']==20) {
					  
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
            'new_customer_buying_quantity' => $customerCoupon['new_customer_buying_quantity'],
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
           if($value['id']==$id){
               $newCustomerCouponPrice = $quantity * $amount;

           }else{
               $newCustomerCouponPrice = $customerCouponData['new_customer_buying_quantity'] * $customerCouponData['voucher_deal_amount'];

           }

            $orderPriceChange += $newCustomerCouponPrice - $customerCouponData['adjust_subtotal_amount'];
            $customerCouponData['adjust_subtotal_amount'] = round($newCustomerCouponPrice, 2);
            $mdl_wj_customer_coupon->update($customerCouponData, $id);

            if ($key == 'voucher_deal_amount') {
          //      $mdl_user_factory_menu_price = $this->loadModel('user_factory_menu_price');
            //    $mdl_user_factory_menu_price->insertOrUpdateUserFactoryPrice($customerCoupon['userId'], $customerCoupon['restaurant_menu_id'], $customerCouponData['voucher_deal_amount']);
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
                $person_first_name = trim(post('person_first_name'));
                $person_last_name = trim(post('person_last_name'));

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
                    $this->setData($person_last_name, 'person_last_name');
                    $this->setData($person_first_name, 'person_first_name');

                } else {
                    $result = self::add_new_customer($username, $mobile, $address,$nickname,$person_last_name,$person_first_name);

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
				
				$contactPersonFirstname = trim(post('person_first_name'));
				
				$contactPersonLastname = trim(post('person_last_name'));
				
				$email = trim(post('email'));
				$username = trim(post('username'));

                $customer_type = trim(post('customer_type'));
				
				
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
                   'address'=>$googleMap,
				   'email'=>$email,
				   'contactPersonFirstname'=>$contactPersonFirstname,
				   'contactPersonLastname'=>$contactPersonLastname,
                   'person_first_name'=>$contactPersonFirstname,
                   'person_last_name'=>$contactPersonLastname,
				   'tel'=>$tel,
				   'phone'=>$phone,
				   'name'=>$username,
                   'displayName'=>$username,
                   'businessName'=>$untity_name
				   
				   );
				   $mdl_user = $this->loadModel('user');
				   $mdl_user->update($data_user,$userId);
				   
				   
				    $data_user_factory=array(
				   //''=>$,
				   'approved'=>$approved,
				   'factory_sales_id'=>$factory_sales_id,
				   'nickname'=>$factory_code,
                        'customer_type'=>$customer_type

				   
				   
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
					'ABNorACN'=>$abn,
                    'business_name'=>$username
				   
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



			$customer_type_list  = $this->loadModel('customer_type')->getList(null, array('business_id' => $FactoryId ));
            $this->setData($customer_type_list,'customer_type_list');
			// get sales list ...
			//如果该用户本身为销售员，则前端不显示该信息，后端也不读取，也不处理。
			// 如果用户为owner 则 获得 user_belong_to_user =该用户，且用户role=101
			
			if($this->loginUser['role']==20) {
				
				
			}else {
				$where =array (
				 'user_belong_to_user'=>$this->loginUser['id'],
				 'role'=>20
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


 public function customer_info_edit_action()
    {
        $userId = trim(get2('id'));
        $this->setData($userId,'userId');



        if (is_post()) {
            //var_dump('here');exit;
            $userId = trim(post('userId'));
            //var_dump('userid is'.$userId);exit;
            $forward_page =HTTP_ROOT_WWW."factory/customer_info?id=".$userId;
                if($userId)  {
                //var_dump($userId);exit;


                //abn info
                $abn = str_replace(' ', '', trim(post('abn')));
                if(strlen($abn)>11) {
                    $abn=substr($abn,0,11);
                }
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





                    $data_user=array(
                        //''=>$,

                        'googleMap'=>$googleMap,
                        'address'=>$googleMap,
                        'email'=>$email,
                        'contactPersonFirstname'=>$contactPersonFirstname,
                        'contactPersonLastname'=>$contactPersonLastname,
                        'person_first_name'=>$contactPersonFirstname,
                        'person_last_name'=>$contactPersonLastname,
                        'displayName'=>$username,
                        'businessName'=>$untity_name,
                        'tel'=>$tel,
                        'phone'=>$phone,

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
                        'business_name'=>$username,
                        'ABNorACN'=>$abn

                    );
                    $mdl_wj_abn_applicationy = $this->loadModel('wj_abn_application');
                    $where =array(
                        'userId'=>$userId
                    );
                    $mdl_wj_abn_applicationy->updateByWhere($data_abn,$where);

                  //  header("Location: ".  HTTP_ROOT_WWW."factory/customer_list");
                    $this->form_response(200, (string)$this->lang->update_success,$forward_page);
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

            }else{

                $this->form_response(201, 'no user info!');
                return;
            }
        }else{
            //get all customer information




            // 获得当前操作用户代表的 供应商用户ID
            $mdl_user_factory = $this->loadModel('user_factory');
            $FactoryId = $mdl_user_factory->getBusinessId($this->loginUser['id'],$this->loginUser['role']);

           // var_dump($userId);exit;
            if($mdl_user_factory->isUserAuthorisedToOperate($userId, $FactoryId)){
                //如果当前用户对该客户操作合法，则 获取当前用户是否可以对客户有 aacount的审核权限
                if( $this->getIfCurrentUserCanDOApprovedCustomer($this->loginUser))  {
                  $this->setData(1,'$isAdulted');

                }else{
                    $this->setData(0,'$isAdulted');

                }
              // var_dump('dddd'.$this->loginUser['$isAdulted']);exit;



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

            if($this->loginUser['role']==20) {


            }else {
                $where =array (
                    'user_belong_to_user'=>$this->loginUser['id'],
                    'role'=>20
                );
                $sales_list = $this->loadModel('user')->getList(null,$where);
                //var_dump($sales_list);exit;
                $this->setData($sales_list,'sales_list');

            }

          // 获得当前用户是否拥有审批客户的权限





            $this->setData($user,'user');
            $this->setData($user_factory_info,'user_factory_info');
            $this->setData($user_abn,'user_abn');
            //exit;






        }
        $this->setData('customer_list', 'submenu_top');
        $this->setData('customer_list', 'submenu');
        $this->setData('customer_management', 'menu');
        $this->display('factory/customerManagement/customer_info_edit');


    }

    public  function upload_image_action(){



        $userid =post('userid');


       $userid =$this->loginUser['id'];

        header("content-type:text/html;charset=utf-8");
        $base64_img =post('imgbase64');
        $up_dir1 = './data/upload/';
        $up_dir2 =date('y-m').'/';//存放在当前目录的upload文件夹下
        $up_dir =$up_dir1.$up_dir2 ;
        $up_dir2_cut ='thumbnails/'.date('y-m').'/';//存放在当前目录的upload文件夹下
        if(!file_exists($up_dir)){
            $file = new file;

            $file->createdir( $up_dir, 0777 );
          //  mkdir($up_dir,0777);
        }

        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_img, $result)){
            $type = $result[2];
            if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){
                $new_file = $up_dir.$userid.'.'.$type;
                $filename= $up_dir2.$userid.'.'.$type;

                $filename_cut = $up_dir2_cut.$userid.'_100x100_cut.'.$type;

                if(file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_img)))){
                    $img_path = str_replace('../../..', '', $new_file);
                    $this->loadModel('user')->saveAvatar($this->loginUser['id'],$filename);
                    $this->cut_image($filename,100,100,'fill',false,true);
                    $str = '图片上传成功</br>![](' .$img_path.' '.$new_file. ')';
                    $this->loginUser['avatar'] =$filename;
                }else{
                    $str =  '图片上传失败</br>';

                }
            }else{
                //文件类型错误
                $str =  '图片上传类型错误';
            }

        }else{
            //文件错误
            $str =  '文件错误';
        }





        echo json_encode($str.'  '.$filename);



    }
   //审核当前用户是否对指定的客户拥有审批权限 ;
  public function getIfCurrentUserCanDOApprovedCustomer($user) {
       $isAdulted =0;
      //如果为企业owner
      if($user['role']==3){
          $isAdulted =1;
      }
      $userid =$user['id'];
      $staff_roles_rec =$this->loadModel('staff_roles')->getByWhere(array('staff_id'=>$userid)) ;
      $staff_roles = $staff_roles_rec['roles'];

      if( substr_count($staff_roles,',4,') || substr_count($staff_roles,',5,') ||
          substr_count($staff_roles,',0,') ||  substr_count($staff_roles,',1,') ) {
          $isAdulted =1;
      }


     return $isAdulted;
}

    public function delete_new_group_member_action()
    {


        $manager_id =get2('groupId');
        $userId =get2('userId');


        $mdl_user_factory =$this->loadModel('user_factory');
        $factoryId =$mdl_user_factory->getBusinessId($this->loginUser['id'],$this->loginUser['role']);
        $groupManagerInfo  =$this->loadModel('user_group_manager')->getGroupMangerInfo($manager_id,$factoryId);

        $this->setData($groupManagerInfo[0],'groupManagerInfo');


                $where =array(
                    'manager_id'=>$manager_id,
                    'factory_id'=>$factoryId,
                    'user_id'=>$userId
                );
               // var_dump($where);exit;
                $mdl_user_group =$this->loadModel('user_group');
                if($mdl_user_group->deleteByWhere($where)) {

                 //   $this->form_response(200,'the member has been deleted ! ', HTTP_ROOT_WWW."factory/add_new_group_member?groupId=".$manager_id);
                    $this->sheader(HTTP_ROOT_WWW."factory/add_new_group_member?groupId=".$manager_id);
                }else{

                    $this->form_response_msg('something wrong when delete...,please contact admin ');

                }






        //获取当前组成员列表，并且可以删除
        $users =$this->loadModel('user_group')->getListOfGroupUser($manager_id);
        foreach ($users as $key =>$value) {
            $users[$key]['dispname'] =$this->getCustomerName($value);

        }
        $this->setData($users, 'users');
        $this->setData('customer_management', 'menu');

        $this->setData('group_order_setting', 'submenu');
        $this->display('factory/add_new_group_member');

        return;
    }

    public function add_new_group_member_action()
    {


        $manager_id =get2('groupId');
        if(!$manager_id) {
            $manager_id =trim(post('manager_id'));
        }

        $mdl_user_factory =$this->loadModel('user_factory');
        $factoryId =$mdl_user_factory->getBusinessId($this->loginUser['id'],$this->loginUser['role']);


        $groupManagerInfo  =$this->loadModel('user_group_manager')->getGroupMangerInfo($manager_id,$factoryId);

        $this->setData($groupManagerInfo[0],'groupManagerInfo');

        if (is_post()) {
            $userId = trim(post('userId'));
            $code =trim(post('code'));
            $manager_id =trim(post('manager_id'));
            $isExistMember = $mdl_user_factory ->checkIfExistMember($factoryId,$userId,$code);
            if(!$isExistMember) {
                $this->form_response_msg('the member info was incorrect!  can not join in the group!');
            }else{
               $data =array(
                   'manager_id'=>$manager_id,
                   'factory_id'=>$factoryId,
                   'user_id'=>$userId
              );
               $mdl_user_group =$this->loadModel('user_group');
               if($mdl_user_group->getCount($data)) {
                   $this->form_response_msg('the member exists already !');
               }else{
                   if($mdl_user_group->insert($data) ){
                       $this->form_response(200,'add member successfully! ', HTTP_ROOT_WWW."factory/add_new_group_member?groupId=".$manager_id);

                   }else{
                       $this->form_response_msg('something wrong when add the member ,please contact admin ');

                   }
               }

            }



        }

        //获取当前组成员列表，并且可以删除
        $users =$this->loadModel('user_group')->getListOfGroupUser($manager_id);
        foreach ($users as $key =>$value) {
            $users[$key]['dispname'] =$this->getCustomerName($value);

        }
        $this->setData($users, 'users');
        $this->setData('customer_management', 'menu');

        $this->setData('group_order_setting', 'submenu');
        $this->display('factory/add_new_group_member');

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
                $person_last_name = trim(post('person_last_name'));
                $person_first_name = trim(post('person_first_name'));

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
                    $this->setData($person_last_name, 'person_last_name');
                    $this->setData($person_first_name, 'person_first_name');
                } else {
                    $result = self::add_new_customer($username, $mobile, $address,$nickname,$person_last_name,$person_first_name);

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

    public function add_new_customer($username, $mobile = '', $address = [],$nickname,$person_last_name,$person_first_name)
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

        $userObject->setBusinessName($username);
        $userObject->setLegalName($nickname);


        $userObject->setBusinessMobile($mobile, true);
        $userObject->setAddress($address['address']);
        $userObject->setFullName($person_last_name,$person_first_name);

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
			//如果该用户的 role =20 ，表示该用户为职员，其不是工厂商家id ,需要找到工厂商家ID，并且要插入一个字段值告知系统，是那个销售员创建的商家。
			if($this->loginUser['role']==20) {
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

    public function update_business_discount_rate_action()
    {

        $business_discount_rate =post('business_discount_rate');
        $grade_id =post('grade_id');

        if(!is_numeric($business_discount_rate) && is_null($grade_id)){
            $this->form_response(600, 'please input number! for example 5.5', 'no access');
        }

        if (is_post()) {


            $mdl_user_factory = $this->loadModel("user_factory");

            $id = post('id');
            $data_field  = post('update_field');
            // 判断如果当前登陆用户和当前操作的记录不是所属关系拒绝操作。



            $FactoryId = $mdl_user_factory->getBusinessId($this->loginUser['id'], $this->loginUser['role']);

            $factory_user=$mdl_user_factory->get($id);



            if ( !$mdl_user_factory->isUserAuthorisedToOperate($factory_user['user_id'], $FactoryId)) {
                $this->form_response(600, (string)$factory_user['factory_id'].'no access'.(string)$FactoryId, 'no access');

            }


            $data = array();

            if($data_field  =='business_discount_rate') {
                $data['business_discount_rate']  = post('business_discount_rate');
            }

            if($data_field  =='grade_id') {
                $data['grade']  =$grade_id;
            }


           // if ($business_discount_rate)= $business_discount_rate;
            $account_type = post('account_type');
            if ($account_type) $data['account_type'] = $account_type;

            try {
                $mdl_user_factory->update($data, $id);


                $this->form_response(200, '', '');
            } catch (Exception $e) {
                $this->form_response(500, $e->getMessage(), '');
            }

        } else {
            //wrong protocol
        }
    }

    public function update_business_payment_terms_action()
    {


        if (is_post()) {


            $mdl_user_factory = $this->loadModel("user_factory");

            $id = post('id');

            // 判断如果当前登陆用户和当前操作的记录不是所属关系拒绝操作。


            $factory_user = $mdl_user_factory->get($id);
            $FactoryId = $mdl_user_factory->getBusinessId($this->loginUser['id'], $this->loginUser['role']);

            if ($factory_user['factory_id'] != $FactoryId) {
                $this->form_response(600, 'no access', 'no access');

            }


            $data = array();


            // if ($business_discount_rate)= $business_discount_rate;
            $account_type = post('account_type');
            if ($account_type) $data['account_type'] = $account_type;

            try {
                $mdl_user_factory->update($data, $id);


                $this->form_response(200, '', '');
            } catch (Exception $e) {
                $this->form_response(500, $e->getMessage(), '');
            }

        } else {
            //wrong protocol
        }
    }

    public function update_xero_account_id_action()
    {


        if (is_post()) {


            $mdl_user_factory = $this->loadModel("user_factory");

            $id = post('id');

            // 判断如果当前登陆用户和当前操作的记录不是所属关系拒绝操作。


            $factory_user = $mdl_user_factory->get($id);


            if ($factory_user['factory_id'] != $this->current_business['id']) {
                $this->form_response(600, 'no access', 'no access');

            }

            if (strlen($factory_user['xero_contact_id'])>2 ) {
                $this->form_response(600, 'xero synced ,could not be changed ');

            }


            $data = array();
            $filedName = post('update_field_name');
            $value =trim(post('value'));
            $data[$filedName]  = $value;
         //   $this->form_response(600, $filedName.' '.$value);
            /*   $data['delivery_tue']  = post('delivery_Tue');
               $data['delivery_wed']  = post('delivery_Wed');
               $data['delivery_thur']  = post('delivery_Thur');
               $data['delivery_fri']  = post('delivery_Fri');
               $data['delivery_sat']  = post('delivery_Sat');
               $data['delivery_sun']  = post('delivery_Sun');
           */

            // if ($business_discount_rate)= $business_discount_rate;


            try {
                $mdl_user_factory->update($data, $id);


                $this->form_response(200, '', '');
            } catch (Exception $e) {
                $this->form_response(500, $e->getMessage(), '');
            }

        } else {
            //wrong protocol
        }
    }


    public function update_deliver_avaliable_date_action()
    {


        if (is_post()) {


            $mdl_user_factory = $this->loadModel("user_factory");

            $id = post('id');

            // 判断如果当前登陆用户和当前操作的记录不是所属关系拒绝操作。


            $factory_user = $mdl_user_factory->get($id);
            $FactoryId = $mdl_user_factory->getBusinessId($this->loginUser['id'], $this->loginUser['role']);

            if ($factory_user['factory_id'] != $FactoryId) {
                $this->form_response(600, 'no access', 'no access');

            }


            $data = array();
            $filedName = post('update_field_name');
            $value =post('value');
            $data[$filedName]  = $value;
         /*   $data['delivery_tue']  = post('delivery_Tue');
            $data['delivery_wed']  = post('delivery_Wed');
            $data['delivery_thur']  = post('delivery_Thur');
            $data['delivery_fri']  = post('delivery_Fri');
            $data['delivery_sat']  = post('delivery_Sat');
            $data['delivery_sun']  = post('delivery_Sun');
        */

            // if ($business_discount_rate)= $business_discount_rate;


            try {
                $mdl_user_factory->update($data, $id);


                $this->form_response(200, '', '');
            } catch (Exception $e) {
                $this->form_response(500, $e->getMessage(), '');
            }

        } else {
            //wrong protocol
        }
    }

    public function adjust_customer_payments_action(){

        if(is_post()){

            $mdl_user_factory =$this->loadModel("user_factory");

            $id = post('id');
          //  $id = 239;
            // $this->form_response(600,$id);
            // 判断如果当前登陆用户和当前操作的记录不是所属关系拒绝操作。


            $factory_user = $mdl_user_factory->get($id);
            $FactoryId =$mdl_user_factory->getBusinessId($this->loginUser['id'],$this->loginUser['role']);

            if ($factory_user['factory_id'] != $FactoryId) {
                $this->form_response(600,'no access','no access');

            }


            $data=array();

            $payment_amount = post('adjust_payment_amount');
           // $payment_amount =450;
            // 校对当日同笔付款是否已执行 ，如果付过 则 不允许再付款。
            // 插入付款数据
            if($payment_amount<=0) {
                $this->form_response(500, 'amount  must larger than 0!','');
            }


            $mdl_statement= $this->loadModel('statement');

            $mdl_statement->begin();


            //插入客户支付数据
            $customer_payment_data = $mdl_statement->getAdjustCustomerPaymentData($this->loginUser['id'],$factory_user,$payment_amount);
            $new_id= $mdl_statement->insert($customer_payment_data);

            if(!$new_id){
                $error=1;
            }

            // 分析该笔支付 是支付那个账单， 顺序依次 是 overdue账单 （按overdue日期递增走），not yet due , 如果还有剩余，则做一个credit . 如果，所剩余额不够下笔账单
            // 付款，则 将余额转成credit .
            //   if(! $isProcessed) $error=1;

            $mdl_statement->updatePaymentsDetails($new_id,$payment_amount,$factory_user,$this->loginUser['id']);

            if($error) {
                $mdl_statement->rollback();
                $this->form_response(500, 'error!','');
            }else{
                $mdl_statement->commit();
                $this->form_response(200,'','');
            }



            //   $this->form_response(600,$payment_amount);

            try {

            } catch (Exception $e) {
            }

        }else{
            //wrong protocol
        }
    }


    public function add_customer_payments_action(){

        if(is_post()){

            $mdl_user_factory =$this->loadModel("user_factory");

            $id = post('id');
          //  $id = 239;
           // $this->form_response(600,$id);
            // 判断如果当前登陆用户和当前操作的记录不是所属关系拒绝操作。


            $factory_user = $mdl_user_factory->get($id);
            $FactoryId =$mdl_user_factory->getBusinessId($this->loginUser['id'],$this->loginUser['role']);

            if ($factory_user['factory_id'] != $FactoryId) {
                $this->form_response(600,'no access','no access');

            }


            $data=array();

            $payment_amount = post('payment_amount');
           // $payment_amount =450;
            // 校对当日同笔付款是否已执行 ，如果付过 则 不允许再付款。
            // 插入付款数据
            if($payment_amount<=0) {
                $this->form_response(500, 'amount  must larger than 0!','');
            }


            $mdl_statement= $this->loadModel('statement');

            $mdl_statement->begin();


            //插入客户支付数据
            $customer_payment_data = $mdl_statement->getCustomerPaymentData($this->loginUser['id'],$factory_user,$payment_amount);
            $new_id= $mdl_statement->insert($customer_payment_data);

            if(!$new_id){
                $error=1;
             }

            // 分析该笔支付 是支付那个账单， 顺序依次 是 overdue账单 （按overdue日期递增走），not yet due , 如果还有剩余，则做一个credit . 如果，所剩余额不够下笔账单
            // 付款，则 将余额转成credit .
          //   if(! $isProcessed) $error=1;

            $mdl_statement->updatePaymentsDetails($new_id,$payment_amount,$factory_user,$this->loginUser['id']);

            if($error) {
                $mdl_statement->rollback();
                $this->form_response(500, 'error!','');
            }else{
                $mdl_statement->commit();
                $this->form_response(200,'','');
            }



         //   $this->form_response(600,$payment_amount);

            try {

                     } catch (Exception $e) {
             }

        }else{
            //wrong protocol
        }
    }


    public function customer_recycle_bin_action(){

        if(is_post()){

            $mdl_user_factory =$this->loadModel("user_factory");

            $id = post('id');
           // $id=4 ;
            // 判断如果当前登陆用户和当前操作的记录不是所属关系拒绝操作。

         //   echo json_encode($id); exit;
            $factory_user = $mdl_user_factory->get($id);
            $FactoryId =$mdl_user_factory->getBusinessId($this->loginUser['id'],$this->loginUser['role']);

            if ($factory_user['factory_id'] != $FactoryId) {
                $this->form_response(600, $FactoryId ,'no access');

            }





            $data = array();
            $data['isHide'] = ($factory_user['isHide'] == '0') ? '1' : '0';



            if ($mdl_user_factory->update($data, $id)) {
                echo json_encode(array('isHide' => $data['isHide']));
            } else {
                $this->form_response(600,'Please try again later');
            }



        }else{
            //wrong protocol
            echo json_encode('ooo'); exit;
        }
    }


  public function update_customer_type_action(){

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

				$customer_type = post('customer_type1');
				if($customer_type)$data['customer_type']=$customer_type;



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

        $where = array('role' => 20, 'user_belong_to_user' => $this->loginUser['id']);
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
                    'role' => 20,
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

    public function receive_payments_action() {
        $mdl_user_factory = $this->loadModel('user_factory');

        $search = trim(get2('search'));
        if($this->loginUser['role']==20) {
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

        $this->setData('receive_payments', 'submenu');
        $this->setData('account_management', 'menu');
        $this->display('factory/receive_payments');
    }



    public function customer_list_action() {
        $mdl_user_factory = $this->loadModel('user_factory');

        $search = trim(get2('search'));
        $customer_type = trim(get2('customer_type'));

		if($this->loginUser['role']==20) {
			 $factoryId =  $mdl_user_factory->getFactoryId($this->loginUser['id']);
			 $salesManId = $this->loginUser['id'];
		}else{
			 $factoryId =  $this->loginUser['id'];
			  $salesManId = 0;
		}
       //var_dump($salesManId );exit;


        $pageSql= $mdl_user_factory->getUserFactoryList($factoryId, $search,$salesManId,0,$customer_type,1);
        //var_dump($pageSql);exit;
        $pageUrl = $this->parseUrl()->set('page');
        $pageSize =30;
        $maxPage =100;
        $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
        $data = $mdl_user_factory->getListBySql($page['outSql']);




       // $data = $mdl_user_factory->getUserFactoryList($factoryId, $search,$salesManId,0,$customer_type);
        //var_dump($data);exit;
        foreach ($data as $key => $user) {
            $expiredAt =strtotime("+36 months", time());
            $link = self::customer_login_link($user['id'], $expiredAt,1);
            $data[$key]['login_link'] = $link;
        }


        $customer_type_list  = $this->loadModel('customer_type')->getList(null, array('business_id' => $factoryId ));
      // var_dump($customer_type_list);exit;
        $this->setData($customer_type_list,'customer_type_list');





        $this->setData($page['pageStr'], 'pager');


        $this->setData($search, 'search');
        $this->setData($customer_type, 'customer_type');
        $this->setData($data, 'data');
        $this->setData(date('d-m-Y', $expiredAt), 'expiredAt');
        $this->setData('customer_list', 'submenu_top');
        $this->setData('customer_list', 'submenu');
        $this->setData('customer_management', 'menu');



        $this->display('factory/customer_list');
    }

    public function group_order_setting_action() {


         $search = trim(get2('search'));

        $mdl_user_factory = $this->loadModel('user_factory');
        $factoryId =  $mdl_user_factory->getFactoryId($this->loginUser['id']);


        $users =$this->loadModel('user_group_manager')->getGroupListOfFactory($factoryId);

           if($users){
               foreach ($users as $key=>$value){
                  //$users[$key]['groupName'] =$this->getCustomerName($value);
                   $expiredAt =strtotime("+36 months", time());
                   $link = self::customer_login_link_index($value['userId'], $expiredAt,1);
                   $users[$key]['login_link'] = $link;
               }
           }


       // var_dump($users);exit;
        $this->setData($search, 'search');
        $this->setData($users, 'users');


        $this->setData('group_order_setting', 'submenu');
        $this->setData('customer_management', 'menu');



        $this->display('factory/group_order_setting');
    }



// 创建一个group manager
    function customer_grade_add_action()
    {


        $id = (int)get2('id');

        $mdl_grade = $this->loadModel('factory_customer_grade');
        $grade_rec = $mdl_grade->get($id);
        $this->setData($grade_rec,'data');

        if (is_post()) {

            $factory_id =$this->loadModel('user_factory')->getFactoryId($this->loginUser['id']);

            $id = trim(post('id'));
            $grade_name = trim(post('grade_name'));
            $grade_id = trim(post('grade_id'));
            $grade_discount_rate = trim(post('grade_discount_rate'));


            //校验
            if (empty($grade_name) ) $this->form_response_msg('Please fill in grade name!');
            if (empty($grade_id) ) $this->form_response_msg('Please fill in grade id!');
            if (empty($grade_discount_rate)  ) $grade_discount_rate=0.0;

            if(!is_numeric($grade_discount_rate)) {
                $this->form_response_msg('Please fill number on discount rate!');
            }

            if($grade_discount_rate<0 || $grade_discount_rate>=80) {
                $this->form_response_msg('Please fill the reasonable grade discount rate!');
            }

            if ($id) {
                //进入编辑模式
                $data = array(
                    // 'name' => $name,
                    'grade_id' => $grade_id,
                    'grade_name' => $grade_name,
                    'grade_discount_rate' => $grade_discount_rate,
                    'createUserId' => $this->loginUser['id'],
                    'gen_date' => time()
                  );

                 if ($mdl_grade->update($data, $id)) {

                    $this->form_response(200,'Success',HTTP_ROOT_WWW.'factory/customer_grade');
                } else {
                    $this->form_response_msg('Error ! check if grade Id is unique!');
                }

            } else {
                //插入新grade

                $data = array(
                    // 'name' => $name,
                    'business_id' =>$factory_id,
                    'grade_id' => $grade_id,
                    'grade_name' => $grade_name,
                    'grade_discount_rate' => $grade_discount_rate,
                    'createUserId' => $this->loginUser['id'],
                    'gen_date' => time()
                );


                if($mdl_grade->insert($data)){
                    $this->form_response(200,'Success ',HTTP_ROOT_WWW.'factory/customer_grade');
                }else{
                    $this->form_response(500,'Insert error ');
                }

            }

        } else {

            $this->setData('Add Customer Grade', 'pagename');
            $this->setData('customer_grade', 'submenu');
            $this->setData('customer_management', 'menu');
            $this->setData('Grade Management - Business Center' . $this->site['pageTitle'], 'pageTitle');
            $this->display('factory/customer_grade_add');
        }
    }





    public function customer_grade_action() {




        $mdl_user_factory = $this->loadModel('user_factory');
        $factoryId =  $mdl_user_factory->getFactoryId($this->loginUser['id']);

       // var_dump('测试一下客户经理可以使用该功能'.$factoryId);exit;
        $grade_lists =$this->loadModel('factory_customer_grade')->getGradeList($factoryId);




        $this->setData($grade_lists, 'grade_lists');


        $this->setData('customer_grade', 'submenu');
        $this->setData('customer_management', 'menu');



        $this->display('factory/customer_grade');
    }

    public function customer_list_recycle_action() {
        $mdl_user_factory = $this->loadModel('user_factory');

        $search = trim(get2('search'));
        if($this->loginUser['role']==20) {
            $factoryId =  $mdl_user_factory->getFactoryId($this->loginUser['id']);
            $salesManId = $this->loginUser['id'];
        }else{
            $factoryId =  $this->loginUser['id'];
            $salesManId = 0;
        }
        //var_dump($salesManId );exit;

        $users = $mdl_user_factory->getUserFactoryList($factoryId, $search,$salesManId,1);
       // var_dump($users);exit;
        foreach ($users as $key => $user) {
            $expiredAt =strtotime("+3 months", time());
            $link = self::customer_login_link($user['id'], $expiredAt);
            $users[$key]['login_link'] = $link;
        }

        $this->setData($search, 'search');
        $this->setData($users, 'users');
        $this->setData(date('d-m-Y', $expiredAt), 'expiredAt');

        $this->setData('customer_list', 'submenu');
        $this->setData('customer_management', 'menu');
        $this->display('factory/customer_list_recycle');
    }


    public function customer_price_management_action() {
        $mdl_user_factory = $this->loadModel('user_factory');

        $search = trim(get2('search'));
        $gradeId = trim(get2('grade'));
        if($this->loginUser['role']==20) {
            $factoryId =  $mdl_user_factory->getFactoryId($this->loginUser['id']);
            $salesManId = $this->loginUser['id'];
        }else{
            $factoryId =  $this->loginUser['id'];
            $salesManId = 0;
        }
        //var_dump($salesManId );exit;

        $pageSql = $mdl_user_factory->getUserFactoryList($factoryId, $search,$salesManId,0,0,1,$gradeId);

        //var_dump($pageSql);exit;
        $pageUrl = $this->parseUrl()->set('page');
        $pageSize =30;
        $maxPage =100;
        $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
        $users = $mdl_user_factory->getListBySql($page['outSql']);




        foreach ($users as $key => $user) {
            $expiredAt =strtotime("+3 months", time());
            $link = self::customer_login_link($user['id'], $expiredAt);
            $users[$key]['login_link'] = $link;

        }

        // 获得客户分级列表

        $grade_list = $this->loadModel('factory_customer_grade')->getGradeList($factoryId);
       // var_dump($grade_list);exit;
        $this->setData($grade_list,'grade_list');
        $this->setData($page['pageStr'], 'pager');
        $this->setData($search, 'search');
        $this->setData($gradeId, 'gradeId');
        $this->setData($users, 'users');
        $this->setData(date('d-m-Y', $expiredAt), 'expiredAt');
        $this->setData('customer_price_management', 'submenu');
        $this->setData('customer_management', 'menu');
        $this->display('factory/customer_price_management');
    }



    public function approve_customer_payments_and_discount_action() {
        $mdl_user_factory = $this->loadModel('user_factory');

        $search = trim(get2('search'));
        if($this->loginUser['role']==20) {
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
        $this->setData('approve_customer_payments_and_discount', 'submenu');
        $this->setData('customer_management', 'menu');
        $this->display('factory/approve_customer_payments_and_discount');
    }

    function to_xero_edit_ajax_action()
    {
        $id = (int)get2('id');



       $mdl= $this->loadModel('user_factory');

        $user_info = $mdl->get($id);

        if ($id < 0 || $user_info['factory_id']!=$this->current_business['id'] ) $this->form_response_msg('no access');

        //检查该商家是否可以管理其它店铺，如果授权即可以该商家权限进入系统。



        $data = array();
        $data['to_xero'] = ($user_info['to_xero'] == '0') ? '1' : '0';

        if ($mdl->update($data, $user_info['id'])) {
            echo json_encode(array('to_xero' => $data['to_xero']));
        } else {
            $this->form_response_msg('Please try again later');
        }


    }




    public function customer_xero_management_action() {


        $search = trim(get2('search'));
        $to_xero = trim(get2('to_xero'));
        if(strlen($to_xero)==0){
            $to_xero =0;
        }

        $mdl_user_factory = $this->loadModel('user_factory');
        $pageSql = $mdl_user_factory->getUserFactoryList_xero($this->current_business['id'], $search,$to_xero,1);



        //var_dump($pageSql);exit;
        $pageUrl = $this->parseUrl()->set('page');
        $pageSize =30;
        $maxPage =100;
        $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
        $users = $mdl_user_factory->getListBySql($page['outSql']);



        $this->setData($page['pageStr'], 'pager');
        $this->setData($to_xero, 'to_xero');
        $this->setData($search, 'search');
        $this->setData($users, 'users');
        $this->setData('customer_xero_management', 'submenu');
        $this->setData('account_management', 'menu');
        $this->display('factory/customer_xero_management');
    }
	/**
	 *  Ajax update parent category discount Rate 
	 */

	public function update_customer_parent_cate_discount_action()
	{
		
		//$this->form_response(500,'yes','');
		
		if(is_post()){

			$mdl_restaurant_category =$this->loadModel("restaurant_category");

			$id = post('id');
			$userId = post('userId');
			$cate_id = post('cate_id');
			$discount_rate = post('discount_rate');
			
		//	$id=0;
			//$userId=319227;
			//$cate_id =38537;
			//$discount_rate=2;

			//操作权限： 检查当前用户对当前的客户是否拥有操作权限
			if(!$this->loadModel('user_factory')->isUserAuthorisedToOperate($userId,$this->current_business['id']))
			{
				$this->form_response(500,'you are not allow to operate this customer !','');
				
			}
			//$this->form_response(500,$id.'='.$userId.'='.$cate_id.'='.$discount_rate,'');
			// 在 discount 表里面 找，如果找到，则更改，如果找不到，看当前值与商家的discount rate是否相同，如果相同，则不做任何操作，如果不同则增加一笔及记录，记录该大类的值
			//$this->form_response(500,'here','');
			$mdl_discount =  $this->loadModel('user_factory_category_discount_rate');
			
			$where =array(
			 'userId'=>$userId,
			 'category_id'=>$cate_id
			
			);
		
			$rec =$mdl_discount->getByWhere($where);
			//var_dump($rec);
            if($rec) { // 如果找到该记录
			// $this->form_response(200,'find record','');
				$data=array(
				 'discount_rate' =>$discount_rate
				 
				);
				if($mdl_discount->updateByWhere($data,$where)){
					//$this->form_response(200,'update successful!','');
				}else{
						$this->form_response(500,'error when update','');
				}
				
				
				
			}else{ //未找到记录
			
			   //看看该值与商家discount相同，相同不做任何操作，不同，增加一条记录在discount Limian 
			   //$this->form_response(200,'did not find record','');
			 //  var_dump('did not find record');
			    $where =array(
				 'user_id'=>$userId,
				 'factory_id'=>$this->current_business['id']
				
				);
				$rec1= $this->loadModel('user_factory')->getByWhere($where);
			///	var_dump($rec1['business_discount_rate'].' '.$discount_rate);
				if($rec1) {
					if(!(number_format($discount_rate, 2) == number_format($rec1['business_discount_rate'], 2) ) ){//如果相同不做操作
						$data =array(
						'userId'=>$userId,
						'category_id'=>$cate_id,
						'discount_rate'=>$discount_rate
						);
						if($mdl_discount->insert($data)){
							//	var_dump('insert ok');
								$this->form_response(200,'','');
						}else{
							var_dump('insert fail');
								$this->form_response(500,'insert error','');
						}
							
					}
					
				}
				
			}


		
			
			

			
		
			try {
				$mdl_restaurant_category->update($data,$id);
				$this->loadModel('user')->update(array('store_update_time' =>time()),$idCreateUser['restaurant_id']);

				$this->form_response(200,'','');
			} catch (Exception $e) {
				$this->form_response(500, $e->getMessage(),'');
			}

		}else{
			//wrong protocol
		}
	}


    public function update_garde_parent_cate_discount_action()
    {

        //$this->form_response(500,'yes','');

        if(is_post()){

            $mdl_restaurant_category =$this->loadModel("restaurant_category");

            $id = post('id');
            $gradeId = post('gradeId');
            $cate_id = post('cate_id');
            $discount_rate = post('discount_rate');

            //	$id=0;
            //$userId=319227;
            //$cate_id =38537;
            //$discount_rate=2;

            //操作权限： 检查当前用户对当前的客户是否拥有操作权限

            $grade_rec = $this->loadModel('factory_customer_grade')->get($id);

            if(!$grade_rec || $grade_rec['business_id']!= $this->current_business['id']) {
                $this->form_response(500,'no access','');
            }




            //$this->form_response(500,$id.'='.$userId.'='.$cate_id.'='.$discount_rate,'');
            // 在 discount 表里面 找，如果找到，则更改，如果找不到，看当前值与商家的discount rate是否相同，如果相同，则不做任何操作，如果不同则增加一笔及记录，记录该大类的值
            //$this->form_response(500,'here','');
            $mdl_discount =  $this->loadModel('user_factory_grade_category_discount_rate');

            $where =array(
                'grade_id'=>$gradeId,
                'category_id'=>$cate_id

            );

            $rec =$mdl_discount->getByWhere($where);
            //var_dump($rec);
            if($rec) { // 如果找到该记录
                // $this->form_response(500,'find record','');
                $data=array(
                    'discount_rate' =>$discount_rate

                );
                if($mdl_discount->updateByWhere($data,$where)){
                    //$this->form_response(200,'update successful!','');
                }else{
                    $this->form_response(500,'error when update','');
                }


            }else{ //未找到记录

                //看看该值与商家discount相同，相同不做任何操作，不同，增加一条记录在discount Limian
              //  $this->form_response(500,'did not find record','');
                //  var_dump('did not find record');

                            $data =array(
                            'grade_id'=>$gradeId,
                            'category_id'=>$cate_id,
                           'discount_rate' =>$discount_rate,
                            'business_id'=>$this->current_business['id']
                        );
                        if($mdl_discount->insert($data)){
                            //	var_dump('insert ok');
                            $this->form_response(200,'','');
                        }else{
                            var_dump('insert fail');
                            $this->form_response(500,'insert error','');
                        }





            }








            try {
                $mdl_restaurant_category->update($data,$id);
                $this->loadModel('user')->update(array('store_update_time' =>time()),$idCreateUser['restaurant_id']);

                $this->form_response(200,'','');
            } catch (Exception $e) {
                $this->form_response(500, $e->getMessage(),'');
            }

        }else{
            //wrong protocol
        }
    }

    public function update_customer_sub_cate_discount_action()
    {
//$this->form_response(500,'yes','');

        if(is_post()){

            $mdl_restaurant_category =$this->loadModel("restaurant_category");

            $id = post('id');
            $userId = post('userId');
            $cate_id = post('cate_id');
            $discount_rate = post('discount_rate');

            //	$id=0;
           // $userId=319227;
          //  $cate_id =40692;
          //  $discount_rate=2;

            //操作权限： 检查当前用户对当前的客户是否拥有操作权限
            if(!$this->loadModel('user_factory')->isUserAuthorisedToOperate($userId,$this->current_business['id']))
            {
                $this->form_response(500,'you are not allow to operate this customer !','');

            }
            //$this->form_response(500,$id.'='.$userId.'='.$cate_id.'='.$discount_rate,'');
            // 在 discount 表里面 找，如果找到，则更改，如果找不到，看当前值与商家的discount rate是否相同，如果相同，则不做任何操作，如果不同则增加一笔及记录，记录该大类的值
            //$this->form_response(500,'here','');
            $mdl_discount =  $this->loadModel('user_factory_category_discount_rate');

            $where =array(
                'userId'=>$userId,
                'category_id'=>$cate_id

            );

            $rec =$mdl_discount->getByWhere($where);
            //var_dump($rec);
            if($rec) { // 如果找到该记录
                // $this->form_response(200,'find record','');
                $data=array(
                    'discount_rate' =>$discount_rate

                );
                if($mdl_discount->updateByWhere($data,$where)){
                    $this->form_response(200,'update successful!','');
                }else{
                    $this->form_response(500,'error when update','');
                }



            }else{ //未找到记录

                //看看该值与商家discount相同，相同不做任何操作，不同，增加一条记录在discount Limian
               // $this->form_response(500,'did not find record '.$userId.' '.$cate_id.' '.$discount_rate,'');
                //  var_dump('did not find record');
                $where =array(
                    'user_id'=>$userId,
                    'factory_id'=>$this->current_business['id']

                );
                $rec1= $this->loadModel('user_factory')->getByWhere($where);
                ///	var_dump($rec1['business_discount_rate'].' '.$discount_rate);
                if($rec1) {

                        $data =array(
                            'userId'=>$userId,
                            'category_id'=>$cate_id,
                            'discount_rate'=>$discount_rate
                        );
                        if($mdl_discount->insert($data)){
                            //	var_dump('insert ok');
                            $this->form_response(200,'','');
                        }else{
                          //  var_dump('insert fail');
                            $this->form_response(500,'insert error!','');
                        }



                }

            }


        }else{
            //wrong protocol
        }
    }

    public function update_grade_menu_discount_action()
    {


        if(is_post()){



            $id = post('id');
            $gardeId = post('gardeId');
         //   $this->form_response(500,'id is '.$id. ' and  grade id is'.$gardeId,'');

            $discount_rate = post('discount_rate');

            	//$id=385486;
              //   $discount_rate=6.6;
               //  $gardeId=6;
            //  $cate_id =40692;
            //  $discount_rate=2;

            //操作权限： 检查当前用户对当前的客户是否拥有操作权限
            $where1 =array(
                'grade_id'=>$gardeId ,
                'business_id'=>$this->current_business['id']

            );
            $grade_rec = $this->loadModel('factory_customer_grade')->getByWhere($where1);

            if(!$grade_rec ) {
                $this->form_response(500,'no access no reccord grade id is'.$gardeId,'');
            }

            if( $grade_rec['business_id']!= $this->current_business['id']) {
                $this->form_response(500,'no access ,no match','');
            }

            // 如果输入的数字不是数字或者是小于0的数字则提示输入错误;

            if(!is_numeric($discount_rate) || number_format($discount_rate,1)<0) {

                $this->form_response(500,'Please input number and must be >=0','');
            }

            //$this->form_response(500,$id.'='.$userId.'='.$cate_id.'='.$discount_rate,'');
            // 在 discount 表里面 找，如果找到，则更改，如果找不到，看当前值与商家的discount rate是否相同，如果相同，则不做任何操作，如果不同则增加一笔及记录，记录该大类的值
            //$this->form_response(500,'here','');
            $mdl_discount =  $this->loadModel('user_factory_grade_menu_price');

            $where =array(
                'grade_id'=>$gardeId,
                'restaurant_menu_id'=>$id

            );

            $rec =$mdl_discount->getByWhere($where);
            $mdl_menu =$this->loadModel('restaurant_menu');
            $menu_rec = $mdl_menu->get($id);
            $discount_price = number_format($menu_rec['price']*(100-$discount_rate)/100,2);
          //  var_dump($rec);
            if($rec) { // 如果找到该记录
                // $this->form_response(200,'find record','');

                //两种情况，如果折扣率为0 ，表示取消产品级的折扣设定，直接删除相关记录。
                //如果折扣大于0 ，则进行更改；
                if(number_format($discount_rate,2) ==0.00){
                    if($mdl_discount->deleteByWhere($where)) {
                        $this->form_response(200,'deleted','');
                    }else{
                        $this->form_response(500,'delete error','');
                    }
                }else{

                    $data=array(
                        'menu_discount_rate' =>$discount_rate,
                        'price'=>$discount_price

                    );
                    if($mdl_discount->updateByWhere($data,$where)){
                        $this->form_response(200,$discount_price,'');
                    }else{
                        $this->form_response(500,'error when update','');
                    }
                }





            }else{ //未找到记录



                $data =array(
                    'grade_id'=>$gardeId,
                    'restaurant_menu_id'=>$id,
                    'price'=>$discount_price,
                    'menu_discount_rate'=>$discount_rate
                );
                if($mdl_discount->insert($data)){
                    //	var_dump('insert ok');
                    $this->form_response(200,$discount_price,'');
                }else{
                    //  var_dump('insert fail');
                    $this->form_response(500,'insert error','');
                }





            }


        }else{
            //wrong protocol
        }
    }


    public function update_customer_menu_discount_action()
    {


        if(is_post()){



            $id = post('id');
            $userId = post('user_id');

            $discount_rate = post('discount_rate');

            //	$id=0;
            // $userId=319227;
            //  $cate_id =40692;
            //  $discount_rate=2;

            //操作权限： 检查当前用户对当前的客户是否拥有操作权限
            if(!$this->loadModel('user_factory')->isUserAuthorisedToOperate($userId,$this->current_business['id']))
            {
                $this->form_response(500,'you are not allow to operate this customer !','');

            }

           // 如果输入的数字不是数字或者是小于0的数字则提示输入错误;

            if(!is_numeric($discount_rate) || number_format($discount_rate,1)<0) {

                $this->form_response(500,'Please input number and must be >=0','');
            }

            //$this->form_response(500,$id.'='.$userId.'='.$cate_id.'='.$discount_rate,'');
            // 在 discount 表里面 找，如果找到，则更改，如果找不到，看当前值与商家的discount rate是否相同，如果相同，则不做任何操作，如果不同则增加一笔及记录，记录该大类的值
            //$this->form_response(500,'here','');
            $mdl_discount =  $this->loadModel('user_factory_menu_price');

            $where =array(
                'user_id'=>$userId,
                'restaurant_menu_id'=>$id

            );

            $rec =$mdl_discount->getByWhere($where);
            $mdl_menu =$this->loadModel('restaurant_menu');
            $menu_rec = $mdl_menu->get($id);
            $discount_price = number_format($menu_rec['price']*(100-$discount_rate)/100,2);
            //var_dump($rec);
            if($rec) { // 如果找到该记录
                // $this->form_response(200,'find record','');

                //两种情况，如果折扣率为0 ，表示取消产品级的折扣设定，直接删除相关记录。
                //如果折扣大于0 ，则进行更改；
                if(number_format($discount_rate,2) ==0.00){
                    if($mdl_discount->deleteByWhere($where)) {
                        $this->form_response(200,'deleted','');
                    }else{
                        $this->form_response(500,'delete error','');
                    }
                }else{

                    $data=array(
                        'menu_discount_rate' =>$discount_rate,
                        'price'=>$discount_price

                    );
                    if($mdl_discount->updateByWhere($data,$where)){
                        $this->form_response(200,$discount_price,'');
                    }else{
                        $this->form_response(500,'error when update','');
                    }
                }





            }else{ //未找到记录



                    $data =array(
                        'user_id'=>$userId,
                        'restaurant_menu_id'=>$id,
                        'price'=>$discount_price,
                        'menu_discount_rate'=>$discount_rate
                    );
                    if($mdl_discount->insert($data)){
                        //	var_dump('insert ok');
                        $this->form_response(200,$discount_price,'');
                    }else{
                        //  var_dump('insert fail');
                        $this->form_response(500,'insert error','');
                    }





            }


        }else{
            //wrong protocol
        }
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
	
	 function login_as_agent($userid,$url) {
        
     
        $mdl_user = $this->loadModel( 'user' );
        if(1){
            $user = $mdl_user->getUserById( $userId );
            $data = array(
                'lastLoginIP'	=> ip(),
                'lastLoginDate'	=> time(),
                'loginCount'	=> $user['loginCount'] + 1
            );

            $mdl_user->updateUserById( $data, $user['id'] );

            $this->session( 'member_user_id', $user['id'] );
            $this->session( 'member_user_shell', $this->md5( $user['id'].$user['name'].$user['password'] ) );

          //  $this->sheader(HTTP_ROOT_WWW . 'factory/order_for_customer_new');
        }
    }
 public function order_for_customer_new_action()
    {

        //检查当前用户是否为客户登陆，还是商家或agent登陆， 如果商家或agent登陆 truelogin is false;
       if(session('truelogin') ){
           //为客户登陆
         //  var_dump('still true login!');exit;
           // 如果该客户没有分店，则提示异常登陆，如果该客户有分店，则获取分店信息，进入帮助分店的点单页面
           $mdl = $this->loadModel('user_group');
           $count  =   $mdl->getCountOfMembers($this->loginUser['id']);
           if($count>0) {
               $userList =$this->get_customer_list_of_groupManager($this->loginUser['id']);
                $this->setData($count,'count');
               //目前暂时只有 319188下单，则在这里把购物链接生成，直接代入到前端页面
               // 将来 这部分代码生成，需要选择前端 下单按钮后，在user_factory里面 获取该客户的供应商连接，然后依次生成带下单连接即可。
               foreach ($userList as $key=>$user){

                       $expiredAt =strtotime("+36 months", time());
                       $link = self::customer_login_link_groupMember($user['id'], $expiredAt);
                       $userList[$key]['login_link'] = $link;


               }
              // var_dump($userList);exit;
               $this->setData(json_encode($userList), 'userList');
               $this->display_pc_mobile('factory/salesman/order_for_group_member', 'factory/salesman/order_for_group_member');
           }else{
               var_dump('no access!');exit;
           }
       }else{

           /*
           获取 当前cookie的groupmanager历史否值，如果有代表目前是groupmanger在管理下单工作。
           */
           $groupManager =$this->cookie->getCookie('groupManager');
           if($groupManager) { //如果当前有groupmanager登陆路过，则优先处理groupmanager
               $this->groupMangerCheckAndSheader($groupManager, 'factory/order_for_customer_new');
               return 1;
           }




           //如果当前已agent方式登陆，则强制转换为agent登陆方式
           $this->AgentActiveCheck($this->loginUser['id'],$this->cookie->getCookie('agentcityb2b'));

           //获取当前操作者的客户列表
           $factoryList =$this->get_customer_list_of_salesOrOwner();
           //var_dump($factoryList);exit;


           $this->setData(json_encode($factoryList), 'factoryUsers');


           $this->display_pc_mobile('factory/salesman/order_for_customer_new', 'factory/salesman/order_for_customer_new');

       }

    }

    public function customer_info_action() {

        $userId = trim(get2('id'));
        $this->setData($userId,'userId');
        //get all customer information

        //如果当前已agent方式登陆，则强制转换为agent登陆方式

        $this->AgentActiveCheck($this->loginUser['id'],$this->cookie->getCookie('agentcityb2b'));


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

        if($this->loginUser['role']==20) {


        }else {
            $where =array (
                'user_belong_to_user'=>$this->loginUser['id'],
                'role'=>20
            );
            $sales_list = $this->loadModel('user')->getList(null,$where);
            //var_dump($sales_list);exit;
            $this->setData($sales_list,'sales_list');

        }


        $expiredAt =strtotime("+36 months", time());
        $link = self::customer_login_link($userId, $expiredAt);
        $user['login_link'] = $link;


        $this->setData($user,'user');
        $this->setData($user_factory_info,'user_factory_info');
        $this->setData($user_abn,'user_abn');
        //exit;







        $this->setData('customer_list', 'submenu_top');
        $this->setData('customer_list', 'submenu');
        $this->setData('customer_management', 'menu');



        $this->display_pc_mobile('factory/salesman/customer_info', 'factory/salesman/customer_info');



    }

    public function customer_list_mobile_action()
    {
		
		
		
		
		
		
		   //检查当前用户是否为客户登陆，还是商家或agent登陆， 如果商家或agent登陆 truelogin is false;
       if(session('truelogin')){
           //为客户登陆
         //  var_dump('still true login!');exit;
           // 如果该客户没有分店，则提示异常登陆，如果该客户有分店，则获取分店信息，进入帮助分店的点单页面
           $mdl = $this->loadModel('user_group');
           $count  =   $mdl->getCountOfMembers($this->loginUser['id']);
           if($count>0) {
               $userList =$this->get_customer_list_of_groupManager($this->loginUser['id']);
                $this->setData($count,'count');
               //目前暂时只有 319188下单，则在这里把购物链接生成，直接代入到前端页面
               // 将来 这部分代码生成，需要选择前端 下单按钮后，在user_factory里面 获取该客户的供应商连接，然后依次生成带下单连接即可。
               foreach ($userList as $key=>$user){

                       $expiredAt =strtotime("+36 months", time());
                       $link = self::customer_login_link_groupMember($user['id'], $expiredAt);
                       $userList[$key]['login_link'] = $link;


               }
              // var_dump($userList);exit;
               $this->setData(json_encode($userList), 'userList');
               $this->display_pc_mobile('factory/group_manager/customer_list', 'factory/group_manager/customer_list');
           }else{
               var_dump('no access!');exit;
           }
       }else{

           /*
           获取 当前cookie的groupmanager历史否值，如果有代表目前是groupmanger在管理下单工作。
           */
           $groupManager =$this->cookie->getCookie('groupManager');
           if($groupManager) { //如果当前有groupmanager登陆路过，则优先处理groupmanager
               $this->groupMangerCheckAndSheader($groupManager, 'factory/customer_list_mobile');
               return 1;
           }




        //如果当前已agent方式登陆，则强制转换为agent登陆方式

        $this->AgentActiveCheck($this->loginUser['id'],$this->cookie->getCookie('agentcityb2b'));

        //获取当前操作者的客户列表
        $factoryList =$this->get_customer_list_of_salesOrOwner();
        //var_dump($factoryList);exit;


        $this->setData(json_encode($factoryList), 'factoryUsers');


        $this->display_pc_mobile('factory/salesman/customer_list', 'factory/salesman/customer_list');


       }

		
		
		
		
		
		
		
		
		
		
		
		
		


    }

  // 获取当前操作者的客户列表
    function get_customer_list_of_salesOrOwner(){
        $mdl_user_factor = $this->loadModel('user_factory');
        //获得当前用户的实际商家所有者商家id
        $factoryId = $mdl_user_factor->getBusinessId( $this->loginUser['id'], $this->loginUser['role']);

        if(	$this->loginUser['role']==20) {
            if($this->loadModel('staff_roles')->CanOperateAllCustomer($this->loginUser['id'])){
                //不做操作
            }else{
                $salesManId = $this->loginUser['id'];
            }

        }
        $factoryList = $mdl_user_factor->getUserFactoryList($factoryId,null,$salesManId);
        foreach ($factoryList as $key => $user) {
            $expiredAt =strtotime("+36 months", time());
            $link = self::customer_login_link($user['id'], $expiredAt);
            $factoryList[$key]['login_link'] = $link;
        }

        return $factoryList;
    }


    // 获取当前操作者的客户列表
    function get_customer_list_of_groupManager($manager_id){


        //获得当前用户的实际商家所有者商家id

        $mdl =$this->loadModel('user_group');
        $factoryList = $mdl->getListOfGroupUser($manager_id);

       // var_dump($factoryList);exit;
        return $factoryList;
    }



    //定义企业员工导向页面入口
    public function employee_navigation_panel_action(){

        $role_id=get2('role_id');
        //检测员工是否拥有权限；
        /*   $isHasRole = $this->loadModel('staff_roles')->isHasRoles($this->loginUser['id'],$role_id); */
        $nav_page = $this->employee_navigation_panel($role_id);
       //  var_dump();
        $this->display('factory/' . $nav_page);

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





    public function customer_login_link_groupMember($userId, $expired) {
        $mdl_user_factory = $this->loadModel('user_factory');
        $factoryId = 319188;
        $token = $mdl_user_factory->generateUserLoginToken($userId,$factoryId, $expired);

        return HTTP_ROOT . "factorypage/user_link_login?user_id=$userId&factory_id=" . $factoryId. "&token=$token";
    }

    public function customer_login_link($userId, $expired,$notAgent) {
        $mdl_user_factory = $this->loadModel('user_factory');
        $factoryId = $mdl_user_factory->getBusinessId($this->loginUser['id'],$this->loginUser['role']);
        $token = $mdl_user_factory->generateUserLoginToken($userId,$factoryId, $expired);

        return HTTP_ROOT . "factorypage/user_link_login?user_id=$userId&notAgent=".$notAgent."&factory_id=" . $factoryId. "&token=$token";
    }

    public function customer_login_link_index($userId, $expired,$notAgent) {
        $mdl_user_factory = $this->loadModel('user_factory');
        $factoryId = $mdl_user_factory->getBusinessId($this->loginUser['id'],$this->loginUser['role']);
        $token = $mdl_user_factory->generateUserLoginToken($userId,$factoryId, $expired);

        return HTTP_ROOT . "factorypage/user_link_login_index?user_id=$userId&notAgent=".$notAgent."&factory_id=" . $factoryId. "&token=$token";
    }
    /**
     *  Ajax update the driver of Truck 
     */

    public function update_truck_driver_action()
    {
        if(is_post()){

            $mdl_truck = $this->loadModel('truck');

            $id = post('id');


            $truckRec  = $mdl_truck->get($id);

            if($truckRec['business_id'] != $this->current_business['id']){
                $this->form_response(600,'no access');
            }





            $data=array();

            $update_field_name = post('update_field_name');

            $value = post('value');

            $data['current_driver'] =$value;


            try {
                $mdl_truck->update($data,$id);

                $this->form_response(200,'','');
            } catch (Exception $e) {
                $this->form_response(500, $e->getMessage(),'');
            }

        }else{
            //wrong protocol
        }
    }


  /**
     *  Ajax update the truck of the order 
     */

    public function update_order_truck_no_action()
    {
        if(is_post()){

            $mdl_order = $this->loadModel('order');

            $id = post('id');


            $orderRec  = $mdl_order->get($id);

            if($orderRec['business_userId'] != $this->current_business['id']){
                $this->form_response(600,'no access');
            }


            $data=array();

           
            $value = post('value');

            $data['logistic_truck_No'] =$value;


            try {
                $mdl_order->update($data,$id);

                $this->form_response(200,'','');
            } catch (Exception $e) {
                $this->form_response(500, $e->getMessage(),'');
            }

        }else{
            //wrong protocol
        }
    }


    public function order_invoice_action(){



        $orderId = get2('order_id');
        $type = get2('type');

        $this->order_invoice($orderId,$type);


    }
	
	  function truck_list_action()
    {
        $id = (int)get2('id');
        $mdl_truck = $this->loadModel('truck');
		
		$mdl_staff_roles =  $this->loadModel('staff_roles');
		$driverList = $mdl_staff_roles->getAllDriverOfBusiness($this->current_business['id']);
		$this->setData($driverList,'driverList');
       
	   $where = array('business_id' => $this->current_business['id']);
        $list = $mdl_truck->getList(null, $where, ' id asc');
		//var_dump($this->currentBusinessId);exit;
        $this->setData($list, 'list');

        $this->setData('Turck List', 'pagename');
        $this->setData('trucklist', 'submenu');
        $this->setData('Logistic_centre', 'menu');
        $this->setData('TruckManagement' . $this->site['pageTitle'], 'pageTitle');
        $this->display('factory/truck_list');
    }
	
	 function truck_edit_action()
    {

        $mdl_truck = $this->loadModel('truck');


        $id = (int)get2('id');

        $truck = $mdl_truck->getByWhere(array('id' => $id, 'business_id' => $this->current_business['id']));

        $mdl_staff_roles =  $this->loadModel('staff_roles');
        $driverList = $mdl_staff_roles->getAllDriverOfBusiness($this->current_business['id']);
        $this->setData($driverList,'driverList');
        //var_dump($dirverList);exit;


        if (is_post()) {



            //var_dump($id);exit;

            $truck_name = trim(post('truck_name'));
            $truck_no = trim(post('truck_no'));
            $plate_number = trim(post('plate_number'));
            $current_driver = trim(post('diverId'));

            $made_factory = trim(post('made_factory'));
            $load_tones = trim(post('load_tones'));


            $where =array(
                'business_id'=>$this->current_business['id'],
                'truck_no'=>$truck_no
            );

            $truc_rec = $mdl_truck->getByWhere($where);

            if($truc_rec){
                $this->form_response_msg('the truck number is exist,please use a different truct number');
            }


            $data = array(
                'truck_no'=>$truck_no,
                'truck_name'=>$truck_name,
                'plate_number'=>$plate_number,
                'made_factory'=>$made_factory,
                'load_tones'=>$load_tones,
                'current_driver'=>$current_driver
            );


            if($truck && $id) {

                if ($mdl_truck->update($data, $id)) {

                    $this->form_response(200,'saved',HTTP_ROOT_WWW.'factory/truck_list');
                } else {
                    $this->form_response_msg('something wrong');
                }

            }else{
                $data['business_id'] = $this->current_business['id'];
                if ($mdl_truck->insert($data)) {


                    $this->form_response(200,'saved',HTTP_ROOT_WWW.'factory/truck_list');
                } else {
                    $this->form_response_msg('something wrong');
                }
            }






        } else {
            $this->setData($truck, 'data');
            $this->setData('Turck Edit', 'pagename');
            $this->setData('trucklist', 'submenu');
            $this->setData('Logistic_centre', 'menu');
            $this->setData('TruckManagement' . $this->site['pageTitle'], 'pageTitle');

            $this->display('factory/truck_edit');
        }
    }



    function item_xero_download_sync_setting_action(){
        // 获得该用户餐厅的菜单分类信息

        if(!$customer_id) {
            $customer_id =$this->current_business['id'];

        }
        $this->setData($customer_id,'customer_id');

        $mdl = $this->loadModel('authrise_manage_other_business_account');
        $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);

        $this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');

        $mdl_restaurant_menu = $this->loadModel('restaurant_menu');

        if($authoriseBusinessList) { //如果该商家可以托管账户
            // 检查接收的托管的商家是否合法



            $isAuthoriseCustomer =0 ;
            foreach ($authoriseBusinessList as $key => $value) {
                if($customer_id ==$value['customer_id'] || $customer_id ==$this->loginUser['id']) {
                    $isAuthoriseCustomer =1;
                }

            }

            if($isAuthoriseCustomer) { //如果是授权的customer


                $mdl_restaurant_category = $this->loadModel('restaurant_category');
                $pageSql = "select  * from cc_restaurant_category where createUserId=$customer_id  and (length(category_cn_name)>0 or length(category_en_name)>0) and ( parent_category_id =0 or  parent_category_id is null) and isdeleted =0  order by isHide,category_sort_id ";
                $data = $mdl_restaurant_category->getListBySql($pageSql);


                if(!$data) {
                    //$this->sheader(null,'您需要首先定义餐厅的菜单分类,然后才可以定义菜品....');
                }
                $this->setData($data,'restaurant_category');



                $sql_Parent_cate_list ="select *,  if(`parent_category_id`,concat('---',category_cn_name),category_cn_name) as category_cn_name1 ,if(`parent_category_id`,concat(category_cn_name),category_cn_name) as   category_cn_name2 ,if(`parent_category_id`,concat(`parent_category_id`,id),concat(id,0)) as parent_id  from cc_restaurant_category where restaurant_id=$customer_id and (length(category_cn_name)>0 or length(category_en_name)>0) and isdeleted =0  order by isHide, parent_id,category_sort_id ";

                $data_parent_cate_list  = $mdl_restaurant_category->getListBySql($sql_Parent_cate_list);
                //var_dump($sql_Parent_cate_list);exit;



                //$ParentCategoryList = $mdl_restaurant_category->getParentCateList($customer_id);
                $catList = $mdl_restaurant_category->getCateList($customer_id);
                $this->setData($catList, 'catList');
                //var_dump($subCategoryList);exit;

                $this->setData($data_parent_cate_list, 'data_parent_cate_list');
                $sk = trim(get2('sk'));

                $allOrspecial = trim(get2('allOrspecial'));

                $onoffguigecatinfo = trim(get2('onoffguigecatinfo'));
                $this->setData($onoffguigecatinfo,'onoffguigecatinfo');

                $onoffcninfo = trim(get2('onoffcninfo'));
                $this->setData($onoffcninfo,'onoffcninfo');
                //	var_dump($onoffcninfo);exit;

                $sub_category =trim(get2('sub_category'));
                $this->setData($sub_category,'sub_category');
                $category = trim(get2('category'));

                if(!$category) {$category='all';}
                //		var_dump($sub_category);exit;
                $this->setData($sk,'sk');
                $this->setData($category,'category1');


                $sqlMenu ="SELECT m.id, m.`restaurant_category_id`,m.`sub_category_id`,trim(concat(m.id,'-',m.menu_id,'-',m.`menu_en_name`)) as menu_en_name  FROM `cc_restaurant_menu` m WHERE m.`restaurant_id` =319188 and m.visible=1 and m.isDeleted=0 and (length(m.menu_cn_name) >0 or length(m.menu_en_name) >0)";
                $menuList =$mdl_restaurant_menu->getListBySql($sqlMenu);
                $this->setData($menuList,'menuList');





                $sql = "select   m.category_id,m.restaurant_menu_id ,o.* ,b.category_cn_name,b.category_en_name  from cc_restaurant_menu o left join cc_restaurant_category b on b.id=o.restaurant_category_id left join cc_restaurant_menu_category m on o.id = m.restaurant_menu_id";

                $whereStr.=" o.restaurant_id = $customer_id and o.isDeleted =0  ";

                if($category =='all' or empty($category)) {
                    $whereStr.=" and (length(o.menu_cn_name) >0 or length(o.menu_en_name) >0) ";
                }else{

                    if($sub_category) {
                        $whereStr.= " and ( m.category_id= $sub_category) ";
                    }else{
                        $whereStr.= " and (o.restaurant_category_id='$category'  or m.category_id= $category ) ";
                    }

                }




                // 提示用户选择菜单分类,如果没有选择菜单分类,则显示当前全部的菜单.
                // 如果选择某一种分类,如果当前没有数据则进行增加50个,如果有数据则直接显示即可.

                $mdl_xero_items_match = $this->loadModel('xero_items_match');
                $pageSql=$mdl_xero_items_match->getXeroMatchList($this->current_business['id'],$sk) ;


                $pageUrl = $this->parseUrl()->set('page');
                $pageSize =30;
                $maxPage =200;
                $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
                $data = $mdl_xero_items_match->getListBySql($page['outSql']);


                $key = 'id';

                $data=$this->assoc_unique($data, $key);
//var_dump($data);exit;

                // 获得该用户的gst type

                $mdl_user =$this->loadModel("user");
                $customerInfo = $mdl_user->get($customer_id);

                //var_dump($customerInfo);exit;





            }else{  //如果可以管理更多店铺




            }


        }//结束主处理


        //获取该商家是否有多个供应商，是否为集合店

        $this->loadModel('freshfood_disp_suppliers_schedule');
        $suppliersList = DispCenter::getSupplierListWithName($customer_id);
        //var_dump($suppliersList);exit;
        if( count($suppliersList) ==1 && $suppliersList[0]['suppliers_id']!=$customer_id ) {  //如果该配货中心下只有一个商家


        }

        $this->setData($suppliersList, 'suppliersList');
        $this->setData($page['pageStr'], 'pager');
        $this->setData($this->parseUrl()->setPath('restaurant/restaurant_edit'), 'editUrl');




        // 获得配菜分类列表

        $where=array();
        $where[]="(length(category_cn_name) >0 or length(category_en_name) >0)";
        $where['restaurant_id']=$customer_id;
        $restaurant_sidedish_category_list=$this->loadModel('restaurant_sidedish_category')->getList(null,$where);
        $this->setData($restaurant_sidedish_category_list,'sidedish_category_list');

        //  获得配菜分类列表

        $where=array();
        $where[]="(length(category_cn_name) >0 or length(category_en_name) >0)";
        $where['restaurant_id']=$customer_id;
        $restaurant_menu_option_list=$this->loadModel('restaurant_menu_option_category')->getList(null,$where);
        $this->setData($restaurant_menu_option_list,'menu_option_list');


        //	var_dump($pager);exit;
        foreach ($data as $key => $menu) {
            $categoryIds = $this->loadModel('restaurant_menu_category')->findCategoryIdsByMenuId($menu['id']);
            $data[$key]['categoryIds'] = $categoryIds;
        }
        $this->setData($data, 'data');

        $this->setData('restaurant_menu', 'submenu_top');

        $this->setData('item_xero_download_sync_setting', 'submenu');
        $this->setData('account_management', 'menu');

        $pagename = "xero item match";
        $pageTitle=  $pagename." - Business Centre - ". $this->site['pageTitle'];

        $this->setData($pagename, 'pagename');

        $this->setData($pageTitle, 'pageTitle');

        $this->setData($this->loginUser['gst_type'], 'gstType');
        $this->display_pc_mobile('factory/item_xero_download_sync_setting', 'factory/item_xero_download_sync_setting');
    }



    function update_xero_match_spec_id_action(){



        $id = (int)post('id');
        $spec_id = (int)post('spec_id');
        //   $id=385785;
        $updateArr =array(
            'guige_id'=>$spec_id
        );
        $mdl_match =$this->loadModel('xero_items_match');
        if($mdl_match->update($updateArr,$id)){
             echo(json_encode(array('error'=>0)));
        }else{
            echo(json_encode(array('error'=>1)));
        }







    }

    function get_guige_of_menu_action(){



          $id = (int)get2('id');
            $menu_id = (int)get2('menu_id');
         //   $id=385785;
            $updateArr =array(
                'product_id'=>$menu_id,
                'guige_id'=>''
            );
            $mdl_match =$this->loadModel('xero_items_match');
            $mdl_match->update($updateArr,$id);



             $mdl=$this->loadModel('restaurant_menu_option');

            $result =$mdl->getGuigeDetails($menu_id);

            echo json_encode($result);




    }
    function assoc_unique($arr, $key) {

        $tmp_arr = array();

        foreach ($arr as $k => $v) {

            if (in_array($v[$key], $tmp_arr)) {//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true

                unset($arr[$k]);

            } else {

                $tmp_arr[] = $v[$key];

            }

        }
        return $arr;
    }
}