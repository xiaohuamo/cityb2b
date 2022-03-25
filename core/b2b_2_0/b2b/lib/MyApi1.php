<?php
class MyApi
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /********************************** CREDENTIALS *************************************************/
    private function getTokens($credentials) // return array of tokens or errors
    {
        $tokens = [];

        if(empty($credentials['username']))
        {
            return [
                'error' => true,
                'origin' => 'local',
                'response' => 'Not a valid username'
            ];
        }

        try {
            $stmt = $this->db->prepare("SELECT tokens FROM tokens WHERE username=? AND client_id=? AND client_secret=?");
            $stmt->bind_param('sss', $credentials['username'], $credentials['client_id'], $credentials['client_secret']);
            $stmt->execute();
            $stmt->bind_result($tokens_json);
            $stmt->fetch();
        } catch (\Exception $e) {
            return [
                'error' => true,
                'origin' => 'local-can not get token acess',
                'response' => $e->getMessage()
            ];
        }

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

    private function doAuth($credentials) // return array of tokens or errors
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
        curl_close($ch);

        if($response === false) {
            return [
                'error' => true,
                'origin' => 'local',
                'response' => url_error($ch)
            ];
        } else {
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
                        'response' => 'Not a valid resposne'
                    ];
                }
            } catch(\Exception $e) {
                return [
                    'error' => true,
                    'origin' => 'local',
                    'response' => $e->getMessage()
                ];
            }
        }
    }

    private function doSaveTokens($credentials, $new_tokens_json)
    {
        try {
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
        } catch(\Exception $e) {
            // do nothing
        }
    }

    private function doRefreshToken($credentials, $refresh_token) // return array of tokens or errors
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
        curl_close($ch);

        if($response === false) {
            return [
                'error' => true,
                'origin' => 'local',
                'response' => curl_error($ch)
            ];
        } else {
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
                        'response' => $response
                    ];
                }
            } catch(\Exception $e) {
                return [
                    'error' => true,
                    'origin' => 'local',
                    'response' => $e->getMessage()
                ];
            }
        }
    }

    /********************************** CREATE CONTACTS *************************************************/
    public function createContacts($credentials, $json = "") // array of Contact in json
    {
        // get data from db to generate json
        if(empty($json)) {
            $sql = "";
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



            //$stmt = $this->db->prepare($sql);
            //$factory_id = 319188;
            //$stmt->bind_param('d', $factory_id);
            //$stmt->execute();
            //$result = $stmt->get_result();
            /************* QUERY RESULTS ******************/
            //$result->fetch_all(MYSQLI_BOTH);
            //while ($row = $result->fetch_array(MYSQLI_NUM)) {
            /**
             * GEBERATE JSON BASED ON CONTACT STRUCTURE BELOW
             * Example:
             *
             * $arr = [
             *  [
             *      'AccountName' => $row['account_number'],
             *      'Name' => $row['company_name']
             *  ]
             * ];
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
                    "PhoneCountryCodce":"+61"
                }]
            }
        ]';
        */
        $json = json_encode($rows);
      //  var_dump($json);exit;
        if(empty($json)) {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => 'Empty'
            ]);
        }



        // valid data ?
        try {
            $data_arr = json_decode($json, true);
            if(is_array($data_arr) && count($data_arr) > 0) {
                foreach($data_arr as $v) {
                    if(empty($v['Name'])) {
                        return json_encode([
                            'error' => true,
                            'origin' => 'local',
                            'response' => 'Name required'
                        ]);
                    }
                }
                // continue if all contacts has Name
            } else {
                return json_encode([
                    'error' => true,
                    'origin' => 'local',
                    'response' => 'Require at least 1 contact'
                ]);
            }
        } catch (\Exception $e) {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => $e->getMessage()
            ]);
        }

        // valid tokens ?
        $tokens = $this->getTokens($credentials);
        if(empty($tokens['access_token']))
        {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => 'Not authorized'
            ]);
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
        curl_close($ch);
        if($response === false) {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => curl_error($ch)
            ]);
        } else {
            return $this->processCreateContacts($response);
        }

        return json_encode([
            'error' => true,
            'origin' => 'local',
            'response' => 'Unhandled errors'
        ]);
    }

    private function processCreateContacts($response)
    {
        /************* SUCCESFUL XERO API RESPONSE IN JSON  ******************/
        $response_parsed = json_decode($response, true);
        // YOU CAN CHECK THE RESPONSE, UPDATE DB IF NEEDED

        // DISPLAY THE RESPONSE
        return $response;
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
       $json =
       ' 
       {
            "ContactID": "abf0c534-d768-46c4-a17c-bf17b547d5c7",
            "ContactNumber":"",
            "AccountNumber":"319275",
            "Name":"Chefkim001",
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


        if(empty($json)) {
            // QUERY DB TO GENERATE JSON
        }

        // valid data ?
        if(empty($json)) {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => 'Empty'
            ]);
        }

        try {
            $data_arr = json_decode($json, true);

            if(is_array($data_arr) && count($data_arr) > 0) {

                if(empty($data_arr['ContactID'])) {
                    return json_encode([
                        'error' => true,
                        'origin' => 'local',
                        'response' => 'ContactID required'
                    ]);
                }
            } else {
                return json_encode([
                    'error' => true,
                    'origin' => 'local',
                    'response' => 'Not a valid Contact'
                ]);
            }
        } catch (\Exception $e) {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => $e->getMessage()
            ]);
        }

        // valid tokens ?
        $tokens = $this->getTokens($credentials);
        if(empty($tokens['access_token']))
        {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => 'Not authorized'
            ]);
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
        curl_close($ch);

        if($response === false) {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => curl_error($ch)
            ]);
        } else {
            return $this->processUpdateContact($response);
        }

        return json_encode([
            'error' => true,
            'origin' => 'local',
            'message' => 'Unhandled errors'
        ]);
    }

    private function processUpdateContact($response)
    {
        /************* SUCCESFUL XERO API RESPONSE IN JSON  ******************/
        $response_parsed = json_decode($response, true);
        // YOU CAN CHECK THE RESPONSE, UPDATE DB IF NEEDED

        // DISPLAY THE RESPONSE
        return $response;
    }

    /********************************** GET CONTACTS *************************************************/
    public function getContacts($credentials)
    {
        // valid tokens ?
        $tokens = $this->getTokens($credentials);
        if(empty($tokens['access_token']))
        {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => 'Not authorized'
            ]);
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
        curl_close($ch);

        if($response === false) {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => curl_error($ch)
            ]);
        } else {
            return $this->processGetContacts($response);
        }

        return json_encode([
            'error' => true,
            'origin' => 'local',
            'message' => 'Unhandled errors'
        ]);
    }

    private function processGetContacts($response)
    {
        /************* SUCCESFUL XERO API RESPONSE IN JSON  ******************/
        $response_parsed = json_decode($response, true);
        // YOU CAN CHECK THE RESPONSE, UPDATE DB IF NEEDED

        // DISPLAY THE RESPONSE
        return $response;
    }

    /********************************** CREATE ITEMS *************************************************/
    public function createItems($credentials, $json = "") // array of Item in json
    {
        $json =
            ' 
     [  {
             "Code": "t7ff0328",
            "Name": "harry mo chips ",
            "IsSold":"true",
            "IsPurchase":"false",
            "Description": "CK MARYLAND FILLET STRIPS MEAT STRIPS SHREDDE D MEAT 5MM",
            "PurchaseDescription": "",
            "PurchaseDetails":
            [{
                "UnitPrice":"5.0000",
                "AccountCode":"",
                "COGSAccountCode":"",
                "TaxType":""
            }],
            "SalesDetails":
            [{
                "UnitPrice":"10.000",
                "AccountCode":"",
                "COGSAccountCode":"",
                "TaxType":""
            }],
            "IsTrackedAsInventory":"false",
            "InventoryAssetAccountCode":"" 
        }]
         ';

        // valid data ?
        if(empty($json)) {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => 'Empty'
            ]);
        }

        try {

            $data_arr = json_decode($json, true);
           // var_dump($data_arr);exit;
            if(is_array($data_arr) && count($data_arr) > 0) {
                foreach($data_arr as $v) {
                    if(empty($v['Code'])) {
                        return json_encode([
                            'error' => true,
                            'origin' => 'local',
                            'response' => 'Code required'
                        ]);
                    }
                }
                // continue if all Item has Code
            } else {
                return json_encode([
                    'error' => true,
                    'origin' => 'local',
                    'response' => 'Require at least 1 item'
                ]);
            }
        } catch (\Exception $e) {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => $e->getMessage()
            ]);
        }

        // valid tokens ?
        $tokens = $this->getTokens($credentials);
        if(empty($tokens['access_token']))
        {
            $api_response = [
                'error' => true,
                'origin' => 'local',
                'response' => 'Not authorized'
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
        curl_close($ch);

        if($response === false) {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => curl_error($ch)
            ]);
        } else {
            return $this->processCreateItems($response);
        }

        return json_encode([
            'error' => true,
            'origin' => 'local',
            'response' => 'Unhandled errors'
        ]);

    }

    private function processCreateItems($response)
    {
        /************* SUCCESFUL XERO API RESPONSE IN JSON  ******************/
        $response_parsed = json_decode($response, true);
        // YOU CAN CHECK THE RESPONSE, UPDATE DB IF NEEDED

        // DISPLAY THE RESPONSE
        return $response;
    }
    /********************************** UPDATE ITEM *************************************************/
    public function updateItem($credentials, $json = "") // Item in json
    {
        /* ITEM STRUCTURE */

      /*所有的里面的子数组都需要[] */
        /*最外面的不需要中括号*/



        $json =
            ' 
       {
             "ItemID": "7f5f0aae-83c7-4224-b5d6-17abf46235a9",
             "Code": "t70328",
            "Name": "harry mo chips ",
            "IsSold":"true",
            "IsPurchase":"false",
            "Description": "CK MARYLAND FILLET STRIPS MEAT STRIPS SHREDDE D MEAT 5MM",
            "PurchaseDescription": "",
            "PurchaseDetails":
            [{
                "UnitPrice":"5.0000",
                "AccountCode":"",
                "COGSAccountCode":"",
                "TaxType":""
            }],
            "SalesDetails":
            [{
                "UnitPrice":"10.000",
                "AccountCode":"",
                "COGSAccountCode":"",
                "TaxType":""
            }],
            "IsTrackedAsInventory":"false",
            "InventoryAssetAccountCode":"" 
        }
         ';







        if(empty($json)) {
            // QUERY DB TO GENERATE JSON
        }

        // valid data ?
        if(empty($json)) {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => 'Empty'
            ]);
        }

        try {
            $data_arr = json_decode($json, true);
          //  var_dump($data_arr);exit;
            if(is_array($data_arr) && count($data_arr) > 0) {
                // check mandatory
                if(empty($data_arr['Code'])) {
                    return json_encode([
                        'error' => true,
                        'origin' => 'local',
                        'response' => 'Code required'
                    ]);
                }
            } else {
                return json_encode([
                    'error' => true,
                    'origin' => 'local',
                    'response' => 'Item to update required'
                ]);
            }
        } catch (\Exception $e) {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => $e->getMessage()
            ]);
        }

        // valid tokens ?
        $tokens = $this->getTokens($credentials);
        if(empty($tokens['access_token']))
        {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => 'Not authorized'
            ]);
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
        curl_close($ch);

        if($response === false) {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => curl_error($ch)
            ]);
        } else {
            return $this->processUpdateItem($response);
        }

        return json_encode([
            'error' => true,
            'origin' => 'local',
            'response' => 'Unhandles errors'
        ]);
    }

    private function processUpdateItem($response)
    {
        /************* SUCCESFUL XERO API RESPONSE IN JSON  ******************/
        $response_parsed = json_decode($response, true);
        // YOU CAN CHECK THE RESPONSE, UPDATE DB IF NEEDED

        // DISPLAY THE RESPONSE
        return $response;
    }

    /********************************** GET ITEMS *************************************************/
    public function getItems($credentials)
    {
        // valid tokens ?
        $tokens = $this->getTokens($credentials);
        if(empty($tokens['access_token']))
        {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => 'Not authorized'
            ]);
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
        curl_close($ch);

        if($response === false) {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => curl_error($ch)
            ]);
        } else {
            return $this->processGetItems($response);
        }

        return json_encode([
            'error' => true,
            'origin' => 'local',
            'response' => 'Unhandled errors'
        ]);
    }

    private function processGetItems($response)
    {
        /************* SUCCESFUL XERO API RESPONSE IN JSON  ******************/
        $response_parsed = json_decode($response, true);
        // YOU CAN CHECK THE RESPONSE, UPDATE DB IF NEEDED

        // DISPLAY THE RESPONSE
        return $response;
    }

    /********************************** CREATE INVOICES *************************************************/
    public function createInvoices($credentials, $json = "") // array of Invoice in json
    {
        /* ITEMS STRUCTURE
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
        */

        // valid data ?
        if(empty($json))
        {
            // QUERY DB TO GENERATE JSON

            $json ='[ {
             "Type": "ACCREC",
              "Contact": {
            "ContactID": "abf0c534-d768-46c4-a17c-bf17b547d5c7"
           
          },
         "LineItems": [
                {
                   "Name": "Harry meant 58mcs",
                    "Description": "CK MARYLAND FILLET STRIPS MEAT STRIPS SHREDDE D MEAT 5MM",
                    "Quantity": "8.000",
                    "UnitAmount": "16.80",
                    "ItemCode": "t70328",
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
    } ]';

        }

        if(empty($json))
        {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => 'Empty'
            ]);
        }

        try {
            $data_arr = json_decode($json, true);
            if(is_array($data_arr) && count($data_arr) > 0) {
                foreach($data_arr as $v) {
                    // check mandatory
                    if( empty($v['Type']) || empty($v['Contact']) || empty($v['LineItems'])) {
                        return json_encode([
                            'error' => true,
                            'origin' => 'local',
                            'response' => 'Type, Contact, LineItems required'
                        ]);
                    }
                }
            } else {
                return json_encode([
                    'error' => true,
                    'origin' => 'local',
                    'response' => 'Require at least 1 invoice'
                ]);
            }
        } catch (\Exception $e) {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => $e->getMessage()
            ]);
        }

        // valid tokens ?
        $tokens = $this->getTokens($credentials);
        if(empty($tokens['access_token']))
        {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => 'Not authorized'
            ]);
        }

        // tokens & data validated
        $params = [
            'data' => $json,
        ];
        $defaults = array(
            CURLOPT_URL => $credentials['endpoint_uri'] . '/v1/xero/create-invoices?accessToken=' . $tokens['access_token'],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_RETURNTRANSFER => true
        );

        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        $response = curl_exec($ch);
        curl_close($ch);

        if($response === false) {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => curl_error($ch)
            ]);
        } else {
            return $this->processCreateInvoices($response);
        }

        return json_encode([
            'error' => true,
            'origin' => 'local',
            'response' => 'INhandled errors'
        ]);
    }

    private function processCreateInvoices($response)
    {
        /************* SUCCESFUL XERO API RESPONSE IN JSON  ******************/
        $response_parsed = json_decode($response, true);
        // YOU CAN CHECK THE RESPONSE, UPDATE DB IF NEEDED

        // DISPLAY THE RESPONSE
        return $response;
    }
    /********************************** UPDATE INVOICE *************************************************/
    public function updateInvoice($credentials, $json = "") // Invoice in json
    {
        /* Invoice Structure
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
        */

        if (empty($json)) {

            // QUERY DB TO CREATE JSON



            $json ='{
             "Type": "ACCREC",
              "Contact": {
            "ContactID": "abf0c534-d768-46c4-a17c-bf17b547d5c7"
           
          },
         "LineItems": [
                {
                   "Name": "Harry meant 58mcs",
                    "Description": "CK MARYLAND FILLET STRIPS MEAT STRIPS SHREDDE D MEAT 5MM",
                    "Quantity": "8.000",
                    "UnitAmount": "16.80",
                    "ItemCode": "t70328",
                    "AccountCode":"200",
                    "LineItemID":"",
                    "TaxType": "OUTPUT",
                    "TaxAmount": "130.44",
                    "LineAmount": "1340.40",
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
          "Date": "2022-03-22",
            "DueDate": "2022-03-28",
            "LineAmountTypes": "Exclusive",
             "InvoiceID": "c9a51a7b-c29f-443e-8dde-2e99529471c6",
            "Reference":"",
            "BrandingThemeID":"",
            "CurrencyCode":"AUD",
            "CurrencyRate":"",
             "Status": "AUTHORISED",
            "SentToContact":"",
            "ExpectedPaymentDate":"",
            "PlannedPaymentDate":""
    }';


        }

        // valid data ?
        if(empty($json)) {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => 'Empty'
            ]);
        }

        try {
            $data_arr = json_decode($json, true);
            if(is_array($data_arr) && count($data_arr) > 0) {
                // check mandatory
                if(empty($data_arr['InvoiceID'])) {
                    return json_encode([
                        'error' => true,
                        'origin' => 'local',
                        'response' => 'InvoiceID required'
                    ]);
                }
                // proceed
            } else {
                return json_encode([
                    'error' => true,
                    'origin' => 'local',
                    'response' => 'Invoice to update required'
                ]);
            }
        } catch (\Exception $e) {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => $e->getMessage()
            ]);
        }

        // valid tokens ?
        $tokens = $this->getTokens($credentials);
        if(empty($tokens['access_token']))
        {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => 'Not authorized'
            ]);
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
        curl_close($ch);

        if($response === false) {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => curl_error($ch)
            ]);
        } else {
            return $this->processUpdateInvoice($response);
        }

        return json_encode([
            'error' => true,
            'origin' => 'local',
            'response' => 'Unhandled errors'
        ]);
    }

    private function processUpdateInvoice($response)
    {
        /************* SUCCESFUL XERO API RESPONSE IN JSON  ******************/
        $response_parsed = json_decode($response, true);
        // YOU CAN CHECK THE RESPONSE, UPDATE DB IF NEEDED

        // DISPLAY THE RESPONSE
        return $response;
    }

    /********************************** GET INVOICES *************************************************/
    public function getInvoices($credentials)
    {
        // valid tokens ?
        $tokens = $this->getTokens($credentials);
        if(empty($tokens['access_token']))
        {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => 'Not authorized'
            ]);
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
        curl_close($ch);

        if($response === false) {
            return json_encode([
                'error' => true,
                'origin' => 'local',
                'response' => curl_error($ch)
            ]);
        } else {
            return $this->processGetInvoices($response);
        }

        return json_encode([
            'error' => true,
            'origin' => 'local',
            'response' => 'Unhandled errors'
        ]);
    }

    private function processGetInvoices($response)
    {
        /************* SUCCESFUL XERO API RESPONSE IN JSON  ******************/
        $response_parsed = json_decode($response, true);
        // YOU CAN CHECK THE RESPONSE, UPDATE DB IF NEEDED

        // DISPLAY THE RESPONSE
        return $response;
    }


}
?>