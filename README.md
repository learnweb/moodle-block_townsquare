[![Moodle Plugin CI](https://github.com/learnweb/moodle-block_townsquare/workflows/Moodle%20Plugin%20CI/badge.svg?branch=main)](https://github.com/learnweb/moodle-block_townsquare/actions?query=workflow%3A%22Moodle+Plugin+CI%22+branch%3Amain)
# Town Square #

A moodle block for the dashboard. This Plugin shows current events and forum posts in courses the user is enrolled.

## Description ##

This plugin shows different notifications (e.g. deadlines), activity completion and forum posts
from all courses an user is enrolled. Additionally, the user is able to filter the notifications by different parameters.
Goal is to provide a easy way to get an overview of all current events and deadlines, without the need to visit every course.

A typical townsquare site looks like this:

![townsquare](https://github.com/user-attachments/assets/2933ffe6-6e37-4001-aad9-a50eb9f2a46e)


Townsquare is build as user-friendly as possible. The user can filter notifications and save the filter settings for future uses.
Notifications are pre-categorized into 3 categories with different colors: Deadlines and information, posts and activity completions.


## Subplugin functionality ##

Townsquare does not show notifications from every installed plugin, as every plugin can have different types of notifications that
not always should be shown to the current user (e.g. a teacher should not see the same notifications as a student).
Therefore, only the Moodle core plugins are supported by the plugin.
To show notifications from other plugins, a subplugin can be implemented or installed. To do that, install the local plugin
[local_townsquaresupport](https://github.com/learnweb/moodle-local_townsquaresupport). Townsquaresupport manages
subplugins and makes them available to Townsquare.

More information on townsquare subplugins: https://github.com/learnweb/moodle-local_townsquaresupport/wiki

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/blocks/townsquare

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## License ##

2023 Tamaro Walter

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
