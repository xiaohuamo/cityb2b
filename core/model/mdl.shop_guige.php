<?php

class mdl_shop_guige extends mdl_base
{

	protected $tableName = '#@_shop_guige';


	public function couponHasGuige($couponId){
		$coupon = loadModel('coupons')->get($couponId);

		$where['userId']=$coupon['createUserId'];
		$where['is_for_one_product']=0;
		$data = $this->getByWhere($where);


		$where1['coupon_id']=$couponId;
		$data1= $this->getByWhere($where1);

		if($coupon['useguige']){
			return ($data || $data1);
		}else{
			return false;
		}
		
	}

	public function getSingleGuigeData($couponId,$guige1Id,$guige2Id){
		$shop_stock = loadModel('shop_stock');
		$stock = $shop_stock->getStock($couponId,$guige1Id,$guige2Id);
		
		$guige_link =loadModel('guige_link')->getGuigeLink($couponId,$guige1Id,$guige2Id);

		if($guige_link!=0&&$guige_link!=$couponId){
			$stock=$shop_stock->getStock($guige_link,$guige1Id,$guige2Id);

			$couponData = loadModel('coupons')->get($guige_link);//must be type 9

			$embedHtmlSource = "$stock<script type='text/javascript'>function gotoguige(){window.location = '".HTTP_ROOT_WWW."coupon/$guige_link?guige1Id=$guige1Id&guige2Id=$guige2Id';}</script>";
			$embedHtmlSource .= "<div id='embedHtmlSource' style='display:none' data-id='$guige_link' data-title='".$couponData['title']."' data-price ='".$couponData['voucher_deal_amount']."' data-o-price='".$couponData['voucher_original_amount']."' data-pic = '".UPLOAD_PATH.$couponData['pic']."' data-stock='$stock'></div>";
			return $embedHtmlSource;
		}else{
			return $stock;
		}
	}

	/**
	 * 用户产品显示 将规格储存码 转换给二位数组
	 * @param  [type] $stripCode [description]
	 * @return [type]            [description]
	 */
	public function getGuigeFromStripCode($stripCode,$language)
	{	
		//21,22#36,37,38,39,48,49,50,51
		$parts = explode('#', $stripCode);

		$mdl_shop_guige_details =loadModel('shop_guige_details');

		$sql1 ="select details.id,guige.name as guige_name,guige.name_en as guige_name_en,guige.is_pic_style,details.name,details.name_en,details.pic,guige.id as guige_id  from cc_shop_guige_details details left join cc_shop_guige guige on details.guige_id =guige.id where details.id in (".$parts[1].") order by guige.id";

		$list = $mdl_shop_guige_details->getListBySql($sql1);

		$guige=array(); // 记录新的规格数组
		$guige_details=array(); // 记录新的规格数组中的包含可选规格列表的的元素的数组项

		$newKey=0;// 新数组索引
		$details_index=0;
		$firstTimein=1;
		$old_guige_id =0;

		foreach ($list as $key => $val) {
			if($firstTimein){
				$firstTimein=0;
				$old_guige_id=$val['guige_id'];
				if($language =='en') {
					  if($val['guige_name_en']){
						  $guige[$newKey]['guige_name']=$val['guige_name_en'];
						   
					  }else{
						  $guige[$newKey]['guige_name']=$val['guige_name'];
					  }
				}else{
					$guige[$newKey]['guige_name']=$val['guige_name'];
				}
				 
				 if($language =='en') {
					  if($val['name_en']){
						  $val['name']=$val['name_en'];
						   
					  }
				}
				 
				$guige[$newKey]['is_pic_style']=$val['is_pic_style'];
				$guige[$newKey]['pic']=$val['pic'];
				$guige[$newKey]['guige_id']=$val['guige_id'];
				$guige_details[$details_index]=$val;
				$details_index ++;
				$guige[$newKey]['guige_details']=$guige_details; // 这句是如果当前只有一条记录 也会被放入数组
			}else{
				//判断，如果到了一个新的规格类型那么将当前的数组完成
				if ($val['guige_id'] !=$old_guige_id){

		          $newKey ++;// 新建一个规格
		          $details_index =0; // 子规格的参数元素的数组索引回0
		          $guige_details=array(); // 清空自规格参数元素数组清0
				  
		          if($language =='en') {
					  if($val['guige_name_en']){
						  $guige[$newKey]['guige_name']=$val['guige_name_en'];
						
					  }else{
						  $guige[$newKey]['guige_name']=$val['guige_name'];
					  }
				}else{
					$guige[$newKey]['guige_name']=$val['guige_name'];
				}
				
				  if($language =='en') {
					  if($val['name_en']){
						  $val['name']=$val['name_en'];
						   
					  }
				}
		          $guige[$newKey]['is_pic_style']=$val['is_pic_style'];
		          $guige[$newKey]['pic']=$val['pic'];
		          $guige[$newKey]['guige_id']=$val['guige_id'];
		          $guige_details[$details_index]=$val;// 规格的 明细列表-暂时只有一个
		          $details_index ++;
		          $guige[$newKey]['guige_details']=$guige_details;
		          $old_guige_id=$val['guige_id'];	
		      }else{
				  if($language =='en') {
					  if($val['name_en']){
						  $val['name']=$val['name_en'];
						   
					  }
				}	
		      	$guige_details[$details_index]=$val;
		      	$guige[$newKey]['guige_details']=$guige_details;
		      	$details_index ++;
		      }
		  }
		}
		
		return $guige;
	}


	/**
	 * 生成 二维规格id 和库存的 对应关系数组
	 * @param  [type] $couponId [couponId]
	 * @param  [type] $guige    [description]
	 * @return [type]           [description]
	 */
	public function getGuigeStockDataFromGuige($id,$guige)
	{
		$guigeData=array();

	  	if(sizeof($guige)==1){
	   	  	//一维规格
	   	  	$guige1list = $guige[0]['guige_details'];
	   	  	foreach ($guige1list as $guige1) {
		  		$key = $guige1['id'].'#undefined';
		  		$guigeData[$key]=$this->getSingleGuigeData($id,$guige1['id'],$guige2['id']);
	   	  	}
		}elseif(sizeof($guige)==2){
			//二维规格
			$guige1list = $guige[0]['guige_details'];
			$guige2list = $guige[1]['guige_details'];

			foreach ($guige1list as $guige1) {
				foreach ($guige2list as $guige2) {
					$key = $guige1['id'].'#'.$guige2['id'];
					$guigeData[$key]=$this->getSingleGuigeData($id,$guige1['id'],$guige2['id']);
				}
			}
		}elseif(!$guige){
			$guigeData['undefined#undefined']=loadModel('shop_stock')->getStock($id,-1,-1);
		}

		return $guigeData;

	}
}

?>