<?php 

class mdl_wj_show_seats extends mdl_base
{
  protected $tableName = '#@_wj_show_seats';

  private $show_id,$stadium_id,$stage_direction;

  private $column=['id','seat_id','show_id','stadium_id','area','row','seat_number','special','sold','reserved','ticket_category_id','price','cartlock'];

  function getShowData($show_id){
      $where['show_id']=$show_id;
      return $this->getList($this->column,$where);

  }

  function getStadiumData($stadium_id){
      $where['stadium_id']=$stadium_id;
      return $this->getList($this->column,$where);
  }

  function getSeatsDataAll($show_id,$stadium_id){
      $where['show_id']=$show_id;
      $where['stadium_id']=$stadium_id;
      return $this->getList($this->column,$where);
  }

  function getSeatsDataArea($show_id,$stadium_id,$area){
      $where['show_id']=$show_id;
      $where['stadium_id']=$stadium_id;
      $where['area']=$area;
      return $this->getList($this->column,$where);
  }

  function getSeatsDataRow($show_id,$stadium_id,$area,$row){
      $where['show_id']=$show_id;
      $where['stadium_id']=$stadium_id;
      $where['area']=$area;
      $where['row']=$row;
    
      return $this->getList($this->column,$where);
  }

  function getSeatsDataSeatsNumber($show_id,$stadium_id,$area,$row,$seatsNumber){
      $where['show_id']=$show_id;
      $where['stadium_id']=$stadium_id;
      $where['area']=$area;
      $where['row']=$row;
      $where['seat_number']=$seatsNumber;
      return $this->getList($this->column,$where);
  }

  function getUnavailableSeatsDataArea($show_id,$stadium_id,$area){
      $where['show_id']=$show_id;
      $where['stadium_id']=$stadium_id;
      $where['area']=$area;
  
      $where[]="(sold = 1 or reserved = 1)";
      return $this->getList($this->column,$where);
  }

  function getAreaList($show_id,$stadium_id){
      $sql = "select area from cc_wj_show_seats where show_id='".$show_id."' and stadium_id='".$stadium_id."' group by area";
      return $this->getListBySql($sql);
  }

  function getRowList($show_id,$stadium_id,$area){
  	// 这里再传递过来一个参数  stadium_direction  (top,bottom) 两种
  	// 这里把 show_id =1 那么 场馆方向在下面  墨尔本 ； show_id=2  那么场馆在上面  	
  	if($this->stage_direction==mdl_wj_show::STAGE_DIRECTION_BOTTOM){

      $sql = "select DISTINCT row from cc_wj_show_seats where show_id='".$show_id."' and stadium_id='".$stadium_id."' and area='".$area."' order by length(row) desc,row desc  ";
  	}else{
  		
  		if($area =='A' || $area =='B' || $area =='C' || $area =='D' || $area =='E' || $area =='F' ) {
  			$sql = "select  DISTINCT (row+0) as  row  from cc_wj_show_seats where row   REGEXP '^[0-9]+$' and show_id='".$show_id."' and stadium_id='".$stadium_id."' and area='".$area."' order by row  ";
  		}else{
  		
  		 	  $sql = "(select DISTINCT row from cc_wj_show_seats where row not  REGEXP '^[0-9]+$' and show_id='".$show_id."' and stadium_id='".$stadium_id."' and area='".$area."' order by length(row),row ) "  ;
  		}
  	//  $sql =$sql . " UNION ALL ( select  DISTINCT (row+0) as  row  from cc_wj_show_seats where row   REGEXP '^[0-9]+$' and show_id='".$show_id."' and stadium_id='".$stadium_id."' and area='".$area."' order by row )  ";
  	//	echo $sql ;
  	}
      return $this->getListBySql($sql);
  }

 
  
  
  function getSeatNumberList($show_id,$stadium_id,$area,$row){
  	
      $sql = "select seat_number from cc_wj_show_seats where show_id='".$show_id."' and stadium_id='".$stadium_id."' and area='".$area."' and row='".$row."' and special = 0 group by ABS(seat_number)";
  	
      return $this->getListBySql($sql);
  }

  function getSeatNumberListDESC($show_id,$stadium_id,$area,$row){
      $sql = "select seat_number from cc_wj_show_seats where show_id='".$show_id."' and stadium_id='".$stadium_id."' and area='".$area."' and row='".$row."' and special = 0 group by ABS(seat_number) DESC";
      return $this->getListBySql($sql);
  }

  function getSeatCategotyListByArea($show_id,$stadium_id,$area){
      $sql = "select ticket_category_id from cc_wj_show_seats where show_id='".$show_id."' and stadium_id='".$stadium_id."' and area='".$area."' group by ticket_category_id";
      return $this->getListBySql($sql);
  } 

  function getSeatCategotyList($show_id,$stadium_id){
      $sql = "select ticket_category_id, price from cc_wj_show_seats where show_id='".$show_id."' and stadium_id='".$stadium_id."' group by ticket_category_id";
      return $this->getListBySql($sql);
  } 
  function updateSeatToLock($show_id,$stadium_id,$area,$row,$seatsNumber){
      $now = time();
      $data=$this->getSeatsDataSeatsNumber($show_id,$stadium_id,$area,$row,$seatsNumber);

      if(sizeof($data)==0)return null;
      if($data[0]['reserved']=='1' || $data[0]['sold']=='1' )return null;
      if(($now-$data[0]['cartlock'])<1200)return null;//10mins lock time;
      
      $where['seat_id']=$data[0]['seat_id'];
      if($this->updateByWhere(['cartlock'=>$now],$where)){
        return $data[0]['seat_id'];
      }else{
        return null;
      }
  }

  function unlockSeat($seat_id){
      $where['id']=$seat_id;
      $this->updateByWhere(['cartlock'=>NULL],$where);
  }

  public function initShowAndStadium($show_id,$stadium_id){
      $this->show_id=$show_id;
      $this->stadium_id=$stadium_id;
      $this->stage_direction=loadModel('wj_show')->getDirection($show_id);
  }

  public function currentShow(){
      return $this->show_id;
  }

  public function currentStadium(){
      return $this->stadium_id;
  }

  public function seatsDataAll(){
      return $this->getSeatsDataAll($this->show_id,$this->stadium_id);
  }

  public function seatsDataArea($area){
      return $this->getSeatsDataArea($this->show_id,$this->stadium_id,$area);
  }


  public function areaHasSeatAvailable($area){
        $where['show_id']=$this->show_id;
        $where['stadium_id']=$this->stadium_id;
        $where['area']=$area;
        $where['sold']='0';
        $where['reserved']='0';

      $result= $this->getList($this->column,$where);

      return (sizeof($result)!=0);
  }


  public function seatsDataRow($area,$row){
      return $this->getSeatsDataRow($this->show_id,$this->stadium_id,$area,$row);
  }

  public function seatsDataSeatsNumber($area,$row,$seatsNumber){
      return $this->getSeatsDataSeatsNumber($this->show_id,$this->stadium_id,$area,$row,$seatsNumber);
  }

  public function areaList(){
      return dbArrayToSimpleArray($this->getAreaList($this->show_id,$this->stadium_id));
  }

  public function rowList($area){
      return dbArrayToSimpleArray($this->getRowList($this->show_id,$this->stadium_id,$area));
  }

  public function seatNumberList($area,$row){
      return dbArrayToSimpleArray($this->getSeatNumberList($this->show_id,$this->stadium_id,$area,$row));
  }
  public function seatNumberListDESC($area,$row){
      return dbArrayToSimpleArray($this->getSeatNumberListDESC($this->show_id,$this->stadium_id,$area,$row));
  }

  public function seatCategotyListByArea($area){
     return $this->getSeatCategotyListByArea($this->show_id,$this->stadium_id,$area);
  }

  public function seatCategotyList($show_id,$stadium_id){
    return $this->getSeatCategotyList($this->show_id,$this->stadium_id);
  }

  public function unavailableSeatsDataArea($area){
      return $this->getUnavailableSeatsDataArea($this->show_id,$this->stadium_id,$area);
  }

  public function getSoldRate($show_id)
  {
    $whereSeats['show_id'] = $show_id;
    $whereSeats['reserved'] = 0;
    $whereSeats['agent_id'] = 1;


    $whereSold=$whereSeats;
    $whereSold['sold']=1;

    $seatCount = $this->getCount($whereSeats);
    $soldCount = $this->getCount($whereSold);

    if($seatCount ==0 || $soldCount == 0 || $show_id == 0 )return 0;

    $rate = $soldCount/$seatCount;

    return $rate;
  }

}

?>