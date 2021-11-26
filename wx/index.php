<?php
error_reporting(0);
define("TOKEN", "weixin");		

$wechatObj = new wechatCallbackapiTest();
if (isset($_GET['echostr'])) {
   $wechatObj->valid();
}else{
   $wechatObj->responseMsg();
}

class wechatCallbackapiTest
{
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    public function responseMsg() {

		require_once "wxjssdk1.php";

	    $postStr = file_get_contents("php://input");

		$errCode =0;

		$encrypt_type = (isset($_GET['encrypt_type']) && ($_GET['encrypt_type'] == 'aes')) ? "aes" : "raw";
		if ($encrypt_type == 'aes'){
			include_once "WXBizMsgCrypt.php";
			$encodingAesKey = "V8bhIcSHRLGnnLE2roi0zOHQR3lliPI4MI9TdIcp39Q";
			$token= TOKEN;
			$appID="wx2a9d55ef6a586842";
			$timeStamp = $_GET["timestamp"];
			$nonce = $_GET["nonce"];
			$msg_sign = $_GET["msg_signature"]; 
			$pc = new WXBizMsgCrypt($token, $encodingAesKey, $appID);
			$msg = '';
			$errCode = $pc->DecryptMsg($msg_sign, $timeStamp, $nonce, $postStr, $msg);
			$postStr=$msg;		

		}
		if ($errCode == 0 && !empty($postStr)) {
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

			$RX_TYPE = trim($postObj->MsgType);
			
			switch($RX_TYPE)
			{
				case "text":
					$resultStr = $this->handleText($postObj);
					break;

				case "event":
					$resultStr = $this->handleEvent($postObj);
					break;

				default:
					$resultStr = "Unknow msg type: ".$RX_TYPE;
					break;

			}
			if ($encrypt_type == 'aes'){
				$encryptMsg = '';
				$errCode = $pc->EncryptMsg($resultStr, $timeStamp, $nonce, $encryptMsg);
				if ($errCode == 0) {
					$resultStr=$encryptMsg ;		// 将$resultStr加密成$encryptMsg
				} else {
					print($errCode . "\n");
				}
			}							
			echo $resultStr;
		}
    }

    public function handleText($postObj)
    {	
	    // 定义投票间隔 0 表示只能投一次;
		
         $content = trim($postObj->Content);
		 
		 if ($content >=422 and $content<=429) {
			 	$timesperday =1 ;
		 }else if ($content >=430 and $content<=437){
			 	$timesperday =1 ;
		 }else if ($content >=438 and $content<=510){
			 	$timesperday =1 ;
		 }else{
			    $timesperday =0;
			 
		 }



        $isone=rand(0,9);
		if($content==599) {
			
			if($isone>=8) {
				 $returnstr =$this->saveTextToDB($postObj, $timesperday);
			}else{
              //$returnstr='1';
			}
			
			
		}else{
			if($content==498){ //block user 
				$returnstr =$this->saveTextToDB($postObj, $timesperday);
			}else{
				$returnstr =$this->saveTextToDB($postObj, $timesperday);
			}
		}
				
				
		
        //  $returnstr =$this->saveTextToDB($postObj, $timesperday);
		
    	if ($returnstr) {
			
		
		    $content = trim($postObj->Content);


    		// 如果content,如果是数字,则更新 count表加一
			
			
			if ($content >=442 and $content<=450) {
				
				// 修改数量
				$this->updateVoteCount($postObj,1);
				
				$contentStr="投票成功! 感谢您对第7届全澳大学华语辩论邀请赛的支持!\n";
					$contentStr=$contentStr."辩论赛明星众筹已经开始,每达到$3000澳元将会邀请一名明星加盟做为辩论赛评委!\n";
					$contentStr=$contentStr."明星众筹及辩论赛预赛 复赛 决赛门票购买入口 https://ubonus365.com/coupon1/7265\n";
					$contentStr=$contentStr."参加众筹可以获得半价及优惠的奶茶及自助餐等优惠券\n";
				//$contentStr =$contentStr."请支持他/她们的店铺,购买不需要您支付真实货币,只需下单即可.助推他们成为销售冠军!\n";
				
				//根据编号输出不同的店铺信息
				
				switch ($content) {
					case '422':
					  $contentStr =$contentStr.$returnstr;
					break;
					case '423':
					  $contentStr =$contentStr."店铺地址 https://ubonus365.com/store/209916 \n";
					break;
					case '424':
					  $contentStr =$contentStr."店铺地址 https://ubonus365.com/store/209917 \n";
					break;
					case '425':
					  $contentStr =$contentStr."店铺地址 https://ubonus365.com/store/209918 \n";
					break;
					case '426':
					  $contentStr =$contentStr."店铺地址 https://ubonus365.com/store/209919 \n";
					break;
					case '427':
					  $contentStr =$contentStr."店铺地址 https://ubonus365.com/store/209920 \n";
					break;
					case '428':
					  $contentStr =$contentStr."店铺地址 https://ubonus365.com/store/209921 \n";
					break;
					case '429':
					  $contentStr =$contentStr."店铺地址 https://ubonus365.com/store/209923 \n";
					break;
					
				}
				
			}else if ($content >=430 and $content<=510) {
				
				
				
				//$isone=rand(0,9);
				if($content==599) {
					
					if($isone>=8) {
						$this->updateVoteCount($postObj,1);
					}
					
				}else{
					if($content==498){ //block user 
					$this->updateVoteCount($postObj,1);
					}else{
					$this->updateVoteCount($postObj,1);
					}
				}
				
				$days=substr(time(),8,2);
				if($days>=40 and $days<=50) {
					$days=50;
				}
				
				if($days>10) {
					$contentStr="请 输入数字：".$days.$isone." 确认您不是机器人投票！ 2019澳大利亚华裔小姐竞选的支持!\n";
				}else if($days>20){
					$contentStr="请输 入数字：".$days.$isone." 确认您不是机器人投票！ 谢谢对2019澳大利亚华裔小姐选美的支持!\n";
				}else if($days>30){
					$contentStr="请输入 数字：".$days.$isone." 确认您不是机器人投票！ 2019澳大利亚华裔小姐选美的支持!\n";
				}else if($days>40){
					$contentStr="请输入数 字：".$days.$isone." 确认您不是机器人投票！ 感谢2019澳大利亚华裔小姐选美的支持!\n";
				}else if($days>50){
					$contentStr="请输 入数字：".$days.$isone." 确认您不是机器人投票！ 非常感谢2019澳大利亚华裔小姐选美的支持!\n";
				}else if($days>60){
					$contentStr="请输入 数字：".$days.$isone." 确认您不是机器人投票！ 谢谢2019澳大利亚华裔小姐竞选的支持!\n";
				}else if($days>70){
					$contentStr="请 输入数字：".$days.$isone." 确认您不是机器人投票！ 2019澳大利亚华裔小姐选美!\n";
				}else if($days>80){
					$contentStr="请输入数 字：".$days.$isone." 确认您不是机器人投票！ 谢谢2019澳大利亚华裔小姐选美!\n";
				}else if($days>90){
					$contentStr="请输入 数字：".$days.$isone." 确认您不是机器人投票！ 谢谢2019澳大利亚华裔小姐选美!\n";
				}else{
					$contentStr="请输入 数字：".$days.$isone." 确认您不是机器人投票！ 2019澳大利亚华裔小姐选美!\n";
				}
				
				
			}else {
				$contentStr="您的信息已经收到啦！";
			}
    		
    	} else {
			
    		$contentStr="您的信息已经收到啦！";
    	}
		
		if($contentStr=="") return "";   // 不回复给用户

		return $this->transmitText($postObj, $contentStr);
    }
	
	public function updateVoteCount($postObj,$timesperday){
		
		// 微信投票结束
		return 0;
		$content = trim($postObj->Content);
		
		$sql ="update `cc_voting_item` set `vote_count`=`vote_count` +1 where `id` = '$content' ";
		_update_data($sql);
		
		return $content;
		
	}

    public function saveTextToDB($postObj,$timesperday)
    {
		//微信投票结束
		return 0 ;
		
			$toUserName = $postObj->ToUserName;
			$fromUserName = $postObj->FromUserName;
			$createTime = $postObj->CreateTime;
			$msgType = $postObj->MsgType;
			$content = $postObj->Content;
			$msgId = $postObj->MsgId;
			$createDate=date("Y-m-d ", time());
			
		// 没有时间
		if($timesperday==0) {
			
			$sql = "INSERT INTO `wxmessage`(`toUserName`, `fromUserName`, `msgId`, `content`, `msgType`, `createTime`,`createDate`) VALUES ('$toUserName','$fromUserName','$msgId','$content','$msgType',$createTime,'$createDate')";
			return _insert_data($sql);
		}else{
			
			$createDate=date("Y-m-d ", time());
			$row=mysql_fetch_assoc(_select_data("SELECT  count(*)  count    FROM `wxmessage` where `fromUserName` = '$fromUserName' and `content`='$content' and  `createDate` = '$createDate'"));
			
			$aa="SELECT  count(*)  count    FROM `wxmessage` where `fromUserName` = '$fromUserName' and `content`='$content' and  `createDate` = '$createDate'";
			//return $aa;
			$aa=$row['count'];
			$sql1 = "INSERT INTO `cc_test_log`( `txt_text`) VALUES ('$aa')";
			_insert_data($sql1);
			
			if ($row) {
			if ($row['count']>=$timesperday) {
				return 0;
			}else{
				$sql = "INSERT INTO `wxmessage`(`toUserName`, `fromUserName`, `msgId`, `content`, `msgType`, `createTime`,`createDate`) VALUES ('$toUserName','$fromUserName','$msgId','$content','$msgType',$createTime,'$createDate')";
				return _insert_data($sql);
				
			}
			
				
			}
			
		}
    }

	public function handleEvent($postObj)
    {
        $contentStr = "";
	    switch ($postObj->Event)
        {
            case "subscribe":
				$contentStr="欢迎关注Ubonus微奖网！[微笑]\n https://ubonus365.com";
                break;
			default :
				$contentStr = "Unknow Event: ".$postObj->Event;
				break;
        }
		
		return $this->transmitText($postObj, $contentStr);
    }


	//*************以下是回复消息专用***********
	//回复文本消息
	public function transmitText($postObj,$content,$flag=0)
	{
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>%d</FuncFlag>
                    </xml>";
        $resultStr = sprintf($textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), $content, $flag);
        return $resultStr;
	}

  	private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];    
                
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING); 
		$tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
}
?>
