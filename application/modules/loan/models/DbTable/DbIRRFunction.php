<?php

class Loan_Model_DbTable_DbIRRFunction extends Zend_Db_Table_Abstract
{
	public static function IRR($values, $guess=0.1) {
		// Credits: algorithm inspired by Apache OpenOffice
	
		// Initialize dates and check that values contains at least one positive value and one negative value
		$dates = array();
		$positive = false;
		$negative = false;
// 		print_r($values);exit();
		foreach($values as $index=>$value){
			$dates[] = ($index===0) ? 0 : $dates[$index-1] + 365;
			if($values[$index] > 0) $positive = true;
			if($values[$index] < 0) $negative = true;
		}
	
		// Return error if values does not contain at least one positive value and one negative value
		if(!$positive || !$negative) return null;
	
		// Initialize guess and resultRate
		$resultRate = $guess;
	
		// Set maximum epsilon for end of iteration
		$epsMax = 0.0000000001;
	
		// Set maximum number of iterations
		$iterMax = 50;
	
		// Implement Newton's method
		$newRate;
		$epsRate;
		$resultValue;
		$iteration = 0;
		$contLoop = true;
		while($contLoop && (++$iteration < $iterMax)){
			$resultValue = self::irrResult($values, $dates, $resultRate);
			$newRate = $resultRate - $resultValue / self::irrResultDeriv($values, $dates, $resultRate);
			$epsRate = abs($newRate - $resultRate);
			$resultRate = $newRate;
			$contLoop = ($epsRate > $epsMax) && (abs($resultValue) > $epsMax);
		}
	
		if($contLoop) return null;
	
		// Return internal rate of return
		return $resultRate;
	}
	
	// Calculates the resulting amount
	public static function irrResult($values, $dates, $rate){
		$r = $rate + 1;
		$result = $values[0];
		for($i=1;$i<count($values);$i++){
			$result += $values[$i] / pow($r, ($dates[$i] - $dates[0]) / 365);
		}
		return $result;
	}
	
	// Calculates the first derivation
	public static function irrResultDeriv($values, $dates, $rate){
		$r = $rate + 1;
		$result = 0;
		for($i=1;$i<count($values);$i++){
			$frac = ($dates[$i] - $dates[0]) / 365;
			$result -= $frac * $values[$i] / pow($r, $frac + 1);
		}
		return $result;
	}
	
	
}

