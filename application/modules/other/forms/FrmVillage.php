<?php 
Class Other_Form_FrmVillage extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate =null;//text validate
	protected $filter=null;
	protected $t_num=null;
	protected $text=null;
	protected $tarea=null;
	protected $date;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->tarea = 'dijit.form.SimpleTextarea';
		$this->date = 'dijit.form.DateTextBox';
	}
	public function FrmAddVillage($data=null){
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>$this->tvalidate,
				'onkeyup'=>'this.submit()',
				'placeholder'=>$this->tr->translate("SEARCH_VILLAGE_INFO")
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
		
		$village_name = new Zend_Dojo_Form_Element_ValidationTextBox('village_name');
		$village_name->setAttribs(array('dojoType'=>'dijit.form.ValidationTextBox',
				'required'=>'true','missingMessage'=>'Invalid Module!','class'=>'fullside'
		));
		$village_namekh = new Zend_Dojo_Form_Element_ValidationTextBox('village_namekh');
		$village_namekh->setAttribs(array('dojoType'=>'dijit.form.ValidationTextBox',
				'required'=>'true','missingMessage'=>'Invalid Module!','class'=>'fullside'
		));
		
		$code = new Zend_Dojo_Form_Element_ValidationTextBox('code');
		$code->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'missingMessage'=>'Invalid Module!','class'=>'fullside'
		));
		
		$_db = new Application_Model_DbTable_DbGlobal();
		
// 		$rows_provice = $_db->getCommune();
// 		$opt_commune = "";
// 		if(!empty($rows_provice))foreach($rows_provice AS $row) $opt_commune[$row['com_id']]=$row['commune_name'];
// 		$commune_name = new Zend_Dojo_Form_Element_FilteringSelect('commune_name');
// 		$commune_name->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside'
// 		));
		
// 		$commune_name->setMultiOptions($opt_commune);
// 		$commune_name->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'required'=>'true',
// 				'class'=>'fullside',));
		
// 		$popup_commune_name = new Zend_Dojo_Form_Element_FilteringSelect('popup_commune_name');
// 		$popup_commune_name->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside'
// 		));
// 		$popup_commune_name->setMultiOptions($opt_commune);
		
// 		$rows_provice = $_db->getAllDistrict();
// 		$opt_province = array($this->tr->translate("SELECT_DISTRICT"));
// 		if(!empty($rows_provice))foreach($rows_provice AS $row) $opt_province[$row['dis_id']]=$row['district_name'];
// 		$district_name = new Zend_Dojo_Form_Element_FilteringSelect('district_name');
// 		$district_name->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside'
// 		));
		
		$_display =  new Zend_Dojo_Form_Element_FilteringSelect('display');
		$_display->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$_display_opt = array(
				1=>$this->tr->translate("NAME_KHMER"),
				2=>$this->tr->translate("NAME_EN"));
		$_display->setMultiOptions($_display_opt);
		
// 		$district_name->setMultiOptions($opt_province);
// 		$district_name->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'required'=>'true',
// 				'class'=>'fullside',));
		
// 		$rows_provice = $_db->getAllDistrict();
// 		$opt_province = array($this->tr->translate("SELECT_DISTRICT"));
// 		if(!empty($rows_provice))foreach($rows_provice AS $row) $opt_province[$row['dis_id']]=$row['district_name'];
// 		$district_name = new Zend_Dojo_Form_Element_FilteringSelect('district_name');
// 		$district_name->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside'
// 		));
		
// 		$district_name->setMultiOptions($opt_province);
// 		$district_name->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'required'=>'true',
// 				'class'=>'fullside',));
		
		
		
		
		$rows_provice = $_db->getAllProvince();
		$opt_province = array($this->tr->translate("SELECT_PROVINCE"));
		if(!empty($rows_provice))foreach($rows_provice AS $row) $opt_province[$row['province_id']]=$row['province_en_name'];
		$_province = new Zend_Dojo_Form_Element_FilteringSelect('province_name');
		
		$_province->setMultiOptions($opt_province);
		$_province->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'class'=>'fullside',
				'onchange'=>'filterDistrict();',
				'invalidMessage'=>false
				));
		$_province->setValue($request->getParam("province_name"));
		
		$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DACTIVE"));
		$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$_status->setMultiOptions($_arr);
		$_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside'));
		$_fromdate = new Zend_Dojo_Form_Element_DateTextBox('from_date');
		$_fromdate->setAttribs(array('dojoType'=>$this->date,
				'class'=>'fullside',
				'onchange'=>'CalculateDate();'));
		$_date = $request->getParam("start_date");

		if(empty($_date)){
			$_date = date('Y-m-01');
		}
		$_fromdate->setValue($_date);
		
		
		$_todate = new Zend_Dojo_Form_Element_DateTextBox('to_date');
		$_todate->setAttribs(array('dojoType'=>$this->date,'class'=>'fullside',
		));
		
		$_date = $request->getParam("end_date");
		
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$_todate->setValue($_date);
		$id = new Zend_Form_Element_Hidden('id');
		if(!empty($data)){
			$id->setValue($data['vill_id']);
			$village_name->setValue($data['village_name']);
			$village_namekh->setValue($data['village_namekh']);
			$_display->setValue($data['displayby']);
			$_province->setValue($data['pro_id']);
			$code->setValue($data['code']);
// 			$commune_name->setValue($data['commune_id']);
			$_status->setValue($data['status']);
			
			
		}
		$this->addElements(array($code,$_fromdate,$_todate,$_btn_search,$_status_search,$_title,$id,$village_name,$_province, $_status,$village_namekh,$_display));
		return $this;
		
	}
	
	
}