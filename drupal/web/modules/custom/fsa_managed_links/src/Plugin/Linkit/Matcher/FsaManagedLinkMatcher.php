<?php

/**
 * @file
 * Custom Linkit profile matcher class, because it wasn't possible
 * to use the included Entity matcher and rewrite the target path
 * of the suggestion results. Without this class, you end up
 * fetching the entity's canonical path which makes sense in every
 * other case except for using this for external linking using
 * and internal entity.
 */
namespace Drupal\fsa_managed_links\Plugin\Linkit\Matcher;

use Drupal\linkit\Plugin\Linkit\Matcher\EntityMatcher;
use Drupal\linkit\Suggestion\EntitySuggestion;
use Drupal\linkit\Suggestion\SuggestionCollection;


/**
* Provides specific LinkIt matchers for FSA Managed Links.
*
* @Matcher(
*   id = "entity:fsa_managed_link",
*   label = @Translation("FSA Managed Link entities"),
*   target_entity = "fsa_managed_link",
*   provider = "fsa_managed_links"
* )
*/
class FsaManagedLinkMatcher extends EntityMatcher {

  /**
  * {@inheritdoc}
  */
  public function execute($string) {
    $suggestions = new SuggestionCollection();
    $query = $this->buildEntityQuery($string);
    $query_result = $query->execute();
    $url_results = $this->findEntityIdByUrl($string);
    $result = array_merge($query_result, $url_results);

    if (empty($result)) {
      return $suggestions;
    }

    $entities = $this->entityTypeManager->getStorage($this->targetType)->loadMultiple($result);

    foreach ($entities as $entity) {
      // Check the access against the defined entity access handler.
      /** @var \Drupal\Core\Access\AccessResultInterface $access */
      $access = $entity->access('view', $this->currentUser, TRUE);
      if (!$access->isAllowed()) {
        continue;
      }

      $entity = $this->entityRepository->getTranslationFromContext($entity);
      $url = $entity->field_managed_link_url->uri;

      if (empty($url)) {
        continue;
      }

      $suggestion = new EntitySuggestion();
      $suggestion->setLabel($this->buildLabel($entity))
        ->setGroup($this->buildGroup($entity))
        ->setDescription($this->buildDescription($entity))
        ->setEntityUuid($entity->uuid())
        ->setEntityTypeId($entity->getEntityTypeId())
        ->setSubstitutionId($this->configuration['substitution_type'])
        ->setPath($url);

      $suggestions->addSuggestion($suggestion);
    }

    return $suggestions;
  }

}
