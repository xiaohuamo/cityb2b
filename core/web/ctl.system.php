<?php 
/**
*  系统和用户相关的公开页面
*/
class ctl_system extends cmsPage
{
	/**
	 * [cloudflare_action]
	 */
	public function cloudflare_action()
	{
		$this->display('specialpage/cloudFlare');
	}

	/**
	 * [send_sms_verification_code_action description]
	 */
	function send_sms_verification_code_action()
	{	
		$mobile =trim(get2('mobile'));
		$full_number = trim(get2('full_number'));

		$code = $this->createRnd();

		$this->session('sms_verification_code',$code);
		$this->session('sms_verification_mobile',$mobile);

		$content=$code.' '.(string)$this->lang->mobile_sent_code_desc.$code;

		send_sms($full_number, $content);
		// echo $mobile."#".$code;
	}

	function send_sms_login_verification_code_action()
	{	
		$mobile =trim(get2('mobile'));
		$full_number = trim(get2('full_number'));

		$code = $this->createRnd();

		$this->session('sms_verification_code',$code);
		$this->session('sms_verification_mobile',$mobile);

		$content= (string)$this->lang->your_verification_code.$code;

		send_sms($full_number, $content);
		// echo $mobile."#".$code;
	}
	
	



	/**
	 * [send_email_verification_code_action description]
	 */
	function send_email_verification_code_action()
	{
		$userEmail = trim(get2('email'));
		$code = $this->createRnd();

		$this->session('email_verification_code',$code);
		$this->session('email_verification_email',$userEmail);

		$template = $this->loadModel('system_mail_template');
		$system_mailer = $this->loadModel('system_mail');

		$title = "Email Verification Ubonus";
		$body  = $template->emailVerificationCodeNotification($code);
		$to    = $userEmail;

		$system_mailer->title($title);
		$system_mailer->body($body);
		$system_mailer->to($to);

		$status=$system_mailer->send();

		// echo $userEmail.$code;
	}
	

	/**
	 * [register_namecheck_action description]
	 */
	function register_namecheck_action() {
		$mdl_user = $this->loadModel( 'user' );
		$name = trim( get2( 'name' ) );
		$user = $mdl_user->getByWhere( " name='$name' " );
		if ( $user ) echo (string)$this->lang->exist;
	}

	/**
	 * 找回密码入口
	 * @return [type] [description]
	 */
	function forget_password_action()
	{	
		if(is_post()){
			$mdl_user=$this->loadModel('user');
			$mdl_reg =$this->loadModel('reg');

			$str=trim(post('name'));
			
			if(!$str)$this->form_response_msg((string)$this->lang->input_username_phone_email);
			
			//if(!verify_recaptcha(post('g-recaptcha-response')))$this->form_response_msg('请点击验证窗口');

			$user = $mdl_user->getByWhere(array('name'=>$str));


			if($user){
				//通过用户名直接锁定用户
				if($mdl_reg->chkPhone('0'.substr($user['name'], -9))){
					$code = $this->createRnd();

					$_SESSION['reset_password_userid']=$user['id'];
					$_SESSION['reset_password_method']=$user['name'];
					$_SESSION['reset_password_code']=$code;
					$_SESSION['reset_password_time']=date("Y-m-d h:i:sa");

					$this->send_reset_password_sms($_SESSION['reset_password_method'],$code);

					$this->form_response(200,(string)$this->lang->get_password_from_phone);
				}

				if($mdl_reg->chkMail($user['name'])){
					$code = $this->createRnd();

					$_SESSION['reset_password_userid']=$user['id'];
					$_SESSION['reset_password_method']=$user['name'];
					$_SESSION['reset_password_code']=$code;
					$_SESSION['reset_password_time']=date("Y-m-d h:i:sa");

					$this->send_reset_password_email($_SESSION['reset_password_method'],$code);

					$this->form_response(200,(string)$this->lang->get_password_from_email);
				}

				if($mdl_reg->chkUsername($user['name'])){

					if($user['phone']&&$mdl_reg->chkPhone('0'.substr($user['phone'], -9))){
						$code = $this->createRnd();

						$_SESSION['reset_password_userid']=$user['id'];
						$_SESSION['reset_password_method']=$user['phone'];
						$_SESSION['reset_password_code']=$code;
						$_SESSION['reset_password_time']=date("Y-m-d h:i:sa");

						$this->send_reset_password_sms($_SESSION['reset_password_method'],$code);

						$this->form_response(200,(string)$this->lang->get_password_from_Username.$user['phone']);
					}

					if($user['email']&&$mdl_reg->chkMail($user['email'])){
						$code = $this->createRnd();

						$_SESSION['reset_password_userid']=$user['id'];
						$_SESSION['reset_password_method']=$user['email'];
						$_SESSION['reset_password_code']=$code;
						$_SESSION['reset_password_time']=date("Y-m-d h:i:sa");

						$this->send_reset_password_email($_SESSION['reset_password_method'],$code);

						$this->form_response(200,(string)$this->lang->get_password_from_Username1.$user['email']);
					}
				}

				
			}else{
				if($mdl_reg->chkPhone($str)){
					
					$where=array();
					$where[]=" (tel='$str' or phone ='$str' or contactMobile='$str') ";
					$list = $mdl_user->getList(null,$where);
					if(sizeof($list)>0){
						// 多个结果 需要进一步核实信息验证用户身份
						$this->form_response_msg((string)$this->lang->get_password_from_ubonus_service);
					}else{
						$this->form_response_msg((string)$this->lang->no_data);
					}
				}

				if($mdl_reg->chkMail($str)){
					$where=array();
					$where[]=" (email = '$str' or backupEmail = '$str') ";
					$list = $mdl_user->getList(null,$where);

					if(sizeof($list)>0){
						// 多个结果 需要进一步核实信息验证用户身份
						$this->form_response_msg((string)$this->lang->get_password_from_ubonus_service);
					}else{
						$this->form_response_msg((string)$this->lang->no_data);
					}
				}

				$this->form_response_msg((string)$this->lang->no_data);
			}
		}else{
            $this->setData( $this->lang->find_password.' - '. $this->site['pageTitle'], 'pageTitle' );
			$this->display( 'welcome/forget_password' );
		}
		
	}

	public function reset_password_by_email_or_mobile_action(){
		if(is_post()){
			$mdl_reg =$this->loadModel('reg');
			$mdl_user=$this->loadModel('user');


			$code=trim(post('code'));
			$password = trim(post('password'));
			$password_again = trim(post('password_again'));

			$userid = $_SESSION['reset_password_userid'];
			$check_code=$_SESSION['reset_password_code'];

			if(!$userid)
				$this->form_response_msg((string)$this->lang->verify_hint2);

			if($code!=$check_code)
				$this->form_response_msg((string)$this->lang->remind_user_register_13);

			if(!$mdl_reg->chkPassword($password))
				$this->form_response_msg((string)$this->lang->remind_user_register_8);

			if($password!=$password_again)
				$this->form_response_msg((string)$this->lang->remind_user_register_7);

			/**
			 * Update User reset password
			 */
			$data['password']=$this->md5($password);

			$mdl_user->update($data,$userid);

			/**
			 * Reset Session
			 */
			unset($_SESSION['reset_password_userid']);
			unset($_SESSION['reset_password_method']);
			unset($_SESSION['reset_password_code']);
			unset($_SESSION['reset_password_time']);

			/**
			 * User Login
			 */
			
			$user=$mdl_user->get( $userid);
			$this->session( 'member_user_id', $user['id'] );
			$this->session( 'member_user_shell', $this->md5( $user['id'].$user['name'].$user['password'] ) );

			if($this->returnUrl){
				$this->form_response(200,(string)$this->lang->reset_succ_login,HTTP_ROOT.$this->returnUrl);
			}else{
				$this->form_response(200,(string)$this->lang->reset_succ_login,HTTP_ROOT_WWW."member/profile");
			}

		}
	}

	public function reset_password_by_username_action(){
		if(is_post()){

		}else{
			$this->display( 'system/reset_password_by_username');
		}
	}


	private function send_reset_password_email($to,$code)
	{	
		$system_mailer = $this->loadModel('system_mail');
		$title = (string)$this->lang->find_password;
        $body  = (string)$this->lang->your_verification_code.$code."，".(string)$this->lang->continue_after_input_verification_code;

        $system_mailer->title($title);
        $system_mailer->body($body);
        $system_mailer->to($to);

        $status=$system_mailer->send();
	}

	private function send_reset_password_sms($to,$code)
	{	
		$content = (string)$this->lang->find_password.",".(string)$this->lang->your_verification_code.$code;
		send_sms($to,$content);
	}

	/**
	 * 后台商家中心以商家登录
	 */
	function system_login_action()
	{
		if ( is_post() ) {
			$name = trim(post( 'name' ));
			$pwd = trim(post( 'pwd' ));
			$remember = (int)post( 'remember' );
			
		//	var_dump('input' . $name.' ' .$pwd);exit;
			
			if ( empty($name) )
				$this->form_response_msg((string)$this->lang->please_input_username);

			if ( empty($pwd) ) $this->form_response_msg((string)$this->lang->please_input_password);


			if(strlen($pwd)<=16) {
				$passwordByCustomMd5 = $this->md5( $pwd );
			}else{
				$passwordByCustomMd5 = $pwd;
			}

			$mdl_user = $this->loadModel( 'user' );
			$user = $mdl_user->getUserByName( $name );

			if ( !$user ) $this->form_response_msg((string)$this->lang->username_or_password_error);

			if ( $passwordByCustomMd5 != $user['password'] ) $this->form_response_msg((string)$this->lang->username_or_password_error);


			if ( ! $user['isApproved'] ) $this->form_response_msg('Member was un approved ,please contact Ubonus');
			
			if ( $user['isSuspended'] ) $this->form_response_msg('Member was suspended ,please contact Ubonus');
			
			$data = array(
				'lastLoginIP'	=> ip(),
				'lastLoginDate'	=> time(),
				'loginCount'	=> $user['loginCount'] + 1
			);

			$mdl_user->updateUserById( $data, $user['id'] );

			$this->session( 'member_user_id', $user['id'] );
			$this->session( 'member_user_shell', $this->md5( $user['id'].$user['name'].$user['password'] ) );

			$this->cookie->setCookie( 'remember', $remember, 60 * 60 * 24 * 365 );
			if ( $remember ) {
				$this->cookie->setCookie( 'remember_user_id', $user['id'], 60 * 60 * 24 * 365 );
				$this->cookie->setCookie( 'remember_user_shell', $this->md5( $user['id'].$user['name'].$user['password'] ), 60 * 60 * 24 * 365 );
			}

			$this->form_response(200,(string)$this->lang->log_in_succ,HTTP_ROOT_WWW);
			
			
		}
	}

}

 ?>