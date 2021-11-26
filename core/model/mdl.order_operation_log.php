<?php
/**
 * 记录订单没查看的时间点，用于监控商家是否及时处理了订单
 */
class mdl_order_operation_log extends mdl_base
{
	protected $tableName = '#@_order_operation_log';

	private $orderId;
	private $userId;
	private $type;

	const VIEW='view';
	const PROCESS='process';

	function __construct()
	{	
		parent::__construct();

		$this->type = self::VIEW; //default value;
	}

	/**
	 * Required
	 */
	public function order($orderId)
	{
		$this->orderId = $orderId;
		return $this;
	}

	/**
	 * optional
	 */
	public function view()
	{
		$this->type = self::VIEW;
		return $this;
	}

	/**
	 * optional
	 */
	public function process()
	{
		$this->type = self::PROCESS;
		return $this;
	}

	/**
	 * optional
	 */
	public function by($userId)
	{
		$this->userId = $userId;
		return $this;
	}

	/**
	 * insert
	 */
	public function log()
	{
		$data             =array();
		$data['order_id']  =$this->orderId;
		$data['user_id']   =$this->userId;
		$data['type']     =$this->type;
		$data['gen_date'] =time();

		return $this->insert($data);
	}

}

