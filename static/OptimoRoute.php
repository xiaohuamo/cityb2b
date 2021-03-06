<?php 
require_once( DOC_DIR.'static/OptimoRouteAPI.php');
    	
class OptimoRoute 
{
	private $api;
	private $dispCenterId;

	function __construct($dispCenterId, $apiKey=null)
	{
		$this->api = new OptimoRouteAPI($apiKey);
		$this->dispCenterId = $dispCenterId;
	}


    public function deleteAllOrders($dateStr) {

        $data = [
            "date"=>$dateStr
        ];

        try {
            $response = $this->api->deleteAllOrders($dateStr);
            return $response;
        } catch (Exception $e) {
            throw new Exception("Error happened when delete orders of ".$dateStr.'. '.$e->getMessage(), 1);
        }
    }



    public function startPlanning($date = null, $options = [])
    {
        $plan =  $this->api->startPlanning($date, $options);
    //  var_dump($plan);exit;
        return $plan;
    }

    public function get_planning_status($planningId){

        try {
            $response =  $this->api->getPlanningStatus($planningId);
            return $response;
        } catch (Exception $e) {
            throw new Exception("Error happened when get planning status .".$e->getMessage(), 1);
        }


    }

	/**
	 * 将送货日期当天的订单同步到Optimoroute
	 */
	public function syncOrderOnDate($dateStr,$auto,$schedule_id)
	{
      //  var_dump($auto);exit;
       $picksList =$this->getPickListOnDeliveryDate($dateStr,0,$schedule_id);
        if($picksList){
            foreach ($picksList as $order) {
                if(!$order['phone']){
                    $order['phone']='111';
                }
                $data = [
                    "operation" => "SYNC",
                    "type" => "P",
                    "orderNo" => $order['orderId'],
                    "priority"=>substr($order['logistic_priority'],0,1),
                    "load1" => (int)$order['boxesNumber'],
                    "date" => date("Y-m-d",$order['logistic_delivery_date']),
                    "location" => [
                        "address" => $order['address'],
                        "acceptPartialMatch"=> true,
                        "acceptMultipleResults"=>true
                    ],
                    "phone" => $order['phone'],
                    // "email" => $order['email'],
                    "duration" => 10, //The time in minutes required to unload the goods or perform a task at the given location.
                    "notes"=> '',
                    //optimoRoute API 预留自定义字段，需要在optimoRoute后台开启后使用
                    "customField1" => $order['logistic_sequence_No'], //统配号
                    "customField2" => '0',
                    "customField3" => '1',
                    "customField4" => '',//$order['order_name'], // order name
                    "customField5" => '',
                ];


                try {
                    $response = $this->api->syncOrder($data);
                } catch (Exception $e) {
                    throw new Exception("Error when upload order ,please check info of address etc of (".$order['orderId']."),then do upload again!:".$e->getMessage(), 1);
                }
            }
        }



        $orders = $this->getOrderOnDeliverDate($dateStr,0,$schedule_id) ;
        if(!$orders){ //if no orders ,return 0
            var_dump('no orders!');exit;
        }
		foreach ($orders as $order) {
			$data = [
				"operation" => "SYNC",
				"type" => "D",
				"orderNo" => $order['orderId'],
                "priority"=>substr($order['logistic_priority'],0,1),
                "load1" => (int)$order['boxesNumber'],
				"date" => date("Y-m-d",$order['logistic_delivery_date']),
				"location" => [
					"address" => $order['address'],
                    "acceptPartialMatch"=> true,
                     "acceptMultipleResults"=>true
				],
				"phone" => $order['phone'],
				// "email" => $order['email'],
				"duration" => 10, //The time in minutes required to unload the goods or perform a task at the given location.
				"notes"=> $order['message_to_business'],
				//optimoRoute API 预留自定义字段，需要在optimoRoute后台开启后使用
				"customField1" => $order['logistic_sequence_No'], //统配号
				"customField2" => $order['logistic_suppliers_info'],
				"customField3" => $order['logistic_suppliers_count'],
				"customField4" => '',//$order['order_name'], // order name
				"customField5" => '',
			];
            //var_dump($order['logistic_driver_code'] .' 。'. !$auto);exit;
            if($order['logistic_schedule_id']>0 && !$auto){
                $data["assignedTo"]=["externalId"=>$order['opti_driver_id']];
            }

			try {
				$response = $this->api->syncOrder($data);	
			} catch (Exception $e) {
				throw new Exception("Error when upload order ,please check info of address etc of (".$order['orderId']."),then do upload again!:".$e->getMessage(), 1);
			}
		}
	}

    function getScheduleOnDeliveryDate($dateStr,$schedule_id) {


        $timestamp = strtotime($dateStr);

        if ($timestamp === false) {
            throw new Exception("dateStr is not recognized", 1);
        }
        $dateTime = new DateTime();
        $dateTime->setTimestamp($timestamp);
        $dateTime->setTime(0,0,0);
        $timestamp = $dateTime->getTimestamp();


        $mdl_schedule =loadModel('truck_driver_schedule');

        $factory_id =$this->dispCenterId;
       if($schedule_id){ //该调度且为已调度
           $sql ="select s.*,t.load_boxes from cc_truck_driver_schedule s  left join cc_truck t on s.factory_id =t.business_id and s.truck_id =t.truck_no  where s.factory_id =$factory_id and  s.delivery_date =$timestamp and s.schedule_id=$schedule_id and s.status=2 order by s.schedule_id";

       }else{
           $sql ="select s.*,t.load_boxes from cc_truck_driver_schedule s  left join cc_truck t on s.factory_id =t.business_id and s.truck_id =t.truck_no  where s.factory_id =$factory_id and  s.delivery_date =$timestamp  order by s.schedule_id";

       }
          $list =$mdl_schedule->getListBySql($sql);
//var_dump($list);exit;

        return $list;

    }

    public function setAllDriverDisable($dateStr,$driverCount) {

        if(!$driverCount){
            $driverCount =6;
        }
        $driver_id =101;
        $truck_id=101;
        $factory_id =$this->dispCenterId;
        $user = loadModel('user')->get($factory_id);
        for($i=0;$i<$driverCount;$i++){
            $data = [
                "externalId" => 'd'.$driver_id,
                "date"=> $dateStr,
                "enabled"=>false,
                "assignedVehicle"=>'v'.$truck_id,
                'workTimeFrom'=>'07:00',
                'workTimeTo'=>'16:00',
                'vehicleCapacity1'=>100,
                'startAddress'=>$user['googleMap'],
                'startLatitude'=>round($user['latitude'],6),
                'startLongitude'=>round($user['longitude'],6),
                'endAddress'=>$user['googleMap'],
                'endLatitude'=>round($user['latitude'],6),
                'endLongitude'=>round($user['longitude'],6)

            ];

            try {
                $response = $this->api->updateDriverParameters($data);
                //  var_dump($response);exit;
            } catch (Exception $e) {
              //  throw new Exception("Error when disable all drivers:".$e->getMessage(), 1);
            }

            $driver_id +=1;
            $truck_id +=1;
        }
    }

    public function updateSchedule($dateStr,$enabledisable,$schedule_id)
    {
        //  var_dump($auto);exit;
        //var_dump('here');exit;
        $schedule_list = $this->getScheduleOnDeliveryDate($dateStr,$schedule_id);
       // var_dump(json_encode($schedule_list));exit;
        foreach ( $schedule_list as $schedule) {
            $data = [
                "externalId" => $schedule['opti_driver_id'],
                "date"=> $dateStr,
                "enabled"=>$enabledisable,
                "assignedVehicle"=>$schedule['opti_truck_id'],
                'workTimeFrom'=>$schedule['driver_work_start_time'],
                'workTimeTo'=>$schedule['driver_work_end_time'],
                'vehicleCapacity1'=>intval($schedule['load_boxes']),
                'startAddress'=>$schedule['driver_start_location'],
                'startLatitude'=>round($schedule['driver_start_lat'],6),
                'startLongitude'=>round($schedule['driver_start_long'],6),
                'endAddress'=>$schedule['driver_end_location'],
                'endLatitude'=>round($schedule['dirver_end_lat'],6),
                'endLongitude'=>round($schedule['driver_end_long'],6),

            ];


//var_dump($data);exit;
            try {
                $response = $this->api->updateDriverParameters($data);
              //  var_dump($response);exit;
            } catch (Exception $e) {
                throw new Exception("Error when upload schedule,then do upload again!:".$e->getMessage(), 1);
            }

        }
    }

	public function generateLogisticSequence($dateStr)
	{
		$mdl_order = loadModel('order');
		$mdl_order_import = loadModel('order_import');

		$orders = $this->getOrderOnDeliverDate($dateStr,1,0); //all today's order

		$logistic_sequence_index = 0;

		foreach ($orders as $order) {
			if ($order['logistic_sequence_No'] > $logistic_sequence_index)
				$logistic_sequence_index = $order['logistic_sequence_No'];
		}
		//找到当前最大的 seq_number ;
		
		$logistic_sequence_index = $logistic_sequence_index < 1 ? 0 : $logistic_sequence_index;
		$logistic_sequence_count = $logistic_sequence_index; 
		
		
		foreach ($orders as $order) {
			//此处加入一个功能临时 ，修补之前没有处理的 两个新增字段
			//$logistic_suppliers_info=$mdl_order->gen_logistic_suppliers_info( $order['orderId'],$this->lang);
          
			$data = [	
				//'logistic_suppliers_info'=>$logistic_suppliers_info['logistic_suppliers_info'],
				//'logistic_suppliers_count'=>$logistic_suppliers_info['logistic_suppliers_count']
			];

			if ($order['logistic_sequence_No'] < 1) {
				$data ['logistic_sequence_No'] = ++$logistic_sequence_count;
			}
				
			if ($order['data_source']=='1') {
				$mdl_order->updateByWhere(
				$data, 
				['orderId' => $order['orderId']]			);
			}else if($order['data_source']=='2'){
				$mdl_order_import->updateByWhere(
				$data, 
				['orderId' => $order['orderId']]);
			
			}else{
				$mdl_order->updateByWhere(
				$data, 
				['orderId' => $order['orderId']]			);
				
			}
			
		}
	}

   public function getPickListOnDeliveryDate($dateStr,$allorder,$schedule_id)
    {

        if(!$allorder) {
            $timestamp = strtotime($dateStr);

            if ($timestamp === false) {
                throw new Exception("dateStr is not recognized", 1);
            }



            $mdl_picking = loadModel('picking');
            $current_user_id =$this->dispCenterId;
            $loginUserId =$this->current_business['id'];

            if(!$schedule_id){
                $sql ="select f.nickname ,cc_picking.*,s.opti_driver_id  from cc_picking  left join  cc_truck_driver_schedule s on cc_picking.business_userId = s.factory_id and cc_picking.logistic_schedule_id = s.schedule_id  left join cc_user_factory f on cc_picking.userId =f.user_id and cc_picking.business_userId = f.factory_id where logistic_delivery_date =$timestamp and coupon_status='p01'   ";

            }else{
                $sql ="select f.nickname ,cc_picking.*,s.opti_driver_id  from cc_picking  left join  cc_truck_driver_schedule s on cc_picking.business_userId = s.factory_id and cc_picking.logistic_schedule_id = s.schedule_id  left join cc_user_factory f on cc_picking.userId =f.user_id and cc_picking.business_userId = f.factory_id where logistic_delivery_date =$timestamp and coupon_status='p01'   and s.schedule_id =$schedule_id  ";

            }

            $sql .= " and ( business_userId =$current_user_id  )";
           // var_dump($sql);exit;





            $ubonusOrderList =$mdl_picking->getListBySql($sql);




            foreach ($ubonusOrderList as $key => $value) {

                $ubonusOrderList[$key]['data_source'] = '1'; //data from ubonus
            }


            return $ubonusOrderList;

        }
    }


    public function getOrderOnDeliverDate($dateStr,$allorder,$schedule_id)
	{
		
	if(!$allorder) {
		$timestamp = strtotime($dateStr);

		if ($timestamp === false) {
			throw new Exception("dateStr is not recognized", 1);
		}



		$mdl_order = loadModel('order');
		$current_user_id =$this->dispCenterId;
		$loginUserId =$this->current_business['id'];
		
		if(!$schedule_id){
            $sql ="select f.nickname ,cc_order.*,s.opti_driver_id  from cc_order  left join  cc_truck_driver_schedule s on cc_order.business_userId = s.factory_id and cc_order.logistic_schedule_id = s.schedule_id  left join cc_user_factory f on cc_order.userId =f.user_id and cc_order.business_userId = f.factory_id where logistic_delivery_date =$timestamp and coupon_status='c01' and ( cc_order.status=1 or accountPay =1)  ";

        }else{
            $sql ="select f.nickname ,cc_order.*,s.opti_driver_id  from cc_order  left join  cc_truck_driver_schedule s on cc_order.business_userId = s.factory_id and cc_order.logistic_schedule_id = s.schedule_id  left join cc_user_factory f on cc_order.userId =f.user_id and cc_order.business_userId = f.factory_id where logistic_delivery_date =$timestamp and coupon_status='c01' and ( cc_order.status=1 or accountPay =1)  and s.schedule_id =$schedule_id  ";

        }

		$sql .= " and ( business_userId =$current_user_id  ";
		$sql .= " or business_userId  in ( select cc_logistic_customers_id from cc_freshfood_logistic_customers where cc_logistic_business_id = $current_user_id) ";
		$sql .="  or business_userId in  (select customer_id from cc_factory2c_list where factroy_id =$current_user_id )  ";
		$sql .="  or business_userId in  (select customer_id from cc_factory_2blist where factroy_id =$current_user_id )  ";
		$sql .=" )";
		//var_dump($sql);exit;
		
		
		
		
		
		$ubonusOrderList =$mdl_order->getListBySql($sql);
		//var_dump($sql);exit;cc
		$sql ="select * from cc_order_import where logistic_delivery_date =$timestamp and coupon_status='c01' and  status =1   and business_userId  in ( select cc_logistic_customers_id from cc_freshfood_logistic_customers where cc_logistic_business_id = $current_user_id)";
	    $ubonusOrderImportList =$mdl_order->getListBySql($sql);
		
		
		
		foreach ($ubonusOrderList as $key => $value) {
		
		   $ubonusOrderList[$key]['data_source'] = '1'; //data from ubonus
		 }
	
		if($ubonusOrderImportList) {
			foreach ($ubonusOrderImportList as $key => $value) {
		
		   $ubonusOrderImportList[$key]['data_source'] = '2'; //data from ubonus
		 }
			$ubonusOrderList=array_merge($ubonusOrderList,$ubonusOrderImportList);
		}
		
		
		return $ubonusOrderList;
		
	} else{
		
		$timestamp = strtotime($dateStr);

		if ($timestamp === false) {
			throw new Exception("dateStr is not recognized", 1);
		}
		$dateTime = new DateTime();
		$dateTime->setTimestamp($timestamp);
		$dateTime->setTime(0,0,0);
		$timestamp = $dateTime->getTimestamp();


		$mdl_order = loadModel('order');
		$current_user_id =$this->dispCenterId;
		
		$sql ="select f.nickname ,cc_order.* from cc_order left join cc_user_factory f on cc_order.userId =f.user_id and cc_order.business_userId = f.factory_id  where logistic_delivery_date =$timestamp and coupon_status='c01'   and ( status=1 or accountPay =1) ";
		
		$ubonusOrderList =$mdl_order->getListBySql($sql);
		//var_dump($sql);exit;
		$sql ="select * from cc_order_import where logistic_delivery_date =$timestamp ";
	    $ubonusOrderImportList =$mdl_order->getListBySql($sql);
		
		
		
		foreach ($ubonusOrderList as $key => $value) {
		
		   $ubonusOrderList[$key]['data_source'] = '1'; //data from ubonus
		 }
	
		if($ubonusOrderImportList) {
			foreach ($ubonusOrderImportList as $key => $value) {
		
		   $ubonusOrderImportList[$key]['data_source'] = '2'; //data from ubonus
		 }
			$ubonusOrderList=array_merge($ubonusOrderList,$ubonusOrderImportList);
		}
		
		
		return $ubonusOrderList;
	}
	}

	public function getBusinessOrderOnDeliverDate($dateStr, $bid,$data_resource,$ref_seq_num) //$ref_seq_num 为 是否试用ubonus_seq 排序 ，还是试用 order_import 的 外部 seq_number_NO_string排序
		{
		$timestamp = strtotime($dateStr);

		if ($timestamp === false) {
			throw new Exception("dateStr is not recognized", 1);
		}
		$dateTime = new DateTime();
		$dateTime->setTimestamp($timestamp);
		$dateTime->setTime(0,0,0);
		$timestamp = $dateTime->getTimestamp();

		$mdl_order = loadModel('order');
		
		//判断传递过来的商家请求的是内部数据源还是外部数据源
		
		if(!$data_resource) $data_resource =1;
		
		if ($data_resource ==1 ) {
			$sql ="select  if(cc_order.edit_boxesNumber >0,cc_order.edit_boxesNumber,cc_order.boxesNumber) as boxes,

          f.nickname ,cc_order.* from cc_order left join cc_user_factory f on cc_order.userId =f.user_id and cc_order.business_userId = f.factory_id  where logistic_delivery_date =$timestamp and coupon_status='c01'  and ( status=1 or accountPay =1) ";
			$sql .=" and ( business_userId =$bid ";
			$sql .=" or  orderId in (select DISTINCT order_id from cc_wj_customer_coupon c where business_id = $bid)";
			$sql .=" or  business_userId in (select customer_id from cc_factory2c_list c where factroy_id = $bid)";
			$sql .=" or  business_userId in (select customer_id from cc_factory_2blist c where factroy_id = $bid)";
			$sql .=")";
	
			
		}elseif ($data_resource ==2 ){
			
			$mdl_order_import = loadModel('order_import');
			
			
			$sql ="select * from cc_order_import where logistic_delivery_date =$timestamp and coupon_status='c01' and  business_userId =$bid ";
			//var_dump($sql);exit;
        
		    // 加入排序  
			if($ref_seq_num !=1){ //表示需要外部seq_num
				
				$sql =$sql.' order by logistic_sequence_No_String ';
			}
			return $mdl_order_import->getListBySql($sql);
		}else{ //暂时放内部数据源
			
			
		}
		
		//	var_dump($sql);exit;
		return $mdl_order->getListBySql($sql);
	}

	public function getSupplierOrderOnDeliverDate($dateStr, $supplierId)
	{
		$timestamp = strtotime($dateStr);

		if ($timestamp === false) {
			throw new Exception("dateStr is not recognized", 1);
		}
		$dateTime = new DateTime();
		$dateTime->setTimestamp($timestamp);
		$dateTime->setTime(0,0,0);
		$timestamp = $dateTime->getTimestamp();

		$mdl_order = loadModel('order');

		$sql = "SELECT o.*, c.business_id FROM `cc_order` as o left JOIN cc_wj_customer_coupon as c on o.orderId = c.order_id  where o.business_userId = ".$this->dispCenterId." and c.business_id = $supplierId and o.logistic_delivery_date =$timestamp and o.coupon_status='c01' and (o.status=1 or o.accountPay=1) ";

		return $mdl_order->getListBySql($sql);
	}

	public function syncRoutesDownOnDeliverDate($dateStr,$factory_id)
	{	
		$mdl_order = loadModel('order');
        $mdl_picking = loadModel('picking');
		$mdl_order_import = loadModel('order_import');
        $mdl_schedule =loadModel('truck_driver_schedule');

		$routes = $this->api->getRoutes($dateStr);

      //  var_dump(json_encode($routes));exit;

		foreach ($routes->routes as $route) {
			//driverExternalId
			//driverSerial
			//vehicleLabel
			//vehicleRegistration
            $where1 =array(
                'factory_id'=>$factory_id,
                'delivery_date'=>strtotime($dateStr),
                'opti_driver_id'=>$route->driverExternalId
            );
         //   var_dump($where1);exit;
            $schedule_rec = $mdl_schedule->getByWhere($where1);
           if(!$schedule_rec){
                var_dump('please set optimoroute driver externalId for d101 ,d102 ... to map the driver.');exit;
            }else{
               // var_dump($schedule_rec);exit;
                //标记该调度为已调度状态；
               $whereupdate =array(
                   'factory_id'=>$factory_id,
                   'schedule_id'=>$schedule_rec['schedule_id']
               );
               $schedue_data=array(
                   'status'=>2
               );

               $mdl_schedule->updateByWhere($schedue_data,$whereupdate);

            }

            $trackNo = $schedule_rec['truck_id'];
            $driverCode = $schedule_rec['driver_id'];
            $schedule_id = $schedule_rec['schedule_id'];
			//$trackNo = $route->vehicleLabel;
			//$driverCode = $route->driverSerial;

			foreach ($route->stops as $stopNo => $stop) {
				$data = [
					'logistic_truck_No' => $trackNo,
					'logistic_stop_No' => intval($stopNo)+1,
					'logisitic_schedule_time' =>strtotime($stop->scheduledAtDt),
					'logistic_arrived_time' =>strtotime($stop->arrivalTimeDt),
					'logistic_driver_code' => $driverCode,
                    'logistic_schedule_id'=>$schedule_id
				];
				$where = [
					'orderId' => $stop->orderNo
				];
                // if check the first letter of order id is p , then update picking status

                if(substr($stop->orderNo,0,1)=='p'){
                    $mdl_picking->updateByWhere($data, $where);
                }else{
                    $mdl_order->updateByWhere($data, $where);
                }

				
				// add cc_order_import 的路程更新
				$where1 = [
					'orderId' => $stop->orderNo
				];
				//$mdl_order_import->updateByWhere($data, $where1);
				
			}
		}
	}

    public function getDriversRoutes($date = null, $options = [])
    {
        $routes =  $this->api->getRoutes($date, $options);

        $mdl_order = loadModel('order');
		$mdl_order_import = loadModel('order_import');

        $driversRoutes = [];
        foreach ($routes->routes as $route) {
            foreach ($route->stops as  $stopNo => $stop) {
                $driversRoute = [];
                $labels = ['order_id', 'vehicle','stop_no','address','phone','logistic_sequence_No','logistic_suppliers_info','logistic_suppliers_count','notes'];
                foreach ($labels as $label) {
                    $driversRoute[$label] = null;
                }
                $driversRoute['vehicle'] = $route->vehicleLabel;
                $driversRoute['order_id'] = "\t$stop->orderNo";
                $driversRoute['stop_no'] = intval($stopNo)+1;
                $driversRoute['address'] = $stop->address;

                $order = $mdl_order->getByOrderId($stop->orderNo);
                if($order) {
                    $driversRoute['phone'] = $order['phone'];
                    $driversRoute['logistic_sequence_No'] = $order['logistic_sequence_No'];
                    $driversRoute['logistic_suppliers_info'] = $order['logistic_suppliers_info'];
                    $driversRoute['logistic_suppliers_count'] = $order['logistic_suppliers_count'];
                    $driversRoute['notes'] = $order['message_to_business'];
                }else{
					$order = $mdl_order_import->getByOrderId($stop->orderNo);
					$driversRoute['phone'] = $order['phone'];
                    $driversRoute['logistic_sequence_No'] = $order['logistic_sequence_No'];
                    $driversRoute['logistic_suppliers_info'] = $order['logistic_suppliers_info'];
                    $driversRoute['logistic_suppliers_count'] = $order['logistic_suppliers_count'];
                    $driversRoute['notes'] = $order['message_to_business'];
				}

                array_push($driversRoutes, $driversRoute);
            }
        }

        return $driversRoutes;
    }
}
