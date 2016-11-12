<?php

class Loan_Model_DbTable_DbLandpayment extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_sale';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
    
//     public function getClient($type){
//     	$this->_name='ln_client';
//     	$sql ="SELECT
//     	client_number ,
//     	name_en,
//     	client_id
//     	FROM $this->_name lm WHERE status=1 AND name_en !='' AND is_group=0  "; ///just and is_group =0;
//     	$db = $this->getAdapter();
//     	$rows = $db->fetchAll($sql);
//     	$options=array(0=>'------Select------');
//     	if(!empty($rows))foreach($rows AS $row){
//     		if($type==1){
//     			$lable = $row['client_number'];
//     		}elseif($type==2){
//     			$lable = $row['name_en'];
//     		}
//     		$options[$row['client_id']]=$lable;
//     	}
//     	return $options;
//     }
    public function getAllIndividuleLoan($search,$reschedule =null){
    	$from_date =(empty($search['start_date']))? '1': " s.buy_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " s.buy_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	$sql=" 
    	SELECT `s`.`id` AS `id`,
    	(SELECT
		     `ln_project`.`project_name`
		   FROM `ln_project`
		   WHERE (`ln_project`.`br_id` = `s`.`branch_id`)
		   LIMIT 1) AS `branch_name`,
    	s.sale_number,
    	`c`.`client_number`   AS `client_number`,
	    `c`.`name_kh`         AS `name_kh`,
	    `c`.`name_en`         AS `name_en`,
	    `p`.`land_code`       AS `land_code`,
	    `p`.`land_address`    AS `land_address`,
	    `p`.`street`          AS `street`,
	    (SELECT name_en FROM `ln_view` WHERE key_code =s.payment_id AND type = 25 limit 1) AS paymenttype,
  		`s`.`price_before`    AS `price_before`,
        `s`.`discount_amount` AS `discount_amount`,
        `s`.`paid_amount`     AS `paid_amount`,
        `s`.`balance`         AS `balance`,
        `s`.`buy_date`        AS `buy_date`,
         s.status
		FROM ((`ln_sale` `s`
		    JOIN `ln_client` `c`)
		   JOIN `ln_properties` `p`)
		WHERE ((`c`.`client_id` = `s`.`client_id`)
       AND (`p`.`id` = `s`.`house_id`)) ";
    	
    	$db = $this->getAdapter();
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
      	 	$s_where[] = " s.receipt_no LIKE '%{$s_search}%'";
      	 	$s_where[] = " p.land_code LIKE '%{$s_search}%'";
      	 	$s_where[] = " p.land_address LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.client_number LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.name_en LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.name_kh LIKE '%{$s_search}%'";
      	 	$s_where[] = " s.price_sold LIKE '%{$s_search}%'";
      	 	$s_where[] = " s.comission LIKE '%{$s_search}%'";
      	 	$s_where[] = " s.total_duration LIKE '%{$s_search}%'";
      	 	$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND s.status = ".$search['status'];
    	}
    	if(($search['client_name'])>0){
    		$where.= " AND `s`.`client_id`=".$search['client_name'];
    	}
    	if(($search['branch_id'])>0){
    		$where.= " AND s.branch_id = ".$search['branch_id'];
    	}
    	if(($search['schedule_opt'])>0){
    		$where.= " AND s.payment_id = ".$search['schedule_opt'];
    	}
//     	if(($search['co_id'])>0){
//     		$where.= " AND lg.co_id=".$search['co_id'];
//     	}
//     	if(($search['currency_type'])>0){
//     		$where.= " AND lm.currency_type=".$search['currency_type'];
//     	}
//     	if(($search['pay_every'])>0){
//     		$where.= " AND lg.pay_term=".$search['pay_every'];
//     	}
//     	if($reschedule!=null){
//     		$where.= ' AND lg.is_reschedule=2 ';
//     	}else{
//     		$where.= ' AND lg.is_reschedule !=2 ';
//     	}
    		
    	$order = " ORDER BY s.id DESC";
    	$db = $this->getAdapter();    
//      	echo $sql.$where.$order;	
    	return $db->fetchAll($sql.$where.$order);
    	//`stGetAllIndividuleLoan`(IN txt_search VARCHAR(30),IN client_id INT,IN method INT,IN branch INT,IN co INT,IN s_status INT,IN from_d VARCHAR(70),IN to_d VARCHAR(70))
    }
    function getTranLoanByIdWithBranch($id,$is_newschedule=null){//group id
    	$sql = " SELECT * FROM `ln_paymentschedule` AS p
			WHERE p.id = $id ";
    	$where="";
    	if($is_newschedule!=null){
    		$where.=" AND p.is_reschedule = 2 ";
    	}
    	
    	$where.=" LIMIT 1 ";
    	return $this->getAdapter()->fetchRow($sql.$where);
    }
    public function getLoanviewById($id){
    	$sql = "SELECT
    	lg.g_id
    	,(SELECT branch_nameen FROM `ln_branch` WHERE br_id =lg.branch_id LIMIT 1) AS branch_name
    	,lg.level,
    	(SELECT name_en FROM `ln_view` WHERE status =1 and type=24 and key_code=lg.for_loantype) AS for_loantype
    	,(SELECT co_firstname FROM `ln_co` WHERE co_id =lg.co_id LIMIT 1) AS co_firstname
    	,(select concat(zone_name,'-',zone_num)as dd from `ln_zone` where zone_id = lg.zone_id ) AS zone_name
    	,(SELECT name_en FROM `ln_view` WHERE status =1 and type=14 and key_code=lg.pay_term) AS pay_term
    	,(SELECT name_en FROM `ln_view` WHERE status =1 and type=14 and key_code=lg.collect_typeterm) AS collect_typeterm
    	,lg.date_release
    	,lg.total_duration
    	,lg.first_payment
    	,lg.time_collect
    	,(SELECT name_en FROM `ln_view` WHERE status =1 and type=2 and key_code=lg.holiday) AS holiday
    	,lg.date_line
    	,lm.pay_after, lm.pay_before
    	,(SELECT payment_nameen FROM `ln_payment_method` WHERE id =lm.payment_method ) AS payment_nameen
    	,(SELECT curr_nameen FROM `ln_currency` WHERE id=lm.currency_type) AS currency_type
    	,lm.graice_period,
    	lm.loan_number,lm.interest_rate,lm.amount_collect_principal,lm.semi,
    	lm.client_id,lm.admin_fee,
    	lm.pay_after,lm.pay_before,lm.other_fee
    	,(SELECT name_kh FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_name_kh,
    	(SELECT name_en FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_name_en,
    	(SELECT group_code FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS group_code,
    	(SELECT client_number FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_number,
    	lm.total_capital,lm.interest_rate,lm.payment_method,
    	lg.time_collect,
    	lg.zone_id,
    	(SELECT co_firstname FROM `ln_co` WHERE co_id =lg.co_id LIMIT 1) AS co_enname,
    	lg.status AS str ,lg.status FROM `ln_loan_group` AS lg,`ln_loan_member` AS lm
    	WHERE lg.g_id = lm.group_id AND lm.member_id = $id LIMIT 1 ";
    	return $this->getAdapter()->fetchRow($sql);
    }

    function round_up($value, $places)
    {
    	$mult = pow(10, abs($places));
    	return $places < 0 ?
    	ceil($value / $mult) * $mult :
    	ceil($value * $mult) / $mult;
    }
    function round_up_currency($curr_id, $value,$places=-2){
    	if ($curr_id==1){
    		return $this->round_up($value, $places);
    	}
    	else{
    		return round($value,2);
    	}
    }
   
    public function addSchedulePayment($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		$loan_number = $dbtable->getLoanNumber($data);
    			   $arr = array(
    				'branch_id'=>$data['branch_id'],
    			   	'house_id'=>$data["land_code"],
    				'sale_number'=>$data['sale_code'],
    			   	'payment_id'=>$data["schedule_opt"],
    				'client_id'=>$data['member'],
    				'price_before'=>$data['total_sold'],
    				'discount_amount'=>$data['discount'],
    				'price_sold'=>$data['sold_price'],
    				'other_fee'=>$data['other_fee'],
    				'paid_amount'=>$data['deposit'],
    				'balance'=>$data['balance'],
    				'buy_date'=>$data['date_buy'],
    				'end_line'=>$data['date_line'],
    				'interest_rate'=>$data['interest_rate'],
    				'total_duration'=>$data['period'],
    			   	'startcal_date'=>$data['release_date'],
    				'first_payment'=>$data['first_payment'],
    			   	'validate_date'=>$data['first_payment'],
    				'payment_method'=>1,//$data['loan_type'],
    				'note'=>$data['note'],
//     				'payment_number'=>$data['loan_type'],
    				//'graice_period'=>$data['pay_every'],
    				//'amount_collect'=>$data['repayment_method'],
    				//'is_renew'=>0,
    				//'admin_fee'=>$data['other_fee'],
    				'staff_id'=>$data['staff_id'],
    				'comission'=>$data['commission'],
    				'create_date'=>date("Y-m-d"),
    				'user_id'=>$this->getUserId()
    				);
    		
    		$id = $this->insert($arr);//add group loan
    		$this->_name="ln_properties";
    		$where = "id =".$data["land_code"];
    		$arr = array(
    				"is_lock"=>1
    				);
    		$this->update($arr, $where);
    		unset($datagroup);
    		
    		$total_day=0;
    		$old_remain_principal = 0;
    		$old_pri_permonth = 0;
    		$old_interest_paymonth = 0;
    		$old_amount_day = 0;
    		$cum_interest=0;
    		$amount_collect = 1;
    		$remain_principal = $data['balance'];
    		$next_payment = $data['first_payment'];
    		$from_date =  $data['release_date'];
    		$curr_type = 2;//$data['currency_type'];
    		$loop_payment = $data['period'];
    		$borrow_term = $data['period'];
    		$payment_method = $data["schedule_opt"];
    		
    		$str_next = '+1 month';
//     		$str_next = $dbtable->getNextDateById($data['collect_termtype'],$data['amount_collect']);//for next,day,week,month;
    		for($i=1;$i<=$loop_payment;$i++){
    			if($payment_method==1){
    				
    			}elseif($payment_method==2){
    				
    			}elseif($payment_method==3){//pay by times//check date payment
    				if($i!=1){
    					$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    					$start_date = $next_payment;
    					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$data['first_payment']);
    				}else{
    					$next_payment = $data['first_payment'];
    					$next_payment = $dbtable->checkFirstHoliday($next_payment,3);//normal day
    				}
    				$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    				$total_day = $amount_day;
    				$interest_paymonth = 0;
    				$pri_permonth = $data['balance']/$borrow_term;
    				if($i==$loop_payment){//for end of record only
    					$pri_permonth = $remain_principal;
    				}
    			}elseif($payment_method==4){
			    		if($i!=1){
			    			$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
			    			$start_date = $next_payment;
			    			$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$data['first_payment']);
			    				
			    		}else{
			    			$next_payment = $data['first_payment'];
			    			$next_payment = $dbtable->checkFirstHoliday($next_payment,3);//normal day
			    		}
			    		$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
			    		$total_day = $amount_day;
// 			    		if($data['collect_termtype']==1){
// 			    			$amount_day=$data['amount_collect'];
// 			    		}
// 			    		$interest_paymonth = $remain_principal*(($data['interest_rate']/12)/100/$borrow_term)*$amount_day;
			    		$interest_paymonth = $remain_principal*(($data['interest_rate']/12)/100);//fixed 30day
			    		$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
// 			    		if($data['collect_termtype']==1){
// 			    			$amount_day= $total_day ;
// 			    		}
			    		$pri_permonth = $data['fixed_payment']-$interest_paymonth;
			    		if($i==$loop_payment){//for end of record only
			    			$pri_permonth = $remain_principal;
			    		}
    			   }
    			   if($payment_method==3 OR $payment_method==4){
			    		$old_remain_principal =$old_remain_principal+$remain_principal;
			    		$old_pri_permonth = $old_pri_permonth+$pri_permonth;
			    		$old_interest_paymonth = $this->round_up_currency($curr_type,($old_interest_paymonth+$interest_paymonth));
			    		$cum_interest = $cum_interest+$old_interest_paymonth;
			    		$old_amount_day =$old_amount_day+ $amount_day;
			    		          $this->_name="ln_saleschedule";
			    			        $datapayment = array(
			    			        	'branch_id'=>$data['branch_id'],
			    			        	'sale_id'=>$id,//good
			    			        	'begining_balance'=> $old_remain_principal,//good
			    			        	'principal_permonth'=> $old_pri_permonth,//good
			    			        	'principal_permonthafter'=>$old_pri_permonth,//good
			    			        	'total_interest'=>$old_interest_paymonth,//good
			    			        	'total_interest_after'=>$old_interest_paymonth,//good
			    			        	'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
			    			        	'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth,//good
			    			        	'ending_balance'=>$old_remain_principal-$old_pri_permonth,
			    			        	'cum_interest'=>$cum_interest,
			    			        	'amount_day'=>$old_amount_day,
			    			        	'is_completed'=>0,
			    			        	'date_payment'=>$next_payment,
	// 		    			        	'collect_by'=>1,
	// 		    			        	'payment_option'=>$cum_interest,
	// 		    			        	'penelize'=>,
	// 		    			        	'service_charge'=>,
	// 		    			        	'status'=>,
			    			        );
		            		 
			    		$this->insert($datapayment);
	// 		    		$amount_collect=0;
			    		$old_remain_principal = 0;
			    		$old_pri_permonth = 0;
			    		$old_interest_paymonth = 0;
			    		$old_amount_day = 0;
			    		$from_date=$next_payment;
    			   }
    		}
	            $db->commit();
	        return 1;
	        }catch (Exception $e){
	            	$db->rollBack();
	            	echo $e->getMessage();exit();
	            	Application_Form_FrmMessage::message("INSERT_FAIL");
	            	Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	        }

	
    }
    function updateLoanById($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		if($data['status_using']==0){
    			$arr_update = array(
    					'status'=>0
    			);
    			$where = ' status = 1 AND id = '.$data['id'];
    			$this->update($arr_update, $where);
    			 
    			$this->_name = 'ln_paymentschedule_detail';
    			$where = ' is_completed = 0 AND status = 1 AND paymentid = '.$data['id'];
    			$this->update($arr_update, $where);
    			
    			$this->_name='ln_landinfo';
    			if($data['land_code']!=$data['old_landid']){
    				$where =" id= ".$data['old_landid'];
    				$arr = array('is_buy'=>0);
    				$this->update($arr,$where);
    			}
    			
    			 
    			$db->commit();
    			return 1;
    		}
    		
    		
    		  $schedule = array(
    					'client_id'=>$data['customer_code'],
    					'land_id'=>$data['land_code'],//$data['loan_code'],
    					'price'=>$data['sold_price'],
    			   		'org_price'=>$data['land_price'],
    					'deposit'=>$data['deposit'],
    					'balance'=>$data['balance'],//$data[''],
    					'payment_type'=>$data['schedule_opt'],
    					'amount_month'=>$data['period'],
    					'interest_rate'=>$data['interest_rate'],
    					'panelty'=>$data['pay_late'],
    			   		'discount'=>$data['discount'],
    					'date_register'=>date("Y-m-d"),
    					'date_buy'=>$data['release_date'],
    					'first_datepay'=>$data['first_payment'],
    					'end_date'=>$data['date_line'],
    					'staff_id'=>$data['co_id'],
    					'commission'=>$data['commission'],
    					'is_reschedule'=>0,
    					'is_completed'=>0,
    					'is_cancel'=>0,
    			   		'user_id'=>$this->getUserId()
    		);
    		$id = $data['id'];
    		$where = $db->quoteInto('id=?', $id);
    		$this->update($schedule, $where);
    		unset($schedule);
    		
    		$this->_name='ln_paymentschedule_detail';
    		$where = $db->quoteInto('paymentid=?', $data['id']);
    		$this->delete($where);

    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		
    		  $this->_name='ln_landinfo';
    		   $where =" id= ".$data['land_code'];
    		   $arr = array('is_buy'=>1);
    		   $this->update($arr,$where);
    			
    			if($data['land_code']!=$data['old_landid']){
    				$where =" id= ".$data['old_landid'];
    				$arr = array('is_buy'=>0);
    				$this->update($arr,$where);
    			}
    			
    			if($data['schedule_opt']==4 AND $data['balance']==0){//if balance no =0 please check it 
    				$this->_name='ln_paymentschedule';
    				$where =" id= ".$id;
    				$arr = array('is_completed'=>1);
    				$this->update($arr,$where);
    			}
    			
    			$old_remain_principal = 0;
    			$old_pri_permonth = 0;
    			$old_interest_paymonth = 0;
    			$old_amount_day = 0;
    			$amount_collect = 1;
    			$interest_paymonth=0;
    			
    			$remain_principal = $data['balance'];
    			$next_payment = $data['first_payment'];
    			$start_date = $data['release_date'];
    			$from_date = $data['release_date'];
    			
	            $str_next = $dbtable->getNextDateById(3,1);//for next,day,week,month;
    			$percent = array(0=>10,1=>5,2=>5,3=>5,4=>5);
    			
    			$this->_name='ln_paymentschedule_detail';
    	if($data['schedule_opt']==1 AND $data['sold_price']>$data['deposit']){
	            	for($index=0;$index<=4;$index++){
	            		if($index!=0){
	            			$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
	            			//$pri_permonth = $remain_principal*($percent[$index]/100);
	            			$pri_permonth = ($data['sold_price'])*($percent[$index]/100);
	            			$pri_permonth = round($pri_permonth,2);
	            			$start_date = $next_payment;
	            			$next_payment = $dbtable->getNextPayment($str_next, $next_payment,1,2,$data['first_payment']);
	            			$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	            		}else{
	            			//$pri_permonth = $remain_principal*($percent[$index]/100);
	            			$pri_permonth = ($data['sold_price'])*($percent[$index]/100)-$data["deposit"];
	            			$next_payment = $data['first_payment'];
	            			$next_payment = $dbtable->checkFirstHoliday($next_payment,2);
	            			$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	            		}
	            		 
	            		$old_remain_principal =$old_remain_principal+$remain_principal;
	            		$old_pri_permonth = $old_pri_permonth+$pri_permonth;
	            		$old_interest_paymonth = round($old_interest_paymonth+$interest_paymonth);
	            		$old_amount_day =$old_amount_day+ $amount_day;
	            	
	            		$datapayment = array(
	            				'paymentid'=>$id,
	            				'outstanding'=>$remain_principal,//good
	            				'principal_permonth'=> $old_pri_permonth,//good
	            				'principal_after'=> $old_pri_permonth,//good
	            				'total_interest'=>$old_interest_paymonth,//good
	            				'total_interest_after'=>$old_interest_paymonth,//good
	            				'total_payment'=>$old_pri_permonth,//good
	            				'total_payment_after'=>$old_pri_permonth,//good
	            				'amount_day'=>$old_amount_day,//good
	            				'date_payment'=>$next_payment,
	            				'penelize'=>0,
	            				'penelize_after'=>0,
	            				'is_completed'=>0,
	            				'status'=>1
	            		);
	            		 
	            		$this->insert($datapayment);
	            	
	            		$from_date=$next_payment;
	            		if($index!=0){
	            			$next_payment = $dbtable->checkDefaultDate($str_next, $start_date, 1,2,$data['first_payment']);
	            		}
	            		 
	            		$amount_collect=0;
	            		$old_remain_principal = 0;
	            		$old_pri_permonth = 0;
	            		$old_interest_paymonth = 0;
	            		$old_amount_day = 0;
	            	}
	            	
	            	$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
	            	$oldtotalprincipal = $remain_principal;
	            	$pri_permonth = $remain_principal/$data['period'];
	            	$pri_permonth = round($pri_permonth,2);
	            	for($i=1;$i<=$data['period'];$i++){//remain of 5 months first payment
	            		if($i!=1){
	            			$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
	            			$start_date = $next_payment;
	            			$interest_paymonth =$oldtotalprincipal*$data['interest_rate']/100;//$remain_principal*$data['interest_rate']/100;
	            			$next_payment = $dbtable->getNextPayment($str_next, $next_payment,1,2,$data['first_payment']);
	            			$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	            			if($i==$data['period']){//end of record
	            				$pri_permonth=$remain_principal;
	            			}
	            		}else{
	            			$interest_paymonth =$oldtotalprincipal*$data['interest_rate']/100;
	            			$next_payment = $data['first_payment'];
	            			$next_payment = $dbtable->checkFirstHoliday($next_payment,2);
	            			$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	            		}
	            		 
	            		$old_remain_principal =$old_remain_principal+$remain_principal;
	            		$old_pri_permonth = $old_pri_permonth+$pri_permonth;
	            		$old_interest_paymonth = round($old_interest_paymonth+$interest_paymonth,2);
	            		$old_amount_day =$old_amount_day+ $amount_day;
	            		 
	            		$datapayment = array(
	            				'paymentid'=>$id,
	            				'outstanding'=>$remain_principal,//good
	            				'principal_permonth'=> $old_pri_permonth,//good
	            				'principal_after'=> $old_pri_permonth,//good
	            				'total_interest'=>$old_interest_paymonth,//good
	            				'total_interest_after'=>$old_interest_paymonth,//good
	            				'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
	            				'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth,//good
	            				'amount_day'=>$old_amount_day,//good
	            				'date_payment'=>$next_payment,
	            				'penelize'=>0,
	            				'penelize_after'=>0,
	            				'is_completed'=>0,
	            				'status'=>1
	            		);
	            		 
	            		$this->insert($datapayment);
	            		 
	            		$from_date=$next_payment;
	            		if($index!=0){
	            			$next_payment = $dbtable->checkDefaultDate($str_next, $start_date, 1,2,$data['first_payment']);
	            		}
	            		 
	            		$amount_collect=0;
	            		$old_remain_principal = 0;
	            		$old_pri_permonth = 0;
	            		$old_interest_paymonth = 0;
	            		$old_amount_day = 0;
	            		 
	            	}
	            }elseif($data['schedule_opt']==2 AND $data['balance']>0){
	            	
	            	$percent1 = array(0=>30,1=>30,2=>40);
	            	$discount = $data['discount']/100;
	            	//$remain_principal = ($data['land_price']-($data['land_price']*$discount));//$data['balance'];
	            	for($index=0;$index<=2;$index++){
	            		if($index!=0){
	            			$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
	            			$pri_permonth = ($data['sold_price'])*($percent1[$index]/100);
	            			//$pri_permonth = ($mainbalance)*($percent1[$index]/100);
	            			$pri_permonth = round($pri_permonth,2);
	            	
	            			$start_date = $next_payment;
	            			$next_payment = $dbtable->getNextPayment($str_next, $next_payment,1,2,$data['first_payment']);
	            			$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	            			if($index==2){
	            				$remain_principal=$pri_permonth;
	            			}
	            		}else{
	            			$mainbalance = $remain_principal;
	            			$pri_permonth = ($data['sold_price'])*($percent1[$index]/100)-$data["deposit"];
	            			$pri_permonth = round($pri_permonth,2);
	            			$next_payment = $data['first_payment'];
	            			$next_payment = $dbtable->checkFirstHoliday($next_payment,2);
	            			$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	            		}
	            		$old_remain_principal =$old_remain_principal+$remain_principal;
	            		$old_pri_permonth = $old_pri_permonth+$pri_permonth;
	            		$old_interest_paymonth = round($old_interest_paymonth+$interest_paymonth);
	            		$old_amount_day =$old_amount_day+ $amount_day;
	            	
	            		$datapayment = array(
	            				'paymentid'=>$id,
	            				'outstanding'=>$remain_principal,//good
	            				'principal_permonth'=> $old_pri_permonth,//good
	            				'principal_after'=> $old_pri_permonth,//good
	            				'total_interest'=>$old_interest_paymonth,//good
	            				'total_interest_after'=>$old_interest_paymonth,//good
	            				'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
	            				'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth,//good
	            				'amount_day'=>$old_amount_day,//good
	            				'date_payment'=>$next_payment,
	            				'penelize'=>0,
	            				'penelize_after'=>0,
	            				'is_completed'=>0,
	            				'status'=>1
	            		);
	            	
	            		$this->insert($datapayment);
	            		$from_date=$next_payment;
	            		if($index!=0){
	            			$next_payment = $dbtable->checkDefaultDate($str_next, $start_date, 1,2,$data['first_payment']);
	            		}
	            	
	            		$amount_collect=0;
	            		$old_remain_principal = 0;
	            		$old_pri_permonth = 0;
	            		$old_interest_paymonth = 0;
	            		$old_amount_day = 0;
	            	}
	            }elseif($data['schedule_opt']==3 AND $data['balance']>0){
	            	
	            	$percent3 = array(0=>20,1=>20,2=>20,3=>20,4=>20);
	            	for($index=0;$index<=4;$index++){
	            		if($index!=0){
	            			// 	            			$pri_permonth = ($data['land_price']-($data['land_price']*$discount))*($percent3[$index]/100);
	            			$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
	            			$pri_permonth = ($data['sold_price'])*($percent3[$index]/100);
	            			$pri_permonth = round($pri_permonth,2);
	            			$start_date = $next_payment;
	            			$next_payment = $dbtable->getNextPayment($str_next, $next_payment,1,2,$data['first_payment']);
	            			$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	            		}else{
	            	
	            			//$pri_permonth =($remain_principal)*($percent3[$index]/100);
	            			$pri_permonth = ($data['sold_price'])*($percent3[$index]/100)-$data["deposit"];
	            	
	            			$mainbalance = $remain_principal;
	            			$pri_permonth = round($pri_permonth,2);
	            			$next_payment = $data['first_payment'];
	            			$next_payment = $dbtable->checkFirstHoliday($next_payment,2);
	            			$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	            		}
	            		$old_remain_principal =$old_remain_principal+$remain_principal;
	            		$old_pri_permonth = $old_pri_permonth+$pri_permonth;
	            		$old_interest_paymonth = round($old_interest_paymonth+$interest_paymonth);
	            		$old_amount_day =$old_amount_day+ $amount_day;
	            	
	            		$datapayment = array(
	            				'paymentid'=>$id,
	            				'outstanding'=>$remain_principal,//good
	            				'principal_permonth'=> $old_pri_permonth,//good
	            				'principal_after'=> $old_pri_permonth,//good
	            				'total_interest'=>$old_interest_paymonth,//good
	            				'total_interest_after'=>$old_interest_paymonth,//good
	            				'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
	            				'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth,//good
	            				'amount_day'=>$old_amount_day,//good
	            				'date_payment'=>$next_payment,
	            				'penelize'=>0,
	            				'penelize_after'=>0,
	            				'is_completed'=>0,
	            				'status'=>1
	            		);
	            	
	            		$this->insert($datapayment);
	            		$from_date=$next_payment;
	            		if($index!=0){
	            			$next_payment = $dbtable->checkDefaultDate($str_next, $start_date, 1,2,$data['first_payment']);
	            		}
	            	
	            		$amount_collect=0;
	            		$old_remain_principal = 0;
	            		$old_pri_permonth = 0;
	            		$old_interest_paymonth = 0;
	            		$old_amount_day = 0;
	            	}
	            } else{
	                $interest_paymonth =($data['balance'])*$data['interest_rate']/100;//$remain_principal*$data['interest_rate']/100;
	            	if($data['balance']>0){//balance >0
	            		$datapayment = array(
	            				'paymentid'=>$id,
	            				'outstanding'=>$data['balance'],//good
	            				'principal_permonth'=> $data['balance'],//good
	            				'principal_after'=> $data['balance'],//good
	            				'total_interest'=> $interest_paymonth,//good
	            				'total_interest_after'=>$interest_paymonth,//good
	            				'total_payment'=>$data['balance']+$interest_paymonth,//good
	            				'total_payment_after'=>$data['balance']+$interest_paymonth,//good
	            				'amount_day'=>0,//good
	            				'date_payment'=>$next_payment,
	            				'penelize'=>0,
	            				'penelize_after'=>0,
	            				'is_completed'=>0,
	            				'status'=>1
	            		);
	            		$this->insert($datapayment);
	            	}
	            }
	            $db->commit();
	            return 1;
    	}catch (Exception $e){
    		$db->rollBack();
    		echo $e->getMessage();exit();
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    public function addScheduleTestPayment($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		$loan_number = $dbtable->getLoanNumber($data);
    		$arr = array(
    				'branch_id'=>$data['branch_id'],
//     				'house_id'=>$data["land_code"],
    				'sale_number'=>$data['sale_code'],
    				'payment_id'=>$data["schedule_opt"],
    				'client_id'=>$data['member'],
    				'price_before'=>$data['total_sold'],
    				'discount_amount'=>$data['discount'],
    				'price_sold'=>$data['sold_price'],
    				'other_fee'=>$data['other_fee'],
    				'paid_amount'=>$data['deposit'],
    				'balance'=>$data['balance'],
    				'buy_date'=>$data['date_buy'],
    				'end_line'=>$data['date_line'],
    				'interest_rate'=>$data['interest_rate'],
    				'total_duration'=>$data['period'],
    				'first_payment'=>$data['first_payment'],
    				'validate_date'=>$data['first_payment'],
    				'payment_method'=>1,//$data['loan_type'],
    				'note'=>$data['note'],
    				'staff_id'=>$data['staff_id'],
    				'comission'=>$data['commission'],
    				'create_date'=>date("Y-m-d"),
    				'user_id'=>$this->getUserId()
    		);
    		$this->_name="ln_sale_test";
    		$id = $this->insert($arr);//add group loan
    
    		$total_day=0;
    		$old_remain_principal = 0;
    		$old_pri_permonth = 0;
    		$old_interest_paymonth = 0;
    		$old_amount_day = 0;
    		$cum_interest=0;
    		$amount_collect = 1;
    		$remain_principal = $data['balance'];
    		$next_payment = $data['first_payment'];
    		$from_date =  $data['release_date'];
    		$curr_type = 2;//$data['currency_type'];
    		$loop_payment = $data['period'];
    		$borrow_term = $data['period'];
    		$payment_method = $data["schedule_opt"];
    
    		$str_next = '+1 month';
    		for($i=1;$i<=$loop_payment;$i++){
    			if($payment_method==1){
    				
    			}elseif($payment_method==2){
    				
    			}elseif($payment_method==3){//pay by times//check date payment
    				if($i!=1){
    					$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    					$start_date = $next_payment;
    					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$data['first_payment']);
    				}else{
    					$next_payment = $data['first_payment'];
    					$next_payment = $dbtable->checkFirstHoliday($next_payment,3);//normal day
    				}
    				$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    				$total_day = $amount_day;
    				$interest_paymonth = 0;
    				$pri_permonth = $data['balance']/$borrow_term;
    				if($i==$loop_payment){//for end of record only
    					$pri_permonth = $remain_principal;
    				}
    			}elseif($payment_method==4){
    				if($i!=1){
    					$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    					$start_date = $next_payment;
    					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$data['first_payment']);
    			   
    				}else{
    					$next_payment = $data['first_payment'];
    					$next_payment = $dbtable->checkFirstHoliday($next_payment,3);//normal day
    				}
    				$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    				$total_day = $amount_day;
    			    $interest_paymonth = $remain_principal*(($data['interest_rate']/12)/100);//fixed 30day
    				$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
    			
    				$pri_permonth = $data['fixed_payment']-$interest_paymonth;
    				if($i==$loop_payment){//for end of record only
    					$pri_permonth = $remain_principal;
    				}
    			}elseif($payment_method==5){//bank
    				
    			}
    			if($payment_method==3 OR $payment_method==4){
	    			$old_remain_principal =$old_remain_principal+$remain_principal;
	    			$old_pri_permonth = $old_pri_permonth+$pri_permonth;
	    			$old_interest_paymonth = $this->round_up_currency($curr_type,($old_interest_paymonth+$interest_paymonth));
	    			$cum_interest = $cum_interest+$old_interest_paymonth;
	    			$old_amount_day =$old_amount_day+ $amount_day;
	    			$this->_name="ln_saleschedule_test";
	    			$datapayment = array(
	    					'branch_id'=>$data['branch_id'],
	    					'sale_id'=>$id,//good
	    					'begining_balance'=> $old_remain_principal,//good
	    					'principal_permonth'=> $old_pri_permonth,//good
	    					'principal_permonthafter'=>$old_pri_permonth,//good
	    					'total_interest'=>$old_interest_paymonth,//good
	    					'total_interest_after'=>$old_interest_paymonth,//good
	    					'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
	    					'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth,//good
	    					'ending_balance'=>$old_remain_principal-$old_pri_permonth,
	    					'cum_interest'=>$cum_interest,
	    					'amount_day'=>$old_amount_day,
	    					'is_completed'=>0,
	    					'date_payment'=>$next_payment,
	    			);    
	    			$this->insert($datapayment);
	    			$old_remain_principal = 0;
	    			$old_pri_permonth = 0;
	    			$old_interest_paymonth = 0;
	    			$old_amount_day = 0;
	    			$from_date=$next_payment;
    			}
    		}
    		if($payment_method==3 OR $payment_method==4){
	    		$sql = " SELECT t.* , DATE_FORMAT(t.date_payment, '%d-%m-%Y') AS date_payments,
	    		DATE_FORMAT(t.date_payment, '%Y-%m-%d') AS date_name FROM
	    		ln_saleschedule_test AS t WHERE t.sale_id = ".$id;
	    		$rows = $db->fetchAll($sql);
    		}else{
    			$sql = " SELECT *,'row_id' FROM ln_sale_test WHERE id = ".$id;
    			$rows = $db->fetchRow($sql);
    		}
    		$db->commit();
    		return $rows;
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    
    
    }
function getLoanPaymentByLoanNumber($data){
    	$db = $this->getAdapter();
    	$loan_number= $data['loan_number'];
    	if($data['type']!=2){
    		$where =($data['type']==1)?'loan_number = '.$loan_number:'client_id='.$loan_number;
    	    $sql=" SELECT *,
    	            (SELECT co_id FROM `ln_loan_group` WHERE g_id = 
    	            (SELECT lm.member_id FROM `ln_loan_member` AS lm WHERE lm.member_id = member_id LIMIT 1)) AS co_id,
    	            (SELECT lm.client_id FROM `ln_loan_member` AS lm WHERE lm.member_id = member_id LIMIT 1) AS client_id
					,(SELECT currency_type FROM `ln_loan_member` WHERE $where LIMIT 1  ) AS curr_type
    	     FROM `ln_loanmember_funddetail` WHERE member_id =
		    	(SELECT  member_id FROM `ln_loan_member` WHERE $where AND status=1 LIMIT 1)
		    	AND status = 1 ";
    	}elseif($data['type']==2){
    		$sql=" SELECT *,
	    		(SELECT co_id FROM `ln_loan_group` WHERE g_id = 
	    	    (SELECT lm.member_id FROM `ln_loan_member` AS lm WHERE lm.member_id = member_id LIMIT 1)) AS co_id,    	            	
	    		(SELECT lm.client_id FROM `ln_loan_member` AS lm WHERE lm.member_id = member_id LIMIT 1) AS client_id
	    		,(SELECT currency_type FROM `ln_loan_member` WHERE $where LIMIT 1  ) AS curr_type
    		 FROM `ln_loanmember_funddetail` WHERE status = 1 AND member_id = 
    		       (SELECT member_id FROM `ln_loan_member` WHERE client_id =
    		       (SELECT client_id FROM `ln_client` WHERE client_number = ".$loan_number." LIMIT 1) LIMIT 1) ";
    	}
    	return $db->fetchAll($sql);
    }
function getLoanLevelByClient($client_id,$type){
    	$db  = $this->getAdapter();
    	if($type==1){
    		$sql = " SELECT COUNT(member_id) FROM `ln_loan_member` WHERE STATUS =1 AND client_id = $client_id LIMIT 1 ";
    	}else{
    		$sql = "SELECT COUNT(m.member_id) FROM `ln_loan_member` AS m,`ln_loan_group` AS g WHERE m.status =1 AND
    		m.group_id =g_id AND m.client_id = $client_id AND g.loan_type=2 LIMIT 1";
    	} 
    	$level  = $db->fetchOne($sql);
    	return ($level+1);
    }
   
    public function getLoanInfo($id){//when repayment shedule
    	$db=$this->getAdapter();
    	$sql="SELECT  (SELECT lf.total_principal FROM `ln_loanmember_funddetail` AS lf WHERE lf. member_id= l.member_id AND STATUS=1 AND lf.is_completed=0 LIMIT 1)  AS total_principal
    	,l.currency_type FROM `ln_loan_member` AS l WHERE l.client_id=$id AND status=1 AND l.is_completed=0
    	";
    	return $db->fetchRow($sql);
    }
    public function getAllMemberLoanById($member_id){//for get id fund detail for update
    	$db = $this->getAdapter();
    	$sql = "SELECT lm.member_id ,lm.client_id,lm.group_id ,lm.loan_number,
    	(SELECT name_kh FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_name_kh,
    	(SELECT name_en FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_name_en,
    	(SELECT client_number FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_number,
    	lm.total_capital,lm.admin_fee,lm.loan_purpose FROM `ln_loan_member` AS lm
    	WHERE lm.status =1 AND lm.group_id = $member_id ";
    	return $db->fetchAll($sql);
    }
    public function getLastPayDate($data){
    	$loanNumber = $data['loan_numbers'];
    	$db = $this->getAdapter();
    	$sql ="SELECT cd.`date_payment` FROM `ln_client_receipt_money_detail` AS cd,`ln_client_receipt_money` AS c WHERE c.`id` = cd.`crm_id` AND c.`loan_number`='$loanNumber' ORDER BY cd.`id` DESC";
    	//return $sql;
    	return $db->fetchOne($sql);
    }
  
}