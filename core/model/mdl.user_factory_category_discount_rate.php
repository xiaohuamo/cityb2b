<?php

class mdl_user_factory_category_discount_rate extends mdl_base
{

	protected $tableName = '#@_user_factory_category_discount_rate';



   //获得当前客户，对于当前business 的大类折扣定制
    public function get_discount_data($business_id,$customer_id){

        //var_dump('business is '.$business_id. ' customerid:',$customer_id);exit;

        $sql ="SELECT ifnull(cate_discount.id,0) as id , cate.id as cate_id,cate.category_cn_name,cate.category_en_name,cate.category_sort_id ,$customer_id as userId,cate_discount.discount_rate,
                (select customer.business_discount_rate from cc_user_factory customer where customer.user_id=$customer_id and customer.factory_id =$business_id and customer.isHide =0 ) as customer_discount_rate
                
                FROM `cc_restaurant_category`  cate  
                left join cc_user_factory_category_discount_rate cate_discount on cate.`id` =cate_discount.category_id and cate_discount.userId =$customer_id
                
                WHERE cate.restaurant_id =$business_id and (length(cate.category_cn_name)>0 or length(cate.category_en_name)>0)  and (cate.parent_category_id =0 or cate.parent_category_id is null)  and isHide=0 and isdeleted=0
                order by cate.category_sort_id" ;
        $list = $this->getListBySql($sql);
        //var_dump($list);exit;

        foreach ($list as $key => $value){

            if(!$value['discount_rate']) {
                if(!$value['customer_discount_rate']) {
                    $list[$key]['discout_rate'] =0;
                    $list[$key]['issetDiscount'] =0;
                }else{
                    $list[$key]['discount_rate'] =$value['customer_discount_rate'];
                    $list[$key]['issetDiscount'] =0;
                }

            }else{
                $list[$key]['issetDiscount'] =1;

            }


          }


          return $list;




     }

    //获得当前客户，对于当前business 当前指定大类的所有小类的折扣信息，如果不指定大类，则为全部小类的信息
    public function get_sub_discount_data($business_id,$customer_id,$parent_category_id){

        $sql ="SELECT ifnull(cate_discount.id,0) as id , cate.parent_category_id ,  cate.id as sub_cate_id,cate.category_cn_name,cate.category_en_name,cate.category_sort_id ,$customer_id as userId,cate_discount.discount_rate, 
        
        (select customer.business_discount_rate from cc_user_factory customer where customer.user_id=$customer_id and customer.factory_id =$business_id and customer.isHide =0 ) as customer_discount_rate ,
        (select discount_rate from cc_user_factory_category_discount_rate where userId =$customer_id and category_id =$parent_category_id) as parent_cate_discount_rate
        
        FROM `cc_restaurant_category` cate left join cc_user_factory_category_discount_rate cate_discount on cate.`id` =cate_discount.category_id and cate_discount.userId =$customer_id 
        
        WHERE cate.restaurant_id =$business_id and cate.parent_category_id =$parent_category_id and   (length(cate.category_cn_name)>0 or length(cate.category_en_name)>0) and isHide=0 and isdeleted=0 order by cate.category_sort_id" ;
        //      var_dump($sql);exit;

        $list = $this->getListBySql($sql);


        foreach ($list as $key => $value){

            if(!$value['discount_rate']) { //如果当前没有定义小类折扣
                if(!$value['parent_cate_discount_rate']) {
                    //如果没有定义对应的大类折扣
                    if(!$value['customer_discount_rate']) {
                        //如果该客户没有定义discount
                        $list[$key]['discout_rate'] =0;
                    }else{
                        $list[$key]['discount_rate'] =$value['customer_discount_rate'];
                    }

                }else{
                   // 如果定义对应的大类折扣，则将大类discount做为小类的discount
                    $list[$key]['discount_rate'] =$value['parent_cate_discount_rate'];

                }

                $list[$key]['issetDiscount'] =0;


            }else{
                $list[$key]['issetDiscount'] =1;

            }



        }


        return $list;

    }



}

?>