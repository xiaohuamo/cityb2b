<?php
/**
 *  corecsm.php 是所有前端功能和admin功能的父类。所有设计业务流程数据操作的代码应该尽量并入Model中。
 */

define('SPECIAL_EVENT_TIME_START', '20:00:00 2019-07-11');

define('SPECIAL_EVENT_TIME_START_ORDER_LIMIT', '20:00:00 2019-07-11');
define('SPECIAL_EVENT_TIME_START1', '2019-07-11 8pm');


define('DEFAULT_PAYPAL_SURCHARGE', '0.0295'); 
define('DEFAULT_ROYALPAY_SURCHARGE', '0.016'); 
define('DEFAULT_HCASH_SURCHARGE', '0.03'); 
define('DEFAULT_CREDITCARD_SURCHARGE', '0.015'); 

define('UBONUSSHOPID', '23989');//自营和商家托管的产品
define('UBONUSSUPPORTID', '25201');//Ubonusharrymo  用户客服和直播
define('UBONUSOFFICIALID', '261');//usales  用于模版编辑
define('UBONUSTICKETS', '25389');//ubonustickets Ubonus活动中心

//系统提成Base + Rate. 单个产品可以单独设置
define('DEFAULT_PLATFORM_COMMISSION_BASE', '0');
define('DEFAULT_PLATFORM_COMMISSION_RATE', '0.1');  

class corecms
{

	public $db;
	protected $tpl;
	protected $file;
	private $classlen = CLASS_LEN;
	private $url_parses = array();
	protected $cookie;
	protected $lang;
	

	function corecms ()
	{	
		$GLOBALS['system'] = &$this;
		$this->db = new db();
		$this->tpl = new Smarty();
		$this->file = new file();
		require_once( CORE_DIR.'v2.1/Cookie.php' );
		$this->cookie = new Cookie;

		libxml_use_internal_errors(true);

		if ( $GLOBALS['gbl_con'] == 'admin' ) {
			//$this->lang = simplexml_load_file(CORE_DIR.'lang/admin/'.$_SESSION['admin_lang'].'.xml');
			$this->lang = simplexml_load_file(CORE_DIR.'lang/admin/'.$GLOBALS['admin_lang'].'.xml');
		}
		else {
			//$this->lang = simplexml_load_file(CORE_DIR.'lang/'.$_SESSION['lang'].'.xml');
			$this->lang = simplexml_load_file(CORE_DIR.'lang/'.$this->getLangStr().'.xml');
		}

		if ($this->lang === false) {
		    echo "Failed loading language xml\n";
		    foreach(libxml_get_errors() as $error) {
		        echo "\t", $error->message;
		    }
		}

		$this->setData($this->lang, 'lang');
		$this->setData( $this->getLangStr(), 'langStr' );
      //  var_dump('this lang is :'.$this->lang);
      //  var_dump('this langstr is :'.$this->getLangStr()); exit;

        $this->tpl->config_dir		= &$GLOBALS['TPL_SM_CONFIG_DIR'];
		$this->tpl->caching			= &$GLOBALS['TPL_SM_CACHEING'];
		$this->tpl->template_dir	= &$GLOBALS['TPL_SM_TEMPLATE_DIR'];
		$this->tpl->compile_dir		= &$GLOBALS['TPL_SM_COMPILE_DIR'];
		$this->tpl->cache_dir		= &$GLOBALS['TPL_SM_CACHE_DIR'];
		$this->tpl->left_delimiter	= &$GLOBALS['TPL_SM_DELIMITER_LEFT'];
		$this->tpl->right_delimiter	= &$GLOBALS['TPL_SM_DELIMITER_RIGHT'];
		$this->tpl->force_compile	= true;

        $lang = trim( $_COOKIE['lang'] );
        if(!lang){
            $lang='en';
        }

	}

	function getUserDevice( $ua = null ) {
		if ( ! isset( $ua ) ) {
			$ua = $_SERVER['HTTP_USER_AGENT'];
		}
		$iphone = strstr( strtolower( $ua ), 'mobile' );
		$android = strstr( strtolower( $ua ), 'android' );
		$windowsPhone = strstr( strtolower( $ua ), 'phone' );
		$microMessenger = strstr( strtolower( $ua ), 'micromessenger' );
	
		if ( $microMessenger ) return 'wechat';
	
		$androidTablet = $this->androidTablet( $ua );
		$ipad = strstr( strtolower( $ua ), 'ipad' );
	
		if ( $androidTablet || $ipad ) {
			return 'tablet';
		}
		elseif ( $iphone && ! $ipad || $android && ! $androidTablet || $windowsPhone ) {
			return 'mobile';
		}
		else {
			return 'desktop';
		}
	}
	function display_pc_mobile($pc_page,$mobile_page)
	{
		$us=$this->getUserDevice();
		if($us=='desktop'){
			$this->display( $pc_page );
		}else{
			$this->display( $mobile_page);
		}
		
	}
	
	function androidTablet( $ua ) {
		if ( strstr( strtolower( $ua ), 'android' ) ) {
			if ( ! strstr( strtolower( $ua ), 'mobile' ) ) {
				return true;
			}
		}
	}
	function getLangStr() {
      //  var_dump($GLOBALS['lang']);exit;

		if ( isset( $GLOBALS['lang'] ) ) {
			$lang = $GLOBALS['lang'];

  	}
		else {
			$lang = trim( $_COOKIE['lang'] );
            //var_dump($lang);exit;
		}
		return $lang;
	}
	
	protected function setTpl ($cache = false, $tplTemplatePath = null, $tplCompile = null, $tplCache = null)
	{
		$GLOBALS['TPL_SM_CACHEING'] = $cache;
		if ($tplTemplatePath)
		{
			$GLOBALS['TPL_SM_TEMPLATE_DIR'] = $tplTemplatePath;
		}
		if ($tplCompile)
		{
			$GLOBALS['TPL_SM_COMPILE_DIR'] = $tplCompile;
		}
		if ($tplCache)
		{
			$GLOBALS['TPL_SM_CACHE_DIR'] = $tplCache;
		}
	}

	protected function setData ($data, $name = null)
	{
		if (!isset($name) || $name === false) $name = 'data';
		$this->tpl->assign($name, $data);
	}

    protected function responseDisplay($page = null)
    {
    	if($this->getUserDevice()=='desktop'){
    		$this->display($page);
    	}else{
    		$this->display('mobile/'.$page);
    	}
    }

	protected function display ($page = null)
	{
		$this->setData(UPLOAD_PATH, "UPLOAD_PATH");
		$this->setData(STATIC_PATH, "STATIC_PATH");

		if ($GLOBALS['gbl_con'] == 'admin')
		{
			$this->setData(HTTP_ROOT_WWW."core/common/skin/admin/", 'SKIN_PATH');
			$this->setData(HTTP_ROOT_WWW, 'http_root_www');
			if (!isset($page) || $page === false) $page = $GLOBALS['gbl_tpl'].$GLOBALS['gbl_act'];
		}
		else
		{
			$this->setData(HTTP_ROOT_WWW.'themes/'.STYLE, "SKIN_PATH");
			if (!isset($page) || $page === false) $page = right($GLOBALS['gbl_tpl'], 1, true);
			if (empty($page)) $page = 'index';
		}
	//	echo $this->tpl->template_dir.'/'.$page.'.htm';exit;
		if (file_exists($this->tpl->template_dir.'/'.$page.'.htm'))
			$this->tpl->display($page.".htm");
		else
			$this->sheader(null, $this->lang->system_parse_failed_template_not_exists.'<br />'.$this->lang->template_name.$this->lang->maohao.$page.'.htm');
	}

	protected function  responseFetch($page = null)
    {
    	if($this->getUserDevice()=='desktop'){
    		$this->fetch($page);
    	}else{
    		$this->fetch('mobile/'.$page);
    	}
    }

	protected function fetch ($page = null)
	{
		$this->setData(UPLOAD_PATH, "UPLOAD_PATH");
		$this->setData(STATIC_PATH, "STATIC_PATH");

		if ($GLOBALS['gbl_con'] == 'admin')
		{
			$this->setData(HTTP_ROOT_WWW."core/common/skin/admin/", 'SKIN_PATH');
			if (!isset($page) || $page === false) $page = $GLOBALS['gbl_tpl'].$GLOBALS['gbl_act'];
		}
		else
		{
			$this->setData(HTTP_ROOT_WWW.'themes/'.STYLE, "SKIN_PATH");
			if (!isset($page) || $page === false) $page = right($GLOBALS['gbl_tpl'], 1, true);
			if (empty($page)) $page = 'index';
		}
		if (file_exists($this->tpl->template_dir.'/'.$page.'.htm'))
			return $this->tpl->fetch($page.".htm");
		else
			$this->sheader(null, $this->lang->system_parse_failed_template_not_exists.'<br />'.$this->lang->template_name.$this->lang->maohao.$page.'.htm');
	}


	protected function sheader ($url = null, $msg = null, $time = 3000)
	{
		if (!isset($msg)) $time = 0;
		if (!isset($url)) $url = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : '';

		ob_end_clean();
		if ($time > 0 || empty($url)) header("Location: ".HTTP_ROOT."?con=admin&ctl=common/warning&msg=".urlencode($msg)."&url=".urlencode($url));
		else header("Location: $url");
		exit;
	}

	protected function parseUrl( $url = null ) {
		if ( empty( $url ) ) {
			$url = 'current';
		}
		if ( ! isset( $this->url_parses[$url] ) ) {
			$this->url_parses[$url] = new ParseURL( $url == 'current' ? null : $url );
		}
		return clone $this->url_parses[$url];
	}

	protected function validate( $data ) {
		require_once( CORE_DIR.'v2.1/Validate.php' );
		$validate = new Validate( $this->validates, $data );
		if ( ! $validate->valid()  ) {
			$this->sheader( null, $validate->getValidateErrors() );
		}
	}

	

	protected function loadModel ($mdl_name)
	{
		if ($mdl_info = getModel($mdl_name))
		{
			if (file_exists(CORE_DIR."model/".$mdl_info['path']))
			{
				include_once CORE_DIR."model/mdl.base.php";
				include_once CORE_DIR."model/".$mdl_info['path'];
				$mdl = new $mdl_info['classname']($this);
				return $mdl;
			}
			else die($this->lang->data_model_not_found.$mdl_name);
		} else return null;
	}

	public function loadConf ($cfg_name)
	{
		$cfg_path = DATA_DIR."conf/$cfg_name";
		if (file_exists($cfg_path))
		{
			return $this->file->readfile($cfg_path);
		}
		else return null;
	}

	public function saveConf ($cfg_name, $content)
	{
		$cfg_path = DATA_DIR."conf/$cfg_name";
		return $this->file->createfile($cfg_path, $content);
	}

	protected function chkfile ($filename)
	{
		return file_exists($filename);
	}

	protected function httpService ($url, $data = array(), $abort = false)
	{
		include_once CORE_DIR."include/class.httpService.php";
		return new httpService($url, $data, $abort);
	}

	protected function gettime ($style = 'Ymd')
	{
		return date($style, time());
	}

	public function createRnd ($length = 6)
	{
		$rnd = '';
		while (strlen($rnd) < $length)
		{
			$rnd .= rand();
		}
		if (strlen($rnd) > $length) $rnd = left($rnd, $length);
		return $rnd;
	}

	protected function download ($url, $filename)  //下载远程文件
	{
		include_once CORE_DIR."include/class.httpService.php";
		$httpService = new httpService($url);
		$this->file->createfile($filename, $httpService->result());
		if (abs(filesize($filename))) self::downloadByFopen($url, $filename);
	}
	
	protected function downloadByFopen ($url, $filename)  //使用fopen下载远程文件
	{
		$file = @fopen($url, 'rb');

		if ($file)
		{
			$new = fopen($filename, 'wb');
			if ($filename)
			{
				while (!feof($file))
				{
					fwrite($new, fread($file, 1024 * 8), 1024 * 8);
				}
				fclose($new);
			}
			fclose($file);
		}
	}

	protected function md5 ($str)
	{
		return md5($GLOBALS['KEY_'].$str.$GLOBALS['_KEY']);
	}

	protected function get ($key, $data)
	{
		$_GET[$key] = $data;
	}

	protected function post ($key, $data)
	{
		$_POST[$key] = $data;
	}

	protected function request ($key, $data)
	{
		$_REQUEST[$key] = $data;
	}

	protected function session ($key, $data)
	{
		$_SESSION[$key] = $data;
	}

	/**
	 * 确保系统中Class id 为三位数据递增
	 */
	public function chkClass ($class_id)
	{
		if (!preg_match('/^[0-9]+$/', $class_id) || strlen($class_id) < $this->classlen)
			return null;

		if (($len = strlen($class_id) % $this->classlen) != 0)
		{
			return right($class_id, $len, true);
		}

		return $class_id;
	}

	protected function dump ($array)
	{
		echo '<pre>';
		print_r($array);
		echo '</pre>';
	}

	/**
	 * 增强版 array_splice
	 * Index can be number or string
	 */
	protected function array_splice ($array, $index, $len = 0)
	{
		if (!is_array($array)) return array();

		if (is_numeric($index)) return array_splice($array, $index, $len);

		$i = 0;
		foreach ($array as $key=>$value)
		{
			if ($key == $index) array_splice($array, $i, 1);
			$i++;
		}

		return $array;
	}

	
	//**********************
	//Pool related method
	//***********************

	protected  function cancel_customer_coupon($user_type,$orderId){
		$mdl_coupon=$this->loadModel('wj_customer_coupon');
		$mdl_order = $this->loadModel('order');
		$mdl_user =$this->loadModel('user');
		$mdl_wj_user_coupon_activity_log =$this->loadModel('wj_user_coupon_activity_log');

		/**
		 * 取消流程基于订单ID
		 */
		$order = $mdl_order->getByOrderId($orderId);
		
		
		//判断如果该订单是多次核对订单，，且已经使用过时，无法canell
	//	var_dump($order['multi_use'].' 使用了'.$order['multi_used']);exit;
		if($order['multi_use']>1 && $order['multi_used']>0) {
			$this->sheader(null,'该订单是可多次(共'.$order['multi_use'].'次)使用的订单，用户已经使用了'.$order['multi_used'].'次，无法取消订单！');
			
		}
		
		
		if ($order['coupon_status'] !='c01' &&  $order['coupon_status'] !='b01') {
				$this->sheader(null,'只能取消 c01 或 b01 订单');
		}
		
		/**
		 * 订单取消时，其所有相关账款 同时取消
		 * Issue： 已经结算过的账款记录，取消时不能 VOID。需要新加一条退款记录
		 */
		$mdl_recharge=$this->loadModel('recharge');
		$mdl_recharge->updataTransactionStatus($orderId,BalanceProcess::VOID);
		

		// 如果该订单状态为客户购买状态, 如果用户已支付,则将交易金额返回到用户钱包
		if ($order['coupon_status']=='c01' && $order['status']==1 ) {
			
			// 检查如果用户订单中如果含有小面5.8 则进行补偿行动.
			$sql = "select * from cc_wj_customer_coupon where order_id ='".$orderId."'";
			$order_list =$mdl_coupon->getListBySql($sql);
			
		
			
			$total_refund =$order['money'] ;
			$note ='退款至钱包';
			
			$data_return_wallet =array(
			   'orderId' =>$orderId,
			   'userId' =>$order['userId'],
			   'money'=>$total_refund,
			   'payment'=>'recharge',
			   'createTime'=>time(),
			   'createIp'=>ip(),
			   'status'=>'1',
			   'paytime'=>time(),
			   'coupon_name'=>$note				
			);
			$mdl_recharge->insert($data_return_wallet);
		 }
		
		
		$sql ="select * from #@_wj_customer_coupon where order_id ='".$orderId."'";
		$list = $mdl_coupon->getListBySql($sql);


		$mdl_show_seats =$this->loadModel('wj_show_seats');

		foreach ( $list as $key => $val ) {
			// 团购 ，演出或电影票，需要将占用的票和座位恢复
			if($list[$key]['bonus_type']=='10' ) {
				$data_show =array(
						'sold'=>'0'
				);
				$mdl_show_seats->update($data_show,$list[$key]['related_id']);
			}
		}
		

		$mdl_coupon->begin();
		

		if ($user_type =='cancelByCustomer') {
			$mdl_wj_user_coupon_activity_log
			->orderId($orderId)
			->userId($this->loginUser['id'])
			->userName($mdl_user->getUserDisplayName($this->loginUser['id']))
			->actionId('d01')
			->log();	

		}elseif ($user_type =='cancelByBusiness'){
			$mdl_wj_user_coupon_activity_log
			->orderId($orderId)
			->userId($this->loginUser['id'])
			->userName($mdl_user->getBusinessDisplayName($this->loginUser['id']))
			->actionId('d01')
			->log();	
		}elseif ($user_type =='cancelBySystem'){
			$mdl_wj_user_coupon_activity_log
			->orderId($orderId)
			->userId(0)
			->userName('系统管理员')
			->actionId('d01')
			->log();	
		}
		
		//new mail system
		$this->loadModel('system_notification_center')->notify(SystemNotification::CancelOrder,$orderId);
		
		// 修改客户产品状态字
		$where1 =array('orderId'=>$orderId);
		$where2 =array('order_id'=>$orderId);
		
		$cn_new_status = $this->loadModel('coupons')->actionlist_info( 'd01');

		$mdl_order->updatebyWhere( array( 'coupon_status' =>'d01',
											'cn_coupon_status_name' =>$cn_new_status,
											'en_coupon_status_name' => $cn_new_status
		),$where1);

		$mdl_coupon->updateByWhere( array( 'coupon_status' =>'d01',
											'cn_coupon_status_name' =>$cn_new_status,
											'en_coupon_status_name' => $cn_new_status
		),$where2);
		
		
		
		if ( $mdl_coupon->errno() ) {
			$mdl_coupon->rollback();
			return false;
		}else {
			$mdl_coupon->commit();

            //更新xero

            $this->auto_send_invoice_to_xero($order['id'],$order['business_userId'],'update');

			return true;
		}
		
	}



	 // 购买voucher流程
  protected  function buy_voucher($arr_post,$orderId){
//	  $this->cookie->setCookie( 'store_display_bid', '197639', 60 * 60 * 24 * 365);
	   $bid = $this->cookie->getCookie('store_display_bid');
	
  	$mdl_user = $this->loadModel( 'user' );
  	
  	$mdl_coupons = $this->loadModel('coupons');
  	$mdl_coupons_sub = $this->loadModel( 'coupons_sub' );

  	$mdl_order = $this->loadModel( 'order' );
  	$mdl_wj_customer_coupon = $this->loadModel( 'wj_customer_coupon' );

  	$mdl_wj_user_coupon_activity_log =$this->loadModel('wj_user_coupon_activity_log');//Ubonus券相关活动日志表 wj_user_conpon_activity_log
  	
  	$cn_action_name = $mdl_coupons->actionlist_info('c01');
	$en_action_name =$cn_action_name;


  	$redeem_code =$this->createRnd(20);
  	$couponCreator = $mdl_user->getUserById($arr_post['business_userId']);
  	$couponBuyer = $mdl_user->getUserById($arr_post['userId']);
  		
    $mdl_restaurant_menu = $this->loadModel('restaurant_menu');
	$mdl_recharge=$this->loadModel('recharge');
    $mdl_user_factory_menu_price = $this->loadModel('user_factory_menu_price');

	$mdl_referral_relation=$this->loadModel('referral_relation');

	$mdl_referrals = $this->loadModel('referrals');

    $mdl_temp_carts =$this->loadModel('wj_user_temp_carts');

	
    $mdl_restaurant_menu = $this->loadModel('restaurant_menu');

    $mdl_restaurant_sidedish_menu = $this->loadModel('restaurant_sidedish_menu');
	
	$total_ubonus_commission = 0;
	//var_dump($arr_post);exit;

  	    //开始事务处理
  	    $mdl_order->begin();

  	    foreach ( $arr_post['ids'] as $key => $val ) {
  	    	
			$customer_buy_quantities= $arr_post['quantities'][$key];
  	    	if($customer_buy_quantities==0){continue;}
	  
	  	    //获取当前的cc_coupons的表信息,不管是子单还是主单，都要先找主单，然后修改一些相关的数据即可
	  	    // 相关的数据包括 标题，价格等等。
	  	    $coupon = $mdl_coupons->get( $arr_post['ids'][$key]);
  	    	//var_dump($arr_post['ids'][$key]);exit;
			// 如果coupon 的 EvoucherOrrealproduct 值为 restaurant_menu ,表示当前的产品为线上点餐的菜单
			if($coupon['EvoucherOrrealproduct']=='restaurant_menu') {
				$restaurant_menu =1;
				
			}
			
  			//检查如果是子卡，则取子卡数据
  			if($arr_post['sub_or_main'][$key]=='s'){
				$subCoupon = $mdl_coupons_sub->get( $arr_post['sub_ids'][$key] );
			}else{
			  	$subCoupon='';
			}

			//准备Ubonus卷数据
			
			// 获得business_id 如果是 餐馆或生鲜  ，并通过menu_id 找到对应的商家，以替代coupon的商家。
			
			
			$data = $this->get_customer_coupon_data($coupon,$customer_buy_quantities,$couponBuyer,$arr_post['menu_id'][$key]);

            $temp_message = $mdl_temp_carts->get($arr_post['temp_id'][$key]);
            $data['message'] =substr($temp_message['item_message'],0,95) ;
           // $this->form_response_msg('something wrong! '.$data['message']);
			//把之前生成的redeemcode置入
			$data['redeem_code'] =$redeem_code ;
			$data['order_id']=$orderId;
			// 插入订单的规格细节参数、当为门票的时候，插入的是门票的id号码
			$data['voucher_original_amount']=$arr_post['original_amount'][$key];
			$data['voucher_deal_amount']=$arr_post['sub_money'][$key];
			
			


				  $data['guige_des']=$arr_post['guige_des'][$key];
                  $data['guige1_id']=$arr_post['guige_ids'][$key];

			//新增子卡ID
		  	if($subCoupon){
		  		$data['sub_bouns_id_code'] = (int)$subCoupon['id'];  //如果没有子卡，则等于0
		  	}else{
		  		$data['sub_bouns_id_code']='0';
		  	}
  
  			$data['business_staff_userid']=$arr_post['business_staff_id'];

			
		  	if($restaurant_menu) {
			    $menu=$mdl_restaurant_menu->get($arr_post['menu_id'][$key]);
			    $sidedish_menu=$mdl_restaurant_sidedish_menu->get($arr_post['sidedish_menu_id'][$key]);

			    if($sidedish_menu){
			    	$data['bonus_title'] = $menu['menu_cn_name'].' 的配菜：'.$sidedish_menu['menu_cn_name'];
			    }else{
			    	$data['bonus_title'] = $menu['menu_cn_name'];
			    }
				$data['menu_id'] = $menu['menu_id'];
                $data['include_gst'] = $menu['include_gst'];
                $data['restaurant_menu_id'] = $menu['id'];
                $data['voucher_original_amount'] = $arr_post['original_amount'][$key];
                $data['commission_free'] = $arr_post['commission_free'][$key];

                $menuCreator = $mdl_user->getUserById($menu['restaurant_id']);
                if($menuCreator['business_type_factory'] == 1) {
                    $mdl_user_factory_menu_price->insertOrUpdateUserFactoryPrice($couponBuyer['id'], $menu['id'], $data['voucher_deal_amount']);
                }
			}else{
				if($arr_post['sub_or_main'][$key]=='s'){
					$data['bonus_title'] = $subCoupon['title'];
		  		}
                $data['include_gst'] = $coupon['include_gst'];
			}
  	
	

			
		    $data['adjust_subtotal_amount']=$data['customer_buying_quantity']*$data['voucher_deal_amount'];
		
            // 下面判断如果购买具体数据的商家编号和订单的商家编号如果不同的话，则表明，这个订单是统配中心的订单，即该订单包括多个供应商信息，因此，需要向结算系统传递给予各个供应商分项的汇总金额信息和commsion
    		if($data['business_id']<>$arr_post['business_userId']){
				//var_dump($data['business_id'].' ' .$arr_post['business_userId'] );
				$uni_dispaitching =1 ;// 表示目前为统配订单；
			}
			
	
  			// 开始写入数据
  
  			$mdl_wj_customer_coupon->insert( $data );
			







			  	if($arr_post['sub_or_main'][$key]=='s'){
			  		$mdl_coupons_sub->updateBuy( $coupon['id'],$arr_post['sub_ids'][$key] ,$customer_buy_quantities);
			  	}else {
			  		$mdl_coupons->updateBuy( $coupon['id'],$customer_buy_quantities);
			  	}
				
				if($coupon['EvoucherOrrealproduct']=='restaurant_menu') {
					$menu_rec =$mdl_restaurant_menu->get($arr_post['menu_id'][$key]);
					//var_dump($menu_rec);exit;
					if($menu_rec) {
						$menu_data=array(
						'qty'=>$menu_rec['qty']-$arr_post['quantities'][$key]
						);
					    $mdl_restaurant_menu->update($menu_data,$menu_rec['id']);
					
						
						
					}
					
				}
				
				
				





  			

		  	$ubonus_commission = ($data['voucher_deal_amount'] * $coupon['platform_commission_rate'] + $coupon['platform_commission_base']) *$customer_buy_quantities;

		  	$total_ubonus_commission += $ubonus_commission;
			

  		}
		
		$balanceProcess = new BalanceProcess($couponBuyer['id'],$arr_post['business_userId'],$orderId);
     

	//如果为统配中心 ，传递一下参数到账务处理模块
		if($uni_dispaitching ){
			$sql ="SELECT `business_id`,sum(`voucher_deal_amount`*`customer_buying_quantity`) as subtotal,
sum((`voucher_deal_amount`*`platform_commission_rate`+`platform_commission_base`)*`customer_buying_quantity`) as sub_commission
 FROM `cc_wj_customer_coupon` WHERE order_id =$orderId group by business_id";
			$sub_business_total = $mdl_wj_customer_coupon->getListBySql($sql);
			
			// 计算平台累计surcharge ,并按比例分配刀每个商家 $arr_post['surcharge'],$arr_post['money']


			// 将数据发送到结算端进行处理
			//var_dump($sub_business_total);exit;

			if($sub_business_total) {
				     $balanceProcess->setSubBusinessTranscationAmount($sub_business_total);

			}
		}
		
       
		

		$balanceProcess->setTransactionAmount($arr_post['money']);

		$balanceProcess->useBalancePay($arr_post['confirmedMoneyAppliedAmount']);

		//if use global code. Ubonus Pay business the discount amount.
		if($this->loadModel('wj_promotion_code')->isGlobalPromotionCodeById($arr_post['promotion_id']))
		$balanceProcess->useGlobalPromotionCode($arr_post['promotion_total']);

		$balanceProcess->setPlatformFee($arr_post['booking_fees']);

		$balanceProcess->setPlatformTotalCommission($total_ubonus_commission);

		// 根据该产品代理商与平台的分润比率计算 平台应付给运营商的commission 
            // 获得该产品对应商家的对应代理商的分润百分比.
			
			$businessUser =$mdl_user->get($coupon['createUserId']);
			if($businessUser) {
				$agentUser=$mdl_user->get($businessUser['user_belong_to_agent']);
				if($agentUser) {
					$agentCommissionRate =$agentUser['agent_commission_rate'];
					//设置运营商编号
					$balanceProcess->setAgent($agentUser['id']);
				}else{

					$agentCommissionRate =0;
				}

			}else{
				$agentCommissionRate =0;
			}

			$agentTotalCommission =$total_ubonus_commission*$agentCommissionRate;


			// 计算平台应该支付给运营商的分润结束

		// 设置平台要付给运营商的佣金

		$balanceProcess->setAgentTotalCommission($agentTotalCommission);
			
	    if($uni_dispaitching ){
			// 对于统配商家 有以下规则 : 1) surcharge 全部是 用户付给平台 2)delivery fee 全部是平台收  3) 
			if($arr_post['payment']=='paypal'){
				$surchargerate = $couponCreator['paypalsurcharge'];
			}elseif($arr_post['payment']=='royalpay'||$arr_post['payment']=='alipay'){
				$surchargerate = $couponCreator['royalpaysurcharge'];
				
			}elseif($arr_post['payment']=='creditcard'){
				$surchargerate = $couponCreator['royalpaysurcharge'];
				
			}
		
			foreach ($sub_business_total as $key => $value) { 
			   $sub_business_total[$key]['surchargefee']=$value['subtotal'] *$surchargerate;
			   $balanceProcess->setSubBusinessTranscationAmount($sub_business_total); //将该surchargefee 存入刀账户处理中。。。
			  }
			  
			 $balanceProcess->initTransactionSurcharge($surchargerate,BalanceProcess::USER,BalanceProcess::PLATFORM);
			 $balanceProcess->initDeliverFee($arr_post['delivery_fees'],BalanceProcess::USER,BalanceProcess::PLATFORM);
			
		}else{
			
				if($arr_post['payment']=='paypal'){
			if($this->loadModel('wj_busi_pay_setting_application')->isPaymentSelfManage($arr_post['business_userId'])){

				switch ($couponCreator['transactionFeeChargeFrom_paypal']) {
					case BalanceProcess::USER:
						$balanceProcess->initTransactionSurcharge($couponCreator['paypalsurcharge'],BalanceProcess::USER,BalanceProcess::BUSINESS);
						break;
					case BalanceProcess::BUSINESS:
						$balanceProcess->initTransactionSurcharge(DEFAULT_PAYPAL_SURCHARGE,BalanceProcess::BUSINESS,BalanceProcess::BUSINESS);
						break;
					case BalanceProcess::PLATFORM:
						$balanceProcess->initTransactionSurcharge(DEFAULT_PAYPAL_SURCHARGE,BalanceProcess::PLATFORM,BalanceProcess::BUSINESS);
						break;
					default:
						# code...
						break;
				}
				
				$balanceProcess->initDeliverFee($arr_post['delivery_fees'],BalanceProcess::USER,BalanceProcess::BUSINESS);
			}else{ //付给平台

				switch ($couponCreator['transactionFeeChargeFrom_paypal']) {
					case BalanceProcess::USER:
						$balanceProcess->initTransactionSurcharge($couponCreator['paypalsurcharge'],BalanceProcess::USER,BalanceProcess::PLATFORM);
						break;
					case BalanceProcess::BUSINESS:
						$balanceProcess->initTransactionSurcharge(DEFAULT_PAYPAL_SURCHARGE,BalanceProcess::BUSINESS,BalanceProcess::PLATFORM);
						break;
					case BalanceProcess::PLATFORM:
						$balanceProcess->initTransactionSurcharge(DEFAULT_PAYPAL_SURCHARGE,BalanceProcess::PLATFORM,BalanceProcess::PLATFORM);
						break;
					default:
						# code...
						break;
				}

				$balanceProcess->initDeliverFee($arr_post['delivery_fees'],BalanceProcess::USER,BalanceProcess::PLATFORM);
			}
		}elseif($arr_post['payment']=='royalpay'||$arr_post['payment']=='alipay'){

			switch ($couponCreator['transactionFeeChargeFrom_royalpay']) {
				case BalanceProcess::USER:
					$balanceProcess->initTransactionSurcharge($couponCreator['royalpaysurcharge'],BalanceProcess::USER,BalanceProcess::PLATFORM);
					break;
				case BalanceProcess::BUSINESS:
					$balanceProcess->initTransactionSurcharge(DEFAULT_ROYALPAY_SURCHARGE,BalanceProcess::BUSINESS,BalanceProcess::PLATFORM);
					break;
				case BalanceProcess::PLATFORM:
					$balanceProcess->initTransactionSurcharge(DEFAULT_ROYALPAY_SURCHARGE,BalanceProcess::PLATFORM,BalanceProcess::PLATFORM);
					break;
				default:
					# code...
					break;
			}

			$balanceProcess->initDeliverFee($arr_post['delivery_fees'],BalanceProcess::USER,BalanceProcess::PLATFORM);

		}elseif($arr_post['payment']=='creditcard'){

			switch ($couponCreator['transactionFeeChargeFrom_creditcard']) {
				case BalanceProcess::USER:
					$balanceProcess->initTransactionSurcharge($couponCreator['creditcardsurcharge'],BalanceProcess::USER,BalanceProcess::PLATFORM);
					break;
				case BalanceProcess::BUSINESS:
					$balanceProcess->initTransactionSurcharge(DEFAULT_CREDITCARD_SURCHARGE,BalanceProcess::BUSINESS,BalanceProcess::PLATFORM);
					break;
				case BalanceProcess::PLATFORM:
					$balanceProcess->initTransactionSurcharge(DEFAULT_CREDITCARD_SURCHARGE,BalanceProcess::PLATFORM,BalanceProcess::PLATFORM);
					break;
				default:
					# code...
					break;
			}

			$balanceProcess->initDeliverFee($arr_post['delivery_fees'],BalanceProcess::USER,BalanceProcess::PLATFORM);
		}
		
			
		}

	
		
		

		$balanceProcess->process();


		if($arr_post['payment']=='offline'){
			//settle the transaction on redeem.
		}else{
			//settle the transaction on redeem as well.
			//$mdl_recharge->updataTransactionStatus($orderId,BalanceProcess::SETTLE);
		}

		//更新折扣码使用次数
		$this->loadModel('wj_promotion_code')->updateAppliedCount($arr_post['promotion_id'],1);






			$order_names=$mdl_order->generateOrderName($orderId,$this->lang);
			$logistic_suppliers_info=$mdl_order->gen_logistic_suppliers_info($orderId,$this->lang);
			

        $approve_user = $this->loadModel('user_factory')->isUserApproved($couponBuyer['id'],$arr_post['business_userId']);
        if($approve_user) {
            $statusOfOrder = 1;
        }else{
           $statusOfOrder =0;
       }
		
	//var_dump($logistic_suppliers_info);exit;


		$data_order = array(
			'orderId'                     => $orderId,
			'order_name'                  => $order_names,
			'logistic_suppliers_info'     => $logistic_suppliers_info['logistic_suppliers_info'],
			'logistic_suppliers_count'     => $logistic_suppliers_info['logistic_suppliers_count'],
			'userId'                      => $couponBuyer['id'],
			'money'                       => $arr_post['money'],
			'money_new'                    => $arr_post['money'],
			'promotion_total'             => $arr_post['promotion_total'],
			'promotion_id'                => $arr_post['promotion_id'],
			'payment'                     => $arr_post['payment'] ,
			'status'                      => 0,
			'createTime'                  => time(),
			'createIp'                    => ip(),
			'business_userId'             => $arr_post['business_userId'],
			'business_staff_id'           => $arr_post['business_staff_id'],
			'coupon_status'               => 'c01',
			'cn_coupon_status_name'       => $cn_action_name,
			'en_coupon_status_name'       => $en_action_name,
			'redeem_code'                 => $redeem_code,
			'customer_delivery_option'    => $arr_post['customer_delivery_option'], 
			'delivery_fees'               => $arr_post['delivery_fees'],
			'booking_fees'                => $arr_post['booking_fees'],
            'displayName'                  => $arr_post['displayName'],
			'first_name'                  => $arr_post['first_name'],
			'last_name'                   => $arr_post['last_name'],
			'phone'                       => $arr_post['phone'],
			'address'                     => $arr_post['address'],

			'house_number'                => $arr_post['house_number'],
			'street'                  	  => $arr_post['street'],
			'city'                 		  => $arr_post['city'],
			'state'						  => $arr_post['state'],
			'country'                     => $arr_post['country'],
			'postalcode'                  => $arr_post['postalcode'],

			'email'                       => $arr_post['email'],
			'id_number'                   => $arr_post['id_number'],
			'message_to_business'         => $arr_post['message_to_business'],
			'confirmedMoneyAppliedAmount' => $arr_post['confirmedMoneyAppliedAmount'],
			'surcharge'                   => $arr_post['surcharge'],
			'surcharge_new'                => $arr_post['surcharge'],
			'multi_use'                   => $arr_post['multi_use'],
            'accountPay'                   => $statusOfOrder

			
		);

		//统一配送时间处理
		$dispCenterUserSelectedDeliveryDate = $arr_post['dispCenterUserSelectedDeliveryDate'];
		if ($dispCenterUserSelectedDeliveryDate) {
			$parts = explode("@", $dispCenterUserSelectedDeliveryDate);
			if (count($parts) == 2) {
				$dateTimestamp = $parts[0];
				$timeType = $parts[1];
				$data_order['logistic_delivery_date']=$dateTimestamp;
				$data_order['logistic_delivery_time_type']=$timeType;

                $seq_number = $mdl_order->generateLogisticSequence($arr_post['business_userId'],$dateTimestamp);
                $data_order['logistic_sequence_No']=$seq_number;

			}
		}

       $new_id = $mdl_order->insert( $data_order );
		if(!$new_id){

			$rollback=1;
			$tablename='order';
		}else{ //更新呢总箱数
            $this->loadModel('boxNumberOutput')->UpdateOrderBoxInfo($orderId);

        }

        //如果新客户第一次购买，之前没有填过用户的基本信息，系统挂自动给补填一下；



      //补填供应商客户表
      $mdl_user_factory = $this->loadModel('user_factory');
      $user_factory_rec = $mdl_user_factory->getUserCodeandName($couponBuyer['id'],$coupon['createUserId']);
      if(!$user_factory_rec) {
          $mdl_user_factory->addCustomerInfo($couponBuyer['id'],$coupon['createUserId'],$data_order);
      }
     // 如果用户表中没有数据则补充一下用户表的数据，包括 姓名，trading name
      $mdl_user = $this->loadModel('user');
      $mdl_user->updatenewCustomerInfo($data_order);






		$mdl_wj_user_coupon_activity_log
			->orderId($orderId)
			->userId($couponBuyer['id'])
			->userName($mdl_user->getUserDisplayName($couponBuyer['id']))
			->actionId('c01')
			->log();	

		if($rollback){
			$mdl_order->rollback();
			$this->form_response_msg('something wrong! '.$tablename);
		}else{
			$mdl_order->commit();

            $this->auto_send_invoice_to_xero($new_id,$arr_post['business_userId'],'create');


        }
  
  }

  public function auto_send_invoice_to_xero($new_id,$factory_id,$createOrUpdate,$secondId){
      $supplier_rec =$this->loadModel('supplier')->getByWhere(array('userId'=>$factory_id));
      if($supplier_rec['xero_auto_pass'] ==1){
          $order_rec =$this->loadModel('order')->get($new_id);
          $user_factory_rec =$this->loadModel('user_factory')->getByWhere(array('factory_id'=>$factory_id,'user_id'=>$order_rec['userId']));
          if($user_factory_rec['to_xero']==1){
              $this->xero_send_invoice($new_id,$createOrUpdate,0,$secondId);
          }

      }

  }

    public function xero_invoice_manage($action,$id){


        switch ($action) {
            case 'craete':
                $this->xero_send_invoice($id);
                break;
            case 'update':
                $this->xero_send_invoice($id,'update');
                break;
            case 'void':
                $nav_page ="dispatching/index";
                break;

            default:
                break;


        }

    }

    public function xero_send_invoice($id,$createOrUpdate,$returnresult=1,$secondId){

        $mdl= $this->loadModel('order');

        $order_info = $mdl->get($id);

        //如果中间xero信息被重置，则修改未创建模式；
        if(strlen($order_info['xero_id'])<2  && $order_info['sent_to_xero']==0) {
            $createOrUpdate='create';
        }


        if ($id < 0 || ($order_info['business_userId']!=$this->current_business['id'] && $order_info['userId']!=$this->loginUser['id'])) $this->form_response_msg(' no access !');

        //检查该商家是否可以管理其它店铺，如果授权即可以该商家权限进入系统。

        // require_once DOC_DIR.'core/b2b_2_0/b2b/lib/Credentials_ubonus100mtest_latest.php';
        // require_once DOC_DIR.'core/b2b_2_0/b2b/lib/Credentials.php';
        require_once DOC_DIR.'core/b2b_2_0/b2b/lib/Database.php';
        require_once DOC_DIR.'core/b2b_2_0/b2b/lib/MyApi001.php';

        $api = new MyApi($db);
        $mdl_xero =$this->loadModel('xero') ;
        $mdl_tokens =$this->loadModel('tokens') ;
        $credentials =$mdl_tokens->getCredentials($order_info['business_userId'],'xero') ;

        if(!$credentials) {
            echo json_encode(array('error' => 'please refresh the page or login in again!'));
        }

        $orderId =$order_info['orderId'];
        $order_data = $mdl_xero->getOrderInvoiceData($orderId,$createOrUpdate);
      //  var_dump($order_data);exit;
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
            $order_info=$mdl->get($order_info['id']);
            $data['xero_invoice_id']=$order_info['xero_invoice_id'];

            if($createOrUpdate!='update'){
                $data['sent_to_xero'] = ($order_info['sent_to_xero'] == '0') ? '1' : '0';

                if ($mdl->update($data, $order_info['id'])) {
                    if($returnresult){
                        echo json_encode($data);
                    }

                } else {
                    $this->form_response_msg('Please try again later');
                }
            }else{
                $data['sent_to_xero'] =1;

                if($secondId){

                    $order_info = $mdl->get($secondId);
                    $orderId =$order_info['orderId'];
                    $order_data = $mdl_xero->getOrderInvoiceData($orderId,$createOrUpdate);
                    $response_arr = $api->updateInvoice($credentials,$order_data);
                }

                if($returnresult) {
                    echo json_encode($data);
                }

            }

        }


    }


    public function xero_send_invoice_action(){

        $id = (int)get2('id');
        $createOrUpdate =trim(get2('createOrUpdate'));

        $this->xero_send_invoice($id,$createOrUpdate);





    }
  
 protected  function get_customer_coupon_data($coupon,$qty,$couponBuyer,$menu_idd){

 	$cn_action_name = $this->loadModel('coupons')->actionlist_info( 'c01');
  	$en_action_name =$cn_action_name;
	
	//表示获得到了菜单编号，也就是说是生鲜或者餐厅类，这个时候，用这个menu_id 寻找business_id ，得到正确的商家信息
	if ($menu_idd) {
		$mdl_restaurant_menu = $this->loadModel('restaurant_menu');
		$menu_rec = $mdl_restaurant_menu->get($menu_idd);
        if ($menu_rec) {
			$user_idd = $menu_rec['restaurant_id'];
			
		}		
	}else {
		$user_idd =$coupon['createUserId'];
		
	}
	

  	$couponCreator=$this->loadModel('user')->getUserById($user_idd);
  
  	$mdl_coupon_type = $this->loadModel('coupon_type');
  	$coupon_type_name = $mdl_coupon_type->get($coupon['bonusType']);
	if($this->getLangStr()=='en'){
		if($coupon['title_en']){
		$coupon['title']=$coupon['title_en'];
	}
	}
  	$data = array(
  			'gen_date' => time(),
  			'userId' => $couponBuyer['id'],
  			'customer_firstname'  =>$couponBuyer['person_first_name'],  
  			'customer_lastname' =>$couponBuyer['person_last_name'],
  			'customer_name'  =>$couponBuyer['nickname'],
  			'customer_area'  =>$couponBuyer['cityId'],
  
  			//客户申领产品
  			'coupon_status'	=>'c01',
  			'cn_coupon_status_name' =>$cn_action_name,
  			'en_coupon_status_name' =>$en_action_name,
  			'bonus_id' => $coupon['id'],
  			'bonus_title' =>$coupon['title'],
  			'bonus_type'=>$coupon['bonusType'],
  			'bonus_type_name'=>$coupon_type_name['name'],
  			'categoryId'=>$coupon['categoryId'],

  			'expire_date' =>$coupon['startTime']+ $coupon['expiredDay']*24*60*60,
  			'business_id' => $user_idd,
  			'business_name'	=>$this->loadModel('user')->getBusinessDisplayName($couponCreator['id']),
  			'business_area'=>$couponCreator['cityId'],
  			'is_visible_for_business'=>$coupon['visibleForBusiness'],
  			'strip_code'=>$coupon['stripCode'],
  
  			'customer_buying_quantity'=>$qty,
            'new_customer_buying_quantity'=>$qty,

  			'platform_commission_base'=>$coupon['platform_commission_base'],
  			'platform_commission_rate'=>$coupon['platform_commission_rate'],
			
  
  	);
  	return $data;
  
  }
  
  
  /**
     * 导出Excel数据表格
     * @param  array    $dataList     要导出的数组格式的数据
     * @param  array    $headList     导出的Excel数据第一列表头
     * @param  string   $fileName     输出Excel表格文件名
     * @param  string   $exportUrl    直接输出到浏览器or输出到指定路径文件下
     * @return bool|false|string
     */
    public static function toExcel($dataList,$headList,$fileName,$exportUrl){
        //set_time_limit(0);//防止超时
        //ini_set("memory_limit", "512M");//防止内存溢出
       header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header('Content-Disposition: attachment;filename="'.$fileName.'.xls"');
        header('Cache-Control: max-age=0');
        //打开PHP文件句柄,php://output 表示直接输出到浏览器,$exportUrl表示输出到指定路径文件下
      


	  $fp = fopen($exportUrl, 'a');
        ob_clean();
	  fwrite($fp, chr(0xEF).chr(0xBB).chr(0xBF)); // 添加 BOM
		//fwrite($fp,chr(0xEF).chr(0xBB).chr(0xBF));
        //输出Excel列名信息
        foreach ($headList as $key => $value) {
            //CSV的Excel支持GBK编码，一定要转换，否则乱码
           // $headList[$key] = iconv('utf-8', 'GBK//IGNORE', $value);
			$headList[$key]=htmlentities($value,ENT_NOQUOTES, 'utf-8');
        }
//var_dump($headList[$key]);exit;
        //将数据通过fputcsv写到文件句柄
        fputcsv($fp, $headList);

        //计数器
        $num = 0;

        //每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
        $limit = 100000;

        //逐行取出数据，不浪费内存
        $count = count($dataList);
        for ($i = 0; $i < $count; $i++) {

            $num++;

            //刷新一下输出buffer，防止由于数据过多造成问题
            if ($limit == $num) {
                ob_flush();
                flush();
                $num = 0;
            }

            $row = $dataList[$i];
			
            foreach ($row as $key => $value) {
                $row[$key] = htmlentities($value,ENT_NOQUOTES, 'utf-8');
            }
			
            fputcsv($fp, $row);
        }
        return $fileName;
    }
	
	
	// 根据订单修补原因号，获得对应提示
	public function get_order_amend_reason_type_desc($reason_type) {
		$mdl_order_amend_reson_type=$this->loadModel('order_amend_reson_type');
		$reason_type_rec = $mdl_order_amend_reson_type->get($reason_type);
		
		if (!reason_type_rec) return 0;
		
		if($this->getLangStr()=='zh-cn') { 
			return  $reason_type_rec['reson_type_cn'];
 
		}else{
	       return  $reason_type_rec['reson_type_en'];
	 
		}
		
		
		
		
	}
	
	// 获得 退货reason_type 
	public function get_order_amend_reson_type_list() {
	
			
		$mdl_order_amend_reson_type =$this->loadModel ('order_amend_reson_type');
		$order_amend_reson_type_list =$mdl_order_amend_reson_type->getList(null,null);
		//var_dump($order_amend_reson_type_list);exit;
		foreach ($order_amend_reson_type_list as $key => $val) {
			if($this->getLangStr()=='en'){
				$order_amend_reson_type_list[$key]['reason_type_desc'] =$val['reson_type_en'];
			}else{
				$order_amend_reson_type_list[$key]['reason_type_desc'] =$val['reson_type_cn'];
			}
			
		}
		return $order_amend_reson_type_list;
	}
	

	
			
}

?>