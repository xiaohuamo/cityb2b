<?php 
/**
* Author Chris wang 
* 24/01/2018
*
* build on top of CommonWeb Rest API Version 45
* Doc: https://paymentgateway.commbank.com.au/api/documentation/apiDocumentation/rest-json/version/45/api.html?locale=en_US
* 
*/
include_once CORE_DIR."cbaAPI/configuration.php";
include_once CORE_DIR."cbaAPI/connection.php";

class CommonWebAPI
{
	
	public function check_gateway()
	{
		
	}

	public function authorize()
	{
		# code...
	}

	public function capture()
	{
		# code...
	}

	public function pay($orderId,$source,$amount,$transactionId=null)
	{	
		//validation
		if(!$orderId)
			throw new Exception("order id can not be null", 1);
			
		if(!is_a($source,"CreditCard"))
			throw new Exception("source must be of type credit card", 1);
		
		if(!$amount)
			throw new Exception("amount can not be null", 1);
		
		if(!$transactionId)
			$transactionId=time();

		$method = "PUT";

		$customUri = "";
		$customUri .= "/order/" . $orderId;
		$customUri .= "/transaction/" . $transactionId;

		$merchantObj = new Merchant();
		$parserObj = new Parser($merchantObj);

		$data=array();
		$data['apiOperation'] ='PAY';

		$data['sourceOfFunds']['type'] ='CARD';

		$data['sourceOfFunds']['provided']['card']['number']=$source->card_number;
		$data['sourceOfFunds']['provided']['card']['expiry']['month']=$source->expiry_month;
		$data['sourceOfFunds']['provided']['card']['expiry']['year']=$source->expiry_year;
		$data['sourceOfFunds']['provided']['card']['securityCode']=$source->security_code;

		$data['order']['amount']=$amount;
		$data['order']['description']="Online purchese CityB2B  Pty Ltd";
		$data['order']['currency']='AUD';


		// form transaction request
		$request = $parserObj->ParseRequest($data);

		// print the request pre-send to server if in debug mode
		// this is used for debugging only. This would not be used in your integration, as DEBUG should be set to FALSE
		if ($merchantObj->GetDebug())
		  echo $request . "<br/><br/>";

		// forms the requestUrl and assigns it to the merchantObj gatewayUrl member
		// returns what was assigned to the gatewayUrl member for echoing if in debug mode
		$requestUrl = $parserObj->FormRequestUrl($merchantObj, $customUri);

		// this is used for debugging only. This would not be used in your integration, as DEBUG should be set to FALSE
		if ($merchantObj->GetDebug())
		  echo $requestUrl . "<br/><br/>";

		// $response is used in receipt page, do not change variable name
		$response = $parserObj->SendTransaction($merchantObj, $request, $method);
		// print response received from server if in debug mode
		// this is used for debugging only. This would not be used in your integration, as DEBUG should be set to FALSE
		if ($merchantObj->GetDebug()) {
		  // replace the newline chars with html newlines
		  $response = str_replace("\n", "<br/>", $response);
		  echo $response . "<br/><br/>";
		}


		return $this->validate_receipt($response);

		// the receipt page is included and displayed here.
		// in your integration, you would most likely also want process the transaction response, and make appropriate updates
		// you can see how to parse and retrieve the results and other fields in the transaction at the top of receipt.php

	}

	public function refund()
	{
		# code...
	}

	public function verify()
	{
		# code...
	}

	public function void()
	{
		# code...
	}

	public function retrieve_order()
	{
		# code...
	}

	public function retrieve_transaction()
	{
		# code...
	}

	public function update_authorization()
	{
		# code...
	}

	public function referral()
	{
		# code...
	}

	/**
	 * process row API response
	 * @param  [type] $response [description]
	 * @return [type]           [description]
	 */
	private function validate_receipt($response)
	{	
		$validate_result=array();

		$errorMessage = "";
		$errorCode = "";
		$gatewayCode = "";
		$result = "";

		$tmpArray = array();
		
		$responseArray = json_decode($response, TRUE);

		// echo "<pre>";
		// var_dump($responseArray);

		if ($responseArray == NULL) {
		  	throw new Exception("JSON decode failed. Please review server response from CBA", 1);
		}

		if (array_key_exists("result", $responseArray))
  			$result = $responseArray["result"];

  		if (array_key_exists("response", $responseArray))
			$gatewayCode = $responseArray["response"]['gatewayCode'];


		switch ($result) {
			case 'ERROR':
				$errorCode=$responseArray['error']['cause'];
				$errorMessage=$responseArray['error']['explanation'];
				break;
			
			case 'FAILURE':
				# code...
				break;

			case 'PENDING':
				# code...
				break;

			case 'SUCCESS':
				# code...
				break;

			case 'UNKNOWN':
				# code...
				break;
			default:
				throw new Exception("invaild result code", 1);
				
				break;
		}

		$validate_result['errorMessage'] =$errorMessage;
		$validate_result['errorCode']    =$errorCode;
		$validate_result['gatewayCode']  =$gatewayCode;
		$validate_result['result']       =$result;

		return $validate_result;

		// echo "<h1>errorCode:$errorCode</h1>";
		// echo "<h1>errorMessage:$errorMessage</h1>";
		// echo "<h1>gatewayCode:$gatewayCode</h1>";
		// echo "<h1>result:$result</h1>";

	}
}

/**
* 
*/
class CreditCard
{
	public $card_number;
	public $expiry_month;
	public $expiry_year;
	public $security_code;
}

/**
* 
*/
class TestCreditCardGenerator
{

	//Type
	const MASTERCARD            ="mastercard";
	const VISA                  ="visa";
	const AMERICAN_EXPRESS      ="american_express";
	const DINERS_CLUB           ="diners_club";
	const JCB                   ="jcb";
	const DISCOVER              ="discover"	;
	const MAESTRO               ="maestro"	;

	//date_error
	const APPROVED              = 'approved';
	const DECLINED              = 'declined';
	const EXPIRED_CARD          = 'expired_card';
	const TIMED_OUT             = 'timed_out';
	const ACQUIRER_SYSTEM_ERROR = 'acquirer_system_error';
	const UNSPECIFIED_FAILURE   = 'unspecified_failure';
	const UNKNOWN               = 'unknown';

	//code_error
	const MATCH                 ='match';
	const NOT_PROCESSED         ='not_processed';
	const NO_MATCH              ='no_match';


	public function init($type=self::MASTERCARD,$date_status=self::APPROVED,$code_status=self::MATCH,$secure_enrolled=true)
	{
		$testCard = new CreditCard();

		switch ($type) {
			case self::MASTERCARD:
				$testCard->card_number = ($secure_enrolled)?'5123450000000008':'5111111111111118';
				break;       
			case self::VISA:
				$testCard->card_number = ($secure_enrolled)?'4508750015741019':'4012000033330026';
				break;             
			case self::AMERICAN_EXPRESS:
				$testCard->card_number = ($secure_enrolled)?'345678901234564':'371449635398431';
				break; 
			case self::DINERS_CLUB:
				$testCard->card_number = ($secure_enrolled)?'30123400000000':'36259600000012';
				break;      
			case self::JCB:
				$testCard->card_number = ($secure_enrolled)?'3528000000000007':'3528111100000001';
				break;              
			case self::DISCOVER:
				$testCard->card_number = ($secure_enrolled)?'6011003179988686':'6011963280099774';
				break;         	
			case self::MAESTRO:
				$testCard->card_number = ($secure_enrolled)?'5000000000000000005':'5666555544443333';
				break;          	
			default:
				throw new Exception("unknow test credit card type", 1);
				break;
		}


		switch ($date_status) {
			case self::APPROVED:
				$testCard->expiry_month='05';
				$testCard->expiry_year='21';
				break;
			case self::DECLINED:
				$testCard->expiry_month='05';
				$testCard->expiry_year='22';
				break;
			case self::EXPIRED_CARD:
				$testCard->expiry_month='04';
				$testCard->expiry_year='27';
				break;
			case self::TIMED_OUT:
				$testCard->expiry_month='08';
				$testCard->expiry_year='28';
				break;
			case self::ACQUIRER_SYSTEM_ERROR:
				$testCard->expiry_month='01';
				$testCard->expiry_year='37';
				break;
			case self::UNSPECIFIED_FAILURE:
				$testCard->expiry_month='02';
				$testCard->expiry_year='37';
				break;
			case self::UNKNOWN:
				$testCard->expiry_month='05';
				$testCard->expiry_year='37';
				break;
			
			default:
				throw new Exception("unknow test credit card date_error_type", 1);
				break;
		}


		switch ($code_status) {
			case self::MATCH:
				$testCard->security_code=($type==self::AMERICAN_EXPRESS)?1000:100;
				break;
			case self::NOT_PROCESSED:
				$testCard->security_code=($type==self::AMERICAN_EXPRESS)?1010:101;
				break;
			case self::NO_MATCH:
				$testCard->security_code=($type==self::AMERICAN_EXPRESS)?1020:102;
				break;

			default:
				throw new Exception("unknow test credit card code_error_type", 1);
				break;
		}

				

		return $testCard;
	}	
}
?>