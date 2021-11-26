<?php

/*
 @ctl_name = subscribe@
*/

class ctl_subscribe extends adminPage
{

	protected $validates = array(
		/*'firstName' => array( 'message' => '' ),
		'lastName' => array( 'message' => '' ),*/
		'email' => array( 'method' => 'email', 'message' => '' )
	);

	function ctl_subscribe() {
		parent::adminPage();
		/*$this->validates['firstName']['message'] = $this->lang->first_name_can_not_empty;
		$this->validates['lastName']['message'] = $this->lang->last_name_can_not_empty;*/
		$this->validates['email']['message'] = $this->lang->email_can_not_empty;
	}

	public function index_action () #act_name = index#
	{
		$search		= array();
		$search['classId'] 		= (int)get2('classId');
		$search['email'] 	= get2('email');
		$bllLink	= $this->loadModel('subscribe');

		if ( $search['classId'] > 0 ) {
			$where[]= " classId='".$search['classId']."'";
		}
		if ( $search['email'] ) {
			$where[]= "  (email like '%".$search['email']."%')";
		}
		$order		= "submit_time desc";
		$pageSql	= $bllLink->getListSql(null, $where, $order);
		$pageUrl	= '?con=admin&ctl=adv/subscribe&';
		$pageSize	= 20;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $bllLink->getListBySql($page['outSql']);

		$mdl_class = $this->loadModel('subscribeclass');
		foreach ( $data as $key => $val ) {
			if ( $val['classId'] > 0 ) {
				$category = $mdl_class->get( $val['classId'] );
				if ( $category ) {
					$data[$key]['category_name'] = $category['name'];
				}
				else {
					$data[$key]['category_name'] = $this->lang->has_been_deleted;
				}
			}
			else {
				$data[$key]['category_name'] = $this->lang->unallocated;
			}
			$data[$key]['submit_time'] = date( 'Y-m-d H:i:s', $val['submit_time'] );
		}

		$this->setData($data, 'data');
		$this->setData($search, 'search');
		$this->setData($page['pageStr'], 'pager');
		$mdl_class = $this->loadModel('subscribeclass');
		$this->setData( $mdl_class->getList(), 'classList' );
		$this->display();
	}

	public function add_action() #act_name = add#
	{
		$bllLink	= $this->loadModel('subscribe');
		if (is_post())
		{
			$data = post('data');
			$this->cookie->setCookie( 'subscribe_create', $data );
			$this->validate( $data );

			$data['submit_time'] = time();
			$data['submit_ip'] = ip();
			if ( ! $bllLink->add( $data ) )
			{
				$this->sheader(null, $this->lang->create_email_failed);
			}
			$this->cookie->clearArrayCookie( 'subscribe_create', $data );
			$this->sheader('?con=admin&ctl=adv/subscribe');
		}
		else
		{
			$mdl_class = $this->loadModel('subscribeclass');
			$this->setData( $mdl_class->getList(), 'classlist' );
			$this->setData( $this->cookie->getCookie( 'subscribe_create' ), 'form' );
			$this->display();
		}
	}

	public function edit_action () #act_name = edit#
	{
		$id			= (int)get2('id');
		$bllLink	= $this->loadModel('subscribe');
		$data		= $bllLink->get($id);
		if (!$data) $this->sheader(null, $this->lang->current_record_not_exists);
		if (is_post())
		{

			$data = post('data');
			$this->cookie->setCookie( 'subscribe_edit_'.$id, $data );
			$this->validate( $data );
			if ($bllLink->update($data, $id))
			{
				$this->cookie->clearArrayCookie( 'subscribe_edit_'.$id, $data );
				$this->sheader('?con=admin&ctl=adv/subscribe');
			}
			else
			{
				$this->sheader(null, $this->lang->edit_email_failed);
			}
		}
		else
		{
			$mdl_class = $this->loadModel('subscribeclass');
			$this->setData( $mdl_class->getList(), 'classlist' );
			$this->setData($data, 'data');
			$this->setData( $this->cookie->getCookie( 'subscribe_edit_'.$id ), 'form' );
			$this->display();
		}
	}

	public function view_action () #act_name = view#
	{
		$id			= (int)get2('id');
		$bllLink	= $this->loadModel('subscribe');
		$data		= $bllLink->get($id);
		if (!$data) $this->sheader(null, $this->lang->current_record_not_exists);
		$data['memo'] = str_replace( "\n", '<br />', $data['memo'] );
		$mdl_class = $this->loadModel('subscribeclass');
		if ( $data['classId'] > 0 ) {
			$category = $mdl_class->get( $data['classId'] );
			if ( $category ) {
				$data['category_name'] = $category['name'];
			}
			else {
				$data['category_name'] = 'Has been deleted';
			}
		}
		else {
			$data['category_name'] = 'Unallocated';
		}
		$this->setData($data, 'data');
		$this->display();
	}

	public function send_action () #act_name = send#
	{
		$bllLink		= $this->loadModel('subscribe');
		$mdl_adminemail = $this->loadModel( 'adminemail' );
		$admin_email	= $mdl_adminemail->get();
		$mdl_site = $this->loadModel( 'site' );
		$site = $mdl_site->get();
		if ( is_post() ) {
			$mail_content = post( 'mail_content' );
			$mail_addr = explode('; ', post( 'mail_addr' ) );
			$mail_addr = array_flip( array_flip( $mail_addr ) );
			$mail_addr_tmp = array();
			$exclude_mails = post( 'exclude_mails' );
			if ( count( $exclude_mails ) > 0 ) {
				foreach ( $mail_addr as $mail_a ) {
					if ( ! in_array( $mail_a, $exclude_mails ) ) {
						$mail_addr_tmp[] = $mail_a;
					}
				}
				$mail_addr = $mail_addr_tmp;
			}
			if ( count( $mail_addr ) > 0 ) {
				foreach ( $mail_addr as $key => $val ) {
					send_mail( $val, 'An e-mail from '.$site['name'], nl2br( $mail_content ) );
				}
			}
			$this->sheader( '?con=admin&ctl=adv/subscribe', $this->lang->bulk_send_mail_success );
		}
		else {
			$classId = (int)get2( 'classId' );
			$mdl_class = $this->loadModel('subscribeclass');
			$mail_addr = $admin_email['email'];
			if ( $classId != 0 ) {
				$where = '';
				if ( $classId > 0 ) {
					$where = "classId='$classId'";
				}
				$mails = $bllLink->getList( $where, 'submit_time desc' );
			}
			$this->setData( $mdl_class->getList(), 'classlist' );
			$this->setData( $mails, 'mails' );
			$this->setData( $mail_addr, 'mail_addr' );
			$this->setData( $classId, 'classId' );
			$this->display();
		}
	}

	public function sendone_action () #act_name = send one#
    {
	    $email			= get2('email');
	    $mdl_adminemail = $this->loadModel( 'adminemail' );
	    $admin_email	= $mdl_adminemail->get();
	    $mdl_site = $this->loadModel( 'site' );
	    $site = $mdl_site->get();
	    if ( is_post() ) {
	        $email	= post('mail_addr');
	        $mail_content = post( 'mail_content' );
	        send_mail( $email, 'An e-mail from '.$site['name'], nl2br( $mail_content ) );
	        $this->sheader( '?con=admin&ctl=adv/subscribe', $this->lang->send_mail_success );
	    }
	    else {
            $mail_addr = $email;
            $this->setData( $mail_addr, 'mail_addr' );
            $this->display();
	       }
    }

    public function sendtwo_action () #act_name = send two#
    {
        $mdl_site = $this->loadModel( 'site' );
        $site = $mdl_site->get();

        $wj_customer_coupon = loadModel( 'wj_customer_coupon' );
        if ( is_post() ) {
            $mail_content = post( 'mail_content' ) ;
            $couponid = post( 'couponid' ) ;
            $mail_addr	= $wj_customer_coupon->getListBySql("SELECT DISTINCT(email),bonus_id FROM  cc_wj_customer_coupon AS C LEFT JOIN cc_user AS U ON C.userId=U.id where email<>'' and bonus_id='$couponid'");

            echo "群发暂不支持，以下是群发列表"."<br>";
            foreach ( $mail_addr as $key => $val ) {
                //send_mail( $val['email'], 'An e-mail from '.$site['name'], nl2br( $mail_content ) );
                echo $val['email']."<br>";
            }
            //$this->sheader( '?con=admin&ctl=adv/subscribe&act=mail_group', $this->lang->send_mail_success );
        }
        else {
            $this->setData(get2('id'), 'couponid' );
            $this->display();
        }
       
    }

    public function sendthree_action () #act_name = send three#
    {
        $mdl_site = $this->loadModel( 'site' );
        $site = $mdl_site->get();

        $wj_customer_coupon = loadModel( 'wj_customer_coupon' );
        if ( is_post() ) {
            $mail_content = post( 'mail_content' );
            $id = post( 'id' );
            $mail_addr	= $wj_customer_coupon->getListBySql("SELECT DISTINCT(email),business_id FROM  cc_wj_customer_coupon AS C LEFT JOIN cc_user AS U ON C.userId=U.id where email<>'' AND  business_id=$id");

             echo "群发暂不支持，以下是群发列表"."<br>";

            foreach ( $mail_addr as $key => $val ) {
                //send_mail( $val['email'], 'An e-mail from '.$site['name'], nl2br( $mail_content ) );
                echo $val['email']."<br>";
            }
            // $this->sheader( '?con=admin&ctl=adv/subscribe&act=mail_group_business', $this->lang->send_mail_success );
        }
        else {
            $this->setData(get2('id'), 'id' );
            $this->display();
        }
        /*
        $mail_addr	= loadModel( 'explosion' )->getListBySql("SELECT e.id,c.title,e.couponid,e.sort,pt.pagename,pnt.name,c.voucher_deal_amount,c.voucher_original_amount,c.pic FROM cc_explosion e LEFT JOIN cc_coupons c ON c.id=e.couponid LEFT JOIN cc_user u ON u.id=c.createUserId LEFT JOIN cc_pagetype AS pt ON pt.id=e.pagetype LEFT JOIN cc_panaltype AS pnt ON pnt.id=e.panaltype WHERE e.pagetype=6 ORDER BY sort");
        $mail_content = post( 'mail_content' );
        $mdl_site = $this->loadModel( 'site' );
        $site = $mdl_site->get();
        foreach ( $mail_addr as $key => $val ) {
            send_mail( $val, 'An e-mail from '.$site['name'], nl2br( $mail_content ) );
        }
        $this->sheader( '?con=admin&ctl=adv/subscribe', $this->lang->send_mail_success );
        **/
    }

    public function marketing_action () #act_name = marketing#
	{
        $mdl_subscribe= $this->loadModel('subscribe');
		$mdl_class = $this->loadModel('subscribeclass');
        $mdl_explosion = loadModel( 'explosion' );

		if ( is_post() ) {
			//$mail_content = post( 'mail_content' );
			$classId = (int)post( 'classId' );

			$where['classId']=$classId;

			$list = $mdl_subscribe->getList($where,'id');

            foreach ($list as $key => $val)//群发送邮件
            {
                $this->loadModel('system_mail_queue')->addMarketEmail($val['email'],$val['userid']);
            }

			$this->sheader( '?con=admin&ctl=adv/subscribe&act=marketing', $this->lang->bulk_send_mail_success." 共".sizeof($list)."封. 邮件将分批从邮件队列中发出。全部发出需要用时约".(sizeof($list)/20)."分钟" );
		}
		else {
			$this->setData( $mdl_class->getList(), 'classlist' );
			$this->setData( $this->parseUrl()->set('act','marketing_email_template_preview'), 'previewUrl' );
			$this->display();
		}
	}

	public function marketing_email_template_preview_action() #act_name = 预览邮件模板#
	{	
		//echo $this->loadModel('system_mail_template')->customerSubscribeNotification();
	}

	public function delete_action () #act_name = delete#
	{
		if (is_post())
		{
			$ids = post('ids');
			if (is_array($ids))
			{
				foreach ($ids as $k=>$v)
				{
					self::_delete((int)$v);
				}
			}
		}
		else
		{
			self::_delete((int)get2('id'));
		}
		$this->sheader('?con=admin&ctl=adv/subscribe');
	}

	private function _delete ($id)
	{
		$id			= (int)$id;
		$bllLink	= $this->loadModel('subscribe');
		$link		= $bllLink->get($id);
		if (!$link)
		{
			$this->sheader(null, $this->lang->current_record_not_exists."<br />id:$id");
		}
		if (!$bllLink->delete($id))
		{
			$this->sheader(null, $this->lang->delete_failed."<br />id:$id");
		}
	}

    public function mail_group_action () #act_name = mail_group#
    {
        $couponname			= get2('couponname');
        if ($couponname!='')
        {
            $where = " WHERE bonus_title like '%$couponname%'";
        }
        $mdl_coupons	= $this->loadModel('coupons');
        $sql="SELECT DISTINCT(bonus_id) as id ,bonus_title as title FROM  cc_wj_customer_coupon ORDER BY id desc $where limit 10";
        $this->setData($mdl_coupons->getListBySql($sql), 'data');

        $this->display();
    }

    public function mail_group_business_action () #act_name = mail_group_business#
    {
        $nickname			= get2('nickname');
        if ($nickname!='')
        {
            $where = " and nickname like '%$nickname%'";
        }
        $mdl_coupons	= $this->loadModel('coupons');
        $sql="SELECT id,nickname FROM cc_user WHERE role='3' and nickname<>'' $where ORDER BY id desc limit 10";
        $this->setData($mdl_coupons->getListBySql($sql), 'data');
        $this->display();
    }
}

?>