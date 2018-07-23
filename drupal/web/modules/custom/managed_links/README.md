# Managed Links

A module that provides a central library of links for usage across the site's
content pages. Managed links are defined as a lightweight custom entity type that
supports translation and publish states.

Managed link entities are incorporated into content through the use of LinkIt
and the CKEditor plugin it provides. A matcher profile is defined that allows
searches to operate on Managed link entity titles and the suggestion URL
uses the Link field URI value rather than the default canonical entity path.

A text filter is needed to convert LinkIt enabled `<a>` element `href` 
attribute values. This filter also ensures that source code tweaking
does not affect the link destination as the URI is managed centrally.

The text filter should be attached to the Basic and Full HTML profiles.

Troubleshooting:

- My URL path points to the canonical entity path, not the URI value.
  - Make sure the text filter is enabled in your WYSIWYG profile.
- Config import fails because it says the entity type or WYSIWYG profile depends 
  on something from this module.
  - This is a known headache and requires careful unpicking of dependencies, usually
  needing the module enabling, content removed, removal of the filter from
  WYSIWYG profiles, then uninstallation of the module. Should not be a problem
  unless you're debugging and switching feature branches in the middle. 
