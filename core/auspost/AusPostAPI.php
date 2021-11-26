<?php 

class AusPostAPI 
{
	const APIKEY='056ba946-da2b-4700-82fb-3e7f13114568';
	const URLPREFIX = 'https://digitalapi.auspost.com.au';
	
	public $result_as_rowjson = true;
	public $result_as_array = true;

	private function queryAPI($url)
	{
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('AUTH-KEY: ' . self::APIKEY));
		$rawBody = curl_exec($ch);

		// Check the response: if the body is empty then an error occurred
		if(!$rawBody){
		  die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
		}

		if($this->result_as_rowjson){
			return $rawBody;
		}else{
			return json_decode($rawBody,$this->result_as_array);
		}
		
	}


	/**
	 * [Retrieve Australia Post standard parcel sizes]
	 * @return [type] [description]
	 */
	public function getStandardParcelSizes()
	{	
		$actionUrl ='/postage/parcel/domestic/size.json';
		$url = self::URLPREFIX . $actionUrl;

		return $this->queryAPI($url);
	}

	/**
	 * [Retrieve a list of available domestic postage services]
	 * @param  string $value [description]
	 * @return [type]        [description]
	 */
	public function getAvailableDomesticPostageServices($value='')
	{
		// Define the service input parameters
		$fromPostcode = '2000';
		$toPostcode = '3000';
		$parcelLengthInCMs = 22;
		$parcelWidthInCMs = 16;
		$parcelHeighthInCMs = 7.7;
		$parcelWeightInKGs = 1.5;

		// Set the query params
		$queryParams = array(
		  "from_postcode" => $fromPostcode,
		  "to_postcode" => $toPostcode,
		  "length" => $parcelLengthInCMs,
		  "width" => $parcelWidthInCMs,
		  "height" => $parcelHeighthInCMs,
		  "weight" => $parcelWeightInKGs
		);


		$actionUrl ='/postage/parcel/domestic/service.json?';
		$url = self::URLPREFIX . $actionUrl . http_build_query($queryParams);

		return $this->queryAPI($url);
	}

	/**
	 * Calculate total delivery price
	 * @param  string $value [description]
	 * @return [type]        [description]
	 */
	public function getDeliveryPrice()
	{
		
		// Define the service input parameters
		$fromPostcode = '2000';
		$toPostcode = '3000';
		$parcelLengthInCMs = 10;
		$parcelWidthInCMs = 10;
		$parcelHeighthInCMs = 10;
		$parcelWeightInKGs = 1;

		// Set the query params
		$queryParams = array(
		  "from_postcode" => $fromPostcode,
		  "to_postcode" => $toPostcode,
		  "length" => $parcelLengthInCMs,
		  "width" => $parcelWidthInCMs,
		  "height" => $parcelHeighthInCMs,
		  "weight" => $parcelWeightInKGs,
		  "service_code" => 'AUS_PARCEL_REGULAR'
		);


		$actionUrl ='/postage/parcel/domestic/calculate.json?';
		$url = self::URLPREFIX . $actionUrl . http_build_query($queryParams);

		return $this->queryAPI($url);
	}


}