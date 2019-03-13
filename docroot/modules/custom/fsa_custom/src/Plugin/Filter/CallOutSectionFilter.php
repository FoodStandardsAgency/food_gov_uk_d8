<?php

namespace Drupal\fsa_custom\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * @Filter(
 *   id = "filter_fsa_custom_call_out_section",
 *   title = @Translation("FSA Call out filter"),
 *   description = @Translation("Format our call out styles"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 * )
 */
class CallOutSectionFilter extends FilterBase {

  /**
   * We are looking for sections with particular classes in the code.
   *
   * Class name is js-callout-style
   *
   * @param string $text
   * @param string $langcode
   *
   * @return \Drupal\filter\FilterProcessResult
   */
  public function process($text, $langcode) {
    $regex_pattern = '/<section class="([^"]*)">(.*)<\/section>/sU';
    $results = preg_match_all($regex_pattern , $text, $matches);

    if (!empty($results)) {
      foreach ($matches[0] as $match_id => $original_string) {
        $classes = $matches[1][$match_id];
        $inner_html = $matches[2][$match_id];

        $function_name = '__' . preg_replace('/[- .]/', '_', strtolower($classes));
        if (method_exists($this, $function_name)) {
          $replacement_text = $this->$function_name($inner_html);
          $text = str_replace($original_string, $replacement_text, $text);
        }
      }
    }

    return new FilterProcessResult($text);
  }

  public function __explanation_js_explanation($inner_html) {
    return $this->__render_call_out('explanation-style', $inner_html, t('FSA Explains'), '');
  }

  public function __important_style_js_explanation($inner_html) {
    return $this->__render_call_out('important-style', $inner_html, t('Important'), 'explanation__title--red');
  }

  public function __best_practice_style_js_explanation($inner_html) {
    return $this->__render_call_out('best-practice-style', $inner_html, t('Best practice'), 'explanation__title--green');
  }

  public function __tips_style_js_explanation($inner_html) {
    return $this->__render_call_out('tips-style', $inner_html, t('Tips'), 'explanation__title--purple');
  }

  public function __legal_advice_style_js_explanation($inner_html) {
    return $this->__render_call_out('legal-advice-style', $inner_html, t('Legal advice'), 'explanation__title--blue');
  }

  public function __render_call_out($type_class, $inner_html, $title, $title_class) {
    $build = array(
      '#theme' => 'fsa_custom_call_out_box',
      '#content' => [
        '#type' => 'processed_text',
        '#text' => $inner_html,
        '#format' => 'full_html',
      ],
      '#wrapper_classes' => [$type_class],
      '#title_classes' => [$title_class],
      '#title' => $title,
    );

    return \Drupal::service('renderer')->render($build);
  }

}
