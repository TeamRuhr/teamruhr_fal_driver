.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


What does it do?
================

This is a test driver for the file abstraction layer (FAL) to be used during development of FAL related issues.

You can create a file storage which allows you to use a local folder everywhere on your disk.
The path to the root directory must be set with the configuration in the extension manager.

In the settings of the storage element you can specify a file where all calls to the functions are logged with their function parameters.

The identifier, path and name are strictly separated. The identifier is not a combination of path and name as in the LocalDriver.

This driver is "work in progress", so not all functions may be filled with the needed code yet.

**Works with TYPO3 CMS 8.7 and 9.x**


Processed folder
================

I recommend using a folder inside the fileadmin as "Folder for manipulated and temporary images etc."
Create a folder with the name "_processed_fal_driver" on the root of the fileadmin and set it as configuration value of the driver element.
1:/_processed_fal_driver


Development
===========

You find the source of this driver on Github (https://github.com/TeamRuhr/teamruhr_fal_driver).
Any pull requests are welcome.
