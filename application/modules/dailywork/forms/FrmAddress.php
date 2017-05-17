<?php 
class Dailywork_Form_FrmAddress extends Zend_Form
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
		$db = new Dailywork_Model_DbTable_DbContact();
		$row = $db->getCategoryname();
		$_name= new Zend_Dojo_Form_Element_TextBox('name');
		$_name->setAttribs(array('required'=>'true','missingMessage'=>'Invalid Module!','class'=>'form-control'
				
		));
		$_phone= new Zend_Dojo_Form_Element_TextBox('phone');
		$_phone->setAttribs(array('required'=>'true','missingMessage'=>'Invalid Module!','class'=>'form-control'
		
		));
		$_note= new Zend_Dojo_Form_Element_Textarea('note');
		$_note->setAttribs(array('required'=>'true','missingMessage'=>'Invalid Module!','class'=>'form-control'
					,"style"=>'height:170px;'
		));
		$_arr = array(1=>$this->tr->translate("SOCIAL"),2=>$this->tr->translate("ONLINE"),3=>$this->tr->translate("NETWORK"),4=>$this->tr->translate("DIRECT"),5=>$this->tr->translate("OTHER"));
		$_notewhere = new Zend_Dojo_Form_Element_FilteringSelect("notewhere");
		$_notewhere->setMultiOptions($_arr);
		$_notewhere->setAttribs(array(
				'required'=>'true',
				'class'=>'form-control'));
		
		$_address= new Zend_Dojo_Form_Element_TextBox('address');
		$_address->setAttribs(array('required'=>'true','missingMessage'=>'Invalid Module!','class'=>'form-control'
		
		));
		
		$opt_u = array(''=>"select Category");
		if(!empty($row)){
			foreach ($row as $rs){
				$opt_u[$rs["id"]] = $rs["name"];
			}
		}
		
		//$_arr = array(1=>$this->tr->translate("WEBSITE"),2=>$this->tr->translate("SYSTEM"));
		$_category = new Zend_Dojo_Form_Element_FilteringSelect("category");
		$_category->setMultiOptions($opt_u);
		$_category->setAttribs(array(
				'required'=>'true',
				'class'=>'form-control'));
		
		$_arr = array(1=>$this->tr->translate("OPEN"),2=>$this->tr->translate("MEETING"),3=>$this->tr->translate("FOLLOWING"),4=>$this->tr->translate("INPROGRESS"),5=>$this->tr->translate("COMPLETED"),6=>$this->tr->translate("CANCEL"));
		$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$_status->setMultiOptions($_arr);
		$_status->setAttribs(array(
				'required'=>'true',
				'class'=>'form-control'));
		if(!empty($data)){
			//print_r($data); exit();
			$_name->setValue($data['contactname']);
			$_phone->setValue($data['contactnumber']);
			$_note->setValue($data['note']);
			$_notewhere->setValue($data['wherenote']);
			$_address->setValue($data['address']);
			$_status->setValue($data['status']);
			//$_category->setValue($data['category']);
		}
		$this->addElements(array($_category,$_name,$_phone,$_note,$_notewhere,$_address,$_status));
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