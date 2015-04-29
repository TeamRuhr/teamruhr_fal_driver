#
# TABLE STRUCTURE FOR TABLE 'tx_mooutsidewebroot_files'
#
CREATE TABLE tx_teamruhrfaltest_files (
	id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	identifier VARCHAR(250) DEFAULT '' NOT NULL,
	path VARCHAR(250) DEFAULT '' NOT NULL,
	name VARCHAR(250) DEFAULT '' NOT NULL,
	parent INT(11) UNSIGNED DEFAULT '0' NOT NULL,
	isDirectory INT(11) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (id),
	KEY identifier_key (identifier),
	KEY name_key (name),
	KEY parent_key (parent)
) ENGINE=InnoDB;
