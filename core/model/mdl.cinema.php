<?php

class Location{
	private $createUserId;
	private $location;
	private $roomName;
	private $roomType;
	private $totalSeats;

	function __construct($data){
		if($data){
			$this->createUserId = $data['user_id'];
			$this->location = $data['location_name'];
			$this->roomName = $data['room_name'];
			$this->roomType = $data['room_type'];
			$this->totalSeats = $data['total_seat'];
		}
	}

	public function setUser($id){
		$this->createUserId=$id;
	}
	public function setLocation($location){
		$this->location=$location;
	}
	public function setRoomName($name){
		$this->roomName=$name;
	}
	public function setRoomType($type){
		$this->roomType=$type;
	}
	public function setTotalSeats($total){
		$this->totalSeats=$total;
	}

	function toDBArray(){
		$data['user_id']=$this->createUserId ;
		$data['location_name']=$this->location;
		$data['room_name']=$this->roomName ;
		$data['room_type']=$this->roomType;
		$data['total_seat']=$this->totalSeats;
		return $data;
	}
}

class Movie{
	private $couponId;
	private $createUserId;
	private $location;
	private $date;
	private $time;
	private $timeStamp;
	private $priceOption; //#array

	function __construct($data){
		if($data){
			$this->couponId = $data['coupon_id'];
			$this->createUserId = $data['create_user_id'];
			$this->location = $data['location_id'];
			$this->date = $data['date'];
			$this->time = $data['time'];
			$this->timeStamp=$data['timeStamp'];
			$this->priceOption = $data['priceOption'];
		}
	}
	public function setCouponId($couponId){
		$this->couponId=$couponId;
	}
	public function setCreateUserId($userId){
		$this->createUserId=$userId;
	}
	public function setLocation($locationId){
		$this->location=$locationId;
	}
	public function setDate($date){
		$this->date=$date;
	}
	public function setTime($time){
		$this->time=$time;
	}
	public function setTimeStamp($timeStamp){
		$this->timeStamp=$timeStamp;
	}
	public function setPriceGroup($priceGroup){
		$this->priceOption=$priceGroup;
	}

	function toDBArray(){
		$data['coupon_id']=$this->couponId;
		$data['create_user_id']=$this->createUserId;
		$data['location_id']=$this->location;
		$data['date']=$this->date;
		$data['time']=$this->time;
		$data['timeStamp']=$this->timeStamp;
		$data['priceOption']=$this->priceOption;
		return $data;
	}
}

class PriceGroup{
	private $userId;
	private $labelName;
	private $priceGroup;

	function __construct(){
		$this->priceGroup=array();
	}
	function toDBArray(){
		$data['user_id']=$this->userId;
		$data['label_name']=$this->labelName;
		$data['price_group']=$this->getPriceGroup();
		return $data;
	}
	public function setUser($id){
		$this->userId=$id;
	}
	public function setLableName($name){
		$this->labelName=$name;
	}
	public function addPrice($name,$value){
		$this->priceGroup[$name]=$value;
	}
	public function getPriceGroup(){
		return serialize($this->priceGroup);
	}

}
class mdl_cinema extends mdl_base{

	protected $tableName='#@_cinema';

	protected $locationTable ='#@_cinema_location';
	protected $priceGroupTable ='#@_cinema_pricegroup';

	public function addNewPriceGroup($priceGroup){
		 return $this->db->insert( $priceGroup->toDBArray(), $this->priceGroupTable );
	}
	public function getPriceGroupList($userId){
		$sql = "select id,label_name,price_group from ". $this->priceGroupTable ." where user_id=" .$userId;
		return $this->getListBySql($sql);
	}

	public function getPriceGroupData($id){
		$sql = "select price_group from ". $this->priceGroupTable ." where id=" .$id;
		$result= $this->getListBySql($sql);
		if(sizeof($result)==0){
			return null;
		}else{
			return $result[0]['price_group'];
		}
	}

	public function deletePriceGroup($id){
		return $this->db->delete( $this->priceGroupTable, "id='$id'" );
	}	

	public function addNewLocation($location){
		return $this->db->insert( $location->toDBArray(), $this->locationTable );
	}

	public function getLocationList($userId){
		$sql = "Select distinct location_name from " . $this->locationTable. " where user_id="  .$userId;
		return $this->getListBySql($sql);
	}

	public function getLocationData($userId){
		$data=[];
		$locationList = $this->getLocationList($userId);
		foreach ($locationList as $key => $value) {
			$roomList = $this->getRoomList($userId,$value['location_name']);
			$data[$value['location_name']]=$roomList;
		}
		return $data;
	}

	public function getRoomList($userId,$locationName){
		$sql = "Select * from "  .$this->locationTable. " where user_id=".$userId ." and location_name= '".$locationName."'";
		return $this->getListBySql($sql);
	}

	public function deleteLocation($id){
		return $this->db->delete( $this->locationTable, "id='$id'" );
	}

	public function addNewMovie($movie){
		return $this->insert($movie->toDBArray());
	}

	public function getMovieList($couponId){
		$sql = "select ci.id, ci.date,ci.time,ci.priceOption,cl.location_name,cl.room_type from cc_cinema as ci left join cc_cinema_location as cl on ci.location_id = cl.id where ci.coupon_id=".$couponId." and ci.timeStamp >".time()." group by ci.date, ci.time";
		return $this->getListBySql($sql);
		//return structured array of movies.    BoxHill->room3->sunday->5:00pm->
	}
	public function processDateForDisplay($data){
		foreach ($data as $key => $value) {
			$dataFormat = 'Y-m-d';
			$dateString = $value['date'];
			
			$newDateTime=DateTime::createFromFormat($dataFormat, $dateString);
			$newDateTime->setTime( 0, 0, 0 );

			$today = new DateTime(); // This object represents current date/time
			$today->setTime( 0, 0, 0 );

			$diff=$today->diff($newDateTime);
			$diffDays = (integer)$diff->format( "%R%a" );
			if($diffDays==0){
				//$data[$key]['date']='Today '.$newDateTime->format("jS F Y");
				$data[$key]['date']=$newDateTime->format("l jS F Y");
			}elseif($diffDays==1){
				//$data[$key]['date']='Tomorrow '.$newDateTime->format("j F Y");
				$data[$key]['date']=$newDateTime->format("l jS F Y");
			}elseif($diffDays<0){
				//before Yesterday not show
				unset($data[$key]);
				continue;
			}else{
				$data[$key]['date']=$newDateTime->format("l jS F Y");
			}
		}
		return $data;
	}

	public function getMovieListByUser($userId){
		$sql = "select ci.id,ci.date,ci.time,ci.priceOption, co.title ,cl.location_name,cl.room_name from cc_cinema  as ci left join cc_coupons as co on ci.coupon_id = co.id left join cc_cinema_location as cl on ci.location_id=cl.id where ci.create_user_id=".$userId;
		return $this->getListBySql($sql);
	}

	public function getMovieListByLocation($couponId,$locationName){
		$sql="select * from cc_cinema as c left join cc_cinema_location as l on c.location_id = l.id where c.coupon_id = ".$couponId. " and l.location_name = " .$locationName;
		return $this->getListBySql($sql);
	}

	public function getMovieListByLocationAndDate(){

	}

	public function deleteMovie($id){
		return $this->delete($id);
	}

}

?>