#
# TABLE STRUCTURE FOR TABLE 'tx_teamruhrfaldriver_files'
#
CREATE TABLE tx_teamruhrfaldriver_files (
	id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	storageId INT(11) UNSIGNED DEFAULT '0' NOT NULL,
	identifier VARCHAR(250) DEFAULT '' NOT NULL,
	path VARCHAR(250) DEFAULT '' NOT NULL,
	name VARCHAR(250) DEFAULT '' NOT NULL,
	parent INT(11) UNSIGNED DEFAULT '0' NOT NULL,
	isDirectory INT(11) UNSIGNED DEFAULT '0' NOT NULL,
	size bigint(20) unsigned DEFAULT '0' NOT NULL,
	creation_date int(11) DEFAULT '0' NOT NULL,
	modification_date int(11) DEFAULT '0' NOT NULL,
	PRIMARY KEY (id),
	KEY storageid_key (storageId),
	KEY identifier_key (identifier),
	KEY name_key (name),
	KEY parent_key (parent)
) ENGINE=InnoDB;
