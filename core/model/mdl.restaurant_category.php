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


    public function getCateList($customer_id){

         $sql ="select a.*,c.id,c.category_sort_id as sub_sort_id,c.category_cn_name as sub_cate_cn_name, c.category_en_name as sub_cate_en_name 
            from (SELECT id as parent_cate_id, category_sort_id as parent_sort_id, category_cn_name as parent_cate_cn_name ,
                         category_en_name as parent_cate_en_name 
            FROM `cc_restaurant_category` 
            WHERE restaurant_id =$customer_id and (parent_category_id is null or parent_category_id=0) and (length(category_cn_name)>0 or 
            length(category_en_name)>0) and isdeleted =0 order by category_sort_id) a
                left join cc_restaurant_category c on ( a.parent_cate_id = c.parent_category_id or a.parent_cate_id =c.id) 
            where (length(c.category_cn_name)>0 or length(c.category_en_name)>0) order by parent_sort_id,sub_sort_id";
         $list =$this->getListBySql($sql);



        foreach ($list as $key=>$val) {
            if ($val['parent_cate_id'] == $val['id']) {
                $list[$key]['title_cn'] = $val['parent_cate_cn_name'] . '|' . $val['parent_cate_en_name'];
                $list[$key]['title_en'] = $val['parent_cate_en_name'];
            } else {
                $list[$key]['title_cn'] = substr($val['parent_cate_en_name'],0,3).'--' . $val['sub_cate_cn_name'] . '|' . $val['sub_cate_en_name'];
                $list[$key]['title_en'] =  substr($val['parent_cate_en_name'],0,3).'--' . $val['sub_cate_en_name'];
            }
        }


        //var_dump($list); exit;

         return $list;


    }
    public function getParentCateList($customer_id){

        $sql ="SELECT id as parent_cate_id, category_sort_id as parent_sort_id, category_cn_name as parent_cate_cn_name ,
                category_en_name as parent_cate_en_name FROM `cc_restaurant_category`
                WHERE restaurant_id  =$customer_id  and (parent_category_id is null or parent_category_id=0)
                and (length(category_cn_name)>0 or length(category_en_name)>0) and isdeleted =0  
                order by category_sort_id";
        $list =$this->getListBySql($sql);


        return $list;


    }




}

?>