# DokuWiki-Plugin: Approve Plus

Additional features for the [approve-Plugin](https://www.dokuwiki.org/plugin:approve)


## Features

* Block showing content of pages, which have no approved version
* Option to completely block pages independently from the approve-system (i.e. making it possible to block pages, which have an approved version)
* Add dw2pdf template-tag ``@APPROVER@``
* Style modifications of the original
* In Combination with [dw2pdf / Modified Version](https://github.com/practical-solutions/dokuwiki-plugin-dw2pdf): Block pdf-Generation of unapproved pages

### Batch approve documents in a namespace

Plugin to quickly approve all documents in a namespace (for instance after using the [move](https://www.dokuwiki.org/plugin:move) or [batch edit](https://www.dokuwiki.org/plugin:batchedit) plugin). Navigate to the admin section to chose the option.

This function is generally to be implemented in coming versions of the approve plugin (see [Issue #23](https://github.com/gkrid/dokuwiki-plugin-approve/issues/25)). 
This function will then be removed.


## Compatibility

Tested with

* PHP **7.3**
* DokuWiki / **Hogfather**
* [Approve-Plugin](https://www.dokuwiki.org/plugin:approve) Version **2020-09-21**
* [dw2pdf / Modified Version](https://github.com/practical-solutions/dokuwiki-plugin-dw2pdf) **2020-09-15** (which is forked from dw2pdf Version **2020-08-11**)
