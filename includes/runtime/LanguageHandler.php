<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

/**
 * Class to handler language translations
 */
class Vtiger_Language_Handler {

	//Contains module language translations
	protected static $languageContainer;
	
	/**
	 * Functions that gets translated string
	 * @param <String> $key - string which need to be translated
	 * @param <String> $module - module scope in which the translation need to be check
	 * @return <String> - translated string
	 */
	public static function getTranslatedString($key, $module = '', $currentLanguage = '', $context = '', $count = '') {
		if (empty($currentLanguage)) $currentLanguage = self::getLanguage();
		$defaultLanguage = vglobal('default_language');
		if (!empty($defaultLanguage) && strcasecmp($defaultLanguage, $currentLanguage) !== 0) $fallback = true;
		else $fallback = false;
		
		//Decoding for Start Date & Time and End Date & Time 
		if (!is_array($key)) $key = decode_html($key);
		
		//Searching for translations
		if(!empty($context) && !empty($count)) {
			$searchKey = $key.'_'.$context;
			$translatedString = self::getLanguageTranslatedString($currentLanguage, self::getPluralizedKey($searchKey, $currentLanguage, $count), $module);
			if($translatedString == null && $fallback == true) $translatedString = self::getLanguageTranslatedString($defaultLanguage, self::getPluralizedKey($searchKey, $defaultLanguage, $count), $module);
		}
		
		if(!empty($context) && $translatedString == null) {
			$searchKey = $key.'_'.$context;			
			$translatedString = self::getLanguageTranslatedString($currentLanguage, $searchKey, $module);
			if($translatedString == null && $fallback == true) $translatedString = self::getLanguageTranslatedString($defaultLanguage, $searchKey, $module);
		}
		
		if(!empty($count) && $translatedString == null) {
			$translatedString = self::getLanguageTranslatedString($currentLanguage, self::getPluralizedKey($key, $currentLanguage, $count), $module);
			if($translatedString == null && $fallback == true) $translatedString = self::getLanguageTranslatedString($defaultLanguage, self::getPluralizedKey($key, $defaultLanguage, $count), $module);
		}
		
		if(empty($context) && empty($count)) {
			$translatedString = self::getLanguageTranslatedString($currentLanguage, $key, $module);
			if($translatedString == null && $fallback == true) $translatedString = self::getLanguageTranslatedString($defaultLanguage, $key, $module);
		}
		
		// If translation is not found then return label
		if ($translatedString === null) {
			$translatedString = $key;
		}
		return $translatedString;
	}
	
	/**
	 * Function returns language specific translated string
	 * @param <String> $language - en_us etc
	 * @param <String> $key - label
	 * @param <String> $module - module name
	 * @return <String> translated string or null if translation not found
	 */
	public static function getLanguageTranslatedString($language, $key, $module = '') {
		$moduleStrings = array();

		$module = str_replace(':', '.', $module);
		if (is_array($module))
			return null;
		$moduleStrings = self::getModuleStringsFromFile($language, $module);
		if (!empty($moduleStrings['languageStrings'][$key])) {
			return $moduleStrings['languageStrings'][$key];
		}
		// Lookup for the translation in base module, in case of sub modules, before ending up with common strings
		if (strpos($module, '.') > 0) {
			$baseModule = substr($module, 0, strpos($module, '.'));
			if ($baseModule == 'Settings') {
				$baseModule = 'Settings.Vtiger';
			}
			$moduleStrings = self::getModuleStringsFromFile($language, $baseModule);
			if (!empty($moduleStrings['languageStrings'][$key])) {
				return $moduleStrings['languageStrings'][$key];
			}
		}

		$commonStrings = self::getModuleStringsFromFile($language);
		if (!empty($commonStrings['languageStrings'][$key]))
			return $commonStrings['languageStrings'][$key];

		return null;
	}

	/**
	 * Functions that gets translated string for Client side
	 * @param <String> $key - string which need to be translated
	 * @param <String> $module - module scope in which the translation need to be check
	 * @return <String> - translated string
	 */
	public static function getJSTranslatedString($language, $key, $module = '') {
		$moduleStrings = array();

		$module = str_replace(':', '.', $module);
		$moduleStrings = self::getModuleStringsFromFile($language, $module);
		if (!empty($moduleStrings['jsLanguageStrings'][$key])) {
			return $moduleStrings['jsLanguageStrings'][$key];
		}
		// Lookup for the translation in base module, in case of sub modules, before ending up with common strings
		if (strpos($module, '.') > 0) {
			$baseModule = substr($module, 0, strpos($module, '.'));
			if ($baseModule == 'Settings') {
				$baseModule = 'Settings.Vtiger';
			}
			$moduleStrings = self::getModuleStringsFromFile($language, $baseModule);
			if (!empty($moduleStrings['jsLanguageStrings'][$key])) {
				return $moduleStrings['jsLanguageStrings'][$key];
			}
		}

		$commonStrings = self::getModuleStringsFromFile($language);
		if (!empty($commonStrings['jsLanguageStrings'][$key]))
			return $commonStrings['jsLanguageStrings'][$key];

		return $key;
	}

	/**
	 * Function that returns translation strings from file
	 * @global <array> $languageStrings - language specific string which is used in translations
	 * @param <String> $module - module Name
	 * @return <array> - array if module has language strings else returns empty array
	 */
	public static function getModuleStringsFromFile($language, $module='Vtiger'){
		$module = str_replace(':', '.', $module);
		if(empty(self::$languageContainer[$language][$module])){
			$qualifiedName = 'languages.'.$language.'.'.$module;
			$file = Vtiger_Loader::resolveNameToPath($qualifiedName);
			$languageStrings = $jsLanguageStrings = array();
			if(file_exists($file)){
				require $file;
				self::$languageContainer[$language][$module]['languageStrings'] = $languageStrings;
				self::$languageContainer[$language][$module]['jsLanguageStrings'] = $jsLanguageStrings;
			}
		}
		$return = array();
		if(isset(self::$languageContainer[$language][$module])){
			$return = self::$languageContainer[$language][$module];
		}
		return $return;
	}

	/**
	 * Returns the language string (i.e. folder name) to be used for translation
	 * Try to get it from database, next from request headers and finally from global configuration
	 * @return	<String> 	-  Language string to be used
	 */
	public static function getLanguage() {
		// First use Database language value		
		$userModel = Users_Record_Model::getCurrentUserModel();		
		if(!empty($userModel)) $locale = $userModel->get('language');
		if(!empty($locale)) return $locale;
		
		//Fallback : Read the Accept-Language header of the request (really useful for login screen)
		if( empty($locale) && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ) {
			//Getting all languages in an array			
			$languages = self::getAllLanguages();
			//Extracting locales strings from header
			preg_match_all("/([a-z-]+)[,;]/i", $_SERVER['HTTP_ACCEPT_LANGUAGE'], $locales);			
			//Looping in found locales and test match against languages
			foreach($locales[1] as $locale) {
				foreach($languages as $code=>$lang) {
					//First case insensitive comparison
					if(strcasecmp($code, $locale) === 0) return $code;
					//Second case with replacing '-' by '_'
					if(strcasecmp($code, str_replace('-','_',$locale) ) === 0) return $code;
					//Finally, try with short 2 letters country code
					if(strcasecmp(substr($code, 0, 2), $locale) === 0) return $code;
				}
			}
		}		
		
		// Last fallback : global configuration
		return vglobal('default_language');
	}

	/**
	 * Function that returns current language short name
	 * @return <String> -
	 */
	public static function getShortLanguageName() {
		$language = self::getLanguage();
		return substr($language, 0, 2);
	}

	/**
	 * Function returns module strings
	 * @param <String> $module - module Name
	 * @param <String> languageStrings or jsLanguageStrings
	 * @return <Array>
	 */
	public static function export($module, $type = 'languageStrings') {
		$userSelectedLanguage = self::getLanguage();
		$defaultLanguage = vglobal('default_language');
		$languages = array($userSelectedLanguage);
		//To merge base language and user selected language translations
		if ($userSelectedLanguage != $defaultLanguage) {
			array_push($languages, $defaultLanguage);
		}
		$resultantLanguageString = array();
		foreach ($languages as $currentLanguage) {
			$exportLangString = array();

			$moduleStrings = self::getModuleStringsFromFile($currentLanguage, $module);
			if (!empty($moduleStrings[$type])) {
				$exportLangString = $moduleStrings[$type];
			}

			// Lookup for the translation in base module, in case of sub modules, before ending up with common strings
			if (strpos($module, '.') > 0) {
				$baseModule = substr($module, 0, strpos($module, '.'));
				if ($baseModule == 'Settings') {
					$baseModule = 'Settings.Vtiger';
				}
				$moduleStrings = self::getModuleStringsFromFile($currentLanguage, $baseModule);
				if (!empty($moduleStrings[$type])) {
					$exportLangString += $commonStrings[$type];
				}
			}

			$commonStrings = self::getModuleStringsFromFile($currentLanguage);
			if (!empty($commonStrings[$type])) {
				$exportLangString += $commonStrings[$type];
			}
			$resultantLanguageString += $exportLangString;
		}

		return $resultantLanguageString;
	}

	/**
	 * Function to returns all language information
	 * @return <Array>
	 */
	public static function getAllLanguages() {
		return Vtiger_Language::getAll();
	}

	/**
	 * Function to get the label name of the Language package
	 * @param <String> $name
	 */
	public static function getLanguageLabel($name) {
		$db = PearDatabase::getInstance();
		$languageResult = $db->pquery('SELECT label FROM vtiger_language WHERE prefix = ?', array($name));
		if ($db->num_rows($languageResult)) {
			return $db->query_result($languageResult, 0, 'label');
		}
		return false;
	}
	
	/**
	 *  This function returns the modified keycode to match the plural form(s) of a given language and a given count with the same pattern used by i18next JS library
	 *  Global patterns for keycode are as below :
	 *  - No plural form : only one non modified key is needed :)
	 *  - 2 forms : unmodified key for singular values and 'key_PLURAL' for plural values
	 *  - 3 or more forms : key_X with X indented for each plural form
	 *  @see https://www.i18next.com/plurals.html for some examples
	 *  @see http://docs.translatehouse.org/projects/localization-guide/en/latest/l10n/pluralforms.html?id=l10n/pluralforms for whole plural rules used by getText
	 *
	 *	@param	<String>	$key		Key to be pluralized
	 *  @param	<String>	$locale		Locale/language value
	 *  @param	<Float>		$count		Quantityu for plural determination
	 *	@return	<String>	Pluralized key to look for
	 */
	protected static function getPluralizedKey($key, $locale, $count) {
		//Extract language code from locale with special cases
		if(strcasecmp($locale,'pt_BR') === 0) $lang='pt_BR';		
		else {
			preg_match("/^[a-z]+/i", $locale, $match);
			$lang = strtolower((empty($match[0]))?'en':$match[0]);
		}
		
	//No plural form
		if (in_array($lang, array(
			'ay','bo','cgg','dz','id','ja','jbo','ka','km','ko','lo','ms','my','sah','su','th','tt','ug','vi','wo','zh'
		))) return $key;
		
	//Two plural forms
		if(in_array($lang, array(
			'ach','ak','am','arn','br','fa','fil','fr','gun','ln','mfe','mg','mi','oc','pt_BR','tg','ti','tr','uz','wa'
		))) return ($count > 1)?$key.'_PLURAL':$key;
		
		if(in_array($lang, array(
			'af','an','anp','as','ast','az','bg','bn','brx','ca','da','de','doi','dz','el','en','eo','es','et','eu','ff','fi','fo','fur','fy',
			'gl','gu','ha','he','hi','hne','hu','hy','ia','it','kk','kl','kn','ku','ky','lb','mai','mk','ml','mn','mni','mr','nah','nap',
			'nb','ne','nl','nn','nso','or','pa','pap','pms','ps','pt','rm','rw','sat','sco','sd','se','si','so','son','sq','sv','sw',
			'ta','te','tk','ur','yo'
		))) return ($count != 1)?$key.'_PLURAL':$key;
		
		if($lang == 'is') {
			return ($count%10 != 1 || $count%100 == 11)?$key.'_PLURAL':$key;
		}
		
	//3 or more plural forms
		if(in_array($lang, array(
			'be','bs','hr','ru','sr','uk'
		))) {
			$i = $count%10;
			$j = $count%100;
			if($i == 1 && $j != 11) return $key.'_0';
			if($i >= 2 && $i <= 4 && ($j < 10 || $j >= 20)) return $key.'_1';
			return $key.'_2';
		}
		
		if(in_array($lang, array(
			'cs','sk'
		))) {
			if($count == 1) return $key.'_0';
			if($count >= 2 && $count <= 4) return $key.'_1';
			return $key.'_2';
		}
		
		if($lang == 'csb') {
			$i = $count%10;
			$j = $count%100;
			if($count == 1) return $key.'_0';
			if($i >= 2 && $i <= 4 && ($j < 10 || $j >= 20)) return $key.'_1';
			return $key.'_2';
		}
		
		if($lang == 'lt') {
			$i = $count%10;
			$j = $count%100;
			if($i == 1 && $j != 11) return $key.'_0';
			if($i >= 2 && ($j < 10 || $j >= 20)) return $key.'_1';
			return $key.'_2';
		}
		
		if($lang == 'lv') {
			$i = $count%10;
			$j = $count%100;
			if($i == 1 && $j != 11) return $key.'_0';
			if($count != 0) return $key.'_1';
			return $key.'_2';
		}
		
		if($lang == 'me') {
			$i = $count%10;
			$j = $count%100;
			if($i == 1 && $j != 11) return $key.'_0';
			if($i >= 2 && $i <= 4 && ($j < 10 || $j >= 20)) return $key.'_1';
			return $key.'_2';
		}
		
		if($lang == 'pl') {
			$i = $count%10;
			$j = $count%100;
			if($count == 1) return $key.'_0';
			if($i >= 2 && $i <=4 && ($j < 10 || $j >= 20)) return $key.'_1';
			return $key.'_2';
		}
		
		if($lang == 'ro') {
			$j = $count%100;
			if($count == 1) return $key.'_0';
			if($count == 0 || ($j > 0 && $j < 20)) return $key.'_1';
			return $key.'_2';
		}
		
		if($lang == 'cy') {
			if($count == 1) return $key.'_0';
			if($count == 2) return $key.'_1';
			if($count != 8 && $count != 11) return $key.'_2';
			return $key.'_3';
		}
		
		if($lang == 'gd') {
			if($count == 1 || $count == 11) return $key.'_0';
			if($count == 2 || $count == 12) return $key.'_1';
			if($count > 2 && $count < 20) return $key.'_2';
			return $key.'_3';
		}
		
		if($lang == 'kw') {
			if($count == 1) return $key.'_0';
			if($count == 2) return $key.'_1';
			if($count == 3) return $key.'_2';
			return $key.'_3';
		}
		
		if($lang == 'mt') {
			$j = $count%100;
			if($count == 1) return $key.'_0';
			if($count == 0 || ($j > 1 && $j < 11)) return $key.'_1';
			if($j > 10 && $j <20) return $key.'_2';
			return $key.'_3';
		}
		
		if($lang == 'sl') {
			$j = $count%100;
			if($j == 1) return $key.'_0';
			if($j == 2) return $key.'_1';
			if($j == 3 || $j == 4) return $key.'_2';
			return $key.'_3';
		}
		
		if($lang == 'ga') {
			if($count == 1) return $key.'_0';
			if($count == 2) return $key.'_1';
			if($count > 2 && $count < 7) return $key.'_2';
			if($count > 6 && $count < 11) return $key.'_3';
			return $key.'_4';
		}
		
		if($lang == 'ar') {
			if($count == 0) return $key.'_0';
			if($count == 1) return $key.'_1';
			if($count == 2) return $key.'_2';
			if($count%100 >= 3 && $count%100 <= 10) return $key.'_3';
			if($count*100 >= 11) return $key.'_4';
			return $key.'_5';
		}
	
		//Fallback if no language found
		return $key;		
	}
}

/**
 *  Legacy function for translating i18n strings upon key based translation for PHP scripts
 *  @param	<String>	$key			Key
 *  @param	<String>	$moduleName		Module (domain) used for fetching key translaton
 *  @param	<String>	$args			Optional. Additional vars to be incorporated in string with sprintf syntax
 *  @return	<String>					Translation
*/
function vtranslate($key, $moduleName = '') {
	$args = func_get_args();
	$formattedString = call_user_func_array(array('Vtiger_Language_Handler', 'getTranslatedString'), $args);
	$args = array_slice($args, 2); //Optimization from 2 array_slice calls
	if (is_array($args) && !empty($args)) {
		$formattedString = call_user_func_array('vsprintf', array($formattedString, $args));
	}
	return $formattedString;
}

/**
 *  Legacy function for translating i18n strings upon key based translation for JS scripts
 *  @param	<String>	$key			Key
 *  @param	<String>	$moduleName		Module (domain) used for fetching key translaton
 *  @param	<String>	$args			Optional. Additional vars to be incorporated in string with sprintf syntax
 *  @return	<String>					Translation
*/
function vJSTranslate($key, $moduleName = '') {
	$args = func_get_args();
	return call_user_func_array(array('Vtiger_Language_Handler', 'getJSTranslatedString'), $args);
}

/**
 *  Returns the translated string for a given key in default domain 'vtiger' (looks in vtiger.php)
 *  
 *  @param	<String>	$key		Key for translation
 *  @return	<String>	Translation sentence
 */
function __($key) {
	$args = func_get_args();
	$formattedString = call_user_func_array(array('Vtiger_Language_Handler', 'getTranslatedString'), array($key,'vtiger',null,null,null));
	$args = array_slice($args, 1);
	if (is_array($args) && !empty($args)) {
		$formattedString = call_user_func_array('vsprintf', array($formattedString, $args));
	}
	return $formattedString;
}

/**
 *  Returns the translated string for a given key in a given module
 *  
 *  @param	<String>	$key		Key for translation
 *  @param	<String>	$module		Module Name
 *  @return	<String>	Translation sentence
 */
function __m($key, $module) {
	$args = func_get_args();
	$formattedString = call_user_func_array(array('Vtiger_Language_Handler', 'getTranslatedString'), array($key,$module,null,null,null));
	$args = array_slice($args, 2);
	if (is_array($args) && !empty($args)) {
		$formattedString = call_user_func_array('vsprintf', array($formattedString, $args));
	}
	return $formattedString;
}

/**
 *  Returns the translated string for a given key in default domain 'vtiger' with a given context (e.g MALE or FEMALE)
 *  
 *  @param	<String>	$key		Key for translation
 *  @param	<String>	$context	Context value (e.g MALE or FEMALE)
 *  @return	<String>	Translation sentence
 */
function __x($key, $context) {
	$args = func_get_args();
	$formattedString = call_user_func_array(array('Vtiger_Language_Handler', 'getTranslatedString'), array($key,'vtiger',null,$context,null));
	$args = array_slice($args, 2);
	if (is_array($args) && !empty($args)) {
		$formattedString = call_user_func_array('vsprintf', array($formattedString, $args));
	}
	return $formattedString;
}

 /**
 *  Returns the translated string for a given key in default domain 'vtiger' in plural forms (language dependent)
 *  
 *  @param	<String>	$key		Key for translation
 *  @param	<String>	$count		Quantity for plural determination
 *  @return	<String>	Translation sentence
 */
function __n($key, $count) {
	$args = func_get_args();
	$formattedString = call_user_func_array(array('Vtiger_Language_Handler', 'getTranslatedString'), array($key,'vtiger',null,null,$count));
	$args = array_slice($args, 2);
	if (is_array($args) && !empty($args)) {
		$formattedString = call_user_func_array('vsprintf', array($formattedString, $args));
	}
	return $formattedString;
}

/**
 *  Returns the translated string for a given key for a given module in a given context
 *  
 *  @param	<String>	$key		Key for translation
 *  @param	<String>	$module		Module Name
 *  @param	<String>	$context	Context value (e.g MALE or FEMALE)
 *  @return	<String>	Translation sentence
 */
function __mx($key, $module, $context) {
	$args = func_get_args();
	$formattedString = call_user_func_array(array('Vtiger_Language_Handler', 'getTranslatedString'), array($key,$module,null,$context,null));
	$args = array_slice($args, 3);
	if (is_array($args) && !empty($args)) {
		$formattedString = call_user_func_array('vsprintf', array($formattedString, $args));
	}
	return $formattedString;
}

 /**
 *  Returns the translated string for a given key for a given module in plural forms (language dependent)
 *  
 *  @param	<String>	$key		Key for translation
 *  @param	<String>	$module		Module Name
 *  @param	<String>	$count		Quantity for plural determination
 *  @return	<String>	Translation sentence
 */
function __mn($key, $module, $count) {
	$args = func_get_args();
	$formattedString = call_user_func_array(array('Vtiger_Language_Handler', 'getTranslatedString'), array($key,$module,null,null,$count));
	$args = array_slice($args, 3);
	if (is_array($args) && !empty($args)) {
		$formattedString = call_user_func_array('vsprintf', array($formattedString, $args));
	}
	return $formattedString;
}

/**
 *  Returns the translated string for a given key in default domain 'vtiger' with a given context (e.g MALE or FEMALE) in plural form
 *  
 *  @param	<String>	$key		Key for translation
 *  @param	<String>	$context	Context value (e.g MALE or FEMALE)
 *  @param	<String>	$count		Quantity for plural determination
 *  @return	<String>	Translation sentence
 */
function __xn($key, $context, $count) {
	$args = func_get_args();
	$formattedString = call_user_func_array(array('Vtiger_Language_Handler', 'getTranslatedString'), array($key,'vtiger',null,$context,$count));
	$args = array_slice($args, 3);
	if (is_array($args) && !empty($args)) {
		$formattedString = call_user_func_array('vsprintf', array($formattedString, $args));
	}
	return $formattedString;
}

/**
 *  Returns the translated string for a given key for a given module with a given context (e.g MALE or FEMALE) in plural form
 *  
 *  @param	<String>	$key		Key for translation
 *  @param	<String>	$module		Module Name
 *  @param	<String>	$context	Context value (e.g MALE or FEMALE)
 *  @param	<String>	$count		Quantity for plural determination
 *  @return	<String>	Translation sentence
 */
function __mxn($key, $module, $context, $count) {
	$args = func_get_args();
	$formattedString = call_user_func_array(array('Vtiger_Language_Handler', 'getTranslatedString'), array($key,$module,null,$context,$count));
	$args = array_slice($args, 4);
	if (is_array($args) && !empty($args)) {
		$formattedString = call_user_func_array('vsprintf', array($formattedString, $args));
	}
	return $formattedString;
}