
<?php

//ALTER TABLE `cc_wj_user_temp_carts` DROP  bonus_points ,DROP additional_bonus_points ,DROP orderId ,DROP order_type ,DROP staff_id

class mdl_wj_user_temp_carts extends mdl_base
{

	protected $tableName = '#@_wj_user_temp_carts';
	
	function getAllCartsItem($userId){
		$where['userId']=$userId;
		return $this->getList(null,$where);
	}

	function getTotalMoney($userId){
		$total  = 0;

		$items=$this->getAllCartsItem($userId);

		foreach ($items as $item) {
			$price = $item['single_amount'];
			$qty = $item['quantity'];

			$total +=$price*$qty;
		}

		return $total;
	}

	function getTotalQty($userId){
		$total  = 0;

		$items=$this->getAllCartsItem($userId);

		foreach ($items as $item) {
			$qty = $item['quantity'];
			$total +=$qty;
		}
		return $total;
	}

	/**
	 * 购物车总数
	 * @param  [type] $userId   [description]
	 * @param  [type] $couponId [description]
	 * @return [type]           [description]
	 */
	function getAllItems($userId,$couponId){
		$where['userId']=$userId;
		$where['main_coupon_id']=$couponId;

		$items= $this->getList(null,$where);
		
		return $items;

	}

	function isItemExist($userId,$couponId,$guige_ids,$sub_coupon_id){
		$where['userId']=$userId;
		$where['main_coupon_id']=$couponId;
		$where['guige_ids']=$guige_ids;
		$where['sub_coupon_id']=$sub_coupon_id;

		$item= $this->getByWhere($where);

		return ($item!=null);
	}

	function itemQuantyUpdateByWhere($amount,$userId,$couponId,$guige_ids,$sub_coupon_id){
		$where=array('main_coupon_id'=>$couponId,'userId'=>$userId,'guige_ids'=>$guige_ids,'sub_coupon_id'=>$sub_coupon_id);

		$item= $this->getByWhere($where);
		$qty = (int)$item['quantity'];
		$newQty = $qty + (int)$amount;

		$data['quantity']=$newQty;

		return $this->update($data,$item['id']);
	}

	/**
	 * 购物车产品数据封装一层，加入商家的id和name，用于购物车循环显示
	 * @param  [type] $userId         
	 * @param  [type] $businessUserId 
	 * @return [Array]                organizedData
	 */
	function getDetailedItem($userId,$businessUserId=null,$language1=null){
        
		$mdl_user = loadModel('user');
		
		if($businessUserId){
			
			// 如果当前的商家属于某个统配中心(可以同时发多个商家的货，多个商家在一个购物车checkout, 输入某一个商家，会显示关联的整配中心其它商家的数据)
			$sql =" select suppliers_id  as businessUserId from cc_freshfood_disp_centre_suppliers where business_id =  (select DISTINCT business_id from cc_freshfood_disp_centre_suppliers where suppliers_id =$businessUserId)";
			$busienssList = loadModel('freshfood_disp_centre_suppliers')->getListBySql($sql);
			if ($busienssList){
				$centre_dispatch=1;
			}else{
				$busienssList=[['businessUserId'=>$businessUserId]];
				$centre_dispatch=0;
			}
			
			
			//var_dump($busienssList);exit;

		}else{
			$sql = "SELECT DISTINCT businessUserId FROM cc_wj_user_temp_carts WHERE userId=".$userId;
			$busienssList = $this->getListBySql($sql);
		}
		

		

		$organizedData = array();
		// var_dump($busienssList);
		foreach ($busienssList as $value) {
			$data=array();

			$data['businessUserId']=$value['businessUserId'];
			$data['centre_dispatch']=$centre_dispatch;
			$data['businessUserName']=$mdl_user->getBusinessDisplayName($value['businessUserId'],$language1);
			$data['items']=$this->getDetailedItemOfBusiness($userId,$value['businessUserId']);
            if($data['items']) {
				
				array_push($organizedData, $data);
			}else{
				
			}
			
		}
	
		return $organizedData;
	}

	/**
	 * 单个商家购物车产品数据
	 * @param  [type] $userId         
	 * @param  [type] $businessUserId 
	 * @return [Array]                Items
	 */
	function getDetailedItemOfBusiness($userId,$businessUserId){
        $sql="select w.*,a.*,concat('/data/upload/',c.menu_pic) as menu_pic,b.pic,b.bonusType,b.sales_user_list,b.pickup_avaliable,b.deliver_avaliable,b.perCustomerLimitQuantity,b.EvoucherOrrealproduct ,
        ifnull(c.unit,c.unit_en) as unit, ifnull(c.unit_en,c.unit) as unit_en ,c.menu_cn_name as title_cn , c.menu_en_name as title_en
from #@_wj_user_temp_carts a LEFT JOIN #@_coupons as b  on  b.id =a.main_coupon_id
    LEFT JOIN #@_wholesale AS w ON w.couponid=a.main_coupon_id 
    left join cc_restaurant_menu c on c.id =a.menu_id 
where businessUserId ='". $businessUserId ."' and  userId =".$userId . " order by a.main_coupon_id ,a.sub_coupon_id  ";

        $data=$this->getListBySql($sql);
        $mdl_menu = loadModel('restaurant_menu');
        foreach ($data as $key => $value) {
        	if($value['menu_id']>0){
        		$d= $mdl_menu->get($value['menu_id']);
        		$data[$key]['pic']=$d['menu_pic'];
        	}

        }

        return $data;
        	
	}

   function addItemsToCart($value,$user_id,$currentCoupon,$businessId){
       $data =array();
       $data['userId'] =$user_id;
       $data['createTime']=time();
       $data['main_coupon_id']=$currentCoupon['id'];
       $data['sub_coupon_id']=$currentCoupon['id'];
       $data['coupon_name']=$value['title_cn'];
       $data['coupon_name_en']=$value['title'];
       $data['businessUserId']=$businessId;
       $data['quantity']=$value['num'];
       $data['single_amount']=$value['price'];
       $data['guige_des']=$value['guige_des'];
       $data['guige_ids']=$value['guige_ids'];
       $data['menu_id']=$value['id'];
       $data['coupon_name_en']=$value['title'];
       $data['seat_id'] =1; //做一个标记，标记、、、

       $newId =$this->insert($data);
       if(!$newId){
           return 0;
       }
      return $newId;
   }


    function deleteAllItemOfThisBusinessId($userid,$business_userId){

        $where=array(
          'businessUserId'=>$business_userId,
            'userId'=>$userid
        );
        $this->deleteByWhere($where);
        return 1;
    }

    function isOwnerOfItem($user_id,$id){
        $rec =$this->get($id);
        if($rec['userId'] != $user_id) {
            return false;
        }
        return true;

    }

	public function allItemsAreEvoucher($items)
	{	
		$allItemsAreEvoucher = true;

		foreach ($items as $key => $value) {
			 if($value['EvoucherOrrealproduct']=='realproduct'  || $value['EvoucherOrrealproduct']=='restaurant_menu'){
			 	$allItemsAreEvoucher=false;
			 	break;
			 }
		}

		return $allItemsAreEvoucher;
	}

	public function itemsHasPickupAvaliable($items)
	{
		$itemsHasPickupAvaliable = false;

		foreach ($items as $key => $value) {
			 if($value['pickup_avaliable']=='1'){
			 	$itemsHasPickupAvaliable=true;
			 	break;
			 }
		}

		return $itemsHasPickupAvaliable;
	}

	public function itemsHasDeliverAvaliable($items)
	{
		$itemsHasDeliverAvaliable = false;

		foreach ($items as $key => $value) {
			 if($value['deliver_avaliable']=='1'){
			 	$itemsHasDeliverAvaliable=true;
			 	break;
			 }
		}

		return $itemsHasDeliverAvaliable;
	}

	/**
	 * 清空用户的购物车
	 * @param  [type] $userId [description]
	 * @return [type]         [description]
	 */
	public function clearTempCart($userId)
	{
		$where ['userId']= $userId;
		return $this->deleteByWhere($where);
	}


	public function removeAllItemOfOtherBusiness($userId,$businessUserId)
	{
		$where = array('businessUserId <>' . $businessUserId, 'userId' => $userId);
		return $this->deleteByWhere($where);
	}

	public function removeAllItemOfBusiness($userId,$businessUserId)
	{
		$where ['userId']= $userId;
		$where ['businessUserId']= $businessUserId;
		$this->deleteByWhere($where);
		$sql ="delete from cc_wj_user_temp_carts where userId=".$userId. " and businessUserId in (select DISTINCT suppliers_id from cc_freshfood_disp_centre_suppliers where  business_id=$businessUserId)";
		//var_dump($sql);exit;
		$this->getListBySql($sql);
		return 1;
	}


}


/**
*  添加购物车流程
*/
class AddCartProcess
{

	private $userId;

	
	private $main_coupon_id;

	private $sub_coupon_id;

	private $sub_or_main;

	private $coupon_name;
	private $coupon_name_en;


	private $single_amount;

	private $quantity;
	private $quantity_operation; //default is add. can be set to update


	private $guige_id;

	private $guige_des;

    private $onSpecial;

	private $businessUserId;


	private $menu_id;

	private $sidedish_menu_id;
    private $commission_free;
    private $original_amount;

	private $mdl;
	

	function __construct()
	{	
		//default value;
		$this->sub_or_main='m';
		$this->quantity=1;

		$this->mdl = loadModel('wj_user_temp_carts');
	}


	public function owner($userId)
	{
		$this->userId = $userId;
		return $this;
	}

	public function qty($qty,$operation=null)
	{
		$this->quantity = $qty;
		$this->quantity_operation = $operation;
		return $this;
	}

	public function addDefault($coupon_id)
	{
		$mdl_coupon = loadModel('coupons');
		$coupon =$mdl_coupon->get($coupon_id);

		//if type 9 and has guige
			//add with default guige

		//else
			//add
	}

	public function add($coupon_id,$sub_coupon_id=null,$guige_id=null,$language1){
		if($coupon_id&&!$sub_coupon_id&&!$guige_id){
			//主卡
			$this->addCoupon($coupon_id);
		}elseif($coupon_id&&$sub_coupon_id&&!$guige_id){
			//子卡
			$this->addCoupon($coupon_id,$sub_coupon_id);
		}elseif($coupon_id&&!$sub_coupon_id&&$guige_id){
			//规格
			$this->addProduct($coupon_id,$guige_id,$language1);
		}elseif($coupon_id&&$sub_coupon_id&&$guige_id){
			throw new Exception("sub coupon and guige can not exist at the same time", 1);
		}elseif(!$coupon_id){
			throw new Exception("missing coupon id", 1);
		}

		if($this->_isItemExist()){
			$this->_itemQuantyUpdate();
		}else{
			$this->_save();
		}

	}

	private function addCoupon($coupon_id,$sub_coupon_id=null)
	{	
		$this->main_coupon_id =$coupon_id;
		$this->sub_coupon_id = $sub_coupon_id;

		if($sub_coupon_id&&$sub_coupon_id!=$coupon_id){
			//添加子卡
			$this->sub_or_main='s';

			$mdl_coupon = loadModel('coupons_sub');
			$coupon =$mdl_coupon->get($sub_coupon_id);

			$this->coupon_name = $coupon['title'];
			$this->coupon_name_en = $coupon['title_en'];
			$this->single_amount=$coupon['customer_amount'];
			$this->businessUserId=$coupon['create_user_id'];
		}else{
			//添加主卡
			$this->sub_or_main='m';

			$mdl_coupon = loadModel('coupons');
			$coupon =$mdl_coupon->get($coupon_id);

			$this->coupon_name = $coupon['title'];
			$this->coupon_name_en = $coupon['title_en'];
			$this->single_amount=$coupon['voucher_deal_amount'];
			$this->businessUserId=$coupon['createUserId'];
		}
	}

	/**
	 * add type 9
	 * @param [type] $coupon_id [description]
	 * @param [type] $guige_id  规格ID必须为 1234 或 1234,1234
	 */
	private function addProduct($coupon_id,$guige_id,$language1)
	{
		$mdl_coupon = loadModel('coupons');
		$coupon =$mdl_coupon->get($coupon_id);

		$this->main_coupon_id =$coupon_id;

		$this->coupon_name = $coupon['title'];
		$this->coupon_name_en = $coupon['title_en'];
		
		if($language1=='en'){
			if($coupon['title_en']){
				$this->coupon_name = $coupon['title_en'];
			}
		}
		$this->single_amount=$coupon['voucher_deal_amount'];
		$this->businessUserId=$coupon['createUserId'];

		//guige1id,guige2id

		$mdl_shop_guige_detials=loadModel('shop_guige_details');

		$guige_des='';
		foreach (explode(',', $guige_id) as $id) {
			$guige_des .= $mdl_shop_guige_detials->getGuigeName($id,$language1)." ";
		}

		$this->guige_id=$guige_id;
		$this->guige_des=$guige_des;

	}


	private function _save()
	{
		$data['userId']         =$this->userId;
		
		$data['main_coupon_id'] =$this->main_coupon_id;
		$data['sub_coupon_id']  =$this->sub_coupon_id;
		$data['sub_or_main']    =$this->sub_or_main;
		$data['coupon_name']    =$this->coupon_name;
		$data['coupon_name_en']    =$this->coupon_name_en;
		
		$data['single_amount']  =$this->single_amount;
		$data['quantity']       =$this->quantity;
		
		$data['guige_ids']       =$this->guige_id;
		$data['guige_des']      =$this->guige_des;
		
		$data['businessUserId'] =$this->businessUserId;
		
		$data['menu_id'] =$this->menu_id;
		$data['onSpecial'] =$this->onSpecial;

		$data['createTime']     =time();

        $data['commission_free'] =$this->commission_free;
        $data['original_amount'] =$this->original_amount;
		return $this->mdl->insert($data);
	}


	private function _isItemExist(){
		$where['userId']         =$this->userId;
		$where['main_coupon_id'] =$this->main_coupon_id;
		$where['guige_ids']      =$this->guige_id;
		$where['sub_coupon_id']  =$this->sub_coupon_id;
		$where['onSpecial']  =$this->onSpecial;

		$where['menu_id']  =$this->menu_id;

		$item= $this->mdl->getByWhere($where);

		return ($item!=null);
	}

	private function _itemQuantyUpdate(){
		$where=array(
			'main_coupon_id'=>$this->main_coupon_id,
			'userId'=>$this->userId,
			'guige_ids'=>$this->guige_id,
			'sub_coupon_id'=>$this->sub_coupon_id,
			'menu_id'=>$this->menu_id,
			'onSpecial'=>$this->onSpecial
			);

		$item= $this->mdl->getByWhere($where);

		if($this->quantity_operation=='update'){
			$newQty = (int)($this->quantity);
		}else{
			$qty = (int)$item['quantity'];
			$newQty = $qty + (int)($this->quantity);
		}

		$data['quantity']=$newQty;
		
		return $this->mdl->update($data,$item['id']);
	}

	/**
	 * 菜单和配菜
	 */

	public function addMenu($main_coupon_id,$menu_id,$language1,$isOnSpecial, $factoruId = null)
	{
		$this->main_coupon_id=$main_coupon_id;
		$this->sub_coupon_id=$main_coupon_id;
		$this->menu_id=$menu_id;
		$this->onSpecial =$isOnSpecial;



		//折扣
		$restaurant =loadModel('coupons')->get($main_coupon_id);
		
        $restaurant_promotion_manjian_rates=loadModel("restaurant_promotion_manjian")->getRestaurantPromotionManjian($restaurant['createUserId']);


		//购买主菜
		$item=loadModel('restaurant_menu')->get($menu_id);
		if ($language1=='en') {
			if ($item['menu_en_name']) {
				
				if($isOnSpecial) {
					
					$this->coupon_name='(Special)'.$item['menu_en_name'];
				}else{
					$this->coupon_name=$item['menu_en_name'];
				}
				
			}else{
				
				if($isOnSpecial) {
					
					$this->coupon_name='(特价)'.$item['menu_cn_name'];
				}else{
					$this->coupon_name=$item['menu_cn_name'];
				}
			
				
			}
				
		}else{
			
			
			if($isOnSpecial) {
					
					$this->coupon_name='(特价)'.$item['menu_cn_name'];
				}else{
					$this->coupon_name=$item['menu_cn_name'];
				}
			
		}
		

		if($isOnSpecial) {
			
			$this->single_amount=$item['speical_price'];
		}else{
		    if($factoruId) {
                $show_origin_price = loadModel('user_factory')->getByWhere([
                    'user_id' => $this->userId,
                    'factory_id' => $factoruId
                ])['show_origin_price'];
                if(!$show_origin_price) {
                  //  $item['price'] = 0; 这个价格还是放进去
                }
            }

            $mdl_user_factory_menu_price = loadModel('user_factory_menu_price');
            $userFactoryPrice =  $mdl_user_factory_menu_price->getUserFactoryPrice($this->userId, $item['id']);
            if($userFactoryPrice) {
                $item['price'] = $userFactoryPrice['price'];
            }

			$this->single_amount=$item['price']*(1-$restaurant_promotion_manjian_rates);
		}
		
        $this->commission_free=$item['commission_free'];
        $this->original_amount=$item['price'];
		$this->businessUserId=$item['restaurant_id'];

		if($this->_isItemExist()){
			$this->_itemQuantyUpdate();
		}else{
			$this->_save();
		}
	}

}
?>