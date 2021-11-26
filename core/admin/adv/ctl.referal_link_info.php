<?php

/*
 @ctl_name = 页面产品管理@
*/

class ctl_referal_link_info extends adminPage
{
    public function index_action () #act_name = 列表#
	{
        $type = get2('type');
        //var_dump($type);
        $title = get2('title');
        $this->setData($type,'type');
  
        $this->setData($title,'title');
        $mdl_referal_link_info	= $this->loadModel('referal_link_info');
		
		
		
		  $mdl_referal_link_type	= $this->loadModel('referal_link_type');
		  $sql_type ="select * from cc_referal_link_type order by id desc";
		  
        
		 $referal_type_list =$mdl_referal_link_info->getListBySql($sql_type);
		
         $this->setData($referal_type_list, 'referal_type_list');


        if (!empty($title)){
            $where = "WHERE a.referal_type like '%$title%'  or a.referal_port_name like '%$title%' or b.type like '%$title%' ";
        }
		
		 if (!empty($type) && $type !='all'){
			   if (!empty($title)){ 
			   $where .= "and  a.referal_type like '%$type%'   ";
			   }else{
				   
				   $where .= "WHERE a.referal_type like '%$type%'  ";
			   }
            
        }

        $sql="SELECT a.* from cc_referal_link_info a left join cc_referal_link_type b on a.referal_type =b.id   $where  ORDER BY a.referal_type desc";
        //var_dump($sql);exit;
		
		$data = $mdl_referal_link_info->getListBySql($sql);
		$mdl_details =$this->loadModel("referal_link_click_details");
		
		foreach ($data as $key => $value) {
			//var_dump($value['type']);
			$type_rec =$mdl_referal_link_type->get($value['referal_type']);
			$data[$key]['type_name'] = $type_rec['type'];
			
			$time_14day = time()-14*24*60*60;
			$time_7day = time()-7*24*60*60;
			$time_1day = time()-24*60*60;
			$time_1hour = time()-60*60;
			$time_12hours = time()-12*60*60;
			$id =$value['id'];
			$sql = " select ( select count(*) as count_14day from   cc_referal_link_click_details where   referal_id =$id and  createtime>$time_14day ) as count_14day ,( select count(*) as count_7day from   cc_referal_link_click_details where   referal_id =$id and  createtime>$time_7day ) as count_7day ,( select count(*) as count_1day from   cc_referal_link_click_details where   referal_id =$id and  createtime>$time_1day ) as count_1day ,(select count(*) as count_1hour from   cc_referal_link_click_details where   referal_id =$id and  createtime>$time_1hour ) as count_1hour ,( select count(*) as count_12hours from   cc_referal_link_click_details where   referal_id =$id and  createtime>$time_12hours ) as count_12hours ";
			$rec_details =$mdl_details->getListBySql($sql);
			//var_dump($sql);exit;
			$data[$key]['count_1hour'] =$rec_details[0]['count_1hour'];
			$data[$key]['count_12hours'] =$rec_details[0]['count_12hours'];
			$data[$key]['count_1day'] =$rec_details[0]['count_1day'];
			$data[$key]['count_7day'] =$rec_details[0]['count_7day'];
			$data[$key]['count_14day'] =$rec_details[0]['count_14day'];
			
		}
		
        $this->setData($data, 'data');

        $this->display();
	}

    public function add_action () #act_name = 创建#
    {
       
        $mdl_referal_link_type	= $this->loadModel('referal_link_type');
		  $sql_type ="select * from cc_referal_link_type order by id desc";
		  
        
		 $referal_type_list =$mdl_referal_link_type->getListBySql($sql_type);
		
         $this->setData($referal_type_list, 'referal_type_list');


       

        if (is_post())
        {
            $data	= post('data');

            $sql = "INSERT INTO cc_referal_link_info (referal_type,referal_port_name,total_click) VALUES ('".$data['referal_type']."','".$data['referal_port_name']."','".$data['total_click']."')";

            if($this->db->query($sql))
            {
                
                $this->display();
            }
            else
            {
                $this->sheader(null, $this->lang->add_category_failed);
            }
        }
        else
        {
            $this->display();
        }
    }
	
	
	

    public function edit_action () #act_name = 编辑#
    {
        $id				= (int)get2('id');
        $mdl_referal_link_info	= $this->loadModel('referal_link_info');
       
        $data			= $mdl_referal_link_info->get($id);
        $mdl_referal_link_type	= $this->loadModel('referal_link_type');
		  $sql_type ="select * from cc_referal_link_type order by id desc";
		  
        
		 $referal_type_list =$mdl_referal_link_type->getListBySql($sql_type);
		
         $this->setData($referal_type_list, 'referal_type_list');

        if (!$data)
        {
            $this->sheader(null, '记录不存在');
        }
        if (is_post())
        {
            $data	= post('data');
            if ($mdl_referal_link_info->update($data, $id))
            {
                $this->sheader("?con=admin&ctl=adv/referal_link_info&act=index&pagetype=".$data['referal_type']."");
            }
            else
            {
                $this->sheader(null, '修改产品失败');
            }
        }
        else
        {
            $this->setData($data, 'data');
            $this->display();
        }
    }

    public function delete_action () #act_name = 删除#
    {
     
        $this->loadModel('referal_link_info')->delete(get2('id'));
        $this->sheader('?con=admin&ctl=adv/referal_link_info&act=index&title='.$title.'');
    }

   
}

?>