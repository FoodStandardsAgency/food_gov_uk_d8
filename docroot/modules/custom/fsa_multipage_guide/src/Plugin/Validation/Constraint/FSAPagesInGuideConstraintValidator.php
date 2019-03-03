<?php

namespace Drupal\fsa_multipage_guide\Plugin\Validation\Constraint;

use Drupal\fsa_multipage_guide\FSAMultiPageGuide;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the FSAPagesInGuide constraint.
 */
class FSAPagesInGuideConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   * @var \Drupal\fsa_multipage_guide\Plugin\Validation\Constraint\FSAPagesInGuideConstraint $constraint
   */
  public function validate($items, Constraint $constraint) {
    /** @var \Drupal\node\Entity\Node $this_guide */
    $this_guide = $this->context->getRoot()->getValue();
    $this_guide_id = $this_guide->id();

    $this_guide_referenced_page_ids = [];

    /** @var \Drupal\node\Entity\Node $page */
    foreach ($items->referencedEntities() as $page) {

      if (in_array($page->id(), $this_guide_referenced_page_ids)) {
        // Referenced the same page twice error.
        $this->context->addViolation($constraint->pageAlreadyInThisGuide, ['%page' => $page->getTitle()]);
      }

      $this_guide_referenced_page_ids[] = $page->id();

      $guide = FSAMultiPageGuide::GetGuideForPage($page);

      if (!empty($guide) && $guide->getId() !== $this_guide_id) {
        // Referenced by other guide error.
        $this->context->addViolation($constraint->pageAlreadyInAGuide, [
          '%page' => $page->getTitle(),
          '%guide' => $guide->getTitle(),
        ]);
      }
    }
  }

}
