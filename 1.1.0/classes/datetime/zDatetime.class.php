<?php
/**
 * @brief byte 단위로 문자열을 치환
 **/
function replaceChar($from, $to, $string)
{
    $len = strlen($string);
    $output = '';

    for($i = 0; $i < $len; ++ $i)
    {
        if(($k = array_search($string[$i], $from)) !== false) $output .= $to[$k];
        else $output .= $string[$i];
    }

    return $output;
}

/**
 * @class  zDatetime
 * @author HNO3 (wdlee91@gmail.com)
 * @brief  날짜/시간 데이터 저장
 *
 * 날짜/시간의 관리와 계산을 쉽게 하도록 돕는 class.
 * DB의 datetime 형은 모두 이 class의 instance로 반환된다.
 * 내부적으로는 UTC와 지역 시간을 자유자재로 변환하여 사용하나,
 * serialize와 unserialize의 경우 무조건 UTC 기준으로 실행된다.
 **/
class zDatetime
{
    var $year, $month, $date, $hour, $minute, $second;
    var $isLocal = false;
    var $timezone;

    // static utilities

    /**
     * @brief 공용 timezone을 얻어옴
     **/
    function getPublicTimezone()
    {
        return PSM::v('publicTimezone');
    }

    /**
     * @brief 공용 timezone을 계산하여 설정
     **/
    function calculatePublicTimezone()
    {
        $publicTimezone = &PSM::v('publicTimezone');

        // TODO: Calculating from member's timezone
        $timezone = intval(str_replace(':', '', $GLOBALS['_time_zone']));
        $publicTimezone = intval($timezone / 100) * 60 + $timezone % 100;
    }

    /**
     * @brief 공용 timezone을 임의로 설정. 기존에 생성된 zDatetime instance에는 영향을 주지 않는다.
     **/
    function setPublicTimezone($timezone)
    {
        $publicTimezone = &PSM::v('publicTimezone');
        $publicTimezone = $timezone;
    }

    /**
     * @brief 인자로 주어진 timezone을 +0900 형태로 변환
     **/
    function getTimezoneHour($timezone)
    {
        return sprintf('%s%02d%02d', ($timezone < 0 ? '-' : '+'), intval($timezone / 60), $timezone % 60);
    }

    /**
     * @brief 인자로 주어진 timezone을 +09:00 형태로 변환
     **/
    function getTimezoneHourMinute($timezone)
    {
        return sprintf('%s%02d:%02d', ($timezone < 0 ? '-' : '+'), intval($timezone / 60), $timezone % 60);
    }

    /**
     * @brief 윤년 여부를 계산
     **/
    function isLeapYear($year)
    {
        return (($year % 4 == 0) && ($year % 100 != 0)) || ($year % 400 == 0);
    }

    /**
     * @brief 해당 연도의 일수를 반환
     **/
    function getYearDateCount($year)
    {
        return 365 + intval(zDatetime::isLeapYear($year));
    }

    /**
     * @brief 해당 월의 영문 이름을 반환
     **/
    function getMonthString($month)
    {
        static $string = array(1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

        return $string[$month];
    }

    /**
     * @brief 해당 연도의 월의 일수를 반환
     **/
    function getMonthDateCount($year, $month)
    {
        static $dates = array(1 => 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

        for(; $month < 1; -- $year, $month += 12);
        for(; $month > 12; ++ $year, $month -= 12);

        if($month == 2)
            return $dates[$month] + intval(zDatetime::isLeapYear($year));
        return $dates[$month];
    }

    /**
     * @brief 해당 연월일이 해당 연도의 몇 번째 날인지 계산
     **/
    function getYearDate($year, $month, $date)
    {
        $order = 0;

        for(-- $month; $month > 0; -- $month)
            $order += zDatetime::getMonthDateCount($year, $month);
        $order += $date;

        return -- $order;
    }

    /**
     * @brief 해당 연월일의 요일 계산. 일요일(0) - 토요일(6)
     **/
    function getWeekday($year, $month, $date)
    {
        $datediff = 0;
        $cyear = 2000;
        $cmonth = 1;

        if($year > 2000)
        {
            for(; $cyear < $year; ++ $cyear)
            {
                $datediff += zDatetime::getYearDateCount($cyear);
            }
        }
        elseif($year < 2000)
        {
            for(; $cyear > $year; -- $cyear)
            {
                $datediff -= zDatetime::getYearDateCount($cyear - 1);
            }
        }

        $datediff += zDatetime::getYearDate($year, $month, $date);

        $weekday = ($datediff + 6) % 7; // 2000-01-01 is saturday(6)
        if($weekday < 0)
            return $weekday + 7;
        return $weekday;
    }

    /**
     * @brief 해당 요일의 영문 이름을 반환. Sunday(0) - Saturday(6)
     **/
    function getWeekdayString($weekday)
    {
        static $string = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

        return $string[$weekday];
    }

    /**
     * @brief 일요일을 한 주의 시작으로 하는 해당 연월일의 해당 연도에서의 주 번호를 계산
     **/
    function getYearWeek($year, $month, $date) // week starts from sunday
    {
        $first_weekday = zDatetime::getWeekday($year, 1, 1);
        if($first_weekday == 0)
            $first_sunday = 1;
        else
            $first_sunday = 8 - $first_weekday;

        $week = intval((zDatetime::getYearDate($year, $month, $date) - $first_sunday + 1) / 7) + 1;
        if($week == 0)
            return zDatetime::getYearWeek($year - 1, 12, 31);
        return $week;
    }

    // main class functions

    /**
     * @brief zDatetime의 생성자
     **/
    function zDatetime($yearOrStringOrObject = null, $month = null, $date = null, $hour = null, $minute = null, $second = null, $utc = false)
    {
        $this->timezone = PSM::v('publicTimezone');

        if(is_null($yearOrStringOrObject))
            $this->setFromString(gmdate('YmdHis'), true);
        elseif(is_string($yearOrStringOrObject))
            $this->setFromString($yearOrStringOrObject, $utc);
        else
            $this->set($yearOrStringOrObject, $month, $date, $hour, $minute, $second, $utc);
    }

    /**
     * @brief 현재 instance의 timezone 반환
     **/
    function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @brief 현재 instance의 timezone 설정
     **/
    function setTimezone($timezone)
    {
        $this->setUTC();
        $this->timezone = $timezone;
    }

    /**
     * @brief 현재 instance의 날짜/시간을 UTC 기준으로 변경
     **/
    function setUTC()
    {
        if($this->is_local)
        {
            $this->subtract(0, 0, 0, 0, $this->timezone, 0);
            $this->is_local = false;
        }
    }

    /**
     * @brief 현재 instance의 날짜/시간을 지역 timezone 기준으로 변경
     **/
    function setLocal()
    {
        if(!$this->is_local)
        {
            $this->add(0, 0, 0, 0, $this->timezone, 0);
            $this->is_local = true;
        }
    }

    /**
     * @brief 현재 instance의 날짜/시간을 반환
     **/
    function get(&$year, &$month, &$date, &$hour, &$minute, &$second)
    {
        $this->setLocal();

        $year = $this->year;
        $month = $this->month;
        $date = $this->date;
        $hour = $this->hour;
        $minute = $this->minute;
        $second = $this->second;
    }

    /**
     * @brief 현재 instance의 날짜/시간을 설정
     **/
    function set($year, $month, $date, $hour, $minute, $second, $utc = false)
    {
        $this->year = $year;
        $this->month = $month;
        $this->date = $date;
        $this->hour = $hour;
        $this->minute = $minute;
        $this->second = $second;

        $this->is_local = !$utc;
        $this->setUTC();
    }

    /**
     * @brief Unix timestamp로부터 현재 instance의 날짜/시간을 설정
     **/
    function setFromTimestamp($timestamp, $utc = false)
    {
        $this->setFromString(date('YmdHis', $timestamp), $utc);
    }

    /**
     * @brief YYYYMMDDHHIISS의 시간값으로부터 현재 instance의 날짜/시간을 설정
     **/
    function setFromString($string = '00000000000000', $utc = false)
    {
        $this->year = intval(substr($string, 0, 4));
        $this->month = intval(substr($string, 4, 2));
        $this->date = intval(substr($string, 6, 2));
        $this->hour = intval(substr($string, 8, 2));
        $this->minute = intval(substr($string, 10, 2));
        $this->second = intval(substr($string, 12, 2));

        $this->is_local = !$utc;
        $this->setUTC();
    }

    /**
     * @brief 현재 instance의 연 반환
     **/
    function getYear() { return $this->year; }
    /**
     * @brief 현재 instance의 월 반환
     **/
    function getMonth() { return $this->month; }
    /**
     * @brief 현재 instance의 일 반환
     **/
    function getDate() { return $this->date; }
    /**
     * @brief 현재 instance의 시 반환
     **/
    function getHour() { return $this->hour; }
    /**
     * @brief 현재 instance의 분 반환
     **/
    function getMinute() { return $this->minute; }
    /**
     * @brief 현재 instance의 초 반환
     **/
    function getSecond() { return $this->second; }

    /**
     * @brief 현재 instance의 날짜/시간을 ISO-8601 format으로 변환
     **/
    function getISO8601String() { return sprintf('%04d-%02d-%02dT%02d:%02d:%02d%s', $this->year, $this->month, $this->date, $this->hour, $this->minute, $this->second, $this->is_local ? zDatetime::getTimezoneHourMinute($this->timezone) : 'Z'); }

    /**
     * @brief 현재 instance의 날짜/시간을 Unix timestamp로 변환
     **/
    function toTimestamp()
    {
        if($this->year < 1970)
            return false;

        return mktime($this->hour, $this->minute, $this->second, $this->month, $this->date, $this->year);
    }

    /**
     * @brief 현재 instance의 날짜/시간을 date() 형태로 꾸며줌. C: Y-m-d H:i:s, E: Y-m-d, J: H:i:s (locale에 따라 변경됨)
     **/
    function format($format = 'C')
    {
        $YmdHis = 'Y-m-d H:i:s';
        $Ymd = 'Y-m-d';
        $His = 'H:i:s';

        $format = str_replace(array('C', 'E', 'J',
                                    'r'),
                              array($YmdHis, $Ymd, $His,
                                    'D, d M Y H:i:s O'),
                              $format);

        // DO Calculate whether the date is in daylight saving time or not.

        $month_string = zDatetime::getMonthString($this->month);
        $weekday = zDatetime::getWeekday($this->year, $this->month, $this->date);
        $weekday_string = zDatetime::getWeekdayString($weekday);

        $ampm = ($this->hour < 12 ? 'AM' : 'PM');

        $date_suffix = (($this->date % 10 == 1 && $this->date != 11) ? 'st' : ($this->date % 10 == 2 && $this->date != 12) ? 'nd' : ($this->date % 10 == 3 && $this->date != 13) ? 'rd' : 'th');

        if($this->is_local)
        {
            $timezone_hour = $this->getTimezoneHour($this->timezone);
            $timezone_hour_minute = $this->getTimezoneHourMinute($this->timezone);
        }
        else
        {
            $timezone_hour = '+0000';
            $timezone_hour_minute = '+00:00';
        }

        $leapyear = intval(zDatetime::isLeapYear($this->year));

        $yeardate = zDatetime::getYearDate($this->year, $this->month, $this->date);

        $monthdatecount = zDatetime::getMonthDateCount($this->year, $this->month);

        $iso8601string = $this->getISO8601String();

        $timestamp = intval($this->toTimestamp());

        $output = replaceChar(
            array('d', 'j', 'm', 'n', 'Y', 'y', 'g',
                  'G', 'h', 'H', 'i', 's', // ymdhis numbers
                  'F', 'M', // month words
                  'D', 'l', 'N', 'w', // weekday numbers & words
                  'a', 'A', // hour words
                  'S', // day words
                  'O', 'P', // timezones
                  'L', // leap year
                  'z', // date of the year
                  't', // date count of the month
                  'u', // millisecond
                  'c', // ISO-8601 date
                  'U'), // timestamp
            array(sprintf('%02d', $this->date), $this->date, sprintf('%02d', $this->month), $this->month, $this->year, $this->year % 100, (($this->hour - 1) % 12) + 1,
                  $this->hour, sprintf('%02d', (($this->hour - 1) % 12) + 1), sprintf('%02d', $this->hour), sprintf('%02d', $this->minute), sprintf('%02d', $this->second),
                  $month_string, substr($month_string, 0, 3),
                  substr($weekday_string, 0, 3), $weekday_string, ($weekday == 0 ? 7 : $weekday), $weekday,
                  strtolower($ampm), $ampm,
                  $date_suffix,
                  $timezone_hour, $timezone_hour_minute,
                  $leapyear,
                  $yeardate,
                  $monthdatecount,
                  0 /* millisecond = 0 */,
                  $iso8601string,
                  $timestamp),
            $format
        );

        return $output;
    }

    /**
     * @brief 현재 instance의 날짜/시간을 저장할 수 있는 문자열 형태로 변환
     **/
    function serialize()
    {
        $this->setUTC();

        return sprintf('%04d%02d%02d%02d%02d%02d', $this->year, $this->month, $this->date, $this->hour, $this->minute, $this->second);
    }

    /**
     * @brief 현재 instance의 날짜/시간에 주어진 날짜/시간을 더함
     **/
    function add($year, $month, $date, $hour, $minute, $second)
    {
        $this->second += $second;
        for(; $this->second >= 60; ++ $this->minute, $this->second -= 60);
        for(; $this->second < 0; -- $this->minute, $this->second += 60);

        $this->minute += $minute;
        for(; $this->minute >= 60; ++ $this->hour, $this->minute -= 60);
        for(; $this->minute < 0; -- $this->hour, $this->minute += 60);

        $this->hour += $hour;
        for(; $this->hour >= 24; ++ $this->date, $this->hour -= 24);
        for(; $this->hour < 0; -- $this->date, $this->hour += 24);

        $this->date += $date;
        while($this->date > ($count = zDatetime::getMonthDateCount($this->year, $this->month)))
        {
            ++ $this->month;
            for(; $this->month > 12; ++ $this->year, $this->month -= 12);
            $this->date -= $count;
        }
        while($this->date < 1)
        {
            -- $this->month;
            for(; $this->month < 1; -- $this->year, $this->month += 12);
            $this->date += zDatetime::getMonthDateCount($this->year, $this->month);
        }

        $this->month += $month;
        for(; $this->month > 12; ++ $this->year, $this->month -= 12);
        for(; $this->month < 1; -- $this->year, $this->month += 12);

        if($this->date > ($count = zDatetime::getMonthDateCount($this->year, $this->month)))
        {
            ++ $this->month;
            if($this->month > 12)
            {
                ++ $this->year;
                $this->month -= 12;
            }
            $this->date -= $count;
        }

        $this->year += $year;
    }

    /**
     * @brief 현재 instance의 날짜/시간에 주어진 날짜/시간을 뺌
     **/
    function subtract($year, $month, $date, $hour, $minute, $second)
    {
        $this->second -= $second;
        for(; $this->second >= 60; ++ $this->minute, $this->second -= 60);
        for(; $this->second < 0; -- $this->minute, $this->second += 60);

        $this->minute -= $minute;
        for(; $this->minute >= 60; ++ $this->hour, $this->minute -= 60);
        for(; $this->minute < 0; -- $this->hour, $this->minute += 60);

        $this->hour -= $hour;
        for(; $this->hour >= 24; ++ $this->date, $this->hour -= 24);
        for(; $this->hour < 0; -- $this->date, $this->hour += 24);

        $this->date -= $date;
        while($this->date > ($count = zDatetime::getMonthDateCount($this->year, $this->month)))
        {
            ++ $this->month;
            for(; $this->month > 12; ++ $this->year, $this->month -= 12);
            $this->date -= $count;
        }
        while($this->date < 1)
        {
            -- $this->month;
            for(; $this->month < 1; -- $this->year, $this->month += 12);
            $this->date += zDatetime::getMonthDateCount($this->year, $this->month);
        }

        $this->month -= $month;
        for(; $this->month > 12; ++ $this->year, $this->month -= 12);
        for(; $this->month < 1; -- $this->year, $this->month += 12);

        if($this->date > ($count = zDatetime::getMonthDateCount($this->year, $this->month)))
        {
            ++ $this->month;
            if($this->month > 12)
            {
                ++ $this->year;
                $this->month -= 12;
            }
            $this->date -= $count;
        }

        $this->year -= $year;
    }

    /**
     * @brief serialize된 문자열을 zDatetime instance로 변환
     **/
    function unserialize($string)
    {
        return new zDatetime($string, null, null, null, null, null, true);
    }

    /**
     * @brief YYYYMMDDHHIISS의 시간값으로 zDatetime instance 생성
     **/
    function fromString($string, $utc = false)
    {
        return new zDatetime(strval($string), null, null, null, null, null, $utc);
    }

    /**
     * @brief Unix timestamp로 zDatetime instance 생성
     **/
    function fromTimestamp($timestamp, $utc = false)
    {
        return new zDatetime(date('YmdHis', $timestamp), null, null, null, null, null, $utc);
    }

    /**
     * @brief 현재 시간으로 zDatetime instance 생성
     **/
    function now()
    {
        return new zDatetime();
    }
}
?>