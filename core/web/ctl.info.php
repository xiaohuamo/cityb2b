<?php

class ctl_info extends cmsPage
{

	function index_action() {
		$alias = get2( 'alias' );
		if ( right( $alias, 1 ) == '/' ) {
			$alias = right( $alias, 1, true );
		}

		$menu = '';
		$pos = strpos( $alias, '/' );
		if ( false === $pos ) {
			$menu = $alias;
		}
		else {
			$menu = substr( $alias, 0, $pos );
		}
		$menu = strtolower( $menu );
		$mdl_info = $this->loadModel( 'info' );
		$mdl_infoclass = $this->loadModel( 'infoClass' );
		$infoclass = $mdl_infoclass->getByAlias( $alias );
		if ( ! $infoclass || ! file_exists( $this->tpl->template_dir.'/'.$infoclass['template'].'.htm' ) ) {
			header( 'HTTP/1.1 404 Not Found' );
			header( 'Status: 404 Not Found' );
			exit;
		}
		if ( $mdl_infoclass->getChildCount( $infoclass['id'] ) > 0 ) {
			$infoclass = $mdl_infoclass->getFirstChild( $infoclass['id'] );
		}
		
		$column = array( 'title', 'publishedDate' );
		$where = array();
		$where[] = "classId like '".$infoclass['id']."%'";
		$where[] = 'isApproved=1';
		$order = 'ordinal desc';
		$pageSql = $mdl_info->getListSqlForWeb( $column, $where, $order );
		$pageUrl = $this->parseUrl()->set( 'page' );
		$pageSize = (int)$infoclass['perPageCount'];
		$page = $this->page( $pageSql, $pageUrl, $pageSize );
		$list = $mdl_info->getListBySql( $page['outSql'] );
		$this->setData( $list, 'list' );
		$this->setData( $page, 'page' );
		
		$this->setData( $infoclass, 'infoclass' );

		$parents = $mdl_infoclass->getParentListArray( $infoclass['id'] );
		$this->setData( $parents, 'parents' );

		$base_class = $parents[0];
		switch($base_class['id']) {
			case '101': $base_class['nameEn'] = 'ABOUT CITIC HIC'; break;
			case '102': $base_class['nameEn'] = 'NEWS & EVENTS'; break;
			case '103': $base_class['nameEn'] = 'MANUFACTURING FACILITIES'; break;
			case '104': $base_class['nameEn'] = 'TECHNOLOGY CENTER'; break;
			case '105': $base_class['nameEn'] = 'PRODUCTS'; break;
			case '106': $base_class['nameEn'] = 'EMPLOYMENT'; break;
			case '107': $base_class['nameEn'] = 'CONTACT US'; break;
		}
		$this->setData( $base_class, 'baseCategory' );
		$second_class = $mdl_infoclass->getChild4( $base_class['id'] );
		if ( ! $second_class ) $second_class = array( $base_class );
		$this->setData( $second_class, 'second_class' );
		$this->setData( $base_class['id'], 'base_id' );
		if ( $parents[1]['id'] ) {
			$this->setData( $parents[1]['id'], 'second_id' );
		}
		else {
			$this->setData( $base_class['id'], 'second_id' );
		}
		$this->setData( $parents[2]['id'], 'third_id' );
		$this->setData( $parents[3]['id'], 'four_id' );

		$this->setData( $alias, 'alias' );
		$this->setData( $menu, 'menu' );

		//seo
		$this->setData( $infoclass['name'].' - '.$this->site['pageTitle'], 'pageTitle' );

		$this->display( $infoclass['template'] );
	}

}