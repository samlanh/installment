<?php 
Class Property_Form_FrmSale extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmSale($data=null,$action=null){
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Application_Model_DbTable_DbGlobal();
		
		$_sale_code = new Zend_Dojo_Form_Element_TextBox('sale_code');
		$_sale_code->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'readonly'=>true,
				'class'=>'fullside',
				'style'=>'color:red; font-weight: bold;'
		));
		$sale_number = $db->getSalePropertyNo();
		$_sale_code->setValue($sale_number);
		
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
		
		$_customer = new Zend_Dojo_Form_Element_FilteringSelect('customer');
		$_customer->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onChange'=>'showPopupFormCustomer()',
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
			if(!empty($data['house_id'])){$land = $data['house_id'];}else{ $land='';}
		$row_land = $db->getBuyLand($action,$land);
		if(!empty($row_land))foreach($row_land AS $row){
			$opt_land[$row['id']]=$row['name'];
		}
		$_land->setMultiOptions($opt_land);
		
		$other_fee = new Zend_Dojo_Form_Element_NumberTextBox('other_fee');
		$other_fee->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				//'readonly'=>true,
				'onkeyup'=>'calculateDiscount();',
				'class'=>'fullside',
		));
		
		$_total_sold = new Zend_Dojo_Form_Element_NumberTextBox('total_sold');
		$_total_sold->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'readonly'=>true,
				'style'=>'color:#008; font-weight: bold;',
		));
		
		$_total_sold_after = new Zend_Dojo_Form_Element_NumberTextBox('total_sold_after');
		$_total_sold_after->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'onkeyup'=>'calculateDiscount(), Balance();',
		));
		
		$sold_price = new Zend_Dojo_Form_Element_NumberTextBox('sold_price');
		$sold_price->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'onkeyup'=>'Balance(),calculateSqure(3);',
				'style'=>'color:red; font-weight: bold;',
				//'readonly'=>true
		));
		$_date_buy = new Zend_Dojo_Form_Element_DateTextBox('date_buy');
		$_date_buy->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required' =>'true',
				'class'=>'fullside',
				//'onchange'=>'checkReleaseDate();',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$_date_buy->setValue(date("Y-m-d"));
		
		$discount = new Zend_Dojo_Form_Element_NumberTextBox('discount');
		$discount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'onKeyup'=>'calculateDiscount();',
				'class'=>'fullside50 fullside',
				'placeHolder'=>'ជាសាច់ប្រាក់',
				//'invalidMessage'=>'អាចបញ្ជូលពី 1 ដល់ 99'
		));
		$discount_percent = new Zend_Dojo_Form_Element_NumberTextBox('discount_percent');
		$discount_percent->setAttribs(array(
				'data-dojo-Type'=>'dijit.form.NumberTextBox',
				'data-dojo-props'=>"regExp:'[0-9]{1,2}',
				'name':'discount_percent',
				'id':'discount_percent',
				'onKeyup':'calculateDiscount();',
				'class':'fullside fullside50',
				'placeHolder':'ភាគរយ(%)',
				'invalidMessage':'អាចបញ្ជូលពី 1 ដល់ 99'"
		));
		
		
		$paid = new Zend_Dojo_Form_Element_NumberTextBox('deposit');
		$paid->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'onkeyup'=>'Balance();',
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
		
		$agency = new Zend_Dojo_Form_Element_TextBox('agency');
		$agency->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		//$agency->setValue('');
		
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
		
		$price_square = new Zend_Dojo_Form_Element_NumberTextBox('price_per_square');
		$price_square->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'data-dojo-props'=>"
				'class':'fullside fullside50',
				'title':'តម្លៃក្នុង១ម៉ែត្រការ៉េ',
				'placeHolder':'តម្លៃក្នុង១ម៉ែត្រការ៉េ'",
				'onKeyup'=>'calculateSqure(2)'
		));
		
		if($data!=null){
			$paid->setValue($data['paid_amount']);
			$balance->setValue($data['balance']);
			$other_fee->setValue($data['other_fee']);
			$_total_sold->setValue($data['price_before']);
			$sold_price->setValue($data['price_sold']);
			$_total_sold_after->setValue($data['land_price_after']);
			$_customer->setValue($data['client_id']);
			$_land->setValue($data['house_id']);
			
			$_date_buy->setValue($data['buy_date']);
			$discount->setValue($data['discount_amount']);
			$discount_percent->setValue($data['discount_percent']);
			$commission->setValue($data['comission']);
// 			$staff_id->setValue($data['house_id']);
			
			$_sale_code->setValue($data['sale_number']);
			$note->setValue($data['note']);
			$_id->setValue($data['house_id']);
			$_status->setValue($data['status']);
			$_land_blog->setValue($data['land_blog']);
			
			$north->setValue($data['north']);
			$south->setValue($data['south']);
			$west->setValue($data['west']);
			$east->setValue($data['east']);
			
			$width->setValue($data['width']);
			$height->setValue($data['height']);
			$size->setValue($data['size']);
			$price_square->setValue($data['price_per_square']);
			$_seller->setValue($data['seller']);
			$agency->setValue($data['agency']);
		}
		$this->addElements(array(
				$_sale_code,$paid,$balance,$_total_sold_after,
				$_customer,$_land,$other_fee,$_total_sold,$sold_price,$_date_buy,$discount,$discount_percent,$note
				,$commission,$staff_id,$_id,$_status,$_land_blog,$_seller,
				$north,$south,$west,$east,
				$width,$height,$size,$price_square,$agency
				));
		return $this;
		
	}	
}