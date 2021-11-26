<?php

//会员

class ctl_referal extends cmsPage
{


	function ctl_referal() {
		parent::cmsPage();

		$act = $GLOBALS['gbl_act'];
		$ignore_list = array('preview_new_rule_ajax','edit_referral_rule_success');

		if ( !$this->loginUser&&!in_array($act, $ignore_list)) {
			$this->sheader( HTTP_ROOT_WWW.'member/login?returnUrl='.urlencode( $_SERVER['REQUEST_URI'] ) );
		}
	}

	function index_action()
	{
		$mdl_referrals = $this->loadModel( 'referrals' );

		$ref = $mdl_referrals->getByWhere( array( 'userId' => $this->loginUser['id'] ) );
		$this->setData( $ref, 'ref' );


		$this->setData( 'referal', 'menu' );
		$this->setData( 'index', 'submenu' );
		$this->setData( '介绍人中心 - '.$this->site['pageTitle'], 'pageTitle' );

		$this->display('referal/referral_index');
	}

	function referrals_action() {

		$type=get2('type');
		

		$mdl_user = $this->loadModel( 'user' );
		$mdl_referrals = $this->loadModel( 'referrals' );
		$mdl_referral_relation = $this->loadModel( 'referral_relation' );

		$this->setData( $type, 'type' );
		
		$shop=get2('shop');

		$this->setData( $shop, 'shop' );

		if($type=='business'){

			$ref_list=$mdl_referral_relation->owner($this->loginUser['id'])->getBusinessList();

			$this->setData( $ref_list, 'ref_list' );
			$this->setData( 'my_mingxingshop', 'menu' );
			$this->setData( 'referrals_business', 'submenu' );
			$this->setData( '商家下线 - '.$this->site['pageTitle'], 'pageTitle' );

			$this->display_pc_mobile('referal/referrals_user','mobile/referal/referrals_user');

		}elseif($type=='user'){

			$ref_list=$mdl_referral_relation->owner($this->loginUser['id'])->getCouponList();

			$this->setData( $ref_list, 'ref_list' );

			$this->setData($mdl_referral_relation->getRefCommissionTotal($this->loginUser['id']), 'totalCommission' );
			$this->setData($mdl_referral_relation->getRefOrderCount($this->loginUser['id']), 'totalOrderCount' );

			$this->setData( 'referal', 'menu' );
			$this->setData( 'referrals_user', 'submenu' );
			$this->setData( '用户下线 - '.$this->site['pageTitle'], 'pageTitle' );

			$this->display_pc_mobile('referal/referrals_user','mobile/referal/referrals_user');
		}
		
	}

	public function shared_coupon_detail_action()
	{	
		$couponId = get2('couponId');

		$this->_onlyAccessableByReferral();

		$mdl_referral_relation = $this->loadModel( 'referral_relation' );

		$ref_list=$mdl_referral_relation->owner($this->loginUser['id'])->getUserList($couponId);


		$this->setData( $ref_list, 'ref_list' );
		$this->setData( 'referal', 'menu' );
		$this->setData( 'referrals_user', 'submenu' );
		$this->setData( '用户下线 - '.$this->site['pageTitle'], 'pageTitle' );

		$this->display_pc_mobile('referal/referrals_user_detail','mobile/referal/referrals_user_detail');

	}

	function referrals_reg_action() //成为介绍人
	{
		$mdl_user = $this->loadModel( 'user' );
		$mdl_referrals = $this->loadModel( 'referrals' );

		$ref = $mdl_referrals->getByWhere( array( 'userId' => $this->loginUser['id'] ) );
		if ( $ref ) $this->sheader( $this->parseUrl()->setPath('referal/index') );

		if ( is_post() ) {
			$firstName = trim( post('firstName') );
			$lastName = trim( post('lastName') );
			$email = trim( post('email') );
			$phone = trim( post('phone') );
			if ( empty( $firstName ) || empty( $lastName ) || empty( $email ) || (empty( $phone )) ) {
				$this->form_response_msg('请将表单填写完整后再提交');
			}

			$info = array(
				'name' => $this->loginUser['person_first_name'] ? $this->loginUser['person_first_name'] : $this->loginUser['name'],
				'firstName' => $firstName,
				'lastName' => $lastName,
				'email' => $email,
				'phone' => $phone,
			);

			if ( $mdl_referrals->join($this->loginUser['id'], $info ) ) {
				$mdl_referrals->setApprove($this->loginUser['id']);
				$this->form_response(200,'申请成功',HTTP_ROOT_WWW.'referal/index');
			}
			else {
				$this->form_response_msg('申请失败，请联系网站客服人员');
			}
		}
		else {
			$this->setData( 'referal', 'menu' );
			$this->setData( 'index', 'submenu' );
			$this->setData( '申请成为介绍人 - 个人中心 - '.$this->site['pageTitle'], 'pageTitle' );
			$this->display('referal/referrals_reg');
		}
	}

	public function view_referral_rule_action()
	{	
		$id = get2('id');

		$mdl_user = $this->loadModel('user');
		$mdl_referral_rule=$this->loadModel('referral_rule');
		$mdl_referral_rule_application=$this->loadModel('referral_rule_application');

		$rule = $mdl_referral_rule->get($id);
		
		if($rule['create_user_id']!=$this->loginUser['id'])$this->sheader(null,'越权访问');

		$this->setData($rule,'rule');


		if($id)$ruleStatistics = $mdl_referral_rule->getStatistics($id);

		$this->setData($ruleStatistics,'ruleStatistics');


		$list = $mdl_referral_rule_application->getUserListOfRule($id);

		foreach ($list as $key => $value) {
			$list[$key]['user_name']=$mdl_user->getDisplayName($value['user_id']);
			if($id)$list[$key]['statistics']=$mdl_referral_rule->getStatistics($id,$value['user_id']);
		}

		$this->setData($list,'list');


		$this->setData( 'community', 'menu' );
		$this->setData( 'manage_referral_rule', 'submenu' );

		$this->setData( '媒体用户列表 - '.$this->site['pageTitle'], 'pageTitle' );

		$this->display('referal/view_referrals_rule');

	}	

	public function manage_referral_rule_action()
	{	
		if($this->getUserDevice()!='desktop')$this->sheader(HTTP_ROOT_WWW.'referal/manage_referral_rule_mobile');

		$mdl_referral_rule = $this->loadModel('referral_rule');

		$mdl_coupons= $this->loadModel('coupons');

		$where['create_user_id']= $this->loginUser['id'];

		$list = $mdl_referral_rule->getList(null,$where,'id desc');

		foreach ($list as $key => $value) {
			$list[$key]['desc']=$mdl_referral_rule->getRuleDesc($value);
			$list[$key]['coupon_pic']=$mdl_coupons->getPic($value['coupon_id']);
			$list[$key]['coupon_title']=$mdl_coupons->getTitle($value['coupon_id']);
		}
		$this->setData($list,'list');

		$this->setData($this->parseUrl()->setPath('referal/view_referral_rule'),'viewUrl');
		$this->setData($this->parseUrl()->setPath('referal/edit_referral_rule'),'editUrl');
		$this->setData($this->parseUrl()->setPath('referal/edit_referral_rule_success'),'codeUrl');

		$this->setData( 'community', 'menu' );
		$this->setData( 'manage_referral_rule', 'submenu' );

		$this->setData( '生成媒体码 - '.$this->site['pageTitle'], 'pageTitle' );
		$this->display('referal/manage_referrals_rule');
	}

	public function manage_referral_rule_mobile_action()
	{	
		if($this->getUserDevice()=='desktop')$this->sheader(HTTP_ROOT_WWW.'referal/manage_referral_rule');

		$mdl_user = $this->loadModel('user');
		$mdl_referral_rule=$this->loadModel('referral_rule');
		$mdl_referral_rule_application=$this->loadModel('referral_rule_application');

		//rule list
		$list = $mdl_referral_rule->getListOfUser($this->loginUser['id']);

		$this->setData($list,'list');


		//rule info
		$id = get2('selectedId');

		$rule = $mdl_referral_rule->get($id);
		
		if($rule&&$rule['create_user_id']!=$this->loginUser['id'])
			$this->sheader(null,'越权访问');

		if($rule){
			$rule['desc']=$mdl_referral_rule->getRuleDesc($rule);

			$this->setData($rule,'rule');
		}

		if($id)$ruleStatistics = $mdl_referral_rule->getStatistics($id);

		$this->setData($ruleStatistics,'ruleStatistics');

		//rule detail
		$list = $mdl_referral_rule_application->getUserListOfRule($id);

		foreach ($list as $key => $value) {
			$list[$key]['user_name']=$mdl_user->getDisplayName($value['user_id']);
			if($id)$list[$key]['statistics']=$mdl_referral_rule->getStatistics($id,$value['user_id']);
		}

		$this->setData($list,'ref_list');


		$this->setData($this->parseUrl()->setPath('referal/edit_referral_rule'),'editUrl');
		$this->setData($this->parseUrl()->setPath('referal/edit_referral_rule_success'),'codeUrl');


		$this->setData( '生成媒体码 - '.$this->site['pageTitle'], 'pageTitle' );
		$this->display('mobile/referal/manage_referrals_rule');
	}

	public function edit_referral_rule_action()
	{
		if(is_post()){

			$mdl_referral_rule = $this->loadModel('referral_rule');

			$coupon_id = post('coupon_id');
			$type = post('type');
			$special_rate = post('special_rate');
			$user_id = $this->loginUser['id'];

			//validation
			if(!$coupon_id)
				$this->form_response_msg('请选择产品');

			$type_array = [mdl_referral_rule::TYPE_PERCENT,mdl_referral_rule::TYPE_FIXED_AMOUNT_PERITEM];
			if(!in_array($type, $type_array))
				$this->form_response_msg('错误类型');

			if(!$special_rate)
				$this->form_response_msg('请填写优惠额度');

			if($type==mdl_referral_rule::TYPE_PERCENT){

				if((floatval($special_rate)>100||floatval($special_rate)<1))$this->form_response_msg('百分比提成率范围1-100');

				$special_rate=$special_rate/100.0;
				
			}elseif($type==mdl_referral_rule::TYPE_FIXED_AMOUNT_PERITEM){

			}


			$rule= $mdl_referral_rule->setOwner($user_id)
    					->setCoupon($coupon_id)
    					->setRate($special_rate,$type)
    					->create();

    		$this->form_response(200,'添加成功',HTTP_ROOT_WWW.'referal/edit_referral_rule_success?id='.$rule['id']);

		}else{
			$where['createUserId']=$this->loginUser['id'];
			$coupon_list = $this->loadModel('coupons')->getList(null,$where);
			$this->setData($coupon_list,'coupon_list');

			$this->setData($this->parseUrl()->setPath('referal/edit_referral_rule'),'editUrl');

			$this->setData( 'community', 'menu' );
			$this->setData( 'edit_referral_rule', 'submenu' );
			$this->setData( '添加媒体码 - '.$this->site['pageTitle'], 'pageTitle' );

			$this->display('referal/edit_referrals_rule');
		}

	}

	/**
	 * 该页面为公开页面。
	 * 用于直接或间接分享邀请媒体人参加
	 */
	public function edit_referral_rule_success_action()
	{	
		$id = get2('id');
		if(!$id)$this->sheader(null,'缺少ID');

		$mdl_referral_rule = $this->loadModel('referral_rule');

		$rule = $mdl_referral_rule->get($id);

		$this->setData($rule);

		$coupon=$this->loadModel('coupons')->get($rule['coupon_id']);
		$business=$this->loadModel('user')->get($rule['create_user_id']);
		
		$this->setData($coupon,'coupon');
		$this->setData($business,'business');


		$url = HTTP_ROOT.'referal/apply_new_rule?referal_rule_code='.$rule['apply_code'];
		$qrcode = generateQRCode($url);

		$this->setData($url,'applyUrl');
		$this->setData($qrcode,'applyqrcode');


		//wx share
		require_once "wx/wxjssdk.php";
        $jssdk = new WXjsSDK();
        $signPackage = $jssdk->GetSignPackage();
        $this->setData($signPackage,'signPackage');

        if($this->loginUser['id']==$rule['create_user_id']){
        	//商家View
        	
        	$this->setData($this->parseUrl()->setPath('referal/manage_referral_rule')->set('id'),'manageUrl');
			$this->setData($this->parseUrl()->setPath('referal/edit_referral_rule')->set('id'),'editUrl');

			$this->setData( 'referal', 'menu' );
			$this->setData( 'manage_referral_rule', 'submenu' );
			$this->setData( '媒体邀请二维码 - '.$this->site['pageTitle'], 'pageTitle' );

			$this->display_pc_mobile('referal/referrals_rule_invite_pc','referal/referrals_rule_invite_mobile');

        }else{
        	//被邀请者View

			$this->setData( 'referal', 'menu' );
			$this->setData( 'manage_referral_rule', 'submenu' );
			$this->setData( '分享推广邀请 - '.$business['businessName'], 'pageTitle' );

			$this->display_pc_mobile('referal/referrals_rule_invite_pc_veiw','referal/referrals_rule_invite_mobile_view');
        }
		
	}



	public function update_referral_rule_ajax_action()
	{

		
		$target = get2('target');

		$value = get2('value');


		$result= $this->loadModel('referral_rule')->setId($target)->updateRuleStatus($value);


		if($result){
			echo "success";
		}else{
			echo "error";
		}

	}


	public function manage_referral_rule_application_action()
	{
		$this->_onlyAccessableByReferral();

		$mdl_referral_rule=$this->loadModel('referral_rule');

		$list = $this->loadModel('referral_rule_application')->getUserRuleList($this->loginUser['id']);

		foreach ($list as $key => $value) {
			$list[$key]['desc']=$mdl_referral_rule->getRuleDesc($value);
		}

		$this->setData($list, 'list' );

		$mdl_referral_relation= $this->loadModel('referral_relation');

		$this->setData($mdl_referral_relation->getAppliedRuleCommissionTotal($this->loginUser['id']), 'totalCommission' );
		$this->setData($mdl_referral_relation->getAppliedRuleOrderCount($this->loginUser['id']), 'totalOrderCount' );

		$this->setData($this->parseUrl()->setPath('referal/apply_new_rule')->set('id'),'applyUrl');

		$this->setData( 'referal', 'menu' );
		$this->setData( 'manage_referral_rule_application', 'submenu' );

		$this->setData( '使用媒体码 - '.$this->site['pageTitle'], 'pageTitle' );

		$this->display_pc_mobile('referal/manage_referrals_rule_application','mobile/referal/manage_referrals_rule_application');
	}

	public function update_referral_rule_application_ajax_action()
	{

		$this->_onlyAccessableByReferral();
		
		$target = get2('target');

		$value = get2('value');


		$result= $this->loadModel('referral_rule_application')->updateStatus($value,$target);


		if($result){
			echo "success";
		}else{
			echo "error";
		}

	}

	public function apply_new_rule_action()
	{	
		$mdl_referral_rule=$this->loadModel('referral_rule');
		$mdl_referrals=$this->loadModel('referrals');

		if(is_post()){
			$referal_rule_code = post('referal_rule_code');

			if(!$referal_rule_code)
				$this->form_response_msg('请填写媒体码');

			$rule = $mdl_referral_rule->getByCode($referal_rule_code);
			if(!$rule)
				$this->form_response_msg('没有找到相应规则');

			$result = $mdl_referral_rule->applyRule($referal_rule_code,$this->loginUser['id']);

			if(!$result){
				$this->form_response_msg('未能成功使用，请联系Ubonus客户或稍后再试');
			}else{
				$mdl_referrals->join($this->loginUser['id'],$this->loginUser);
				$mdl_referrals->setApprove($this->loginUser['id']);

				$this->form_response(200,'使用成功',HTTP_ROOT_WWW."referal/apply_new_rule_success?referal_rule_code=$referal_rule_code");
			}

		}else{
			$referal_rule_code = get2('referal_rule_code');

			if($referal_rule_code){
				$rule = $mdl_referral_rule->getByCode($referal_rule_code);
				if(!$rule)
					$this->sheader(null,'没有找到相应规则');

				$result = $mdl_referral_rule->applyRule($referal_rule_code,$this->loginUser['id']);

				if(!$result){
					$this->sheader(null,'未能成功使用，请联系Ubonus客户或稍后再试');
				}else{
					$mdl_referrals->join($this->loginUser['id'],$this->loginUser);
					$mdl_referrals->setApprove($this->loginUser['id']);

					$this->sheader(HTTP_ROOT_WWW."referal/apply_new_rule_success?referal_rule_code=$referal_rule_code");
				}
			}

			$this->setData( 'referal', 'menu' );
			$this->setData( 'manage_referral_rule_application', 'submenu' );

			$this->setData($this->parseUrl(), 'postUrl');
			
			$this->setData( '使用媒体码 - '.$this->site['pageTitle'], 'pageTitle' );

			$this->display('referal/apply_new_rule');
		}

	}

	public function apply_new_rule_success_action()
	{	
		$referal_rule_code = get2('referal_rule_code');

		if(!$referal_rule_code)$this->sheader(null,'referal_rule_code 缺失');

		$rule = $this->loadModel('referral_rule')->getByCode($referal_rule_code);

		if(!$this->loadModel('referral_rule')->hasApplication($rule['id'],$this->loginUser['id']))
			$this->sheader(null,'您还没有应用该媒体码');
		
		//商家
		$rule['create_user_id'];
		$this->setData($this->loadModel('user')->getBusinessDisplayName($rule['create_user_id']),'businessName');
		

		//产品
		$rule['coupon_id'];
		$this->setData($this->loadModel('coupons')->get($rule['coupon_id']),'coupon');

		//desc
		$desc= $this->loadModel('referral_rule')->getRuleDesc($rule);
		$this->setData($desc,'desc');
		
		//shareLink
		$url = HTTP_ROOT."coupon1/".$rule['coupon_id']."?reftag=".$this->loginUser['id'];
		$this->setData($url,'shareUrl');

	    //shareQRCode
		$this->setData(generateQRCode($url),'shareQRCode');


		$this->setData( 'referal', 'menu' );
		$this->setData( 'manage_referral_rule_application', 'submenu' );
		$this->setData( '成功使用媒体码 - '.$this->site['pageTitle'], 'pageTitle' );

		$this->display('referal/apply_new_rule_success');
	}

	/**
	 * 该页面为公开页面。
	 */
	public function preview_new_rule_ajax_action()
	{
		$referal_rule_code = get2('referal_rule_code');

		if(!$referal_rule_code)echo "没有code";

		$mdl_referral_rule= $this->loadModel('referral_rule');

		$where['apply_code']=$referal_rule_code;
		$where['status']=mdl_referral_rule::STATUS_ENABLE;

		$rule = $mdl_referral_rule->getByWhere($where);

		$rule['desc'] = $mdl_referral_rule->getRuleDesc($rule );

		if($rule){
			foreach ($rule as $key => $value) {
				if(is_numeric($key))unset($rule[$key]);
			}
			$rule['create_user_name']=$this->loadModel('user')->getDisplayName($rule['create_user_id']);
			echo json_encode($rule);
		}else{
			echo "验证失败";
		}

	}


	private function _onlyAccessableByReferral()
	{
		$ref = $this->loadModel('referrals')->getByWhere( array( 'userId' => $this->loginUser['id'] ) );
		if(!$ref)$this->sheader( $this->parseUrl()->setPath('referal/index')->set('type') );
	}
	
}