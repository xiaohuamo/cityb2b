<?php

class ctl_freshfood extends cmsPage
{
    function ctl_freshfood()
    {

        parent::cmsPage();

       
    }
	
				

    function index_action()
    {

       

        $this->setData('生鲜首页', 'pagename');
        $this->setData('index', 'menu');
        $this->setData('生鲜首页 - '.$this->site['pageTitle'], 'pageTitle');
        $this->display_pc_mobile('freshfood1/index', 'freshfood1/index');
    }

   function search_action()
    {

       

        $this->setData('生鲜首页', 'pagename');
        $this->setData('index', 'menu');
        $this->setData('生鲜首页 - '.$this->site['pageTitle'], 'pageTitle');
        $this->display_pc_mobile('freshfood1/search', 'freshfood1/search');
    }
	
	  function details_action()
    {

       

        $this->setData('生鲜首页', 'pagename');
        $this->setData('index', 'menu');
        $this->setData('生鲜首页 - '.$this->site['pageTitle'], 'pageTitle');
        $this->display_pc_mobile('freshfood1/businessDetails', 'freshfood1/businessDetails');
    }
	

}