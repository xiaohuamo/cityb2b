<?php

class mdl_reg extends mdl_base
{

	function chkMail ($s, $p = '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/')
	{
		return preg_match($p, $s);
	}

	function chkUrl ($s, $p = '/[a-zA-z]+:\/\/[^\s]*/')
	{
		return preg_match($p, $s);
	}

	//6-16个由a-z，A-Z，0-9以及下划线组成的字符串，但不能以数字和下划线开头
	function chkUsername ($s, $p = '/^[a-zA-Z][a-zA-Z0-9_]{5,16}$/')
	{
		return preg_match($p, $s);
	}

	//6-16个由a-z，A-Z，0-9以及下划线组成的字符串
	function chkPassword ($s, $p = '/^[a-zA-Z0-9_]{6,16}$/')
	{
		return preg_match($p, $s);
	}

    //11个由纯数字组成的字符串
    function chkABN ($s, $p = "/^[0-9]{11}$/")
    {
        return preg_match($p, $s);
    }

	function chkPhone ($s, $p = '')
	{	
		/**
		 * China mobile phone 135 0987 2734
		 * @var string
		 */
		$p_cn="/^1[0-9]{10}$/";

		/**
		 * Australia mobile phone 0432 123 343
		 * @var string
		 */
		$p_au="/^04[0-9]{8}$/";


		if(preg_match($p_au, $s)||preg_match($p_cn, $s)){
			return true;
		}else{
			return false;
		}
	}

	function chkColor ($s, $p = '/^#[0-9A-Za-z]{3,6}$/')
	{
		return preg_match($p, $s);
	}

}

?>