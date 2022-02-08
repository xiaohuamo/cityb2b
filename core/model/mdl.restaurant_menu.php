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


        $discount_rates =0.00;
        if($userid) {
            $where =array(
                'user_id'=>$userid,
                'factory_id'=>$factory_id
            );
            $user_info =loadModel('user_factory')->getByWhere($where);
            $discount_rates =$user_info['business_discount_rate'];
            if(!$discount_rates){
                $discount_rates =0.00;
            }
        }




       // get the good list of business .
       //get the main table good list
        $sql_main =" select  m.sub_category_id as sub_category_id,ifnull(menu_price.price,0) as menu_discount_price ,ifnull(menu_price.menu_discount_rate,0) as menu_discount_rate ,
                       ifnull(sub_cate_discount.discount_rate,0) as sub_cate_discount_rate ,ifnull(parent_cate_discount.discount_rate,0) as parent_cate_discount_rate ,
                       $discount_rates as customer_dicount_rate, m.id,m.restaurant_id,m.restaurant_category_id as parent_category_id,c01.category_sort_id as parent_cat_sort_id ,
                       c01.category_cn_name as parent_cat_cn_name,c01.category_en_name as parent_cate_en_name,c02.category_sort_id as sub_cate_sort_id ,
                       c02.category_cn_name as sub_cat_cn_name,c02.category_en_name as sub_cat_en_name,m.menu_id,m.menu_cn_name as title_cn, 
                       ROUND(m.price,2) as old_price,if(length(m.menu_pic)>0,concat('$uploadpath',m.menu_pic),'') as menu_pic ,
                       m.menu_desc,m.menu_en_desc,m.menu_option,if(length(m.menu_en_name)>0,m.menu_en_name,m.menu_cn_name) as title , 
                       if(length(m.unit_en)>0,m.unit_en,m.unit)as unit_en,if(length(m.unit)>0,m.unit,m.unit_en) as unit,
                       m.onSpecial,0 as status,if(m.menu_option>0,1,0) as hasGG,false as isTouch,0 as num 
                    from cc_restaurant_menu m
                    left join cc_restaurant_category c01 on c01.id=m.restaurant_category_id
                    left join cc_restaurant_category c02 on c02.id = m.sub_category_id 
                    left join cc_user_factory_category_discount_rate parent_cate_discount on ( m.restaurant_category_id =parent_cate_discount.category_id  and parent_cate_discount.userId =$userid)
                    left join cc_user_factory_category_discount_rate sub_cate_discount on ( m.sub_category_id =sub_cate_discount.category_id and sub_cate_discount.userId =$userid)
                    left join cc_user_factory_menu_price menu_price on ( m.id =menu_price.restaurant_menu_id and user_id =$userid )
                    where m.restaurant_id=$factory_id and ( length(menu_cn_name)>0 or length(menu_en_name)>0) and visible=1
                ";
      //  var_dump($sql_main);exit;

        $sql_sum_final ="select main_table.*,ifnull(bought_table.id,0) as bought from (". $sql_main ." ) as main_table 
    
                left join (select a.restaurant_menu_id as id from cc_wj_customer_coupon a where userId = $userid and business_id =$factory_id group by a.menu_id ) as bought_table on main_table.id = bought_table.id order by parent_cat_sort_id,sub_cate_sort_id
                   ";






     //   $userFactoryMenuPrices = loadModel('user_factory_menu_price')->getUserFactoryPriceList($userid, $factory_id);

       // var_dump($userFactoryMenuPrices);exit;
     //   var_dump('show_orginal_price:' . $show_origin_price);exit;
        //var_dump($sql_sum_final);exit;
        // $sql_sum = "select ($sql_main_table) as a union select ($sql_sub_cate_good_list) as b union select ($sql_parent_cate_good_list) as c ";
        $goodList =$this->getListBySql($sql_sum_final);

        // get the temp carts record of current user of the business .

        $sql ="select a.id as idd,a.userId,a.businessUserId,a.coupon_name as title,a.quantity as num,a.guige_des,a.guige_ids,a.menu_id as id   from cc_wj_user_temp_carts a
          where a.userId=$userid and a.businessUserId=$factory_id ";
        $cartItems =$this->getListBySql($sql);

        $mdl_sidedish_menu = loadModel('restaurant_sidedish_menu');
        $mdl_menu_option = loadModel('restaurant_menu_option');
        $mdl_restaurant_menu_option_category =loadModel('restaurant_menu_option_category');
        //获取规格
        foreach ($goodList as $key =>$value) {

          /*  //加载配菜
                if ($goodList[$key]['sidedish_category'] > 0) {
                    $goodList[$key]['sidedish_menu'] = $mdl_sidedish_menu->getList(null, array('restaurant_id' => $goodList[$key]['restaurant_id'], 'restaurant_category_id' => $goodList[$key]['sidedish_category'], " (length(menu_cn_name)>0 or length(menu_en_name)>0) "));
             }
          */


            //加载菜品规格
       /*     if (array_key_exists($value['id'], $userFactoryMenuPrices)) {
                $goodList[$key]['price'] = $userFactoryMenuPrices[$value['id']]['price'];
            } */

            $goodList[$key]['price']=$this->calculate_current_menu_price($value,$value['old_price']);


            if ($goodList[$key]['menu_option'] > 0) {

                $guigeList = $mdl_menu_option->getList(null, array('restaurant_id' => $goodList[$key]['restaurant_id'], 'restaurant_category_id' => $goodList[$key]['menu_option'], " (length(menu_cn_name)>0 or length(menu_en_name)>0) "));
                foreach ($guigeList as $key10=>$value10) {
                    $guigeList[$key10]['old_price']=$value10['price'];
                    $guigeList[$key10]['price']=$this->calculate_current_menu_price($value,$value10['price']);
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

    function  calculate_current_menu_price($value,$old_price){

        if($value['menu_discount_rate']>0) {
            //第一优先级 单品折扣率
            $newPrice = $old_price*(100-$value['menu_discount_rate'])/100;
            return round($newPrice,2);
        }elseif($value['menu_discount_price']>0){
            //第二优先级产品价格
            return round($value['menu_discount_price'],2);
        }elseif($value['sub_cate_discount_rate']>0){
            //第三优先级小类
            $newPrice = $old_price*(100-$value['sub_cate_discount_rate'])/100;
            return round($newPrice,2);
        }elseif($value['parent_cate_discount_rate']>0){
            //第四优先级大类
            $newPrice =$old_price*(100-$value['parent_cate_discount_rate'])/100;
            return round($newPrice,2);
         }elseif($value['customer_dicount_rate']>0){
            //第五优先级客户
            $newPrice = $old_price*(100-$value['customer_dicount_rate'])/100;
            return round($newPrice,2);
        }else{
            return $old_price;
        }


    }

    public function getUserBoughtMenu_new($userId, $businessId)
    {
/*
  1) 处理过程
    获得 bought list 并已大类进行排询；
        拿到大类后，建立一个大类列表，填充已购买的二级分类

        点击已购买，可以弹出二级分类，点击二级分类，可以到指定的地方。

        可能需要将这个产品列表封装到good_list2中，做为一个大类， 然后他的小类封装到小类里面。》》》》》》

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
            $menu[$key]['onSpecial'] = 0; //之前的on sepcial 都已提出了，下面再常规类别中显示的都可以不视为special
            if ($value['original_price'] <= 0) { //如果原价为空
                $menu[$key]['original_price'] = $value['price'];
            }

            $new_cat = $value['category_id'];
            // 如果en不为空，则保存en cate名称
            if (! $value['category_en_name']) {
                $menu[$key]['category_en_name'] = $value['category_cn_name'];
            }

            //根据语言类型 统一处理中英文显示
            if ($lang == '简体中文') {
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
                $menu[$key]['new_cat'] = $new_cat;//新种类意味着需要在前端标示
                $old_cat = $new_cat;
            } else {
                $menu[$key]['new_cat'] = 0;//如果种类和前边的相同怎不用特别标注
            }
        }

        return $menu;
    }
    public function getUserBoughtMenu($userId, $businessId, $deliveryTime, $lang = '简体中文')
    {
        //通过送货日期筛选可以包括在内的商家
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
            $menu[$key]['onSpecial'] = 0; //之前的on sepcial 都已提出了，下面再常规类别中显示的都可以不视为special
            if ($value['original_price'] <= 0) { //如果原价为空
                $menu[$key]['original_price'] = $value['price'];
            }

            $new_cat = $value['category_id'];
            // 如果en不为空，则保存en cate名称
            if (! $value['category_en_name']) {
                $menu[$key]['category_en_name'] = $value['category_cn_name'];
            }

            //根据语言类型 统一处理中英文显示
            if ($lang == '简体中文') {
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
                $menu[$key]['new_cat'] = $new_cat;//新种类意味着需要在前端标示
                $old_cat = $new_cat;
            } else {
                $menu[$key]['new_cat'] = 0;//如果种类和前边的相同怎不用特别标注
            }
        }

        return $menu;
    }
	
	
	//返回的是对于当前商家，当前类别（大类 或大类中类组合）可以待输入的空产品数量。
	
	
	 // 重要：   如果用户只选主分类的情况下，我们只检测 restaurant_menu 表中， 对应 的  restaurant_category_id 是否空记录；
	 // 如果用户选主分类，又选择子分类， 我们检查  restaurant_menu 表 与 联动表 cc_restaurant_menu_category 中相关的子类 的空记录信息。 因为  restaurant_menu 表 只存放主类信息。
	 // 当用户增加一个子类记录时， 在表 restaurant_menu 中的分类字段添加该子类的父类， 而 关联表中，添加该条单品号与对应子分类。 
     	 
		 
		 
	public function findCountOfEmptyMenuUponCategory($BusinessId,$Category,$sub_category)
  
	{
		 //var_dump('oo'.$sub_category);exit;
		if($sub_category) { //如果有子类
			$sql ="select m.*,o.* from cc_restaurant_menu o left join  cc_restaurant_menu_category m  on o.id =m.restaurant_menu_id ";
			$sql .= " where o.restaurant_id=$BusinessId and m.category_id =$sub_category ";
			
			
		}else{ //没有子类的情况
			
			$sql ="select o.* from cc_restaurant_menu o ";
			$sql .= " where o.restaurant_id=$BusinessId and (o.restaurant_category_id =$Category) and (length(o.menu_cn_name)=0 and length(o.menu_en_name)=0) ";
		}
		//var_dump($sql);exit;
		$EmptyRecord = $this->getListBySql ($sql);
		
		$countOfEmptyRecord =count($EmptyRecord);
		//var_dump($countOfEmptyRecord);exit;
		return $countOfEmptyRecord ;
		
	}
	
	
	//根据当前的分类获得当前分类的产品数量；
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
	
	
	public function addMenusUponCategory($BusinessId,$category,$sub_category,$isSubCategory,$currentCategoryCount,$new_add_count){
		
		$mdl_user =loadModel("user");
        $customerInfo = $mdl_user->get($BusinessId);
		$gstType = $customerInfo['gst_type'] % 2 ; //默认gst根据公司gst类型，1，3为全部gst和多数gst，2，4为全部无gst和少数gst
		
		if($isSubCategory){
		    $mdl =loadModel('restaurant_menu_category');
			for($i=0;$i<$new_add_count;$i++) {
				  
				$menu_id =($currentCategoryCount +$i +1)*10;
				$menu_info=array(
					'createUserId'=>$BusinessId,
					'restaurant_id'=>$BusinessId,
					'restaurant_category_id'=>$category, // 类别还是使用大类的类别
					'menu_id'=>$sub_category.$menu_id,
					'menu_cn_name'=>'',
					'price'=>'',
					'guige_group_id_2'=>'',
					'menu_pic'=>'',
					'Menu_desc'=>'',
					'menu_en_name'=>'',
					'include_gst' => $gstType
				);
				$newId = $this->insert($menu_info);
				if($newId) {
					 $menu_category_data =array(
					  'restaurant_menu_id'=>$newId,
					  'category_id'=>$sub_category, //关联表里写入小类的类别。
		   
					);
					
					$mdl->insert($menu_category_data);
				//var_dump($newId.' '.$sub_category);
				}			
			}

		
		  
		
			
			
		}else{
			 //如果是大类增加，不会在  关联表内添加对应记录。 
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
