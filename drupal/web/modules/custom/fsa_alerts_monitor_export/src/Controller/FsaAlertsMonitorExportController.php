<?php

namespace Drupal\fsa_alerts_monitor_export\Controller;

use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\Controller\ControllerBase;
use League\Csv\EncloseField;
use League\Csv\Writer;

class FsaAlertsMonitorExportController extends ControllerBase {

  /**
   * @var $response_format
   */
  private $response_format;

  /**
   * @var $date_from
   */
  private $date_from;

  /**
   * @var $date_to
   */
  private $date_to;

  public function handleExportRequest(string $response_format, string $date_from, string $date_to) {
    // TODO: move to constructor.
    $this->response_format = $response_format;
    $this->date_from = empty($date_from) ? strtotime('-1 week') : strtotime($date_from);
    $this->date_to = empty($date_to) ? \Drupal::time()->getCurrentTime() : strtotime($date_to);

    // Can switch response formats; could use to wire up to JSON encoding too.
    switch ($response_format) {
      case 'csv':
        return $this->generateCsvResponse();
        break;

      default:
        return new CacheableResponse('Unspecified or unrecognised response format', 400);
    }
  }

  /**
   * Controller callback to build and return the CSV file response.
   *
   * @return \Drupal\Core\Cache\CacheableResponse
   *   A cacheable response object.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \League\Csv\CannotInsertRecord
   */
  public function generateCsvResponse() {
    $headers = [
      'User ID',
      'Activity',
      'Timestamp',
      'Food alerts',
      'Food alert medium',
      'Allergy alerts',
      'Allergy alert medium',
      'News alerts',
      'News alert medium',
      'Consultation alerts',
      'Consultation alert medium',
    ];

    // Query the database for data between our required or default date ranges.
    // Where default = last 7 days.

    $data = \Drupal::database()->query('SELECT * FROM {fsa_alerts_monitor} WHERE created BETWEEN :datefrom AND :dateto', [
      ':datefrom' => $this->date_from,
      ':dateto' => $this->date_to,
    ]);

    $rows = [];
    while ($row = $data->fetchAssoc()) {
      $rows[] = [
        $row['uid'],
        $row['activity'],
        \Drupal::service('date.formatter')->format($row['created'], 'custom', 'Y-m-d H:i:s'),
        $row['food_alerts'],
        $row['food_alert_medium'],
        $row['allergy_alerts'],
        $row['allergy_alert_medium'],
        $row['news_alerts'],
        $row['news_alert_medium'],
        $row['consultation_alerts'],
        $row['consultation_alert_medium'],
      ];
    }

    // Create a new file stream to allow us to build up the CSV output.
    $writer = Writer::createFromStream(tmpfile());
    EncloseField::addTo($writer, "\t\x1f");
    $writer->insertOne($headers);
    $writer->insertAll($rows);


    $filename = sprintf('%s_%s__alerts_monitor_export.csv',
      \Drupal::service('date.formatter')->format($this->date_from, 'custom', 'Ymd'),
      \Drupal::service('date.formatter')->format($this->date_to, 'custom', 'Ymd')
    );

    // Return the response.
    return new CacheableResponse($writer->getContent(), 200, [
      'Content-Encoding' => 'none',
      'Content-Type' => 'text/csv; charset=UTF-8',
      'Content-Disposition' => 'attachment; filename="' . $filename . '"',
      'Content-Description' => 'File Transfer',
    ]);
  }

}
