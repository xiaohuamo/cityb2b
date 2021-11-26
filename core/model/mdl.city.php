<?php

class mdl_city extends mdl_base
{

	protected $tableName = '#@_city';

	public function getParents( $id ) {
		$cat = $this->get( (int)$id );
		if ( !$cat ) return null;
		if ( !$cat['map'] ) return array( $cat );

		return $this->getList( null, array( "id in (".str_replace( '-', ',', $cat['map'] ).")" ), 'map asc' );
	}

	public function getChild ( $pid = 0 ) {
		static $catArr = array();

		$where = array();
		$where['parentId'] = (int)$pid;
		if ( $this->lang ) $where['lang'] = $this->getLang();

		$re = $this->db->select( '*', $this->tableName, $where, 'sortnum asc' );
		while ( $ro = $this->db->fetch_array( $re ) ) {
			$catArr[]	= array(
				'id'					=> $ro['id'],
				'sortnum'				=> $ro['sortnum'],
				'parentId'				=> $ro['parentId'],
				'name'					=> $ro['name'],
				'en_name'				=> $ro['en_name'],
				'map'					=> $ro['map'],
				'status'				=> $ro['status'],
				'level'					=> count( explode( '-', $ro['map'] ) ),
				'hasSon'				=> $this->getCount( array( 'parentId' => $ro['id'] ) ) > 0 ? 1 : 0,
			);
			$this->getChild( $ro['id'] );
		}

		if ( is_array( $catArr ) && count( $catArr ) ) return $catArr;
		else return null;
	}

	public function getChildTree($pid = 0 )
	{
		$list=$this->getList(null,array('parentId'=>$pid,'status'=>1),'sortnum');
		foreach ($list as $key => $value) {
			$list[$key]['child']=$this->getList(null,array('parentId'=>$value['id'],'status'=>1),'sortnum');

				foreach($list[$key]['child'] as $k => $v){
						$list[$key]['child'][$k]['child']=$this->getList(null,array('parentId'=>$v['id'],'status'=>1),'sortnum');
				}
		}
		return $list;
	}


	public function getCityListByLevel($level = -1){
		$sql = 'select * from '.$this->tableName.' where city_level = '.$level;
		return $this->getListBySql($sql);

	}

	public function getChildLevelList($id){
		$levelstr=$this->getOne('map',array('id'=>$id));
		$levels= explode("-", $levelstr);
		$level=sizeof($levels)-1;

		//level could be 0 1 2 3
		$childLevel = $level + 1;
		if($childLevel>3)$childLevel=3;

		$sql = 'select id ,en_name,name from '.$this->tableName.' where  parentId =' .$id.' order by sortnum';
		return $this->getListBySql($sql);

	}

	
	
	public function getCityName($userCityField){
	
	   $cityInfo =explode(",",$userCityField);
	 
	   arsort($cityInfo);
	   
	   if($cityInfo){
	   //只取2级
	   $i=0;
	   foreach ($cityInfo as $key => $value) { 
			if($value) {
				if($i==2) break;
				$cityname_record= $this->get($value);
				// 将国家地区去掉,并且先显示地区后显示城市
			      if($cityname_record['parentId']==0){}else{
				  $city_name .=' '.$cityname_record['name'];
				  
				$i++;
				  }
			}
	
		}
		
       }else{
		   return 0;
	   }
	   
	   
		return trim($city_name);
	}
	
	
	// 根据用户的所在城市获得该用户所在的区,城市或国家的名称,并以数组方式进行记录
	// 如果一个用户注册在某个区,则返回,区和城市两个名称, 如果一个用户注册在城市,则返回城市,国家两个名称
	// 如果为空,则直接返回当前国家名称.
	
	// $usercityField 的格式可能为 ",ddd,dddd,ddd,"
	public function getCitynameandParentCityname($userCityField,$language1){
	
	   $cityInfo =explode(",",$userCityField);
	 
	   arsort($cityInfo);
	   
	   if($cityInfo){
	   //只取2级
	   $i=0;
	   foreach ($cityInfo as $key => $value) { 
			if($value) {
				if($i==2) break;
				$cityname_record= $this->get($value);
				// 将国家地区去掉,并且先显示地区后显示城市
			      if($language1 =='en') {
					   if ($cityname_record['en_name']){
						    $city_name[$i] =$cityname_record['en_name'];
					   }else{
						    $city_name[$i] =$cityname_record['name'];
					   }
					  
					  
				  }else{
					  
					   $city_name[$i] =$cityname_record['name'];
				  }
				 
				
				$i++;
			}
	
		}
		
       }else{
		   return 0;
	   }
	   
	   
		return $city_name;
	}
}

?>