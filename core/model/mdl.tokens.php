<?php

class mdl_tokens extends mdl_base
{

	protected $tableName = '#@_tokens';


  public function getCredentials($business_id,$type) {
      $where =array(
          'business_id'=>$business_id
      );
      $rec =$this->getByWhere($where) ;
      if($rec){
          $credentials1 =array(
              'username' =>$rec['username'],
              'password' =>$rec['password'],
              'client_id' => $rec['client_id'],
              'client_secret' => $rec['client_secret'],

              'endpoint_uri' => "https://api.whls.wisenetware.com"
          );
      }
    //  var_dump($rec);exit;
      return $credentials1;


}


    }

?>