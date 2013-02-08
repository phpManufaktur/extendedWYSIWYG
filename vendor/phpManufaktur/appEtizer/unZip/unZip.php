<?php

/**
 * Appetizer
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\appEtizer\UnZip;

use phpManufaktur\CMS\Bridge\Control\boneClass;
use phpManufaktur\appEtizer\unZip\unZipException;
use ZipArchive;

class unZip {

  protected static $unzip_path = null;
  protected static $use_pclzip = false;
  protected $pclzip = null;
  protected static $file_list = array();

  /**
   * Constructor for the class UnZip
   */
  public function __construct() {
    global $tools;
    global $logger;

    if (!class_exists('ZipArchive')) {
      // ZipArchive was the preferred method
      $logger->addDebug(sprintf('[%s - %s] %s]', __METHOD__, __LINE__, 'Missing the ZipArchive extension!'));
      // check if ziblib is installed ...
      if (!function_exists('gzopen')) {
        // no more chance ...
        throw UnZipException::error('Missing the ZipArchive or the zlib extension - can\'t unzip any file!');
      }
      self::$use_pclzip = true;
    }

    self::$file_list = array();

    // set the unzip path
    self::$unzip_path = CMS_PATH.'/temp/unzip';
    // check directory and create it if necessary
    if (!$tools->checkDirectory(self::$unzip_path, true)) {
      throw UnZipException::error($tools->getError());
    }

  } // __construct()

  /**
   * Set the path for the unzip operation
   *
   * @param string $unzip_path
   */
  public function setUnZipPath($unzip_path) {
    self::$unzip_path = $unzip_path;
  } // setUnZipPath()

  /**
   * Return the UnZip Path
   *
   * @return string path
   */
  public function getUnZipPath() {
    return self::$unzip_path;
  } // getUnZipPath()

  /**
   * Return the list of the extracted files and directories
   *
   * @return array
   */
  public function getFileList() {
    return self::$file_list;
  } // getFileList()

  /**
   * Create a list of the unzipped files
   *
   * @param array $list
   */
  protected function createPclZipFileList($list) {
    $file_list = array();
    foreach ($list as $item) {
      if ($item['folder'] == 1) continue;
      $file_list[] = array(
          'file_path' => $item['filename'],
          'file_name' => $item['stored_filename'],
          'file_size' => $item['size'],
          );
    }
    self::$file_list = $file_list;
  } // createPclZipFileList()

  /**
   * Create a list of the unzipped files
   */
  protected function createZipArchiveFileList() {
    global $tools;
    $file_list = array();
    $list = $tools->directoryTree(self::$unzip_path);
    foreach ($list as $file) {
      $file_list[] = array(
          'file_path' => $file,
          'file_name' => substr($file, strlen(self::$unzip_path)+1),
          'file_size' => filesize($file),
          );
    }
    self::$file_list = $file_list;
  } // createZipArchiveFileList()

  /**
   * Unzip the desired $zip_file and return a list with the extracted files
   *
   * @param string $zip_file
   * @throws UnZipException
   * @return array|boolean
   */
  public function extract($zip_file) {
    global $tools;

    // delete the files and directories from the unzip path
    if (!$tools->deleteDirectory(self::$unzip_path)) {
      throw new UnZipException($tools->getError());
    }

    if (self::$use_pclzip) {
      // use PclZip for decompressing
      try {
        // require the PclZip library
        require_once __DIR__.'/pclzip/pclzip.lib.php';
        // set the temporary directory for pclzip
        if (!defined('PCLZIP_TEMPORARY_DIR'))
          define('PCLZIP_TEMPORARY_DIR', self::$unzip_path);
        // create PclZip instance
        $this->pclzip = new \PclZip($zip_file);
        $list = $this->pclzip->extract(self::$unzip_path);
        if (!is_array($list)) {
          throw UnZipException::error(sprintf('PclZip Error - Code: %d - Message: %s',
              $this->pclzip->error_code, $this->pclzip->error_string));
        }
        return true;
        $this->createPclZipFileList($list);
      } catch (\Exception $e) {
        throw UnZipException::error($e->getMessage());
      }
    }

    // use ZipArchive for decrompressing
    try {
      $zipArchive = new \ZipArchive();
      if (true !== ($status = $zipArchive->Open($zip_file))) {
        throw UnZipException::errorZipArchiveOpen($status, $zip_file);
      }
      if (!$zipArchive->extractTo(self::$unzip_path)) {
        throw UnZipException::error(sprintf('Can\'t extract the archive to %s', self::$unzip_path));
      }
      // create the file list
      $this->createZipArchiveFileList();
      return true;
    } catch (\Exception $e) {
      throw UnZipException::error($e->getMessage());
    }

    return false;
  } // extract()

} // class unZip