<?php

/*
 @ctl_name = 页面产品管理@
*/

class ctl_moduleset extends adminPage
{
    public function index_action () #act_name = 列表#
	{
        $pagetype = (int)get2('pagetype');
        $panaltype = (int)get2('panaltype');
        $title = get2('title');
        $this->setData($pagetype,'pagetypeno');
        $this->setData($panaltype,'panaltypeno');
        $this->setData($title,'title');
        $mdl_explosion	= $this->loadModel('explosion');

        if ($title=='NULL')
        {
            $where = " where c.title IS NULL ";
        }
        else
        {
            $where = "WHERE c.title like '%$title%' and e.pagetype=$pagetype AND e.panaltype=$panaltype";
        }

        $sql="SELECT e.id,c.title,e.couponid,e.sort,pt.pagename,pnt.name FROM cc_explosion e LEFT JOIN cc_coupons c ON (c.id=e.couponid or c.createUserId=e.couponid ) LEFT JOIN cc_user u ON u.id=c.createUserId LEFT JOIN cc_pagetype AS pt ON pt.id=e.pagetype LEFT JOIN cc_panaltype AS pnt ON pnt.id=e.panaltype  $where  ORDER BY panaltype";

        $this->setData($mdl_explosion->getListBySql($sql), 'data');

        $mdl_pagetype	= $this->loadModel('pagetype');
        $this->setData($mdl_pagetype->getListBySql("select * from cc_pagetype"), 'pagetype');

        $mdl_panaltype	= $this->loadModel('panaltype');
        $this->setData($mdl_panaltype->getListBySql("SELECT * FROM cc_panaltype WHERE pagetype=1"), 'panaltype');

        $this->display();
	}

    public function add_action () #act_name = 创建#
    {
        $this->setData('1','pageTypeno');
        $this->setData('1','panalTypeno');
        $mdl_panaltype	= $this->loadModel('panaltype');
        $this->setData($mdl_panaltype->getListBySql("SELECT * FROM cc_panaltype WHERE pagetype=1"), 'panalType');

        $mdl_pagetype	= $this->loadModel('pagetype');
        $this->setData($mdl_pagetype->getListBySql("SELECT * FROM cc_pagetype"), 'pagetype');

        if (is_post())
        {
            $data	= post('data');

            $sql = "INSERT INTO cc_explosion (type,couponid,sort,pageType,panalType,alias,lang) VALUES ('".$data['type']."','".$data['couponid']."','".$data['sort']."','".$data['pageType']."','".$data['panalType']."','".$data['alias']."','".$data['lang']."')";

            if($this->db->query($sql))
            {
                $this->setData($data['pageType'],'pageTypeno');
                $this->setData($data['panalType'],'panalTypeno');

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
        $mdl_explosion	= $this->loadModel('explosion');
        $sql="SELECT e.id,c.title,e.couponid,e.sort,e.type,e.panaltype,e.pagetype,e.alias FROM cc_explosion e LEFT JOIN cc_coupons c ON c.id=e.couponid LEFT JOIN cc_user u ON u.id=c.createUserId WHERE e.id=$id ORDER BY panaltype" ;
        $data			= $mdl_explosion->getListBySql($sql)[0];
        $mdl_panaltype	= $this->loadModel('panaltype');
        $this->setData($mdl_panaltype->getListBySql("SELECT * FROM cc_panaltype WHERE pagetype=1"), 'panaltype');
        $mdl_pagetype	= $this->loadModel('pagetype');
        $this->setData($mdl_pagetype->getListBySql("SELECT * FROM cc_pagetype"), 'pagetype');

        if (!$data)
        {
            $this->sheader(null, '记录不存在');
        }
        if (is_post())
        {
            $data	= post('data');
            if ($mdl_explosion->update($data, $id))
            {
                $this->sheader("?con=admin&ctl=adv/moduleset&act=index&pagetype=".$data['pageType']."&panaltype=".$data['panaltype']."");
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
        $pagetype = (int)get2('pagetype');
        $panaltype = (int)get2('panaltype');
        $title = get2('title');
        $this->loadModel('explosion')->delete(get2('id'));
        $this->sheader('?con=admin&ctl=adv/moduleset&act=index&pagetype='.$pagetype.'&panaltype='.$panaltype.'&title='.$title.'');
    }

    public function pagetype_action () #act_name = 页面类别联动#
    {
        $pageType	= (int)get2('pageType');
        $mdl_pagetype = $this->loadModel('pagetype')->getListBySql("SELECT * FROM cc_panaltype WHERE pagetype=$pageType");
        for ($i = 0; $i < sizeof($mdl_pagetype); $i++) {
            $list[$i]=array("id"=>$mdl_pagetype[$i][id],"name"=>$mdl_pagetype[$i][name]);
        }
        echo json_encode($list);
    }
}

?>