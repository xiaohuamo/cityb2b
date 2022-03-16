
<?php


class cmsPage extends corecms
{

	protected $site;
	protected $loginUser;
	protected $city; // 大城市,父类为国家
	protected $city1; //地区
	protected $payments;
	protected $paymentids;
	protected $wx_auth_code = null;
	protected $wx_openID = null;
	protected $returnUrl = '';
    protected $current_business;
    protected $trueLogin=0; //对于从登陆接口进来的登陆，且为客户类，truelogin =1 ,该标志放置有已登陆过agent的cookie留有agent强制登陆设置

	function cmsPage ()
	{
		parent::corecms();
		
        $w=get2('wholesale');
        $this->setData($w, 'wholesale' );
		
		$this->setData($this->getBusinessType(),'business_type');

        // 获取当前登陆用户的角色



		$ua =$this->getUserDevice();
	//	if( $ua=='wechat' && ($_SERVER['HTTP_HOST']!='cityb2b.com') )header("Location: https://cityb2b.com/".$_SERVER['REQUEST_URI']);
		$this->setData( $ua, 'ua' );

		$this->setData(HTTP_ROOT, 'http_root');
		$this->setData(HTTP_ROOT_WWW, 'http_root_www');
		$this->setData(HTTP_ROOT_WX, 'http_root_wx');


		/**
		 * 站点信息数据库读取
		 * @var [type]
		 */
		$mdl_site	= $this->loadModel('site');
		$this->site	= $mdl_site->get();
		$this->setData( $this->site, 'site' );

		/**
		 * 返回URL 处理
		 * A value of "application/x-www-form-urlencoded" means that your POST body will need to be URL encoded just like a GET parameter string. A value of "multipart/form-data" means that you'll be using content delimiters and NOT url encoding the content
		 */
		$getReturnUrl= trim( get2( 'returnUrl' ) );
		$postReturnUrl=urldecode(trim(post( 'returnUrl' )));
		$this->returnUrl=($getReturnUrl)?$getReturnUrl:$postReturnUrl;

		$this->setData(urlencode($this->returnUrl),'returnUrl');

		/**
		 * 用于返回当前页面
		 */
		$this->setData(urlencode( $_SERVER['REQUEST_URI']),'currentUrl');

		/**
		 * 用于需要登录的 ajax请求
		 */
		$this->setData(HTTP_ROOT_WWW.'member/login?returnUrl='.urlencode( $_SERVER['REQUEST_URI']),'loginReturnUrl');

		

		/**
		 * 扫码登录的手机端微信跳转
		 */
		$pc_wexin_scan_login =(int)get2('pcLoginId');
		if($pc_wexin_scan_login)$this->pc_scan_redirect($pc_wexin_scan_login);
		
		/**
		 * 微信自动登录
		 */
		$mdl_user = $this->loadModel('user');


		$openUser = null;


/*微信程序关闭
		$this->wx_auth_code = get2( 'code' );
		
		if ( !empty( $this->wx_auth_code ) ) {
			require_once "wx/wxjssdk.php";
			$this->wx_openID = getOpenID( $this->wx_auth_code );
			
			$this->cookie->setCookie( 'wx_openID', $this->wx_openID, 60 * 60 * 24 * 365 );
		}
		else {
			$this->wx_openID=$this->cookie->getCookie( 'wx_openID');
		}



		$multiWxLoginIgnoreList=array('multiple_wx_login','success');
		if(!in_array($GLOBALS['gbl_act'], $multiWxLoginIgnoreList) && !session( 'member_user_id')&&$this->wx_openID){
			$mutilWxBind=$mdl_user->hasMutilWxBind($this->wx_openID);
			$defaultWxBind=$mdl_user->hasDefaultWxBind($this->wx_openID);

			if($mutilWxBind&&!$defaultWxBind){
				$rurl=($this->returnUrl)?$this->returnUrl:$_SERVER['REQUEST_URI'];
				$this->sheader(HTTP_ROOT_WWW.'member/multiple_wx_login?openId='.$this->wx_openID.'&returnUrl='.urlencode($rurl));
			}elseif ($mutilWxBind&&$defaultWxBind) {
				$openUser=$defaultWxBind;
			}elseif(!$mutilWxBind){
				$where['wx_openID']=$this->wx_openID;
				$openUser = $mdl_user->getByWhere($where);
			}

			$this->loginUser = $openUser;
		}
  */
        //cookie remember login
		if ( !$this->loginUser ) {
			$userId = session( 'member_user_id' );
			$userShell = session( 'member_user_shell' );
			$remember = (int)$this->cookie->getCookie( 'remember' );
			
			if ( $userId <= 0 && $remember ) {
				$userId = (int)$this->cookie->getCookie( 'remember_user_id' );
				$userShell = $this->cookie->getCookie( 'remember_user_shell' );
			}
			if ( $userId > 0 ) {
				$this->loginUser = $mdl_user->getUserById( $userId );
				if ( ! $this->loginUser['isApproved'] ) {
					$this->loginUser = null;
				}
				if ( $userShell != $this->md5( $this->loginUser['id'].$this->loginUser['name'].$this->loginUser['password'] ) ) {
					$this->loginUser = null;
				}
			}
		}


        $nickname = $mdl_user->getUserDisplayName($this->loginUser['id']);
        $this->setData($nickname,'nickname');

		$this->setData( $this->loginUser, 'loginUser' );



		/**
		 * 城市切换
		 */
		$mdl_city = $this->loadModel( 'city' );

		

		$citylist=$mdl_city->getChildTree();

		$this->setData( $citylist, 'fullcitylist' );


		$cityid = (int)$_GET['cityid'];
		
		if ( $cityid ==530 or $cityid ==556 or $cityid ==568 ) setcookie( 'cityid', $cityid, time() + 60 * 60 ,'/');
		else $cityid = (int)$_COOKIE['cityid'];

		$this->city = $mdl_city->get( $cityid,$this->getLangStr() );
		
		if ( ! $this->city ) {
			$this->city = $mdl_city->get(556);//default melbourne
			setcookie( 'cityid', $this->city['id'], time() + 60 * 60 ,'/');
		}


		$citylist=$mdl_city->getChildTree($this->city['id']);
		if(!$citylist)$citylist=$mdl_city->getChildTree($this->city['parentId']);

		$this->setData( $citylist, 'citylist' );
		
		$this->setData( $mdl_city->get( $this->city['parentId'] ), 'cityParent' );

		$this->setData( $this->city, 'city' );
		


		/**
		 * 加载系统支付方式
		 * @var [type]
		 */
		$mdl_payment = $this->loadModel( 'payment' );
		$payments = $mdl_payment->getList( null, null, null );
		foreach ( $payments as $key => $payment ) {
			$this->payments[$payment['id']] = $payment;
			$this->payments[$payment['id']]['config'] = unserialize( $payment['config'] );
			$this->paymentids[] = $payment['id'];
		}


		// chat ID  will be overwrited in individual controler if needed 
		if($this->loginUser){
			$this->setData($this->loginUser['id'],'clientChatId');
		}else{
			$this->setData(session_id(),'clientChatId');
		}
		$this->setData('25201','businessChatId'); // Harry' Id as ubonus support
		

		/**
		 * bindWxQRCode 全局可用
		 */
		$this->setData(bindWxQRCode($this->loginUser['id']),'bindWxQRCode');


		/**
		 * 购物车数量全局可用
		 */
		$carts_count =$this->loadModel('wj_user_temp_carts')->getTotalQty($this->loginUser['id']);
		$this->setData($carts_count,'carts_count');
		
		
		// 获取购物车的商家，如果该商家是生鲜商家，则，自动调到该生鲜商家页面。。。
		$sql11 ="select a.*,b.business_type_freshfood from cc_wj_user_temp_carts a left join cc_user b on a.businessUserId =b.id  where userId = ".$this->loginUser['id']." order by id desc";
		
		$cart_business =$this->loadModel('wj_user_temp_carts')->getListBySql($sql11);
		//var_dump($sql11);exit;
		if($cart_business) {
			if($cart_business[0]['business_type_freshfood'] ==1) {
				$business_idd = $cart_business[0]['businessUserId'];
				$this->setData('?business_userid='.$business_idd.'#directCheckout','freshfood_link');
			}
			
		}

		/**
		 * 切换语言URL
		 */
		$this->setData($this->parseUrl()->set('lang'),'langSwitchUrl');
		
		
		
			 /* 下面一段是 看看这个登陆的用户是否是可以设置 团购日期的商家，如果是，则 菜单管理打开 */
		  $mdl = $this->loadModel('freshfood_disp_suppliers_schedule');
			$loginUserId = $this->loginUser['id'];
			$isSuplier = in_array($loginUserId, DispCenter::getSupplierList());
			
			$isDispCenter =in_array($loginUserId, DispCenter::getDispCenterList());
			
			 if($isSuplier || $isDispCenter) {
				 
				 $showDelivery_date_setting_menu =1 ;
			 }
			$this->setData($showDelivery_date_setting_menu,'showDelivery_date_setting_menu');
			
			/* 下面看一下改用户是否为可以授权管理其它商家的商家 */
			
			//检查该商家是否可以管理其它店铺，如果授权即可以该商家权限进入系统。
		
       
		 $mdl = $this->loadModel('authrise_manage_other_business_account');
         $authrise_manage_other_business_account = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);
		// 
 		$this->setData($authrise_manage_other_business_account, 'authrise_manage_other_business_account');

         
		 //获得商家做为dispatching centre 管理的商家列表
		 //获得用户做为工厂管理的channel的列表
		 //获得工厂如果是做b端的指定供应商列表
		 
		 // 检查2c主店 是否同时管理2b店业务，如果管理，系统菜单提示可以切入2b店铺。
		$userId2b =$this->loadModel('factory2c_list')->getFactory_2b_id($this->loginUser['id']);
		 if($userId2b){
			 
			 $this->setData($userId2b,'show_2c_switch_sb_menu');
		 }
		 
		 $userId2c =$this->loadModel('factory2c_list')->getFactory_2c_id($this->loginUser['id']);
		 if($userId2c){
			 
			 $this->setData($userId2c,'userId2c');
		 }


         //如果用户类型不是客户
         if($this->loginUser && $this->loginUser['role']!=4) {

             $act = $GLOBALS['gbl_act'];
             // 如果是确认订单已兑付 或者是显示 客户订单详细 这两个动作，不需要登陆，其它的动作都需要登陆。
             if(  $act=='customer_order_detail') {

             }else{
                 if (!$this->loginUser) {
                     $this->sheader(HTTP_ROOT_WWW . 'member/login?returnUrl=' . urlencode($_SERVER['REQUEST_URI']));
                 }
             }




             // 获得当前登陆人管理的商业账户的类型，如果该账户是员工账户，则获取员工账户对应的商家账户的商业类行

             $role = $this->loginUser['role'];
             if($role==20) {
                 $this->current_business = $this->loadModel('user')->get($this->loginUser['user_belong_to_user']);

             }else{
                 $this->current_business =$this->loginUser;
             }

             // 获取该登陆用户的角色

             if($this->loginUser) {

                 $user_roles= $this->loadModel('staff_roles')->getByWhere(array('staff_id' => $this->loginUser['id']));
                 $user_roles['role'] = $role;
                 $this->setData($user_roles, 'user_roles');
                 // var_dump($user_roles);exit;
             }

             // 如果当前的用户不是该商家的Owner ,需要进行操作授权检查。
             if($this->loginUser['role']!=3) {

                 if(!$this->checkActCanBeExecuted($act,$user_roles['roles']) ) {
                     //插入一个非常使用功能提醒。
                    $this->loadModel('alert_log')->insertAlertLog($this->loginUser,'factory_panel',$act);
                    var_dump('no access , please contact Administrator !'); exit;
                 }else{


                 }


             }


         }





         // 获取 用户设置
        if($this->loginUser){
            $mdl_user_setting =$this->loadModel('user_setting');
            $user_setting=$mdl_user_setting->get($this->loginUser['id']);
            if($user_setting) {

                $this->setData($user_setting['isLanguageEng'],'user_setting');
            }else{
                $data=array(
                    'id'=>$this->loginUser['id'],
                    'isLanguageEng'=>1
               );
                $this->lang ='en';
                $mdl_user_setting->insert($data);
                $user_setting=$mdl_user_setting->get($this->loginUser['id']);

                $this->setData($user_setting['isLanguageEng'],'user_setting');

            }

        //   var_dump($user_setting);exit;
         }else{
             if ($this->getLangStr()=='en') {
                 $this->setData('1','user_setting');
             }else{
                 $this->setData('0','user_setting');
             }

        }
		 
		
	}
    // if it is customer login , the role ==4 ,then set trulogin =1
public function  setCustomerTrueLogin($userrole){
        if($userrole==4){
            $this->session('truelogin',1);

        }

}

    public function sendInvoice($fileattr,$mdl_order,$mdl_user,$orderId) {
        //写入invoice数据
        if($fileattr) {


            //检查文件是否存在，如果存在向客户发送email ,如果不存在，则不发送
            $filenameWithPath =$fileattr['filePath'].$fileattr['fileName'];
            // var_dump($filenameWithPath);exit;
            if(file_exists($filenameWithPath)){

                $template = $this->loadModel('system_mail_template');
                $system_mailer = $this->loadModel('system_mail');

                $order = $mdl_order->getByOrderId($orderId);
                $customerEmail = $order['email'];
                $business_name = $mdl_user->getBusinessDisplayName($order['business_userId'],'en');
                $title = 'Invoice #'.$order['id'].' from '.$business_name;
                // var_dump($title);exit;
                $body  = $template->customerOrderNotificationNew($orderId,$this->getLangStr());
                $to    = $customerEmail;

                $system_mailer->title($title);
                $system_mailer->body($body);
                $path =$_SERVER['DOCUMENT_ROOT'].'/'.$fileattr['filePath'];
                // var_dump($path);exit;
                $name =(string)$fileattr['fileName'];
                //var_dump($path.$name);exit;
                $system_mailer->attachment($path.$name,'');
                // 8  $path =$_SERVER['DOCUMENT_ROOT'].'/themes/zh-cn/images/logo.png';
                // 8  $name ='logo.png';
                //8  $system_mailer->attachment($path,$name);
                $system_mailer->to($to);
                $system_mailer->send();
                echo $body;

            }else{

            }
        }

    }

//根据订单信息获得客户名称
    public function getCustomerName($order){

        //第一优先级 客户简码;
        // 如果有客户填写的客户名，同时附上
        if (trim($order['nickname'])) {
            if(trim($order['displayName'])){
                return trim($order['nickname']).'('. trim(trim($order['displayName'])).')';
            }else{
                return trim($order['nickname']);
            }

        }

        //如果没有客户简码，则客户提交订单时的 客户名 为第二优先级 ，如果客户同时填写了姓名，附上姓名；
        if(trim($order['displayName'])){
            if(trim($order['first_name']) || trim($order['last_name']) ) {
                return trim($order['displayName']).'('. trim($order['first_name']).' '.trim($order['last_name']).')';
            }else{
                return trim($order['displayName']);
            }

        }

        //  如果客户无简码，并且提交订单时未填写客户户名，则，客户填写的 姓 ，名 做为第三优先级 ；
        if(trim($order['first_name']) || trim($order['last_name']) ) {
            return  trim($order['first_name']).' '.trim($order['last_name']);
        }

        //如果以上均为捕获到客户信息，则获取用户注册时的用户信息做为标记；
        $user = loadModel('user')->get($order['userId']);
        if($user){
            if(trim($user['displayName'])){
                return trim($user['displayName']);
            }
            if(trim($user['businessName'])){
                return trim($user['businessName']);

            }
            if(trim($user['person_first_name']) || trim($user['person_last_name'])){
                return trim($user['person_first_name']).' '.trim($user['person_last_name']);
            }
            return trim($user['name']);
        }


    }

    //check if the loginuser have the right to operate user id
public function checkifLoginUserCanOperatedUserId($loginuser,$userId){

   // 如果接收的用户与Loginuser相同则允许
   // var_dump($loginuser['id']); exit;
    if($loginuser['id']==$userId) {
        return 1;
    }else{
        if($loginuser['role']==20 || $loginuser['role']==3 ) {
            $mdl_user_factory =$this->loadModel('user_factory');
            $FactoryId = $mdl_user_factory->getBusinessId($loginuser['id'],$loginuser['role']);
              //  var_dump($FactoryId.$loginuser['id']); exit;
            if($mdl_user_factory->isUserAuthorisedToOperate($userId,$FactoryId)) {
                return 1;
            }
        }

    }

    return 0;


}


//以groupmanager身份登陆
public function  groupMangerCheckAndSheader($groupManager, $url){

    $mdl_user =$this->loadModel('user');

    $user = $mdl_user->getUserById($groupManager);
    $data = array(
        'lastLoginIP'	=> ip(),
        'lastLoginDate'	=> time(),
        'loginCount'	=> $user['loginCount'] + 1
    );

    $mdl_user->updateUserById( $data, $user['id'] );

    $this->session( 'member_user_id', $user['id'] );
    $this->session( 'member_user_shell', $this->md5( $user['id'].$user['name'].$user['password'] ) );
    $this->session('truelogin',1);
    $this->loginUser=$user;
    $this->sheader(HTTP_ROOT_WWW.$url);
}

//如果当前已agent方式登陆，则强制转换为agent登陆方式
public function AgentActiveCheck($id,$agentId){

   // var_dump($agentId); exit;
    $mdl_user =$this->loadModel('user');
    $agentUser = $mdl_user->get($id);

    //如果只是客户，则直接返回

    if($agentUser['role'] ==3 || $agentUser['role'] == 20 ) {
       // var_dump('gotit ' .$agentUser['role']); exit;
        return 1;
    }

   // var_dump('not gotit ' .$agentUser['role']); exit;
    $user_roles= $this->loadModel('staff_roles')->getByWhere(array('staff_id' => $agentId));
    $this->setData($user_roles, 'user_roles');
   // var_dump($user_roles); exit;

    if ($agentId !=$id) {


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
    }
}




    //定义企业员工导向页面入口
    public function employee_navigation_panel($role_id){
        //检测员工是否拥有权限；
        /*   $isHasRole = $this->loadModel('staff_roles')->isHasRoles($this->loginUser['id'],$role_id); */
        $isHasRole =1;
        if($isHasRole){
            switch ($role_id) {
                case '5':
                    $nav_page ="salesman/index";
                    break;
                case '6':
                    $nav_page ="salesman/index";
                    break;
                case '9':
                    $nav_page ="dispatching/index";
                    break;
                case '10':
                    $nav_page ="dispatching/index";
                    break;
                case '11':
                    $nav_page ="operator/index";
                    break;
                case '12':
                    $nav_page ="dispatching/index";
                    break;
                case '16':
                    $nav_page ="driver/index";
                    break;
                default:
                    break;
            }

        }

        return $nav_page;

    }

    //get the company /index page accroding to login role and permissions

    public function  getindexPageOfUser($loginuser,$pclogin,$countOfRoles,$id){
        //如果是pc端直接进入pc端程序；
        if($pclogin) {
            return 'factory/index';
          }

        //如果role类型为3 ，则为商家的owner ,拥有全部权限；
        if($loginuser['role'] ==3 || $countOfRoles !=1) {
            // var_dump('3');exit;
            return 'factory/index_factory';
        }



        //如果只有1个用户角色，则进入相应的页面；
        if($countOfRoles ==1) {


         //   exit;
            $nav_page = $this->employee_navigation_panel( $id);
           //  $link;
            //只显示这个pannel

           return  'factory/' . $nav_page;

        }








    }

	protected function page( $sql, $pageUrl, $pageSize, $maxPage = 5,$pageUntil=false ) {
		$pageUrl		= preg_replace( '/&?perPageCount=\d+/', '', $pageUrl );
		$perPageCount	= (int)get2( 'perPageCount' );
		if ($perPageCount > 0)
		{
			$pageSize	= $perPageCount;
			$pageUrl	.= "perPageCount={$perPageCount}&";
		}
		$page			= (int)get2( 'page' );
		$pageUrl		= $pageUrl."page=";

		$recordCount	= $this->db->cnt( $this->db->query( $sql ) );
		$pageCount		= ceil( $recordCount / $pageSize );
		$page			= limitInt( $page, 1 );
		$prev_page = $page - 1;
		$next_page = $page + 1;
		if ( $prev_page < 1 ) {
			$prev_page = 1;
		}
		if ( $next_page > $pageCount ) {
			$next_page = $pageCount;
		}
		$page_l = ceil( $page - $maxPage / 2 );
		if ( $page_l < 1 ) $page_l = 1;
		$page_r = $page_l + $maxPage;
		if ( $page_r > $pageCount ) $page_r = $pageCount;

		$pageStr = '';
		if ( $pageCount > 1 ) {
			if ( $page > 1 ) {
				$pageStr .= '<a class="prev" href="'.$pageUrl.( $page - 1 ).'">上一页</a>';
			}
			else {
				$pageStr .= '<em class="prev">上一页</em>';
			}
			if ( $page_l > 1 ) {
				$pageStr .= '<a href="'.$pageUrl.'1">1</a><a>...</a>';
			}
			while ( $page_l <= $page_r ) {
				$pageStr .= '<a href="'.$pageUrl.$page_l.'"'.($page_l == $page ? ' class="current"' : '').'>'.$page_l.'</a>';
				$page_l++;
			}
			if ( $page_r < $pageCount ) {
				$pageStr .= '<a>...</a><a href="'.$pageUrl.$pageCount.'">'.$pageCount.'</a>';
			}
			if ( $page < $pageCount ) {
				$pageStr .= '<a class="next" href="'.$pageUrl.( $page + 1 ).'">下一页</a>';
			}
			else {
				$pageStr .= '<em class="next">下一页</em>';
			}
		}

		$pageStart = ( $page - 1 ) * $pageSize + 1;
		$pageEnd = ( $page - 1 ) * $pageSize + $pageSize;
		if ( $page == $pageCount ) {
			$pageEnd = $recordCount;
		}

		if($pageUntil){
			$outSql= $sql.' limit '.'0,'.( $page * $pageSize );
		}else{
			$outSql= $sql.' limit '.( ( $page - 1 ) * $pageSize ).','.$pageSize;
		}

		return array(
			'recordCount' => $recordCount,
			'perPageCount' => $pageSize,
			'pageStart' => $pageStart,
			'pageEnd' => $pageEnd,
			'pageStr' => $pageStr,
			'outSql' => $outSql,
			'pc' => $pageCount,
			'cp' => $page
			);

	}
	

	
	 //创建多语言过滤语句
	public function get_multiLanguage_where($alias) {
		
		if($this->getLangStr()=='en'){
			if($alias) {
					$whereLanguage=" and ".$alias.".languageType_en =1 ";
				}else{
					
					$whereLanguage=" and languageType_en =1 ";
				}
			}
		if($this->getLangStr()=='zh-cn'){
			if($alias) {
				$whereLanguage=" and ".$alias.".languageType_cn =1 ";
			}else{
				$whereLanguage=" and languageType_cn =1 ";
				
			}
			}
			
	return $whereLanguage;
		
	}

	public function form_response($status=null,$msg=null,$redirect=null){
		$result=array();
		if($status!=null)$result['status'] = $status;
		if($msg!=null)$result['msg'] = $msg;
		if($redirect!=null)$result['redirect'] =$redirect;
		echo json_encode( $result );exit;
	}
	public function form_response_msg($msg){
		$this->form_response(null,$msg,null);
	}

	function pc_scan_redirect($pc_wexin_scan_login){
		if ( $this->getUserDevice() == 'wechat' ) {
			$url = HTTP_ROOT_WX."member/authorize_pcscan_login?authorizeId=".$pc_wexin_scan_login."&returnUrl=".urlencode("/member/authorize_pcscan_login?authorizeId=".$pc_wexin_scan_login);
			$query = array(
				'appid' => 'wx8320e8511d65c1b4',
				'redirect_uri' =>$url,
				'response_type' => 'code',
				'scope' => 'snsapi_userinfo',
				'state' => 1
				);
			
			$query = http_build_query( $query );
			$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?'.$query.'#wechat_redirect';

			$this->sheader( $url );
		}	
	}

	public function specialEventTimeCheck($form_response=true)
	{	
        $ss = strtotime(SPECIAL_EVENT_TIME_START)-time();
        if($ss>0){
            $s = $ss%60;
            $m = floor(($ss%3600)/60);
            $h = floor(($ss%86400)/3600);
            $d = floor(($ss%2592000)/86400);
            $M = floor($ss/2592000);
            $msg=  "您还不能购买,距离抢购(".SPECIAL_EVENT_TIME_START1.")开始还有 $d 天 $h 小时 $m 分 $s 秒";
            
            if($form_response){
            	$this->form_response_msg($msg);
            }else{
            	return $msg;
            }
        }else{
        	if($form_response){
        		//doing nothing;
            }else{
            	return false;
            }
        }
	}

	/**
	 * [specialEventTotalQtyLimitCheck description]
	 * @param  [type] $userId     [description]
	 * @param  string $checkPoint [检查地点可以是 添加购物车(add) 或者支付检出(buy)]
	 * @return [type]             [description]
	 */
	public function specialEventTotalQtyLimitCheck($userId,$checkPoint='add',$form_response=true)
	{	
		$qtyLimit = 99;//

		$qtyLimitCheck=0;

		if($checkPoint=='add'){
			$qtyLimitCheck=$qtyLimit;

		}elseif($checkPoint=='buy'){
			$qtyLimitCheck=$qtyLimit+1;

		}

		$ss = strtotime(SPECIAL_EVENT_TIME_START_ORDER_LIMIT);

		// isInManagement内所有产品.
        $sql ="SELECT cc.id FROM cc_wj_customer_coupon as cc left join cc_coupons as c on cc.bonus_id=c.id WHERE cc.userId =".$userId." and cc.coupon_status!='d01' and c.isInManagement = 1 and cc.gen_date>=".$ss;
        $data=$this->loadModel('coupons')->getListBySql($sql);

        $limit1 = sizeof($data);


        $sql ="SELECT cc.id FROM cc_wj_user_temp_carts as cc left join cc_coupons as c on cc.main_coupon_id=c.id WHERE cc.userId =".$userId;
        $data=$this->loadModel('wj_user_temp_carts')->getListBySql($sql);

        $limit2 = sizeof($data);

        if($limit1+$limit2>=$qtyLimitCheck){
        	$msg = "特殊抢购产品每个用户只能购买 $qtyLimit 个,";

			if($limit1>0)
				$msg .= "您已经购买了 $limit1 个,";

			if($limit2>0)
				$msg .= "购物车中有 $limit2 个,";

			$msg .= "不能继续购买";

			if($form_response){
				$this->form_response_msg($msg);
			}else{
				return $msg;
			}
        }else{
        	if($form_response){
        		//doing nothing
			}else{
				return false;
			}

        }
	}

    public function checkActCanBeExecuted($act,$role){
     //   var_dump($act);exit;
        // 根据当前的当作，获得可以执行该动作的角色
        $authroised_roles = $this->loadModel("action_roles")->getByWhere (array('action_name'=>$act));
        if(!$authroised_roles) {
         // 如果没有发现该动作对应的角色，则默认可以执行
         //   var_dump('did not find this action  '); exit;
            return 1;
        }else{


            // 如果为管理员或总经理 可以执行
            $user_can_do_rules = explode(',', $role);
            foreach ($user_can_do_rules as $key1 => $value1) {
               if($value1=='0'|| $value1 =='1') {
                //  var_dump('role match (is a administrator) '); exit;
                   return 1;
               }
            }

            // 将该校色字符串变成数组
            $authroised_roles_arr = explode(',', $authroised_roles['roles']);
            // 将当前登陆用户的角色转成数组

           //  var_dump( $authroised_roles_arr); exit;
           // var_dump( $user_can_do_rules); exit;
            // 从该动作允许的角色数组中，依次提出角色，查看当前用户配置的角色中是否存在，如果存在，表示该用户可以执行该动作。
            foreach ($authroised_roles_arr as $key => $value) {
                if(in_array($value,$user_can_do_rules)) {
                   // var_dump('role match (not a administrator) '); exit;
                    return 1;
                }

            }
            // 如果当前动作规定了角色，且当前登陆用户没有授权该角色，则返回状态0 ，表示，用户无权操作该功能
          //  var_dump('role not match at all'); exit;
            return 0;

        }

    }

    public function  getFilePath($upload_path,$typename,$businessId,$dateym){

        $filepath =  $upload_path.'/'.$typename.'/'.$businessId.'/'.$dateym.'/';
        if(!file_exists($filepath)){
            $file = new file;

            $file->createdir( $filepath, 0777 );
            //  mkdir($up_dir,0777);
        }
        return $filepath;

    }

    //生产发票
    public  function order_invoice($orderId,$FileOrBrowser) {


        $mel_user = $this->loadModel('user');
        $mdl_abn_application = $this->loadModel('wj_abn_application');
        $mdl_user_account_info = $this->loadModel('user_account_info');

        $order = $this->loadModel('order')->getByOrderId($orderId);
        $items = $this->loadModel('wj_customer_coupon')->getItemsInOrder_menu($orderId, $this->current_business['id']);
        foreach ($items as $key1 =>$value){
            $totalAmount += $value['voucher_deal_amount']* $value['customer_buying_quantity'];
        }
        $totalAmount='$' . sprintf("%1\$.2f", $totalAmount);

        $user =$mel_user->getUserById($order['userId']);
        $userWhere = [
            'userId' => $order['userId'],
        ];
        $userABN = $mdl_abn_application->getByWhere($userWhere);
        //var_dump($userABN);exit;

        $factory = $mel_user->getUserById($order['business_userId']);
        $factoryWhere = [
            'userId' => $factory['id'],
        ];
        //  var_dump($factoryWhere);
        $factoryAccount = $mdl_user_account_info->getByWhere($factoryWhere);

        $factoryABN = $mdl_abn_application->getByWhere($factoryWhere);

        // 获得该用户的简称
        $mdl_user_factory =$this->loadModel("user_factory");
        $user_code_rec =$mdl_user_factory->getByWhere(array('user_id'=>$order['userId'],'factory_id'=>$order['business_userId']));
        //var_dump($user_code_rec);exit;





        if($order['business_userId']==319188) {

            $this->loadModel('factory_invoice_dnl');
            $report = new OrderInvoice($order, $items);
            if($this->current_business['logo']) {
                $report->logoPath(UPLOAD_PATH . $this->current_business['logo']);
            }

            $user['address']=$order['address'];
            $report->setTotalAmount($totalAmount);
            if($user_code_rec['account_type']=='COD') {
                $accountType ='COD';
            }else{
                $accountType =$user_code_rec['account_type'] *7 .'D';
            }
            $report->setaccountPayment_period($accountType);
            $report->setUser_Code($user_code_rec);
            $report->setUser($user, $userABN);
        // var_dump($user);exit;
            $factoryABN['untity_name']='DNL FOOD';
             $report->setFactory($factory, $factoryABN, $factoryAccount);

            $report->generatePDFDNL();
        }else{
            $this->loadModel('factory_invoice');
            $report = new OrderInvoice($order, $items);
            if($this->loginUser['logo']) {
                $report->logoPath('UPLOAD_PATH' . $this->loginUser['logo']);
            }

            $user['address']=$order['address'];
            $report->setUser_Code($user_code_rec);
            $report->setUser($user, $userABN);
            $report->setFactory($factory, $factoryABN, $factoryAccount);
            $report->generatePDF();
        }

        $dateym =date('y-m');

        //   $this->file->createdir('')
        if($FileOrBrowser != 'fileCC') {
            $report->outPutToBrowser(  'Invoice-' . $order['id'] . '.pdf');
        }else{
            $filePath= $this->getFilePath('data/upload','invoice',$order['business_userId'],$dateym);
            $filename =$order['id'].'.pdf';
            $report->outPutToFile($filePath.$filename);
            $fileattr =array(
                'filePath'=>$filePath,
                'fileName'=>$filename
            );
            return $fileattr;
        }

    }


    // 删除生鲜餐馆点单生成的旧的临时文件
	public function restaurantStaticFileDeleteProcess($business_id,$refresh_code_old)	{
		
		// 将所有文件清除
			$filename = DATA_DIR.'upload/htm/restaurant/menu_'.$business_id.$refresh_code_old.'.htm';
			unlink($filename); 
			$filename = DATA_DIR.'upload/htm/restaurant/menu_'.$business_id.'_en'.$refresh_code_old.'.htm';
			unlink($filename); 	   
			$filename = DATA_DIR.'upload/htm/restaurant/menu_'.$business_id.'_1'.$refresh_code_old.'.htm';
			unlink($filename);    
			$filename = DATA_DIR.'upload/htm/restaurant/menu_'.$business_id.'_1_en'.$refresh_code_old.'.htm';
			unlink($filename);  
			$filename = DATA_DIR.'upload/htm/restaurant/category_'.$business_id.$refresh_code_old.'.htm';
			unlink($filename);  
			$filename = DATA_DIR.'upload/htm/restaurant/category_'.$business_id.'_en'.$refresh_code_old.'.htm';
			unlink($filename); 
			$filename = DATA_DIR.'upload/htm/restaurant/coupon_list_'.$business_id.'_en'.$refresh_code_old.'.htm';
			unlink($filename);  
			$filename = DATA_DIR.'upload/htm/restaurant/coupon_list_'.$business_id.$refresh_code_old.'.htm';
			unlink($filename);  	   
			$filename = DATA_DIR.'upload/htm/restaurant/title_promotion_'.$business_id.$refresh_code_old.'.htm';
			unlink($filename);  
			$filename = DATA_DIR.'upload/htm/restaurant/title_promotion_'.$business_id.'_en'.$refresh_code_old.'.htm';
			unlink($filename);  
			return 1;
	}

    public function getWeekDayandDateOfSchedue($schedue){

        $days_cn =['周日','周一','周二','周三','周四','周五','周六'];
        $optional_cn = '可选';
        $notOptional_cn = '不可选';

        $days=['SUN','MON','TUE','WED','THUR','FRI','SAT'];
        $optional = 'Available';
        $notOptional = 'No Delivery';

        foreach ($schedue as $key => $value) {
            $schedue[$key]->isselected =0;

            $schedue[$key]->days_cn= $days_cn[date('w',  $schedue[$key]->orderDeliveryTimestamp)];
            $schedue[$key]->days=   $days[date('w',  $schedue[$key]->orderDeliveryTimestamp)];
            $schedue[$key]->disPlayDate=  date('d-M',  $schedue[$key]->orderDeliveryTimestamp);

            if( $schedue[$key]->isAvaliable) {
                $schedue[$key]->optionalDisplay_cn =$optional_cn;
                $schedue[$key]->optionalDisplay =$optional;
            }else{
                $schedue[$key]->optionalDisplay_cn =$notOptional_cn;
                $schedue[$key]->optionalDisplay =$notOptional;
            }


        }
        return $schedue;
    }
	/*获得当前日期的年度星期值*/
	
  public function  getYearWeekofDate(){
      $weekNumber =date('W');
      $weekday =date('w');
      $year =date('Y');


   return $year.$weekNumber;

  }
	/**
	 * [freeProductPurcheseLimitCheck description]
	 * @param  [type] $userId     [description]
	 * @param  string $checkPoint [检查地点可以是 添加购物车(add) 或者支付检出(buy)]
	 * @return [type]             [description]
	 */

	public function freeProductPurcheseLimitCheck($userId,$couponId,$checkPoint='add',$form_response=true)
	{	
		$FREEPRODUCTCHECKLISTARRAY=[4728,4730,4732,4735,4736,4740,4741,4742,4743,4744,4745,4483];
		$FREEPRODUCTCHECKLISTSTR=join($FREEPRODUCTCHECKLISTARRAY,',');

		if(!in_array($couponId, $FREEPRODUCTCHECKLISTARRAY)){
			return false;
		}

		$qtyLimit = 1;

		$qtyLimitCheck=0;

		if($checkPoint=='add'){
			$qtyLimitCheck=$qtyLimit;

		}elseif($checkPoint=='buy'){
			$qtyLimitCheck=$qtyLimit+1;

		}

		/**
		 * [$sql 已经购买数量]
		 */
		
		$sql = "select id from cc_wj_customer_coupon where userid =".$userId." and  bonus_id  in (".$FREEPRODUCTCHECKLISTSTR.") and coupon_status!='d01' ";

		$data=$this->loadModel('coupons')->getListBySql($sql);

		$limit1 = sizeof($data);

		/**
		 * [$sql 购物车中数量]
		 */

		$sql = "select id from cc_wj_user_temp_carts where userId =".$userId." and  main_coupon_id  in (".$FREEPRODUCTCHECKLISTSTR.")";

    	$data=$this->loadModel('wj_user_temp_carts')->getListBySql($sql);

    	$limit2 = sizeof($data);

		if($limit1+$limit2>=$qtyLimitCheck){
			$msg = "免费产品只能购买 $qtyLimit 个,";

			if($limit1>0)
				$msg .= "您已经购买了 $limit1 个,";

			if($limit2>0)
				$msg .= "购物车中有 $limit2 个,";

			$msg .= "不能继续购买";

			if($form_response){
				$this->form_response_msg($msg);
			}else{
				return $msg;
			}
		}else{
			if($form_response){
				//doing nothing;
			}else{
				return false;
			}
		}

	}

  public function get_same_dispatching_centre_suppliers_list($business_id)
	{
		$mdl_freshfood_disp_centre_suppliers=$this->loadModel('freshfood_disp_centre_suppliers');
		
		
		$sql =" select suppliers_id from cc_freshfood_disp_centre_suppliers where business_id =  (select DISTINCT business_id from cc_freshfood_disp_centre_suppliers where suppliers_id =$business_id)";
		$listofsuppliers = $mdl_freshfood_disp_centre_suppliers->getListBySql($sql);
		
		if($listofsuppliers) {
			$first=1;
			 foreach ($listofsuppliers as $key => $value) {
				 if($first){
					 $suppliers=$value['suppliers_id'];
					 $first=0;
					 
				 }else{
					  $suppliers.=','.$value['suppliers_id'];
					 
				 }
				 
			 }
			 
			$suppliers = '('.$suppliers.')';
		}else{
			$suppliers='('.$business_id.')';
			return $suppliers;
		}
		
		
		return $suppliers;
		
	}
	
	// 根据访问订单的用户的权限，以及当前订单的类别，返回可供显示的详细订单结果和状态， 该状态对应相应的显示页面，比如某些显示页面没有操作权限
	
	 public function get_order_details_and_related($data,$current_user) {
		
		 $item_count =sizeof($data);
		 $count=0;
		 foreach($data as $key=>$value) {
			 if($value['business_id']==$current_user) {
				 $return_data['data'][$count]=$value;
				 $count ++;
			 }
		 }
		 if($count==$item_count) {
			 $return_data['status'] =2 ;//当前用户为当前订单唯一商家。
			 $return_data['data']=$data;
		 }else if($count==0) {
			 $return_data['status'] =1 ; //当前用户为普通查看者，不是订单的商家
			 $return_data['data']=$data;
		 } else if($count<$item_count) {
			 $return_data['status'] =3 ; //当前用户为当前订单的某一个商家（该情况存在于如果该订单为统配中心订单的情况） 。
		 }
		 //var_dump($return_data['status']);exit;
		 return $return_data;
	 }
	 
	 //更具某个商家编号，返回统配中心商家号码，如果该商家不是通配中心的商家则返回0 
	 
	 public function  get_dispatching_centre_businessId($business_id) {
		 
		 $sql =" select business_id  from cc_freshfood_disp_centre_suppliers where suppliers_id=$business_id";
			$busienssList = $this->loadModel('freshfood_disp_centre_suppliers')->getListBySql($sql);
			if ($busienssList){
				return $busienssList[0]['business_id'];
			}else{
				return 0;
			}
			
	 }

    function  getBusinessType1($current_business) {
      //  var_dump($current_business);exit;
        $type='';


            if(current_business['business_type_shop']=='1') {
                $type = 'business_type_shop';
                return  $type;
            }else if (current_business['business_type_service']=='1'){
                $type = 'business_type_service';
                return  $type;
            }else if (current_business['business_type_restaurant']=='1'){
                $type = 'business_type_restaurant';
                return  $type;
            }else if (current_business['business_type_freshfood']=='1'){
                $type = 'business_type_freshfood';
                return  $type;
            }else if (current_business['business_type_media']=='1'){
                $type = 'business_type_media';
                return  $type;
            }else if (current_business['business_type_factory']=='1'){
                $type = 'business_type_factory';
                return  $type;
            }else{
                $type = 'business_type_factory';
                return $type;


        }

        return $type;
    }
	 
	   function  getBusinessType() {
		
		$type='';
		if($this->loginUser['business_type_shop']=='2') {
			    $type = 'business_type_shop';
	            return  $type;			
		}else if ($this->loginUser['business_type_service']=='2'){
			    $type = 'business_type_service';
	            return  $type;
		}else if ($this->loginUser['business_type_restaurant']=='2'){
			    $type = 'business_type_restaurant';
	            return  $type;
		}else if ($this->loginUser['business_type_media']=='2'){
			    $type = 'business_type_media';
	            return  $type;
		}else if ($this->loginUser['business_type_freshfood']=='2'){
			    $type = 'business_type_freshfood';
	            return  $type;
		}else if ($this->loginUser['business_type_factory']=='2'){
			    $type = 'business_type_factory';
	            return  $type;
		}
		
		
		else{
			
			if($this->loginUser['business_type_shop']=='1') {
			    $type = 'business_type_shop';
	            return  $type;			
			}else if ($this->loginUser['business_type_service']=='1'){
			    $type = 'business_type_service';
	            return  $type;
			}else if ($this->loginUser['business_type_restaurant']=='1'){
			    $type = 'business_type_restaurant';
	            return  $type;
			}else if ($this->loginUser['business_type_freshfood']=='1'){
			    $type = 'business_type_freshfood';
	            return  $type;
			}else if ($this->loginUser['business_type_media']=='1'){
			    $type = 'business_type_media';
				return  $type;
			}else if ($this->loginUser['business_type_factory']=='1'){
			    $type = 'business_type_factory';
				return  $type;
			}else{
				
				return $type;
				}
			
		}
		
		return $type;
	}
	
public  function get_cn_weekdayName_from_en_weekdayName($weekday) {
	
    switch (strtoupper($weekday)) { 
		case 'MON':
			$cnweekdayName ='周一';  
			break;
		case 'TUE':
			$cnweekdayName ='周二';
			break;
		case 'WED':
			$cnweekdayName ='周三';
			break;
		case 'THUR':
			$cnweekdayName ='周四';
			break;
		case 'FRI':
			$cnweekdayName ='周五';
			break;
		case 'SAT':
			$cnweekdayName ='周六';
			break;
		case 'SUN':
			$cnweekdayName ='周日';
			break;
 
	 
	 }
	 
 return $cnweekdayName;
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
	//var_dump($business_user);
	if ($business_lat){
			//如果已经存在不做处理
		}else{
			 $business_address =$business_user['googleMap'];
			 $business_lat = $this->get_address_latitude($business_address,1);
			}
	return $business_lat;
	
	
}

//根据商家的运费设置获取某个订单的运费

public function get_custom_delivery_fee($freight_rates_arr,$distance,$totalAmount,$business_userId) {
 
 $index=0;
 $countOfarr =count($freight_rates_arr);
 $isEndRecord=0;
 
 foreach ($freight_rates_arr as $key => $value) { 
	 $index++;
	 if($index ==$countOfarr){ $isEndRecord=1;}
	
	 if($totalAmount<=$value['end_amount1'] || $isEndRecord ) { //表示订单总额在该条运费规定的记录之内,或已经到最后一条记录，则不管是否价格在不在区间都执行相应运费策略，次条会避免用户设置运费时没有设置无上限额度的运费造成的问题
		
		 if($distance > $value['farest_distance']) {
			 
			  $total_delivery_fee = 9999; //表示相对于该订单额度超出配送范围，提示用户查看配送规则
			  break;
		 }
		 
		
		 if( $distance <=$value['distance1'] ) { //表示距离在第一个区间
		    // var_dump('distance1');
			 $basic_delivery_fee =$value['delivery_fees1'];
			 if($value['plus_fees_per_km_1']>0){ //如果有附加运费
				 $addtional_delivery_fee = ($distance-0)*$value['plus_fees_per_km_1'];
			 }else{
				 $addtional_delivery_fee =0;
			 }
			 $total_delivery_fee =  $basic_delivery_fee + $addtional_delivery_fee;
			 break;
				 
			 
		 } else if($distance <=$value['distance2'] ) { //如果顾客地址不再第一个范围之内，检查第二个配送范围
		 
			//var_dump('distance2');
			  $basic_delivery_fee =$value['delivery_fees2'];
			 if($value['plus_fees_per_km_2']>0){ //如果有附加运费
				 $addtional_delivery_fee = ($distance-$value['distance1'])*$value['plus_fees_per_km_2'];
			 }else{
				 $addtional_delivery_fee =0;
			 }
			 $total_delivery_fee =  $basic_delivery_fee + $addtional_delivery_fee;
			 break;
			 
		 }else if($distance <=$value['distance3'] ) { //如果顾客地址不再第二个范围之内，检查第三个配送范围
			// var_dump('distance3');
			  $basic_delivery_fee =$value['delivery_fees3'];
			 if($value['plus_fees_per_km_3']>0){ //如果有附加运费
				 $addtional_delivery_fee = ($distance-$value['distance2'])*$value['plus_fees_per_km_3'];
			 }else{
				 $addtional_delivery_fee =0;
			 }
			 $total_delivery_fee =  $basic_delivery_fee + $addtional_delivery_fee;
			 break;
			 
		 }
	 }
	 
	 }
	//  var_dump($distance.' '.$value['distance1'].' '.$totalAmount.' '.$value['end_amount1'].' '.$isEndRecord);
	 return  round($total_delivery_fee,2);
 
} 

public function get_business_delivery_des ($business_id){
	
	$mdl_user =  $this->loadModel('user');
	$mdl_custom_freight_rates = $this->loadModel('custom_freight_rates');
	$where = ['business_id' => $business_id]; 
	
	$business_info =$mdl_user->get($business_id);

	$freight_rates_arr = $mdl_custom_freight_rates->getList([],$where,'end_amount1');
	
	$start_amount = $business_info['amount_for_minimum_delivery'];
	$free_delivery_amount = $business_info['amount_for_free_delivery'];
			
			
			
	   if(!$start_amount) $start_amount =0;
		
		$delivery_desc ="";
		
		if( $start_amount>0){
			$delivery_desc ="起送金额".$start_amount.'.';
		}else{
			$delivery_desc ="无起订金额".'.';
		}
		
		if( $free_delivery_amount >0){
			$delivery_desc =$delivery_desc." ,免运金额".$free_delivery_amount.'.';
		}
		
		if($freight_rates_arr){
			$index=0;
			foreach ($freight_rates_arr as $key => $value) {
				$index++;
			
				if($value['end_amount1']>=9999) {
					
					$freight_rates_arr[$key]['end_amount11'] = '<br>'.$index.') 订单额大于$'.(int)$start_amount;
				}else{
					$freight_rates_arr[$key]['end_amount11'] = '<br>'.$index.') 订单额$'.(int)$start_amount.'-$'.(int)$value['end_amount1'];
				}
				
				
				$delivery_desc_new =' '.$freight_rates_arr[$key]['end_amount11'].','.$value['distance1'].'km内';
				
				if($value['delivery_fees1']<=0) {
					$delivery_desc_new =$delivery_desc_new .'免运,';
				}else{
					$delivery_desc_new =$delivery_desc_new .'运费'.$value['delivery_fees1'].',';
				}
				
				
				if($value['plus_fees_per_km_1']>0){					
					$delivery_desc_new =$delivery_desc_new .' 并从0公里开始，加收$'.$value['plus_fees_per_km_1'].'/km的运费，';
				}
				
				if($value['distance2']){
					
					$delivery_desc_new = $delivery_desc_new .$value['distance1'].'-'.$value['distance2'].'km';
					
					
					if($value['delivery_fees2']<=0) {
						$delivery_desc_new =$delivery_desc_new .'免运费,';
					}else{
						$delivery_desc_new =$delivery_desc_new .'运费$'.$value['delivery_fees2'].',';
					}
				
						
					if($value['plus_fees_per_km_2']>0){					
						$delivery_desc_new =$delivery_desc_new .' 并从'.$value['distance1'].'km开始，加收$'.$value['plus_fees_per_km_2'].'/km的运费，';
					}
				}
				
				if($value['distance3']){
					
					$delivery_desc_new = $delivery_desc_new .$value['distance2'].'-'.$value['distance3'].'km';
					
					
					if($value['delivery_fees3']<=0) {
						$delivery_desc_new =$delivery_desc_new .'免运费,';
					}else{
						$delivery_desc_new =$delivery_desc_new .'运费$'.$value['delivery_fees3'].',';
					}
					
					if($value['plus_fees_per_km_3']>0){					
						$delivery_desc_new =$delivery_desc_new .' 并从'.$value['distance2'].'km开始，加收$'.$value['plus_fees_per_km_3'].'/km的运费，';
					}
				}
				
				$delivery_desc_new = $delivery_desc_new .'超过'.$value['farest_distance'].'km不配送！';
				
				$start_amount = $value['end_amount1'];
				$delivery_desc = $delivery_desc . $delivery_desc_new;
			 }
			
			 
		}	
			
			
			return $delivery_desc;
	 }
		 
		 
    // 根据当前商家，在没有searchword的情况下，选择分类后（大类，或大类中类），如果不存在空的记录（待客户直接输入产品形成一个真的产品），给该商家的产品库增加相应大类，中类的空数据。
	
	public function addNewEmptyMenuUponCategory($BusinessId,$Category,$sub_category,$insertCount){
		
		// return 0 : enough empty 
		// return 1 : success
		// return 2 : failure 
		
		//var_dump('rr'.$Category);exit;
		$mdl__restaurant_menu = $this->loadModel('restaurant_menu');
		//返回的是对于当前商家，当前类别（大类 或大类中类组合）可以待输入的空产品数量。
		//var_dump('rr'.$insertCount);exit;
		$CountOfEmptyMenuUponCategory =$mdl__restaurant_menu->findCountOfEmptyMenuUponCategory($BusinessId,$Category,$sub_category); //如果不存在返回应该是0 
		
		if(!$CountOfEmptyMenuUponCategory){
			$CountOfEmptyMenuUponCategory =0;
		}
		
		if($CountOfEmptyMenuUponCategory <=$insertCount){
			$new_add_count = $insertCount-$CountOfEmptyMenuUponCategory;
			
		}else{
			$new_add_count = 0;
			return '0'; //无需添加 
		}
		
		if($new_add_count) {
		   // var_dump('here');exit;
			if ($this->addEmptyMenuUponCategory($BusinessId,$Category,$sub_category,$new_add_count) ){
				
				return 1;  //success
			}else{
				return 2;  //failure
				
			}
			
		}
		
		
	}
		
		
	// 增加相应数量的空记录
	public function addEmptyMenuUponCategory($BusinessId,$Category,$sub_category,$new_add_count){ 
	
		//var_dump($sub_category);exit;
	      if($sub_category){ // 如果是子类存在，则在相关表取该子类的数量，并生成初始菜单编号
			  $currentCategoryCount = $this->loadModel("restaurant_menu_category") ->getCountOfCurrentSubCategory($sub_category);
			   $this->loadModel('restaurant_menu')->addMenusUponCategory($BusinessId,$Category,$sub_category,1,$currentCategoryCount,$new_add_count);
		  }else{
			  
			  $currentCategoryCount = $this->loadModel("restaurant_menu") ->getCountOfCurrentCategory($Category);
			 
			   $this->loadModel('restaurant_menu')->addMenusUponCategory($BusinessId,$Category,$sub_category,0,$currentCategoryCount,$new_add_count);
		  }
		 
		
			 		
			
	
	}	
	

 public function add_cut_image_action() {
	
 $mdl_res = $this->loadModel('restaurant_menu');
 $sql ="select id,menu_pic from cc_restaurant_menu where restaurant_id =318951 and  length(menu_pic)>0 ";
 $list_data =$mdl_res->getListBySql($sql);
 
 foreach ($list_data as $key => $value) { 
  $this->cut_image($value['menu_pic'],100,100,'cut');
  
 }
	
	
}



public function test_get_image_action() {
	
	
	
	
	$url = 'https://images.barcodelookup.com/11996/119965368-1.jpg';
	
	
	$pic_path_filename='standardpic/'.substr($url,strrpos($url, '/', -1)+1);
	
	//$data['menu_pic'] =$pic_path_filename;
	$this->save_pic($url,$pic_path_filename);
	$this->cut_image($pic_path_filename,150,150,'cut');
	

	
}			





	

public function gen_image_file_from_barcode_web($images,$uploadPath) {
	
	 	if(!$uploadPath) { $uploadPath = 'standardpic';}
		
	
	
	 $pic_array =array();
	
	if(is_array($images)) {
		  // var_dump('is array');exit;
			foreach ($images as $key => $value) {
				$url =   $value;      
				if($url ) {
					
					$pic_path_filename=$uploadPath.'/'.substr($url,strrpos($url, '/', -1)+1);
					
					//$data['menu_pic'] =$pic_path_filename;
					$this->save_pic($url,$pic_path_filename);
					
					$this->cut_image($pic_path_filename,150,150,'cut');
					$pic_array[$key]=$pic_path_filename;
				}		
					
			 }
		
		
	}else{
		// var_dump('is not array');exit;	
		$url=$images;
		
		$pic_path_filename=$uploadPath.'/'.substr($url,strrpos($url, '/', -1)+1);
					
		//$data['menu_pic'] =$pic_path_filename;
		$this->save_pic($url,$pic_path_filename);
		
		
		$this->cut_image($pic_path_filename,150,150,'cut');
		$this->cut_image($pic_path_filename,66,66,'fill');
		$pic_array[0]=$pic_path_filename;
		
		
	}

	
  return  $pic_array;
	
}	

public function http_get_data($url) {  
      
    $ch = curl_init ();  
    curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );  
    curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );  
    curl_setopt ( $ch, CURLOPT_URL, $url ); 
	curl_setopt ( $ch, CURLOPT_TIMEOUT, 5 ); 
	
    ob_start ();  
    curl_exec ( $ch );  
    $return_content = ob_get_contents ();  
    ob_end_clean ();  
      
    $return_code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );  
	curl_close($ch); // close handle
    return $return_content;  
}  
 

public function get_pic_name($url,$uploadPath)
{
	if(!$uploadPath) {
		$uploadPath ='standardpic';
	}
	
	$filename=substr($url,strrpos($url, '/', -1)+1);
	$path='data/upload/'.$uploadPath.'/';
	return $path.$filename;
}		


public  function generate_images_data($rec) {
	  
	  $data=array();
	  $data['menu_pic'] = $rec['images1'];
	  $data['menu_pics'] = $rec['imagesmore'];
	  return $data;
	  
	  
  }
  
 public function  count_of_menu_pics($menu) {
	 
	  $countOfMenupics =sizeof(unserialize($menu['menu_pics']));
	  if($menu['menu_pic']) $countOfMenupics++;
	  
	  return $countOfMenupics;
	 
 }

public function save_pic($url,$filename){
	
	if (file_exists($filename)) {
   
		} else {
			$return_content = $this->http_get_data($url);  
			//var_dump('here');exit;	
			$fp= @fopen('data/upload/'.$filename,"a"); //将文件绑定到流
			
			fwrite($fp,$return_content); //写入文件  
			//var_dump($filename);exit;
		}
			
}
	
	
	
 public  function cut_image( $string, $width, $height, $method = 'fill',$baseOnSkinPath=false,$forceNew=false) {
  //  $string ="2020-12/1609828130-d8c7131ef7db494f2ad9d19dac7c4208.jpg";
	$baseDir = $baseOnSkinPath?TPL_DIR:UPDATE_DIR;
	
	
	//$width =100;
//	$height=100;
	//var_dump($baseDir);exit;

	$noImage = false;
	if ( empty( $string ) ||  !file_exists( $baseDir.$string ) || ! in_array( $method, array( 'cut', 'fill' ) ) ) {
		$noImage = true;
		$string = 'no-image.gif';
		if ( ! file_exists( $baseDir.$string ) ) {
			return '';
		}
		$method = 'cut';
		//return $string;
	}

	$width = (int)$width;
	$height = (int)$height;

	if ( $width <= 0 || $height <= 0 ) {
		return $string;
	}

	$image_state = getimagesize( $baseDir.$string );
	
	switch ( $image_state[2] ) {
		case 1 : $im = imagecreatefromgif( $baseDir.$string ); break;
		case 2 : $im = imagecreatefromjpeg( $baseDir.$string ); break;
		case 3 : $im = imagecreatefrompng( $baseDir.$string ); break;
	}
	$old_width = $image_state[0];
	$old_height = $image_state[1];

	if ( $old_width == $width && $old_height == $height ) {
		return $string;
	}

	$file = new file;
	$newImageDir = $baseDir.'thumbnails/';
	$newImageUrl = $file->nameExtend( $string, "_{$width}x{$height}_{$method}" );
	//$newImageUrl = $file->nameExtend( $string, "" );

	if ( file_exists( $newImageDir.$newImageUrl )&& !$forceNew) {
		return 'thumbnails/'.$newImageUrl;
	}

	$newImagePath = $file->name( $newImageUrl );
	$newImagePath = str_replace( $newImagePath, '', $newImageUrl );
	$file->createdir( $newImageDir.$newImagePath, 0777 );
	if ( $method == 'fill' ) {
		$file->resize( $baseDir.$string, $newImageDir.$newImageUrl, $width, $height );
		$file->fillColor( $newImageDir.$newImageUrl, $newImageDir.$newImageUrl, $width, $height, array( 255, 255, 255 ) );
	}
	elseif ( $method == 'cut' ) {
		$file->resize( $baseDir.$string, $newImageDir.$newImageUrl, $width, $height, true, true );
		$file->cutByPos( $newImageDir.$newImageUrl, $newImageDir.$newImageUrl, $width, $height );
	}

	// watermarkImage($newImageDir.$newImageUrl); disable water mark of image

	return 'thumbnails/'.$newImageUrl;
}
	
	
	
	public function image_file_insert_cut_info($fileName,$letters_insert){
	// $fileName,$letters_insert
	
	//$fileName ='standardpic/136605054-1.jpg';
	//$letters_insert ="_66x66_fill";
	
	$findLastDotPosition = strripos($fileName,'.',0);
	
	$frontLetters = substr($fileName,0,$findLastDotPosition);
	$endLetters =substr($fileName,$findLastDotPosition);
	
	
	return $frontLetters.$letters_insert.$endLetters ;
	
	
	
	
	
	
}
	
}



?>