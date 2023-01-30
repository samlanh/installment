<?php
class Report_StockreportController extends Zend_Controller_Action {
  public function init()
  {    	
    header('content-type: text/html; charset=utf8');
    $this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
}
public function indexAction(){
}

public function rptCurrentstockAction(){
  
        if($this->getRequest()->isPost()){
            $search = $this->getRequest()->getPost();
          }
          else{
           $search = array(
				'adv_search'=>'',
				'branch_id'=>-1,
				'isCountStock'=>-1,
				'categoryId'=>0,
				'budgetItem'=>0,
				'measureId'=>0,
				'status'=>-1,
           		'start_date'=> date('Y-m-d'),
           		'end_date'=>date('Y-m-d'),
				);
          }
          	$rs_rows = array();
          try{
	       	 	$db = new Report_Model_DbTable_DbStockReports();
	        	$rs_rows = $db->getAllProductLocation($search);
	          	
	      }catch (Exception $e){
	        	Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	       	 	Application_Form_FrmMessage::message("APPLICATION_ERROR");
	      }
	      
      	$this->view->rows=$rs_rows;
      	$this->view->search=$search;
      	
     	$frm = new Application_Form_FrmAdvanceSearchStock();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
		$frm = new Product_Form_Frmproduct();
		$frm = $frm->FrmSearchProduct();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frmSearchProduct = $frm;
      
	    $frmpopup = new Application_Form_FrmPopupGlobal();
	    $this->view->footerReport = $frmpopup->getFooterReport();
	    $this->view->headerReport = $frmpopup->getLetterHeadReport();
}

public function rptUsageAction(){
	$rs_rows = array();
	$search = array();
  try{
        if($this->getRequest()->isPost()){
            $search = $this->getRequest()->getPost();
         }
         else{
	           $search = array(
						'adv_search'=>'',
						'branch_id'=>-1,
						'status'=>-1,
						'propertyType'=>'',
						'workType'=>0,
						'contractor'=>0,
						'staffWithdraw'=>0,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
					);
          }
        
          $db = new Report_Model_DbTable_DbStockReports();
          $rs_rows = $db->getAllUsageStock($search);
      }catch (Exception $e){
        Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
        Application_Form_FrmMessage::message("APPLICATION_ERROR");
      }
     
      $this->view->rows=$rs_rows;
      $this->view->search=$search;
      
      $frm = new Application_Form_FrmAdvanceSearch();
	  $frm = $frm->AdvanceSearch();
	  Application_Model_Decorator::removeAllDecorator($frm);
	  $this->view->frm_search = $frm;
	
	  $fm = new Stockinout_Form_FrmStockOut();
	  $frm = $fm->FrmWithdrawStock();
	  Application_Model_Decorator::removeAllDecorator($frm);
	  $this->view->frm_stock = $frm;
      
      $frmpopup = new Application_Form_FrmPopupGlobal();
      $this->view->footerReport = $frmpopup->getFooterReport();
      $this->view->headerReport = $frmpopup->getLetterHeadReport();
}


	public function rptUsagedetailAction(){
	  try{
	    $db = new Report_Model_DbTable_DbStockReports();
	    $id=$this->getRequest()->getParam('id');
	    $id = empty($id)?0:$id;
	    $row = $db->getDataRow($id);
	    if (empty($row)){
	      Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/report/stockreport/rpt-usage",2);
	      exit();
	    }
	    $this->view->row = $row;
	    $this->view->rowdetail = $db->getDataAllRow($id);
	  
	  }catch (Exception $e){
	    Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    Application_Form_FrmMessage::message("APPLICATION_ERROR");
	  }
	  
	  $frmpopup = new Application_Form_FrmPopupGlobal();
	  $this->view->printByFormat = $frmpopup->printByFormat();
	}

	public function rptReceivestockAction(){
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
		}
		else{
			$search=array(
				'adv_search'=>"",
				'branch_id' => -1,
				'verifyStatus'=>-1,
				'supplierId'=>0,
				'start_date'=> date('Y-m-d'),
				'end_date'=>date('Y-m-d'),
				'status'=>-1,
			);
		}
	  try{
	    	$db = new Report_Model_DbTable_DbStockReports();
	      
	  }catch (Exception $e){
	    	Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	Application_Form_FrmMessage::message("APPLICATION_ERROR");
	  }
		  $rs_rows = $db->getAllReceiveStock($search);
		  $this->view->rows=$rs_rows;
	 	  $this->view->search = $search;
	 	  
		 $frm_search = new Application_Form_FrmAdvanceSearchStock();
		 $frm = $frm_search->AdvanceSearch();
		 Application_Model_Decorator::removeAllDecorator($frm);
		 $this->view->frm_search = $frm;
		  
	    $frmpopup = new Application_Form_FrmPopupGlobal();
	    $this->view->footerReport = $frmpopup->getFooterReport();
	    $this->view->headerReport = $frmpopup->getLetterHeadReport();
	}


	public function rptReceivestockdetailAction(){
		$db = new Stockinout_Model_DbTable_DbReceiveStock();
	  try{
	    	$id = $this->getRequest()->getParam('id');
			$id = empty($id)?0:$id;
			$param = array(
					'dnId'=>$id,
					'transactionType'=>1,//dn request
			);
	  
	  }catch (Exception $e){
	    Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    Application_Form_FrmMessage::message("APPLICATION_ERROR");
	  }
	  
	  $rs = $db->getDNById($param);
	  if(empty($id) OR empty($rs)){
	  	Application_Form_FrmMessage::Sucessfull("NO_DATA","/report/stockreport/rpt-receivestock",2);
	  }
	  
	  
		  $this->view->rsRow = $rs;
		  $this->view->dnDetail = $db->getDNDetailById($id);
		  
	  	  $frmpopup = new Application_Form_FrmPopupGlobal();
	  	  $this->view->printByFormat = $frmpopup->printByFormat();
	  	  
	  	  $frmpopup = new Application_Form_FrmPopupGlobal();
	  	  $this->view->footerReport = $frmpopup->getFooterReport();
	  	  $this->view->headerReport = $frmpopup->getLetterHeadReport();
	}
	public function rptConcretednAction(){
		$db = new Stockinout_Model_DbTable_DbReceiveStock();
		try{
			$id = $this->getRequest()->getParam('id');
			$id = empty($id)?0:$id;
			$param = array(
					'dnId'=>$id,
					'transactionType'=>2,
					);
			$rs = $db->getDNById($param);
			if(empty($id) OR empty($rs)){
				Application_Form_FrmMessage::Sucessfull("NO_DATA","/po/concret/index",2);
			}
			 
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
		 
		$this->view->rsRow = $rs;
		$this->view->dnDetail = $db->getDNDetailById($id);
	
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->printByFormat = $frmpopup->printByFormat();
	
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->footerReport = $frmpopup->getFooterReport();
		$this->view->headerReport = $frmpopup->getLetterHeadReport();
	}

	public function rptSummarystockAction(){
	  try{
	    if($this->getRequest()->isPost()){
	        $search = $this->getRequest()->getPost();
	      }
	      else{
	        $search=array(
		        'adv_search'=>"",
		        'branch_id' => -1,
	        	'reportDate'=>-1,
	        	'productId'=>'-1',
	        	'categoryId'=>'',
		        'status'=>-1,
	      	);
	      }
	    
	      $this->view->search = $search;
	      $db = new Report_Model_DbTable_DbStockReports();
	      $rs_rows = $db->getSummaryStockReport($search);
	      $this->view->rows=$rs_rows;
	      $this->view->search=$search;
	      
		  }catch (Exception $e){
		    Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		    Application_Form_FrmMessage::message("APPLICATION_ERROR");
		  }
	  
		  $frm_search = new Application_Form_FrmAdvanceSearchStock();
		  $frm = $frm_search->AdvanceSearch();
		  Application_Model_Decorator::removeAllDecorator($frm);
		  $this->view->frm_search = $frm;
		  $db = new Application_Model_DbTable_DbGlobalStock();
		  $categorResult = $db->getAllCategoryProduct(0,'','');
		  
		  $this->view->categoryList  = $categorResult;
		  
		  $frmpopup = new Application_Form_FrmPopupGlobal();
		  $this->view->footerReport = $frmpopup->getFooterReport();
		  $this->view->headerReport = $frmpopup->getLetterHeadReport();
	}

	public function rptTransferAction(){
		try{
		  if($this->getRequest()->isPost()){
			  $search = $this->getRequest()->getPost();
			}
			else{
			  $search=array(
				  'adv_search'=>"",
				  'branch_id' => -1,
				  'start_date'=> date('Y-m-d'),
				  'end_date'=>date('Y-m-d'),
				  'status'=>-1,
				);
			}
				$this->view->search = $search;
			  	$db = new Report_Model_DbTable_DbStockReports();
			  	$rs_rows = $db->getTransferAllReport($search);
				$this->view->rows=$rs_rows;
				$this->view->search=$search;
			
			}catch (Exception $e){
				  Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				  Application_Form_FrmMessage::message("APPLICATION_ERROR");
			}
		
			$frm_search = new Application_Form_FrmAdvanceSearchStock();
			$frm = $frm_search->AdvanceSearch();
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm_search = $frm;
			
			$frmpopup = new Application_Form_FrmPopupGlobal();
			$this->view->footerReport = $frmpopup->getFooterReport();
			$this->view->headerReport = $frmpopup->getLetterHeadReport();
	}
  public function rptTransferdetailAction(){
	try{
	    $db = new Report_Model_DbTable_DbStockReports();
	    $id=$this->getRequest()->getParam('id');
	    $id = empty($id)?0:$id;
	    $row = $db->getTransferRow($id);
	    if (empty($row)){
	      Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/report/stockreport/rpt-transfer",2);
	      exit();
	    }
	    $this->view->row = $row;
	    $this->view->rowdetail = $db->getTransferAllRow($id);
	  
	  }catch (Exception $e){
	    Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    Application_Form_FrmMessage::message("APPLICATION_ERROR");
	  }
	  
	  $frmpopup = new Application_Form_FrmPopupGlobal();
	  $this->view->printByFormat = $frmpopup->printByFormat();
	
}
 

	public function rptAdjustAction(){
		try{
			if($this->getRequest()->isPost()){
			  $search = $this->getRequest()->getPost();
			}
			else{
			  $search=array(
				  'adv_search'=>"",
				  'branch_id' => -1,
				  'start_date'=> date('Y-m-d'),
				  'end_date'=>date('Y-m-d'),
				  'status'=>-1,
				);
			}
	  
		$this->view->search = $search;
	    $db = new Report_Model_DbTable_DbStockReports();
	    $rs_rows = $db->getAllAdjustStock($search);
		$this->view->rows=$rs_rows;
		
	}catch (Exception $e){
		 Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		 Application_Form_FrmMessage::message("APPLICATION_ERROR");
	}
	
	$frm_search = new Application_Form_FrmAdvanceSearchStock();
	$frm = $frm_search->AdvanceSearch();
	Application_Model_Decorator::removeAllDecorator($frm);
	$this->view->frm_search = $frm;
	
	$frmpopup = new Application_Form_FrmPopupGlobal();
	$this->view->footerReport = $frmpopup->getFooterReport();
	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  }
    public function rptReceiveLetterAction(){
		try{
			$db = new Report_Model_DbTable_DbStockReports();
			$id=$this->getRequest()->getParam('id');
			$id = empty($id)?0:$id;
			$row = $db->getReceivedTransferRow($id);
			if (empty($row)){
			  Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/report/stockreport/rpt-received-transfer",2);
			  exit();
			}
			$this->view->row = $row;
			$this->view->rowdetail = $db->getReceiveTransferDetail($id);
		  
		  }catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		  }
		  
		  $frmpopup = new Application_Form_FrmPopupGlobal();
		  $this->view->printByFormat = $frmpopup->printByFormat();
		
	}
	
	public function rptReceivedTransferAction(){
		try{
		  if($this->getRequest()->isPost()){
			  $search = $this->getRequest()->getPost();
			}
			else{
			  $search=array(
				  'adv_search'=>"",
				  'branch_id' => -1,
				  'start_date'=> date('Y-m-d'),
				  'end_date'=>date('Y-m-d'),
				  'status'=>-1,
				);
			}
			$this->view->search = $search;
			$db = new Report_Model_DbTable_DbStockReports();
			$rs_rows = $db->getAllReceiveTransferStock($search);
			$this->view->rows=$rs_rows;
			$this->view->search=$search;
			
		}catch (Exception $e){
			  Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			  Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
		
			$frm_search = new Application_Form_FrmAdvanceSearchStock();
			$frm = $frm_search->AdvanceSearch();
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm_search = $frm;
			
			$frmpopup = new Application_Form_FrmPopupGlobal();
			$this->view->footerReport = $frmpopup->getFooterReport();
			$this->view->headerReport = $frmpopup->getLetterHeadReport();
	}


}