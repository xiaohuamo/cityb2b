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
                        where f.factory_id =$business_id   limit $offset ,$lengthOflists  ";
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
            if ($row['email']) {

            $new_data[$key]['ContactPersons'] = [[
                'FirstName' => $row['person_first_name'],
                'LastName' => $row['person_last_name'],
                'EmailAddress' => $row['email'],
                'IncludeInEmails' => false
            ]];
        }

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

    //var_dump($new_data);exit;
       return json_encode($new_data);

    }


   /*
     * 获取产品列表并在xero创建产品。
     *  isedit表示这个产品被编辑过，也就是需要更新， 如果为1，则过滤所有修改过的产品，如果为0表示不需要
     * */

     public function  getItemListForCreateItemOnXero($business_id,$isEdit,$offset,$lengthOflists)
     {
         $sql = "SELECT
                m.menu_option ,if (length(m.menu_option)>0,spec.xero_itemcode,m.xero_itemcode) as xero_itemcode,
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
            LEFT JOIN(
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
                ) and (spec.xero_itemcode is null and length(m.xero_itemcode)=0)  limit $offset ,$lengthOflists  ";

         $rows = $this->getlistbysql($sql);
      //  var_dump($sql);exit;
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
                'UnitPrice'=>$value['price']

            );
            $new_data[$key]['SalesDetails'] =array(
                'UnitPrice'=>'1.00',
                'AccountCode'=>'',
                'COGSAccountCode'=>'',
                'TaxType'=>''
             );
            $new_data[$key]['IsTrackedAsInventory'] ='false';
            $new_data[$key]['InventoryAssetAccountCode'] ='';


        }



         return json_encode($new_data);

     }


//$custom_response= $mdl_xero->updateXeroItemCode($response_arr);
     public function  updateXeroItemCode($response){
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
      //   var_dump($guigeId.' '.$itemid);exit;
         return $str;
     }

    public function  updateXeroContactId($response,$factoryId){
        if(is_array($response) && count($response) > 0)
        {
            foreach($response as $v)
            {

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
                                $str.=' <br> Contact  update  Failed : '.$v['AccountNumber'];
                            }


                        } else {

                             $str.=' <br> Error happen when create contact: '.$v['AccountNumber'].' '. $v['ValidationErrors'];
                            // log somehere else
                        }
                     }
                 }







            }
        }
        //   var_dump($guigeId.' '.$itemid);exit;
        return $str;
    }

    }

?>