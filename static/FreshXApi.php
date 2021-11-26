<?php 

/**
 * 
 */
class FreshXApi
{	
	public $maxRetries = 1;
	public $connectTimeout = 30;
	public $timeout = 80;
	public $retryDelay = 0.5;
	//public $maxAsyncRequests = 5;

	private $curlSession;
	private $reuseSession = false;

	public static $apiBaseUrl = 'https://b2b.freshxproduce.com.au';

	private $username ='wang1230ji@gmail.com';
	private $password = 'uBonusFreshxApiAccess2020';
	private $clientId = '2_3oaais6g39c00oo0skg4gc4w4888w0ccc0888sccsow4swso84';
	private $clientSecret = '5f92l5aoi1kwso80soo4w4cswcgwwo0o0ogo08kw0k40gc00s0';

	private $access_token = '';
	private $refresh_token = '';

	const URL_LOGIN = "/oauth/v2/token";
	const URL_PRICE = "/api/coa/v1/pricelist";
	const URL_ORDER = "/api/coa/v1/order/";

	public function login()
	{	
		$url = static::URL_LOGIN;

		$data = [
			"grant_type" => 'password',
			"client_id" => $this->clientId,
			"client_secret" => $this->clientSecret,
			"username" => $this->username,
			"password" => $this->password
		];

		$result = $this->makeRequest($url, "post", $data);
		
		$this->access_token = $result->access_token;
		$this->refresh_token = $result->refresh_token;
	}


	public function refreshTocken()
	{
		$url = static::URL_LOGIN;

		$data = [
			"grant_type" => "refresh_token",
			"client_id" => $this->clientId,
			"client_secret" => $this->clientSecret,
			"refresh_token" => $this->refresh_token,
		];

		$result = $this->makeRequest($url, "post", $data);
		
		$this->access_token = $result->access_token;
		$this->refresh_token = $result->refresh_token;
	}

	public function getProductList()
	{
		$url = static::URL_PRICE;
		$data['access_token'] = $this->access_token;
		$result = $this->makeRequest($url, "get", $data);

		return $result;
	}

	public function createOrder($data)
	{
		$url = static::URL_ORDER . "?access_token=" . $this->access_token;

		$result = $this->makeRequest($url, "post", $data);
		if ($result->message == 'Order is created successfully') {
			return $result->order_id;
		} else {
			throw new Exception($result->message, 1);
		}
	}

	public function updateOrder($orderId, $data)
	{
		$url = static::URL_ORDER . $orderId . "?access_token=" . $this->access_token;

		$result = $this->makeRequest($url, "post", $data);

		if ($result->message == 'Order is updated successfully') {
			return $result->order_id;
		} else {
			return false;
		}
	}

	public function getOrder($orderId)
	{
		$url = static::URL_ORDER . $orderId . "?access_token=" . $this->access_token;;
		$result = $this->makeRequest($url, "get", $data);
		return $result;
	}

	public function getOrderList($deliveryDateStr = '')
	{
		$url = static::URL_ORDER;
		$data = [];
		$data['delivery_date'] = $deliveryDateStr; // format 2020-05-22
		$data['access_token'] = $this->access_token;
		$result = $this->makeRequest($url, "get", $data);

		return $result;
	}

	/**
	 * Builds and executes a curl request to the OptimoRoute API and returns its result. Retries on failure.
	 *
	 * @param string $urlSegment
	 * @param string $method
	 * @param array|null $data
	 * @return \StdObject response from the OptimoRoute API
	 * @throws \Exception on curl error or error response from OptimoRoute
	 */
	private function makeRequest($urlSegment, $method, $data = null) {

		$request = $this->buildRequest($urlSegment, $method, $data);
		// Try the request, up to our $maxRetries limit
		$numTries = 0;
		while(true) {
			try {
				$numTries++;
				$rbody = $this->executeCurl($request);
			}
			catch(\Exception $e) {
				if($numTries > $this->maxRetries) {
					throw $e; // Rethrow the exception if we've used up our retries, otherwise ignore it and try again
				}
				else {
					usleep(intval($this->retryDelay * 1000000)); // Delay the retry by the $retryDelay
					continue;
				}
			}

			$rbody->numTries = $numTries; // Pass along the number of tries the request took in the response
			break;
		}

		if(!$this->reuseSession) $this->closeSession();

		return $rbody;
	}

	/**
	 * Called by makeRequest() on each request attempt
	 *
	 * @param $request
	 * @return \StdObject response from the OptimoRoute API
	 * @throws \Exception
	 */
	private function executeCurl($request) {
		$rbody = curl_exec($request);
		// If the curl request failed, convert it to an exception
		if($rbody === false) {
			$errno = curl_errno($request);
			$message = curl_error($request);
			throw new \Exception($message, (int)$errno);
		}

		$rbody = json_decode($rbody);

		return $rbody;
	}

	/**
	 * Prepares a curl session/handle for execution
	 *
	 * @param string $urlSegment
	 * @param string $method post or get
	 * @param array|null $data data to send with the request
	 * @return curl session/handle
	 */
	private function buildRequest($urlSegment, $method, $data = null) {
		$opts[CURLOPT_HTTPHEADER] = ['Content-Type:application/json'];
		$opts[CURLOPT_TIMEOUT] = $this->timeout;
		$opts[CURLOPT_CONNECTTIMEOUT] = $this->connectTimeout;
		$opts[CURLOPT_URL] = self::$apiBaseUrl . $urlSegment;
		$opts[CURLOPT_RETURNTRANSFER] = true; // Don't echo response
		$opts[CURLOPT_FOLLOWLOCATION] = true;
		$opts[CURLOPT_SSL_VERIFYPEER ] = false;

		if($method === 'post') {
			$opts[CURLOPT_POST] = true;
			$opts[CURLOPT_POSTFIELDS] = json_encode($data);
		}
		else {
			$opts[CURLOPT_HTTPGET] = true;
				if ($data)
					$opts[CURLOPT_URL] .= '?' . http_build_query($data);
		}

		$request = $this->getSession();
		curl_setopt_array($request, $opts);

		return $request;
	}


	private function getSession() {
		if(!$this->curlSession) {
			$this->curlSession = curl_init();
		}
		return $this->curlSession;
	}

	public function closeSession() {
		if($this->curlSession !== null) {
			curl_close($this->curlSession);
			$this->curlSession = null;
		}
	}

	/**
	 * Should the same curl session/handle be reused for every request? (Setting to true will improve performance, but you will have to manually call closeSession() after using the API.
	 *
	 * @param bool $value
	 */
	public function setReuseSession($value) {
		$this->reuseSession = $value;
	}

}