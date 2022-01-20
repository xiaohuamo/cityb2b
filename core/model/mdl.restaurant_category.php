 <?php
 


class mdl_restaurant_category extends mdl_base
{

	protected $tableName = '#@_restaurant_category';

	
	 public function getCountOfCategory($restaurant_id)
    {
        $sql ="select count(*) as count  from cc_restaurant_category where restaurant_id =$restaurant_id";
		$rec = $this->getListBySql($sql);
		//var_dump($rec);exit;
		return  $rec[0]['count'];
    }
    public function getCategoryList($id) {

         $sql =" select id,category_id,category_sort_id as sort,category_cn_name as title_cn,category_en_name as title_en,hot from cc_restaurant_category where restaurant_id =$id and isHide=0 and isdeleted=0 and (parent_category_id=0 or parent_category_id is null) and (length(category_cn_name)>0 or length(category_en_name)>0) order by hot desc,category_sort_id";
         $cateList =$this->getListBySql($sql);
         // get the subcategory of the parent category
         foreach ($cateList as $key=>$value){
             $parent_category_id =$value['id'];
             $sql = "select id,category_id,category_sort_id as sort,category_cn_name as title_cn,category_en_name as title_en,hot from cc_restaurant_category where restaurant_id =$id  and parent_category_id=$parent_category_id and isHide=0 and isdeleted=0  and (length(category_cn_name)>0 or length(category_en_name)>0) order by category_sort_id ";
             $subCategorylist = $this->getListBySql($sql);
             $cateList[$key]['subCategoryList']=$subCategorylist;

         }
        // var_dump($cateList);exit;
         return $cateList;
    }


}

?>