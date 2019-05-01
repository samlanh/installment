<?php 
Class Group_Form_FrmClient extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddClient($data=null){
		
		$clienttype_nameen= new Zend_Dojo_Form_Element_DateTextBox('clienttype_nameen');
		$clienttype_nameen->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside'
		));
		$clienttype_namekh= new Zend_Dojo_Form_Element_DateTextBox('clienttype_namekh');
		$clienttype_namekh->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside'
		));
		$_dob= new Zend_Dojo_Form_Element_DateTextBox('dob_client');
		$_dob->setAttribs(array('dojoType'=>'dijit.form.DateTextBox','class'=>'fullside','constraints'=>"{datePattern:'dd/MM/yyyy'}",));
		
		$client_issuedateid= new Zend_Dojo_Form_Element_DateTextBox('client_issuedateid');
		$client_issuedateid->setAttribs(array('dojoType'=>'dijit.form.DateTextBox','class'=>'fullside','constraints'=>"{datePattern:'dd/MM/yyyy'}",));
		
		$join_issuedateid= new Zend_Dojo_Form_Element_DateTextBox('join_issuedateid');
		$join_issuedateid->setAttribs(array('dojoType'=>'dijit.form.DateTextBox','class'=>'fullside','constraints'=>"{datePattern:'dd/MM/yyyy'}",));
		
		
		$dob_buywith= new Zend_Dojo_Form_Element_DateTextBox('dob_buywith');
		$dob_buywith->setAttribs(array('dojoType'=>'dijit.form.DateTextBox','class'=>'fullside','constraints'=>"{datePattern:'dd/MM/yyyy'}",));
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Application_Model_DbTable_DbGlobal();
		
		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'onchange'=>'getClientNo();'
		));
		$rows_branch = $db->getAllBranchName();
		$options_branch=array();
		$options_branch=array();
		if(!empty($rows_branch))foreach($rows_branch AS $row){
			$options_branch[$row['br_id']]=$row['project_name'];
		}
		$branch_id->setMultiOptions($options_branch);
		$branch_id->setValue($request->getParam("branch_id"));
		
		$_member = new Zend_Dojo_Form_Element_FilteringSelect('customer_id');
		$_member->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'onchange'=>'getGroupCode();'
		));
		$db = new Application_Model_DbTable_DbGlobal();
// 		$rows = $db->getClientByType();
		$opt_client=array(-1=>$this->tr->translate("CHOOSE_CUSTOEMR"));
// 		if(!empty($rows))foreach($rows AS $row) $options[$row['client_id']]=$row['name_en'];
// 		$_member->setMultiOptions($options);
		
		$rows = $db->getAllClient();
		if(!empty($rows))foreach($rows AS $row){
			$opt_client[$row['id']]=$row['name'];
		}
		$_member->setMultiOptions($opt_client);
		$_member->setValue($request->getParam("customer_id"));
		
		$_namekh = new Zend_Dojo_Form_Element_TextBox('name_kh');
		$_namekh->setAttribs(array(
						'dojoType'=>'dijit.form.ValidationTextBox',
						'class'=>'fullside',
						'required' =>'true'
		));
		
 		$id_client = $db->getNewClientIdByBranch();
		$_clientno = new Zend_Dojo_Form_Element_TextBox('client_no');
		$_clientno->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readonly'=>'readonly',
				'style'=>'color:red;'
		));
 		$_clientno->setValue($id_client);
	
		$_nameen = new Zend_Dojo_Form_Element_TextBox('name_en');
		$_nameen->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				));
		
		$_join_with = new Zend_Dojo_Form_Element_TextBox('join_with');
		$_join_with->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_join_nation_id = new Zend_Dojo_Form_Element_TextBox('join_nation_id');
		$_join_nation_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_nationality = new Zend_Dojo_Form_Element_TextBox('nationality');
		$_nationality->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$_nationality->setValue("ខ្មែរ");
		
		$p_nationality = new Zend_Dojo_Form_Element_TextBox('p_nationality');
		$p_nationality->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$p_nationality->setValue("ខ្មែរ");
		
		$_sex = new Zend_Dojo_Form_Element_FilteringSelect('sex');
		$_sex->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$opt_status = $db->getVewOptoinTypeByType(11,1);
		unset($opt_status[-1]);
		unset($opt_status['']);
		$_sex->setMultiOptions($opt_status);
		
		$client_d_type = new Zend_Dojo_Form_Element_FilteringSelect('client_d_type');
		$client_d_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		
		$_province = new Zend_Dojo_Form_Element_FilteringSelect('province');
		$_province->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'onchange'=>'filterDistrict();',
		));
		
		$rows =  $db->getAllProvince();
		$options=array($this->tr->translate("SELECT_PROVINCE")); //array(''=>"------Select Province------",-1=>"Add New");
		if(!empty($rows))foreach($rows AS $row) $options[$row['province_id']]=$row['province_en_name'];
		$_province->setMultiOptions($options);
		
		$_house = new Zend_Dojo_Form_Element_TextBox('house');
		$_house->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
		));
		
		$_street = new Zend_Dojo_Form_Element_TextBox('street');
		$_street->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_id_no = new Zend_Dojo_Form_Element_TextBox('id_no');
		$_id_no->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required' =>'true'
		));
		
		$_phone = new Zend_Dojo_Form_Element_TextBox('phone');
		$_phone->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$_email = new Zend_Dojo_Form_Element_TextBox('email');
		$_email->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$photo=new Zend_Form_Element_File('photo');
		$photo->setAttribs(array(
		));
		$job = new Zend_Dojo_Form_Element_TextBox('job');
		$job->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$national_id=new Zend_Dojo_Form_Element_TextBox('national_id');
		$national_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				));
	
		$referecce_national_id=new Zend_Dojo_Form_Element_TextBox('reference_national_id');
		$referecce_national_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_id = new Zend_Form_Element_Hidden("id");
		$_desc = new Zend_Dojo_Form_Element_TextBox('desc');
		$_desc->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside',
				'style'=>'width:96%;min-height:50px;'));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		$_join_type =  new Zend_Dojo_Form_Element_TextBox('join_type');
		$_join_type->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside',));
		
		$_hnamekh = new Zend_Dojo_Form_Element_TextBox('hname_kh');
		$_hnamekh->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				//'required' =>'true'
		));
		
		$_bnamekh = new Zend_Dojo_Form_Element_TextBox('bname_kh');
		$_bnamekh->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				//'required' =>'true'
		));
		
		$_ksex = new Zend_Dojo_Form_Element_FilteringSelect('ksex');
		$_ksex->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$opt_kstatus= $db->getVewOptoinTypeByType(11,1);
		unset($opt_kstatus['-1']);
		unset($opt_kstatus['']);
		$_ksex->setMultiOptions($opt_kstatus);
		
		$_pnameen = new Zend_Dojo_Form_Element_ValidationTextBox('pname_en');
		$_pnameen->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required' =>'true'
		));
		$_cprovince = new Zend_Dojo_Form_Element_FilteringSelect('cprovince');
		$_cprovince->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'dfilterDistrict();',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		
		$rows =  $db->getAllProvince();
		$options=array($this->tr->translate("SELECT_PROVINCE")); //array(''=>"------Select Province------",-1=>"Add New");
		if(!empty($rows))foreach($rows AS $row) $options[$row['province_id']]=$row['province_en_name'];
		$_cprovince->setMultiOptions($options);
		
		$_dstreet = new Zend_Dojo_Form_Element_TextBox('dstreet');
		$_dstreet->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$_ghouse = new Zend_Dojo_Form_Element_TextBox('ghouse');
		$_ghouse->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
		));
		$_lphone = new Zend_Dojo_Form_Element_TextBox('lphone');
		$_lphone->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$_istatus=  new Zend_Dojo_Form_Element_FilteringSelect('istatus');
		$_istatus->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$_istatus_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_istatus->setMultiOptions($_status_opt);
		
		$_edesc = new Zend_Dojo_Form_Element_TextBox('edesc');
		$_edesc->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside',
				'style'=>'width:96%;min-height:50px;'));
		$_vid_no = new Zend_Dojo_Form_Element_TextBox('vid_no');
		$_vid_no->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required' =>'true'
		));
		$_bmember = new Zend_Dojo_Form_Element_FilteringSelect('bgroup_id');
		$_bmember->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'getGroupCode();',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				
		));
		$_rid_no = new Zend_Dojo_Form_Element_TextBox('rid_no');
		$_rid_no->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				//'required' =>'true'
		));
		
		$_arid_no = new Zend_Dojo_Form_Element_TextBox('arid_no');
		$_arid_no->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				//'required' =>'true'
		));
		if($data!=null){
			$branch_id->setValue($data['branch_id']);
			//$branch_id->setAttribs(array("readonly"=>true));
			$_namekh->setValue($data['name_kh']);
			$_nameen->setValue($data['name_en']);
			$_sex->setValue($data['sex']);
			$_province->setValue($data['pro_id']);
			$dob_buywith->setValue($data['dob_buywith']);
// 			$_commune->setValue($data['com_id']);
// 			$_village->setValue($data['village_id']);
			$_house->setValue($data['house']);
			$_street->setValue($data['street']);
			$_id_no->setValue($data['id_number']);
			$_phone->setValue($data['phone']);
			$_email->setValue($data['email']);
			$_desc->setValue($data['remark']);
			$_status->setValue($data['status']);
			$_clientno->setValue($data['client_number']);	
			$photo->setValue($data['photo_name']);
			$_id->setValue($data['client_id']);
			$job->setValue($data['job']);
			$_nationality->setValue($data['nationality']);
			$national_id->setValue($data['nation_id']);
            $client_d_type->setValue($data['client_d_type']);
			$_dob->setValue($data['dob']);
			$_hnamekh->setValue($data['hname_kh']);
			$_bnamekh->setValue($data['bname_kh']);
			$_ghouse->setValue($data['ghouse']);
			$_lphone->setValue($data['lphone']);
			$_dstreet->setValue($data['dstreet']);
			$_rid_no->setValue($data['rid_no']);
			$_arid_no->setValue($data['arid_no']);
			$_edesc->setValue($data['edesc']);
			$_ksex->setValue($data['ksex']);
			$p_nationality->setValue($data['p_nationality']);
			$_cprovince->setValue($data['cprovince']);
// 			$_adistrict->setValue($data['adistrict']);
// 			$_dcommune->setValue($data['dcommune']);
			$_join_type->setValue($data['join_type']);
			$referecce_national_id->setValue($data['refe_nation_id']);
			$client_issuedateid->setValue($data['client_issuedateid']);
			$join_issuedateid->setValue($data['join_issuedateid']);
		}
		$this->addElements(array($join_issuedateid,$client_issuedateid,$dob_buywith,$_join_type,$referecce_national_id,$p_nationality,$_nationality,$_arid_no,$_rid_no,$_bmember,$_vid_no,$_edesc,$_istatus,$_lphone,$_ghouse,$_dstreet,$_cprovince,$_pnameen,$_bnamekh,$_ksex,$_hnamekh,$client_d_type,$_join_nation_id,
				$_join_with,$_id,$photo,$job,$national_id,$_member,$_namekh,$_nameen,$_sex,
				$_province,$_house,$_street,$_id_no,$branch_id,$_email,
				$_phone,$_desc,$_status,$_clientno,$_dob,$clienttype_namekh,$clienttype_nameen));
		return $this;
		
	}	
	public function FrmLandInfo($data=null){
		$clienttype_nameen= new Zend_Dojo_Form_Element_DateTextBox('clienttype_nameen');
		$clienttype_nameen->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside'
		));
		
		$clienttype_namekh= new Zend_Dojo_Form_Element_DateTextBox('clienttype_namekh');
		$clienttype_namekh->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside'
		));
	
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Application_Model_DbTable_DbGlobal();
	
		$_landcode = new Zend_Dojo_Form_Element_TextBox('landcode');
		$_landcode->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'readonly'=>'readonly',
				'required' =>'true'
		));
	
		$streetlist = new Zend_Dojo_Form_Element_FilteringSelect('streetlist');
		$streetlist->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				
		));
		$streetopt = $db->getAllStreet();
		$streetlist->setMultiOptions($streetopt);
		$streetlist->setValue($request->getParam("streetlist"));
		
		$landaddress = new Zend_Dojo_Form_Element_ValidationTextBox('land_address');
		$landaddress->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'onblur'=>'checkTitle();',
		));
		
// 		$street = new Zend_Dojo_Form_Element_TextBox('street');
// 		$street->setAttribs(array(
// 				'dojoType'=>'dijit.form.TextBox',
// 				'class'=>'fullside',
// 				'onkeyup'=>'checkTitle();',
// 		));
		
		$street = new Zend_Dojo_Form_Element_FilteringSelect('street');
		$street->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'onchange'=>'showStreet();'
		
		));
		$rows = $db->getAllStreetForOpt();
		$options = array(''=>$this->tr->translate("CHOOSE_STREET"),'-1'=>$this->tr->translate("ADD_NEW"));
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['name']]=$row['name'];
		}
		$street->setMultiOptions($options);
		$street->setValue($request->getParam("street"));
		
		$land_price = new Zend_Dojo_Form_Element_NumberTextBox('land_price');
		$land_price->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'onKeyup'=>'CalculatePrice();'
		));
		$house_price = new Zend_Dojo_Form_Element_NumberTextBox('house_price');
		$house_price->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'onKeyup'=>'CalculatePrice();'
		));
		
		$_price = new Zend_Dojo_Form_Element_NumberTextBox('price');
		$_price->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'readonly'=>'readonly'
		));
		
		$_size = new Zend_Dojo_Form_Element_NumberTextBox('size');
		$_size->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
		));
		
		$width = new Zend_Dojo_Form_Element_NumberTextBox('width');
		$width->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'onkeyup'=>'calCulateSize();'
		));
		
		$height = new Zend_Dojo_Form_Element_NumberTextBox('height');
		$height->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'onkeyup'=>'calCulateSize();'
		));
		
		$width_land = new Zend_Dojo_Form_Element_NumberTextBox('width_land');
		$width_land->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'onkeyup'=>'calCulateFullSize();',
				'class'=>'fullside',
		));
		
		$height_land = new Zend_Dojo_Form_Element_NumberTextBox('height_land');
		$height_land->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'onkeyup'=>'calCulateFullSize();',
		));
		
		$full_size = new Zend_Dojo_Form_Element_NumberTextBox('full_size');
		$full_size->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
		));
		
		$hardtitle = new Zend_Dojo_Form_Element_TextBox('hardtitle');
		$hardtitle->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
		));
		
	
		$_id_no = new Zend_Form_Element_Hidden('id');
		$_id_no->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required' =>'true'
		));
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect'
				,'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		$_desc = new Zend_Dojo_Form_Element_TextBox('desc');
		$_desc->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside',
				'style'=>'width:96%;min-height:50px;'));
		
		$propertiestype = new Zend_Dojo_Form_Element_FilteringSelect('property_type');
		$propertiestype->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
				'required'=>false,
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'class'=>'fullside','onChange'=>'showPopupForm();'));
		
		$propertiestype_search = new Zend_Dojo_Form_Element_FilteringSelect('property_type_search');
		$propertiestype_search->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*'));
		$propertiestype_search_opt = $db->getPropertyTypeForsearch();
		$propertiestype_search->setMultiOptions($propertiestype_search_opt);
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$value = $request->getParam("property_type_search");
		$propertiestype_search->setValue($value);
		
		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true',
				'onchange'=>'getPropertyNo();',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$options_branch = $db->getAllBranchName(null,1);

		$branch_id->setMultiOptions($options_branch);
		$branch_id->setValue($request->getParam("branch_id"));
		
		$floor = new Zend_Dojo_Form_Element_TextBox('floor');
		$floor->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside',));
		
		$bedroom = new Zend_Dojo_Form_Element_TextBox('bedroom');
		$bedroom->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside',));
		
		$bathroom = new Zend_Dojo_Form_Element_TextBox('bathroom');
		$bathroom->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside',));
		
		$living = new Zend_Dojo_Form_Element_TextBox('living');
		$living->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside',));
		
		$dinnerroom = new Zend_Dojo_Form_Element_TextBox('dinnerroom');
		$dinnerroom->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside',));
		
		$BuidingYear = new Zend_Dojo_Form_Element_TextBox('buidingyear');
		$BuidingYear->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside',));
		
		$ParkingSpace = new Zend_Dojo_Form_Element_TextBox('parkingspace');
		$ParkingSpace->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside',));
		
		$photo=new Zend_Form_Element_File('photo');
		$photo->setAttribs(array(
		));		
		
		$north = new Zend_Dojo_Form_Element_TextBox('north');
		$north->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside'));
		$east = new Zend_Dojo_Form_Element_TextBox('east');
		$east->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside'));
		$west = new Zend_Dojo_Form_Element_TextBox('west');
		$west->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside'));
		$south = new Zend_Dojo_Form_Element_TextBox('south');
		$south->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside'));
		
		$status_using = new Zend_Dojo_Form_Element_FilteringSelect('buy_status');
		$status_using->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true'
		));
		
		$options= array(-1=>$this->tr->translate("ALL"),
				0=>$this->tr->translate("NOT_YET_SALE"),
				1=>$this->tr->translate("SOLD_OUT"));
		$action_name = $request->getActionName();
		if($action_name=='add' OR $action_name=='edit' OR $action_name=='copy'){
			unset($options[-1]);
		}
		$status_using->setMultiOptions($options);
		
		$status_using->setValue($request->getParam("buy_status"));
		
// 		$_type_of_property=  new Zend_Dojo_Form_Element_FilteringSelect('type_property_sale');
// 		$_type_of_property->setAttribs(array('dojoType'=>$this->filter,	'class'=>'fullside',));
// 		$_type_of = array(
// 				'-1'=>$this->tr->translate("ALL"),
// 				'1'=>$this->tr->translate("SOLD_OUT"),
// 				'0'=>$this->tr->translate("NOT_YET_SALE"));
// 		$_type_of_property->setMultiOptions($_type_of);
// 		$_type_of_property->setValue($request->getParam("type_property_sale"));
		
		if($data!=null){
			
			$north->setValue($data['north']);
			$east->setValue($data['east']);
			$south->setValue($data['south']);
			$west->setValue($data['west']);
			
			$branch_id->setValue($data['branch_id']);
			$branch_id->setAttribs(array("readonly"=>true));
			$_id_no->setValue($data['id']);
			$_desc->setValue($data['note']);
			$_status->setValue($data['status']);
			$_landcode->setValue($data['land_code']);
			$landaddress->setValue($data['land_address']);
			$_price->setValue($data['price']);
			$land_price->setValue($data['land_price']);
			$house_price->setValue($data['house_price']);
			$_size->setValue($data['land_size']);
			$width->setValue($data['width']);
			$height->setValue($data['height']);
			$width_land->setValue($data['land_width']);
			$height_land->setValue($data['land_height']);
			$full_size->setValue($data['full_size']);
			$street->setValue($data['street']);
			$hardtitle->setValue($data['hardtitle']);
			
			$BuidingYear->setValue($data['buidingyear']);
			$ParkingSpace->setValue($data['parkingspace']);
			$dinnerroom->setValue($data['dinnerroom']);
			$living->setValue($data['living']);
			$bedroom->setValue($data['bedroom']);
			$propertiestype->setValue($data['property_type']);
			$floor->setValue($data['floor']);
			$status_using->setValue($data['is_lock']);
		}
		$this->addElements(array($full_size,$status_using,$width_land,$height_land,$north,$east,$south,$west,$streetlist,$street,$propertiestype_search,$land_price,$house_price,$branch_id,$photo,$BuidingYear,$ParkingSpace,$dinnerroom,$living,$bedroom,$propertiestype,$floor,$_id_no,$_desc,$_status,$_landcode,$landaddress,$_price,$_size,$width,$height,$hardtitle));
		return $this;
	}
}