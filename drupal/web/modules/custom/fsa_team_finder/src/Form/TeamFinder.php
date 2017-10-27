<?php

namespace Drupal\fsa_team_finder\Form;

use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
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

    // Set header/intro for the form (updated with AJAX).
    $form['form-header'] = [
      '#markup' => '<h3 class="form-title">' . $this->t('Find a food safety team') . '</h3>',
      ];
    $form['form-intro'] = [
      '#markup' => '<p class="form-intro">' . $this->t('Please enter a postcode to find the food safety team in the area') . '</p>',
      ]
    ;
    $form['query'] = array(
      '#type' => 'textfield',
      '#title' => t('Enter a full postcode'),
      '#description_display' => 'before',
      '#size' => 9,
      '#maxlength' => 9,
      '#required' => TRUE,
    );
    // The AJAX result placeholder.
    $form['ajax-results'] = [
      '#markup' => '<div id="team-finder-results"></div>',
    ];
    $form['actions'] = array(
      '#type' => 'actions',
      'submit' => array(
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
        '#attributes' => ['class' => ['submit-finder']],
        '#ajax' => [
          'callback' => [$this, 'ajaxSubmitForm'],
          'event' => 'click',
          'effect' => 'fade',
          'speed' => 500,
          'progress' => [
            'type' => 'throbber',
          ],
        ],
      ),
      'reset' => array(
        '#type' => 'submit',
        '#value' => $this->t('Reset'),
        '#attributes' => ['class' => ['visually-hidden', 'submit-reset']],
        '#ajax' => [
          'callback' => [$this, 'ajaxResetForm'],
          'event' => 'click',
          'effect' => 'fade',
          'speed' => 500,
          'progress' => [
            'type' => 'throbber',
          ],
        ],
      ),
    );

    if ($form_state->getValue('query')) {
      // Leave as is for non-js fallback.

      // get mapit local authority details
      $la = $this->getLocalAuthority($form_state->getValue('query'));
      if (!empty($la)) {

        // entity query fsa local authority
        $fsa_authority = \Drupal::entityTypeManager()
          ->getStorage('fsa_authority')
          ->loadByProperties(array(
            'field_mapit_area' => $la['mapit_area'],
          ));
        if ($fsa_authority = reset($fsa_authority)) {

          // generate links
          $email = $fsa_authority->get('field_email')->getString();
          $email_alt = $fsa_authority->get('field_email_alt')->getString();
          $overridden = $fsa_authority->get('field_email_overridden')->getString();
          $email_value = $overridden ? $email_alt : $email;
          $email_link = Link::fromTextAndUrl($email_value, Url::fromUri('mailto:' . $email_value, array()))
            ->toString();
          $site_value = $fsa_authority->get('field_url')->getString();
          $site_link = Link::fromTextAndUrl($site_value, Url::fromUri($site_value, array()))
            ->toString();

          // reconstruct form
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
              '@mail' => $email_link,
              '@site' => $site_link,
            )),
          );
          $form['back'] = array(
            '#title' => $this->t('Back to form'),
            '#type' => 'link',
            '#url' => Url::fromRoute('<current>'),
          );
        } else {

          // null entity query
          drupal_set_message(t('No food safety team found.'), 'error');
        }
      } else {

        // no mapit response
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
//    $form_state->setRebuild();
  }

  /**
   * Ajax reset functionality.
   *
   * @param array $form
   *   Form fields.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state values.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response.
   */
  public function ajaxResetForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    // Toggle element visibilities.
    $toggleClasses = [
      '#fsa-team-finder .submit-reset',
      '#fsa-team-finder .submit-finder',
      '#fsa-team-finder .form-item-query',
      '#fsa-team-finder .form-intro',
    ];
    foreach ($toggleClasses AS $class) {
      $response->addCommand(new InvokeCommand(
        $class, 'toggleClass', ['visually-hidden']
      ));
    }

    // Clear query input.
    $response->addCommand(new InvokeCommand(
      '#fsa-team-finder .form-item-query input',
      'val',
      ['']
    ));

    // Revert form title.
    $response->addCommand(new HtmlCommand(
      '#fsa-team-finder .form-title',
      $this->t('Find a food safety team')
    ));

    // Clear results wrapper.
    $response->addCommand(new HtmlCommand(
      '#team-finder-results', ''
    ));

    return $response;
  }

  /**
   * Ajax submit functionality.
   *
   * @param array $form
   *   Form fields.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state values.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response.
   */
  public function ajaxSubmitForm(array &$form, FormStateInterface $form_state) {

    $response = new AjaxResponse();
    $result_name = NULL;
    $result_mail = NULL;
    $result_site = NULL;
    $query = $form_state->getValue('query');
    $la = $this->getLocalAuthority($query);

    // Use fsa-team-finder-results.html.twig for the result.
    $result['#theme'] = 'fsa_team_finder_results';

    if (isset($la['mapit_area'])) {
      $fsa_authority = \Drupal::entityTypeManager()
        ->getStorage('fsa_authority')
        ->loadByProperties(array(
          'field_mapit_area' => $la['mapit_area'],
        ));
      if (is_numeric(key($fsa_authority))) {
        $fsa_authority = reset($fsa_authority);

        $overridden = $fsa_authority->field_email_overridden->getString();
        $email_value = $overridden ? $fsa_authority->field_email_alt->getString() : $fsa_authority->field_email->getString();
        $email_link = Link::fromTextAndUrl($email_value, Url::fromUri('mailto:' . $email_value, []))->toString();

        $site_value = $fsa_authority->field_url->getString();
        $site_link = Link::fromTextAndUrl($site_value, Url::fromUri($site_value, []))->toString();

        // Set results.
        $result_message = $this->t('Details of the food safety team covering <strong>@query</strong> are shown below.', ['@query' => $query]);
        $result_name = $la['name'];
        $result_mail = $email_link;
        $result_site = $site_link;

        // Toggle element visibilities.
        $toggleClasses = [
          '#fsa-team-finder .submit-reset',
          '#fsa-team-finder .submit-finder',
          '#fsa-team-finder .form-item-query',
          '#fsa-team-finder .form-intro',
        ];
        foreach ($toggleClasses AS $class) {
          $response->addCommand(new InvokeCommand(
            $class, 'toggleClass', ['visually-hidden']
          ));
        }

        // Update form title.
        $response->addCommand(new HtmlCommand(
          '#fsa-team-finder .form-title',
          $this->t('Food safety team details')
        ));
      }
      else {
        $result_message = $this->t('No results found.');
      }
    }
    else {
      // In case mapit_area value was not found display helpful error msgs.
      if (!isset($query) || $query == '') {
        // @todo: maybe not even allow submit before input has value entered.
        $result_message = $this->t('Please enter value.');
      }
      else if (!$this->testValidUkPostcode($query)) {
        $result_message = $this->t('Invalid postcode.');
      }
      else {
        $result_message = $this->t('No food safety team found for postcode <strong>@query</strong>', ['@query' => $query]);
      }
    }

    $response->addCommand(new HtmlCommand(
      '#team-finder-results',
      [
        '#theme' => 'fsa_team_finder_results',
        '#message' => $result_message,
        '#name' => $result_name,
        '#mail' => $result_mail,
        '#site' => $result_site,
        ]
    ));

    return $response;

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
    $key = 'cGEi7enM22ZPLNJmm7i1t9g0E6K6MABwHeLhKFxI';
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
      } elseif (isset($data['shortcuts']['council']['district'])) {
        $council = $data['shortcuts']['council']['district'];
      } else {
        return array();
      }
    } else {
      return array();
    }
    return array(
      'name' => $data['areas'][$council]['name'],
      'mapit_area' => $council,
    );
  }
}