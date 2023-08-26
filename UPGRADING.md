# Upgrading Kimai

_Make sure to create a backup before you start!_ 

Read the [updates documentation](https://www.kimai.org/documentation/updates.html) to find out how 
you can upgrade your Kimai installation to the latest stable release.

Check below if there are more version specific steps required, which need to be executed after the normal update process.
Perform EACH version specific task between your version and the new one, otherwise you risk data inconsistency or a broken installation.

## [2.0](https://github.com/kimai/kimai/releases/tag/2.0)

**!! This release requires minimum PHP version to 8.1 !!**

Developer read the full documentation at [https://www.kimai.org/documentation/migration-v2.html](https://www.kimai.org/documentation/migration-v2.html).

- **All plugins need to be updated**: delete all previous version from your installation (`rm -r var/plugins/*`) before updating!
- Invoice renderer and templates for XML, JSON and TEXT were moved to the [Extended invoicing plugin](https://www.kimai.org/store/invoice-bundle.html) (install if you use one of those)
- Moved `company.docx` to [external repo](https://github.com/kimai/invoice-templates/tree/main/docx-company) (needs to be re-uploaded if you want to keep on using it!)
- Role names are forced to be uppercase 
- Removed unused `public/avatars/` directory 
- `local.yaml` is not compatible with old version, remove it before the update and then re-create it after everything works 
  - dashboard default config
  - removed: theme.branding.translation
  - removed: kimai.plugin_dir
- Time-tracking mode `duration_only` was removed, existing installations will be switched to `duration_fixed_begin`
- Removed Twig filters. You might have to replace them in your custom export/invoice templates:
  - `date_full` => `date_time`
  - `duration_decimal` => `duration(true)`
  - `currency` => `currency_name`
  - `country` => `country_name`
  - `language` => `language_name`
- Removed support for custom translation files (use [TranslationBundle](https://www.kimai.org/store/translation-bundle.html) instead or write your own plugin)
- Removed all 3rd party mailer packages, you need to install them manually (ONLY if you used a short syntax to configure the `MAILER_URL` in `.env`): 
  - `composer require symfony/amazon-mailer`
  - `composer require symfony/google-mailer`
  - `composer require symfony/mailchimp-mailer`
  - `composer require symfony/mailgun-mailer`
  - `composer require symfony/postmark-mailer`
  - `composer require symfony/sendgrid-mailer`

## [1.16](https://github.com/kimai/kimai/releases/tag/1.16)

If you are using MariaDB, it must be at least version 10.1, older versions are not supported any longer.

**DEVELOPER**

- Removed `formDateTime` field from API model `I18nConfig`
- Upgraded to Doctrine DBAL 3, see [docs](https://github.com/doctrine/dbal/blob/3.1.x/UPGRADE.md) - you might have to update your bundle

## [1.15](https://github.com/kimai/kimai/releases/tag/1.15)

**Many database changes: don't forget to [run the updater](https://www.kimai.org/documentation/updates.html).**

Updating the database might take quite a while, depending on the amount of timesheet entries and speed of your database server (~1 minute per 100k records).

**ATTENTION**

- This release bumps the minimum required [PHP version to 7.3](https://www.kimai.org/blog/2021/php8-support-php72-dropped/)
- Self-registration is disabled by default
- Self-registration now always requires email confirmation
- All plugins that use own databases need to be updated as well
- Removed the YearChart widget and the related configs named `userRecapThisYear`, `userRecapLastYear`, `userRecapTwoYears`, `userRecapThreeYears`

**LDAP & SAML**

Please verify your config with the [LDAP](https://www.kimai.org/documentation/ldap.html) and [SAML](https://www.kimai.org/documentation/saml.html) documentation, especially:

- SAML users: activate it by setting the `kimai.saml.activate: true` config key
- LDAP users: activate it by setting the `kimai.ldap.activate: true` config key
- LDAP and SAML users need to remove the complete `security` section from their `local.yaml`

**DEVELOPER**  

PHP 8 compatibility forced to upgrade MANY libraries, including but not limited to:

- Removed FOSUserBundle and hslavich/oneloginsaml
- Doctrine Migrations, whose new major version forces plugin updates 
- Gedmo v3 (which include BC breaks in definitions)
- Doctrine DBAL and others, which required PHP 7.3 as well

**API BC break**: Due to team structure changes, it was impossible to keep the (writing) API structure. Please adjust your code accordingly!

## [1.14](https://github.com/kimai/kimai/releases/tag/1.14)

**CRITICAL BC break**: SQLite support was removed. If you are using SQLite, you have to [read this blog post](https://www.kimai.org/blog/2021/sqlite-and-ftp-support-removed/) and migrate to MySQL/MariaDB first!

**New database tables and fields: don't forget to [run the updater](https://www.kimai.org/documentation/updates.html).**

Permission changes:
- `history_invoice` - removed permission entirely

## [1.13](https://github.com/kimai/kimai/releases/tag/1.13)

- Deprecated `now` variable in export templates: create it yourself with `{% set now = create_date('now', app.user) %}`
- Changed invoice filename generation (check if you use cronjob for invoices)
- **BC break**: duration entered as plain numbers will now be treated as decimal duration in hours instead of seconds

## [1.12](https://github.com/kimai/kimai/releases/tag/1.12)

- Export templates can now include items from plugins (eg. Expenses).

## [1.10](https://github.com/kimai/kimai/releases/tag/1.10)

**New database tables and fields were created, don't forget to [run the updater](https://www.kimai.org/documentation/updates.html).**

- Invoice renderer `CSV` was removed
- Sessions are now stored in the database (all users have to re-login after upgrade)
- New permissions: `lockdown_grace_timesheet`, `lockdown_override_timesheet`, `view_all_data`
- Fixed team permissions on user queries: depending on your previous team & permission setup your users might see less data (SUPER_ADMINS see all data, but new: ADMINS only see all data if they own the `view_all_data` permission)
- Markdown does not support headings anymore, text like `# foo` is not converted to `<h1 id="foo">foo</h1>` anymore

### Developer

- **BC break**: removed registration of `.env` with `putenv()` - do not rely on `getenv()` as it is not thread-safe
- **BC break**: interface method signature `HtmlToPdfConverter::convertToPdf()` changed
- **BC break**: the macros `badge` and `label` do not apply the `|trans` filter any more
- **BC Break**: removed `getVisible()` (deprecated since 1.4) method on Customer, Project and Activity (use `isVisible()` instead, templates are still working)
- **BC Break**: API changes
    - some representation names changed (eg. from `ActivityMetaField` to `ActivityMeta`, `TimesheetSubCollection` vs `TimesheetCollectionExpanded`), you could use `class_alias()` if you use auto-generated code from Swagger-Gen or alike
    - new result types were introduced
    - result data changed in some areas to smooth out inconsistencies (eg. TeamEntity fields changed in nested results)

## [1.9](https://github.com/kimai/kimai/releases/tag/1.9)

**New database tables and fields were created, don't forget to [run the updater](https://www.kimai.org/documentation/updates.html).**

- The directory `var/data/invoices/` will be used to store archived invoice files (check file permissions)
- The default invoice number format changed. If you want to use the old one: configure `{date}` as format - see [invoice documentation](https://www.kimai.org/documentation/invoices.html)
- HTML invoice templates are now treated like other files and offered as download. If you are using relative URLs for including assets (CSS, images) you need to either inline them (see the default templates) or use absolute URLs
- Invoice templates that use the templates variables `${activity.X}` or `${project.X}` should be checked and possibly adapted, as multi-select is now possible for filtering
- Invoice templates have access to all meta-fields as variables, not only the ones marked as visible
- Rates configuration/structure changed for customer, project and activity 
  - **Invoice templates**: rates variables were removed

Permission changes:
- `history_invoice` - NEW: grants all features for the new invoice archive (by default for all admins)

### Developer

- **BC break**: `InvoiceItemInterface` has new methods `getType()` and `getCategory()`
- **BC break**: API fields changed - see new `/rates` endpoints

## [1.8](https://github.com/kimai/kimai/releases/tag/1.8)

**New database tables and fields were created, don't forget to [run the updater](https://www.kimai.org/documentation/updates.html).**

- New PHP requirement: `ext-xsl` - which should be pre-installed in most environments when `ext-xml` is loaded
- New mailer library: check if emails are still working (eg. by using the "password forgotten" function) or if you need to adjust your configuration, [see docs at symfony.com](https://symfony.com/doc/current/components/mailer.html#transport)
- Support for line breaks in multiline invoice fields for spreadsheets (check your invoice templates after the update) 

Permission changes:

- `comments_create_customer` - NEW: permission that allows to add new comments for customers  
- `comments_create_team_customer` - NEW: permission that allows to add new comments for team members of the current customer 
- `comments_create_teamlead_customer` - NEW: permission that allows to add new comments for a teamlead of the current customer 
- `comments_create_project` - NEW: permission that allows to add new comments for project  
- `comments_create_team_project` - NEW: permission that allows to add new comments for team members of the current project 
- `comments_create_teamlead_project` - NEW: permission that allows to add new comments for a teamlead of the current project 
- `edit_teamlead_project` - removed default permission from ROLE_TEAMLEAD (if you use it: change it in the Role & Permission UI) 
- `edit_teamlead_customer` - removed default permission from ROLE_TEAMLEAD (if you use it: change it in the Role & Permission UI) 
- `upload_invoice_template` - NEW: permission that allows to upload invoice documents from the UI

## [1.7](https://github.com/kimai/kimai/releases/tag/1.7)

**New database tables and fields were created, don't forget to [run the updater](https://www.kimai.org/documentation/updates.html).**

New permissions:

- `comments_customer` - show comment list on customer detail page (new feature) 
- `details_customer` - show detail information for customers (customer number, vat, rates, meta-fields, assigned teams ...)
- `comments_project` - show comment list on project detail page (new feature) 
- `details_project` - show detail information for projects (rates, meta-fields, assigned teams ...)

If you are using teams, please read on: The following list of permissions are now also available in the UI and they (can) replace the `X_project` and `X_customer` permissions. 
They are more strict, as they allow only access to team specific items, the older permissions without `_teamlead_`/`_team_` work on a global level instead.
  
- `view_teamlead_customer`, `edit_teamlead_customer`, `budget_teamlead_customer`, `permissions_teamlead_customer`, `comments_teamlead_customer`, `details_teamlead_customer` - allows access to customer data when user is teamlead of a team assigned to the customer (replaces more global permission like `view_customer` for teamleads)
- `view_team_customer`, `edit_team_customer`, `budget_team_customer`, `comments_team_customer`, `details_team_customer` - allows access to customer data when user is member of a team assigned to the customer (replaces more global permission like `view_customer` for users)
- `view_teamlead_project`, `edit_teamlead_project`, `budget_teamlead_project`, `permissions_teamlead_project`, `comments_teamlead_project`, `details_teamlead_project` - allows access to customer data when user is teamlead of a team assigned to the project (replaces more global permission like `view_project` for teamleads)
- `view_team_project`, `edit_team_project`, `budget_team_project`, `comments_team_project`, `details_team_project` - allows access to customer data when user is member of a team assigned to the project (replaces more global permission like `view_project` for users)

### ExpenseBundle

**ATTENTION** due to incompatibilities in the underlying frameworks users of the ExpenseBundle need to do one more step:
You need to delete the bundle before updating: `rm -r var/plugins/ExpenseBundle`, otherwise you will run into errors during the update.
After the Kimai update was successful, you have to re-install the latest bundle version, which is compatible with Kimai 1.7 only. 

### Developer

- Projects now have a start and end date and the API will only return those, which are either unconfigured or currently active, you might want to reload the list of projects once the user entered begin and end datetime OR use the new `ignoreDates` parameter.
- Doctrine bundle was updated to v2, check your code for [the usage of RegistryInterface and ObjectManager](https://github.com/doctrine/DoctrineBundle/blob/master/UPGRADE-2.0.md)
- Removed the webserver bundle and the command `server:run` - see [docs](https://www.kimai.org/documentation/developers.html)

## [1.6](https://github.com/kimai/kimai/releases/tag/1.6), [1.6.1](https://github.com/kimai/kimai/releases/tag/1.6.1), [1.6.2](https://github.com/kimai/kimai/releases/tag/1.6.2)

**New database tables and fields were created, don't forget to [run the updater](https://www.kimai.org/documentation/updates.html).**

- Invoice changes:
  - Moved CSV, ODS and XSLX invoice templates to [another repository](https://github.com/Keleo/kimai2-invoice-templates). Using them? Install them manually (see [invoice documentation](https://www.kimai.org/documentation/invoices.html)).
  - Added new invoice fields (VAT, contact, payment details) and customer field (VAT). Used the twig settings before? Move them to the respective invoice template settings.
- Permissions can be managed via Admin UI. Please move your permission settings from [local.yaml to your database](https://www.kimai.org/documentation/permissions.html).
- Important permission change: regular users with the `view_other_timesheet` permission could see all timesheets. This was a legacy from the time before team permissions were introduced. If you rely on this behavior, you need to create a team with all users and the teamlead being the user who needs access to all timesheets. 

### Developer

Please add default permissions to your [plugin](https://www.kimai.org/documentation/plugins.html).

## [1.5](https://github.com/kimai/kimai/releases/tag/1.5)

[Update as usual](https://www.kimai.org/documentation/updates.html)

## [1.4](https://github.com/kimai/kimai/releases/tag/1.4)

**There is a new directory, which needs to be writable by the webserver: `public/avatars/`.**

New permission (used in new dashboard widget):
- `view_team_member` - display team assignments (names, teamleads and members) for the current user

Activated Javascript select component by default (check mobile devices).

### Developer: BC breaks

- Dashboard widgets and rows need to define their `type` by FQCN
- Switched to Symfony 4.3 event types, this could fail in plugins, but only if they didn't use the official constants for event names

## [1.3](https://github.com/kimai/kimai/releases/tag/1.3)

Added `manage_tag` permission for new tag features

### Developer: BC breaks

- Refactored toolbars and search, plugins needs to be checked 
- Invoices now supports multiple repositories, some method signatures had to be changed (eg. `calculateSumIdentifier()`)  

## [1.2](https://github.com/kimai/kimai/releases/tag/1.2)

**If you are still using 0.7 or below, you need to upgrade to 1.1 before upgrading to this version.**

- Deleted timezone conversion command. 
- Minimum password length raised from 5 to 8 character (applies only for password changes and new users)
- Maximum customer name length lowered to 150 character
- Maximum project name length lowered to 150 character
- Maximum activity name length lowered to 150 character
- Added new permission: `manage_invoice_template`
  - Removed permissions: `view_invoice_template`, `create_invoice_template`, `edit_invoice_template`, `delete_invoice_template`
- Removed permission: `view_export` (using `create_export` only)

### Developer: BC breaks

- Custom export renderer need to check for usage of `Timesheet::getEnd()` as running entries can now be exported as well

## [1.1](https://github.com/kimai/kimai/releases/tag/1.1)

[Update as usual](https://www.kimai.org/documentation/updates.html), nothing special for this release if you upgrade from 1.0 / 1.0.1.

## [1.0](https://github.com/kimai/kimai/releases/tag/1.0)

This release contains several changes, as I still have the goal to stabilize the code base to prevent 
such "challenges" after 1.0 for a while.

### Changes for your local.yaml
 
New permissions are available. You have to add them to your `local.yaml` ONLY if you use a custom permission structure, 
otherwise you can't use the new features: 
- `view_tag` - view all tags
- `delete_tag` - delete tags
- `edit_exported_timesheet` - allows to edit records which were exported
- `role_permissions` - view calculated permissions for user roles
- `budget_activity` - view and edit budgets for activities
- `budget_project` - view and edit budgets for projects
- `budget_customer` - view and edit budgets for customers

Removed permission:
- `system_actions` - removed experimental feature to flush app cache from the about screen

### BC BREAKS

- API: Format for queries including a datetime object fixed to use HTML5 format (previously `2019-03-02 14:23` - now `2019-03-02T14:23:00`)
- **Permission config**: the `permissions` definition in your `local.yaml` needs to be verified/changed, as the internal structure was highly optimized to simplify the definition. 
Thanks to the new structure, you should be able to remove almost everything from your `local.yaml` (tip: start over from scratch!). Please read [the updated permission docu](https://www.kimai.org/documentation/permissions.html). 
- default widgets were removed from `kimai.yaml`, that shouldn't cause any issues ... but if something is odd: [look here for help](https://www.kimai.org/documentation/dashboard.html)

## [0.9](https://github.com/kimai/kimai/releases/tag/0.9)

Remember to execute the necessary timezone conversion script, if you haven't updated to 0.8 before (see below)!

### BC BREAKS

This release contains some BC breaks which were necessary before 1.0 will be released (_now or never_), to prevent those BC breaks after 1.0. 

- **Kimai requires PHP 7.2 now => [PHP 7.1 expired 4 month ago](https://www.php.net/supported-versions.php)**
- The `.env` variable `DATABASE_PREFIX` was removed and the table prefix is now hardcoded to `kimai2_`. If you used another prefix, 
you have to rename your tables manually before starting the update process. You can delete the row `DATABASE_PREFIX` from your `.env` file.
- API: Format for DateTime objects changed, now including timezone identifier (previously `2019-03-02 14:23` - now `2019-03-02T14:23:00+00:00`), see [#718](https://github.com/kimai/kimai/pull/718)
- API: changed from snake_case to camelCase (affected fields: hourlyRate vs hourly_rate / fixedRate vs fixed_rate / orderNumber vs order_number / i18n config object)
- Plugin mechanism changed: existing Plugins have to be deleted or updated

### Apply necessary changes to your `local.yaml`: 

New permissions are available: 
- `system_configuration` - for accessing the new system configuration screen
- `system_actions` - for the experimental feature to flush your cache from the about screen
- `plugins` - for accessing the new plugins screen

The setting `kimai.timesheet.mode` replaces the setting `kimai.timesheet.duration_only`. If you used the duration_only mode, you need to change your config:
```yaml
# Before
kimai:
    timesheet:
        duration_only: true
        
# After
kimai:
    timesheet:
        mode: duration_only
```
Or switch the mode directly in the new System configuration screen within Kimai.  

## [0.8.1](https://github.com/kimai/kimai/releases/tag/0.8.1)

A bug fixing release. Remember to execute the necessary timezone conversion script, if you haven't updated to 0.8 before (see below)!

## [0.8](https://github.com/kimai/kimai/releases/tag/0.8)

After you followed the normal update and database migration process (see above), you need to execute a bash command to convert your timesheet data for timezone support:

- Read this [pull request](https://github.com/kimai/kimai/pull/372) BEFORE you follow the instructions to convert the 
timezones in your existing time records with `bin/console kimai:convert-timezone`. Without that, you will end up with wrong times in your database.

### Apply necessary changes to your `local.yaml`: 

- A new boolean setting `kimai.timesheet.rules.allow_future_times` was introduced
- New permissions are available: 
  - `view_export` - for the new export feature
  - `create_export` - for the new export feature
  - `edit_export_own_timesheet` - for the new export feature
  - `edit_export_other_timesheet` - for the new export feature
  - `system_information` - to see the new about screen

## [0.7](https://github.com/kimai/kimai/releases/tag/0.7)

The configuration `kimai.theme.active_warning` was deprecated and should be replaced in your local.yaml, 
[read config docs for more information](https://www.kimai.org/documentation/timesheet.html#limit-active-entries).

## [0.6.1](https://github.com/kimai/kimai/releases/tag/0.6.1)

A bugfix release to address database compatibility issues with older MySQL/MariaDB versions.

## [0.6](https://github.com/kimai/kimai/releases/tag/0.6)

The API has some minor BC breaks: some fields were renamed and entities have a larger attribute set than collections. 
Be aware that the API is still is development mode and shouldn't be considered stable for now.

## [0.5](https://github.com/kimai/kimai/releases/tag/0.5)

Some configuration nodes were removed, if you have one of them in your `local.yaml` you need to delete them before you start the update:
- `kimai.invoice.calculator`
- `kimai.invoice.renderer`
- `kimai.invoice.number_generator`

The new config `kimai.invoice.documents` was introduced, holding a list of directories ([read more](https://www.kimai.org/documentation/invoices.html)).

**BC break:** InvoiceTemplate name was changed from 255 characters to 60. If you used longer invoice-template names, they will be truncated when upgrading the database.
Please make sure that they are unique in the first 60 character before you upgrade your database with `doctrine:migrations:migrate`. 

## [0.4](https://github.com/kimai/kimai/releases/tag/0.4)

In the time between 0.3 and 0.4 there was a release of composer that introduced a BC break, 
which leads to problems between Composer and Symfony Flex, resulting in an error like this when running it:

```
  [ErrorException]
  Declaration of Symfony\Flex\ParallelDownloader::getRemoteContents($originUrl, $fileUrl, $context) should be compatible with Composer\Util\RemoteFilesystem::getRemoteContents($originUrl, $fileUrl, $context, ?array &$responseHeaders = NULL)
```

This can be fixed by updating Composer and Flex before executing the Kimai update:
```
sudo composer self-update
sudo -u www-data composer update symfony/flex --no-plugins --no-scripts
```

## [0.3](https://github.com/kimai/kimai/releases/tag/0.3)

You need to adjust your `.env` file and add your `from` address for [all emails](https://www.kimai.org/documentation/emails.html) generated by Kimai 2:
```
MAILER_FROM=kimai@example.com
```

Create a file and database backup before executing the following steps: 

```bash
git pull origin master
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data bin/console cache:clear --env=prod
sudo -u www-data bin/console cache:warmup --env=prod
bin/console doctrine:migrations:version --add 20180701120000
bin/console doctrine:migrations:migrate
```
