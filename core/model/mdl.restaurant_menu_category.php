<?php

class mdl_restaurant_menu_category extends mdl_base
{
    protected $tableName = '#@_restaurant_menu_category';

    public function insertIfExist($restaurantMenuId, $categoryId)
    {
        if(!$categoryId) {
            return null;
        }
        $menuCategory = $this->getByWhere([
            'restaurant_menu_id' => $restaurantMenuId,
            'category_id' => $categoryId
        ]);

        if(!$menuCategory) {
            $menuCategory = $this->insert([
                'restaurant_menu_id' => $restaurantMenuId,
                'category_id' => $categoryId
            ]);
        }
        return $menuCategory;
    }




 public function getFirstCateInRestaurantMenuCategory($restaurantMenuId) {
	 
	 $where =array(
	   'restaurant_menu_id'=>$restaurantMenuId
	 
	 );
	 
	 $firstRec = $this->getByWhere($where);
		
	 if($firstRec) {
		 //获取该分类的父类，如果不是父类的话。
		 $mdl =loadModel('restaurant_category');
		 $cateRec =$mdl->get($firstRec['category_id']);
		 if($cateRec['parent_category_id']) {
			 return  $cateRec['parent_category_id'];
			 
		 }else{
			  return  $cateRec['id'];
			 
		 }
		 
	 }else{
		 
		 return 0;
		 
	 }
	 
	 
 }

    public function deleteIfExist($restaurantMenuId, $categoryId)
    {
        if(!$categoryId) {
            return null;
        }
        $menuCategory = $this->getByWhere([
            'restaurant_menu_id' => $restaurantMenuId,
            'category_id' => $categoryId
        ]);

        if($menuCategory) {
			//var_dump($restaurantMenuId.' ' .$categoryId);exit;
            $menuCategory = $this->deleteByWhere([
                'restaurant_menu_id' => $restaurantMenuId,
                'category_id' => $categoryId
            ]);
        }
        return $menuCategory;
    }

    public function findMenuIdsByCategoryId($categoryId)
    {
        return $this->getList(['restaurant_menu_id'], [
            'category_id' => $categoryId
        ]);
    }

    public function findCategoryIdsByMenuId($restaurantMenuId)
    {
        return $this->getList(['category_id'], [
            'restaurant_menu_id' => $restaurantMenuId
        ]);
    }
	
	public function getCountOfCurrentSubCategory($sub_category)
    {
        return $this->getCount([
		'category_id' => $sub_category
        ]);
    }

	
}
?>