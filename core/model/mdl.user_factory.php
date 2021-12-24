<?php

class mdl_user_factory extends mdl_base
{
    protected $tableName = '#@_user_factory';
    protected $encryptMethod = 'aes-128-cbc';

    public function isUserApproved($userId, $factoryId) {
        return !!$this->getByWhere([
            'user_id' => $userId,
            'factory_id' => $factoryId,
            'approved' => true
        ]);
    }

   public function isUserAuthorisedToOperate($userId, $factoryId) {
        return !!$this->getByWhere([
            'user_id' => $userId,
            'factory_id' => $factoryId
            
        ]);
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
	
	 public function getBusinessId($salesMan,$role)
    {
       
	   if($role ==20) { //如果用户是销售员
		    $userFactory =loadModel('user')->get($salesMan);
		    if($userFactory) {
           return $userFactory['user_belong_to_user'];
        }
	   }else{
		   return $salesMan;
		   
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

    public function getUserFactoryList($factoryId, $search = null,$salesmanId) {



        $sql = "SELECT f.id as idd,f.nickname as code,f.account_type ,f.user_id,f.factory_id,f.approved,f.show_origin_price,f.factory_sales_id,f.business_discount_rate ,u.id,u.name,u.phone
                FROM cc_user_factory f
                LEFT JOIN cc_user u ON u.id = f.user_id
			    WHERE f.factory_id = $factoryId";
				//var_dump ($sql);exit;
		if($salesmanId ){
			
			$sql .= " and f.factory_sales_id = $salesmanId";
			//var_dump($sql);exit;
		}

        if($search) {
            $sql .= " AND (u.id ='%$search%'
                     OR u.phone like '%$search%'
                     OR u.name like '%$search%'
					  OR f.nickname like '%$search%'
					 )";
        }
//var_dump($sql);exit;
        return $this->getListBySql($sql);
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