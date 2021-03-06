<?php

class mdl_restaurant_menu extends mdl_base
{
    protected $tableName = '#@_restaurant_menu';

    private $listField = 'id,restaurant_category_id,menu_id,guige_group_id_1,menu_cn_name,price,original_price,limit_buy_qty,speical_price,freshx_price,guige_group_id_2,menu_pic,menu_desc,menu_en_name,sidedish_category,menu_option,createUserId,ref_restaurant_id,men_desc-bk,deliver_price_bk,visible,menu_order_id,qty,unit,include_gst,onSpecial,commission_free';

    public function getAllRestaurantMenuListSql($where)
    {
        return $this->db->getSelectSql(explode(',', $this->listField), $this->tableName, $where, 'id desc');
    }

    public function getAllRestaurantMenuListBySql($sql)
    {
        return $this->db->toArray($this->db->query($sql));
    }




    public function getGoodList($factory_id,$userid=null,$uploadpath) {

        $mdl = loadModel('user_factory');
        $discount_rates = $mdl->getCurrentUserDiscountRate($userid,$factory_id);
        $gradeId = $mdl->getUserGradeId($userid, $factory_id);
        //$gradeId =0;


      // var_dump($discount_rates);exit;




       // get the good list of business .
       //get the main table good list
        $sql_main =" select  m.sub_category_id as sub_category_id,
                        ifnull(menu_price.price,0) as menu_discount_price ,
                        ifnull(grade_menu_price.price,0) as grade_menu_discount_price , 
                        ifnull(menu_price.menu_discount_rate,0) as menu_discount_rate ,
        
                        ifnull(grade_menu_price.menu_discount_rate,0) as grade_menu_discount_rate ,
                       
                       ifnull(sub_cate_discount.discount_rate,0) as sub_cate_discount_rate ,
                     ifnull(sub_cate_grade_discount.discount_rate,0) as sub_cate_grade_discount_rate ,
        
                        ifnull(parent_cate_discount.discount_rate,0) as parent_cate_discount_rate ,
                        ifnull(parent_cate_grade_discount.discount_rate,0) as parent_cate_grade_discount_rate ,
                       $discount_rates as customer_dicount_rate, m.id,m.restaurant_id,m.restaurant_category_id as parent_category_id,c01.category_sort_id as parent_cat_sort_id ,
                       c01.category_cn_name as parent_cat_cn_name,c01.category_en_name as parent_cate_en_name,c02.category_sort_id as sub_cate_sort_id ,
                       c02.category_cn_name as sub_cat_cn_name,c02.category_en_name as sub_cat_en_name,m.menu_id,m.menu_cn_name as title_cn, 
                       ROUND(m.price,2) as old_price,if(length(m.menu_pic_100)>0,concat('$uploadpath',m.menu_pic_100),concat('$uploadpath',m.menu_pic)) as menu_pic,
                        if(length(m.menu_pic_300)>0,concat('$uploadpath',m.menu_pic_300),concat('$uploadpath',m.menu_pic)) as menu_pic_300 ,
                       m.menu_desc,m.menu_en_desc,m.menu_option,if(length(m.menu_en_name)>0,m.menu_en_name,m.menu_cn_name) as title , 
                       if(length(m.unit_en)>0,m.unit_en,m.unit)as unit_en,if(length(m.unit)>0,m.unit,m.unit_en) as unit,
                       m.onSpecial,0 as status,if(m.menu_option>0,1,0) as hasGG,false as isTouch,m.lowest_price,0 as num ,m.menu_order_id
                    from cc_restaurant_menu m
                    left join cc_restaurant_category c01 on c01.id=m.restaurant_category_id
                    left join cc_restaurant_category c02 on c02.id = m.sub_category_id 
                    left join cc_user_factory_category_discount_rate parent_cate_discount on ( m.restaurant_category_id =parent_cate_discount.category_id  and parent_cate_discount.userId =$userid)
                    left join cc_user_factory_grade_category_discount_rate parent_cate_grade_discount on ( m.restaurant_category_id =parent_cate_grade_discount.category_id  and parent_cate_grade_discount.grade_id =$gradeId)
                    left join cc_user_factory_category_discount_rate sub_cate_discount on ( m.sub_category_id =sub_cate_discount.category_id and sub_cate_discount.userId =$userid)
                    left join cc_user_factory_grade_category_discount_rate sub_cate_grade_discount on ( m.sub_category_id =sub_cate_grade_discount.category_id and sub_cate_grade_discount.grade_id =$gradeId)
                
                    left join cc_user_factory_menu_price menu_price on ( m.id =menu_price.restaurant_menu_id and user_id =$userid )
                     left join cc_user_factory_grade_menu_price grade_menu_price on ( m.id =grade_menu_price.restaurant_menu_id and grade_menu_price.grade_id =$gradeId )
                    where m.restaurant_id=$factory_id  and ( length(menu_cn_name)>0 or length(menu_en_name)>0) and visible=1  
                ";
      //  var_dump($sql_main);exit;

        $sql_sum_final ="select main_table.*,ifnull(bought_table.id,0) as bought from (". $sql_main ." ) as main_table 
    
                left join (select a.restaurant_menu_id as id from cc_wj_customer_coupon a where userId = $userid and business_id =$factory_id group by a.menu_id ) as bought_table on main_table.id = bought_table.id order by parent_cat_sort_id,sub_cate_sort_id,menu_order_id 
                   ";






     //   $userFactoryMenuPrices = loadModel('user_factory_menu_price')->getUserFactoryPriceList($userid, $factory_id);

       // var_dump($userFactoryMenuPrices);exit;
     //   var_dump('show_orginal_price:' . $show_origin_price);exit;
       // var_dump($sql_sum_final);exit;
        // $sql_sum = "select ($sql_main_table) as a union select ($sql_sub_cate_good_list) as b union select ($sql_parent_cate_good_list) as c ";
        $goodList =$this->getListBySql($sql_sum_final);

        // get the temp carts record of current user of the business .

        $sql ="select a.id as idd,a.userId,a.businessUserId,a.coupon_name as title,a.quantity as num,a.guige_des,a.guige_ids,a.menu_id as id   from cc_wj_user_temp_carts a
          where a.userId=$userid and a.businessUserId=$factory_id ";
        $cartItems =$this->getListBySql($sql);

        $mdl_sidedish_menu = loadModel('restaurant_sidedish_menu');
        $mdl_menu_option = loadModel('restaurant_menu_option');
        $mdl_restaurant_menu_option_category =loadModel('restaurant_menu_option_category');
        //????????????
        foreach ($goodList as $key =>$value) {

          /*  //????????????
                if ($goodList[$key]['sidedish_category'] > 0) {
                    $goodList[$key]['sidedish_menu'] = $mdl_sidedish_menu->getList(null, array('restaurant_id' => $goodList[$key]['restaurant_id'], 'restaurant_category_id' => $goodList[$key]['sidedish_category'], " (length(menu_cn_name)>0 or length(menu_en_name)>0) "));
             }
          */


            //??????????????????
       /*     if (array_key_exists($value['id'], $userFactoryMenuPrices)) {
                $goodList[$key]['price'] = $userFactoryMenuPrices[$value['id']]['price'];
            } */

            $goodList[$key]['price']=$this->calculate_current_menu_price($value,$value['old_price']);
       //  var_dump( $goodList[$key]['price']);exit;
            $newprice=$this->calculate_current_menu_price($value,$value['old_price']);
            //??????????????????
            if(floatval($newprice)<floatval($value['lowest_price'])){
                $goodList[$key]['price'] =floatval($value['lowest_price']);
            }else{
                $goodList[$key]['price'] =$newprice;
            }


            if ($goodList[$key]['menu_option'] > 0) {

                $guigeList = $mdl_menu_option->getList(null, array('restaurant_id' => $goodList[$key]['restaurant_id'], 'restaurant_category_id' => $goodList[$key]['menu_option'], " (length(menu_cn_name)>0 or length(menu_en_name)>0) "));

              //?????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????
                if($goodList[$key]['bought']){
                    $boughtGuigeList =$this->getBoughtGuigeList($userid,$goodList[$key]['id']);

                }


                foreach ($guigeList as $key10=>$value10) {
                    if($value10['price']<=0) {
                        $guige_price =  $goodList[$key]['old_price'];
                    }else{
                        $guige_price = $value10['price'];
                    }
                    $guigeList[$key10]['old_price']=floatval($guige_price);
                    $guigeList[$key10]['price']=floatval($this->calculate_current_menu_price($value,$guige_price));

                    $newprice=$this->calculate_current_menu_price($value,$guige_price);

                    //??????????????????
                    if($newprice< $goodList[$key]['lowest_price']){
                         $guigeList[$key10]['price'] =floatval($goodList[$key]['lowest_price']);

                    }else{
                        $guigeList[$key10]['price'] =$newprice;
                    }


                    if($boughtGuigeList){
                        //??????????????????????????????????????????????????????

                      if(strstr($boughtGuigeList,$value10['id'])){
                          $guigeList[$key10]['islastBought'] =1;
                      }else{
                          $guigeList[$key10]['islastBought'] =0;
                      }



                    }else{
                        $guigeList[$key10]['islastBought'] =0;
                    }
                }

                $goodList[$key]['menu_option_list'] = $guigeList;

                // var_dump($goodList[$key]['menu_option_list']);exit;
                $goodList[$key]['guige_des1'] =$mdl_restaurant_menu_option_category->get($value['menu_option']);



          }

        }
       // var_dump($goodList);exit;

        foreach ($cartItems as $key => $value) {
            foreach ($goodList as $keysub =>$valuesub){
                if($value['id']==$valuesub['id']){
                    $goodList[$keysub]['num']=$value['num'];
                  //  var_dump('ttt '.goodList[$keysub]);
                    break;
                }

            }

        }



                


     //  var_dump($goodList);exit;
        return $goodList;

    }

    function CheckIfSpecIdisAvaliable($id,$specId){

        $sql ="select count(*) as count from cc_restaurant_menu where menu_option=$specId and id !=$id";
        $result  =$this->getListBySql($sql);
        if($result){
            if($result[0]['count']==0){
                return 1;
            }else{
                return 0;
            }
        }else{
            return 1;
        }

    }

    function  getBoughtGuigeList($userid,$id){

        $sql ="select order_id,guige1_id  from cc_wj_customer_coupon where restaurant_menu_id=$id and userId =$userid order by id desc limit 5";

        $data = $this->getListBySql($sql);
        if($data){
            $old_order_id =0;
            $guigeStr ='';
            foreach ($data as $key=>$value){ //????????????????????????????????????order??? ????????????????????????
              if($old_order_id  ==0) {
                  $old_order_id =$value['order_id'];
                  $guigeStr =','.$value['guige1_id'].',';
                  continue;
              }else{
                  if($old_order_id == $value['order_id']) {
                      $guigeStr .= $value['guige1_id'].',';
                      $old_order_id =$value['order_id'];
                  }else{
                      break;
                  }
              }

            }
            return  $guigeStr;
        }else{
            return '';
        }



    }

    function  calculate_current_menu_price($value,$old_price){

        if($value['menu_discount_rate']>0) {
            //??????????????? ???????????????
            $newPrice = $old_price*(100-$value['menu_discount_rate'])/100;
            return round($newPrice,2);
        }elseif($value['grade_menu_discount_rate']>0) {
            //??????????????? ???????????????
            $newPrice = $old_price*(100-$value['grade_menu_discount_rate'])/100;
            return round($newPrice,2);
        }elseif($value['menu_discount_price']>0){
            //???????????????????????????
            return round($value['menu_discount_price'],2);
        }elseif($value['grade_menu_discount_price']>0){
            //???????????????????????????
            return round($value['grade_menu_discount_price'],2);
        }elseif($value['sub_cate_discount_rate']>0){
            //?????????????????????
            $newPrice = $old_price*(100-$value['sub_cate_discount_rate'])/100;
            return round($newPrice,2);
        }elseif($value['sub_cate_grade_discount_rate']>0){
            //?????????????????????
            $newPrice = $old_price*(100-$value['sub_cate_grade_discount_rate'])/100;
            return round($newPrice,2);
        }elseif($value['parent_cate_discount_rate']>0){
            //?????????????????????
            $newPrice =$old_price*(100-$value['parent_cate_discount_rate'])/100;
            return round($newPrice,2);
         }elseif($value['parent_cate_grade_discount_rate']>0){
            //?????????????????????
            $newPrice =$old_price*(100-$value['parent_cate_grade_discount_rate'])/100;
            return round($newPrice,2);
        }elseif($value['customer_dicount_rate']>0){
            //?????????????????????
            $newPrice = $old_price*(100-$value['customer_dicount_rate'])/100;
            return round($newPrice,2);
        }elseif($value['customer_dicount_rate']<0){
            //?????????????????????
            $newPrice = $old_price*(100-$value['customer_dicount_rate'])/100;
            return round($newPrice,1);
        }else{
            return $old_price;
        }


    }

    public function getUserBoughtMenu_new($userId, $businessId)
    {
/*
  1) ????????????
    ?????? bought list ???????????????????????????
        ???????????????????????????????????????????????????????????????????????????

        ?????????????????????????????????????????????????????????????????????????????????????????????

        ??????????????????????????????????????????good_list2??????????????????????????? ????????????????????????????????????????????????????????????

*/

        $sql = "SELECT d.cn_displayName as category_cn_name ,
                    d.en_displayName as category_en_name ,
                    a.business_id as category_id,
                    c.category_sort_id,
                    a.`restaurant_menu_id`,
                    a.`bonus_title`,
                    sum(a.customer_buying_quantity) as sumofbuy ,
                    b.*  
                FROM `cc_wj_customer_coupon` a 
                left join cc_restaurant_menu b on a.restaurant_menu_id = b.id  
                left join cc_restaurant_category c on b.restaurant_category_id =c.id 
                WHERE a.business_id =$businessId and a.userId=$userId  and a.restaurant_menu_id is not null 
                and (b.menu_cn_name <> '' or b.menu_en_name <> '')
               
                group by a.restaurant_menu_id  
                order by sumofbuy desc";

        $menu = $this->getListBySql($sql);

        $old_cat = "";
        foreach ($menu as $key => $value) {
            $menu[$key]['onSpecial'] = 0; //?????????on sepcial ?????????????????????????????????????????????????????????????????????special
            if ($value['original_price'] <= 0) { //??????????????????
                $menu[$key]['original_price'] = $value['price'];
            }

            $new_cat = $value['category_id'];
            // ??????en?????????????????????en cate??????
            if (! $value['category_en_name']) {
                $menu[$key]['category_en_name'] = $value['category_cn_name'];
            }

            //?????????????????? ???????????????????????????
            if ($lang == '????????????') {
                $menu[$key]['category_name'] = $value['category_cn_name'];
                $menu[$key]['menu_name'] = $value['menu_cn_name'];
            } else {
                if ($lang == 'English') {
                    $menu[$key]['category_name'] = $value['category_en_name'];
                    $menu[$key]['menu_desc'] = $value['menu_en_desc'];
                    $menu[$key]['menu_name'] = $value['menu_cn_name'];
                }
            }

            if ($old_cat <> $new_cat) {
                $menu[$key]['new_cat'] = $new_cat;//???????????????????????????????????????
                $old_cat = $new_cat;
            } else {
                $menu[$key]['new_cat'] = 0;//???????????????????????????????????????????????????
            }
        }

        return $menu;
    }
    public function getUserBoughtMenu($userId, $businessId, $deliveryTime, $lang = '????????????')
    {
        //???????????????????????????????????????????????????
        $parts = explode("@", $deliveryTime);
        $dateTimestamp = $parts[0];
        $timeType = $parts[1];
        $centreBusinessId = DispCenter::getDispCenterIdOfSupplier($businessId);
        $businessIds = [];
        if ($centreBusinessId) {
            $business = DispCenter::getAvailableBusinessForDeliverDate($dateTimestamp, $timeType, $centreBusinessId, $this->lang['lang'][0]);
            $businessIds = array_keys($business);
        }

        $whereSting = " b.restaurant_id = $businessId ";
        if (count($businessIds) > 0) {
            $businessIdsString = join(',', $businessIds);
            $whereSting = " ($whereSting or b.restaurant_id in ($businessIdsString))";
        }

        $sql = "SELECT d.cn_displayName as category_cn_name ,
                    d.en_displayName as category_en_name ,
                    a.business_id as category_id,
                    c.category_sort_id,
                    a.`restaurant_menu_id`,
                    a.`bonus_title`,
                    sum(a.customer_buying_quantity) as sumofbuy ,
                    b.*  
                FROM `cc_wj_customer_coupon` a 
                left join cc_restaurant_menu b on a.restaurant_menu_id = b.id  
                left join cc_restaurant_category c on b.restaurant_category_id =c.id 
                left join cc_freshfood_disp_centre_suppliers d on a.business_id =d.suppliers_id  
                WHERE userId=$userId 
                and $whereSting
                and restaurant_menu_id is not null 
                and (b.menu_cn_name <> '' or b.menu_en_name <> '')
                and b.price != 0
                group by restaurant_menu_id  
                order by a.business_id ,sumofbuy desc";

        $menu = $this->getListBySql($sql);

        $old_cat = "";
        foreach ($menu as $key => $value) {
            $menu[$key]['onSpecial'] = 0; //?????????on sepcial ?????????????????????????????????????????????????????????????????????special
            if ($value['original_price'] <= 0) { //??????????????????
                $menu[$key]['original_price'] = $value['price'];
            }

            $new_cat = $value['category_id'];
            // ??????en?????????????????????en cate??????
            if (! $value['category_en_name']) {
                $menu[$key]['category_en_name'] = $value['category_cn_name'];
            }

            //?????????????????? ???????????????????????????
            if ($lang == '????????????') {
                $menu[$key]['category_name'] = $value['category_cn_name'];
                $menu[$key]['menu_name'] = $value['menu_cn_name'];
            } else {
                if ($lang == 'English') {
                    $menu[$key]['category_name'] = $value['category_en_name'];
                    $menu[$key]['menu_desc'] = $value['menu_en_desc'];
                    $menu[$key]['menu_name'] = $value['menu_cn_name'];
                }
            }

            if ($old_cat <> $new_cat) {
                $menu[$key]['new_cat'] = $new_cat;//???????????????????????????????????????
                $old_cat = $new_cat;
            } else {
                $menu[$key]['new_cat'] = 0;//???????????????????????????????????????????????????
            }
        }

        return $menu;
    }
	
	
	//?????????????????????????????????????????????????????? ????????????????????????????????????????????????????????????
	
	
	 // ?????????   ????????????????????????????????????????????????????????? restaurant_menu ????????? ?????? ???  restaurant_category_id ??????????????????
	 // ???????????????????????????????????????????????? ????????????  restaurant_menu ??? ??? ????????? cc_restaurant_menu_category ?????????????????? ????????????????????? ??????  restaurant_menu ??? ????????????????????????
	 // ??????????????????????????????????????? ?????? restaurant_menu ????????????????????????????????????????????? ??? ????????????????????????????????????????????????????????? 
     	 
		 
		 
	public function findCountOfEmptyMenuUponCategory($BusinessId,$Category,$sub_category)
  
	{
		 //var_dump('oo'.$sub_category);exit;
		if($sub_category) { //???????????????
			$sql ="select m.*,o.* from cc_restaurant_menu o left join  cc_restaurant_menu_category m  on o.id =m.restaurant_menu_id ";
			$sql .= " where o.restaurant_id=$BusinessId and m.category_id =$sub_category ";
			
			
		}else{ //?????????????????????
			
			$sql ="select o.* from cc_restaurant_menu o ";
			$sql .= " where o.restaurant_id=$BusinessId and (o.restaurant_category_id =$Category) and (length(o.menu_cn_name)=0 and length(o.menu_en_name)=0) ";
		}
		//var_dump($sql);exit;
		$EmptyRecord = $this->getListBySql ($sql);
		
		$countOfEmptyRecord =count($EmptyRecord);
		//var_dump($countOfEmptyRecord);exit;
		return $countOfEmptyRecord ;
		
	}
	
	
	//?????????????????????????????????????????????????????????
	public function getCountOfCurrentCategory($Category){
		
		$where =array(
		  'restaurant_category_id'=>$Category
		);
		$count = $this->getCount($where);
		
		if($count) {
			return $count;
			
		}else{
			return 0;
		}
		
		
	}

    public function getItemWithSpecInfo($factory_id){

        $sql ="select spec.id as spec_type_id ,spec.category_en_name as spec_type_name , 
       if(spec_details.id is null,0,spec_details.id) as spec_id, stock.stock_qty,spec_details.menu_en_name as spec_name,
       o.* ,b.category_cn_name,b.category_en_name 
from cc_restaurant_menu o left join cc_restaurant_category b on b.id=o.restaurant_category_id 
    left join cc_restaurant_menu_option_category spec on o.menu_option = spec.id 
    left join cc_restaurant_menu_option spec_details on spec.id=spec_details.restaurant_category_id 
                                                            and (length(spec_details.menu_en_name)>0 or length(spec_details.menu_cn_name)>0 ) 
    left join cc_producing_item_stock stock on o.id =stock.item_id and if(spec_details.id is null ,0,spec_details.id)=stock.spec_id 
where o.isDeleted=0 and (length(o.menu_cn_name) >0 or length(o.menu_en_name) >0)
  and o.restaurant_id=$factory_id order by restaurant_category_id,LENGTH(o.menu_id),o.menu_id";
     // var_dump($sql);exit;
       $result = $this->getListBySql($sql);
       return $result;
    }
	
	
	public function addMenusUponCategory($BusinessId,$category,$sub_category,$isSubCategory,$currentCategoryCount,$new_add_count){
		
		$mdl_user =loadModel("user");
        $customerInfo = $mdl_user->get($BusinessId);
		$gstType = $customerInfo['gst_type'] % 2 ; //??????gst????????????gst?????????1???3?????????gst?????????gst???2???4????????????gst?????????gst
		
		if($isSubCategory){
		    $mdl =loadModel('restaurant_menu_category');
			for($i=0;$i<$new_add_count;$i++) {
				  
				$menu_id =($currentCategoryCount +$i +1)*10;
				$menu_info=array(
					'createUserId'=>$BusinessId,
					'restaurant_id'=>$BusinessId,
					'restaurant_category_id'=>$category, // ?????????????????????????????????
					'menu_id'=>$sub_category.$menu_id,
					'menu_cn_name'=>'',
					'price'=>'',
					'guige_group_id_2'=>'',
					'menu_pic'=>'',
					'Menu_desc'=>'',
					'menu_en_name'=>'',
					'include_gst' => $gstType,
                    'visible'=>0
				);
				$newId = $this->insert($menu_info);
				if($newId) {
					 $menu_category_data =array(
					  'restaurant_menu_id'=>$newId,
					  'category_id'=>$sub_category, //????????????????????????????????????
		   
					);
					
					$mdl->insert($menu_category_data);
				//var_dump($newId.' '.$sub_category);
				}			
			}

		
		  
		
			
			
		}else{
			 //?????????????????????????????????  ????????????????????????????????? 
			//var_dump($new_add_count);exit;
				
			
			for($i=0;$i<$new_add_count;$i++) {
				
				$menu_id =($currentCategoryCount +$i +1)*10;
				$menu_info=array(
					'createUserId'=>$BusinessId,
					'restaurant_id'=>$BusinessId,
					'restaurant_category_id'=>$category,
					'menu_id'=>$category.$menu_id,
					'menu_cn_name'=>'',
					'price'=>'',
					'guige_group_id_2'=>'',
					'menu_pic'=>'',
					'Menu_desc'=>'',
					'menu_en_name'=>'',
					'include_gst' => $gstType
				);
				//var_dump($new_add_count);exit;
				$newId = $this->insert($menu_info);
				//var_dump($menu_info);exit;		
			}

			
		}
		
	}
	

}
