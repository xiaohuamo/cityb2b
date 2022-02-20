<?php

class ctl_index extends cmsPage
{
	function index_action() {


      $this->setData( 'index', 'mobile_menu' );

     $this->display('home');


	}
	
  public function about_us_action(){

      $this->display('index/aboutUs');
  }

   public function set_up_account_action(){

        $this->display('index/KH');
    }

   public function suppliers_set_up_account_action(){

        $this->display('index/suppliers_set_up_account');
    }
}