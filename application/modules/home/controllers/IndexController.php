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
		$db_user=new Application_Model_DbTable_DbUsers();
		$user_info = $db_user->getUserInfo($userid);
		
		if (!empty($user_info['staff_id'])){
			$this->_redirect("/home/index/dashboard");
		}
		
		$db = new Home_Model_DbTable_DbDashboard();
		$allProperty = $db->getAllProperty();
		$propertySold = $db->getAllProperty(1);
		$availableProperty = $db->getAllProperty(null,1);
		
		$CountAllClient = $db->CountAllClient();
		$CountAllAgency = $db->CountAllAgency();
		
		$CountAllSale = $db->CountAllSale();
		$CountCompletedSale = $db->CountCompletedSale();
		$CountCanceledSale = $db->CountCanceledSale();
		$CancelPropertyAmount = $db->TotalExpense(1);
		
		$TotalExpense = $db->TotalExpense();
		$TotalOtherIncome = $db->getTotalOtherIncome();
		$TotalSaleIncome = $db->getTotalSaleIncome();
		
		$this->view->allProperty =$allProperty;
		$this->view->propertySold =$propertySold;
		$this->view->availableProperty =$availableProperty;
		
		$this->view->AllClient = $CountAllClient;
		$this->view->CountAllAgency = $CountAllAgency;
		
		$this->view->CountAllSale = $CountAllSale;
		$this->view->CountCompletedSale = $CountCompletedSale;
		$this->view->CountCanceledSale = $CountCanceledSale;
		$this->view->CancelPropertyAmount = $CancelPropertyAmount;
		
		$this->view->totalExpense = $TotalExpense;
		
		$totalIncome = $TotalSaleIncome+$TotalOtherIncome;
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
			$this->_redirect("/index");
		}
		$db_user=new Application_Model_DbTable_DbUsers();
		$user_info = $db_user->getUserInfo($userid);
		
		if (empty($user_info['staff_id'])){
			$this->_redirect("/home");
		}
		$db = new Home_Model_DbTable_DbDashboard();
		$lastest = $db->getAllNews(9);
		$this->view->lastestnews = $lastest;
		$this->view->allnews = $db->getAllNews();
		
		$this->view->totalFullCommission = $db->getTotalFullCommission();
		$this->view->commissionpaid = $db->getCommissionPiadByAgent();
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
// 		$this->_helper->layout()->disableLayout();
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
}

