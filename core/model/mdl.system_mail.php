<?php

class mdl_system_mail extends mdl_base
{
	public $handler;

	function __construct(){
		require_once CORE_DIR.'PHPMailer/PHPMailerAutoload.php';
		$this->handler =  new PHPMailer;

		$this->handler->isSMTP();                                      // Set mailer to use SMTP
		//$this->handler->SMTPDebug = 2;
		$this->handler->Host = 'smtp.sg.aliyun.com';                        // Specify main and backup SMTP servers
		$this->handler->SMTPAuth = true;                               // Enable SMTP authentication
		$this->handler->Username = 'info@cityb2b.com';                 // SMTP username
		$this->handler->Password = '2012Tang1';                           // SMTP password
		$this->handler->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
		$this->handler->Port = 465;                                    // TCP port to connect to
		$this->handler->isHTML(true);
		$this->handler->CharSet="UTF-8";
		$this->handler->setFrom('info@cityb2b.com', 'Cityb2b');
	}

	function from($address,$name)
	{
		$this->handler->setFrom($address, $name);
		return $this;
	}

	function to($address)
	{
		$address='hhxx_2012@hotmail.com';
        $this->handler->addAddress($address); // Add a recipient  // Name is optional //
        return $this;
	}

	function title($title)
	{
		 $this->handler->Subject=$title;
		 return $this;
	}

	function body($body)
	{
 		$this->handler->Body=$body;
 		return $this;
	}

	function altBody($altBody)
	{
		$this->handler->AltBody = $altBody;
		return $this;
	}

	function attachment($path,$name)
	{
		 $this->handler->addAttachment($path,$name); // Add attachments  // Optional name file name
		//var_dump($path.$name);exit;
		 return $this;
	}

	function send(){
		return $this->handler->send();
	}

	function clearAddresses()
	{
		return $this->handler->clearAddresses();
	}
}

?>
