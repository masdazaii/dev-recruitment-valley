<?php

namespace Helper;

use \IntlDateFormatter;

class DateHelper
{
    /**
     * localize date function
     * using PHP IntlDateFormatter
     *
     * parameters for IntlDateFormatter :
     * $localization : default 'en_US'
     * $dateType : oneOf ['none', 'short', 'medium', 'full']
     * $dateType : oneOf ['none', 'short', 'medium', 'full']
     * $timezone : ex 'UTC' or 'Asia/Jakarta'
     * $pattern : date format, dd MMMM yyyy HH:mm
     *
     * @param string $date
     * @param string $locale
     * @return mixed
     */
    public static function doLocale($date, $locale = 'en_US'): mixed
    {
        $dateFormatter = new IntlDateFormatter($locale, IntlDateFormatter::FULL, IntlDateFormatter::FULL);
        $dateFormatter->setPattern('dd MMMM yyyy, HH:mm');

        if (self::_isValidTimestamp($date)) {
            return $dateFormatter->format($date);
        } else {
            return $dateFormatter->format(strtotime($date));
        }
    }

    private static function _isValidTimestamp($timestamp)
    {
        return ((int) (string) $timestamp === $timestamp)
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX);
    }
}
