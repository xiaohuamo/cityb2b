<?php

/*
 @ctl_name = 数据统计@
*/

class ctl_evaluate extends adminPage
{
    public function index_action () #act_name = 列表#
    {
        $title = get2('title');
        if ($title=='NULL')
        {
            $where = " where a.user_nickname IS NULL ";
        }
        else
        {
            $where = " where a.user_nickname like '%$title%' or a.business_name like '%$title%'";
        }
        $pageSql	= 'SELECT a.id, a.userId,a.user_nickname,a.score_avg,a.description,a.createTime,a.business_name,a.isApproved FROM cc_wj_customer_rating a '.$where.' order by createTime DESC';
        $pageUrl	= $this->parseUrl()->set( 'page' );
        $pageSize	= 10;
        $maxPage	= 10;
        $page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
        $evaluation		= $this->loadModel('wj_customer_rating')->getListBySql($page['outSql']);
        $user=$this->loadModel('user');
        foreach ($evaluation as $key => $value) {
            $evaluation[$key]['displayname']=$user->getDisplayName($value['userId']);
        }
        $this->setData($evaluation,'evaluation');
        $this->setData($page['pageStr'], 'pager');
        $this->display();
    }

    public function getEvaluate($where){
        $sql='';
        $result= $this->getListBySql($sql);
        return 	 $result;
    }

    public function examine_action() #act_name = 审核#
    {
        $data = array(
            'isApproved' => get2('isApproved')
        );
        $where['id']=get2('id');
        $this->loadModel('wj_customer_rating')->updateByWhere($data, $where);
        $this->sheader('?con=admin&ctl=adv/evaluate&act=index');
    }
}

?>