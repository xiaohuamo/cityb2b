<?php
/**
 * 商家相关的公开页面
 */
class ctl_business extends cmsPage
{
	 function index_action(){
	 	$this->setData( '招商首页'.' - '.$this->site['pageTitle'], 'pageTitle' );
		$this->display('businesshomepage/index');
	}

	function enquiry_form_submit_action(){
		$name = trim(get2('name'));
		$email = trim(get2('email'));
		$phone = trim(get2('phone'));
		$company = trim(get2('company'));
		$country = trim(get2('country'));
		$content = trim(get2('content'));
		$yzm = trim(get2('yzm'));

		$to='sales@cityb2b.com.au';
		$subject='新客户咨询：$name ';

		if($phone)$subject .='--Phone：'.$phone;
		if($company)$subject .='--Company：'.$company;

		$email_content=$content;
		$from=$email;

		echo 'mail has been sent ,page will return in few secconds...';
		echo "<script>setTimeout(function(){window.location.href='".HTTP_ROOT_WWW."business/index'},5000);</script>";
	}


	function intro_action()
    {	
       if($this->getUserDevice()!='desktop')
        {
            //wx share
            require_once "wx/wxjssdk.php";
            $jssdk = new WXjsSDK();
            $signPackage = $jssdk->GetSignPackage();
            $this->setData($signPackage,'signPackage');

            $this->setData( 'Ubonus美食生活商家入驻', 'pageTitle' );
			 $this->setData( 'Ubonus美食生活目前有近200家餐厅加盟,我们提供入驻一条龙服务方案包括-拍摄,上产品,推广和客户服务', 'pageDescription' );
            $this->setData(HTTP_ROOT_WWW.'themes/'.STYLE."mobile/canvassbusiness/".$this->getLangStr().'/','baseResourcePath');

            $this->display("mobile/canvassbusiness/".$this->getLangStr()."/index");
           
        }
        else
        {	

        	$this->setData(HTTP_ROOT_WWW.'themes/'.STYLE."canvassbusiness/".$this->getLangStr().'/','baseResourcePath');

            $this->display("canvassbusiness/".$this->getLangStr()."/index");

        }
       
    }

    function welcome_action()
    {
    	 if($this->getUserDevice()!='desktop')
        {
            //wx share
            require_once "wx/wxjssdk.php";
            $jssdk = new WXjsSDK();
            $signPackage = $jssdk->GetSignPackage();
            $this->setData($signPackage,'signPackage');

            $this->setData( 'Ubonus美食生活商家入驻', 'pageTitle' );
			$this->setData( 'Ubonus美食生活目前有近200家餐厅加盟,我们提供入驻一条龙服务方案包括-拍摄,上产品,推广和客户服务', 'pageDescription' );
            $this->setData(HTTP_ROOT_WWW.'themes/'.STYLE."mobile/canvassbusiness/".$this->getLangStr().'/','baseResourcePath');

            $this->display("mobile/canvassbusiness/".$this->getLangStr()."/index");
           
        }
        else
        {	

        	$this->setData(HTTP_ROOT_WWW.'themes/'.STYLE."canvassbusiness/".$this->getLangStr().'/','baseResourcePath');
			$this->setData( 'Ubonus美食生活商家入驻', 'pageTitle' );
			$this->setData( 'Ubonus美食生活目前有近200家餐厅加盟,我们提供入驻一条龙服务方案包括-拍摄,上产品,推广和客户服务', 'pageDescription' );
            $this->display("canvassbusiness/".$this->getLangStr()."/index");

        }
    }

	/**
	 * 新秀丽商家页面
	 */
	public function samsonite_action()
	{	
		if($this->getUserDevice()!='desktop'){
			$this->display('specialpage/samsonite/mobile/index');
		}else{
			$this->display('specialpage/samsonite/index');
		}
		
	}

	/**
	 *  新首页
	 */
	public function newindex_action()
	{
		$this->display('specialpage/newhomepage/index');
	}

	/**
	 * 系统通知
	 */

	public function notice_action(){
		$this->display('specialpage/notice/notice');
	}

	/**
	 * 合作商家
	 */
	public function logowall_action()
	{	
		$result = scandir(DATA_DIR."/upload/logowall");
		$result = array_slice($result,2);
		$this->setData($result,'list');
		$this->setData( 'Ubonus美食生活 合作商家'.' - '.$this->site['pageTitle'], 'pageTitle' );
		$this->display('specialpage/logowall');
	}

	public function wx_official_account_action(){
		$this->setData( '微信公众号'.' - '.$this->site['pageTitle'], 'pageTitle' );
		$this->display('specialpage/wx_official_account');
	}


}

?>