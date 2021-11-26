<?php

/*
 @ctl_name = contact管理@
*/

class ctl_contact extends adminPage
{

	public function index_action () #act_name = contact列表#
	{
		$bllLink	= $this->loadModel('contact');
		$where		= "";
		$order		= "createtime desc";
		$pageSql	= $bllLink->getListSql(null, $where, $order);
		$pageUrl	= '?con=admin&ctl=adv/contact&';
		$pageSize	= 20;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $bllLink->getListBySql($page['outSql']);

		foreach ( $data as $key => $val ) {
			$data[$key]['message'] = utf8substr( $val['message'], 80, 0, '...' );
			$data[$key]['createtime'] = date( 'd/m/Y', $val['createtime'] );
		}

		$this->setData($data, 'data');
		$this->setData($page['pageStr'], 'pager');
		$this->display();
	}

	public function edit_action () #act_name = contact回复#
	{
		$id			= (int)get2('id');
		$bllLink	= $this->loadModel('contact');
		$data		= $bllLink->get($id);
		if (!$data) $this->sheader(null, $this->lang->current_record_not_exists);
		if (is_post())
		{
			$email	= trim( $data['email'] );
			$data	= post('data');
			$data	= trim( $data['reply'] );
			if ( empty( $data ) ) {
				$this->sheader( null, $this->lang->enter_reply_message );
			}
			$data	= array(
				'reply' 	=> $data,
				'replytime' => time(),
				'replyuserid' => $this->user_id
			);
			if ( $bllLink->update( $data, $id ) ) {
				//send mail
				if ( ! empty( $email ) ) {
					$mdl_adminemail = $this->loadModel( 'adminemail' );
					$admin_email = $mdl_adminemail->get();
					$mdl_site = $this->loadModel( 'site' );
					$site = $mdl_site->get();
					send_mail( $email, "New Message From ".$site['name'], nl2br( $data['reply'] ) );
				}
				$this->sheader( '?con=admin&ctl=adv/contact' );
			}
			else {
				$this->sheader( null, $this->lang->reply_failed );
			}
		}
		else
		{
			$data['createtime'] = date('Y-m-d H:i:s', $data['createtime']);
			$data['message'] = str_replace( "\n", '<br />', $data['message'] );
			if ( empty( $data['replytime'] ) ) {
				$data['replytime'] = '';
			}
			else {
				$data['replytime'] = date( 'Y-m-d H:i:s', $data['replytime'] );
			}
			$bllUser = $this->loadModel( 'user' );
			if ( $user = $bllUser->getUserById( $data['replyuserid'] ) ) {
				$data['replyusername'] = $user['displayName'];
			}
			else {
				$data['replyusername'] = 'unknown';
			}
			$this->setData($data, 'data');
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
		$this->sheader('?con=admin&ctl=adv/contact');
	}

	private function _delete ($id)
	{
		$id			= (int)$id;
		$bllLink	= $this->loadModel('contact');
		$link		= $bllLink->get($id);
		if (!$link)
		{
			$this->sheader(null, $this->lang->current_record_not_exists."<br />id:$id");
		}
		if ($bllLink->delete($id))
		{
			
		}
		else
		{
			$this->sheader(null, $this->lang->delete_failed."<br />id:$id");
		}
	}

}

?>