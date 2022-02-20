<?php

class ctl_index extends cmsPage
{
	function index_action() {

       $type= get2('type');


       if($this->getLangStr()=='en')
       {            $lang=" and lang=1";       }
       else      {            $lang=" and lang=0";      }

   
	

      if($this->cookie->getCookie('bannerOpened')){
            $this->setData( $this->cookie->getCookie('bannerOpened'),'bannerOpened');
      }else{
             $this->cookie->setCookie('bannerOpened','true',3600*24);
      }


        $signature = $_GET["signature"];

        $timestamp = $_GET["timestamp"];

        $nonce = $_GET["nonce"];

        $token = 'weixin';

        $tmpArr = array($token, $timestamp, $nonce);

        sort($tmpArr, SORT_STRING);

        $tmpStr = implode( $tmpArr );

        $tmpStr = sha1( $tmpStr );

        $echostr = $_GET['echostr'];



        if( $tmpStr == $signature ){

            return $echostr;

        }else{

            return false;

        }



            //wx share
            require_once "wx/wxjssdk.php";
            $jssdk = new WXjsSDK();
            $signPackage = $jssdk->GetSignPackage();
            $this->setData($signPackage,'signPackage');

            $this->setData( 'index', 'mobile_menu' );

            if($type==old){
                $this->display_pc_mobile('index-pc','mobile/index');
            }else{
                $this->display('home');
            }

		

			  

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