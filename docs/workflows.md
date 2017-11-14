#Workflows

##Editoral

Three roles are pertinent to the editorial workflow:
1. Author
2. Translator
3. Editor

To have the power to do everything in this workflow, a user should possess all three roles.

An _author_ may view published content,create new content, and view or edit their own unpublished content. When they are satisfied, they may move their content from a _draft_ state into a _drafted_ state. They may, also, move their content back into a _draft_ state if, for example, they notice a mistake in what they have authored. Authors cannot edit each others content.

**Author: Draft <-> Drafted**

An _editor_ can perform most operations on content. If the drafted content does not require translation, the editor can move it directly into a _published_ state, at which point its status will automatically change to published. The content can, now, be viewed by all visitors to the site, and indexed by search engines. They can also move it back into a _draft_ state, if they feel it requires more work. They can log a message, for the attention of an author, stating why they have moved it back.

**Editor: Draft <- Drafted -> Published**

If, after reviewing a piece of content, in a _drafted_ state, the _editor_ feels it is ready to be translated, they may change it's state to _untranslated_. Alternatively, they can put it back into a _draft_ state and mark it as requiring further improvement by an _author_, before it can be translated.

**Editor: Draft <- Drafted -> Untranslated**

In the case where the editor moves a piece of content into the _untranslated_ state, a _translator_ is now able to translate the content, A translator can translate any unpublished content. When their translation is completed, they may move it into a _translated_ state. Again, if they spot a mistake after moving it, they may move it back to an _untranslated_ state and continue working on the content, moving it forward, again, when they are satisfied it is correct.

**Translator: Untranslated <-> Translated**

Once moved into a translated state, the content is ready to be published by an _editor_. When it is moved into a published state it will automatically undergo a status change from unpublished to published. After publication, content can either be moved back into an _untranslated_ state (or into a _draft_ state - see above).

**Editor: Untranslated <- Translated -> Published**

Alternatively, after publication, content can be put into an _archived_ state, if it is no longer needed. Of course, it could be restored to either a _published_ or _draft_ state. This can be useful for seasonal content, which is re-purposed year after year.

**Editor: Published / Draft <-> Archived**
