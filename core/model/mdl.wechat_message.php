<?php
abstract class WechatMessageType
{
    const CustomerOrderNotification=0;//新订单通知用户并且抄送给ubonus
    const BusinessOrderNotification=1;//新订单通知商家

    const CustomerCancelOrderNotification=2;//取消订单通知用户并且抄送给ubonus
    const BusinessCancelOrderNotification=3;//取消订单通知商家

    const CustomerRegistryNotification=4;//用户注册通知用户

    const BusinessDeliveryNotification=5;//商家发货通知用户并且抄送给ubonus

    const BusinessBalanceNotification=6;//ubonus自动结算给商家通知
}


class mdl_wechat_message extends mdl_base
{   
    //向关注了 WeChat Official Accounts 的用户 推送模版消息。 发送组件由田野开发维护

    public $tableName = '#@_wechat_order_msg';

    public function send($id,$type)
    {
        $mdl_order = loadModel('order');
        $mdl_user  = loadModel('user');
        $data=array();

        switch ($type) {
            case WechatMessageType::CustomerOrderNotification:
                $order = $mdl_order->getByOrderId($id);
                if(!$order)return false;
                $user=  $mdl_user->get($order['userId']);

                $data['openid']         =$user['wx_openID'];
                $data['url']            =HTTP_ROOT_WX."member/exchange_detail?id=".$order['orderId'];
                $data['title']          ="尊敬的客户，您的新订单已经成功确认";
                $data['product']        =$order['order_name'];
                $data['money']          =$order['money'];
                $data['status']         =$order['status']; //1 已支付  2 未支付
                break;

            case WechatMessageType::BusinessOrderNotification:
                $order = $mdl_order->getByOrderId($id);
                if(!$order)return false;
                $business=  $mdl_user->get($order['business_userId']);

                $data['openid']         =$business['wx_openID'];
                $data['url']            =HTTP_ROOT_WX."company/customer_order_detail?id=".$order['orderId'];
                $data['title']          ="尊敬的商家，您收到了新的客户订单";
                $data['product']        =$order['order_name'];
                $data['money']          =$order['money'];
                $data['status']         =$order['status']; //1 已支付  2 未支付

                break;

            case WechatMessageType::CustomerCancelOrderNotification:
                $order = $mdl_order->getByOrderId($id);
                if(!$order)return false;
                $user=  $mdl_user->get($order['userId']);

                $data['openid']         =$user['wx_openID'];
                $data['url']            =HTTP_ROOT_WX."member/exchange_detail?id=".$order['orderId'];
                $data['title']          ="尊敬的客户，您的订单已被取消";
                $data['product']        =$order['order_name'];
                $data['money']          =$order['money'];
                $data['status']         =$order['status']; //1 已支付  2 未支付
                $data['type']           ='2';//取消订单
                break;

            case WechatMessageType::BusinessCancelOrderNotification:
                $order = $mdl_order->getByOrderId($id);
                if(!$order)return false;
                $business=  $mdl_user->get($order['business_userId']);

                $data['openid']         =$business['wx_openID'];
                $data['url']            =HTTP_ROOT_WX."company/customer_order_detail?id=".$order['orderId'];
                $data['title']          ="尊敬的商家，您的订单已被取消";
                $data['product']        =$order['order_name'];
                $data['money']          =$order['money'];
                $data['status']         =$order['status']; //1 已支付  2 未支付
                $data['type']           ='2';//取消订单
                break;

            case WechatMessageType::BusinessDeliveryNotification:
                $this->tableName = '#@_wechat_delivery_msg';
                $order = $mdl_order->getByOrderId($id);
                if(!$order)return false;
                $user=  $mdl_user->get($order['userId']);

                $title = "尊敬的客户，您的订单已经发货";
                if ($order['logisitic_schedule_time'] > 0) {
                    $title.= ". ETA:".date('F j, g:i a', $order['logisitic_schedule_time']);
                }

                $data['openid']         =$user['wx_openID'];
                $data['url']            =HTTP_ROOT_WX."member/exchange_detail?id=".$order['orderId'];
                $data['title']          ="尊敬的客户，您的订单已经发货";
                $data['orderid']        =$id;
                //$data['orderstatus']    ='1';订单状态：mysql默认1：已发货
                //$data['logisticsName']  ='1';物流信息：mysql默认1：fast way
                $data['logisticsNo']    =$order['tracking_id'];

                break;

            case WechatMessageType::CustomerRegistryNotification:
                $this->tableName = '#@_wechat_registry_msg';
                $customerRegistry= $mdl_user->get($id);//用户注册id
                $data['openid']         =$customerRegistry['wx_openID'];
                $data['url']            ='http://livechat.ubonus365.com/WebClientComputer.aspx?ClientID='.$id.'&BusinessID=25201';
                $data['title']          ="尊敬的客户，恭喜您注册ubonus账号成功";
                $data['username']      =$customerRegistry['name'];
                $data['remark']        ="感谢您的注册，有疑问请联系客服！";
                break;

            case WechatMessageType::BusinessBalanceNotification:
                $this->tableName = '#@_wechat_balance_msg';
                /**
                $business=  $mdl_user->get($order['business_userId']);

                $data['openid']         =$business['wx_openID'];
                $data['url']            =HTTP_ROOT_WX."company/customer_order_detail?id=".$order['orderId'];
                $data['title']          ="尊敬的商家，您收到了新的客户订单";
                $data['product']        =$order['order_name'];
                $data['money']          =$order['money'];
                $data['status']         =$order['status']; //1 已支付  2 未支付
                 **/
                break;

            default :
                # code...
                break;
        }
        if(!$data['openid'])return false;

        return $this->insert($data);

    }
}   

?>