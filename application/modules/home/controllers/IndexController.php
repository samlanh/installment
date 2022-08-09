<?php
class Home_IndexController extends Zend_Controller_Action {
	
	
public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
	}
	public function indexAction()
	{
		$dbglobal = new Application_Model_DbTable_DbGlobal();
		$userid = $dbglobal->getUserId();
		if (empty($userid)){
			$this->_redirect("/index");
		}
		
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	$username = $session_user->first_name;
    	$systemType = $session_user->systemType;
		if($systemType==2){
			$this->_redirect("/home/request/");
			exit();
		}
		
		$db_user=new Application_Model_DbTable_DbUsers();
		$user_info = $db_user->getUserInfo($userid);
		
		if (!empty($user_info['staff_id'])){
			$this->_redirect("/home/index/dashboard");
		}
		
		$db = new Home_Model_DbTable_DbDashboard();
		
		$TotalOtherIncome = $db->getTotalOtherIncome();
		$TotalSaleIncome = $db->getTotalSaleIncome();
		$houseRepaireIncome = $db->getTotalHouseRepaireIncome(12);
		
		//$TotalCreditPayment = $db->getTotalSaleAmountCreditPayment();
		//$TotalCreditPayment = empty($TotalCreditPayment)?0:$TotalCreditPayment;
		
		$totalRentIncome = $db->getTotalRentPaymentIncome();
		$totalRefundRentExpense = $db->getTotalRefundRentDeposit();
		$totalRefundRentExpense = empty($totalRefundRentExpense)?0:$totalRefundRentExpense;
		
		$houseRepaireExpense = abs($db->getTotalHouseRepaireIncome(13));
		
		$this->view->allProperty =$db->getAllProperty();
		$this->view->propertySold =$db->getAllProperty(1);;
		$this->view->availableProperty =$db->getAllProperty(null,1);
		
		$this->view->AllClient = $db->CountAllClient();
		$this->view->CountAllAgency = $db->CountAllAgency();
		$this->view->CountSupplier = $db->getCountSupplier();
		
		$this->view->CountAllSale = $db->CountAllSale();
		$this->view->CountCompletedSale = $db->CountCompletedSale();
		$this->view->CountCanceledSale = $db->CountCanceledSale();
		$this->view->CancelPropertyAmount = $db->TotalExpense(1);
		
		$totalComissionPayment = $db->getTotalComissionPayment();
		$totalComissionPayment = empty($totalComissionPayment)?0:$totalComissionPayment;
		
		$otherExpense = $db->TotalExpense();
		$expenseFeatureList = EXPENSE_FEATURE_LIST;
		if($expenseFeatureList==1){
			$otherExpense = $db->TotalExpensePayment();
		}
		
		$TotalExpense = $otherExpense+$db->getAllComission()+$houseRepaireExpense+$totalRefundRentExpense+$totalComissionPayment;
		$this->view->totalExpense = $TotalExpense;
		
		$totalIncome = $TotalSaleIncome+$TotalOtherIncome+$houseRepaireIncome+$totalRentIncome;//+$TotalCreditPayment
		$this->view->totalIncome = $totalIncome;
		
		$netincome = $totalIncome-$TotalExpense;
		$this->view->netIncome = $netincome;
		
		$db = new Report_Model_DbTable_DbloanCollect();
		$rs = $db->getCustomerNearlyPayment();
		$this->view->customerNearlyPayment = $rs;
		$rsAgree = $db->getCustomerNearAgreement();
		$this->view->customerNearlyAgreement = $rsAgree;
		
		$db = new Home_Model_DbTable_DbDashboard();
		$lastest = $db->getAllNews(9);
		$this->view->lastestnews = $lastest;
		$this->view->allnews = $db->getAllNews();
		
// 		$this->view->allbranch = $dbglobal->getAllBranchName();
// 		$this->view->client = $dbglobal->getAllClient();
	}
	
	public function dashboardAction()
	{
// 		$this->_helper->layout()->disableLayout();
		$dbglobal = new Application_Model_DbTable_DbGlobal();
		$userid = $dbglobal->getUserId();
		if (empty($userid)){
			//$this->_redirect("/index");
		}
		$db_user=new Application_Model_DbTable_DbUsers();
		$user_info = $db_user->getUserInfo($userid);
		
		if (empty($user_info['staff_id'])){
			//$this->_redirect("/home");
		}
		$db = new Home_Model_DbTable_DbDashboard();
		$lastest = $db->getAllNews(9);
		$this->view->lastestnews = $lastest;
		$this->view->allnews = $db->getAllNews();
		
		$this->view->totalFullCommission = $db->getTotalFullCommission();
		$this->view->commissionpaid = $db->getCommissionPiadByAgent();
		$this->view->commissionPayment = $db->getCommissionPaymentPaidByAgent();
		$this->view->totalSale = $db->getTotalSaleByAgent();
		
		$param = $this->getRequest()->getParams();
		if(isset($param['search'])){
// 			$search=$this->getRequest()->getPost();
			$rs_rows= $db->getAllLandInfo($param);
			$this->view->row = $rs_rows;
			$this->view->soldout = $db->getAllLandInfo($param,1);
			
			$paginator = Zend_Paginator::factory($rs_rows);
			$paginator->setDefaultItemCountPerPage(16);
			$allItems = $paginator->getTotalItemCount();
			$countPages= $paginator->count();
			$p = Zend_Controller_Front::getInstance()->getRequest()->getParam('pages');
			
			if(isset($p))
			{
				$paginator->setCurrentPageNumber($p);
			} else $paginator->setCurrentPageNumber(1);
				
			$currentPage = $paginator->getCurrentPageNumber();
			
			$this->view->property  = $paginator;
			$this->view->countItems = $allItems;
			$this->view->countPages = $countPages;
			$this->view->currentPage = $currentPage;
				
			if($currentPage == $countPages)
			{
				$this->view->nextPage = $countPages;
				$this->view->previousPage = $currentPage-1;
			}
			else if($currentPage == 1)
			{
				$this->view->nextPage = $currentPage+1;
				$this->view->previousPage = 1;
			}
			else {
				$this->view->nextPage = $currentPage+1;
				$this->view->previousPage = $currentPage-1;
			}
		}
		else{
			$search = array(
					'adv_search' => '',
					'branch_id' => -1,
					'property_type_search'=>-1,
					'streetlist'=>''
			);
		}
		
		$frm = new Application_Form_FrmAdvanceSearch();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
		$fm = new Group_Form_FrmClient();
		$frmserch = $fm->FrmLandInfo();
		Application_Model_Decorator::removeAllDecorator($frmserch);
		$this->view->frm_land = $frmserch;
	}
	function clicknewsAction(){
		if ($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db=new Home_Model_DbTable_DbDashboard();
			$product= $db->addNewFeedClick($data['newfeedid']);
			print_r(Zend_Json::encode($product));
			exit();
		}
	}
	
	function newsAction(){
		$id = $this->getRequest()->getParam("detail");
		$db = new Home_Model_DbTable_DbDashboard();
		if (!empty($id)) {
			$detail =	$db->getNewsDetail($id);
			$this->view->allnews = $db->getAllNews(9,$id);
			$this->view->detail = $detail;
			$db->UpdateNewFeedRead($id);
		}else{
			$row =  $db->getAllNews();
			$this->view->row = $row;
			
			$paginator = Zend_Paginator::factory($row);
			$paginator->setDefaultItemCountPerPage(20);
			$allItems = $paginator->getTotalItemCount();
			$countPages= $paginator->count();
			$p = Zend_Controller_Front::getInstance()->getRequest()->getParam('pages');
				
			if(isset($p))
			{
				$paginator->setCurrentPageNumber($p);
			} else $paginator->setCurrentPageNumber(1);
			
			$currentPage = $paginator->getCurrentPageNumber();
				
			$this->view->allnews  = $paginator;
			$this->view->countItems = $allItems;
			$this->view->countPages = $countPages;
			$this->view->currentPage = $currentPage;
			
			if($currentPage == $countPages)
			{
				$this->view->nextPage = $countPages;
				$this->view->previousPage = $currentPage-1;
			}
			else if($currentPage == 1)
			{
				$this->view->nextPage = $currentPage+1;
				$this->view->previousPage = 1;
			}
			else {
				$this->view->nextPage = $currentPage+1;
				$this->view->previousPage = $currentPage-1;
			}
		}
	}
	
	function getalllandAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$action = (!empty($data['action'])?$data['action']:null);
			$propertytype= empty($data['property_type'])?null:$data['property_type'];
			$db = new Home_Model_DbTable_DbDashboard();
			$faculty = $db->getAllLand($data['branch_id'],1,$action,$propertytype);
			print_r(Zend_Json::encode($faculty));
			exit();
		}
	}
	
	function rptIncomeGraphicAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search = array(
					'branch_id'=>0,
					'start_date'=> date('Y'),
			);
		}
		$this->view->search=$search;
		$dbLanreport  = new Report_Model_DbTable_DbLandreport();
		$year = $dbLanreport->groupByYear();
		$this->view->yearOption = $year;
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->footerReport = $frmpopup->getFooterReport();
	}
}

