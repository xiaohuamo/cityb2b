<?php

class mdl_shop_stock extends mdl_base
{
	//couponId  guige1Id guige2Id qty buy
	protected $tableName = '#@_shop_stock';

	private function insertRow($couponId,$guige1Id,$guige2Id,$qty,$buy){
		$data=[
			'couponId'=>$couponId,
			'guige1Id'=>$guige1Id,
			'guige2Id'=>$guige2Id,
			'qty'=>$qty,
			'buy'=>$buy
		];

		return $this->insert($data);
	}

	private function updateRow($couponId,$guige1Id,$guige2Id,$qty,$buy){
		$data=['qty'=>$qty,
				'buy'=>$buy
				];

		$where=[
			'couponId'=>$couponId,
			'guige1Id'=>$guige1Id,
			'guige2Id'=>$guige2Id
		];

		return $this->updateByWhere( $data, $where );
	}
	public function guigeArrayToIdStr($guigeArray){
		$tempArray=array();
		foreach ($guigeArray as $value) {
			array_push($tempArray, $value['id']);
		}
		$guigeStr="(".join(',',$tempArray).")";
		return $guigeStr;
	}
	public function clearStockTable($id,$guige1Array,$guige2Array){
		//$id is the unique link of one whole set of record entry in this table; which is the coupon_id field, but it also can be u+[number] eg:u312  to represent a table of one user.
		//SELECT * FROM `cc_shop_stock` WHERE (guige1Id not in (88,89) or guige2Id not in (90,91)) and couponId = 1935
		$where['couponId']=$id;
		$guige1Str = $this->guigeArrayToIdStr($guige1Array);
		$guige2Str = $this->guigeArrayToIdStr($guige2Array);
		$where[]=" (guige1Id not in $guige1Str or guige2Id not in $guige2Str) ";
		return $this->deleteByWhere($where);
	}

	public function initStock($couponId,$guige1Array,$guige2Array,$data=null){
		$this->db->begin();
		try {
			foreach ($guige1Array as $guige1 ) {
				foreach ($guige2Array as $guige2 ) {
					if($data==null)$this->insertRow($couponId,$guige1['id'],$guige2['id'],0,0);
				}
			}	

			$this->clearStockTable($couponId,$guige1Array,$guige2Array);
		} catch (Exception $e) {
			$this->db->rollback();
			return false;
		}

		$this->db->commit();
		return true;
	}

	public function getFullStockData($couponId){
		$where=[
			'couponId'=>$couponId
		];
		$result=$this->getList( null,$where,null,null );
		return $result;
	}
	public function getDefaultGuigeSet($couponId){
		$data=$this->getFullStockData($couponId);
		//use the first set as the default;
		if($data){
			$guige1Id=$data[0]['guige1Id'];
			$guige2Id=$data[0]['guige2Id'];
		}else{
			$guige1Id=-1;
			$guige2Id=-1;
		}

		$guigeSet = array();
		array_push($guigeSet, $guige1Id);
		array_push($guigeSet, $guige2Id);
		return $guigeSet;
	}

	public function getStock($couponId,$guige1Id,$guige2Id){
		if(!$guige1Id)$guige1Id=-1;
		if(!$guige2Id)$guige2Id=-1;
		$where=[
			'couponId'=>$couponId,
			'guige1Id'=>$guige1Id,
			'guige2Id'=>$guige2Id
		];
		$result=$this->getByWhere( $where );
		return $result['qty'];
	}
	public function updateStock($couponId,$guige1Id,$guige2Id,$qty){
		$data=['qty'=>$qty];

		$where=[
			'couponId'=>$couponId,
			'guige1Id'=>$guige1Id,
			'guige2Id'=>$guige2Id
		];

		return $this->updateByWhere( $data, $where );

	}
	private function getBuy($couponId,$guige1Id,$guige2Id){
		$where=[
			'couponId'=>$couponId,
			'guige1Id'=>$guige1Id,
			'guige2Id'=>$guige2Id
		];
		$result=$this->getByWhere( $where );
		return $result['buy'];
	}
	private function updateBuy($couponId,$guige1Id,$guige2Id,$buy){
		$data=['buy'=>$buy];

		$where=[
			'couponId'=>$couponId,
			'guige1Id'=>$guige1Id,
			'guige2Id'=>$guige2Id
		];

		$this->updateByWhere( $data, $where );
	}

	public function stockSale($couponId,$guige1Id,$guige2Id,$num){
		$currentBuy = $this->getBuy($couponId,$guige1Id,$guige2Id);
		$currentStock = $this->getStock($couponId,$guige1Id,$guige2Id);
		if($currentStock>0 && $currentStock>=$num){
			return $this->updateRow($couponId,$guige1Id,$guige2Id,$currentStock-$num,$currentBuy+$num);
		}else{
			return false;
		}
		
	}
}

?>