<?php

class Application_Model_Decorator
{
	public function init()
	{
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
	
	public function baseUrl(){
		return Zend_Controller_Front::getInstance()->getBaseUrl();
	}
}