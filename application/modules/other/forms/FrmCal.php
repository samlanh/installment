<?php 
Class Global_Form_FrmCal extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmCalculator($data=null){
		
		$_hundred = new Zend_Dojo_Form_Element_TextBox('d_hundred');
		$_hundred->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'onkeyup'=>'Calcuhundred()'));
		
		$_fifty = new Zend_Dojo_Form_Element_TextBox('d_fifty');
		$_fifty->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'onkeyup'=>'Calfifty()'));

		$_tweenty = new Zend_Dojo_Form_Element_TextBox('d_tweenty');
		$_tweenty->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'onkeyup'=>'Caltweenty()'));
		
		$_ten = new Zend_Dojo_Form_Element_TextBox('d_ten');
		$_ten->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'onkeyup'=>'Calten()'));
		
		$_five = new Zend_Dojo_Form_Element_TextBox('d_five');
		$_five->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'onkeyup'=>'Calfive()'));
		
		$_one = new Zend_Dojo_Form_Element_TextBox('d_one');
		$_one->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'onkeyup'=>'Calone()'));
		
		///*** result of calculator ///***
		$rs_hundred = new Zend_Dojo_Form_Element_TextBox('rs_hundred');
		$rs_hundred->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$rs_fifty = new Zend_Dojo_Form_Element_TextBox('rs_fifty');
		$rs_fifty->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$rs_tweenty = new Zend_Dojo_Form_Element_TextBox('rs_tweenty');
		$rs_tweenty->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$rs_ten = new Zend_Dojo_Form_Element_TextBox('rs_ten');
		$rs_ten->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside','readonly'=>true));
		
		$rs_five = new Zend_Dojo_Form_Element_TextBox('rs_five');
		$rs_five->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside','readonly'=>true));
		
		$rs_one = new Zend_Dojo_Form_Element_TextBox('rs_one');
		$rs_one->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside','readonly'=>true));
		
		//**control khmer currency**//
		$_fiftyhousend = new Zend_Dojo_Form_Element_TextBox('r_fiftyhousend');
		$_fiftyhousend->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'onkeyup'=>'Calfiftyhousend()'));
		
		$_tenthousend = new Zend_Dojo_Form_Element_TextBox('r_tenthousend');
		$_tenthousend->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'onkeyup'=>'Caltenthousend()'));
		
		$_fivehousend = new Zend_Dojo_Form_Element_TextBox('r_fivehousend');
		$_fivehousend->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'onkeyup'=>'Calfivehousend()'));
		
		$_tweentyhousend = new Zend_Dojo_Form_Element_TextBox('r_tweentyhousend');
		$_tweentyhousend->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'onkeyup'=>'Caltweentyhousend()'));
		
		$_thousend = new Zend_Dojo_Form_Element_TextBox('thousend');
		$_thousend->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'onkeyup'=>'Calthousend()'));
		
		$_fivehundred = new Zend_Dojo_Form_Element_TextBox('r_fivehundred');
		$_fivehundred->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'onkeyup'=>'Calfivehundred()'));
		
		$_onehundred = new Zend_Dojo_Form_Element_TextBox('r_onehundred');
		$_onehundred->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'onkeyup'=>'Calonehundred()'));
		//**rs khmer currency**//
		
		$rs_fiftyhousend = new Zend_Dojo_Form_Element_TextBox('rs_fiftyhousend');
		$rs_fiftyhousend->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$rs_tenthousend = new Zend_Dojo_Form_Element_TextBox('rs_tenthousend');
		$rs_tenthousend->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$rs_fivehousend = new Zend_Dojo_Form_Element_TextBox('rs_fivehousend');
		$rs_fivehousend->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$rs_tweentyhousend = new Zend_Dojo_Form_Element_TextBox('rs_tweentyhousend');
		$rs_tweentyhousend->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$rs_thousend = new Zend_Dojo_Form_Element_TextBox('rs_thousend');
		$rs_thousend->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$rs_fivehundred = new Zend_Dojo_Form_Element_TextBox('rs_fivehundred');
		$rs_fivehundred->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$rs_onehundred = new Zend_Dojo_Form_Element_TextBox('rs_onehundred');
		$rs_onehundred->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$rs_totalkh = new Zend_Dojo_Form_Element_TextBox('rs_kh_total');
		$rs_totalkh->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$rs_dollar_total = new Zend_Dojo_Form_Element_TextBox('rs_dollar_total');
		$rs_dollar_total->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$reil_to_dollar = new Zend_Dojo_Form_Element_TextBox('total_reil_dollar');
		$reil_to_dollar->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$_rate = new Zend_Dojo_Form_Element_TextBox('rate');
		$_rate->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		$_rate->setValue(4000);
		
		$amount_total = new Zend_Dojo_Form_Element_TextBox('total_amount');
		$amount_total->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside black',
				'readonly'=>true));
		
		$this->addElements(array(
			  $_hundred, $_fifty, $_tweenty, $_ten,$_five, $_one,
			  $rs_hundred, $rs_fifty, $rs_tweenty, $rs_ten,$rs_five, $rs_one,
			  $_fiftyhousend,$_tenthousend, $_fivehousend, $_tweentyhousend, $_thousend, $_fivehundred, $_onehundred,
			  $rs_fiftyhousend,$rs_tenthousend, $rs_fivehousend, $rs_tweentyhousend, $rs_thousend, $rs_fivehundred, $rs_onehundred,
			  $rs_totalkh, $rs_dollar_total,$reil_to_dollar,$_rate,$amount_total
			  ));
		
		return $this;
		
	}
	
}