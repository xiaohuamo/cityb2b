<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/23 0023
 * Time: 下午 5:42
 */

class mdl_wholesale extends mdl_base
{
    protected $tableName = 'cc_wholesale';
    function getwholesale($id)
    {	
    	$where['couponid']=$id;
    	
        return $this->getByWhere($where);
    }

    function delete($id)
    {
        return $this->db->delete($this->tableName,  "couponid='$id'" );
    }

    /**
     * take raw post data to create new wholesale record
     * @param  [type] $couponId    [description]
     * @param  [type] $storeId     [description]
     * @param  [type] $amountArray [description]
     * @param  [type] $priceArray  [description]
     * @return [type]              [description]
     */
    function saveWholesale($couponId,$storeId,$amountArray,$priceArray){
    	if(!is_array($amountArray)||!is_array($priceArray))return false;

    	if(sizeof($amountArray)!=sizeof($priceArray)){
    		// throw new Exception("parameter error", 1);
    		return false;
    	}

    	for ($i=0; $i < sizeof($amountArray); $i++) { 
    		if(!$amountArray[$i]>0||!$priceArray[$i]>0){
    			unset($amountArray[$i]);
    			unset($priceArray[$i]);
    		}
    	}
    	//reindex
    	$amountArray=array_values($amountArray);
    	$priceArray=array_values($priceArray);


		$amount  =$amountArray[0];
		$price   =$priceArray[0];
		
		$amount1 =$amountArray[1];
		$price1  =$priceArray[1];
		
		$amount2 =$amountArray[2];
		$price2  =$priceArray[2];

		$data['couponid']=$couponId;
    	$data['amount']=$amount;
    	$data['price']=$price;
    	$data['amount1']=$amount1;
    	$data['price1']=$price1;
    	$data['amount2']=$amount2;
		$data['price2']=$price2;
		$data['storeid']=$storeId;

        if($this->getByWhere(array('couponid'=>$couponId))){
        	$where['couponid']=$couponId;

        	$this->updateByWhere($data,$where);

        }else{

        	$this->insert($data);
        }


       
    }
}
