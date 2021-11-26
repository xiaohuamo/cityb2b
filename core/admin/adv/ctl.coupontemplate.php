<?php

/*
 @ctl_name = 积分券模板管理@
*/

class ctl_coupontemplate extends adminPage
{

	public function index_action () #act_name = index#
	{
		$mdl_infoClass = $this->loadModel( 'infoClass' );
		$infoclass = $mdl_infoClass->getChild( '106' );

		$mdl_coupon_type = $this->loadModel( 'coupon_type' );
		$typelist = $mdl_coupon_type->getList(null, null, 'sortnum asc');

		if ( is_post() ) {
			foreach( $infoclass as $cat ) {
				$val = array();
				foreach ( $typelist as $type ) {
					if ( (int)post( 'cat_c'.$cat['id'].'_t'.$type['id'] ) ) $val[] = $type['id'];
				}
				$mdl_infoClass->updateById( array( 'coupontype' => ','.implode(',', $val).',' ), $cat['id'] );
			}
			$this->sheader( $this->parseUrl() );
		}
		else {
			$this->setData($infoclass, 'catlist');
			$this->setData($typelist, 'typelist');
			$this->display();
		}
	}

}

?>