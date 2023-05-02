<?php

class Invpayment_Model_DbTable_DbInvoiceStatement extends Zend_Db_Table_Abstract
{
    protected $_name = '';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllStmProduct($search){
    	$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();

		$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
		$sql="SELECT st.id,
                (SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id=st.projectId LIMIT 1) AS projectName,
                st.stProductNo, st.stmentDate, st.supplierStmentNo, st.totalExternal,

                (SELECT sp.supplierName FROM `st_supplier` AS sp  WHERE sp.id = st.supplierId LIMIT 1) AS supplierName,
                st.note, 
                (SELECT u.first_name FROM `rms_users` AS u WHERE u.id = st.userId LIMIT 1 ) AS byUser, st.status 
         ";

		$sql.=$dbGb->caseStatusShowImage("st.status");
		$sql.="
                  FROM `st_statement_product` AS st WHERE 1
			";
		
    	$where = "";
    	$from_date =(empty($search['start_date']))? '1': " st.stmentDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " st.stmentDate <= '".$search['end_date']." 23:59:59'";
    	
    	$where.= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){

    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		$s_where[] = " st.stmentNo LIKE '%{$s_search}%'";
    		$s_where[] = " st.supplierStmentNo LIKE '%{$s_search}%'";
    		$s_where[] = " st.totalExternal LIKE '%{$s_search}%'";

    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND st.status = ".$search['status'];
    	}
    	if(($search['branch_id'])>0){
    		$where.= " AND st.projectId = ".$search['branch_id'];
    	}
		if(!empty($search['supplierId'])){
    		$where.= " AND st.supplierId = ".$search['supplierId'];
    	}
    	$order=' ORDER BY st.id DESC  ';
    	$where.=$dbGb->getAccessPermission("st.projectId");

    	return   $db->fetchAll($sql.$where.$order);
		
    }
    function addInvoiceStatment($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    	
	    	$arr = array(
	    			'statementType'=>1,
	    			'projectId'=>$data['branch_id'],
	    			'supplierId'=>$data['supplierId'],
	    			'stProductNo'=>$data['invoiceNo'],
	    			'stmentDate'=>date("Y-m-d"),
	    			'supplierStmentNo'=>$data['supplierstMentNo'],
	            	'invIdList'=>$data['invIdentity'],
	    			'fromDate'=>$data['startDate'],
	    			'toDate'=>$data['endDate'],
                    'createDate'=>date("Y-m-d H:i:s"),
	    			'note'=>$data['note'],
	    			'userId'=>$this->getUserId(),
	    			'totalExternal'=>$data['totalAmountExternal'],
	    		);
             $this->_name='st_statement_product';
	    	$stmentId  = $this->insert($arr);
	    	
	    	$ids = explode(',', $data['identity']);
	    	foreach ($ids as $i){
	    		$arr = array(
	    				'stmProId' =>$stmentId,
	    				'invId'    =>$data['invId'.$i],
	    				'proId'    =>$data['proId'.$i],
	    				'qty'    =>$data['qty'.$i],
                        'unitPrice'    =>$data['unitPrice'.$i],
                        'discount'    =>$data['discount'.$i],
	    				'subTotal' =>$data['subTotal'.$i],
	    			);
	    		$this->_name='st_statement _product_detail';
	    		$this->insert($arr);

	    		$this->_name='st_invoice';
	    		$arr = array(
	    				'isInvoiceStatement'=>1
	    				);
	    		$where = 'id='.$data['invId'.$i];
	    		$this->update($arr, $where);
	    	}
    		$db->commit();
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL","/invpayment/index/add");
    	}
    }
   
    function getInvoiceId($data){
        $tr = Application_Form_FrmLanguages::getCurrentlanguage();

    	$db =$this->getAdapter();
    	$sql="SELECT iv.id,iv.invoiceNo,
	            vd.proId,
	            (SELECT proName FROM `st_product` AS p WHERE p.proId=vd.proId) AS proName,
	            (SELECT proCode FROM `st_product` AS p WHERE p.proId=vd.proId) AS proCode,
	            (SELECT NAME FROM `st_measure`AS m WHERE m.id= (SELECT measureId FROM `st_product` WHERE proId = vd.proId)) AS measure,
	            vd.totalQtyreceive, vd.unitPriceReceive, vd.totalReceiveDiscount, vd.totalReceive
            FROM `st_invoice`  AS iv
                JOIN `st_invoice_detail` AS vd
            ON iv.id =vd.invId
        ";
    	$sql.=" WHERE status=1 ";
    	 
    	if(!empty($data['branch_id'])){
    		$sql.=" AND iv.projectId = ".$data['branch_id'];
    	}
    	
    	if(!empty($data['supplierId'])){
    		$sql.=" AND iv.supplierId= ".$data['supplierId'];
    	}
    	if(isset($data['isInvoiceStatement'])){
    		$sql.=" AND iv.isInvoiceStatement= ".$data['isInvoiceStatement'];
    	}
//     	if(!empty($data['transactionType'])){
//     		$sql.=" AND iv.transactionType= ".$data['transactionType'];
//     	}
    	
    	$fromDate =(empty($data['fromDate']))? '1': " iv.invoiceDate >= '".$data['fromDate']." 00:00:00'";
    	$toDate = (empty($data['toDate']))? '1': " iv.invoiceDate <= '".$data['toDate']." 23:59:59'";
    	
    	$sql.= " AND ".$fromDate." AND ".$toDate;
	
    	$results =  $db->fetchAll($sql);


        $string='';
    	$no = $data['keyindex'];
    	$identity='';
		$invIdentity='';
    	$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
        $gTotalExternal = 0;
		
    	if(!empty($results)){
    		foreach ($results as $key => $row){
    			if (empty($identity)){
    				$identity=$no;
                    $invIdentity=$row['id'];
    			}else{
                    $identity=$identity.",".$no;
                    $invIdentity = $invIdentity.",".$row['id'];
    			}
				
				$classRowBg = "odd";
				if(($key%2)==0){
				$classRowBg = "regurlar";
				}	

                $gTotalExternal = $gTotalExternal+$row['totalReceive'];
				
                $string.='<tr id="row'.$no.'" class="rowData '.$classRowBg.'" >';
	    			$string.='<td  class="numberRecord infoCol" align="center"><span title="'.$tr->translate("REMOVE_RECORD").'" class="removeRow" onclick="deleteRecord('.$no.');"><i class="glyphicon glyphicon-trash" aria-hidden="true"></i></span></td>';
	    			$string.='<td  class="numberRecord infoCol" data-label="'.$tr->translate("N_O").'" align="center" >'.($key+1).'<input type="hidden" dojoType="dijit.form.TextBox" class="fullside" id="rsId'.$no.'" name="rsId'.$no.'" value="'.$row['id'].'"></td>';
	    			$string.='<td  data-label="'.$tr->translate("PRODUCT_NAME").'" class="productName infoCol" >'.$row['proCode'].'<br />'.$row['proName'].'<input type="hidden" dojoType="dijit.form.TextBox" class="fullside" id="proId'.$no.'" name="proId'.$no.'" value="'.$row['proId'].'" type="text" ></td>';
	    			$string.='<td  data-label="'.$tr->translate("MEASURE").'" class="red infoCol"  style="color:red;" >'.$row['measure'].'</td>';
	    			$string.='<td  data-label="'.$tr->translate("INVOICE_NO").'" class="red infoCol" ><input readOnly dojoType="dijit.form.ValidationTextBox" class="fullside" id="invId'.$no.'" name="invId'.$no.'" value="'.$row['id'].'" type="hidden" >'.$row['invoiceNo'].'</td>';
	    			$string.='<td data-label="'.$tr->translate("QTY").'" class=" bold" ><input readOnly dojoType="dijit.form.NumberTextBox" required="true" class="fullside" id="qty'.$no.'" name="qty'.$no.'" placeholder="'.$tr->translate("QTY").'" value="'.$row['totalQtyreceive'].'" type="text" ></td>';
	    			$string.='<td data-label="'.$tr->translate("UNIT_PRICE").'" class=" bold"><input readOnly  dojoType="dijit.form.NumberTextBox" required="true"  class="fullside" id="unitPrice'.$no.'" name="unitPrice'.$no.'" placeholder="'.$tr->translate("UNIT_PRICE").'" value="'.$row['unitPriceReceive'].'" type="text" ></td>';
					$string.='<td data-label="'.$tr->translate("DISCOUNT").'" class=" bold"><input readOnly  dojoType="dijit.form.NumberTextBox" required="true"  class="fullside" id="discount'.$no.'" name="discount'.$no.'" placeholder="'.$tr->translate("UNIT_PRICE").'" value="'.$row['totalReceiveDiscount'].'" type="text" ></td>';
	    			$string.='<td data-label="'.$tr->translate("SUBTOTAL").'" class=" bold"><input dojoType="dijit.form.NumberTextBox" readOnly required="true" class="fullside" id="subTotal'.$no.'" name="subTotal'.$no.'" placeholder="'.$tr->translate("TOTAL").'" value="'.$row['totalReceive'].'" type="text"  ></td>';
    			$string.='</tr>';
    			$no++;
    			
    		}
    	}else{
    		$no++;
    	}
		
		$array = array(

            'stringrow'=>$string,
            'keyindex'=>$no,
            'identity'=>$identity,
            'gTotalExternal'=>$gTotalExternal,
            'invIdentity'=>$invIdentity
		);
        return $array;
	
    	// if(!empty($data['labelList'])){
    	// 	return $this->getAllInvoicebyData($results,$data);
    	// }else{
    	// 	return $results;
    	// }
    }

    function getProductStatement($recordId){
    	$db =$this->getAdapter();
    	$sql="SELECT 
                ( SELECT v.invoiceDate  FROM `st_invoice` AS v WHERE v.id=sd.invId LIMIT 1) AS invoiceDate,
                ( SELECT v.invoiceNo  FROM `st_invoice` AS v WHERE v.id=sd.invId LIMIT 1) AS invoiceNo,
                (SELECT sp.supplierName FROM `st_supplier` AS sp  WHERE sp.id = st.supplierId LIMIT 1) AS supplierName,
                ( SELECT p.proName FROM `st_product` AS p WHERE p.proId=sd.proId LIMIT 1) AS proName,
                ( SELECT p.proCode FROM `st_product` AS p WHERE p.proId=sd.proId LIMIT 1) AS proCode,
                ( SELECT p.measureLabel FROM `st_product` AS p WHERE p.proId=sd.proId LIMIT 1) AS measureLabel,
                sd.qty, sd.unitPrice, sd.subTotal, st.stmentDate
               FROM `st_statement _product_detail` sd  JOIN `st_statement_product` AS st ON sd.stmProId= st.id
                 WHERE sd.stmProId = ".$recordId;
    	return $db->fetchAll($sql);
    }

	function getStatementRow($recordId){
    	$db =$this->getAdapter();
    	$sql="SELECT *,
			(SELECT sp.supplierName FROM `st_supplier` AS sp  WHERE sp.id = st.supplierId LIMIT 1) AS supplierName,
			(SELECT sp.contactName FROM `st_supplier` AS sp  WHERE sp.id = st.supplierId LIMIT 1) AS contactName,
			(SELECT sp.contactNumber FROM `st_supplier` AS sp  WHERE sp.id = st.supplierId LIMIT 1) AS contactNumber
			 FROM `st_statement_product` AS st  WHERE st.id = ".$recordId;
    	return $db->fetchRow($sql);
    }


    
    // function getAllInvoicebyData($invData,$data){
    // 	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	
    // 	$string='';
    // 	$no = $data['keyindex'];
    // 	$identity='';
    // 	if(!empty($invData)){
    // 		foreach ($invData as $key => $row){
    // 			if (empty($identity)){
    // 				$identity=$no;
    // 				$dnIdentity=$row['id'];
    // 			}else{
    // 				$identity=$identity.",".$no;
    // 				$dnIdentity=$dnIdentity.",".$row['id'];
    // 			}
    			
    // 			$row = $this->getInvoiceDetail($row['id']);
    			
    // 			$classRowBg = "odd";
    // 			if(($key%2)==0){
    // 				$classRowBg = "regurlar";
    // 			}
    	
    // 			$qtyReceived=0;
    // 			$note='';
    	
    // 			$string.='<tr id="row'.$no.'" class="rowData '.$classRowBg.'" >';
	//     			$string.='<td  class="numberRecord infoCol" align="center"><span title="'.$tr->translate("REMOVE_RECORD").'" class="removeRow" onclick="deleteRecord('.$no.');"><i class="glyphicon glyphicon-trash" aria-hidden="true"></i></span></td>';
	//     			$string.='<td  class="numberRecord infoCol" data-label="'.$tr->translate("N_O").'" align="center" >'.($key+1).'<input type="hidden" dojoType="dijit.form.TextBox" class="fullside" id="rsId'.$no.'" name="rsId'.$no.'" value="'.$row['id'].'"></td>';
	//     			$string.='<td  data-label="'.$tr->translate("PRODUCT_NAME").'" class="productName infoCol" >'.$row['proCode'].'<br />'.$row['proName'].'<input type="hidden" dojoType="dijit.form.TextBox" class="fullside" id="proId'.$no.'" name="proId'.$no.'" value="'.$row['proId'].'" type="text" ></td>';
	//     			$string.='<td  data-label="'.$tr->translate("MEASURE").'" class="red infoCol"  >'.$row['measure'].'</td>';
	//     			$string.='<td  data-label="'.$tr->translate("INVOICE_NO").'" class="red infoCol" ><input readOnly dojoType="dijit.form.ValidationTextBox" class="fullside" id="invId'.$no.'" name="invId'.$no.'" value="'.$row['id'].'" type="hidden" >'.$row['invoiceNo'].'</td>';
	//     			$string.='<td data-label="'.$tr->translate("QTY").'" class=" bold" ><input readOnly dojoType="dijit.form.NumberTextBox" required="true" class="fullside" id="qty'.$no.'" name="qty'.$no.'" placeholder="'.$tr->translate("QTY").'" value="'.$row['totalQtyreceive'].'" type="text" ></td>';
	//     			$string.='<td data-label="'.$tr->translate("UNIT_PRICE").'" class=" bold"><input readOnly  dojoType="dijit.form.NumberTextBox" required="true"  class="fullside" id="unitPrice'.$no.'" name="unitPrice'.$no.'" placeholder="'.$tr->translate("UNIT_PRICE").'" value="'.$row['unitPriceReceive'].'" type="text" ></td>';
	// 				$string.='<td data-label="'.$tr->translate("DISCOUNT").'" class=" bold"><input readOnly  dojoType="dijit.form.NumberTextBox" required="true"  class="fullside" id="discount'.$no.'" name="discount'.$no.'" placeholder="'.$tr->translate("UNIT_PRICE").'" value="'.$row['totalReceiveDiscount'].'" type="text" ></td>';
	//     			$string.='<td data-label="'.$tr->translate("SUBTOTAL").'" class=" bold"><input dojoType="dijit.form.NumberTextBox" readOnly required="true" class="fullside" id="subTotal'.$no.'" name="subTotal'.$no.'" placeholder="'.$tr->translate("TOTAL").'" value="'.$row['totalReceive'].'" type="text"  ></td>';
    // 			$string.='</tr>';
    // 			$no++;
    // 		}
    // 	}
    // 	$array = array(
    //                     'stringrow'=>$string,
    // 					'keyindex'=>$no,
    // 					'identity'=>$identity,
    // 					'dnIdentity'=>$dnIdentity);
    // 	return $array;
    // }


	// function getInvoiceDetail($_data=null){
	// 	$db=$this->getAdapter();
		
	// 	$sql="SELECT iv.id,iv.invoiceNo,
	// 	vd.proId,
	// 	(SELECT proName FROM `st_product` AS p WHERE p.proId=vd.proId) AS proName,
	// 	(SELECT proCode FROM `st_product` AS p WHERE p.proId=vd.proId) AS proCode,
	// 	(SELECT NAME FROM `st_measure`AS m WHERE m.id= (SELECT measureId FROM `st_product` WHERE proId = vd.proId)) AS measure,
	// 	vd.totalQtyreceive, vd.unitPriceReceive, vd.totalReceiveDiscount, vd.totalReceive
	// 	FROM `st_invoice`  AS iv
	// 		JOIN `st_invoice_detail` AS vd
	// 	ON iv.id =vd.invId WHERE 1 ";	
	// 	if(!empty($_data['invId'])){
	// 		$sql.=" AND vd.invId=".$_data['invId'];
	// 	}
	// 	return $db->fetchRow($sql);
		
	// }  
}