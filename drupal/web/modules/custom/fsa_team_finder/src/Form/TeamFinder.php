<?php

namespace Drupal\fsa_team_finder\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Component\Serialization\Json;
use GuzzleHttp\Exception\RequestException;

/**
 * Class TeamFinder.
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
      ),
    );
    if ($form_state->getValue('query')) {

      // get local authority details
      $la = $this->getLocalAuthority($form_state->getValue('query'));
      if (!empty($la)) {

        // reconstruct form
        $form['progress']['#markup'] = t('Step 2 of 2');
        unset($form['query']);
        unset($form['actions']);
        $form['confirmation'] = array(
          '#type' => 'item',
          '#markup' => $this->t('<h2>Food safety team details</h2><p>Details of the food safety team covering <strong>@query</strong> are shown below. Please contact them to report the food problem.</p>', array(
            '@query' => $form_state->getValue('query'),
          )),
        );
        $form['details'] = array(
          '#type' => 'item',
          '#markup' => t('<p><strong>@name</strong><br />Email address: @mail<br />Website: @site</p>', array(
            '@name' => $la['name'],
            '@mail' => '@todo', // $this->getLocalAuthorityLink(1760, 'field_email'),
            '@site' => '@todo', // $this->getLocalAuthorityLink(1760, 'field_url'),
          )),
        );
        $form['back'] = array(
          '#title' => $this->t('Back to form'),
          '#type' => 'link',
          '#url' => Url::fromRoute('fsa_team_finder.render')
        );
      } else {

        // no mapit response warning
        drupal_set_message(t('No food safety team found.'), 'error');
      }
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    // check for valid uk postcode
    if (!$this->testValidUkPostcode($form_state->getValue('query'))) {
      $form_state->setErrorByName('query', $this->t('Invalid postcode.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild();
  }

  /**
   * Test for valid UK postcode.
   * @see https://stackoverflow.com/questions/164979/uk-postcode-regex-comprehensive/14257846#14257846
   *
   * @param string
   *
   * @return boolean
   */
   public function testValidUkPostcode($query) {
     $regex = '/^([g][i][r][0][a][a])$|^((([a-pr-uwyz]{1}([0]|[1-9]\d?))|([a-pr-uwyz]{1}[a-hk-y]{1}([0]|[1-9]\d?))|([a-pr-uwyz]{1}[1-9][a-hjkps-uw]{1})|([a-pr-uwyz]{1}[a-hk-y]{1}[1-9][a-z]{1}))(\d[abd-hjlnp-uw-z]{2})?)$/i';
     $postcode = str_replace(' ', '', $query);
     return preg_match($regex, $postcode);
   }

  /**
   * Provides local authority ONS code and name.
   * @see https://mapit.mysociety.org/docs/
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
    $client = \Drupal::httpClient();
    $client->request('GET', $url, array('http_errors' => FALSE));
    try {
      $response = $client->get($url);
      $data = Json::decode($response->getBody()->getContents());
    }
    catch (RequestException $e) {
      watchdog_exception('fsa_team_finder', $e);
      return array();
    }
    if (isset($data['shortcuts']['council'])) {

      // negotiate two-tier local government
      if (!is_array($data['shortcuts']['council'])) {
        $council = $data['shortcuts']['council'];
      } elseif (isset($data['shortcuts']['council']['county'])) {
        $council = $data['shortcuts']['council']['county'];
      } else {
        return array();
      }
    } else {
      return array();
    }
    return array(
      'gss' => $data['areas'][$council]['codes']['gss'],
      'name' => $data['areas'][$council]['name'],
    );
  }

  /**
   * Provides local authority mail or site link.
   *
   * @param integer
   *
   * @return string
   */
  public function getLocalAuthorityLink($id, $field) {
    $value = \Drupal::entityTypeManager()
      ->getStorage('fsa_authority')
      ->load($id)
      ->get($field)
      ->getString();
    $scheme = $field == 'field_email' ? 'mailto:' : NULL;
    return Link::fromTextAndUrl($value, Url::fromUri($scheme . $value, array()))
      ->toString();
  }
}
