 <?php

class Capital_Model_DbTable_DbCapital extends Zend_Db_Table_Abstract
{
    protected $_name ='ln_branch_capital';
    public function getCapiitalById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT bc.`id`,bc.`branch_id`,bc.`amount_dollar`,bc.`amount_bath`,bc.`amount_riel` FROM `ln_branch_capital` AS bc WHERE bc.`branch_id`=$id";
    	return $db->fetchRow($sql);
    }
   	Public function addCapital($_data){
   		$db = $this->getAdapter();
   		$db->beginTransaction();
   		$session_user=new Zend_Session_Namespace('auth');
   		$user_id = $session_user->user_id;
   		$branch = $_data["brance"];
   		try {
	   		$row_capital = $this->getCapiitalById($branch);
	   		if($row_capital){
	   			$amountDolloar	= $row_capital["amount_dollar"];
	   			$amountBath		= $row_capital["amount_bath"];
	   			$amountReil		= $row_capital["amount_riel"];
	   			
	   			$update_arr= array(
	   					'amount_dollar'	=>	$_data['usa'] + $amountDolloar,
	   					'amount_riel'	=>	$_data['reil'] + $amountReil,
	   					'amount_bath'	=>	$_data['bath'] + $amountBath,
	   			);
	   			$this->_name = "ln_branch_capital";
	   			$where = $this->getAdapter()->quoteInto("branch_id=?", $branch);
	   			$this->update($update_arr, $where);
	   			
	   			$arr_history = array(
	   				'transation_id'	=>	$row_capital["id"],
	   				'transation_type'	=>	1,
	   				'amount_dollar'		=>	$_data['usa'],
	   				'amount_bath'		=>	$_data['reil'],
	   				'amount_reil'		=>	$_data['bath'],
	   				'date'				=>	$_data['note'],
	   				'note'				=>	$_data['note'],
	   				'user_id'			=>	$user_id
	   			);
	   			$this->_name = "ln_capital_detail";
	   			$this->insert($arr_history);
	   		}else {
		    	$_arr = array(
		    		'branch_id'		=>	$_data['brance'],
		    	    'date'			=>	$_data['date'],
		    	    'status'		=>	$_data['status'],
		    	    'amount_dollar'	=>	$_data['usa'],
		    	    'amount_riel'	=>	$_data['reil'],
		    		'amount_bath'	=>	$_data['bath'],
		    		'note'			=>	$_data['note'],
		    		'user_id'		=> 	$user_id
		    	);
		    	$capital = $this->insert($_arr);
		    	
		    	$arr_history = array(
		    			'transation_id'	=>	$capital,
		    			'transation_type'	=>	1,
		    			'amount_dollar'		=>	$_data['usa'],
		    			'amount_bath'		=>	$_data['reil'],
		    			'amount_reil'		=>	$_data['bath'],
		    			'date'				=>	$_data['note'],
		    			'note'				=>	$_data['note'],
		    			'user_id'			=>	$user_id
		    	);
		    	$this->_name = "ln_capital_detail";
		    	$this->insert($arr_history);
	   		}
	   		$db->commit();
   		}catch (Exception $e){
   			$db->rollBack();
   			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
   		}
    }
    function getAllCapital($search=NULL){
    	$db = $this->getAdapter();
    	$sql="SELECT brc.id,br.`branch_namekh`,brc.`date`,brc.note,brc.amount_dollar,brc.amount_riel,brc.amount_bath,brc.`status`
    	FROM ln_branch_capital AS brc,`ln_branch` AS br WHERE brc.`branch_id`=br.`br_id`";
    	
    	$order=" order by id DESC";
    	$where = '';
    	
    	if(!empty($search['search'])){
    		$s_where = array();
    		$s_search = $search['search'];
    		$s_where[] = "branch_namekh LIKE '%{$s_search}%'";
    		$s_where[] = " branch_nameen LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND brc.`status` = ".$search['status'];
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    public function getpartnerById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM ln_branch_capital WHERE id = ".$db->quote($id);
    	$sql.=" LIMIT 1 ";
    	$row=$db->fetchRow($sql);
    	return $row;
    }
    function updateCapital($_data){
    	$db = $this->getAdapter();
    	$id = $_data["id"];
    	$session_user=new Zend_Session_Namespace('auth');
    	$user_id = $session_user->user_id;
    	try {
	    	$arr=array(
	    			'branch_id'		=>	$_data['brance'],
	    			'date'			=>	$_data['date'],
	    			'status'		=>	$_data['status'],
	    			'amount_dollar'	=>	$_data['usa'],
	    			'amount_riel'	=>	$_data['reil'],
	    			'amount_bath'	=>	$_data['bath'],
	    			'note'			=>	$_data['note'],
	    			'user_id'		=>	$user_id
	    	);
	    	$where = $db->quoteInto("id=?", $id);
	    	return  $this->update($arr, $where);
    	}catch (Exception $e){
    		$e->getMessage();
    		$err =$e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($err);
    	}
    }
}