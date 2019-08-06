<?php
/**
 * Copyright 2017 Lengow SAS
 *
 * NOTICE OF LICENSE
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/agpl-3.0
 *
 * @category    Lengow
 * @package     Lengow
 * @subpackage  Components
 * @author      Team module <team-module@lengow.com>
 * @copyright   2017 Lengow SAS
 * @license     https://www.gnu.org/licenses/agpl-3.0 GNU Affero General Public License, version 3
 */

/**
 * Lengow Translation Class
 */
class Shopware_Plugins_Backend_Lengow_Components_LengowTranslation
{
    /**
     * @var array all translations
     */
    protected static $translation = null;

    /**
     * @var string fallback iso code
     */
    public $fallbackIsoCode = 'default';

    /**
     * Translate message
     *
     * @param string $message localization key
     * @param array $args arguments to replace word in string
     * @param string|null $isoCode translation iso code
     *
     * @return string
     */
    public function t($message, $args = array(), $isoCode = null)
    {
        if (!isset(self::$translation[$isoCode])) {
            self::loadFile();
        }
        if (isset(self::$translation[$isoCode][$message])) {
            return $this->translateFinal(self::$translation[$isoCode][$message], $args);
        } else {
            if (!isset(self::$translation[$this->fallbackIsoCode])) {
                self::loadFile($this->fallbackIsoCode);
            }
            if (isset(self::$translation[$this->fallbackIsoCode][$message])) {
                return $this->translateFinal(self::$translation[$this->fallbackIsoCode][$message], $args);
            } else {
                return 'Missing Translation [' . $message . ']';
            }
        }
    }

    /**
     * Translate string
     *
     * @param string $text localization key
     * @param array $args arguments to replace word in string
     *
     * @return string
     */
    protected function translateFinal($text, $args)
    {
        if ($args) {
            $params = array();
            $values = array();
            foreach ($args as $key => $value) {
                $params[] = '%{' . $key . '}';
                $values[] = $value;
            }
            return stripslashes(str_replace($params, $values, $text));
        } else {
            return stripslashes($text);
        }
    }

    /**
     * Load ini file
     *
     * @param string|null $isoCode translation iso code
     * @param string|null $fileName file location
     *
     * @return boolean
     */
    public static function loadFile($isoCode = null, $fileName = null)
    {
        if (!$fileName) {
            $pluginPath = Shopware_Plugins_Backend_Lengow_Components_LengowMain::getLengowFolder();
            $fileName = $pluginPath . 'Snippets/backend/Lengow/translation.ini';
        }
        $translation = array();
        if (file_exists($fileName)) {
            try {
                self::$translation = parse_ini_file($fileName, true);
            } catch (Exception $e) {
               return false;
            }
        }
        self::$translation[$isoCode] = $translation;
        return count($translation) > 0;
    }

    /**
     * File contains iso code
     *
     * @param string $isoCode translation iso code
     *
     * @return boolean
     */
    public static function containsIso($isoCode)
    {
        if (!isset(self::$translation[$isoCode])) {
            self::loadFile();
        }
        return array_key_exists($isoCode, self::$translation);
    }

    /**
     * Get translations from an array
     *
     * @param array $keys list of translation keys
     *
     * @return array
     */
    public static function getTranslationsFromArray($keys)
    {
        // get locale from session
        $locale = Shopware_Plugins_Backend_Lengow_Components_LengowMain::getLocale();
        $lengowTranslation = new Shopware_Plugins_Backend_Lengow_Components_LengowTranslation();
        $translations = array();
        foreach ($keys as $path => $key) {
            foreach ($key as $value) {
                $translationParam = array();
                if (preg_match('/^(([a-z\_]*\/){1,3}[a-z\_]*)(\[(.*)\]|)$/', $path . $value, $result)) {
                    if (isset($result[1])) {
                        $tKey = $result[1];
                        $translations[$value] = $lengowTranslation->t($tKey, $translationParam, $locale);
                    }
                }
            }
        }
        return $translations;
    }
}
