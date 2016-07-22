<?php

/**
 * Copyright 2016 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 * @author    Team Connector <team-connector@lengow.com>
 * @copyright 2016 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */
class Shopware_Plugins_Backend_Lengow_Components_LengowFeed
{
    /**
     * Protection.
     */
    const PROTECTION = '"';

    /**
     * CSV separator
     */
    const CSV_SEPARATOR = '|';

    /**
     * End of line.
     */
    const EOL = "\r\n";

    /**
     * @var string name of file containing part of export (in cas of timeout)
     */
    public $part_file_name;

    /**
     * @var LengowFile temporary export file
     */
    protected $file;

    /**
     * @var string feed content
     */
    protected $content = '';

    /**
     * @var string feed format
     */
    protected $format;

    /**
     * @var string export shop folder
     */
    protected $shop_folder = null;

    /**
     * @var string full export folder
     */
    protected $export_folder;

    /**
     * @var array formats available for export
     */
    public static $AVAILABLE_FORMATS = array(
        'csv',
        'yaml',
        'xml',
        'json',
    );

    /**
     * @var string lengow export folder
     */
    public static $LENGOW_EXPORT_FOLDER = 'export';

    public function __construct($stream, $format, $shop_name = null, $part_file_name = null)
    {
        $this->stream = $stream;
        $this->format = $format;
        $this->part_file_name = $part_file_name;
        $this->shop_name = $shop_name;
        $this->shop_folder = self::formatFields($shop_name, 'shop');
        if (!$this->stream) {
            $this->initExportFile();
        }
    }

    /**
     * Create export file
     */
    public function initExportFile()
    {
        $sep = DIRECTORY_SEPARATOR;
        $this->export_folder = self::$LENGOW_EXPORT_FOLDER . $sep . $this->shop_folder;
        $folder_path = Shopware_Plugins_Backend_Lengow_Components_LengowMain::getLengowFolder()
            .$sep.$this->export_folder;
        if (!file_exists($folder_path)) {
            if (!mkdir($folder_path)) {
                throw new Shopware_Plugins_Backend_Lengow_Components_LengowException(
                    Shopware_Plugins_Backend_Lengow_Components_LengowMain::setLogMessage(
                        'log.export.error_unable_to_create_folder',
                        array('folder_path' => $folder_path)
                    )
                );
            }
        }
        if ($this->part_file_name) {
            $file_name = $this->part_file_name;
        } else {
            // TODO : put iso_code in file name
            // ex. Prestashop : -'.Context::getContext()->language->iso_code.'
            $file_name = 'flux-'.time().'.'.$this->format;
        }
        $this->file = new Shopware_Plugins_Backend_Lengow_Components_LengowFile($this->export_folder, $file_name);
    }

    /**
     * Write feed
     *
     * @param array $data export data
     */
    public function write($type, $data = array(), $is_first = null)
    {
        switch ($type) {
            case 'header':
                if ($this->stream) {
                    header(self::getHtmlHeader($this->format));
                    if ($this->format == 'csv') {
                        header('Content-Disposition: attachment; filename=feed.csv');
                    }
                }
                $header = self::getHeader($data, $this->format);
                $this->flush($header);
                break;
            case 'body':
                $body = self::getBody($data, $is_first, $this->format);
                $this->flush($body);
                break;
            case 'footer':
                $footer = self::getFooter($this->format);
                $this->flush($footer);
                break;
        }
    }

    /**
     * Return feed header
     *
     * @param string $format feed format
     *
     * @return string
     */
    public static function getHeader($data, $format = 'csv')
    {
        switch ($format) {
            case 'csv':
                $header = '';
                foreach ($data as $field) {
                    $header.= self::PROTECTION.self::formatFields($field).self::PROTECTION.self::CSV_SEPARATOR;
                }
                return rtrim($header, self::CSV_SEPARATOR).self::EOL;
            case 'xml':
                return '<?xml version="1.0" encoding="UTF-8"?>'.self::EOL
                .'<catalog>'.self::EOL;
            case 'json':
                return '{"catalog":[';
            case 'yaml':
                return '"catalog":'.self::EOL;
        }
    }

    /**
     * Get feed body
     *
     * @param string  $format   feed format
     * @param array   $data     feed data
     * @param boolean $is_first is first product
     *
     * @return string
     */
    public static function getBody($data, $is_first, $format = 'csv')
    {
        switch ($format) {
            case 'csv':
                $content = '';
                foreach ($data as $value) {
                    $content.= self::PROTECTION.$value.self::PROTECTION.self::CSV_SEPARATOR;
                }
                return rtrim($content, self::CSV_SEPARATOR).self::EOL;
            case 'xml':
                $content = '<product>';
                foreach ($data as $field => $value) {
                    $field = self::formatFields($field, $format);
                    $content.= '<'.$field.'><![CDATA['.$value.']]></'.$field.'>'.self::EOL;
                }
                $content.= '</product>'.self::EOL;
                return $content;
            case 'json':
                $content = $is_first ? '' : ',';
                $json_array = array();
                foreach ($data as $field => $value) {
                    $field = self::formatFields($field, $format);
                    $json_array[$field] = $value;
                }
                $content .= json_encode($json_array);
                return $content;
            case 'yaml':
                $content = '  '.self::PROTECTION.'product'.self::PROTECTION.':'.self::EOL;
                $fieldMaxSize = self::getFieldMaxSize($data);
                foreach ($data as $field => $value) {
                    $field = self::formatFields($field, $format);
                    $content.= '    '.self::PROTECTION.$field.self::PROTECTION.':';
                    $content.= self::indentYaml($field, $fieldMaxSize).(string)$value.self::EOL;
                }
                return $content;
        }
    }

    /**
     * Return feed footer
     *
     * @param string $format feed format
     *
     * @return string
     */
    public static function getFooter($format = 'csv')
    {
        switch ($format) {
            case 'xml':
                return '</catalog>';
            case 'json':
                return ']}';
            default:
                return '';
        }
    }

    /**
     * Flush feed content
     *
     * @param string $content feed content to be flushed
     *
     */
    public function flush($content)
    {
        if ($this->stream) {
            echo $content;
            flush();
        } else {
            $this->file->write($content);
        }
    }

    /**
     * Finalize export generation
     *
     * @return bool
     */
    public function end()
    {
        $this->write('footer');
        if (!$this->stream) {
            //TODO : put iso_code in file name
            // ex. Prestashop : -'.Context::getContext()->language->iso_code.'
            $old_file_name = 'flux.'.$this->format;
            $old_file = new Shopware_Plugins_Backend_Lengow_Components_LengowFile($this->export_folder, $old_file_name);
            if ($old_file->exists()) {
                $old_file_path = $old_file->getPath();
                $old_file->delete();
            }
            $rename = false;
            if (isset($old_file_path)) {
                $rename = $this->file->rename($old_file_path);
                $this->file->file_name = $old_file_name;
            } else {
                $sep = DIRECTORY_SEPARATOR;
                $rename = $this->file->rename($this->file->getFolderPath().$sep.$old_file_name);
                $this->file->file_name = $old_file_name;
            }
            return $rename;
        }
        return true;
    }

    /**
     * Get feed URL
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->file->getLink();
    }

    /**
     * Get file name
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->file->getPath();
    }

    /**
     * Return HTML header according to the given format
     *
     * @param string $format feed format
     *
     * @return string
     */
    public static function getHtmlHeader($format)
    {
        switch ($format) {
            case 'csv':
                return 'Content-Type: text/csv; charset=UTF-8';
            case 'xml':
                return 'Content-Type: application/xml; charset=UTF-8';
            case 'json':
                return 'Content-Type: application/json; charset=UTF-8';
            case 'yaml':
                return 'Content-Type: text/x-yaml; charset=UTF-8';
        }
    }

    /**
     * v3
     * Format field names according to the given format
     *
     * @param string $str    field name
     * @param string $format feed format
     *
     * @return string
     */
    public static function formatFields($str, $format = 'csv')
    {
        switch ($format) {
            case 'csv':
                return substr(
                    strtoupper(
                        preg_replace(
                            '/[^a-zA-Z0-9_]+/',
                            '',
                            str_replace(
                                array(' ', '\''),
                                '_',
                                Shopware_Plugins_Backend_Lengow_Components_LengowMain::replaceAccentedChars($str)
                            )
                        )
                    ),
                    0,
                    58
                );
            default:
                return strtolower(
                    preg_replace(
                        '/[^a-zA-Z0-9_]+/',
                        '',
                        str_replace(
                            array(' ','\''),
                            '_',
                            Shopware_Plugins_Backend_Lengow_Components_LengowMain::replaceAccentedChars($str)
                        )
                    )
                );
        }
    }

    /**
     * For YAML, add spaces to have good indentation.
     *
     * @param string $name    the field name
     * @param string $maxsize space limit
     *
     * @return string
     */
    protected static function indentYaml($name, $maxsize)
    {
        $strlen = strlen($name);
        $spaces = '';
        for ($i = $strlen; $i <= $maxsize; $i++) {
            $spaces.= ' ';
        }
        return $spaces;
    }

    /**
     * Get the maximum length of the fields
     * Used for indentYaml function
     *
     * @param array $fields List of fields to export
     *
     * @return integer Length of the longer field
     */
    protected static function getFieldMaxSize($fields)
    {
        $maxSize = 0;
        foreach ($fields as $key => $field) {
            $field = self::formatFields($key);
            if (strlen($field) > $maxSize) {
                $maxSize = strlen($field);
            }
        }
        return $maxSize;
    }

    /**
     * Get file links for a shop
     *
     * @param string $shopName Shop name
     *
     * @return array()
     */
    public static function getLinks($shopName = null)
    {
        $sep = DIRECTORY_SEPARATOR;
        $folder = self::$LENGOW_EXPORT_FOLDER.$sep.self::formatFields($shopName, 'shop');
        $files = Shopware_Plugins_Backend_Lengow_Components_LengowFile::getFilesFromFolder($folder);
        if (empty($files)) {
            return false;
        }
        $feeds = array();
        foreach ($files as $file) {
            $feeds[] = $file->getLink();
        }
        return $feeds;
    }
}
