<?php

class mdl_fav extends mdl_base
{	
	//userId
	//productId
	//createTime
	//type

	//two types
	const COUPON ='coupon';
	const STORE = 'store';

	protected $tableName = '#@_fav';

	public function add($userId,$itemId,$type)
	{	
		if (!$userId||!$itemId) {
			return false;
		}

		$data['userId']=$userId;
		$data['productId']=$itemId;
		$data['type']=$type;
		$data['createTime']=time();

		return $this->insert($data);
	}

	public function remove($userId,$itemId,$type=NULL)
	{	
		if (!$userId||!$itemId) {
			return false;
		}

		$where['userId']=$userId;
		$where['productId']=$itemId;
		if($type)$where['type']=$type;

		$this->deleteByWhere($where);
	}

	public function exist($userId,$itemId,$type=NULL)
	{	
		if (!$userId||!$itemId) {
			return false;
		}

		$where['userId']=$userId;
		$where['productId']=$itemId;
		if($type)$where['type']=$type;

		$count = $this->getCount($where);
		$count=intval($count);

		if($count==1){
			return true;
		}elseif($count==0){
			return false;
		}else{
			//throw new Exception("impossible result".$count, 1);
			return true;
		}
	}

	public function getWatchList($userId,$type,$lang)
	{
			
		
		if($type ==self::COUPON){
			//left join cc coupons
			
			if($lang =='en') {
				//var_dump($lang);exit;
				$sql="Select f.id, c.id as couponId, c.title_en,c.title, c.pic, f.createTime from cc_fav as f left join cc_coupons as c on f.productId=c.id where f.userId=$userId and f.type='$type' order by createTime desc";
			}else{
				$sql="Select f.id, c.id as couponId, c.title_en, c.title, c.pic, f.createTime from cc_fav as f left join cc_coupons as c on f.productId=c.id where f.userId=$userId and f.type='$type' order by createTime desc";
		}
			$data = $this->getListBySql($sql);
			return $data;

		}elseif($type==self::STORE){
			//left join cc user
			$sql="Select f.id,u.id as userId, u.logo as pic, u.businessName as title, f.createTime from cc_fav as f left join cc_user as u on f.productId=u.id where f.userId=$userId and f.type='$type'";
			$data = $this->getListBySql($sql);
			return $data;

		}else{
			throw new Exception("unknow fav type", 1);
		}
	}




}

?>