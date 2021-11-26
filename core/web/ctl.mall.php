<?php

class ctl_mall extends cmsPage
{
    public function index_action()
    {
        $this->setData($this->loadModel('info')->getListByClass('107',10,'ordinal'),'bannerData');
        $currentTime=strtotime ('now');
        $list=$this->get_data("SELECT u.logo,e.isStore,c.createUserId,c.id, c.title, c.pic, c.hits, c.voucher_deal_amount,c.voucher_original_amount,c.categoryId,e.panaltype FROM cc_explosion e LEFT JOIN cc_coupons c ON c.id=e.couponid LEFT JOIN cc_user u ON u.id=c.createUserId WHERE e.pagetype=3 AND c.isapproved=1 and c.status=4 AND !(c.autoOffline=1 AND ('$currentTime'<c.startTime or '$currentTime'>c.endTime)) ORDER BY sort");
        $this->setData($list,'data');
    }

    private function get_data($sql)
    {
        $mdl_coupons= $this->loadModel("coupons");
        return $mdl_coupons->getListBySql($sql);
    }

    public function mall_action()
    {
        if($this->getUserDevice()!='desktop')
        {
            $this->index_action();
            $this->display('mobile/mall/index');
        }
        else
        {
            $this->index_action();
            $this->display('mall/index');
        }
    }
}
