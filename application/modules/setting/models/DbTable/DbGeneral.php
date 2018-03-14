<?php

class Setting_Model_DbTable_DbGeneral extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_setting';
    
    public function geLabelByKeyName($keyName){
    	$db = $this->getAdapter();
    	$sql = " SELECT s.`code`,s.keyName,s.keyValue 
				FROM `rms_setting` AS s
				WHERE s.status=1 
				AND s.`keyName` ='$keyName' LIMIT 1";
    	return $db->fetchRow($sql);
    }
	public function updateWebsitesetting($data){
		try{
			
			$arr = array('keyValue'=>$data['label_animation'],);
			$where=" keyName= 'label_animation'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['smsWarnningKH'],);
			$where=" keyName= 'sms-warnning-kh'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['reciept_kh'],);
			$where=" keyName= 'reciept_kh'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['exchange_ratetitle'],);
			$where=" keyName= 'exchange_ratetitle'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['exchange_reciept'],);
			$where=" keyName= 'exchange_reciept'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['comment'],);
			$where=" keyName= 'comment'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['brand_client'],);
			$where=" keyName= 'brand_client'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['brand_holiday'],);
			$where=" keyName= 'brand_holiday'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['brand_call'],);
			$where=" keyName= 'brand_call'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['rptTransferTitleKh'],);
			$where=" keyName= 'rpt-transfer-title-kh'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['branchAddClient'],);
			$where=" keyName= 'branch-add-client'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['telClient'],);
			$where=" keyName= 'tel-client'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['client_website'],);
			$where=" keyName= 'client_website'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['email_client'],	);
			$where=" keyName= 'email_client'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['power_by'],	);
			$where=" keyName= 'power_by'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['branchTel'],	);
			$where=" keyName= 'branch-tel'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['branch_add'],	);
			$where=" keyName= 'branch_add'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['branch_email'],	);
			$where=" keyName= 'branch_email'";
			$this->update($arr, $where);
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
}

