# FSA Custom

Module for all small customization that do not make sense to put in separate module(s).

##### FSA Custom module functionality list:
* Creates base for custom module configuration (`/admin/config/fsa` and `/admin/config/fsa/custom`) pages and basic customization admin form.
* Alter WYSIWYG allowed tags
* Creates "Year select" plugin for views
* Creates FSA Hero block
  * Contact section static hero.
  * FHRS Establishment static hero.
* Handles "Page title" block visibilities
  * The Page title block is set to Hero AND Content region and shown in either region based on template requirements. 
* Overrides cookieconsent plugin default CSS.
* Adds 'Delete' link to /admin/content/files view, which links to new route and confirmation form.