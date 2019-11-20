=== DCO Comment Attachment ===
Contributors: denisco
Tags: comment, comment attachment, attachment, image, video
Requires at least: 4.6
Tested up to: 5.3
Requires PHP: 5.4
Stable tag: 1.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows your visitors to attach files with their comments

== Description ==
DCO Comment Attachment allows your visitors to attach images, videos, audios, documents and other files with their comments.

With plugin settings you can:

* Select an attachment image size from thumbnails available in your WordPress install.
* Limit the maximum file upload size.
* Make an attachment required.
* Specify whether the attachment will be embedded or displayed as a link.
* Attach an attachment to a commented post.
* Restrict attachment file types.

You can also:

* Add, replace or delete an attachment from a comment on Edit Comment screen.
* Delete an attachment on Comments screen.

Attachments are uploaded to the WordPress Media Library and deleted along with the comment (if this is set in the settings).

DCO Comment Attachment is also available on [GitHub](https://github.com/yadenis/DCO-Comment-Attachment).

== Installation ==
1. Upload `dco-comment-attachment` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 1.1.2 =
* Fixed display of empty allowed types if the website administrator has forbidden the upload of all extensions of this type. (thank you @nunofrsilva)

= 1.1.1 =
* Added filters for the attachment field customization

= 1.1.0 =
* Now you can select and deselect Allowed File Types by the type in one click.
* Added `dco_ca_disable_attachment_field` hook for disable the upload attachment field.
* Reduced the effect of mime types filtering. Now it applies only for comment attachment upload.
* Added the feature to attach an attachment to a commented post.

= 1.0.0 =
* Initial Release