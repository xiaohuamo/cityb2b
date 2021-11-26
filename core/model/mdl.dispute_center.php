<?php 
class CaseAction{

	const PLATFORM_REFUND    ='platform_refund';  //平台退款 商家退货
	const BUSINESS_REFUND    ='business_refund';  //商家退款 商家退货

	public $action_list;
	public $action_length;
	public $current_index;

	private $isActionComplete;
	function CaseAction($type){
		$action_list= array();

		switch ($type) {
			case self::PLATFORM_REFUND:
				/**
				 * 用户退货
				 */
				$action_list[]=new CaseActionStep(CaseActionStep::ACTION_GOODSREFUND_SEND,CaseActionStep::USER);

				/**
				 * 商家收到退货
				 */
				$action_list[]=new CaseActionStep(CaseActionStep::ACTION_GOODSREFUND_RECEIVE,CaseActionStep::BUSINESS);

				/**
				 * 平台退款
				 */
				$action_list[]=new CaseActionStep(CaseActionStep::ACTION_MONEYREFUND_SEND,CaseActionStep::PLATFORM);

				/**
				 * 用户收到退款
				 */
				$action_list[]=new CaseActionStep(CaseActionStep::ACTION_MONEYREFUND_RECEIVE,CaseActionStep::USER);
				break;
			
			case self::BUSINESS_REFUND:
				/**
				 * 用户退货
				 */
				$action_list[]=new CaseActionStep(CaseActionStep::ACTION_GOODSREFUND_SEND,CaseActionStep::USER);

				/**
				 * 商家收到退货
				 */
				$action_list[]=new CaseActionStep(CaseActionStep::ACTION_GOODSREFUND_RECEIVE,CaseActionStep::BUSINESS);

				/**
				 * 商家退款
				 */
				$action_list[]=new CaseActionStep(CaseActionStep::ACTION_MONEYREFUND_SEND,CaseActionStep::BUSINESS);

				/**
				 * 用户收到退款
				 */
				$action_list[]=new CaseActionStep(CaseActionStep::ACTION_MONEYREFUND_RECEIVE,CaseActionStep::USER);
				break;

			default:
				break;
		}

		$this->action_list=$action_list;
		$this->action_length=sizeof($this->action_list);
		$this->current_index=0;
		$this->isActionComplete=false;
	}

	public function getNextStep()
	{	
		$next_index = $this->current_index+1;

		if($next_index<$this->action_length){
			return $this->action_list[$next_index];
		}else{
			return null;
		}
		
	}

	public function getPrevStep()
	{	
		$prev_index = $this->current_index-1;

		if($prev_index<0){
			return null;
		}else{
			return $this->action_list[$prev_index];
		}
		
	}

	public function getCurrentStep()
	{	
		return $this->action_list[$this->current_index];
	}

	public function completeCurrentStep()
	{
		$currentActionStep= $this->getCurrentStep();

		if(!$currentActionStep)return false;

		$currentActionStep->completeStep();

		$this->current_index++;

		$this->isActionComplete();
	}

	public function completeToStep($index)
	{
		if($index<0||$index>=$this->action_length){
			return false;
		}else{
			$this->current_index=$index;
			for ($i=$this->current_index; $i >=0 ; $i--) { 
				$s = $this->action_list[$i];
				$s->completeStep();
			}

			for ($i=$this->current_index; $i <$this->action_length ; $i++) { 
				$s = $this->action_list[$i];
				$s-> pendingStep();
			}
		}
	}

	public function isActionComplete()
	{	
		if($this->isActionComplete){
			return $this->isActionComplete;
		}else{
			$this->isActionComplete=($this->current_index>=$this->action_length);
			return $this->isActionComplete;
		}
	}

}


/**
 *   纠纷CASE 有一定的解决流程。CaseAction定义没有个动作  
 */
class CaseActionStep{

	/**
	 * 动作的类型
	 */
	public $type;

	const ACTION_GOODSREFUND_SEND    ='action_goodsrefund_send';
	const ACTION_GOODSREFUND_RECEIVE ='action_goodsrefund_receive';
	const ACTION_MONEYREFUND_SEND    ='action_moneyrefund_send';
	const ACTION_MONEYREFUND_RECEIVE ='action_moneyrefund_receive';
	
	/**
	 * 动做的执行者
	 */
	public $required_user;

	const BUSINESS                   ='business';
	const USER                       ='user';
	const PLATFORM                   ='platform';
	
	/**
	 * 动作的执行状态
	 */
	public $status;

	const PENDING                    ='pending';
	const DONE                       ='done';

	public $update_date;

	public function CaseActionStep($type,$required_user,$status=self::PENDING)
	{
		$this->type          =$type;
		$this->required_user =$required_user;
		$this->status        =$status;
	}

	public function completeStep()
	{
		$this->status=self::DONE;
		$this->update_date=time();
	}

	public function pendingStep()
	{
		$this->status=self::PENDING;
	}

	public function getActionDesc()
	{
		switch ($this->type) {

			case self::ACTION_GOODSREFUND_SEND:
				return "退货";
				break;

			case self::ACTION_GOODSREFUND_RECEIVE:
				return "确认收到退货";
				break;

			case self::ACTION_MONEYREFUND_SEND:
				return "退款";
				break;

			case self::ACTION_MONEYREFUND_RECEIVE:
				return "确认收到退款";
				break;

			default:
				# code...
				break;
		}
	}
}


/**
 * 处理客户纠纷，正式客户咨询和产品问询
 * 每一条记录 就是一个 CASE
 */

class mdl_dispute_center extends mdl_base
{
	const OPEN='open';
	const CLOSE='close';

	protected $tableName = '#@_dispute_center';

	public $case_id;

	//涉及的订单ID
	public $order_id;

	//订单涉及的买卖双方
	public $customer_id;
	public $business_id;

	//提交case的人
	public $case_creator_id;

	//case需要完成的动作
	public $action;
	public $action_type;
	//描述
	public $reason;

	//状态
	public $status;



	public function owner($case_creator_id)
	{
		$this->case_creator_id=$case_creator_id;
		return $this;
	}

	public function order($order_id)
	{
		$this->order_id=$order_id;

		$order= loadModel('order')->getByOrderId($order_id);
		if($order){
			$this->customer_id     =$order['userId'];
			$this->business_id     =$order['business_userId'];

			if($this->isPaymentSelfManagement()){
				//原始订单为商家收款
				$this->action(CaseAction::BUSINESS_REFUND);
			}else{
				//原始订单为平台收款
				$this->action(CaseAction::PLATFORM_REFUND);
			}
		}

		return $this;
	}

	/**
	 * 一定要是 CaseAction Object
	 * @param  [CaseAction] $caseAction 
	 */
	public function action($caseActionType)
	{	
		$this->action_type=$caseActionType;
		$this->action=new CaseAction($caseActionType);
		return $this;
	}

	public function reason($reason)
	{
		$this->reason=$reason;
		return $this;
	}

	public function getCase($id)
	{	
		if($id){
			$data = $this->get($id);
		}else{
			$data = $this->get($this->case_id);
		}

		$this->case_id         =$data['id'];
		$this->order_id        =$data['order_id'];
		$this->customer_id     =$data['customer_id'];
		$this->business_id     =$data['business_id'];
		$this->case_creator_id =$data['case_creator_id'];
		$this->action          =unserialize($data['action']);
		$this->action_type     =$data['action_type'];
		$this->reason          =$data['reason'];
		$this->status          =$data['status'];
		
		return $this;
	}

	public function closeCase()
	{

	}

	public function reopenCase()
	{

	}


	public function openNewCase()
	{	
		$data['order_id']        =$this->order_id;
		$data['customer_id']     =$this->customer_id;
		$data['business_id']     =$this->business_id;
		$data['case_creator_id'] =$this->case_creator_id;
		$data['action']          =serialize($this->action);
		$data['action_type']     =$this->action_type;
		$data['reason']          =$this->reason;
		$data['status']          =self::OPEN;
		$data['gen_date']        =time();
		$data['last_update_date']=time();

		$this->case_id = $this->insert($data);

		return $this->case_id;
	}

	public function updateCase()
	{
		
		$data['action']          =serialize($this->action);
		$data['status']          =$this->status;
		$data['last_update_date']=time();

		return $this->update($data,$this->case_id);
	}

	public function actionDesc($type)
	{
		if(!$type){
			$type = $this->action_type;
		}

		switch ($type) {
			case CaseAction::BUSINESS_REFUND:
				return "商家退款 商家退货";
				break;
			
			case CaseAction::PLATFORM_REFUND:
				return "平台退款 商家退货";
				break;
			default:
				return false;
				break;
		}
	}

	private function isPaymentSelfManagement(){
		/**
		 * 根据订单类型植入不同的 caseAction
		 * 这里需要倒追找到订单交易时商家收款还是平台收款
		 * 在订单表里增加字段记录此数据比较合理，但是却无法支持之前订单的数据
		 * 所以采用倒追 ccrecharge 记录的方法 只要存在 TYPE_SYS_TRANSACTION_BALANCE， 即为平台收款
		 * 同时订单表中也新增了字段。将来当可以忽略2017-09-01以前的订单时。就可以切换处理方式。*
		 */
		
		$mdl_recharge=loadModel('recharge');

		$where['orderId']=$this->order_id;
		$recoreds = $mdl_recharge->getList(null,$where);

		$isPaymentSelfManagement=true;
		foreach ($recoreds as $r) {
			if($r['payment']==BalanceProcess::TYPE_SYS_TRANSACTION_BALANCE){
				$isPaymentSelfManagement=false;
				break;
			}
		}

		return $isPaymentSelfManagement;
	}


  

}

