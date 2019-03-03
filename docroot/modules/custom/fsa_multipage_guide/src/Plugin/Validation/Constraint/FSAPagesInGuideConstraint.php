<?php

namespace Drupal\fsa_multipage_guide\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
* Checks that the submitted duration is of the format HH:MM:SS
*
* @Constraint(
*   id = "FSAPagesInGuide",
*   label = @Translation("Pages in guide", context = "Validation"),
* )
*/
class FSAPagesInGuideConstraint extends Constraint {
  public $pageAlreadyInThisGuide = 'You can only reference the same page once in a guide. Remove all but one reference to %page.';
  public $pageAlreadyInAGuide = 'A page can only ever be in one multi page guide. %page is already in %guide';
}
