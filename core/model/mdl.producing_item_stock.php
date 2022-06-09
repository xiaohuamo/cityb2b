 <?php
 


class mdl_producing_item_stock extends mdl_base
{
	protected $tableName = '#@_producing_item_stock';

    public function getItemAreaInfo($item_id,$spec_id){

         // 获得当前记录

         $rec = $this->getByWhere(array('item_id'=>$item_id,'spec_id'=>$spec_id));
         if(!$rec || !$rec['store_area_ids']){
             return 0;
         }else{

             $areaStr =$rec['store_area_ids'];
             $areaStrWithCut =substr($areaStr,1,strlen($areaStr)-2);
             $totalStockQuantity = $rec['stock_qty'];

             $sql ="SELECT area.id ,area.store_house_id,concat(room.code,'-',area.store_area) as area_name,area.note  , $totalStockQuantity as totalstk 
                FROM `cc_store_house_area` area left join cc_store_house room on area.store_house_id = room.id 
                where  area.id in ($areaStrWithCut)";
          //   var_dump($sql);exit;
             $areaList =  $this->getListBySql($sql);
             return $areaList;
         }

        }


}

?>