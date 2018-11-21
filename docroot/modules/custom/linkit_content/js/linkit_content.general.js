(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.linkit_content = {
        attach: function (context, settings) {

            // There's a dialog:afterclose action, that removes save action from linkit modal
            // when the close button is clicked (see ckeditor.js, search for "Drupal.ckeditor.saveCallback").
            // So we are overriding the dialog.close function, but only when dialog
            // class is "linkit-content-embed-dialog".
            $(window).on({
                'dialog:beforecreate': function (event, dialog, $element, settings) {

                    if (settings.dialogClass == 'linkit-content-embed-dialog') {

                        settings.close = closeDialog;

                        function closeDialog(value) {
                            $(window).trigger('dialog:beforeclose', [dialog, $element]);
                            $element.dialog('close');
                            dialog.returnValue = value;
                            dialog.open = false;
                            // This one is the reason it breaks apart, maybe ckeditor does something about it one day.
                            // $(window).trigger('dialog:afterclose', [dialog, $element]);
                        }
                    }

                },
            });

            // Disable scrolling of the whole browser window to not interfere with the
            // iframe scrollbar.
            $(window).on({
                'dialog:aftercreate': function (event, dialog, $element, settings) {
                    $('body').css({overflow: 'hidden'});
                },
                'dialog:beforeclose': function (event, dialog, $element) {
                    $('body').css({overflow: 'inherit'});
                }
            });
        }
    }
})(jQuery, Drupal, drupalSettings);