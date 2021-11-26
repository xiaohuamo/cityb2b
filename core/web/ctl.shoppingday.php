<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/3 0003
 * Time: 上午 11:37
 */
class ctl_shoppingday extends cmsPage
{
    public function index_action()
    {
        $bonus=$this->get_data("SELECT * FROM cc_lottery WHERE is_approved=1 and status=1 ORDER BY id DESC limit 8");//抽奖
        $this->setData($bonus,'bonus');

        $this->setData($this->loadModel('info')->getListByClass('107',10,'ordinal'),'bannerData');

        $this->inManagementCoupon();

        $currentTime=strtotime ('now');

        //超值拼团
        $mdl_group_pin=$this->loadModel('group_pin');
        $sql = "Select c.title,c.pic,c.voucher_deal_amount,c.voucher_original_amount, gp.coupon_id,gp.group_size_each,gp.group_size_total,gp.reward_type,gp.reward_value from cc_group_pin as gp left join cc_coupons as c on c.id = gp.coupon_id where c.isApproved=1 and c.status=4 AND !(c.autoOffline=1 AND ('$currentTime'<c.startTime or '$currentTime'>c.endTime)) ORDER BY gen_date DESC  limit 12";
        $group_pin_list=$mdl_group_pin->getListBySql($sql);
        $this->setData($group_pin_list, 'GroupPurchase');


        $list=$this->get_data("SELECT c.createUserId,u.logo,e.isStore,c.createUserId,c.id, c.title, c.pic, c.hits, c.voucher_deal_amount,c.voucher_original_amount,c.categoryId,e.panaltype,c.city FROM cc_explosion e LEFT JOIN cc_coupons c ON c.id=e.couponid LEFT JOIN cc_user u ON u.id=c.createUserId WHERE c.isapproved=1 and c.status=4 AND e.pagetype=2 AND !(c.autoOffline=1 AND ('$currentTime'<c.startTime or '$currentTime'>c.endTime)) ORDER BY sort");


        $list5=array();
        $list6=array();
        $list7=array();
        $list36=array();
        $list37=array();
        $list38=array();
        $list39=array();
        $list40=array();
        $list41=array();
        $list45=array();
        $list47=array();
        $list49=array();
        $list50=array();

        foreach ( $list as $key => $val ) {
            switch ($list[$key]['panaltype'])
            {
                case 5:
                    array_push($list5,$list[$key]);
                    break;
                case 6:
                    array_push($list6,$list[$key]);
                    break;
                case 7:
                    array_push($list7,$list[$key]);
                    break;
                case 36;
                    array_push($list36,$list[$key]);
                    break;
                case 37:
                    array_push($list37,$list[$key]);
                    break;
                case 38:
                    array_push($list38,$list[$key]);
                    break;
                case 39:
                    array_push($list39,$list[$key]);
                    break;
                case 40:
                    array_push($list40,$list[$key]);
                    break;
                case 41:
                    array_push($list41,$list[$key]);
                    break;
                case 45:
                    array_push($list45,$list[$key]);
                    break;
                case 47:
                    array_push($list47,$list[$key]);
                    break;
                case 49:
                    array_push($list49,$list[$key]);
                    break;
                case 50:
                    array_push($list50,$list[$key]);
                    break;
            }
        }
        $this->setData($list5,'list5');

        $this->setData($list6,'list6');

        $this->setData($list7,'list7');

        $this->setData($list36,'list36');

        $this->setData($list37,'list37');

        $this->setData($list38,'list38');

        $this->setData($list39,'list39');

        $this->setData($list40,'list40');

        $this->setData($list41,'list41');

        $this->setData($list45,'list45');

        $this->setData($list47,'list47');

        $this->setData($list49,'list49');

        $this->setData($list50,'list50');
    }


    public function inManagementCoupon()
    {

        $buy_List=$this->get_data("select c.id,c.createUserId,c.title,c.pic,c.hits,c.voucher_original_amount,c.voucher_deal_amount,c.categoryId,c.qty,c.buy,c.bonusType from cc_coupons  as c left join cc_coupon_event_management as ec on c.id = ec.coupon_id where c.isApproved=1 and c.status=4  AND c.isInManagement = 1 and ec.status = 2 ORDER BY ((c.voucher_original_amount-c.voucher_deal_amount)/c.voucher_original_amount) desc ");


        $mdl_coupons_sub=$this->loadModel('coupons_sub');
        $mdl_shop_stock=$this->loadModel('shop_stock');
        $mdl_shop_guige=$this->loadModel('shop_guige');

        foreach ($buy_List as $key =>$value) {
            $coupon_id = $value['id'];

            $totalQty= intval($value['qty']);

            $sub_coupons = $mdl_coupons_sub->getList('sum(quantity) as totalQty, sum(buy) as totalBuy',array('parent_coupon_id'=>$coupon_id));

            $totalQty += intval($sub_coupons[0]['totalQty']);

            
            if($mdl_shop_guige->couponHasGuige($value['id'])&&$value['bonusType']==9){
                 $stock=$mdl_shop_stock->getList('sum(qty) as totalQty, sum(buy) as totalBuy',array('couponId'=>$coupon_id));
                $totalQty=intval($stock[0]['totalQty']);
            }

            $buy_List[$key]['totalQty']=$totalQty;

            // echo "coupon = ".$value['id']."  qty = ".$value['qty']." sub = " .$sub_coupons[0]['totalQty']." stock =".$stock[0]['totalQty']."total=".$totalQty."<br>";
        }

         $this->setData($buy_List,'buy_List');

    }

    private function get_data($sql)
    {
        $mdl_coupons= $this->loadModel("coupons");
        return $mdl_coupons->getListBySql($sql);
    }

    public function shoppingday_action()
    {
        if($this->getUserDevice()!='desktop')
        {
            //wx share
            require_once "wx/wxjssdk.php";
            $jssdk = new WXjsSDK();
            $signPackage = $jssdk->GetSignPackage();
            $this->setData($signPackage,'signPackage');

            $this->setData( '圣诞购物狂欢季', 'pageTitle' );

            $this->index_action();
            $this->display('mobile/shoppingday/index');
        }
        else
        {
            $this->setData( '圣诞购物狂欢季', 'pageTitle' );
            $this->index_action();
            $this->display('shoppingday/index');
        }
    }

}