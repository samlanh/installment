<?php
class Stock_Model_DbTable_DbCutStock extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_saledetail';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    public function getCutStockode($branch_id=null){
    	$db = $this->getAdapter();
    	$pre="";
    	$sql="SELECT COUNT(id) FROM rms_cutstock WHERE 1 ";
    	if (!empty($branch_id)){
    		$sql.=" AND branch_id=".$branch_id;
    		$_dbgb = new Application_Model_DbTable_DbGlobal();
    		$pre.= $_dbgb->getPrefixCode($branch_id);//by branch
    	}
    	$sql.=" ORDER BY id DESC";
    	$stu_num = $db->fetchOne($sql);
    	$pre.='ST-';
    	$new_acc_no= (int)$stu_num+1;
    	$length = strlen((int)$new_acc_no);
    	for($i = $length;$i<4;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
    function getAllProducts($product_type=null,$option=null){
    	$db = $this->getAdapter();
    	$sql="SELECT i.id,
    	CONCAT(i.title,' (',(SELECT it.title FROM `rms_product_cate` AS it WHERE it.id = i.items_id LIMIT 1),')') AS name
    	FROM `rms_product` AS i
    	WHERE i.status =1 AND i.items_type=3   ";
    
    	if(!empty($product_type)){//check type product sale or office
    		$sql.= " AND i.product_type = ".$db->quote($product_type);
    	}
    	$dbgb = new Application_Model_DbTable_DbGlobal();
    	$branchlist = $dbgb->getAllSchoolOption();
    	if (!empty($branchlist)){
    		foreach ($branchlist as $i){
    			$s_where[] = $i['id']." IN (i.schoolOption)";
    		}
    		$sql .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	$user = $dbgb->getUserInfo();
    	$level = $user['level'];
    	if ($level!=1){
    		$sql .=' AND '.$user['schoolOption'].' IN (i.schoolOption)';
    	}
    	$sql.=" ORDER BY i.items_id ASC, i.ordering ASC";
    	$rows = $db->fetchAll($sql);
    	
    	if (!empty($option)){
    		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    		array_unshift($rows, array('id'=>-1,'name'=>$tr->translate("PLEASE_SELECT")));
    		$options = '';
    		if(!empty($rows))foreach($rows as $value){
    			$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
    		}
    		return $options;
    	}
    	return $rows;
    }
    public function getStudentProductPaymentDetail($data){
    	$db = $this->getAdapter();
    	$student_id = $data['student_id'];
    	$branch_id = $data['branch_id'];
    	
    	$sql=" SELECT 
				spd.*,
				sp.branch_id,
				sp.receipt_number,
				sp.create_date AS payment_date,
				(SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id = sp.student_id LIMIT 1) AS student_name,
				(SELECT s.stu_enname FROM `rms_student` AS s WHERE s.stu_id = sp.student_id LIMIT 1) AS stu_enname,
				(SELECT s.last_name FROM `rms_student` AS s WHERE s.stu_id = sp.student_id LIMIT 1) AS last_name,
				(SELECT s.stu_code FROM `rms_student` AS s WHERE s.stu_id = sp.student_id LIMIT 1) AS stu_code,
				(SELECT ie.title FROM `rms_product` AS ie WHERE ie.id = spd.pro_id LIMIT 1) AS items_name,
				(SELECT ie.items_type FROM `rms_product` AS ie WHERE ie.id = spd.pro_id LIMIT 1) AS items_type
				FROM `rms_student_payment` AS sp,
				`rms_saledetail` AS spd
				WHERE spd.payment_id = sp.id
				AND (SELECT ie.items_type FROM `rms_product` AS ie WHERE ie.id = spd.pro_id LIMIT 1) =3
				AND sp.is_void=0
				AND spd.qty_after >0
				AND sp.student_id=$student_id
				AND sp.branch_id=$branch_id";
	    	if (!empty($data['bypuchase_no'])){
	    		$s_search=addslashes(trim($data['bypuchase_no']));
	    		$sql.= " AND sp.receipt_number LIKE '%{$s_search}%'";
	    	}
    	$rs = $db->fetchAll($sql);
    	$stuName="";
    	$stuCode="";
    	$string='';
    	$no = $data['keyindex'];
    	$identity='';
    	$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
    	
    	$allproduct = $this->getAllProducts(1);
    	if(!empty($rs)){
    		foreach ($rs as $key => $row){
    			if (empty($identity)){
    				$identity=$no;
    			}else{$identity=$identity.",".$no;
    			}
    			$stuName=$row['stu_enname']." ".$row['last_name'];
    			$stuCode=$row['stu_code'];
    			$string.='
    			<tr id="row'.$no.'" style="background: #fff; border: solid 1px #bac;">
	    			<td align="center" style="  padding: 0 10px;"><input checked="checked"  OnChange="CheckAllTotal('.$no.')" style=" vertical-align: top; height: initial;" type="checkbox" class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]"/></td>
	    			<td style="text-align: center;vertical-align: middle; ">'.($key+1).'</td>
	    			<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; min-width: 100px;">&nbsp;
	    			<label id="billingdatelabel'.$no.'">'.date("d-M-Y",strtotime($row['payment_date'])).'</label>
	    			<input type="hidden" dojoType="dijit.form.TextBox" name="payment_id'.$no.'" id="payment_id'.$no.'" value="'.$row['payment_id'].'" >
	    			<input type="hidden" dojoType="dijit.form.TextBox" name="paymentdetail_id'.$no.'" id="paymentdetail_id'.$no.'" value="'.$row['id'].'" >
	    			<input type="hidden" dojoType="dijit.form.TextBox" name="productname'.$no.'" id="productname'.$no.'" value="'.$row['items_name'].'" >
    			</td>
    			<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; min-width: 100px;">&nbsp;<label id="invoicelabel'.$no.'" title="'.$row['receipt_number'].'" class="invoicelabel" >'.$row['receipt_number'].'</label>
    			<input type="hidden" dojoType="dijit.form.TextBox" name="receipt_number'.$no.'" id="receipt_number'.$no.'" value="'.$row['receipt_number'].'" >
    			&nbsp;</td>
    			<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; min-width: 100px;">
    				<select queryExpr="*${0}*" autoComplete="false" dojoType="dijit.form.FilteringSelect" class="fullside" name="itemdetail_id'.$no.'" id="itemdetail_id'.$no.'" >';
		    			if (!empty($allproduct)) foreach ($allproduct as $pro){ 
		    				$selected="";
		    				if ($row['pro_id']==$pro['id']){
		    					$selected='Selected="Selected"';
		    				}
		    				$string.='<option '.$selected.' value="'.$pro['id'].'">'.$pro['name'].'</option>';
		    			}
    	$string.='</select>
    			</td>
    			<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; min-width: 70px;">&nbsp;
    			<label id="origtotallabel'.$no.'">'.number_format($row['qty'],2).'</label>
    			<input type="hidden" dojoType="dijit.form.TextBox" name="qty'.$no.'" id="qty'.$no.'" value="'.$row['qty'].'" >
    			</td>
    			<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc;  min-width: 70px; ">&nbsp;
    			<label id="duelabel'.$no.'">'.number_format($row['qty_after'],2).'</label>
    			<input type="hidden" dojoType="dijit.form.TextBox" name="qty_balance'.$no.'" id="qty_balance'.$no.'" value="'.$row['qty_after'].'" >
    			</td>
    			<td style="width: 70px;"><input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateamount('.$no.');" name="qty_receive'.$no.'" id="qty_receive'.$no.'" value="'.$row['qty_after'].'" style="text-align: center;" ></td>
    			<td style="width: 70px;"><input type="text" class="fullside" readonly="readonly" dojoType="dijit.form.NumberTextBox" required="required" name="remain'.$no.'" id="remain'.$no.'" value="0" style="text-align: center;" ></td>
    			<td >
    				<input class="fullside" type="text" dojoType="dijit.form.DateTextBox" name="remide_date'.$no.'" id="remide_date'.$no.'" value="now" >
    			</td>
    			</tr>
    			';$no++;
    		}
    	}else{
    		$no++;
    		//constraints="{datePattern:'dd/MM/yyyy'}"
    	}
    	$all_balance =0;
    	$userbalace = $this->getCurrentBalanceByStudent($data);
    	if (!empty($userbalace)){
    		$all_balance = $userbalace;
    	}
    	$array = array('stuName'=>$stuName,'stucode'=>$stuCode,'stringrow'=>$string,'keyindex'=>$no,'identity'=>$identity,'all_balance'=>$all_balance);
    	return $array;
    }
    function getCurrentBalanceByStudent($data){
    	$db = $this->getAdapter();
    	
    	$student_id = $data['student_id'];
    	$branch_id = $data['branch_id'];
    	
    	$sql="SELECT 
    		SUM(spd.`qty_after`) AS all_balance
			FROM `rms_student_payment` AS sp,
			`rms_saledetail` AS spd
			WHERE spd.payment_id = sp.id
			AND (SELECT ie.items_type FROM `rms_product` AS ie WHERE ie.id = spd.pro_id LIMIT 1) =3
			AND sp.is_void=0
			AND spd.qty_after >0
			AND sp.student_id=$student_id
			AND sp.branch_id=$branch_id";
//     	$sql = "SELECT SUM(inv.`amount_due_after`) AS all_balance FROM `rms_purchase` AS inv 
//     	WHERE inv.`status`=1 AND inv.`is_paid`=0 AND inv.`sup_id`=".$data['supplier_id']." 
//     	AND inv.branch_id =".$data['branch_id'];
    	return $db->fetchOne($sql);
    }
    
    function addCutStock($_data){
    	try{
    		$itemsCode = $this->getCutStockode($_data['branch_id']);
    		$_arr=array(
    				'branch_id'	   => $_data['branch_id'],
    				'serailno'	   => $itemsCode,
    				'student_id'   => $_data['student_id'],
    				'balance'      => $_data['balance'],
    				'total_received'=> $_data['total_paid'],
    				'total_qty_due' => $_data['total_due'],
    				'received_date' => $_data['date_payment'],
    				'create_date'   => date("Y-m-d H:i:s"),
    				'modify_date'	=> date("Y-m-d H:i:s"),
    				'status'        => 1,
    				'user_id'       => $this->getUserId(),
    				'note'          => $_data['note'],
    		);
    		$this->_name ='rms_cutstock';
    		$cut_id =  $this->insert($_arr);
    		
    		$ids = explode(',', $_data['identity']);
    		$qtyfter=0;
    		foreach ($ids as $i){
    			$stupaydetail = $this->getStudentPaymentDetailById($_data['paymentdetail_id'.$i],$_data['payment_id'.$i],$_data['branch_id']);
    			$qtyreceive = $_data['qty_receive'.$i];
    			
    			if (!empty($stupaydetail)){
    				$qtyfter = $stupaydetail['qty_after']-$qtyreceive;
    				// update Purchase Balance
    				$array=array(
    						'qty_after'=>$qtyfter,
    				);
    				$where="id=".$_data['paymentdetail_id'.$i]." AND payment_id =".$_data['payment_id'.$i];
    				$this->_name="rms_saledetail";
    				$this->update($array, $where);
    			}
    			
    			$arrs = array(
    					'cutstock_id'=>$cut_id,
//     					'payment_id'=>$_data['payment_id'.$i],
    					'student_paymentdetail_id'=>$_data['paymentdetail_id'.$i],
    					'product_id'=>$_data['itemdetail_id'.$i],
    					'due_amount'=>$_data['qty_balance'.$i],
    					'qty_receive'=>$_data['qty_receive'.$i],
    					'remain'=>$_data['remain'.$i],
//     					'received_date'=>date("Y-m-d"),
    					'remide_date'=>$_data['remide_date'.$i],
    			);
    			$this->_name ='rms_cutstock_detail';
    			$this->insert($arrs);
    			
    			//cut stock
    			$dbpu = new Stock_Model_DbTable_DbPurchase();
    			$dbpu->updateStock($_data['itemdetail_id'.$i],$_data['branch_id'],-$_data['qty_receive'.$i]);
    		}
    		return $cut_id;
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    
    function getStudentPaymentDetailById($st_paydetail,$stu_payid=null,$branch_id){
    	$db=$this->getAdapter();
    	$sql="SELECT spd.* 
			FROM`rms_student_payment` AS sp,
			`rms_saledetail` AS spd
			WHERE  spd.payment_id = sp.id
			AND sp.branch_id=$branch_id
			AND spd.id = $st_paydetail";
    	if (!empty($stu_payid)){
    		$sql.=" AND spd.payment_id=$stu_payid";
    	}
    	$sql.=" LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function getAllCutStock($search){
    	$db = $this->getAdapter();
    	try{
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		
    		$sql="
    		SELECT
    		pp.id,
    		(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = pp.branch_id LIMIT 1) AS branch_name,
    		pp.serailno,
    		(SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id = pp.student_id LIMIT 1 ) AS student_name,
    		pp.balance,
    		pp.total_received,pp.total_qty_due,
    		pp.received_date ";
    		$sql.=$dbp->caseStatusShowImage("pp.status");
    		$sql.=" FROM `rms_cutstock` AS pp WHERE 1 ";
    		
    		$from_date =(empty($search['start_date']))? '1': " pp.received_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " pp.received_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
    		$sql.= " AND  ".$from_date." AND ".$to_date;
    		$where="";
    		if(!empty($search['adv_search'])){
    			$s_where=array();
    			$s_search=addslashes(trim($search['adv_search']));
    			$s_where[]= " pp.serailno LIKE '%{$s_search}%'";
    			$s_where[]= " pp.balance LIKE '%{$s_search}%'";
    			$s_where[]= " pp.total_received LIKE '%{$s_search}%'";
    			$s_where[]= " pp.total_qty_due LIKE '%{$s_search}%'";
    
    			$where.=' AND ('.implode(' OR ', $s_where).')';
    		}
    		if(!empty($search['student_id'])){
    			$where.=" AND pp.student_id=".$search['student_id'];
    		}
    		if(!empty($search['status_search'])){
    			$where.=" AND pp.status=".$search['status_search'];
    		}
    		if(!empty($search['branch_id'])){
    			$where.=" AND pp.branch_id=".$search['branch_id'];
    		}
    		
    		$where.=$dbp->getAccessPermission('pp.branch_id');
    		$order=" ORDER BY pp.id DESC";
    
    		return $db->fetchAll($sql.$where.$order);
    
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function getCutStockBYId($id){
    	$db = $this->getAdapter();
    	$sql="SELECT pp.*,
			(SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id = pp.student_id LIMIT 1 ) AS stu_khname,
			(SELECT s.stu_enname FROM `rms_student` AS s WHERE s.stu_id = pp.student_id LIMIT 1 ) AS stu_enname,
			(SELECT s.last_name FROM `rms_student` AS s WHERE s.stu_id = pp.student_id LIMIT 1 ) AS last_name,
			(SELECT s.stu_code FROM `rms_student` AS s WHERE s.stu_id = pp.student_id LIMIT 1 ) AS stu_code,
			(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE id=pp.user_id LIMIT 1) As user_name
    	FROM rms_cutstock AS pp WHERE pp.id = $id ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('pp.branch_id');
    	$sql.=" LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function getCutStockDetailBYId($cutstockid){
    	$db=$this->getAdapter();
    	$sql="SELECT ct.*,
(SELECT ie.title FROM `rms_product` AS ie WHERE ie.id = ct.product_id LIMIT 1) AS items_name,
(SELECT sp.receipt_number FROM `rms_student_payment` AS sp WHERE sp.id = (SELECT spd.payment_id FROM `rms_saledetail` AS spd WHERE spd.id = ct.student_paymentdetail_id LIMIT 1) LIMIT 1) AS receipt_number
    	 FROM `rms_cutstock_detail` AS ct
				WHERE ct.cutstock_id=$cutstockid";
    	return $db->fetchAll($sql);
    }
    function getCutstockDetailByCutstockIdAndStuDetailId($cutstockid,$stu_paymetdetail_id){
    	$db = $this->getAdapter();
    	$sql="SELECT ct.*,
    	(SELECT ie.title FROM `rms_product` AS ie WHERE ie.id = ct.product_id LIMIT 1) AS items_name,
(SELECT p.qty FROM `rms_saledetail` AS p WHERE p.id = ct.student_paymentdetail_id LIMIT 1) AS qty,
(SELECT p.qty_after FROM `rms_saledetail` AS p WHERE p.id = ct.student_paymentdetail_id LIMIT 1) AS qty_after
    	 FROM `rms_cutstock_detail` AS ct
    	WHERE ct.cutstock_id=$cutstockid AND ct.student_paymentdetail_id=$stu_paymetdetail_id ";
    	return $db->fetchRow($sql);
    }
    public function getStudentProductPaymentDetailEdit($data){
    	$db = $this->getAdapter();

    	$rows = $this->getCutStockDetailBYId($data['cutstockid']);
    	$listSaleidpaid ='';
    	if (!empty($rows)) foreach ($rows as $paymentdetail){
    		if (empty($listSaleidpaid)){
    			$listSaleidpaid=$paymentdetail['student_paymentdetail_id'];
    		}else{$listSaleidpaid=$listSaleidpaid.",".$paymentdetail['student_paymentdetail_id'];
    		}
    	}
    	
    	$student_id = $data['student_id'];
    	$branch_id = $data['branch_id'];
    	
    	$sql="
    	SELECT 
				spd.*,
				(SELECT sp.branch_id FROM `rms_student_payment` AS sp WHERE spd.payment_id = sp.id LIMIT 1) AS branch_id,
				(SELECT sp.receipt_number FROM `rms_student_payment` AS sp WHERE spd.payment_id = sp.id LIMIT 1) AS receipt_number,
	    		(SELECT sp.create_date FROM `rms_student_payment` AS sp WHERE spd.payment_id = sp.id LIMIT 1) AS payment_date,
	    		
	    		(SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id = (SELECT sp.student_id FROM `rms_student_payment` AS sp WHERE spd.payment_id = sp.id LIMIT 1) LIMIT 1) AS student_name,
	    		(SELECT s.stu_enname FROM `rms_student` AS s WHERE s.stu_id = (SELECT sp.student_id FROM `rms_student_payment` AS sp WHERE spd.payment_id = sp.id LIMIT 1) LIMIT 1) AS stu_enname,
	    		(SELECT s.last_name FROM `rms_student` AS s WHERE s.stu_id = (SELECT sp.student_id FROM `rms_student_payment` AS sp WHERE spd.payment_id = sp.id LIMIT 1) LIMIT 1) AS last_name,
	    		(SELECT s.stu_code FROM `rms_student` AS s WHERE s.stu_id = (SELECT sp.student_id FROM `rms_student_payment` AS sp WHERE spd.payment_id = sp.id LIMIT 1) LIMIT 1) AS stu_code,
	    	
	    		(SELECT ie.title FROM `rms_product` AS ie WHERE ie.id = spd.pro_id LIMIT 1) AS items_name,
	    		(SELECT ie.items_type FROM `rms_product` AS ie WHERE ie.id = spd.pro_id LIMIT 1) AS items_type
	    	
				FROM
				`rms_saledetail` AS spd
				WHERE 
				(SELECT ie.items_type FROM `rms_product` AS ie WHERE ie.id = spd.pro_id LIMIT 1) =3
		    	AND (SELECT sp.is_void FROM `rms_student_payment` AS sp WHERE spd.payment_id = sp.id LIMIT 1)=0
		    	AND spd.qty_after >0
		    	AND (SELECT sp.student_id FROM `rms_student_payment` AS sp WHERE spd.payment_id = sp.id LIMIT 1)=$student_id
	    		AND (SELECT sp.branch_id FROM `rms_student_payment` AS sp WHERE spd.payment_id = sp.id LIMIT 1)=$branch_id 
    	";
    	if (!empty($data['bypuchase_no'])){
    		$s_search=addslashes(trim($data['bypuchase_no']));
    		$sql.= " AND (SELECT sp.receipt_number FROM `rms_student_payment` AS sp WHERE spd.payment_id = sp.id LIMIT 1) LIKE '%{$s_search}%'";
    	}
    	if (!empty($listSaleidpaid)){
    		$sql.=" OR spd.`id` IN ($listSaleidpaid) ";
    	}
    	
    	$rs = $db->fetchAll($sql);
    
    	$string='';
    	$no = $data['keyindex'];
    	$identity='';
    	$identityedit='';
    	$stuName="";
    	$stuCode="";
    	$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
    	$allproduct = $this->getAllProducts(1);
    	if(!empty($rs)){
    	
	    	foreach ($rs as $key => $row){
		    	if (empty($identity)){
		    		$identity=$no;
			    }else{$identity=$identity.",".$no;
			    }
			    $stuName=$row['stu_enname']." ".$row['last_name'];
			    $stuCode=$row['stu_code'];
			    $rowpaymentdetail = $this->getCutstockDetailByCutstockIdAndStuDetailId($data['cutstockid'], $row['id']);
			    if (!empty($rowpaymentdetail)){
			    	if (empty($identityedit)){
			    		$identityedit=$no;
			    	}else{$identityedit=$identityedit.",".$no;
			    	}
			    	$duevalu=$rowpaymentdetail['due_amount'];
			    	$paymenttailbybilling = $this->getSumCutStockDetailByStuPayDetId($rowpaymentdetail['student_paymentdetail_id'], $rowpaymentdetail['id']);// get other pay amount on this Purchase on other payment number
			    	if (!empty($paymenttailbybilling)){
			    		$duevalu = $rowpaymentdetail['qty']-$paymenttailbybilling['tolalpayamount'];
			    	}
			    	
			    	$string.='
			    	<tr id="row'.$no.'" style="background: #fff; border: solid 1px #bac;">
				    	<td align="center" style="  padding: 0 10px;"><input checked="checked" OnChange="CheckAllTotal('.$no.')" style=" vertical-align: top; height: initial;" type="checkbox" class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]"/></td>
				    	<td style="text-align: center;vertical-align: middle; ">'.($key+1).'</td>
				    	<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; min-width: 100px;">&nbsp;
				    		<label id="billingdatelabel'.$no.'">'.date("d-M-Y",strtotime($row['payment_date'])).'</label>
				    	<input type="hidden" dojoType="dijit.form.TextBox" name="payment_id'.$no.'" id="payment_id'.$no.'" value="'.$row['payment_id'].'" >
				    		<input type="hidden" dojoType="dijit.form.TextBox" name="paymentdetail_id'.$no.'" id="paymentdetail_id'.$no.'" value="'.$row['id'].'" >
				    		<input type="hidden" dojoType="dijit.form.TextBox" name="productname'.$no.'" id="productname'.$no.'" value="'.$rowpaymentdetail['items_name'].'" >
				    		
				    	</td>
				    	<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; min-width: 100px;">
				    	&nbsp;<label id="invoicelabel'.$no.'" title="'.$row['receipt_number'].'" class="invoicelabel"">'.$row['receipt_number'].'</label>&nbsp;
				    	<input type="hidden" dojoType="dijit.form.TextBox" name="receipt_number'.$no.'" id="receipt_number'.$no.'" value="'.$row['receipt_number'].'" >
				    	</td>
				    	<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; min-width: 100px;">
				    		<select queryExpr="*${0}*" autoComplete="false" dojoType="dijit.form.FilteringSelect" class="fullside" name="itemdetail_id'.$no.'" id="itemdetail_id'.$no.'" >';
						    	if (!empty($allproduct)) foreach ($allproduct as $pro){
						    		$selected="";
						    		if ($rowpaymentdetail['product_id']==$pro['id']){
						    			$selected='Selected="Selected"';
						    		}
						    		$string.='<option '.$selected.' value="'.$pro['id'].'">'.$pro['name'].'</option>';
						    	}
				   $string.='</select></td>
				    	<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; min-width: 70px;">&nbsp;
				    		<label id="origtotallabel'.$no.'">'.number_format($rowpaymentdetail['qty'],2).'</label>
				    		<input type="hidden" dojoType="dijit.form.TextBox" name="qty'.$no.'" id="qty'.$no.'" value="'.$rowpaymentdetail['qty'].'" >
				    	</td>
				    	<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc;  min-width: 70px; ">&nbsp;
				    		<label id="duelabel'.$no.'">'.number_format($duevalu,2).'</label>
				    		<input type="hidden" dojoType="dijit.form.TextBox" name="qty_balance'.$no.'" id="qty_balance'.$no.'" value="'.$duevalu.'" >
				    		<input type="hidden" dojoType="dijit.form.TextBox" name="detailid'.$no.'" id="detailid'.$no.'" value="'.$rowpaymentdetail['id'].'" >
				    	</td>
				    	<td style="width: 70px;"><input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateamount('.$no.');" name="qty_receive'.$no.'" id="qty_receive'.$no.'" value="'.$rowpaymentdetail['qty_receive'].'" style="text-align: center;" ></td>
				    	<td style="width: 70px;"><input type="text" class="fullside" readonly="readonly" dojoType="dijit.form.NumberTextBox" required="required" name="remain'.$no.'" id="remain'.$no.'" value="'.$rowpaymentdetail['qty_after'].'" style="text-align: center;" ></td>
				    	<td >
				    		<input class="fullside" type="text" dojoType="dijit.form.DateTextBox" name="remide_date'.$no.'" id="remide_date'.$no.'" value="'.date("Y-m-d",strtotime($rowpaymentdetail['remide_date'])).'" >
				    	</td>
			    	</tr>';
			    	
			    }else{
			    	$string.='
			    	<tr id="row'.$no.'" style="background: #fff; border: solid 1px #bac;">
				    	<td align="center" style="  padding: 0 10px;"><input  OnChange="CheckAllTotal('.$no.')" style=" vertical-align: top; height: initial;" type="checkbox" class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]"/></td>
				    	<td style="text-align: center;vertical-align: middle; ">'.($key+1).'</td>
				    	<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; min-width: 100px;">&nbsp;
				    		<label id="billingdatelabel'.$no.'">'.date("d-M-Y",strtotime($row['payment_date'])).'</label>
				    		<input type="hidden" dojoType="dijit.form.TextBox" name="payment_id'.$no.'" id="payment_id'.$no.'" value="'.$row['payment_id'].'" >
				    		<input type="hidden" dojoType="dijit.form.TextBox" name="paymentdetail_id'.$no.'" id="paymentdetail_id'.$no.'" value="'.$row['id'].'" >
				    	<input type="hidden" dojoType="dijit.form.TextBox" name="productname'.$no.'" id="productname'.$no.'" value="'.$row['items_name'].'" >
				    	</td>
				    	<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; min-width: 100px;">&nbsp;
				    	<label id="invoicelabel'.$no.'" title="'.$row['receipt_number'].'" class="invoicelabel">'.$row['receipt_number'].'</label>&nbsp;
				    	<input type="hidden" dojoType="dijit.form.TextBox" name="receipt_number'.$no.'" id="receipt_number'.$no.'" value="'.$row['receipt_number'].'" >
				    	</td>
				    	<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; min-width: 100px;">
				    	<select queryExpr="*${0}*" autoComplete="false" dojoType="dijit.form.FilteringSelect" class="fullside" name="itemdetail_id'.$no.'" id="itemdetail_id'.$no.'" >';
						    	if (!empty($allproduct)) foreach ($allproduct as $pro){
						    		$selected="";
						    		if ($row['pro_id']==$pro['id']){
						    			$selected='Selected="Selected"';
						    		}
						    		$string.='<option '.$selected.' value="'.$pro['id'].'">'.$pro['name'].'</option>';
						    	}
				   $string.='</select>	
				    	</td>
				    	<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; min-width: 70px;">&nbsp;
				    		<label id="origtotallabel'.$no.'">'.number_format($row['qty'],2).'</label>
				    		<input type="hidden" dojoType="dijit.form.TextBox" name="qty'.$no.'" id="qty'.$no.'" value="'.$row['qty'].'" >
				    	</td>
				    	<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc;  min-width: 70px; ">&nbsp;
				    		<label id="duelabel'.$no.'">'.number_format($row['qty_after'],2).'</label>
				    		<input type="hidden" dojoType="dijit.form.TextBox" name="qty_balance'.$no.'" id="qty_balance'.$no.'" value="'.$row['qty_after'].'" >
				    	</td>
				    	<td style="width: 70px;"><input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateamount('.$no.');" name="qty_receive'.$no.'" id="qty_receive'.$no.'" value="0" style="text-align: center;" ></td>
				    	<td style="width: 70px;"><input type="text" class="fullside" readonly="readonly" dojoType="dijit.form.NumberTextBox" required="required" name="remain'.$no.'" id="remain'.$no.'" value="'.$row['qty_after'].'" style="text-align: center;" ></td>
				    	<td >
				    		<input class="fullside" type="text" dojoType="dijit.form.DateTextBox" name="remide_date'.$no.'" id="remide_date'.$no.'" value="now" >
				    	</td>
			    	</tr>';
			    }
			    $no++;
			    
		    }
	    }else{
	    	$no++;
	    }
	    $all_balance =0;
	    $userbalace = $this->getCurrentBalanceByStudent($data);
	    if (!empty($userbalace)){
	   	 	$all_balance = $userbalace;
	    }
	    $array = array('stuName'=>$stuName,'stucode'=>$stuCode,'stringrow'=>$string,'keyindex'=>$no,'identity'=>$identity,'identitycheck'=>$identityedit,'all_balance'=>$all_balance,'sql'=>$sql);
	    return $array;
    }
    function getSumCutStockDetailByStuPayDetId($stupaydetail_id,$cutstockdetailid){
    	$db = $this->getAdapter();
    	$sql="SELECT SUM(pd.`qty_receive`) AS tolalpayamount FROM `rms_cutstock_detail` AS pd WHERE pd.`student_paymentdetail_id`=$stupaydetail_id AND pd.`id` != $cutstockdetailid AND (SELECT p.`status`=1 FROM `rms_cutstock` AS p WHERE p.`id` = pd.`cutstock_id` LIMIT 1) =1";
    	return $db->fetchRow($sql);
    }
    
    function updateCutStock($_data){
    	try{
    		$_arr=array(
    				'branch_id'	   => $_data['branch_id'],
    				'serailno'	   => $_data['serailno'],
    				'student_id'   => $_data['student_id'],
    				'balance'      => $_data['balance'],
    				'total_received'=> $_data['total_paid'],
    				'total_qty_due' => $_data['total_due'],
    				'received_date' => $_data['date_payment'],
//     				'create_date'   => date("Y-m-d H:i:s"),
    				'modify_date'	=> date("Y-m-d H:i:s"),
    				'status'        => 1,
    				'user_id'       => $this->getUserId(),
    				'note'          => $_data['note'],
    		);
    		$this->_name ='rms_cutstock';
    		$cut_id = $_data['id'];
    		$where = " id = $cut_id";
    		$this->update($_arr, $where);
    
    		$row = $this->getCutStockDetailBYId($cut_id);
    		
    		if (!empty($row)) foreach ($row as $pay_detail){
    			$rowpaymentdetail = $this->getCutstockDetailByCutstockIdAndStuDetailId($cut_id, $pay_detail['student_paymentdetail_id']);
//     			print_r($rowpaymentdetail);exit();
    			if (!empty($rowpaymentdetail)){
    				$stupaydetail = $this->getStudentPaymentDetailById($pay_detail['student_paymentdetail_id'],null,$_data['branch_id']);
    					
    				$qtyreceive=$rowpaymentdetail['qty_receive'];
    				
    				$paymenttailbysale = $this->getSumCutStockDetailByStuPayDetId($pay_detail['student_paymentdetail_id'], $pay_detail['id']);// get other pay amount on this Purchase id on other payment receipt number
    				$qtyfter = $stupaydetail['qty_after']+$qtyreceive;
    				
    				if (!empty($paymenttailbysale['tolalpayamount'])){
    					$duevalu = ($rowpaymentdetail['qty']-$paymenttailbysale['tolalpayamount']);
    					$qtyfter =$duevalu;
    				}
    				
    				$array=array(
    						'qty_after'=>$qtyfter,
    				);
    				$where="id=".$pay_detail['student_paymentdetail_id'];
    				$this->_name="rms_saledetail";
    				$this->update($array, $where);
    				
    				//return product to stock
    				$dbpu = new Stock_Model_DbTable_DbPurchase();
    				$dbpu->updateStock($pay_detail['product_id'],$_data['branch_id'],$qtyreceive);
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
    		$this->_name="rms_cutstock_detail";
    		$where2=" cutstock_id = ".$cut_id;
    		if (!empty($detailidlist)){ // check if has old payment detail  detail id
    			$where2.=" AND id NOT IN (".$detailidlist.")";
    		}
    		$this->delete($where2);
    		
    		$qtyfter=0;
    		foreach ($ids as $i){
    			$stupaydetail = $this->getStudentPaymentDetailById($_data['paymentdetail_id'.$i],$_data['payment_id'.$i],$_data['branch_id']);
    			$qtyreceive = $_data['qty_receive'.$i];
    			 
    			if (!empty($stupaydetail)){
    				$qtyfter = $stupaydetail['qty_after']-$qtyreceive;
    				// update Purchase Balance
    				$array=array(
    						'qty_after'=>$qtyfter,
    				);
    				$where="id=".$_data['paymentdetail_id'.$i]." AND payment_id =".$_data['payment_id'.$i];
    				$this->_name="rms_saledetail";
    				$this->update($array, $where);
    			}
    			if (!empty($_data['detailid'.$i])){
    				$arrs = array(
    						'cutstock_id'=>$cut_id,
    						'student_paymentdetail_id'=>$_data['paymentdetail_id'.$i],
    						'product_id'=>$_data['itemdetail_id'.$i],
    						'due_amount'=>$_data['qty_balance'.$i],
    						'qty_receive'=>$_data['qty_receive'.$i],
    						'remain'=>$_data['remain'.$i],
    						'remide_date'=>$_data['remide_date'.$i],
    				);
    				$this->_name ='rms_cutstock_detail';
    				$where=" id= ".$_data['detailid'.$i];
    				$this->update($arrs, $where);
    			}else{
	    			$arrs = array(
	    					'cutstock_id'=>$cut_id,
	    					//     					'payment_id'=>$_data['payment_id'.$i],
	    					'student_paymentdetail_id'=>$_data['paymentdetail_id'.$i],
	    					'product_id'=>$_data['itemdetail_id'.$i],
	    					'due_amount'=>$_data['qty_balance'.$i],
	    					'qty_receive'=>$_data['qty_receive'.$i],
	    					'remain'=>$_data['remain'.$i],
	    					//     					'received_date'=>date("Y-m-d"),
	    					'remide_date'=>$_data['remide_date'.$i],
	    			);
	    			$this->_name ='rms_cutstock_detail';
	    			$this->insert($arrs);
    			}
    			 
    			//cut stock
    			$dbpu = new Stock_Model_DbTable_DbPurchase();
    			$dbpu->updateStock($_data['itemdetail_id'.$i],$_data['branch_id'],-$_data['qty_receive'.$i]);
    		}
    		return $cut_id;
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    
    function voidCutStock($id,$branch_id){
    	try{
	    	$_arr=array(
	    			'status'	      => 0,
	    			'user_id'  =>$this->getUserId(),
	    			'modify_date'	  => date("Y-m-d H:i:s"),
	    	);
	    	$this->_name ='rms_cutstock';
	    	$where = ' id = '.$id;
	    	$this->update($_arr, $where);
	    	$cut_id = $id;
   			$row = $this->getCutStockDetailBYId($cut_id);
    		
    		if (!empty($row)) foreach ($row as $pay_detail){
    			$rowpaymentdetail = $this->getCutstockDetailByCutstockIdAndStuDetailId($cut_id, $pay_detail['student_paymentdetail_id']);
    			if (!empty($rowpaymentdetail)){
    				$stupaydetail = $this->getStudentPaymentDetailById($pay_detail['student_paymentdetail_id'],null,$branch_id);
    					
    				$qtyreceive=$rowpaymentdetail['qty_receive'];
    				
    				$paymenttailbysale = $this->getSumCutStockDetailByStuPayDetId($pay_detail['student_paymentdetail_id'], $pay_detail['id']);// get other pay amount on this Purchase id on other payment receipt number
    				$qtyfter = $stupaydetail['qty_after']+$qtyreceive;
    				//     				echo $dueafters;exit();
    				if (!empty($paymenttailbysale['tolalpayamount'])){
    					$duevalu = ($rowpaymentdetail['qty']-$paymenttailbysale['tolalpayamount']);
    					$qtyfter =$duevalu;
    				}
    				
    				$array=array(
    						'qty_after'=>$qtyfter,
    				);
    				$where="id=".$pay_detail['student_paymentdetail_id'];
    				$this->_name="rms_saledetail";
    				$this->update($array, $where);
    				
    				//return product to stock
    				$dbpu = new Stock_Model_DbTable_DbPurchase();
    				$dbpu->updateStock($pay_detail['product_id'],$branch_id,$qtyreceive);
    			}
    		}
    		
    		return $cut_id;
    	}catch(Exception $e){
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
}





