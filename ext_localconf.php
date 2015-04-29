<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

// register additional driver
$TYPO3_CONF_VARS['SYS']['fal']['registeredDrivers'][\Teamruhr\TeamruhrFalTest\Driver\FalTestDriver::DRIVER_TYPE] = array(
	'class' => 'Teamruhr\TeamruhrFalTest\Driver\FalTestDriver',
	'label' => 'FAL test',
	'flexFormDS' => 'FILE:EXT:teamruhr_fal_test/Configuration/FlexForm/FalTestDriverFlexForm.xml'
);
