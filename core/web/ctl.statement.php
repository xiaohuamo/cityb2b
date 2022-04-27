<?php

class ctl_statement extends cmsPage
{
    function ctl_statement()
    {

        parent::cmsPage();



        $act = $GLOBALS['gbl_act'];

        if ($act == 'customer_coupon_approving' || $act == 'customer_order_detail') {

        } else {

            if (! $this->loginUser) {
                $this->sheader(HTTP_ROOT_WWW.'member/login?returnUrl='.urlencode($_SERVER['REQUEST_URI']));
            }
        }


    }

    /*
     生成商家的客户的statement
     * */
    public function generate_statement_action()
    {

        $mdl_statement_list = $this->loadModel('statement_list');
        $mdl_statement = $this->loadModel('statement');


        // 获得登陆用户管理的 供应商商家
        $mdl_user_factory = $this->loadModel('user_factory');
        $factoryId = $mdl_user_factory->getFactoryId($this->loginUser['id']);

        // var_dump('here');exit;

        /*
        生成statement 的处理过程
        1） 获得当前statement 的 年度星期 如 202138 代表2021年第38周。 检测当前客户是否已经有该年度星期值的数据，如果已经存在，则不再生成。
        2） 从 statement 表中，拿出所有 未 settle的数据， 开始生成statement_list 的数据表信息。包括这次statment 包含的statement表格的数据id
        3) 将statement 的 生成信息写入数据库。
        */





        // 获得当前供应商需要生成statement 的客户列表
        $needToProcessCustomerList = $mdl_statement_list->getNeedToProcessStatementCustomerList($factoryId);

        //依次生成statement

     //   var_dump($needToProcessCustomerList);exit;

        foreach ($needToProcessCustomerList as $key => $value) {


            // lock record this customer data

            // get opening balance
           $openBalance = $mdl_statement_list->getCustomerOpeningBalance($factoryId,$value['customer_id']);

        //  var_dump($openBalance);exit;
            // get closeing balance

            $closeBalance = $mdl_statement->getCustomerCloseingBalanceAndData($factoryId,$value['customer_id']);
            //  label all statment detail is process

          //  var_dump($closeBalance);exit;

            $statementData = $mdl_statement->getStatementData($factoryId,$value['customer_id'],$this->loginUser['id'],$openBalance,$closeBalance);
//var_dump($statementData);exit;
            $mdl_statement_list->insert($statementData);


        }
        $this->setData('statement_list', 'submenu');
        $this->setData('account_management', 'menu');
        $this->display('statement/statement_list');

    }

    public function statement_view_action() {

        $id =  get2('id');
        $currentStatementRec = $this->loadModel('statement_list')->get($id);




        if($currentStatementRec && $currentStatementRec['statement_ids']) {
            //var_dump($currentStatementRec['statement_ids']);exit;
           $statement_details_data_not_over_due = $this->loadModel('statement')->getStatementDetailsNotOverDue($currentStatementRec['statement_ids'],$currentStatementRec['gen_date']);
           $statement_details_data_over_due = $this->loadModel('statement')->getStatementDetailsOverDue($currentStatementRec['statement_ids'],$currentStatementRec['gen_date']);

        }else{
            $this->form_response_msg('Could not find the record!');
        }


        $orderId = get2('order_id');
        $mel_user = $this->loadModel('user');
        $mdl_abn_application = $this->loadModel('wj_abn_application');
        $mdl_user_account_info = $this->loadModel('user_account_info');

        $order = $this->loadModel('order')->getByOrderId($orderId);
        $items = $this->loadModel('wj_customer_coupon')->getItemsInOrder_menu($orderId, $this->loginUser['id']);

        $user =$mel_user->getUserById($order['userId']);
        $userWhere = [
            'userId' => $order['userId'],
        ];
        $userABN = $mdl_abn_application->getByWhere($userWhere);
//var_dump($order['userId']);exit;

        $factory = $mel_user->getUserById($this->loginUser['id']);
        $factoryWhere = [
            'userId' => $this->loginUser['id'],
        ];
        $factoryAccount = $mdl_user_account_info->getByWhere($factoryWhere);
        $factoryABN = $mdl_abn_application->getByWhere($factoryWhere);

        // 获得该用户的简称
        $mdl_user_factory =$this->loadModel("user_factory");
        $user_code_rec =$mdl_user_factory->getByWhere(array('user_id'=>$order['userId'],'factory_id'=>$this->loginUser['id']));
        //var_dump($user_code_rec);exit;

        $this->loadModel('statement_output');
        $report = new customer_statement($currentStatementRec,$statement_details_data_over_due,$statement_details_data_not_over_due);


        if($this->loginUser['logo']) {
            $report->logoPath('data/upload/' . $this->loginUser['logo']);
        }


        $report->setUser_Code($user_code_rec);
        $report->setUser($user, $userABN);
        $report->setFactory($factory, $factoryABN, $factoryAccount);
        $report->setStatementDetailsData($statement_details_data);
        $report->setStatementData($currentStatementRec);


        $report->title('Statement');



        $report->generatePDF($this->lang);


        $report->outPutToBrowser('statement.pdf');
        $filePath = date('Y-m');
        $this->file->createdir('data/statement/'.$filePath);

         if($filePath) {//如果是系统内部调用会直接在指定路径创建文件
             $report->outPutToFile('data/statement/'.$filePath.'/'.'statement.pdf');
             return $filePath;
         }
       // $report->outPutToFile('data/statement/'.$filePath.'/'.$order['userId'].'-'.$currentStatementRec['id'].".pdf","F");
        exit;


    }


    public function transcation_list_action() {

        $mdl_statement = $this->loadModel('statement');
        $mdl_user_factory = $this->loadModel('user_factory');

        $search = trim(get2('search'));

        $customer_id=get2('customer_id');
        $factoryId =  $mdl_user_factory->getFactoryId($this->loginUser['id']);
        // var_dump($factoryId);exit;


        $data = $mdl_statement->getStatementTranscations($factoryId, $customer_id,$search);
        //var_dump($data);exit;

        foreach ($data as $key => $value) {



        }

        $this->setData($search, 'search');
        $this->setData($data, 'data');

        $this->setData('statement_list', 'submenu');
        $this->setData('account_management', 'menu');
        $this->display('statement/transcation_list');
    }

    public function statement_list_action() {

        $mdl_statement_list = $this->loadModel('statement_list');
        $mdl_user_factory = $this->loadModel('user_factory');

        $search = trim(get2('search'));


        $factoryId =  $mdl_user_factory->getFactoryId($this->loginUser['id']);
       // var_dump($factoryId);exit;


        $data = $mdl_statement_list->getStatementList($factoryId, $search);
       // var_dump($data);exit;

        foreach ($data as $key => $value) {



        }

        $this->setData($search, 'search');
        $this->setData($data, 'data');

        $this->setData('statement_list', 'submenu');
        $this->setData('account_management', 'menu');
        $this->display('statement/statement_list');
    }


}