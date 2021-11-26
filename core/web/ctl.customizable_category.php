<?php 
class ctl_customizable_category extends cmsPage{

	function edit_action(){
		
		//var_dump('ctl_customizable_category');exit;
		$shop=get2('shop');
          $this->setData( $shop, 'shop' );
		
		$type = trim(get2('type'));
		$mdl= $this->loadModel('customizableCategory');
		$mdl->setUserId($this->loginUser['id']);
		$list =$mdl->getTopLevelItemList();

		foreach ($list as $key => $value) {
			$list[$key]['hasChild']=$mdl->hasChild($value['id']);
		}

		$this->setData($list,'list');
		$table_tr=$this->fetch('customizable_category/table_tr');

		$this->setData($table_tr,'table_tr');

		if($type=='mingxingdian'){
			$this->setData( 'my_mingxingshop', 'menu' );
		}else{
			$this->setData( 'index_publish', 'menu' );
		}
		
		
		$this->setData( 'category_edit', 'submenu' );
		$this->setData('自定义分类编辑 - ' . $this->site['pageTitle'], 'pageTitle');
		$this->display_pc_mobile('customizable_category/edit','mobile/customizable_category/edit');
		
	}
	//ajax
	function add_node_action(){
		$mdl= $this->loadModel('customizableCategory');
		$mdl->setUserId($this->loginUser['id']);

		$parentId = get2('pid');

		$nodeorder = get2('nodeorder');

		$nodecname = get2('nodecname');
		$nodecname_en = get2('nodecname_en');
       	$item = new CustomizableCategoryItem();
		$item->setName($nodecname);
		$item->setName_en($nodecname_en);
		$item->setOrder($nodeorder);

		if(!is_numeric($parentId))$parentId=null;
		$mdl->addChild($item,$parentId);
	}

	//ajax
	function load_child_action(){
		$mdl= $this->loadModel('customizableCategory');
		$mdl->setUserId($this->loginUser['id']);

		$parentId = get2('pid');

		$list =$mdl->getChildList($parentId);

		foreach ($list as $key => $value) {
			$list[$key]['hasChild']=$mdl->hasChild($value['id']);
		}

		$this->setData($list,'list');

		echo $this->fetch('customizable_category/table_tr');
	}

	function load_child_show_action(){
		$mdl= $this->loadModel('customizableCategory');
		$mdl->setUserId($this->loginUser['id']);

		$parentId = get2('pid');

		$list =$mdl->getChildList($parentId);

		foreach ($list as $key => $value) {
			$list[$key]['hasChild']=$mdl->hasChild($value['id']);
		}

		$this->setData($list,'tr_list');

		echo $this->fetch('customizable_category/table_tr_show');
	}

	//ajax
	function load_category_data_action(){
		$mdl= $this->loadModel('customizableCategory');
		$userId=$this->loginUser['id'];

		if(get2('businessUserId')){
			$userId=get2('businessUserId');
		}
		$mdl->setUserId($userId);

		$rootId = get2('rootid'); 

		if($rootId>0){
			$data = $mdl->getChildList($rootId);
		}else{
			$data = $mdl->getTopLevelItemList();
		}

		echo json_encode($data);
	}

	function delete_node_action(){
		$id = get2('id');
		$mdl= $this->loadModel('customizableCategory');
		$mdl->setUserId($this->loginUser['id']);
		
		$mdl->deleteNode($id);
		$this->sheader( HTTP_ROOT_WWW.'customizable_category/edit');
	}

	function get_coupon_list_by_custom_category_action(){
		$userId = get2('businessUserId');
		$cid=get2('cid');

		$mdl=$this->loadModel('coupons');
		$data=$mdl->getCouponListByCustomCategory($userId,$cid);

		$mdl_shop_guige = $this->loadModel('shop_guige');
		foreach ($data as $key => $item) {
			$data[$key]['couponHasGuige']=$mdl_shop_guige->couponHasGuige($item['id']);
		}

		$this->setData($data,'list');

		echo $this->fetch('customizable_category/category_coupons');

	}


}

?>