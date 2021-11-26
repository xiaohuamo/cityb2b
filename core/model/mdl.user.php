<?php
class User{
	public $isApproved,
	$isAdmin,
	$wx_openID,
	$person_first_name,
	$person_last_name,
	$nickname,
	$displayName,
	$businessName,
	$abn,
	$googleMap,
	$name,
	$email,
	$phone,
	$tel,
	$password,
	$init_password,
	$role,
	$groupid,
	$createdDate,
	$lastLoginIp,
	$lastLoginDate,
	$loginCount,
	$contactPersonFirstname,
	$contactPersonLastname,

	$avatar,
	$business_plan,

	$isBusinessReferalExist,
	$referralId,
	$memberId,
	$user_belong_to_agent,
	$businessRefPointPercent,
	$customerRefPointPercent,
	$trustLevel,
	$visibleForBusiness,
	$languageType,
	$applcationStatus,  
	$isSuspended,
	$needReapprovedAfterEdit,
	
	$deliver_avaliable,
	$pickup_avaliable,

	$flat_rates_to_local_city,
	$flat_rates_national,
	$flat_rates_international,
	$amount_for_free_delivery;

	public $paypalsurcharge;
	public $royalpaysurcharge;
	public $hcashsurcharge;
	public $creditcardsurcharge;

	public $transactionFeeChargeFrom_paypal;
	public $transactionFeeChargeFrom_royalpay;
	public $transactionFeeChargeFrom_hcash;
	public $transactionFeeChargeFrom_creditcard;

	public $supportofflinepayment;
	public $supportpaypalpayment;
	public $supportroyalpaypayment;
	public $supporthcashpayment;
	public $supportcreditcardpayment;

	public $platform_commission_base;
	public $platform_commission_rate;



	const ROLE_USER=4;
	const ROLE_BUSINESS=3;
	const ROLE_STAFF=5;

	public function __construct(){
		$this->initData();
	}

	private function initData(){
		$this->isApproved=1;
		$this->isAdmin=0;
		$this->wx_openID='';
		$this->person_first_name='';
		$this->person_last_name='';
		$this->nickname='';
		$this->displayName='';
		$this->businessName='';
		$this->abn='';
		$this->googleMap='';
		$this->isAdmin='';
		$this->name='';
		$this->email='';
		$this->phone='';
		$this->phone_verified='false';
		$this->tel='';
		$this->password='';
		$this->init_password='';
		$this->role=4;
		$this->groupid=1;
		$this->createdDate=time();
		$this->lastLoginIp=ip();
		$this->lastLoginDate=time();
		$this->loginCount=1;
		$this->contactPersonFirstname='';
		$this->contactPersonLastname='';

		$this->avatar='';
		$this->business_plan='';

		$this->isBusinessReferalExist = 0;
		$this->referralId = 0;
		$this->memberId = 0;
	 	$this->user_belong_to_agent = 0;
		$this->businessRefPointPercent = 10;
		$this->customerRefPointPercent = 10;
		$this->trustLevel = 0;
		$this->visibleForBusiness = 1;
		$this->languageType = 'zh-en';
		$this->applcationStatus = 5;  //2015-3-10 自动激活状态
		$this->isSuspended = 0;
		$this->needReapprovedAfterEdit = 1;

		$this->paypalsurcharge=DEFAULT_PAYPAL_SURCHARGE;
		$this->royalpaysurcharge=DEFAULT_ROYALPAY_SURCHARGE;
		$this->hcashsurcharge=DEFAULT_HCASH_SURCHARGE;
		$this->creditcardsurcharge=DEFAULT_CREDITCARD_SURCHARGE;
		$this->transactionFeeChargeFrom_paypal='user';
		$this->transactionFeeChargeFrom_royalpay='user';
		$this->transactionFeeChargeFrom_hcash='user';
		$this->transactionFeeChargeFrom_creditcard='user';
		
		$this->supportpaypalpayment='supportpaypalpayment';
		$this->supportroyalpaypayment='supportroyalpaypayment';
		$this->supportcreditcardpayment='supportcreditcardpayment';
		$this->supporthcashpayment='';
		$this->supportofflinepayment='';

        $this->deliver_avaliable='1';
        $this->pickup_avaliable='1';
        $this->flat_rates_to_local_city='0';
        $this->flat_rates_national='0';
        $this->flat_rates_international='0';
        $this->amount_for_free_delivery='0';

        $this->platform_commission_rate=DEFAULT_PLATFORM_COMMISSION_RATE;
        $this->platform_commission_base=DEFAULT_PLATFORM_COMMISSION_BASE;
	}

	public function setFullName($person_first_name,$person_last_name){
		$this->person_first_name=$person_first_name;
		$this->person_last_name = $person_last_name;
		$this->contactPersonFirstname=$person_first_name;
		$this->contactPersonLastname=$person_last_name;
	}

	public function setNickName($nickname){
		$this->nickname=$nickname;
	}

	public function setName($name){
		$this->name=$name;
	}
	public function setPhone($phone){
		$this->phone=$phone;
	}

	public function setEmail($email){
		$this->email=$email;
	}

    public function setIsAdmin($isAdmin){
		$this->isAdmin=$isAdmin;
	}
	
	public function setPassword($password){
		$this->password=$password;
	}
	public function setInitPassowrd($password){
		$this->init_password=$password;
	}

	public function setRef($refId,$redUserId){
		$this->isBusinessReferalExist = 1;
		$this->referralId = $refId;
		$this->memberId = $redUserId;
		$this->user_belong_to_agent = $redUserId;
	}

	public function setAvater($avatar){
		$this->avatar=$avatar;
	}

	public function setBusinessName($businessName){
		$this->displayName=$businessName;
	}
	public function setLegalName($legalName){
		$this->businessName=$legalName;
	}

	public function setABN($abn){
		$this->abn=$abn;
	}

	public function setBusinessPhone($businessPhone){
		$this->tel=$businessPhone;
	}

	public function setBusinessMobile($businessMobile,$verified=false){
		$this->phone=$businessMobile;
		$this->phone_verified=($verified)?'true':'false';
	}

	public function setAddress($business_address){
		$this->googleMap=$business_address;
	}

	public function setBusinessPlan($plan){
		$this->business_plan=$plan;
	}

	public function setRole($role){
		$this->role=$role;
	}

	public function setOpenID($openID){
		$this->wx_openID=$openID;
	}

	public function toDBArray(){
		return get_object_vars($this);
	}


	
}

class mdl_user extends mdl_base
{

	protected $tableName = '#@_user';
	private $roleTName = '#@_role';
	private $listField = 'id, name, displayName,businessName,person_first_name,person_last_name,phone, isAdmin, isApproved, role, roleExtendType, groupid, createdDate, lastLoginDate, cityId';

	public function getUserById ($id)
	{
		return $this->db->selectOne(null, $this->tableName, "id='$id'");
	}

	public function getUserByName ($name)
	{
		return $this->db->selectOne(null, $this->tableName, "name='$name'");
	}

	public function chkUserName ($name)
	{
		return $this->db->getCount($this->tableName, "name='$name'");
	}

	public function getAllUserListSql ($where)
	{
		return $this->db->getSelectMultipleSql(array($this->listField, array('roleName'=>'name')), array($this->tableName, $this->roleTName), '0#role=1#id', $where, 't0.createdDate desc');
		//echo $this->db->getSelectMultipleSql(array($this->listField, array('roleName'=>'name')), array($this->tableName, $this->roleTName), '0#role=1#id', $where);exit;
	}

	public function getAllUserListSqlByRole ($role)
	{
		return $this->db->getSelectMultipleSql(array($this->listField, array('roleName'=>'name')), array($this->tableName, $this->roleTName), '0#role=1#id', array('0#role' => $role));
	}

	public function getAllUserList ()
	{
		return $this->db->toArray($this->db->selectMultiple(array($this->listField, array('roleName'=>'name')), array($this->tableName, $this->roleTName), '0#role=1#id'));
	}

	public function getAllUserListWithoutById ($where)
	{
		return $this->db->toArray($this->db->select($this->listField, $this->tableName, $where));
	}

	public function getAllUserListBySql ($sql)
	{
		return $this->db->toArray($this->db->query($sql));
	}

	public function getUserListByName ($name)
	{
		return $this->db->toArray($this->db->select($this->listField, $this->tableName, "name like '%$name%'"));
	}

	public function addUser ($data)
	{
		//echo $this->db->getInsertSql($data, $this->tableName);exit;
		return $this->db->insert($data, $this->tableName);
	}

	public function updateUserById ($data, $id)
	{
		return $this->db->update($data, $this->tableName, "id='$id'");
	}

	public function deleteUserById ($id)
	{	
		/**
		 * 用户删除时 应该同时删除该用户的所有系统数据
		 */
		
		return $this->db->delete($this->tableName, "id='$id'");
	}

	public function isBusiness($id){
		$user =$this->getUserById($id);
		
		if($user['role']==3) return 1;
		return 0;
	}

	public function getLogo($id){
		$user =$this->getUserById($id);
		$logo = $user['logo'];

		$defaultLogo=null;

		$logo = ($logo)?$logo:$defaultLogo;

		return $logo;
	}

	public function getAvatar($id){
		$user =$this->getUserById($id);
		$avatar = $user['avatar'];

		$defaultAvatar="default/avatar.png";//UPLOAD_PATH

		$avatar = ($avatar)?$avatar:$defaultAvatar;
		return $avatar;
	}

	/**
	 * User staff list 
	 * @param  [type]  $id          [description]
	 * @param  boolean $includeSelf user it self count as a staff
	 * @return [type]               [description]
	 */
	public function getAllStaff($id,$includeSelf=false){
		if($includeSelf){
			$where = " user_belong_to_user= $id or id = $id";
		}else{
			$where = array( 'user_belong_to_user' => $id );
		}
		
		$list = $this->getList( array( 'id', 'email', 'nickname','contactPersonNickName', 'person_first_name', 'person_last_name', 'cityId', 'phone', 'tel','avatar','googleMap'), $where, 'createdDate asc' );

		return $list;
	}
	
	
	public function getAllStaff1($id,$includeSelf=false){
		if($includeSelf){
			$sql ="select a.* from  cc_redeem_staff b left join  cc_user a  on b.user_id = a.id where b.business_id = $id";
		}else{
			$sql ="select  a.*  from  cc_redeem_staff b left join  cc_user a  on b.user_id = a.id where b.business_id = $id";
		}
		//var_dump($sql);exit;
		$list = $this->getListBySql($sql);
		return $list;
	}

	/**
	 * 从coupon 的 sales_user_list 获得 staffList
	 * @param  [type] $str [description]
	 * @return [type]      [description]
	 */
	public function getAllStaffFromString($str){
		
		$where = " id in ($str)";
		
		$list = $this->getList( array( 'id', 'email', 'nickname','contactPersonNickName', 'contactMobile','person_first_name', 'person_last_name', 'cityId', 'phone', 'tel','avatar','googleMap'), $where, 'createdDate asc' );

		return $list;
	}

	/**
	 * 从多个产品的 sales_user_list 获得 并集
	 * @param  [type] $str_array [array of sales_user_list string ]
	 * @return [type]            [description]
	 */
	public function findCommonStaffStr($str_array)
	{
		$full_str = join(',',$str_array);

		$arr = explode(',', $full_str);

		$unique_array= 	array_unique($arr);

		return join(',',$unique_array);
	}

	/**
	 * [用于处理购物车中产品的自取点]
	 * @param  array $array_items [购物车产品的数组]
	 * @return [type]        [员工数组]
	 */
	public function findCommonStaff($array_items)
	{	
		$str_array=[];
		foreach ($array_items as $key => $value) {
			if(!$value['sales_user_list'])continue;

			$str_array[] = $value['sales_user_list'];
		}
		return $this->getAllStaffFromString($this->findCommonStaffStr($str_array));
	}


	public function getMainPics($id){
		$user =$this->getUserById($id);
		$pic = $user['pic'];
		$pics = $user['pics'];


		$picArray = unserialize($pics);

		$mainPics = array();
		array_push($mainPics, ($pic)?UPLOAD_PATH.$pic:null);
		foreach ($picArray as $item) {
			array_push($mainPics, ($item)?UPLOAD_PATH.$item['pic']:null);
		}

		return $mainPics;
	}

	public function getCompanyDescription($id){
		$row = $this->getUserById($id);
		return $row['companyDescription'];
	}

	public function getContactInfo($id){
		$row=$this->getUserById($id);
		
		$num = $row['addrNumber'];
		$street = $row['addrStreet'];
		$addrPost=$row['addrPost'];
		$suburb = $row['addrSuburb'];
		$state = $row['addrState'];

		$addressStr = $num.' '.$street.' '.$suburb.','.$state ;

		$email = $row['email'];

		$phone =$row['tel'];		if(!$phone) {			$phone =$row['phone'];					}

		$backupEmail = $row['backupEmail'];

		$data=array();
        if($addressStr) {			$data['address']=$addressStr;		}
		        if($phone){			$data['phone']=$phone;		}
				if($email){			$data['email']=$email;					}        
				
		//$data['website']=$backupEmail;

		return $data;

	}

	public function isFirstTimeWithdraw($id){
		return ($this->getNumOfWithdrawals($id)==0)?true:false;
	}

	public function getNumOfWithdrawals($id){
		$result=$this->get($id);

		$number_of_withdrawals = null;
		if($result==null){
			$number_of_withdrawals = null;
		}else{
			$number_of_withdrawals = $result['num_of_withdrawals'];
		}

		return $number_of_withdrawals;
	}

	public function updateNumOfWithdrawals($id,$times){
		$num_of_withdrawals=$this->getNumOfWithdrawals($id);

		$new_number  = $num_of_withdrawals +$times;

		return $this->update(array('num_of_withdrawals' => $new_number),$id);
	}

	public function keyMatchBusinessName_SAME($key){
		if($key==null)return null;
		$where['businessName']=$key;
		$where['role'] = 3;

		$result=$this->getByWhere($where);

		$id=null;
		if(sizeof($result)>0){
			$id = $result[0]['id'];
		}
		
		return $id;
	}

	public function keyMatchBusinessName_SIMLAR($key,$similarity){
		//similarity is a number between 0 and 100
		$businessName=$this->getListBySql("select id, businessName from ".$this->tableName." where role = 3");

		$result = array();

		foreach ($businessName as $key => $row) {
			similar_text($row['businessName'] , $key, $percent);

			if($percent>$similarity)array_push($result, $row['id']);
		}
		return $result;
		//return a array of user id;
	}

	public function getBusinessNameById ($id)
	{
		$result= $this->db->selectOne('displayName', $this->tableName, "id='$id'");
		return $result['displayName'];
	}

	public function getBookingFee($id){
		$result= $this->db->selectOne('bookingfee', $this->tableName, "id='$id'");
		return $result['bookingfee'];
	}

	public function updateBookingFee($id,$amount){
		return $this->update(array('bookingfee' => $amount),$id);
	}

	public function getBookingFeeType($id){
		$result= $this->db->selectOne('bookingfeetype', $this->tableName, "id='$id'");
		return $result['bookingfeetype'];
	}

	public function updateBookingFeeType($id,$type){
		$types = ['peritem','flatrate'];
		if(!in_array($type, $types))throw new Exception('invalid booking type.');

		return $this->update(array('bookingfeetype' => $type),$id);
	}

	//=====================================================
	/**
	 * 用户名字段
	 * name
	 * person_first_name
	 * person_last_name
	 * nickname
	 * 
	 * displayName   (businessDisplayName)
	 * businessName  (businessLegalName)
	 * contactPersonFirstname
	 * contactPersonLastname
	 * contactPersonNickName
	 */
	//=====================================================
	public function getUserDisplayName($id){
		$user=$this->getUserById($id);

		if($user['nickname']){
			$displayName = $user['nickname'];

		}else if($user['person_first_name'] || $user['person_last_name']) {
			$displayName = $user['person_first_name'] . ' '. $user['person_last_name'];

		}else{
			$displayName = $user['name'];
		}

		return $displayName;
	}

	public function getBusinessDisplayName($id,$language1){
		$user=$this->getUserById($id);

     if($language1=='en') {
		if($user['displayName_en']) {
			$displayName =$user['displayName_en'];

		}else if($user['businessName']) {
			$displayName =$user['businessName'];

		}else if($user['displayName']){
			$displayName =$user['displayName'];

		}else if($user['contactPersonNickName']){
			$displayName =$user['contactPersonNickName'];

		}else if($user['contactPersonFirstname'] || $user['contactPersonLastname']){
			$displayName = $user['contactPersonFirstname'] . ' '. $user['contactPersonLastname'];
			
		}else{
			$displayName =$user['name'];
		}
		
	 }else{
		 if($user['displayName']) {
			$displayName =$user['displayName'];

		}else if($user['businessName']) {
			$displayName =$user['businessName'];

		}else if($user['contactPersonNickName']){
			$displayName =$user['contactPersonNickName'];

		}else if($user['contactPersonFirstname'] || $user['contactPersonLastname']){
			$displayName = $user['contactPersonFirstname'] . ' '. $user['contactPersonLastname'];
			
		}else{
			$displayName =$user['name'];
		}
		 
	 }

		return $displayName;
	}


	public function getPickupLocationName($id,$language1){
	  	$user=$this->getUserById($id);
		
		
         if($language1=='en') {
			    if($user['displayName_en']){
					
					$displayName =$user['displayName_en'];
				}else if($user['contactPersonNickName']){
					$displayName =$user['contactPersonNickName'];
					
				}else if($user['contactPersonFirstname'] || $user['contactPersonLastname']){
					$displayName = $user['contactPersonFirstname'] . ' '. $user['contactPersonLastname'];

				}else if($user['displayName']) {
					$displayName =$user['displayName'];

				}else if($user['businessName']) {
					$displayName =$user['businessName'];
					
				}else{
					$displayName =$user['name'];
				}
			 
			 
		 }else{
				if($user['contactPersonNickName']){
					$displayName =$user['contactPersonNickName'];
					
				}else if($user['contactPersonFirstname'] || $user['contactPersonLastname']){
					$displayName = $user['contactPersonFirstname'] . ' '. $user['contactPersonLastname'];

				}else if($user['displayName']) {
					$displayName =$user['displayName'];

				}else if($user['businessName']) {
					$displayName =$user['businessName'];
					
				}else{
					$displayName =$user['name'];
				}
			 
			 
		 }
		
		
		

		return $displayName;
	}




	public function getDisplayName($id){
		$user=$this->getUserById($id);

		if($user['displayName']) {
			$displayName =$user['displayName'];

		}else if($user['businessName']) {
			$displayName =$user['businessName'];

		}else if($user['contactPersonNickName']){
			$displayName =$user['contactPersonNickName'];

		}else if($user['contactPersonFirstname'] || $user['contactPersonLastname']){
			$displayName = $user['contactPersonFirstname'] . ' '. $user['contactPersonLastname'];

		}else if($user['nickname']){
			$displayName = $user['nickname'];

		}else if($user['person_first_name'] || $user['person_last_name']) {
			$displayName = $user['person_first_name'] . ' '. $user['person_last_name'];

		}else{
			$displayName =$user['name'];
		}

		return $displayName;
	}



	public function saveStripCode($userid,$stripCode){
		return $this->update(array('stripCode' => $stripCode),$userid);
	}
	public function getStripCode($userid){
		$result= $this->db->selectOne('stripCode', $this->tableName, "id='$userid'");
		return $result['stripCode'];
	}
	public function getInitPasswordById($userid){
		$user = $this->getUserById($userid);
		if($user){
			return $user['init_password'];
		}else{
			return false;
		}
		
	}

	public function hasMutilWxBind($wx_openID){
		$where['wx_openID']=$wx_openID;
		$count=$this->getCount($where);
		return ($count>1);
	}

	public function hasDefaultWxBind($wx_openID){
		$where['wx_openID']=$wx_openID;
		$where['isDefaultWxBind']=true;
		$account=$this->getByWhere($where);
		return $account;
	}

	public function getBroadcastInfo($id)
	{
		 $data =$this->getList(array('isbroadcasting','broadcastType','broadcastTitle'),array('id'=>$id));
		 return $data[0];
	}

	public function getBroadcastingUserList()
	{
		return $this->getList(array('id','logo','avatar'),array('isbroadcasting'=>'on'));
	}

	public function getBusinessAddress($userId)
	{
		$data= $this->get($userId);
		return $data['googleMap'];
	}

	public function getBusinessDeliveryInfo($userId,$lang)
	{
		loadModel('freshfood_disp_suppliers_schedule');
		$dispCenterId = DispCenter::getDispCenterIdOfSupplier($userId);
		if ($dispCenterId) {
			$userId = $dispCenterId;// 统配中心商家统配商家统管用户的设置。
		}
		
		$business_info =$this->get($userId);

		$business_delivery_info =array(
			'base_local_rate'=>$business_info['flat_rates_to_local_city'],
			'base_national_rate'=>$business_info['flat_rates_national'],
			'base_international_rate'=>$business_info['flat_rates_international'],
			'amount_for_free_delivery'=>$business_info['amount_for_free_delivery'],
			'deliver_enable'=>$business_info['deliver_avaliable'],
            'pickup_enable'=>$business_info['pickup_avaliable'],

            'delivery_description'=>$business_info['delivery_description'],
			'delivery_description_en'=>$business_info['delivery_description_en'],
			'pickup_des'=>$business_info['pickup_des'],
			'pickup_des_en'=>$business_info['pickup_des_en'],

			'supportofflinepayment'=>$business_info['supportofflinepayment'],
			'offline_pay_des'=>$business_info['offline_pay_des'],
			'offline_pay_des_en'=>$business_info['offline_pay_des_en'],

			'supportpaypalpayment'=>$business_info['supportpaypalpayment'],
			'supportroyalpaypayment'=>$business_info['supportroyalpaypayment'],
			'supporthcashpayment'=>$business_info['supporthcashpayment'],
			'supportcreditcardpayment'=>$business_info['supportcreditcardpayment'],

			'paypalsurcharge'=>$business_info['paypalsurcharge'],
			'royalpaysurcharge'=>$business_info['royalpaysurcharge'],
			'hcashsurcharge'=>$business_info['hcashsurcharge'],
			'creditcardsurcharge'=>$business_info['creditcardsurcharge'],

			'transactionFeeChargeFrom_paypal'=>$business_info['transactionFeeChargeFrom_paypal'],
			'transactionFeeChargeFrom_royalpay'=>$business_info['transactionFeeChargeFrom_royalpay'],
			'transactionFeeChargeFrom_hcash'=>$business_info['transactionFeeChargeFrom_hcash'],
			'transactionFeeChargeFrom_creditcard'=>$business_info['transactionFeeChargeFrom_creditcard']
            
		);
			if ($lang=='en') {
				  $business_delivery_info['delivery_description']=$business_info['delivery_description_en'];
				  $business_delivery_info['pickup_des']=$business_info['pickup_des_en'];
				  $business_delivery_info['offline_pay_des']=$business_info['offline_pay_des_en'];
				 }
		return $business_delivery_info;
			
	}

	public function getBusienssNotice($id)
	{
		$businessUser = $this->get($id);
		$notice = $businessUser['notice'];

		if($notice){
			$notice = trim($notice);

			$result = array();
			$result = explode(';', $notice);

			foreach ($result as $key => $value) {
				$result[$key]= trim($value);
			}

			return $result;
		}else{

			return false;
		}
	}

	public function getBusinessChatId($id)
	{	
		$businessChatId = 25201;//default

		$user = $this->get($id);
		if($user['IsTransform'])
			$businessChatId=$id;

		return $businessChatId;
	}

	/**
	 * 信用卡加密储存
	 * @param  [type] $cardData [size = 4 的 数组。 依次为卡号 月 年 code]
	 * @param  [type] $userId   [description]
	 * @return [type]           [description]
	 */
	public function save_card($cardData,$userId)
	{	
		if(!$cardData||!$userId)
			return false;

		$cardData = serialize($cardData);

		$data['card']=$this->encrypt_card($cardData);

		$this->update($data,$userId);
	}

	/**
	 * 信用卡解码读取
	 * @param  [type] $userId [description]
	 * @return [cardData]         [size = 4 的 数组。 依次为卡号 月 年 code]
	 */
	public function get_card($userId)
	{
		$data = $this->get($userId);

		$encrypt_card_data = $data['card'];

		$cardData=$this->dencrypt_card($encrypt_card_data);

		$cardData=unserialize($cardData);

		return $cardData;
	}

	public function clear_card($userId)
	{	
		$data['card']=" ";
		$this->update($data,$userId);
	}

	/**
	 * 用于显示的 card number
	 * @param  [type] $userId [description]
	 * @return [type]         [description]
	 */
	public function get_card_on_file($userId)
	{
		$cardData = $this->get_card($userId);

		if($cardData){
			$card_number = $cardData['card_number'];

			$last_four_digit = substr($card_number, -4);

			$card_number = str_repeat("**** ",3).$last_four_digit;

			return $card_number;
		}else{

			return false;
		}
	}

	public function encrypt_card($str)
	{	
		$secret_key = 'my_simple_secret_key';
	    $secret_iv = 'my_simple_secret_iv';
	 
	    $output = false;
	    $encrypt_method = "AES-256-CBC";
	    $key = hash( 'sha256', $secret_key );
	    $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

		$output = base64_encode( openssl_encrypt( $str, $encrypt_method, $key, 0, $iv ) );

		return $output;
	}

	public function dencrypt_card($str)
	{	
		$secret_key = 'my_simple_secret_key';
	    $secret_iv = 'my_simple_secret_iv';
	 
	    $output = false;
	    $encrypt_method = "AES-256-CBC";
	    $key = hash( 'sha256', $secret_key );
	    $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

		$output = openssl_decrypt( base64_decode( $str ), $encrypt_method, $key, 0, $iv );
		
		return $output;
	}

	
	/**
	 * 计算用户的信用级别
	 * @param  [type] $data [用户数据]
	 * @return [type]       [description]
	 */
	public function calculateTrustLevel($data)
	{	
		$mdl_reg = loadModel('reg');

		$name=$data['name'];
		$email=$data['email'];
		$phone=$data['phone'];
		$phone_verified=$data['phone_verified'];


		$trustLevel=0;

		//用户通过手机注册 或者手机通过验证
		if($mdl_reg->checkPhone($name)||$phone_verified=='true'){
			$trustLevel=1;
		}

		return $trustLevel;

	}


	/**
	 * 设置用户的信用级别
	 */
	public function setTrustLevel($level,$userId){
		if($level<0||$level>9)return false;

		$data['trustLevel']=$level;

		return $this->update($data,$userId);
	}
	// 设置某个用户(商家)的指定销售员 
    public function setSalesManager($salesManager,$userId){
		
		$data['belong_to_sales_manager']=$salesManager;

		return $this->update($data,$userId);
	}
	
// 设置某个用户(商家)的指定销售员 
    public function setUser_belong_to_agent($user_belong_to_agent,$userId){
		
		$data['user_belong_to_agent']=$user_belong_to_agent;

		return $this->update($data,$userId);
	}
	
	
	// 更新店铺（生鲜店铺）最近一次被更改的时间，用于判断再更新之后店铺是否被刷新过
    public function updateStoreEditTime($businessId,$time){
		
		$data['store_update_time']=$time;
		$this->update($data,$businessId);

		return 1;
	}
}

?>