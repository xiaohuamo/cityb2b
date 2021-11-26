 <?php
class mdl_standard_product_info extends mdl_base
{
	protected $tableName = '#@_standard_product_info';
	
	
public function check_if_barcode_in_standard_info($barcode) {
	$where =array (
	   'barcode_number'=>$barcode
	
	);
	$rec = $this->getByWhere($where);
	if ($rec) {
		return 1;
	}else{
		return 0;
	}
	
}
	

public function ifFindBarcode($barcode) {
	
	
	$barcode_number =$barcode;
	$firstletter =substr($barcode_number,0,1);
	
	if (!$firstletter) {
	//	var_dump($barcode);exit;
     $barcode_number =str_replace(' ','0',ltrim(str_replace('0',' ',$barcode_number)));
	
	}else{
		
		
	}
	//var_dump($barcode_number );exit;
	
	$sql ="SELECT * FROM `cc_standard_product_info` a  WHERE Replace(ltrim(REPLACE(barcode_number,'0',' ' )),' ',0)=$barcode_number  and length(images1)>0 ";
	
	$rec =$this->getListBySql($sql);
	if($rec) {
		return $rec[0];
		
	}
	return 0;
	
}
	
	
}

 