<?php
class Project_importController extends Zend_Controller_Action {
	
	const REDIRECT_URL_ADD ='/items/measure/add';
	const REDIRECT_URL_CLOSE ='/items/measure/index';
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			include  PUBLIC_PATH.'/Classes/PHPExcel/IOFactory.php';
			$db=new Project_Model_DbTable_DbImport();
			if($this->getRequest()->isPost()){
				$data=$this->getRequest()->getPost();
				$branch_id = empty($data['branch_id'])?0:$data['branch_id'];
				$adapter = new Zend_File_Transfer_Adapter_Http();
				$part= PUBLIC_PATH.'/images';
				$adapter->setDestination($part);
				$adapter->receive();
				$file = $adapter->getFileInfo();
				$inputFileName = $file['file_excel']['tmp_name'];
 				try {
					$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
				} catch(Exception $e) {
					die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
				}
				
				$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
				$db->updateItemsByImport($sheetData,$branch_id);
				Application_Form_FrmMessage::message("Import Successfully");
			}
			else{
			}
			
			$frm = new Project_Form_FrmImport();
			$frm->FrmImport(null);
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm = $frm;
			
		}catch (Exception $e){
			echo $e->getMessage();exit();
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	
}

