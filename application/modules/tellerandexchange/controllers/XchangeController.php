<?php

class Tellerandexchange_XchangeController extends Zend_Controller_Action
{
	const REDIRECT_URL='/tellerandexchange/xchange';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL') || define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Tellerandexchange_Model_DbTable_Dbexchange();
// 			if($this->getRequest()->isPost()){
// 				$search=$this->getRequest()->getPost();
// 			}
// 			else{
// 				$search = array(
// 						'client_id' => -1,
// 						'status' => -1,
// 						'from_date' =>date('Y-m-d'),
// 						'to_date' => date('Y-m-d'),
// 				);
// 			}
			$rs_rows= $db->getAllExchangeListMulti();
			$rs_row =array();
			foreach ($rs_rows AS $key => $rs){
				$rs_row[$key]=array(
						'id'=>$rs['id'],
						'client_name'=>$rs['client_name'],
						'date'=>$rs['date'],
						'invoice_code'=>$rs['invoice_code'],
						'from_to'=>'',//$rs['from_to'],
						'from_amount'=>'',//$rs['from_amount'],
						'exchange_rate'=>'',//$rs['exchange_rate'],
						'to_amount'=>'',//$rs['to_amount'],
						'receive_dollar'=>$rs['receive_dollar'],
						'return_dollar'=>$rs['return_dollar'],
						'exchange_type'=>$rs['exchange_type'],
						'status'=>$rs['status'],
						);
				
				$sub_transac = $db->getExchangeDetail($rs['id']);//for get receiver
				$total_from_d = 0;$total_from_r=0;$total_from_b=0;
				$total_to_d = 0;$total_to_r=0;$total_to_b=0;
				foreach ($sub_transac as $j => $s_tran){
					if($s_tran['from_currency_type']==2){
						$total_from_d = $total_from_d+$s_tran['from_amount'];
					}elseif($s_tran['from_currency_type']==1){
						$total_from_r = $total_from_r+$s_tran['from_amount'];
					}elseif($s_tran['from_currency_type']==3){
						$total_from_b = $total_from_b+$s_tran['from_amount'];
					}
					if($s_tran['to_currency_type']==2){
						$total_to_d = $total_to_d+$s_tran['to_amount'];
					}elseif($s_tran['to_currency_type']==1){
						$total_to_r = $total_to_r+$s_tran['to_amount'];
					}elseif($s_tran['to_currency_type']==3){
						$total_to_b = $total_to_b+$s_tran['to_amount'];
					}
// 						$rs_row[$key]['from_to'].=$s_tran['from_to'].$s_tran['from_name'].",";
// 						$rs_row[$key]['to_amount'].=$s_tran['to_amount'].$s_tran['to_name'].",";
						$rs_row[$key]['exchange_rate'].=$s_tran['exchange_rate'].",";
				}
				
				$total_from_r = empty($total_from_r)?'':$total_from_r.$s_tran['from_name'];
				$total_from_b = empty($total_from_b)?'':$total_from_b.$s_tran['from_name'];
				$total_from_d = (empty($total_from_d)?'':$total_from_d.$s_tran['from_name']);
				
				$total_to_d = empty($total_to_d)?'':$total_to_d.$s_tran['to_name'].",";
				$total_to_b = empty($total_to_b)?'':$total_to_b.$s_tran['to_name'].",";
				$total_to_r = (empty($total_to_r)?'':$total_to_r.$s_tran['to_name'].",");
				
				$rs_row[$key]['from_amount']=$total_from_d.$total_from_r.$total_from_b;//;
				$rs_row[$key]['to_amount']=$total_to_d.$total_to_b.$total_to_r;//;
				
			}

			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_row, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("ឈ្មោះអតិថិជន","ថ្ងៃ​ប្រតិបត្តិ","វិ.បត្រ","ការប្តូរប្រាក់","ទិញចូល","អត្រាប្តូរប្រាក់","លក់ចេញ","ប្រាក់​ទទួល​បាន​","ប្រាក់​អាប់","TYPE","STATUS");
			$link=array(
					'module'=>'tellerandexchange','controller'=>'exchanges','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('client_name'=>$link,'invoice_code'=>$link,'date'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Application_Form_FrmAdvanceSearch();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
	}
	public function addAction()
	{
		    	if($this->getRequest()->isPost()){
					$data=$this->getRequest()->getPost();
// 					print_r($data);exit();
					try {
						//$db = new tellerandexchange_m();
						$db = $db->addXchangeMoney($data);
						Application_Form_FrmMessage::Sucessfull('', self::REDIRECT_URL);
					} catch (Exception $e) {
						Application_Form_FrmMessage::message("INSERT_FAIL");
						$err = $e->getMessage();
						echo $err;exit();
						Application_Model_DbTable_DbUserLog::writeMessageError($err);
					}
				}
		$pructis=new Tellerandexchange_Form_Frmxchange();
		$frm = $pructis->xchange();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm=$frm;
		$db = new Application_Model_DbTable_DbGlobal();
	    $this->view->currency_type = $db->CurruncyTypeOption();
	    
	    $session_user=new Zend_Session_Namespace('auth');
	    $this->view->user_name = $session_user->last_name .' '. $session_user->first_name;
	    
	    $db_keycode = new Application_Model_DbTable_DbKeycode();
	    $this->view->keycode = $db_keycode->getKeyCodeMiniInv();
	    
	    $db_g = new Application_Model_DbTable_DbGlobal();
	    $this->view->inv_no = $db_g ->getNewInvoiceExchange();
	    
	
// 		$db = new Application_Model_DbTable_DbGlobal();
// 		$this->view->currency_type = $db->CurruncyTypeOption();
	}
 	public function checkRateAction(){
    	$db_rate=new Application_Model_DbTable_DbRate();
    	echo $db_rate->getCurrentRateJson();
    	exit();
    }

}