<?php
class Report_Model_DbTable_DbStaffSalary extends Zend_Db_Table_Abstract
{
    protected  $db_name='ln_client';
    public function getAllLnClient(){
    	 $db = $this->getAdapter();
          $sql="SELECT client_id,client_number,name_kh,name_en,sex,village_id,street,house,phone,remark FROM ln_client ORDER BY client_id";
          return $db->fetchAll($sql);
    }
}

