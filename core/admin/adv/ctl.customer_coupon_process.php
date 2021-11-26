<?php

/*
 @ctl_name = 客户订单管理@
*/

class ctl_customer_coupon_process extends adminPage
{
    function index_action() #act_name = 列表#
    {
        $sk = trim(get2('sk'));
        $status = trim(get2('status'));
        $payment = trim(get2('payment'));

        $onlyNotApproved = limitInt(get2('onlyNotApproved'), 0, 1);
        $mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');

        $order = " gen_date desc ";

        if ($status == null) {
            $status = 5;
        }
        if (! empty($sk)) {
            $where = " where orderId LIKE '%$sk%'  or ( money > $sk-0.1 and money<$sk+0.1) OR phone LIKE '%$sk%' OR tracking_id LIKE '%$sk%' OR customer_firstname like '%$sk%' OR customer_lastname like '%$sk%' OR customer_name like '%$sk%' OR bonus_title like '%$sk%'";
        }
        //var_dump($status);

        if (! empty($sk)) {
            $where .= " and status =$status";
        } else {
            if ($status == 5) {
            } else {
                $where .= " where status =$status";
            }
        }

        if ($this->user['role'] == 6 || $this->user['role'] == 11) {
            if (! empty($where)) {
                //有查询条件
            } else {
                $where = " where cus.business_id in (select id from cc_user where user_belong_to_agent =".$_SESSION['admin_user_id'].")";
            }
        }

        $search = [

            'status' => $status,

        ];

        $pageSql = "SELECT (select min(a.id)  from cc_order a where a.userId =ord.userId ) as first_id,cus.*,ord.id as idd ,ord.house_number,ord.coupon_status,ord.logistic_delivery_date,ord.logisitic_schedule_time,ord.payment,ord.money,ord.address,ord.status,ord.first_name,ord.last_name,ord.phone FROM cc_wj_customer_coupon AS cus LEFT JOIN cc_order AS ord ON cus.order_id=ord.orderId".$where." order by ".$order;
        $pageUrl = $this->parseUrl()->set('page');
        $pageSize = 60;
        $maxPage = 50;
        $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
        $data = $mdl_wj_customer_coupon->getListBySql($page['outSql']);

        $this->setData($data, 'data');
        $this->setData( $this->loadModel('order')->isOrderEditable($sk), 'editAble');
        $this->setData($onlyNotApproved, 'onlyNotApproved');

        $this->setData($page['pageStr'], 'pager');
        $this->setData($this->parseUrl()->set('act', 'detail'), 'viewUrl');
        $this->setData($this->parseUrl()->set('sk')->set('status')->set('page'), 'searchUrl');
        $this->setData($this->parseUrl()->set('deleteId')->set('id'), 'delUrl');
        $this->setData($search, 'search');
        $this->setData($this->parseUrl(), 'refreshUrl');

        $this->display();
    }

    function detail_action() #act_name = 详情#
    {

        $id = trim(get2('id'));
        $mdl_order = $this->loadModel('order');
        $data = $mdl_order->getByOrderId($id);
        $type = get2('type');
        if (! $data) {
            $this->sheader(null, '记录不存在');
        }

        $mdl_coupons = $this->loadModel('coupons');

        $activity_log = $this->loadModel('wj_user_coupon_activity_log')->getList(null, ['orderId' => $id]);
        foreach ($activity_log as $k => $l) {
            $activity_log[$k]['cn_description'] = $mdl_coupons->actionlist_info($l['action_id']);
        }
        $this->setData($activity_log, 'log');

        $items = $this->loadModel('wj_customer_coupon')->getList(null, ['order_id' => $id]);

        $this->setData($items, 'items');

        $this->setData($data, 'data');

        if ($type == 'warning1') {
            $this->setData($this->parseUrl()->set('ctl', 'adv/operation_monitor')->set('act', 'warning1'), 'listUrl');
        } else {
            if ($type == 'warning2') {
                $this->setData($this->parseUrl()->set('ctl', 'adv/operation_monitor')->set('act', 'warning2'), 'listUrl');
            } else {
                $this->setData($this->parseUrl()->set('act')->set('id'), 'listUrl');
            }
        }

        $this->setData($this->parseUrl()->set('act', 'cancel')->set('id', $id), 'cancelUrl');

        $this->setData($this->parseUrl()->set('ctl', 'adv/hcash_record')->set('act', 'index')->set('keyword'), 'hcashUrl');

        $this->setData($this->parseUrl()->set('ctl', 'adv/company')->set('act', 'edit')->set('keyword'), 'businessUrl');

        $this->setData($this->loadModel('user')->getBusinessDisplayName($data['business_userId']), 'businessName');

        $this->display();
    }

    function cancel_action() #act_name = 取消订单#
    {
        //	var_dump($this->parseUrl()->set( 'act','detail')->set('id',$id));exit;

        $id = trim(get2('id'));

        if ($id > 0) {
            $result = $this->cancel_customer_coupon('cancelBySystem', $id);
        }

        $this->sheader($this->parseUrl()->set('act', 'detail')->set('id', $id));
    }
}

?>