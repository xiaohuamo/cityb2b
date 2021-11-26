<?php
abstract class SystemNotification{
	const NewOrder=0;//订单生成
    const CancelOrder=1;//订单取消
    const CustomerRegistry=2;//用户注册
	const BusinessDelivery=3;//商家发货
    const BusinessBalance=4;//商家结算
    const CustomerSubscribe=5;//商品订阅
}

class mdl_system_notification_center extends mdl_base
{
	public function notify($type,$id)
	{	
		switch ($type) {
			case SystemNotification::NewOrder:
				$mail_services = loadModel('system_mail_queue');
				$mail_services->add($id,EmailType::CustomerOrderNotification);
				$mail_services->add($id,EmailType::BusinessOrderNotification);

				$mdl_wechat_message=loadModel('wechat_message');
				$mdl_wechat_message->send($id,WechatMessageType::CustomerOrderNotification);
				$mdl_wechat_message->send($id,WechatMessageType::BusinessOrderNotification);
				
			
               
     			// if order 为 团购产品 ，类型为7 ，则发送短信到手机端
				
				$sql ="SELECT DISTINCT o.phone,bonus_type ,c.EvoucherOrrealproduct ,order_id from cc_wj_customer_coupon a left join cc_order o on a.order_id=o.orderId left join cc_coupons c on a.bonus_id = c.id   where o.orderId  = $id and EvoucherOrrealproduct ='evoucher'";
				
				$findVoucher = loadModel('order')->getListBySql($sql);
				
				if($findVoucher){
					 $mobile =$findVoucher[0]['phone'];
					$firstletter=substr( $mobile, 0, 1 );
					if($firstletter =='0') {
						$full_number = '+61'.substr( $mobile, 1,9 );
					}else{
						$full_number = '+61'.substr( $mobile, 0,9 );
						
					}
					//var_dump($full_number);exit;
					  $content= ' 【Ubonus-亿折扣】 您预定成功：'.'https://ubonus365.com/member/exchange_detail?type=member&id='.$id;
					  send_sms($full_number, $content);
					
				}
				
				

					
				break;
            case SystemNotification::CancelOrder:
                $mail_services = loadModel('system_mail_queue');
                $mail_services->add($id,EmailType::CustomerCancelOrderNotification);
                $mail_services->add($id,EmailType::BusinessCancelOrderNotification);

                $mdl_wechat_message=loadModel('wechat_message');
                $mdl_wechat_message->send($id,WechatMessageType::CustomerCancelOrderNotification);
                $mdl_wechat_message->send($id,WechatMessageType::BusinessCancelOrderNotification);
                break;
            case SystemNotification::CustomerRegistry:
                $mail_services = loadModel('system_mail_queue');
                $mail_services->add($id,EmailType::CustomerRegistryNotification);

                $mdl_wechat_message=loadModel('wechat_message');
                $mdl_wechat_message->send($id,WechatMessageType::CustomerRegistryNotification);
                break;
            case SystemNotification::BusinessDelivery:
                $mail_services = loadModel('system_mail_queue');
                $mail_services->add($id,EmailType::BusinessDeliveryNotification);

                $mdl_wechat_message=loadModel('wechat_message');
                $mdl_wechat_message->send($id,WechatMessageType::BusinessDeliveryNotification);
                break;
            case SystemNotification::BusinessBalance:
                $mail_services = loadModel('system_mail_queue');
                $mail_services->add($id,EmailType::BusinessBalanceNotification);

                $mdl_wechat_message=loadModel('wechat_message');
                $mdl_wechat_message->send($id,WechatMessageType::BusinessBalanceNotification);
                break;
            case SystemNotification::CustomerSubscribe:
                $mail_services = loadModel('system_mail_queue');
                $mail_services->add($id,EmailType::CustomerSubscribeNotification);
                break;
			default:
				# code...
				break;
		}
	}

	
}

?>