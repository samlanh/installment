<?php

class Application_Model_DbTable_DbRate extends Zend_Db_Table_Abstract
{

    protected $_name = 'cs_rate';
    
    /**
     * Get current rate 
     * @return array(6);
     */
    function getCurrentRate(){
    	$db = $this->getAdapter();
    	$sql = "SELECT `id`,`in_cur_id`,`out_cur_id`,`rate_in`,`rate_out`
				FROM `cs_rate` as r
    			WHERE r.`active` = 1
				ORDER BY r.`in_cur_id`, r.`out_cur_id`";
    	$rows = $db->fetchAll($sql);
    	
    	$result =array(
    					'DB'=>0,
		    			'BD'=>0,
		    			'DR'=>0,
		    			'RD'=>0,
		    			'BR'=>0,
		    			'RB'=>0
    				);
		foreach ($rows AS $i => $r){
			echo "<br/><br/>";
			if($r['in_cur_id'] == 1 && $r['out_cur_id'] == 2){
				$result['DB'] = $r['rate_in'];
				$result['BD'] = $r['rate_out'];
			}
			elseif($r['in_cur_id'] == 1 && $r['out_cur_id'] == 3){
				$result['DR'] = $r['rate_in'];
				$result['RD'] = $r['rate_out'];				
			}
			elseif($r['in_cur_id'] == 2 && $r['out_cur_id'] == 3){
				$result['BR'] = $r['rate_in'];
				$result['RB'] = $r['rate_out'];
			}
		}
    	return $result;
    }
    
    /**
     * Get Current Rate 
     * @return JSON Data
     */
    function getCurrentRateJson(){
    	$db = $this->getAdapter();
    	$sql = "SELECT `id`,`in_cur_id`,`out_cur_id`,`rate_in`,`rate_out`
				FROM `ln_exchangerate` as r
    			WHERE r.`active` = 1
				ORDER BY r.`in_cur_id`, r.`out_cur_id`";
    	$rows = $db->fetchAll($sql);
    	return Zend_Json::encode($rows);
    }
    
    function setNewRate($data){
    	$db = $this->getAdapter();
    	$db_loan = new Application_Model_DbTable_DbLoan();
    	$db->beginTransaction();
    	try {
    		$date = date("Y-m-d H:i:s");
    		$_data=array(
			   			"rate_in"=>$data['DB'],
			   			"rate_out"=>$data['BD'],
    					"create_date"=>$date
			   			);
	    	$where = "in_cur_id=1 AND out_cur_id=2";
	    	$this->update($_data, $where);
	    	
	    	$_data=array(
	    			"rate_in"=>$data['DR'],
	    			"rate_out"=>$data['RD'],
	    			"create_date"=>$date
	    	);
	    	$where = "in_cur_id=1 AND out_cur_id=3";
	    	$this->update($_data, $where);
	    	
	    	$_data=array(
	    			"rate_in"=>$data['BR'],
	    			"rate_out"=>$data['RB'],
	    			"create_date"=>$date
	    	);
	    	$where = "in_cur_id=2 AND out_cur_id=3";
	    	$this->update($_data, $where);
    		return  $db->commit();
    	} catch (Exception $e) {
    		$db->rollBack();
//     		echo $e->getMessage(); exit;
    	}
    }
 	
}


