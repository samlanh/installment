<?php

class Stock_Model_DbTable_DbRequestProduct extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_request_order';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    	 
    }
    function getAllRequest($search=null){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql="SELECT 
    			id,
    			(SELECT project_name FROM ln_project WHERE br_id= branch_id LIMIT 1) AS branch_name,
    			request_no,
    			(SELECT title from rms_request_for as rf where rf.id = request_for LIMIT 1) as request_for,
    			(SELECT title from rms_for_section as fs where fs.id = for_section  LIMIT 1) as for_section,
    			purpose,
    			request_date,
			    (SELECT SUM(rd.qty_request) FROM rms_request_orderdetail AS rd WHERE rd.request_id=rms_request_order.id  LIMIT 1) AS total_qty,
			    (SELECT first_name FROM rms_users WHERE id=rms_request_order.user_id LIMIT 1) AS user_name
		";
    	$sql.=$dbp->caseStatusShowImage("status");
    	$sql.=" FROM rms_request_order  WHERE 1 ";
    	
	    	$where="";
	    	$from_date =(empty($search['start_date']))? '1': " request_date >= '".$search['start_date']." 00:00:00'";
	    	$to_date = (empty($search['end_date']))? '1': " request_date <= '".$search['end_date']." 23:59:59'";
	    	$where = " AND ".$from_date." AND ".$to_date;
	    	if(!empty($search['title'])){
	    		$s_where=array();
	    		$s_search = str_replace(' ', '', addslashes(trim($search['title'])));
	    		$s_where[]= " REPLACE(request_no,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(request_name,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(purpose,' ','') LIKE '%{$s_search}%'";
	    		$where.=' AND ('.implode(' OR ', $s_where).')';
	    	}
	    	if($search['status_search']==1 OR $search['status_search']==0){
	    		$where.=" AND status=".$search['status_search'];
	    	}
	    	if($search['request_for']>0){
	    		$where.=" AND request_for=".$search['request_for'];
	    	}
	    	if($search['for_section']>0){
	    		$where.=" AND for_section=".$search['for_section'];
	    	}
	    	if(!empty($search['branch_id'])){
	    		$where.=" AND branch_id=".$search['branch_id'];
	    	}
	    	
	    	
	    	$sql.=$dbp->getAccessPermission('branch_id');
	    	$order=" ORDER BY id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }

    public function getProQtyByLocation($branch_id,$pro_id){
    	$db=$this->getAdapter();
    	$sql=" SELECT 
    				pl.id,
    				pl.pro_id,
    				pl.pro_qty 
    			FROM 
    				rms_product_location AS pl,
    				rms_product AS p
				WHERE 
					pl.pro_id=$pro_id 
					AND pl.brand_id=$branch_id
				 	AND p.id=pl.pro_id 
    			LIMIT 1	
    		";
    	$row = $db->fetchRow($sql);
    	if(empty($row)){
    		return $row = $db->fetchRow($sql);
    	}else{
    		return $row;
    	}
    }
    public function addRequest($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$_pur = new Stock_Model_DbTable_DbRequestProduct();
			$receipt = $this->getRequestCode($data["branch"]);
			$arr=array(
					"request_no"    => 	$receipt,
					"request_for"   => 	$data["request_for"],
					"for_section"   => 	$data["for_section"],
					"purpose"     	=> 	$data["purpose"],
					"branch_id"     => 	$data["branch"],
					"request_date"  => 	$data['request_date'],
					"create_date"   => 	date("Y-m-d H:i:s"),
					"user_id"       => 	$this->getUserId(),
					"status"        => 	1,
			);
			$this->_name="rms_request_order";
			$request_id = $this->insert($arr); 
			unset($arr);

			if($data['identity']!=""){
				$ids=explode(',',$data['identity']);
				foreach ($ids as $i)
				{
					$data_item= array(
							'request_id'	=>  $request_id,
							'branch_id'		=> 	$data['branch'],
							'pro_id'		=>  $data['product_name_'.$i],
							'qty_curr'		=> 	$data['curr_qty'.$i],
							'qty_request'	=>  $data['request_qty'.$i],
							'qty_receive'	=>  $data['receive_qty'.$i],
							'price'			=> 	$data['cost_'.$i],
							'pro_type'		=>  2,
							'create_date'	=>  date("Y-m-d H:i:s"),
							'remark'  		=> 	$data['note_'.$i],
							'user_id'		=> 	$this->getUserId(),
							'status'		=> 	1,
					);
					$this->_name='rms_request_orderdetail';
					$this->insert($data_item);
					$rows=$this->getProQtyByLocation($data['branch'], $data['product_name_'.$i]); 
					if($rows){
						$datatostock= array(
							'pro_qty' 	=> $rows["pro_qty"]-$data['receive_qty'.$i],
							'date'		=> date("Y-m-d H:i:s"),
							'user_id'	=> $this->getUserId()
						);
						$this->_name="rms_product_location";
						$where=" id = ".$rows['id'];
						$this->update($datatostock, $where);
					}
				 }
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
			Application_Form_FrmMessage::message('INSERT_FAIL');
		}
	}
 	function updateRequest($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$rsold = $this->getRequestById($data['id']);
			if($rsold['status']==1){//if befor status = active
				$rows=$this->getRequestDetail($data['id']);
				if(!empty($rows)){
					foreach ($rows as $row){
						$qty=$this->getProQtyByLocation($row['branch_id'], $row['pro_id']); 
						if($qty){
							$datat= array(
								'pro_qty' 	=> $qty["pro_qty"]+$row['qty_receive'],
								'date'		=> date("Y-m-d H:i:s"),
								'user_id'	=> $this->getUserId()
							);
							$this->_name="rms_product_location";
							$where=" id = ".$qty['id'];
							$this->update($datat, $where);
						}
					}
				}
			}
			$arr=array(
					"request_no"    => 	$data["request_no"],
					"request_for"   => 	$data["request_for"],
					"for_section"   => 	$data["for_section"],
					"purpose"     	=> 	$data["purpose"],
					"branch_id"     => 	$data["branch"],
					"request_date"  => 	date("Y-m-d",strtotime($data['request_date'])),
					"create_date"   => 	date("Y-m-d H:i:s"),
					"user_id"       => 	$this->getUserId(),
					"status"        => 	$data['status'],
			);
			$this->_name="rms_request_order";
			$where="id=".$data['id'];
			$this->update($arr, $where);
			unset($arr);
			$this->_name='rms_request_orderdetail';
			$where=" request_id=".$data['id'];
			$this->delete($where);
			
			if($data['identity']!=""){
				$ids=explode(',',$data['identity']);
				foreach ($ids as $i)
				{
					$data_item= array(
							'request_id'	=>  $data['id'],
							'branch_id'		=> 	$data['branch_id_'.$i],
							'pro_id'		=>  $data['product_name_'.$i],
							'qty_curr'		=> 	$data['curr_qty'.$i],
							'qty_request'	=>  $data['request_qty'.$i],
							'qty_receive'	=>  $data['receive_qty'.$i],
							'price'			=> 	$data['cost_'.$i],
							'pro_type'		=>  2,
							'create_date'	=>  date("Y-m-d H:i:s"),
							'remark'  		=> 	$data['note_'.$i],
							'user_id'		=> 	$this->getUserId(),
							'status'		=> 	$data['status'],
					);
					$this->_name='rms_request_orderdetail';
					$this->insert($data_item);
					
					if($data['status']==1){
						$rows=$this->getProQtyByLocation($data['branch_id_'.$i], $data['product_name_'.$i]); 
						if(!empty($rows)){
							$datatostock= array(
									'pro_qty' 	=> $rows["pro_qty"]-$data['receive_qty'.$i],
									'date'		=> date("Y-m-d H:i:s"),
									'user_id'	=> $this->getUserId()
							);
							$this->_name="rms_product_location";
							$where=" id = ".$rows['id'];
							$this->update($datatostock, $where);
						}
					}
				 }
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
			Application_Form_FrmMessage::message('INSERT_FAIL');
		}
	}
	function getRequestById($id){
		$db=$this->getAdapter();
		$sql="SELECT * FROM rms_request_order WHERE id=$id";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('branch_id');
		return $db->fetchRow($sql);
	}
	
	function getRequestDetail($id){
		$db=$this->getAdapter();
		$sql="SELECT *,branch_id,
		(SELECT ide.title FROM `rms_product` AS ide WHERE ide.items_type=3 AND ide.id = pro_id LIMIT 1) AS pro_name,
			    pro_id,qty_curr,qty_request,remark FROM rms_request_orderdetail 
				WHERE request_id=$id";
		return $db->fetchAll($sql);
	}

    function getRequestCode($branch_id=null){
    	$db = $this->getAdapter();
    	$sql="SELECT COUNT(id) FROM rms_request_order WHERE STATUS=1 ";
    	$pre="";
    	if (!empty($branch_id)){
    		$sql.=" AND branch_id=".$branch_id;
    		$_dbgb = new Application_Model_DbTable_DbGlobal();
    		$pre.= $_dbgb->getPrefixCode($branch_id);//by branch
    	}
    	$sql.=" ORDER BY id DESC LIMIT 1";
    	$acc_no = $db->fetchOne($sql);
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	$pre.='RQ-';
    	for($i = $acc_no;$i<4;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
    
    function getProductQty($location,$pro_id){
    	$db=$this->getAdapter();
    	$sql="SELECT 
    				pl.pro_qty,
    				p.cost
    			FROM 
    				rms_product AS p,
    				rms_product_location AS pl
		  		WHERE 
		  			p.id=pl.pro_id
				  	AND pl.pro_id=$pro_id 
				  	AND pl.brand_id=$location
    		";
    	return $db->fetchRow($sql);
    }
    
    function getAllProductBybranch($branch_id){
    	$db = $this->getAdapter();
    	$_db  = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
    	if($lang==1){// khmer
    		$grade = "p.title";
    	}else{ // English
    		$grade = "p.title_en";
    	}
    	$sql = "SELECT 
				  p.id,
				  pl.brand_id,
				  $grade AS `name` 
				FROM
				  `rms_product` AS p,
				  rms_product_location AS pl 
				WHERE 
				  p.id = pl.pro_id 
				  AND p.status = 1 
				  AND pl.brand_id = $branch_id
    		";
    	$order=' ORDER BY p.id DESC';
    	return $db->fetchAll($sql.$order);
    }
    
    function getAllRequestFor(){
    	$db=$this->getAdapter();
    	$sql="SELECT
			    	id,
			    	title as name
    			FROM
			    	rms_request_for AS rf
    			WHERE
    				status=1
    				and title != '' ";
    	return $db->fetchAll($sql);
    }
    
    function getAllForSection(){
    	$db=$this->getAdapter();
    	$sql="SELECT
			    	id,
			    	title as name
    			FROM
    				rms_for_section AS fs
    			WHERE
			    	status=1
			    	and title != '' ";
    	return $db->fetchAll($sql);
    }
    function addNewRequestFor($data){
    	$this->_name="rms_request_for";
    	$arr = array(
    			'title'=>$data['title'],
    			'create_date'=>date("Y-m-d"),
    			'user_id'=>$this->getUserId(),
    		);
    	return $this->insert($arr);
    }
    
    function addNewForSection($data){
    	$this->_name="rms_for_section";
    	$arr = array(
    			'title'=>$data['title_for_section'],
    			'create_date'=>date("Y-m-d"),
    			'user_id'=>$this->getUserId(),
    	);
    	return $this->insert($arr);
    }
}