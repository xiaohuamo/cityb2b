<?php 

class OptimoRouteAPI {

	public $maxRetries = 1;
	public $connectTimeout = 30;
	public $timeout = 80;
	public $retryDelay = 0.5; 
	//public $maxAsyncRequests = 5;

//	private $apiKey = '7ce379ec24c46957d344883356facb69I9CGMbItfm0';
	//private $apiKey = '7ce379ec24c46957d344883356facb69I9CGMbItfm0';
	private $apiKey = 'c8f28b620a412b753759368b9690a456ZdCZFHJgtg';
	
	private static $apiBaseUrl = 'https://api.optimoroute.com/v1/';
	private $curlSession;
	private $reuseSession = false;


	public function __construct($apiKey=null) {
		if ($apiKey) {
			$this->apiKey = $apiKey;
		}
	}

	/**
	 * @param array $options
	 * @return \StdObject
	 * @throws \Exception
	 */
	public function createOrder($options) {
		$options['operation'] = 'CREATE';

		return $this->makeRequest('create_order', 'post', $options);
	}

	/**
	 * @param array $options
	 * @return \StdObject
	 * @throws \Exception
	 */
	public function syncOrder($options) {
		$options['operation'] = 'SYNC';

		return $this->makeRequest('create_order', 'post', $options);
	}

	/**
	 * @param string $orderNo
	 * @return \StdObject
	 * @throws \Exception
	 */
	public function deleteOrder($orderNo) {
		return $this->makeRequest('delete_order', 'post', ['orderNo' => $orderNo]);
	}

	/**
	 * @param string $date
	 * @return \StdObject
	 * @throws \Exception
	 */
	public function deleteAllOrders($date) {
		return $this->makeRequest('delete_all_orders', 'post', ['date' => $date]);
	}

	/**
	 * @param string|null $date YYYY-MM-DD format, Defaults to today
	 * @param array $options
	 * @return \StdObject
	 * @throws \Exception
	 */
	public function getRoutes($date = null, $options = []) {
		if(!$date) $date = date('Y-m-d');
		$options['date'] = $date;

		return $this->makeRequest('get_routes', 'get', $options);
	}

	/**
	 * @param string $orderNo
	 * @return \StdObject
	 * @throws \Exception
	 */
	public function getSchedulingInfo($orderNo) {
		return $this->makeRequest('get_scheduling_info', 'get', ['orderNo' => $orderNo]);
	}

	/**
	 * @param string|null $date YYYY-MM-DD format, Defaults to today
	 * @param array|null $options
	 * @return \StdObject
	 * @throws \Exception
	 */
	public function startPlanning($date = null, $options = []) {
		if(!$date) $date = date('Y-m-d');
		$options['date'] = $date;

		return $this->makeRequest('start_planning', 'post', $options);
	}

	/**
	 * @param int $planningId
	 * @return \StdObject
	 * @throws \Exception
	 */
	public function stopPlanning($planningId) {
		return $this->makeRequest('stop_planning', 'post', ['planningId' => $planningId]);
	}

	/**
	 * @param int $planningId
	 * @return \StdObject
	 * @throws \Exception
	 */
	public function getPlanningStatus($planningId) {
		return $this->makeRequest('get_planning_status', 'get', ['planningId' => $planningId]);
	}

	/**
	 * @param array $options
	 * @return \StdObject
	 * @throws \Exception
	 */
	public function updateDriverParameters($options) {
		return $this->makeRequest('update_driver_parameters', 'post', ['planningId' => $planningId]);
	}

	/**
	 * @param string|null $afterTag
	 * @return \StdObject
	 * @throws \Exception
	 */
	public function getMobileEvents($afterTag = null) {
		return $this->makeRequest('get_events', 'post', ['after_tag' => $afterTag]);
	}


	/*
	 * @todo; Create an optional mechanism for grouping requests into a curl_multi request
	 */
	 /* public function createOrders(array $orders) {

		$orderBatches = array_chunk($orders, $this->maxAsyncRequests);

		foreach($orderBatches as $orderBatch) {
			$requests = [];

			// Create the individual requests (handles/sessions)
			foreach($orderBatch as $order) {
				$requests = $this->buildRequest('create_order', 'post', $order);
			}

			$multi = curl_multi_init();

			// Add the requests to the multi handle
			foreach($requests as $request) {
				curl_multi_add_handle($multi, $request);
			}

			// Execute all requests async
			do {
			    $mrc = curl_multi_exec($multi, $active);
			} while ($mrc == CURLM_CALL_MULTI_PERFORM);


			// Shut down this batch and all its individual requests
			foreach($requests as $request) {
				curl_multi_remove_handle($multi, $request);
			}

			curl_multi_close($multi);

			foreach($requests as $request) {
				curl_close($request);
			}
		}
	}*/


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

		// If optimo returned an error, convert it to an exception
		if($rbody->success == false) {
			$message = '';
			if(isset($rbody->message))
				$message = $rbody->message;
			else {
				$message = 'OptimoRoute Error Code: ' . (int)$rbody->code;
			}

			throw new \Exception($message, (int)$rbody->code);
		}

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
		$opts[CURLOPT_URL] = self::$apiBaseUrl . $urlSegment . '?key=' . $this->apiKey;
		$opts[CURLOPT_RETURNTRANSFER] = true; // Don't echo response

		if($method === 'post') {
			$opts[CURLOPT_POST] = true;
			$opts[CURLOPT_POSTFIELDS] = json_encode($data);
		}
		else {
			$opts[CURLOPT_HTTPGET] = true;
			$opts[CURLOPT_URL] .= '&' . http_build_query($data);
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