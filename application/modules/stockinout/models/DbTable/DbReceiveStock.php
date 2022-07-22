<?php

class Stockinout_Model_DbTable_DbReceiveStock extends Zend_Db_Table_Abstract
{
    protected $_name = '';
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
   
    function addData($data){
    	
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$arr = array(
    				''=>''
    				
    				);
    		//$this->_name='';
    		$id = $this->insert($arr);
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
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
    	$purchaseId = $data['purchaseId'];
    	$sql="SELECT 
				p.id,
				pd.purchaseId,
				(SELECT proName FROM `st_product` WHERE st_product.proId=pd.proId LIMIT 1) AS proName,
				(SELECT measureLabel FROM `st_product` WHERE st_product.proId=pd.proId LIMIT 1) AS measureLabel,
				pd.qty,
				pd.qtyAfter,
				pd.unitPrice,
				pd.subTotal,
				pd.requestInDate,
				pd.isClosed
			FROM 
				`st_purchasing` p,
				`st_purchasing_detail` pd
			WHERE 
				p.id=pd.purchaseId
				AND pd.purchaseId=	".$purchaseId;
    
    	$rs = $db->fetchAll($sql);
    	 
    	$string='';
    	$no = 1;
    	$identity='';
    	
    	$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
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
	    		$string.='
		    		<tr id="row'.$no.'" class="rowData '.$classRowBg.'" >
			    		<td align="center" style="padding: 0 10px;"><input OnChange="CheckAllTotal('.$no.')" style="vertical-align: top; height: initial;" type="checkbox" class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]"/></td>
			    		<td class="textCenter">'.($key+1).'</td>
			    		<td class="textCenter">'.$row['proName'].'('.$row['measureLabel'].')</td>
		    			<td><input type="text" class="fullside" readonly dojoType="dijit.form.NumberTextBox" required="required" name="qtyPO'.$no.'" id="qtyPO'.$no.'" value="'.$row['qty'].'" style="text-align: center;" >
		    				<input type="hidden" class="fullside" name="price'.$no.'" id="price'.$no.'" value="'.$row['unitPrice'].'" style="text-align: center;" >
		    			</td>
		    			<td><input type="text" class="fullside" readonly dojoType="dijit.form.NumberTextBox" required="required" name="qtyAfter'.$no.'" id="qtyAfter'.$no.'" value="'.$row['qtyAfter'].'" style="text-align: center;" ></td>
		    			<td><input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateamount('.$no.');" name="qtyReceive'.$no.'" id="qtyReceive'.$no.'" value="'.$row['qtyAfter'].'" style="text-align: center;" ></td>
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
    	
    	//,'keyindex'=>$no,'identity'=>$identity,'gTotalBalance'=>$gTotalBalance
    	$array = array('stringrow'=>$string);
    	return $array;
    }
    function getDataRow($recordId){
    	$db = $this->getAdapter();
    	
    	$sql=" SELECT * FROM $this->_name WHERE id=".$recordId." LIMIT 1";
    	return $db->fetchRow($sql);
    }
   
}