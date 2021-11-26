<?php 
class ctl_group_buy1 extends cmsPage{

	function ctl_group_buy() {
		parent::cmsPage();

		$act = $GLOBALS['gbl_act'];
		$ignore_list = array('group_list_show_ajax','list');
		if ( !in_array($act, $ignore_list) && !$this->loginUser ) {
			$this->sheader( HTTP_ROOT_WWW.'member/login?returnUrl='.urlencode( $_SERVER['REQUEST_URI'] ) );
		}
	
	}

	function index_action(){

		//IMPORTANT:ALL display use mobile version for now.
		$this->index_mobile_action();exit;

		if($this->getUserDevice()!='desktop'){$this->index_mobile_action();exit;}

		$group_buy_id=get2('group_buy_id');
       	$id = get2('coupon_id');

       	//$group_buy_id=52;
       	//$id=2389;

        if($group_buy_id){
            
            $group=$this->loadModel('group_buy')->getGroup($group_buy_id,$this->loginUser['id']);

            $displayName = $this->loadModel('user')->getUserDisplayName($group['create_user_id']);
            $this->setData($displayName,'groupOwnerName');

            $this->setData( $displayName."的团".' - '.$coupon['title'].' - '.$this->site['pageTitle'] , 'pageTitle' );

            $pic= $this->loadModel('coupons')->db->selectOne( 'pic','cc_coupons', "id=".$group['coupon_id'] );
           	$group['coupon_pic']=$pic[0];

            if($group)$group=array($group);
            $this->setData($group,'groups');

        }else{
            $groups=$this->loadModel('group_buy')->getAvailabelGroups($id,$this->loginUser['id']);
            $this->setData($groups,'groups');

            $myCreatedGroup=$this->loadModel('group_buy')->getMyCreatedGroup($this->loginUser['id'],$id);
            $this->setData($myCreatedGroup,'myCreatedGroup');

            $displayName = $this->loadModel('user')->getUserDisplayName($groups[0]['create_user_id']);
            $this->setData($displayName,'groupOwnerName');
           
        }

        $this->display('group_buy/index');
	}

	function index_mobile_action()
	{	
		//group id
		$id = get2('group_buy_id');
		$mdl_group = $this->loadModel('group_buy');
		
		// group info
		$group = $mdl_group->getGroup($id);
		if(!$group)$this->sheader(null,'该团购已经不在了');
		$this->setData($group,'group');

		// group coupon info
		$coupon = $this->loadModel('coupons')->get($group['coupon_id']);
		$this->loadModel('coupons')->caculatePriceAndPoint($coupon);
		$this->setData($coupon,'coupon');

		// winning condition
		$conditions=$group['settings']->get_conditions();
		$this->setData($conditions,'conditions');

		$reward_type=$group['reward_type'];
		if($reward_type==2){	
			//flat
			$reward_type_text='Dollar OFF';
		}else if($reward_type==1){
			//percent
			$reward_type_text='% OFF';
		}
		$this->setData($reward_type_text,'reward_type_text');
		//userList
		$this->setData($mdl_group->currentGroupSize($id),'current');

		$data=$mdl_group->getGroupList($id);
		$this->setData(array_reverse($data),'data');

		$reward_msg=$mdl_group->getRewardMsg($id);
		$this->setData($reward_msg,'reward_msg');
		
		$isInGroup=$mdl_group->isAlreadyInGroup($id,$this->loginUser['id']);
		$this->setData($isInGroup,'isInGroup');

		$isAlreadyBuy=$mdl_group->isAlreadyBuy($id,$this->loginUser['id']);
		$this->setData($isAlreadyBuy,'isAlreadyBuy');

		$jsTrigger = get2('jstrigger');
		$this->setData($jsTrigger,'jsTrigger');

		//wx share
		require_once "wx/wxjssdk.php";
        $jssdk = new WXjsSDK();
        $signPackage = $jssdk->GetSignPackage();
        $this->setData($signPackage,'signPackage');

		if($group['allow_user_group']==1&&$group['parentId']==0){
			$this->display('group_buy/index_mobile_new_mastergroup');
		}else{
			$this->display('group_buy/index_mobile_new');
		}
		
	}

	public function list_action()
	{
		$sql = "SELECT g.id,g.name,g.status,g.max_user_group,g.condition_level,g.allow_user_group,c.pic FROM `cc_group_buy_status` as g left join cc_coupons as c on g.coupon_id=c.id";

		$sql .= " where g.status !=5 and g.status !=2 "; //[ g.parentId=0 ]child 自开团不显示 //已完成不显示//关闭的不显示

		$sql .= " ORDER BY
				   CASE g.status
				      WHEN '1' THEN 1
				      WHEN '3' THEN 2
				      WHEN '4' THEN 3
				      WHEN '5' THEN 4
				      WHEN '2' THEN 5
				      WHEN '0' THEN 6
				   END, id desc";

		$list = $this->loadModel('group_buy')->getListBySql($sql);

		foreach ($list as $key => $value) {
			$list[$key]['size'] = end(array_keys(unserialize($value['condition_level'])));
		}
		$this->setData($list,'list');

		//wx share
		require_once "wx/wxjssdk.php";
        $jssdk = new WXjsSDK();
        $signPackage = $jssdk->GetSignPackage();
        $this->setData($signPackage,'signPackage');
	        
		$this->display('group_buy/list');
	}

	function group_manage_action(){
		
		$mdl_group=$this->loadModel('group_buy');

		$groups=$mdl_group->getAllGroups($this->loginUser['id']);
		$this->setData($groups,'groups');

		$this->setData('group_manage','submenu');
		$this->setData('community','menu');

		$this->setData( 'groupbuy', 'mobile_menu' );

		$this->display_pc_mobile('group_buy/manage_group','group_buy/manage_group_mobile');
	}

	function child_group_list_action(){
		$masterGroupId=get2('id');

		$mdl_group=$this->loadModel('group_buy');

		$group = $mdl_group->getGroup($masterGroupId);
		if(!$group)$this->sheader(null,'该团购已经不在了');
		$this->setData($group,'masterGroup');

		$groups=$mdl_group->getAllChildGroups($masterGroupId);
		$this->setData($groups,'groups');

		$this->setData('group_manage','submenu');
		$this->setData('','menu');

		$this->setData( 'groupbuy', 'mobile_menu' );

		$this->display_pc_mobile('group_buy/manage_group_child_mobile','group_buy/manage_group_child_mobile');
	}

	public function create_group_view_action($value='')
	{	
		$mdl_coupons= $this->loadModel('coupons');
		$couponList = $mdl_coupons->getCouponList($this->loginUser['id']);
		$this->setData($couponList,'couponList');

		$this->display('group_buy/create_group_view');
	}

	function create_group_action(){
		$mdl_group=$this->loadModel('group_buy');

		$couponId=post('couponId');
		$name = post('name');
		$description = post('description');
		$createUserId=$this->loginUser['id'];
		$group_setting_option=post('group_setting_option');

		$start_time=strtotime(post('start_time'));
		$end_time=strtotime(post('end_time'));

		if($couponId==-1)$this->form_response_msg('请选择产品');

		$option = new GroupOption();
		$option->set_auto_start_time($start_time);
		$option->set_auto_end_time($end_time);

		if($group_setting_option=='basic'){
			//basic
			$complete_condition=post('complete_condition');
			$reward_type=post('reward_type');
			$complete_value=post('complete_value');
			$reward_value=post('reward_value');

			if(!$complete_value)$complete_condition=GroupOption::CONDITION_UNLEMITED;
			if(!$reward_value)$reward_type=GroupOption::REWARD_TYPE_NONE;

			$option->set_complete_condition($complete_condition);
			$option->set_reward_type($reward_type);
			if($complete_value || $reward_value)$option->add_condition_level($complete_value,$reward_value);
			
		}elseif($group_setting_option=='advanced'){
			//advance
			$complete_condition=post('adv_complete_condition');
			$reward_type=post('adv_reward_type');
			$condition = post('condition');

			$auto_reward=post('auto_reward');
			$allow_user_group=post('allow_user_group');
			$max_user_group=post('max_user_group');

			if(!$condition){
				$complete_condition=GroupOption::CONDITION_UNLEMITED;
				$reward_type=GroupOption::REWARD_TYPE_NONE;
			}

			$option->set_complete_condition($complete_condition);
			$option->set_reward_type($reward_type);

			foreach ($condition as $con) {
				$option->add_condition_level($con['condition'],$con['reward']);
			}

			if($allow_user_group=='1'){
				$option->enable_user_group($max_user_group);
			}else{
				$option->disbale_user_group();
			}

			if($auto_reward=='1'){
				$option->enable_auto_reward();
			}else{
				$option->disable_auto_reward();
			}
		}

		if($mdl_group->groupCreate($couponId,$name,$createUserId,$description,$option)){
			$this->form_response(200,'添加成功'.$coupinId .$group_name.$group_size,HTTP_ROOT_WWW.'group_buy/group_manage');
		}else{
			$this->form_response_msg('添加失败');
		}
		
	}

	function group_delete_action(){
		$id = get2('id');
		$this->loadModel('group_buy')->groupDelete($id);
		$this->sheader(HTTP_ROOT_WWW.'group_buy/group_manage');
	}

	function group_open_action(){
		$id = get2('id');
		$this->loadModel('group_buy')->groupOpen($id);
		$this->sheader(HTTP_ROOT_WWW.'group_buy/group_manage');
	}

	function group_close_action(){
		$id = get2('id');
		$this->loadModel('group_buy')->groupClose($id);
		$this->sheader(HTTP_ROOT_WWW.'group_buy/group_manage');
	}

	function give_reward_and_complete_group_action(){
		$groupId = get2('id');

		$mdl_group=$this->loadModel('group_buy')->give_reward_and_complete_group($groupId);

		$this->sheader(HTTP_ROOT_WWW.'group_buy/group_manage');
	}

	function give_reward_at_max_level_action(){
		$groupId = get2('id');

		$mdl_group=$this->loadModel('group_buy')->give_reward_at_max_level($groupId);

		$this->sheader(HTTP_ROOT_WWW.'group_buy/group_manage');
	}

	function group_list_show_ajax_action(){
		$id = get2('id');
		$type = get2('type');
		$mdl_group=$this->loadModel('group_buy');
		
		$this->setData($mdl_group->currentGroupSize($id),'current');

		$data=$mdl_group->getGroupList($id);
		$this->setData(array_reverse($data),'data');

		$reward_msg=$mdl_group->getRewardMsg($id);
		$this->setData($reward_msg,'reward_msg');

		$this->setData($this->condition_level_html($id),'condition_level_html');

		if($type=='user'){
			echo $this->fetch('group_buy/group_list_show_user');
		}elseif($type=='admin'){
			echo $this->fetch('group_buy/group_list_show_admin');
		}else{
			$this->display('group_buy/group_list_show_admin');
		}
		
	}

	function group_list_show_action(){
		//商家view only
		$id = get2('id');
		$mdl_group=$this->loadModel('group_buy');
		
		$this->setData($mdl_group->currentGroupSize($id),'current');

		$data=$mdl_group->getGroupList($id);
		$this->setData(array_reverse($data),'data');

		$reward_msg=$mdl_group->getRewardMsg($id);
		$this->setData($reward_msg,'reward_msg');

		$this->setData($this->condition_level_html($id),'condition_level_html');

		$this->setData( 'groupbuy', 'mobile_menu' );
		
		$this->display('group_buy/group_list_show_admin_mobile');
	}


	function condition_level_html($id){
		$mdl_group=$this->loadModel('group_buy');
		$group=$mdl_group->getGroup($id);
		$conditions=$group['settings']->get_conditions();
		$this->setData($conditions,'conditions');

		$complete_condition=$group['complete_condition'];
		$reward_type=$group['reward_type'];
		if($complete_condition==1){
			//成员(单人模式)
			$condition_type_text ='人';
		}else if($complete_condition==2){
			//成员(单人模式)
			$condition_type_text ='人';
		}else if($complete_condition==3){
			//商品
			$condition_type_text ='件';
		}

		if($reward_type==2){	
			//flat
			$reward_type_text='Dollar OFF';
		}else if($reward_type==1){
			//percent
			$reward_type_text='% OFF';
		}

		$this->setData($condition_type_text,'condition_type_text');
		$this->setData($reward_type_text,'reward_type_text');
		$html=$this->fetch('group_buy/condition_level_html');
		return $html;

	}

	function create_user_group_action(){
		$groupId = get2('groupId');
		$groupName = trim(get2('groupName'));
		$userId = $this->loginUser['id'];
		
		$mdl_group=$this->loadModel('group_buy');
		$couponId=$mdl_group->getGroupCouponId($groupId);
		if(!$this->loginUser['phone'])$this->form_response(200,'个人信息不全,请补全手机号及个人信息',HTTP_ROOT_WWW.'company/fill_profile?action=autoCreate&g_id='.$groupId.'&g_name='.$groupName);
		
		$id=$mdl_group->userCreateGroup($groupId,$groupName,$userId);
		if($id){
			$this->form_response(200,"开团成功 ID:".$id, HTTP_ROOT_WWW."group_buy/index?jstrigger=joinaftercreate&group_buy_id=".$id);
		}else{
			$this->form_response_msg('现在无法开团');
		}

	}

	//user side action
	function join_group_action(){
		$groupId = get2('groupId');
		$numberOfPeople=get2('numberOfPeople');
		$userId = $this->loginUser['id'];

		$mdl_group=$this->loadModel('group_buy');
		$couponId= $mdl_group->getGroupCouponId($groupId);

		if(!$mdl_group->isGroupRunning($groupId)){
			$this->form_response_msg('Group is not open Yet');
		}

		if($mdl_group->isAlreadyInGroup($groupId,$userId)){
			$this->form_response_msg('Already in Group');
		}

		if(!$this->loginUser['phone'])$this->form_response(100,'个人信息不全,请补全手机号及个人信息',HTTP_ROOT_WWW.'company/fill_profile?action=autoJoin&g_id='.$groupId.'&g_qty='.$numberOfPeople);

		$data = array(['coupon_id'=>$couponId,'qty'=>$numberOfPeople]);
		$orderId = $this->loadModel('group_buy')->createOrder($data);

		if($mdl_group->joinGroup($groupId,$userId,$orderId)){
			$mdl_group->recalculateGroupStatus($groupId);
			$this->form_response(200,'成功加入该团！',HTTP_ROOT_WWW.'group_buy/manage_joined_group');
		}
	}

	function leave_group_action(){
		$userId = $this->loginUser['id'];
		$groupId = get2('group_id');
		$mdl_group=$this->loadModel('group_buy');


		if(!$mdl_group->isGroupRunning($groupId)){
			$this->form_response_msg('Can not leave');
		}

		if(!$mdl_group->isAlreadyInGroup($groupId,$userId)){
			$this->form_response_msg('You do not exist in this group');
		}

		if($this->loadModel('group_buy')->leaveGroup($groupId,$userId)){
			$mdl_group->recalculateGroupStatus($groupId);
			$this->form_response(200,'成功退出该团！',HTTP_ROOT_WWW.'group_buy/manage_joined_group');
		}
	}

	function manage_joined_group_action(){
		$data = $this->loadModel('group_buy')->getJoinedGroups($this->loginUser['id']);
		$this->setData($data,'groups');

		$this->setData('manage_joined_group','submenu');
		$this->setData('community','menu');


		$this->setData( 'groupbuy', 'mobile_menu' );

		$this->display_pc_mobile('group_buy/manage_joined_group','group_buy/manage_joined_group_mobile');
	}

	function test_action(){
		//echo $this->loadModel('group_buy')->userCreateGroup(20,'chris 自开团',123456);
	}


}
?>