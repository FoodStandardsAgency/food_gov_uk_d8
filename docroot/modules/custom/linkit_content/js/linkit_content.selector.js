(function ($) {
    Drupal.behaviors.linkit_content_selector = {
        attach: function (context, settings) {

            $(context).find('tr.linkit-content-selector-row').once('register-row-click').click(function (event) {

                    // Get link from last column.
                    selected_link = $(this).find('td.views-field-nid').html();
                    selected_link = selected_link.trim();

                    // Pass selected item to parent frame settings.
                    parent.drupalSettings.linkit_content.selected_link = selected_link;

                    // Redirect this iframe to anchor selection page.
                    document.location.href = document.location.href + "?selected_link=" + selected_link;
                });

            $(context).find('tr.anchor-row').once('register-row-click').click(function (event) {

                // Get selected anchor from last column.
                selected_anchor = $(this).find('td.anchor-id').html();
                selected_anchor = selected_anchor.trim();

                // Get currently selected link from drupalSettings object we passed while generating page.
                selected_link = drupalSettings.linkit_content.selected_link;
                selected_uuid = drupalSettings.linkit_content.selected_uuid;
                selected_type = drupalSettings.linkit_content.selected_type;
                selected_substitution = drupalSettings.linkit_content.selected_substitution;

                // Pass selected item to parent frame settings. We don't actually need them in this step anymore,
                // but it feels good to have complete data in drupalSettings object. #randomjoyofdeveloper.
                parent.drupalSettings.linkit_content.selected_anchor = selected_anchor;
                parent.drupalSettings.linkit_content.selected_link = selected_link;
                parent.drupalSettings.linkit_content.selected_uuid = selected_uuid;
                parent.drupalSettings.linkit_content.selected_type = selected_type;
                parent.drupalSettings.linkit_content.selected_substitution = selected_substitution;

                // And what's the most important, fill the linkit  autocomplete field
                var caller_dialog = parent.jQuery(parent.document).find('.editor-link-dialog');
                caller_dialog.find('.ui-autocomplete-input').val(selected_link + selected_anchor);
                caller_dialog.find('input[data-drupal-selector=edit-href-dirty-check]').val(selected_link + selected_anchor);
                caller_dialog.find('input[data-drupal-selector=edit-attributes-data-entity-type]').val(selected_type);
                caller_dialog.find('input[data-drupal-selector=edit-attributes-data-entity-uuid]').val(selected_uuid);

                // TODO: Linkit filter should honour the URL query and fragment
                // Apparently, there's an issue with this one in linkit module - when the substitution is canonical,
                // it displays the node link alias from uuid instead of href and looses the anchor.
                // See issue https://www.drupal.org/node/2895153
                // We will have ugly links in code (/node/123#anchorlink) instead of (/somepage#anchorlink)
                // unless that issue is fixed :(
                // selected_substitution = 'invalid'; // Remove this once fixed
                caller_dialog.find('input[data-drupal-selector=edit-attributes-data-entity-substitution]').val(selected_substitution);

                // Close dialog window (there should be CloseDialogCommand command for js somewhere, but I can't find it now)
                parent.jQuery(parent.document).find('.linkit-content-modal-iframe')
                    .parents('.ui-dialog').eq(0).find('.ui-dialog-titlebar-close').click();
            });
        }
    };
})(jQuery);
