<?php

class Product_Model_DbTable_DbCategory extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_category';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllCategory($search){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$normal=$tr->translate('NOMAL');
		$material=$tr->translate('MATERIAL');
    	$sql="SELECT c.id,
    		(SELECT p.categoryName FROM $this->_name As p WHERE p.id=c.parentId LIMIT 1) AS parentTitle,
	    	c.categoryName,
			CASE 
				WHEN isMaterial=0 THEN   '$normal'
				WHEN isMaterial=1 THEN  '$material'
			END
			AS categoryType,
	    	c.createDate,
	    	(SELECT first_name from rms_users as u where u.id = c.userId LIMIT 1) as user ,
    		(SELECT name_en from ln_view where type=3 and key_code = c.status LIMIT 1) AS status
    	FROM $this->_name As c
    		WHERE 1
    	";
    	
    	$from_date =(empty($search['start_date']))? '1': " c.createDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " c.createDate <= '".$search['end_date']." 23:59:59'";
    	
    	$where_date = " AND ".$from_date." AND ".$to_date;
    	$where='';
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		$s_where[] = " c.categoryName LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND c.status = ".$search['status'];
    	}
    	
    	$order=' ORDER BY c.id DESC  ';
    	
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where_date.$where.$order);
    }
   
    function addCategory($data){
    	try
    	{
    		$db = new Application_Model_DbTable_DbGlobalStock();
    		$result = $db->dataExisting($this->_name,"categoryName='".$data['categoryTitle']."'");
    		
    		if(empty($result)){
	    		$arr = array(
	    				'parentId'=>$data['parent_id'],
	    				'categoryName'=>$data['categoryTitle'],
						'isMaterial'=>$data['isMaterial'],
	    				'note'=>$data['note'],
	    				'createDate'=>date("Y-m-d"),
	    				'status'=>1,
	    				'userId'=>$this->getUserId(),
	    			);
	    		$this->insert($arr);
    		}else{
    			Application_Form_FrmMessage::Sucessfull("DATA_EXISTING", "/product/category/add",2);
    		}
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL", "/product/category/add",2);
    	}
    }
    function updateCategory($data){
    	try
    	{
    		$arr = array(
    			'parentId'=>$data['parent_id'],
				'isMaterial'=>$data['isMaterial'],
    			'categoryName'=>$data['categoryTitle'],
    			'note'=>$data['note'],
    			'status'=>$data['status'],
    			'userId'=>$this->getUserId(),
    		);
    		
    		$where = 'id = '.$data['id'];
			$this->update($arr, $where);
			
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		Application_Form_FrmMessage::Sucessfull("UPDATE_FAIL", "/product/category/index",2);
    	}
    }
    function getDataRow($recordId){
    	$db = $this->getAdapter();
    	$sql=" SELECT * FROM $this->_name WHERE id=".$recordId." LIMIT 1";
    	return $db->fetchRow($sql);
    }
   
}