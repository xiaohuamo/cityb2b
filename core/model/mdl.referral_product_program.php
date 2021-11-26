<?php

class mdl_referral_product_program extends mdl_base
{

	protected $tableName = '#@_referral_product_program';

	public function getProductList($userId)
	{
		$sql = "select c.*,p.promote from cc_referral_product_program  as p left join cc_coupons as c on p.productId = c.id where p.userId =".$userId ." order by p.id desc";
        $list = $this->getListBySql($sql);
        return $list;
	}

	public function getAvailableProducts($userId)
	{	
		$currentTime= time();

		$sql = "select c.* from cc_referral_product_program  as p left join cc_coupons as c on p.productId = c.id where p.userId =".$userId ." and c.isApproved=1 and c.status=4 AND !(c.autoOffline=1 AND ('$currentTime'<c.startTime or '$currentTime'>c.endTime)) order by p.promote desc";

        $list = $this->getListBySql($sql);

        return $list;
		
	}

	public function getAvailableProductsByCustomCategory($userId,$cid,$includeChild=true,$limit=false)
	{	
		$currentTime= time();

		if($cid){
			$s=array();
			array_push($s, "p.cCategoryId like '%".$cid."%'");

			if($includeChild){
				$mdl= loadModel('customizableCategory');
				$mdl->setUserId($userId);
				$ids =$mdl->getChildIdList($cid);

				foreach ($ids as $id) {
					array_push($s, "p.cCategoryId like '%".$id."%'");
				}
			}
			
			$c_where=" And (".join(' or ',$s).")";

		}

		$sql = "select c.* from cc_referral_product_program  as p left join cc_coupons as c on p.productId = c.id where p.userId =".$userId ." and c.isApproved=1 and c.status=4 AND !(c.autoOffline=1 AND ('$currentTime'<c.startTime or '$currentTime'>c.endTime)) $c_where order by p.promote desc";

		if($limit)$sql.=" Limit $limit";
        $list = $this->getListBySql($sql);

        return $list;
		
	}

}

?>