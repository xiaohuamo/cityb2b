<?php

//会员

class ctl_message extends cmsPage
{

	function ctl_message() {
		parent::cmsPage();
		
		if ( !$this->loginUser ) {
			$this->sheader( HTTP_ROOT_WWW.'member/login?returnUrl='.urlencode( $_SERVER['REQUEST_URI'] ) );
		}
	}

	function index_action() {
		$mdl_messages = $this->loadModel( 'messages' );

		$where = array();
		
		$where['to'] = $this->loginUser['id'];
		
		$pageSql	= $mdl_messages->getListSql( null, $where, 'sendTime desc' );
		$pageUrl	= $this->parseUrl()->set('page');
		$pageSize	= 40;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $mdl_messages->getListBySql($page['outSql']);
		
		$this->setData( $data, 'data' );
		$this->setData( $page['pageStr'], 'pager' );
		$this->setData( $this->parseUrl()->set('deleteId')->set('id'), 'delUrl' );

		$this->setData( '我的信息', 'pagename' );
		$this->setData( 'messages', 'menu' );
		$this->setData( '我的信息 - 个人中心 - '.$this->site['pageTitle'], 'pageTitle' );
		$this->display_pc_mobile('message/messages','mobile/message/messages');
	}
	

}