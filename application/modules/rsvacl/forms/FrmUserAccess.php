<?php 
class RsvAcl_Form_FrmUserAccess extends Zend_Form
{
	private $plus;
	public function getPlus()
	{
		return $this->plus;
	}	
	
	public function init()
    {
    	
    	//user name    	
    	$user_type=new Zend_Form_Element_Text('user_type');
    	$user_type->setAttribs(array(
    		'id'=>'user_type',
    	));
    	$this->addElement($user_type);
    	
    	//Main parent of user type
		$db=new Application_Model_DbTable_DbGlobal();
		
		$rs=$db->getGlobalDb("SELECT ut.user_type_id,ut.user_type FROM rms_acl_user_type AS ut");
		$options=array();
		
		foreach($rs as $read) $options[$read['user_type_id']]=$read['user_type'];
		
		$user_type_id=new Zend_Form_Element_Select('user_type_id');		
    	$user_type_id->setMultiOptions($options);
    	$this->addElement($user_type_id);		
		
		
		
		$rs=$db->getGlobalDb("SELECT acl.acl_id, CONCAT(acl.module,'/', acl.controller,'/', acl.action) AS user_access FROM rms_acl_acl AS acl");
		$options=array();
		
		foreach($rs as $read) $options[$read['acl_id']]=$read['user_access'];
		
		$user_access_id=new Zend_Form_Element_Select('acl_id');		
    	$user_access_id->setMultiOptions($options);
    	$this->addElement($user_access_id);
    	
    
  
		
		//Project assignment  
		//Sophen add here to assign project
		$user_session_id = new Zend_Session_Namespace('auth');
		$id=$user_session_id->user_type_id; 
		//echo $user_id;exit;
		$db_user=new Application_Model_DbTable_DbGlobal();
		if($id==1){
			$sql = "select acl.acl_id,CONCAT(acl.module,'/', acl.controller,'/', acl.action) AS user_access from rms_acl_acl as acl";
		}else {
			$sql="SELECT acl.acl_id, CONCAT(acl.module,'/', acl.controller,'/', acl.action) AS user_access, acl.status FROM rms_acl_user_access AS ua 
		      INNER JOIN rms_acl_user_type AS ut ON (ua.user_type_id = ut.parent_id)
			  INNER JOIN rms_acl_acl AS acl ON (acl.acl_id = ua.acl_id) WHERE ut.user_type_id =".$id;
		}
		
		//print_r($sql); exit;
		$project=$db_user->getGlobalDb($sql);		
          if($project){
			$i=0;			
						
			foreach($project as $read){
				//print_r($read);exit;
				$i++;
				
				
				$check_fund = new Zend_Form_Element_Checkbox('acl_id_'.$i, 
									array('label' => $read['user_access']));
			    $check_fund ->setUncheckedValue('')					
							->setCheckedValue($read['acl_id']);
			
				$this->addElement($check_fund);	
				
			}
			$this->plus=$i;
		  }
    
    //remove decorator
    Application_Model_Decorator::removeAllDecorator($this); 		
        		
    }
       
    public function setAcl($arr){
    	//print_r($arr);exit;
        for($i=1;$i<=$this->getPlus();$i++)
    	{
    		foreach($arr as $div){
    			$check=$this->getElement('acl_id_'.$i);
				if($div['acl_id']==$check->getCheckedValue()){
					$check->setChecked(true);
				}
    		}
    	}
	}
    
}

?>