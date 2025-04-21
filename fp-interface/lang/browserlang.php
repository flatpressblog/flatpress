<?php
/**
 * Determines the language of the browser based on a list of accepted languages
 *
 * @param array|string $arrAllowedLanguages List of accepted languages (e.g. 'de-de', 'en-us', da-dk)
 * @param string $strDefaultLanguage Default language if no match is found
 * @param string|null $strLangVariable Optional language from the HTTP header (by default $_SERVER['HTTP_ACCEPT_LANGUAGE'])
 * @param bool $boolStrictMode If true, only exact match is accepted
 * @return string The determined language (e.g. 'de-de')
 */
function getBrowserLanguage($arrAllowedLanguages, $strDefaultLanguage, $strLangVariable = null, $boolStrictMode = false) {

	// Check whether the language was explicitly transmitted via URL parameter 'language'
	if (isset($_REQUEST ['language'])) {

		// If only a general language code (e.g. "de") is transferred
		if (strlen($_REQUEST ['language']) == 2) {
			// Convert to lower case
			$genericLang = strtolower($_REQUEST ['language']);

			// Search for a specific variant of this language (e.g. "de-de")
			foreach ($arrAllowedLanguages as $allowedLang) {
				if (strpos($allowedLang, $genericLang . '-') === 0) {
					// Use the most specific variant
					return strtolower($allowedLang);
				}
			}

			// Fallback to generic language if no specific one is found
			return $genericLang;
		}

		// Search for an exact match in the list of permitted languages
		foreach ($arrAllowedLanguages as $strValue) {
			if (preg_match('/^' . $strValue . '\-/i', $_REQUEST ['language'])) {
				return strtolower($strValue);
			}
		}
	}

	// If the list of permitted languages was passed as a string, convert it into an array
	if (!is_array($arrAllowedLanguages)) {
		if (strpos($arrAllowedLanguages, ';')) {
			$array = explode(';', $arrAllowedLanguages);
			$arrAllowedLanguages = $array;
		}
	}

	// If the HTTP_ACCEPT_LANGUAGE header is not set, use the fallback language
	if (!isset($_SERVER ['HTTP_ACCEPT_LANGUAGE']) || empty($_SERVER ['HTTP_ACCEPT_LANGUAGE'])) {
		return $strDefaultLanguage;
	}

	// Use the default HTTP_ACCEPT_LANGUAGE if no explicit language was passed
	if ($strLangVariable === null) {
		$strLangVariable = $_SERVER ['HTTP_ACCEPT_LANGUAGE'];
	}

	// If the language variable is empty, use the default language
	if (empty($strLangVariable)) {
		return $strDefaultLanguage;
	}

	// Break down the HTTP_ACCEPT_LANGUAGE header into a list of languages with priorities
	$arrAcceptedLanguages = preg_split('/,\s*/', $strLangVariable);
	$strCurrentLanguage = $strDefaultLanguage;
	// Current quality assessment of the language found
	$intCurrentQ = 0;

	// Run through all languages accepted by the browser
	foreach ($arrAcceptedLanguages as $arrAcceptedLanguage) {

		// Check the format of the language specification (e.g. "de-de;q=0.8")
		$boolResult = preg_match('/^([a-z]{1,8}(?:-[a-z]{1,8})*)' . '(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i', $arrAcceptedLanguage, $arrMatches);
		if (!$boolResult) {
			// Skip invalid entries
			continue;
		}

		// Break down the language into main and subcategories
		$arrLangCode = explode('-', $arrMatches [1]);
		$intLangQuality = isset($arrMatches [2]) ? (float)$arrMatches [2] : 1.0; // Default Q value is 1.0

		// Search the permitted languages for matches
		while (count($arrLangCode)) {
			// Create the language combination
			$lang = strtolower(join('-', $arrLangCode));

			// Check whether the language is in the list of permitted languages
			if (in_array($lang, $arrAllowedLanguages)) {
				if ($intLangQuality > $intCurrentQ) {
					// Update the current language
					$strCurrentLanguage = $lang;
					// Update the highest quality
					$intCurrentQ = $intLangQuality;
					// Abort the loop as the best match has been found
					break;
				}
			}

			// Check for a generic language such as "de" if strict mode is deactivated
			if (!$boolStrictMode && count($arrLangCode) == 1) {
				foreach ($arrAllowedLanguages as $allowedLang) {
					if (strpos($allowedLang, $lang . '-') === 0) {
						$strCurrentLanguage = strtolower($allowedLang);
						$intCurrentQ = $intLangQuality;
						// End both loops, as a match was found
						break 2;
					}
				}
			}

			if ($boolStrictMode) {
				// Abort if no exact match is found
				break;
			}

			// Remove the most specific region and try again
			array_pop($arrLangCode);
		}
	}

	// Return of the determined language
	return $strCurrentLanguage;
}

// Only enter another language abbreviation here, if the language files are available for FlatPress, the plugins, the admin area and for the setup!
// Accept the following languages, otherwise fall back to "en-us"
$browserLang = getBrowserLanguage(array('cs-cz', 'da-dk', 'de-de', 'es-es', 'fr-fr', 'el-gr', 'it-it', 'ja-jp', 'nl-nl', 'pt-br', 'ru-ru', 'sl-si', 'tr-tr'), 'en-us');
?>
