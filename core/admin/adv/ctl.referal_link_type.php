<?php

/*
 @ctl_name = 页面产品管理@
*/

class ctl_referal_link_type extends adminPage
{
    public function index_action () #act_name = 列表#
	{
        
        $title = get2('title');
      
        $this->setData($title,'title');
        $mdl_referal_link_type	= $this->loadModel('referal_link_type');

        if (!empty($title)){
            $where = "WHERE type like '%$title%'   ";
        }
		
		

        $sql="SELECT * from cc_referal_link_type  $where  ORDER BY type";
        //var_dump($sql);exit;
        $this->setData($mdl_referal_link_type->getListBySql($sql), 'data');

         $sql ="select id,type from cc_referal_link_type group by type ";
		 $referal_type_list =$mdl_referal_link_type->getListBySql($sql);
		 
         $this->setData($referal_type_list, 'referal_type_list');

        $this->display();
	}

    public function add_action () #act_name = 创建#
    {
       
       

       

        if (is_post())
        {
            $data	= post('data');

            $sql = "INSERT INTO cc_referal_link_type (type) VALUES ('".$data['type']."')";

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
        $mdl_referal_link_type	= $this->loadModel('referal_link_type');
       
        $data			= $mdl_referal_link_type->get($id);
       

        if (!$data)
        {
            $this->sheader(null, '记录不存在');
        }
        if (is_post())
        {
            $data	= post('data');
            if ($mdl_referal_link_type->update($data, $id))
            {
                $this->sheader("?con=admin&ctl=adv/referal_link_type&act=index");
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
     
        $this->loadModel('referal_link_type')->delete(get2('id'));
        $this->sheader('?con=admin&ctl=adv/referal_link_type&act=index&title='.$title.'');
    }

   
}

?>