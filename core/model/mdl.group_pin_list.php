<?php 
/**
 * 	拼多多拼团模式
 * 	入团时必须购买
 * 	成团后发货
 * 	失败后退款
 *
 * 	记录用户参加团的模组
 */
class mdl_group_pin_list extends mdl_base{
	protected $tableName = '#@_group_pin_list';

	private $id;

	/**
	 * 用户团的ID
	 * @var [type]
	 */
	private $user_group_id;


	/**
	 * 用户ID
	 * @var [type]
	 */
	private $user_id;


    /**
	 * order ID
	 * @var [type]
	 */
	private $order_id;


	/**
	 * 创建时间
	 * @var int 11
	 */	
	private $gen_date;

 

}



?>