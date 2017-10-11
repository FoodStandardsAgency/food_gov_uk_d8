<?php

namespace Drupal\fsa_team_finder\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Component\Serialization\Json;
use GuzzleHttp\Exception\RequestException;

/**
 * Class TeamFinder.
 * @see https://mapit.mysociety.org/docs/
 */
class TeamFinder extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fsa_team_finder';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {


    $form['#prefix'] = '<div id="fsa-team-finder-wrapper">';
    $form['#suffix'] = '</div>';
    $form['progress'] = array(
      '#type' => 'item',
      '#markup' => t('Step 1 of 2'),
    );
    $form['query'] = array(
      '#type' => 'textfield',
      '#title' => t('Find a food safety team'),
      '#description' => t('<div>Please enter a postcode to find the food safety team in the area.</div><div>Enter a full postcode</div>'),
      '#description_display' => 'before',
      '#size' => 9,
      '#maxlength' => 9,
      '#required' => TRUE,
    );
    $form['actions'] = array(
      '#type' => 'actions',
      'submit' => array(
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
        '#ajax' => array(
          'callback' => '::rebuildForm',
          'event' => 'click',
          'wrapper' => 'fsa-team-finder-wrapper',
        ),
      ),
    );
    return $form;
  }

  /**
   * Rebuilds form with query result.
   *
   * @param array $form
   * @param $form_state
   *
   * @return array $form
   */
  public function rebuildForm(array $form, FormStateInterface $form_state) {
    $form['progress']['#markup'] = t('Step 2 of 2');
    unset($form['query']);
    unset($form['actions']);
    $form['confirmation'] = array(
      '#type' => 'item',
      '#markup' => $this->t('<h2>Food safety team details</h2><p>Details of the food safety team covering <strong>@query</strong> are shown below. Please contact them to report the food problem.</p>', array(
        '@query' => $form_state->getValue('query'),
      )),
    );
    $la = $this->getLocalAuthority($form_state->getValue('query'));
    $email = '';
    $website = '';
    $form['details'] = array(
      '#type' => 'item',
      '#markup' => t('<p><strong>@name</strong><br />Email address: ' . $email . '<br />Website: ' . $website . '</p>', array(
        '@name' => $la['name'],
      )),
    );
    $form['back'] = array(
      '#title' => $this->t('Back to form'),
      '#type' => 'link',
      '#url' => Url::fromRoute('fsa_team_finder.render')
    );
    return $form;
  }

  /**
   * Provides local authority ONS code and name.
   *
   * @param string
   *
   * @return array
   */
  public function getLocalAuthority($query) {

    // build mapit request
    $base = 'https://mapit.mysociety.org';
    $postcode = str_replace(' ', '', $query);
    $key = 'KEY';
    $url = $base . '/postcode/' . $postcode . '?api_key=' . $key;

    // call mapit
    /*$client = \Drupal::httpClient();
    $client->request('GET', $url);
    try {
      $response = $client->get($url);
      $data = Json::decode($response->getBody()->getContents());
    }
    catch (RequestException $e) {
      watchdog_exception('fsa_team_finder', $e->getMessage());
    }
    $council = $data['shortcuts']['council'];
    return array(
      'ons' => $data['areas'][$council]['codes']['ons'],
      'name' => $data['areas'][$council]['name'],
    );*/

    // save on mapit api calls
    return array(
      'ons' => '00BG',
      'name' => 'Tower Hamlets Borough Council',
    );
  }

  /**
   * Maps local authority ONS code to identifier.
   *
   * @param string
   *
   * @return integer
   */
  public function MapOnsToId($ons) {
    // create a mappings table in an install file
    // query the id
    return $id;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

}
