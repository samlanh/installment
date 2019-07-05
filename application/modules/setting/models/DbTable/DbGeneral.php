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
			$dbg = new Application_Model_DbTable_DbGlobal();
			
			$arr = array('keyValue'=>$data['label_animation'],);
			$where=" keyName= 'label_animation'";
			$this->update($arr, $where);
			
// 			$arr = array('keyValue'=>$data['smsWarnningKH'],);
// 			$where=" keyName= 'sms-warnning-kh'";
// 			$this->update($arr, $where);
			
// 			$arr = array('keyValue'=>$data['reciept_kh'],);
// 			$where=" keyName= 'reciept_kh'";
// 			$this->update($arr, $where);
			
// 			$arr = array('keyValue'=>$data['exchange_ratetitle'],);
// 			$where=" keyName= 'exchange_ratetitle'";
// 			$this->update($arr, $where);
			
// 			$arr = array('keyValue'=>$data['exchange_reciept'],);
// 			$where=" keyName= 'exchange_reciept'";
// 			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['comment'],);
			$where=" keyName= 'comment'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['comment1'],);
			$where=" keyName= 'comment1'";
			$this->update($arr, $where);
			
// 			$arr = array('keyValue'=>$data['brand_client'],);
// 			$where=" keyName= 'brand_client'";
// 			$this->update($arr, $where);
			
// 			$arr = array('keyValue'=>$data['brand_holiday'],);
// 			$where=" keyName= 'brand_holiday'";
// 			$this->update($arr, $where);
			
// 			$arr = array('keyValue'=>$data['brand_call'],);
// 			$where=" keyName= 'brand_call'";
// 			$this->update($arr, $where);
			
// 			$arr = array('keyValue'=>$data['rptTransferTitleKh'],);
// 			$where=" keyName= 'rpt-transfer-title-kh'";
// 			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['footer_branch'],);
			$where=" keyName= 'footer_branch'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['telClient'],);
			$where=" keyName= 'tel-client'";
			$this->update($arr, $where);
			
// 			$arr = array('keyValue'=>$data['client_website'],);
// 			$where=" keyName= 'client_website'";
// 			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['email_client'],	);
			$where=" keyName= 'email_client'";
			$this->update($arr, $where);
			
// 			$arr = array('keyValue'=>$data['power_by'],	);
// 			$where=" keyName= 'power_by'";
// 			$this->update($arr, $where);
			
// 			$arr = array('keyValue'=>$data['branchTel'],	);
// 			$where=" keyName= 'branch-tel'";
// 			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['website'],	);
			$where=" keyName= 'website'";
			$this->update($arr, $where);
			
// 			$arr = array('keyValue'=>$data['branch_add'],	);
// 			$where=" keyName= 'branch_add'";
// 			$this->update($arr, $where);
			
// 			$arr = array('keyValue'=>$data['branch_email'],	);
// 			$where=" keyName= 'branch_email'";
// 			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['account_sign'],	);
			$where=" keyName= 'account_sign'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['customer_sign'],	);
			$where=" keyName= 'customer_sign'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['teller_sign'],	);
			$where=" keyName= 'teller_sign'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['customer_sign'],);
			$where=" keyName= 'customer_sign'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['bank_info'],);
			$where=" keyName= 'bank_info'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['show_propertyprice'],);
			$where=" keyName= 'show_propertyprice'";
			$this->update($arr, $where);
			$arr = array('keyValue'=>$data['bank_account1'],);
			$where=" keyName= 'bank_account1'";
			$this->update($arr, $where);
			$arr = array('keyValue'=>$data['bank_account1number'],);
			$where=" keyName= 'bank_account1number'";
			$this->update($arr, $where);
			$arr = array('keyValue'=>$data['bank_account2'],);
			$where=" keyName= 'bank_account2'";
			$this->update($arr, $where);
			$arr = array('keyValue'=>$data['bank_account2number'],);
			$where=" keyName= 'bank_account2number'";
			$this->update($arr, $where);
			$arr = array('keyValue'=>$data['cheque_receiver'],);
			$where=" keyName= 'cheque_receiver'";
			$this->update($arr, $where);
			$arr = array('keyValue'=>$data['showhouseinfo'],);
			$where=" keyName= 'showhouseinfo'";
			$this->update($arr, $where);
			
			$rows = $this->geLabelByKeyName('penalty_type');
			if (empty($rows)){
				$arr = array('keyValue'=>$data['penalty_type'],'keyName'=>'penalty_type','note'=>"1=ភាគរយ, 2=សាច់ប្រាក់",'user_id'=>$dbg->getUserId());
				$this->insert($arr);
			}else{
				$arr = array('keyValue'=>$data['penalty_type'],);
				$where=" keyName= 'penalty_type'";
				$this->update($arr, $where);
			}
			
			$rows = $this->geLabelByKeyName('penalty_value');
			if (empty($rows)){
				$arr = array('keyValue'=>$data['penalty_value'],'keyName'=>"penalty_value",'user_id'=>$dbg->getUserId());
				$this->insert($arr);
			}else{
				$arr = array('keyValue'=>$data['penalty_value'],);
				$where=" keyName= 'penalty_value'";
				$this->update($arr, $where);
			}
			
			$rows = $this->geLabelByKeyName('graice_pariod_late');
			if (empty($rows)){
				$arr = array('keyValue'=>$data['graice_pariod_late'],'keyName'=>'graice_pariod_late','note'=>"ចំនួនថ្ងៃអនុគ្រោះបង់យឺត",'user_id'=>$dbg->getUserId());
				$this->insert($arr);
			}else{
				$arr = array('keyValue'=>$data['graice_pariod_late'],);
				$where=" keyName= 'graice_pariod_late'";
				$this->update($arr, $where);
			}
			
			$rows = $this->geLabelByKeyName('agree_day_alert');
			if (empty($rows)){
				$arr = array('keyValue'=>$data['agree_day_alert'],'keyName'=>'agree_day_alert','note'=>"ចំនួនថ្ងៃដែលត្រូវ Alert កិច្ចសន្យាមុន",'user_id'=>$dbg->getUserId());
				$this->insert($arr);
			}else{
				$arr = array('keyValue'=>$data['agree_day_alert'],);
				$where=" keyName= 'agree_day_alert'";
				$this->update($arr, $where);
			}
			
			$rows = $this->geLabelByKeyName('payment_day_alert');
			if (empty($rows)){
				$arr = array('keyValue'=>$data['payment_day_alert'],'keyName'=>'payment_day_alert','note'=>"ចំនួនថ្ងៃដែលត្រូវ  Alert ថ្ងៃបង់លុយមុន",'user_id'=>$dbg->getUserId());
				$this->insert($arr);
			}else{
				$arr = array('keyValue'=>$data['payment_day_alert'],);
				$where=" keyName= 'payment_day_alert'";
				$this->update($arr, $where);
			}
			
			$rows = $this->geLabelByKeyName('signatur_agree');
			if (empty($rows)){
				$arr = array('keyValue'=>$data['signatur_agree'],'keyName'=>"signatur_agree",'user_id'=>$dbg->getUserId());
				$this->insert($arr);
			}else{
				$arr = array('keyValue'=>$data['signatur_agree'],);
				$where=" keyName= 'signatur_agree'";
				$this->update($arr, $where);
			}
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
}

