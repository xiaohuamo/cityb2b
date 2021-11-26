<?php 
/**
 * 四方开放平台
 * http://open.au.4px.com/apiInfo
 */
class FourpxAPI 
{
	//config
	const APP_KEY = '86d4ac4c-a52f-45ed-b7ce-f71a0b27298c';
	const APP_SECRET = '272a5c14-0ca0-497f-ab06-97abc99eb6cc';

	const SANDBOX_APP_KEY = 'c7665454-12e6-4266-a2f2-0bbbc181dcd3';
	const SANDBOX_APP_SECRET = '4e511e36-e49c-4310-94fc-e9da1f5530ca';
	const FORMAT = 'json';
	const VERSION = '1.0';

	const SANDBOXSERVER = "open.sandbox.4px.com";
	const SERVER = "open.au.4px.com";

	const API_CALL_SUCCESS = 1;
	const API_CALL_FAILED = 0;

	private $sandbox;

	// logistics_product_name_cn: "澳邮Eparcel快递",
	const LOGISTICS_PRODUCT_AU_EPARCEL_EXPRESS = "F030";
	// logistics_product_name_cn: "澳邮Eparcel包裹",
	const LOGISTICS_PRODUCT_AUPARCEL = "F031";
	// logistics_product_name_cn: "澳洲TOLL本地大货",
	const LOGISTICS_PRODUCT_AU_TOLL_INLAND_HEAVY_DUTY_CARGO_DELIVERY = "F032";
	// logistics_product_name_cn: "澳洲TOLL-FBA本地大货",
	const LOGISTICS_PRODUCT_AU_TOLL_INLAND_FBA_HEAVY_DUTY_CARGO_DELIVERY = "F033";
	// logistics_product_name_cn: "澳洲挂号LETTER服务",
	const LOGISTICS_PRODUCT_AU_POST_REGISTERED_LETTER = "F035";
	// logistics_product_name_cn: "澳洲平邮LETTER服务",
	const LOGISTICS_PRODUCT_AU_POST_ORDINARY_LETTER = "F036";
	// logistics_product_name_cn: "墨尔本同城",
	const LOGISTICS_PRODUCT_MEL_DOMESTIC_DELIVERY = "F400";

	function __construct()
	{
		$this->sandbox = false;
	}

	public function enableSandBox($enable)
	{
		$this->sandbox = $enable;
	}

	public function serverUrl()
	{
		return ($this->sandbox) ? self::SANDBOXSERVER : self::SERVER;
	}

	public function appKey()
	{
		return ($this->sandbox) ? self::SANDBOX_APP_KEY : self::APP_KEY;
	}

	public function appSecret()
	{
		return ($this->sandbox) ? self::SANDBOX_APP_SECRET : self::APP_SECRET;
	}

	public function call()
	{	
		$app_key='c7665454-12e6-4266-a2f2-0bbbc181dcd3';
		$format='json';
		$method='fu.wms.outbound.create';
		$timestamp = time() * 1000;
		$v='1.0';

		$app_secret='4e511e36-e49c-4310-94fc-e9da1f5530ca';

		$body = array(
			'aa'=>'bb'
		);

		$str = 'app_key'.$app_key.'format'.$format.'method'.$method.'timestamp'.$timestamp.'v'.$v.json_encode($body).$app_secret;

		$sign = md5($str);

		$url = self::SANDBOXSERVER . "/router/api/service?";

		$url .= http_build_query([
			"method" => $method,
			"app_key" => $app_key,
			"v" => $v,
			"timestamp" => $timestamp,
			"format" => $format,
			"sign" => $sign,
		]);

		$ch = curl_init(); 

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$result=curl_exec($ch);
		curl_close($ch);
		echo $result;
		exit;
	}

	
	private function http_call($action, $body)
	{
		$url = $this->getActionUrl($action, $body);
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$result=curl_exec($ch);

		if (!$result) {
			throw new Exception("Network issue:".curl_error($ch), 1);
		}

		curl_close($ch);
		return $result;
	}

	private function getActionUrl($method, $body)
	{
		$timestamp = time() * 1000;

		$str = 'app_key'.$this->appKey()
			.'format'.self::FORMAT
			.'method'.$method
			.'timestamp'.$timestamp
			.'v'.self::VERSION
			.json_encode($body)
			.$this->appSecret();

		$sign = md5($str);

		$url = $this->serverUrl() . "/router/api/service?";

		$url .= http_build_query([
			"method" => $method,
			"app_key" => $this->appKey(),
			"v" => self::VERSION,
			"timestamp" => $timestamp,
			"format" => self::FORMAT,
			"sign" => $sign,
		]);

		return $url;
	}


	//订单轨迹查询
	public function orderTrackingAction()
	{
		$action = FourpxAPIAction::TRACKING_GET;

		$body = [
			'deliveryOrderNo' => '1Z8E26Y00366094077'
		];

		$response = $this->http_call($action, $body);

		return $response;
	}

	//云仓出库委托
	// $body = [
	// 	"ref_no"=> "testorder001",   //Require 
	// 	"country"=> "AU",    //Require
	// 	"state"=> "VIC",      //Require
	// 	"city"=> "MELBOURNE",    //Require
	// 	"post_code"=> "3000",     //Require
	// 	"street"=> "JDHSDHFSDHFKS",  //Require
	// 	"house_number"=> "138",      //Require
	// 	"last_name"=> "Last Name",   //Require
	// 	"first_name"=> "Jack",       //Require
	// 	"phone"=> "15644556258",
	// 	"email"=> "test@163.com"
	//  "logistics"=> "F400",
	// 	"oconsignment_sku"=> [[
	// 		"sku_code"=> "LLLETTER",    //Require
	// 		"qty"=> 1,				  //Require
	// 	]]
	// ];

	public function outboundCreateAction($data)
	{
		$action = FourpxAPIAction::OUTBOUND_CREATE;

		$body = [
			// "customer_code"=> "",
			"ref_no"=> $data['ref_no'],   //Require 
			"from_warehouse_code"=> "AUMELA", //Require 
			"consignment_type"=> "S", 		  //Require  S(标准出库); D(下架销毁); R(退仓出库); O(其他出库)
			"logistics_service_info"=> [
				"logistics_product_code"=> $data['logistics'],   //Require
				// "ocustoms_service"=> "",
				// "icustoms_service"=> "",
				// "return_service"=> "",
				// "signature_service"=> "",
			],
			// "oda"=> "",
			// "sales_platform"=> "",
			// "seller_id"=> "",
			// "sales_no"=> "",
			// "insure_services"=> "",
			// "currency"=> "",
			// "insure_value"=> "",
			// "remark"=> "",
			"oconsignment_desc"=> [
				"country"=> $data['country'],    //Require
				"state"=> $data['state'],      //Require
				"city"=> $data['city'],    //Require
				// "district"=> "District",
				"post_code"=> $data['post_code'],     //Require
				"street"=>$data['house_number']." ".$data['street'],  //Require
				"house_number"=>'',      //Require
				// "company"=> "4PX",
				"last_name"=> $data['last_name'],   //Require
				"first_name"=> $data['first_name'],       //Require
				"phone"=> $data['phone'],
				"email"=> $data['email']
			],
			// "identity_info"=> [
			// 	"id_type"=> "ID CARD TYPE",
			// 	"id_card"=> "430524199111052479",
			// 	"id_front_pic"=> "data=>image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAMCAgMCAgMDAwMEAwMEBQgFBQQ==",
			// 	"id_back_pic"=> "data=>image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAMCAgMCAgMDAwMEAwMEBQgFBQQ=="
			// ],
			"oconsignment_sku"=> $data['oconsignment_sku']
		];

		$response = $this->http_call($action, $body);

		return json_decode($response, true);
	}

	public function outboundGetListAction()
	{
		$action = FourpxAPIAction::OUTBOUND_GETLIST;

		$body = [
			// "ref_no" => "",
			// "consignment_no" => "",
			// "4px_tracking_no" => "",
			"status" => "S", //N（草稿）；S（出库中）；P（已下架）；C（已出库）；X（已取消）；D（已删除）；E（异常）
			// "sku_code" => "",
			"from_warehouse_code" => "AUMELA",
			// "logistics_product_code" => "",
			// "sales_platform" => "",
			// "seller_id" => "",
			// "sales_no" => "",
			// "shipping_no" => "",
			// "country" => "",
			"page_no" => 1,
			"page_size" => 50
		];

		$response = $this->http_call($action, $body);

		return json_decode($response, true);
	}


	//查询物流产品信息
	public function getLogisticsProductListAction()
	{
		$action = FourpxAPIAction::LOGISTICS_PRODUCT_LIST;

		$body = [
			'service_code' => 'F',       //Required F(订单履约)
			'category_code' => 'end',    //Required first(头程);warehouse(仓内);end(尾程)。
			'source_position_code' => 'AUMELA',
			'dest_position_code' => '',
			'dest_country_code' => '',
			'dest_post_code' => '',
			'dest_state_name' => '',
			'dest_city_name' => ''
		];

		$response = $this->http_call($action, $body);

		return $response;
	}

	// 查询仓库信息
	public function getWarehouseListAction()
	{
		$action = FourpxAPIAction::WAREHOUSE_LIST;

		$body = [
			'service_code' => 'F',
			'country' => 'AU',
		];

		$response = $this->http_call($action, $body);

		return $response;
	}

	public static function isFourpxTrackingOperator($trackingOperator)
	{
		$list = [
			STATIC::LOGISTICS_PRODUCT_AU_EPARCEL_EXPRESS,
			STATIC::LOGISTICS_PRODUCT_AUPARCEL,
			STATIC::LOGISTICS_PRODUCT_AU_TOLL_INLAND_HEAVY_DUTY_CARGO_DELIVERY,
			STATIC::LOGISTICS_PRODUCT_AU_TOLL_INLAND_FBA_HEAVY_DUTY_CARGO_DELIVERY,
			STATIC::LOGISTICS_PRODUCT_AU_POST_REGISTERED_LETTER,
			STATIC::LOGISTICS_PRODUCT_AU_POST_ORDINARY_LETTER,
			STATIC::LOGISTICS_PRODUCT_MEL_DOMESTIC_DELIVERY,
		];
		return in_array($trackingOperator, $list);
	}

	public function getAusPostTrackingId($consignmentNo)
	{	
		$loopCount = 0;

		$trackingId = '';

		 while ($trackingId === '') {
			$res = $this->outboundGetListAction();
			$list = $res['data']['data'];
			
			$result = array_filter($list, function($item) use ($consignmentNo){
				return ($item['consignment_no'] == $consignmentNo);
			});
			
			if (sizeof($result) > 0) {
				$result= array_shift($result);
				$trackingId = $result['shipping_no'];
			}

			$loopCount++;
			if ($loopCount>5) {
				throw new Exception("getAusPostTrackingId timeout", 1);
			}

			if ($trackingId === '') {
				sleep(4);
			}
		}

		return $trackingId;
	}

	public static function isCoveredArea($postcode)
	{
		$coveredArea = [
			3000,//MELBOURNE
			3073,//RESERVOIR EAST
			3143,//ARMADALE NORTH
			3001,//MELBOURNE
			3073,//RESERVOIR NORTH
			3144,//KOOYONG
			3002,//EAST MELBOURNE
			3073,//RESERVOIR SOUTH
			3144,//MALVERN
			3003,//WEST MELBOURNE
			3078,//ALPHINGTON
			3144,//MALVERN NORTH
			3004,//MELBOURNE
			3078,//FAIRFIELD
			3145,//CAULFIELD EAST
			3004,//ST KILDA ROAD CENTRAL
			3079,//IVANHOE
			3145,//CENTRAL PARK
			3005,//WORLD TRADE CENTRE
			3079,//IVANHOE EAST
			3145,//DARLING
			3006,//SOUTH WHARF
			3079,//IVANHOE NORTH
			3145,//DARLING SOUTH
			3006,//SOUTHBANK
			3083,//BUNDOORA
			3145,//MALVERN EAST
			3008,//DOCKLANDS
			3083,//KINGSBURY
			3146,//GLEN IRIS
			3010,//UNIVERSITY OF MELBOURNE
			3083,//LA TROBE UNIVERSITY
			3147,//ASHBURTON
			3011,//FOOTSCRAY,
			3101,//KEW
			3147,//ASHWOOD
			3011,//SEDDON
			3102,//KEW EAST
			3148,//CHADSTONE
			3011,//SEDDON WEST
			3103,//BALWYN
			3148,//CHADSTONE CENTRE
			3012,//BROOKLYN
			3103,//BALWYN EAST
			3148,//HOLMESGLEN
			3012,//KINGSVILLE
			3103,//DEEPDENE
			3148,//JORDANVILLE
			3012,//KINGSVILLE WEST
			3104,//BALWYN NORTH
			3149,//MOUNT WAVERLEY
			3012,//MAIDSTONE
			3104,//GREYTHORN
			3149,//SYNDAL
			3012,//TOTTENHAM
			3105,//BULLEEN
			3150,//BRANDON PARK
			3012,//WEST FOOTSCRAY
			3105,//BULLEEN SOUTH
			3150,//GLEN WAVERLEY
			3013,//YARRAVILLE
			3106,//TEMPLESTOWE
			3150,//WHEELERS HILL
			3013,//YARRAVILLE WEST
			3107,//TEMPLESTOWE LOWER
			3151,//BURWOOD EAST
			3019,//BRAYBROOK
			3108,//DONCASTER
			3151,//BURWOOD HEIGHTS
			3019,//BRAYBROOK NORTH
			3109,//DONCASTER EAST
			3161,//CAULFIELD JUNCTION
			3020,//ALBION
			3109,//DONCASTER HEIGHTS
			3161,//CAULFIELD NORTH
			3020,//SUNSHINE
			3122,//HAWTHORN
			3162,//CAULFIELD
			3020,//SUNSHINE NORTH
			3122,//HAWTHORN NORTH
			3162,//CAULFIELD SOUTH
			3020,//SUNSHINE WEST
			3122,//HAWTHORN WEST
			3162,//HOPETOUN GARDENS
			3021,//ST ALBANS
			3123,//AUBURN
			3163,//CARNEGIE
			3022,//ARDEER
			3123,//HAWTHORN EAST
			3163,//GLEN HUNTLY
			3022,//DEER PARK EAST
			3124,//CAMBERWELL
			3163,//MURRUMBEENA
			3023,//DEER PARK
			3125,//BENNETTSWOOD
			3166,//HUGHESDALE
			3023,//DEER PARK NORTH
			3125,//BURWOOD
			3166,//HUNTINGDALE
			3027,//WILLIAMS LANDING
			3125,//SURREY HILLS SOUTH
			3166,//OAKLEIGH
			3029,//HOPPERS CROSSING
			3126,//CANTERBURY
			3166,//OAKLEIGH EAST
			3032,//MARIBYRNONG
			3127,//MONT ALBERT
			3167,//OAKLEIGH SOUTH
			3051,//NORTH MELBOURNE
			3127,//SURREY HILLS
			3168,//CLAYTON
			3052,//MELBOURNE UNIVERSITY
			3127,//SURREY HILLS NORTH
			3168,//NOTTING HILL
			3052,//PARKVILLE
			3128,//BOX HILL
			3169,//CLARINDA
			3053,//CARLTON
			3128,//BOX HILL CENTRAL
			3169,//CLAYTON SOUTH
			3053,//CARLTON SOUTH
			3128,//BOX HILL SOUTH
			3170,//MULGRAVE
			3070,//NORTHCOTE
			3129,//BOX HILL NORTH
			3171,//SPRINGVALE
			3070,//NORTHCOTE SOUTH
			3129,//MONT ALBERT NORTH
			3172,//DINGLEY VILLAGE
			3071,//THORNBURY
			3130,//BLACKBURN
			3172,//SPRINGVALE SOUTH
			3072,//GILBERTON
			3130,//BLACKBURN NORTH
			3173,//KEYSBOROUGH
			3072,//NORTHLAND CENTRE
			3130,//BLACKBURN SOUTH
			3174,//NOBLE PARK
			3072,//PRESTON
			3131,//BRENTFORD SQUARE
			3174,//NOBLE PARK EAST
			3072,//PRESTON LOWER
			3131,//FOREST HILL
			3174,//NOBLE PARK NORTH
			3072,//PRESTON SOUTH
			3131,//NUNAWADING
			3205,//SOUTH MELBOURNE
			3072,//PRESTON WEST
			3132,//MITCHAM
			3205,//SOUTH MELBOURNE DC
			3072,//REGENT WEST
			3133,//VERMONT
			3800,//MONASH UNIVERSITY
			3073,//KEON PARK
			3133,//VERMONT SOUTH
			3073,//RESERVOIR
			3143,//ARMADALE
		];
		return in_array($postcode, $coveredArea);
	}
}


/**
 *  4px 开放接口 action
 *  全部接口请查看文档 http://open.au.4px.com/apiInfo
 */
class FourpxAPIAction 
{
	//调用此接口可在4PX的订单履约服务中建立客户的SKU信息
	const SKU_CREATE = "fu.wms.sku.create";

	//调用此接口可在4PX的订单履约服务中可查询出SKU信息集合
	const SKU_GETLIST = "fu.wms.sku.getlist";

	//针对SKU创建时图片下载失败的，客户需要补录SKU图片。只支持图片下载失败的SKU补录图片信息。
	const SKU_EDITPICTURE = "fu.wms.sku.editpicture";

	//调用此接口可在4PX的订单履约服务中创建入库委托。
	const INBOUND_CREATE = "fu.wms.inbound.create";

	//调用此接口可在4PX的订单履约服务中取消入库委托。
	const INBOUND_CANCEL = "fu.wms.inbound.cancel";

	//调用此接口可在4PX的订单履约服务中查询入库委托信息。查询优先级：如果 入库委托单号 、 入库委托参考号、 const 4px跟踪号三个参数不为空，则优先按单号查询
	const INBOUND_GETLIST = "fu.wms.inbound.getlist";

	//调用此接口可在4PX的订单履约服务中创建出库委托。
	const OUTBOUND_CREATE = "fu.wms.outbound.create";

	//调用此接口可在4PX的订单履约服务中取消出库委托。取消成功悔返回委托单状态，如果状态是未拣货状态，则不增加取消费用。如果状态是已拣货未出库const 状态，则取消的委托单会增加额外取消费用
	const OUTBOUND_CANCEL = "fu.wms.outbound.cancel";

	//批量查询出库委托
	const OUTBOUND_GETLIST = "fu.wms.outbound.getlist";

	//查询出库委托费用
	const OUTBOUND_GETBILLING = "fu.wms.outbound.getbilling";

	//库存查询
	const INVENTORY_GET = "fu.wms.inventory.get";

	//查询库存库龄	
	const INVENTORY_GETDETAIL = "fu.wms.inventory.getdetail";

	//查询库存流水
	const INVENTORY_GETLOG = "fu.wms.inventory.getlog";

	//物流轨迹查询
	const TRACKING_GET = "tr.order.tracking.get";

	//查询物流产品信息
	const LOGISTICS_PRODUCT_LIST = "com.basis.logistics_product.getlist";

	//查询仓库信息
	const WAREHOUSE_LIST = "com.basis.warehouse.getlist";
}

