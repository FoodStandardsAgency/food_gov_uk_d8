### Linkit content

Linkit content is a module that alters popup dialog of Linkit WYSIWYG button. It adds `Find content` button in linkit dialog window. When the `Find content` button is pressed a popup with content selection view is opened and user can find and select some content (node). When the node is selected, it's body field is scanned for `h2` tags and links to those tags are returned. User can select link and it's returned into `linkit` dialog window prepending an anchor to the /node/ID path. The module also utilizes content linking by uuid.

Requires a patch to [Linkit](https://www.drupal.org/project/linkit) module: https://www.drupal.org/project/linkit/issues/2895153#comment-12395118

Functionality originally built by janis.bebritis@wunder.io