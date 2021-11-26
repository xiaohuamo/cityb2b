<?php

class ctl_menu extends adminPage
{

	public function index_action ()
	{
		$class_id	= '';
		$mm			= $this->loadModel('relation');
		$menuData	= $mm->getChild2($class_id);

		if ($this->user_id == '-1')
		{
			$menuData[] = array(
				'name'			=> $this->lang->super_management,
				'childCount'	=> 1,
				'child'			=> array(
					array(
						'name'	=> $this->lang->update_cache,
						'url'	=> '?con=admin&ctl=hidden/cls'
					),
					array(
						'name'	=> $this->lang->global_info_list_column,
						'url'	=> '?con=admin&ctl=hidden/column'
					),
					array(
						'name'	=> $this->lang->menu_manager,
						'url'	=> '?con=admin&ctl=hidden/relation'
					),
					array(
						'name'	=> $this->lang->generate_multiple_lang,
						'url'	=> '?con=admin&ctl=hidden/gml'
					)
				)
			);
		}
		else
		{
			$showMenus = array();
			foreach ($menuData as $key=>$value)
			{
				$i = 0;
				if (in_array($value['id'], $this->user['relation'])) {
					$showMenus[$key] = true;
					$i++;
				}
				foreach ($value['child'] as $sk=>$sv)
				{
					if (in_array($sv['id'], $this->user['relation'])) {
						$showMenus[$key] = true;
					}
					else
					{
						$menuData[$key]['child'][$sk]['hide'] = true;
						$i++;
					}
				}
				if ($value['childCount'] == $i) $menuData[$key]['hide'] = true;
			}
		}
		if ( count( $showMenus ) == 1 ) {
			foreach ( $showMenus as $key => $show ) {
				if ( $menuData[$key]['child'] ) $menuData = $menuData[$key]['child'];
			}
		}

		$this->setData($menuData);
		$this->setData(strlen($class_id), 'currentLevel');
		$this->display('common/menu');
	}

}

?>