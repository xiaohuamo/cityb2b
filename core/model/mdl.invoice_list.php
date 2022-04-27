<?php

class mdl_invoice_list extends mdl_base
{

	protected $tableName = '#@_invoice_list';


    public function insertOrUpdate($data){
     //check if has record or not
        $where =array(
          'factory_id'=>$data['factory_id'],
            'invoiceId'=>$data['invoiceId'],
            'type'=>$data['type']
        );
        $rec =$this->getByWhere($where);
        if($rec){
            $dataupdate =array(

                'gendate'=>time(),
                'createUserId'=>$data['createUserId'],
                'amount'=>$data['amount'],
            );

            $this->update($dataupdate,$rec['id']);

        }else{
            $this->insert($data);

        }

    }
}

?>