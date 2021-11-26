<?php

class mdl_wj_show_tickets_category_price extends mdl_base
{

	protected $tableName = '#@_wj_show_tickets_category_price';

	public function getTicketCategory($show_id,$stadium_id){
		 $charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

		$col=['id','ticket_category','ticket_category_desc','price'];
		$where['show_id']=$show_id;
		$whrer['stadium_id']=$stadium_id;
		$result = $this->getList($col,$where);

		$category=[];
		$index = 0;
		foreach ($result as $cat) {
			array_push($category, ['id'=>$cat['id'],
									'symbol'=>$charset[$index],
									'price'=>$cat['price'],
									'dec'=>$cat['ticket_category_desc']
									]);
			$index++;
		}
		return $category;
	}
}

?>