<?php 
Class Capital_Form_FrmCapitale extends Zend_Dojo_Form {
	public function frmCapital($_data=null)
	{
		/* Form Elements & Other Definitions Here ... */
		$branch = new Zend_Dojo_Form_Element_TextBox("branch");
		$branch->setAttribs(array('dojoType'=>'dijit.form.TextBox',
 				'class'=>'fullside',
				'required' =>'true',));
		$brance = new Zend_Dojo_Form_Element_FilteringSelect('brance');
		$brance->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
				'required' =>'true',
		));
		$db = new Application_Model_DbTable_DbGlobal();
		$rows = $db->getAllBranchName();
		$options='';
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['br_id']]=$row['branch_namekh'];
		}
		$brance->setMultiOptions($options);
		
		$date=new Zend_Dojo_Form_Element_DateTextBox('date');
		$date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox'
		));
		$date->setValue(date('Y-m-d'));
		$date->setValue(date('Y-m-d'));
		$_stutas = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_stutas ->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
		));
		$options= array(1=>"ប្រើប្រាស់",0=>"មិនប្រើប្រាស់");
		$_stutas->setMultiOptions($options);
		$note=new Zend_Dojo_Form_Element_TextBox('note');
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox'));
		$usa=new Zend_Dojo_Form_Element_NumberTextBox('usa');
		$usa->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'placeHolder'   =>  '0',
				'class'	=>	'td',
				'Onkeyup'	=>	'validateTransfer(1);',
		));
		$usa->setValue(0);
		$bath=new Zend_Dojo_Form_Element_NumberTextBox('bath');
		$bath->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'	=>	'td',
				'required'	=> true,
				'Onkeyup'	=>	'validateTransfer(2);'
		));
		$bath->setValue(0);
		$reil=new Zend_Dojo_Form_Element_NumberTextBox('reil');
		$reil->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'placeHolder'   =>  '0',
				'class'	=>	'td',
				'required'	=> true,
				'Onkeyup'	=>	'validateTransfer(3);'
		));
		$reil->setValue(0);
		$id = new Zend_Form_Element_Hidden('id');
		if($_data!=null){
			$brance->setValue($_data['id']);
			$date->setValue($_data['date']);
			$_stutas->setValue($_data['status']);
			$note->setValue($_data['note']);
			$usa->setValue($_data['amount_dollar']);
			$reil->setValue($_data['amount_riel']);
			$bath->setValue($_data['amount_bath']);
			$id->setValue($_data['id']);
		
		}
		$this->addElements(array($branch,$brance,$date,$_stutas,
				$note,$bath,$usa,$reil,$id));
		return $this;
	}
	public function frmCapitalTransfer($_data=null)
	{
		/* Form Elements & Other Definitions Here ... */
		
		$db = new Application_Model_DbTable_DbGlobal();
		$rows = $db->getAllBranchName();
		$options=array(-1=>'សូមជ្រើសរើស សាខា');
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['br_id']]=$row['branch_namekh'];
		}
		
		$brance_from = new Zend_Dojo_Form_Element_FilteringSelect('brance_from');
		$brance_from->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				// 				'class'=>'fullside',
				'required' =>'true',
				'OnChange'	=> 'getAmountFrom();',
		));
		$brance_from->setMultiOptions($options);
		
		$brance_to = new Zend_Dojo_Form_Element_FilteringSelect('brance_to');
		$brance_to->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				// 				'class'=>'fullside',
				'required' =>'true',
				'OnChange'	=> 'getAmountTo();',
		));
		
		$brance_to->setMultiOptions($options);
	
		$date=new Zend_Dojo_Form_Element_DateTextBox('date');
		$date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox'
		));
		$date->setValue(date('Y-m-d'));
		$date->setValue(date('Y-m-d'));
		$_stutas = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_stutas ->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'OnChange' => 'statusCheck();'
		));
		$options= array(-1=>"ជ្រើសរើស ស្ថានភាព",1=>"ប្រើប្រាស់",0=>"មិនប្រើប្រាស់");
		$_stutas->setMultiOptions($options);
		$note=new Zend_Dojo_Form_Element_TextBox('note');
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'	=>	'fullside',
				));
		$usa=new Zend_Dojo_Form_Element_NumberTextBox('usa');
		$usa->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'placeHolder'   =>  '0',
				'class'	=>	'td',
				'Onkeyup'	=>	'validateTransfer(1);',
		));
		$usa->setValue(0);
		$bath=new Zend_Dojo_Form_Element_NumberTextBox('bath');
		$bath->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'	=>	'td',
				'required'	=> true,
				'Onkeyup'	=>	'validateTransfer(2);'
		));
		$bath->setValue(0);
		$reil=new Zend_Dojo_Form_Element_NumberTextBox('reil');
		$reil->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'placeHolder'   =>  '0',
				'class'	=>	'td',
				'required'	=> true,
				'Onkeyup'	=>	'validateTransfer(3);'
		));
		$reil->setValue(0);
		$usa_from=new Zend_Dojo_Form_Element_NumberTextBox('usa_from');
		$usa_from->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'required'	=> true,
				'class'	=>	'td'
		));
		$usa_from->setValue(0);
		$bath_from=new Zend_Dojo_Form_Element_NumberTextBox('bath_from');
		$bath_from->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'placeHolder'   =>  '0',
				'required'	=> true,
				'class'	=>	'td'
		));
		$bath_from->setValue(0);
		$reil_from=new Zend_Dojo_Form_Element_NumberTextBox('reil_from');
		$reil_from->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'placeHolder'   =>  '0',
				'required'	=> true,
				'class'	=>	'td'
		));
		$reil_from->setValue(0);
		$usa_to=new Zend_Dojo_Form_Element_NumberTextBox('usa_to');
		$usa_to->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'placeHolder'   =>  '0',
				'required'	=> true,
				'class'	=>	'td'
		));
		$usa_to->setValue(0);
		$bath_to=new Zend_Dojo_Form_Element_NumberTextBox('bath_to');
		$bath_to->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'placeHolder'   =>  '0',
				'required'	=> true,
				'class'	=>	'td'
		));
		$bath_to->setValue(0);
		$reil_to=new Zend_Dojo_Form_Element_NumberTextBox('reil_to');
		$reil_to->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'placeHolder'   =>  '0',
				'required'	=> true,
				'class'	=>	'td'
		));
		$reil_to->setValue(0);
		$id = new Zend_Form_Element_Text('id');
		
		$btnSearch=new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$btnSearch->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch',
				'label'	=>	'Search'));
		if($_data!=null){
			$brance_from->setValue($_data['from_branch']);
			$brance_to->setValue($_data['to_branch']);
			$date->setValue($_data['date']);
			$_stutas->setValue($_data['status']);
			$note->setValue($_data['note']);
			$usa->setValue($_data['amount_dollar']);
			$reil->setValue($_data['amount_riel']);
			$bath->setValue($_data['amount_bath']);
			$id->setValue($_data['id']);
	
		}
		$this->addElements(array($brance_from,$brance_to,$date,$_stutas,
				$note,$bath,$usa,$reil,$usa_from,$bath_from,$reil_from,$usa_to,$bath_to,$reil_to,$id,$btnSearch));
		return $this;
	}
	
	public function frmCapitalResource($_data=null)
	{
		/* Form Elements & Other Definitions Here ... */
		$brance = new Zend_Dojo_Form_Element_FilteringSelect('brance');
		$brance->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				//'class'=>'fullside',
				'required' =>'true',
				'OnChange'	=>	'getAmounts();'
		));
		$db = new Application_Model_DbTable_DbGlobal();
		$rows = $db->getAllBranchName();
		$options=array(0=>'សូមជ្រើសរើស សាខា');
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['br_id']]=$row['branch_namekh'];
		}
		$brance->setMultiOptions($options);
	
		$date=new Zend_Dojo_Form_Element_DateTextBox('date');
		$date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox'
		));
		$date->setValue(date('Y-m-d'));
		$date->setValue(date('Y-m-d'));
		$_stutas = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_stutas ->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
		));
		$options= array(1=>"ប្រើប្រាស់",0=>"មិនប្រើប្រាស់");
		$_stutas->setMultiOptions($options);
		
		$note=new Zend_Dojo_Form_Element_TextBox('note');
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox','class'	=>	'fullside',));
		
		$usa=new Zend_Dojo_Form_Element_NumberTextBox('usa');
		$usa->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'placeHolder'   =>  '0',
				'class'	=>	'td',
				'Onkeyup'	=>	'validateTransfer(1);',
		));
		$usa->setValue(0);
		
		$bath=new Zend_Dojo_Form_Element_NumberTextBox('bath');
		$bath->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'	=>	'td',
				'required'	=> true,
				'Onkeyup'	=>	'validateTransfer(2);'
		));
		
		$bath->setValue(0);
		$reil=new Zend_Dojo_Form_Element_NumberTextBox('reil');
		$reil->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'placeHolder'   =>  '0',
				'class'	=>	'td',
				'required'	=> true,
				'Onkeyup'	=>	'validateTransfer(3);'
		));
		$reil->setValue(0);
		
		$dollar_current = new Zend_Dojo_Form_Element_NumberTextBox("dollar_current");
		$dollar_current->setAttribs(array(
			'dojoType'	=>	'dijit.form.NumberTextBox',
			'class'		=>	'td',
		));
		
		$bath_current = new Zend_Dojo_Form_Element_NumberTextBox("bath_current");
		$bath_current->setAttribs(array(
			'dojoType'	=>	'dijit.form.NumberTextBox',
			'class'		=>	'td'
		));
		
		$reil_current = new Zend_Dojo_Form_Element_NumberTextBox("reil_current");
		$reil_current->setAttribs(array(
			'dojoType'	=>	'dijit.form.NumberTextBox',
			'class'		=>	'td'
		));
		$id = new Zend_Form_Element_Hidden('id');
		if($_data!=null){
			$brance->setValue($_data['id']);
			$date->setValue($_data['date']);
			$_stutas->setValue($_data['status']);
			$note->setValue($_data['note']);
			$usa->setValue($_data['amount_dollar']);
			$reil->setValue($_data['amount_riel']);
			$bath->setValue($_data['amount_bath']);
			$id->setValue($_data['id']);
		}
		$this->addElements(array($dollar_current,$reil_current,$bath_current,$brance,$date,$_stutas,
				$note,$bath,$usa,$reil,$id));
		return $this;
	}
	public function frmSearch(){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Application_Model_DbTable_DbGlobal();
		$rows = $db->getAllBranchName();
		$options=array(-1=>'សូមជ្រើសរើស សាខា');
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['br_id']]=$row['branch_namekh'];
		}
		
		$brance_from = new Zend_Dojo_Form_Element_FilteringSelect('brance_from');
		$brance_from->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect'
		));
		$brance_from->setMultiOptions($options);
		$brance_from->setValue($request->getParam("brance_from"));
		
		$brance_to = new Zend_Dojo_Form_Element_FilteringSelect('brance_to');
		$brance_to->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect'
		));
		$brance_to->setValue($request->getParam("brance_to"));
		$brance_to->setMultiOptions($options);
		
		$btnSearch=new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$btnSearch->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch',
				'label'	=>	'Search'));
		
		$_stutas = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_stutas ->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
		));
		$options= array(-1=>"ជ្រើសរើស ស្ថានភាព",1=>"ប្រើប្រាស់",0=>"មិនប្រើប្រាស់");
		$_stutas->setMultiOptions($options);
		$_stutas->setValue($request->getParam("status"));
		
		$date=new Zend_Dojo_Form_Element_DateTextBox('date');
		$date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox'
		));
		$date->setValue(date('Y-m-d'));
		$search = new Zend_Dojo_Form_Element_TextBox("search");
		$search->setAttribs(array('dojoType' => 'dijit.form.TextBox','placeHolder'   =>  'ឈ្មោះសាខា ជាភាសាខ្មែរ ឬ អង់គ្លេស',));
		$search->setValue($request->getParam("search"));
		
		
		return $this->addElements(array($search,$brance_from,$brance_to,$_stutas,$btnSearch,$date));
	}
	
}
?>