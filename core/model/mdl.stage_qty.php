 <?php
class mdl_stage_qty extends mdl_base
{
	//cc_coupon     qty   20   stage_qty 10#10#10
	//cc_coupon sub qty   20   stage_qty 10#10#10
	//cc_shop_stock qty   20   stage_qty 10#10#10

	public $couponList=[];
	
	const MAX_STAGES=3;
	const STAGES_RATE=[0.5,0.3,0.2,];

	// 将现有库存按照比例分割
	// 分割完成后 库存替换为 第一阶段的数量
	public function qtySegmentationProcess($value='')
	{	
		try {
			$this->begin();

			$mdl_coupons=loadModel('coupons');
			$mdl_coupons_sub=loadModel('coupons_sub');
			$mdl_shop_stock=loadModel('shop_stock');

			$list=loadModel('coupon_event_management')->getList('coupon_id',array('status'=>CouponEventStatus::Approved));

			foreach ($list as $l ) {
				$id = $l['coupon_id'];
				//coupon
				
				$coupon = $mdl_coupons->get($id);
				if($coupon['qty']>0&&$coupon['stage_qty']==null){
					$data=[];
					$data['stage_qty']=$this->qtySegmentation($coupon['qty']);
					$data['qty']=0;
					$mdl_coupons->update($data,$id);
				}
				

				//sub coupons
				$sub_coupons = $mdl_coupons_sub->getList(['id','quantity','stage_qty'],array('parent_coupon_id'=>$id));
				foreach ($sub_coupons as $sc ) {
					if($sc['quantity']>0&&$sc['stage_qty']==null){
						$data=[];
						$data['stage_qty']=$this->qtySegmentation($sc['quantity']);
						$data['quantity']=0;
						$mdl_coupons_sub->update($data,$sc['id']);
					}
				}

				//shop stock
				$stocks = $mdl_shop_stock->getList(['guige1Id','guige2Id','couponId','qty','stage_qty'],array('couponId'=>$id));
				foreach ($stocks as $s) {
					if($s['qty']>0&&$s['stage_qty']==null){
						$data=[];
						$data['stage_qty']=$this->qtySegmentation($s['qty']);
						$data['qty']=0;
						$where['guige1Id']=$s['guige1Id'];
						$where['guige2Id']=$s['guige2Id'];
						$where['couponId']=$s['couponId'];
						$mdl_shop_stock->updateByWhere($data,$where);
					}
				}
			}
			
			$this->commit();
		} catch (Exception $e) {
			$this->rollback();
		}
		
	}

	//将下一阶段的库存加入当前库存中。
	public function nextStage()
	{
		try {

			$this->begin();

			$mdl_coupons=loadModel('coupons');
			$mdl_coupons_sub=loadModel('coupons_sub');
			$mdl_shop_stock=loadModel('shop_stock');

			$list=loadModel('coupon_event_management')->getList('coupon_id',array('status'=>CouponEventStatus::Approved));

			foreach ($list as $l ) {
				$id = $l['coupon_id'];

				//coupon
				$coupon = $mdl_coupons->get($id);

				$stage_qty_arr=explode("#", $coupon['stage_qty']);

				if(sizeof($stage_qty_arr)>0){

					$add = $stage_qty_arr[0];

					$data=[];

					$data['qty']=$coupon['qty']+$add;

					$data['stage_qty']=join('#',array_slice($stage_qty_arr, 1));

					$mdl_coupons->update($data,$coupon['id']);

				}

				//sub coupons
				$sub_coupons = $mdl_coupons_sub->getList(['id','quantity','stage_qty'],array('parent_coupon_id'=>$id));
				foreach ($sub_coupons as $sc ) {

					$stage_qty_arr= explode("#", $sc['stage_qty']);

					if(sizeof($stage_qty_arr)>0){

						$add = $stage_qty_arr[0];

						$data=[];

						$data['quantity']=$sc['quantity']+$add;

						$data['stage_qty']=join('#',array_slice($stage_qty_arr, 1));

						$mdl_coupons_sub->update($data,$sc['id']);
					}
					
				}

				//shop stock
				$stocks = $mdl_shop_stock->getList(['guige1Id','guige2Id','couponId','qty','stage_qty'],array('couponId'=>$id));
				foreach ($stocks as $s) {

					$stage_qty_arr= explode("#", $s['stage_qty']);

					if(sizeof($stage_qty_arr)>0){

						$add = $stage_qty_arr[0];

						$data=[];

						$data['qty']=$s['qty']+$add;

						$where['guige1Id']=$s['guige1Id'];
						$where['guige2Id']=$s['guige2Id'];
						$where['couponId']=$s['couponId'];

						$data['stage_qty']=join('#',array_slice($stage_qty_arr, 1));

						$mdl_shop_stock->updateByWhere($data,$where);
					}
					
				}
			}
			$this->commit();
		} catch (Exception $e) {
			$this->rollback();
		}
	}

	private function qtySegmentation($qty)
	{
		$result;

		if($qty<0)return false;
		if(sizeof(self::STAGES_RATE)!=self::MAX_STAGES)return false;
		if(array_sum(self::STAGES_RATE)!=1)return false;

		$stage_qty_arr=[];

		for ($i=0; $i < self::MAX_STAGES; $i++) { 
			$already_staged_qty = array_sum($stage_qty_arr);

			$s = ceil(($qty-$already_staged_qty) * self::STAGES_RATE[$i] / array_sum(array_slice(self::STAGES_RATE,$i)));

			$stage_qty_arr[$i]=$s;
		}

		//adjustment
		$total = array_sum($stage_qty_arr);
		if($total!=$qty){
			for ($i=self::MAX_STAGES-1; $i >=0 ; $i--) { 
				if($stage_qty_arr[$i]>($total-$qty)){
					$stage_qty_arr[$i]=$stage_qty_arr[$i]-(($total-$qty));
					break;
				}
			}
		}

		$result = join('#',$stage_qty_arr);

		return $result;
	}

}

?>