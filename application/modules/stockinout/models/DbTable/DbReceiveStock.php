<?php

class Stockinout_Model_DbTable_DbReceiveStock extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_receive_stock';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllDataRows($search){
    	$sql="";
    	
    	
    	$from_date =(empty($search['start_date']))? '1': " send_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " send_date <= '".$search['end_date']." 23:59:59'";
    	$where='';
    	$where_date = " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		//$s_where[] = " sms.contance LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND s.status = ".$search['status'];
    	}
    	
    	$order.=' ORDER BY id DESC  ';
    	
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where_date.$order);
    }
   
    function addReceiveStock($data){
    	
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	$dbs = new Application_Model_DbTable_DbGlobalStock();
    	try
    	{
    		$param = array(
    				'fetchRow'=>1,
    				'purchaseId'=>$data['purId']
    				);
    		$rowData = $dbs->getProductPOInfo($param);
    		
    		$arr = array(
    				'projectId'=>$data['branch_id'],
    				'dnType'=>1,
    				'poId'=>$data['purId'],
    				'requestId'=>$rowData['requestId'],
    				'supplierId'=>$rowData['supplierId'],
    				'receiveDate'=>$data['dnDate'],
    				'dnNumber'=>$data['dnTitle'],
    				'staffCounter'=>$data['counter'],
    				'driverName'=>$data['driver'],
    				'plateNo'=>$data['truckNumber'],
    				'note'=>$data['note'],
    				'userId'=>$this->getUserId(),
    				'createDate'=>date('Y-m-d'),
    			);
    		$receivedId = $this->insert($arr);
    		
    		$ids = explode($data['identity'], ',');
    		if(!empty($ids)){
    			foreach($ids as $i){
    				$arr = array(
    					'receiveId'=>$receivedId,
    					'proId'=>$data['productId'.$i],
    					'qtyReceive'=>$data['qtyReceive'.$i],
    					'qtyAfterReceive'=>$data['qtyAfter1'.$i]-$data['qtyReceive'.$i],
    					'price'=>$data['price'.$i],
    					'subTotal'=>$data['qtyReceive'.$i]*$data['price'.$i],
    					'isClosed'=>$data['receiveStatus'.$i],
    					'note'=>$data['note'.$i],
    				);
    				
    				$this->_name='st_receive_stock_detail';
    				$id = $this->insert($arr);
    				
    				$param = array(
    					'branch_id'=>$data['branch_id'],
    					'productId'=>$data['productId'.$i],
    					'EntyQty'=>$data['qtyReceive'.$i]
    				);
    				
    				$dbs->updateStockbyBranchAndProductId($param);//Update Stock qty 
    				
    				$dbs->addProductHistoryQty($data['branch_id'],$data['productId'.$i],2,$data['qtyReceive'.$i],$id);//movement
    			}
    		}
    		
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
    function checkPOStatus($poId){
    	
    }
    function updateData($data){
    	 
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$arr = array(
    				''=>''
    
    		);
    		
    		//$this->_name='';
    		$where = 'client_id = '.$data['id'];
			$this->update($arr, $where);
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
    function getAllProductByPO($data){
    	$db = $this->getAdapter();
    	
    	$db = new Application_Model_DbTable_DbGlobalStock();
    	$rs = $db->getProductPOInfo($data);
    
    	$string='';
    	$no = 1;
    	$identity='';
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	
    	if(!empty($rs)){
    	foreach ($rs as $key => $row){
	    	if (empty($identity)){
	    		$identity=$no;
	    	}else{
	    		$identity=$identity.",".$no;
	    	}
	    
	    		$classRowBg = "odd";
	    	if(($key%2)==0){
	    			$classRowBg = "regurlar";
	    	}
	    
	    	$Message="rangeMessage:'".$tr->translate('CAN_NOT_RECEIVE_OVER')."'";
	    		$string.='
		    		<tr id="row'.$no.'" class="rowData '.$classRowBg.'" >
			    		<td align="center" style="padding: 0 10px;"><input OnChange="CheckAllTotal('.$no.')" style="vertical-align: top; height: initial;" type="checkbox" class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]"/></td>
			    		<td class="textCenter">'.($key+1).'</td>
			    		<td class="textCenter">'.$row['proName'].'('.$row['measureLabel'].')</td>
		    			<td><input type="text" class="fullside" readonly dojoType="dijit.form.NumberTextBox" required="required" name="qtyPO'.$no.'" id="qtyPO'.$no.'" value="'.$row['qty'].'" style="text-align: center;" >
		    				<input type="hidden" class="fullside" name="productId'.$no.'" id="productId'.$no.'" value="'.$row['proId'].'" style="text-align: center;" >
		    				<input type="hidden" class="fullside" name="price'.$no.'" id="price'.$no.'" value="'.$row['unitPrice'].'" style="text-align: center;" >
		    			</td>
		    			<td><input type="text" class="fullside" readonly dojoType="dijit.form.NumberTextBox" required="required" name="qtyAfter'.$no.'" id="qtyAfter'.$no.'" value="'.$row['qtyAfter'].'" style="text-align: center;" ></td>
		    			<td><input type="text" class="fullside" data-dojo-props="constraints:{min:0,max:'.$row['qtyAfter'].'},'.$Message.'" dojoType="dijit.form.NumberTextBox" required="required" name="qtyReceive'.$no.'" id="qtyReceive'.$no.'" value="'.$row['qtyAfter'].'" style="text-align: center;" ></td>
		    			<td><select dojoType="dijit.form.FilteringSelect" class="fullside" name="receiveStatus'.$no.'" id="receiveStatus'.$no.'" >
		    					<option value="1">ទទួលគ្រប់</option>
		    					<option value="0">ទទួលមិនគ្រប់</option>
		    				</select>
		    			</td>
		    			<td><input type="text" class="fullside" dojoType="dijit.form.TextBox" name="note'.$no.'" id="note'.$no.'" /></td>
		    			</tr>
	    			';
	    		$no++;
	    	}
	    	}else{
	    		$no++;
	    	}
	    	$data['fetchRow']=1;
	    	$rowData = $db->getProductPOInfo($data);
	    	
	    	$strPOInfo = '
	    		<div class="form-group" style="padding: 4px !important;">
	             	<span class="note_score">&nbsp;&nbsp; <i class="fa fa-file-text-o" aria-hidden="true"></i>&nbsp;&nbsp;
	                   	'.$tr->translate("PURCHASING_INFO").'</span>
                   		 <ul>
                   		 	<li><span class="lbl-tt">'.$tr->translate("PO_DATE").'</span>: <span class="red">'.$rowData['createDate'].'</span></li>
                   		 	<li><span class="lbl-tt">'.$tr->translate("PO_NO").'</span>: <span class="red">'.$rowData['purchaseNo'].'</span></li>
                   		 	<li><span class="lbl-tt">'.$tr->translate("SUPPLIER_NAME").'</span>: <span class="red">'.$rowData['supplierName'].'</span></li>
                   			<li><span class="lbl-tt">'.$tr->translate("REQUEST_NO").'</span>: <span class="red">'.$rowData['requestNo'].'</span></li>
                   		</ul>
             	</div>';
    	
    	$array = array('stringrow'=>$string,'POInfoDataBlog'=>$strPOInfo);
    	return $array;
    }
    function getDataRow($recordId){
    	$db = $this->getAdapter();
    	
    	$sql=" SELECT * FROM $this->_name WHERE id=".$recordId." LIMIT 1";
    	return $db->fetchRow($sql);
    }
   
}