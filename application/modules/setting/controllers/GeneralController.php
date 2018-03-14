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
				echo $e->getMessage();
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
		$row['brand_client'] = $db_gs->geLabelByKeyName('brand_client');
		$row['brand_holiday'] = $db_gs->geLabelByKeyName('brand_holiday');
		$row['brand_call'] = $db_gs->geLabelByKeyName('brand_call');
		$row['rpt-transfer-title-kh'] = $db_gs->geLabelByKeyName('rpt-transfer-title-kh');
		
		$row['branch-add-client'] = $db_gs->geLabelByKeyName('branch-add-client');
		$row['tel-client'] = $db_gs->geLabelByKeyName('tel-client');
		$row['client_website'] = $db_gs->geLabelByKeyName('client_website');
		$row['email_client'] = $db_gs->geLabelByKeyName('email_client');
		
		$row['power_by'] = $db_gs->geLabelByKeyName('power_by');
		$row['branch-tel'] = $db_gs->geLabelByKeyName('branch-tel');
		$row['branch_add'] = $db_gs->geLabelByKeyName('branch_add');
		$row['branch_email'] = $db_gs->geLabelByKeyName('branch_email');
		
		$fm = new Setting_Form_FrmGeneral();
		$frm = $fm->FrmGeneral($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_general = $frm;
	}
	
}

