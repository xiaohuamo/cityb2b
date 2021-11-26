<?php

class mdl_order_print_log extends mdl_base
{
    protected $tableName = '#@_order_print_log';

    public function addRecord($orderId, $userId)
    {
        $data['orderId'] = $orderId;
        $data['userId'] = $userId;
        $data['print_at'] = time();

        return $this->insert($data);
    }

    public function getRecordList($centreId, $conditions = [])
    {
        $suppliersList = loadModel('dispatching_centre_customer_list')->getDispatchingCentreCustomerList($centreId);
        $supplierIds = implode(',', array_column($suppliersList, 'business_id'));

        $sql = "SELECT * FROM `cc_order_print_log`
                LEFT JOIN `cc_order` ON `cc_order`.`orderId` = `cc_order_print_log`.`orderId`
                WHERE `cc_order`.`business_userId` in ($supplierIds)";

        if (count($conditions) > 0) {
            foreach ($conditions as $key => $value) {
                if ($key == 'print_at') {
                    $startTime = strtotime($value);
                    $endTime = strtotime($value) + 60 * 60 * 24;

                    $conditionSql = "`cc_order_print_log`.`$key` BETWEEN $startTime AND $endTime";
                } else {

                    $conditionSql = "`cc_order_print_log`.`$key` = $value";
                }

                $sql .= " AND $conditionSql";
            }
        }

        return $this->getListBySql($sql);
    }
}

?>