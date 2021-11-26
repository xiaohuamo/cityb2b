<?php
class mdl_coupon_event_management extends mdl_base
{	
	protected $tableName = '#@_coupon_event_management';
	//id
	//coupon_id
	//coupon_title
	//user_id
	//note
	//status

	protected $logTable = '#@_coupon_event_management_log';
	//id
	//action
	//note
	//time
	//performer

	public function addToList($coupon_id)
	{	
		try {
			$this->begin();

			$data=array();

			$mdl_coupons=loadModel('coupons');
			$coupon = $mdl_coupons->get($coupon_id);

			$data['coupon_id']=$coupon['id'];
			$data['coupon_title']=$coupon['title'];
			$data['user_id']=$coupon['createUserId'];
			$data['status']=CouponEventStatus::Init;
			$this->insert($data);

			$cdata['isInManagement']=1;
			$cdata['status']=1;
			$cdata['isApproved']=1;
			$mdl_coupons->update($cdata,$coupon_id);

			$this->commit();
		} catch (Exception $e) {

			$this->rollback();

		}
		
	}

	public function removeFromList($coupon_id)
	{	
		try {
			$this->begin();

			$where['coupon_id']=$coupon_id;


			$cdata['isInManagement']=0;
			$cdata['status']=1;
			$cdata['isApproved']=1;

			loadModel('coupons')->update($cdata,$coupon_id);

			$this->deleteByWhere($where);

			$this->commit();
		} catch (Exception $e) {

			$this->rollback();
			
		}
		
	}

	public function getListOfUser($user_id)
	{
		$where['user_id']=$user_id;
		return $this->getList(null,$where);
	}

	public function isInManagement($coupon_id)
	{
		$where['coupon_id']=$coupon_id;
		return $this->getByWhere($where);
	}

	public function updateStatus($coupon_id,$status)
	{	
		if(CouponEventStatus::Approved==$status){
			$cdata['status']=4;
		}else{
			$cdata['status']=1;
		}
		loadModel('coupons')->update($cdata,$coupon_id);

		$data['status']=$status;
		$where['coupon_id']=$coupon_id;
		$data['note']=$this->defaultStatusNote($status);
		return $this->updateByWhere($data,$where);
	}

	public function getStatus($coupon_id)
	{
		$where['coupon_id']=$coupon_id;
		$data= $this->getByWhere($where);
		return $data['status'];
	}

	public function addLog($action,$note,$performer)
	{	
		$data['action']=$action;
		//$data['desc']=$desc;
		$data['note']=$note;
		$data['performer']=$performer;
		$data['time']=time();

		if ($sql = $this->db->getInsertSql($data, $logTable)) return $this->db->execute($sql);
	}

	public function defaultStatusNote($status)
	{	

		switch ($status) {
			case CouponEventStatus::Init:
				return '请确保产品无误后提交送审';
				break;
			case CouponEventStatus::Processing:
				return '通常审核需要1-2天';
				break;
			case CouponEventStatus::Approved:
				return '产品将于周六8:00pm 自动开始销售';
				break;
			case CouponEventStatus::Rejected:
				return '产品不符合要求，具体原因请联系Ubonus';
				break;
			case CouponEventStatus::EventRunning:
				return '产品将于周六11:00pm 自动下线';
				break;
			case CouponEventStatus::EventFinish:
				return null;
				break;

			default:
				return null;
				break;

			
		}
	}
}


abstract class CouponEventStatus{
	const Init = 0;

	const Processing=1;
	const Approved = 2;
	const Rejected = 3;

	const EventRunning = 4;
	const EventFinish  = 5;
}
abstract class CouponEventAction{
	const Processing=1;
	const Approved = 2;
	const Rejected = 3;

	const EventRunning = 4;
	const EventFinish  = 5;

}





?>