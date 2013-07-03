This repository is the code for [Babelium][]'s Moodle plug-ins.

[Babelium]: http://babeliumproject.com

Here you will find the latest versions of the plug-ins. Sometimes it takes a while for the plug-ins. to be added to the official Moodle plug-in repository so if you want to test the latest features this is the way to go.

The plug-in is currently available in 3 languages: English, Spanish and Basque.

Cloning the repository
----------------------
To run the development version of Babelium first clone the git repository.

	$ git clone git://github.com/babeliumproject/moodle-assignsubmission_babelium.git babelium-moodle-plugins

Now the entire project should be in the `babelium-moodle-plugins/` directory.

Installing the Moodle 1.9 plug-ins.
----------------------------------
Copy the `common` and `moodle19/mod` folders to Moodle's home directory.
	
	$ cd babelium-moodle-plugins
	$ cp -r common/* <moodle_directory>
	$ cp -r moodle19/mod/* <moodle_directory>/mod

If you exported the folders in the right place you should see some files listed here:

	$ ls <moodle_directory>/mod/assignment/type/babelium

Next is providing the Moodle site registration data. There are two ways of doing this:

* Rename or copy the `babelium_config.php.template` to `babelium_config.php` and fill the data with your Moodle site registration data.

	```
	$ cp <moodle_directory>/mod/assignment/type/babelium/babelium_config.php.template <moodle_directory>/mod/assignment/type/babelium/babelium_config.php
	$ vi <moodle_directory>/mod/assignment/type/babelium/babelium_config.php
	```

* Install the **Babelium filter plug-in** and fill the data later, using the provided UI menu in (Site Administration -&gt; Modules -&gt; Filter -&gt; Manage Filters -&gt; Babelium):

	```
	$ cd babelium-moodle-plugins
	$ cp -r moodle19/filter/* <moodle_directory>/filter
	```

  If you exported the folders in the right place you should see some files listed here:
	
	```
	$ ls <moodle_directory>/filter/babelium
	```

**NOTE:** If you are using your own Babelium server see below for further clarification on how to enable Moodle site support on your Babelium server.

###Enabling the plug-in
After you are done with the previous steps, log in with an admin account into your Moodle site. Moodle should automatically detect that a new plug-in is being added and prompt you for actions to take. If this is not the case, browse to the following URL to force the plug-in installation page to appear:

	http://<moodle_domain>/admin

If you also installed the **filter plug-in**, the filter settings page will automatically appear after the installation is done. Fill the settings with the information you got when registering your Moodle site in [Babelium] and click on **Save changes**. You can change the settings of the filter by visiting `Site Administration -> Modules -> Filters -> Manage Filters -> Babelium` using an admin account.

###Create a sample Babelium assignment
Choose a random course and add a new sample Babelium assignment choosing

	Add an activity... -> Assignments -> Babelium activity

###Troubleshooting
See the section below.

Installing the Moodle 2.0-2.2 plug-ins.
--------------------------------------
Copy the `common` and `moodle20-22/mod` folders to Moodle's home directory.
	
	$ cd babelium-moodle-plugins
	$ cp -r common/* <moodle_directory>/
	$ cp -r moodle20-22/mod/* <moodle_directory>/mod

If you exported the folders in the right place you should see some files listed here:

	$ ls <moodle_directory>/mod/assignment/type/babelium

Next is providing the Moodle site registration data. There are two ways of doing this:

* Rename or copy the `babelium_config.php.template` to `babelium_config.php` and fill the data with your Moodle site registration data.

	```
	$ cp <moodle_directory>/mod/assignment/type/babelium/babelium_config.php.template <moodle_directory>/mod/assignment/type/babelium/babelium_config.php
	$ vi <moodle_directory>/mod/assignment/type/babelium/babelium_config.php
	```

* Install the Babelium filter plug-in and fill the data later, using the provided UI menu in (Site Administration -&gt; Plugins -&gt; Filter -&gt; Manage Filters -&gt; Babelium):

	```
	$ cd babelium-moodle-plugins
	$ cp -r moodle20-22/filter/* <moodle_directory>/filter
	```

  If you exported the folders in the right place you should see some files listed here:
	
	```
	$ ls <moodle_directory>/filter/babelium
	```

**NOTE:** If you are using your own Babelium server see below for further clarification on how to enable Moodle site support on your Babelium server.

###Enabling the plug-in
After you are done with the previous steps, log in with an admin account into your Moodle site. Moodle should automatically detect that a new plug-in is being added and prompt you for actions to take. If this is not the case, browse to the following URL to force the plug-in installation page to appear:

	http://<moodle_domain>/admin

If you also installed the **filter plug-in**, the filter settings page will automatically appear after the installation is done. Fill the settings with the information you got when registering your Moodle site in [Babelium] and click on **Save changes**. You can change the settings of the filter by visiting `Site Administration -> Plugins -> Filters -> Manage Filters -> Babelium` using an admin account.

###Troubleshooting
See the section below.

Installing the Moodle 2.3+ plug-in
---------------------------------

**NOTE:** Also works for Moodle 2.4.

From Moodle 2.3 onwards, `assignment_type` plug-ins. are deprecated and replaced by `assign_submission` or `assign_feedback` plug-ins.

Copy the files from the `moodle23` to Moodle's home directory. 

	$ cd babelium-moodle-plugins
	$ cp -r moodle23/* <moodle_directory>/

If you copied the folders in the right place you should see some files here:

	$ ls <moodle_directory>/mod/assign/submission/babelium

###Enabling the plug-in
Log in with an admin account to your Moodle site. Moodle should automatically detect that a new plug-in is being added and prompt you for actions to take. If this is not the case, browse to the following URL to force the plug-in installation page to appear:

	http://<moodle_domain>/admin

After successfully installing the plug-in you will see a settings page where you should input the key-pair provided in your Moodle site registration step.

**NOTE:** If you are using your own Babelium server see below for further clarification on how to enable Moodle site support on your Babelium server.

###Create a sample Babelium assignment
Choose a random course and add a new sample Babelium assignment choosing

	Add an activity or resource -> Assignments

In the form that is displayed you can see Babelium alongside File and Text submissions. This means you can have man kinds of submissions for the same assignment.

###Upgrading from previous versions
If you have the 1.9 or 2.0-2.2 version of Babelium's Moodle plug-in installed you can upgrade all your assignment data to the new architecture to avoid the deprecated modules. Simply install Babelium's Moodle 2.3 plug-in, follow the configuration steps and when you are done, use the built-in migration wizard. Check Moodle's documentation if you don't know how to proceed.

###Troubleshooting
See the section below.

Enabling Moodle support in the Babelium server
--------------------------------------------
If you are using your own Babelium server and want to enable Moodle instances to access the exercises stored there, you have to take additional steps, such as placing some API files and compiling a special version of the video player.

Copy the Moodle API files and the Moodle site registration script:

	$ cd babelium-moodle-plugins/patches
	$ cp -r api <babelium_home>/
	$ cp -r css <babelium_home>/
	$ cp moodleapi.php <babelium_home>/

Copy the video merging script to the Babelium script folder:

	$ cd babelium-moodle-plugins/patches
	$ cp script/* <babelium_script_directory>/

Copy the placeholder video that will be displayed while the videos are still not merged:

	$ cd babelium-moodle-plugins/patches/media
	$ cp placeholder_merge.flv <red5_home>/webapps/vod/streams

[Inkscape]: http://inkscape.org/

**NOTE:** The provided placeholder video file displays a message in Basque, Spanish and English (the message reads "Your video is still processing. Sorry for the inconvenience."). If you wish to display another message or want to display a message in some other language you can make your own placeholde video. For that purpose, we provide the original vectorial image (the video loop source) of placeholder_merge.flv in `babelium-moodle-plugins/patches/media/placeholder_merge.svg`. You can edit this file using the open source application Inkscape. When you are done editing, you have to export the SVG file as PNG (File -> Export Bitmap...) to use it as an input for ffmpeg. The following ffmpeg command creates a 20 second video using the PNG image we exported as loop source:

	$ ffmpeg -y -loop 1 -f image2 -i placeholder_merge.png -ar 22050 -f s16le -i /dev/zero -r 25 -t 25 -s 426x240 -g 25 -qmin 3 -b:v 512k placeholder_merge.flv

Apply the provided SQL patch to enable Moodle site registration

	$ mysql -u <babeliumdbuser> -p
	> use <babeliumdbname>;
	> source babelium-moodle-plugins/patches/sql/moodle_patch.sql;

Fill the data of `<babelium_home>/api/services/utils/Config.php` (or copy from `<babelium_home>/services/utils/Config.php`) and check if the paths in `<babelium_home>/api/services/utils/VideoProcessor.php` are right.

Copy the standalone video player to the Babelium home directory (the embeddable player is available in other repository, please read below).

	$ cd babelium-flex-embeddable-player/dist
	$ cp babeliumPlayer.* <babelium_home>/

**NOTE:** If you need help compiling the standalone player read the next point.

Following these steps you should be able to register Moodle instances in your Babelium server.

###Compile the standalone video player
Babelium needs a special version of the video player to support embedding in Moodle and other systems. These are the steps you need to take to compile the standalone player from the source code.

Download and unpack Flex SDK 4.6

	$ wget http://download.macromedia.com/pub/flex/sdk/flex_sdk_4.6.zip
	$ unzip flex_sdk_4.6.zip

Make a locale for Basque language (because it is not included by default):

	$ cd <flex_home>/bin
	$ ./copylocale en_US eu_ES

Fill the build.properties file for the standalone player (assuming you already cloned the git repository):

	$ cd babelium-flex-embeddable-player
	$ cp build.properties.template build.properties
	$ vi build.properties

Specify the home folder of the Flex SDK **FLEX_HOME** and the local path of the repository **BASE**. Leave the rest of the fields unchanged (we don't need them for this purpose).

	$ ant

The compiled files are placed in the `dist` folder.

Copy the standalone video player to the Babelium home directory.

	$ cd babelium-flex-embeddable-player/dist
	$ cp babeliumPlayer.* <babelium_home>/

Finally, don't forget to update your Apache's configuration file by adding the following line:

	Header set Access-Control-Allow-Origin "*"

This will allow your embeddable-player to use remote JavaScript calls, even if the called scripts are located 
on a a different server.

**NOTE:** using "*" means you give access to any host and that could lead to some attacks. We use this wildcard because in our demo server we let users from any domain to sign-up for a Moodle API key, and thus, can't determine the origin beforehand. If you are part of an institution you can limit the access control to your domains to have less security risks.

Troubleshooting & technical support
-----------------------------------
These are some common errors you might find and how to go about them.

###How to contact technical support
If you have other errors, or don't know how to proceed, please don't hesitate to contact us at support@babeliumproject.com describing your problem. Please provide the following data in your e-mail so that we can give you a better answer:
* A copy of your `babelium.log` file (placed in the root of your Moodle site's `moodledata` folder)
* Version of your browser. You can usually find it in the Help or About areas (e.g. __Mozilla Firefox 19.0.2__)
* Version of Flash Player Plugin. You can find it in the `about:plug-ins.` section of your browser (e.g. __Shockwave Flash 11.6 r602 PPAPI (out-of-process)__)
* Moodle version. You can find it in `<moodle_directory>/version.php` (e.g. `$release  = '2.2.7+ (Build: 20130222)'`)
* The Babelium Moodle plug-in version. You can find it in `<moodle_directory>/mod/assignment/type/babelium/version.php` or `<moodle_directory>/mod/assign/submissions/babelium/version.php` (e.g. `$plug-in->release = '0.9.6 (Build: 2012090600)'`);
* The Babelium username you used to register your Moodle site in the system


###Babelium Error 403. Wrong authorization credentials
* Check the lengths of the key-set you were given. The access key should be 20 characters long and the private access key should be 40 characters long. If the lengths are wrong please contact us to get a new set of keys.

* Check the time of your server against a public time server. The timestamp of the requests to the Babelium server is checked to minimize request replication and we drop the requests that are too skewed. Different timezones are supported but you should be within a +/- 15 minute boundary relative to the actual time.

* If you your key-set and server time is correct perhaps you have a problem with the domain of your Moodle site. Please contact us so that we can analyze the problem.

###Babelium Error 400. Malformed request error
* If you are using your own Babelium server, take a look at the log file of ZendRestJson.php (by default it is placed in /tmp/moodle.log) to see if you have a permission issue in your Babelium file system.

* If you are using babeliumproject.com please contact us stating your problem, we might have some kind of issue in our own server.

###Babelium Error 500. Internal server error
* Very unlikely to occur. Could happen when the Babelium server uses an old version of PHP (&lt; PHP 5.0)

###How to completely uninstall Babelium plug-ins.
This is useful when the plug-in isn't correctly installed. With these steps you can completely remove the Babelium plug-in, in order to do a fresh installation.

Remove all the plug-in files and folders (they should be in one of the following locations):

```sh
rm -r <moodle_home>/mod/assignment/type/babelium
rm -r <moodle_home>/filter/babelium

rm -r <moodle_home>/mod/assign/submission/babelium
```

Now, in your Moodle database, check if there is a table called `mdl_assignsubmission_babelium`, if so, drop that table.

```sql
DROP TABLE mdl_assignsubmission_babelium;
```

Also check in the `mdl_config_plug-in` table for any values related to babelium and delete them:

```sql
DELETE FROM mdl_config_plug-in WHERE plug-in='assignsubmission_babelium';
```

Clean the `mdl_config` table of any Babelium filter plug-in values (if you installed the filter plug-in):

```sql
DELETE FROM mdl_config WHERE name LIKE 'filter_babelium%';
```

If `mdl_config` has a row called `textfilters` (this row tells Moodle 1.9 which Filter plug-ins. are enabled) remove that part of the field that belongs to Babelium (the enabled filters are comma-separated):

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
DELETE FROM mdl_upgrade_log WHERE plug-in='assignment_babelium' OR plug-in='assignsubmission_babelium';
```

With that, we should have removed all the traces of the failed plug-in install. Please refer to the previous sections to do a fresh install of your plug-in.



