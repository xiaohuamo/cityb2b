<?php
// parent_coupon_id
// title
// product_description
// picture
// create_user_id
// create_time
// customer_amount 
// original_amount
// quantity
// stage_qty
// buy

class mdl_coupons_sub extends mdl_base
{

	protected $tableName = '#@_coupons_sub';
	function updateBuy( $parent_coupon_id,$id,$qty ) {
		$id = (int)$id;
		return $this->db->query("update {$this->tableName} set buy=buy+{$qty}, quantity=quantity-{$qty} where parent_coupon_id='{$parent_coupon_id}' and id='{$id}'");
	}

	public function getChildList($parent_coupon_id)
	{
		$where['parent_coupon_id']=$parent_coupon_id;
		return $this->getList(null,$where);
	}

	public function copy($id,$parent_coupon_id,$create_user_id)
	{
		$c = $this->get($id);

		$data=array();

		foreach ( $c as $key => $rec ) {
			if ( !is_numeric( $key ) && strtolower($key) != 'id' ) {
				if($key =='create_time'){
					$data[$key] =time();
					
				}else if($key =='create_user_id'){
					$data[$key] =$create_user_id;
					
				}else if($key =='parent_coupon_id'){
					$data[$key] =$parent_coupon_id;

				}else if($key =='picture'){
					
				}else {
					$data[$key] = $c[$key];
				}
			}
		}

		return $this->insert($data);

	}
}

?>