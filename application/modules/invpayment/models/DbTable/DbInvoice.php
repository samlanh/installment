<?php

class Invpayment_Model_DbTable_DbInvoice extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_invoice';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    
	
    function getDataRow($recordId){
    	$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();		
			$this->_name='st_invoice';
			$sql=" SELECT po.* FROM $this->_name AS po WHERE po.id=".$recordId;
			$sql.=$dbGb->getAccessPermission("po.projectId");
			$sql.=" LIMIT 1 ";
    	return $db->fetchRow($sql);
    }
	function getAllInvoiceBySupplier($data){
    	$db = $this->getAdapter();
		
		$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
		$arrStep = array(
			'keyIndex'=>"inv.ivType",
			'typeKeyIndex'=>3,
		);
		
		
			
    	$supplier_id = empty($data['supplierId'])?0:$data['supplierId'];
    	$branch_id = $data['branch_id'];
    	$sql="SELECT inv.* ";
    	$sql.=$dbGBstock->invoiceTypeKey($arrStep);
    	$sql.=" FROM `st_invoice` AS inv  
				WHERE  inv.status=1 
						AND inv.isPaid = 0 
						AND inv.projectId =$branch_id  ";
    	$sql.=" AND inv.supplierId =$supplier_id ";
		
    	$from_date =(empty($data['start_date']))? '1': " inv.receiveIvDate >= '".date("Y-m-d",strtotime($data['start_date']))." 00:00:00'";
    	$to_date = (empty($data['end_date']))? '1': " inv.receiveIvDate <= '".date("Y-m-d",strtotime($data['end_date']))." 23:59:59'";
    	$sql.= " AND  ".$from_date." AND ".$to_date;
    	if (!empty($data['advanceFilter'])){
			
			$s_where = array();
			$s_search = trim(addslashes($data['advanceFilter']));
			$s_where[] = " inv.invoiceNo LIKE '%{$s_search}%'";
			$s_where[] = " inv.supplierInvoiceNo LIKE '%{$s_search}%'";
			$s_where[] = " inv.note LIKE '%{$s_search}%'";
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
				
				$classRowBg = "odd";
				if(($key%2)==0){
				$classRowBg = "regurlar";
				}
    			$string.='
    			<tr id="row'.$no.'" class="rowData '.$classRowBg.'" >
    				<td align="center" style="  padding: 0 10px;"><input  OnChange="CheckAllTotal('.$no.')" style=" vertical-align: top; height: initial;" type="checkbox" class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]"/></td>
	    			<td class="textCenter">'.($key+1).'</td>
	    			<td class="textCenter">&nbsp;
	    				<label id="billingdatelabel'.$no.'">'.$row['ivTypeTitle'].'<br />'.date("d-M-Y",strtotime($row['receiveIvDate'])).'</label>
	    				<input type="hidden" dojoType="dijit.form.TextBox" name="invoiceId'.$no.'" id="invoiceId'.$no.'" value="'.$row['id'].'" >
    				</td>
					<td class="invNoCol">
	    				<label id="titleInvoice'.$no.'">'.$row['invoiceNo'].'<br />'.$row['supplierInvoiceNo'].'</label>
    				</td>
    			
					<td class="textCenter">
						<label id="origtotallabel'.$no.'">'.number_format($row['totalAmountExternal'],2).'</label>
					</td>
					<td class="textCenter">
						<label id="duelabel'.$no.'">'.number_format($row['totalAmountExternalAfter'],2).'</label>
						<input type="hidden" dojoType="dijit.form.TextBox" name="dueAmount'.$no.'" id="dueAmount'.$no.'" value="'.$row['totalAmountExternalAfter'].'" >
					</td>
					
					<td><input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateamount('.$no.');" name="paymentAmount'.$no.'" id="paymentAmount'.$no.'" value="0" style="text-align: center;" ></td>
					<td><input type="text" class="fullside" readonly="readonly" dojoType="dijit.form.NumberTextBox" required="required" name="remain'.$no.'" id="remain'.$no.'" value="'.$row['totalAmountExternalAfter'].'" style="text-align: center;" ></td>
				</tr>
    			';$no++;
    		}
    	}else{
    		$no++;
    	}
    	$gTotalBalance =0;
    	$supplierBalace = $this->getCurrentBalanceBySupplier($data);
    	if (!empty($supplierBalace)){
    		$gTotalBalance = $supplierBalace;
    	}
    	$array = array('stringrow'=>$string,'keyindex'=>$no,'identity'=>$identity,'gTotalBalance'=>$gTotalBalance);
    	return $array;
    }
	function getCurrentBalanceBySupplier($data){
    	$db = $this->getAdapter();
    	$sql = "SELECT SUM(inv.`totalAmountExternal`) AS gTotalBalance FROM `st_invoice` AS inv WHERE inv.`status`=1 AND inv.`isPaid`=0 AND inv.`supplierId`=".$data['supplierId']." AND inv.projectId =".$data['branch_id'];
    	return $db->fetchOne($sql);
    }
   
}