 <?php
 


class mdl_restaurant_menu_option extends mdl_base
{

	protected $tableName = '#@_restaurant_menu_option';

    public function getGuigeDetails($itemid){
        $sql ="SELECT g.id, g.menu_en_name,c.category_en_name FROM `cc_restaurant_menu` m
                left join cc_restaurant_menu_option g on m.menu_option=g.restaurant_category_id
                left join cc_restaurant_menu_option_category c on m.menu_option =c.id 
                WHERE m.id=$itemid and (length(g.menu_cn_name)>0 or length(g.menu_en_name)>0)" ;
//var_dump($sql);exit;
        $result = $this->getListBySql($sql);
        return $result;

    }

}

?>