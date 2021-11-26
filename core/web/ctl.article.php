<?php 
class ctl_article extends cmsPage
{

	public function index_action()
	{	
		
		$id= get2('id');
		
		if ($this->loginUser['id']==201964  ) {
			
				$sql = "select * from cc_article where   id='".$id. "' or keyword1 like '%".$id."%' or keyword2 like '%".$id."%'";
		}else{
				$sql = "select * from cc_article where is_approved=1 and status=4 and   id='".$id. "' or keyword1 like '%".$id."%' or keyword2 like '%".$id."%'";
			
		}
		
	
		//var_dump($sql); exit;
		
		 $mdl_article = $this->loadModel('article');
		  $mdl_user=$this->loadModel('user');
		 $article =$mdl_article->getListBySql($sql);
		   $this->setData($this->loadModel('user')->getBusinessDisplayName($article[0]['createUserId']), 'businessDisplayName');
		// var_dump($article);exit;
		 if ($article) {
			 
			 //获取阅读原文链接
			 $article_info =$article[0];
			 
			 if($article_info['business_id']){
				 $link = '/store/'.$article_info['business_id'];
			 }else if($article_info['product_id']) {
				 $link = '/coupon7m/'.$article_info['product_id'].'?id='.$article_info['product_id'];
			 }else{
				 $link = '/food1?key='.$article_info['keyword1'];
				 
			 }
            $article[0]['link'] =  $link;

				
			 
			 
			 
			 
			
			 
			 $mdl_coupons = $this->loadModel( 'coupons' );
			 $orderby='id desc';
			 
			 
			 $pageSql=" 
			SELECT c.EvoucherOrrealproduct,c.id,c.categoryName,c.cityName,0 as subid,c.title,c.coupon_summery_description,c.searchKeywords,c.pic,c.createUserId,c.bonusType,c.hits,c.buy,c.voucher_deal_amount,c.voucher_original_amount,c.businessName,c.startTime,c.endTime,c.autoOffline,c.categoryId,c.city
			FROM cc_coupons as c
			WHERE c.isApproved=1 and c.status=4  and c.categoryId like '%106126%' ";
				
			$keyword1 =$article[0]['keyword1'];
			$keyword2 =$article[0]['keyword2'];
			
			if($keyword1)
				$pageSql.=" and (title like '%$keyword1%' or businessName like '%$keyword1%' or  searchKeywords like '%$keyword1%') ";
			
			if($keyword2)
				if($keyword1) {
					$pageSql.=" or (title  like '%$keyword2%' or businessName like '%$keyword2%' or  searchKeywords like '%$keyword2%') ";
				}else{
					$pageSql.=" and (title like '%$keyword2%' or businessName like '%$keyword2%' or  searchKeywords like '%$keyword2%') ";
					
				}
				
			
			
			//	 获得关键字相关类别的产品
			
			$mdl_infoclass=$this->loadModel('infoClass');
			$sql =" select id,name from cc_infoclass where name like '%$keyword1%' or name like '%$keyword2%'";
			//var_dump($sql);exit;
			$aliaslist= $mdl_infoclass->getListBySql($sql);
			if ($aliaslist) {
				//数组转字串
				$str="(";
				$start=1;
				 foreach ($aliaslist as $key => $value) {
					 if($start) {
						 $str .=$aliaslist[$key]['id'];
						 $start=0;
					 }else{
						 $str .=",".$aliaslist[$key]['id'];
						 
					 }
					
				}
				$str .=")";
				
				
				
				//var_dump($str);exit;
				
			}
			if($str) {
				$strsql = " c.categoryId in ".$str." or title  like " ;
				//var_dump($strsql);exit;
				$pageSql=str_replace('title like',$strsql,$pageSql);
			}
			$pageSql.= " order by ".$orderby;
			//var_dump($pageSql);exit;
			$pageUrl	= $this->parseUrl()->set('page')->set( 'listType','coupons');
			$pageSize	= 20;
			$maxPage	= ($this->getUserDevice()=='desktop')?10:0;
			$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage,$wholePage);
			$data		= $mdl_coupons->getListBySql($page['outSql']);
			
		
		
		
			foreach ($data as $key => $value) {
			$mdl_coupons->caculatePriceAndPoint($data[$key]);
			$data[$key]['businessDisplayName']=$mdl_user->getBusinessDisplayName($value['createUserId']);
			
			
			// 如果产品类型为代金券或者团购券 ,那么使用快速链接
			if ($value['bonusType']==7 or $value['bonusType']==18) {
				
				$data[$key]['a_link'] = 'coupon7m/'.$value['id'].'?id='.$value['id'].'&reftag='.$reftag;
			}else { //否则使用标准链接
				$data[$key]['a_link'] = 'coupon1/'.$value['id'].'?reftag='.$reftag;
				
			}
			if($data[$key]['EvoucherOrrealproduct']=='restaurant_menu' ) {
				
				
				$data[$key]['businessDisplayName'] =$data[$key]['title'];
				//使用直接到餐馆的链接
				$data[$key]['a_link'] = 'restaurant2/'.$value['createUserId'].'?id='.$value['createUserId'].'&reftag='.$reftag;
			
		
			}
		}
		   //var_dump($data);exit;
			$this->setData( $page, 'pager' );

			$this->setData( $data, 'data' );

		
		  // 更新当前阅读量
		  
		   $article_id= $article[0]['id'];
		   $article_hits = $article[0]['hits'];
		   $update_data =array(
		     'hits'=>$article_hits +1
		   
		   );
		  // var_dump($update_data);exit;
		   $mdl_article->update($update_data,$article_id);
		   
		   	//wx share
		require_once "wx/wxjssdk.php";
        $jssdk = new WXjsSDK();
        $signPackage = $jssdk->GetSignPackage();
        $this->setData($signPackage,'signPackage');

        $shareUrl = HTTP_ROOT_WX."article/".$id;
        $this->setData($shareUrl,'shareUrl');
        $this->setData(generateQRCode($shareUrl),'shareQRCode');
        
        $this->setData( get2('action'), 'returnAction' );

        $this->setData($this->loadModel('overwriteCouponStoreLink')->getOverWriteLink($id),'storeOverWriteLink');
		   
		$this->setData(  $article[0]['title'] , 'pageTitle' );
		$this->setData( $article[0]['keyword1'].' | '.$article[0]['keyword2'] , 'h1' );
		$this->setData( $article[0]['keyword1'].' | '.$article[0]['keyword2'], 'pageKeywords' );
		$this->setData(  $article[0]['title'] , 'pageDescription' );
		 $this->setData($article[0],'article');
		 
		 
		 
		 
		 /* 获得 voting 信息 */
		 
			$votingId=$article[0]['votingId'];
			$this->setData($votingId,'votingId');
			
           if($votingId){
			   
			$voting=$this->loadModel('voting')->getVoting($votingId);
			$this->setData($voting,'voting');

			$item_list=$this->loadModel('voting_item')->getVotingItemList($votingId,$this->loginUser['id']);
			$this->setData($item_list,'list');


			$this->setData(  '参与投票赢取霸王餐-'.$article[0]['title'] , 'pageTitle' );
		
			 }
		 
		 
		 
		 
		/* 获得voting信息结束  */
		
			if($this->getUserDevice()=='desktop'){
			$result_section = $this->fetch('food_result_tpl_pc');
			}else{
				$result_section = $this->fetch('food_result_tpl');
			}
			 $this->setData($result_section,'result_section');
			 
			// var_dump($article);//exit;
			if ($id==71  ) {
				
				 $this->display('mobile/article/show1');
			}else if ($id ==70){
				// 计算网页投票一次性投票数量
				 $current_date = date("Y-m-j",time());
				 $end_date = date("Y-m-j",1563929999);
		         $days = (int)((1563929999-time())/(60*60*24))+1;
				 
				// $days=substr(time(),8,2);
				
				 $this->setData($days,'days');
				 //$this->form_response_msg('活动已经结束! '.$tablename);
				// var_dump('活动已经结束！');exit;
				 $this->display('mobile/article/show2');
			}else if ($id ==69){
				
				 $this->display('mobile/article/show3');
			}else if ($id ==74){
				
				 $this->display('mobile/article/show5');
			}
			else if ($id ==73 or $id==72){
				
				 $this->display('mobile/article/show_bianlunsai');
			}else{
				 $this->display('mobile/article/show');
				
			}
			 
			 
		 }
    	
	}
	
	
	public function get_zhibo_data_action(){
		
		 $data=$this->curl_file_get_contents("http://mp.jingchang.tv/live/api/getPlayerList?page=1&num=50&callback=callback");
		 
   

		 $return_data=$this->jsonp_decode($data, $assoc = false);
		 
		 $return_array=$this->object2array($return_data);

		 $jiali_zhibo=$return_array['data'];
		 
		 
		 $mdl_voting_item=$this->loadModel("voting_item");
		 
		  foreach ($jiali_zhibo as $key => $value) {
			  
			  $player_no= $value['player_no'];
			  $vote_count= $value['vote_count'];
              $vote_id = $this->get_jiali_vote_id($player_no);
			  $data0=array(
			     'zhibo_count'=>$vote_count
			  );
			 // var_dump('play_no'.$player_no.'  ' .$vote_count. ' ' .$vote_id);
			  $mdl_voting_item->update($data0,$vote_id);
		  }
		 

		// var_dump($jiali_zhibo[0]['id']);
		//var_dump($jiali_zhibo);exit; 	
		//	var_dump($data);exit; 	
		echo  $data;return; 
		//echo  json_decode($jiali_zhibo);return; 
	}
		
		
	
function object2array($object) {
             $object =  json_decode( json_encode( $object),true);
             return  $object;
}


	function jsonp_decode($jsonp, $assoc = false)
		{
			$jsonp = trim($jsonp);
			if(isset($jsonp[0]) && $jsonp[0] !== '[' && $jsonp[0] !== '{') {
				$begin = strpos($jsonp, '(');
				if(false !== $begin)
				{
					$end = strrpos($jsonp, ')');
					if(false !== $end)
					{
						$jsonp = substr($jsonp, $begin + 1, $end - $begin - 1);
					}
				}
			}
			return json_decode($jsonp, $assoc);
		}
			
	
	public  function vote_screen_action() {
		
		//ignore_user_abort();//关闭浏览器仍然执行
		//set_time_limit(0);//让程序一直执行下去
		//$interval=3000;//每隔一定时间运行
	//	$mdl_voting_item=$this->loadModel("voting_item");
		
	//$msg=date("Y-m-d H:i:s");
			//file_put_contents("log.log",$msg,FILE_APPEND);//记录日志
		//  sleep($interval);//等待时间，进行下一次操作。
		//	  $data=$this->curl_file_get_contents("http://mp.jingchang.tv/live/api/getPlayerList?page=1&num=50&callback=callback");
		//	    var_dump($data);exit;
			  
		//	   $zhibo_data = json_decode($data, true); 
		//	    var_dump($zhibo_data);exit;
		//	  $zhibo_jiali_data=$zhibo_data['data'];
		//	  var_dump($zhibo_jiali_data);exit;
		//	  foreach ($zhibo_jiali_data as $key => $value) {
		//		 $zhibo_jiali_data[key]['vote_id']=get_jiali_vote_id($value['player_no']);
		//	  }
		//	  var_dump($zhibo_jiali_data);exit;

/*

	do{
			
		}while(true);
		
*/		
	    // $data=$this->curl_file_get_contents("http://mp.jingchang.tv/live/api/getPlayerList?page=1&num=50&callback=callback");
		// var_dump($data);exit;
		 $this->display('brandstore/2019miss/miss2/sortMiss2_middle_newest');
		
		
	}
	
	public  function vote_screen_online_action() {
		
		//ignore_user_abort();//关闭浏览器仍然执行
		//set_time_limit(0);//让程序一直执行下去
		//$interval=3000;//每隔一定时间运行
	//	$mdl_voting_item=$this->loadModel("voting_item");
		
	//$msg=date("Y-m-d H:i:s");
			//file_put_contents("log.log",$msg,FILE_APPEND);//记录日志
		//  sleep($interval);//等待时间，进行下一次操作。
		//	  $data=$this->curl_file_get_contents("http://mp.jingchang.tv/live/api/getPlayerList?page=1&num=50&callback=callback");
		//	    var_dump($data);exit;
			  
		//	   $zhibo_data = json_decode($data, true); 
		//	    var_dump($zhibo_data);exit;
		//	  $zhibo_jiali_data=$zhibo_data['data'];
		//	  var_dump($zhibo_jiali_data);exit;
		//	  foreach ($zhibo_jiali_data as $key => $value) {
		//		 $zhibo_jiali_data[key]['vote_id']=get_jiali_vote_id($value['player_no']);
		//	  }
		//	  var_dump($zhibo_jiali_data);exit;

/*

	do{
			
		}while(true);
		
*/		
	    // $data=$this->curl_file_get_contents("http://mp.jingchang.tv/live/api/getPlayerList?page=1&num=50&callback=callback");
		// var_dump($data);exit;
		 $this->display('brandstore/2019miss/miss2/sortMiss2_online2');
		
		
	}
	
	function get_jiali_vote_id($zhibo_id) {
		
		switch ($zhibo_id) {
				
				case 24:
				    $jiali_id=473;
					break;
				
				case 1:
					$jiali_id=437;
				    break;
			
				case 10:
					$jiali_id=464;
				    break;
				
				case 33:
					$jiali_id=475;
				    break;
					
				case 20:
					$jiali_id=456;
				    break;
					
				case 27:
					$jiali_id=430;
				    break;

				case 7:
					$jiali_id=470;
				    break;
					
				case 9:
					$jiali_id=479;
				    break;
					
				case 12:
					$jiali_id=438;
				    break;
					
				case 13:
					$jiali_id=440;
				    break;
					
				case 11:
					$jiali_id=431;
				    break;
					
				case 16:
					$jiali_id=439;
				    break;
					
				case 23:
					$jiali_id=468;
				    break;
					
				case 6:
					$jiali_id=463;
				    break;
				
				case 8:
					$jiali_id=453;
				    break;
					
				case 4:
					$jiali_id=483;
				    break;
					
				case 32:
					$jiali_id=485;
				    break;
					
				case 15:
					$jiali_id=455;
				    break;
					
				case 30:
					$jiali_id=482;
				    break;
					
    			default:
					$jiali_id=999;
					break;
		}
		return $jiali_id;
	}
	
	
	
	function curl_file_get_contents($durl){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $durl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回  
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回  
    $r = curl_exec($ch);
    curl_close($ch);
    return $r;
}
	
	public  function vote_screen_small_action() {
		
	
		 $this->display('brandstore/2019miss/miss2/sortMiss2_right');
		
		
	}
	
	
	public function vote_weekly_action() {
		
		$jiali_id =(int)get2('id');
		$this->setData($jiali_id,'jiali_id');
		
		$sql ="select id, title,weeks,sum(wechat_total) as wechat_total,sum(vote_total) as vote_total , sum(gift_total) as gift_total ,sum(selling_total) as selling_total,(sum(wechat_total) + sum(vote_total) + sum(gift_total) + sum(selling_total)) as point_total from ( SELECT a.id,a.title,b.weeks, b.vote_total,0 as gift_total ,0 as wechat_total,0 as selling_total from cc_voting_item a, (select vote_id ,DATE_FORMAT(FROM_UNIXTIME(`createTime`+7200),'%Y-%u') weeks,sum(vote_count) as vote_total from cc_vote_miss_quick_vote group by vote_id,weeks) as b where a.group_id=9 and b.vote_id=a.id UNION SELECT a.id,a.title,b.weeks,0 as vote_total,b.vote_total as gift_total ,0 as wechat_total,0 as selling_total from cc_voting_item a, (select vote_id ,DATE_FORMAT(FROM_UNIXTIME(`createTime`+7200),'%Y-%u') weeks,sum(vote_count) as vote_total from cc_vote_miss_gift_vote group by vote_id,weeks) as b where a.group_id=9 and b.vote_id=a.id UNION SELECT a.id,a.title,b.weeks,0 as vote_total,0 as gift_total, 0 as wechat_total,b.vote_total as selling_total from cc_voting_item a, (select vote_id ,DATE_FORMAT(FROM_UNIXTIME(`createTime`+7200),'%Y-%u') weeks,sum(vote_count) as vote_total from cc_vote_miss_selling group by vote_id,weeks) as b where a.group_id=9 and b.vote_id=a.id union SELECT a.id,a.title,b.weeks, 0 as vote_total,0 as gift_total ,b.vote_total as wechat_total,0 as selling_total from cc_voting_item a left join (select cast(content as unsigned integer) as vote_id ,DATE_FORMAT(FROM_UNIXTIME(`createTime`+7200),'%Y-%u') weeks,count(id) as vote_total from wxmessage where cast(content as unsigned integer)>0 group by vote_id,weeks) as b on ( a.id=b.vote_id ) where a.group_id=9 ) a where weeks is not null  group by id,weeks order by weeks desc,point_total desc";
		
		$mdl_voting_item = $this->loadModel( 'voting_item' );
		$data =$mdl_voting_item->getListBySql($sql);
		if ($data) {
			

			$first=1;
			$old_week=0;
			$new_week=0;
			$cat_index=0;
			foreach ($data as $key => $value) {
				
				$new_week =$data[$key]['weeks'];
				
				if($old_week <> $new_week) {
					$data[$key]['new_cat']=1;
					$cat_index=0;
					$data[$key]['cat_index']=0;
				}else{
					$data[$key]['new_cat']=0;
					$cat_index=$cat_index+1;
					$data[$key]['cat_index']=$cat_index;
					
				}
				$old_week =$data[$key]['weeks'];
				
				$year=substr($data[$key]['weeks'],0,3);
				$week=substr($data[$key]['weeks'],5,6)-1;
				$time = strtotime("1 January 2019", time());
				$day = date('w', $time);
				$time += ((7*$week)+2-$day)*24*3600;
				$return[0] = date('Y-n-j', $time);
				$time += 6*24*3600;
				$return[1] = date('Y-n-j', $time);
				$data[$key]['weeks_str']=$return[0]. ' 至 ' .$return[1]. '当周Top20排名';
				$data[$key]['weeks_str1']=$return[0]. ' 至 ' .$return[1]. '人气累计';
				//$data[$key]['new_cat']=1;
                $data[$key]['time'] =time();
			
			
			
			}
			
		 $this->setData($data,'data');
		$this->display('brandstore/2019miss/miss_weekly_rank');
		}
		
		
	//	var_dump('error');exit;
		
		
		
		
		
		
	}
	
	
	 public function list_action()
	{	
	
	
	
	   if($this->getUserDevice()=='desktop'){
			$this->pc_list();
		}else{
				$this->mobile_list();
			}
			
			
			
	
	}
	
	
	function pc_list() {
		
		
		$type=(int)get2('type');
	
	    $this->setData($type,'type');
		
	    $key=get2('key');
	
	    $this->setData($key,'searchKeywords');
	
		/**
		 * 排序规则
		 * @var [type]
		 */
		$orderby = trim( get2( 'orderby' ) );
		if ( ! in_array( $orderby, array( 'default','id', 'hits', 'buy' ) ) ) $orderby = 'id';

		$this->setData( $orderby, 'orderby' );




     	$cityid = (int)get2('cityid');
		
		if (!$cityid){
			$city1 =$this->city;
		
		}else{
		//var_dump('cityid is: '.$cityid);exit;
		 $city1 = $this->loadModel('city')->get( $cityid );
        
		}
      
		/**
		 * 产品分类模组
		 */
		$mdl_article_type = $this->loadModel( 'article_type' );

		/**
		 * 产品类型模组
		 */
		$mdl_coupon_type = $this->loadModel('coupon_type');

		/**
		 * 高级检索模组
		 */
		$mdl_advancedKeySearch=$this->loadModel('advancedKeySearch');

		/**
		 * 产品模组
		 */
		 $mdl_article = $this->loadModel('article');

		/**
		 * 用户模组
		 */
		$mdl_user = $this->loadModel( 'user' );
		
		/**
		 * 评价模组
		 */
		$mdl_customer_rating =$this->loadModel('wj_customer_rating');



		/**
		 * 页面分类动态显示
		 */
		 $sql_article_type_list ="select * from cc_article_type";
		$article_type_list=$mdl_article_type->getListBySql( $sql_article_type_list );//single

		//如果有子分类选择,那么找到分类名称,创建页面标题及关键字描述
		if($type) {
	      foreach($article_type_list as $key => $value){
			  if ($value['idd']==$type) {
				
				  $cat_name = $article_type_list[$key]['cat_name'];
				  $this->setData($cat_name,'cat_name');
				  break;
			  }
			  
		  }
	    }
        // var_dump($article_type_list);exit;
		$this->setData( $article_type_list, 'article_type_list' );
		
		$field ="`id`,`title`,`Description`,`category_id`,`cat_name`,`keyword1`,`keyword2`,`pic`,`pics`,`link`,`is_approved`,`status`,`createUserId`,`createTime`,`editor`,`businessName`,`city`,`cityName`,`country_code`,`hits`,`read_userId`,`select_city_id`,`select_cat_id`,`recommend`,`restaurant_recommend` ";
	    if($type) {
		 $where = " and category_id =".$type;
		}
		if($key) {
			 $where = " and ( keyword1 like '%".$key."%' or keyword2 like '%".$key."%' or title like '%".$key."%')";
			
		}
			 
			 $pageSql	  = "select " .$field . "  from cc_article where is_approved=1 and status=4 and createUserId=201964 ".$where." order by  ".$orderby  ." desc";
	
           
			$pageUrl	= $this->parseUrl()->set('page')->set( 'listType','coupons');
			$pageSize	= 30;
			$maxPage	= ($this->getUserDevice()=='desktop')?10:0;
			$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
			//var_dump($pageSql);exit;
			$data		= $mdl_article->getListBySql($page['outSql']);
			

			$this->setData( $page, 'pager' );

			$this->setData( $data, 'data' );
	

        $this->get_google_seo_info_base_search($data,$city1,$category,$searchKeywords);
		

		//$this->setData( $mdl_coupons->getRandom(),'guessYouLike');


		$this->setData( $this->parseUrl()->set( 'page' ), 'catUrl' );
		$this->setData( $this->parseUrl()->set( 'page' )->set( 'orderby' ), 'orderbyUrl' );
		$this->setData( $this->parseUrl()->set( 'page' )->set( 'cityid' ), 'cityUrl' );
		$this->setData( $this->parseUrl()->set( 'page' )->set( 'key' ), 'searchUrl' );
	

	



		
			$this->responseDisplay('article/search_result');
		
	
		
		
		
		
	}
	
	function mobile_list(){
		
		
	    $type=(int)get2('type');
	
	    $this->setData($type,'type');
	    $field ="`id`,`title`,`Description`,`category_id`,`cat_name`,`keyword1`,`keyword2`,`pic`,`pics`,`link`,`is_approved`,`status`,`createUserId`,`createTime`,`editor`,`businessName`,`city`,`cityName`,`country_code`,`hits`,`read_userId`,`select_city_id`,`select_cat_id`,`recommend`,`restaurant_recommend` ";
		
		$sql_newest = "select " .$field . "  from cc_article where is_approved=1 and status=4 and createUserId=201964 order by createTime  Desc limit 50";
		
		//美食攻略
		$sql_recommend = "select " .$field . "  from cc_article where is_approved=1 and status=4 and createUserId=201964 and recommend=1 order by createTime  Desc limit 50";
		
		$sql_gonglue = "select " .$field . "  from cc_article where  category_id='1' and is_approved=1 and status=4 and createUserId=201964  order by createTime  Desc limit 50";
		
		$sql_restaurant = "select " .$field . "  from cc_article where  category_id='2' and is_approved=1 and status=4 and createUserId=201964  order by restaurant_recommend,createTime  Desc limit 50";
	
		
		$sql_news_articles = "select " .$field . "  from cc_article where  category_id='4' and is_approved=1 and status=4 and createUserId=201964  order by createTime  Desc limit 50";
	
	
		 $mdl_article = $this->loadModel('article');
		// var_dump($sql_restaurant);exit;
		 $article_newest =$mdl_article->getListBySql($sql_newest);
		 $article_recommend =$mdl_article->getListBySql($sql_recommend);
		 $article_gonglue =$mdl_article->getListBySql($sql_gonglue);
		 $article_restaurant=$mdl_article->getListBySql($sql_restaurant);
		 $article_news_articles =$mdl_article->getListBySql($sql_news_articles);
	
		 //var_dump($sql_newest);exit;
		 $this->setData($article_newest,'article_newest');
		 $this->setData($article_recommend,'article_recommend');
		 $this->setData($article_gonglue,'article_gonglue');
		 $this->setData($article_restaurant,'article_restaurant');
		 $this->setData($article_news_articles,'article_news_articles');
	
	      //var_dump('here');exit;
		  
		  
		  

	
		$this->display('mobile/article/mobile_list');
	}
	
	function get_google_seo_info_base_search($coupons,$city,$category,$keywords) {
	
	
	// 如果分类为票务
	 if($category['id']=='106119111'){
		 		
		$this->setData( '墨尔本 演唱会 | 墨尔本 票务活动' , 'pageTitle' );
      	$this->setData('墨尔本 演唱会 | 墨尔本 票务活动', 'pageKeywords' );
		$this->setData('Ubonus 美食生活,墨尔本最棒的票务销售平台,超过百场演唱会及票务发售,包括那英,张惠妹,张学友,周杰伦,郭德纲,李健,莫文蔚,梁静茹,林忆莲,周华健等等演唱会,与众多演出单位和媒体长期合作' , 'description' );
		$this->setData( 'Ubonus 美食生活,墨尔本最棒的票务销售平台,超过百场演唱会及票务发售,包括那英,张惠妹,张学友,周杰伦,郭德纲,李健,莫文蔚,梁静茹,林忆莲,周华健等等演唱会,与众多演出单位和媒体长期合作' , 'pageDescription' );
		
		$this->setData('墨尔本 演唱会 | 墨尔本 票务活动' , 'str' );
		 return;
	 }
	
	  if($category['id']=='106126112' && $city['id']==639){
		 // var_dump($city);exit;		
		$this->setData( '墨尔本 粤菜 | 墨尔本 市区 粤菜 | 粤菜' , 'pageTitle' );
      	$this->setData('墨尔本 粤菜 | 墨尔本 市区 粤菜 | 粤菜', 'pageKeywords' );
		$this->setData('Ubonus 美食生活为您搜罗最棒的墨尔本 粤菜以及墨尔本 市区 粤菜餐厅, 并且提供多样的美食券折扣信息。新用户有很多优惠赠送，快来注册吧。' , 'description' );
		$this->setData( 'Ubonus 美食生活为您搜罗最棒的墨尔本 粤菜以及墨尔本 市区 粤菜餐厅, 并且提供多样的美食券折扣信息。新用户有很多优惠赠送，快来注册吧。' , 'pageDescription' );
		
		$this->setData('墨尔本 粤菜 | 墨尔本 市区 粤菜 | 粤菜' , 'str' );
		 return;
	 }
	 
	  if($category['id']=='106126109' && $city['id']==639){
		 // var_dump($city);exit;		
		$this->setData( 'Chinatown 川菜 | 墨尔本唐人街 美食' , 'pageTitle' );
      	$this->setData('Chinatown 川菜 | 墨尔本唐人街 美食', 'pageKeywords' );
		$this->setData('微奖网为您精选墨尔本唐人街 美食商家，满足不同地区，不同口味的消费者的不同需求，同时兼顾折扣及团购信息，让大家吃的省钱，省心。更多Chinatown 川菜，粤菜等菜系的活动请关注网站信息。。' , 'description' );
		$this->setData( '微奖网为您精选墨尔本唐人街 美食商家，满足不同地区，不同口味的消费者的不同需求，同时兼顾折扣及团购信息，让大家吃的省钱，省心。更多Chinatown 川菜，粤菜等菜系的活动请关注网站信息。' , 'pageDescription' );
		
		$this->setData('Chinatown 川菜 | 墨尔本唐人街 美食' , 'str' );
		 return;
	 }
	
	 if($category['id']=='106126'  ){
		 
		 if($city['id'] =='639') {
			 
			 
		$this->setData( ' Chinatown 美食 | 墨尔本 市区 美食 ' , 'pageTitle' );
      	$this->setData(' Chinatown 美食 | 墨尔本 市区 美食', 'pageKeywords' );
		$this->setData('Ubonus为华人朋友提供关于墨尔本市区美食折扣及团购的信息。更多chinatown美食团购折扣信息可以登录微奖网查询，还可以获得更多优惠体验。' , 'description' );
		$this->setData( 'Ubonus为华人朋友提供关于墨尔本市区美食折扣及团购的信息。更多chinatown美食团购折扣信息可以登录微奖网查询，还可以获得更多优惠体验。' , 'pageDescription' );
		
		$this->setData( 'Chinatown 美食 | 墨尔本 市区 美食 ' , 'str' );
		 return;

		 }else if ( $city['id'] =='556' ){
		 		
		$this->setData( '墨尔本 美食券 | 墨尔本 美团' , 'pageTitle' );
      	$this->setData('墨尔本 美食券 | 墨尔本 美团', 'pageKeywords' );
		$this->setData('作为专注墨尔本华人美食生活的电商平台，ubonus为您提供多种的墨尔本美食券以及墨尔本美团信息。更多折扣请关注我们公众号 UbonusMel' , 'description' );
		$this->setData( '作为专注墨尔本华人美食生活的电商平台，ubonus为您提供多种的墨尔本美食券以及墨尔本美团信息。更多折扣请关注我们公众号 UbonusMel' , 'pageDescription' );
		
		$this->setData( '墨尔本 美食券 | 墨尔本 美团' , 'str' );
		 return;
		}else if ( $city['id'] =='644' ){
		 		
		$this->setData( 'clayton 美食 | clayton 火锅 | clayton 川菜' , 'pageTitle' );
      	$this->setData('clayton 美食 | clayton 火锅 | clayton 川菜', 'pageKeywords' );
		$this->setData('微奖网为您精选clayton 美食商家，满足不同地区，不同口味的消费者的不同需求，同时兼顾折扣及团购信息，让大家吃的省钱，省心。更有clayton 火锅以及clayton 川菜等优惠信息。' , 'description' );
		$this->setData( '微奖网为您精选clayton 美食商家，满足不同地区，不同口味的消费者的不同需求，同时兼顾折扣及团购信息，让大家吃的省钱，省心。更有clayton 火锅以及clayton 川菜等优惠信息。' , 'pageDescription' );
		
		$this->setData( 'clayton 美食 | clayton 火锅 | clayton 川菜' , 'str' );
		 return;
		}else{
			
			
		}
		
	 }	
		
		
		if($this->set_special_coupon_id_seo('',$keywords)) return;
		
		if($category['name']=='行业分类') {
			$category['name']='Ubonus 美食生活';
		}
		
		
		if ($city) 
		//获取city 的父级城市
	    $cityparent= $this->loadModel('city')->get( $this->city1['parentId'] );
	    // var_dump($cityparent);exit;
	   
	   // 1: 有关键字;2:无关键字
	
	   
	   if($keywords){
		   
		  $category['name']=$keywords;
	   }   
		   
		   
		   
	
		   
		   //标题: 分类名 + 城市 | 城市 + 分类名    ,
           //Key word:
           //H1 : 都是一样呢

           //Decription : Ubonus 美食生活提供 城市 . 地区的 “搜索字” 或”分类”的信息,包括 循环几个商家产品(比如6个)...
		   
		   if($category['name']) $str=$str.' '.$category['name'];
		   if ($city)   $str=$str.' '.$city['name'];
		   if($cityparent) {
			   $str=$str.' '.$cityparent['name'];
		   } 
		   
		   if ($city)   $str1=$str1.' '.$city['name'];
		   if($category['name']) $str1=$str1.' '.$category['name'];
		   if($cityparent) {
			   $str1=$str1.' '.$cityparent['name'];
		   } 
		   $description =$cityparent['name'].' ' .$city['name'].' ' . $category['name'].", Ubonus365 ";
		   //var_dump($coupons);exit;
		   if ($coupons){
			   $i=0;
			   foreach($coupons as $key => $value){
				  $description1  .=$value['title'];
				  $i++;
				  if($i==6) break;
			   }
			   $description1 =$description.$description1;
		   }
		   $str=$str.', '.$str1;
		  
	   
			
		$this->setData( $str , 'pageTitle' );
      	$this->setData($str, 'pageKeywords' );
		$this->setData( $description , 'description' );
		$this->setData( $description1 , 'pageDescription' );
		
		$this->setData( $str , 'str' );
	}

	function set_special_coupon_id_seo($couponid,$keywords) {
		
	
		
		
		if($keywords =='咖啡') {
		
		 $this->setData( '墨尔本 咖啡' , 'pageTitle' );
		 $this->setData( '墨尔本 咖啡' , 'h1' );
		 $this->setData( '墨尔本 咖啡', 'pageKeywords' );
		 $this->setData( '墨尔本 咖啡是世界闻名的，墨尔本人的早上是由咖啡开始的，Ubonus美食生活为墨尔本华人朋友精选墨尔本咖啡及团购折扣信息，让大家在咖啡的香气中开始忙碌的一天。' , 'pageDescription' );
		return true;
		}
		
		if($keywords =='brunch') {
		
		 $this->setData( '墨尔本 brunch | 墨尔本 brunch' , 'pageTitle' );
		 $this->setData( '墨尔本 brunch | 墨尔本 brunch' , 'h1' );
		 $this->setData( '墨尔本 brunch | 墨尔本 brunch', 'pageKeywords' );
		 $this->setData( '墨尔本 brunch作为墨尔本一大特色，是众多文艺人士的心头好，Ubonus微奖网为大家提供了 墨尔本 brunch的折扣及团购信息。' , 'pageDescription' );
		return true;
		}
		
		if($keywords =='干锅香锅') {
		
		 $this->setData( '墨尔本 干锅香锅 | 墨尔本 干锅香锅' , 'pageTitle' );
		 $this->setData( '墨尔本 干锅香锅 | 墨尔本 干锅香锅' , 'h1' );
		 $this->setData( '墨尔本 干锅香锅 | 墨尔本 干锅香锅', 'pageKeywords' );
		 $this->setData( 'Ubonus 微奖网 为您搜罗最棒的墨尔本美食以及墨尔本 干锅香锅餐厅, 并且提供多样的美食券折扣信息。新用户有很多优惠赠送，快来注册吧。' , 'pageDescription' );
		return true;
		}
		
		return false;
	}
}
 ?>
