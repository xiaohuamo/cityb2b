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


    public function get_category_list_json_action() {

        $id = (int) get2('id');
     

        $categoryList =$this->loadModel('restaurant_category')->getCategoryList($id);
        echo json_encode($categoryList);

    }

//获得某个客户所有商家的订货，这个用在单独显示购物车页面
    public function get_cart_details_action(){

        $business_userid = (int)get2('business_userid');
       
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
                $subdata['menu_pic'] =$value1['menu_pic'];
               $goodlist[$key1] =$subdata;
            }
           $data[$key]['goodsList'] = $goodlist;

        }
       // var_dump($cartItems);exit;
        echo json_encode($data);

    }

   public function get_good_list_action(){

       $id = (int) get2('id');
     
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

    public function  add_item_temp_cart_table_action(){

       // 接收购物车中的数据

        $user_id = $this->loginUser['id'];
        $businessId =post('businessId');

        if(!$user_id || !$businessId) {
            echo json_encode('Could not find user ID or Business Id ,please Logon in and try again!');
            exit;
        }

        // receive cart items
        $itemData1=stripslashes(post('itemlist')); // remove spec letter of data
        $itemData =json_decode($itemData1,true);



        $mdl_wj_user_temp_carts =$this->loadModel( 'wj_user_temp_carts' );
        //remove original items of current user of this business
        $mdl_wj_user_temp_carts->deleteAllItemOfThisBusinessId($user_id,$businessId);

        // get the main coupon ID of this business which include the shop card information .
        $mdl_coupons =$this->loadModel('coupons');
        $currentCoupon = $mdl_coupons->getByWhere(array('createUserId'=>$businessId,'EvoucherOrrealproduct'=>restaurant_menu));


        //insert new record .





      foreach ($itemData as $key=>$value){

          if(!$mdl_wj_user_temp_carts->addItemsToCart($value,$user_id,$currentCoupon,$businessId)){

              echo json_encode('0');
              exit;
          }

       }



          echo json_encode('1');


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

    public function update_language_type_action() {

        //  return json($_post());//判断当前用户是该数据拥有者


        $languageValue =post('languageValue');

        $mdl_user_setting =$this->loadModel('user_setting');

        $data =array(
            'isLanguageEng'=>$languageValue
         );

    // 如果发现则修改，如果未发现则新增
        if($mdl_user_setting->get($this->loginUser['id'])) {
            $mdl_user_setting->update($data,$this->loginUser['id']);
        }else{
            $data['id']=$this->loginUser['id'];
            $mdl_user_setting->insert($data,$this->loginUser['id']);

        }

        echo json_encode( $data);

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