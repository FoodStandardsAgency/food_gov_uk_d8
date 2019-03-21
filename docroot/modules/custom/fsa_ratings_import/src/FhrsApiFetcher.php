<?php

namespace Drupal\fsa_ratings_import;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class FhrsApiFetcher
 */
class FhrsApiFetcher {

  /** Defines the number of the first page. */
  const FIRST_PAGE = 1;

  /** Defines finished state. */
  const STATUS_FINISHED = 1;

  /**
   * @var object
   */
  protected $apiController;

  /**
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected $keyValueStorage;

  /**
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * @var string
   */
  protected $apiControllerClass = '\Drupal\fsa_ratings_import\Controller\FhrsApiController';

  /**
   * @var string
   */
  protected $path = 'public://api';

  /**
   * @var string
   */
  protected $fileFormat = 'fhrs_results_@page.json';

  /**
   * @var null
   */
  protected $pagesTotal = NULL;

  /**
   * @var null
   */
  protected $savedFilesMatches = NULL;

  /**
   * FhrsApiFetcher constructor.
   *
   * @param \Drupal\Core\DependencyInjection\ClassResolverInterface $class_resolver
   *   Class resolver.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   File system interface.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   Date formatter.
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $key_value_factory
   *   Key value store.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   */
  public function __construct(ClassResolverInterface $class_resolver, FileSystemInterface $file_system, DateFormatterInterface $date_formatter, KeyValueFactoryInterface $key_value_factory, LoggerInterface $logger) {
    $this->apiController = $class_resolver->getInstanceFromDefinition($this->apiControllerClass);
    $this->fileSystem = $file_system;
    $this->dateFormatter = $date_formatter;
    $this->keyValueStorage = $key_value_factory->get('fsa_ratings_import');
    $this->logger = $logger;
  }

  /**
   * Returns date value suitable for use in "date" column in the map table.
   *
   * @param string $pattern
   *   Date pattern.
   * @param int $timestamp
   *   UNIX timestamp.
   *
   * @return string
   *   Formatted date string.
   */
  public function getDateValue($pattern = 'Ymd', int $timestamp = NULL) {
    $timestamp = $timestamp ? $timestamp : time();

    return $this->dateFormatter->format($timestamp, 'custom', $pattern);
  }

  /**
   * Returns total number of pages.
   *
   * @return int|null
   *   Total pages.
   */
  public function getPagesTotal() {
    if (is_null($this->pagesTotal)) {
      $this->pagesTotal = $this->apiController->pagesTotal();
    }

    return $this->pagesTotal;
  }

  /**
   * Returns path to saved files.
   *
   * @param mixed $date
   *   Date object.
   *
   * @return string
   *   Path to files.
   */
  public function getPath($date = NULL) {
    $date = $date ? $date : $this->getDateValue('Y-m-d');

    return $this->path . '/' . $date;
  }

  /**
   * Returns formatted filename from the file pattern.
   *
   * @param mixed $page
   *   Page indicator.
   *
   * @return \Drupal\Component\Render\FormattableMarkup
   *   Markup for filename.
   */
  public function getFilename($page) {
    return new FormattableMarkup($this->fileFormat, [
      '@page' => $page,
    ]);
  }

  /**
   * Marks the finish of fetching for the day.
   *
   * @return mixed
   *   Placeholder comment - unclear exactly what this returns on all paths.
   */
  public function finish() {
    return $this->keyValueStorage->set('api_fetch_finish_last_date', $this->getDateValue('Y-m-d'));
  }

  /**
   * Removes old folders from import folder.
   */
  public function purge() {
    // Get real today path.
    $real_today_path = $this->fileSystem->realpath($this->getPath());
    // $this->path is used here for purpose as path root.
    $real_path = $this->fileSystem->realpath($this->path);

    $match_callback = function ($return_matches, $real_dir_path) use ($real_today_path) {
      if ($real_dir_path != $real_today_path) {
        $return_matches[] = $real_dir_path;
      }

      return $return_matches;
    };

    // Get all removable folders.
    $removable_folders = array_reduce(glob($real_path . '/*', GLOB_ONLYDIR), $match_callback, []);
    $removed_folders = [];

    // Recursively remove folders.
    array_map(function ($dir) use (&$removed_folders) {
      if (file_unmanaged_delete_recursive($dir)) {
        $removed_folders[] = $dir;
      }

    }, $removable_folders);

    $this->logger->info('Old API import folders were removed: {folders}', [
      'folders' => implode(', ', $removed_folders),
    ]);
  }

  /**
   * Returns TRUE if fetching is finished for the day.
   *
   * @return bool
   *   Returns TRUE if fetching is finished for the day.
   */
  public function isFinished() {
    $finished_last_day = $this->keyValueStorage->get('api_fetch_finish_last_date');

    return $finished_last_day == $this->getDateValue('Y-m-d');
  }

  /**
   * Returns an array of saved file matches.
   *
   * If there are files "[filename_pattern]_025.json" and
   * "[filename_pattern]_028.json", then the following result is returned:
   *
   * [
   *   [
   *     'filename' => '[filename_pattern]_025.json',
   *     'page' => 025,
   *   ],
   *   [
   *     'filename' => '[filename_pattern]_028.json',
   *     'page' => 028,
   *   ]
   * ]
   *
   * @param null|string $date
   *   Date string.
   *
   * @return array
   *   File matches.
   */
  public function getSavedFilesMatches($date = NULL) {
    if (is_null($this->savedFilesMatches)) {
      // Get the absolute path to the Json file directory.
      $real_path = $this->fileSystem->realpath($this->getPath($date));
      // Format the file pattern to look for.
      $file_pattern = $this->getFilename('([\d]*)');
      // Escape the dots.
      $file_pattern = str_replace('.', '\.', (string) $file_pattern);

      // The match callback matches each file and returns the saved page number.
      $match_callback = function ($return_matches, $filename) use ($file_pattern) {
        if (preg_match(sprintf('/^%s$/', $file_pattern), $filename, $matches)) {
          $return_matches[] = [
            'filename' => $matches[0],
            'page' => $matches[1],
          ];
        }

        return $return_matches;
      };

      $this->savedFilesMatches = array_reduce(scandir($real_path), $match_callback, []);
    }

    return $this->savedFilesMatches;
  }

  /**
   * Returns an array of filename that are saved on the system.
   *
   * @param null|string $date
   *   Date string.
   *
   * @return array
   *   Array of filenames.
   */
  public function getSavedFiles($date = NULL) {
    $saved_files_matches = $this->getSavedFilesMatches($date);

    return array_map(function ($item) {
      return $item['filename'];
    }, $saved_files_matches);
  }

  /**
   * Returns an array of page numbers that are saved on the system.
   *
   * If there are files "[filename_pattern]_025.json" and
   * "[filename_pattern]_028.json", then [25, 28] is returned.
   *
   * @param null|string $date
   *   Date string.
   *
   * @return array
   *   Array of page numbers that are saved on the system.
   */
  public function getSavedPages($date = NULL) {
    $saved_files_matches = $this->getSavedFilesMatches($date);

    return array_map(function ($item) {
      return (int) $item['page'];
    }, $saved_files_matches);
  }

  /**
   * Returns the next page that needs to be fetched.
   *
   * @return int|bool
   *   Next page number or FALSE.
   */
  public function getNextPageNumber() {
    $saved_pages = $this->getSavedPages();
    // Match the key with values for discoverability.
    $saved_pages = array_combine($saved_pages, $saved_pages);

    // Get total page count.
    $pages_total = $this->getPagesTotal();

    if ($pages_total >= 1) {
      // Loop through all the pages until non-saved page is found.
      for ($i = self::FIRST_PAGE; $i <= $pages_total; $i++) {
        if (in_array($i, $saved_pages, TRUE)) {
          $next_page = $i + 1;

          if (!isset($saved_pages[$next_page])) {
            // Return the next page until the last page.
            // Return FALSE when next page goes over the last page.
            return ($next_page <= $pages_total) ? $next_page : FALSE;
          }
        }
      }

      // Return the first page.
      return self::FIRST_PAGE;
    }

    return FALSE;
  }

  /**
   * Fetches the results from API and stores on the disk.
   *
   * @return bool|string
   *   Path to the filename or FALSE is returned.
   */
  public function fetchAndSave() {
    $result = FALSE;

    // Check if fetching for today is required.
    if (!$this->isFinished()) {
      // Check if path is writable.
      $path = $this->getPath();
      $path_writable = file_prepare_directory($path, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);

      if (!$path_writable) {
        $this->logger->error('{path} is not writable.', [
          'path' => $path,
        ]);

        return FALSE;
      }

      // Check if next page number can be retrieved.
      if (($next_page = $this->getNextPageNumber()) !== FALSE) {
        // Remove old files if starting anew.
        if ($next_page == self::FIRST_PAGE) {
          $this->purge();
        }

        // Sort is added to have a consistent order on the results. While sort is
        // only performed on result-set only, it still gives some structure.
        $options = ['sortOptionKey' => 'alpha'];

        if ($response = $this->apiController->fetch($next_page, $this->apiController->getMaxPageSize(), $options)) {
          if ($response->getStatusCode() == 200 && $content = $response->getBody()) {
            // Format the file pattern. Add padding to page number.
            $file_pattern = $this->getFilename(str_pad($next_page, 3, '0', STR_PAD_LEFT));
            $filename = $this->getPath() . '/' . $file_pattern;

            if ($result = file_unmanaged_save_data($content, $filename, FILE_EXISTS_REPLACE)) {
              $this->logger->info('Results saved to {filename}', [
                'filename' => $filename,
              ]);

              return $result;
            }
            else {
              $this->logger->error('Could not save file {file}.', [
                'file' => $filename,
              ]);
            }
          }
          else {
            $this->logger->error('Request to {url} produced {status_code} code.', [
              'url' => $this->apiController->getFetchUrl($next_page, $this->apiController->getMaxPageSize(), $options),
              'status_code' => $response->getStatusCode(),
            ]);
          }
        }
      }
      // If there are no pages to fetch, call it a day.
      else {
        $this->finish();

        $this->logger->info('Fetching is finished for {date}', [
          'date' => $this->getDateValue('Y-m-d'),
        ]);

        $result = self::STATUS_FINISHED;
      }
    }
    else {
      $result = self::STATUS_FINISHED;
    }

    return $result;
  }

}
