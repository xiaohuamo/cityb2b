<?php

/*
 @ctl_name = 支付方式设置@
*/

class ctl_payment extends adminPage
{

	public function index_action () #act_name = 编辑#
	{
		$mdl_payment = $this->loadModel( 'payment' );
		if ( is_post() ) {
			$data = post( 'data' );
			$mdl_payment->begin();
			foreach ( $data as $key => $val ) {
				$data[$key]['config'] = serialize( $val['config'] );
				$mdl_payment->update( $data[$key], $key );
			}
			if ( $mdl_payment->errno() ) $mdl_payment->rollback();
			else $mdl_payment->commit();
			$this->sheader( $this->parseUrl() );
		}
		else {
			$payments = $mdl_payment->getList( null, null, null );
			foreach ( $payments as $key => $payment ) {
				$payments[$key]['config'] = unserialize( $payment['config'] );
			}
			$this->setData( $payments, 'payments' );
			$this->display();
		}
	}

}

?>