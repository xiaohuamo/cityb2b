<?php

class ctl_query extends cmsPage
{

    function index_action()
    {

        $cmd = trim(get2('cmd'));
        if (empty($cmd)) $cmd = trim(post('cmd'));
        if (empty($cmd)) exit;
        switch ($cmd) {

            /**
             * 微信扫码登录检测
             */
            case 'check_weixin_login':
                $result='INVALID';
                $pc_id = (int)(post('id'));

                $userId = $this->loadModel('wj_pc_weixin_login_temp_info')->getUserId($pc_id);
                if ($userId) {
                    $mdl_user = $this->loadModel('user');
                    
                    $user = $mdl_user->get($userId);
                    if ($user) {

                        $data = array(
                            'lastLoginIP' => ip(),
                            'lastLoginDate' => time(),
                            'loginCount' => $user['loginCount'] + 1
                        );
                        $mdl_user->updateUserById($data, $user['id']);

                        $this->session('member_user_id', $user['id']);
                        $this->session('member_user_shell', $this->md5($user['id'] . $user['name'] . $user['password']));

                        $this->cookie->setCookie('remember', $remember, 60 * 60 * 24 * 365);
                        if ($remember) {
                            $this->cookie->setCookie('remember_user_id', $user['id'], 60 * 60 * 24 * 365);
                            $this->cookie->setCookie('remember_user_shell', $this->md5($user['id'] . $user['name'] . $user['password']), 60 * 60 * 24 * 365);
                        }

                        $result='VALID';
                    }
                } 

                echo $result;
                break;


            /**
             * 更新购物车产品数量
             */
            case 'update_carts':

                $data=trim(post('data'));

                $data=stripslashes($data);

                $list = json_decode($data,true);


                $mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');


                try {
                    
                    $mdl_wj_user_temp_carts->begin();

                    foreach ($list as $key => $value) {
                         $data = array(
                            'quantity' => $value
                        );

                         $mdl_wj_user_temp_carts->update($data, $key);
                    }

                    $mdl_wj_user_temp_carts->commit();

                    $this->form_response_msg('保存成功');

                } catch (Exception $e) {
                    
                    $mdl_wj_user_temp_carts->rollback();

                    $this->form_response_msg('保存失败');

                }
                
                break;

            case "update_carts_with_result_back":

                $data=trim(post('data'));

                $businessUserId = post('businessUserId');

                $data=stripslashes($data);

                $list = json_decode($data,true);


                $mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');


                try {
                    
                    $mdl_wj_user_temp_carts->begin();

                    foreach ($list as $key => $value) {
                         $data = array(
                            'quantity' => $value
                        );

                         $mdl_wj_user_temp_carts->update($data, $key);
                    }

                    $mdl_wj_user_temp_carts->commit();


                } catch (Exception $e) {
                    
                    $mdl_wj_user_temp_carts->rollback();

                }

                $cartItems=$mdl_wj_user_temp_carts->getDetailedItem($this->loginUser['id'], $businessUserId);
                 $totalQuantity=0;
                $totalMenuPrice=0;
                $totalVoucherPrice=0;

                foreach ($cartItems[0]['items'] as $item) {
                    $totalQuantity+=$item['quantity'];

                    if($item['EvoucherOrrealproduct']=='restaurant_menu'){
                        $totalMenuPrice+=$item['quantity']*$item['single_amount'];
                    }else{
                        $totalVoucherPrice+=$item['quantity']*$item['single_amount'];
                    }
                    
                }

                $result['totalQuantity']=$totalQuantity;
                $result['totalMenuPrice']=$totalMenuPrice;
                $result['totalVoucherPrice']=$totalVoucherPrice;
                echo json_encode($result);

                break;

            case "empty_cart":
                if ($this->loginUser['id']) {
                    $this->loadModel('wj_user_temp_carts')->clearTempCart($this->loginUser['id']);
                    echo 'success';
                }
                break;
			//这里有漏洞 ,没有判断当前用户是否为产品owner
            case "remove_cart_item";
			    $id = get2('id');
				$temp = $this->loadModel('wj_user_temp_carts');
					$record =$temp->get($id);
					 
					 if($record) {
						  if ($record['userId'] !=$this->loginUser['id']) {
								echo ('非法更改!');
						 }else{
							   
							$temp->delete($id);
							 
						 }
						 
					 }
		        
				break;
				
			case "min_cart_item";
                $id = get2('id');
				$temp = $this->loadModel('wj_user_temp_carts');
				$record =$temp->get($id);
				 
				 if($record) {
					  if ($record['userId'] !=$this->loginUser['id']) {
							echo ('非法更改!');
					 }
					  $quantity = $record['quantity']-1;
					  if ($quantity==0) $quantity=1;
					 $data =array(
						'quantity' =>$quantity
						);
					 if($temp->update($data,$id)){
						// echo ('修改成功');
						 
					 }else{
						 
						 echo ('修改不成功');
					 }
					 
				 }else{
					 
					 echo ('未找到记录!');
				 }
			
				break;
				
			case "add_cart_item";
					$id = get2('id');
					$temp = $this->loadModel('wj_user_temp_carts');
					$record =$temp->get($id);
					 
					 if($record) {
						  if ($record['userId'] !=$this->loginUser['id']) {
								echo ('非法更改!');
						 }
						  $quantity = $record['quantity']+1;
						 // 获得当前库存是否大于产品中的库存，如果大于则显示，库存以达最大值
						 $menu_rec =$this->loadModel('restaurant_menu')->get($record['menu_id']);
						 if($menu_rec['qty']<$quantity) {
							 $result =1;
							  echo $result;
						 }else if ($quantity >$menu_rec['limit_buy_qty'] && $menu_rec['limit_buy_qty']>0){
							 if($record['onSpecial']) {
								   $result =2;
									echo $result;
								 
							 }else{
								 
								 $data =array(
									'quantity' =>$quantity
									);
								 if($temp->update($data,$id)){
									// echo ('修改成功');
									 
								 }else{
									 
									 echo ('修改不成功');
								 }
										 
										 
									 }
							
							 
							 
						 }else{
							 
							 $data =array(
							'quantity' =>$quantity
							);
						 if($temp->update($data,$id)){
							// echo ('修改成功');
							 
						 }else{
							 
							 echo ('修改不成功');
						 }
						 }
						 
						 
						 
						 
						 
					 }else{
						 
						 echo ('未找到记录!');
					 }
				
					break;
            /**
             * 票务加入购物车
             */
            case 'add_carts_ticket':

                $userId = trim(post('userId'));
                $businessUserId = trim(post('businessUserId'));

                $main_coupon_id = trim(post('main_coupon_id'));
                $sub_coupon_id = trim(post('sub_coupon_id'));
                
                $coupon_name = trim(post('coupon_name'));
                $sub_or_main = trim(post('sub_or_main'));
                
                $seat_des = trim(post('seat_des'));

                $mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');

                $mdl_wj_user_temp_carts->removeAllItemOfOtherBusiness($userId,$businessUserId);

                $data = array(
                    'userId' => $userId,
                    'createTime' => time(),
                    'main_coupon_id' => $main_coupon_id,
                    'sub_coupon_id' => $sub_coupon_id,
                    'coupon_name' => $coupon_name,
                    'businessUserId' => $businessUserId,
                   
                    'sub_or_main' => $sub_or_main,
                );


                $ssinfo = $this->loadModel('wj_show')->getShowAndStadium($main_coupon_id);

                $mdl_seats = $this->loadModel('wj_show_seats');

                $mdl_seats->begin();
                //门票
                try {
                    $show = $ssinfo['show_id'];
                    $stadium = $ssinfo['stadium_id'];
                    $seats = explode('#', $seat_des);
                    $seats = array_slice($seats, 0, -1);

                    $c_name = $data['coupon_name'];

                    $successList = [];
                    $failList = [];
                    foreach ($seats as $s) {
                        $parts = explode('_', $s);
                        $area = $parts[0];
                        $row = $parts[1];
                        $col = $parts[2];
                        $desc = 'Sec:' . $area . ' Row:' . $row . ' Seat:' . $col;

                        $id = $mdl_seats->updateSeatToLock($show, $stadium, $area, $row, $col);// while in shoping cart. lock it for a customer
                        if ($id == null) {
                            array_push($failList, $desc);
                            continue;
                        };//if  id is null means no that seats is not available;

                        $d = $mdl_seats->getSeatsDataSeatsNumber($show, $stadium, $area, $row, $col);
                        $data['seat_id'] = $d[0]['id'];
                        $data['seat_des'] = $desc;
                        $data['coupon_name'] = $c_name . $data['seat_des'];
                        $data['quantity'] = 1;
                        $data['single_amount'] = $d[0]['price'];
                        if ($mdl_wj_user_temp_carts->insert($data)) {
                            array_push($successList, $desc);
                        } else {
                            echo '无法选票' . $s;
                            throw new Exception();
                        }
                    }
                    $mdl_seats->commit();
                    $msg = "";
                    if ($successList) $msg .= "成功选票 " . join(" ", $successList) . "\n";
                    if ($failList) $msg .= "下面这些票刚刚被其他用户锁定,请稍后再试:" . join(" ", $failList);

                    $result['status'] = ($successList) ? 500 : 501;//500 move to shopping cart. 501 stay in this page.
                    $result['msg'] = $msg;
                    echo json_encode($result);

                } catch (Exception $e) {
                    $mdl_seats->rollback();
                }

                break;

            /**
             * 正常加入购物车
             */
            case 'add_carts':
                $main_coupon_id = trim(post('main_coupon_id'));
                $quantity = trim(post('quantity'));
                $sub_coupon_id = trim(post('sub_coupon_id'));
                $guige_ids = trim(post('guige_ids'));

                $userId=$this->loginUser['id'];

              //  var_dump($main_coupon_id .'  子券' .$sub_coupon_id);
                if(!$userId)$this->form_response_msg((string)$this->lang->log_in_please);

                if(!$main_coupon_id)$this->form_response_msg('coupon id lost!');

                if($quantity<=0)$this->form_response_msg((string)$this->lang->alert_min_stocks);


               
				
				$mdl_coupons=$this->loadModel('coupons');
                $coupon =$mdl_coupons->get($main_coupon_id);
				

              
                if($coupon['isInManagement']==1&&$userId!=UBONUSSHOPID){
                    $this->specialEventTimeCheck();
                    $this->specialEventTotalQtyLimitCheck($userId);
                }

                
                $this->freeProductPurcheseLimitCheck($userId,$main_coupon_id);
                

                $mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');

				if($coupon['perCustomerLimitQuantity']==0){
					
					$coupon['perCustomerLimitQuantity']=99999;
				}
				
				//如果加入的产品为优惠券 bonustype=4 or bonustype =18 代金券, 则清除购物车里所有该用户的购买清单.不予提示
				if($coupon['bonusType']==4 || $coupon['bonusType']==18) {
					$where1 = array (
					   'userId'=>$this->loginUser['id']
					);
					$mdl_wj_user_temp_carts->deleteByWhere($where1);
					
				}
				
					
				 // 这里需要做两个判断， 1）如果当前购物车种有多次使用产品，那么删除多次产品后，才插入当前产品。 2） 如果插入的产品是多次使用的产品，那么需要删除购物车该商家的其它产品。
				//$this->form_response(200,$coupon['multi_use']);
				if($coupon['multi_use']>1) {
					//表示当前产品为多次使用产品，需要删除购物车中该商家的其它产品
					$delete_where_01=array(
					  'userId'=>$this->loginUser['id'],
					  'businessUserId'=> $coupon['createUserId']
					);
					$mdl_wj_user_temp_carts->deleteByWhere($delete_where_01);
					$is_multi_time_usd=1;
				}
				
				if($guige_ids){
					
					
				}else{
				
				// 检查当前购物车中该用户 指定商家的临时购物车中是否有多次使用产品。如果有则清除
				// 获取当前用户在当前临时购物车中，该商家的其它商品，如果其它商品中存在多次使用的商品，则需要删除。
				$delete_where_01=array(
					  'userId'=>$this->loginUser['id'],
					  'businessUserId'=> $coupon['createUserId']
					);
					
					
				$user_temp_list=$mdl_wj_user_temp_carts->getList(null,$delete_where_01);

				
				if($user_temp_list) {
					foreach ($user_temp_list as $key => $val) {
                          $coupon_id = $val['main_coupon_id'];
							
						  $curr_coupon = $mdl_coupons->get($coupon_id);  
						 
							
						  if ($curr_coupon['multi_use']>1) {
							  $mdl_wj_user_temp_carts->delete($val['id']);
							  continue;
						  }
						  
						  //如果当前产品为子卡，则跳过检查。
						//  $this->form_response(200,'mainid:'.$main_coupon_id.' and subid'.$sub_coupon_id);
						//  break;
						
						  if($main_coupon_id <> $sub_coupon_id && $sub_coupon_id>0) {
							  //如果是子卡，那么不做限制处理。
							  
							  
						  }else{
						  
						  if($coupon_id ==$coupon['id']) {
							  //如果发现临时购物车中有该产品
							  // 判断当前的购物车中的数量 加上新增的数量 是否大于限制数量，如果大于限制数量，则新增数量进行重新计算。
							  if(($val['quantity'] +$quantity)>$coupon['perCustomerLimitQuantity']) {
								  
								  $quantity =$coupon['perCustomerLimitQuantity']-$val['quantity'];
								  $message1='数量调整至本产品购买上限-'.$coupon['perCustomerLimitQuantity'];
								  if($quantity<=0) {

									    $this->form_response(200,(string)$this->lang->max_quantity.$coupon['perCustomerLimitQuantity'].','.(string)$this->lang->can_not_add_more);
										break;
								  }
							  }
							  
						  }
						  }
						
					}
					
				}else{
					// 如果购物车中没有数据也需要检查当前用户选择的数量是否大于最大限制数量。
					
					  if($quantity>$coupon['perCustomerLimitQuantity']) {
								  
								  $quantity =$coupon['perCustomerLimitQuantity'];
								  $message1='数量调整至本产品购买上限-'.$coupon['perCustomerLimitQuantity'];
								 
							  }
					
					
					
				}
				}
				
                $process = new AddCartProcess();

                $process->owner($this->loginUser['id']); 
                 
				 
									
				if($is_multi_time_usd) {
						$quantity  =1; //如果当前订单为多次使用，数量只能为 1         
					}

                $process->qty($quantity)->add($main_coupon_id,$sub_coupon_id,$guige_ids,$this->getLangStr());
				
				if($message1) {
					
					$this->form_response(200,$message1);
				}else{
					
					$this->form_response(200,$this->lang->add_successfully.$quantity.$this->lang->item);
				}
               


                break;

             /**
             * 正常加入购物车
             * 使用字符串作为变量
             * 3452,4352,5#2342,3234,5  (主卡id,子卡ID,数量)
             */
            case 'add_carts_batch':
			
			

               $userId=$this->loginUser['id'];

                if(!$userId)$this->form_response_msg('0'); 

                $code_str = trim(post('code_str'));

                if(!$code_str) $this->form_response_msg('请选择产品');

                $list = explode('#', $code_str);

                foreach ($list as $l) {
                    $data = explode(',', $l);

                    $main_coupon_id     = (isset($data[0]))?$data[0]:null;
                    $quantity           = (isset($data[1]))?$data[1]:null;
                    $sub_coupon_id      = (isset($data[2]))?$data[2]:null;
                    $guige_ids          = (isset($data[4]))?$data[4]:null;

                }
               

				$mdl_coupons=$this->loadModel('coupons');
                $coupon =$mdl_coupons->get($main_coupon_id);
              
                if($coupon['isInManagement']==1&&$userId!=UBONUSSHOPID){
                    $this->specialEventTimeCheck();
                    $this->specialEventTotalQtyLimitCheck($userId);
                }

                
                $this->freeProductPurcheseLimitCheck($userId,$main_coupon_id);
                

                $mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');


               
				
				
			


			
				// 如果是多次使用产品， 下面的代码已经不再执行
				
				//  找到当前用户，对于当前选择产品对应商家的所有临时购物车产品。

				
				
				$delete_where_01=array(
					  'userId'=>$this->loginUser['id'],
					  'businessUserId'=> $coupon['createUserId']
					);
					
					
				$user_temp_list=$mdl_wj_user_temp_carts->getList(null,$delete_where_01);

				
				// 不管怎样，如果新插入的为多次coupon ,那么也要清空购物车中的所有多次coupon .
				
				if($user_temp_list) {
					foreach ($user_temp_list as $key => $val) {
                          $coupon_id = $val['main_coupon_id'];
							
						  $curr_coupon = $mdl_coupons->get($coupon_id);  
						 
							
						  if ($curr_coupon['multi_use']>1) {
							  $mdl_wj_user_temp_carts->delete($val['id']);
							  continue;
						  }
					}
				}
				
				
			
				

                // 对于手机接收的所有产品，分别进行处理，添加到临时购物车中。
				
				
                foreach ($list as $l) {
                    $data = explode(',', $l);

                    $main_coupon_id     = (isset($data[0]))?$data[0]:null;
                    $sub_coupon_id      = (isset($data[1]))?$data[1]:null;
                    $quantity           = (isset($data[2]))?$data[2]:null;
                    $guige_ids          = (isset($data[4]))?$data[4]:null;


                    $process = new AddCartProcess();

                    $process->owner($userId); 
					
					
					// 如果为子卡则不处理数量限制
					if($main_coupon_id<>$sub_coupon_id && $sub_coupon_id>0) {
						
						$process->qty($quantity)->add($main_coupon_id,$sub_coupon_id,$guige_ids);
					
						
					// 主卡检查数量限制
					}else{
						
							
						//检查当前主卡产品是否有数量限制
						 $curr_coupon = $mdl_coupons->get($main_coupon_id);  
						 
						 
						 
						 
						if($curr_coupon['multi_use']>1) {
							//表示当前产品为多次使用产品，需要删除购物车中所有产品，然后添加该产品且数量为 1 
							$delete_where_01=array(
							  'userId'=>$this->loginUser['id'],
							  'businessUserId'=> $curr_coupon['createUserId']
							);
							$mdl_wj_user_temp_carts->deleteByWhere($delete_where_01);
							
							
							$quantity=1; //多次购买的产品数量只能唯一。
							
							
							$process->qty($quantity)->add($main_coupon_id,$sub_coupon_id,$guige_ids);
							$message1='多次使用产品，数量最大为1';
							continue;
						}
	
						 if ($curr_coupon['perCustomerLimitQuantity']>0) {
							 				 
							 $is_find=0;
							 //有数量限制
							if($user_temp_list) {
							
									// 如果购物车中有数据，检查是否有当前主产品存在，如果有则计算 要追加的数量。
									
								
									
									foreach ($user_temp_list as $key => $val) {
										
										  $coupon_id = $val['main_coupon_id'];
										  $sub_id = $val['sub_coupon_id'];
										  
										  if($coupon_id==$sub_id) { //表示当前行为主卡
											  
											   $is_find=1;  //表示发现了主产品 
											 
											 // 获取要新增的数量
											   
											   if(($val['quantity'] +$quantity)>$curr_coupon['perCustomerLimitQuantity']) {
												   //如果当前购物车中数量加新增数量大于最大数字，则进行数量调整。
											  
												  $quantity =$curr_coupon['perCustomerLimitQuantity']-$val['quantity'];
												  $message1='数量调整至本产品购买上限-'.$curr_coupon['perCustomerLimitQuantity'];
												  if($quantity<=0) { 
												    $quantity=0;
												  }
												}
											  
											  
										  }else{
											  //非主卡，不做任何处理
											  
										  }
											
											
									}
									
									if($is_find==0) {
										//临时购物车里没有 找到对应的数据
										if($quantity > $curr_coupon['perCustomerLimitQuantity']) {
									
										$quantity=$curr_coupon['perCustomerLimitQuantity'];
										$message1='最大购买数为'.$coupon['perCustomerLimitQuantity'];
									
								}
										
									}
							
							}else{
								
								//临时购物车中没有 任何数据
								if($quantity > $curr_coupon['perCustomerLimitQuantity']) {
									
									$quantity=$curr_coupon['perCustomerLimitQuantity'];
									$message1='最大购买数为'.$coupon['perCustomerLimitQuantity'];
									
								}
							}				 
							 
							 if($quantity>0) {
								 $process->qty($quantity)->add($main_coupon_id,$sub_coupon_id,$guige_ids);
								 
							 }
							 
							 
							 
						 }else{
							 //无数量限制
						 	$process->qty($quantity)->add($main_coupon_id,$sub_coupon_id,$guige_ids);
							 
						 }
							
				
					 }
                   

                }
             

			   if($message1) {
				    $this->form_response(200,$message1);
			   }else{
				    $this->form_response(200,(String)$this->lang->add_successfully);
			   }
               

                break;
             case 'restaurant_add_menu_with_option':

                $userId=$this->loginUser['id'];
  
                if(!$userId)$this->form_response_msg('0');

                $mdl_coupons = $this->loadModel('coupons');
                $mdl_restaurant_menu = $this->loadModel('restaurant_menu');
                $restaurant_sidedish_menu = $this->loadModel('restaurant_sidedish_menu');
                $restaurant_menu_option = $this->loadModel('restaurant_menu_option');
                $mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');

                $data=post('data');
                if($data){
                    $d=reset($data);
                    $main_coupon_id=$d['main_coupon_id'];
                    $menu_id=$d['menu_id'];
                }

                $coupon = $mdl_coupons->get($main_coupon_id);
                $menu = $mdl_restaurant_menu->get($menu_id);


                //menu has optipn
                $menuHasOption=false;

                $name = $menu['menu_cn_name'];
                $option='';
                $sidedish='';
                $totalPrice=0;

                foreach ($data as $d) {
                    $type=$d['type'];
                    $id=$d['id'];
                    $quantity=$d['quantity'];
                    $price=$d['price'];

                    if($type=='menu_option'){
                        $item = $restaurant_menu_option->get($id);
                        $option.="规格：".$item['menu_cn_name'];

                        $menuHasOption=true;
                    }elseif($type=='sidedish_menu'){
                        $item = $restaurant_sidedish_menu->get($id);
                        $sidedish.=$item['menu_cn_name'] . "x" . $quantity . " ";
                    }

                    $totalPrice+=$price*$quantity;
                }  

                $restaurant_promotion_manjian_rates=loadModel("restaurant_promotion_manjian")->getRestaurantPromotionManjian($coupon['createUserId']);

                //如果menu没有规格，价格中计入餐品原价。
                if(!$menuHasOption)$totalPrice+=$menu['price']*(1-$restaurant_promotion_manjian_rates);

               

                //add data
                $data=array();
                $data['userId']         =$userId;
        
                $data['main_coupon_id'] =$main_coupon_id;
                $data['sub_coupon_id']  =$main_coupon_id;
                $data['sub_or_main']    ='m';
                $data['coupon_name']    =$name;
                
                
                
                
                $data['guige_ids']      =null;
                $data['guige_des']      =$option . " 配菜：" . $sidedish;
                
                $data['businessUserId'] =$menu['restaurant_id'];
                
                $data['menu_id'] =$menu_id;

                $exist=$mdl_wj_user_temp_carts->getByWhere($data);
                if($exist){
                    $update['quantity']=$exist['quantity']+1;

                    $mdl_wj_user_temp_carts->updateByWhere($update,$data);

                }else{
                    $data['quantity']       =1;

                    $data['single_amount']  =$totalPrice;

                    $data['createTime']     =time();

                    $mdl_wj_user_temp_carts->insert($data);
                }


				
				
				
				
				$userId=$this->loginUser['id'];
               
			   $sql ="SELECT category_sort_id,category_cn_name,a.*,b.menu_pic as pic ,d.pic as coupon_pic FROM `cc_wj_user_temp_carts` a left join `cc_restaurant_menu` b on b.id=a.`menu_id` left join `cc_restaurant_category` c on c.id=b.restaurant_category_id left join cc_coupons d on d.id=a.main_coupon_id  where a.userId=" .$userId."   and businessUserId =".$menu['restaurant_id'] . " order by category_sort_id,b.menu_id";
           
				
                $cartItems=$mdl_wj_user_temp_carts->getListBySql($sql);

                $totalQuantity=0;
                $totalMenuPrice=0;
                $totalVoucherPrice=0;

	       
                $cartTotalPrice = 0;
				
				
				$old_category='0';
				foreach ($cartItems as $key => $val) {
					 $totalQuantity+=$val['quantity'];
					 
					 if($val['EvoucherOrrealproduct']=='restaurant_menu'){
                        $totalMenuPrice+=$val['quantity']*$val['single_amount'];
                    }else{
                        $totalVoucherPrice+=$val['quantity']*$val['single_amount'];
                    }
					
					if ($val['category_sort_id'] !== $old_category) {
						if(!$val['category_sort_id']) { // 没有分类 就是团购套餐
							
							$cartItems[$key]['category_cn_name']='团购';
						}
						$cartItems[$key]['new_cat']=1;
					}else{
						$cartItems[$key]['new_cat']=0;
						
					}
					$old_category=$val['category_sort_id'];
					if(!$val['category_sort_id']) {
						$cartItems[$key]['pic']=$val['coupon_pic'];
					}
					
					
					
		            $cartTotalPrice+=$val['single_amount']*$val['quantity'];
				}
				
                $this->setData($cartTotalPrice,'totalPrice');

                $this->setData($cartItems,'items');
                $this->setData($businessUserId,'businessUserId');
                
				
				$us=$this->getUserDevice();
					if($us=='desktop'){
					 $html = $this->fetch('/restaurant/ajax_cart');
					}else{
					 $html = $this->fetch('ajax_cart');
					}
				
			   
            


                $data['totalQuantity']=$totalQuantity;
                $data['totalMenuPrice']=$totalMenuPrice;
                $data['totalVoucherPrice']=$totalVoucherPrice;
				 $data['html']=$html;
                echo json_encode($data);
				
				
					

             break;
			 
			  case 'factorypage_add_menu_with_option': //factory b2b


                $userId =post('user_id');
                
				if(!userId) 
				$userId=$this->loginUser['id'];
  
                if(!$userId)$this->form_response_msg('0');

                $mdl_coupons = $this->loadModel('coupons');
                $mdl_restaurant_menu = $this->loadModel('restaurant_menu');
                $restaurant_sidedish_menu = $this->loadModel('restaurant_sidedish_menu');
                $restaurant_menu_option = $this->loadModel('restaurant_menu_option');
                $mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');

                $data=post('data');
                if($data){
                    $d=reset($data);
                    $main_coupon_id=$d['main_coupon_id'];
                    $menu_id=$d['menu_id'];
                }

                $coupon = $mdl_coupons->get($main_coupon_id);
                $menu = $mdl_restaurant_menu->get($menu_id);


                //menu has optipn
                $menuHasOption=false;

                $name = $menu['menu_cn_name'];
                $option='';
                $sidedish='';
                $totalPrice=0;

                foreach ($data as $d) {
                    $type=$d['type'];
                    $id=$d['id'];
					$quantity=$d['quantity'];
                    $price=$d['price'];
					
					
                    if($type=='menu_option'){
						$guige_ids =$id;
                        $item = $restaurant_menu_option->get($id);
                        $option.="SPEC：".$item['menu_cn_name'];

                        $menuHasOption=true;
                    }elseif($type=='sidedish_menu'){
                        $item = $restaurant_sidedish_menu->get($id);
                        $sidedish.=$item['menu_cn_name'] . "x" . $quantity . " ";
						$sidedish_menu_id =$id;
                    }

                    $totalPrice+=$price*$quantity;
                }  

                $restaurant_promotion_manjian_rates=loadModel("restaurant_promotion_manjian")->getRestaurantPromotionManjian($coupon['createUserId']);

                //如果menu没有规格，价格中计入餐品原价。
                if(!$menuHasOption) {
                    if ($item['price']) {
                        $totalPrice+=$item['price']*(1-$restaurant_promotion_manjian_rates);
                    }else{
                        $totalPrice+=$menu['price']*(1-$restaurant_promotion_manjian_rates);
                    }

                }




                //add data
                $data=array();
                $data['userId']         =$userId;

                $data['main_coupon_id'] =$main_coupon_id;
                $data['sub_coupon_id']  =$main_coupon_id;
                $data['sub_or_main']    ='m';
                $data['coupon_name']    =$name;



                if($guige_ids) {
					 $data['guige_ids']      =$guige_ids;
				}

				if($sidedish){
					 $data['sidedish_menu_id']      =$sidedish_menu_id;
					$data['guige_des']      =$option . " SUBSPEC：" . $sidedish;
				}else{
					$data['guige_des']      =$option ;
					
				}
                
                
                $data['businessUserId'] =$menu['restaurant_id'];
                
                $data['menu_id'] =$menu_id;

                $exist=$mdl_wj_user_temp_carts->getByWhere($data);
                if($exist){
                    $update['quantity']=$exist['quantity']+$quantity;

                    $mdl_wj_user_temp_carts->updateByWhere($update,$data);

                }else{
                    $data['quantity']       =$quantity;

                    $data['single_amount']  =$totalPrice;

                    $data['createTime']     =time();

                    $newid = $mdl_wj_user_temp_carts->insert($data);


                }


				
				
				
				
				$userId=$this->loginUser['id'];
               
			   $sql ="SELECT category_sort_id,category_cn_name,a.*,b.menu_pic as pic ,d.pic as coupon_pic FROM `cc_wj_user_temp_carts` a left join `cc_restaurant_menu` b on b.id=a.`menu_id` left join `cc_restaurant_category` c on c.id=b.restaurant_category_id left join cc_coupons d on d.id=a.main_coupon_id  where a.userId=" .$userId."   and businessUserId =".$menu['restaurant_id'] . " order by category_sort_id,b.menu_id";
           
				
                $cartItems=$mdl_wj_user_temp_carts->getListBySql($sql);

                $totalQuantity=0;
                $totalMenuPrice=0;
                $totalVoucherPrice=0;

	       
                $cartTotalPrice = 0;
				
				
				$old_category='0';
				foreach ($cartItems as $key => $val) {
					 $totalQuantity+=$val['quantity'];
					 
					 if($val['EvoucherOrrealproduct']=='restaurant_menu'){
                        $totalMenuPrice+=$val['quantity']*$val['single_amount'];
                    }else{
                        $totalVoucherPrice+=$val['quantity']*$val['single_amount'];
                    }
					
					if ($val['category_sort_id'] !== $old_category) {
						if(!$val['category_sort_id']) { // 没有分类 就是团购套餐
							
							$cartItems[$key]['category_cn_name']='团购';
						}
						$cartItems[$key]['new_cat']=1;
					}else{
						$cartItems[$key]['new_cat']=0;
						
					}
					$old_category=$val['category_sort_id'];
					if(!$val['category_sort_id']) {
						$cartItems[$key]['pic']=$val['coupon_pic'];
					}
					
					
					
		            $cartTotalPrice+=$val['single_amount']*$val['quantity'];
				}
				
                $this->setData($cartTotalPrice,'totalPrice');

                $this->setData($cartItems,'items');
                $this->setData($businessUserId,'businessUserId');
                
				
				$us=$this->getUserDevice();
					if($us=='desktop'){
					 $html = $this->fetch('/factorypage/ajax_cart');
					}else{
					 $html = $this->fetch('/factorypage/ajax_cart');
					}
				
			   
            


                $data['totalQuantity']=$totalQuantity;
                $data['totalMenuPrice']=$totalMenuPrice;
                $data['totalVoucherPrice']=$totalVoucherPrice;
				 $data['html']=$html;
                echo json_encode($data);
				
				
					

             break;
			 

             case "get_cart_item_ajax":
			 
			   $businessUserId = get2('businessUserId');

               $html =$this->get_cart_item_ajax($businessUserId);
				
               
                echo $html;
             break;

			 
			 case "load_menu":
			 
			 
			   $businessUserId = get2('businessUserId');

               $menu =$this->load_menu($businessUserId);
			   
		         
                echo json_encode($menu);
             break;
			 
			 
             case 'restaurant_menu_add_carts':

                $userId=$this->loginUser['id'];
				
				  
  
                if(!$userId)
				    $this->form_response_msg(['code' => 0, 'error' => 'Need Login']);
				$mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');
				$mdl_restaurant_menu = $this->loadModel('restaurant_menu');
                $restaurant_sidedish_menu = $this->loadModel('restaurant_sidedish_menu');
                $restaurant_menu_option = $this->loadModel('restaurant_menu_option');
				$mdl_coupons = $this->loadModel('coupons');
               

                $type=post('type');
                $quantity=post('quantity');
                $main_coupon_id=post('main_coupon_id');
                $sub_coupon_id=post('sub_coupon_id');
                $menu_id=post('menu_id');
                $sidedish_menu_id=post('sidedish_menu_id');
                $menu_option_id=post('menu_option_id');
                $onSpecial= post('onspecial');
                $factory_id = post('factory_id');
			    $coupon =$mdl_coupons->get($main_coupon_id);
				
				
                switch ($type) {
                    case 'voucher':
                        $main_coupon_id;
                        $sub_coupon_id;
                        $quantity;

                        $process = new AddCartProcess();

                        $process->owner($userId); 

                        $process->qty($quantity,'update')->add($main_coupon_id,$sub_coupon_id,$guige_ids);


                    case 'menu':
                        $main_coupon_id;
                        $menu_id;
                        $quantity;
						$onSpecial;
						
						// 检查当前产品是否已经下架，检查当前产品库存是否充足
						
						$current_menu_rec = $mdl_restaurant_menu->get($menu_id);
						if (!$current_menu_rec['visible'] )  {
							$this->form_response_msg(['code' => 1, 'error' =>  (string)$this->lang->unable_to_buy]);
                        }
						//检查当前产品产品库存是否不足
						
						$where_temp_menu_id=array(
						'businessUserId'=>$coupon['createUserId'],
						'menu_id'=>$menu_id
						
						);
						
					/*	$menu_item_in_temp_cart = $mdl_wj_user_temp_carts->getByWhere($where_temp_menu_id);
						if($menu_item_in_temp_cart) {
							$stock_qty_in_temp  =$menu_item_in_temp_cart['quantity'];
						}else{
							$stock_qty_in_temp  =0;
						}
					*/	
					
					   //以下代码判断，当前商家是否为销售渠道（厂商下面的客户）
					   
					   $mdl = $this->loadModel('factory2c_list');
					   if(Factory2c_centre::getIfCurrentUserIsSalesChannal($current_menu_rec['restaurant_id'])) {
						   
						   
						   /* 暂时关掉，然后调整渠道的时候打开 
						   
						   $factory_menu_rec = $mdl_restaurant_menu->get($current_menu_rec['source_menu_id']);
						   if ( $quantity>$factory_menu_rec['qty']) {
							
							$this->form_response_msg(['code' => 2, 'error' =>  (string)$this->lang->alert_no_enough_stocks]);  //表示购物车库存大于当前库存，不能选了。
							}
							if ( $quantity>$factory_menu_rec['limit_buy_qty'] && $factory_menu_rec['limit_buy_qty']>0 && $onSpecial) {
							
							$this->form_response_msg(['code' => 3, 'error' =>  (string)$this->lang->over_buy_limit]);  //表示购物车库存大于限购库存。
						  }
						  */
						      if ( $quantity>$current_menu_rec['qty']) {
							
							$this->form_response_msg(['code' => 2, 'error' => 'oh '. (string)$this->lang->alert_no_enough_stocks]);  //表示购物车库存大于当前库存，不能选了。
							}
						   if ( $quantity>$current_menu_rec['limit_buy_qty'] && $current_menu_rec['limit_buy_qty']>0 && $onSpecial) {
							
							$this->form_response_msg(['code' => 3, 'error' => 'oh '. (string)$this->lang->over_buy_limit]);  //表示购物车库存大于限购库存。
						}
					   }else{
						   if ( $quantity>$current_menu_rec['qty']) {
							
							$this->form_response_msg(['code' => 2, 'error' => 'oh '. (string)$this->lang->alert_no_enough_stocks]);  //表示购物车库存大于当前库存，不能选了。
							}
						   if ( $quantity>$current_menu_rec['limit_buy_qty'] && $current_menu_rec['limit_buy_qty']>0 && $onSpecial) {
							
							$this->form_response_msg(['code' => 3, 'error' => 'oh '. (string)$this->lang->over_buy_limit]);  //表示购物车库存大于限购库存。
						}
					   }
					
						
						
						
						
						

                        $process = new AddCartProcess();

                        $process->owner($userId); 

                        $process->qty($quantity,'update')->addMenu($main_coupon_id,$menu_id,$this->getLangStr(),$onSpecial, $factory_id);


                        //如果主菜为0;删除主菜并删除所有配菜;
                        if($quantity==0){
                            $where['main_coupon_id']=$main_coupon_id;
                            $where['menu_id']=$menu_id;
                            $where['userId']=$userId;
                            $mdl_wj_user_temp_carts->deleteByWhere($where);
                        }

                        break;

                    case 'sidedish_menu':
                        $main_coupon_id;
                        $menu_id;
                        $sidedish_menu_id;
                        $quantity;

                        $process = new AddCartProcess();

                        $process->owner($userId); 

                        $process->qty($quantity,'update')->addMenu($main_coupon_id,$menu_id,$sidedish_menu_id);
                        break;
                    

                    case 'menu_option':
                        $main_coupon_id;
                        $menu_id;
                        $menu_option_id;
                        $quantity;

                        $process = new AddCartProcess();

                        $process->owner($userId); 

                        $process->qty($quantity,'update')->addMenu($main_coupon_id,$menu_id,$menu_option_id);
                        break;
                    
                    default:
                       
                        break;
                }

				
                
				$userId=$this->loginUser['id'];
			
			
			// 获取该商家关联的配送中心所有商家列表，并将用户在这些相关的商家列表中的购买清单全部列出来
			
			$suppliers_list = $this->get_same_dispatching_centre_suppliers_list($coupon['createUserId']);
			
			$sql ="SELECT b.restaurant_category_id,category_sort_id,category_cn_name,category_en_name,a.*,b.menu_pic as pic,d.pic as coupon_pic ,e.displayName,e.businessName FROM `cc_wj_user_temp_carts` a left join `cc_restaurant_menu` b on b.id=a.`menu_id` left join `cc_restaurant_category` c on c.id=b.restaurant_category_id left join cc_coupons d on d.id=a.main_coupon_id   left join cc_user e on e.id=a.businessUserId where a.userId=" .$userId."   and businessUserId in $suppliers_list order by businessUserId,category_sort_id,b.menu_id";
            $cartItems=$mdl_wj_user_temp_carts->getListBySql($sql);

			// 对临时购物车的记录进行校验 ，比如当前购物车的产品已经下线，或者当前购物车的产品价格已经更新，或者当前购物车里的产品库存超过库房库存 。
		

			$cartTotalPrice = 0;

			$totalMenuPrice=0;
			$totalVoucherPrice=0;
    		$old_category='0';
    		$totalQuantity=0;
			$old_business_id=0;
    		foreach ($cartItems as $key => $val) {
    			$totalQuantity+=$val['quantity'];
    			if ($val['restaurant_category_id'] !== $old_category) {
						if(!$val['restaurant_category_id']) { // 没有分类 就是团购套餐
							
							$cartItems[$key]['category_cn_name']=(string)$this->lang->group_buy;
							
						}
					
						$cartItems[$key]['new_cat']=1;
						if($old_business_id==0 || ($cartItems[$key]['businessUserId'] <> $old_business_id) ) {
							if($cartItems[$key]['displayName']){
								$cartItems[$key]['business_name'] =$cartItems[$key]['displayName'];
							}else{
								$cartItems[$key]['business_name'] =$cartItems[$key]['businessName'];
							}
							$old_business_id =$cartItems[$key]['businessUserId'];
						}
					}else{
						$cartItems[$key]['new_cat']=0;
						
					}
					$old_category=$val['restaurant_category_id'];
					if(!$val['restaurant_category_id']) {
						$cartItems[$key]['pic']=$val['coupon_pic'];
					}
					
					if ($val['main_coupon_id']== $main_coupon_id){
						
						$totalMenuPrice += $val['single_amount']*$val['quantity'];
					}else{
						
						$totalVoucherPrice += $val['single_amount']*$val['quantity'];
						
					}
					
					
					$cartTotalPrice+=$val['single_amount']*$val['quantity'];
				}
				//var_dump($cartTotalPrice);exit;
				
				$this->setData($cartTotalPrice,'totalPrice');
				$this->setData($totalQuantity,'totalQuantity');

				$this->setData($cartItems,'items');
				$this->setData($businessUserId,'businessUserId');
				



               
                
				
				$us=$this->getUserDevice();
					if($us=='desktop'){
					 $html = $this->fetch('/restaurant/ajax_cart');
					}else{
					 $html = $this->fetch('ajax_cart');
					}
				
			   
            

				$data['totalPrice'] =$cartTotalPrice;
			
                $data['totalQuantity']=$totalQuantity;
                $data['totalMenuPrice']=$totalMenuPrice;
                $data['totalVoucherPrice']=$totalVoucherPrice;
				 $data['html']=$html;
                echo json_encode($data);
                
                break;

            case "cart_info":
                $userId = $this->loginUser['id'];

                $businessUserId = get2('businessUserId');
                if(!userId||!$businessUserId)return false;

                $cartItems=$this->loadModel('wj_user_temp_carts')->getDetailedItem($userId, $businessUserId);

                $totalQuantity=0;
                $totalMenuPrice=0;
                $totalVoucherPrice=0;

             
			  
			    foreach ($cartItems as $key => $value) {

                foreach ($value['items'] as $item) {
                    $totalQuantity+=$item['quantity'];

                    if($item['EvoucherOrrealproduct']=='restaurant_menu'){
                        $totalMenuPrice+=$item['quantity']*$item['single_amount'];
                    }else{
                        $totalVoucherPrice+=$item['quantity']*$item['single_amount'];
                    }
                    
                }
				}
                $data['totalQuantity']=$totalQuantity;
                $data['totalMenuPrice']=$totalMenuPrice;
                $data['totalVoucherPrice']=$totalVoucherPrice;
				 echo json_encode($data);
            break;
            /**
             * PC版 search dropdown 提示
             */
            case 'searchv2':
                echo $this->getSearchHint(trim(post('key')));
                break;

            /**
             * 手机版 search dropdown 提示
             */
            case 'searchv2mobile':
                echo $this->getSearchHintMobile(trim(post('key')));
                break;

            /**
             * 手机版 发布产品 search dropdown 提示
             */
            case 'searchUserCoupons':
                echo $this->getSearchUserCoupons(trim(post('key')));
                break;

            case 'calculate_deliver_fee': 
                
                $mdl_wj_user_temp_carts = $this->loadModel( 'wj_user_temp_carts' );
				$mdl_user = $this->loadModel( 'user' );
                $mdl_coupons=$this->loadModel('coupons');
                $mdl_freshfood_disp_centre_suppliers=$this->loadModel('freshfood_disp_centre_suppliers');

                $business_userId = (int)get2('id');

                $cartItems=$mdl_wj_user_temp_carts->getDetailedItem($this->loginUser['id'], $business_userId);

				$address = trim(get2('address'));

                $distance = $this->get_distance_two_address($address, $business_userId,0);
				
				
			  /*  获得距离输出
			  
			  $output['distance']=$distance;
              $output['msg']='ok';
               
                echo json_encode($output);
				exit;
				*/
                
                $items = reset($cartItems);
				//var_dump($items['centre_dispatch']);exit;
				if($items['centre_dispatch']==1) { //表示统一配货，那么统一配货的购物车总额在这里计算
					//获得当前统配商家的免运费金额
					
					$sql ="select DISTINCT business_id as businessUserId from cc_freshfood_disp_centre_suppliers where suppliers_id =$business_userId";
		
					$dispaitch_business = $mdl_freshfood_disp_centre_suppliers->getListBySql($sql);
					
					if($dispaitch_business ) { 
					 $dispaitch_business_id =$dispaitch_business[0]['businessUserId'];
					
					}
					$business_info =$mdl_user->getBusinessDeliveryInfo($dispaitch_business_id,$this->getLangStr());
					$amount_for_free_delivery =(float)$business_info['amount_for_free_delivery'];
					
				    $totalAmount =0;
					$total_deliver_fee = $business_info['base_local_rate'];
					// 获得当前商家的购物车总额
						foreach ($cartItems as $key => $per_business_items) {
							
								foreach ($per_business_items['items'] as $key1 => $value) {
									
										$totalAmount+=(float)$value['single_amount']*(int)$value['quantity'];
								}
						}
					
					// 如果满足免运费，则0，否则，设置运费，返回
					if($totalAmount>=$amount_for_free_delivery&&$amount_for_free_delivery>0){
						$total_deliver_fee = 0;
					}
					
					//根据订单的的额度和距离获取高级运费设置中的运费。
					
					$mdl_custom_freight_rates =$this->loadModel('custom_freight_rates');
					$where11 = ['business_id' => $business_userId]; 
					$freight_rates_arr = $mdl_custom_freight_rates->getList([],$where11,'end_amount1');
					
					
					if($distance && $freight_rates_arr) { //如果距离大于0 且有定制数据
						//var_dump($total_deliver_fee.' ');
						
						$total_deliver_fee =$this->get_custom_delivery_fee($freight_rates_arr,$distance,$totalAmount,$business_userId);
						
						
						//var_dump($distance.' ');
						//var_dump($total_deliver_fee.' ');
						//var_dump($totalAmount. ' ');
						//var_dump($business_userId);
						//exit;
					}
					
					
					
					
					
					/**
					 * 合单运费计算。 同地址第二单免运费
					 */
					$combineDeliver = true;
					$orderCombineTimeLimit = 2 * 24 * 3600; //2days

					$address = trim(get2('address'));

					if($address && $combineDeliver && $total_deliver_fee>0){
						$where['userId']=$this->loginUser['id'];
						$where['business_userId']=$dispaitch_business_id;
						$where['coupon_status']='c01';
						$where['customer_delivery_option']='1';
						$where['address']=$address;
						$where[]=" createTime > " .(time() - $orderCombineTimeLimit);

						$result = $this->loadModel('order')->getList(['orderId','createTime'],$where);
//var_dump($result);exit;
						if(sizeof($result)>=1){
							$total_deliver_fee=0;
							$msg=(string)$this->lang->delivery_fee_combine_free;
						};
					}
					
					
					
				}else{
					//var_dump('not-dump');exit;
					$base_local_rate=0;
					$base_national_rate=0;
					$base_international_rate=0;
					$amount_for_free_delivery=0;

					$flat_rates_to_local_city=0;
					$flat_rates_national=0;
					$flat_rates_international=0;

					$totalAmount = 0;

					$msg='';

					foreach ($items['items'] as $key => $value) {
						$deliver_info = $mdl_coupons->getDeliveryInfo($value['main_coupon_id']);

						$base_local_rate          =(float)$deliver_info['base_local_rate'];
						$base_national_rate       =(float)$deliver_info['base_national_rate'];
						$base_international_rate  =(float)$deliver_info['base_international_rate'];
						
						$amount_for_free_delivery =(float)$deliver_info['amount_for_free_delivery'];

						$totalAmount+=(float)$value['single_amount']*(int)$value['quantity'];

						if($deliver_info['deliverFeeCalculationType']=='per_coupon'){

							$flat_rates_to_local_city +=(float)$deliver_info['flat_rates_to_local_city'];
							$flat_rates_national      +=(float)$deliver_info['flat_rates_national'];
							$flat_rates_international +=(float)$deliver_info['flat_rates_international'];

						}elseif($deliver_info['deliverFeeCalculationType']=='per_coupon_item'){

							$flat_rates_to_local_city +=(int)$value['quantity'] * (float)$deliver_info['flat_rates_to_local_city'];
							$flat_rates_national      +=(int)$value['quantity'] * (float)$deliver_info['flat_rates_national'];
							$flat_rates_international +=(int)$value['quantity'] * (float)$deliver_info['flat_rates_international'];

						}
					}

					$total_rates_to_local_city= $flat_rates_to_local_city + $base_local_rate;
					$total_rates_national= $flat_rates_national + $base_national_rate;
					$total_rates_international=$flat_rates_international + $base_international_rate;

					//calculation base on local rate ;
					$total_deliver_fee=$total_rates_to_local_city;
				//	 var_dump( $amount_for_free_delivery);exit;
					if($totalAmount>=$amount_for_free_delivery&&$amount_for_free_delivery>0){
						$total_deliver_fee = 0;
					}


				 //根据订单的的额度和距离获取高级运费设置中的运费。
					
					$mdl_custom_freight_rates =$this->loadModel('custom_freight_rates');
					$where11 = ['business_id' => $business_userId]; 
					$freight_rates_arr = $mdl_custom_freight_rates->getList([],$where11,'end_amount1');
					
					
					if($distance && $freight_rates_arr) { //如果距离大于0 且有定制数据
						//var_dump($total_deliver_fee.' ');
						
						$total_deliver_fee =$this->get_custom_delivery_fee($freight_rates_arr,$distance,$totalAmount,$business_userId);
						
						
						//var_dump($distance.' ');
						//var_dump($total_deliver_fee.' ');
						//var_dump($totalAmount. ' ');
						//var_dump($business_userId);
						//exit;
					}

					// echo $total_deliver_fee;

					/**
					 * 合单运费计算。 同地址第二单免运费
					 */
					$combineDeliver = true;
					$orderCombineTimeLimit = 2 * 24 * 3600; //2days

					$address = trim(get2('address'));

					if($address && $combineDeliver && $total_deliver_fee>0){
						$where['userId']=$this->loginUser['id'];
						$where['business_userId']=$business_userId;
						$where['coupon_status']='c01';
						$where['customer_delivery_option']='1';
						$where['address']=$address;
						$where[]=" createTime > " .(time() - $orderCombineTimeLimit);

						$result = $this->loadModel('order')->getList(['orderId','createTime'],$where);

						if(sizeof($result)>=1){
							$total_deliver_fee=0;
							$msg=(string)$this->lang->delivery_fee_combine_free;
						};
					}
					
					
			}

            

                $output['total_deliver_fee']=$total_deliver_fee;
                $output['msg']=$msg;
				$output['distance']=$distance;
               
                echo json_encode($output);

                break;
            
            /**
             * 首页还没逛够查看更多
             */
            case 'getLatestCouponHtml':
                $offset = 12;
                $page = (int)trim(get2('page'));
                $offset = $offset * $page;

                $newestSql = " select * from #@_coupons a where a.isApproved=1 ". $this->get_multiLanguage_where('a')." and a.status=4 and a.EvoucherOrrealproduct <> 'restaurant_menu' ";
                $newestSql .= " order by a.createTime desc limit " . $offset . ",12";
               $mdl_coupons =$this->loadModel('coupons');
			   $latest_coupons = $mdl_coupons->getListBySql($newestSql);
				foreach ($latest_coupons as $key =>$value) {
				   if($this->getLangStr()=='en' && $latest_coupons[$key]['title_en']){
					   
					   $latest_coupons [$key]['title']=  $latest_coupons [$key]['title_en'];
				   }else{
					   
					   
				   }
                  $mdl_coupons->caculatePriceAndPoint($latest_coupons[$key]);
              }
 
                $this->setData($latest_coupons, 'latest_coupons');
                echo $this->fetch('index_inc_latest_coupons');
                break;

             /**
             * 代购页还没逛够查看更多
             */
			 case 'getLatestCouponHtml_mobile':
                $offset = 12;
                $page = (int)trim(get2('page'));
                $offset = $offset * $page;

                $newestSql = " select * from #@_coupons a where a.isApproved=1 ". $this->get_multiLanguage_where('a')." and a.status=4 and a.EvoucherOrrealproduct <> 'restaurant_menu' ";
                $newestSql .= " order by a.createTime desc limit " . $offset . ",12";
               $mdl_coupons =$this->loadModel('coupons');
			   $latest_coupons = $mdl_coupons->getListBySql($newestSql);
				foreach ($latest_coupons as $key =>$value) {
					if($this->getLangStr()=='en' && $latest_coupons[$key]['title_en']){
					   
					   $latest_coupons [$key]['title']=  $latest_coupons [$key]['title_en'];
				   }else{
					   
					   
				   }
                  $mdl_coupons->caculatePriceAndPoint($latest_coupons[$key]);
              }
 

                $this->setData($latest_coupons, 'latest_coupons');
                echo $this->fetch('index_inc_latest_coupons_mobile');
                break;

             /**
             * 代购页还没逛够查看更多
             */
            case 'getDaigouList':
                $offset = 12;
                $page = (int)trim(get2('page'));
                $offset = $offset * $page;

                $newestSql = " select * from #@_coupons a where a.categoryid like '%,106105118%' AND a.isApproved=1 and a.status=4 and !(a.autoOffline=1 AND ('$currentTime'<a.startTime or '$currentTime'>a.endTime))";
                $newestSql .= " order by a.createTime desc limit " . $offset . ",12";
                $latest_coupons = $this->loadModel('coupons')->getListBySql($newestSql);

                $this->setData($latest_coupons, 'latest_coupons');
                echo $this->fetch('index_inc_latest_coupons');
                break;

            /**
             * 购物车更新 送货地址
             */
            case 'saveAddress':
                $addressID=trim(get2('type'));
                $data = array(
                    'userId'=>$this->loginUser['id'],
                    'createTime'=>time(),
                    'first_name'=>trim(get2('firstname')),
                    'last_name'=>trim(get2('lastname')),
                    'address'=>trim(get2('address')),
                    'phone'=>trim(get2('phone')),
                    'email'=>trim(get2('email')),
					'Street_number'=>trim(get2('addr_house_number')),
					
                    'id_number'=>trim(get2('id_number'))
                    //'isDefaultAddress'=>$isDefaultAddress,
                    //'country'=>$country
                );
                if ($addressID=="0")
                {
                    echo $this->loadModel('wj_user_delivery_info')->insert($data);
                }
                else  if($this->loadModel('wj_user_delivery_info')->update($data,$addressID)=='1')
                {
                     echo $addressID;
                }
                break;
			
			case 'fav_add':
                $userId = get2('userId');
                $itemId = get2('itemId');
                $type = get2('type');

                $mdl_fav = $this->loadModel('fav');

                if($mdl_fav->exist($userId,$itemId,$type)){
                    //already in;
                    echo 'error';
                }else{
                     $mdl_fav->add($userId,$itemId,$type);
                     echo 'success';
                }
                break;
			case 'fav_remove':
                $userId = get2('userId');
                $itemId = get2('itemId');
                $type = get2('type');

                $mdl_fav = $this->loadModel('fav');

                if($mdl_fav->exist($userId,$itemId,$type)){
                    $mdl_fav->remove($userId,$itemId,$type);
                    echo 'success';
                }else{
                    //no record;
                     echo 'error';
                }

                break;

            /**
             * 购物车 hcash 实时汇率
             */
             case 'hcash_rate':
                
                
                
                $url = 'https://acx.io:443//api/v2/tickers.json';

                $rowJsonData = file_get_contents($url);

                $dataArray = json_decode($rowJsonData,true);
                
                $hrs_to_aud=$dataArray['hsraud']['ticker']['sell'];

                $hrs_to_aud=floatval($hrs_to_aud);

                $hrs_to_aud= $hrs_to_aud/1.1;

                $rate = 1/$hrs_to_aud;

                $data['hsr_rate']=$rate;


                //http://api.fixer.io/latest?base=AUD
                //AUD TO CNY
                
                 $url = 'http://api.fixer.io/latest?base=AUD';

                $rowJsonData = file_get_contents($url);

                $dataArray = json_decode($rowJsonData,true);

                $aud_to_cny=$dataArray['rates']['CNY'];

                $data['aud_to_cny']=$aud_to_cny;

                echo json_encode($data);

                break;
				
				
				
				case "set_business_trading_hours":
				  $trading_hours =trim(get2('trading_hours'));
				  
				  $trading_hours_desc=trim(get2('trading_hours_desc'));
				  
				  $customer_id = trim(get2('customer_id'));
				  $mdl_user = $this->loadModel('user');
                    
                   if($this->loginUser['id']) {
					   $data=array(
					     'trading_hours'=>$trading_hours,
						 'trading_hours_desc'=>$trading_hours_desc
					   );
					   
					 if($customer_id ){
						 $update_user_id =$customer_id;						 
					 }else{
						  $update_user_id =$this->loginUser['id'];		
						 
					 }
					 if($mdl_user->update($data,$update_user_id)){
						 
						  if(!$customer_id ){
							  $this->loginUser['trading_hours']=$trading_hours;
						      $this->loginUser['trading_hours_desc']=$trading_hours_desc;
						  }
						 
						 echo $this->loginUser['trading_hours'].'修改成功';
						 
					 }else{
						 
						 echo '修改失败';
					 }
				   }
				  
				  break;
				
				
				
				
				

                /**
                 * 助攻活动用户更新信息
                 */
                case "savePlayerInfo":

                    $userId = $this->loginUser['id'];

                    $phone =trim(get2('phone'));

                    $nickname =trim(get2('nickname'));

                    $mdl_user= $this->loadModel('user');

                    if($mdl_user->get($userId)){
                        
                        $data['phone']= $phone;

                        $data['nickname']= $nickname;

                        $mdl_user->update($data,$userId);

                        echo 'success';
                    }else{
                        echo '用户不存在';
                    }
                    break;
        }
        exit;
    }
	
	
	public function update_unsuccessful_trading_status_action()
	{
		if(is_post()){
			$id = post('id');
			$userId = post('user_id');
			$status = post('status');

			$where =array (
			  'id<='.$idb,
			  'userId'=>$userId
			);
			
			$data =array(
			  'contacted '=>$status
			
			);
			$result=$this->loadModel('wj_temp_orderid_carts_for_yunying')->updateByWhere($data,$where);
			if($result){
				//success
				echo 'success';
			}else{
				//fail
				echo 'fail';
			}
		}else{
			//fail
			echo 'fail';
		}		
		
	}
	

    function getSearchHint($key)
    {

        $str = '';
        $count = 0;
        $mdl_coupons = $this->loadModel('coupons');

        $sql1 = "select title as name from #@_coupons where title like '%" . $key . "%' and isApproved ='1' and (city like ',%" . $this->city['id'] . "%,' or city='') and status = '4' limit 10";

        $sql2 = "select businessName from #@_user where businessName like '%" . $key . "%' and isApproved ='1' and (cityId like ',%" . $this->city['id'] . "%,' or cityId='') and role = '3' limit 10";

        $data_coupons = $mdl_coupons->getListBySql($sql1);
        $data_business = $mdl_coupons->getListBySql($sql2);

        foreach ($data_business as $key => $val) {
            $count = $count + 1;
            $str .= '<li  onclick="gotoSearchItem(this);" data-hint-type="business">商家: ' . '<span class="search-hint-text">' . mb_substr($val["businessName"], 0, 25) . '</span>' . '</li>';
        }

        foreach ($data_coupons as $key => $val) {
            $count = $count + 1;
            $str .= '<li  onclick="gotoSearchItem(this);" data-hint-type="coupon"> ' . '<span class="search-hint-text">' . mb_substr($data_coupons[$key]["name"], 0, 25) . '</span>' . '</li>';
        }
        return $str;

    }

    function getSearchHintMobile($key)
    {

        $str = '';
        $count = 0;
        $mdl_coupons = $this->loadModel('coupons');

        $sql1 = "select pic,title,voucher_deal_amount as name from #@_coupons where title like '%" . $key . "%' and isApproved ='1' and city like ',%" . $this->city['id'] . "%,' and status = '4' limit 50";
        $sql2 = "select logo,businessName from #@_user where businessName like '%" . $key . "%' and isApproved ='1' and cityId = '" . $this->city['id'] . "' and role = '3' limit 50";
        $sql3 = "select u.displayName,menu_cn_name,menu_en_name,menu_pic,price,restaurant_id from #@_restaurant_menu m left join cc_user u on m.restaurant_id = u.id left join cc_coupons c on m.restaurant_id =c.createUserId  where c.EvoucherOrrealproduct ='restaurant_menu' and  (menu_cn_name  like '%" . $key . "%' or menu_en_name  like '%" . $key . "%' ) and c.isApproved=1 and c.status=4  and visible=1 limit 200  ";

        $data_menu = $mdl_coupons->getListBySql($sql3);
        $data_coupons = $mdl_coupons->getListBySql($sql1);
        $data_business = $mdl_coupons->getListBySql($sql2);

        foreach ($data_menu as $key => $val) {
            $count = $count + 1;
			//if ($val['displayName']) {
			//	$title1 = mb_substr($val["menu_cn_name"], 0, 30).'-'.mb_substr($val['displayName'],0,20);
			//}
			$title1 = mb_substr($val["menu_cn_name"], 0, 20);
			if ($val['displayName']) {
				
				$title2 = '<dt style="    float: right;"> <span class="search-hint-text" style="      color: #927e7e;  margin-left: 10px;">'.$val["displayName"].'</span></dt>';
			}
            $str .= '<li  onclick="gotoSearchItem(this);" data-hint-type="coupon"> <dl><dt>' . '<img style="width:66px;height:66px;" src="/data/upload/'. $val['menu_pic'].'"> <span class="search-hint-text" style="    margin-left: 10px;">' . $title1 . '($'.$val['price'].')</span>' . '</dt>'.$title2.'</dl></li>';
        }


	    foreach ($data_business as $key => $val) {
            $count = $count + 1;
            $str .= '<li  onclick="gotoSearchItem(this);" data-hint-type="business"><dl><dt>商家: ' . '<img style="width:66px;height:66px;" src="/data/upload/'. $val['logo'].'"> <span class="search-hint-text" style="    margin-left: 10px;">' . mb_substr($val["businessName"], 0, 25) . '</span>' . '</dt></dl></li>';
        }

        foreach ($data_coupons as $key => $val) {
            $count = $count + 1;
            $str .= '<li  onclick="gotoSearchItem(this);" data-hint-type="coupon"> <dl><dt>' . '<img style="width:66px;height:66px;" src="/data/upload/'. $val['pic'].'"> <span class="search-hint-text" style="    margin-left: 10px;">' . mb_substr($data_coupons[$key]["name"], 0, 25) . '('.$val['voucher_deal_amount'].')</span>' . '</dt></dl></li>';
        }
        return $str;

    }

    function getSearchUserCoupons($key)
    {
        $str = '';
        $sql = "select id,title as name from #@_coupons where createUserid = " . $this->loginUser['id'] . " and title like '%" . $key . "%' and isApproved ='1'  and status = '4' limit 20";

        $data = $this->loadModel("coupons")->getListBySql($sql);


        foreach ($data as $key => $val) {
            $str .= '<li  onclick="gotoSearchItem(this);" data-hint-type="' . $data[$key]["id"] . '"> <dl><dt>' . '<span class="search-hint-text">' . mb_substr($data[$key]["name"], 0, 25) . '</span>' . '</dt></dl></li>';
        }
        return $str;
    }

	
	 function get_cart_item_ajax($businessUserId){
		
				$mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');

                 $userId=$this->loginUser['id'];
                //$businessUserId = get2('businessUserId');
				     $sql ="SELECT category_sort_id,category_cn_name,a.*,b.menu_pic as pic ,d.pic as coupon_pic FROM `cc_wj_user_temp_carts` a left join `cc_restaurant_menu` b on b.id=a.`menu_id` left join `cc_restaurant_category` c on c.id=b.restaurant_category_id left join cc_coupons d on d.id=a.main_coupon_id  where a.userId=" .$userId."   and businessUserId =".$businessUserId . " order by category_sort_id,b.menu_id";
           
                if(!$userId||!$businessUserId)return false;

               // $cartItems=$mdl_wj_user_temp_carts->getDetailedItem($userId, $businessUserId);
			   
			   $cartItems=$mdl_wj_user_temp_carts->getListBySql($sql);

                $cartTotalPrice = 0;
				
				
				$old_category='0';
				foreach ($cartItems as $key => $val) {
					if ($val['category_sort_id'] !== $old_category) {
						if(!$val['category_sort_id']) { // 没有分类 就是团购套餐
							
							$cartItems[$key]['category_cn_name']='团购';
						}
						$cartItems[$key]['new_cat']=1;
					}else{
						$cartItems[$key]['new_cat']=0;
						
					}
					$old_category=$val['category_sort_id'];
					if(!$val['category_sort_id']) {
						$cartItems[$key]['pic']=$val['coupon_pic'];
					}
					
					
					
		            $cartTotalPrice+=$val['single_amount']*$val['quantity'];
				}
				
                $this->setData($cartTotalPrice,'totalPrice');

                $this->setData($cartItems,'items');
                $this->setData($businessUserId,'businessUserId');
                
				
				$us=$this->getUserDevice();
					if($us=='desktop'){
					 $html = $this->fetch('/restaurant/ajax_cart');
				}else{
					 $html = $this->fetch('ajax_cart');
				}
				
			   
                return $html;
		
	}
	
	
	
	function get_distance_two_address($address,$business_id,$isCBD) {
	
	
	if($isCBD) {
		$cbdAddress ='CBD, Melbourne VIC 3000';
	}else{
		$cbdAddress =0;
	}
	
   // $address='15 Gum tree close croyon 3136';
	
	
	//获得客户地址
	$customer_address_latitude = $this->get_address_latitude($address,0);
	//var_dump($customer_address_latitude);
	
	if(!$customer_address_latitude) 
	{
		var_dump('无法获得客户地址位置！');
	}
	
	//获得商家地址
	
	$business_address_latitude=$this->get_business_address_latitue($business_id,$cbdAddress);
	
	if(!$business_address_latitude) 
	{
		var_dump('无法获得商家地址位置1！');
	}
	
	//var_dump($business_address_latitude);exit;
	
	
    $cust_lat_arr = explode(',', $customer_address_latitude);
	
	
	$busi_lat_arr = explode(',', $business_address_latitude);
	
	$distance =$this->calculateDistanceBetweenTwoPoints($cust_lat_arr[0], $cust_lat_arr[1], $busi_lat_arr[0], $busi_lat_arr[1],'KM',false,2);
	
	// chengdu ziwei 2 address pick a close one for customer 
	
	if($business_id == 320985) {
		
		 $business_address_latitude1 = $this->get_address_latitude('14 Star Cres, Docklands VIC 3008',1);
		 $busi_lat_arr1 = explode(',', $business_address_latitude1);
		

		 $business_address_latitude2 = $this->get_address_latitude('1/369- 369 Clayton Rd, Clayton VIC 3168',1);
		 $busi_lat_arr2 = explode(',', $business_address_latitude2);
		
		$distance1 =$this->calculateDistanceBetweenTwoPoints($cust_lat_arr[0], $cust_lat_arr[1], $busi_lat_arr1[0], $busi_lat_arr1[1],'KM',false,2);
		
		$distance2=$this->calculateDistanceBetweenTwoPoints($cust_lat_arr[0], $cust_lat_arr[1], $busi_lat_arr2[0], $busi_lat_arr2[1],'KM',false,2);
		
		if($distance1<=$distance2){
			$distance =$distance1;
			
		}else{
			$distance =$distance2;
			
		}
		
		//var_dump($distance1.' ' . $distance2);exit;
	}
	
	
	// chengdu ziwei end 
	
	
	
	
	
	
    $distance =$distance*1.2;
	//var_dump($distance); exit;
	return $distance;
}
	
	
	function load_menu($id){
		
		// 获取的餐馆ID--是商家的ID ,判断当前商家是否设置餐厅入口
		$where =array(
		   'createUserId' => $id,
		   'EvoucherOrrealproduct' =>'restaurant_menu'
		);
		
		$mdl_coupons =$this->loadModel("coupons");
		
		$restaurant_coupon= $mdl_coupons->getByWhere($where);
		$mdl_restaurant_menu = $this->loadModel( 'restaurant_menu' );
		$where1=array(
		   'restaurant_id' => $id,
		  );
	   	$mdl_restaurant_promotion_manjian =$this->loadModel("restaurant_promotion_manjian");
		
	   	$restaurant_promotion_manjian=$mdl_restaurant_promotion_manjian->getByWhere($where1);
		//var_dump($id);exit;
		if($restaurant_promotion_manjian) {
		   $restaurant_promotion_manjian_rates =$restaurant_promotion_manjian[discount]/100;
	   }else{
		   $restaurant_promotion_manjian_rates=0;
	   }
	  
	  
       	if(!$this->loginUser) {
		   	$sql="select c.category_cn_name,a.*,b.category_id as restaurant_category_id from cc_restaurant_category c,cc_restaurant_menu a, cc_restaurant_category b where a.restaurant_id=".$id . "  and (length(a.menu_cn_name)>0 or length(a.menu_en_name)>0) and  c.id=a.restaurant_category_id  and b.id = a.restaurant_category_id order by b.category_sort_id,menu_id";
		   	$menu = $mdl_restaurant_menu->getListBySql($sql);
			
			//显示新价格
			foreach ($menu as $key => $value) {
				$menu[$key]['new_price'] =number_format($menu[$key]['price'] *(1-$restaurant_promotion_manjian_rates),2);

				
			}
	     }else{
	     	//清除购物车其它产品
			 $mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');
			 $where =array (
				 'businessUserId <>' . $id,
				 'userId '=> $this->loginUser['id']
			 );
			$mdl_wj_user_temp_carts->deleteByWhere($where);
			 

			//菜单列表
			$sql="select c.category_cn_name,a.*,b.category_id as restaurant_category_id,b.category_sort_id from cc_restaurant_category c,cc_restaurant_menu a, cc_restaurant_category b where a.restaurant_id=".$id . "  and (length(a.menu_cn_name)>0 or length(a.menu_en_name)>0) and  c.id=a.restaurant_category_id  and b.id = a.restaurant_category_id order by b.category_sort_id,menu_id";
		   	$menu = $mdl_restaurant_menu->getListBySql($sql);
			

			//显示新价格
			foreach ($menu as $key => $value) {
				$menu[$key]['new_price'] =number_format($menu[$key]['price'] *(1-$restaurant_promotion_manjian_rates),2);

				//加载购物车已购买数量
				$sql ="select quantity from cc_wj_user_temp_carts where main_coupon_id=".$restaurant_coupon['id']." and menu_id=".$value['id']." and sidedish_menu_id=0 and userId=".$this->loginUser['id'];
	  
				$result = $mdl_wj_user_temp_carts->getListBySql($sql);
				$menu[$key]['quantity']=$result[0]['quantity'];
			}

			//加载配菜
			$mdl_sidedish_menu=$this->loadModel('restaurant_sidedish_menu');
			foreach ($menu as $key => $value) {
				if($menu[$key]['sidedish_category']>0){
					$menu[$key]['sidedish_menu']=$mdl_sidedish_menu->getList(null,array('restaurant_id'=>$menu[$key]['restaurant_id'],'restaurant_category_id'=>$menu[$key]['sidedish_category']," (length(menu_cn_name)>0 or length(menu_en_name)>0) "));

					foreach ($menu[$key]['sidedish_menu'] as $k => $v) {
						//配菜新价格
						$menu[$key]['sidedish_menu'][$k]['new_price'] =number_format($menu[$key]['sidedish_menu'][$k]['price'] *(1-$restaurant_promotion_manjian_rates),2);
					}
					
				}
			}


			//加载菜品规格
			$mdl_menu_option=$this->loadModel('restaurant_menu_option');
			foreach ($menu as $key => $value) {
				if($menu[$key]['menu_option']>0){
					$menu[$key]['menu_option_list']=$mdl_menu_option->getList(null,array('restaurant_id'=>$menu[$key]['restaurant_id'],'restaurant_category_id'=>$menu[$key]['menu_option']," (length(menu_cn_name)>0 or length(menu_en_name)>0) "));

					foreach ($menu[$key]['menu_option_list'] as $k => $v) {
						//配菜新价格
						$menu[$key]['menu_option_list'][$k]['new_price'] =number_format($menu[$key]['menu_option_list'][$k]['price'] *(1-$restaurant_promotion_manjian_rates),2);
					}
					
				}
			}
		
			

		}

		

		$old_cat="";
		foreach ($menu as $key => $value) {
			$new_cat =$menu[$key]['category_cn_name'];
			if($old_cat<>$new_cat) {
				$menu[$key]['new_cat']=$new_cat;
				$old_cat=$new_cat;
			}else{
				$menu[$key]['new_cat']=0;
			}
		}
		$this->setData( $restaurant_coupon, 'coupon' );
		  $this->setData( $restaurant_coupon['id'], 'restaurant_couponID' );
         $this->setData( $restaurant_promotion_manjian_rates*100, 'restaurant_promotion_manjian_rates' );
		 $this->setData($restaurant_promotion_manjian,'restaurant_promotion_manjian');
	    	$us=$this->getUserDevice();
					if($us=='desktop'){
					$menu1 = $this->fetch('/mobile/restaurant/menu');
					
				}else{
					$menu1 = $this->fetch('/mobile/restaurant/menu');
					
				}
		
		return  $menu1 ;
	}
}