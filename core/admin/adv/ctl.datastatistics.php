<?php

/*
 @ctl_name = 数据统计@
*/

class ctl_datastatistics extends adminPage
{
    public function index_action ($businessID='') #act_name = 列表#
    {
        $mdl_user	= $this->loadModel('user');
        $todaydate=time();

        $beforedata7 = strtotime(date("Y-m-d", strtotime('-7 days')));
        $this->setData($mdl_user->getListBySql("SELECT count(1) FROM cc_user WHERE createdDate>=$beforedata7 AND createdDate<=$todaydate")[0][0], 'date7');
        $this->setData($mdl_user->getListBySql("SELECT SUM(voucher_deal_amount),bonus_id FROM cc_wj_customer_coupon WHERE gen_date>=$beforedata7 AND gen_date<=$todaydate")[0][0], 'salemoney7');
        $this->setData($mdl_user->getListBySql("SELECT SUM(customer_buying_quantity) AS sumcount,`bonus_title` FROM cc_wj_customer_coupon WHERE   gen_date>=$beforedata7 AND gen_date<=$todaydate GROUP BY bonus_id ORDER BY sumcount DESC LIMIT 100"), 'hotsale7');

        $beforedata15 = strtotime(date("Y-m-d", strtotime('-15 days')));
        $this->setData($mdl_user->getListBySql("SELECT count(1) FROM cc_user WHERE createdDate>=$beforedata15 AND createdDate<=$todaydate")[0][0], 'date15');
        $this->setData($mdl_user->getListBySql("SELECT SUM(voucher_deal_amount),bonus_id FROM cc_wj_customer_coupon WHERE gen_date>=$beforedata15 AND gen_date<=$todaydate")[0][0], 'salemoney15');
        $this->setData($mdl_user->getListBySql("SELECT SUM(customer_buying_quantity) AS sumcount,`bonus_title` FROM cc_wj_customer_coupon WHERE   gen_date>=$beforedata15 AND gen_date<=$todaydate GROUP BY bonus_id ORDER BY sumcount DESC LIMIT 100"), 'hotsale15');

        $beforedata30 = strtotime(date("Y-m-d", strtotime('-30 days')));
        $this->setData($mdl_user->getListBySql("SELECT count(1) FROM cc_user WHERE createdDate>=$beforedata30 AND createdDate<=$todaydate")[0][0], 'date30');
        $this->setData($mdl_user->getListBySql("SELECT SUM(voucher_deal_amount),bonus_id FROM cc_wj_customer_coupon WHERE gen_date>=$beforedata30 AND gen_date<=$todaydate")[0][0], 'salemoney30');
        $this->setData($mdl_user->getListBySql("SELECT SUM(customer_buying_quantity) AS sumcount,`bonus_title` FROM cc_wj_customer_coupon WHERE gen_date>=$beforedata30 AND gen_date<=$todaydate GROUP BY bonus_id ORDER BY sumcount DESC LIMIT 100"), 'hotsale30');

        $this->setData($mdl_user->getListBySql("SELECT count(1) FROM cc_user")[0][0], 'dateall');
        $this->setData($mdl_user->getListBySql("SELECT SUM(voucher_deal_amount),bonus_id FROM cc_wj_customer_coupon ")[0][0], 'salemoneyall');
        $this->setData($mdl_user->getListBySql("SELECT SUM(customer_buying_quantity) AS sumcount,`bonus_title` FROM cc_wj_customer_coupon   GROUP BY bonus_id ORDER BY sumcount DESC LIMIT 20"), 'hotsaleall');
        $this->display();
    }
}

?>