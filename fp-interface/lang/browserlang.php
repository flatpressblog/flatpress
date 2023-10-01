<?php
/**
 * Get the language code from the browser
 *
 * @param array Allowed Languages "array('cs-cz','de-de','en-us','it-it','ja-jp','nl-nl','pt-br','sl-si')"
 * @param string Default language
 * @param string Language string from HTTP-Header
 * @param bool Strict-Mode
 * @return array|int Data as array or null
 */
error_reporting(0);
function getBrowserLanguage($arrAllowedLanguages, $strDefaultLanguage, $strLangVariable = null, $boolStrictMode = true) {
	if (isset($_REQUEST ['language'])) {
		if (strlen($_REQUEST ['language']) == 2) {
			return strtolower($_REQUEST ['language']);
		}
		foreach ($arrAllowedLanguages as $strValue) {
			if (preg_match('/^' . $strValue . '\-/i', $_REQUEST ['language'])) {
				return strtolower($strValue);
			}
		}
	}
	if (!is_array($arrAllowedLanguages)) {
		if (strpos($arrAllowedLanguages,';')) {
			$array = explode(';', $arrAllowedLanguages);
			$arrAllowedLanguages = $array;
		}
	}
	if (!isset($_SERVER ['HTTP_ACCEPT_LANGUAGE'])) {
		return $arrAllowedLanguages [0];
	}
	if ($strLangVariable === null) $strLangVariable = $_SERVER ['HTTP_ACCEPT_LANGUAGE'];
	if (empty($strLangVariable)) return $strDefaultLanguage;
	$arrAcceptedLanguages = preg_split('/,\s*/', $strLangVariable);
	$strCurrentLanguage = $strDefaultLanguage;
	$intCurrentQ = 0;
	foreach ($arrAcceptedLanguages as $arrAcceptedLanguage) {
		$boolResult = preg_match ('/^([a-z]{1,8}(?:-[a-z]{1,8})*)' . '(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i', $arrAcceptedLanguage, $arrMatches);
		if (!$boolResult) continue;
		$arrLangCode = explode ('-', $arrMatches [1]);
		if (isset($arrMatches [2]))
			$intLangQuality = (float)$arrMatches [2];
		else
			$intLangQuality = 1.0;
		while (count ($arrLangCode)) {
			if (!is_array($arrAllowedLanguages)) $arrAllowedLanguages = array($arrAllowedLanguages);
			if (in_array (strtolower (join ('-', $arrLangCode)), $arrAllowedLanguages)) {
				if ($intLangQuality > $intCurrentQ) {
					$strCurrentLanguage = strtolower (join ('-', $arrLangCode));
					$intCurrentQ = $intLangQuality;
					break;
				}
			}
			if ($boolStrictMode) break;
			array_pop ($arrLangCode);
		}
	}
	return $strCurrentLanguage;
}

// accept the following languages, otherwise fall back to "en-us"
$browserLang = getBrowserLanguage(array('cs-cz', 'de-de', 'en-us', 'it-it', 'ja-jp', 'nl-nl', 'pt-br', 'sl-si'), 'en-us');

?>
