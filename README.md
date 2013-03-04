This repository is the code for [Babelium's][] Moodle plugins.

[Babelium's]: http://babeliumproject.com

Here you will find the latest versions of the plugins. Sometimes it takes a while for the plugins to be added
to the official Moodle plugin repository so if you want to test the latest features this is the way to go.

The plugin is currently available in 3 languages: English, Spanish and Basque.

Installing the Mooodle 1.9 plugin
---------------------------------

Export the contents of the `common` and `moodle19` folders to Moodle's home directory. If you exported the folders
in the right place you should see some files under

<moodle_home>/mod/assignment/type/babelium

Rename or copy the `babelium_config.php.template` to `babelium_config.php` and fill the data with your Moodle site
registration data.

**NOTE:** If you are using your own Babelium server see below for further clarification on how to enable Moodle
site support on your Babelium server.

###Enabling the plugin
After copying the files and filling in the `babelium_config.php` file login with an admin account to your Moodle
site. Moodle should automatically detect that a new plugin is being added and prompt you for actions to take.
If this is not the case, browse to the following URL to force the plugin installation page to appear:

http://<moodle_domain>/admin

###Create a sample Babelium assignment
Choose a random course and add a new sample Babelium assignment choosing

Add an activity... -> Assignments -> Babelium activity

###Troubleshooting
* Check the lenghts of the key-set you were given. The access key should be 20 characters long and the private access
key should be 40 characters long. If the lengths are wrong please contact us at support@babeliumproject.com

* Check the time of your server against a public time server. The timestamp of the requests to the Babelium server
is checked to minimize request replication and we drop the requests that are too skewed. Different timezones are
supported but you should be within a +/- 15 minute boundary relative to the actual time.

If you have other errors, or don't know how to proceed, please don't hesitate to contact us at support@babeliumproject.com
describing your problem. Please also provide a copy of your `babelium.log` file (placed in the root of your Moodle site's `moodledata` folder) 
in the email. That way, we can have a more detailed view of the problem.

Installing the Mooodle 2.0-2.2 plugin
-------------------------------------

Export the contents of the `common` and `moodle20-22` folders to Moodle's home directory. If you exported the folders
in the right place you should see some files under

<moodle_home>/mod/assignment/type/babelium

Rename or copy the `babelium_config.php.template` to `babelium_config.php` and fill the data with your Moodle site
registration data.

**NOTE:** If you are using your own Babelium server see below for further clarification on how to enable Moodle
site support on your Babelium server.

###Enabling the plugin
After copying the files and filling in the `babelium_config.php` file login with an admin account to your Moodle
site. Moodle should automatically detect that a new plugin is being added and prompt you for actions to take.
If this is not the case, browse to the following URL to force the plugin installation page to appear:

http://<moodle_domain>/admin

###Troubleshooting
* Check the lenghts of the key-set you were given. The access key should be 20 characters long and the private access
key should be 40 characters long. If the lengths are wrong please contact us at support@babeliumproject.com

* Check the time of your server against a public time server. The timestamp of the requests to the Babelium server
is checked to minimize request replication and we drop the requests that are too skewed. Different timezones are
supported but you should be within a +/- 15 minute boundary relative to the actual time.

If you have other errors, or don't know how to proceed, please don't hesitate to contact us at support@babeliumproject.com
describing your problem. Please also provide a copy of your `babelium.log` file (placed in the root of your Moodle site's `moodledata` folder) 
in the email. That way, we can have a more detailed view of the problem.

Installing the Moodle 2.3+ plugin
---------------------------------

**NOTE:** Also works for Moodle 2.4.

Since Moodle 2.3 `assignment_type` plugins are deprecated and replaced by `assign_submission` or
`assign_feedback` plugins.

Export the files from the `moodle23` to Moodle's home directory. If you exported the folders
in the right place you should see some files under

<moodle_home>/mod/assign/submission/babelium

###Enabling the plugin
Log in with an admin account to your Moodle site. Moodle should automatically detect that a new plugin
is being added and prompt you for actions to take. If this is not the case, browse to the following URL 
to force the plugin installation page to appear:

http://<moodle_domain>/admin

After successfully installing the plugin you will see a settings page where you should input the key-pair
provided in your moodle site registration step.

**NOTE:** If you are using your own Babelium server see below for further clarification on how to enable Moodle
site support on your Babelium server.

###Create a sample Babelium assignment
Choose a random course and add a new sample Babelium assignment choosing

Add an activity or resource -> Assignments

In the form that is displayed you can see Babelium alongside File and Text submissions. This means you can have
man kinds of submissions for the same assignment.

###Upgrading from previous versions
If you have the 1.9 or 2.0-2.2 version of Babelium's Moodle plugin installed you can upgrade all your
assignment data to the new architecture to avoid the deprecated modules. Simply install Babelium's Moodle
2.3 plugin, follow the configuration steps and when you are done, use the built-in migration wizard.

###Troubleshooting
* Check the lenghts of the key-set you were given. The access key should be 20 characters long and the private access
key should be 40 characters long. If the lengths are wrong please contact us at support@babeliumproject.com

* Check the time of your server against a public time server. The timestamp of the requests to the Babelium server
is checked to minimize request replication and we drop the requests that are too skewed. Different timezones are
supported but you should be within a +/- 15 minute boundary relative to the actual time.

If you have other errors, or don't know how to proceed, please don't hesitate to contact us at support@babeliumproject.com
describing your problem. Please also provide a copy of your `babelium.log` file (placed in the root of your Moodle site's `moodledata` folder) 
in the email. That way, we can have a more detailed view of the problem.

Patching the Babelium server to give access to Moodle users
-----------------------------------------------------------
* Copy the folders `api`, `css` and the file `moodleapi.php` to your Babelium server's home directory.
* Copy the scripts of the `script` folder to your Babelium server's script folder.
* Copy `media\placeholder_merge.flv` to the root folder of your Streaming server's webapp. 
  For example, if you use Red5 and work with the vod application, you could place the file in:

<red5_home>/webapps/vod/streams/placeholder_merge.flv

* Apply the provided SQL patch against your Babelium server database to enable Moodle site registration.

* Copy the Config.php file from <babelium_home>/services/utils to <babelium_home>/api/services/utils and make
  the appropriate path changes (if necessary).

* Also check the paths at the beginning of <babelium_home>/api/services/utils/VideoProcessor.php are correct.

###Troubleshooting
If you need help configuring the server or find any bug please don't hesitate to contact us at suppor@babeliumproject.com


