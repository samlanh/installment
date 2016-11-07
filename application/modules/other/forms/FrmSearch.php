<?php 
Class Other_Form_FrmSearch extends Zend_Dojo_Form{
	protected $tr = null;
	protected $btn =null;//text validate
	protected $filter = null;
	protected $text =null;
	protected $validate = null;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->btn = 'dijit.form.Button';
		$this->validate = 'dijit.form.ValidationTextBox';
	}
	public function FrmDepartment($_data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,
				'placeholder'=>$this->tr->translate("SEARCH_FACULTY_NAME")));
		$_title->setValue($request->getParam("title"));
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
	
		$this->addElements(array($_title,$_status));
	
		return $this;
	}
	
	public function FrmMajors($_data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,
				'placeholder'=>$this->tr->translate("INPUT_FACULTY_MAJOR")));
		$_title->setValue($request->getParam("title"));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
		
		$this->addElements(array($_title,$_status));
		
		return $this;
	}
	
	public function FrmSetting($_data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,
				'placeholder'=>$this->tr->translate("INPUT_LABEL_VALUE")));
		$_title->setValue($request->getParam("title"));
	
		$this->addElements(array($_title));
		return $this;
	}
	public function FrmAddSetting($_data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_keyname = new Zend_Dojo_Form_Element_TextBox('key_name');
		$_keyname->setAttribs(array('dojoType'=>$this->validate,
				'required'=>'true','class'=>'full',
				'placeholder'=>$this->tr->translate("INPUT_KEY_SETTING")));
	
		$_keyvalue = new Zend_Dojo_Form_Element_TextBox('key_value');
		$_keyvalue->setAttribs(array('dojoType'=>$this->validate,'class'=>'full',
				'required'=>'true',
				'placeholder'=>$this->tr->translate("INPUT_VALUE_SETTING")));
		
		$_id = new Zend_Form_Element_Hidden('id');
	
		$this->addElements(array($_keyname,$_keyvalue,$_id));
		if(!empty($_data)){
			$_id->setValue($_data['code']);
			$_keyname->setValue($_data['keyName']);
			$_keyname->setAttrib("ReadOnly", true);
			$_keyvalue->setValue($_data['keyValue']);
		}
	
		return $this;
	}
	public function frmSearchTeacher($_data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,
				'placeholder'=>$this->tr->translate("SEARCH_BY_TEACHER_NAME")));
		$_title->setValue($request->getParam('title'));
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$rows = $_db->getGlobalDb("SELECT DISTINCT subject_name_en FROM rms_teacher WHERE teacher_name_en!='' AND subject_name_en!=''");
		$opt = array(-1=>$this->tr->translate("CHOOSE_SUJECT"));
		if(!empty($rows))foreach($rows AS $row) $opt[$row['subject_name_en']]=$row['subject_name_en'];
		$_subject = new Zend_Dojo_Form_Element_FilteringSelect('subjec_name');
		$_subject->setMultiOptions($opt);
		$_subject->setAttribs(array(
				'dojoType'=>$this->filter,
				'required'=>'true',
				'placeholder'=>$this->tr->translate("INPUT_VALUE_SETTING")));
		$_subject->setValue($request->getParam('subjec_name'));
		
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
	
	
		$this->addElements(array($_title,$_subject,$_status));
		if(!empty($_data)){
		}
	
		return $this;
	}
	public function searchRoom(){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,
				'placeholder'=>$this->tr->translate("SEARCH_ROOM_TITLE")));
		$_title->setValue($request->getParam("title"));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
		$this->addElements(array($_title,$_status));
		
		return $this;
	}
	public function SubjectExam(){
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,
				'placeholder'=>$this->tr->translate("SEARCH_SUBJECT_TITLE")));
		$_title->setValue($request->getParam("title"));
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
		$this->addElements(array($_title,$_status));
	
		return $this;
	}
	public function searchProvinnce(){
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,
				'placeholder'=>$this->tr->translate("SEARCH_PROVINCE_TITLE")));
		$_title->setValue($request->getParam("title"));
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
		$this->addElements(array($_title,$_status));
	
		return $this;
	}
	public function frmServiceType($_data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,
				'placeholder'=>$this->tr->translate("SEARCH_BY_TEACHER_NAME")));
		$_title->setValue($request->getParam('title'));
	
		$_type = new Zend_Dojo_Form_Element_FilteringSelect('type');
		$_status_type = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("SERVICE"),
				2=>$this->tr->translate("PROGRAM"));
		$_type->setMultiOptions($_status_type);
		$_type->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("SEARCH_BY_TYPE")));
	
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
	
	
		$this->addElements(array($_title,$_type,$_status));
		if(!empty($_data)){
		}
	
		return $this;
	}
	public function frmSearchServiceProgram($_data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,
				'placeholder'=>$this->tr->translate("SEARCH_BY_TEACHER_NAME")));
		$_title->setValue($request->getParam('title'));
		
		$db =new Application_Model_DbTable_DbGlobal();
		$row = $db->getServiceType();
		$_cate_opt="";
		$_cate_name = new Zend_Dojo_Form_Element_FilteringSelect('cate_name');
		if(!empty($row)){
			foreach($row as $rs)$_cate_opt[$rs['id']]=$rs['title'];
			$_cate_name->setMultiOptions($_cate_opt);
		}
		
		$_cate_name->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("SEARCH_BY_TYPE")));
	
		$_type = new Zend_Dojo_Form_Element_FilteringSelect('type');
		$_status_type = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("SERVICE"),
				2=>$this->tr->translate("PROGRAM"));
		$_type->setMultiOptions($_status_type);
		$_type->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("SEARCH_BY_TYPE")));
	
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
	
	
		$this->addElements(array($_cate_name,$_title,$_type,$_status));
		if(!empty($_data)){
		}
	
		return $this;
	}
	public function frmSearchTutionFee($_data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_title = new Zend_Dojo_Form_Element_TextBox('fee_code');
		$_title->setAttribs(array('dojoType'=>$this->text,
				'placeholder'=>$this->tr->translate("SEARCH_BY_CODE")));
		$_title->setValue($request->getParam('title'));
		
		$_batch = new Zend_Dojo_Form_Element_TextBox('batch');
		$_batch->setAttribs(array('dojoType'=>$this->text,
				'placeholder'=>$this->tr->translate("SEARCH_BY_BATCH")));
		$_batch->setValue($request->getParam('title'));
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
		
		$db = new Application_Model_DbTable_DbGlobal();
		$rows = $db->getAllFecultyName();
		$opt = array(-1=>$this->tr->translate("CHOOSE"));
		if(!empty($rows))foreach ($rows as $row)$opt[$row['dept_id']]=$row['en_name'];
		$_faculty=new Zend_Dojo_Form_Element_FilteringSelect('faculty');
		$_faculty->setAttribs(array('dojoType'=>$this->filter,));
		$_faculty->setMultiOptions($opt);
		$_faculty->setValue($request->getParam("faculty"));
		
		$row = $db ->getAllDegree();
		//$row=array(-1=>$this->tr->translate("CHOOSE"));
		$_degree =  new Zend_Dojo_Form_Element_FilteringSelect('degree');
		$_degree->setAttribs(array('dojoType'=>$this->filter,));
		$_degree->setMultiOptions($row);
		
		$row = $db->getAllMention();
		array_unshift($row, array(-1=>$this->tr->translate("CHOOSE")));
		$_metion =  new Zend_Dojo_Form_Element_FilteringSelect('metion');
		$_metion->setAttribs(array('dojoType'=>$this->filter,));
		$_metion->setMultiOptions($row);
	
	
		$this->addElements(array($_degree,$_batch,$_faculty,$_title,$_status,$_metion));
		if(!empty($_data)){
		}
		return $this;
	}
	public function frmSearchServiceChageFee($_data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_code = new Zend_Dojo_Form_Element_TextBox('service_code');
		$_code->setAttribs(array('dojoType'=>$this->text,
				'placeholder'=>$this->tr->translate("SEARCH_BY_CODE")));
		$_code->setValue($request->getParam('service_code'));
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
	
		$db = new Application_Model_DbTable_DbGlobal();
		$rows = $db->getServiceType();
		$opt = array(-1=>$this->tr->translate("CHOOSE"));
		if(!empty($rows))foreach ($rows as $row)$opt[$row['id']]=$row['title'];
		$_cate_type=new Zend_Dojo_Form_Element_FilteringSelect('cate_type');
		$_cate_type->setAttribs(array('dojoType'=>$this->filter,));
		$_cate_type->setMultiOptions($opt);
		$_cate_type->setValue($request->getParam("cate_type"));
	
		$rows = $db ->getAllServiceItemsName(0);
		$_service_name =  new Zend_Dojo_Form_Element_FilteringSelect('service_name');
		$_service_name->setAttribs(array('dojoType'=>$this->filter,));
		$opt = array(-1=>$this->tr->translate("CHOOSE"));
		if(!empty($rows))foreach ($rows as $row)$opt[$row['id']]=$row['title'];
		$_service_name->setMultiOptions($opt);
		$_service_name->setValue($request->getParam("service_name"));
	
		$this->addElements(array($_service_name,$_cate_type,$_code,$_status));
		if(!empty($_data)){
		}
		return $this;
	}
}