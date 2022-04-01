 <?php
 


class mdl_xero_items_match extends mdl_base
{

	protected $tableName = '#@_xero_items_match';

    public function getXeroMatchList($factory_id,$sk){

        $sql ="select x.*,m.menu_en_name ,o.menu_en_name as spec_name,if((x.xero_ItemID=x.new_xero_ItemCode),1,0) as updated 
             from cc_xero_items_match x  
             left join cc_restaurant_menu m  on x.product_id =m.id
             left join cc_restaurant_menu_option o on x.guige_id = o.id ";


        if (! empty($sk)) {
            $sql .= " where  x.xero_code like  '%$sk%' or x.xero_name like   '%$sk%'  ";

        }

        $sql .= " order by x.xero_code ";
//var_dump($sql);exit;
        return $sql;

    }



}

?>