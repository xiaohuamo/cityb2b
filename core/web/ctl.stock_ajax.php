<?php

class ctl_stock_ajax extends cmsPage
{
	function index_action(){
		echo 'hhe';
	}

	function init_stock_table_action(){
		$trArray=$_REQUEST['trArray'];
		$tdArray=$_REQUEST['tdArray'];
		$couponId= $_REQUEST['couponId'];

		echo ($this->loadModel('shop_stock')->initStock($couponId,$trArray,$tdArray))?'success':'error';

	}

	function update_stock_action(){
		$guige1Id=$_REQUEST['guige1Id'];
		$guige2Id=$_REQUEST['guige2Id'];
		$couponId= $_REQUEST['couponId'];
		$qty=$_REQUEST['qty'];
		
		echo ($this->loadModel('shop_stock')->updateStock($couponId,$guige1Id,$guige2Id,$qty))?'success':'error';
	}

	function get_stock_action(){
		$guige1Id=$_REQUEST['guige1Id'];
		$guige2Id=$_REQUEST['guige2Id'];
		$couponId= $_REQUEST['couponId'];

		if(!$this->loadModel('shop_guige')->couponHasGuige($couponId)){

			echo $this->loadModel('coupons')->getQty($couponId);
		}else{
			echo $this->loadModel('shop_guige')->getSingleGuigeData($couponId,$guige1Id,$guige2Id);
		}
		
	}

	function retrieve_stock_data_action(){
		$trArray=$_REQUEST['trArray'];
		$tdArray=$_REQUEST['tdArray'];
		$couponId= $_REQUEST['couponId'];

		$result=$this->loadModel('shop_stock')->getFullStockData($couponId);
		echo json_encode($result);
	}
	

}