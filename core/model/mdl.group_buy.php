<?php 

class GroupOption{
	const DBK_COMPLETE_CONDITION='complete_condition';
	const DBK_REWARD_TYPE='reward_type';
	const DBK_CONDITION_LEVEL='condition_level';
	const DBK_ALLOW_USER_GROUP='allow_user_group';
	const DBK_MAX_USER_GROUP='max_user_group';
	const DBK_AUTO_REWARD='auto_reward';
	const DBK_START_TIME='start_time';
	const DBK_END_TIME='end_time';

	private $group_complete_condition;

	const CONDITION_PEOPLE=1;
	/* each person count for 1 in the group
	*/ 

	const CONDITION_PEOPLE_MULTI=2;
	/* each person can enter a number for whatever nember of people he represents
	*/ 

	const CONDITION_ITEM=3;
	

	const CONDITION_UNLEMITED=0;
	/* group will keep going until the owner manully finalize it.
	*/ 

	private $reward_type;

	const REWARD_TYPE_FLAT=2;
	const REWARD_TYPE_PERCENT=1;
	const REWARD_TYPE_NONE=0;

	private $condition_level;

	private $allow_user_group; 
	/* special group type. the group itself is not joinable. User make a copy (open) of it and run as another group. the reward is set by the originated creator
	*/ 
	private $max_user_group;
	/* max number of user group that is allowed to be created.
	*/ 

	private $auto_reward;
	/* at finalize stage, people still can leave. auto_reward will auto close it.
	*/ 

	private $start_time;
	private $end_time;

	function GroupOption($data=null){
		if($data){
			$this->init_with_data($data);
		}else{
			$this->init_with_default();
		}		
	}

	function init_with_default(){
		$this->group_complete_condition=self::CONDITION_UNLEMITED;
		$this->reward_type=self::REWARD_TYPE_NONE;
		$this->condition_level=array();
		$this->auto_reward=false;
		$this->max_user_group=0;
		$this->allow_user_group=false;
		$this->start_time=0;
		$this->end_time=0;
	}

	function init_with_data($data){
		$this->group_complete_condition			=$data[self::DBK_COMPLETE_CONDITION];
		$this->reward_type 						=$data[self::DBK_REWARD_TYPE];
		$this->condition_level 					=unserialize($data[self::DBK_CONDITION_LEVEL]) ;
		$this->allow_user_group					=(bool)$data[self::DBK_ALLOW_USER_GROUP];
		$this->max_user_group 					=$data[self::DBK_MAX_USER_GROUP];
		$this->auto_reward 						=(bool)$data[self::DBK_AUTO_REWARD];
		$this->start_time 						=$data[self::DBK_START_TIME];
		$this->end_time 						=$data[self::DBK_END_TIME];
	}

	function set_complete_condition($condition){
		$this->group_complete_condition=$condition;
	}
	function get_complete_condition($condition){
		return $this->group_complete_condition;
	}
	function set_reward_type($type){
		$this->reward_type=$type;
	}
	function add_condition_level($condition,$reward){
		$this->condition_level[$condition]=$reward;
	}
	function get_conditions(){
		return $this->condition_level;
	}
	function get_max_condition(){
		return end(array_keys($this->condition_level));
	}
	function enable_user_group($max=0){
		if($max>0){
			$this->allow_user_group=true;
			$this->max_user_group=$max;
		}
	}
	function disbale_user_group(){
		$this->allow_user_group=false;
	}
	function allow_user_group(){
		return $this->allow_user_group;
	}
	function enable_auto_reward(){
		$this->auto_reward=true;
	}
	function disable_auto_reward(){
		$this->auto_reward=false;
	}
	function auto_reward(){
		return $this->auto_reward;
	}
	function set_auto_start_time($time){
		$this->start_time=$time;
	}
	function set_auto_end_time($time){
		$this->end_time=$time;
	}

	function to_db_array(){
		$data=array();
		$data[self::DBK_COMPLETE_CONDITION]					=$this->group_complete_condition;
		$data[self::DBK_REWARD_TYPE]						=$this->reward_type;
		$data[self::DBK_CONDITION_LEVEL]					=serialize($this->condition_level);
		$data[self::DBK_ALLOW_USER_GROUP]					=$this->allow_user_group;
		$data[self::DBK_MAX_USER_GROUP]						=$this->max_user_group;
		$data[self::DBK_AUTO_REWARD]						=$this->auto_reward;
		$data[self::DBK_START_TIME]							=$this->start_time;
		$data[self::DBK_END_TIME]							=$this->end_time;
		return $data;
	}

}

class mdl_group_buy extends mdl_base{
	protected $tableName = '#@_group_buy';
	protected $status_tableName = 'cc_group_buy_status';
	protected $order_tableName ='cc_group_buy_order';

	const STATUS_READY=0;
	/* After the creation of a group and before opening
	*  user see it as comming soon, not joinable
	*  admin see it as ready, available action: Open
	*/ 
	const STATUS_OPEN=1;
	/* After the open of a group 
	*  user see it as running , joinable, available action: Leave
	*  admin see it as running, available action: Close
	*/ 
	const STATUS_CLOSE=2;
	/* After the close of a group 
	*  user see it as close, not joinable.
	*  admin see it as close, available action: Open
	*/ 
	const STATUS_STAGING=3;
	/* After the group reach its rewarding level 
	*  user see it as staging,  joinable. available action: Leave
	*  admin see it as staging, available action:  give reward
	*/ 
	const STATUS_FINALIZING=4;
	/* After the group reach its max rewarding level
	*  user see it as FINALIZING ,not joinable. available action: Leave
	*  admin see it as FINALIZING, available action: give reward.
	*/ 
	const STATUS_COMPLETE=5;
	/* After the group receiv reward
	*  user see it as complete .
	*  admin see it as complete.
	*/ 

	function joinGroup($groupId,$userId,$orderId=null){
		$data['group_id']=$groupId;
		$data['user_id']=$userId;
		$data['order_id']=$orderId;
		return $this->insert($data);
	}

	function leaveGroup($groupId,$userId){
		$where['group_id']=$groupId;
		$where['user_id']=$userId;

		$entry=$this->getByWhere($where);
		$this->deleteOrder($entry['order_id']);

		return $this->deleteByWhere( $where );
	}

	function isAlreadyInGroup($groupId,$userId){
		$where['group_id']=$groupId;
		$where['user_id']=$userId;
		$result = $this->getByWhere($where);

		return (sizeof($result)!=0);
	}

	function isAlreadyBuy($groupId,$userId){
		$where['group_id']=$groupId;
		$where['user_id']=$userId;
		$result = $this->getByWhere($where);

		if(sizeof($result)==0) return false;
		if($result['real_order_id']){
			return $result['real_order_id'];
		}else{
			return false;
		}
	}

	function isGroupRunning($groupId){
		$status= $this->getGroupStaus($groupId);
		return ($status==self::STATUS_OPEN || $status==self::STATUS_STAGING);
	}
	
	function currentGroupSize($groupId){
		if($this->getGroupCompleteCondition($groupId)==GroupOption::CONDITION_PEOPLE){
			$where['group_id']=$groupId;
			$count = $this->getCount($where);
			if($count==null)$count=0;
			return $count;
		}elseif($this->getGroupCompleteCondition($groupId)==GroupOption::CONDITION_PEOPLE_MULTI){
			$sql = "SELECT sum(gbo.qty) as total from  cc_group_buy as gb left join cc_group_buy_order as gbo on gbo.order_id = gb.order_id WHERE gb.group_id ='$groupId' ";
			$result= $this->getListBySql($sql);
			$count = ($result[0]['total']==null)?0:$result[0]['total'];
			return $count;
		}
		
	}
	function getGroupList($groupId){
		$sql = "SELECT 
					g.user_id,
					g.order_id,
					o.qty,
					u.name as user_name,
					u.avatar as avatar,
					u.nickname as user_nickname,
					u.email as user_email, 
					u.phone as user_phone,
					g.real_order_id
				FROM cc_group_buy as g 
					left join cc_group_buy_order as o on o.order_id = g.order_id 
					left join cc_user as u on u.id = g.user_id
				WHERE g.group_id ='$groupId'";
		$result=$this->getListBySql($sql);
		return $result;
	}

	function recalculateGroupStatus($groupId){
		$group=$this->getGroup($groupId);
		$this->isGroupComplete($group);
	}
	function updateCurrentReward($groupId,$reward){
		$where = "id='$groupId'";
		$data['current_reward']=$reward;
		return $this->db->update( $data, $this->status_tableName, $where );
	}
	private function isGroupComplete($group){
		$currentReward=0;
		$isGroupComplete=false;
		$isGroupStaging=false;
		$conditions = $group['settings']->get_conditions();
		$autoReward = $group['settings']->auto_reward();

		switch ($group[GroupOption::DBK_COMPLETE_CONDITION]) {

			case GroupOption::CONDITION_PEOPLE:
			case GroupOption::CONDITION_PEOPLE_MULTI:
				$currentIndex = 0;
				$length =sizeof($conditions);
				foreach ($conditions as $key => $value) {
					if($group['current']>=$key){
						$currentReward=$value;
						if($currentIndex<$length-1){
							$isGroupStaging=true;
						}
						if($currentIndex==$length-1){
							$isGroupComplete=true;
						}
					}
					$currentIndex++;
				}
				break;

			case GroupOption::CONDITION_ITEM:
				foreach ($conditions as $key => $value) {
					
				}
				break;

			case GroupOption::CONDITION_UNLEMITED:
				$isGroupComplete=false;
				break;

			default:
				throw new Exception("Unkonw Group Complete Condition ", 1);
				break;
		}

		$this->updateCurrentReward($group['id'],$currentReward);
		if($isGroupComplete &&!$autoReward)return $this->groupFinalizing($group['id']);
		if($isGroupComplete && $autoReward){
			$this->groupFinalizing($group['id']);
			$this->give_reward_and_complete_group($group['id']);
			return;
		} 
		if($isGroupStaging)return $this->groupStaging($group['id']);
		
	}

	function groupCreate($couponId,$name,$createUserId,$desc=null,$option=null,$parentId=0){
		if($option!=null && !is_a($option, 'GroupOption'))throw new Exception("option must be a instance of GroupOption Class", 1);
		if($option!=null && is_a($option, 'GroupOption'))$data = $option->to_db_array();

		$data['name']=$name;
		$data['status']=self::STATUS_READY;
		$data['coupon_id']=$couponId;
		$data['create_user_id']=$createUserId;
		$data['description']=$desc;
		$data['current_reward']=0;
		$data['parentId']=$parentId;
		$this->db->insert( $data, $this->status_tableName );
		return $this->db->insert_id();
	}

	function userCreateGroup($groupId,$name,$createUserId){
		if($this->isGroupRunning($groupId)==false)return false;
		if($this->userGroupLimitReached($groupId))return false;

		$group=$this->getGroup($groupId);
		$group['settings']->disbale_user_group();

		$id= $this->groupCreate($group['coupon_id'],$name,$createUserId,$group['description'],$group['settings'],$groupId);
		//auto open
		$this->groupOpen($id);

		//creater auto join// move to front js trigger
		// $data = array(['coupon_id'=>$group['coupon_id'],'qty'=>'1']);
		// $orderId = $this->createOrder($data);
		// $this->joinGroup($id,$createUserId,$orderId);

		return $id;
	}
	function getMyCreatedGroup($userId,$couponId){
		$sql = "SELECT gs.* FROM `cc_group_buy_status` as gs  WHERE gs.create_user_id='$userId' and gs.coupon_id = '$couponId' and parentId!=0";
		return $this->getListBySql($sql);
	}

	private function userGroupLimitReached($groupId){
		$group=$this->getGroup($groupId);
		if($group['settings']->allow_user_group()==false)return true;

		$max = $group['max_user_group'];
		$count = $this->childGroupCount($groupId);

		if($count==$max)return true;
		if($count>$max)throw new Exception("UserGroupExceedLimit count:".$count." Max:".$max, 1);
		if($count<$max)return false;
		
	}
	private function childGroupCount($groupId){
		$where['parentId']=$groupId;
		$count = $this->db->getCount( $this->status_tableName, $where );
		return $count;
	}

	function getAllGroups($createUserId){
		$sql = "SELECT gs.*,c.id as c_id,c.title,c.pic FROM `cc_group_buy_status` as gs left join cc_coupons as c on c.id=gs.coupon_id WHERE create_user_id='$createUserId' ";
		return $this->getGroupListFromSql($sql);
	}

	function getAllChildGroups($masterGroupId)
	{
		$sql = "SELECT gs.*,c.id as c_id,c.title,c.pic FROM `cc_group_buy_status` as gs left join cc_coupons as c on c.id=gs.coupon_id WHERE gs.parentId='$masterGroupId'";
		return $this->getGroupListFromSql($sql);
	}

	function getAvailabelGroups($couponId,$userId=null){
		$sql = "SELECT gs.* FROM `cc_group_buy_status` as gs  WHERE gs.coupon_id='$couponId' and parentId = 0";
		return $this->getGroupListFromSql($sql,$userId);
	}

	function groupDelete($groupId){
		$where['group_id']=$groupId;
		$this->deleteByWhere($where);
		return $this->db->delete( $this->status_tableName, "id='$groupId'");
	}

	function groupOpen($groupId){
		return $this->updateGroupStatus($groupId,self::STATUS_OPEN);
	}

	function groupClose($groupId){
		return $this->updateGroupStatus($groupId,self::STATUS_CLOSE);
	}

	function groupComplete($groupId){
		return $this->updateGroupStatus($groupId,self::STATUS_COMPLETE);
	}

	function groupStaging($groupId){
		return $this->updateGroupStatus($groupId,self::STATUS_STAGING);
	}

	function groupFinalizing($groupId){
		return $this->updateGroupStatus($groupId,self::STATUS_FINALIZING);
	}

	function give_reward_and_complete_group($groupId){
		$mdl_promotionCode=loadModel('wj_promotion_code');

		$group=$this->getGroup($groupId);

		if($group['status']!=self::STATUS_STAGING && $group['status']!=self::STATUS_FINALIZING){
			return false;
		}

		switch ($group[GroupOption::DBK_REWARD_TYPE]) {
			case GroupOption::REWARD_TYPE_FLAT:
				$type=PromotionCode::TYPE_FIXEDAMOUNT;
				break;
			case GroupOption::REWARD_TYPE_PERCENT:
				$type=PromotionCode::TYPE_PERCENTAGE;
				break;
			case GroupOption::REWARD_TYPE_NONE:
				$type=null;
				break;
			default:
				throw new Exception("Unknown REWARD TYPE ", 1);
				break;
		}

		if($type){
			$pcode = new PromotionCode();
			$pcode->setUserId(loadModel('coupons')->getCreateUserId($group['coupon_id']));
			$pcode->setCouponId($group['coupon_id']);
			$pcode->setDescription('凑团奖励');
			$pcode->setType($type, $group['current_reward']);
			//$pcode->setExpireType(PromotionCode::EXPIRETYPE_FIXEDQTY,$group['current']);
			$pcode->setCode(PromotionCode::RANDOM_CODE);
			$mdl_promotionCode->addPromotionCode($pcode);
			$this->updateCurrentReward($group['id'],$pcode->getCode());
		}

		$this->groupComplete($group['id']);

		$this->sendNotification($group['id']);
	}
	function give_reward_at_max_level($groupId)
	{
		$mdl_promotionCode=loadModel('wj_promotion_code');

		$group=$this->getGroup($groupId);
		
		$max_reward=end(unserialize($group['condition_level']));

		switch ($group[GroupOption::DBK_REWARD_TYPE]) {
			case GroupOption::REWARD_TYPE_FLAT:
				$type=PromotionCode::TYPE_FIXEDAMOUNT;
				break;
			case GroupOption::REWARD_TYPE_PERCENT:
				$type=PromotionCode::TYPE_PERCENTAGE;
				break;
			case GroupOption::REWARD_TYPE_NONE:
				$type=null;
				break;
			default:
				throw new Exception("Unknown REWARD TYPE ", 1);
				break;
		}

		if($type){
			$pcode = new PromotionCode();
			$pcode->setUserId(loadModel('coupons')->getCreateUserId($group['coupon_id']));
			$pcode->setCouponId($group['coupon_id']);
			$pcode->setDescription('凑团奖励');
			$pcode->setType($type, $max_reward);
			$pcode->setExpireType(PromotionCode::EXPIRETYPE_FIXEDQTY,$group['current']);
			$pcode->setCode(PromotionCode::RANDOM_CODE);
			$mdl_promotionCode->addPromotionCode($pcode);
			$this->updateCurrentReward($group['id'],$pcode->getCode());
		}

		$this->groupComplete($group['id']);

		$this->sendNotification($group['id']);
	}

	private function updateGroupStatus($groupId,$status){
		$where = "id='$groupId'";
		$data['status']=$status;
		return $this->db->update( $data, $this->status_tableName, $where );
	}

	function getGroupStaus($groupId){
		$result = $this->getGroupRawData($groupId);
		return $result['status'];
	}
	function getGroupCompleteCondition($groupId){
		$result = $this->getGroupRawData($groupId);
		return $result[GroupOption::DBK_COMPLETE_CONDITION];
	}

	function getGroupRawData($groupId){
		$where = "id='$groupId'";
		$result = $this->db->selectOne( null, $this->status_tableName, $where );
		return $result;
	}

	function getJoinedGroups($userId){
		$sql = "SELECT gb.user_id,gb.real_order_id, gs.*,c.id as c_id, c.title,c.pic FROM cc_group_buy as gb left join  cc_group_buy_status as gs on gb.group_id = gs.id  left join cc_coupons as c on c.id=gs.coupon_id WHERE gb.user_id = '$userId'";
		return $this->getGroupListFromSql($sql);
	}

	function getGroup($groupId,$userId=null){
		$sql = "SELECT  gs.* from cc_group_buy_status as gs WHERE gs.id = '$groupId'"; 
		$result = $this->getGroupListFromSql($sql,$userId);
		return $result[0];
	}

	function getGroupCouponId($groupId){
		$group=$this->db->selectOne( null, $this->status_tableName, "id='$groupId'" );
		return $group['coupon_id'];
	}

	private function getGroupListFromSql($sql,$testUserId=null){
		$result=$this->getListBySql($sql);
		foreach ($result as $key => $value) {
			$result[$key]['settings']= new GroupOption($value);
			$result[$key]['current']=$this->currentGroupSize($value['id']);
			$result[$key]['max']=$result[$key]['settings']->get_max_condition();;

			if($testUserId)$result[$key]['isUserIn']=$this->isAlreadyInGroup($value['id'],$testUserId);
		}
		return $result;
	}

	function getRewardMsg($groupId){
		$group=$this->getGroup($groupId);
		$msg='';

		if($group['status']==mdl_group_buy::STATUS_COMPLETE ){
			$msg='恭喜您凑团已经成功，折扣码: <em>'. $group['current_reward'].'</em>';
		}else{
			switch ($group[GroupOption::DBK_REWARD_TYPE]) {
			case GroupOption::REWARD_TYPE_FLAT:
				$msg= "$".$group['current_reward'].' Discount';
				break;
			case GroupOption::REWARD_TYPE_PERCENT:
				$msg= $group['current_reward'].'% OFF';
				break;
			case GroupOption::REWARD_TYPE_NONE:
				$msg='商家没有为这个团设置任何最终奖励哦! :) ';
				break;
			default:
				throw new Exception("Unknown REWARD TYPE ", 1);
				break;
			}
		}
		return $msg;
	}

	function generateOrderId(){
		$part1=date("Y");
		$part2=time();
		$part3=rand(0,999);
		return $part1.$part2.$part3;
	}

	//$data = array(['coupon_id'=>'12345',qty=>'3'],['coupon_id'=>'12345',qty=>'3']);
	function createOrder($data){
		//INSERT INTO tbl_name (a,b,c) VALUES(1,2,3),(4,5,6),(7,8,9);
		$orderId=$this->generateOrderId();

		$keystr=" (order_id,coupon_id,qty) ";
		$valueArray=[];
		foreach ($data as $row) {
			array_push($valueArray, "(".$orderId.",".$row['coupon_id'].",".$row['qty'].")");
		}
		$valuestr=join($valueArray,",");

		$sql = "INSERT INTO ".$this->order_tableName.$keystr." VALUES ".$valuestr.";";

		if($this->db->query($sql)){
			return $orderId;
		}else{
			return false;
		}
	}

	function deleteOrder($orderId){
		$where = "order_id='$orderId'";
		return $this->db->delete( $this->order_tableName, $where );
	}
	function getOrderList($orderId){
		$sql="SELECT * from ".$this->order_tableName." where order_id=".$orderId;
		return $this->getListBySql($sql);
	}
	function getOrderTotalQty($orderId){
		return $this->db->getSum( $this->order_tableName, 'qty', ['order_id'=>$orderId] );
	}

	public function getOrderQty($userId,$code)
	{
		$sql="SELECT b.qty FROM cc_group_buy_status as g left join cc_group_buy as a on g.id = a.group_id left join cc_group_buy_order as b on a.order_id=b.order_id WHERE g.current_reward='$code' and a.user_id =$userId";
		$data=$this->getListBySql($sql);
		if(sizeof($data)==1){
			return $data[0]['qty'];
		}else{
			return 0;
			//throw new Exception("getOrderQty impossible result", 1);
		}
	}

	public function realiseGroupBuyRecord($promotionId,$userId,$orderId)
	{
		//如果使用的折扣码是团购生成的，用户的相应团购纪录在商家端显示已下单。
		//promotionId --> promotion code--> group buy status --(+ user ID)-> group buy --> real_order_id
		$sql ="SELECT gb.id as recordId FROM cc_wj_promotion_code as p left join cc_group_buy_status as gs on gs.current_reward=p.promotion_code left join cc_group_buy as gb on gb.group_id = gs.id where gb.user_id= $userId and p.id=$promotionId";

		$result = $this->getListBySql($sql);

		if(sizeof($result)==1){
			$recordId=$result[0]['recordId'];
			$data['real_order_id']=$orderId;
			$this->update($data,$recordId);	
		}
		
	}

	function test(){
		$id = $this->groupCreate(201,'1团',21511,'desc',new GroupOption());
		echo "create PASS id = '$id' status=".$this->getGroupStaus($id)."<br/>";

		$this->groupOpen($id);
		echo "open PASS status=".$this->getGroupStaus($id)."<br/>";

		$this->joinGroup($id,10001,123456);

		echo ($this->isAlreadyInGroup($id,10001))?'isAlreadyInGroup Pass':'isAlreadyInGroup Failed';
		echo "<br/>";
		echo ($this->currentGroupSize($id)==1)?'currentGroupSize Pass':'currentGroupSize Failed';
		echo "<br/>";
		echo ($this->leaveGroup($id,10001))?'leaveGroup PASS':'leaveGroup Failed';
		echo "<br/>";
		echo (!$this->isAlreadyInGroup($id,10001))?'isAlreadyInGroup Pass':'isAlreadyInGroup Failed';
		echo "<br/>";
		echo ($this->currentGroupSize($id)==0)?'currentGroupSize Pass':'currentGroupSize Failed';
		echo "<br/>";

		$this->groupClose($id);
		echo "close PASS status=".$this->getGroupStaus($id)."<br/>";

		$this->groupDelete($id);
		echo "delete PASS status=".$this->getGroupStaus($id)."<br/>";
	}

	public function sendNotification($groupid)
	{	
		//Group Owner
		$group=$this->getGroupRawData($groupid);

		$group['coupon_id'];
		$group['current_reward'];
		$group['name'];

		//User list
		$userList= $this->getGroupList($groupid);

		$checkoutUrl ="https://ubonus365.com/member/showcart?specialGroupBuyCheckoutCode=".$group['current_reward'];
		$checkoutQRCode = generateQRCode($checkoutUrl);


		$system_mailer = loadModel('system_mail');

		
		foreach ($userList as $user) {
			//Livechat Notification
			$cid=$user['user_id']; //用户id
	        $bid='25201'; //系统客服

	        //clean g_name
	        $g_name = str_replace(' ', '-', $group['name']); // Replaces all spaces with hyphens.
	        $g_name= preg_replace('/[^\pL\pS0-9]/u', '-', $g_name); // Removes special chars.

	        $msg="您在Ubonus美食生活上参加的凑团(".$g_name.")已经达成，商家已经发放了奖励。本次凑团奖励为折扣码(".$group['current_reward'].")请点击下面的链接直接使用折扣码领取奖品。+"."ubonus365.com%2Fmember%2Fshowcart%3FspecialGroupBuyCheckoutCode%3D".$group['current_reward'];

	        $content= json_encode($msg);
	        $content = substr($content, 1,-1);  
	        $content=str_replace("\\", '%', $content);
	        $url ='http://livechatserver.ubonus365.com:1500/?RequestNo=9703&cid='.$cid.'&bid='.$bid.'&chatrecord='.$content;
	        $result = file_get_contents($url);

	        //Email Notification
	        $email=$user['user_email'];

	        $subject='凑团成功--奖励发放--Ubonus美食生活';

	        $email_content='';
	        $email_content.="<h1 style='text-align:center'><img src='https://ubonus365.com/themes/zh-cn/mobile/images/logo.png'><br>恭喜您凑团已经成功！</h1>";
	        $email_content.="<p style='text-align:center'>";
			$email_content.="您在Ubonus美食生活上参加的凑团（".$group['name']."）已经达成，商家已经发放了奖励<br>";
	        $email_content.="本次凑团奖励为折扣码 (<em>".$group['current_reward']."</em>)<br>";
	        $email_content.="请点击下面的链接或者扫描二维码直接使用折扣码领取奖品。<br>";

	        $email_content.="<img width='148px' height='148px' src='".$checkoutQRCode."'><br>";

	        $email_content.="<a href='".$checkoutUrl."'>直接购买 Link</a><br>";

	        $email_content.="或者您可以通过下面的链接直接购买<br>";

	       	$email_content.="<a href='https://ubonus365.com/coupon/".$group['coupon_id']."'>产品在这里 Link</a><br>";

	       	$email_content.="提示：如果不知道登录密码，请用手机微信扫码登录。您参团的用户名：".$user['user_name'].",默认密码000000<br>";

	        $email_content.="www.ubonus365.com";
	        $email_content.="</p>";


	        $system_mailer->title($subject);
            $system_mailer->body($email_content);
            $system_mailer->to($email);

            $status=$system_mailer->send();

		}
		
	}

}


?>