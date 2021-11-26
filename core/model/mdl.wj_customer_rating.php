<?php

class mdl_wj_customer_rating extends mdl_base
{

	protected $tableName = '#@_wj_customer_rating';


	public function getAvgScore($id){
		$sql ='select AVG(score_avg) as score from '.$this->tableName.' where business_userId = '.$id;;
		$result= $this->getListBySql($sql);
		$score = $result[0]['score'];
		if($score == null)$score = 0;
		return 	$score;
	}

	public function getDetailedScore($id){
		$sql ='select AVG(score_0) as score0 , AVG(score_1) as score1, AVG(score_2) as score2, AVG(score_3) as score3, AVG(score_4) as score4, AVG(score_avg) as scoreavg from '.$this->tableName.' where business_userId = '.$id;
		$result= $this->getListBySql($sql);

		return 	 $result[0];
	}
    public function getRecentCustomerFeedback($couponId){
	    $sql='SELECT DISTINCT (b.order_id), a.userId,a.user_nickname,a.score_avg,a.description,a.createTime FROM '.$this->tableName.' a left join cc_wj_customer_coupon b on b.order_id = a.orderId where a.isApproved=1 and b.bonus_id='.$couponId.' order by createTime DESC  limit 10';
	    $result= $this->getListBySql($sql);
        return  $result;
    }



    public function getScoreCount($id)
    {
    	$where['business_userId'] = $id;

    	return $this->getCount($where);
    }
}

?>