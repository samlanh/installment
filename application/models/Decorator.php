<?php

class Application_Model_Decorator
{
	public function init()
	{
		/* Initialize action controller here */
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public static function removeAllDecorator($form)
	{
		$elements=$form->getElements(); 
		foreach($elements as $element){
			$element->removeDecorator('HtmlTag');
			$element->removeDecorator('DtDdWrapper');
			$element->removeDecorator('Label');		
			$element->removeDecorator('Errors');	
		}
		
	}	
	public static function removeAllDecoratorExceptError($form)
	{
		$elements=$form->getElements();
		foreach($elements as $element){
			$element->removeDecorator('HtmlTag');
			$element->removeDecorator('DtDdWrapper');
			$element->removeDecorator('Label');
		}
	}
	public function getMenuLeft($arr_menu,$controller,$module=null){
		$menu='';
		$i=0;
		$session_user=new Zend_Session_Namespace('auth');
		$arr_actin=$session_user->arr_actin;
// 		print_r($arr_actin);
		
		//if(is_array($arr_menu)){
			foreach($arr_menu as $param=>$url){
				//$access = array_search($module.'/'.$param,$arr_actin);
				//if($access!=''){
						if($param==$controller){
							$uri=$this->baseUrl().'/'.$module.'/'.$param;
							$url=str_replace('href=""', 'href="'.$uri.'"', $url);
							$menu.=$this->spanMenu($url,$controller=null);
						}else{
							if($module!=null){
								$uri=$this->baseUrl().'/'.$module.'/'.$param;
								$url=str_replace('href=""', 'href="'.$uri.'"', $url);
								$menu.=$url;
							}else{
								$menu.=$url;
							}
						}
				//}
				$i++;
			}
			return $menu;
	 // }
		//return null;
	}
	public function spanMenu($url,$controller,$class="current-left"){
		$temp=str_replace('<a', '<a class="'.$class.'"', $url);
		$temp=str_replace('</a>', '</span>', $temp);
		return $temp;
	}
	public function baseUrl(){
		return Zend_Controller_Front::getInstance()->getBaseUrl();
	}
}