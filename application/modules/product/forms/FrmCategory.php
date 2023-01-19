<?php 
Class Product_Form_FrmCategory extends Zend_Dojo_Form {

	public function FrmAddCategory($data=null){
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$filter = 'dijit.form.FilteringSelect';
		$tvalidate = 'dijit.form.ValidationTextBox';
		$tarea = 'dijit.form.Textarea';
		
		$db = new Application_Model_DbTable_DbGlobalStock();
		
		$parentId = new Zend_Dojo_Form_Element_FilteringSelect('parent_id');
		$parentId->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
		));
		$options = $db->getAllCategoryProduct(0,'','',1);
		$parentId->setMultiOptions($options);
		
		$status = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$status->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
		));
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$optStatus = $dbg->getViewById(3,1);
		unset($optStatus[-1]);
		
		$status->setMultiOptions($optStatus);
		
		
		$categoryTitle = new Zend_Dojo_Form_Element_ValidationTextBox('categoryTitle');
		$categoryTitle->setAttribs(array(
			'dojoType'=>$tvalidate,
			'class'=>'fullside',
			'required'=>true,
		));
		
		$note = new Zend_Dojo_Form_Element_Textarea("note");
		$note->setAttribs(array(
			'dojoType'=>$tarea,
			'class'=>'fullside',
			'style'=>'width:100%;min-height:103px; font-size:14px; font-family:khmer os Battambang'
		));

		$isMaterial = new Zend_Dojo_Form_Element_FilteringSelect('isMaterial');
		$isMaterial->setAttribs(array(
			'dojoType'=>$filter,
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
		));
		
		$optcat = $db->initilizeCategoryType();
		unset($optcat[-1]);
		$isMaterial->setMultiOptions($optcat);
		
		$id = new Zend_Form_Element_Hidden('id');
		
		if(!empty($data)){
			$parentId->setValue($data['parentId']);
			$status->setValue($data['status']);
			$isMaterial->setValue($data['isMaterial']);
			$categoryTitle->setValue($data['categoryName']);
			$note->setValue($data['note']);
			$id->setValue($data['id']);
		}
		
		$this->addElements(array(
				$id,
				$categoryTitle,
				$note,
				$parentId,
				$status,
				$isMaterial
		));
		return $this;
	}
}