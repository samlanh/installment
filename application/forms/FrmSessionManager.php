<?php

class Application_Form_FrmSessionManager extends Zend_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
    }

	static function clearSessionSearch(){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$module=$request->getModuleName();
		
		if($module != 'transfer'){
			//Clear session in module transfer 
	    	$session_transfer=new Zend_Session_Namespace('search_transfer');        	
	        $session_transfer->unsetAll();
		}  
        
		if($module != 'user'){
	        //Clear session in module user
	    	$session_search_user=new Zend_Session_Namespace('search_user');        	
	        $session_search_user->unsetAll();
		}

		if($module != 'subagent'){
	        //Clear session in module sub agent
	    	$session_search_subagent=new Zend_Session_Namespace('search_subagent');        	
	        $session_search_subagent->unsetAll();
		}
        
		if($module != 'reports'){
	        //clear session in module Report
	        $session_search_rpt=new Zend_Session_Namespace('search_rpt');        	
	        $session_search_rpt->unsetAll();
		}
		
		if($module != 'agent'){
	        //Cleat session in module agent
	    	$session_search_agent=new Zend_Session_Namespace('search_agent');
	        $session_search_agent->unsetAll();
		}
		
		if($module != 'exchange'){
	        //Cleat session in module exchange
	    	$session_search_agent=new Zend_Session_Namespace('search_exhcange');
	        $session_search_agent->unsetAll();
		}
		
		if($module != 'acl'){
			//Cleat session in module acl
			$session_search_agent=new Zend_Session_Namespace('search_acl');
			$session_search_agent->unsetAll();
			
			$session_search_agent=new Zend_Session_Namespace('search_user_type');
			$session_search_agent->unsetAll();			
			
			$session_search_agent=new Zend_Session_Namespace('search_user_type');
			$session_search_agent->unsetAll();
		}
	}
}

