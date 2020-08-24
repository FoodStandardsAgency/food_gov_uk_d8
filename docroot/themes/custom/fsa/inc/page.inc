<?php

/**
 * @file
 * Includes FSA theme page preprocess functionality.
 */

use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

/**
 * Implements template_preprocess_page().
 */
function fsa_preprocess_page(&$variables) {
  $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
  $variables['current_language'] = $language;
  if ($language == 'cy') {
    $variables['home_url'] = '/cy';
  }
  else {
    $variables['home_url'] = '/';
  }

  // Do magic when we have a node object.
  $node = \Drupal::routeMatch()->getParameter('node');
  if (is_object($node)) {

    // Get attached hero image and send correct image style url for template.
    if (isset($node->field_header_image->target_id)) {
      $media = Media::load($node->field_header_image->target_id);
      $uri = File::load($media->get('field_image')->target_id)->getFileUri();

      if ($style = ImageStyle::load('hero')) {
        $url = $style->buildUrl($uri);
      }
      else {
        // Fallback to the original if custom style(s) do not exist.
        $url = file_create_url($uri);
      }

      $variables['hero_image_url'] = $url;
    }
  }
}
