<?php
class Report_indexController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
  function indexAction(){
  	$this->_redirect('report/loan');
  }
  function addAction()
  {
		if($this->getRequest()->isPost()){
// 			$_data = $this->getRequest()->getPost();
// 			try {
// 				$_dbmodel = new Global_Model_DbTable_DbProvince();
// 				$_dbmodel->addNewProvince($_data);
// 				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/global/index/subject-list");
// 			}catch (Exception $e) {
// 				Application_Form_FrmMessage::message("INSERT_FAIL");
// 				$err =$e->getMessage();
// 				Application_Model_DbTable_DbUserLog::writeMessageError($err);
// 			}
		}
		$frm = new Loan_Form_FrmLoan();
		$frm_loan=$frm->FrmAddLoan();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
	}
	
	function rptClientAction()
	{
		
		if($this->getRequest()->isPost()){
			// 			$_data = $this->getRequest()->getPost();
			// 			try {
			// 				$_dbmodel = new Global_Model_DbTable_DbProvince();
			// 				$_dbmodel->addNewProvince($_data);
			// 				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/global/index/subject-list");
			// 			}catch (Exception $e) {
			// 				Application_Form_FrmMessage::message("INSERT_FAIL");
			// 				$err =$e->getMessage();
			// 				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			// 			}
		}
		$db = new Accounting_Model_DbTable_DbExpense();
		$this->view->client_list = $db->getAllExpense();
		
	}

function rptPaymentdetailAction(){
 	
}
function delinquentloansAction(){
	
}

 function rptIlpaymentdetailAction(){
 	
 }
 function rptGeneralAction(){
 	// 		$fr=new Application_Form_FrmReportParameter();
 	// 		$form=$fr->getFormReport(array(
 	// 				'budget'=>-1,
 	// 				'division'=>-1,
 	// 				'fund_type'=>-1,
 	// 				'year'=>-1,
 	// 				'wa_type'=>-1,
 	// 				'fund_no'=>-1,
 	// 		),$this,true);
 	// 		$this->view->filter = $form;
 	
 	$file='/budget/budget.html';
 	// 		$head=$report->getHeadBudgetList('project',date('Y'));
 	$report=new Application_Model_DbTable_DbGlobal();
 	$content=$report->getContent($result_row);
 	$param=array(
 			'division'=>1,
 			'budget'=>1,
 			'head'=>1,
 			'content'=>1);
 	$db=new Application_Model_DbTable_DbGlobal();
 	$new=$db->setReportParam($param, $file);
 	$this->view->file=$new;
 }
 function staffSalaryAction(){
 	
 }

 public  function clientPersonalAction(){
 	
 }

 function contractInformationAction(){
 	
 }
 function contractHistoryAction(){
 	
 }

 
//  function indexAction(){
 	
//  }
 function individulAction(){
 	
 }

 function oblAndLlpAction(){
 	
 }

 function rptLoancollectAction(){
 	
 }
 function allRptAction(){
 
 }
 function rptKhmerAction(){
 
 }

 function indexnAction(){
 	
 }
 
 function zendhtmlAction(){
 	
 }
}

