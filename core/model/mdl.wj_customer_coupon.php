<?php

class mdl_wj_customer_coupon extends mdl_base
{

	protected $tableName = '#@_wj_customer_coupon';

	
	const CLN_USERID 		= 'userId';
	const CLN_BONUSID		= 'bonus_title';
	const CLN_BUSINESSSTAFFID='business_staff_userid';
	const CLN_GUIGEDES		= 'guige_des';
	const CLN_COUPONSTATUS	= 'coupon_status';

	const CLN_BUSINESSID 	= 'business_id';
	const CLN_GENDATE		= 'gen_date';
	

	function getStatics($id,$columns=array(),$from=null,$to=null){
		if(is_null($id))return null;

		$where = [];
		array_push($where, self::CLN_BUSINESSID." = ".$id );
		array_push($where, "coupon_status <> 'd01'" );
		if($from)	array_push($where, self::CLN_GENDATE." >= ".$from);
		if($to)		array_push($where, self::CLN_GENDATE." <= ".$to);

		$groupBy = $columns;

		array_push($columns, 'count(id) as total, sum(customer_buying_quantity) as qty');

		if($groupBy){
			$sqlFormat = " select %s from %s where %s group By %s";
			$sql = sprintf($sqlFormat, join(',',$columns), $this->tableName, join(' and ',$where) , join(',',$groupBy) );
		}else{
			$sqlFormat = " select %s from %s where %s ";
			$sql = sprintf($sqlFormat, join(',',$columns), $this->tableName, join(' and ',$where)  );
		}
		return $this->getListBySql($sql);
	}

	function replaceStaticTableLabel($data){

		if(sizeof($data)==0)return array(array('总数'=>0));
		$label = array(
				self::CLN_BONUSID=>"产品",
				self::CLN_GUIGEDES=>"规格",
				self::CLN_USERID=>"用户",
				self::CLN_BUSINESSSTAFFID=>"员工",
				self::CLN_COUPONSTATUS=>"状态",
				'total'=>"总订单",
				'qty'=>"总销量"
			);
		$result = array();

		$mdl_user=loadModel('user');

		$mdl_coupons=loadModel('coupons');

		foreach ($data as $row) {
			$newrow=array();
			foreach ($row as $key => $value) {
				if(!array_key_exists($key, $label))continue;

				if($key==self::CLN_USERID){
					//replace user it with user name
					//$newrow[$label[$key]]=$mdl_user->getUserDisplayName($value);
					$newrow[$label[$key]]='<a href="'.HTTP_ROOT_WWW.'company/customer_orders?sk='.$value.'" >'.$value.'</a>';

				}elseif($key==self::CLN_BUSINESSSTAFFID){
					//replace user it with user name
					$newrow[$label[$key]]=$mdl_user->getPickupLocationName($value);
				
				}elseif($key==self::CLN_COUPONSTATUS){
					//replace coupon status with name
					$newrow[$label[$key]]=$mdl_coupons->actionlist_info($value);

				}elseif($key==self::CLN_BONUSID){
					//replace coupon title with title+id
					$newrow[$label[$key]]=$row[$key].'('.$row['bonus_id'].')';

				}else{
					$newrow[$label[$key]]=$value;
				}
				
			}
			array_push( $result,$newrow);
		}

		return $result;
	}

	function getItemsInOrder($order_id,$business_id){
		
	
		
		$column = ['id','bonus_title','bonus_type','bonus_type_name','customer_buying_quantity','guige_des','voucher_deal_amount'];
		$where['order_id'] = $order_id;
		if($business_id) {
			$where['business_id'] = $business_id;
		}
		
		$sql =" select c.id,if(length(m.menu_en_name)>0,m.menu_en_name,c.bonus_title) as bonus_title,c.bonus_type,c.bonus_type_name,c.menu_id,c.new_customer_buying_quantity as customer_buying_quantity,
        c.guige_des,c.voucher_deal_amount from cc_wj_customer_coupon c
        left join cc_restaurant_menu m on c.restaurant_menu_id =m.id

";
		$sql.= " where c.order_id =$order_id and ((c.business_id =$business_id ) or c.business_id in (select suppliers_id from cc_freshfood_disp_centre_suppliers where business_id =$business_id) or  business_id in (select customer_id from cc_factory2c_list where factroy_id =$business_id) )";
		
		
//var_dump($sql);exit;

		$data = $this->getListBySql($sql);
		
		return $data;

	}
	



    function getItemsInOrder_menu($order_id,$business_id){



        $column = ['id','bonus_title','bonus_type','bonus_type_name','new_customer_buying_quantity','guige_des','voucher_deal_amount'];
        $where['order_id'] = $order_id;
        if($business_id) {
            $where['business_id'] = $business_id;
        }

        $sql =" select c.id,upper(m.menu_en_name) as menu_en_name,upper(c.bonus_title) as bonus_title,c.bonus_type,c.bonus_type_name,c.voucher_original_amount,c.menu_id,c.new_customer_buying_quantity as customer_buying_quantity,upper(if(length(m.unit_en)>0,m.unit_en,m.unit)) as unit ,c.guige1_id, upper(g.menu_en_name) as guige_des,c.voucher_deal_amount from cc_wj_customer_coupon c left join cc_restaurant_menu  m  ";
        $sql.= " on c.restaurant_menu_id =m.id  left join cc_restaurant_menu_option g on c.guige1_id =g.id  where c.order_id =$order_id and ((c.business_id =$business_id ) or c.business_id in (select suppliers_id from cc_freshfood_disp_centre_suppliers where c.business_id =$business_id) or  c.business_id in (select customer_id from cc_factory2c_list where factroy_id =$business_id) or  business_id in (select customer_id from cc_factory_2blist where factroy_id =$business_id) )";


//var_dump($sql);exit;

        $data = $this->getListBySql($sql);

        return $data;

    }

	function getItemsInOrderWithPic($order_id){
		$sql = "select c.pic,cc.id,cc.bonus_title,cc.bonus_type,cc.bonus_type_name,cc.customer_buying_quantity,cc.guige_des,cc.voucher_deal_amount from cc_wj_customer_coupon as cc left join cc_coupons as c on cc.bonus_id = c.id where cc.order_id=".$order_id;

		$data = $this->getListBySql($sql);

		return $data;

	}

	function isOrderContainsItem($order_id,$coupon_id){
		$containsItem=false;

		$column = ['bonus_id'];
		$where['order_id'] = $order_id;

		$data = $this->getList($column,$where);
		foreach ($data as $item ) {
			if($item['bonus_id']==$coupon_id){
				$containsItem=true;
				break;
			}
		}

		return $containsItem;

	}

    function getOrderItems($orderId) {

        $sql ="select a.*,m.menu_en_name,m.menu_cn_name, if(length(m.unit_en)>0,m.unit_en,m.unit) as unit  from cc_wj_customer_coupon a 
                left join cc_restaurant_menu m on a.restaurant_menu_id = m.id 
                where a.order_id =$orderId order by a.id";
        $list = $this->getListBySql($sql);
    //   var_dump($list);exit;

      return $list;
    }

	
}

?>