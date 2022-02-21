<?php

class ctl_index extends cmsPage
{
	function index_action() {


      $this->setData( 'index', 'mobile_menu' );

     $this->display('home');


	}
	



}