<?php

class ctl_show extends cmsPage
{	
	function ctl_member() {
		parent::cmsPage();

		$this->setData( 'member', 'footer_menu' );

		$act = $GLOBALS['gbl_act'];
		$ignore_list = array('seats_data_ajax');
		if ( !in_array($act, $ignore_list) && !$this->loginUser ) {
			$this->sheader( HTTP_ROOT_WWW.'member/login?returnUrl='.urlencode( $_SERVER['REQUEST_URI'] ) );
		}
		
		
	}

	function seats_data_ajax_action(){
		$orientation=$this->loadModel('wj_show')->getHorizontalOrientation($_GET['cid']);

		if($orientation==mdl_wj_show::HORIZONTAL_ORIENTATION_RIGHT){
			echo json_encode($this->seats_data($_GET['cid']));
		}else{
			echo json_encode($this->seats_data_desc($_GET['cid']));
		}
	}

	function seats_data($coupon_id){
		$ssinfo=$this->loadModel('wj_show')->getShowAndStadium($coupon_id);

		$mdl_seats=$this->loadModel('wj_show_seats');
		$mdl_seats->initShowAndStadium($ssinfo['show_id'],$ssinfo['stadium_id']);

		//this will be convert into item in charsmap js at front
		$category = $this->loadModel('wj_show_tickets_category_price')->getTicketCategory($ssinfo['show_id'],$ssinfo['stadium_id']);

		$areas=$mdl_seats->areaList();
		
		$frontData=[];

		foreach ($areas as $k => $area) {
			if(!$mdl_seats->areaHasSeatAvailable($area))continue;
			
			$seats=$mdl_seats->seatsDataArea($area);
			$rows = $mdl_seats->rowList($area);

			$frontDataArea['row']=[];
			$frontDataArea['column'];
			$frontDataArea['map'];
			$frontDataArea['unavailable'];
				// echo "<pre>";
				// echo 'zone:'.$area;
				// echo ' size'.sizeof($seats);
				// echo ' num of row '.sizeof($rows);

				// echo '<br>';
			$largestRow=null;
			$largestSeatNumber=-1;
			$smallestSeatNumber=1000;

			foreach ($rows as $row) {
				$seatsnumber=$mdl_seats->seatNumberList($area,$row);
				if($seatsnumber[0]<$smallestSeatNumber){
					$smallestSeatNumber=$seatsnumber[0];
				}
				if(end($seatsnumber)>$largestSeatNumber){
					$largestSeatNumber=end($seatsnumber);
				}
			}

				//echo '<br> from '.$smallestSeatNumber.' to '.$largestSeatNumber.'<br>';
			$banchMarkRow=[];
			for($i =$smallestSeatNumber; $i<=$largestSeatNumber;$i++){
				array_push($banchMarkRow, (int)$i);
			}

				//echo 'banchMarkRow '.join('|',$banchMarkRow).'<br/>';
			$structure=[];
			$offsetData = [];

			foreach ($rows as $row) {
				$sd= $mdl_seats->seatsDataRow($area,$row);
				$tci=$sd[0]['ticket_category_id'];

					$symbol='a';//default
					foreach ($category as $key => $value) {
						if($value['id']==$tci){
							$symbol=$value['symbol'];
							break;
						}
					}
					

					$seatsnumber=$mdl_seats->seatNumberList($area,$row);
					// echo $row.' row   ';
					// echo 'size:'.sizeof($seatsnumber).'  ';
					// echo join('|', $seatsnumber);
					// echo '<br>';

					$diff_left_org = abs($banchMarkRow[0]-$seatsnumber[0]);//orginal alignment
					$diff_right_org=abs(end($banchMarkRow)-end($seatsnumber));//orginal alignment

					$diff=abs(abs(end($banchMarkRow)-$banchMarkRow[0])-abs($seatsnumber[0]-end($seatsnumber)));

					$diff_left=ceil($diff/2.0);//center alignment
					$diff_right=floor($diff/2.0);//center alignment

					$offsetIndex= $diff_right-$diff_right_org;

					$d = join('|', $seatsnumber);
					if(abs(end($seatsnumber)-$seatsnumber[0])>=sizeof($seatsnumber))$d = $this->exactPosition($seatsnumber);

					$d = preg_replace('/\d+/', $symbol, $d );
					$d = str_replace('|', '', $d);
					$d = sprintf('%s%s%s',str_repeat("_",$diff_left ),$d,str_repeat("_",$diff_right ));

					array_push($structure, $d);
					$offsetData[$row]=$offsetIndex;
				}

				//unavailable seats;
				$unavailableSeats=[];
				$unavailableSeatsData=$mdl_seats->unavailableSeatsDataArea($area);
				
				foreach ($unavailableSeatsData as $key => $ud) {
					array_push($unavailableSeats, $ud['row'].'_'.$ud['seat_number']);
				}


				$frontDataArea['row']= $rows;
				$frontDataArea['rowOffset']=$offsetData;
				$frontDataArea['column']= $banchMarkRow;
				$frontDataArea['unavailable']=$unavailableSeats;

				
				//var_dump($structure);
				$frontDataArea['map']=$structure;

				$frontData[$area]=$frontDataArea;
			}

			return $frontData;

		}


		function exactPosition($seatsnumber){
			$startIndex = $seatsnumber[0];
			$endIndex = end($seatsnumber);
			$d =[];
			for ($i=$startIndex; $i<=$endIndex;$i++) { 
				if(in_array($i, $seatsnumber)){
					array_push($d, $i);
				}else{
					array_push($d, '_');
				}
			}
			return join('|',$d);
		}



	//DESC EACH Column. seatnumber increase from left to right 1 2 3 4 5 6 7 8 9 ...

		function seats_data_desc($coupon_id){
			$ssinfo=$this->loadModel('wj_show')->getShowAndStadium($coupon_id);

			$mdl_seats=$this->loadModel('wj_show_seats');
			$mdl_seats->initShowAndStadium($ssinfo['show_id'],$ssinfo['stadium_id']);

		//this will be convert into item in charsmap js at front
			$category = $this->loadModel('wj_show_tickets_category_price')->getTicketCategory($ssinfo['show_id'],$ssinfo['stadium_id']);

			$areas=$mdl_seats->areaList();

			$frontData=[];

			foreach ($areas as $k => $area) {
				if(!$mdl_seats->areaHasSeatAvailable($area))continue;

				
				$rows = $mdl_seats->rowList($area);

				$frontDataArea['row']=[];
				$frontDataArea['column'];
				$frontDataArea['map'];
				$frontDataArea['unavailable'];
				// echo "<pre>";
				// echo 'zone:'.$area;
				// echo ' size'.sizeof($seats);
				// echo ' num of row '.sizeof($rows);

				// echo '<br>';
				$largestRow=null;
				$largestSeatNumber=-1;
				$smallestSeatNumber=1000;

				foreach ($rows as $row) {
					$seatsnumber=$mdl_seats->seatNumberListDESC($area,$row);
					if(end($seatsnumber)<$smallestSeatNumber){
						$smallestSeatNumber=end($seatsnumber);
					}
					if($seatsnumber[0]>$largestSeatNumber){
						$largestSeatNumber=$seatsnumber[0];
					}
				}

				//echo '<br> from '.$smallestSeatNumber.' to '.$largestSeatNumber.'<br>';
				$banchMarkRow=[];
				for($i =$largestSeatNumber; $i>=$smallestSeatNumber;$i--){
					array_push($banchMarkRow, (int)$i);
				}

				//echo 'banchMarkRow '.join('|',$banchMarkRow).'<br/>';
				$structure=[];
				$offsetData = [];

				foreach ($rows as $row) {
					$sd= $mdl_seats->seatsDataRow($area,$row);
					$tci=$sd[0]['ticket_category_id'];

					$symbol='a';//default
					foreach ($category as $key => $value) {
						if($value['id']==$tci){
							$symbol=$value['symbol'];
							break;
						}
					}
					

					$seatsnumber=$mdl_seats->seatNumberListDESC($area,$row);
					// echo $row.' row   ';
					// echo 'size:'.sizeof($seatsnumber).'  ';
					// echo join('|', $seatsnumber);
					// echo '<br>';

					$diff_left_org = abs($banchMarkRow[0]-$seatsnumber[0]);//orginal alignment
					$diff_right_org=abs(end($banchMarkRow)-end($seatsnumber));//orginal alignment

					$diff=abs(abs(end($banchMarkRow)-$banchMarkRow[0])-abs($seatsnumber[0]-end($seatsnumber)));

					$diff_left=ceil($diff/2.0);//center alignment
					$diff_right=floor($diff/2.0);//center alignment

					$offsetIndex= $diff_right_org-$diff_right;

					$d = join('|', $seatsnumber);
					if(abs(end($seatsnumber)-$seatsnumber[0])>=sizeof($seatsnumber))$d = $this->exactPositionDESC($seatsnumber);

					$d = preg_replace('/\d+/', $symbol, $d );
					$d = str_replace('|', '', $d);
					$d = sprintf('%s%s%s',str_repeat("_",$diff_left ),$d,str_repeat("_",$diff_right ));

					array_push($structure, $d);
					$offsetData[$row]=$offsetIndex;
					
				}

				//unavailable seats;
				$unavailableSeats=[];
				$unavailableSeatsData=$mdl_seats->unavailableSeatsDataArea($area);
				
				foreach ($unavailableSeatsData as $key => $ud) {
					array_push($unavailableSeats, $ud['row'].'_'.$ud['seat_number']);
				}


				$frontDataArea['row']= $rows;
				$frontDataArea['rowOffset']=$offsetData;
				$frontDataArea['column']= $banchMarkRow;
				$frontDataArea['unavailable']=$unavailableSeats;

				
				//var_dump($structure);
				$frontDataArea['map']=$structure;

				$frontData[$area]=$frontDataArea;
			}

			return $frontData;

		}

		function exactPositionDESC($seatsnumber){
			$startIndex = $seatsnumber[0];
			$endIndex = end($seatsnumber);
			$d =[];
			for ($i=$startIndex; $i>=$endIndex;$i--) { 
				if(in_array($i, $seatsnumber)){
					array_push($d, $i);
				}else{
					array_push($d, '_');
				}
			}
			return join('|',$d);
		}







		//?????????????????????????????????
	function show_area_row_seats_add_action(){
		
		$mdl_wj_show_stadium =$this->loadModel('wj_show_stadium');

		if ( is_post() ) {
			
			$mdl_wj_show_seats =$this->loadModel('wj_stadium_seats');
			$stadium_id =trim(post('stadium_id'));
			$area=trim(post('area'));
			$row=strtoupper(trim(post('row')));
			$seats_start =(int)post('seats_start');
			$seats_end =(int)post('seats_end');
			$seats_special=trim(post('seats_special'));
			
			
			$this->setData($stadium_id,'stadium_id');
			$this->setData($area,'area');
			$this->setData($row,'row');
			$this->setData($seats_start,'seats_start');
			$this->setData($seats_end,'seats_end');
			
			for($i=$seats_start;$i<$seats_end+1;$i++){

				
				$data = array(
					'stadium_id'=>$stadium_id,
					'area'=>$area,
					'row'=>$row,
					'seat_number'=>$i,
					'special'=>$seats_special
					);



				if ( $mdl_wj_show_seats->insert( $data ) ) {

					$success=1;

				}else{

					$success=0;
				}

			}	
			if ( $success ) {

				
			}
			else {
				
				$result['status'] = 500;
				$result['msg'] = '????????????';
				echo json_encode( $result );exit;
			}
		}
		

		$sql ="select a.* from #@_wj_show_stadium a,#@_wj_show b where b.createUserId=".$this->loginUser['id']." and b.stadium_id=a.id";
		$stadium=$mdl_wj_show_stadium->getListBySql($sql);

		$this->setData($stadium,'stadium');
		
		$this->setData( '??????????????????', 'pagename' );
		$this->setData( 'show', 'menu' );
		$this->setData( 'add_seats', 'submenu' );
		$this->setData( '?????????????????? - ???????????? - '.$this->site['pageTitle'], 'pageTitle' );
		$this->display( 'show/show_area_row_seats_add' );

		


	}


	//?????????????????????????????????
	function show_link_stadium_action(){
		
		$mdl_wj_show=$this->loadModel('wj_show');

		if ( is_post() ) {
			
			$mdl_wj_show_seats =$this->loadModel('wj_show_seats');
			$mdl_wj_stadium_seats =$this->loadModel('wj_stadium_seats');
			
			// ??????????????????????????????
			$show_id =post('show_id');
			$stadium_id=post('stadium_id');
			
			
			//$result['status'] = 500;
			//$result['msg'] = $show_id.' '.$stadium_id;
			//echo json_encode( $result );exit;
			
			$data =array(
				'stadium_id'=>$stadium_id

				);
			
			if($mdl_wj_show->update($data,$show_id)) {
			}else{
				$result['status'] = 500;
				$result['msg'] = '????????????';
				echo json_encode( $result );exit;
				
			}
			
			// ???????????????????????????????????????????????????
			
			//1) ????????????????????????????????????
			$sql_stadium_seats = "select * from #@_wj_stadium_seats where stadium_id =".$stadium_id;
			$stadium_seats_list = $mdl_wj_stadium_seats->getListBySql($sql_stadium_seats);
			
			
			//2) ?????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????
			if($stadium_seats_list){
				$success =0;
				$unsuccess=0;
				foreach ( $stadium_seats_list as $key => $val ) {
					$data_insert_show_seats=array(
						'show_id'=>$show_id,
						'seat_id'=>$val['id'],
						'stadium_id'=>$stadium_id,
						'area'=>$val['area'],
						'row'=>$val['row'],
						'seat_number'=>$val['seat_number'],
						'special'=>$val['special']
						);

					if ( $mdl_wj_show_seats->insert($data_insert_show_seats)) {

						$success ++;

					}else{

						$unsuccess++;
					}
					
				}
			} 
			
			if ( $success>0 ) {
				$result['status'] = 200;
				$result['msg'] = '?????????????????????'.$success." ?????????????????????".$unsuccess;
				echo json_encode( $result );exit;

			}else{
				$result['status'] = 200;
				$result['msg'] = '????????????';
				echo json_encode( $result );exit;
				
			}
			
			

		}else{
			
			// ?????????????????????

			// ????????????????????????
			$mdl_wj_show=$this->loadModel('wj_show');
			$mdl_wj_show_stadium=$this->loadModel('wj_show_stadium');
			
			$sql_show="select * from #@_wj_show where createUserid=".$this->loginUser['id'];
			
			
			$sql_show_stadium ="select a.* from #@_wj_show_stadium a,#@_wj_show b where b.createUserId=".$this->loginUser['id']." and b.stadium_id=a.id";
			
			
			
			$show=$mdl_wj_show->getListBySql($sql_show);
			$show_stadium=$mdl_wj_show->getListBySql($sql_show_stadium);

			$this->setData($show,'show');
			$this->setData($show_stadium,'show_stadium');	
			
			
		}


		$this->setData( '????????????????????????', 'pagename' );
		$this->setData( 'show', 'menu' );
		$this->setData( 'link_stadium', 'submenu' );
		$this->setData( '???????????????????????? - ???????????? - '.$this->site['pageTitle'], 'pageTitle' );
		$this->display( 'show/show_link_stadium' );




	}

	//?????????,??????????????????
	function show_set_seats_price_action(){

		$mdl_wj_show_seats =$this->loadModel('wj_show_seats');
		$mdl_wj_show=$this->loadModel('wj_show');
		$mdl_wj_show_tickets_category_price = $this->loadModel('wj_show_tickets_category_price');
		
		$show_id =get2('show_id');
		$stadium_id=get2('stadium_id');
		$area =get2('area');
		$row_1 =get2('row');
		

		if ( is_post() ) {
			
		  // ???????????????ID?????????????????????????????????ID
			$show_id =post('show_id');
			$where =array(
				'id'=>$show_id
				);
			$current_show =$mdl_wj_show->get($show_id);
			if($current_show){
				$stadium_id =$current_show['stadium_id'];

			}else{
				$result['status'] = 200;
				$result['msg'] = '????????????-??????????????????????????????';
				echo json_encode( $result );exit;

			}
		  // ???????????????????????? show_ticket_category ???????????? ticket_category
			$price =post('price'); 
			$where =array(
				'show_id'=>$show_id,
				'price'=>$price		
				);
			$current_ticket_category =$mdl_wj_show_tickets_category_price->getByWhere($where);
			if($current_ticket_category){
				$ticket_category =$current_ticket_category['ticket_category'];
				$ticket_category_id =$current_ticket_category['id'];
			}else{
				$result['status'] = 200;
				$result['msg'] = '????????????-?????????????????????????????????????????????????????????';
				echo json_encode( $result );exit;

			}
			
		  // ???????????????
			$area =post('area');
			$row= post('row');

			$arr_row =explode(",",$row);
			$first =1;		   	
			foreach ( $arr_row as $key => $val ) {
				if($first){
					$row_for_sql ="'".$val."'";
					$first =0;
				}else {
					$row_for_sql = $row_for_sql.",'".$val."'";
				}


			}

			
         // ???????????????????????????????????????????????????
         // 1) ?????? sql 
         // 2) ???????????????ticket_category ?????????
			$mdl_wj_show_seats =$this->loadModel('wj_show_seats');


			if($row){
				$where_update =array(
					'show_id'=>$show_id,
					'stadium_id'=>$stadium_id,
					'area'=>$area,
					'row in ('. $row_for_sql.')'	
					);
			}else{
				$where_update =array(
					'show_id'=>$show_id,
					'stadium_id'=>$stadium_id,
					'area'=>$area
					);

			}
			$data_update =array(
				'price'=>$price,
				'ticket_category'=>$ticket_category,
				'ticket_category_id'=>$ticket_category_id,
				'gen_time'=>time()	
				);
			if($mdl_wj_show_seats->updateByWhere($data_update,$where_update)){

				$this->setData("???????????????",'success');

			}else{

				$result['status'] = 200;
				$result['msg'] = '??????????????????-????????????';
				echo json_encode( $result );exit;
			}	
			
			$this->setData($show_id,'show_id');
			$this->setData($area,'area');
			$this->setData($row,'row');
			$this->setData($price,'price');






		}else{

			// ?????????????????????

			// ????????????????????????
			$mdl_wj_show=$this->loadModel('wj_show');
			

			$sql_show="select * from #@_wj_show where createUserId=".$this->loginUser['id']." order by id desc";
			
			//echo $sql_show; exit;
			$show=$mdl_wj_show->getListBySql($sql_show);


			// ?????? $row_1 ???????????????????????????????????????
			if($row_1){
				$mdl_wj_stadium_seats =$this->loadModel('wj_stadium_seats');
				$where_show=array(
					'show_id'=>$show_id,
					'area'=>$area,
					'row'=>$row_1
					);
				$where_stadium=array(
					'stadium_id'=>$show_id,
					'area'=>$area,
					'row'=>$row_1
					);
				
				$mdl_wj_show_seats->begin();
				$mdl_wj_stadium_seats->begin();
				$mdl_wj_stadium_seats->deleteByWhere($where_stadium);
				$mdl_wj_show_seats->deleteByWhere($where_show);
				
				
				if($mdl_wj_show_seats->errno() || $mdl_wj_stadium_seats ->errno()) {
					$mdl_wj_show_seats->rollback();
					$mdl_wj_stadium_seats->rollback();
					$result['status'] = 200;
					$result['msg'] = 'seat_row_delete fail';
					echo json_encode( $result );exit;
				}else{
					
					$mdl_wj_show_seats->commit();
					$mdl_wj_stadium_seats->commit();
					
				}
			}
			
			
			$this->setData($show,'show');
			$this->setData($show_id,'show_id');
			$this->setData($area,'area');
			
			


		}

	//   if(!$show_id){$show_id=2;}
		if ($show_id){
			
		// 	???????????????????????????

			$sql ="select area from #@_wj_show_seats where show_id=".$show_id. " group by area order by length(area),area";	

			$area_list=$mdl_wj_show_seats->getListBySql($sql);

			if($area_list){
				$this->setData($area_list,'area_list');	

			}	



		// ???????????????????????????
			$sql ="select * from #@_wj_show_tickets_category_price where show_id=".$show_id." order by price";
			$price_list=$mdl_wj_show_tickets_category_price->getListBySql($sql);
			if($price_list){
				$this->setData($price_list,'price_list');
				
			}

		// ??????????????????????????????
			if ($area){
				$sql ="select gen_time,show_id ,stadium_id,area,row as row_1 ,ticket_category,price from #@_wj_show_seats where show_id=".$show_id." and area='".$area."' group by gen_time,show_id ,stadium_id,area,row_1,ticket_category,price  order by gen_time desc";
			}else {
				$sql ="select gen_time,show_id ,stadium_id,area,row as row_1 ,ticket_category,price from #@_wj_show_seats where show_id=".$show_id." group by gen_time,show_id ,stadium_id,area,row_1,ticket_category,price  order by gen_time desc";
			}		

		// $result['status'] = 200;
		// $result['msg'] = $sql;
		// echo json_encode( $result );exit;

			$pageSql	= $sql;
			$pageUrl	= $this->parseUrl()->set('page');
			$pageSize	= 30;
			$maxPage	= 20;
			$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
			$data		= $mdl_wj_show_seats->getListBySql($page['outSql']);
			
			
			
			$this->setData( $data, 'data' );
			$this->setData( $page['pageStr'], 'pager' );
			
		}
		
		$this->setData( '???????????????????????????', 'pagename' );
		$this->setData( 'show', 'menu' );
		$this->setData( 'seats_price', 'submenu' );
		$this->setData( '???????????????????????????- ???????????? - '.$this->site['pageTitle'], 'pageTitle' );
		$this->display( 'show/show_set_seats_price' );




	}



	function show_manage_agents_action(){
		
		
		$id = (int)get2('id');
		$mdl_show_manage_agents = $this->loadModel( 'wj_show_agent_info' );
		
		if ( $id > 0 ) {
			$show_manage_agents = $mdl_show_manage_agents->get( $id );
			if ( ! $show_manage_agents ) $this->sheader( null, '???????????????' );

			if ( $mdl_show_manage_agents->delete( $id ) ) {

			}
			$this->sheader( $this->parseUrl()->set( 'id' ) );
		}
		
		$pageSql	= $mdl_show_manage_agents->getListSql( null, array( 'createUserId ='. $this->loginUser['id'] .' or createUserId=0' ) );
		$pageUrl	= $this->parseUrl()->set('page');
		$pageSize	= 10;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $mdl_show_manage_agents->getListBySql($page['outSql']);
		
		
		
		$this->setData( $data, 'data' );
		$this->setData( $page['pageStr'], 'pager' );
		$this->setData( $this->parseUrl()->setPath( 'show/show_manage_agents_edit' ), 'editUrl' );
		
		$this->setData( '???????????????????????????', 'pagename' );
		$this->setData( 'show', 'menu' );
		$this->setData( 'manage_agents', 'submenu' );
		$this->setData( '??????????????????????????? - ???????????? - '.$this->site['pageTitle'], 'pageTitle' );
		$this->display( 'show/show_manage_agents' );
	}

	function show_manage_agents_edit_action(){
		
		
		$id=(int)get2('id');
		$mdl_wj_show_agent_info =$this->loadModel("wj_show_agent_info");
		
		
		
		if($id){
			$user =$mdl_wj_show_agent_info->get($id);
		}
			// ??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????
			//??????????????????
		

		
		if ( is_post() ) {
			$name =trim(post('name'));
			$company_name =trim(post('company_name'));
			$address  =trim(post('address'));
			$contact_person =trim(post('contact_person'));
			$tel =trim(post('tel'));
			$mobile =trim(post('mobile'));


				//echo json_encode($title);exit;
			$data = array(
				'name'=>$name,
				'company_name'=>$company_name,
				'address'=>$address,
				'contact_person'=>$contact_person,
				'tel'=>$tel,
				'mobile'=>$mobile,
				'createUserId'=>$this->loginUser['id']
				);

			if($user){
				if ( $mdl_wj_show_agent_info->update( $data,$id ) ) {
					$this->form_response(200,'????????????',HTTP_ROOT_WWW."show/show_manage_agents");
				}
				else {
					$this->form_response_msg('????????????');
				}

			}else{

				if ( $mdl_wj_show_agent_info->insert( $data) ) {
					$this->form_response(200,'????????????',HTTP_ROOT_WWW."show/show_manage_agents");
				}
				else {
					$this->form_response_msg('????????????');
				}
			}

		}
		else {
			$this->setData( $user, 'data' );

			$this->setData( '?????????????????????', 'pagename' );
			$this->setData( 'business_setting', 'menu' );
			$this->setData( '', 'submenu' );
			$this->setData( '?????????????????? - ???????????? - '.$this->site['pageTitle'], 'pageTitle' );
			$this->display( 'show/show_manage_agents_edit' );

		}
		
		
	}




	//?????????,???????????????????????????
	function show_set_seats_agent_action(){
		
		
		$mdl_wj_show_agent_info=$this->loadModel('wj_show_agent_info');
		
		$mdl_wj_show_seats =$this->loadModel('wj_show_seats');
		$mdl_wj_show=$this->loadModel('wj_show');

		
		$show_id =get2('show_id');
		$area =get2('area');
		$row =get2('row');
		$agent_id=get2('agent_id');
		$type =get2('type'); //??????????????????????????????????????????????????????????????????????????????
		
		if($type=='show'){
			// ???????????????????????????????????????????????????
			$agent_id=0;
			$area=0;
			$row=0;
			
		}else if ($type=='agent'){
			$area=0;
			$row=0;
			
		}else if ($type=='area'){
			$row=0;
			
		}else{
			
		}
		
		
		
		// ???????????????????????????ubonus?????????????????????????????????agent_id = null ?????? ????????? ??????ubonus
		
		if(!$agent_id){
			
			$agent_id=1; //ubonus
		}
		
		// ???????????????deleteID ??????????????? ???????????????????????????????????????????????????????????? ?????????3 ???
		$deleted=get2('deleted');
		if($deleted){
			$update_where =array(
				'show_id'=>$show_id,
				'area'=>$area,
				'row'=>$row,
				'sold <>1'	
				);			
			$update_data =array(
				'agent_id'=>3,
				'reserved'=>1

				);
			$mdl_wj_show_seats->updateByWhere($update_data,$update_where);
			
		}
		
		
		
		if( is_post()){
			
			$show_id =post('show_id');
			$area =post('area');
			$row=post('row');
			$seats_number=post('seats_number');
			
			if ( $seats_number ) {
				$seats = implode( ',', $seats_number );
			}
			
			//$result['status'] = 200;
			//$result['msg'] = $seats;
			//echo json_encode( $result );exit;
			
			$agent_id=post('agent_id');
			
			
			if($seats_number){
				$where_update =array(
					'show_id'=>$show_id,
					'seat_number in ('.$seats.')',
					'sold <>1'
					);

				$data_update=array(
					'agent_id'=>$agent_id,
					'reserved'=>0
					);
			}else{
				$where_update =array(
					'show_id'=>$show_id
					);
				$data_update=array(
					'agent_id'=>3,
					'reserved'=>1
					);
			}
			if($area){
				$where_update['area']=$area;
			}
			if($row){
				$where_update['row']=$row;
			}	

			
			
			//init the row before edit
			$mdl_wj_show_seats->updateByWhere(array('agent_id'=>3,'reserved'=>1),array('show_id'=>$show_id,'area'=>$area,'row'=>$row));
			if($mdl_wj_show_seats->updateByWhere($data_update,$where_update)) {
				$result['msg'] = '???????????????';
				echo json_encode( $result );exit;
			}else{
				$result['status'] = 200;
				$result['msg'] = '????????????-??????????????????????????????';
				echo json_encode( $result );exit;
				
			}

			
		}else {

              // ??????agent ??????
			$sql_agent="select * from #@_wj_show_agent_info ";
			
			$agent_list=$mdl_wj_show_agent_info->getListBySql($sql_agent);
			
			// ?????????????????????
			
			
			$mdl_wj_show=$this->loadModel('wj_show');

			
			$sql_show="select * from #@_wj_show where createUserId=".$this->loginUser['id']." order by id desc";


			
			$show=$mdl_wj_show->getListBySql($sql_show);
			
			
			// ?????? $row ??????????????????????????????row??? 
			if($row){
				$sql_seats ="select agent_id,id,seat_number,special from #@_wj_show_seats where show_id=".$show_id . " and area ='".$area . "' and row='".$row."' and sold<>1 order by ABS(seat_number) ";
			//$result['status'] = 200;
			//$result['msg'] = $sql_seats;
			//echo json_encode( $result );exit;
				
				$seats_list = $mdl_wj_show_seats->getListBySql($sql_seats);
				$this->setData($seats_list,'seats_list');

			}
			$this->setData($show,'show');
			$this->setData($show_id,'show_id');
			$this->setData($area,'area');
			$this->setData($row,'row');
			$this->setData($agent_id,'agent_id');
			$this->setData($agent_list,'agent_list');
		}
		
	//	if(!$show_id){$show_id=2;}
		if ($show_id){

			// 	???????????????????????????

			$sql ="select area from #@_wj_show_seats where show_id=".$show_id. " group by area order by length(area),area";

			$area_list=$mdl_wj_show_seats->getListBySql($sql);

			if($area_list){
				$this->setData($area_list,'area_list');

			}





			// ??????????????????????????????
			if ($area){
				$sql ="select show_id,area,row as row_1 ,ticket_category,price from #@_wj_show_seats where show_id=".$show_id." and area='".$area."' and agent_id =".$agent_id."  group by show_id,area,row_1,ticket_category,price  order by length(row_1),row_1";
				$sql_row_list ="select row as row_1 from #@_wj_show_seats where show_id=".$show_id ." and area='".$area. "' group by row_1 order by length(row_1),row_1";
			}else {
				$sql ="select show_id,area,row as row_1 ,ticket_category,price from #@_wj_show_seats where show_id=".$show_id." and agent_id =".$agent_id." group by show_id,area,row_1,ticket_category,price  order by length(row_1),row_1";
			}

			$row_list =$mdl_wj_show_seats->getListBySql($sql_row_list);
			
			$this->setData($row_list,'row_list');
			
			// $result['status'] = 200;
			// $result['msg'] = $sql;
			// echo json_encode( $result );exit;

			$pageSql	= $sql;
			$pageUrl	= $this->parseUrl()->set('page');
			$pageSize	= 20;
			$maxPage	= 20;
			$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
			$data		= $mdl_wj_show_seats->getListBySql($page['outSql']);



			$this->setData( $data, 'data' );
			$this->setData( $page['pageStr'], 'pager' );

		}
	
		$this->setData( '???????????????????????????', 'pagename' );
		$this->setData( 'show', 'menu' );
		$this->setData( 'seats_agent', 'submenu' );
		$this->setData( '???????????????????????????- ???????????? - '.$this->site['pageTitle'], 'pageTitle' );
		$this->display( 'show/show_set_seats_agent' );
		
		
		
	}



}