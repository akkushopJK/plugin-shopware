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
 * Lengow File Class
 */
class Shopware_Plugins_Backend_Lengow_Components_LengowFile
{
    /**
     * @var string file name
     */
    public $fileName;

    /**
     * @var string folder name that contains the file
     */
    public $folderName;

    /**
     * @var ressource file hande
     */
    public $instance;

    /**
     * Construct
     *
     * @param string $folderName Lengow folder name
     * @param string $fileName   Lengow file name
     * @param string $mode       type of access
     *
     * @throws Shopware_Plugins_Backend_Lengow_Components_LengowException unable to create file
     */
    public function __construct($folderName, $fileName = null, $mode = 'a+')
    {
        $this->fileName = $fileName;
        $this->folderName = $folderName;
        $this->instance = self::getRessource($this->getPath(), $mode);
        if (!is_resource($this->instance)) {
            throw new Shopware_Plugins_Backend_Lengow_Components_LengowException(
                Shopware_Plugins_Backend_Lengow_Components_LengowMain::setLogMessage(
                    'log/export/error_unable_to_create_file',
                    array(
                        'file_name'   => $fileName,
                        'folder_name' => $folderName
                    )
                )
            );
        }
    }

    /**
     * Destruct
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Write content in file
     *
     * @param string $txt text to be written
     */
    public function write($txt)
    {
        if (!$this->instance) {
            $this->instance = fopen($this->getPath(), 'a+');
        }
        fwrite($this->instance, $txt);
    }

    /**
     * Delete file
     */
    public function delete()
    {
        if ($this->exists()) {
            if ($this->instance) {
                $this->close();
            }
            unlink($this->getPath());
        }
    }

    /**
     * Get resource of a given stream
     *
     * @param string $path path to the file
     * @param string $mode type of access
     *
     * @return resource
     */
    public static function getRessource($path, $mode = 'a+')
    {
        return fopen($path, $mode);
    }

    /**
     * Get file link
     *
     * @return string
     */
    public function getLink()
    {
        if (empty($this->link)) {
            if (!$this->exists()) {
                $this->link = null;
            }
            $sep = DIRECTORY_SEPARATOR;
            $base = Shopware_Plugins_Backend_Lengow_Components_LengowMain::getBaseUrl();
            $lengowDir = Shopware_Plugins_Backend_Lengow_Components_LengowMain::getPathPlugin();
            $this->link = $base.$lengowDir.$this->folderName.$sep.$this->fileName;
        }
        return $this->link;

    }

    /**
     * Get file path
     *
     * @return string
     */
    public function getPath()
    {
        $sep = DIRECTORY_SEPARATOR;
        return Shopware()->Plugins()->Backend()->Lengow()->Path().$this->folderName.$sep.$this->fileName;
    }

    /**
     * Get folder path of current file
     *
     * @return string
     */
    public function getFolderPath()
    {
        return Shopware()->Plugins()->Backend()->Lengow()->Path().$this->folderName;
    }

    /**
     * Rename file
     *
     * @param string $newName new file name
     *
     * @return boolean
     */
    public function rename($newName)
    {
        return rename($this->getPath(), $newName);
    }

    /**
     * Close file handle
     */
    public function close()
    {
        if (is_resource($this->instance)) {
            fclose($this->instance);
        }
    }

    /**
     * Check if current file exists
     *
     * @return boolean
     */
    public function exists()
    {
        return file_exists($this->getPath());
    }

    /**
     * Get a file list for a given folder
     *
     * @param string $folder folder name
     *
     * @return array
     */
    public static function getFilesFromFolder($folder)
    {
        $folderPath = Shopware()->Plugins()->Backend()->Lengow()->Path().$folder;
        if (!file_exists($folderPath)) {
            return false;
        }
        $folderContent = scandir($folderPath);
        $files = array();
        foreach ($folderContent as $file) {
            if (!preg_match('/^\.[a-zA-Z\.]+$|^\.$|index\.php/', $file)) {
                $files[] = new Shopware_Plugins_Backend_Lengow_Components_LengowFile($folder, $file);
            }
        }
        return $files;
    }
}
