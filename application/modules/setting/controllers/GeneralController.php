<?php
class Setting_generalController extends Zend_Controller_Action {
	
	
public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
	}
	public function indexAction()
	{
		$id = $this->getRequest()->getParam("id");
		$db_gs = new Setting_Model_DbTable_DbGeneral();
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$db_gs->updateWebsitesetting($data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/setting/general");
			}catch (Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAILE");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$row =array();
		$row['label_animation'] = $db_gs->geLabelByKeyName('label_animation');
		$row['sms-warnning-kh'] = $db_gs->geLabelByKeyName('sms-warnning-kh');
		$row['reciept_kh'] = $db_gs->geLabelByKeyName('reciept_kh');
		$row['exchange_ratetitle'] = $db_gs->geLabelByKeyName('exchange_ratetitle');
		$row['exchange_reciept'] = $db_gs->geLabelByKeyName('exchange_reciept');
		$row['comment'] = $db_gs->geLabelByKeyName('comment');
		$row['comment1'] = $db_gs->geLabelByKeyName('comment1');
		$row['brand_client'] = $db_gs->geLabelByKeyName('brand_client');
		$row['brand_holiday'] = $db_gs->geLabelByKeyName('brand_holiday');
		$row['brand_call'] = $db_gs->geLabelByKeyName('brand_call');
		$row['rpt-transfer-title-kh'] = $db_gs->geLabelByKeyName('rpt-transfer-title-kh');
		
		$row['footer_branch'] = $db_gs->geLabelByKeyName('footer_branch');
		$row['tel-client'] = $db_gs->geLabelByKeyName('tel-client');
		$row['client_website'] = $db_gs->geLabelByKeyName('client_website');
		$row['email_client'] = $db_gs->geLabelByKeyName('email_client');
		
		$row['power_by'] = $db_gs->geLabelByKeyName('power_by');
		$row['branch-tel'] = $db_gs->geLabelByKeyName('branch-tel');
		$row['branch_add'] = $db_gs->geLabelByKeyName('branch_add');
		$row['branch_email'] = $db_gs->geLabelByKeyName('branch_email');
		$row['website'] = $db_gs->geLabelByKeyName('website');
		$row['customer_sign'] = $db_gs->geLabelByKeyName('customer_sign');
		$row['teller_sign'] = $db_gs->geLabelByKeyName('teller_sign');
		$row['account_sign'] = $db_gs->geLabelByKeyName('account_sign');
		$row['bank_info'] = $db_gs->geLabelByKeyName('bank_info');
		
		$row['show_propertyprice'] = $db_gs->geLabelByKeyName('show_propertyprice');
		$row['bank_account1'] = $db_gs->geLabelByKeyName('bank_account1');
		$row['bank_account_name1'] = $db_gs->geLabelByKeyName('bank_account_name1');
		$row['bank_account1number'] = $db_gs->geLabelByKeyName('bank_account1number');
		$row['bank_account2'] = $db_gs->geLabelByKeyName('bank_account2');
		$row['bank_account_name2'] = $db_gs->geLabelByKeyName('bank_account_name2');
		$row['bank_account2number'] = $db_gs->geLabelByKeyName('bank_account2number');
		
		$row['bank_account3'] = $db_gs->geLabelByKeyName('bank_account3');
		$row['bank_account_name3'] = $db_gs->geLabelByKeyName('bank_account_name3');
		$row['bank_account3number'] = $db_gs->geLabelByKeyName('bank_account3number');
		
		$row['cheque_receiver'] = $db_gs->geLabelByKeyName('cheque_receiver');
		$row['showhouseinfo'] = $db_gs->geLabelByKeyName('showhouseinfo');
		
		$row['penalty_type'] = $db_gs->geLabelByKeyName('penalty_type');
		$row['penalty_value'] = $db_gs->geLabelByKeyName('penalty_value');
		$row['graice_pariod_late'] = $db_gs->geLabelByKeyName('graice_pariod_late');
		
		$row['agree_day_alert'] = $db_gs->geLabelByKeyName('agree_day_alert');
		$row['payment_day_alert'] = $db_gs->geLabelByKeyName('payment_day_alert');
		$row['signatur_agree'] = $db_gs->geLabelByKeyName('signatur_agree');
		
		$row['autocalcualte_period'] = $db_gs->geLabelByKeyName('autocalcualte_period');
		$row['logo'] = $db_gs->geLabelByKeyName('logo');
		$this->view->row = $row;
		
		$fm = new Setting_Form_FrmGeneral();
		$frm = $fm->FrmGeneral($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_general = $frm;
	}
	function refreshAction(){
		
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$param = $this->getRequest()->getParam("channy");
				$param = empty($param)?"":$param;
				$type=0;
				if (!empty($data['type_fomate'])){
					$type=$data['type_fomate'];
				}
				$dbglobal = new Application_Model_DbTable_DbGlobal();
				$return = $dbglobal->testTruncate($type,$param);
				if ($return==-1){
					Application_Form_FrmMessage::Sucessfull("Can not Clear Data", "/setting/general/refresh");
				}else{
					Application_Form_FrmMessage::Sucessfull("SUCCESSFULLY", "/setting/general/refresh");
				}
				
			}catch (Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAILE");
				echo $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$fm = new Setting_Form_FrmGeneral();
		$frm = $fm->FrmTruncate();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_general = $frm;
	}
	
}

