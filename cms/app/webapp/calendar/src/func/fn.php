<?php
/**
 * @param int, int
 * @return int
 */
function soycalendar_get_last_date(int $year, int $month){
    return (int)date("Ymd", soycalendar_get_last_date_timestamp($year, $month));
}
/**
 * @param int, int
 * @return int<timestamp>
 */
function soycalendar_get_last_date_timestamp(int $year, int $month){
    //12月対策
    if($month == 12){
        $year++;
        $month = 1;
    }else{
        $month++;
    }
    return mktime(0, 0, 0, $month, 0, $year);
}
/**
 * @param int, int, int
 * @return int<timestamp>
 */
function soycalendar_get_schedule(int $year, int $month, int $day){
    return mktime(0, 0, 0, $month, $day, $year);
}

/**
 * @param array(year, month, day)
 * @return int<timestamp>
 */
function soycalendar_get_schedule_by_array(array $arr){
    if(!isset($arr["year"]) || !isset($arr["month"]) || !isset($arr["day"])) return 0;
    return soycalendar_get_schedule((int)$arr["year"], (int)$arr["month"], (int)$arr["day"]);
}

/**
 * @param int<timestamp>
 * @return array("year", "month", "day", "w")
 */
function soycalendar_get_Ynjw(int $timestamp){
	return array(
		"year" => (int)date("Y",$timestamp),
		"month" => (int)date("n",$timestamp),
		"day" => (int)date("j",$timestamp),
		"week" => (int)date("w",$timestamp)
	);
}

/**
 * @param int
 * @return array
 */
function soycalendar_get_date_array_by_ymd(int $ymd){
    return array(
		"year" => substr($ymd, 0, 4),
		"month" => substr($ymd, 4, 2),
		"day" => substr($ymd, 6, 2)
	);
}

/**
 * @param int, int, int
 * @return int
 */
function soycalendar_get_timestamp(int $year, int $month, int$day){
    return mktime(0,0,0,$month,$day,$year);
}

/**
 * @param int<Ymd>
 * @return int <timestamp>
 */
function soycalendar_get_timestamp_by_ymd(int $ymd){
    $y = substr($ymd, 0, 4);
    $m = (int)substr($ymd, 4, 2);
    $d = (int)substr($ymd, 6, 2);
    return mktime(0, 0, 0, $m, $d, $y);
}

/**
 * @param int<timestamp>, int
 * @return array(int<timestamp>, int<timestamp>)
 */
function soycalendar_get_first_date_or_last_date_timestamp(int $timestamp){
    $y = date("Y", $timestamp);
    $m = date("n", $timestamp);
    $start = mktime(0, 0, 0, $m, 1, $y);
	$end = mktime(0, 0, 0, $m + 1, 1, $y) - 1;
    return array($start, $end);
}