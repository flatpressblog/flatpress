<?php
date_default_timezone_set('UTC');

function date_strformat($format, $timestamp = null) {
	global $lang;

	// If no timestamp is specified, use the current timestamp
	if ($timestamp === null || $timestamp === 0) {
		$timestamp = date_time();
	}

	// D l day

	if (strpos($format, '%a') !== false) {
		$i = strftime_replacement('%w', $timestamp);
		$format = str_replace('%a', $lang ['date'] ['weekday_abbr'] [$i], $format);
	}

	if (strpos($format, '%A') !== false) {
		$i = strftime_replacement('%w', $timestamp);
		$format = str_replace('%A', $lang ['date'] ['weekday'] [$i], $format);
	}

	// F M month

	if (strpos($format, '%b') !== false) {
		$i = intval(strftime_replacement('%m', $timestamp)) - 1;
		$format = str_replace('%b', $lang ['date'] ['month_abbr'] [$i], $format);
	}

	if (strpos($format, '%B') !== false) {
		$i = intval(strftime_replacement('%m', $timestamp)) - 1;
		$format = str_replace('%B', $lang ['date'] ['month'] [$i], $format);
	}

	// Windows-specific customizations
	if (DIRECTORY_SEPARATOR == '\\') {
		$_win_from = array(
			'%D',
			'%h',
			'%n',
			'%r',
			'%R',
			'%t',
			'%T'
		);
		$_win_to = array(
			'%m/%d/%y',
			'%b',
			"\n",
			'%I:%M:%S %p',
			'%H:%M',
			"\t",
			'%H:%M:%S'
		);
		if (strpos($format, '%e') !== false) {
			$_win_from [] = '%e';
			$_win_to [] = sprintf('%\' 2d', date('j', $timestamp));
		}
		if (strpos($format, '%l') !== false) {
			$_win_from [] = '%l';
			$_win_to [] = sprintf('%\' 2d', date('h', $timestamp));
		}
		$format = str_replace($_win_from, $_win_to, $format);
	}

	return strftime_replacement($format, $timestamp);
}

/**
 * Returns the current UNIX timestamp adjusted by a time offset in hours.
 *
 * If no offset is provided, the function attempts to retrieve it from
 * $fp_config['locale']['timeoffset']. If the offset is invalid or not set,
 * it defaults to 0 (no offset).
 *
 * @param int|float|string|null $offset Time offset in hours. Can be null to use the default from configuration.
 * @return int UNIX timestamp with the offset applied (in seconds).
 */
function date_time($offset = null) {
	global $fp_config;

	if (!is_numeric($offset)) {
		if (isset($fp_config ['locale'] ['timeoffset']) && is_numeric($fp_config ['locale'] ['timeoffset'])) {
			$offset = $fp_config ['locale'] ['timeoffset'];
		} else {
			$offset = 0;
		}
	}

	return time() + (int)$offset * 3600;
}

/*
 * function date_now($offset=0) {
 * $timestamp = gmtime();
 * $time_stamp = intval($timestamp) + intval($offset) * 60 * 60;
 * return date($format, $time_stamp);
 *
 * }
 */

/**
 * Takes filename and extension as a parameter, strips
 * alphabetic chars (ascii) from filename and "parses" the date;
 * In fact it's just a substr, counting on the fact filename should be
 * "prefix%y%m%d-%H%M%S.ext"
 */
function date_from_id($id) {

	if (!is_string($id) || strlen($id) < 13) {
		return array();
	}

	$strdate = substr($id, -13);

	if (!preg_match('/[0-9]{6}-[0-9]{6}/', $strdate)) {
		return array();
	}

	$arr ['y'] = substr($strdate, 0, 2);
	$arr ['m'] = substr($strdate, 2, 2);
	$arr ['d'] = substr($strdate, 4, 2);

	$arr ['H'] = substr($strdate, 7, 2);
	$arr ['M'] = substr($strdate, 9, 2);
	$arr ['S'] = substr($strdate, 11, 2);

	$arr ['ymd'] = $arr ['y'] . $arr ['m'] . $arr ['d'];
	$arr ['HMS'] = $arr ['H'] . $arr ['M'] . $arr ['S'];

	if (
		checkdate((int)$arr ['m'], (int)$arr ['d'], (int)$arr ['y']) &&
		(int)$arr ['H'] >= 0 && (int)$arr ['H'] <= 23 &&
		(int)$arr ['M'] >= 0 && (int)$arr ['M'] <= 59 &&
		(int)$arr ['S'] >= 0 && (int)$arr ['S'] <= 59
	) {
		$arr ['time'] = mktime(
			(int)$arr ['H'], (int)$arr ['M'], (int)$arr ['S'],
			(int)$arr ['m'], (int)$arr ['d'], (int)$arr ['y']
		);
	} else {
		$arr ['time'] = false;
	}

	return $arr;
}

/**
 * Locale-formatted strftime using \IntlDateFormatter (PHP 8.1 compatible)
 * This provides a cross-platform alternative to strftime() for when it will be removed from PHP.
 * Note that output can be slightly different between libc sprintf and this function as it is using ICU.
 *
 * Usage:
 * use function \PHP81_BC\strftime;
 * echo strftime('%A %e %B %Y %X', new \DateTime('2021-09-28 00:00:00'), 'fr_FR');
 *
 * Original use:
 * \setlocale('fr_FR.UTF-8', LC_TIME);
 * echo \strftime('%A %e %B %Y %X', strtotime('2021-09-28 00:00:00'));
 *
 * @param string $format Date format
 * @param int|string|\DateTimeInterface|null $timestamp Timestamp
 * @param string|null $locale
 * @return string
 * @author BohwaZ <https://bohwaz.net/>
 */
function strftime_replacement(string $format, $timestamp = null, ?string $locale = null): string {
	if (null === $timestamp) {
		$timestamp = new \DateTime();
	} elseif (is_numeric($timestamp)) {
		$timestamp = date_create('@' . $timestamp);

		if ($timestamp instanceof \DateTimeInterface) {
			$timezone = date_default_timezone_get();

			// Check whether the time zone is a valid character string and whether it exists
			if (in_array($timezone, \DateTimeZone::listIdentifiers())) {
				$timestamp->setTimezone(new \DateTimeZone($timezone));
			} else {
				// Fallback to UTC if the time zone is invalid
				$timestamp->setTimezone(new \DateTimeZone('UTC'));
			}
		}
	} elseif (is_string($timestamp)) {
		$timestamp = date_create($timestamp);
	}

	if (!($timestamp instanceof \DateTimeInterface)) {
		throw new \InvalidArgumentException('$timestamp argument is neither a valid UNIX timestamp, a valid date-time string or a DateTime object.');
	}

	// Locale handling
	$locale = substr((string) $locale, 0, 5);

	if (!$locale || !class_exists('ResourceBundle') || !in_array($locale, \ResourceBundle::getLocales(''))) {
		$locale = 'en_US';
	}

	// IntlDateFormatter caching mechanism (with fallback for hosts without ext-intl)
	static $formatter_cache = []; // Caching array
	$intl_available = class_exists('IntlDateFormatter');

	$cache_key = md5($locale . '|' . $format . '|' . $timestamp->getTimezone()->getName());
	if (!isset($formatter_cache [$cache_key])) {
		$intl_formats = [
			'%a' => 'EEE', // An abbreviated textual representation of the day Sun through Sat
			'%A' => 'EEEE', // A full textual representation of the day Sunday through Saturday
			'%b' => 'MMM', // Abbreviated month name, based on the locale Jan through Dec
			'%B' => 'MMMM', // Full month name, based on the locale January through December
			'%h' => 'MMM' // Abbreviated month name, based on the locale (an alias of %b) Jan through Dec
		];

		if ($intl_available) {
			// Formatter function to create IntlDateFormatter
			$intl_formatter = function (\DateTimeInterface $timestamp, string $format) use ($intl_formats, $locale) {
				$tz = $timestamp->getTimezone();
				$date_type = \IntlDateFormatter::FULL;
				$time_type = \IntlDateFormatter::FULL;
				$pattern = '';

				// %c = Preferred date and time stamp based on locale
				if ($format == '%c') {
					$date_type = \IntlDateFormatter::LONG;
					$time_type = \IntlDateFormatter::SHORT;
				}
				// %x = Preferred date representation based on locale, without the time
				elseif ($format == '%x') {
					$date_type = \IntlDateFormatter::SHORT;
					$time_type = \IntlDateFormatter::NONE;
				}
				// %X = Preferred time representation based on locale, without the date
				elseif ($format == '%X') {
					$date_type = \IntlDateFormatter::NONE;
					$time_type = \IntlDateFormatter::MEDIUM;
				} else {
					$pattern = $intl_formats [$format] ?? $format;
				}

				// Return formatted date/time
				return (new \IntlDateFormatter($locale, $date_type, $time_type, $tz, null, $pattern))->format($timestamp);
			};
			$formatter_cache [$cache_key] = $intl_formatter;
		} else {
			// Fallback for hosts without ext-intl: non-localized but functional.
			$formatter_cache [$cache_key] = function (\DateTimeInterface $timestamp, string $format) {
				switch ($format) {
					case '%a':
						return $timestamp->format('D');
					case '%A':
						return $timestamp->format('l');
					case '%b':
					case '%h':
						return $timestamp->format('M');
					case '%B':
						return $timestamp->format('F');
					case '%X':
						return $timestamp->format('H:i:s');
					case '%x':
						return $timestamp->format('Y-m-d');
					case '%c':
					default:
						return $timestamp->format('Y-m-d H:i:s');
				}
			};
		}
	}

	// Same order as https://www.php.net/manual/en/function.strftime.php
	$translation_table = [
		// Day
		'%a' => $formatter_cache [$cache_key],
		'%A' => $formatter_cache [$cache_key],
		'%d' => 'd',
		'%e' => function ($timestamp) {
			return sprintf('% 2u', $timestamp->format('j'));
		},
		'%j' => function ($timestamp) {
			// Day number in year, 001 to 366
			return sprintf('%03d', $timestamp->format('z') + 1);
		},
		'%u' => 'N',
		'%w' => 'w',

		// Week
		'%U' => function ($timestamp) {
			// Number of weeks between date and first Sunday of year
			$day = new \DateTime(sprintf('%d-01 Sunday', $timestamp->format('Y')));
			return sprintf('%02u', 1 + ($timestamp->format('z') - $day->format('z')) / 7);
		},
		'%V' => 'W',
		'%W' => function ($timestamp) {
			// Number of weeks between date and first Monday of year
			$day = new \DateTime(sprintf('%d-01 Monday', $timestamp->format('Y')));
			return sprintf('%02u', 1 + ($timestamp->format('z') - $day->format('z')) / 7);
		},

		// Month
		'%b' => $formatter_cache [$cache_key],
		'%B' => $formatter_cache [$cache_key],
		'%h' => $formatter_cache [$cache_key],
		'%m' => 'm',

		// Year
		'%C' => function ($timestamp) {
			// Century (-1): 19 for 20th century
			return floor($timestamp->format('Y') / 100);
		},
		'%g' => function ($timestamp) {
			return substr($timestamp->format('o'), -2);
		},
		'%G' => 'o',
		'%y' => 'y',
		'%Y' => 'Y',

		// Time
		'%H' => 'H',
		'%k' => function ($timestamp) {
			return sprintf('% 2u', $timestamp->format('G'));
		},
		'%I' => 'h',
		'%l' => function ($timestamp) {
			return sprintf('% 2u', $timestamp->format('g'));
		},
		'%M' => 'i',
		'%p' => 'A', // AM PM (this is reversed on purpose!)
		'%P' => 'a', // am pm
		'%r' => 'h:i:s A', // %I:%M:%S %p
		'%R' => 'H:i', // %H:%M
		'%S' => 's',
		'%T' => 'H:i:s', // %H:%M:%S
		'%X' => $formatter_cache [$cache_key], // Preferred time representation based on locale, without the date

		// Timezone
		'%z' => 'O',
		'%Z' => 'T',

		// Time and Date Stamps
		'%c' => $formatter_cache [$cache_key],
		'%D' => 'm/d/Y',
		'%F' => 'Y-m-d',
		'%s' => 'U',
		'%x' => $formatter_cache [$cache_key]
	];

	$out = preg_replace_callback('/(?<!%)(%[a-zA-Z])/', function ($match) use ($translation_table, $timestamp) {
		// Handle newlines and tabs
		if ($match [1] == '%n') {
			return "\n";
		} elseif ($match [1] == '%t') {
			return "\t";
		}

		// Check if the format is known in the translation table
		if (!isset($translation_table [$match [1]])) {
			// If unknown, return a safe default, like the literal format string (unprocessed)
			return $match [0];
		}

		$replace = $translation_table [$match [1]];

		if (is_string($replace)) {
			return $timestamp->format($replace);
		} else {
			return $replace($timestamp, $match [1]);
		}
	}, $format);

	$out = str_replace('%%', '%', $out);
	return $out;
}
?>
