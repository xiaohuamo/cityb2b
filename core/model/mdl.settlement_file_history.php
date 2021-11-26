<?php

class mdl_settlement_file_history extends mdl_base
{
    protected $tableName = '#@_settlement_file_history';

    protected $filePath = 'data/settlement/';

    private $listField = 'id, business_id, file_path, createTime, startDate, endDate';

    public function get($id)
    {
        return $this->db->selectOne($this->listField, $this->tableName, "id='$id'");
    }

    public function getUserSettlementHistory($userId, $startDate = '', $endDate = '') {
        $startTime = empty($startDate) ? strtotime('2020-01-01') : strtotime($startDate);
        if(empty($endDate)) {
            $endTime = strtotime(date('Y-m-d')) + 60 * 60 * 24;
        } else {
            $endTime = strtotime($endDate) + 60 * 60 * 24;
        }

        $sql = "SELECT $this->listField 
                FROM $this->tableName 
                WHERE business_id = $userId
                AND createTime >= $startTime
                AND createTime <= $endTime";

        return $this->getListBySql($sql);
    }

    public function generateFileDirectoryPath($businessId)
    {
        return $this->filePath.$businessId.'/'.date('Y').'/';
    }

    public function generateFilePath($businessId, $settlementType, $fileType = 'pdf')
    {
        return self::generateFileDirectoryPath($businessId)."$settlementType-".time().'.'.$fileType;
    }

    public function createHistoryFile($businessId, $filePath, $startDate, $endDate)
    {
        $data = [
            'business_id' => $businessId,
            'file_path' => $filePath,
            'createTime' => time(),
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];

        return $this->insert($data);
    }
}

?>