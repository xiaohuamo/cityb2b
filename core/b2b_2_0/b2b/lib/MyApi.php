<?php
class MyApi
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    private function getTokens($credentials)
    {
        $tokens = [];

        if(empty($credentials['username'])) return [
            'error' => true,
            'origin' => 'local',
            'message' => 'Not a valid username'
        ];

        $stmt = $this->db->prepare("SELECT tokens FROM tokens WHERE username=? AND client_id=? AND client_secret=?");
        $stmt->bind_param('sss', $credentials['username'], $credentials['client_id'], $credentials['client_secret']);    
        $stmt->execute();   
        $stmt->bind_result($tokens_json);
        $stmt->fetch();        
        if(!empty($tokens_json)) {
            try {
                $tokens_arr = json_decode($tokens_json, true);
                if(is_array($tokens_arr) && count($tokens_arr) > 0) {
                    if(
                        !empty($tokens_arr['access_token']) && 
                        !empty($tokens_arr['expires_in']) && 
                        !empty($tokens_arr['token_type']) && 
                        !empty($tokens_arr['scope']) && 
                        !empty($tokens_arr['refresh_token']) &&
                        !empty($tokens_arr['expiration_time'])  
                    ) {
                        if( intval($tokens_arr['expiration_time']) <= (time() - 5) ) { // do refresh token
                            $tokens = $this->doRefreshToken($credentials, $tokens_arr['refresh_token']);
                        } else {
                            $tokens = $tokens_arr;
                        }
                    }
                }
            } catch (\Exception $e) {
                $tokens = [];
            }
        }
        
        if(empty($tokens)) {
            return $this->doAuth($credentials);
        } else {
            return $tokens;
        }
    }

    private function doSaveTokens($credentials, $new_tokens_json)
    {
        $stmt = $this->db->prepare("SELECT tokens FROM tokens WHERE username=? AND client_id=? AND client_secret=?");
        $stmt->bind_param('sss', $credentials['username'], $credentials['client_id'], $credentials['client_secret']);      
        $stmt->execute();   
        $stmt->bind_result($tokens_json);
        $stmt->fetch();        
        if(!empty($tokens_json)) { // update
            $stmt = $this->db->prepare("UPDATE tokens SET tokens=? WHERE username=? AND client_id=? AND client_secret=?");            $stmt->bind_param('ss', $new_tokens_json, $credentials['username']);    
            $save = $stmt->execute();   
        } else { // insert
            $stmt = $this->db->prepare("INSERT INTO tokens (username, client_id, client_secret, tokens) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssss', $credentials['username'], $credentials['client_id'], $credentials['client_secret'], $new_tokens_json);    
            $save = $stmt->execute();   
        }
    }

    private function doRefreshToken($credentials, $refresh_token)
    {
        $params=[
            'username' => $credentials['username'], 
            'password' => $credentials['password'], 
            'client_id'=> $credentials['client_id'],
            'client_secret' => $credentials['client_secret'],
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token
        ];
        $defaults = array(
            CURLOPT_URL => $credentials['endpoint_uri'] . '/oauth2/token',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_RETURNTRANSFER => true
        );        
        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        $response = curl_exec($ch);
        if($response === false) {
            curl_close($ch);
            return curl_error($ch);
        } else {
            curl_close($ch);
            try {
                // save or update table tokens
                $new_tokens_arr = json_decode($response, true);
                if(
                    !empty($new_tokens_arr['access_token']) && 
                    !empty($new_tokens_arr['expires_in']) && 
                    !empty($new_tokens_arr['token_type']) && 
                    !empty($new_tokens_arr['scope']) && 
                    !empty($new_tokens_arr['refresh_token'])  
                ) {
                    $new_tokens_arr['expiration_time'] = time() + intval($new_tokens_arr['expires_in']);
                    // save to table tokens
                    $stored = $this->doSaveTokens($credentials, json_encode($new_tokens_arr));
                    return $new_tokens_arr;
                } else {
                    return [
                        'error' => true,
                        'message' => 'Not a valid response'
                    ];
                }
            } catch(\Exception $e) {
                return $e->getMessage();
            }
        }
    }    

    private function doAuth($credentials)
    {
        $params=[
            'username' => $credentials['username'], 
            'password' => $credentials['password'], 
            'client_id'=> $credentials['client_id'],
            'client_secret' => $credentials['client_secret'],
            'grant_type' => 'password'
        ];
        $defaults = array(
            CURLOPT_URL => $credentials['endpoint_uri'] . '/oauth2/token',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_RETURNTRANSFER => true
        );        
        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        $response = curl_exec($ch);
        if($response === false) {
            curl_close($ch);
            return curl_error($ch);
        } else {
            curl_close($ch);
            try {
                // save or update table tokens
                $new_tokens_arr = json_decode($response, true);
                if(
                    !empty($new_tokens_arr['access_token']) && 
                    !empty($new_tokens_arr['expires_in']) && 
                    !empty($new_tokens_arr['token_type']) && 
                    !empty($new_tokens_arr['scope']) && 
                    !empty($new_tokens_arr['refresh_token'])  
                ) {
                    $new_tokens_arr['expiration_time'] = time() + intval($new_tokens_arr['expires_in']);
                    // save to table tokens
                    $stored = $this->doSaveTokens($credentials, json_encode($new_tokens_arr));
                    return $new_tokens_arr;
                } else {
                    return [
                        'error' => true,
                        'origin' => 'local',
                        'message' => 'Not a valid resposne'
                    ];
                }
            } catch(\Exception $e) {
                return $e->getMessage();
            }
        }
    }

    /********************************** CREATE CONTACTS *************************************************/
    public function createContacts($credentials, $json = "") // array of Contact in json
    {
        $api_response = false;

        // get data from db to generate json
        if(empty($json)) {
            $sql = "

                   select  /*u.id ,   */
                        f.xero_account_number as AccountNumber,
                        f.xero_contact_id as ContactID , 
                        f.nickname as Name ,
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
                        where f.factory_id =? limit 26,2 
                        ";

            $stmt = $this->db->prepare($sql);
            $factory_id = 319188;
            $stmt->bind_param('d', $factory_id);
            $stmt->execute();
            $result = $stmt->get_result();


            /************* QUERY RESULTS ******************/
            $rows =$result->fetch_all(MYSQLI_BOTH);

            foreach ($rows as $key=> $row) {
                //printf("%s (%s)\n", $row["Name"], $row["CountryCode"]);

                $ContactPersons =
                    [
                        [
                            'FirstName' => $row['person_first_name'],
                            'LastName' => $row['person_last_name'],
                            'EmailAddress' => $row['email'],
                            'IncludeInEmails' => false

                        ]];
            //    var_dump(json_encode($ContactPersons));exit;


                $rows[$key]['ContactPersons'] =$ContactPersons;

                $addrNumber =$row['addrNumber'];
                $addrStreet =$row['addrStreet'];
                $addrSuburb =$row['addrSuburb'];
                $addrState =$row['addrState'];
                $addrPost =$row['addrPost'];
                $Addresses = [ [
                    'AddressType' =>'',
                    'AddressLine1' => $addrNumber.' '. $addrStreet,
                    'AddressLine2' => '',
                    'AddressLine3' => '',
                    'AddressLine4' => '',
                    'City' => $addrSuburb,
                    'Region' => $addrState,
                    'PostalCode' => $addrPost,
                    'Country'=>'AU',
                    'AttentionTo'=> ''
                ]
               ];

                $rows[$key]['Addresses'] =$Addresses;
            }







            //while ($row = $result->fetch_array(MYSQLI_NUM)) {
                /**
                 * GEBERATE JSON BASED ON CONTACT STRUCTURE BELOW
                 * Example: 
                 * 
                 * $arr = [
                 *      'AccountName' => $row['account_number'],
                 *      'Name' => $row['company_name']
                 * ]
                 * 
                 * $json = json_encode($arr);
                 * 
                 * that it, below code will process $json
                 * the XERO Response will be process under function  processContacts()
                 * see more comments in the function
                 */
            //}
            /************* END OF QUERY RESULTS ******************/
        }

        /* CONTACT STRUCTURE
        $json = '[
            {
                "ContactNumber":"",
                "AccountNumber":"",
                "Name":"Chef Kim\'s1",
                "FirstName":"Chef",
                "LastName":"Kim\'s1",
                "EmailAddress":"example@example.com",
                "BankAccountDetails":"Commonwealth Bank ACC No. 319254",
                "CompanyNumber":"Conpany Reg. Number",
                "TaxNumber":"ABN Number",
                "AccountsReceivableTaxType":"EXEMPTOUTPUT",
                "AccountsPayableTaxType":"EXEMPTOUTPUT",
                "IsSupplier":"false",
                "IsCustomer":"true",
                "DefaultCurrency":"AUD",
                "ContactPersons":[{
                    "FirstName":"First",
                    "LastName":"Last",
                    "EmailAddress":"example@example.com",
                    "IncludeInEmails":"false"
                }],
                "Addresses":[{
                    "AddressType":"STREET",
                    "AddressLine1":"266-274 Derrimut Rd",
                    "AddressLine2":"Hoppers Crossing",
                    "AddressLine3":"",
                    "AddressLine4":"",
                    "City":"City of Wyndham",
                    "Region":"VIC",
                    "PostalCode":"3029",
                    "Country":"AU",
                    "AttentionTo": "Mr. Attention"
                }],
                "Phones":[{
                    "PhoneType":"DEFAULT",
                    "PhoneNumber":"123456789",
                    "PhoneAreaCode":"+3",
                    "PhoneCountryCode":"+61"
                }]
            }
        ]';
        */
        $json = json_encode($rows);
        var_dump($json);exit;
        // valid data ?
        try {
            $data_arr = json_decode($json, true);
            if(is_array($data_arr) && count($data_arr) > 0) {
                // proceed
            } else {
                $api_response = [
                    'error' => true,
                    'origin' => 'local',
                    'message' => 'Require at least 1 contact'
                ];
            }
        } catch (\Exception $e) {
            $api_response  = [
                'error' => true,
                'origin' => 'local',
                'message' => $e->getMessage()
            ];
        }

        // valid tokens ?
        $tokens = $this->getTokens($credentials);
        if(empty($tokens['access_token'])) 
        {
            $api_response = [
                'error' => true,
                'origin' => 'local',
                'message' => 'Not authorized'
            ];
        }

        // tokens & data validated
        $params = [
            'data' => $json, 
        ];
        $defaults = array(
            CURLOPT_URL => $credentials['endpoint_uri'] . '/v1/xero/create-contacts?accessToken=' . $tokens['access_token'],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_RETURNTRANSFER => true
        );        
        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        $response = curl_exec($ch);
        if($response === false) {
            curl_close($ch);
            $api_response = [
                'error' => true,
                'origin' => 'local',
                'message' => curl_error($ch)
            ];
        } else {
            curl_close($ch);
            $api_response = $response;
        }            

        return $this->processContacts($api_response);
    }

    private function processContacts($api_response)
    {
        $html = '';
        if($api_response) {
            /************* VALIDATION IN ARRAY, NOT SENT TO XERO YET AS IT DOESN'T VALIDATED  ******************/
            if(is_array($api_response) && count($api_response) > 0) {
                foreach($api_response as $k=>$v) {
                    $html.= $k . ': ' . $v . '<br/>';
                }
            } else {
                try {
                    /************* SUCCESFUL XERO API RESPONSE IN JSON  ******************/
                    $api_response_parsed = json_decode($api_response, true);

                    // YOU CAN CHECK THE RESPONSE, UPDATE DN IF NEEDED, SUCH AS UPDATE ContactID 

                    /************* DISPLAYING XERO API CALL RESPONSE, YOU CAN REMOVE THESE *********************/
                    if(is_array($api_response_parsed) && count($api_response_parsed) > 0) {
                        foreach($api_response_parsed as $k => $v) {
                            if($k == 'response') {
                                if(is_array($v) && count($v) > 0) {
                                    foreach($v as $kv => $vv) {
                                        if(is_array($vv) && count($vv) > 0) {
                                            $html.= $kv . ': ' . print_r($vv, true) . '<br/>';
                                        } else {
                                            $html.= $kv . ': ' . $vv . '<br/>';
                                        }

                                        if($vv['ContactID'] !='00000000-0000-0000-0000-000000000000') {
                                         $html .='contactid is:'.print_r($vv['ContactID'], true) . '<br/>';
                                         $html .='accountNumber is:'.print_r($vv['AccountNumber'], true) . '<br/>';
                                         $html .=" UPDATE cc_user_factory SET xero_contact_id='".$vv['ContactID']."' WHERE factory_id=319188 AND xero_account_number='".$vv['AccountNumber']."'". '<br/>';;



                                        if($vv['ContactID'] !='00000000-0000-0000-0000-000000000000') {
                                            $stmt = $this->db->prepare("UPDATE cc_user_factory SET xero_contact_id='".$vv['ContactID']."' WHERE factory_id=319188 AND xero_account_number='".$vv['AccountNumber']."'");

                                           $stmt->execute();


                                        }

                                     }
                                    }
                                } else {
                                    $html.= $k . ':' . $v . '<br/>';
                                }
                            } else {
                                $html.= $k . ':' . $v . '<br/>';
                            }
                        }
                    } else {
                        /********** XERO API CALL RESPONSE IS NOT IN JSON, MAYBE ERROR'S MESSAGES *********/
                        $html .= $api_response;
                    }
                    /************* END OF DISPLAYING XERO API CALL RESPONSE *********************/
                } catch (\Exception $e) {
                    $html .= $e->getMessage();
                }
            }
        } else {
            $html = '<p>No response</p>';
        }
            
        return $html;
    }
    /********************************** UPDATE CONTACT *************************************************/
    public function updateContact($credentials, $json = "") // Contact in json
    {
        $api_response = false;

        // get data from db to generate json
        if(empty($json)) {
            $sql = 
<<<END

END;
            $stmt = $this->db->prepare($sql);
            $factory_id = 319188;
            $stmt->bind_param('d', $factory_id);
            $stmt->execute();
            /************* GENERATE CONTACT ******************/

            /************* END OF GENERATE CONTACT ******************/
        }

        /* CONTACT STRUCTURE
        $json = '
            {
                "ContactID":"a517984c-559f-4ff0-98ad-4fb0cbe3b5bc",
                "ContactNumber":"",
                "AccountNumber":"",
                "Name":"Chef Kim\'s1",
                "FirstName":"Chef",
                "LastName":"Kim\'s1",
                "EmailAddress":"example@example.com",
                "BankAccountDetails":"Commonwealth Bank ACC No. 319254",
                "CompanyNumber":"Conpany Reg. Number",
                "TaxNumber":"ABN Number",
                "AccountsReceivableTaxType":"EXEMPTOUTPUT",
                "AccountsPayableTaxType":"EXEMPTOUTPUT",
                "IsSupplier":"false",
                "IsCustomer":"true",
                "DefaultCurrency":"AUD",
                "ContactPersons":[{
                    "FirstName":"First",
                    "LastName":"Last",
                    "EmailAddress":"example@example.com",
                    "IncludeInEmails":"false"
                }],
                "Addresses":[{
                    "AddressType":"STREET",
                    "AddressLine1":"266-274 Derrimut Rd",
                    "AddressLine2":"Hoppers Crossing",
                    "AddressLine3":"",
                    "AddressLine4":"",
                    "City":"City of Wyndham",
                    "Region":"VIC",
                    "PostalCode":"3029",
                    "Country":"AU",
                    "AttentionTo": "Mr. Attention"
                }],
                "Phones":[{
                    "PhoneType":"DEFAULT",
                    "PhoneNumber":"123456789",
                    "PhoneAreaCode":"+3",
                    "PhoneCountryCode":"+61"
                }]
            }
        ';
        */        

        // valid data ?
        try {
            $data_arr = json_decode($json, true);
            if(is_array($data_arr) && count($data_arr) > 0) {
                // proceed
            } else {
                $api_response = [
                    'error' => true,
                    'origin' => 'local',
                    'message' => 'Require at least 1 contact'
                ];
            }
        } catch (\Exception $e) {
            $api_response  = [
                'error' => true,
                'origin' => 'local',
                'message' => $e->getMessage()
            ];
        }

        // valid tokens ?
        $tokens = $this->getTokens($credentials);
        if(empty($tokens['access_token'])) 
        {
            $api_response = [
                'error' => true,
                'origin' => 'local',
                'message' => 'Not authorized'
            ];
        }

        // tokens & data validated
        $params = [
            'data' => $json, 
        ];
        $defaults = array(
            CURLOPT_URL => $credentials['endpoint_uri'] . '/v1/xero/update-contact?accessToken=' . $tokens['access_token'],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_RETURNTRANSFER => true
        );        
        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        $response = curl_exec($ch);
        if($response === false) {
            curl_close($ch);
            $api_response = [
                'error' => true,
                'origin' => 'local',
                'message' => curl_error($ch)
            ];
        } else {
            curl_close($ch);
            $api_response = $response;
        }            

        return $api_response;
    }
    /********************************** GET CONTACTS *************************************************/
    public function getContacts($credentials)
    {
        $api_response = false;

        // valid tokens ?
        $tokens = $this->getTokens($credentials);
        if(empty($tokens['access_token'])) 
        {
            $api_response = [
                'error' => true,
                'origin' => 'local',
                'message' => 'Not authorized'
            ];
        }

        // tokens validated
        $defaults = array(
            CURLOPT_URL => $credentials['endpoint_uri'] . '/v1/xero/get-contacts?accessToken=' . $tokens['access_token'],
            CURLOPT_POST => false,
            CURLOPT_RETURNTRANSFER => true
        );        
        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        $response = curl_exec($ch);
        if($response === false) {
            curl_close($ch);
            $api_response = [
                'error' => true,
                'origin' => 'local',
                'message' => curl_error($ch)
            ];
        } else {
            curl_close($ch);
            $api_response = $response;
        }            

        return $api_response;
    }    

    /********************************** CREATE ITEMS *************************************************/
    public function createItems($credentials, $json = "") // array of Item in json
    {
        $api_response = false;

        $json = '
        [
            {
                "Code": "10007",
                "Name": "CK MARYLAND FILLET STRIPS MEAT STRIPS",
                "IsSold":"true",
                "IsPurchase":"false",
                "Description": "CK MARYLAND FILLET STRIPS MEAT STRIPS SHREDDE D MEAT 5MM",
                "PurchaseDescription": "",
                "PurchaseDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "SalesDetails":{
                    "UnitPrice":"10",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "IsTrackedAsInventory":"false",
                "InventoryAssetAccountCode":""
            },
            {
                "Code": "10014",
                "Name": "PORK FILLET STRIPS MEAT STRIPS MEAT",
                "IsSold":"true",
                "IsPurchase":"false",
                "Description": "PORK FILLET STRIPS MEAT STRIPS MEAT STRIPS 13 MM",
                "PurchaseDescription": "",
                "PurchaseDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "SalesDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "IsTrackedAsInventory":"false",
                "InventoryAssetAccountCode":""
            },
            {
                "Code": "P1003",
                "Name": "PORK NECK",
                "IsSold":"true",
                "IsPurchase":"false",
                "Description": "PORK NECK",
                "PurchaseDescription": "",
                "PurchaseDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "SalesDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "IsTrackedAsInventory":"false",
                "InventoryAssetAccountCode":""
            },
            {
                "Code": "D1001",
                "Name": "PEKIN DUCK 1.8-2.4KG WHOLE DUCK WHOLE DUCK",
                "IsSold":"true",
                "IsPurchase":"false",
                "Description": "PEKIN DUCK 1.8-2.4KG WHOLE DUCK WHOLE DUCK HEADLESS 1.8-2.0KG/BOX",
                "PurchaseDescription": "",
                "PurchaseDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "SalesDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "IsTrackedAsInventory":"false",
                "InventoryAssetAccountCode":""
            },
            {
                "Code": "D1009",
                "Name": "DUCK WINGS",
                "IsSold":"true",
                "IsPurchase":"false",
                "Description": "DUCK WINGS",
                "PurchaseDescription": "",
                "PurchaseDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "SalesDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "IsTrackedAsInventory":"false",
                "InventoryAssetAccountCode":""
            },
            {
                "Code": "D1005",
                "Name": "OLD DUCK",
                "IsSold":"true",
                "IsPurchase":"false",
                "Description": "OLD DUCK",
                "PurchaseDescription": "",
                "PurchaseDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "SalesDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "IsTrackedAsInventory":"false",
                "InventoryAssetAccountCode":""
            },
            {
                "Code": "S1001",
                "Name": "剁椒蝴蝶鱼腩 500G",
                "IsSold":"true",
                "IsPurchase":"false",
                "Description": "剁椒蝴蝶鱼腩 500G",
                "PurchaseDescription": "",
                "PurchaseDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "SalesDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "IsTrackedAsInventory":"false",
                "InventoryAssetAccountCode":""
            },
            {
                "Code": "S1003",
                "Name": "THIRTEEN SPICE CRAWFISH",
                "IsSold":"true",
                "IsPurchase":"false",
                "Description": "THIRTEEN SPICE CRAWFISH",
                "PurchaseDescription": "",
                "PurchaseDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "SalesDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "IsTrackedAsInventory":"false",
                "InventoryAssetAccountCode":""
            },
            {
                "Code": "S1004",
                "Name": "SPICY CRAYFISH",
                "IsSold":"true",
                "IsPurchase":"false",
                "Description": "SPICY CRAYFISH",
                "PurchaseDescription": "",
                "PurchaseDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "SalesDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "IsTrackedAsInventory":"false",
                "InventoryAssetAccountCode":""
            },
            {
                "Code": "S1006",
                "Name": "WHOLE COOKED VAN PRAWN",
                "IsSold":"true",
                "IsPurchase":"false",
                "Description": "WHOLE COOKED VAN PRAWN",
                "PurchaseDescription": "",
                "PurchaseDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "SalesDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "IsTrackedAsInventory":"false",
                "InventoryAssetAccountCode":""
            },
            {
                "Code": "S1009",
                "Name": "FRESHWATER CRAYFISH",
                "IsSold":"true",
                "IsPurchase":"false",
                "Description": "FRESHWATER CRAYFISH",
                "PurchaseDescription": "",
                "PurchaseDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "SalesDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "IsTrackedAsInventory":"false",
                "InventoryAssetAccountCode":""
            },
            {
                "Code": "S1000",
                "Name": "FRZ BASA STEAK",
                "IsSold":"true",
                "IsPurchase":"false",
                "Description": "FRZ BASA STEAK",
                "PurchaseDescription": "",
                "PurchaseDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "SalesDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "IsTrackedAsInventory":"false",
                "InventoryAssetAccountCode":""
           }
        ]        
        
        ';




        // valid data ?
        try {
            $data_arr = json_decode($json, true);
            if(is_array($data_arr) && count($data_arr) > 0) {
                // proceed
            } else {
                $api_response = [
                    'error' => true,
                    'origin' => 'local',
                    'message' => 'Require at least 1 item'
                ];
            }
        } catch (\Exception $e) {
            $api_response  = [
                'error' => true,
                'origin' => 'local',
                'message' => $e->getMessage()
            ];
        }

        // valid tokens ?
        $tokens = $this->getTokens($credentials);
        if(empty($tokens['access_token'])) 
        {
            $api_response = [
                'error' => true,
                'origin' => 'local',
                'message' => 'Not authorized'
            ];
        }

        // tokens & data validated
        $params = [
            'data' => $json, 
        ];
        $defaults = array(
            CURLOPT_URL => $credentials['endpoint_uri'] . '/v1/xero/create-items?accessToken=' . $tokens['access_token'],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_RETURNTRANSFER => true
        );        
        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        $response = curl_exec($ch);
        if($response === false) {
            curl_close($ch);
            $api_response = [
                'error' => true,
                'origin' => 'local',
                'message' => curl_error($ch)
            ];
        } else {
            curl_close($ch);
            $api_response = $response;
        }          

        return $this->processItems($api_response);

    }

    private function processItems($api_response)
    {
        $html = '';
        if($api_response) {
            if(is_array($api_response) && count($api_response) > 0) {
                foreach($api_response as $k=>$v) {
                    $html.= $k . ': ' . $v . '<br/>';
                }
            } else { // maybe json
                try {
                    $api_response_parsed = json_decode($api_response, true);
                    if(is_array($api_response_parsed) && count($api_response_parsed) > 0) {
                        foreach($api_response_parsed as $k => $v) {
                            if($k == 'response') {
                                if(is_array($v) && count($v) > 0) {
                                    foreach($v as $kv => $vv) {
                                        if(is_array($vv) && count($vv) > 0) {
                                            $html.= $kv . ': ' . print_r($vv, true) . '<br/>';
                                        } else {
                                            $html.= $kv . ': ' . $vv . '<br/>';
                                        }
                                    }
                                } else {
                                    $html.= $k . ':' . $v . '<br/>';
                                }
                            } else {
                                $html.= $k . ':' . $v . '<br/>';
                            }
                        }
                    } else {
                        $html .= $api_response;
                    }
                } catch (\Exception $e) {
                    $html .= $e->getMessage();
                }
            }
        } else {
            $html = '<p>No response</p>';
        }
            
        return $html;
    }
    /********************************** UPDATE ITEM *************************************************/
    public function updateItem($credentials, $json = "") // Item in json
    {
        $api_response = false;

        $json = '
            {
                "Code": "10007",
                "Name": "CK MARYLAND FILLET STRIPS MEAT STRIPS",
                "IsSold":"true",
                "IsPurchase":"false",
                "Description": "CK MARYLAND FILLET STRIPS MEAT STRIPS SHREDDE D MEAT 5MM",
                "PurchaseDescription": "",
                "PurchaseDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "SalesDetails":{
                    "UnitPrice":"10",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "IsTrackedAsInventory":"false",
                "InventoryAssetAccountCode":""
            },
            {
                "Code": "10014",
                "Name": "PORK FILLET STRIPS MEAT STRIPS MEAT",
                "IsSold":"true",
                "IsPurchase":"false",
                "Description": "PORK FILLET STRIPS MEAT STRIPS MEAT STRIPS 13 MM",
                "PurchaseDescription": "",
                "PurchaseDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "SalesDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "IsTrackedAsInventory":"false",
                "InventoryAssetAccountCode":""
            },
            {
                "Code": "P1003",
                "Name": "PORK NECK",
                "IsSold":"true",
                "IsPurchase":"false",
                "Description": "PORK NECK",
                "PurchaseDescription": "",
                "PurchaseDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "SalesDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "IsTrackedAsInventory":"false",
                "InventoryAssetAccountCode":""
            },
            {
                "Code": "D1001",
                "Name": "PEKIN DUCK 1.8-2.4KG WHOLE DUCK WHOLE DUCK",
                "IsSold":"true",
                "IsPurchase":"false",
                "Description": "PEKIN DUCK 1.8-2.4KG WHOLE DUCK WHOLE DUCK HEADLESS 1.8-2.0KG/BOX",
                "PurchaseDescription": "",
                "PurchaseDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "SalesDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "IsTrackedAsInventory":"false",
                "InventoryAssetAccountCode":""
            },
            {
                "Code": "D1009",
                "Name": "DUCK WINGS",
                "IsSold":"true",
                "IsPurchase":"false",
                "Description": "DUCK WINGS",
                "PurchaseDescription": "",
                "PurchaseDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "SalesDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "IsTrackedAsInventory":"false",
                "InventoryAssetAccountCode":""
            },
            {
                "Code": "D1005",
                "Name": "OLD DUCK",
                "IsSold":"true",
                "IsPurchase":"false",
                "Description": "OLD DUCK",
                "PurchaseDescription": "",
                "PurchaseDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "SalesDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "IsTrackedAsInventory":"false",
                "InventoryAssetAccountCode":""
            },
            {
                "Code": "S1001",
                "Name": "剁椒蝴蝶鱼腩 500G",
                "IsSold":"true",
                "IsPurchase":"false",
                "Description": "剁椒蝴蝶鱼腩 500G",
                "PurchaseDescription": "",
                "PurchaseDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "SalesDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "IsTrackedAsInventory":"false",
                "InventoryAssetAccountCode":""
            },
            {
                "Code": "S1003",
                "Name": "THIRTEEN SPICE CRAWFISH",
                "IsSold":"true",
                "IsPurchase":"false",
                "Description": "THIRTEEN SPICE CRAWFISH",
                "PurchaseDescription": "",
                "PurchaseDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "SalesDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "IsTrackedAsInventory":"false",
                "InventoryAssetAccountCode":""
            },
            {
                "Code": "S1004",
                "Name": "SPICY CRAYFISH",
                "IsSold":"true",
                "IsPurchase":"false",
                "Description": "SPICY CRAYFISH",
                "PurchaseDescription": "",
                "PurchaseDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "SalesDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "IsTrackedAsInventory":"false",
                "InventoryAssetAccountCode":""
            },
            {
                "Code": "S1006",
                "Name": "WHOLE COOKED VAN PRAWN",
                "IsSold":"true",
                "IsPurchase":"false",
                "Description": "WHOLE COOKED VAN PRAWN",
                "PurchaseDescription": "",
                "PurchaseDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "SalesDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "IsTrackedAsInventory":"false",
                "InventoryAssetAccountCode":""
            },
            {
                "Code": "S1009",
                "Name": "FRESHWATER CRAYFISH",
                "IsSold":"true",
                "IsPurchase":"false",
                "Description": "FRESHWATER CRAYFISH",
                "PurchaseDescription": "",
                "PurchaseDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "SalesDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "IsTrackedAsInventory":"false",
                "InventoryAssetAccountCode":""
            },
            {
                "Code": "S1000",
                "Name": "FRZ BASA STEAK",
                "IsSold":"true",
                "IsPurchase":"false",
                "Description": "FRZ BASA STEAK",
                "PurchaseDescription": "",
                "PurchaseDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "SalesDetails":{
                    "UnitPrice":"",
                    "AccountCode":"",
                    "COGSAccountCode":"",
                    "TaxType":""
                },
                "IsTrackedAsInventory":"false",
                "InventoryAssetAccountCode":""
           }
        ';
        // valid data ?
        try {
            $data_arr = json_decode($json, true);
            if(is_array($data_arr) && count($data_arr) > 0) {
                // proceed
            } else {
                $api_response = [
                    'error' => true,
                    'origin' => 'local',
                    'message' => 'Require at least 1 item'
                ];
            }
        } catch (\Exception $e) {
            $api_response  = [
                'error' => true,
                'origin' => 'local',
                'message' => $e->getMessage()
            ];
        }

        // valid tokens ?
        $tokens = $this->getTokens($credentials);
        if(empty($tokens['access_token'])) 
        {
            $api_response = [
                'error' => true,
                'origin' => 'local',
                'message' => 'Not authorized'
            ];
        }

        // tokens & data validated
        $params = [
            'data' => $json, 
        ];
        $defaults = array(
            CURLOPT_URL => $credentials['endpoint_uri'] . '/v1/xero/update-item?accessToken=' . $tokens['access_token'],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_RETURNTRANSFER => true
        );        
        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        $response = curl_exec($ch);
        if($response === false) {
            curl_close($ch);
            $api_response = [
                'error' => true,
                'origin' => 'local',
                'message' => curl_error($ch)
            ];
        } else {
            curl_close($ch);
            $api_response = $response;
        }          

        return $api_response;
    }

    /********************************** GET ITEMS *************************************************/
    public function getItems($credentials)
    {
        $api_response = false;

        // valid tokens ?
        $tokens = $this->getTokens($credentials);
        if(empty($tokens['access_token'])) 
        {
            $api_response = [
                'error' => true,
                'origin' => 'local',
                'message' => 'Not authorized'
            ];
        }

        // tokens validated
        $defaults = array(
            CURLOPT_URL => $credentials['endpoint_uri'] . '/v1/xero/get-items?accessToken=' . $tokens['access_token'],
            CURLOPT_POST => false,
            CURLOPT_RETURNTRANSFER => true
        );        
        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        $response = curl_exec($ch);
        if($response === false) {
            curl_close($ch);
            $api_response = [
                'error' => true,
                'origin' => 'local',
                'message' => curl_error($ch)
            ];
        } else {
            curl_close($ch);
            $api_response = $response;
        }          

        return $api_response;
    }

    /********************************** CREATE INVOICES *************************************************/
    public function createInvoices($credentials, $json = "") // array of Invoice in json
    {
        $api_response = false;

        $json = '

        [
            {
                "Type": "ACCREC",
                "Contact": {
                    "ContactID":"a517984c-559f-4ff0-98ad-4fb0cbe3b5bc"
                },
                "LineItems": [
                    {
                        "Description": "CK MARYLAND FILLET STRIPS MEAT STRIPS SHREDDE D MEAT 5MM",
                        "Quantity": "8.000",
                        "UnitAmount": "16.80",
                        "ItemCode": "10007",
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
        ';


        // valid data ?
        try {
            $data_arr = json_decode($json, true);
            if(is_array($data_arr) && count($data_arr) > 0) {
                // proceed
            } else {
                $api_response = [
                    'error' => true,
                    'origin' => 'local',
                    'message' => 'Require at least 1 invoice'
                ];
            }
        } catch (\Exception $e) {
            $api_response  = [
                'error' => true,
                'origin' => 'local',
                'message' => $e->getMessage()
            ];
        }

        // valid tokens ?
        $tokens = $this->getTokens($credentials);
        if(empty($tokens['access_token'])) 
        {
            $api_response = [
                'error' => true,
                'origin' => 'local',
                'message' => 'Not authorized'
            ];
        }

        // tokens & data validated
        $params = [
            'data' => $json, 
        ];
        $defaults = array(
            CURLOPT_URL => $credentials['endpoint_uri'] . '/v1/xero/post-invoices?accessToken=' . $tokens['access_token'],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_RETURNTRANSFER => true
        );        
        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        $response = curl_exec($ch);
        if($response === false) {
            curl_close($ch);
            $api_response = [
                'error' => true,
                'origin' => 'local',
                'message' => curl_error($ch)
            ];
        } else {
            curl_close($ch);
            $api_response = $response;
        }            

        return $this->processInvoices($api_response);        
        
    }
    private function processInvoices($api_response)
    {
        $html = '';
        if($api_response) {
            if(is_array($api_response) && count($api_response) > 0) {
                foreach($api_response as $k=>$v) {
                    $html.= $k . ': ' . $v . '<br/>';
                }
            } else { // maybe json
                try {
                    $api_response_parsed = json_decode($api_response, true);
                    if(is_array($api_response_parsed) && count($api_response_parsed) > 0) {
                        foreach($api_response_parsed as $k => $v) {
                            if($k == 'response') {
                                if(is_array($v) && count($v) > 0) {
                                    foreach($v as $kv => $vv) {
                                        if(is_array($vv) && count($vv) > 0) {
                                            $html.= $kv . ': ' . print_r($vv, true) . '<br/>';
                                        } else {
                                            $html.= $kv . ': ' . $vv . '<br/>';
                                        }
                                    }
                                } else {
                                    $html.= $k . ':' . $v . '<br/>';
                                }
                            } else {
                                $html.= $k . ':' . $v . '<br/>';
                            }
                        }
                    } else {
                        $html .= $api_response;
                    }
                } catch (\Exception $e) {
                    $html .= $e->getMessage();
                }
            }
        } else {
            $html = '<p>No response</p>';
        }
            
        return $html;
    }
    /********************************** UPDATE INVOICE *************************************************/
    public function updateInvoice($credentials, $json = "") // Invoice in json
    {
        $api_response = false;

        $json = '
            {
                "InvoiceID":"", 
                "Type": "ACCREC",
                "Contact": {
                    "ContactID":"a517984c-559f-4ff0-98ad-4fb0cbe3b5bc"
                },
                "LineItems": [
                    {
                        "Description": "CK MARYLAND FILLET STRIPS MEAT STRIPS SHREDDE D MEAT 5MM",
                        "Quantity": "8.000",
                        "UnitAmount": "16.80",
                        "ItemCode": "10007",
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
        ';


        // valid data ?
        try {
            $data_arr = json_decode($json, true);
            if(is_array($data_arr) && count($data_arr) > 0) {
                // proceed
            } else {
                $api_response = [
                    'error' => true,
                    'origin' => 'local',
                    'message' => 'Require at least 1 invoice'
                ];
            }
        } catch (\Exception $e) {
            $api_response  = [
                'error' => true,
                'origin' => 'local',
                'message' => $e->getMessage()
            ];
        }

        // valid tokens ?
        $tokens = $this->getTokens($credentials);
        if(empty($tokens['access_token'])) 
        {
            $api_response = [
                'error' => true,
                'origin' => 'local',
                'message' => 'Not authorized'
            ];
        }

        // tokens & data validated
        $params = [
            'data' => $json, 
        ];
        $defaults = array(
            CURLOPT_URL => $credentials['endpoint_uri'] . '/v1/xero/update-invoice?accessToken=' . $tokens['access_token'],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_RETURNTRANSFER => true
        );        
        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        $response = curl_exec($ch);
        if($response === false) {
            curl_close($ch);
            $api_response = [
                'error' => true,
                'origin' => 'local',
                'message' => curl_error($ch)
            ];
        } else {
            curl_close($ch);
            $api_response = $response;
        }            

        return $api_response;
    }

    /********************************** GET INVOICES *************************************************/
    public function getInvoices($credentials)
    {
        $api_response = false;

        // valid tokens ?
        $tokens = $this->getTokens($credentials);
        if(empty($tokens['access_token'])) 
        {
            $api_response = [
                'error' => true,
                'origin' => 'local',
                'message' => 'Not authorized'
            ];
        }

        // tokens validated
        $defaults = array(
            CURLOPT_URL => $credentials['endpoint_uri'] . '/v1/xero/get-invoices?accessToken=' . $tokens['access_token'],
            CURLOPT_POST => false,
            CURLOPT_RETURNTRANSFER => true
        );        
        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        $response = curl_exec($ch);
        if($response === false) {
            curl_close($ch);
            $api_response = [
                'error' => true,
                'origin' => 'local',
                'message' => curl_error($ch)
            ];
        } else {
            curl_close($ch);
            $api_response = $response;
        }            

        return $api_response;  
    }

}
?>