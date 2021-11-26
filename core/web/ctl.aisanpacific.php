<?php

class ctl_aisanpacific extends cmsPage
{
    function ctl_aisanpacific()
    {

        parent::cmsPage();
		
       
    }
	
	
		
	
	
	
	
	function add_category($mdl_restaurant_category,$restaurant_id,$category_name,$category_Parent_id) {
		
		if($category_name!='分类') {
			$data=array(
			  'restaurant_id'=>$restaurant_id,
			   'category_id'=>54333,
			  'category_sort_id'=>100,
			  'category_cn_name'=>$category_name,
			  'createUserId'=>$restaurant_id,
			  'parent_category_id'=>$category_Parent_id
			);
			
			$newId = $mdl_restaurant_category->insert($data);
			$data_new_cate_id = array(
			  'category_id'=>$newId
			);
		
		
			
		
		 $mdl_restaurant_category->update($data_new_cate_id,$newId);
		}
		return $newId;
		
	}
	
	/*
	 分析分类信息，然后对分类信息进行更新
	 不存在的进行增加，存在的不做改变。
	
	*/
	function update_category_action(){
		
		header("Content-type: text/html;charset=utf8_general_ci");
		$array_menu_price =post('ProductList');
		
		$ignore_pic =post('ignore_pic');
		$array_menu_price=stripslashes($array_menu_price);

		$array_menu_price = json_decode($array_menu_price,true);

		if($array_menu_price) {
			$result['message']='数据取出成功';
		}else{
			$result['message']='数据未取出';
		}

	
    $restaurant_id=$this->loginUser['id'];
	$mdl_restaurant_category =$this->loadModel('restaurant_category');
	$mdl_restaurant_menu = $this->loadModel('restaurant_menu');
	$mdl_restaurant_menu_category = $this->loadModel('restaurant_menu_category');
	
	 // 系统首先要关掉所有产品的线上状态？
	 //这个过程就比较麻烦，因为要花几分钟，这个可以考虑怎么处理一下。
	$update_visiable =array(
	 'visible'=>0,
	 'onSpecial'=>0
	);
	
	$where =array(
	 'restaurant_id'=>$this->loginUser['id']
	);
	
	$mdl_restaurant_menu ->updateByWhere($update_visiable,$where);
	
	
	foreach ($array_menu_price as $key => $value) { 
		
       //首先处理分类的字符串，将字符串按照；和/进行拆分
	   $categoryItems =  explode(';',$value['categories']);
	   
	   
	   $onSpecial =0;
       if($categoryItems){ //如果为数组
	   
	   $main_cat_to_field='';
	   $whole_cat=array();
	   $menu_id=0;
	   
	   if(!$value['code']) {
		    $result['message'].= '/'.$value['title'].'无编号！未插入！';
			continue;
		   
	   }
	  
	  
	   
	   
	   
	   
		foreach ($categoryItems as $categoryItem) { 
		
			  if($categoryItem) {
				  //如果字符中包含/，则表示前面为父类，后面为子类进行相应检索。
				  
				if(strpos($categoryItem,'每周特价')  !== false ){ 
				 $onSpecial =1;
				}
				  
				$pos =strpos($categoryItem,'/');
				
				if($pos){ //表示含有子类
					
					$categoryItem_main_cate=substr($categoryItem,0,$pos);
					
					$categoryItem_sub_cate=substr($categoryItem,$pos+1);
				//	$result['message'].=' 检测到父子类'.$categoryItem_main_cate.'-'.$categoryItem_sub_cate;
					//插入主分类
					
					$where_cate =array(
					'restaurant_id'=>$this->loginUser['id'],
					'category_cn_name' =>$categoryItem_main_cate
					);
					
					
					$main_cate =$mdl_restaurant_category->getByWhere($where_cate);
					if($main_cate) {
						//如果发现主分类
						$main_cat_to_field = $main_cate['id'];
						$main_cate_id  =$main_cate['id'];
					//	$result['message'].=' 主分类'.$categoryItem_main_cate.'已存在,无需插入 '.$main_cate_id ;
					}else{
						
						$main_cate_id =$this-> add_category($mdl_restaurant_category,$restaurant_id,$categoryItem_main_cate,0);
						$main_cat_to_field = $main_cate_id;
					//	$result['message'].=' 主分类'.$categoryItem_main_cate.'不存在,插入，获得编号 '.$main_cate_id ;
					}
					
					$where_cate =array(
					'restaurant_id'=>$this->loginUser['id'],
					'category_cn_name' =>$categoryItem_sub_cate
					);
					
					$sub_cat_rec = $mdl_restaurant_category->getByWhere($where_cate);
					if($sub_cat_rec) {
						//如果发现子分类
						//$result['message'].=' 子分类'.$categoryItem_sub_cate.'已存在,无需插入 ' ;
						$whole_cat[]=$sub_cat_rec['id'];
					}else{
						$sub_cate_id=$this-> add_category($mdl_restaurant_category,$restaurant_id,$categoryItem_sub_cate,$main_cate_id);
						//$result['message'].=' 子分类'.$categoryItem_sub_cate.'不存在,插入，获得编号 '.$sub_cate_id ;
						$whole_cat[]=$sub_cate_id;
					}
					
					
					
					
				}else{
					
					$where_cate =array(
					'restaurant_id'=>$this->loginUser['id'],
					'category_cn_name' =>$categoryItem
					);
		
			        $curr_rec = $mdl_restaurant_category->getByWhere($where_cate);
					if($curr_rec) {
						$main_cat_to_field = $curr_rec['id'];
						$whole_cat[]=$curr_rec['id'];
						//$result['result'].=' 发现'.$categoryItem;
						//$result['message'].=' 发现正常类'.$categoryItem;
					}else{
					//	$result['result'].=' 未发现'.$categoryItem;
					//	$result['message'].=' 未发现'.$categoryItem;
						$main_cate_id=$this-> add_category($mdl_restaurant_category,$restaurant_id,$categoryItem,0);
						$main_cat_to_field = $main_cate_id;
						$whole_cat[]=$main_cate_id;
					}
					
				}
				

				
			  }

			}
	   }
		
		
	
			$where = array(
				'restaurant_id'=>$this->loginUser['id'],
				'menu_id'=>$value['code']

			);

			$data=array();
			if($value['stock']==0) {
				$data['visible']=0;
			}else{
				$data['visible']=1;
			}
			
			$data['qty']=$value['stock'];
			$data['original_price']=$value['original_price'];
			
			
			
			
			
			if($onSpecial) {
				$data['speical_price']=$value['miaosha_price'];
				$data['limit_buy_qty']=0;
				$data['onSpecial']=1;
			}
			
			$data['price']=$value['price'];
			$data['menu_en_name']='';
			
			$data['unit']='';
			$data['restaurant_category_id'] = $main_cat_to_field;
			
			//$data['menu_cn_name']=$value['CODE'].'-'.$value['ProductName1']."(".$value['ProductName2'].") ".$value['Units'];
			
			$data['menu_cn_name']=$value['title'];
			
			
			if(!$ignore_pic) {
				 $first_pic_link = substr($value['images'],0,strpos($value['images'],';'));
				$pic_path_filename='2020-12/'.substr($first_pic_link,strrpos($first_pic_link, '/', -1)+1);
				$data['menu_pic'] =$pic_path_filename;
				$this->save_pic($first_pic_link,$pic_path_filename);
				$this->cut_image($pic_path_filename,100,100,'cut');
				
			}
		   
			$data['menu_pics'] =$value['images'];
			
			$arry_no_find=array();
			$index =0;
			
			
			
			$menu_rec = $mdl_restaurant_menu->getByWhere($where);
			if($menu_rec){ // 如果找到这个产品编号
				
				$arr_no_find [index] =$value['code'].'/'.$value['title'];
				$index ++;
				$menu_id = $menu_rec['id'];
				if(!$mdl_restaurant_menu->updateByWhere($data,$where)){
					$result['result']='修改成功';
				}  else{
					$result['result']='修改失败';
					
				}
			
			}else{ //未找到则添加
				
				// 获得到分类编号
				
				   $data['restaurant_category_id'] = $main_cat_to_field;
					
				
				$data['restaurant_id'] =$this->loginUser['id'];
				$data['menu_id'] =$value['code'];
				$data['createUserId'] =$this->loginUser['id'];
				
				 $first_pic_link = substr($value['images'],0,strpos($value['images'],';'));
		    	$pic_path_filename='2020-12/'.substr($first_pic_link,strrpos($first_pic_link, '/', -1)+1);
				$data['menu_pic'] =$pic_path_filename;
				$this->save_pic($first_pic_link,$pic_path_filename);
			     $this->cut_image($pic_path_filename,100,100,'cut');
				 $data['menu_pics'] =$value['images'];
			  if($value['code']!='商家编码') {
				  
				 $newId = $mdl_restaurant_menu ->insert($data);
				if( $newId) {
					$result['result']='插入成功';
					$menu_id=$newId;
					
				}else{
					$result['result']='插入失败';
					
				}
				}
			}
			
			// 多个分类插入
			if($menu_id) {
				
				foreach ($whole_cat as $cat_idd) {  
				  $data_cate = array(
				   
				  'restaurant_menu_id'=>$menu_id,
				   'category_id'=>$cat_idd
				  
				  );
				
				 $mdl_restaurant_menu_category->insert($data_cate);
				
				}
				
			}
			

			if ( $arr_no_find) {
				$result['result']=json_encode($arr_no_find);
			}else{
				$result['result']='数据全部处理完成';
			}  
		} 
		
		
		echo json_encode($result);
		
		
		
		
	}
	
	
 public function add_cut_image_action() {
	
 $mdl_res = $this->loadModel('restaurant_menu');
 $sql ="select id,menu_pic from cc_restaurant_menu where restaurant_id =318951 and  length(menu_pic)>0 ";
 $list_data =$mdl_res->getListBySql($sql);
 
 foreach ($list_data as $key => $value) { 
  $this->cut_image($value['menu_pic'],100,100,'cut');
  
 }
	
	
}
		
 function cut_image( $string, $width, $height, $method = 'fill',$baseOnSkinPath=false) {
  //  $string ="2020-12/1609828130-d8c7131ef7db494f2ad9d19dac7c4208.jpg";
	$baseDir = $baseOnSkinPath?TPL_DIR:UPDATE_DIR;
	
	
	//$width =100;
//	$height=100;
	//var_dump($baseDir);exit;

	$noImage = false;
	if ( empty( $string ) ||  !file_exists( $baseDir.$string ) || ! in_array( $method, array( 'cut', 'fill' ) ) ) {
		$noImage = true;
		$string = 'no-image.gif';
		if ( ! file_exists( $baseDir.$string ) ) {
			return '';
		}
		$method = 'cut';
		//return $string;
	}

	$width = (int)$width;
	$height = (int)$height;

	if ( $width <= 0 || $height <= 0 ) {
		return $string;
	}

	$image_state = getimagesize( $baseDir.$string );
	
	switch ( $image_state[2] ) {
		case 1 : $im = imagecreatefromgif( $baseDir.$string ); break;
		case 2 : $im = imagecreatefromjpeg( $baseDir.$string ); break;
		case 3 : $im = imagecreatefrompng( $baseDir.$string ); break;
	}
	$old_width = $image_state[0];
	$old_height = $image_state[1];

	if ( $old_width == $width && $old_height == $height ) {
		return $string;
	}

	$file = new file;
	$newImageDir = $baseDir.'thumbnails/';
	$newImageUrl = $file->nameExtend( $string, "_{$width}x{$height}_{$method}" );
	//$newImageUrl = $file->nameExtend( $string, "" );

	if ( file_exists( $newImageDir.$newImageUrl ) ) {
		return 'thumbnails/'.$newImageUrl;
	}

	$newImagePath = $file->name( $newImageUrl );
	$newImagePath = str_replace( $newImagePath, '', $newImageUrl );
	$file->createdir( $newImageDir.$newImagePath, 0777 );
	if ( $method == 'fill' ) {
		$file->resize( $baseDir.$string, $newImageDir.$newImageUrl, $width, $height );
		$file->fillColor( $newImageDir.$newImageUrl, $newImageDir.$newImageUrl, $width, $height, array( 255, 255, 255 ) );
	}
	elseif ( $method == 'cut' ) {
		$file->resize( $baseDir.$string, $newImageDir.$newImageUrl, $width, $height, true, true );
		$file->cutByPos( $newImageDir.$newImageUrl, $newImageDir.$newImageUrl, $width, $height );
	}

	// watermarkImage($newImageDir.$newImageUrl); disable water mark of image

	return 'thumbnails/'.$newImageUrl;
}





function get_pic_name($url)
{
	$filename=substr($url,strrpos($url, '/', -1)+1);
	$path='data/upload/2020-12/';
	return $path.$filename;
}		


function save_pic($url,$filename){
	
	if (file_exists($filename)) {
   
		} else {
			$return_content = $this->http_get_data($url);  
			$fp= @fopen($this->get_pic_name($url),"a"); //将文件绑定到流
			fwrite($fp,$return_content); //写入文件  
		}
			
}
public function test_get_image_action() {
	
	
	
	
	$url = 'https://cdn.wechat.bebridge.cn/wp-content/uploads/sites/268/2020/09/1609827934-54f81a0db3639d5509bae24abb808634.png';
	$return_content = $this->http_get_data($url);  
	
	//var_dump($filename);exit; 
	$fp= @fopen($this->get_pic_name($url),"a"); //将文件绑定到流
	fwrite($fp,$return_content); //写入文件  
	
}				



function http_get_data($url) {  
      
    $ch = curl_init ();  
    curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );  
    curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );  
    curl_setopt ( $ch, CURLOPT_URL, $url );  
    ob_start ();  
    curl_exec ( $ch );  
    $return_content = ob_get_contents ();  
    ob_end_clean ();  
      
    $return_code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );  
    return $return_content;  
}  
 



				

    function index_action()
    {

       

        $this->setData('生鲜首页', 'pagename');
        $this->setData('index', 'menu');
        $this->setData('生鲜首页 - '.$this->site['pageTitle'], 'pageTitle');
        $this->display_pc_mobile('freshfood/index', 'freshfood/index');
    }

   function search_action()
    {

        $this->setData('生鲜首页', 'pagename');
        $this->setData('index', 'menu');
        $this->setData('生鲜首页 - '.$this->site['pageTitle'], 'pageTitle');
        $this->display_pc_mobile('freshfood/search', 'freshfood/search');
    }

}