<?php

class mdl_stock_details extends mdl_base
{

	protected $tableName = '#@_stock_details';


    public function getItemStockHistroy($item_id,$spec_id){
        $sql ="select d.id, st.name,from_unixtime(d.gen_date,'%Y-%m-%d') as stock_date,d.store_area_ids,concat(user.name,'-',user.person_first_name) as operator_name ,d.quantity,from_unixtime(d.expire_date,'%Y-%m-%d') as expire_date,d.note 
            from cc_stock_details d  
                left join cc_user user on d.operator_user_id =user.id 
                    left join cc_stock_type st on d.type=st.type where d.item_id =$item_id and spec_id =$spec_id  order by d.id desc limit 40";
        $list =$this->getListBySql($sql);
        $mdl = loadModel('store_house_area');
       // var_dump($list);exit;
        foreach ($list as $key=>$value) {
            $areaList = $value['store_area_ids'];
            $areaListArray = explode(',',$areaList);
            $areDesc ='';
            foreach ($areaListArray as $key1=>$val1){

                if($val1){
                    $sql1 ="SELECT area.id ,area.store_house_id,concat(room.code,'-',area.store_area) as area_name 
                FROM `cc_store_house_area` area left join cc_store_house room on area.store_house_id = room.id 
                where  area.id =$val1";
                   // var_dump($sql1);exit;
                    $rec = $mdl->getListBySql($sql1);
                    if(!$areDesc){
                        $areDesc .=$rec[0]['area_name'];
                    }else{
                        $areDesc .=' , '.$rec[0]['area_name'];
                    }


                }
             }
            $list[$key]['area_name']=$areDesc;

        }
      return $list;

    }

    public  function refreshStock($type,$factory_id,$item_id,$spec_id,$operator_id,$quantity,$store_area_id,$note,$expiration_date,$refId){

        $this->begin();


        // insert data to stock details log ..

        $data=array(
            'factory_id'=>$factory_id,
            'type'=>$type,
            'item_id'=>$item_id,
            'spec_id'=>$spec_id,
            'gen_date'=>time(),
            'store_area_ids'=>$store_area_id,
            'operator_user_id'=>$operator_id,
            'quantity'=>$quantity,
            'expire_date'=>$expiration_date,
            'ref_id'=>$refId,
            'note'=>$note

        );
        $newId = $this->insert($data);

        if(!$newId){
            $err=1;
        }

        // update 总库存

         $where =array (
             'item_id'=>$item_id,
             'spec_id'=>$spec_id
         );

        $mdl_stock = loadModel('producing_item_stock');
        $stock_rec =$mdl_stock->getByWhere($where);
        if($stock_rec){
            $newQuantity = $stock_rec['stock_qty'] +$quantity;
            $dataForUpdate =array(
                'stock_qty'=>$newQuantity,
                'store_area_ids'=>$store_area_id
            );
            if(!$mdl_stock->update($dataForUpdate,$stock_rec['id'])){
                $err=1;
            }

        }else{

            $data=array(
                'factory_id'=>$factory_id,
                'item_id'=>$item_id,
                'spec_id'=>$spec_id,
                'stock_qty'=>$quantity,
                'store_area_ids'=>$store_area_id
            );
            if( !$mdl_stock->insert($data) ){
                $err=1;
            }
        }

      if($err==1){
          $this->rollback();
          return 0;
      }else{
          $this->commit();
          return 1;
      }



    }
}

?>