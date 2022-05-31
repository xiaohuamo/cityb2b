<?php
             $mdl_autorun_data =$this->loadModel('autorun_data');
             $result =$mdl_autorun_data->getListBySql('select * from cc_autorun_data where status=0');
              foreach ($result as $key=>$value){
                  if($value['data_type']==100){
                      $this->auto_send_invoice_to_xero($value['ref_id'],$value['ref_value1'],'update');
                  }
              }
              $where =array(
                  'status'=>0
              );
              $mdl_autorun_data->updateByWhere(array('status'=>1),$where);
          