<?php

class Application_Model_DbTable_DbLunaCalendar extends Zend_Db_Table_Abstract
{
	const LUNAR_MONTH_LISTS = "មិគសិរ_បុស្ស_មាឃ_ផល្គុន_ចេត្រ_ពិសាខ_ជេស្ឋ_អាសាឍ_ស្រាពណ៍_ភទ្របទ_អស្សុជ_កក្ដិក_បឋមាសាឍ_ទុតិយាសាឍ";
	const MONTHS = "មករា_កុម្ភៈ_មីនា_មេសា_ឧសភា_មិថុនា_កក្កដា_សីហា_កញ្ញា_តុលា_វិច្ឆិកា_ធ្នូ";
	const MOON_STATUS = "កើត_រោច";
	const MOON_STATUS_SHORT = "ក_រ";
	const MOON_DAY = "᧡_᧢_᧣_᧤_᧥_᧦_᧧_᧨_᧩_᧪_᧫_᧬_᧭_᧮_᧯_᧱_᧲_᧳_᧴_᧵_᧶_᧷_᧸_᧹_᧺_᧻_᧼_᧽_᧾_᧿";
	
	const WEEKDAYS = "អាទិត្យ_ច័ន្ទ_អង្គារ_ពុធ_ព្រហស្បតិ៍_សុក្រ_សៅរ៍";
	const WEEKDAYS_SHORT = "អា_ច_អ_ព_ព្រ_សុ_ស";
 	const ANIMAL_YEAR = "ជូត_ឆ្លូវ_ខាល_ថោះ_រោង_ម្សាញ់_មមីរ_មមែ_វក_រកា_ច_កុរ";
 	
	const ERA_Year ="សំរឹទ្ធិស័ក_ឯកស័ក_ទោស័ក_ត្រីស័ក_ចត្វាស័ក_បញ្ចស័ក_ឆស័ក_សប្តស័ក_អដ្ឋស័ក_នព្វស័ក";
	protected $config="";
	protected $moment="";
	protected $Caching="";
	function __construct($moment){
		$this->config = $this->format_configs();
		$this->moment = $moment;
		$this->Caching = $moment; //initailize caching
	}
	
	function khNewYearMoments(){ // ឆ្នាំលើកលែងមួយចំនួនដែលខុសពីការគណនា
		$arr = array(
				'1879' => '12-04-1879 11:36',
				'2011' => '14-04-2011 13:12',
				'2012' => '14-04-2012 19:11',
				'2013' => '14-04-2013 02:12',
				'2014' => '14-04-2014 08:07',
				'2015' => '14-04-2015 14:02'
				);
		return $arr;
	}
	function SolarMonth(){
		$month = explode("_", self::MONTHS);
		$SolarMonth = array();
		foreach ($month as $key => $index){
			$SolarMonth[$index]=$key;
				
		}
		return $SolarMonth;
	}
	
	function LunarMonths(){
		$month = explode("_", self::LUNAR_MONTH_LISTS);
		$LunarMonths = array();
		foreach ($month as $key => $index){
			$LunarMonths[$index]=$key;
			
		}
		return $LunarMonths;
	}
	function MoonStatus(){
		$moon = explode("_", self::MOON_STATUS);
		$MoonStatus = array();
		foreach ($moon as $key => $index){
			$MoonStatus[$index]=$key;
		}
		return $MoonStatus;
	}
	
	function format_configs(){
		 $symbolMap = array('1'=> '១',
			'2'=> '២',
			'3'=> '៣',
			'4'=> '៤',
			'5'=> '៥',
			'6'=> '៦',
			'7'=> '៧',
			'8'=> '៨',
			'9'=> '៩',
			'0'=> '០');
			
		$numberMap = array(
			'១'=> '1',
			'២'=>'2',
			'៣'=> '3',
			'៤'=>'4',
			'៥'=>'5',
			'៦'=>'6',
			'៧'=>'7',
			'៨'=>'8',
			'៩'=>'9',
			'០'=>'0'
		);
		
		$arr_config=array(
				'months'=>explode("_", self::MONTHS),
				'monthsShort'=>explode("_", self::MONTHS),
				'moonDays'=>explode("_", self::MOON_DAY),
				'moonStatus'=>explode("_", self::MOON_STATUS),
				'moonStatusShort'=>explode("_", self::MOON_STATUS_SHORT),
				
				'weekdays'=>explode("_", self::WEEKDAYS),
				'weekdaysShort'=>explode("_", self::WEEKDAYS_SHORT),
				'lunarMonths'=>explode("_", self::LUNAR_MONTH_LISTS),
				'animalYear'=>explode("_", self::ANIMAL_YEAR),
				'eraYear'=>explode("_", self::ERA_Year),
				
				'preparse'=>$numberMap,
				'postformat'=>$numberMap,
				);
		return $arr_config;
	}
/**
   * Bodithey: បូតិថី
   * Bodithey determines if a given beYear is a leap-month year. Given year target year in Buddhist Era
   * @return Number (0-29)
   */
  function getBodithey($beYear) {
    $ahk = $this->getAharkun($beYear);
    $avml = floor((11 * $ahk + 25)  / 692);
   	$m = $avml + $ahk + 29;
    return ($m % 30);
  }

  /**
   * Avoman: អាវមាន
   * Avoman determines if a given year is a leap-day year. Given a year in Buddhist Era as denoted as adYear
   * @param beYear (0 - 691)
   */
  function getAvoman($beYear) {
  	$ahk = $this->getAharkun($beYear);
  	$avm = (11 * $ahk + 25)  % 692;
  	return $avm;
  }

  /**
   * Aharkun: អាហារគុណ ឬ ហារគុណ
   * Aharkun is used for Avoman and Bodithey calculation below. Given adYear as a target year in Buddhist Era
   * @param beYear
   * @returns {number}
   */
  function getAharkun($beYear) {
    $t = $beYear * 292207 + 499;
    $ahk = floor($t / 800) + 4;
    return $ahk;
  }

  /**
   * Kromathupul
   * @param beYear
   * @returns {number} (1-800)
   */
  function kromthupul($beYear) {
    $ah = $this->getAharkunMod($beYear);
    $krom = 800 - $ah;
    return $krom;
  }

  /**
   * isKhmerSolarLeap
   * @param beYear
   * @returns {number}
   */
  function isKhmerSolarLeap($beYear) {
    $krom = $this->kromthupul($beYear);
    if ($krom <= 207)
      return 1;
    else
      return 0;
  }

  /**
   * getAkhakunMod
   * @param beYear
   * @returns {number}
   */
  function getAharkunMod($beYear) {
    $t = $beYear * 292207 + 499;
    $ahkmod = $t % 800;
    return $ahkmod;
  }

  /**
   * * Regular if year has 30 day
   * * leap month if year has 13 months
   * * leap day if Jesth month of the year has 1 extra day
   * * leap day and month: both of them
   * @param beYear
   * @returns {number} return 0:regular, 1:leap month, 2:leap day, 3:leap day and month
   */
  function getBoditheyLeap($beYear) {
    $result = 0;
    $avoman = $this->getAvoman($beYear);
    $bodithey = $this->getBodithey($beYear);

    // check bodithey leap month
    $boditheyLeap = 0;
    if ($bodithey >= 25 || $bodithey <= 5) {
      $boditheyLeap = 1;
    }
    // check for avoman leap-day based on gregorian leap
    $avomanLeap = 0;
    if ($this->isKhmerSolarLeap($beYear)) {
      if ($avoman <= 126)
        $avomanLeap = 1;
    } else {
      if ($avoman <= 137) {
        // check for avoman case 137/0, 137 must be normal year (p.26)
        if ($this->getAvoman($beYear + 1) === 0) {
          $avomanLeap = 0;
        } else {
          $avomanLeap = 1;
        }
      }
    }

    // case of 25/5 consecutively
    // only bodithey 5 can be leap-month, so set bodithey 25 to none
    if ($bodithey === 25) {
      $nextBodithey = $this->getBodithey($beYear + 1);
      if ($nextBodithey === 5) {
        $boditheyLeap = 0;
      }
    }

    // case of 24/6 consecutively, 24 must be leap-month
    if ($bodithey == 24) {
      $nextBodithey = $this->getBodithey($beYear + 1);
      if ($nextBodithey == 6) {
        $boditheyLeap = 1;
      }
    }

    // format leap result (0:regular, 1:month, 2:day, 3:both)
    if ($boditheyLeap == 1 && $avomanLeap == 1) {
      $result = 3;
    } else if ($boditheyLeap === 1) {
      $result = 1;
    } else if ($avomanLeap === 1) {
      $result = 2;
    } else {
      $result = 0;
    }

    return $result;
  }

  // return 0:regular, 1:leap month, 2:leap day (no leap month and day together)
  /**
   * bodithey leap can be both leap-day and leap-month but following the khmer calendar rule, they can't be together on the same year, so leap day must be delayed to next year
   * @param beYear
   * @returns {number}
   */
  function getProtetinLeap($beYear) {
    $b = $this->getBoditheyLeap($beYear);
    if ($b === 3) {
      return 1;
    }
    if ($b === 2 || $b === 1) {
      return $b;
    }
    // case of previous year is 3
    if ($this->getBoditheyLeap($beYear - 1) == 3) {
      return 2;
    }
    // normal case
    return 0;
  }
  
  /**
   * Maximum number of day in Khmer Month
   * @param beMonth
   * @param beYear
   * @returns {number}
   */
  function getNumberOfDayInKhmerMonth($beMonth, $beYear) {
  	$LunarMonths = $this->LunarMonths();
  	if ($beMonth == $LunarMonths['ជេស្ឋ'] && $this->isKhmerLeapDay($beYear)) {
  		return 30;
  	}
  	if ($beMonth == $LunarMonths['បឋមាសាឍ'] || $beMonth == $LunarMonths['ទុតិយាសាឍ']) {
  		return 30;
  	}
  	
  	// មិគសិរ : 29 , បុស្ស : 30 , មាឃ : 29 .. 30 .. 29 ..30 .....
  	if(($beMonth % 2)==0){
  		return 29;
  	}else{
  		return 30;
  	}
  	//return $beMonth % 2 == 0 ? 29 : 30;
  }

  
  /**
   * Get number of day in Khmer year
   * @param beYear
   * @returns {number}
   */
  function getNumerOfDayInKhmerYear($beYear) {
  	if ($this->isKhmerLeapMonth($beYear)) {
  		return 384;
  	} else if ($this->isKhmerLeapDay($beYear)) {
  		return 355;
  	} else {
  		return 354;
  	}
  }
  
  /**
   * Get number of day in Gregorian year
   * @param adYear
   * @returns {number}
   */
  function getNumberOfDayInGregorianYear($adYear) {
  	if ($this->isGregorianLeap($adYear)) {
  		return 366;
  	} else {
  		return 365;
  	}
  }
  
  /**
   * A year with an extra month is called Adhikameas (អធិកមាស). This year has 384 days.
   * @param beYear
   * @returns {boolean}
   */
  function isKhmerLeapMonth($beYear) {
  	return $this->getProtetinLeap($beYear) == 1;
  }
  
  /**
   * A year with an extra day is called Chhantrea Thimeas (ចន្ទ្រាធិមាស) or Adhikavereak (អធិកវារៈ). This year has 355 days.
   * @param beYear
   * @returns {boolean}
   */
  function isKhmerLeapDay($beYear) {
  	return $this->getProtetinLeap($beYear) == 2;
  }
  
  /**
   * Gregorian Leap
   * @param adYear
   * @returns {boolean}
   */
  function isGregorianLeap($adYear) {
  	if ($adYear % 4 === 0 && $adYear % 100 !== 0 || $adYear % 400 === 0) {
  		return true;
  	} else {
  		return false;
  	}
  }
  
  /**
   * Buddhist Era
   * ថ្ងៃឆ្លងឆ្នាំ គឺ ១ រោច ខែពិសាខ
   * @ref http://news.sabay.com.kh/article/1039620
   * @summary: ឯកឧត្តម សេង សុមុនី អ្នកនាំពាក្យ​ក្រ​សួង​ធម្មការ និង​សាសនា​ឲ្យ​Sabay ដឹង​ថា​នៅ​ប្រ​ទេស​កម្ពុជា​ការ​ឆ្លង​ចូល​ពុទ្ធសករាជថ្មី​គឺ​កំណត់​យក​នៅ​ថ្ងៃព្រះ​ពុទ្ធយាងចូល​និព្វាន ពោល​គឺ​នៅ​ថ្ងៃ​១រោច ខែពិសាខ។
   * @param moment
   * @returns {*}
   */
  function getBEYear($moment) {
  	if ($this->khMonth($moment) > 5 || $this->khMonth($moment) == 5 && $this->khDay($moment) >= 15) {
//   		if ($this->khMonth($moment) > 5 || $this->khMonth($moment) == 5 || $this->khDay($moment) >= 15) {//wrong check from momentkh
  		return date_create($moment)->format('Y') + 544;
  	} else {
  		
  		return date_create($moment)->format('Y') + 543;
  	}
  }
  /**
   * Due to recursive problem, I need to calculate the BE based on new year's day
   * This won't be displayed on final result, it is used to find number of day in year,
   * It won't affect the result because on ខែចេត្រ និង ខែពិសាខ, number of days is the same every year
   * ពីព្រោះចូលឆ្នាំតែងតែចំខែចេត្រ​ ឬ ពិសាខ
   * @param moment
   * @returns {*}
   */
  function getMaybeBEYear($moment) {
  	$SolarMonth = $this->SolarMonth();
  	$moment = date_create($moment);
  	if ($moment->format('m') < $SolarMonth['មេសា'] + 1){
  		return $moment->format('Y') + 543;
  	}else{
  		return $moment->format('Y') + 544;
  	}
//   	if (parseInt(moment.format('M')) <= SolarMonth.មេសា + 1) {
//   		return parseInt(moment.format('YYYY')) + 543;
//   	} else {
//   		return parseInt(moment.format('YYYY')) + 544;
//   	}
  }
  
//   /**
//    * Moha Sakaraj
//    * @param adYear
//    * @returns {number}
//    */
//   function getMohaSakarajYear($adYear) {
//   	return $adYear - 77;
//   }
  
  /**
   * Jolak Sakaraj
   * @param beYear
   * @returns {number}
   */
  function getJolakSakarajYear($moment) {
  	$gregorianYear = date_create($moment)->format('Y');
  	$newYearMoment = $this->getKhNewYearMoment($gregorianYear);
  	
  	if ( date_create($newYearMoment)->diff(date_create($moment))->format('%R%a') < 0) {
  		return $gregorianYear + 543 - 1182;
  	}else{
  		return $gregorianYear + 544 - 1182;
  	}
//   	if (moment.diff($newYearMoment) < 0) {
//   		return $gregorianYear + 543 - 1182;
//   	} else {
//   		return $gregorianYear + 544 - 1182;
//   	}
  }
  
  /**
   * ១កើត ៤កើត ២រោច ១៤រោច ...
   * @param day 1-30
   * @returns {{count: number, moonStatus: number}}
   */
  function getKhmerLunarDay($day) {
  	$moonStatus = $this->MoonStatus();
  	$count= ($day % 15) + 1;
  	$moonStatus = $day > 14 ? $moonStatus['រោច'] : $moonStatus['កើត'];
  	$array = array('count'=>$count,'moonStatus'=>$moonStatus);
  	return $array;
//   	return {
//   		count: ($day % 15) + 1,
//   		moonStatus: $day > 14 ? $moonStatus['រោច'] : $moonStatus['កើត']
//   	}
  }
  
  /**
   * Turn be year to animal year
   * @param beYear
   * @returns {number}
   */
  function getAnimalYear($moment) {
  	$gregorianYear = date_create($moment)->format('Y');
  	$newYearMoment = $this->getKhNewYearMoment($gregorianYear);
  	if ( date_create($newYearMoment)->diff(date_create($moment))->format('%R%a') < 0) {
  		return ($gregorianYear + 543 + 4) % 12;
  	} else {
  		return ($gregorianYear + 544 + 4) % 12;
  	}
//   	if (moment.diff($newYearMoment) < 0) {
//   		return ($gregorianYear + 543 + 4) % 12;
//   	} else {
//   		return ($gregorianYear + 544 + 4) % 12;
//   	}
  }
  
  
  /**
   * Khmer date format handler
   * @param day
   * @param month
   * @param moment
   * @param format
   * @returns {*}
   */
  function formatKhmerDate($day, $month, $moment, $format=null) {
  		$config = $this->config;
  		$dbgb = new Application_Model_DbTable_DbGlobal();
  		if ($format == null || $format == 'undefined') {
//   			// Default date format
  				$dayOfWeek = date_create($moment)->format('w');
  				$moonDay = $this->getKhmerLunarDay($day);
  				$beYear = $this->getBEYear($moment);
  				$animalYear = $this->getAnimalYear($moment);
  				$eraYear = $this->getJolakSakarajYear($moment) % 10;
  				$string="";
  				$string.="ថ្ងៃ".$config['weekdays'][$dayOfWeek];
  				$string.=  " ".$dbgb->getNumberInkhmer($moonDay['count']);
  				$string.=  " ".$config['moonStatus'][$moonDay['moonStatus']];
  				$string.=  " ខែ".$config['lunarMonths'][$month];
  				$string.=  " ឆ្នាំ".$config['animalYear'][$animalYear];
  				$string.=  " ".$config['eraYear'][$eraYear];
  				$string.=  " ពុទ្ធសករាជ ".$dbgb->getNumberInkhmer($beYear);
  				
  				
  				$string.=  " ត្រូវនឹងថ្ងៃទី ".$dbgb->getNumberInkhmer(date("d",strtotime($moment)));
  				$string.=" ខែ".$config['months'][sprintf("%01d", date("m",strtotime($moment)))-1]." ឆ្នាំ".$dbgb->getNumberInkhmer(date("Y",strtotime($moment)));

  				return $string;
//   				return $config['postformat'];
//   			return config.postformat(`ថ្ងៃ${config.weekdays[dayOfWeek]} ${moonDay.count}${config.moonStatus[moonDay.moonStatus]} ខែ${config.lunarMonths[month]} ឆ្នាំ${config.animalYear[animalYear]} ${config.eraYear[eraYear]} ពុទ្ធសករាជ ${beYear}`);
  		} else if (gettype($format) == 'string') {
		  	// Follow the format
//   			$formatRule="";
//   			switch ($format) {
//   				case 'W': // Day of week
//   					$dayOfWeek = date_create($moment)->format('w');
//   					$formatRule = $config['weekdays'][$dayOfWeek];
//   					break;
//   				case 'w': // Day of week
//   					$dayOfWeek = date_create($moment)->format('w');
//   					$formatRule = $config['weekdaysShort'][$dayOfWeek];
//   					break;
//   				case 'd': // no determine digit
//   					$moonDay = $this->getKhmerLunarDay($day);
//   					$formatRule = $dbgb->getNumberInkhmer($moonDay['count']);
//   					break;
//   				case 'D': // minimum 2 digits
//   					$moonDay = $this->getKhmerLunarDay($day);
//   					$spp = str_split($moonDay['count']);
//   					$count = count($spp)==1 ?'0'.$moonDay['count'] : $moonDay['count'];
//   					$formatRule = $dbgb->getNumberInkhmer($count);
//   					break;
  					
//   				case 'n':
//   					$moonDay = $this->getKhmerLunarDay($day);
//   					$formatRule = $config['moonStatusShort'][$moonDay['moonStatus']];
//   					break;
//   				case 'N': 
//   					$moonDay = $this->getKhmerLunarDay($day);
//   					$formatRule = $config['moonStatus'][$moonDay['moonStatus']];
//   					break;
//   				case 'o':
//   					$formatRule = $config['moonDays'][$day];
//   					break;
//   				case 'm':
//   					$formatRule = $config['lunarMonths'][$month];
//   					break;
//   				case 'a':
//   					$animalYear = $this->getAnimalYear($moment);
//   					$formatRule = $config['animalYear'][$animalYear];
//   					break;
//   				case 'e':
//   					$eraYear =  $this->getJolakSakarajYear($moment) % 10;
//   					$formatRule = $dbgb->getNumberInkhmer($config['eraYear'][$eraYear]);
//   					break;
//   				case 'b':
//   					$formatRule = $dbgb->getNumberInkhmer($this->getBEYear($moment));
//   					break;
//   				case 'c':
//   					$formatRule = $dbgb->getNumberInkhmer(date_create($moment)->format('Y'));
//   					break;
//   				case 'j':
//   					$formatRule = $dbgb->getNumberInkhmer($this->getJolakSakarajYear($moment));
//   					break;
//   				case 'fk':
//   						$dayOfWeek = date_create($moment)->format('w');
// 		  				$moonDay = $this->getKhmerLunarDay($day);
// 		  				$beYear = $this->getBEYear($moment);
// 		  				$animalYear = $this->getAnimalYear($moment);
// 		  				$eraYear = $this->getJolakSakarajYear($moment) % 10;
// 		  				$string="";
// 		  				$string.="ថ្ងៃ".$config['weekdays'][$dayOfWeek];
// 		  				$string.=  " ".$dbgb->getNumberInkhmer($moonDay['count']);
// 		  				$string.=  " ".$config['moonStatus'][$moonDay['moonStatus']];
// 		  				$string.=  " ខែ".$config['lunarMonths'][$month];
// 		  				$string.=  " ឆ្នាំ".$config['animalYear'][$animalYear];
// 		  				$string.=  " ".$config['eraYear'][$eraYear];
// 		  				$string.=  " ពុទ្ធសករាជ ".$dbgb->getNumberInkhmer($beYear);
		  				
// 		  				$formatRule = $string;
//   						break;
//   				default:
//   					null;
//   			}
//   			return $formatRule;

  			
  			$dayOfWeek = date_create($moment)->format('w');
  			$moonDay = $this->getKhmerLunarDay($day);
  			$spp = str_split($moonDay['count']);
  			$count = count($spp)==1 ?'0'.$moonDay['count'] : $moonDay['count'];
  			
  			$beYear = $this->getBEYear($moment);
  			$animalYear = $this->getAnimalYear($moment);
  			$eraYear = $this->getJolakSakarajYear($moment) % 10;
  			
  			$formatRule = array(
  					'W'=>	$config['weekdays'][$dayOfWeek],
  					'w'=>	$config['weekdaysShort'][$dayOfWeek],
  					'd'=>	$dbgb->getNumberInkhmer($moonDay['count']),
  					'D'=>	$dbgb->getNumberInkhmer($count),
  					'n'=>	$config['moonStatusShort'][$moonDay['moonStatus']],
  					'N'=>	$config['moonStatus'][$moonDay['moonStatus']],
  					
  					'o'=>	$config['moonDays'][$day],
  					'm'=>	$config['lunarMonths'][$month],
  					'a'=>	$config['animalYear'][$animalYear],
  					'e'=>	$dbgb->getNumberInkhmer($config['eraYear'][$eraYear]),
  					'b'=>	$dbgb->getNumberInkhmer($this->getBEYear($moment)),
  					'c'=>	$dbgb->getNumberInkhmer(date_create($moment)->format('Y')),
  					'j'=>	$dbgb->getNumberInkhmer($this->getJolakSakarajYear($moment)),
  					
  					's'=>	$dbgb->getNumberInkhmer($config['months'][sprintf("%01d",date_create($moment)->format('m')-1)]),
  					'g'=>	$dbgb->getNumberInkhmer(date_create($moment)->format('d')),
  					);
  		$spp = str_split($format);
    	$return="";
    	foreach ($spp as $ss){
    		if (!empty($formatRule[$ss])){
    			$return.=$formatRule[$ss];
    		}else{
    			$return.=$ss;
    		}
    	}
    	return $return;
  		}
  }
  
  /**
   * Read Khmer lunar date
   * @param params : String (2) (input and format)
   * @or @param Object {ថ្ងៃ: ..., កើត: ..., ខែ: ..., ពស: ...}
   * @return Moment
   */
//   function readLunarDate(...params) {
//   	console.log('Now working yet')
//   }

  /**
   * Calculate date from momoentjs to Khmer date
   * @param target : Moment
   * @returns {{day: number, month: *, epochMoved: (*|moment.Moment)}}
   */
  function findLunarDate($target) {
  	/**
  	 * Epoch Date: January 1, 1900
  	 */
  	
  	$LunarMonths = $this->LunarMonths();
  	$epochMoment = "1900-1-1"; //$Moment('1/1/1900', 'D/M/YYYY');
  	$khmerMonth = $LunarMonths['បុស្ស'];
  	$khmerDay = 0; // 0 - 29 ១កើត ... ១៥កើត ១រោច ...១៤រោច (១៥រោច)
  
  	$target = date_create($target);
  	
  	$differentFromEpoch = date_create($epochMoment)->diff($target);
	$differentFromEpoch = $differentFromEpoch->format('%R%a');
  	// Find nearest year epoch
  	if ($differentFromEpoch > 0) {
//   		echo $target->diff($epochMoment)->format('%R%a');
			while (date_create($epochMoment)->diff($target)->format('%R%a') > $this->getNumerOfDayInKhmerYear($this->getMaybeBEYear(date_add(date_create($epochMoment),date_interval_create_from_date_string("+1 year"))->format('Y-m-d'))) ){
				$amount_day= $this->getNumerOfDayInKhmerYear($this->getMaybeBEYear(date_add(date_create($epochMoment),date_interval_create_from_date_string("+1 year"))->format('Y-m-d')));
				$epochMoment = date_add(date_create($epochMoment),date_interval_create_from_date_string("+$amount_day days"))->format('Y-m-d');
			}
  		
  	} else {
  		do {
  			$amount_day = $this->getNumerOfDayInKhmerYear($this->getMaybeBEYear($epochMoment));
  			$epochMoment = date_add(date_create($epochMoment),date_interval_create_from_date_string("- $amount_day days"))->format('Y-m-d');//->format('Y-m-d');
  		} while (date_create($epochMoment)->diff($target)->format('%R%a')>0);
  		
  	}
  	// Move epoch month
  	while (date_create($epochMoment)->diff($target)->format('%R%a')> $this->getNumberOfDayInKhmerMonth($khmerMonth, $this->getMaybeBEYear($epochMoment)) ){
  		
  		$amountDay = $this->getNumberOfDayInKhmerMonth($khmerMonth, $this->getMaybeBEYear($epochMoment));
  		$epochMoment = date_add(date_create($epochMoment),date_interval_create_from_date_string("+$amountDay days"))->format('Y-m-d');
  		switch ($khmerMonth) {
  			case $LunarMonths['មិគសិរ']:
  				$khmerMonth = $LunarMonths['បុស្ស'];
  				break;
  			case $LunarMonths['បុស្ស']:
  				$khmerMonth = $LunarMonths['មាឃ'];
  				break;
  			case $LunarMonths['មាឃ']:
  				$khmerMonth = $LunarMonths['ផល្គុន'];
  				break;
  			case $LunarMonths['ផល្គុន']:
  				$khmerMonth = $LunarMonths['ចេត្រ'];
  				break;
  			case $LunarMonths['ចេត្រ']:
  				$khmerMonth = $LunarMonths['ពិសាខ'];
  				break;
  			case $LunarMonths['ពិសាខ']:
  				$khmerMonth = $LunarMonths['ជេស្ឋ'];
  				break;
  			case $LunarMonths['ជេស្ឋ']: {
  				if ($this->isKhmerLeapMonth($this->getMaybeBEYear($epochMoment))) {
  					$khmerMonth = $LunarMonths['បឋមាសាឍ'];
  				} else {
  					$khmerMonth = $LunarMonths['អាសាឍ'];
  				}
  				break;
  			}
  			case $LunarMonths['អាសាឍ']:
  				$khmerMonth = $LunarMonths['ស្រាពណ៍'];
  				break;
  			case $LunarMonths['ស្រាពណ៍']:
  				$khmerMonth = $LunarMonths['ភទ្របទ'];
  				break;
  			case $LunarMonths['ភទ្របទ']:
  				$khmerMonth = $LunarMonths['អស្សុជ'];
  				break;
  			case $LunarMonths['អស្សុជ']:
  				$khmerMonth = $LunarMonths['កក្ដិក'];
  				break;
  			case $LunarMonths['កក្ដិក']:
  				$khmerMonth = $LunarMonths['មិគសិរ'];
  				break;
  			case $LunarMonths['បឋមាសាឍ']:
  				$khmerMonth = $LunarMonths['ទុតិយាសាឍ'];
  				break;
  			case $LunarMonths['ទុតិយាសាឍ']:
  				$khmerMonth = $LunarMonths['ស្រាពណ៍'];
  				break;
  			default:
  				null;
  		}
  	}
  		//$epochMoment +1 day cuse
//   			echo $epochMoment." ".$target->format('Y-m-d')."<br />";
  	$amountKHDay = $this->getNumberOfDayInKhmerMonth($khmerMonth, $this->getMaybeBEYear($epochMoment));
  	$khmerDay = floor(date_create($epochMoment)->diff($target)->format('%R%a'));
  	if($amountKHDay==$khmerDay){
  		$khmerMonth=$khmerMonth+1;
  		if ($khmerMonth>$LunarMonths['ទុតិយាសាឍ']){
  			$khmerMonth=$LunarMonths['ស្រាពណ៍'];
  		}
  		$khmerDay=0;
  	}
  	  		
  	  		$amountDay = date_create($epochMoment)->diff($target)->format('%R%a');
  	  		$epochMoment = date_add(date_create($epochMoment),date_interval_create_from_date_string("$amountDay days"))->format('Y-m-d');
  	  		$array = array('day'=>$khmerDay,'month'=>$khmerMonth,'epochMoved'=>$epochMoment,);
  	  		return $array;
  	
  }
  
  /**
   * Return khmer lunar date
   * @param format String (wanted format)
   * @return String
   * @or @param Array (wanted field) [ថ្ងៃ ថ្ងៃទី កើត/រោច ខែចន្ទគតិ ខែសុរិយគតិ ឆ្នាំសត្វ ឆ្នាំស័ក ពស គស ចស មស សីល]
   * @return Object
   */
  function toLunarDate($format=null) {
  
  	$target = $this->moment;
  	$result = $this->findLunarDate($target);
  	return $this->formatKhmerDate($result['day'], $result['month'], $target,$format);
  	
//   	return formatKhmerDate({
//   		day: result.day,
//   		month: result.month,
//   		moment: target
//   	}, format)
  }
  
  function getKhNewYearMoment($gregorianYear) {
  	
  	$khNewYearMoments = $this->khNewYearMoments();
  	if (!empty($khNewYearMoments[$gregorianYear])) {
  		// console.log('cache')
//   		return date_create($this->Caching)->format('d-m-Y H:i:s');
  		return date_create($khNewYearMoments[$gregorianYear])->format('d-m-Y H:i:s');
  	} else {
	  		// console.log('calculate')
	  		// ពីគ្រិស្ដសករាជ ទៅ ចុល្លសករាជ
	  		$jsYear = ($gregorianYear + 544) - 1182;
	  		$dbSoriya = new Application_Model_DbTable_DbSoriyatraLerngSak($jsYear);
	  		$info = $dbSoriya->getSoriyatraLerngSak($jsYear);
	  		// ចំនួនថ្ងៃចូលឆ្នាំ
	  		$numberNewYearDay ="";
	  		if ($info["newYearsDaySotins"][0]["angsar"]== 0) { // ថ្ងៃ ខែ ឆ្នាំ ម៉ោង និង នាទី ចូលឆ្នាំ
	  			// ចូលឆ្នាំ ៤ ថ្ងៃ
	  			$numberNewYearDay = 4;
	  			
// 	  			$hour = $info["timeOfNewYear"]["hour"];
// 	  			$minute = $info["timeOfNewYear"]["minute"];
// 	  			$dateNewYear = "13-04-$gregorianYear"." ".$hour.":".$minute;
// 	  			$dateNewYear = date_create($dateNewYear);
// 	  			return $dateNewYear->format('Y-m-d H:i');
	  			
	  		} else {
	  			// ចូលឆ្នាំ ៣ ថ្ងៃ
	  			$numberNewYearDay = 3;
	  			
// 	  			$hour = $info["timeOfNewYear"]["hour"];
// 	  			$minute = $info["timeOfNewYear"]["minute"];
// 	  			$dateNewYear = "14-04-$gregorianYear"." ".$hour.":".$minute;
// 	  			$dateNewYear = date_create($dateNewYear);
// 	  			return $dateNewYear->format('Y-m-d H:i');
	  		}
	  		
	  		$hour = $info["timeOfNewYear"]["hour"];
	  		$minute = $info["timeOfNewYear"]["minute"];
	  		$epochLerngSak= "17-04-$gregorianYear"." ".$hour.":".$minute;
// 	  		$epochLerngSak = date_create($date);
	  		
// 	  		$epochLerngSak = $date->format('Y-m-d H:i');
	  		$khEpoch = $this->findLunarDate($epochLerngSak);
	  		$diffFromEpoch = (( ($khEpoch['month'] - 4) * 30) + $khEpoch['day']) -
	  		( (($info['lunarDateLerngSak']['month'] - 4) * 30) + $info['lunarDateLerngSak']['day']);
	  		
	  		$subtractday = $diffFromEpoch+$numberNewYearDay -1;
	  		
	  		$result = date_add(date_create($epochLerngSak),date_interval_create_from_date_string("-$subtractday  days"))->format('Y-m-d');
	  		
	  		// Caching
	  		 $khNewYearMoments[$gregorianYear]= date_create($result)->format('Y-m-d H:i');
	  		 $this->Caching = date_create($result)->format('Y-m-d H:i');
	  		 
	  		return  $result;
	  }
  }
  
  function khDay($moment) {
  	$result = $this->findLunarDate($moment);//this.clone()
  	return $result['day'];
  }
  
  function khMonth($moment) {
  	$result = $this->findLunarDate($moment);//this.clone()
  	return $result['month'];
  }
  
  function khYear($moment) {
  	return $this->getBEYear($moment);//this.clone()
  }
}

/*
 * 
 * Religious Events
 */


/*
 * ពិធីបុណ្យមាឃបូជា
 * ១៥កើត ខែមាឃ។
 * បុណ្យមាឃបូជាប្រារព្ធឡើង ដើម្បីរំលឹកដល់ថៃ្ងដែល ព្រះសម្មាសម្ពុទ្ធទ្រង់ប្រកាសបង្កើត ព្រះពុទ្ធសាសនាឡើងក្នុងលោកនាប្រទេសឥណ្ឌាកាលពី ៥៨៨ ឆ្នាំ
 */
function MeakBochea($khMonth,$khDay){
	$LunarMonths = $this->LunarMonths();
	if($LunarMonths['មាឃ']==$khMonth AND $khDay=15){
		return true;
	}
	return false;
}


/* Example
 * 
$myDate = date("Y-m-d"));
$lunar = new Application_Model_DbTable_DbLunaCalendar($myDate);
echo $lunar->toLunarDate();
 ថ្ងៃច័ន្ទ ១៥ កើត ខែជេស្ឋ ឆ្នាំកុរ ឯកស័ក ពុទ្ធសករាជ ២៥៦៣ ត្រូវនឹងថ្ងៃទី ១៧ ខែមិថុនា ឆ្នាំ២០១៩
 *
 * Format
 * 
By default, it will return the format as shown in example above. However, you can also customize your format.
 * DbLunarCalendar
$myDate = date("Y-m-d",strtotime("4/3/1992"));
$lunar = new Application_Model_DbTable_DbLunaCalendar($myDate);
echo $lunar->toLunarDate('dN ថ្ងៃW ខែm ព.ស. b');
 ៦កើត ថ្ងៃព្រហស្បតិ៍ ខែមិគសិរ ព.ស. ២៥៦២'

Format	Description	Example
W	ថ្ងៃនៃសប្ដាហ៍	អង្គារ
w	ថ្ងៃនៃសប្ដាហ៍កាត់	អ
d	ថ្ងៃទី ចាប់ពីលេខ ១ ដល់ ១៥	១
D	ថ្ងៃទី ចាប់ពីលេខ 0១ ដល់ ១៥	០១
n	កើត ឬ រោច	ក
N	កើត ឬ រោច	កើត
o	របៀបសរសេរខ្លីអំពីថ្ងៃទី	᧡ (មានន័យថា ១កើត)
m	ខែ	មិគសិរ ​
a	ឆ្នាំសត្វ	រកា
e	ស័ក	ឯកស័ក
b	ឆ្នាំពុទ្ធសករាជ	២៥៥៦
c	ឆ្នាំគ្រិស្តសករាជ	២០១៩
j	ឆ្នាំចុល្លសករាជ	១៤៦៣
g	ថ្ងៃទីសកល	១ ដល់ ៣១
s	ខែសកល	មិថុនា
*/
?>