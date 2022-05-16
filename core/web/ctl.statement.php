<?php

class ctl_statement extends cmsPage
{
    function ctl_statement()
    {

        parent::cmsPage();



        $act = $GLOBALS['gbl_act'];

        if ($act == 'customer_coupon_approving' || $act == 'customer_order_detail') {

        } else {

            if (! $this->loginUser) {
                $this->sheader(HTTP_ROOT_WWW.'member/login?returnUrl='.urlencode($_SERVER['REQUEST_URI']));
            }
        }


    }


}