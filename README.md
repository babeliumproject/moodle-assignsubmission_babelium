This repository is the code for [Babelium's][] Moodle plugins.

[Babelium's]: http://babeliumproject.com

Here you will find the latest versions of the plugins. Sometimes it takes a while for the plugins to be added to the official Moodle plugin repository so if you want to test the latest features this is the way to go.

The plugin is currently available in 3 languages: English, Spanish and Basque.

Cloning the repository
----------------------
To run the development version of Babelium first clone the git repository.

	$ git clone git://github.com/moodle-plugins/moodle-plugins.git babelium-moodle-plugins

Now the entire project should be in the `babelium-moodle-plugins/` directory.

Installing the Mooodle 1.9 plugins
----------------------------------
Copy the `common` and `moodle19/mod` folders to Moodle's home directory.
	
	$ cd babelium-moodle-plugins
	$ cp -r common/* <moodle_directory>
	$ cp -r moodle19/mod/* <moodle_directory>/mod

If you exported the folders in the right place you should see some files listed here:

	$ ls <moodle_directory>/mod/assignment/type/babelium

Next is providing the Moodle site registration data. There are two ways of doing this:

* Rename or copy the `babelium_config.php.template` to `babelium_config.php` and fill the data with your Moodle site registration data.

	```sh
	$ cp <moodle_directory>/mod/assignment/type/babelium/babelium_config.php.template <moodle_directory>/mod/assignment/type/babelium/babelium_config.php
	$ vi <moodle_directory>/mod/assignment/type/babelium/babelium_config.php
	```

* Install the Babelium filter plugin and fill the data later, using the provided UI menu in (Site Administration -&gt; Modules -&gt; Filter -&gt; Manage Filters -&gt; Babelium):

	```sh
	$ cd babelium-moodle-plugins
	$ cp -r moodle19/filter/* <moodle_directory>/filter
	```

  If you exported the folders in the right place you should see some files listed here:
	
	```sh
	$ ls <moodle_directory>/filter/babelium
	```

**NOTE:** If you are using your own Babelium server see below for further clarification on how to enable Moodle site support on your Babelium server.

###Enabling the plugin
After you are done with the previous steps, log in with an admin account into your Moodle site. Moodle should automatically detect that a new plugin is being added and prompt you for actions to take. If this is not the case, browse to the following URL to force the plugin installation page to appear:

	<http://&lt;moodle_domain&gt;/admin>

If you also installed the filter plugin, the filter settings page will automatically appear after the installation is done. Fill the settings with the information you got when registering your Moodle site in babeliumproject.com and click on 'Save changes'. You can change the settings of the filter by visiting "Site Administration -&gt; Modules -&gt; Filters -&gt; Manage Filters -&gt; Babelium" using an admin account.

###Create a sample Babelium assignment
Choose a random course and add a new sample Babelium assignment choosing

	Add an activity... -> Assignments -> Babelium activity

###Troubleshooting
See the section below.

Installing the Mooodle 2.0-2.2 plugins
--------------------------------------
Copy the `common` and `moodle20-22/mod` folders to Moodle's home directory.
	
	$ cd babelium-moodle-plugins
	$ cp -r common/* <moodle_directory>/
	$ cp -r moodle20-22/* <moodle_directory>/

If you exported the folders in the right place you should see some files listed here:

	$ ls <moodle_directory>/mod/assignment/type/babelium

Next is providing the Moodle site registration data. There are two ways of doing this:

* Rename or copy the `babelium_config.php.template` to `babelium_config.php` and fill the data with your Moodle site registration data.

	```sh
	$ cp <moodle_directory>/mod/assignment/type/babelium/babelium_config.php.template <moodle_directory>/mod/assignment/type/babelium/babelium_config.php
	$ vi <moodle_directory>/mod/assignment/type/babelium/babelium_config.php
	```

* Install the Babelium filter plugin and fill the data later, using the provided UI menu in (Site Administration -&gt; Plugins -&gt; Filter -&gt; Manage Filters -&gt; Babelium):

	```sh
	$ cd babelium-moodle-plugins
	$ cp -r moodle20-22/filter/* <moodle_directory>/filter
	```

  If you exported the folders in the right place you should see some files listed here:
	
	```sh
	$ ls <moodle_directory>/filter/babelium
	```

**NOTE:** If you are using your own Babelium server see below for further clarification on how to enable Moodle site support on your Babelium server.

###Enabling the plugin
After you are done with the previous steps, log in with an admin account into your Moodle site. Moodle should automatically detect that a new plugin is being added and prompt you for actions to take. If this is not the case, browse to the following URL to force the plugin installation page to appear:

	<http://&lt;moodle_domain&gt;/admin>

If you also installed the filter plugin, the filter settings page will automatically appear after the installation is done. Fill the settings with the information you got when registering your Moodle site in babeliumproject.com and click on 'Save changes'. You can change the settings of the filter by visiting "Site Administration -&gt; Plugins -&gt; Filters -&gt; Manage Filters -&gt; Babelium" using an admin account.

###Troubleshooting
See the section below.

Installing the Moodle 2.3+ plugin
---------------------------------

**NOTE:** Also works for Moodle 2.4.

From Moodle 2.3 onwards, `assignment_type` plugins are deprecated and replaced by `assign_submission` or `assign_feedback` plugins.

Copy the files from the `moodle23` to Moodle's home directory. 

	$ cd babeliumproject/moodle
	$ cp -r moodle23/* <moodle_directory>/

If you copied the folders in the right place you should see some files here:

	$ ls <moodle_directory>/mod/assign/submission/babelium

###Enabling the plugin
Log in with an admin account to your Moodle site. Moodle should automatically detect that a new plugin is being added and prompt you for actions to take. If this is not the case, browse to the following URL to force the plugin installation page to appear:

	http://<moodle_domain>/admin

After successfully installing the plugin you will see a settings page where you should input the key-pair provided in your moodle site registration step.

**NOTE:** If you are using your own Babelium server see below for further clarification on how to enable Moodle site support on your Babelium server.

###Create a sample Babelium assignment
Choose a random course and add a new sample Babelium assignment choosing

	Add an activity or resource -> Assignments

In the form that is displayed you can see Babelium alongside File and Text submissions. This means you can have man kinds of submissions for the same assignment.

###Upgrading from previous versions
If you have the 1.9 or 2.0-2.2 version of Babelium's Moodle plugin installed you can upgrade all your assignment data to the new architecture to avoid the deprecated modules. Simply install Babelium's Moodle 2.3 plugin, follow the configuration steps and when you are done, use the built-in migration wizard. Check Moodle's documentation if you don't know how to proceed.

###Troubleshooting
See the section below.

Enabling Moodle support in the Babelium server
--------------------------------------------
If you are using your own Babelium server and want to enable Moodle instances to access the exercises stored there, you have to take additional steps, such as placing some API files and compiling a special version of the video player.

Copy the Moodle API files and the Moodle site registration script:

	$ cd babeliumproject/moodle/patches
	$ cp -r api <babelium_home>/
	$ cp -r css <babelium_home>/
	$ cp moodleapi.php <babelium_directory>/

Copy the video merging script to the Babelium script folder:

	$ cd babeliumproject/moodle/patches
	$ cp script/* <babelium_script_directory>/

Copy the placeholder video to display while the videos are still not merged:

	$ cd babeliumproject/moodle/patches/media
	$ cp placeholder_merge.flv <red5_home>/webapps/vod/streams

Apply the provided SQL patch to enable Moodle site registration

	$ mysql -u <babeliumdbuser> -p
	> use <babeliumdbname>;
	> source babeliumproject/moodle/patches/sql/moodle_patch.sql;

Fill the data of `<babelium_home>/api/services/utils/Config.php` (or copy from `<babelium_home>/services/utils/Config.php`) and check if the paths in `<babelium_home>/api/services/utils/VideoProcessor.php` are right.

Copy the standalone video player to the Babelium home directory.

	$ cd babeliumproject/flex/standalone/player/dist
	$ cp babeliumPlayer.* <babelium_directory>/

**NOTE:** If you need help compiling the standalone player read the next point.

Following these steps you should be able to register Moodle instances in your Babelium server.

###Compile the standalone video player
Babelium needs a special version of the video player to support embeding in Moodle and other systems. These are the steps you need to take to compile the standalone player from the source code.

Download and unpack Flex SDK 4.6

	$ wget http://download.macromedia.com/pub/flex/sdk/flex_sdk_4.6.zip
	$ unzip flex_sdk_4.6.zip

Make a locale for Basque language (because it is not included by default):

	$ cd <flex_home>/bin
	$ ./copylocale en_US eu_ES

Fill the build.properties file for the standalone player (assuming you already clone the git repository):

	$ cd babeliumproject/flex/embeddable/player
	$ cp build.properties.template build.properties
	$ vi build.properties

Specify the home folder of Flex SDK FLEX_HOME and leave the rest of the fields unchanged (we don't need them for this purpose).

	$ ant

The compiled files are placed in the `dist` folder.

Copy the standalone video player to the Babelium home directory.

	$ cd babeliumproject/flex/standalone/player/dist
	$ cp babeliumPlayer.* <babelium_directory>/

Troubleshooting & technical support
-----------------------------------
These are some common errors you might find and how to go about them.

###How to contact technical support
If you have other errors, or don't know how to proceed, please don't hesitate to contact us at support@babeliumproject.com describing your problem. Please provide the following data in your e-mail so that we can give you a better answer:
* A copy of your `babelium.log` file (placed in the root of your Moodle site's `moodledata` folder)
* Version of your browser. You can usually find it in the Help or About areas (e.g. __Mozilla Firefox 19.0.2__)
* Version of Flash Player Plugin. You can find it in the `about:plugins` section of your browser (e.g. __Shockwave Flash 11.6 r602 PPAPI (out-of-process)__)
* Moodle version. You can find it in `<moodle_directory>/version.php` (e.g. `$release  = '2.2.7+ (Build: 20130222)'`)
* The Babelium Moodle plugin version. You can find it in `<moodle_directory>/mod/assignment/type/babelium/version.php` or `<moodle_directory>/mod/assign/submissions/babelium/version.php` (e.g. `$plugin->release = '0.9.6 (Build: 2012090600)'`);
* The Babelium username you used to register your Moodle site in the system


###Babelium Error 403. Wrong authorization credentials
* Check the lenghts of the key-set you were given. The access key should be 20 characters long and the private access key should be 40 characters long. If the lengths are wrong please contact us to get a new set of keys.

* Check the time of your server against a public time server. The timestamp of the requests to the Babelium server is checked to minimize request replication and we drop the requests that are too skewed. Different timezones are supported but you should be within a +/- 15 minute boundary relative to the actual time.

* If you your key-set and server time is correct perhaps you have a problem with the domain of your moodle site. Please contact us so that we can analyze the problem.

###Babelium Error 400. Malformed request error
* If you are using your own Babelium server, take a look at the log file of ZendRestJson.php (by default it is placed in /tmp/moodle.log) to see if you have a permission issue in your Babelium filesystem.

* If you are using babeliumproject.com please contact us stating your problem, we might have some kind of issue in our own server.

###Babelium Error 500. Internal server error
* Very unlikely to occur. Could happen when the Babelium server uses an old version of PHP (&lt; PHP 5.0)

###How to completely uninstall Babelium plugins
This is useful when the plugin isn't correctly installed. With these steps you can completely remove the Babelium plugin, in order to do a fresh installation.

Remove all the plugin files and folders (they should be in one of the following locations):

```sh
rm -r <moodle_home>/mod/assignment/type/babelium
rm -r <moodle_home>/filter/babelium

rm -r <moodle_home>/mod/assign/submission/babelium
```

Now, in your moodle database, check if there is a table called `mdl_assignsubmission_babelium`, if so, drop that table.

```sql
DROP TABLE mdl_assignsubmission_babelium;
```

Also check in the `mdl_config_plugin` table for any values related to babelium and delete them:

```sql
DELETE FROM mdl_config_plugin WHERE plugin='assignsubmission_babelium';
```

Clean the `mdl_config` table of any Babelium filter plugin values (if you installed the filter plugin):

```sql
DELETE FROM mdl_config WHERE name LIKE 'filter_babelium%';
```

If `mdl_config` has a row called `textfilters` (this row tells Moodle 1.9 which Filter plugins are enabled) remove that part of the field that belongs to Babelium (the enabled filters are comma-separated):

```sql
SELECT value FROM mdl_config WHERE name='textfilters' AND value LIKE '%babelium%';
```

If you are in Moodle 2.0-2.2 `mdl_filter_active` and `mdl_filter_config` tables might contain entries related to Babelium. If so delete them:

```sql
DELETE FROM mdl_filter_active WHERE filter LIKE '%babelium%';

DELETE FROM mdl_filter_config WHERE filter LIKE '%babelium%';
```

Let's also clean any trace left in the `mdl_upgrade_log`:

```sql
DELETE FROM mdl_upgrade_log WHERE plugin='assignment_babelium' OR plugin='assignsubmission_babelium';
```

With that, we should have removed all the traces of the failed plugin install. Please refer to the previous sections to do a fresh install of your plugin.



