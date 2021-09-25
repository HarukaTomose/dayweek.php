<?php
// �������б���
//// ���������ϡ�������Υ����ȤΥ����������Ѥ��Ƥ��ޤ���
//http://www.pahoo.org/e-soul/webtech/php02/php02-27-01.shtm
//
// 2020.1.23 Ver 1.1
// 	��2019ǯ��ʿ�������¡פˤȤ�ʤ��ѹ�����
// 	��Dayweek������ȼ������å���κ��ľ�����б�


define('PKWK_DAYWEEK_CASH_DIR', DATA_HOME.'dayweek/');

class Dayweek
{
	public $count;
	public $targetMonth;
	public $current;
	public $dwdate; // ver1.1, ���Υե����뼫�Τ�filetime ��¸��

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
		// ���Ǥ��׵ᤵ�줿�����������Ǥ��Ƥ���ʤ顢������ᤷ�ƽ�λ��
		if( $this->targetMonth == $year.$month) return $current;

		// ����å��夬����С�������ɤߤ������᤹��
		$this->current = $this->getCash($year,$month);
		if(! $this->current=="") {
			$this->targetMonth = $year.$month;
			return $this->current;
		}
		// ����å����ʤˤ�ʤ��Τǡ�������������
		$this->current = $this->createCal($year,$month);
		return $this->current;

	}
	
	function getCash($year,$month)
	{
		$target = PKWK_DAYWEEK_CASH_DIR.$year.$month.".txt";
		if( ! file_exists($target)) return ""; // ����å��夬�ʤ��Τ�¨�ᤷ��

		// ver1.1 ����å��夬���äƤ�Ť��ä��顢����å��������ơ֤ʤ��װ����ˤ��롣
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
//�������
static $fixed_holiday = array(
//    ��  ��  ����ǯ ��λǯ  ̾��
array( 1,  1, 1949, 9999, '����',         "New Year's Day"),
array( 1, 15, 1949, 1999, '���ͤ���',     'Coming of Age Day'),
array( 2, 11, 1967, 9999, '����ǰ����', 'National Foundation Day'),
array( 2, 23, 2020, 1989, 'ŷ��������',   "The Emperor's Birthday"),
array( 4, 29, 1949, 1989, 'ŷ��������',   "The Emperor's Birthday"),
array( 4, 29, 1990, 2006, '�ߤɤ����',   'Greenery Day'),
array( 4, 29, 2007, 9999, '���¤���',     'Showa Day'),
array( 5,  3, 1949, 9999, '��ˡ��ǰ��',   'Constitution Memorial Day'),
array( 5,  4, 1988, 2006, '��̱�ε���',   'Holiday for a Nation'),
array( 5,  4, 2007, 9999, '�ߤɤ����',   'Greenery Day'),
array( 5,  5, 1949, 9999, '���ɤ����',   "Children's Day"),
array( 7, 20, 1996, 2002, '������',       'Marine Day'),
array( 8, 11, 2016, 9999, '������',       'Mountain Day'),
array( 9, 15, 1966, 2002, '��Ϸ����',     'Respect for the Aged Day'),
array(10, 10, 1966, 1999, '�ΰ����',     'Health and Sports Day'),
array(11,  3, 1948, 9999, 'ʸ������',     'National Culture Day'),
array(11, 23, 1948, 9999, '��ϫ���դ���', 'Labbor Thanksgiving Day'),
array(12, 23, 1989, 2018, 'ŷ��������',   "The Emperor's Birthday"),
//�ʲ���1ǯ�����ν���
array( 4, 10, 1959, 1959, '���������οƲ��η뺧�ε�', "The Rite of Wedding of HIH Crown Prince Akihito"),
array( 2, 24, 1989, 1989, '����ŷ�Ĥ����Ӥ���', "The Funeral Ceremony of Emperor Showa."),
array(11, 12, 1990, 1990, '¨�������¤ε�', "The Ceremony of the Enthronement
      of His Majesty the Emperor (at the Seiden)"),
array( 6,  9, 1993, 1993, '���������οƲ��η뺧�ε� ', "The Rite of Wedding of HIH Crown Prince Naruhito"),
array( 5,  1, 2019, 2019, 'ŷ�Ĥ�¨�̤���', 'Day of cadence'),
array(10, 22, 2019, 2019, '¨�������¤ε�', 'The Ceremony of the Enthronement of His Majesty the Emperor (at the Seiden)'),
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
 * ����ǯ�ν�ʬ���������
 * @param	int $year ����ǯ
 * @return	int ����3��Ρ�
*/
function getVernalEquinox($year) {
	return floor(20.8431 + 0.242194 * ($year - 1980) - floor(($year - 1980) / 4));
}

/**
 * ����ǯ�ν�ʬ���������
 * @param	int $year ����ǯ
 * @return	int ����9��Ρ�
*/
function getAutumnalEquinox($year) {
	return floor(23.2488 + 0.242194 * ($year - 1980) - floor(($year - 1980) / 4));
}

/**
 * ��ư�����ʽ�ʬ����ʬ�����ˤǤ���С�����̾�Τ��������
 * @param	int $year  ����ǯ
 * @param	int $month ��
 * @param	int $day   ��
 * @param	string lang jp=���ܸ�̾�Ρ�en=�Ѹ�̾��
 * @return	string ��ư������̾�Ρ�FALSE=�����ǤϤʤ�
*/
function getMovableHoliday1($year, $month, $day, $lang) {
	$name = FALSE;

	//��ʬ����
	//tomoseDBG('gmh_1');
	$dd = $this->getVernalEquinox($year);
	if ($year >=1949 && $day == $dd && $month == 3) {
		$name = preg_match("/JP/i", $lang) == 1 ? '��ʬ����' : 'Vernal Equinox Day';
	}
	//��ʬ����
	$dd = $this->getAutumnalEquinox($year);

	if ($year >=1948 && $day == $dd && $month == 9) {

		$name = preg_match("/JP/i", $lang) == 1 ? '��ʬ����' : 'Autumnal Equinox Day';
	}

	return $name;
}

/**
 * ��������N���������
 * @param	int $year ����ǯ
 * @param	int $month ��
 * @param	int $week  �����ֹ桨0 (����)�� 6 (����)
 * @param	int $n     ��N����
 * @return	int $day ��
*/
function getWeeksOfMonth($year, $month, $week, $n) {
	if ($n < 1)		return FALSE;

	$jd1 = $this->Gregorian2JD($year, $month, 1);
	$wn1 = $this->getWeekNumber($year, $month, 1);
	$dd  = $week - $wn1 < 0 ? 7 + $week - $wn1 : $week - $wn1;
	$jd2 = $jd1 + $dd;
	$jdn = $jd2 + 7 * ($n - 1);
	list($yy, $mm, $dd) = $this->JD2Gregorian($jdn);

	if ($mm != $month)	return FALSE;	//��Υ����С��ե�

	return $dd;
}

/**
 * ��ư�����ʥϥåԡ��ޥ�ǡ��ˤǤ���С�����̾�Τ��������
 * @param	int $year  ����ǯ
 * @param	int $month ��
 * @param	int $day   ��
 * @param	string lang jp=���ܸ�̾�Ρ�en=�Ѹ�̾��
 * @return	string ��ư������̾�Ρ�FALSE=�����ǤϤʤ�
*/
function getMovableHoliday2($year, $month, $day, $lang) {
//��ư�����ʥϥåԡ��ޥ�ǡ�ˡ��
static $movable_holiday = array(
//    ��  �����ֹ� ��N���� ����ǯ  ��λǯ  ̾��
array( 1, 1, 2, 2000, 9999, '���ͤ���', 'Coming of Age Day'),
array( 7, 1, 3, 2003, 9999, '������',   'Marine Day'),
array( 9, 1, 3, 2003, 9999, '��Ϸ����', 'Respect for the Aged Day'),
array(10, 1, 2, 2000, 9999, '�ΰ����', 'Health and Sports Day')
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
 * ��������ޤ��ϰ�ư�������ɤ���Ĵ�٤�
 * @param	int $year  ����ǯ
 * @param	int $month ��
 * @param	int $day   ��
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
 * ���ص������ɤ���Ĵ�٤�
 * @param	int $year  ����ǯ
 * @param	int $month ��
 * @param	int $day   ��
 * @return	bool TRUE/FALSE
*/
function isTransferHoliday($year, $month, $day) {

	$jd = $this->Gregorian2JD($year, $month, $day);
	$j0 = $this->Gregorian2JD(1973, 4, 12);
	if ($jd < $j0)	return FALSE;		//ͭ���ʤΤ�1973ǯ4��12���ʹ�

	//�����������ʤ� FALSE
		//tomoseDBG('itH_0');
	if ($this->isFixedMovableHoliday($year, $month, $day))		return FALSE;

	$n = ($year <= 2006) ? 1 : 7;	//����ˡ�ʤ����7�����̤�
	$jd--;							//1����
	for ($i = 0; $i < $n; $i++) {		//̵�¥롼�פ˴٤�ʤ��褦��
		//tomoseDBG('itH_1');
		list($yy, $mm, $dd) = $this->JD2Gregorian($jd);
		//���������������ʤ鿶�ص���
		//tomoseDBG('itH_2');
		if ($this->isFixedMovableHoliday($yy, $mm, $dd)
			&& ($this->getWeekNumber($yy, $mm, $dd) == 0))		return TRUE;
		//�����Ǥʤ�����Ǥ��ڤ�
		//tomoseDBG('itH_4');
		if (! $this->isFixedMovableHoliday($yy, $mm, $dd))		break;
		$jd--;	//1����
	}
		//tomoseDBG('itH_4');
	return FALSE;
}

/**
 * ��̱�ε������ɤ���Ĵ�٤�
 * @param	int $year  ����ǯ
 * @param	int $month ��
 * @param	int $day   ��
 * @return	bool TRUE/FALSE
*/
function isNationalHoliday($year, $month, $day) {
	//tomoseDBG('inh_1');
	if ($year < 2003)	return FALSE;	//ͭ���ʤΤ�2003ǯ�ʹ�
	$j0 = $this->Gregorian2JD($year, $month, $day) - 1;	//����
	list($yy0, $mm0, $dd0) = $this->JD2Gregorian($j0);
	$j1 = $this->Gregorian2JD($year, $month, $day) + 1;	//����
	list($yy1, $mm1, $dd1) = $this->JD2Gregorian($j1);
	//tomoseDBG('inh_2');

	//��������������������ޤ��ϰ�ư�����ʤ��̱�ε���
	if ($this->isFixedMovableHoliday($yy0, $mm0, $dd0)
		&& $this->isFixedMovableHoliday($yy1, $mm1, $dd1))	return TRUE;
	//tomoseDBG('inh_3');
	return FALSE;
}

/**
 * �����Ǥ���С�����̾�Τ��������
 * @param	int $year  ����ǯ
 * @param	int $month ��
 * @param	int $day   ��
 * @param	string lang jp=���ܸ�̾�Ρ�en=�Ѹ�̾��
 * @return	string ������̾�Ρ�FALSE=�����ǤϤʤ�
*/
function getHoliday($year, $month, $day, $lang) {
	//�������
	//tomoseDBG('1');
	$name = $this->getFixedHoliday($year, $month, $day, 'jp');
	if ($name != FALSE)		return $name;
	//��ư�����ʽ�ʬ����ʬ������
	//tomoseDBG('2');
	$name = $this->getMovableHoliday1($year, $month, $day, 'jp');
	if ($name != FALSE)		return $name;
	//��ư�����ʥϥåԡ��ޥ�ǡ���
	//tomoseDBG('3');
	$name = $this->getMovableHoliday2($year, $month, $day, 'jp');
	if ($name != FALSE)		return $name;
	//���ص���
	//tomoseDBG('4');
	if ($this->isTransferHoliday($year, $month, $day)) {
		return preg_match("/JP/i", $lang) == 1 ? '���ص���' : 'holiday in lieu';
	}
	//��̱�ν���
	//tomoseDBG('5');
	if ($this->isNationalHoliday($year, $month, $day)) {
		return preg_match("/JP/i", $lang) == 1 ? '��̱�ε���' : "Citizen's Holiday";
	}
	//�����ǤϤʤ�
	return FALSE;
}

/**
 * �������ɤ�����Ĵ�٤�
 * @param	int $year  ����ǯ
 * @param	int $month ��
 * @param	int $day   ��
 * @return	bool TRUE/FALSE
*/
function isHoliday($year, $month, $day) {
	//tomoseDBG('day:'.$day);
	return $this->getHoliday($year, $month, $day, 'jp') == FALSE ? FALSE : TRUE;
}
/**
 * ���쥴�ꥪ��ͥ�ꥦ�������Ѵ�
 * @param	int $year  ����ǯ
 * @param	int $month ��
 * @param	int $day   ��
 * @return	double ��ꥦ����(JD)
*/
function Gregorian2JD($year, $month, $day) {
	if ($month <= 2) {
		$month += 12;
		$year--;
	}
	return floor(365.25 * $year) - floor($year / 100) + floor($year / 400) + floor(30.6001 * ($month + 1)) + $day +1720996.5;
}

/**
 * ��ꥦ�����ͥ��쥴�ꥪ���Ѵ�
 * @param	double $jd ��ꥦ����
 * @return	array($year, $month, $day)  ����ǯ����
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
 * �����ֹ�����
 * @param	int $year  ����ǯ
 * @param	int $month ��
 * @param	int $day   ��
 * @return	int �����ֹ��0:������, 1:������...6:��������
*/
function getWeekNumber($year, $month, $day) {
	$jd = $this->Gregorian2JD($year, $month, $day);
	return ($jd + 1.5) % 7;
}



}

?>
