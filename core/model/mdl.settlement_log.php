<?php

class mdl_settlement_log extends mdl_base
{
    protected $tableName = '#@_settlement_log';

    private $user_id;

    private $settle_from;

    private $settle_to;

    private $settle_order_status;

    private $settle_amount;

    private $operation_type;

    const OPERATION_TYPE_MANUAL = 'manual'; //手动结算

    const OPERATION_TYPE_AUTO = 'auto';   //系统自动结算

    private $gen_date;

    public function owner($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function settleFrom($from)
    {
        $this->settle_from = $from;

        return $this;
    }

    public function settleTo($to)
    {
        $this->settle_to = $to;

        return $this;
    }

    public function settleOrderStatus($status)
    {
        $this->settle_order_status = $status;

        return $this;
    }

    public function settleAmount($amount)
    {
        $this->settle_amount = $amount;

        return $this;
    }

    public function operationType($type)
    {
        $this->operation_type = $type;

        return $this;
    }

    public function log()
    {
        $data = [];
        $data['user_id'] = $this->user_id;
        $data['settle_from'] = $this->settle_from;
        $data['settle_to'] = $this->settle_to;
        $data['settle_order_status'] = $this->settle_order_status;
        $data['settle_amount'] = $this->settle_amount;
        $data['operation_type'] = $this->operation_type;
        $data['order_list'] = $this->getOrderList();
        $data['gen_date'] = time();

        return $this->insert($data);
    }

    public function lastSettlementPoint()
    {
        $where['user_id'] = $this->user_id;
        $data = $this->getList(null, $where, 'id desc', 1);

        return $data[0];
    }

    /**
     * 订单结算的同时 记录此次结算的所有orderId
     *
     */
    public function getOrderList()
    {
        $mdl_recharge = loadModel('recharge');

        $bid = $this->user_id;;
        $time1 = strtotime($this->settle_from);
        $time2 = strtotime($this->settle_to);
        $type = $this->settle_order_status;

        $businessSettleableRecord = $mdl_recharge->businessSettleableRecord();
        $inStr_businessSettleableRecord = "'".join("','", $businessSettleableRecord)."'";
        $where_str = "r.payment in ($inStr_businessSettleableRecord) ";

        $sql = "SELECT distinct r.orderId  from cc_recharge as r left join cc_order as o on r.orderId = o.orderId left join cc_wj_user_coupon_activity_log as l on o.orderId = l.orderId where r.userId =$bid and o.business_userId =$bid and l.action_id=o.coupon_status and l.gen_date>=$time1 and l.gen_date<=$time2 and o.coupon_status='$type' and $where_str";

        $list = $mdl_recharge->getListBySql($sql);

        $data = [];
        foreach ($list as $l) {
            $data[] = $l['orderId'];
        }

        return join(',', $data);
    }
}

?>