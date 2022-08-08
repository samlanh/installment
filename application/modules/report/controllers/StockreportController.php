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
        $db = new Report_Model_DbTable_DbAccountant();
        $rs_rows = $db->getAllPurchasing($search);
          $this->view->row=$rs_rows;
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

public function rptusageAction(){

  
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
        $db = new Report_Model_DbTable_DbAccountant();
        $rs_rows = $db->getAllPurchasing($search);
          $this->view->row=$rs_rows;
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


public function rptUsagedetailAction(){
  try{
    $db = new Report_Model_DbTable_DbStockMg();
    $id=$this->getRequest()->getParam('id');
      $id = empty($id)?0:$id;
    $row = $db->getRequestPOById($id);
    if (empty($row)){
      Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/requesting/request");
      exit();
    }
    $this->view->row = $row;
    $this->view->rowdetail = $db->getRequestPODetailById($row);
  
  }catch (Exception $e){
    Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    Application_Form_FrmMessage::message("APPLICATION_ERROR");
  }
  
  $frmpopup = new Application_Form_FrmPopupGlobal();
  $this->view->printByFormat = $frmpopup->printByFormat();
}


}