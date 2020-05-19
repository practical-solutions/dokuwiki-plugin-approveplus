# DokuWiki-Plugin: Approve Plus

Additional features for the [approve-Plugin](https://www.dokuwiki.org/plugin:approve)


## Features

* Block showing content of pages, which have no approved version
* Option to completely block pages independently from the approve-system (i.e. making it possible to block pages, which have an approved version)
* Style modifications of the original

## Coming up / planned / ideas
* Config-Option: Show Block/Unblock-Button
* german translation
* migration from old plugin
* add dw2pdf template-tag ``@APPROVER@``

## Technical Information

### Migration from the "old" approve-plugin

On update to the new, sqlite-base version of the approve-plugin, the changelog-summary-based version is not affected in general. But: The new system considers all documents as unapproved and thus as drafts, which ALL have to bei approved again. This can be a big problem for large DokuWikis with lots of pages.

The migration tool checks up the changelog file for all entries and adds all latest "approved" entries into the new database- This means: Only the last approve action while be registered in the database.

This will only work if the setting for ''recent_days'' and ''recent'' are high enough, so that the changelog contains all the entries. An alternative approach would be possible (e.g. like nspages-Plugin) but not needed until now.

This migration tool will ofcourse be removed in later versions, as it is only needed once when migrating.


### Testing

Tested with

* PHP7
* Approve-Plugin version 2020-05-11
