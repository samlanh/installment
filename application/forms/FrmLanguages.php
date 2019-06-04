<?php
class Application_Form_FrmLanguages
{
    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
    }	
	public static function  getCurrentlanguage($lang=1,$layout=false){	
		// set up translation adapter
		$session_lang=new Zend_Session_Namespace('lang');
		$lang_id=$session_lang->lang_id;
// 		echo $lang_id;exit();
		if($lang_id==1 OR empty($lang_id)){
			$str="km";
		}elseif($lang_id==3){
			$str="ch";
		}
		else{
			$str="en";
		 }	
		$tr = new Zend_Translate('ini', PUBLIC_PATH.'/lang/'.$str,  null, array('scan' => Zend_Translate::LOCALE_FILENAME));
		// set locale
		$tr->setLocale('en');
		$session_language=new Zend_Session_Namespace('lang');		
		if(!empty($session_language->language)){
			$tr->setLocale(strtolower($session_language->language));
		}
		return $tr;
	}	
}