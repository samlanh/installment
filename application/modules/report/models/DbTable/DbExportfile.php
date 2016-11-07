<?php
class Report_Model_DbTable_DbExportfile extends Zend_Db_Table_Abstract
{
      public function getFileby($table,$data,$datahead){
		$db = $this->getAdapter(); 
// 		foreach ($datahead as $index =>$rs){
// 			echo $datahead[$index]."<br />";
// 		}
// 		exit();
//       	$db = new Application_Model_DbTable_DbGlobal();
//       	$sql = "SHOW COLUMNS FROM $table ";
//       	$datahead=$db->getGlobalDb($sql);
//       	print_r($datahead);exit();
// //       	print_r($datahead);exit();
//       	foreach($datahead as $id =>$rs){
//       		$thead[]=$rs['Field'];
//       	}
//       	print_r($thead);exit();
//       	foreach($datahead as $id =>$rs){
//       		echo $rs[$id]."<br />";
//       	}
//       	exit();
      	foreach ($data  AS $index => $row)
      	{
      		foreach ($datahead as $id =>$rs){
      			$finalData[$index][$id]=utf8_decode($row[$datahead[$id]]);
      		}
      	}
      	return $finalData;
      }
 }

