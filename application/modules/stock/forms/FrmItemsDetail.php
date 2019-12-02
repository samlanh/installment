<?php

class Stock_Form_FrmItemsDetail extends Zend_Dojo_Form
{
	protected  $tr;
	protected $filter;

    public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();	
    	$this->filter = 'dijit.form.FilteringSelect';
    }
    function FrmAddItemsDetail($data,$typeItems=null){
    	
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$typeItems = empty($typeItems)?1:$typeItems;
    	$_dbgb_pro = new Stock_Model_DbTable_DbGlobalProduct();
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$code = new Zend_Dojo_Form_Element_TextBox('code');
    	$code->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Title")
    	));
    	$itemsCode = $_dbgb_pro->getItemsDetailCodeByItemsType($typeItems);
    	$code->setValue($itemsCode);
    	
    	$title = new Zend_Dojo_Form_Element_TextBox('title');
    	$title->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>'fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Title")
    	));
		
		$title_en = new Zend_Dojo_Form_Element_TextBox('title_en');
    	$title_en->setAttribs(array(
    			'required'=>'true',
    			'dojoType'=>'dijit.form.ValidationTextBox',
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
    	$Option = $_dbgb_pro->getAllItems($typeItems);
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_items_id = new Zend_Dojo_Form_Element_FilteringSelect("items_id");
    	$_items_id->setMultiOptions($_arr_opt);
    	$_items_id->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'onChange'=>'checkaddItems();',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside',));
    	
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
    	
    	$_arr = array(1=>$this->tr->translate("COUNT_STOCK"),2=>$this->tr->translate("NOT_COUNT_STOCK"));
    	$_product_type = new Zend_Dojo_Form_Element_FilteringSelect("product_type");
    	$_product_type->setMultiOptions($_arr);
    	$_product_type->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	$_cost = new Zend_Dojo_Form_Element_NumberTextBox('cost');
    	$_cost->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'class'=>' fullside height-text',
    			'required'=>'true',
    			'placeholder'=>$this->tr->translate("UNIT_COST"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Cost")
    	));
    	
    	$_price = new Zend_Dojo_Form_Element_NumberTextBox('price');
    	$_price->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'class'=>' fullside height-text',
    			'required'=>'true',
    			'placeholder'=>$this->tr->translate("SELL_PRICE"),
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
    	$Option = $_dbgb_pro->getAllItems($typeItems);//degree
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_items_search = new Zend_Dojo_Form_Element_FilteringSelect("items_search");
    	$_items_search->setMultiOptions($_arr_opt);
    	$_items_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
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
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	$_product_type_search->setValue($request->getParam("product_type_search"));
    	
    	$_arr = array(
    			-1=>$this->tr->translate("SELECT_TYPE"),
    			0=>$this->tr->translate("IS_VALIDATE"),
    			1=>$this->tr->translate("ONE_PAYMENTONLY"));
    	if($request->getActionName()!='index'){
    		unset($_arr[-1]);
    	}
    	
    	$_onepayment = new Zend_Dojo_Form_Element_FilteringSelect("is_onepayment");
    	$_onepayment->setMultiOptions($_arr);
    	$_onepayment->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	$_onepayment->setValue($request->getParam("is_onepayment"));
    	
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
    	
    	$_arr_opt_branch = array(""=>$this->tr->translate("ONE_PAYMENT"));
    	$optionBranch = $_dbgb->getAllBranchName();
    	if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['br_id']]=$row['project_name'];
    	$_branch_search = new Zend_Dojo_Form_Element_FilteringSelect("branch_search");
    	$_branch_search->setMultiOptions($_arr_opt_branch);
    	$_branch_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	$_branch_search->setValue($request->getParam("branch_search"));
    	
    	$auto_payment = new Zend_Dojo_Form_Element_FilteringSelect("auto_payment");
    	$_arr = array(
    			-1=>$this->tr->translate("SELECT_TYPE"),
    			0=>$this->tr->translate("NORMAL"),
    			1=>$this->tr->translate("AUTO_PAYMENT"));
    	if($request->getActionName()!='index'){
    		unset($_arr[-1]);
    	}
    	$auto_payment->setMultiOptions($_arr);
    	$auto_payment->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	$auto_payment->setValue($request->getParam("auto_payment"));
    	
    	$_total_score = new Zend_Dojo_Form_Element_NumberTextBox('total_score');
    	$_total_score->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'class'=>' fullside height-text',
    			'required'=>'true',
    			//'readonly'=>'true',
    			'onKeyUp'=>'calculateMaxAverage();',
    			'placeholder'=>$this->tr->translate("TOTAL_SCORE"),
    	));
    	
    	$_amount_subject = new Zend_Dojo_Form_Element_NumberTextBox('amount_subject');
    	$_amount_subject->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'class'=>' fullside height-text',
    			'required'=>'true',
    			'onKeyUp'=>'calculateMaxAverage();',
    			'placeholder'=>$this->tr->translate("AMOUNT_SUBJECT"),
    	));
    	
    	$_max_average = new Zend_Dojo_Form_Element_NumberTextBox('max_average');
    	$_max_average->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'class'=>' fullside height-text',
    			'required'=>'true',
    			'readonly'=>'true',
    			'placeholder'=>$this->tr->translate("MAX_AVERAGE"),
    	));
    	
    	if(!empty($data)){
    		$title->setValue($data["title"]);
			$title_en->setValue($data["title_en"]);
    		$_shortcut->setValue($data["shortcut"]);
    		$_items_id->setValue($data["items_id"]);
    		$_ordering->setValue($data["ordering"]);
    		$note->setValue($data["note"]);
    		$_status->setValue($data["status"]);
    		$id->setValue($data["id"]);
    		$_onepayment->setValue($data["is_onepayment"]);
    		if($request->getActionName()=='edit'){
    			$code->setValue($data["code"]);
    		}
    		if ($data["items_type"]==3){ // product
	    		$_product_type->setValue($data["product_type"]);
	    		$_cost->setValue($data["cost"]);
	    		$_price->setValue($data["price"]);
    		}
    		$_total_score->setValue($data["total_score"]);
    		$_amount_subject->setValue($data["amount_subject"]);
    		$_max_average->setValue($data["max_average"]);
    	}
    	$this->addElements(array(
    			$auto_payment,
    			$_total_score,
    			$_amount_subject,
    			$_max_average,
    			$title,
				$title_en,
				$_shortcut,
				$_items_id,
				$_ordering,
				$note,
				$_status,
				$id,
    			$_onepayment,
				$advance_search,
				$_status_search,
				$_items_search,
    			$_product_type_search,
    			$start_date,
    			$end_date,
    			$code,
    			$_product_type,
    			$_cost,
    			$_price,
    			
    			$_branch_search
    			));
    	return $this;
    }
}