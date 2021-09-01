<?php
class Incexp_Model_DbTable_DbExpensePayment extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_for_section';
    
    public function getUserId(){
		$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
    
    function getAllSuplier(){
    	$db=$this->getAdapter();
    	$sql="SELECT id,name as name FROM ln_supplier WHERE STATUS=1 ORDER BY id DESC";
    	return $db->fetchAll($sql);
    }
    
    function getPuchasePaymentCode(){
    	$db = $this->getAdapter();
    	$sql ="SELECT id AS number FROM `rms_expense_payment` ORDER BY id DESC LIMIT 1 ";
    	$acc_no = $db->fetchOne($sql);
    
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	$pre="RE";
    	for($i = $acc_no;$i<6;$i++){
    		$pre.='0';
    	}
    	$last = '';
    	return $pre.$new_acc_no.$last;
    }
    
    
    function getAllPurchasePayment($search){
    	$db = $this->getAdapter();
    	try{
    		$sql="
    			SELECT
					pp.id,
					(SELECT b.project_name FROM `ln_project` AS b  WHERE b.br_id = pp.branch_id LIMIT 1) AS branch_name,
					pp.receipt_no,
					(SELECT s.name FROM `ln_supplier` AS s WHERE s.id = pp.supplier_id LIMIT 1 ) AS supplier_name,
					pp.balance,
					pp.total_paid,pp.total_due,
					(SELECT v.name_kh FROM `ln_view` AS v WHERE v.key_code = pp.paid_by AND v.type=26 LIMIT 1) AS paid_by,
					pp.date_payment,
					pp.status
				FROM `rms_expense_payment` AS pp WHERE 1
    		";
    		$from_date =(empty($search['start_date']))? '1': " pp.date_payment >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " pp.date_payment <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
    		$sql.= " AND  ".$from_date." AND ".$to_date;
    		$where="";
    		if(!empty($search['adv_search'])){
    			$s_where=array();
    			$s_search=addslashes(trim($search['adv_search']));
    			$s_where[]= " pp.receipt_no LIKE '%{$s_search}%'";
    			$s_where[]= " pp.balance LIKE '%{$s_search}%'";
    			$s_where[]= " pp.total_paid LIKE '%{$s_search}%'";
    			$s_where[]= " pp.total_due LIKE '%{$s_search}%'";
    			$s_where[]= " (SELECT s.name FROM `ln_supplier` AS s WHERE s.id = pp.supplier_id LIMIT 1 ) LIKE '%{$s_search}%'";
    			 
    			$where.=' AND ('.implode(' OR ', $s_where).')';
    		}
    		if(!empty($search['supplier_search'])){
    			$where.=" AND pp.supplier_id=".$search['supplier_search'];
    		}
    		if(!empty($search['status_search'])){
    			$where.=" AND pp.status=".$search['status_search'];
    		}
    		if(!empty($search['branch_search'])){
    			$where.=" AND pp.branch_id=".$search['branch_search'];
    		}
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$where.=$dbp->getAccessPermission('pp.branch_id');
    		$order=" ORDER BY pp.id DESC";
    		
    		return $db->fetchAll($sql.$where.$order);
    		
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function getPurchaseBySupplier($data){
    	$db = $this->getAdapter();
		
    	$supplier_id = empty($data['supplier_id'])?0:$data['supplier_id'];
    	$branch_id = $data['branch_id'];
    	$sql="SELECT * FROM `ln_expense` AS p  WHERE  p.status=1 AND p.is_paid = 0 AND p.branch_id =$branch_id ";
    	if(!empty($data['supplier_id'])){
			$sql.=" AND p.supplier_id =$supplier_id ";
		}
		
    	$from_date =(empty($data['start_date']))? '1': " p.date >= '".date("Y-m-d",strtotime($data['start_date']))." 00:00:00'";
    	$to_date = (empty($data['end_date']))? '1': " p.date <= '".date("Y-m-d",strtotime($data['end_date']))." 23:59:59'";
    	$sql.= " AND  ".$from_date." AND ".$to_date;
    	if (!empty($data['bypuchase_no'])){
    		//$sql.=" AND p.invoice ='".addslashes(trim($data['bypuchase_no']))."'";
			
			$s_where = array();
			$s_search = trim(addslashes($data['bypuchase_no']));
			$s_where[] = " p.title LIKE '%{$s_search}%'";
			$s_where[] = " p.invoice LIKE '%{$s_search}%'";
			$s_where[] = " p.other_invoice LIKE '%{$s_search}%'";
			$sql .=' AND ('.implode(' OR ',$s_where).')';
				
    	}
    	$rs = $db->fetchAll($sql);
    	
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
    			<tr id="row'.$no.'" style="background: #fff; border: solid 1px #bac;">
    				<td align="center" style="  padding: 0 10px;"><input  OnChange="CheckAllTotal('.$no.')" style=" vertical-align: top; height: initial;" type="checkbox" class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]"/></td>
	    			<td style="text-align: center;vertical-align: middle; ">'.($key+1).'</td>
	    			<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; min-width: 100px;">&nbsp;
	    				<label id="billingdatelabel'.$no.'">'.date("d-M-Y",strtotime($row['date'])).'</label>
	    				<input type="hidden" dojoType="dijit.form.TextBox" name="purchase_id'.$no.'" id="purchase_id'.$no.'" value="'.$row['id'].'" >
    				</td>
					<td style="vertical-align: top; font-size:11px; text-align: left; border-left:solid 1px #ccc; min-width: 100px;">
	    				<label id="titleExpense'.$no.'">'.$row['title'].'</label>
    				</td>
    			<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; min-width: 100px;">
    			<label id="invoicelabel'.$no.'">'.$row['other_invoice'].' ('.$row['invoice'].')</label>
    			<input type="hidden" dojoType="dijit.form.TextBox" name="invoice_hidden'.$no.'" id="invoice_hidden'.$no.'" value="'.$row['invoice'].'" >
    			</td>
    			<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; min-width: 100px;">&nbsp;
    			<label id="origtotallabel'.$no.'">'.number_format($row['total_amount'],2).'</label>
    			</td>
    			<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc;  min-width: 100px; ">&nbsp;
    			<label id="duelabel'.$no.'">'.number_format($row['total_amount_after'],2).'</label>
    			<input type="hidden" dojoType="dijit.form.TextBox" name="due_val'.$no.'" id="due_val'.$no.'" value="'.$row['total_amount_after'].'" >
    			</td>
    			<td><input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateamount('.$no.');" name="discount'.$no.'" id="discount'.$no.'" value="0" style="text-align: center;" >
    			<input type="hidden" dojoType="dijit.form.TextBox" name="discount_amount'.$no.'" id="discount_amount'.$no.'" value="" >
    			</td>
    			<td><input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateamount('.$no.');" name="payment_amount'.$no.'" id="payment_amount'.$no.'" value="0" style="text-align: center;" ></td>
    			<td><input type="text" class="fullside" readonly="readonly" dojoType="dijit.form.NumberTextBox" required="required" name="remain'.$no.'" id="remain'.$no.'" value="'.$row['total_amount_after'].'" style="text-align: center;" ></td>
    			</tr>
    			';$no++;
    		}
    	}else{
    		$no++;
    	}
    	$all_balance =0;
    	$userbalace = $this->getCurrentBalanceBySupplier($data);
    	if (!empty($userbalace)){
    		$all_balance = $userbalace;
    	}
    	//<td><input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateamount('.$no.');" name="credit'.$no.'" id="credit'.$no.'" value="0" style="text-align: center;" ></td>
    	$array = array('stringrow'=>$string,'keyindex'=>$no,'identity'=>$identity,'all_balance'=>$all_balance);
    	return $array;
    }
    function getPurchasePaymentDetail($payment_id){
    	$db = $this->getAdapter();
    	$sql="SELECT pd.*,
			(SELECT p.invoice FROM `ln_expense` AS p WHERE p.id = pd.purchase_id LIMIT 1) AS supplier_no
			 FROM `rms_expense_payment_detail` AS pd WHERE pd.payment_id =$payment_id ";
    	return $db->fetchAll($sql);
    }
    function getPaymentReceiptDetailByPaymentIdAndPurchaseId($payment_id,$purchase_id){
    	$db = $this->getAdapter();
    	$sql="SELECT pd.*,
    	(SELECT p.title FROM `ln_expense` AS p WHERE p.id = pd.purchase_id LIMIT 1) AS title,
    	(SELECT p.invoice FROM `ln_expense` AS p WHERE p.id = pd.purchase_id LIMIT 1) AS supplier_no,
		(SELECT p.other_invoice FROM `ln_expense` AS p WHERE p.id = pd.purchase_id LIMIT 1) AS other_invoice,
    	(SELECT p.total_amount_after FROM `ln_expense` AS p WHERE p.id = pd.purchase_id LIMIT 1) AS total_amount_after,
    	(SELECT p.total_amount FROM `ln_expense` AS p WHERE p.id = pd.purchase_id LIMIT 1) AS total_amount,
    	(SELECT p.date FROM `ln_expense` AS p WHERE p.id = pd.purchase_id LIMIT 1) AS date
    	FROM `rms_expense_payment_detail` AS pd WHERE pd.payment_id =$payment_id AND pd.purchase_id =$purchase_id LIMIT 1 ";
    	return $db->fetchRow($sql);
    }
    
    function getSumPaymentReceiptDetailByPurchaseId($purchase_id,$paymentdetailid){
    	$db = $this->getAdapter();
    	$sql="SELECT SUM(pd.`payment_amount`) AS tolalpayamount FROM `rms_expense_payment_detail` AS pd WHERE pd.`purchase_id`=$purchase_id AND pd.`id` != $paymentdetailid AND (SELECT p.`status`=1 FROM `rms_expense_payment` AS p WHERE p.`id` = pd.`payment_id` LIMIT 1) =1";
    	return $db->fetchRow($sql);
    }
    function getPurchaseBySupplierEdit($data){
    	
    	$rows = $this->getPurchasePaymentDetail($data['payment_id']);
    	$listSaleidpaid ='';
    	if (!empty($rows)) foreach ($rows as $paymentdetail){
    		if (empty($listSaleidpaid)){
    			$listSaleidpaid=$paymentdetail['purchase_id'];
    		}else{$listSaleidpaid=$listSaleidpaid.",".$paymentdetail['purchase_id'];
    		}
    	}
    	
    	$db = $this->getAdapter();
    	$supplier_id = empty($data['supplier_id'])?0:$data['supplier_id'];
    	$branch_id = $data['branch_id'];
    	$sql="SELECT * FROM `ln_expense` AS p  WHERE  p.status=1 AND p.is_paid = 0 AND p.branch_id =$branch_id ";
		if(!empty($data['supplier_id'])){
			$sql.=" AND p.supplier_id =$supplier_id ";
		}
    	$from_date =(empty($data['start_date']))? '1': " p.date >= '".date("Y-m-d",strtotime($data['start_date']))." 00:00:00'";
    	$to_date = (empty($data['end_date']))? '1': " p.date <= '".date("Y-m-d",strtotime($data['end_date']))." 23:59:59'";
    	//$sql.= " AND  ".$from_date." AND ".$to_date;
    	if (!empty($data['bypuchase_no'])){
    		//$sql.=" AND p.supplier_no ='".addslashes(trim($data['bypuchase_no']))."'";
			$s_where = array();
			$s_search = trim(addslashes($data['bypuchase_no']));
			$s_where[] = " p.title LIKE '%{$s_search}%'";
			$s_where[] = " p.invoice LIKE '%{$s_search}%'";
			$s_where[] = " p.other_invoice LIKE '%{$s_search}%'";
			$sql .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	
    	if (!empty($listSaleidpaid)){
    		$sql.=" OR p.`id` IN ($listSaleidpaid) ";
    	}
    	
    	$rs = $db->fetchAll($sql);
    	 
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
    			
    			$rowpaymentdetail = $this->getPaymentReceiptDetailByPaymentIdAndPurchaseId($data['payment_id'], $row['id']);
    			if (!empty($rowpaymentdetail)){
    				$discoun_amont =  ($rowpaymentdetail['due_amount']*$rowpaymentdetail['discount'])/100;
    				$duevalu=$rowpaymentdetail['due_amount'];
    				$paymenttailbybilling = $this->getSumPaymentReceiptDetailByPurchaseId($rowpaymentdetail['purchase_id'], $rowpaymentdetail['id']);// get other pay amount on this Purchase on other payment number
    				if (!empty($paymenttailbybilling)){
    					$duevalu = $rowpaymentdetail['total_amount']-$paymenttailbybilling['tolalpayamount'];
    				}
    				if (empty($identityedit)){
    					$identityedit=$no;
    				}else{$identityedit=$identityedit.",".$no;
    				}
    				$string.='
    				<tr id="row'.$no.'" style="background: #fff; border: solid 1px #bac;">
	    				<td align="center" style="  padding: 0 10px;"><input checked="checked" OnChange="CheckAllTotal('.$no.')" style=" vertical-align: top; height: initial;" type="checkbox" class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]"/></td>
	    				<td style="text-align: center;vertical-align: middle; ">'.($key+1).'</td>
	    				<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; min-width: 100px;">&nbsp;
		    				<label id="billingdatelabel'.$no.'">'.date("d-M-Y",strtotime($rowpaymentdetail['date'])).'</label>
		    				<input type="hidden" dojoType="dijit.form.TextBox" name="purchase_id'.$no.'" id="purchase_id'.$no.'" value="'.$rowpaymentdetail['purchase_id'].'" >
	    				</td>
						<td style="vertical-align: top; font-size:11px; text-align: left; border-left:solid 1px #ccc; min-width: 100px;">
							<label id="titleExpense'.$no.'">'.$rowpaymentdetail['title'].'</label>
						</td>
	    				<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; min-width: 100px;">
		    				<label id="invoicelabel'.$no.'">'.$rowpaymentdetail['other_invoice'].' ('.$rowpaymentdetail['supplier_no'].')</label>
		    				<input type="hidden" dojoType="dijit.form.TextBox" name="invoice_hidden'.$no.'" id="invoice_hidden'.$no.'" value="'.$rowpaymentdetail['supplier_no'].'" >
	    				</td>
	    				<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; min-width: 100px;">&nbsp;
	    					<label id="origtotallabel'.$no.'">'.number_format($rowpaymentdetail['total_amount'],2).'</label>
	    				</td>
	    				<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc;  min-width: 100px; ">&nbsp;
		    				<label id="duelabel'.$no.'">'.number_format($rowpaymentdetail['total_amount_after'],2).'</label>
		    				<input type="hidden" dojoType="dijit.form.TextBox" name="due_val'.$no.'" id="due_val'.$no.'" value="'.$duevalu.'" >
	    				</td>
	    				<td>
		    				<input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateamount('.$no.');" name="discount'.$no.'" id="discount'.$no.'" value="'.$rowpaymentdetail['discount'].'" style="text-align: center;" >
		    				<input type="hidden" dojoType="dijit.form.TextBox" name="discount_amount'.$no.'" id="discount_amount'.$no.'" value="'.$discoun_amont.'" >
	    					<input type="hidden" dojoType="dijit.form.TextBox" name="detailid'.$no.'" id="detailid'.$no.'" value="'.$rowpaymentdetail['id'].'" >
	    				</td>
	    				<td><input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateamount('.$no.');" name="payment_amount'.$no.'" id="payment_amount'.$no.'" value="'.$rowpaymentdetail['payment_amount'].'" style="text-align: center;" ></td>
	    				<td><input type="text" class="fullside" readonly="readonly" dojoType="dijit.form.NumberTextBox" required="required" name="remain'.$no.'" id="remain'.$no.'" value="'.$rowpaymentdetail['total_amount_after'].'" style="text-align: center;" ></td>
    				</tr>';
    			}else{
	    			$string.='
						<tr id="row'.$no.'" style="background: #fff; border: solid 1px #bac;">
							<td align="center" style="  padding: 0 10px;"><input  OnChange="CheckAllTotal('.$no.')" style=" vertical-align: top; height: initial;" type="checkbox" class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]"/></td>
							<td style="text-align: center;vertical-align: middle; ">'.($key+1).'</td>
							<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; min-width: 100px;">&nbsp;
								<label id="billingdatelabel'.$no.'">'.date("d-M-Y",strtotime($row['date'])).'</label>
								<input type="hidden" dojoType="dijit.form.TextBox" name="purchase_id'.$no.'" id="purchase_id'.$no.'" value="'.$row['id'].'" >
							</td>
							<td style="vertical-align: top; font-size:11px; text-align: left; border-left:solid 1px #ccc; min-width: 100px;">
								<label id="titleExpense'.$no.'">'.$row['title'].'</label>
							</td>
						<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; min-width: 100px;">
						<label id="invoicelabel'.$no.'">'.$row['other_invoice'].' ('.$row['invoice'].')</label>
						<input type="hidden" dojoType="dijit.form.TextBox" name="invoice_hidden'.$no.'" id="invoice_hidden'.$no.'" value="'.$row['invoice'].'" >
						</td>
						<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; min-width: 100px;">&nbsp;
						<label id="origtotallabel'.$no.'">'.number_format($row['total_amount'],2).'</label>
						</td>
						<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc;  min-width: 100px; ">&nbsp;
						<label id="duelabel'.$no.'">'.number_format($row['total_amount_after'],2).'</label>
						<input type="hidden" dojoType="dijit.form.TextBox" name="due_val'.$no.'" id="due_val'.$no.'" value="'.$row['total_amount_after'].'" >
						</td>
						<td><input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateamount('.$no.');" name="discount'.$no.'" id="discount'.$no.'" value="0" style="text-align: center;" >
						<input type="hidden" dojoType="dijit.form.TextBox" name="discount_amount'.$no.'" id="discount_amount'.$no.'" value="" >
						</td>
						<td><input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateamount('.$no.');" name="payment_amount'.$no.'" id="payment_amount'.$no.'" value="0" style="text-align: center;" ></td>
						<td><input type="text" class="fullside" readonly="readonly" dojoType="dijit.form.NumberTextBox" required="required" name="remain'.$no.'" id="remain'.$no.'" value="'.$row['total_amount_after'].'" style="text-align: center;" ></td>
						</tr>
						';
    			}$no++;
    		}
    	}else{
    		$no++;
    	}
    	$all_balance =0;
    	$userbalace = $this->getCurrentBalanceBySupplier($data);
    	if (!empty($userbalace)){
    		$all_balance = $userbalace;
    	}
    	$array = array('sql'=>$sql,'stringrow'=>$string,'keyindex'=>$no,'identity'=>$identity,'identitycheck'=>$identityedit,'all_balance'=>$all_balance);
    	return $array;
    }
    function getCurrentBalanceBySupplier($data){
    	$db = $this->getAdapter();
    	$sql = "SELECT SUM(inv.`total_amount_after`) AS all_balance FROM `ln_expense` AS inv WHERE inv.`status`=1 AND inv.`is_paid`=0 AND inv.`supplier_id`=".$data['supplier_id']." AND inv.branch_id =".$data['branch_id'];
    	return $db->fetchOne($sql);
    }
    
    
    public function addPaymentReceipt($_data){
    	try{
    		$receipt_no = $this->getPuchasePaymentCode();
			
    		$_arr=array(
    				'branch_id'	  => $_data['branch_id'],
    				'receipt_no'	  => $receipt_no,
    				'supplier_id'	      => $_data['supplier_id'],
    				'balance'      => $_data['balance'],
    				'total_paid'=> $_data['total_paid'],
    				'total_discount'	  => $_data['total_discount'],
    				'total_due'      => $_data['total_due'],
    				'date_payment'      => $_data['date_payment'],
    				'paid_by'      => $_data['paid_by'],
					'cheque_no'      => $_data['cheque_no'],
					'cheque_issuer'      => $_data['cheque_issuer'],
    				'create_date'=> date("Y-m-d H:i:s"),
    				'modify_date'	  => date("Y-m-d H:i:s"),
    				'status'=> 1,
    				'user_id'  =>$this->getUserId(),
    				'note'=>$_data['note'],
    		);			
			
    		$this->_name ='rms_expense_payment';
    		$payment_id =  $this->insert($_arr);			
    		$ids = explode(',', $_data['identity']);
    		$dueafter=0;
			
    		foreach ($ids as $i){
    			$is_payment =0;
				
    			$purchase = $this->getPruchaseById($_data['purchase_id'.$i],$_data['branch_id']);
				
    			$paid = (float)$_data['payment_amount'.$i]+(float)$_data['discount_amount'.$i];
    			
    			if (!empty($purchase)){
    				$dueafter = $purchase['total_amount_after']-$paid;
    				if ($dueafter>0){
    					$is_payment=0;
    				}else{
    					$is_payment=1;
    				}
    				
    				// update Purchase Balance
    				$array=array(
    						'is_paid'=>$is_payment,
    						'total_amount_after'=>$dueafter,
    				);
    				$where="id=".$_data['purchase_id'.$i]." AND branch_id =".$_data['branch_id'];
    				$this->_name="ln_expense";
    				$this->update($array, $where);
    			}
    			
    			$arrs = array(
    					'payment_id'=>$payment_id,
    					'purchase_id'=>$_data['purchase_id'.$i],
    					'due_amount'=>$_data['due_val'.$i],
    					'discount'=>$_data['discount'.$i],
    					'payment_amount'=>$_data['payment_amount'.$i],
    					'remain'=>$_data['remain'.$i],
    			);
    			$this->_name ='rms_expense_payment_detail';
    			$this->insert($arrs);
    		}
    		return $payment_id;
    
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    
    public function updatePaymentReceipt($_data){
    	try{
//     		$receipt_no = $this->getPuchasePaymentCode();
    		$_arr=array(
    				'branch_id'	  => $_data['branch_id'],
//     				'receipt_no'	  => $receipt_no,
    				'supplier_id'	      => $_data['supplier_id'],
    				'balance'      => $_data['balance'],
    				'total_paid'=> $_data['total_paid'],
    				'total_discount'	  => $_data['total_discount'],
    				'total_due'      => $_data['total_due'],
    				'date_payment'      => $_data['date_payment'],
    				'paid_by'      => $_data['paid_by'],
					'cheque_no'      => $_data['cheque_no'],
					'cheque_issuer'      => $_data['cheque_issuer'],
//     				'create_date'=> date("Y-m-d H:i:s"),
    				'modify_date'	  => date("Y-m-d H:i:s"),
    				'status'=> 1,
    				'user_id'  =>$this->getUserId(),
    				'note'=>$_data['note'],
    		);
    		$this->_name ='rms_expense_payment';
    		$payment_id = $_data['id'];
    		$where = " id = ".$payment_id;
    		$this->update($_arr, $where);
    		
    		$row = $this->getPurchasePaymentDetail($payment_id);
    		if (!empty($row)) foreach ($row as $pay_detail){
    			$rowpaymentdetail = $this->getPaymentReceiptDetailByPaymentIdAndPurchaseId($payment_id, $pay_detail['purchase_id']);
    			
    			if (!empty($rowpaymentdetail)){
    				$purchase = $this->getPruchaseById($pay_detail['purchase_id'],$_data['branch_id']);
    					
    				$duevalu=$rowpaymentdetail['payment_amount'];
    				$paymenttailbysale = $this->getSumPaymentReceiptDetailByPurchaseId($pay_detail['purchase_id'], $pay_detail['id']);// get other pay amount on this Purchase id on other payment receipt number
    				$dueafters = $purchase['total_amount_after']+$duevalu;
//     				echo $dueafters;exit();
    				if (!empty($paymenttailbysale['tolalpayamount'])){
    					$duevalu = ($rowpaymentdetail['total_amount']-$paymenttailbysale['tolalpayamount']);
    					$dueafters =$duevalu;
    				}
    				
    				if ($dueafters>0){
    					$is_payments=0;
    				}else{
    					$is_payments=1;
    				}
    				
    				// update Purchase Balance
    				$array=array(
    						'is_paid'=>$is_payments,
    						'amount_due_after'=>$dueafters,
    						'purchase_id'=>$pay_detail['purchase_id'],
    						'branch_id'=>$_data['branch_id'],
    				);
    				$this->updatePurchase($array);
    				
    			}
    		}
    		
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
    		$this->_name="rms_expense_payment_detail";
    		$where2=" payment_id = ".$payment_id;
    		if (!empty($detailidlist)){ // check if has old payment detail  detail id
    			$where2.=" AND id NOT IN (".$detailidlist.")";
    		}
    		$this->delete($where2);
    		
    		$dueafter=0;
    		foreach ($ids as $i){
    			$is_payment =0;
    			$purchase = $this->getPruchaseById($_data['purchase_id'.$i],$_data['branch_id']);
    			$paid = $_data['payment_amount'.$i]+(float)$_data['discount_amount'.$i];
    			 
    			if (!empty($purchase)){
    				$dueafter = $purchase['total_amount_after']-$paid;
    				if ($dueafter>0){
    					$is_payment=0;
    				}else{
    					$is_payment=1;
    				}
    
    				// update Purchase Balance
    				$array=array(
    						'is_paid'=>$is_payment,
    						'total_amount_after'=>$dueafter,
    				);
    				$where="id=".$_data['purchase_id'.$i]." AND branch_id =".$_data['branch_id'];
    				$this->_name="ln_expense";
    				$this->update($array, $where);
    			}
    			 
    			if (!empty($_data['detailid'.$i])){
	    			$arrs = array(
	    					'payment_id'=>$payment_id,
	    					'purchase_id'=>$_data['purchase_id'.$i],
	    					'due_amount'=>$_data['due_val'.$i],
	    					'discount'=>$_data['discount'.$i],
	    					'payment_amount'=>$_data['payment_amount'.$i],
	    					'remain'=>$_data['remain'.$i],
	    			);
	    			$this->_name ='rms_expense_payment_detail';
	    			$where=" id= ".$_data['detailid'.$i];
	    			$this->update($arrs, $where);
    			}else{
    				$arrs = array(
    						'payment_id'=>$payment_id,
    						'purchase_id'=>$_data['purchase_id'.$i],
    						'due_amount'=>$_data['due_val'.$i],
    						'discount'=>$_data['discount'.$i],
    						'payment_amount'=>$_data['payment_amount'.$i],
    						'remain'=>$_data['remain'.$i],
    				);
    				$this->_name ='rms_expense_payment_detail';
    				$this->insert($arrs);
    			}
    		}
    		return $payment_id;
    
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function voidPaymentReceipt($id,$branch_id){
    	$_arr=array(
    			'status'	      => 0,
    			'user_id'  =>$this->getUserId(),
    			'modify_date'	  => date("Y-m-d H:i:s"),
    	);
    	$this->_name ='rms_expense_payment';
    	$where = ' id = '.$id;
    	$this->update($_arr, $where);
    	$payment_id = $id;
    	
    	$row = $this->getPurchasePaymentDetail($payment_id);
		if (!empty($row)) foreach ($row as $pay_detail){
			$rowpaymentdetail = $this->getPaymentReceiptDetailByPaymentIdAndPurchaseId($payment_id, $pay_detail['purchase_id']);
			
			if (!empty($rowpaymentdetail)){
				$purchase = $this->getPruchaseById($pay_detail['purchase_id'],$branch_id);
				$discoun_amont =  ($rowpaymentdetail['due_amount']*$rowpaymentdetail['discount'])/100;
				$duevalu=$rowpaymentdetail['payment_amount']+$discoun_amont;
				$paymenttailbysale = $this->getSumPaymentReceiptDetailByPurchaseId($pay_detail['purchase_id'], $pay_detail['id']);// get other pay amount on this Purchase id on other payment receipt number
				$dueafters = $purchase['total_amount_after']+$duevalu;
//     				echo $dueafters;exit();
				if (!empty($paymenttailbysale['tolalpayamount'])){
					$duevalu = ($rowpaymentdetail['total_amount']-$paymenttailbysale['tolalpayamount']);
					$dueafters =$duevalu;
				}
				
				if ($dueafters>0){
					$is_payments=0;
				}else{
					$is_payments=1;
				}
				
				// update Purchase Balance
				$array=array(
						'is_paid'=>$is_payments,
						'amount_due_after'=>$dueafters,
						'purchase_id'=>$pay_detail['purchase_id'],
						'branch_id'=>$branch_id,
				);
				$this->updatePurchase($array);
				
			}
		}
    }
    
    function updatePurchase($data){
    	$db=$this->getAdapter();
    	$array=array(
    			'is_paid'=>$data['is_paid'],
    			'total_amount_after'=>$data['amount_due_after'],
    	);
    	$where="id=".$data['purchase_id']." AND branch_id =".$data['branch_id'];
    	$this->_name="ln_expense";
    	$this->update($array, $where);
    }
    function getPruchaseById($id,$branch_id){
    	$db=$this->getAdapter();
    	$sql="SELECT sp.*,
		(SELECT s.name FROM ln_supplier AS s WHERE s.id=sp.supplier_id LIMIT 1)as sup_name,
		(SELECT s.supplier_code FROM ln_supplier AS s WHERE s.id=sp.supplier_id LIMIT 1) as purchase_no,
		(SELECT s.email FROM ln_supplier AS s WHERE s.id=sp.supplier_id LIMIT 1)as email,
		(SELECT s.address FROM ln_supplier AS s WHERE s.id=sp.supplier_id LIMIT 1)as address
		 FROM ln_expense AS sp
    	WHERE  sp.status=1 AND sp.id=$id AND sp.branch_id =$branch_id";
    	return $db->fetchRow($sql);
    }
    
    function getPurchasePaymentById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT pp.* FROM `rms_expense_payment` AS pp WHERE pp.id = $id ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('pp.branch_id');
    	return $db->fetchRow($sql);
    }
	
	function getAllChequeIssue(){
		$db = $this->getAdapter();
		$sql = " SELECT DISTINCT cheque_issuer as name,cheque_issuer as id FROM `rms_expense_payment` WHERE cheque_issuer!='' ORDER BY cheque_issuer ASC ";
		return $db->fetchAll($sql);
	}
    
    
    
}



