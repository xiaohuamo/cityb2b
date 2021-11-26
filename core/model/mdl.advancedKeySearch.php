<?php

class mdl_advancedKeySearch extends mdl_base{
	private $resultKey;
	private $location;

	public function advancedKeyCalculation($key){
		//First check for location
		$originalKey=$key;

		$location=$this->containsLocation($key);
		if($location){
			$key = str_ireplace($location,'',$key);
			$key = str_ireplace($this->solidString($location),'',$this->solidString($key));
		}
		
		$this->location=$location['id'];
		//echo $location;
		//Break the rest down
		$parts = $this->subKey($originalKey);

		foreach ($parts as $k => $value) {
			if(strpos($key, $value)===false&& $originalKey!=$value)unset($parts[$k]);
		}

		//switch each part with its synonyms
		$result =array();
		foreach ($parts as $p) {
			$result = array_merge($result,$this->getSynonym($p));
		}

		$this->resultKey=$result;

		return $result;
	}

	public function getKeySql($sqlHead,$flag){
		$operation = ($flag)?' like ':' = ';// true make it a like.
		$wildCard = ($flag)?'%':'';
		$logicOperator = ' or ';

		$sql =array();

		foreach ($this->resultKey as $k) {
			$sql[]= $sqlHead . $operation ."'". $wildCard . $k . $wildCard . "'";
		}

		return join($logicOperator,$sql);
	}

	public function getLocation(){
		return $this->location;
	}

	function subKey($key){
		//if a key is separated by space, break it down to sub keys
		return preg_split('/\s+/', trim($key));
	}

	function solidString($string){
		return preg_replace('/\s+/', '', $string);
	}

	function containsLocation($string){
		//if a key contians a string of location, extract it out as a search condition
		/* test infomation
		$locationList=array(
			'新南威尔士'=>'New South Wales',
			'悉尼'=>'Sydney',
			'奥尔伯里'=>'Albury',
			'阿米代尔'=>'Armidale',
			'巴瑟斯特'=>'Bathurst',
			'布罗肯希尔'=>'Broken Hill',
			'塞斯诺克'=>'Cessnock',
			'科夫斯港'=>'Coffs Harbour',
			'达博'=>'Dubbo',
			'戈斯福'=>'Gosford',
			'古尔本'=>'Goulburn',
			'格拉夫顿'=>'Grafton',
			'格里菲斯'=>'Griffith',
			'麦觉理湖'=>'Lake Macquarie',
			'利斯摩尔'=>'Lismore',
			'梅特兰'=>'Maitland',
			'纽卡斯尔'=>'Newcastle',
			'诺拉'=>'Nowra',
			'奥兰治'=>'Orange',
			'麦觉理港'=>'Port Macquarie',
			'昆比恩'=>'Queanbeyan',
			'塔姆沃思'=>'Tamworth',
			'堤维德岬'=>'Tweed Heads',
			'沃加沃加'=>'Wagga Wagga',
			'伍伦贡'=>'Wollongong',
			'怀昂'=>'Wyong',
			'维多利亚州'=>'Victoria' ,
			'墨尔本'=>'Melbourne',
			'雅拉瑞特'=>'Ararat',
			'本纳拉'=>'Benalla',
			'巴拉腊特'=>'Ballarat',
			'本迪戈'=>'Bendigo',
			'吉朗'=>'Geelong',
			'米尔迪拉'=>'Mildura',
			'雪帕顿'=>'Shepparton',
			'天鹅山'=>'Swan Hill',
			'旺加拉塔'=>'Wangaratta',
			'瓦南布尔'=>'Warrnambool',
			'沃东加'=>'Wodonga',
			'马里伯勒'=>'Maryborough',
			'昆士兰州'=>'Queensland',
			'布里斯班'=>'Brisbane',
			'班德堡'=>'Bundaberg',
			'凯恩斯'=>'Cairns',
			'格列士敦'=>'Gladstone',
			'黄金海岸'=>'Gold Coast',
			'金彼'=>'Gympie',
			'赫维湾'=>'Hervey Bay',
			'伊普斯威奇'=>'Ipswich',
			'罗根'=>'Logan City',
			'麦凯'=>'Mackay',
			'伊萨山'=>'Mt. Isa',
			'洛坎普顿'=>'Rockhampton',
			'阳光海岸'=>'Sunshine Coast',
			'图文巴'=>'Toowoomba',
			'汤斯维尔'=>'Townsville',
			'西澳大利亚州'=>'Western Australia ',
			'伯斯'=>'Perth' ,
			'奥班尼'=>'Albany',
			'布鲁姆'=>'Broome',
			'班伯利'=>'Bunbury',
			'杰拉尔顿'=>'Geraldton',
			'费利曼图'=>'Fremantle',
			'卡尔古利'=>'Kalgoorlie',
			'曼哲拉'=>'Mandurah',
			'黑德兰港'=>'Port Hedland',
			'南澳大利亚州'=>'South Australia' ,
			'阿德莱德'=>'Adelaide',
			'甘比尔山'=>'Mount Gambier',
			'梅里韦尔桥'=>'Merivale Bridge',
			'奥古斯塔港'=>'Port Augusta',
			'皮里港'=>'Port Pirie',
			'林肯港'=>'Port Lincoln',
			'维克多港'=>'Victor Harbor',
			'怀阿拉'=>'Whyalla',
			'塔斯曼尼亚'=>'Tasmania',
			'霍巴特'=>'Hobart',
			'伯尔尼'=>'Burnie',
			'德文波特'=>'Devonport',
			'阿瑟港'=>'Port Arthur',
			'朗赛斯顿'=>'Launceston',
			'罗斯'=>'Ross',
			'里奇蒙'=>'Richmond',
			'布鲁尼岛'=>'Bruny Island',
			'圣海伦斯'=>'St Helens',
			'摇篮山'=>'Cradle Mountain',
			'戈登坝'=>'Gordon Dam',
			'北领地'=>'Northern Territory ',
			'达尔文'=>'Darwin' ,
			'艾丽斯斯普林斯'=>'Alice Springs',
			'凯瑟琳'=>'Katherine',
			'威尔顿'=>'Wilton');
		*/

		$sql='select id, name from #@_city';
		$locationList=$this->getListBySql($sql);
		//var_dump($locationList);
		foreach ($locationList as $key => $value) {
			// if(strpos($this->solidString($string), $this->solidString($key))!==false){
			// 	return $key;
			// }
			if(strpos($this->solidString($string), $this->solidString($value['name']))!==false){
				return $value;
			}	
		}

		return false;
	}

	function getSynonym($word){
		//if a word has synonyms, return all its synoyms
		$dictionary=array('房地产'=>'房产#地产#固定资产#不动产');

		if(array_key_exists($word, $dictionary)){
			$result=explode('#', $dictionary[$word]);
			array_push($result, $word);
			return $result;
		}else{
			return array($word);
		}
	}

	//additional possible string functions: //soundex  //Levenshtein(edit) distance  //similar_text

}

?>