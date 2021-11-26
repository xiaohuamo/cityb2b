 <?php
class mdl_freshfood_disp_suppliers_schedule extends mdl_base
{
	protected $tableName = '#@_freshfood_disp_suppliers_schedule';
}


class DispCenter {
	public static function getSupplierList($dispCenterId = null)
	{	
		$where = $dispCenterId ? ['business_id' => $dispCenterId] : []; 
		$data = loadModel('freshfood_disp_centre_suppliers')->getList([],$where);
		return array_column($data, 'suppliers_id');
	}
	
	
	public static function getSupplierListWithRefreshCode($dispCenterId = null)
	{	
		$sql ="select a.business_id, a.`suppliers_id`, b.business_store_refresh_code from cc_freshfood_disp_centre_suppliers a left join cc_user b on a.`suppliers_id` =b.id where a.business_id=$dispCenterId";
	//	var_dump($sql);exit;
		$data = loadModel('freshfood_disp_centre_suppliers')->getListBySql($sql);
		return $data;
	}
	
	public static function getSupplierListWithName($dispCenterId = null)
	{	
		$where = $dispCenterId ? ['business_id' => $dispCenterId] : []; 
		$data = loadModel('freshfood_disp_centre_suppliers')->getList([],$where);  
		return $data;
	}

	public static function getDispCenterList()
	{	
		$data = loadModel('freshfood_disp_centre_suppliers')->getList();
		return array_unique(array_column($data, 'business_id'));
	}

	public static function getDispCenterIdOfSupplier($suppliersId)
	{
		$where = ['suppliers_id' => $suppliersId];
		$data = loadModel('freshfood_disp_centre_suppliers')->getByWhere($where);
		return $data['business_id'];
	}

	public static function getPostcode($businessid)
	{
		$where = [];
		$where[] = "business_userId=".$businessid;
		$where[] = "is_avaliable='avaliable'";
		$list = loadModel('local_delivery_postcodes')->getList([], $where);
		return array_column($list, 'postcode');
	}

	public static function isSingleSupplierDispCenter($suppliersId)
	{	
		$businessid = static::getDispCenterIdOfSupplier($suppliersId);
		$where = ['business_id' =>$businessid];
		$list = loadModel('freshfood_disp_centre_suppliers')->getList([],$where);
		return count($list) <= 1;
	}

	public static function getAvailableDeliverDateOfBusiness($businessList)
	{	
		$where = [];

		if (count($businessList)>0) {
			$where[] = 'business_id IN ('.join($businessList, ',').')' ;
		}
		$where[] = 'enable = 1';
		$column = ['delivery_date_of_week'];

		$dateList = loadModel('freshfood_disp_suppliers_schedule')->getList($column, $where);
        
		return array_unique(array_column($dateList, 'delivery_date_of_week'));
	}

	public static function getAvailableBusinessForDeliverDate($timestamp, $period = null, $centre_business_id = null, $lang = '简体中文') {
		$days = array('SUN', 'MON', 'TUE', 'WED','THUR','FRI', 'SAT');
		$periodOptions = ['morning','afternoon','anytime'];

		$selectedDay = $days[date('w', $timestamp)];

		$column = ['business_id', 'business_name', 'business_name_en', 'order_start_of_date', 'order_cut_of_date', 'order_start_of_time', 'order_cut_of_time'];
		$where = [];
		$where[] = 'delivery_date_of_week="'.$selectedDay.'"';

		if ($period && in_array(strtolower($period), $periodOptions)) {
			$where[] = 'delivery_'.strtolower($period).'=1';
		}

		if ($centre_business_id) {
			$where[] = 'centre_business_id="'.$centre_business_id.'"';
		}

		$businessList = loadModel('freshfood_disp_suppliers_schedule')->getList($column, $where);

		$result = [];
		foreach ($businessList as $business) {
            $businessNameCn = $business['business_name'];
            $businessNameEn = $business['business_name_en'];
		    if($lang == 'English') {
                $businessName = $businessNameEn;
            } else if($lang == '简体中文'){
                $businessName = $businessNameCn;
            }

		    //英文名可能未设置
		    if(!$businessName) {
                $businessName = $businessNameCn;
            }

		    if(self::checkCurrentTimeAvailable($business['order_start_of_date'], $business['order_cut_of_date'], $business['order_start_of_time'], $business['order_cut_of_time'])) {
                $result[$business['business_id']]= $businessName;
            }
        }
		return $result;
	}

	//确认当前时间是否在下单时间内
    private static function checkCurrentTimeAvailable($startDay, $endDay, $startTime = '00:00', $endTime = '00:00') {
        $weekDays =['MON', 'TUE', 'WED','THUR','FRI', 'SAT', 'SUN'];
        $startDay = array_search($startDay, $weekDays) + 1;
        $endDay = array_search($endDay, $weekDays) + 1;

        $startDayTimeStamp = self::getWeekDayTimeStamp($startDay, $startTime);
        $endDayTimeStamp = self::getWeekDayTimeStamp($endDay, $endTime);
        $currentTimeStamp = time();

        if($startDayTimeStamp < $endDayTimeStamp) {//周一开始，周五结束
            return $currentTimeStamp >= $startDayTimeStamp && $currentTimeStamp <= $endDayTimeStamp;
        } else if($startDayTimeStamp > $endDayTimeStamp) {//周五开始，周一结束
            return !($currentTimeStamp >= $endDayTimeStamp && $currentTimeStamp <= $startDayTimeStamp);
        }

    }

    //获取当前周的任意时间并转为时间戳, 周一为1，周日为7
    private static function getWeekDayTimeStamp($weekDay, $time = '00:00') {
        $weekDayTimeStamp = (time() - ((date('w') == 0 ? 7 : date('w')) - $weekDay) * 24 * 3600);
        $weekDayString = date('Y-m-d', $weekDayTimeStamp) . $time . ':00'; // 2020-10-10 10:00:00
        return strtotime($weekDayString);//1603630800
    }

	//获得接下来8个递送的日子
	public static function getNextNAvailableDeliverDate($businessList)
	{
		$n = 8;
		$days = array('SUN', 'MON', 'TUE', 'WED','THUR','FRI', 'SAT');
		
		$availableDays = array_intersect(
			$days, 
			STATIC::getAvailableDeliverDateOfBusiness($businessList)
		);

		$dayList = [];
		$now = time();
		for ($i=0; $i < $n * 7 ; $i++) { 
			$d = strtotime("+$i day", $now);
			if (in_array($days[date('w', $d)], $availableDays)){
				$dayList[] = $d;
			}
		}
		return array_slice($dayList, 0, $n);
	}


	public static function getBusinessDispSchedule($businessId)
	{	
		$n = 8;

		$where = [];
		$where[] = 'business_id = '. $businessId;
		$where[] = 'centre_business_id = '. static::getDispCenterIdOfSupplier($businessId);
		$businessList = loadModel('freshfood_disp_suppliers_schedule')->getList([], $where);
		//var_dump($businessList);exit;
		$dispScheduleList = [];
		$referenceTimepoint = strtotime("-1 week");
		$loopcount = 0;

		if (count($businessList) > 0) {
			while (count($dispScheduleList) < $n) {
				foreach ($businessList as $businessData) {
					$ds = new DispSchedule();
					$ds->loadData($businessData);
					$ds->instancing($referenceTimepoint);
					$dispScheduleList[] = $ds;
				}
				$loopcount++;
				$referenceTimepoint = strtotime("+$loopcount week", $referenceTimepoint);
			}
		}
//var_dump($dispScheduleList);exit;
		uasort($dispScheduleList, function($a,$b){
			return $a->orderDeliveryTimestamp - $b->orderDeliveryTimestamp;
		});
//var_dump($dispScheduleList);exit;
		$dispScheduleList = array_filter($dispScheduleList, function($ds){
			return (time() < $ds->orderEndTimestamp && time() > $ds->orderStartTimestamp);
		});
		return array_slice($dispScheduleList, 0, $n);
	}

	public static function getFollowingNDaysIncludeAvailableDeliver($dispScheduleList)
	{
		
		$n = 8;

		$current = time();
       // var_dump($current);exit;
		$nextNDays = [];
		for ($i=0; $i < $n ; $i++) { 
			$ts = strtotime("+$i day",$current);
			$dt = new DateTime();
			$dt->setTimestamp($ts);
			$dt->setTime(0,0,0);
			$timestamp = $dt->getTimestamp();

			$availableDay = array_filter($dispScheduleList, function($ds) use ($timestamp){
				return $ds->orderDeliveryTimestamp == $timestamp && !STATIC::isOverMaxDailyOrderLimit($timestamp);
			});
			//var_dump($availableDay);exit;

			if ($availableDay) {
				$nextNDays[] = array_pop($availableDay);
			} else {
				//fake a obj
				$ds = new DispSchedule();
				$ds->orderDeliveryTimestamp = $timestamp;
				$ds->isAvaliable = false;
				$nextNDays[] = $ds;
			}
		}

		return $nextNDays;

	}

	public static function isDeliverDateStillValid($deliveryDateTimestamp, $businessId)
	{
		$currentAvailableDateList = STATIC::getBusinessDispSchedule($businessId);

		$match = array_filter($currentAvailableDateList, function($ds) use ($deliveryDateTimestamp){
			return $ds->orderDeliveryTimestamp ==$deliveryDateTimestamp;
		});

		return count($match) == 1;
	}

	public static function isOverMaxDailyOrderLimit($deliveryDateTimestamp)
	{
		$dailyOrderLimit = 120;

		if ($deliveryDateTimestamp) {
			return loadModel('order')->getCount([
				'coupon_status' => 'c01',
				'logistic_delivery_date' => $deliveryDateTimestamp
			]) >= $dailyOrderLimit;
		}
	}

	public static function getBusinessDispScheduleData($businessId) {
		return array_map(function($dispSchedule){
			return $dispSchedule->toArray();
		},STATIC::getBusinessDispSchedule($businessId));
	}
}


class DispSchedule 
{   
	private $delivery_date_of_week;
	private $order_start_of_date;
	private $order_start_of_time;
	private $order_cut_of_date;
	private $order_cut_of_time;
	public $delivery_morning;
	public $delivery_afternoon;
	public $delivery_anytime;

	public $orderStartTimestamp;
	public $orderEndTimestamp;
	public $orderDeliveryTimestamp;

	public $orderStart;
	public $orderEnd;
	public $orderDelivery;

	public $isAvaliable;
	function __construct($data = null)
	{	
		$this->isAvaliable = true;
	}

	function loadData($data){
		$this->delivery_date_of_week = $data['delivery_date_of_week']; 
		$this->order_start_of_date = $data['order_start_of_date']; 
		$this->order_start_of_time = $data['order_start_of_time']; 
		$this->order_cut_of_date = $data['order_cut_of_date']; 
		$this->order_cut_of_time = $data['order_cut_of_time']; 
		$this->delivery_morning = $data['delivery_morning']; 
		$this->delivery_afternoon = $data['delivery_afternoon']; 
		$this->delivery_anytime = $data['delivery_anytime']; 
	}

	// will instancing based on referenceTimepoint's week
	public function instancing ($referenceTimepoint = null)
	{	
	
		$delivery_date_of_week = $this->delivery_date_of_week;
		$order_start_of_date = $this->order_start_of_date;
		$order_start_of_time = $this->order_start_of_time;
		$order_cut_of_date = $this->order_cut_of_date;
		$order_cut_of_time = $this->order_cut_of_time;
		$delivery_morning = $this->delivery_morning;
		$delivery_afternoon = $this->delivery_afternoon;
		$delivery_anytime = $this->delivery_anytime;

		$days = array('SUN', 'MON', 'TUE', 'WED','THUR','FRI', 'SAT');

		$currentDayOfWeekIndex = date('w',$referenceTimepoint);

		$order_start_of_date_index = array_search($order_start_of_date, $days);


		if ($order_start_of_date_index < $currentDayOfWeekIndex) {
			$diff = $order_start_of_date_index + 7 - $currentDayOfWeekIndex;
		} 

		if ($order_start_of_date_index > $currentDayOfWeekIndex) {
			$diff = $order_start_of_date_index - $currentDayOfWeekIndex;
		} 

		if ($order_start_of_date_index == $currentDayOfWeekIndex) {
			$diff = 0;
		} 

		$orderStart = new DateTime();
		$orderStart->setTimestamp(strtotime("+$diff day",$referenceTimepoint));

		list($hour, $minute) = $this->getHourAndMinuteFromTimeStr($order_start_of_time);
		$orderStart->setTime($hour, $minute, 0);
		$orderStartTimestamp = $orderStart->getTimestamp();



		$order_cut_of_date_index = array_search($order_cut_of_date, $days);

		if (order_cut_of_date_index < $order_start_of_date_index) {
			$diff = $order_cut_of_date_index + 7 - $order_start_of_date_index;
		} 

		if ($order_cut_of_date_index > $order_start_of_date_index) {
			$diff = $order_cut_of_date_index - $order_start_of_date_index;
		}
		
		if ($order_cut_of_date_index == $order_start_of_date_index) {
			$diff = 0;
		} 

		$orderEnd = new DateTime();
		$orderEnd->setTimestamp(strtotime("+$diff day", $orderStartTimestamp));

		list($hour, $minute) = $this->getHourAndMinuteFromTimeStr($order_cut_of_time);
		$orderEnd->setTime($hour, $minute, 0);
		$orderEndTimestamp = $orderEnd->getTimestamp();



		$delivery_date_of_week_index = array_search($delivery_date_of_week, $days);

		if (delivery_date_of_week_index < $order_cut_of_date_index) {
			$diff = $delivery_date_of_week_index + 7 - $order_cut_of_date_index;
		} 

		if ($delivery_date_of_week_index > $order_cut_of_date_index) {
			$diff = $delivery_date_of_week_index - $order_cut_of_date_index ;
		} 
		
		if ($delivery_date_of_week_index == $order_cut_of_date_index) {
			$diff =0;
		}  

		$orderDelivery = new DateTime();
		$orderDelivery->setTimestamp(strtotime("+$diff day", $orderEndTimestamp));

		$orderDelivery->setTime(0, 0, 0);

		$orderDeliveryTimestamp = $orderDelivery->getTimestamp();


		$this->orderStartTimestamp = $orderStartTimestamp;
		$this->orderEndTimestamp = $orderEndTimestamp;
		$this->orderDeliveryTimestamp = $orderDeliveryTimestamp;

		$this->orderStart = date('r', $orderStartTimestamp);
		$this->orderEnd = date('r', $orderEndTimestamp);
		$this->orderDelivery= date('r', $orderDeliveryTimestamp);
	}

	public function getOrderDeliveryDisplay($lang = '简体中文')
	{
	    if($lang == '简体中文') {
            $days=['周日','周一','周二','周三','周四','周五','周六'];
            $optional = '可选';
            $notOptional = '不可选';
        } else if($lang == 'English') {
            $days=['SUN','MON','TUE','WED','THU','FRI','SAT'];
            $optional = 'Optional';
            $notOptional = 'Not Optional';
        }
		$html = $days[date('w',$this->orderDeliveryTimestamp)]. "<em>". date('d/M',$this->orderDeliveryTimestamp) ."</em>";

		if ($this->isAvaliable) {
			$html .= "<i>$optional</i>";
		} else {
			$html .= "<i>$notOptional</i>";
		}
		return $html;
	}

    public function getOrderDeliveryWeekDisplay($lang = '简体中文')
    {
        if($lang == '简体中文') {
            $days=['周日','周一','周二','周三','周四','周五','周六'];
        } else if($lang == 'English') {
            $days=['SUN','MON','TUE','WED','THU','FRI','SAT'];
        }
        return $days[date('w',$this->orderDeliveryTimestamp)];
    }

    public function getOrderDeliveryOptionalDisplay($lang = '简体中文')
    {
        if($lang == '简体中文') {
            $optional = '可选';
            $notOptional = '不可选';
        } else if($lang == 'English') {
            $optional = 'Optional';
            $notOptional = 'Not Optional';
        }

        $html = '';
        if ($this->isAvaliable) {
            $html .= "<i>$optional</i>";
        } else {
            $html .= "<i>$notOptional</i>";
        }

        return $html;
    }

    public function getOrderDeliveryDateDisplay()
    {
        return date('d/M',$this->orderDeliveryTimestamp);
    }

	/**
	 * 24小时制 实现 没有必要有结尾的 am 或 pm, 但因为数据库中有这里需要处理
	 * @param $timeStr can be of format 12:30AM or 12:30
	 */
	public function getHourAndMinuteFromTimeStr($timeStr)
	{
		$suffix = substr($timeStr, -2, 2);

		if (in_array(strtolower($suffix), ["am", "pm"]) ) {
			$parts = explode(':', substr($timeStr, 0, -2));
		} else {
			$parts = explode(':', $timeStr);
		}
		return [(int)$parts[0], (int)$parts[1]];
	}

	public function toArray()
	{
		return [
			'delivery_date_of_week' => $this->delivery_date_of_week,
			'order_start_of_date' => $this->order_start_of_date,
			'order_start_of_time' => $this->order_start_of_time,
			'order_cut_of_date' => $this->order_cut_of_date,
			'order_cut_of_time' => $this->order_cut_of_time,
			'delivery_morning' => $this->delivery_morning,
			'delivery_afternoon' => $this->delivery_afternoon,
			'delivery_anytime' => $this->delivery_anytime,
			'orderStartTimestamp' => $this->orderStartTimestamp,
			'orderEndTimestamp' => $this->orderEndTimestamp,
			'orderDeliveryTimestamp' => $this->orderDeliveryTimestamp,
			'orderStart' => $this->orderStart,
			'orderEnd' => $this->orderEnd,
			'orderDelivery' => $this->orderDelivery,
		];
	}
}

?>