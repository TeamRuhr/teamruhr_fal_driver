<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "teamruhr_fal_test".
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF['teamruhr_fal_test'] = array (
	'title' => 'FAL test driver for files outside the web root',
	'description' => 'Provides a FAL test driver for using files which are stored outside of the web root directory. See documentation for more details.',
	'category' => 'be',
	'version' => '1.0.2',
	'state' => 'beta',
	'uploadfolder' => false,
	'createDirs' => '',
	'clearcacheonload' => false,
	'author' => 'Michael Oehlhof',
	'author_email' => 'typo3@oehlhof.de',
	'author_company' => '',
	'constraints' =>
		array (
			'depends' =>
				array (
					'typo3' => '7.2.0-7.99.99',
				),
			'conflicts' =>
				array (
				),
			'suggests' =>
				array (
				),
		),
);

