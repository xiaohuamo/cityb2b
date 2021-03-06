<?php

class mdl_user_factory extends mdl_base
{
    protected $tableName = '#@_user_factory';
    protected $encryptMethod = 'aes-128-cbc';

    public function isUserApproved($userId, $factoryId) {
		
		$sql ="select count(*) as count from cc_user_factory where user_id =$userId and (factory_id =$factoryId or factory_sales_id =$factoryId) and approved =1 ";
         $rec = $this->getListBySql($sql);
		 if($rec[0]['count']>0) return 1;
		 return 0; 
    }

    /* 再该表中，如果一个user_id 在多个供应商订货，该user_id 会有多个记录， 获取最近的一条user_id记录 */

    public function getLastUserInfo($user_id){
        $sql =" select * from cc_user_factory where user_id = $user_id order by id desc limit 1 ";
        $rec = $this->getListBySql($sql);
        return $rec[0];
    }


    public function getUserCodeandName($userId, $factoryId) {

        $where =array(
            'user_id' => $userId,
            'factory_id' => $factoryId
        );

        $rec = $this->getByWhere($where);
        return $rec;

    }

    public function getUserGradeId($userId, $factoryId) {

       $sql = "select g.grade_id  from cc_factory_customer_grade g 
             left join cc_user_factory f  on f.grade =g.id 
            where f.factory_id  =$factoryId and f.user_id =$userId 

            ";

        $rec = $this->getListBySql($sql);
        if (!$rec)  return 0;
        return (string)$rec[0]['grade_id'];


    }



   public function addCustomerInfo($userId, $factoryId,$data_order) {
     $data=array(
          'user_id'=>$data_order['userId'],
          'factory_id'=>$data_order['business_userId'],
          'nickname'=>'',
          'approved'=>0,
          'show_origin_price'=>0,
          'factory_sales_id'=>0,
          'account_type'=>'COD',
          'business_discount_rate'=>0.0,
          'isHide'=>0,
          'delivery_mon'=>1,
          'delivery_tue'=>1,
         'delivery_wed'=>1,
         'delivery_thur'=>1,
         'delivery_fri'=>1,
         'delivery_sat'=>1,
         'delivery_sun'=>1
    );
    $this->insert($data);

   }

    public function getCurrentUserDiscountRate($userid,$factory_id){

        $discount_rates =0.00;

        if($userid) {
            $where =array(
                'user_id'=>$userid,
                'factory_id'=>$factory_id
            );
            $user_info =$this->getByWhere($where);
            $discount =$user_info['business_discount_rate'];

            if($discount !=0 ){
                $discount_rates=$discount;

            }else{

                // 如果为0 ，看看它是否定义了客户级别，并查看级别里是否有discount ,有则返回
                if($user_info['grade']) {

                    $grade_info = loadModel('factory_customer_grade')->get($user_info['grade']);

                    if($grade_info && $grade_info['grade_discount_rate']){
                        $discount_rates=$grade_info['grade_discount_rate'];
                    }
                }


            }
        }
      // var_dump($discount_rates);exit;
        return $discount_rates;
    }

   public function isUserAuthorisedToOperate($userId, $factoryId) {

       $where =array(
           'user_id' => $userId,
           'factory_id' => $factoryId
       );

       $rec = $this->getByWhere($where);
       if($rec) return 1;

        return 0;
    }

    public function getFactoryId($login_user)
    {
       
		
        $user =loadModel('user')->get($login_user);
        if($user && $user['user_belong_to_user']) {
           return $user['user_belong_to_user'];
        } else {
           return $login_user;
        }
        return $login_user;
    }

  public function checkIfExistMember($factoryId,$userId,$code){

        $where  =array(
            'user_id'=>$userId,
            'factory_id'=>$factoryId,
            'nickname'=>$code

        );
        return $this->getCount($where);
  }

	 public function getBusinessId($salesMan,$role)
    {

	   if(trim($role) ==20) { //如果用户是销售员
		    $userFactory =loadModel('user')->get($salesMan);

		    if($userFactory) {
             //   var_dump('i'.$userFactory['user_belong_to_user']);exit;
           return $userFactory['user_belong_to_user'];
        }
	   }else{
         //  var_dump('p'.$salesMan);exit;
		   return $salesMan;
		   
	   }
		
        
    }

    public function updateApprove1($userId, $factoryId, $approved,$salesManId)
    {

        $where = [
            'user_id' => $userId,
            'factory_id' => $factoryId

        ];
        $userFactory = $this->getByWhere($where);
        if($userFactory) {
            return $this->updateByWhere([
                'approved' => $approved,
            ],[
                'user_id' => $userId,
                'factory_id' => $factoryId
            ]);
        }
    }

    public function updateApprove($userId, $factoryId, $approved,$salesManId)
    {
		if (!$salesManId) {
			$salesManId = $factoryId;
		}
        $where = [
            'user_id' => $userId,
            'factory_id' => $factoryId,
			'factory_sales_id' => $salesManId,
        ];
        $userFactory = $this->getByWhere($where);
        if($userFactory) {
            return $this->updateByWhere([
                'approved' => $approved,
            ],[
                'user_id' => $userId,
                'factory_id' => $factoryId
            ]);
        } else {
            $data = $where;
            $data['approved'] = $approved;
            return $this->insert($data);
        }
    }

    /* 获得当前供应商 当前客户的账期到期日*/
    public function getUserOverDueDate($factory_id,$cutomer_id) {
        $where =array(
            'factory_id'=>$factory_id,
            'user_id'=>$cutomer_id

        );

        $current_rec = $this->getByWhere($where);
        if(!$current_rec || $current_rec['COD']) {
            $overduedays =2;
        }else{
            $overduedays =$current_rec['account_type']*7;
        }
        $overduetime =$overduedays * 24 *60*60 +time();
        return $overduetime;


    }



    public function getUserFactoryList($factoryId, $search = null,$salesmanId,$isHide=0,$customer_type,$returnSql,$gradeId=0) {



        $sql = "SELECT f.id as idd,f.nickname as code,f.discountOfInvoice,f.to_xero,f.xero_contact_id,f.grade,f.customer_type, c.customer_type as type_name,g.grade_name,g.id as gradeId,g.grade_discount_rate,f.account_type ,u.googleMap as address,u.addrPost,u.addrSuburb,f.isHide,
		f.user_id,f.factory_id,f.approved,f.show_origin_price,f.factory_sales_id,f.business_discount_rate ,u.id,u.name,u.phone
		,u.displayName,f.delivery_mon,f.delivery_tue,f.delivery_wed,f.delivery_thur,f.delivery_fri,f.delivery_sat,f.delivery_sun
                FROM cc_user_factory f
                LEFT JOIN cc_user u ON u.id = f.user_id 
                left join cc_factory_customer_grade g on f.grade =g.id 
                left join cc_customer_type c on f.customer_type =c.id 
			    WHERE f.factory_id = $factoryId and f.isHide=$isHide";
				//var_dump ($sql);exit;
		if($salesmanId ){

            $rec =loadModel('staff_roles')->getByWhere(array('staff_id'=>$salesmanId));
            //  var_dump($this->loginUser['id']); var_dump($rec);exit;
            if(substr_count($rec[roles],',5,')>0 || substr_count($rec[roles],',6,')>0 ) {
                $sql .= " and f.factory_sales_id = $salesmanId";
            }



			//var_dump($sql);exit;
		}
      //  var_dump($sql);exit;
        if($customer_type) {
            $sql .= " and f.customer_type = $customer_type ";
        }
        if($gradeId) {
            $sql .= " and f.grade = $gradeId ";
        }



        if($search) {
            $sql .= " AND (u.id like '%$search%'  
                     OR u.phone like '%$search%'
                     OR u.name like '%$search%'
					  OR f.nickname like '%$search%'
					 )";
        }

//var_dump($sql);exit;
        if($returnSql){
            return $sql;
        }else{
            return $this->getListBySql($sql);
        }

    }

    public function getUserFactoryList_xero($factoryId, $search = null,$to_xero,$returnSql) {



        $sql = "SELECT f.id as idd,f.nickname as code,f.to_xero,f.xero_name,f.xero_account_number,f.xero_contact_id,f.grade,f.customer_type, c.customer_type as type_name,g.grade_name,g.id as gradeId,g.grade_discount_rate,f.account_type ,u.googleMap as address,u.addrPost,u.addrSuburb,f.isHide,
		f.user_id,f.factory_id,f.approved,f.show_origin_price,f.factory_sales_id,f.business_discount_rate ,u.id,u.name,u.phone
		,f.delivery_mon,f.delivery_tue,f.delivery_wed,f.delivery_thur,f.delivery_fri,f.delivery_sat,f.delivery_sun
                FROM cc_user_factory f
                LEFT JOIN cc_user u ON u.id = f.user_id 
                left join cc_factory_customer_grade g on f.grade =g.id 
                left join cc_customer_type c on f.customer_type =c.id 
			    WHERE f.factory_id = $factoryId and f.isHide=0";






        if($search) {
            $sql .= " AND (u.id like '%$search%'  
                     OR u.phone like '%$search%'
                     OR u.name like '%$search%'
					  OR f.nickname like '%$search%'
					 )";
        }

        if($to_xero) {
           if($to_xero==1){
               $sql .= " AND f.to_xero =1 ";
           }
            if($to_xero==2){
                $sql .= " AND f.to_xero =0 ";
            }
        }



        if($returnSql){
            return $sql;
        }else{
            return $this->getListBySql($sql);
        }


    }


    public  function getAccountType($userid,$business_id){
        $where11= array(
          'user_id'=>$userid,
            'factory_id'=>$business_id
          );
        $rec =$this->getByWhere($where11);
      //  var_dump('userid'.$userid.' and '.$business_id);exit;
        if(!$rec){
            //did not find the record mean it is not a customer of business
            $accountType ='payWhenPurchase';
        }else{
            if($rec['approved']){
                if($rec['account_type']=='COD'){
                    $accountType ='COD';
                }else{
                    $accountType =$rec['account_type'];
                }
            }else{
                $accountType ='payWhenPurchase';
            }

        }
        return $accountType;

    }

    public function  filiterUserAvaliableDeliveryDate($datelist,$userId,$factoryId){

    //   var_dump(json_encode($datelist));

        $where =array(
            'user_id'=>$userId,
            'factory_id'=>$factoryId
       );

        $user_rec = $this->getByWhere($where);
//  data('w',timestamp )函数  返回值为0 代表sunday ,然后顺次 周一-周六
        if($user_rec){
            for ($i=0;$i<8;$i++){
                if($datelist[$i]->isAvaliable) {
                    $days = date('w',$datelist[$i]->orderDeliveryTimestamp);
                    $isStillAvaliable = $this->filterAvaliableDate($user_rec,$days);
                    if(!$isStillAvaliable) {
                        $datelist[$i]->isAvaliable=false;
                    }
                }

            }
                //逐个日期过滤


     }


     //  var_dump(json_encode($datelist));exit;
        return $datelist;


    }


    function filterAvaliableDate($user_rec,$days){

        if($days=='0') {
            if($user_rec['delivery_sun'] ==0) return false;
            return true;
        }

        if($days=='1') {
            if($user_rec['delivery_mon'] ==0) return false;
            return true;
        }

        if($days=='2') {
            if($user_rec['delivery_tue'] ==0) return false;
            return true;
        }

        if($days=='3') {
            if($user_rec['delivery_wed'] ==0) return false;
            return true;
        }

        if($days=='4') {
            if($user_rec['delivery_thur'] ==0) return false;
            return true;
        }

        if($days=='5') {
            if($user_rec['delivery_fri'] ==0) return false;
            return true;
        }

        if($days=='6') {
            if($user_rec['delivery_sat'] ==0) return false;
            return true;
        }



    }

    public function   getCustomerDiscountRates($userId,$factoryId){

        $where =array(
            'user_id'=>$userId,
            'factory_id'=>$factoryId

        );
        $rec = $this->getByWhere($where);
        if($rec) return round($rec['business_discount_rate'],2);
        return 0.00;

}

    public function generateUserLoginToken($userId, $factoryId, $expiredAt) {
        $data = [
            'user_id' => $userId,
            'factory_id' => $factoryId,
            'expired_at' => $expiredAt
        ];

       return openssl_encrypt(json_encode($data), $this->encryptMethod, $userId, 0, $factoryId);
    }

    public function decryptUserLoginToken($userId, $factoryId, $token) {
        return json_decode(openssl_decrypt(str_replace(' ', '+', $token), $this->encryptMethod, $userId, 0, $factoryId));
    }
}

?>