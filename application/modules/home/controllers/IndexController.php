<?php
class Home_IndexController extends Zend_Controller_Action {
	
	
public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
	}
	public function indexAction()
	{
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
	}
	
}

