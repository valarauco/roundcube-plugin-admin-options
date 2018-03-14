Roundcube Plugin: admin_options
===============================

Extendable plugin for Roundcube Webmail to create administrative options.

This plugin will only be shown to users if they are part of the configured admin users and it adds the ability for devs to extend its functionality with other plugins through hooks.

Users with access to admin-options can see a list of other hooked "admin plugins" and used them from settings tab. For example: other plugins can create webui options to control installed/activated plugins, install new skins, change config options, create popup notifications, and so on.

Stable versions are available from the [Roundcube plugin repository][rcplugin] or from the [releases section][releases] on GitHub repository.

Composer Installation
----------------------------------------

Add the plugin to your "require" section in `composer.json` file:

    "require": {
        (...)
        "valarauco/admin_options": "~0.1"
    }

And run `$ php composer.phar install`.

Manual Installation
----------------------------------------

Place this directory under your Rouncdube `plugins/` folder and rename it as `admin_options`, copy `config.inc.php.dist` to `config.inc.php` and modify it as necessary.

If you are using git, change your working directory to your Rouncdube `plugins/` folder and clone the repository:

    $ git clone git@github.com:valarauco/roundcube-plugin-admin-options.git admin-options

Then you can checkout the branch or release you want to use.

:bangbang: Don't forget to enable the plugin in Roundcube configuration file `config/config.inc.php`.

Configuration
----------------------------------------

- **$config['admin_options_users']** - array with the list of usernames allowed to access the admin options.

See the `config.inc.php.dist` for more information.

admin_options_list hook
-----------------------
This hook is triggered when the admin_options are loaded, right after admin_options task is selected from settings and is used to register the list of admin options available in the section list
Arguments/Return:
* list - (array) List of available admin options (sections), each item must register a named key and contain an id and a section name as it should be shown in the UI.
** key - key for the admin option in the list (section name)
** id - (string) machine name for the admin option
** section - (string) printable section name

```php
$args['list']['notifications'] = array(
    'id' => 'notifications',
    'section' => rcube::Q($this->gettext('notifications'))
);
```

admin_options_data hook
-----------------------
This hook is triggered when an admin option is selected and is used to load option's data to the form section, this data can be saved when form is submitted. Plugins should verify if `section` corresponds to its section name.
Arguments/Return:
* section - (string) section name to be check, if section name doesn't match, plugin should not do anything.
* header - (string, optional) header for the option's form
* blocks - (array) List of blocks to be rendered in the option's form, each block should have a key and contain an array with the following structure:
** name - (string, optional) Block’s name to be rendered as a fieldset
** options - (array) if array structure contains `options` key it will be rendered as a table with 2 columns as follows:
*** title - (string) title column
*** content - (string) content column with plain html
** content - (string) if array structure doesn't contain `options` key but contains the key `content` it will show this as plain html inside the fieldset.

```php
$args['blocks']['id_notif'] = array('content' => html::tag('input', array('name' => '_id','type'  => 'hidden','value' => $notificacion['id'])));
```
```php
$args['blocks']['create_notif'] = array('options' => array(),
    'name' => rcube::Q($this->gettext('notifications_create'))
);
$args['blocks']['create_notif']['options'][] = array(
    'title' => rcube::Q($this->gettext('notification_isactive')),
    'content' => html::tag('input', $chbox_atrib)
);
```

admin_options_header hook
-----------------------
This hook is triggered when an admin option is selected and is used to change option's header in the form section. Plugins should verify if `section` corresponds to its section name.
Similar to the header option in admin_options_data, this overrides data header.
Arguments/Return:
* section - (string) section name to be check, if section name doesn't match, plugin should not do anything.
* header - (string) header for the option's form

admin_options_save hook
-----------------------
This hook is triggered when an admin option form is submitted and is used to save changes in option's data form. Plugins should verify if `section` corresponds to its section name and `abort` flag is not `true`.
Arguments/Return:
* section - (string) section name to be check, if section name doesn't match, plugin should not do anything.
* abort - (bool) stop-processing flag

License
----------------------------------------

This plugin is released under the [GNU General Public License Version 3+][gpl].

Contact
----------------------------------------

Comments and suggestions are welcome!

Please, feel free to open an issue if necessary on the [issues section][issues] on the Github repo

author: Manuel Delgado (ValaRaucO)

[rcplugin]: https://plugins.roundcube.net/packages/valarauco/admin_options
[releases]: https://github.com/valarauco/roundcube-plugin-admin-options/releases
[issues]: https://github.com/valarauco/roundcube-plugin-admin-options/issues
[gpl]: https://www.gnu.org/licenses/gpl.html
