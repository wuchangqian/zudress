<?php
/**
 * Magento Webshopapps Shipping Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Webshopapps
 * @package    Webshopapps_Wsacommon
 * @copyright  Copyright (c) 2012 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt
 * @author     Karen Baker <sales@webshopapps.com>
 */
class Webshopapps_Wsacommon_Model_Export_Csv extends Mage_Core_Model_Abstract
{
    const ENCLOSURE = '"';
    const DELIMITER = ',';
    private $fileDates = array();
    private $dateFileArray = array();
    private $theDataArray = array();

    /**
     * Get latest CSV file name and content from var/export
     *
     * @param $website Id of the current store config scope
     * @return Array The name of the CSV and data of the CSV file to be returned
     */
    public function createCSV($website, $extension)
    {
        if (is_dir(Mage::getBaseDir('var') . DS . 'export' . DS)) {
            $directory = Mage::getBaseDir('var') . DS . 'export' . DS;
            $extensionArray = explode("_", $extension);
            $extension = $extensionArray[1];
            $csvFiles = glob($directory . 'WSA_' . $extension . '*.csv');
            if (empty($csvFiles)) {
                $csvFiles = glob($directory . 'WSA*.csv');
            }
            if (empty($csvFiles)) { // If no WSA*.csv files found, search all .csv files.
                $csvFiles = glob($directory . '*.csv');
            }

            foreach ($csvFiles as $file) {
                $file = basename($file);
                $posOfId = strpos($file, 'Id=');
                $websiteId = substr($file, $posOfId+3, 1);
                // Get files for the current website config scope
                if ($website == $websiteId) {
                    $this->timeSortSetup($file);
                }

            }
            // if no file names with website id found, recheck all files
            if (empty($websiteId) || !is_numeric($websiteId)) {
                $this->noWebsiteId($csvFiles);
            }

            // If $this->fileDates is empty return a blank CSV
            if (!isset($this->fileDates) || empty($this->fileDates)) {
                $this->noCSVPresent(Mage::getBaseDir('var') . DS . 'export' . DS);
                return $this->theDataArray;
            }

            $this->findMostRecentCSV();

        } else {
            // If var/export is not a directory return a blank CSV
            $this->noCSVPresent(Mage::getBaseDir('var') . DS . 'export' . DS);
        }
        return $this->theDataArray;
    }

    /**
     * Loops through csv files and sets up $dateFileArray & $theDataArray
     *
     * @param $csvFiles Array of csv files in var/export
     */
    public function noWebsiteId($csvFiles)
    {
        foreach ($csvFiles as $file) {
            $file = basename($file);
            $this->timeSortSetup($file);
        }
    }

    /**
     * Get most recent csv, read data and assign to $this->$theData
     *
     * @param $csvFiles Array of csv files in var/export.
     */
    public function findMostRecentCSV()
    {
        // Get file with the most recent timestamp
        array_multisort($this->fileDates, SORT_DESC);
        $mostRecent = $this->fileDates[0];
        $mostRecentCSV = $this->dateFileArray[$mostRecent];
        $dir = Mage::getBaseDir('var') . DS . 'export' . DS . $mostRecentCSV;

        if (is_file($dir)) {
            $fp = fopen($dir, 'r');
            $theData = fread($fp, filesize($dir));
            fclose($fp);
        } else {
            $theData = $this->noCSVPresent($dir);
            return $theData;
        }

        $this->theDataArray = array($mostRecentCSV, $theData);
    }

    /**
     * Sets up $this->fileDates & $this->dateFileArray to be sorted
     *
     * @param $csvFiles Array of csv files in var/export.
     */
    public function timeSortSetup($file)
    {
        $currentModified = filectime(Mage::getBaseDir('var') . DS . 'export' . DS. $file);
        $this->fileDates[] = $currentModified;
        $this->dateFileArray[$currentModified] = $file;
    }

    /**
     * Assigns blank CSV file to $this->theDataArray and posts a log
     *
     * @param $dir Location of var/export.
     */
    public function noCSVPresent($dir)
    {
        Mage::helper('wsacommon/log')->postMajor('WSA Helper','No file found in var/export with the name:', $dir);
        $this->theDataArray = array('', 'blank');
    }

}
?>
