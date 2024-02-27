<?php

class Loan_Model_DbTable_DbPaymentCombine extends Zend_Db_Table_Abstract
{	protected $_name = 'ln_client_receipt_money_combine';
	
	public function getUserId(){
		$db = new Application_Model_DbTable_DbGlobal();
		return $db->getUserId();
	}
	
	public function getAllPaymentCombine($search){
		$start_date = $search['start_date'];
    	$end_date = $search['end_date'];
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
    	
    	$sql = "
			SELECT 
				cmb.id
				,(SELECT project_name FROM `ln_project` WHERE br_id=cmb.`branchId` LIMIT 1) AS branch_name
				,c.`name_kh` AS clientName
				,(SELECT CONCAT(crm.`receipt_no`,' ".$tr->translate("COMBINE")."') FROM `ln_client_receipt_money` AS crm WHERE crm.combineId = cmb.id ORDER BY crm.id ASC LIMIT 1) AS recieptNo
				,cmb.`datePayment`
				,cmb.`totalPrinciple`
				,cmb.`totalInterest`
				,cmb.`totalPayment`
				,(SELECT v.name_kh FROM `ln_view` AS v WHERE v.type=2 AND v.key_code=cmb.`paymentMethod` LIMIT 1) AS paymentMethodTitle
				,(SELECT  u.first_name FROM rms_users AS u WHERE u.id=cmb.`userId` LIMIT 1 ) AS userName
			";
				
		$sql.=$dbp->caseStatusShowImage("cmb.status");
		$sql.="
			FROM 
				`ln_client_receipt_money_combine` AS cmb 
				LEFT JOIN `ln_client` AS c ON c.`client_id` = cmb.`clientId`
			WHERE 1
		";
    	$where ='';
    	$from_date =(empty($search['start_date']))? '1': " cmb.`datePayment` >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " cmb.`datePayment` <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['advance_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['advance_search']));
    		$s_where[] = " (SELECT crm.`receipt_no` FROM `ln_client_receipt_money` AS crm WHERE crm.combineId = cmb.id ORDER BY crm.id ASC LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " c.`name_kh` LIKE '%{$s_search}%'";
    		$s_where[] = " c.`phone` LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if($search['branch_id']>0){
    		$where.= " AND cmb.`branchId` = ".$search['branch_id'];
    	}
    	if($search['status']>-1){
    		$where.= " AND cmb.status = ".$search['status'];
    	}
    	if($search['client_name']>0){
    		$where.=" AND cmb.`clientId`= ".$search['client_name'];
    	}
    	if($search['payment_method']>0){
    		$where.=" AND cmb.`paymentMethod`= ".$search['payment_method'];
    	}
    	$order = " ORDER BY cmb.id DESC";
    	$where.=$dbp->getAccessPermission("cmb.`branchId`");
    	
    	return $db->fetchAll($sql.$where.$order);
    }
	
	public function getCombineNumber($data=array('branchId'=>1)){
   	$this->_name='ln_client_receipt_money_combine';
   	$db = $this->getAdapter();
	
	$lenghtReceipt=6;
   	$oldNumber=0;
	$pre='№ ';
   	$sql=" SELECT COUNT(id) FROM $this->_name WHERE branchId =".$data['branchId'];
	$acc_no = $db->fetchOne($sql);
   	$new_acc_no= (int)$acc_no+$oldNumber+1;
   	$acc_no= strlen((int)$acc_no+$oldNumber+1);
  	for($i = $acc_no;$i<$lenghtReceipt;$i++){//phnom penh thmey
   		$pre.='0';
   	}
   	return $pre.$new_acc_no;
   }
	
	public function addPaymentCombine($data){
    	$db = $this->getAdapter();
    	$userId = $this->getUserId();
    	try{
			$arrNum = array(
				'branchId'	=>	$data["branchId"]
			);
			$combineNumber = $this->getCombineNumber($arrNum);
			$arr_client_pay = array(
    			'combineNumber'			=>	$combineNumber,
    			'branchId'				=>	$data["branchId"],
    			'datePayment'			=>	$data['datePayment'],
    			
				'clientId'              =>	$data['clientId'],
    			'paymentMethod'			=>	$data["paymentMethod"],
    			'bankId'				=>	$data["bankId"],
    			'cheque'				=>	$data["cheque"],
    		
    			'totalInterest' 			=>  $data["totalInterest"],
    			'totalPrinciple' 			=>  $data["totalPrinciple"],
    			'totalPayment' 				=>  $data["totalPayment"],
    			'totalAllPaid' 				=>  $data["totalAllPaid"],
    			'totalBalance' 				=>  $data["totalBalance"],
    			'note' 					=>  $data["note"],
    			'userId' 				=>  $userId,
    			'createDate'		=> date("Y-m-d H:i:s"),
				'modifyDate'	  	=> date("Y-m-d H:i:s"),
    		);
    		
			$this->_name = "ln_client_receipt_money_combine";
    		$crmCombineId = $this->insert($arr_client_pay);
			
			$ids = explode(',', $data['identity']);
			if(!empty($ids)){
				$dbGb = new Application_Model_DbTable_DbGlobal();
				$dbPayment = new Loan_Model_DbTable_DbLoanILPayment();
				$dbRePayment = new Loan_Model_DbTable_DbRepaymentSchedule();
				
				$arrRowSale = array(
						"branch_id"=>$data["branchId"]
						,"to_branch_id"=>$data["branchId"]
						,"collect_date"=>$data["datePayment"]
						,"client_id"=>$data['clientId']
						
						,"member"=>$data['clientId'] //no schedule
						,"paid_date"=>$data["datePayment"] //no schedule
						,"date_line"=>$data["datePayment"] //no schedule
						,"second_depostit"=>0 //no schedule
						
						,"service_charge"=>0
						,"extrapayment"=>0
						,"penalize_amount"=>0
						,"option_pay"=>1
						,"is_payoff"=>0
						
						,'payment_method'		=>	$data["paymentMethod"]
						,'bank_id'				=>	$data["bankId"]
						,'cheque'				=>	$data["cheque"]
						,'note'				=>	$data["note"]
					);
				foreach ($ids as $i){
					
					$reciept_no = $dbGb->getReceiptByBranch(array("branch_id"=>$data["branchId"]));
					
					$arrRowSale["co_id"] = $data["agencyId$i"];
					if($data["scheduleId$i"]>0){ //payment has schedule
						$arrRowSale["reciept_no"] 			= $reciept_no;
						$arrRowSale["loan_number"] 			= $data["saleId$i"];
						$arrRowSale["property_id"] 			= $data["houseId$i"];
						$arrRowSale["outstanding_balance"] 	= $data["currentBalance$i"];
						$arrRowSale["sold_price"] 			= $data["priceSold$i"];
						$arrRowSale["amount_receive"] 		= $data["principleAmount$i"];
						$arrRowSale["os_amount"] 			= $data["principleAmount$i"];
						$arrRowSale["total_interest"] 		= $data["interestAmount$i"];
						$arrRowSale["total_payment"] 		= $data["totalPayment$i"];
						$arrRowSale["priciple_amount"] 		= $data["balanceAmount$i"];
						$arrRowSale["remain"] 				= 0;//not ready
						$arrRowSale["paid_times"] 			= $data["paidTimes$i"];
						$arrRowSale["schedule_opt"] 		= $data["scheduleOpt$i"];
						$arrRowSale["interest_rate"] 		= $data["interestRate$i"];
						$arrRowSale["date_payment"] 		= $data["datePayment$i"];
						$crmId = $dbPayment->addILPayment($arrRowSale);
						if(!empty($crmId)){
							$arra = array(
								'combineId'=>$crmCombineId,
								
								);
							$where = "id = ".$crmId;
							$this->_name="ln_client_receipt_money";
							$this->update($arra, $where);
						}
						
					}else{// កក់បន្ថែម
						
						$arrRowSale["receipt"] 				= $reciept_no;
						$arrRowSale["sale_id"] 				= $data["saleId$i"];
						$arrRowSale["land_code"] 			= $data["houseId$i"];
						$arrRowSale["sold_price"] 			= $data["priceSold$i"];
						$arrRowSale["paid_before"] 			= $data["allPaidBefore$i"];
						$arrRowSale["deposit"] 				= $data["principleAmount$i"]+$data["allPaidBefore$i"];
						$arrRowSale["new_deposit"] 			= $data["principleAmount$i"];
						$arrRowSale["balance"] 				= $data["balanceAmount$i"];
						
						$arrRowSale["schedule_opt"] 		= $data["scheduleOpt$i"];
						$crmId = $dbRePayment->addPaymenttoSale($arrRowSale);
						
						if(!empty($crmId)){
							$arra = array(
								'combineId'=>$crmCombineId,
								);
							$where = "id = ".$crmId;
							$this->_name="ln_client_receipt_money";
							$this->update($arra, $where);
						}
					}
				}
			}
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
	
	public function editPaymentCombine($data){
    	$db = $this->getAdapter();
    	$userId = $this->getUserId();
    	try{
			$crmCombineId =$data['id'];
			$arr_client_pay = array(
    			'datePayment'			=>	$data['datePayment'],
    			'paymentMethod'			=>	$data["paymentMethod"],
    			'bankId'				=>	$data["bankId"],
    			'cheque'				=>	$data["cheque"],
    			'note' 					=>  $data["note"],
    			'userId' 				=>  $userId,
				'modifyDate'	  	=> date("Y-m-d H:i:s"),
    		);
    		
			$this->_name = "ln_client_receipt_money_combine";
			$where = 'id = '.$crmCombineId;
			$this->update($arr_client_pay, $where);
			
			$rsReciept = $this->getReceiptInCombinePaymentRS($crmCombineId);
			if($crmCombineId>0){
				$this->_name = "ln_client_receipt_money";
				$arrReciept = array(
					'date_pay'				=>	$data['datePayment'],
					'date_input'			=>	$data['datePayment'],
					'payment_method'		=>	$data["paymentMethod"],
					'bank_id'				=>	$data["bankId"],
					'cheque'				=>	$data["cheque"],
					'user_id' 				=>  $userId,
				);
				$whereReciept = 'combineId = '.$crmCombineId;
				$this->update($arrReciept, $whereReciept);
			}
			
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
	
	public function getClientSaleForPayment($_data){
    	$db=$this->getAdapter();
		$_data['clientId'] = empty($_data['clientId']) ? 0 : $_data['clientId'];
    	$sql="
			SELECT 
				s.`id`
				,s.`client_id`
				,s.`house_id` AS houseId
				,s.`interest_rate` AS interestRate
				,s.`payment_id` AS scheduleOpt
				,s.`staff_id` AS agencyId
				,CONCAT(COALESCE(p.land_address,''),' ',COALESCE(p.street,'')) AS landAddress
				,s.`price_sold`
				,(COALESCE((SELECT SUM(COALESCE(crm.total_principal_permonthpaid,0)+COALESCE(crm.extra_payment,0)) FROM `ln_client_receipt_money` AS crm WHERE crm.sale_id=s.id AND s.`status`=1 LIMIT 1),0) + (COALESCE((SELECT COALESCE(SUM(crd.total_amount),0) FROM `ln_credit` AS crd WHERE crd.status=1 AND crd.sale_id = s.id LIMIT 1),0)) ) AS allPaidBefore
				,COALESCE((SELECT `sch`.`no_installment` FROM `ln_saleschedule` AS sch WHERE sch.sale_id = s.id AND  `sch`.`status` = 1 AND `sch`.`is_completed` = 0 ORDER BY sch.date_payment ASC LIMIT 1),0) AS paidTimes
				,COALESCE((SELECT `sch`.`id` FROM `ln_saleschedule` AS sch WHERE sch.sale_id = s.id AND  `sch`.`status` = 1 AND `sch`.`is_completed` = 0 ORDER BY sch.date_payment ASC LIMIT 1),0) AS scheduleId
				,CASE 
					WHEN COALESCE((SELECT `sch`.`id` FROM `ln_saleschedule` AS sch WHERE sch.sale_id = s.id AND  `sch`.`status` = 1 AND `sch`.`is_completed` = 0 ORDER BY sch.date_payment ASC LIMIT 1),0) > 0 
					 THEN COALESCE((SELECT `sch`.`principal_permonthafter` FROM `ln_saleschedule` AS sch WHERE sch.sale_id = s.id AND  `sch`.`status` = 1 AND `sch`.`is_completed` = 0 ORDER BY sch.date_payment ASC LIMIT 1),0) 
					ELSE s.`price_sold` - (COALESCE((SELECT SUM(COALESCE(crm.total_principal_permonthpaid,0)+COALESCE(crm.extra_payment,0)) FROM `ln_client_receipt_money` AS crm WHERE crm.sale_id=s.id AND s.`status`=1 LIMIT 1),0) + (COALESCE((SELECT COALESCE(SUM(crd.total_amount),0) FROM `ln_credit` AS crd WHERE crd.status=1 AND crd.sale_id = s.id LIMIT 1),0)) )
				END AS principleAmount
				,CASE 
					WHEN COALESCE((SELECT `sch`.`id` FROM `ln_saleschedule` AS sch WHERE sch.sale_id = s.id AND  `sch`.`status` = 1 AND `sch`.`is_completed` = 0 ORDER BY sch.date_payment ASC LIMIT 1),0) > 0 
					 THEN COALESCE((SELECT `sch`.`total_interest_after` FROM `ln_saleschedule` AS sch WHERE sch.sale_id = s.id AND  `sch`.`status` = 1 AND `sch`.`is_completed` = 0 ORDER BY sch.date_payment ASC LIMIT 1),0) 
					ELSE 0
				END AS interestAmount
				,CASE 
					WHEN COALESCE((SELECT `sch`.`id` FROM `ln_saleschedule` AS sch WHERE sch.sale_id = s.id AND  `sch`.`status` = 1 AND `sch`.`is_completed` = 0 ORDER BY sch.date_payment ASC LIMIT 1),0) > 0 
					 THEN COALESCE((SELECT `sch`.`date_payment` FROM `ln_saleschedule` AS sch WHERE sch.sale_id = s.id AND  `sch`.`status` = 1 AND `sch`.`is_completed` = 0 ORDER BY sch.date_payment ASC LIMIT 1),0) 
					ELSE DATE_FORMAT(CURRENT_TIMESTAMP,'%Y-%m-%d')
				END AS datePayment
		";
    	$sql.=" FROM 
					`ln_sale` AS s
					JOIN `ln_properties` AS p ON p.id = s.house_id
				WHERE s.status = 1
					AND s.is_cancel = 0
					AND s.is_completed = 0
					AND s.`price_sold` > (COALESCE((SELECT SUM(COALESCE(crm.total_principal_permonthpaid,0)+COALESCE(crm.extra_payment,0)) FROM `ln_client_receipt_money` AS crm WHERE crm.sale_id=s.id AND s.`status`=1 LIMIT 1),0) + (COALESCE((SELECT COALESCE(SUM(crd.total_amount),0) FROM `ln_credit` AS crd WHERE crd.status=1 AND crd.sale_id = s.id LIMIT 1),0)) )

			";
    	$sql.="  AND s.`client_id` = ".$_data['clientId'];
		if(!empty($_data['branchId'])){
			$sql.="  AND s.`branch_id` = ".$_data['branchId'];
		}
    	$sql.="  ORDER BY p.land_address ASC ";
    	return $db->fetchAll($sql);
    }
	
	
	function getSaleForPaymentRecord($_data){
		$rs = $this->getClientSaleForPayment($_data);
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$str="";
		$no = $_data['keyindex'];
    	$identity='';
		$allSalePaid=0;
		$allSaleBalance=0;
		if(!empty($rs)){
			foreach($rs as $key => $row){
				if (empty($identity)){
    				$identity=$no;
    			}else{$identity=$identity.",".$no;
    			}
				
				$totalPayment = $row['principleAmount']+$row['interestAmount'];
				
				$allPaid =  $row['allPaidBefore']+$row['principleAmount'];
				$balance = $row['price_sold']-$allPaid;
				$currentBalance = $row['price_sold']-$row['allPaidBefore'];
				
				$allSalePaid = $allSalePaid +$allPaid;
				$allSaleBalance = $allSaleBalance +$balance;
				
				$date = new DateTime($row['datePayment']);
				$datePayment =  $date->format("d-M-Y");
				
				$readOnly = "";
				if(!empty($row['scheduleId'])){
					$readOnly = "readOnly";
				}
				
				$str.='<tr id="row'.$no.'" class="rowData">';
					$str.='
						<td data-label="'.$tr->translate("CHECKING").'" align="center" style="  padding: 0 10px;">
							<div class="custom-control custom-checkbox ">
								<input type="checkbox" class="checkboxSystem custom-control-input checkbox" checked  OnChange="CheckAllTotal('.$no.')" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]" >
								<label class="custom-control-label" for="mfdid_'.$no.'">
								</label>
							</div>
						</td>
						<td data-label="'.$tr->translate("NUM").'" >'.($key+1).'</td>
						<td data-label="'.$tr->translate("PROPERTY_CODE").'" ><strong>'.$row['landAddress'].'</strong></td>
						<td data-label="'.$tr->translate("DATE_PAYMENT").'" >&nbsp;
							<label id="billingdatelabel'.$no.'">'.$datePayment.'</label>
							<input type="hidden" dojoType="dijit.form.TextBox" name="saleId'.$no.'" id="saleId'.$no.'" value="'.$row['id'].'" >
							<input type="hidden" dojoType="dijit.form.TextBox" name="scheduleId'.$no.'" id="scheduleId'.$no.'" value="'.$row['scheduleId'].'" >
							<input type="hidden" dojoType="dijit.form.TextBox" name="datePayment'.$no.'" id="datePayment'.$no.'" value="'.$row['datePayment'].'" >
							<input type="hidden" dojoType="dijit.form.TextBox" name="houseId'.$no.'" id="houseId'.$no.'" value="'.$row['houseId'].'" >
							<input type="hidden" dojoType="dijit.form.TextBox" name="interestRate'.$no.'" id="interestRate'.$no.'" value="'.$row['interestRate'].'" >
							<input type="hidden" dojoType="dijit.form.TextBox" name="scheduleOpt'.$no.'" id="scheduleOpt'.$no.'" value="'.$row['scheduleOpt'].'" >
							<input type="hidden" dojoType="dijit.form.TextBox" name="paidTimes'.$no.'" id="paidTimes'.$no.'" value="'.$row['paidTimes'].'" >
							<input type="hidden" dojoType="dijit.form.TextBox" name="priceSold'.$no.'" id="priceSold'.$no.'" value="'.$row['price_sold'].'" >
							<input type="hidden" dojoType="dijit.form.TextBox" name="allPaidBefore'.$no.'" id="allPaidBefore'.$no.'" value="'.$row['allPaidBefore'].'" >
							<input type="hidden" dojoType="dijit.form.TextBox" name="currentBalance'.$no.'" id="currentBalance'.$no.'" value="'.$currentBalance.'" >
							<input type="hidden" dojoType="dijit.form.TextBox" name="agencyId'.$no.'" id="agencyId'.$no.'" value="'.$row['agencyId'].'" >
						</td>
						<td data-label="'.$tr->translate("PRICE_SOLD").'"><label class="price">$ '.number_format($row['price_sold'],2).'</label></td>
						<td data-label="'.$tr->translate("OS_AMOUNT").'"><input type="text" '.$readOnly.' class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateRowAmount('.$no.');" name="principleAmount'.$no.'" id="principleAmount'.$no.'" value="'.$row["principleAmount"].'" style="text-align: center;" ></td>
						<td data-label="'.$tr->translate("INTEREST").'"><input type="text" '.$readOnly.' class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateRowAmount('.$no.');" name="interestAmount'.$no.'" id="interestAmount'.$no.'" value="'.$row["interestAmount"].'" style="text-align: center;" ></td>
						<td data-label="'.$tr->translate("TOTAL_AMOUNT").'"><input type="text" readOnly class="fullside" dojoType="dijit.form.NumberTextBox" required="required"  name="totalPayment'.$no.'" id="totalPayment'.$no.'" value="'.$totalPayment.'" style="text-align: center;" ></td>
						<td data-label="'.$tr->translate("BALANCE").'"><input type="text" readOnly class="fullside" dojoType="dijit.form.NumberTextBox" name="balanceAmount'.$no.'" id="balanceAmount'.$no.'" value="'.$balance.'" style="text-align: center;" ></td>
					';
				$str.='</tr>';
				$no++;
			}
		}else{
    		$no++;
    	}
		
		$array = array(
			'stringrow'=>$str
			,'keyindex'=>$no
			,'identity'=>$identity
			,'allSalePaid'=>$allSalePaid
			,'allSaleBalance'=>$allSaleBalance
		);
    	return $array;
		
	}
	
	public function getReceiptInCombinePayment($combieId){
		$db = $this->getAdapter();
		if($combieId>0){
			$sql="
				SELECT 
					crm.`id` AS paymentId
					,crm.`sale_id`
					,crm.`is_closed`
					,cmb.`status`
					,(SELECT crm1.id FROM `ln_client_receipt_money` AS crm1 WHERE crm1.`sale_id` = crm.`sale_id` AND crm1.total_payment>0 ORDER BY crm1.id DESC LIMIT 1) AS lastPaymentId
				FROM 
					`ln_client_receipt_money` AS crm,
					`ln_client_receipt_money_combine` AS cmb
				WHERE 
					cmb.id = crm.`combineId` 
					AND crm.`combineId` = $combieId 
				";
			$sql.=" ORDER BY crm.`is_closed` DESC ";
			return $db->fetchAll($sql);
		}else{
			return null;
		}
		
	}
	public function checkReceiptInCombinePayment($combieId){
		
		$rs = $this->getReceiptInCombinePayment($combieId);
		$canVoid=true;
		if(!empty($rs)) {
			foreach($rs as $row){
				if($row["is_closed"]=="1"){
					$canVoid=false;
					break;
				}else if($row["paymentId"] !=$row["lastPaymentId"] ){
					$canVoid=false;
					break;
				}else if($row["status"] == 0 ){
					$canVoid=false;
					break;
				}
			}
		}
		return $canVoid;
	}
	
	 function voidCombineReciept($data){
		 $db = $this->getAdapter();
		 $combieId = $data['id'];
		 $rs = $this->getReceiptInCombinePayment($combieId);
		 if(!empty($rs)) {
			 $arr = array(
					'totalInterest'		=>0,
					'totalPrinciple'	=>0,
					'totalPayment'		=>0,
					'totalAllPaid'		=>0,
					'totalBalance'		=>0,
					'note'				=>"មោឃៈ",
					'status'		=>0,
					'void_reason'	=>$data['reason'],
					'void_by'		=>$this->getUserId(),
					'void_date'		=>date("Y-m-d H:i:s"),
			);
			$where=" id = ".$combieId;
			$this->_name="ln_client_receipt_money_combine";
			$this->update($arr, $where);
			
			$dbPayment = new Loan_Model_DbTable_DbLoanILPayment();	
			foreach($rs as $row){
				$paymentId = empty($row['paymentId'])?0:$row['paymentId'];	
				$rrCm = $dbPayment->checkifExistingDelete($paymentId);
				if(!empty($rrCm)){
					$dbPayment->deleteReceipt($paymentId);
					$dbPayment->recordhistory($paymentId);
					$data["id"] = $paymentId;
					$dbPayment->setVoidReceiptReason($data);
				}
			}
		}
	 }
	 
	function getCombinePaymentInfoById($id){
		$db = $this->getAdapter();
		$sql="SELECT 
				  cmb.*
				FROM
				  `ln_client_receipt_money_combine` AS cmb 
				WHERE cmb.id = $id
				 ";
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("cmb.branchId");
		$sql.=" LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	
	
	public function getReceiptInCombinePaymentRS($combieId){
		$db = $this->getAdapter();
		if($combieId>0){
			$sql="
				SELECT 
					crm.*
					,(SELECT project_name FROM `ln_project` WHERE br_id=crm.branch_id LIMIT 1) AS projectName
					,(SELECT v.`name_kh` FROM `ln_view` AS v WHERE ((v.`key_code` = `crm`.`payment_method`) AND (v.`type` = 2))LIMIT 1) AS `paymentMethod`
					,c.`name_kh` AS clientName
					,c.`sex`
					,(SELECT v.`name_kh` FROM `ln_view` AS v WHERE ((v.`key_code` = c.`sex`) AND (v.`type` = 11))LIMIT 1) AS `sexTitle`
					
					,c.`phone`
					,c.`client_number`  AS clientNumber
					,s.`price_sold`  AS priceSold
					
					,p.land_address AS landAddress
					,p.street
					,p.land_code AS landCode
					,(SELECT pt.type_nameen FROM `ln_properties_type` AS pt WHERE pt.id = p.property_type LIMIT 1)AS propertyType
					,(SELECT CONCAT(last_name,' ',first_name) FROM `rms_users` WHERE rms_users.id=crm.`user_id` LIMIT 1) AS byUser
				
					
				FROM 
					`ln_client_receipt_money` AS crm
					JOIN `ln_sale` AS s ON s.id = crm.`sale_id`
					LEFT JOIN `ln_properties` AS p ON p.id = s.house_id 
					LEFT JOIN ln_client AS c ON c.client_id = crm.client_id
				WHERE  crm.`combineId` = $combieId 
				";
			$sql.=" ORDER BY crm.`id` ASC ";
			return $db->fetchAll($sql);
		}else{
			return null;
		}
		
	}
}

