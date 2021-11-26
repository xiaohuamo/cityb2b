<?php 
class ctl_category_new extends cmsPage
{
	function ctl_category_new()
	{
		parent::cmsPage();
		
	}	
	
	public function show_action(){ 
		
		
		   $this->display_pc_mobile('category_new/index','category_new/index');
	}
	
	public function list02_action(){ 
		
		
		   $this->display_pc_mobile('category_new/list02','category_new/list02');
	}
	
}
?>
