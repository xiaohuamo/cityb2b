<?php

class GuigeItem{
	private $couponId;
	private $stripCode;
	private $parentStripCode;
	private $guigeHorizontal;
	private $guigeVertical;
	private $userId;

	public function __construct($id){
		if(!isset($id))throw new Exception("guigeItem Id is not set", 1);

		$this->couponId=$id;
		
		$this->resolveStripCode();

		if($this->guigeHorizontal==null && $this->guigeVertical == null){
			throw new Exception("Invalid strip Code for coupon".$this->couponId, 1);
			
		}
	}

	public function setGuigeHorizontal($guige){	
		$this->guigeHorizontal=$guige;
	}

	public function setGuigeVertical($guige){	
		$this->guigeVertical=$guige;
	}

	public function getGuigeHorizontal(){	
		return $this->guigeHorizontal;
	}

	public function getGuigeVertical(){	
		return $this->guigeVertical;
	}
	public function getParentStripCode(){
		return $this->parentStripCode;
	}

	public function getCouponId(){
		return $this->couponId;
	}

	public function getUserId(){
		return $this->userId;
	}

	public function resolveStripCode(){
		$userId = loadModel('coupons')->getCreateUserId($this->couponId);

		$this->stripCode =loadModel('coupons')->getStripCode($this->couponId);
		if(!isset($this->stripCode))throw new Exception("guigeItem stripCode is not set", 1);

		$parts = explode('#', $this->stripCode);

		$parentGuigeList =explode(',', $parts[0]);
		sort($parentGuigeList);

		$subGuigeList =explode(',', $parts[1]);
		sort($subGuigeList);

		$this->parentStripCode=join(',',$parentGuigeList);

		$this->stripCode=$this->parentStripCode . "#" . join(',',$subGuigeList);

		$horizontalGuigeFullList	=loadModel('shop_guige_details')->getFullGuigeListIdArray($userId,$parentGuigeList[0]);
		$verticalGuigeFullList		=loadModel('shop_guige_details')->getFullGuigeListIdArray($userId,$parentGuigeList[1]);

		$this->guigeHorizontal = array_intersect($subGuigeList, $horizontalGuigeFullList);
		$this->guigeVertical = array_intersect($subGuigeList, $verticalGuigeFullList);

		if(!$this->guigeVertical)$this->guigeVertical=array(0=>0);
		$this->userId=$userId;

	}

	public function mapDataIntoMasterMap(&$map){
		foreach ($this->getGuigeHorizontal() as $h) {
			foreach ($this->getGuigeVertical() as $v) {
				array_push($map[$h][$v], $this->getCouponId());
			}
		}
	}

}

class GuigeFusion{
	private $guigeItems;
	private $masterHorizontalGuige;
	private $masterVerticalGuige;
	private $masterStripCode;

	private $couponIdList;
	private $parentStripCodeList;

	private $masterGuigeMap;



	public function __construct(){
		$this->guigeItems=array();
	}

	public function addGuigeItem(guigeItem $guigeItem){
		array_push($this->guigeItems, $guigeItem);
	}

	public function startFusion(){
		
		$this->initData();

		$this->itemValidation();

		$this->processData();
		
	}

	public function getFusionResult(){
		return $this->masterGuigeMap;
	}
	public function getMasterStripCode(){
		return $this->masterStripCode;
	}
	public function getGuigeItems(){
		return $this->guigeItems;
	}
	public function fusionResultValidation(){
		$fusionSuccess=true;

		foreach ($this->masterGuigeMap as $hkey => $hvalue) {
			foreach ($hvalue as $vkey => $value) {
				if(sizeof($value)>1)$fusionSuccess=false;
			}
		}

		return $fusionSuccess;
	}

	public function mapDataVisualization(){
		
		// $html= "<style>
		// 	table {
		// 	    border-collapse: collapse;
		// 	    width:90vw;
		// 	    height:90vh;
		// 	}
		// 	table, th, td {
		// 	    border: 1px solid black;
		// 	    padding:10px;
		// 	    text-align:center;
		// 	}
		// </style>";

		$mdl_shop_guige_detail = loadModel('shop_guige_details');

		$html= "<table style='color:black;display:none;'>";
		$html.= "<thead>";
		$html.= "<th>/</th>";
		foreach (reset($this->masterGuigeMap) as $key => $value) {
			$html.= "<th>".$key."-".$mdl_shop_guige_detail->getGuigeName($key)."</th>";
		}
		$html.= "</thead>";

		$html.="<tbody>";
		foreach ($this->masterGuigeMap as $hkey => $hvalue) {
			$html.= '<tr>';
			$html.= "<td>".$hkey."-".$mdl_shop_guige_detail->getGuigeName($hkey)."</td>";
			foreach ($hvalue as $vkey => $value) {
				$html.= (sizeof($value)>1)?"<td style='background:#fd9e9e'>":"<td style='background:#5ce45c'>";
				$html.= join(',',$value)."</td>";
			}
			$html.= "</tr>";
		}
		$html.= "</tbody>";
		$html.= "</table>";

		return $html;
	}

	public function processData(){
		$this->masterStripCode = current($this->parentStripCodeList).'#'.join(',',array_merge($this->masterHorizontalGuige,$this->masterVerticalGuige));

		foreach ($this->guigeItems as $value) {
			$value->mapDataIntoMasterMap($this->masterGuigeMap);
		}
	}

	public function initData(){
		$couponIdList=array();
		$parentStripCodeList = array();
		$masterHorizontalGuigeList=array();
		$masterVerticalGuigeList=array();
  
		foreach ($this->guigeItems as $key => $value) {
			array_push($couponIdList, $value->getCouponId());
			array_push($parentStripCodeList, $value->getParentStripCode());
			$masterHorizontalGuigeList=array_merge($masterHorizontalGuigeList,$value->getGuigeHorizontal());
			$masterVerticalGuigeList=array_merge($masterVerticalGuigeList,$value->getGuigeVertical());
		}

		$this->couponIdList=$couponIdList;
		$this->parentStripCodeList=$parentStripCodeList;
		$this->masterHorizontalGuige=array_unique($masterHorizontalGuigeList);
		$this->masterVerticalGuige=array_unique($masterVerticalGuigeList);

		$map=array();
		foreach ($this->masterHorizontalGuige as $hg) {
			foreach ($this->masterVerticalGuige as $vg) {
				$map[$hg][$vg]=array();
			}
		}
		$this->masterGuigeMap=$map;
	}

	public function itemValidation(){
		//at least 2 item to start
		if(sizeof($this->guigeItems)<2)throw new Exception("Fusion need at least two item to start", 1);

		//same coupon Id is not allowed
		if (sizeof(array_unique($this->couponIdList))<sizeof($this->couponIdList)) throw new Exception("same coupon Id is not allowed", 1);

		//all coupons need to be under the same two parent guige
		if(sizeof(array_unique($this->parentStripCodeList)) >1) throw new Exception("all coupons need to be under the same two parent guige", 1);

	}

}


class mdl_guige_link extends mdl_base
{

	protected $tableName = '#@_shop_guige_link';

	public function save_guige_link(GuigeFusion $GuigeFusion){
		$data['link_id']=time();
		$data['stripcode']=$GuigeFusion->getMasterStripCode();

		foreach ($GuigeFusion->getGuigeItems() as $value) {
			$data['coupon_id']=$value->getCouponId();
			$data['user_id']=$value->getUserId();
			$this->insert($data);
		}
	}

	public function getLinkedCouponList($userId){
		$sql ="SELECT DISTINCT coupon_id FROM cc_shop_guige_link WHERE user_id =$userId";

		$data = $this->getListBySql($sql);

		$result = array();
		foreach ($data as $key => $value) {
			array_push($result, $value['coupon_id']);
		}
		return $result;
	}

	public function guigeLinkMasterStripCode($userId,$couponId){
		$where['user_id']=$userId;
		$where['coupon_id']=$couponId;
		$data=$this->getByWhere($where);
		if($data){
			return $data['stripcode'];
		}else{
			return false;
		}
		
	}
	public function getGuigeLink($couponId,$guige1Id,$guige2Id){
		
		$data =$this->getByWhere(array('coupon_id'=>$couponId));

		$couponList = $this->getList('coupon_id',array('link_id'=>$data['link_id']));

		$couponListArray=array();
		foreach ($couponList as $value) {
			array_push($couponListArray, $value['coupon_id']);
		}

		if(!$guige1Id)$guige1Id=-1;
		if(!$guige2Id)$guige2Id=-1;
		$where['guige1Id']=$guige1Id;
		$where['guige2Id']=$guige2Id;
		$where[]='couponId in ('.join(',',$couponListArray).')';
		
		$result= loadModel('shop_stock')->getByWhere($where);
		
		return $result['couponId'];
	}

	public function getGuigeLinkList($userId){
		$sql ="SELECT link_id, GROUP_CONCAT(coupon_id) as coupon_list FROM `cc_shop_guige_link` WHERE user_id = ".$userId." group By link_id";

		$data =$this->getListBySql($sql);

		return $data;
	}

	public function deleteGuigeLink($link_id){
		$where['link_id']=$link_id;
		$this->deleteByWhere($where);
	}

	/**
	 * 当一个产品的规格发生变化是，它所牵涉的所有规格链接将被删除 
	 * @param  [type] $coupon_id [description]
	 * @return [type]            [description]
	 */
	public function deleteGuigeLinkByCouponId($coupon_id)
	{
		$list=$this->getList(null,array('coupon_id'=>$coupon_id));

		foreach ($list as $value) {
			$this->deleteGuigeLink($value['link_id']);
		}
	}

}

?>