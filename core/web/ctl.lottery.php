<?php

//会员

class ctl_lottery extends cmsPage
{
	
	function ctl_lottery() {
		parent::cmsPage();
		
		$act = $GLOBALS['gbl_act'];
		$ignore_list = array('index','lottery_show','lottery_get_result','business_records_update','lottery','business_records_management');
		if ( !in_array($act, $ignore_list) && !$this->loginUser ) {
			$this->sheader( HTTP_ROOT_WWW.'member/login?returnUrl='.urlencode( $_SERVER['REQUEST_URI'] ) );
		}
	
	}

	
	function lotterys_action() {

			$id = (int)get2('id');
			$mdl_lottery = $this->loadModel( 'lottery' );

			if ( $id > 0 ) {
				//delete action
				$where['id']=$id;
				$where['createUserId']=$this->loginUser['id'];
				if ($mdl_lottery->getByWhere($where)) $mdl_lottery->deleteByWhere($where);
			}
           
			$sql ="select * from #@_lottery  where createUserId=".$this->loginUser['id']." order by id desc";
			$list = $mdl_lottery->getListBySql($sql);
			$this->setData( $list, 'list' );

			$this->setData( '抽奖管理', 'pagename' );
			$this->setData( 'lotterys', 'submenu' );
			$this->setData( 'community', 'menu' );
			$this->setData( '抽奖信息 - 商家中心 - '.$this->site['pageTitle'], 'pageTitle' );
			$this->display( 'lottery/lotterys' );
		}

	public function user_records_management_action()
	{	
		$data =  $this->loadModel('lottery_records')->getWinningRecordsOfUser($this->loginUser['id']);
		//var_dump ($this->loginUser['id']);exit;
		$this->setData($data,'data');

		$this->setData( '查看中奖 - '.'产品 - 商家中心 - '.$this->site['pageTitle'], 'pageTitle' );
		$this->display('lottery/user_records_management');
	}

	public function business_records_management_action()
	{	
		$lotteryId =get2('lottery_id');

		$status_list=$this->loadModel('lottery_status')->getList();
		$this->setData($status_list,'status_list');

		$search=get2('search');

		$pageSql = $this->loadModel('lottery_records')->getWinningRecordsOfBusinessSql(23989,$lotteryId,$search);

		//echo $pageSql;exit;
		$pageUrl	= $this->parseUrl()->set('page');
		$pageSize	= 10;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $this->loadModel('lottery_records')->getListBySql($page['outSql']);

		$this->setData( $data, 'data' );
		$this->setData( $page['pageStr'], 'pager' );

		$this->setData($search,'search');
		$this->setData($this->parseUrl()->set('search')->set('page'),'searchUrl');

		$this->setData( 'lotterys', 'submenu' );
		$this->setData( 'community', 'menu' );
		
		$this->setData( '查看中奖 - '.'产品 - 商家中心 - '.$this->site['pageTitle'], 'pageTitle' );
		$this->display('lottery/business_records_management');
	}

	public function business_records_update_action()
	{
		if(is_post()){
			$id = post('id');
			$status = post('status');

			$result=$this->loadModel('lottery_records')->updateWinningRecordsStatus($id,$status);
			if($result){
				//success
				echo 'success';
			}else{
				//fail
				echo 'fail';
			}
		}else{
			//fail
			echo 'fail';
		}		
		
	}
	
	
		
	function new_lottery_action() {
	 	$id = (int)get2('id');
		$mdl_lottery = $this->loadModel( 'lottery' );
			
		if ( is_post() ) {
			$cityId = (int)end( post( 'city' ) );

			$description = post( 'description' ) ;
			$redeem =  post( 'redeem' ) ;
			$redeem_details =  post( 'redeem_details' ) ;
			
			$type = trim( post( 'type' ) );
			$createtime=time();
			$pic1= trim( post( 'pic1' ) );
			$pic2= trim( post( 'pic2' ) );
			$title= trim( post( 'title' ) );
			$expired_time= trim( post( 'expired_time' ) );
			$createUserId= trim( post( 'createUserId' ) );
			
			
			//还有一个google map的坐标
			if ( $title == '' ) {
				$this->form_response_msg("请填写抽奖名称");
            }      
			
			$data = array(
					'cityId' => $cityId,
					'description' => $description,
					'redeem' => $redeem,
					'redeem_details' => $redeem_details,
					'type' => $type,
					'createtime' => $createtime,
					'title' => $title,
					'expired_time' => $expired_time,
					'createUserId' => $this->loginUser['id']
					
					
					
					);
		    //做为抽奖管理人员可以访问该字段
			if($createUserId){
				
				$data['createUserId']=$createUserId;
			}
			
			if ( $pic1 != '' ) {
				$data['pic1'] = $pic1;
			}
			if ( $pic2 != '' ) {
				$data['pic2'] = $pic2;
			}

			$mdl_lottery = $this->loadModel( 'lottery' );
			
			if(!$id){
			
				if ( $mdl_lottery->insert( $data)) {
				
					$this->form_response(200,'保存成功',HTTP_ROOT_WWW.'lottery/lotterys');
				}
				else {
					$this->form_response_msg("添加失败");
				}
			}else{
				if ( $mdl_lottery->update( $data,$id)) {
				
					$this->form_response(200,'保存成功',HTTP_ROOT_WWW.'lottery/lotterys');
				}
				else {
					$this->form_response_msg("修改失败");
				}
				
			}
		}
		else {
			
			$lottery= $mdl_lottery->get($id);
			
			$this->setData($lottery,'lottery');

			$this->setData( '抽奖管理', 'pagename' );
			$this->setData( 'community', 'menu' );
			$this->setData( 'lottery_new_lottery', 'submenu' );
			$this->setData( '抽奖管理 - 商家中心 - '.$this->site['pageTitle'], 'pageTitle' );
			$this->display( 'lottery/new_lottery' );
		}
	}
	
	function lottery_details_action() {
            $id = (int)get2('id');
			$lottery_id = (int)get2('lottery_id');
			$mdl_lottery_details = $this->loadModel( 'lottery_details' );
			$mdl_lottery = $this->loadModel( 'lottery' );

			$where['id']=$lottery_id;
			$where['createUserId']=$this->loginUser['id'];
			
			if(!$this->loadModel('lottery')->getByWhere($where))$this->sheader(null, '您要编辑的抽奖不存在,或者不是您的');

			if ( $id > 0 ) {
				//delete action 
				$where1['id']=$id;
				$where1['createUserId']=$this->loginUser['id'];
				if ( $mdl_lottery_details->getByWhere( $where1 ))$mdl_lottery_details->deleteByWhere($where1); 
			}
 
            $sum_total_award = $this->sum_total_award($lottery_id,$mdl_lottery_details);
			$this->setData($sum_total_award,'sum_total_award');

			$lottery = $mdl_lottery->get($lottery_id);
			$title =$lottery['title'];
            
			$where = array( 'createUserId' => $this->loginUser['id'],'lottery_id'=>$lottery_id);
			
			$list = $mdl_lottery_details->getList( array('id','is_award','lottery_id','lottery_sub_id','lottery_sub_name','lottery_sub_details','Probability','qty'), $where);
			$this->setData( $list, 'list' );

			$this->setData( $lottery_id, 'lottery_id' );
			$this->setData( $title, 'title' );
			$this->setData( '抽奖奖品及概率管理', 'pagename' );
			$this->setData( 'lottery_details', 'submenu' );
			$this->setData( 'community', 'menu' );
			$this->setData( '商品参数明细管理 - 商家中心 - '.$this->site['pageTitle'], 'pageTitle' );
			$this->display( 'lottery/lottery_details' );
		}

		public function lottery_show_action()
		{	
		   
			$ajax = (int)get2( 'ajax' );
			$mdl_lottery_draw_bit_counts=$this->loadModel('lottery_draw_bit_counts');

			$where = array();
			$where['is_approved'] = 1;
			$where['status'] = 1;

			$pageSql	= $mdl_lottery_draw_bit_counts->getListSql( null, $where, "lottery_id desc" );
            //echo $pageSql; exit;
			$pageUrl	= $this->parseUrl()->set('page');
			$pageSize	= 30;
			$maxPage	= 10;
			$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
			$data		= $mdl_lottery_draw_bit_counts->getListBySql($page['outSql']);
							
			
			$this->setData( $data, 'heads' );
			$this->setData( $this->parseUrl()->set('page')->set('ajax', 1), 'ajaxUrl' );
			if ( $ajax === 1 ) {
				if ( $page['cp'] >= 1 && $page['cp'] != (int)get2( 'page' ) ) {
					//dont return anything
				}else{
					$this->display( 'lottery/lottery_show_ajax' );
				}
				exit;
			}
		
			$this->setData( 'category', 'footer_menu' );
			$this->setData( '抽奖 - '.$this->site['pageTitle'], 'pageTitle' );
			
			
			$this->display('lottery/lottery_show');
			
		}
		
	
	// 根据当前的抽奖项目，求所有奖品数量之和 （包括没有中奖的奖项）
	function sum_total_award($lottery_id,$mdl_lottery_details){
		$where =array(
		    'lottery_id'=>$lottery_id
		);
		$field=array(
		 'Probability'
		);
		
		return $mdl_lottery_details->getSum( 'Probability', $where );
		
	}

	function lottery_publish_ajax_action() {
			$id = (int)get2('id');
			$mdl_lottery = $this->loadModel( 'lottery' );
			
			if($id<0)$this->form_response_msg('lottery id invalid');

			$lottery = $mdl_lottery->get( $id );

			if($lottery['createUserId']!=$this->loginUser['id'])$this->form_response_msg('lottery do not belong to you');

			$data=array();
			$data['status']=($lottery['status'] =='1')?'0':'1';
			if($mdl_lottery->update($data, $id)){
				echo json_encode(array('lottery_status'=>$data['status']));
			}else{
				$this->form_response_msg('Please try again later');
			}

			
	}

	function lottery_details_new_action(){
		$lottery_id=(int)get2('lottery_id');

		$where['id']=$lottery_id;
		$where['createUserId']=$this->loginUser['id'];
		
		if(!$this->loadModel('lottery')->getByWhere($where))$this->sheader(null, '您要编辑的抽奖不存在,或者不是您的');

		$mdl_lottery_details = $this->loadModel( 'lottery_details' );

		if(is_post()){
			$lottery_id = post('lottery_id');
			$lottery_sub_id = post('lottery_sub_id');
			$lottery_sub_name = post('lottery_sub_name');
			$createUserId = $this->loginUser['id'];
			$lottery_sub_details = post('lottery_sub_details');
			$qty = post('qty');
			$Probability = post('Probability');
			$is_award = post('is_award');

			$is_online_discount=post('is_online_discount');
			$online_discount_amount=post('online_discount_amount');
			$min_spending=post('min_spending');
			$expireindays=post('expireindays');

			if ( empty( $lottery_sub_name )   ) {
				$this->form_response_msg("请填写奖品名称");
			}
			if(strlen($lottery_sub_name)>50){
				$this->form_response_msg("每次提交字符串长度最大为50");
			}
			if ( empty( $lottery_sub_id )   ) {
				$this->form_response_msg("请填写奖品名称编号");
			}

			if($is_online_discount==0){
				//not online discount
				$online_discount_amount=0;
				$min_spending=0;
				$expireindays=0;
			}else{
				//is online discount
				if($online_discount_amount<=0)$this->form_response_msg("打折码抵用金额不能为空");
			}
			
			$data = array(
			  	'createUserId'=>$this->loginUser['id'],
				'lottery_id'=>$lottery_id,
				'lottery_sub_id'=>$lottery_sub_id,
				'lottery_sub_name'=>$lottery_sub_name,
				'lottery_sub_details'=>$lottery_sub_details,
				'qty'=>$qty,
				'Probability'=>$Probability,
				'is_award'=>$is_award,
				'online_discount'=>$is_online_discount,
				'online_discount_amount'=>$online_discount_amount,
				'min_spending'=>$min_spending,
				'expireindays'=>$expireindays,

			);

			if ( $mdl_lottery_details->insert( $data ) ) 
				$this->form_response(200,'保存成功',HTTP_ROOT_WWW.'lottery/lottery_details?lottery_id='.$lottery_id);

		}else{
			$this->setData( $lottery_id, 'lottery_id' );
			$this->setData( '商品规格参数管理', 'pagename' );
			$this->setData( 'lottery', 'submenu' );
			$this->setData( 'community', 'menu' );
			$this->setData( '商品规格参数管理 - 商家中心 - '.$this->site['pageTitle'], 'pageTitle' );
			$this->display( 'lottery/lottery_details_edit' );
		}

	}

		
	function lottery_details_edit_action() {

			// 如果不是处于编辑状态，在新增时，需要记录当前编辑奖品对应的抽奖记录信息。
			$lottery_id = (int)get2('lottery_id');
			$where['id']=$lottery_id;
			$where['createUserId']=$this->loginUser['id'];
			
			if(!$this->loadModel('lottery')->getByWhere($where))$this->sheader(null, '您要编辑的抽奖不存在,或者不是您的');

			//此处如果获得一个id ,代表进入编辑某一个奖品的处理，所以，此处获得该 id 对应纪录的所有信息。
			$id = (int)get2( 'id' );
			$mdl_lottery_details = $this->loadModel( 'lottery_details' );
			$lottery_details = $mdl_lottery_details->get($id);

			if(!$lottery_details)$this->sheader(null, '您要编辑的奖项不存在,或者不是您的');
				
			if ( is_post() ) {
				$lottery_id = post('lottery_id');
				$lottery_sub_id = post('lottery_sub_id');
				$lottery_sub_name = post('lottery_sub_name');
				$createUserId = $this->loginUser['id'];
				$lottery_sub_details = post('lottery_sub_details');
				$qty = post('qty');
				$Probability = post('Probability');
				$is_award = post('is_award');

				$is_online_discount=post('is_online_discount');
				$online_discount_amount=post('online_discount_amount');
				$min_spending=post('min_spending');
				$expireindays=post('expireindays');


				if(!$is_award){
					$qty =1;
					
				}
				if($is_online_discount==0){
					//not online discount
					$online_discount_amount=0;
					$min_spending=0;
					$expireindays=0;
				}else{
					//is online discount
					if($online_discount_amount<=0)$this->form_response_msg("打折码抵用金额不能为空");
				}


				$data = array(
					'lottery_sub_id'=>$lottery_sub_id,
					'lottery_sub_name'=>$lottery_sub_name,
					'lottery_sub_details'=>$lottery_sub_details,
					'qty'=>$qty,
					'Probability'=>$Probability,
					'is_award'=>$is_award,
					'online_discount'=>$is_online_discount,
					'online_discount_amount'=>$online_discount_amount,
					'min_spending'=>$min_spending,
					'expireindays'=>$expireindays
					);
				
				if ( $mdl_lottery_details->update( $data, $lottery_details['id'] ) ) 
					$this->form_response(200,'保存成功',HTTP_ROOT_WWW.'lottery/lottery_details?lottery_id='.$lottery_id);
				
				
			}
			else {
				$this->setData( $lottery_id, 'lottery_id' );
				$this->setData( $lottery_details,'data');
				$this->setData( '商品规格参数管理', 'pagename' );
				$this->setData( 'lottery', 'submenu' );
				$this->setData( 'community', 'menu' );
				$this->setData( '商品规格参数管理 - 商家中心 - '.$this->site['pageTitle'], 'pageTitle' );
				$this->display( 'lottery/lottery_details_edit' );
			}
		}	
	
	function lottery_action() {
		
		// 获取当前的抽奖项目编号对应的抽奖信息。
		 $id = (int)get2('id');
		 $uid = (int)get2('uid');
		 $friends=(int)get2('friends');
		 $this->setData($friends,'friends');
		 $this->setData($uid,'uid');
		 $mdl_lottery=$this->loadModel('lottery');
		 $lottery=$mdl_lottery->get($id);
		 
		 
		if(!$lottery){
			$this->sheader( HTTP_ROOT_WWW.'lottery/lottery_show');
			return ;
		}

		if (!$this->loginUser ) {
			$this->sheader( HTTP_ROOT_WWW.'member/login?returnUrl='.urlencode( $_SERVER['REQUEST_URI'] ) );
		}
		 
		//如果 pic1 中包含'/' 表示，图片是从 upload 来得 ，如果不包含/ 表示从 images/lottery来
		if( strstr($lottery['pic1'], '/')){
			$this->setData('upload','imagePath');
		}else{
			$this->setData('lottery','imagePath');
		}
		$businessName= $this->loadModel('user')->getBusinessNameById($lottery['createUserId']);
		if (!$businessName) {
		$lottery['businessName']='Ubonus美食生活-ubonus';
		}else{
			$lottery['businessName']=$businessName;
    	}

    	$promotionData = $this->loadModel('coupons')->getMostRecentOfUser($lottery['createUserId'],6);
		$this->setData($promotionData,'promotionData');
		 
		 // 获取当前的抽奖项目对应的奖品信息
		 $mdl_lottery_details=$this->loadModel('lottery_details');
		 $where =array(
		   'lottery_id' =>$lottery['id']
		 );
		$lottery_details=$mdl_lottery_details->getByWhere($where);
		
		
		// 查看当前用户是否中奖，如果已经中奖的话，返回中奖状态。并显示相应的结果。
		 $where =array(
		    'userId'=>$this->loginUser['id'],
			'lottery_id'=>$id,
			'is_award'=>1
    	 );
		 $mdl_lottery_records =$this->loadModel('lottery_records');
		 $lottery_rec =$mdl_lottery_records->getByWhere($where);
		 
		 if($lottery_rec){
			 
			 //获得奖励名称
			 $where =array(
		    'lottery_id' =>$lottery_rec['lottery_id'],
			'lottery_sub_id'=>$lottery_rec['lottery_sub_id'],
	   	     );
		    $lottery_rec_details=$mdl_lottery_details->getByWhere($where);
			 
			$lottery_rec['lottery_name'] = $lottery_rec_details['lottery_sub_name'].": ".$lottery_rec_details['lottery_sub_details'];
			 
			 //表示中奖
			 $lottery_rec['status_desc'] =$this->get_lottery_cn_status_desc($lottery_rec['status']);
			
		     $lottery_rec['lottery_sub_name'] =$lottery_rec_details['lottery_sub_name'];
			 $this->setData($lottery_rec,'lottery_rec');
		 }
		 
		// 获取发布抽奖的商家是否有红包
		 
		 $redbagId =$this->loadModel('redbag')->get_redbagId_byBusinessUserId($lottery['createUserId']);
         $this->setData($redbagId,'redbagId');
		 
		 //  获得当前 抽取总数，获取当前中奖zhongshu
		 $totaldraw =$mdl_lottery_records->getCount();
		 $where =array(
		    is_award=>'1'
		 );
		 $totalgot =$mdl_lottery_records->getCount($where);
		 
		 
		 $this->setData($totaldraw,'totaldraw');
		 $this->setData($totalgot,'totalgot');
		 
		 
		// 获得uid，如果与Loginuserid相同，则忽略，如果不同，则作为refUserId
		if($uid>0 && $this->loginUser['id']>0 && ($uid!=$this->loginUser['id'])){
			$this->setData($uid,'ref_userId');
			
		}else{
			
			$this->setData(0,'ref_userId');
		}
		
		
		// 获得该用户的抽奖加速倍数
		// 抽奖加速倍数 可以做成一个数组，包括 当前总加速倍数 ，抽奖次数，抽奖次数带来的加速倍数，推荐朋友圈点击数，推荐朋友圈带来的家属倍数
		$user_lottery_speed_data=$this->get_user_lottery_speed_data($mdl_lottery_records);
		if($user_lottery_speed_data['total_speed']<=1){
			$this->loginUser['lottery_speed']=1;
		}else{
			$this->loginUser['lottery_speed']=$user_lottery_speed_data['total_speed']+1;
		}
		//filelog($this->loginUser['lottery_speed'],'abc.txt');
		
		
		//wx share
		require_once "wx/wxjssdk.php";
        $jssdk = new WXjsSDK();
        $signPackage = $jssdk->GetSignPackage();
        $this->setData($signPackage,'signPackage');

		// 获取Ubonus推荐列表
		 $mdl_recommend_list =$this->loadModel('explosion');
		 $recommend_list =$mdl_recommend_list->get_Recomend_list();
		 $this->setData($recommend_list,'recommend_list');
		
		$this->setData($user_lottery_speed_data,'user_lottery_speed_data');
		
		$this->setData($lottery_detals,'lottery_detals');
		$this->setData($lottery,'lottery');
		$this->setData( '抽奖', 'pagename' );
		$this->setData( 'lottery', 'menu' );
		$this->setData( 'lottery', 'submenu' );
		$this->setData( '抽奖 - 个人中心 - '.$this->site['pageTitle'], 'pageTitle' );
		$this->display( 'lottery/lottery' );
		
	}
	function  get_user_lottery_speed_data($mdl_lottery_records){
		
		$recommand_count =0;
		$recommand_speed=0;
		$self_draw_speed=0;
		$self_draw_count=0;
		$is_get_award =0;
		
		
		if(!$this->loginUser){
			
			$data['recommand_count']=0; //转发朋友圈后点击次数
		    $data['recommand_speed']=0; // 转发朋友圈后倍数计算
			$data['self_draw_speed']=0; // 自己抽奖倍数计算
			$data['self_draw_count']=0;  // 自己抽奖点击次数
			$data['total_speed']=0;
			$data['is_get_award']=0;
			return $data;
		}
		
		// 首先计算当前用户的抽奖次数,30天内
		$old_time=time()-30*24*60*60;
		
		// 获取当前用户最后一次获得2等奖以上的奖励的时间，如果获得了2等奖，那么，时间将会被cut在这个点上，而不是30天的时间，
		
		
		$where =array(
		  'userId'=>$this->loginUser['id']
		 );
		 $user_award_time = $this->loadModel("view_lottery_award_createtime")->getByWhere($where);
		 if($user_award_time){
			 // 如果存在记录表示已经中过奖
			 if ($user_award_time['createtime']>$old_time){
				 //如果该时间在30天内，那么计算倍数的时间点为该时间。
				 $old_time =$user_award_time['createtime'];
				 $is_get_award=1;
			 }
			 
		 }
		 
		
		$where =array(
		  'userId'=>$this->loginUser['id'],
		  'createtime > '.$old_time
		);
		$self_draw_count= $mdl_lottery_records->getCount($where);
		if($self_draw_count) {
			$self_draw_speed=round($self_draw_count);// /10
			if($self_draw_speed>=500){
				$self_draw_speed =500;
			}
		}
		
		// 计算当前用户推荐给朋友圈后，朋友的抽奖次数
		$where =array(
		  'ref_userId'=>$this->loginUser['id'],
		  'createtime > '.$old_time
		);
		$recommand_count= $mdl_lottery_records->getCount($where);
		if($recommand_count) {
			$recommand_speed=round($recommand_count);// /10
			if($recommand_speed>=500){
				$recommand_speed =500;
			}
		}
		
		
		
		$total_speed =$recommand_speed + $self_draw_speed;
		
		if ($total_speed<1 ){
			$total_speed = 0;
		}elseif($total_speed>1000){
            $total_speed=1000;
		}
		
		$data['recommand_count']=$recommand_count; //转发朋友圈后点击次数
		$data['recommand_speed']=$recommand_speed; // 转发朋友圈后倍数计算
		$data['self_draw_speed']=$self_draw_speed; // 自己抽奖倍数计算
		$data['self_draw_count']=$self_draw_count;  // 自己抽奖点击次数
		$data['total_speed']=$total_speed;  // 总共加速倍数
		$data['is_get_award']=$is_get_award; // 如果30天内曾将获奖
		return $data;
	}
		
	 function get_lottery_cn_status_desc($lottery_rec_status){
			switch ($lottery_rec_status)
			{
			case 0:
				$lottery_rec['status_desc'] ='未中奖';
				break;  
			case 1:
		        $lottery_rec['status_desc'] ='中奖，还未提交个人信息';
				break;
			case 2:
		        $lottery_rec['status_desc'] ='中奖，已提交,等待处理中...';
				break;
			case 3:
		        $lottery_rec['status_desc'] ='中奖，在处理中';
				break;
			case 4:
		        $lottery_rec['status_desc'] ='中奖，已兑付完奖品！';
				break;
			case 5:
		        $lottery_rec['status_desc'] ='中奖，过期被取消！';
				break;
			case 6:
		        $lottery_rec['status_desc'] ='可以随时使用！';
				break;
			case 7:
		        $lottery_rec['status_desc'] ='中奖，违约被取消！';
				break;
			case 8:
		        $lottery_rec['status_desc'] ='中奖，个案调查中';
				break;
			default:
			    $lottery_rec['status_desc'] ='状态号:'.$lottery_rec_status;
				break;
		
			} 
			return $lottery_rec['status_desc'];
			 
		 }
			 
	function getRand($proArr) {
		$result = '';
		//概率数组的总概率精度
		$proSum = array_sum($proArr);
		//概率数组循环
		foreach ($proArr as $key => $proCur) {
		$randNum = mt_rand(1, $proSum);
         if ($randNum <= $proCur) {
           $result = $key;
           break;
       } else {
         $proSum -= $proCur;
     }
    }
    unset ($proArr);
  return $result; 
  
  }
	
	
	function  get_prize_arr($mdl_lottery_details,$lottery_id){
		
		$sql ="select lottery_sub_id as id,lottery_sub_name as prize,lottery_sub_details,lottery_sub_name,Probability as v, is_award,(qty-bit_qty) as remain_qty,online_discount,min_spending,online_discount_amount,expireindays from cc_lottery_details where qty>bit_qty and lottery_id =" .$lottery_id . " order by Probability ";
		$list = $mdl_lottery_details->getListBySql($sql);
		
		$index_arr=0;
		foreach ( $list as $key => $val ) {
			
		
			$prize_arr[$index_arr]['id']= $val['id'];
			$prize_arr[$index_arr]['prize']= $val['prize'];
			$prize_arr[$index_arr]['prize_name']= $val['lottery_sub_name'];
			$prize_arr[$index_arr]['prize_details']= $val['lottery_sub_details'];
			$prize_arr[$index_arr]['v']= $val['v'];
			$prize_arr[$index_arr]['is_award']= $val['is_award'];
			$prize_arr[$index_arr]['remain_qty']= $val['remain_qty'];
			$prize_arr[$index_arr]['online_discount']= $val['online_discount'];
			$prize_arr[$index_arr]['online_discount_amount']= $val['online_discount_amount'];
			$prize_arr[$index_arr]['min_spending']= $val['min_spending'];
			$prize_arr[$index_arr]['expireindays']= $val['expireindays'];
			
			$index_arr++;			
		  		
		}
		/*	$prize_arr = array(
			'0' => array('id'=>1,'prize'=>'一等奖','is_award'=>1,'v'=>20),
			'1' => array('id'=>2,'prize'=>'二等奖','is_award'=>1,'v'=>20),
			'2' => array('id'=>3,'prize'=>'三等奖','is_award'=>1,'v'=>20),
			'3' => array('id'=>4,'prize'=>'四等奖','is_award'=>0,'v'=>40),
		); */
		return $prize_arr;
		
		
	}
	
	function check_lottery_status($lottery){
		
		
		
		if(!$this->loginUser){
			 if($this->getUserDevice()=='wechat') {
				 $return_msg = array(
				   'status'=>'wechatlogin',
				   'msg'=>'还没中！请启用微信自动登陆，否则无法关联中奖信息。',
				    'id'=>$lottery['id']
				);
				 
			 }else {
				$return_msg = array(
				   'status'=>'notwechatlogin',
				   'msg'=>'还没中奖，请登陆，否则抽奖后无法记录用户信息！',
				   'id'=>$lottery['id']
				);
			 }
			 return $return_msg;
			
			
		}
		if($lottery){
			
			
			if($lottery['is_approved']==0) {
				$return_msg = array(
				   'status'=>'cancelled by ubonus',
				   'msg'=>'抽奖被官方取消'
				);
				return $return_msg;
			}
			
			if($lottery['status']==1) {
				$return_msg = array(
				   'status'=>'running',
				   'msg'=>'抽奖正在进行'
				);
				return $return_msg;
			}
			$expire_time =$lottery['createtime'] +$lottery['expired_time']*24*60*60 ;
			if (time()>$expire_time) {
				$return_msg = array(
				   'status'=>'award is expired',
				   'msg'=>'抽奖已经过期，自动下架'
				);
				return $return_msg;
				
			}
			if($lottery['status']==2) {
				$return_msg = array(
				   'status'=>'lottery_close',
				   'msg'=>'太晚了，抽奖已经关闭'
				);
				return $return_msg;
			}
			if($lottery['status']==0) {
				$return_msg = array(
				   'status'=>'not ready',
				   'msg'=>'太早了，抽奖还未开始'
				);
				return $return_msg;
			}
			if($lottery['status']==3) {
				$return_msg = array(
				   'status'=>'done',
				   'msg'=>'来迟了，所有抽奖已经抽完！谢谢参与'
				);
				return $return_msg;
			}
			
			
			
		}
		
		if(!$this->loginUser){
			 if($this->getUserDevice()=='wechat') {
				 $return_msg = array(
				   'status'=>'wechatlogin',
				   'msg'=>'还没中奖！使用微信自动登陆后，获奖后可以直接关联您的微信账户！',
				    'id'=>$lottery['id']
				);
				 
			 }else {
				$return_msg = array(
				   'status'=>'notwechatlogin',
				   'msg'=>'还没中奖，请登陆，否则中奖无法记录用户信息！',
				   'id'=>$lottery['id']
				);
			 }
			 return $return_msg;
			
			
		}
		
				$return_msg = array(
				   'status'=>'not found award',
				   'msg'=>'未发现抽奖信息'
				);
				return $return_msg;
	}
	
	
	function get_error_msg($lottery_status){
		
		$data['status'] =$lottery_status['status'];
		$data['error']=1;
		$data['id']=$lottery_status['id'];
		$data['success']=0;
		$data['msg']=$lottery_status['msg'];
		$data['prizetype']=null;
		$data['sn']=0;
		
		return $data;
	}
	
	function getUserDeliveryInfo(){
	
	// 从用户快递信息表中获取用户快递使用的姓名，地址等数据
		// 如果该用户的数据为空，那么做如下处理
		// 从 cc_user 用户表中取出相应的信息，添加到该表中，再取出这些数据。
		
		$mdl_wj_user_delivery_info= $this->loadModel('wj_user_delivery_info');
		$where =array(
				'userId'=>$this->loginUser['id'],
				'isDefaultAddress'=>1
		);
		$wj_user_delivery_info = $mdl_wj_user_delivery_info->getbyWhere($where);
		if($wj_user_delivery_info){
			$data =$wj_user_delivery_info;
			return $data;
				
		}else{
			//当前没有快递使用的姓名，等数据，需要从cc_user表中取，或直接从 $logonuser中取出
			//但是之前要将相关的用户快递信息写入到cc_wj_user_delivery_info中以备将来使用。
			$data=array(
					'userId'=>$this->loginUser['id'],
					'first_name'=>$this->loginUser['person_first_name'],
					'last_name'=>$this->loginUser['person_last_name'],
					'phone'=>$this->loginUser['phone'],
					'email'=>$this->loginUser['email'],					
			);
			return $data;
		}
		}
	
	
	function  turntable($lottery_id,$ref_userId){
		
		
		$mdl_lottery= $this->loadModel( 'lottery' );
		
		// 获取当前的抽奖项目信息
		$lottery = $mdl_lottery->get($lottery_id);
		
		
				
				
		// 检查当前抽奖项目状态，返回值是一个数组(status,msg)，如果返回值是1，则表示当前抽奖项目有效。
		// 返回2 -已经下架 返回3 奖品全部抽完 包括 是否过期和是否被官方取消等等。
		
		$lottery_status =$this->check_lottery_status($lottery);
		
		if($lottery_status['status'] !='running'){
			//无效写入相应结果
			
			$data = $this->get_error_msg($lottery_status);
			if(!$this->loginUser){
				
				$data['error']=0;
			}
			 echo json_encode($data);
			 exit;
			
		}
		
		// if user not login ,return false;
		
		
		
		$mdl_lottery_details = $this->loadModel( 'lottery_details' );
		
		
		// 判断当前用户是否不满足抽奖条件，比如：已经达到了规定时间内的抽奖次数，或者，已经获得了奖励
		// 改查询返回一个状态，并放入返回列表的error中。如果，满足抽奖条件则返回0
		
		// 如果超过当日抽奖次数或者超过总数则返回提示
		
		$mdl_lottery_records =$this->loadModel("lottery_records");
		
		
		// 检查是否参与了澳纽集团抽奖
	
		if ($lottery_id==90){
		$choujiang_data = $this->check_if_buy_coupon_4833_azgroup($lottery);
		if($choujiang_data){
		//表示没有购买课程不能参与抽奖
    	  echo json_encode($choujiang_data);
		  exit;
		}
		
		}
		
		// 如果未超过次数，则返回0
		$over_max_times_data = $this->check_over_max_times($mdl_lottery_records,$lottery);
		// 检查当前用户是否已经抽中大奖
		
		
		
		if($over_max_times_data){
		//表示超过次数
    	  echo json_encode($over_max_times_data);
		  exit;
		}
		
        $got_ward = $this->check_if_got_award($mdl_lottery_records,$lottery,$mdl_lottery_details);
	   if($got_ward){
		//表示超过次数
    	  echo json_encode($got_ward);
		  exit;
		}
		
		
		/* 抽奖 */
		
		$draw_record = $this->get_draw_record($mdl_lottery_details,$lottery_id);
		
		$is_award =$draw_record['is_award'];
		

	  
	  
	// 如果获奖，或不获奖，的相关数据处理
	 if($is_award) {
		   $sn_order_id =date( 'Ymd' ).$this->createRnd();
		   $data = $this->set_got_award($draw_record,$sn_order_id) ;
		     $status=1; // 中奖 
		  
		 
	      
	 }else{
		   $sn_order_id =0;
		   $data =$this->set_no_award();
		   $status=0; 
		 
	 }
	  
	  
	 // 写入抽奖记录，如果获得奖励则要更改对应奖项的获奖数量。
	 
	
	 
	  /*    写入当前用户此次的抽奖记录 --不管是否抽中，还是没有抽中*/
	       $lottery_record =array(
		     'lottery_id'=>$lottery_id,
			 'userId'=>$this->loginUser['id'],
			 'is_award'=>$is_award,
			 'lottery_sub_id'=>$draw_record['id'],
			 'createtime'=>time(),
			 'redeem_code'=>$sn_order_id,
			 'status'=>$status,
			 'ref_userId'=>$ref_userId
		   );
	   // 向抽奖记录中写入记录
	   if($mdl_lottery_records->insert($lottery_record)) {
		   
		   if($is_award){
			   
			   //如果当前用户获得了奖励，那么需要将当前的获奖奖品数量+1 
			   $return_data = $this->update_lottery_details_bit_qty($mdl_lottery_details,$lottery_id,$draw_record);
			   if($return_data){
				   //正常为0 表示更改成功，如果不为0 则表示返回错误，数据库读写出现问题
				   return $return_data;
			   }else{
				   
				   // 如果当前的折扣是Online_discount ,则向 discount折扣标写入相关的折扣。
				   
				   if($draw_record['online_discount']){
					  	$mdl_promotionCode=$this->loadModel('wj_promotion_code'); 
					  	$pcode = new PromotionCode();
						$pcode->setUserId($lottery['createUserId']);
						$pcode->setCouponId(PromotionCode::APPLY_TO_ALL_COUPONID);
						$pcode->setDescription('抽奖奖品兑付折扣码');

						//$pcode->setType($type, $value);
						$pcode->setType(PromotionCode::TYPE_FIXEDAMOUNT, $draw_record['online_discount_amount']);

						$applyConditionValue=$draw_record['min_spending'];
						$applyConditionType=($applyConditionValue>0)?PromotionCode::CONDITION_MINSPEND:PromotionCode::CONDITION_NONE;
						$pcode->setCondition($applyConditionType,$applyConditionValue);

						$expireValue=$draw_record['expireindays'];
						$expireType=($expireValue>0)?PromotionCode::EXPIRETYPE_EXPIREINDAYS:PromotionCode::EXPIRETYPE_UNLIMITED;
						$pcode->setExpireType($expireType,$expireValue);

						if($pcode->setCode($sn_order_id)){
							$mdl_promotionCode->addPromotionCode($pcode);
						}
				   }
				   
				   //表示更改数量成功。
				   //判断如果所有奖项都已经抽完则写入控制信息。
				   $where =array(
				       'lottery_id'=>$lottery_id,
					   'qty >bit_qty',
                       'is_award'=>1					   
				   );
				  if($mdl_lottery_details->getCount($where)==0) {
					  $data_update_lottery_status=array(
					    'status'=>3
					  );
					  $mdl_lottery->update($data_update_lottery_status,$lottery_id);
					}
			   }

		   }
	   }else{
		    $data = $this->set_error_wrong_datatabe_access('发生意外，写入抽奖数据错误！');
			return $data;
	   }
	   
	  
	  /*   写入数据结束  */
	  
	 
		return $data;
	  
	  
		
	}
	
	
		function check_if_got_award($mdl_lottery_records,$lottery,$mdl_lottery_details){
			// 判断当前用户是否获得了大奖 
			$where =array(
			   'userId'=>$this->loginUser['id'],
			   'lottery_id'=>$lottery['id'],
			   'is_award'=>1
			);
			$award_rec =$mdl_lottery_records->getByWhere($where);
			if($award_rec) {
				
				
				$where =array(
				  'lottery_id'=>$award_rec['lottery_id'],
				  'lottery_sub_id'=>$award_rec['lottery_sub_id']
				);
				$lottery_details= $mdl_lottery_details->getByWhere($where);
				
				$data['delivery_info'] =$this->getUserDeliveryInfo();
				//设置其它信息
				$data['status'] ='true';
				$data['error']=0;
				$data['success']=1;
				$data['msg']='(之前已经中)'.$lottery_details['lottery_sub_name'].': '.$lottery_details['lottery_sub_details'];
				$data['prizetype']=$award_rec['lottery_sub_id'];  
				$data['sn']=$award_rec['redeem_code'];
				$data['is_award']= 1;
			    return $data;
				 
				 
				 
				
			}
			
			
			
			
			return 0;
		}
		
	function check_if_buy_coupon_4833_azgroup ($lottery){
			
			$where = array(
			   'userId'=>$this->loginUser['id'],
			   'bonus_id'=>4833
			);
			$mdl_wj_customer_coupon= $this->loadModel( 'wj_customer_coupon' );
			
			if($mdl_wj_customer_coupon->getCount($where)<1){
			    
				 //获取奖品商家的提供方式
		
			$businessName= $this->loadModel('user')->getBusinessNameById($lottery['createUserId']);
			if (!$businessName) {
			$lottery['businessName']='Ubonus美食生活-ubonus';
			}else{
				$lottery['businessName']=$businessName;
			}
				$data['status'] ='over_max_times';
				$data['error']=1;
				$data['success']=0;
				$data['msg']='您需要购买澳纽集团的一对一课程$9.95,才能参与抽奖. cityb2b.com/coupon/4833';
				$data['prizetype']=null;
				$data['sn']=0;
				
				 return $data;
			}
			
			
			return 0;
		}
	
	
	
	
	function check_over_max_times($mdl_lottery_records,$lottery){
			
			/* 计算当日抽奖次数是否超过总数
			本段代码执行没有成功
			$time1 =$time()-60*60*24;
			
			$userid=$this->loginUser['id'];
			$lottery_id =$lottery['id'];
		    $sql ="select count(*) as cnt from cc_lottery_records where userId =".$userid." and lottery_id =".$lottery_id. " and createtime >".$time1;
			filelog($sql,'prize_arr4.txt');
			$list =$mdl_lottery_records->getListBySql($sql);
			if ($list[0]['cnt']>=$lottery['draw_timers_perDay_perUser']) {
				$data['status'] ='over_max_times_perday';
				$data['error']=1;
				$data['success']=0;
				$data['msg']='过去24小时内最多抽奖'.$lottery['draw_timers_perDay_perUser'].'次！';
				$data['prizetype']=null;
				$data['sn']=0;
				
				 return $data;
			}
			*/
			// 计算总共的次数是否超过总数
			$where = array(
			   'userId'=>$this->loginUser['id'],
			   'lottery_id'=>$lottery['id']
			);
				if($mdl_lottery_records->getCount($where)>=$lottery['max_draw_times_perUser']){
			    
				 //获取奖品商家的提供方式
		
			$businessName= $this->loadModel('user')->getBusinessNameById($lottery['createUserId']);
			if (!$businessName) {
			$lottery['businessName']='Ubonus美食生活-ubonus';
			}else{
				$lottery['businessName']=$businessName;
			}
				$data['status'] ='over_max_times';
				$data['error']=1;
				$data['success']=0;
				$data['msg']='达上限('.$lottery['max_draw_times_perUser'].'次)，转发朋友圈，中奖几率成百倍增加，让你的朋友试试运气？';
				$data['prizetype']=null;
				$data['sn']=0;
				
				 return $data;
			}
			
			
			return 0;
		}
	
	
	//抽奖
	function get_draw_record($mdl_lottery_details,$lottery_id){
			
		  //获得奖品列表
		  $prize_arr =$this->get_prize_arr($mdl_lottery_details,$lottery_id);
		  
		  //每个奖品的中奖几率,奖品ID作为数组下标
		  
		  // 然后调用抽奖程序，获取奖励组
		    $index_prize=0;
			foreach($prize_arr as $val){
				$item[$index_prize]['id']=$val['id'];
				$item[$index_prize]['v']=$val['v'];
				$item[$index_prize]['is_award']=$val['is_award'];
				//$item[$val['id']] = $val['v'];
				$index_prize ++;
				
			}
			$res = $this->get_award($item,$lottery_id);
			// 表示抽中了大奖
			//filelog('id: '.$lottery_id.'  res: '.$res,'prize_arr00.txt');
			if($lottery_id ==25 && $res==0){
				$rand = mt_rand(1,50);
				filelog('以接近抽中大奖，再次随机取号码'.$rand,'biggest_award.txt');
				
				if($rand ==1){
					
					$user_id=$this->loginUser['id'];
					$ip=ip();
					$award_time=time();
					$email_content =$user_id.' '.$ip.' '.$award_time;
					filelog('正式确认抽中大奖'.$email_content,'biggest_award_confirm.txt');
				
			}else{
				filelog('再次抽取号码为：'.$rand,'biggest_award.txt');
				return $prize_arr[count($prize_arr)-1];
			  }
			}
			return $prize_arr[$res];
		}

	
	
	   //如果当前用户获得了奖励，那么需要将当前的获奖奖品数量+1 
	   function update_lottery_details_bit_qty($mdl_lottery_details,$lottery_id,$draw_record){
				   
				    $where_update_bitqty =array(
		      'lottery_id'=>$lottery_id,
			  'lottery_sub_id'=>$draw_record['id']
		   );
		   
		   // 获得当前记录
		   $lottery_details_sub_lottery_id =$mdl_lottery_details->getByWhere($where_update_bitqty);
		   
		   if (!$lottery_details_sub_lottery_id) {
			   $data = $this->set_error_wrong_datatabe_access('访问抽奖表获得记录信息时出错，此错误会导致无法更新获奖数据');
			   return $data;
		   }else{
			   if ($lottery_details_sub_lottery_id['bit_qty']>=$lottery_details_sub_lottery_id['qty']){
				   //如果当前的抽奖，当前的奖品的获奖数量已经达到规定值，则，视为本次抽奖无效。
				    $data = $this->set_no_award();
				    return $data;
			   }
			   // 修改获奖项 +1
			   $new_qty = $lottery_details_sub_lottery_id['bit_qty'] +1;
			   $data_update_bit_qty =array(   'bit_qty' => $new_qty  );
		       if($mdl_lottery_details->updateByWhere($data_update_bit_qty,$where_update_bitqty)){
				   return 0;
			   }
				   
			   }
	
		    }
	
	function set_got_award($draw_record,$sn_order_id) {
	       // 获取用户的个人信息中奖
	   
	        $data['delivery_info'] =$this->getUserDeliveryInfo();
			//设置其它信息
			$data['status'] ='true';
			$data['error']=0;
			$data['success']=1;
			$data['msg']=$draw_record['prize_name'].': '.$draw_record['prize_details'];
			$data['prize_name']=$draw_record['prize_name'];
			$data['prizetype']=$draw_record['id'];  
			$data['sn']=$sn_order_id;
			$data['is_award']= 1;
			
			return $data;
	
	}
	
	
	
	function set_error_wrong_datatabe_access($msg){
		
		  $data['status'] ='ture';
		   $data['error']=1;
		   $data['success']=0;
		   $data['msg']=$msg;
		   $data['prizetype']=null;
		   $data['is_award']= 0;
		   
		   
		   
		   return $data;
	}
	
		
	function set_no_award(){
		
		  $data['status'] ='ture';
		   $data['error']=0;
		   $data['success']=1;
		   $data['msg']='未获得奖励';
		   $data['prizetype']=null;
		   $data['is_award']= 0;
		  	   
		   return $data;
	}
	
	
	// 已经做到了，获奖后将提示信息在前台显示了。yes
	// 此时，需要修改用户填写的表格， 比如：电话，邮件，姓名等等 yes
	
	// 但是在写这些之前，也需要向数据库写入中奖信息。  yes
	// 当用户提交email后，发送中奖信息。 还没有 not yet 
	
	/*另一个问题是，如果用户的信息都已经全了的话，就不需要提交按钮了。 yes
	信息将自动发送到用户的email 中，或者，确认领奖，信息将发送到您的email中。
	
	// 需要正式注明，目前的抽奖都是由Ubonus官方组织的。 Ubonus的任何抽奖不需要用户支付任何费用。
	
	// 抽奖 写入数据的过程
	
	1） 任何人抽奖将会创建一条记录。
	2）如果抽奖成功，创建一条记录的同时，需要将相应的奖品数量减一 。
	3） 如果所有奖品都已经抽完。那么需要置一个状态，表示所有奖品都抽完了。
	
	4） 主表需要规定，每个用户最多抽多少次，每天最多抽几次 ，比如 最多抽5此，每天抽5次。
	这样用户在抽奖之前需要自动登陆，否则不能抽奖。 如果到达抽奖的最大数，则不能再抽奖。
	
	
	*/
	
	
	function lottery_add_activeuser_action() {
		
		$last_name =get2('last_name');
		$first_name =get2('first_name');
		$email =get2('email');
		$phone =get2('phone');
		$id=get2('id');
		$sub_lottery_id=get2('sub_id');
		$prize_name =get2('prize_name');
		
	 
		 
		 
		 // 首先更新delivery_infomaiton表
		 
		 $data_delivery =array(
		    'userId'=>$this->loginUser['id'],
			'first_name'=>$first_name,
			'last_name'=>$last_name,
			'phone'=>$phone,
			'email'=>$email,
			'createTime'=>time()
			 );
		 $mdl_user_delivery_info =$this->loadModel('wj_user_delivery_info');
		 $where =array(
			   'userId'=>$this->loginUser['id']
			 );
		 $delivery_user_info =$mdl_user_delivery_info->getByWhere($where);
		 
		 if ($delivery_user_info){
			 // filelog('find the delivery_user_info','running_log.txt');
			 if($mdl_user_delivery_info->updateByWhere($data_delivery,$where)){
				  $succ =1;
			 }else{
				 $succ =0;
			 }
		 }else{
			 // filelog('did not find the delivery_user_info','running_log.txt');
			 if($mdl_user_delivery_info->insert($data_delivery)){
				 $succ=1;
			 }else{
			   $succ =0;
		   }
		 }
		 
		 
		 // 如果user表里面没有数据，则更新user里面的表 （很可能user表中的email重复，那就不管它写入是否成功。
		 $mdl_user=$this->loadModel('user');
		 $user_info=$mdl_user->get($this->loginUser['id']);
		 if (!$user_info['person_first_name']) {
			 $data_user_update['person_first_name'] =$first_name;
		 }
		  if (!$user_info['person_last_name']) {
			 $data_user_update['person_last_name'] =$last_name;
		 }
		  if (!$user_info['phone']) {
			 $data_user_update['phone'] =$phone;
		 }
		  if (!$user_info['email']) {
			 $data_user_update['email'] =$email;
		 }
		 // 可能会出现错误，不过没有关系
         if($data_user_update){
			// filelog('find the delivery_user_info','running_log.txt');
			 $mdl_user->update($data_user_update,$this->loginUser['id']);
			 
		 }

		$mdl_lottery_records=$this->loadModel('lottery_records');		
		 // 判断当前的抽奖是不是已经过期，如果已经过期则，返回提示！
		 
		
		 $where_expired =array(
		    'userId'=>$this->loginUser['id'],
			'lottery_id'=>$id,
			'is_award'=>1,
			'status'=>5
	   	 );
		 if ($mdl_lottery_records->getByWhere($where_expired)){
			 $data['error']=0;
			 $data['success']=1;
			 $data['status'] ='过期被取消';
		  	 $data['msg']='当前的兑奖申请因为超时已经被取消！谢谢参与';
			 echo json_encode($data);
			 return;
		 }
		 		
				
				
				
		 // 然后在标Lottery_record 里面加一个状态，客户已经申领奖项的状态。
		
		 // 为防止外部攻击，加入更多条件获取要修改的记录。
		 $where =array(
		    'userId'=>$this->loginUser['id'],
			'lottery_id'=>$id,
			'status'=>1,
			'is_award'=>1
    	 );
		 
		 
		 $where1=array(
		  'lottery_id'=>$id,
		  'lottery_sub_id'=>$sub_lottery_id
		 );
		 $rec_of_lottery =$this->loadModel('lottery_details')->getByWhere($where1);
		 
		 if($rec_of_lottery['online_discount']==1){
			  $data_update_record ['status']=6;// 如果是线上可以使用折扣码 。
		 }else{
			  $data_update_record ['status']=2;// 注名改兑奖用户已经开始申领 。
		 }
		
		 
		 //获取当前获奖记录的信息
		 $lottery_award_rec =$mdl_lottery_records->getByWhere($where);
		 $lottery_award_rec['prize_name']=$prize_name;
		 $lottery_award_rec['last_name']=$last_name;
		 $lottery_award_rec['first_name']=$first_name;
		 $lottery_award_rec['phone']=$phone;
		 $lottery_award_rec['email']=$email;
		 
		 
		 
		 if($mdl_lottery_records->updateByWhere($data_update_record,$where)){
			 $succ1=1;
		 }else{
			 $succ1=0;
			 
		 }
		   if($succ && $succ1){
			 $data['error']=0;
			 $data['success']=1;
			 $data['status'] =$this->get_lottery_cn_status_desc(2);
			 		 
		  	 $data['msg']='提交成功！信息会发送到邮箱，请联系客服，处理相关兑奖事宜！';
		   }else{
			 $data['error']=1;
			 $data['success']=0;
			 $data['status'] ='ture';
		  	 $data['msg']='您的信息提交中断，不过，您的中奖信息已经存储在系统中，请稍后再试，或者直接联系Ubonus客服';
		     filelog('error:lottery_add_activeuser，中奖后填写个人信息提交时出现错误','running_log.txt');
			   
		   }
		   
		   
		   
		   
		   // 发送email 
		   
		   
			
		  	$subject='获奖通知-您在Ubonus美食生活(Ubonus）抽取到大奖';
			$email_content = $this->getLotteryEmailContent($lottery_award_rec);

			$system_mailer = $this->loadModel('system_mail');

			$system_mailer->title($subject);
            $system_mailer->body($email_content);
            $system_mailer->to($email);

            $status=$system_mailer->send();

		echo json_encode($data);
		
		
		// 判断用户传递的奖励是否正确
		// 写入相关的用户信息，包括用户表和delivery表
		// 获奖record记录中添加一个字段，注明用户已经申领奖品
		// 用户个人中心添加一个中奖菜单，指示中奖记录及状态。
		// 商家可以兑付中奖完成，用户状态随之改变。
		// 中奖后发送email到用户邮箱。
		// 14天后自动置中奖信息已经失效。
		// 需要设置奖品是否为自动赠予，如Ubonus金币。如果是自动赠与 则 直接发送积分及置标志即可。
		// Ubonus积分抽奖的每个用户的最高额度是 88
		
	}
	
	function main_pic_edit_action(){
		$id=(int)get2('id');

		$mdl_lottery =$this->loadModel("lottery");
		$lottery =$mdl_lottery->get($id);
		$this->setData($lottery,'lottery');
		
		if ( is_post() ) {
			$data['pic']=trim(reset(post('images')));

			$mdl_lottery->update($data,$id);

			$this->form_response(200,'编辑成功',HTTP_ROOT_WWW."lottery/lotterys");
		}
		else {
			$this->setData( '抽奖图片编辑', 'pagename' );
			$this->setData( 'community', 'menu' );
			$this->setData( 'lottery', 'submenu' );
			$this->setData( '抽奖图片编辑 - 抽奖管理 - '.$this->site['pageTitle'], 'pageTitle' );
			$this->display( 'lottery/main_pic_edit' );
		}
	}

	function upload_zhuanpan_action(){

		
		$id=(int)get2('id');
		$mdl_lottery =$this->loadModel("lottery");

		$lottery =$mdl_lottery->get($id);
		
		$this->setData($lottery,'lottery');
	
		
		if ( is_post() ) {
			$data['pic1']=trim(reset(post('images')));

			$mdl_lottery->update($data,$id);

			$this->form_response(200,'编辑成功',HTTP_ROOT_WWW."lottery/lotterys");
		}
		else {
			

			$this->setData( '抽奖图片编辑', 'pagename' );
			$this->setData( 'community', 'menu' );
			$this->setData( 'lottery', 'submenu' );
			$this->setData( '抽奖图片编辑 - 抽奖管理 - '.$this->site['pageTitle'], 'pageTitle' );
			$this->display( 'lottery/upload_zhuanpan' );

		}


	}
	
	
	public function test_action()
	{	
		$mdl_lottery_records=$this->loadModel('lottery_records');
		$lottery_award_rec =$mdl_lottery_records->get(1473);
		 $lottery_award_rec['prize_name']='一等奖';
		 $lottery_award_rec['last_name']='chris';
		 $lottery_award_rec['first_name']='wang';
		 $lottery_award_rec['phone']=123456;
		 $lottery_award_rec['email']='wang1230ji@gmail.com';

		 echo $this->getLotteryEmailContent($lottery_award_rec);
	}
	
	
	  function getLotteryEmailContent($lottery_award_rec){
		  
		$mdl_lottery =$this->loadModel('lottery');
		
		
		
		 
		
			 $mdl_lottery_details=$this->loadModel('lottery_details');
			 //获得奖励名称
			 $where =array(
		    'lottery_id' =>$lottery_award_rec['lottery_id'],
			'lottery_sub_id'=>$lottery_award_rec['lottery_sub_id'],
	   	     );
		    $lottery_rec_details=$mdl_lottery_details->getByWhere($where);
			 
			$lottery_rec['lottery_name'] = $lottery_rec_details['lottery_sub_name'].": ".$lottery_rec_details['lottery_sub_details'];
			 
			 //表示中奖
			$lottery_rec['status_desc'] =$this->get_lottery_cn_status_desc(2);
			
			$this->setData($lottery_rec,'lottery_rec');
		
		
		$lottery =$mdl_lottery->get($lottery_award_rec['lottery_id']);
		
		
  		$mdl_user = $this->loadModel( 'user' );

  		$this->setData(date('Y-m-d H:i:s', $lottery_award_rec['createtime']), 'createTime');
		
	  	$this->setData($lottery_award_rec['first_name'].' '.$lottery_award_rec['last_name'], 'customerName');
	  	$this->setData('恭喜！ 您在Ubonus美食生活上抽奖成功', 'message');
		$this->setData($lottery_award_rec['redeem_code'], 'redeemCode');
		$this->setData($lottery_award_rec['id'],'order_id');
	  	$this->setData($lottery_award_rec['prize_name'], 'prize_name');
		
		$this->setData($lottery['title'], 'title');
	  
	  	$redeem_details=$lottery['redeem_details'];
	  	$redeem_details=addBaseHref($redeem_details);
		$this->setData($redeem_details,'redeem_details');
		
		$redeem=$lottery['redeem'];
		$redeem=addBaseHref($redeem);
		$this->setData($redeem,'redeem');
		
		$description=$lottery['description'];
		$description=addBaseHref($description);
		$this->setData($description,'description');

	   	    
	 	$Business_user =$mdl_user->get( $lottery['createUserId'] );
	  	$this->setData($Business_user['businessName'], 'businessName');
	  	$this->setData($Business_user['phone'],'business_phone');
	  	$this->setData($Business_user['tel'],'business_tel');
	  	$this->setData($Business_user['googleMap'],'business_address');
	    	    
	   	return  $this->fetch( 'email/lottery_apply_confirmation_email' );
  }

	
	
  function getLotteryEmailContent_business($order){
  		$mdl_user = $this->loadModel( 'user' );

	  	$this->setData(date('Y-m-d H:i:s', $order['createTime']), 'createTime');
	  	$this->setData('您公司在ubonus发布的产品已经被领取或购买，订单的明细如下：', 'message');
	  	$this->setData($order['redeem_code'], 'redeemCode');
	  	$this->setData($order['orderId'],'order_id');
	  	$this->setData($order['order_name'], 'productName');
	  	$this->setData($this->getEmailComponent_orderDetail($order['orderId']),'itemTable');
	  	$this->setData($order['money'], 'productPrice');
	  	$this->setData($order['delivery_fees'], 'deliveryFees');
	  	$this->setData($order['booking_fees'], 'bookingFees');
	  	$this->setData($order['promotion_total'], 'promotionTotal');
	  	$this->setData($order['surcharge'], 'surcharge');

	    $this->setData($order['payment'],'payment');
	    $this->setData($order['status'],'status');

	    $delivery_option=($order['customer_delivery_option']);
 		$this->setData($delivery_option,'delivery_option');
	  	if($delivery_option==2){
	    	$this->setData('自取-pick up','delivery_option_desc');
	    	$business_staff = $mdl_user->get($order['business_staff_id']);
		  	if($business_staff){
		  		$this->setData($business_staff['nickname'],'pickupnickname');
		  		$this->setData($business_staff['googleMap'],'pickupaddress');
		  	}

	    }elseif($delivery_option==1){
	    	$this->setData('商家送货','delivery_option_desc');
	    }else{
	    	$this->setData('当面兑付','delivery_option_desc');
	    }
	  	
	  	$this->setData($order['first_name'].' '.$order['last_name'], 'customerName');
	    $this->setData($order['address'],'address');
	    $this->setData($order['phone'],'phone');
	    $this->setData($order['email'],'email');
	    $this->setData($order['postalcode'],'postalcode');
	    $this->setData($order['message_to_business'],'customer_message');
	  	
	  	return  $this->fetch( 'email/apply_confirmation_email_business' );
  }
	
	
	
	function lottery_get_result_action() {
		
		
		//获取抽奖方式，如幸运大转盘 1，刮刮卡等
		
		$type=(int)get2('type');
		$lottery_id=(int)get2('id');
		$ref_userId=(int)get2('ref_userId');
		
		//filelog($ref_userId,'ref.txt');
		
		
		switch ($type)
		{
		case 1:
			//幸运大转盘
			$data =$this->turntable($lottery_id,$ref_userId);
			break;  
		case 2:
			
			break;
				
		default:
			$data['status'] ='error_type';
			$data['error']=1;
			$data['success']=0;
			$data['msg']='抽奖类型错误';
			$data['prizetype']=null;
			$data['sn']=0;
			break;
			
		}
	
	
	
	 echo json_encode($data);
	 
	 
	 
	 // 1） 如果超过14天没有兑付则会自动删除。
	 // 2） 
	 // 
	 // 7 ） 如果奖品是自动发放的话，那么直接发放即可。如Ubonus金币。
	 //
      	 
		
	}

private function get_award($item,$lottery_id){
			//中奖概率基数 
			//$num = array_sum($item['v']);//当前一等奖概率1/2000
			$num = 0;
            $item1 =$item;
			
			foreach($item1 as $k => $v) {
				$num += $v[ 'v' ];
				$item2[$k]['v'] = $num;
				//filelog('pro: '.$item2[$k]['v'],'prize_arr3.txt');
			}
			
			
			
	 		
			
			//获取一个1到当前基数范围的随机数
			$rand = mt_rand(1,$num);
			$draw_index=0;
    		foreach($item1 as $key=>$val){
			
			if ($rand <=$val['v'] ) {
				
				$res = $draw_index;
				break;
			}
						
			$draw_index ++;
			
          }
		 // 如果抽到的是已经获奖的数组，不做任何处理, $rand 在10以内基本上是获奖的，所以不做加速
		 // 25为现金抽奖不做加速
		 $is_award= $item[$res]['is_award'];
		 
		 $mdl_lottery_records=$this->loadModel('lottery_records');
		 $user_lottery_speed_data=$this->get_user_lottery_speed_data($mdl_lottery_records);
		 if( $user_lottery_speed_data){
			 $speed =$user_lottery_speed_data['total_speed'];
		 }else{
			 $speed =0;
		 }
		 
		 
		 //filelog('没有中奖,rand is :'.$rand.' lottery_id:'.$lottery_id.' is_award:'. $is_award.' speed:'.$speed,'prize_arr1.txt');
		 if( $is_award!=1 && $rand>10 && $lottery_id!=90) {
			//表示没有中奖，则开始检查用户加速倍数信息，如果加速倍数为1，则不做任何处理
			if($speed>0){
				//有加速倍速，重新开始做一次中奖匹配
				//filelog('没有中奖进入到加倍抽奖程序,rand is :'.$rand,'prize_arr1.txt');
				$draw_index=0;
				if ($speed>=1000) {
					$speed=1000;
				}
				if($speed>=$rand){
					$rand= mt_rand(2,50);
				}else{
					$rand=$rand-$speed+2;
				}
				
				//filelog('没有中奖,加速后rand is :'.$rand,'prize_arr1.txt');
				foreach($item2 as $key=>$val){
				
					if ($rand <=$val['v'] ) {
						$res = $draw_index;
						break;
	    		}
						
			    $draw_index ++;
			
             }
			}
			 
		 }
			 
		
		 
		 
		 
	//filelog('id: '.$val['id'].'  v : '.$val['v'],'prize_arr1.txt');
	//filelog('rand:'.$rand. ' res:'.$res.' num: '.$num,'prize_arr1.txt');
    return $res;
}
	

	

}