<?php 
Class Property_Form_FrmClient extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddClient($data=null){
		
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
		
		$db = new Application_Model_DbTable_DbGlobal();
		
		$_namekh = new Zend_Dojo_Form_Element_TextBox('name_kh');
		$_namekh->setAttribs(array(
						'dojoType'=>'dijit.form.ValidationTextBox',
						'class'=>'fullside',
						'required' =>'true'
		));
		
 		$i
		$_clientno = new Zend_Dojo_Form_Element_TextBox('client_no');
		$_clientno->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readonly'=>'readonly',
				'style'=>'color:red;'
		));
 		
	
		$_nameen = new Zend_Dojo_Form_Element_ValidationTextBox('name_en');
		$_nameen->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required' =>'true'
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
		
		$_p_nation_issue_date= new Zend_Dojo_Form_Element_DateTextBox('p_national_id_issue_date');
		$_p_nation_issue_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox','class'=>'fullside','constraints'=>"{datePattern:'dd/MM/yyyy'}",
		));
		
		$_sex = new Zend_Dojo_Form_Element_FilteringSelect('sex');
		$_sex->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$opt_status = $db->getVewOptoinTypeByType(11,1);
		unset($opt_status[-1]);
		unset($opt_status['']);
		$_sex->setMultiOptions($opt_status);
		
		
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
		$_nation_issue_date= new Zend_Dojo_Form_Element_DateTextBox('national_id_issue_date');
		$_nation_issue_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox','class'=>'fullside','constraints'=>"{datePattern:'dd/MM/yyyy'}",
		));
		
		$referecce_national_id=new Zend_Dojo_Form_Element_TextBox('reference_national_id');
		$referecce_national_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_id = new Zend_Form_Element_Hidden("id");
		$_desc = new Zend_Dojo_Form_Element_TextBox('desc');
		$_desc->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside',
				'style'=>'width:100%;min-height:60px;'));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
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
		
		$_lphone = new Zend_Dojo_Form_Element_TextBox('lphone');
		$_lphone->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$_istatus=  new Zend_Dojo_Form_Element_FilteringSelect('istatus');
		$_istatus->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$_istatus_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
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
		
		$_age = new Zend_Dojo_Form_Element_TextBox('age');
		$_age->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$p_age = new Zend_Dojo_Form_Element_TextBox('p_age');
		$p_age->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				//'required' =>'true'
		));
		
		$group = new Zend_Dojo_Form_Element_TextBox('group');
		$group->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				//'required' =>'true'
		));
		$current_address = new Zend_Dojo_Form_Element_TextBox('current_address');
		$current_address->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside',
				'style'=>'width:100%;min-height:60px;'));
		$_is_type_of_relevant = new Zend_Dojo_Form_Element_FilteringSelect('is_type_of_relevant');
		$_is_type_of_relevant->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$rows =  $db->getVewOptoinTypeByType(26,1,null,null);
		unset($rows[-1]);
		$_is_type_of_relevant->setMultiOptions($rows);
		
		$_customer_sex = new Zend_Dojo_Form_Element_FilteringSelect('customer_sex');//for  popup form
		$_customer_sex->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$opt_status = $db->getVewOptoinTypeByType(11,1);
		unset($opt_status[-1]);
		unset($opt_status['']);
		$_customer_sex->setMultiOptions($opt_status);
		
		$customer_phone = new Zend_Dojo_Form_Element_TextBox('cus_phone');//for  popup form
		$customer_phone->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		if($data!=null){
			$branch_id->setValue($data['branch_id']);
			$branch_id->setAttribs(array("readonly"=>true));
			$_namekh->setValue($data['name_kh']);
			$_nameen->setValue($data['name_en']);
			$_sex->setValue($data['sex']);
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
			$_dob->setValue($data['dob']);
			$_hnamekh->setValue($data['hname_kh']);
			$_bnamekh->setValue($data['bname_kh']);
			$_lphone->setValue($data['lphone']);
			$_rid_no->setValue($data['rid_no']);
			$_arid_no->setValue($data['arid_no']);
			$_edesc->setValue($data['edesc']);
			$_ksex->setValue($data['ksex']);
			$p_nationality->setValue($data['p_nationality']);
			$_age->setValue($data['age']);
			$p_age->setValue($data['p_age']);
			$_nation_issue_date->setValue(date("Y-m-d",strtotime($data['nation_id_issue_date'])));
			$_p_nation_issue_date->setValue(date("Y-m-d",strtotime($data['p_nation_issue_date'])));
			$current_address->setValue($data['current_address']);
			$_is_type_of_relevant->setValue($data['is_relevant_type']);
			$referecce_national_id->setValue($data['refe_nation_id']);
		}
		$this->addElements(array($referecce_national_id,$p_nationality,$_nationality,$_arid_no,$_rid_no,$_vid_no,$_edesc,$_istatus,$_lphone,$_pnameen,$_bnamekh,$_ksex,$_hnamekh,$_join_nation_id,
				$_join_with,$_id,$photo,$job,$national_id,$_namekh,$_nameen,$_sex,$_p_nation_issue_date,$_is_type_of_relevant,
				$_id_no,$branch_id,$_email,$group,$_nation_issue_date,$current_address,
				$_phone,$_desc,$_status,$_clientno,$_dob,$_age,$p_age,$_customer_sex,$customer_phone));
		return $this;
		
	}	
	public function FrmLandInfo($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
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
	
		$landaddress = new Zend_Dojo_Form_Element_TextBox('land_address');
		$landaddress->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
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
				'required' =>'true'
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
				//'readonly'=>'readonly'
		));
		
		$_size = new Zend_Dojo_Form_Element_TextBox('size');
		$_size->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required' =>'true',

		));
		
		$width = new Zend_Dojo_Form_Element_TextBox('width');
		$width->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'onKeyup'=>'calculateSize();'
		));
		
		$height = new Zend_Dojo_Form_Element_TextBox('height');
		$height->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'onKeyup'=>'calculateSize();'
		));
		
		$hardtitle = new Zend_Dojo_Form_Element_TextBox('hardtitle');
		$hardtitle->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'onBlur'=>'checkTitle();',
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
				0=>$this->tr->translate("DEACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		$_desc = new Zend_Dojo_Form_Element_TextBox('desc');
		$_desc->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside',
				'style'=>'width:99%;min-height:50px;'));
		
		$propertiestype = new Zend_Dojo_Form_Element_FilteringSelect('property_type');
		$propertiestype->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside','onChange'=>'showPopupForm();'));
		//$propertiestype_opt = $db->getPropertyType();
		//$propertiestype->setMultiOptions($propertiestype_opt);
		
		$propertiestype_search = new Zend_Dojo_Form_Element_FilteringSelect('property_type_search');
		$propertiestype_search->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside'));
		$propertiestype_search_opt = $db->getPropertyTypeForsearch();
		$propertiestype_search->setMultiOptions($propertiestype_search_opt);
		
		$value = $request->getParam("property_type_search");
		$propertiestype_search->setValue($value);
		
		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true',
				'onchange'=>'getPropertyNo();'
		));
		$rows_branch = $db->getAllBranchName();
		//=array(''=>"---Select Branch---");
		if(!empty($rows_branch))foreach($rows_branch AS $row){
			$options_branch[$row['br_id']]=$row['project_name'];
		}
		$branch_id->setMultiOptions($options_branch);
		$branch_id->setValue($request->getParam("branch_id"));
		
		$project_value = $request->getParam("branch_id");
		$branch_id->setValue($project_value);
		
		$border_north = new Zend_Dojo_Form_Element_TextBox('border_north');
		$border_north->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$border_south = new Zend_Dojo_Form_Element_TextBox('border_south');
		$border_south->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$border_west = new Zend_Dojo_Form_Element_TextBox('border_west');
		$border_west->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$border_east = new Zend_Dojo_Form_Element_TextBox('border_east');
		$border_east->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$credentail_no = new Zend_Dojo_Form_Element_TextBox('credentail_no');
		$credentail_no->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_issue_date= new Zend_Dojo_Form_Element_DateTextBox('issue_date');
		$_issue_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox','class'=>'fullside','constraints'=>"{datePattern:'dd/MM/yyyy'}",
		));
		
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
		$photo1=new Zend_Form_Element_File('photo1');
		$photo1->setAttribs(array(
		));
		$photo2=new Zend_Form_Element_File('photo2');
		$photo2->setAttribs(array(
		));
		$photo3=new Zend_Form_Element_File('photo3');
		$photo3->setAttribs(array(
		));
		$photo4=new Zend_Form_Element_File('photo4');
		$photo4->setAttribs(array(
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
			$hardtitle->setValue($data['land_address']);
			
			$BuidingYear->setValue($data['buidingyear']);
			$ParkingSpace->setValue($data['parkingspace']);
			$dinnerroom->setValue($data['dinnerroom']);
			$living->setValue($data['living']);
			$bedroom->setValue($data['bedroom']);
			$propertiestype->setValue($data['property_type']);
			$floor->setValue($data['floor']);
			
			$border_north->setValue($data['border_north']);
			$border_south->setValue($data['border_south']);
			$border_west->setValue($data['border_west']);
			$border_east->setValue($data['border_east']);
			$_issue_date->setValue($data['issue_date']);
			$credentail_no->setValue($data['credentail_no']);
			$photo4->setValue($data['photo4']);
			$photo3->setValue($data['photo3']);
			$photo2->setValue($data['photo2']);
			$photo1->setValue($data['photo']);
		}
		$this->addElements(array($street,$propertiestype_search,$land_price,$house_price,$branch_id,$photo,
				$border_north,$border_south,$border_west,$border_east,$_issue_date,$credentail_no,$photo1,$photo2,$photo3,$photo4,
				$BuidingYear,$ParkingSpace,$dinnerroom,$living,$bedroom,$propertiestype,$floor,$_id_no,$_desc,$_status,$_landcode,$landaddress,$_price,$_size,$width,$height,$hardtitle));
		return $this;
	
	}
}