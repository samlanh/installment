<?php 
class Dailywork_Form_FrmResult extends Zend_Form
{
	protected $tr;
	public function init()
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	/////////////	Form Product		/////////////////
	public function add($data=null){
		$_employeename= new Zend_Dojo_Form_Element_TextBox('employeename');
		$_employeename->setAttribs(array('dojoType'=>'dijit.form.TextBox','required'=>'true','missingMessage'=>'Invalid Module!','class'=>'fullside'
				
		));
		$_emailemployee= new Zend_Dojo_Form_Element_TextBox('emailemployee');
		$_emailemployee->setAttribs(array('dojoType'=>'dijit.form.TextBox','required'=>'true','missingMessage'=>'Invalid Module!','class'=>'fullside'
		
		));
		$_workresult= new Zend_Dojo_Form_Element_TextBox('workresult');
		$_workresult->setAttribs(array('dojoType'=>'dijit.form.TextBox','required'=>'true','missingMessage'=>'Invalid Module!','class'=>'fullside'
		
		));
		if(!empty($data)){
			//print_r($data); exit();
			$_employeename->setValue($data['name']);
			$_emailemployee->setValue($data['email']);
			$_workresult->setValue($data['result']);
			
		}
		$this->addElements(array($_employeename,$_emailemployee,$_workresult));
		return $this;
		
	}
	function productFilter(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Product_Model_DbTable_DbProduct();
		$ad_search = new Zend_Form_Element_Text("ad_search");
		$ad_search->setAttribs(array(
				'class'=>'form-control',
		));
		$ad_search->setValue($request->getParam("ad_search"));
		
		$branch = new Zend_Form_Element_Select("branch");
		$opt = array(''=>$tr->translate("SELECT_BRANCH"));
		if(!empty($db->getBranch())){
			foreach ($db->getBranch() as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$branch->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$branch->setMultiOptions($opt);
		$branch->setValue($request->getParam("branch"));
		
		$status = new Zend_Form_Element_Select("status");
		$opt = array('1'=>$tr->translate("ACTIVE"),'2'=>$tr->translate("DEACTIVE"));
		$status->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$status->setMultiOptions($opt);
		$status->setValue($request->getParam("status"));
		
		$opt = array(''=>$tr->translate("SELECT_BRAND"));
		$brand = new Zend_Form_Element_Select("brand");
		$brand->setAttribs(array(
				'class'=>'form-control select2me',
		));
		if(!empty($db->getBrand())){
			foreach ($db->getBrand() as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$brand->setMultiOptions($opt);
		$brand->setValue($request->getParam("brand"));
			
		$opt = array(''=>$tr->translate("SELECT_MODEL"));
		$model = new Zend_Form_Element_Select("model");
		$model->setAttribs(array(
				'class'=>'form-control select2me',
		));
		if(!empty($db->getModel())){
			foreach ($db->getModel() as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$model->setMultiOptions($opt);
		$model->setValue($request->getParam("model"));
			
		$opt = array(''=>$tr->translate("SELECT_CATEGORY"));
		$category = new Zend_Form_Element_Select("category");
		$category->setAttribs(array(
				'class'=>'form-control select2me',
		));
		if(!empty($db->getCategory())){
			foreach ($db->getCategory() as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$category->setMultiOptions($opt);
		$category->setValue($request->getParam("category"));
		
		$opt = array(''=>$tr->translate("SELECT_COLOR"));
		$color = new Zend_Form_Element_Select("color");
		$color->setAttribs(array(
				'class'=>'form-control select2me',
		));
		if(!empty($db->getColor())){
			foreach ($db->getColor() as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$color->setMultiOptions($opt);
		$color->setValue($request->getParam("color"));
			
		$opt = array(''=>$tr->translate("SELECT_SIZE"));
		$size = new Zend_Form_Element_Select("size");
		$size->setAttribs(array(
				'class'=>'form-control select2me',
		));
		if(!empty($db->getSize())){
			foreach ($db->getSize() as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$size->setMultiOptions($opt);
		$size->setValue($request->getParam("size"));
		
		$this->addElements(array($ad_search,$branch,$brand,$model,$category,$color,$size,$status));
		return $this;
	}
}