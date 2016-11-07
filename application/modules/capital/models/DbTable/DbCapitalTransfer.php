 <?php

class Capital_Model_DbTable_DbCapitalTransfer extends Zend_Db_Table_Abstract
{
    protected $_name ='ln_capital_transfer';
    public function getAmountByBranch($branch){
    	$db = $this->getAdapter();
    	$rowGetAmount = "SELECT brc.`branch_id`,brc.`amount_bath`,brc.`amount_dollar`,brc.`amount_riel` 
    								FROM `ln_branch_capital` AS brc WHERE brc.`branch_id` = $branch";
    	return $db->fetchRow($rowGetAmount);
    }
   	Public function addTransfer($_data){
   		$db = $this->getAdapter();
   		$db->beginTransaction();
   		$session_user=new Zend_Session_Namespace('auth');
   		$user_id = $session_user->user_id;
   		
   		//control declaration 
   		$brance_from = $_data["brance_from"];
   		$dollar_from = $_data["usa_from"];
   		$bath_from = $_data["bath_from"];
   		$riel_from =$_data["reil_from"];
   		
   		$brance_to = $_data["brance_to"];
   		$dollar_to = $_data["usa_to"];
   		$bath_to = $_data["bath_to"];
   		$riel_to =$_data["reil_to"];
   		
   		$dollar_transfer = $_data["usa"];
   		$bath_transfer = $_data["bath"];
   		$riel_transfer =$_data["reil"];
   		
   		try {
   			if($_data["brance_from"] != $_data["brance_to"]){
   				if($_data["status"] == 1){
					$row_brance_from	=	$this->getAmountByBranch($brance_from);
					$row_brance_to 		= 	$this->getAmountByBranch($brance_to);
	   				
	   				if($row_brance_from){
	   					$amount_dollar_from = $row_brance_from["amount_dollar"] - $dollar_transfer;
	   					$amount_bath_from 	= $row_brance_from["amount_bath"] - $bath_transfer;
	   					$amount_reil_from 	= $row_brance_from["amount_riel"] - $riel_transfer;
	   					
	   					$db->getProfiler()->setEnabled(true);
	   					$update_brance_from  = array(
	   							'amount_dollar'		=>	$amount_dollar_from,
	   							'amount_bath'		=>	$amount_bath_from,
	   							'amount_riel'		=>	$amount_reil_from,
	   					);
	   					$this->_name = "ln_branch_capital";
	   					$where = $db->quoteInto("branch_id=?", $_data["brance_from"]);
	   					$this->update($update_brance_from, $where);
	   					
	   					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
	   					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
	   					$db->getProfiler()->setEnabled(false);
	   					
	   				}
	   				if($row_brance_to){
	   					$amount_dollar_to 	= $row_brance_to["amount_dollar"] + $dollar_transfer;
	   					$amount_bath_to 	= $row_brance_to["amount_bath"] + $bath_transfer;
	   					$amount_reil_to 	= $row_brance_to["amount_riel"] + $riel_transfer;
	   					
	   					$db->getProfiler()->setEnabled(true);
	   					$update_brance_to  = array(
	   							'amount_dollar'		=>	$amount_dollar_to,
	   							'amount_bath'		=>	$amount_bath_to,
	   							'amount_riel'		=>	$amount_reil_to,
	   					);
	   					$this->_name = "ln_branch_capital";
	   					$where = $db->quoteInto("branch_id=?", $_data["brance_to"]);
	   					$this->update($update_brance_to, $where);
	   					
	   					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
	   					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
	   					$db->getProfiler()->setEnabled(false);
	   					
	   				}else{
	   					$db->getProfiler()->setEnabled(true);
	   					$insert_arr = array(
	   							'branch_id'		=>	$brance_to,
	   							'date'			=>	$_data['date'],
	   							'status'		=>	$_data['status'],
	   							'amount_dollar'	=>	$_data['usa'],
	   							'amount_riel'	=>	$_data['reil'],
	   							'amount_bath'	=>	$_data['bath'],
	   							'note'			=>	$_data['note'],
	   							'user_id'		=> 	$user_id
	   					);
	   					$this->_name = "ln_branch_capital";
	   					$this->insert($insert_arr);
	   					
	   					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
	   					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
	   					$db->getProfiler()->setEnabled(false);
	   				}
	   				
	   				$db->getProfiler()->setEnabled(true);
			    	$_arr = array(
			    		'from_branch'		=>	$_data['brance_from'],
			    		'to_branch'			=>	$_data['brance_to'],
			    	    'date'				=>	$_data['date'],
			    	    'amount_dollar'		=>	$_data['usa'],
			    		'amount_bath'		=>	$_data['bath'],
			    	    'amount_riel'		=>	$_data['reil'],
			    		'note'				=>	$_data['note'],
			    		'user_id'			=> 	$user_id,
			    	    'status'			=>	$_data['status'],
			    	);
			    	$this->_name= "ln_capital_transfer";
				    $capital = $this->insert($_arr);
				    
				    Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
				    Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
				    $db->getProfiler()->setEnabled(false);
				    
				    $db->getProfiler()->setEnabled(true);
				    $arr_history = array(
				    		'transation_id'		=>	$capital,
				    		'transation_type'	=>	2,
				    		'amount_dollar'		=>	$_data['usa'],
				    		'amount_bath'		=>	$_data['reil'],
				    		'amount_reil'		=>	$_data['bath'],
				    		'date'				=>	$_data['note'],
				    		'note'				=>	$_data['note'],
				    		'user_id'			=>	$user_id,
				    		'status'			=>	$_data["status"],	
				    );
				    $this->_name = "ln_capital_detail";
				    $this->insert($arr_history);
				    
				    Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
				    Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
				    $db->getProfiler()->setEnabled(false);
   				}else {
   					$db->getProfiler()->setEnabled(true);
   					$_arr = array(
   							'from_branch'		=>	$_data['brance_from'],
   							'to_branch'			=>	$_data['brance_to'],
   							'date'				=>	$_data['date'],
   							'amount_dollar'		=>	$_data['usa'],
   							'amount_bath'		=>	$_data['bath'],
   							'amount_riel'		=>	$_data['reil'],
   							'note'				=>	$_data['note'],
   							'user_id'			=> 	$user_id,
   							'status'			=>	$_data['status'],
   					);
   					$transfer = $this->_name= "ln_capital_transfer";
   					$this->insert($_arr);
   					
   					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
   					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
   					$db->getProfiler()->setEnabled(false);
   					
   					$db->getProfiler()->setEnabled(true);
   					$arr_history = array(
   							'transation_id'	=>	$transfer,
   							'transation_type'	=>	2,
   							'amount_dollar'		=>	$_data['usa'],
   							'amount_bath'		=>	$_data['reil'],
   							'amount_reil'		=>	$_data['bath'],
   							'date'				=>	$_data['note'],
   							'note'				=>	$_data['note'],
   							'user_id'			=>	$user_id,
   							'status'			=>	$_data['status'],
   					);
   					$this->_name = "ln_capital_detail";
   					$this->insert($arr_history);
   					
   					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
   					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
   					$db->getProfiler()->setEnabled(false);
   				}
   			}
   			exit();
	    	$db->commit();
   		}catch (Exception $e){
   			$db->rollBack();
   			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
   		}
    }
    function updateTransfer($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	$id = $_data["id"];
    	$session_user = new Zend_Session_Namespace('auth');
    	$user_id = $session_user->user_id;
    	 
    	//control declaration
    	$brance_from = $_data["brance_from"];
    	$dollar_from = $_data["usa_from"];
    	$bath_from = $_data["bath_from"];
    	$riel_from =$_data["reil_from"];
    
    	$brance_to = $_data["brance_to"];
    	$dollar_to = $_data["usa_to"];
    	$bath_to = $_data["bath_to"];
    	$riel_to =$_data["reil_to"];
    
    	$dollar_transfer = $_data["usa"];
    	$bath_transfer = $_data["bath"];
    	$riel_transfer =$_data["reil"];
    	
    	$statuss = $_data["status"];
    	try {
    		if($_data["brance_from"] != $_data["brance_to"]){
	    			$row_transfer = $this->getTransferByID($id);
	    			if($row_transfer){
	    				$branch_from 	= $row_transfer["from_branch"];
	    				$branch_to	 	= $row_transfer["to_branch"];
	    				$amount_dollor	= $row_transfer["amount_dollar"];
	    				$amount_bath	= $row_transfer["amount_bath"];
	    				$amount_reil	= $row_transfer["amount_riel"];
	    				$status = $row_transfer["status"];
	    				 
	    				$row_branch_from = $this->getAmountByBranch($branch_from);
	    				$row_branch_to	 = $this->getAmountByBranch($branch_to);
	    				if($row_transfer["status"] == 1){
		    				$oldamount_dollar = $row_branch_from["amount_dollar"] + $amount_dollor;
		    				$oldamount_bath = $row_branch_from["amount_bath"] + $amount_bath;
		    				$oldamount_reil = $row_branch_from["amount_riel"] + $amount_reil;
		    				
		    				$db->getProfiler()->setEnabled(true);
		    				$arr_oldamount = array(
		    						'amount_dollar'		=>	$oldamount_dollar,
		    						'amount_bath'		=>	$oldamount_bath,
		    						'amount_riel'		=>	$oldamount_reil,
		    				);
		    				$this->_name = "ln_branch_capital";
		    				$where = $db->quoteInto("branch_id=?", $branch_from);
		    				$this->update($arr_oldamount, $where);
		    				
		    				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
		    				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
		    				$db->getProfiler()->setEnabled(false);
		    				
		    				if($row_branch_to){
		    					$oldamount_dollar_to = $row_branch_to["amount_dollar"] - $amount_dollor;
		    					$oldamount_bath_to = $row_branch_to["amount_bath"] - $amount_bath;
		    					$oldamount_reil_to = $row_branch_to["amount_riel"] - $amount_reil;
		    					
		    					if($oldamount_dollar_to < $dollar_transfer or $oldamount_bath_to < $bath_transfer or $oldamount_reil_to < $riel_transfer  ){
		    						Application_Form_FrmMessage::Sucessfull("The Branch that have been transfer is not enought money!", "/capital/capital-transfer");
		    					}else {
		    						$db->getProfiler()->setEnabled(true);
		    						$arr_oldamount = array(
		    								'amount_dollar'		=>	$row_branch_to["amount_dollar"] - $amount_dollor,
		    								'amount_bath'		=>	$row_branch_to["amount_bath"] - $amount_bath,
		    								'amount_riel'		=>	$row_branch_to["amount_riel"] - $amount_reil,
		    						);
		    						$this->_name = "ln_branch_capital";
		    						$where = $db->quoteInto("branch_id=?", $branch_to);
		    						$this->update($arr_oldamount, $where);
		    				
		    						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
		    						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
		    						$db->getProfiler()->setEnabled(false);
		    					}
		    				}else{
		    					$db->getProfiler()->setEnabled(true);
		    					$insert_arr = array(
		    							'branch_id'		=>	$branch_to,
		    							'date'			=>	$_data['date'],
		    							'status'		=>	1,
		    							'amount_dollar'	=>	$_data['usa'],
		    							'amount_riel'	=>	$_data['reil'],
		    							'amount_bath'	=>	$_data['bath'],
		    							'note'			=>	$_data['note'],
		    							'user_id'		=> 	$user_id
		    					);
		    					$this->_name = "ln_branch_capital";
		    					$this->insert($insert_arr);
		    				
		    					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
		    					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
		    					$db->getProfiler()->setEnabled(false);
		    				}
	    				}

	    				if($_data["status"] == 1){
			    			$row_brance_from	=	$this->getAmountByBranch($brance_from);
			    			$row_brance_to 		= 	$this->getAmountByBranch($brance_to);
			    				
			    			if($row_brance_from){
			    				$amount_dollar_from = $row_brance_from["amount_dollar"] - $dollar_transfer;
			    				$amount_bath_from 	= $row_brance_from["amount_bath"] - $bath_transfer;
			    				$amount_reil_from 	= $row_brance_from["amount_riel"] - $riel_transfer;
			    		
			    				$db->getProfiler()->setEnabled(true);
			    				$update_brance_from  = array(
			    						'amount_dollar'		=>	$amount_dollar_from,
			    						'amount_bath'		=>	$amount_bath_from,
			    						'amount_riel'		=>	$amount_reil_from,
			    				);
			    				$this->_name = "ln_branch_capital";
			    				$where = $db->quoteInto("branch_id=?", $_data["brance_from"]);
			    				$this->update($update_brance_from, $where);
			    				
			    				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
			    				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
			    				$db->getProfiler()->setEnabled(false);
			    		
			    			}
			    			if($row_brance_to){
			    				$amount_dollar_to 	= $row_brance_to["amount_dollar"] + $dollar_transfer;
			    				$amount_bath_to 	= $row_brance_to["amount_bath"] + $bath_transfer;
			    				$amount_reil_to 	= $row_brance_to["amount_riel"] + $riel_transfer;
			    		
			    				$db->getProfiler()->setEnabled(true);
			    				$update_brance_to  = array(
			    						'amount_dollar'		=>	$amount_dollar_to,
			    						'amount_bath'		=>	$amount_bath_to,
			    						'amount_riel'		=>	$amount_reil_to,
			    				);
			    				$this->_name = "ln_branch_capital";
			    				$where = $db->quoteInto("branch_id=?", $_data["brance_to"]);
			    				$this->update($update_brance_to, $where);
			    				
			    				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
			    				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
			    				$db->getProfiler()->setEnabled(false);
			    		
			    			}else{
			    		
			    				$db->getProfiler()->setEnabled(true);
			    				$insert_arr = array(
			    						'branch_id'		=>	$brance_to,
			    						'date'			=>	$_data['date'],
			    						'status'		=>	$_data['status'],
			    						'amount_dollar'	=>	$_data['usa'],
			    						'amount_riel'	=>	$_data['reil'],
			    						'amount_bath'	=>	$_data['bath'],
			    						'note'			=>	$_data['note'],
			    						'user_id'		=> 	$user_id
			    				);
			    				$this->_name = "ln_branch_capital";
			    				$this->insert($insert_arr);
			    				
			    				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
			    				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
			    				$db->getProfiler()->setEnabled(false);
			    			}
			    			
			    			$db->getProfiler()->setEnabled(true);
			    			$_arr = array(
			    					'from_branch'		=>	$_data['brance_from'],
			    					'to_branch'			=>	$_data['brance_to'],
			    					'date'				=>	$_data['date'],
			    					'amount_dollar'		=>	$_data['usa'],
			    					'amount_bath'		=>	$_data['bath'],
			    					'amount_riel'		=>	$_data['reil'],
			    					'note'				=>	$_data['note'],
			    					'user_id'			=> 	$user_id,
			    					'status'			=>	$_data['status'],
			    			);
			    			$this->_name= "ln_capital_transfer";
			    			$where = $db->quoteInto("id", $id);
			    			$this->update($_arr, $where);
			    			
			    			Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
			    			Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
			    			$db->getProfiler()->setEnabled(false);
	    				}else {
	    					$db->getProfiler()->setEnabled(true);
	    					$_arr = array(
	    							'from_branch'		=>	$_data['brance_from'],
	    							'to_branch'			=>	$_data['brance_to'],
	    							'date'				=>	$_data['date'],
	    							'amount_dollar'		=>	$_data['usa'],
	    							'amount_bath'		=>	$_data['bath'],
	    							'amount_riel'		=>	$_data['reil'],
	    							'note'				=>	$_data['note'],
	    							'user_id'			=> 	$user_id,
	    							'status'			=>	$_data['status'],
	    					);
	    					$this->_name= "ln_capital_transfer";
	    					$where = $db->quoteInto("id", $id);
	    					$this->update($_arr, $where);
	    					
	    					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
	    					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
	    					$db->getProfiler()->setEnabled(false);
	    				}
	    			}
    		}
    		$db->commit();
    	}catch (Exception $e){
    		$db->rollBack();
    		$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    	}
    }
    public function getAmountByBranceId($data){
    	$db = $this->getAdapter();
    	$sql="SELECT brc.`amount_dollar`,brc.`amount_bath`,brc.`amount_riel` FROM `ln_branch_capital` AS brc WHERE brc.`branch_id`=$data AND brc.`status`=1";
    	return $db->fetchRow($sql);
    }
    function getTransferByID($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT 
				  lct.`id`,
				  lct.`from_branch`,
				  lct.`to_branch`,
				  lct.`date`,
				  lct.`amount_dollar`,
				  lct.`amount_bath`,
				  lct.`amount_riel`,
				  lct.`note`,
				  lct.`status` 
				FROM
				  `ln_capital_transfer` AS lct WHERE lct.`id` =$id";
    	return $db->fetchRow($sql);
    }
    function getAllTransfer($search=null){
    	$db = $this->getAdapter();
    	try {
	    	$sql = "SELECT 
					  lct.`id`,
					  (SELECT 
					    lb.`branch_namekh` 
					  FROM
					    `ln_branch` AS lb 
					  WHERE lb.`br_id` = lct.`from_branch`) AS from_branch,
					  (SELECT 
					    lb.`branch_namekh` 
					  FROM
					    `ln_branch` AS lb 
					  WHERE lb.`br_id` = lct.`to_branch`) AS to_branch,
					  lct.`date`,
					  lct.`amount_dollar`,
					  lct.`amount_bath`,
					  lct.`amount_riel`,
					  lct.`note`,
					  lct.`status` 
					FROM
					  `ln_capital_transfer` AS lct 
					WHERE 1";
	    	$order=" ORDER BY lct.`id` DESC";
	    	$where = '';
	    	
	    	if($search['brance_from']>-1){
	    		$where.= " AND lct.`from_branch` = ".$search['brance_from'];
	    	}
	    	if($search['brance_to']>-1){
	    		$where.= " AND lct.`to_branch` = ".$search['brance_to'];
	    	}
	    	if($search['status']>-1){
	    		$where.= " AND status = ".$search['status'];
	    	}
	    	return $db->fetchAll($sql.$where.$order);
    	}catch (Exception $e){
    		$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    	}
    }
    
}