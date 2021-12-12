<?php

class ctl_factorypage extends cmsPage
{
    function ctl_factorypage()
    {
        parent::cmsPage();
        $mdl_restaurant = $this->loadModel('restaurant_info');
        $restaurant = $mdl_restaurant->getByWhere(['userId' => $this->loginUser['id']]);
        $this->setData($restaurant, 'restaurant');
    }

    public function restaurant_action()
    {
        $id = (int) get2('id');
        $userId = $this->loginUser['id'];
        $cart = (int) get2('cart');
        $default_menu_page_items = 10;

        if (! $id) {
            $this->sheader(null, '请选择正确工厂');
        }

        if ($this->loginUser) {
            //插入一段获取某用户购买历史的程序
            $deliveryTime = $this->cookie->getCookie('DispCenterUserSelectedDeliveryDate');
            $menu_bought_list = $this->loadModel("restaurant_menu")->getUserBoughtMenu($userId, $id, $deliveryTime, $this->lang['lang'][0]);

            $mdl_user_factory_menu_price = $this->loadModel('user_factory_menu_price');
            $mdl_user_factory = $this->loadModel('user_factory');
            $userFactoryMenuPrices = $mdl_user_factory_menu_price->getUserFactoryPriceList($userId, $id);

            foreach ($menu_bought_list as $key => $value) {
                $show_origin_price = $mdl_user_factory->getByWhere([
                    'user_id' => $id,
                    'factory_id' => $this->loginUser['id']
                ])['show_origin_price'];
                if(!$show_origin_price) {
                    $menu_bought_list[$key]['price'] = 0;
                }

                if (array_key_exists($value['id'], $userFactoryMenuPrices)) {
                    $menu_bought_list[$key]['price'] = $userFactoryMenuPrices[$value['id']]['price'];
                }
            }
			
			//var_dump(menu_bougt_list);exit;
			//加载配菜
			$mdl_sidedish_menu=$this->loadModel('restaurant_sidedish_menu');
			foreach ($menu_bought_list as $key => $value) {
				if($menu_bought_list[$key]['sidedish_category']>0){
					$menu_bought_list[$key]['sidedish_menu']=$mdl_sidedish_menu->getList(null,array('restaurant_id'=>$menu_bought_list[$key]['restaurant_id'],'restaurant_category_id'=>$menu_bought_list[$key]['sidedish_category']," (length(menu_cn_name)>0 or length(menu_en_name)>0) "));

					

				}
			}

			//加载菜品规格
			$mdl_menu_option=$this->loadModel('restaurant_menu_option');
			foreach ($menu_bought_list as $key => $value) {
				if($menu_bought_list[$key]['menu_option']>0){
					$menu_bought_list[$key]['menu_option_list']=$mdl_menu_option->getList(null,array('restaurant_id'=>$menu_bought_list[$key]['restaurant_id'],'restaurant_category_id'=>$menu_bought_list[$key]['menu_option']," (length(menu_cn_name)>0 or length(menu_en_name)>0) "));

					

				}
			}
			
			
			
			
			
			
			
            $this->setData($menu_bought_list, 'menu_bought_list');

            if($this->loginUser['password'] == $this->loginUser['init_password']) {
                $this->setData(true, 'need_update');
                $this->setData( $this->loadModel('wj_abn_application')->getByWhere([
                    'userId' => $this->loginUser['id'],
                ]), 'abnAccount');
            }


            $show_origin_price = $mdl_user_factory->getByWhere([
                'user_id' => $this->loginUser['id'],
                'factory_id' => $id
            ])['show_origin_price'];
            $this->setData($show_origin_price, 'show_origin_price');

            $this->setData($mdl_user_factory->isUserApproved($userId, $id), 'userApproved');
        }

        $where = [
            'createUserId' => $id,
            'EvoucherOrrealproduct' => 'restaurant_menu',
        ];

        $mdl_coupons = $this->loadModel("coupons");

        $restaurant_coupon = $mdl_coupons->getByWhere($where);
        $this->setData($restaurant_coupon['id'], 'restaurant_couponID');

        // 获取的餐馆ID--是商家的ID ,判断当前商家是否设置餐厅入口
        if (($restaurant_coupon['isApproved'] == 1 && $restaurant_coupon['status'] == 4) || $restaurant_coupon['createUserId'] == $this->loginUser['id'] || $_SESSION['coupon_private_view_allowed'] == $restaurant_coupon['id']) {
            $mdl_user = $this->loadModel("user");
            $business_user = $mdl_user->get($restaurant_coupon['createUserId']);
            $restaurant_coupon['business'] = $business_user;

            $this->setData($restaurant_coupon, 'coupon');
        } else {
            $this->sheader(HTTP_ROOT_WWW.'coupon1/coupon_private_view_gate?id='.$restaurant_coupon['id']);
            $this->sheader(null, '当前商家还未开启线上餐厅,请稍后..');
        }

        $this->setData($id, 'restaurant_id');
        $this->setData($cart, 'cart');

        $title = str_replace('|', '', $restaurant_coupon['title']);
        $this->setData($title, 'pageTitle');
        $this->setData($title, 'pageKeywords');
        $this->setData($title, 'pageDescription');

        $this->loadModel('freshfood_disp_suppliers_schedule');
        $businessDispSchedule = DispCenter::getBusinessDispSchedule($id);
        $businessDispScheduleFilledWithContinueDates = DispCenter::getFollowingNDaysIncludeAvailableDeliver($businessDispSchedule);
        $this->setData($businessDispScheduleFilledWithContinueDates, 'businessDispSchedule');
        $this->setData(in_array($id, DispCenter::getSupplierList()), 'isDispCenterBusiness');

        $this->setData(join(DispCenter::getPostcode(DispCenter::getDispCenterIdOfSupplier($id)), ','), 'postcodeSupported'); //使用统配商家邮编信息

        $where1 = [
            'restaurant_id' => $id,
        ];
        $mdl_restaurant_promotion_manjian = $this->loadModel("restaurant_promotion_manjian");
        $restaurant_promotion_manjian = $mdl_restaurant_promotion_manjian->getByWhere($where1);
        $menu = self::get_menu_list($id, $restaurant_coupon, $restaurant_promotion_manjian);
        $this->setData((int) (count($menu) / $default_menu_page_items), 'menu_pages');
        $this->setData(array_slice($menu, 0, $default_menu_page_items), 'menu');
        $this->setData(1, 'lazyload');

        $restaurant_category = self::get_category_list($id);
        $this->setData($restaurant_category, 'restaurant_category');

        $this->setData($this->loadModel('overwriteCouponStoreLink')->getOverWriteLink($id), 'storeOverWriteLink');

        $this->display_pc_mobile('mobile/factorypage/coupon_detail_coupon', 'mobile/factorypage/coupon_detail_coupon');

        return;
    }

    public function order_for_customer_action()
    {
        $default_menu_page_items = 10;
        $id = $this->loginUser['id'];
        $cart = (int) get2('cart');
        $userId = (int) get2('user_id');

        $mdl_user = $this->loadModel("user");
        $currentUser = $mdl_user->getUserById($userId);
        $this->setData($userId, 'userId');
        $this->setData($currentUser, 'currentUser');

        $mdl_user_factor = $this->loadModel('user_factory');
		
		//获得当前用户的实际商家所有者商家id
		$factoryId = $mdl_user_factor->getBusinessId( $this->loginUser['id'], $this->loginUser['role']);
		$id = $factoryId;
		if(	$this->loginUser['role']==20) {
			$salesManId = $this->loginUser['id'];
			
		}
		//var_dump($factoryId);exit;
		$factoryList = $mdl_user_factor->getUserFactoryList($factoryId,null,$salesManId);
		//var_dump($salesManId);exit;
        $this->setData($factoryList, 'factoryUsers');

        $where = [
            'createUserId' => $id,
            'EvoucherOrrealproduct' => 'restaurant_menu',
        ];

        $mdl_coupons = $this->loadModel("coupons");

        $restaurant_coupon = $mdl_coupons->getByWhere($where);
        $this->setData($restaurant_coupon['id'], 'restaurant_couponID');

        $business_user = $mdl_user->get($id);
        $restaurant_coupon['business'] = $business_user;

        //插入一段获取某用户购买历史的程序
        $deliveryTime = $this->cookie->getCookie('DispCenterUserSelectedDeliveryDate');
        $menu_bought_list = $this->loadModel("restaurant_menu")->getUserBoughtMenu($userId, $id, $deliveryTime, $this->lang['lang'][0]);

        $mdl_user_factory_menu_price = $this->loadModel('user_factory_menu_price');
        $userFactoryMenuPrices = $mdl_user_factory_menu_price->getUserFactoryPriceList($userId, $id);

        foreach ($menu_bought_list as $key => $value) {
            if (array_key_exists($value['id'], $userFactoryMenuPrices)) {
                $menu_bought_list[$key]['price'] = $userFactoryMenuPrices[$value['id']]['price'];
            }
        }
		
		//加载配菜
			$mdl_sidedish_menu=$this->loadModel('restaurant_sidedish_menu');
			foreach ($menu_bought_list as $key => $value) {
				if($menu_bought_list[$key]['sidedish_category']>0){
					$menu_bought_list[$key]['sidedish_menu']=$mdl_sidedish_menu->getList(null,array('restaurant_id'=>$menu_bought_list[$key]['restaurant_id'],'restaurant_category_id'=>$menu_bought_list[$key]['sidedish_category']," (length(menu_cn_name)>0 or length(menu_en_name)>0) "));

					

				}
			}

			//加载菜品规格
			$mdl_menu_option=$this->loadModel('restaurant_menu_option');
			foreach ($menu_bought_list as $key => $value) {
				if($menu_bought_list[$key]['menu_option']>0){
					$menu_bought_list[$key]['menu_option_list']=$mdl_menu_option->getList(null,array('restaurant_id'=>$menu_bought_list[$key]['restaurant_id'],'restaurant_category_id'=>$menu_bought_list[$key]['menu_option']," (length(menu_cn_name)>0 or length(menu_en_name)>0) "));

					

				}
			}
		
				 // 获得配菜分类列表
				 
				$where=array();
				$where[]="(length(category_cn_name) >0 or length(category_en_name) >0)";
				$where['restaurant_id']=$id;
				$restaurant_sidedish_category_list=$this->loadModel('restaurant_sidedish_category')->getList(null,$where);
				$this->setData($restaurant_sidedish_category_list,'sidedish_category_list');
				
				//  获得配菜分类列表
				
				$where=array();
				$where[]="(length(category_cn_name) >0 or length(category_en_name) >0)";
				$where['restaurant_id']=$id;
				$restaurant_menu_option_list=$this->loadModel('restaurant_menu_option_category')->getList(null,$where);
				$this->setData($restaurant_menu_option_list,'menu_option_list');
		
		
				foreach ($data as $key => $menu) {
					$categoryIds = $this->loadModel('restaurant_menu_category')->findCategoryIdsByMenuId($menu['id']);
					$data[$key]['categoryIds'] = $categoryIds;
				}
		
			$this->setData($data, 'data');
			
        $this->setData($menu_bought_list, 'menu_bought_list');

        $this->setData($restaurant_coupon, 'coupon');

        $this->setData($id, 'restaurant_id');
        $this->setData($cart, 'cart');

        $title = str_replace('|', '', $restaurant_coupon['title']);
        $this->setData($title, 'pageTitle');
        $this->setData($title, 'pageKeywords');
        $this->setData($title, 'pageDescription');

        $this->loadModel('freshfood_disp_suppliers_schedule');

        $businessDispSchedule = DispCenter::getBusinessDispSchedule($id);
        $businessDispScheduleFilledWithContinueDates = DispCenter::getFollowingNDaysIncludeAvailableDeliver($businessDispSchedule);
        $this->setData($businessDispScheduleFilledWithContinueDates, 'businessDispSchedule');
        $this->setData(in_array($id, DispCenter::getSupplierList()), 'isDispCenterBusiness');

        $this->setData(join(DispCenter::getPostcode(DispCenter::getDispCenterIdOfSupplier($id)), ','), 'postcodeSupported'); //使用统配商家邮编信息

        $where1 = [
            'restaurant_id' => $id,
        ];
        $mdl_restaurant_promotion_manjian = $this->loadModel("restaurant_promotion_manjian");
        $restaurant_promotion_manjian = $mdl_restaurant_promotion_manjian->getByWhere($where1);
        $menu = self::get_menu_list($id, $restaurant_coupon, $restaurant_promotion_manjian, $userId);

        $show_origin_price = $this->loadModel('user_factory')->getByWhere([
            'user_id' => $userId,
            'factory_id' => $id
        ])['show_origin_price'];

        $this->setData($show_origin_price, 'show_origin_price');

        $this->setData((int) (count($menu) / $default_menu_page_items), 'menu_pages');
        $this->setData(array_slice($menu, 0, $default_menu_page_items), 'menu');
        $this->setData(1, 'lazyload');

        $restaurant_category = self::get_category_list($id);
        $this->setData($restaurant_category, 'restaurant_category');

        $this->setData($this->loadModel('overwriteCouponStoreLink')->getOverWriteLink($id), 'storeOverWriteLink');

        $this->display_pc_mobile('mobile/factorypage/order_for_customer', 'mobile/factorypage/order_for_customer');

        return;
    }

    public function get_category_list($id)
    {
        //加载餐馆菜单
        $mdl_restaurant_category = $this->loadModel('restaurant_category');
        $restaurant_category = $mdl_restaurant_category->getListBySql("select * from cc_restaurant_category where restaurant_id = ".$id." and (length(category_cn_name)>0 or length(category_en_name)>0) order by `category_sort_id`");

        foreach ($restaurant_category as $key => $value) {
            if ($restaurant_category[$key]['category_en_name'] == '') {
                $restaurant_category[$key]['category_en_name'] = $restaurant_category[$key]['category_cn_name'];
            }
            if ($this->lang['lang'][0] == 'English') {
                $restaurant_category[$key]['category_name'] = $restaurant_category[$key]['category_en_name'];
            } else {
                $restaurant_category[$key]['category_name'] = $restaurant_category[$key]['category_cn_name'];
            }
        }

        // 如果发现有special菜单，那么生成一个新的类别编号，并置顶
        $mdl_restaurant_menu = $this->loadModel('restaurant_menu');
        $sql_special = "select count(*) as count from cc_restaurant_menu a where a.restaurant_id =$id and  (length(a.menu_cn_name)>0 or length(a.menu_en_name)>0) and a.visible=1 and a.onSpecial =1  ";
        $exist_special = $mdl_restaurant_menu->getListBySql($sql_special);
        if ($exist_special[0]['count'] > 0) {

            $special_array = [
                'restaurant_id' => $id,
                'category_id' => 1000,
                'category_sort_id' => 10,
                'createUserId' => $id,
                'ref_restaurant_id' => 0,
                'ref_DishTypeId' => 0,
                'hot' => 1,

            ];

            if ($this->lang['lang'][0] == 'English') {
                $special_array['category_name'] = 'On Sale';
            } else {
                $special_array['category_name'] = '本期优惠';
            }
            array_unshift($restaurant_category, $special_array);
        }

        return $restaurant_category;
    }

    public function get_cart_action()
    {
        $id = (int) get2('businessUserId');

        $userId = (int) get2('user_id');
        if (!$userId) {
            $userId = $this->loginUser['id'];
        }

        if ($userId) {
            //清除购物车其它产品
            $mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');

            // 获取该商家关联的配送中心所有商家列表，并将用户在这些相关的商家列表中的购买清单全部列出来

            $suppliers_list = $this->get_same_dispatching_centre_suppliers_list($id);

            $sql = "SELECT b.restaurant_category_id,category_sort_id,category_cn_name,category_en_name,a.*,b.menu_pic as pic,d.pic as coupon_pic ,e.displayName,e.businessName FROM `cc_wj_user_temp_carts` a left join `cc_restaurant_menu` b on b.id=a.`menu_id` left join `cc_restaurant_category` c on c.id=b.restaurant_category_id left join cc_coupons d on d.id=a.main_coupon_id   left join cc_user e on e.id=a.businessUserId where a.userId=".$userId."   and businessUserId in $suppliers_list order by businessUserId,category_sort_id,b.menu_id";
            $cartItems = $mdl_wj_user_temp_carts->getListBySql($sql);
          //  var_dump($cartItems);exit;
			
            // 对临时购物车的记录进行校验 ，比如当前购物车的产品已经下线，或者当前购物车的产品价格已经更新，或者当前购物车里的产品库存超过库房库存 。
            $mdl_restaurant_menu = $this->loadModel('restaurant_menu');

            $mdl_user_factory_menu_price = $this->loadModel('user_factory_menu_price');
            $userFactoryMenuPrices = $mdl_user_factory_menu_price->getUserFactoryPriceList($userId, $id);
            foreach ($cartItems as $key => $val) {
                $menu_rec = $mdl_restaurant_menu->get($val['menu_id']);
                if (! $menu_rec) {
                    $mdl_wj_user_temp_carts->delete($val['id']); // 如果菜单中没有这个，那么从临时购物车中删除。
                    continue;
                } else {

                    // 如果找到菜单中的项目，检查是否已经下线，如果下线，则删除

                    if (! $menu_rec['visible']) {
                        $mdl_wj_user_temp_carts->delete($val['id']);
                    }

                    ///如果是特价产品
                    if ($val['onSpecial']) {

                        if ($menu_rec['speical_price'] != $val['single_amount']) {


                            $new_item_price_data = [
                                'single_amount' => $menu_rec['speical_price'],
                            ];
                            $mdl_wj_user_temp_carts->update($new_item_price_data, $val['id']);
                        }
                    } else {

                        // 如果在菜单中找到该产品，检查价格是否和菜单中描述一致，不一致，则更新
                        if ($menu_rec['price'] != $val['single_amount']) {


                            $new_item_price_data = [
                                'single_amount' => $menu_rec['price'],
                            ];
                            $mdl_wj_user_temp_carts->update($new_item_price_data, $val['id']);
                        }
                    }

                    // 如果在菜单中找到该产品，检查库存是否溢出，如果溢出，则把库存调整到当前最大值

                    if ($menu_rec['qty'] < $val['quantity']) {
                        $new_item_quantity_data = [
                            'quantity' => $menu_rec['qty'],
                        ];
                        $mdl_wj_user_temp_carts->update($new_item_quantity_data, $val['id']);
                    }

                    $show_origin_price = $this->loadModel('user_factory')->getByWhere([
                        'user_id' => $userId,
                        'factory_id' => $id
                    ])['show_origin_price'];

                    if(!$show_origin_price) {
                        $mdl_wj_user_temp_carts->update([
                            'single_amount' => 0,
                        ], $val['id']);
                    }

                    if (array_key_exists($val['id'], $userFactoryMenuPrices)) {
                        $mdl_wj_user_temp_carts->update([
                            'single_amount' => $userFactoryMenuPrices[$val['id']]['price'],
                        ], $val['id']);
                    }
                }
            }

            /* 获得该用户订单数量,总额 ,同时 生成点击订单按钮的显示数据  */
            $cartItems = $mdl_wj_user_temp_carts->getListBySql($sql);

            $cartTotalPrice = 0;

            $old_category = '0';
            $totalQuantity = 0;
            $old_business_id = 0;

            foreach ($cartItems as $key => $val) {
                $totalQuantity += $val['quantity'];
                if ($val['restaurant_category_id'] !== $old_category) {
                    if (! $val['restaurant_category_id']) { // 没有分类 就是团购套餐

                        $cartItems[$key]['category_cn_name'] = (string) $this->lang->group_buy;
                    }

                    $cartItems[$key]['new_cat'] = 1;
                    if ($old_business_id == 0 || ($cartItems[$key]['businessUserId'] <> $old_business_id)) {
                        if ($cartItems[$key]['displayName']) {
                            $cartItems[$key]['business_name'] = $cartItems[$key]['displayName'];
                        } else {
                            $cartItems[$key]['business_name'] = $cartItems[$key]['businessName'];
                        }
                        $old_business_id = $cartItems[$key]['businessUserId'];
                    }
                } else {
                    $cartItems[$key]['new_cat'] = 0;
                }
                $old_category = $val['restaurant_category_id'];
                if (! $val['restaurant_category_id']) {
                    $cartItems[$key]['pic'] = $val['coupon_pic'];
                }

                $cartTotalPrice += $val['single_amount'] * $val['quantity'];
            }

            $this->setData($cartTotalPrice, 'totalPrice');
            $this->setData($totalQuantity, 'totalQuantity');

            $this->setData($cartItems, 'items');

            $html = $this->fetch('/factorypage/ajax_cart');
			//var_dump($html);exit;
        }

        // init page 的内容
        $data = [];

        $data['html'] = $html;
        $data['totalPrice'] = $cartTotalPrice;
        $data['totalQuantity'] = $totalQuantity;

        echo json_encode($data);

        //return;
    }

    public function get_menu_list_action()
    {
        $id = (int) get2('id');
        $userId = (int) get2('user_id');

        $page = (int) get2('page');
        $default_menu_page_items = 10;

        $where = [
            'createUserId' => $id,
            'EvoucherOrrealproduct' => 'restaurant_menu',
        ];

        $mdl_coupons = $this->loadModel("coupons");
        $restaurant_coupon = $mdl_coupons->getByWhere($where);
        $this->setData($restaurant_coupon['id'], 'restaurant_couponID');

        $where1 = [
            'restaurant_id' => $id,
        ];

        $mdl_restaurant_promotion_manjian = $this->loadModel("restaurant_promotion_manjian");
        $restaurant_promotion_manjian = $mdl_restaurant_promotion_manjian->getByWhere($where1);

        $show_origin_price = $this->loadModel('user_factory')->getByWhere([
            'user_id' => $userId,
            'factory_id' => $id
        ])['show_origin_price'];
        $menu = self::get_menu_list($id, $restaurant_coupon, $restaurant_promotion_manjian, $userId);
        $this->setData($show_origin_price, 'show_origin_price');

        $this->setData(array_slice($menu, $default_menu_page_items * $page, $default_menu_page_items), 'menu');
        $this->setData(1, 'lazyload');

        echo $this->fetch('mobile/factorypage/menu');

        return;
    }

    public function get_menu_list($id, $restaurant_coupon, $restaurant_promotion_manjian, $userId = null)
    {
        $mdl_restaurant_menu = $this->loadModel('restaurant_menu');

        if ($restaurant_promotion_manjian) {
            $restaurant_promotion_manjian_rates = $restaurant_promotion_manjian['discount'] / 100;
        } else {
            $restaurant_promotion_manjian_rates = 0;
        }

        if (! $this->loginUser) {
            $sql = "select c.category_cn_name,c.category_en_name,a.*,b.category_id as restaurant_category_id from cc_restaurant_category c,cc_restaurant_menu a, cc_restaurant_category b where (a.restaurant_id=".$id."  or a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id." )) and a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id." ) and (length(a.menu_cn_name)>0 or length(a.menu_en_name)>0) and  c.id=a.restaurant_category_id  and b.id = a.restaurant_category_id order by b.category_sort_id,menu_order_id,menu_id";
            $menu = $mdl_restaurant_menu->getListBySql($sql);

            $sql_special = "select '本期特价' as category_cn_name, 'On Sale' as category_en_name,a.*,'1000' as  restaurant_category_id,'1' as category_sort_id from cc_restaurant_category c,cc_restaurant_menu a, cc_restaurant_category b where (a.restaurant_id=".$id."  or a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id." )) and a.onSpecial =1 and  a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id.")   and (length(a.menu_cn_name)>0 or length(a.menu_en_name)>0) and  c.id=a.restaurant_category_id  and b.id = a.restaurant_category_id and a.visible=1 order by a.menu_order_id,a.menu_id";
            $menu_sub = $mdl_restaurant_menu->getListBySql($sql_special);
            foreach ($menu_sub as $key => $value) {
                $menu_sub[$key]['price'] = $value['speical_price'];
                $menu_sub[$key]['onSpecial'] = 1;
                if ($value['original_price'] <= 0) { //如果原价为空

                    $menu_sub[$key]['original_price'] = $value['price'];
                }
                if ($value['limit_buy_qty'] > 0) {
                    $menu_sub[$key]['menu_cn_name'] = '('.$this->lang->limit_buy.' '.$value['limit_buy_qty'].' '.$this->lang->original_price.$menu_sub[$key]['original_price'].')'.$menu_sub[$key]['menu_cn_name'];
                } else {
                    $menu_sub[$key]['menu_cn_name'] = '('.$this->lang->original_price.$menu_sub[$key]['original_price'].')'.$menu_sub[$key]['menu_cn_name'];
                }
            }

            foreach ($menu as $key => $value) {
                $menu[$key]['onSpecial'] = 0; //之前的on sepcial 都已提出了，下面再常规类别中显示的都可以不视为special
                if ($value['original_price'] <= 0) { //如果原价为空

                    $menu[$key]['original_price'] = $value['price'];
                }
                $menu[$key]['new_price'] = number_format($menu[$key]['price'] * (1 - $restaurant_promotion_manjian_rates), 2);
            }

            $menu = array_merge($menu_sub, $menu);
        }
        else {
            //清除购物车其它产品
            $mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');

            //菜单列表
            $sql = "select c.category_cn_name, c.category_en_name,a.*,b.category_id as restaurant_category_id,b.category_sort_id from cc_restaurant_category c,cc_restaurant_menu a, cc_restaurant_category b where (a.restaurant_id=".$id."  or a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id." )) and  a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id.")   and (length(a.menu_cn_name)>0 or length(a.menu_en_name)>0) and  c.id=a.restaurant_category_id  and b.id = a.restaurant_category_id and a.visible=1 order by b.category_sort_id,a.menu_order_id,a.menu_id";
            $menu = $mdl_restaurant_menu->getListBySql($sql);

            $sql_special = "select '本期特价' as category_cn_name, 'On Sale' as category_en_name,a.*,'1000' as  restaurant_category_id,'1' as category_sort_id from cc_restaurant_category c,cc_restaurant_menu a, cc_restaurant_category b where (a.restaurant_id=".$id."  or a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id." )) and a.onSpecial =1 and  a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id.")   and (length(a.menu_cn_name)>0 or length(a.menu_en_name)>0) and  c.id=a.restaurant_category_id  and b.id = a.restaurant_category_id and a.visible=1 order by a.menu_order_id,a.menu_id";
            $menu_sub = $mdl_restaurant_menu->getListBySql($sql_special);
            foreach ($menu_sub as $key => $value) {
                $menu_sub[$key]['price'] = $value['speical_price'];
                $menu_sub[$key]['onSpecial'] = 1;

                if ($value['original_price'] <= 0) { //如果原价为空

                    $menu_sub[$key]['original_price'] = $value['price'];
                }
                if ($value['limit_buy_qty'] > 0) {
                    $menu_sub[$key]['menu_cn_name'] = '('.$this->lang->limit_buy.' '.$value['limit_buy_qty'].' '.$this->lang->original_price.$menu_sub[$key]['original_price'].')'.$menu_sub[$key]['menu_cn_name'];;
                } else {
                    $menu_sub[$key]['menu_cn_name'] = '('.$this->lang->original_price.$menu_sub[$key]['original_price'].')'.$menu_sub[$key]['menu_cn_name'];;
                }
            }

            $menu = array_merge($menu_sub, $menu);

            foreach ($menu as $key => $value) {
                $menu[$key]['onSpecial'] = 0; //之前的on sepcial 都已提出了，下面再常规类别中显示的都可以不视为special
                if ($value['original_price'] <= 0) { //如果原价为空

                    $menu[$key]['original_price'] = $value['price'];
                }

                if ($this->getLangStr() == 'en') {
                    if (! $menu[$key]['menu_en_name']) {
                        $menu[$key]['menu_en_name'] = $menu[$key]['menu_cn_name'];
                    }
                }

                $menu[$key]['new_price'] = number_format($menu[$key]['price'] * (1 - $restaurant_promotion_manjian_rates), 2);

                //加载购物车已购买数量
                $sql = "select quantity from cc_wj_user_temp_carts where main_coupon_id=".$restaurant_coupon['id']." and menu_id=".$value['id']." and sidedish_menu_id=0 and userId=".$this->loginUser['id'];

                $result = $mdl_wj_user_temp_carts->getListBySql($sql);

                //显示新价格
                $menu[$key]['quantity'] = $result[0]['quantity'];
            }
        }

        if (! $userId) {
            $userId = $this->loginUser['id'];
        }

        $mdl_user_factory_menu_price = $this->loadModel('user_factory_menu_price');
        $userFactoryMenuPrices = $mdl_user_factory_menu_price->getUserFactoryPriceList($userId, $id);

        $show_origin_price = $this->loadModel('user_factory')->getByWhere([
            'user_id' => $userId,
            'factory_id' => $id
        ])['show_origin_price'];



		//加载配菜
			$mdl_sidedish_menu=$this->loadModel('restaurant_sidedish_menu');
			foreach ($menu as $key => $value) {
				if($menu[$key]['sidedish_category']>0){
					$menu[$key]['sidedish_menu']=$mdl_sidedish_menu->getList(null,array('restaurant_id'=>$menu[$key]['restaurant_id'],'restaurant_category_id'=>$menu[$key]['sidedish_category']," (length(menu_cn_name)>0 or length(menu_en_name)>0) "));

					

				}
			}

			//加载菜品规格
			$mdl_menu_option=$this->loadModel('restaurant_menu_option');
			foreach ($menu as $key => $value) {
				if($menu[$key]['menu_option']>0){
					$menu[$key]['menu_option_list']=$mdl_menu_option->getList(null,array('restaurant_id'=>$menu[$key]['restaurant_id'],'restaurant_category_id'=>$menu[$key]['menu_option']," (length(menu_cn_name)>0 or length(menu_en_name)>0) "));

					

				}
			}





        // 换 en cn
        $old_cat = "";
        foreach ($menu as $key => $value) {
            if (! $menu[$key]['category_en_name']) {
                $menu[$key]['category_en_name'] = $menu[$key]['category_cn_name'];
            }

            if (! $menu[$key]['unit_en']) {
                $menu[$key]['unit_en'] = $menu[$key]['unit'];
            }

            if ($this->lang['lang'][0] == 'English') {
                $new_cat = $menu[$key]['category_en_name'];
                $menu[$key]['category_name'] = $menu[$key]['category_en_name'];
                $menu[$key]['unit'] = $menu[$key]['unit_en'];
                $menu[$key]['menu_name'] = $menu[$key]['menu_en_name'];
                $menu[$key]['menu_desc'] = $menu[$key]['menu_en_desc'];
            } else {
                $new_cat = $menu[$key]['category_cn_name'];
                $menu[$key]['category_name'] = $menu[$key]['category_cn_name'];
                $menu[$key]['menu_name'] = $menu[$key]['menu_cn_name'];
            }

            if ($old_cat <> $new_cat) {
                $menu[$key]['new_cat'] = $new_cat;
                $old_cat = $new_cat;
            } else {
                $menu[$key]['new_cat'] = 0;
            }

            if (array_key_exists($value['id'], $userFactoryMenuPrices)) {
                $menu[$key]['new_price'] = $userFactoryMenuPrices[$value['id']]['price'];
            }

            if(!$show_origin_price) {
                $menu[$key]['new_price'] = 0;
            }
        }
//var_dump($menu);exit;
        return $menu;
    }

    public function get_business_info_action()
    {
        $businessUserIdtt = get2('businessUserId');

        $id = (int) get2('id');

        if (! $id) {
            $id = $businessUserIdtt;
            $businessUserId = $businessUserIdtt;
        }
        if (! $id) {
            $this->sheader(null, '请选择正确商家');
        }

        // 获取的餐馆ID--是商家的ID ,判断当前商家是否设置餐厅入口
        $where = [
            'createUserId' => $id,
            'EvoucherOrrealproduct' => 'restaurant_menu',
        ];

        $mdl_coupons = $this->loadModel("coupons");

        $restaurant_coupon = $mdl_coupons->getByWhere($where);

        if (($restaurant_coupon['isApproved'] == 1 && $restaurant_coupon['status'] == 4) || $restaurant_coupon['createUserId'] == $this->loginUser['id'] || $_SESSION['coupon_private_view_allowed'] == $restaurant_coupon['id']) {
            $mdl_user = $this->loadModel("user");
            $business_user = $mdl_user->get($restaurant_coupon['createUserId']);
            $restaurant_coupon['business'] = $business_user;

            $this->setData($restaurant_coupon, 'coupon');

            //获得自己店的其它产品

            $selfProduct = $mdl_coupons->getListBySql("select * from cc_coupons where createUserId =".$restaurant_coupon['createUserId']." and isApproved =1 and status=4 and EvoucherOrrealproduct <> 'restaurant_menu'");

            $this->setData($selfProduct, "selfProduct");

            $this->setData($mdl_coupons->getRecommendProduct($restaurant_coupon['id']), 'recommends');
        }

        $business_info = $this->fetch('/mobile/factorypage/sec_explain');
        echo $business_info;

        return;
    }

    public function restaurant_menu_edit_action()
    {
        $freshfood = get2('freshfood');
        if (! $freshfood) {
            $freshfood = post('freshfood');
        }
        $this->setData($freshfood, 'freshfood');

        $customer_id = get2('customer_id');
        if (! $customer_id) {
            $customer_id = $this->loginUser['id'];
        }
        $this->setData($customer_id, 'customer_id');

        $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);

        $this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');

        if ($authoriseBusinessList) { //如果该商家可以托管账户
            // 检查接收的托管的商家是否合法
            $isAuthoriseCustomer = 0;
            foreach ($authoriseBusinessList as $key => $value) {
                if ($customer_id == $value['customer_id'] || $customer_id == $this->loginUser['id']) {
                    $isAuthoriseCustomer = 1;
                }
            }
            if ($isAuthoriseCustomer) { //如果是授权的customer
                $mdl_restaurant_category = $this->loadModel('restaurant_category');
                $pageSql = "select  * from cc_restaurant_category where createUserId=$customer_id  and (length(category_cn_name)>0 or length(category_en_name)>0) order by category_sort_id ";
                $data = $mdl_restaurant_category->getListBySql($pageSql);

                $this->setData($data, 'restaurant_category');
                $sk = trim(get2('sk'));
                $allOrspecial = trim(get2('allOrspecial'));
                $category = trim(get2('category'));

                $this->setData($sk, 'sk');
                $this->setData($category, 'category');

                $sql = "select  o.* ,b.category_cn_name,b.category_en_name  from cc_restaurant_menu o left join cc_restaurant_category b on b.id=o.restaurant_category_id";

                $whereStr = '';
                if ($category == 'all' or empty($category)) {
                    $whereStr .= " (length(o.menu_cn_name) >0 or length(o.menu_en_name) >0) and  o.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id=$customer_id ) and (o.restaurant_id=$customer_id  or o.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id=$customer_id ) )";
                } else {
                    $whereStr .= " (o.restaurant_id=$customer_id and  o.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id=$customer_id )   or o.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id=$customer_id ) )";
                }

                if (! empty($category) && $category != 'all') {
                    $whereStr .= " and o.restaurant_category_id='$category' ";
                }

                if (! empty($sk)) {
                    $whereStr .= " and (o.menu_cn_name  like  '%".$sk."%'";
                    $whereStr .= " or o.menu_en_name  like  '%".$sk."%'";
                    $whereStr .= " or o.Menu_desc  like  '%".$sk."%')";
                }

                if ($allOrspecial == 'special') {
                    $whereStr .= " and onSpecial =1";
                    $this->setData($allOrspecial, 'allOrspecial');
                } else {
                    $this->setData($allOrspecial, 'all');
                }

                // 提示用户选择菜单分类,如果没有选择菜单分类,则显示当前全部的菜单.
                // 如果选择某一种分类,如果当前没有数据则进行增加50个,如果有数据则直接显示即可.
                $mdl_restaurant_menu = $this->loadModel('restaurant_menu');
                $pageSql = $sql." where ".$whereStr." order by restaurant_category_id,LENGTH(menu_id),menu_id";
                $pageUrl = $this->parseUrl()->set('page');
                $pageSize = 200;
                $maxPage = 10;
                $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
                $data = $mdl_restaurant_menu->getListBySql($page['outSql']);

                // 获得该用户的gst type
                $mdl_user = $this->loadModel("user");
                $customerInfo = $mdl_user->get($customer_id);
				
				// if count of record for cn name and en name empty records is 0 ,still add more items .
                 $sql_for_empty_cn_en_name = $sql." where ".$whereStr. " and length(o.menu_cn_name)=0  and length(o.menu_en_name)=0 ";
				 $empty_record_count = $mdl_restaurant_menu->getListBySql($sql_for_empty_cn_en_name);
				// var_dump($empty_record_count);exit;
                				
                if (! $data  || !$empty_record_count) {
                    if ($category != 'all' && ! empty($category)) {
                        // 增加50个菜单分类
                        $menu_id = 100;
                        for ($i = 0; $i < 100; $i++) {
                            $menu_info = [
                                'createUserId' => $customer_id,
                                'restaurant_id' => $customer_id,
                                'restaurant_category_id' => $category,
                                'menu_id' => $category.$menu_id,
                                'menu_cn_name' => '',
                                'price' => '',
                                'guige_group_id_2' => '',
                                'menu_pic' => '',
                                'Menu_desc' => '',
                                'menu_en_name' => '',
                                'include_gst' => $customerInfo['gst_type'] % 2 //默认gst根据公司gst类型，1，3为全部gst和多数gst，2，4为全部无gst和少数gst
                            ];
                            $mdl_restaurant_menu->insert($menu_info);
                            $menu_id = $menu_id + 1;
                        }

                        $pageSql = "select  * from cc_restaurant_menu where createUserId=$customer_id and restaurant_category_id =".$category." order by id";
                        $pageUrl = $this->parseUrl()->set('page');
                        $pageSize = 200;
                        $maxPage = 10;
                        $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
                        $data = $mdl_restaurant_menu->getListBySql($page['outSql']);
                    }
                }

                //获取该商家是否有多个供应商，是否为集合店
                $this->loadModel('freshfood_disp_suppliers_schedule');
                $suppliersList = DispCenter::getSupplierListWithName($customer_id);

                $this->setData($suppliersList, 'suppliersList');
                $this->setData($data, 'data');
                $this->setData($page['pageStr'], 'pager');
                $this->setData($this->parseUrl()->setPath('restaurant/restaurant_edit'), 'editUrl');

                /**
                 * 获得配菜分类列表
                 */
                $where = [];
                $where[] = "(length(category_cn_name) >0 or length(category_en_name) >0)";
                $where['restaurant_id'] = $customer_id;
                $restaurant_sidedish_category_list = $this->loadModel('restaurant_sidedish_category')->getList(null, $where);
                $this->setData($restaurant_sidedish_category_list, 'sidedish_category_list');
                /**
                 * 获得配菜分类列表
                 */
                $where = [];
                $where[] = "(length(category_cn_name) >0 or length(category_en_name) >0)";
                $where['restaurant_id'] = $customer_id;
                $restaurant_menu_option_list = $this->loadModel('restaurant_menu_option_category')->getList(null, $where);
                $this->setData($restaurant_menu_option_list, 'menu_option_list');
            }
        } else { //如果只管理自己的店铺
            $mdl_restaurant_category = $this->loadModel('restaurant_category');
            $pageSql = "select  * from cc_restaurant_category where createUserId=".$this->loginUser['id']." and (length(category_cn_name)>0 or length(category_en_name)>0) order by category_sort_id ";
            $data = $mdl_restaurant_category->getListBySql($pageSql);

            if (! $data) {
                $this->sheader(null, '您需要首先定义餐厅的菜单分类,然后才可以定义菜品....');
            }
            $this->setData($data, 'restaurant_category');

            $sk = trim(get2('sk'));
            $allOrspecial = trim(get2('allOrspecial'));
            $category = trim(get2('category'));

            $this->setData($sk, 'sk');
            $this->setData($category, 'category');

            $sql = "select  o.* ,b.category_cn_name,b.category_en_name  from cc_restaurant_menu o left join cc_restaurant_category b on b.id=o.restaurant_category_id";
            $whereStr = '';
            if ($category == 'all' or empty($category)) {
                $whereStr .= " (length(o.menu_cn_name) >0 or length(o.menu_en_name) >0) and  o.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id= ".$this->loginUser['id']." ) and (o.restaurant_id= ".$this->loginUser['id']."  or o.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id= ".$this->loginUser['id']." ) )";
            } else {
                $whereStr .= " (o.restaurant_id= ".$this->loginUser['id']." and  o.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id= ".$this->loginUser['id']." )   or o.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id= ".$this->loginUser['id']." ) )";
            }

            if (! empty($category)) {
                if ($category != 'all') {
                    $whereStr .= " and o.restaurant_category_id='$category' ";
                }
            }
            if (! empty($sk)) {
                $whereStr .= " and (o.menu_cn_name  like  '%".$sk."%'";
                $whereStr .= " or o.menu_en_name  like  '%".$sk."%'";
                $whereStr .= " or o.Menu_desc  like  '%".$sk."%')";
            }

            if ($allOrspecial == 'special') {
                $whereStr .= " and onSpecial =1";
                $this->setData($allOrspecial, 'allOrspecial');
            } else {
                $this->setData($allOrspecial, 'all');
            }

            // 提示用户选择菜单分类,如果没有选择菜单分类,则显示当前全部的菜单.
            // 如果选择某一种分类,如果当前没有数据则进行增加50个,如果有数据则直接显示即可.
            $mdl_restaurant_menu = $this->loadModel('restaurant_menu');
            $pageSql = $sql." where ".$whereStr." order by restaurant_category_id,LENGTH(menu_id),menu_id";
            $pageUrl = $this->parseUrl()->set('page');
            $pageSize = 200;
            $maxPage = 10;
            $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
            $data = $mdl_restaurant_menu->getListBySql($page['outSql']);

            if (! $data) {
                if ($category != 'all' && ! empty($category)) {
                    $menu_id = 100;
                    for ($i = 0; $i < 100; $i++) {
                        $menu_info = [
                            'createUserId' => $this->loginUser['id'],
                            'restaurant_id' => $this->loginUser['id'],
                            'restaurant_category_id' => $category,
                            'menu_id' => $category.$menu_id,
                            'menu_cn_name' => '',
                            'price' => '',
                            'guige_group_id_2' => '',
                            'menu_pic' => '',
                            'Menu_desc' => '',
                            'menu_en_name' => '',
                            'include_gst' => $this->loginUser['gst_type'] % 2 //默认gst根据公司gst类型，1，3为全部gst和多数gst，2，4为全部无gst和少数gst
                        ];
                        $mdl_restaurant_menu->insert($menu_info);
                        $menu_id = $menu_id + 1;
                    }

                    $pageSql = "select  * from cc_restaurant_menu where createUserId=".$this->loginUser['id']." and restaurant_category_id =".$category." order by menu_id";
                    $pageUrl = $this->parseUrl()->set('page');
                    $pageSize = 200;
                    $maxPage = 10;
                    $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
                    $data = $mdl_restaurant_menu->getListBySql($page['outSql']);
                }
            }

            //获取该商家是否有多个供应商，是否为集合店
            $this->loadModel('freshfood_disp_suppliers_schedule');
            $suppliersList = DispCenter::getSupplierListWithName($this->loginUser['id']);

            $this->setData($suppliersList, 'suppliersList');
            $this->setData($data, 'data');
            $this->setData($page['pageStr'], 'pager');
            $this->setData($this->parseUrl()->setPath('restaurant/restaurant_edit'), 'editUrl');

            /**
             * 获得配菜分类列表
             */
            $where = [];
            $where[] = "(length(category_cn_name) >0 or length(category_en_name) >0)";
            $where['restaurant_id'] = $this->loginUser['id'];
            $restaurant_sidedish_category_list = $this->loadModel('restaurant_sidedish_category')->getList(null, $where);
            $this->setData($restaurant_sidedish_category_list, 'sidedish_category_list');
            /**
             * 获得配菜分类列表
             */
            $where = [];
            $where[] = "(length(category_cn_name) >0 or length(category_en_name) >0)";
            $where['restaurant_id'] = $this->loginUser['id'];
            $restaurant_menu_option_list = $this->loadModel('restaurant_menu_option_category')->getList(null, $where);
            $this->setData($restaurant_menu_option_list, 'menu_option_list');
        }

        $this->setData('restaurant_menu', 'submenu_top');
        $this->setData('restaurant_menu_edit', 'submenu');
        $this->setData('index_publish', 'menu');

        $pagename = "店铺单品管理";
        $pageTitle = $pagename." - 商家中心 - ".$this->site['pageTitle'];

        $this->setData($pagename, 'pagename');

        $this->setData($pageTitle, 'pageTitle');

        $this->setData($this->loginUser['gst_type'], 'gstType');
        $this->display_pc_mobile('restaurant/menu_edit', 'restaurant/menu_edit');
    }

    public function restaurant_edit_action()
    {


        $freshfood = get2('freshfood');
        $this->setData($freshfood, 'freshfood');

        //检查该商家是否可以管理其它店铺，如果授权即可以该商家权限进入系统。

        $mdl = $this->loadModel('authrise_manage_other_business_account');
        $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);

        $this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');

        if ($authoriseBusinessList) { //如果该商家可以托管账户
            // 检查接收的托管的商家是否合法

            $customer_id = get2('customer_id');

            if (! $customer_id) {
                $customer_id = $this->loginUser['id'];
            }
            $this->setData($customer_id, 'customer_id');

            $isAuthoriseCustomer = 0;
            foreach ($authoriseBusinessList as $key => $value) {
                if ($customer_id == $value['customer_id'] || $customer_id == $this->loginUser['id']) {
                    $isAuthoriseCustomer = 1;
                }
            }

            if ($isAuthoriseCustomer) {

                $mdl_restaurant_category = $this->loadModel('restaurant_category');
                $exist = $mdl_restaurant_category->getByWhere(['createUserId' => $customer_id]);

                if (! $exist) {
                    $category_id = 100;
                    $category_sort_id = 10;

                    for ($i = 0; $i < 50; $i++) {
                        $menu_category_info = [
                            'category_cn_name' => '',
                            'category_en_name' => '',
                            'restaurant_id' => $customer_id,
                            'category_id' => $category_id,
                            'category_sort_id' => $category_sort_id,
                            'createUserId' => $customer_id,
                        ];
                        $mdl_restaurant_category->insert($menu_category_info);
                        $category_id = $category_id + 100;
                        $category_sort_id = $category_sort_id + 10;
                    }
                }

                $pageSql = "select  * from cc_restaurant_category where restaurant_id=".$customer_id." order by category_sort_id ";
                //var_dump($pageSql);exit;
                $pageUrl = $this->parseUrl()->set('page');
                $pageSize = 50;
                $maxPage = 10;
                $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
                $data = $mdl_restaurant_category->getListBySql($page['outSql']);

                $this->setData($data, 'data');
                $this->setData($page['pageStr'], 'pager');
                $this->setData($this->parseUrl()->setPath('restaurant/restaurant_edit'), 'editUrl');
            }
        } else { //直接按照之前的方式走

            $mdl_restaurant_category = $this->loadModel('restaurant_category');
            $exist = $mdl_restaurant_category->getByWhere(['createUserId' => $this->loginUser['id']]);

            if (! $exist) {
                $category_id = 100;
                $category_sort_id = 10;

                for ($i = 0; $i < 50; $i++) {
                    $menu_category_info = [
                        'category_cn_name' => '',
                        'category_en_name' => '',
                        'restaurant_id' => $this->loginUser['id'],
                        'category_id' => $category_id,
                        'category_sort_id' => $category_sort_id,
                        'createUserId' => $this->loginUser['id'],
                    ];
                    $mdl_restaurant_category->insert($menu_category_info);
                    $category_id = $category_id + 100;
                    $category_sort_id = $category_sort_id + 10;
                }
            }

            $pageSql = "select  * from cc_restaurant_category where createUserId=".$this->loginUser['id']." order by category_sort_id ";
            $pageUrl = $this->parseUrl()->set('page');
            $pageSize = 50;
            $maxPage = 10;
            $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
            $data = $mdl_restaurant_category->getListBySql($page['outSql']);

            $this->setData($data, 'data');
            $this->setData($page['pageStr'], 'pager');
            $this->setData($this->parseUrl()->setPath('restaurant/restaurant_edit'), 'editUrl');
        }

        $this->setData('restaurant_edit', 'submenu_top');
        $this->setData('restaurant_edit', 'submenu');
        $this->setData('index_publish', 'menu');

        $pagename = "店铺品类管理";
        $pageTitle = $pagename." - 商家中心 - ".$this->site['pageTitle'];

        $this->setData($pagename, 'pagename');
        $this->setData($pageTitle, 'pageTitle');

        $this->setData($this->loginUser['gst_type'], 'gstType');
        $this->display_pc_mobile('factory/edit', 'factory/edit');
    }

    public function user_link_login_action()
    {
        $userId = get2('user_id');
        $factoryId = get2('factory_id');
        $token = get2('token');

        $mdl_user_factory = $this->loadModel('user_factory');

        $loginData = $mdl_user_factory->decryptUserLoginToken($userId, $factoryId, $token);

        if ($loginData->expired_at > time() && $mdl_user_factory->isUserApproved($userId, $loginData->factory_id)) {
            $mdl_user = $this->loadModel('user');
            $user = $mdl_user->getUserById($userId);
            $mdl_user->updateUserById([
                'lastLoginIP' => ip(),
                'lastLoginDate' => time(),
                'loginCount' => $user['loginCount'] + 1,
            ], $userId);

            $this->session('member_user_id', $userId);
            $this->session('member_user_shell', $this->md5($userId.$user['name'].$user['password']));

            $this->sheader(HTTP_ROOT_WWW.'factorypage/'.$factoryId);
        } else {
            print_r('Link has expired or user did not be approved');
            die();
        }
    }
	
	

    public function update_cart_action()
    {
        $userId = (int) get2('user_id');

        if (! $userId) {
            if (! $this->loginUser['id']) {
                $this->form_response_msg(['code' => 0, 'error' => 'Need Login']);

                return;
            }
            $userId = $this->loginUser['id'];
        }
        $mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');
        $mdl_restaurant_menu = $this->loadModel('restaurant_menu');
        $mdl_coupons = $this->loadModel('coupons');

        $quantity = post('quantity');
        $main_coupon_id = post('main_coupon_id');
        $menu_id = post('menu_id');
        $onSpecial = post('onspecial');
        $coupon = $mdl_coupons->get($main_coupon_id);
        // 检查当前产品是否new_price已经下架，检查当前产品库存是否充足

        $current_menu_rec = $mdl_restaurant_menu->get($menu_id);
        if (! $current_menu_rec['visible']) {
            $this->form_response_msg(['code' => 1, 'error' => (string) $this->lang->unable_to_buy]);
        }
        //检查当前产品产品库存是否不足
        //以下代码判断，当前商家是否为销售渠道（厂商下面的客户）

        $mdl = $this->loadModel('factory2c_list');
        if (Factory2c_centre::getIfCurrentUserIsSalesChannal($current_menu_rec['restaurant_id'])) {

            $factory_menu_rec = $mdl_restaurant_menu->get($current_menu_rec['source_menu_id']);
            if ($quantity > $factory_menu_rec['qty']) {

                $this->form_response_msg(['code' => 2, 'error' => (string) $this->lang->alert_no_enough_stocks]);  //表示购物车库存大于当前库存，不能选了。
            }
            if ($quantity > $factory_menu_rec['limit_buy_qty'] && $factory_menu_rec['limit_buy_qty'] > 0 && $onSpecial) {

                $this->form_response_msg(['code' => 3, 'error' => (string) $this->lang->over_buy_limit]);  //表示购物车库存大于限购库存。
            }
        } else {
            if ($quantity > $current_menu_rec['qty']) {

                $this->form_response_msg(['code' => 2, 'error' => 'oh '.(string) $this->lang->alert_no_enough_stocks]);  //表示购物车库存大于当前库存，不能选了。
            }
            if ($quantity > $current_menu_rec['limit_buy_qty'] && $current_menu_rec['limit_buy_qty'] > 0 && $onSpecial) {

                $this->form_response_msg(['code' => 3, 'error' => 'oh '.(string) $this->lang->over_buy_limit]);  //表示购物车库存大于限购库存。
            }
        }

        $process = new AddCartProcess();

        $process->owner($userId);

        $process->qty($quantity, 'update')->addMenu($main_coupon_id, $menu_id, $this->getLangStr(), $onSpecial, (int) get2('factory_id'));

        //如果主菜为0;删除主菜并删除所有配菜;
        if ($quantity == 0) {
            $where['main_coupon_id'] = $main_coupon_id;
            $where['menu_id'] = $menu_id;
            $where['userId'] = $userId;
            $mdl_wj_user_temp_carts->deleteByWhere($where);
        }

        // 获取该商家关联的配送中心所有商家列表，并将用户在这些相关的商家列表中的购买清单全部列出来

        $suppliers_list = $this->get_same_dispatching_centre_suppliers_list($coupon['createUserId']);

        $sql = "SELECT b.restaurant_category_id,category_sort_id,category_cn_name,category_en_name,a.*,b.menu_pic as pic,d.pic as coupon_pic ,e.displayName,e.businessName FROM `cc_wj_user_temp_carts` a left join `cc_restaurant_menu` b on b.id=a.`menu_id` left join `cc_restaurant_category` c on c.id=b.restaurant_category_id left join cc_coupons d on d.id=a.main_coupon_id   left join cc_user e on e.id=a.businessUserId where a.userId=".$userId."   and businessUserId in $suppliers_list order by businessUserId,category_sort_id,b.menu_id";
        $cartItems = $mdl_wj_user_temp_carts->getListBySql($sql);

        // 对临时购物车的记录进行校验 ，比如当前购物车的产品已经下线，或者当前购物车的产品价格已经更新，或者当前购物车里的产品库存超过库房库存 。

        $cartTotalPrice = 0;

        $totalMenuPrice = 0;
        $totalVoucherPrice = 0;
        $old_category = '0';
        $totalQuantity = 0;
        $old_business_id = 0;


        $show_origin_price = $this->loadModel('user_factory')->getByWhere([
            'user_id' => $userId,
            'factory_id' => (int) get2('factory_id')
        ])['show_origin_price'];

        foreach ($cartItems as $key => $val) {
            $totalQuantity += $val['quantity'];
            if ($val['restaurant_category_id'] !== $old_category) {
                if (! $val['restaurant_category_id']) { // 没有分类 就是团购套餐

                    $cartItems[$key]['category_cn_name'] = (string) $this->lang->group_buy;
                }

                $cartItems[$key]['new_cat'] = 1;
                if ($old_business_id == 0 || ($cartItems[$key]['businessUserId'] <> $old_business_id)) {
                    if ($cartItems[$key]['displayName']) {
                        $cartItems[$key]['business_name'] = $cartItems[$key]['displayName'];
                    } else {
                        $cartItems[$key]['business_name'] = $cartItems[$key]['businessName'];
                    }
                    $old_business_id = $cartItems[$key]['businessUserId'];
                }
            } else {
                $cartItems[$key]['new_cat'] = 0;
            }
            $old_category = $val['restaurant_category_id'];
            if (! $val['restaurant_category_id']) {
                $cartItems[$key]['pic'] = $val['coupon_pic'];
            }

            if(!$show_origin_price) {
                $cartItems[$key]['single_amount'] = 0;
                continue;
            }
            if ($val['main_coupon_id'] == $main_coupon_id) {

                $totalMenuPrice += $val['single_amount'] * $val['quantity'];
            } else {

                $totalVoucherPrice += $val['single_amount'] * $val['quantity'];
            }

            $cartTotalPrice += $val['single_amount'] * $val['quantity'];
        }

        $this->setData($cartTotalPrice, 'totalPrice');
        $this->setData($totalQuantity, 'totalQuantity');

        $this->setData($cartItems, 'items');

        $html = $this->fetch('/factorypage/ajax_cart');

        $data['totalPrice'] = $cartTotalPrice;
        $data['totalQuantity'] = $totalQuantity;
        $data['totalMenuPrice'] = $totalMenuPrice;
        $data['totalVoucherPrice'] = $totalVoucherPrice;
        $data['html'] = $html;
        echo json_encode($data);
    }

    public function empty_cart_action()
    {
        $userId = (int) get2('user_id');
        if (! $userId) {
            $userId = $this->loginUser['id'];
        }
        $this->loadModel('wj_user_temp_carts')->clearTempCart($userId);
    }

    public function add_cart_item_action()
    {
        $id = get2('id');
        $temp = $this->loadModel('wj_user_temp_carts');
        $record = $temp->get($id);
        if ($record) {
            $quantity = $record['quantity'] + 1;
            // 获得当前库存是否大于产品中的库存，如果大于则显示，库存以达最大值
            $menu_rec = $this->loadModel('restaurant_menu')->get($record['menu_id']);

            if ($menu_rec['qty'] < $quantity) {
                $result = 1;
                echo $result;
                return;
            } else {
                if ($quantity > $menu_rec['limit_buy_qty'] && $menu_rec['limit_buy_qty'] > 0) {
                    if ($record['onSpecial']) {
                        $result = 2;
                        echo $result;
                        return;
                    }
                }

                $data = [
                    'quantity' => $quantity,
                ];

                if (! $temp->update($data, $id)) {
                    echo('修改不成功');
                }

            }
        } else {
            echo('未找到记录!');
            return;
        }
    }

    public function min_cart_item_action()
    {
        $id = get2('id');
        $temp = $this->loadModel('wj_user_temp_carts');
        $record = $temp->get($id);

        if ($record) {
            $quantity = $record['quantity'] - 1;
            if ($quantity == 0) {
                $quantity = 1;
            }
            $data = [
                'quantity' => $quantity,
            ];
            if (! $temp->update($data, $id)) {
                echo('修改不成功');
            }
        } else {
            echo('未找到记录!');
        }
    }

    public function update_carts_with_result_back_action()
    {
        $data = trim(post('data'));

        $businessUserId = post('businessUserId');

        $data = stripslashes($data);

        $list = json_decode($data, true);

        $mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');

        try {

            $mdl_wj_user_temp_carts->begin();

            foreach ($list as $key => $value) {
                $data = [
                    'quantity' => $value,
                ];

                $show_origin_price = $this->loadModel('user_factory')->getByWhere([
                    'user_id' => trim(post('userId')),
                    'factory_id' => $this->loginUser['id']
                ])['show_origin_price'];

                if(!$show_origin_price) {
                    $data['single_amount'] = 0;
                }

                $mdl_user_factory_menu_price = $this->loadModel('user_factory_menu_price');
                $userFactoryMenuPrices = $mdl_user_factory_menu_price->getUserFactoryPriceList(trim(post('userId')), $this->loginUser['id']);
                if (array_key_exists($value['id'], $userFactoryMenuPrices)) {
                    $data['single_amount'] = $userFactoryMenuPrices[$value['id']]['price'];
                }

                $mdl_wj_user_temp_carts->update($data, $key);
            }

            $mdl_wj_user_temp_carts->commit();
        } catch (Exception $e) {

            $mdl_wj_user_temp_carts->rollback();
        }

        $cartItems = $mdl_wj_user_temp_carts->getDetailedItem($this->loginUser['id'], $businessUserId);
        $totalQuantity = 0;
        $totalMenuPrice = 0;
        $totalVoucherPrice = 0;

        foreach ($cartItems[0]['items'] as $item) {
            $totalQuantity += $item['quantity'];

            if ($item['EvoucherOrrealproduct'] == 'restaurant_menu') {
                $totalMenuPrice += $item['quantity'] * $item['single_amount'];
            } else {
                $totalVoucherPrice += $item['quantity'] * $item['single_amount'];
            }
        }

        $result['totalQuantity'] = $totalQuantity;
        $result['totalMenuPrice'] = $totalMenuPrice;
        $result['totalVoucherPrice'] = $totalVoucherPrice;
        echo json_encode($result);
    }

    public function remove_cart_item_action(){
        $id = get2('id');
        $temp = $this->loadModel('wj_user_temp_carts');
        $record =$temp->get($id);

        if($record) {
            $temp->delete($id);
        }
    }


 public function totalsales_commissions_action($dataFomOtherMethod = [])
    {
        if($dataFomOtherMethod['file_path'] && $dataFomOtherMethod['business_id']) {
            $filePath = $dataFomOtherMethod['file_path'];
            $this->loginUser['id'] = $dataFomOtherMethod['business_id'];
        }

        $mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');
        $mdl_order = $this->loadModel('order');
        $mdl_user= $this->loadModel('user');


		/**
         * sales Lists
         */
        $where_sales = "((role = 20 and user_belong_to_user =".$this->loginUser['id'].") or id = ".$this->loginUser['id'].")";
        $listsales = $mdl_user->getList(null, $where_sales, 'createdDate asc');

		foreach ($listsales as $key => $value) {
			if( $listsales[$key]['contactPersonNickName']) {
				 $listsales[$key]['displayName']=$value['contactPersonNickName'];
			}else{
				 $listsales[$key]['displayName']=$mdl_user->getBusinessDisplayName($value['id']);
			}
			
			
          
        }
        $this->setData($listsales, 'listsales');
		
		//var_dump($listsales);exit;

        /**
         * staff List
         */
        $where_staff = "((role = 5 and user_belong_to_user =".$this->loginUser['id'].") or id = ".$this->loginUser['id'].")";
        $list = $mdl_user->getList(null, $where_staff, 'createdDate asc');
		
		
        foreach ($list as $key => $value) {
			if( $list[$key]['contactPersonNickName']) {
				 $list[$key]['displayName']=$value['contactPersonNickName'];
			}else{
				 $list[$key]['displayName']=$mdl_user->getBusinessDisplayName($value['id']);
			}
			
			
          
        }
        $this->setData($list, 'staff_list');
        // var_dump($list);exit;
        /**
         * status List
         */

        /**
         * payment Type List
         */

         /**
         * customer_delivery_option Type List
         */
       
       
	    // 获得当前用户管理的商家列表信息
		// 该商家列表可能包括：
		// 如果该商家是集卖店的总店账户，可以看到集卖店所有的商家和自己
		// 如果该商家是统配中心的管理者，那么他可以看到统配中心下面所有商家的信息，如果某一个商家是工厂总店，那可以看到2c 或 2b的商家列表
		// 如果该用户是工厂总店，那么可以看到下面的2c客户的列表
		// 如果该用户是工厂总店，那么可以看到下面2b的客户列表
		
		
	   
	   
	   
	    $currentSaleID = trim(get2('currentSaleID'));
	//var_dump($currentSaleID);exit;
	  
	    $this->setData($currentSaleID, 'currentSaleID');

		$groupbysales = trim(get2('groupbysales'));

        //var_dump($groupbysales);exit;


		$selecedperiod = trim(get2('selecedperiod'));
		$this->setData($selecedperiod, 'selecedperiod');



      
		$st=trim(get2('startTime'));
        $et=trim(get2('endTime'));
        $payment=trim(get2('payment'));
       

      
        $this->setData($st,'st');
        $this->setData($et,'et');
      
		
		
		//$sql = "select "
		
		
       // var_dump($currentBusinessId);exit;
		if( $currentBusinessId && $currentBusinessId !='all') {
		   
		       $sql = "SELECT cust.displayName,o.* ,cust.ori_sum from cc_order as o left join (select order_id,business_id,sum(voucher_deal_amount*customer_buying_quantity) as ori_sum ,uu. displayName from cc_wj_customer_coupon tt , cc_user uu where tt.business_id =uu.id  group by order_id,business_id) cust on o.orderId=cust.order_id and cust.business_id =".$currentBusinessId." left join cc_wj_user_coupon_activity_log as l on o.orderId=l.orderId and o.coupon_status=l.action_id ";
 
		}else{
		     $sql = "SELECT cust.displayName,o.* ,cust.ori_sum from cc_order as o left join (select order_id,business_id,sum(voucher_deal_amount*customer_buying_quantity) as ori_sum ,uu. displayName from cc_wj_customer_coupon tt , cc_user uu where tt.business_id =uu.id  group by order_id,business_id) cust on o.orderId=cust.order_id and cust.business_id =".$this->loginUser['id']." left join cc_wj_user_coupon_activity_log as l on o.orderId=l.orderId and o.coupon_status=l.action_id ";

		
		}
   
      //  $whereStr.=" (business_userId= ".$this->loginUser['id']." or  o.orderId in (select DISTINCT c.order_id from cc_wj_customer_coupon c where business_id = ".$this->loginUser['id']."))";
        //var_dump($sql);exit;
        $whereStr.=" ( business_userId= ".$this->loginUser['id'];
	  $whereStr.="  or  o.orderId in (select DISTINCT c.order_id from cc_wj_customer_coupon c where business_id = ".$this->loginUser['id'].")";
	  //plus 如果该用户是统配中心用户，其下所有商家的订单
	  $whereStr.=" or  business_userId in (select business_id from  cc_dispatching_centre_customer_list where dispatching_centre_id =".$this->loginUser['id'].")";
      //如果该商家是集合店铺所有人，则所有其下店铺的订单
	  $whereStr.=" or  business_userId in (select suppliers_id from  cc_freshfood_disp_centre_suppliers where business_id =".$this->loginUser['id'].")";
	  // 如果该用户为授权用户，则其下所有订单均可以看到。
	  $whereStr.=" or  business_userId in (select customer_id from  cc_authrise_manage_other_business_account where authorise_business_id =".$this->loginUser['id'].")";
      $whereStr.=" or  business_userId in (select customer_id from  	cc_factory2c_list where factroy_id =".$this->loginUser['id'].")";
      $whereStr.=" or  business_userId in (select customer_id from  	cc_factory_2blist where factroy_id =".$this->loginUser['id'].")";

      $whereStr.=")";
    



	  if (!empty($sk)) {
            $whereStr.=" and (o.redeem_code like  '%" . $sk . "%'";
            $whereStr.=" or o.last_name like  '%" . $sk . "%'";
            $whereStr.=" or o.phone like  '%" . $sk . "%'";
            $whereStr.=" or o.orderId like  '%" . $sk . "%'";
            $whereStr.=" or o.order_name like  '%" . $sk . "%'";
            $whereStr.=" or o.tracking_id like  '%" . $sk . "%'";
            $whereStr.=" or o.first_name like  '%" . $sk . "%'";
			$whereStr.=" or o.userId like  '%" . $sk . "%')";
            $where[]=$whereStr;
        }
   
	
		
        if (!empty($payment)) {
            if ($payment != 'all') {
               $whereStr.= " and o.payment='$payment' ";
            }
        }
	
		
	
		
  
     

   

        $pageSql=$sql . " where " . $whereStr . " order by createTime desc";
       // var_dump ($pageSql);exit;
       
            $pageUrl = $this->parseUrl()->set('page');
            $pageSize = 40;
            $maxPage = 10;
            $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);

            $data = $mdl_order->getListBySql($page['outSql']);

       

        $this->setData($page['pageStr'],'pager');

        $this->setData($data,'data');

        $this->setData('salesManagement', 'menu');
        $this->setData('totalsales_commissions', 'submenu');
        
        $this->setData(HTTP_ROOT_WWW.'factorypage/totalsales_commissions', 'searchUrl');

        $this->setData($this->parseUrl(), 'currentUrl');

        $this->setData('销售员销售及回扣查询 - ' . $this->site['pageTitle'], 'pageTitle');

        $this->display_pc_mobile('factorypage/totalsales_commissions','factorypage/totalsales_commissions');
    }
	





    public function checkout_action()
    {
        $userId = trim(post('userId'));
        $business_userid = $this->loginUser['id'];


// 2021-7-17 如果当前登陆用户为销售员，那么找到他的商家ID，并传递该值 。
// 销售员的用户role 为101

  if($this->loginUser['role']==20) {
	  
	  $business_userid = $this->loginUser['user_belong_to_user'];
	  
  }


        $mdl_wj_user_temp_carts = $this->loadModel( 'wj_user_temp_carts' );
        $mdl_user = $this->loadModel('user');

        /**
         * 购物车内容
         */

        $ids = [];
        $sub_ids = [];
        $quantities = [];
        $single_amounts = [];
        $original_amounts = [];
        $commission_frees = [];
        $sub_or_mains = [];
        $menu_ids = [];
        $sidedish_menu_ids = [];
        $coupon_names = [];
        $guige_des = [];
        $guige_ids = [];
        $total = 0;

        $cartItems=$mdl_wj_user_temp_carts->getDetailedItem($userId, $business_userid,$this->getLangStr());
        foreach ($cartItems as $raws) {
            foreach ($raws['items'] as $item) {
                array_push($ids, $item['main_coupon_id']);
                array_push($sub_ids, $item['sub_coupon_id']);
                array_push($quantities, $item['quantity']);
                array_push($single_amounts, $item['single_amount']);
                array_push($original_amounts, $item['original_amount']);
                array_push($commission_frees, $item['commission_free'] && $item['onSpecial']);
                array_push($sub_or_mains, $item['sub_or_main']);
                array_push($menu_ids, $item['menu_id']);
                array_push($sidedish_menu_ids, $item['sidedish_menu_id']);
                array_push($coupon_names, $item['coupon_name']);
                array_push($guige_des, $item['guige_des']);
                array_push($guige_ids, $item['guige_ids']);
                $total += $item['quantity'] * $item['single_amount'];
            }
        }

        $user = $mdl_user->getUserById($userId);

        $delivery_option = 1;
        $payment_option = 'offline';
        $first_name = $user['person_first_name'];
        $last_name = $user['person_last_name'];
        $phone = $user['phone'];
        $address = $user['address'];
        $email  =  $user['email'];

        $house_number  =  $user['addrNumber'];
        $street  =  $user['addrStreet'];
        $postalcode  =  $user['addrPost'];
        $city  =  $user['addrSuburb'];
        $state  =  $user['addrSuburb'];
        $country  =  $user['country'];

        if ( ! $this->loginUser ) {
            $this->form_response_msg($this->lang->log_in_please);
        }

        /**
         * Check Customer Info
         */
        $checkResponseCode = array();
        $checkResponseMsg = array();


        $orderId=date( 'YmdHis' ).$this->createRnd();
        $user_name = trim($user['person_first_name'].' '. $user['person_last_name']);
        $arr_post_yunying =array(
            'orderId'=>$orderId,
            'phone'=>$user['phone'],
            'userId'=>$userId,
            'name'=>$user_name
        );

        // 这里为运营提供第一个数据记录，就是一旦基础的输入信息检测通过，则写入数据库。 这个可能会产生不少信息，比如后面的信息一旦有问题，会重写，但是， 如果这个用户在这天下了单，就不去调查，如果该用户没有下成单，则需要进行追查。
        // 当前已经把用户信息存到表种， 如果一切正常到下面再把订单信息补进来， 如果 这个过程中间断掉了， 可能出现很多问题， 我这样，一会呢， 后面加一个错误号，这样，基础信息过后，就有错误号告诉客服原因。
        // 这个号， 字段上增加一个错误号的处理。
        $this->loadModel('wj_temp_orderID_carts_for_yunying')->save_temp_data($arr_post_yunying,$orderId);;

        $arr_post_yunying =array(
            'orderId'=>$orderId,
            'userId'=>$userId,
            'name'=>$user_name
        );

        if(sizeof($checkResponseCode)>0) {
            //修改方面还有问题。。。。。 还没有调通
            $this->loadModel('wj_temp_orderID_carts_for_yunying')->update_temp_data($arr_post_yunying,join($checkResponseMsg,','),join($checkResponseMsg,','));
            $this->form_response(join($checkResponseCode,'#'),join($checkResponseMsg,','));
        }

        /**
         * Check Item Status
         */
        $quantityLimitReached=false;
        $errorMsg='Opps! ';

        $businessIdList = [];
        $mdl_coupons=$this->loadModel('coupons');

        $arr_quantity = $quantities;
        $coupon_arr = $ids;
        $multi_use = 1;
        for ($i=0; $i <sizeof($coupon_arr) ; $i++) {
            $coupon_id =$coupon_arr[$i];
            $curr_coupon=$mdl_coupons->get($coupon_id);
            if ($this->getLangstr()=='en' && $curr_coupon['title_en']) {
                $curr_coupon['title']=$curr_coupon['title_en'];
            }

            // 如果当前的coupon 是多次使用的产品，则 会将订单置为多次可用。
            if ($curr_coupon['multi_use']>1){
                $multi_use =$curr_coupon['multi_use'];
            }

            $max_quantity =$curr_coupon['perCustomerLimitQuantity'];
            $min_quantity =$curr_coupon['perCustomerMinLimitQuantity'];

            if($max_quantity==0){
                $max_quantity=100000;
            }

            $purchese_qty = $arr_quantity[$i];

            if($purchese_qty>$max_quantity){
                $quantityLimitReached=true;
                $errorMsg.=$this->lang->buy_limit_desc1.$curr_coupon['title'].$this->lang->buy_limit_desc2.$purchese_qty.$this->lang->buy_limit_desc3.$max_quantity.$this->lang->buy_limit_desc4."<br>";
            }

            if($purchese_qty<$min_quantity){
                $quantityLimitReached=true;
                $errorMsg.=(string)$this->lang->buy_limit_desc1.$curr_coupon['title'].$this->lang->buy_limit_desc2.$purchese_qty.$this->lang->buy_limit_desc5.$min_quantity.$this->lang->buy_limit_desc4."<br>";
            }

            $alert = $mdl_coupons->checkIsPublish($curr_coupon);
            if($alert){
                $quantityLimitReached=true;
                $errorMsg.=$this->lang->buy_limit_desc1.$curr_coupon['title'].")".$alert.$this->lang->buy_limit_desc6."<br>";
            }

            $businessIdList[] = $curr_coupon['createUserId'];

            /**
             * same check as query/add_cart
             */
            if($curr_coupon['isInManagement']==1){
                $this->specialEventTimeCheck();
                $this->specialEventTotalQtyLimitCheck($userId,'buy');
            }

            $this->freeProductPurcheseLimitCheck($userId,$curr_coupon['id'],'buy');
        }

        if($quantityLimitReached){
            //修改方面还有问题。。。。。 还没有调通
            $this->loadModel('wj_temp_orderID_carts_for_yunying')->update_temp_data($arr_post_yunying,$errorMsg,$errorMsg);
            $this->form_response_msg($errorMsg);
        }

        $this->loadModel('freshfood_disp_suppliers_schedule');
        /**
         * 合单运费计算。 同地址第二单免运费
         */
        $orderCombineTimeLimit = 2 * 24 * 3600; //2days
        $dispatch_business_id=$this->get_dispatching_centre_businessId($curr_coupon['createUserId']);

        if($address){
            $where['userId']=$this->loginUser['id'];
            $where['business_userId']=$dispatch_business_id;
            $where['coupon_status']='c01';
            $where['customer_delivery_option']='1';
            $where['address']=$address;
            $where[]=" createTime > " .(time() - $orderCombineTimeLimit);

            $result = $this->loadModel('order')->getList(['orderId','createTime'],$where);
            if(sizeof($result)>=1){
                $combind_ordered=1;
            };
        }

        //  如果门牌号重写，则放重写的门牌号码


        $arr_post=array(
            'ids'=>	 $ids,
            'sub_ids'=> $sub_ids,
            'quantities'=> $quantities,
            'sub_money'	=> $single_amounts,
            'original_amount' => $original_amounts,
            'commission_free' => $commission_frees,
            'coupon_names'	=> $coupon_names,
            'money'=> $total,
            'promotion_total' => 0,
            'promotion_id' => null,
            'payment'=> $payment_option,
            'business_userId'=> $dispatch_business_id,
            'business_staff_id' => null,
            'sub_or_main'=> $sub_or_mains,
            'guige_des'=> $guige_des,
            'guige_ids'=> $guige_ids,
            'seat_id'=> null,
            'seat_des'=> null,
            'customer_delivery_option'=>$delivery_option,
            'delivery_fees'=> 0,
            'booking_fees'=> 0,
            'first_name'=>$first_name,
            'last_name'=>$last_name,
            'phone'=> $phone,
            'address'=> $address,

            'house_number' =>$house_number,
            'street' =>$street,
            'city' => $city,
            'state' => $state,
            'country' => $country,
            'postalcode' => $postalcode,

            'email' => $email,
            'id_number' => null,
            'message_to_business' => null,
            'confirmedMoneyAppliedAmount' => 0,
            'surcharge' => 0,
            'specialGroupPinCheckoutUserGroupId' => null,

            'dispCenterUserSelectedDeliveryDate' => $this->cookie->getCookie('DispCenterUserSelectedDeliveryDate'),

            'menu_id'=> $menu_ids,
            'sidedish_menu_id'=> $sidedish_menu_ids,

            'hcashOrderId' => null,
            'hcashOrderTag' => null,
            'hcashRate' => null,
            'hcashAmount' => null,

            'card_number' => null,
            'card_expire_month' => null,
            'card_expire_year' => null,
            'card_security_code' =>null,
        );


        /**
         * Checkout Real Time Quantity
         */
        $msg = '';
        $mdl_restaurant_menu = $this->loadModel('restaurant_menu');

        foreach ( $arr_post['ids'] as $key => $val ) {
            $notEnoughQty=false;

            // 定义变量接受客户的购买数量
            $customer_buy_quantities= $arr_post['quantities'][$key];
            if($customer_buy_quantities==0){continue;}

            $coupon = $this->loadModel('coupons')->get( $arr_post['ids'][$key]);

            //检查如果是子卡，则取子卡数据
            if($arr_post['sub_or_main'][$key]=='s'){
                $subCoupon = $this->loadModel('coupons_sub')->get( $arr_post['sub_ids'][$key] );

                $stock=$subCoupon['quantity'];
                if(!$stock){
                    $stock= 99999;
                }
                $pendingQty=$this->loadModel('wj_temp_orderID_carts')->getPendingQty($arr_post['sub_ids'][$key],'s');
            }else{
                if($this->loadModel('shop_guige')->couponHasGuige($coupon['id'])&&$coupon['bonusType']==9){

                    $guige_ids_array=explode(',', $arr_post['guige_ids'][$key]);
                    $guige1Id=$guige_ids_array[0];
                    $guige2Id=$guige_ids_array[1];
                    if($guige1Id=='null'||$guige1Id==null||$guige1Id==''||$guige1Id<0||$guige1Id=='undefined'){$guige1Id=-1;}
                    if($guige2Id=='null'||$guige2Id==null||$guige2Id==''||$guige2Id<0||$guige2Id=='undefined'){$guige2Id=-1;}

                    $stock=$this->loadModel('shop_stock')->getStock($coupon['id'],$guige1Id,$guige2Id);
                    $pendingQty=$this->loadModel('wj_temp_orderID_carts')->getPendingQty($coupon['id'],'m');
                }else{
                    $stock=$coupon['qty'];
                    $pendingQty=$this->loadModel('wj_temp_orderID_carts')->getPendingQty($arr_post['ids'][$key],'m');
                }
            }

            if($stock-$pendingQty<$customer_buy_quantities)$notEnoughQty=true;

            if($notEnoughQty){
                $msg.=" ".$coupon['title'];
            }

            //检查如果是餐馆产品的购买数量，或是超市
            $msg="";
            if($arr_post['menu_id'][$key]) {
                $menu_rec = $mdl_restaurant_menu->get($arr_post['menu_id'][$key]);
                if($menu_rec['qty']<$arr_post['quantities'][$key]){
                    $msg .=$arr_post['coupon_names'][$key]."数量超过最大库存： ".$menu_rec['qty']." 请调整数量！ ";
                }
            }
        }

        if($msg!=''){
            $this->form_response_msg($msg."哦～");
        }

        /**
         * END
         */

        $arr_post['userId']=$userId;
        $arr_post['orderId']= $orderId;
        if ($multi_use>1) {
            $arr_post['multi_use']= $multi_use;
        }else{
            $arr_post['multi_use']= 1;
        }

        // 将该数据存入临时表中
        if($arr_post['money']==0){
            //如果最终结算金额为0，标定为线下支付。订单将在下一步直接支付成功
            $arr_post['payment']='offline';
        }

        $this->loadModel('wj_temp_orderID_carts')->save_temp_data($arr_post,$orderId);
        $arr_post_yunying['enter_paying_process']='1';
        $arr_post_yunying['arr_post']=$arr_post;
        $payment_type =$arr_post['payment'];
        $this->loadModel('wj_temp_orderID_carts_for_yunying')->update_temp_data($arr_post_yunying,'支付方式:'.$payment_type.(string)$this->lang->submission_success,(string)$this->lang->submission_success);
       
	   $this->form_response(200,(string)$this->lang->submission_success,HTTP_ROOT_WWW.'payment/orderpaymentprocess/pay?status=1&orderId='.$orderId);
    }

    public function get_cart_info_action() {

        $userId = get2('user_id');
        if(!$userId) {
            $userId = $this->loginUser['id'];
        }

        $businessUserId = get2('businessUserId');

        $cartItems=$this->loadModel('wj_user_temp_carts')->getDetailedItem($userId, $businessUserId);
        $totalQuantity=0;
        $totalMenuPrice=0;
        $totalVoucherPrice=0;

        foreach ($cartItems as $key => $value) {

            foreach ($value['items'] as $item) {
                $totalQuantity+=$item['quantity'];
                if($item['EvoucherOrrealproduct']=='restaurant_menu'){
                    $totalMenuPrice+=$item['quantity']*$item['single_amount'];
                }else{
                    $totalVoucherPrice+=$item['quantity']*$item['single_amount'];
                }
            }
        }
        $data['totalQuantity']=$totalQuantity;
        $data['totalMenuPrice']=$totalMenuPrice;
        $data['totalVoucherPrice']=$totalVoucherPrice;
        echo json_encode($data);
        return;
    }
}

?>
