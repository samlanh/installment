<?php 
Class Property_Form_FrmRent extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmRent($data=null,$action=null){
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Application_Model_DbTable_DbGlobal();
		
		$_rent_code = new Zend_Dojo_Form_Element_TextBox('rent_code');
		$_rent_code->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'readonly'=>true,
				'class'=>'fullside',
				'style'=>'color:red; font-weight: bold;'
		));
		$rent_number = $db->getRentPropertyNo();
		$_rent_code->setValue($rent_number);
		
		$_customer = new Zend_Dojo_Form_Element_FilteringSelect('customer');
		$_customer->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onChange'=>'showPopupFormCustomer()'
		));
		$opt_cus= array(''=>$this->tr->translate("SELECT_CLIENT"),'-1'=>$this->tr->translate("ADD_NEW"),);
		$row_customer = $db->getAllClientname();
		if(!empty($row_customer))foreach($row_customer AS $row){
			$opt_cus[$row['id']]=$row['name'];
		}
		$_customer->setMultiOptions($opt_cus);
		
		$_land = new Zend_Dojo_Form_Element_FilteringSelect('land');
		$_land->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onChange'=>'getlandinfo();'
		));
		$opt_land= array(''=>"Select Land");
		if(!empty($data['land_id'])){$land = $data['land_id'];}else{ $land='';}
		$row_land = $db->getBuyLand($action,$land);
		if(!empty($row_land))foreach($row_land AS $row){
			$opt_land[$row['id']]=$row['name'];
		}
		$_land->setMultiOptions($opt_land);
		
		$_date_rent = new Zend_Dojo_Form_Element_DateTextBox('date_rent');
		$_date_rent->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required' =>'true',
				'class'=>'fullside',
				//'onchange'=>'calCulateEndDate();',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$_date_rent->setValue(date("Y-m-d"));
		
		$_start_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$_start_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'calCulateEndDate();',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$_start_date->setValue(date("Y-m-d"));
		
		$_end_date = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$_end_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required' =>'true',
				'class'=>'fullside',
				//'onchange'=>'checkReleaseDate();',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$_end_date->setValue(date("Y-m-d"));
		
		$rent_price_permont = new Zend_Dojo_Form_Element_NumberTextBox('rent_price_permonth');
		$rent_price_permont->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true,
				'onkeyup'=>'calCulateTotalRent();',
		));
		
		$total_rent = new Zend_Dojo_Form_Element_NumberTextBox('total_rent');
		$total_rent->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true,
				'readonly'=>true,
		));
		
		$paid = new Zend_Dojo_Form_Element_NumberTextBox('deposit');
		$paid->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'onkeyup'=>'calCulateBalance();',
				'required'=>true,
		));
		
		$balance = new Zend_Dojo_Form_Element_NumberTextBox('balance');
		$balance->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'readonly'=>true,
		
		));
		$staff_id = new Zend_Dojo_Form_Element_FilteringSelect('staff_id');
		$staff_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'popupCheckCO();'
		));
		$options = $db->getAllCOName(1);
		$staff_id->setMultiOptions($options);
		
		$commission = new Zend_Dojo_Form_Element_NumberTextBox('commission');
		$commission->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
		));
		$commission->setValue(0);
		
		$period = new Zend_Dojo_Form_Element_NumberTextBox('period');
		$period->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true,
				'onkeyup'=>'calCulateEndDate(), calCulateTotalRent();',
		));
		$period->setValue(0);
		
		$_id = new Zend_Form_Element_Hidden("old_land_id");
		$_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_note = new Zend_Dojo_Form_Element_Textarea('note');
		$_note->setAttribs(array('dojoType'=>'dijit.form.Textarea','class'=>'fullside',
				'style'=>'width:100%;min-height:60px; font-size: 96%; font-family: "Kh Battambang","Khmer Battambang",Arial,Helvetica,sans-serif;'));
		
		$note = new Zend_Dojo_Form_Element_Textarea('note');
		$note->setAttribs(array('dojoType'=>'dijit.form.Textarea','class'=>'fullside',
				'style'=>'width:100%;min-height:60px; font-size: 96%; font-family: "Kh Battambang","Khmer Battambang",Arial,Helvetica,sans-serif;'));
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		$_land_blog = new Zend_Dojo_Form_Element_FilteringSelect('land_blog');
		$_land_blog->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onChange'=>'getAllLand(), getlandinfo(1);'
		));
		$db_landblog = new Property_Model_DbTable_DbLandBlog();
		$landblog = $db_landblog->getLandBlog();
		$opt_land_blog= array(''=>"ជ្រើសរើសប្លុកទីតាំងដី");
		if(!empty($landblog))foreach($landblog AS $row){
			$opt_land_blog[$row['id']]=$row['name'];
		}
		$_land_blog->setMultiOptions($opt_land_blog);
		
		$is_complete=  new Zend_Dojo_Form_Element_FilteringSelect('is_complete');
		$is_complete->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$_is_complete_opt = array(
				1=>$this->tr->translate("បានផុតកំណត់"),
				0=>$this->tr->translate("មិនទាន់ផុតកំណត់"));
		$is_complete->setMultiOptions($_is_complete_opt);
		
		$_seller = new Zend_Dojo_Form_Element_FilteringSelect('seller');
		$_seller->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onChange'=>'getLandBlog();'
		));
		$opt_seller= array(''=>"ជ្រើសរើសអ្នកលក់");
		$row_seller = $db->getAllseller();
		if(!empty($row_seller))foreach($row_seller AS $row){
			$opt_seller[$row['name']]=$row['name'];
		}
		$_seller->setMultiOptions($opt_seller);
		
		$north = new Zend_Dojo_Form_Element_TextBox('north');
		$north->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'data-dojo-props'=>"
				'class':'fullside fullside50',
				'title':'ព្រំប្រទល់ខាងជើង	',
				'placeHolder':'ព្រំប្រទល់ខាងជើង	'",
		));
		$south = new Zend_Dojo_Form_Element_TextBox('south');
		$south->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'data-dojo-props'=>"
				'class':'fullside fullside50',
				'title':'ព្រំប្រទល់ខាងត្បូង',
				'placeHolder':'ព្រំប្រទល់ខាងត្បូង'",
		));
		$west = new Zend_Dojo_Form_Element_TextBox('west');
		$west->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'data-dojo-props'=>"
				'class':'fullside fullside50',
				'title':'ព្រំប្រទល់ខាងលិច',
				'placeHolder':'ព្រំប្រទល់ខាងលិច'",
		));
		$east = new Zend_Dojo_Form_Element_TextBox('east');
		$east->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'data-dojo-props'=>"
				'class':'fullside fullside50',
				'title':'ព្រំប្រទល់ខាងកើត',
				'placeHolder':'ព្រំប្រទល់ខាងកើត'",
		));
		
		$width = new Zend_Dojo_Form_Element_NumberTextBox('width');
		$width->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'data-dojo-props'=>"
				'class':'fullside fullside50',
				'title':'ទទឹង (ក្បាល)',
				'placeHolder':'ទទឹង (ក្បាល)'",
		));
		$height = new Zend_Dojo_Form_Element_NumberTextBox('height');
		$height->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'data-dojo-props'=>"
				'class':'fullside fullside50',
				'title':'បណ្ដោយ',
				'placeHolder':'បណ្ដោយ'",
		));
		$size = new Zend_Dojo_Form_Element_NumberTextBox('size');
		$size->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'data-dojo-props'=>"
				'class':'fullside fullside50',
				'title':'ទំហំ',
				'placeHolder':'ទំហំ'",
				'onKeyup'=>'calculateSqure(1)'
		));
		if($data!=null){
			$paid->setValue($data['pay_first']);
			$balance->setValue($data['balance']);
			$_customer->setValue($data['client_id']);
			$_land->setValue($data['land_id']);
			
			$_date_rent->setValue($data['date_rent']);
// 			$commission->setValue($data['comission']);
// 			$staff_id->setValue($data['house_id']);
			
			$_rent_code->setValue($data['rent_no']);
			$note->setValue($data['note']);
			$_id->setValue($data['land_id']);
			$_status->setValue($data['status']);
			
			$_start_date->setValue($data['date_start']);
			$_end_date->setValue($data['date_end']);
			$rent_price_permont->setValue($data['price_permont']);
			$total_rent->setValue($data['total_price']);
			$period->setValue($data['rent_duration']);
			$_land_blog->setValue($data['land_blog']);
			$is_complete->setValue($data['is_complete']);
			$_seller->setValue($data['seller']);
			
			$north->setValue($data['north']);
			$south->setValue($data['south']);
			$west->setValue($data['west']);
			$east->setValue($data['east']);
				
			$width->setValue($data['width']);
			$height->setValue($data['height']);
			$size->setValue($data['size']);
		}
		$this->addElements(array(
				$_rent_code,$paid,$balance,
				$_customer,$_land,$_date_rent,$note
				,$commission,$staff_id,$_id,$_status,$period,
				$_end_date,$_start_date,$rent_price_permont,
				$total_rent,$_land_blog,$is_complete,
				$_seller,
				$north,$south,$west,$east,
				$width,$height,$size,
				));
		return $this;
		
	}	
}