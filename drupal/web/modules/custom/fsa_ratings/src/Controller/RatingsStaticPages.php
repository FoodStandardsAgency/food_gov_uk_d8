<?php

namespace Drupal\fsa_ratings\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller class for the ratings pages.
 *
 * Functions that creates static pages not editable in CMS.
 *
 * @package Drupal\fsa_ratings\Controller
 */
class RatingsStaticPages extends ControllerBase {

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  public $request;

  /**
   * Form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Class constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   Request stack.
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   Form builder.
   */
  public function __construct(RequestStack $request, FormBuilderInterface $formBuilder) {
    $this->request = $request;
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('form_builder')
    );
  }

  /**
   * Page callback for Ratings meanings page.
   */
  public function ratingMeanings() {

    $ratings_table = [];
    $item_theme = 'fsa_ratings_meanings_item';

    // Define the rating descriptions for each key.
    // @todo: add copy texts for different rating explanations.
    $ratings = [
      '5' => $this->t('Top rating. The business is doing well in all three elements (food hygiene, cleanliness of premises and food safety management)'),
      '4' => $this->t('This is an explanation text for this rating.'),
      '3' => $this->t('This is an explanation text for this rating.'),
      '2' => $this->t('This is an explanation text for this rating.'),
      '1' => $this->t('This is an explanation text for this rating.'),
      '0' => $this->t('This is an explanation text for this rating.'),
    ];

    foreach ($ratings as $key => $description) {
      $ratings_table[] = [
        '#theme' => $item_theme,
        '#rating_score' => $key,
        '#rating_badge' => RatingsHelper::ratingBadgeImageDisplay($key),
        '#rating_description' => $description,
      ];
    }

    // Get FHRSID from url (if set) to link back to the referring Establishment.
    $fhrsid = $this->request->getCurrentRequest()->get('fhrsid');
    // Loose check with if is numeric to avoid WSOD.
    if (is_numeric($fhrsid)) {
      $url = Url::fromRoute('entity.fsa_establishment.canonical', ['fsa_establishment' => $fhrsid]);
      // Pass query params to preserve the form submission.
      $query = $this->request->getCurrentRequest()->query->all();
      unset($query['fhrsid']);
      $url->setOptions(['query' => $query]);
      $backlink_text = $this->t('Back');
    }
    else {
      // If not a proper referrer or empty fhrsid link to search page.
      $url = Url::fromRoute('fsa_ratings.ratings_search');
      $backlink_text = $this->t('Back to ratings search');
    }

    $backlink = Link::fromTextAndUrl($backlink_text, $url)->toString();

    return [
      '#theme' => 'fsa_ratings_meanings',
      '#search_form' => $this->formBuilder->getForm('Drupal\fsa_ratings\Form\FsaRatingsSearchForm'),
      '#ratings' => $ratings_table,
      '#paragraph_1' => $this->t('The food hygiene rating reflects the hygiene standards found at the time the business is inspected by a food safety officer. These officers are specially trained to assess food hygiene standards.'),
      '#paragraph_2' => $this->t('The rating given shows how well the business is doing overall but also takes account of the element or elements most in need of improving and also the level of risk to people’s health that these issues pose. This is because some businesses will do well in some areas and less well in others but each of the three elements checked is essential for making sure that food hygiene standards meet requirements and the food served or sold to you is safe to eat.'),
      '#backlink' => $backlink,
    ];
  }

}
