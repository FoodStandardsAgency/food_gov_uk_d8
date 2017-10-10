<?php

namespace Drupal\fsa_team_finder\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
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
    $form['about'] = array(
      '#type' => 'item',
      '#markup' => t('Food safety teams advise consumers and businesses on food related issues, such as inspections of food premises, and concerns and complaints raised by the public.'),
    );
    $form['progress'] = array(
      '#type' => 'item',
      '#markup' => t('Step 1 of 2'),
    );
    $form['postcode'] = array(
      '#type' => 'textfield',
      '#title' => t('Find a food safety team'),
      '#description' => t('<div>Please enter a postcode to find the food safety team in the area.</div><div>Enter a full postcode</div>'),
      '#description_display' => 'before',
      '#size' => 9,
      '#maxlength' => 9,
      '#required' => TRUE,
    );
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (strlen($form_state->getValue('postcode')) < 6) {
      $form_state->setErrorByName('postcode', $this->t('Error!'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // build mapit request
    /*$base = 'https://mapit.mysociety.org';
    $postcode = str_replace(' ', '', 'E16 1BF');
    $key = 'KEY';
    $url = $base . '/postcode/' . $postcode . '?api_key=' . $key;

    // call mapit
    $client = \Drupal::httpClient();
    $client->request('GET', $url);
    try {
      $response = $client->get($url);
      $data = Json::decode($response->getBody()->getContents());
    }
    catch (RequestException $e) {
      watchdog_exception('fsa_team_finder', $e->getMessage());
    }*/

    // tests
    /*drupal_set_message(t('Postcode: @result', array('@result' => var_export($data['postcode'], TRUE))));
    drupal_set_message(t('Name: @result', array('@result' => var_export($data['areas']['2510']['name'], TRUE))));*/
  }

}
