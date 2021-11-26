<?php

/*
 @ctl_name = subscribe class@
*/

class ctl_subscribeclass extends adminPage
{

	public function index_action () #act_name = index#
	{
		$bllLink	= $this->loadModel('subscribeclass');
		$where		= "";
		$order		= "id desc";
		$pageSql	= $bllLink->getListSql(null, $where, $order);
		$pageUrl	= '?con=admin&ctl=adv/subscribeclass&';
		$pageSize	= 20;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $bllLink->getListBySql($page['outSql']);

		$this->setData($data, 'data');
		$this->setData($page['pageStr'], 'pager');
		$this->display();
	}

	public function add_action () #act_name = create#
	{
		$bllLink	= $this->loadModel('subscribeclass');
		if (is_post())
		{
			$data	= post('data');
			if ($data = self::_filter($data))
			{
				if ($bllLink->add($data))
				{
					$this->sheader('?con=admin&ctl=adv/subscribeclass');
				}
				else
				{
					$this->sheader(null, $this->lang->add_category_failed);
				}
			}
			else
			{
				$this->sheader(null, $this->lang->your_submit_incomplete);
			}
		}
		else
		{
			$this->display();
		}
	}

	public function edit_action () #act_name = edit#
	{
		$id			= (int)get2('id');
		$bllLink	= $this->loadModel('subscribeclass');
		$data		= $bllLink->get($id);
		if (!$data) $this->sheader(null, $this->lang->current_record_not_exists);
		if (is_post())
		{
			$data	= post('data');
			if ($data = self::_filter($data))
			{
				if ($bllLink->update($data, $id))
				{
					$this->sheader('?con=admin&ctl=adv/subscribeclass');
				}
				else
				{
					$this->sheader(null, $this->lang->edit_category_failed);
				}
			}
			else
			{
				$this->sheader(null, $this->lang->your_submit_incomplete);
			}
		}
		else
		{
			$this->setData($data, 'data');
			$this->display();
		}
	}

	public function delete_action () #act_name = delete#
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
		$this->sheader('?con=admin&ctl=adv/subscribeclass');
	}

	private function _delete ($id)
	{
		$id			= (int)$id;
		$bllLink	= $this->loadModel('subscribeclass');
		$link		= $bllLink->get($id);
		if (!$link)
		{
			$this->sheader(null, $this->lang->current_record_not_exists."<br />id:$id");
		}
		if ($bllLink->delete($id))
		{
			$this->file->deletefile(UPDATE_DIR.$link['imageUrl']);
		}
		else
		{
			$this->sheader(null, $this->lang->delete_failed."<br />id:$id");
		}
	}

	private function _filter ($data)
	{
		foreach ($data as $k=>$v)
		{
			$data[$k] = trim($v);
		}
		if ( empty( $data['name'] ) ) {
			return false;
		}
		return $data;
	}

}

?>