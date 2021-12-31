<?php

class ctl_index extends cmsPage
{
	function index_action() {
		


       if($this->getLangStr()=='en')
       {            $lang=" and lang=1";       }
       else      {            $lang=" and lang=0";      }

   
	

      if($this->cookie->getCookie('bannerOpened')){
            $this->setData( $this->cookie->getCookie('bannerOpened'),'bannerOpened');
      }else{
             $this->cookie->setCookie('bannerOpened','true',3600*24);
      }

	      

            //wx share
            require_once "wx/wxjssdk.php";
            $jssdk = new WXjsSDK();
            $signPackage = $jssdk->GetSignPackage();
            $this->setData($signPackage,'signPackage');

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