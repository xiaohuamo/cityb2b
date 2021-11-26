<?php
	class ctl_voting extends cmsPage
	{
		
		function voting_manage_action(){
			$list=$this->loadModel('voting')->getVotingListByUser($this->loginUser['id']);
			$this->setData($list,'list');
			$this->setData( '投票管理 - '.$this->site['pageTitle'], 'pageTitle' );
			$this->setData('voting_manage', 'submenu');
			$this->setData('article_vote', 'menu');
			$this->display('voting/voting_manage');
		}

		function voting_create_action(){
			if(is_post()){
				$createUserId = $this->loginUser['id'];
				$title=post('title');
				$description=post('description');
				$id=$this->loadModel('voting')->createVoting($title,$description,$createUserId);

				$this->form_response(200,'创建成功', HTTP_ROOT_WWW.'voting/manage_item?id='.$id);
			}else{
				$this->setData( '投票创建 - '.$this->site['pageTitle'], 'pageTitle' );
				$this->display('voting/voting_create');
			}
		}


		function voting_update_action(){
			
			
			//增加权限管理 
			$id = get2('id');
			$mdl_voting=$this->loadModel('voting');

			if(is_post()){
				
				
				
				$title=post('title');
				$description=post('description');

				$data[mdl_voting::CLN_TITLE]=$title;
				$data[mdl_voting::CLN_DESCRIPTION]=$description;

				$id=$mdl_voting->updateVoting($data,$id);

				$this->form_response(200,'更新成功', HTTP_ROOT_WWW.'voting/manage_item?id='.$id);
			}
		}

		function manage_item_action(){
			
			//增加权限管理
			
			
			$id = get2('id');
			$this->setData($id,'voting_id');

			$data=$this->loadModel('voting')->getVoting($id);
			$this->setData($data[mdl_voting::CLN_TITLE],'voting_title');
			$this->setData($data[mdl_voting::CLN_DESCRIPTION],'voting_description');

			$item_list=$this->loadModel('voting_item')->getItemsByGroup($id);
			$this->setData($item_list,'item_list');

			$this->setData( '投票项管理 - '.$this->site['pageTitle'], 'pageTitle' );
			$this->display('voting/manage_item');
		}
		function item_delete_action(){
			
			// 如果不是拥有着无权限
			
			
			$id = get2('id');
			$item=$this->loadModel( 'voting_item' )->getItem($id);
			
			
			if( $this->loginUser['id'] ==$item['create_user_id']) {
			$this->loadModel( 'voting_item' )->itemDelete($id);
			
			$this->file->deletefile( UPDATE_DIR.$item['pic']);
			$this->sheader( HTTP_ROOT_WWW.'voting/manage_item?id='.$item['group_id'] );
			}else{
				
					$this->form_response(200,'no permission', HTTP_ROOT_WWW.'voting/manage_item?id='.$id);
			}
		}

		function item_create_action(){
			
			
			// 如果Id 不是所有者 或者不是佳丽本身 ,直接跳出无权限
			
			
			
			$id = get2('id');
			$this->setData($id,'voting_id');


			$item_id=get2('item_id');
			$this->setData($item_id,'item_id');

			$mdl_voting_item = $this->loadModel( 'voting_item' );
			
			//表明为miss
			if($this->loginUser['business_type_miss']) {
				$where=array(
				    'couponid'=>$this->loginUser['id']
				);
				
			
			$miss_item =$mdl_voting_item->getByWhere($where);
			if($miss_item) {
				
				$item_id=$miss_item['id'];
				$this->setData($item_id,'item_id');
				$id =9;
			    $this->setData($id,'voting_id');
				
			}
			}

			if(is_post()){
				
				
				 $images=post('images');
                    if($images){
                        foreach ($images as $key => $value) {
                            if($value=="default/image_upload.jpg")
                                unset($images[$key]);
                            else
                                $images[$key]=trim($value);
                        }
                        $data['pic']=reset($images);

                        $data['pics'] = serialize(array_slice($images, 1));
                    }

				//$data['pic'] = trim(reset(post('images')));

				$data[mdl_voting_item::CLN_GROUPID]=$id;
				$data[mdl_voting_item::CLN_SORTID]=post('sort_num');
				$data[mdl_voting_item::CLN_TITLE]=post('title');
				$data[mdl_voting_item::CLN_DESCRIPTION]=post('description');
				$data[mdl_voting_item::CLN_DETAIL]=post('detail');
				//$data[mdl_voting_item::CLN_COUPONID]=post('couponid');
				$data[mdl_voting_item::CLN_VIDEO]=post('video');
				
				if($item_id){
					$item =$mdl_voting_item->get($item_id);
					if ($item['create_user_id']==$this->loginUser['id'] || $item['couponid']==$this->loginUser['id'] ) {
						
					if ( $mdl_voting_item->update( $data, $item_id ) ) {
						if($miss_item){
							
							$this->form_response(200,'更新成功', HTTP_ROOT_WWW.'company/index');
						}else{
							
							$this->form_response(200,'更新成功', HTTP_ROOT_WWW.'voting/manage_item?id='.$id);
						}
						
					}
					else {
						$this->file->deletefile( UPDATE_DIR.$photo );
						$this->form_response_msg('保存失败');
					}
					}else{
						$this->form_response_msg('保存失败');
						
					}
					
				}else{
					if ( $mdl_voting_item->createItem( $data, $this->loginUser['id'] ) ) {
						$this->form_response(200,'创建成功', HTTP_ROOT_WWW.'voting/manage_item?id='.$id);
					}
					else {
						$this->file->deletefile( UPDATE_DIR.$photo );
						$this->form_response_msg('保存失败');
					}
				}
				


			}else{
				
				if($miss_item){
					
					$this->setData('修改佳丽信息', 'pagename');
					$this->setData('miss_page', 'menu');
					$this->setData('voting_manage', 'submenu');
					
				}
				
				$this->setData( $mdl_voting_item->get($item_id), 'voting_item' );
				$this->setData( '创建投票项 - '.$this->site['pageTitle'], 'pageTitle' );
				$this->display('voting/item_create');
			}
			
		}
		function show_action(){
			$id=get2('id');

			$voting=$this->loadModel('voting')->getVoting($id);
			$this->setData($voting,'voting');

			$item_list=$this->loadModel('voting_item')->getVotingItemList($id,$this->loginUser['id']);
			$this->setData($item_list,'list');


			$this->setData( $this->site['pageTitle'].'-投票-'.$voting['title'], 'pageTitle' );
			$this->setData( $this->site['pageTitle'].'-投票-'.$voting['description'], 'pageDescription' );

			$ui =$this->getUserDevice();
			if($ui=='mobile' || $ui=='wechat'){
				$this->display('voting/show_mobile');
			}else{
				$this->display('voting/show');
			}
		}
		function list_action(){
			$list = $this->loadModel('voting')->getVotingList();
			$this->setData($list,'list');
			$this->display('voting/list');
		}

		function item_detail_action(){
			$id=get2('id');
			$data=$this->loadModel('voting_item')->getItem($id);

			$mdl_voting_count = loadModel('voting_count');
			$w[mdl_voting_count::CLN_USERID]=$this->loginUser['id'];
			$w[mdl_voting_count::CLN_ITEMID]=$id;
			$w[mdl_voting_count::CLN_VOTED]=true;
			$data['voted']=$mdl_voting_count->entryExist($w);

			$this->setData($data,'item');
			$this->setData( '墨尔本第一届十大网红评选-'.$this->site['pageTitle'].'-'.$data['title'], 'pageTitle' );
			$this->setData( $this->site['pageTitle'].'-投票-'.$data['description'], 'pageDescription' );
			$this->display('voting/item_detail_show');
		}

		function vote_ajax_action(){
			$userId=$this->loginUser['id'];
			$itemId=get2('id');
			if($this->loadModel('voting_count')->vote($itemId,$userId)){
				$this->loadModel('voting_item')->updateCount($itemId,+1);
				echo 'success';
			}else{
				echo 'fail';
			}

		}
		function withdraw_vote_ajax_action(){
			$userId=$this->loginUser['id'];
			$itemId=get2('id');
			if($this->loadModel('voting_count')->withdrawVote($itemId,$userId)){
				$this->loadModel('voting_item')->updateCount($itemId,-1);
				echo 'success';
			}else{
				echo 'fail';
			}
		}

		function vote_open_action(){
			$this->sheader( HTTP_ROOT_WWW.'voting/voting_manage');
		}
		function vote_close_action(){
			$this->sheader( HTTP_ROOT_WWW.'voting/voting_manage');
		}
		function item_open_action(){

		}
		function item_close_action(){

		}

	    function test_action(){
	    	 $data = $this->loadModel('voting_item')->getVotingItemList(1,$this->loginUser['id']);
	    	
		}
		
	}
?>