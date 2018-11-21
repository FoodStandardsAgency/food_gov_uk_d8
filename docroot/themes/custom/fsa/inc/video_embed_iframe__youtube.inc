<?php

/**
 * @file
 * Includes FSA theme video embed iframe youtube preprocess functionality.
 */

/**
 * Implements template_preprocess_video_embed_iframe__youtube().
 */
function fsa_preprocess_video_embed_iframe__youtube(&$variables) {
  $variables['query']['enablejsapi'] = '1';
}
