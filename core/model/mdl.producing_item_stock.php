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
             if(!$rec['stock_qty']) {
                 $totalStockQuantity = 0;
             }else{
                 $totalStockQuantity = $rec['stock_qty'];
             }

             // 获取货架信息
             $shelfAndLayerStr =$rec['stock_shelf_info'];
             if($shelfAndLayerStr) {
                 $shelfAndLayerStrArr =json_decode($shelfAndLayerStr,true);

             }



             $sql ="SELECT area.id ,area.store_house_id,concat(room.code,'-',area.store_area) as area_name,area.note  , $totalStockQuantity as totalstk 
                FROM `cc_store_house_area` area left join cc_store_house room on area.store_house_id = room.id 
                where  area.id in ($areaStrWithCut)";
          //   var_dump($sql);exit;
             $areaList =  $this->getListBySql($sql);

             if($shelfAndLayerStr){
                 foreach ($areaList as $key=>$value) {
                     //$areaList[$key]['area_name']='hahah';
                     foreach ($shelfAndLayerStrArr as $key1=>$value1) {
                         if($value1[0]==$value['id']){
                             //找到当前的area 和shelf area 相同的话，那么构造 显示字符串
                             foreach ($value1[1] as $key2=>$value2){
                                 if($key2==0){
                                     $shelfandlevelStr =' (S-'.$value2[0].' L-'.$value2[1];
                                 }else{
                                     $shelfandlevelStr .=' , S-'.$value2[0].' L-'.$value2[1];
                                 }

                             }
                             $shelfandlevelStr.=')';
                             $areaList[$key]['area_name'].=$shelfandlevelStr;
                             break;

                         }
                     }
                 }

             }else{

             }
             return $areaList;
         }

        }


}

?>