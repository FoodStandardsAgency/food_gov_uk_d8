(function ($) {
  'use strict';

  function my_addtoany_onready() {
    $('a.addtoany_share_save').html(Drupal.t('Share'));
    $('a.addtoany_share_save').removeClass('ext');
  }

  // Setup AddToAny "onReady" callback function
  a2a_config.callbacks = a2a_config.callbacks || [];
  a2a_config.callbacks.push({
      ready: my_addtoany_onready
  });

}(jQuery));
