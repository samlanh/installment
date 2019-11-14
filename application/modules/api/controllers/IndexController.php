<?php
class Api_IndexController extends Zend_Controller_Action {
	
// 	const REDIRECT_URL='/project';
	protected $tr;
	public function init()
	{
		$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
		/* Initialize action controller here */
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
  		$this->_helper->layout()->disableLayout();
		$db = new Api_Model_DbTable_Dbapi();
		
		if($this->getRequest()->getParams()){
			$search = $this->getRequest()->getParams();
		}
		else{
			$search = array(
				'adv_search'=>'',
				'client_name'=> -1,
				'schedule_opt' => -1,
				'branch_id' => -1,
				'streetlist'=>'',
				'status' => -1,
				'co_id' => -1,
				'land_id'=>-1,
				'start_date'=> date('Y-m-d'),
				'end_date'=>date('Y-m-d'),
			);
		}
		print_r(Zend_Json::encode($db->getAllSold($search)));
		exit();
	}
	public function salebytypeAction(){
		$this->_helper->layout()->disableLayout();
		$db = new Api_Model_DbTable_Dbapi();
		print_r(Zend_Json::encode($db->getSalebyType()));
		exit();
	}
	public function incomeAction(){
		$this->_helper->layout()->disableLayout();
		$db = new Api_Model_DbTable_Dbapi();
		print_r(Zend_Json::encode($db->getAllIncome()));
		exit();
	}
	function expenseAction(){
		$db = new Api_Model_DbTable_Dbapi();
		$rs_expense = $db->getExpenseType();
		$rs_comission = $db->getAllComissionbyType();
		$rs = array_merge($rs_expense,$rs_comission);
		
		$total_expense = 0;
		if(!empty($rs)){
			foreach($rs as $r){
				$total_expense = $total_expense+$r['total_expense'];	
			}
		}
		$total_expense = array('total_expense'=>number_format($total_expense,2));
		$rs = array('total_expense'=>$total_expense,'expense_bytype'=>$rs);
		print_r(Zend_Json::encode($rs));
		exit();
	}
	function expensedetailAction(){
		$db = new Api_Model_DbTable_Dbapi();
		$rs_expense = $db->getAllDetailExpense();
		$rs_comission = $db->getAllComissionDetail();
		$rs = array_merge($rs_expense,$rs_comission);
		print_r(Zend_Json::encode($rs));
		exit();
	}
	function expectincomeAction(){
		$db = new Api_Model_DbTable_Dbapi();
		print_r(Zend_Json::encode($db->getALLLoanExpectIncome()));
		exit();
	}
	function outstandingAction(){
		$db = new Api_Model_DbTable_Dbapi();
		print_r(Zend_Json::encode($db->getAllOutstadingLoan()));
		exit();
	}
	function cancelsaleAction(){
		$db = new Api_Model_DbTable_Dbapi();
		print_r(Zend_Json::encode($db->getAllSaleCancel()));
		exit();
	}
	function getdailyincomeAction(){
		$db = new Api_Model_DbTable_Dbapi();
		print_r(Zend_Json::encode($db->getDailyIncome()));
		exit();
	}
	function getdailyexpenseAction(){
		$db = new Api_Model_DbTable_Dbapi();
		print_r(Zend_Json::encode($db->getDailyExpense()));
		exit();
	}
	function dailycollectAction(){//ត្រូវបង់
		$db = new Api_Model_DbTable_Dbapi();
		print_r(Zend_Json::encode($db->getAllCollectPayment()));
		exit();
	}
	function agreementAction(){//ត្រូវបង់
		$db = new Api_Model_DbTable_Dbapi();
		print_r(Zend_Json::encode($db->getAllSaleAgreement()));
		exit();
	}
	function commissionAction(){//ត្រូវបង់
		$db = new Api_Model_DbTable_Dbapi();
		print_r(Zend_Json::encode($db->getAllCommission()));
		exit();
	}
	function otherdataAction(){//ត្រូវបង់
		header("Content-Type: application/json");
		$db = new Api_Model_DbTable_Dbapi();
// 		$string='[{"id":"1","createdAt":"2019-09-20T21:08:20.349Z","name":"Leonor Bins","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/rweve/128.jpg"},{"id":"2","createdAt":"2019-09-21T05:09:00.668Z","name":"Miss Halle Hickle","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/duck4fuck/128.jpg"},{"id":"3","createdAt":"2019-09-20T22:39:29.948Z","name":"Mrs. Dan Fritsch","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/aaronkwhite/128.jpg"},{"id":"4","createdAt":"2019-09-20T21:21:55.421Z","name":"Miss Alexys Mraz","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/digitalmaverick/128.jpg"},{"id":"5","createdAt":"2019-09-20T08:44:49.101Z","name":"Bernardo Rempel","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/joshuaraichur/128.jpg"},{"id":"6","createdAt":"2019-09-21T04:51:53.561Z","name":"Dock McDermott","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/naitanamoreno/128.jpg"},{"id":"7","createdAt":"2019-09-20T23:13:33.863Z","name":"Derrick Lockman","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/hanna_smi/128.jpg"},{"id":"8","createdAt":"2019-09-20T10:28:58.701Z","name":"Euna Raynor","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/salleedesign/128.jpg"},{"id":"9","createdAt":"2019-09-21T06:51:48.962Z","name":"Keshaun Murphy I","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/ryuchi311/128.jpg"},{"id":"10","createdAt":"2019-09-20T15:19:51.937Z","name":"Sharon Leuschke","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/gauchomatt/128.jpg"},{"id":"11","createdAt":"2019-09-20T16:52:01.352Z","name":"Dayne Stroman","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/keryilmaz/128.jpg"},{"id":"12","createdAt":"2019-09-21T01:54:55.894Z","name":"Jannie Ernser","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/rickdt/128.jpg"},{"id":"13","createdAt":"2019-09-21T01:38:18.047Z","name":"Shirley Watsica","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/operatino/128.jpg"},{"id":"14","createdAt":"2019-09-21T00:06:49.506Z","name":"Cathy Trantow","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/akmalfikri/128.jpg"},{"id":"15","createdAt":"2019-09-20T13:52:14.984Z","name":"Mr. Bridgette Sporer","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/gt/128.jpg"},{"id":"16","createdAt":"2019-09-20T19:49:00.470Z","name":"Miss Marlon Hayes","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/russell_baylis/128.jpg"},{"id":"17","createdAt":"2019-09-20T23:29:32.064Z","name":"Leopoldo Kunde MD","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/rude/128.jpg"},{"id":"18","createdAt":"2019-09-21T03:19:15.718Z","name":"Mohammad Rodriguez","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/jeffgolenski/128.jpg"},{"id":"19","createdAt":"2019-09-20T14:06:04.019Z","name":"Buster Friesen","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/jpscribbles/128.jpg"},{"id":"20","createdAt":"2019-09-20T22:27:12.035Z","name":"Darion Walter","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/coreyginnivan/128.jpg"},{"id":"21","createdAt":"2019-09-20T16:42:04.222Z","name":"Amelia Dickens","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/alxleroydeval/128.jpg"},{"id":"22","createdAt":"2019-09-20T09:37:55.557Z","name":"Beaulah Swaniawski","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/kurtinc/128.jpg"},{"id":"23","createdAt":"2019-09-20T15:10:34.303Z","name":"Kaylie Hara III","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/anthonysukow/128.jpg"},{"id":"24","createdAt":"2019-09-21T01:44:34.552Z","name":"Noe Schroeder","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/reetajayendra/128.jpg"},{"id":"25","createdAt":"2019-09-20T09:13:39.871Z","name":"Darryl Donnelly","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/richardgarretts/128.jpg"},{"id":"26","createdAt":"2019-09-21T02:05:17.678Z","name":"Josianne Bergstrom","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/adrienths/128.jpg"},{"id":"27","createdAt":"2019-09-21T03:56:46.092Z","name":"Lydia Mayert","avatar":"https://s3.amazonaws.com/uifaces/faces/twitter/skkirilov/128.jpg"}]';
// 		echo $string;
		print_r(Zend_Json::encode($db->otherdata()));
		exit();
	}
	
}

