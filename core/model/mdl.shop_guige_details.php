<?php

class mdl_shop_guige_details extends mdl_base
{

	protected $tableName = '#@_shop_guige_details';


	public function getFullGuigeList($userId,$guigeId){
		$where['userId']=$userId;
		$where['guige_id']=$guigeId;
		$data=$this->getList(null,$where);
		return $data;
	}

	public function getFullGuigeListIdArray($userId,$guigeId){
		
		$idList = array();

		$data=$this->getFullGuigeList($userId,$guigeId);
		
		foreach ($data as $key => $value) {
			array_push($idList, $value['id']);
		}

		return $idList;
	}

	public function getGuigeName($guigeId,$lang)
	{	
		$sql = "SELECT a.name as item_name,a.name_en as item_name_en,b.name as guige_name,b.name_en as guige_name_en FROM cc_shop_guige_details as a left join cc_shop_guige as b on a.guige_id = b.id where a.id = $guigeId";

		$result=$this->getListBySql($sql);

		if(sizeof($result)==0){
			$fullName = '';
		}else{
			if ($lang=='en'){
				$fullName = $result[0]['guige_name_en'].':'.$result[0]['item_name_en'];
			}else{
				
				$fullName = $result[0]['guige_name'].':'.$result[0]['item_name'];
			}
		}
		
		return $fullName;
	}


}

?>