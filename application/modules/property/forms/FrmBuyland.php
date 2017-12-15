<?php 
Class Property_Form_FrmBuyland extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmBuyLand($data=null){
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Application_Model_DbTable_DbGlobal();
		
		
// 		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
// 		$branch_id->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'required' =>'true',
// 				'onchange'=>'getClientNo();'
// 		));
// 		$rows_branch = $db->getAllBranchName();
// 		//=array(''=>"---Select Branch---");
// 		if(!empty($rows_branch))foreach($rows_branch AS $row){
// 			$options_branch[$row['br_id']]=$row['project_name'];
// 		}
// 		$branch_id->setMultiOptions($options_branch);
// 		$branch_id->setValue($request->getParam("branch_id"));	
			
		$title = new Zend_Dojo_Form_Element_TextBox('title');
		$title->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required' =>'true'
		));
		
		$slae_name_kh = new Zend_Dojo_Form_Element_TextBox('sale_name_kh');
		$slae_name_kh->setAttribs(array(
						'dojoType'=>'dijit.form.ValidationTextBox',
						'class'=>'fullside',
						'required' =>'true'
		));
		$_sale_nationality = new Zend_Dojo_Form_Element_TextBox('sale_nationality');
		$_sale_nationality->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$_sale_nationality->setValue("ខ្មែរ");
		
		$sale_nation_id = new Zend_Dojo_Form_Element_TextBox('sale_nation_id');
		$sale_nation_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$sale_address = new Zend_Dojo_Form_Element_Textarea('sale_address');
		$sale_address->setAttribs(array('dojoType'=>'dijit.form.Textarea','class'=>'fullside',
				'style'=>'width:100%;min-height:60px;font-size: 96%; font-family: "Kh Battambang","Khmer Battambang",Arial,Helvetica,sans-serif;'));
		
		$sale_phone = new Zend_Dojo_Form_Element_TextBox('sale_phone');
		$sale_phone->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_sale_sex = new Zend_Dojo_Form_Element_FilteringSelect('sale_sex');
		$_sale_sex->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$opt_status = $db->getVewOptoinTypeByType(11,1);
		unset($opt_status[-1]);
		unset($opt_status['']);
		$_sale_sex->setMultiOptions($opt_status);
		
		$sale_age = new Zend_Dojo_Form_Element_TextBox('sale_age');
		$sale_age->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$sale_relevent_name = new Zend_Dojo_Form_Element_TextBox('sale_relevent_name');
		$sale_relevent_name->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$sale_relevent_nationlity = new Zend_Dojo_Form_Element_TextBox('sale_relevent_nationlity');
		$sale_relevent_nationlity->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$sale_relevent_nationlity->setValue("ខ្មែរ");
		
		$sale_relevent_nationid = new Zend_Dojo_Form_Element_TextBox('sale_relevent_nationid');
		$sale_relevent_nationid->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$sale_relevent_age = new Zend_Dojo_Form_Element_TextBox('sale_relevent_age');
		$sale_relevent_age->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$sale_relevent_sex = new Zend_Dojo_Form_Element_FilteringSelect('sale_relevent_sex');
		$sale_relevent_sex->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$opt_status = $db->getVewOptoinTypeByType(11,1);
		unset($opt_status[-1]);
		unset($opt_status['']);
		$sale_relevent_sex->setMultiOptions($opt_status);
		
		$sale_relevent_is = new Zend_Dojo_Form_Element_FilteringSelect('sale_relevent_is');
		$sale_relevent_is->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$rows =  $db->getVewOptoinTypeByType(26,1,null,null);
		unset($rows[-1]);
		$sale_relevent_is->setMultiOptions($rows);
		
	//======================End sale control================================
 		$id_buy_no = $db->getBuylandNo();
		$_buy_no = new Zend_Dojo_Form_Element_TextBox('buy_no');
		$_buy_no->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readonly'=>'readonly',
				'style'=>'color:red;'
		));
 		$_buy_no->setValue($id_buy_no);
	
		$_buy_name_kh = new Zend_Dojo_Form_Element_ValidationTextBox('buy_name_kh');
		$_buy_name_kh->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required' =>'true'
				));
		
		$_buy_nation_id = new Zend_Dojo_Form_Element_TextBox('buy_nation_id');
		$_buy_nation_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$buy_nationality = new Zend_Dojo_Form_Element_TextBox('buy_nationality');
		$buy_nationality->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$buy_nationality->setValue("ខ្មែរ");
		
		$buy_age = new Zend_Dojo_Form_Element_TextBox('buy_age');
		$buy_age->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_buy_sex = new Zend_Dojo_Form_Element_FilteringSelect('buy_sex');
		$_buy_sex->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$opt_status = $db->getVewOptoinTypeByType(11,1);
		unset($opt_status[-1]);
		unset($opt_status['']);
		$_buy_sex->setMultiOptions($opt_status);
		
		$buy_address = new Zend_Dojo_Form_Element_Textarea('buy_address');
		$buy_address->setAttribs(array('dojoType'=>'dijit.form.Textarea','class'=>'fullside',
				'style'=>'width:100%;min-height:60px;font-size: 96%; font-family: "Kh Battambang","Khmer Battambang",Arial,Helvetica,sans-serif;'));
		
		$_buy_phone = new Zend_Dojo_Form_Element_TextBox('buy_phone');
		$_buy_phone->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$buyer_relevent_name = new Zend_Dojo_Form_Element_TextBox('buyer_relevent_name');
		$buyer_relevent_name->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$buyer_relevent_nationality = new Zend_Dojo_Form_Element_TextBox('buyer_relevent_nationality');
		$buyer_relevent_nationality->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$buyer_relevent_nationality->setValue("ខ្មែរ");
		
		$buyer_relevent_nationid = new Zend_Dojo_Form_Element_TextBox('buyer_relevent_nationid');
		$buyer_relevent_nationid->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$buyer_relevent_age = new Zend_Dojo_Form_Element_TextBox('buyer_relevent_age');
		$buyer_relevent_age->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$buyer_relevent_sex = new Zend_Dojo_Form_Element_FilteringSelect('buyer_relevent_sex');
		$buyer_relevent_sex->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$opt_status = $db->getVewOptoinTypeByType(11,1);
		unset($opt_status[-1]);
		unset($opt_status['']);
		$buyer_relevent_sex->setMultiOptions($opt_status);
		
		$buyer_relevent_is = new Zend_Dojo_Form_Element_FilteringSelect('buyer_relevent_is');
		$buyer_relevent_is->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$rows =  $db->getVewOptoinTypeByType(26,1,null,null);
		unset($rows[-1]);
		$buyer_relevent_is->setMultiOptions($rows);
		
		//======================End Buy control================================
		$photo=new Zend_Form_Element_File('photo');
		$photo->setAttribs(array(
		));
	
		
		$_id = new Zend_Form_Element_Hidden("id");
		$_note = new Zend_Dojo_Form_Element_Textarea('note');
		$_note->setAttribs(array('dojoType'=>'dijit.form.Textarea','class'=>'fullside',
				'style'=>'width:100%;min-height:60px;font-size: 96%; font-family: "Kh Battambang","Khmer Battambang",Arial,Helvetica,sans-serif;'));
		
		$location = new Zend_Dojo_Form_Element_Textarea('location');
		$location->setAttribs(array('dojoType'=>'dijit.form.Textarea','class'=>'fullside',
				'style'=>'width:100%;min-height:60px; font-size: 96%; font-family: "Kh Battambang","Khmer Battambang",Arial,Helvetica,sans-serif;'));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		$_size = new Zend_Dojo_Form_Element_NumberTextBox('size');
		$_size->setAttribs(array('dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside','required' =>'true',));
		
		$width = new Zend_Dojo_Form_Element_NumberTextBox('width');
		$width->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'onKeyup'=>'calculateSize();'
		));
		
		$height = new Zend_Dojo_Form_Element_NumberTextBox('height');
		$height->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside','onKeyup'=>'calculateSize();'
		));
		$land_price = new Zend_Dojo_Form_Element_NumberTextBox('land_price');
		$land_price->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside','required' =>'true',
		));
		$credentail_no = new Zend_Dojo_Form_Element_TextBox('credentail_no');
		$credentail_no->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
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
		
		$_issue_date= new Zend_Dojo_Form_Element_DateTextBox('issue_date');
		$_issue_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox','class'=>'fullside','constraints'=>"{datePattern:'dd/MM/yyyy'}",
		));
		$_issue_date->setValue(date("Y-m-d"));
		$_buy_date= new Zend_Dojo_Form_Element_DateTextBox('buy_date');
		$_buy_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox','class'=>'fullside','constraints'=>"{datePattern:'dd/MM/yyyy'}",
		));
		$_buy_date->setValue(date("Y-m-d"));
		$propertiestype = new Zend_Dojo_Form_Element_FilteringSelect('property_type');
		$propertiestype->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside','onChange'=>'showPopupForm();'));
		$propertiestype_opt = $db->getPropertyType();
		$propertiestype->setMultiOptions($propertiestype_opt);
		
		$properties_type_search = new Zend_Dojo_Form_Element_FilteringSelect('property_type_search');
		$properties_type_search->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside','onChange'=>'showPopupForm();'));
		$properties_type_search_opt = $db->getPropertyType(1);
		$properties_type_search->setMultiOptions($properties_type_search_opt);
		$properties_type_search->setValue($request->getParam("property_type_search"));
		
		$_land_blog = new Zend_Dojo_Form_Element_FilteringSelect('land_blog');
		$_land_blog->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$db_landblog = new Property_Model_DbTable_DbLandBlog();
		$landblog = $db_landblog->getLandBlog();
		$opt_land_blog= array(0=>$this->tr->translate("SELECT_LAND_BLOG"));
		if(!empty($landblog))foreach($landblog AS $row){
			$opt_land_blog[$row['id']]=$row['name'];
		}
		$_land_blog->setMultiOptions($opt_land_blog);
		$_land_blog->setValue($request->getParam("land_blog"));
		
		$photo1=new Zend_Form_Element_File('land_img1');
		$photo1->setAttribs(array());
		$photo2=new Zend_Form_Element_File('land_img2');
		$photo2->setAttribs(array());
		$sale_img1=new Zend_Form_Element_File('sale_img1');
		$sale_img1->setAttribs(array());
		$sale_img2=new Zend_Form_Element_File('sale_img2');
		$sale_img2->setAttribs(array());
		
		$old_land_img1= new Zend_Form_Element_Hidden("old_land_img1");
		$old_land_img1->setAttribs(array());
		$old_land_img2= new Zend_Form_Element_Hidden("old_land_img2");
		$old_land_img2->setAttribs(array());
		$old_sale_img1= new Zend_Form_Element_Hidden("old_sale_img1");
		$old_sale_img1->setAttribs(array());
		$old_sale_img2= new Zend_Form_Element_Hidden("old_sale_img2");
		$old_sale_img2->setAttribs(array());
		
		if($data!=null){
			$title->setValue($data['title']);
			$_buy_no->setValue($data['buy_no']);
			$propertiestype->setValue($data['land_type']);
			$land_price->setValue($data['price']);
			$width->setValue($data['width']);
			$height->setValue($data['height']);
			$_size->setValue($data['size']);
			$_buy_date->setValue($data['buy_date']);
			$credentail_no->setValue($data['credentail_no']);
			$_issue_date->setValue($data['issue_date']);
			$location->setValue($data['location']);
			$slae_name_kh->setValue($data['sale_name']);
			$_sale_sex->setValue($data['sale_sex']);
			$sale_age->setValue($data['sale_age']);
			$_sale_nationality->setValue($data['sale_nationlity']);
			$sale_nation_id->setValue($data['sale_nation_id']);
			$sale_phone->setValue($data['sale_phone']);
			$sale_address->setValue($data['sale_addrees']);
			$_buy_name_kh->setValue($data['buyer_name']);
			$_buy_sex->setValue($data['buyer_sex']);
			$buy_age->setValue($data['buyer_age']);
			$buy_nationality->setValue($data['buyer_nationality']);
			$_buy_nation_id->setValue($data['buyer_nation_id']);
			$_buy_phone->setValue($data['buyer_phone']);
			$buy_address->setValue($data['buyer_address']);
			$_status->setValue($data['status']);
			$_note->setValue($data['note']);
			$border_north->setValue($data['border_north']);
			$border_south->setValue($data['border_south']);
			$border_east->setValue($data['border_east']);
			$border_west->setValue($data['border_west']);
			$sale_relevent_name->setValue($data['sale_relevent_name']);
			$sale_relevent_nationlity->setValue($data['sale_relevent_nationlity']);
			$sale_relevent_nationid->setValue($data['sale_relevent_nationid']);
			$sale_relevent_age->setValue($data['sale_relevent_age']);
			$sale_relevent_sex->setValue($data['sale_relevent_sex']);
			$sale_relevent_is->setValue($data['sale_relevent_is']);
			
			$buyer_relevent_name->setValue($data['buyer_relevent_name']);
			$buyer_relevent_nationid->setValue($data['buyer_relevent_nationid']);
			$buyer_relevent_nationality->setValue($data['buyer_relevent_nationality']);
			$buyer_relevent_age->setValue($data['buyer_relevent_age']);
			$buyer_relevent_sex->setValue($data['buyer_relevent_sex']);
			$buyer_relevent_is->setValue($data['buyer_relevent_is']);
			
			$old_land_img1->setValue($data['land_photo1']);
			$old_land_img2->setValue($data['land_photo2']);
			$old_sale_img1->setValue($data['sale_photo1']);
			$old_sale_img2->setValue($data['sale_photo2']);
		}
		$this->addElements(array($_buy_no,$slae_name_kh,$sale_age,$sale_nation_id,$sale_phone,$sale_address,$_sale_nationality,$_sale_sex,
				$sale_relevent_name,$sale_relevent_nationlity,$sale_relevent_nationid,$sale_relevent_age,$sale_relevent_sex,$sale_relevent_is,
				$buy_address,$buy_age,$buy_nationality,$_buy_name_kh,$_buy_nation_id,$_buy_phone,$_buy_sex,$properties_type_search,
				$buyer_relevent_name,$buyer_relevent_nationid,$buyer_relevent_nationality,$buyer_relevent_age,$buyer_relevent_sex,$buyer_relevent_is,
				$_status,$_note,$_size,$width,$height,$credentail_no,$_issue_date,$propertiestype,$land_price,$location,$_buy_date,
				$border_north,$border_south,$border_east,$border_west,$title,$_land_blog,
				$photo1,$photo2,$sale_img1,$sale_img2,$old_land_img1,$old_land_img2,$old_sale_img1,$old_sale_img2
				));
		return $this;
		
	}	
}