<?php

/*
 @ctl_name = 城市管理@
*/

class ctl_zhaopin_category extends adminPage
{

	public function index_action () #act_name = index#
	{
		$mdl_location	= $this->loadModel('zp_category');
		$data			= $mdl_location->getChild();
		$this->setData($data, 'data');
		$this->display();
	}

	public function add_action () #act_name = create#
	{
		$parentId = (int)get2('parentId');
		$mdl_location	= $this->loadModel('zp_category');
		if (is_post())
		{
			$data	= post('data');
			if ($data = self::_filter($data))
			{
				$parent = $mdl_location->get( $parentId );
				$data['parentId'] = $parentId;
				$data['map'] = $parent['map'];
				if ( $data['sortnum'] < 0 ) $data['sortnum'] = $mdl_location->getMax('sortnum', array('parentId' => $parentId)) + 10;

				if ( $id = $mdl_location->insert($data))
				{
					$mdl_location->update( array( 'map' => $data['map'] ? $data['map'].'-'.$id : $id ), $id );
					$this->sheader('?con=admin&ctl=adv/zhaopin_category');
				}
				else
				{
					$this->sheader(null, '创建行业失败');
				}
			}
			else
			{
				$this->sheader(null, '表单未填写完整');
			}
		}
		else
		{
			$this->setData($mdl_location->getMax('sortnum', array('parentId' => $parentId)) + 10, 'sortnum');
			$this->setData($parentId, 'parentId');
			$this->display();
		}
	}

	public function edit_action () #act_name = edit#
	{
		$id				= (int)get2('id');
		$mdl_location	= $this->loadModel('zp_category');
		$data			= $mdl_location->get($id);
		if (!$data) $this->sheader(null, '记录不存在');
		if (is_post())
		{
			$oldData = $data;
			$data	= post('data');
			if ($data = self::_filter($data))
			{
				if ( $data['sortnum'] < 0 ) $data['sortnum'] = $mdl_location->getMax('sortnum', array('parentId' => $oldData['parentId'])) + 10;

				if ($mdl_location->update($data, $id))
				{
					$this->sheader('?con=admin&ctl=adv/zhaopin_category');
				}
				else
				{
					$this->sheader(null, '修改城市失败');
				}
			}
			else
			{
				$this->sheader(null, '表单未填写完整');
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
		$this->sheader('?con=admin&ctl=adv/zhaopin_category');
	}

	private function _delete ($id)
	{
		$id				= (int)$id;
		$mdl_location	= $this->loadModel('zp_category');
		$link			= $mdl_location->get($id);
		if (!$link)
		{
			$this->sheader(null, $this->lang->current_record_not_exists."<br />id:$id");
		}
		if ($mdl_location->delete($id))
		{
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