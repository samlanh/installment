<?php 
Class Project_Form_FrmLand extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
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