<?php 
/**
 * 	拼多多拼团模式
 * 	入团时必须购买
 * 	成团后发货
 * 	失败后退款
 *
 * 	记录用户参加团的模组
 */
class mdl_group_pin_user_group extends mdl_base{
	protected $tableName = '#@_group_pin_user_group';

	/**
	 * 用户团的ID
	 * @var [type]
	 */
	private $user_group_id;

	/**
	 * 父团ID
	 * @var [type]
	 */
	
	private $group_id;
	

	/**
	 * 创建时间
	 * @var int 11
	 */	
	private $gen_date;

	private $status;
	const STATUS_OPEN=0;
	const STATUS_COMPLETE=1;
	const STATUS_EXPIRE=2;
	const STATUS_DELETE=3;


}



?>