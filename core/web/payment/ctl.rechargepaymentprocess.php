<?php

class ctl_rechargepaymentprocess extends cmsPage
{
	public function checkout_action()
	{
		if ( is_post() ) {
			$result = array();

			$payment = trim( post( 'payment' ) );
			$money = (float)post( 'money' );

			if ( $money < 1 ) {
				$this->form_response_msg((string)$this->lang->min_recharge.'$1');
			}

           $orderId = '101'.date( 'YmdHis' ).$this->createRnd(3);
			
			$this->create_balance($orderId,$this->loginUser['id'],$money);

			$this->form_response(200,(string)$this->lang->ready_to_payment_page,HTTP_ROOT_WWW.'payment/rechargepaymentprocess/pay?orderId='.$orderId.'&payment='.$payment);
			
		}

	}


	public function pay_action()	
	{
		$orderId = trim( get2( 'orderId' ) );
		$payment = trim( get2( 'payment' ) );

		$mdl_recharge = $this->loadModel( 'recharge' );
		$order = $mdl_recharge->getByWhere( array( 'orderId' => $orderId ) );

		if(!$payment)
			$this->sheader( null, (string)$this->lang->unknow_payment_method );

		if ( ! $order || $order['userId'] != $this->loginUser['id'] ) 
			$this->sheader( null, (string)$this->lang->no_data );

		if ( $order['status']!=BalanceProcess::INIT ) 
			$this->sheader( null, (string)$this->lang->order_had_been_processed );


		switch ( $payment ) {
			case 'paypal':
				$payto=$this->payments['paypal']['config']['business'];//ubonus

				$order_name = $order['coupon_name'].'-'.$order['orderId'];

				$this->paypal_form($payto,$order['orderId'],$order_name,$order['money']);
				break;
			case 'eway':
				
				//none
					
				break;

			case 'royalpay':

				$order_name = $order['coupon_name'];

				$this->royalpay_form_action($order['orderId'],$order_name,$order['money']);
					
				break;
			default:
				echo (string)$this->lang->unknow_payment_method ; exit;
				break;
		}
	}

	public function paypal_form($payto,$orderId,$order_name,$amount)
	{
		$action = $this->payments['paypal']['config']['sandmode'] ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
		$payto =$this->payments['paypal']['config']['sandmode'] ? 'ubonus-paypal-facilitator@ubonus.com.au' : $payto;

		$form = array();
		$form['cmd'] = '_xclick';
		$form['notify_url'] = HTTP_ROOT.'payment/rechargepaymentprocess/paypal_notify?orderId='.$orderId;
		$form['return'] = HTTP_ROOT.'payment/rechargepaymentprocess/paypal_success?orderId='.$orderId;
		
		$form['business'] =$payto;
		
		$form['item_name'] =$order_name;
		$form['currency_code'] = 'AUD';
		$form['amount'] = $amount;
		$form['orderId'] = $orderId;
		$form['charset'] = 'utf-8';
		
		
		$this->setData( $action, 'action' );
		$this->setData( $form, 'form' );
		$this->display( 'payment/paypal/form' );
	}

	public function paypal_varify(){
			require_once('core/paypal/PaypalIPN.php');

			$ipn = new PaypalIPN();

			if ($this->payments['paypal']['config']['sandmode']) $ipn->useSandbox();

			return $ipn->verifyIPN();
	}

	public function paypal_notify_action()
	{	

		if ( $this->paypal_varify()==true) {
				if ( ( $_POST['payment_status'] == 'Completed' )  ) {

					$orderId = $_GET['orderId'];

					$this->update_order_paid($orderId);

				}elseif($_POST['payment_status'] == 'Pending'){

					$orderId = $_GET['orderId'];

					$this->update_order_pending($orderId);

					//possible email notification

				}elseif($_POST['payment_status'] == 'Refunded'){

					$orderId = $_GET['orderId'];

					//possible system action to cancel order
					
				}else{
					//Denied
				}
			filelog('SUCCESS#'.$_POST['payment_status'].'#'.$_GET['orderId'].'#'.date("F j, Y, g:i a"),'paypal.txt');

		}else{
			filelog('FAIL#'.serialize($_POST),'paypal.txt');
		}

	}

	public function paypal_success_action($id=null)
	{	
		$orderId = $_GET['orderId'];

		if($id)$orderId=$id;

		if($orderId){
			$order = $this->loadModel('recharge')->getByWhere(array('orderId'=>$orderId));
		}else{

		}

		$this->setData( 'Paypal Recharge Successful － '.$this->site['pageTitle'], 'pageTitle' );

		$this->setData('Recharge Successful $'.$order['money'],'heading');
		$this->setData((string)$this->lang->paypal_success8,'sys_message');

		if($order['status']==BalanceProcess::INIT){
			$sys_message_detail="
			<br>
			<p>".(string)$this->lang->paypal_success2."</p>
			<p><small>". (string)$this->lang->paypal_success3."</small> </p>
			<i class='fa fa-check fa-5x' style ='color:#767676'></i>";

		}elseif($order['status']==BalanceProcess::SETTLE){
			$sys_message_detail="
			<br>
			<p></p>
			<i class='fa fa-check fa-5x' style ='color:#3aff33'></i>";

		}elseif($order['status']==BalanceProcess::PENDING){
			$sys_message_detail="
			<br>
			<p>". (string)$this->lang->paypal_success5 ."</p>
			<p><small>". (string)$this->lang->paypal_success6 ."</small> </p>
			<i class='fa fa-check fa-5x' style ='color:#ff8e33'></i>";
		}
		
		$this->setData($sys_message_detail,'sys_message_detail');
		
		$further_action="<a href='".HTTP_ROOT_WWW."member/recharge'>".(string)$this->lang->footer_my.(string)$this->lang->wallet."</a>";
		$this->setData($further_action,'further_action');


		if($this->getUserDevice()=='desktop'){
			$this->display( 'payment/paypal/return' );
		}else{
			$this->display( 'payment/paypal/return_mobile' );
		}
		
	}


	public function royalpay_form_action($orderId,$order_name,$money)		
	{
		require_once('core/royalpay/lib/RoyalPay.Api.php');

		if($this->getUserDevice()=='wechat'){

			$input = new RoyalPayUnifiedOrder();
			$input->setOrderId($orderId);
			$input->setDescription($order_name);
			$input->setPrice($money * 100);
			$input->setCurrency("AUD");
			$input->setNotifyUrl(HTTP_ROOT.'payment/rechargepaymentprocess/royalpay_notify');
			$input->setOperator("Cityb2b-System");

			//支付下单
			$result = RoyalPayApi::jsApiOrder($input);

			//跳转
			$inputObj = new RoyalPayJsApiRedirect();
			$inputObj->setDirectPay('true');
			$inputObj->setRedirect(urlencode(HTTP_ROOT.'payment/rechargepaymentprocess/royalpay_success?orderId=' . strval($input->getOrderId())));

			$action = RoyalPayApi::getJsApiRedirectUrl($result['pay_url'], $inputObj);

			$this->sheader($action);
		}else{
			$input = new RoyalPayUnifiedOrder();
			$input->setOrderId($orderId);
			$input->setDescription($order_name);
			$input->setPrice($money * 100);
			$input->setCurrency("AUD");
			$input->setNotifyUrl(HTTP_ROOT.'payment/rechargepaymentprocess/royalpay_notify');
			$input->setOperator("Cityb2b-System");

			$result = RoyalPayApi::qrOrder($input);

			//跳转
			$inputObj = new RoyalPayRedirect();
			$inputObj->setRedirect(urlencode(HTTP_ROOT.'payment/rechargepaymentprocess/royalpay_success?orderId=' . strval($input->getOrderId())));

			$action= RoyalPayApi::getQRRedirectUrl($result['pay_url'], $inputObj);

			$this->setData( $action, 'action' );
			$this->display( 'payment/royalpay/form' );
		}
	}

	public function royalpay_notify_action()
	{
		require_once('core/royalpay/lib/RoyalPay.Api.php');
		$response = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
		
		if ($this->royalpay_varify($response)) {//验证成功
		    
		    //商户订单号
		    $order_id = $response['partner_order_id'];
		    //RoyalPay订单号
		    $royal_order_id = $response['order_id'];
		    //订单金额，单位是最小货币单位
		    $order_amt = $response['total_fee'];
		    //支付金额，单位是最小货币单位
		    $pay_amt = $response['real_fee'];
		    //币种
		    $currency = $response['currency'];
		    //订单创建时间，格式为'yyyy-MM-dd HH:mm:ss'，澳洲东部时间
		    $create_time = $response['create_time'];
		    //订单支付时间，格式为'yyyy-MM-dd HH:mm:ss'，澳洲东部时间
		    $pay_time = $response['pay_time'];

		    filelog('SUCCESS#'.serialize($response),'royalpay.txt');

		    //update order paid
		    $this->update_order_paid($order_id);

		} else {//验证失败
			filelog('FAIL#'.serialize($response),'royalpay.txt');
		}
	}

	public function royalpay_varify($response)
	{	
		require_once('core/royalpay/lib/RoyalPay.Api.php');
		$input = new RoyalPayDataBase();
		$input->setNonceStr($response['nonce_str']);
		$input->setTime($response['time']);
		$input->setSign();
		if ($input->getSign() == $response['sign']) {//验证成功
		   return true;
		} else {//验证失败
		   return false;
		}
	}

	public function royalpay_success_action($id=null)
	{
		$orderId = $_GET['orderId'];

		if($id)$orderId=$id;

		require_once('core/royalpay/lib/RoyalPay.Api.php');
	    $input = new RoyalPayOrderQuery();
	    $input->setOrderId($orderId);
	    $result=RoyalPayApi::orderQuery($input);

	    if($result['result_code']=='PAY_SUCCESS'){

	    	$sys_message_detail="
	    	<p>".$result['result_code']."</p>
			<i class='fa fa-check fa-5x' style ='color:#fc3'></i>";

	    	$this->setData( 'RoyalPay 充值 － '.$this->site['pageTitle'], 'pageTitle' );

	    	$this->setData('充值成功 $'.$order['money'],'heading');
			$this->setData('恭喜您成功完成了Royal支付！','sys_message');
			$this->setData($sys_message_detail,'sys_message_detail');
			
			$further_action="<a href='".HTTP_ROOT_WWW."member/recharge'>我的钱包</a>";

			$this->setData($further_action,'further_action');
	    }else{

	    	$sys_message_detail="
	    	<p>".$result['result_code']."</p>
			<i class='fa fa-close fa-5x' style ='color:#f23030'></i>";

	    	$this->setData( 'RoyalPay 充值 － '.$this->site['pageTitle'], 'pageTitle' );
	    	
	    	$this->setData('充值失败','heading');
			$this->setData('Opps! RoyalPay支付没有成功!','sys_message');
			$this->setData($sys_message_detail,'sys_message_detail');

			$this->setData($further_action,'further_action');
	    }


		if($this->getUserDevice()=='desktop'){
			$this->display( 'payment/royalpay/return' );
		}else{
			$this->display( 'payment/royalpay/return_mobile' );
		}
		
	}



	private function create_balance($orderId,$userId,$amount){
		$mdl_recharge=$this->loadModel('recharge');

		$order = $mdl_recharge->getByWhere(array('orderId'=>$orderId));

		if(!$order){
			$data['userId']=$userId;
			$data['money']=$amount;
			$data['orderId']=$orderId;
			$data['coupon_name']='客户手动充值 $'.$amount;

			$data['payment']=BalanceProcess::TYPE_RECHARGE;

			$data['createTime']=time();
			$data['createIp']=ip();

			$data['status']=BalanceProcess::INIT;

			return $mdl_recharge->insert($data);
		}else{
			//already exist
		}
		
		
	}

	private function update_order_paid($orderId){
		$mdl_order = $this->loadModel( 'recharge' );

		return $mdl_order->updataTransactionStatus($orderId,BalanceProcess::SETTLE);
		
	}

	private function update_order_pending($orderId){
		$mdl_order = $this->loadModel( 'recharge' );

		return $mdl_order->updataTransactionStatus($orderId,BalanceProcess::PENDING);
		
	}

}