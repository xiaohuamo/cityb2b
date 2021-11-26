<?php

/*
 @ctl_name = 免佣金产品@
*/

class ctl_commission_free extends adminPage
{
    public function index_action() #act_name = 列表#
    {
        $keyword = get2('keyword');
        $onlyCommissionFree = get2('onlyCommissionFree');
        $search = [
            'keyword' => $keyword,
            'onlyCommissionFree' => $onlyCommissionFree,
        ];

        $mdl_restaurant_menu = $this->loadModel('restaurant_menu');

        $where = [];
        $where[] = " (onSpecial=1) ";
        $where[] = " (id='$keyword' or menu_cn_name like '%$keyword%' or menu_en_name like '%$keyword%') ";
        if ($onlyCommissionFree == 1) {
            $where[] = " (commission_free=1) ";
        }

        $pageSql = $mdl_restaurant_menu->getAllRestaurantMenuListSql($where);
        $pageUrl = $this->parseUrl()->set('page');
        $pageSize = 15;
        $maxPage = 5;

        $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
        $data = $this->_out($mdl_restaurant_menu->getAllRestaurantMenuListBySql($page['outSql']));

        $this->setData($data);

        $this->setData($page['pageStr'], 'pager');
        $this->setData($search, 'search');

        $this->setData($this->parseUrl(), 'listUrl');
        $this->setData($this->parseUrl()->set('type')->set('keyword')->set('onlyNotApproved'), 'searchUrl');
        $this->display();
    }

    public function edit_action() #act_name = 移除#
    {
        $mdl_restaurant_menu = $this->loadModel('restaurant_menu');
        $id = get2('id');
        $commissionFree = get2('commission_free');
        $mdl_restaurant_menu->updateByWhere(['commission_free' => $commissionFree], ['id' => $id]);
        $this->sheader($this->parseUrl()->set('act'));
    }

    private function _out($data)
    {
        $mdl_city = $this->loadModel('city');
        foreach ($data as $key => $value) {
            if ($value['lastLoginDate']) {
                $data[$key]['lastLoginDate'] = date('Y-m-d H:i', $value['lastLoginDate']);
            } else {
                $data[$key]['lastLoginDate'] = '-';
            }
            $data[$key]['createdDate'] = date('Y-m-d H:i', $value['createdDate']);
            $data[$key]['lastModifiedDate'] = date('Y-m-d H:i', $value['lastModifiedDate']);
            $data[$key]['lastPasswordChangedDate'] = date('Y-m-d H:i', $value['lastPasswordChangedDate']);
        }

        return $data;
    }
}

?>