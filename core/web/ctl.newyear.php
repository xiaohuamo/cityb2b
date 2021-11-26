<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/3 0003
 * Time: 上午 11:37
 */
class ctl_newyear extends cmsPage
{
    public function index_action()
    {
        $currentTime=strtotime ('now');

        $list55=array();
        $list56=array();
        $list57=array();
        $list58=array();
        $list59=array();
        $list60=array();
        $list61=array();
        $list62=array();
        $list63=array();
        $list64=array();
        $list65=array();
        $list66=array();

        $list=$this->get_data1("SELECT c.createUserId,u.logo,e.isStore,c.createUserId,c.id, c.title, c.pic, c.hits, c.voucher_deal_amount,c.voucher_original_amount,c.categoryId,e.panaltype,c.city FROM cc_explosion e LEFT JOIN cc_coupons c ON c.id=e.couponid LEFT JOIN cc_user u ON u.id=c.createUserId WHERE c.isapproved=1 and c.status=4 AND e.pagetype=2 AND !(c.autoOffline=1 AND ('$currentTime'<c.startTime or '$currentTime'>c.endTime)) ORDER BY sort");
        foreach ( $list as $key => $val ) {
            switch ($list[$key]['panaltype'])
            {
                case 55:
                    array_push($list55,$list[$key]);
                    break;
                case 56:
                    array_push($list56,$list[$key]);
                    break;
                case 57:
                    array_push($list57,$list[$key]);
                    break;
                case 58;
                    array_push($list58,$list[$key]);
                    break;
                case 59:
                    array_push($list59,$list[$key]);
                    break;
                case 60:
                    array_push($list60,$list[$key]);
                    break;
                case 61:
                    array_push($list61,$list[$key]);
                    break;
                case 62:
                    array_push($list62,$list[$key]);
                    break;
                case 63:
                    array_push($list63,$list[$key]);
                    break;
                case 64:
                    array_push($list64,$list[$key]);
                    break;
                case 65:
                    array_push($list65,$list[$key]);
                    break;
                case 66:
                    array_push($list66,$list[$key]);
                    break;
            }
        }
                    $this->setData($list55,'list55');

                    $this->setData($list56,'list56');

                    $this->setData($list57,'list57');

                    $this->setData($list58,'list58');

                    $this->setData($list59,'list59');

                    $this->setData($list60,'list60');

                    $this->setData($list61,'list61');

                    $this->setData($list62,'list62');

                    $this->setData($list63,'list63');

                    $this->setData($list64,'list64');

                    $this->setData($list65,'list65');

                    $this->setData($list66,'list66');

        $this->setData($this->loadModel('info')->getListByClass('107',10,'ordinal'),'bannerData');
        $this->inManagementCoupon();
    }


    public function inManagementCoupon()
    {
        $buy_List=$this->get_data1("select c.id,c.createUserId,c.title,c.pic,c.hits,c.voucher_original_amount,c.voucher_deal_amount,c.categoryId,c.qty,c.buy,c.bonusType from cc_coupons  as c left join cc_coupon_event_management as ec on c.id = ec.coupon_id where c.isApproved=1 and c.status=4  AND c.isInManagement = 1 and ec.status = 2 ORDER BY ((c.voucher_original_amount-c.voucher_deal_amount)/c.voucher_original_amount) desc ");


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

    private function get_data1($sql)
    {
        $mdl_coupons= $this->loadModel("coupons");
        return $mdl_coupons->getListBySql($sql);
    }

    public function newyear_action()
    {
        if($this->getUserDevice()!='desktop')
        {
            //wx share
            /*
            require_once "wx/wxjssdk.php";
            $jssdk = new WXjsSDK();
            $signPackage = $jssdk->GetSignPackage();
            $this->setData($signPackage,'signPackage');
            **/
            $this->setData( '购物狂欢季', 'pageTitle' );
            $this->index_action();
            $this->display('mobile/newyear/index');
        }
        else
        {
            $this->setData( '购物狂欢季', 'pageTitle' );
            $this->index_action();
            $this->display('newyear/index');
        }
    }
}