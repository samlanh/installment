<?php

class Stock_Model_DbTable_DbReceivedNote extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_transferstock';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getReceiveNoteNo($branch_id){
    	$db = $this->getAdapter();
    	$sql="SELECT (id+1)FROM `rms_received_note` WHERE branch_id = $branch_id ORDER BY id DESC  LIMIT 1";
    	$acc_no = $db->fetchOne($sql);
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	$pre="RC";
    	for($i = $acc_no;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
    function getAllTranferByToBranch($branch_id,$transfer_id=null){
    	$db = $this->getAdapter();
    	$sql="SELECT ts.id,
				CONCAT(COALESCE(ts.transfer_no,''),' ',COALESCE((SELECT branch_nameen FROM `rms_branch` WHERE br_id=ts.from_location LIMIT 1),''),'-',COALESCE((SELECT branch_nameen FROM `rms_branch` WHERE br_id=ts.to_location LIMIT 1),'')) AS `name`
				 FROM `rms_transferstock` AS ts
				 WHERE ts.is_received = 0 AND ts.status=1 AND ts.to_location=$branch_id";
    	if (!empty($transfer_id)){
    		$sql.=" OR ts.id=$transfer_id";
    	}
    	return $db->fetchAll($sql);
    }
    function getTransferInfo($transfer_id){
    	$db = $this->getAdapter();
    	 
    	$sql=" SELECT ts.id,
				CONCAT(COALESCE(ts.transfer_no,''),' ',COALESCE((SELECT branch_nameen FROM `rms_branch` WHERE br_id=ts.from_location LIMIT 1),''),'-',COALESCE((SELECT branch_nameen FROM `rms_branch` WHERE br_id=ts.to_location LIMIT 1),'')) AS `name`
				,ts.*
				 FROM `rms_transferstock` AS ts
				 WHERE ts.id= $transfer_id LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function getTransferDetail($transfer_id){
    	$db = $this->getAdapter();
    	$dbgb = new Application_Model_DbTable_DbGlobal();
    	$currentLang = $dbgb->currentlang();
    	$coloum="ide.title_en";
    	if ($currentLang==1){
    		$coloum="ide.title";
    	}
    	
    	$sql=" SELECT 
			(SELECT $coloum FROM `rms_product` AS ide WHERE ide.id = td.pro_id LIMIT 1) AS product_name,
			td.* 
			FROM `rms_transferdetail` AS td 
			WHERE
				td.transferid=$transfer_id ";
    	return $db->fetchAll($sql);
    }
    
    function addReceiveNoteTransfer($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$receive_no = $this->getReceiveNoteNo($_data['branch_id']);
    		$_arr = array(
    				'branch_id' 	=>$_data['branch_id'],
    				'transfer_id' 	=>$_data['transfer_id'],
    				'receive_no'	=>$receive_no,
    				'receive_date'	=>$_data['receive_date'],
    				'note'=>$_data['note'],
    				'create_date'=>date("Y-m-d H:i:s"),
    				'modify_date'=>date("Y-m-d H:i:s"),
    				'user_id'=>$this->getUserId(),
    				'status'=>1,
    		);
    		$this->_name ="rms_received_note";
    		$receive_id = $this->insert($_arr);
    		
    		$arrupd = array(
    				'is_received' 	=>1,
    				'modify_date'=>date("Y-m-d H:i:s"),
    				'received_by'=>$this->getUserId(),
    				);
    		$this->_name ="rms_transferstock";
    		$where = "id = ".$_data['transfer_id'];
    		$this->update($arrupd, $where);
    		
    		$ids = explode(',', $_data['identity']);
    		if (!empty($ids)){
    			foreach ($ids as $i){
    				$_arr = array(
    						'receive_id'=> $receive_id,
    						'pro_id'	=> $_data['pro_id_'.$i],
    						'qty'		=> $_data['qty_'.$i],
    						'note'		=> $_data['remark_'.$i],
    				);
    				$this->_name='rms_received_note_detail';
    				$this->insert($_arr);
    				 
    				$rs = $this->checkisProductSet($_data['pro_id_'.$i]);
    				if(empty($rs)){//normal product
    					$qty_stock = $this->getProductLocation($_data['pro_id_'.$i],$_data['f_branch']);
    					$this->_name="rms_product_location";
    					if(!empty($qty_stock)){
    						$qty = $qty_stock['pro_qty'] - $_data['qty_'.$i];
    						$array = array(
    								'pro_qty'=>$qty,
    						);
    						$where = " id = ".$qty_stock['id'];
    						$this->update($array, $where);
    					}
    					//to location
    					$qty_stock = $this->getProductLocation($_data['pro_id_'.$i],$_data['branch']);
    					if(!empty($qty_stock)){
    						$qty = $qty_stock['pro_qty'] + $_data['qty_'.$i];
    						$array = array(
    								'pro_qty'=>$qty,
    						);
    						$where = " id = ".$qty_stock['id'];
    						$this->update($array, $where);
    					}else{
    						$data = array(
    								'pro_id'=>$_data['pro_id_'.$i],
    								'brand_id'=>$_data['branch'],
    								'pro_qty'=>$_data['qty_'.$i],
    								'note'=>'ពីផ្ទេរទំនិញចូល',
    								'date' =>date("Y-m-d"),
    								'user_id'=>$this->getUserId(),
    								'status'=>1);
    						$this->insert($data);
    					}
    				}//prodcut set
    				else{
    					$rsset = $this->getProductSet($_data['pro_id_'.$i]);
    					if(!empty($rsset)){
    						foreach($rsset as $rs){
    							$qty_stock = $this->getProductLocation($rs['subpro_id'],$_data['f_branch']);
    							$this->_name="rms_product_location";
    							if(!empty($qty_stock)){
    								$qty = $qty_stock['pro_qty'] - $_data['qty_'.$i];//ត្រូវគុណចំនួនត្រូវផ្ទេរជាមួយនឹងបរិមាណទំនិញក្នុងមួយ setទើបត្រូវ
    								$array = array(
    										'pro_qty'=>$qty,
    								);
    								$where = " id = ".$qty_stock['id'];
    								$this->update($array, $where);
    							}
    							//to location
    							$qty_stock = $this->getProductLocation($rs['subpro_id'],$_data['branch']);
    							if(!empty($qty_stock)){
    								$qty = $qty_stock['pro_qty'] + $_data['qty_'.$i];
    								$array = array(
    										'pro_qty'=>$qty,
    								);
    								$where = " id = ".$qty_stock['id'];
    								$this->update($array, $where);
    							}else{
    								$data = array(
    										'pro_id'=>$_data['pro_id_'.$i],
    										'brand_id'=>$_data['branch'],
    										'pro_qty'=>$_data['qty_'.$i],
    										'note'=>'ពីផ្ទេរទំនិញចូល',
    										'date' =>date("Y-m-d"),
    										'user_id'=>$this->getUserId(),
    										'status'=>1
    								);
    								$this->insert($data);
    							}
    						}
    					}
    				}
    			}
    		}
    		
    		$db->commit();
    		return true;
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		return false;
    	}
    }
    function checkisProductSet($product_id){//if product is set
    	$db = $this->getAdapter();
    	$sql="SELECT * FROM  rms_product WHERE is_productseat=1 AND id = ".$product_id;
    	return $db->fetchRow($sql);
    }
    function getProductLocation($pro_id,$location_id){
    	$db = $this->getAdapter();
    	$sql="select * from rms_product_location where pro_id=".$pro_id." AND brand_id = ".$location_id;
    	$row = $db->fetchRow($sql);
    	return $row;
    }
    function getProductSet($product_id){// select item for product set
    	$db = $this->getAdapter();
    	$sql="SELECT * FROM `rms_product_setdetail` WHERE pro_id  = ".$product_id;
    	return $db->fetchAll($sql);
    }
    
    function getAllTransfer($search=null){  
    	$db=$this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql = "SELECT 
			r.id,
			(SELECT project_name FROM `ln_project` WHERE br_id=r.branch_id LIMIT 1) AS branch_name,
			r.receive_no,
			r.receive_date,
			(SELECT CONCAT(COALESCE(ts.transfer_no,''),' ',COALESCE((SELECT project_name FROM `ln_project` WHERE br_id=ts.from_location LIMIT 1),''))  FROM `rms_transferstock` AS ts
							 WHERE ts.id=r.transfer_id LIMIT 1) AS transfer_no,
			r.note,
			(SELECT first_name FROM `rms_users` WHERE id=r.user_id LIMIT 1) AS user_name
			  ";
    	$sql.=$dbp->caseStatusShowImage("r.status");
    	$sql.=" FROM `rms_received_note` AS r WHERE 1 ";
    	
    	//(SELECT name_en FROM `rms_view` WHERE TYPE=1 AND key_code=s.status ) as status
    	$from_date =(empty($search['start_date']))? '1': " r.receive_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " r.receive_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
	    if(!empty($search['title'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['title']));
	    	$s_where[] = " r.receive_no LIKE '%{$s_search}%'";
	    	$s_where[] = " r.note LIKE '%{$s_search}%'";
	    	$s_where[] = " (SELECT CONCAT(COALESCE(ts.transfer_no,''),' ',COALESCE((SELECT branch_nameen FROM `rms_branch` WHERE br_id=ts.from_location LIMIT 1),''))  FROM `rms_transferstock` AS ts
							 WHERE ts.id=r.transfer_id LIMIT 1) LIKE '%{$s_search}%'";
	    	$where .=' AND ( '.implode(' OR ',$s_where).')';
	    }
	    $where.=$dbp->getAccessPermission('r.branch_id');
	    
    	$order=" ORDER BY r.id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
    function getReceiveNoteById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT r.* FROM `rms_received_note` AS r WHERE r.id= $id ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('r.branch_id');
    	$sql.=" LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function getReceiveNoteDetailById($id){
    	$db = $this->getAdapter();
    	$dbgb = new Application_Model_DbTable_DbGlobal();
    	$currentLang = $dbgb->currentlang();
    	$coloum="ide.title_en";
    	if ($currentLang==1){
    		$coloum="ide.title";
    	}
    	 
    	$sql=" SELECT
    	(SELECT $coloum FROM `rms_product` AS ide WHERE ide.id = td.pro_id LIMIT 1) AS product_name,
    	td.*
    	FROM `rms_received_note_detail` AS td
    	WHERE
    	td.receive_id=$id ";
    	return $db->fetchAll($sql);
    }
    
    function updateReceiveNoteTransfer($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$receive_id = $_data['id'];
    		//Reverse data to old
    		$odlrow = $this->getReceiveNoteById($receive_id);
    		
    		$arrupd = array(
    				'is_received' 	=>0,
    				'modify_date'=>date("Y-m-d H:i:s"),
    				'received_by'=>"",
    		);
    		$this->_name ="rms_transferstock";
    		$where = "id = ".$odlrow['transfer_id'];
    		$this->update($arrupd, $where);
    		$oldTransfer = $odlrow['transfer_id'];
    		$olddetail = $this->getReceiveNoteDetailById($receive_id);
    		
    		if (!empty($olddetail)){
    			$transferInfo = $this->getTransferInfo($oldTransfer);
    			
    			foreach ($olddetail as $rsd){
    				$qty_stock = $this->getProductLocation($rsd['pro_id'],$transferInfo['from_location']);
    				$this->_name="rms_product_location";
    				if(!empty($qty_stock)){
    					$qty = $qty_stock['pro_qty'] + $rsd['qty'];
    					$array = array(
    							'pro_qty'=>$qty,
    					);
    					$where = " id = ".$qty_stock['id'];
    					$this->update($array, $where);
    				}
    				$qty_stock = $this->getProductLocation($rsd['pro_id'],$transferInfo['to_location']);
    				
    				if(!empty($qty_stock)){
    					$qty = $qty_stock['pro_qty'] - $rsd['qty'];
    					$array = array(
    							'pro_qty'=>$qty,
    					);
    					$where = " id = ".$qty_stock['id'];
    					$this->update($array, $where);
    				}
    			}
    			
    		}
    		
    		
    		$_arr = array(
    				'branch_id' 	=>$_data['branch_id'],
    				'transfer_id' 	=>$_data['transfer_id'],
    				'receive_date'	=>$_data['receive_date'],
    				'note'=>$_data['note'],
    				'create_date'=>date("Y-m-d H:i:s"),
    				'modify_date'=>date("Y-m-d H:i:s"),
    				'user_id'=>$this->getUserId(),
    				'status'=>$_data['status'],
    		);
    		
    		$this->_name ="rms_received_note";
    		$where = "id = ".$receive_id;
    		$this->update($_arr, $where);
    
    		if ($_data['status']==0){
    			return false;
    		}
    		
    		$this->_name='rms_received_note_detail';
    		$where = "receive_id = ".$receive_id;
    		$this->delete($where);
    		
    		$arrupd = array(
    				'is_received' 	=>1,
    				'modify_date'=>date("Y-m-d H:i:s"),
    				'received_by'=>$this->getUserId(),
    		);
    		$this->_name ="rms_transferstock";
    		$where = "id = ".$_data['transfer_id'];
    		$this->update($arrupd, $where);
    		
    		$ids = explode(',', $_data['identity']);
    		if (!empty($ids)){
    			foreach ($ids as $i){
    				$_arr = array(
    						'receive_id'=> $receive_id,
    						'pro_id'	=> $_data['pro_id_'.$i],
    						'qty'		=> $_data['qty_'.$i],
    						'note'		=> $_data['remark_'.$i],
    				);
    				$this->_name='rms_received_note_detail';
    				$this->insert($_arr);
    					
    				$rs = $this->checkisProductSet($_data['pro_id_'.$i]);
    				if(empty($rs)){//normal product
    					$qty_stock = $this->getProductLocation($_data['pro_id_'.$i],$_data['f_branch']);
    					$this->_name="rms_product_location";
    					if(!empty($qty_stock)){
    						$qty = $qty_stock['pro_qty'] - $_data['qty_'.$i];
    						$array = array(
    								'pro_qty'=>$qty,
    						);
    						$where = " id = ".$qty_stock['id'];
    						$this->update($array, $where);
    					}
    					//to location
    					$qty_stock = $this->getProductLocation($_data['pro_id_'.$i],$_data['branch']);
    					if(!empty($qty_stock)){
    						$qty = $qty_stock['pro_qty'] + $_data['qty_'.$i];
    						$array = array(
    								'pro_qty'=>$qty,
    						);
    						$where = " id = ".$qty_stock['id'];
    						$this->update($array, $where);
    					}else{
    						$data = array(
    								'pro_id'=>$_data['pro_id_'.$i],
    								'brand_id'=>$_data['branch'],
    								'pro_qty'=>$_data['qty_'.$i],
    								'note'=>'ពីផ្ទេរទំនិញចូល',
    								'date' =>date("Y-m-d"),
    								'user_id'=>$this->getUserId(),
    								'status'=>1);
    						$this->insert($data);
    					}
    				}//prodcut set
    				else{
    					$rsset = $this->getProductSet($_data['pro_id_'.$i]);
    					if(!empty($rsset)){
    						foreach($rsset as $rs){
    							$qty_stock = $this->getProductLocation($rs['subpro_id'],$_data['f_branch']);
    							$this->_name="rms_product_location";
    							if(!empty($qty_stock)){
    								$qty = $qty_stock['pro_qty'] - $_data['qty_'.$i];//ត្រូវគុណចំនួនត្រូវផ្ទេរជាមួយនឹងបរិមាណទំនិញក្នុងមួយ setទើបត្រូវ
    								$array = array(
    										'pro_qty'=>$qty,
    								);
    								$where = " id = ".$qty_stock['id'];
    								$this->update($array, $where);
    							}
    							//to location
    							$qty_stock = $this->getProductLocation($rs['subpro_id'],$_data['branch']);
    							if(!empty($qty_stock)){
    								$qty = $qty_stock['pro_qty'] + $_data['qty_'.$i];
    								$array = array(
    										'pro_qty'=>$qty,
    								);
    								$where = " id = ".$qty_stock['id'];
    								$this->update($array, $where);
    							}else{
    								$data = array(
    										'pro_id'=>$_data['pro_id_'.$i],
    										'brand_id'=>$_data['branch'],
    										'pro_qty'=>$_data['qty_'.$i],
    										'note'=>'ពីផ្ទេរទំនិញចូល',
    										'date' =>date("Y-m-d"),
    										'user_id'=>$this->getUserId(),
    										'status'=>1
    								);
    								$this->insert($data);
    							}
    						}
    					}
    				}
    			}
    		}
    
    		$db->commit();
    		return true;
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		return false;
    	}
    }
}