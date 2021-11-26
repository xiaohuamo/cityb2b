<?php

class mdl_wj_user_coupon_activity_log extends mdl_base
{
    /**
     * 基于订单动作 记录
     *
     * 功能完成后可以 更名为order_activity_log,并清理无用字段
     */
    protected $tableName = '#@_wj_user_coupon_activity_log';

    private $orderId;

    private $action_user_id;

    private $action_user_name;

    private $action_id;

    private $gen_date;

    public function orderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function userId($user_id)
    {
        $this->action_user_id = $user_id;

        return $this;
    }

    public function userName($user_name)
    {
        $this->action_user_name = $user_name;

        return $this;
    }

    public function actionId($action_id)
    {
        $this->action_id = $action_id;

        return $this;
    }

    public function log()
    {
        $data = [];
        $data['orderId'] = $this->orderId;
        $data['action_user_id'] = $this->action_user_id;
        $data['action_user_name'] = $this->action_user_name;
        $data['action_id'] = $this->action_id;

        $data['gen_date'] = time();

        return $this->insert($data);
    }

    public function getLogList() {
        return $this->getList(['action_id', 'gen_date'], ['orderId' => $this->orderId]);
    }
}

?>