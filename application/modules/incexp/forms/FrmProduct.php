<?php

class Incexp_Form_FrmProduct extends Zend_Dojo_Form
{
	protected  $tr;

    public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();	
    }
    function FrmAddProduct($data,$typeItems=null){
    	
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$typeItems = empty($typeItems)?1:$typeItems;
    	$_dbgb = new Application_Model_DbTable_DbGlobal();

    	$code = new Zend_Dojo_Form_Element_TextBox('code');
    	$code->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Title")
    	));
    	//$itemsCode = $_dbgb->getItemsDetailCodeByItemsType($typeItems);
    	//$code->setValue($itemsCode);
    	
    	$title = new Zend_Dojo_Form_Element_TextBox('title');
    	$title->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>'fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Title")
    	));
    	
    	$_shortcut = new Zend_Dojo_Form_Element_TextBox('shortcut');
    	$_shortcut->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Shortcut")
    	));
    	
		$_arr_opt = array(""=>$this->tr->translate("PLEASE_SELECT"),"-1"=>$this->tr->translate("ADD_NEW"));
    	$Option = $_dbgb->getAllItems();
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_items_id = new Zend_Dojo_Form_Element_FilteringSelect("items_id");
    	$_items_id->setMultiOptions($_arr_opt);
    	$_items_id->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'onChange'=>'checkaddItems();',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	$_ordering = new Zend_Dojo_Form_Element_NumberTextBox('ordering');
    	$_ordering->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Ordering")
    	));
    	
    	$note=  new Zend_Form_Element_Textarea('note');
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit;  min-height:100px !important;'));
    	
    	$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DEACTIVE"));
    	$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
    	$_status->setMultiOptions($_arr);
    	$_status->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	
    	$_cost = new Zend_Dojo_Form_Element_NumberTextBox('cost');
    	$_cost->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'class'=>' fullside height-text',
    			'placeholder'=>$this->tr->translate("UNIT_COST"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Cost")
    	));
    	
    	$_price = new Zend_Dojo_Form_Element_NumberTextBox('price');
    	$_price->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'class'=>' fullside height-text',
    			'placeholder'=>$this->tr->translate("PRICE"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Price")
    	));
    	
    	$id = new Zend_Form_Element_Hidden('id');
    	
    	$advance_search = new Zend_Dojo_Form_Element_TextBox('advance_search');
    	$advance_search->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside height-text',
    			'placeholder'=>$this->tr->translate("SEARCH_HERE"),
    			'missingMessage'=>$this->tr->translate("SEARCH_HERE")
    	));
    	$advance_search->setValue($request->getParam("advance_search"));
    	
    	$_arr = array(-1=>$this->tr->translate("ALL"),1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DEACTIVE"));
    	$_status_search = new Zend_Dojo_Form_Element_FilteringSelect("status_search");
    	$_status_search->setMultiOptions($_arr);
    	$_status_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	$_status_search->setValue($request->getParam("status_search"));
    	
    
		$_arr_opt = array(""=>$this->tr->translate("PLEASE_SELECT"));
    	$Option = $_dbgb->getAllItems();
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_items_search = new Zend_Dojo_Form_Element_FilteringSelect("items_search");
    	$_items_search->setMultiOptions($_arr_opt);
    	$_items_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	$_items_search->setValue($request->getParam("items_search"));

    	
    	$_arr = array(-1=>$this->tr->translate("TYPE"),
    			1=>$this->tr->translate("PRODUCT_FOR_SELL"),
    			2=>$this->tr->translate("OFFICE_MATERIAL"));
    	$_product_type_search = new Zend_Dojo_Form_Element_FilteringSelect("product_type_search");
    	$_product_type_search->setMultiOptions($_arr);
    	$_product_type_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	$_product_type_search->setValue($request->getParam("product_type_search"));
    	
    	
    	
    	$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
    	$start_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'value'=>'now',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'class'=>'fullside',));
    	$_date = $request->getParam("start_date");
    	$start_date->setValue($_date);
    	
    	$end_date= new Zend_Dojo_Form_Element_DateTextBox('end_date');
    	$date = date("Y-m-d");
    	$end_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'class'=>'fullside',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'required'=>false));
    	$_date = $request->getParam("end_date");
    	if(empty($_date)){
    		$_date = date("Y-m-d");
    	}
    	$end_date->setValue($_date);
    	
    	if(!empty($data)){
    		$title->setValue($data["title"]);
    		$_shortcut->setValue($data["shortcut"]);
    		$_items_id->setValue($data["items_id"]);
    		$_ordering->setValue($data["ordering"]);
    		$note->setValue($data["note"]);
    		$_status->setValue($data["status"]);
    		$id->setValue($data["id"]);
    		
    		$code->setValue($data["code"]);
    		$_price->setValue($data["price"]);
    	}
    	$this->addElements(array(
    			$title,
				$_shortcut,
				$_items_id,
				$_ordering,
				$note,
				$_status,
				$id,
    			
				$advance_search,
				$_status_search,
				$_items_search,
    			$_product_type_search,
    			$start_date,
    			$end_date,
    			$code,
    			
    			$_cost,
    			$_price
    			));
    	return $this;
    }
}