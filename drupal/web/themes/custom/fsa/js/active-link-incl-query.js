(function (Drupal, drupalSettings) {

  'use strict';

  // Get original activeLinks behaviour.
  var activeLinks = Drupal.behaviors.activeLinks || { attach: function(){} };

  /**
   * Add "is-active" class, regardless of query parameters.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.activeLinksInclQuery = {
    attach: function (context) {
      // Save current query.
      var currentQuery = drupalSettings.path.currentQuery;
      // Unset query so that activeLinks behavior is not aware of them.
      drupalSettings.path.currentQuery = '';
      // Call the behavior's attach method.
      activeLinks.attach.call(this, context);
      // Set the query back.
      drupalSettings.path.currentQuery = currentQuery;
    }
  };

})(Drupal, drupalSettings);
