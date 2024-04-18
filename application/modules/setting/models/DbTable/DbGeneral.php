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
			
			$showPropertyprice = empty($data['show_propertyprice'])?0:1;
			$arr = array('keyValue'=>$showPropertyprice,);
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
			
			$showHouseinfo = empty($data['showhouseinfo'])?0:1;
			$arr = array('keyValue'=>$showHouseinfo,);
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
			
			
			$rows = $this->geLabelByKeyName('autocalcualte_period');
			$autoCalculatePeriod = empty($data['autocalcualte_period'])?0:1;
			if (empty($rows)){
				$arr = array('keyValue'=>$autoCalculatePeriod,'keyName'=>"autocalcualte_period",'user_id'=>$dbg->getUserId());
				$this->insert($arr);
			}else{
				$arr = array('keyValue'=>$autoCalculatePeriod,);
				$where=" keyName= 'autocalcualte_period'";
				$this->update($arr, $where);
			}
			
			
			$rows = $this->geLabelByKeyName('bank_account_name1');
			if (empty($rows)){
				$arr = array('keyValue'=>$data['bank_account_name1'],'keyName'=>'bank_account_name1','note'=>"",'user_id'=>$dbg->getUserId());
				$this->insert($arr);
			}else{
				$arr = array('keyValue'=>$data['bank_account_name1'],);
				$where=" keyName= 'bank_account_name1'";
				$this->update($arr, $where);
			}
			$rows = $this->geLabelByKeyName('bank_account_name2');
			if (empty($rows)){
				$arr = array('keyValue'=>$data['bank_account_name2'],'keyName'=>'bank_account_name2','note'=>"",'user_id'=>$dbg->getUserId());
				$this->insert($arr);
			}else{
				$arr = array('keyValue'=>$data['bank_account_name2'],);
				$where=" keyName= 'bank_account_name2'";
				$this->update($arr, $where);
			}
			
			$rows = $this->geLabelByKeyName('bank_account3');
			if (empty($rows)){
				$arr = array('keyValue'=>$data['bank_account3'],'keyName'=>'bank_account3','note'=>"",'user_id'=>$dbg->getUserId());
				$this->insert($arr);
			}else{
				$arr = array('keyValue'=>$data['bank_account3'],);
				$where=" keyName= 'bank_account3'";
				$this->update($arr, $where);
			}
			
			$rows = $this->geLabelByKeyName('bank_account_name3');
			if (empty($rows)){
				$arr = array('keyValue'=>$data['bank_account_name3'],'keyName'=>'bank_account_name3','note'=>"",'user_id'=>$dbg->getUserId());
				$this->insert($arr);
			}else{
				$arr = array('keyValue'=>$data['bank_account_name3'],);
				$where=" keyName= 'bank_account_name3'";
				$this->update($arr, $where);
			}
			
			$rows = $this->geLabelByKeyName('bank_account3number');
			if (empty($rows)){
				$arr = array('keyValue'=>$data['bank_account3number'],'keyName'=>'bank_account3number','note'=>"",'user_id'=>$dbg->getUserId());
				$this->insert($arr);
			}else{
				$arr = array('keyValue'=>$data['bank_account3number'],);
				$where=" keyName= 'bank_account3number'";
				$this->update($arr, $where);
			}
			
			$rows = $this->geLabelByKeyName('crmConnectWithSale');
			$crmConnectWithSale = empty($data['crmConnectWithSale'])?0:1;
			if (empty($rows)){
				$arr = array('keyValue'=>$crmConnectWithSale,'keyName'=>"crmConnectWithSale",'note'=>"សម្រាប់ តម្រូវការអតិថិជន អាចភ្ជាប់ព័ត៌មានទំនាក់ទំនងជាមួយការលក់",'user_id'=>$dbg->getUserId());
				$this->insert($arr);
			}else{
				$arr = array('keyValue'=>$crmConnectWithSale,);
				$where=" keyName= 'crmConnectWithSale'";
				$this->update($arr, $where);
			}
			
			$valid_formats = array("jpg", "png", "gif", "bmp","jpeg","ico");
			$part= PUBLIC_PATH.'/images/photo/logo/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			$name = $_FILES['photo']['name'];
			$size = $_FILES['photo']['size'];
			$photo='';
			
			$rows = $this->geLabelByKeyName('logo');
			if (empty($rows)){
				if (!empty($name)){
					$tem =explode(".", $name);
					$image_name = "logo".time().".".end($tem);
					$tmp = $_FILES['photo']['tmp_name'];
					if(move_uploaded_file($tmp, $part.$image_name)){
						$photo = $image_name;
					}
					else
						$string = "Image Upload failed";
					
					$arr = array(
							'keyName'=>'logo',
							'keyValue'=>$photo,
							'note'=>"",
							'user_id'=>$dbg->getUserId()
					);
					$this->insert($arr);
				}
			}else{
				if (!empty($name)){
					$tem =explode(".", $name);
					$image_name = time()."logo.".end($tem);
					$tmp = $_FILES['photo']['tmp_name'];
					if(move_uploaded_file($tmp, $part.$image_name)){
						$photo = $image_name;
					}
					else
						$string = "Image Upload failed";
					
					$arr = array(
							'keyValue'=>$photo,
					);
					$where=" keyName= 'logo'";
					$this->update($arr, $where);
				}
			}
			
			
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	
	public function geCheckKeycode($keyName){
		$db = $this->getAdapter();
		$sql = " SELECT s.`id`
		FROM `ln_system_setting` AS s
		WHERE s.`keycode` ='$keyName' LIMIT 1";
		return $db->fetchRow($sql);
	}
	public function updatelicense($data){
		try{
			$this->_name = "ln_system_setting";
			$licenseKey = $data['licenseKey'];
			
			if (strpos($licenseKey, 'K') === false) {
				return false;
			}	
			$explode1 = explode("K", $licenseKey);
			$meKun = end($explode1);
			if (strpos( $explode1[0], 'C') === false) {
				return false;
			}
			$explode2 = explode("C", $explode1[0]);
			
			$systenCode = $explode2[0];
			if (($systenCode/$meKun)==ICODE){
				if (strpos( end($explode2), 'P') === false) {
					return false;
				}
				$explode3 = explode("P", end($explode2));
				$day = ($explode3[0]/$meKun);
				
				if (strpos( end($explode3), 'T') === false) {
					return false;
				}
				$explode4 = explode("T", end($explode3));
				$month = ($explode4[0]/$meKun);
				$year = (end($explode4)/$meKun);
				
				
				
				$rows = $this->geCheckKeycode('lDD');
				if (empty($rows)){
					$arr = array('keycode'=>'lDD','value'=>$day);
					$this->insert($arr);
				}else{
					$arr = array('value'=>$day,);
					$where=" keycode= 'lDD'";
					$this->update($arr, $where);
				}
				$rows = $this->geCheckKeycode('lMM');
				if (empty($rows)){
					$arr = array('keycode'=>'lMM','value'=>$month,);
					$this->insert($arr);
				}else{
					$arr = array('value'=>$month,);
					$where=" keycode= 'lMM'";
					$this->update($arr, $where);
				}
				$rows = $this->geCheckKeycode('lY');
				if (empty($rows)){
					$arr = array('keycode'=>'lY','value'=>$year,);
					$this->insert($arr);
				}else{
					$arr = array('value'=>$year,);
					$where=" keycode= 'lY'";
					$this->update($arr, $where);
				}
				
				$rows = $this->geCheckKeycode('licenseKey');
				if (empty($rows)){
					$arr = array('keycode'=>'licenseKey','value'=>$licenseKey,'note'=>"license Key");
					$this->insert($arr);
				}else{
					$arr = array('value'=>$licenseKey,);
					$where=" keycode= 'licenseKey'";
					$this->update($arr, $where);
				}
				return true;
			}else{
				return false;
			}
				
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
}

