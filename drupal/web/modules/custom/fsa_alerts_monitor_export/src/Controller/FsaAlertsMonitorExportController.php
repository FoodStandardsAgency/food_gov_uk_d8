<?php

namespace Drupal\fsa_alerts_monitor_export\Controller;

use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\Controller\ControllerBase;
use League\Csv\Writer;

class FsaAlertsMonitorExportController extends ControllerBase {

  /**
   * Controller callback to build and return the CSV file response.
   *
   * @return \Drupal\Core\Cache\CacheableResponse
   *   A cacheable response object.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \League\Csv\CannotInsertRecord
   */
  public function generateCsv() {
    $date_from = strototime('-1 week');
    $date_to = \Drupal::time()->getCurrentTime();

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
      ':datefrom' => $date_from,
      ':dateto' => $date_to,
    ]);

    $rows = [];
    while ($row = $data->fetchAssoc()) {
      $rows[] = [
        $row['uid'],
        $row['activity'],
        \Drupal::dateFormatter()->format($row['created'], 'custom', 'Y-m-d H:i:s'),
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
    $writer->insertOne($headers);
    $writer->insertAll($rows);

    // Return the response.
    return new CacheableResponse($writer->getContent(), 200, [
      'Content-Encoding' => 'none',
      'Content-Type' => 'text/csv; charset=UTF-8',
      // TODO: make filename dynamic.
      'Content-Disposition' => 'attachment; filename="alerts_report.csv"',
      'Content-Description' => 'File Transfer',
    ]);
  }

}
