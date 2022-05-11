<?php
	class ctl_chart_per_business extends cmsPage
	{
		
		
	  function ctl_chart_per_business()
    {

        parent::cmsPage();

       
		
		
    }
	
   public function index_action() {
	   
	   	$this->display('chart_per_business/index');
   }
		
		
		
		function new_user_weekly_action(){
			
		
		
		 $list_year = trim(get2('list_year'));
			 
		 
		if (empty($list_year)) {
            $list_year=  date('Y');
			//var_dump($list_month);exit;
           }
		$this->setData($list_year,'list_year');	 
			
			
		
		$sql ="SELECT date_format(FROM_UNIXTIME(u.createdDate),'%Y') as years,DATE_FORMAT(FROM_UNIXTIME(u.createdDate),'%u') as weeks,count(u.id) as user_count FROM cc_user u WHERE date_format(FROM_UNIXTIME(u.createdDate),'%Y')='$list_year'  group by years,weeks order by years,weeks limit 52";
		//var_dump ($sql);exit;
	    $titleName ="新用户注册-".$list_year."年";
			
	
		
		//var_dump ($sql);exit;
		$mdl_order =$this->loadModel('order');
		$new_user_weekly =$mdl_order->getListBySql($sql);
		//var_dump($new_user_weekly);exit;
		
		
		foreach ($new_user_weekly as $key => $value) {
			$new_user_weekly[$key]['weekday'] =$this->get_firstday_of_week($value['years'],$value['weeks']);
			
		}
		
	//  var_dump($new_user_weekly);exit;
	
		$this->setData($new_user_weekly,'new_user_weekly');
	
		$this->setData($titleName,'titleName');
		
		
		$this->setData('user_chart', 'menu');
        $this->setData('new_user_weekly', 'submenu');
        $this->setData('新用户分析 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
		$this->display('chart_per_business/new_user_weekly');
		
		}
		
		
		
		function new_user_daily_action(){
			
		
		
		 $list_month = trim(get2('list_month'));
		 $disp_mode = trim(get2('disp_mode'));
			 
		 if (empty($disp_mode)) {
			 
			  $disp_mode ='all';
			
		 }
		   $this->setData($disp_mode,'disp_mode');	 
		 
		if (empty($list_month)) {
            $list_month=  date('Y-m');
			//var_dump($list_month);exit;
           }
		$this->setData($list_month,'list_month');	 
		
		 $list_month= str_replace("-", "", $list_month);
			// var_dump($list_month);exit;
		
			
		
		//$sql ="SELECT date_format(FROM_UNIXTIME(u.createdDate),'%Y') as years,DATE_FORMAT(FROM_UNIXTIME(u.createdDate),'%Y%m') as months,date_format(FROM_UNIXTIME(u.createdDate),'%Y,%m-1,%d') as days,count(u.id) as user_count FROM cc_user u WHERE date_format(FROM_UNIXTIME(u.createdDate),'%Y')='2021' and DATE_FORMAT(FROM_UNIXTIME(u.createdDate),'%Y%m') ='$list_month' group by months,days order by months,days limit 31";//var_dump ($sql);exit;
	   

		$sql="SELECT date_format(FROM_UNIXTIME(u.createdDate),'%Y') as years,DATE_FORMAT(FROM_UNIXTIME(u.createdDate),'%Y%m') as months,date_format(FROM_UNIXTIME(u.createdDate),'%Y,%m-1,%d') as days,date_format(FROM_UNIXTIME(u.createdDate),'%Y,%m,%d') as days1,count(u.id) as user_count  , (select count(ord.id) from  cc_order ord  where ord.userId  in (select id from cc_user  user where date_format(FROM_UNIXTIME(user.createdDate),'%Y,%m-1,%d')  = date_format(FROM_UNIXTIME(u.createdDate),'%Y,%m-1,%d'))) as count_order  FROM cc_user u WHERE date_format(FROM_UNIXTIME(u.createdDate),'%Y')='2021' and DATE_FORMAT(FROM_UNIXTIME(u.createdDate),'%Y%m') ='$list_month' group by months,days order by months,days limit 31";

	   $titleName ="新用户增长统计-".$list_month."月";
			
	
		
		//var_dump ($sql);exit;
		$mdl_order =$this->loadModel('order');
		$new_user_daily =$mdl_order->getListBySql($sql);
		//var_dump($daily_selling);exit;
		
		
		$total_new_order=0;
		$total_new_user=0;
		foreach ($new_user_daily as $key => $value) {
			$total_new_user += $value['user_count'];
			$total_new_order += $value['count_order'];
			if(!$value['user_count']) {
				$daily_rates ='0.00%';
			}else{
				$daily_rates =sprintf("%2.2f%%", $value['count_order']/$value['user_count'] * 100);
			}
			
			$new_user_daily[$key]['daily_rates'] =$daily_rates;
		}
		
		$buyingRate =sprintf("%2.2f%%", $total_new_order/$total_new_user * 100);
		$this->setData($buyingRate,'buyingRate');
		
		//获得月份列表
		$sql ="SELECT date_format(FROM_UNIXTIME(a.createTime),'%Y') as years,DATE_FORMAT(FROM_UNIXTIME(a.createTime),'%Y%m') as months FROM `cc_order` a left join cc_user u on a.business_userId = u.id WHERE date_format(FROM_UNIXTIME(a.createTime),'%Y')>=2019  group by years, months  order by years,months limit 40 ";
		$mdl_user=$this->loadModel('user');
		$list_months = $mdl_user->getListBySql($sql);
		
		foreach ($list_months as $key => $value) {
			$list_months[$key]['year_month'] =substr_replace($value['months'], '-', 4, 0);;
			
		}
		
		if ($disp_mode=='all'){
			$newUserADD =$new_user_daily;
			$newUserOrderADD =$new_user_daily;
			//var_dump($newUserADD);exit;
			
		}elseif ($disp_mode=='onlyNewUser') {
			$newUserADD =$new_user_daily;
			$newUserOrderADD =null;
			
		}elseif($disp_mode=='onlyNewOrder'){
			$newUserOrderADD  =$new_user_daily;
			$newUserADD =null;
			
		}
		
	//  var_dump($list_months);exit;
	
		$this->setData($new_user_daily,'new_user_daily');
		$this->setData($newUserADD,'newUserADD');
		$this->setData($newUserOrderADD,'newUserOrderADD');
		$this->setData($list_months,'list_months');
		$this->setData($titleName,'titleName');
		
		
		$this->setData('user_chart', 'menu');
        $this->setData('new_user_daily', 'submenu');
        $this->setData('新用户分析 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
		$this->display('chart_per_business/new_user_daily');
		
		}
		
		
		
		function selling_daily_action(){
			
		

		
		  $list_month = trim(get2('list_month'));
		  $business_id = trim(get2('business_id'));
          $category = trim(get2('category'));
          $sub_category = trim(get2('sub_category'));
          $accountType =get2('accountType');

          if(!$accountType){
              $accountType='all';

          }

        if(!$category) {
            $category='all';
        }

         if(!$sub_category) {
            $sub_category='all';
         }


           $this->setData($accountType,'accountType');
           $this->setData($sub_category,'sub_category');
           $this->setData($category,'category1');

		  
		  //如果为空，为当前登陆商家
		  if(!$business_id){
			  
			  $business_id = $this->current_business['id'];
			  
		  }

        $current_u =$this->loadModel('user')->get($business_id);
		$business_name =$current_u['displayName'];
		$this->setData($business_name,'business_name');
		 
		 $this->setData($business_id,'business_id');
		 
		 
		if (empty($list_month)) {
            $list_month=  date('Y-m');
			//var_dump($list_month);exit;
           }
		$this->setData($list_month,'list_month');	 
		
		 $list_month= str_replace("-", "", $list_month);
			// var_dump($list_month);exit;
		
		
		//var_dump ($sql);exit;
		$this->setData($sk,'sk');
		$this->setData($business_id,'business_id');
		 
		$mdl_user=$this->loadModel('user');
		
		
		$curr_user = $this->loadModel('user')->get($business_id);
		

		$sql ="SELECT date_format(FROM_UNIXTIME(a.gen_date),'%Y') as years,DATE_FORMAT(FROM_UNIXTIME(a.gen_date),'%Y%m') as months,
       date_format(FROM_UNIXTIME(a.gen_date),'%Y,%m,%d') as days,round(sum(a.voucher_deal_amount*a.new_customer_buying_quantity),2) as subtotal FROM `cc_wj_customer_coupon` a 
           left join cc_user u on a.business_Id = u.id 
           left join cc_order o on o.orderId=a.order_id 
            left join cc_restaurant_menu m  on a.restaurant_menu_id=m.id 
            left join cc_user_factory factory on a.business_id =factory.factory_id and a.userId =user_id 
            
        WHERE  a.business_id =$business_id  
          and date_format(FROM_UNIXTIME(a.gen_date),'%Y')='2022' 
          and DATE_FORMAT(FROM_UNIXTIME(a.gen_date),'%Y%m') ='$list_month' 
          and (a.`coupon_status`='c01' or a.`coupon_status`='b01')  ";




       if($category && $category !='all') {

           $sql .= "and m.restaurant_category_id = $category ";
       }
          //  var_dump ($accountType);exit;
            if($accountType && $accountType !='all') {
                if($accountType=='1'){
                    $sql .= "and factory.to_xero = 1 ";

                }
                if($accountType=='2'){
                    $sql .= "and factory.to_xero = 0 ";

                }

            }



        if($sub_category && $sub_category !='all') {

            $sql .= "and m.sub_category_id = $sub_category ";
        }


      $sql .= "   group by months,days order by months,days limit 31";

				

		//	var_dump ($sql);exit;
				$current_user =$mdl_user->get($business_id);
				if($current_user){
					
					if ($current_user['displayName']) 
					{
						  $titleName =$current_user['displayName'];
					}elseif ($current_user['businessName']){
						 $titleName =$current_user['businessName'];
					}else{
						 $titleName =$current_user['name'];
						
					}
				}

		//var_dump ($sql);exit;
		$mdl_order =$this->loadModel('order');
		$daily_selling =$mdl_order->getListBySql($sql);
		//var_dump($daily_selling);exit;

		//获得月份列表
		$sql ="SELECT date_format(FROM_UNIXTIME(a.createTime),'%Y') as years,DATE_FORMAT(FROM_UNIXTIME(a.createTime),'%Y%m') as months FROM `cc_order` a left join cc_user u on a.business_userId = u.id WHERE date_format(FROM_UNIXTIME(a.createTime),'%Y')>=2022  group by years, months  order by years,months limit 50 ";
		$mdl_user=$this->loadModel('user');
		$list_months = $mdl_user->getListBySql($sql);
		
		foreach ($list_months as $key => $value) {
			$list_months[$key]['year_month'] =substr_replace($value['months'], '-', 4, 0);;
		}
		if($curr_user['role']==20) {
			
			$business_id =$curr_user['user_belong_to_user'];
			
		}
		//var_dump($list_months);exit;
		$this->setData($list_months,'list_months');
		// 获取 最近30个生鲜商家 
			$sql ="select u.id,  IFNULL(u.name, IFNULL(u.displayName, u.businessName))as name from cc_user u   where ( u.id=$business_id  or u.user_belong_to_user = $business_id ) order by u.id desc limit 50";

		//	var_dump($sql);exit;


            $mdl_restaurant_category = $this->loadModel('restaurant_category');
            $pageSql = "select  * from cc_restaurant_category where createUserId=$business_id  and (length(category_cn_name)>0 or length(category_en_name)>0) and ( parent_category_id =0 or  parent_category_id is null) and isdeleted =0  order by isHide,category_sort_id ";
            $data = $mdl_restaurant_category->getListBySql($pageSql);
            $this->setData($data,'restaurant_category');


            $sql_Parent_cate_list ="select *,  if(`parent_category_id`,concat('---',category_en_name),category_en_name) as category_cn_name1 ,if(`parent_category_id`,concat(category_en_name),category_en_name) as   category_cn_name2 ,if(`parent_category_id`,concat(`parent_category_id`,id),concat(id,0)) as parent_id  from cc_restaurant_category where restaurant_id=$business_id and (length(category_cn_name)>0 or length(category_en_name)>0) and isdeleted =0  order by isHide, parent_id,category_sort_id ";

            $data_parent_cate_list  = $mdl_restaurant_category->getListBySql($sql_Parent_cate_list);
            //var_dump($sql_Parent_cate_list);exit;



            $this->setData($data_parent_cate_list, 'data_parent_cate_list');


            $lastest_business_list = $mdl_user->getListBySql($sql);
		
		//var_dump($lastest_business_list);exit;
		$this->setData($daily_selling,'daily_selling');
		$this->setData($titleName,'titleName');
		$this->setData($lastest_business_list,'lastest_business_list');
		
		$this->setData('selling', 'menu');
        $this->setData('selling_daily', 'submenu');
        $this->setData('turnover -business Centre ' . $this->site['pageTitle'], 'pageTitle');
		$this->display('chart_per_business/selling_daily');
		
		}
		
		
		
		
		
		
		
		
		
		function selling_weeekly_action(){
			
		

		 $sk = trim(get2('sk'));
		 $business_id = trim(get2('business_id'));
		 
		 if(!$business_id) {
			 
			 $business_id = $this->loginUser['id'];
			
		 }
		 
		//var_dump($business_id);exit;
		
		$business_name =$this->loadModel('user')->getUserDisplayName($business_id);
		$this->setData($business_name,'business_name');
		
		$this->setData($sk,'sk');
		
	
		$this->setData($business_id,'business_id');
		 
		//如果商家为sales, 则sql语句不一样
		
		$curr_user = $this->loadModel('user')->get($business_id);
		
		if($curr_user['role']==101) {
			
			$whereStr .= " a.userId in (select user_id from cc_user_factory where factory_sales_id =$business_id ) ";
			$sql ="SELECT `business_Id`, IFNULL(u.displayName, IFNULL(u.name, u.businessName))as name,date_format(FROM_UNIXTIME(a.gen_date),'%Y') as years,date_format(FROM_UNIXTIME(a.gen_date),'%u') as weeks,sum(`adjust_subtotal_amount`) as subtoal FROM `cc_wj_customer_coupon` a left join cc_user u on a.business_Id = u.id WHERE  ". $whereStr." and date_format(FROM_UNIXTIME(a.gen_date),'%Y')='2021' and (a.`coupon_status`='c01' or a.`coupon_status`='b01')   group by business_Id,years,weeks order by business_id,weeks limit 52";
	        //var_dump($sql);exit;
		}else{
			$whereStr .= "business_Id = $business_id";
			$sql ="SELECT `business_Id`, IFNULL(u.displayName, IFNULL(u.name, u.businessName))as name,date_format(FROM_UNIXTIME(a.gen_date),'%Y') as years,date_format(FROM_UNIXTIME(a.gen_date),'%u') as weeks,sum(`adjust_subtotal_amount`) as subtoal FROM `cc_wj_customer_coupon` a left join cc_user u on a.business_Id = u.id WHERE  ". $whereStr." and date_format(FROM_UNIXTIME(a.gen_date),'%Y')='2021' and (a.`coupon_status`='c01' or a.`coupon_status`='b01')   group by business_Id,years,weeks order by business_id,weeks limit 52";
	//var_dump($sql);exit;
		}
	    
			
			
		
		
		$mdl_order =$this->loadModel('order');
		$weekly_selling =$mdl_order->getListBySql($sql);
		
		
		foreach ($weekly_selling as $key => $value) {
			$weekly_selling[$key]['weekday'] =$this->get_firstday_of_week($value['years'],$value['weeks']);
			
		}
		//var_dump($weekly_selling);exit;
		// 获取 最近30个生鲜商家 
		
		if($curr_user['role']==101) {
			
			$business_id =$curr_user['user_belong_to_user'];
			
		}
		
		$sql ="select u.id,  IFNULL(u.name, IFNULL(u.displayName, u.businessName))as name from cc_user u   where ( u.id=$business_id  or u.user_belong_to_user = $business_id ) order by u.id desc limit 30";
		//	var_dump('dddd'.$sql);exit;
		
		
		
		
		$mdl_user=$this->loadModel('user');
		$lastest_business_list = $mdl_user->getListBySql($sql);
		
		//var_dump($lastest_business_list);exit;
		$this->setData($weekly_selling,'weekly_selling');
		$this->setData($lastest_business_list,'lastest_business_list');
		
		$this->setData('selling', 'menu');
        $this->setData('selling_weekly', 'submenu');
        $this->setData('销售分析 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
		$this->display('chart_per_business/selling_weekly');
		
		}

		function selling_weekly_compare_action(){
			
		

		
		 $weekNum = trim(get2('weekNum'));
		 
		
		$current_week_num= date('W');
		  
		 if (empty($weekNum)) {
            $weekNum =$current_week_num;
        }
		
		$business_id = $this->loginUser['id'];
	
		$this->setData($weekNum,'weekNum');
		 
		
		
			
		 /*$sql ="SELECT date_format(FROM_UNIXTIME(curdate()),'%u') as currentweeks, `business_Id`, IFNULL(u.name, IFNULL(u.displayName, u.businessName))as name,
		date_format(FROM_UNIXTIME(a.gen_date),'%Y') as years,date_format(FROM_UNIXTIME(a.gen_date),'%u') as weeks,sum(`adjust_subtotal_amount`) as subtotal
		FROM `cc_wj_customer_coupon` a left join cc_user u on a.business_Id = u.id WHERE  date_format(FROM_UNIXTIME(a.gen_date),'%Y')='2021' 
		and (a.`coupon_status`='c01' or a.`coupon_status`='b01')  and date_format(FROM_UNIXTIME(a.gen_date),'%u') = $weekNum  
		group by business_Id,years,weeks order by business_id,weeks limit 30"; */
		
		$sql ="SELECT date_format(FROM_UNIXTIME(curdate()),'%u') as currentweeks,f.factory_sales_id as business_id, 
		IFNULL(u.name, IFNULL(u.displayName, u.businessName))as name, date_format(FROM_UNIXTIME(a.gen_date),'%Y') as years,
		date_format(FROM_UNIXTIME(a.gen_date),'%u') as weeks,sum(`adjust_subtotal_amount`) as subtotal 
		FROM `cc_wj_customer_coupon` a left join cc_user_factory f on a.userId= f.user_id 
		left join cc_user u on f.factory_sales_id= u.id WHERE f.factory_id =$business_id and date_format(FROM_UNIXTIME(a.gen_date),'%Y')='2021' 
		and (a.`coupon_status`='c01' or a.`coupon_status`='b01') and date_format(FROM_UNIXTIME(a.gen_date),'%u') = $weekNum 
		group by f.factory_sales_id,years,weeks order by business_id,weeks limit 30";
		
		$mdl_order =$this->loadModel('order');
		$weekly_selling =$mdl_order->getListBySql($sql);
		
		//var_dump($sql);exit;
		
		
		if($curr_user['role']==101) {
			
			$business_id =$curr_user['user_belong_to_user'];
			
		}
		
		$this->setData($current_week_num,'current_week_num');
		//var_dump($sql);exit;
		// 获取 最近30个生鲜商家 
		$sql ="select u.id,  IFNULL(u.name, IFNULL(u.displayName, u.businessName))as name from cc_user u   where ( u.id=$business_id  or u.user_belong_to_user = $business_id ) order by u.id desc limit 30";

		//	var_dump($sql);exit;
		
		
		
		
		$mdl_user=$this->loadModel('user');
		$lastest_business_list = $mdl_user->getListBySql($sql);
		
		//var_dump($lastest_business_list);exit;
		$this->setData($weekly_selling,'weekly_selling');
		$this->setData($lastest_business_list,'lastest_business_list');
		
		$this->setData('selling', 'menu');
        $this->setData('selling_weekly_compare', 'submenu');
        $this->setData('销售分析周对比 - 销售分析 - ' . $this->site['pageTitle'], 'pageTitle');
		$this->display('chart_per_business/selling_weekly_compare');
		
		}

	function get_firstday_of_firstweek($year){
			//计算这一年第一天星期几，范围0-6，分别是星期日到星期六
				$tm_wday = strtotime("$year-01-01","%Y-%m-%d")['tm_wday'];
				$tm_wday = $tm_wday == 0 ? 7 : $tm_wday;
				$tm_wday -- ;
				return strtotime("$year-01-01 - $tm_wday days");
			}
			 
			 
			 
			public function get_firstday_of_week($year,$week){
			//计算这一周星期一距第一年第一周的星期一多少天
				$days = ($week )*7;
				$firstday_of_firstweek = $this->get_firstday_of_firstweek($year);
				$date_str = date("Y-m-d",$firstday_of_firstweek);
			//加上天数就获取到这一周星期一的日期了
				//return date("Y-m-d",strtotime("$days day"));
				$year1= date('Y', strtotime ("$days day", strtotime($date_str)));
		        $month= date('m', strtotime ("$days day", strtotime($date_str)));
				$day= date('d', strtotime ("$days day", strtotime($date_str)));
				return $year1 .','.($month-1).','.$day;
				
	}
	}
?>