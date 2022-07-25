<?php 
Class Product_Form_Frmproduct extends Zend_Dojo_Form {

	public function FrmAddProduct($data=null){
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$filter = 'dijit.form.FilteringSelect';
		$tvalidate = 'dijit.form.ValidationTextBox';
		$tbox = 'dijit.form.TextBox';
		$numbox = 'dijit.form.NumberTextBox';
		$tarea = 'dijit.form.Textarea';
		$chbox = 'dijit.form.CheckBox';
		
		$db = new Application_Model_DbTable_DbGlobalStock();
		
		$categoryId = new Zend_Dojo_Form_Element_FilteringSelect('categoryId');
		$categoryId->setAttribs(array(
			'dojoType'=>$filter,
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'onchange'=>'addNewCategory();'
		));
		$options = $db->getAllCategoryProduct(0,'','',1);
		$categoryId->setMultiOptions($options);
		
		$status = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$status->setAttribs(array(
			'dojoType'=>$filter,
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
		));
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$optStatus = $dbg->getViewById(3,1);
		unset($optStatus[-1]);
		
		$status->setMultiOptions($optStatus);
		
		$isService = new Zend_Dojo_Form_Element_FilteringSelect('isService');
		$isService->setAttribs(array(
			'dojoType'=>$filter,
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'onchange'=>'checkCutStock();'
		));
		$optProduct = $db->initilizeProductType();
		unset($optProduct[-1]);
		$isService->setMultiOptions($optProduct);
		
		$cutStock = new Zend_Dojo_Form_Element_FilteringSelect('isCountStock');
		$cutStock->setAttribs(array(
			'dojoType'=>$filter,
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
		));
		
		$optProduct = $db->initilizeStockType();
		unset($optProduct[-1]);
		$cutStock->setMultiOptions($optProduct);
		
		$measureId = new Zend_Dojo_Form_Element_FilteringSelect('measureId');
		$measureId->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'onchange'=>'ConvertMeasure();'
		));
		
		$dbp = new Product_Model_DbTable_DbMeasure();
		$measureId->setMultiOptions($dbp->getAllMeasureList(1));
		
		$budgetItem = new Zend_Dojo_Form_Element_FilteringSelect('budgetItem');
		$budgetItem->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
 			'onchange'=>'addNewBudgetItem();'
		));
		
		$dbp = new Budget_Model_DbTable_DbbudgetItem();
		$budgetItem->setMultiOptions($db->getAllBudgetItem(0,'', '',1,null));
		
		
		$productName = new Zend_Dojo_Form_Element_ValidationTextBox('productName');
		$productName->setAttribs(array(
			'dojoType'=>$tvalidate,
			'class'=>'fullside',
			'required'=>true,
		));
		
		$productCode = new Zend_Dojo_Form_Element_ValidationTextBox('productCode');
		$productCode->setAttribs(array(
			'dojoType'=>$tvalidate,
			'class'=>'fullside',
			'readonly'=>true,
			'required'=>true,
		));
		$dbp = new Product_Model_DbTable_DbProduct();
		$productCode->setValue($dbp->generateProductCode());
		
		$barCode = new Zend_Dojo_Form_Element_TextBox('barCode');
		$barCode->setAttribs(array(
			'dojoType'=>$tbox,
			'class'=>'fullside',
		));
		
		$costing = new Zend_Dojo_Form_Element_TextBox('costing');
		$costing->setAttribs(array(
			'dojoType'=>$numbox,
			'class'=>'fullside',
			'required'=>true,
		));
		
		$labelMeasure = new Zend_Dojo_Form_Element_ValidationTextBox('labelMeasure');
		$labelMeasure->setAttribs(array(
			'dojoType'=>$tvalidate,
			'class'=>'fullside',
			'required'=>true,
		));
		
		$qtyMeasure = new Zend_Dojo_Form_Element_TextBox('qtyMeasure');
		$qtyMeasure->setAttribs(array(
			'dojoType'=>$numbox,
			'class'=>'fullside',
			'required'=>true,
		));
		
		$note = new Zend_Dojo_Form_Element_Textarea("note");
		$note->setAttribs(array(
			'dojoType'=>$tbox,
			'class'=>'fullside',
		));
		
		$oldPhoto = new Zend_Form_Element_Hidden("oldPhoto");
		
		$convert = new Zend_Dojo_Form_Element_CheckBox("isConvert");
		$convert->setAttribs(array(
			'dojoType'=>$chbox,
			'onclick'=>'ConvertMeasure();'
		));
		
		$id = new Zend_Form_Element_Hidden('id');
		
		if(!empty($data)){
			$oldPhoto->setValue($data['image']);
			$categoryId->setValue($data['categoryId']);
			$status->setValue($data['status']);
			$productName->setValue($data['proName']);
			$note->setValue($data['note']);
			
			$isService->setValue($data['isService']);
			$productCode->setValue($data['proCode']);
			$barCode->setValue($data['barCode']);
			$measureId->setValue($data['measureId']);
			$labelMeasure->setValue($data['measureLabel']);
			$qtyMeasure->setValue($data['measureValue']);
			$id->setValue($data['proId']);
			$convert->setValue($data['isConvertMeasure']);
			$cutStock->setValue($data['isCountStock']);
			$budgetItem->setValue($data['budgetId']);
		}
		
		$this->addElements(array(
				$oldPhoto,
				$budgetItem,
				$cutStock,
				$convert,
				$qtyMeasure,
				$labelMeasure,
				$measureId,
				$costing,
				$barCode,
				$productCode,
				$isService,
				$id,
				$productName,
				$note,
				$categoryId,
				$status
		));
		return $this;
	}
	public function FrmSearchProduct($data=null){
	
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$filter = 'dijit.form.FilteringSelect';
		$tbox = 'dijit.form.TextBox';
	
		$db = new Application_Model_DbTable_DbGlobalStock();
	
		$categoryId = new Zend_Dojo_Form_Element_FilteringSelect('categoryId');
		$categoryId->setAttribs(array(
			'dojoType'=>$filter,
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
		));
		$options = $db->getAllCategoryProduct(0,'','',1);
		unset($options[-1]);
		$categoryId->setMultiOptions($options);
		$categoryId->setValue($request->getParam('categoryId'));
		
		$isService = new Zend_Dojo_Form_Element_FilteringSelect('isService');
		$isService->setAttribs(array(
			'dojoType'=>$filter,
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
		));
	
		$optProduct = $db->initilizeProductType();
		
		$isService->setMultiOptions($optProduct);
		$isService->setValue($request->getParam('isService'));
	
		$cutStock = new Zend_Dojo_Form_Element_FilteringSelect('isCountStock');
		$cutStock->setAttribs(array(
			'dojoType'=>$filter,
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
		));
	
		$optProduct = $db->initilizeStockType();
		
		$cutStock->setMultiOptions($optProduct);
		$cutStock->setValue($request->getParam('isCountStock'));
	
		$measureId = new Zend_Dojo_Form_Element_FilteringSelect('measureId');
		$measureId->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
		));
	
		$dbp = new Product_Model_DbTable_DbMeasure();
		$options = $dbp->getAllMeasureList(1);
		unset($options[-1]);
		$measureId->setMultiOptions($options);
		$measureId->setValue($request->getParam('measureId'));
	
		$budgetItem = new Zend_Dojo_Form_Element_FilteringSelect('budgetItem');
		$budgetItem->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
		));
	
		$options = $db->getAllBudgetItem(0,'', '',1);
		unset($options[-1]);
		$budgetItem->setMultiOptions($options);
		$budgetItem->setValue($request->getParam('budgetItem'));
	
		$this->addElements(array(
				$categoryId,
				$budgetItem,
				$cutStock,
				$measureId,
				$isService,
		));
		return $this;
	}
}