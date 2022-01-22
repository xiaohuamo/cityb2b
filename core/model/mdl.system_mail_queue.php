<?php

abstract class EmailType{
    const CustomerOrderNotification=0;//新订单通知用户并且抄送给ubonus
    const BusinessOrderNotification=1;//新订单通知商家

    const CustomerCancelOrderNotification=2;//取消订单通知用户并且抄送给ubonus
    const BusinessCancelOrderNotification=3;//取消订单通知商家

    const CustomerRegistryNotification=4;//用户注册通知用户并且抄送给ubonus

    const BusinessDeliveryNotification=5;//商家发货通知用户并且抄送给ubonus

    const BusinessBalanceNotification=6;//ubonus自动结算给商家通知

    const CustomerSubscribeNotification=7;//客户订阅邮件
}


class mdl_system_mail_queue extends mdl_base
{
    const WAITING            = 'waiting';
    const SENDING            = 'sending';
    const COMPLETE           = 'complete';
    const FAIL               = 'fail';
    const ERROR              = 'error';

    protected $tableName = '#@_system_mail_queue';
   
    public function add($systemid,$type)
    {   
        if(!$systemid)return false;

        $system_mail_data=array();

        switch ($type) {
            case EmailType::CustomerOrderNotification:

                $order = loadModel('order')->getByOrderId($systemid);

                $customerEmail = $order['email'];

                $system_mail_data = array(
                    'systemid'      => $systemid,
                    'type'          => $type,
                    'status'        => self::WAITING,
                    'to'            => $customerEmail
                );

                /**
                * 设置模板语言 
                */
                $system_mail_data['lang']=$this->getLang();

                break;

            case EmailType::BusinessOrderNotification:

                $order = loadModel('order')->getByOrderId($systemid);

                $businessUser = loadModel('user')->get($order['business_userId']);

                $businessUserEmail = $businessUser['email'];

                $system_mail_data = array(
                    'systemid'      => $systemid,
                    'type'          => $type,
                    'status'        => self::WAITING,
                    'to'            => $businessUserEmail
                );

                break;

            case EmailType::CustomerCancelOrderNotification:

                $order = loadModel('order')->getByOrderId($systemid);

                $customerEmail = $order['email'];

                $system_mail_data = array(
                    'systemid'      => $systemid,
                    'type'          => EmailType::CustomerCancelOrderNotification,
                    'status'        => self::WAITING,
                    'to'            => $customerEmail
                );

                break;

            case EmailType::BusinessCancelOrderNotification:
                $order = loadModel('order')->getByOrderId($systemid);
                $businessUser = loadModel('user')->get($order['business_userId']);

                $businessUserEmail = $businessUser['email'];

                $system_mail_data = array(
                    'systemid'      => $systemid,
                    'type'          => EmailType::BusinessCancelOrderNotification,
                    'status'        => self::WAITING,
                    'to'            => $businessUserEmail
                );

                break;

            case EmailType::CustomerRegistryNotification:
                $user = loadModel('user')->get($systemid);
                $customerEmail = $user['email'];
                $system_mail_data = array(
                    'systemid'      => $systemid,
                    'type'          => EmailType::CustomerRegistryNotification,
                    'status'        => self::WAITING,
                    'to'            => $customerEmail
                );
                break;

            case EmailType::BusinessDeliveryNotification:
                $order = loadModel('order')->getByOrderId($systemid);
                $customerEmail = $order['email'];
                $system_mail_data = array(
                    'systemid'      => $systemid,
                    'type'          => EmailType::BusinessDeliveryNotification,
                    'status'        => self::WAITING,
                    'to'            => $customerEmail
                );
                break;

            case EmailType::BusinessBalanceNotification:
                $businessUser = loadModel('user')->get($systemid);
                $businessUserEmail = $businessUser['email'];
                $system_mail_data = array(
                    'systemid'      => $systemid,
                    'type'          => EmailType::BusinessBalanceNotification,
                    'status'        => self::WAITING,
                    'to'            => $businessUserEmail
                );
                break;

            case EmailType::CustomerSubscribeNotification://客户订阅邮件
                $user = loadModel('user')->get($systemid);
                $customerUserEmail = $user['email'];
                $system_mail_data = array(
                    'systemid'      => $systemid,
                    'type'          => EmailType::CustomerSubscribeNotification,
                    'status'        => self::WAITING,
                    'to'            => $customerUserEmail
                );
                break;

            default:
                # code...
                break;
        }
        if(!$system_mail_data['to'])return false;

        return $this->insert($system_mail_data);
    }

    /**
     * Special add
     */
    public function addMarketEmail($address,$systemid)
    {
        $system_mail_data = array(
                    'systemid'      => $systemid,
                    'type'          => EmailType::CustomerSubscribeNotification,
                    'status'        => self::WAITING,
                    'to'            => $address
                );

        if(!$system_mail_data['to'])return false;

        return $this->insert($system_mail_data);
    }

    public function delete($systemid)
    {
        $where['systemid'] = $systemid;
        return $this->deleteByWhere($where);
    }

    public function updateStatus($systemid, $status)
    {
        $data = array(
            'status' => $status,
            'operationtime' => date('Y-m-d H:i:s')
        );
        $where['systemid']=$systemid;
        $this->updateByWhere($data, $where);
    }

    public function getQueuingList($limit=10)
    {
        $where[]= " (status= '" .self::WAITING."')";
        $where[]= " (type != '".EmailType::CustomerSubscribeNotification."') ";
        $order= " id desc ";

        return  $this->getList(null,$where,$order,$limit);
    }

    public function getQueuingList_CustomerSubscribeNotification($limit=10)
    {
        $where[]= " (status= '" .self::WAITING."')";
        $where[]= " (type = '".EmailType::CustomerSubscribeNotification."') ";
        $order= " id desc ";

        return  $this->getList(null,$where,$order,$limit);
    }

    private function acquireTasks()
    {
        try {
            $this->begin();

            $list1 =$this->getQueuingList(20);
            $list2 =$this->getQueuingList_CustomerSubscribeNotification(20);

            $list =array_merge($list1,$list2);

            foreach ($list as $l) {
               $data=[];
               $data['status']=self::SENDING;
               $data['operationtime'] = date('Y-m-d H:i:s');
               $this->update($data,$l['id']);
            }
            
            $this->commit();

            return $list;
        } catch (Exception $e) {

            $this->rollback();

            return null;
        }
       
    }

    private function dispatcher($queueItem)
    {   
        $status=false;

        $template = loadModel('system_mail_template');
        $system_mailer = loadModel('system_mail');


        $systemId = $queueItem['systemid'];

        if(!$queueItem['to'])return false;

        /**
         * 设置模板语言
         */
        $template->setTemplateLang($queueItem['lang']);

        switch ($queueItem['type']) {
            case EmailType::CustomerOrderNotification:

                $orderName=loadModel('order')->generateOrderName($systemId,$this->lang);

                $title = (string)$this->lang->email_order_confirmed . " -- $orderName -- cityb2b.com";
                $body  = $template->customerOrderNotification($systemId,$this->getLang());
                $to    = $queueItem['to'];

                $system_mailer->title($title);
                $system_mailer->body($body);
                $system_mailer->to($to);

                $status=$system_mailer->send();

                break;
            
            case EmailType::BusinessOrderNotification:
                
                $title = (string)$this->lang->email_order_confirmed ."-- cityb2b.com";
                $body  = $template->businessOrderNotification($systemId);
                $to    = $queueItem['to'];

                $system_mailer->title($title);
                $system_mailer->body($body);
                $system_mailer->to($to);

                $status=$system_mailer->send();

                break;

            case EmailType::CustomerCancelOrderNotification:

                $title = (string)$this->lang->email_order_cancelled."-- cityb2b.com";
                $body  = $template->customerCancelOrderNotification($systemId);
                $to    = $queueItem['to'];

                $system_mailer->title($title);
                $system_mailer->body($body);
                $system_mailer->to($to);

                $status=$system_mailer->send();

                break;

            case EmailType::BusinessCancelOrderNotification:

                $title = (string)$this->lang->email_order_cancelled."-- cityb2b.com";
                $body  = $template->businessCancelOrderNotification($systemId);
                $to    = $queueItem['to'];

                $system_mailer->title($title);
                $system_mailer->body($body);
                $system_mailer->to($to);

                $status=$system_mailer->send();

                break;

            case EmailType::CustomerRegistryNotification:

                $title = (string)$this->lang->email_new_register."-- cityb2b.com";
                $body  = $template->customerRegistryNotification($systemId);
                $to    = $queueItem['to'];

                $system_mailer->title($title);
                $system_mailer->body($body);
                $system_mailer->to($to);

                $status=$system_mailer->send();

                break;

            case EmailType::BusinessDeliveryNotification:

                $title = (string)$this->lang->email_business_send_item." -- cityb2b.com";
                $body  = $template->businessDeliveryNotification($systemId);
                $to    = $queueItem['to'];

                $system_mailer->title($title);
                $system_mailer->body($body);
                $system_mailer->to($to);

                $status=$system_mailer->send();

                break;

            case EmailType::BusinessBalanceNotification:

                $title = (string)$this->lang->email_payments_notice."-- cityb2b.com";
                $body  = $template->businessBalanceNotification($systemId);
                $to    = $queueItem['to'];

                $system_mailer->title($title);
                $system_mailer->body($body);
                $system_mailer->to($to);

                $status=$system_mailer->send();

                break;
            case EmailType::CustomerSubscribeNotification:

                $title = (string)$this->lang->email_subscriptions."-- cityb2b.com";
                $body  = $template->customerSubscribeNotification($systemId);

                $to    = $queueItem['to'];

                $system_mailer->title($title);
                $system_mailer->body($body);
                $system_mailer->to($to);

                $status=$system_mailer->send();

                break;
            default:
                $status=false;
                break;
        }

        if(!$status)filelog("Email send fail:($systemId)".date('Y-m-d H:i:s').$system_mailer->handler->ErrorInfo,'emailQueue.txt');

        $status=($status)?self::COMPLETE:self::FAIL;

        $this->update(array('status'=>$status,'operationtime' => date('Y-m-d H:i:s')), $queueItem['id']);

    }

    public function run()
    {
        $list = $this->acquireTasks();
        foreach ($list as $queueItem) {
            try {
                $this->dispatcher($queueItem);
                   
            } catch (Exception $e) {
                filelog("Email send fail:($systemId)".date('Y-m-d H:i:s').$e,'emailQueue.txt');
                $this->update(array('status'=>self::ERROR,'operationtime' => date('Y-m-d H:i:s')), $queueItem['id']);
            }
           
        }

    }

}

?>