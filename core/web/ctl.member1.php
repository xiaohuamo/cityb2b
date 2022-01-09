<?php

//会员

class ctl_member1 extends cmsPage
{

	function ctl_member1() {
		parent::cmsPage();


		$act = $GLOBALS['gbl_act'];
		$ignore_list = array('bind_wx');
		if ( !in_array($act, $ignore_list) && !$this->loginUser ) {
			$this->sheader( HTTP_ROOT_WWW.'member/login?returnUrl='.urlencode( $_SERVER['REQUEST_URI'] ) );
		}

	}

    function account_setting_action() {



        if ( is_post() ) {

            $email =  trim( post( 'email' ) );
            $nickname = trim( post( 'nickname' ) );
            $person_first_name = trim( post( 'person_first_name' ) );
            $person_last_name = trim( post( 'person_last_name' ) );
            $tel = trim( post( 'tel' ) );
            $phone = trim( post( 'phone' ) );

            $mdl_user = $this->loadModel( 'user' );
            $mdl_reg = $this->loadModel( 'reg' );

            if ( $email&&!$mdl_reg->chkMail( $email ) ) {
                if($this->getUserDevice()=='desktop'){
                    $this->form_response_msg((string)$this->lang->wrong_email);
                }else{

                    $this->sheader(null,(string)$this->lang->wrong_email);
                }
            }

            if ( $phone&&!$mdl_reg->chkPhone( $phone ) ) {
                if($this->getUserDevice()=='desktop'){
                    $this->form_response_msg((string)$this->lang->only_10_digital_phone);
                }else{
                    $this->sheader(null,(string)$this->lang->only_10_digital_phone);
                }
            }

            $data = array();
            $data['email'] = $email;
            $data['nickname'] = $nickname;
            $data['person_first_name'] = $person_first_name;
            $data['person_last_name'] = $person_last_name;
            $data['tel'] = $tel;
            $data['phone'] = $phone;

            if($this->loginUser['phone']!=$phone)$data['phone_verified']='false';

            if($this->getUserDevice()=='desktop'){
                if ( $mdl_user->updateUserById( $data, $this->loginUser['id'] ) ) {
                    $this->form_response(200,(string)$this->lang->update_success,HTTP_ROOT_WWW."member/index");
                }
                else {
                    $this->form_response_msg((string)$this->lang->update_failure);
                }
            }else{
                if ( $mdl_user->updateUserById( $data, $this->loginUser['id'] ) ) {
                    $this->sheader(HTTP_ROOT_WWW."member/index");
                }
                else {

                }
            }

        }
        else {
            $this->setData( 'Personal setting', 'pagename' );
            $this->setData(get2('side'),'side');
            $this->setData( 'business_setting','menu' );
            $this->setData( 'profile_manager', 'submenu' );
            $this->setData( 'Personal setting - '.$this->site['pageTitle'], 'pageTitle' );
            $this->display_pc_mobile('member/profile','member1/accountSetting');
        }
    }


    function changepwd_action() {

        $menu_item = trim(get2( 'menu_item' ));
        $side = trim(get2( 'side' ));

        if ( is_post() ) {
            $oldpwd		= trim( post('oldpwd') );
            $password	= trim( post('password') );
            $password2	= trim( post('password2') );


            if ( $this->md5( $oldpwd ) != $this->loginUser['password'] ) {

                if($this->getUserDevice()=='desktop'){
                    $this->form_response_msg((string)$this->lang->old_password_incorrect);
                }else{

                    $this->sheader(null,(string)$this->lang->old_password_incorrect);
                }

            }

            $mdl_reg = $this->loadModel( 'reg' );
            if ( ! $mdl_reg->chkPassword( $password ) ) {

                if($this->getUserDevice()=='desktop'){
                    $this->form_response_msg((string)$this->lang->password_requirement);
                }else{

                    $this->sheader(null,(string)$this->lang->password_requirement);
                }


            }
            if ( $password != $password2 ) {

                if($this->getUserDevice()=='desktop'){
                    $this->form_response_msg((string)$this->lang->remind_user_register_7);
                }else{

                    $this->sheader(null,(string)$this->lang->remind_user_register_7);
                }


            }

            $passwordByCustomMd5 = $this->md5( $password );
            $data = array(
                'password' => $passwordByCustomMd5
            );
            $mdl_user = $this->loadModel('user');
            if ( $mdl_user->updateUserById( $data, $this->loginUser['id'] ) ) {
                $this->session( 'member_user_id', $this->loginUser['id'] );
                $this->session( 'member_user_shell', $this->md5( $this->loginUser['id'].$this->loginUser['name'].$passwordByCustomMd5 ) );



                if($this->getUserDevice()=='desktop'){
                    $this->form_response_msg((string)$this->lang->update_success);

                }else{
                    $this->sheader(HTTP_ROOT_WWW."member/index");
                }


            }
            else {

                if($this->getUserDevice()=='desktop'){
                    $this->form_response_msg((string)$this->lang->update_failure);

                }else{
                    $this->sheader(null,(string)$this->lang->update_failure);
                }


            }
        }
        else {


            if($side=='company') {
                $this->setData( 'basic_setting','menu' );
            }else{
                $this->setData( 'user_setting','menu' );

            }
            $this->setData( 'changepwd', 'submenu' );
            $this->setData( 'Change Password'.$this->site['pageTitle'], 'pageTitle' );

            $this->display_pc_mobile('member/changepwd','member1/passwordSetting');
        }
    }


    public function get_category_list_action() {

        $id = (int) get2('id');
        $id =319188;

        $categoryList =$this->loadModel('restaurant_category')->getCategoryList($id);
        echo json_encode($categoryList);

    }
// 获得某个客户关于某个商家的购物清单，这个数据用在订货页面
    public function get_cart_action()
    {
        $id = (int) get2('businessUserId');

        $userId = (int) get2('user_id');
        if (!$userId) {
            $userId = $this->loginUser['id'];
        }

        if ($userId) {
            //清除购物车其它产品
            $mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');

            // 获取该商家关联的配送中心所有商家列表，并将用户在这些相关的商家列表中的购买清单全部列出来

            //$suppliers_list = $this->get_same_dispatching_centre_suppliers_list($id);

           // $sql = "SELECT b.restaurant_category_id,category_sort_id,category_cn_name,category_en_name,a.*,b.menu_pic as pic,d.pic as coupon_pic ,e.displayName,e.businessName FROM `cc_wj_user_temp_carts` a left join `cc_restaurant_menu` b on b.id=a.`menu_id` left join `cc_restaurant_category` c on c.id=b.restaurant_category_id left join cc_coupons d on d.id=a.main_coupon_id   left join cc_user e on e.id=a.businessUserId where a.userId=".$userId."   and businessUserId =$id order by businessUserId,category_sort_id,b.menu_id";
           // $cartItems = $mdl_wj_user_temp_carts->getListBySql($sql);
          $uploadPath =UPLOAD_PATH;
           $sql ="select a.id as idd,a.userId,a.businessUserId,a.coupon_name as title,a.quantity as num,a.single_amount as price,a.guige_des,a.guige_ids,a.menu_id as id,a.coupon_name_en,a.onSpecial,if(length(b.menu_pic)>0,concat('$uploadPath',b.menu_pic),'') as menu_pic
            from cc_wj_user_temp_carts a 
                left join cc_restaurant_menu b on a.menu_id =b.id 
            where a.userId=$userId and a.businessUserId=$id ";


           $cartItems = $mdl_wj_user_temp_carts->getListBySql($sql);
            //  var_dump($sql);exit;

            // 对临时购物车的记录进行校验 ，比如当前购物车的产品已经下线，或者当前购物车的产品价格已经更新，或者当前购物车里的产品库存超过库房库存 。
            $mdl_restaurant_menu = $this->loadModel('restaurant_menu');

            $mdl_user_factory_menu_price = $this->loadModel('user_factory_menu_price');
            $userFactoryMenuPrices = $mdl_user_factory_menu_price->getUserFactoryPriceList($userId, $id);
            foreach ($cartItems as $key => $val) {

                $cartItems[$key] ['isTouch'] =false;
                $cartItems[$key] ['status'] =0;
                $cartItems[$key] ['hasGG'] =false;

                $menu_rec = $mdl_restaurant_menu->get($val['id']);
                if (! $menu_rec) {
                    $mdl_wj_user_temp_carts->delete($val['idd']); // 如果菜单中没有这个，那么从临时购物车中删除。
                    continue;
                } else {

                    // 如果找到菜单中的项目，检查是否已经下线，如果下线，则删除

                    if (! $menu_rec['visible']) {
                        $mdl_wj_user_temp_carts->delete($val['idd']);
                    }

                    ///如果是特价产品
                    if ($val['onSpecial']) {

                        if ($menu_rec['speical_price'] != $val['single_amount']) {


                            $new_item_price_data = [
                                'single_amount' => $menu_rec['speical_price'],
                            ];
                            $mdl_wj_user_temp_carts->update($new_item_price_data, $val['idd']);
                        }
                    } else {

                        // 如果在菜单中找到该产品，检查价格是否和菜单中描述一致，不一致，则更新
                        if ($menu_rec['price'] != $val['single_amount']) {


                            $new_item_price_data = [
                                'single_amount' => $menu_rec['price'],
                            ];
                            $mdl_wj_user_temp_carts->update($new_item_price_data, $val['idd']);
                        }
                    }

                    // 如果在菜单中找到该产品，检查库存是否溢出，如果溢出，则把库存调整到当前最大值

                    if ($menu_rec['qty'] < $val['quantity']) {
                        $new_item_quantity_data = [
                            'quantity' => $menu_rec['qty'],
                        ];
                        $mdl_wj_user_temp_carts->update($new_item_quantity_data, $val['idd']);
                    }

                    $show_origin_price = $this->loadModel('user_factory')->getByWhere([
                        'user_id' => $userId,
                        'factory_id' => $id
                    ])['show_origin_price'];

                    if(!$show_origin_price) {
                        $mdl_wj_user_temp_carts->update([
                            'single_amount' => 0,
                        ], $val['idd']);
                    }

                    if (array_key_exists($val['idd'], $userFactoryMenuPrices)) {
                        $mdl_wj_user_temp_carts->update([
                            'single_amount' => $userFactoryMenuPrices[$val['idd']]['price'],
                        ], $val['idd']);
                    }
                }
            }

             $this->setData($cartItems, 'items');

            echo json_encode($cartItems);



        }


    }
//获得某个客户所有商家的订货，这个用在单独显示购物车页面
    public function get_cart_details_action(){

        $business_userid = (int)get2('business_userid');
        $business_userid =319188;
        $userid = $this->loginUser['id'];
        $mdl_wj_user_temp_carts = $this->loadModel( 'wj_user_temp_carts' );
        $cartItems=$mdl_wj_user_temp_carts->getDetailedItem( $userid , $business_userid,$this->getLangStr());
        $data=array();
        $goodlist=array();
        $subdata=array();

        foreach ($cartItems as $key=>$value){
            $data[$key]['business_userId'] =$value['businessUserId'];
            $data[$key]['goodsTitle'] =$value['businessUserName'];
            $data[$key]['status'] =false;
            $items =$value['items'];
            foreach ($items as $key1=>$value1){
                $subdata ['id'] =$value1['id'];
                $subdata ['title'] =$value1['coupon_name'];
                $subdata ['price'] =$value1['single_amount'];
                $subdata ['num'] =$value1['quantity'];
                $subdata ['isTouch'] =false;
                $subdata ['status'] =0;
               $goodlist[$key1] =$subdata;
            }
           $data[$key]['goodsList'] = $goodlist;

        }
       // var_dump($cartItems);exit;
        echo json_encode($data);

    }

   public function get_good_list_action(){

       $id = (int) get2('id');
       $id =319188;
       $userId = $this->loginUser['id'];
       $cart = (int) get2('cart');
       $default_menu_page_items = 10;

       if (! $id) {
           $this->sheader(null, 'Please choose the supplier');
       }
/*
       if(!$this->loginUser){

           $this->sheader(null, 'Please login in and place the order ');
       }
*/

       $mdl_restaurant_edit =$this->loadModel('restaurant_menu');
       $list = $mdl_restaurant_edit-> getGoodList($id,$userId,UPLOAD_PATH);
       //var_dump($list);exit;

       echo json_encode($list);


   }


    public function  clear_cart_of_business_action(){

        $businessId =post('businessId');


         $mdl_wj_user_temp_carts =$this->loadModel( 'wj_user_temp_carts' );
         $mdl_wj_user_temp_carts->deleteAllItemOfThisBusinessId($this->loginUser['id'],$businessId);

        echo json_encode($businessId);


    }

    public function update_cart_item_quantity_action() {

      //  return json($_post());//判断当前用户是该数据拥有者


        $id =post('id');
        $quantity =post('number');
        $type =post('type');


       $mdl_wj_user_temp_carts =$this->loadModel( 'wj_user_temp_carts' );
       $isOwnerOfItem = $mdl_wj_user_temp_carts->isOwnerOfItem($this->loginUser['id'],$id);

       if($isOwnerOfItem){

         if($type=='update') {
             $data=array(
                 'quantity' =>$quantity
             );
             $mdl_wj_user_temp_carts->update($data,$id);
         }  elseif($type=='delete'){
             $mdl_wj_user_temp_carts->delete($id);
         }
    }

        echo json_encode( $id);

   }

    function delivery_address_edit1_action(){
        $id=(int)get2('id');
        $mdl_delivery_addres =$this->loadModel("wj_user_delivery_info");


        if ( is_post() ) {
            $first_name =trim(post('first_name'));
            $last_name =trim(post('last_name'));
            $address = trim(post('address'));
            $phone =trim(post('phone'));
            $email =trim(post('email'));
            $id_number =trim(post('id_number'));

            $country = trim(post('country'));
            $isDefaultAddress = trim(post('isDefaultAddress'));
            //echo json_encode($title);exit;
            $data = array(
                'userId'=>$this->loginUser['id'],
                'createTime'=>time(),
                'first_name'=>$first_name,
                'last_name'=>$last_name,
                'address'=>$address,
                'phone'=>$phone,
                'isDefaultAddress'=>$isDefaultAddress,
                'email'=>$email,
                'id_number'=>$id_number,
                'country'=>$country
            );

            if($isDefaultAddress){
                //reset all other to none default
                $d['isDefaultAddress']=0;
                $w['userId']=$this->loginUser['id'];
                $mdl_delivery_addres->updateByWhere( $d,$w );
            }

            if($mdl_delivery_addres->get($id)){
                $mdl_delivery_addres->update( $data,$id );
            }else{
                $mdl_delivery_addres->insert( $data );
            }

            $this->sheader( HTTP_ROOT_WWW.'member/delivery_address' );
        }
        else {
            $data_delivery_address =$mdl_delivery_addres->get($id);

            $this->setData( $data_delivery_address, 'data' );

            $this->setData( $lang->delivery.$this->lang->info, 'pagename' );
            $this->setData( 'myorder', 'menu' );
            $this->setData( 'delivery_address', 'submenu' );
            $this->setData( $this->lang->delivery.$this->lang->info .'-个人中心 '.$this->site['pageTitle'], 'pageTitle' );
            $this->display_pc_mobile('member/delivery_address_edit','member1/addAddress');
        }


    }


    function delivery_address1_action(){


        $id = (int)get2('id');
        $mdl_wj_user_delivery_info = $this->loadModel( 'wj_user_delivery_info' );

        if ( $id > 0 ) {
            $wj_user_delivery_info = $mdl_wj_user_delivery_info->get( $id );
            // 此处要加入判断一下该Id 所对应的用户是不是当前登陆的用户
            if ( ! $wj_user_delivery_info || $wj_user_delivery_info['userId']!=$this->loginUser['id'] ) $this->sheader( null, '记录不存在' );
            $this->sheader( $this->parseUrl()->set( 'id' ) );
        }

        $pageSql	= $mdl_wj_user_delivery_info->getListSql( null, array( 'userId' => $this->loginUser['id'] ), 'id desc' );
        //echo $pageSql; exit;
        $pageUrl	= $this->parseUrl()->set('page');
        $pageSize	= 10;
        $maxPage	= 10;
        $page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
        $data		= $mdl_wj_user_delivery_info->getListBySql($page['outSql']);

        $this->setData( $data, 'data' );
        $this->setData( $page['pageStr'], 'pager' );
        $this->setData( $this->parseUrl()->setPath( 'memeber/delivery_address_edit' ), 'editUrl' );
        $this->setData( $this->parseUrl()->setPath( 'memeber/delivery_address_edit' ), 'showUrl' );

        $this->setData( '配送地址', 'pagename' );
        $this->setData( 'myorder', 'menu' );
        $this->setData( 'delivery_address', 'submenu' );
        $this->setData( $menu_item,'menu_item' );
        $this->setData( '配送地址 - 个人中心 - '.$this->site['pageTitle'], 'pageTitle' );
        $this->display_pc_mobile('member/delivery_address','member1/addressList');
    }



}