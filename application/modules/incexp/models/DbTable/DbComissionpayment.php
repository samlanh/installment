<?php

class Incexp_Model_DbTable_DbComissionpayment extends Zend_Db_Table_Abstract
{	protected $_name = 'ln_sale_cancel';
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	
	}

	public function getAllComissionPayment($search=null){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		
		try{
			
			$from_date =(empty($search['start_date']))? '1': " cp.date_payment >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " cp.date_payment <= '".$search['end_date']." 23:59:59'";
			$where = " WHERE ".$from_date." AND ".$to_date;
			
			$sql=" SELECT 
					cp.id,
					(SELECT  p.`project_name` FROM `ln_project` AS p WHERE (p.`br_id` = cp.`branch_id`) LIMIT 1) AS branch_name,
					cp.receipt_no,
					(SELECT co_khname FROM `ln_staff` WHERE co_id=cp.agency_id LIMIT 1) AS staff_name,
					(SELECT name_kh FROM `ln_view` WHERE TYPE=13 AND key_code=cp.category LIMIT 1) AS category_name,
					(SELECT name_kh FROM `ln_view` WHERE TYPE=2 AND key_code=cp.payment_method LIMIT 1) AS payment_type,
					cp.total_paid,
					cp.total_due,
					cp.date_payment,
					(SELECT  first_name FROM rms_users WHERE id=cp.user_id LIMIT 1 ) AS user_name
				";
			$sql.=$dbp->caseStatusShowImage("cp.status");
			$sql.=" FROM `rms_commission_payment` AS cp ";
			
			if (!empty($search['adv_search'])){
					$s_where = array();
					$s_search = trim(addslashes($search['adv_search']));
					$s_where[] = " cp.receipt_no LIKE '%{$s_search}%'";
					$s_where[] = " cp.total_paid LIKE '%{$s_search}%'";
					$s_where[] = " cp.total_due LIKE '%{$s_search}%'";
					$s_where[] = " cp.cheque_no LIKE '%{$s_search}%'";
					$s_where[] = " (SELECT name_kh FROM `ln_view` WHERE TYPE=13 AND key_code=cp.category LIMIT 1) LIKE '%{$s_search}%'";
					$s_where[] = " (SELECT co_khname FROM `ln_staff` WHERE co_id=cp.agency_id LIMIT 1) LIKE '%{$s_search}%'";
					$where .=' AND ('.implode(' OR ',$s_where).')';
				}
		
			if($search['branch_id_search']>0){
				$where.= " AND branch_id = ".$search['branch_id_search'];
			}
			if($search['staff_id']>0){
				$condiction = $dbp->getChildAgency($search['staff_id']);
				if (!empty($condiction)){
					$where.=" AND cp.agency_id IN ($condiction)";
				}else{
					$where.=" AND cp.agency_id=".$search['staff_id'];
				}
			}
			if($search['status']>-1){
				$where.= " AND cp.status = ".$search['status'];
			}
			$where.=$dbp->getAccessPermission("c.`branch_id`");
			$order=' ORDER BY cp.`date_payment` DESC,cp.id DESC ';
			return $db->fetchAll($sql.$where.$order);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function getAllChequeIssue(){
		$db = $this->getAdapter();
		$sql = " SELECT DISTINCT cheque_issuer AS `name`,cheque_issuer as id FROM `rms_commission_payment` WHERE cheque_issuer!='' ORDER BY cheque_issuer ASC ";
		return $db->fetchAll($sql);
	}
	
	
	function addComissionPayment($_data){
		$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
			
			$dbexpense = new Incexp_Model_DbTable_DbExpense();
			$invoice = $dbexpense->getInvoiceNo($_data['branch_id']);
				
    		$_arr=array(
    				'branch_id'	 	 	=> $_data['branch_id'],
    				'receipt_no'		=> $invoice,
    				'agency_id'	    	=> $_data['staff_id'],
    				'category'	    	=> $_data['income_category'],
    				
    				'date_payment'  	=> $_data['date'],
    				'payment_method'	=> $_data['payment_type'],
					'cheque_no'      	=> $_data['cheque'],
					'cheque_issuer'     => $_data['cheque_issuer'],
					'bank_id'     		=> $_data['bank_id'],
    				'create_date'		=> date("Y-m-d H:i:s"),
    				'modify_date'	  	=> date("Y-m-d H:i:s"),
    				'status'			=> 1,
    				'user_id'  			=>$this->getUserId(),
    				'note'				=>$_data['note'],
					
					'balance'      		=> $_data['balance'],
    				'total_paid'		=> $_data['total_paid'],
					'total_due'      	=> $_data['total_due'],
    		);			
    		$this->_name ='rms_commission_payment';
    		$payment_id =  $this->insert($_arr);	
			
			$ids = explode(',', $_data['identity']);
    		$dueafter=0;
    		foreach ($ids as $i){
    			$is_payment =0;
    			$arrs = array(
    					'payment_id'	=>$payment_id,
    					'sale_id'	=>$_data['sale_id'.$i],
    					'house_id'	=>$_data['house_id'.$i],
    					'due_amount'	=>$_data['due_val'.$i],
    					'payment_amount'=>$_data['payment_amount'.$i],
    					'remain'		=>$_data['remain'.$i],
    			);
    			$this->_name ='rms_commission_payment_detail';
    			$this->insert($arrs);
    		}
			
			$db->commit();
	       return $payment_id;
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
	
	 function getCommissionPaymentById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT cp.*
    	FROM rms_commission_payment AS cp
    	WHERE cp.id=$id ";
		$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission("cp.`branch_id`");
    	$sql.=" LIMIT 1 ";
		
    	return $db->fetchRow($sql);
    }
	
	function getCommisionByAgency($data){
    	$db = $this->getAdapter();
    	$staffId = $data['staff_id'];
    	$branchId = empty($data['branch_id'])?0:$data['branch_id'];
    	$sql="SELECT s.*,
					(SELECT c.`name_kh` FROM `ln_client` AS c WHERE c.`client_id`=s.`client_id` LIMIT 1) AS customerName,
					(SELECT land_address FROM `ln_properties` WHERE ln_properties.id=s.house_id LIMIT 1) AS landCode,
					(SELECT street FROM `ln_properties` WHERE ln_properties.id=s.house_id LIMIT 1) AS street,
					(s.full_commission-((SELECT COALESCE(SUM(com.total_amount),0) FROM `ln_comission` AS com WHERE com.status=1 AND com.sale_id = s.id LIMIT 1))-((SELECT COALESCE(SUM(cpd.payment_amount),0) FROM `rms_commission_payment_detail` AS cpd,rms_commission_payment AS cp WHERE cp.id = cpd.payment_id AND cp.status =1 AND cpd.sale_id = s.id LIMIT 1)) ) 
					
					AS full_commission_after
					
			FROM `ln_sale` AS s  
			WHERE s.staff_id =$staffId 
				AND s.status=1 
				AND s.is_cancel = 0  
				AND s.full_commission >0 
				AND (s.full_commission-((SELECT COALESCE(SUM(com.total_amount),0) FROM `ln_comission` AS com WHERE com.status=1 AND com.sale_id = s.id LIMIT 1))-((SELECT COALESCE(SUM(cpd.payment_amount),0) FROM `rms_commission_payment_detail` AS cpd,rms_commission_payment AS cp WHERE cp.id = cpd.payment_id AND cp.status =1 AND cpd.sale_id = s.id LIMIT 1)) ) >0
				";
    	if(!empty($branchId)){
			$sql.=" AND s.branch_id =$branchId ";
		}
    	$rs = $db->fetchAll($sql);
    	
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$string='';
    	$no = $data['keyindex'];
    	$identity='';
    	$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
    	if(!empty($rs)){
    		foreach ($rs as $key => $row){
    			if (empty($identity)){
    				$identity=$no;
    			}else{$identity=$identity.",".$no;
    			}
    			$string.='
    			<tr id="row'.$no.'" class="rowData">
    				<td data-label="'.$tr->translate("CHECKING").'" align="center" style="  padding: 0 10px;">
						<div class="custom-control custom-checkbox ">
							<input type="checkbox" class="checkboxSystem custom-control-input checkbox"  OnChange="CheckAllTotal('.$no.')" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]" >
							<label class="custom-control-label" for="mfdid_'.$no.'">
							</label>
						</div>
					</td>
	    			<td data-label="'.$tr->translate("NUM").'" >'.($key+1).'</td>
	    			<td data-label="'.$tr->translate("CUSTOMER_NAME").'" >&nbsp;
	    				<label id="customerName'.$no.'">'.$row['customerName'].'</label>
	    				<input type="hidden" dojoType="dijit.form.TextBox" name="sale_id'.$no.'" id="sale_id'.$no.'" value="'.$row['id'].'" >
    				</td>
    			<td data-label="'.$tr->translate("PROPERTY_CODE").'" >&nbsp;
					<label id="landCode'.$no.'" >'.$row['landCode']." , ".$row['street'].'</label>
					<input type="hidden" dojoType="dijit.form.TextBox" name="house_id'.$no.'" id="house_id'.$no.'" value="'.$row['house_id'].'" >
    			</td>
    			<td data-label="'.$tr->translate("COMMISSION_AMOUNT").'" >&nbsp;
					<label id="origtotallabel'.$no.'">'.number_format($row['full_commission'],2).'</label>
					<input type="hidden" dojoType="dijit.form.TextBox" name="origVal'.$no.'" id="origVal'.$no.'" value="'.$row['full_commission'].'" >
    			</td>
    			<td data-label="'.$tr->translate("DUE").'" >&nbsp;
					<label id="duelabel'.$no.'">'.number_format($row['full_commission_after'],2).'</label>
					<input type="hidden" dojoType="dijit.form.TextBox" name="due_val'.$no.'" id="due_val'.$no.'" value="'.$row['full_commission_after'].'" >
    			</td>
    			<td data-label="'.$tr->translate("PAYMENT_AMOUNT").'"><input type="text" readonly="readonly" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateamount('.$no.');" name="payment_amount'.$no.'" id="payment_amount'.$no.'" value="0" style="text-align: center;" ></td>
    			<td data-label="'.$tr->translate("REMAIN").'"><input type="text" class="fullside" readonly="readonly" dojoType="dijit.form.NumberTextBox" required="required" name="remain'.$no.'" id="remain'.$no.'" value="'.$row['full_commission_after'].'" style="text-align: center;" ></td>
    			</tr>
    			';$no++;
    		}
    	}else{
    		$no++;
    	}
    	$all_balance =0;
    	$userbalace = $this->getCurrentBalanceByAgency($data);
    	if (!empty($userbalace)){
    		$all_balance = $userbalace;
    	}
    	$array = array('stringrow'=>$string,'keyindex'=>$no,'identity'=>$identity,'all_balance'=>$all_balance);
    	return $array;
    }
	function getCurrentBalanceByAgency($data){
    	$db = $this->getAdapter();
		
		$staffId = $data['staff_id'];
		$branchId = empty($data['branch_id'])?0:$data['branch_id'];
		$stringSql="";
		$sttSqlStum="";
		$sttSqlStum="";
		if(!empty($branchId)){
			$stringSql=" AND s.branch_id =$branchId ";
			$sttSqlStum=" AND com.branch_id =$branchId ";
			$strCP=" AND cp.branch_id =$branchId ";
		}
		
    	
    	$sql = "SELECT SUM(s.`full_commission`)-((SELECT COALESCE(SUM(com.total_amount),0) FROM `ln_comission` AS com WHERE com.status=1 AND com.staff_id=s.staff_id  $sttSqlStum LIMIT 1))-((SELECT COALESCE(SUM(cp.total_paid),0) FROM rms_commission_payment AS cp WHERE  cp.status =1 AND cp.agency_id=s.staff_id  $strCP  LIMIT 1)) AS all_balance 
			FROM `ln_sale` AS s  
			WHERE s.staff_id =$staffId 
				AND s.status=1 
				AND s.is_cancel = 0  
				AND s.full_commission >0";
		$sql.=$stringSql;
    	return $db->fetchOne($sql);
    }
	
	function getCommissionPaymentDetail($payment_id){
    	$db = $this->getAdapter();
    	$sql="SELECT pd.*,
				(SELECT c.`name_kh` FROM `ln_client` AS c WHERE c.`client_id`=s.`client_id` LIMIT 1) AS customerName,
				(SELECT land_address FROM `ln_properties` WHERE ln_properties.id=s.house_id LIMIT 1) AS landCode,
				(SELECT street FROM `ln_properties` WHERE ln_properties.id=s.house_id LIMIT 1) AS street
			 FROM `rms_commission_payment_detail` AS pd,
					ln_sale AS s
			 WHERE s.id = pd.sale_id AND pd.payment_id =$payment_id ";
    	return $db->fetchAll($sql);
    }
	function getCommisionPaymentDetailByPaymentIdAndSaleId($payment_id,$sale_id){
    	$db = $this->getAdapter();
    	$sql="SELECT 
			pd.*,
				(SELECT c.`name_kh` FROM `ln_client` AS c WHERE c.`client_id`=s.`client_id` LIMIT 1) AS customerName,
				(SELECT land_address FROM `ln_properties` WHERE ln_properties.id=s.house_id LIMIT 1) AS landCode,
				(SELECT street FROM `ln_properties` WHERE ln_properties.id=s.house_id LIMIT 1) AS street,
				s.full_commission,
				(s.full_commission-((SELECT COALESCE(SUM(com.total_amount),0) FROM `ln_comission` AS com WHERE com.status=1 AND com.sale_id = s.id LIMIT 1))-((SELECT COALESCE(SUM(cpd.payment_amount),0) FROM `rms_commission_payment_detail` AS cpd,rms_commission_payment AS cp WHERE cp.id = cpd.payment_id AND cp.status =1 AND cpd.sale_id = s.id LIMIT 1)) ) 
					AS full_commission_after
    	FROM 
			`rms_commission_payment_detail` AS pd,
			ln_sale AS s			
		WHERE s.id = pd.sale_id AND pd.payment_id =$payment_id AND pd.sale_id =$sale_id LIMIT 1 ";
    	return $db->fetchRow($sql);
    }
	function getSumPaymentDetailBySaleId($arrQuery){
    	$db = $this->getAdapter();
		$sale_id = empty($arrQuery['sale_id'])?0:$arrQuery['sale_id'];
		$paymentdetailid = empty($arrQuery['commissionPaymentDetail'])?0:$arrQuery['commissionPaymentDetail'];
		$staffId = empty($arrQuery['staffId'])?0:$arrQuery['staffId'];
		$branchId = empty($arrQuery['branchId'])?0:$arrQuery['branchId'];
		
    	$sql="SELECT 
				COALESCE(SUM(pd.`payment_amount`),0)+((SELECT COALESCE(SUM(com.total_amount),0) FROM `ln_comission` AS com WHERE com.status=1 AND com.staff_id=$staffId  AND com.branch_id=$branchId LIMIT 1)) AS tolalpayamount 
			FROM `rms_commission_payment_detail` AS pd ,
			rms_commission_payment AS p
			WHERE p.id = pd.payment_id 
				AND  pd.`sale_id`=$sale_id 
				AND pd.`id` != $paymentdetailid 
				AND p.`status`=1 ";
    	return $db->fetchRow($sql);
    }
	function getCommisionByAgencyEdit($data){
    	
    	$rows = $this->getCommissionPaymentDetail($data['payment_id']);
    	$listSaleidpaid ='';
    	if (!empty($rows)) foreach ($rows as $paymentdetail){
    		if (empty($listSaleidpaid)){
    			$listSaleidpaid=$paymentdetail['sale_id'];
    		}else{$listSaleidpaid=$listSaleidpaid.",".$paymentdetail['sale_id'];
    		}
    	}
    	
    	$db = $this->getAdapter();
		
		$staffId = empty($data['staff_id'])?0:$data['staff_id'];
    	$branchId = empty($data['branch_id'])?0:$data['branch_id'];
    	$sql="SELECT s.*,
					(SELECT c.`name_kh` FROM `ln_client` AS c WHERE c.`client_id`=s.`client_id` LIMIT 1) AS customerName,
					(SELECT land_address FROM `ln_properties` WHERE ln_properties.id=s.house_id LIMIT 1) AS landCode,
					(SELECT street FROM `ln_properties` WHERE ln_properties.id=s.house_id LIMIT 1) AS street,
					(s.full_commission-((SELECT COALESCE(SUM(com.total_amount),0) FROM `ln_comission` AS com WHERE com.status=1 AND com.sale_id = s.id LIMIT 1))-((SELECT COALESCE(SUM(cpd.payment_amount),0) FROM `rms_commission_payment_detail` AS cpd,rms_commission_payment AS cp WHERE cp.id = cpd.payment_id AND cp.status =1 AND cpd.sale_id = s.id LIMIT 1)) ) 
					
					AS full_commission_after
					
			FROM `ln_sale` AS s  
			WHERE s.staff_id =$staffId 
				AND s.status=1 
				AND s.is_cancel = 0  
				AND s.full_commission >0 
				AND (s.full_commission-((SELECT COALESCE(SUM(com.total_amount),0) FROM `ln_comission` AS com WHERE com.status=1 AND com.sale_id = s.id LIMIT 1))-((SELECT COALESCE(SUM(cpd.payment_amount),0) FROM `rms_commission_payment_detail` AS cpd,rms_commission_payment AS cp WHERE cp.id = cpd.payment_id AND cp.status =1 AND cpd.sale_id = s.id LIMIT 1)) ) >0
				";
    	if(!empty($branchId)){
			$sql.=" AND s.branch_id =$branchId ";
		}
		if (!empty($listSaleidpaid)){
    		$sql.=" OR s.`id` IN ($listSaleidpaid) ";
    	}
    	$rs = $db->fetchAll($sql);
    	 
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$string='';
    	$no = $data['keyindex'];
    	$identity='';
    	$identityedit='';
    	$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
    	if(!empty($rs)){
    		foreach ($rs as $key => $row){
    			if (empty($identity)){
    				$identity=$no;
    			}else{$identity=$identity.",".$no;
    			}
    			
    			$rowpaymentdetail = $this->getCommisionPaymentDetailByPaymentIdAndSaleId($data['payment_id'], $row['id']);
    			if (!empty($rowpaymentdetail)){
    				
    				$duevalu=$rowpaymentdetail['due_amount'];
					
					$arrQuery= array(
						'commissionPaymentDetail'=>$rowpaymentdetail['id'],
						'sale_id'=>$rowpaymentdetail['sale_id'],
						'branchId'=>$branchId,
						'staffId'=>$staffId,
					);
    				$paymenttailbybilling = $this->getSumPaymentDetailBySaleId($arrQuery);// get other pay amount on this Purchase on other payment number
    				if (!empty($paymenttailbybilling)){
    					$duevalu = $rowpaymentdetail['full_commission']-$paymenttailbybilling['tolalpayamount'];
    				}
    				if (empty($identityedit)){
    					$identityedit=$no;
    				}else{$identityedit=$identityedit.",".$no;
    				}
    				$string.='
    				<tr id="row'.$no.'" class="rowData">
	    				<td data-label="'.$tr->translate("CHECKING").'" align="center" style="  padding: 0 10px;">
							<div class="custom-control custom-checkbox ">
								<input type="checkbox" class="checkboxSystem custom-control-input checkbox" checked="checked" OnChange="CheckAllTotal('.$no.')" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]" >
								<label class="custom-control-label" for="mfdid_'.$no.'">
								</label>
							</div>
						</td>
	    				<td data-label="'.$tr->translate("NUM").'" >'.($key+1).'</td>
	    				<td data-label="'.$tr->translate("CUSTOMER_NAME").'" >&nbsp;
		    				<label id="customerName'.$no.'">'.$rowpaymentdetail['customerName'].'</label>
							<input type="hidden" dojoType="dijit.form.TextBox" name="sale_id'.$no.'" id="sale_id'.$no.'" value="'.$rowpaymentdetail['sale_id'].'" >
	    				</td>
	    				<td data-label="'.$tr->translate("PROPERTY_CODE").'" >&nbsp;
							<label id="landCode'.$no.'" >'.$rowpaymentdetail['landCode']." , ".$rowpaymentdetail['street'].'</label>
							<input type="hidden" dojoType="dijit.form.TextBox" name="house_id'.$no.'" id="house_id'.$no.'" value="'.$rowpaymentdetail['house_id'].'" >
	    				</td>
	    				<td data-label="'.$tr->translate("COMMISSION_AMOUNT").'" >&nbsp;
	    					<label id="origtotallabel'.$no.'">'.number_format($rowpaymentdetail['full_commission'],2).'</label>
	    				</td>
	    				<td data-label="'.$tr->translate("DUE").'" >&nbsp;
		    				<label id="duelabel'.$no.'">'.number_format($rowpaymentdetail['full_commission_after'],2).'</label>
		    				<input type="hidden" dojoType="dijit.form.TextBox" name="due_val'.$no.'" id="due_val'.$no.'" value="'.$duevalu.'" >
							<input type="hidden" dojoType="dijit.form.TextBox" name="detailid'.$no.'" id="detailid'.$no.'" value="'.$rowpaymentdetail['id'].'" >
	    				</td>
	    				<td data-label="'.$tr->translate("PAYMENT_AMOUNT").'"><input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateamount('.$no.');" name="payment_amount'.$no.'" id="payment_amount'.$no.'" value="'.$rowpaymentdetail['payment_amount'].'" style="text-align: center;" ></td>
	    				<td data-label="'.$tr->translate("REMAIN").'"><input type="text" class="fullside" readonly="readonly" dojoType="dijit.form.NumberTextBox" required="required" name="remain'.$no.'" id="remain'.$no.'" value="'.$rowpaymentdetail['full_commission_after'].'" style="text-align: center;" ></td>
    				</tr>';
    			}else{
	    			$string.='
						<tr id="row'.$no.'" class="rowData">
							<td data-label="'.$tr->translate("CHECKING").'" align="center" style="  padding: 0 10px;">
								<div class="custom-control custom-checkbox ">
									<input type="checkbox" class="checkboxSystem custom-control-input checkbox"  OnChange="CheckAllTotal('.$no.')" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]" >
									<label class="custom-control-label" for="mfdid_'.$no.'">
									</label>
								</div>
							
							</td>
							<td data-label="'.$tr->translate("NUM").'" >'.($key+1).'</td>
							<td data-label="'.$tr->translate("CUSTOMER_NAME").'" >&nbsp;
								<label id="customerName'.$no.'">'.$row['customerName'].'</label>
								<input type="hidden" dojoType="dijit.form.TextBox" name="sale_id'.$no.'" id="sale_id'.$no.'" value="'.$row['id'].'" >
							</td>
						<td data-label="'.$tr->translate("PROPERTY_CODE").'" >&nbsp;
							<label id="landCode'.$no.'" >'.$row['landCode']." , ".$row['street'].'</label>
							<input type="hidden" dojoType="dijit.form.TextBox" name="house_id'.$no.'" id="house_id'.$no.'" value="'.$row['house_id'].'" >
						</td>
						<td data-label="'.$tr->translate("COMMISSION_AMOUNT").'" >&nbsp;
							<label id="origtotallabel'.$no.'">'.number_format($row['full_commission'],2).'</label>
							<input type="hidden" dojoType="dijit.form.TextBox" name="origVal'.$no.'" id="origVal'.$no.'" value="'.$row['full_commission'].'" >
						</td>
						<td data-label="'.$tr->translate("DUE").'" >&nbsp;
							<label id="duelabel'.$no.'">'.number_format($row['full_commission_after'],2).'</label>
							<input type="hidden" dojoType="dijit.form.TextBox" name="due_val'.$no.'" id="due_val'.$no.'" value="'.$row['full_commission_after'].'" >
						</td>
						<td data-label="'.$tr->translate("PAYMENT_AMOUNT").'"><input type="text" readonly="readonly" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateamount('.$no.');" name="payment_amount'.$no.'" id="payment_amount'.$no.'" value="0" style="text-align: center;" ></td>
						<td data-label="'.$tr->translate("REMAIN").'"><input type="text" class="fullside" readonly="readonly" dojoType="dijit.form.NumberTextBox" required="required" name="remain'.$no.'" id="remain'.$no.'" value="'.$row['full_commission_after'].'" style="text-align: center;" ></td>
						</tr>
						';
    			}$no++;
    		}
    	}else{
    		$no++;
    	}
    	$all_balance =0;
    	$userbalace = $this->getCurrentBalanceByAgency($data);
    	if (!empty($userbalace)){
    		$all_balance = $userbalace;
    	}
    	$array = array('sql'=>$sql,'stringrow'=>$string,'keyindex'=>$no,'identity'=>$identity,'identitycheck'=>$identityedit,'all_balance'=>$all_balance);
    	return $array;
    }
	
	function editComissionPayment($_data){
		$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
			
			if($_data['status']==0){
				$_arr=array(
    				'modify_date'	  	=> date("Y-m-d H:i:s"),
    				'status'			=> $_data['status'],
    				'user_id'  			=>$this->getUserId(),
    				'note'				=>$_data['note'],
				);			
				$this->_name ='rms_commission_payment';
				$payment_id = $_data['id'];
				$where = " id = ".$payment_id;
				$this->update($_arr, $where);
				
			}else{
				$_arr=array(
						'branch_id'	 	 	=> $_data['branch_id'],
						'agency_id'	    	=> $_data['staff_id'],
						'category'	    	=> $_data['income_category'],
						
						'date_payment'  	=> $_data['date'],
						'payment_method'	=> $_data['payment_type'],
						'cheque_no'      	=> $_data['cheque'],
						'cheque_issuer'     => $_data['cheque_issuer'],
						'bank_id'     		=> $_data['bank_id'],
						'modify_date'	  	=> date("Y-m-d H:i:s"),
						'status'			=> $_data['status'],
						'user_id'  			=>$this->getUserId(),
						'note'				=>$_data['note'],
						
						'balance'      		=> $_data['balance'],
						'total_paid'		=> $_data['total_paid'],
						'total_due'      	=> $_data['total_due'],
				);			
				$this->_name ='rms_commission_payment';
				$payment_id = $_data['id'];
				$where = " id = ".$payment_id;
				$this->update($_arr, $where);
				
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
				// delete old payment detail that don't have on new payment detail after edit
				$this->_name="rms_commission_payment_detail";
				$where2=" payment_id = ".$payment_id;
				if (!empty($detailidlist)){ // check if has old payment detail  detail id
					$where2.=" AND id NOT IN (".$detailidlist.")";
				}
				$this->delete($where2);
				
				
				$ids = explode(',', $_data['identity']);
				$dueafter=0;
				foreach ($ids as $i){
					if (!empty($_data['detailid'.$i])){
						$arrs = array(
								'payment_id'=>$payment_id,
								'sale_id'=>$_data['sale_id'.$i],
								'house_id'=>$_data['house_id'.$i],
								'due_amount'=>$_data['due_val'.$i],
								'payment_amount'=>$_data['payment_amount'.$i],
								'remain'=>$_data['remain'.$i],
						);
						$this->_name ='rms_commission_payment_detail';
						$where=" id= ".$_data['detailid'.$i];
						$this->update($arrs, $where);
					}else{
						$arrs = array(
								'payment_id'	=>$payment_id,
								'sale_id'	=>$_data['sale_id'.$i],
								'house_id'	=>$_data['house_id'.$i],
								'due_amount'	=>$_data['due_val'.$i],
								'payment_amount'=>$_data['payment_amount'.$i],
								'remain'		=>$_data['remain'.$i],
						);
						$this->_name ='rms_commission_payment_detail';
						$this->insert($arrs);
					}
				}
			}
			$db->commit();
	       return $payment_id;
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
}

