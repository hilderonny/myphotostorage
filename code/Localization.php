<?php

/*
 * The MIT License
 *
 * Copyright 2014 Ronny Hildebrandt <ronny.hildebrandt@avorium.de>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Provides localization functions for strings. Uses the global variable
 * "defaultlanguage" to determine the default language for the installation.
 * The used locale is determined by the settings sent by the user's browser.
 * Only the language (not the country) ist extracted. When a string in a 
 * specific language does not exist, it gets created in the corresponding
 * translation file and the installation default translation is returned.
 * When the default language has also not the string, it will be put into the
 * default language's translatio file and is simply returned.
 *
 * @author Ronny Hildebrandt <ronny.hildebrandt@avorium.de>
 */
class Localization {
	
	/**
	 * @var array Associative array containing the translations for the
	 * currently used language.
	 */
	static $translations = false;
	
	/**
	 * Remember the preferred visitor's language for the request.
	 * @var string Preferred visitor's language
	 */
	static $preferredvisitorlanguage = false;
        
        /**
         * Returns the language currently preferred by the visitor
         * 
         * @return string Two digit language code in lower case
         */
        static function getPreferredVisitorLanguage() {
            if (!self::$preferredvisitorlanguage) {
                    // Get preferred visitor language
                    $acceptedvisitorlanguages = filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE');
                    if (empty($acceptedvisitorlanguages)) {
                            self::$preferredvisitorlanguage = $GLOBALS['defaultlanguage'] ?: 'en';
                    } else {
                            $languagelist = explode(',', $acceptedvisitorlanguages);
                            self::$preferredvisitorlanguage = strtolower(explode('-', $languagelist[0])[0]);
                    }
            }
            return self::$preferredvisitorlanguage;
        }
	
	/**
	 * Translates the given string into the visitor's language or into the default
	 * language and returns the translation.
	 * 
	 * @param string $str String to translate
	 * @return string Translated string
	 */
	static function translate($str) {
            $prefferedlanguage = self::getPreferredVisitorLanguage();
            // Get translations or load them
            if (!self::$translations) {
                    $languagetousefilename = dirname(__DIR__).'/locale/'.$prefferedlanguage.'.json';
                    if (!file_exists($languagetousefilename)) {
                            $defaultlanguage = $GLOBALS['defaultlanguage'] ?: 'en';
                            $defaultlanguagefilename = dirname(__DIR__).'/locale/'.$defaultlanguage.'.json';
                            if ($prefferedlanguage === $defaultlanguage || !file_exists($defaultlanguagefilename)) {
                                    file_put_contents($languagetousefilename, json_encode([], JSON_PRETTY_PRINT), LOCK_EX);
                            } else {
                                    copy($defaultlanguagefilename, $languagetousefilename);
                            }
                    }
                    self::$translations = json_decode(file_get_contents($languagetousefilename), true);
            }
            // Check whether a translation exists
            if (!array_key_exists($str, self::$translations)) {
                    $languagetousefilename = dirname(__DIR__).'/locale/'.$prefferedlanguage.'.json';
                    self::$translations[$str] = $str;
                    ksort(self::$translations);
                    file_put_contents($languagetousefilename, json_encode(self::$translations, JSON_PRETTY_PRINT), LOCK_EX);
                    return $str;
            } else {
                    return self::$translations[$str];
            }
	}
}
