<?php

class mdl_xero extends mdl_base
{

	protected $tableName = '#@_xero';



    public function getContactListForCreateContactOnXero($business_id,$isEdit,$offset,$lengthOflists) {

        $sql = "

                   select  /*u.id ,   */
                        f.xero_account_number as AccountNumber,
                        f.xero_contact_id as ContactID , 
                          f.user_id as ContactNumber,
                         if(length(f.nickname)>0,f.nickname,u.displayName) as Name,
                        abn.untity_name, 
                        f.nickname as customer_code, 
                        u.person_first_name,
                        u.person_last_name, 
                        u.address ,
                        u.addrNumber,
                        u.addrStreet,
                        u.addrSuburb,
                        u.addrState,
                        u.addrPost,
                        u.email,
                        abn.ABNorACN,
                        concat (u.tel,' ',u.phone) as phone,
                        if(f.account_type='COD','COD',concat(convert(CAST(f.account_type AS SIGNED)*7 ,CHAR),'D')) as disp_accountType , 
                        if(f.account_type='COD',0,CAST(f.account_type AS SIGNED)*7 ) as payment_period  
                        from cc_user u 
                        left join cc_user_factory f on u.id =f.user_id
                        left join cc_wj_abn_application abn on u.id =abn.userId
                        where f.factory_id =$business_id  and (length(f.xero_contact_id)=0 or (f.xero_contact_id is null))  limit $offset ,$lengthOflists  ";

      // var_dump($sql);exit;
        $rows = $this->getListBySql($sql);
        $new_data = [];
        foreach ($rows as $key=> $row) {




            $new_data[$key]['ContactNumber']=$row['ContactNumber'];
            $new_data[$key]['AccountNumber'] = $row['AccountNumber'];
            $new_data[$key]['Name'] = str_replace('&', ' ', $row['Name']);
            if(! $new_data[$key]['Name'] ) {
                $new_data[$key]['Name']='no name';
            }
            $new_data[$key]['FirstName'] = $row['person_first_name'];
            $new_data[$key]['LastName'] = $row['person_last_name'];
            $new_data[$key]['EmailAddress'] = $row['email'];
            $new_data[$key]['BankAccountDetails'] = '';
            $new_data[$key]['CompanyNumber'] = '';
            $new_data[$key]['TaxNumber'] = $row['ABNorACN'];
            $new_data[$key]['AccountsReceivableTaxType'] = 'EXEMPTOUTPUT';
            $new_data[$key]['AccountsPayableTaxType'] = 'EXEMPTOUTPUT';
            $new_data[$key]['IsSupplier'] = 'false';
            $new_data[$key]['IsCustomer'] = 'true';
            $new_data[$key]['DefaultCurrency'] = "AUD";
            if (strlen(trim($row['email']))==0) {
                $new_data[$key]['EmailAddress'] ='no-email@hotmail.com';
                }
            $new_data[$key]['ContactPersons'] = [[
                'FirstName' => $row['person_first_name'],
                'LastName' => $row['person_last_name'],
                'EmailAddress' => $row['email'],
                'IncludeInEmails' => false
            ]];


            $new_data[$key]['Addresses']=[
                [


                    "AddressType" => "STREET",
                    "AddressLine1" => $row['addrNumber'].' '.$row['addrStreet'],
                    "AddressLine2" => "",
                    "AddressLine3" => "",
                    "AddressLine4" => "",
                    "City" => $row['addrSuburb'],
                    "Region" => $row['addrState'],
                    "PostalCode" => $row['addrPost'],
                    "Country" => "AU",
                    "AttentionTo" => ""
                ]
            ];




            $new_data[$key]['Phones']=[[
                "PhoneType" =>'DEFAULT',
                "PhoneNumber"=>$row['phone'],
                "PhoneAreaCode"=>'',
                "PhoneCountryCode"=>'+61'
            ]];




        }


       return json_encode($new_data);

    }

    function  getorderdata($orderId) {

        $sql ="select o.id,
            o.xero_id,
            o.xero_invoice_id as invoice_id ,  
            o.userId as reference_user_id , 
            f.xero_account_number  as account_number  , 
            f.xero_contact_id  , 
            o.first_name as contact_first_name,
            o.last_name as contact_last_name,
            o.house_number ,
            o.street,
            f.nickname as customer_code ,
            o.city,
            o.state,
            o.postalcode as postcode,
            o.country,
            o.address,
            o.displayName as trading_name,
            o.id as reference_id, 
            o.email,
            abn.untity_name, 
            f.nickname as customer_code, 
            if(f.account_type='COD',0,CAST(f.account_type AS SIGNED)*7 ) as payment_period,
            if(f.account_type='COD','COD',concat(convert(CAST(f.account_type AS SIGNED)*7 ,CHAR),'D')) as disp_accountType ,
            o.message_to_business as message ,
            FROM_UNIXTIME(o.logistic_delivery_date,'%m/%d/%Y') as delivery_date ,
            abn.ABNorACN,
            concat (u.tel,' ',u.phone) as phone,
            o.delivery_fees 
            from cc_order o
            left join cc_user_factory f on o.userId =f.user_id and o.business_userId =f.factory_id 
            left join cc_user u on o.userId =u.id 
            left join cc_wj_abn_application abn on u.id =abn.userId
            where o.orderId =$orderId";

            $order_data =loadModel('order')->getListBySql($sql);
           // var_dump($order_data);exit;
            return $order_data[0];

    }

  function getDetailsData ($orderId) {

      $sqlDetails ="select c.id ,
            m.id as product_id,
            ifnull(spec.id ,'') as spec_id,
            if(length(spec.id)>0 or (spec.id is not null),concat(m.id,'-',spec.id),m.id) as item_id,
            if(length(spec.id)>0 or (spec.id is not null),spec.xero_itemcode,m.xero_itemcode) as xero_item_id,
            concat(upper(c.menu_id) ,' ',upper(concat(
                if(length(m.menu_en_name)>0 ,m.menu_en_name,m.menu_cn_name), if(length(spec.menu_en_name)>0,spec.menu_en_name,''))),' ',upper(if(length( m.unit_en)>0,   m.unit_en,m.unit)))as xero_item_name,
            upper(c.menu_id) as item_code,
            upper(concat(
                if(length(m.menu_en_name)>0 ,m.menu_en_name,m.menu_cn_name), if(length(spec.menu_en_name)>0,spec.menu_en_name,''))) as item_name ,
            sum(c.new_customer_buying_quantity) as quantity ,
            upper(if(length( m.unit_en)>0,   m.unit_en,m.unit)) as unit ,
            c.voucher_deal_amount as price,
            if(m.include_gst,10,0) as gst,
            sum(round((c.voucher_deal_amount * c.new_customer_buying_quantity),2))  as amount 
               
            from cc_wj_customer_coupon c 
            left join cc_restaurant_menu m on c.restaurant_menu_id =m.id 
            left join cc_restaurant_menu_option spec on c.guige1_id =spec.id
            
            where order_id = $orderId group by item_id";

          $order_item_details =loadModel('wj_customer_coupon')->getListBySql($sqlDetails);
         // var_dump($sqlDetails);exit;
          return $order_item_details;


  }

    public function getOrderInvoiceData($orderId){

          //get order data
          $order_data = $this->getorderdata($orderId);
         // get order details data
          $details_data =$this->getDetailsData($orderId);

    // var_dump($order_data);exit;
         $new_data=[];


            $new_data['Type'] ="ACCREC";

            $new_data['Contact'] =array(
                'ContactID'=>$order_data['xero_contact_id']
            );


            $detail=[];

            foreach($details_data as $key=>$value) {
                $detail[$key]['Description']=$value['xero_item_name'];
                $detail[$key]['Quantity']=$value['quantity'];
                $detail[$key]['UnitAmount']=$value['price'];
                $detail[$key]['ItemCode']=$value['item_id'];
                $detail[$key]['AccountCode']="200";
                $detail[$key]['LineItemID']=$value['xero_item_id'];
                $detail[$key]['TaxType']="BASEXCLUDED";
                $detail[$key]['TaxAmount']=0;
                $detail[$key]['LineAmount']=$value['amount'];
                $detail[$key]['DiscountRate']="";
                $detail[$key]['DiscountAmount']="";
              }


            $new_data['LineItems'] =$detail;

            $new_data['InvoiceID'] =$order_data['xero_id'];
            $new_data['InvoiceNumber'] =$order_data['invoice_id'];

            $new_data['Date'] =date('m/d/Y',time());
            $dueDays =$order_data['payment_period']+1;

            $new_data['DueDate'] =date('m/d/Y',strtotime("+$dueDays day"));
            $new_data['LineAmountTypes'] ="Exclusive";

            $new_data['Reference'] ='';
            $new_data['BrandingThemeID'] ='';
            $new_data['CurrencyCode'] ='AUD';
            $new_data['Status'] ='DRAFT';
            $new_data['SentToContact'] ='';
            $new_data['ExpectedPaymentDate'] ='';
            $new_data['PlannedPaymentDate'] ='';

            /***
             *
             * 这个比较重要， 就是如果返回的时候需要天聪 hashcode ,invoicenumber , 再次创建的时候就不行了。 测试后要把那个是 hash 那个是invocie搞清除填进去
             */


            /*
              [
            {
                "Type": "ACCREC",
                "Contact": {
                    "ContactID":"1a288e15-a08e-45e8-ac9d-4f4e81be97fe"
                },
                "LineItems": [
                    {
                        "Description": "CK MARYLAND FILLET STRIPS MEAT STRIPS SHREDDE D MEAT 5MM",
                        "Quantity": "8.000",
                        "UnitAmount": "16.80",
                        "ItemCode": "385484-1801",
                        "AccountCode":"200",
                        "LineItemID":"",
                        "TaxType": "OUTPUT",
                        "TaxAmount": "13.44",
                        "LineAmount": "134.40",
                        "DiscountRate":"",
                        "DiscountAmount":"",
                        "Tracking":[
                            {
                                "Name":"",
                                "Option":""
                            }
                        ]
                    }
                ],
                "Date": "2022-03-15",
                "DueDate": "2022-03-22",
                "LineAmountTypes": "Exclusive",
                "InvoiceNumber":"",
                "Reference":"",
                "BrandingThemeID":"",
                "CurrencyCode":"AUD",
                "CurrencyRate":"",
                "Status":"",
                "SentToContact":"",
                "ExpectedPaymentDate":"",
                "PlannedPaymentDate":""
            }
        ]
             *
             * */


      return ('['.json_encode($new_data).']');





    }
   /*
     * 获取产品列表并在xero创建产品。
     *  isedit表示这个产品被编辑过，也就是需要更新， 如果为1，则过滤所有修改过的产品，如果为0表示不需要
     * */

     public function  getItemListForCreateItemOnXero($business_id,$isEdit,$offset,$lengthOflists)
     {
         $sql = "SELECT
                m.menu_option ,if (length(m.menu_option)>1,spec.xero_itemcode,m.xero_itemcode) as xero_itemcode,
                m.xero_itemcode as product_xerocode,
                spec.xero_itemcode as guige_xerocode,
                       CONCAT(
                    m.id,
                    IFNULL(CONCAT('-', spec.spec_id),
                    '')
                ) AS Code,
                /* xero code unique */
                CONCAT(
                    UPPER(m.menu_id),
                    ' ',
                    UPPER(
                        CONCAT(
                            IF(
                                LENGTH(m.menu_en_name) > 0,
                                m.menu_en_name,
                                m.menu_cn_name
                            ),
                            ' ',
                            IF(
                                LENGTH(spec.spec_name) > 0,
                                spec.spec_name,
                                ''
                            )
                        )
                    ),
                    UPPER(
                        IF(
                            LENGTH(m.unit_en) > 0,
                            m.unit_en,
                            m.unit
                        )
                    )
                ) AS Name,
                UPPER(m.menu_id) AS item_code,
                m.id AS product_id,
                UPPER(
                    CONCAT(
                        IF(
                            LENGTH(m.menu_en_name) > 0,
                            m.menu_en_name,
                            m.menu_cn_name
                        ),
                        IF(
                            LENGTH(spec.spec_name) > 0,
                            spec.spec_name,
                            ''
                        )
                    )
                ) AS item_name,
                UPPER(m.menu_desc) AS Description,
                m.price,
                IF(m.include_gst, 10, 0) AS gst
            FROM
                cc_restaurant_menu m
            LEFT JOIN (
                SELECT m.id,
                    g.id AS spec_id,
                    g.menu_en_name AS spec_name,
                    g.menu_cn_name AS spec_name_cn,
                    g.xero_itemcode ,
                       
                    c.category_en_name AS spec_desc
                FROM
                    `cc_restaurant_menu` m
                LEFT JOIN cc_restaurant_menu_option g ON
                    m.menu_option = g.restaurant_category_id
                LEFT JOIN cc_restaurant_menu_option_category c ON
                    m.menu_option = c.id
            ) AS spec
            ON
                m.id = spec.id AND(
                    LENGTH(spec.spec_name_cn) > 0 OR LENGTH(spec.spec_name) > 0
                )
            WHERE   
                m.restaurant_id = $business_id AND(
                    LENGTH(m.menu_cn_name) > 0 OR LENGTH(m.menu_en_name) > 0
                ) and m.isDeleted =0 and visible =1 and (((spec.xero_itemcode is null) or length(spec.xero_itemcode)<=2) and (length(m.xero_itemcode)<=2 or (m.xero_itemcode is null)))  limit $offset,$lengthOflists  ";

         $rows = $this->getlistbysql($sql);
       // var_dump($sql);exit;
        $new_data =[];
        foreach ($rows as $key =>$value) {
            $new_data[$key]['Code'] =$value['Code'];

          //  $name= str_replace ('&','\&', $value['Name']);
        //   $name= htmlspecialchars_decode($value['Name'], ENT_NOQUOTES);
         //   $new_data[$key]['Name'] =$name;
            $new_data[$key]['Name'] =str_replace('&',' ',substr($value['Name'],0,45));
            $new_data[$key]['IsSold'] ='true';
            $new_data[$key]['IsPurchase'] ='true';
            $new_data[$key]['PurchaseDescription'] ='';
            $new_data[$key]['PurchaseDetails'] =array(
                'UnitPrice'=>1.00

            );
            $new_data[$key]['SalesDetails'] =array(
                'UnitPrice'=>$value['price'],
                'AccountCode'=>'',
                'COGSAccountCode'=>'',
                'TaxType'=>''
             );
            $new_data[$key]['IsTrackedAsInventory'] ='false';
            $new_data[$key]['InventoryAssetAccountCode'] ='';


        }


//var_dump(json_encode($new_data));exit;
         return json_encode($new_data);

     }
    //更新发票信息
    public function createXeroInvoiceInfo($response,$orderId){

        if(is_array($response) && count($response) > 0)
        {
            foreach($response as $v)
            {
                if( !empty($v['InvoiceID']) && !empty($v['InvoiceNumber']) && empty($v['ValidationErrors']) )
                {
                    // update the ItemID to your DB
                    $updateArr =array(
                        'xero_invoice_id'=>$v['InvoiceNumber'],
                        'xero_id'=>$v['InvoiceID']
                    );

                    $where =array(
                        'orderId'=>$orderId
                    );

                        if(loadModel('order')->updateByWhere($updateArr,$where)){
                            $str='';
                        }else{
                            $str.=' <br> order update  Failed : '.$v['AccountNumber'].' '. 'order Id '.$orderId.' '.$v['ValidationErrors'];
                        }
                        // loadModel('restaurant_menu')->update($updateArr1,$itemid);




                } else {
                    // log somehere else

                    $str=' <br> Error happen: '.$v['AccountNumber'].' '. 'order Id '.$orderId.' '.json_encode($v['ValidationErrors']);
                }
            }
        }
        //   var_dump($guigeId.' '.$itemid);exit;
        return $str;




    }
//$custom_response= $mdl_xero->updateXeroItemCode($response_arr);
     public function  updateXeroItemCode($response){
        //var_dump($response);
         //var_dump('is array '. is_array($response));
        // var_dump('count > 0  '.  count($response));exit;
         if(is_array($response) && count($response) > 0)
         {

             foreach($response as $v)
             {
                 if( !empty($v['ItemID']) && !empty($v['Code']) && empty($v['ValidationErrors']) )
                 {
                     // update the ItemID to your DB
                     $updateArr =array(
                         'xero_itemcode'=>$v['ItemID']
                     );
                     $updateArr1 =array(
                         'xero_itemcode'=>'SPEC'
                     );

                    $code =$v['Code'];
                    $guigepos =strpos($code,'-');

                    // 如果有分割符号，表明为规格
                     if($guigepos) {
                         $itemid=  substr($code,0,$guigepos-1);
                         $guigeId =substr($code,$guigepos+1);

                         if(loadModel('restaurant_menu_option')->update($updateArr,$guigeId)){
                             $str.=' <br> Specification xero_id update success : '.$v['Code'];
                         }else{
                             $str.=' <br> Specification xero_id update  Failed : '.$v['Code'];
                         }
                        // loadModel('restaurant_menu')->update($updateArr1,$itemid);

                     }else{
                         if( loadModel('restaurant_menu')->update($updateArr,$code)){
                             $str.=' <br> Product  xero_id update  success : '.$v['Code'];
                         }else{
                             $str.=' <br> Product  xero_id update  Failed : '.$v['Code'];
                         }


                     }


                 } else {
                     // log somehere else
                     $str.=' <br> Error happen: '.$v['Code'].' '. $v['ValidationErrors'];
                 }
             }
         }
        // var_dump($str);exit;
         return $str;
     }

//yongzai factory tongbu xero match record
    public function  updateXeroItemCode1($response){
        //var_dump($response);
        //var_dump('is array '. is_array($response));
        // var_dump('count > 0  '.  count($response));exit;
        if(is_array($response) && count($response) > 0)
        {

            foreach($response as $v)
            {
                if( !empty($v['ItemID']) && !empty($v['Code']) && empty($v['ValidationErrors']) )
                {
                    // update the ItemID to your DB
                    $updateArr =array(
                        'xero_itemcode'=>$v['ItemID']
                    );
                    $updateArr1 =array(
                        'xero_itemcode'=>'SPEC'
                    );

                    $code =$v['Code'];
                    $guigepos =strpos($code,'-');

                    // 如果有分割符号，表明为规格
                    if($guigepos) {
                        $itemid=  substr($code,0,$guigepos-1);
                        $guigeId =substr($code,$guigepos+1);

                        if(loadModel('restaurant_menu_option')->update($updateArr,$guigeId)){
                           return 1;
                        }else{
                            return 0;
                        }
                        // loadModel('restaurant_menu')->update($updateArr1,$itemid);

                    }else{
                        if( loadModel('restaurant_menu')->update($updateArr,$code)){
                          return 1;
                        }else{
                           return 0 ;
                        }


                    }


                } else {
                    // log somehere else
                   return 0;
                }
            }
        }
        // var_dump($str);exit;
        return 0;
    }


    public function  createXeroContactId($response,$factoryId){


                 if(is_array($response) && count($response) > 0)
                 {
                     foreach($response as $v)
                    {
                         if( !empty($v['ContactID']) && !empty($v['AccountNumber']) && !empty($v['ContactNumber']) && empty($v['HasValidationErrors']) )
                        {
                            // update the ContactID to your DB
                            // update the ItemID to your DB
                            $updateArr =array(
                                'xero_contact_id'=>$v['ContactID']

                            );
                            $where =array(
                                'user_id'=>$v['ContactNumber'],
                                'xero_account_number'=>$v['AccountNumber'],
                                'factory_id'=>$factoryId
                            );
                            if(loadModel('user_factory')->updateByWhere($updateArr,$where)){

                                $str.=' <br> Contact  update success : '.$v['AccountNumber'];
                            }else{
                                $str.=' <br> Contact  update  Failed-- : '.$v['AccountNumber'];
                                $str.=  'xero_contact_id'.' '.$v['ContactID'].' '.'user_id'.' '. $v['ContactNumber'].' '.$v['AccountNumber'].' '.$factoryId ;
                            }


                        } else {

                             $str.=' <br> Error happen when create contact: '.$v['AccountNumber'].' '. json_encode($v['ValidationErrors']);
                            // log somehere else
                        }
                     }
                 }








        //   var_dump($guigeId.' '.$itemid);exit;
        return $str;
    }

    public function updateXeroContactId($response,$factoryId){

       //  var_dump($response_arr);exit;
   // return 1;
        if(is_array($response) && count($response) > 0)
        {
            foreach($response as $v)
            {
                if( !empty($v['ContactID']) && !empty($v['AccountNumber'])  && empty($v['HasValidationErrors']) )
                {
                    // update the ContactID to your DB
                    // update the ItemID to your DB
                    $Name =str_replace("'"," ",$v['Name']);
                    $updateArr =array(
                        'xero_contact_id'=>$v['ContactID'],
                        'xero_name'=>$Name
                    );
                    $where =array(
                        'xero_account_number'=>$v['AccountNumber'],
                        'factory_id'=>$factoryId
                    );
                    if(loadModel('user_factory')->updateByWhere($updateArr,$where)){

                        $str.=' <br> Contact  update success : '.$v['AccountNumber'];
                    }else{
                        $str.=' <br> Contact  update  Failed-- : '.$v['AccountNumber'];
                        $str.=  'xero_contact_id'.' '.$v['ContactID'].' '.'user_id'.' '. $v['ContactNumber'].' '.$v['AccountNumber'].' '.$factoryId ;

                    }


                } else {

                    $str.=' <br> Error happen when create contact: '.$v['AccountNumber'].' '. json_encode($v['ValidationErrors']);
                    // log somehere else
                }
            }
        }



        //   var_dump($guigeId.' '.$itemid);exit;
        return $str;

    }


//download xero items and save to sync match table

    public function  createXeroSyncItems($response,$business_id){
        if(is_array($response) && count($response) > 0)
        {
            foreach($response as $v)
            {
                if( !empty($v['ItemID']) && !empty($v['Code']) && empty($v['ValidationErrors']) )
                {
                    // update the ItemID to your DB
                    $addData =array(
                        'xero_ItemID'=>$v['ItemID'],
                        'xero_code'=>$v['Code'],
                        'xero_name'=>$v['Name'],
                        'business_id'=>$business_id
                );


                    // 如果有分割符号，表明为规格

                        if( loadModel('xero_items_match')->insert($addData)){
                            $str.=' <br> Product  xero_id add  success : '.$v['Code'];
                        }else{
                            $str.=' <br> Product  xero_id add  Failed : '.$v['Code'];
                        }





                } else {
                    // log somehere else
                    $str.=' <br> Error happen: '.$v['Code'].' '. json_encode($v['ValidationErrors']);
                }
            }
        }
        //   var_dump($guigeId.' '.$itemid);exit;
        return $str;
    }


    }

?>