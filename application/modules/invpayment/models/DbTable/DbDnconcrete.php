<?php

class Invpayment_Model_DbTable_DbDnconcrete extends Zend_Db_Table_Abstract
{
    protected $_name = '';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getDnConcreteList($data){
    	$db = $this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    
    	$supplierId = empty($data['supplierId'])?0:$data['supplierId'];
    	$branchId = $data['branch_id'];
    
    	$sql="SELECT 
		    	 po.purchaseNo AS purchaseNo ,
		    	 po.date AS purchaseDate,
		    	 rst.dnNumber,
		    	 rst.receiveDate,
	    	 
    	";
    	$sql.="
    	FROM st_receive_stock AS rst
    		JOIN `st_purchasing`AS po ON po.id = rst.poId
    	";
    	$sql.=" WHERE rst.status=1 ";
    	$sql.=" AND rst.verified =1 ";
    	$sql.=" AND rst.projectId =$branchId ";
    	$sql.=" AND rst.supplierId =$supplierId ";
    	
    	if(!empty($data['transactionType'])){
    		$sql.=" AND rst.transactionType = ".$data['transactionType'];
    	}
    	
    	$dbiv = new Invpayment_Model_DbTable_DbInvoice();
    	$DNListFromInvoice = $dbiv->getDnIDInInvoice($data);
    	if(!empty($DNListFromInvoice)){ //For Only Available DN Of Purchase To Make Invoice
    		$sql.=" AND rst.id NOT IN ($DNListFromInvoice) ";
    	}
    
    	$rs = $db->fetchAll($sql);
    
    
    	$string='';
    	$no = $data['dnKeyIndex'];
    	$dnIdentity='';
    	$dnIdentityChecked='';
    	$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
    
    	$gTotalInternal = 0;
    	$gTotalExternal = 0;
    	$string.='<ul class="listDn">';
    	if(!empty($rs)){
    		foreach ($rs as $key => $row){
    			if (empty($dnIdentity)){
    				$dnIdentity=$no;
    			}else{$dnIdentity=$dnIdentity.",".$no;
    			}
    			if(!empty($data['dnId'])){
    				$currentInvoice = $data['dnId'];
    				if(!empty($currentInvoice)){
    					$dnIds = explode(",",$currentInvoice);
    					$true=0;
    					foreach ($dnIds as $iid){
    						if($iid==$row['id']){
    							if (empty($dnIdentityChecked)){
    								$dnIdentityChecked=$no;
    							}else{$dnIdentityChecked=$dnIdentityChecked.",".$no;
    
    							}
    							$true=1;
    							$string.='
    							<li>
    							<div class="custom-control custom-checkbox ">
    							<input type="checkbox" class="checkbox custom-control-input" checked="checked" OnChange="checkAllDn('.$no.')"  class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]" >
    							<label class="custom-control-label dnNumber " for="mfdid_'.$no.'">
    							<span  class="dnNumber">'.$row['dnNumber'].' '.date("d-m-Y",strtotime($row['receiveDate'])).'</span>
    							</label>
    							</div>
    							<small class="poNumber">'.$tr->translate("PO_NO").' : '.$row['purchaseNo'].' '.date("d-m-Y",strtotime($row['purchaseDate'])).'</small>
    							//<small class="requestNumber">'.$tr->translate("REQUEST_NO").' : '.$row['requestNo'].' '.date("d-m-Y",strtotime($row['requestDate'])).'</small>
    							<input type="hidden" dojoType="dijit.form.TextBox" name="deliveryId'.$no.'" id="deliveryId'.$no.'" value="'.$row['id'].'" >
    							</li>
    							';
    							break;
    						}
    					}
    					if($true==0){
    						$string.='
    						<li>
    						<div class="custom-control custom-checkbox ">
    						<input type="checkbox" class="checkbox custom-control-input" OnChange="checkAllDn('.$no.')" class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]" >
    						<label class="custom-control-label dnNumber " for="mfdid_'.$no.'">
    						<span  class="dnNumber">'.$row['dnNumber'].' '.date("d-m-Y",strtotime($row['receiveDate'])).'</span>
    						</label>
    						</div>
    						<small class="poNumber">'.$tr->translate("PO_NO").' : '.$row['purchaseNo'].' '.date("d-m-Y",strtotime($row['purchaseDate'])).'</small>
    						<small class="requestNumber">'.$tr->translate("REQUEST_NO").' : '.$row['requestNo'].' '.date("d-m-Y",strtotime($row['requestDate'])).'</small>
    						<input type="hidden" dojoType="dijit.form.TextBox" name="deliveryId'.$no.'" id="deliveryId'.$no.'" value="'.$row['id'].'" >
    						</li>
    						';
    					}
    				}else{
    					$string.='
    					<li>
    					<div class="custom-control custom-checkbox ">
    					<input type="checkbox" class="checkbox custom-control-input" OnChange="checkAllDn('.$no.')" class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]" >
    					<label class="custom-control-label dnNumber " for="mfdid_'.$no.'">
    					<span  class="dnNumber">'.$row['dnNumber'].' '.date("d-m-Y",strtotime($row['receiveDate'])).'</span>
    					</label>
    					</div>
    					<small class="poNumber">'.$tr->translate("PO_NO").' : '.$row['purchaseNo'].' '.date("d-m-Y",strtotime($row['purchaseDate'])).'</small>
    					<small class="requestNumber">'.$tr->translate("REQUEST_NO").' : '.$row['requestNo'].' '.date("d-m-Y",strtotime($row['requestDate'])).'</small>
    					<input type="hidden" dojoType="dijit.form.TextBox" name="deliveryId'.$no.'" id="deliveryId'.$no.'" value="'.$row['id'].'" >
    					</li>
    					';
    				}
    			}else{
    				if (empty($dnIdentityChecked)){
    					$dnIdentityChecked=$no;
    				}else{$dnIdentityChecked=$dnIdentityChecked.",".$no;
    					
    				}
    				$string.='
    				<li>
    				<div class="custom-control custom-checkbox ">
    				<input type="checkbox" class="checkbox custom-control-input" checked="checked" OnChange="checkAllDn('.$no.')"  class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]" >
    				<label class="custom-control-label dnNumber " for="mfdid_'.$no.'">
    				<span  class="dnNumber">'.$row['dnNumber'].' '.date("d-m-Y",strtotime($row['receiveDate'])).'</span>
    				</label>
    				</div>
    				<small class="poNumber">'.$tr->translate("PO_NO").' : '.$row['purchaseNo'].' '.date("d-m-Y",strtotime($row['purchaseDate'])).'</small>
    				<small class="requestNumber">'.$tr->translate("REQUEST_NO").' : '.$row['requestNo'].' '.date("d-m-Y",strtotime($row['requestDate'])).'</small>
    				<input type="hidden" dojoType="dijit.form.TextBox" name="deliveryId'.$no.'" id="deliveryId'.$no.'" value="'.$row['id'].'" >
    				</li>
    				';
    			}
    			$no++;
    		}
    	}else{
    		$no++;
    	}
    	$string.='</ul>';
    
    	$array = array(
    			'stringrow'=>$string,
    			'dnKeyIndex'=>$no,
    			'dnIdentity'=>$dnIdentity,
    			'dnIdentityChecked'=>$dnIdentityChecked,
    	);
    
    	return $array;
    
    }
    function getConcreteDnData($data){
    	$db =$this->getAdapter();
    	$sql="SELECT
	    	rst.id,
	    	rst.dnNumber AS name ";
    	$sql.="
	    	FROM st_receive_stock AS rst
	    	JOIN `st_purchasing`AS po ON po.id = rst.poId
    	";
    	$sql.=" WHERE rst.status=1 ";
    	$sql.=" AND rst.verified =1 ";
    	 
    	if(!empty($data['branch_id'])){
    		$sql.=" AND rst.projectId = ".$data['branch_id'];
    	}
    	if(!empty($data['transactionType'])){
    		$sql.=" AND rst.transactionType = ".$data['transactionType'];
    	}
    	if(!empty($data['supplierId'])){
    		$sql.=" AND rst.supplierId= ".$data['supplierId'];
    	}
    	if(!empty($data['fromDate'])){
    		$sql.=" AND rst.receiveDate= ".$data['fromDate'];
    	}
    	if(!empty($data['toDate'])){
    		$sql.=" AND rst.receiveDate= ".$data['fromDate'];
    	}
    	$dbiv = new Invpayment_Model_DbTable_DbInvoice();
    	$DNListFromInvoice = $dbiv->getDnIDInInvoice($data);
    	if(!empty($DNListFromInvoice)){ 
    		$sql.=" AND rst.id NOT IN ($DNListFromInvoice) ";
    	}
    	return $db->fetchAll($sql);
    }
    
}