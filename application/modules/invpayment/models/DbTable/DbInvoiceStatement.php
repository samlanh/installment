<?php

class Invpayment_Model_DbTable_DbInvoiceStatement extends Zend_Db_Table_Abstract
{
    protected $_name = '';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    // function getDnConcreteList($data){
    // 	$db = $this->getAdapter();
    // 	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    
    // 	$supplierId = empty($data['supplierId'])?0:$data['supplierId'];
    // 	$branchId = $data['branch_id'];
    
    // 	$sql="SELECT 
	// 	    	 po.purchaseNo AS purchaseNo ,
	// 	    	 po.date AS purchaseDate,
	// 	    	 rst.dnNumber,
	// 	    	 rst.receiveDate,
	    	 
    // 	";
    // 	$sql.="
    // 	FROM st_receive_stock AS rst
    // 		JOIN `st_purchasing`AS po ON po.id = rst.poId
    // 	";
    // 	$sql.=" WHERE rst.status=1 ";
    // 	$sql.=" AND rst.verified =1 ";
    // 	$sql.=" AND rst.projectId =$branchId ";
    // 	$sql.=" AND rst.supplierId =$supplierId ";
    	
    // 	if(!empty($data['transactionType'])){
    // 		$sql.=" AND rst.transactionType = ".$data['transactionType'];
    // 	}
    	
    // 	$dbiv = new Invpayment_Model_DbTable_DbInvoice();
    // 	$DNListFromInvoice = $dbiv->getDnIDInInvoice($data);
    // 	if(!empty($DNListFromInvoice)){ 
    // 		$sql.=" AND rst.id NOT IN ($DNListFromInvoice) ";
    // 	}
    
    // 	$rs = $db->fetchAll($sql);
    
    
    // 	$string='';
    // 	$no = $data['dnKeyIndex'];
    // 	$dnIdentity='';
    // 	$dnIdentityChecked='';
    // 	$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
    
    // 	$gTotalInternal = 0;
    // 	$gTotalExternal = 0;
    // 	$string.='<ul class="listDn">';
    // 	if(!empty($rs)){
    // 		foreach ($rs as $key => $row){
    // 			if (empty($dnIdentity)){
    // 				$dnIdentity=$no;
    // 			}else{$dnIdentity=$dnIdentity.",".$no;
    // 			}
    // 			if(!empty($data['dnId'])){
    // 				$currentInvoice = $data['dnId'];
    // 				if(!empty($currentInvoice)){
    // 					$dnIds = explode(",",$currentInvoice);
    // 					$true=0;
    // 					foreach ($dnIds as $iid){
    // 						if($iid==$row['id']){
    // 							if (empty($dnIdentityChecked)){
    // 								$dnIdentityChecked=$no;
    // 							}else{$dnIdentityChecked=$dnIdentityChecked.",".$no;
    
    // 							}
    // 							$true=1;
    // 							$string.='
    // 							<li>
    // 							<div class="custom-control custom-checkbox ">
    // 							<input type="checkbox" class="checkbox custom-control-input" checked="checked" OnChange="checkAllDn('.$no.')"  class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]" >
    // 							<label class="custom-control-label dnNumber " for="mfdid_'.$no.'">
    // 							<span  class="dnNumber">'.$row['dnNumber'].' '.date("d-m-Y",strtotime($row['receiveDate'])).'</span>
    // 							</label>
    // 							</div>
    // 							<small class="poNumber">'.$tr->translate("PO_NO").' : '.$row['purchaseNo'].' '.date("d-m-Y",strtotime($row['purchaseDate'])).'</small>
    // 							//<small class="requestNumber">'.$tr->translate("REQUEST_NO").' : '.$row['requestNo'].' '.date("d-m-Y",strtotime($row['requestDate'])).'</small>
    // 							<input type="hidden" dojoType="dijit.form.TextBox" name="deliveryId'.$no.'" id="deliveryId'.$no.'" value="'.$row['id'].'" >
    // 							</li>
    // 							';
    // 							break;
    // 						}
    // 					}
    // 					if($true==0){
    // 						$string.='
    // 						<li>
    // 						<div class="custom-control custom-checkbox ">
    // 						<input type="checkbox" class="checkbox custom-control-input" OnChange="checkAllDn('.$no.')" class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]" >
    // 						<label class="custom-control-label dnNumber " for="mfdid_'.$no.'">
    // 						<span  class="dnNumber">'.$row['dnNumber'].' '.date("d-m-Y",strtotime($row['receiveDate'])).'</span>
    // 						</label>
    // 						</div>
    // 						<small class="poNumber">'.$tr->translate("PO_NO").' : '.$row['purchaseNo'].' '.date("d-m-Y",strtotime($row['purchaseDate'])).'</small>
    // 						<small class="requestNumber">'.$tr->translate("REQUEST_NO").' : '.$row['requestNo'].' '.date("d-m-Y",strtotime($row['requestDate'])).'</small>
    // 						<input type="hidden" dojoType="dijit.form.TextBox" name="deliveryId'.$no.'" id="deliveryId'.$no.'" value="'.$row['id'].'" >
    // 						</li>
    // 						';
    // 					}
    // 				}else{
    // 					$string.='
    // 					<li>
    // 					<div class="custom-control custom-checkbox ">
    // 					<input type="checkbox" class="checkbox custom-control-input" OnChange="checkAllDn('.$no.')" class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]" >
    // 					<label class="custom-control-label dnNumber " for="mfdid_'.$no.'">
    // 					<span  class="dnNumber">'.$row['dnNumber'].' '.date("d-m-Y",strtotime($row['receiveDate'])).'</span>
    // 					</label>
    // 					</div>
    // 					<small class="poNumber">'.$tr->translate("PO_NO").' : '.$row['purchaseNo'].' '.date("d-m-Y",strtotime($row['purchaseDate'])).'</small>
    // 					<small class="requestNumber">'.$tr->translate("REQUEST_NO").' : '.$row['requestNo'].' '.date("d-m-Y",strtotime($row['requestDate'])).'</small>
    // 					<input type="hidden" dojoType="dijit.form.TextBox" name="deliveryId'.$no.'" id="deliveryId'.$no.'" value="'.$row['id'].'" >
    // 					</li>
    // 					';
    // 				}
    // 			}else{
    // 				if (empty($dnIdentityChecked)){
    // 					$dnIdentityChecked=$no;
    // 				}else{$dnIdentityChecked=$dnIdentityChecked.",".$no;
    					
    // 				}
    // 				$string.='
    // 				<li>
    // 				<div class="custom-control custom-checkbox ">
    // 				<input type="checkbox" class="checkbox custom-control-input" checked="checked" OnChange="checkAllDn('.$no.')"  class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]" >
    // 				<label class="custom-control-label dnNumber " for="mfdid_'.$no.'">
    // 				<span  class="dnNumber">'.$row['dnNumber'].' '.date("d-m-Y",strtotime($row['receiveDate'])).'</span>
    // 				</label>
    // 				</div>
    // 				<small class="poNumber">'.$tr->translate("PO_NO").' : '.$row['purchaseNo'].' '.date("d-m-Y",strtotime($row['purchaseDate'])).'</small>
    // 				<small class="requestNumber">'.$tr->translate("REQUEST_NO").' : '.$row['requestNo'].' '.date("d-m-Y",strtotime($row['requestDate'])).'</small>
    // 				<input type="hidden" dojoType="dijit.form.TextBox" name="deliveryId'.$no.'" id="deliveryId'.$no.'" value="'.$row['id'].'" >
    // 				</li>
    // 				';
    // 			}
    // 			$no++;
    // 		}
    // 	}else{
    // 		$no++;
    // 	}
    // 	$string.='</ul>';
    
    // 	$array = array(
    // 			'stringrow'=>$string,
    // 			'dnKeyIndex'=>$no,
    // 			'dnIdentity'=>$dnIdentity,
    // 			'dnIdentityChecked'=>$dnIdentityChecked,
    // 	);
    
    // 	return $array;
    
    // }
    function getInvoiceId($data){
    	$db =$this->getAdapter();
    	$sql="SELECT 
				id , invoiceNo AS name
	  		  FROM `st_invoice` 
    	";
    	$sql.=" WHERE status=1 ";
    	 
    	if(!empty($data['branch_id'])){
    		$sql.=" AND projectId = ".$data['branch_id'];
    	}
    	
    	if(!empty($data['supplierId'])){
    		$sql.=" AND supplierId= ".$data['supplierId'];
    	}
    	// if(isset($data['isissueStatement'])){
    	// 	$sql.=" AND rst.isissueStatement= ".$data['isissueStatement'];
    	// }
    	
    	$fromDate =(empty($data['fromDate']))? '1': " invoiceDate >= '".$data['fromDate']." 00:00:00'";
    	$toDate = (empty($data['toDate']))? '1': " invoiceDate <= '".$data['toDate']." 23:59:59'";
    	
    	$sql.= " AND ".$fromDate." AND ".$toDate;
	
    	// $dbiv = new Invpayment_Model_DbTable_DbInvoice();
    	// $DNListFromInvoice = $dbiv->getDnIDInInvoice($data);
    	// if(!empty($DNListFromInvoice)){ 
    	// 	$sql.=" AND rst.id NOT IN ($DNListFromInvoice) ";
    	// }

    	$results =  $db->fetchAll($sql);
	
    	if(!empty($data['labelList'])){
    		return $this->getAllInvoicebyData($results,$data);
    	}else{
    		return $results;
    	}
    }
    function getAllInvoicebyData($invData,$data){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	
    	$string='';
    	$no = $data['keyindex'];
    	$identity='';
    	if(!empty($invData)){
    		foreach ($invData as $key => $row){
    			if (empty($identity)){
    				$identity=$no;
    				$dnIdentity=$row['id'];
    			}else{
    				$identity=$identity.",".$no;
    				$dnIdentity=$dnIdentity.",".$row['id'];
    			}
    			
    			$row = $this->getInvoiceDetail($row['id']);
    			
    			$classRowBg = "odd";
    			if(($key%2)==0){
    				$classRowBg = "regurlar";
    			}
    	
    			$qtyReceived=0;
    			$note='';
    	
    			$string.='<tr id="row'.$no.'" class="rowData '.$classRowBg.'" >';
	    			$string.='<td  class="numberRecord infoCol" align="center"><span title="'.$tr->translate("REMOVE_RECORD").'" class="removeRow" onclick="deleteRecord('.$no.');"><i class="glyphicon glyphicon-trash" aria-hidden="true"></i></span></td>';
	    			$string.='<td  class="numberRecord infoCol" data-label="'.$tr->translate("N_O").'" align="center" >'.($key+1).'<input type="hidden" dojoType="dijit.form.TextBox" class="fullside" id="rsId'.$no.'" name="rsId'.$no.'" value="'.$row['id'].'"></td>';
	    			$string.='<td  data-label="'.$tr->translate("PRODUCT_NAME").'" class="productName infoCol" >'.$row['proCode'].'<br />'.$row['proName'].'<input type="hidden" dojoType="dijit.form.TextBox" class="fullside" id="proId'.$no.'" name="proId'.$no.'" value="'.$row['proId'].'" type="text" ></td>';
	    			$string.='<td  data-label="'.$tr->translate("MEASURE").'" class="red infoCol"  >'.$row['measure'].'</td>';
	    			$string.='<td  data-label="'.$tr->translate("INVOICE_NO").'" class="red infoCol" ><input readOnly dojoType="dijit.form.ValidationTextBox" class="fullside" id="invId'.$no.'" name="invId'.$no.'" value="'.$row['id'].'" type="hidden" >'.$row['invoiceNo'].'</td>';
	    			$string.='<td data-label="'.$tr->translate("QTY").'" class=" bold" ><input readOnly dojoType="dijit.form.NumberTextBox" required="true" class="fullside" id="qty'.$no.'" name="qty'.$no.'" placeholder="'.$tr->translate("QTY").'" value="'.$row['totalQtyreceive'].'" type="text" ></td>';
	    			$string.='<td data-label="'.$tr->translate("UNIT_PRICE").'" class=" bold"><input readOnly  dojoType="dijit.form.NumberTextBox" required="true"  class="fullside" id="unitPrice'.$no.'" name="unitPrice'.$no.'" placeholder="'.$tr->translate("UNIT_PRICE").'" value="'.$row['unitPriceReceive'].'" type="text" ></td>';
					$string.='<td data-label="'.$tr->translate("DISCOUNT").'" class=" bold"><input readOnly  dojoType="dijit.form.NumberTextBox" required="true"  class="fullside" id="unitPrice'.$no.'" name="unitPrice'.$no.'" placeholder="'.$tr->translate("UNIT_PRICE").'" value="'.$row['totalReceiveDiscount'].'" type="text" ></td>';
	    			$string.='<td data-label="'.$tr->translate("SUBTOTAL").'" class=" bold"><input dojoType="dijit.form.NumberTextBox" readOnly required="true" class="fullside" id="subTotal'.$no.'" name="subTotal'.$no.'" placeholder="'.$tr->translate("TOTAL").'" value="'.$row['totalReceive'].'" type="text"  ></td>';
    			$string.='</tr>';
    			$no++;
    		}
    	}
    	$array = array('stringrow'=>$string,
    					'keyindex'=>$no,
    					'identity'=>$identity,
    					'dnIdentity'=>$dnIdentity);
    	return $array;
    }


	function getInvoiceDetail($_data=null){
		$db=$this->getAdapter();
		
		$sql="SELECT iv.id,iv.invoiceNo,
		vd.proId,
		(SELECT proName FROM `st_product` AS p WHERE p.proId=vd.proId) AS proName,
		(SELECT proCode FROM `st_product` AS p WHERE p.proId=vd.proId) AS proCode,
		(SELECT NAME FROM `st_measure`AS m WHERE m.id= (SELECT measureId FROM `st_product` WHERE proId = vd.proId)) AS measure,
		vd.totalQtyreceive, vd.unitPriceReceive, vd.totalReceiveDiscount, vd.totalReceive
		FROM `st_invoice`  AS iv
			JOIN `st_invoice_detail` AS vd
		ON iv.id =vd.invId WHERE 1 ";	
		if(!empty($_data['invId'])){
			$sql.=" AND vd.invId=".$_data['invId'];
		}
		return $db->fetchRow($sql);
		
	}  
}