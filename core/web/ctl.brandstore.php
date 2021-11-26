<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/19 0019
 * Time: 上午 11:24
 */
class ctl_brandstore extends cmsPage
{
    public function index_action()
    {
        $id = trim(get2('id'))==''?' 1=1':'e.alias='.trim(get2('id'));
        $lang=$this->getLangStr();
        $type=$this->get_data("SELECT DISTINCT(e.alias),c.name FROM cc_explosion e LEFT JOIN cc_infoclass c ON c.alias=e.alias WHERE e.panaltype=48 and e.pageType=7 and c.lang='$lang' ORDER BY sort");

        $this->setData($type,'type');
        $list=$this->get_data("SELECT u.logo,e.isStore,c.createUserId,c.id, c.title, c.pic, c.hits, c.voucher_deal_amount,c.voucher_original_amount,c.categoryId,e.panaltype,c.city,u.businessname FROM cc_explosion e LEFT JOIN cc_coupons c ON c.id=e.couponid LEFT JOIN cc_user u ON u.id=c.createUserId WHERE e.panaltype=48 and e.pageType=7 ORDER BY sort ");
        
        $this->setData($list,'data');

        if($this->getUserDevice()!='desktop')
        {
            $this->display('mobile/storeNavigation/index');
        }
        else
        {
            $this->display('brandstore/index');
        }
    }

    private function get_data($sql)
    {
        $mdl_coupons= $this->loadModel("coupons");
        return $mdl_coupons->getListBySql($sql);
    }
}