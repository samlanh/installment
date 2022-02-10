<?php 
Class Project_Form_FrmMappin extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddMapBox($data=null){
		$db = new Application_Model_DbTable_DbGlobal();
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'required' =>'true',
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'onChange'=>'getAllPropertyBranch();'
		));
		$options = $db->getAllBranchName(null,1);
		$_branch_id->setMultiOptions($options);	
		
		
		$width = new Zend_Dojo_Form_Element_NumberSpinner("width");
		$width->setAttribs(array(
				'dojoType'=>'dijit.form.NumberSpinner',
				'class'=>'fullside',
				'data-dojo-props'=>"smallDelta:1, constraints:{min:0,max:1000,places:0}",
				'onKeyup '=>'doPinMap();'
		));
		
		$height = new Zend_Dojo_Form_Element_NumberSpinner("height");
		$height->setAttribs(array(
				'dojoType'=>'dijit.form.NumberSpinner',
				'class'=>'fullside',
				'data-dojo-props'=>"smallDelta:1, constraints:{min:0,max:1000,places:0}",
				'onKeyup '=>'doPinMap();'
		));
		
		$transform = new Zend_Dojo_Form_Element_NumberSpinner("transform");
		$transform->setAttribs(array(
				'dojoType'=>'dijit.form.NumberSpinner',
				'data-dojo-props'=>"smallDelta:1, constraints:{min:0,max:1000,places:0}",
				'class'=>'fullside',
				'onKeyup '=>'doPinMap();'
		));
		
		$top = new Zend_Dojo_Form_Element_NumberSpinner("top");
		$top->setAttribs(array(
				'dojoType'=>'dijit.form.NumberSpinner',
				'data-dojo-props'=>"smallDelta:1, constraints:{min:0,max:1000,places:0}",
				'class'=>'fullside',
				'onKeyup'=>'doPinMap();'
		));
		$left = new Zend_Dojo_Form_Element_NumberSpinner("left");
		$left->setAttribs(array(
				'dojoType'=>'dijit.form.NumberSpinner',
				'data-dojo-props'=>"smallDelta:1, constraints:{min:0,max:1000,places:0}",
				'class'=>'fullside',
				'onKeyup'=>'doPinMap();'
		));
		
		if($data!=null){
// 			$agreement_for->setValue($data['agreement_for']);
		}
		$this->addElements(array(
				$_branch_id,
				$width,
				$height,
				$transform,
				$top,$left
				));
		return $this;
	}	
}