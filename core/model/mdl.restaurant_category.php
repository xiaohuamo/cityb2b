 <?php
 


class mdl_restaurant_category extends mdl_base
{

	protected $tableName = '#@_restaurant_category';

	
	 public function getCountOfCategory($restaurant_id)
    {
        $sql ="select count(*) as count  from cc_restaurant_category where restaurant_id =$restaurant_id";
		$rec = $this->getListBySql($sql);
		//var_dump($rec);exit;
		return  $rec[0]['count'];
    }

}

?>