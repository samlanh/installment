<?php

class Invest_Model_DbTable_DbWithdrawBroker extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_investment';
    public function getUserId(){
    	$db = new Application_Model_DbTable_DbGlobal();
    	return $db->getUserId();
    }
    
    function getAllInvestmentReceipt($search = null){
    	try{
    		$db = $this->getAdapter();
    		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    		$label1 = $tr->translate("NORMAL_WITHDRAW");
    		$label2 = $tr->translate("PRINCIPAL_WITHDRAW");
    		$label3 = $tr->translate("PAYOFF_BROKER_WITHDRAW");
    		
    		$from_date =(empty($search['start_date']))? '1': "w.paid_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "w.paid_date <= '".$search['end_date']." 23:59:59'";
    		$where = " AND  ".$from_date." AND ".$to_date;
    		$sql = "
    		SELECT 
				w.id,w.receipt_no,
				v.name,
				(SELECT i.invest_no FROM `rms_investment` AS i WHERE i.id = w.investment_id LIMIT 1) AS invest_no,
				w.interest_paid,
				w.total_payment,
				w.recieve_amount,
				w.payment_date,
				w.paid_date,
				CASE
					WHEN  w.`option_pay` = 1 THEN '$label1'
					WHEN  w.`option_pay` = 2 THEN '$label2'
					WHEN  w.`option_pay` = 3 THEN '$label3'
				END AS option_pay,
				(SELECT  first_name FROM rms_users WHERE id=w.user_id LIMIT 1 ) AS user_name
    		";
    		 
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$sql.=$dbp->caseStatusShowImage("w.status");
    		$sql.="  FROM `rms_investor_withdraw_broker` AS w,	`rms_broker` AS v WHERE v.id = w.broker_id ";
    		 
    		if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
    		$s_where[] = " w.receipt_no LIKE '%{$s_search}%'";
    		$s_where[] = " w.principle_paid LIKE '%{$s_search}%'";
    		$s_where[] = " w.interest_paid LIKE '%{$s_search}%'";
    		$s_where[] = " w.recieve_amount LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT i.invest_no FROM `rms_investment` AS i WHERE i.id = w.investment_id LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " w.receipt_no LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    		}
    		if($search['status']>-1){
    			$where.= " AND w.status = ".$search['status'];
    		}
	    	if(!empty($search['broker_search'])){
	    		$where.=" AND w.broker_id = ".$search['broker_search'];
	    	}
	    	$where.=$dbp->getAccessPermission("w.branch_id");
	    	
    		$order=" ORDER BY w.id DESC ";
    		return $db->fetchAll($sql.$where.$order);
    		 
    		}catch (Exception $e){
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    function getAllBroker($is_complete=0){
    	$db = $this->getAdapter();
    	$sql ="SELECT i.id,
			CONCAT(i.invest_no,' ',iv.name) AS `name`
			FROM `rms_investment` AS i,
			`rms_broker` AS iv
			WHERE 
			i.broker_id = iv.id
			AND i.status=1 
    	";
    	if ($is_complete==0){
    		$sql.=" AND i.is_broker_completed=0 ";
    	}
    	 
    	return $db->fetchAll($sql);
    }
    public function getReceiptNO(){
    	$this->_name='rms_investor_withdraw_broker';
    	$db = $this->getAdapter();
    	$sql=" SELECT count(id)  FROM $this->_name WHERE 1 LIMIT 1 ";
    	$acc_no = $db->fetchOne($sql);
    	 
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	$prefix='№';
    	$pre= "";
    	for($i = $acc_no;$i<6;$i++){
    		$pre.='0';
    	}
    	return $prefix.$pre.$new_acc_no;
    }
    
    public function addInvestmentWithdrawal($_data){
    	try{
    		$receiptNO = $this->getReceiptNO();
    		
    		$option_pay = $_data["option_pay"];
    		$investment_id = $_data['investment_id'];
    		$amount_receive = $_data["recieve_amount"];
    		$total_payment = $_data["total_payment"];
    		$is_compleated=0;
    		if($amount_receive>=$total_payment){
    			$is_compleated = 1;
    		}
    		 
    		$pay_off = 0;
    		if($_data["option_pay"]==3){//payoff
    			$pay_off = 1;
    		}
    		$_arr=array(
    				'receipt_no'	  		=> $receiptNO,
    				'investment_id'	  		=> $investment_id,
    				'broker_id'	  		=> $_data['broker_id'],
    				'paid_date'	      		=> $_data['paid_date'],
    				'option_pay'	      	=> $option_pay,
    				'payment_method'  		=> $_data['payment_method'],
    				'cheque_no'	  			=> $_data['cheque_no'],
    				'payment_date'	    	=> $_data['payment_date'],
    				'interest_paid'			=>$_data['interest_paid'],
    				'total_payment' 		=> $_data['total_payment'],
    				'recieve_amount' 		=> $_data['recieve_amount'],
    				'remain' 				=> $_data['remain'],
    				'times' 				=> $_data['times'],
    				'note' 					=> $_data['note'],
    				'is_payoff' 			=> $pay_off,
    				'is_completed' 			=> $is_compleated,
    				'status' 				=> 1,
    				'user_id'	  			=> $this->getUserId(),
    				'create_date' 			=> date("Y-m-d H:i:s"),
    				'modify_date' 			=> date("Y-m-d H:i:s"),
    		);
    		$this->_name='rms_investor_withdraw_broker';
    		$receipt_id = $this->insert($_arr);
    		
    		
    		$total_interest = $_data["interest_paid"];
    		$rows = $this->getBrokerScheduleById($investment_id, 1);
    		if(!empty($rows)){
    			$AllrecordSchedule = count($rows);
    			$remain_money = $_data['recieve_amount'];
    			foreach ($rows AS $key => $row){
    				if($remain_money<=0){
    					break;
    				}
    				if($_data['option_pay']!=2){
    					$arr_money_detail = array(
    							'withdraw_id'				=>	$receipt_id,
    							'lfd_id'					=>	$row["id"],//ok
    							'investment_id'				=>	$row["investment_id"],//ok
    							'date'						=>	$row["date"], // ថ្ងៃដែលត្រូវបង់
    							'beginning_balance'			=>	$row["beginning_balance"],
    							'beginning_balanceafter'	=>	$row["beginning_balanceafter"],
    							'ending_balance'			=>	$row["ending_balance"],
    							'percent_return'			=>	$row["percent_return"],
    							'principle'					=>	$row["principle"],
    							'principle_after'			=>	$row["principle_after"],
    							'interest_amount'			=>	$row["interest_amount"],
    							'interest_amountafter'		=>	$row["interest_amountafter"],
    							'total_payment'				=>	$row["total_payment"],
    							'total_paymentafter'		=>  $row["total_paymentafter"],
    							'time'						=>	$row["time"],
    							'is_complete'				=>	1,
    							'type_record'				=>	$row["type_record"],
    							'payment_option'			=>	$option_pay,
    							'date_receive'				=>	$_data['paid_date'],
    							'note'						=>	$row['note'],
    							'status'					=>	$row['status'],
    							);
    					$this->_name='rms_investor_withdraw_broker_detail';
    					$this->insert($arr_money_detail);
    				}
    				
    				$after_payment_after = $row['total_paymentafter'];
    				
    				$after_interest = $row['interest_amountafter'];
    				
    				if($option_pay==1){
    					$total_interest = $after_interest;
    				}
    				$date_payment = $row['date'];
    				
    				$paid_interest = 0;
    				$is_compleated_d=0;
    				
    				if($key!=0){
    					if($option_pay==3 OR $option_pay==2 ){
    						$total_interest=0;
    					}
    				}
    				
    				if($remain_money>=0){
    					$remain_money = round($remain_money - $total_interest,2);
    					if($remain_money>=0){
    						$paid_interest = $total_interest;
    						$after_interest = 0;
    						$is_compleated_d=1;
    				    	
    					}else{
    							$paid_interest = $total_interest-abs($remain_money);
    							$after_interest =abs($remain_money);
    							$is_compleated_d=0;
    					}
    				}
    				
    				
    				if($option_pay!=2){//ព្រោះបញ្ចូលជា Extra payment hz
    					$arra = array(
    							'interest_amountafter'=>$after_interest,
    							'total_paymentafter'=>	$after_interest,
    							'is_complete'=>$is_compleated_d,
    							'payment_option'	=>	$option_pay,
    							'date_receive'=> ($is_compleated_d==1)?$_data['paid_date']:0
    					);
    					$where = " id = ".$row['id'];
    					$this->_name="rms_investment_detail_broker";
    					$this->update($arra, $where);
    				}
    				
    				$paid_interestall = $paid_interestall+$paid_interest;
    				
    				if ($is_compleated_d==1){
    					if ($AllrecordSchedule==$key+1){
    						$arr_invest = array(
    								'is_broker_completed'=>1,
    						);
    						$where_invest = " id = ".$row['investment_id'];
    						$this->_name="rms_investment";
    						$this->update($arr_invest, $where_invest);
    					}
    				}
    			}//end foreach 
    		}
    		
    		$rows = $this->getBrokerScheduleById($investment_id, 1);
    		if(empty($rows)){
    			$this->updatePayoff($investment_id,$receipt_id);
    		}
    		return $receipt_id;
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function updatePayoff($investment_id,$receipt_id){
    	$this->_name='rms_investor_withdraw_broker';
    	$where = " id =".$receipt_id;
    	$data= array(
    			"is_payoff"=>1
    	);
    	$this->update($data, $where);
    }
    function getBrokerScheduleById($investment_id,$orderby=1){
    	$db = $this->getAdapter();
    	$sql="SELECT * FROM  rms_investment_detail_broker WHERE status=1 AND is_complete=0 AND investment_id=$investment_id ";
    	if($orderby==1){
    		$sql.=" ORDER BY id ASC ";
    	}else{
    		$sql.=" ORDER BY id DESC ";
    	}
    	return $db->fetchAll($sql);
    }
    
    function getBrokerPaymentScheduleByID($data){//tab1
    	$db = $this->getAdapter();
    	$sql ="SELECT
	    	s.*,
	    	s.date AS sold_date,
	    	DATE_FORMAT(s.date, '%d-%m-%Y') AS `buy_date`,
	    	(SELECT iv.phone FROM `rms_broker` AS iv WHERE iv.id=s.broker_id LIMIT 1) AS phone,
	    	(SELECT iv.name FROM `rms_broker` AS iv WHERE iv.id=s.broker_id LIMIT 1) AS investor_name,
	    	(SELECT crm.`paid_date` FROM `rms_investor_withdraw` AS crm WHERE crm.investment_id=s.id ORDER BY crm.id DESC LIMIT 1) AS paid_date,
	    	(SELECT SUM(crm.principle_paid+crm.interest_paid) FROM `rms_investor_withdraw` AS crm WHERE crm.investment_id = s.id AND crm.status=1 LIMIT 1) AS total_principal_permonthpaid,
	    	ss.*
    	FROM
	    	`rms_investment` AS s,
	    	`rms_investment_detail_broker` AS ss
    	WHERE s.id = ss.`investment_id`
	    	AND s.status = 1
	    	AND ss.is_complete = 0
	    	AND s.id = ".$data['investment_id']." ORDER BY ss.id ASC ";
    	return $db->fetchAll($sql);
    }
    
    
    function getAllBrokerPaymentScheduleById($data){//tab2
    	$db = $this->getAdapter();
    	$investment_id= $data['investment_id'];
    	$sql = "SELECT *,
    		DATE_FORMAT(scd.date, '%d-%m-%Y') AS `date_payment`
    	FROM
	    	rms_investment as s ,
	    	rms_investment_detail_broker as scd
    	WHERE
	    	scd.status=1
	    	AND s.id=scd.investment_id
	    	AND s.id = $investment_id
	    	ORDER BY scd.time ASC ";
    	return $db->fetchAll($sql);
    }
    
    	public function getBrokerHasPayByID($investment_id){//tab3
	    	$db= $this->getAdapter();
	    	$sql="
	    	SELECT
		    	crm.*,
		    	DATE_FORMAT(crm.paid_date, '%d-%m-%Y') AS `date_input`,
		    	crm.principle_paid AS total_principal_permonthpaid
	    	FROM
	    		`rms_investor_withdraw_broker` AS crm
	    	WHERE
		    	crm.status=1
		    	AND crm.`investment_id` = $investment_id ORDER BY crm.`id` DESC ";
	    	return $db->fetchAll($sql);
    	}
    	
    	function getReceiptByID($receipt_id){
    		$db = $this->getAdapter();
    		$sql="SELECT * FROM rms_investor_withdraw_broker WHERE id =$receipt_id ";
    		
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$sql.=$dbp->getAccessPermission("branch_id");
    		$sql.=" LIMIT 1 ";
    		
    		return $db->fetchRow($sql);
    	}
    	function checkifExistingDelete($id){
    		$db = $this->getAdapter();
    		$sql="SELECT recieve_amount FROM `rms_investor_withdraw_broker` WHERE id=$id AND recieve_amount>0";
    		return $db->fetchOne($sql);
    	}
    	function deleteReceipt($receipt_id){
    		$db = $this->getAdapter();
    		$db->beginTransaction();
    		try{
    			$rsreceipt = $this->getReceiptByID($receipt_id);
    			$investment_id = $rsreceipt['investment_id'];
    			 
    			$user_id = $this->getUserId();
    			 
    			$arr_client_pay = array(
    					'principle_paid'		=>	0,
    					'interest_paid'			=>	0,
    					'total_payment'			=>	0,
    					'recieve_amount'		=>	0,
    					'remain'				=>	0,
    						
    					'note'					=>	$rsreceipt['note']." លុប ",
    					'user_id'				=>	$user_id,
    					'status'				=>	1,
    			);
    			$this->_name = "rms_investor_withdraw_broker";
    			$where="id =".$receipt_id;
    			$this->update($arr_client_pay, $where);
    			 
    			$arr = array(
    					'is_broker_completed'=>0
    			);
    			$this->_name="rms_investment";
    			$where="id=".$investment_id;
    			$this->update($arr, $where);
    	
    			$sql = "SELECT
    			crmd.*,
    			crm.branch_id
    			 
    			FROM
    			`rms_investor_withdraw_broker_detail` AS crmd,
    			rms_investor_withdraw_broker AS crm
    			WHERE
    			crm.id = crmd.`withdraw_id`
    			and crmd.`withdraw_id` = $receipt_id ";
    			
    			$receipt_money_detail = $db->fetchAll($sql);
    			 
    			$branc_id=1;
    			 
    			if(!empty($receipt_money_detail)){
    				foreach ($receipt_money_detail as $rs){
    					if(!empty($rs['lfd_id'])){
    						$arra = array(
    								'beginning_balance' 	    => $rs['beginning_balance'],
    								'beginning_balanceafter' 	=> $rs['beginning_balanceafter'],
    								'ending_balance'         	=> $rs['ending_balance'],
    								"principle"	 				=> $rs['principle'],
    								"principle_after"			=> $rs['principle_after'],
    								'interest_amount'   		=> $rs['interest_amount'],
    								'interest_amountafter'   	=> $rs['interest_amountafter'],
    								'total_payment'    			=> $rs['total_payment'],
    								'total_paymentafter'    	=> $rs['total_paymentafter'],
    								'is_complete'           	=> 0,
    						);
    						$where ="id=".$rs['lfd_id'];
    						$this->_name="rms_investment_detail_broker";
    						$updated = $this->update($arra, $where);
    							
    						if($updated==0){//ករណីមានលុបខ្លះ ត្រូវបញ្ចូលឡើងវិញ
    							$sql="SELECT (time) FROM rms_investment_detail_broker WHERE status=1 AND investment_id=$investment_id ORDER BY time DESC ";
    							$start_id = $db->fetchOne($sql);
    							 
    							$this->_name="rms_investment_detail_broker";
    							$datapayment = array(
    									'investment_id'				=>$investment_id,
    									'date'						=>$rs['date'],
    									'beginning_balance'			=>$rs['beginning_balance'],
    									'beginning_balanceafter'	=>$rs['beginning_balanceafter'],
    									'ending_balance'			=>$rs['ending_balance'],
    									'principle'					=>$rs['principle'],
    									'principle_after'			=>$rs['principle_after'],
    									'interest_amount'			=>$rs['interest_amount'],
    									'interest_amountafter'		=>$rs['interest_amountafter'],
    									'total_payment'				=>$rs['total_payment'],
    									'total_paymentafter'		=>$rs['total_paymentafter'],
    									'is_completed'=>0,
    									'note'=>'',
    									'status'=>1,
    									'is_complete'=>0,
    									'time'=>$start_id+1,
    							);
    							$this->insert($datapayment);
    						}
    					}
    				}
    			}
    			
    			$arr_money_detail = array(
    					'beginning_balance'			=>	0,
    					'beginning_balanceafter'	=>	0,
    					'ending_balance'			=>	0,
    					'percent_return'			=>	0,
    					'principle'					=>	0,
    					'principle_after'			=>	0,
    					'interest_amount'			=>	0,
    					'interest_amountafter'		=>	0,
    					'total_payment'				=>	0,
    					'total_paymentafter'		=>  0,
    					'is_complete'				=>	0,
    					'status'					=>	1
    			);
    			
    			$where = " withdraw_id = ".$receipt_id;
    			$this->_name="rms_investor_withdraw_broker_detail";
    			$this->update($arr_money_detail, $where);
    			
    			$db->commit();
    		}catch(Exception $e){
    			$db->rollBack();
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
}