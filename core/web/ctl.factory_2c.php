		<?php

		class ctl_factory_2c extends cmsPage
		{


			function ctl_factory_2c()
			{

				parent::cmsPage();

				$this->setData('factory_2c', 'footer_menu'); //old version mobile
				$this->setData( 'dashboard', 'mobile_menu' ); //new version mobile

				$act = $GLOBALS['gbl_act'];
			   
			   if( $act=='customer_coupon_approving' || $act=='customer_order_detail') {
				  
				}else{
			   
				if (!$this->loginUser) {
					$this->sheader(HTTP_ROOT_WWW . 'member/login?returnUrl=' . urlencode($_SERVER['REQUEST_URI']));
				}

			 
			   }

				
				$this->setData($this->getBusinessType(),'business_type');
				
				
			
				
				
				
			}
			
	public function login_as_customer_action() {
        $userId = trim(get2('user_id'));
        $mdl_user_factory2c = $this->loadModel('factory2c_list');
        $mdl_user = $this->loadModel( 'user' );
        if($mdl_user_factory2c->if2cMainIDCanLogin2bId($userId, $this->loginUser['id'])){
            $user = $mdl_user->getUserById( $userId );
            $data = array(
                'lastLoginIP'	=> ip(),
                'lastLoginDate'	=> time(),
                'loginCount'	=> $user['loginCount'] + 1
            );

            $mdl_user->updateUserById( $data, $user['id'] );

            $this->session( 'member_user_id', $user['id'] );
            $this->session( 'member_user_shell', $this->md5( $user['id'].$user['name'].$user['password'] ) );
			//var_dump($this->loginUser);exit;

            $this->sheader(HTTP_ROOT_WWW . 'company/index');
        }
    }


				
			 function index_action()
			{   
			
				// 获取公司一段时间内的销售额,30天
				$mdl_order = $this->loadModel('order');
				$totalSales = $mdl_order->getTotalSales($this->loginUser['id'], 30);
				$this->setData($totalSales, 'totalSales');

				
				
				// 获取公司账户的余额
				$mdl_recharge = $this->loadModel('recharge');
				$balance = $mdl_recharge->getBalance($this->loginUser['id']);
				$this->setData($balance, 'balance');

				$this->setData($this->loadModel('user')->getBusinessDisplayName($this->loginUser['id']), 'businessDisplayName');

				$this->setData('工厂首页', 'pagename');
				$this->setData('index', 'menu');
				$this->setData('工厂中心 - ' . $this->site['pageTitle'], 'pageTitle');
				$this->display_pc_mobile('company/index', 'mobile/company/index');
			}
				
		/**
		 * 菜单分类编辑页面
		 */

		function restaurant_edit_action(){
			
			
				$freshfood =get2('freshfood');
				$this->setData($freshfood,'freshfood');
			
			//检查该商家是否可以管理其它店铺，如果授权即可以该商家权限进入系统。
			
			 $mdl = $this->loadModel('authrise_manage_other_business_account');
			 $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);
			
			 $this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');
			
			if($authoriseBusinessList) { //如果该商家可以托管账户
				// 检查接收的托管的商家是否合法
				
				$customer_id =get2('customer_id');
				
				if(!$customer_id) {
				  $customer_id =$this->loginUser['id'];
					
				}
				$this->setData($customer_id,'customer_id');
				
				$isAuthoriseCustomer =0 ;
				foreach ($authoriseBusinessList as $key => $value) {
					if($customer_id ==$value['customer_id'] || $customer_id ==$this->loginUser['id']) {
							$isAuthoriseCustomer =1;
					}
					
				}
				
				if ($isAuthoriseCustomer) {
				
					$mdl_restaurant_category = $this->loadModel('restaurant_category');
					$exist = $mdl_restaurant_category->getByWhere(array('createUserId'=>$customer_id));

					if(!$exist){
						$category_id =100;
						$category_sort_id=10;
						
						for($i=0;$i<50;$i++) {
							$menu_category_info=array(
								'category_cn_name'=>'',
								'category_en_name'=>'',
								'restaurant_id'=>$customer_id,
								'category_id'=>$category_id,
								'category_sort_id'=>$category_sort_id,
								'createUserId'=>$customer_id
							);
							$mdl_restaurant_category->insert($menu_category_info);
							$category_id =$category_id+100;
							$category_sort_id =$category_sort_id +10;
						}
					}

					$pageSql = "select  * from cc_restaurant_category where restaurant_id=".$customer_id. " order by category_sort_id ";
					//var_dump($pageSql);exit;
					$pageUrl = $this->parseUrl()->set('page');
					$pageSize =50;
					$maxPage = 10;
					$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
					$data = $mdl_restaurant_category->getListBySql($page['outSql']);
					
					$this->setData($data, 'data');
					$this->setData($page['pageStr'], 'pager');
					$this->setData($this->parseUrl()->setPath('factory_2c/restaurant_edit'), 'editUrl');
					
				}else {
					//do nothing  attack 
					
				}
			
				
			}else{ //直接按照之前的方式走 
			
				$mdl_restaurant_category = $this->loadModel('restaurant_category');
				$exist = $mdl_restaurant_category->getByWhere(array('createUserId'=>$this->loginUser['id']));

				if(!$exist){
					$category_id =100;
					$category_sort_id=10;
					
					for($i=0;$i<50;$i++) {
						$menu_category_info=array(
							'category_cn_name'=>'',
							'category_en_name'=>'',
							'restaurant_id'=>$this->loginUser['id'],
							'category_id'=>$category_id,
							'category_sort_id'=>$category_sort_id,
							'createUserId'=>$this->loginUser['id']
						);
						$mdl_restaurant_category->insert($menu_category_info);
						$category_id =$category_id+100;
						$category_sort_id =$category_sort_id +10;
					}
				}

				$pageSql = "select  * from cc_restaurant_category where createUserId=".$this->loginUser['id']. " order by category_sort_id ";
				$pageUrl = $this->parseUrl()->set('page');
				$pageSize =50;
				$maxPage = 10;
				$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
				$data = $mdl_restaurant_category->getListBySql($page['outSql']);
				
				$this->setData($data, 'data');
				$this->setData($page['pageStr'], 'pager');
				$this->setData($this->parseUrl()->setPath('factory_2c/restaurant_edit'), 'editUrl');

				
					
			}
			
				

				$this->setData('restaurant_edit', 'submenu_top');
				$this->setData('restaurant_edit', 'submenu');
				$this->setData('index_publish', 'menu');
			
				$pagename = "店铺品类管理";
				$pageTitle=  $pagename." - 商家中心 - ". $this->site['pageTitle'];


				$this->setData($pagename, 'pagename');
				$this->setData($pageTitle, 'pageTitle');

				$this->setData($this->loginUser['gst_type'], 'gstType');
				$this->display_pc_mobile('factory_2c/edit', 'factory_2c/edit');
			
		}

/**
		 * 菜单编辑页面
		 */
		function single_producing_item_print_edit_action(){


		
			$customer_id =get2('customer_id');
			
		 if(!$customer_id) {
			  $customer_id =$this->current_business['id'];
				
			}
			$this->setData($customer_id,'customer_id');
				$dataType =get2('dataType');
		
			
			if($dataType=='all' or !$dataType) {
				$dataType='all';
			}
			
			$this->setData($dataType,'dataType');
			
			 $mdl = $this->loadModel('authrise_manage_other_business_account');
			 $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);
			
			 $this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');
			
			if($authoriseBusinessList) { //如果该商家可以托管账户
				// 检查接收的托管的商家是否合法
				
			
				
				$isAuthoriseCustomer =0 ;
				foreach ($authoriseBusinessList as $key => $value) {
					if($customer_id ==$value['customer_id'] || $customer_id ==$this->loginUser['id'] || $customer_id =$this->current_business['id']) {
							$isAuthoriseCustomer =1;
					}
					
				}
				
				if($isAuthoriseCustomer) { //如果是授权的customer

					$mdl_restaurant_category = $this->loadModel('restaurant_category');
					$pageSql = "select  * ,if(`parent_category_id`,concat(`parent_category_id`,id),concat(id,0)) as parent_id from cc_restaurant_category where createUserId=$customer_id  and (length(category_cn_name)>0 or length(category_en_name)>0)  and isdeleted =0  order by isHide, parent_id,category_sort_id ";
					$data = $mdl_restaurant_category->getListBySql($pageSql);

					
					if(!$data) {
						//$this->sheader(null,'您需要首先定义餐厅的菜单分类,然后才可以定义菜品....');
					}
					$this->setData($data,'restaurant_category');

					$sk = trim(get2('sk'));
					

					
					
					
					$category = trim(get2('category'));
					
					$this->setData($sk,'sk');
					$this->setData($category,'category');

					
					$sql = "select  o.* ,b.category_cn_name,b.category_en_name  from cc_restaurant_menu o left join cc_restaurant_category b on b.id=o.restaurant_category_id";
					
					if($category =='all' or empty($category)) {
						$whereStr.=" o.isDeleted=0 and (length(o.menu_cn_name) >0 or length(o.menu_en_name) >0) and o.restaurant_id=$customer_id  ";
					}else{
						$whereStr.=" o.isDeleted=0 and  (length(o.menu_cn_name) >0 or length(o.menu_en_name) >0) and o.restaurant_id=$customer_id   and (o.restaurant_category_id =$category or o.sub_category_id =$category) ";
					}
					

					if (!empty($sk)) {
						$whereStr.=" and (o.menu_cn_name  like  '%" . $sk . "%'";
						$whereStr.=" or o.menu_en_name  like  '%" . $sk . "%'";
						$whereStr.=" or o.menu_id  like  '%" . $sk . "%'";
						$whereStr.=" or o.Menu_desc  like  '%" . $sk . "%')";
					}
					
					if($dataType =='OnlySelected') {
							$whereStr.=" and proucing_item =1";
							
						}else{
						
					}
					
					// 提示用户选择菜单分类,如果没有选择菜单分类,则显示当前全部的菜单.
					// 如果选择某一种分类,如果当前没有数据则进行增加50个,如果有数据则直接显示即可.

					$mdl_restaurant_menu = $this->loadModel('restaurant_menu');
					$pageSql=$sql . " where " . $whereStr . " order by restaurant_category_id,LENGTH(menu_id),menu_id";
					//var_dump($pageSql);exit;
					$pageUrl = $this->parseUrl()->set('page');
					$pageSize =200;
					$maxPage = 10;
					$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
					$data = $mdl_restaurant_menu->getListBySql($page['outSql']);
					
					
					// 获得该用户的gst type 

					$mdl_user =$this->loadModel("user");
					$customerInfo = $mdl_user->get($customer_id);
					
					//var_dump($customerInfo);exit;
					

			  
					//获取该商家是否有多个供应商，是否为集合店
				 
					$this->loadModel('freshfood_disp_suppliers_schedule');
					$suppliersList = DispCenter::getSupplierListWithName($customer_id);
					//var_dump($suppliersList);exit;
					if( count($suppliersList) ==1 && $suppliersList[0]['suppliers_id']!=$customer_id ) {  //如果该配货中心下只有一个商家
						
						
					}
					
			   
					$this->setData($suppliersList, 'suppliersList');
					$this->setData($data, 'data');
					$this->setData($page['pageStr'], 'pager');
					$this->setData($this->parseUrl()->setPath('ctl.factory_2c/restaurant_edit'), 'editUrl');

					/**
					 * 获得配菜分类列表
					 */
					$where=array();
					$where[]="(length(category_cn_name) >0 or length(category_en_name) >0)";
					$where['restaurant_id']=$customer_id;
					$restaurant_sidedish_category_list=$this->loadModel('restaurant_sidedish_category')->getList(null,$where);
					$this->setData($restaurant_sidedish_category_list,'sidedish_category_list');
					/**
					 * 获得配菜分类列表
					 */
					$where=array();
					$where[]="(length(category_cn_name) >0 or length(category_en_name) >0)";
					$where['restaurant_id']=$customer_id;
					$restaurant_menu_option_list=$this->loadModel('restaurant_menu_option_category')->getList(null,$where);
					$this->setData($restaurant_menu_option_list,'menu_option_list');
					
				}else{  //如果可以管理更多店铺 
					
					
					

				}
		

			}else{ //如果只管理自己的店铺 
				
				//该代码删除
				
			}









			$this->setData('single_producing_item_print_edit', 'submenu');
			$this->setData('Producing_Centre', 'menu');

			$pagename = "设置加工类产品";
			$pageTitle=  $pagename." - Business Center - ". $this->site['pageTitle'];

			$this->setData($pagename, 'pagename');

			$this->setData($pageTitle, 'pageTitle');

			$this->setData($this->loginUser['gst_type'], 'gstType');
			$this->display_pc_mobile('factory_2c/single_producing_item_print_edit', 'factory_2c/single_producing_item_print_edit');
		}
            /**
             * 菜单编辑页面
             */

/*
        * 菜单编辑页面
        */
            function producing_item_stock_management_action(){



                $customer_id =get2('customer_id');

                if(!$customer_id) {
                    $customer_id =$this->current_business['id'];

                }
                $this->setData($customer_id,'customer_id');
                $dataType ='OnlySelected';



                $mdl = $this->loadModel('authrise_manage_other_business_account');
                $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);

                $this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');

                if($authoriseBusinessList) { //如果该商家可以托管账户
                    // 检查接收的托管的商家是否合法



                    $isAuthoriseCustomer =0 ;
                    foreach ($authoriseBusinessList as $key => $value) {
                        if($customer_id ==$value['customer_id'] || $customer_id ==$this->loginUser['id'] || $customer_id =$this->current_business['id']) {
                            $isAuthoriseCustomer =1;
                        }

                    }

                    if($isAuthoriseCustomer) { //如果是授权的customer

                        $mdl_restaurant_category = $this->loadModel('restaurant_category');
                        $pageSql = "select  * ,if(`parent_category_id`,concat(`parent_category_id`,id),concat(id,0)) as parent_id from cc_restaurant_category where createUserId=$customer_id  and (length(category_cn_name)>0 or length(category_en_name)>0)  and isdeleted =0  order by isHide, parent_id,category_sort_id ";
                        $data = $mdl_restaurant_category->getListBySql($pageSql);


                        if(!$data) {
                            //$this->sheader(null,'您需要首先定义餐厅的菜单分类,然后才可以定义菜品....');
                        }
                        $this->setData($data,'restaurant_category');

                        $sk = trim(get2('sk'));





                        $category = trim(get2('category'));

                        $this->setData($sk,'sk');
                        $this->setData($category,'category');


                        $sql = "select  spec.id as spec_type_id ,spec.category_en_name  as spec_type_name , if(spec_details.id is null,0,spec_details.id) as  spec_id, stock.stock_qty,spec_details.menu_en_name as spec_name, o.* ,b.category_cn_name,b.category_en_name
                          from cc_restaurant_menu o left join cc_restaurant_category b on b.id=o.restaurant_category_id 
                            left join cc_restaurant_menu_option_category spec on o.menu_option = spec.id 
                            left join cc_restaurant_menu_option spec_details on spec.id=spec_details.restaurant_category_id  and (length(spec_details.menu_en_name)>0 or length(spec_details.menu_cn_name)>0 )
                            left join cc_producing_item_stock stock on o.id =stock.item_id and if(spec_details.id is null ,0,spec_details.id)=stock.spec_id 
                        
                        ";

                        if($category =='all' or empty($category)) {
                            $whereStr.=" o.isDeleted=0 and (length(o.menu_cn_name) >0 or length(o.menu_en_name) >0) and o.restaurant_id=$customer_id  ";
                        }else{
                            $whereStr.=" o.isDeleted=0 and  (length(o.menu_cn_name) >0 or length(o.menu_en_name) >0) and o.restaurant_id=$customer_id   and (o.restaurant_category_id =$category or o.sub_category_id =$category) ";
                        }


                        if (!empty($sk)) {
                            $whereStr.=" and (o.menu_cn_name  like  '%" . $sk . "%'";
                            $whereStr.=" or o.menu_en_name  like  '%" . $sk . "%'";
                            $whereStr.=" or o.menu_id  like  '%" . $sk . "%'";
                            $whereStr.=" or o.Menu_desc  like  '%" . $sk . "%')";
                        }

                        if($dataType =='OnlySelected') {
                            $whereStr.=" and proucing_item =1";

                        }else{

                        }

                        // 提示用户选择菜单分类,如果没有选择菜单分类,则显示当前全部的菜单.
                        // 如果选择某一种分类,如果当前没有数据则进行增加50个,如果有数据则直接显示即可.

                        $mdl_restaurant_menu = $this->loadModel('restaurant_menu');
                        $pageSql=$sql . " where " . $whereStr . " order by restaurant_category_id,LENGTH(o.menu_id),o.menu_id ";
                       // var_dump($pageSql);exit;
                        $pageUrl = $this->parseUrl()->set('page');
                        $pageSize =200;
                        $maxPage = 10;
                        $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
                        $data = $mdl_restaurant_menu->getListBySql($page['outSql']);


                        // 获得该用户的gst type

                        $mdl_user =$this->loadModel("user");
                        $customerInfo = $mdl_user->get($customer_id);

                        //var_dump($customerInfo);exit;



                        //获取该商家是否有多个供应商，是否为集合店

                        $this->loadModel('freshfood_disp_suppliers_schedule');
                        $suppliersList = DispCenter::getSupplierListWithName($customer_id);
                        //var_dump($suppliersList);exit;
                        if( count($suppliersList) ==1 && $suppliersList[0]['suppliers_id']!=$customer_id ) {  //如果该配货中心下只有一个商家


                        }


                        $this->setData($suppliersList, 'suppliersList');
                        $this->setData($data, 'data');
                        $this->setData($page['pageStr'], 'pager');
                        $this->setData($this->parseUrl()->setPath('ctl.factory_2c/restaurant_edit'), 'editUrl');

                        /**
                         * 获得配菜分类列表
                         */
                        $where=array();
                        $where[]="(length(category_cn_name) >0 or length(category_en_name) >0)";
                        $where['restaurant_id']=$customer_id;
                        $restaurant_sidedish_category_list=$this->loadModel('restaurant_sidedish_category')->getList(null,$where);
                        $this->setData($restaurant_sidedish_category_list,'sidedish_category_list');
                        /**
                         * 获得配菜分类列表
                         */
                        $where=array();
                        $where[]="(length(category_cn_name) >0 or length(category_en_name) >0)";
                        $where['restaurant_id']=$customer_id;
                        $restaurant_menu_option_list=$this->loadModel('restaurant_menu_option_category')->getList(null,$where);
                        $this->setData($restaurant_menu_option_list,'menu_option_list');

                    }else{  //如果可以管理更多店铺




                    }


                }else{ //如果只管理自己的店铺

                    //该代码删除

                }








                $this->setData('producing_item_stock_management', 'submenu_top');
                $this->setData('producing_item_stock_management', 'submenu');
                $this->setData('dispatching_center', 'menu');

                $pagename = "Producing Item stock Management";
                $pageTitle=  $pagename." - Business Center - ". $this->site['pageTitle'];

                $this->setData($pagename, 'pagename');

                $this->setData($pageTitle, 'pageTitle');

                $this->setData($this->loginUser['gst_type'], 'gstType');
                $this->display_pc_mobile('factory_2c/producing_item_stock_management', 'factory_2c/producing_item_stock_management');
            }
            /**
             * 菜单编辑页面
             */

            /*
                    * 菜单编辑页面
                    */
            function producing_stock_to_dispatching_action(){


                $mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');
                $mdl_order = $this->loadModel('order');
                $mdl_user= $this->loadModel('user');

                /**
                 * status List
                 */

                /**
                 * payment Type List
                 */

                /**
                 * customer_delivery_option Type List
                 */
                $three_days_times = time()-60*60*24*7;
                $mdl = $this->loadModel('factory2c_list');
                $availableDates = Factory2c_centre::getAvaliableDateOfAllSalesChannelOfThisFactory($this->current_business['id']);
                $availableDates = array_map(function($d){
                    return date('Y-m-d',$d['logistic_delivery_date']);
                }, $availableDates);
                $this->setData($availableDates, 'availableDates');
//var_dump($availableDates);exit;


                $sepratePage =get2('sepratePage');
                $this->setData($sepratePage,'sepratePage');
                //	var_dump($sepratePage);exit;

                //** 获取该商家管辖工厂渠道商家

                $mdl = $this->loadModel('factory2c_list');
                $SalesChannelCustomerList= Factory2c_centre::getCustmerListsIncludeFactoryWithBusinessName($this->current_business['id'],$this->current_business['displayName']);
                $this->setData($SalesChannelCustomerList, 'SalesChannelCustomerList');




                //交易状态购买
                //if(!status) {
                $status ='c01';
                //}
                //支付状态
                $ifpaid=1;

                $business_id = trim(get2('business_id'));
                if($business_id) {
                    $business_user = $mdl_user->get($business_id) ;
                    $business_tradingName=$business_user ['displayName'];
                } else{
                    $business_id =$this->current_business['id'];
                    $business_tradingName=$this->current_business['displayName'];
                }

                // 做到这里，如果 是suppliersID 且数据源!=1 则要使用cc_order_import 做为引导。

                if($this->loadModel('dispatching_centre_customer_list')->getIfBusinessIsExportDataSource($business_id)) {
                    $export_data_source =1;
                    $query_table_name='cc_order_import';
                }else{
                    $export_data_source =0;
                    $query_table_name='cc_order';
                }

                //var_dump($export_data_source);exit;
                $this->setData($business_id,'business_id');




                $sk = trim(get2('sk'));




                $customer_delivery_date = trim(get2('customer_delivery_date'));


                $this->setData($sk,'sk');
                $this->setData($customer_delivery_date,'customer_delivery_date');

                //获取当前用户点击的大类
                $cate_id = trim(get2('cate_id'));
                $this->setData($cate_id,'cate_id');


                //获取是否为生产类
                $producing = trim(get2('producing'));
                if(!$producing) $producing =0;
                $this->setData($producing,'producing');



                //获取当前用户点击的大类
                $logistic_truck_No = trim(get2('logistic_truck_No'));
                $this->setData($logistic_truck_No,'logistic_truck_No');

                $TuckListOfTheDay =$this->loadModel('truck')->getAllOrdersTruckListwithCount($this->current_business['id'],$customer_delivery_date);
                $this->setData($TuckListOfTheDay,'TuckListOfTheDay');




                // 加入了一个前面可以选择一个商家，然后显示该商家的相关记录，如果商家id 为空，则保持原来的处理，如果不为空则进行相应的处理
                //获得该商家是否为外部数据源，如果是外部数据源，则需要使用外部订单总表关联
             //   var_dump('businessid is '.$business_id);exit;
                $sql= Factory2c_centre::getSqlofAllOrdersDataOfCurrentBusiness($business_id,$query_table_name,$this->current_business['id']);




                if (!empty($sk)) {
                    $whereStr.=" and ( c.bonus_title like  '%" . $sk . "%'";
                    $whereStr.=" or cate.category_cn_name like  '%" . $sk . "%'";
                    $whereStr.=" or cate.category_cn_name like  '%" . $sk . "%'";
                    $whereStr.=" or r.menu_id like  '%" . $sk . "%'";
                    $whereStr.=" or r.menu_en_name like  '%" . $sk . "%'";
                    $whereStr.=" or r.menu_cn_name like  '%" . $sk . "%'";
                    $whereStr.=" or c.business_id like  '%" . $sk . "%')";
                    $where[]=$whereStr;
                }

                if (!empty($cate_id)) {
                    if($cate_id !='all') {
                        $whereStr.=" and r.restaurant_category_id =$cate_id ";
                    }



                }


                // 如果为生产类，加入条件
                if ($producing) {
                    $whereStr.=" and r.proucing_item =1 ";
                }else{
                    $whereStr.=" and r.proucing_item =0 ";
                }

                if (!empty($logistic_truck_No)) {
                    if($logistic_truck_No !='all') {
                        $whereStr.=" and o.logistic_truck_No =$logistic_truck_No ";
                    }
                }

                //deleivery date
                if (!empty($customer_delivery_date)) {
                    if ($customer_delivery_date != 'all') {
                        $whereStr.= " and  DATE_FORMAT(from_unixtime(o.logistic_delivery_date),'%Y-%m-%d') = '$customer_delivery_date' ";
                    }else{
                        $three_days_times = time()-259200;
                        $whereStr.= " and  o.logistic_delivery_date > $three_days_times";


                    }
                }else {
                    $three_days_times = time()-259200;
                    $whereStr.= " and  o.logistic_delivery_date > $three_days_times";
                }



                if($business_id) {
                    $pageSql=$sql . $whereStr . " group by  DATE_FORMAT(from_unixtime(o.logistic_delivery_date),'%Y-%m-%d'),r.source_menu_id ,c.guige1_id order by o.logistic_delivery_date,cate.category_sort_id,r.menu_order_id";


                }else{ //打印总单

                    $pageSql=$sql . $whereStr . " group by  DATE_FORMAT(from_unixtime(o.logistic_delivery_date),'%Y-%m-%d'),r.source_menu_id,c.guige1_id order by o.logistic_delivery_date,cate.category_sort_id,r.menu_order_id";

                }
               // var_dump($pageSql);exit;
                $data = $mdl_order->getListBySql($pageSql);

                // var_dump($pageSql);exit;

                // 获得当前订单得大类汇总

                $cateData =$this->loadModel('restaurant_category')->getParentCateList($this->current_business['id']);


                // var_dump($cateData);exit;
                $this->setData($cateData,'cateData');

                $this->setData($page['pageStr'],'pager');



                $this->setData('producing_stock_to_dispatching', 'submenu_top');
                $this->setData('producing_item_stock_management', 'submenu');
                $this->setData('dispatching_center', 'menu');
                $this->setData('dispatching center - ' . $this->site['pageTitle'], 'pageTitle');

                $assign_stock = trim(get2('assign_stock'));
                $this->setData($assign_stock,'assign_stock');
            // 这里需要加入 如果正在被锁定或者已经处理好的item 不要放在分配的列表中。
                //或者将这个逻辑放在细节中处理 。。。。 ，is procedduing down ar is lock
                if ($assign_stock==1) {
                    $mdl_stock_assign = $this->loadModel('stock_assign');
                    $assign_stock_quantity=0;
                    $mdl_producing_item_stock = $this->loadModel('producing_item_stock');
                    foreach ($data as $key => $value) {
                        // lock this product all items detials

                      $assign_stock_quantity = $mdl_stock_assign->assgin_single_item_stock($value,$logistic_truck_No,$this->loginUser['id']);
                      $data[$key]['assign_stock_quantity'] = $assign_stock_quantity;

                      $new_stock_qty = $value['stock_qty']-$assign_stock_quantity;
                      $data[$key]['rest_stock_qty'] = $new_stock_qty;
                       $mdl_producing_item_stock->updateByWhere(array('stock_qty'=>$new_stock_qty),array('item_id'=>$value['id'],'spec_id'=>$value['guige1_id']));
                      // unlocak this prodouct details

                    }
                }
                $this->setData($data,'data');

                    $this->setData(HTTP_ROOT_WWW.'factory_2c/producing_stock_to_dispatching', 'searchUrl');
                    $this->setData($this->parseUrl(), 'currentUrl');

                    $this->display_pc_mobile('factory_2c/producing_stock_to_dispatching','factory_2c/producing_stock_to_dispatching');













                           }
            /**
             * 菜单编辑页面
             */

            function single_item_print_edit_action(){

                // 获得该用户餐厅的菜单分类信息

                $freshfood =get2('freshfood');

                if ($freshfood) {
                    $this->setData($freshfood,'freshfood');
                }else{
                    $freshfood = post('freshfood');
                    $this->setData($freshfood,'freshfood');
                    //var_dump($freshfood);exit;

                }

                $customer_id =get2('customer_id');


                if(!$customer_id) {
                    $customer_id =$this->current_business['id'];

                }
                $this->setData($customer_id,'customer_id');
                $dataType =get2('dataType');


                if($dataType=='all' or !$dataType) {
                    $dataType='all';
                }

                $this->setData($dataType,'dataType');

                $mdl = $this->loadModel('authrise_manage_other_business_account');
                $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->current_business['id']);

                $this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');

                if($authoriseBusinessList) { //如果该商家可以托管账户
                    // 检查接收的托管的商家是否合法



                    $isAuthoriseCustomer =0 ;
                    foreach ($authoriseBusinessList as $key => $value) {
                        if($customer_id ==$value['customer_id'] || $customer_id ==$this->current_business['id']) {
                            $isAuthoriseCustomer =1;
                        }

                    }

                    if($isAuthoriseCustomer) { //如果是授权的customer
                        $mdl_restaurant_category = $this->loadModel('restaurant_category');
                        $pageSql = "select  * ,if(`parent_category_id`,concat(`parent_category_id`,id),concat(id,0)) as parent_id from cc_restaurant_category where createUserId=$customer_id  and (length(category_cn_name)>0 or length(category_en_name)>0)  and isdeleted =0  order by isHide, parent_id,category_sort_id ";
                        $data = $mdl_restaurant_category->getListBySql($pageSql);


                        if(!$data) {
                            //$this->sheader(null,'您需要首先定义餐厅的菜单分类,然后才可以定义菜品....');
                        }
                        $this->setData($data,'restaurant_category');

                        $sk = trim(get2('sk'));

                        $allOrspecial = trim(get2('allOrspecial'));



                        $category = trim(get2('category'));

                        $this->setData($sk,'sk');
                        $this->setData($category,'category');


                        $sql = "select  o.* ,b.category_cn_name,b.category_en_name  from cc_restaurant_menu o left join cc_restaurant_category b on b.id=o.restaurant_category_id";

                        if($category =='all' or empty($category)) {
                            $whereStr.=" o.isDeleted=0 and (length(o.menu_cn_name) >0 or length(o.menu_en_name) >0) and o.restaurant_id=$customer_id  ";
                        }else{
                            $whereStr.=" o.isDeleted=0 and  (length(o.menu_cn_name) >0 or length(o.menu_en_name) >0) and o.restaurant_id=$customer_id   and (o.restaurant_category_id =$category or o.sub_category_id =$category) ";
                        }


                        if (!empty($sk)) {
                            $whereStr.=" and (o.menu_cn_name  like  '%" . $sk . "%'";
                            $whereStr.=" or o.menu_en_name  like  '%" . $sk . "%'";
                            $whereStr.=" or o.menu_id  like  '%" . $sk . "%'";
                            $whereStr.=" or o.Menu_desc  like  '%" . $sk . "%')";
                        }

                        if($dataType =='OnlySelected') {
                            $whereStr.=" and singleItemPrintBuyingList =1";

                        }else{

                        }

                        // 提示用户选择菜单分类,如果没有选择菜单分类,则显示当前全部的菜单.
                        // 如果选择某一种分类,如果当前没有数据则进行增加50个,如果有数据则直接显示即可.

                        $mdl_restaurant_menu = $this->loadModel('restaurant_menu');
                        $pageSql=$sql . " where " . $whereStr . " order by restaurant_category_id,LENGTH(menu_id),menu_id";
                        //var_dump($pageSql);exit;
                        $pageUrl = $this->parseUrl()->set('page');
                        $pageSize =200;
                        $maxPage = 10;
                        $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
                        $data = $mdl_restaurant_menu->getListBySql($page['outSql']);


                        // 获得该用户的gst type

                        $mdl_user =$this->loadModel("user");
                        $customerInfo = $mdl_user->get($customer_id);

                        //var_dump($customerInfo);exit;



                        //获取该商家是否有多个供应商，是否为集合店

                        $this->loadModel('freshfood_disp_suppliers_schedule');
                        $suppliersList = DispCenter::getSupplierListWithName($customer_id);
                        //var_dump($suppliersList);exit;
                        if( count($suppliersList) ==1 && $suppliersList[0]['suppliers_id']!=$customer_id ) {  //如果该配货中心下只有一个商家


                        }


                        $this->setData($suppliersList, 'suppliersList');
                        $this->setData($data, 'data');
                        $this->setData($page['pageStr'], 'pager');
                        $this->setData($this->parseUrl()->setPath('ctl.factory_2c/restaurant_edit'), 'editUrl');

                        /**
                         * 获得配菜分类列表
                         */
                        $where=array();
                        $where[]="(length(category_cn_name) >0 or length(category_en_name) >0)";
                        $where['restaurant_id']=$customer_id;
                        $restaurant_sidedish_category_list=$this->loadModel('restaurant_sidedish_category')->getList(null,$where);
                        $this->setData($restaurant_sidedish_category_list,'sidedish_category_list');
                        /**
                         * 获得配菜分类列表
                         */
                        $where=array();
                        $where[]="(length(category_cn_name) >0 or length(category_en_name) >0)";
                        $where['restaurant_id']=$customer_id;
                        $restaurant_menu_option_list=$this->loadModel('restaurant_menu_option_category')->getList(null,$where);
                        $this->setData($restaurant_menu_option_list,'menu_option_list');

                    }else{  //如果可以管理更多店铺




                    }


                }else{ //如果只管理自己的店铺

                    $mdl_restaurant_category = $this->loadModel('restaurant_category');
                    $pageSql = "select  * from cc_restaurant_category where createUserId=".$this->current_business['id']. " and (length(category_cn_name)>0 or length(category_en_name)>0) order by category_sort_id ";
                    $data = $mdl_restaurant_category->getListBySql($pageSql);


                    if(!$data) {
                        $this->sheader(null,'您需要首先定义餐厅的菜单分类,然后才可以定义菜品....');
                    }
                    $this->setData($data,'restaurant_category');

                    $sk = trim(get2('sk'));

                    $allOrspecial = trim(get2('allOrspecial'));



                    $category = trim(get2('category'));

                    $this->setData($sk,'sk');
                    $this->setData($category,'category');


                    $sql = "select  o.* ,b.category_cn_name,b.category_en_name  from cc_restaurant_menu o left join cc_restaurant_category b on b.id=o.restaurant_category_id";

                    if($category =='all' or empty($category)) {
                        $whereStr.=" (length(o.menu_cn_name) >0 or length(o.menu_en_name) >0) and  o.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id= ".$this->current_business['id']." ) and (o.restaurant_id= ".$this->current_business['id'] ."  or o.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id= ".$this->current_business['id']." ) )";
                    }else{
                        $whereStr.=" (o.restaurant_id= ".$this->current_business['id'] ." and  o.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id= ".$this->current_business['id']." )   or o.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id= ".$this->current_business['id']." ) )";
                    }

                    if (!empty($category)) {
                        if ($category != 'all') {
                            $whereStr.= " and o.restaurant_category_id='$category' ";
                        }
                    }
                    if (!empty($sk)) {
                        $whereStr.=" and (o.menu_cn_name  like  '%" . $sk . "%'";
                        $whereStr.=" or o.menu_en_name  like  '%" . $sk . "%'";
                        $whereStr.=" or o.Menu_desc  like  '%" . $sk . "%')";
                    }

                    if($allOrspecial =='special') {
                        $whereStr.=" and onSpecial =1";
                        $this->setData($allOrspecial,'allOrspecial');
                    }else{
                        $this->setData($allOrspecial,'all');
                    }

                    // 提示用户选择菜单分类,如果没有选择菜单分类,则显示当前全部的菜单.
                    // 如果选择某一种分类,如果当前没有数据则进行增加50个,如果有数据则直接显示即可.

                    $mdl_restaurant_menu = $this->loadModel('restaurant_menu');
                    $pageSql=$sql . " where " . $whereStr . " order by restaurant_category_id,LENGTH(menu_id),menu_id";
                    //	var_dump($pageSql);exit;
                    $pageUrl = $this->parseUrl()->set('page');
                    $pageSize =200;
                    $maxPage = 10;
                    $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
                    $data = $mdl_restaurant_menu->getListBySql($page['outSql']);

                    if(!$data){

                        if($category !='all' && !empty($category)) {
                            // 增加50个菜单分类
                            $menu_id =100;


                            for($i=0;$i<100;$i++) {
                                $menu_info=array(
                                    'createUserId'=>$this->current_business['id'],
                                    'restaurant_id'=>$this->current_business['id'],
                                    'restaurant_category_id'=>$category,
                                    'menu_id'=>$category.$menu_id,
                                    'menu_cn_name'=>'',
                                    'price'=>'',
                                    'guige_group_id_2'=>'',
                                    'menu_pic'=>'',
                                    'Menu_desc'=>'',
                                    'menu_en_name'=>'',
                                    'include_gst' => $this->current_business['gst_type'] % 2 //默认gst根据公司gst类型，1，3为全部gst和多数gst，2，4为全部无gst和少数gst
                                );
                                $mdl_restaurant_menu->insert($menu_info);
                                $menu_id =$menu_id+1;
                            }

                            $pageSql = "select  * from cc_restaurant_menu where createUserId=".$this->current_business['id']. " and restaurant_category_id =".$category." order by menu_id";
                            $pageUrl = $this->parseUrl()->set('page');
                            $pageSize =200;
                            $maxPage = 10;
                            $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
                            $data = $mdl_restaurant_menu->getListBySql($page['outSql']);
                        }
                    }

                    //获取该商家是否有多个供应商，是否为集合店

                    $this->loadModel('freshfood_disp_suppliers_schedule');
                    $suppliersList = DispCenter::getSupplierListWithName($this->current_business['id']);
                    //var_dump($suppliersList);exit;
                    if( count($suppliersList) ==1 && $suppliersList[0]['suppliers_id']!=$this->current_business['id'] ) {  //如果该配货中心下只有一个商家


                    }


                    $this->setData($suppliersList, 'suppliersList');
                    $this->setData($data, 'data');
                    $this->setData($page['pageStr'], 'pager');
                    $this->setData($this->parseUrl()->setPath('factory_2c/singleItemPrintEdit'), 'editUrl');

                    /**
                     * 获得配菜分类列表
                     */
                    $where=array();
                    $where[]="(length(category_cn_name) >0 or length(category_en_name) >0)";
                    $where['restaurant_id']=$this->current_business['id'];
                    $restaurant_sidedish_category_list=$this->loadModel('restaurant_sidedish_category')->getList(null,$where);
                    $this->setData($restaurant_sidedish_category_list,'sidedish_category_list');
                    /**
                     * 获得配菜分类列表
                     */
                    $where=array();
                    $where[]="(length(category_cn_name) >0 or length(category_en_name) >0)";
                    $where['restaurant_id']=$this->current_business['id'];
                    $restaurant_menu_option_list=$this->loadModel('restaurant_menu_option_category')->getList(null,$where);
                    $this->setData($restaurant_menu_option_list,'menu_option_list');




                }







                $this->setData('restaurant_menu', 'submenu_top');

                $this->setData('singleItemPrintEdit', 'submenu');
                $this->setData('dispatching_center', 'menu');

                $pagename = "打印单品管理";
                $pageTitle=  $pagename." - 商家中心 - ". $this->site['pageTitle'];

                $this->setData($pagename, 'pagename');

                $this->setData($pageTitle, 'pageTitle');

                $this->setData($this->current_business['gst_type'], 'gstType');
                $this->display_pc_mobile('factory_2c/singleItemPrintEdit', 'factory_2c/singleItemPrintEdit');
            }

            public function adjust_item_box_action(){
                $id=get2('id');
                $delivery_date =get2('delivery_date');
                $mdl_details =$this->loadModel('wj_customer_coupon');
                $delivery_date_number =   strtotime($delivery_date." 00:00:00");
                $sql ="select order_id from cc_wj_customer_coupon c left join cc_order o on c.order_id =o.orderId where c.restaurant_menu_id = $id and  o.logistic_delivery_date >=$delivery_date_number group by c.order_id";
                $orderList = $mdl_details->getListBySql($sql);
//var_dump($sql);exit;
                $mdl=$this->loadModel('boxNumberOutput');
                foreach ($orderList as $key=>$value){
                    $totalBoxNumber =$mdl->UpdateOrderBoxInfo($value['order_id']);
                    $str .= "order id :".$value['order_id'].' and new box number is : '.$totalBoxNumber.' ';

                }
                echo  $str;


            }
	/**
		 * 菜单编辑页面
		 */
		function restaurant_menu_edit_action(){

			// 获得该用户餐厅的菜单分类信息
			
			$freshfood =get2('freshfood');
			
			if ($freshfood) {
				$this->setData($freshfood,'freshfood');
			}else{
				$freshfood = post('freshfood');
				$this->setData($freshfood,'freshfood');
				//var_dump($freshfood);exit;
				
			}
		
			$customer_id =get2('customer_id');
			
			if(!$customer_id) {
			  $customer_id =$this->current_business['id'];
				
			}
			$this->setData($customer_id,'customer_id');
			
			 $mdl = $this->loadModel('authrise_manage_other_business_account');
			 $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->current_business['id']);
			
			 $this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');
			
			if($authoriseBusinessList) { //如果该商家可以托管账户
				// 检查接收的托管的商家是否合法
				
			
				
				$isAuthoriseCustomer =0 ;
				foreach ($authoriseBusinessList as $key => $value) {
					if($customer_id ==$value['customer_id'] || $customer_id ==$this->current_business['id']) {
							$isAuthoriseCustomer =1;
					}
					
				}
				
				if($isAuthoriseCustomer) { //如果是授权的customer  
					$mdl_restaurant_category = $this->loadModel('restaurant_category');
					$pageSql = "select  * from cc_restaurant_category where createUserId=$customer_id  and (length(category_cn_name)>0 or length(category_en_name)>0) order by category_sort_id ";
					$data = $mdl_restaurant_category->getListBySql($pageSql);
					
					
					if(!$data) {
						//$this->sheader(null,'您需要首先定义餐厅的菜单分类,然后才可以定义菜品....');
					}
					$this->setData($data,'restaurant_category');

					$sk = trim(get2('sk'));
					
					$allOrspecial = trim(get2('allOrspecial'));
					
					
					
					$category = trim(get2('category'));
					
					$this->setData($sk,'sk');
					$this->setData($category,'category');

					
					$sql = "select  o.* ,b.category_cn_name,b.category_en_name  from cc_restaurant_menu o left join cc_restaurant_category b on b.id=o.restaurant_category_id";
					
					if($category =='all' or empty($category)) {
						$whereStr.=" (length(o.menu_cn_name) >0 or length(o.menu_en_name) >0) and  o.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id=$customer_id ) and (o.restaurant_id=$customer_id  or o.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id=$customer_id ) )";
					}else{
						$whereStr.=" (o.restaurant_id=$customer_id and  o.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id=$customer_id )   or o.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id=$customer_id ) )";
					}
					
					if (!empty($category)) {
						if ($category != 'all') {
							$whereStr.= " and o.restaurant_category_id='$category' ";
						}
					}
					if (!empty($sk)) {
						$whereStr.=" and (o.menu_cn_name  like  '%" . $sk . "%'";
						$whereStr.=" or o.menu_en_name  like  '%" . $sk . "%'";
						$whereStr.=" or o.Menu_desc  like  '%" . $sk . "%')";
					}
					
					if($allOrspecial =='special') {
							$whereStr.=" and onSpecial =1";
							$this->setData($allOrspecial,'allOrspecial');
						}else{
						$this->setData($allOrspecial,'all');
					}
					
					// 提示用户选择菜单分类,如果没有选择菜单分类,则显示当前全部的菜单.
					// 如果选择某一种分类,如果当前没有数据则进行增加50个,如果有数据则直接显示即可.

					$mdl_restaurant_menu = $this->loadModel('restaurant_menu');
					$pageSql=$sql . " where " . $whereStr . " order by restaurant_category_id,LENGTH(menu_id),menu_id";
				//	var_dump($pageSql);exit;
					$pageUrl = $this->parseUrl()->set('page');
					$pageSize =200;
					$maxPage = 10;
					$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
					$data = $mdl_restaurant_menu->getListBySql($page['outSql']);
					
					
					// 获得该用户的gst type 

					$mdl_user =$this->loadModel("user");
					$customerInfo = $mdl_user->get($customer_id);
					
					//var_dump($customerInfo);exit;
					
					if(!$data){

						if($category !='all' && !empty($category)) {
							// 增加50个菜单分类
							$menu_id =100;
							
							
							for($i=0;$i<100;$i++) {
								$menu_info=array(
									'createUserId'=>$customer_id,
									'restaurant_id'=>$customer_id,
									'restaurant_category_id'=>$category,
									'menu_id'=>$category.$menu_id,
									'menu_cn_name'=>'',
									'price'=>'',
									'guige_group_id_2'=>'',
									'menu_pic'=>'',
									'Menu_desc'=>'',
									'menu_en_name'=>'',
									'include_gst' => $customerInfo['gst_type'] % 2 //默认gst根据公司gst类型，1，3为全部gst和多数gst，2，4为全部无gst和少数gst
								);
								$mdl_restaurant_menu->insert($menu_info);
								$menu_id =$menu_id+1;
							}

							$pageSql = "select  * from cc_restaurant_menu where createUserId=$customer_id and restaurant_category_id =".$category." order by menu_id";
							$pageUrl = $this->parseUrl()->set('page');
							$pageSize =200;
							$maxPage = 10;
							$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
							$data = $mdl_restaurant_menu->getListBySql($page['outSql']);
						}
					}
			  
					//获取该商家是否有多个供应商，是否为集合店
				 
					$this->loadModel('freshfood_disp_suppliers_schedule');
					$suppliersList = DispCenter::getSupplierListWithName($customer_id);
					//var_dump($suppliersList);exit;
					if( count($suppliersList) ==1 && $suppliersList[0]['suppliers_id']!=$customer_id ) {  //如果该配货中心下只有一个商家
						
						
					}
					
			   
					$this->setData($suppliersList, 'suppliersList');
					$this->setData($data, 'data');
					$this->setData($page['pageStr'], 'pager');
					$this->setData($this->parseUrl()->setPath('ctl.factory_2c/restaurant_edit'), 'editUrl');

					/**
					 * 获得配菜分类列表
					 */
					$where=array();
					$where[]="(length(category_cn_name) >0 or length(category_en_name) >0)";
					$where['restaurant_id']=$customer_id;
					$restaurant_sidedish_category_list=$this->loadModel('restaurant_sidedish_category')->getList(null,$where);
					$this->setData($restaurant_sidedish_category_list,'sidedish_category_list');
					/**
					 * 获得配菜分类列表
					 */
					$where=array();
					$where[]="(length(category_cn_name) >0 or length(category_en_name) >0)";
					$where['restaurant_id']=$customer_id;
					$restaurant_menu_option_list=$this->loadModel('restaurant_menu_option_category')->getList(null,$where);
					$this->setData($restaurant_menu_option_list,'menu_option_list');
					
				}else{  //如果可以管理更多店铺 
					
					
					

				}
		

			}else{ //如果只管理自己的店铺 
				
				$mdl_restaurant_category = $this->loadModel('restaurant_category');
				$pageSql = "select  * from cc_restaurant_category where createUserId=".$this->current_business['id']. " and (length(category_cn_name)>0 or length(category_en_name)>0) order by category_sort_id ";
				$data = $mdl_restaurant_category->getListBySql($pageSql);
				
				
				if(!$data) {
					$this->sheader(null,'您需要首先定义餐厅的菜单分类,然后才可以定义菜品....');
				}
				$this->setData($data,'restaurant_category');

				$sk = trim(get2('sk'));
				
				$allOrspecial = trim(get2('allOrspecial'));
				
				
				
				$category = trim(get2('category'));
				
				$this->setData($sk,'sk');
				$this->setData($category,'category');

				
				$sql = "select  o.* ,b.category_cn_name,b.category_en_name  from cc_restaurant_menu o left join cc_restaurant_category b on b.id=o.restaurant_category_id";
				
				if($category =='all' or empty($category)) {
					$whereStr.=" (length(o.menu_cn_name) >0 or length(o.menu_en_name) >0) and  o.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id= ".$this->current_business['id']." ) and (o.restaurant_id= ".$this->current_business['id'] ."  or o.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id= ".$this->current_business['id']." ) )";
				}else{
					$whereStr.=" (o.restaurant_id= ".$this->current_business['id'] ." and  o.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id= ".$this->current_business['id']." )   or o.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id= ".$this->current_business['id']." ) )";
				}
				
				if (!empty($category)) {
					if ($category != 'all') {
						$whereStr.= " and o.restaurant_category_id='$category' ";
					}
				}
				if (!empty($sk)) {
					$whereStr.=" and (o.menu_cn_name  like  '%" . $sk . "%'";
					$whereStr.=" or o.menu_en_name  like  '%" . $sk . "%'";
					$whereStr.=" or o.Menu_desc  like  '%" . $sk . "%')";
				}
				
				if($allOrspecial =='special') {
						$whereStr.=" and onSpecial =1";
						$this->setData($allOrspecial,'allOrspecial');
					}else{
					$this->setData($allOrspecial,'all');
				}
				
				// 提示用户选择菜单分类,如果没有选择菜单分类,则显示当前全部的菜单.
				// 如果选择某一种分类,如果当前没有数据则进行增加50个,如果有数据则直接显示即可.

				$mdl_restaurant_menu = $this->loadModel('restaurant_menu');
				$pageSql=$sql . " where " . $whereStr . " order by restaurant_category_id,LENGTH(menu_id),menu_id";
				$pageUrl = $this->parseUrl()->set('page');
				$pageSize =200;
				$maxPage = 10;
				$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
				$data = $mdl_restaurant_menu->getListBySql($page['outSql']);
				
				if(!$data){

					if($category !='all' && !empty($category)) {
						// 增加50个菜单分类
						$menu_id =100;
						
						
						for($i=0;$i<100;$i++) {
							$menu_info=array(
								'createUserId'=>$this->current_business['id'],
								'restaurant_id'=>$this->current_business['id'],
								'restaurant_category_id'=>$category,
								'menu_id'=>$category.$menu_id,
								'menu_cn_name'=>'',
								'price'=>'',
								'guige_group_id_2'=>'',
								'menu_pic'=>'',
								'Menu_desc'=>'',
								'menu_en_name'=>'',
								'include_gst' => $this->current_business['gst_type'] % 2 //默认gst根据公司gst类型，1，3为全部gst和多数gst，2，4为全部无gst和少数gst
							);
							$mdl_restaurant_menu->insert($menu_info);
							$menu_id =$menu_id+1;
						}

						$pageSql = "select  * from cc_restaurant_menu where createUserId=".$this->current_business['id']. " and restaurant_category_id =".$category." order by menu_id";
						$pageUrl = $this->parseUrl()->set('page');
						$pageSize =200;
						$maxPage = 10;
						$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
						$data = $mdl_restaurant_menu->getListBySql($page['outSql']);
					}
				}
		  
				//获取该商家是否有多个供应商，是否为集合店
			 
				$this->loadModel('freshfood_disp_suppliers_schedule');
				$suppliersList = DispCenter::getSupplierListWithName($this->current_business['id']);
				//var_dump($suppliersList);exit;
				if( count($suppliersList) ==1 && $suppliersList[0]['suppliers_id']!=$this->current_business['id'] ) {  //如果该配货中心下只有一个商家
					
					
				}
				
		   
				$this->setData($suppliersList, 'suppliersList');
				$this->setData($data, 'data');
				$this->setData($page['pageStr'], 'pager');
				$this->setData($this->parseUrl()->setPath('factory_2c/restaurant_edit'), 'editUrl');

				/**
				 * 获得配菜分类列表
				 */
				$where=array();
				$where[]="(length(category_cn_name) >0 or length(category_en_name) >0)";
				$where['restaurant_id']=$this->current_business['id'];
				$restaurant_sidedish_category_list=$this->loadModel('restaurant_sidedish_category')->getList(null,$where);
				$this->setData($restaurant_sidedish_category_list,'sidedish_category_list');
				/**
				 * 获得配菜分类列表
				 */
				$where=array();
				$where[]="(length(category_cn_name) >0 or length(category_en_name) >0)";
				$where['restaurant_id']=$this->current_business['id'];
				$restaurant_menu_option_list=$this->loadModel('restaurant_menu_option_category')->getList(null,$where);
				$this->setData($restaurant_menu_option_list,'menu_option_list');

				
				
				
			}
			
			
			
			
			

			
			$this->setData('restaurant_menu', 'submenu_top');

			$this->setData('restaurant_menu_edit', 'submenu');
			$this->setData('index_publish', 'menu');

			$pagename = "店铺单品管理";
			$pageTitle=  $pagename." - 商家中心 - ". $this->site['pageTitle'];

			$this->setData($pagename, 'pagename');

			$this->setData($pageTitle, 'pageTitle');

			$this->setData($this->current_business['gst_type'], 'gstType');
			$this->display_pc_mobile('factory_2c/menu_edit', 'factory_2c/menu_edit');
		}



        function channel_copy_action()
    {
        $id = (int)get2('id');
      

        $mdl_coupons = $this->loadModel('coupons');
     

        $coupon = $mdl_coupons->get($id);
		
		if($coupon['createUserId'] != $this->loginUser['id']) {
			var_dump('未知错误');exit;
			
		}
		
		 $customer_id = (int)get2('customer_id');
		 
		 // 如果该客户不是当前商家的channel 不容许拷贝
		 
		 $sql1 ="select *  from cc_factory2c_list where factroy_id=".$this->loginUser['id']." and customer_id =$customer_id";
		 $customer_rec  = $this->loadModel("factory2c_list")->getListBySql($sql1);
		
		 if(!$customer_rec) {
			 
			 var_dump('未知错误1,未发现对应关系');exit;
		 }
		 
		  $sql1 ="select count(*) as count  from cc_coupons where createUserId=$customer_id  and EvoucherOrrealproduct ='restaurant_menu'";
		  $coupon_rec = $mdl_coupons->getListBySql($sql1);
		  if($coupon_rec[0]['count']) {
			 
			 var_dump('每个channel只能复制一张你卡片，请检查该channel是否已经存在类似店铺！');exit;
		 }
		
		  
		  

        if ($id > 0) {
            $newid = $mdl_coupons->copy_sales_channel($id, $customer_id, $customer_rec[0]['simply_name']);

          
        }

			$this->sheader(HTTP_ROOT_WWW . 'factory_2c/sales_channel_management');


    }

		/**
		 * 菜单编辑页面
		 */
		function sales_channel_management_action(){

			// 获得该用户餐厅的菜单分类信息
		
				//获得coupon id ,为复制店铺卡片
				
				$mdl_coupon =$this->loadModel("coupons");
				$sql1="SELECT * FROM `cc_coupons` WHERE createUserId=".$this->loginUser['id'];
				
				$coupon =$mdl_coupon->getListBySql($sql1);
				$this->setData($coupon[0],'coupon');
				
				$mdl_factory2c_list = $this->loadModel('factory2c_list');
				$pageSql = "select  a.*  ,b.phone ,b.nickname,b.displayName,b.googleMap,c.id as couponId,(select count(*) from cc_restaurant_menu where restaurant_id =a.customer_id) as menu_count,(select count(*) from 	cc_restaurant_category where restaurant_id =a.customer_id) as cate_count from cc_factory2c_list a left join cc_coupons c  on a.customer_id=c.createUserId and EvoucherOrrealproduct ='restaurant_menu' left join cc_user b on a.customer_id =b.id  where factroy_id=".$this->loginUser['id']. " order by new_application ";
				$data = $mdl_factory2c_list->getListBySql($pageSql);
				//var_dump($data);exit;
				
				if(!$data) {
					$this->sheader(null,'您当前没有销售渠道...');
				}
				$this->setData($data,'factory2c_list');
		
				$this->setData($data, 'data');
	
				$this->setData('sales_channel', 'submenu');
				$this->setData('channel_manage', 'menu');

				$pagename = "销售渠道管理";
				$pageTitle=  $pagename." - 工厂中心 - ". $this->site['pageTitle'];

			$this->setData($pagename, 'pagename');

			$this->setData($pageTitle, 'pageTitle');

			
			$this->display_pc_mobile('factory_2c/sales_channel_management', 'factory_2c/sales_channel_management');
		}

	/**
		 * ajax update menu item
		 */

		function update_menu_item_action(){

			if(is_post()){
				$mdl_restaurant_menu =$this->loadModel("restaurant_menu");
				
				$id = post('id');
				
				$idCreateUser = $mdl_restaurant_menu->get($id);
				
				$mdl  = $this->loadModel('authrise_manage_other_business_account');
				$isAuthoriseCustomer =Authorise_Center::getIsCustomerIdIsAuthorised($this->loginUser['id'],$idCreateUser['restaurant_id']);
				
			   if($idCreateUser['restaurant_id'] != $this->loginUser['id']) {
					if(!$isAuthoriseCustomer) $this->form_response(600,'未发现产品','未发现产品');
			   }
			
			
				$data=array();

				$price = post('price');
				if($price)$data['price']=$price;
				
				$speical_price = post('speical_price');
				if($speical_price)$data['speical_price']=$speical_price;
				
				
				$freshx_price = post('freshx_price');
				if($freshx_price)$data['freshx_price']=$freshx_price;
				
				

				$menu_id = post('menu_id');
				if($menu_id)$data['menu_id']=$menu_id;

				$menu_cn_name = post('menu_cn_name');
				if(isset($menu_cn_name))$data['menu_cn_name']=$menu_cn_name;

				$menu_en_name = post('menu_en_name');
				if(isset($menu_en_name))$data['menu_en_name']=$menu_en_name;
				
				$qty = post('qty');
				if(isset($qty))$data['qty']=$qty;
				
				$limit_buy_qty = post('limit_buy_qty');
				if(isset($limit_buy_qty))$data['limit_buy_qty']=$limit_buy_qty;
				
				$unit = post('unit');
				if(isset($unit))$data['unit']=$unit;

				$include_gst = post('include_gst');
				if(isset($include_gst))$data['include_gst']=$include_gst;

				$menu_desc = post('menu_desc');
				if(isset($menu_desc))$data['menu_desc']=$menu_desc;

				$menu_en_desc = post('menu_en_desc');
				if(isset($menu_en_desc))$data['menu_en_desc']=$menu_en_desc;
				
				$restaurant_category_id = post('restaurant_category_id');
				if(isset($restaurant_category_id))$data['restaurant_category_id']=$restaurant_category_id;

				$sidedish_category = post('sidedish_category');
				if(isset($sidedish_category))$data['sidedish_category']=$sidedish_category;

				$menu_option = post('menu_option');
				if(isset($menu_option))$data['menu_option']=$menu_option;
				
				/*$restaurant_id = post('restaurant_id');
				if(isset($restaurant_id))$data['restaurant_id']=$restaurant_id; */
				
				$menu_order_id = post('menu_order_id');
				if(isset($menu_order_id))$data['menu_order_id']=$menu_order_id;


				try {
					$mdl_restaurant_menu->update($data,$id);
					$mdl_user =$this->loadModel('user');
					$mdl_user->update(array('store_update_time' =>time()),$idCreateUser['restaurant_id']);
					
					
					//修改子店的相关信息

					  $where =array(
						 'source_menu_id'=>$id
					  );
					
					$mdl_restaurant_menu->updateByWhere($data,$where);
					
					// 修改所有店铺的刷新时间 
					//获得所有子店铺信息，并进行全部对应修改
					
					$mdl = $this->loadModel('factory2c_list');
					$subCustomerList =Factory2c_centre::getCustmerLists($this->loginUser['id']);
					
					foreach ($subCustomerList as $key => $value) {  
					 
						$mdl_user->update(array('store_update_time' =>time()),$value);
					
					}
					
					
					$this->form_response(200,'','');
				} catch (Exception $e) {
					$this->form_response(500, $e->getMessage(),'');
				}

			}else{
				//wrong protocol
			}
		}
		
		/**
		 * ajax update menu item
		 */

		function update_sales_item_action(){

			if(is_post()){
				$mdl_factory2c_list =$this->loadModel("factory2c_list");
				
				$id = post('id');
				
				
			
			
				$data=array();

				$simply_name = post('simply_name');
				if($simply_name)$data['simply_name']=$simply_name;
				
				

				try {
					$mdl_factory2c_list->update($data,$id);
				
					$this->form_response(200,'','');
				} catch (Exception $e) {
					$this->form_response(500, $e->getMessage(),'');
				}

			}else{
				//wrong protocol
			}
		}
		
		
		public  function item_producing_set_ajax_action()
		{
			$id = (int)get2('id');
			
			
				
			$mdl_restaurant_menu = $this->loadModel('restaurant_menu');

			$menu = $mdl_restaurant_menu->get($id);
			
			if ($id < 0 || !$menu ) $this->form_response_msg('menu id invalid');
			
		//检查该商家是否可以管理其它店铺，如果授权即可以该商家权限进入系统。
			
			 $mdl = $this->loadModel('authrise_manage_other_business_account');
			 $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);
			
			 $this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');
					
			
			$isAuthoriseCustomer =0 ;
			foreach ($authoriseBusinessList as $key => $value) {
					if($menu['createUserId'] ==$value['customer_id']) {
							$isAuthoriseCustomer =1;
					}
					
				}
			

		

			if ($menu['createUserId'] != $this->current_business['id'] && !$isAuthoriseCustomer  ) $this->form_response_msg('you are not allow to update!');

			$data = array();
			$data['proucing_item'] = ($menu['proucing_item'] == '0') ? '1' : '0';

			if ($mdl_restaurant_menu->update($data, $menu['id'])) {
				
					
				echo json_encode(array('proucing_item' => $data['proucing_item']));
			} else {
				$this->form_response_msg('Please try again later');
			}


		}


            public  function item_print_set_ajax_action()
            {
                $id = (int)get2('id');



                $mdl_restaurant_menu = $this->loadModel('restaurant_menu');

                $menu = $mdl_restaurant_menu->get($id);

                if ($id < 0 || !$menu ) $this->form_response_msg('menu id invalid');

                //检查该商家是否可以管理其它店铺，如果授权即可以该商家权限进入系统。

                $mdl = $this->loadModel('authrise_manage_other_business_account');
                $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);

                $this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');


                $isAuthoriseCustomer =0 ;
                foreach ($authoriseBusinessList as $key => $value) {
                    if($menu['createUserId'] ==$value['customer_id']) {
                        $isAuthoriseCustomer =1;
                    }

                }




                if ($menu['createUserId'] != $this->loginUser['id'] && !$isAuthoriseCustomer  ) $this->form_response_msg('you are not allow to update!');

                $data = array();
                $data['singleItemPrintBuyingList'] = ($menu['singleItemPrintBuyingList'] == '0') ? '1' : '0';

                if ($mdl_restaurant_menu->update($data, $menu['id'])) {


                    echo json_encode(array('singleItemPrintBuyingList' => $data['singleItemPrintBuyingList']));
                } else {
                    $this->form_response_msg('Please try again later');
                }


            }


            public  function menu_publish_ajax_action()
		{
			$id = (int)get2('id');
			
			
				
			$mdl_restaurant_menu = $this->loadModel('restaurant_menu');

			$menu = $mdl_restaurant_menu->get($id);
			
			if ($id < 0 || !$menu ) $this->form_response_msg('menu id invalid');
			
		//检查该商家是否可以管理其它店铺，如果授权即可以该商家权限进入系统。
			
			 $mdl = $this->loadModel('authrise_manage_other_business_account');
			 $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);
			
			 $this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');
					
			
			$isAuthoriseCustomer =0 ;
			foreach ($authoriseBusinessList as $key => $value) {
					if($menu['createUserId'] ==$value['customer_id']) {
							$isAuthoriseCustomer =1;
					}
					
				}
			

		

			if ($menu['createUserId'] != $this->loginUser['id'] && !$isAuthoriseCustomer  ) $this->form_response_msg('you are not allow to update!');

			$data = array();
			$data['visible'] = ($menu['visible'] == '0') ? '1' : '0';

			if ($mdl_restaurant_menu->update($data, $menu['id'])) {
				$mdl_user=$this->loadModel("user");
				$mdl_user->update(array('store_update_time' =>time()),$menu['createUserId']);
				// 插入更新其它销售channel数据
				
				// 更新刷新时间 （这个明天做吧。。。。。）
				// 明早把这个东西处理一下。。。。 就是 修改store_update_time 都集中在一起吧，或者更改数据啥的，看看情况。。。
			
			   // 如果主店上架，分店便上架 ，主店下架，分店下架， 分店可以标记强制下架，如果该种情况，即使主店上架，分店也不上架，这个需要设置一个标价
			   
				  $where =array(
						 'source_menu_id'=> $menu['id']
					  );
					
					$mdl_restaurant_menu->updateByWhere($data,$where);
					
					// 修改所有店铺的刷新时间 
					//获得所有子店铺信息，并进行全部对应修改
					
					$mdl = $this->loadModel('factory2c_list');
					$subCustomerList =Factory2c_centre::getCustmerLists($this->loginUser['id']);
				
					foreach ($subCustomerList as $key => $value) {  
					 
						$mdl_user->update(array('store_update_time' =>time()),$value);
					
					}
				
				
				echo json_encode(array('visible' => $data['visible']));
			} else {
				$this->form_response_msg('Please try again later');
			}


		}
		
		
		public  function set_channel_status_ajax_action()
		{
			$id = (int)get2('id');
			$couponId = (int)get2('couponId');
			
			
				
			$mdl_factory2c_list = $this->loadModel('factory2c_list');

			$channel_rec = $mdl_factory2c_list->get($id);
			
			if ($id < 0 || !$channel_rec ) $this->form_response_msg('menu id invalid');
			
		//检查该商家是否可以管理其它店铺，如果授权即可以该商家权限进入系统。
			
			 $mdl = $this->loadModel('authrise_manage_other_business_account');
			 $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);
			
			 $this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');
					
			
			$isAuthoriseCustomer =0 ;
			foreach ($authoriseBusinessList as $key => $value) {
					if($channel_rec['factroy_id'] ==$value['customer_id']) {
							$isAuthoriseCustomer =1;
					}
					
				}
			

		

			if ($channel_rec['factroy_id'] != $this->loginUser['id'] && !$isAuthoriseCustomer  ) $this->form_response_msg('you are not allow to update!');

			$data = array();
			$data1 = array();
			$data['status'] = ($channel_rec['status'] == '0') ? '1' : '0';
			$data['new_application'] =0;

			if ($mdl_factory2c_list->update($data, $channel_rec['id'])) {
				
				$mdl_coupon =$this->loadModel("coupons");
				if($data['status']) {
						$data1['isApproved']=1;
						$data1['status']=4;
				}else{
					$data1['isApproved']=0;
					$data1['status']=0;
				}
			
				$mdl_coupon->update($data1, $couponId);
				
				echo json_encode(array('status' =>$data['status']));
			} else {
				$this->form_response_msg('Please try again later');
			}


		}
		
		 function menu_publish_ajax1_action()
		{
			$id = (int)get2('id');
			
			
				
			$mdl_restaurant_menu = $this->loadModel('restaurant_menu');

			$menu = $mdl_restaurant_menu->get($id);
			
			if ($id < 0 || !$menu ) $this->form_response_msg('menu id invalid');
			
			 $mdl = $this->loadModel('authrise_manage_other_business_account');
			 $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);
			
			 $this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');
				
			$isAuthoriseCustomer =0 ;
			foreach ($authoriseBusinessList as $key => $value) {
					if($menu['createUserId'] ==$value['customer_id']) {
							$isAuthoriseCustomer =1;
					}
					
				}
			

		

			if ($menu['createUserId'] != $this->loginUser['id'] && !$isAuthoriseCustomer  ) $this->form_response_msg('you are not allow to update!');

			$data = array();
			$data['onSpecial'] = ($menu['onSpecial'] == '0') ? '1' : '0';
			$data['limit_buy_qty'] = 1;

			if ($mdl_restaurant_menu->update($data, $menu['id'])) {
					$mdl_user=$this->loadModel("user");
					$mdl_user->update(array('store_update_time' =>time()),$menu['createUserId']);
					$where =array(
						 'source_menu_id'=> $menu['id']
					  );
					
					$mdl_restaurant_menu->updateByWhere($data,$where);
					
					// 修改所有店铺的刷新时间 
					//获得所有子店铺信息，并进行全部对应修改
					
					$mdl = $this->loadModel('factory2c_list');
					$subCustomerList =Factory2c_centre::getCustmerLists($this->loginUser['id']);
				
					foreach ($subCustomerList as $key => $value) {  
					 
						$mdl_user->update(array('store_update_time' =>time()),$value);
					
				
				}
				
				
				
				
				
				
				echo json_encode(array('onSpecial' => $data['onSpecial']));
			} else {
				$this->form_response_msg('Please try again later');
			}


		}
		
		
		public function become_sales_channel_action(){
			
			$factory_id=get2('factory_id');
			
			$mdl_factory2c_list =$this->loadModel('factory2c_list');
			
			if($factory_id ==$this->loginUser['id']) {
				$note ="不能添加自己！";
				
				
			}else{
				
				$note ="申请成功";
			}
			
			$this->setData($note,'note');
			
			$data=array();
			
			$data['factroy_id']=$factory_id;
			$data['customer_id']=$this->loginUser['id'];
			$data['status']=0;
			$data['simply_name']=$this->loginUser['name'];
			
			$mdl_factory2c_list ->insert($data);
			
			
			
			$this->display('factory_2c/sales_channel_application_success');
			
			
			
			
			
			
			
			
		}
		
		
		/**
	 * 该页面为公开页面。
	 * 用于直接或间接分享邀请媒体人参加
	 */
	public function sales_chaneel_invite_page_action()
	{	
		$factory_id = get2('id');
		if(!$factory_id)$this->sheader(null,'缺少ID');

		


		$url = HTTP_ROOT.'factory_2c/become_sales_channel?factory_id='.$id;
		$qrcode = generateQRCode($url);

		$this->setData($url,'applyUrl');
		$this->setData($qrcode,'applyqrcode');


		//wx share
		require_once "wx/wxjssdk.php";
        $jssdk = new WXjsSDK();
        $signPackage = $jssdk->GetSignPackage();
        $this->setData($signPackage,'signPackage');

      

			$this->setData( 'channel_manage', 'menu' );
			$this->setData( 'sales_chaneel_invite', 'submenu' );
			$this->setData( '渠道邀请二维码 - '.$this->site['pageTitle'], 'pageTitle' );

			$this->display_pc_mobile('factory_2c/sales_chaneel_invite_page_pc','factory_2c/sales_chaneel_invite_page');

      
	}
		
			function menu_pic_action()
			{
				$id =get2('id');
				$this->setData($id,'id');

				if (is_post()) {
					$id =post('id');
					$images = post('images');

					foreach ($images as $key => $value) {
						if($value=="default/image_upload.jpg")
							unset($images[$key]);
						else
							$images[$key]=trim($value);
					}

					$data=array(
						'menu_pic'=>trim(reset($images))
					);

					
					$mdl_restaurant_menu = $this->loadModel('restaurant_menu');
					if ($mdl_restaurant_menu->update($data,$id)) {
					   $mdl_user =$this->loadModel('user');
					   $mdl_user->update(array('store_update_time' =>time()),$this->loginUser['id']);
						//修改子店的相关信息

					  $where =array(
						 'source_menu_id'=>$id
					  );
					
					$mdl_restaurant_menu->updateByWhere($data,$where);
					
					// 修改所有店铺的刷新时间 
					//获得所有子店铺信息，并进行全部对应修改
					//var_dump($where);exit;
					
					$mdl = $this->loadModel('factory2c_list');
					$subCustomerList =Factory2c_centre::getCustmerLists($this->loginUser['id']);
					
					foreach ($subCustomerList as $key => $value) {  
					 
						$mdl_user->update(array('store_update_time' =>time()),$value);
					
					}
						
						$this->form_response_msg( '保存成功');
					} else {

						$this->form_response_msg('保存失败');
					}
				} else {
					$this->setData('添加菜单图片 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
					$this->setData('restaurant_menu', 'submenu');
					$this->setData('restaurant', 'menu');
					$this->display('factory_2c/menu_pic');
				}
			}	
			
			
	
	public function generate_restaurant_static_file_action(){

		
			$id  =get2('id');
			$type=get2('type');
			
			
			if(!$id)$this->sheader(null,'error!');
			
			// 获取的餐馆ID--是商家的ID ,判断当前商家是否设置餐厅入口
			$where =array(
				'createUserId' => $id,
				'EvoucherOrrealproduct' =>'restaurant_menu'
			);
			
			
			$mdl_user=$this->loadModel('user');
			$mdl_coupons =$this->loadModel("coupons");
			
			$restaurant_coupon= $mdl_coupons->getByWhere($where);
			
			if(($restaurant_coupon['isApproved']==1 && $restaurant_coupon['status']==4) || $restaurant_coupon['createUserId']==$id  || $_SESSION['coupon_private_view_allowed']==$restaurant_coupon['id']) {
				
				$business_user =$mdl_user->get($restaurant_coupon['createUserId']);
				$restaurant_coupon['business']=$business_user;
				
				$this->setData( $restaurant_coupon, 'coupon' );
				$refresh_code_old =$business_user['business_store_refresh_code'];
				//获得自己店的其它产品

				$this->setData( $restaurant_coupon['id'], 'restaurant_couponID' );
				
			}else{
				$this->sheader(HTTP_ROOT_WWW.'coupon1/coupon_private_view_gate?id='.$restaurant_coupon['id']);
				$this->sheader(null,'当前商家还未开启线上餐厅,请稍后..');
				
			}
			
			//获得 该厂家的更多的子商家
			
			$mdl = $this->loadModel('factory2c_list');
			$subCustomerList =Factory2c_centre::getCustmerLists($id);
			
			
			$this->generate_restaurant_static_file($id,$type,$refresh_code_old);
			 
			$mdl_user->update(array('store_update_time' =>time()),$id);
			
			foreach ($subCustomerList as $key => $value) {  
				$where =array(
					'createUserId' => $value,
					'EvoucherOrrealproduct' =>'restaurant_menu'
				);
				$restaurant_coupon= $mdl_coupons->getByWhere($where); //fetch模板需要下面信息
				$this->setData( $restaurant_coupon, 'coupon' );
				$this->setData( $restaurant_coupon['id'], 'restaurant_couponID' );
				$current_user1 =$mdl_user->get($value);
				$refresh_code_old =$current_user1['business_store_refresh_code'];
				$this->generate_restaurant_static_file($value,$type,$refresh_code_old);
			 
				$mdl_user->update(array('store_update_time' =>time()),$value);
			
			}
			
		
			var_dump('刷新成功');exit;
			
	}		
			
			


	
	 function generate_restaurant_static_file($id,$type,$refresh_code_old){

			$mdl_user=$this->loadModel('user');
			  $mdl_coupons =$this->loadModel("coupons");
			
			  $mdl_restaurant_menu = $this->loadModel( 'restaurant_menu' );
			
			  // 当用户调用页面时，保证调用新的页面，而不是缓存得旧页面


				$refresh_code =time();

				$data=array(
					'business_store_refresh_code'=>$refresh_code,
					'store_fresh_time'=>$refresh_code

				);
				$mdl_user->update($data,$id);
				
			
			
		   $restaurant_promotion_manjian_rates=0;


			//菜单列表
			$sql="select c.category_cn_name, c.category_en_name,a.*,b.category_id as restaurant_category_id,b.category_sort_id from cc_restaurant_category c,cc_restaurant_menu a, cc_restaurant_category b where (a.restaurant_id=".$id . "  or a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id." )) and  a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id.")   and (length(a.menu_cn_name)>0 or length(a.menu_en_name)>0) and  c.id=a.restaurant_category_id  and b.id = a.restaurant_category_id and a.visible=1 order by b.category_sort_id,a.menu_order_id,a.menu_id";
			$menu = $mdl_restaurant_menu->getListBySql($sql);
			
			//$sql_special="select '本期特价' as category_cn_name, 'On Sale' as category_en_name,a.*,'1000' as  restaurant_category_id,'1' as category_sort_id from cc_restaurant_category c,cc_restaurant_menu a, cc_restaurant_category b where (a.restaurant_id=".$id . "  or a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id." )) and a.onSpecial =1 and  a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id.")   and (length(a.menu_cn_name)>0 or length(a.menu_en_name)>0) and  c.id=a.restaurant_category_id  and b.id = a.restaurant_category_id and a.visible=1 order by a.menu_order_id,a.menu_id";
			$sql_special="select '本期特价' as category_cn_name, 'On Sale' as category_en_name,a.*,'1000' as restaurant_category_id,'1' as category_sort_id from cc_restaurant_menu a  where a.restaurant_id=$id and a.onSpecial =1  and (length(a.menu_cn_name)>0 or length(a.menu_en_name)>0) and a.visible=1 order by a.menu_order_id,a.menu_id";
			
			$menu_sub = $mdl_restaurant_menu->getListBySql($sql_special);
			//var_dump($sql_special);exit;
			foreach ($menu_sub as $key => $value) {
				$menu_sub[$key]['price'] =$value['speical_price'];
				$menu_sub[$key]['onSpecial'] =1;
				
				if($value['original_price']<=0) { //如果原价为空
				
						$menu_sub[$key]['original_price'] =$value['price'];
				
					
				}
				if($value['limit_buy_qty']>0) {
					$menu_sub[$key]['menu_cn_name']='('.$this->lang->limit_buy.' '. $value['limit_buy_qty'].' '.$this->lang->original_price.$menu_sub[$key]['original_price'].')'.$menu_sub[$key]['menu_cn_name'];;
				}else{
					$menu_sub[$key]['menu_cn_name']='('.$this->lang->original_price.$menu_sub[$key]['original_price'].')'.$menu_sub[$key]['menu_cn_name'];;
					
				}
			}
			
			foreach ($menu as $key => $value) {
				$menu[$key]['onSpecial'] =0; //之前的on sepcial 都已提出了，下面再常规类别中显示的都可以不视为special
			if($value['original_price']<=0) { //如果原价为空
					
						$menu[$key]['original_price'] =$value['price'];
					
					
				}
			}
			//array_unshift($menu,$menu_sub);
			$menu=array_merge($menu_sub,$menu);
			
			
			//////    做到这里 ，这块 的 价格 需要替换成 特价  加紧购物车价格有问题 / 未登陆的用户还没有处理 / 原价显示不对 （原价显示逻辑是 如果原价未0 ，则找现价未原价， 之前的原价，如果未空或与现价相等则不显示
		//  var_dump($menu);exit; //  需要检查中文/英文 /登陆/未登录的所有场景 
			//显示新价格
			foreach ($menu as $key => $value) {
				//$menu[$key]['onSpecial'] =0; //之前的on sepcial 都已提出了，下面再常规类别中显示的都可以不视为special
				if ($this->getLangStr() =='en') {
					if(!$menu[$key]['menu_en_name']) {
						$menu[$key]['menu_en_name']=$menu[$key]['menu_cn_name'];
					}
					
				}
				
				$menu[$key]['new_price'] =number_format($menu[$key]['price'] *(1-$restaurant_promotion_manjian_rates),2);

				
			}

			//加载配菜
			$mdl_sidedish_menu=$this->loadModel('restaurant_sidedish_menu');
			foreach ($menu as $key => $value) {
				if($menu[$key]['sidedish_category']>0){
					$menu[$key]['sidedish_menu']=$mdl_sidedish_menu->getList(null,array('restaurant_id'=>$menu[$key]['restaurant_id'],'restaurant_category_id'=>$menu[$key]['sidedish_category']," (length(menu_cn_name)>0 or length(menu_en_name)>0) "));

					foreach ($menu[$key]['sidedish_menu'] as $k => $v) {
						//配菜新价格
						$menu[$key]['sidedish_menu'][$k]['new_price'] =number_format($menu[$key]['sidedish_menu'][$k]['price'] *(1-$restaurant_promotion_manjian_rates),2);
					}
					
				}
			}

			//加载菜品规格
			$mdl_menu_option=$this->loadModel('restaurant_menu_option');
			foreach ($menu as $key => $value) {
				if($menu[$key]['menu_option']>0){
					$menu[$key]['menu_option_list']=$mdl_menu_option->getList(null,array('restaurant_id'=>$menu[$key]['restaurant_id'],'restaurant_category_id'=>$menu[$key]['menu_option']," (length(menu_cn_name)>0 or length(menu_en_name)>0) "));

					foreach ($menu[$key]['menu_option_list'] as $k => $v) {
						//配菜新价格
						$menu[$key]['menu_option_list'][$k]['new_price'] =number_format($menu[$key]['menu_option_list'][$k]['price'] *(1-$restaurant_promotion_manjian_rates),2);
					}
					
				}
			}
			
			
				
				// 换 en cn 
			$old_cat="";
			foreach ($menu as $key => $value) {
				$new_cat =$menu[$key]['category_cn_name'];
				// 如果en不为空，则保存en cate名称 
				if ($menu[$key]['category_en_name']) {
					$new_cat_en =$menu[$key]['category_en_name'];
				}else{
					// 如果英文分类为空，换中文
					$menu[$key]['category_en_name']=$menu[$key]['category_cn_name'];
					$new_cat_en =$new_cat;
					
				}
				if($old_cat<>$new_cat) {
					$menu[$key]['new_cat']=$new_cat;
					$menu[$key]['new_cat_en']=$new_cat_en;
					$old_cat=$new_cat;
				}else{
					$menu[$key]['new_cat']=0;
					$menu[$key]['new_cat_en']=0;
				}
				
				
			}

			if ( count($menu)<=7) {
				
					$menu_en = $this->fetch('/mobile/restaurant/menu_en');
					$menu = $this->fetch('/mobile/restaurant/menu');
				



				$filename = DATA_DIR.'upload/htm/restaurant/menu_'.$id.$refresh_code.'.htm';
				if(!is_file($filename)){
					$fh = fopen($filename, "w"); //w从开头写入 a追加写入
					fwrite($fh, $menu);
					fclose($fh);
				}
				
				$filename = DATA_DIR.'upload/htm/restaurant/menu_'.$id.'_en'.$refresh_code.'.htm';
				if(!is_file($filename)){
					$fh = fopen($filename, "w"); //w从开头写入 a追加写入
					fwrite($fh, $menu_en);
					fclose($fh);
				}
				
				if ($this->getLangStr()=='en'){
					echo $menu_en; return;
				}else{
					echo $menu; return;
					
				}
			}else{


				$this->setData( $restaurant_promotion_manjian_rates*100, 'restaurant_promotion_manjian_rates' );


				$menu1 =array_slice($menu,0,7);

				$us=$this->getUserDevice();

				$this->setData( $menu1, 'menu' );
				$this->setData( 0,'lazyload');
				$this->setData( 0,'start_index');
				
				
				if($type !='cn') {
					$menu1_en = $this->fetch('/mobile/restaurant/menu_en');
					$filename =DATA_DIR. 'upload/htm/restaurant/menu_'.$id.'_en'.$refresh_code.'.htm';
					if(!is_file($filename)){
						$fh = fopen($filename, "w"); //w从开头写入 a追加写入
						fwrite($fh, $menu1_en);
						fclose($fh);
					}
					
				}
				
					
				$menu1 = $this->fetch('/mobile/restaurant/menu');
				
				$filename =DATA_DIR. 'upload/htm/restaurant/menu_'.$id.$refresh_code.'.htm';
				if(!is_file($filename)){
					$fh = fopen($filename, "w"); //w从开头写入 a追加写入
					fwrite($fh, $menu1);
					fclose($fh);
				}
				
				
				
				//var_dump($menu);exit;
				$menu2 =array_slice($menu,7);
				$this->setData( 1,'lazyload');
				$this->setData( 7,'start_index');
				$us=$this->getUserDevice();
				$this->setData( $menu2, 'menu' );
				
				
				
					
				$menu2 = $this->fetch('/mobile/restaurant/menu');
				
				if($type !='cn') {
					$menu2_en = $this->fetch('/mobile/restaurant/menu_en');
					$filename = DATA_DIR.'upload/htm/restaurant/menu_'.$id.'_1_en'.$refresh_code.'.htm';
					if(!is_file($filename)){
						$fh = fopen($filename, "w"); //w从开头写入 a追加写入
						fwrite($fh, $menu2_en);
						fclose($fh);
					}
				}

				$filename = DATA_DIR.'upload/htm/restaurant/menu_'.$id.'_1'.$refresh_code.'.htm';
				if(!is_file($filename)){
					$fh = fopen($filename, "w"); //w从开头写入 a追加写入
					fwrite($fh, $menu2);
					fclose($fh);
				}
				
			}
			
			
			
				$mdl_restaurant_category=$this->loadModel('restaurant_category');
				$restaurant_category=$mdl_restaurant_category->getListBySql("select * from cc_restaurant_category where restaurant_id = ".$id . " and (length(category_cn_name)>0 or length(category_en_name)>0) order by `category_sort_id`");

				foreach ($restaurant_category as $key => $value) { 
					if($restaurant_category[$key]['category_en_name']=='') {
						$restaurant_category[$key]['category_en_name']=$restaurant_category[$key]['category_cn_name'];
					}
				}


			// 如果发现有special菜单，那么生成一个新的类别编号，并置顶 
			$mdl_restaurant_menu = $this->loadModel( 'restaurant_menu' );
			$sql_special  = "select count(*) as count from cc_restaurant_menu a where a.restaurant_id =$id and  (length(a.menu_cn_name)>0 or length(a.menu_en_name)>0) and a.visible=1 and a.onSpecial =1  ";
			//var_dump($sql_special);exit;
			$exist_special =$mdl_restaurant_menu->getListBySql($sql_special);
			if($exist_special[0]['count']>0) {
				
				///var_dump($exist_special);exit;
				
				$special_array = array(
				   'restaurant_id'=>$id,
				   'category_id'=>1000,
				   'category_sort_id'=>10,
				   'category_cn_name'=>'本期优惠',
				   'category_en_name'=>'On Sale',
				   'createUserId'=>$id,
				   'ref_restaurant_id'=>0,
				   'ref_DishTypeId'=>0,
				   'hot'=>1
				
				);
				array_unshift($restaurant_category,$special_array);
				
				//var_dump($restaurant_category);exit;
				
			}
			
		 

		   // 获取该商家的所有coupon 及子coupon 并列表
				$coupon_list =$mdl_coupons->getAllCouponsofUser($id);



		   //如果当前用户购买了那个coupon ,则直接更改其购买的数量
				foreach ($coupon_list as $key => $value) {
					
						$coupon_list[$key]['qty']=0;
					
				}

				//$this->setData( $coupon_list, 'coupon_list' );


				$this->setData( $restaurant_category, 'restaurant_category' );

				
				
				$category_list = $this->fetch('/mobile/restaurant/category');
				
				$coupon_list = $this->fetch('/mobile/restaurant/coupon_list');
				$title_promotion =$this->fetch('/mobile/restaurant/title_promotion');
			
				
					
				$filename =DATA_DIR. 'upload/htm/restaurant/category_'.$id.$refresh_code.'.htm';
				
				if(!is_file($filename)){
					$fh = fopen($filename, "w"); //w从开头写入 a追加写入
					fwrite($fh, $category_list);
					fclose($fh);
				}
				
				if($type !='cn') {
					
					$category_list_en = $this->fetch('/mobile/restaurant/category_en');
					$title_promotion_en =$this->fetch('/mobile/restaurant/title_promotion_en');
					$filename =DATA_DIR. 'upload/htm/restaurant/category_'.$id.'_en'.$refresh_code.'.htm';
					if(!is_file($filename)){
						$fh = fopen($filename, "w"); //w从开头写入 a追加写入
						fwrite($fh, $category_list_en);
						fclose($fh);
					}
					
					$filename =DATA_DIR. 'upload/htm/restaurant/title_promotion_'.$id.'_en'.$refresh_code.'.htm';
					if(!is_file($filename)){
						$fh = fopen($filename, "w"); //w从开头写入 a追加写入
						fwrite($fh, $title_promotion_en);
						fclose($fh);
					}
				}
				
				$filename =DATA_DIR. 'upload/htm/restaurant/title_promotion_'.$id.$refresh_code.'.htm';
				if(!is_file($filename)){
					$fh = fopen($filename, "w"); //w从开头写入 a追加写入
					fwrite($fh, $title_promotion);
					fclose($fh);
				}
				
				
				$filename =DATA_DIR. 'upload/htm/restaurant/coupon_list_'.$id.$refresh_code.'.htm';
				if(!is_file($filename)){
					$fh = fopen($filename, "w"); //w从开头写入 a追加写入
					fwrite($fh, $coupon_list);
					fclose($fh);
				}
				
				//删除该商家的临时文件。 该操作应该在新的文件生成之后。
				$this->restaurantStaticFileDeleteProcess($id,$refresh_code_old);
				
				
			
			
	}		
			
			


	public  function refresh_menu_action() {

				$business_id= get2('business_id');

				if(!$business_id) {
					
					$business_id =$this->loginUser['id'];
				}
				
				
			 $mdl = $this->loadModel('authrise_manage_other_business_account');
			 $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);
			
			 $this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');
				
				$isAuthoriseCustomer =0 ;
				foreach ($authoriseBusinessList as $key => $value) {
						if($business_id ==$value['customer_id']) {
								$isAuthoriseCustomer =1;
								
						}
						
					}
			
			
				
				if ($this->loginUser['id'] != $business_id  &&  !$isAuthoriseCustomer) {

					$this->sheader(null,'您无权使用该功能');
				}	//var_dump($this->loginUser['id'] != $business_id);exit;

			// 将所有文件清除
				$filename = DATA_DIR.'upload/htm/restaurant/menu_'.$business_id.'.htm';
				unlink($filename); 
				$filename = DATA_DIR.'upload/htm/restaurant/menu_'.$business_id.'_en.htm';
				unlink($filename); 	   
				$filename = DATA_DIR.'upload/htm/restaurant/menu_'.$business_id.'_1.htm';
				unlink($filename);    
				$filename = DATA_DIR.'upload/htm/restaurant/menu_'.$business_id.'_1_en.htm';
				unlink($filename);  
				$filename = DATA_DIR.'upload/htm/restaurant/category_'.$business_id.'.htm';
				unlink($filename);  
				$filename = DATA_DIR.'upload/htm/restaurant/category_'.$business_id.'_en.htm';
				unlink($filename); 
				$filename = DATA_DIR.'upload/htm/restaurant/coupon_list_'.$business_id.'_en.htm';
				unlink($filename);  
				$filename = DATA_DIR.'upload/htm/restaurant/coupon_list_'.$business_id.'.htm';
				unlink($filename);  	   
				$filename = DATA_DIR.'upload/htm/restaurant/title_promotion_'.$business_id.'.htm';
				unlink($filename);  
				$filename = DATA_DIR.'upload/htm/restaurant/title_promotion_'.$business_id.'_en.htm';
				unlink($filename);  

		   //生成新的刷新码
		   // 当用户调用页面时，保证调用新的页面，而不是缓存得旧页面


				$refresh_code =time();

				$data=array(
					'business_store_refresh_code'=>$refresh_code,
					'store_fresh_time'=>$refresh_code

				);
				$this->loadModel('user')->update($data,$business_id);



		   // 重新运行页面，生成新的文件。


				$this->sheader(HTTP_ROOT_WWW.'restaurant/'. $business_id.'?force=1');
			}	
			


		
		/**
		 *  Ajax update Category item
		 */
		
		public function update_category_item_action()
		{
			if(is_post()){

				$mdl_restaurant_category =$this->loadModel("restaurant_category");

				$id = post('id');
				
				
				$idCreateUser = $mdl_restaurant_category->get($id);
				$mdl  = $this->loadModel('authrise_manage_other_business_account');
				$isAuthoriseCustomer =Authorise_Center::getIsCustomerIdIsAuthorised($this->loginUser['id'],$idCreateUser['restaurant_id']);
				
			   if($idCreateUser['restaurant_id'] != $this->loginUser['id']) {
					if(!$isAuthoriseCustomer ) $this->form_response(600,'未发现产品','未发现产品');
			   }
			

				
				$data=array();

				$category_sort_id = post('category_sort_id');
				if($category_sort_id)$data['category_sort_id']=$category_sort_id;

				$category_en_name = post('category_en_name');
				if(isset($category_en_name))$data['category_en_name']=$category_en_name;

				$category_cn_name = post('category_cn_name');
				if(isset($category_cn_name))$data['category_cn_name']=$category_cn_name;

				$hot = post('hot');
				if(isset($hot))$data['hot']=$hot;
				try {
					
					//先修改主店铺信息
					$mdl_restaurant_category->update($data,$id);
					$mdl_user =$this->loadModel('user');
					$mdl_user->update(array('store_update_time' =>time()),$idCreateUser['restaurant_id']);
					
					//修改子店的相关信息

					  $where =array(
						 'source_category_id'=>$id
					  );
					
					$mdl_restaurant_category->updateByWhere($data,$where);
					
					// 修改所有店铺的刷新时间 
					//获得所有子店铺信息，并进行全部对应修改
					
					$mdl = $this->loadModel('factory2c_list');
					$subCustomerList =Factory2c_centre::getCustmerLists($this->loginUser['id']);
					
					foreach ($subCustomerList as $key => $value) {  
					 
						$mdl_user->update(array('store_update_time' =>time()),$value);
					
					}
					//$data11=array();
					//$data11['msg'] =$subCustomerList[0]['customer_id'];
					// return  $data11; 
					//$data['status'] = 200;
					//$data['msg'] = $this->loginUser['id'];
				   // echo json_encode($data);
				   // exit;
					$this->form_response(200,(string)$subCustomerList[0]['customer_id'],'');
				} catch (Exception $e) {
					$this->form_response(500, $e->getMessage(),'');
				}

			}else{
				//wrong protocol
			}
		}
		
	//店铺拷贝功能
	public function  restaurant_copy_action() {
		
		// 接收 from  to   参数为餐馆编号或者说商家编号
		 $from = get2('from');
		 $to = get2('to');
		// 检查 to 是否已经建立 
		
		
		
		if( !$from  ) {
			
			var_dump('来源商家不明');exit;
		}
		//检查当前登陆的用户是否为被拷贝的商家的所有者
		
		if( $this->loginUser['id'] != $from   ) {
			
			var_dump('商家不匹配！');exit;
		}
		
		
		
		if( !$to   ) {
			
			var_dump('目标商家不明');exit;
		}else{
			
			
			//var_dump($this->loginUser['id']);exit;
			$mdl_restaurant_category =$this->loadModel('restaurant_category');
			$mdl_restaurant_menu =$this->loadModel('restaurant_menu');
			$mdl_coupon =$this->loadModel('coupons');
		
					
			// 如果目标数据库已经有数据了，则不能再进行copy
			
			$where_category_count =array(
			   'restaurant_id' => $to
			);
			
			$where_coupon_count =array(
			   'createUserId' => $to
			);		
			$category_count = $mdl_restaurant_category->getCount($where_category_count);
			
			$menu_count = $mdl_restaurant_menu->getCount($where_category_count);
			
			if($category_count ) {
					var_dump('已存在 category count:'.$category_count); exit;
				
			}
			
			if($menu_count) {
					var_dump( '已存在 menu_count :'. $menu_count); exit;
				
			}
				
			$coupon_count =$mdl_coupon->getCount($where_coupon_count);
			
			if (!$coupon_count){
				var_dump( '还未建立超市卡片 '.$coupon_count); exit;
				
			}
			
				//检查目标商家是否已经创建了coupon 
				
				
				// 检查目标商家是否已经创建了数据
				
				
			
			
		}
		
		
		
		



	   $sql = "select * from cc_restaurant_category where restaurant_id =$from";
	   
	   $category_rec = $mdl_restaurant_category->getListBySql($sql) ;
	   
	  
	   $mdl_restaurant_category->begin();
	   if ($category_rec) {
		   //var_dump($category_rec);exit;
			// 逐条检查并插入新商家的分类
			$data_category=array();
			foreach ($category_rec as $key => $value) { 
			 
			  $data_category['restaurant_id'] =$to;
			  $data_category['category_id']=$value['category_id'];
			  $data_category['category_sort_id']=$value['category_sort_id'];
			  $data_category['category_cn_name']=$value['category_cn_name'];
			  $data_category['category_en_name']=$value['category_en_name'];
			  $data_category['createUserId']=$to;
			  $data_category['ref_restaurant_id']=$value['ref_restaurant_id'];
			  $data_category['ref_DishTypeId']=$value['ref_DishTypeId'];
			  $data_category['hot']=$value['hot'];
			  $data_category['source_category_id']=$value['id'];
			  
			  
			 $new_id = $mdl_restaurant_category->insert($data_category);
			  
			 //$new_id = 333333;

			 if($new_id) {
				  
				 // var_dump($new_id);exit;
				 
				  //获得原餐馆的该分类下的数据，进入插入新的menu数据
				  $cate_id =$value['id'];
				  $where_menu_rec ="select * from cc_restaurant_menu where restaurant_id = $from and restaurant_category_id =$cate_id";
				  
				  
				  
				  
				  $data_current_category_menu_rec =$mdl_restaurant_menu->getListBySql($where_menu_rec);
				 // var_dump(count($data_current_category_menu_rec));exit;
				  
				  
				  $data_new_menu=array();
				  foreach ($data_current_category_menu_rec as $key1 => $val) { 
				  
				   
				  
				   $data_new_menu['restaurant_id'] = $to;
				   $data_new_menu['restaurant_category_id'] = $new_id;
				   $data_new_menu['menu_id'] = $val['menu_id'];
				   $data_new_menu['guige_group_id_1'] = $val['guige_group_id_1']    ;
				   $data_new_menu['menu_cn_name'] = $val['menu_cn_name'] ;
				   $data_new_menu['price'] = $val['price']  ;
				   $data_new_menu['original_price'] = $val['original_price']  ;
				   $data_new_menu['limit_buy_qty'] = $val['limit_buy_qty']  ;
				   $data_new_menu['speical_price'] = $val['speical_price']  ;
				    
				   $data_new_menu['freshx_price'] = $val['freshx_price']  ;
				   $data_new_menu['guige_group_id_2'] = $val['guige_group_id_2']  ;  
				   $data_new_menu['menu_pic'] = $val['menu_pic'] ;
				   $data_new_menu['menu_desc'] = $val['menu_desc']  ;
				   $data_new_menu['menu_en_name'] = $val['menu_en_name']  ;
				   $data_new_menu['sidedish_category'] = $val['sidedish_category']   ; 
				   $data_new_menu['menu_option'] = $val['menu_option'] ;
				   $data_new_menu['createUserId'] = $to; 
				   $data_new_menu['ref_restaurant_id'] = $val['ref_restaurant_id']  ; 
				   $data_new_menu['men_desc-bk'] = $val['men_desc-bk']  ;			   
				   $data_new_menu['deliver_price_bk'] = $val['deliver_price_bk']  ;
				   $data_new_menu['visible'] = $val['visible']  ;
				   $data_new_menu['menu_order_id'] = $val['menu_order_id']  ;			   
				   $data_new_menu['qty'] = $val['qty']  ;
				   $data_new_menu['unit'] = $val['unit']  ;
				   $data_new_menu['onSpecial'] = $val['onSpecial']  ;
				   $data_new_menu['commission_free'] = $val['commission_free']  ;
				   $data_new_menu['source_menu_id'] =$val['id']    ;
				   
				   if($mdl_restaurant_menu->insert($data_new_menu)) {
					   //	var_dump('插入成功');exit;			   				   
				   }else{
					   $mdl_restaurant_category->rollback();
					   $failofoperation =1;
				   }
				   
				   
				   
				   
				   
				  }
			  }else{
				   $failofoperation =1; 
				  
			  }
			  
			  
			}
		   
		   
	   }
	   
	   
		 if( !$failofoperation){
			 
			 $mdl_restaurant_category->commit();
			$this->sheader(HTTP_ROOT_WWW . 'factory_2c/sales_channel_management');
			 
		 }else{
			  var_dump('复制失败');
			 
		 }
		 
		// 拷贝分类数据 拷贝分类数据的时候，变化是自动增加的， 所以  要做一个循环处理。
		
		
		// 拷贝 菜单数据
		
		
		// 拷贝结束
		
		
		
		 
		 
		 
		
	}


	 public function factroy_order_summery_action()
    {
  
        $mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');
        $mdl_order = $this->loadModel('order');
        $mdl_user= $this->loadModel('user');


        $three_days_times = time()-60*60*24*7;
		$mdl = $this->loadModel('factory2c_list');
	    $availableDates = Factory2c_centre::getAvaliableDateOfAllSalesChannelOfThisFactory($this->current_business['id']);
    	$availableDates = array_map(function($d){
    		return date('Y-m-d',$d['logistic_delivery_date']);
    	}, $availableDates);
    	$this->setData($availableDates, 'availableDates');
		//var_dump($availableDates);exit;
  
  	  //查看是否打印总单和所有渠道配货单
		$totalandeverychannelPrint =get2('printAllBusiness');


        //** 获取该商家管辖工厂渠道商家
		
		$mdl = $this->loadModel('factory2c_list');
		$SalesChannelCustomerList= Factory2c_centre::getCustmerListsIncludeFactoryWithBusinessName($this->current_business['id'],$this->current_business['displayName']);
		$mdl = $this->loadModel('factory_2blist');
		$SalesChannelCustomerList2b= Factory2b_centre::getCustmerListsIncludeFactoryWithBusinessName($this->current_business['id'],$this->current_business['displayName']);
	    
		$SalesChannelCustomerList =array_merge($SalesChannelCustomerList,$SalesChannelCustomerList2b);
		//var_dump($SalesChannelCustomerList);exit;
		$this->setData($SalesChannelCustomerList, 'SalesChannelCustomerList');
		
	


		//交易状态购买
		//if(!status) {
		$status ='c01';
		//}
		//支付状态
		$ifpaid=1;
		
		 $business_id = trim(get2('business_id'));
		 if($business_id) {
			 $business_user = $mdl_user->get($business_id) ;
			 $business_tradingName=$business_user ['displayName'];
		 } else{
			 $business_tradingName=$this->current_business['displayName'];
		 }
		 
		// 做到这里，如果 是suppliersID 且数据源!=1 则要使用cc_order_import 做为引导。
		
		if($this->loadModel('dispatching_centre_customer_list')->getIfBusinessIsExportDataSource($business_id)) {
			$export_data_source =1;
			$query_table_name='cc_order_import';
		}else{
			$export_data_source =0;
			$query_table_name='cc_order';
		}
		
		 //var_dump($export_data_source);exit;
		 $this->setData($business_id,'business_id');

        //获取是否为生产类
        $producing = trim(get2('producing'));
        if(!$producing) $producing =0;
        $this->setData($producing,'producing');
		
        $sk = trim(get2('sk'));
        $this->setData($sk,'sk');
		
		$customer_delivery_date = trim(get2('customer_delivery_date'));
		$this->setData($customer_delivery_date,'customer_delivery_date');

        //获取当前用户点击的大类
        $cate_id = trim(get2('cate_id'));
        $this->setData($cate_id,'cate_id');

        //获取当前用户点击的大类
        $logistic_truck_No = trim(get2('logistic_truck_No'));
        $this->setData($logistic_truck_No,'logistic_truck_No');

        $TuckListOfTheDay =$this->loadModel('truck')->getAllOrdersTruckListwithCount($this->current_business['id'],$customer_delivery_date);
        $this->setData($TuckListOfTheDay,'TuckListOfTheDay');

        // 加入了一个前面可以选择一个商家，然后显示该商家的相关记录，如果商家id 为空，则保持原来的处理，如果不为空则进行相应的处理
		//获得该商家是否为外部数据源，如果是外部数据源，则需要使用外部订单总表关联
		//var_dump($business_id);exit;
		  $sql= Factory2c_centre::getSqlofAllOrdersDataOfCurrentBusiness($business_id,$query_table_name,$this->current_business['id']);
		
		  if($totalandeverychannelPrint) { //如果是同时打印总单及分单 
			    $sql1= Factory2c_centre::getSqlofAllOrdersDataOfCurrentBusiness($business_id,$query_table_name,$this->current_business['id'],$totalandeverychannelPrint);
	
		  }
	 
         //var_dump($sql);exit;
        if (!empty($sk)) {
            $whereStr.=" and ( c.bonus_title like  '%" . $sk . "%'";
            $whereStr.=" or cate.category_cn_name like  '%" . $sk . "%'";
            $whereStr.=" or cate.category_cn_name like  '%" . $sk . "%'";
            $whereStr.=" or r.menu_id like  '%" . $sk . "%'";
            $whereStr.=" or r.menu_en_name like  '%" . $sk . "%'";
            $whereStr.=" or r.menu_cn_name like  '%" . $sk . "%'";
			$whereStr.=" or c.business_id like  '%" . $sk . "%')";
            $where[]=$whereStr;
        }


        if (!empty($cate_id)) {
            if($cate_id !='all') {
                $whereStr.=" and r.restaurant_category_id =$cate_id ";
            }
        }

        if (!empty($logistic_truck_No)) {
            if($logistic_truck_No !='all') {
                $whereStr.=" and o.logistic_truck_No =$logistic_truck_No ";
            }
        }

       // 如果为生产类，加入条件
        if ($producing) {
            $whereStr.=" and r.proucing_item =1 ";
        }else{
            $whereStr.=" and r.proucing_item =0 ";
        }

		//deleivery date
		if (!empty($customer_delivery_date)) {
            if ($customer_delivery_date != 'all') {
               $whereStr.= " and  DATE_FORMAT(from_unixtime(o.logistic_delivery_date),'%Y-%m-%d') = '$customer_delivery_date' ";
            }else{
				$three_days_times = time()-3600*24*7;
				$whereStr.= " and  o.logistic_delivery_date > $three_days_times";
				 
				
			}
        }else {
			$three_days_times = time()-3600*24*7;
				$whereStr.= " and  o.logistic_delivery_date > $three_days_times";
		}
    
	
     
        if($business_id) {
			    $pageSql=$sql . $whereStr . " group by c.business_id,main_code_id,c.bonus_title,c.guige1_id order by cate.category_sort_id,r.menu_order_id";
    
			
		}else{ //打印总单
			
			    $pageSql=$sql . $whereStr . " group by main_code_id,c.bonus_title,c.guige1_id  order by cate.category_sort_id,r.menu_order_id";
    
		}
		 if($totalandeverychannelPrint) {
			$sqlOfTotalAndGroupByBusiness = $sql1 . $whereStr . " group by c.business_id,main_code_id,c.bonus_title,c.guige1_id order by c.business_id,cate.category_sort_id,r.menu_order_id";
			//var_dump($sqlOfTotalAndGroupByBusiness);exit;
			 }
	   	//检查该商家是否可以管理其它店铺，如果授权即可以该商家权限进入系统。
		
		// $mdl = $this->loadModel('authrise_manage_other_business_account');
        // $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($business_id);
		 
		//var_dump($pageSql);exit;

        $mdl_cate = $this->loadModel('restaurant_category');
        $cateData =$mdl_cate->getParentCateList($this->current_business['id']);
        // var_dump($cateData);exit;
        $this->setData($cateData,'cateData');
	 // var_dump(get2('output'));exit;
	 if(trim(get2('output'))) {
	  $fileNameofOutput =$this->getOutputFileName($business_tradingName,$customer_delivery_date,trim(get2('output')),$business_id,$totalandeverychannelPrint);
	 }
	  
	   
        if(trim(get2('output'))=='totalOrderSummeryForDeliveryDate'){

            //获取打印表单上的分类名称
            $cate_name = $mdl_cate->getCategoryName($cate_id);

            $driverAndTruckInfo = $this->loadModel('truck')->getTruckAndDriverInfo($logistic_truck_No) ;
          //  var_dump($cate_name);exit;
			
			if($totalandeverychannelPrint){
				$data = $mdl_order->getListBySql($pageSql);
				$data1 = $mdl_order->getListBySql($sqlOfTotalAndGroupByBusiness);
				
				$this->loadModel('factoryReport');
				$report = new OrderInfoReport();
				 if($this->current_business['logo']) {
					$report->logoPath('data/upload/' . $this->current_business['logo']);
				}
					 $report->setTradingName($this->current_business['displayName'])
					->setCustomer_delivery_date($customer_delivery_date)
                    ->setCate_name($cate_name)
                    ->setDriverAndTruckInfo($driverAndTruckInfo)
             		->title("Summery Packing List-".$this->current_business['displayName'])
					->OrderData($data)
					->OrderDataEveryChannel($data1);
					
				$report->generatePDF_totalOrderSummeryAndEachChannelForDeliveryDate();
				$report->outPutToBrowser($fileNameofOutput);
				//$report->outPutToBrowser(substr($user_abn['untity_name'],0,8).'-'.substr($from,0,10).'-'.$to.'_Settlement.pdf');
				exit;
				
				
				
				//var_dump('gou');exit;
			}else{
				//var_dump ('did not ');exit;
				$data = $mdl_order->getListBySql($pageSql);
				
			   // var_dump($data);exit;
			   

				$this->loadModel('factoryReport');
				$report = new OrderInfoReport();
				 if($this->current_business['logo']) {
					$report->logoPath('data/upload/' . $this->current_business['logo']);
				}
				
				//如果打印的是某一个商家，则获得该商家的商家名称
				if($business_id) {
					
					 $report->setTradingName($business_user['displayName'])
					->setCustomer_delivery_date($customer_delivery_date)
                    ->setCate_name($cate_name)
                    ->setDriverAndTruckInfo($driverAndTruckInfo)
					->title("Stock lists-".$business_user['displayName'])
					->OrderData($data);
					
					
				}else{
					 $report->setTradingName($this->current_business['displayName'])
					->setCustomer_delivery_date($customer_delivery_date)
					->title("Summery of Packing List-".$this->current_business['displayName'])
                    ->setCate_name($cate_name)
                    ->setDriverAndTruckInfo($driverAndTruckInfo)
					->OrderData($data);
				}
					
				$report->generatePDF_totalOrderSummeryForDeliveryDate();
				$report->outPutToBrowser($fileNameofOutput);
				exit;
				
			}
			

        }elseif(trim(get2('output'))=='shippingLabel'){

            $fitInPage=(get2('fitInPage')=='true')?true:false;

            $pageSql=$sql . " where " . $whereStr . " order by o.address desc";

            $data = $mdl_order->getListBySql($pageSql);
           // var_dump($data);exit;
            $lotteryUserList=array();
            foreach ($data as $key => $value) {
					if($business_id) {
					//	var_dump($business_id);
					 $data[$key]['items']=$mdl_wj_customer_coupon->getItemsInOrder($value['orderId'],$business_id);
					// var_dump($data[$key]['items']);exit;
					
				}else{
					 $data[$key]['items']=$mdl_wj_customer_coupon->getItemsInOrder($value['orderId'],$this->current_business['id']);
					
				}



                $data[$key]['redeemQRCode']=redeemQRCode($value['redeem_code']);

                if(trim(get2('with'))=='lottery'&&!in_array($value['userId'], $lotteryUserList)){
                    $data[$key]['hasLottery']=$this->loadModel('lottery')->getUserRewardItems($value['userId'],$value['business_userId']);
                    array_push($lotteryUserList, $value['userId']);
                }
            }

            $this->loadModel('invoice');
            $report = new shippingLabel();
            if($this->current_business['logo']) {
                $report->logoPath('data/upload/' . $this->current_business['logo']);
            }
            $report->setStarttime(date('Y-m-d H:i:s',$st))
                ->setEndtime(date('Y-m-d H:i:s',$et))
                ->title("Shipping Label")
                ->setReturnAddress($this->current_business['googleMap'])
                ->fitInPage($fitInPage)
                ->OrderData($data);
            $report->generatePDF();
            $report->outPutToBrowser();
            exit;
        }elseif(trim(get2('output'))=='excel'){
			
			   $lists_new = array();
			  $lists = $mdl_order->getListBySql($pageSql);
			 //var_dump($pageSql);exit;
			   foreach ($lists as $key => $value) {
				$lists_new[$key]['logistic_delivery_date']="\t{$lists[$key]['logistic_delivery_date']}";
				$lists_new[$key]['category_cn_name']="\t{$lists[$key]['category_cn_name']}";
				$lists_new[$key]['menu_id']="\t{$lists[$key]['menu_id']}";
				$lists_new[$key]['bonus_title']="\t{$lists[$key]['bonus_title']}";
				$lists_new[$key]['sum']=$lists[$key]['total_quantity'];
				$lists_new[$key]['unit']=$lists[$key]['unit'];
				$lists_new[$key]['restaurant_menu_id']="\t{$lists[$key]['restaurant_menu_id']}";
				$lists_new[$key]['category_en_name']="\t{$lists[$key]['category_en_name']}";
				$lists_new[$key]['menu_en_name']="\t{$lists[$key]['menu_en_name']}";
			
			  
				}
				// var_dump($lists_new);exit;
				$file_name =date('Y-m-d',time());
				$this->toExcel($lists_new,array('Delivery_Date','Category','Code','Product_name','Sum','Unit','Product_id','Category_en_name','Product_en_name'),$fileNameofOutput,'php://output');
					exit;
			
			
		}else{
            $pageUrl = $this->parseUrl()->set('page');
            $pageSize = 40;
            $maxPage = 10;
            $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);

            $data = $mdl_order->getListBySql($page['outSql']);

        }

        $this->setData($page['pageStr'],'pager');

        $this->setData($data,'data');

        if($producing){
            //如果为生产类，转到生产相关页面
            $this->setData('Producing_Centre', 'menu');
            $this->setData('Producing Center - ' . $this->site['pageTitle'], 'pageTitle');

        }else{
            $this->setData('dispatching_center', 'menu');
            $this->setData('Dispatching center - ' . $this->site['pageTitle'], 'pageTitle');

        }
        $this->setData(HTTP_ROOT_WWW.'factory_2c/factroy_order_summery', 'searchUrl');
        $this->setData('factroy_order_summery', 'submenu');
        $this->setData($this->parseUrl(), 'currentUrl');
        $this->display_pc_mobile('factory_2c/factroy_order_summery','factory_2c/factroy_order_summery');


    }
	




	 public function print_single_item_buying_list_action()
    {
  
        $mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');
        $mdl_order = $this->loadModel('order');
        $mdl_user= $this->loadModel('user');

        /**
         * status List
         */

        /**
         * payment Type List
         */

         /**
         * customer_delivery_option Type List
         */
        $three_days_times = time()-60*60*24*7;
		$mdl = $this->loadModel('factory2c_list');
	    $availableDates = Factory2c_centre::getAvaliableDateOfAllSalesChannelOfThisFactory($this->current_business['id']);
    	$availableDates = array_map(function($d){
    		return date('Y-m-d',$d['logistic_delivery_date']);
    	}, $availableDates);
    	$this->setData($availableDates, 'availableDates');
//var_dump($availableDates);exit;
  
  	 
		$sepratePage =get2('sepratePage');
		$this->setData($sepratePage,'sepratePage');
	//	var_dump($sepratePage);exit;
  
        //** 获取该商家管辖工厂渠道商家
		
		$mdl = $this->loadModel('factory2c_list');
		$SalesChannelCustomerList= Factory2c_centre::getCustmerListsIncludeFactoryWithBusinessName($this->current_business['id'],$this->current_business['displayName']);
		$this->setData($SalesChannelCustomerList, 'SalesChannelCustomerList');
		
	


		//交易状态购买
		//if(!status) {
		$status ='c01';
		//}
		//支付状态
		$ifpaid=1;
		
		 $business_id = trim(get2('business_id'));
		 if($business_id) {
			 $business_user = $mdl_user->get($business_id) ;
			 $business_tradingName=$business_user ['displayName'];
		 } else{
			 $business_tradingName=$this->loginUser['displayName']; 			 
		 }
		 
		// 做到这里，如果 是suppliersID 且数据源!=1 则要使用cc_order_import 做为引导。
		
		if($this->loadModel('dispatching_centre_customer_list')->getIfBusinessIsExportDataSource($business_id)) {
			$export_data_source =1;
			$query_table_name='cc_order_import';
		}else{
			$export_data_source =0;
			$query_table_name='cc_order';
		}
		
		 //var_dump($export_data_source);exit;
		 $this->setData($business_id,'business_id');
		
		
		
		
        $sk = trim(get2('sk'));
		
		
		
		
		$customer_delivery_date = trim(get2('customer_delivery_date'));
		

        $this->setData($sk,'sk');
        $this->setData($customer_delivery_date,'customer_delivery_date');
    
		//获取当前用户点击的大类
         $cate_id = trim(get2('cate_id'));
         $this->setData($cate_id,'cate_id');


         //获取是否为生产类
         $producing = trim(get2('producing'));
         if(!$producing) $producing =0;
         $this->setData($producing,'producing');

		 
		 
		     //获取当前用户点击的大类
        $logistic_truck_No = trim(get2('logistic_truck_No'));
        $this->setData($logistic_truck_No,'logistic_truck_No');

        $TuckListOfTheDay =$this->loadModel('truck')->getAllOrdersTruckListwithCount($this->current_business['id'],$customer_delivery_date);
        $this->setData($TuckListOfTheDay,'TuckListOfTheDay');


			
		
		// 加入了一个前面可以选择一个商家，然后显示该商家的相关记录，如果商家id 为空，则保持原来的处理，如果不为空则进行相应的处理
		//获得该商家是否为外部数据源，如果是外部数据源，则需要使用外部订单总表关联
		//var_dump($business_id);exit;
		  $sql= Factory2c_centre::getSqlofAllOrdersDataOfCurrentBusiness($business_id,$query_table_name,$this->current_business['id']);
		
		
		 
        
        if (!empty($sk)) {
            $whereStr.=" and ( c.bonus_title like  '%" . $sk . "%'";
            $whereStr.=" or cate.category_cn_name like  '%" . $sk . "%'";
            $whereStr.=" or cate.category_cn_name like  '%" . $sk . "%'";
            $whereStr.=" or r.menu_id like  '%" . $sk . "%'";
            $whereStr.=" or r.menu_en_name like  '%" . $sk . "%'";
            $whereStr.=" or r.menu_cn_name like  '%" . $sk . "%'";
			$whereStr.=" or c.business_id like  '%" . $sk . "%')";
            $where[]=$whereStr;
        }

         if (!empty($cate_id)) {
             if($cate_id !='all') {
                 $whereStr.=" and r.restaurant_category_id =$cate_id ";
             }



         }


         // 如果为生产类，加入条件
         if ($producing) {
             $whereStr.=" and r.proucing_item =1 ";
         }else{
             $whereStr.=" and r.proucing_item =0 ";
         }
		 
		  if (!empty($logistic_truck_No)) {
            if($logistic_truck_No !='all') {
                $whereStr.=" and o.logistic_truck_No =$logistic_truck_No ";
            }
        }

		//deleivery date
		if (!empty($customer_delivery_date)) {
            if ($customer_delivery_date != 'all') {
               $whereStr.= " and  DATE_FORMAT(from_unixtime(o.logistic_delivery_date),'%Y-%m-%d') = '$customer_delivery_date' ";
            }else{
				$three_days_times = time()-259200;
				$whereStr.= " and  o.logistic_delivery_date > $three_days_times";
				 
				
			}
        }else {
			$three_days_times = time()-259200;
				$whereStr.= " and  o.logistic_delivery_date > $three_days_times";
		}
    
	
     
        if($business_id) {
			    $pageSql=$sql . $whereStr . " group by  DATE_FORMAT(from_unixtime(o.logistic_delivery_date),'%Y-%m-%d'),r.source_menu_id ,c.guige1_id order by o.logistic_delivery_date,cate.category_sort_id,r.menu_order_id";
    
			
		}else{ //打印总单
			
			    $pageSql=$sql . $whereStr . " group by  DATE_FORMAT(from_unixtime(o.logistic_delivery_date),'%Y-%m-%d'),r.source_menu_id,c.guige1_id order by o.logistic_delivery_date,cate.category_sort_id,r.menu_order_id";
    
		}
		//var_dump($pageSql);exit;
	   $data = $mdl_order->getListBySql($pageSql);
	   
	// var_dump($data);exit;

         // 获得当前订单得大类汇总

        $cateData =$this->loadModel('restaurant_category')->getParentCateList($this->current_business['id']);


        // var_dump($cateData);exit;
         $this->setData($cateData,'cateData');

        $this->setData($page['pageStr'],'pager');

        $this->setData($data,'data');


        if($producing){
            $this->setData('Producing_Centre', 'menu');
            $this->setData('Producing Center - ' . $this->site['pageTitle'], 'pageTitle');
        }else{
            $this->setData('dispatching_center', 'menu');
            $this->setData('dispatching center - ' . $this->site['pageTitle'], 'pageTitle');
        }
        $this->setData('print_single_item_buying_list', 'submenu');
        $this->setData(HTTP_ROOT_WWW.'factory_2c/print_single_item_buying_list', 'searchUrl');
        $this->setData($this->parseUrl(), 'currentUrl');

        $this->display_pc_mobile('factory_2c/print_single_item_buying_list','factory_2c/print_single_item_buying_list');
    }
	






	 public function print_single_item_buying_list_pdf_action()
    {
  
        $mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');
        $mdl_order = $this->loadModel('order');
        $mdl_user= $this->loadModel('user');
		$mdl = $this->loadModel('factory2c_list');
		
	     
  	  //查看是否打印总单和所有渠道配货单
		$sepratePage =get2('sepratePage');
		
		if($sepratePage=='true') {
			$sepratePage=1;
		}else{
			$sepratePage=0;
			
		}
		$business_id = trim(get2('business_id'));
		$itemLists = trim(get2('itemLists'));
        $guigeId = trim(get2('guigeId'));

		$customer_delivery_date = trim(get2('customer_delivery_date'));
		$printItemRange =trim(get2('output'));
		//var_dump($sepratePage);exit;
		 if($business_id) {
			 $business_user = $mdl_user->get($business_id) ;
			 $business_tradingName=$business_user ['displayName'];
		 } else{
			 $business_tradingName=$this->current_business['displayName'];
		 }
		 
		// 做到这里，如果 是suppliersID 且数据源!=1 则要使用cc_order_import 做为引导。
		
		if($this->loadModel('dispatching_centre_customer_list')->getIfBusinessIsExportDataSource($business_id)) {
			$export_data_source =1;
			$query_table_name='cc_order_import';
		}else{
			$export_data_source =0;
			$query_table_name='cc_order';
		}
		

		
 	  $sql= Factory2c_centre::getSqlofOrdersOfDefinedItemOfCurrentBusiness($business_id,$query_table_name,$this->current_business['id'],$printItemRange,$itemLists,$guigeId);
		
	      if (!empty($sk)) {
            $whereStr.=" and ( c.bonus_title like  '%" . $sk . "%'";
            $whereStr.=" or r.menu_id like  '%" . $sk . "%'";
            $whereStr.=" or r.menu_en_name like  '%" . $sk . "%'";
            $whereStr.=" or r.menu_cn_name like  '%" . $sk . "%'";
			$whereStr.=" or c.business_id like  '%" . $sk . "%')";
            $where[]=$whereStr;
        }
      
 	

		//deleivery date
		if (!empty($customer_delivery_date)) {
            if ($customer_delivery_date != 'all') {
               $whereStr.= " and  DATE_FORMAT(from_unixtime(o.logistic_delivery_date),'%Y-%m-%d') = '$customer_delivery_date' ";
            }else{
			
				 
				
			}
        }

        $logistic_truck_No = trim(get2('logistic_truck_No'));

        if (!empty($logistic_truck_No)) {
            if($logistic_truck_No !='all') {
                $whereStr.=" and o.logistic_truck_No =$logistic_truck_No ";
            }
        }


		$orderStr =" order by r.source_menu_id ,c.guige1_id ";
		$sql.=$whereStr.$orderStr;
	   
	//var_dump($sql);exit;
	 // var_dump(get2('output'));exit;
	 if(trim(get2('output'))) {
	  $fileNameofOutput =$this->getOutputFileName($business_tradingName,$customer_delivery_date,trim(get2('output')),$business_id,0);
	 }
	  
	   
        if(trim(get2('output'))=='singItemPrint'){
			
			
		
				//var_dump ('did not ');exit;
				$data = $mdl_order->getListBySql($sql);
				
			   // var_dump($data);exit;
			   

				$this->loadModel('factoryReport');
				$report = new OrderInfoReport();
				 if($this->current_business['logo']) {
					$report->logoPath('data/upload/' . $this->current_business['logo']);
				}
				
				//如果打印的是某一个商家，则获得该商家的商家名称
				
					 $report->setTradingName($business_tradingName)
					->setCustomer_delivery_date($customer_delivery_date)
					->title("Item Producing Lists-".$business_tradingName)
					->OrderData($data);
				
					
				$report->generatePDFSelectedItemsBuyingList();
				$report->outPutToBrowser($fileNameofOutput);
				exit;
				
		
			

        }elseif(trim(get2('output'))=='selectedItem'){

          
				//var_dump ('did not ');exit;
				$data = $mdl_order->getListBySql($sql);
				
			   // var_dump($sql);exit;
			   

				$this->loadModel('factoryReport');
				$report = new OrderInfoReport();
				 if($this->current_business['logo']) {
					$report->logoPath('data/upload/' . $this->current_business['logo']);
				}
				
				//如果打印的是某一个商家，则获得该商家的商家名称
				
					 $report->setTradingName($business_tradingName)
					->setCustomer_delivery_date($customer_delivery_date)
					->title("Item Producing Lists-".$business_tradingName)
					->setSepratePage($sepratePage)
					->OrderData($data);


				$report->generatePDFSelectedItemsBuyingList();
				$report->outPutToBrowser($fileNameofOutput);
				exit;
        }elseif(trim(get2('output'))=='allDefinedItem'){
			
			  
				//var_dump ('did not ');exit;
				$data = $mdl_order->getListBySql($sql);
				
			   // var_dump($data);exit;
			   

				$this->loadModel('factoryReport');
				$report = new OrderInfoReport();
				 if($this->current_business['logo']) {
					$report->logoPath('data/upload/' . $this->current_business['logo']);
				}
				
				//如果打印的是某一个商家，则获得该商家的商家名称
				
					 $report->setTradingName($business_tradingName)
					->setCustomer_delivery_date($customer_delivery_date)
					->title("Item Producing Lists-".$business_tradingName)
					->setSepratePage($sepratePage)
					->OrderData($data);
				
					
				$report->generatePDFSelectedItemsBuyingList();
				$report->outPutToBrowser($fileNameofOutput);
				exit;
			
			
		}else{
            $pageUrl = $this->parseUrl()->set('page');
            $pageSize = 40;
            $maxPage = 10;
            $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);

            $data = $mdl_order->getListBySql($page['outSql']);

        }

        $this->setData($page['pageStr'],'pager');

        $this->setData($data,'data');

        $this->setData('dispatching_center', 'menu');
        $this->setData('print_single_item_buying_list', 'submenu');
        
        $this->setData(HTTP_ROOT_WWW.'factory_2c/print_single_item_buying_list', 'searchUrl');

        $this->setData($this->parseUrl(), 'currentUrl');

        $this->setData('配货中心 - ' . $this->site['pageTitle'], 'pageTitle');

        $this->display_pc_mobile('factory_2c/print_single_item_buying_list','factory_2c/print_single_item_buying_list');
    }
	
	





public function  getOutputFileName($tradingName,$DataDate,$type,$business_id,$totalandeverychannelPrint){
	 
	 
	 $filename =$DataDate.'-'.$tradingName;
	 
	
	   switch ($type) {

            /**
             * 微信扫码登录检测
             */
            case 'excel':
			
			  $type1 = 'OrdersItemsSum';
			
			case 'shippingLabel':
			  $type1='packingslips';
			
			case 'totalOrderSummeryForDeliveryDate':
			 $type1='OrdersItemsSum';
			
			default:
			
	   }
	   
	   if(!$business_id) {
		   $type1.="-main";
	   }
	   
	   if($totalandeverychannelPrint) {
		   $type1.= '-withChannels';
	   }
	   
	  $filename .=$type1;
	  // var_dump($filename);exit;
	  return $filename;
	 
	 
 }

//导出汇总数据
	public function export_freshfood_summery_action() {
		
		$customer_delivery_date =trim(get2('customer_delivery_date'));
		
		$mdl_wj_customer_coupon= $this->loadModel('wj_customer_coupon');
		
		$business_id =get2('business_id');
		$printAllBusiness =get2('printAllBusiness'); //是否输出全部数据
		$customer_delivery_date =strtotime($customer_delivery_date);
		//var_dump($printAllBusiness);exit;
		
		if($business_id) {
			
			
			
		}else{
			
			$business_id =$this->current_business['id'];
			
		}
		
	
		
		if($customer_delivery_date) {
			$sql ="SELECT wj.`menu_id` , wj.`bonus_title`, sum(wj.`customer_buying_quantity`) as sum  FROM `cc_wj_customer_coupon` wj ,cc_order o
		WHERE  wj.order_Id =o.orderid and  o.logistic_delivery_date =$customer_delivery_date and   wj.`business_id` =$business_id and wj.`coupon_status`='c01' and (o.status =1 or o.accountPay=1)  group by wj.`menu_id`,
		`bonus_title` order by menu_id desc";
		
		//	var_dump($sql);exit;
		}else{
			
			$sql ="SELECT c.`menu_id` , c.`bonus_title`, sum(c.`customer_buying_quantity`) as sum  FROM `cc_wj_customer_coupon` c left join cc_order o on c.order_id =o.orderId 
		WHERE c.`business_id` =".$this->current_business['id']." and c.`coupon_status`='c01' and (o.status =1 or o.accountPay=1)   group by `menu_id`,
		`bonus_title` order by menu_id desc";
		//var_dump($sql);exit;
		
		}
		
		
		
		$lists =$mdl_wj_customer_coupon->getListBySql($sql);
		//$lists_new =$this->object2array($lists);
		
		$lists_new = array();
		
	   foreach ($lists as $key => $value) {
		$lists_new[$key]['menu_id']="\t{$lists[$key]['menu_id']}";
		$lists_new[$key]['bonus_title']="\t{$lists[$key]['bonus_title']}";
		$lists_new[$key]['sum']=$lists[$key]['sum'];
		}
		//var_dump($lists_new);exit;
		$file_name =date('Y-m-d',time());
		$this->toExcel($lists_new,array('Menu_id','Item_name','Sum'),'ubonus-kagroo_sum'.$file_name,'php://output');
	}
		




	/* 
	智能填补已存在下线渠道商家的 category 对应关系；
	*/
	public function fill_source_category_id_2_exist_sub_business_action(){
		
			// 接收 from  to   参数为餐馆编号或者说商家编号
		 $from = get2('from');
		 $to = get2('to');
		// 检查 to 是否已经建立 
		
		
		
		if( !$from  ) {
			
			var_dump('来源商家不明');exit;
		}
		//检查当前登陆的用户是否为被拷贝的商家的所有者
		
		if( $this->loginUser['id'] != $from   ) {
			
			var_dump('商家不匹配！');exit;
		}
		
		
		
		if( !$to   ) {
			
			var_dump('目标商家不明');exit;
		}else{
			
			// 检查目标商家是否为原商家的销售渠道 到 factory_2c_customer_list中检查；
			 $mdl =$this->loadModel('factory2c_list');
	 
		 $IfCurrentUserCanOperateThisCustomer = Factory2c_centre::getIfCurrentUserCanOperateThisCustomer($from ,$to);
		
		
		 if(!IfCurrentUserCanOperateThisCustomer )
		 {
			 var_dump('对不起，没有操作权限！');exit;
		 }
			
			
			$mdl_restaurant_category =$this->loadModel('restaurant_category');
		
			$sql ="select * from cc_restaurant_category where restaurant_id =$to ";
			$category_list = $mdl_restaurant_category->getListBySql($sql);
			//var_dump($category_list);exit;
			$missing_count =0;
			foreach ($category_list as $key => $value) {
				if(!$value['category_cn_name']) continue;
				$where =array(
					'restaurant_id'=>$from,
					'category_cn_name'=> $value['category_cn_name']
				);
				$source_category_rec =$mdl_restaurant_category->getByWhere($where);
				if($source_category_rec){ //发现了同一个类别
					$data =array(
						'source_category_id'=>$source_category_rec['id']
					);
					$mdl_restaurant_category->update($data,$value['id']);
					//var_dump('yes for '. $source_category_rec['category_cn_name'].'  ');
				}else{
					$missing_count ++;
					$str_miss_cate .= " ".$value['category_cn_name'];
					
				}
				
				
				
			}
			if($missing_count>0) {
					var_dump('共有'.$missing_count.'条未发现对应分类，分别是：'. $str_miss_cate);
					
				}else{
					
					var_dump('全部匹配！');
				}
				
				
				
			
			
		}
		
	}
			
	/* 
	智能填补已存在下线渠道商家的 category 对应关系；
	*/
	public function fill_source_menu_id_2_exist_sub_business_action(){
		
			// 接收 from  to   参数为餐馆编号或者说商家编号
		 $from = get2('from');
		 $to = get2('to');
		// 检查 to 是否已经建立 
		
		
		
		if( !$from  ) {
			
			var_dump('来源商家不明');exit;
		}
		//检查当前登陆的用户是否为被拷贝的商家的所有者
		
		if( $this->loginUser['id'] != $from   ) {
			
			var_dump('商家不匹配！');exit;
		}
		
		
		
		if( !$to   ) {
			
			var_dump('目标商家不明');exit;
		}else{
			
			// 检查目标商家是否为原商家的销售渠道 到 factory_2c_customer_list中检查；
			
		 $mdl =$this->loadModel('factory2c_list');
	 
		 $IfCurrentUserCanOperateThisCustomer = Factory2c_centre::getIfCurrentUserCanOperateThisCustomer($from ,$to);
		
		
		 if(!IfCurrentUserCanOperateThisCustomer )
		 {
			 var_dump('对不起，没有操作权限！');exit;
		 }
			
			
			
			
			$mdl_restaurant_menu =$this->loadModel('restaurant_menu');
			
			$sql ="select * from cc_restaurant_menu where restaurant_id =$to ";
			$menu_list = $mdl_restaurant_menu->getListBySql($sql);
			//var_dump($menu_list);exit;
			$missing_count =0;
			foreach ($menu_list as $key => $value) {
				if(!$value['menu_cn_name']) continue;
				$where =array(
					'restaurant_id'=>$from,
					'menu_cn_name'=> $value['menu_cn_name']
				);
				$source_menu_rec =$mdl_restaurant_menu->getByWhere($where);
				if($source_menu_rec){ //发现了同一个类别
					$data =array(
						'source_menu_id'=>$source_menu_rec['id']
					);
					$mdl_restaurant_menu->update($data,$value['id']);
					//var_dump('yes for '. $source_menu_rec['menu_cn_name'].'  ');
				}else{
					$missing_count ++;
					$str_miss_cate .= " ".$value['menu_cn_name'];
					
				}
				
				
				
			}
			if($missing_count>0) {
					var_dump('共有'.$missing_count.'条未发现对应分类，分别是：'. $str_miss_cate);
					
				}else{
					
					var_dump('全部匹配！');
				}
				
			
			
		}
		
	}		
	}