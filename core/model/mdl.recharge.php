<?php

class mdl_recharge extends mdl_base
{

	protected $tableName = '#@_recharge';

    public function getRecharge($id){
        return $this->db->selectOne(null, $this->tableName, "id='$id'");
    }

	public function getBalanceOfUser($userid){
		return $this->getBalance($userid)+$this->getOutGoingPending($userid);
	}

	public function getBalance($userid){
		$sql = "SELECT sum(`money`)
		FROM `#@_recharge`
		where `userId` ='".$userid."' and status =1   ";
		
		$sum_money = $this->getListBySql($sql);
		
		return $sum_money[0][0];
	}

	public function getIncomingPending($userid){
		$sql = "SELECT sum(`money`)
		FROM `#@_recharge`
		where `userId` ='".$userid."' and (status=0 ) and (money > 0) ";
		
		$sum_money = $this->getListBySql($sql);
		
		return $sum_money[0][0];
	} 

	public function getOutGoingPending($userid){
		$sql = "SELECT sum(`money`)
		FROM `#@_recharge`
		where `userId` ='".$userid."' and (status=0 ) and (money < 0) ";
		
		$sum_money = $this->getListBySql($sql);
		
		return $sum_money[0][0];
	}

	public function getTotalPending($userid){
		$sql = "SELECT sum(`money`)
		FROM `#@_recharge`
		where `userId` ='".$userid."' and (status=0 ) ";
		
		$sum_money = $this->getListBySql($sql);
		
		return $sum_money[0][0];
	}

	public function addBalanceTo($amount,$to,$orderId,$desc,$type=''){
		$data['userId']=$to;
		$data['money']=$amount;
		$data['orderId']=$orderId;
		$data['coupon_name']=$desc;
		$data['payment']=$type;

		$data['createTime']=time();
		$data['createIp']=ip();

		$data['status']=0;//init state padding
        //var_dump($data);
		return $this->insert($data);
	}

	public function updataTransactionStatus($orderId,$status){	

		$where['orderId']=$orderId;
		$data['status']=$status;
		$data['paytime']=time();
		
		if($status==BalanceProcess::VOID){
			//return $this->deleteByWhere($where);
			return $this->updateByWhere($data,$where); //如果显示 void的 流水， 显示需要进一步修改
		}else{
			return $this->updateByWhere($data,$where);
		}
		
	}

	public function updataNote($orderId,$note){	
		$where['orderId']=$orderId;
		$data['note']=$note;
		return $this->updateByWhere($data,$where);
	}

	/**
	 *  商家自动结算时需要结算的条目
	 **/
	public function businessSettleableRecord()
	{
		$list = array();
		$list[]=BalanceProcess::TYPE_SYS_MONEYPAY_BALANCETRANSFER;
		$list[]=BalanceProcess::TYPE_SYS_TRANSACTION_BALANCE;
		$list[]=BalanceProcess::TYPE_SYS_TRANSACTION_BALANCE_AMEND;
		$list[]=BalanceProcess::TYPE_SYS_PROMOTION_CODE_REFOUND;
		$list[]=BalanceProcess::TYPE_SYS_PLATFORM_FEE;
		$list[]=BalanceProcess::TYPE_SYS_UBONUS_COMMISSION;
		$list[]=BalanceProcess::TYPE_SYS_UBONUS_COMMISSION_AMEND;
		$list[]=BalanceProcess::TYPE_SYS_UBONUS_PAY_AGENT_COMMISSION;
		$list[]=BalanceProcess::TYPE_SYS_DELIVER_FEE;
		$list[]=BalanceProcess::TYPE_SYS_TRANSACTION_FEE_PLATFORM_REFOUND;
		$list[]=BalanceProcess::TYPE_SYS_TRANSACTION_FEE_PLATFORM_CHARGE;

		return $list;
	}

	public function personalSettleableRecord()
	{
		$list = array();
		$list[]=BalanceProcess::TYPE_WITHDRAW;
		$list[]=BalanceProcess::TYPE_REDBAG;
		$list[]=BalanceProcess::TYPE_SYS_USEMONEYPAY;
		$list[]=BalanceProcess::TYPE_SYS_CUSTOMER_REF_COMMISSION;
		$list[]=BalanceProcess::TYPE_SYS_BUSINESS_REF_COMMISSION;
		return $list;
	}


	/**
	 * Helper Functions
	 */
	
	public function balanceProcessTypeArray($lang)
	{
		
		if($lang=='en'){
			$list=array(
			BalanceProcess::TYPE_RECHARGE                             =>'Recharge',
			BalanceProcess::TYPE_WITHDRAW                             =>'Withdraw',
			BalanceProcess::TYPE_REDBAG                               =>'Red envelopes',
			
			BalanceProcess::TYPE_SYS_USEMONEYPAY                      =>'user pay via wallet',
			BalanceProcess::TYPE_SYS_MONEYPAY_BALANCETRANSFER         =>'Business receive red envelops payment',
			
			BalanceProcess::TYPE_SYS_SETTLEMENT_RECHARGE              =>'System settlement recharge',
			BalanceProcess::TYPE_SYS_SETTLEMENT_WITHDRAW              =>'System settlement withdraw',
			
			BalanceProcess::TYPE_SYS_TRANSACTION_BALANCE              =>'Order Transaction balance',
			BalanceProcess::TYPE_SYS_TRANSACTION_BALANCE_AMEND        =>'Order Transaction balance_amend',
			BalanceProcess::TYPE_SYS_PROMOTION_CODE_REFOUND           =>'Promotion code refund to business ',
			BalanceProcess::TYPE_SYS_PLATFORM_FEE                     =>'Platform fees (banks etc)',
			BalanceProcess::TYPE_SYS_UBONUS_COMMISSION                =>'Commissions paid by Business',
			BalanceProcess::TYPE_SYS_UBONUS_COMMISSION_AMEND           =>'Commissions amend paid by Business',
			BalanceProcess::TYPE_SYS_UBONUS_PAY_AGENT_COMMISSION      =>'commissions to agent',
			BalanceProcess::TYPE_SYS_DELIVER_FEE                      =>'Freight Fees',
			BalanceProcess::TYPE_SYS_TRANSACTION_FEE_PLATFORM_REFOUND =>'Transcation fee platform refund.',
			BalanceProcess::TYPE_SYS_TRANSACTION_FEE_PLATFORM_CHARGE  =>'Transcation fee platform charge',
			
			BalanceProcess::TYPE_SYS_CUSTOMER_REF_COMMISSION          =>'User referal commissions',
			BalanceProcess::TYPE_SYS_BUSINESS_REF_COMMISSION          =>'Business referal commissions',
			);
		}else{
			$list=array(
			BalanceProcess::TYPE_RECHARGE                             =>'充值',
			BalanceProcess::TYPE_WITHDRAW                             =>'取现',
			BalanceProcess::TYPE_REDBAG                               =>'红包',
			
			BalanceProcess::TYPE_SYS_USEMONEYPAY                      =>'用户使用钱包支付',
			BalanceProcess::TYPE_SYS_MONEYPAY_BALANCETRANSFER         =>'商家收到的钱包支付账款',
			
			BalanceProcess::TYPE_SYS_SETTLEMENT_RECHARGE              =>'系统结算自动充值记录',
			BalanceProcess::TYPE_SYS_SETTLEMENT_WITHDRAW              =>'系统结算自动取现取现',
			
			BalanceProcess::TYPE_SYS_TRANSACTION_BALANCE              =>'订单交易金额',
			BalanceProcess::TYPE_SYS_TRANSACTION_BALANCE_AMEND              =>'订单退款金额',
			BalanceProcess::TYPE_SYS_PROMOTION_CODE_REFOUND           =>'使用全局折扣码，Ubonus返还金额给商家',
			BalanceProcess::TYPE_SYS_PLATFORM_FEE                     =>'平台向用户收取手续费',
			BalanceProcess::TYPE_SYS_UBONUS_COMMISSION                =>'平台向商家收取提成',
			BalanceProcess::TYPE_SYS_UBONUS_COMMISSION_AMEND                =>'平台向商家收取提成变更',
			BalanceProcess::TYPE_SYS_UBONUS_PAY_AGENT_COMMISSION      =>'平台向代理商支付佣金',
			BalanceProcess::TYPE_SYS_DELIVER_FEE                      =>'运费',
			BalanceProcess::TYPE_SYS_TRANSACTION_FEE_PLATFORM_REFOUND =>'平台退还交易费',
			BalanceProcess::TYPE_SYS_TRANSACTION_FEE_PLATFORM_CHARGE  =>'平台收取交易费',
			
			BalanceProcess::TYPE_SYS_CUSTOMER_REF_COMMISSION          =>'用户介绍关系赚取的手续费',
			BalanceProcess::TYPE_SYS_BUSINESS_REF_COMMISSION          =>'商家介绍关系赚取的手续费',
		);

			
		}
		
		return $list;
	}

	public function businessDisplayRecord()
	{
		$list = array();
		$list[]=BalanceProcess::TYPE_SYS_MONEYPAY_BALANCETRANSFER;
		$list[]=BalanceProcess::TYPE_SYS_SETTLEMENT_RECHARGE;
		$list[]=BalanceProcess::TYPE_SYS_SETTLEMENT_WITHDRAW;
		$list[]=BalanceProcess::TYPE_SYS_TRANSACTION_BALANCE;
		$list[]=BalanceProcess::TYPE_SYS_TRANSACTION_BALANCE_AMEND;
		$list[]=BalanceProcess::TYPE_SYS_PROMOTION_CODE_REFOUND;
		$list[]=BalanceProcess::TYPE_SYS_PLATFORM_FEE;
		$list[]=BalanceProcess::TYPE_SYS_UBONUS_COMMISSION;
		$list[]=BalanceProcess::TYPE_SYS_UBONUS_COMMISSION_AMEND;
		$list[]=BalanceProcess::TYPE_SYS_UBONUS_PAY_AGENT_COMMISSION;
		$list[]=BalanceProcess::TYPE_SYS_DELIVER_FEE;
		$list[]=BalanceProcess::TYPE_SYS_TRANSACTION_FEE_PLATFORM_REFOUND;
		$list[]=BalanceProcess::TYPE_SYS_TRANSACTION_FEE_PLATFORM_CHARGE;
		return $list;	
	}

	public function userDisplayRecord()
	{	
		$list = array();
		$list[]=BalanceProcess::TYPE_RECHARGE;
		$list[]=BalanceProcess::TYPE_WITHDRAW;
		$list[]=BalanceProcess::TYPE_REDBAG;
		$list[]=BalanceProcess::TYPE_SYS_USEMONEYPAY;
		$list[]=BalanceProcess::TYPE_SYS_CUSTOMER_REF_COMMISSION;
		$list[]=BalanceProcess::TYPE_SYS_BUSINESS_REF_COMMISSION;
		return $list;
	}

}



class BalanceProcess{
	private $user;
	private $business;
	private $agent;
	private $platform;

	const USER     ='user';
	const BUSINESS ='business';
	const AGENT ='agent';
	const PLATFORM ='platform';

	private $deliverFeeChargeFrom;
	private $deliverFeePayTo;
	private $transactionFeeChargeFrom;
	private $transactionRecipient;

    private $subBusinessTranscationAmount;
    private $subBusinessCommissionAmount;

	private $transactionAmount;
	private $useBalance;
	private $useGlobalPromotionCode;
	private $deliverFee;
	private $transactionSurcharge;

	private $platformFee;
	private $platformTotalCommission;
	private $agentTotalCommission;

	private $paymentMethod;
	const PAYMENTMETHOD_OFFLINE ='offline';
	const PAYMENTMETHOD_ONLINE  ='online';

	private $transactionOrderId;

	private $processer;

	const PENDING =0;  //处理中
	const SETTLE  =1;  //已结算
	const VOID    =2;    //已取消
	const INIT    =3;    //未处理

	const TYPE_RECHARGE   ='recharge';//充值
	const TYPE_WITHDRAW   ='withdraw';//取现
	const TYPE_REDBAG     ='redbag';//红包

	const TYPE_SYS_USEMONEYPAY                      ='usemoneypay';                  //用户使用钱包支付;
	const TYPE_SYS_MONEYPAY_BALANCETRANSFER         ='MONEYPAY_BALANCETRANSFER';     //商家收到的钱包支付账款;
	
	const TYPE_SYS_SETTLEMENT_RECHARGE              ='SETTLEMENT_RECHARGE';  //系统结算自动充值记录
	const TYPE_SYS_SETTLEMENT_WITHDRAW              ='SETTLEMENT_WITHDRAW';  //系统结算自动取现取现
	
	const TYPE_SYS_TRANSACTION_BALANCE              ='TRANSACTION_BALANCE';  //订单交易金额
	const TYPE_SYS_TRANSACTION_BALANCE_AMEND        ='TRANSACTION_BALANCE_AMEND';  //订单t退款金额
	const TYPE_SYS_PROMOTION_CODE_REFOUND           ='PROMOTION_REFOUND'; //使用全局折扣码，Ubonus返还金额给商家
	const TYPE_SYS_PLATFORM_FEE                     ='PLATFORM_FEE'; 			   //平台向用户收取手续费
	const TYPE_SYS_UBONUS_COMMISSION                ='UBONUS_COMMISSION';      //平台向商家收取提成
	const TYPE_SYS_UBONUS_COMMISSION_AMEND          ='UBONUS_COMMISSION_AMEND';      //平台向商家收取提成
	const TYPE_SYS_UBONUS_PAY_AGENT_COMMISSION      ='UBONUS_PAY_AGENT_COMMISSION';      //平台向商家收取提成
	
	const TYPE_SYS_DELIVER_FEE                      ='DELIVER_FEE';                  //运费
	const TYPE_SYS_TRANSACTION_FEE_PLATFORM_REFOUND ='TRANSACTION_FEE_PLATFORM_REFOUND'; //平台退还交易费
	const TYPE_SYS_TRANSACTION_FEE_PLATFORM_CHARGE  ='TRANSACTION_FEE_PLATFORM_CHARGE'; //平台收取交易费
	
	const TYPE_SYS_CUSTOMER_REF_COMMISSION          ='CUSTOMER_REF_COMMISSION'; //用户介绍关系赚取的手续费
	const TYPE_SYS_BUSINESS_REF_COMMISSION          ='BUSINESS_REF_COMMISSION'; //商家介绍关系赚取的手续费

	public function __construct($from,$to,$transactionOrderId=null){
		$this->user=$from;
		$this->business=$to;

		$this->platform=-10001; //special ID for platform account

		$this->transactionOrderId=$transactionOrderId;

		$this->platformFee=0;
		$this->paymentMethod=self::PAYMENTMETHOD_OFFLINE;

		$this->processer = new mdl_recharge();
	}

	public function setPlatformFee($value){
		$this->platformFee=$value;
	}

	public function setPlatformTotalCommission($value)
	{
		$this->platformTotalCommission=$value;
	}

	public function setAgentTotalCommission($value)
	{
		$this->agentTotalCommission=$value;
	}
	
	public function setAgent($value)
	{
		$this->agent=$value;
	}
	
	// 统一配货中心获得订单后，将结算本金分开至各个供应商
	public function  setSubBusinessTranscationAmount($sub_business_amount_arr) {
		$this->subBusinessTranscationAmount=$sub_business_amount_arr;
	}
	
	
	public function setTransactionAmount($amount){
		$this->transactionAmount=$amount;
	}

	public function useBalancePay($amount){
		if($amount <= 0)return;
		$this->useBalance=$amount;
	}

	public function useGlobalPromotionCode($amount){
		if($amount <= 0)return;
		$this->useGlobalPromotionCode=$amount;
	}

	public function initTransactionSurcharge($rate,$chargeFrom,$payto){
		$this->paymentMethod=self::PAYMENTMETHOD_ONLINE;

		$this->transactionSurcharge = $rate;
		$this->transactionFeeChargeFrom = $chargeFrom;
		$this->transactionRecipient = $payto;
	}

	public function initDeliverFee($fee,$chargeFrom,$payto){
		$this->deliverFee = $fee;
		$this->deliverFeeChargeFrom =$chargeFrom;
		$this->deliverFeePayTo = $payto;
	}

	public function process(){
		
		//var_dump($this->subBusinessTranscationAmount);exit;
		if($this->transactionAmount<0)throw new Exception("error amount to be processed <1", 1);
		
		if($this->transactionOrderId==null)throw new Exception('balance will process based on no order', 1);

		if($this->useBalance>0){
			$this->balanceTransfer($this->useBalance,
									$this->user,
									$this->platform,
									$this->transactionOrderId,
									self::TYPE_SYS_USEMONEYPAY);

			$this->balanceTransfer($this->useBalance,
									$this->platform,
									$this->business,
									$this->transactionOrderId,
									self::TYPE_SYS_MONEYPAY_BALANCETRANSFER);
		}

		if($this->useGlobalPromotionCode>0)
			$this->balanceTransfer($this->useGlobalPromotionCode,
									$this->platform,
									$this->business,
									$this->transactionOrderId,
									self::TYPE_SYS_PROMOTION_CODE_REFOUND);

		if($this->platformFee>0)		
			$this->balanceTransfer( $this->platformFee,
									$this->business,
									$this->platform,
									$this->transactionOrderId,
									self::TYPE_SYS_PLATFORM_FEE);
									
	   // 如果是统配中心，某个订单包含多个供应商 ，则commission进行split to 不同的供应商
	   
	   if($this->subBusinessTranscationAmount) {
		 //  var_dump($this->subBusinessTranscationAmount);//exit;
		       foreach ($this->subBusinessTranscationAmount as $key => $value) {
				   //var_dump( 'sub_commission is '.value['sub_commission']);
				    if($value['sub_commission']>0)
			$this->balanceTransfer( $value['sub_commission'],
								 $value['business_id'],
								$this->platform,
								$this->transactionOrderId,
								self::TYPE_SYS_UBONUS_COMMISSION);
				   
			   }
		   
	   }else{
		   if($this->platformTotalCommission>0)
			$this->balanceTransfer( $this->platformTotalCommission,
								$this->business,
								$this->platform,
								$this->transactionOrderId,
								self::TYPE_SYS_UBONUS_COMMISSION);
	   }

		

		
		if($this->agentTotalCommission>0)
		$this->balanceTransfer( $this->agentTotalCommission,
								$this->platform,
								$this->agent,
								$this->transactionOrderId,
								self::TYPE_SYS_UBONUS_PAY_AGENT_COMMISSION);
		
		
		switch ($this->paymentMethod) {
			case self::PAYMENTMETHOD_OFFLINE:
				$this->_processOfflinePayment();
				break;
			case self::PAYMENTMETHOD_ONLINE:
				$this->_processOnlinePayment();
				break;
			default:
				throw new Exception("Error Processing Request", 1);
				break;
		}
	}

	private function balanceTransfer($amount,$from,$to,$orderID,$type){
		//var_dump('amount is :' . $amount);
		if($amount>0){
			$desc=$this->getTransactionDesc($type);

			$this->processer->addBalanceTo( -$amount, $from, $orderID, $desc ,$type);
			$this->processer->addBalanceTo(  $amount, $to,   $orderID, $desc ,$type);
		}

	}

	private function getTransactionDesc($type)
	{	
		$desc ='';
		switch ($type) {
			case self::TYPE_SYS_USEMONEYPAY://'usemoneypay'                   //用户使用钱包支付;
				$desc='钱包支付';
				break;

			case self::TYPE_SYS_MONEYPAY_BALANCETRANSFER://'usemoneypay'       //钱包支付的商家进账;
				$desc='钱包支付进账';
				break;

			case self::TYPE_SYS_PROMOTION_CODE_REFOUND://'PROMOTION_REFOUND'; //使用全局折扣码，Ubonus返还金额给商家
				$desc='用户使用Ubonus发放的折扣码，平台返还金额给商家';
				break;

			case self::TYPE_SYS_PLATFORM_FEE://'PLATFORM_FEE'; 			   //平台向用户收取手续费
				$desc='Ubonus claim platform fee ';
				break;

			case self::TYPE_SYS_UBONUS_COMMISSION://'UBONUS_COMMISSION';      //平台向商家收取提成
				$desc='平台交易手续费';
				break;
			case self::TYPE_SYS_UBONUS_PAY_AGENT_COMMISSION://'UBONUS_PAY_AGENT_COMMISSION';      //平台向代理商支付佣金
				$desc='平台向运营商支付佣金';
				break;

			case self::TYPE_SYS_TRANSACTION_FEE_PLATFORM_REFOUND://'TRANSACTION_FEE_PLATFORM_REFOUND'; //平台退还交易费
				$desc='平台返还第三方支付交易手续费('.$this->transactionSurcharge*100 . "%)" ;
				break;

			case self::TYPE_SYS_DELIVER_FEE://'DELIVER_FEE';                  //运费
				$desc='运费';
				break;

			case self::TYPE_SYS_TRANSACTION_BALANCE://'TRANSACTION_BALANCE';  //订单交易金额
				$desc='订单交易金额';
				break;
			case self::TYPE_SYS_TRANSACTION_BALANCE_AMEND://'TRANSACTION_BALANCE';  //订单交易金额
				$desc='订单退款金额';
				break;

			case self::TYPE_SYS_TRANSACTION_FEE_PLATFORM_CHARGE://'TRANSACTION_FEE_PLATFORM_CHARGE'; //平台收取交易费
				$desc='平台收取第三方支付交易手续费('.$this->transactionSurcharge*100 . "%)" ;
				break;

			default:
				
				break;


		}

		return $desc;
	}


	private function _processOfflinePayment(){
     // 对于 offline支付 包括使用钱包支付，都需要计算本金给商家，如果是钱进入到Ubonus账户下面。 如果钱进入到客户账户，无需做操作。因此需要offline 有一个默认设置，默认设置为Ubonus收款，如果Ubonus放开收款权限给商家，则这里bu
	 //传输本金给客户
	 $ubonus_received_payment=1; // 表示Ubonus收取本金， 可以在cc_user offline收费下面加一个 ubonus 收取还是商家收取。 这里默认ubonus收取
	 if($ubonus_received_payment){
		  if($this->subBusinessTranscationAmount) { //如果是统一配货
		 //  var_dump($this->subBusinessTranscationAmount);//exit;
		  foreach ($this->subBusinessTranscationAmount as $key => $value) {
			  
			  	$this->balanceTransfer( $value['subtotal'],
									$this->platform,
									$value['business_id'],
									$this->transactionOrderId,
									self::TYPE_SYS_TRANSACTION_BALANCE
									);
			  
		  }
		  
		  
		  }else{
			  
			  
			  	$this->balanceTransfer( $this->transactionAmount,
									$this->platform,
									$this->business,
									$this->transactionOrderId,
									self::TYPE_SYS_TRANSACTION_BALANCE
									);
		  }
		 
	 }
	}

	private function _processOnlinePayment(){

		switch ($this->transactionRecipient) {
			case self::BUSINESS:


				if($this->transactionFeeChargeFrom==self::USER){

				}elseif($this->transactionFeeChargeFrom==self::BUSINESS){

				}elseif($this->transactionFeeChargeFrom==self::PLATFORM){
					$transactionFeePlatformRebate = $this->transactionAmount * $this->transactionSurcharge;

					$this->balanceTransfer( $transactionFeePlatformRebate,
								$this->platform,
								$this->business,
								$this->transactionOrderId,
								self::TYPE_SYS_TRANSACTION_FEE_PLATFORM_REFOUND
								);

				}else{
					throw new Exception("unknow transactionFeeChargeFrom", 1);
				}


				if($this->deliverFee<=0)break;

				if($this->deliverFeeChargeFrom==self::USER){

					if($this->deliverFeePayTo==self::BUSINESS){

					}elseif($this->deliverFeePayTo==self::PLATFORM){

						$this->balanceTransfer( $this->deliverFee,
								$this->business,
								$this->platform,
								$this->transactionOrderId,
								self::TYPE_SYS_DELIVER_FEE
								);

					}else{
						throw new Exception("unknow deliverFeePayTo", 1);
					}

				}elseif($this->deliverFeeChargeFrom==self::BUSINESS){

					if($this->deliverFeePayTo==self::BUSINESS){

					}elseif($this->deliverFeePayTo==self::PLATFORM){

						$this->balanceTransfer( $this->deliverFee,
								$this->business,
								$this->platform,
								$this->transactionOrderId,
								self::TYPE_SYS_DELIVER_FEE
								);

					}else{
						throw new Exception("unknow deliverFeePayTo", 1);
					}

				}elseif($this->deliverFeeChargeFrom==self::PLATFORM){

					if($this->deliverFeePayTo==self::BUSINESS){

						$this->balanceTransfer( $this->deliverFee,
								$this->platform,
								$this->business,
								$this->transactionOrderId,
								self::TYPE_SYS_DELIVER_FEE
								);

					}elseif($this->deliverFeePayTo==self::PLATFORM){

					}else{
						throw new Exception("unknow deliverFeePayTo", 1);
					}

				}else{
					throw new Exception("unknow deliverFeeChargeFrom", 1);
				}


				break;

			case self::PLATFORM:



              
		  if($this->subBusinessTranscationAmount) { //如果是统一配货
		 //  var_dump($this->subBusinessTranscationAmount);//exit;
		  foreach ($this->subBusinessTranscationAmount as $key => $value) {
			  
			  	$this->balanceTransfer( $value['subtotal'],
									$this->platform,
									$value['business_id'],
									$this->transactionOrderId,
									self::TYPE_SYS_TRANSACTION_BALANCE
									);
			  
		  }
		  
		  
		  }else{
			  
			  
			  $this->balanceTransfer( $this->transactionAmount,
									$this->platform,
									$this->business,
									$this->transactionOrderId,
									self::TYPE_SYS_TRANSACTION_BALANCE
									);
		  }
		 
	

				


				if($this->transactionFeeChargeFrom==self::USER){
					
					
                   if($this->subBusinessTranscationAmount) { //如果是统一配货
					
						 /* foreach ($this->subBusinessTranscationAmount as $key => $value) {
							  
								
							$transactionFeePlatformCharge = $value['surchargefee'];

							$this->balanceTransfer( $transactionFeePlatformCharge,
										$this->business,
										$this->platform,
										$this->transactionOrderId,
										self::TYPE_SYS_TRANSACTION_FEE_PLATFORM_CHARGE
										);
							//var_dump($value['surchargefee']);
							  
						  } */
						
							  $transactionFeePlatformCharge = $this->transactionAmount * (1-1/(1+$this->transactionSurcharge));

							$this->balanceTransfer( $transactionFeePlatformCharge,
										$this->business,
										$this->platform,
										$this->transactionOrderId,
										self::TYPE_SYS_TRANSACTION_FEE_PLATFORM_CHARGE
										);
						  
						  }else{
							  
							  $transactionFeePlatformCharge = $this->transactionAmount * (1-1/(1+$this->transactionSurcharge));

							$this->balanceTransfer( $transactionFeePlatformCharge,
										$this->business,
										$this->platform,
										$this->transactionOrderId,
										self::TYPE_SYS_TRANSACTION_FEE_PLATFORM_CHARGE
										);
						  }



					

				}elseif($this->transactionFeeChargeFrom==self::BUSINESS){

					$transactionFeePlatformCharge = $this->transactionAmount * $this->transactionSurcharge;

					$this->balanceTransfer( $transactionFeePlatformCharge,
								$this->business,
								$this->platform,
								$this->transactionOrderId,
								self::TYPE_SYS_TRANSACTION_FEE_PLATFORM_CHARGE
								);

				}elseif($this->transactionFeeChargeFrom==self::PLATFORM){
					

				}else{
					throw new Exception("unknow transactionFeeChargeFrom", 1);
				}


				if($this->deliverFee<=0)break;

				if($this->deliverFeeChargeFrom==self::USER){

					if($this->deliverFeePayTo==self::BUSINESS){

						$this->balanceTransfer( $this->deliverFee,
								$this->platform,
								$this->business,
								$this->transactionOrderId,
								self::TYPE_SYS_DELIVER_FEE
								);

					}elseif($this->deliverFeePayTo==self::PLATFORM){

					}else{
						throw new Exception("unknow deliverFeePayTo", 1);
					}

				}elseif($this->deliverFeeChargeFrom==self::BUSINESS){

					if($this->deliverFeePayTo==self::BUSINESS){

					}elseif($this->deliverFeePayTo==self::PLATFORM){

						$this->balanceTransfer( $this->deliverFee,
								$this->business,
								$this->platform,
								$this->transactionOrderId,
								self::TYPE_SYS_DELIVER_FEE
								);

					}else{
						throw new Exception("unknow deliverFeePayTo", 1);
					}

				}elseif($this->deliverFeeChargeFrom==self::PLATFORM){

					if($this->deliverFeePayTo==self::BUSINESS){

						$this->balanceTransfer( $this->deliverFee,
								$this->platform,
								$this->business,
								$this->transactionOrderId,
								self::TYPE_SYS_DELIVER_FEE
								);

					}elseif($this->deliverFeePayTo==self::PLATFORM){

					}else{
						throw new Exception("unknow deliverFeePayTo", 1);
					}

				}else{
					throw new Exception("unknow deliverFeeChargeFrom", 1);
				}

				break;

			default:
				throw new Exception("unknow transactionRecipient", 1);
				break;
		}
	}



}

?>