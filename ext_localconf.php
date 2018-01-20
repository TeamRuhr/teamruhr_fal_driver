<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

// register additional driver
$TYPO3_CONF_VARS['SYS']['fal']['registeredDrivers'][\Teamruhr\TeamruhrFalDriver\Driver\TeamruhrFalDriver::DRIVER_TYPE] = array(
	'class' => 'Teamruhr\TeamruhrFalDriver\Driver\TeamruhrFalDriver',
	'label' => 'TeamRuhr FAL driver',
	'flexFormDS' => 'FILE:EXT:teamruhr_fal_driver/Configuration/FlexForms/TeamruhrFalDriverFlexForm.xml'
);
