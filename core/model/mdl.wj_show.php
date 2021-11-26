<?php

class mdl_wj_show extends mdl_base
{
	const STAGE_DIRECTION_TOP='top';
	const STAGE_DIRECTION_BOTTOM='bottom';

	const HORIZONTAL_ORIENTATION_RIGHT='right';
	const HORIZONTAL_ORIENTATION_LEFT='left';

	protected $tableName = '#@_wj_show';

	public function getShowAndStadium($couponId){
		$where['couponId']=$couponId;
		$result = $this->getByWhere($where);
		$data['show_id']=$result['id'];
		$data['stadium_id']=$result['stadium_id'];
		$data['file']=$result['templateFile'];
		$data['stage_direction']=$result['show_direction'];
		return $data;
	}

	public function getDirection($showId){
		$where['id']=$showId;
		$result = $this->getByWhere($where);
		if($result){
			if($result['show_direction']==self::STAGE_DIRECTION_TOP)return self::STAGE_DIRECTION_TOP;
			if($result['show_direction']==self::STAGE_DIRECTION_BOTTOM) return self::STAGE_DIRECTION_BOTTOM;
			//defaule
			return self::STAGE_DIRECTION_TOP;
		}else{
			//default
			return self::STAGE_DIRECTION_TOP;
		}
	}

	public function getHorizontalOrientation($couponId){
		$where['couponId']=$couponId;
		$result = $this->getByWhere($where);
		$result['horizontal_orientation'];
		
		if($result){
			if($result['horizontal_orientation']==self::HORIZONTAL_ORIENTATION_RIGHT)return self::HORIZONTAL_ORIENTATION_RIGHT;
			if($result['horizontal_orientation']==self::HORIZONTAL_ORIENTATION_LEFT)return self::HORIZONTAL_ORIENTATION_LEFT;
			//default
			return self::HORIZONTAL_ORIENTATION_RIGHT;
		}else{
			return self::HORIZONTAL_ORIENTATION_RIGHT;
		}	
	}
}

?>