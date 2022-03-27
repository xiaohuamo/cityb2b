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
            $stmt = $this->db->prepare("SELECT cc_tokens FROM cc_tokens WHERE username=? AND client_id=? AND client_secret=?");
            $stmt->bind_param('sss', $credentials['username'], $credentials['client_id'], $credentials['client_secret']);
            $stmt->execute();
            $stmt->bind_result($tokens_json);
            $stmt->fetch();
        } catch (\Exception $e) {
            return [
                'error' => true,
                'origin' => 'local',
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
        /* LIST OF CONTACT STRUCTURE
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

        // SAMPLE CODE
        // Not all of the contact structure are needed
        // Name is mandatory and ContactNumber is needed when update xero identifier to your db

        $arr = [
            [
                "AccountNumber" => "1e2532345", // your system db customer id
                "Name" => "Cust4oemer THree", // company or customer name
                "FirstName" => "First",
                "Lastname" => "Last",
                "EmailAddress" => "customer_one@whls.wisenetware.com", // FirstName, LastName, Email will be auto added as primary contact person by Xero
                "Addresses" => [
                    [
                        "AddressType" => "STREET",
                        "AddressLine1" => "266-274 Derrimut Rd",
                        "AddressLine2" => "Hoppers Crossing",
                        "City" => "City of Wyndham",
                        "Region" => "VIC",
                        "PostalCode" => "3029",
                        "Country" => "AU",
                        "AttentionTo" => "Mr. Attention" // it's very strict, if you added extra comman at the end, it won't works => "AttentionTo" => "Mr. Attention",
                    ]
                ],
                "Phones" => [
                    [
                        "PhoneType" => "DEFAULT",
                        "PhoneNumber" => "123456789",
                        "PhoneAreaCode" => "+3",
                        "PhoneCountryCode" => "+61"
                    ]
                ]
            ]
        ];
      //  $json = json_encode($arr);
      // var_dump($json);exit;

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
                    if(empty($v['Name'])  ) {
                        return json_encode([
                            'error' => true,
                            'origin' => 'local',
                            'response' => 'Name required'
                        ]);
                    }

                    if(empty($v['AccountNumber'])  ) {
                        return json_encode([
                            'error' => true,
                            'origin' => 'local',
                            'response' => $v['ContactNumber'] .'Account Number  required'
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
        // RESPONSE FROM SAMPLE CODE => response is in json
        /*
        [ // response from xero when create contacts
            {
                "ContactID": "58905daa-1641-4dfc-bd78-6fb7ff4d0e9b",
                "ContactNumber": "12345",
                "ContactStatus": "ACTIVE",
                "Name": "Customer THree",
                "FirstName": "First",
                "EmailAddress": "customer_one@whls.wisenetware.com",
                "ContactPersons": [
                    {
                        "FirstName": "First",
                        "EmailAddress": "customer_one@whls.wisenetware.com",
                        "IncludeInEmails": false
                    }
                ],
                "BankAccountDetails": "",
                "Addresses": [
                    {
                        "AddressType": "STREET",
                        "AddressLine1": "266-274 Derrimut Rd",
                        "AddressLine2": "Hoppers Crossing",
                        "City": "City of Wyndham",
                        "Region": "VIC",
                        "PostalCode": "AU",
                        "Country": ""
                    },
                    {
                        "AddressType": "POBOX",
                        "City": "",
                        "Region": "",
                        "PostalCode": "",
                        "Country": ""
                    }
                ],
                "Phones": [
                    {
                        "PhoneType": "DEFAULT",
                        "PhoneNumber": "123456789",
                        "PhoneAreaCode": "",
                        "PhoneCountryCode": ""
                    },
                    {
                        "PhoneType": "DDI",
                        "PhoneNumber": "",
                        "PhoneAreaCode": "",
                        "PhoneCountryCode": ""
                    },
                    {
                        "PhoneType": "FAX",
                        "PhoneNumber": "",
                        "PhoneAreaCode": "",
                        "PhoneCountryCode": ""
                    },
                    {
                        "PhoneType": "MOBILE",
                        "PhoneNumber": "",
                        "PhoneAreaCode": "",
                        "PhoneCountryCode": ""
                    }
                ],
                "IsSupplier": false,
                "IsCustomer": false,
                "SalesTrackingCategories": [],
                "PurchasesTrackingCategories": [],
                "UpdatedDateUTC": "\/Date(1647995730573+0000)\/",
                "ContactGroups": [],
                "HasAttachments": false,
                "HasValidationErrors": false,
                "StatusAttributeString": "OK"
            }
        ]';
        */

        /************* SUCCESFUL XERO API RESPONSE IN JSON  ******************/
        $response_parsed = json_decode($response, true);
        // YOU CAN CHECK THE RESPONSE, UPDATE DB IF NEEDED

        // if(is_array($response_parsed) && count($response_parsed) > 0)
        // {
        //     foreach($response_parsed as $v)
        //     {
        //         if( !empty($v['ContactID']) && !empty($v['ContactNumber']) && empty($v['HasValidationErrors']) )
        //         {
        //             // update the ContactID to your DB
        //         } else {
        //             // log somehere else
        //         }
        //     }
        // }

        // DISPLAY THE RESPONSE
        return $response_parsed;
    }
    /********************************** UPDATE CONTACT *************************************************/
    public function updateContact($credentials, $json = "") // Contact in json
    {
        /* CONTACT STRUCTURE
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
        */

        // SAMPLE CODE
        $arr = [
            "ContactID" => "58905daa-1641-4dfc-bd78-6fb7ff4d0e9b", // from previous response when created successfully
            "Name" => "Customer THree",
            "BankAccountDetails" => "Commonwealth Bank ACC No. 319254" // update this field
        ];
        $json = json_encode($arr);

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
        /* Response from Xero, BankAccountDetails updated
        [
            {
                "ContactID": "58905daa-1641-4dfc-bd78-6fb7ff4d0e9b",
                "ContactNumber": "12345",
                "ContactStatus": "ACTIVE",
                "Name": "Customer THree",
                "FirstName": "First",
                "EmailAddress": "customer_one@whls.wisenetware.com",
                "ContactPersons": [
                    {
                        "FirstName": "First",
                        "EmailAddress": "customer_one@whls.wisenetware.com",
                        "IncludeInEmails": false
                    }
                ],
                "BankAccountDetails": "Commonwealth Bank ACC No. 319254",
                "Addresses": [
                    {
                        "AddressType": "STREET",
                        "AddressLine1": "266-274 Derrimut Rd",
                        "AddressLine2": "Hoppers Crossing",
                        "City": "City of Wyndham",
                        "Region": "VIC",
                        "PostalCode": "AU",
                        "Country": ""
                    },
                    {
                        "AddressType": "POBOX",
                        "City": "",
                        "Region": "",
                        "PostalCode": "",
                        "Country": ""
                    }
                ],
                "Phones": [
                    {
                        "PhoneType": "DEFAULT",
                        "PhoneNumber": "123456789",
                        "PhoneAreaCode": "",
                        "PhoneCountryCode": ""
                    },
                    {
                        "PhoneType": "DDI",
                        "PhoneNumber": "",
                        "PhoneAreaCode": "",
                        "PhoneCountryCode": ""
                    },
                    {
                        "PhoneType": "FAX",
                        "PhoneNumber": "",
                        "PhoneAreaCode": "",
                        "PhoneCountryCode": ""
                    },
                    {
                        "PhoneType": "MOBILE",
                        "PhoneNumber": "",
                        "PhoneAreaCode": "",
                        "PhoneCountryCode": ""
                    }
                ],
                "IsSupplier": false,
                "IsCustomer": false,
                "SalesTrackingCategories": [],
                "PurchasesTrackingCategories": [],
                "UpdatedDateUTC": "\/Date(1648000246657+0000)\/",
                "ContactGroups": [],
                "BatchPayments": {
                    "BankAccountNumber": "Commonwealth Bank ACC No. 319254"
                },
                "HasAttachments": false,
                "HasValidationErrors": false
            }
        ]
        */

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
        // "ItemID": "00000000-0000-0000-0000-000000000000",

        /* List of Items Structure
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
        ]
        */

        // SAMPLE CODE

     /*   $arr = [
            [
                "Code" => "P-1004",
                "Name" => "Product 105",
                "IsSold" => "true",
                "IsPurchased" => "true",
                "Description" => "Product 101 description",
                "PurchaseDetails" => [
                    "UnitPrice" => "20.000"
                ],
                "SalesDetails" => [
                    "UnitPrice" => "10.000",
                    "AccountCode" => "200"
                ]
            ],
            [
                "Code" => "P-1005",
                "Name" => "Product 104",
                "IsSold" => "true",
                "IsPurchased" => "true",
                "Description" => "Product 101 description",
                "PurchaseDetails" => [
                    "UnitPrice" => "20.000"
                ],
                "SalesDetails" => [
                    "UnitPrice" => "10.000",
                    "AccountCode" => "200"
                ]
            ]
        ];


        $json = json_encode($arr); */
    /*    $json ='
        
        	[
                    {
                        "Code":"P-1006",
                        "Name":"Product 106",
                        "IsSold":"true",
                        "IsPurchased":"true",
                        "Description":"Product 101 description",
                        "PurchaseDetails":
                            {
                            "UnitPrice":"20.000"
                            },
                        "SalesDetails":
                            {
                            "UnitPrice":"10.000",
                            "AccountCode":"200"
                            }
                    },
                    {
                        "Code":"P-1007",
                        "Name":"Product 107",
                        "IsSold":"true",
                        "IsPurchased":"true",
                        "Description":"Product 101 description",
                        "PurchaseDetails":
                            {
                            "UnitPrice":"20.000"
                            },
                        "SalesDetails":
                            {
                            "UnitPrice":"10.000",
                            "AccountCode":"200"
                            }
                    }
                ]
        
        ';  */
        //var_dump($json);exit;
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
        // RESPONSE FROM SAMPLE CODE => response is in json
        /*
        [
            {
                "Code": "P-1003",
                "Name": "Product 103",
                "IsSold": true,
                "IsPurchased": true,
                "Description": "Product 101 description",
                "PurchaseDetails": {
                    "UnitPrice": 20,
                    "TaxType": ""
                },
                "SalesDetails": {
                    "UnitPrice": 10,
                    "AccountCode": "200",
                    "TaxType": "TAX001"
                },
                "IsTrackedAsInventory": false,
                "UpdatedDateUTC": "\/Date(1648004932970)\/",
                "ItemID": "5560efb7-bfda-412e-b8a4-a5a34e972201",
                "StatusAttributeString": "OK",
                "ValidationErrors": []
            }
        ]
        */
        /************* SUCCESFUL XERO API RESPONSE IN JSON  ******************/
        $response_parsed = json_decode($response, true);
        // YOU CAN CHECK THE RESPONSE, UPDATE DB IF NEEDED

       /*  if(is_array($response_parsed) && count($response_parsed) > 0)
         {
             foreach($response_parsed as $v)
            {
                if( !empty($v['ItemID']) && !empty($v['Code']) && empty($v['ValidationErrors']) )
                {
                     // update the ItemID to your DB
                    $str .=($v['ItemID'].' '.$v['Code'].' ');

                 } else {
                    // log somehere else
                 }
            }
        } */

        // DISPLAY THE RESPONSE
        return $response_parsed;
    }
    /********************************** UPDATE ITEM *************************************************/
    public function updateItem($credentials, $json = "") // Item in json
    {
        /* ITEM STRUCTURE
        {
            "Code": "10007",
            "Name": "CK MARYLAND FILLET STRIPS MEAT STRIPS",
            "IsSold":"true",
            "IsPurchase":"false",
            "Description": "CK MARYLAND FILLET STRIPS MEAT STRIPS SHREDDE D MEAT 5MM",
            "PurchaseDescription": "",
            "PurchaseDetails":
            {
                "UnitPrice":"",
                "AccountCode":"",
                "COGSAccountCode":"",
                "TaxType":""
            }
            "SalesDetails":
            {
                "UnitPrice":"10",
                "AccountCode":"",
                "COGSAccountCode":"",
                "TaxType":""
            },
            "IsTrackedAsInventory":"false",
            "InventoryAssetAccountCode":""
        }
        */

        // SAMPLE CODE
        /*
        $arr = [
            "ItemID" => "5560efb7-bfda-412e-b8a4-a5a34e972201", // from previous create items
            "Code" => "P-1003", // required
            "Name" => "Product 103-a"
        ];

        $json = json_encode($arr);
        */

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
                if(empty($data_arr['ItemID']) && empty($data_arr['Code'])) {
                    return json_encode([
                        'error' => true,
                        'origin' => 'local',
                        'response' => 'ItemID & Code required'
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
        // RESPONSE FROM XERO
        /*
        [
            {
                "Code": "P-1003",
                "Name": "Product 103-a",
                "IsSold": true,
                "IsPurchased": true,
                "Description": "Product 101 description",
                "PurchaseDetails": {
                    "UnitPrice": 20,
                    "TaxType": ""
                },
                "SalesDetails": {
                    "UnitPrice": 10,
                    "AccountCode": "200",
                    "TaxType": "TAX001"
                },
                "IsTrackedAsInventory": false,
                "UpdatedDateUTC": "\/Date(1648005999704)\/",
                "ItemID": "5560efb7-bfda-412e-b8a4-a5a34e972201",
                "ValidationErrors": []
            }
        ]
        */



        /************* SUCCESFUL XERO API RESPONSE IN JSON  ******************/
        $response_parsed = json_decode($response, true);
        // YOU CAN CHECK THE RESPONSE, UPDATE DB IF NEEDED

        // if(is_array($response_parsed) && count($response_parsed) > 0)
        // {
        //     foreach($response_parsed as $v)
        //     {
        //         if( !empty($v['ItemID']) && !empty($v['Code']) && empty($v['ValidationErrors']) )
        //         {
        //             // do something here
        //         } else {
        //             // log somehere else
        //         }
        //     }
        // }

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

        // SAMPLE CODE
        /*
        $arr = [
            [
                "Type" => "ACCREC",
                "Contact" => [
                    "ContactID" => "58905daa-1641-4dfc-bd78-6fb7ff4d0e9b" // from previous create contacts
                ],
                "LineItems" => [
                    [
                        "Code" =>  "P-1003",
                        "Quantity" => "3",
                        "UnitAmount" => "25" // override existing price
                    ]
                ]
            ]
        ];

        $json = json_encode($arr);
        */

        // valid data ?
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
        // RESPONSE FROM ZERO
        /*
        [
            {
                "Type": "ACCREC",
                "Contact": {
                    "ContactID": "58905daa-1641-4dfc-bd78-6fb7ff4d0e9b",
                    "ContactNumber": "12345",
                    "ContactStatus": "ACTIVE",
                    "Name": "Customer THree",
                    "FirstName": "First",
                    "EmailAddress": "customer_one@whls.wisenetware.com",
                    "ContactPersons": [
                        {
                            "FirstName": "First",
                            "EmailAddress": "customer_one@whls.wisenetware.com",
                            "IncludeInEmails": false
                        }
                    ],
                    "BankAccountDetails": "Commonwealth Bank ACC No. 319254",
                    "Addresses": [
                        {
                            "AddressType": "STREET",
                            "AddressLine1": "266-274 Derrimut Rd",
                            "AddressLine2": "Hoppers Crossing",
                            "City": "City of Wyndham",
                            "Region": "VIC",
                            "PostalCode": "AU",
                            "Country": ""
                        },
                        {
                            "AddressType": "POBOX",
                            "City": "",
                            "Region": "",
                            "PostalCode": "",
                            "Country": ""
                        }
                    ],
                    "Phones": [
                        {
                            "PhoneType": "DEFAULT",
                            "PhoneNumber": "123456789",
                            "PhoneAreaCode": "",
                            "PhoneCountryCode": ""
                        },
                        {
                            "PhoneType": "DDI",
                            "PhoneNumber": "",
                            "PhoneAreaCode": "",
                            "PhoneCountryCode": ""
                        },
                        {
                            "PhoneType": "FAX",
                            "PhoneNumber": "",
                            "PhoneAreaCode": "",
                            "PhoneCountryCode": ""
                        },
                        {
                            "PhoneType": "MOBILE",
                            "PhoneNumber": "",
                            "PhoneAreaCode": "",
                            "PhoneCountryCode": ""
                        }
                    ],
                    "IsSupplier": false,
                    "IsCustomer": true,
                    "SalesTrackingCategories": [],
                    "PurchasesTrackingCategories": [],
                    "UpdatedDateUTC": "\/Date(1648000246657+0000)\/",
                    "ContactGroups": [],
                    "BatchPayments": {
                        "BankAccountNumber": "Commonwealth Bank ACC No. 319254"
                    },
                    "HasAttachments": false,
                    "HasValidationErrors": false
                },
                "LineItems": [
                    {
                        "LineItemID": "98820860-bb94-4bec-a7d7-560d0abaf835",
                        "Quantity": 3,
                        "UnitAmount": 25,
                        "LineAmount": 75,
                        "Tracking": []
                    }
                ],
                "Date": "\/Date(1647993600000+0000)\/",
                "LineAmountTypes": "Exclusive",
                "InvoiceNumber": "INV-0004",
                "Reference": "",
                "BrandingThemeID": "f9f82b37-b18f-411c-81d6-b0836d006a3a",
                "CurrencyCode": "IDR",
                "CurrencyRate": 1,
                "Status": "DRAFT",
                "SentToContact": false,
                "SubTotal": 75,
                "TotalTax": 0,
                "Total": 75,
                "InvoiceID": "0fac3661-18b5-4656-b6b0-eb4eef0bf7b5",
                "HasAttachments": false,
                "IsDiscounted": false,
                "Prepayments": [],
                "Overpayments": [],
                "AmountDue": 75,
                "AmountPaid": 0,
                "UpdatedDateUTC": "\/Date(1648007043660+0000)\/",
                "HasErrors": false,
                "StatusAttributeString": "OK"
            }
        ]
        */

        /************* SUCCESFUL XERO API RESPONSE IN JSON  ******************/
        $response_parsed = json_decode($response, true);
        // YOU CAN CHECK THE RESPONSE, UPDATE DB IF NEEDED

        // if(is_array($response_parsed) && count($response_parsed) > 0)
        // {
        //     foreach($response_parsed as $v)
        //     {
        //         if( !empty($v['InvoiceID']) && !empty($v['InvoiceNumber']) && empty($v['HasErrors']) )
        //         {
        //             // update InvoiceID & InvoicenUmber
        //             // loop the LIneItems and get the LineItemID, which can be use for print invoice later
        //         } else {
        //             // log somehere else
        //         }
        //     }
        // }

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

        // SAMPLE CODE
        /*
        $arr = [
            "InvoiceID" => "0fac3661-18b5-4656-b6b0-eb4eef0bf7b5",
            "Type" => "ACCREC",
            "Contact" => [
                "ContactID" => "58905daa-1641-4dfc-bd78-6fb7ff4d0e9b" // from previous create contacts
            ],
            "LineItems" => [
                [
                    "LineItemID" => "98820860-bb94-4bec-a7d7-560d0abaf835",
                    "Quantity" => "5", // update qty
                    "Code" =>  "P-1003",
                    "UnitAmount" => "30" // update price
                ]
            ]
        ];
        */

        $json = json_encode($arr);

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
        // RESPONSE FROm XERO
        /*
        [
            {
                "Type": "ACCREC",
                "Contact": {
                    "ContactID": "58905daa-1641-4dfc-bd78-6fb7ff4d0e9b",
                    "ContactNumber": "12345",
                    "ContactStatus": "ACTIVE",
                    "Name": "Customer THree",
                    "FirstName": "First",
                    "EmailAddress": "customer_one@whls.wisenetware.com",
                    "ContactPersons": [
                        {
                            "FirstName": "First",
                            "EmailAddress": "customer_one@whls.wisenetware.com",
                            "IncludeInEmails": false
                        }
                    ],
                    "BankAccountDetails": "Commonwealth Bank ACC No. 319254",
                    "Addresses": [
                        {
                            "AddressType": "STREET",
                            "AddressLine1": "266-274 Derrimut Rd",
                            "AddressLine2": "Hoppers Crossing",
                            "City": "City of Wyndham",
                            "Region": "VIC",
                            "PostalCode": "AU",
                            "Country": ""
                        },
                        {
                            "AddressType": "POBOX",
                            "City": "",
                            "Region": "",
                            "PostalCode": "",
                            "Country": ""
                        }
                    ],
                    "Phones": [
                        {
                            "PhoneType": "DEFAULT",
                            "PhoneNumber": "123456789",
                            "PhoneAreaCode": "",
                            "PhoneCountryCode": ""
                        },
                        {
                            "PhoneType": "DDI",
                            "PhoneNumber": "",
                            "PhoneAreaCode": "",
                            "PhoneCountryCode": ""
                        },
                        {
                            "PhoneType": "FAX",
                            "PhoneNumber": "",
                            "PhoneAreaCode": "",
                            "PhoneCountryCode": ""
                        },
                        {
                            "PhoneType": "MOBILE",
                            "PhoneNumber": "",
                            "PhoneAreaCode": "",
                            "PhoneCountryCode": ""
                        }
                    ],
                    "IsSupplier": false,
                    "IsCustomer": true,
                    "SalesTrackingCategories": [],
                    "PurchasesTrackingCategories": [],
                    "UpdatedDateUTC": "\/Date(1648000246657+0000)\/",
                    "ContactGroups": [],
                    "BatchPayments": {
                        "BankAccountNumber": "Commonwealth Bank ACC No. 319254"
                    },
                    "HasAttachments": false,
                    "HasValidationErrors": false
                },
                "LineItems": [
                    {
                        "LineItemID": "98820860-bb94-4bec-a7d7-560d0abaf835",
                        "Quantity": 5,
                        "UnitAmount": 30,
                        "LineAmount": 150,
                        "Tracking": []
                    }
                ],
                "Date": "\/Date(1647993600000+0000)\/",
                "LineAmountTypes": "Exclusive",
                "InvoiceNumber": "INV-0004",
                "Reference": "",
                "BrandingThemeID": "f9f82b37-b18f-411c-81d6-b0836d006a3a",
                "CurrencyCode": "IDR",
                "CurrencyRate": 1,
                "Status": "DRAFT",
                "SentToContact": false,
                "SubTotal": 150,
                "TotalTax": 0,
                "Total": 150,
                "InvoiceID": "0fac3661-18b5-4656-b6b0-eb4eef0bf7b5",
                "HasAttachments": false,
                "IsDiscounted": false,
                "Prepayments": [],
                "Overpayments": [],
                "AmountDue": 150,
                "AmountPaid": 0,
                "UpdatedDateUTC": "\/Date(1648008476330+0000)\/",
                "HasErrors": false
            }
        ]
        */

        /************* SUCCESFUL XERO API RESPONSE IN JSON  ******************/
        $response_parsed = json_decode($response, true);
        // YOU CAN CHECK THE RESPONSE, UPDATE DB IF NEEDED


        // if(is_array($response_parsed) && count($response_parsed) > 0)
        // {
        //     foreach($response_parsed as $v)
        //     {
        //         if( !empty($v['InvoiceID']) && !empty($v['InvoiceNumber']) && empty($v['HasErrors']) )
        //         {
        //             // do something here
        //         } else {
        //             // log somehere else
        //         }
        //     }
        // }

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