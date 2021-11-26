<?php

/*
 @ctl_name = 行业分类管理@
*/

class ctl_category extends adminPage
{

	private $refreshUrl;

	function ctl_category ()
	{
		parent::adminPage();

		$this->refreshUrl = '?con=admin&ctl=adv/category&';
	}

	public function index_action () #act_name = 列表#
	{
		$mdl_infoClass = $this->loadModel( 'infoClass' );
		$infoclass = $mdl_infoClass->getChild( '106' );

		$this->setData( $infoclass );
		$this->display();
	}

	public function add_action () #act_name = 添加#
	{
		$infoclass = $this->loadModel('infoClass');

		$parent_id = (int)get2( 'parentId' );
		if ( $parent_id <= 0 || !preg_match('/^106[0-9]+/', $parent_id) ) {
			$parent_id = 106;
		}
		$parent = $infoclass->get( $parent_id );

		if ( is_post() )
		{
			$this->file->deletefile( 'data/cats.tmp' );

			$data = post( 'data' );
			$data['id'] = $infoclass->getChildClassIdToInsert( $parent_id );
			$data['ordinal'] = $infoclass->getChildClassOrdinal( $parent_id );
			$data['template'] = 'content';
			$data['extend'] = $parent['extend'];
			$data['info'] = $parent['info'];
			$data['other'] = $parent['other'];

			if ( empty( $data['name'] ) || empty( $data['alias'] ) ) {
			//if ( empty( $data['name'] ) ) {
				$this->sheader( null, $this->lang->your_submit_incomplete );
			}

			if ( $infoclass->chkAlias( 0, $data['alias'] ) > 0 ) {
				$this->sheader( null, $this->lang->alias_has_been_occupied );
			}

			//图片
			$image_exts = array( 'jpg', 'jpeg', 'png', 'gif' );
			$filepath = date( 'Y-m' );
			$this->file->createdir( 'data/upload/'.$filepath );
			if ( $_FILES['imageUrl']['size'] > 0 ) {
				$filename = $this->file->upfile( $image_exts, $_FILES['imageUrl'], UPDATE_DIR, $filepath.'/'.date( 'YmdHis' ).$this->createRnd() );
				if ( $filename ) {
					$data['imageUrl'] = $filename;
				}
			}

			if ( $infoclass->create( $data ) ) {
				//自动创建栏目列表
				$column = $infoclass->getColumns( $this->user['id'], $parent_id );
				$infoclass->saveColumns( $column, $this->user['id'], $data['id'] );
				$this->sheader( $this->refreshUrl );
			}
			else {
				$this->file->deletefile( UPDATE_DIR.$data['imageUrl'] );
				$this->sheader( null, $this->lang->add_category_failed );
			}
		}
		else
		{
			$data = array( 'extend' => unserialize( $parent['extend'] ) );
			$this->setData( $data );
			$this->setData( $this->refreshUrl, 'refreshUrl' );
			$this->setData( $parent_id, 'parent_id' );
			$this->setData( $infoclass->getChildClassOrdinal( $parent_id ), 'ordinal' );
			$this->display();
		}
	}

	public function edit_action () #act_name = 编辑#
	{
		$id = get2( 'id' );
		$infoclass = $this->loadModel( 'infoClass' );
		$data = $infoclass->get( $id );

		if ( is_post() )
		{
			$this->file->deletefile( 'data/cats.tmp' );

			$oldData = $data;
			$data = post('data');
			if ( $data['ordinal'] < 0 ) $data['ordinal'] = $infoclass->getChildClassOrdinal( $infoclass->getParentId( $id ) );
			if ( empty( $data['name'] ) || empty( $data['alias'] ) ) {
			//if ( empty( $data['name'] ) ) {
				$this->sheader( null, $this->lang->your_submit_incomplete );
			}
			if ( $infoclass->chkAlias( $id, $data['alias'] ) > 0 ) {
				$this->sheader( null, $this->lang->alias_has_been_occupied );
			}

			//图片
			$image_exts = array( 'jpg', 'jpeg', 'png', 'gif' );
			$filepath = date( 'Y-m' );
			$this->file->createdir( 'data/upload/'.$filepath );
			if ( $_FILES['imageUrl']['size'] > 0 ) {
				$filename = $this->file->upfile( $image_exts, $_FILES['imageUrl'], UPDATE_DIR, $filepath.'/'.date( 'YmdHis' ).$this->createRnd() );
				if ( $filename ) {
					$data['imageUrl'] = $filename;
				}
			}

			if ( $infoclass->updateById( $data, $id ) ) {
				if ( $data['imageUrl'] ) $this->file->deletefile( UPDATE_DIR.$oldData['imageUrl'] );
				$this->sheader( $this->refreshUrl );
			}
			else {
				$this->file->deletefile( UPDATE_DIR.$data['imageUrl'] );
				$this->sheader( null, $this->lang->edit_category_failed );
			}
		}
		else
		{
			$data['extend'] = unserialize( $data['extend'] );
			$this->setData( $data );
			$this->setData( $this->refreshUrl, 'refreshUrl' );
			$this->display();
		}
	}

	public function delete_action () #act_name = 删除#
	{
		$id = get2( 'id' );
		$infoclass = $this->loadModel( 'infoClass' );
		$mdl_products = $this->loadModel('products');
		if ($infoclass->getInfoCount($id) > 0) $this->sheader(null, $this->lang->category_under_info_delete_all_first);
		if ($mdl_products->getCount(array( "categoryId like '".$id."%'" )) > 0) $this->sheader(null, '当前行业分类下存在产品，不可以删除');
		if ( $infoclass->deleteById( $id ) ) {
			$this->file->deletefile( 'data/cats.tmp' );
			$this->sheader( $this->refreshUrl );
		}
		else $this->sheader( null, $this->lang->delete_failed );
	}

}

?>