<?php
class Other_TestsaleController extends Zend_Controller_Action {
	const REDIRECT_URL='/other';
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
	protected $tr;
    public function init()
    {    	
     /* Initialize action controller here */
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
// 				$_dbmodel = new Loan_Model_DbTable_DbLandpayment();
// 				$_dbmodel->addSchedulePayment($_data);
// 				if(!empty($_data['saveclose'])){
// 					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan");
// 				}else{
// 					Application_Form_FrmMessage::message("INSERT_SUCCESS");
// 				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$frm = new Loan_Form_FrmLoan();
		$frm_loan=$frm->FrmAddLoan();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$db = new Application_Model_DbTable_DbGlobal();
		$co_name = $db->getAllCoNameOnly();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		array_unshift($co_name,array(
		        'id' => -1,
		        'name' =>$tr->translate("ADD_NEW"),
		) );
	    $this->view->co_name=$co_name;
	    
	    $interest = $db->getAllInterestratestore();
	    array_unshift($interest,array(
	    'id' => -1,
	    'name' =>$tr->translate("ADD_NEW"),
	    ) );
	    $this->view->rs_interest = $interest;
	    
	    $db = new Application_Model_DbTable_DbGlobal();
	    $this->view->client_doc_type = $db->getclientdtype();
	    
	    $key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}
   
}

