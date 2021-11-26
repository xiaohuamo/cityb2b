<?php 

	class ctl_daigou extends cmsPage
	{
		
		//代购页面
        public function daigou_action()
        {
            if($this->getUserDevice()!='desktop')
            {
                $this->mobile_data();
                $this->display('daigou/mobile/index');
            }
            else
            {
                $this->index_data();
                $this->display('daigou/index');
            }
        }


        public function index_data()
        {
            $data_List=$this->get_data(' categoryid like "%,106105118%" ');
            $this->setData($data_List,'data_List');
        }

        function  mobile_data()
        {
            $data_List=$this->get_data(' categoryid like "%,106105118%" ',' limit 4');
            $this->setData($data_List,'data_List');
        }


         /*
         * get_data：查询数据库返回对象公共方法
         * $strwhere：查询条件商家ID
         * */
        private function get_data($strwhere,$count=' limit 12 ')
        {
            $where= array();
            $where['isApproved'] = 1;
            $where['status'] = 4;
            $currentTime=time();
            $where[] = " !(autoOffline=1 AND ('$currentTime'<startTime or '$currentTime'>endTime)) ";
            $where[]=$strwhere;
            $mdl_coupons= $this->loadModel("coupons");
            $Sql= $mdl_coupons->getListSql( array( 'id', 'title','pic','createUserId', 'createTime', 'bonusType', 'hits', 'buy', 'coupon_summery_description', 'sales_user_list','businessName','voucher_deal_amount','voucher_original_amount'), $where, " createTime desc ").$count;
            return $mdl_coupons->getListBySql($Sql);
        }

	}


