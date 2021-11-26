<?php

class mdl_pick extends mdl_base
{

	protected $tableName = '#@_pick';

	public function get ($id)
	{
		return $this->db->selectOne(null, $this->tableName, "id='$id'");
	}

	public function getListSql ($fields, $where, $order)
	{
		return $this->db->getSelectMultipleSql(array($fields, array('userName' => 'name', 'userDisplayName' => 'displayName')), array($this->tableName, '#@_user'), array('0#createUserId=1#id'), $where, $order);
		//return $this->db->getSelectSql($fields, $this->tableName, $where, $order);
	}

	public function getListBySql ($sql)
	{
		return $this->db->toArray($this->db->query($sql));
	}

	public function getList ()
	{
		return $this->db->toArray($this->db->select(null, $this->tableName));
	}

	public function getListWithoutById ($id)
	{
		return $this->db->toArray($this->db->select(null, $this->tableName, "id<>'$id'"));
	}

	public function add ($data)
	{
		return $this->db->insert($data, $this->tableName);
		//echo $this->db->getInsertSql($data, $this->tableName);exit;
	}

	public function update ($data, $id)
	{
		//echo $this->db->getUpdateSql($data, $this->tableName, "id='$id'");exit;
		return $this->db->update($data, $this->tableName, "id='$id'");
	}

	public function delete ($id)
	{
		return $this->db->delete($this->tableName, array('id' => $id));
	}

	//测试采集
	public function doTest (& $pick, & $list, & $item, $url = null)
	{
		if (trim($pick['list']['list'][0][0]) == ''  && empty($pick['item']['url'])) return;
		include_once(CORE_DIR.'include/class.httpService.php');
		$httpService 	= new httpService(trim($pick['list']['list'][0][0]));
		$httpService->charset = $pick['charset'];
		$listContent	= $httpService->result();
		if (empty($listContent)) $listContent = file_get_contents(trim($pick['list']['list'][0][0]));
		//$listContent	= interception($listContent, strpos($listContent, $pick['list']['htmlstart']), strpos($listContent, $pick['list']['htmlend']));
		$listContent	= self::_getMatch(self::_filterEnter($listContent), $pick['list']['htmlstart'].'(*)'.$pick['list']['htmlend']);  //调整使用正则
		$preg_a			= "<a.+?href=['\"\"]?([^'\"\">]+?)['\"\"]?(>| [^>]*?>)(.{1,}?)<\/a>";
		preg_match_all("/$preg_a/i", $listContent, $list);
		if (trim($pick['list']['list'][0][0]) == '')
		{
			$list		= array();
			$list[1]	= array($pick['item']['url']);
		}
		if (is_array($list))
		{
			$tmplist	= $list;
			if (empty($pick['item']['parentUrl'])) $list = getFullUrl($list[1], $pick['list']['list'][0][0]);
			else $list = getFullUrl($list[1], $pick['item']['parentUrl']);
			$urlfilter	= explode("\r\n", $pick['list']['urlfilter']);
			foreach ($list as $k=>$v)
			{
				if (in_array($v, $urlfilter))
				{
					array_splice($list, $k, 1);
				}
			}

			$listImg	= array();
			foreach ($tmplist[3] as $key=>$value) $listImg[] = array($list[$key], self::_getImg(trim($pick['list']['list'][0][0]), $tmplist[3][$key]));
			unset($httpService);
			//echo empty($url) ? ($pick['item']['url'] ? $pick['item']['url'] : $list[0]) : $url;exit;
			$value = empty($url) ? ($pick['item']['url'] ? $pick['item']['url'] : $list[0]) : $url;
			$httpService = new httpService($value);
			$httpService->charset = $pick['charset'];
			$itemContent = $httpService->result();
			if (empty($itemContent)) $itemContent = file_get_contents($value);
			$itemContent = self::_filterEnter($itemContent);

			//缩略图
			if (is_array($listImg[0][1][0]))
			{
				$item['imageUrl'] = array($listImg[0][1][0][0], $listImg[0][1][1][0]);
			}
			//关键字
			$item['keywords'] = self::_replace(self::_getMatch($itemContent, array("<meta[\s]+name=['\"]keywords['\"] content=['\"](.*)['\"]", "<meta[\s]+content=['\"](.*)['\"] name=['\"]keywords['\"]")), $pick['item']['keywords_replace']);
			//描述
			$item['description'] = self::_replace(self::_getMatch($itemContent, array("<meta[\s]+name=['\"]description['\"] content=['\"](.*)['\"]", "<meta[\s]+content=['\"](.*)['\"] name=['\"]description['\"]")), $pick['item']['description_replace']);

			$itemContent = substr($itemContent, strpos($itemContent, $pick['item']['htmlstart']));
			//标题
			$item['title'] = self::_replace(self::_getMatch($itemContent, $pick['item']['title']), $pick['item']['title_replace']);
			//发布时间
			$item['publishdate'] = date('Y-m-d', strtotime(self::_replace(self::_getMatch($itemContent, $pick['item']['publishdate']), $pick['item']['publishdate_replace'])));
			if ($item['publishdate'] == '1970-01-01') $item['publishdate'] = date('Y-m-d');
			//作者
			$item['author'] = self::_replace(self::_getMatch($itemContent, $pick['item']['author']), $pick['item']['author_replace']);
			//来源
			$item['source'] = self::_replace(self::_getMatch($itemContent, $pick['item']['source']), $pick['item']['source_replace']);
			//内容
			$item['content'] = self::_replace(self::_getMatch($itemContent, $pick['item']['content']), $pick['item']['content_replace']);
			if ( $pick['item']['isJsContent'] && !empty( $pick['item']['jsContent'] ) ) {
				$jsContentUrl = self::_getMatch($item['content'], $pick['item']['jsContent']);
				if ( ! empty( $jsContentUrl ) ) {
					unset($httpService);
					$httpService = new httpService($jsContentUrl);
					$httpService->charset = $pick['charset'];
					$item['content'] = self::_replace($httpService->result(), $pick['item']['content_replace']);
					if (empty($item['content'])) $item['content'] = self::_replace(file_get_contents($jsContentUrl), $pick['item']['content_replace']);
					//自动过滤document.write('');
					$item['content'] = preg_replace( "/document.write\(\\\'/iU", '', $item['content'] );
					$item['content'] = preg_replace( "/\\\'\);/iU", '', $item['content'] );
				}
			}
			$pages = null;
			$page_replace = explode("\r\n", $pick['item']['page_replace']);
			$page_replace[] = $value;
			if ($pagestr = self::_getMatch($itemContent, $pick['item']['page']))
			{
				preg_match_all("/$preg_a/i", $pagestr, $pages);
				$pages = $pages[1];
			}
			if (is_array($pages))
			{
				$item['content'] .= self::_replace(self::_getPageContent($value, $pages, $pick, $page_replace), $pick['item']['content_replace']);
			}
		}
		unset($httpService);
	}

	public function getPickUrlCount($pick) {
		include_once(CORE_DIR.'include/class.httpService.php');

		$itemCount	= 0;

		if ($pick['list']['list'][0][0] != '' && empty($htmlList))
		{
			for ($i = 0; $i < count($pick['list']['list']); $i++)
			{
				for ($j = 0; $j < count($pick['list']['list'][$i]); $j++)
				{
					$httpService = new httpService(trim($pick['list']['list'][$i][$j]));
					$httpService->charset = $pick['charset'];
					$listContent = $httpService->result();
					if (empty($listContent)) $listContent = file_get_contents(trim($pick['list']['list'][$i][$j]));
					//$listContent =  interception($listContent, strpos($listContent, $pick['list']['htmlstart']), strpos($listContent, $pick['list']['htmlend']));
					$listContent	= self::_getMatch(self::_filterEnter($listContent), $pick['list']['htmlstart'].'(*)'.$pick['list']['htmlend']);  //调整使用正则
					$preg_a		 = "<a.+?href=['\"\"]?([^'\"\">]+?)['\"\"]?(>| [^>]*?>)(.{1,}?)<\/a>";
					preg_match_all("/$preg_a/i", $listContent, $list);
					if (is_array($list))
					{
						$tmplist = $list;  //记录原始网址信息
						if (empty($pick['item']['parentUrl'])) $list = getFullUrl($list[1], $pick['list']['list'][$i][$j]);
						else $list = getFullUrl($list[1], $pick['item']['parentUrl']);
						$urlfilter	= explode("\r\n", $pick['list']['urlfilter']);
						foreach ($list as $k=>$v)
						{
							if (in_array($v, $urlfilter))
							{
								array_splice($list, $k, 1);
							}
						}
						$itemCount += count( $list );
					}
					unset($httpService);
				}
			}
		}

		return $itemCount;
	}

	//正式采集
	public function doPick (& $pick, & $urlList, & $urlItem, & $urlItemImg, & $urlListCount, $autoCheck = 0, $perCount = 0, $maxCount = 0, $htmlList, $urlCount)
	{
		include_once(CORE_DIR.'include/class.httpService.php');

		$itemlist	= array();  //采集过的网址
		$cnt		= 0;  //当前采集数量，这里建议以后改成：
		//先获取到所有详细页面的网址，保存到文件或数据库中，然后再循环采集，当达到指定的数量时，带上当前采集的数量跳转到本页面，就可以实现采集进度了。

		if ($pick['list']['list'][0][0] != '' && empty($htmlList))
		{
			for ($i = 0; $i < count($pick['list']['list']); $i++)
			{
				for ($j = 0; $j < count($pick['list']['list'][$i]); $j++)
				{
					$httpService = new httpService(trim($pick['list']['list'][$i][$j]));
					$httpService->charset = $pick['charset'];
					$listContent = $httpService->result();
					if (empty($listContent)) $listContent = file_get_contents(trim($pick['list']['list'][$i][$j]));
					//$listContent =  interception($listContent, strpos($listContent, $pick['list']['htmlstart']), strpos($listContent, $pick['list']['htmlend']));
					$listContent	= self::_getMatch(self::_filterEnter($listContent), $pick['list']['htmlstart'].'(*)'.$pick['list']['htmlend']);  //调整使用正则
					$preg_a		 = "<a.+?href=['\"\"]?([^'\"\">]+?)['\"\"]?(>| [^>]*?>)(.{1,}?)<\/a>";
					preg_match_all("/$preg_a/i", $listContent, $list);
					if (is_array($list))
					{
						$tmplist = $list;  //记录原始网址信息
						if (empty($pick['item']['parentUrl'])) $list = getFullUrl($list[1], $pick['list']['list'][$i][$j]);
						else $list = getFullUrl($list[1], $pick['item']['parentUrl']);
						$urlfilter	= explode("\r\n", $pick['list']['urlfilter']);
						foreach ($list as $k=>$v)
						{
							if (in_array($v, $urlfilter))
							{
								array_splice($list, $k, 1);
							}
						}
						
						//检测是否需要处理链接中的图片
						if ($pick['list']['img'])
						{
							$listImg	= array();  //列表链接中的图片
							foreach ($tmplist[3] as $key=>$value) $listImg[] = array($list[$key], self::_getImg(trim($pick['list']['list'][$i][0]), $tmplist[3][$key]));
						}
						unset($httpService);

						foreach ($list as $key=>$value)
						{
							$itemContent = '';
							if (in_array($value, $itemlist)) continue;
							$itemlist[]	= $value;  //记录当前采集的网址
							//检测数据库中是否存在
							if ( $this->db->getCount( '#@_pick_html', "url='".$value."'" ) > 0 ) {
								$cnt++;
								continue;
							}
							$tmpItem	= array();
							$httpService = new httpService($value);
							$httpService->charset = $pick['charset'];
							$itemContent = $httpService->result();
							if (empty($itemContent)) $itemContent = file_get_contents($value);
							$itemContent = self::_filterEnter($itemContent);

							//分类，由于新增支持批量采集，所以在采集时自动加上分类
							$tmpItem['classId']		= trim($pick['outClassId'][$i]);
							//审核
							$tmpItem['isApproved']	= $autoCheck;
							//源网址
							$tmpItem['sourceHtml']	= $value;
							//缩略图
							if ($pick['list']['img'])
							{
								if (is_array($listImg[$key][1][0]))
								{
									$urlItemImg[] = array(array($listImg[$key][1][0][0]), array($listImg[$key][1][1][0]));
									$tmpItem['imageUrl'] = $listImg[$key][1][1][0];  //只采集链接中出现的第一张图
								}
							}
							//关键字
							$tmpItem['keywords'] = self::_replace(self::_getMatch($itemContent, array("<meta[\s]+name=['\"]keywords['\"] content=['\"](.*)['\"]", "<meta[\s]+content=['\"](.*)['\"] name=['\"]keywords['\"]")), $pick['item']['keywords_replace']);
							//描述
							$tmpItem['description'] = self::_replace(self::_getMatch($itemContent, array("<meta[\s]+name=['\"]description['\"] content=['\"](.*)['\"]", "<meta[\s]+content=['\"](.*)['\"] name=['\"]description['\"]")), $pick['item']['description_replace']);

							$itemContent = substr($itemContent, strpos($itemContent, $pick['item']['htmlstart']));
							//标题
							$tmpItem['title'] = self::_replace(self::_getMatch($itemContent, $pick['item']['title']), $pick['item']['title_replace']);
							//发布时间
							$tmpItem['publishdate'] = date('Y-m-d', strtotime(self::_replace(self::_getMatch($itemContent, $pick['item']['publishdate']), $pick['item']['publishdate_replace'])));
							if ($tmpItem['publishdate'] == '1970-01-01') $tmpItem['publishdate'] = date('Y-m-d');
							//作者
							$tmpItem['author'] = self::_replace(self::_getMatch($itemContent, $pick['item']['author']), $pick['item']['author_replace']);
							//来源
							$tmpItem['source'] = self::_replace(self::_getMatch($itemContent, $pick['item']['source']), $pick['item']['source_replace']);
							//内容
							$tmpItem['content'] = self::_replace(self::_getMatch($itemContent, $pick['item']['content']), $pick['item']['content_replace']);
							if ( $pick['item']['isJsContent'] && !empty( $pick['item']['jsContent'] ) ) {
								$jsContentUrl = self::_getMatch($tmpItem['content'], $pick['item']['jsContent']);
								if ( ! empty( $jsContentUrl ) ) {
									unset($httpService);
									$httpService = new httpService($jsContentUrl);
									$httpService->charset = $pick['charset'];
									$tmpItem['content'] = self::_replace($httpService->result(), $pick['item']['content_replace']);
									if (empty($tmpItem['content'])) $tmpItem['content'] = self::_replace(file_get_contents($jsContentUrl), $pick['item']['content_replace']);
									//自动过滤document.write('');
									$tmpItem['content'] = preg_replace( "/document.write\(\\\'/iU", '', $tmpItem['content'] );
									$tmpItem['content'] = preg_replace( "/\\\'\);/iU", '', $tmpItem['content'] );
								}
							}
							$pages = null;
							$page_replace = explode("\r\n", $pick['item']['page_replace']);
							$page_replace[] = $value;
							if ($pagestr = self::_getMatch($itemContent, $pick['item']['page']))
							{
								preg_match_all("/$preg_a/i", $pagestr, $pages);
								$pages = $pages[1];
							}
							if (is_array($pages))
							{
								$tmpItem['content'] .= self::_replace(self::_getPageContent($value, $pages, $pick, $page_replace), $pick['item']['content_replace']);
							}
							unset($httpService);
							$img = self::_getImg($value, $tmpItem['content']);
							if (is_array($img[0]))
							{
								if ($pick['item']['downimg'])  //下载内容中图片
								{
									$urlItemImg[] = $img;
									$tmpItem['images'] = implode(',', $img[1]);
								}
								else  //不下载内容中图片，所以将图片地址补全
								{
									foreach ($img[1] as $key=>$value)
									{
										$tmpItem['content'] = str_replace(UPLOAD_PATH.$value, $img[0][$key], $tmpItem['content']);
									}
								}
							}
							$urlItem[] = $tmpItem;
							unset($tmpItem);
							$cnt++;
							echo '<script type="text/javascript"> pickResultPreloader.css( \'width\', ( '.$cnt.' / '.$urlCount.' ) * 100 + \'%\' ); pickResultPreloaderText.html( '.$cnt.' ); </script>';
							ob_flush();
							if ( $maxCount > 0 && $cnt >= $maxCount ) {
								return true;
							}
						}
						$urlList[$i] = $list;
						$urlListCount += count($list);
					}
					unset($httpService);
				}
			}
		}
		else if (!empty($pick['item']['url']) || !empty($htmlList))
		{
			if (!is_array($htmlList))
			{
				$htmlList = array($pick['item']['url']);
			}
			foreach ($htmlList as $lk=>$lv)
			{
				$value			= $lv;
				//检测数据库中是否存在
				if ( $this->db->getCount( '#@_pick_html', "url='".$value."'" ) > 0 ) {
					$cnt++;
					continue;
				}
				$itemContent	= '';
				$tmpItem		= array();
				$httpService 	= new httpService($value);
				$httpService->charset = $pick['charset'];
				$itemContent 	= $httpService->result();
				if (empty($itemContent)) $itemContent = file_get_contents($value);
				$itemContent = self::_filterEnter($itemContent);

				//分类，由于新增支持批量采集，所以在采集时自动加上分类
				$tmpItem['classId']		= trim($pick['outClassId'][0]);
				//审核
				$tmpItem['isApproved']	= $autoCheck;
				//源网址
				$tmpItem['sourceHtml']	= $value;
				//关键字
				$tmpItem['keywords'] = self::_replace(self::_getMatch($itemContent, array("<meta[\s]+name=['\"]keywords['\"] content=['\"](.*)['\"]", "<meta[\s]+content=['\"](.*)['\"] name=['\"]keywords['\"]")), $pick['item']['keywords_replace']);
				//描述
				$tmpItem['description'] = self::_replace(self::_getMatch($itemContent, array("<meta[\s]+name=['\"]description['\"] content=['\"](.*)['\"]", "<meta[\s]+content=['\"](.*)['\"] name=['\"]description['\"]")), $pick['item']['description_replace']);

				$itemContent = substr($itemContent, strpos($itemContent, $pick['item']['htmlstart']));
				//标题
				$tmpItem['title'] = self::_replace(self::_getMatch($itemContent, $pick['item']['title']), $pick['item']['title_replace']);
				//发布时间
				$tmpItem['publishdate'] = date('Y-m-d', strtotime(self::_replace(self::_getMatch($itemContent, $pick['item']['publishdate']), $pick['item']['publishdate_replace'])));
				if ($tmpItem['publishdate'] == '1970-01-01') $tmpItem['publishdate'] = date('Y-m-d');
				//作者
				$tmpItem['author'] = self::_replace(self::_getMatch($itemContent, $pick['item']['author']), $pick['item']['author_replace']);
				//来源
				$tmpItem['source'] = self::_replace(self::_getMatch($itemContent, $pick['item']['source']), $pick['item']['source_replace']);
				//内容
				$tmpItem['content'] = self::_replace(self::_getMatch($itemContent, $pick['item']['content']), $pick['item']['content_replace']);
				if ( $pick['item']['isJsContent'] && !empty( $pick['item']['jsContent'] ) ) {
					$jsContentUrl = self::_getMatch($tmpItem['content'], $pick['item']['jsContent']);
					if ( ! empty( $jsContentUrl ) ) {
						unset($httpService);
						$httpService = new httpService($jsContentUrl);
						$httpService->charset = $pick['charset'];
						$tmpItem['content'] = self::_replace($httpService->result(), $pick['item']['content_replace']);
						if (empty($tmpItem['content'])) $tmpItem['content'] = self::_replace(file_get_contents($jsContentUrl), $pick['item']['content_replace']);
						//自动过滤document.write('');
						$tmpItem['content'] = preg_replace( "/document.write\(\\\'/iU", '', $tmpItem['content'] );
						$tmpItem['content'] = preg_replace( "/\\\'\);/iU", '', $tmpItem['content'] );
					}
				}
				$pages 	= null;
				$preg_a	= "<a.+?href=['\"\"]?([^'\"\">]+?)['\"\"]?(>| [^>]*?>)(.{1,}?)<\/a>";
				$page_replace = explode("\r\n", $pick['item']['page_replace']);
				$page_replace[] = $value;
				if ($pagestr = self::_getMatch($itemContent, $pick['item']['page']))
				{
					preg_match_all("/$preg_a/i", $pagestr, $pages);
					$pages = $pages[1];
				}
				if (is_array($pages))
				{
					$tmpItem['content'] .= self::_replace(self::_getPageContent($value, $pages, $pick, $page_replace), $pick['item']['content_replace']);
				}
				unset($httpService);
				$img = self::_getImg($value, $tmpItem['content']);
				if (is_array($img[0]))
				{
					if ($pick['item']['downimg'])  //下载内容中图片
					{
						$urlItemImg[] = $img;
						$tmpItem['images'] = implode(',', $img[1]);
					}
					else  //不下载内容中图片，所以将图片地址补全
					{
						foreach ($img[1] as $key=>$value)
						{
							$tmpItem['content'] = str_replace(UPLOAD_PATH.$value, $img[0][$key], $tmpItem['content']);
						}
					}
				}
				$urlItem[] = $tmpItem;
				$cnt++;
				echo '<script type="text/javascript"> pickResultPreloader.css( \'width\', ( '.$cnt.' / '.$urlCount.' ) * 100 + \'%\' ); pickResultPreloaderText.html( '.$cnt.' ); </script>';
				ob_flush();
				if ( $maxCount > 0 && $cnt >= $maxCount ) {
					return true;
				}
			}
		}
	}

	private function _filterExp ($str)
	{
		return str_replace("\r\n", '', str_replace('"', '\"', str_replace('/', '\/', str_replace('(*)', '(.*)', $str))));
	}

	private function _filterEnter ($str)
	{
		return str_replace("\n", '', str_replace("\r\n", '', $str));
	}

	private function _getMatch ($str, $exp)
	{
		$match		= '';
		if (is_array($exp))
		{
			foreach ($exp as $key=>$value)
			{
				$preg = self::_filterExp($value);
				preg_match("/$preg/iU", $str, $preg_item);
				if ($preg_item[1])
				{
					$match = $preg_item[1];
					break;
				}
			}
		}
		else
		{
			$multiExp	= explode('|', $exp);
			$preg		= self::_filterExp($exp);
			preg_match("/$preg/iU", $str, $preg_item);
			if ( empty( $preg_item ) ) {
				preg_match("/$preg/i", $str, $preg_item);
			}
			for ($i = 1; $i <= count($multiExp) + 1; $i++)
			{
				if ($preg_item[$i])
				{
					$match = $preg_item[$i];
					break;
				}
			}
		}
		return $match;
	}

	private function _replace ($str, $exp)
	{
		foreach (explode("\n", $exp) as $key=>$value)
		{
			preg_match('/\[trim value=[\'"](.*)[\'"]\](.*)\[\/trim\]/iU', $value, $expArr);
			$str = preg_replace("/".self::_filterExp($expArr[2])."/iU", $expArr[1], $str);
		}
		return str_replace('\'', '\\\'', $str);
	}

	private function _getImg ($url, & $str)
	{
		preg_match_all("/<img\b[^<>]*?\bsrc[\s\t\r\n]*=[\s\t\r\n]*[\"']?[\s\t\r\n]*(?<imgUrl>[^\s\t\r\n\"'<>]*)[^<>]*?\/?[\s\t\r\n]*>/iu", $str, $array);
		foreach ($array['imgUrl'] as $key=>$value)
		{
			$list[] = str_replace('\'', '', str_replace('"', '', $value));
		}
		
		$listFullUrl	= array_distinct(getFullUrl($list, $url));
		$listNewUrl		= array();

		//分配新图片文件名，供之后下载图片
		foreach ($listFullUrl as $key=>$value)
		{
			$ext = end(explode('.', $value));
			if (!in_array($ext, array('jpg', 'gif', 'jpeg', 'png', 'bmp'))) $ext = 'jpg';
			$img = date('Y-m').'/'.date('Ymdhis').$GLOBALS['system']->createRnd(5).'.'.$ext;
			$listNewUrl[] = $img;
			//替换原内容中的图片路径
			$str = str_replace($list[$key], UPLOAD_PATH.$img, $str);
		}

		return array($listFullUrl, $listNewUrl, $list);
	}

	private function _getPageContent ($parentUrl, $pages, $pick, $replace = array())
	{
		$content	= array();
		$pages		= array_distinct($pages);
		$preg_a		= "<a.+?href=['\"\"]?([^'\"\">]+?)['\"\"]?(>| [^>]*?>)(.{1,}?)<\/a>";
		while (list($k, $v) = each($pages))
		{
			if ($v == '#')
			{
				continue;
			}
			$v				= getFullUrl($v, $parentUrl);
			if (in_array($v, $replace))
			{
				continue;
			}
			$http			= new httpService($v);
			$http->charset	= $pick['charset'];
			$resultContent	= $http->result();
			$resultContent	= self::_filterEnter($resultContent);
			$tmpContent		= trim(self::_getMatch($resultContent, $pick['item']['content']));
			if (!empty($tmpContent))
			{
				if ( $pick['item']['isJsContent'] && !empty( $pick['item']['jsContent'] ) ) {
					$jsContentUrl = self::_getMatch($tmpContent, $pick['item']['jsContent']);
					if ( ! empty( $jsContentUrl ) ) {
						unset($httpService);
						$httpService = new httpService($jsContentUrl);
						$httpService->charset = $pick['charset'];
						$tmpContent = self::_replace($httpService->result(), $pick['item']['content_replace']);
						//自动过滤document.write('');
						$tmpContent = preg_replace( "/document.write\(\\\'/iU", '', $tmpContent );
						$tmpContent = preg_replace( "/\\\'\);/iU", '', $tmpContent );
					}
				}
				$content[]	= $tmpContent;
			}
			$_pages			= null;
			if ($_pagestr = self::_getMatch($resultContent, $pick['item']['page']))
			{
				preg_match_all("/$preg_a/i", $_pagestr, $_pages);
				$_pages = $_pages[1];
			}
			if (is_array($_pages))
			{
				foreach ($_pages as $sk=>$sv)
				{
					if (!in_array($sv, $pages) && !in_array($sv, $replace))
					{
						$pages[] = $sv;
					}
				}
			}
		}
		return implode("<div style=\"page-break-after: always;\">
	<span style=\"display: none;\">&nbsp;</span></div>", $content);
	}

}

?>