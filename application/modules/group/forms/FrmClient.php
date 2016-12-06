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
		$_dob->setAttribs(array('dojoType'=>'dijit.form.DateTextBox','class'=>'fullside','constraints'=>"{datePattern:'dd/MM/yyyy'}",
		));
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Application_Model_DbTable_DbGlobal();
		
		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true',
				'onchange'=>'getClientNo();'
		));
		$rows_branch = $db->getAllBranchName();
		//=array(''=>"---Select Branch---");
		if(!empty($rows_branch))foreach($rows_branch AS $row){
			$options_branch[$row['br_id']]=$row['project_name'];
		}
		$branch_id->setMultiOptions($options_branch);
		$branch_id->setValue($request->getParam("branch_id"));
		
		$_member = new Zend_Dojo_Form_Element_FilteringSelect('group_id');
		$_member->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'getGroupCode();'
		));
		$db = new Application_Model_DbTable_DbGlobal();
		$rows = $db->getClientByType();
		$options=array(''=>"---Select Group Name---");
		if(!empty($rows))foreach($rows AS $row) $options[$row['client_id']]=$row['name_en'];
		$_member->setMultiOptions($options);
		
		$_namekh = new Zend_Dojo_Form_Element_TextBox('name_kh');
		$_namekh->setAttribs(array(
						'dojoType'=>'dijit.form.ValidationTextBox',
						'class'=>'fullside',
						'required' =>'true'
		));
		
 		$id_client = $db->getNewClientId();
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
		));
		$opt_status = $db->getVewOptoinTypeByType(11,1);
		unset($opt_status[-1]);
		unset($opt_status['']);
		$_sex->setMultiOptions($opt_status);
		
		$client_d_type = new Zend_Dojo_Form_Element_FilteringSelect('client_d_type');
		$client_d_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		
		$_province = new Zend_Dojo_Form_Element_FilteringSelect('province');
		$_province->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'filterDistrict();',
		));
		
		$rows =  $db->getAllProvince();
		$options=array($this->tr->translate("SELECT_PROVINCE")); //array(''=>"------Select Province------",-1=>"Add New");
		if(!empty($rows))foreach($rows AS $row) $options[$row['province_id']]=$row['province_en_name'];
		$_province->setMultiOptions($options);
		
// 		$_district = new Zend_Dojo_Form_Element_FilteringSelect('district');
// 		$_district->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'onchange'=>'popupCheckDistrict();'
// 		));
		
// 		$_commune = new Zend_Dojo_Form_Element_FilteringSelect('commune');
// 		$options=array(''=>"------Select------",-1=>"Add New");
// 		$_commune->setMultiOptions($options);
// 		$_commune->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'onchange'=>'popupCheckCommune();'
// 		));
		
// 		$_village = new Zend_Dojo_Form_Element_FilteringSelect('village');
// 		$_village->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'required' =>'true',
// 				'onchange'=>'popupCheckVillage();'
// 		));
// 		$rows =  $db->getVillage();
// 		$options=array(''=>"------Select------",-1=>"Add New");
// 		if(!empty($rows))foreach($rows AS $row) $options[$row['vill_id']]=$row['village_name'];
// 		$_village->setMultiOptions($options);
		
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
		$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
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
		));
		
		$rows =  $db->getAllProvince();
		$options=array($this->tr->translate("SELECT_PROVINCE")); //array(''=>"------Select Province------",-1=>"Add New");
		if(!empty($rows))foreach($rows AS $row) $options[$row['province_id']]=$row['province_en_name'];
		$_cprovince->setMultiOptions($options);
		
// 		$_adistrict = new Zend_Dojo_Form_Element_FilteringSelect('adistrict');
// 		$_adistrict->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'onchange'=>'popupCheckDistrict();'
// 		));
// 		$rows =  $db->getAllDistrict();
// 		$options=array($this->tr->translate("SELECT_DISTRICT")); //array(''=>"------Select Province------",-1=>"Add New");
// 		if(!empty($rows))foreach($rows AS $row) $options[$row['dis_id']]=$row['district_name'];
// 		$_adistrict->setMultiOptions($options);
		
		
// 		$_dcommune = new Zend_Dojo_Form_Element_FilteringSelect('dcommune');
// 		$options=array(''=>"------Select------",-1=>"Add New");
// 		$_dcommune->setMultiOptions($options);
// 		$_dcommune->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'onchange'=>'popupCheckCommune();'
// 		));
// 		$_qvillage = new Zend_Dojo_Form_Element_FilteringSelect('qvillage');
// 		$_qvillage->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'required' =>'true',
// 				'onchange'=>'popupCheckVillage();'
// 		));
// 		$rows =  $db->getVillage();
// 		$options=array(''=>"------Select------",-1=>"Add New");
// 		if(!empty($rows))foreach($rows AS $row) $options[$row['vill_id']]=$row['village_name'];
// 		$_qvillage->setMultiOptions($options);
		
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
				'onchange'=>'getGroupCode();'
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
			$branch_id->setAttribs(array("readonly"=>true));
			$_namekh->setValue($data['name_kh']);
			$_nameen->setValue($data['name_en']);
			$_sex->setValue($data['sex']);
			$_province->setValue($data['pro_id']);
// 			$_district->setValue($data['dis_id']);
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
		}
		$this->addElements(array($_join_type,$referecce_national_id,$p_nationality,$_nationality,$_arid_no,$_rid_no,$_bmember,$_vid_no,$_edesc,$_istatus,$_lphone,$_ghouse,$_dstreet,$_cprovince,$_pnameen,$_bnamekh,$_ksex,$_hnamekh,$client_d_type,$_join_nation_id,
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
	
		$landaddress = new Zend_Dojo_Form_Element_ValidationTextBox('land_address');
		$landaddress->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'onKeyup'=>'checkTitle();',
		));
		
		$street = new Zend_Dojo_Form_Element_TextBox('street');
		$street->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
	
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
		
		$hardtitle = new Zend_Dojo_Form_Element_TextBox('hardtitle');
		$hardtitle->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
		));
		
	
		$_id_no = new Zend_Form_Element_hidden('id');
		$_id_no->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required' =>'true'
		));
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		$_desc = new Zend_Dojo_Form_Element_TextBox('desc');
		$_desc->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside',
				'style'=>'width:96%;min-height:50px;'));
		
		$propertiestype = new Zend_Dojo_Form_Element_FilteringSelect('property_type');
		$propertiestype->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','required'=>false,
				'class'=>'fullside','onChange'=>'showPopupForm();'));
		
		$propertiestype_search = new Zend_Dojo_Form_Element_FilteringSelect('property_type_search');
		$propertiestype_search->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside'));
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
				'onchange'=>'getPropertyNo();'
		));
		$options_branch = $db->getAllBranchName(null,1);
// 		if(!empty($rows_branch))foreach($rows_branch AS $row){
// 			$options_branch[$row['br_id']]=$row['project_name'];
// 		}
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
		
		if($data!=null){
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
			$street->setValue($data['street']);
			$hardtitle->setValue($data['hardtitle']);
			
			$BuidingYear->setValue($data['buidingyear']);
			$ParkingSpace->setValue($data['parkingspace']);
			$dinnerroom->setValue($data['dinnerroom']);
			$living->setValue($data['living']);
			$bedroom->setValue($data['bedroom']);
			$propertiestype->setValue($data['property_type']);
			$floor->setValue($data['floor']);
		}
		$this->addElements(array($street,$propertiestype_search,$land_price,$house_price,$branch_id,$photo,$BuidingYear,$ParkingSpace,$dinnerroom,$living,$bedroom,$propertiestype,$floor,$_id_no,$_desc,$_status,$_landcode,$landaddress,$_price,$_size,$width,$height,$hardtitle));
		return $this;
	
	}
}