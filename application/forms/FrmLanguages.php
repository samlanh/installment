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
		if($lang_id==1){
			$str="";
		}else{$str="en"; }	
		$tr = new Zend_Translate('ini', PUBLIC_PATH.'/lang/'.$str,  null, array('scan' => Zend_Translate::LOCALE_FILENAME));
		// set locale
		$tr->setLocale('en');
		$session_language=new Zend_Session_Namespace('language');		
		if(!empty($session_language->language)){
			$tr->setLocale(strtolower($session_language->language));
		}
		return $tr;
	}	
	public static  function getActiveLanguage(){
		$baseurl =  Zend_Controller_Front::getInstance()->getBaseUrl();
		$md_site_language = new Application_Model_DbTable_DbSiteLanguages();
		$site_langs = $md_site_language->getSiteLanguageActive();
		$str ="";
		foreach ($site_langs as $i => $lang) {
		 $str .="<a href='". $baseurl ."/langs/index/index?ln=".$lang['language_short'] ."'>".
					"<img src='". $baseurl ."/images/flag/". $lang['icon'] ."' title='". $lang['language'] .
			 			"' alt='". $lang['language'] ."' class='icon32' />
				</a>";
		}
		return $str;
	}
}