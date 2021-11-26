<?php
class CustomizableCategoryItem{
	private $id;
	private $path;
	private $level;
	private $name;
	private $name_en;
	private $order;
	private $createUserId;
	private $parentId;

	const DBK_ID='id';
	const DBK_PATH='path';
	const DBK_LEVEL='level';
	const DBK_NAME='name';
	const DBK_NAME_EN='name_en';
	const DBK_ORDER='order_num';
	const DBK_CREATEUSERID='create_user_id';
	const DBK_PARENTID='parent_id';

	function __construct($data=null){
		if($data){
			$this->path=$data[self::DBK_PATH];
			$this->id  =$data[self::DBK_ID];
			$this->level  =$data[self::DBK_LEVEL];
			$this->name  =$data[self::DBK_NAME];
			$this->name_en  =$data[self::DBK_NAME_EN];
			$this->order  =$data[self::DBK_ORDER];
			$this->createUserId  =$data[self::DBK_CREATEUSERID];
			$this->parentId  =$data[self::DBK_PARENTID];
		}
	}
	function getId(){
		return $this->id;
	}	
	function getPath(){
		return $this->path;
	}
	function getLevel(){
		return $this->level;
	}
	function setParentId($pid){
		$this->parentId=$pid;
	}
	function setCreateUserId($id){
		$this->createUserId=$id;
	}
	function setName($name){
		$this->name=$name;
	}
	function setName_en($name_en){
		$this->name_en=$name_en;
	}
	function setOrder($order){
		$this->order=$order;
	}
	function setPath($path){
		$this->path=$path;
		
		$itemPath = new CustomizableCategoryItemPath($path);

		$this->level=$itemPath->level();
	}
	function toDBArray(){
		$data[self::DBK_PATH]=$this->path;
		$data[self::DBK_LEVEL]=$this->level;
		$data[self::DBK_NAME]=$this->name;
		$data[self::DBK_NAME_EN]=$this->name_en;
		$data[self::DBK_ORDER]=$this->order;
		$data[self::DBK_CREATEUSERID]=$this->createUserId;
		$data[self::DBK_PARENTID]=$this->parentId;
		return $data;
	}
}

class CustomizableCategoryItemPath{

	private $path;

	private $pathSectionsArray;

	const DELIMITER ='.';

	function __construct($path){

		$this->path=$path;
		
		if($path){
			$this->pathSectionsArray=explode(self::DELIMITER, $path);
		}else{
			$this->pathSectionsArray=array();
		}
	}

	function nextPathString(){

		$array = $this->pathSectionsArray;

		$length = sizeof($array);

		$lastSec= intval(end($array));

		$lastSec +=1;

		$array[$length-1]=$lastSec;

		return join(self::DELIMITER,$array);

	}

	function firstChildPath(){
		$array = $this->pathSectionsArray;

		array_push($array, '1');

		return join(self::DELIMITER,$array);
	}
	function level(){
		return sizeof($this->pathSectionsArray);
	}
}


class mdl_customizableCategory extends mdl_base
{
	protected $tableName = '#@_customizablecategory';

	private $createUserId;

	function setUserId($id){
		$this->createUserId=$id;
	}

	function deleteNode($id){
		$cItem = new CustomizableCategoryItem($this->get($id));

		$path =$cItem->getPath();

		$where[]=CustomizableCategoryItem::DBK_PATH." like '".$path.".%'";

		$where[CustomizableCategoryItem::DBK_CREATEUSERID]=$this->createUserId;

		$this->deleteByWhere($where);

		$this->delete($id);
	}

	function addChild($childItem,$parentItemId=null){

		if(!is_a($childItem,'CustomizableCategoryItem'))throw new Exception("childItem must be of CustomizableCategoryItem", 1)
		;

		$cpath = $this->nextChildPath($parentItemId);

		$childItem->setParentId($parentItemId);
		
		$childItem->setPath($cpath);

		$childItem->setCreateUserId($this->createUserId);

		$id = $this->insert($childItem->toDBArray());

		return $id;
	}

	function nextChildPath($parentItemId){

		$childList =($parentItemId!=null)?$this->getChildList($parentItemId):$this->getTopLevelItemList();

		if($childList){
			$lastChild =new CustomizableCategoryItem(end($childList));

			$lastChildPath = new CustomizableCategoryItemPath($lastChild->getPath());

			return $lastChildPath->nextPathString();
		}else{

			$cItem = new CustomizableCategoryItem($this->get($parentItemId));

			$cItemPath = new CustomizableCategoryItemPath($cItem->getPath());

			return $cItemPath->firstChildPath();
		}	

	}
	function getTopLevelItemList(){
		//$sql = "select * from ".$this->tableName." where level = 1";

		$where[CustomizableCategoryItem::DBK_LEVEL]=1;

		$where[CustomizableCategoryItem::DBK_CREATEUSERID]=$this->createUserId;

		$order=CustomizableCategoryItem::DBK_ORDER;

		$result=$this->getList(null,$where,$order);

		return $result;
	}

	function getChildList($parentItemId,$includeAllChild=false){
		$cItem = new CustomizableCategoryItem($this->get($parentItemId));

		$path =$cItem->getPath();

		$level =$cItem->getlevel();

		//$sql = "select * from ".$this->tableName." where ".CustomizableCategoryItem::DBK_PATH." like '".$path."%' and level = ".($level+1);
		if($includeAllChild==false){
			$where[CustomizableCategoryItem::DBK_LEVEL]=$level+1;
		}

		$where[]=CustomizableCategoryItem::DBK_PATH." like '".$path.".%'";
		//$where[] = "parent_id != 0";
		
		$where[CustomizableCategoryItem::DBK_CREATEUSERID]=$this->createUserId;

		$order=CustomizableCategoryItem::DBK_ORDER;

		$result=$this->getList(null,$where,$order);

		return $result;
	}

	function getChildIdList($parentItemId){
		$cItemList = $this->getChildList($parentItemId,true);

		$ids=array();
		foreach ($cItemList as $key => $value) {
			array_push($ids, $value['id']);
		}
		return $ids;
	}

	function hasChild($parentItemId){
		$childList = $this->getChildList($parentItemId);

		$numOfChild = sizeof($childList);
		if($numOfChild==0){
			return false;
		}else{
			return $numOfChild;
		}
	}

	function getCategoryTree($rootId=0,$levelDepth=2){
		if($rootId>0){
			$data = $this->getChildList($rootId);
			$currentLevel=$data[0]['level'];
		}else{
			$data = $this->getTopLevelItemList();
			$currentLevel=1;
		}
		$levelUntil = $currentLevel + $levelDepth - 1;

		$this->getAllChildNextLevel($data,$levelUntil);

		return $data;
	}

	function getAllChildNextLevel(&$data,$levelUntil){
		//recursive function 
		foreach ($data as $key => $value) {
			if($value['level']<$levelUntil){
				$data[$key]['child']=$this->getChildList($value['id']);
				$this->getAllChildNextLevel($data[$key]['child'],$levelUntil);
			}
		}
	}
}

?>