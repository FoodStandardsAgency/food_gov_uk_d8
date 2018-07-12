<?php

namespace Drupal\fsa_signin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fsa_custom\FsaCustomHelper;
use Drupal\fsa_signin\Controller\DefaultController;
use Drupal\user\Entity\User;
use Drupal\fsa_signin\SignInService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DeliveryOptions.
 */
class DeliveryOptions extends FormBase {

  /**
   * Signin service.
   *
   * @var \Drupal\fsa_signin\SignInService
   */
  protected $signInService;

  /**
   * {@inheritdoc}
   */
  public function __construct(SignInService $signInService) {
    $this->signInService = $signInService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('fsa_signin.service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'delivery_options';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#attributes']['class'][] = DefaultController::PROFILE_FORM_HTML_CLASS;

    /** @var \Drupal\user\Entity\User $account */
    $account = User::load(\Drupal::currentUser()->id());

    $form['delivery']['delivery_method'] = [
      '#title' => $this->t('I want to receive food and allergy alerts via'),
      '#type' => 'checkboxes',
      '#options' => [
        'email' => $this->t('Email'),
        'sms' => $this->t('SMS'),
      ],
      '#default_value' => array_column($account->get('field_delivery_method')->getValue(), 'value'),
    ];
    $form['delivery']['delivery_method_news'] = [
      '#title' => $this->t('I want to receive news and consultations via'),
      '#type' => 'checkboxes',
      '#options' => [
        'email' => $this->t('Email'),
      ],
      '#default_value' => array_column($account->get('field_delivery_method_news')->getValue(), 'value'),
    ];
    $form['delivery']['frequency'] = [
      '#type' => 'item',
      '#markup' => '<h3>' . $this->t('Frequency') . '</h3>',
    ];
    $form['delivery']['sms_notification_delivery'] = [
      '#type' => 'checkboxes',
      '#options' => [],
      '#title' => $this->t('SMS frequency'),
      '#markup' => '<p>' . $this->t('SMS updates are sent immediately') . '</p>',
    ];

    // field_email_frequency is a new field replacing old combined
    // field_notification_method hence can be empty on some users. Set default
    // in case is empty.
    $email_frequency = $account->get('field_email_frequency')->getString();
    $email_frequency = ($email_frequency) ? $email_frequency : 'immediate';
    $form['delivery']['email_frequency'] = [
      '#type' => 'radios',
      '#title' => $this->t('Email frequency'),
      '#required' => TRUE,
      '#options' => [
        'immediate' => $this->t('Send updates immediately'),
        'daily' => $this->t('Send updates daily'),
        'weekly' => $this->t('Send updates weekly'),
      ],
      '#default_value' => $email_frequency,
    ];
    $form['delivery']['personal_info'] = [
      '#type' => 'item',
      '#markup' => '<h3>' . $this->t('Personal information') . '</h3>',
    ];
    $form['delivery']['email'] = [
      '#type' => 'item',
      '#title' => $this->t('Email address'),
      '#markup' => '<i>' . $account->getEmail() . '</i>',
    ];
    $form['delivery']['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone number'),
      '#default_value' => $account->get('field_notification_sms')->getString(),
      '#description' => $this->t('This service is only for UK telephone numbers'),
      '#field_prefix' => '+' . SignInService::DEFAULT_COUNTRY_CODE,
      '#states' => [
        'visible' => [
          ':input[name="delivery_method[sms]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['profile']['language'] = [
      '#type' => 'radios',
      '#title' => $this->t('Language preference'),
      '#options' => [
        'en' => $this->t('English'),
        'cy' => $this->t('Cymraeg'),
      ],
      '#default_value' => $account->getPreferredLangcode(),
    ];
    $form['extra']['privacy_notice'] = [
      '#type' => 'item',
      '#markup' => FsaCustomHelper::privacyNoticeLink('alerts'),
    ];

    // Submit and other actions.
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['back'] = [
      '#markup' => DefaultController::linkMarkup('fsa_signin.user_preregistration_news_form', $this->t('Previous'), ['back arrow']),
    ];
    $form['actions']['submit_edit'] = [
      '#type' => 'button',
      '#executes_submit_callback' => TRUE,
      '#value' => $this->t('Save'),
    ];
    // Attach js for the "select all" feature.
    $form['#attached']['library'][] = 'fsa_signin/subscription_alerts';

    // Fetch session values for data layer work.
    $session = \Drupal::service('user.private_tempstore')->get('fsa_signin');
    $session_proceed = $session->get('deliverySubmitted');
    $session_val = $session->get('deliveryEdit');
    if ($session_proceed && $session_val) {
      // Push event info to data layer based on session values.
      datalayer_add($session_val);
      // Delete the form submission session data.
      $session->delete('deliverySubmitted');
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    /*
    $email = $form_state->getValue('email');
    if (!\Drupal::service('email.validator')->isValid($email)) {
    $form_state->setErrorByName('email',$this->t('Email value is not valid.'));
    }
     */

    $delivery_method = $form_state->getValue('delivery_method');
    $delivery_method = array_filter(array_values($delivery_method));
    if (in_array('sms', $delivery_method)) {
      $phone = $form_state->getValue('phone');

      if (!preg_match('/^[0-9 ]{0,}$/', $phone)) {
        $form_state->setErrorByName('phone', $this->t('Special characters are not allowed in phone number.'));
      }
      elseif (!preg_match('/^[0-9 ]{8,}$/', $phone)) {
        $form_state->setErrorByName('phone', $this->t('Phone number appears to be too short.'));
      }
      if ($phone == '') {
        $form_state->setErrorByName('phone', $this->t('You selected to receive alerts via SMS, please enter your phone number.'));
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\user\Entity\User $account */
    $account = User::load(\Drupal::currentUser()->id());

    // $email = $form_state->getValue('email');
    // $account->setEmail($email);
    $delivery_method = $form_state->getValue('delivery_method');
    $delivery_method = array_filter(array_values($delivery_method));
    $account->set('field_delivery_method', $delivery_method);

    $delivery_method_news = $form_state->getValue('delivery_method_news');
    $delivery_method_news = array_filter(array_values($delivery_method_news));
    $account->set('field_delivery_method_news', $delivery_method_news);

    // Store phone without spaces and remove countrycode if it was entered.
    $phone = ltrim(str_replace(' ', '', $form_state->getValue('phone')), SignInService::DEFAULT_COUNTRY_CODE);
    if (in_array('sms', $delivery_method)) {
      // Only store the phone number if user subscribed via SMS.
      $account->set('field_notification_sms', $phone);
    }
    else {
      $account->set('field_notification_sms', '');
    }

    $email_frequency = $form_state->getValue('email_frequency');
    $account->set('field_email_frequency', $email_frequency);

    $language = $form_state->getValue('language');
    $account->set('langcode', $language);
    $account->set('preferred_langcode', $language);

    // Gather user data to put into arrays - text lists.
    // field_delivery_method
    $field_delivery_method = ['email' => FALSE, 'sms' => FALSE];
    $field_delivery_method = $this->createTextListArray($field_delivery_method, $delivery_method);
    // field_delivery_method_news
    $field_delivery_method_news = ['email' => FALSE];
    $field_delivery_method_news = $this->createTextListArray($field_delivery_method_news, $delivery_method_news);
    // field_subscribed_food_alerts
    $food_alerts = array_column($account->get('field_subscribed_food_alerts')->getValue(), 'value');
    $field_subscribed_food_alerts = ['all' => FALSE];
    $field_subscribed_food_alerts = $this->createTextListArray($field_subscribed_food_alerts, $food_alerts);
    // field_email_frequency
    $field_email_frequency = ['immediate' => FALSE, 'daily' => FALSE, 'weekly' => FALSE];
    if (array_key_exists($email_frequency, $field_email_frequency)) {
      $field_email_frequency[$email_frequency] = TRUE;
    }
    
    // Gather user data to put into arrays - taxonomies.
    // field_subscribed_news
    $news_terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('news_type');
    $user_sub_news = $account->get('field_subscribed_news')->getValue();
    $field_subscribed_news = $this->createVocabArray($news_terms, $user_sub_news);
    // field_subscribed_notifications
    $allergy_terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('alerts_allergen');
    $user_sub_allergy = $account->get('field_subscribed_notifications')->getValue();
    $field_subscribed_notifications = $this->createVocabArray($allergy_terms, $user_sub_allergy);
    // field_subscribed_cons
    $cons_terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('consultations_type_alerts');
    $user_sub_cons = $account->get('field_subscribed_cons')->getValue();
    $field_subscribed_cons = $this->createVocabArray($cons_terms, $user_sub_cons);

    // Fetch the profile field values to include in the data layer.
    $event_label = array(
      'field_subscribed_food_alerts' => $field_subscribed_food_alerts,
      'field_subscribed_notifications' => $field_subscribed_notifications,
      'field_subscribed_news' => $field_subscribed_news,
      'field_subscribed_cons' => $field_subscribed_cons,
      'field_delivery_method' => $field_delivery_method,
      'field_notification_sms' => $phone,
      'field_delivery_method_news' => $field_delivery_method_news,
      'field_email_frequency' => $field_email_frequency,
      'preferred_langcode' => $language,
    );

    // Create a variable for the event session.
    $delivery_field = $account->get('field_initial_delivery_settings')->value;
    if ($delivery_field == 1) {
      $delivery_edit = $this->deliveryDataLayer('Edit', $event_label);
    }
    else {
      $delivery_edit = $this->deliveryDataLayer('Set', $event_label);
    }

    // Set the user value to TRUE.
    $account->set('field_initial_delivery_settings', 1);

    if ($account->save()) {
      drupal_set_message($this->t('Your preferences are updated.'));

      // Set a session variable based on the current user value.
      $session = \Drupal::service('user.private_tempstore')->get('fsa_signin');
      $session->set('deliveryEdit', $delivery_edit);
      // Set a session variable to confirm submission.
      $session->set('deliverySubmitted', TRUE);
    }
    else {
      drupal_set_message($this->t('There was an error updating your preferences. Please try again.'));
    }
  }

  // Creates an array of user info for use in the data layer.
  function deliveryDataLayer($form_process, $event_label) {
    $delivery_edit = array();
    if (in_array($form_process, array('Set', 'Edit'))) {
      $delivery_edit = array(
        'event' => 'Subscription Saved',
        'eventCategory' => 'Subscription',
        'eventAction' => $form_process,
        'eventLabel' => $event_label,
        'eventValue' => 0,
      );
    }
    return $delivery_edit;
  }

  // Converts a string to a machine name style format.
  function termNameTransform($string) {
    $new_string = strtolower($string);
    $new_string = preg_replace('/[^a-z0-9_]+/', '_', $new_string);
    return preg_replace('/_+/', '_', $new_string);
  }

  // Create an array containing a user's collection of items from a text list.
  function createTextListArray($all_items, $user_items) {
    foreach ($user_items as $key => $user_item) {
      if (array_key_exists($user_item, $all_items)) {
        $all_items[$user_item] = TRUE;
      }
    }
    return $all_items;
  }

  // Create an array containing a user's collection of terms.
  function createVocabArray($all_terms, $user_terms) {
    $vocab_array = array();
    if (is_array($all_terms) && is_array($user_terms)) {
      // Tidy the format of the user terms array.
      $user_terms_filtered = array();
      foreach ($user_terms as $user_term) {
        $user_terms_filtered[] = $user_term['target_id'];
      }
      // Create the vocab array, mark to true if also in user's array.
      foreach ($all_terms as $this_term) {
        $term_name = $this->termNameTransform($this_term->name);
        $vocab_array[$term_name] = FALSE;
        if (in_array($this_term->tid, $user_terms_filtered)) {
          $vocab_array[$term_name] = TRUE;
        }
      }
    }
    return $vocab_array;
  }

}
