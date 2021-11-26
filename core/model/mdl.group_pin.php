<?php 
/**
 * 	拼多多拼团模式
 * 	入团时必须购买
 * 	成团后发货
 * 	失败后退款
 */
class mdl_group_pin extends mdl_base{
	protected $tableName = '#@_group_pin';
	protected $tableName_user_group = '#@_group_pin_user_group';
	protected $tableName_list = '#@_group_pin_list';

	private $id;

	/**
	 * 团基于的产品
	 * @var int
	 */
	private $coupon_id;

	/**
	 * 创建用户
	 * @var int
	 */
	private $create_user_id;

	/**
	 * 单个团的人数要求
	 * @var int
	 */
	private $group_size_each;

	/**
	 * 可拼团的最大数量
	 * @var int
	 */
	private $group_size_total;

	/**
	 * 拼团奖励：为生成好的折扣码。用于直接引导用户通过折扣码购买。
	 * @var [type]
	 */
	private $reward;

	private $reward_type;

	private $reward_value;

	/**
	 * 状态：开 关
	 */
	private $status;
	const STATUS_OPEN=0;
	const STATUS_CLOSE=1;

	/**
	 * 时间限制
	 */
	private $time_limit;
	const DEFAULT_TIME_LIMIT = 3600*48; // 2 days

	/**
	 * 创建时间
	 * @var int 11
	 */	
	private $gen_date;




    public function realiseGroupPinRecord($promotionId,$user_id,$order_id,$user_group_id)
    {
        //如果使用的折扣码是拼多多团购生成的，用户完成购买后会相应的加入团
        
        $code = loadModel('wj_promotion_code')->getPromotionCodeById($promotionId);

        $group=$this->getByWhere(array('reward'=>$code['promotion_code']));
        $group_id = $group['id'];

        if($group['id']&&$user_group_id){
            $this->joinGroup($user_id,$user_group_id,$order_id);

        }elseif($group['id']){
            $this->createAndJoinGroup($user_id,$group_id,$order_id);

        }
        
    }


	/**
	 * 去拼单
	 * @param  int $user_id       用户ID
	 * @param  int $user_group_id 用户团ID
	 * @param  string $order_id   订单ID
	 * @return boolean            加入是否成功
	 */
	public function joinGroup($user_id,$user_group_id,$order_id)
	{	
		/**
		 * Check
		 */
		if(!$this->isUserGroupOpen($user_group_id)){
			return false;
		}

		/**
		 * Action
		 */
		$mdl_group_pin_list = loadModel('group_pin_list');

		$data=array();

		$data['user_group_id']=$user_group_id;
		$data['user_id']=$user_id;
    	$data['order_id']=$order_id;
    	$data['gen_date']=time();

    	$joinSuccess=$mdl_group_pin_list->insert($data);

    	/**
    	 * Check
    	 */
    	if($this->isUserGroupFull($user_group_id)){
			$this->updateUserGroupComplete($user_group_id);
		}

		return $joinSuccess;

	}

	/**
	 * 一键拼单					
	 * @param  [type] $user_id  [description]
	 * @param  [type] $group_id [description]
	 * @param  [type] $order_id [description]
	 * @return [type]           [description]
	 */
	public function createAndJoinGroup($user_id,$group_id,$order_id)
	{	
		$user_group_id=$this->createUserGroup($group_id);

		if($user_group_id){
			$this->joinGroup($user_id,$user_group_id,$order_id);

			return $user_group_id;
		}else{

			return false;
		}
		
	}


    public function createUserGroup($group_id)
    {	
    	/**
    	 * Check
    	 */
    	if($this->isGroupClose($group_id)||$this->isGroupFull($group_id))return false;

    	/**
    	 * Action
    	 */
    	$mdl_group_pin_user_group = loadModel('group_pin_user_group');

    	$data=array();

    	$data['group_id']=$group_id;
    	$data['status']=0;
    	$data['gen_date']=time();

    	return $mdl_group_pin_user_group->insert($data);
    }

    public function getUserGroupList($group_id,$limit=null,$order=null)
    {
    	$mdl_group_pin_user_group = loadModel('group_pin_user_group');

    	$where['group_id']=$group_id;

    	return $mdl_group_pin_user_group->getList(null,$where,$order,$limit);
    }

    public function getUserGroupUserList($user_group_id,$flag=false,$limit=null)
    {
    	$mdl_group_pin_list = loadModel('group_pin_list');
        $mdl_user = loadModel('user');

    	$where['user_group_id']=$user_group_id;
    	
    	$list= $mdl_group_pin_list->getList(null,$where,$limit);

        if($flag){
            // additional user info
            foreach ($list as $key => $value) {
                $list[$key]['user_name']=$mdl_user->getUserDisplayName($value['user_id']);
                $list[$key]['user_logo']=$mdl_user->getAvatar($value['user_id']);
            }
        }

        return $list;
    }

    public function isUserGroupFull($user_group_id)
    {
    	$setting= $this->getGroupSettingByUserGroupId($user_group_id);
    	$list = $this->getUserGroupUserList($user_group_id);

    	return (sizeof($list)>=$setting['group_size_each']);
    }

    public function isGroupFull($group_id)
    {
    	$setting= $this->getGroupSettingByGroupId($group_id);
    	$list = $this->getUserGroupList($group_id);

    	return (sizeof($list)>=$setting['group_size_total']);
    }

    public function isUserGroupExpire($user_group_id)
    {
    	$setting= $this->getGroupSettingByUserGroupId($user_group_id);
    	$user_group = loadModel('group_pin_user_group')->get($user_group_id);

    	return ((time()-$user_group['gen_date'])>$setting['time_limit']);
    }

    public function getGroupSettingByGroupId($group_id)
    {
    	$setting = $this->get($group_id);

    	return $setting;
    }

    public function getGroupSettingByUserGroupId($user_group_id)
    {
    	$mdl_group_pin_user_group = loadModel('group_pin_user_group');
    	$userGroup=$mdl_group_pin_user_group->get($user_group_id);

    	return $this->getGroupSettingByGroupId($userGroup['group_id']);
    }

    public function hasGroupPin($coupon_id)
    {	
    	$where['coupon_id']=$coupon_id;
    	return $this->getByWhere($where);
    }

    public function hasUserGroup($order_id){
        $mdl_group_pin_list = loadModel('group_pin_list');
        $where['order_id']=$order_id;
        $record= $mdl_group_pin_list->getByWhere($where);

        return $record['user_group_id'];
    }

    public function updateUserGroupComplete($user_group_id)
    {
    	$mdl_group_pin_user_group = loadModel('group_pin_user_group');

    	$data['last_update_date']=time();
    	$data['status']=mdl_group_pin_user_group::STATUS_COMPLETE;

    	return $mdl_group_pin_user_group->update($data,$user_group_id);
    }

    public function updateUserGroupExpire($user_group_id)
    {
    	$mdl_group_pin_user_group = loadModel('group_pin_user_group');

    	$data['last_update_date']=time();
    	$data['status']=mdl_group_pin_user_group::STATUS_EXPIRE;

    	return $mdl_group_pin_user_group->update($data,$user_group_id);
    }

     public function updateUserGroupOpen($user_group_id)
    {
        $mdl_group_pin_user_group = loadModel('group_pin_user_group');

        $data['last_update_date']=time();
        $data['status']=mdl_group_pin_user_group::STATUS_OPEN;

        return $mdl_group_pin_user_group->update($data,$user_group_id);
    }

     public function isUserGroupOpen($user_group_id)
    {
    	$mdl_group_pin_user_group = loadModel('group_pin_user_group');
    	$userGroup= $mdl_group_pin_user_group->get($user_group_id);

    	return ($userGroup['status']==mdl_group_pin_user_group::STATUS_OPEN);
    }

    public function updateGroupOpen($group_id)
    {	
    	$data['status']=self::STATUS_OPEN;
    	return $this->update($data,$group_id);
    }

    public function updateGroupClose($group_id)
    {	
    	$data['status']=self::STATUS_CLOSE;
    	return $this->update($data,$group_id);
    }

    public function isGroupClose($group_id)
    {
    	$group = $this->get($group_id);

    	return ($group['status']==self::STATUS_CLOSE);

    }

    public function isGroupPinCode($specialGroupBuyCheckoutCode)
    {
        $where['reward']=$specialGroupBuyCheckoutCode;
        $data = $this->getByWhere($where);
        return ($data!=null);
    }



}



?>