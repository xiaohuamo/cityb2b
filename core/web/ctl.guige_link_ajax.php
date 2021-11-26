<?php

class ctl_guige_link_ajax extends cmsPage
{
	function index_action(){
		echo 'hhe';
	}

	function init_stock_table_action(){
		$trArray=$_REQUEST['trArray'];
		$tdArray=$_REQUEST['tdArray'];

		echo ($this->loadModel('shop_stock')->initStock($couponId,$trArray,$tdArray))?'success':'error';

	}

	function update_stock_action(){
		$guige1Id=$_REQUEST['guige1Id'];
		$guige2Id=$_REQUEST['guige2Id'];
		$qty=$_REQUEST['qty'];
		
		echo ($this->loadModel('shop_stock')->updateStock($couponId,$guige1Id,$guige2Id,$qty))?'success':'error';
	}

	function get_stock_action(){
		$guige1Id=$_REQUEST['guige1Id'];
		$guige2Id=$_REQUEST['guige2Id'];
		
		echo $this->loadModel('shop_stock')->getStock($couponId,$guige1Id,$guige2Id);
	}

	function retrieve_stock_data_action(){
		$trArray=$_REQUEST['trArray'];
		$tdArray=$_REQUEST['tdArray'];

		$result=$this->loadModel('shop_stock')->getFullStockData($couponId);
		echo json_encode($result);
	}
	

}