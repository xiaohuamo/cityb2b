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

    public function getFactoryId($salesMan)
    {
       
		
        $userFactory =loadModel('user')->get($salesMan);
        if($userFactory) {
           return $userFactory['user_belong_to_user'];
        } else {
           
        }
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

    public function getUserFactoryList($factoryId, $search = null,$salesmanId) {
        $sql = "SELECT cc_user_factory.id as idd,cc_user_factory.nickname as code,cc_user_factory.* ,cc_user.*
                FROM cc_user_factory 
                LEFT JOIN cc_user ON cc_user.id = cc_user_factory.user_id
			    WHERE cc_user_factory.factory_id = $factoryId";
			//	var_dump ($salesmanId);exit;
		if($salesmanId ){
			
			$sql .= " and cc_user_factory.factory_sales_id = $salesmanId";
			//var_dump($sql);exit;
		}

        if($search) {
            $sql .= " AND (cc_user.id ='%$search%'
                     OR cc_user.phone like '%$search%'
                     OR cc_user.name like '%$search%'
					  OR cc_user_factory.nickname like '%$search%'
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