<?php
// ・祝日対応。
//// 祝日処理は、こちらのサイトのソースを利用しています。
//http://www.pahoo.org/e-soul/webtech/php02/php02-27-01.shtm
//
// 2020.1.23 Ver 1.1
// 	・2019年「平成→令和」にともなう変更数点
// 	・Dayweek更新に伴うキャッシュの作り直しに対応


define('PKWK_DAYWEEK_CASH_DIR', DATA_HOME.'dayweek/');

class Dayweek
{
	public $count;
	public $targetMonth;
	public $current;
	public $dwdate; // ver1.1, このファイル自体のfiletime 保存。

	function Dayweek()
	{
		if(! file_exists(PKWK_DAYWEEK_CASH_DIR)) mkdir(PKWK_DAYWEEK_CASH_DIR);
		$this->count = 0;
		$this->targetMonth= "";
		$this->current ="";
		$this->dwdate = filemtime( LIB_DIR.'dayweek.php' );
	}

	function test()
	{
		return 1;
	}

	function getCal($year,$month)
	{
		//tomoseDBG("getCal");
		// すでに要求されたカレンダーができているなら、それを戻して終了。
		if( $this->targetMonth == $year.$month) return $current;

		// キャッシュがあれば、それを読みだして戻す。
		$this->current = $this->getCash($year,$month);
		if(! $this->current=="") {
			$this->targetMonth = $year.$month;
			return $this->current;
		}
		// キャッシュもなにもないので、カレンダー作成
		$this->current = $this->createCal($year,$month);
		return $this->current;

	}
	
	function getCash($year,$month)
	{
		$target = PKWK_DAYWEEK_CASH_DIR.$year.$month.".txt";
		if( ! file_exists($target)) return ""; // キャッシュがないので即戻し。

		// ver1.1 キャッシュがあっても古かったら、キャッシュ削除して「なし」扱いにする。
		if( filemtime($target)< $this->dwdate ){
			unlink($target);
			return "";
		}
		
		$fp = file($target);
		$current = array();
		foreach ($fp as $line) {
			$tmp = explode(',',$line);
//			if (count($tmp) != 3 ) continue; 
			list ($date, $wday, $info) = $tmp;

			$current[$date]=array(
				'wday' => $wday,
				'info' => $info
			);


		}

		return $current;

	}

	function createCal($year,$month)
	{
		
		$this->targetMonth=$year.$month;
		$curday = 1;

		$fstday = getdate(mktime(0,0,0,$month,$curday,$year) - LOCALZONE + ZONETIME);
		$cwday = $fstday['wday'];
		$lstday = getdate(mktime(0,0,0,$month+1,0,$year) - LOCALZONE + ZONETIME);

		$current = array();
		while( $curday<=$lstday['mday'] )
		{
			$tmp=$this->getHoliday($year,$month,$curday,'JP');
			if(! $tmp){

				$current[$curday]=array(
					'wday' => $cwday,
					'info' => ""
				);
			}else{

				$current[$curday]=array(
					'wday' => '9',
					'info' => $tmp
				);
			}
			$cwday = (++$cwday)%7;
			++$curday;

			if($curday>50)break;
		}

		$this->saveCal($year,$month,$current);
		return $current;

	}

	function saveCal($year,$month,$current)
	{
		$target = PKWK_DAYWEEK_CASH_DIR.$year.$month.".txt";

		$fp = fopen($target, 'w');
		set_file_buffer($fp, 0);
		flock($fp, LOCK_EX);
		rewind($fp);
		
		foreach($current as $date => $info){
			$wbuf = $date.','.$info['wday'].','.$info['info'];
			fwrite($fp, $wbuf. "\n");

		}
		flock($fp, LOCK_UN);
		fclose($fp);


	}


function getFixedHoliday($year, $month, $day, $lang) {
//固定祝日
static $fixed_holiday = array(
//    月  日  開始年 終了年  名称
array( 1,  1, 1949, 9999, '元日',         "New Year's Day"),
array( 1, 15, 1949, 1999, '成人の日',     'Coming of Age Day'),
array( 2, 11, 1967, 9999, '建国記念の日', 'National Foundation Day'),
array( 2, 23, 2020, 1989, '天皇誕生日',   "The Emperor's Birthday"),
array( 4, 29, 1949, 1989, '天皇誕生日',   "The Emperor's Birthday"),
array( 4, 29, 1990, 2006, 'みどりの日',   'Greenery Day'),
array( 4, 29, 2007, 9999, '昭和の日',     'Showa Day'),
array( 5,  3, 1949, 9999, '憲法記念日',   'Constitution Memorial Day'),
array( 5,  4, 1988, 2006, '国民の休日',   'Holiday for a Nation'),
array( 5,  4, 2007, 9999, 'みどりの日',   'Greenery Day'),
array( 5,  5, 1949, 9999, 'こどもの日',   "Children's Day"),
array( 7, 20, 1996, 2002, '海の日',       'Marine Day'),
array( 7, 22, 2021, 2021, '海の日',       'Marine Day'),
array( 7, 23, 2020, 2020, '海の日',       'Marine Day'),
array( 7, 23, 2021, 2021, 'スポーツの日', 'Health Sports Day'),
array( 7, 24, 2020, 2020, 'スポーツの日', 'Health Sports Day'),
array( 8,  8, 2021, 2021, '山の日',       'Mountain Day'),
array( 8, 11, 2016, 2019, '山の日',       'Mountain Day'),
array( 8, 10, 2020, 2020, '山の日',       'Mountain Day'),
array( 8, 11, 2022, 9999, '山の日',       'Mountain Day'),
array( 9, 15, 1966, 2002, '敬老の日',     'Respect for the Aged Day'),
array(10, 10, 1966, 1999, '体育の日',     'Health and Sports Day'),
array(11,  3, 1948, 9999, '文化の日',     'National Culture Day'),
array(11, 23, 1948, 9999, '勤労感謝の日', 'Labbor Thanksgiving Day'),
array(12, 23, 1989, 2018, '天皇誕生日',   "The Emperor's Birthday"),
//以下、1年だけの祝日
array( 4, 10, 1959, 1959, '皇太子明仁親王の結婚の儀', "The Rite of Wedding of HIH Crown Prince Akihito"),
array( 2, 24, 1989, 1989, '昭和天皇の大喪の礼', "The Funeral Ceremony of Emperor Showa."),
array(11, 12, 1990, 1990, '即位礼正殿の儀', "The Ceremony of the Enthronement
      of His Majesty the Emperor (at the Seiden)"),
array( 6,  9, 1993, 1993, '皇太子徳仁親王の結婚の儀 ', "The Rite of Wedding of HIH Crown Prince Naruhito"),
array( 5,  1, 2019, 2019, '天皇の即位の日', 'Day of cadence'),
array(10, 22, 2019, 2019, '即位礼正殿の儀', 'The Ceremony of the Enthronement of His Majesty the Emperor (at the Seiden)'),
);

	$name = FALSE;
	foreach ($fixed_holiday as $val) {
		if ($month == $val[0] && $day == $val[1]) {
			if ($year >= $val[2] && $year <= $val[3]) {
				$name = preg_match("/JP/i", $lang) == 1 ? $val[4] : $val[5];
				break;
			}
		}
	}
//	if(! $name=="") setCal($year, $month, $day,$name);
	return $name;
}

/**
 * ある年の春分の日を求める
 * @param	int $year 西暦年
 * @return	int 日（3月の）
*/
function getVernalEquinox($year) {
	return floor(20.8431 + 0.242194 * ($year - 1980) - floor(($year - 1980) / 4));
}

/**
 * ある年の秋分の日を求める
 * @param	int $year 西暦年
 * @return	int 日（9月の）
*/
function getAutumnalEquinox($year) {
	return floor(23.2488 + 0.242194 * ($year - 1980) - floor(($year - 1980) / 4));
}

/**
 * 移動祝日（春分／秋分の日）であれば、その名称を取得する
 * @param	int $year  西暦年
 * @param	int $month 月
 * @param	int $day   日
 * @param	string lang jp=日本語名称／en=英語名称
 * @return	string 移動祝日の名称／FALSE=祝日ではない
*/
function getMovableHoliday1($year, $month, $day, $lang) {
	$name = FALSE;

	//春分の日
	//tomoseDBG('gmh_1');
	$dd = $this->getVernalEquinox($year);
	if ($year >=1949 && $day == $dd && $month == 3) {
		$name = preg_match("/JP/i", $lang) == 1 ? '春分の日' : 'Vernal Equinox Day';
	}
	//秋分の日
	$dd = $this->getAutumnalEquinox($year);

	if ($year >=1948 && $day == $dd && $month == 9) {

		$name = preg_match("/JP/i", $lang) == 1 ? '秋分の日' : 'Autumnal Equinox Day';
	}

	return $name;
}

/**
 * ある月の第N曜日を求める
 * @param	int $year 西暦年
 * @param	int $month 月
 * @param	int $week  曜日番号；0 (日曜)～ 6 (土曜)
 * @param	int $n     第N曜日
 * @return	int $day 日
*/
function getWeeksOfMonth($year, $month, $week, $n) {
	if ($n < 1)		return FALSE;

	$jd1 = $this->Gregorian2JD($year, $month, 1);
	$wn1 = $this->getWeekNumber($year, $month, 1);
	$dd  = $week - $wn1 < 0 ? 7 + $week - $wn1 : $week - $wn1;
	$jd2 = $jd1 + $dd;
	$jdn = $jd2 + 7 * ($n - 1);
	list($yy, $mm, $dd) = $this->JD2Gregorian($jdn);

	if ($mm != $month)	return FALSE;	//月のオーバーフロー

	return $dd;
}

/**
 * 移動祝日（ハッピーマンデー）であれば、その名称を取得する
 * @param	int $year  西暦年
 * @param	int $month 月
 * @param	int $day   日
 * @param	string lang jp=日本語名称／en=英語名称
 * @return	string 移動祝日の名称／FALSE=祝日ではない
*/
function getMovableHoliday2($year, $month, $day, $lang) {
//移動祝日（ハッピーマンデー法）
static $movable_holiday = array(
//    月  曜日番号 第N曜日 開始年  終了年  名称
array( 1, 1, 2, 2000, 9999, '成人の日', 'Coming of Age Day'),
array( 7, 1, 3, 2003, 9999, '海の日',   'Marine Day'),
array( 9, 1, 3, 2003, 9999, '敬老の日', 'Respect for the Aged Day'),
array(10, 1, 2, 2000, 9999, '体育の日', 'Health and Sports Day')
);

	$name = FALSE;
	foreach ($movable_holiday as $val) {
		if ($month == $val[0] && $day == $this->getWeeksOfMonth($year, $month, $val[1], $val[2])) {
			if ($year >= $val[3] && $year <= $val[4]) {
				$name = preg_match("/JP/i", $lang) == 1 ? $val[5] : $val[6];
				break;
			}
		}
	}
	return $name;
}

/**
 * 固定祝日または移動祝日かどうか調べる
 * @param	int $year  西暦年
 * @param	int $month 月
 * @param	int $day   日
 * @return	bool TRUE/FALSE
*/
function isFixedMovableHoliday($year, $month, $day) {
	//tomoseDBG('ifmh_1');
	if ($this->getFixedHoliday($year, $month, $day, 'en') != FALSE)	return TRUE;
	//tomoseDBG('ifmh_2');
	if ($this->getMovableHoliday1($year, $month, $day, 'en') != FALSE)	return TRUE;
	//tomoseDBG('ifmh_3');
	if ($this->getMovableHoliday2($year, $month, $day, 'en') != FALSE)	return TRUE;
	//tomoseDBG('ifmh_4');
	return FALSE;
}

/**
 * 振替休日かどうか調べる
 * @param	int $year  西暦年
 * @param	int $month 月
 * @param	int $day   日
 * @return	bool TRUE/FALSE
*/
function isTransferHoliday($year, $month, $day) {

	$jd = $this->Gregorian2JD($year, $month, $day);
	$j0 = $this->Gregorian2JD(1973, 4, 12);
	if ($jd < $j0)	return FALSE;		//有効なのは1973年4月12日以降

	//当日が祝日なら FALSE
		//tomoseDBG('itH_0');
	if ($this->isFixedMovableHoliday($year, $month, $day))		return FALSE;

	$n = ($year <= 2006) ? 1 : 7;	//改正法なら最大7日間遡る
	$jd--;							//1日前
	for ($i = 0; $i < $n; $i++) {		//無限ループに陥らないように
		//tomoseDBG('itH_1');
		list($yy, $mm, $dd) = $this->JD2Gregorian($jd);
		//祝日かつ日曜日なら振替休日
		//tomoseDBG('itH_2');
		if ($this->isFixedMovableHoliday($yy, $mm, $dd)
			&& ($this->getWeekNumber($yy, $mm, $dd) == 0))		return TRUE;
		//祝日でなければ打ち切り
		//tomoseDBG('itH_4');
		if (! $this->isFixedMovableHoliday($yy, $mm, $dd))		break;
		$jd--;	//1日前
	}
		//tomoseDBG('itH_4');
	return FALSE;
}

/**
 * 国民の休日かどうか調べる
 * @param	int $year  西暦年
 * @param	int $month 月
 * @param	int $day   日
 * @return	bool TRUE/FALSE
*/
function isNationalHoliday($year, $month, $day) {
	//tomoseDBG('inh_1');
	if ($year < 2003)	return FALSE;	//有効なのは2003年以降
	$j0 = $this->Gregorian2JD($year, $month, $day) - 1;	//前日
	list($yy0, $mm0, $dd0) = $this->JD2Gregorian($j0);
	$j1 = $this->Gregorian2JD($year, $month, $day) + 1;	//翌日
	list($yy1, $mm1, $dd1) = $this->JD2Gregorian($j1);
	//tomoseDBG('inh_2');

	//前日と翌日が固定祝日または移動祝日なら国民の休日
	if ($this->isFixedMovableHoliday($yy0, $mm0, $dd0)
		&& $this->isFixedMovableHoliday($yy1, $mm1, $dd1))	return TRUE;
	//tomoseDBG('inh_3');
	return FALSE;
}

/**
 * 祝日であれば、その名称を取得する
 * @param	int $year  西暦年
 * @param	int $month 月
 * @param	int $day   日
 * @param	string lang jp=日本語名称／en=英語名称
 * @return	string 祝日の名称／FALSE=祝日ではない
*/
function getHoliday($year, $month, $day, $lang) {
	//固定祝日
	//tomoseDBG('1');
	$name = $this->getFixedHoliday($year, $month, $day, 'jp');
	if ($name != FALSE)		return $name;
	//移動祝日（春分／秋分の日）
	//tomoseDBG('2');
	$name = $this->getMovableHoliday1($year, $month, $day, 'jp');
	if ($name != FALSE)		return $name;
	//移動祝日（ハッピーマンデー）
	//tomoseDBG('3');
	$name = $this->getMovableHoliday2($year, $month, $day, 'jp');
	if ($name != FALSE)		return $name;
	//振替休日
	//tomoseDBG('4');
	if ($this->isTransferHoliday($year, $month, $day)) {
		return preg_match("/JP/i", $lang) == 1 ? '振替休日' : 'holiday in lieu';
	}
	//国民の祝日
	//tomoseDBG('5');
	if ($this->isNationalHoliday($year, $month, $day)) {
		return preg_match("/JP/i", $lang) == 1 ? '国民の休日' : "Citizen's Holiday";
	}
	//祝日ではない
	return FALSE;
}

/**
 * 祝日かどうかを調べる
 * @param	int $year  西暦年
 * @param	int $month 月
 * @param	int $day   日
 * @return	bool TRUE/FALSE
*/
function isHoliday($year, $month, $day) {
	//tomoseDBG('day:'.$day);
	return $this->getHoliday($year, $month, $day, 'jp') == FALSE ? FALSE : TRUE;
}
/**
 * グレゴリオ暦⇒ユリウス日　変換
 * @param	int $year  西暦年
 * @param	int $month 月
 * @param	int $day   日
 * @return	double ユリウス日(JD)
*/
function Gregorian2JD($year, $month, $day) {
	if ($month <= 2) {
		$month += 12;
		$year--;
	}
	return floor(365.25 * $year) - floor($year / 100) + floor($year / 400) + floor(30.6001 * ($month + 1)) + $day +1720996.5;
}

/**
 * ユリウス日⇒グレゴリオ暦　変換
 * @param	double $jd ユリウス日
 * @return	array($year, $month, $day)  西暦年月日
*/
function JD2Gregorian($jd) {
	$jd += 0.5;
	$z = floor($jd);
	$f = $jd - $z;
	$aa = floor(($z - 1867216.25) / 36524.25);
	$a = floor($z + 1 + $aa - floor($aa / 4));
	$b = $a + 1524;
	$c = floor(($b - 122.1) / 365.25);
	$k = floor(365.25 * $c);
	$e = floor(($b - $k) / 30.6001);

	$day = floor($b - $k - floor(30.6001 * $e));
	$month = ($e < 13.5) ? ($e - 1) : ($e - 13);
	$year = ($month > 2.5) ? ($c - 4716) : ($c - 4715);

	return array($year, $month, $day);
}

/**
 * 曜日番号を求める
 * @param	int $year  西暦年
 * @param	int $month 月
 * @param	int $day   日
 * @return	int 曜日番号（0:日曜日, 1:月曜日...6:土曜日）
*/
function getWeekNumber($year, $month, $day) {
	$jd = $this->Gregorian2JD($year, $month, $day);
	return ($jd + 1.5) % 7;
}



}

?>
