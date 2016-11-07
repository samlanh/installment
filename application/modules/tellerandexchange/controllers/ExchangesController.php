// <?php

// class Tellerandexchange_ExchangesController extends Zend_Controller_Action
// {

// 	const REDIRECT_URL = '/tellerandexchange';
	
//     public function init()
//     {
//         /* Initialize action controller here */
//     	header('content-type: text/html; charset=utf8');
//     	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
//     }

//     public function indexAction()
//     {
//         try{
// 			$db = new Tellerandexchange_Model_DbTable_Dbexchange();
// 			if($this->getRequest()->isPost()){
// 				$search=$this->getRequest()->getPost();
// 			}
// 			else{
// 				$search = array(
// 						'client_id' => -1,
// 						'status' => -1,
// 						'from_date' =>date('Y-m-d'),
// 						'to_date' => date('Y-m-d'),
// 						 );
// 			}
// 			$rs_rows= $db->getAllExchangeList($search);
// 			$glClass = new Application_Model_GlobalClass();
// 			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
// 			$list = new Application_Form_Frmtable();
// 			$collumns = array("ឈ្មោះអតិថិជន","ថ្ងៃ​ប្រតិបត្តិ","វិ.បត្រ","ការប្តូរប្រាក់","ចំនួនទឹកប្រាក់","អត្រាប្តូរប្រាក់","ចំនួនទឹកប្រាក់បានប្តូររួច","ប្រាក់​ទទួល​បាន​","ប្រាក់​អាប់","TYPE","STATUS");
// 			$link=array(
// 					'module'=>'tellerandexchange','controller'=>'exchanges','action'=>'edit',
// 			);
// 			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('client_name'=>$link,'invoice_code'=>$link,'date'=>$link));
// 		}catch (Exception $e){
// 			Application_Form_FrmMessage::message("Application Error");
// 			echo $e->getMessage();
// 			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
// 		}	
// 		$frm = new Application_Form_FrmAdvanceSearch();
// 		$frm = $frm->AdvanceSearch();
// 		Application_Model_Decorator::removeAllDecorator($frm);
// 		$this->view->frm_search = $frm;	
//     }


//     public function addAction()
//     {
//         $session_user=new Zend_Session_Namespace('auth');
//         $this->view->user_name = $session_user->last_name .' '. $session_user->first_name;
        
//         $db_keycode = new Application_Model_DbTable_DbKeycode();
// 		$this->view->keycode = $db_keycode->getKeyCodeMiniInv();
		
// 		$db_g = new Application_Model_DbTable_DbGlobal();
// 		$this->view->inv_no = $db_g ->getNewInvoiceExchange();
		
// 		if($this->getRequest()->isPost()){
// 			$formdata=$this->getRequest()->getPost();	
// 			$db_xchange = new Tellerandexchange_Model_DbTable_Dbexchange();	
// 			try {
// 				$db_xchange->saveExchange($formdata);
// 				if(!empty($formdata['save_new'])){
// 					Application_Form_FrmMessage::message('ការ​បញ្ចូល​​ជោគ​ជ័យ');
// 				}else{
// 					Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL . '/exchanges/index');
// 				}
// 			} catch (Exception $e) {
// 				Application_Form_FrmMessage::message("INSERT_FAIL");
// 				$err =$e->getMessage();
// 				Application_Model_DbTable_DbUserLog::writeMessageError($err);
// 			}
// 		}
        
//     }
//     public function editAction()
//     {
//     	$session_user=new Zend_Session_Namespace('auth');
//     	$this->view->user_name = $session_user->last_name .' '. $session_user->first_name;
    	
//     	$db_keycode = new Application_Model_DbTable_DbKeycode();
//     	$this->view->keycode = $db_keycode->getKeyCodeMiniInv();
    	
//     	$db_g = new Application_Model_DbTable_DbGlobal();
//     	$this->view->inv_no = $db_g ->getNewInvoiceExchange();
    	
    	
//     	//         // action body
//     	//         //Get value from url
//     	//         $ex_id = $this->getRequest()->getParam('ex_id',0);
    
//     	//      	$session_user=new Zend_Session_Namespace('auth');
//     	//         $this->view->user_name = $session_user->last_name .' '. $session_user->first_name;
    
//     	//         $db_keycode = new Application_Model_DbTable_DbKeycode();
//     	// 		$this->view->keycode = $db_keycode->getKeyCodeMiniInv();
    
//     	// 		$cur = new Application_Model_DbTable_DbCurrencies();
//     	// 		$currency = $cur->getCurrencyList();
    
//     	// 		$this->view->currency = $this->_helpfilteroption($currency);
    
//     	// 		$db_exc=new Application_Model_DbTable_DbExchange();
//     	// 		$this->view->dataedit = $db_exc->getDataById($ex_id);
//     	// 		if($this->getRequest()->isPost()){
//     	// 			$formdata=$this->getRequest()->getPost();
//     	// 			try {
//     	// 				$result = $db_exc->getDataById($formdata['id']);
//     	// 				$to_type = $db_exc->getCurrencyById("`name`, `symbol`,`country_id`", $formdata["to_amount_type"]);
//     	// 				$from_type = $db_exc->getCurrencyById("`name`, `symbol`,`country_id`", $formdata["from_amount_type"]);
    
//     	// 				if($result['fromAmountType']==$from_type['country_id'] && $result['toAmountType']==$to_type['country_id']){//if the edit not change the currency
//     	// 					$id = $db_exc->updateData($formdata);
//     	// 				}else{//if edit change the currency we need to add new record
//     	// 					$formdata['inv_no'] = Application_Model_GlobalClass::getInvoiceNo();
//     	// 					$id = $db_exc->save($formdata);
//     	// 				}
    
//     	// 				Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL);
    
//     	// 			} catch (Exception $e) {
//     	// 				$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
//     	// 			}
//     	// 		}
//     }
  

    
// }











