 <?php
class mdl_factory_2blist extends mdl_base
{
	protected $tableName = '#@_factory_2blist';

	
	
	
}
	
	class Factory2b_centre {
		
		
		
		
		
		
	public static function getIfCurrentUserCanOperateThisCustomer($from ,$customer_id) {
		
      	
		$sql ="select * from cc_factory_2blist where customer_id=$customer_id and factroy_id=$from";

		$customer_List = loadModel('factory_2blist')->getListBySql($sql);
		//var_dump($customer_List);exit;
		if($customer_List) {
			
				return 1;
				
			
			
		}

		return 0;
    }	
	
	
	public static function getIfCurrentUserIsFactory2bchannel($customer_id) {
		
      	
		$sql ="select count(*) as count  from cc_factory_2blist where customer_id=$customer_id ";

		$customer_List_count = loadModel('factory_2blist')->getListBySql($sql);
		//var_dump($customer_List_count);exit;
		if($customer_List_count[0]['count']>0) {
			
				return 1;
				
			
			
		}

		return 0;
    }


	public static function getIfCurrentUserIsFactory($id) {
		
      	
		$sql ="select count(*) as count  from cc_factory_2blist where factroy_id=$id ";

		$customer_List_count = loadModel('factory_2blist')->getListBySql($sql);
		//var_dump($customer_List_count);exit;
		if($customer_List_count[0]['count']>0) {
			
				return 1;
				
			
			
		}

		return 0;
    }		
	
	//获得客户商家列表
	public static function getCustmerLists($factroy_id = null)
	{	
		$where = $factroy_id ? ['factroy_id' => $factroy_id] : []; 
		$data = loadModel('factory_2blist')->getList([],$where);
		//var_dump($data);exit;
		return array_column($data, 'customer_id');
	}
	
	//获得客户商家列表 带名字
	public static function getCustmerListsWithBusinessName($factroy_id = null)
	{	
		
		
	
		
		$sql = "select a.customer_id,b.displayName  from cc_factory_2blist a left join cc_user b on a.customer_id =b.id  where factroy_id =".$factroy_id;
    
		$data = loadModel('factory_2blist')->getListBySql($sql);
		
	
		
		return $data;
	}
	
		//获得客户商家列表 带名字
	public static function getCustmerListsIncludeFactoryWithBusinessName($factroy_id = null,$business_name)
	{	
		
		
	
		
		$sql = "select a.customer_id,b.displayName  from cc_factory_2blist a left join cc_user b on a.customer_id =b.id  where factroy_id =".$factroy_id;
      //  var_dump($sql);exit;
		$data = loadModel('factory_2blist')->getListBySql($sql);
		
	
		
		return $data;
	}
	
	//找出该工厂七天前至未来的所有可用日期订单
   public static  function getAvaliableDateOfAllSalesChannelOfThisFactory($businessId) {
		
       //获取cc_order可以配送的日期
		$sql_cc_order_avaliabe_date ='SELECT DISTINCT logistic_delivery_date  from cc_order where status=1 and coupon_status="c01" and logistic_delivery_date >'.(time()-3600*24*7). ' and ';
        $sql_cc_order_avaliabe_date .= '  ( business_userId = '.$businessId ;
		$sql_cc_order_avaliabe_date .='  	or business_userId in (select customer_id  from cc_factory_2blist where factroy_id ='.$businessId.'))';
	
        $sql_cc_order_import_avaliabe_date ='SELECT DISTINCT logistic_delivery_date  from cc_order_import where logistic_delivery_date >'.(time()-3600*24*7). ' and ( business_userId = '.$businessId.' or business_userId in (select business_id  from cc_dispatching_centre_customer_list where dispatching_centre_id ='.$businessId.'))';
        $sql_union = 'select DISTINCT  logistic_delivery_date from (select * from( ('. $sql_cc_order_avaliabe_date.') union ('.$sql_cc_order_import_avaliabe_date.')) as d ) as c';
	  // var_dump($sql_cc_order_avaliabe_date);exit;
		$dateOptions =loadModel('dispatching_centre_customer_list')->getListBySql($sql_union);
		//var_dump($dateOptions);exit;
		return $dateOptions; //为本地数据源 或没有查找到该商家，视为本地数据源
    }
	
	
	
	   public static  function getSqlofAllOrdersDataOfCurrentBusiness($businessId,$tableName,$factroy_id,$totalandeverychannelPrint) {
		
		//如果指定某一个商家
		
		if(!$totalandeverychannelPrint) {
				//var_dump($totalandeverychannelPrint);exit;
		  if($businessId) {
			$sql =" select o.logistic_delivery_date,c.business_id,cate.category_sort_id,r.menu_order_id,cate.category_cn_name,cate.category_en_name,r.source_menu_id as main_code_id,r.menu_id,r.menu_en_name,r.unit,c.bonus_title,sum(c.customer_buying_quantity) as  total_quantity from cc_wj_customer_coupon c left join $tableName o on c.order_id =o.orderId left join cc_restaurant_menu r on c.restaurant_menu_id =r.id left join  cc_restaurant_category  cate on r.restaurant_category_id =cate.id";
			$sql .= " where (c.business_id =$businessId )  and o.coupon_status='c01' and o.status=1 ";
				
	
			}else{
				
				$sql =" select o.logistic_delivery_date,cate.category_sort_id,r.menu_order_id,cate.category_cn_name,cate.category_en_name,r.source_menu_id as main_code_id,r.menu_id,r.menu_en_name,r.unit,c.bonus_title,sum(c.customer_buying_quantity) as  total_quantity from cc_wj_customer_coupon c left join $tableName o on c.order_id =o.orderId left join cc_restaurant_menu r on c.restaurant_menu_id =r.id left join  cc_restaurant_category  cate on r.restaurant_category_id =cate.id";
				$sql .= " where (c.business_id =$factroy_id or c.business_id in (select customer_id from cc_factory_2blist where factroy_id =$factroy_id))  and o.coupon_status='c01' and o.status=1 ";
			
				//var_dump($sql);exit;
			}
				
		}else{
			 $sql =" select c.business_id,u.displayName,o.logistic_delivery_date,cate.category_sort_id,r.menu_order_id,cate.category_cn_name,cate.category_en_name,r.source_menu_id as main_code_id,r.menu_id,r.menu_en_name,r.unit,c.bonus_title,sum(c.customer_buying_quantity) as  total_quantity from cc_wj_customer_coupon c left join $tableName o on c.order_id =o.orderId left join cc_restaurant_menu r on c.restaurant_menu_id =r.id left join cc_user u on c.business_id =u.id left join  cc_restaurant_category  cate on r.restaurant_category_id =cate.id";
			 $sql .= " where (c.business_id =$factroy_id or c.business_id in (select customer_id from cc_factory_2blist where factroy_id =$factroy_id))  and o.coupon_status='c01' and o.status=1 ";
			//var_dump($sql);exit;
		}
      
		//var_dump($sql);exit;
		return $sql;
		
    }
	

	
	
		  public static  function getSqlofOrdersOfDefinedItemOfCurrentBusiness($businessId,$tableName,$factroy_id,$allDefinedItem,$itemLists) {
		
		//如果指定某一个商家
		
		$whereProductRange ='';
		//判断如果 $allDefinedItem ,则 不寻找单项或选择的id列表语法
		//判断如果$itemLists含有逗号，则使用 in 语法， 如果不含都好，使用=语法；
		
		if($allDefinedItem =='allDefinedItem') {
			
			$sql="select source_menu_id from cc_restaurant_menu where restaurant_id=$factroy_id and  singleItemPrintBuyingList=1";
			$data = loadModel('restaurant_menu')->getListBySql($sql);
			$menuList = implode(',',array_column($data, 'source_menu_id'));
			if(!$menuList)  {var_dump('did not defined print item!');exit; }

			$whereProductRange = "r.source_menu_id in (".$menuList.") ";
		
		}else{
			if(strstr($itemLists,',')){
				
				$whereProductRange = "r.source_menu_id in (".$itemLists.") ";
			}else{
				
				$whereProductRange ="  r.source_menu_id =".$itemLists ;
			}
			
		}
		
		
		//var_dump($totalandeverychannelPrint);exit;
		  if($businessId) {
			$sql =" SELECT r.source_menu_id,c.restaurant_menu_id ,r.menu_id,r.menu_cn_name,r.menu_en_name,r.unit ,c.order_id,u.googleMap,u.displayName,o.address,concat(o.first_name,' ',o.last_name) as customerName ,o.logistic_sequence_No,o.logistic_truck_No,c.bonus_title,c.customer_buying_quantity,r.unit  from ";
			$sql .=" cc_wj_customer_coupon c left join  cc_order o on c.order_id=o.orderId left join cc_user u on c.business_id=u.id  left join cc_restaurant_menu r on c.restaurant_menu_id =r.id  ";
			$sql .= " where c.business_id=$businessId and ".$whereProductRange."  and o.coupon_status='c01' and o.status=1 ";
				
	
		}else{
				
			$sql =" SELECT  r.source_menu_id,c.restaurant_menu_id ,r.menu_id,r.menu_cn_name,r.menu_en_name,r.unit ,c.order_id,u.googleMap,u.displayName,o.address,concat(o.first_name,' ',o.last_name) as customerName ,o.logistic_sequence_No,o.logistic_truck_No,c.bonus_title,c.customer_buying_quantity,r.unit from ";
			$sql .=" cc_wj_customer_coupon c left join  cc_order o on c.order_id=o.orderId left join cc_user u on c.business_id=u.id  left join cc_restaurant_menu r on c.restaurant_menu_id =r.id  ";
			$sql .= " where (c.business_id =$factroy_id or c.business_id in (select customer_id from cc_factory_2blist where factroy_id =$factroy_id)) and o.coupon_status='c01' and o.status=1 and  ".$whereProductRange ." ";
	
					
				
		}
				
		
   
		
		return $sql;
		
    }
	
	   
}
	
