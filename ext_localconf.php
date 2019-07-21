<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

// register additional driver
$TYPO3_CONF_VARS['SYS']['fal']['registeredDrivers'][\TeamRuhr\TeamruhrFalDriver\Driver\TeamruhrFalDriver::DRIVER_TYPE] = array(
	'class' => 'TeamRuhr\TeamruhrFalDriver\Driver\TeamruhrFalDriver',
	'label' => 'TeamRuhr FAL driver',
	'flexFormDS' => 'FILE:EXT:teamruhr_fal_driver/Configuration/FlexForms/TeamruhrFalDriverFlexForm.xml'
);
