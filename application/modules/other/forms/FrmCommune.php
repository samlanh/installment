<?php 
Class Other_Form_FrmCommune extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate =null;//text validate
	protected $filter=null;
	protected $t_num=null;
	protected $text=null;
	protected $tarea=null;
	
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->tarea = 'dijit.form.SimpleTextarea';
	}
	public function FrmAddCommune($data=null){
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>$this->tvalidate,
				'onkeyup'=>'this.submit()',
				'placeholder'=>$this->tr->translate("SEARCH_COMMUNE_INFO")
		));
		$_title->setValue($request->getParam("adv_search"));
		
		$_status_search=  new Zend_Dojo_Form_Element_FilteringSelect('search_status');
		$_status_search->setAttribs(array('dojoType'=>$this->filter));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status_search->setMultiOptions($_status_opt);
		$_status_search->setValue($request->getParam("search_status"));
		
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch',
		
		));
		
		$commune = new Zend_Dojo_Form_Element_TextBox('commune_name');
		$commune->setAttribs(array('dojoType'=>'dijit.form.ValidationTextBox',
				'required'=>'true','missingMessage'=>'Invalid Module!','class'=>'fullside'
		));
		
		$commune_en = new Zend_Dojo_Form_Element_TextBox('commune_nameen');
		$commune_en->setAttribs(array('dojoType'=>'dijit.form.ValidationTextBox',
				'required'=>'true','missingMessage'=>'Invalid Module!','class'=>'fullside'
		));
		
		$code = new Zend_Dojo_Form_Element_TextBox('code');
		$code->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'missingMessage'=>'Invalid Module!','class'=>'fullside'
		));
		
		$communekh = new Zend_Dojo_Form_Element_TextBox('commune_namekh');
		$communekh->setAttribs(array('dojoType'=>'dijit.form.ValidationTextBox',
				'required'=>'true','missingMessage'=>'Invalid Module!','class'=>'fullside'
		));
		$_display =  new Zend_Dojo_Form_Element_FilteringSelect('display');
		$_display->setAttribs(array('
				dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				));
		$_display_opt = array(
				1=>$this->tr->translate("NAME_KHMER"),
				2=>$this->tr->translate("NAME_EN"));
		$_display->setMultiOptions($_display_opt);
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$rows_district = $_db->getAllDistrict();
		$opt_district = "";
		if(!empty($rows_district)) foreach($rows_district AS $row) $opt_district[$row['dis_id']]=$row['district_name'];
		
		$district_name = new Zend_Dojo_Form_Element_FilteringSelect('district_name');
		$district_name->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside'
		));
		$district_name->setMultiOptions($opt_district);
		$district_nameen = new Zend_Dojo_Form_Element_FilteringSelect('district_nameen');
		$district_nameen->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside'
		));
		$district_nameen->setMultiOptions($opt_district);
		
		$_db = new Application_Model_DbTable_DbGlobal();		
		$rows_provice = $_db->getAllProvince();
		$opt_province = array($this->tr->translate("SELECT_PROVINCE"));
		if(!empty($rows_provice))foreach($rows_provice AS $row) $opt_province[$row['province_id']]=$row['province_en_name'];
		$_province = new Zend_Dojo_Form_Element_FilteringSelect('province_name');
		$_province->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'filterDistrict();',
		));
		$_province->setMultiOptions($opt_province);
// 		$_province->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'required'=>'true',
// 				'class'=>'fullside',));
		//$rows_provicess = $_db->getAllProvince();
		//$opt_provicess = array($this->tr->translate("SELECT_PROVINCE"));
		
		
		
		$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DACTIVE"));
		$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$_status->setMultiOptions($_arr);
		$_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside'));
		if(!empty($data)){
			$commune->setValue($data['commune_name']);
			$code->setValue($data['code']);
// 			echo $data['commune_namekh'];exit();
			$communekh->setValue($data['commune_namekh']);
			$_display->setValue($data['displayby']);
			$district_name->setValue($data['district_id']);
			$_province->setValue($data['pro_id']);
			$_status->setValue($data['status']);
		}
		$this->addElements(array($code,$district_nameen,$commune_en,$_btn_search,$_status_search,$_title,$commune,$district_name,$communekh,$_province, $_status, $_display));
		return $this;
		
	}
	
	
}