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
		
		$optProduct = array(
				'0'=>$tr->translate("PRODUCT"),
				'1'=>$tr->translate('SERVICE')
				);
		$isService->setMultiOptions($optProduct);
		
		$cutStock = new Zend_Dojo_Form_Element_FilteringSelect('isCutStock');
		$cutStock->setAttribs(array(
				'dojoType'=>$filter,
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		
		$optProduct = array(
				1=>$tr->translate("CUTSTOCK"),
				0=>$tr->translate('NONSTOCK')
		);
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
				'required'=>true,
		));
		
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
		
		$labelMeasure = new Zend_Dojo_Form_Element_TextBox('labelMeasure');
		$labelMeasure->setAttribs(array(
				'dojoType'=>$tbox,
				'class'=>'fullside',
		));
		
		$qtyMeasure = new Zend_Dojo_Form_Element_TextBox('qtyMeasure');
		$qtyMeasure->setAttribs(array(
				'dojoType'=>$numbox,
				'class'=>'fullside',
		));
		
		$note = new Zend_Dojo_Form_Element_Textarea("note");
		$note->setAttribs(array(
			'dojoType'=>$tarea,
			'class'=>'fullside',
			'style'=>'width:100%;min-height:103px; font-size:14px; font-family:khmer os Battambang'
		));
		
		$convert = new Zend_Dojo_Form_Element_CheckBox("isConvert");
		$convert->setAttribs(array(
			'dojoType'=>$chbox,
			'onclick'=>'ConvertMeasure();'
		));
		
		$id = new Zend_Form_Element_Hidden('id');
		
		if(!empty($data)){
			$categoryId->setValue($data['categoryId']);
			$status->setValue($data['status']);
			$productName->setValue($data['proName']);
			$note->setValue($data['note']);
			
			$isService->setValue($data['isService']);
			$productCode->setValue($data['proCode']);
			$barCode->setValue($data['barCode']);
			$costing->setValue($data['costing']);
			$measureId->setValue($data['measureId']);
			$labelMeasure->setValue($data['measureLabel']);
			$qtyMeasure->setValue($data['measureValue']);
			$id->setValue($data['id']);
			//$convert
			//$cutStock
		}
		
		$this->addElements(array(
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
}