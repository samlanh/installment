<?php

class Invest_Model_DbTable_DbInvestment extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_investment';
    public function getUserId(){
    	$db = new Application_Model_DbTable_DbGlobal();
    	return $db->getUserId();
    }
    
    function getAllInvestment($search = null){
    	try{
    		$db = $this->getAdapter();
    		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    		$labelyear = $tr->translate("YEAR");
    		$from_date =(empty($search['start_date']))? '1': "i.date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "i.date <= '".$search['end_date']." 23:59:59'";
    		$where = " AND  ".$from_date." AND ".$to_date;
    		$sql = "
    		SELECT i.id,i.invest_no,
				(SELECT iv.name FROM `rms_investor` AS iv WHERE iv.id=i.investor_id LIMIT 1 ) investor,
				i.date,
				CONCAT(i.amount,' ','($)'),
				CONCAT(i.duration,' ','$labelyear'),
				(SELECT b.name FROM `rms_broker` AS b WHERE b.id=i.broker_id LIMIT 1 ) broker
				 ";
    		 
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$sql.=$dbp->caseStatusShowImage("i.status");
    		$sql.="  FROM `rms_investment` AS i WHERE 1 ";
    		 
    		if(!empty($search['adv_search'])){
    			$s_where = array();
    			$s_search = addslashes(trim($search['adv_search']));
    			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
    			$s_where[] = " i.invest_no LIKE '%{$s_search}%'";
    			$s_where[] = " i.amount LIKE '%{$s_search}%'";
    			$s_where[] = " i.duration LIKE '%{$s_search}%'";
    			$where .=' AND ('.implode(' OR ',$s_where).')';
    		}
    		if($search['status']>-1){
    			$where.= " AND i.status = ".$search['status'];
    		}
    		if(!empty($search['investor_id'])){
    			$where.=" AND i.investor_id = ".$search['investor_id'];
    		}
    		if(!empty($search['broker_id'])){
    			$where.=" AND i.broker_id = ".$search['broker_id'];
    		}
    		$order=" ORDER BY i.id DESC ";
    		return $db->fetchAll($sql.$where.$order);
    		 
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    
    public function getInvestmentNO(){
    	$this->_name='rms_investment';
    	$db = $this->getAdapter();
    	$sql=" SELECT count(id)  FROM $this->_name WHERE 1 LIMIT 1 ";
    	$acc_no = $db->fetchOne($sql);
    	 
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	$prefix="";
    	$pre= "";
    	for($i = $acc_no;$i<6;$i++){
    		$pre.='0';
    	}
    	return $prefix.$pre.$new_acc_no;
    }
    
    public function addInvestment($_data){
    	try{
    		$investmenNo = $this->getInvestmentNO();
    		$_arr=array(
    				'invest_no'	  		=> $investmenNo,
    				'investor_id'	  	=> $_data['investor_id'],
    				'date'	      		=> $_data['date'],
    				'amount'	      	=> $_data['amount'],
    				'duration'  		=> $_data['duration'],
    				'note'	  			=> $_data['note'],
    				'broker_id'	    	=> $_data['broker_id'],
    				'broker_percent'	=>$_data['broker_percent'],
    				'broker_amount'		=>$_data['broker_amount'],
    				'broker_duration' 	=> $_data['broker_duration'],
    				'status' 			=> 1,
    				'user_id'	  		=> $this->getUserId(),
    				'create_date' 		=> date("Y-m-d H:i:s"),
    				'modify_date' 		=> date("Y-m-d H:i:s"),
    		);
    		$invest_id = $this->insert($_arr);
    		
    		if(!empty($_data['identity'])){
    			$ids = explode(',', $_data['identity']);
    			$countrecord = count($ids);
    			$time = 1;
    			foreach ($ids as $i){
    				$old_beginning_balance=$_data['amount'];
    				$end_balance=$_data['amount'];
    				$principle=0;
    				$_paid_perrecord=$_data['total_payment'.$i];
    				$_totalpaid_perrecord=$_data['total_payment'.$i];
    				if ($time == $countrecord){
    					$old_beginning_balance=$_data['amount'];
    					$end_balance=0;
    					$principle=$_data['amount'];
    					$_totalpaid_perrecord= $principle+$_data['total_payment'.$i];
    				}
    				
    				$arr_invest_detail=array(
    						'investment_id'	  		=> $invest_id,
    						'date'	  				=> $_data['date_payment'.$i],
    						'beginning_balance'	  	=> $old_beginning_balance,
    						'beginning_balanceafter'=> $old_beginning_balance,
    						'ending_balance'	  	=> $end_balance,
    						'percent_return'	  	=> $_data['percent'.$i],
    						'principle'	  			=> $principle,
    						'principle_after'	  	=> $principle,
    						'interest_amount'	  	=> $_paid_perrecord,
    						'interest_amountafter'	=> $_paid_perrecord,
    						'total_payment'	  		=> $_totalpaid_perrecord,
    						'total_paymentafter'	=> $_totalpaid_perrecord,
    						'time'	  				=> $time,
    						'is_complete'	  		=> 0,
    						'type_record'	  		=> 0,
    						'note'	  				=> $_data['remark'.$i],
    				);
    				$this->_name='rms_investment_detail';
    				$this->insert($arr_invest_detail);
    				$time = $time+1;
    			}
    		}
    		if ($_data['broker_id']>0){
    			if(!empty($_data['identity_broker'])){
    				$ids = explode(',', $_data['identity_broker']);
    				$time_broker = 1;
    				foreach ($ids as $i){
    					$old_paid_pertime = $_data['total_payment_broker'.$i];
    					$arr_invest_broker=array(
    							'investment_id'	  		=> $invest_id,
    							'date'	  				=> $_data['date_payment_broker'.$i],
//     							'percent_return'	  	=> $_data['percent_broker'.$i],
    							'principle'	  			=> 0,
    							'principle_after'	  	=> 0,
    							'interest_amount'	  	=> $old_paid_pertime,
    							'interest_amountafter'	=> $old_paid_pertime,
    							'total_payment'	  		=> $old_paid_pertime,
    							'total_paymentafter'	=> $old_paid_pertime,
    							'time'	  				=> $time_broker,
    							'is_complete'	  		=> 0,
    							'type_record'	  		=> 0,
    							'note'	  				=> $_data['remark_broker'.$i],
    					);
    					$this->_name='rms_investment_detail_broker';
    					$this->insert($arr_invest_broker);
    					$time_broker = $time_broker+1;
    				}
    			}
    		}
    		return $invest_id;
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    
    public function getInvestmentById($id){
    	$this->_name='rms_investment';
    	$db = $this->getAdapter();
    	$sql=" SELECT *  FROM $this->_name WHERE id = $id LIMIT 1 ";
    	$row = $db->fetchRow($sql);
    	return $row;
    }
    public function getInvestmentDetailById($id){
    	$this->_name='rms_investment_detail';
    	$db = $this->getAdapter();
    	$sql=" SELECT *  FROM $this->_name WHERE investment_id = $id  ";
    	$row = $db->fetchAll($sql);
    	return $row;
    }
    public function getInvestmentDetailBrokerById($id){
    	$this->_name='rms_investment_detail_broker';
    	$db = $this->getAdapter();
    	$sql=" SELECT *  FROM $this->_name WHERE investment_id = $id ";
    	$row = $db->fetchAll($sql);
    	return $row;
    }
    
    public function editInvestment($_data){
    	try{
    		
    		$_arr=array(
    				'investor_id'	  	=> $_data['investor_id'],
    				'date'	      		=> $_data['date'],
    				'amount'	      	=> $_data['amount'],
    				'duration'  		=> $_data['duration'],
    				'note'	  			=> $_data['note'],
    				'broker_id'	    	=> $_data['broker_id'],
    				'broker_percent'	=>$_data['broker_percent'],
    				'broker_amount'		=>$_data['broker_amount'],
    				'broker_duration' 	=> $_data['broker_duration'],
    				'status' 			=> $_data['status'],
    				'user_id'	  		=> $this->getUserId(),
    				'modify_date' 		=> date("Y-m-d H:i:s"),
    		);
    		
    		$invest_id = $_data['id'];
    		$where = " id = $invest_id";
    		
    		$this->_name = 'rms_investment';
    		$this->update($_arr, $where);
    		
    		$this->_name = 'rms_investment_detail';
    		$whereDetail = ' investment_id = '.$invest_id;
    		$this->delete($whereDetail);
    		
    		$this->_name = 'rms_investment_detail_broker';
    		$whereBroker = ' investment_id = '.$invest_id;
    		$this->delete($whereBroker);
    		
    		if(!empty($_data['identity'])){
    			$ids = explode(',', $_data['identity']);
    			$countrecord = count($ids);
    			$time = 1;
    			foreach ($ids as $i){
    				$old_beginning_balance=$_data['amount'];
    				$end_balance=$_data['amount'];
    				$principle=0;
    				$_paid_perrecord=$_data['total_payment'.$i];
    				$_totalpaid_perrecord=$_data['total_payment'.$i];
    				if ($time == $countrecord){
    					$old_beginning_balance=$_data['amount'];
    					$end_balance=0;
    					$principle=$_data['amount'];
    					$_totalpaid_perrecord= $principle+$_data['total_payment'.$i];
    				}
    
    				$arr_invest_detail=array(
    						'investment_id'	  		=> $invest_id,
    						'date'	  				=> $_data['date_payment'.$i],
    						'beginning_balance'	  	=> $old_beginning_balance,
    						'beginning_balanceafter'=> $old_beginning_balance,
    						'ending_balance'	  	=> $end_balance,
    						'percent_return'	  	=> $_data['percent'.$i],
    						'principle'	  			=> $principle,
    						'principle_after'	  	=> $principle,
    						'interest_amount'	  	=> $_paid_perrecord,
    						'interest_amountafter'	=> $_paid_perrecord,
    						'total_payment'	  		=> $_totalpaid_perrecord,
    						'total_paymentafter'	=> $_totalpaid_perrecord,
    						'time'	  				=> $time,
    						'is_complete'	  		=> 0,
    						'type_record'	  		=> 0,
    						'note'	  				=> $_data['remark'.$i],
    				);
    				$this->_name='rms_investment_detail';
    				$this->insert($arr_invest_detail);
    				$time = $time+1;
    			}
    		}
    		if ($_data['broker_id']>0){
    			if(!empty($_data['identity_broker'])){
    				$ids = explode(',', $_data['identity_broker']);
    				$time_broker = 1;
    				foreach ($ids as $i){
    					$old_paid_pertime = $_data['total_payment_broker'.$i];
    					$arr_invest_broker=array(
    							'investment_id'	  		=> $invest_id,
    							'date'	  				=> $_data['date_payment_broker'.$i],
//     							'percent_return'	  	=> $_data['percent_broker'.$i],
    							'principle'	  			=> 0,
    							'principle_after'	  	=> 0,
    							'interest_amount'	  	=> $old_paid_pertime,
    							'interest_amountafter'	=> $old_paid_pertime,
    							'total_payment'	  		=> $old_paid_pertime,
    							'total_paymentafter'	=> $old_paid_pertime,
    							'time'	  				=> $time_broker,
    							'is_complete'	  		=> 0,
    							'type_record'	  		=> 0,
    							'note'	  				=> $_data['remark_broker'.$i],
    					);
    					$this->_name='rms_investment_detail_broker';
    					$this->insert($arr_invest_broker);
    					$time_broker = $time_broker+1;
    				}
    			}
    		}
    		return $invest_id;
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    
    public function checkInvestmentInWithdrawById($investment_id){
    	$db = $this->getAdapter();
    	$sql=" SELECT w.* FROM `rms_investor_withdraw` AS w
			WHERE w.status =1 AND w.investment_id=$investment_id ORDER BY w.id DESC LIMIT 1";
    	$row = $db->fetchRow($sql);
    	return $row;
    }
    public function checkInvestmentInWithdrawBrokerById($investment_id){
    	$db = $this->getAdapter();
    	$sql=" SELECT w.* FROM `rms_investor_withdraw_broker` AS w
    	WHERE w.status =1 AND w.investment_id=$investment_id ORDER BY w.id DESC LIMIT 1";
    	$row = $db->fetchRow($sql);
    	return $row;
    }
    
    
    public function updateBrokerSchedule($_data){
    	try{
	    	$_arr=array(
	    			'broker_duration' 	=> $_data['duration'],
	    			'user_id'	  		=> $this->getUserId(),
	    			'modify_date' 		=> date("Y-m-d H:i:s"),
	    	);
	    	$invest_id = $_data['id'];
	    	$where = " id = $invest_id";
	    	$this->_name = 'rms_investment';
	    	$this->update($_arr, $where);
	    	
	    	if(!empty($_data['identity'])){
	    		$ids = explode(',', $_data['identity']);
	    		$detailidlist = '';
	    		foreach ($ids as $i){
	    			if (empty($detailidlist)){
	    				if (!empty($_data['detailid'.$i])){
	    					$detailidlist= $_data['detailid'.$i];
	    				}
	    			}else{
	    				if (!empty($_data['detailid'.$i])){
	    					$detailidlist = $detailidlist.",".$_data['detailid'.$i];
	    				}
	    			}
	    		}
	    		$this->_name="rms_investment_detail_broker";
	    		$where2=" investment_id = ".$invest_id;
	    		if (!empty($detailidlist)){ // check if has old detail  detail id
	    			$where2.=" AND id NOT IN (".$detailidlist.")";
	    		}
	    		$this->delete($where2);
	    		
	    		foreach ($ids as $key => $i){
	    			if (!empty($_data['detailid'.$i])){
	    				
	    			}else{
	    				$old_paid_pertime = $_data['total_payment_'.$i];
	    				$old_paid_pertimeaffter = $_data['total_payment_'.$i];
	    				$is_complete = 0;
	    				if ($_data['ready_paid_'.$i]>0){
	    					$old_paid_pertimeaffter = $old_paid_pertime-$_data['ready_paid_'.$i];
	    					if ($old_paid_pertimeaffter==0){
	    						$is_complete = 1;
	    					}
	    				}
		    			$arr_invest_broker=array(
		    					'investment_id'	  		=> $invest_id,
		    					'date'	  				=> $_data['date_payment'.$i],
		    					'principle'	  			=> 0,
		    					'principle_after'	  	=> 0,
		    					'interest_amount'	  	=> $old_paid_pertime,
		    					'interest_amountafter'	=> $old_paid_pertimeaffter,
		    					'total_payment'	  		=> $old_paid_pertime,
		    					'total_paymentafter'	=> $old_paid_pertimeaffter,
		    					'time'	  				=> $key+1,
		    					'is_complete'	  		=> $is_complete,
		    					'type_record'	  		=> 0,
		    					'note'	  				=> $_data['note_'.$i],
		    			);
		    			$this->_name='rms_investment_detail_broker';
		    			$detailid = $this->insert($arr_invest_broker);
		    			
		    			if (!empty($_data['detailid_notcomplete'.$i])){
		    				if ($_data['ready_paid_'.$i]>0){
		    					$record = $this->checkInpaymentDetailBroker($_data['detailid_notcomplete'.$i]);
		    					if (!empty($record)){
		    						foreach ($record as $rs){
		    							$sum = $this->sumPaidDetailInOtherPaymentBroker($rs['investment_id'], $detailid, $rs['withdraw_id']);
		    							$interest_amount_oldpaid = empty($sum['total_interest_paid'])?0:$sum['total_interest_paid'];
		    							$total_payment_oldpaid = empty($sum['total_total_payment'])?0:$sum['total_total_payment'];
		    								
		    							$arr_money_detail = array(
		    									'lfd_id'	  				=> $detailid,
		    									'date'	  					=> $_data['date_payment'.$i],
		    									'principle'					=>	0,
		    									'principle_after'			=>	0,
		    									'interest_amount'			=>	$old_paid_pertime,
		    									'interest_amountafter'		=>	$old_paid_pertime-$interest_amount_oldpaid,
		    									'total_payment'				=>	$old_paid_pertime,
		    									'total_paymentafter'		=>  $old_paid_pertime-$total_payment_oldpaid,
		    									'time'	  				=> $key+1,
		    									'is_complete'				=>	0,
		    									'note'	  				=> $_data['note_'.$i],
		    									'status'					=>	1
		    							);
		    			
		    							$where = " id = ".$rs['id'];
		    							$this->_name="rms_investor_withdraw_broker_detail";
		    							$this->update($arr_money_detail, $where);
		    						}
		    					}
		    				}
		    			}
		    			
	    			}
	    		}
	    	}
	    	
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    
    
    public function updateInvestorSchedule($_data){
    	try{
    		$_arr=array(
    				'duration' 	=> $_data['duration'],
    				'user_id'	  		=> $this->getUserId(),
    				'modify_date' 		=> date("Y-m-d H:i:s"),
    		);
    		$invest_id = $_data['id'];
    		$where = " id = $invest_id";
    		$this->_name = 'rms_investment';
    		$this->update($_arr, $where);
    
    		if(!empty($_data['identity'])){
    			$ids = explode(',', $_data['identity']);
    			$detailidlist = '';
    			foreach ($ids as $i){
    				if (empty($detailidlist)){
    					if (!empty($_data['detailid'.$i])){
    						$detailidlist= $_data['detailid'.$i];
    					}
    				}else{
    					if (!empty($_data['detailid'.$i])){
    						$detailidlist = $detailidlist.",".$_data['detailid'.$i];
    					}
    				}
    			}
    			$this->_name="rms_investment_detail";
    			$where2=" investment_id = ".$invest_id;
    			if (!empty($detailidlist)){ // check if has old detail  detail id
    				$where2.=" AND id NOT IN (".$detailidlist.")";
    			}
    			$this->delete($where2);
    	   
    			foreach ($ids as $key => $i){
    				if (!empty($_data['detailid'.$i])){
    					 
    				}else{
    					
    					$old_beginning_balance=$_data['beginning_balance_'.$i];
    					$end_balance=$_data['ending_balance_'.$i];
    					$principle=$_data['principle_'.$i];
    					$principleafter=$_data['principle_'.$i];
    					
    					$_paid_perrecord=$_data['total_interest_'.$i];
    					$_paid_perrecordafter=$_data['total_interest_'.$i];
    					
    					$_totalpaid_perrecord=$_data['total_payment_'.$i];
    					$_totalpaid_perrecordafter=$_data['total_payment_'.$i];
    					
    					$is_complete = 0;
    					
    					if ($_data['ready_paid_'.$i]>0){
    						$remainmoney = $principleafter-$_data['ready_paid_'.$i];
    						if($remainmoney<=0){
    							$principleafter = 0;
    							$remainmoney = abs($remainmoney);
    							
    							$remainmoney = $_paid_perrecordafter-$remainmoney;
    							if($remainmoney<=0){
    								$_paid_perrecordafter = 0;
    							}else{
    								$_paid_perrecordafter = $remainmoney;
    							}
    							
    						}else{
    							$principleafter = $remainmoney;
    						}
    						
    						
    						$_totalpaid_perrecordafter = $_totalpaid_perrecordafter-$_data['ready_paid_'.$i];
    						if ($_totalpaid_perrecordafter==0){
    							$is_complete = 1;
    						}
    					}
    					
    					$arr_invest_detail=array(
    							'investment_id'	  		=> $invest_id,
    							'date'	  				=> $_data['date_payment'.$i],
    							'beginning_balance'	  	=> $old_beginning_balance,
    							'beginning_balanceafter'=> $old_beginning_balance,
    							'ending_balance'	  	=> $end_balance,
    							'percent_return'	  	=> $_data['percent_return_'.$i],
    							'principle'	  			=> $principle,
    							'principle_after'	  	=> $principleafter,
    							'interest_amount'	  	=> $_paid_perrecord,
    							'interest_amountafter'	=> $_paid_perrecordafter,
    							'total_payment'	  		=> $_totalpaid_perrecord,
    							'total_paymentafter'	=> $_totalpaid_perrecordafter,
    							'time'	  				=> $key+1,
    							'is_complete'	  		=> $is_complete,
    							'type_record'	  		=> 0,
    							'note'	  				=> $_data['note_'.$i],
    					);
    					$this->_name='rms_investment_detail';
    					$detailid = $this->insert($arr_invest_detail);
    					
    					if (!empty($_data['detailid_notcomplete'.$i])){
    						if ($_data['ready_paid_'.$i]>0){
    							$record = $this->checkInpaymentDetail($_data['detailid_notcomplete'.$i]);
    							if (!empty($record)){
    								foreach ($record as $rs){
    									$sum = $this->sumPaidDetailInOtherPayment($rs['investment_id'], $detailid, $rs['withdraw_id']);
    									$principle_oldpaid = empty($sum['total_principle_paid'])?0:$sum['total_principle_paid'];
    									$interest_amount_oldpaid = empty($sum['total_interest_paid'])?0:$sum['total_interest_paid'];
    									$total_payment_oldpaid = empty($sum['total_total_payment'])?0:$sum['total_total_payment'];
    									
    									$arr_money_detail = array(
    											'lfd_id'	  				=> $detailid,
    											'date'	  					=> $_data['date_payment'.$i],
    											'beginning_balance'			=>	$old_beginning_balance,
    											'beginning_balanceafter'	=>	$old_beginning_balance,
    											'ending_balance'			=>	$end_balance,
    											'percent_return'			=>	$_data['percent_return_'.$i],
    											'principle'					=>	$principle,
    											'principle_after'			=>	$principle-$principle_oldpaid,
    											'interest_amount'			=>	$_paid_perrecord,
    											'interest_amountafter'		=>	$_paid_perrecord-$interest_amount_oldpaid,
    											'total_payment'				=>	$_totalpaid_perrecord,
    											'total_paymentafter'		=>  $_totalpaid_perrecord-$total_payment_oldpaid,
    											'time'	  				=> $key+1,
    											'is_complete'				=>	0,
    											'note'	  				=> $_data['note_'.$i],
    											'status'					=>	1
    									);
    										
    									$where = " id = ".$rs['id'];
    									$this->_name="rms_investor_withdraw_detail";
    									$this->update($arr_money_detail, $where);
    								}
    							}
    						}
    					}
    					
    				}
    			}
    		}
    
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    
    function checkInpaymentDetail($detailid){
    	$db = $this->getAdapter();
    	$sql="SELECT wd.* FROM `rms_investor_withdraw_detail` AS wd WHERE wd.lfd_id =".$detailid." ORDER BY wd.withdraw_id,wd.id ASC";
    	$row = $db->fetchAll($sql);
    	return $row;
    }
    
    function sumPaidDetailInOtherPayment($invest_id,$detailid,$receipt_id){
    	$db = $this->getAdapter();
    	$sql="SELECT SUM(w.principle_paid) AS total_principle_paid,
			SUM(w.interest_paid) AS total_interest_paid,
			SUM(w.total_payment) AS total_total_payment FROM `rms_investor_withdraw` AS w,
			`rms_investor_withdraw_detail` AS wd
			WHERE 
			wd.withdraw_id = w.id AND
			w.investment_id=$invest_id AND w.id<$receipt_id
			AND
			wd.lfd_id=$detailid";
    	return $db->fetchRow($sql);
    }
    
    function checkInpaymentDetailBroker($detailid){
    	$db = $this->getAdapter();
    	$sql="SELECT wd.* FROM `rms_investor_withdraw_broker_detail` AS wd WHERE wd.lfd_id =".$detailid." ORDER BY wd.withdraw_id,wd.id ASC";
    	$row = $db->fetchAll($sql);
    	return $row;
    }
    
    function sumPaidDetailInOtherPaymentBroker($invest_id,$detailid,$receipt_id){
    	$db = $this->getAdapter();
    	$sql="SELECT SUM(w.principle_paid) AS total_principle_paid,
    	SUM(w.interest_paid) AS total_interest_paid,
    	SUM(w.total_payment) AS total_total_payment FROM `rms_investor_withdraw_broker` AS w,
    	`rms_investor_withdraw_broker_detail` AS wd
    	WHERE
    	wd.withdraw_id = w.id AND
    	w.investment_id=$invest_id AND w.id<$receipt_id
    	AND
    	wd.lfd_id=$detailid";
    	return $db->fetchRow($sql);
    }
}