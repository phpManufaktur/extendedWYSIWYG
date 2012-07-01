### extendedWYSIWYG

_extendedWYSIWYG_ is an replacement for the standard WYSIWYG module of the Content Management Systems [WebsiteBaker] [1] _or_ [LEPTON CMS] [2]. It offers a version control, delayed publish, backup and many more. 

#### Requirements

* minimum PHP 5.2.x
* using [WebsiteBaker] [1] 2.8.x _or_ using [LEPTON CMS] [2] 1.x
* [Dwoo Template Engine] [6] installed
* [libMarkdown] [7] (_optional_)

#### Downloads

* [Dwoo Template Engine] [8]
* [libMarkdown] [9]
* [extendedWYSIWYG] [3]

#### Important

The original WYSIWYG module will be replaced by **extendedWYSIWYG**. In different to other add-ons the WYSIWYG module is an mandantory system extension which is needed to output nearly each page, so it can **not** uninstalled because of the risk to loose all your content.

If you want to switch back from the **extendedWYSIWYG** to the original WYSIWYG you must do this manually. In the folder _/modules/wysiwyg/restore_ you will find the original WYSIWYG modules for the different WebsiteBaker and LEPTON versions - the [Addons Support Group] [4] will help you to restore.

#### Installation

* download the actual [extendedWYSIWYG] [3] installation archive
* in CMS backend select the ZIP file from "Add-ons" -> "Modules" -> "Install module"

#### First Steps

If you add a new page in the _page tree_ you will now see **extendedWYSISWYG** instead of **WYSIWYG** as default type:

![Add a new page in the page tree] [20]

Now add or modify a page. At the right side above your default WYSIWYG editor you will see two menu items:

* `extendedWYSIWYG` will display the about dialog and give you additional informations to the installed release number and show you the CHANGELOG.
* `Settings` will display the configuration dialog of extendedWYSIWYG

If you switch `Create Archive Files` in the settings to `Yes` **extendedWYSIWYG** will create the protected directory `/media/wysiwyg_archive` and start to store WYSIWYG sections which get the status `BACKUP` as HTML files. This function will also copy all embedded images. `Create Archive Files` is intended to document all changes like a _time machine_ so it will need some disk space.

Below your default WYSIWYG editor you will see a small action bar:

![The action bar below the WYSIWYG editor] [21]

It's easy to handle. If you change the content, deselect the `publish` checkbox and save the section, the content will stored as `UNPUBLISHED`. This mean, that this content is only present in your WYSIWYG editor but not published at the website. The next time you open this section for edit, the WYSIWYG editor will show you the unpublished content - you can edit ahead. If you want to publish the content just check `publish` and save the section.

In the dropdown you can select previous versions - marked as `BACKUP`, the actual published content - marked as `ACTIVE` or not published content - marked as `UNPUBLISHED`.

Please visit the [phpManufaktur] [5] to get more informations about **extendedWYSIWYG** and join the [Addons Support Group] [4].

[1]: http://websitebaker2.org "WebsiteBaker Content Management System"
[2]: http://lepton-cms.org "LEPTON CMS"
[3]: https://addons.phpmanufaktur.de/download.php?file=extendedWYSIWYG "Download extendedWYSIWYG"
[4]: https://phpmanufaktur.de/support "Addons Support Group"
[5]: https://addons.phpmanufaktur.de/extendedWYSIWYG "Read more about extendedWYSIWYG"
[6]: https://addons.phpmanufaktur.de/Dwoo "Read more about Dwoo"
[7]: https://addons.phpmanufaktur.de/libMarkdown "Read more about libMarkdown"
[8]: https://addons.phpmanufaktur.de/download.php?file=Dwoo "Download Dwoo"
[9]: https://addons.phpmanufaktur.de/download.php?file=libMarkdown "Download libMarkdown"

[20]: https://media.phpmanufaktur.de/content/addons/readme/extendedwysiwyg/add-page.png
[21]: https://media.phpmanufaktur.de/content/addons/readme/extendedwysiwyg/action-bar.png
