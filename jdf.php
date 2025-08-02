<?php
date_default_timezone_set('Asia/Tehran');
function jdate($format, $timestamp = '', $none = '', $time_zone = 'Asia/Tehran', $tr_num = 'fa') {
    $T_sec = 0;
    $jdate_month_name = array(
        "فروردین", "اردیبهشت", "خرداد", "تیر",
        "مرداد", "شهریور", "مهر", "آبان",
        "آذر", "دی", "بهمن", "اسفند"
    );
    $jdate_day_name = array(
        "شنبه", "یکشنبه", "دوشنبه", "سه‌شنبه",
        "چهارشنبه", "پنج‌شنبه", "جمعه"
    );

    if ($time_zone != 'local') date_default_timezone_set($time_zone);
    $ts = ($timestamp == '') ? time() : tr_num($timestamp, 'en') + $T_sec;

    $date = explode('_', date('H_i_j_n_w_Y', $ts));
    list($h, $i, $j, $n, $w, $Y) = $date;

    list($jy, $jm, $jd) = gregorian_to_jalali($Y, $n, $j);

    $result = '';
    for ($i = 0; $i < strlen($format); $i++) {
        $sub = substr($format, $i, 1);
        switch ($sub) {
            case 'Y':
                $result .= $jy;
                break;
            case 'y':
                $result .= substr($jy, 2, 2);
                break;
            case 'm':
                $result .= ($jm < 10 ? '0' : '') . $jm;
                break;
            case 'n':
                $result .= $jm;
                break;
            case 'd':
                $result .= ($jd < 10 ? '0' : '') . $jd;
                break;
            case 'j':
                $result .= $jd;
                break;
            case 'F':
                $result .= $jdate_month_name[$jm - 1];
                break;
            case 'l':
                $result .= $jdate_day_name[$w];
                break;
            case 'H':
                $result .= $h;
                break;
            case 'i':
                $result .= $i;
                break;
            case 's':
                $result .= date('s', $ts);
                break;
            default:
                $result .= $sub;
        }
    }

    return ($tr_num != 'en') ? tr_num($result, 'fa') : $result;
}

function tr_num($str, $mod = 'fa', $mf = '٫') {
    $num_a = array('۰','۱','۲','۳','۴','۵','۶','۷','۸','۹');
    $key_a = array('0','1','2','3','4','5','6','7','8','9');
    return $mod == 'fa' ? str_replace($key_a, $num_a, $str) : str_replace($num_a, $key_a, $str);
}

function gregorian_to_jalali($g_y, $g_m, $g_d) {
    $g_days_in_month = array(31,28,31,30,31,30,31,31,30,31,30,31);
    $j_days_in_month = array(31,31,31,31,31,31,30,30,30,30,30,29);

    $gy = (int)$g_y - 1600;
    $gm = (int)$g_m - 1;
    $gd = (int)$g_d - 1;

    $g_day_no = 365*$gy + (int)(($gy+3)/4) - (int)(($gy+99)/100) + (int)(($gy+399)/400);
    for ($i=0; $i < $gm; ++$i) $g_day_no += $g_days_in_month[$i];
    if ($gm > 1 && (($gy%4==0 && $gy%100!=0) || ($gy%400==0))) ++$g_day_no;
    $g_day_no += $gd;

    $j_day_no = $g_day_no - 79;
    $j_np = (int)($j_day_no / 12053);
    $j_day_no = $j_day_no % 12053;

    $jy = 979 + 33*$j_np + 4*(int)($j_day_no/1461);
    $j_day_no %= 1461;

    if ($j_day_no >= 366) {
        $jy += (int)(($j_day_no-1)/365);
        $j_day_no = ($j_day_no-1)%365;
    }

    for ($i = 0; $i < 11 && $j_day_no >= $j_days_in_month[$i]; ++$i)
        $j_day_no -= $j_days_in_month[$i];

    $jm = $i + 1;
    $jd = $j_day_no + 1;

    return array($jy, $jm, $jd);
}
function jalali_to_gregorian($j_y, $j_m, $j_d) {
    $g_days_in_month = array(31,28,31,30,31,30,31,31,30,31,30,31);
    $j_days_in_month = array(31,31,31,31,31,31,30,30,30,30,30,29);

    $jy = (int)$j_y - 979;
    $jm = (int)$j_m - 1;
    $jd = (int)$j_d - 1;

    $j_day_no = 365*$jy + (int)($jy / 33) * 8 + (int)(($jy % 33 + 3) / 4);
    for ($i=0; $i < $jm; ++$i)
        $j_day_no += $j_days_in_month[$i];

    $j_day_no += $jd;

    $g_day_no = $j_day_no + 79;

    $gy = 1600 + 400 * (int)($g_day_no / 146097);
    $g_day_no = $g_day_no % 146097;

    $leap = true;
    if ($g_day_no >= 36525) {
        $g_day_no--;
        $gy += 100 * (int)($g_day_no / 36524);
        $g_day_no = $g_day_no % 36524;

        if ($g_day_no >= 365) $g_day_no++;
        else $leap = false;
    }

    $gy += 4 * (int)($g_day_no / 1461);
    $g_day_no %= 1461;

    if ($g_day_no >= 366) {
        $leap = false;
        $g_day_no--;
        $gy += (int)($g_day_no / 365);
        $g_day_no = $g_day_no % 365;
    }

    for ($i = 0; $g_day_no >= $g_days_in_month[$i] + ($i == 1 && $leap); $i++)
        $g_day_no -= $g_days_in_month[$i] + ($i == 1 && $leap);

    $gm = $i + 1;
    $gd = $g_day_no + 1;

    return array($gy, $gm, $gd);
}
