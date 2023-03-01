<?php
class Setting_importController extends Zend_Controller_Action {
	
    public function init()
    {    	
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			include  PUBLIC_PATH.'/Classes/PHPExcel/IOFactory.php';
			$db=new Setting_Model_DbTable_DbImport();
			if($this->getRequest()->isPost()){
				$data=$this->getRequest()->getPost();
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
// 				$db->ImportPPLand($sheetData);
				//$db->ImportADLand($sheetData);
				//$db->updateItemsByImport($sheetData);
				//$db->importHanuman($sheetData);
				$db->insertPayment($sheetData);
				
				
				Application_Form_FrmMessage::message("Import Successfully");
			}
		}catch (Exception $e){
			echo $e->getMessage();exit();
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$land_string = 'A+B+C';
		$str_arr = explode ("+", $land_string);
		print_r(count($str_arr));
	}
}