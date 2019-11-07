<?php 

class Stock_Form_FrmSearchProduct extends Zend_Dojo_Form
{
	protected $tr = null;
	protected $btn =null;//text validate
	protected $filter = null;
	protected $text =null;
	protected $validate = null;

	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->btn = 'dijit.form.Button';
	}
	public function FrmSearchProduct($data=null){ 
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$_dbg = new Application_Model_DbTable_DbGlobal();
		$_dbg_pro = new Stock_Model_DbTable_DbGlobalProduct();
		
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array(
				'dojoType'=>$this->text,
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("SEARCH")));
		$_title->setValue($request->getParam("title"));
	    
		
		
		$service = new Zend_Dojo_Form_Element_FilteringSelect('service');
		$service->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("SERVIC"),
				'class'=>'fullside',
				'required'=>false
		));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
		
		$_sort_by = new Zend_Dojo_Form_Element_FilteringSelect('sort_by');
		$_sort_by->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
		));
		$_order_opt = array(
				-1=>$this->tr->translate("SORT_BY"),
				1=>$this->tr->translate("PRODUCT_CATEGORY"),
				2=>$this->tr->translate("PRODUCT_NAME"));
		$_sort_by->setMultiOptions($_order_opt);
		$_sort_by->setValue($request->getParam("sort_by"));
		
		//date 
		$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$start_date->setAttribs(array(
				'dojoType'=>"dijit.form.DateTextBox",
				'value'=>'now',
				'placeHolder'=>$this->tr->translate("START_DATE"),
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside',));
		$_date = $request->getParam("start_date");
// 		if(empty($_date)){
// 			$_date = date('Y-m-d');
// 		}
		$start_date->setValue($_date);
		
		$end_date= new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$date = date("Y-m-d");
		$end_date->setAttribs(array(
				'dojoType'=>"dijit.form.DateTextBox",
				'class'=>'fullside',
				'placeHolder'=>$this->tr->translate("END_DATE"),
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'required'=>false));
		$_date = $request->getParam("end_date");
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$end_date->setValue($_date);
		///control form product 
		$category= new Zend_Dojo_Form_Element_FilteringSelect('category');
		$category->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'required'=>false,
		));
		$category->setValue($request->getParam("category"));
		
		$_opt = array(
				-1=>$this->tr->translate("PRODUCT_CATEGORY"),);
		$category->setMultiOptions($_opt);
		//product name 
		$product= new Zend_Dojo_Form_Element_FilteringSelect('product');
		$product->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		
		$product->setValue($request->getParam("product"));
		$opt_p = array(''=>$this->tr->translate("SELECT_PRODUCT"));
		$db = new Stock_Model_DbTable_DbProduct();
		$rows= $db->getAllProductsNormal(1);//only Normal product for sale
// 		$_pro=new Accounting_Model_DbTable_DbProduct();
// 		$rows=$_pro->getProductName();
		if(!empty($rows))foreach ($rows As $row)$opt_p[$row['id']]=$row['name'];
		$product->setMultiOptions($opt_p);
		
		$location= new Zend_Dojo_Form_Element_FilteringSelect('location');
		$location->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$location->setValue($request->getParam("location"));
		$opt_ls = array(''=>$this->tr->translate("SELECT_LOCATION"));
		$row = $_dbg->getAllBranchName();
		if(!empty($row))foreach ($row As $rs)$opt_ls[$rs['br_id']]=$rs['project_name'];
		$location->setMultiOptions($opt_ls);
		
		$branch_id= new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$branch_id->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$branch_id->setMultiOptions($opt_ls);
		$branch_id->setValue($request->getParam("branch_id"));
		
		$cate= new Zend_Dojo_Form_Element_FilteringSelect('category_id');
		$cate->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'required'=>false,
				'onChange'=>'getProductByCate();',
		));
		$cate->setValue($request->getParam("category_id"));
		
		$opt_ls = array(''=>$this->tr->translate("PRODUCT_CATEGORY"));
		
		$row =  $_dbg_pro->getAllItems(3);
		
		if(!empty($row))foreach ($row As $rs)$opt_ls[$rs['id']]=$rs['name'];
		$cate->setMultiOptions($opt_ls);
		
		//supplier 
		$supplier_id= new Zend_Dojo_Form_Element_FilteringSelect('supplier_id');
		$supplier_id->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
		));
		$supplier_id->setValue($request->getParam("supplier_id"));
		$opt_p = array(''=>$this->tr->translate("SUPPLIER_NAME"));
		$_pro=new Stock_Model_DbTable_DbPurchase();
		$rows=$_pro->getSuplierName();
		if(!empty($rows))foreach ($rows As $row)$opt_p[$row['id']]=$row['sup_name'];
		$supplier_id->setMultiOptions($opt_p);
		
		$product_type=  new Zend_Dojo_Form_Element_FilteringSelect('product_type');
		$product_type->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
		));
		$_opt = array(
				0=>$this->tr->translate("TYPE"),
				1=>$this->tr->translate("PRODUCT_FOR_SELL"),
				2=>$this->tr->translate("OFFICE_MATERIAL"));
		$product_type->setMultiOptions($_opt);
		$product_type->setValue($request->getParam("product_type"));
		
		$user = new Zend_Dojo_Form_Element_FilteringSelect('user');
		$user->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$user->setValue($request->getParam('user'));
		$opt_user = array(''=>$this->tr->translate("LASTNAME_FIRSTNAME"));
		$all_user=$_dbg->getAllUser();
		if(!empty($all_user))foreach ($all_user As $row)$opt_user[$row['id']]=$row['by_user'];
		$user->setMultiOptions($opt_user);
		
		$this->addElements(array($user,$_sort_by,$product_type,$supplier_id,$cate,$branch_id,$location,$product,$category,$start_date,$end_date,$_title,$_status,$service));
		return $this;
	} 

}
