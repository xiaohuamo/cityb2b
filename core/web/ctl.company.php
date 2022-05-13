<?php

class ctl_company extends cmsPage
{


    function ctl_company()
    {

        parent::cmsPage();

        $this->setData('company', 'footer_menu'); //old version mobile
        $this->setData( 'dashboard', 'mobile_menu' ); //new version mobile










        // 获取该用户是否为餐馆信息
		$mdl_restaurant =$this->loadModel('restaurant_info');
		$restaurant =$mdl_restaurant->getByWhere(array('userId'=>$this->loginUser['id']));
		$this->setData($restaurant,'restaurant');
		
		// 获取用户当前是哪个版本  ==2 默认  =1 可选  isnull or 0 为未选
		// 商家类型包括: 商城版/服务版/餐厅版/媒体网红版
		
		
		 $mdl = $this->loadModel('authrise_manage_other_business_account');
         $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);
		
		 $this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');
		
		
		
    }
	
	 function  	choose_business_type_action(){
		   if(is_post()) {
			   
			   $business_type=post('business_type');
			   $data=array(
			      'business_type_restaurant'=>0,
				  'business_type_media'=>0,
				  'business_type_service'=>0,
				  'business_type_shop'=>0,
				  'business_type_freshfood'=>0,
				  'business_type_factory'=>0,
				   'business_type_factory_2c'=>0,
			   );
			   
			   if($business_type=='business_type_restaurant')  $data['business_type_restaurant']=1;
			   if($business_type=='business_type_media')  $data['business_type_media']=1;
			   if($business_type=='business_type_service')  $data['business_type_service']=1;
			   if($business_type=='business_type_shop')  $data['business_type_shop']=1;
			   if($business_type=='business_type_freshfood')  $data['business_type_freshfood']=1;
			     if($business_type=='business_type_factory')  $data['business_type_factory']=1;
				  if($business_type=='business_type_factory_2c')  $data['business_type_factory_2c']=1;
			   
			   if( $this->loadModel('user')->update($data,$this->loginUser['id'])){
    			   if($business_type=='business_type_restaurant')  $this->loginUser['business_type_restaurant']=1;
    			   if($business_type=='business_type_media')  $this->loginUser['business_type_media']=1;
    			   if($business_type=='business_type_service')  $this->loginUser['business_type_service']=1;
    			   if($business_type=='business_type_shop')  $this->loginUser['business_type_shop']=1;
				   if($business_type=='business_type_freshfood')  $this->loginUser['business_type_freshfood']=1;
				   if($business_type=='business_type_factory')  $this->loginUser['business_type_factory']=1;
				   if($business_type=='business_type_factory_2c')  $this->loginUser['business_type_factory_2c']=1;
			   }
                if($business_type=='business_type_factory') {
					  $this->form_response(200, '保存成功', HTTP_ROOT_WWW . 'factory/index');
					
				}elseif($business_type=='business_type_factory_2c') {
					  $this->form_response(200, '保存成功', HTTP_ROOT_WWW . 'factory_2c/index');
					
				}else{
					  $this->form_response(200, '保存成功', HTTP_ROOT_WWW . 'company/index');
				}
			  
		   }else{
			   
			    $this->setData(get2('fullscreen'),'fullscreen');
				
			    $this->setData($this->parseUrl,'postUrl');
                $this->setData('切换店铺类型 - ' . $this->site['pageTitle'], 'pageTitle');
				$this->setData('choose_business_type', 'menu');
				if(get2('freshfood')) {
					$this->setData('advanced_setting', 'menu');
					$this->setData('choose_business_type', 'submenu');
				}
				
				$this->display('company/choose_business_type');
				
		       
		   }		
	}
	
	
	
	

	

  
	
    function business_start_guide_action()
    {
        $this->setData('新手导航', 'pagename');
        $this->setData('index', 'menu');
        $this->setData('商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
        $this->display('company/business_start_guide');
    }

    function index_action()
    {




        //如果当前已agent方式登陆，则强制转换为agent登陆方式
        $this->AgentActiveCheck($this->loginUser['id'],$this->cookie->getCookie('agentcityb2b'));
		
		//将可能存在的 groupmanager ，用户端的 管理多家分店管理员的cookie清掉，因为这个cookie的优先级比 agenta 高，有的底，不是一致的。
		$this->cookie->setCookie( 'groupManager', '');

        // 获取公司一段时间内的销售额,30天
        $mdl_order = $this->loadModel('order');
        $totalSales = $mdl_order->getTotalSales($this->loginUser['id'], 30);
        $this->setData($totalSales, 'totalSales');

		
		
        // 获取公司账户的余额
        $mdl_recharge = $this->loadModel('recharge');
        $balance = $mdl_recharge->getBalance($this->loginUser['id']);
        $this->setData($balance, 'balance');

        $this->setData($this->loadModel('user')->getBusinessDisplayName($this->loginUser['id']), 'businessDisplayName');

        $this->setData('商家首页', 'pagename');
        $this->setData('index', 'menu');
        $this->setData('商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
		// var_dump($this->current_business['id'] . 'type id is'.$this->current_business['business_type_factory']);exit;
		if($this->current_business['business_type_factory']==1) {

            $pclogin =trim(get2('pclogin')) ;

            //get role of the login user ,such as sales , operator , dispatching operator (拣货员）,driver etc
            $mdl_roles =$this->loadModel('staff_roles');
            $roles_list =$mdl_roles->getRoleList($this->loginUser['id'],$this->loginUser['role']);
            $this->setData(json_encode($roles_list),'roles_list');
           // var_dump($roles_list);exit;
            // 记录当前员工的校色数量，并根据角色数量切换到不同的页面，如果没有角色，则切换到没有角色页面，如果只有一个校色，直接切换到
            // 角色的管理台，如果角色大于1 ，则切换到总控台。

            $countOfRoles =sizeof($roles_list);
            $this->setData($countOfRoles,'countOfRoles');

             $pageHtm = $this->getindexPageOfUser($this->loginUser,$pclogin,$countOfRoles,$roles_list[0]['id']);
           //  var_dump($pageHtm);exit;
			 $this->display_pc_mobile($pageHtm, $pageHtm);

		}else{
			
			
            $pclogin =trim(get2('pclogin')) ;

            //get role of the login user ,such as sales , operator , dispatching operator (拣货员）,driver etc
            $mdl_roles =$this->loadModel('staff_roles');
            $roles_list =$mdl_roles->getRoleList($this->loginUser['id'],$this->loginUser['role']);
            $this->setData(json_encode($roles_list),'roles_list');
           // var_dump($roles_list);exit;
            // 记录当前员工的校色数量，并根据角色数量切换到不同的页面，如果没有角色，则切换到没有角色页面，如果只有一个校色，直接切换到
            // 角色的管理台，如果角色大于1 ，则切换到总控台。

            $countOfRoles =sizeof($roles_list);
            $this->setData($countOfRoles,'countOfRoles');

             $pageHtm = $this->getindexPageOfUser($this->loginUser,$pclogin,$countOfRoles,$roles_list[0]['id']);
            // var_dump($pageHtm);exit;
			 $this->display_pc_mobile($pageHtm, $pageHtm);
		}
       
    }


	

	
	
    function index_publish_action()
    {
        $this->setData('商家首页', 'pagename');
        $this->setData('index_publish', 'menu');
        $this->setData('index_publish', 'submenu');
        $this->setData('商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
        $this->display_pc_mobile('company/index_publish', 'mobile/company/index_publish');
    }

    
   public function resend_order_confirmation_action()
    {
        $systemId = get2('id');
       // $systemId ='20211218074509604123';
       // var_dump($systemId);exit;
        if($systemId){
            $orderName=$this->loadModel('order')->generateOrderName($systemId);
            $template = $this->loadModel('system_mail_template');
            $system_mailer = $this->loadModel('system_mail');

            $order = $this->loadModel('order')->getByOrderId($systemId);
            $customerEmail = $order['email'];

            $title = (string)$this->lang->email_order_confirmed ."-- $orderName --cityb2b.com";
            $body  = $template->customerOrderNotification($systemId,$this->getLangStr());
            $to    = $customerEmail;

            $system_mailer->title($title);
            $system_mailer->body($body);
            $path =$_SERVER['DOCUMENT_ROOT'].'/themes/zh-cn/images/logo.png';
           $name ='statement.pdf';
          $system_mailer->attachment($path,$name);
           $path =$_SERVER['DOCUMENT_ROOT'].'/themes/zh-cn/images/logo.png';
           $name ='logo.png';
            $system_mailer->attachment($path,$name);
            $system_mailer->to('hhxx_2012@hotmail.com');
         //   $system_mailer->to($to);

            echo $system_mailer->send();
        }
    }

    function moneyview_action()
    {
        $from = get2('from');
        if ($from == "") {
            $from = "2000-01-01";
        }
        $to = get2('to');
        if ($to == "") {
            $to = date('Y-m-d');
        }

        $mdl_recharge = $this->loadModel('recharge');
        $time1 = strtotime($from);
        $time2 = strtotime($to);

        $sql = "select orderId,coupon_name,money,createTime,status from cc_recharge where  userId =" . $this->loginUser['id'] . " and createTime BETWEEN '" . $time1 . "' AND '" . $time2 . "' order by createTime desc ";

        $arr = $mdl_recharge->getListBySql($sql);
        $this->loadModel('invoice');

        $report = new AccountTransactionReport();
        $report->from($from)
            ->to($to)
            ->title("商家报表-资金流水")
            ->transactionData($arr);

       $report->generatePDF($this->lang);
        $report->outPutToBrowser();
        //$report->outPutToFile("C:\Andi.pdf");

    }

    function money_review_action($dataFomOtherMethod = [])
    {
        if($dataFomOtherMethod['file_path'] && $dataFomOtherMethod['business_id']) {
            $filePath = $dataFomOtherMethod['file_path'];
            $this->loginUser['id'] = $dataFomOtherMethod['business_id'];
        }
		// 统配商家 如果该商家属于某个统配商家，则获得统配商家号码
		$this->loadModel('freshfood_disp_suppliers_schedule');
		$dispaitch_business_id =DispCenter::getDispCenterIdOfSupplier($this->loginUser['id']);
		$this->setData($dispaitch_business_id,'dispatch_or_individual');

		if ($dispaitch_business_id) {
				$orderDateMustAfter='2017-07-27';// 由于之前的数据没有详细分录，并入统计会造成数据出错，必须剔除
				$lastSettlementPoint=$this->loadModel('settlement_log')->owner($this->loginUser['id'])->lastSettlementPoint();
				$this->setData($lastSettlementPoint,'lastSettlementPoint');

				$from = get2('startTime');
				if ($from == "") {
					$from =($lastSettlementPoint)?$lastSettlementPoint['settle_to']:$orderDateMustAfter;
				}
				$to = get2('endTime');
				if ($to == "") {
					$to = date('Y-m-d');
				}

				$defaultSettlementType = $this->loginUser['settlement_type'];

				$this->setData(trim(get2('settlement_type'))?trim(get2('settlement_type')):'system','settlement_type');
				$this->setData($from,'from');
				$this->setData($to,'to');

				$time1 = strtotime($from);
				$time2 = strtotime($to);
				$type = get2('status');
				$type = ($type)?$type:$defaultSettlementType;
				$this->setData($type,'status');

				$where_str='';
				switch ($type) {
					case 'c01':
						$where_str1=" and o.coupon_status='c01' ";
						break;
					case 'b01':
						$where_str1=" and o.coupon_status='b01' ";
						break;
					case 'all':
						$where_str1=" and (o.coupon_status='c01' or o.coupon_status='b01')";
						break;
					default:
						break;
				}


				$businessUserId=$this->loginUser['id'];
				$time1= ($time1>strtotime($orderDateMustAfter))?$time1:strtotime($orderDateMustAfter);
				$mdl_recharge= $this->loadModel('recharge');
				/**=============================================================================
				 * 统计各项资金分录的总金额
				 * =============================================================================
				 * dataSet1 中的数据集
				 *
				 * totalIn
				 * 钱包总进账
				 *
				 * totalOut
				 * 钱包总支出
				 *
				 * totalBalance
				 * 钱包总金额
				 *
				 * BalanceProcess::TYPE_SYS_MONEYPAY_BALANCETRANSFER
				 * 使用钱包支付进账的总额
				 *
				 * BalanceProcess::TYPE_SYS_PROMOTION_CODE_REFOUND
				 * 使用平台折扣码的金额返还
				 *
				 * BalanceProcess::TYPE_SYS_PLATFORM_FEE
				 * 平台手续费
				 *
				 * BalanceProcess::TYPE_SYS_UBONUS_COMMISSION
				 * 平台佣金
				 *
				 * BalanceProcess::TYPE_SYS_TRANSACTION_FEE_PLATFORM_REFOUND
				 * 平台补贴第三方支付手续费
				 *
				 * BalanceProcess::TYPE_SYS_DELIVER_FEE
				 * 运费
				 *
				 * BalanceProcess::TYPE_SYS_TRANSACTION_BALANCE
				 * 总线上交易金额
				 *
				 * BalanceProcess::TYPE_SYS_TRANSACTION_FEE_PLATFORM_CHARGE
				 * 平台收取第三方支付手续费
				 *
				 */

				$dataSet1[BalanceProcess::TYPE_SYS_MONEYPAY_BALANCETRANSFER]=0;
				$dataSet1[BalanceProcess::TYPE_SYS_PROMOTION_CODE_REFOUND]=0;
				$dataSet1[BalanceProcess::TYPE_SYS_PLATFORM_FEE]=0;
				$dataSet1[BalanceProcess::TYPE_SYS_UBONUS_COMMISSION]=0;
				$dataSet1[BalanceProcess::TYPE_SYS_UBONUS_COMMISSION_AMEND]=0;
				$dataSet1[BalanceProcess::TYPE_SYS_TRANSACTION_FEE_PLATFORM_REFOUND]=0;
				$dataSet1[BalanceProcess::TYPE_SYS_DELIVER_FEE]=0;
				$dataSet1[BalanceProcess::TYPE_SYS_TRANSACTION_BALANCE]=0;
				$dataSet1[BalanceProcess::TYPE_SYS_TRANSACTION_BALANCE_AMEND]=0;
				$dataSet1[BalanceProcess::TYPE_SYS_TRANSACTION_FEE_PLATFORM_CHARGE]=0;

				//DataSet1
				$totalBalance=0;
				$totalIn=0;
				$totalOut=0;
				$businessSettleableRecord=$mdl_recharge->businessSettleableRecord();

			    $sql1 ="SELECT sum(r.money) as amount,r.payment from cc_recharge as r left join cc_order as o on r.orderId = o.orderId left join cc_wj_user_coupon_activity_log as l on o.orderId = l.orderId where r.userId =$businessUserId $where_str1 and (o.business_userId =$businessUserId or o.business_userId =$dispaitch_business_id) and l.action_id=o.coupon_status and l.gen_date>=$time1 and l.gen_date<=$time2 group by r.payment ";
			    $data1 = $mdl_recharge->getListBySql($sql1);
			    foreach ($data1 as $d ) {
					$amount=$d['amount'];
					$type=$d['payment'];

					if(in_array($type, $businessSettleableRecord)) {
						$dataSet1[$type]=$amount;

						if($amount>0){
							 if($d['payment'] =='UBONUS_COMMISSION_AMEND') { //如果是因为订单变动导致commission变化，那么该数据单独保存，但不作为营收进项
								$dataSet1[$type]=$amount;
								$totalOut+=$amount;
							}else{
							$totalIn+=$amount;
							}
						}else{
							if($type=='TRANSACTION_BALANCE_AMEND' || $type=='TRANSACTION_FEE_PLATFORM_CHARGE') { //如果为销售订单调整不计入支出，其它的销售款中已经扣除

							}else{
								$totalOut+=$amount;
							}


						}
					  $totalBalance+=$amount;
					}
				}

				$dataSet1['totalIn']=$totalIn;
				$dataSet1['totalOut']=$totalOut;
				$dataSet1['totalBalance']=$totalBalance;

				/**=============================================================================
				 * 统计订单交易中各项资金分录的总金额
				 * =============================================================================
				 * dataSet2 中的数据集
				 *
				 * transactionBalance
				 * 线上交易总额
				 *
				 * useMoney
				 * 钱包支付总额
				 *
				 * promotionTotal
				 * 使用的各类折扣卷总额
				 *
				 * deliveryFee
				 * 运费总额
				 *
				 * platformFee
				 * 预定费总额
				 *
				 * transactionFee
				 * 三方支付费用总额
				 *
				 * totalGoodsSales
				 * 商品销售总额 =  线上交易总额 +  钱包支付总额 + 使用的各类折扣卷总额 - 运费总额 - 预定费总额 - 三方支付费用总额-退货总额
				 */
                $mdl_order=$this->loadModel('order');
				$sql2="SELECT sum(money) as transactionBalance, sum(confirmedMoneyAppliedAmount) as useMoney , sum(delivery_fees) as deliveryFee,sum(booking_fees) as platformFee ,sum(promotion_total) as promotionTotal, sum(surcharge) as transactionFee FROM cc_order as o left join cc_wj_user_coupon_activity_log as l on o.orderId = l.orderId where o.business_userId =$businessUserId $where_str1 and l.action_id=o.coupon_status and l.gen_date>=$time1 and l.gen_date<=$time2 ";
				$data2 = $mdl_order->getListBySql($sql2);
				$dataSet2 = $data2[0];

                $gstGroupBySql = "SELECT
                                sum(cust.customer_buying_quantity*cust.voucher_deal_amount) as totalGoodsSales,
                                sum(cust.adjust_subtotal_amount) as totalNetsales,
                                cust.include_gst
                                FROM cc_wj_customer_coupon as cust
                                    left join cc_order as o on cust.order_id=o.orderId
                                    left join  cc_wj_user_coupon_activity_log as l on cust.order_Id = l.orderId
                                where cust.business_id =$businessUserId $where_str1 
                                    and l.action_id=o.coupon_status 
                                    and l.gen_date>=$time1 
                                    and l.gen_date<=$time2
                                group by cust.include_gst";
                $data8 = $mdl_order->getListBySql($gstGroupBySql);
                foreach ($data8 as $data) {
                    $goodSales = $data['totalGoodsSales'];
                    $dataSet2['totalGoodsSales']+= $goodSales;
                    switch ($data['include_gst']) {
                        case 1:
                            $dataSet2['totalGoodsSalesWithGst'] += $goodSales;
                            break;
                        case 0:
                            $dataSet2['totalGoodsSalesNoGst'] += $goodSales;
                            break;
                    }
                }

				/*****获得指定时间内的退货总额****************/
				/* 程序开发的时候， 一旦整个报表结算完成， 这些退货记录不能够再被更改，这个是后台结算中心，点击已结算的时候，要做这项操作。
				/* amend order 一旦被产生，如果系统取消订单，那么该amendorder 不再起作用。  */
				$mdl_order_amend = $this->loadModel('order_amend');
				$sqlamend = "select sum(old_sub_total-new_sub_total) as sum_amend,
                            include_gst
                            from cc_order_amend 
                            	left join cc_order as o on cc_order_amend.order_id = o.orderId
                                left join cc_wj_customer_coupon on cc_order_amend.item_buying_id = cc_wj_customer_coupon.id 
                            where createUserId =$businessUserId $where_str1 
                                and cc_order_amend.createTime>=$time1  
                                and cc_order_amend.createTime<=$time2
                            group by include_gst";
                $amenda_data =$mdl_order_amend->getListBySql($sqlamend);
                if($amenda_data) {
                    foreach ($amenda_data as $data) {
                        $amendAmount = $data['sum_amend'];
                        $dataSet2['totalamend']+= $amendAmount;
                        switch ($data['include_gst']) {
                            case 1:
                                $dataSet2['totalAmendWithGst'] += $amendAmount;
                                break;
                            case 0:
                                $dataSet2['totalAmendNoGst'] += $amendAmount;
                                break;
                        }
                    }
                }


				/****************END*/

			  // 获取商家基本信息 ，比如：公司实体名称， trading name , abn , ref_number , 公司GST 类型
				$curr_user = $this->loadModel('user')->get($this->loginUser['id']);
			    $this->setData($curr_user,'curr_user');

				// 页面输出信息统计 ， 下面程序 将根据该商家的销售额，报损，计算净销售和commission ,并根据是否含有GST计算出最后的balance
                $commissionOfsales = 0;
                $commissionOfsaleswithoutGst = 0;
                $commissionOfsalesGst = 0;

                $commissionOfsalesWithGst = ($dataSet2['totalGoodsSalesWithGst']-$dataSet2['totalAmendWithGst'])*$curr_user['platform_commission_rate'];
                $commissionOfsaleswithoutGst += sprintf("%1\$.2f", $commissionOfsalesWithGst/11*10) ;
                $commissionOfsalesGst +=  sprintf("%1\$.2f", $commissionOfsalesWithGst/11) ;

                $commissionOfsalesNoGst = ($dataSet2['totalGoodsSalesNoGst']-$dataSet2['totalAmendNoGst'])*$curr_user['platform_commission_rate'];
                $commissionOfsalesGst +=  sprintf("%1\$.2f", $commissionOfsalesNoGst*0.1) ;
                $commissionOfsales += sprintf("%1\$.2f",  $commissionOfsalesWithGst + $commissionOfsalesNoGst +$commissionOfsalesGst) ;
                $commissionOfsaleswithoutGst += sprintf("%1\$.2f", $commissionOfsalesNoGst) ;

				$dataSet2['commissionOfsales'] ='$'.$commissionOfsales;
				$dataSet2['commissionOfsaleswithoutGst'] ='$'.$commissionOfsaleswithoutGst;
				$dataSet2['commissionOfsalesGst'] ='$'.$commissionOfsalesGst;

				// 如果是全部含gst ,则 balance 不变 ，如果是 不含gst公司， balance 扣减110%gst

                $dataSet2['commissionRebate'] = $this->calculateCommissionRebate($businessUserId, $where_str1, $time1, $time2);
				$dataSet1['totalBalance'] = $dataSet2['totalGoodsSales']
                    - $dataSet2['totalamend']
                    - $commissionOfsales
                    - $dataSet2['commissionRebate']['rebate']['commission']['amount']
                    + $dataSet2['commissionRebate']['rebate']['promotion']['amount'];

                if($curr_user['delivery_fee_type'] ==1) {
                    $dataSet1['totalBalance'] += $dataSet2['deliveryFee'];
                }

				$this->setData('结算中心 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');

				if (get2('output')=="pdf")
				{

					$where12=array(
					   'userId'=>$this->loginUser['id']

					);
				   $user_abn = $this->loadModel('wj_abn_application')->getByWhere($where12);

					$inStr_businessSettleableRecord = "'".join("','",$businessSettleableRecord)."'";

					$sql = "select o.orderId, o.order_name,r.coupon_name, r.money,l.gen_date as createTime,r.status from cc_recharge as r left join cc_order as o on r.orderId = o.orderId left join cc_wj_user_coupon_activity_log as l on (o.orderId = l.orderId and o.coupon_status = l.action_id)
					where  r.payment in ($inStr_businessSettleableRecord) and r.userId =$businessUserId and o.business_userId =$businessUserId  and l.gen_date>=$time1 and l.gen_date<=$time2  $where_str1 order by l.gen_date";

					$arr =$mdl_recharge->getListBySql($sql);

					$this->loadModel('invoice');

					$report = new AccountTransactionReport();
					$report->title("订单的资金结算")
						->from($from)
						->to($to)
						->transactionData($arr)
						->dataSet1($dataSet1)
						->dataSet2($dataSet2);

					$report->generatePDF();

                    if($filePath) {//如果是系统内部调用会直接在指定路径创建文件
                        $report->outPutToFile($filePath);
                        return $filePath;
                    }

					$report->outPutToBrowser(substr($user_abn['untity_name'],0,8).'-'.substr($from,0,10).'-'.$to.'_Transcations.pdf');
					//$report->outPutToBrowser();
				}
				elseif(get2('output')=="pdf_settlement_dispatching_supplier"){

					 $inStr_businessSettleableRecord = "'".join("','",$businessSettleableRecord)."'";

					$sql = "select o.orderId, o.order_name,r.coupon_name, r.money,l.gen_date as createTime,r.status from cc_recharge as r left join cc_order as o on r.orderId = o.orderId left join cc_wj_user_coupon_activity_log as l on (o.orderId = l.orderId and o.coupon_status = l.action_id)
					where  r.payment in ($inStr_businessSettleableRecord) and r.userId =$businessUserId and o.business_userId =$businessUserId  and l.gen_date>=$time1 and l.gen_date<=$time2  $where_str1 order by l.gen_date";



					$data =$mdl_recharge->getListBySql($sql);

					$lotteryUserList=array();
					$where12=array(
					   'userId'=>$this->loginUser['id']

					);

					$where13=array(
					   'userid'=>$this->loginUser['id']

					);
				   $user_abn = $this->loadModel('wj_abn_application')->getByWhere($where12);
				   $user_account = $this->loadModel('user_account_info')->getByWhere($where13);
				  // var_dump($user_abn['ABNorACN']);exit;

					$this->loadModel('invoice');
					$report = new settlementReportDispatchingSupplier();
					$report->setStarttime(substr($from,0,10))
						->setEndtime($to)
						->title("Settlement Report")
						->setGstType($curr_user['gst_type'])
                        ->setDeliveryFeeType($curr_user['delivery_fee_type'])
						->setBusinessName($user_abn['untity_name'])
						->setTradingName($curr_user['displayName'])
						->setABN($user_abn['ABNorACN'])
						->setAccountName($user_account['account_name'])
						->setBSB($user_account['bsb_number'])
						->setAccountNumber1($user_account['account_number'])
						->setAccountNumber($curr_user['id'])
						->setRefNumber(date('Ymd',time()).'-'.$curr_user['id'].rand(10,99))
						->setCommissionRate($curr_user['platform_commission_rate'])
						->dataSet1($dataSet1)
						->dataSet2($dataSet2);

					$report->generatePDF();

                    if($filePath) {//如果是系统内部调用会直接在指定路径创建文件
                        $report->outPutToFile($filePath);
                        return $filePath;
                    }

					$report->outPutToBrowser(substr($user_abn['untity_name'],0,8).'-'.substr($from,0,10).'-'.$to.'_Settlement.pdf');
					//$report->outPutToFile($user_abn['untity_name'].substr($from,0,10)."-".$to.".pdf","F");

					exit;



				}else
				{
					$this->setData($dataSet1,'dataSet1');
					$this->setData($dataSet2,'dataSet2');


					$this->setData('balance_account','menu');
					$this->setData('money_review','submenu');

					$this->display('company/money_review');
				}

		}
		else {



				$orderDateMustAfter='2017-07-27';// 由于之前的数据没有详细分录，并入统计会造成数据出错，必须剔除

				$lastSettlementPoint=$this->loadModel('settlement_log')->owner($this->loginUser['id'])->lastSettlementPoint();
				$this->setData($lastSettlementPoint,'lastSettlementPoint');


				$from = get2('startTime');
				if ($from == "") {
					$from =($lastSettlementPoint)?$lastSettlementPoint['settle_to']:$orderDateMustAfter;
				}
				$to = get2('endTime');
				if ($to == "") {
					$to = date('Y-m-d');
				}

				$defaultSettlementType = $this->loginUser['settlement_type'];

				$this->setData(trim(get2('settlement_type'))?trim(get2('settlement_type')):'system','settlement_type');

				$this->setData($from,'from');
				$this->setData($to,'to');

				$time1 = strtotime($from);
				$time2 = strtotime($to);

				$type = get2('status');
				$type = ($type)?$type:$defaultSettlementType;
				$this->setData($type,'status');

				$where_str='';
				switch ($type) {
					case 'c01':
						$where_str1=" and o.coupon_status='c01' ";
						break;
					case 'b01':
						$where_str1=" and o.coupon_status='b01' ";
						break;
					case 'all':
						$where_str1=" and (o.coupon_status='c01' or o.coupon_status='b01')";
						break;
					default:
						break;
				}


				$businessUserId=$this->loginUser['id'];
				$time1= ($time1>strtotime($orderDateMustAfter))?$time1:strtotime($orderDateMustAfter);
				$mdl_recharge= $this->loadModel('recharge');

				/**=============================================================================
				 * 统计各项资金分录的总金额
				 * =============================================================================
				 * dataSet1 中的数据集
				 *
				 * totalIn
				 * 钱包总进账
				 *
				 * totalOut
				 * 钱包总支出
				 *
				 * totalBalance
				 * 钱包总金额
				 *
				 * BalanceProcess::TYPE_SYS_MONEYPAY_BALANCETRANSFER
				 * 使用钱包支付进账的总额
				 *
				 * BalanceProcess::TYPE_SYS_PROMOTION_CODE_REFOUND
				 * 使用平台折扣码的金额返还
				 *
				 * BalanceProcess::TYPE_SYS_PLATFORM_FEE
				 * 平台手续费
				 *
				 * BalanceProcess::TYPE_SYS_UBONUS_COMMISSION
				 * 平台佣金
				 *
				 * BalanceProcess::TYPE_SYS_TRANSACTION_FEE_PLATFORM_REFOUND
				 * 平台补贴第三方支付手续费
				 *
				 * BalanceProcess::TYPE_SYS_DELIVER_FEE
				 * 运费
				 *
				 * BalanceProcess::TYPE_SYS_TRANSACTION_BALANCE
				 * 总线上交易金额
				 *
				 * BalanceProcess::TYPE_SYS_TRANSACTION_FEE_PLATFORM_CHARGE
				 * 平台收取第三方支付手续费
				 *
				 */


				$dataSet1[BalanceProcess::TYPE_SYS_MONEYPAY_BALANCETRANSFER]=0;
				$dataSet1[BalanceProcess::TYPE_SYS_PROMOTION_CODE_REFOUND]=0;
				$dataSet1[BalanceProcess::TYPE_SYS_PLATFORM_FEE]=0;
				$dataSet1[BalanceProcess::TYPE_SYS_UBONUS_COMMISSION]=0;
				$dataSet1[BalanceProcess::TYPE_SYS_UBONUS_COMMISSION_AMEND]=0;
				$dataSet1[BalanceProcess::TYPE_SYS_TRANSACTION_FEE_PLATFORM_REFOUND]=0;
				$dataSet1[BalanceProcess::TYPE_SYS_DELIVER_FEE]=0;
				$dataSet1[BalanceProcess::TYPE_SYS_TRANSACTION_BALANCE]=0;
				$dataSet1[BalanceProcess::TYPE_SYS_TRANSACTION_BALANCE_AMEND]=0;
				$dataSet1[BalanceProcess::TYPE_SYS_TRANSACTION_FEE_PLATFORM_CHARGE]=0;

				//DataSet1
				$totalBalance=0;
				$totalIn=0;
				$totalOut=0;

				$businessSettleableRecord=$mdl_recharge->businessSettleableRecord();

			  


			  $sql1 ="SELECT sum(r.money) as amount,r.payment from cc_recharge as r left join cc_order as o on r.orderId = o.orderId left join cc_wj_user_coupon_activity_log as l on o.orderId = l.orderId where r.userId =$businessUserId $where_str1 and o.business_userId =$businessUserId and l.action_id=o.coupon_status and l.gen_date>=$time1 and l.gen_date<=$time2 group by r.payment ";
			


			   $data1 = $mdl_recharge->getListBySql($sql1);
				 foreach ($data1 as $d ) {
					$amount=$d['amount'];
					$type=$d['payment'];

					if(in_array($type, $businessSettleableRecord)) {
						$dataSet1[$type]=$amount;

						if($amount>0){
							 if($d['payment'] =='UBONUS_COMMISSION_AMEND') { //如果是因为订单变动导致commission变化，那么该数据单独保存，但不作为营收进项
								$dataSet1[$type]=$amount;
								$totalOut+=$amount;
							}else{
							$totalIn+=$amount;
							}
						}else{
							if($type=='TRANSACTION_BALANCE_AMEND' || $type=='TRANSACTION_FEE_PLATFORM_CHARGE') { //如果为销售订单调整不计入支出，其它的销售款中已经扣除

							}else{
							$totalOut+=$amount;
							}


						}
					  $totalBalance+=$amount;
					}

				}

				$dataSet1['totalIn']=$totalIn;
				$dataSet1['totalOut']=$totalOut;
				$dataSet1['totalBalance']=$totalBalance;


				/**=============================================================================
				 * 统计订单交易中各项资金分录的总金额
				 * =============================================================================
				 * dataSet2 中的数据集
				 *
				 * transactionBalance
				 * 线上交易总额
				 *
				 * useMoney
				 * 钱包支付总额
				 *
				 * promotionTotal
				 * 使用的各类折扣卷总额
				 *
				 * deliveryFee
				 * 运费总额
				 *
				 * platformFee
				 * 预定费总额
				 *
				 * transactionFee
				 * 三方支付费用总额
				 *
				 * totalGoodsSales
				 * 商品销售总额 =  线上交易总额 +  钱包支付总额 + 使用的各类折扣卷总额 - 运费总额 - 预定费总额 - 三方支付费用总额-退货总额
				 */
				//DataSet2
				$mdl_order=$this->loadModel('order');

				$sql2="SELECT sum(money) as transactionBalance, sum(confirmedMoneyAppliedAmount) as useMoney , sum(delivery_fees) as deliveryFee,sum(booking_fees) as platformFee ,sum(promotion_total) as promotionTotal, sum(surcharge) as transactionFee FROM cc_order as o left join cc_wj_user_coupon_activity_log as l on o.orderId = l.orderId where o.business_userId =$businessUserId $where_str1 and l.action_id=o.coupon_status and l.gen_date>=$time1 and l.gen_date<=$time2 ";

				$data2 = $mdl_order->getListBySql($sql2);
				$dataSet2 = $data2[0];

                $gstGroupBySql = "SELECT sum(cust.customer_buying_quantity*cust.voucher_deal_amount) as totalGoodsSales, 
                                    sum(cust.adjust_subtotal_amount) as totalNetsales,
                                    cust.include_gst
                                    FROM cc_wj_customer_coupon as cust 
                                        left join cc_order as o on cust.order_id=o.orderId 
                                        left join  cc_wj_user_coupon_activity_log as l on cust.order_Id = l.orderId 
                                    where cust.business_id =$businessUserId $where_str1 
                                        and l.action_id=o.coupon_status 
                                        and l.gen_date>=$time1 
                                        and l.gen_date<=$time2
                                    group by cust.include_gst";
                $data8 = $mdl_order->getListBySql($gstGroupBySql);
                foreach ($data8 as $data) {
                    $goodSales = $data['totalGoodsSales'];
                    $dataSet2['totalGoodsSales']+= $goodSales;
                    switch ($data['include_gst']) {
                        case 1:
                            $dataSet2['totalGoodsSalesWithGst'] += $goodSales;
                            break;
                        case 0:
                            $dataSet2['totalGoodsSalesNoGst'] += $goodSales;
                            break;
                    }
                }

				/*****获得指定时间内的退货总额****************/
				/* 程序开发的时候， 一旦整个报表结算完成， 这些退货记录不能够再被更改，这个是后台结算中心，点击已结算的时候，要做这项操作。
				/* amend order 一旦被产生，如果系统取消订单，那么该amendorder 不再起作用。  */
				$mdl_order_amend = $this->loadModel('order_amend');
				$sqlamend = "select sum(old_sub_total-new_sub_total) as sum_amend,
                             include_gst
                            from cc_order_amend 
                            	left join cc_order as o on cc_order_amend.order_id = o.orderId
                                left join cc_wj_customer_coupon on cc_order_amend.item_buying_id = cc_wj_customer_coupon.id 
                            where createUserId =$businessUserId $where_str1 
                                and cc_order_amend.createTime>=$time1  
                                and cc_order_amend.createTime<=$time2
                            group by include_gst";
				$amenda_data =$mdl_order_amend->getListBySql($sqlamend);
				if($amenda_data) {
				    foreach ($amenda_data as $data) {
                        $amendAmount = $data['sum_amend'];
                        $dataSet2['totalamend']+= $amendAmount;
                        switch ($data['include_gst']) {
                            case 1:
                                $dataSet2['totalAmendWithGst'] += $amendAmount;
                                break;
                            case 0:
                                $dataSet2['totalAmendNoGst'] += $amendAmount;
                                break;
                        }
                    }
				}

				/****************END*/
				/**=============================================================================
				 * 统计线上交易中不同支付方式的分录
				 * =============================================================================
				 * dataSet2 中的数据集
				 *
				 * paypal
				 * 线上交易
				 *
				 * royalpay
				 * 线上交易
				 *
				 * creditcard
				 * 线上交易
				 *
				 * hcash
				 * 线上交易
				 * 
				 * offline
				 * 线下交易
				 *
				 *
				 * transactionBalanceOnline
				 * 线上交易总额 =  paypal + royalpay +creditcard +hcash
				 *
				 */
				$dataSet3['paypal']=0;
				$dataSet3['royalpay']=0;
				$dataSet3['alipay']=0;
				$dataSet3['creditcard']=0;
				$dataSet3['offline']=0;
				$dataSet3['hcash']=0;
				$dataSet3['transactionBalanceOnline']=0;

				//DataSet3
				$mdl_order=$this->loadModel('order');

				$sql3="SELECT o.payment,sum(money) as transactionBalance FROM cc_order as o left join cc_wj_user_coupon_activity_log as l on o.orderId = l.orderId where o.business_userId =$businessUserId  $where_str1 and l.action_id=o.coupon_status and l.gen_date>=$time1 and l.gen_date<=$time2 group by o.payment ";

				$data3 = $mdl_order->getListBySql($sql3);
				foreach ($data3 as $d) {
					$dataSet3[$d['payment']]=$d['transactionBalance'];
				}

				$dataSet3['transactionBalanceOnline']=$dataSet3['paypal']+$dataSet3['royalpay']+$dataSet3['alipay']+$dataSet3['creditcard']+$dataSet3['hcash']+$dataSet3['offline'];


				 /**=============================================================================
				 * 个人行为交易
				 * =============================================================================
				 * dataSet4 中的数据集
				 *
				 * BalanceProcess::TYPE_WITHDRAW
				 * 取现
				 *
				 * BalanceProcess::TYPE_REDBAG
				 * 红包
				 *
				 * BalanceProcess::TYPE_SYS_USEMONEYPAY
				 * 用户使用钱包支付
				 *
				 * BalanceProcess::TYPE_SYS_CUSTOMER_REF_COMMISSION
				 * 用户介绍关系赚取的手续费
				 * 
				 * BalanceProcess::TYPE_SYS_BUSINESS_REF_COMMISSION
				 * 商家介绍关系赚取的手续费
				 *
				 *
				 * personalTransactionTotal
				 * 个人行为交易交易总额 =  
				 *
				 */
				 $dataSet4=array();
				 $dataSet4[BalanceProcess::TYPE_WITHDRAW]=0;
				 $dataSet4[BalanceProcess::TYPE_REDBAG]=0;
				 $dataSet4[BalanceProcess::TYPE_SYS_USEMONEYPAY]=0;
				 $dataSet4[BalanceProcess::TYPE_SYS_CUSTOMER_REF_COMMISSION]=0;
				 $dataSet4[BalanceProcess::TYPE_SYS_BUSINESS_REF_COMMISSION]=0;
				 $dataSet4['personalTransactionTotal']=0;


				$sql4 ="SELECT sum(r.money) as amount,r.payment from cc_recharge as r where r.userId =$businessUserId and r.createTime>=$time1 and r.createTime<=$time2 group by r.payment ";

				$data4 = $mdl_recharge->getListBySql($sql4);
				foreach ($data4 as $d) {
					$dataSet4[$d['payment']]=$d['amount'];
				}
				$dataSet4['personalTransactionTotal']=
					$dataSet4[BalanceProcess::TYPE_WITHDRAW]
					+$dataSet4[BalanceProcess::TYPE_REDBAG]
					+$dataSet4[BalanceProcess::TYPE_SYS_USEMONEYPAY]
					+$dataSet4[BalanceProcess::TYPE_SYS_CUSTOMER_REF_COMMISSION]
					+$dataSet4[BalanceProcess::TYPE_SYS_BUSINESS_REF_COMMISSION];


				$dataSet1['totalBalance']+=$dataSet4['personalTransactionTotal'];
				// $dataSet1['totalBalance']+= $dataSet3['offline']; // harry : 这里加入了线下支付，因为现在所有的线下支付都直接转到Ubonus

			  // 获取商家基本信息 ，比如：公司实体名称， trading name , abn , ref_number , 公司GST 类型

				$curr_user = $this->loadModel('user')->get($this->loginUser['id']);
			    $this->setData($curr_user,'curr_user');



				// 页面输出信息统计 ， 下面程序 将根据该商家的销售额，报损，计算净销售和commission ,并根据是否含有GST计算出最后的balance
                $commissionOfsales = 0;
                $commissionOfsaleswithoutGst = 0;
                $commissionOfsalesGst = 0;

                $commissionOfsalesWithGst = ($dataSet2['totalGoodsSalesWithGst']-$dataSet2['totalAmendWithGst'])*$curr_user['platform_commission_rate'];
                $commissionOfsaleswithoutGst += sprintf("%1\$.2f", $commissionOfsalesWithGst/11*10) ;
                $commissionOfsalesGst +=  sprintf("%1\$.2f", $commissionOfsalesWithGst/11) ;

                $commissionOfsalesNoGst = ($dataSet2['totalGoodsSalesNoGst']-$dataSet2['totalAmendNoGst'])*$curr_user['platform_commission_rate'];
                $commissionOfsalesGst +=  sprintf("%1\$.2f", $commissionOfsalesNoGst*0.1) ;
                $commissionOfsales += sprintf("%1\$.2f",  $commissionOfsalesWithGst + $commissionOfsalesNoGst +$commissionOfsalesGst) ;
                $commissionOfsaleswithoutGst += sprintf("%1\$.2f", $commissionOfsalesNoGst) ;

				$dataSet2['commissionOfsales'] ='$'.$commissionOfsales;
				$dataSet2['commissionOfsaleswithoutGst'] ='$'.$commissionOfsaleswithoutGst;
				$dataSet2['commissionOfsalesGst'] ='$'.$commissionOfsalesGst;
				// 如果是全部含gst ,则 balance 不变 ，如果是 不含gst公司， balance 扣减110%gst
                 $dataSet2['commissionRebate'] = $this->calculateCommissionRebate($businessUserId, $where_str1, $time1, $time2);
                $dataSet1['totalBalance'] = $dataSet2['totalGoodsSales']
                    - $dataSet2['totalamend']
                    - $commissionOfsales
                    - $dataSet2['commissionRebate']['rebate']['commission']['amount']
                    + $dataSet2['commissionRebate']['rebate']['promotion']['amount'];

                if($curr_user['delivery_fee_type'] ==1) {
                    $dataSet1['totalBalance'] += $dataSet2['deliveryFee'];
                }


            $this->setData('结算中心 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
				if (get2('output')=="pdf")
				{
					$inStr_businessSettleableRecord = "'".join("','",$businessSettleableRecord)."'";

					$sql = "select o.orderId, o.order_name,r.coupon_name, r.money,l.gen_date as createTime,r.status from cc_recharge as r left join cc_order as o on r.orderId = o.orderId left join cc_wj_user_coupon_activity_log as l on (o.orderId = l.orderId and o.coupon_status = l.action_id)
					where  r.payment in ($inStr_businessSettleableRecord) and r.userId =$businessUserId and o.business_userId =$businessUserId  and l.gen_date>=$time1 and l.gen_date<=$time2  $where_str1 order by l.gen_date";

					$arr =$mdl_recharge->getListBySql($sql);

					$this->loadModel('invoice');

					$report = new AccountTransactionReport();
					$report->title("订单的资金结算")
						->from($from)
						->to($to)
						->transactionData($arr)
						->dataSet1($dataSet1)
						->dataSet2($dataSet2)
						->dataSet3($dataSet3)
						->dataSet4($dataSet4);

					$report->generatePDF();

                    if($filePath) {//如果是系统内部调用会直接在指定路径创建文件
                        $report->outPutToFile($filePath);
                        return $filePath;
                    }

					$report->outPutToBrowser();
				}
				elseif(get2('output')=="pdf_settlement"){
					
					 $inStr_businessSettleableRecord = "'".join("','",$businessSettleableRecord)."'";

					$sql = "select o.orderId, o.order_name,r.coupon_name, r.money,l.gen_date as createTime,r.status from cc_recharge as r left join cc_order as o on r.orderId = o.orderId left join cc_wj_user_coupon_activity_log as l on (o.orderId = l.orderId and o.coupon_status = l.action_id)
					where  r.payment in ($inStr_businessSettleableRecord) and r.userId =$businessUserId and o.business_userId =$businessUserId  and l.gen_date>=$time1 and l.gen_date<=$time2  $where_str1 order by l.gen_date";

				  

					$data =$mdl_recharge->getListBySql($sql);

					$lotteryUserList=array();
					$where13=array(
					   'userid'=>$this->loginUser['id']
					   
					);
                    $where12=array(
                        'userId'=>$this->loginUser['id']
                    );
				   $user_abn = $this->loadModel('wj_abn_application')->getByWhere($where12);
				   $user_account = $this->loadModel('user_account_info')->getByWhere($where13);
				  // var_dump($user_abn['ABNorACN']);exit;

					$this->loadModel('invoice');
					$report = new settlementReportDispatchingSupplier();
					$report->setStarttime(substr($from,0,10))
						->setEndtime($to)
						->title("Settlement Report")
						->setGstType($curr_user['gst_type'])
                        ->setDeliveryFeeType($curr_user['delivery_fee_type'])
						->setBusinessName($user_abn['untity_name'])
						->setTradingName($curr_user['displayName'])
						->setABN($user_abn['ABNorACN'])
						->setAccountName($user_account['account_name'])
						->setBSB($user_account['bsb_number'])
						->setAccountNumber1($user_account['account_number'])
						->setAccountNumber($curr_user['id'])
						->setRefNumber(date('Ymd',time()).'-'.$curr_user['id'].rand(10,99))
						->setCommissionRate($curr_user['platform_commission_rate'])
						->dataSet1($dataSet1)
						->dataSet2($dataSet2);
					$report->generatePDF();

                    if($filePath) {//如果是系统内部调用会直接在指定路径创建文件
                        $report->outPutToFile($filePath);
                        return $filePath;
                    }

					$report->outPutToBrowser($user_abn['untity_name'].substr($from,0,10).'-'.$to.'.pdf');
					//$report->outPutToFile($user_abn['untity_name'].substr($from,0,10)."-".$to.".pdf","F");
					exit;
					
					
					
					
					
					
					
				}else
				{   
					$this->setData($dataSet1,'dataSet1');
					$this->setData($dataSet2,'dataSet2');
					$this->setData($dataSet3,'dataSet3');
					$this->setData($dataSet4,'dataSet4');

					$this->setData('balance_account','menu');
					$this->setData('money_review','submenu');
				
					$this->display('company/money_review');
				}
				
		}
    }

    private function calculateCommissionRebate($businessUserId, $whereCondition1, $startTime, $endTime) {
        $mdl_order = $this->loadModel('order');
        $mdl_order_amend = $this->loadModel('order_amend');
        $curr_user = $this->loadModel('user')->get($this->loginUser['id']);

        $commissionRefundData = [
            'products' => [],
            'rebate' => [
                'promotion' => [
                    'amount' => 0,
                    'amount_ex_gst' => 0,
                    'gst' => 0
                ],
                'commission' => [
                    'amount' => 0,
                    'amount_ex_gst' => 0,
                    'gst' => 0
                ],
            ]
        ];

        $promotionBySql = "SELECT cust.customer_buying_quantity,
                                    cust.voucher_deal_amount,
                                    cust.voucher_original_amount,
                                    cust.include_gst,
                                    cust.restaurant_menu_id,
                                    m.menu_cn_name,
                                    m.menu_id
                                FROM cc_wj_customer_coupon as cust 
                                LEFT JOIN cc_order as o on cust.order_id=o.orderId 
                                LEFT JOIN  cc_wj_user_coupon_activity_log as l on cust.order_Id = l.orderId 
                                LEFT JOIN  cc_restaurant_menu as m on cust.restaurant_menu_id = m.id 
                                WHERE cust.business_id = $businessUserId $whereCondition1 
                                    AND l.action_id = o.coupon_status 
                                    AND l.gen_date >= $startTime 
                                    AND l.gen_date <= $endTime
                                    AND cust.commission_free = 1";
        $promotionData = $mdl_order->getListBySql($promotionBySql);

        $promotionAmendSql = "SELECT old_sub_total,
                                        new_sub_total,
                                        include_gst
                                        FROM cc_order_amend
                                            LEFT JOIN cc_wj_customer_coupon on cc_order_amend.item_buying_id = cc_wj_customer_coupon.id 
                                        WHERE createUserId = $businessUserId 
                                            AND createTime >= $startTime  
                                            AND createTime <= $endTime
                                            AND cc_wj_customer_coupon.commission_free=1";
        $promotionAmendData =$mdl_order_amend->getListBySql($promotionAmendSql);

        foreach ($promotionData as $data) {
            $product = [];
            $commissionRate = $curr_user['platform_commission_rate'];
            $product['quantity'] = $data['customer_buying_quantity'];
            $product['include_gst'] = $data['include_gst'];
            $product['restaurant_menu_id'] = $data['restaurant_menu_id'];
            $product['menu_cn_name'] = $data['menu_cn_name'];

            $product['amend'] = 0;
            foreach ($promotionAmendData as $amendData) {
                if($amendData['item_buying_id'] == $data['id']) {
                    $product['amend'] = $amendData['old_sub_total'] - $amendData['new_sub_total'];
                }
            }

            $originalTotalNetSale = $data['voucher_original_amount'] * $product['quantity'] - $product['amend'];
            $product['original_total_sale'] = $this->calculateGst($data['voucher_original_amount'] * $product['quantity'], $product['include_gst']);
            $product['original_single_sale'] = $this->calculateGst($data['voucher_original_amount'], $product['include_gst']);
            $product['original_commission'] = $this->calculateGst($originalTotalNetSale * $commissionRate, $product['include_gst'] - 1);/**产品含税则佣金无需交税，产品不含税则佣金补税**/

            $promotionTotalNetSale = $data['voucher_deal_amount'] * $product['quantity'] - $product['amend'];
            $product['promotion_total_sale'] = $this->calculateGst($data['voucher_deal_amount'] * $product['quantity'], $product['include_gst']);
            $product['promotion_single_sale'] = $this->calculateGst($data['voucher_deal_amount'], $product['include_gst']);
            $product['promotion_commission'] = $this->calculateGst($promotionTotalNetSale * $commissionRate, $product['include_gst'] - 1);/**产品含税则佣金无需交税，产品不含税则佣金补税**/

            $product['commission_rebate'] = $this->arraySubtraction( $product['original_commission'], $product['promotion_commission']);
            $product['promotion_rebate'] = $this->arraySubtraction($product['original_total_sale'], $product['promotion_total_sale']);

            $productInArray = false;
            foreach ($commissionRefundData['products'] as $index => $existsProduct) {
                if($existsProduct['restaurant_menu_id'] != $product['restaurant_menu_id']) {
                    continue;
                }

                $mergeColumns = ['original_total_sale',
                    'original_commission',
                    'original_commission',
                    'promotion_total_sale',
                    'promotion_commission',
                    'commission_rebate',
                    'promotion_rebate',
                ];

                foreach ($mergeColumns as $mergeColumn) {
                    $commissionRefundData['products'][$index][$mergeColumn] = self::arrayAdd($existsProduct[$mergeColumn], $product[$mergeColumn]);
                }
                $commissionRefundData['products'][$index]['quantity'] += $product['quantity'];
                $productInArray = true;
            }
            if(!$productInArray) {
                array_push($commissionRefundData['products'], $product);
            }

            $commissionRefundData['rebate']['commission'] = self::arrayAdd($commissionRefundData['rebate']['commission'], $product['commission_rebate']);
            $commissionRefundData['rebate']['promotion'] = self::arrayAdd($commissionRefundData['rebate']['promotion'], $product['promotion_rebate']);
        }
        return $commissionRefundData;
    }

    private function arrayAdd($arrayA, $arrayB) {
        foreach ($arrayB as $key => $value) {
            $arrayA[$key] += $value;
        }
        return $arrayA;
    }

    private function arraySubtraction($arrayA, $arrayB) {
        foreach ($arrayB as $key => $value) {
            $arrayA[$key] -= $value;
        }
        return $arrayA;
    }

    private function calculateGst($amount, $includeGst = null) {
        $result = [];
        if($includeGst == 1) {//含GST
            $result['amount'] = $amount;
            $result['amount_ex_gst'] = $amount / 11 * 10;
            $result['gst'] = $amount / 11;
        } else if($includeGst == 0) {//不含GST
            $result['amount'] = $amount;
            $result['amount_ex_gst'] = $amount;
            $result['gst'] = 0;
        } else if($includeGst == -1) {//补充计算GST
            $result['amount'] = $amount + $amount / 10;
            $result['amount_ex_gst'] = $amount;
            $result['gst'] = $amount / 10;
        }
        return $result;
    }

    function redeemed_transaction_view_action(){
         $from = get2('from');
        if ($from == "") {
            $from = "2017-07-27";
        }
        $to = get2('to');
        if ($to == "") {
            $to = date('Y-m-d');
        }

        $type = get2('status');
        
        $where_str='';
        switch ($type) {
            case 'c01':
                $where_str=" and o.coupon_status='c01' ";
               
                break;
            case 'b01':
                $where_str=" and o.coupon_status='b01' ";
                # code...
                break;
            case 'all':
                $where_str=" and (o.coupon_status='c01' or o.coupon_status='b01')";
                # code...
                break;
            default:
                # code...
                break;
        }


        $time1 = strtotime($from);
        $time2 = strtotime($to);
        $businessUserId=$this->loginUser['id'];

        $mdl_recharge=$this->loadModel('recharge');

        $businessSettleableRecord=$mdl_recharge->businessSettleableRecord();
        $inStr_businessSettleableRecord = "'".join("','",$businessSettleableRecord)."'";

         $sql = "select o.orderId, o.order_name,r.coupon_name, r.money,l.gen_date as createTime,r.status from cc_recharge as r left join cc_order as o on r.orderId = o.orderId left join cc_wj_user_coupon_activity_log as l on (o.orderId = l.orderId and o.coupon_status = l.action_id)
            where  r.payment in ($inStr_businessSettleableRecord) and r.userId =$businessUserId and o.business_userId =$businessUserId  and l.gen_date>=$time1 and l.gen_date<=$time2  $where_str order by l.gen_date";

        $arr =$mdl_recharge->getListBySql($sql);

        $this->loadModel('invoice');

        $report = new AccountTransactionReport();
        $report->title("订单的资金结算")
            ->from($from)
            ->to($to)
            ->transactionData($arr);

        $report->generatePDF();
        $report->outPutToBrowser();
        //$report->outPutToFile("C:\Andi.pdf");
    }

    public function manual_settlement_action()
    {
        $from    = trim(get2('from'));
        $to      = trim(get2('to'));
        $status  = trim(get2('status'));
        $balance = trim(get2('balance'));

        if($from==$to)$this->sheader(null,'结算的时段不能小于1天。今日的订单需要明天才能结算');

        $mdl_settlement_log=$this->loadModel('settlement_log');
    
        $mdl_settlement_log
        ->owner($this->loginUser['id'])
        ->settleFrom($from)
        ->settleTo($to)
        ->settleOrderStatus($status)
        ->settleAmount($balance)
        ->operationType(mdl_settlement_log::OPERATION_TYPE_MANUAL)
        ->log();    # code...

        $balance = (float)$balance;
        $mdl_recharge = $this->loadModel( 'recharge' );

        /**
        * 如果是c01结算动作， 此次结算涉及到的所有订单记录，都需要从 PENDING  ==> SETTLE
        */
        if($status=='c01'){
            $order_in_str = " orderId in (".$mdl_settlement_log->getOrderList().") ";
            $mdl_recharge->updateByWhere(array('status'=>BalanceProcess::SETTLE), $order_in_str);
        }
       

        if($balance>0){
            //取现流程
            $orderId = '102'.date( 'YmdHis' ).$this->createRnd(3);
            $msg = "系统结算取现(From:$from To:$to $$balance)";

            $data = array(
                'orderId' => $orderId,
                'userId' => $this->loginUser['id'],
                'money' => 0-$balance,
                'payment' => BalanceProcess::TYPE_SYS_SETTLEMENT_WITHDRAW,
                'status' => BalanceProcess::PENDING, 
                'createTime' => time(),
                'createIp' => ip(),
                'coupon_name'=> $msg
            );
            $mdl_recharge->insert( $data );

            $this->sheader(HTTP_ROOT_WWW."company/account_balance");

        }elseif($balance<0){
            //充值流程
            $orderId = '101'.date( 'YmdHis' ).$this->createRnd(3);

            $msg = "系统结算充值(From:$from To:$to $$balance)";

            $data = array(
                'orderId' => $orderId,
                'userId' => $this->loginUser['id'],
                'money' => 0-$balance,
                'payment' => BalanceProcess::TYPE_SYS_SETTLEMENT_RECHARGE,
                'status' => BalanceProcess::INIT, 
                'createTime' => time(),
                'createIp' => ip(),
                'coupon_name'=> $msg
            );

            $mdl_recharge->insert($data);

            $this->sheader(HTTP_ROOT_WWW."company/account_balance");
        }else{
            $this->sheader(HTTP_ROOT_WWW."company/money_review");
        }

    }

    public function settlement_file_history_action() {

        $startDate  = trim(get2('startDate'));
        $endDate  = trim(get2('endDate'));

        $mdl_settlement_file_history = $this->loadModel('settlement_file_history');
        $settlementFileHistory = $mdl_settlement_file_history->getUserSettlementHistory($this->loginUser['id'], $startDate, $endDate);

        $this->setData($settlementFileHistory, 'settlementFileHistory');
        $this->setData($startDate, 'startDate');
        $this->setData($endDate, 'endDate');

        $this->setData('balance_account', 'menu');
        $this->setData('settlement_file_history', 'submenu');
        $this->setData($this->lang->settlement_file_history . ' - ' . $this->site['pageTitle'], 'pageTitle');

        $this->display('company/settlement_file_history');
    }

    //用于在结算时候生成结算周报，销售明细，报损明细作为历史记录留档，不可单独调用
    public function save_user_settlement_files_action() {
        $loginUserId = $this->loginUser['id'];
        $startDate = trim(get2('startDate'));
        $endDate = trim(get2('endDate'));
        $businessId = trim(get2('businessId'));

        $status = trim(get2('status'));

        $mdl_settlement_file_history = $this->loadModel('settlement_file_history');

        // Create Directory if not exists
        $this->file->createdir( $mdl_settlement_file_history->generateFileDirectoryPath($businessId));
        $fileSaveData = ['business_id' => $businessId];
        // Save money review file
        $dispatchBusinessId = DispCenter::getDispCenterIdOfSupplier($businessId);
        $_GET['output'] =  $dispatchBusinessId ? 'pdf_settlement_dispatching_supplier' : 'pdf_settlement';// Set the necessary $_GET parameters for the money_review method

        $moneyReviewFilePath = $mdl_settlement_file_history->generateFilePath($businessId, 'money_review');
        $fileSaveData['file_path'] = $moneyReviewFilePath;
        self::money_review_action($fileSaveData);

        $mdl_settlement_file_history->createHistoryFile($businessId, $moneyReviewFilePath, $startDate, $endDate);

        // Save customer orders file
        $_GET['status'] = $status;
        $_GET['output'] = 'pdf';
        $customerOrdersFilePath = $mdl_settlement_file_history->generateFilePath($businessId, 'customer_orders');
        $fileSaveData['file_path'] = $customerOrdersFilePath;
        self::customer_orders_action($fileSaveData);

        $mdl_settlement_file_history->createHistoryFile($businessId, $customerOrdersFilePath, $startDate, $endDate);

        // Save customer orders amend file
        $_GET['status'] = $status;
        $_GET['output'] = 'pdf';
        $customerOrdersAmendFilePath = $mdl_settlement_file_history->generateFilePath($businessId, 'customer_orders_amend');
        $fileSaveData['file_path'] = $customerOrdersAmendFilePath;
        self::customer_orders_amend_action($fileSaveData);

        $mdl_settlement_file_history->createHistoryFile($businessId, $customerOrdersAmendFilePath, $startDate, $endDate);

        $this->loginUser['id'] = $loginUserId;
    }

    public function settlement_log_action()
    {
        $mdl_settlement_log=$this->loadModel('settlement_log');

        $pageSql = $mdl_settlement_log->getListSql(null, array('user_id' => $this->loginUser['id']), 'gen_date desc');

        $pageUrl = $this->parseUrl()->set('page');
        $pageSize = 10;
        $maxPage = 10;
        $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
        $data = $mdl_settlement_log->getListBySql($page['outSql']);
        $this->setData($page['pageStr'],'pager');

        $this->setData($data);

        $this->setData('balance_account','menu');
        $this->setData('money_review','submenu');

        $this->setData('结算点纪录 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');

        $this->display('company/settlement_log');
    }

    function profile_web_action()
    {
        if (is_post()) {
            $websiteLink = trim(post('websiteLink'));
            $busi_weixin_name = trim(post('busi_weixin_name'));
            $busi_sina_weibo = trim(post('busi_sina_weibo'));
            $busi_facebook = trim(post('busi_facebook'));
            $busi_linkdin = trim(post('busi_linkdin'));
           
            $images = post('images');

            foreach ($images as $key => $value) {
                if($value=="default/image_upload.jpg")
                    unset($images[$key]);
                else
                    $images[$key]=trim($value);
            }
            $busi_weixin_scancode=trim(reset($images));

            $data = array(
                'websiteLink' => $websiteLink,
                'busi_weixin_name' => $busi_weixin_name,
                'busi_sina_weibo' => $busi_sina_weibo,
                'busi_facebook' => $busi_facebook,
                'busi_linkdin' => $busi_linkdin,
                'busi_weixin_scancode'=>$busi_weixin_scancode,
            );

            $mdl_user = $this->loadModel('user');
            if ($mdl_user->updateUserById($data, $this->loginUser['id'])) {
                $this->form_response_msg( '修改成功');
            } else {
                $this->form_response_msg('修改失败');
            }
        } else {
            $this->setData('网站社交修改', 'pagename');
            $this->setData('advanced_setting', 'menu');
            $this->setData('profile_web', 'submenu');
            $this->setData('网站社交修改 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->display('company/profile_web');
        }
    }
     function notice_action()
    {   
	
	    $shop=get2('shop');
			$this->setData( $shop, 'shop' );
        if(is_post()){
            $notice = post('business_notice');

            $notice=str_replace('；', ';',$notice);

            $data['notice']= $notice;

            $this->loadModel('user')->update($data,$this->loginUser['id']);

            $this->form_response_msg('保存成功');
        }else{
            $this->setData('store_setting', 'menu');
            $this->setData('notice', 'submenu');
            $this->setData('商家通知 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->display('company/notice');
        }
        
    }

    function profile_contact_action()
    {
			$shop=get2('shop');
			$this->setData( $shop, 'shop' );
        if (is_post()) {

            $contactPersonFirstname = trim(post('contactPersonFirstname'));
            $contactPersonLastname = trim(post('contactPersonLastname'));
            $contactPersonNickName = trim(post('contactPersonNickName'));
            $contactMobile = trim(post('contactMobile'));

            $googleMap = trim(post('googleMap'));
            $google_location = trim(post('location')); //12.123123,-123.123123
            $l= explode(',', $google_location);
            $latitude=$l[0];
            $longitude=$l[1];

            $addrNumber = trim(post('street_number'));  //30
            $addrStreet = trim(post('route'));          //jean st
            $addrPost = trim(post('postal_code'));      //3204
            $addrSuburb = trim(post('locality'));       //malvern east

            $state = trim(post('administrative_area_level_1'));   //vic
            $country_short = trim(post('country_short'));  //AU

            $country = trim(post('country'));   //Australia
            $googleMapUrl = trim(post('url'));

            $data = array(
                'contactPersonFirstname' => $contactPersonFirstname,
                'contactPersonLastname' => $contactPersonLastname,
                'contactPersonNickName' => $contactPersonNickName,
                'contactMobile' => $contactMobile,

                'googleMap' => $googleMap,
                'google_location' => $google_location,
                'latitude'=>$latitude,
                'longitude'=>$longitude,

                'addrNumber' => $addrNumber,
                'addrStreet' => $addrStreet,
                'addrPost' => $addrPost,
                'addrSuburb' => $addrSuburb,

                'addrState' => $state,
                'countryCode' => $country_short,

                'country' => $country,
                'googleMapUrl' => $googleMapUrl,
            );

            $mdl_user = $this->loadModel('user');
            if ($mdl_user->updateUserById($data, $this->loginUser['id'])) {
                $this->form_response_msg('修改成功');
            } else {
                $this->form_response_msg('修改失败');
            } 
        } else {

            $this->setData('联系信息修改', 'pagename');
            $this->setData('store_setting', 'menu');
            $this->setData('profile_contact', 'submenu');
            $this->setData('联系信息修改 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->display('company/profile_contact');
        }
    }


    function become_business_user_action()
    {   
        if(is_post()){

            $business_referal_id= trim(post('business_referal_id'));
            if($business_referal_id && is_numeric($business_referal_id)){
                $this->loadModel('referral_relation')->owner($business_referal_id)->addBusiness($this->loginUser['id']);
            }

      

            $displayName= trim(post('displayName'));
            
            if(!$displayName){
                 $this->form_response_msg('公司名称不可为空');
            }

            $city =post('city');
            $city =($city)?','.join(',', $city).',':' ';
			
			
			
			
			
			
			
			
				
			$googleMap = trim(post('googleMap'));
            $google_location = trim(post('location')); //12.123123,-123.123123
            $l= explode(',', $google_location);
            $latitude=$l[0];
            $longitude=$l[1];

            $addrNumber = trim(post('street_number'));  //30
            $addrStreet = trim(post('route'));          //jean st
            $addrPost = trim(post('postal_code'));      //3204
            $addrSuburb = trim(post('locality'));       //malvern east

            $state = trim(post('administrative_area_level_1'));   //vic
            $country_short = trim(post('country_short'));  //AU

            $country = trim(post('country'));   //Australia
            $googleMapUrl = trim(post('url'));
			
	     
            $data = array(
                'cityId' => $city,
               	'displayName'=>$displayName,
				'role'=>3,
                'googleMap' => $googleMap,
                'google_location' => $google_location,
                'latitude'=>$latitude,
                'longitude'=>$longitude,

                'addrNumber' => $addrNumber,
                'addrStreet' => $addrStreet,
                'addrPost' => $addrPost,
                'addrSuburb' => $addrSuburb,

                'addrState' => $state,
                'countryCode' => $country_short,

                'country' => $country,
                'googleMapUrl' => $googleMapUrl,
            );
          
			
	          

            $mdl_user = $this->loadModel('user');
            if ($mdl_user->updateUserById($data, $this->loginUser['id'])) {
               $this->form_response(200, '', HTTP_ROOT_WWW . 'company/index');
            } else {
                $this->form_response_msg("修改失败");
            }
        }else{
			if(strlen(trim($this->loginUser['cityId']))==0){
				$this->loginUser['cityId']=',526,556,';
			}
         //  var_dump('cityid:'.$this->loginUser['cityId']);exit;
            $this->setData('填写信息成为商家 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->display('company/become_business_user');
        }
        
    }

    function profile_action()
    {
        if (is_post()) {

            $companyDescription = post('companyDescription');
			 $companyDescription_en = post('companyDescription_en');
            $languageType = trim(post('languageType'));

            $businessName= trim(post('businessName'));
            $displayName= trim(post('displayName'));
			$displayName_en= trim(post('displayName_en'));

            $city =post('city');
			
            $city =($city)?','.join(',', $city).',':' ';
			
			
			
			
			$googleMap = trim(post('googleMap'));
            $google_location = trim(post('location')); //12.123123,-123.123123
            $l= explode(',', $google_location);
            $latitude=$l[0];
            $longitude=$l[1];

            $addrNumber = trim(post('street_number'));  //30
            $addrStreet = trim(post('route'));          //jean st
            $addrPost = trim(post('postal_code'));      //3204
            $addrSuburb = trim(post('locality'));       //malvern east

            $state = trim(post('administrative_area_level_1'));   //vic
            $country_short = trim(post('country_short'));  //AU

            $country = trim(post('country'));   //Australia
            $googleMapUrl = trim(post('url'));
			
	     
            $data = array(
                'cityId' => $city,
                'companyDescription' => $companyDescription,
				'companyDescription_en' => $companyDescription_en,
				'displayName'=>$displayName,
                'businessName'=>$businessName,
				'displayName_en'=>$displayName_en,
				 'googleMap' => $googleMap,
                'google_location' => $google_location,
                'latitude'=>$latitude,
                'longitude'=>$longitude,

                'addrNumber' => $addrNumber,
                'addrStreet' => $addrStreet,
                'addrPost' => $addrPost,
                'addrSuburb' => $addrSuburb,

                'addrState' => $state,
                'countryCode' => $country_short,

                'country' => $country,
                'googleMapUrl' => $googleMapUrl,
            );
          

            $mdl_user = $this->loadModel('user');
            if ($mdl_user->updateUserById($data, $this->loginUser['id'])) {
                $this->form_response(200, '保存成功',"SELF");
            } else {
                $this->form_response_msg("修改失败");
            }
        } else {
            $shop=get2('shop');
			$this->setData( $shop, 'shop' );
            $this->setData('商家信息修改', 'pagename');
            $this->setData('basic_setting', 'menu');
            $this->setData('profile', 'submenu');
            $this->setData('商家信息修改 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->display('company/profile');
        }
    }


    function payment_setting_action()
    {
         if (is_post()) {
          
            $offline_pay_des = trim(post('offline_pay_des'));
			$offline_pay_des_en = trim(post('offline_pay_des_en'));
            
            $supportofflinepayment = post('supportofflinepayment');

			/*
            $supportpaypalpayment = post('supportpaypalpayment'); */
            $supportroyalpaypayment = post('supportroyalpaypayment');
            $supporthcashpayment = post('supporthcashpayment');
            $supportcreditcardpayment = post('supportcreditcardpayment');

         /*   $paypalsurcharge = post('paypalsurcharge'); */
            $royalpaysurcharge = post('royalpaysurcharge');
            $hcashsurcharge = post('hcashsurcharge');
            $creditcardsurcharge = post('creditcardsurcharge');

          /*  $paypalsurcharge = $paypalsurcharge/100; */
            $royalpaysurcharge = $royalpaysurcharge/100;
            $hcashsurcharge = $hcashsurcharge/100;
            $creditcardsurcharge = $creditcardsurcharge/100;

          /* $transactionFeeChargeFrom_paypal=post('transactionFeeChargeFrom_paypal'); */
            $transactionFeeChargeFrom_royalpay=post('transactionFeeChargeFrom_royalpay');
            $transactionFeeChargeFrom_hcash=post('transactionFeeChargeFrom_hcash');
            $transactionFeeChargeFrom_creditcard=post('transactionFeeChargeFrom_creditcard');

            $noAlert=(trim(post('noAlert')))?0:1;
          
            $data = array(
                'offline_pay_des' => $offline_pay_des,
				 'offline_pay_des_en' => $offline_pay_des_en,
                
                'supportofflinepayment' => $supportofflinepayment,
              /*  'supportpaypalpayment' => $supportpaypalpayment,*/
                'supportroyalpaypayment'=>$supportroyalpaypayment,
                'supporthcashpayment'=>$supporthcashpayment,
                'supportcreditcardpayment'=>$supportcreditcardpayment,

               /* 'paypalsurcharge' => $paypalsurcharge,*/
                'royalpaysurcharge'=>$royalpaysurcharge,
                'hcashsurcharge'=>$hcashsurcharge,
                'creditcardsurcharge'=>$creditcardsurcharge,

              /*  'transactionFeeChargeFrom_paypal'=>$transactionFeeChargeFrom_paypal, */
                'transactionFeeChargeFrom_royalpay'=>$transactionFeeChargeFrom_royalpay,
                'transactionFeeChargeFrom_hcash'=>$transactionFeeChargeFrom_hcash,
                'transactionFeeChargeFrom_creditcard'=>$transactionFeeChargeFrom_creditcard,

                'noAlert'=>$noAlert
            );

            $mdl_user = $this->loadModel('user');
            if ($mdl_user->updateUserById($data, $this->loginUser['id'])) {
                $this->form_response(200, "保存成功", HTTP_ROOT_WWW . "company/payment_setting?freshfood=");
            } else {
                $this->form_response_msg("修改失败");
            }
        } else {
            $this->setData($this->loadModel('wj_busi_pay_setting_application')->isPaymentSelfManage($this->loginUser['id']),'isPaymentSelfManage');

            $this->setData('商家递送设置', 'pagename');
			if(get2('freshfood')){
				 $this->setData('open_store', 'menu');
			}else{
				 $this->setData('advanced_setting', 'menu');
			}
           
            $this->setData('payment_setting', 'submenu');
            $this->setData('商家递送设置 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->display('company/payment_setting');
        }
    }

    function delivery_setting_action()
    {
        if (is_post()) {
            $deliver_enable = trim(post('deliver_enable'));
            
            $pickup_enbale = trim(post('pickup_enbale'));

            $base_local_rate = trim(post('base_local_rate'));
            $base_national_rate = trim(post('base_national_rate'));
            $base_international_rate = trim(post('base_international_rate'));
            $amount_for_free_delivery = post('amount_for_free_delivery');
			
			$max_orders_accept_per_day = post('max_orders_accept_per_day'); 
			$amount_for_minimum_delivery = post('amount_for_minimum_delivery');
            
            $pickup_des = trim(post('pickup_des'));
            $delivery_description = post('delivery_description');
            $refund_policy = trim(post('refund_policy'));
			
			 $pickup_des_en = trim(post('pickup_des_en'));
            $delivery_description_en = post('delivery_description_en');
            $refund_policy_en = trim(post('refund_policy_en'));

            //如果是商家
            if ($this->loginUser['role'] == 3) {
                $data = array(
                    'deliver_avaliable' => $deliver_enable,
                    'pickup_avaliable' => $pickup_enbale,

                    'flat_rates_to_local_city' => $base_local_rate,
                    'flat_rates_national' => $base_national_rate,
                    'flat_rates_international' => $base_international_rate,
                    'amount_for_free_delivery' => $amount_for_free_delivery,
					'max_orders_accept_per_day'=>$max_orders_accept_per_day,
					'amount_for_minimum_delivery'=>$amount_for_minimum_delivery,

                    'pickup_des' => $pickup_des,
                    'delivery_description' => $delivery_description,
                    'refund_policy'=>$refund_policy,
					
					  'pickup_des_en' => $pickup_des_en,
                    'delivery_description_en' => $delivery_description_en,
                    'refund_policy_en'=>$refund_policy_en
                );
            }

            $mdl_user = $this->loadModel('user');
            if ($mdl_user->updateUserById($data, $this->loginUser['id'])) {
				
				
				$dataRestaurant =array(
				   'deliver_avaliable' => $deliver_enable,
                    'pickup_avaliable' => $pickup_enbale
				);
				$where =array(
				  'EvoucherOrrealproduct'=>'restaurant_menu',
				  'createUserId'=>$this->loginUser['id'],
				);
				$this->loadModel('coupons')->updateByWhere($dataRestaurant,$where);
				
                $this->form_response(200, "保存成功", HTTP_ROOT_WWW . "company/delivery_setting");
            } else {
                $this->form_response_msg("修改失败");
            }
        } else {

        	$delivery_fee_desc  = $this->get_business_delivery_des ($this->loginUser['id']);
			 $this->setData($delivery_fee_desc, 'delivery_fee_desc');
			

            $this->setData('商家递送设置', 'pagename');
			
			if(get2('freshfood')){
				 $this->setData('open_store', 'menu');
			}else{
				 $this->setData('advanced_setting', 'menu');
			}
			
           
            $this->setData('delivery_setting', 'submenu');
            $this->setData('商家递送设置 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->display('company/delivery_setting');
        }
    }

    /**
     * ajax update used in coupon_edit_step6
     * @return [type] [description]
     */
    public function update_user_deliver_fee_action()
    {
        $fee = get2('fee');
        $data['flat_rates_to_local_city']=$fee;
        $this->loadModel('user')->updateUserById($data, $this->loginUser['id']);
    }

    function customer_list_action()
    {
        $mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');
        $mdl_order = $this->loadModel('order');
        $mdl_user = $this->loadModel('user');

        $pageSql = "SELECT DISTINCT userId FROM cc_wj_customer_coupon WHERE business_id = " . $this->loginUser['id'];

        //echo $pageSql;exit;
        $pageUrl = $this->parseUrl()->set('page');
        $pageSize = 20;
        $maxPage = 10;
        $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
        $data = $mdl_wj_customer_coupon->getListBySql($page['outSql']);

        foreach ($data as $key => $value) {
            $data[$key]['orders'] = $mdl_order->getOrderListOfCustomer($value['userId'], $this->loginUser['id']);
            $data[$key]['userName']=$mdl_user->getUserDisplayName($value['userId']);
        }

        $this->setData($data, 'data');
        $this->setData($page['pageStr'], 'pager');

        $this->setData('客户管理', 'pagename');
        $this->setData('online_center', 'menu');
        $this->setData('customer_list', 'submenu');
        $this->setData('客户管理 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
        $this->display('company/customer_list');
    }


    function other_option_action()
    {
        $mdl_user = $this->loadModel('user');
        $user = $this->loginUser['id'];
        if (is_post()) {
            $bookingfee = post('bookingfee');
            $bookingfeetype = post('bookingfeetype');

            if (is_null($bookingfee)) $this->form_response_msg('不能为空');
            if (!is_numeric($bookingfee)) $this->form_response_msg('必须为数字');
            if ($bookingfee < 0) $this->form_response_msg('不能小于零');
            $bookingfee = number_format($bookingfee, 2);

            $mdl_user->begin();
            $mdl_user->updateBookingFee($user, $bookingfee);
            $mdl_user->updateBookingFeeType($user, $bookingfeetype);

            if (!$mdl_user->error()) {
                $mdl_user->commit();
                $this->form_response_msg("$" . $bookingfee . " " . $bookingfeetype . " 保存成功");
            } else {
                $mdl_user->rollback();
                $this->form_response_msg("保存失败，请稍后再试");
            }

        } else {
            $this->setData($mdl_user->getBookingFee($user), 'bookingfee');
            $this->setData($mdl_user->getBookingFeeType($user), 'bookingfeetype');
            $this->setData('其他设置', 'pagename');
            $this->setData('business_setting', 'menu');
            // $this->setData( 'business_setting', 'submenu' );
            $this->setData('其他设置 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->display('company/other_options');
        }
    }

    function customer_rating_list_action()
    {

        $mdl_customer_rating_list = $this->loadModel('wj_customer_rating');

        $pageSql = $mdl_customer_rating_list->getListSql(null, array('business_userId' => $this->loginUser['id'], 'isapproved' => '1'), 'createTime desc');
        
        $pageUrl = $this->parseUrl()->set('page');
        $pageSize = 10;
        $maxPage = 10;
        $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
        $data = $mdl_customer_rating_list->getListBySql($page['outSql']);

        
        $this->setData($data, 'data');
        $this->setData($page['pageStr'], 'pager');

        $this->setData('客户评价', 'pagename');
        $this->setData('online_center', 'menu');
        $this->setData('customer_rating_list', 'submenu');
        $this->setData('客户评价 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
        $this->display('company/customer_rating_list');

    }

   


    function customers_rating_edit_action()
    {
        $mdl_user = $this->loadModel('user');
        $mdl_customer_rating = $this->loadModel('wj_customer_rating');
        $mdl_order = $this->loadModel("order");
        
       
        $orderId = trim(get2('orderId'));

        $order = $mdl_order->getByOrderId($orderId);

        if($order['userId']!=$this->loginUser['id'])
            $this->sheader(null,'越权查看');

        $business_id=$order['business_userId'];

        if (is_post()) {
            $score = floatval(post('score'));
            $desc = trim(post('desc'));

            $score = $score/2.0;
            if ($business_id == $this->loginUser['id']) {
                $this->form_response_msg('您无法给自己做评价！');
            }

            if($score==0){
                 $this->form_response_msg('请选择一个分数');
            }
            
            $data = array(
                'orderId' => $orderId,
                'userId' => $this->loginUser['id'],
                'user_nickname' => $mdl_user->getUserDisplayName($this->loginUser['id']),
                'business_userId' => $business_id,
                'business_name' => $mdl_user->getBusinessDisplayName($business_id),
              
                'score_avg' => $score,
                'description' => $desc,

                'createTime' => time(),
                'ip_address' => ip(),
                'isApproved' => '1',
            );

            try {
                $mdl_customer_rating->insert($data);
               
                $mdl_order->updateByWhere(array('rated'=>1), array('orderId' => $orderId));

                $this->form_response(200,(string)$this->lang->submit_success,HTTP_ROOT_WWW.'member/myorders?filter=pleaseRate');

            } catch (Exception $e) {
                $this->form_response_msg((string)$this->lang->submit_error);
            }

        } else {
            $this->setData($mdl_user->getBusinessDisplayName($business_id), 'business_name');
            $this->setData($mdl_order->generateOrderName($orderId), 'order_name');

            $this->setData('客户评级', 'pagename');
            $this->setData('客户评级 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->display_pc_mobile('company/customers_rating_edit', 'company/customers_rating_edit');
        }
    }


    function busi_pay_setting_application_action()
    {
        $mdl_busi_pay_setting_application = $this->loadModel("wj_busi_pay_setting_application");
        $where = array(
            'userId' => $this->loginUser['id']
        );
        $data = $mdl_busi_pay_setting_application->getByWhere($where);

        if (is_post()) {

            $paypal_email = trim(post('pmxhpemail'));
            $epay_account = trim(post('emxhway'));

            if (strlen($paypal_email) < 6 || strlen($paypal_email) > 100) {
                $this->form_response_msg('请填写正确的支付信息');
            }
            
            $business_name = $this->loadModel('user')->getBusinessDisplayName($this->loginUser['id']);
            //如果是商家

            $data1 = array(
                'paypal_email' => $paypal_email,
                'epay_account' => $epay_account,
                'isApproved' => 2,
                'userId' => $this->loginUser['id'],
                'business_name' => $business_name,
                'createDate' => time()

            );

            //检查是否已经存在记录，如果存在记录则检查是否ABN 被修改，如果被修改需要重新认证。

            if ($data) {
                $succ = $mdl_busi_pay_setting_application->update($data1, $data['id']);
            } else {
                $succ = $mdl_busi_pay_setting_application->insert($data1);
            }


            if ($succ) {
                $this->form_response(200,'保存成功',HTTP_ROOT_WWW . 'company/busi_pay_setting_application');
            } else {
                $this->form_response_msg('修改失败');
            }

        } else {

            $this->setData($data, 'data');
            $this->setData('商家支付设置申请', 'pagename');
            $this->setData('advanced_setting', 'menu');
            $this->setData('busi_pay_setting_application', 'submenu');
            $this->setData('商家支付设置申请 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->display('company/busi_pay_setting_application');
        }
    }


    function abn_application_action()
    {
        $mdl_abn_application = $this->loadModel("wj_abn_application");

        $where = array(
            'userId' => $this->loginUser['id']
        );
        $data = $mdl_abn_application->getByWhere($where);

        if (is_post()) {

            $ABNorACN = trim(post('ABNorACN'));
            $untity_name = trim(post('untity_name'));

            if (strlen($ABNorACN) < 8 || !is_numeric($ABNorACN) || strlen($ABNorACN) > 15) {
               
                $this->form_response_msg('请填写正确的ABN或ACN(大于8位-15位数字，可包含空格，不能有字符');
            }

            $business_name = $this->loadModel('user')->getBusinessDisplayName($this->loginUser['id']);
            //如果是商家

            $data1 = array(
                'ABNorACN' => $ABNorACN,
                'isApproved' => 2,
                'userId' => $this->loginUser['id'],
                'business_name' => $business_name,
                'createDate' => time(),
                'untity_name' => $untity_name
            );
            //检查是否已经存在记录，如果存在记录则检查是否ABN 被修改，如果被修改需要重新认证。

            if ($data) {
                $succ = $mdl_abn_application->update($data1, $data['id']);
            } else {
                $succ = $mdl_abn_application->insert($data1);
            }

            if ($succ) {
                $this->form_response(200,'申请成功',HTTP_ROOT_WWW.'company/abn_application');
            } else {
                $this->form_response_msg('修改失败');
            }

        } else {

            $this->setData($data, 'data');
            $this->setData('ABN/ACN认证申请', 'pagename');
            $this->setData('basic_setting', 'menu');
            $this->setData('abn_application', 'submenu');
            $this->setData('ABN/ACN认证申请 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->display('company/abn_application');
        }
    }

    function abn_destory_action()
    {
        $mdl_abn_application = $this->loadModel("wj_abn_application");
        $where = array(
            'userId' => $this->loginUser['id']
        );
        $data = $mdl_abn_application->getByWhere($where);

        $data1 = array(
            'isApproved' => 0
        );

        $succ = $mdl_abn_application->update($data1, $data['id']);
        
        $this->sheader(HTTP_ROOT_WWW.'company/abn_application');

    }

    function busi_pay_setting_destory_action()
    {
        $mdl_busi_pay_setting_application = $this->loadModel("wj_busi_pay_setting_application");
        $where = array(
            'userId' => $this->loginUser['id']
        );
        $data = $mdl_busi_pay_setting_application->getByWhere($where);

        $data1 = array(
            'isApproved' => 0
        );
        $succ = $mdl_busi_pay_setting_application->update($data1, $data['id']);
        
        $this->sheader(HTTP_ROOT_WWW.'company/busi_pay_setting_application');
    }

   

    function logo_action()
    {
        if (is_post()) {

            $images = post('images');

            foreach ($images as $key => $value) {
                if($value=="default/image_upload.jpg")
                    unset($images[$key]);
                else
                    $images[$key]=trim($value);
            }

            
            $data['logo']=trim(reset($images));

            $mdl_user = $this->loadModel('user');
            if ($mdl_user->updateUserById($data, $this->loginUser['id'])) {
                $this->form_response_msg( '保存logo成功');
            } else {
                $this->form_response_msg('保存logo失败');
            }
        } else {
            $this->setData('修改logo - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->setData('basic_setting', 'menu');
            $this->setData('logo', 'submenu');
            $this->display('company/logo');
        }
    }
	
	  function weixin_bangding_action()
    {
      
            $this->setData('Wechat绑定 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->setData('advanced_setting', 'menu');
            $this->setData('weixin_bangding', 'submenu');
            $this->display('company/weixin_panding');
       
    }
	
	  function weixin_kefu_action()
    {
      
            $this->setData('关注服务号 -接收客服发送客服信息 ' . $this->site['pageTitle'], 'pageTitle');
            $this->setData('advanced_setting', 'menu');
            $this->setData('weixin_kefu', 'submenu');
            $this->display('company/weixin_kefu');
       
    }


    function coupons_template_action()
    {

        $type1 = get2('type1');
        $type2 = get2('type2');

        $this->setData('Ubonus模板 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
        $this->setData('publishment', 'menu');

        $this->setData($type1, 'type1');
        $this->setData($type2, 'type2');


         /**
         * 分类树形结构数据
         */
        $this->setData( $this->loadModel( 'infoClass' )->getChild4( '106' ), 'categories' );
                    
        $this->setData('coupons_template', 'submenu');
        $this->setData('模板中心', 'pagename');
        $this->display('company/coupons_template');

        //var_dump($data);
    }

    function coupons_template_ajax_action()
    {
        $cid = get2('cid');
        $type1 = get2('type1');
        $type2 = get2('type2');

        $mdl_coupons = $this->loadModel('coupons');

        $data = $mdl_coupons->getCouponsTemplatesByCategory($cid, $type1, $type2);

        $mdl_coupon_type = $this->loadModel('coupon_type');

        foreach ($data as $key => $value) {
            $mdl_coupons->caculatePriceAndPoint($data[$key]);
            $data[$key]['coupontype'] = $mdl_coupon_type->get($value['bonusType']);
        }

        $this->setData($data, 'data');
        $html = $this->fetch('company/coupons_template_each');
        echo $html;
    }

    function coupons_copy_action()
    {
        $id = (int)get2('id');
        $type = get2('coupon_type');

        $mdl_coupons = $this->loadModel('coupons');
        $mdl_coupons_sub = $this->loadModel("coupons_sub");

        $coupon = $mdl_coupons->get($id);

        if ($id > 0) {
            $newid = $mdl_coupons->copy($id, $this->loginUser['id'], $this->loginUser['businessName']);

            if ($newid) {
                $where['parent_coupon_id']=$id;
                $sub_coupon_list = $mdl_coupons_sub->getList('id',$where);
                foreach ($sub_coupon_list as $c) {
                    $sub_id=$mdl_coupons_sub->copy($c['id'],$newid,$this->loginUser['id']);
                }

            } 
        }

        $this->sheader(HTTP_ROOT_WWW."company/coupons?coupon_type=$type");


    }

    function coupons_publish_ajax_action()
    {
        $id = (int)get2('id');
        $mdl_coupons = $this->loadModel('coupons');

        $coupon = $mdl_coupons->get($id);

        if ($id < 0) $this->form_response_msg('coupon id invalid');

        if ($coupon['createUserId'] != $this->loginUser['id']) $this->form_response_msg('coupon do not belong to you');

        $data = array();
        $data['status'] = ($coupon['status'] == '4') ? '1' : '4';

        if ($mdl_coupons->update($data, $coupon['id'])) {
            echo json_encode(array('coupon_status' => $data['status']));
        } else {
            $this->form_response_msg('Please try again later');
        }


    }
	



	 function article_publish_ajax_action()
    {
        $id = (int)get2('id');
        $mdl_article = $this->loadModel('article');

        $article = $mdl_article->get($id);

        if ($id < 0) $this->form_response_msg('article id invalid');

        if ($article['createUserId'] != $this->loginUser['id']) $this->form_response_msg('article do not belong to you');

        $data = array();
        $data['status'] = ($article['status'] == '4') ? '1' : '4';

        if ($mdl_article->update($data, $article['id'])) {
            echo json_encode(array('article_status' => $data['status']));
        } else {
            $this->form_response_msg('Please try again later');
        }


    }
	
    function single_coupon_redeem_ajax_action()
    {
        $id = (int)get2('id');

        if ($id <= 0) $this->form_response_msg('coupon id invalid');

        $mdl_wj_coupons = $this->loadModel('wj_customer_coupon');

        $coupon = $mdl_wj_coupons->get($id);

        //if($coupon['createUserId']!=$this->loginUser['id'])$this->form_response_msg('coupon do not belong to you');
        $data = array();
        $data['coupon_status'] = ($coupon['coupon_status'] == 'c01') ? 'b01' : 'c01';

        if ($mdl_wj_coupons->update($data, $coupon['id'])) {
            echo json_encode(array('coupon_status' => $data['coupon_status']));
        } else {
            $this->form_response_msg('Please try again later');
        }


    }

     function update_price_action()
    {
		 $id = post('id');
		//检查改产品是否为改商家所有
		
		$mdl_coupon =$this->loadModel("coupons");
		
		$sql ="select id from cc_coupons  where id=".$id. " and createUserId=".$this->loginUser['id'];
		
		// $this->form_response(600,$sql,$sql);
		if( !$mdl_coupon->getListBySql($sql)) {
			
			$this->form_response(600,'未发现产品','未发现产品');
			return;
		}
		
	     if(is_post()){
            //json reply
            $id = post('id');
            $price = post('price');
			$data=array(
			 'voucher_deal_amount'=>$price
			);
			 try {
                $mdl_coupon->update($data,$id);

                $this->form_response(200,'','');
            } catch (Exception $e) {
                $this->form_response(500, $e->getMessage(),'');
            }

        }else{
            //web page reply
            $orderId = get2('id');

            try {
               
            } catch (Exception $e) {
                $this->sheader(null,$e->getMessage());
            }
        }
    }

 public function update_delivery_time_action()
    {
		 $id = post('id');
		// $id =get2('id');
		//检查改产品是否为改商家所有 factroy_id 21442 
		
		$mdl_order =$this->loadModel("order");
		$curr_user_id =$this->current_business['id'];
		
		$sql ="select id from cc_order  where id=$id and  ( business_userId=$curr_user_id " ;  // 商家本身 
		$sql .=" or business_userId in (select customer_id  from cc_authrise_manage_other_business_account where authorise_business_id =$curr_user_id )"; //是否为授权用户
		$sql .=" or business_userId in (select business_id  from cc_dispatching_centre_customer_list where dispatching_centre_id =$curr_user_id )";//是否为配货中心用户
		$sql .=" or business_userId in (select suppliers_id  from cc_freshfood_disp_centre_suppliers where business_id =$curr_user_id )";//是否为统一商铺用户
		$sql .=" or business_userId in (select customer_id  from cc_factory2c_list where factroy_id =$curr_user_id )";//是否为其管辖的2c附店客户
		$sql .=" or business_userId in (select cc_logistic_customers_id  from  cc_freshfood_logistic_customers where cc_logistic_business_id =$curr_user_id )";//是否为其管辖的物流客户
		$sql .=" or business_userId in (select customer_id  from  cc_factory_2blist where factroy_id =$curr_user_id )";//是否为其管辖的2b客户
		$sql .=" )";
		//var_dump($sql);exit;
		// $this->form_response(600,$sql,$sql);
		if( !$mdl_order->getListBySql($sql)) {
			
			$this->form_response(600,'Did not find items!','Did not find items!');
			return;
		}
		
	     if(is_post()){
            //json reply
            $id = post('id');
            $delivery_time = post('delivery_time');
            $delivery_time_stamp =strtotime($delivery_time);
            $seq_number = $mdl_order->generateLogisticSequence($this->current_business['id'],$delivery_time_stamp);
           //  $this->form_response(500,$seq_number);
			$data=array(
			 'logistic_delivery_date'=>$delivery_time_stamp,
			 'logistic_sequence_No'=>$seq_number,
			 'logistic_stop_No'=>0,
			 'logistic_truck_No'=>0
			);
			 try {
                $mdl_order->update($data,$id);

                $this->form_response(200,'','');
            } catch (Exception $e) {
                $this->form_response(500, $e->getMessage(),'');
            }

        }else{
            //web page reply
            $orderId = get2('id');

            try {
               
            } catch (Exception $e) {
                $this->sheader(null,$e->getMessage());
            }
        }
    }
	
	 public function merge_order_update_action()
    {
		 $id = get2('id');
        //echo json_encode(array('merge_to_another_order' => 1));
		//检查改产品是否为改商家所有
	//	$id ='26902';
		$mdl_order =$this->loadModel("order");
		
		// 销售员的用户role 为101

		  if($this->loginUser['role']==20) {
			  
			  $business_userid = $this->loginUser['user_belong_to_user'];
			  
		  }else{
			  
			   $business_userid = $this->loginUser['id'];
			  
		  }

		if( !$mdl_order->check_if_order_belong_to_login_user($business_userid,$id)) {
			
			$this->form_response(600,'Did not find items!','Did not find items!');
			return;
		}
		
		
		
		$first_rec = $mdl_order->get_first_order_sameuserId_sameday ($id);
		//var_dump($first_rec);exit;
		if( !$first_rec) {

            echo json_encode(array('merge_to_another_order' => 3));
			return;
		}
		if($first_rec['sent_to_xero']==1) {
            echo json_encode(array('merge_to_another_order' => 2)); //main order has been sent to xero
            return;
        }
		
		$error =$mdl_order->merge_order($id,$first_rec);
		
		
		
		
	  
   		
		
			
			if ( !$error) {
                //更新箱数;
                $this->loadModel('boxNumberOutput')->UpdateOrderBoxInfo($first_rec['orderId']);
				echo json_encode(array('merge_to_another_order' => 1));
			} else {
				echo json_encode(array('merge_to_another_order' => 7));
			}
			
			

      
    }


     function update_pickup_location_action()
    {
		 $id = post('id');
		//检查改产品是否为改商家所有
		
		$mdl_order =$this->loadModel("order");
		
		$sql ="select id from cc_order  where id=".$id. " and business_userId=".$this->loginUser['id'];
		
		// $this->form_response(600,$sql,$sql);
		if( !$mdl_order->getListBySql($sql)) {
			
			$this->form_response(600,'未发现产品','未发现产品');
			return;
		}
		
	     if(is_post()){
            //json reply
            $id = post('id');
            $pickup = post('pickup');
			$data=array(
			 'business_staff_id'=>$pickup
			);
			 try {
                $mdl_order->update($data,$id);

                $this->form_response(200,'','');
            } catch (Exception $e) {
                $this->form_response(500, $e->getMessage(),'');
            }

        }else{
            //web page reply
            $orderId = get2('id');

            try {
               
            } catch (Exception $e) {
                $this->sheader(null,$e->getMessage());
            }
        }
    }


 function update_qty_action()
    {
		 $id = post('id');
		  $qty = post('qty');
		//检查改产品是否为改商家所有
		
		$mdl_coupon =$this->loadModel("coupons");
		
		$sql ="select id from cc_coupons  where id=".$id. " and createUserId=".$this->loginUser['id'];
		
		// $this->form_response(600,$id,$id);
		if( !$mdl_coupon->getListBySql($sql)) {
			
			$this->form_response(600,'未发现产品','未发现产品');
			return;
		}
		
	     if(is_post()){
            //json reply
            $id = post('id');
            $qty = post('qty');
			$data=array(
			 'qty'=>$qty
			);
			 try {
                $mdl_coupon->update($data,$id);

                $this->form_response(200,'','');
            } catch (Exception $e) {
                $this->form_response(500, $e->getMessage(),'');
            }

        }else{
            //web page reply
            $orderId = get2('id');

            try {
               
            } catch (Exception $e) {
                $this->sheader(null,$e->getMessage());
            }
        }
    }

    public function test_customer_coupon_approving_action(){
        $this->_customer_coupon_approving('20220415010709228262');
}
    function customer_coupon_approving_action()
    {
		
		$sys_op=get2('sys_op');
		
        if(is_post()){
            //json reply
            $orderId = post('id');

            try {
                $this->_customer_coupon_approving($orderId);
               
                $this->form_response(200,'','');
            } catch (Exception $e) {
                $this->form_response(500, $e->getMessage(),'');
            }

        }else{
            //web page reply
            $orderId = get2('id');

            try {
				//var_dump($sys_op);exit;
               $this->_customer_coupon_approving($orderId);
               if($sys_op) {
				   
				   $this->sheader(HTTP_ROOT_WWW.'index.php?con=admin&ctl=adv/customer_coupon_process&act=detail&id='.$orderId);
			   }else{
                  $this->sheader(HTTP_ROOT_WWW.'member/exchange_detail?type=member&id='.$orderId);
			   }
            } catch (Exception $e) {
                $this->sheader(null,$e->getMessage());
            }
        }
    }
	
  function update_payment_status_action()
    {
		
		
			$mdl_order =$this->loadModel('order');
        if(is_post()){
            //json reply
            $orderId = post('id');

            try {
                //判断用户是否为授权用户；
			
				$where =array(
				 'orderId'=>$orderId
				);
				$order_rec =$mdl_order->getListBySql("select * from cc_order where orderId=$orderId");
				//var_dump($order_rec);exit;
				if($order_rec) {
					if($order_rec[0]['business_userId'] ==$this->loginUser['id']) {
						//var_dump('find');exit;
						$isAuthoriseCustomer =true;
					}else{
						//var_dump($order_rec);exit;
					  $mdl  = $this->loadModel('authrise_manage_other_business_account');
				      $isAuthoriseCustomer =Authorise_Center::getIsCustomerIdIsAuthorised($this->loginUser['id'],$$order_rec['business_userId']);	
				 		
					}
					
					if($isAuthoriseCustomer) {
						
						$data=array(
						  'status'=>1
						);
						//var_dump($order_rec[0]['id']);exit;
						if($mdl_order->update($data,$order_rec[0]['id'])){
							  $this->form_response(200,'','');
						}else{
							
							$this->form_response(500, 'can not update','');
						}
						
					}
					
				}else{
					$this->form_response(500, 'no find orderId','');
				}
				
				
				
				
               
              
            } catch (Exception $e) {
                $this->form_response(500, $e->getMessage(),'');
            }

        }
    }
	
	


    private  function _customer_coupon_approving($orderId)
    {
        
        $mdl_user = $this->loadModel('user');
        $mdl_order = $this->loadModel('order');
        $where = array(
            'orderId' => $orderId
        );
        $order = $mdl_order->getByWhere($where);

        $mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');
        $where1 = array(
            'order_Id' => $orderId
        );
        $customer_coupon = $mdl_wj_customer_coupon->getByWhere($where1);

        /**
        * 验证操作用户
        */
        
        $staff_id = $customer_coupon['business_staff_userid'];
/*
        if ($order['business_userId'] != $this->loginUser['id'] && (strpos($staff_id, $this->loginUser['id']) == false && $this->loadModel('redeem_staff')->isRedeemStaff($this->loginUser['id']) == false)) {

            // $this->sheader(null,'操作员工既不是商家本人，也不是门店员工，也不是兑付员工，尝试非法操作！终止');
            throw new Exception("操作员工既不是商家本人，也不是门店员工，也不是兑付员工，尝试非法操作！终止", 1);
        }
*/
        if ($order['coupon_status'] != 'c01') {
            // $this->sheader(null,'只能兑付C01订单(还未兑付或还未取消))');
            throw new Exception("只能兑付C01订单(还未兑付或还未取消)", 1);
        }

        $mdl_wj_user_coupon_activity_log = $this->loadModel('wj_user_coupon_activity_log');

        $cn_new_status = $this->loadModel('coupons')->actionlist_info( 'b01');
        $en_new_status = $cn_new_status;
		
		// 如果该voucher 为多次使用voucher ,如果非多次使用和已经使用完成的，走else ,否则走
		if(($order['multi_use']>1 && ($order['multi_use']==$order['multi_used']+1)) || $order['multi_use']<=1) {
		//	var_dump('yes');exit;
			if($order['multi_use']>1 && ($order['multi_use']==$order['multi_used']+1)){
				// 如果多次订单，已经使用满，则将 multi_used+1 ,然后按照正常流程处理。
				$order_update_data=array(
				  'multi_used'=>$order['multi_used'] +1
				);
				$mdl_order->update($order_update_data,$order['id']);
			}
			
			$mdl_order->begin();

			/**
			 * 结算账款
			 */
			$mdl_recharge = $this->loadModel('recharge');
		//	$mdl_recharge->updataTransactionStatus($orderId, BalanceProcess::SETTLE);
            $mdl_statement=$this->loadModel('statement');

			 /**
			 * 更新状态
			 */
			$data_update_order = array(
				'coupon_status' => 'b01',
				'cn_coupon_status_name' => $cn_new_status,
				'en_coupon_status_name' => $en_new_status
			);
			$mdl_order->updateByWhere($data_update_order, $where);
			$mdl_wj_customer_coupon->updateByWhere($data_update_order, $where1);

			/**
			 * 新增log记录
			 */
			$mdl_wj_user_coupon_activity_log
				->orderId($orderId)
				->userId($this->loginUser['id'])
				->userName($mdl_user->getBusinessDisplayName($this->loginUser['id']))
				->actionId('b01')
				->log();

            /**
             * 新增statement 记录
             */

            $balance =$mdl_statement->getBalanceAmountOfCustomer($order['business_userId'],$order['userId']);
            $balance_due =$order['money_new']+$balance;

            if(!$order['xero_invoice_id']) {
                $ref_customer_id =$order['xero_invoice_id'];
            }else{
                $ref_customer_id =$order['id'];
            }
            $data=array(
                'create_user'=>$this->loginUser['id'],
                'gen_date'=>time(),
                'invoice_number'=>$order['id'],
                'type_code'=>1001,
                'factory_id'=>$order['business_userId'],
                'customer_id'=>$order['userId'],
                'customer_ref_id'=>$ref_customer_id,
                'debit_amount'=>$order['money_new'],
                'balance_due'=>$balance_due,
                'credit_amount'=>0.00,
                'gst'=>0.00,
                'is_settled'=>0,
                'overdue_date'=>$this->loadModel('user_factory')->getUserOverDueDate($order['business_userId'],$order['userId'])
            );

            $mdl_statement->insert($data);

            //生成Invocie数据
            $fileattr = $this->order_invoice($orderId,'fileCC');
            $filenameWithPath =$fileattr['filePath'].$fileattr['fileName'];
            $data_invoice=array(
                'factory_id'=>$order['business_userId'],
                'gendate'=>time(),
                'createUserId'=>$this->loginUser['id'],
                 'type'=>1,
                'customer_id'=>$order['userId'],
                'invoiceId'=>$order['id'],
                'amount'=>$order['money_new'],
                'creditOrDebit'=>1,
                'filepathname'=>$filenameWithPath,
                'isAvaliable'=>1
            );

            $this->loadModel('invoice_list')->insert($data_invoice);


			if ($mdl_order->errno()) {
				$mdl_order->rollback();
			} else {
                //$mdl_order->rollback();
                $mdl_order->commit();
                //如果成功则生成invoice 文件 ，并发送欸客户户;
                $this->sendInvoice($fileattr,$mdl_order,$mdl_user,$orderId) ;

			}
			
		}else {
          //  var_dump('no');exit;
			$data_update_order = array(
				'cn_coupon_status_name' => '部分使用',
				'en_coupon_status_name' => 'parts of used',
				 'multi_used'=>$order['multi_used'] +1
			);
			$mdl_order->updateByWhere($data_update_order, $where);
				
			$mdl_wj_user_coupon_activity_log
				->orderId($orderId)
				->userId($this->loginUser['id'])
				->userName($mdl_user->getBusinessDisplayName($this->loginUser['id']))
				->actionId('p01')
				->log();    
			
		}
    }





    public function customer_order_detail_action()
    {	
    	require_once( DOC_DIR.'static/4pxAPI.php' );

        //商家查看的订单详情以及操作
        $orderId = trim(get2('id'));
		
		

        $mdl_order = $this->loadModel('order');
        $mdl_coupons = $this->loadModel('coupons');
        $mdl_wj_customer_coupon=$this->loadModel('wj_customer_coupon');

        //订单信息
        $data = $mdl_order->getByOrderId($orderId);
		
		// 获得该商家的精确订单汇总 （可能上面的是含多个商家的汇总）
		$busi_id=$this->current_business['id'];
        $sql1= "select sum(voucher_deal_amount*customer_buying_quantity) as ori_sum ,sum(adjust_subtotal_amount) as ajust_sum from cc_wj_customer_coupon  where order_id=$orderId and  business_id=$busi_id";
		//var_dump($sql1);exit;
		$data1 =$mdl_wj_customer_coupon->getListBySql($sql1);
		$data['money']=$data1[0]['ori_sum'];
		$data['ajust_sum']=$data1[0]['ajust_sum'];
		
		//coding end 

        $operator = $this->current_business['id'];
      //  if($data['business_userId']!=$operator&&$data['business_staff_id']!=$operator&&!$this->loadModel('redeem_staff')->isRedeemStaff($operator))$this->sheader(null,'越权查看');

        $this->setData($data,'data');

        $moneyDetail=$mdl_order->getMoneyDetail1($orderId,$this->current_business['id']);
        $this->setData($moneyDetail,'moneyDetail');
		
		
				

        //订单详情
        $items = $mdl_wj_customer_coupon->getList(null,array('order_id'=>$orderId));
		
		// 获取当前订单和当前客户的关系
		// 返回结果： 
		// status 1 : 当前用户为普通查看者，不是订单的商家
		// status 2 : 当前用户为当前订单唯一商家。
		// status 3 : 当前用户为当前订单的某一个商家（该情况存在于如果该订单为统配中心订单的情况） 。
		// status 4 : 当前用户为配货中心管理用户，拥有完整权限管理权和数据管理权
		// 每种状态对应相应的显示页面。
		
		// 如果当前用户就是订单中的商家，代表，其是通配中心商家。
		if($data['business_userId']==$this->current_business['id']){
			$display_status=4;
		}else{
			$order_details_and_related = $this->get_order_details_and_related($items,$this->current_business['id']);
			$items =$order_details_and_related['data'];
			$display_status = $order_details_and_related['status'];
		}
		
		//var_dump($items);exit;
		// 获得 退货reason_type 
		
		$this->setData($this->get_order_amend_reson_type_list(),'order_amend_reson_type_list');
		
		// 获取该订单变更信息
		$mdl_order_amend =$this->loadModel('order_amend');
		$item_order_amend =$mdl_order_amend->getList(null,array('order_id'=>$orderId));
		 foreach ($items as $key => $val) {
			
			 $items[$key]['reason_type'] ='0';
			 foreach ($item_order_amend as $key1 => $value) {
				 
					 if( $value['item_buying_id'] ==$val['id']  ){
						  $items[$key]['reason_type'] =$value['reason_type'];		
						  $items[$key]['reason_type_desc'] =$this->get_order_amend_reason_type_desc($value['reason_type']);							  
					 }
			  }
			  
		 }
        $this->setData($items,'items');
       // var_dump($items);exit;
        //allow 4px
        $allowFourpx = true;
        foreach ($items as $item) {
        	$coupon = $mdl_coupons->get($item['bonus_id']);
        	if (!$coupon['fourpx_sku']) {
        		$allowFourpx = false;//订单中有非云仓产品，无法一起云仓发货。
        		break;
        	}
        }
        $this->setData($allowFourpx,'allowFourpx');

        //desc info 
        $firstItemId = $items[0]['bonus_id'];

        $info = $mdl_coupons->get($firstItemId);
        $info['delivery_description'];
        $info['pickup_des'];
        $info['offline_pay_des'];

        //special info
        $info['visibleForBusiness'];//
        $info['bonusType'] ;//

        $this->setData($info,'info');

        //pickup Loaction
        $puckupLocation= $this->loadModel('user')->getUserById($data['business_staff_id']);
        $pickupInfo['pickupname']=$puckupLocation['contactPersonNickName'];
        $pickupInfo['pickupaddress']=$puckupLocation['googleMap'];
        $pickupInfo['pickupphone']=$puckupLocation['contactMobile'];

        $this->setData($pickupInfo,'pickupInfo');

        //group pin
        $mdl_group_pin = $this->loadModel('group_pin');
        $user_group_id = $mdl_group_pin->hasUserGroup($orderId);
        if($user_group_id){
            $userGroup=$this->loadModel('group_pin_user_group')->get($user_group_id);
            $this->setData($userGroup,'userGroup');
        }

        //log
        $activity_log=$this->loadModel('wj_user_coupon_activity_log')->getList(null,array('orderId'=>$orderId));
        foreach ($activity_log as $k => $l) {
            $activity_log[$k]['cn_description']=$mdl_coupons->actionlist_info($l['action_id']);
        }
        $this->setData($activity_log, 'log');

        //system log
        $this->loadModel('order_operation_log')->order($orderId)->log();

        
		// 检查当前商家settle的时间，如果settle超过14天，那么前面的价格不能再调整。
		
		// $mdl_settlement_log = $this->loadModel('settlement_log');
		// $settle_record = $mdl_settlement_log->getByWhere(array('user_id'=>$this->current_business['id']));
		 $days = (time()-$data['createTime'])/(24*60*60);

        $this->setData($mdl_order->isOrderEditable($orderId), 'editAble');
		 $this->setData($days,'days');
		
		
        $this->setData('online_center', 'menu');
        $this->setData('customer_coupon_process', 'submenu');

        $this->setData('订单详情 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');

        //根据状态转向不同的显示页面
		// status 1 : 当前用户为普通查看者，不是订单的商家 customer_order_detail_gen
		// status 2 : 当前用户为当前订单唯一商家。
		// status 3 : 当前用户为当前订单的某一个商家（该情况存在于如果该订单为统配中心订单的情况） 。
		// status 4 : 当前用户为配货中心管理用户，拥有完整权限管理权和数据管理权
		if($display_status==1) {
			$this->display('company/customer_order_detail_full_control');
		}else if($display_status==2) {
			$this->display('company/customer_order_detail_full_control');
		}else if($display_status==3) {
			$this->display('company/customer_order_detail_full_control');
		}else if($display_status==4) {
			$this->display('company/customer_order_detail_full_control');
		}else{
			$this->display('company/customer_order_detail_full_control');
		}
        
    }

    public function update_tracking_id_action()
    {
		$orderId= trim(get2('id'));
		$trackingId = trim(get2('trackingId'));
		$trackingOperator = trim(get2('trackingOperator'));

		require_once( DOC_DIR.'static/4pxAPI.php' );

		if (FourpxAPI::isFourpxTrackingOperator($trackingOperator)) {
			$order = $this->loadModel('order')->getByOrderId($orderId);
			$items = $this->loadModel('wj_customer_coupon')->getList(null,array('order_id'=>$orderId));

			$oconsignment_sku = [];
		    foreach ($items as $item) {
	        	$coupon = $this->loadModel('coupons')->get($item['bonus_id']);
	        	$oconsignment_sku [] = [
	        		"sku_code"=> $coupon['fourpx_sku'],    //Require
					"qty"=> $item['customer_buying_quantity'],	
					"stock_quality"=> "G"
	        	];
	        }

	        $trackingOperator = FourpxAPI::isCoveredArea(trim(get2('post_code')))
	        	?FourpxAPI::LOGISTICS_PRODUCT_MEL_DOMESTIC_DELIVERY
	        	:FourpxAPI::LOGISTICS_PRODUCT_AUPARCEL;

	        $fourpxRequestBody = [];
	        $fourpxRequestBody['ref_no'] = $orderId;
	        $fourpxRequestBody['oconsignment_sku'] = $oconsignment_sku;
	        $fourpxRequestBody['address'] = $order['address'];

			$fourpxRequestBody['country'] = trim(get2('country'));
			$fourpxRequestBody['state'] = trim(get2('state'));
			$fourpxRequestBody['city'] = trim(get2('city'));
			$fourpxRequestBody['post_code'] = trim(get2('post_code'));
			$fourpxRequestBody['street'] = trim(get2('street'));
			$fourpxRequestBody['house_number'] = trim(get2('house_number'));

			$fourpxRequestBody['last_name'] = $order['last_name'];
			$fourpxRequestBody['first_name'] = $order['first_name'];
			$fourpxRequestBody['phone'] = $order['phone'];
			$fourpxRequestBody['email'] = $order['email'];
			$fourpxRequestBody['logistics'] = $trackingOperator;

	    	$fourpx = new FourpxAPI();
	    	// $fourpx->enableSandBox(true);
	    	try {
	    		$res = $fourpx->outboundCreateAction($fourpxRequestBody);
	    		if ($res['result'] == FourpxAPI::API_CALL_SUCCESS) {
	    			$trackingId = $res['data']['consignment_no'];
	    		} else {
	    			$errormsg = $res['msg'].":";
	    			foreach ($res['errors'] as $error) {
	    				$errormsg .= $error['error_msg'] ."(". $error['error_data'] .");";
	    			}
	    			echo json_encode(['status'=>'error','msg'=>$errormsg]);
	    			exit;
	    		}
	    	} catch (Exception $e) {
	    		echo json_encode(['status'=>'error','msg'=>$e->getMessage()]);
	    		exit;
	    	}

	    	if ($trackingOperator === FourpxAPI::LOGISTICS_PRODUCT_AUPARCEL) {
	    		//澳邮需要异步获得tracking ID.
	    		try {
	    			$trackingId = $fourpx->getAusPostTrackingId($trackingId);
	    		} catch (Exception $e) {
	    			//timeout
	    			echo json_encode(['status'=>'error','msg'=>'澳邮发货失败，请联系管理员处理失败订单']);
	    			exit;
	    		}
	    	}

			$data['tracking_operator']=$trackingOperator;
			$data['tracking_id']=$trackingId;
			$where['orderId']=$orderId;

			$this->loadModel('order')->updateByWhere($data,$where);

			//new mail system
			$this->loadModel('system_notification_center')->notify(SystemNotification::BusinessDelivery,$orderId);

			echo json_encode(['status'=>'success','trackingId'=>$trackingId]);
		} else{
			$data['tracking_operator']=$trackingOperator;
			$data['tracking_id']=$trackingId;
			$where['orderId']=$orderId;

			$this->loadModel('order')->updateByWhere($data,$where);

			//new mail system
			$this->loadModel('system_notification_center')->notify(SystemNotification::BusinessDelivery,$orderId);
			echo json_encode(['status'=>'success','trackingId'=>$trackingId]);
		}
    }

    public function update_order_address_action()
    {
        $orderId= trim(get2('id'));
        $address = trim(get2('address'));

        $data['address']=$address;
        $where['orderId']=$orderId;

        $this->loadModel('order')->updateByWhere($data,$where);
    }

    public function update_order_phone_action()
    {
        $orderId= trim(get2('id'));
        $phone = trim(get2('phone'));

        $data['phone']=$phone;
        $where['orderId']=$orderId;

        $this->loadModel('order')->updateByWhere($data,$where);
    }

    public function update_order_email_action()
    {
        $orderId= trim(get2('id'));
        $email = trim(get2('email'));

        $data['email']=$email;
        $where['orderId']=$orderId;

        $this->loadModel('order')->updateByWhere($data,$where);
    }
	
	
	
   public function customer_orders_action($dataFomOtherMethod = [])
    {
        if($dataFomOtherMethod['file_path'] && $dataFomOtherMethod['business_id']) {
            $filePath = $dataFomOtherMethod['file_path'];
            $this->loginUser['id'] = $dataFomOtherMethod['business_id'];
        }

        $mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');
        $mdl_order = $this->loadModel('order');
        $mdl_user= $this->loadModel('user');


        /**
         * staff List
         */
        $where_staff = "((role = 5 and user_belong_to_user =".$this->current_business['id'].") or id = ".$this->current_business['id'].")";
        $list = $mdl_user->getList(null, $where_staff, 'createdDate asc');
		
		
        foreach ($list as $key => $value) {
			if( $list[$key]['contactPersonNickName']) {
				 $list[$key]['displayName']=$value['contactPersonNickName'];
			}else{
				 $list[$key]['displayName']=$mdl_user->getBusinessDisplayName($value['id']);
			}
          
        }
        $this->setData($list, 'staff_list');
        // var_dump($list);exit;
        /**
         * status List
         */

        /**
         * payment Type List
         */

         /**
         * customer_delivery_option Type List
         */
       
       
	    // 获得当前用户管理的商家列表信息
		// 该商家列表可能包括：
		// 如果该商家是集卖店的总店账户，可以看到集卖店所有的商家和自己
		// 如果该商家是统配中心的管理者，那么他可以看到统配中心下面所有商家的信息，如果某一个商家是工厂总店，那可以看到2c 或 2b的商家列表
		// 如果该用户是工厂总店，那么可以看到下面的2c客户的列表
		// 如果该用户是工厂总店，那么可以看到下面2b的客户列表
		
		
	   
	   
	   
	    $currentBusinessId = trim(get2('currentBusinessId'));
	
	  
	    $this->setData($currentBusinessId, 'currentBusinessId');

        $sk = trim(get2('sk'));
        $status = trim(get2('status'));
		$st=trim(get2('startTime'));
        $et=trim(get2('endTime'));
        $payment=trim(get2('payment'));
        $customer_delivery_option=trim(get2('customer_delivery_option'));
        $staff=trim(get2('staff'));
		$ifpaid=trim(get2('ifpaid'));
		if(!$ifpaid) {$ifpaid='all';}
	    //var_dump($ifpaid);exit;

        $this->setData($sk,'sk');
        $this->setData($status,'status');
		$this->setData($ifpaid,'ifpaid');
        $this->setData($st,'st');
        $this->setData($et,'et');
        $this->setData($payment,'payment');
        $this->setData($customer_delivery_option,'customer_delivery_option');
        $this->setData($staff,'staff');
		
		
		// 如果该商家是统配中心商家，那么查询条件增加一个
		
		$mdl_freshfood_disp_centre_suppliers=$this->loadModel('freshfood_disp_centre_suppliers');
		$sql_tongpei =" select * from cc_freshfood_disp_centre_suppliers where business_id =".$this->current_business['id'];
		$rec = $mdl_freshfood_disp_centre_suppliers->getListBySql($sql_tongpei);
		//var_dump($rec);exit;
		if($rec) {
			$this->setData(1,'dispatching_user');
			
		}
       // var_dump($currentBusinessId);exit;
		if( $currentBusinessId && $currentBusinessId !='all') {
		   
		       $sql = "SELECT cust.displayName,o.* ,cust.ori_sum from cc_order as o left join (select order_id,business_id,sum(voucher_deal_amount*customer_buying_quantity) as ori_sum ,uu. displayName from cc_wj_customer_coupon tt , cc_user uu where tt.business_id =uu.id  group by order_id,business_id) cust on o.orderId=cust.order_id and cust.business_id =".$currentBusinessId." left join cc_wj_user_coupon_activity_log as l on o.orderId=l.orderId and o.coupon_status=l.action_id ";
 
		}else{
		     $sql = "SELECT cust.displayName,o.* ,cust.ori_sum from cc_order as o left join (select order_id,business_id,sum(voucher_deal_amount*customer_buying_quantity) as ori_sum ,uu. displayName from cc_wj_customer_coupon tt , cc_user uu where tt.business_id =uu.id  group by order_id,business_id) cust on o.orderId=cust.order_id and cust.business_id =".$this->current_business['id']." left join cc_wj_user_coupon_activity_log as l on o.orderId=l.orderId and o.coupon_status=l.action_id ";

		
		}
   
      //  $whereStr.=" (business_userId= ".$this->current_business['id']." or  o.orderId in (select DISTINCT c.order_id from cc_wj_customer_coupon c where business_id = ".$this->current_business['id']."))";
        //var_dump($sql);exit;
        $whereStr.=" ( business_userId= ".$this->current_business['id'];
	  $whereStr.="  or  o.orderId in (select DISTINCT c.order_id from cc_wj_customer_coupon c where business_id = ".$this->current_business['id'].")";
	  //plus 如果该用户是统配中心用户，其下所有商家的订单
	  $whereStr.=" or  business_userId in (select business_id from  cc_dispatching_centre_customer_list where dispatching_centre_id =".$this->current_business['id'].")";
      //如果该商家是集合店铺所有人，则所有其下店铺的订单
	  $whereStr.=" or  business_userId in (select suppliers_id from  cc_freshfood_disp_centre_suppliers where business_id =".$this->current_business['id'].")";
	  // 如果该用户为授权用户，则其下所有订单均可以看到。
	  $whereStr.=" or  business_userId in (select customer_id from  cc_authrise_manage_other_business_account where authorise_business_id =".$this->current_business['id'].")";
      $whereStr.=" or  business_userId in (select customer_id from  	cc_factory2c_list where factroy_id =".$this->current_business['id'].")";
      $whereStr.=" or  business_userId in (select customer_id from  	cc_factory_2blist where factroy_id =".$this->current_business['id'].")";

      $whereStr.=")";
    



	  if (!empty($sk)) {
            $whereStr.=" and (o.redeem_code like  '%" . $sk . "%'";
            $whereStr.=" or o.last_name like  '%" . $sk . "%'";
            $whereStr.=" or o.phone like  '%" . $sk . "%'";
            $whereStr.=" or o.orderId like  '%" . $sk . "%'";
            $whereStr.=" or o.order_name like  '%" . $sk . "%'";
            $whereStr.=" or o.tracking_id like  '%" . $sk . "%'";
            $whereStr.=" or o.first_name like  '%" . $sk . "%'";
			$whereStr.=" or o.userId like  '%" . $sk . "%')";
            $where[]=$whereStr;
        }
        if (!empty($status)) {
            if ($status != 'all') {
               $whereStr.= " and o.coupon_status='$status' ";
            }
        }
	// var_dump($ifpaid);exit;
		if (!empty($ifpaid)) {
            if ($ifpaid != 'all') {
				if($ifpaid ==3) {
					 $whereStr.= " and o.status=0 ";
				}else{
					 $whereStr.= " and o.status='$ifpaid' ";
				}
              
            }
        }else{
			if($ifpaid==3) {
		 	 $whereStr.= " and o.status=0 ";
			}else{
				 $whereStr.= " and o.status='$ifpaid' ";
			}
		}
		
        if (!empty($payment)) {
            if ($payment != 'all') {
               $whereStr.= " and o.payment='$payment' ";
            }
        }
	
		 if (!empty($currentBusinessId)) {
            if ($currentBusinessId != 'all') {
             ///  $whereStr.= " and ( o.business_userId='$currentBusinessId' or cust.business_id =  '$currentBusinessId' ) ";
            }
        }
		
	
		
        if (!empty($customer_delivery_option)) {
            if ($customer_delivery_option != 'all') {
               $whereStr.= " and o.customer_delivery_option='$customer_delivery_option'";
            }
        }

         if (!empty($staff)) {
            if ($staff != 'all') {
               $whereStr.= " and o.business_staff_id = '$staff' ";
            }
        }

        /**
         * 全部订单删选已订单生成时间为基准
         */
        if ($status == 'all') {

            if (!empty($st)) {
                $st=strtotime($st." 00:00:00");
                $whereStr.= " and o.createTime>='$st'";
            }

            if (!empty($et)) {
                $et=strtotime($et." 23:59:59");
                $whereStr.= " and o.createTime<='$et'";
            }

        }else{

            if (!empty($st)) {
                $st=strtotime($st." 00:00:00");
                $whereStr.= " and l.gen_date>='$st'";
            }

            if (!empty($et)) {
                $et=strtotime($et." 23:59:59");
                $whereStr.= " and l.gen_date<='$et'";
            }
        }

        $pageSql=$sql . " where " . $whereStr . " order by createTime desc";
       // var_dump ($pageSql);exit;
        if(trim(get2('output'))=='pdf'){
			
			$where12=array(
					   'userId'=>$this->current_business['id']
					   
					);
			  $user_abn = $this->loadModel('wj_abn_application')->getByWhere($where12);
			
			
            $data = $mdl_order->getListBySql($pageSql);

            foreach ($data as $key => $value) {

                $data[$key]['items']=$mdl_wj_customer_coupon->getItemsInOrder($value['orderId'],$this->current_business['id']);
            }

            $this->loadModel('invoice');
            $report = new OrderInfoReport();
			$orderReport=$this->lang->ordersReport;
            $report->setStarttime(date('Y-m-d H:i:s',$st))
                ->setEndtime(date('Y-m-d H:i:s',$et))
                ->title($orderReport)
                ->OrderData($data);
            $report->generatePDF($this->lang);

            if($filePath) {//如果是系统内部调用会直接在指定路径创建文件
                $report->outPutToFile($filePath);
                return $filePath;
            }

			$report->outPutToBrowser(substr($user_abn['untity_name'],0,8).'-'.date('Y-m-d',$st).'-'.date('Y-m-d',$et).'_SellingDetails.pdf');
            exit;

        }elseif(trim(get2('output'))=='shippingLabel'){

            $fitInPage=(get2('fitInPage')=='true')?true:false;

            $pageSql=$sql . " where " . $whereStr . " order by o.address desc";

            $data = $mdl_order->getListBySql($pageSql);

            $lotteryUserList=array();
            foreach ($data as $key => $value) {
                $data[$key]['items']=$mdl_wj_customer_coupon->getItemsInOrder($value['orderId'],$this->current_business['id']);

                $data[$key]['redeemQRCode']=redeemQRCode($value['redeem_code']);

                if(trim(get2('with'))=='lottery'&&!in_array($value['userId'], $lotteryUserList)){
                    $data[$key]['hasLottery']=$this->loadModel('lottery')->getUserRewardItems($value['userId'],$value['business_userId']);
                    array_push($lotteryUserList, $value['userId']);
                }
            }
//var_dump($data);exit;
            $this->loadModel('invoice');
            $report = new shippingLabel();
            $report->setStarttime(date('Y-m-d H:i:s',$st))
                ->setEndtime(date('Y-m-d H:i:s',$et))
                ->title("Shipping Label")
                ->setReturnAddress($this->current_business['googleMap'])
                ->fitInPage($fitInPage)
                ->OrderData($data);
            $report->generatePDF();

            if($filePath) {//如果是系统内部调用会直接在指定路径创建文件
                $report->outPutToFile($filePath);
                return $filePath;
            }

            $report->outPutToBrowser("shippingLabel-".date('Y-m-d',$et));
            exit;
        }else{
            $pageUrl = $this->parseUrl()->set('page');
            $pageSize = 40;
            $maxPage = 10;
            $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);

            $data = $mdl_order->getListBySql($page['outSql']);

        }

        $this->setData($page['pageStr'],'pager');

        $this->setData($data,'data');

        $this->setData('online_center', 'menu');
        $this->setData('customer_coupon_process', 'submenu');
        
        $this->setData(HTTP_ROOT_WWW.'company/customer_orders', 'searchUrl');

        $this->setData($this->parseUrl(), 'currentUrl');

        $this->setData('客户订单中心 - ' . $this->site['pageTitle'], 'pageTitle');

        $this->display_pc_mobile('company/customer_orders','mobile/company/customer_orders');
    }
	
	
	   public function customer_orders_logistic_query_action()
    {

        $mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');
        $mdl_order = $this->loadModel('order');
        $mdl_user= $this->loadModel('user');


        /**
         * Driver List of Current Business
         */
		 
		$customer_delivery_date = trim(get2('customer_delivery_date'));
        $postcode = trim(get2('postcode'));

        $mdl_truck =  $this->loadModel('truck');
	//	if($customer_delivery_date){
	//			$all_avaliable_trucks = $mdl_truck->getAllTruckOfBusinessWithOrderCounts($this->current_business['id'],$customer_delivery_date);
	//	}else{
				$all_avaliable_trucks = $mdl_truck->getAllTruckOfBusiness($this->current_business['id']);
	//	}
		
	
		$this->setData($all_avaliable_trucks,'all_avaliable_trucks');
      //  var_dump($all_avaliable_trucks);exit;








        /**
         * staff List
         */
        $where_staff = "((role = 5 and user_belong_to_user =".$this->current_business['id'].") or id = ".$this->current_business['id'].")";
        $list = $mdl_user->getList(null, $where_staff, 'createdDate asc');
        foreach ($list as $key => $value) {
           $list[$key]['displayName']=$mdl_user->getBusinessDisplayName($value['id']);
        }
        $this->setData($list, 'staff_list');

        /**
         * status List
         */

        /**
         * payment Type List
         */

         /**
         * customer_delivery_option Type List
         */
        $three_days_times = time()-259200;
		
        $sql_avaliable_date =" SELECT DISTINCT o.logistic_delivery_date from (select * from cc_order where (`business_userId` = ". 
		$this->current_business['id'].") or (`business_userId` in (select DISTINCT business_id from cc_freshfood_disp_centre_suppliers where suppliers_id =".$this->current_business['id'].")) ) as o where o.logistic_delivery_date >".$three_days_times." order by logistic_delivery_date ";
       // var_dump($sql_avaliable_date);exit;
	   
	       $availableDates = $this->loadModel('freshfood_logistic_customers')->getAvaliableDateOfThisLogisiticCompany($this->current_business['id']);

	   // $availableDates = $this->loadModel('order')->getListBySql( $sql_avaliable_date);
    	$availableDates = array_map(function($d){
    		return date('Y-m-d',$d['logistic_delivery_date']);
    	}, $availableDates);
    	$this->setData($availableDates, 'availableDates');

        //存放当前日订单的postcode列表
         if($customer_delivery_date){

            $postcode_list = $mdl_order->getPostCodeGroupAndCountOfOrder($this->current_business['id'],$customer_delivery_date);
             $this->setData($postcode_list,'postcode_list');
         }



				
        $sk = trim(get2('sk'));
		$ifpaid=trim(get2('ifpaid'));
	
		
		$logistic_truck_No = trim(get2('logistic_truck_No'));
	
		
		$this->setData($logistic_truck_No,'logistic_truck_No');
		$this->setData($status,'status');
		$this->setData($ifpaid,'ifpaid');

        $TuckListOfTheDay =$this->loadModel('truck')->getAllOrdersTruckListwithCount($this->current_business['id'],$customer_delivery_date);
        $this->setData($TuckListOfTheDay,'TuckListOfTheDay');
		
		if($customer_delivery_date){
				$truckList = $mdl_truck->getAllOrdersTruckListwithCount($this->current_business['id'],$customer_delivery_date);
		}else{
			$truckList = $mdl_truck->getAllTruckOfBusiness($this->current_business['id']);
		}
		
	   	$this->setData($truckList,'truckList');
      //  var_dump(json_encode($truckList));exit;
        $customer_delivery_option=trim(get2('customer_delivery_option'));
        $staff=trim(get2('staff'));

        $this->setData($sk,'sk');
        $this->setData($customer_delivery_option,'customer_delivery_option');
        $this->setData($staff,'staff');
		$this->setData($customer_delivery_date,'customer_delivery_date');
        $this->setData($postcode,'postcode');

        $sql = "SELECT (SELECT  CEIL(sum( c.`new_customer_buying_quantity`/m.unitQtyPerBox ))     FROM `cc_wj_customer_coupon` c  left join cc_restaurant_menu m  on  c.`restaurant_menu_id` =m.id  WHERE order_id = o.orderId ) as boxes,f.nickname,o.* ,cust.ori_sum from cc_order as o left join cc_user_factory f on o.userId=f.user_id and o.business_userId = f.factory_id  left join (select order_id,business_id,sum(voucher_deal_amount*customer_buying_quantity) as ori_sum from cc_wj_customer_coupon group by order_id,business_id) cust on o.orderId=cust.order_id and cust.business_id =".$this->current_business['id']." left join cc_wj_user_coupon_activity_log as l on o.orderId=l.orderId and o.coupon_status=l.action_id ";

        $whereStr.=" (business_userId= ".$this->current_business['id']." or  o.orderId in (select DISTINCT c.order_id from cc_wj_customer_coupon c where business_id = ".$this->current_business['id']."))";
        //var_dump($sql);exit;
        if (!empty($sk)) {
            $whereStr.=" and (o.redeem_code like  '%" . $sk . "%'";
            $whereStr.=" or o.last_name like  '%" . $sk . "%'";
            $whereStr.=" or o.phone like  '%" . $sk . "%'";
            $whereStr.=" or o.orderId like  '%" . $sk . "%'";
            $whereStr.=" or o.order_name like  '%" . $sk . "%'";
            $whereStr.=" or o.tracking_id like  '%" . $sk . "%'";
            $whereStr.=" or o.first_name like  '%" . $sk . "%'";
			$whereStr.=" or o.userId like  '%" . $sk . "%')";
            $where[]=$whereStr;
        }
      
         
               $whereStr.= " and (o.coupon_status='c01' or o.coupon_status='b01' )";
       
		
		
               $whereStr.= " and (o.status =1 or o.accountPay=1) ";
        
		
        if (!empty($customer_delivery_option)) {
            if ($customer_delivery_option != 'all') {
               $whereStr.= " and o.customer_delivery_option='$customer_delivery_option'";
            }
        }

         if (!empty($staff)) {
            if ($staff != 'all') {
               $whereStr.= " and o.business_staff_id = '$staff' ";
            }
        }
		//deleivery date
		if (!empty($customer_delivery_date)) {
            if ($customer_delivery_date != 'all') {
               $whereStr.= " and  DATE_FORMAT(from_unixtime(o.logistic_delivery_date),'%Y-%m-%d') = '$customer_delivery_date' ";
            }else{
				$three_days_times = time()-259200;
				$whereStr.= " and  logistic_delivery_date > $three_days_times";
				 
				
			}
        }else {
			$three_days_times = time()-259200;
				$whereStr.= " and  logistic_delivery_date > $three_days_times";
		}
    	
	   if (!empty($logistic_truck_No)) {
		  
            if ($logistic_truck_No != 'all') {
               $whereStr.= " and  logistic_truck_No = '$logistic_truck_No' ";
			   
            }
        }
		
      if ($logistic_truck_No =='0' ) {
		       $whereStr.= " and  logistic_truck_No =0 ";
         // var_dump($logistic_truck_No);exit;
        }

      if($postcode){
          $whereStr.= " and  postalcode =$postcode ";

      }

        $pageSql=$sql . " where " . $whereStr . " order by DATE_FORMAT(from_unixtime(o.logistic_delivery_date),'%Y-%m-%d'),logistic_truck_No,logistic_stop_No";
      
    // var_dump($pageSql); exit;
            $pageUrl = $this->parseUrl()->set('page');
            $pageSize = 40;
            $maxPage = 10;
            $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);

            $data = $mdl_order->getListBySql($page['outSql']);

            foreach ($data as $key => $value) {
                $data[$key]['name'] =$this->getCustomerName($value);

            }

        $this->setData($page['pageStr'],'pager');

        $this->setData($data,'data');

        $this->setData('Logistic_centre', 'menu');
        $this->setData('customer_orders_logistic_query', 'submenu');
        
        $this->setData(HTTP_ROOT_WWW.'company/customer_orders_logistic_query', 'searchUrl');

        $this->setData($this->parseUrl(), 'currentUrl');

        $this->setData('Order Logisitic Schedule - ' . $this->site['pageTitle'], 'pageTitle');

        $this->display_pc_mobile('company/customer_orders_logistic_query','company/customer_orders_logistic_query');
    }
	
	
	
	
	
	 public function customer_orders_logistic_action()
    {
  
        $mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');
        $mdl_order = $this->loadModel('order');
        $mdl_user= $this->loadModel('user');

       
      

        /**
         * staff List
         */
        $where_staff = "((role = 5 and user_belong_to_user =".$this->current_business['id'].") or id = ".$this->current_business['id'].")";
        $list = $mdl_user->getList(null, $where_staff, 'createdDate asc');
        foreach ($list as $key => $value) {
           $list[$key]['displayName']=$mdl_user->getBusinessDisplayName($value['id']);
        }
        $this->setData($list, 'staff_list');

        /**
         * status List
         */

        /**
         * payment Type List
         */

         /**
         * customer_delivery_option Type List
         */
        $three_days_times = time()-2592000;
	    $availableDates = $this->loadModel('dispatching_centre_customer_list')->getAvaliableDateOfThisDispatchingCentre($this->current_business['id']);
    	$availableDates = array_map(function($d){
    		return date('Y-m-d',$d['logistic_delivery_date']);
    	}, $availableDates);
    	$this->setData($availableDates, 'availableDates');

         // var_dump($availableDates);exit;
        //** 获取该商家管辖的所有商家
		
		
		
		$suplierList=$this->loadModel('dispatching_centre_customer_list')->getALLAvaliableCustomerListsForCurrentBusiness($this->current_business['id'],$this->current_business['business_type_factory_2c']);
			//suplierList
		
		//var_dump($suplierList);exit;


	
		
	  
	 $this->setData($suplierList, 'newsuplierList');
		
	
		
		
		//** 获取结束



		//交易状态购买
		//if(!status) {
		$status ='c01';
		//}
		//支付状态
		$ifpaid=1;
		
		 $business_id = trim(get2('business_id'));
		 
		// 做到这里，如果 是suppliersID 且数据源!=1 则要使用cc_order_import 做为引导。
		
		if($this->loadModel('dispatching_centre_customer_list')->getIfBusinessIsExportDataSource($business_id)) {
			$export_data_source =1;
			$query_table_name='cc_order_import';
		}else{
			$export_data_source =0;
			$query_table_name='cc_order';
		}
		
		 //var_dump($export_data_source);exit;
		 $this->setData($business_id,'business_id');
		
		
		// 获得商家是否允许自提，以决定前端是否显示pickup 选择项
		
		$this->setData($this->current_business['pickup_avaliable'],'pickup_avaliable');
		//var_dump ($this->current_business['pickup_avaliable']);exit;
		
        $sk = trim(get2('sk'));
		
		$customer_delivery_date = trim(get2('customer_delivery_date'));
		
		$logistic_truck_No = trim(get2('logistic_truck_No'));
		$this->setData($logistic_truck_No,'logistic_truck_No');


        $TuckListOfTheDay =$this->loadModel('truck')->getAllOrdersTruckListwithCount($this->current_business['id'],$customer_delivery_date);
        $this->setData($TuckListOfTheDay,'TuckListOfTheDay');
     
        $customer_delivery_option=trim(get2('customer_delivery_option'));
        $staff=trim(get2('staff'));

        $this->setData($sk,'sk');
        $this->setData($customer_delivery_option,'customer_delivery_option');
        $this->setData($staff,'staff');
		$this->setData($customer_delivery_date,'customer_delivery_date');
		
		
		// 加入了一个前面可以选择一个商家，然后显示该商家的相关记录，如果商家id 为空，则保持原来的处理，如果不为空则进行相应的处理
		//获得该商家是否为外部数据源，如果是外部数据源，则需要使用外部订单总表关联
		
		
			  
			  
		if($business_id) {
			//var_dump($business_id);//exit;
			$sql = "SELECT f.nickname,o.* ,uu.displayName as name,cust.ori_sum from ".$query_table_name." as o  left join cc_user_factory f on o.userId =f.user_id and o.business_userId = f.factory_id left join cc_user uu on o.userId=uu.id  left join (select order_id,business_id,sum(voucher_deal_amount*customer_buying_quantity) as ori_sum from cc_wj_customer_coupon group by order_id,business_id) cust on o.orderId=cust.order_id and cust.business_id =".$this->current_business['id']." left join cc_wj_user_coupon_activity_log as l on o.orderId=l.orderId and o.coupon_status=l.action_id ";
            $whereStr.=" (business_userId= $business_id or  o.orderId in (select DISTINCT c.order_id from cc_wj_customer_coupon c where  (business_id =$business_id  or business_id in (select customer_id from cc_factory2c_list where factroy_id =$business_id ))) )";
      
			
		}else{
			
			  $sql = "SELECT  f.nickname, o.* ,uu.displayName as name ,cust.ori_sum from ".$query_table_name." as o  left join cc_user_factory f on o.userId =f.user_id and o.business_userId = f.factory_id  left join cc_user uu on o.userId=uu.id left join (select order_id,business_id,sum(voucher_deal_amount*customer_buying_quantity) as ori_sum from cc_wj_customer_coupon group by order_id,business_id) cust on o.orderId=cust.order_id and cust.business_id =".$this->current_business['id']." left join cc_wj_user_coupon_activity_log as l on o.orderId=l.orderId and o.coupon_status=l.action_id ";
          $whereStr.=" (business_userId= ".$this->current_business['id']." or  o.orderId in (select DISTINCT c.order_id from cc_wj_customer_coupon c where business_id = ".$this->current_business['id']."))";
     
			
		}
		

  
         //var_dump($sql);exit;
        if (!empty($sk)) {
            $whereStr.=" and (o.redeem_code like  '%" . $sk . "%'";
            $whereStr.=" or o.last_name like  '%" . $sk . "%'";
            $whereStr.=" or o.phone like  '%" . $sk . "%'";
            $whereStr.=" or o.orderId like  '%" . $sk . "%'";
            $whereStr.=" or o.order_name like  '%" . $sk . "%'";
            $whereStr.=" or o.tracking_id like  '%" . $sk . "%'";
            $whereStr.=" or o.first_name like  '%" . $sk . "%'";
			$whereStr.=" or o.userId like  '%" . $sk . "%')";
            $where[]=$whereStr;
        }
      
        
		 if (!empty($status)) {
          
               $whereStr.= " and o.coupon_status = '$status' ";
          
        }
		
		 if (!empty($ifpaid)) {
          
               $whereStr.= " and ( o.status = 1 or o.accountPay =1) ";
          
        }
		
		
        if (!empty($customer_delivery_option)) {
            if ($customer_delivery_option != 'all') {
               $whereStr.= " and o.customer_delivery_option='$customer_delivery_option'";
            }
        }

         if (!empty($staff)) {
            if ($staff != 'all') {
               $whereStr.= " and o.business_staff_id = '$staff' ";
            }
        }
		//deleivery date
		if (!empty($customer_delivery_date)) {
            if ($customer_delivery_date != 'all') {
				
				
			   require_once( DOC_DIR.'static/OptimoRoute.php');
				$opRoute = new OptimoRoute($this->current_business['id']);
				
				$opRoute->generateLogisticSequence($customer_delivery_date);
               $whereStr.= " and  DATE_FORMAT(from_unixtime(o.logistic_delivery_date),'%Y-%m-%d') = '$customer_delivery_date' ";
            }else{
				$three_days_times = time()-259200;
				$whereStr.= " and  logistic_delivery_date > $three_days_times";
				 
				
			}
        }else {
			$three_days_times = time()-259200;
				$whereStr.= " and  logistic_delivery_date > $three_days_times";
		}
    
	   if (!empty($logistic_truck_No)) {
            if ($logistic_truck_No != 'all') {
               $whereStr.= " and  logistic_truck_No = '$logistic_truck_No' ";
            }
        }
     

        $pageSql=$sql . " where " . $whereStr . " order by DATE_FORMAT(from_unixtime(o.logistic_delivery_date),'%Y-%m-%d'),logistic_truck_No,logistic_stop_No";
       //var_dump($pageSql);exit;
	   
	   	//检查该商家是否可以管理其它店铺，如果授权即可以该商家权限进入系统。
		
		// $mdl = $this->loadModel('authrise_manage_other_business_account');
        // $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($business_id);
		 
		 
	   
	   
        if(trim(get2('output'))=='pdf'){
			//var_dump ($pageSql);exit;
            $data = $mdl_order->getListBySql($pageSql);
           // var_dump($data);exit;
		   	$checkID=get2('checkID');
			$arr_print = explode(',',$checkID);
            foreach ($data as $key => $value) {
				
				 if(!in_array($value['id'], $arr_print)){
						unset($data[$key]); continue;
				 }
                if($business_id) {
					 $data[$key]['items']=$mdl_wj_customer_coupon->getItemsInOrder($value['orderId'],$business_id);
					
					
				}else{
					 $data[$key]['items']=$mdl_wj_customer_coupon->getItemsInOrder($value['orderId'],$this->current_business['id']);
					
				}
                //获得用户的名称
                $data[$key]['name'] =$this->getCustomerName($value);
               
            }

            $this->loadModel('invoice');
            $report = new OrderInfoReport();
            $report->setStarttime(date('Y-m-d H:i:s',$st))
                ->setEndtime(date('Y-m-d H:i:s',$et))
				->setCustomer_delivery_date($customer_delivery_date)
                ->title("配货单")
                ->OrderData($data);
            $report->generatePDF_logistic_list();
            $report->outPutToBrowser();
            exit;

        }elseif(trim(get2('output'))=='shippingLabel'){
			
			 

            $fitInPage=(get2('fitInPage')=='true')?true:false;

            $pageSql=$sql . " where " . $whereStr . " order by o.address desc";

            $data = $mdl_order->getListBySql($pageSql);
           // var_dump($pageSql);exit;
            $lotteryUserList=array();
			
			$checkID=get2('checkID');
			$arr_print = explode(',',$checkID);
			
            foreach ($data as $key => $value) {
					
				 if(!in_array($value['id'], $arr_print)){
						unset($data[$key]); continue;
				 }
				
				
					if($business_id) {
					//	var_dump($business_id);
					 $data[$key]['items']=$mdl_wj_customer_coupon->getItemsInOrder($value['orderId'],$business_id);
					// var_dump($data[$key]['items']);exit;
					
				}else{
					 $data[$key]['items']=$mdl_wj_customer_coupon->getItemsInOrder($value['orderId'],$this->current_business['id']);
					
				}

//var_dump($data[$key]['items']);exit;

                $data[$key]['redeemQRCode']=redeemQRCode($value['redeem_code']);

                if(trim(get2('with'))=='lottery'&&!in_array($value['userId'], $lotteryUserList)){
                    $data[$key]['hasLottery']=$this->loadModel('lottery')->getUserRewardItems($value['userId'],$value['business_userId']);
                    array_push($lotteryUserList, $value['userId']);
                }
                $data[$key]['name'] =$this->getCustomerName($value);
            }

            $this->loadModel('invoice');
            $report = new shippingLabel();
            if($this->current_business['logo']) {
                $report->logoPath('data/upload/' . $this->current_business['logo']);
            }
            $report->setStarttime(date('Y-m-d H:i:s',$st))
                ->setEndtime(date('Y-m-d H:i:s',$et))
                ->title("Shipping Label")
                ->setReturnAddress($this->current_business['googleMap'])
                ->fitInPage($fitInPage)
                ->OrderData($data);
            $report->generatePDF($this->lang);
            $report->outPutToBrowser();
            exit;
        }else{
            $pageUrl = $this->parseUrl()->set('page');
            $pageSize = 40;
            $maxPage = 10;
            $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);

            $data = $mdl_order->getListBySql($page['outSql']);

            foreach ($data as $key => $value) {
                $data[$key]['name'] =$this->getCustomerName($value);

            }

        }

        $this->setData($page['pageStr'],'pager');

        $this->setData($data,'data');

        $this->setData('dispatching_center', 'menu');
        $this->setData('customer_coupon_logistic', 'submenu');
        
        $this->setData(HTTP_ROOT_WWW.'company/customer_orders_logistic', 'searchUrl');

        $this->setData($this->parseUrl(), 'currentUrl');

        $this->setData('配货中心 - ' . $this->site['pageTitle'], 'pageTitle');

        $this->display_pc_mobile('company/customer_orders_logistic','company/customer_orders_logistic');
    }
	
	
	
	
	
	
	
	
	
	
	
	
    public function customer_orders_import_logistic_action()
    {
  
        
        $mdl_order = $this->loadModel('order');
        $mdl_user= $this->loadModel('user');


       

        $three_days_times = time()-2592000;
		
        $sql_avaliable_date =" SELECT DISTINCT o.logistic_delivery_date from (select * from cc_order where (`business_userId` = ". 
		$this->current_business['id'].") or (`business_userId` in (select DISTINCT business_id from cc_freshfood_disp_centre_suppliers where suppliers_id =".$this->current_business['id'].")) ) as o where o.logistic_delivery_date >".$three_days_times." order by logistic_delivery_date ";
      
	   $availableDates = $this->loadModel('order')->getListBySql( $sql_avaliable_date);
    	$availableDates = array_map(function($d){
    		return date('Y-m-d',$d['logistic_delivery_date']);
    	}, $availableDates);
    	$this->setData($availableDates, 'availableDates');

   


        //** 获取该商家管辖的所有商家
		
		
			//suplierList
		$sql = "select * from cc_dispatching_centre_customer_list where dispatching_centre_id = ". $this->current_business['id']." and data_source =2  order by sort ";
		$suplierList = loadModel('dispatching_centre_customer_list')->getListBySql($sql);

	if($suplierList) {
        // 如果某一个是统配店，那么拿到它的子店
		
		$newsuplierList=array();
		
		$index=0;
		 foreach ($suplierList as $key => $value) {
			 $newsuplierList[$index]=$value;
			 $index ++;
			 $where00 = array(
			  'business_id'	=>$value['business_id']		 
			 );
			  $dispacthing_busienss_list = $this->loadModel('freshfood_disp_centre_suppliers')->getList([],$where00);
			  if(count($dispacthing_busienss_list)>1) {

				  //var_dump($dispacthing_busienss_list)	  ;exit;
				  foreach ($dispacthing_busienss_list as $key1 => $value1) {
					  $newsuplierList[$index]['dispatching_centre_id']= $value1['business_id'];
					  $newsuplierList[$index]['dispatching_name']= $value['dispatching_name'].'-'.$value1['suppliers_name'];
					  $newsuplierList[$index]['business_id']= $value1['suppliers_id'];
					  $newsuplierList[$index]['business_name']= $value['dispatching_name'].'-'.$value1['suppliers_name'];
					  $newsuplierList[$index]['data_source']= 1;;
					 
					  
					  $index ++;  
				  }
				  
				  
				  
				  }
			 
		 }
		 
		 
		 //selectedSupplierName 
		$selectedSupplierName = 'Please select a business';
		for ($i=0; $i <count($newsuplierList) ; $i++) { 
			if ($newsuplierList[$i]['business_id'] == $bid) {
				$selectedSupplierName = $newsuplierList[$i]['business_name'];
				$selectedSupplierDataSource = $newsuplierList[$i]['data_source'];
				break;
			}
		}
		
		
		 if (count($newsuplierList)>1) {
			
			 $this->setData($newsuplierList, 'newsuplierList');
		 }
		
	}	
		
		
	

		//交易状态购买
		//if(!status) {
		$status ='c01';
		//}
		//支付状态
		$ifpaid=1;
		
		 $business_id = trim(get2('business_id'));
		 $this->setData($business_id,'business_id');
		
		
		// 获得商家是否允许自提，以决定前端是否显示pickup 选择项
		
		$this->setData($this->current_business['pickup_avaliable'],'pickup_avaliable');
		//var_dump ($this->loginUser['pickup_avaliable']);exit;
		
        $sk = trim(get2('sk'));
		
		$customer_delivery_date = trim(get2('customer_delivery_date'));
		
		$logistic_truck_No = trim(get2('logistic_truck_No'));
		$this->setData($logistic_truck_No,'logistic_truck_No');
		

		     
        $customer_delivery_option=trim(get2('customer_delivery_option'));
        $staff=trim(get2('staff'));

        $this->setData($sk,'sk');
        $this->setData($customer_delivery_option,'customer_delivery_option');
        $this->setData($staff,'staff');
		$this->setData($customer_delivery_date,'customer_delivery_date');
		
		
		// 加入了一个前面可以选择一个商家，然后显示该商家的相关记录，如果商家id 为空，则保持原来的处理，如果不为空则进行相应的处理
		
		if($business_id) {
			//var_dump($business_id);//exit;
			  $sql = "SELECT o.*  from cc_order as o ";
			  $whereStr.=" where business_userId =$business_id ";
     
			
		}else{
			
			  $sql = "SELECT o.*  from cc_order as o ";
			  $whereStr.=" where business_userId =".$this->loginUser['id'];
			
		}
		

  
         //var_dump($sql);exit;
        if (!empty($sk)) {
            $whereStr.=" and (o.redeem_code like  '%" . $sk . "%'";
            $whereStr.=" or o.last_name like  '%" . $sk . "%'";
            $whereStr.=" or o.phone like  '%" . $sk . "%'";
            $whereStr.=" or o.orderId like  '%" . $sk . "%'";
            $whereStr.=" or o.order_name like  '%" . $sk . "%'";
            $whereStr.=" or o.tracking_id like  '%" . $sk . "%'";
            $whereStr.=" or o.first_name like  '%" . $sk . "%'";
			$whereStr.=" or o.userId like  '%" . $sk . "%')";
            $where[]=$whereStr;
        }
      
        
		 if (!empty($status)) {
          
               $whereStr.= " and o.coupon_status = '$status' ";
          
        }
		
		 if (!empty($ifpaid)) {
          
               $whereStr.= " and o.status = '$ifpaid' ";
          
        }
		
		
       

	
		if (!empty($customer_delivery_date)) {
            if ($customer_delivery_date != 'all') {
               $whereStr.= " and  DATE_FORMAT(from_unixtime(o.logistic_delivery_date),'%Y-%m-%d') = '$customer_delivery_date' ";
            }else{
				$three_days_times = time()-259200;
				$whereStr.= " and  logistic_delivery_date > $three_days_times";
				 
				
			}
        }else {
			$three_days_times = time()-259200;
				$whereStr.= " and  logistic_delivery_date > $three_days_times";
		}
    
	  

        $pageSql=$sql . " where " . $whereStr . " order by DATE_FORMAT(from_unixtime(o.logistic_delivery_date),'%Y-%m-%d')";
       
	   
	 
		 
	   
	   
    
            $pageUrl = $this->parseUrl()->set('page');
            $pageSize = 40;
            $maxPage = 10;
            $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);

            $data = $mdl_order->getListBySql($page['outSql']);

     

        $this->setData($page['pageStr'],'pager');

        $this->setData($data,'data');

        $this->setData('dispatching_center', 'menu');
        $this->setData('customer_coupon_logistic', 'submenu');
        
        $this->setData(HTTP_ROOT_WWW.'company/customer_orders_logistic', 'searchUrl');

        $this->setData($this->parseUrl(), 'currentUrl');

        $this->setData('配货中心 - ' . $this->site['pageTitle'], 'pageTitle');

        $this->display_pc_mobile('company/customer_orders_logistic','company/customer_orders_logistic');
    }

		public function customer_orders_amend_action($dataFomOtherMethod = [])
    {
        if($dataFomOtherMethod['file_path'] && $dataFomOtherMethod['business_id']) {
            $filePath = $dataFomOtherMethod['file_path'];
            $this->loginUser['id'] = $dataFomOtherMethod['business_id'];
        }

        $mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');
		$mdl_order_amend = $this->loadModel('order_amend');
        $mdl_order = $this->loadModel('order');
        $mdl_user= $this->loadModel('user');


        /**
         * staff List
         */
        $where_staff = "((role = 5 and user_belong_to_user =".$this->current_business['id'].") or id = ".$this->current_business['id'].")";
        $list = $mdl_user->getList(null, $where_staff, 'createdDate asc');
        foreach ($list as $key => $value) {
           $list[$key]['displayName']=$mdl_user->getBusinessDisplayName($value['id']);
        }
        $this->setData($list, 'staff_list');

        /**
         * status List
         */

        /**
         * payment Type List
         */

         /**
         * customer_delivery_option Type List
         */
        


        $sk = trim(get2('sk'));
        $status = trim(get2('status'));
        $st=trim(get2('startTime'));
        $et=trim(get2('endTime'));
        $payment=trim(get2('payment'));
        $customer_delivery_option=trim(get2('customer_delivery_option'));
        $staff=trim(get2('staff'));

        $this->setData($sk,'sk');
        $this->setData($status,'status');
        $this->setData($st,'st');
        $this->setData($et,'et');
        $this->setData($payment,'payment');
        $this->setData($customer_delivery_option,'customer_delivery_option');
        $this->setData($staff,'staff');

        $sql = "SELECT a.* from cc_order as a left join cc_wj_user_coupon_activity_log as l on a.orderId=l.orderId and a.coupon_status=l.action_id ";
        $sql ="select amend.item_buying_id,amend.old_sub_total,amend.new_sub_total,(amend.old_sub_total-amend.new_sub_total) as amend ,amend.message,details.bonus_title,amend.reason_type,amend.createTime as amend_time,o.*  from cc_order_amend as amend
		left join (".$sql.") as o on amend.order_id =o.orderId
        left join cc_wj_customer_coupon as details on amend.item_buying_id=details.id
		";
		
		//var_dump($sql);exit;
		
        $whereStr.=" ( business_userId= ".$this->current_business['id'].  " or amend.createUserId  =".$this->current_business['id'] .") " ;

        if (!empty($sk)) {
            $whereStr.=" and (o.redeem_code like  '%" . $sk . "%'";
            $whereStr.=" or o.last_name like  '%" . $sk . "%'";
            $whereStr.=" or o.phone like  '%" . $sk . "%'";
            $whereStr.=" or o.orderId like  '%" . $sk . "%'";
            $whereStr.=" or o.order_name like  '%" . $sk . "%'";
            $whereStr.=" or o.tracking_id like  '%" . $sk . "%'";
            $whereStr.=" or o.first_name like  '%" . $sk . "%'";
			$whereStr.=" or o.userId like  '%" . $sk . "%')";
            $where[]=$whereStr;
        }
        if (!empty($status)) {
            if ($status != 'all') {
               $whereStr.= " and o.coupon_status='$status' ";
            }
        }
        if (!empty($payment)) {
            if ($payment != 'all') {
               $whereStr.= " and o.payment='$payment' ";
            }
        }
        if (!empty($customer_delivery_option)) {
            if ($customer_delivery_option != 'all') {
               $whereStr.= " and o.customer_delivery_option='$customer_delivery_option'";
            }
        }

         if (!empty($staff)) {
            if ($staff != 'all') {
               $whereStr.= " and o.business_staff_id = '$staff' ";
            }
        }

        /**
         * 只统计选定时间范围的修补订单，这样不会出现错误。 以后结算后应该将每一个结算点做一个标记，这样就不会出问题。
         */
      
            if (!empty($st)) {
                $st=strtotime($st." 00:00:00");
                $whereStr.= " and amend.createTime>='$st'";
            }

            if (!empty($et)) {
                $et=strtotime($et." 00:00:00");
                $whereStr.= " and amend.createTime<='$et'";
            }

        
		
		
		$where12=array(
					   'userId'=>$this->current_business['id']
					   
					);
				   $user_abn = $this->loadModel('wj_abn_application')->getByWhere($where12);

        $pageSql=$sql . " where " . $whereStr . " order by createTime desc";
		//var_dump($pageSql);exit;
      
        if(trim(get2('output'))=='pdf'){
            $data = $mdl_order->getListBySql($pageSql);

            foreach ($data as $key => $value) {

                $data[$key]['items']=$mdl_wj_customer_coupon->getItemsInOrder($value['orderId']);
            }

            $this->loadModel('invoice');
            $report = new OrderInfoReport();
            $report->setStarttime(date('Y-m-d H:i:s',$st))
                ->setEndtime(date('Y-m-d H:i:s',$et))
                ->title("商家报表-订单调整流水")
                ->OrderData($data);
            $report->generatePDF_order_amend();

            if($filePath) {//如果是系统内部调用会直接在指定路径创建文件
                $report->outPutToFile($filePath);
                return $filePath;
            }

			$report->outPutToBrowser(substr($user_abn['untity_name'],0,8).'-'.date('Y-m-d',$st).'-'.date('Y-m-d',$to).'_MissingOrDamage.pdf');
				
            //$report->outPutToBrowser();
            exit;

        }elseif(trim(get2('output'))=='shippingLabel'){

            $fitInPage=(get2('fitInPage')=='true')?true:false;

            $pageSql=$sql . " where " . $whereStr . " order by o.address desc";

            $data = $mdl_order->getListBySql($pageSql);

            $lotteryUserList=array();
            foreach ($data as $key => $value) {
                $data[$key]['items']=$mdl_wj_customer_coupon->getItemsInOrder($value['orderId']);

                $data[$key]['redeemQRCode']=redeemQRCode($value['redeem_code']);

                if(trim(get2('with'))=='lottery'&&!in_array($value['userId'], $lotteryUserList)){
                    $data[$key]['hasLottery']=$this->loadModel('lottery')->getUserRewardItems($value['userId'],$value['business_userId']);
                    array_push($lotteryUserList, $value['userId']);
                }
            }

            $this->loadModel('invoice');
            $report = new shippingLabel();
            $report->setStarttime(date('Y-m-d H:i:s',$st))
                ->setEndtime(date('Y-m-d H:i:s',$et))
                ->title("Shipping Label")
                ->setReturnAddress($this->current_business['googleMap'])
                ->fitInPage($fitInPage)
                ->OrderData($data);
          $report->generatePDF($this->lang);

            if($filePath) {//如果是系统内部调用会直接在指定路径创建文件
                $report->outPutToFile($filePath);
                return $filePath;
            }

            $report->outPutToBrowser();
            exit;
        }else{
            $pageUrl = $this->parseUrl()->set('page');
            $pageSize = 40;
            $maxPage = 10;
            $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);

            $data = $mdl_order->getListBySql($page['outSql']);

        }

        $this->setData($page['pageStr'],'pager');

        $this->setData($data,'data');

        $this->setData('online_center', 'menu');
        $this->setData('customer_orders_amend', 'submenu');
        
        $this->setData(HTTP_ROOT_WWW.'company/customer_orders_amend', 'searchUrl');

        $this->setData($this->parseUrl(), 'currentUrl');

        $this->setData('客户订单变更 - ' . $this->site['pageTitle'], 'pageTitle');

        $this->display_pc_mobile('company/customer_orders_amend','company/customer_orders_amend');
    }




   function  customer_order_amend_edit_action () {
	   
	   $id =get2('item_buying_id');
	   $return_link =get2('return_link');
	   $this->setData($return_link,'return_link');
	   
	   if(is_post()){
		    $id =post('item_buying_id');
		    $return_link =post('return_link');
	   }
	   
	   // 获得 退货reason_type 
		
	  $this->setData($this->get_order_amend_reson_type_list(),'order_amend_reson_type_list');
	   
	   $mdl_order_amend =$this->loadModel('order_amend');
	   $sql ="select a.*,b.bonus_title from cc_order_amend a , cc_wj_customer_coupon b where b.id=a.item_buying_id and a.item_buying_id=".$id . " and createUserId =".$this->current_business['id'];
		//var_dump($sql);exit;	  
	  $list = $mdl_order_amend->getListBySql($sql);
	   if(!$list){
		    $this->coupons_edit_failure('无数据！');
	   }
	   $this->setData($list[0],$data);
	   $coupon =$list[0];
	   $order_id = $list[0]['order_id'];
	   
	    if (is_post()) {
			
			        $message=post('message');
					$reason_type=post('reason_type');
                    $data['message']=$message;
					$data['reason_type']=$reason_type;
                    $images=post('images');
                    if($images){
                        foreach ($images as $key => $value) {
                            if($value=="default/image_upload.jpg")
                                unset($images[$key]);
                            else
                                $images[$key]=trim($value);
                        }
                        $data['pic']=reset($images);

                        $data['pics'] = serialize(array_slice($images, 1));
                    }else{
                       $this->coupons_edit_toStep($coupon['id'], 4); //no data no update. slow internet error
                    }

                    if ($mdl_order_amend->update($data, $coupon['id'])) {
						//var_dump($return_link);exit;
                       if($return_link=='details'){
						   $this->form_response(200,'',HTTP_ROOT_WWW.'company/customer_order_detail?id='.$order_id);
					   }else{
						   $this->form_response(200,'',HTTP_ROOT_WWW.'company/customer_orders_amend');
					   }
						
                    } else {
                        $this->coupons_edit_failure('保存失败');
                    }

                } else {

					$this->setData('online_center', 'menu');
					$this->setData('customer_orders_amend', 'submenu');
                    $this->display('company/customer_order_amend_edit');

                }
	   
	   
	   
	   
	   
   }


   


    function customer_order_statistics_action()
    {   
        $this->setData('订单数据统计 - ' . $this->site['pageTitle'], 'pageTitle');
        $this->setData('online_center', 'menu');
        $this->setData('customer_order_statistics', 'submenu');
        $this->display('company/customer_order_statistics');
    }

    function statistics_action(){
        $modelName = 'wj_customer_coupon';
        $modelClassName='mdl_'.$modelName;
        $mdl_wj_customer_coupon = $this->loadModel($modelName);

        if(is_post()){
            $fields = [];
            if(post('product'))     {array_push($fields, $modelClassName::CLN_BONUSID);array_push($fields,'bonus_id');}
            if(post('guige'))       array_push($fields, $modelClassName::CLN_GUIGEDES);
            if(post('user'))        array_push($fields, $modelClassName::CLN_USERID);
            if(post('staff'))       array_push($fields, $modelClassName::CLN_BUSINESSSTAFFID);
            if(post('status'))      array_push($fields, $modelClassName::CLN_COUPONSTATUS);
            
            $static = $mdl_wj_customer_coupon->getStatics($this->current_business['id'],$fields,strtotime(post('fromdate')),strtotime(post('todate')));

            $this->setData($mdl_wj_customer_coupon->replaceStaticTableLabel($static),'static');

        }

        echo $this->fetch('company/statisticsshow');
    }


    function customer_order_redeem_qrscan_action(){
        //mobile page only
        $orderId = trim(get2('qrscanorderid'));
        $redeemCode = trim(get2('qrscanredeemcode'));

        $operator = $this->loginUser['id'];

        //owner 
        $whereStr="(";
        $whereStr.=" business_userId=$operator";

        //staff
        if ($this->loginUser['user_belong_to_user'] > 0) {
             $whereStr.=  " OR business_staff_id =$operator ";
        }
       
        //redeem staff
        if ($this->loadModel('redeem_staff')->isRedeemStaff($operator)) {
            $whereStr .= " OR business_userId  IN (" . join(',', $this->loadModel('redeem_staff')->getBusinessListArray($operator)) . ") ";
        }
        $whereStr.=")";
        $where1[]=$whereStr;
		$where=array();

        if($orderId){
            $where['orderId']=$orderId;
            $data =  $this->loadModel('order')->getByWhere($where);
        }elseif($redeemCode){
            $where['redeem_code']=$redeemCode;
            $data =  $this->loadModel('order')->getByWhere($where);
        }else{
             $data =  null;
        }
        
        if($data){
            $this->setData($this->loadModel('user')->getBusinessNameById($data['business_userId']),'businessName');
            if ($data['business_staff_id']==$operator) {
                //operator is staff;
                $this->setData('staff','role');
            } elseif(in_array($data['business_userId'], $this->loadModel('redeem_staff')->getBusinessListArray($operator))){
               //operator is redeemStaff;
               $this->setData('redeem_staff','role');
            }else{
                $this->setData('owner','role');
            }
			//获取如果该商家为生鲜商家，则暂时不能兑付
			$business_user =$this->loadModel('user')->get($$data['business_userId']) ;
		if($business_user['business_type_freshfood']==1) { $this->setData(1,'isFreshFoodSupplier');} 
			
        }
        

        $this->setData($data,'data');

        $this->setData('订单扫码兑付 - ' . $this->site['pageTitle'], 'pageTitle');

        $this->display('company/customer_order_redeem_qrscan');

    }   

    public function cancel_order_action()
    {        
        $orderId= get2('orderId');
        
        if ( $orderId > 0 ) {
            $this->cancel_customer_coupon('cancelByBusiness',$orderId);
        }

        $this->sheader(HTTP_ROOT_WWW.'company/customer_order_detail?id='.$orderId );
    }

    function update_order_status_paid_action()
    {

        $sys_op=get2('sys_op');
        $orderId = get2('orderId');

        $mdl_order = $this->loadModel('order');
		//$mdl_activity_log=$this->loadModel('wj_user_coupon_activity_log');

		
		$operator = $this->loginUser['id'];
		
		$data1 = $mdl_order->getByOrderId($orderId);
    // $this->sheader(null,$operator['isAdmin']);
        if($operator['isAdmin'] ==0  && $data1['business_userId']!=$operator&&$data1['business_staff_id']!=$operator&&!$this->loadModel('redeem_staff')->isRedeemStaff($operator))$this->sheader(null,'越权处理');

		
        $data['status'] = 1;
        $data['paytime'] = time();
        $where['orderId'] = $orderId;
        $mdl_order->updateByWhere($data, $where);

      //  $data_active[''] =;
		
       

        if($sys_op) {
				   
				   $this->sheader(HTTP_ROOT_WWW.'index.php?con=admin&ctl=adv/customer_coupon_process&act=detail&id='.$orderId);
			   }else{
                   $this->sheader(HTTP_ROOT_WWW . 'company/customer_order_detail?&id='.$orderId);
			   }


       

    }

    private function coupons_edit_toStep($id, $step)
    {
        $redirect = HTTP_ROOT_WWW . 'company/coupons_edit?id=' . $id . '&step=' . $step . '#pagename';
        $this->form_response(200,'',$redirect);
    }
	
	 private function youhuiquan_edit_toStep($id, $step)
    {
        $redirect = HTTP_ROOT_WWW . 'company/youhuiquan_edit?id=' . $id . '&step=' . $step . '#pagename';
        $this->form_response(200,'',$redirect);
    }

	  private function restaurant_edit_toStep($id, $step)
    {
        $redirect = HTTP_ROOT_WWW . 'company/restaurant_edit?id=' . $id . '&step=' . $step . '#pagename';
        $this->form_response(200,'',$redirect);
    }
	
	  private function freshfood_edit_toStep($id, $step)
    {
        $redirect = HTTP_ROOT_WWW . 'company/freshfood_edit?id=' . $id . '&step=' . $step . '#pagename';
        $this->form_response(200,'',$redirect);
    }
	
    private function coupons_edit_failure($msg = '保存失败')
    {
        $this->form_response(500, $msg, null);
    }

     //coupon and shop edit insert and save category
     function category_insert_single_action()
    {
      
        $cid = trim(get2('cid'));

        $mdl_user = $this->loadModel('user');

        if (!preg_match('/^106/i', $cid) || strlen($cid) % 3 != 0) {
            //行业有误,无法插入
            exit;
        }

        if (!$this->loginUser) {
            //无登录用户,无法插入
            exit;
        }

        if ($cid) {

            $currentCategory=$this->loginUser['categoryId'];
            $currentCategoryArray=explode(',', $currentCategory);
            array_push($currentCategoryArray, $cid);
            $newCategoryStr = join(',',$currentCategoryArray);

            $this->loginUser['categoryId']=$newCategoryStr;
            $mdl_user->updateUserById(array('categoryId'=>$this->loginUser['categoryId']), $this->loginUser['id']);
        }
    }

    function coupons_action()
    {
        $id = (int)get2('id');
        if (get2('wholesale')=='1')
        {
            $this->cancel_wholesale($id,'coupons');
            return;
        }

        $coupon_type = (int)get2('coupon_type');
        $category = (int)get2('category');
        $keyword = get2('sk');

        $mdl_coupons = $this->loadModel('coupons');
        $mdl_coupons_sub=$this->loadModel('coupons_sub');
        $mdl_coupon_type = $this->loadModel('coupon_type');


        if ($id > 0) {
            $coupon = $mdl_coupons->get($id);
            if (!$coupon || $coupon['createUserId'] != $this->loginUser['id']) $this->sheader(null, '产品不存在');
            if ($coupon['buy'] > 0) $this->sheader(null, '该产品存在兑换记录，不可以删除');

            $mdl_coupons->delete($id);
            $mdl_coupons_sub->deleteByWhere(array('parent_coupon_id'=>$id));
            // $this->file->deletefile( UPDATE_DIR.$coupon['pic'] );
            
            $this->sheader($this->parseUrl()->set('id'));
        }
       // 如果时运营商 那么判断条件有所变化
	   
	   if( $this->loginUser['role']==6) {
		   
		    $where=" where createUserId in (select id from cc_user where user_belong_to_agent ='".$this->loginUser['id']."') and isInManagement='0' ";
	   }else{
		   
		    $where=" where createUserId='".$this->loginUser['id']."' and isInManagement='0' ";
		   
	   }
		
       

        if ($category) $where = $where." and categoryId like '%$category%'";
        if ($keyword) $where = $where." and title like '%$keyword%'";

        if (!$coupon_type ) {

            $where = $where.' and bonusType in (1,2,4,7,18)';
            
        } else {
            if($coupon_type==7){
				 $where = $where." and EvoucherOrrealproduct <> 'restaurant_menu' and  bonusType =" . $coupon_type;
			}else{
				$where = $where.' and bonusType =' . $coupon_type;
			 }
        }

        $pageSql = "select  * from cc_coupons ".$where." order by createTime desc";
		//var_dump($pageSql);exit;
        $pageUrl = $this->parseUrl()->set('page');
        $pageSize = 20;
        $maxPage = 10;
        $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
        $data = $mdl_coupons->getListBySql($page['outSql']);

        foreach ($data as $key => $val) {
            $data[$key]['coupontype'] = $mdl_coupon_type->get($val['bonusType']);
        }

        $this->setData($category, 'category');
        $this->setData($data, 'data');
        $this->setData($page['pageStr'], 'pager');
		
		$coupon_edit_path = $this->get_coupon_edit_path($val['bonusType']);
		
		
        $this->setData($this->parseUrl()->setPath($coupon_edit_path), 'editUrl');

       // $this->setData($this->parseUrl()->setPath('company/coupons_edit'), 'editUrl');


        if ($coupon_type == 4) {
            $this->setData('offline_voucher', 'submenu');
            $this->setData('index_publish', 'menu');

            $pagename = "线下优惠管理";
            $pageTitle=  $pagename." - 商家中心 - ". $this->site['pageTitle'];
        }elseif (($coupon_type == 7)) {
            $this->setData('local', 'submenu');
            $this->setData('index_publish', 'menu');

            $pagename = "本地服务管理";
            $pageTitle=  $pagename." - 商家中心 - ". $this->site['pageTitle'];

            if(get2('sub')=='event'){
                $this->setData('event', 'submenu');
                $this->setData('index_publish', 'menu');

                $pagename = "活动管理";
                $pageTitle=  $pagename." - 商家中心 - ". $this->site['pageTitle'];
            }
			
        } elseif (($coupon_type == 18)) {
            $this->setData('daijinquan', 'submenu');
            $this->setData('index_publish', 'menu');

            $pagename = "代金券";
            $pageTitle=  $pagename." - 商家中心 - ". $this->site['pageTitle'];
      
        }elseif (($coupon_type == 9)) {
            $this->setData('shop', 'submenu');
            $this->setData('index_publish', 'menu');

            $pagename = "商品管理";
            $pageTitle=  $pagename." - 商家中心 - ". $this->site['pageTitle'];
        } else{
            $this->setData('all', 'submenu');
            $this->setData('index_publish', 'menu');

            $pagename = "全部产品管理";
            $pageTitle=  $pagename." - 商家中心 - ". $this->site['pageTitle'];
        }


        // 这个是 存储当前卡券类型
        $this->setData($coupon_type, 'coupon_type');

        $this->setData($keyword, 'sk');

        $this->setData($coupon_type_name, 'coupon_type_name');

        $this->setData($pagename, 'pagename');

        $this->setData($pageTitle, 'pageTitle');
		
		
		
        if ($coupon_type==18) {
			$this->display_pc_mobile('company/daijinquan', 'company/daijinquan');
       
			
		}else if($coupon_type==4) {
			$this->display_pc_mobile('company/youhuiquan', 'company/youhuiquan');
		
		}else{
			$this->display_pc_mobile('company/coupons', 'mobile/company/coupons');
       
			
		}
       
    }

	
	

function  referral_product_program_share_action() {
	  $storeLink="http://$_SERVER[HTTP_HOST]/store/".$this->loginUser['id'];
      $this->setData($storeLink,'storeLink');
      $this->setData(generateQRCode($storeLink),'storeQrCode');
	  
	  
	  $this->setData('send_share', 'submenu');
      $this->setData('store_setting', 'menu');
	  $this->display_pc_mobile('company/referral_product_program_share', 'mobile/company/referral_product_program_share');
	  
}

function  referral_food_share_action() {
	  $storeLink="http://$_SERVER[HTTP_HOST]/food?reftag=".$this->loginUser['id'];
	  $foodLink="http://$_SERVER[HTTP_HOST]/referal/referrals?type=user";
	  
	 
      $this->setData($foodLink,'foodLink');
	  $this->setData($storeLink,'storeLink');
      $this->setData(generateQRCode($storeLink),'storeQrCode');
	  
	  
	  $this->setData('referral_food_share', 'submenu');
      $this->setData('my_mingxingshop', 'menu');
	  $this->display_pc_mobile('company/referral_food_share', 'mobile/company/referral_food_share');
}



function  business_hour_setting_action() {
	  
	  
	  $this->setData($this->loginUser['trading_hours'],'trading_hours');
	  
	  //var_dump($this->loginUser['trading_hours']);exit;
	 
	  $this->setData('business_hour_setting', 'submenu');
      $this->setData('basic_setting', 'menu');
	  $this->display_pc_mobile('company/business_hour_setting1', 'company/business_hour_setting1');
	//  $this->display_pc_mobile('company/index1', 'company/index1');
}




function freshfood_edit_action()    {   
       


       $this->setData('1','freshfood');

	   /**
         * 加载模组
         */
        $mdl_coupons = $this->loadModel('coupons');
		
		
		//获取是否为线上餐厅
        $restaurant =get2('restaurant');
		if($restaurant) {
			$where00 =array(
		   'createUserId' => $this->loginUser['id'],
		   'EvoucherOrrealproduct' =>'restaurant_menu'
		   );
		   $restaurant_coupon= $mdl_coupons->getByWhere($where00);
		   if($restaurant_coupon) {
			   $id=$restaurant_coupon['id'];
		   }
		}else{
			  $id = (int)get2('id');
		}

        /**
         * 当前编辑第几步
         */
        $step = (int)get2('step');

        if ($step <= 0) $step = 1;
        if ($step > 7) $step = 7;

        $this->setData($step, 'step');
        $this->setData($this->parseUrl()->set('step'), 'stepUrl');

        /**
         * 产品ID
         */
      

        if ($id) {
            //编辑产品
            $coupon = $mdl_coupons->get($id);

            if (!$coupon || $coupon['createUserId'] != $this->loginUser['id']) $this->sheader(null, '产品不存在');

            $this->setData($coupon, 'data');

        }else{
            
        }

        /**
         * 产品类型
         */
        $coupon_type = ($id)?$coupon['bonusType']:(int)get2('coupon_type');

        $this->setData($coupon_type, 'coupon_type');

        if ($coupon_type == 4) {
            $this->setData('voucher', 'submenu');
            $this->setData('index_publish', 'menu');
        }elseif (($coupon_type == 7)) {
            $this->setData('local', 'submenu');
            $this->setData('index_publish', 'menu');
        } elseif (($coupon_type == 9)) {
            $this->setData('shop', 'submenu');
            $this->setData('index_publish', 'menu');
        } else{
            $this->setData('all', 'submenu');
            $this->setData('index_publish', 'menu');
        }


        /**
         * 页面信息
         */
        $this->setData($this->parseUrl(), 'postUrl');
        $this->setData($this->parseUrl()->set('step',$step-1), 'prevUrl');

        $this->setData(($coupon ? '编辑' : '添加') . '产品', 'pagename');
        $this->setData(($coupon ? '编辑' : '添加') . '产品 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
         $mdl_city=$this->loadModel('city');

        /**
         * 分步骤编辑
         */
        switch ($step) {
            case 1:
                if (is_post()) {
                    /**
                     * 接收数据
                     */
                    $title = trim(post('title'));
                    $bonusType = (int)post('bonusType');
                    $userCategoryId = post('userCategoryId');

                    /**
                     * 验证
                     */
                    if (empty($title)) $this->coupons_edit_failure('请添加标题');
                    if (empty($bonusType)) $this->coupons_edit_failure('请选择产品类型');
                    if (empty($userCategoryId)) $this->coupons_edit_failure('请将选择至少一个行业');

                    /**
                     * 预处理
                     */
					$mdl_infoclass=$this->loadModel('infoClass');
                    $userCategoryId= ",".join(',',$userCategoryId).",";

					$mdl_restaurant_info =$this->loadModel('restaurant_info');
					$mdl_restaurant_promotion_manjian =$this->loadModel("restaurant_promotion_manjian");
				
				
						// 插入线上餐厅的相关数据
						
						$data1['is_approved']=1;
						$data1['status']=4;
						$data1['userID']=$this->loginUser['id'];
						$data1['name']=$title;
						
						
						
						$restaurant_info = $mdl_restaurant_info->getByWhere(array('userID'=>$this->loginUser['id']));
				
						if($restaurant_info) {
							$mdl_restaurant_info->update($data1,$restaurant_info['id']);
					
						}else{
							$mdl_restaurant_info->insert($data1);
						}
						
						
						// 更新餐厅优惠数据库数据
				
					
						$restaurant_promotion_manjian = $mdl_restaurant_promotion_manjian->getByWhere(array('restaurant_id'=>$this->loginUser['id']));

						if( $restaurant_promotion_manjian) {
					 							
						}else{
							$data_manjian['restaurant_id'] =$this->loginUser['id'];
							$data_manjian['createUserId'] =$this->loginUser['id'];
							$data_manjian['discount'] =0;
						if($mdl_restaurant_promotion_manjian->insert($data_manjian))  {
						}else{
							$this->form_response_msg('线上餐厅开启失败，请稍候再试');
						}
					}
					
                    if (!$coupon) {
                        $data = array();
                        $data['title'] = $title;
                        $data['bonusType'] = $bonusType;
                        $data['categoryId'] = $userCategoryId;
                        $data['categoryName'] = $mdl_coupons->getCategoryName($data['categoryId'],$mdl_infoclass);
						
						if($restaurant) {
							$data['EvoucherOrrealproduct'] = 'restaurant_menu';
						}
                        //默认值
                        $data['startTime'] = time();
                        $data['endTime'] = strtotime('+ 90 days', time()) - 1;
                        $data['autoOffline'] = false;

                        $data['createUserId'] = $this->loginUser['id'];
                        $data['createTime'] = time();

                        $data['isApproved'] = ($this->loginUser['trustLevel'] >= 1)?1:0;
                        $data['status'] = 1;

                        $data['visibleForBusiness'] = $this->loginUser['visibleForBusiness'];
                        $data['city'] = $this->loginUser['cityId'];
						$data['cityName']= $mdl_city->getCityName($data['city']);
						//var_dump($data['city'].$data['cityName']);exit;
						
                        $data['languageType'] = $this->loginUser['languageType'];
                        $data['businessName'] = $this->loadModel('user')->getBusinessDisplayName($this->loginUser['id']);

                        $data['qty']=0;
                        $data['perCustomerLimitQuantity']=0;
                        $data['perCustomerMinLimitQuantity']=0;

                        $data['deliverFeeCalculationType'] = 'per_coupon';
                        $data['platform_commission_base'] = $this->loginUser['platform_commission_base'];;
                        $data['platform_commission_rate'] = $this->loginUser['platform_commission_rate'];;
                        

                        if ($coupon_id=$mdl_coupons->insert($data)) {
                            $this->freshfood_edit_toStep($coupon_id, 2);
                        } else {
                            $this->coupons_edit_failure('保存失败');
                        }
						
						
							
						

                    } else {

                        $data = array();
                        $data['title'] = $title;
                        $data['bonusType'] = $bonusType;
                        $data['categoryId'] = $userCategoryId;
                        $data['categoryName'] = $mdl_coupons->getCategoryName($data['categoryId'],$mdl_infoclass);
                        if ($this->loginUser['needReapprovedAfterEdit']) {
                            //$data['status'] = 1;
                        }

                        if ($mdl_coupons->update($data, $coupon['id'])) {
                            $this->freshfood_edit_toStep($coupon['id'], 2);
                        } else {
                            $this->coupons_edit_failure('保存失败');
                        }
                    }
					
					
					
					
                } else {
                    
                    /**
                     * 可选产品类型
                     */
                    $mdl_coupon_type = $this->loadModel('coupon_type');

                    if ($coupon_type) {
                        $types = $mdl_coupon_type->getList(null, array('isApproved' => 1, 'id' => $coupon_type), 'sortnum asc');
                    } else {
                        $types = $mdl_coupon_type->getList(null, array('isApproved' => 1,"id in (9,7,4)" ), 'sortnum asc');
                    }

                    $this->setData($types, 'types');


                    /**
                     * 用户曾选的分类
                     */
                    $mdl_infoclass = $this->loadModel('infoClass');

                    $catids = explode(',', $this->loginUser['categoryId']);
                   
                    $userCategory = array();
                    foreach ($catids as $catid) {
                        $cat = $mdl_infoclass->get($catid);

                        if(!$cat)continue;//filter out none exist cat, data still exist in db.

                        $cat_large = $mdl_infoclass->get(substr($catid, 0, 6));

                        $cat['name'] = $cat_large['name'] . '->' . $cat['name'];
                        $userCategory[] = $cat;
                    }
                    $this->setData($userCategory, 'userCategory');
                 
                    /**
                     * 全部可用分类
                     */
                    $categoriesAll = $mdl_infoclass->getListBySql("select id, name, (select count(*) from #@_infoclass where id like concat(c.id,'___')) as childCount from #@_infoclass as c where id like '106%' and lang ='".$this->getLangStr()."' order by ordinal asc");
                    $this->setData($categoriesAll, 'categoriesAll');


                    /**
                    * 分类树形结构数据
                    */
                    $mdl_infoclass=$this->loadModel( 'infoClass' );
                    $categories=  $mdl_infoclass->getChild4( '106' );
                   
                      foreach ($categories as $key => $value) {
						  if ($categories[$key][id] !='106124' && $categories[$key][id] !='106126'  && $categories[$key][id] !='106105' ) {
							   
							  	unset($categories[$key]);
						  }
						  
					  }
				 //  var_dump($categories[$key]);exit;
					
                    

                    $this->setData($categories, 'categories' );
                    $this->setData('index_publish', 'menu');
					
					$this->setData('restaurant_set', 'submenu');
					if(get2('freshfood')){
						 $this->setData('open_store', 'menu');
					
						$this->setData('freshshop_card', 'submenu');
						
					}
                  
					$this->setData('restaurant_set', 'submenu_top');

                    $this->display('company/freshfood_edit');
                }
                break;
            case 2:
                if (is_post()) {
                    /**
                     * 数据
                     */
                    $country_code = (int)post('country_code');
                    $city = post('city');
                    $startTime=trim(post('startTime'));
                    $endTime=trim(post('endTime'));
                    $cCategoryId_lock=post('cCategoryId_lock');
                    $cCategoryId=post('cCategoryId');
                    
                    $searchKeywords=trim(post('searchKeywords'));
                    $autoOffline=(post('autoOffline'))?1:0;

                    /**
                     * 预处理
                     */
                    $city =($city)?','.join(',', $city).',':' ';

                    $startTime =($startTime)?$startTime:date('Y-m-d');
                    $endTime = $endTime?$endTime:date('Y-m-d', strtotime('+90 days'));
                    $startTime = strtotime($startTime);
                    $endTime = strtotime($endTime);

                    $cCategoryId_lock=($cCategoryId_lock)?1:0;
                    $cCategoryId=",".join(',',post('cCategoryId')).",";


                    /**
                     * 准备更新数据
                     */
                    $data=array();
                    $data['country_code'] = $country_code;
                    $data['city'] = $city;
					$data['cityName']= $mdl_city->getCityName($data['city']);
                    $data['startTime'] = $startTime;
                    $data['endTime'] = $endTime;
                    $data['cCategoryId_lock'] = $cCategoryId_lock;
                    $data['cCategoryId'] = $cCategoryId;
                    $data['searchKeywords'] = $searchKeywords;
                    $data['autoOffline'] = $autoOffline;


                    /**
                     * 处理 coupons_addon_数据
                     */
                    $this->loadModel('coupons_addon')->set($coupon['id'],$_POST);

                    if ($mdl_coupons->update($data, $coupon['id'])) {
                        $this->freshfood_edit_toStep($coupon['id'], 3);
                    } else {
                        $this->coupons_edit_failure('保存失败,请稍后再试');
                    }

                } else {
                     /**
                     * 自定义分类
                     */

                    $mdl = $this->loadModel('customizableCategory');
                    $mdl->setUserId($this->loginUser['id']);
                    $list = $mdl->getTopLevelItemList();
                    foreach ($list as $key => $value) {
                        $list[$key]['hasChild'] = $mdl->hasChild($value['id']);
                    }
                    $this->setData($list, 'tr_list');
                    $table_tr = $this->fetch('customizable_category/table_tr_show');
                    $this->setData($table_tr, 'table_tr');

                    /**
                     * addon data
                     */
                    $addonData = $this->loadModel('coupons_addon')->getAddonData($id);
                    $this->setData($addonData, 'addonData');
					$this->setData('index_publish', 'menu');
                    $this->setData('restaurant_set', 'submenu');
					$this->setData('restaurant_set', 'submenu_top');
					$this->display('company/freshfood_edit2');
                }
                break;
            case 3:
			
			
                if (is_post()) {

                    $images=post('images');
                    if($images){
                        foreach ($images as $key => $value) {
                            if($value=="default/image_upload.jpg")
                                unset($images[$key]);
                            else
                                $images[$key]=trim($value);
                        }
                        $data['pic']=reset($images);

                        $data['pics'] = serialize(array_slice($images, 1));
                    }else{
                       $this->freshfood_edit_toStep($coupon['id'], 4); //no data no update. slow internet error
                    }

                    if ($mdl_coupons->update($data, $coupon['id'])) {
                        $this->freshfood_edit_toStep($coupon['id'], 4);
                    } else {
                        $this->coupons_edit_failure('保存失败');
                    }

                } else {
                    $this->setData('index_publish', 'menu');
                    $this->setData('restaurant_set', 'submenu');
					$this->setData('restaurant_set', 'submenu_top');
                    $this->display('company/freshfood_edit3');

                }
                break;
            case 4:
                $mdl_restaurant_promotion_manjian = $this->loadModel('restaurant_promotion_manjian');
                $where =array(
			    'restaurant_id'=> $coupon['createUserId']
			    );
				$manjian_info =$mdl_restaurant_promotion_manjian->getByWhere($where);
				if($manjian_info) {
					$this->setData($manjian_info,'manjian_info');
				}
				
                if (is_post()) {
                    $data['discount'] = post('discount');
                   $data['promotion_desc'] = post('promotion_desc');
				    $data['promotion_desc_en'] = post('promotion_desc_en');
                   // var_dump($manjian_info['id']);exit;
               
                    //更新价格
					$where1=array(
					  'Restaurant_id'=>$this->loginUser['id']
					);
					
					
					if($mdl_restaurant_promotion_manjian->getByWhere($where1)) {
						
						 $mdl_restaurant_promotion_manjian->update($data, $manjian_info['id']);
						
					}else{
						
						$data['Restaurant_id']=$this->loginUser['id'];
						$data['createUserId']=$this->loginUser['id'];
						$mdl_restaurant_promotion_manjian->insert($data);
					}
					
                   


                   

                    if ($mdl_coupons->errno()) {
                        $mdl_coupons->rollback();
                        $this->coupons_edit_failure('保存失败');
                    } else {
                        $mdl_coupons->commit();
                        $this->freshfood_edit_toStep($coupon['id'], 5);
                    }

                } else {
					
					$this->setData('index_publish', 'menu');
                    $this->setData('restaurant_set', 'submenu');
					$this->setData('restaurant_set', 'submenu_top');

                    switch ($coupon['bonusType']) {
                      
                        case 7:
                            $this->setData($coupon);

                            $this->display('company/freshfood_edit4');
                            break;
                      
                        default:
                            $this->sheader(null,'无此类型');
                            break;
                    }
 

                }
                break;
            case 5:
                if (is_post()) {
                    $coupon_summery_description = post('coupon_summery_description');
                    $highlight                  = post('highlight');
                    $finePrint                  = post('finePrint');
                    $content                    = post('content');
                    $refund_policy              = post('refund_policy');
                    $redeemProcedure            = post('redeemProcedure');

                    $data['coupon_summery_description'] = $coupon_summery_description;  
                    $data['highlight']                  = $highlight;
                    $data['finePrint']                  = $finePrint;
                    $data['content']                    = $content;
                    $data['redeemProcedure']            = $redeemProcedure;
                    $data['refund_policy']              = $refund_policy;

                    if(!$this->loginUser['refund_policy'])$this->loadModel('user')->updateUserById(array('refund_policy'=>$refund_policy), $this->loginUser['id']);

                    if ($mdl_coupons->update($data, $coupon['id'])) {
                        $this->freshfood_edit_toStep($coupon['id'], 6);
                    } else {
                        $this->coupons_edit_failure('保存失败');
                    }
                } else {
					$this->setData('index_publish', 'menu');
                    $this->setData('restaurant_set', 'submenu');
					$this->setData('restaurant_set', 'submenu_top');

                    $this->display('company/freshfood_edit5');

                }
                break;
           case 6:
               if (is_post()) {
                //获取递送的信息
                  $deliver_avaliable         = trim(post('deliver_avaliable'));
                $flat_rates_to_local_city  = trim(post('flat_rates_to_local_city'));
                   $flat_rates_national       = trim(post('flat_rates_national'));
                  $flat_rates_international  = trim(post('flat_rates_international'));
                   $pickup_avaliable          = trim(post('pickup_avaliable'));
                   $EvoucherOrrealproduct     = trim(post('EvoucherOrrealproduct'));
                    $deliverFeeCalculationType = trim(post('deliverFeeCalculationType'));

                   $sales_user_list     = post('sales_user_list');
                    $staff_region_limited = trim(post('staff_region_limited'));

                    if(!post('product_deliver_fee_checkbox'))$flat_rates_to_local_city=0;

                    if($coupon['bonusType']=='1'||$coupon['bonusType']=='2'||$coupon['bonusType']=='4')$EvoucherOrrealproduct='evoucher';

                    $data['deliver_avaliable']         = $deliver_avaliable;
                   $data['flat_rates_to_local_city']  = $flat_rates_to_local_city;
                   $data['flat_rates_national']       = $flat_rates_national;
                    $data['flat_rates_international']  = $flat_rates_international;
                    $data['pickup_avaliable']          = $pickup_avaliable;
                   $data['EvoucherOrrealproduct']     = $EvoucherOrrealproduct;
                   $data['deliverFeeCalculationType'] = $deliverFeeCalculationType;
                   $data['sales_user_list']           = join(',',$sales_user_list);
                    $data['staff_region_limited']      = $staff_region_limited;


                   $pickup_des = trim(post('pickup_des'));
                   $delivery_description =  trim(post('delivery_description'));
                    $address =  trim(post('address'));
                   
                   $userData=[];
                    if($address)$userData['googleMap']=$address;
                    if($pickup_des)$userData['pickup_des']=$pickup_des;
                    if($delivery_description)$userData['delivery_description']=$delivery_description;
					$userData['deliver_avaliable']=$deliver_avaliable;
					$userData['pickup_avaliable']=$pickup_avaliable;

                   $this->loadModel('user')->updateUserById($userData, $this->loginUser['id']);

                    if ($mdl_coupons->update($data, $coupon['id'])) {
                          $redirect = HTTP_ROOT_WWW . 'company/payment_setting';
							$this->form_response(200,'保存成功',$redirect);
                    } else {
                        $this->coupons_edit_failure('保存失败');
                    }

                } else {
					$this->setData('index_publish', 'menu');
                   $this->setData('restaurant_set', 'submenu');
					$this->setData('restaurant_set', 'submenu_top');

                   /**
                     * 加载全部员工组
                     */
                   $mdl_user = $this->loadModel('user');
                    $staff_list=$mdl_user->getAllStaff($this->loginUser['id']);
                    $this->setData($staff_list, 'staff_list');

                    /**
                    * 已选员工
                    */
                    $this->setData(explode(',',$coupon['sales_user_list']),'selected_sales_user_list');


                    $this->display('company/freshfood_edit6');

                }
                break;
            case 7:
                if (is_post()) {
                    $userData=[];
                    $address =  trim(post('address'));
                    if($address)$userData['googleMap']=$address;

                    $noAlert = trim(post('noAlert'));
                    if($noAlert)$userData['noAlert']=1;


					$status = trim(post('status'));
					 
                    $this->loadModel('user')->updateUserById($userData, $this->loginUser['id']);

                    if($status ==4) {
					//	$confirm = (int)post('confirm');
                     //   if (!$confirm) $this->coupons_edit_failure('请先阅读产品发布说明');
						$data['status'] = 4;
                        if ($mdl_coupons->update($data, $coupon['id'])) {
                            $this->form_response(200,'发布成功','SELF');
                        } else {
                            $this->coupons_edit_failure('发布失败');
                        }
						//$this->form_response(200,'修改成功',HTTP_ROOT_WWW .'company/coupons?coupon_type='.$coupon['bonusType']);
					}else if($status ==1) {
						
						$data['status'] = 1;
                        if ($mdl_coupons->update($data, $coupon['id'])) {
                            $this->form_response(200,'取消成功，产品已下线','SELF');
                        } else {
                            $this->coupons_edit_failure('取消失败');
                        }
						
						
					}else{
						
						 $this->coupons_edit_failure('未知错误，请联系平台方');
						
					}
 
                } else {
					//var_dump('here');exit;
                    $couponLink="http://$_SERVER[HTTP_HOST]/restaurant/".$this->loginUser['id'];
                    $this->setData($couponLink,'couponLink');
                    $this->setData(generateQRCode($couponLink),'couponQrCode');

                
                    $packageLink="http://$_SERVER[HTTP_HOST]/coupon1/4894";
                    $this->setData($packageLink,'packageLink');
                    $this->setData(generateQRCode($packageLink),'packageQrCode');

                    $listUrl=HTTP_ROOT_WWW."company/coupons?coupon_type=".$coupon['bonusType'];
                    $this->setData($listUrl,'listUrl');

                    $coupon_delivery_info =$mdl_coupons->getDeliveryInfo($id);
                    $this->setData( $coupon_delivery_info, 'coupon_delivery_info' );
                    $this->setData('index_publish', 'menu');
                    $this->setData('onoff_share', 'submenu');
					$this->setData('restaurant_set', 'submenu_top');
                    $this->display('company/freshfood_edit7');

                }
                break;
        }
    }
	









	function mingxing_coupons_action()
    {
		
		
        $id = (int)get2('id');
		$categoryid ='';
		
		$allCategoryId1 =get2('allCategoryId');
		if ( $allCategoryId1[2]) {
			$categoryid =$allCategoryId1[2];
		}else if ($allCategoryId1[1]) {
			
			$categoryid =$allCategoryId1[1];
		}else if ($allCategoryId1[0]) {
			
			$categoryid =$allCategoryId1[0];
		}
		
	     //从页面获得的最后类别,直接选择最末级菜单发生
		 $alias = get2('alias');
		 if($alias){
			 $categoryid =$alias;
			 
		 }
		 

        $coupon_type = (int)get2('coupon_type');
        $category = (int)get2('category');
        $keyword = get2('sk');

		
		
 /**
                     * 可选产品类型
                     */
                    $mdl_coupon_type = $this->loadModel('coupon_type');

                    if ($coupon_type) {
                        $types = $mdl_coupon_type->getList(null, array('isApproved' => 1, 'id' => $coupon_type), 'sortnum asc');
                    } else {
                        $types = $mdl_coupon_type->getList(null, array('isApproved' => 1,"id in (9,7,4)" ), 'sortnum asc');
                    }

                    $this->setData($types, 'types');


                    /**
                     * 用户曾选的分类
                     */
                    $mdl_infoclass = $this->loadModel('infoClass');

                    $catids = explode(',', $this->loginUser['categoryId']);
                   
                    $userCategory = array();
                    foreach ($catids as $catid) {
                        $cat = $mdl_infoclass->get($catid);

                        if(!$cat)continue;//filter out none exist cat, data still exist in db.

                        $cat_large = $mdl_infoclass->get(substr($catid, 0, 6));

                        $cat['name'] = $cat_large['name'] . '->' . $cat['name'];
                        $userCategory[] = $cat;
                    }
                    $this->setData($userCategory, 'userCategory');
                 
                    /**
                     * 全部可用分类
                     */
                    $categoriesAll = $mdl_infoclass->getListBySql("select id, name, (select count(*) from #@_infoclass where id like concat(c.id,'___')) as childCount from #@_infoclass as c where id like '106%' and lang ='".$this->getLangStr()."' order by ordinal asc");
                    $this->setData($categoriesAll, 'categoriesAll');


                    /**
                    * 分类树形结构数据
                    */
                    $mdl_infoclass=$this->loadModel( 'infoClass' );
                    $categories=  $mdl_infoclass->getChild4( '106' );

                    if ($coupon_type == 4) {

                    }elseif (($coupon_type == 7)) {
                        unset($categories['106124']);
                        unset($categories['106120']);
                        unset($categories['106105']);
                    }elseif (($coupon_type == 9)) {
                        unset($categories['106121']);
                        unset($categories['106119']);
                    }else{

                    }

                    $this->setData($categories, 'categories' );		
		
		
		
		
		
		
		
		
		
        $mdl_coupons = $this->loadModel('coupons');
        $mdl_coupons_sub=$this->loadModel('coupons_sub');
        $mdl_coupon_type = $this->loadModel('coupon_type');


 

        $where=" where  isapproved=1 and status =4 ";

        if ($categoryid) $where = $where." and categoryId like '%,$categoryid%'";
        if ($keyword) $where = $where." and title like '%$keyword%'";

       
	   // 如果该用户是华裔小姐,则选择的商家的产品范围限制在 大上海运营商商家范围内.
	   
	   //判断条件是 商家的 belong to business user 的编号位  user_belong_to_agent  210276 服务器商的编号 查找一下 210362 
	    if ($this->loginUser['business_type_miss']==1) {
			$where_extra =" and a.createUserId in ( select id from cc_user where user_belong_to_agent=210362)";
			
			$where=$where.$where_extra;
			
		}
	   
	   
	   
	   

        $pageSql = "select  a.* ,b.id as idd  from cc_coupons a left join ( select * from cc_referral_product_program where userID=".$this->loginUser['id'].") as  b on a.id=b.productId  ".$where." order by createTime desc";
		//var_dump($pageSql);exit;
        $pageUrl = $this->parseUrl()->set('page');
        $pageSize = 20;
        $maxPage = 10;
        $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
        $data = $mdl_coupons->getListBySql($page['outSql']);

        foreach ($data as $key => $val) {
            $data[$key]['coupontype'] = $mdl_coupon_type->get($val['bonusType']);
        }

        $this->setData($category, 'category');
        $this->setData($data, 'data');
        $this->setData($page['pageStr'], 'pager');
		
		$coupon_edit_path = $this->get_coupon_edit_path($val['bonusType']);
		
		
        $this->setData($this->parseUrl()->setPath($coupon_edit_path), 'editUrl');

 
      
            $this->setData('yijianbanjia', 'submenu');
            $this->setData('my_mingxingshop', 'menu');

            $pagename = "全部产品管理";
            $pageTitle=  $pagename." - 商家中心 - ". $this->site['pageTitle'];
       


        // 这个是 存储当前卡券类型
        $this->setData($coupon_type, 'coupon_type');
		$this->setData($allCategoryId1, 'allCategoryId1');

        $this->setData($keyword, 'sk');

        $this->setData($coupon_type_name, 'coupon_type_name');

        $this->setData($pagename, 'pagename');

        $this->setData($pageTitle, 'pageTitle');

        $this->display_pc_mobile('company/mingxing_coupons', 'mobile/company/mingxing_coupons');
       
    }

	private function get_coupon_edit_path($bonusType) {
		 switch ($bonusType) {
            case '4':
                $path='company/youhuiquan_edit';
                break;
            case '7':
               $path='company/coupons_edit';
                break;
            case '9':
               $path='company/coupons_edit';   # code...
                break;
            default:
			  $path='company/coupons_edit';
                # code...
                break;
        }
		  return  $path;
		}
		
		
	 function company_category_set_action()
    {   
  
  
        /**
         * 页面信息
         */
 
        $this->setData('设置商家行业类型', 'pagename');
        $this->setData('设置商家行业类型' . $this->site['pageTitle'], 'pageTitle');
  
  
  
  
    if (is_post()) {
  
  
  
        /**
         * 分步骤编辑
         */
        $userCategoryId = post('userCategoryId');

          /**
          * 验证
         */
         if (empty($userCategoryId)) $this->coupons_edit_failure('请将选择至少一个行业');

		/**
		 * 预处理
		 */
		$mdl_infoclass=$this->loadModel('infoClass');
		$userCategoryId= ",".join(',',$userCategoryId).",";

	
			$data = array();
			$mdl_coupons  = $this->loadModel('coupons');
			$data['categoryId'] = $userCategoryId;
			//$data['categoryName'] = $mdl_coupons->getCategoryName($data['categoryId'],$mdl_infoclass);
			
			$mdl_user=$this->loadModel('user');
			// var_dump($data);exit;
			if ($mdl_user->update($data,$this->loginUser['id'])) {
				$redirect = HTTP_ROOT_WWW . 'company/company_category_set';
                 $this->form_response(200,'',$redirect);
			} else {
				$this->coupons_edit_failure('保存失败');
			}
						
	}else{
		
		 /**
                     * 可选产品类型
                     */
                    $mdl_coupon_type = $this->loadModel('coupon_type');

                    if ($coupon_type) {
                        $types = $mdl_coupon_type->getList(null, array('isApproved' => 1, 'id' => $coupon_type), 'sortnum asc');
                    } else {
                        $types = $mdl_coupon_type->getList(null, array('isApproved' => 1,"id in (9,7,4)" ), 'sortnum asc');
                    }

                    $this->setData($types, 'types');


                    /**
                     * 用户曾选的分类
                     */
                    $mdl_infoclass = $this->loadModel('infoClass');

                    $catids = explode(',', $this->loginUser['categoryId']);
                   
                    $userCategory = array();
                    foreach ($catids as $catid) {
                        $cat = $mdl_infoclass->get($catid);

                        if(!$cat)continue;//filter out none exist cat, data still exist in db.

                        $cat_large = $mdl_infoclass->get(substr($catid, 0, 6));

                        $cat['name'] = $cat_large['name'] . '->' . $cat['name'];
                        $userCategory[] = $cat;
                    }
                    $this->setData($userCategory, 'userCategory');
                 
                    /**
                     * 全部可用分类
                     */
                    $categoriesAll = $mdl_infoclass->getListBySql("select id, name, (select count(*) from #@_infoclass where id like concat(c.id,'___')) as childCount from #@_infoclass as c where id like '106%' and lang ='".$this->getLangStr()."' order by ordinal asc");
                    $this->setData($categoriesAll, 'categoriesAll');


                    /**
                    * 分类树形结构数据
                    */
                    $mdl_infoclass=$this->loadModel( 'infoClass' );
                    $categories=  $mdl_infoclass->getChild4( '106' );
                    //var_dump($this->loginUser['business_type_restaurant']);exit;
                    if ($this->loginUser['business_type_restaurant']==1) {
                       unset($categories['106105']);
					     unset($categories['106119']);  
						 unset($categories['106120']); 
						 unset($categories['106124']);
						 
                    }else{

                    }

                    $this->setData($categories, 'categories' );


           
		
		      $this->setData('商家信息修改', 'pagename');
            $this->setData('basic_setting', 'menu');
            $this->setData('company_category_set', 'submenu');
            $this->setData('商家信息修改 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->display('company/company_category_set');
		

	}
			
			
			
			
			
		}				
		
		
		
		
 function restaurant_edit_action()    {   
        /**
         * 加载模组
         */
        $mdl_coupons = $this->loadModel('coupons');
		
		
		//获取是否为线上餐厅
        $restaurant =get2('restaurant');
		if($restaurant) {
			$where00 =array(
		   'createUserId' => $this->loginUser['id'],
		   'EvoucherOrrealproduct' =>'restaurant_menu'
		   );
		   $restaurant_coupon= $mdl_coupons->getByWhere($where00);
		   if($restaurant_coupon) {
			   $id=$restaurant_coupon['id'];
		   }
		}else{
			  $id = (int)get2('id');
		}

        /**
         * 当前编辑第几步
         */
        $step = (int)get2('step');

        if ($step <= 0) $step = 1;
        if ($step > 7) $step = 7;

        $this->setData($step, 'step');
        $this->setData($this->parseUrl()->set('step'), 'stepUrl');

        /**
         * 产品ID
         */
      

        if ($id) {
            //编辑产品
            $coupon = $mdl_coupons->get($id);

            if (!$coupon || $coupon['createUserId'] != $this->loginUser['id']) $this->sheader(null, '产品不存在');

            $this->setData($coupon, 'data');

        }else{
            
        }

        /**
         * 产品类型
         */
        $coupon_type = ($id)?$coupon['bonusType']:(int)get2('coupon_type');

        $this->setData($coupon_type, 'coupon_type');

        if ($coupon_type == 4) {
            $this->setData('voucher', 'submenu');
            $this->setData('index_publish', 'menu');
        }elseif (($coupon_type == 7)) {
            $this->setData('local', 'submenu');
            $this->setData('index_publish', 'menu');
        } elseif (($coupon_type == 9)) {
            $this->setData('shop', 'submenu');
            $this->setData('index_publish', 'menu');
        } else{
            $this->setData('all', 'submenu');
            $this->setData('index_publish', 'menu');
        }


        /**
         * 页面信息
         */
        $this->setData($this->parseUrl(), 'postUrl');
        $this->setData($this->parseUrl()->set('step',$step-1), 'prevUrl');

        $this->setData(($coupon ? '编辑' : '添加') . '产品', 'pagename');
        $this->setData(($coupon ? '编辑' : '添加') . '产品 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
         $mdl_city=$this->loadModel('city');

        /**
         * 分步骤编辑
         */
        switch ($step) {
            case 1:
                if (is_post()) {
                    /**
                     * 接收数据
                     */
                    $title = trim(post('title'));
                    $bonusType = (int)post('bonusType');
                    $userCategoryId = post('userCategoryId');

                    /**
                     * 验证
                     */
                    if (empty($title)) $this->coupons_edit_failure('请添加标题');
                    if (empty($bonusType)) $this->coupons_edit_failure('请选择产品类型');
                    if (empty($userCategoryId)) $this->coupons_edit_failure('请将选择至少一个行业');

                    /**
                     * 预处理
                     */
					$mdl_infoclass=$this->loadModel('infoClass');
                    $userCategoryId= ",".join(',',$userCategoryId).",";

					$mdl_restaurant_info =$this->loadModel('restaurant_info');
					$mdl_restaurant_promotion_manjian =$this->loadModel("restaurant_promotion_manjian");
				
				
						// 插入线上餐厅的相关数据
						
						$data1['is_approved']=1;
						$data1['status']=4;
						$data1['userID']=$this->loginUser['id'];
						$data1['name']=$title;
						
						
						
						$restaurant_info = $mdl_restaurant_info->getByWhere(array('userID'=>$this->loginUser['id']));
				
						if($restaurant_info) {
							$mdl_restaurant_info->update($data1,$restaurant_info['id']);
					
						}else{
							$mdl_restaurant_info->insert($data1);
						}
						
						
						// 更新餐厅优惠数据库数据
				
					
						$restaurant_promotion_manjian = $mdl_restaurant_promotion_manjian->getByWhere(array('restaurant_id'=>$this->loginUser['id']));

						if( $restaurant_promotion_manjian) {
					 							
						}else{
							$data_manjian['restaurant_id'] =$this->loginUser['id'];
							$data_manjian['createUserId'] =$this->loginUser['id'];
							$data_manjian['discount'] =0;
						if($mdl_restaurant_promotion_manjian->insert($data_manjian))  {
						}else{
							$this->form_response_msg('线上餐厅开启失败，请稍候再试');
						}
					}
					
                    if (!$coupon) {
                        $data = array();
                        $data['title'] = $title;
                        $data['bonusType'] = $bonusType;
                        $data['categoryId'] = $userCategoryId;
                        $data['categoryName'] = $mdl_coupons->getCategoryName($data['categoryId'],$mdl_infoclass);
						
						if($restaurant) {
							$data['EvoucherOrrealproduct'] = 'restaurant_menu';
						}
                        //默认值
                        $data['startTime'] = time();
                        $data['endTime'] = strtotime('+ 90 days', time()) - 1;
                        $data['autoOffline'] = false;

                        $data['createUserId'] = $this->loginUser['id'];
                        $data['createTime'] = time();

                        $data['isApproved'] = ($this->loginUser['trustLevel'] >= 1)?1:0;
                        $data['status'] = 1;

                        $data['visibleForBusiness'] = $this->loginUser['visibleForBusiness'];
                        $data['city'] = $this->loginUser['cityId'];
						$data['cityName']= $mdl_city->getCityName($data['city']);
						//var_dump($data['city'].$data['cityName']);exit;
						
                        $data['languageType'] = $this->loginUser['languageType'];
                        $data['businessName'] = $this->loadModel('user')->getBusinessDisplayName($this->loginUser['id']);

                        $data['qty']=0;
                        $data['perCustomerLimitQuantity']=0;
                        $data['perCustomerMinLimitQuantity']=0;

                        $data['deliverFeeCalculationType'] = 'per_coupon';
                        $data['platform_commission_base'] = $this->loginUser['platform_commission_base'];;
                        $data['platform_commission_rate'] = $this->loginUser['platform_commission_rate'];;
                        

                        if ($coupon_id=$mdl_coupons->insert($data)) {
                            $this->restaurant_edit_toStep($coupon_id, 2);
                        } else {
                            $this->coupons_edit_failure('保存失败');
                        }
						
						
							
						

                    } else {

                        $data = array();
                        $data['title'] = $title;
                        $data['bonusType'] = $bonusType;
                        $data['categoryId'] = $userCategoryId;
                        $data['categoryName'] = $mdl_coupons->getCategoryName($data['categoryId'],$mdl_infoclass);
                        if ($this->loginUser['needReapprovedAfterEdit']) {
                            //$data['status'] = 1;
                        }

                        if ($mdl_coupons->update($data, $coupon['id'])) {
                            $this->restaurant_edit_toStep($coupon['id'], 2);
                        } else {
                            $this->coupons_edit_failure('保存失败');
                        }
                    }
					
					
					
					
                } else {
                    
                    /**
                     * 可选产品类型
                     */
                    $mdl_coupon_type = $this->loadModel('coupon_type');

                    if ($coupon_type) {
                        $types = $mdl_coupon_type->getList(null, array('isApproved' => 1, 'id' => $coupon_type), 'sortnum asc');
                    } else {
                        $types = $mdl_coupon_type->getList(null, array('isApproved' => 1,"id in (9,7,4)" ), 'sortnum asc');
                    }

                    $this->setData($types, 'types');


                    /**
                     * 用户曾选的分类
                     */
                    $mdl_infoclass = $this->loadModel('infoClass');

                    $catids = explode(',', $this->loginUser['categoryId']);
                   
                    $userCategory = array();
                    foreach ($catids as $catid) {
                        $cat = $mdl_infoclass->get($catid);

                        if(!$cat)continue;//filter out none exist cat, data still exist in db.

                        $cat_large = $mdl_infoclass->get(substr($catid, 0, 6));

                        $cat['name'] = $cat_large['name'] . '->' . $cat['name'];
                        $userCategory[] = $cat;
                    }
                    $this->setData($userCategory, 'userCategory');
                 
                    /**
                     * 全部可用分类
                     */
                    $categoriesAll = $mdl_infoclass->getListBySql("select id, name, (select count(*) from #@_infoclass where id like concat(c.id,'___')) as childCount from #@_infoclass as c where id like '106%' and lang ='".$this->getLangStr()."' order by ordinal asc");
                    $this->setData($categoriesAll, 'categoriesAll');


                    /**
                    * 分类树形结构数据
                    */
                    $mdl_infoclass=$this->loadModel( 'infoClass' );
                    $categories=  $mdl_infoclass->getChild4( '106' );

                    if ($coupon_type == 4) {

                    }elseif (($coupon_type == 7)) {
                        unset($categories['106124']);
                        unset($categories['106120']);
                        unset($categories['106105']);
                    }elseif (($coupon_type == 9)) {
                        unset($categories['106121']);
                        unset($categories['106119']);
                    }else{

                    }

                    $this->setData($categories, 'categories' );
                    $this->setData('index_publish', 'menu');
                    $this->setData('restaurant_set', 'submenu');
					$this->setData('restaurant_set', 'submenu_top');

                    $this->display('company/restaurant_edit');
                }
                break;
            case 2:
                if (is_post()) {
                    /**
                     * 数据
                     */
                    $country_code = (int)post('country_code');
                    $city = post('city');
                    $startTime=trim(post('startTime'));
                    $endTime=trim(post('endTime'));
                    $cCategoryId_lock=post('cCategoryId_lock');
                    $cCategoryId=post('cCategoryId');
                    
                    $searchKeywords=trim(post('searchKeywords'));
                    $autoOffline=(post('autoOffline'))?1:0;

                    /**
                     * 预处理
                     */
                    $city =($city)?','.join(',', $city).',':' ';

                    $startTime =($startTime)?$startTime:date('Y-m-d');
                    $endTime = $endTime?$endTime:date('Y-m-d', strtotime('+90 days'));
                    $startTime = strtotime($startTime);
                    $endTime = strtotime($endTime);

                    $cCategoryId_lock=($cCategoryId_lock)?1:0;
                    $cCategoryId=",".join(',',post('cCategoryId')).",";


                    /**
                     * 准备更新数据
                     */
                    $data=array();
                    $data['country_code'] = $country_code;
                    $data['city'] = $city;
					$data['cityName']= $mdl_city->getCityName($data['city']);
                    $data['startTime'] = $startTime;
                    $data['endTime'] = $endTime;
                    $data['cCategoryId_lock'] = $cCategoryId_lock;
                    $data['cCategoryId'] = $cCategoryId;
                    $data['searchKeywords'] = $searchKeywords;
                    $data['autoOffline'] = $autoOffline;


                    /**
                     * 处理 coupons_addon_数据
                     */
                    $this->loadModel('coupons_addon')->set($coupon['id'],$_POST);

                    if ($mdl_coupons->update($data, $coupon['id'])) {
                        $this->restaurant_edit_toStep($coupon['id'], 3);
                    } else {
                        $this->coupons_edit_failure('保存失败,请稍后再试');
                    }

                } else {
                     /**
                     * 自定义分类
                     */

                    $mdl = $this->loadModel('customizableCategory');
                    $mdl->setUserId($this->loginUser['id']);
                    $list = $mdl->getTopLevelItemList();
                    foreach ($list as $key => $value) {
                        $list[$key]['hasChild'] = $mdl->hasChild($value['id']);
                    }
                    $this->setData($list, 'tr_list');
                    $table_tr = $this->fetch('customizable_category/table_tr_show');
                    $this->setData($table_tr, 'table_tr');

                    /**
                     * addon data
                     */
                    $addonData = $this->loadModel('coupons_addon')->getAddonData($id);
                    $this->setData($addonData, 'addonData');
					$this->setData('index_publish', 'menu');
                    $this->setData('restaurant_set', 'submenu');
					$this->setData('restaurant_set', 'submenu_top');
                    $this->display('company/restaurant_edit2');
                }
                break;
            case 3:
                if (is_post()) {

                    $images=post('images');
                    if($images){
                        foreach ($images as $key => $value) {
                            if($value=="default/image_upload.jpg")
                                unset($images[$key]);
                            else
                                $images[$key]=trim($value);
                        }
                        $data['pic']=reset($images);

                        $data['pics'] = serialize(array_slice($images, 1));
                    }else{
                       $this->restaurant_edit_toStep($coupon['id'], 4); //no data no update. slow internet error
                    }

                    if ($mdl_coupons->update($data, $coupon['id'])) {
                        $this->restaurant_edit_toStep($coupon['id'], 4);
                    } else {
                        $this->coupons_edit_failure('保存失败');
                    }

                } else {
                    $this->setData('index_publish', 'menu');
                    $this->setData('restaurant_set', 'submenu');
					$this->setData('restaurant_set', 'submenu_top');
                    $this->display('company/restaurant_edit3');

                }
                break;
            case 4:
                $mdl_restaurant_promotion_manjian = $this->loadModel('restaurant_promotion_manjian');
                $where =array(
			    'restaurant_id'=> $coupon['createUserId']
			    );
				$manjian_info =$mdl_restaurant_promotion_manjian->getByWhere($where);
				if($manjian_info) {
					$this->setData($manjian_info,'manjian_info');
				}
				
                if (is_post()) {
                    $data['discount'] = post('discount');
                   $data['promotion_desc'] = post('promotion_desc');
				    $data['promotion_desc_en'] = post('promotion_desc_en');
                   // var_dump($manjian_info['id']);exit;
               
                    //更新价格
					$where1=array(
					  'Restaurant_id'=>$this->loginUser['id']
					);
					
					
					if($mdl_restaurant_promotion_manjian->getByWhere($where1)) {
						
						 $mdl_restaurant_promotion_manjian->update($data, $manjian_info['id']);
						
					}else{
						
						$data['Restaurant_id']=$this->loginUser['id'];
						$data['createUserId']=$this->loginUser['id'];
						$mdl_restaurant_promotion_manjian->insert($data);
					}
					
                   


                   

                    if ($mdl_coupons->errno()) {
                        $mdl_coupons->rollback();
                        $this->coupons_edit_failure('保存失败');
                    } else {
                        $mdl_coupons->commit();
                        $this->restaurant_edit_toStep($coupon['id'], 5);
                    }

                } else {
					
					$this->setData('index_publish', 'menu');
                    $this->setData('restaurant_set', 'submenu');
					$this->setData('restaurant_set', 'submenu_top');

                    switch ($coupon['bonusType']) {
                      
                        case 7:
                            $this->setData($coupon);

                            $this->display('company/restaurant_edit4');
                            break;
                      
                        default:
                            $this->sheader(null,'无此类型');
                            break;
                    }
 

                }
                break;
            case 5:
                if (is_post()) {
                    $coupon_summery_description = post('coupon_summery_description');
                    $highlight                  = post('highlight');
                    $finePrint                  = post('finePrint');
                    $content                    = post('content');
                    $refund_policy              = post('refund_policy');
                    $redeemProcedure            = post('redeemProcedure');

                    $data['coupon_summery_description'] = $coupon_summery_description;  
                    $data['highlight']                  = $highlight;
                    $data['finePrint']                  = $finePrint;
                    $data['content']                    = $content;
                    $data['redeemProcedure']            = $redeemProcedure;
                    $data['refund_policy']              = $refund_policy;

                    if(!$this->loginUser['refund_policy'])$this->loadModel('user')->updateUserById(array('refund_policy'=>$refund_policy), $this->loginUser['id']);

                    if ($mdl_coupons->update($data, $coupon['id'])) {
                        $this->restaurant_edit_toStep($coupon['id'], 6);
                    } else {
                        $this->coupons_edit_failure('保存失败');
                    }
                } else {
					$this->setData('index_publish', 'menu');
                    $this->setData('restaurant_set', 'submenu');
					$this->setData('restaurant_set', 'submenu_top');

                    $this->display('company/restaurant_edit5');

                }
                break;
            case 6:
                if (is_post()) {
                    //获取递送的信息
                    $deliver_avaliable         = trim(post('deliver_avaliable'));
                    $flat_rates_to_local_city  = trim(post('flat_rates_to_local_city'));
                    $flat_rates_national       = trim(post('flat_rates_national'));
                    $flat_rates_international  = trim(post('flat_rates_international'));
                    $pickup_avaliable          = trim(post('pickup_avaliable'));
                    $EvoucherOrrealproduct     = trim(post('EvoucherOrrealproduct'));
                    $deliverFeeCalculationType = trim(post('deliverFeeCalculationType'));

                    $sales_user_list     = post('sales_user_list');
                    $staff_region_limited = trim(post('staff_region_limited'));

                    if(!post('product_deliver_fee_checkbox'))$flat_rates_to_local_city=0;

                    if($coupon['bonusType']=='1'||$coupon['bonusType']=='2'||$coupon['bonusType']=='4')$EvoucherOrrealproduct='evoucher';

                    $data['deliver_avaliable']         = $deliver_avaliable;
                    $data['flat_rates_to_local_city']  = $flat_rates_to_local_city;
                    $data['flat_rates_national']       = $flat_rates_national;
                    $data['flat_rates_international']  = $flat_rates_international;
                    $data['pickup_avaliable']          = $pickup_avaliable;
                    $data['EvoucherOrrealproduct']     = $EvoucherOrrealproduct;
                    $data['deliverFeeCalculationType'] = $deliverFeeCalculationType;
                    $data['sales_user_list']           = join(',',$sales_user_list);
                    $data['staff_region_limited']      = $staff_region_limited;


                    $pickup_des = trim(post('pickup_des'));
                    $delivery_description =  trim(post('delivery_description'));
                    $address =  trim(post('address'));
                    
                    $userData=[];
                    if($address)$userData['googleMap']=$address;
                    if($pickup_des)$userData['pickup_des']=$pickup_des;
                    if($delivery_description)$userData['delivery_description']=$delivery_description;

                    $this->loadModel('user')->updateUserById($userData, $this->loginUser['id']);

                    if ($mdl_coupons->update($data, $coupon['id'])) {
                        $this->restaurant_edit_toStep($coupon['id'], 7);
                    } else {
                        $this->coupons_edit_failure('保存失败');
                    }

                } else {
					$this->setData('index_publish', 'menu');
                    $this->setData('restaurant_set', 'submenu');
					$this->setData('restaurant_set', 'submenu_top');

                    /**
                     * 加载全部员工组
                     */
                    $mdl_user = $this->loadModel('user');
                    $staff_list=$mdl_user->getAllStaff($this->loginUser['id']);
                    $this->setData($staff_list, 'staff_list');

                    /**
                     * 已选员工
                     */
                    $this->setData(explode(',',$coupon['sales_user_list']),'selected_sales_user_list');


                    $this->display('company/restaurant_edit6');

                }
                break;
            case 7:
                if (is_post()) {
                    $userData=[];
                    $address =  trim(post('address'));
                    if($address)$userData['googleMap']=$address;

                    $noAlert = trim(post('noAlert'));
                    if($noAlert)$userData['noAlert']=1;

                    $this->loadModel('user')->updateUserById($userData, $this->loginUser['id']);


                    if($coupon['status']==4){
                        $this->form_response(200,'修改成功',HTTP_ROOT_WWW .'company/coupons?coupon_type='.$coupon['bonusType']);
                    }else{
                        $confirm = (int)post('confirm');
                        if (!$confirm) $this->coupons_edit_failure('请先阅读产品发布说明');
                        $data['status'] = 4;
                        if ($mdl_coupons->update($data, $coupon['id'])) {
                            $this->form_response(200,'发布成功','SELF');
                        } else {
                            $this->coupons_edit_failure('发布失败');
                        }
                    }

                   

                } else {
                    $couponLink="http://$_SERVER[HTTP_HOST]/coupon1/".$id;
                    $this->setData($couponLink,'couponLink');
                    $this->setData(generateQRCode($couponLink),'couponQrCode');

                    $storeLink="http://$_SERVER[HTTP_HOST]/store/".$this->loginUser['id'];
                    $this->setData($storeLink,'storeLink');
                    $this->setData(generateQRCode($storeLink),'storeQrCode');

                    $packageLink="http://$_SERVER[HTTP_HOST]/coupon/4894";
                    $this->setData($packageLink,'packageLink');
                    $this->setData(generateQRCode($packageLink),'packageQrCode');

                    $listUrl=HTTP_ROOT_WWW."company/coupons?coupon_type=".$coupon['bonusType'];
                    $this->setData($listUrl,'listUrl');

                    $coupon_delivery_info =$mdl_coupons->getDeliveryInfo($id);
                    $this->setData( $coupon_delivery_info, 'coupon_delivery_info' );
                    $this->setData('index_publish', 'menu');
                    $this->setData('restaurant_set', 'submenu');
					$this->setData('restaurant_set', 'submenu_top');
                    $this->display('company/restaurant_edit7');

                }
                break;
        }
    }
	
	

		
    function coupons_edit_action()
    {   
        /**
         * 加载模组
         */
        $mdl_coupons = $this->loadModel('coupons');
		
		
		//获取是否为线上餐厅
        $restaurant =get2('restaurant');
		if($restaurant) {
			$where00 =array(
		   'createUserId' => $this->loginUser['id'],
		   'EvoucherOrrealproduct' =>'restaurant_menu'
		   );
		   $restaurant_coupon= $mdl_coupons->getByWhere($where00);
		   if($restaurant_coupon) {
			   $id=$restaurant_coupon['id'];
		   }
		}else{
			  $id = (int)get2('id');
		}

        /**
         * 当前编辑第几步
         */
        $step = (int)get2('step');

        if ($step <= 0) $step = 1;
        if ($step > 7) $step = 7;

        $this->setData($step, 'step');
        $this->setData($this->parseUrl()->set('step'), 'stepUrl');

        /**
         * 产品ID
         */
      

        if ($id) {
            //编辑产品
            $coupon = $mdl_coupons->get($id);

            if (!$coupon || $coupon['createUserId'] != $this->loginUser['id']) $this->sheader(null, '产品不存在');

            $this->setData($coupon, 'data');
			//var_dump($coupon );exit;

        }else{
            
        }

        /**
         * 产品类型
         */
        $coupon_type = ($id)?$coupon['bonusType']:(int)get2('coupon_type');

        $this->setData($coupon_type, 'coupon_type');

        if ($coupon_type == 4) {
            $this->setData('voucher', 'submenu');
            $this->setData('index_publish', 'menu');
        }elseif (($coupon_type == 7)) {
            $this->setData('local', 'submenu');
            $this->setData('index_publish', 'menu');
        } elseif (($coupon_type == 9)) {
            $this->setData('shop', 'submenu');
            $this->setData('index_publish', 'menu');
        } else{
            $this->setData('all', 'submenu');
            $this->setData('index_publish', 'menu');
        }

        if($restaurant){
            $this->setData('restaurant_set', 'submenu');
            $this->setData('restaurant', 'menu');
        }

        /**
         * 页面信息
         */
        $this->setData($this->parseUrl(), 'postUrl');
        $this->setData($this->parseUrl()->set('step',$step-1), 'prevUrl');

        $this->setData(($coupon ? '编辑' : '添加') . '产品', 'pagename');
        $this->setData(($coupon ? '编辑' : '添加') . '产品 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
       $mdl_city=$this->loadModel('city');

        /**
         * 分步骤编辑
         */
        switch ($step) {
            case 1:
                if (is_post()) {
                    /**
                     * 接收数据
                     */
                    $title = trim(post('title'));
                    $bonusType = (int)post('bonusType');
                    $userCategoryId = post('userCategoryId');
					$languageType_cn = post('languageType_cn');
					$languageType_en = post('languageType_en');
					$title_en = post('title_en');
					
					// $this->coupons_edit_failure($languageType);
                    /**
                     * 验证
                     */
                    if (empty($title)) $this->coupons_edit_failure('请添加标题');
                    if (empty($bonusType)) $this->coupons_edit_failure('请选择产品类型');
                    if (empty($userCategoryId)) $this->coupons_edit_failure('请将选择至少一个产品类别');
					 if (empty($languageType_cn) && empty($languageType_en)) $this->coupons_edit_failure('至少选择一种语言');
					 
                    /**
                     * 预处理
                     */
                    $userCategoryId= ",".join(',',$userCategoryId).",";
					$mdl_infoclass=$this->loadModel('infoClass');
					
                    if (!$coupon) {
                        $data = array();
                        $data['title'] = $title;
						 $data['title_en'] = $title_en;
                       
					   $data['bonusType'] = $bonusType;
                        $data['categoryId'] = $userCategoryId;
						$data['categoryName'] = $mdl_coupons->getCategoryName($data['categoryId'],$mdl_infoclass);
						
						if($restaurant) {
							$data['EvoucherOrrealproduct'] = 'restaurant_menu';
						}
                        //默认值
                        $data['startTime'] = time();
                        $data['endTime'] = strtotime('+ 90 days', time()) - 1;
                        $data['autoOffline'] = false;

                        $data['createUserId'] = $this->loginUser['id'];
                        $data['createTime'] = time();

                        $data['isApproved'] = ($this->loginUser['trustLevel'] >= 1)?1:0;
                        $data['status'] = 1;

                        $data['visibleForBusiness'] = $this->loginUser['visibleForBusiness'];
                        $data['city'] = $this->loginUser['cityId'];
						
						
						$data['cityName']= $mdl_city->getCityName($data['city']);
						//var_dump($data['city'].$data['cityName']);exit;
						
                        $data['languageType'] = $this->loginUser['languageType'];
						
						if ($languageType_cn) {
							 $data['languageType_cn'] = 1;
							// $this->coupons_edit_failure('选择了中文');
						}else{
							
							 $data['languageType_cn'] = 0;
						}
						
						if ($languageType_en) {
							 $data['languageType_en'] = 1;
							// $this->coupons_edit_failure('选择了英文');
							
						}else{
							
							 $data['languageType_en'] = 0;
						}
						                    
						
						
						$data['businessName'] = $this->loadModel('user')->getBusinessDisplayName($this->loginUser['id']);

                        $data['qty']=0;
                        $data['perCustomerLimitQuantity']=0;
                        $data['perCustomerMinLimitQuantity']=0;

                        $data['deliverFeeCalculationType'] = 'per_coupon';
                        $data['platform_commission_base'] = $this->loginUser['platform_commission_base'];;
                        $data['platform_commission_rate'] = $this->loginUser['platform_commission_rate'];;
                        

                        if ($coupon_id=$mdl_coupons->insert($data)) {
						 
                            $this->coupons_edit_toStep($coupon_id, 2);
                        } else {
                            $this->coupons_edit_failure('保存失败');
                        }

                    } else {

                        $data = array();
                        $data['title'] = $title;
						 $data['title_en'] = $title_en;
                        $data['bonusType'] = $bonusType;
                        $data['categoryId'] = $userCategoryId;
                        $data['categoryName'] = $mdl_coupons->getCategoryName($data['categoryId'],$mdl_infoclass);
						if ($languageType_cn) {
							 $data['languageType_cn'] = 1;
							// $this->coupons_edit_failure('选择了中文');
						}else{
							
							 $data['languageType_cn'] = 0;
						}
						
						if ($languageType_en) {
							 $data['languageType_en'] = 1;
							// $this->coupons_edit_failure('选择了英文');
							
						}else{
							
							 $data['languageType_en'] = 0;
						}
						          
						
                        if ($this->loginUser['needReapprovedAfterEdit']) {
                            //$data['status'] = 1;
                        }

                        if ($mdl_coupons->update($data, $coupon['id'])) {
						
                            $this->coupons_edit_toStep($coupon['id'], 2);
                        } else {
                            $this->coupons_edit_failure('保存失败');
                        }
                    }
                } else {
                    
                    /**
                     * 可选产品类型
                     */
                    $mdl_coupon_type = $this->loadModel('coupon_type');

                    if ($coupon_type) {
                       // $types = $mdl_coupon_type->getList(null, array('isApproved' => 1, 'id' => $coupon_type), 'sortnum asc');
					   $types = $mdl_coupon_type->getList(null, array('isApproved' => 1) ,'sortnum asc');
                    } else {
                      //  $types = $mdl_coupon_type->getList(null, array('isApproved' => 1,"id in (9,7)" ), 'sortnum asc');
					  $types = $mdl_coupon_type->getList(null, array('isApproved' => 1), 'sortnum asc');
                    }

                    $this->setData($types, 'types');


                    /**
                     * 用户曾选的分类
                     */
                    $mdl_infoclass = $this->loadModel('infoClass');

                    $catids = explode(',', $this->loginUser['categoryId']);
                   
                    $userCategory = array();
                    foreach ($catids as $catid) {
                        $cat = $mdl_infoclass->get($catid);

                        if(!$cat)continue;//filter out none exist cat, data still exist in db.

                        $cat_large = $mdl_infoclass->get(substr($catid, 0, 6));

                        $cat['name'] = $cat_large['name'] . '->' . $cat['name'];
                        $userCategory[] = $cat;
                    }
                    $this->setData($userCategory, 'userCategory');
                 
                    /**
                     * 全部可用分类
                     */
                    $categoriesAll = $mdl_infoclass->getListBySql("select id, name, (select count(*) from #@_infoclass where id like concat(c.id,'___')) as childCount from #@_infoclass as c where id like '106%' and lang ='".$this->getLangStr()."' order by ordinal asc");
                    $this->setData($categoriesAll, 'categoriesAll');


                    /**
                    * 分类树形结构数据
                    */
                    $mdl_infoclass=$this->loadModel( 'infoClass' );
                    $categories=  $mdl_infoclass->getChild4( '106' );

                    if ($coupon_type == 4) {

                    }elseif (($coupon_type == 7)) {
                       // unset($categories['106124']);
                       // unset($categories['106120']);
                       // unset($categories['106105']);
                    }elseif (($coupon_type == 9)) {
                       // unset($categories['106121']);
                       // unset($categories['106119']);
                    }else{

                    }

                    $this->setData($categories, 'categories' );


                    $this->display('company/coupons_edit');
                }
                break;
            case 2:
                if (is_post()) {
                    /**
                     * 数据
                     */
                    $country_code = (int)post('country_code');
                    $city = post('city');
                    $startTime=trim(post('startTime'));
                    $endTime=trim(post('endTime'));
					
                    $cCategoryId_lock=post('cCategoryId_lock');
                    $cCategoryId=post('cCategoryId');
					$buy_link=post('buy_link');
					
                    $searchKeywords=trim(post('searchKeywords'));
                    $autoOffline=(post('autoOffline'))?1:0;

                    
                   // var_dump($multi_use);exit;
				   
				   $is_multi_use = (int)post('is_multi_use');
				   
				   if($is_multi_use) {
					   $multi_use=(int)post('multi_use');
					   //如果为多次服务，将会自动清楚改产品相关子卡信息。
					   $where101=array(
							'create_user_id'=>$this->loginUser['id'],
							'parent_coupon_id'=>$coupon['id']
							   
					   );
					   $mdl_coupon_sub=$this->loadModel("coupons_sub");
					   $mdl_coupon_sub->deleteByWhere($where101);
					     
				   }else{
					    $multi_use = 1;
					   
				   }
				   

                    /**
                     * 预处理
                     */
                    $city =($city)?','.join(',', $city).',':' ';

                    $startTime =($startTime)?$startTime:date('Y-m-d');
                    $endTime = $endTime?$endTime:date('Y-m-d', strtotime('+90 days'));
                    $startTime = strtotime($startTime);
                    $endTime = strtotime($endTime);

                    $cCategoryId_lock=($cCategoryId_lock)?1:0;
                    $cCategoryId=",".join(',',post('cCategoryId')).",";


                    /**
                     * 准备更新数据
                     */
                    $data=array();
                    $data['country_code'] = $country_code;
                    $data['city'] = $city;
					$data['cityName']= $mdl_city->getCityName($data['city']);
                    $data['startTime'] = $startTime;
                    $data['endTime'] = $endTime;
                    $data['cCategoryId_lock'] = $cCategoryId_lock;
                    $data['cCategoryId'] = $cCategoryId;
                    $data['searchKeywords'] = $searchKeywords;
                    $data['autoOffline'] = $autoOffline;
					$data['buy_link'] = $buy_link;
					$data['multi_use']=$multi_use;
					
				
						


                    /**
                     * 处理 coupons_addon_数据
                     */
                    $this->loadModel('coupons_addon')->set($coupon['id'],$_POST);

                    if ($mdl_coupons->update($data, $coupon['id'])) {
                        $this->coupons_edit_toStep($coupon['id'], 3);
                    } else {
                        $this->coupons_edit_failure('保存失败,请稍后再试');
                    }

                } else {
                     /**
                     * 自定义分类
                     */
                   
                    $mdl = $this->loadModel('customizableCategory');
                    $mdl->setUserId($this->loginUser['id']);
                    $list = $mdl->getTopLevelItemList();
                    foreach ($list as $key => $value) {
                        $list[$key]['hasChild'] = $mdl->hasChild($value['id']);
                    }
                    $this->setData($list, 'tr_list');
                    $table_tr = $this->fetch('customizable_category/table_tr_show');
                    $this->setData($table_tr, 'table_tr');

                    /**
                     * addon data
                     */
                    $addonData = $this->loadModel('coupons_addon')->getAddonData($id);
                    $this->setData($addonData, 'addonData');

                    $this->display('company/coupons_edit2');
                }
                break;
            case 3:
                if (is_post()) {

                    $images=post('images');
                    if($images){
                        foreach ($images as $key => $value) {
                            if($value=="default/image_upload.jpg")
                                unset($images[$key]);
                            else
                                $images[$key]=trim($value);
                        }
                        $data['pic']=reset($images);

                        $data['pics'] = serialize(array_slice($images, 1));
                    }else{
                       $this->coupons_edit_toStep($coupon['id'], 4); //no data no update. slow internet error
                    }

                    if ($mdl_coupons->update($data, $coupon['id'])) {
                        $this->coupons_edit_toStep($coupon['id'], 4);
                    } else {
                        $this->coupons_edit_failure('保存失败');
                    }

                } else {

                    $this->display('company/coupons_edit3');

                }
                break;
            case 4:
                $mdl_coupons_sub = $this->loadModel('coupons_sub');
               
                if (is_post()) {
                    $data['voucher_deal_amount'] = post('voucher_deal_amount');
                    $data['voucher_original_amount'] = post('voucher_original_amount');

                    $qty = (int)post('qty');
                    if ($qty < 0) $qty = 0;
                    if ($qty > 999999) $qty = 999999;

                    $perCustomerLimitQuantity = (int)post('perCustomerLimitQuantity');
                    if ($perCustomerLimitQuantity < 0) $perCustomerLimitQuantity = 0;
                    if ($perCustomerLimitQuantity > 999999) $perCustomerLimitQuantity = 999999;

                    $perCustomerMinLimitQuantity = (int)post('perCustomerMinLimitQuantity');
                    if ($perCustomerMinLimitQuantity < 0) $perCustomerMinLimitQuantity = 0;
                    if ($perCustomerMinLimitQuantity > 999999) $perCustomerMinLimitQuantity = 999999;

                    $data['qty'] = $qty;
                    $data['perCustomerLimitQuantity'] = $perCustomerLimitQuantity;
                    $data['perCustomerMinLimitQuantity'] = $perCustomerMinLimitQuantity;

                    //更新价格
                    $mdl_coupons->update($data, $coupon['id']);


                    switch ($coupon['bonusType']) {
                        case 1:
                            break;
                        case 4:
                        case 7:
                            //子卡
                            $isSubCoupons = (int)post('isSubCoupons');  //是否勾选了创建子卡复选框

                            $mdl_coupons->begin();

                            if ($isSubCoupons) {

                                $sub_id = post('sub_id');
                                
                                $sub_title = post('sub_title');
                                $sub_description = post('product_description');
                                $sub_customer_amount = post('sub_customer_amount');
                                $sub_original_amount = post('sub_original_amount');
                                $sub_quantity = post('sub_quantity');
                               
                                //更新子卡
                                foreach ($sub_id as $k => $sid) {
                                    
                                    $sid = (int)$sid;

                                    $sc_data['title'] = trim($sub_title[$k]);
                                    $sc_data['product_description'] = trim($sub_description[$k]);
                                    $sc_data['customer_amount'] = (float)$sub_customer_amount[$k];
                                    $sc_data['original_amount'] = (float)$sub_original_amount[$k];
                                    $sc_data['quantity'] =  $sub_quantity[$k];
                                   
                                    if($sid==0){
                                        
                                        $sc_data['parent_coupon_id'] = $coupon['id'];
                                        $sc_data['create_user_id'] = $this->loginUser['id'];
                                        $sc_data['create_time'] = time();

                                        $mdl_coupons_sub->insert($sc_data);
                                    }else{
                                        //如果在原列表中没有 删除
                                        $mdl_coupons_sub->updateByWhere($sc_data, array('id' => $sid, 'parent_coupon_id' => $coupon['id']));
                                    }
                                   
                                }

                            } else {
                                //全部删除
                                 $mdl_coupons_sub->deleteByWhere( array('parent_coupon_id' => $coupon['id']));
                            }

                            break;
							
						case 18:
                            //子卡
                            $isSubCoupons = (int)post('isSubCoupons');  //是否勾选了创建子卡复选框

                            $mdl_coupons->begin();

                            if ($isSubCoupons) {

                                $sub_id = post('sub_id');
                                
                                $sub_title = post('sub_title');
                                $sub_description = post('product_description');
                                $sub_customer_amount = post('sub_customer_amount');
                                $sub_original_amount = post('sub_original_amount');
                                $sub_quantity = post('sub_quantity');
                               
                                //更新子卡
                                foreach ($sub_id as $k => $sid) {
                                    
                                    $sid = (int)$sid;

                                    $sc_data['title'] = trim($sub_title[$k]);
                                    $sc_data['product_description'] = trim($sub_description[$k]);
                                    $sc_data['customer_amount'] = (float)$sub_customer_amount[$k];
                                    $sc_data['original_amount'] = (float)$sub_original_amount[$k];
                                    $sc_data['quantity'] =  $sub_quantity[$k];
                                   
                                    if($sid==0){
                                        
                                        $sc_data['parent_coupon_id'] = $coupon['id'];
                                        $sc_data['create_user_id'] = $this->loginUser['id'];
                                        $sc_data['create_time'] = time();

                                        $mdl_coupons_sub->insert($sc_data);
                                    }else{
                                        //如果在原列表中没有 删除
                                        $mdl_coupons_sub->updateByWhere($sc_data, array('id' => $sid, 'parent_coupon_id' => $coupon['id']));
                                    }
                                   
                                }

                            } else {
                                //全部删除
                                 $mdl_coupons_sub->deleteByWhere( array('parent_coupon_id' => $coupon['id']));
                            }

                            break;
                        case 9:

                            $data=array();
                            $stripCode = post('stirpcode');
                            $useguige = post('useguige');
                            $includeGst = post('include_gst') == 'on';

                            if($coupon['useguige']!=$useguige||$coupon['stripCode']!=$stripCode){
                                $this->loadModel('guige_link')->deleteGuigeLinkByCouponId($coupon['id']);
                            }

                            $data['useguige'] = $useguige;
                            $data['stripCode'] = $stripCode;
                            $data['include_gst'] = $includeGst;

                            //更新规格
                            $mdl_coupons->update($data, $coupon['id']);


                            //更新批发价
                            $useWholesale=post('useWholesale');
                            $wholesaleAmount=post('wholesaleAmount');
                            $wholesalePrice=post('wholesalePrice');

                            $mdl_wholesale = $this->loadModel('wholesale');

                            if($useWholesale){
                                $mdl_wholesale->saveWholesale($id,$this->loginUser['id'], $wholesaleAmount,$wholesalePrice);
                            }else{
                                $mdl_wholesale->delete($id);
                            }

                            break;
                        case 10:
                            break;
                        default:
                            $this->sheader(null,'无此类型');
                            break;
                    }
                   

                    if ($mdl_coupons->errno()) {
                        $mdl_coupons->rollback();
                        $this->coupons_edit_failure('保存失败');
                    } else {
                        $mdl_coupons->commit();
                        $this->coupons_edit_toStep($coupon['id'], 5);
                    }

                } else {

                    switch ($coupon['bonusType']) {
                        case 1:
                            $this->display('company/coupons_edit4_1');
                            break;
                        case 4:

                            $coupon['subCoupons'] = $mdl_coupons_sub->getList(null, array('parent_coupon_id' => $coupon['id']), 'id asc');
                            $this->setData($coupon);

                            $this->display('company/coupons_edit4_4');
                            break;
                        case 7:
                            $coupon['subCoupons'] = $mdl_coupons_sub->getList(null, array('parent_coupon_id' => $coupon['id']), 'id asc');
                            $this->setData($coupon);

                            $this->display('company/coupons_edit4_7');
                            break;
						 case 18:
                            $coupon['subCoupons'] = $mdl_coupons_sub->getList(null, array('parent_coupon_id' => $coupon['id']), 'id asc');
                            $this->setData($coupon);

                            $this->display('company/coupons_edit4_7');
                            break;
                        case 9:
                            // 开始获取是否有产品规格、尺寸之类的数据
                            $mdl_shop_guige = $this->loadModel('shop_guige');
                            $mdl_shop_guige_details = $this->loadModel('shop_guige_details');
                            $sql = "select * from cc_shop_guige where (coupon_id=0 or coupon_id=" . $coupon['id'] . ") and userId=" . $this->loginUser['id'];
                            $list = $mdl_shop_guige->getListBySql($sql);

                            // 开始填入每个规格的具体明细参数
                            foreach ($list as $key => $val) {
                                $sql_guige_details = "select * from cc_shop_guige_details where guige_id=" . $list[$key]['id'];
                                $list[$key]['guige_details'] = $mdl_shop_guige_details->getListBySql($sql_guige_details);
                            }
                            $this->setData($list, 'list');

                            $wholesaleData=$this->loadModel('wholesale')->getwholesale($id);
                            $this->setData($wholesaleData,'wholesaleData');

                            $this->display('company/coupons_edit4_9');
                            break;
                        case 10:
                            $this->display('company/coupons_edit4_10');
                            break;
                        case 11:
                            $this->display('company/coupons_edit4_11');
                            break;
                        default:
                            $this->sheader(null,'无此类型');
                            break;
                    }
 

                }
                break;
            case 5:
                if (is_post()) {
                    $coupon_summery_description = post('coupon_summery_description');
                    $highlight                  = post('highlight');
                    $finePrint                  = post('finePrint');
                    $content                    = post('content');
                    $refund_policy              = post('refund_policy');
                    $redeemProcedure            = post('redeemProcedure');
					
					$coupon_summery_description_en = post('coupon_summery_description_en');
                    $highlight_en                  = post('highlight_en');
                    $finePrint_en                  = post('finePrint_en');
                    $content_en                    = post('content_en');
                    $refund_policy_en              = post('refund_policy_en');
                    $redeemProcedure_en            = post('redeemProcedure_en');

                    $data['coupon_summery_description_en'] = $coupon_summery_description_en;  
                    $data['highlight_en']                  = $highlight_en;
                    $data['finePrint_en']                  = $finePrint_en;
                    $data['content_en']                    = $content_en;
                    $data['redeemProcedure_en']            = $redeemProcedure_en;
                    $data['refund_policy_en']              = $refund_policy_en;
					
					$data['coupon_summery_description'] = $coupon_summery_description;  
                    $data['highlight']                  = $highlight;
                    $data['finePrint']                  = $finePrint;
                    $data['content']                    = $content;
                    $data['redeemProcedure']            = $redeemProcedure;
                    $data['refund_policy']              = $refund_policy;

                    if(!$this->loginUser['refund_policy'])$this->loadModel('user')->updateUserById(array('refund_policy'=>$refund_policy), $this->loginUser['id']);

                    if ($mdl_coupons->update($data, $coupon['id'])) {
                        $this->coupons_edit_toStep($coupon['id'], 6);
                    } else {
                        $this->coupons_edit_failure('保存失败');
                    }
                } else {

                    $this->display('company/coupons_edit5');

                }
                break;
            case 6:
                if (is_post()) {
                    //获取递送的信息
                    $deliver_avaliable         = trim(post('deliver_avaliable'));
                    $flat_rates_to_local_city  = trim(post('flat_rates_to_local_city'));
                    $flat_rates_national       = trim(post('flat_rates_national'));
                    $flat_rates_international  = trim(post('flat_rates_international'));
                    $pickup_avaliable          = trim(post('pickup_avaliable'));
                    $EvoucherOrrealproduct     = trim(post('EvoucherOrrealproduct'));
                    $deliverFeeCalculationType = trim(post('deliverFeeCalculationType'));

                    $sales_user_list     = post('sales_user_list');
                    $staff_region_limited = trim(post('staff_region_limited'));

                    if(!post('product_deliver_fee_checkbox'))$flat_rates_to_local_city=0;

                    if($coupon['bonusType']=='1'||$coupon['bonusType']=='2'||$coupon['bonusType']=='4' ||$coupon['bonusType']=='18')$EvoucherOrrealproduct='evoucher';

                    $data['deliver_avaliable']         = $deliver_avaliable;
                    $data['flat_rates_to_local_city']  = $flat_rates_to_local_city;
                    $data['flat_rates_national']       = $flat_rates_national;
                    $data['flat_rates_international']  = $flat_rates_international;
                    $data['pickup_avaliable']          = $pickup_avaliable;
                    $data['EvoucherOrrealproduct']     = $EvoucherOrrealproduct;
                    $data['deliverFeeCalculationType'] = $deliverFeeCalculationType;
                    $data['sales_user_list']           = join(',',$sales_user_list);
                    $data['staff_region_limited']      = $staff_region_limited;

                    $data['fourpx_sku']      = trim(post('fourpx_sku'));

                    $pickup_des = trim(post('pickup_des'));
                    $delivery_description =  trim(post('delivery_description'));
                    $address =  trim(post('address'));
                    
                    $userData=[];
                    if($address)$userData['googleMap']=$address;
                    if($pickup_des)$userData['pickup_des']=$pickup_des;
                    if($delivery_description)$userData['delivery_description']=$delivery_description;

                    $this->loadModel('user')->updateUserById($userData, $this->loginUser['id']);

                    if ($mdl_coupons->update($data, $coupon['id'])) {
                        $this->coupons_edit_toStep($coupon['id'], 7);
                    } else {
                        $this->coupons_edit_failure('保存失败');
                    }

                } else {

                    /**
                     * 加载全部员工组
                     */
                    $mdl_user = $this->loadModel('user');
                    $staff_list=$mdl_user->getAllStaff($this->loginUser['id']);
                    $this->setData($staff_list, 'staff_list');

                    /**
                     * 已选员工
                     */
                    $this->setData(explode(',',$coupon['sales_user_list']),'selected_sales_user_list');


                    $this->display('company/coupons_edit6');

                }
                break;
            case 7:
                if (is_post()) {
                    $userData=[];
                    $address =  trim(post('address'));
                    if($address)$userData['googleMap']=$address;

                    $noAlert = trim(post('noAlert'));
                    if($noAlert)$userData['noAlert']=1;

                    $this->loadModel('user')->updateUserById($userData, $this->loginUser['id']);


                    if($coupon['status']==4){
                        $this->form_response(200,'修改成功',HTTP_ROOT_WWW .'company/coupons?coupon_type='.$coupon['bonusType']);
                    }else{
                        $confirm = (int)post('confirm');
                        if (!$confirm) $this->coupons_edit_failure('请先阅读产品发布说明');
                        $data['status'] = 4;
                        if ($mdl_coupons->update($data, $coupon['id'])) {
                            $this->form_response(200,'发布成功','SELF');
                        } else {
                            $this->coupons_edit_failure('发布失败');
                        }
                    }

                   

                } else {
                    $couponLink="http://$_SERVER[HTTP_HOST]/coupon1/".$id;
                    $this->setData($couponLink,'couponLink');
                    $this->setData(generateQRCode($couponLink),'couponQrCode');

                    $storeLink="http://$_SERVER[HTTP_HOST]/store/".$this->loginUser['id'];
                    $this->setData($storeLink,'storeLink');
                    $this->setData(generateQRCode($storeLink),'storeQrCode');

                    $packageLink="http://$_SERVER[HTTP_HOST]/coupon/4894";
                    $this->setData($packageLink,'packageLink');
                    $this->setData(generateQRCode($packageLink),'packageQrCode');

                    $listUrl=HTTP_ROOT_WWW."company/coupons?coupon_type=".$coupon['bonusType'];
                    $this->setData($listUrl,'listUrl');

                    $coupon_delivery_info =$mdl_coupons->getDeliveryInfo($id);
                    $this->setData( $coupon_delivery_info, 'coupon_delivery_info' );
                  
                    $this->display('company/coupons_edit7');

                }
                break;
        }
    }

	
	function youhuiquan_quick_add_action()
    {   
        /**
         * 加载模组
         */
        $mdl_coupons = $this->loadModel('coupons');
		
	    
        /**
         * 页面信息
         */
        $this->setData('快速添加代金券', 'pagename');
        $this->setData('快速添加代金券' . $this->site['pageTitle'], 'pageTitle');
      

        /**
         * 分步骤编辑
         */
       
                if (is_post()) {
                    /**
                     * 接收数据
                     */
                    $title = trim(post('title'));
                    $userCategoryId = post('userCategoryId');

                    /**
                     * 验证
                     */
                    if (empty($title)) $this->coupons_edit_failure('请添加标题');
                    if (empty($userCategoryId)) $this->coupons_edit_failure('请将选择至少一个产品类别');

					
					$googleMap = trim(post('googleMap'));
					 if (empty($googleMap)) $this->coupons_edit_failure('请输入商家地址!');
					
					$voucher_deal_amount= 2.00;
                    $voucher_original_amount	= 5.00;	
					$main_coupon_title =$title;
					
					
				
					 $data['voucher_deal_amount'] =$voucher_deal_amount;
                     $data['voucher_original_amount'] = $voucher_original_amount;
					
					
					$businessName=$this->loadModel('user')->getBusinessDisplayName($this->loginUser['id']);
					
				     /**
                     * 预处理
                     */
                    $userCategoryId= ",".join(',',$userCategoryId).",";
					$mdl_infoclass=$this->loadModel('infoClass');
					$mdl_city=$this->loadModel('city');
					
                 
                        $data = array();
                        $data['title'] = $main_coupon_title;
                        $data['bonusType'] =4;
                        $data['categoryId'] = $userCategoryId;
						$data['categoryName'] = $mdl_coupons->getCategoryName($data['categoryId'],$mdl_infoclass);
						
					    //默认值
                     
                        $data['autoOffline'] = false;

                        $data['createUserId'] = $this->loginUser['id'];
                        $data['createTime'] = time();

                        $data['isApproved'] = ($this->loginUser['trustLevel'] >= 1)?1:0;
                        $data['status'] = 1;

                        $data['visibleForBusiness'] = $this->loginUser['visibleForBusiness'];
                        $data['city'] = $this->loginUser['cityId'];
						
						
                        $data['languageType'] = $this->loginUser['languageType'];
						
                        $data['businessName'] = $businessName;
						
						

                        $data['qty']=999999;
                        $data['perCustomerLimitQuantity']=0;
                        $data['perCustomerMinLimitQuantity']=0;

                        $data['deliverFeeCalculationType'] = 'per_coupon';
                        $data['platform_commission_base'] = 0;
                        $data['platform_commission_rate'] = 100;
                        $data['voucher_deal_amount'] =$voucher_deal_amount;
                        $data['voucher_original_amount'] =  $voucher_original_amount;
                        $data['coupon_summery_description'] = '在线领取'.$businessName.'优惠券到店消费时获得优惠券描述的折扣减免';											
                                        
						

						
						
					$country_code = (int)post('country_code');
                    $city = post('city');
                    $startTime=trim(post('startTime'));
                    $endTime=trim(post('endTime'));
                   
                    $searchKeywords=trim(post('searchKeywords'));
                  

                
                    $city =($city)?','.join(',', $city).',':' ';
 
                    $startTime =($startTime)?$startTime:date('Y-m-d');
                    $endTime = $endTime?$endTime:date('Y-m-d', strtotime('+10000 days'));
					
                    $startTime = strtotime($startTime);
                    $endTime = strtotime($endTime);

                  
  
                    $data['country_code'] = $country_code;
                    $data['city'] = $city;
					
					$data['cityName']= $mdl_city->getCityName($data['city']);
                    $data['startTime'] = $startTime;
                    $data['endTime'] = $endTime;
                   
                    $data['searchKeywords'] = $searchKeywords;
                  


                  
						
				  /* step3______________________________________________________________*/
						
						
					
						
						
					$pic=post('main_pic');
					$picpath='youhuiquan/yellow.jpg';
					if($pic =='yellow') {
						
					 $picpath='youhuiquan/yellow.jpg';
					}
					if($pic =='skyblue') {
						
						$picpath='youhuiquan/skyblue.jpg';
					}
					if($pic =='red') {
						
						$picpath='youhuiquan/red.jpg';
					}
					if($pic =='self') {
						
					$images=post('images');
                    if($images){
                        foreach ($images as $key => $value) {
                            if($value=="default/image_upload.jpg")
                                unset($images[$key]);
                            else
                                $images[$key]=trim($value);
                        }
                        $picpath=reset($images);

                        $data['pics'] = serialize(array_slice($images, 1));
                    }	
					}
					
                 
                    $data['pic']=$picpath;

				   // var_dump($data['pic']);exit;
						
						
			
                    $finePrint                  = post('finePrint');
					$refund_policy              = post('refund_policy');
                    $redeemProcedure            = post('redeemProcedure');

                  
             
                    $data['finePrint']                  = $finePrint;
                  
                    $data['redeemProcedure']            = $redeemProcedure;
                    $data['refund_policy']              = $refund_policy;

                    if(!$this->loginUser['refund_policy'])$this->loadModel('user')->updateUserById(array('refund_policy'=>$refund_policy), $this->loginUser['id']);

						
                        if ($coupon_id=$mdl_coupons->insert($data)) {
							
							/* 插入子券处理 */
					
                                      /**
									* 处理 coupons_addon_数据
                     */
								$this->loadModel('coupons_addon')->set($coupon_id,$_POST);
							
                                   
									
					
					
						
							$googleMap = trim(post('googleMap'));
							$google_location = trim(post('location')); //12.123123,-123.123123
							$l= explode(',', $google_location);
							$latitude=$l[0];
							$longitude=$l[1];

							$addrNumber = trim(post('street_number'));  //30
							$addrStreet = trim(post('route'));          //jean st
							$addrPost = trim(post('postal_code'));      //3204
							$addrSuburb = trim(post('locality'));       //malvern east

							$state = trim(post('administrative_area_level_1'));   //vic
							$country_short = trim(post('country_short'));  //AU

							$country = trim(post('country'));   //Australia
							$googleMapUrl = trim(post('url'));
							
						   
							$data1 = array(
								'cityId'=>$data['city'],
								 'googleMap' => $googleMap,
								'google_location' => $google_location,
								'latitude'=>$latitude,
								'longitude'=>$longitude,

								'addrNumber' => $addrNumber,
								'addrStreet' => $addrStreet,
								'addrPost' => $addrPost,
								'addrSuburb' => $addrSuburb,

								'addrState' => $state,
								'countryCode' => $country_short,

								'country' => $country,
								'googleMapUrl' => $googleMapUrl,
							);
						  

							$mdl_user = $this->loadModel('user');
							if ($mdl_user->updateUserById($data1, $this->loginUser['id'])) {
							   // $this->form_response(200, '保存成功',"SELF");
							} else {
								//$this->form_response_msg("修改失败");
							}
									
					
					
                            $this->coupons_edit_toStep($coupon_id, 7);
                        } else {
                            $this->coupons_edit_failure('保存失败');
                        }

                   
                } else {
                     $mdl_infoclass = $this->loadModel('infoClass');
                    /**
                     * 可选产品类型
                     */
                    $mdl_coupon_type = $this->loadModel('coupon_type');

                    if ($coupon_type) {
                        $types = $mdl_coupon_type->getList(null, array('isApproved' => 1, 'id' => $coupon_type), 'sortnum asc');
                    } else {
                        $types = $mdl_coupon_type->getList(null, array('isApproved' => 1,"id in (9,7,4)" ), 'sortnum asc');
                    }
                   $mdl_user= $this->loadModel('user');
					$data=array(
						'city'=>$this->loginUser['cityId'],
					    'categoryName'=>$mdl_coupons->getCategoryName($this->loginUser['categoryId'],$mdl_infoclass),
						'BusinessName'=>$mdl_user->getBusinessDisplayName($this->loginUser['id']),
						'pic'=>$this->loginUser['pic'],
						'pics'=>$this->loginUser['pics'],
						
					);
					/* goolge map */
					if(!$this->loginUser['googleMap']) {
					
						$this->loginUser['googleMap']=	$this->loginUser['address']	;		
						
					} else{
						
						
					}
					
					 $this->setData($this->loginUser['googleMap'], 'googleMap');
					 $this->setData($this->loginUser['trading_hours'],'trading_hours');
					 $this->setData($data, 'data');
					
                    $this->setData($types, 'types');


                    /**
                     * 用户曾选的分类
                     */
                   

                    $catids = explode(',', $this->loginUser['categoryId']);
                   
                    $userCategory = array();
                    foreach ($catids as $catid) {
                        $cat = $mdl_infoclass->get($catid);

                        if(!$cat)continue;//filter out none exist cat, data still exist in db.

                        $cat_large = $mdl_infoclass->get(substr($catid, 0, 6));

                        $cat['name'] = $cat_large['name'] . '->' . $cat['name'];
                        $userCategory[] = $cat;
                    }
                    $this->setData($userCategory, 'userCategory');
                 
                    /**
                     * 全部可用分类
                     */
                    $categoriesAll = $mdl_infoclass->getListBySql("select id, name, (select count(*) from #@_infoclass where id like concat(c.id,'___')) as childCount from #@_infoclass as c where id like '106%' and lang ='".$this->getLangStr()."' order by ordinal asc");
                    $this->setData($categoriesAll, 'categoriesAll');


                    /**
                    * 分类树形结构数据
                    */
                    $categories=  $mdl_infoclass->getChild4( '106' );

                    if ($coupon_type == 4) {

                    }elseif (($coupon_type == 7)) {
                        unset($categories['106124']);
                        unset($categories['106120']);
                        unset($categories['106105']);
                    }elseif (($coupon_type == 9)) {
                        unset($categories['106121']);
                        unset($categories['106119']);
                    }elseif (($coupon_type == 18)) {
                        unset($categories['106124']);
                        unset($categories['106120']);
                        unset($categories['106105']);
                    }else{

                    }

                    $this->setData($categories, 'categories' );

					$this->setData('youhuiquan', 'submenu');
					$this->setData('index_publish', 'menu');

                    $this->display('company/youhuiquan_quick_add');
                }
          
       
    }

	
	
		function daijinquan_quick_add_action()
    {   
        /**
         * 加载模组
         */
        $mdl_coupons = $this->loadModel('coupons');
		
	    
        /**
         * 页面信息
         */
        $this->setData('快速添加代金券', 'pagename');
        $this->setData('快速添加代金券' . $this->site['pageTitle'], 'pageTitle');
      

        /**
         * 分步骤编辑
         */
       
                if (is_post()) {
                    /**
                     * 接收数据
                     */
                    $title = trim(post('title'));
                    $bonusType = (int)post('bonusType');
                    $userCategoryId = post('userCategoryId');

                    /**
                     * 验证
                     */
                    if (empty($title)) $this->coupons_edit_failure('请添加标题');
                    if (empty($userCategoryId)) $this->coupons_edit_failure('请将选择至少一个行业');

					
					$googleMap = trim(post('googleMap'));
					 if (empty($googleMap)) $this->coupons_edit_failure('请输入商家地址!');
					
					$voucher_deal_amount= post('voucher_deal_amount');
                    $voucher_original_amount	= post('voucher_original_amount');	
					$main_coupon_title =$title.'$'.$voucher_deal_amount.'抵$'.$voucher_original_amount;
					
					$voucher_original_amount_sub04 = (int)post('voucher_original_amount_sub04');
					$voucher_original_amount_sub03 = (int)post('voucher_original_amount_sub03');
					$voucher_original_amount_sub02 = (int)post('voucher_original_amount_sub02');
					$voucher_deal_amount_sub02=(int)post('voucher_deal_amount_sub02');
					$voucher_deal_amount_sub03=(int)post('voucher_deal_amount_sub03');
					$voucher_deal_amount_sub04=(int)post('voucher_deal_amount_sub04');
					
					if($voucher_deal_amount <=0) $this->coupons_edit_failure('代金券1购买金额要大于0!');
					
					if($voucher_deal_amount >=$voucher_original_amount) $this->coupons_edit_failure('代金券1面值要大于购买金额!');
					
					if($voucher_original_amount_sub04<=$voucher_deal_amount_sub04 && $voucher_original_amount_sub04>0) $this->coupons_edit_failure('代金券4购买金额要小于面值!');
					
					
					if($voucher_original_amount_sub03<=$voucher_deal_amount_sub03 && $voucher_original_amount_sub03>0) $this->coupons_edit_failure('代金券3购买金额要小于面值!');
					if($voucher_original_amount_sub02<=$voucher_deal_amount_sub02 && $voucher_original_amount_sub02>0) $this->coupons_edit_failure('代金券2购买金额要小于面值!');
				
					 $data['voucher_deal_amount'] = post('voucher_deal_amount');
                     $data['voucher_original_amount'] = post('voucher_original_amount');
					
					
					$businessName=$this->loadModel('user')->getBusinessDisplayName($this->loginUser['id']);
					
				     /**
                     * 预处理
                     */
                    $userCategoryId= ",".join(',',$userCategoryId).",";
					$mdl_infoclass=$this->loadModel('infoClass');
					$mdl_city=$this->loadModel('city');
					
                 
                        $data = array();
                        $data['title'] = $main_coupon_title;
                        $data['bonusType'] =18;
                        $data['categoryId'] = $userCategoryId;
						$data['categoryName'] = $mdl_coupons->getCategoryName($data['categoryId'],$mdl_infoclass);
						
					    //默认值
                     
                        $data['autoOffline'] = false;

                        $data['createUserId'] = $this->loginUser['id'];
                        $data['createTime'] = time();

                        $data['isApproved'] = ($this->loginUser['trustLevel'] >= 1)?1:0;
                        $data['status'] = 1;

                        $data['visibleForBusiness'] = $this->loginUser['visibleForBusiness'];
                        $data['city'] = $this->loginUser['cityId'];
						
						
                        $data['languageType'] = $this->loginUser['languageType'];
                        $data['businessName'] = $businessName;
						
						

                        $data['qty']=999999;
                        $data['perCustomerLimitQuantity']=0;
                        $data['perCustomerMinLimitQuantity']=0;

                        $data['deliverFeeCalculationType'] = 'per_coupon';
                        $data['platform_commission_base'] = $this->loginUser['platform_commission_base'];
                        $data['platform_commission_rate'] = 0.05;
                        $data['voucher_deal_amount'] = post('voucher_deal_amount');
                        $data['voucher_original_amount'] = post('voucher_original_amount');
                        $data['coupon_summery_description'] = '在网上花费$'.$voucher_deal_amount.'购买'.$businessName.'面值$'.$voucher_original_amount.'代金券,到店消费时,可以抵用$'.$voucher_original_amount.'元现金';											
                                        
						
                     /* 如果需要预约的相关信息 */
					 
					     $data2 =array();
					   
					   
						 if(trim(post('tel'))) {
							$data2['tel']=trim(post('tel'));
							if(!$this->loginUser['tel']){
							$this->loginUser['tel']=trim(post('tel'));
							}
							
						 }
					 
						 if(trim(post('phone'))) {
							 
							$data2['phone']=trim(post('phone'));
							if(!$this->loginUser['phone']){
							$this->loginUser['phone']=trim(post('phone'));
							}
							
						 }
					      if(trim(post('nickname'))) {
							 
							$data2['nickname']=trim(post('nickname'));
							if(!$this->loginUser['nickname']){
								$this->loginUser['nickname']=trim(post('nickname'));
							}
						
						 }
					     
					  
						
					 
						
						
					$country_code = (int)post('country_code');
                    $city = post('city');
                    $startTime=trim(post('startTime'));
                    $endTime=trim(post('endTime'));
                   
                    $searchKeywords=trim(post('searchKeywords'));
                  

                
                    $city =($city)?','.join(',', $city).',':' ';
 
                    $startTime =($startTime)?$startTime:date('Y-m-d');
                    $endTime = $endTime?$endTime:date('Y-m-d', strtotime('+10000 days'));
					
                    $startTime = strtotime($startTime);
                    $endTime = strtotime($endTime);

                  
  
                    $data['country_code'] = $country_code;
                    $data['city'] = $city;
					
					$data['cityName']= $mdl_city->getCityName($data['city']);
                    $data['startTime'] = $startTime;
                    $data['endTime'] = $endTime;
                   
                    $data['searchKeywords'] = $searchKeywords;
                  


                  
						
				  /* step3______________________________________________________________*/
						
						
					
						
						
					$pic=post('main_pic');
					$picpath='daijinquan/yellow.jpg';
					if($pic =='yellow') {
						
					 $picpath='daijinquan/yellow.jpg';
					}
					if($pic =='skyblue') {
						
						$picpath='daijinquan/skyblue.jpg';
					}
					if($pic =='red') {
						
						$picpath='daijinquan/red.jpg';
					}
					if($pic =='self') {
						
					$images=post('images');
                    if($images){
                        foreach ($images as $key => $value) {
                            if($value=="default/image_upload.jpg")
                                unset($images[$key]);
                            else
                                $images[$key]=trim($value);
                        }
                        $picpath=reset($images);

                        $data['pics'] = serialize(array_slice($images, 1));
                    }	
					}
					
                 
                    $data['pic']=$picpath;

				   // var_dump($data['pic']);exit;
						
						
			
                    $finePrint                  = post('finePrint');
					$refund_policy              = post('refund_policy');
                    $redeemProcedure            = post('redeemProcedure');

                  
             
                    $data['finePrint']                  = $finePrint;
                  
                    $data['redeemProcedure']            = $redeemProcedure;
                    $data['refund_policy']              = $refund_policy;

                    if(!$this->loginUser['refund_policy'])$this->loadModel('user')->updateUserById(array('refund_policy'=>$refund_policy), $this->loginUser['id']);

						
                        if ($coupon_id=$mdl_coupons->insert($data)) {
							
							/* 插入子券处理 */
					
                                      /**
									* 处理 coupons_addon_数据
                     */
								$this->loadModel('coupons_addon')->set($coupon_id,$_POST);
								//更新子卡
                                    $mdl_coupons_sub = $this->loadModel('coupons_sub');
									$sc_data=array();
									 
									// $coupon_id
									$voucher_deal_amount_sub=$voucher_deal_amount_sub02;
									$voucher_original_amount_sub=$voucher_original_amount_sub02;
									
                                    if($voucher_deal_amount_sub>=0 && $voucher_original_amount_sub>0){
										
										$sc_data['title']=$title.'$'.$voucher_deal_amount_sub.'抵$'.$voucher_original_amount_sub;
										$sc_data['customer_amount'] = (float)$voucher_deal_amount_sub;
										$sc_data['original_amount'] = (float)$voucher_original_amount_sub;
										$sc_data['quantity'] =  9999;
                                        $sc_data['parent_coupon_id'] = $coupon_id;
                                        $sc_data['create_user_id'] = $this->loginUser['id'];
                                        $sc_data['create_time'] = time();
										$sc_data['product_description'] = '在网上花费$'.$voucher_deal_amount_sub.'购买'.$businessName.'面值$'.$voucher_original_amount_sub.'代金券,到店消费时,可以抵用$'.$voucher_original_amount_sub.'元现金';											
                                        $mdl_coupons_sub->insert($sc_data);
                     				}
					
					                $voucher_deal_amount_sub=$voucher_deal_amount_sub03;
									$voucher_original_amount_sub=$voucher_original_amount_sub03;
									
                                    if($voucher_deal_amount_sub>=0 && $voucher_original_amount_sub>0){
										
										$sc_data['title']=$title.'$'.$voucher_deal_amount_sub.'抵$'.$voucher_original_amount_sub;
										$sc_data['customer_amount'] = (float)$voucher_deal_amount_sub;
										$sc_data['original_amount'] = (float)$voucher_original_amount_sub;
										$sc_data['quantity'] =  9999;
                                        $sc_data['parent_coupon_id'] = $coupon_id;
                                        $sc_data['create_user_id'] = $this->loginUser['id'];
                                        $sc_data['create_time'] = time();
										$sc_data['product_description'] = '在网上花费$'.$voucher_deal_amount_sub.'购买'.$businessName.'面值$'.$voucher_original_amount_sub.'代金券,到店消费时,可以抵用$'.$voucher_original_amount_sub.'元现金';											
                                        $mdl_coupons_sub->insert($sc_data);
                     				}
									
									$voucher_deal_amount_sub=$voucher_deal_amount_sub04;
									$voucher_original_amount_sub=$voucher_original_amount_sub04;
									
                                    if($voucher_deal_amount_sub>=0 && $voucher_original_amount_sub>0){
										
										$sc_data['title']=$title.'$'.$voucher_deal_amount_sub.'抵$'.$voucher_original_amount_sub;
										$sc_data['customer_amount'] = (float)$voucher_deal_amount_sub;
										$sc_data['original_amount'] = (float)$voucher_original_amount_sub;
										$sc_data['quantity'] =  9999;
                                        $sc_data['parent_coupon_id'] = $coupon_id;
                                        $sc_data['create_user_id'] = $this->loginUser['id'];
                                        $sc_data['create_time'] = time();
										$sc_data['product_description'] = '在网上花费$'.$voucher_deal_amount_sub.'购买'.$businessName.'面值$'.$voucher_original_amount_sub.'代金券,到店消费时,可以抵用$'.$voucher_original_amount_sub.'元现金';											
                                        $mdl_coupons_sub->insert($sc_data);
                     				}
									
					
					
						
							$googleMap = trim(post('googleMap'));
							$google_location = trim(post('location')); //12.123123,-123.123123
							$l= explode(',', $google_location);
							$latitude=$l[0];
							$longitude=$l[1];

							$addrNumber = trim(post('street_number'));  //30
							$addrStreet = trim(post('route'));          //jean st
							$addrPost = trim(post('postal_code'));      //3204
							$addrSuburb = trim(post('locality'));       //malvern east

							$state = trim(post('administrative_area_level_1'));   //vic
							$country_short = trim(post('country_short'));  //AU

							$country = trim(post('country'));   //Australia
							$googleMapUrl = trim(post('url'));
							
						   
							$data1 =array(
								'cityId'=>$data['city'],
								 'googleMap' => $googleMap,
								'google_location' => $google_location,
								'latitude'=>$latitude,
								'longitude'=>$longitude,

								'addrNumber' => $addrNumber,
								'addrStreet' => $addrStreet,
								'addrPost' => $addrPost,
								'addrSuburb' => $addrSuburb,

								'addrState' => $state,
								'countryCode' => $country_short,

								'country' => $country,
								'googleMapUrl' => $googleMapUrl,
							);
						  
                           $data1 =array_merge($data1,$data2);
							$mdl_user = $this->loadModel('user');
							if ($mdl_user->updateUserById($data1, $this->loginUser['id'])) {
							   // $this->form_response(200, '保存成功',"SELF");
							} else {
								//$this->form_response_msg("修改失败");
							}
									
					
					
                            $this->coupons_edit_toStep($coupon_id, 7);
                        } else {
                            $this->coupons_edit_failure('保存失败');
                        }

                   
                } else {
                     $mdl_infoclass = $this->loadModel('infoClass');
                    /**
                     * 可选产品类型
                     */
                    $mdl_coupon_type = $this->loadModel('coupon_type');

                    if ($coupon_type) {
                        $types = $mdl_coupon_type->getList(null, array('isApproved' => 1, 'id' => $coupon_type), 'sortnum asc');
                    } else {
                        $types = $mdl_coupon_type->getList(null, array('isApproved' => 1,"id in (9,7,4)" ), 'sortnum asc');
                    }
                   $mdl_user= $this->loadModel('user');
				   if (!trim($this->loginUser['cityId'])) {
					   
					   $this->loginUser['cityId']=',526,556,';
				   }
					$data=array(
						'city'=>$this->loginUser['cityId'],
					    'categoryName'=>$mdl_coupons->getCategoryName($this->loginUser['categoryId'],$mdl_infoclass),
						'BusinessName'=>$mdl_user->getBusinessDisplayName($this->loginUser['id']),
						'pic'=>$this->loginUser['pic'],
						'pics'=>$this->loginUser['pics'],
						
					);
					
					//var_dump($data);exit;
					
					/* goolge map */
					if(!$this->loginUser['googleMap']) {
					
						$this->loginUser['googleMap']=	$this->loginUser['address']	;		
						
					} else{
						
						
					}
					
					 $this->setData($this->loginUser['googleMap'], 'googleMap');
					 $this->setData($this->loginUser['trading_hours'],'trading_hours');
					 $this->setData($data, 'data');
					
                    $this->setData($types, 'types');


                    /**
                     * 用户曾选的分类
                     */
                   

                    $catids = explode(',', $this->loginUser['categoryId']);
                   
                    $userCategory = array();
                    foreach ($catids as $catid) {
                        $cat = $mdl_infoclass->get($catid);

                        if(!$cat)continue;//filter out none exist cat, data still exist in db.

                        $cat_large = $mdl_infoclass->get(substr($catid, 0, 6));

                        $cat['name'] = $cat_large['name'] . '->' . $cat['name'];
                        $userCategory[] = $cat;
                    }
                    $this->setData($userCategory, 'userCategory');
                 
                    /**
                     * 全部可用分类
                     */
                    $categoriesAll = $mdl_infoclass->getListBySql("select id, name, (select count(*) from #@_infoclass where id like concat(c.id,'___')) as childCount from #@_infoclass as c where id like '106%' and lang ='".$this->getLangStr()."' order by ordinal asc");
                    $this->setData($categoriesAll, 'categoriesAll');


                    /**
                    * 分类树形结构数据
                    */
                    $categories=  $mdl_infoclass->getChild4( '106' );

                    if ($coupon_type == 4) {

                    }elseif (($coupon_type == 7)) {
                        unset($categories['106124']);
                        unset($categories['106120']);
                        unset($categories['106105']);
                    }elseif (($coupon_type == 9)) {
                        unset($categories['106121']);
                        unset($categories['106119']);
                    }elseif (($coupon_type == 18)) {
                        unset($categories['106124']);
                        unset($categories['106120']);
                        unset($categories['106105']);
                    }else{

                    }

                    $this->setData($categories, 'categories' );
					$this->setData('daijinquan', 'submenu');
					$this->setData('index_publish', 'menu');

                    $this->display('company/daijinquan_quick_add');
                }
          
       
    }

	
	
	
	function article_add_action()
    {   
        /**
         * 加载模组
         */
        $mdl_article = $this->loadModel('article');
		  $mdl_user = $this->loadModel('user');
	    	$mdl_city=$this->loadModel('city');
					
        /**
         * 页面信息
         */
        $this->setData('添加文章', 'pagename');
        $this->setData('添加文章' . $this->site['pageTitle'], 'pageTitle');
      
       $id=(int)post('id');
        /**
         * 分步骤编辑
         */
       
                if (is_post()) {
					
                    /**
                     * 接收数据
                     */
					 
					
                    $title = trim(post('title'));
					$keyword1 = trim(post('keyword1'));
					$keyword2 = trim(post('keyword2'));
					$Description = trim(post('Description'));
					$category_id = trim(post('category_id'));
					$recommend = (int)post('recommend1');
					$restaurant_recommend = (int)post('restaurant_recommend');
					
					//var_dump($restaurant_recommend);exit;			 
                  			
					$country_code = (int)post('country_code');
					
					$set_cityRange = (int)post('set_cityRange');
					
					if($set_cityRange) {
						
						  $city = post('city');
					}else{
						  $city ='';
						
					}
                   
					$content  = post('content');
					$editor  = post('editor');
					$business_id = (int)post('business_id');
					$product_id = (int)post('product_id');
					
					
					
                
                

                    /**
                     * 验证
                     */
                    if (empty($title)) $this->coupons_edit_failure('请添加标题');
                  
	
					$businessName=$this->loadModel('user')->getBusinessDisplayName($this->loginUser['id']);
					
				   
				      $mdl_coupons = $this->loadModel('coupons');
   
   
   
                       $set_catRange =(int)post('set_catRange');
					   
					   if($set_catRange) {
						 $userCategoryId = post('userCategoryId');
						 $userCategoryId= ",".join(',',$userCategoryId).",";
						   
					   }else{
						    $userCategoryId ='';
						   
					   }
   
								
						 
						 $data = array();
					   $data['select_cat_id'] = $userCategoryId;
					   $data['cat_name'] = $mdl_coupons->getCategoryName($data['categoryId'],$mdl_infoclass);
									   
				   
				
                 
                     
                                             
						$data['title'] = $title;                
                        $data['keyword1'] = $keyword1;
						$data['keyword2'] = $keyword2;
						$data['Description'] = $Description;
						$data['createUserId'] = $this->loginUser['id'];					 
                        $data['category_id'] = $category_id;
					    if($business_id==201964) $business_id=0;
						$data['business_id'] = $business_id;
						$data['product_id'] = $product_id;
						//  var_dump($data['product_id']);exit;
						$data['recommend'] = $recommend;
						$data['restaurant_recommend'] = $restaurant_recommend;
						
				 
                  	     $data['createUserId'] = $this->loginUser['id'];
                        $data['createTime'] = time();
                        $data['is_approved'] = ($this->loginUser['trustLevel'] >= 1)?1:0;
                       
                        $data['businessName'] = $businessName;
						$data['content']     = $content;
						$data['editor']     = $editor;
						
                     /* 如果需要预约的相关信息 */
					 
					 
						$city =($city)?','.join(',', $city).',':' ';
						$data['city'] = $city;
						
						$data['cityName']= $mdl_city->getCityName($data['city']);
					   
				
						//var_dump($data['business_id']);exit;
						
					
						
						
					$pic=post('main_pic');
				
					$images=post('images');
                    if($images){
                        foreach ($images as $key => $value) {
                            if($value=="default/image_upload.jpg")
                                unset($images[$key]);
                            else
                                $images[$key]=trim($value);
                        }
                        $data['pic']=reset($images);

                        $data['pics'] = serialize(array_slice($images, 1));
                    }
					 
				   // var_dump($data['pic']);exit;
						
						
			
                 //   var_dump ($data);exit;

                if ($id) {
					   $mdl_article=$this->loadModel('article');
					   $article= $mdl_article->get($id);
					    if($article['createUserId']==$this->loginUser['id']) {
							  if ($mdl_article->update($data,$id)) {
							$redirect = HTTP_ROOT_WWW . 'company/article';
							$this->form_response(200,'修改成功',$redirect);
						
                        } else {
							 $this->setData($data, 'data');
							 $this->display('company/article_add');
                            $this->coupons_edit_failure('意外原因-保存失败!');
                        }
							
						}else{
							
							$this->coupons_edit_failure('保存失败!');
							
						}
					
					
					
				}else{
					if ($mdl_article->insert($data)) {
							 $redirect = HTTP_ROOT_WWW . 'company/article';
							$this->form_response(200,'保存成功',$redirect);
						
                        } else {
							 $this->setData($data, 'data');
							 $this->display('company/article_add');
                           // $this->coupons_edit_failure('保存失败!');
                        }
				}
						
                      

                   
                } else {
                   
						   $id =(int)get2('id');
						   $this->setData($id,'id');
						      $mdl_infoclass = $this->loadModel('infoClass');
						      
						   $mdl_article_type = $this->loadModel('article_type');
						   
						   $sql ="select * from cc_article_type";
						   
						   $article_type = $mdl_article_type->getListBySql($sql);
						 //  var_dump($article_type);exit;
						   
						   $this->setData($article_type, 'article_type');
						   $data=array();
						  if(!$this->loginUser['cityId']) {
							  
							  $this->loginUser['cityId']=',526,556,';
							  $data['city']=',526,556,';
						  }else {
							  
							  $data['city'] =$this->loginUser['cityId'];
						  }
						   
						   $this->setData($data,'data');
						   /**
								 * 全部可用分类
								 */
								$categoriesAll = $mdl_infoclass->getListBySql("select id, name, (select count(*) from #@_infoclass where id like concat(c.id,'___')) as childCount from #@_infoclass as c where id like '106%' and lang ='".$this->getLangStr()."' order by ordinal asc");
								$this->setData($categoriesAll, 'categoriesAll');


								/**
								* 分类树形结构数据
								*/
								$categories=  $mdl_infoclass->getChild4( '106' );

								if ($coupon_type == 4) {

								}elseif (($coupon_type == 7)) {
									unset($categories['106124']);
									unset($categories['106120']);
									unset($categories['106105']);
								}elseif (($coupon_type == 9)) {
									unset($categories['106121']);
									unset($categories['106119']);
								}elseif (($coupon_type == 18)) {
									unset($categories['106124']);
									unset($categories['106120']);
									unset($categories['106105']);
								}else{

								}

								$this->setData($categories, 'categories' );
								
						   
						   
						   
						   if ($id){
							   $mdl_article=$this->loadModel('article');
							   $data= $mdl_article->get($id);
						
						   
						
							
							if($data['createUserId']==$this->loginUser['id']) {
								
										  /**
								 * 用户曾选的分类
								 */
							 

								$catids = explode(',', $data['select_cat_id']);
							   
								$userCategory = array();
								foreach ($catids as $catid) {
									$cat = $mdl_infoclass->get($catid);

									if(!$cat)continue;//filter out none exist cat, data still exist in db.

									$cat_large = $mdl_infoclass->get(substr($catid, 0, 6));

									$cat['name'] = $cat_large['name'] . '->' . $cat['name'];
									$userCategory[] = $cat;
								}
								$this->setData($userCategory, 'userCategory');
							 
								
								
								
								
								
						
								
							 $this->setData($data, 'data');
							
							$this->setData('article', 'submenu');
							$this->setData('index_publish', 'menu');

							$this->display('company/article_add');
								
							}else {
								 $this->coupons_edit_failure('fail to open 打开编辑失败!');
								
							}
							
						}else{
							
							$this->setData('article', 'submenu');
						$this->setData('index_publish', 'menu');

						$this->display('company/article_add');
						}
					
					
					
					
                }
          
       
    }

	
	  function article_action()
    {
        $id = (int)get2('id');
      
        $article_type = (int)get2('article_type');
		$this->setData($article_type,'$article_type');
        $keyword = get2('sk');

        $mdl_article = $this->loadModel('article');
        $mdl_article_type = $this->loadModel('article_type');
        $article_type_list =  $mdl_article_type->getListBySql("select * from cc_article_type ");
		$this->setData($article_type_list,'article_type_list');
		

        if ($id > 0) {
            $article = $mdl_article->get($id);
            if (!$article || $article['createUserId'] != $this->loginUser['id']) $this->sheader(null, '产品不存在');
          

            $mdl_article->delete($id);
         
            $this->sheader($this->parseUrl()->set('id'));
        }

        $where=" where createUserId='".$this->loginUser['id']."'";
		if($article_type) {
			 $where .=" and category_id='".$article_type."'";
			
		}
   
    
        if ($keyword) $where = $where." and (title like '%$keyword%' or keyword1 like '%$keyword%' or keyword2 like '%$keyword%')";

       
        $pageSql = "select  * from cc_article ".$where." order by createTime desc";
        $pageUrl = $this->parseUrl()->set('page');
        $pageSize = 20;
        $maxPage = 10;
        $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
        $data = $mdl_article->getListBySql($page['outSql']);

        foreach ($data as $key => $val) {
            $data[$key]['cat_type'] = $mdl_article_type->get($val['category_id']);
        }

    
        $this->setData($data, 'data');
        $this->setData($page['pageStr'], 'pager');
		
		//var_dump($pageSql);exit;
		
		
        $this->setData($this->parseUrl()->setPath('/company/article_add'), 'editUrl');


        // 这个是 存储当前卡券类型
        $this->setData($article_type, 'article_type');

        $this->setData($keyword, 'sk');

        $this->setData('article', 'submenu');
        $this->setData('article_vote', 'menu');
        $this->setData($pagename, 'pagename');

        $this->setData($pageTitle, 'pageTitle');
		
		$this->display_pc_mobile('company/article', 'company/article');
       
       
    }

	
	
	
	
	
	
	
	
	 function youhuiquan_edit_action()
    {   
        /**
         * 加载模组
         */
        $mdl_coupons = $this->loadModel('coupons');
		
		
		//获取是否为线上餐厅
        $restaurant =get2('restaurant');
		if($restaurant) {
			$where00 =array(
		   'createUserId' => $this->loginUser['id'],
		   'EvoucherOrrealproduct' =>'restaurant_menu'
		   );
		   $restaurant_coupon= $mdl_coupons->getByWhere($where00);
		   if($restaurant_coupon) {
			   $id=$restaurant_coupon['id'];
		   }
		}else{
			  $id = (int)get2('id');
		}

        /**
         * 当前编辑第几步
         */
        $step = (int)get2('step');

        if ($step <= 0) $step = 1;
        if ($step > 7) $step = 7;

        $this->setData($step, 'step');
        $this->setData($this->parseUrl()->set('step'), 'stepUrl');

        /**
         * 产品ID
         */
      

        if ($id) {
            //编辑产品
            $coupon = $mdl_coupons->get($id);

            if (!$coupon || $coupon['createUserId'] != $this->loginUser['id']) $this->sheader(null, '产品不存在');

            $this->setData($coupon, 'data');

        }else{
            
        }

        /**
         * 产品类型
         */
        $coupon_type = ($id)?$coupon['bonusType']:(int)get2('coupon_type');

        $this->setData($coupon_type, 'coupon_type');

        if ($coupon_type == 4) {
            $this->setData('voucher', 'submenu');
            $this->setData('index_publish', 'menu');
        }elseif (($coupon_type == 7)) {
            $this->setData('local', 'submenu');
            $this->setData('index_publish', 'menu');
        } elseif (($coupon_type == 9)) {
            $this->setData('shop', 'submenu');
            $this->setData('index_publish', 'menu');
        } else{
            $this->setData('all', 'submenu');
            $this->setData('index_publish', 'menu');
        }

        if($restaurant){
            $this->setData('restaurant_set', 'submenu');
            $this->setData('restaurant', 'menu');
        }

        /**
         * 页面信息
         */
        $this->setData($this->parseUrl(), 'postUrl');
        $this->setData($this->parseUrl()->set('step',$step-1), 'prevUrl');

        $this->setData(($coupon ? '编辑' : '添加') . '产品', 'pagename');
        $this->setData(($coupon ? '编辑' : '添加') . '产品 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
       $mdl_city=$this->loadModel('city');

        /**
         * 分步骤编辑
         */
        switch ($step) {
            case 1:
                if (is_post()) {
                    /**
                     * 接收数据
                     */
                    $title = trim(post('title'));
                    $bonusType = (int)post('bonusType');
                    $userCategoryId = post('userCategoryId');
					$coupon_summery_description=post('coupon_summery_description');
                    /**
                     * 验证
                     */
                    if (empty($title)) $this->coupons_edit_failure('请添加标题');
                    if (empty($bonusType)) $this->coupons_edit_failure('请选择产品类型');
                    if (empty($userCategoryId)) $this->coupons_edit_failure('请将选择至少一个行业');

                    /**
                     * 预处理
                     */
                    $userCategoryId= ",".join(',',$userCategoryId).",";
					$mdl_infoclass=$this->loadModel('infoClass');
					
                    if (!$coupon) {
                        $data = array();
                        $data['title'] = $title;
                        $data['bonusType'] = $bonusType;
                        $data['categoryId'] = $userCategoryId;
						$data['categoryName'] = $mdl_coupons->getCategoryName($data['categoryId'],$mdl_infoclass);
						$data['coupon_summery_description']=$coupon_summery_description;
						
						
                        //默认值
                        $data['startTime'] = time();
                        $data['endTime'] = strtotime('+ 90 days', time()) - 1;
                        $data['autoOffline'] = false;

                        $data['createUserId'] = $this->loginUser['id'];
                        $data['createTime'] = time();

                        $data['isApproved'] = ($this->loginUser['trustLevel'] >= 1)?1:0;
                        $data['status'] = 1;

                        $data['visibleForBusiness'] = $this->loginUser['visibleForBusiness'];
                        $data['city'] = $this->loginUser['cityId'];
						
						
						$data['cityName']= $mdl_city->getCityName($data['city']);
						//var_dump($data['city'].$data['cityName']);exit;
						
                        $data['languageType'] = $this->loginUser['languageType'];
                        $data['businessName'] = $this->loadModel('user')->getBusinessDisplayName($this->loginUser['id']);
	
                        $data['qty']=999999;
                        $data['perCustomerLimitQuantity']=1;
                        $data['perCustomerMinLimitQuantity']=1;
						$data['voucher_deal_amount']=2.00;
						$data['voucher_deal_amount']=2.00;
						$data['redeemProcedure']='<p><span style="font-size:14px">1) 请出示订单信息给商家服务人员. (订单信息在邮件中(请检查Junk Mail ) 或 您在Ubonus平台的账户中,请访问 <a href="https://www.cityb2b.com/member/exchange">我的订单</a> 或 (https://www.cityb2b.com/member/exchange)</span></p>

<p><span style="font-size:14px">2)商家服务员检查优惠券是否可以使用,如可以使用,核兑后即可使用.</span></p>';
						
						$data['finePrint']='<p>优惠券仅能使用一次</p><p>优惠券购买后请在7天之内使用(或查看具体商家说明),过期作废.</p><p>&nbsp;</p>

<p>&nbsp;</p>';

                        $data['deliverFeeCalculationType'] = 'per_coupon';
                        $data['platform_commission_base'] = $this->loginUser['platform_commission_base'];;
                        $data['platform_commission_rate'] = $this->loginUser['platform_commission_rate'];;
                        

                        if ($coupon_id=$mdl_coupons->insert($data)) {
                            $this->youhuiquan_edit_toStep($coupon_id, 2);
                        } else {
                            $this->coupons_edit_failure('保存失败');
                        }

                    } else {

                        $data = array();
                        $data['title'] = $title;
                        $data['bonusType'] = $bonusType;
                        $data['categoryId'] = $userCategoryId;
                        $data['categoryName'] = $mdl_coupons->getCategoryName($data['categoryId'],$mdl_infoclass);
						
						
                        if ($this->loginUser['needReapprovedAfterEdit']) {
                            //$data['status'] = 1;
                        }

                        if ($mdl_coupons->update($data, $coupon['id'])) {
                            $this->youhuiquan_edit_toStep($coupon['id'], 2);
                        } else {
                            $this->coupons_edit_failure('保存失败');
                        }
                    }
                } else {
                    
                    /**
                     * 可选产品类型
                     */
                    $mdl_coupon_type = $this->loadModel('coupon_type');

                    if ($coupon_type) {
                        $types = $mdl_coupon_type->getList(null, array('isApproved' => 1, 'id' => $coupon_type), 'sortnum asc');
                    } else {
                        $types = $mdl_coupon_type->getList(null, array('isApproved' => 1,"id in (9,7,4)" ), 'sortnum asc');
                    }

                    $this->setData($types, 'types');


                    /**
                     * 用户曾选的分类
                     */
                    $mdl_infoclass = $this->loadModel('infoClass');

                    $catids = explode(',', $this->loginUser['categoryId']);
                   
                    $userCategory = array();
                    foreach ($catids as $catid) {
                        $cat = $mdl_infoclass->get($catid);

                        if(!$cat)continue;//filter out none exist cat, data still exist in db.

                        $cat_large = $mdl_infoclass->get(substr($catid, 0, 6));

                        $cat['name'] = $cat_large['name'] . '->' . $cat['name'];
                        $userCategory[] = $cat;
                    }
                    $this->setData($userCategory, 'userCategory');
                 
                    /**
                     * 全部可用分类
                     */
                    $categoriesAll = $mdl_infoclass->getListBySql("select id, name, (select count(*) from #@_infoclass where id like concat(c.id,'___')) as childCount from #@_infoclass as c where id like '106%' and lang ='".$this->getLangStr()."' order by ordinal asc");
                    $this->setData($categoriesAll, 'categoriesAll');


                    /**
                    * 分类树形结构数据
                    */
                    $mdl_infoclass=$this->loadModel( 'infoClass' );
                    $categories=  $mdl_infoclass->getChild4( '106' );

                    if ($coupon_type == 4) {

                    }elseif (($coupon_type == 7)) {
                        unset($categories['106124']);
                        unset($categories['106120']);
                        unset($categories['106105']);
                    }elseif (($coupon_type == 9)) {
                        unset($categories['106121']);
                        unset($categories['106119']);
                    }else{

                    }

                    $this->setData($categories, 'categories' );


                    $this->display('company/youhuiquan_edit');
                }
                break;
            case 2:
                if (is_post()) {
                    /**
                     * 数据
                     */
                    $country_code = (int)post('country_code');
                    $city = post('city');
                    $startTime=trim(post('startTime'));
                    $endTime=trim(post('endTime'));
                    $cCategoryId_lock=post('cCategoryId_lock');
					$finePrint=post('finePrint');
					
					
                    $data['autoOffline'] = $autoOffline;
				  

                    $searchKeywords=trim(post('searchKeywords'));
                    $autoOffline=(post('autoOffline'))?1:0;

                    /**
                     * 预处理
                     */
                    $city =($city)?','.join(',', $city).',':' ';

                    $startTime =($startTime)?$startTime:date('Y-m-d');
                    $endTime = $endTime?$endTime:date('Y-m-d', strtotime('+90 days'));
                    $startTime = strtotime($startTime);
                    $endTime = strtotime($endTime);

                    $cCategoryId_lock=($cCategoryId_lock)?1:0;
                    $cCategoryId=",".join(',',post('cCategoryId')).",";


                    /**
                     * 准备更新数据
                     */
                    $data=array();
                    $data['country_code'] = $country_code;
                    $data['city'] = $city;
					$data['cityName']= $mdl_city->getCityName($data['city']);
                    $data['startTime'] = $startTime;
                    $data['endTime'] = $endTime;
                    $data['cCategoryId_lock'] = $cCategoryId_lock;
                    $data['cCategoryId'] = $cCategoryId;
                    $data['searchKeywords'] = $searchKeywords;
                    $data['autoOffline'] = $autoOffline;
					$data['finePrint'] = $finePrint;


                    /**
                     * 处理 coupons_addon_数据
                     */
                    $this->loadModel('coupons_addon')->set($coupon['id'],$_POST);

                    if ($mdl_coupons->update($data, $coupon['id'])) {
                        $this->youhuiquan_edit_toStep($coupon['id'], 3);
                    } else {
                        $this->coupons_edit_failure('保存失败,请稍后再试');
                    }

                } else {
                     /**
                     * 自定义分类
                     */

                    $mdl = $this->loadModel('customizableCategory');
                    $mdl->setUserId($this->loginUser['id']);
                    $list = $mdl->getTopLevelItemList();
                    foreach ($list as $key => $value) {
                        $list[$key]['hasChild'] = $mdl->hasChild($value['id']);
                    }
                    $this->setData($list, 'tr_list');
                    $table_tr = $this->fetch('customizable_category/table_tr_show');
                    $this->setData($table_tr, 'table_tr');

                    /**
                     * addon data
                     */
                    $addonData = $this->loadModel('coupons_addon')->getAddonData($id);
                    $this->setData($addonData, 'addonData');

                    $this->display('company/youhuiquan_edit2');
                }
                break;
            case 3:
                if (is_post()) {

                    $images=post('images');
                    if($images){
                        foreach ($images as $key => $value) {
                            if($value=="default/image_upload.jpg")
                                unset($images[$key]);
                            else
                                $images[$key]=trim($value);
                        }
                        $data['pic']=reset($images);

                        $data['pics'] = serialize(array_slice($images, 1));
                    }else{
                       $this->youhuiquan_edit_toStep($coupon['id'], 4); //no data no update. slow internet error
                    }

                    if ($mdl_coupons->update($data, $coupon['id'])) {
                        $this->youhuiquan_edit_toStep($coupon['id'], 4);
                    } else {
                        $this->coupons_edit_failure('保存失败');
                    }

                } else {

                    $this->display('company/youhuiquan_edit3');

                }
                break;
            case 4:
                $mdl_coupons_sub = $this->loadModel('coupons_sub');
               
                if (is_post()) {
                    $data['voucher_deal_amount'] = post('voucher_deal_amount');
                    $data['voucher_original_amount'] = post('voucher_original_amount');
                    $coupon_summery_description = post('coupon_summery_description');
                    $refund_policy              = post('refund_policy');
                    $redeemProcedure            = post('redeemProcedure');

                    $data['coupon_summery_description'] = $coupon_summery_description;  
                
                    $data['redeemProcedure']            = $redeemProcedure;
                    $data['refund_policy']              = $refund_policy;

					
					
					
					
					
					
					
					
					
					
                    $qty = (int)post('qty');
                    if ($qty < 0) $qty = 0;
                    if ($qty > 999999) $qty = 999999;

                    $perCustomerLimitQuantity = (int)post('perCustomerLimitQuantity');
                    if ($perCustomerLimitQuantity < 0) $perCustomerLimitQuantity = 0;
                    if ($perCustomerLimitQuantity > 999999) $perCustomerLimitQuantity = 999999;

                    $perCustomerMinLimitQuantity = (int)post('perCustomerMinLimitQuantity');
                    if ($perCustomerMinLimitQuantity < 0) $perCustomerMinLimitQuantity = 0;
                    if ($perCustomerMinLimitQuantity > 999999) $perCustomerMinLimitQuantity = 999999;

                    $data['qty'] = $qty;
                    $data['perCustomerLimitQuantity'] = $perCustomerLimitQuantity;
                    $data['perCustomerMinLimitQuantity'] = $perCustomerMinLimitQuantity;

                    //更新价格
                    $mdl_coupons->update($data, $coupon['id']);


                    switch ($coupon['bonusType']) {
                        case 1:
                            break;
                        case 4:
                        case 7:
                            //子卡
                            $isSubCoupons = (int)post('isSubCoupons');  //是否勾选了创建子卡复选框

                            $mdl_coupons->begin();

                            if ($isSubCoupons) {

                                $sub_id = post('sub_id');
                                
                                $sub_title = post('sub_title');
                                $sub_description = post('product_description');
                                $sub_customer_amount = post('sub_customer_amount');
                                $sub_original_amount = post('sub_original_amount');
                                $sub_quantity = post('sub_quantity');
                               
                                //更新子卡
                                foreach ($sub_id as $k => $sid) {
                                    
                                    $sid = (int)$sid;

                                    $sc_data['title'] = trim($sub_title[$k]);
                                    $sc_data['product_description'] = trim($sub_description[$k]);
                                    $sc_data['customer_amount'] = (float)$sub_customer_amount[$k];
                                    $sc_data['original_amount'] = (float)$sub_original_amount[$k];
                                    $sc_data['quantity'] =  $sub_quantity[$k];
                                   
                                    if($sid==0){
                                        
                                        $sc_data['parent_coupon_id'] = $coupon['id'];
                                        $sc_data['create_user_id'] = $this->loginUser['id'];
                                        $sc_data['create_time'] = time();

                                        $mdl_coupons_sub->insert($sc_data);
                                    }else{
                                        //如果在原列表中没有 删除
                                        $mdl_coupons_sub->updateByWhere($sc_data, array('id' => $sid, 'parent_coupon_id' => $coupon['id']));
                                    }
                                   
                                }

                            } else {
                                //全部删除
                                 $mdl_coupons_sub->deleteByWhere( array('parent_coupon_id' => $coupon['id']));
                            }

                            break;
						 case 18:
                            //子卡
                            $isSubCoupons = (int)post('isSubCoupons');  //是否勾选了创建子卡复选框

                            $mdl_coupons->begin();

                            if ($isSubCoupons) {

                                $sub_id = post('sub_id');
                                
                                $sub_title = post('sub_title');
                                $sub_description = post('product_description');
                                $sub_customer_amount = post('sub_customer_amount');
                                $sub_original_amount = post('sub_original_amount');
                                $sub_quantity = post('sub_quantity');
                               
                                //更新子卡
                                foreach ($sub_id as $k => $sid) {
                                    
                                    $sid = (int)$sid;

                                    $sc_data['title'] = trim($sub_title[$k]);
                                    $sc_data['product_description'] = trim($sub_description[$k]);
                                    $sc_data['customer_amount'] = (float)$sub_customer_amount[$k];
                                    $sc_data['original_amount'] = (float)$sub_original_amount[$k];
                                    $sc_data['quantity'] =  $sub_quantity[$k];
                                   
                                    if($sid==0){
                                        
                                        $sc_data['parent_coupon_id'] = $coupon['id'];
                                        $sc_data['create_user_id'] = $this->loginUser['id'];
                                        $sc_data['create_time'] = time();

                                        $mdl_coupons_sub->insert($sc_data);
                                    }else{
                                        //如果在原列表中没有 删除
                                        $mdl_coupons_sub->updateByWhere($sc_data, array('id' => $sid, 'parent_coupon_id' => $coupon['id']));
                                    }
                                   
                                }

                            } else {
                                //全部删除
                                 $mdl_coupons_sub->deleteByWhere( array('parent_coupon_id' => $coupon['id']));
                            }

                            break;
                        case 9:

                            $data=array();
                            $stripCode = post('stirpcode');
                            $useguige = post('useguige');

                            if($coupon['useguige']!=$useguige||$coupon['stripCode']!=$stripCode){
                                $this->loadModel('guige_link')->deleteGuigeLinkByCouponId($coupon['id']);
                            }

                            $data['useguige'] = $useguige;
                            $data['stripCode'] = $stripCode;

                            //更新规格
                            $mdl_coupons->update($data, $coupon['id']);


                            //更新批发价
                            $useWholesale=post('useWholesale');
                            $wholesaleAmount=post('wholesaleAmount');
                            $wholesalePrice=post('wholesalePrice');

                            $mdl_wholesale = $this->loadModel('wholesale');

                            if($useWholesale){
                                $mdl_wholesale->saveWholesale($id,$this->loginUser['id'], $wholesaleAmount,$wholesalePrice);
                            }else{
                                $mdl_wholesale->delete($id);
                            }

                            break;
                        case 10:
                            break;
                        default:
                            $this->sheader(null,'无此类型');
                            break;
                    }
                   

                    if ($mdl_coupons->errno()) {
                        $mdl_coupons->rollback();
                        $this->coupons_edit_failure('保存失败');
                    } else {
                        $mdl_coupons->commit();
                        $this->youhuiquan_edit_toStep($coupon['id'], 5);
                    }

                } else {

                    switch ($coupon['bonusType']) {
                       
                        case 4:

                            $coupon['subCoupons'] = $mdl_coupons_sub->getList(null, array('parent_coupon_id' => $coupon['id']), 'id asc');
                            $this->setData($coupon);

                            $this->display('company/youhuiquan_edit4_4');
                            break;
                       
                        default:
                            $this->sheader(null,'无此类型');
                            break;
                    }
 

                }
                break;
        
           
            case 5:
                if (is_post()) {
                    $userData=[];
                    $address =  trim(post('address'));
                    if($address)$userData['googleMap']=$address;

                    $noAlert = trim(post('noAlert'));
                    if($noAlert)$userData['noAlert']=1;

                    $this->loadModel('user')->updateUserById($userData, $this->loginUser['id']);


                    if($coupon['status']==4){
                        $this->form_response(200,'修改成功',HTTP_ROOT_WWW .'company/coupons?coupon_type='.$coupon['bonusType']);
                    }else{
                        $confirm = (int)post('confirm');
                        if (!$confirm) $this->coupons_edit_failure('请先阅读产品发布说明');
                        $data['status'] = 4;
                        if ($mdl_coupons->update($data, $coupon['id'])) {
                            $this->form_response(200,'发布成功','SELF');
                        } else {
                            $this->coupons_edit_failure('发布失败');
                        }
                    }

                   

                } else {
                    $couponLink="http://$_SERVER[HTTP_HOST]/coupon1/".$id;
                    $this->setData($couponLink,'couponLink');
                    $this->setData(generateQRCode($couponLink),'couponQrCode');

                    $storeLink="http://$_SERVER[HTTP_HOST]/store/".$this->loginUser['id'];
                    $this->setData($storeLink,'storeLink');
                    $this->setData(generateQRCode($storeLink),'storeQrCode');

                    $packageLink="http://$_SERVER[HTTP_HOST]/coupon/4894";
                    $this->setData($packageLink,'packageLink');
                    $this->setData(generateQRCode($packageLink),'packageQrCode');

                    $listUrl=HTTP_ROOT_WWW."company/coupons?coupon_type=".$coupon['bonusType'];
                    $this->setData($listUrl,'listUrl');

                    $coupon_delivery_info =$mdl_coupons->getDeliveryInfo($id);
                    $this->setData( $coupon_delivery_info, 'coupon_delivery_info' );
                  
                    $this->display('company/youhuiquan_edit6');

                }
                break;
        }
    }

	
	
    public function easy_edit_advance_action()
    {   
       if (is_post()) {
            //$this->form_response_msg('发布成功');

            
            $data['title'] = post('title');
            $data['bonusType'] = 7;
            $data['voucher_deal_amount']= post('voucher_deal_amount');
            $data['voucher_original_amount'] = post('voucher_original_amount');

            $userCategoryId = post('userCategoryId');
            $data['categoryId'] = ','.$userCategoryId.',';

            $city = post('city');
            $city =($city)?','.join(',', $city).',':' ';
            $data['city'] = $city;

            $qty = (int)post('qty');
            if ($qty < 0) $qty = 0;
            if ($qty > 999999) $qty = 999999;

            $perCustomerLimitQuantity = (int)post('perCustomerLimitQuantity');
            if ($perCustomerLimitQuantity < 0) $perCustomerLimitQuantity = 0;
            if ($perCustomerLimitQuantity > 999999) $perCustomerLimitQuantity = 999999;

            $data['qty'] = $qty;
            $data['perCustomerLimitQuantity'] = $perCustomerLimitQuantity;

            $data['finePrint']= post('finePrint');
            $data['content']= post('content');

            $data['EvoucherOrrealproduct']= 'realproduct';

            $data['deliver_avaliable']         = trim(post('deliver_avaliable'));
            $data['pickup_avaliable']          = trim(post('pickup_avaliable'));

            $images=post('images');

            foreach ($images as $key => $value) {
                if($value=="default/image_upload.jpg")
                    unset($images[$key]);
                else
                    $images[$key]=trim($value);
            }
            $data['pic']=reset($images);
            $data['pics'] = serialize(array_slice($images, 1));


             //默认值
            $data['startTime'] = time();
            $data['endTime'] = strtotime('+ 90 days', time()) - 1;

            $data['createUserId'] = $this->loginUser['id'];
            $data['createTime'] = time();

            $data['isApproved'] = ($this->loginUser['trustLevel'] >= 1)?1:0;
            $data['status'] = 4;

            $data['visibleForBusiness'] = $this->loginUser['visibleForBusiness'];
            $data['languageType'] = $this->loginUser['languageType'];
            $data['businessName'] = $this->loadModel('user')->getBusinessDisplayName($this->loginUser['id']);

            $data['deliverFeeCalculationType'] = 'per_coupon';
            $data['platform_commission_base'] = DEFAULT_PLATFORM_COMMISSION_BASE;
            $data['platform_commission_rate'] = DEFAULT_PLATFORM_COMMISSION_RATE;

            if($this->loadModel('coupons')->insert($data)){
               $this->form_response(200,'发布成功',HTTP_ROOT_WWW .'company/coupons');
            }else{
                $this->form_response_msg('抱歉，出错了，请稍后再试');
            }
        } else {
            /**
             * 全部分类
             */
            $this->setData( $this->loadModel( 'infoClass' )->getChild4( '106' ), 'categories' );
            

            $this->setData( '极速发布 - 产品', 'pagename');
            $this->setData( '极速发布 - 产品 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->setData('publishment', 'menu');
            $this->setData('product_show', 'submenu');
            $this->display('company/easy_edit_advance');
        }
    }


    function business_application_success_action()
    {
        $this->setData('商家申请提交成功', 'pagename');
        $this->setData('index', 'menu');
        $this->setData('商家申请提交成功 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
        $this->display('company/business_application_success');
    }

    function coupons_edit_success_action()
    {
        $id = (int)get2('id');
        $this->setData('产品提交审核成功', 'pagename');
        $this->setData('coupons', 'menu');
        $this->setData('产品提交审核成功 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
        $this->display('company/coupons_edit_success');
    }



    function business_setting_main_action()
    {

        $this->setData('商家设置', 'pagename');
        $this->setData('business_setting', 'menu');
        $this->setData('business_setting', 'submenu');
        $this->setData('商家设置 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
        $this->display('company/business_setting_main');
    }

    function community_index_action()
    {   
        $this->setData('社群营销 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
        $this->setData('community', 'menu');
        $this->display_pc_mobile('company/community_index', 'mobile/company/community_index');

    }

    function ubonus_package_action()
    {   
        $where[]=' id in (4894) ';
        $data = $this->loadModel('coupons')->getList(null,$where);
        $this->setData($data,'package');
    

        $this->setData('Ubonus套餐 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
        $this->setData('community', 'menu');
		  $this->setData('ubonus_package', 'submenu');
		
        $this->display('company/ubonus_package');
    }

    //used in sub coupon edit
    public function sub_coupon_delete_action()
    {
        $id= post('id');

        if(!$id){
            $this->form_response_msg('id missing');
        }

        $this->loadModel('coupons_sub')->delete($id);

        $this->form_response(200,'delete success',null);

    }

    //帮助中心文章显示


    function shop_guige_details_action()
    {

        $id = (int)get2('id');
        $guige_id = (int)get2('guige_id');
        $mdl_shop_guige_details = $this->loadModel('shop_guige_details');
        $mdl_shop_guige = $this->loadModel('shop_guige');

        if ($id > 0) {
            $shop_guige_details = $mdl_shop_guige_details->get($id);

            if ($mdl_shop_guige_details->delete($id)) {

            }

        }


        $guige = $mdl_shop_guige->get($guige_id);
        $guige_name = $guige['name'];

        $where = array('userId' => $this->loginUser['id'], 'guige_id' => $guige_id);
        $list = $mdl_shop_guige_details->getList(array('id', 'name','name_en', 'guige_id'), $where);
        $this->setData($list, 'list');

        $this->setData($guige_id, 'guige_id');
        $this->setData($guige_name, 'guige_name');
        $this->setData('商品参数明细管理', 'pagename');
        $this->setData('shop_guige', 'submenu');
        $this->setData('index_publish', 'menu');
        $this->setData('商品参数明细管理 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
        $this->display('company/shop_guige_details');
    }


    function shop_guige_action()
    {

        $id = (int)get2('id');
        $mdl_shop_guige = $this->loadModel('shop_guige');

        if ($id > 0) {
            $shop_guige = $mdl_shop_guige->get($id);

            if ($mdl_shop_guige->delete($id)) {

            }

        }

        $sql = "select a.*,b.title from #@_shop_guige a left join #@_coupons b  on  a.coupon_id=b.id where a.userId=" . $this->loginUser['id'] . " order by a.is_for_one_product, a.id desc";
        $list = $mdl_shop_guige->getListBySql($sql);
        $this->setData($list, 'list');

        $this->setData('商店商品参数管理', 'pagename');
        $this->setData('shop_guige', 'submenu');
        $this->setData('index_publish', 'menu');
        $this->setData('商店商品参数管理 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
        $this->display('company/shop_guige');
    }

    function shop_guige_details_edit_action()
    {
        $id = (int)get2('id');
        $guige_id = (int)get2('guige_id');
        $mdl_shop_guige_details = $this->loadModel('shop_guige_details');
        $shop_guige_details = $mdl_shop_guige_details->get($id);

        if (is_post()) {
            $name = trim(post('name'));
			$name_en = trim(post('name_en'));
            $guige_id = post('guige_id');

            if ($shop_guige_details) {

                $data = array(
                    'name' => $name,
					 'name_en' => $name_en,
                    'userId' => $this->loginUser['id'],
                    'guige_id' => $guige_id
                );
                if ($mdl_shop_guige_details->update($data, $shop_guige_details['id'])) {
                    $this->form_response(200,'保存成功',HTTP_ROOT_WWW."company/shop_guige_details?guige_id=".$guige_id);
                } else {
                    $this->form_response_msg('保存失败');
                }
            } else {
                if (empty($name)) {
                    $this->form_response_msg('请填写商品规格描述');
                } else if (strlen($name) > 500) {
                    $this->form_response_msg('每次提交字符串长度最大为500');
                }

                $name = str_replace("，", ",", $name);
				 $name_en = str_replace("，", ",", $name_en);

                $arr_name = explode(",", $name);
				 $arr_name_en = explode(",", $name_en);

                foreach ($arr_name as $key => $val) {
                    $keyword = trim($val);
                    $keyword_en = trim($arr_name_en[$key]);
                    if (strlen($keyword) > 30) {
                        $keyword = substr($keyword, 0, 30);
                    }
                    if(!$keyword)continue;

                    $data = array(
                        'name' => $keyword,
						'name_en' => $keyword_en,
                        'userId' => $this->loginUser['id'],
                        'guige_id' => $guige_id
                    );

                    $mdl_shop_guige_details->insert($data);
                }
                $this->form_response(200,'保存成功',HTTP_ROOT_WWW."company/shop_guige_details?guige_id=".$guige_id);   

            }
        } else {
            $this->setData($guige_id, 'guige_id');
            $this->setData($shop_guige_details);
            $this->setData('商品规格参数管理', 'pagename');
            $this->setData('shop_guige', 'submenu');
            $this->setData('index_publish', 'menu');
            $this->setData('商品规格参数管理 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->display('company/shop_guige_details_edit');
        }
    }

    function shop_guige_edit_action()
    {
        $id = (int)get2('id');
        $mdl_shop_guige = $this->loadModel('shop_guige');
        $shop_guige = $mdl_shop_guige->get($id);

        // 获取商品列表
        $mdl_coupons = $this->loadModel('coupons');
        $sql = "select id,title from #@_coupons where createUserId=" . $this->loginUser['id'] . " order by id desc limit 200";
        $data_coupon = $mdl_coupons->getListBySql($sql);

        if (is_post()) {
            $name = trim(post('name'));
			$name_en = trim(post('name_en'));
            $is_pic_style = (int)post('is_pic_style');
            $is_for_one_product = (int)post('is_for_one_product');
            $coupon_id = (int)post('coupon_id');


            if ($shop_guige) {

                $data = array(
                    'name' => $name,
					'name_en' => $name_en,
                    'is_pic_style' => $is_pic_style,
                    'userId' => $this->loginUser['id'],
                    'coupon_id' => $coupon_id,
                    'is_for_one_product' => $is_for_one_product
                );
                if ($mdl_shop_guige->update($data, $shop_guige['id'])) {
                   $this->form_response(200,'保存成功',HTTP_ROOT_WWW."company/shop_guige");
                } else {
                    $this->form_response_msg('保存成功');
                }
            } else {
                if (empty($name)) {
                    $this->form_response_msg('请填写商品规格描述');
                }

                $data = array(
                    'name' => $name,
					 'name_en' => $name_en,
                    'is_pic_style' => $is_pic_style,
                    'userId' => $this->loginUser['id'],
                    'coupon_id' => $coupon_id,
                    'is_for_one_product' => $is_for_one_product
                );

                if ($mdl_shop_guige->insert($data)) {
                    $this->form_response(200,'保存成功',HTTP_ROOT_WWW."company/shop_guige");
                } else {
                   $this->form_response_msg('保存成功');
                }
            }
        } else {

            $this->setData($data_coupon, 'data_coupon');
            $this->setData($shop_guige);

            $this->setData('商城商品规格参数管理', 'pagename');
            $this->setData('index_publish', 'menu');
            $this->setData('shop_guige', 'submenu');
            $this->setData('商城商品规格参数管理 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->display('company/shop_guige_edit');
        }
    }

    function guige_link_edit_action()
    {
        if (get2('link_id')) {
            $this->loadModel('guige_link')->deleteGuigeLink(get2('link_id'));
        }

        if (is_post()) {
            $coupon_list = post('coupon_list');

            $mdl = $this->loadModel('guige_link');

            $guigeFusion = new GuigeFusion();

            try {

                foreach ($coupon_list as $value) {
                    $guigeFusion->addGuigeItem(new GuigeItem($value));
                }

                $guigeFusion->startFusion();

                if ($guigeFusion->fusionResultValidation()) {
                    $mdl->save_guige_link($guigeFusion);
                    $this->form_response(200, '生成成功', HTTP_ROOT_WWW . "company/guige_link_edit");

                } else {
                    echo "合成失败,规格数据有重合。";
                    echo "<span onclick='$(this).next().toggle();' style='color:#2bb8aa'>查看结果</span>";
                    echo $guigeFusion->mapDataVisualization();
                }

            } catch (Exception $e) {
                echo 'system error:' . $e->getMessage();
            }

        } else {

            $list = $this->loadModel('guige_link')->getGuigeLinkList($this->loginUser['id']);
            $this->setData($list, 'list');

            $mdl_coupon = $this->loadModel('coupons');
            $c_list = $mdl_coupon->getCouponListOfType($this->loginUser['id'], 9);

            $ex_list = $this->loadModel('guige_link')->getLinkedCouponList($this->loginUser['id']);

            foreach ($c_list as $ckey => $cvalue) {
                if (in_array($cvalue['id'], $ex_list)) unset($c_list[$ckey]);
                if ($cvalue['stripCode'] == '') unset($c_list[$ckey]);
            }

            $this->setData($c_list, 'c_list');

            $this->setData($this->parseUrl(), 'postUrl');

            $this->setData('index_publish', 'menu');
            $this->setData('guige_link_edit', 'submenu');
            $this->setData('规格链接编辑 -' . $this->site['pageTitle'], 'pageTitle');
            $this->display('company/guige_link_edit');
        }

    }

    function redeem_staff_delete_action(){
        if (get2('userid')){
            $this->loadModel('redeem_staff')->leaveCompany(get2('userid'), $this->loginUser['id']);
        }

        $this->sheader(HTTP_ROOT_WWW.'company/redeem_staff_manage');
    }
    function redeem_staff_manage_action()
    {
        $returnUrl = '/company/become_redeem_staff?businessId=' . $this->loginUser['id'];
        $url = HTTP_ROOT . "member/wx_redirect?returnUrl=" . urlencode($returnUrl);
        $staffInviteQrCode = generateQRCode($url);
        $this->setData($staffInviteQrCode, 'staffInviteQrCode');


        $staff_list = $this->loadModel('redeem_staff')->getStaffList($this->loginUser['id']);
        $this->setData($staff_list, 'staff_list');

        $this->setData('advanced_setting', 'menu');
        $this->setData('redeem_staff_manage', 'submenu');
        $this->setData('兑付员工管理 -' . $this->site['pageTitle'], 'pageTitle');
        $this->display('company/redeem_staff_manage');
    }


    function become_redeem_staff_action()
    {
        $businessId = get2('businessId');
        $userid = $this->loginUser['id'];

        if (!$businessId || !$userid) {
            $msg = '网络错误请稍后再试。'."<br><i class='fa fa-close fa-5x' style ='color:#f30'></i>";

        } elseif (intval($businessId) == intval($userid)) {
            $msg = '错误：不能自己加入自己'."<br><i class='fa fa-close fa-5x' style ='color:#fc3'></i>";

        } elseif ($this->loadModel('redeem_staff')->existInCompany($userid, $businessId)) {
            $msg = "您已经是 %s 公司的兑付员工了，不用重复加入。"."<br><i class='fa fa-close fa-5x' style ='color:#fc3'></i>";

        } elseif ($this->loadModel('redeem_staff')->joinCompany($userid, $businessId)) {
            $msg = "您成功加入了 %s 公司，成为可以兑付的员工！"."<br><i class='fa fa-check fa-5x' style ='color:green'></i>";

        } else {
            $msg = '网络错误请稍后再试。'."<br><i class='fa fa-close fa-5x' style ='color:#fc3'></i>";

        }

        $businessName = $this->loadModel('user')->getBusinessNameById($businessId);
        $userName = $this->loadModel('user')->getUserDisplayName($userid);

        $msg = sprintf($msg, $businessName);
        $this->setData($msg, 'msg');
        $this->setData($businessName, 'businessName');
        $this->setData($userName, 'userName');

        $this->display('company/become_redeem_staff');

    }

    function staff_action()
    {
        $id = (int)get2('id');
        $mdl_user = $this->loadModel('user');
      
        $where = array('role' => 5, 'user_belong_to_user' => $this->loginUser['id']);
        $list = $mdl_user->getList(null, $where, 'createdDate asc');
        $this->setData($list, 'list');

        $this->setData('分店管理', 'pagename');
        $this->setData('staff', 'submenu');
        $this->setData('advanced_setting', 'menu');
        $this->setData('分店管理 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
        $this->display('company/staff');
    }

    function staffnew_action()
    {
        $id = (int)get2('id');
        $mdl_user = $this->loadModel('user');

        $where = array('role' => 20, 'user_belong_to_user' => $this->loginUser['id']);
        $list = $mdl_user->getList(null, $where, 'createdDate asc');
        $this->setData($list, 'list');

        $this->setData('员工管理', 'pagename');
        $this->setData('staffnew', 'submenu');
        $this->setData('advanced_setting', 'menu');
        $this->setData('分店管理 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
        $this->display('company/staffnew');
    }
	
	  function truck_list_action()
    {
        $id = (int)get2('id');
        $mdl_truck = $this->loadModel('truck');

        $where = array('business_id' => $this->current_business['id']);
        $list = $mdl_truck->getList(null, $where, ' id asc');
		//var_dump($this->current_businessId);exit;
        $this->setData($list, 'list');

        $this->setData('Turck List', 'pagename');
        $this->setData('trucklist', 'submenu');
        $this->setData('Logistic_centre', 'menu');
        $this->setData('TruckManagement' . $this->site['pageTitle'], 'pageTitle');
        $this->display('factory/truck_list');
    }
    function staff_permissions_action()
    {


        // 只有商家用户可以操作该菜单

        if ($this->loginUser['role'] !=3){
            $this->sheader(null, "no access!");
        }

        $mdl_staff_roles =$this->loadModel(('staff_roles'));


        if (is_post()) {


            $staff_id = post('staff_id');
            if(!$staff_id){
                $this->form_response(500,'please choose the staff!');
            }
            $roles = post('roles');
            if(!$roles){
                $this->form_response(500,'please choose the roles for current staff!');
            }
            $roles =($roles)?','.join(',', $roles).',':' ';
          //  var_dump(($roles));exit;

            $data=array();
            $data['staff_id'] = $staff_id;
            $data['roles'] = $roles;

           // var_dump(($data));exit;
            $staff = $mdl_staff_roles->getByWhere(array('staff_id' => $staff_id));

           if ($staff) {
              //权限表里存在则修改
               $updatadata =array(
                   'roles'=>$roles
               );
             //  var_dump($updatadata);exit;
               if($mdl_staff_roles->updateByWhere($updatadata,array('staff_id' => $staff_id))){

                   $this->form_response(500,'修改成功！');
               }else{

                   $this->form_response(500,'something wrong !');
               }
           }else{
              //否则增加
               if($mdl_staff_roles->insert($data)){
                //   $redirect = HTTP_ROOT_WWW . 'company/staff_permissions';
                   $this->form_response(500,'保存成功！');
               }else{

                   $this->form_response(500,'something wrong !');
               }
           }



        }else{

            $staff_id = get2('staff_id');

            if($staff_id){
                $current_staff = $this->loadModel('staff_roles')->getByWhere(array('staff_id'=>$staff_id));
                if(!$current_staff){
                    $current_staff=array();
                    $current_staff['id']='';
                    $current_staff['staff_id']=$staff_id;
                    $current_staff['roles']='';

                }

            }
            $this->setData($current_staff,'current_staff');

            $stafflist =     $this->loadModel('user')->getAllStaffnew($this->loginUser['id']);

            $this->setData($stafflist, 'stafflist');

          //  var_dump($current_staff); exit;

            // 获得目前部门列表 并把角色列表加入刀部门下面
            $Departmentlist =     $this->loadModel('department')->getList();
            $mdl_roles =$this->loadModel('roles');
            foreach ($Departmentlist as $key => $value) {
                $Departmentlist[$key]['roles'] = $mdl_roles->getlist(null,array('role_belongto_department'=>$value['id']));
            }
            $this->setData($Departmentlist, 'departmentlist');

            // var_dump($Departmentlist); exit;

        }

            //获取该商家下所有员工信息


        $this->setData($this->parseUrl,'postUrl');
        $this->setData('员工授权', 'pagename');
        $this->setData('staff_permissions', 'submenu');
        $this->setData('advanced_setting', 'menu');
        $this->setData('员工授权 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
        $this->display('company/staff_permissions');
    }

    function staff_delete_action()
    {   
        $mdl_user = $this->loadModel('user');

        $id = (int)get2('id');

        if(!$id)$this->sheader(null, "员工ID缺失");


        $user = $mdl_user->get($id);

        if(!$user)$this->sheader(null, "没有找到该员工");

        
        if ($mdl_user->delete($id)) {
            if ($user['avatar']) {
                $this->file->deletefile(UPDATE_DIR . $user['avatar']);
            }
        }

        $this->sheader(HTTP_ROOT_WWW."company/staff");

    }
    function group_manager_delete_action()
    {
        $mdl_group_manager = $this->loadModel('user_group_manager');
        $mdl_group_user = $this->loadModel('user_group');

        $id = (int)get2('id');

        if(!$id)$this->sheader(null, "no find group manager info");

        $where =array(
            'factory_id'=>$this->current_business['id'],
            'manager_id'=>$id
        );
        $groupUser = $mdl_group_manager->getByWhere($where);

        if(!$groupUser)$this->sheader(null, "did not find group manager info");


        if ($mdl_group_manager->deleteByWhere($where)) {

            $mdl_group_user->deleteByWhere($where);
        }

        $this->sheader(HTTP_ROOT_WWW."factory/group_order_setting");

    }

    function staff_edit_action()
    {
        $mdl_user = $this->loadModel('user');
        $mdl_reg = $this->loadModel('reg');

        $id = (int)get2('id');

        $staff = $mdl_user->getByWhere(array('id' => $id, 'user_belong_to_user' => $this->loginUser['id']));

        if (is_post()) {
            /**
             * Location related data
             */
            $googleMap = trim(post('googleMap'));

            $addrNumber = trim(post('street_number'));
            $addrStreet = trim(post('route'));
            $addrPost = trim(post('postal_code'));
            $addrSuburb = trim(post('locality'));
            $google_location = trim(post('location'));
            $country_short = trim(post('country_short'));
            $addrState = trim(post('administrative_area_level_1'));
            $country = trim(post('country'));
            $googleMapUrl = trim(post('url'));


            /**
             * 管理员信息
             */
            $name = trim(post('username'));
            $email = trim(post('email'));

            $change_password = (int)post('change_password');
            $password = trim(post('password'));
            $password2 = trim(post('password2'));

            $person_first_name = trim(post('person_first_name'));
            $person_last_name = trim(post('person_last_name'));
            $tel = trim(post('tel'));
            $phone = trim(post('phone'));


            /**
             * 员工信息
             */
            $businessName = $this->loginUser['businessName'].'-分部';
            $contactPersonFirstname = trim(post('contactPersonFirstname'));
            $contactPersonLastname = trim(post('contactPersonLastname'));
            $contactPersonNickName = trim(post('contactPersonNickName'));
            $contactMobile = trim(post('contactMobile'));


            if ($staff) {
                $data = array(
                    // 'name' => $name,
                    'email' => $email,
                    'nickname' => $nickname,
                    'person_first_name' => $person_first_name,
                    'person_last_name' => $person_last_name,
                    'tel' => $tel,
                    'phone' => $phone,

                    'contactPersonFirstname'=>$contactPersonFirstname,
                    'contactPersonLastname'=>$contactPersonLastname,
                    'contactPersonNickName'=>$contactPersonNickName,
                    'contactMobile'=>$contactMobile,

                    'addrNumber' => $addrNumber,
                    'addrStreet' => $addrStreet,
                    'addrPost' => $addrPost,
                    'addrSuburb' => $addrSuburb,
                    'addrState' => $addrState,
                    'countryCode' => $country_short,
                    'country' => $country,
                    'googleMapUrl' => $googleMapUrl,
                    'google_location' => $google_location,
                    'googleMap' => $googleMap
                );

                if ($change_password) {
                    if (!$mdl_reg->chkPassword($password)) $this->form_response_msg('密码需要6-16个由a-z，A-Z，0-9以及下划线组成的字符串');

                    if ($password != $password2)$this->form_response_msg('确认密码与密码填写不一致');

                    $passwordByCustomMd5 = $this->md5($password);

                    $data['password'] = $passwordByCustomMd5;
                }


                if ($mdl_user->updateUserById($data, $staff['id'])) {

                    $this->form_response(200,'保存成功',HTTP_ROOT_WWW.'company/staff');
                } else {
                    $this->form_response_msg('保存成功');
                }

            } else {
                if (empty($name) ) $this->form_response_msg('请填写用户名');

                if ($mdl_user->chkUserName($name) > 0)$this->form_response_msg('该用户名已经存在');

                if (!$mdl_reg->chkUserName($name))$this->form_response_msg((string)$this->lang->remind_user_register_5);

                if (empty($password)) $this->form_response_msg('请填写密码');

                if (!$mdl_reg->chkPassword($password)) $this->form_response_msg('密码需要6-16个由a-z，A-Z，0-9以及下划线组成的字符串');

                if ($password != $password2) $this->form_response_msg('确认密码与密码填写不一致');




                $passwordByCustomMd5 = $this->md5($password);


                $data = array(
                    'user_belong_to_user' => $this->loginUser['id'],
                    'isApproved' => 1,
                    'isAdmin' => 0,
                    'person_first_name' => $person_first_name,
                    'person_last_name' => $person_last_name,
                    'nickname' => $nickname,
                    'name' => $name,
                    'email' => $email,
                    'password' => $passwordByCustomMd5,
                    'phone' => $phone,
                    'tel' => $tel,

                    'businessName' => $businessName,

                    'contactPersonFirstname'=>$contactPersonFirstname,
                    'contactPersonLastname'=>$contactPersonLastname,
                    'contactPersonNickName'=>$contactPersonNickName,
                    'contactMobile'=>$contactMobile,

                    'cityId' => $cityId,
                    'role' => 5,
                    'groupid' => 1,
                    'createdDate' => time(),
                    'lastLoginIp' => ip(),
                    'lastLoginDate' => time(),
                    'loginCount' => 1,
                    'addrNumber' => $addrNumber,
                    'addrStreet' => $addrStreet,
                    'addrPost' => $addrPost,
                    'addrSuburb' => $addrSuburb,
                    'addrState' => $addrState,
                    'countryCode' => $country_short,
                    'country' => $country,
                    'googleMapUrl' => $googleMapUrl,
                    'google_location' => $google_location,
                    'googleMap' => $googleMap
                );

                $data['isBusinessReferalExist'] = 0;
                $data['referralId'] = 0;
                $data['businessRefPointPercent'] = 10;
                $data['customerRefPointPercent'] = 10;
                $data['trustLevel'] = 0;
                $data['visibleForBusiness'] = 0;
                $data['languageType'] = 'zh-en';
                $data['isSuspended'] = 0;
                $data['needReapprovedAfterEdit'] = 1;
                $data['isApproved'] = 1;

                if ($mdl_user->addUser($data)) $this->form_response(200,'保存成功',HTTP_ROOT_WWW.'company/staff');

            }

        } else {
            $this->setData($staff);
            $this->setData('员工管理', 'pagename');
            $this->setData('staff', 'submenu');
            $this->setData('business_setting', 'menu');
            $this->setData('员工管理 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->display('company/staff_edit');
        }
    }




    function staff_edit_new_action()
    {   
        $mdl_user = $this->loadModel('user');
        $mdl_reg = $this->loadModel('reg');

        $id = (int)get2('id');

        $staff = $mdl_user->getByWhere(array('id' => $id, 'user_belong_to_user' => $this->loginUser['id']));

        if (is_post()) {
            /**
             * Location related data
             */
            $googleMap = trim(post('googleMap'));

            $addrNumber = trim(post('street_number'));
            $addrStreet = trim(post('route'));
            $addrPost = trim(post('postal_code'));
            $addrSuburb = trim(post('locality'));
            $google_location = trim(post('location'));
            $country_short = trim(post('country_short'));
            $addrState = trim(post('administrative_area_level_1'));
            $country = trim(post('country'));
            $googleMapUrl = trim(post('url'));


            /**
             * 管理员信息
             */
            $name = trim(post('username'));
            $email = trim(post('email'));

            $change_password = (int)post('change_password');
            $password = trim(post('password'));
            $password2 = trim(post('password2'));

            $person_first_name = trim(post('person_first_name'));
            $person_last_name = trim(post('person_last_name'));
            $tel = trim(post('tel'));
            $phone = trim(post('phone'));

           
            /**
             * 员工信息
             */
            $businessName = '';
            $contactPersonFirstname = trim(post('contactPersonFirstname'));
            $contactPersonLastname = trim(post('contactPersonLastname'));
            $contactPersonNickName = trim(post('contactPersonNickName'));
            $contactMobile = trim(post('contactMobile'));


            if ($staff) {
                $data = array(
                    // 'name' => $name,
                    'email' => $email,
                    'nickname' => $nickname,
                    'person_first_name' => $person_first_name,
                    'person_last_name' => $person_last_name,
                    'tel' => $tel,
                    'phone' => $phone,

                    'contactPersonFirstname'=>$contactPersonFirstname,
                    'contactPersonLastname'=>$contactPersonLastname,
                    'contactPersonNickName'=>$contactPersonNickName,
                    'contactMobile'=>$contactMobile,

                    'addrNumber' => $addrNumber,
                    'addrStreet' => $addrStreet,
                    'addrPost' => $addrPost,
                    'addrSuburb' => $addrSuburb,
                    'addrState' => $addrState,
                    'countryCode' => $country_short,
                    'country' => $country,
                    'googleMapUrl' => $googleMapUrl,
                    'google_location' => $google_location,
                    'googleMap' => $googleMap
                );

                if ($change_password) {
                    if (!$mdl_reg->chkPassword($password)) $this->form_response_msg('密码需要6-16个由a-z，A-Z，0-9以及下划线组成的字符串');

                    if ($password != $password2)$this->form_response_msg('确认密码与密码填写不一致');

                    $passwordByCustomMd5 = $this->md5($password);

                    $data['password'] = $passwordByCustomMd5;
                }
                

                if ($mdl_user->updateUserById($data, $staff['id'])) {

                    $this->form_response(200,'保存成功',HTTP_ROOT_WWW.'company/staffnew');
                } else {
                    $this->form_response_msg('保存成功');
                }

            } else {
                if (empty($name) ) $this->form_response_msg('请填写用户名');

                if ($mdl_user->chkUserName($name) > 0)$this->form_response_msg('该用户名已经存在');

                if (!$mdl_reg->chkUserName($name))$this->form_response_msg((string)$this->lang->remind_user_register_5);

                if (empty($password)) $this->form_response_msg('请填写密码');

                if (!$mdl_reg->chkPassword($password)) $this->form_response_msg('密码需要6-16个由a-z，A-Z，0-9以及下划线组成的字符串');

                if ($password != $password2) $this->form_response_msg('确认密码与密码填写不一致');

               


                $passwordByCustomMd5 = $this->md5($password);
                

                $data = array(
                    'user_belong_to_user' => $this->loginUser['id'],
                    'isApproved' => 1,
                    'isAdmin' => 0,
                    'person_first_name' => $person_first_name,
                    'person_last_name' => $person_last_name,
                    'nickname' => $nickname,
                    'name' => $name,
                    'email' => $email,
                    'password' => $passwordByCustomMd5,
                    'phone' => $phone,
                    'tel' => $tel,

                    'businessName' => $businessName,

                    'contactPersonFirstname'=>$contactPersonFirstname,
                    'contactPersonLastname'=>$contactPersonLastname,
                    'contactPersonNickName'=>$contactPersonNickName,
                    'contactMobile'=>$contactMobile,

                    'cityId' => $cityId,
                    'role' => 20,
                    'groupid' => 1,
                    'createdDate' => time(),
                    'lastLoginIp' => ip(),
                    'lastLoginDate' => time(),
                    'loginCount' => 1,
                    'addrNumber' => $addrNumber,
                    'addrStreet' => $addrStreet,
                    'addrPost' => $addrPost,
                    'addrSuburb' => $addrSuburb,
                    'addrState' => $addrState,
                    'countryCode' => $country_short,
                    'country' => $country,
                    'googleMapUrl' => $googleMapUrl,
                    'google_location' => $google_location,
                    'googleMap' => $googleMap
                );

                $data['isBusinessReferalExist'] = 0;
                $data['referralId'] = 0;
                $data['businessRefPointPercent'] = 10;
                $data['customerRefPointPercent'] = 10;
                $data['trustLevel'] = 0;
                $data['visibleForBusiness'] = 0;
                $data['languageType'] = 'zh-en';
                $data['isSuspended'] = 0;
                $data['needReapprovedAfterEdit'] = 1;
                $data['isApproved'] = 1;

                if ($mdl_user->addUser($data)) $this->form_response(200,'保存成功',HTTP_ROOT_WWW.'company/staffnew');

            }

        } else {
            $this->setData($staff);
            $this->setData('员工管理', 'pagename');
            $this->setData('staffnew', 'submenu');
            $this->setData('advanced_setting', 'menu');
            $this->setData('员工管理 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->display('company/staff_edit_new');
        }
    }


    function  groupmanager_edit_action(){

        $mdl_user = $this->loadModel('user');
        $mdl_group = $this->loadModel('user_group_manager');


        $this->setData($group_manager);
        $this->setData('Group Management', 'pagename');
        $this->setData('group_order_setting', 'submenu');
        $this->setData('customer_management', 'menu');
        $this->setData('Group Management - Business Center' . $this->site['pageTitle'], 'pageTitle');
        $this->display('company/groupmanager_new');

    }


// 创建一个group manager
    function groupmanager_new_action()
    {
        $mdl_user = $this->loadModel('user');
        $mdl_group = $this->loadModel('user_group_manager');
        $mdl_reg = $this->loadModel('reg');

        $id = (int)get2('id');

        $group_manager =$mdl_group->getGroupMangerInfo($id,$this->current_business['id']);
       // var_dump($group_manager);exit;
        $factory_id =$this->loadModel('user_factory')->getBusinessId($this->loginUser['id'],$this->loginuser['role']);
       // var_dump($factory_id);exit;


        if (is_post()) {
            /**
             * Location related data
             */
            $g_approved = trim(post('g_approved'));
            if(!$g_approved) {
                $g_approved=0;
            }
            //var_dump($g_approved);exit;
            $googleMap = trim(post('googleMap'));

            $addrNumber = trim(post('street_number'));
            $addrStreet = trim(post('route'));
            $addrPost = trim(post('postal_code'));
            $addrSuburb = trim(post('locality'));
            $google_location = trim(post('location'));
            $country_short = trim(post('country_short'));
            $addrState = trim(post('administrative_area_level_1'));
            $country = trim(post('country'));
            $googleMapUrl = trim(post('url'));


            /**
             * 管理员信息
             */
            $name = trim(post('username'));
            $email = trim(post('email'));

            $change_password = (int)post('change_password');
            $password = trim(post('password'));
            $password2 = trim(post('password2'));

            $person_first_name =  trim(post('contactPersonFirstname'));
            $person_last_name = trim(post('contactPersonLastname'));
            $tel = trim(post('tel'));



            /**
             * 员工信息
             */
            $businessName = '';
            $contactPersonFirstname = trim(post('contactPersonFirstname'));
            $contactPersonLastname = trim(post('contactPersonLastname'));
            $contactPersonNickName = trim(post('contactPersonNickName'));
            $nickname =$contactPersonNickName;
            $phone = trim(post('phone'));


            if ($group_manager) {
                $data = array(
                    // 'name' => $name,
                    'email' => $email,
                    'nickname' => $nickname,
                    'displayName'=>$nickname,
                    'person_first_name' => $contactPersonFirstname,
                    'person_last_name' => $contactPersonLastname,
                    'tel' => $tel,


                    'contactPersonFirstname'=>$contactPersonFirstname,
                    'contactPersonLastname'=>$contactPersonLastname,
                    'contactPersonNickName'=>$contactPersonNickName,
                    'phone'=>$phone,

                    'addrNumber' => $addrNumber,
                    'addrStreet' => $addrStreet,
                    'addrPost' => $addrPost,
                    'addrSuburb' => $addrSuburb,
                    'addrState' => $addrState,
                    'countryCode' => $country_short,
                    'country' => $country,
                    'googleMapUrl' => $googleMapUrl,
                    'google_location' => $google_location,
                    'googleMap' => $googleMap
                );

                if ($change_password) {
                    if (!$mdl_reg->chkPassword($password)) $this->form_response_msg('密码需要6-16个由a-z，A-Z，0-9以及下划线组成的字符串');

                    if ($password != $password2)$this->form_response_msg('two passwords are different');

                    $passwordByCustomMd5 = $this->md5($password);

                    $data['password'] = $passwordByCustomMd5;
                }

                $data_group=array(
                    'nickname'=>$nickname,
                    'isApproved'=>$g_approved
                );
                $where =array(
                    'factory_id'=>$this->current_business['id'],
                    'manager_id'=>$id
                );
               // var_dump($id);exit;
                $mdl_group->updateByWhere($data_group,$where);


                if ($mdl_user->updateUserById($data, $group_manager['id'])) {

                    $this->form_response(200,'保存成功',HTTP_ROOT_WWW.'factory/group_order_setting');
                } else {
                    $this->form_response_msg('保存成功');
                }

            } else {
                if (empty($name) ) $this->form_response_msg('请填写用户名');

                if ($mdl_user->chkUserName($name) > 0)$this->form_response_msg('该用户名已经存在');

                if (!$mdl_reg->chkUserName($name))$this->form_response_msg((string)$this->lang->remind_user_register_5);

                if (empty($password)) $this->form_response_msg('请填写密码');

                if (!$mdl_reg->chkPassword($password)) $this->form_response_msg('密码需要6-16个由a-z，A-Z，0-9以及下划线组成的字符串');

                if ($password != $password2) $this->form_response_msg('确认密码与密码填写不一致');




                $passwordByCustomMd5 = $this->md5($password);


                $data = array(
                    'isApproved' => 1,
                    'isAdmin' => 0,
                    'person_first_name' => $person_first_name,
                    'person_last_name' => $person_last_name,
                    'nickname' => $nickname,
                    'name' => $name,
                    'email' => $email,
                    'password' => $passwordByCustomMd5,
                    'phone' => $phone,
                    'tel' => $tel,

                    'businessName' => $businessName,

                    'contactPersonFirstname'=>$contactPersonFirstname,
                    'contactPersonLastname'=>$contactPersonLastname,
                    'contactPersonNickName'=>$contactPersonNickName,
                    'contactMobile'=>$contactMobile,

                    'cityId' => '',
                    'role' => 4,
                    'groupid' => 1,
                    'createdDate' => time(),
                    'lastLoginIp' => ip(),
                    'lastLoginDate' => time(),
                    'loginCount' => 1,
                    'addrNumber' => $addrNumber,
                    'addrStreet' => $addrStreet,
                    'addrPost' => $addrPost,
                    'addrSuburb' => $addrSuburb,
                    'addrState' => $addrState,
                    'countryCode' => $country_short,
                    'country' => $country,
                    'googleMapUrl' => $googleMapUrl,
                    'google_location' => $google_location,
                    'googleMap' => $googleMap
                );

                $data['isBusinessReferalExist'] = 0;
                $data['referralId'] = 0;
                $data['businessRefPointPercent'] = 10;
                $data['customerRefPointPercent'] = 10;
                $data['trustLevel'] = 0;
                $data['visibleForBusiness'] = 0;
                $data['languageType'] = 'zh-en';
                $data['isSuspended'] = 0;
                $data['needReapprovedAfterEdit'] = 1;
                $data['isApproved'] = 1;
                $data['displayName'] =$nickname;

                $newid =$mdl_user->insert($data);
                if ($newid)

                {
                    $data_grou_manager =array(
                        'factory_id'=>$factory_id,
                        'manager_id'=>$newid,
                        'nickname'=>$nickname,
                        'is_hide'=>0,
                        'gendate'=>time,
                        'isCreatedByFactory'=>1,
                        'isApproved'=>1
                     );
                    $mdl_group->insert($data_grou_manager);
                    $this->form_response(200,'保存成功',HTTP_ROOT_WWW.'factory/group_order_setting');
                }


            }

        } else {
          //  var_dump($group_manager);exit;
            $this->setData($group_manager,'data');
            $this->setData('Group Management', 'pagename');
            $this->setData('group_order_setting', 'submenu');
            $this->setData('customer_management', 'menu');
            $this->setData('Group Management - Business Center' . $this->site['pageTitle'], 'pageTitle');
            $this->display('company/groupmanager_new');
        }
    }



    function profile_pic_action()
    {
        $shop=get2('shop');
			$this->setData( $shop, 'shop' );
        $mdl_user = $this->loadModel("user");

        if (is_post()) {
            $images=post('images');

            foreach ($images as $key => $value) {
                if($value=="default/image_upload.jpg")
                    unset($images[$key]);
                else
                    $images[$key]=trim($value);
            }

            $data['pic']=trim(reset($images));

            if ($mdl_user->update($data, $this->loginUser['id'])) {
                $this->form_response_msg( '保存成功');
            } else {
                $this->form_response_msg('保存失败');
            }
        } else {
            
            $this->setData('图片信息', 'pagename');
            $this->setData('basic_setting', 'menu');
            $this->setData('profile_pic', 'submenu');
            $this->setData('图片信息修改 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->display('company/profile_pic');

        }
    }


    function fill_profile_action()
    {
        if (is_post()) {
            $returnUrl = $this->returnUrl;
            $action = post('action');
            $g_id = post('g_id');
            $g_name = post('g_name');
            $g_qty = post('g_qty');

            $data = array();
            $data['nickname'] = trim(post('nickname'));
            $data['phone'] = trim(post('phone'));
            $data['email'] = trim(post('email'));
            $mdl_user = $this->loadModel('user');
            if ($mdl_user->updateUserById($data, $this->loginUser['id'])) {

                if ($action == 'autoJoin') {
                     if(!$this->loadModel('group_buy')->isAlreadyInGroup($g_id, $this->loginUser['id'])){
                        $data = array(['coupon_id'=>'-1','qty'=>$g_qty]);
                         $orderId = $this->loadModel('group_buy')->createOrder($data);
                         $this->loadModel('group_buy')->joinGroup($g_id, $this->loginUser['id'],$orderId);
                     }
                    $returnUrl=HTTP_ROOT_WWW."group_buy/index?group_buy_id=".$g_id;

                } elseif ($action == 'autoCreate') {
                    $id =$this->loadModel('group_buy')->userCreateGroup($g_id, $g_name, $this->loginUser['id']);
                    
                    $returnUrl=HTTP_ROOT_WWW."group_buy/index?jstrigger=joinaftercreate&group_buy_id=".$id;
                }

                $this->form_response(200, "修改成功", $returnUrl);
            } else {
                $this->form_response_msg("修改失败");
            }
        } else {
            $type = get2('type');

            $action = get2('action');
            $g_id = get2('g_id');
            $g_name = get2('g_name');
            $g_qty = get2('g_qty');
			
            $this->setData($action, 'action');
            $this->setData($g_id, 'g_id');
            $this->setData($g_name, 'g_name');
            $this->setData($g_qty, 'g_qty');

            $this->setData('团购资料补全', 'pagename');
            $this->setData('info', 'menu');
            $this->setData('profile_manager', 'submenu');
            $this->setData('修改资料 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->display('company/fill_profile');
        }
    }

    function profile_manager_action()
    {
       
        $mdl_reg = $this->loadModel('reg');

        if (is_post()) {
            $nickname = trim(post('nickname'));
         
            $phone = trim(post('phone'));
            $tel = trim(post('tel'));
            $email = trim(post('email'));
            $backupEmail = trim(post('backupEmail'));

            if($phone&&!$mdl_reg->chkPhone($phone))$this->form_response_msg('手机仅支持中国或澳洲手机号');
            if($email&&!$mdl_reg->chkMail($email))$this->form_response_msg('请输入正确的邮箱');

            $data = array();
            $data['nickname'] = $nickname;
            $data['person_first_name'] = $person_first_name;
            $data['person_last_name'] = $person_last_name;
            $data['phone'] = $phone;
            $data['tel'] = $tel;
            $data['backupEmail'] = $backupEmail;
            $data['email'] = $email;

            if($this->loginUser['phone']!=$phone)$data['phone_verified']='false';

            $mdl_user = $this->loadModel('user');
            if ($mdl_user->updateUserById($data, $this->loginUser['id'])) {
                if ($this->returnUrl) {
                    $this->form_response(200, "修改成功", $this->returnUrl);
                } else {
                    $this->form_response(200, "修改成功", 'SELF');
                }
            } else {
                $this->form_response_msg("修改失败");
            }
        } else {
            $this->setData('管理员资料修改', 'pagename');
            $this->setData('basic_setting', 'menu');
            $this->setData('profile_manager', 'submenu');
            $this->setData('修改资料 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->display('company/profile_manager');
        }
    }


/*

1） 从产品库中找到了多少个产品的图片。
2）  有多少个产品从产品库中获取图片成功。

3） 从网上获得多少图片
4） 成功多少个产品。




*/



   public    function images_grab_action()
    {
       
       	$barcode =get2('barcode');

        if (is_post() || $barcode) {

			
			 $customer_id =$this->loginUser['id'];
			 
			 $mdl_restaurant_menu= $this->loadModel('restaurant_menu');
			 $sql ="select * from cc_restaurant_menu where restaurant_id =".$customer_id ." and ( length(menu_cn_name)>0  or length(menu_en_name)>0)  ";
			 $menuList =$mdl_restaurant_menu->getListBySql($sql);
			 //var_dump($menuList);exit;
			
			$mdl_standard_product_info= $this->loadModel('standard_product_info');
			$mdl_standard_product_query_histroy= $this->loadModel('standard_product_query_histroy');
			
			
            // $noQuanli
			$countOfBarCodeNotLongEnough = 0 ;
			$countofWebCollectMissing =0;
			$countofWebCollected =0;
			$countOfStandardProductLibCollected =0;
			$countOfStandardProductLibCollecteFailied=0;
			
			
			//var_dump($menuList);exit;
			
			 
			
			$index=0;
			foreach ($menuList as $key => $value) {
			  if($barcode) {
				  if ($value['barcode_number'] <>$barcode ) continue;
				  
			  }
			 // var_dump($value['barcode_number']);exit;
              //$index++;
			 // if($index==100) break;
				// 如果条形码的长度不超过7 ，则跳过，走下一条记录
                 if( strlen(trim($value['barcode_number']))<=7) {
					 $countOfBarCodeNotLongEnough ++;
					 continue;
					 
				 }

               // 查找在产品库中是否存在，如果存在，则将图片路径新鲜，复制到该产品之下。 可能包含 主图和子图信息。
				$where =array (
					'barcode_number' =>$value['barcode_number']
				 );
				$rec = $mdl_standard_product_info->getByWhere($where);
				 if($rec) {
					 //将 图片信息填到该记录这里
					 
					$picData = $this->generate_images_data($rec);
					if($picData) {
						
						if($mdl_restaurant_menu->update($picData,$value['id'])) 
							{
								$countOfStandardProductLibCollected ++ ;
							
							}else{
								
								$countOfStandardProductLibCollecteFailied ++ ;
							}
					   continue; 
				  }

				 }
				 
				 
				
				
				// 如果该编码已经查询过了，则不再向网站进行请求。 
				 
				 $where1 =array (
					 'barcode_number' =>$value['barcode_number']
					 			 
				 );
				 $rec1= $mdl_standard_product_query_histroy->getByWhere($where);
				 // 如果该编码已经查询过了，则不再向网站进行请求。 
				 if($rec1) {
					 //如果历史上已经查询该记录，并且未找到结果的话，则这次不再查找。
					 // var_dump($rec1['barcode_number']);
					 continue; 
				 }else{
					// 进入网站开始查询 暂时只有barcode-loopup
                   	 //var_dump('here');exit;			
					 $response = $this->get_product_image_info($value['barcode_number']);
					 usleep(700000);
					 if($response) { //获得查询结果 
						
						 
						
						  //如果在网站上获取到了记录，需要做以下动作
						  /*
						    1) 生成图片文件(包括cut文件） 
							2) 生成标准产品库记录
							3） 修改菜单的图片信息：根据产品库记录 生成 当前菜单对应的图片数据路径并复制给当前菜单，包括主图及附图，同时可能赋值更多信息，比如：descriptiono ,menu_en_name etc
							4） 历史记录中增加已查询到该记录
						  
						  
						  */
						  
						  /*  1) 生成图片文件(包括cut文件）  */
						  
						  $images = $response->products[0]->images;
						  //var_dump($images);exit;
						  if($images) {
							$pic_arr= $this->gen_image_file_from_barcode_web($images);
		                   // var_dump($pic_arr);exit;
							if($pic_arr) {
								
								$menu_pic= $pic_arr[0];
								$pic_arr = array_splice($pic_arr,1);
								
							}
							//如果数组中还存在元素，则生成子图json字符串
							if($pic_arr) {
								$menu_pics= trim(serialize($pic_arr));
								
							}
						    if($menu_pics ==NULL) {
								$menu_pics='';
							}
							}

						   /*  2) 生成标准产品库记录  */ 
							  $insertData  =array (
								 'barcode_number'=>$response->products[0]->barcode_number,
								 'title'=>$response->products[0]->title,
								 'title_cn'=>$response->products[0]->title,
								 'category'=>$response->products[0]->category,
								 'description'=>$response->products[0]->description,
								 'images1'=>$menu_pic,
								 'imagesmore'=>$menu_pics,
							     'grapType'=>1,
								 'source_business_id'=>$customer_id
								
							  
							  );
							 // var_dump($insertData);
							  $newid = $mdl_standard_product_info->insert($insertData);
							 // var_dump('newid is : '.$newid);				 

								/*3） 修改菜单的图片信息：根 */
							if($newid)  {
								  	 
									$rec2 = $mdl_standard_product_info->get($newid);
									
									if($rec2) {
										//将 图片信息填到该记录这里
							 
										$picData2 = $this->generate_images_data($rec2);
										
										if($picData2) {
											
											if($mdl_restaurant_menu->update($picData2,$value['id'])) {
												
												//var_dump('新增的产品库修改图片数据最终成功');exit;
											}
										
										}
									}
								  

								}else{
									 //  var_dump('插入出了问题');exit;
									   
								   }
						
						  
							/* 历史记录中增加已查询到该记录 */ 
						
							 $insertData1 =array (
							 'barcode_number'=>$response->products[0]->barcode_number,
							  'isFind'=>1	
								);
							$mdl_standard_product_query_histroy->insert($insertData1);
							
							
							
							
						  
						     $countofWebCollected++;
						  
						  
					 }else{
						 // var_dump('到这里');exit;	
						 $countofWebCollectMissing ++;
						 $insertData1 =array (
							 'barcode_number'=>$value['barcode_number'],
							  'isFind'=>0	
						 );
						 $mdl_standard_product_query_histroy->insert($insertData1);
					 }
					 
					 
				 }
				 
			
            }
			 
			 
			 $this->setData($countOfBarCodeNotLongEnough,'countOfBarCodeNotLongEnough');
			 $this->setData($countofWebCollectMissing,'countofWebCollectMissing');
			 $this->setData($countofWebCollected,'countofWebCollected');
			 $this->setData($countOfStandardProductLibCollected,'countOfStandardProductLibCollected');
			 $this->setData($countOfStandardProductLibCollecteFailied,'countOfStandardProductLibCollecteFailied');
			 
			 
			
			 
			 
			 
			 
			
			 
		
			
			
                $this->setData(20,'sucessfulNumber');
               
                
              
           
        } else {
			
		}
		
		
		
            $this->setData('自动图片匹配', 'pagename');
            $this->setData('index_publish', 'menu');
            $this->setData('images_grab', 'submenu');
            $this->setData('自动图片匹配 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->display('company/images_grab');
       
    }


function get_product_image_info($code) {
	


$api_key = 'qkal0xb17wczjsawgngde250k9o6ib';
$url = 'https://api.barcodelookup.com/v3/products?barcode='.$code.'&formatted=y&key=' . $api_key;

$ch = curl_init(); // Use only one cURL connection for multiple queries

$data = $this->get_data($url, $ch);

$response = array();
$response = json_decode($data);
if($code ==41390008009) {
	
	//var_dump ($response);
}
return $response; 
/*

echo '<strong>Barcode Number:</strong> ' . $response->products[0]->barcode_number . '<br><br>';

echo '<strong>Title:</strong> ' . $response->products[0]->title . '<br><br>';


echo '<strong>pic:</strong> ' . $response->products[0]->images[0] . '<br><br>';

echo '<strong>Entire Response:</strong><pre>';
print_r($response);
echo '</pre>';

*/


}

function get_data($url, $ch) {
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}







    function account_balance_action()
    {
        $mdl_recharge = $this->loadModel('recharge');


        /**
         * Filter
         */

        $filter=array(
            'all'       =>'全部',
            'business'  =>'商家款项',
            'user'      =>'用户款项',
        );
        $filter = array_merge ($filter,$mdl_recharge->balanceProcessTypeArray());
        $this->setData($filter,'filter');

    
        $type=trim(get2("type"));
        if(!$type)$type='all';
        $this->setData($type,'type');

        $where['userId']=$this->loginUser['id'];
        
        if($type=='all'){
        }elseif($type=='user'){
            $where[]= $mdl_recharge->getOrCounditionSqlStr('payment',$mdl_recharge->userDisplayRecord());
        }elseif($type=='business'){
            $where[]= $mdl_recharge->getOrCounditionSqlStr('payment',$mdl_recharge->businessDisplayRecord());
        }else{
            $where['payment']=$type;
        }
        
        /**
         * Data Caculation
         */
        

        $pageSql = $mdl_recharge->getListSql(null, $where, 'createTime desc');

        $pageUrl = $this->parseUrl()->set('page');
        $pageSize = 30;
        $maxPage = 10;
        $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
        $data = $mdl_recharge->getListBySql($page['outSql']);


        $balance = $mdl_recharge->getBalance($this->loginUser['id']);
        $this->setData($balance, 'balance');

        $available_fund = $mdl_recharge->getBalanceOfUser($this->loginUser['id']);
        $this->setData($available_fund, 'available_fund');

        $pending_fund = $mdl_recharge->getTotalPending($this->loginUser['id']);
        $this->setData($pending_fund, 'pending_fund');

        $pending_fund_in = $mdl_recharge->getIncomingPending($this->loginUser['id']);
        $this->setData($pending_fund_in, 'pending_fund_in');

        $pending_fund_out = $mdl_recharge->getOutGoingPending($this->loginUser['id']);
        $this->setData($pending_fund_out, 'pending_fund_out');


        $this->setData($data, 'data');

        $this->setData($page['pageStr'], 'pager');

        $this->setData('账户记录', 'pagename');
        $this->setData('balance_account', 'menu');

        $this->setData('recharge', 'submenu');

        $this->setData('账户记录 - 管理中心 - ' . $this->site['pageTitle'], 'pageTitle');

        $this->display_pc_mobile('company/account_balance','mobile/company/account_balance');
    }

    public function update_recharge_void_action(){
        $mdl_order = $this->loadModel( 'recharge' );

        $orderId=$_GET['orderId'];

        $order = $mdl_order->getByWhere(array('orderId'=>$orderId));

        if(!$orderId||!$order){
            $this->sheader( null, '找不到该订单' );
        }
        
        if($mdl_order->updataTransactionStatus($orderId,BalanceProcess::VOID)){
            $this->sheader( HTTP_ROOT_WWW.'company/account_balance' );
        }else{
            $this->sheader( null, '更新出错，请稍后再试' );
        }
        
    }

    function promotion_code_delete_action()
    {
        $id = get2('id');
        $mdl_promotionCode = $this->loadModel('wj_promotion_code');

        if ($mdl_promotionCode->isCodeBelongToUser($id, $this->loginUser['id'])) {
            $mdl_promotionCode->deletePromotionCode($id);
            $this->sheader(HTTP_ROOT_WWW . 'company/promotion_code');

        } else {
            $this->sheader(null, "不是你的打折码或打折码不存在");
        }
    }

    function check_promotion_code_action()
    {
        $pcode = get2('pcode');
        $businessUserId = get2('businessUserId'); 

		$total_cart_amount =(int)get2('total_cart_amount1'); 
		
        $mdl_promotionCode = $this->loadModel('wj_promotion_code');

        $codeList = $mdl_promotionCode->matchedCode($this->loginUser['id'],$businessUserId);
    // var_dump($codeList);exit;
        if (in_array($pcode, $codeList)) {
            $promotionData = $mdl_promotionCode->getPromotionCode($pcode);
            $promotionCode = new PromotionCode($promotionData);

            //新人卷，每个用户只可以使用一次
            $alreadyUsed=$this->loadModel('order')->getByWhere(array('userId'=>$this->loginUser['id'],'promotion_id'=>$promotionData['id'],' coupon_status!="d01" '));
            if($promotionData['single_use_per_user'] && $alreadyUsed){
                $data['status'] = false;
                $data['msg'] = '该折扣码只能使用一次,您已经使用过了 ';
                echo json_encode($data);
                exit;
            }

            if ($promotionCode->isCodeExpired()) {
                $data['status'] = false;
                $data['msg'] = '验证失败,折扣码失效';
            } else {
                $promotion_amount = $promotionCode->applyCode($this->loginUser['id']);
                //var_dump($promotion_amount);exit;
                if ($promotion_amount == 0) {
                    $data['status'] = false;
                    $value = (int)$promotionData['apply_condition_value'];
                    $data['msg'] = '验证成功,但是需要购买大于' . $value . '个才能生效。';
                } else {
					
					$value = (int)$promotionData['apply_condition_value'];
					
					if($pcode == '777') {
						
						if($total_cart_amount <30) {
							
							$data['status'] = false;
							$value = (int)$promotionData['apply_condition_value'];
							$data['msg'] = '验证成功,但是需要购买达到$30个才能生效。';
							
						}else{
							
							$data['status'] = true;
							$data['msg'] = '验证成功';
							$data['promotion_amount'] = $promotion_amount;
							$data['promotion_id'] = $promotionData['id'];
							$data['promotion_code'] = $pcode;
							$data['promotion_des'] = $promotionCode->getDescription();
						}
						
						
						
						
					}else{
						//var_dump($total_cart_amount.''.$value);exit;
				     if(  $total_cart_amount <$value ) {
						   $data['status'] = false;
                  
							$data['msg'] = '验证成功,但是需要购买大于$' . $value . '个才能生效。';
						 
					 }else{
						 $data['status'] = true;
						$data['msg'] = (string)$this->lang->verified_successful;;
						$data['promotion_amount'] = $promotion_amount;
						$data['promotion_id'] = $promotionData['id'];
						$data['promotion_code'] = $pcode;
						$data['promotion_des'] = $promotionCode->getDescription();
						 
					 }
 				  
						
						
						
						
					
						
					}
                   
                }
            }

        } else {
            $data['status'] = false;
            $data['msg'] = (string)$this->lang->promotion_verified_failure;
        }
       
        echo json_encode($data);

    }

    function promotion_code_manage_action()
    {

        $mdl_coupons = $this->loadModel('coupons');
        $mdl_promotionCode = $this->loadModel('wj_promotion_code');

        $where['user_id'] = $this->loginUser['id'];

        $pageSql = $mdl_promotionCode->getListSql(null, $where);

        $pageUrl = $this->parseUrl()->set('page');
        $pageSize = 10;
        $maxPage = 10;
        $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
        $data = $mdl_promotionCode->getListBySql($page['outSql']);

        $this->setData($data, 'promotionCodeList');
        $this->setData($page['pageStr'], 'pager');

        $this->setData('index_publish', 'menu');
        $this->setData('promotion_code_manage', 'submenu');
        $this->setData('编辑折扣吗 - ' . '产品 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
        $this->display('company/promotion_code');

    }

    function promotion_code_action()
    {
        $mdl_coupons = $this->loadModel('coupons');
        $mdl_promotionCode = $this->loadModel('wj_promotion_code');
        if (is_post()) {
            $couponId = trim(post('couponId'));
            $type = trim(post('type'));
            $value = trim(post('value'));
            $expireType = trim(post('expireType'));
            $expireValue = trim(post('expireValue'));
            $codeGenMethod = trim(post('codeGenMethod'));
            $promotionCode = trim(post('promotionCode'));
            $promotionDes = trim(post('promotionDes'));
            $applyConditionType = trim(post('apply_condition_type'));
            $applyConditionValue = trim(post('apply_condition_value'));

            $singleUsePerUser = trim(post('single_use_per_user'));

            if ($codeGenMethod == 'random') $promotionCode = PromotionCode::RANDOM_CODE;
            if ($couponId == 'all') $couponId = PromotionCode::APPLY_TO_ALL_COUPONID;

            if ($type == 'fixed') $type = PromotionCode::TYPE_FIXEDAMOUNT;
            if ($type == 'percentage') $type = PromotionCode::TYPE_PERCENTAGE;

            if ($applyConditionType == 'none') $applyConditionType = PromotionCode::CONDITION_NONE;
            if ($applyConditionType == 'conditionQty') $applyConditionType = PromotionCode::CONDITION_QTY;
            if ($applyConditionType == 'conditionMinspend') $applyConditionType = PromotionCode::CONDITION_MINSPEND;

            if ($expireType == 'unlimited') $expireType = PromotionCode::EXPIRETYPE_UNLIMITED;
            if ($expireType == 'fixedQty') $expireType = PromotionCode::EXPIRETYPE_FIXEDQTY;
            if ($expireType == 'expireInDays') $expireType = PromotionCode::EXPIRETYPE_EXPIREINDAYS;

            if($singleUsePerUser=='enable') $singleUsePerUser=1;

            $pcode = new PromotionCode();
            $pcode->setUserId($this->loginUser['id']);
            $pcode->setCouponId($couponId);
            $pcode->setDescription($promotionDes);
            $pcode->setType($type, $value);
            $pcode->setCondition($applyConditionType, $applyConditionValue);
            $pcode->setExpireType($expireType, $expireValue);
            $pcode->setSingleUsePerUser($singleUsePerUser);


            if ($pcode->setCode($promotionCode)) {
                $mdl_promotionCode->addPromotionCode($pcode);
                $this->form_response(200, '添加成功', HTTP_ROOT_WWW . 'company/promotion_code_manage');
            } else {
                $this->form_response_msg($promotionCode . ' 已经存在，不能重复添加');
            }
        }else{
            $couponList = $mdl_coupons->getCouponList($this->loginUser['id']);
            $this->setData($couponList, 'couponList');
            
            $this->setData('index_publish', 'menu');
            $this->setData('promotion_code_manage', 'submenu');

            $this->setData('编辑折扣吗 - ' . '产品 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->display('company/promotion_code_add');
        }

    }

    /**
     * 商家推荐他人的产品，放在自己的店铺中销售 的管理页面。
     */
    function referral_product_program_action(){
        $search = get2('search');

		
		$shop=get2('shop');
        $this->setData( $shop, 'shop' );
        $preview= $this->loadModel('coupons')->get($search);
        $this->setData($preview,'preview');


        $add = get2('add');
        $mdl=$this->loadModel('referral_product_program');
        if($add){
            $data['userId']=$this->loginUser['id'];
            $data['productId'] = $add;
            if(!$mdl->getByWhere($data)){
                $data['createTime']=time();
                $mdl->insert($data);
            }
        }


        $delete= get2('delete');
        if($delete){
            $where['userId']=$this->loginUser['id'];
            $where['productId']=$delete;
            $mdl->deleteByWhere($where);
        }

        $list = $mdl->getProductList($this->loginUser['id']);
        $this->setData($list,'data');

        $this->setData('my_mingxingshop', 'menu');
        $this->setData('referral_product_program1', 'submenu');

        $this->setData('分类及置顶设置 - ' . $this->site['pageTitle'], 'pageTitle');

        $this->display_pc_mobile('company/referral_product_program','mobile/company/referral_product_program');
    }
	
	public function referral_product_ajax_add_item_action()
    {
        if(is_post()){
			
			$id = trim(post('id'));
			$promote = trim(post('promote'));
			
			$mdl=$this->loadModel('referral_product_program');
			
			if($promote) {
    			if($id){
    				$data['userId']=$this->loginUser['id'];
    				$data['productId'] = $id;
    				if(!$mdl->getByWhere($data)){
    					$data['createTime']=time();
    					$mdl->insert($data);
    				}
    			}
			}else{
				$mdl->delete($data);
			}
            echo 'success';
        }
    }
	
	
    public function mark_promotion_item_action()
    {
        if(is_post()){
             $mdl=$this->loadModel('referral_product_program');

             $data['promote']=trim(post('promote'));

             $where['productId']=trim(post('id'));
             $where['userId']=$this->loginUser['id'];

             $mdl->updateByWhere($data,$where);

             echo 'success';
        }
    }

    public function referral_product_category_action()
    {   
        $productId= trim(get2('productId'));

        if(is_post()){

            if(!$productId)$this->form_response_msg('产品ID缺失');

            $cCategoryId=",".join(',',post('cCategoryId')).",";

            $mdl=$this->loadModel('referral_product_program');

            $where['userId']=$this->loginUser['id'];
            $where['productId']=$productId;
            $data['cCategoryId']=$cCategoryId;
            $mdl->updateByWhere($data,$where);

            $this->form_response(200,'保存成功',HTTP_ROOT_WWW.'company/referral_product_program');

        }else{
            $preview= $this->loadModel('coupons')->get($productId);
            $this->setData($preview,'preview');

            $where['userId']=$this->loginUser['id'];
            $where['productId']=$productId;
            $this->setData($this->loadModel('referral_product_program')->getByWhere($where)); 

            $mdl = $this->loadModel('customizableCategory');
            $mdl->setUserId($this->loginUser['id']);
            $list = $mdl->getTopLevelItemList();
            foreach ($list as $key => $value) {
                $list[$key]['hasChild'] = $mdl->hasChild($value['id']);
            }
            $this->setData($list, 'tr_list');
            $table_tr = $this->fetch('customizable_category/table_tr_show');
            $this->setData($table_tr, 'table_tr');

            $this->setData($this->parseUrl(), 'postUrl');

            $this->setData('my_mingxingshop', 'menu');
            $this->setData('referral_product_program1', 'submenu');

            $this->setData('推荐商城产品自定义分类 - ' . ' - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
      		
			$this->display_pc_mobile('company/referral_product_category','mobile/company/referral_product_category');
        }
       
    }


    function help_action()
    {
        $id = (int)get2('id');
        $mdl_info = $this->loadModel('info');
        $info = null;

        $info = $mdl_info->get($id);
        if (!$info) $this->sheader(null, '文章不存在');

        $previousinfo = $mdl_info->getPreviousOfSameClass($id);
        $nextinfo = $mdl_info->getNextOfSameClass($id);

        $previousLink = ($previousinfo['id']) ? $this->parseUrl()->set('id', $previousinfo['id']) : 'javascript:void(0)';
        $nextLink = ($nextinfo['id']) ? $this->parseUrl()->set('id', $nextinfo['id']) : 'javascript:void(0)';

        $this->setData($previousLink, 'previousLink');
        $this->setData($nextLink, 'nextLink');

        $this->setData($info, 'info');
        
        $this->setData('帮助中心 - 信息中心 - ' . $this->site['pageTitle'], 'pageTitle');
        $this->display( 'company/help' );
    }

    function bulkedit_action()
    {   
        //editableFieldType
        $TEXT='text';
        $RICHTEXT='richtext';
        $INPUT='inputtext';
        $NUMBER='number';


        if (is_post()) {

            if (post('selectedItem') && post('dataField') && post('updateValue')) {
                $where[] = "id in (" . join(post('selectedItem'), ',') . ")";
                $where['createUserId'] = $this->loginUser['id'];
                $data[post('dataField')] = post('updateValue');
                if ($this->db->update($data, 'cc_coupons', $where)) {
                    $this->form_response_msg('修改成功');
                } else {
                    $this->form_response_msg('修改出错，请稍后再试');
                }
            } else {
                $this->form_response_msg('信息不全，无法修改');
            }
        } else {
            $editableField = array();

            $editableField[] =array('field' =>'coupon_summery_description',
                                    'title'=>'副标题',
                                    'type'=> $RICHTEXT );
            $editableField[] =array('field' =>'highlight',
                                    'title'=>'亮点',
                                    'type'=> $RICHTEXT ); 
            $editableField[] =array('field' =>'content',
                                    'title'=>'详情',
                                    'type'=> $RICHTEXT ); 
            $editableField[] =array('field' =>'finePrint',
                                    'title'=>'温馨提示',
                                    'type'=> $RICHTEXT ); 
            $editableField[] =array('field' =>'redeemProcedure',
                                    'title'=>'如何使用服务',
                                    'type'=> $RICHTEXT ); 
            $editableField[] =array('field' =>'refund_policy',
                                    'title'=>'退货政策',
                                    'type'=> $RICHTEXT ); 

            $editableField[] =array('field' =>'flat_rates_to_local_city',
                                    'title'=>'本地统一邮费',
                                    'type'=> $NUMBER ); 


            $list = $this->loadModel('coupons')->getCouponList($this->loginUser['id']);

            $this->setData($list, 'list');
            $this->setData($editableField, 'editableField');

            $this->display('company/bulkedit');
        }
    }

    public function coupon_private_view_setting_action()
    {
        if(is_post()){
            $pass=trim(post('coupon_private_view_pass'));

            if($pass){
                $data['coupon_private_view_pass']=$pass;
            }else{
                $data['coupon_private_view_pass']='';
            } 

            $this->loadModel('user')->update($data,$this->loginUser['id']);

            $this->form_response_msg('保存成功');

        }else{
            $this->setData('未上线产品的口令访问 - ' . $this->site['pageTitle'], 'pageTitle');
         
			
			$this->setData('coupon_private_view_setting', 'submenu');
            $this->setData('index_publish', 'menu');
			   $this->display('company/coupon_private_view_setting');
        }
    }

    public function sub_coupon_addon_edit_action()
    {   

        $mdl_coupons_sub=$this->loadModel('coupons_sub');

        if (is_post()) {
            $sub_couponid = get2('id');

            $data=$mdl_coupons_sub->get($sub_couponid);

            if(!$data)$this->form_response_msg('子卡ID缺失');
              
            $this->loadModel('coupons_addon')->set($sub_couponid,$_POST,'s');

            $parent_coupon=$this->loadModel('coupons')->get($data['parent_coupon_id']);

            $this->form_response(200,'保存成功，返回产品编辑页面',HTTP_ROOT_WWW."company/coupons_edit?coupon_type=".trim($parent_coupon['bonusType'])."&id=".trim($parent_coupon['id'])."&step=4");

        }else{
            $sub_couponid = get2('id');

            $data=$mdl_coupons_sub->get($sub_couponid);

            if(!$data)$this->sheader(null,'子卡ID缺失');

            $this->setData($data,'sub_coupon');

            $addonData = $this->loadModel('coupons_addon')->getAddonData($sub_couponid,'s');
            $this->setData($addonData, 'addonData');

            $this->setData($this->parseUrl,'postUrl');

            $this->display('company/sub_coupon_addon_edit');
        }
    }

    public function broadcastEmail()
    {   
        $system_mailer=$this->loadModel('system_mail');
        $mdl_user = $this->loadModel('user');

        $list=$mdl_user->getListBySql('SELECT DISTINCT email from cc_user');
        //$list[]=array('email'=>'chriswangworking@gmail.com');
        
        $body="<html>";

        $body.="<h1 style='text-align:center'>重磅！Ubonus美食生活近万商品超级1元秒杀！今晚8点开抢！</h1>";
        $body.="<h1 style='text-align:center'><a href='https://cityb2b.com/shoppingday'>点击进入，开始抢购！</a></h1>";
        $body.="<div style='text-align:center'>";
        $body.="<img src='https://cityb2b.com/themes/zh-cn/email/emailad2.jpg'>";
        $body.="<br>";
        $body.="<img src='https://cityb2b.com/themes/zh-cn/email/emailad1.jpg'>";
        $body.="</div>";
        $body.="</html>";

        $system_mailer->title('重磅！Ubonus美食生活近万商品超级1元秒杀！今晚8点开抢！');
        $system_mailer->body($body);
        
        foreach ($list as $l) {
            $system_mailer->to($l['email']);
            $system_mailer->send();
            $system_mailer->clearAddresses();
            echo $l['email']."<br>";
        }

    }
    //ALTER TABLE `cc_user` ADD `banner_pics` TEXT NOT NULL AFTER `pics`;
    public function store_banner_edit_action()
    {   
         if (is_post()) {
            $images=post('images');
            if($images){
                foreach ($images as $key => $value) {
                    if($value=="default/image_upload.jpg")
                        unset($images[$key]);
                    else
                        $images[$key]=trim($value);
                }

                $data['banner_pics'] = serialize($images);
            }else{
                $data['banner_pics'] = '';
            }

            if ($this->loadModel('user')->update($data, $this->loginUser['id'])) {
                $this->form_response_msg('保存成功');
            } else {
                $this->form_response_msg('保存失败，请稍后再试');
            }
        } else {
			$this->setData('store_banner_edit', 'submenu');
            $this->setData('store_setting', 'menu');
            $this->setData('店铺Banner管理 - ' . $this->site['pageTitle'], 'pageTitle');
            $this->display('company/store_banner_edit');
        }
    }

    public function poster_generate_action()
    {   
        $imageprocess = $this->loadModel('imageprocess');
        $templateList = $imageprocess->loadTemplate();
        $templatePath = HTTP_ROOT_WWW . mdl_imageprocess::TEMPLATEPATH;

        $couponList = $this->loadModel('coupons')->getCouponList($this->loginUser['id']);

        if (is_post()) {
            $template = post('selectedTemplate');
            $couponId = post('selectedCoupon');
            $useMainPic = post('usemainpic');
            $useQrcode = post('useqrcode');
            $insertText = post('insertText');

            $insertImages = [];
            if($useMainPic){
                $coupon = $this->loadModel('coupons')->get($couponId);
                $mainPic = DOC_DIR."data/upload/".$coupon['pic'];
                if (!is_file($mainPic)) $this->form_response_msg('产品主图不存在，请先上传产品主图');
                $insertImages[] = ['path'=>$mainPic, 'position'=>Position::TOPLEFT];
            }

            if($useQrcode){
                $shareUrl = HTTP_ROOT_WX."coupon1/".$couponId;
                $qrCode = generateQRCode($shareUrl,'FILE');
                if (!is_file($qrCode)) $this->form_response_msg('二维码生成出错啦，请稍后再试');
                $insertImages[] = ['path'=>$qrCode, 'position'=>Position::BOTTOMRIGHT];
            }

            $url = $imageprocess->generatePoster($template, $insertImages, $insertText, $this->loginUser['id']);
           
            $this->form_response('200','cool', $url);
        } else {
            $this->setData($couponList, 'couponList');
            $this->setData($templatePath, 'templatePath');
            $this->setData($templateList, 'templateList');
            $this->display('company/poster_generate');
        }
    }
	
		function  cancel_pulbish_action() {
		
		 $id=get2('id');
		 $mdl_coupon= $this->loadModel('coupons');
		 $coupon =$mdl_coupon->get($id);
		 if ($thhis->loginUser['id'] != $coupon['createUserId']) {
			 
			$this->form_response(500, '当前用户无法取消'); 
		 }
		 $data =array (
		    'status'=>1
		 
		 );
		 
	
		 if ( $mdl_coupon->update($data,$id)) {
				//	 $this->form_response(200, '保存成功', HTTP_ROOT_WWW . 'freshfood_edit?coupon_type=7&restaurant=1');
				  $this->form_response(200,'修改成功',HTTP_ROOT_WWW .'company/coupons?coupon_type='.$coupon['bonusType']);
				} else {
 $this->form_response(200,'修改成功',HTTP_ROOT_WWW .'company/coupons?coupon_type='.$coupon['bonusType']);
					// $this->form_response(500, '保存失败', HTTP_ROOT_WWW . 'freshfood_edit?coupon_type=7&restaurant=1');
				}
		
		
		
	}
	
	public function update_wj_customer_coupon_subtotal_action() {
		// $this->form_response(600,'ddddd','不能高于');
		if(is_post()){
			
	
			$mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');

			$id = post('id');
			//$id=34323;
			
			$adjust_subtotal_amount = post('adjust_subtotal_amount');
			//$adjust_subtotal_amount =0.1;
			$sql ="select id,order_id,bonus_id,voucher_deal_amount,business_id,customer_buying_quantity,platform_commission_rate,platform_commission_base  from cc_wj_customer_coupon  where id=".$id;
         
        
            $list=$mdl_wj_customer_coupon->getListBySql($sql);
			if( !$list) 
			{	  
		         //判断如果该用户是合法用户 (比如 该记录的商家与登陆的商家相同，或者 与登陆商家的统配管理员账户相同
				
		      
				$this->form_response(600,'未发现产品','未发现产品');
				
				
            }else{
				
				$uni_business_id=$list[0]['business_id'];
				
				$sql1 ="select business_id  from cc_freshfood_disp_centre_suppliers  where suppliers_id =".$uni_business_id;
				$tongpei_busi_rec = $mdl_wj_customer_coupon ->getListBySql($sql1);
				 if($tongpei_busi_rec) {
					 
					 if ( ($tongpei_busi_rec[0]['business_id'] != $this->current_business['id']) && ( $uni_business_id!=$this->current_business['id']) ) {
						///	var_dump();exit;
							$this->form_response(600,'aa无授权','aa无授权'); 
						 
					 }
				 }
				   
				
				$orderid =$list[0]['order_id'];
				$coupon_id =$list[0]['bonus_id'];
			
				
				if(!is_numeric($adjust_subtotal_amount)){
					$this->form_response(600,'请输入数字。','不能高于');
					
				}
	            $items_subtotal_old =  $list[0]['voucher_deal_amount']* $list[0]['customer_buying_quantity'];
				//var_dump($items_subtotal_old );exit;
				if ($adjust_subtotal_amount >$items_subtotal_old) {
					$hint =$list[0]['voucher_deal_amount']* $list[0]['customer_buying_quantity'] .'(价格$'.(string)$list[0]['voucher_deal_amount'].'*数量'.(string)$list[0]['customer_buying_quantity'].')';
					$this->form_response(600,'新的价格不能高于客户之前该产品购买总额：$'.$hint,'不能高于');
				}
				if ($adjust_subtotal_amount <0) {
					
					$this->form_response(600,'调整的价格不能小于0，最低为0','');
				}
				
			}
			
			$mdl_order = $this->loadModel( 'order' );
			$order_record = $mdl_order->getByWhere(array('orderId'=>$orderid));
			
			// 下面操做的商家在非统配中心时，order_rec 里面的business_userid 就是和 uni_business_id 是一个，但是在统配的情况下，就出现了不一致，导致整个退款出现问题，所以，在这个阶段，将 该记录的 商家Id字段替换成
			// 当前操做的记录对应的商家字段 ， 对于非统配商家 没有影响，对于统配商家，则 将统配商家id 改成 对应的实际商家id ,然后保证退款的账户争取。下面这一行记录进行了替换。
			
			$order_record['business_userId']=$uni_business_id;
			//var_dump($order_record['business_userId']);exit;
			if (!$order_record) {
					$this->form_response(500, '未查到订单信息！','');
			}
			// 准备好数据插入到 订单报损表种
			
			$mdl_order_amend = $this->loadModel('order_amend');
			
			$where_order_amend =array(
			
			 'item_buying_id'=>$id
			);
			
			$data_order_amend=array();
			
			if($mdl_order_amend->getByWhere($where_order_amend )) {
				//查找到
				$data_order_amend['new_sub_total'] = $adjust_subtotal_amount;
				
				// 这条语句 后期放到 begin he commit 里面
				$mdl_order_amend->updateByWhere($data_order_amend,$where_order_amend);
			}else{
				//未查找到 ，则新增
				$data_order_amend['item_buying_id'] =$id;
				$data_order_amend['order_id']  =$orderid;
				$data_order_amend['createTime'] =time();
				$data_order_amend['reason_type'] ='0';
				$data_order_amend['old_sub_total'] =$items_subtotal_old;
				$data_order_amend['new_sub_total']  = $adjust_subtotal_amount;
				$data_order_amend['createUserId'] =$uni_business_id;
				
				$mdl_order_amend->insert($data_order_amend);
			}
			
			
			// 查找 order_amend 是否已经存在该 item_id ,如果存在，则建立修改数据，否则创建新增数组
			
			
			
			
			$data=array();

			
			if(isset($adjust_subtotal_amount)) 		$data['adjust_subtotal_amount']=$adjust_subtotal_amount;
			
			try {
				
			
				$mdl_recharge = $this->loadModel( 'recharge' );
			
				
				
				
				
				
				// 开始一个事务 ，如果不成攻就一次性回滚
				$mdl_wj_customer_coupon->begin();	

			


    			//修改 订单 向表 将汇总数量改为填入的数量。
				if(!$mdl_wj_customer_coupon->update($data,$id)) {
						$mdl_wj_customer_coupon->rollback;
						$error_table ='wj_customer_coupon';
						$this->form_response(500, $error_table,'');
				}
				
				$sql ="select * from cc_wj_customer_coupon  where order_id=".$orderid;//. " and business_id=".$this->loginUser['id'];
				$wj_customer_coupon =$mdl_wj_customer_coupon->getListBySql($sql);
				
				
				//以下参数是整个计算的依据 
				$new_items_total_money =0;  //改变后该订单的货品总值
				$old_total_amount_this_order =0;  //订单之前的货品总值
				$old_commission_total =0;   // 改变之前commssion 总值
				$new_commsision_total =0;   //改变之后该订单总值
				$current_items_amount_change =$items_subtotal_old-$adjust_subtotal_amount; //当前销售记录 的金额变化
				
				foreach ($wj_customer_coupon as $key => $value) {	
				  if ($id ==$wj_customer_coupon[ $key]['id']) {
					$new_items_total_money += $adjust_subtotal_amount;
				  
                    if($value['voucher_deal_amount']>0) { //对于免费产品 不计算
						$new_commsision_total +=$adjust_subtotal_amount*$value['platform_commission_rate'] + ($adjust_subtotal_amount/$value['voucher_deal_amount'])*$value['platform_commission_base'];
				    }
				 }else{
					$new_items_total_money += $wj_customer_coupon[ $key]['adjust_subtotal_amount'];
				     if($value['voucher_deal_amount']>0) { //对于免费产品 不计算
						$new_commsision_total +=$value['adjust_subtotal_amount']*$value['platform_commission_rate'] + ($value['adjust_subtotal_amount']/$value['voucher_deal_amount'])*$value['platform_commission_base'];
                     }
				  }
				 //  var_dump($new_items_total_money);
				  $old_commission_total +=  ($value['voucher_deal_amount']*$value['customer_buying_quantity'])*$value['platform_commission_rate'] + $value['customer_buying_quantity']*$value['platform_commission_base'];
				  
				  $old_total_amount_this_order +=$value['voucher_deal_amount'] *$value['customer_buying_quantity'];
				  
				  
				}
				
				//var_dump($new_items_total_money);exit;
				// 该order 总的价格改变，包括其他订单改变导致的。这个改变做为计算其它的总体参考额度 。
				
				
				//  这一段计算该单产品 销售额变更后 commsiion 的变化数值。
				
				$amount_change =  $old_total_amount_this_order-$new_items_total_money;
				
				
				
				//$amount_change = $list[0]['customer_buying_quantity'] *$list[0]['voucher_deal_amount']-$adjust_subtotal_amount;
				$commission_change_base_rate = $amount_change*$list[0]['platform_commission_rate'];
				$commission_change_base_unit = ($amount_change/$list[0]['voucher_deal_amount'])*$list[0]['platform_commission_base'];
				
				
				$total_commission_change = $old_commission_total-$new_commsision_total;
               // var_dump($total_commission_change);exit;
				
				
				
			
				//var_dump($total_amount_change_this_order);exit;
				//旧的订单总额，不包surcharge。
				$old_items_total_money =$order_record['money']- $order_record['surcharge']-$order_record['delivery_fees'];
				
				//新的订单总额，不包含surcharge 
				$new_items_total_money_withou_sur = $new_items_total_money +$order_record['delivery_fees'] + $order_record['booking_fees'];
				
				
				// 通过计算 比率，计算出新的surcharge ,将来这笔surcharge 要返到用户钱包。
								
				$surcharge_new =$new_items_total_money_withou_sur * ($order_record['surcharge']/($order_record['money']-$order_record['surcharge']));
				//var_dump($surcharge_new);exit;
			 
							
				// 根据新获得的订单，反推新的手续费
				// 将在订单order 的表中 重新计算 新的订单总额，并更新，重新计算surcharge ,并更新到cc_order种,cc_order增加两个字段
			
				$new_items_total_money = $new_items_total_money_withou_sur +$surcharge_new;
				//var_dump($new_items_total_money);exit;
				
				$data_order =array (
				  'money_new'=>$new_items_total_money,
				  'surcharge_new'=>$surcharge_new
				
				);
				$where =array (
				  'orderId'=>$orderid
				);
				//var_dump($surcharge_change_amount);exit;
				
				//更新 order中的 moneynew ,surchargenew , 
				
				if(!$mdl_order->updateByWhere($data_order,$where)){
					
					$mdl_wj_customer_coupon->rollback;
						$error_table ='wj_customer_coupon';
						$this->form_response(500, $error_table,'');
				}
					
				// commsiion 计算为： 按照订单总额的新值，计算变化率， 然后获取该订单commission分配数据，计算出修补commsion数据， 如果找到对应记录则修改，未找到则插入。
				// 目前这里有一个计算需要再精确，就是 money_new 是产品购买计算，并未包含 handing fee  delviery fee surcharge 等，而 money 是order 整个的汇总
				// 因此  money_new 的计算需要重新处理一下。
				
				
				// commission 不会分 handinigling fee ,和 surcharge ,但会分 delivery fee 
				// 因此计算commsiion 的波动比率 的经计算如 -也就是 实际销售额的波动比率
				
				
				//var_dump($commission_rate);exit;
				
				
				
				
				
				///**** 此村非常复杂 ， 当但商家时 这里上面的计算不存在问题，但是当多商家时，(通配),给的数字是该商家的。所以重构
				
				
				$sql ="select * from cc_wj_customer_coupon  where order_id=".$orderid. " and business_id=".$uni_business_id;
				$wj_customer_coupon =$mdl_wj_customer_coupon->getListBySql($sql);
				
				
				//以下参数是整个计算的依据 
				$new_items_total_money =0;  //改变后该订单的货品总值
				$old_total_amount_this_order =0;  //订单之前的货品总值
				$old_commission_total =0;   // 改变之前commssion 总值
				$new_commsision_total =0;   //改变之后该订单总值
				$current_items_amount_change =$items_subtotal_old-$adjust_subtotal_amount; //当前销售记录 的金额变化
				
				foreach ($wj_customer_coupon as $key => $value) {	
				  if ($id ==$wj_customer_coupon[ $key]['id']) {
					$new_items_total_money += $adjust_subtotal_amount;
				  
                    if($value['voucher_deal_amount']>0) { //对于免费产品 不计算
						$new_commsision_total +=$adjust_subtotal_amount*$value['platform_commission_rate'] + ($adjust_subtotal_amount/$value['voucher_deal_amount'])*$value['platform_commission_base'];
				    }
				 }else{
					$new_items_total_money += $wj_customer_coupon[ $key]['adjust_subtotal_amount'];
				     if($value['voucher_deal_amount']>0) { //对于免费产品 不计算
						$new_commsision_total +=$value['adjust_subtotal_amount']*$value['platform_commission_rate'] + ($value['adjust_subtotal_amount']/$value['voucher_deal_amount'])*$value['platform_commission_base'];
                     }
				  }
				
				  $old_commission_total +=  ($value['voucher_deal_amount']*$value['customer_buying_quantity'])*$value['platform_commission_rate'] + $value['customer_buying_quantity']*$value['platform_commission_base'];
				  
				  $old_total_amount_this_order +=$value['voucher_deal_amount'] *$value['customer_buying_quantity'];
				  
				  
				}
				
			
				// 该order 总的价格改变，包括其他订单改变导致的。这个改变做为计算其它的总体参考额度 。
				
				
				//  这一段计算该单产品 销售额变更后 commsiion 的变化数值。
				
				$amount_change_singlebusiness =  $old_total_amount_this_order-$new_items_total_money;
				// 在多商家统配下，某一个被修改的产品当前商家，的总量变化
				
				
				//$amount_change = $list[0]['customer_buying_quantity'] *$list[0]['voucher_deal_amount']-$adjust_subtotal_amount;
				$commission_change_base_rate = $amount_change_singlebusiness*$list[0]['platform_commission_rate'];
				$commission_change_base_unit = ($amount_change_singlebusiness/$list[0]['voucher_deal_amount'])*$list[0]['platform_commission_base'];
				
				
				$total_commission_change = $old_commission_total-$new_commsision_total;
               // var_dump($total_commission_change);exit;
				
				
				
			
				
				
				
				
				
				
				
				
				// 结束
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				$recharge_record_list = $mdl_recharge->getListBySql("select * from cc_recharge where orderId =".$orderid);
				
			 // var_dump($total_commission_change);exit;

                // 查看当前 借计项是否存在 Ubonus commission
				$this->generate_amand_data($mdl_recharge,$recharge_record_list,'UBONUS_COMMISSION',$total_commission_change,null,null,$uni_business_id);
			
			    // 处理 agent 运营代理商的 commssion ajust 
				
				// 获得运营代理商的记录， 找到 commission 的值， 计算出比率，然后，在总的 commission_change 乘一个百分比，调用处理程序即可
				$rate_percentage=0;
				$record_of_agent_commission = $this->findCertainItemRecharge($recharge_record_list,'UBONUS_PAY_AGENT_COMMISSION','-10001')	;
			    if ($record_of_agent_commission) {
					 $agent_commission_money = (-1)*$record_of_agent_commission['money'];
					 // 找 总commission  
					 $record_of_ubonus_commission = $this->findCertainItemRecharge($recharge_record_list,'UBONUS_COMMISSION','-10001')	;
					 if($record_of_ubonus_commission) {
						 $ubonus_commisison = $record_of_ubonus_commission['money'];
						 $rate_percentage = $agent_commission_money /$ubonus_commisison;
						 //var_dump($rate_percentage);exit;
						 
					 }
				 }
			
			    $agent_commission_change_amount = $total_commission_change * $rate_percentage;
				$this->generate_amand_data($mdl_recharge,$recharge_record_list,'UBONUS_PAY_AGENT_COMMISSION',(-1)*$agent_commission_change_amount);
				
				
				
				//var_dump('p'.$amount_change.'o'.$amount_change_singlebusiness);exit;
				
				//累计balance 变更
				$this->generate_amand_data_transcationbalance($mdl_recharge,$recharge_record_list,$amount_change,$order_record,$amount_change_singlebusiness);
				
				//累计surcharge变更
				$surcharge_change_amount = $order_record['surcharge'] -$surcharge_new;
				//var_dump($surcharge_change_amount);exit;
				// 如果是商家支付 surcharge 下面的程序是对的，如果是用户承担surcharge 那么需要将返回的钱 付给用户的钱包。
				
				//获得该订单是使用那种支付方式下单及该订单的surcharge是由谁来支付
				
				$mdl_user  = $this->loadModel('user');
				
				$payment_type =$order_record['payment'];
				
				
				$current_user =$mdl_user->get($order_record['business_userId']);
				
				//var_dump($payment_type);
				//var_dump($current_user['transactionFeeChargeFrom_royalpay']);
				//var_dump($current_user['transactionFeeChargeFrom_paypal']);
				//var_dump($current_user['transactionFeeChargeFrom_creditcard']);
				//var_dump($order_record['userId']);exit;
				
				if ($payment_type == 'royalpay') {
					
					if($current_user['transactionFeeChargeFrom_royalpay']=='user'){
						if($uni_business_id) {
							
							 	$this->generate_amand_data_TRANSACTION_FEE($mdl_recharge,$recharge_record_list,'TRANSACTION_FEE_PLATFORM_CHARGE',$surcharge_change_amount,$order_record['userId']);	
		
						}else{
								$this->generate_amand_data($mdl_recharge,$recharge_record_list,'TRANSACTION_FEE_PLATFORM_CHARGE',$surcharge_change_amount,$coupon_id,$order_record['userId']);	
		
							
						}
				
					}else if($current_user['transactionFeeChargeFrom_royalpay']=='business'){
						
						$this->generate_amand_data($mdl_recharge,$recharge_record_list,'TRANSACTION_FEE_PLATFORM_CHARGE',$surcharge_change_amount);	
						
					}
					
				}else if ($payment_type == 'paypal') {
					
					if($current_user['transactionFeeChargeFrom_paypal']=='user'){
						
						if($uni_business_id) {
							//var_dump($surcharge_change_amount);exit;
								$this->generate_amand_data_TRANSACTION_FEE($mdl_recharge,$recharge_record_list,'TRANSACTION_FEE_PLATFORM_CHARGE',$surcharge_change_amount,$order_record['userId']);	
			
						}else{
								$this->generate_amand_data($mdl_recharge,$recharge_record_list,'TRANSACTION_FEE_PLATFORM_CHARGE',$surcharge_change_amount,$coupon_id,$order_record['userId']);	
		
							
						}
						
						
						
						
						
						//var_dump($surcharge_change_amount);exit;
					        
				
					}else if($current_user['transactionFeeChargeFrom_paypal']=='business'){
					
						$this->generate_amand_data($mdl_recharge,$recharge_record_list,'TRANSACTION_FEE_PLATFORM_CHARGE',$surcharge_change_amount);	
						
					}
					
					
				}else if($payment_type == 'creditcard') {
					
					if($current_user['transactionFeeChargeFrom_creditcard']=='user'){
					
						if($uni_business_id) {
								$this->generate_amand_data_TRANSACTION_FEE($mdl_recharge,$recharge_record_list,'TRANSACTION_FEE_PLATFORM_CHARGE',$surcharge_change_amount,$order_record['userId']);	
			
						}else{
								$this->generate_amand_data($mdl_recharge,$recharge_record_list,'TRANSACTION_FEE_PLATFORM_CHARGE',$surcharge_change_amount,$coupon_id,$order_record['userId']);	
		
							
						}


					// var_dump('user');exit;
					
					}else if($current_user['transactionFeeChargeFrom_creditcard']=='business'){
						 //var_dump('business');exit;
						$this->generate_amand_data($mdl_recharge,$recharge_record_list,'TRANSACTION_FEE_PLATFORM_CHARGE',$surcharge_change_amount);
						
					}
					
				}else if($payment_type == 'offline') {
					 
				  // var_dump('offline');exit;
						
					//   $this->generate_amand_data($mdl_recharge,$recharge_record_list,'TRANSACTION_FEE_PLATFORM_CHARGE',0,null,$order_record['userId']);	
				
					
				}
							  
				
				
		
					
				
				
				// 计算该产品 到推荐客户中的比率
				
				// 1 ： 在cc_charge中找到改产品， 通过coupon_id/orderid/CUSTOMER_REF_COMMISSION/大于0 / 三个条件  获得 commission 值， 
				// 2 ： 然后 找到改产品的销售额 ， quantity *voucher_deal_amount 
                // 3 : 计算commission比率 （因为有的用户commission 高，有的底，不是一致的。 this_coupon_customer_ref_commission_rate =
                
				// 4 ：  计算销售额变化导致分配到推荐人commission变化数额   change_amount * this_coupon_customer_ref_commission_rate
				
				// 5 :  调用 generrate_amand_data 进行增加或修改，但是，因为在一笔交易中可能有多比单项享受，我们只能对该条检录进行处理。因此需要再增加一个搜索条件
				//       就是 cc-recharge coupon_id/orderid/CUSTOMER_REF_COMMISSION_amend ，而不是之前的  /orderid/CUSTOMER_REF_COMMISSION_amend
				//         两个搜索项， 也就是可能存在多个 commision _ref_customer 退款 ，逐个对应保持只有一条。
						 
				//var_dump ($coupon_id);exit;		
				$current_coupon_customer_ref_record = $this->findCertainItemRecharge($recharge_record_list,'CUSTOMER_REF_COMMISSION',1,$coupon_id);
				if($current_coupon_customer_ref_record ) {
					$commision_cust_ref = $current_coupon_customer_ref_record['money'];
					$coupon_customer_ref_commission_rate = $commision_cust_ref/$items_subtotal_old;
					$cust_ref_commission_change =$current_items_amount_change*$coupon_customer_ref_commission_rate;
					
					
					$this->generate_amand_data($mdl_recharge,$recharge_record_list,'CUSTOMER_REF_COMMISSION',(-1)*$cust_ref_commission_change,$coupon_id);
			
					
					
					//var_dump ($cust_ref_commission_change);exit;	
					
					
					
				}
				
				
				
				
				
				
				
				
				
				//var_dump ($current_coupon_customer_ref_record);exit;			 
 				
				
				
				// 将改动做填入log中， 可以命名为 update amount for order 
				
				//  将客户的钱包增加 （如果计算出来负值是不可能的） ，增加的过程是 ，如果 检查 客户的某个订单的的 order_ajust_recharge项 是否存在，如果存在，那么
				// 直接修改改行， 如果没有发现增加一行即可。
				
				
				



				$mdl_wj_customer_coupon->commit();
				$this->form_response(200,'','');
				
			} catch (Exception $e) {
				$mdl_wj_customer_coupon->rollback();
				$this->form_response(500, $e->getMessage(),'');
			}

		}else{
			//wrong protocol
			
			
		}
		
		
		
	}


  function  generate_amand_data_TRANSACTION_FEE($mdl_recharge,$recharge_record_list,$process_item,$total_transaction_fee,$buyer_id){//最后一个参数为当前商家
  
  			
				
				// 下面处理 -10001 是否存在 surch
				$recharge_current_record =$this->findCertainItemRecharge($recharge_record_list,$process_item,'-10001');
				//var_dump($recharge_current_record);exit;
				if ($recharge_current_record){
					
					
					// 继续判断 是否存在已增补记录，不存在，新增一条，存在，修改数据。
					$recharge_current_record_amend = $this->findCertainItemRecharge($recharge_record_list,$process_item.'_AMEND',$buyer_id);
				// var_dump($total_transaction_fee);exit;

  				 if ($recharge_current_record_amend){
						//var_dump($total_transaction_fee .'ddd'.$buyer_id);exit;
						
						$data_update =array(
						   'money'=>$total_transaction_fee,
						);
												
						if(!$mdl_recharge->update($data_update,$recharge_current_record_amend['id'])){
							
						   $mdl_recharge->rollback;
						   $error_table ='update  recharge of ubonus_commisison_amend -10001 ';
						   $this->form_response(500, $error_table,'');
						}
						
						
					}else{
													
							
						
						
						// 插入一条记录
						$data1 =array(
						   'orderId'=>$recharge_current_record['orderId'],
						   'userId'=>$buyer_id,
						   //'userId'=>'-10001',
						   'money'=>$total_transaction_fee ,
						   'payment'=>$process_item.'_AMEND',
						   'createTime'=>time(),
						   'createIp'=>ip(),
						   'status'=>$recharge_current_record['status'],
						   'paytime'=>$recharge_current_record['paytime'],
						   'txn_id'=>$recharge_current_record['txn_id'],
						   'txn_result'=>$recharge_current_record['txn_result'],
						   'coupon_id'=>$recharge_current_record['coupon_id'],
						   'coupon_name'=>$recharge_current_record['coupon_name'].'-amend',
						   'main_coupon_id'=>$recharge_current_record['main_coupon_id'],
						   'business_userId'=>$recharge_current_record['business_userId'],
						   'wj_customer_coupon_id'=>$recharge_current_record['wj_customer_coupon_id'],
						   'special_rule_id'=>$recharge_current_record['special_rule_id'],
						   'note'=>$recharge_current_record['note'],
						
						);
						
						
						
						if(!$mdl_recharge->insert($data1)){
							
						   $mdl_recharge->rollback;
						   $error_table ='insert recharge of ubonus_commisison_amend_business_user';
						   $this->form_response(500, $error_table,'');
						}
						
						
						
					}
					//$this->form_response(500, 'I find it ','');
					
				}else{
					//$this->form_response(500, 'did not find ubonus_commisison ,it is unnormal','');
					
				}
				
				
				
  
  
  
  }

 
 	function generate_amand_data ($mdl_recharge,$recharge_record_list,$process_item,$total_commission_change,$coupon_id,$buyer_id,$uni_business_id){//最后一个参数为当前商家
					
					
					
				
				// var_dump($buyer_id);
				$recharge_current_record =$this->findCertainItemRecharge($recharge_record_list,$process_item,'-10001',$coupon_id);
				
				if ($recharge_current_record){
					
					// 继续判断 是否存在已增补记录，不存在，新增一条，存在，修改数据。
					$recharge_current_record_amend = $this->findCertainItemRecharge($recharge_record_list,$process_item.'_AMEND',$recharge_current_record['userId'],$coupon_id);
				 	if ($recharge_current_record_amend){
						
						$data_update =array(
						   'money'=>$total_commission_change *(-1),
						);
												
						if(!$mdl_recharge->update($data_update,$recharge_current_record_amend['id'])){
							
						   $mdl_recharge->rollback;
						   $error_table ='update  recharge of ubonus_commisison_amend -10001 ';
						   $this->form_response(500, $error_table,'');
						}
					}else{
						
						
						if($buyer_id && $process_item=='TRANSACTION_BALANCE') {
							$userIDD = $buyer_id;
						//	$total_commission_change=$total_commission_change *(-1);
						}else{
							$userIDD =$recharge_current_record['userId'];
							
						}
						
						// 这里有一个特例 ，就是 转向给购买用户的退款  上面的 TRANSACTION_BALANCE
						
						//检测如果当前用户buyer_id 和 TRANSACTION_BALANCE_amend 存在记录，则更改，否则增加
						
						$find=0;
						if($buyer_id) {
						foreach ($recharge_record_list as $key => $value) {	
							
							//var_dump($value['userId']. ' '.  $buyer_id . 
							if($value['userId'] ==$buyer_id && $value['payment']=='TRANSACTION_BALANCE_AMEND'){
								$find=1;
								
								$ajust_amount =abs($total_commission_change) ;
								// var_dump($ajust_amount. ' ' .$value['id']);
								$data_update =array(
									   'money'=> $ajust_amount
									);
															
									if(!$mdl_recharge->update($data_update,$value['id'])){
										
									   $mdl_recharge->rollback;
									   $error_table ='update  recharge of ubonus_commisison_amend -10001 ';
									   $this->form_response(500, $error_table,'');
									}
							}
							
						}
						}
						
						
						
						if(!$find) {
						
						
						
						
						// 插入一条记录
						$data =array(
						   'orderId'=>$recharge_current_record['orderId'],
						   'userId'=>$userIDD,
						   //'userId'=>'-10001',
						   'money'=>$total_commission_change *(-1),
						   'payment'=>$process_item.'_AMEND',
						   'createTime'=>time(),
						   'createIp'=>ip(),
						   'status'=>$recharge_current_record['status'],
						   'paytime'=>$recharge_current_record['paytime'],
						   'txn_id'=>$recharge_current_record['txn_id'],
						   'txn_result'=>$recharge_current_record['txn_result'],
						   'coupon_id'=>$recharge_current_record['coupon_id'],
						   'coupon_name'=>$recharge_current_record['coupon_name'].'-amend',
						   'main_coupon_id'=>$recharge_current_record['main_coupon_id'],
						   'business_userId'=>$recharge_current_record['business_userId'],
						   'wj_customer_coupon_id'=>$recharge_current_record['wj_customer_coupon_id'],
						   'special_rule_id'=>$recharge_current_record['special_rule_id'],
						   'note'=>$recharge_current_record['note'],
						
						);
						
						if(!$mdl_recharge->insert($data)){
							
						   $mdl_recharge->rollback;
						   $error_table ='insert recharge of ubonus_commisison_amend_business_user';
						   $this->form_response(500, $error_table,'');
						}
						//var_dump($data);exit;
						}
					}
					//$this->form_response(500, 'I find it ','');
					
				}else{
					//$this->form_response(500, 'did not find ubonus_commisison ,it is unnormal','');
					
				}
				
				
				
				// 生成对应端（ubonus对应的） 数据
				
				//准备调试顶顶顶顶顶顶顶顶顶顶顶顶顶顶顶顶顶顶顶  当手续费支付 为商家或用户时的 退回手续费 到哪个用户的程序				
				
				$recharge_current_record =$this->findCertainItemRecharge($recharge_record_list,$process_item,$uni_business_id,$coupon_i);
				
				
				//TRANSACTION_FEE_PLATFORM_CHARGE_AMEND
				
				if ($recharge_current_record){
					
					// 继续判断 是否存在已增补记录，不存在，新增一条，存在，修改数据。
					$recharge_current_record_amend = $this->findCertainItemRecharge($recharge_record_list,$process_item.'_AMEND',$recharge_current_record['userId'],$coupon_id);
				 	if ($recharge_current_record_amend){
						//表明如果该笔修改是 购买用户，并且为balanceajust的话， 要使用正数 （强制） 
						if($buyer_id) {
							
							//var_dump ($recharge_record_list['userId']. ' ' . $buyer_id . ' '. $recharge_current_record['payment']);
						}
						if($recharge_current_record['userId'] ==$buyer_id && $recharge_current_record['payment'] == 'TRANSACTION_BALANCE_AMEND') {
							$data_update =array(
						   'money'=>$total_commission_change *-1,
							);
							
						}else{
							$data_update =array(
						   'money'=>$total_commission_change ,
							);
							
						}
						
												
						if(!$mdl_recharge->update($data_update,$recharge_current_record_amend['id'])){
							
						   $mdl_recharge->rollback;
						   $error_table ='update  recharge of ubonus_commisison_amend  ';
						   $this->form_response(500, $error_table,'');
						}
					}else{
						
						// buyerid 参数如果存在 说明是用户支付了surcharge ,如果退回，需要退回给用户账户
						if($buyer_id && $process_item=='TRANSACTION_FEE_PLATFORM_CHARGE') {
							$userIDD = $buyer_id;
						}else{
							$userIDD =$recharge_current_record['userId'];
							
						}
						
						// 插入一条记录
						$data =array(
						   'orderId'=>$recharge_current_record['orderId'],
						  // 'userId'=>$recharge_current_record['userId'],
						   'userId'=>$userIDD,
						   'money'=>$total_commission_change ,
						   'payment'=>$process_item.'_AMEND',
						   'createTime'=>time(),
						   'createIp'=>ip(),
						   'status'=>$recharge_current_record['status'],
						   'paytime'=>$recharge_current_record['paytime'],
						   'txn_id'=>$recharge_current_record['txn_id'],
						   'txn_result'=>$recharge_current_record['txn_result'],
						   'coupon_id'=>$recharge_current_record['coupon_id'],
						   'coupon_name'=>$recharge_current_record['coupon_name'].'-amend',
						   'main_coupon_id'=>$recharge_current_record['main_coupon_id'],
						   'business_userId'=>$recharge_current_record['business_userId'],
						   'wj_customer_coupon_id'=>$recharge_current_record['wj_customer_coupon_id'],
						   'special_rule_id'=>$recharge_current_record['special_rule_id'],
						   'note'=>$recharge_current_record['note'],
						
						);
						
						if(!$mdl_recharge->insert($data)){
							
						   $mdl_recharge->rollback;
						   $error_table ='insert recharge of ubonus_commisison_amend_business_user';
						   $this->form_response(500, $error_table,'');
						}
						//var_dump($data);exit;
						
					}
					//$this->form_response(500, 'I find it ','');
					
				}else{
					//$this->form_response(500, 'did not find ubonus_commisison ,it is unnormal','');
					
				}
				
				
				
				}
 
 // 因为 transaction_balance 比价特殊，所以单独拿出来处理
 // transactionBalance 一般有两个项 一个是 -100001 ，一个是商家 
 // 如果调整价格后， 需要 将商家的进行减，然后给客户增加 都生成 _amend 项， 如果发现商家的amend ,就更改，否则增加，用户端也同理
 
 // 首先检测是否有 transactionBalance 记录 ，对于钱包和线下支付没有需要额外处理 （混合支付是什么情况需要查一下），第一步处理 使用线上支付的记录
 
 
 function generate_amand_data_transcationbalance($mdl_recharge,$recharge_record_list,$amount_change,$order_record,$amount_change_singlebusiness){
					
				
					
					
				// 找到 是否有transcation-balance 的记录，且 用户号不是 -10001 ，这个是商家 
				
			      $find_business_transcation =0;
     			  foreach ($recharge_record_list as $key => $value) {
                     
					   if($value['payment'] =='TRANSACTION_BALANCE' && $value['userId'] == $order_record['business_userId']) {
						   
						   $find_business_transcation=1;
						   $recharge_current_record =$value;
					   }else{
						   
						   $recharge_current_record1 =$value; 
					   }
					}
					
					
				 // 如果未发现，则退出 （可能是钱包或线下支付，混合支付也有可能需要查
                 if (!$find_business_transcation)
                 {
					//var_dump($order_record);exit;
					//如果没有发现可能这笔付款为线下支付  ，如果为线下支付，正常来讲是付给Ubonus,不付给商家。 因此当退款的时候 ，用户的账上增加，商家的账上增加
				   // return 0;	 
				 }
					
				 
				// 如果发现了，表示找到了商家的transaction_process 记录
								
				// 进行如下处理  
				// 该记录为商家的ID，
				
				// 查询是否有 ——amend 记录 （对应于该 userid ),如果有走 update， 如果没有 走 insert .
				$find_seller_transaction_balance_amend =0;
				
				foreach ($recharge_record_list as $key => $value) {
                     
					   if($value['payment'] =='TRANSACTION_BALANCE_AMEND' && $value['userId'] == $order_record['business_userId']) {
						   
						   $find_seller_transaction_balance_amend=1;
						   $recharge_current_record_amend =$value;
						   
						  
					   }
					}
				
				if($find_seller_transaction_balance_amend ){
					//update seller transcation_blanace_amend record 
					
						$data_update =array(
						   'money'=>$amount_change_singlebusiness *(-1),
						);
												
						if(!$mdl_recharge->update($data_update,$recharge_current_record_amend['id'])){
							
						   $mdl_recharge->rollback;
						   $error_table ='update  recharge of buyer transcation_blanace_amend ';
						   $this->form_response(500, $error_table,'');
						}
					
					
					
				}else{
					//insert a transcation_blanace_amend for seller
						// 插入一条记录
						if(!$recharge_current_record) {
							
							$recharge_current_record=$recharge_current_record1;
						}
						$data =array(
						   'orderId'=>$order_record['orderId'],
						   'userId'=>$order_record['business_userId'],
						   'money'=>$amount_change_singlebusiness *(-1),
						   'payment'=>'TRANSACTION_BALANCE_AMEND',
						   'createTime'=>time(),
						   'createIp'=>ip(),
						   'status'=>$recharge_current_record['status'],
						   'paytime'=>$recharge_current_record['paytime'],
						   'txn_id'=>$recharge_current_record['txn_id'],
						   'txn_result'=>$recharge_current_record['txn_result'],
						   'coupon_id'=>$recharge_current_record['coupon_id'],
						   'coupon_name'=>$recharge_current_record['coupon_name'].'-amend',
						   'main_coupon_id'=>$recharge_current_record['main_coupon_id'],
						   'business_userId'=>$recharge_current_record['business_userId'],
						   'wj_customer_coupon_id'=>$recharge_current_record['wj_customer_coupon_id'],
						   'special_rule_id'=>$recharge_current_record['special_rule_id'],
						   'note'=>$recharge_current_record['note'],
						
						);
						//var_dump($data);exit;
						if(!$mdl_recharge->insert($data)){
							
						   $mdl_recharge->rollback;
						   $error_table ='insert recharge of ubonus transcation_blanace_amend for seller';
						   $this->form_response(500, $error_table,'');
						}
						
					
					
					
				}
				// 找到当前order 的下单用户
				
				$buyer_id =  $order_record['userId'];
				
				// 找是否有对应该用户的 _amend记录如果有则修改，如果没有则增加。
				$find_buyer_transaction_balance_amend =0;
				
					foreach ($recharge_record_list as $key => $value) {
                     
					   if($value['payment'] =='TRANSACTION_BALANCE_AMEND' && $value['userId'] == $buyer_id) {
						   
						   $find_buyer_transaction_balance_amend=1;
						   $recharge_current_record_amend =$value;
						  
					   }
					}
				
				if($find_buyer_transaction_balance_amend ){
					//update seller transcation_blanace_amend record 
						$data_update =array(
						   'money'=>$amount_change ,
						);
												
						if(!$mdl_recharge->update($data_update,$recharge_current_record_amend['id'])){
							
						   $mdl_recharge->rollback;
						   $error_table ='update  recharge of buyer transcation_blanace_amend ';
						   $this->form_response(500, $error_table,'');
						}
					
					
					
				}else{
					//insert a transcation_blanace_amend for seller
						// 插入一条记录
						$data =array(
						   'orderId'=>$recharge_current_record['orderId'],
						   'userId'=>$buyer_id,
						   'money'=>$amount_change,
						   'payment'=>'TRANSACTION_BALANCE_AMEND',
						   'createTime'=>time(),
						   'createIp'=>ip(),
						   'status'=>$recharge_current_record['status'],
						   'paytime'=>$recharge_current_record['paytime'],
						   'txn_id'=>$recharge_current_record['txn_id'],
						   'txn_result'=>$recharge_current_record['txn_result'],
						   'coupon_id'=>$recharge_current_record['coupon_id'],
						   'coupon_name'=>$recharge_current_record['coupon_name'].'-amend',
						   'main_coupon_id'=>$recharge_current_record['main_coupon_id'],
						   'business_userId'=>$recharge_current_record['business_userId'],
						   'wj_customer_coupon_id'=>$recharge_current_record['wj_customer_coupon_id'],
						   'special_rule_id'=>$recharge_current_record['special_rule_id'],
						   'note'=>$recharge_current_record['note'],
						
						);
					
						if(!$mdl_recharge->insert($data)){
							
						   $mdl_recharge->rollback;
						   $error_table ='insert recharge of ubonus transcation_blanace_amend for buyer';
						   $this->form_response(500, $error_table,'');
						}
						
					
					
					
				}
				
			
				
				}
 
 
	//搜索特定借计项	
	function findCertainItemRecharge($recharge_record_list,$balance_process_item,$userId,$coupon_id)	{
			
		  //如果coupon_id 不为空，表示当前查找的是 cust_ref,或者 busi_ref 
		  // 因为它存在多条 cust_ref记录，必须找到对应的那条， （每个单品销售都有一条）
		 
		  if($coupon_id) {
			  
			  	foreach ($recharge_record_list as $key => $value) {	
				 	   // 获得
					   
					  // var_dump($recharge_record_list[$key]['userId']);
					  
					  if ($userId =='-10001') {
					   if(($balance_process_item==trim($recharge_record_list[$key]['payment'])) && $recharge_record_list[$key]['coupon_id']==$coupon_id && $recharge_record_list[$key]['money']<0) {
						  // var_dump(100);exit;
						   return $value;
					   }
					  }else {
						 // 当 查找 非_amend借机项时，当查找推荐有户记录时，只需判断金额是>0 可以找到唯一的一条记录， 该记录描述的是
						 // 某个订单，某个单品销售，唯一一个做为推荐客户的记录，这条推荐的money是大于0的
						 
						 // 如果是查找_amend cust_ref_commission , 即借机项中包含 _AMEND , 那么 ，通过上层不带 _AMEND的查找，可以获得当前记录的 userid ,可以组合
						 //  借机向 "xxxx_amend', coupon_id ,和 user_id ,获得 是否存在该记录，如果存在，前面走更新。 如果是不存在，则进行插入操作。 
						 if(strpos($recharge_record_list[$key]['payment'],'_AMEND')) {
							 	if(($balance_process_item==trim($recharge_record_list[$key]['payment'])) && $recharge_record_list[$key]['coupon_id']==$coupon_id  && $recharge_record_list[$key]['userId']==$userId) {
						  // var_dump(100);exit;
						   return $value;
					   }  
							 
						 }else{
							 
							 	if(($balance_process_item==trim($recharge_record_list[$key]['payment'])) && $recharge_record_list[$key]['coupon_id']==$coupon_id  && $recharge_record_list[$key]['money']>0) {
						  // var_dump(100);exit;
						   return $value;
					   }  
							 
						 }
					
						  
						  
					  }
					  
					
			} 
			  
		  }else{
			   
			  	foreach ($recharge_record_list as $key => $value) {	
					  if ($userId =='-10001') {
					         // 如果 buyer 是有效且 记账类型 为 transcation_blanace_amend ,表示 将检测 返回用户钱包是否已经执行。如果执行则不再执行
						
					
    					 if(($balance_process_item==trim($recharge_record_list[$key]['payment'])) && $userId == $recharge_record_list[$key]['userId']) {
						   return $value;
					   }
					  }else {
						  
										
						if(($balance_process_item==trim($recharge_record_list[$key]['payment'])) && $recharge_record_list[$key]['userId'] ==$userId) {
						   return $value;
					   } else{
						   
					   } 
					  }
			} 
			  
		  }
			return 0;
		}	
		
	public function export_freshfood_summery_action() {
		
		$customer_delivery_date =trim(get2('customer_delivery_date'));
		
		$mdl_wj_customer_coupon= $this->loadModel('wj_customer_coupon');
		
		$business_id =get2('business_id');
		if($business_id) {
			
			
			
		}else{
			
			$business_id =$this->loginUser['id'];
			
		}
		
		$customer_delivery_date =strtotime($customer_delivery_date);
		
		if($customer_delivery_date) {
			$sql ="SELECT wj.`menu_id` , wj.`bonus_title`, sum(wj.`customer_buying_quantity`) as sum  FROM `cc_wj_customer_coupon` wj ,cc_order o
		WHERE  wj.order_Id =o.orderid and  o.logistic_delivery_date =$customer_delivery_date and   wj.`business_id` =$business_id and wj.`coupon_status`='c01' and (o.status =1 or o.accountPay=1)  group by wj.`menu_id`,
		`bonus_title` order by menu_id desc";
		
		//	var_dump($sql);exit;
		}else{
			
			$sql ="SELECT c.`menu_id` , c.`bonus_title`, sum(c.`customer_buying_quantity`) as sum  FROM `cc_wj_customer_coupon` c left join cc_order o on c.order_id =o.orderId 
		WHERE c.`business_id` =".$this->loginUser['id']." and c.`coupon_status`='c01' and (o.status =1 or o.accountPay=1)   group by `menu_id`,
		`bonus_title` order by menu_id desc";
		//var_dump($sql);exit;
		
		}
		
		
		
		$lists =$mdl_wj_customer_coupon->getListBySql($sql);
		//$lists_new =$this->object2array($lists);
		
		$lists_new = array();
		
	   foreach ($lists as $key => $value) {
		$lists_new[$key]['menu_id']="\t{$lists[$key]['menu_id']}";
		$lists_new[$key]['bonus_title']="\t{$lists[$key]['bonus_title']}";
		$lists_new[$key]['sum']=$lists[$key]['sum'];
		}
		
		$file_name =date('Y-m-d',time());
		$this->toExcel($lists_new,array('Menu_id','Item_name','Sum'),'ubonus-kagroo_sum'.$file_name,'php://output');
	}
		

		public function export_csv_fresh_and_restaurant_action() {
		
		$mdl_wj_customer_coupon= $this->loadModel('wj_customer_coupon');
		
		//获得配送日期
		$customer_delivery_date = trim(get2('customer_delivery_date'));
		$business_id = trim(get2('business_id'));
		
		$logistic_delivery_date =strtotime($customer_delivery_date);
		
		//获得统配商家号
		$mdl_freshfood_disp_centre_suppliers=$this->loadModel('freshfood_disp_centre_suppliers');
		
		$sql1 ="select DISTINCT business_id as businessUserId from cc_freshfood_disp_centre_suppliers where suppliers_id =".$this->loginUser['id'];
		
					$dispaitch_business = $mdl_freshfood_disp_centre_suppliers->getListBySql($sql1);
					
					if($dispaitch_business ) { 
					 $dispaitch_business_id =$dispaitch_business[0]['businessUserId'];
					
					}
		if(!$dispaitch_business_id) $dispaitch_business_id=0;	
        if ($customer_delivery_date) {
			
		$sql ="SELECT a.address,a.logistic_truck_No,a.logistic_stop_No,a.logistic_suppliers_info,a.logistic_suppliers_count,a.logistic_delivery_date,a.logistic_sequence_No,a.`phone`, b.menu_id,b.bonus_title,b.customer_buying_quantity,b.voucher_deal_amount,
		(b.customer_buying_quantity *b.voucher_deal_amount) as sub_total,concat(a.`first_name` ,a.`last_name`) as name , 
		a.`address`,a.orderId,a.house_number,a.street,a.city,a.state,a.message_to_business,a.postalcode,a.money  FROM `cc_order` a ,
		cc_wj_customer_coupon b where b.order_id=a.orderId and ( a.`business_userId` =".$this->loginUser['id']."  or   a.`business_userId` =$dispaitch_business_id ) and b.business_id =".$this->loginUser['id'] ." and a.status=1 and  a.`coupon_status` ='c01' and a.logistic_delivery_date = $logistic_delivery_date order by a.phone,a.address,a.orderId";
        //var_dump($sql);exit;
  
			
		}else{
			$sql ="SELECT a.address,a.logistic_truck_No,a.logistic_stop_No,a.logistic_suppliers_info,a.logistic_suppliers_count,a.logistic_delivery_date,a.logistic_sequence_No,a.`phone`, b.menu_id,b.bonus_title,b.customer_buying_quantity,b.voucher_deal_amount,
		(b.customer_buying_quantity *b.voucher_deal_amount) as sub_total,concat(a.`first_name` ,a.`last_name`) as name , 
		a.`address`,a.orderId,a.house_number,a.street,a.city,a.state,a.message_to_business,a.postalcode,a.money  FROM `cc_order` a ,
		cc_wj_customer_coupon b where b.order_id=a.orderId and ( a.`business_userId` =".$this->loginUser['id']."  or   a.`business_userId` =$dispaitch_business_id ) and b.business_id =".$this->loginUser['id'] ."  and (o.status =1 or o.accountPay=1) and  a.`coupon_status` ='c01' order by a.phone,a.address,a.orderId";
       
    
			
		}
		//var_dump($sql);exit;
		
		//$sql="SELECT a.address,a.logistic_truck_No,a.logistic_stop_No,a.logistic_suppliers_info,a.logistic_suppliers_count,a.logistic_delivery_date,a.logistic_sequence_No,a.`phone`, b.menu_id,b.bonus_title,b.customer_buying_quantity,b.voucher_deal_amount, (b.customer_buying_quantity *b.voucher_deal_amount) as sub_total,concat(a.`first_name` ,a.`last_name`) as name , a.`address`,a.orderId,a.house_number,a.street,a.city,a.state,a.message_to_business,a.postalcode,a.money FROM `cc_order` a , cc_wj_customer_coupon b where b.order_id=a.orderId and ( a.`business_userId` =25201 or a.`business_userId` =0 )  and a.`coupon_status` ='c01' and a.logistic_delivery_date = 1597413600 order by a.phone,a.address,a.orderId";
		$lists =$mdl_wj_customer_coupon->getListBySql($sql);
		//$lists_new =$this->object2array($lists);
		
		$lists_new = array();
		
		
		
		$combine=0;
	   foreach ($lists as $key => $value) {
		
		$lists_new[$key]['Group Name']='';
		
	
		   
		if($lists[$key]['money']<($this->loginUser['amount_for_free_delivery']) && $key>0) {
			if( ( $current_change_order_id && $current_change_order_id == $lists[$key]['orderId']) || ($lists[$key]['phone'] ==$lists[$key-1]['phone']) && ($lists[$key]['address']==$lists[$key-1]['address']) && ($lists[$key]['orderId'] <>$lists[$key-1]['orderId']) ) {
				 
			if($current_change_order_id == $lists[$key]['orderId']) {}else{
				
				 $ajust_to_orderid= "\t{$lists[$key-1]['orderId']}";
			}
			

				 $current_change_order_id = $lists[$key]['orderId'];
				 $lists_new[$key]['OrderID']="\t{$ajust_to_orderid}";
				 $combine=1;
				
				 
				 
			}else{
				 $current_change_order_id =0;
			    $combine=0;
				$lists_new[$key]['OrderID']="\t{$lists[$key]['orderId']}";
			}
			
		}else{
			 $current_change_order_id=0;
			$combine=0;
			$lists_new[$key]['OrderID']="\t{$lists[$key]['orderId']}";
			
		}
		$lists_new[$key]['Product code']=(string)$lists[$key]['menu_id'];
		
			
		$old_phone=$lists[$key]['phone'];
		$old_address=$lists[$key]['address'];
				
		$lists_new[$key]['logistic_delivery_date']=date('Y-m-d',$lists[$key]['logistic_delivery_date']);
		$lists_new[$key]['logistic_sequence_No']=$lists[$key]['logistic_sequence_No'];
		$lists_new[$key]['logistic_suppliers_info']=$lists[$key]['logistic_suppliers_info'];
		$lists_new[$key]['logistic_suppliers_count']=$lists[$key]['logistic_suppliers_count'];
		$lists_new[$key]['ProductName']=$lists[$key]['bonus_title'];
		$lists_new[$key]['Quantity']=$lists[$key]['customer_buying_quantity'];
		$lists_new[$key]['Price']=$lists[$key]['voucher_deal_amount'];
		$lists_new[$key]['CustomerName']=$lists[$key]['name'];
		$lists_new[$key]['ContactNumber']="\t{$lists[$key]['phone']}";	
		$lists_new[$key]['Street Address']=$lists[$key]['house_number'].' ' .$lists[$key]['street'];
	    $lists_new[$key]['Suburb']=$lists[$key]['city'];
		$lists_new[$key]['PostCode']=$lists[$key]['postalcode'];
		$lists_new[$key]['Total']=$lists[$key]['customer_buying_quantity']*$lists[$key]['voucher_deal_amount'];
		$lists_new[$key]['logistic_truck_No']=$lists[$key]['logistic_truck_No'];
		$lists_new[$key]['logistic_stop_No']=$lists[$key]['logistic_stop_No'];
		$lists_new[$key]['address']=$lists[$key]['address'];
		
		if($combine ==1) {
			$lists_new[$key]['message']='Combine order with' ."\t{$lists[$key]['orderId']}".$lists[$key]['message_to_business'];
			$combine=0;
		}else{
			$lists_new[$key]['message']=$lists[$key]['message_to_business'];
		}
		
		
		
		//'Phone','Order_id','Menu_id','Item_name','Quantity',
		// 'Price','Customer Name','Street','Suburb','Post Code','Sub_total','message'
		/*
		.$lists[$key]['phone']."','"
		.$lists[$key]['menu_id']."','"
		.$lists[$key]['bonus_title']."','"
		.$lists[$key]['customer_buying_quantity']."','"
		.$lists[$key]['voucher_deal_amount']."','"
		.$lists[$key]['sub_total']."','"
		.$lists[$key]['name']."','"
		.$lists[$key]['address']."','"
		.$lists[$key]['orderId']
		."'";
			*/
		}
		//var_dump($lists_new);exit;
		//$obj->getActiveSheet()->getColumnDimension('Phone')->setAutoSize(false);
		//$obj->getActiveSheet()->getColumnDimension('Phone')->setWidth("400");
		$file_name =date('Y-m-d',time());
		$this->toExcel($lists_new,array('Group Name','OrderID','Product code','logistic_delivery_date','logistic_sequence_No','logistic_suppliers_info','logistic_suppliers_count','ProductName','Quantity','Price','Customer Name','ContactNumber','Street Address','Suburb','PostCode','Total','logistic_truck_No','logistic_stop_No','address','Message'),$this->loginUser['name'].'-'.$file_name,'php://output');
		
		
	}	
	
	
	public function findCatName($business_id,$catName){
		$mdl = $this->loadModel('restaurant_category');
		$where = array(
			'restaurant_id'=>$business_id,
			'category_cn_name'=>$catName
		);
		
		
		if($mdl->getCount($where)){
			
			return 1;
		}
		
		
		return 0;
	}
	
	public function AddCatName($business_id,$catName,$catEnName) {
		
		//获取当前内部分类的最大序号，然后加10形成新的分类序号
		
		$mdl = $this->loadModel('restaurant_category');
		
		$sql = "SELECT *,convert(category_id,signed) as cate FROM `cc_restaurant_category` where restaurant_id =$business_id ORDER BY cate DESC limit 1";
		
		
		$cateList =$mdl->getListBySql($sql);
		//var_dump('ddddd');exit;
		if($cateList ) { 
		   $category_id =$cateList[0]['category_id'] +100;
		
		}else {
		   $category_id ='100';
			
		}
		
		$data =array (
		  'restaurant_id'=>$business_id,
		   'category_id'=>$category_id,
		   'category_sort_id'=>'100',
		   'category_cn_name'=>$catName,
		   'category_en_name'=>$catEnName,
		   'createUserId'=>$business_id
		    
		);
		
		if($mdl->insert($data)){
			
			return 1;
		}else{
			return 0;
		}
			
		
		
	}
	
		//导入fresh的菜单
	public function import_menu_with_add_category_insert_newitem_action() {
		
		header("Content-type: text/html;charset=utf8_general_ci");
		$array_menu_price =post('ProductList');
		$array_menu_price=stripslashes($array_menu_price);

		$array_menu_price = json_decode($array_menu_price,true);

		if($array_menu_price) {
			$result['message']='数据取出成功';
		}else{
			$result['message']='数据未取出';
		}

		$mdl_restaurant_menu= $this->loadModel('restaurant_menu');
		$index=0;	

		$data1=array();
		$data1['visible']=0;
		$where = array(
			'restaurant_id'=>$this->loginUser['id']
		);

		$mdl_restaurant_menu->updateByWhere($data1,$where);



    
 
      //这段将来加一个程序，检查是否出现新的品类，如果出现添加。
	  
	foreach ($array_menu_price as $key => $value) { 
	
	  $catName =trim($value['cat_name']);
	  if(!$catName) continue;
	  
	  if($old_catName !=$catName ) {
		   $isFindCat =$this->findCatName($this->loginUser['id'],$catName);
				if(!$isFindCat) {
			  
			  $success =$this->AddCatName($this->loginUser['id'],$catName,$catEnName);
			  $infomessage .=','.$catName;
		  }
	  }
	 $old_catName=trim($value['cat_name']);
	
	}
	
	
	
	
	
		
	
	
 
   //下载当前freshfood商家的分类信息
	  
	  $mdl_restaurant_category =$this->loadModel('restaurant_category');
	  $where_cate =array(
	    'restaurant_id'=>$this->loginUser['id']
	  );
      $cateList =$mdl_restaurant_category->getList([],$where_cate);
	 // var_dump($cateList);exit;
  
 
        $filepath = date('Y-m');
        $this->file->createdir('data/upload/'.$filepath);
   
		// 下面这段程序不仅有添加功能而且有修改功能

		foreach ($array_menu_price as $key => $value) {
			
			//如果商家产品库中没有code ,则需要根据规则，自动生成一个code 并返回
			// 生成方法是，获得当前最大的code 编号，然后 加10 .
			
			$where = array(
				'restaurant_id'=>$this->loginUser['id'],
				'menu_id'=>substr($value['CODE'],0,18)

			);

			$data=array();
			if($value['CurrentQuantity']==0) {
				$data['visible']=0;
			}else{
				$data['visible']=1;
			}
			
		
			
			
			//根据表格继续调整 ，本次使用为一个亚洲超市， 它有上架/下架的列
			
			if($value['Status']=='上架' || $value['Status']==1) {
				$data['visible']=1;
				if($value['CurrentQuantity']>0) {
					//保持之前的设置
				}else{
	    			$data['qty']=9999;
				}
				
			}else if($value['Status']=='下架' ||   $value['Status']==0) {
				$data['visible']=0;
				$data['qty']=0;
				
			}else{
				//如果表格没有此列，不做处理
				
			}
				$data['qty']=$value['CurrentQuantity'];
				if($data['qty']>0) {
					
					$data['visible']=1;
				}
			// 写入gst 
			
			if($value['gst']) {
				
				$data['include_gst']=1;
				
			}else if ($value['gst']==0) {
				$data['include_gst']=0;
			}else{
				
			}
			
			
			
			$data['price']=$value['NowPrice'];
			$data['menu_en_name']=$value['ProductName2']."(".$value['Units'].")";
			
			$data['unit']=$value['Units'];
			
			
			//$data['menu_cn_name']=$value['CODE'].'-'.$value['ProductName1']."(".$value['ProductName2'].") ".$value['Units'];
			
			$data['menu_cn_name']=$value['ProductName1'];
			$data['menu_desc']=$value['Description'];
			
			$data['barcode_number']=$value['barcode_number'];
			$data['menu_order_id']=$value['sort'];
		
			if($value['ProductName2']){
				$data['menu_cn_name'].="(".$value['ProductName2'].") ";
			}
			
			if($value['Units']){
				$data['menu_cn_name'].="(".$value['Units'].") ";
			}
			
			// 如果有图片信息，生成图片信息，再更新字段信息。
			if($value['ImageURL']){
				
				$rec = $mdl_restaurant_menu->getByWhere($where);
				if($rec['menu_pic']) {
					
					//有图片不做处理
					
				}else{
					
					
					$url=$value['ImageURL'];
			
					
					$pic_arr = $this->gen_image_file_from_barcode_web($url,$filepath);
					
				//	$pic_path_filename=$filepath.'/'.substr($url,strrpos($url, '/', -1)+1);
								
					//$data['menu_pic'] =$pic_path_filename;
					//$this->save_pic($url,$pic_path_filename);
					//$this->cut_image($pic_path_filename,150,150,'cut');
					//$this->cut_image($pic_path_filename,66,66,'fill');
					//$data['menu_pic']=$pic_path_filename;
					
					$data['menu_pic']=$pic_arr[0];
					
					
				}
				
				
				
				
			}
			
			
			
		
			$arry_no_find=array();
			$index =0;
			
			
			
			
			if($mdl_restaurant_menu->getByWhere($where)){ // 如果找到这个产品编号
				
				//$arr_no_find [index] =$value['CODE'].'/'.$value['ProductName1'].'/'.$value['ProductName2'].'/'.$value['Units'].'/'.$value['NowPrice'].'/'.$value['CurrentQuantity'].'/';
				$index ++;
				if(!$mdl_restaurant_menu->updateByWhere($data,$where)){
					
					
					$result['result']='修改成功';
				}  else{
					$result['result']='修改失败';
					
				}
			
			}else{ //未找到则添加
				
				// 获得到分类编号
				foreach ($cateList as $key1 => $value1) { 
				   if(trim($value['cat_name']) == trim($value1['category_cn_name'])) {  //如果获取到相同的分类中文名称
					   $data['restaurant_category_id'] = $value1['id'];
					   continue;
				   }
				}
				
				// 如果未发现分类号，则创建一个分类名称，并返回分类id
				
				
				/*   创建结束 */
				
				
				
				$data['restaurant_id'] =$this->loginUser['id'];
				$data['menu_id'] =substr($value['CODE'],0,18);
				$data['createUserId'] =$this->loginUser['id'];
				//$data['menu_pic'] ='';
			
				if( $mdl_restaurant_menu ->insert($data)) {
					$result['result']='插入成功';
					
				}else{
					$result['result']='插入失败';
					
				}
			}

			if ( $arr_no_find) {
				$result['result']=json_encode($arr_no_find);
			}else{
				$result['result']='数据全部处理完成';
			}  
		} 
		echo json_encode($result);
	}
	
	//导入fresh的菜单
	public function send_import_xls_action() {
		
		header("Content-type: text/html;charset=utf8_general_ci");
		$array_menu_price =post('ProductList');
		$array_menu_price=stripslashes($array_menu_price);

		$array_menu_price = json_decode($array_menu_price,true);

		if($array_menu_price) {
			$result['message']='数据取出成功';
		}else{
			$result['message']='数据未取出';
		}

		$mdl_restaurant_menu= $this->loadModel('restaurant_menu');
		$index=0;	

		$data1=array();
		$data1['visible']=0;
		$where = array(
			'restaurant_id'=>$this->loginUser['id']
		);

		$mdl_restaurant_menu->updateByWhere($data1,$where);




       









		foreach ($array_menu_price as $key => $value) {
			$where = array(
				'restaurant_id'=>$this->loginUser['id'],
				'menu_id'=>$value['CODE']

			);

			$data=array();
			if($value['CurrentQuantity']==0) {
				$data['visible']=0;
			}else{
				$data['visible']=1;
			}
			$data['price']=$value['NowPrice'];
			$data['menu_en_name']=$value['ProductName2']."(".$value['Units'].")";
			$data['qty']=$value['CurrentQuantity'];
			$data['unit']=$value['Units'];
			$data['menu_cn_name']=$value['CODE'].'-'.$value['ProductName1']."(".$value['ProductName2'].") ".$value['Units'];

			$arry_no_find=array();
			$index =0;
			if(!$mdl_restaurant_menu->getByWhere($where)){
				$arr_no_find [index] =$value['CODE'].'/'.$value['ProductName1'].'/'.$value['ProductName2'].'/'.$value['Units'].'/'.$value['NowPrice'].'/'.$value['CurrentQuantity'].'/';
				$index ++;

			}  
			if(!$mdl_restaurant_menu->updateByWhere($data,$where)){

			}  

			if ( $arr_no_find) {
				$result['result']=json_encode($arr_no_find);
			}else{
				$result['result']='数据全部处理完成';
			}  
		} 
		echo json_encode($result);
	}
	
		//导入fresh的菜单
	public function set_postcodes_xls_action() {
		
		
		
		
		header("Content-type: text/html;charset=utf8_general_ci");
		$array_menu_price =post('Postcodes');
		$array_menu_price=stripslashes($array_menu_price);

		$array_menu_price = json_decode($array_menu_price,true);

		if($array_menu_price) {
			
			$result['message']='数据取出成功';
		}else{
			$result['message']='数据未取出';
			
		}
		
		$mdl_local_delivery_postcodes= $this->loadModel('local_delivery_postcodes');
		$transfer_success =1;
		$mdl_local_delivery_postcodes->begin();
		
           //清除之前的postcode数据
		$where = array(
			'business_userId'=>$this->loginUser['id']
		);
		
		$mdl_local_delivery_postcodes->deleteByWhere($where);
		
           //分别写入每行的新postcode数据
		
		$data=array();
		foreach ($array_menu_price as $key => $value) {
			$data['business_userId']=$this->loginUser['id'];
			$data['postcode']=$value['postcode'];
			$data['delivery_fees']=$value['delivery_fees'];
			$data['is_avaliable']=$value['is_avaliable'];
			
			if(!$mdl_local_delivery_postcodes->insert($data)){
				$transfer_success =0;
			}  
		}
		
		if ($transfer_success) {
			$mdl_local_delivery_postcodes->commit();
			$result['message']='数据处理成功';
		} else {
			$mdl_local_delivery_postcodes->rollback();
			$result['message']='数据处理时出错';
		}
		echo json_encode($result);
		
	}

	public function generate_sequence_number_action(){
		$this->loadModel('freshfood_disp_suppliers_schedule');

	//	if (!in_array($this->loginUser['id'], DispCenter::getDispCenterList())) {
		//	$this->sheader(null,'您无权限访问该页面');
	//	}
		
		$disp = get2('disp');
		$customer_delivery_date = get2('customer_delivery_date');
		
		
    	require_once( DOC_DIR.'static/OptimoRoute.php');
    	$opRoute = new OptimoRoute($this->loginUser['id']);
    		
    	$date = get2('date');
    	$this->setData($date,'date');
    	
    	if (strtotime($date)) {
    		switch (get2('task')) {
    			case 'generate_logistic_sequence':
	    			$opRoute->generateLogisticSequence($date);
	    			$this->sheader(HTTP_ROOT_WWW . 'company/generate_sequence_number?date='.$date);
	    			break;
	    		

	    	}

	    	$orders = $opRoute->getOrderOnDeliverDate($date);
    		$this->setData($orders,'orders');
    	}


     	   
	   

    	$availableDates = $this->loadModel('freshfood_logistic_customers')->getAvaliableDateOfThisLogisiticCompany($this->loginUser['id']);
    	$availableDates = array_map(function($d){
    		return date('Y-m-d',$d['logistic_delivery_date']);
    	}, $availableDates);
    	$this->setData($availableDates, 'availableDates');

    	$this->setData('OptimoRoute 控制面板 - ' . $this->site['pageTitle'], 'pageTitle');
		$this->setData('dispatching_center', 'menu');
        $this->setData('generate_sequence_number', 'submenu');
		
		$this->display('company/generate_sequence_number');
		
		
	}
	
		public function print_label_admin_action(){
		//$this->loadModel('freshfood_disp_suppliers_schedule');
		//if (!in_array($this->loginUser['id'], DispCenter::getDispCenterList())) {
		//	$this->sheader(null,'您无权限访问该页面');
		//}
		
		$disp = get2('disp');
		$customer_delivery_date = get2('customer_delivery_date');
		
		
    	require_once( DOC_DIR.'static/OptimoRoute.php');
    	$opRoute = new OptimoRoute($this->current_business['id']);
    		
    	$date = get2('date');
    	$this->setData($date,'date');
    	
    	if (strtotime($date)) {
    		switch (get2('task')) {
    			case 'generate_logistic_sequence':
	    			$opRoute->generateLogisticSequence($date);
	    			$this->sheader(HTTP_ROOT_WWW . 'company/print_label_admin?date='.$date);
	    			break;
	    		

	    	}

	    	$orders = $opRoute->getOrderOnDeliverDate($date);
            foreach ($orders as $key => $value) {
                $orders[$key]['name'] =$this->getCustomerName($value);

            }

    		$this->setData($orders,'orders');
    	}
       
        //$availableDates =$this->loadModel("order")->getListofAvailableDates($this->loginUser['id']);
        $availableDates = $this->loadModel('freshfood_logistic_customers')->getAvaliableDateOfThisLogisiticCompany($this->current_business['id']);
		$availableDates = array_map(function($d){
    		return date('Y-m-d',$d['logistic_delivery_date']);
    	}, $availableDates);
    	
	 	$this->setData($availableDates, 'availableDates');

    	$this->setData('OptimoRoute panel - ' . $this->site['pageTitle'], 'pageTitle');
		$this->setData('dispatching_center', 'menu');
        $this->setData('print_label_admin', 'submenu');
		
		$this->display('company/print_label_admin');
		
		
	}

	public function oproute_action(){

        $this->loadModel('freshfood_disp_suppliers_schedule');
        $mdl_user_account_info	= $this->loadModel('user_account_info');
        $accountInfo = $mdl_user_account_info->getByWhere(array('userid'=>$this->current_business['id']));
	/*	if (!in_array($this->current_business['id'], DispCenter::getDispCenterList())) {
			$this->sheader(null,'您无权限访问该页面');
		} */ 
		
		$disp = get2('disp');
		$customer_delivery_date = get2('customer_delivery_date');
		$logistic_truck_No = get2('logistic_truck_No');
		
    	require_once( DOC_DIR.'static/OptimoRoute.php');
    	$opRoute = new OptimoRoute($this->current_business['id'], $accountInfo['op_route_key']);
    		
    	$date = get2('date');
    	$this->setData($date,'date');
    	
    	if (strtotime($date)) {
    		switch (get2('task')) {
    			case 'generate_logistic_sequence':
	    			$opRoute->generateLogisticSequence($date);
	    			$this->sheader(HTTP_ROOT_WWW . 'company/oproute?date='.$date);
	    			break;
	    		case 'syncup':
	    			//Step1
	    			try {
	    				$opRoute->syncOrderOnDate($date);
	    			} catch (Exception $e) {
	    				$this->sheader(null,$e->getMessage());
	    			}
	    			
	    			$this->sheader(HTTP_ROOT_WWW . 'company/oproute?date='.$date);
	    			break;
	    		case 'syncdown':
	    			$opRoute->syncRoutesDownOnDeliverDate($date);
					
					if(!$disp) {
						$this->sheader(HTTP_ROOT_WWW . 'company/oproute?date='.$date);
					}else{
						$this->sheader(HTTP_ROOT_WWW . 'company/customer_orders_logistic_query?logistic_truck_No='.$logistic_truck_No.'&customer_delivery_date='.$date);
					}
	    			
	    			break;
	    		case 'notifyuser':
	    			$mdl_system_notification_center = $this->loadModel('system_notification_center');
	    			foreach ($opRoute->getOrderOnDeliverDate($date) as $o) {
	    				$mdl_system_notification_center->notify(SystemNotification::BusinessDelivery, $o['orderId']);
	    			}
	    			$this->sheader(HTTP_ROOT_WWW . 'company/oproute?date='.$date);
	    			break;
	    		case 'approveall':
	    			foreach ($opRoute->getOrderOnDeliverDate($date) as $o) {
	    				$this->_customer_coupon_approving($o['orderId']);
	    			}
	    			$this->sheader(HTTP_ROOT_WWW . 'company/oproute?date='.$date);
	    			break;
	    		case 'freshx_import':
	    			$mdl_order = $this->loadModel('order');
	    			$opRoute->generateLogisticSequence($date);
	    			//25201 下217005 的freshx订单同步
	    			require_once( DOC_DIR.'static/FreshXApi.php');
					$fx = new FreshXApi();
					$fx->login();

	    			foreach ($opRoute->getOrderOnDeliverDate($date) as $order) {
	    				if ($order['address'] == '') {
							continue; //订单地址为空不能上传
						}

						if ($order['freshx_order_id'] != 0) {
							continue;
						}

						$addr = $order['house_number'] . ' ' . $order['street'];
						if ($addr === ' ') {
							$addr = $order['address'];
						}

	    				$data = [
							"reference_number" => $order['logistic_sequence_No'],
							"delivery_note" => $order['logistic_suppliers_info'],
							"delivery_date" => $date,
							"customer" => [
								"customer_name" =>"LSN-" . $order['logistic_sequence_No'] ." ". $order['first_name'] . " " . $order['last_name'],
								"contact_number" => $order['phone'],
								"address" => $addr,
								"suburb" => $order['city'],
								"postcode" => $order['postalcode'],
								"state" => $order['state']
							],
							"items" => []
						];
						$items = $mdl_order->getListBySql('SELECT menu_id, customer_buying_quantity, voucher_original_amount from cc_wj_customer_coupon where order_id=' . $order['orderId'] . ' and business_id = 217005' );

						if (count($items)>0) {
							foreach ($items as $item) {
								$data['items'][] = [
									"product_id" => (int)$item['menu_id'],
									"quantity" => (int)$item['customer_buying_quantity'],
									"price" => (float)$item['voucher_original_amount']
								];
							}
						} else {
							$data['items'][] = [
								"product_id" => 1090,
								"quantity" => 1,
								"price" => 0
							];
						}

						$freshxOrderId = $fx->createOrder($data);
						$mdl_order->updateByWhere(
							[
								'freshx_order_id' => $freshxOrderId
							],
							[
								'orderId' => $order['orderId']
							]
						);
	    			}
	    			$this->sheader(HTTP_ROOT_WWW . 'company/oproute?date='.$date);
	    			break;

	    	}

	    	$orders = $opRoute->getOrderOnDeliverDate($date);
			//var_dump($orders);exit;
    		$this->setData($orders,'orders');
    	}

    	//$availableDates = $this->loadModel('order')->getListBySql('SELECT DISTINCT logistic_delivery_date from cc_order where logistic_delivery_date >'.(time()-3600*24*7). ' and business_userId = '.$this->current_business['id']);
    	
		$availableDates = $this->loadModel('freshfood_logistic_customers')->getAvaliableDateOfThisLogisiticCompany($this->current_business['id']);
    	 
//var_dump($availableDates);exit;
		$availableDates = array_map(function($d){
    		return date('Y-m-d',$d['logistic_delivery_date']);
    	}, $availableDates);
    	$this->setData($availableDates, 'availableDates');
		
		
		$this->setData('Logistic_centre', 'menu');
        $this->setData('customer_oproute', 'submenu');
		

    	$this->setData('OptimoRoute 控制面板 - ' . $this->site['pageTitle'], 'pageTitle');
		$this->display('company/opRouteAdmin');
	}
	
	public function get_truck_list_of_deliver_date_ajax_action()
	{
		$datestr = trim(get2('datestr'));//Y-m-d

	//	$date = strtotime($datestr);

        $TuckListOfTheDay =$this->loadModel('truck')->getAllOrdersTruckListwithCount($this->current_business['id'],$datestr);





	   echo json_encode($TuckListOfTheDay);
	}
	
	
	/**
	 *  Ajax update 一个商家的面板信息，物流配送单上的信息。
	 */

	public function update_dispatching_centre_business_info_action()
	{
		if(is_post()){

			$mdl_freshfood_disp_centre_suppliers =$this->loadModel("freshfood_disp_centre_suppliers");

			$id = post('id');


			$idCreateUser = $mdl_freshfood_disp_centre_suppliers->get($id);
			$mdl  = $this->loadModel('authrise_manage_other_business_account');
			$isAuthoriseCustomer =Authorise_Center::getIsCustomerIdIsAuthorised($this->current_business['id'],$idCreateUser['suppliers_id']);

	       if ($idCreateUser['suppliers_id'] != $this->current_business['id']  || $idCreateUser['business_id'] != $this->current_business['id']  ) {
			   	if(!$isAuthoriseCustomer ) $this->form_response(500,'未发现产品','未发现产品');
		   }



			$data=array();

			$suppliers_name = post('suppliers_name');
			if($suppliers_name)$data['suppliers_name']=$suppliers_name;

			$cn_displayName = post('cn_displayName');
			if(isset($cn_displayName))$data['cn_displayName']=$cn_displayName;

			$en_displayName = post('en_displayName');
			if(isset($en_displayName))$data['en_displayName']=$en_displayName;

			
			
			
			

			
			try {
				$mdl_freshfood_disp_centre_suppliers->update($data,$id);
				

				$this->form_response(200,'','');
			} catch (Exception $e) {
				$this->form_response(500, $id,'');
			}

		}else{
			//wrong protocol
		}
	}
	

	public function dispcenter_action()
	{	
	
	    $type=get2('type');
		
		$this->setData($type, 'type');
		$mdl = $this->loadModel('freshfood_disp_suppliers_schedule');

		$loginUserId = $this->current_business['id'];

		$isSuplier = in_array($loginUserId, DispCenter::getSupplierList());
		$isDispCenter = in_array($loginUserId, DispCenter::getDispCenterList());

		$where = ['business_id' => $loginUserId]; 
		$suplierList = loadModel('freshfood_disp_centre_suppliers')->getList([],$where);

		$mdl_user=$this->loadModel('user');
		for ($i=0; $i <count($suplierList) ; $i++) { 
			$suplierList[$i]['name'] = $mdl_user->getBusinessNameById($suplierList[$i]['suppliers_id']);
		}

		$this->setData($isSuplier,'isSuplier');
		$this->setData($isDispCenter, 'isDispCenter');

		$this->setData($suplierList, 'suplierList');

		
			if(get2('freshfood')){
				 $this->setData('open_store', 'menu');
			}else{
				 $this->setData('index_publish', 'menu');
			}
        $this->setData('dispcenter', 'submenu');
		$this->setData('配送中心设置 - ' . $this->site['pageTitle'], 'pageTitle');
		$this->display('company/dispcenter');
	}



		


   	public function custom_delivery_fee_action()
	{	
	
	    
		
		
	  

		$business_id = $this->current_business['id'];
		
		$delivery_desc=$this->get_business_delivery_des ($business_id);



	
	$mdl_custom_freight_rates = $this->loadModel('custom_freight_rates');
	$where = ['business_id' => $business_id]; 
	
	

	$freight_rates_arr = $mdl_custom_freight_rates->getList([],$where,'end_amount1');




			   // 获取页面显示的下一个起始订单金额
		if($freight_rates_arr){	
			
			foreach ($freight_rates_arr as $key => $value) {
				
				if($value['end_amount1']>=9999) {
					
					$freight_rates_arr[$key]['end_amount11'] = '大于$'.(int)$start_amount;
				}else{
					$freight_rates_arr[$key]['end_amount11'] = '$'.(int)$start_amount.'-$'.(int)$value['end_amount1'];
				}
				$start_amount = $value['end_amount1'];
				
			}
			
			$this->setData($start_amount,'start_amount');
			
		}else{
			
		   $this->setData($this->current_business['amount_for_minimum_delivery'],'start_amount');
				
		}	
			
		$this->setData($delivery_desc,'delivery_desc');

      
		
        //var_dump($freight_rates_arr);exit;
		
        $this->setData($freight_rates_arr,'freight_fees');
		$this->setData('index_publish', 'menu');
        $this->setData('dispcenter', 'submenu');
		$this->setData('配送中心设置 - ' . $this->site['pageTitle'], 'pageTitle');
		$this->display('company/custom_delivery_fee');
	}


public function custom_delivery_fee_delete_action()
	{	
	
	
	 
	  	$mdl_custom_freight_rates = $this->loadModel('custom_freight_rates');
	    $data = $mdl_custom_freight_rates->get(get2('deleteid'));
	     //var_dump($this->current_business['id']);exit;
		if($this->current_business['id'] == $data['business_id'] ) {
			$mdl_custom_freight_rates->delete(get2('deleteid'));
			$this->sheader(HTTP_ROOT_WWW . 'company/custom_delivery_fee');
		}else{
			
			 $this->form_response_msg('no access');
		
			
		}
		
		
		
		
		

		
	}


public function custom_delivery_fee_add_action()
	{	
		
		
		
		$end_amount1 =trim(post('end_amount1'));
		
		$distance1 =trim(post('distance1'));
		$delivery_fees1 =trim(post('delivery_fees1'));
		$plus_fees_per_km_1 =trim(post('plus_fees_per_km_1'));
		
		$distance2 =trim(post('distance2'));
		$delivery_fees2 =trim(post('delivery_fees2'));
		$plus_fees_per_km_2 =trim(post('plus_fees_per_km_2'));
		
		
		$distance3 =trim(post('distance3'));
		$delivery_fees3 =trim(post('delivery_fees3'));
		$plus_fees_per_km_3 =trim(post('plus_fees_per_km_3'));
		
		$farest_distance =trim(post('farest_distance'));
		
		if($distance2<$distance1 && $distance2) {
			
			 $this->form_response(500,'距离范围2公里数应大于距离范围1公里数！');     
		}
		
		if($distance3<$distance2  && $distance3) {
			
			 $this->form_response(500,'距离范围3公里数应大于距离范围2公里数！');     
		}
		
		if($farest_distance<$distance3 && $farest_distance) {
			
			 $this->form_response(500,'最远距离公里数不应小于距离范围3公里数！');     
		}
		
		
		if($distance1>0) {
			$farest_distance =$distance1;
			
		}
		
		if($distance2>0) {
			$farest_distance =$distance2;
			
		}
		
		if($distance3>0) {
			$farest_distance =$distance3;
			
		}
		

		$mdl_custom_freight_rates = $this->loadModel('custom_freight_rates');

		 $loginUserId = $this->current_business['id'];
		
		 $data = array(
                        'business_id' => $loginUserId,
						'end_amount1' => $end_amount1,
                        'distance1' =>$distance1,
                        'delivery_fees1' => $delivery_fees1,
						 'plus_fees_per_km_1' => $plus_fees_per_km_1,
						'distance2' => $distance2,
                        'delivery_fees2' => $delivery_fees2,
                        'plus_fees_per_km_2' => $plus_fees_per_km_2,
						 'distance3' => $distance3,
						'delivery_fees3' => $delivery_fees3,
                        'plus_fees_per_km_3' => $plus_fees_per_km_3,
						'farest_distance' => $farest_distance
                    );
					

          if($mdl_custom_freight_rates->insert($data)){
               
			  $this->form_response(200,'保存成功',HTTP_ROOT_WWW."company/custom_delivery_fee");   
		  }else{
			  
			 $this->form_response(500,'失败');     
		  }
		
		
		
		
	//	var_dump($end_amount1);exit;
		
		
		
		
		
		
		
		
		
		
		
		
        
       
		
 
	}




	public function dispcenter_add_customer_action()
	{	
		$mdl = $this->loadModel('dispatching_centre_customer_list');

		$loginUserId = $this->loginUser['id'];
		
		

		$suplierList = $mdl->getALLAvaliableCustomerListsForCurrentBusiness($loginUserId,$this->loginUser['business_type_factory_2c']);
       // var_dump($customer_list);exit;
		
		
		//$isSuplier = in_array($loginUserId, DispCenter::getSupplierList());
		//$isDispCenter = in_array($loginUserId, DispCenter::getDispCenterList());
		$isDispCenter = 1;

		//$where = ['business_id' => $loginUserId]; 
		//$suplierList = loadModel('freshfood_disp_centre_suppliers')->getList([],$where);

		$mdl_user=$this->loadModel('user');
		for ($i=0; $i <count($suplierList) ; $i++) { 
			$suplierList[$i]['name'] = $mdl_user->getBusinessNameById($suplierList[$i]['business_id']);
		}

		//$this->setData($isSuplier,'isSuplier');
		$this->setData($isDispCenter, 'isDispCenter');

		$this->setData($suplierList, 'suplierList');
		
   // var_dump($suplierList);exit;
		$this->setData('dispatching_center', 'menu');
        $this->setData('dispcenter_add_customer', 'submenu');
		$this->setData('配送中心设置-添加商家 - ' . $this->site['pageTitle'], 'pageTitle');
		$this->display('company/dispcenter_add_customer');
	}

	public function dispcenter_open_action()
	{	
		$type=get2('type');
		
		$data = [];
		$data['business_id'] = $this->loginUser['id'];
		$data['suppliers_id'] = $this->loginUser['id'];
		$this->loadModel('freshfood_disp_centre_suppliers')->insert($data);

		$data = [];
		$data['centre_business_id'] = $this->loginUser['id'];
		$where = ['business_id' => $this->loginUser['id']];
		$this->loadModel('freshfood_disp_suppliers_schedule')->updateByWhere($data,$where);

		$this->sheader(HTTP_ROOT_WWW . 'company/dispcenter'.'?type='.$type);
	}

	public function dispcenter_delete_action()
	{	
	$type=get2('type');
	
	  // 检查是否有删除该商家的全力
	  	$mdl = $this->loadModel('freshfood_disp_centre_suppliers');
	    $data = $mdl->get(get2('deleteid'));
	
		//var_dump($data['suppliers_id']);exit;
		if(DispCentreSuppliers::getIsCustomerIdIsInDispCentre($this->loginUser['id'],$data['suppliers_id'])){
			
			DispCentreSuppliers::deleteAllScheduleTimeForCertainSuppliers($this->loginUser['id'],$data['suppliers_id']);
			$mdl->delete(get2('deleteid'));
			$this->sheader(HTTP_ROOT_WWW . 'company/dispcenter'.'?type='.$type);
		}else{
			
			 $this->form_response_msg('no access');
		
			
		}
		
		
		
		
		

		
	}

	public function dispcenter_invite_action()
	{	
		$id = post('business_user_id');
		$pwd = post('business_user_password');
		$cn_displayName = post('cn_displayName');
		$en_displayName = post('en_displayName');
		$suppliers_name = post('suppliers_name');

		


	//	$this->form_response_msg('功能暂未开放');

		if ( empty($id) ) $this->form_response_msg('请输入商家ID');
	
		if ( empty($cn_displayName) ) $this->form_response_msg('请输入商家中文名称');
		if ( empty($suppliers_name) ) $this->form_response_msg('请输入面单商家简写');

		$mdl_user = $this->loadModel( 'user' );
		$user = $mdl_user->getUserById( $id );
		if ( !$user ) $this->form_response_msg('无此商家');

		if(strlen($pwd)<=16) {
			$passwordByCustomMd5 = $this->md5( $pwd );
		}else{
			$passwordByCustomMd5 = $pwd;
		}
		if ( $passwordByCustomMd5 != $user['password'] ) $this->form_response_msg('密码错误');

		$data = [];
		$data['business_id'] = $this->loginUser['id'];
		$data['suppliers_id'] = $id;
		$data['cn_displayName'] = $cn_displayName;
		$data['en_displayName'] = $en_displayName;
		$data['suppliers_name'] = $suppliers_name;
		
		$this->loadModel('freshfood_disp_centre_suppliers')->insert($data);

		$data = [];
		$data['centre_business_id'] = $this->loginUser['id'];
		$where = ['business_id' => $id];
		$this->loadModel('freshfood_disp_suppliers_schedule')->updateByWhere($data,$where);

		$this->form_response(200,'添加成功',HTTP_ROOT_WWW.'company/dispcenter');
	}

	public function dispcenter_leave_action()
	{	
		$where = [];
		$where['suppliers_id'] = $this->loginUser['id'];
		$this->loadModel('freshfood_disp_centre_suppliers')->deleteByWhere($where);

		$data = [];
		$data['centre_business_id'] = 0;
		$where = ['business_id' => $this->loginUser['id']];
		$this->loadModel('freshfood_disp_suppliers_schedule')->updateByWhere($data,$where);
		$this->sheader(HTTP_ROOT_WWW . 'company/dispcenter');
	}

	public function dispcenter_schedule_action()
	{	
	
	   //获取当前管理的客户号码
	   	$customer_id =get2('customer_id');
		
		if(!$customer_id) {
		  $customer_id =$this->loginUser['id'];
			
		}
		$this->setData($customer_id,'customer_id');
	
	    $mdl  = $this->loadModel('authrise_manage_other_business_account');
		
		$authrise_manage_other_business_account = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);
    	$this->setData($authrise_manage_other_business_account, 'authrise_manage_other_business_account');
		
		
		
		if($authrise_manage_other_business_account) { //如果该商家可以托管账户
			// 检查接收的托管的商家是否合法
			
		
			
			$isAuthoriseCustomer =Authorise_Center::getIsCustomerIdIsAuthorised($this->loginUser['id'],$customer_id);
	
			//var_dump($isAuthoriseCustomer);exit;
			
	     	if($isAuthoriseCustomer) { //如果是授权的customer  
			
				$mdl = $this->loadModel('freshfood_disp_suppliers_schedule');
				$where = [];
				$where['business_id'] = $customer_id;
				$where['centre_business_id'] = DispCenter::getDispCenterIdOfSupplier($customer_id);
				$list = $mdl->getList(null, $where);

				$id = get2('deleteid');
				if ($id && in_array($id, array_column($list, 'id'))) {
					$mdl->delete($id);
					$list = array_filter($list, function($s) use ($id){
						return $s['id'] !== $id;
					});
				}
			}else{
				
				$this->setData(0,'customer_id');
			}
		}else{
			
			$mdl = $this->loadModel('freshfood_disp_suppliers_schedule');
				$where = [];
				$where['business_id'] = $customer_id;
				$where['centre_business_id'] = DispCenter::getDispCenterIdOfSupplier($this->loginUser['id']);
				$list = $mdl->getList(null, $where);

				$id = get2('deleteid');
				if ($id && in_array($id, array_column($list, 'id'))) {
					$mdl->delete($id);
					$list = array_filter($list, function($s) use ($id){
						return $s['id'] !== $id;
					});
				}
		}
	
		
	
		$this->setData($list[0]['business_name'], 'business_name');
        $this->setData($list[0]['business_name_en'], 'business_name_en');
		$this->setData($list, 'data');
		$this->setData('配送日期设置 - ' . $this->site['pageTitle'], 'pageTitle');
		
		if(get2('freshfood')){
			 $this->setData('open_store', 'menu');
		}else{
			 $this->setData('index_publish', 'menu');
		}
		
        $this->setData('dispcenter_schedule', 'submenu');
		
		$this->display('company/dispcenter_schedule');
	}

	public function dispcenter_schedule_create_action()
	{
		
		 //检查是否输入进来的客户与登陆商家是否有代理关系
		// var_dump($customer_id);exit;
		  $customer_id =post('customer_id');
		  $this->loadModel('authrise_manage_other_business_account');
		  
		  if($customer_id !=$this->loginUser['id']) {
			  
			  if(!Authorise_Center::getIsCustomerIdIsAuthorised($this->loginUser['id'],$customer_id ) ){
				$this->form_response(500,'权限问题',HTTP_ROOT_WWW.'company/dispcenter_schedule');  
			  }
		  }
		
		if (is_post())
			{
				
			$mdl_freshfood_disp_centre_suppliers= $this->loadModel('freshfood_disp_centre_suppliers');
			$where = array (
				  'suppliers_id'=>$customer_id
				);
			$suppliersInfo =$mdl_freshfood_disp_centre_suppliers->getByWhere($where); 
			
			//$suppliersInfo = DispCentreSuppliers::getSupplierNameOnCertainCentreBusinessId($this->loginUser['id'],$customer_id);
			//var_dump($suppliersInfo);exit;	
				
			$business_name = $suppliersInfo['cn_displayName'];
			$business_name_en =  $suppliersInfo['en_displayName'];
			//var_dump($business_name_en);exit;
		
			
			
			$mdl = $this->loadModel('freshfood_disp_suppliers_schedule');
			
			$data = [];
            $data['business_name_en'] = $business_name_en;
			$data['business_name'] = $business_name;
			$data['delivery_date_of_week'] = post('delivery_date_of_week');
			$data['delivery_anytime'] = post('delivery_anytime');
			$data['delivery_morning'] = post('delivery_morning');
			$data['delivery_afternoon'] = post('delivery_afternoon');
			$data['order_start_of_date'] = post('order_start_of_date');
			$data['order_start_of_time'] = post('order_start_of_time_hour') . ":" . post('order_start_of_time_minute');
			$data['order_cut_of_date'] = post('order_cut_of_date');
			$data['order_cut_of_time'] = post('order_cut_of_time_hour'). ":" .  post('order_cut_of_time_minute');

			$data['business_id'] = $customer_id;
			$data['centre_business_id'] = DispCenter::getDispCenterIdOfSupplier($customer_id);

			$mdl->insert($data);
			//将所有的business_name 修改称新的 business_name 
			//$this->setData($customer_id,'customer_id');
			$this->form_response(200,'添加成功',HTTP_ROOT_WWW.'company/dispcenter_schedule?customer_id='.$customer_id);
		}
	}

	public function freshx_price_action()
	{	
		//ALTER TABLE `cc_restaurant_menu` ADD `freshx_price` DECIMAL(8,2) NOT NULL AFTER `price`;
		require_once( DOC_DIR.'static/FreshXApi.php');
		$fx = new FreshXApi();
		$fx->login();
		$list =  $fx->getProductList();

		$this->setData($list,'list');

		$this->setData('index_publish', 'menu');
        $this->setData('freshx_price', 'submenu');
		$this->setData('FreshX 价格更新 - ' . $this->site['pageTitle'], 'pageTitle');
		$this->display('company/freshx_price');
	}

	public function freshx_price_db_update_ajax_action()
	{
		 require_once( DOC_DIR.'static/FreshXApi.php');
		 $fx = new FreshXApi();
		 $fx->login();
		 $list =  $fx->getProductList();

		 $mdl = $this->loadModel('restaurant_menu');
		 $mdl_freshx  = $this->loadModel('freshx_price');
		 //开始执行一个事务，事务处理过程如下 
		 
		 // 1）将所有品类下线。
		 // 2） 从第一个开始， 在系统中查找对应的Item id ，如果能够查询到，则，将该产品上线，并将进价设置为当前进价。
		 // 3） 如果没有查找到，则在 freshx_price 表中插入该数据，并在最后导出。 导出后可以手动将产品录入到系统，再从新更新，直到导出新表的数据为0 ，表示所有的品类都已更新。 
		 
		 $mdl->begin();
		 
		 // 将当前所有品类下线
		 
		 $mdl->updateByWhere(['visible'=>0],['(restaurant_id=217005 or restaurant_id=218639)']);
		 $mdl_freshx->deleteByWhere('1=1');
		 foreach ($list as $item) {
			 
			$curr_rec =$mdl->getByWhere( ['menu_id' =>$item->product_id,'(restaurant_id=217005)']);
			 
			if($curr_rec){
			   $mdl->update(['freshx_price'=>$item->price,'visible'=>1],$curr_rec['id']);	
			
			}else{
				
				$sql = "INSERT INTO cc_freshx_price VALUES ('".$item->product_id ."','".$item->product_name ."','".$item->image_url ."','".$item->unit_name ."','".$item->category_name ."','".$item->price ."','".$item->price_confirmed_date ."')";
				//var_dump($sql);exit;
				$mdl_freshx->db->query($sql);
			}
		 	
			$curr_rec =$mdl->getByWhere( ['menu_id' =>$item->product_id,'(restaurant_id=218639)']);
			 
			if($curr_rec){
			   $mdl->update(['freshx_price'=>$item->price,'visible'=>1],$curr_rec['id']);	
			
			}else{
				
				$sql = "INSERT INTO cc_freshx_price VALUES ('".$item->product_id ."','".$item->product_name ."','".$item->image_url ."','".$item->unit_name ."','".$item->category_name ."','".$item->price ."','".$item->price_confirmed_date ."')";
				//var_dump($sql);exit;
				$mdl_freshx->db->query($sql);
			}
			$mdl->commit();
		 }
		$this->form_response_msg('success');
	}

	public function freshx_price_export_action() {	
		require_once( DOC_DIR.'static/FreshXApi.php');
		$fx = new FreshXApi();
		$fx->login();
		$list =  $fx->getProductList();

		$mdl = $this->loadModel('freshx_price');
		$recordIds = array_column($mdl->getList(['product_id']), 'product_id');

		
		
		//加入导出电子表格 ，告诉采编人员处理相应记录
		 
	

	$new = array_filter($list, function($i) use ($recordIds){
			return in_array($i->product_id, $recordIds);
		});

		

		header('Content-Disposition: attachment; filename="freshx_price_list.csv";');
		$out = fopen('php://output', 'w');
		if (count($new) > 0) {
			fputcsv($out, ['新增产品数据']);
			fputcsv($out, ['ID', 'Name', 'Image', 'Unit', 'Category', 'Price', 'last_update']);
			foreach ($new as $item) {
				fputcsv($out, array_values((array)$item));
			}
		}

		
		fclose($out);
		
		/*
		
		$mdl->db->query("TRUNCATE TABLE cc_freshx_price;");

		$sql = "INSERT INTO cc_freshx_price VALUES ";
		$sql .= implode(',',  array_map(function($item){
			return "(" . implode(",", array_map(function($i){
				return "'".$i."'";
			}, (array)$item)) . ")";
		}, $list));
		$mdl->db->query($sql);

		$new = array_filter($list, function($i) use ($recordIds){
			return !in_array($i->product_id, $recordIds);
		});

		header('Content-Disposition: attachment; filename="freshx_price_list.csv";');
		$out = fopen('php://output', 'w');
		if (count($new) > 0) {
			fputcsv($out, ['新增产品数据']);
			fputcsv($out, ['ID', 'Name', 'Image', 'Unit', 'Category', 'Price', 'last_update']);
			foreach ($new as $item) {
				fputcsv($out, array_values((array)$item));
			}
		}

		fputcsv($out, ['全部产品数据']);
		fputcsv($out, ['ID', 'Name', 'Image', 'Unit', 'Category', 'Price', 'last_update']);
		foreach ($list as $item) {
			fputcsv($out, array_values((array)$item));
		}
		fclose($out); */
	}

	public function freshx_order_update_ajax_action()
	{	
		if ($this->loginUser['id'] != 218639 && $this->loginUser['id'] != 25201) {
			$this->sheader(null,'您无权限访问该页面');
		}

		$freshx_order_id = get2('freshx_order_id');

		require_once( DOC_DIR.'static/FreshXApi.php');
		$fx = new FreshXApi();
		$fx->login();

		$order = $this->loadModel('order')->getByWhere(['freshx_order_id' => $freshx_order_id]);

		$addr = $order['house_number'] . ' ' . $order['street'];
		if ($addr === ' ') {
			$addr = $order['address'];
		}
		$data = [
			"delivery_note" => $order['logistic_suppliers_info'],
			"delivery_date" => date("Y-m-d", $order['logistic_delivery_date']),
			"customer" => [
				"customer_name" => $order['first_name'] . " " . $order['last_name'],
				"contact_number" => $order['phone'],
				"address" => $addr,
				"suburb" => $order['city'],
				"postcode" => $order['postalcode'],
				"state" => $order['state']
			],
			"items" => []
		];
		if ( $this->loginUser['id'] == 25201) {
			//统配中心商家的订单有统配号；
			$data["reference_number"] = $order['logistic_sequence_No'];
			$data["customer"]["customer_name"] = "LSN-" . $order['logistic_sequence_No'] ." ". $order['first_name'] . " " . $order['last_name'];
		}

		$items = $this->loadModel('order')->getListBySql('SELECT menu_id, customer_buying_quantity, voucher_original_amount from cc_wj_customer_coupon where order_id=' . $order['orderId']);
		foreach ($items as $item) {
			$data['items'][] = [
				"product_id" => (int)$item['menu_id'],
				"quantity" => (int)$item['customer_buying_quantity'],
				"price" => (float)$item['voucher_original_amount']
			];
		}

		$result = $fx->updateOrder($freshx_order_id, $data);
	}

	//218639 的 freshx 订单导入 非统配
	public function freshx_order_import_action()
	{	
		if ($this->loginUser['id'] != 218639) {
			$this->sheader(null,'您无权限访问该页面');
		}
		$mdl_order = $this->loadModel('order');

		$date = get2('date');
    	$this->setData($date,'date');

		if ($date) {
			$timestamp = strtotime($date);
			if ($timestamp === false) {
				throw new Exception("dateStr is not recognized", 1);
			}
			$dateTime = new DateTime();
			$dateTime->setTimestamp($timestamp);
			$dateTime->setTime(0,0,0);
			$timestamp = $dateTime->getTimestamp();
			$sql = 'SELECT o.* from cc_order as o where o.logistic_delivery_date =' .$timestamp. ' and o.business_userId = '.$this->loginUser['id'];
			$orders = $mdl_order->getListBySql($sql);
    		$this->setData($orders,'orders');
    		$task = get2('task');
    		if ($task == 'freshx_import') {
    			require_once( DOC_DIR.'static/FreshXApi.php');
				$fx = new FreshXApi();
				$fx->login();
				foreach ($orders as $order) {
					if ($order['address'] == '') {
						continue;
					}

					if ($order['freshx_order_id'] != 0) {
						continue;
					}
					$addr = $order['house_number'] . ' ' . $order['street'];
					if ($addr === ' ') {
						$addr = $order['address'];
					}
					$data = [
						// "reference_number" => $order['orderId'],
						"delivery_note" => $order['logistic_suppliers_info'],
						"delivery_date" => $date,
						"customer" => [
							"customer_name" => $order['first_name'] . " " . $order['last_name'],
							"contact_number" => $order['phone'],
							"address" => $addr,
							"suburb" => $order['city'],
							"postcode" => $order['postalcode'],
							"state" => $order['state']
						],
						"items" => []
					];

					$items = $mdl_order->getListBySql('SELECT menu_id, customer_buying_quantity, voucher_original_amount from cc_wj_customer_coupon where order_id='. $order['orderId']);

					foreach ($items as $item) {
						$data['items'][] = [
							"product_id" => (int)$item['menu_id'],
							"quantity" => (int)$item['customer_buying_quantity'],
							"price" => (float)$item['voucher_original_amount']
						];
					}

					//ALTER TABLE `cc_order` ADD `freshx_order_id` INT NOT NULL AFTER `logistic_suppliers_count`;
					$freshxOrderId = $fx->createOrder($data);
					$mdl_order->updateByWhere(
						[
							'freshx_order_id' => $freshxOrderId
						],
						[
							'orderId' => $order['orderId']
						]
					);
				}
				$this->sheader(HTTP_ROOT_WWW . 'company/freshx_order_import?date='.$date);
    		}
		}

		$availableDates = $mdl_order->getListBySql('SELECT DISTINCT o.logistic_delivery_date from cc_order as o left join cc_wj_customer_coupon as c on o.orderId = c.order_id where o.logistic_delivery_date >'.(time()-3600*24*7). ' and c.business_id = '.$this->loginUser['id']);
    	$availableDates = array_map(function($d){
    		return date('Y-m-d',$d['logistic_delivery_date']);
    	}, $availableDates);
    	$this->setData($availableDates, 'availableDates');

		$this->setData('index_publish', 'menu');
        $this->setData('freshx_order_import', 'submenu');
		$this->setData('FreshX 订单导入 - ' . $this->site['pageTitle'], 'pageTitle');
		$this->display('company/freshx_order_import');
	}

	public function labelprint_action(){
		$date = get2('date');
		$this->setData($date, 'date');
		
		
		$data_resource = get2('data_resource');
		$this->setData($data_resource, 'data_resource');
		
		
		$ref_seq_num = get2('ref_seq_num');
		$this->setData($ref_seq_num, 'ref_seq_num');




		$bid = get2('bid');
		$this->setData($bid, 'bid');

        $mdl_user_account_info	= $this->loadModel('user_account_info');
        $accountInfo = $mdl_user_account_info->getByWhere(array('userid'=>$this->current_business['id']));
		
		//调用生成本商家的相关的物流公司的所有order的sequenceNumber
		require_once( DOC_DIR.'static/OptimoRoute.php');
    	$opRoute = new OptimoRoute($this->current_business['id']);
		$opRoute->generateLogisticSequence($date);
		

    	require_once( DOC_DIR.'static/OptimoRoute.php');
		$opRoute = new OptimoRoute($this->current_business['id'], $accountInfo['op_route_key']);
		$orders = [];
		if ($date && $bid) {
			$orders = $opRoute->getBusinessOrderOnDeliverDate($date,$bid,$data_resource,$ref_seq_num);
		}
	//	var_dump($orders);exit;
		if($ref_seq_num ==1) {  //表示来自Ubonus标准数据源
			usort($orders, function($a, $b){
				return $a['logistic_sequence_No'] - $b['logistic_sequence_No'];
			});
		}else{
			
			usort($orders, function($a, $b){
			return intval(substr($a['logistic_sequence_No_String'], 1))
			-intval(substr($b['logistic_sequence_No_String'], 1));
		 });
		}

		/**
		 * F10 格式的排序
		 */
		// usort($orders, function($a, $b){
		// 	return intval(substr($a['logistic_sequence_No'], 1))
		// 	-intval(substr($b['logistic_sequence_No'], 1));
		// });

		//parsedOrdersJson
		
		
		
			
		$parsedOrders = [];
		
		
		foreach ($orders as $order) {
			$parsed = [];


            
			$parsed['orderId'] = $order['orderId'];

            $name= substr($this->getCustomerName($order),0,25);


                $name=str_replace("'","",$name);
				$order['first_name']=str_replace("'","",$name);
				$order['last_name']='';
				
				$parsed['first_name'] = $order['first_name'];
				$parsed['last_name'] = '';
				//$name =   $order['first_name'];

			
			
			
			
			
			$order['address']=str_replace("'","",$order['address']);
			
			$parsed['address'] = $order['address'];
			
			
			$parsed['phone'] = $order['phone'];
			$order['message_to_business']=str_replace("'","",$order['message_to_business']);
			$parsed['message_to_business'] = $order['message_to_business'];
			$parsed['boxes'] =$order['boxes'];
			$parsed['logistic_truck_No'] = $order['logistic_truck_No'];
            $truckName =$this->loadModel('truck')->getByWhere(array('business_id'=>$order['business_userId'],'truck_no'=>$order['logistic_truck_No']));
            $parsed['truck_name'] = substr($truckName['truck_name'],0,8).'-'.$truckName['plate_number'];

			if($ref_seq_num ==1) { //试用ubonus标准 物流号
				
				$parsed['logistic_sequence_No'] = $order['logistic_sequence_No'];
			}else{  //试用其它物流号
				$parsed['logistic_sequence_No'] = $order['logistic_sequence_No_String'];
				
			}
			//$parsed['logistic_sequence_No'] = $order['logistic_sequence_No'];
			$parsed['logistic_stop_No'] = $order['logistic_stop_No'];
			$parsed['logistic_delivery_date'] = $order['logistic_delivery_date'];
			$parsed['logistic_suppliers_info'] = $order['logistic_suppliers_info'];
            if(!$order['displayName']) {
                $order['displayName'] =$name;
            }
			$parsed['displayName'] = substr($order['displayName'],0,11);
            $parsed['boxesNumber'] = $order['boxesNumber'];

               if( $order['boxesNumber'] >0 &&  $order['boxesNumber'] < $order['boxesNumberSortId']) {
                   $parsed['allprinted'] =1;
                   $parsed['boxesNumberSortId'] = $order['boxesNumber'];
               }else{
                   $parsed['allprinted'] =0;
                   $parsed['boxesNumberSortId'] = $order['boxesNumberSortId'];
               }


			$parsed['redeem_code'] = $order['redeem_code'];

			$payment=$order['payment'];
	        $status=$order['status'];
	       // $status=($status==0)?'未付款':'已付款';
		   $status ='';
	        $customer_delivery_option=$order['customer_delivery_option'];
	        if($customer_delivery_option=='1'){
	            $customer_delivery_option='Delivery';
	        }elseif($customer_delivery_option=='2'){
	            $customer_delivery_option='Pick up';
	        }else{
	            $customer_delivery_option='No Delivery';
	        }
			//$parsed['subtitle'] = $payment."|".$status."|".$customer_delivery_option;
			//$parsed['subtitle'] = $payment."|".$customer_delivery_option."  CustId:".$order['userId']." <br>" .'CustName:<strong  style=\"width: 100%;font-size:25px;font-weight:bolder\" >'. $name."</strong>" ;
            $parsed['subtitle'] = $customer_delivery_option."  CustId:".$order['userId']." <br>" .'CustName:<strong  style=\"width: 80%;font-size:16px;font-weight:bolder\" >'. $name."</strong>" ;


			$parsedOrders[$order['orderId']] = $parsed;
		}
 
		//operators
		$mdl_user =  $this->loadModel('user');
		$operators = $mdl_user->getAllStaffListUponRoles($this->current_business['id'], 0);
		$operators = array_map(function($o) use ($mdl_user){
			return [
				'id' => $o['id'],
				'displayName' => $o['displayName']
			];
		}, $operators);

		$availableDates =$this->loadModel("order")->getListofAvailableDates($this->current_business['id']);

    	
		 
		 
	

        $newsuplierList= $this->loadModel('dispatching_centre_customer_list')->getALLAvaliableCustomerListsForCurrentBusiness($this->current_business['id'],$this->current_business['business_type_factory_2c']);
		
		if(!$newsuplierList) {
			$index=0;
			 $newsuplierList[$index]['dispatching_centre_id']= $this->current_business['id'];
			  $newsuplierList[$index]['dispatching_name']=$this->current_business['displayName'];
			  $newsuplierList[$index]['business_id']= $this->current_business['id'];
			  $newsuplierList[$index]['business_name']= $this->current_business['displayName'];
			  $newsuplierList[$index]['data_source']= 1;
			   $newsuplierList[$index]['ref_seq_num']= 1;
			
		}
		
		
		//var_dump($newsuplierList);exit;
		//selectedSupplierName 
		$selectedSupplierName = 'Please select a business';
		for ($i=0; $i <count($newsuplierList) ; $i++) { 
			if ($newsuplierList[$i]['business_id'] == $bid) {
				$selectedSupplierName = $newsuplierList[$i]['business_name'];
				$selectedSupplierDataSource = $newsuplierList[$i]['data_source'];
				break;
			}
		}


		$this->setData($operators, 'operators');
		$this->setData($availableDates, 'availableDates');
		$this->setData($newsuplierList, 'suplierList');
		$this->setData($selectedSupplierName, 'selectedSupplierName');
		$this->setData($selectedSupplierDataSource, 'selectedSupplierDataSource');
		$this->setData(json_encode($parsedOrders), 'parsedOrdersJson');
       
		$this->setData('Label Print - ' . $this->site['pageTitle'], 'pageTitle');
		$this->display('company/labelprint');
	}
	
	function findCustomerName($userId){
		
		//首先找displayName 
		$user =$this->loadModel('user')->get($userId) ;
		if($user['displayName']) {
			return $user['displayName'];
		} else{
			
			$name = $user['name'];
		}
			
		//之后找 user.name  user_factory.nickname 
		$where =array (
		  'user_id'=>$userId 
		
		);
		$user1 = loadModel('user_factory')->getByWhere($where);
		if($user1) {
			if($user1['nickname']) {
				return $user1['nickname'];
			}else{
				
				return $name;
			}
			
			
		}else{
			
			return $name;
		}
		
		
		
		
		
	}

	public function record_label_print_action() {
        if(is_post()){

            $orderId = post('orderId');
            $boxesNumber =  post('totalCopay');
            $singleOrAll = post('singleOrAll');
            $copysortId =  post('copysortId');

            if($singleOrAll==1) {
                if($copysortId<=$boxesNumber){
                    $copysortId ++;
                }
            }else{
                $copysortId=$boxesNumber;
            }


            $this->loadModel('order_print_log')->addRecord($orderId, $this->loginUser['id']);

            //修改order表 box数量
            $data =array(
                'boxesNumber'=>$boxesNumber,
                'boxesNumberSortId'=>$copysortId
            );
            $where =array('orderId'=>$orderId);
            $this->loadModel('order')->updateByWhere($data,$where);
        }
    }

    public function label_print_log_action() {
        $conditions = [];
        $printDate = get2('print-date');
        if($printDate) {
            $conditions['print_at'] = $printDate;
        }

        $orderId = get2('order-id');
        if($orderId) {
            $conditions['orderId'] = $orderId;
        }

        $userId = get2('user-id');
        if($userId) {
            $conditions['userId'] = $userId;
        }

        $printLogs = $this->loadModel('order_print_log')->getRecordList($this->loginUser['id'], $conditions);

        $staffs = $this->loadModel('redeem_staff')->getStaffList($this->current_business['id']);
        array_push($staffs, [
            'id' => $this->loginUser['id'],
            'name' =>$this->loginUser['name']
        ]);
        $this->setData($staffs, 'staffs');
        $this->setData($printLogs, 'printLogs');
        $this->setData('dispatching_center', 'menu');
        $this->setData('label_print_log', 'submenu');
        $this->setData('Label Print Logs', 'pageTitle');
        $this->display('company/print_label_log');
    }

//get_truck_list_of_deliver_date_ajax_action

    public function export_driver_route_local_action()
    {

        $mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');
        $mdl_order = $this->loadModel('order');
        $mdl_user= $this->loadModel('user');
        $mdl_truck =  $this->loadModel('truck');

        /**
         * Driver List of Current Business
         */

        $customer_delivery_date = trim(get2('customer_delivery_date'));



        $three_days_times = time()-259200;

        $sql_avaliable_date =" SELECT DISTINCT o.logistic_delivery_date from (select * from cc_order where (`business_userId` = ".
            $this->current_business['id'].") or (`business_userId` in (select DISTINCT business_id from cc_freshfood_disp_centre_suppliers where suppliers_id =".$this->current_business['id'].")) ) as o where o.logistic_delivery_date >".$three_days_times." order by logistic_delivery_date ";
        // var_dump($sql_avaliable_date);exit;

        $availableDates = $this->loadModel('freshfood_logistic_customers')->getAvaliableDateOfThisLogisiticCompany($this->current_business['id']);

        // $availableDates = $this->loadModel('order')->getListBySql( $sql_avaliable_date);
        $availableDates = array_map(function($d){
            return date('Y-m-d',$d['logistic_delivery_date']);
        }, $availableDates);
        $this->setData($availableDates, 'availableDates');



        $logistic_truck_No = trim(get2('logistic_truck_No'));


        $this->setData($logistic_truck_No,'logistic_truck_No');


        $TuckListOfTheDay =$this->loadModel('truck')->getAllOrdersTruckListwithCount($this->current_business['id'],$customer_delivery_date);
        $this->setData($TuckListOfTheDay,'TuckListOfTheDay');


        $this->setData($customer_delivery_date,'customer_delivery_date');
        $this->setData($postcode,'postcode');

        $sql = "SELECT f.nickname,o.* ,cust.ori_sum from cc_order as o left join cc_user_factory f on o.userId=f.user_id and o.business_userId = f.factory_id  left join (select order_id,business_id,sum(voucher_deal_amount*customer_buying_quantity) as ori_sum from cc_wj_customer_coupon group by order_id,business_id) cust on o.orderId=cust.order_id and cust.business_id =".$this->current_business['id']." left join cc_wj_user_coupon_activity_log as l on o.orderId=l.orderId and o.coupon_status=l.action_id ";

        $whereStr.=" (business_userId= ".$this->current_business['id']." or  o.orderId in (select DISTINCT c.order_id from cc_wj_customer_coupon c where business_id = ".$this->current_business['id']."))";
        //var_dump($sql);exit;



        $whereStr.= " and (o.coupon_status='c01' or o.coupon_status='b01' )";



        $whereStr.= " and (o.status =1 or o.accountPay=1) ";





        //deleivery date
        if (!empty($customer_delivery_date)) {
            if ($customer_delivery_date != 'all') {
                $whereStr.= " and  DATE_FORMAT(from_unixtime(o.logistic_delivery_date),'%Y-%m-%d') = '$customer_delivery_date' ";
            }else{
                $three_days_times = time()-259200;
                $whereStr.= " and  logistic_delivery_date > $three_days_times";


            }
        }else {
            $three_days_times = time()-259200;
            $whereStr.= " and  logistic_delivery_date > $three_days_times";
        }

        if (!empty($logistic_truck_No)) {

            if ($logistic_truck_No != 'all') {
                $whereStr.= " and  logistic_truck_No = '$logistic_truck_No' ";

            }
        }

        if ($logistic_truck_No =='0' ) {
            $whereStr.= " and  logistic_truck_No =0 ";
            // var_dump($logistic_truck_No);exit;
        }



        $pageSql=$sql . " where " . $whereStr . " order by DATE_FORMAT(from_unixtime(o.logistic_delivery_date),'%Y-%m-%d'),logistic_truck_No,logistic_stop_No";

        // var_dump($pageSql); exit;
        $pageUrl = $this->parseUrl()->set('page');
        $pageSize = 40;
        $maxPage = 10;
        $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);

        $data = $mdl_order->getListBySql($page['outSql']);

        foreach ($data as $key => $value) {
            $data[$key]['name'] =$this->getCustomerName($value);

        }

        $this->setData($page['pageStr'],'pager');

        $this->setData($data,'data');

        $this->setData('Logistic_centre', 'menu');
        $this->setData('export_driver_route_local', 'submenu');

        $this->setData(HTTP_ROOT_WWW.'company/export_driver_route_local', 'searchUrl');

        $this->setData($this->parseUrl(), 'currentUrl');

        $this->setData('Order Logisitic Schedule - ' . $this->site['pageTitle'], 'pageTitle');

        $this->display_pc_mobile('company/export_driver_route_local','company/export_driver_route_local');
    }


    public function export_driver_route_excel_action() {

        $mdl_user_account_info	= $this->loadModel('user_account_info');
        $mdl_order	= $this->loadModel('order');
        $accountInfo = $mdl_user_account_info->getByWhere(array('userid'=>$this->current_business['id']));

        $date = get2('date');
        $this->setData($date,'date');

        $driverSerial = (string)get2('driver-serial');
        $this->setData($driverSerial,'driverSerial');

      //  var_dump('data is: '.$date .' and driver id is '.$driverSerial);exit;

        $truckName =$this->loadModel('truck')->getTruckAndDriverInfo1($driverSerial,$this->current_business['id']);
//var_dump($truckName);exit;
        if(strtotime($date)) {
            $orders =$mdl_order->getdriversheetList($this->current_business['id'],$date,$driverSerial);
            $this->setData($orders,'orders');



            $lists_new = array();
//var_dump($orders);exit;
            foreach ($orders as $key => $value) {

                $lists_new[$key]['InvoiceNumber']=$value['xero_invoice_id'];
                $lists_new[$key]['CustomerName']=$value['nickname'];
                 $lists_new[$key]['Address']=$value['address'];

                $lists_new[$key]['StopNumber']=$value['logistic_stop_No'];
                $lists_new[$key]['logistic_sequence_No']=$value['logistic_sequence_No'];
                $lists_new[$key]['Phone']=$value['phone'];
                $lists_new[$key]['BoxesQuantity']=$value['boxesNumber'];
                $lists_new[$key]['Notes']=$value['Notes'];
            //    $lists_new[$key]['signed']=' ';
                $logistic_delivery_time = $value['logistic_delivery_date'];
           }

           //var_dump($orders);exit;
            if(get2('is-export') == 'true') {
                $labels = ['Inv No', 'Customer Name','Address','Stop No','Seq No','Phone','Boxes Qty','Notes'];
                $fileName =$date.'_'.substr($accountInfo['account_name'],0,5).(empty($driverSerial) ? '' : '_Driver'.$driverSerial );




                error_reporting(E_ALL);
                ini_set('display_errors', TRUE);
                ini_set('display_startup_errors', TRUE);
                date_default_timezone_set('Australia/Sydney');

                if (PHP_SAPI == 'cli')
                    die('This example should only be run from a Web Browser');

                /** Include PHPExcel */

                require_once DOC_DIR.'core/phpexcel180/Classes/PHPExcel.php';
              //  require_once DOC_DIR.'core/b2b_2_0/b2b/lib/Database.php';

                $count = count($lists_new);
                $head="Dirver delivery sheet ";
                $start_time=date('Y-m-d',$logistic_delivery_time);
                $end_time=date('Y-m-d',$logistic_delivery_time);

// Create new PHPExcel object


                $obj = new PHPExcel();

                $obj->getActiveSheet()->getDefaultRowDimension()->setRowHeight(18);
                $obj->getActiveSheet()->getDefaultColumnDimension()->setCollapsed(true);
                $obj->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
                $obj->getActiveSheet()->getColumnDimension("A")->setWidth(15);
                $obj->getActiveSheet()->getColumnDimension("B")->setWidth(15);
                $obj->getActiveSheet()->getColumnDimension("C")->setWidth(45);
                $obj->getActiveSheet()->getColumnDimension("D")->setWidth(10);
                $obj->getActiveSheet()->getStyle('C')->getAlignment()->setWrapText(true);//自动换行
                $obj->getActiveSheet()->getColumnDimension("E")->setWidth(10);
                $obj->getActiveSheet()->getStyle('E1:E50')->getFont()->setSize(16);
                $obj->getActiveSheet()->getStyle('E1:E50')->getFont()->setBold(true);
                $obj->getActiveSheet()->getColumnDimension("F")->setWidth(10);
                $obj->getActiveSheet()->getStyle('G1:G50')->getFont()->setSize(16);
                $obj->getActiveSheet()->getStyle('G1:G50')->getFont()->setBold(true);
                $obj->getActiveSheet()->getColumnDimension("G")->setWidth(10);
                $obj->getActiveSheet()->getColumnDimension("H")->setWidth(10);
                $obj->getActiveSheet()->getStyle('H')->getAlignment()->setWrapText(true);//自动换行
           //     $obj->getActiveSheet()->getColumnDimension("I")->setWidth(5);

              //  $obj->getActiveSheet()->getDefaultColumnDimension('C')->setWidth(30);
                $obj->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
                $obj->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
                $obj->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
                $obj->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
// Add some data
                $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
                $obj->getActiveSheet(0)->setTitle(substr($truckName,0,29));

                $_row = 2;   //设置纵向单元格标识
                if ($labels) {
                    $_cnt = count($labels);
                    $obj->getActiveSheet()->getRowDimension('2')->setRowHeight(18);
                    $obj->getActiveSheet()->getStyle("A2:H2")->getFont()->setBold(true);
                    $obj->getActiveSheet()->getStyle('A2:H2')->getFont()->setSize(12);
                    $obj->getActiveSheet(0)->mergeCells('A1' . ':' . $cellName[$_cnt - 1] . '1');   //合并单元格
                    $obj->setActiveSheetIndex(0)->setCellValue('A1', $truckName);  //设置合并后的单元格内容
                    $obj->getActiveSheet()->mergeCells('A2:C2');//合并起始日期单元格
                    $obj->setActiveSheetIndex(0)->setCellValue('A2', 'Delivery Date [' . $start_time . ']');//设置值
                   // $obj->getActiveSheet()->mergeCells('C2:D2');//合并终止日期单元格
                  //  $obj->setActiveSheetIndex(0)->setCellValue('C2', '终止日期[' . $end_time . ']');//设置值
                    $_row++;
                    $i = 0;
                    $obj->getActiveSheet()->getRowDimension('3')->setRowHeight(18);
                    $obj->getActiveSheet()->getStyle("A3:H3")->getFont()->setBold(true);
                    $obj->getActiveSheet()->getStyle('A3:H3')->getFont()->setSize(12);
                   // $obj->getActiveSheet()->getStyle('A3:I3')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                   // $obj->getActiveSheet()->getStyle('A3:I3')->getFill()->()->setARGB("#FFC7CE");
                    foreach ($labels as $v) {   //设置列标题
                        $obj->setActiveSheetIndex(0)->setCellValue($cellName[$i] . $_row, $v);
                        $i++;
                    }
                    $_row++;
                }


                //填写数据
                if ($lists_new) {
                    $i = 0;
                    foreach ($lists_new as $_v) {
                        $j = 0;
                        foreach ($_v as $_cell) {
                            $obj->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + $_row), ' ' . $_cell);
                            $j++;
                        }
                        $i++;
                    }
                }




// Set active sheet index to the first sheet, so Excel opens this as the first sheet
                $obj->setActiveSheetIndex(0);

                //设置共几条数据行
            //    $obj->setActiveSheetIndex(0)->setCellValue('A' . (4 + $count + 1), '共' . $count . '条数据');//A10 = A（4+x+1）
                //导出时间行
            //    $obj->setActiveSheetIndex(0)->setCellValue('A' . (5 + $count + 1), '导出时间');
            //    $datetime = date('Y-m-d H:i:s', time());
            //    $obj->setActiveSheetIndex(0)->setCellValue('B' . (5 + $count + 1), "$datetime");

                ob_end_clean();
// Redirect output to a client’s web browser (Excel2007)
                $objWriter = PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="'.$fileName.'.xlsx"');
                header('Cache-Control: max-age=0');




                $objWriter->save('php://output');
                exit;



















               // $this->toExcel($lists_new,$labels,$fileName,'php://output');
               // return;
            }
        }

        // $dateOptions = $this->loadModel('order')->getListBySql('select DISTINCT  logistic_delivery_date from  ( (SELECT DISTINCT logistic_delivery_date  from cc_order where logistic_delivery_date >'.(time()-3600*24*7). ' and ( business_userId = '.$this->current_business['id']. ' or business_userId in (select cc_logistic_customers_id  from cc_freshfood_logistic_customers where cc_logistic_business_id ='.$this->current_business['id'].'))) as a union (SELECT DISTINCT logistic_delivery_date  from cc_order_import where logistic_delivery_date >'.(time()-3600*24*7). ' and ( business_userId = '.$this->current_business['id']. ' or business_userId in (select cc_logistic_customers_id  from cc_freshfood_logistic_customers where cc_logistic_business_id ='.$this->current_business['id'].'))) as b) ');

        $dateOptions = $this->loadModel('freshfood_logistic_customers')->getAvaliableDateOfThisLogisiticCompany($this->current_business['id']);

        $dateOptions = array_map(function($dateOption){
            return date('Y-m-d',$dateOption['logistic_delivery_date']);
        }, $dateOptions);


        //$driversOptions = $this->loadModel('order')->getListBySql('SELECT DISTINCT logistic_driver_code  from cc_order where logistic_delivery_date >'.(time()-3600*24*7). ' and business_userId = '.$this->current_business['id']);

        $driversOptions = $this->loadModel('freshfood_logistic_customers')->getDriversOfAvaliableDateOfThisLogisiticCompany($this->current_business['id'],$date);
        $driversOptions = array_map(function($dateOption){
            return str_pad($dateOption['logistic_truck_No'],3,'0',STR_PAD_LEFT);
        }, $driversOptions);

        $this->setData($dateOptions, 'dateOptions');
        $this->setData($driversOptions, 'driversOptions');

        $this->setData('Logistic_centre', 'menu');
        $this->setData('export_driver_route', 'submenu');
        $this->setData('导出路程单 - ' . $this->site['pageTitle'], 'pageTitle');


        $this->display('company/export_driver_route');
    }


    public function export_driver_route_action() {
        $this->loadModel('freshfood_disp_suppliers_schedule');
        $mdl_user_account_info	= $this->loadModel('user_account_info');
        $accountInfo = $mdl_user_account_info->getByWhere(array('userid'=>$this->current_business['id']));
        if (!in_array($this->current_business['id'], DispCenter::getDispCenterList())) {
            $this->sheader(null,'no access');
        }

        require_once( DOC_DIR.'static/OptimoRoute.php');
        $opRoute = new OptimoRoute($this->current_business['id'], $accountInfo['op_route_key']);

        $date = get2('date');
        $this->setData($date,'date');
        $opRoute->syncRoutesDownOnDeliverDate($date);

        $driverSerial = (string)get2('driver-serial');
        $this->setData($driverSerial,'driverSerial');

        if(strtotime($date)) {
            $orders = $opRoute->getDriversRoutes($date, empty($driverSerial) ?[] :  ['driverSerial' => $driverSerial]);
            $this->setData($orders,'orders');

            if(get2('is-export') == 'true') {
                $labels = ['Order ID', 'Vehicle Label','Stop Number','Address','Phone','logistic_sequence_No','logistic_suppliers_info','logistic_suppliers_count','Notes'];
                $fileName =$date.'_'.$accountInfo['account_name'].(empty($driverSerial) ? '' : '_Driver'.$driverSerial );
                $this->toExcel($orders,$labels,$fileName,'php://output');
                return;
            }
        }

       // $dateOptions = $this->loadModel('order')->getListBySql('select DISTINCT  logistic_delivery_date from  ( (SELECT DISTINCT logistic_delivery_date  from cc_order where logistic_delivery_date >'.(time()-3600*24*7). ' and ( business_userId = '.$this->current_business['id']. ' or business_userId in (select cc_logistic_customers_id  from cc_freshfood_logistic_customers where cc_logistic_business_id ='.$this->current_business['id'].'))) as a union (SELECT DISTINCT logistic_delivery_date  from cc_order_import where logistic_delivery_date >'.(time()-3600*24*7). ' and ( business_userId = '.$this->current_business['id']. ' or business_userId in (select cc_logistic_customers_id  from cc_freshfood_logistic_customers where cc_logistic_business_id ='.$this->current_business['id'].'))) as b) ');
       
        $dateOptions = $this->loadModel('freshfood_logistic_customers')->getAvaliableDateOfThisLogisiticCompany($this->current_business['id']);

	    $dateOptions = array_map(function($dateOption){
            return date('Y-m-d',$dateOption['logistic_delivery_date']);
        }, $dateOptions);
        
		
		//$driversOptions = $this->loadModel('order')->getListBySql('SELECT DISTINCT logistic_driver_code  from cc_order where logistic_delivery_date >'.(time()-3600*24*7). ' and business_userId = '.$this->current_business['id']);
      
          $driversOptions = $this->loadModel('freshfood_logistic_customers')->getDriversOfAvaliableDateOfThisLogisiticCompany($this->current_business['id'],$date);
    	  $driversOptions = array_map(function($dateOption){
            return str_pad($dateOption['logistic_truck_No'],3,'0',STR_PAD_LEFT);
        }, $driversOptions);

        $this->setData($dateOptions, 'dateOptions');
        $this->setData($driversOptions, 'driversOptions');
		
		$this->setData('Logistic_centre', 'menu');
        $this->setData('export_driver_route', 'submenu');
		$this->setData('导出路程单 - ' . $this->site['pageTitle'], 'pageTitle');
		
		
        $this->display('company/export_driver_route');
    }

    public function set_dropnum_ajax_action()
    {	
    	/**
    	 * 前端 label print 页面点击  打印的时候弹出 drop Num 页面， 点击任意数字出发 ajax 到这里。
    	 * 要如何储存在数据库里请在这里 添加。
    	 */
    	$orderId = post('orderId');
    	$dropNum = post('dropNum');
    	echo 'success' . $orderId . '-' . $dropNum ;
    }
	
		
public function import_new_order_xiaochengxu_action() {
		
		header("Content-type: text/html;charset=utf8_general_ci");
		$order_import_data =post('newusers');
		$order_import_data=stripslashes($order_import_data);

		$order_import_data = json_decode($order_import_data,true);
		
		
		$delivery_date =post('delivery_date');
		$business_id0 =post('business_id0');
		
		

		if($order_import_data) {
			
			$result['message']='数据取出成功';
		}else{
			$result['message']='数据未取出';
			
			
			
		}
		
		
		
		//$result['message']=(string)$business_id0;
		//		echo json_encode($result); return;
		
	//	$result['message']='数据未取出';
		//echo json_encode($result);
		//
		/*if($this->loginUser['id'] !=25201) {
				$result['message']='no access';
		
				echo json_encode($result);
			   exit;
		}  */
		
		$mdl_order_import= $this->loadModel('order_import');
		
		//$mdl_user->begin();
		
        //清除之前的postcode数据
	
		if(!$delivery_date) {
				
				$time1 = strtotime('2020-10-15');
			
				
			}else{
				
					$delivert_date = strtotime($delivery_date);
			}
	
	   $where =array(
	   'business_userId'=>$business_id0,
	   'logistic_delivery_date'=>$delivert_date
	   
	   );
		//将本日的该商家的订单都要清除
		// $mdl_order_import ->deleteByWhere($where);
        
		   
	  
		
		$data=array();
		
		
		
		foreach ($order_import_data as $key => $value) {
			
			//'h:i:s a m/d/Y'
			
			
			
			
			
		
		
			$aa=rand(1000,99999);
			
			$data['order_name'] =$value['RowID'].'-'.$value['Contact'];
			$data['orderId'] =$aa.$value['Phone'];
			$data['money'] =$value['Total'];
			$data['business_userId'] =$business_id0;
			$data['first_name'] =$value['Contact'];
			$data['last_name'] ='';
			$data['address'] =$value['Address'];
			$data['phone'] ='0'.$value['Phone'];
			
			if($value['PaymentStatus']=='已支付') {
				$data['status'] ='1';
				
			}
			
			if($value['DeliverMethod']=='仅配送') {
				$data['customer_delivery_option'] ='1';
				
			}
			
			
			$data['state'] =$value['State'];
			$data['city'] =$value['City'];
			$data['logistic_delivery_date'] =$delivert_date;
			$data['logistic_sequence_No_String'] =$value['RowID'];
			
			//$sql = $this->getInsertSql($data,'order_import');
				$result['message']=$sql;
		//
			//	echo json_encode($result);
			//   exit;
			   
			if($value['RowID']){
				$where =array(
				  'order_name'=>$value['RowID'].'-'.$value['Contact'],
				  'logistic_delivery_date'=>$delivert_date,
				  'phone'=>$value['Phone']
				
		    	);
				if($mdl_order_import->getCount($where)>0) {
				//	$result['message']='重复插入';
				 $mdl_order_import->updateByWhere($data,$where);
					
				}else{
					$mdl_order_import->insert($data);
					
				}
				
				
				
			}
		
			$result['message']='处理成功';
			
			
			
			
			
			
			
		}
		
		
		echo json_encode($result);
		
	}
	
	public function import_new_order_easi_action() {
		
		header("Content-type: text/html;charset=utf8_general_ci");
		$order_import_data =post('newusers');
		$order_import_data=stripslashes($order_import_data);

		$order_import_data = json_decode($order_import_data,true);
		
		
		$delivery_date =post('delivery_date');
		$business_id0 =post('business_id0');
		
		

		if($order_import_data) {
			
			$result['message']='数据取出成功';
		}else{
			$result['message']='数据未取出';
			
			
			
		}
		
		
		
		//$result['message']=(string)$business_id0;
		//		echo json_encode($result); return;
		
	//	$result['message']='数据未取出';
		//echo json_encode($result);
		//
		/*if($this->loginUser['id'] !=25201) {
				$result['message']='no access';
		
				echo json_encode($result);
			   exit;
		}  */
		
		$mdl_order_import= $this->loadModel('order_import');
		
		 $mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');
		
		//$mdl_user->begin();
		
        //清除之前的postcode数据
	
		if(!$delivery_date) {
				
				$time1 = strtotime('2020-11-06');
			
				
			}else{
				
					$delivert_date = strtotime($delivery_date);
			}
	
	   $where =array(
	   'business_userId'=>$business_id0,
	   'logistic_delivery_date'=>$delivert_date
	   
	   );
		//将本日的该商家的订单都要清除
		// $mdl_order_import ->deleteByWhere($where);
        
		   
	  
		
		$data=array();
		
		
		
		foreach ($order_import_data as $key => $value) {
			
			//'h:i:s a m/d/Y'
			
			if(strlen($value['订单ID'])<=12) {
				$data['orderId']=$business_id0.$value['订单ID'];
			}else{
				$data['orderId']=$value['订单ID'];
				
			}
			
			$data['order_name'] =$value['联系人'].'-'.$value['商家名称'];
			//$data['orderId'] =$value['订单ID'].$business_id0;
			$data['money'] =0;
			$data['business_userId'] =$business_id0;
			$data['first_name'] =$value['联系人'];
			$data['last_name'] ='';
			$data['address'] =$value['送货地址'];
			$data['phone'] =$value['送货电话'];
			
			$data['status'] ='1';
				
			$data['customer_delivery_option'] ='1';
			
			$data['house_number'] =$value['地址补充'];
			
			$data['logistic_delivery_date'] =$delivert_date;
			$data['message_to_business']=$value['备注'];
			
			//$data['logistic_sequence_No_String'] =$value['RowID'];
			
			//$sql = $this->getInsertSql($data,'order_import');
			//	$result['message']=$sql;
		
			//	echo json_encode($result);
			//   exit;
			   
			 //检查改订单是否被写入过
			if($value['订单ID']){
				$where =array(
				  'orderId'=>$data['orderId'],
				  'logistic_delivery_date'=>$delivert_date
		    	);
				if($mdl_order_import->getCount($where)>0) {
				//	$result['message']='重复插入';
				 $mdl_order_import->updateByWhere($data,$where);
					
				}else{
					$mdl_order_import->insert($data);
					
				}
				
				
				
			}
			
			//开始写入明细
		
			
			
			$data1=array(
			//'order_id'=>$business_id0.'-'.$value['订单ID'],
			'gen_date'=>time,
			'userId'=>$value['送货电话'],
			'customer_lastname'=>$value['联系人'],
			'customer_area'=>',556,',
			'bonus_id'=>'99999',
			'bonus_title'=>$value['商品名称'].'('.$value['商品英文名称'].')',
			'bonus_type'=>7,
			'coupon_status'=>'c01',
			'cn_coupon_status_name'=>'已购买',
			'en_coupon_status_name'=>'Bought',
			'business_id'=>$business_id0,
			'customer_buying_quantity'=>$value['购买份数'],
			'voucher_deal_amount'=>$value['商品单价'],
			'adjust_subtotal_amount'=>$value['购买份数']*$value['商品单价']			
			
			);
			
			
			if(strlen($value['订单ID'])<=12) {
				$data1['order_id']=$business_id0.$value['订单ID'];
			}else{
				$data1['order_id']=$value['订单ID'];
				
			}
			
			if($value['订单ID']){
				$where =array(
				  'order_id'=>$data1['order_id'],
				  'bonus_title'=>$value['商品名称']
		    	);
				if($mdl_wj_customer_coupon->getCount($where)>0) {
				//	$result['message']='重复插入';
				 $mdl_wj_customer_coupon->updateByWhere($data1,$where);
					
				}else{
					$mdl_wj_customer_coupon->insert($data1);
					
				}
				
				
				
			}
			
			
		
			$result['message']='处理成功';
			
			
			
			
			
			
			
		}
		
		
		echo json_encode($result);
		
	}
	
public function import_new_order_action(){
	
	   $mdl_order = $this->loadModel('order_import');
        $mdl_user= $this->loadModel('user');
	  $customer_delivery_date = trim(get2('customer_delivery_date'));
	  $deleteFlag =(int)get2('deleteFlag');
	    $business_id =(int)get2('business_id');
		   $business_id1 =(int)get2('business_id1');
	 // var_dump($deleteFlag);exit;
	  if ( $customer_delivery_date && $deleteFlag) {
		  
		  $where =array(
			 ' DATE_FORMAT(from_unixtime(logistic_delivery_date),"%Y-%m-%d")' => $customer_delivery_date,
			  'business_userId'=>$business_id1
		  );
		 $mdl_order->deleteByWhere($where);
	 }
	   
	 
       


       

        $three_days_times = time()-25920000;
		
        $sql_avaliable_date =" SELECT DISTINCT o.logistic_delivery_date from (select logistic_delivery_date  from cc_order_import where `business_userId` in (select  business_id from cc_dispatching_centre_customer_list  where dispatching_centre_id =".	$this->loginUser['id'].")  ) as o where o.logistic_delivery_date >".$three_days_times." order by logistic_delivery_date ";
      
	   $availableDates = $this->loadModel('order')->getListBySql( $sql_avaliable_date);
    	$availableDates = array_map(function($d){
    		return date('Y-m-d',$d['logistic_delivery_date']);
    	}, $availableDates);
    	$this->setData($availableDates, 'availableDates');

   


        //** 获取该商家管辖的所有商家
		
		
			//suplierList
		$sql = "select * from cc_dispatching_centre_customer_list where dispatching_centre_id = ". $this->loginUser['id']." and data_source =2  order by sort ";
		$suplierList = loadModel('dispatching_centre_customer_list')->getListBySql($sql);
       // var_dump($suplierList);exit;
 	 if($suplierList) {
        // 如果某一个是统配店，那么拿到它的子店
		
		$newsuplierList=array();
		
		$index=0;
		 foreach ($suplierList as $key => $value) {
			 $newsuplierList[$index]=$value;
			 $index ++;
			 $where00 = array(
			  'business_id'	=>$value['business_id']		 
			 );
			 
			 
		 }
		 
		 
		 //selectedSupplierName 
		$selectedSupplierName = 'Please select a business';
		for ($i=0; $i <count($newsuplierList) ; $i++) { 
			if ($newsuplierList[$i]['business_id'] == $bid) {
				$selectedSupplierName = $newsuplierList[$i]['business_name'];
				$selectedSupplierDataSource = $newsuplierList[$i]['data_source'];
				break;
			}
		}
		
		
		 if (count($newsuplierList)>0) {
			
			 $this->setData($newsuplierList, 'newsuplierList');
		 }
		
	}	
		
		
	

		//交易状态购买
		//if(!status) {
		$status ='c01';
		//}
		//支付状态
		$ifpaid=1;
		
		 $business_id = trim(get2('business_id'));
		 $this->setData($business_id,'business_id');
		
		
		// 获得商家是否允许自提，以决定前端是否显示pickup 选择项
		
		$this->setData($this->loginUser['pickup_avaliable'],'pickup_avaliable');
		//var_dump ($this->loginUser['pickup_avaliable']);exit;
		
        $sk = trim(get2('sk'));
		
	
		
		$logistic_truck_No = trim(get2('logistic_truck_No'));
		$this->setData($logistic_truck_No,'logistic_truck_No');
		

		     
        $customer_delivery_option=trim(get2('customer_delivery_option'));
        $staff=trim(get2('staff'));

        $this->setData($sk,'sk');
        $this->setData($customer_delivery_option,'customer_delivery_option');
        $this->setData($staff,'staff');
		$this->setData($customer_delivery_date,'customer_delivery_date');
		
		
		// 加入了一个前面可以选择一个商家，然后显示该商家的相关记录，如果商家id 为空，则保持原来的处理，如果不为空则进行相应的处理
		
		if($business_id) {
			//var_dump($business_id);//exit;
			  $sql = "SELECT o.*  from cc_order_import as o ";
			  $whereStr.="  business_userId =$business_id ";
     
			
		}else{
			
			  $sql = "SELECT o.*  from cc_order_import as o ";
			  $whereStr.="  business_userId =".$this->loginUser['id'];
			
		}
		

  
         //var_dump($sql);exit;
        if (!empty($sk)) {
            $whereStr.=" and (o.redeem_code like  '%" . $sk . "%'";
            $whereStr.=" or o.last_name like  '%" . $sk . "%'";
            $whereStr.=" or o.phone like  '%" . $sk . "%'";
            $whereStr.=" or o.orderId like  '%" . $sk . "%'";
            $whereStr.=" or o.order_name like  '%" . $sk . "%'";
            $whereStr.=" or o.tracking_id like  '%" . $sk . "%'";
            $whereStr.=" or o.first_name like  '%" . $sk . "%'";
			$whereStr.=" or o.userId like  '%" . $sk . "%')";
            $where[]=$whereStr;
        }
      
        
		 if (!empty($status)) {
          
               $whereStr.= " and o.coupon_status = '$status' ";
          
        }
		
		 if (!empty($ifpaid)) {
          
               $whereStr.= " and o.status = '$ifpaid' ";
          
        }
		
		
       

	
		if (!empty($customer_delivery_date)) {
            if ($customer_delivery_date != 'all') {
               $whereStr.= " and  DATE_FORMAT(from_unixtime(o.logistic_delivery_date),'%Y-%m-%d') = '$customer_delivery_date' ";
            }else{
				$three_days_times = time()-259200;
				$whereStr.= " and  logistic_delivery_date > $three_days_times";
				 
				
			}
        }else {
			$three_days_times = time()-259200;
				$whereStr.= " and  logistic_delivery_date > $three_days_times";
		}
    
	  

        $pageSql=$sql . " where " . $whereStr . " order by id desc ";
       
	//  var_dump($pageSql);exit;
	 
		 
	   
	   
    
            $pageUrl = $this->parseUrl()->set('page');
            $pageSize = 40;
            $maxPage = 10;
            $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);

            $data = $mdl_order->getListBySql($page['outSql']);

     

        $this->setData($page['pageStr'],'pager');

        $this->setData($data,'data');

       
        
        $this->setData(HTTP_ROOT_WWW.'company/import_new_order', 'searchUrl');

        $this->setData($this->parseUrl(), 'currentUrl');
	
	
	
	
	
	
	
	
		$this->setData('dispatching_center', 'menu');
        $this->setData('import_new_order_xiaochengxu', 'submenu');
		$this->setData('外部数据 订单导入 - ' . $this->site['pageTitle'], 'pageTitle');
	     $this->display('company/import_new_order_xiaochengxu');
	
}
}