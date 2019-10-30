<?php 
Class Accounting_Form_Frmitem extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate ;//text validate
	protected $filter;
	protected $t_date;
	protected $t_num;
	protected $text;
	protected $check;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
	}
	public function Frmitem($data=null){
	
		$_afl = new Zend_Dojo_Form_Element_TextBox('fl');
		$_afl->setAttribs(array('dojoType'=>$this->t_num,'class'=>'fullside',));
		
		$_aitem = new Zend_Dojo_Form_Element_TextBox('item_description');
		$_aitem->setAttribs(array('dojoType'=>$this->tvalidate,'required'=>'true','class'=>'fullside',));
		
		$_st1 = new Zend_Dojo_Form_Element_TextBox('st1');
		$_st1->setAttribs(array('dojoType'=>$this->t_num,'class'=>'fullside',));
		
		$_st2 = new Zend_Dojo_Form_Element_TextBox('st2');
		$_st2->setAttribs(array('dojoType'=>$this->t_num,'class'=>'fullside',));
		
		$_st3= new Zend_Dojo_Form_Element_TextBox('st3');
		$_st3->setAttribs(array('dojoType'=>$this->t_num,'class'=>'fullside',));
		
		
		$this->addElements(array($_afl,$_aitem,$_st1,$_st2,$_st3));
		
		return $this;
		
	}
	
	public function FrmProgramType($data=null){//for servicetype and program type
	
		$_title = new Zend_Dojo_Form_Element_ValidationTextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->tvalidate,'class'=>'fullside','required'=>'true'));
	
		$code = new Zend_Dojo_Form_Element_TextBox('code');
		$code->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside',));
		
		$_tem_desc = new Zend_Dojo_Form_Element_TextBox('item_desc');
		$_tem_desc->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside',));
	
		$_status = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		$_type = new Zend_Dojo_Form_Element_FilteringSelect('type');
		$_status_type = array(
				1=>$this->tr->translate("SERVICE"),
				2=>$this->tr->translate("PROGRAM"));
		$_type->setMultiOptions($_status_type);
		
		$_type->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		
		$_id = new Zend_Form_Element_Hidden('id');
		$_id->setAttribs(array('dojoType'=>$this->text));
		
		if($data !=null){
			$_id->setValue($data['id']);
			$_title->setValue($data['title']);
			$_tem_desc->setValue($data['item_desc']);
			$_status->setValue($data['status']);
			$_type->setValue($data['type']);
			$code->setValue($data['code']);
		}
		$this->addElements(array($code,$_title,$_tem_desc,$_status,$_type,$_id));
	
		return $this;
	
	}
	
	
}