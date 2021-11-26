<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/3 0003
 * Time: 上午 10:34
 */

class ctl_local_service extends cmsPage
{
    public function index_action()
    {
        $this->setData($this->loadModel('info')->getListByClass('110',10,'ordinal'),'bannerData');
       

        $this->setData($this->get_explosion_data(19),'data19');

        $this->setData($this->get_explosion_data(20),'data20');

        $this->setData($this->get_explosion_data(21),'data21');

        $this->setData($this->get_explosion_data(22),'data22');

        $this->setData($this->get_explosion_data(23),'data23');

        $this->setData($this->get_explosion_data(24),'data24');
       
	     $this->setData('墨尔本 本地服务 | 墨尔本 美容 按摩 ','h1_footer');
    }

    /*
     * get_data：查询数据库返回对象公共方法
     * $strwhere：查询条件商家ID
     * */
    private function get_explosion_data($panaltype)
    {   
        $currentTime=strtotime ('now');

        $mdl_coupons= $this->loadModel("coupons");
        $sql="SELECT u.logo,e.isStore,c.createUserId,c.id, c.title, c.pic, c.hits, c.voucher_deal_amount,c.voucher_original_amount,c.categoryId,e.panaltype FROM cc_explosion e LEFT JOIN cc_coupons c ON c.id=e.couponid LEFT JOIN cc_user u ON u.id=c.createUserId WHERE e.panaltype='$panaltype' AND c.isapproved=1 and c.EvoucherOrrealproduct <> 'restaurant_menu' and c.status=4 AND !(c.autoOffline=1 AND ('$currentTime'<c.startTime or '$currentTime'>c.endTime)) ORDER BY sort LIMIT 6";

        return $mdl_coupons->getListBySql($sql);
    }

    public function local_service_action()
    {
        if($this->getUserDevice()!='desktop')
        {
            $this->index_action();
            $this->display('localService/mobile/index');
        }
        else
        {
            $this->index_action();
            $this->display('localService/index');
        }
    }

}