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

This driver is "work in progress", so not all functions may be filled with the needed code yet.

**Requires TYPO3 CMS 7.2**


Processed folder
================

I recommend using a folder inside the fileadmin as "Folder for manipulated and temporary images etc."
Create a folder with the name "_processed_fal_test" on the root of the fileadmin and set it as configuration value of the driver element.
1:/_processed_fal_test


