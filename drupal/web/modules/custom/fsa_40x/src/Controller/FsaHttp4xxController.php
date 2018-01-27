<?php

namespace Drupal\fsa_40x\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Controller to override Drupal default HTTP 4xx responses.
 *
 * @package Drupal\fsa_40x\Controller
 */
class FsaHttp4xxController extends ControllerBase {

  /**
   * The 401 page content override.
   *
   * @return array
   *   Render array to display themed 401 error page content.
   */
  public function on401() {
    return [
      '#theme' => 'fsa_40x_response',
      '#status_code' => '401',
      '#error' => $this->t('Unauthorized'),
      '#content' => $this->t('You are not allowed to this resource. Please check the URL or go to the <a href="@home">homepage</a>.', ['@home' => self::getHomeUrl()]),
    ];
  }

  /**
   * The 403 page content override.
   *
   * @return array
   *   Render array to display themed 403 error page content.
   */
  public function on403() {
    return [
      '#theme' => 'fsa_40x_response',
      '#status_code' => '403',
      '#error' => $this->t('Access denied'),
      '#content' => $this->t('Access has been denied. Please check the URL or go to the <a href="@home">homepage</a>.', ['@home' => self::getHomeUrl()]),
    ];
  }

  /**
   * The 404 page content override.
   *
   * @return array
   *   Render array to display themed 404 page not found content.
   */
  public function on404() {
    return [
      '#theme' => 'fsa_40x_response',
      '#status_code' => '404',
      '#error' => $this->t('Page not found'),
      '#content' => $this->t('The page you were looking for has been moved or does not exist. Please go to the <a href="@home">homepage</a> or use our search to find the information you need.', ['@home' => self::getHomeUrl()]),
    ];
  }

  /**
   * Helper to return correct homepage url for any language.
   *
   * @return \Drupal\Core\GeneratedUrl|string
   *   Url of homepage.
   */
  private function getHomeUrl() {
    return Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();
  }

}
