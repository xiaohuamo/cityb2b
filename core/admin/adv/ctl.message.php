<?php

/*
 @ctl_name = message管理@
*/

class ctl_message extends adminPage
{

	public function index_action () #act_name = contact列表#
	{
		$from = trim( get2( 'from' ) );
		$to = trim( get2( 'to' ) );
		$justAdmin = (int)get2( 'ja' );  //只显示管理员收到信息
		$bllUser = $this->loadModel( 'user' );

		if ( $justAdmin ) {
			$fromId = 0;
			$toId = 2;
		}
		else {
			$fromId = 0;
			$toId = 0;
			if ( ! empty( $from ) ) {
				$fromUser = $bllUser->getByWhere( array( 'name' => $from ) );
				if ( $fromUser ) $fromId = $fromUser['id'];
			}
			if ( ! empty( $to ) ) {
				$toUser = $bllUser->getByWhere( array( 'name' => $to ) );
				if ( $toUser ) $toId = $toUser['id'];
			}
			$search = array( 'from' => $from, 'to' => $to );
			$this->setData($search, 'search');
		}

		$bllLink	= $this->loadModel('messages');
		$where		= array();
		if ( $fromId > 0 && $toId > 0 ) $where = '(`from` in('.$fromId.','.$toId.') and `to` in('.$fromId.','.$toId.'))';
		else if ( $fromId > 0 ) $where['from'] = $fromId;
		else if ( $toId > 0 ) $where['to'] = $toId;
		
		
		if($this->user['role']==6){
			//	$where[] ="user_belong_to_agent=". $_SESSION['admin_user_id'];
			
			$where ="`from` in (select id from #@_user where user_belong_to_agent =" . $_SESSION['admin_user_id'].")";
		}
		
		$order		= "sendTime desc";
		$pageSql	= $bllLink->getListSql(null, $where, $order);
		//echo $pageSql;exit;
		$pageUrl	= $this->parseUrl()->set( 'page' );
		$pageSize	= 20;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $bllLink->getListBySql($page['outSql']);

		foreach ( $data as $key => $val ) {
			$data[$key]['fromUser'] = $bllUser->get( $val['from'] );
			$data[$key]['toUser'] = $bllUser->get( $val['to'] );
		}

		$this->setData($data, 'data');
		$this->setData($page['pageStr'], 'pager');
		$this->setData($this->parseUrl(), 'pageUrl');
		$this->display();
	}

	

	public function write_action () #act_name = 发送#
	{
		if ( is_post() ) {
			$to = trim( post( 'to' ) );
			$subject = trim( post( 'subject' ) );
			$content = trim( post( 'content' ) );

			$mdl_user = $this->loadModel( 'user' );
			$mdl_messages = $this->loadModel( 'messages' );
			if ( empty($to) ) $to = array();
			else {
				$toids = explode( ';', $to );
				$to = array();
				foreach ( $toids as $k => $ti ) {
					$tmp = $mdl_user->getUserByName( $ti );
					$tmp = (int)$tmp['id'];
					if ( $tmp > 0 ) $to[] = $tmp;
				}
			}
			if ( empty( $to ) || empty( $subject ) || empty( $content ) ) {
				echo '请填写正确的接收人，并填写信息标题和内容';exit;
			}

			foreach ( $to as $k => $t ) {
				$data = array(
					'from' => 2,
					'to' => $t,
					'subject' => $subject,
					'content' => $content,
					'sendTime' => time()
				);
				$mdl_messages->insert( $data );
			}

			$this->sheader( $this->parseUrl()->set('id')->set('act') );
		}
		else {
			$this->setData( $this->parseUrl()->set('id')->set('act'), 'returnUrl' );
			$this->display();
		}
	}

	public function delete_action () #act_name = 删除Contact#
	{
		if (is_post())
		{
			$ids = post('ids');
			if (is_array($ids))
			{
				foreach ($ids as $k=>$v)
				{
					self::_delete((int)$v);
				}
			}
		}
		else
		{
			self::_delete((int)get2('id'));
		}
		$this->sheader($this->parseUrl()->set( 'act' )->set( 'id' ));
	}

	private function _delete ($id)
	{
		$id			= (int)$id;
		$bllLink	= $this->loadModel('messages');
		$link		= $bllLink->get($id);
		if (!$link)
		{
			$this->sheader(null, $this->lang->current_record_not_exists."<br />id:$id");
		}
		if ($bllLink->delete($id))
		{
			$files = unserialize( $link['files'] );
			foreach ( $files as $f ) {
				$this->file->deletefile( UPDATE_DIR.$f['file'] );
			}
		}
		else
		{
			$this->sheader(null, $this->lang->delete_failed."<br />id:$id");
		}
	}

}

?>