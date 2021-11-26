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
