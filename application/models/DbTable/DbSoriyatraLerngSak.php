<?php

class Application_Model_DbTable_DbSoriyatraLerngSak extends Zend_Db_Table_Abstract
{
	const LUNAR_MONTH_LISTS = "មិគសិរ_បុស្ស_មាឃ_ផល្គុន_ចេត្រ_ពិសាខ_ជេស្ឋ_អាសាឍ_ស្រាពណ៍_ភទ្របទ_អស្សុជ_កក្ដិក_បឋមាសាឍ_ទុតិយាសាឍ";
	protected $jsYear="";
	function __construct($jsYear){
		$this->jsYear = $jsYear; 
	}
	function LunarMonths(){
		$month = explode("_", self::LUNAR_MONTH_LISTS);
		$LunarMonths = array();
		foreach ($month as $key => $index){
			$LunarMonths[$index]=$key;
				
		}
		return $LunarMonths;
	}
	function getSoriyatraLerngSak(){
		$jsYear = $this->jsYear;
		$info = $this->getInfo($jsYear);
		$has366day = $this->getHas366day($jsYear);
		$isAthikameas = $this->getIsAthikameas($jsYear);
		$isChantreathimeas = $this->getIsChantreathimeas($jsYear);
		/**
		 * រកមើលថាតើថ្ងៃឡើងស័កចំថ្ងៃអ្វី
		 * @type {number}
		 */
		$dayLerngSak = ($info['harkun'] - 2) % 7;
		$jesthHas30 = $this->jesthHas30($jsYear);
		$lunarDateLerngSak = $this->lunarDateLerngSak($jsYear);
		$newYearsDaySotins = $this->newYearsDaySotins();
		$timeOfNewYear = $this->timeOfNewYear();
		return array(
				'has366day'			=>$has366day,			// សុរិយគតិខ្មែរ
				'isAthikameas'		=>$isAthikameas,		// 13 months
				'isChantreathimeas'	=>$isChantreathimeas,	// 30ថ្ងៃនៅខែជេស្ឋ
				'jesthHas30' 		=>$jesthHas30,			// ខែជេស្ឋមាន៣០ថ្ងៃ
				'dayLerngSak'	 	=> $dayLerngSak,		// ថ្ងៃឡើងស័ក ច័ន្ទ អង្គារ ..
				'lunarDateLerngSak'	=> $lunarDateLerngSak,	// ថ្ងៃទី ខែ ឡើងស័ក
				'newYearsDaySotins'	=> $newYearsDaySotins,	// សុទិនសម្រាប់គណនាថ្ងៃចូលឆ្នាំ ថ្ងៃវ័នបត និង ថ្ងៃឡើងស័ក
				'timeOfNewYear'		=>$timeOfNewYear,		// ម៉ោងទេវតាចុះ
				);
	}
 /**
     * គណនា ហារគុន Kromathopol អវមាន និង បូតិថី
     * @param jsYear
     * @returns {{bodithey: number, avaman: number, kromathopol: number, harkun: number}}
     */
    function getInfo($jsYear) {
      $h = 292207 * $jsYear + 373;
      $harkun = floor($h / 800) + 1;
      $kromathopol = 800 - ($h % 800);

      $a = 11 * $harkun + 650;
      $avaman = $a % 692;
      $bodithey = ($harkun + floor(($a / 692))) % 30;
      $arr = array('harkun'=>$harkun,'kromathopol'=>$kromathopol,'avaman'=>$avaman,'bodithey'=>$bodithey);
      return $arr;
    }

    

    /**
     * ឆ្នាំចុល្លសករាជដែលមាន៣៦៦ថ្ងៃ
     * @param jsYear
     * @returns {boolean}
     */
    function getHas366day($jsYear) {
      $infoOfYear = $this->getInfo($jsYear);
      return $infoOfYear['kromathopol'] <= 207;
    }

    /**
     * រកឆ្នាំអធិកមាស
     * @param jsYear
     * @returns {boolean}
     */
    function getIsAthikameas($jsYear) {
      $infoOfYear = $this->getInfo($jsYear);
      $infoOfNextYear = $this->getInfo($jsYear + 1);
      return (!($infoOfYear['bodithey'] == 25 && $infoOfNextYear['bodithey'] == 5) &&
        ($infoOfYear['bodithey'] > 24 ||
          $infoOfYear['bodithey'] < 6 ||
          ($infoOfYear['bodithey'] == 24 &&
            $infoOfNextYear['bodithey'] == 6
          )
        )
      );
    }

    /**
     * រកឆ្នាំចន្ទ្រាធិមាស
     * @param jsYear
     * @returns {boolean}
     */
    function getIsChantreathimeas($jsYear) {
      $infoOfYear = $this->getInfo($jsYear);
      $infoOfNextYear = $this->getInfo($jsYear + 1);
      $infoOfPreviousYear = $this->getInfo($jsYear - 1);
      $has366day = $this->getHas366day($jsYear);
      return (($has366day && $infoOfYear['avaman'] < 127) ||
        (!($infoOfYear['avaman'] == 137 &&
            $infoOfNextYear['avaman'] == 0) &&
          ((!$has366day &&
              $infoOfYear['avaman'] < 138) ||
            ($infoOfPreviousYear['avaman'] == 137 &&
              $infoOfYear['avaman'] == 0
            )
          )
        )
      );
    }
    
    /**
     * ឆែកមើលថាជាឆ្នាំដែលខែជេស្ឋមាន៣០ថ្ងៃឬទេ
     * @type {boolean}
     */
    function jesthHas30($jsYear){
    	$isChantreathimeas = $this->getIsChantreathimeas($jsYear);
    	$isAthikameas = $this->getIsAthikameas($jsYear);
    	$tmp = $isChantreathimeas;
    	if ($isAthikameas && $isChantreathimeas) {
    		$tmp = false;
    	}
    	if (!$isChantreathimeas && $this->getIsAthikameas($jsYear - 1) && $this->getIsChantreathimeas($jsYear - 1)) {
    		$tmp = true;
    	}
    	return $tmp;
    }

    /**
     * គណនារកថ្ងៃឡើងស័ក
     * @type {{month, day}}
     */
  function lunarDateLerngSak($jsYear){
  	$info = $this->getInfo($jsYear);
  	$bodithey = $info['bodithey'];
  	if ($this->getIsAthikameas($jsYear - 1) && $this->getIsChantreathimeas($jsYear - 1)) {
  		$bodithey = ($bodithey + 1) % 30;
  	}
  	$LunarMonths = $this->LunarMonths();
  	return array(
  			'day'=>$bodithey >= 6 ? $bodithey - 1 : $bodithey,
  			'month' => $bodithey >= 6 ? $LunarMonths['ចេត្រ'] : $LunarMonths['ពិសាខ']
  			);
  }
  
  function sunAverageAsLibda($sotin){// មធ្យមព្រះអាទិត្យ គិតជាលិប្ដា
	  	$jsYear = $this->jsYear;
	  	$infoOfPreviousYear = $this->getInfo($jsYear - 1);
	  	$r2 = 800 * $sotin + $infoOfPreviousYear['kromathopol'];
	  	$reasey = floor($r2 / 24350); // រាសី
	  	$r3 = $r2 % 24350;
	  	$angsar = floor($r3 / 811); // អង្សា
	  	$r4 = $r3 % 811;
	  	$l1 = floor($r4 / 14);
	  	$libda = $l1 - 3; // លិប្ដា
  	return (30 * 60 * $reasey) + (60 * $angsar) + $libda;
  }
  function leftOver($sunAverageAsLibda){
  	$s1 = ((30 * 60 * 2) + (60 * 20));
  	$leftOver = $sunAverageAsLibda - $s1; // មធ្យមព្រះអាទិត្យ - R2.A20.L0
  	if ($sunAverageAsLibda < $s1) { // បើតូចជាង ខ្ចី ១២ រាសី
  		$leftOver += (30 * 60 * 12);
  	}
  	return $leftOver;
  }
  function chhayaSun($khan){
  	$multiplicities = array(35, 32, 27, 22, 13, 5);
  	$chhayas = array(0, 35, 67, 94, 116, 129);
  	switch ($khan) {
  		case 0:
  		case 1:
  		case 2:
  		case 3:
  		case 4:
  		case 5:
  			return array(
  			'multiplicity'=>$multiplicities[$khan],
  			'chhaya'=>$chhayas[$khan]
  			);
  			
  		default:
  			return array(
  					'chhaya'=>134
  			);
  	}
  }
  
  function getSunInfo($sotin) {  // សុទិន
  	$jsYear = $this->jsYear;
  	$infoOfPreviousYear = $this->getInfo($jsYear - 1);
  	// ១ រាសី = ៣០ អង្សា
  	// ១ អង្សា = ៦០ លិប្ដា
  	$sunAverageAsLibda = $this->sunAverageAsLibda($sotin);
  	$leftOver = $this->leftOver($sunAverageAsLibda);
  	$kaen = floor($leftOver / (30 * 60));
  	
  	$rs = -1;
  	if (in_array($kaen, array(0, 1, 2))) {
  		$rs = $kaen;
  	}else if (in_array($kaen, array(3, 4, 5))) {
  		$rs = (30 * 60 * 6) - $leftOver; // R6.A0.L0 - leftover
  	}else if (in_array($kaen, array(6, 7, 8))) {
  		$rs = $leftOver - (30 * 60 * 6); // leftover - R6.A0.L0
  	}else if (in_array($kaen, array(9, 10, 11))) {
  		$rs = ((30 * 60 * 11) + (60 * 29) + 60) - $leftOver; // R11.A29.L60 - leftover
  	}
  	$lastLeftOver = array('reasey'=>floor($rs / (30 * 60)),'angsar'=>floor(($rs % (30 * 60)) / (60)),'libda'=>($rs % 60) );
  	
  	$khan=''; $pouichalip='';
  	if ($lastLeftOver['angsar'] >= 15) {// ខណ្ឌ និង pouichalip
  		$khan = 2 * $lastLeftOver['reasey'] + 1;
  		$pouichalip = 60 * ($lastLeftOver['angsar'] - 15) + $lastLeftOver['libda'];
  	}else{
  		$khan = 2 * $lastLeftOver['reasey'];
  		$pouichalip = 60 * $lastLeftOver['angsar'] + $lastLeftOver['libda'];
  	}
  	$phol =array();
  	$val = $this->chhayaSun($khan);
  	$q = floor(($pouichalip * $val['multiplicity']) / 900);
  	$phol = array(
  			'reasey'=>0,
  			'angsar'=>floor(($q + $val['chhaya']) / 60),
  			'libda'=>($q + $val['chhaya']) % 60
  			);
  	
  	$sunInaugurationAsLibda="";// សម្ពោធព្រះអាទិត្យ
  	$pholAsLibda = (30 * 60 * $phol['reasey']) + (60 * $phol['angsar']) + $phol['libda'];
  	if ($kaen <= 5) {
  		$sunInaugurationAsLibda =  $sunAverageAsLibda - $pholAsLibda;
  	} else {
  		$sunInaugurationAsLibda =  $sunAverageAsLibda + $pholAsLibda;
  	}
  	
  	return array(
  			'sunAverageAsLibda'=>$sunAverageAsLibda,
  			'khan'=>$khan,
  			'pouichalip'=>$pouichalip,
  			'phol'=>$phol,
  			'sunInaugurationAsLibda'=>$sunInaugurationAsLibda
  			
  			);
  
  }
  
  function newYearsDaySotins(){// ចំនួនថ្ងៃវ័នបត
  	$jsYear = $this->jsYear;
  	$sotins = $this->getHas366day($jsYear - 1) ? array(363,364,365,366) : array(362,363,364,365); // សុទិន
  	
  	$names = array_map(
  			function($sotin) {
  		$sunInfo = $this->getSunInfo($sotin);
		return array(
				'sotin'=>$sotin,
				'reasey'=>floor($sunInfo['sunInaugurationAsLibda'] / (30 * 60)),
				'angsar'=>floor(($sunInfo['sunInaugurationAsLibda'] % (30 * 60)) / (60)),
				'libda'=>$sunInfo['sunInaugurationAsLibda'] % 60
				);
  	},
  	$sotins
  	);
  	return $names;
  }
  function timeOfNewYear(){
  	$newYearsDaySotins = $this->newYearsDaySotins();
  	$sotinNewYear = array_filter($newYearsDaySotins, function ($sotin) {
  		return ($sotin['angsar'] == 0);
  	});
  	$sotinNewYear = array_values($sotinNewYear);
  	if (count($sotinNewYear) == 1) {
  		$libda = $sotinNewYear[0]['libda']; // ២៤ ម៉ោង មាន ៦០លិប្ដា
  		$minutes = (24 * 60) - ($libda * 24);
  		return array(
  				'hour' =>floor($minutes / 60),
  				'minute' =>$minutes % 60
  				);
  	} else {
  		///Error('Plugin is facing wrong calculation on new years hour');
  	}

  }
 
}
?>