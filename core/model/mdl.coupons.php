 <?php
 //alter table cc_coupons drop zhanhui_id;
 //alter table cc_coupons drop bookingTimeSlotData;
 //alter table cc_coupons drop sub_voucher_descriptionv;
 //
/**
id
EvoucherOrrealproduct
sortnum
categoryId
title
businessName
bonusType
pic
*intro
content
*points    //未清理  牵连过大
expiredDay
startTime
endTime
createUserId
createTime
index
recommend
featured
isApproved
hits
buy
country_code
city
qty
stage_qty
industry
perCustomerLimitQuantity
highlight
finePrint
pics
visibleForBusiness
stripCode
voucher_original_amount
voucher_deal_amount
group_buying_id
group_buying_name
redeemProcedure
status
adminId
languageType
sales_user_list
staff_region_limited
coupon_summery_description
deliver_avaliable
flat_rates_to_local_city
flat_rates_national
flat_rates_international
delivery_description
pickup_des
offline_pay_des
pickup_avaliable
amount_for_free_delivery
deliverFeeCalculationType
bookingTimeSlotData
cCategoryId
cCategoryId_lock
isshow_shop

isInManagement
refund_policy
platform_commission_rate
platform_commission_base

fourpx_sku
ALTER TABLE `cc_coupons` ADD `fourpx_sku` VARCHAR(255) NULL AFTER `languageType_en`;
*/



class mdl_coupons extends mdl_base
{

	protected $tableName = '#@_coupons';

	function updateHits( $id ) {
		$id = (int)$id;
		return $this->db->query("update {$this->tableName} set hits=hits+1 where id='{$id}'");
	}

	
	function updateBuy( $id,$qty ) {
		$id = (int)$id;
		return $this->db->query("update {$this->tableName} set buy=buy+{$qty},qty=qty-{$qty} where id='{$id}'");
	}

	function getMostRecentOfUser($userid,$limit){
		$column = array('title','hits','coupon_summery_description','id','pic',
						'bonusType','voucher_deal_amount','voucher_original_amount');
		$where 	= array('isApproved' => 1,
						'status' => 4,
						'isInManagement' => 0,
						 'createUserId' => $userid);
		$order = 'createTime desc';
		
		$data= $this->getList($column,$where,$order,$limit);

		foreach ($data as $key =>$value) {
  			$this->caculatePriceAndPoint($data[$key]);
  		}

  		return $data;
	}
	
	/*
	获得某个类别id的类别名称
	类别id的格式: ,543,542,776,...
	*/
	
	function getCategoryName($categoryId,$mdl_infoclass){
		
		$categoryInfo =explode(",",$categoryId);
	  
		foreach ($categoryInfo as $key => $value) { 
			if($value) {
				$categoryname_record= $mdl_infoclass->get($value);
				$category_name .=$categoryname_record['name'].' ';
			}
	
		}
		
		return $category_name;
	}
	
	
	
	function copy($id,$toUserId,$toUserName){
		$coupon = $this->get( $id );
			
		foreach ( $coupon as $key => $rec ) {
			if ( !is_numeric( $key ) && strtolower($key) != 'id' ) {
				//判断非数字并且不是ID
				if($key =='title'){
					$data[$key] ='(COPY OF) '. $coupon[$key] ;
						
				}else if($key =='createTime'){
					$data[$key] =time();
					
				}else if($key =='businessName'){
					$data[$key] =$toUserName;
					
				}else if ($key =='hits' || $key =='buy' || $key=='pic' || $key=='pics' || $key=='recommend' || $key=='featured' ){

				}else if($key =='createUserId'){
					$data[$key] = $toUserId;
					
				}else {
					$data[$key] = $coupon[$key];
				}
			}
		}
		$data['status'] = 1;
		$newid =$this->insert($data);

		return $newid;
	}
	
	
	function copy_sales_channel($id,$toUserId,$toUserName){
		$coupon = $this->get( $id );
			
		foreach ( $coupon as $key => $rec ) {
			if ( !is_numeric( $key ) && strtolower($key) != 'id' ) {
				//判断非数字并且不是ID
				if($key =='title'){
					$data[$key] =$toUserName;
						
				}else if($key =='createTime'){
					$data[$key] =time();
					
				}else if($key =='businessName'){
					$data[$key] =$toUserName;
					
				}else if ($key =='hits' || $key =='buy' || $key=='pics' || $key=='recommend' || $key=='featured' ){

				}else if($key =='createUserId'){
					$data[$key] = $toUserId;
					
				}else {
					$data[$key] = $coupon[$key];
				}
			}
		}
		$data['status'] = 1;
		$newid =$this->insert($data);

		return $newid;
	}

	function caculatePriceAndPoint(&$data){

		$data['price1'] ='$'.$data['voucher_deal_amount'];

		if($data['voucher_original_amount']>0){
			$data['price2'] ='$'.$data['voucher_original_amount'];
			$data['priceSave'] ='$'.number_format(($data['voucher_original_amount']-$data['voucher_deal_amount']),2);
			$data['priceOff'] =round(100 - $data['voucher_deal_amount']/$data['voucher_original_amount']*100);
		}
		

		switch ($data['bonusType']) {

				case '1':
					$data['typeName']='产品卡';
					$data['typecss']='type-2';
					break;

				case '2':
					$data['typeName']='返现卷';
					$data['typecss']='type-2';
					break;

				case '4':
					$data['typeName']='优惠卡';
					$data['typecss']='type-4';
					break;
		
				case '7':
					
					$data['typeName']='团购券';
					$data['typecss']='type-7';
					break;
				case '18':
					
					$data['typeName']='代金券';
					$data['typecss']='type-7';
					break;	
				case '9':
					$data['typeName']='商品';
					$data['typecss']='type-9';
					break;
				
				case '10':
					$data['typeName'] ='演唱会';
					$data['typecss']='type-10';
					break;

				case '11':
					$data['typeName'] ='电影票';
					$data['typecss']='type-11';
					break;
					
				default:
					$data['price1'] ='';
					$data['price2'] ='';
					$data['typeName'] ='';
					$data['typecss']='type-default';
					break;
			}

		if($data['wholesale_price3']>0||$data['wholesale_price2']>0||$data['wholesale_price1']>0){
			if($data['wholesale_price3']>0){
				$data['price1'] ='$'.$data['wholesale_price3'];
			}elseif($data['wholesale_price2']>0){
				$data['price1'] ='$'.$data['wholesale_price2'];
			}elseif($data['wholesale_price1']>0){
				$data['price1'] ='$'.$data['wholesale_price1'];
			}

			$data['typeName']='批发';
			$data['typecss']='type-9';
		}
	}


	function getCouponsTemplatesByCategory($cid,$type1,$type2){
		if ($type1) {
			$str = " bonusType =".$type1;
		}
		if($type2){
			$str = $str. " or bonusType =".$type2;
		}
		if ($str) {
			$sql ="select * from ".$this->tableName." where createUserId = 261 and (".$str.") and (categoryId like '%106109131%' or categoryId Like '%".$cid."%')";
		}else {
			$sql ="select * from ".$this->tableName." where createUserId = 261 and (categoryId like '%106109131%' or categoryId Like '%".$cid."%')";
		}
		//echo $sql ;exit;
		$result = $this->getListBySql($sql);
		return $result;
	}
	
	
	function getAllCouponsofUser($id){
		
		
		
		$sql ="select * from (SELECT `id`,'m' as main_or_sub ,id as sub_id,`title`,`voucher_original_amount`,`voucher_deal_amount`,round((voucher_original_amount-voucher_deal_amount)/voucher_original_amount*100,1) as discount,`coupon_summery_description`,`qty` ,`pic` FROM `cc_coupons` WHERE createUserId =".$id." and (`bonusType`=7 or `bonusType`=18) and isApproved =1 and status =4 and `EvoucherOrrealproduct` <> 'restaurant_menu'
UNION
select `parent_coupon_id` as id ,'s' as main_or_sub ,id as sub_id, `title`,`original_amount` as voucher_original_amount,`customer_amount` as  voucher_deal_amount,round((original_amount-customer_amount)/original_amount*100,1) as discount,`product_description` as coupon_summery_description,`quantity`  as qty,`picture` as pic from cc_coupons_sub
where `parent_coupon_id` in (select id from cc_coupons where createUserId =" .$id. " and ( `bonusType`=7  or `bonusType`=18) and isApproved =1 and status =4 and `EvoucherOrrealproduct` <> 'restaurant_menu')) as a order by id ,main_or_sub	";		//echo $sql ;exit;
		$result = $this->getListBySql($sql);
		return $result;
	}

	function getCouponList($userId){
		$column=['title','id'];
		$where['createUserId']=$userId;
		return $this->getList($column,$where);
	}

	function getCouponListOfType($userId,$type){
		$column=['title','id','stripCode'];
		$where['createUserId']=$userId;
		$where['bonusType']=$type;
		return $this->getList($column,$where);
	}

	function getCreateUserId($id){
		$result=$this->get($id);
		return $result['createUserId'];
	}

	function getStripCode($id){
		$result=$this->get($id);
		return $result['stripCode'];
	}

	function getQty($id){
		$result=$this->get($id);

		$stock= intval($result['qty']);
		if($stock<0)$stock=0;

		return $stock;
	}

	function getTitle($id)
	{
		$result=$this->get($id);
		return $result['title'];
	}

	function getPic($id)
	{
		$result=$this->get($id);
		return $result['pic'];
	}

	function getCouponListByCustomCategory($userId,$cid,$includeChild=true){
		$where 	= array('isApproved' => 1,
						'status' => 4,
						 'createUserId' => $userId);
		$order =" id desc ";

		if($cid){
			$s=array();
			array_push($s, "cCategoryId like '%".$cid."%'");

			if($includeChild){
				$mdl= loadModel('customizableCategory');
				$mdl->setUserId($userId);
				$ids =$mdl->getChildIdList($cid);

				foreach ($ids as $id) {
					array_push($s, "cCategoryId like '%".$id."%'");
				}
			}
			
			$where[]="(".join(' or ',$s).")";

		}
		return $this->getList($column,$where,$order);
	}

	public function getDeliveryInfo($couponId,$lang)
	{
		$coupon=$this->get($couponId);

		$business_info =loadModel("user")->getBusinessDeliveryInfo($coupon['createUserId'],$lang);

		$delivery_info =array(
			'EvoucherOrrealproduct'=>$coupon['EvoucherOrrealproduct'],
			'deliverFeeCalculationType'=>$coupon['deliverFeeCalculationType'],
			'pickup_avaliable'=>$coupon['pickup_avaliable'],
			'deliver_avaliable'=>$coupon['deliver_avaliable'],

			'flat_rates_to_local_city'=>$coupon['flat_rates_to_local_city'],
			'flat_rates_national'=>$coupon['flat_rates_national'],
			'flat_rates_international'=>$coupon['flat_rates_international'],
		);

		return array_merge($business_info,$delivery_info);
			
	}


  	public function getRandom($limit=8)
  	{	

  		$column = array('title','coupon_summery_description','id','pic','hits',
						'bonusType','voucher_deal_amount','voucher_original_amount');
		
        $where['isApproved']=1;
	    $where['status']=4;
        $currentTime=strtotime ('now');
	    $where[]=" !(autoOffline=1 AND ('$currentTime'<startTime or '$currentTime'>endTime)) ";

		$order = 'RAND()';
		
		$data= $this->getList($column,$where,$order,$limit);
  		foreach ($data as $key =>$value) {
  			$this->caculatePriceAndPoint($data[$key]);
  		}
  		return $data;
  	}

  	public function getRelatedProduct($id)
  	{	
  		$coupon = $this->get($id);
  		$relatedProductStr = $coupon['relatedProduct'];

  		$relatedProductStr=str_replace('#', ',',$relatedProductStr);

  		$where[] = " id in ($relatedProductStr) ";
  		$list = $this->getList(null,$where);

  		return $list;
  	}

  	/**
  	 * 获得一个产品的相关关联产品列表
  	 * 推荐和此相匹配的为奖卡，1 该商家的为奖卡， 2 同小类的产品 3 同中类的为奖卡
  	 * @param  [type] $id [coupon id]
  	 * @return [type]     [array of data to display]
  	 */
  	public function getRecommendProduct($id,$lang)
  	{
  		$coupon=$this->get($id);

       
			$coupon_field=array('id', 'title','title_en', 'pic', 'hits', 'buy','voucher_deal_amount');
		
  		
		if($lang=='en'){
		$coupon_where[]=" languageType_en=1 ";
		}
		if($lang=='zh-cn'){
		$coupon_where[]=" languageType_cn=1 ";
		}	

  		$coupon_where['isApproved']=1;
		$coupon_where['status']=4;
		$currentTime=strtotime ('now');
		$coupon_where[]=" !(autoOffline=1 AND ('$currentTime'<startTime or '$currentTime'>endTime)) ";

		$coupon_where[]=" id<>".$coupon['id'];
  		
  		$coupon_order =' id desc ';

  		
  		$cList = explode(',', $coupon['categoryId']);

  		foreach ($cList as $key => $v) {
  			if(!$v)unset($cList[$key]);
  		}

  		$cpList=array();
  		foreach ($cList as $c) {
  			$cp = substr($c, 0,-3);
  			if($cp)array_push($cpList, $cp);
  		}
  		$cpList=array_unique($cpList);

  		foreach ($cList as $key => $v) {
  			$cList[$key]="'%,$v%,'";
  		}

  		foreach ($cpList as $key => $v) {
  			$cpList[$key]="'%,$v%,'";
  		}

  		$sameSmallCategory=" categoryId like ".join(" or ",$cList);
  		$sameMediumCategory=" categoryId like ".join(" or ",$cpList);

      	$where_sameSmallCategory = array_merge($coupon_where, array($sameSmallCategory));
      	$recommends_sameSmallCategory = $this->getList( $coupon_field,$where_sameSmallCategory, $coupon_order, 8 );

      	$where_sameMediumCategory = array_merge($coupon_where, array("$sameMediumCategory"));
      	$recommends_sameMediumCategory = $this->getList( $coupon_field,$where_sameMediumCategory, $coupon_order, 2);

      	//过滤相同ID
      	$recommends=array();
		
		foreach ($recommends_sameSmallCategory as $c) {
			if (!in_array($c['id'], $idList)){
				array_push($recommends, $c);
				array_push($idList, $c['id']);
			}
		}
		foreach ($recommends_sameMediumCategory as $c) {
			if (!in_array($c['id'], $idList)){
				array_push($recommends, $c);
				array_push($idList, $c['id']);
			}
		}

		if(sizeof($recommends)%2==1)$recommends=array_slice($recommends, 0, -1); //ensure the size is even

		return $recommends;
  	}


  	/**
  	 * 检查产品是可购买
  	 * @param  [type] $data [coupon data or coupon Id]
  	 * @return [type]       [description]
  	 */
  	public function checkIsPublish($data)
  	{	
  		if(is_array($data)){

  		}else{
  			$data = $this->get($data);
  		}

  		if(!array_key_exists('isApproved',$data)
  			||!array_key_exists('status',$data)
  			||!array_key_exists('autoOffline',$data)
  			||!array_key_exists('startTime',$data)
  			||!array_key_exists('endTime',$data)
  		){
  			//throw new Exception("Missing checking parameter", 1);
  			return '产品不可访问';
  		}

  		if($data['isApproved']!=1)return '产品未发布';

  		if($data['status']!=4)return '产品未上线';

  		$now = time();

  		if($data['autoOffline']==1&&($data['startTime']>$now||$data['endTime']<$now))return '产品已过期';

  		return false;

  	}


  	public function actionlist_info($action_id,$lan='cn')
	{	
		$str='';
		switch ($action_id) {
			case 'c01':
			  //customer_apply_coupon;
			  $str= '已购买';
			  break;

			case 'b01':
			  //customer_redeem_coupon;
			  $str= "已交易";
			  break;
            case 'p01':
			  //customer_redeem_coupon;
			  $str= "部分使用";
			  break;
			case 'd01':
			  //coupon_cancelled;
			  $str= "订单取消";
			  break;

			case 'f01':
			  //coupon_dispute_processing;
			  $str= "订单争议处理中";
			  break;

			default:
				break;
		}

		return $str;
		
	}
  

}

?>