<?php

namespace Teamruhr\TeamruhrFalTest\Driver;

use TYPO3\CMS\Core\Resource\Driver\AbstractHierarchicalFilesystemDriver;
use TYPO3\CMS\Core\Resource\ResourceStorage;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/*
 *  Copyright notice
 *
 *  (c) 2015 Michael Oehlhof <typo3@oehlhof.de>
 *  All rights reserved
 */

/**
 * Driver for testing FAL using files outside of the webroot directory
 *
 * @author Michael Oehlhof <typo3@oehlhof.de>
 */
class FalTestDriver extends AbstractHierarchicalFilesystemDriver{

	const DRIVER_TYPE = 'TeamruhrFalTest';

	const TABLE_NAME = 'tx_teamruhrfaltest_files';

	/**
	 * @var array
	 */
	protected $configuration;

	/**
	 * @var int
	 */
	protected $storageUid;

	/**
	 * @var resource
	 */
	protected $fileHandle = NULL;

	/**
	 * Processes the configuration for this driver.
	 * @return void
	 */
	public function processConfiguration() {
		$this->writeLog('processConfiguration()');
	}

	/**
	 * Sets the storage uid the driver belongs to
	 *
	 * @param int $storageUid
	 * @return void
	 */
	public function setStorageUid($storageUid) {
		$this->writeLog('setStorageUid(' . $storageUid . ')');
		$this->storageUid = $storageUid;
	}

	/**
	 * Initializes this object. This is called by the storage after the driver
	 * has been attached.
	 *
	 * @return void
	 */
	public function initialize() {
		$this->capabilities = ResourceStorage::CAPABILITY_BROWSABLE | ResourceStorage::CAPABILITY_WRITABLE;
		$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mo_outside_webroot']);
		$this->configuration['rootPath'] = $configuration['rootPath'];
		$this->writeLog('initialize()');
		$dbResult = $this->getDatabaseConnection()->exec_SELECTquery('identifier,path,name', self::TABLE_NAME, 'identifier="root"');
		if ($this->getDatabaseConnection()->sql_num_rows($dbResult) == 0) {
			$this->configuration['rootIdentifier'] = 'root';
			$rootRecord = array('identifier' => $this->configuration['rootIdentifier'],
				'path' => $this->configuration['rootPath'],
				'name' => '',
				'parent' => 0,
				'isDirectory' => 1);
			$dbResult = $this->getDatabaseConnection()->exec_INSERTquery(self::TABLE_NAME, $rootRecord);
		} else {
			$rootRecord = $this->getDatabaseConnection()->sql_fetch_assoc($dbResult);
			// @TODO: Check if $this->configuration['rootPath'] == $rootRecord['path'], init if !=
			$this->configuration['rootIdentifier'] = $rootRecord['identifier'];
			$this->configuration['rootPath'] = $rootRecord['path'];
			$this->configuration['rootName'] = $rootRecord['name'];
		}
	}

	/**
	 * Returns the capabilities of this driver.
	 *
	 * @return int
	 * @see Storage::CAPABILITY_* constants
	 */
	public function getCapabilities() {
		$this->writeLog('getCapabilities()');
		return $this->capabilities;
	}

	/**
	 * Merges the capabilities merged by the user at the storage
	 * configuration into the actual capabilities of the driver
	 * and returns the result.
	 *
	 * @param int $capabilities
	 * @return int
	 */
	public function mergeConfigurationCapabilities($capabilities) {
		$this->writeLog('mergeConfigurationCapabilities(' . $capabilities . ')');
		$this->capabilities &= $capabilities;
		return $this->capabilities;
	}

	/**
	 * Returns TRUE if this driver has the given capability.
	 *
	 * @param int $capability A capability, as defined in a CAPABILITY_* constant
	 * @return bool
	 */
	public function hasCapability($capability) {
		$this->writeLog('hasCapability(' . $capability . ')');
		return (($this->capabilities & $capability) == $capability);
	}

	/**
	 * Returns TRUE if this driver uses case-sensitive identifiers. NOTE: This
	 * is a configurable setting, but the setting does not change the way the
	 * underlying file system treats the identifiers; the setting should
	 * therefore always reflect the file system and not try to change its
	 * behaviour
	 *
	 * @return bool
	 */
	public function isCaseSensitiveFileSystem() {
		$this->writeLog('isCaseSensitiveFileSystem()');
		return FALSE;
	}

	/**
	 * Cleans a fileName from not allowed characters
	 *
	 * @param string $fileName
	 * @param string $charset Charset of the a fileName
	 *                        (defaults to current charset; depending on context)
	 * @return string the cleaned filename
	 */
	public function sanitizeFileName($fileName, $charset = '') {
		$this->writeLog('sanitizeFileName(' . $fileName . ',' . $charset . ')');
		$this->writeLog('	!!! not yet implemented');
		return $fileName;
	}

	/**
	 * Hashes a file identifier, taking the case sensitivity of the file system
	 * into account. This helps mitigating problems with case-insensitive
	 * databases.
	 *
	 * @param string $identifier
	 * @return string
	 */
	public function hashIdentifier($identifier) {
		$this->writeLog('hashIdentifier(' . $identifier . ')');
		$identifier = $this->canonicalizeAndCheckFileIdentifier($identifier);
		return sha1($identifier);
	}

	/**
	 * Returns the identifier of the root level folder of the storage.
	 *
	 * @return string
	 */
	public function getRootLevelFolder() {
		$this->writeLog('getRootLevelFolder()');
		return $this->configuration['rootIdentifier'];
	}

	/**
	 * Returns the identifier of the default folder new files should be put into.
	 *
	 * @return string
	 */
	public function getDefaultFolder() {
		$this->writeLog('getDefaultFolder()');
		return $this->getRootLevelFolder();
	}

	/**
	 * Returns the identifier of the folder the file resides in
	 *
	 * @param string $fileIdentifier
	 * @return string
	 */
	public function getParentFolderIdentifierOfIdentifier($fileIdentifier) {
		$this->writeLog('getParentFolderIdentifierOfIdentifier(' . $fileIdentifier . ')');
		$where = 'identifier="' . rtrim($fileIdentifier, '/') . '"';
		$dbResult = $this->getDatabaseConnection()->exec_SELECTquery('identifier,path,name,id,parent', self::TABLE_NAME, $where);
		$resultRecord = $this->getDatabaseConnection()->sql_fetch_assoc($dbResult);
		if ($resultRecord !== FALSE) {
			$where = 'id=' . intval($resultRecord['parent']);
			$dbResult = $this->getDatabaseConnection()->exec_SELECTquery('identifier,path,name,id,parent', self::TABLE_NAME, $where);
			$resultRecord = $this->getDatabaseConnection()->sql_fetch_assoc($dbResult);
			return $resultRecord['identifier'];
		} else {
			return '';
		}
	}

	/**
	 * Returns the public URL to a file.
	 * Either fully qualified URL or relative to PATH_site (rawurlencoded).
	 *
	 * @param string $identifier
	 * @return string
	 */
	public function getPublicUrl($identifier) {
		$this->writeLog('getPublicUrl(' . $identifier . ')');
		$this->writeLog('	!!! not yet implemented');
		return '';
	}

	/**
	 * Creates a folder, within a parent folder.
	 * If no parent folder is given, a root level folder will be created
	 *
	 * @param string $newFolderName
	 * @param string $parentFolderIdentifier
	 * @param bool $recursive
	 * @return string the Identifier of the new folder
	 */
	public function createFolder($newFolderName, $parentFolderIdentifier = '', $recursive = FALSE) {
		$this->writeLog('createFolder(' . $newFolderName . ',' . $parentFolderIdentifier . ',' . $recursive . ')');
		if ($parentFolderIdentifier == '') {
			$newRecord['parent'] = 1;
		} else {
			$folderInfo = $this->getFolderInfoByIdentifier($parentFolderIdentifier);
			$newRecord['parent'] = $folderInfo['id'];
		}
		$parentPath = $this->getFullPath($newRecord['parent']);
		$newRecord['path'] = uniqid('trft_');
		mkdir($parentPath . '/' . $newRecord['path']);
		$newRecord['identifier'] = $this->generateUuid();
		$newRecord['name'] = $newFolderName;
		$newRecord['isDirectory'] = 1;
		$dbResult = $this->getDatabaseConnection()->exec_INSERTquery(self::TABLE_NAME, $newRecord);
		if ($newFolderName == '_processed_') {
			// When we have to create the _processed_ folder, we have also to update the driver configuration
			$where = 'uid=' . $this->storageUid;
			$this->getDatabaseConnection()->exec_UPDATEquery('sys_file_storage', $where, array('processingfolder' => $newRecord['identifier']));
		}
		return $newRecord['identifier'];
	}

	/**
	 * Renames a folder in this storage.
	 *
	 * @param string $folderIdentifier
	 * @param string $newName
	 * @return array A map of old to new file identifiers of all affected resources
	 */
	public function renameFolder($folderIdentifier, $newName) {
		$this->writeLog('renameFolder(' . $folderIdentifier . ',' . $newName . ')');
		$this->writeLog('	!!! not yet implemented');
	}

	/**
	 * Removes a folder in filesystem.
	 *
	 * @param string $folderIdentifier
	 * @param bool $deleteRecursively
	 * @return bool
	 */
	public function deleteFolder($folderIdentifier, $deleteRecursively = FALSE) {
		$this->writeLog('deleteFolder(' . $folderIdentifier . ',' . $deleteRecursively . ')');
		if (!$this->isFolderEmpty($folderIdentifier)) {
			$fileList = $this->getFilesInFolder($folderIdentifier);
			foreach ($fileList as $fileIdentifier) {
				if (!$this->deleteFile($fileIdentifier)) {
					return FALSE;
				}
			}
			if ($deleteRecursively === TRUE) {
				$folderList = $this->getFoldersInFolder($folderIdentifier);
				foreach ($folderList as $subFolderIdentifier) {
					if (!$this->deleteFolder($subFolderIdentifier, TRUE)) {
						return FALSE;
					}
				}
			}
			if (!$this->isFolderEmpty($folderIdentifier)) {
				return FALSE;
			}
		}
		$folderInfo = $this->getFolderInfoByIdentifier($folderIdentifier);
		$folderPath = $this->getFullPath($folderInfo['id']);
		$result = rmdir($folderPath);
		if ($result) {
			$this->getDatabaseConnection()->exec_DELETEquery(self::TABLE_NAME, 'id=' . intval($folderInfo['id']));
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Checks if a file exists.
	 *
	 * @param string $fileIdentifier
	 * @return bool
	 */
	public function fileExists($fileIdentifier) {
		$this->writeLog('fileExists(' . $fileIdentifier . ')');
		$where = 'identifier="' . $fileIdentifier . '"';
		$dbResult = $this->getDatabaseConnection()->exec_SELECTquery('path,name', self::TABLE_NAME, $where);
		return ($this->getDatabaseConnection()->sql_num_rows($dbResult) > 0);
	}

	/**
	 * Checks if a folder exists.
	 *
	 * @param string $folderIdentifier
	 * @return bool
	 */
	public function folderExists($folderIdentifier) {
		$this->writeLog('folderExists(' . $folderIdentifier . ')');
		$where = 'identifier="' . rtrim($folderIdentifier, '/') . '"';
		$dbResult = $this->getDatabaseConnection()->exec_SELECTquery('path,name', self::TABLE_NAME, $where);
		return ($this->getDatabaseConnection()->sql_num_rows($dbResult) > 0);
	}

	/**
	 * Checks if a folder contains files and (if supported) other folders.
	 *
	 * @param string $folderIdentifier
	 * @return bool TRUE if there are no files and folders within $folder
	 */
	public function isFolderEmpty($folderIdentifier) {
		$this->writeLog('isFolderEmpty(' . $folderIdentifier . ')');
		$folderInfo = $this->getFolderInfoByIdentifier($folderIdentifier);
		$where = 'parent=' . intval($folderInfo['id']);
		$dbResult = $this->getDatabaseConnection()->exec_SELECTquery('path,name', self::TABLE_NAME, $where);
		return ($this->getDatabaseConnection()->sql_num_rows($dbResult) == 0);
	}

	/**
	 * Adds a file from the local server hard disk to a given path in TYPO3s
	 * virtual file system. This assumes that the local file exists, so no
	 * further check is done here! After a successful the original file must
	 * not exist anymore.
	 *
	 * @param string $localFilePath (within PATH_site)
	 * @param string $targetFolderIdentifier
	 * @param string $newFileName optional, if not given original name is used
	 * @param bool $removeOriginal if set the original file will be removed
	 *                                after successful operation
	 * @return string the identifier of the new file
	 */
	public function addFile($localFilePath, $targetFolderIdentifier, $newFileName = '', $removeOriginal = TRUE) {
		$this->writeLog('addFile(' . $localFilePath . ',' . $targetFolderIdentifier . ',' . $newFileName . ',' . $removeOriginal . ')');
		$folderInfo = $this->getFolderInfoByIdentifier($targetFolderIdentifier);
		$newRecord['parent'] = $folderInfo['id'];
		$parentPath = $this->getFullPath($newRecord['parent']);
		$newRecord['path'] = uniqid('trft_');
		copy($localFilePath, $parentPath . '/' . $newRecord['path']);
		if ($removeOriginal) {
			unlink($localFilePath);
		}
		$newRecord['identifier'] = $this->generateUuid();
		if ($newFileName == '') {
			$pathParts = pathinfo($localFilePath);
			$newRecord['name'] = $pathParts['basename'];
		} else {
			$newRecord['name'] = $newFileName;
		}
		$newRecord['isDirectory'] = 0;
		$dbResult = $this->getDatabaseConnection()->exec_INSERTquery(self::TABLE_NAME, $newRecord);
		return $newRecord['identifier'];
	}

	/**
	 * Creates a new (empty) file and returns the identifier.
	 *
	 * @param string $fileName
	 * @param string $parentFolderIdentifier
	 * @return string
	 */
	public function createFile($fileName, $parentFolderIdentifier) {
		$this->writeLog('createFile(' . $fileName . ',' . $parentFolderIdentifier . ')');
		$folderInfo = $this->getFolderInfoByIdentifier($parentFolderIdentifier);
		$newRecord['parent'] = $folderInfo['id'];
		$parentPath = $this->getFullPath($newRecord['parent']);
		$newRecord['path'] = uniqid('trft_');
		touch($parentPath . '/' . $newRecord['path']);
		$newRecord['identifier'] = $this->generateUuid();
		$newRecord['name'] = $fileName;
		$newRecord['isDirectory'] = 0;
		$dbResult = $this->getDatabaseConnection()->exec_INSERTquery(self::TABLE_NAME, $newRecord);
		return $newRecord['identifier'];
	}

	/**
	 * Copies a file *within* the current storage.
	 * Note that this is only about an inner storage copy action,
	 * where a file is just copied to another folder in the same storage.
	 *
	 * @param string $fileIdentifier
	 * @param string $targetFolderIdentifier
	 * @param string $fileName
	 * @return string the Identifier of the new file
	 */
	public function copyFileWithinStorage($fileIdentifier, $targetFolderIdentifier, $fileName) {
		$this->writeLog('copyFileWithinStorage(' . $fileIdentifier . ',' . $targetFolderIdentifier . ',' . $fileName . ')');
		$fileInfo = $this->getFileInfoByIdentifier($fileIdentifier);
		$fullPath = $this->getFullPath(intval($fileInfo['id']));
		return $this->addFile($fullPath, $targetFolderIdentifier, $fileName, FALSE);
	}

	/**
	 * Renames a file in this storage.
	 *
	 * @param string $fileIdentifier
	 * @param string $newName The target path (including the file name!)
	 * @return string The identifier of the file after renaming
	 */
	public function renameFile($fileIdentifier, $newName) {
		$this->writeLog('renameFile(' . $fileIdentifier . ',' . $newName . ')');
		$where = 'identifier="' . $fileIdentifier . '"';
		$this->getDatabaseConnection()->exec_UPDATEquery(self::TABLE_NAME, $where, array('name' => $newName));
		return $fileIdentifier;
	}

	/**
	 * Replaces a file with file in local file system.
	 *
	 * @param string $fileIdentifier
	 * @param string $localFilePath
	 * @return bool TRUE if the operation succeeded
	 */
	public function replaceFile($fileIdentifier, $localFilePath) {
		$this->writeLog('replaceFile(' . $fileIdentifier . ',' . $localFilePath . ')');
		$fileInfo = $this->getFileInfoByIdentifier($fileIdentifier);
		$fullPath = $this->getFullPath(intval($fileInfo['id']));
		$result = copy($localFilePath, $fullPath);
		return $result;
	}

	/**
	 * Removes a file from the filesystem. This does not check if the file is
	 * still used or if it is a bad idea to delete it for some other reason
	 * this has to be taken care of in the upper layers (e.g. the Storage)!
	 *
	 * @param string $fileIdentifier
	 * @return bool TRUE if deleting the file succeeded
	 */
	public function deleteFile($fileIdentifier) {
		$this->writeLog('deleteFile(' . $fileIdentifier . ')');
		$fileInfo = $this->getFileInfoByIdentifier($fileIdentifier);
		$fullPath = $this->getFullPath(intval($fileInfo['id']));
		$result = unlink($fullPath);
		if ($result) {
			$this->getDatabaseConnection()->exec_DELETEquery(self::TABLE_NAME, 'id=' . intval($fileInfo['id']));
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Creates a hash for a file.
	 *
	 * @param string $fileIdentifier
	 * @param string $hashAlgorithm The hash algorithm to use
	 * @return string
	 */
	public function hash($fileIdentifier, $hashAlgorithm) {
		$this->writeLog('hash(' . $fileIdentifier . ',' . $hashAlgorithm . ')');
		return $this->hashIdentifier($fileIdentifier);
	}


	/**
	 * Moves a file *within* the current storage.
	 * Note that this is only about an inner-storage move action,
	 * where a file is just moved to another folder in the same storage.
	 *
	 * @param string $fileIdentifier
	 * @param string $targetFolderIdentifier
	 * @param string $newFileName
	 * @return string
	 */
	public function moveFileWithinStorage($fileIdentifier, $targetFolderIdentifier, $newFileName) {
		$this->writeLog('moveFileWithinStorage(' . $fileIdentifier . ',' . $targetFolderIdentifier . ',' . $newFileName . ')');
		$fileInfo = $this->getFileInfoByIdentifier($fileIdentifier);
		$fullPath = $this->getFullPath(intval($fileInfo['id']));
		$result = $this->addFile($fullPath, $targetFolderIdentifier, $newFileName, FALSE);
		$this->deleteFile($fileIdentifier);
		return $result;
	}


	/**
	 * Folder equivalent to moveFileWithinStorage().
	 *
	 * @param string $sourceFolderIdentifier
	 * @param string $targetFolderIdentifier
	 * @param string $newFolderName
	 * @return array All files which are affected, map of old => new file identifiers
	 */
	public function moveFolderWithinStorage($sourceFolderIdentifier, $targetFolderIdentifier, $newFolderName) {
		$this->writeLog('moveFolderWithinStorage(' . $sourceFolderIdentifier . ',' . $targetFolderIdentifier . ',' . $newFolderName . ')');
		$this->writeLog('	!!! not yet implemented');
	}

	/**
	 * Folder equivalent to copyFileWithinStorage().
	 *
	 * @param string $sourceFolderIdentifier
	 * @param string $targetFolderIdentifier
	 * @param string $newFolderName
	 * @return bool
	 */
	public function copyFolderWithinStorage($sourceFolderIdentifier, $targetFolderIdentifier, $newFolderName) {
		$this->writeLog('copyFolderWithinStorage(' . $sourceFolderIdentifier . ',' . $targetFolderIdentifier . ',' . $newFolderName . ')');
		$this->writeLog('	!!! not yet implemented');
		return FALSE;
	}

	/**
	 * Returns the contents of a file. Beware that this requires to load the
	 * complete file into memory and also may require fetching the file from an
	 * external location. So this might be an expensive operation (both in terms
	 * of processing resources and money) for large files.
	 *
	 * @param string $fileIdentifier
	 * @return string The file contents
	 */
	public function getFileContents($fileIdentifier) {
		$this->writeLog('getFileContents(' . $fileIdentifier . ')');
		$fileInfo = $this->getFileInfoByIdentifier($fileIdentifier);
		$fullPath = $this->getFullPath(intval($fileInfo['id']));
		return file_get_contents($fullPath);
	}

	/**
	 * Sets the contents of a file to the specified value.
	 *
	 * @param string $fileIdentifier
	 * @param string $contents
	 * @return int The number of bytes written to the file
	 */
	public function setFileContents($fileIdentifier, $contents) {
		$this->writeLog('setFileContents(' . $fileIdentifier . ',' . $contents . ')');
		$fileInfo = $this->getFileInfoByIdentifier($fileIdentifier);
		$fullPath = $this->getFullPath(intval($fileInfo['id']));
		return file_put_contents($fullPath, $contents);
	}

	/**
	 * Checks if a file inside a folder exists
	 *
	 * @param string $fileName
	 * @param string $folderIdentifier
	 * @return bool
	 */
	public function fileExistsInFolder($fileName, $folderIdentifier) {
		$this->writeLog('fileExistsInFolder(' . $fileName . ',' . $folderIdentifier . ')');
		$folderInfo = $this->getFolderInfoByIdentifier($folderIdentifier);
		$where = 'name="' . rtrim($fileName, '/') . '" AND isDirectory=0 AND parent=' . intval($folderInfo['id']);
		$dbResult = $this->getDatabaseConnection()->exec_SELECTquery('identifier,path,name,id,parent', self::TABLE_NAME, $where);
		return ($this->getDatabaseConnection()->sql_num_rows($dbResult) > 0);
	}

	/**
	 * Checks if a folder inside a folder exists.
	 *
	 * @param string $folderName
	 * @param string $folderIdentifier
	 * @return bool
	 */
	public function folderExistsInFolder($folderName, $folderIdentifier) {
		$this->writeLog('folderExistsInFolder(' . $folderName . ',' . $folderIdentifier . ')');
		$folderInfo = $this->getFolderInfoByIdentifier($folderIdentifier);
		$where = 'name="' . rtrim($folderName, '/') . '" AND isDirectory=1 AND parent=' . intval($folderInfo['id']);
		$dbResult = $this->getDatabaseConnection()->exec_SELECTquery('identifier,path,name,id,parent', self::TABLE_NAME, $where);
		return ($this->getDatabaseConnection()->sql_num_rows($dbResult) > 0);
	}

	/**
	 * Returns a path to a local copy of a file for processing it. When changing the
	 * file, you have to take care of replacing the current version yourself!
	 *
	 * @param string $fileIdentifier
	 * @param bool $writable Set this to FALSE if you only need the file for read
	 *                       operations. This might speed up things, e.g. by using
	 *                       a cached local version. Never modify the file if you
	 *                       have set this flag!
	 * @return string The path to the file on the local disk
	 */
	public function getFileForLocalProcessing($fileIdentifier, $writable = TRUE) {
		$this->writeLog('getFileForLocalProcessing(' . $fileIdentifier . ',' . $writable . ')');
		if ($writable === FALSE) {
			$fileInfo = $this->getFileInfoByIdentifier($fileIdentifier);
			return $this->getFullPath(intval($fileInfo['id']));
		} else {
			return $this->copyFileToTemporaryPath($fileIdentifier);
		}
	}

	/**
	 * Returns the permissions of a file/folder as an array
	 * (keys r, w) of boolean flags
	 *
	 * @param string $identifier
	 * @return array
	 */
	public function getPermissions($identifier) {
		$this->writeLog('getPermissions(' . $identifier . ')');
		return array('r' => TRUE, 'w' => TRUE);
	}

	/**
	 * Directly output the contents of the file to the output
	 * buffer. Should not take care of header files or flushing
	 * buffer before. Will be taken care of by the Storage.
	 *
	 * @param string $identifier
	 * @return void
	 */
	public function dumpFileContents($identifier) {
		$this->writeLog('dumpFileContents(' . $identifier . ')');
		readfile($this->getAbsolutePath($this->canonicalizeAndCheckFileIdentifier($identifier)), 0);
	}

	/**
	 * Checks if a given identifier is within a container, e.g. if
	 * a file or folder is within another folder.
	 * This can e.g. be used to check for web-mounts.
	 *
	 * Hint: this also needs to return TRUE if the given identifier
	 * matches the container identifier to allow access to the root
	 * folder of a filemount.
	 *
	 * @param string $folderIdentifier
	 * @param string $identifier identifier to be checked against $folderIdentifier
	 * @return bool TRUE if $content is within or matches $folderIdentifier
	 */
	public function isWithin($folderIdentifier, $identifier) {
		$this->writeLog('isWithin(' . $folderIdentifier . ',' . $identifier . ')');
		$folderInfo = $this->getFolderInfoByIdentifier($folderIdentifier);
		$where = 'identifier="' . rtrim($identifier, '/') . '"';
		$dbResult = $this->getDatabaseConnection()->exec_SELECTquery('identifier,path,name,id,parent', self::TABLE_NAME, $where);
		$resultRecord = $this->getDatabaseConnection()->sql_fetch_assoc($dbResult);
		return ($folderInfo['id'] === $resultRecord['parent']);
	}

	/**
	 * Returns information about a file.
	 *
	 * @param string $fileIdentifier
	 * @param array $propertiesToExtract Array of properties which are be extracted
	 *                                   If empty all will be extracted
	 * @return array
	 */
	public function getFileInfoByIdentifier($fileIdentifier, array $propertiesToExtract = array()) {
		$this->writeLog('getFileInfoByIdentifier(' . $fileIdentifier . ',' . $propertiesToExtract . ')');
		$where = 'identifier="' . rtrim($fileIdentifier, '/') . '" AND isDirectory=0';
		$dbResult = $this->getDatabaseConnection()->exec_SELECTquery('identifier,path,name,id,parent', self::TABLE_NAME, $where);
		$fileRecord = $this->getDatabaseConnection()->sql_fetch_assoc($dbResult);
		return array('identifier' => $fileRecord['identifier'],
			'name' => $fileRecord['name'],
			'path' => $fileRecord['path'],
			'id' => $fileRecord['id'],
			'parent' => $fileRecord['parent'],
			'storage' => $this->storageUid);
	}

	/**
	 * Returns information about a file.
	 *
	 * @param string $folderIdentifier
	 * @return array
	 */
	public function getFolderInfoByIdentifier($folderIdentifier) {
		$this->writeLog('getFolderInfoByIdentifier(' . $folderIdentifier . ')');
		$where = 'identifier="' . rtrim($folderIdentifier, '/') . '" AND isDirectory=1';
		$dbResult = $this->getDatabaseConnection()->exec_SELECTquery('identifier,path,name,id,parent', self::TABLE_NAME, $where);
		if ($this->getDatabaseConnection()->sql_num_rows($dbResult) > 0) {
			$folderRecord = $this->getDatabaseConnection()->sql_fetch_assoc($dbResult);
			return array('identifier' => $folderRecord['identifier'],
				'name' => $folderRecord['name'],
				'path' => $folderRecord['path'],
				'id' => $folderRecord['id'],
				'parent' => $folderRecord['parent'],
				'storage' => $this->storageUid);
		} else {
			return array();
		}
	}

	/**
	 * Returns a list of files inside the specified path
	 *
	 * @param string $folderIdentifier
	 * @param int $start
	 * @param int $numberOfItems
	 * @param bool $recursive
	 * @param array $filenameFilterCallbacks callbacks for filtering the items
	 * @param string $sort Property name used to sort the items.
	 *                     Among them may be: '' (empty, no sorting), name,
	 *                     fileext, size, tstamp and rw.
	 *                     If a driver does not support the given property, it
	 *                     should fall back to "name".
	 * @param bool $sortRev TRUE to indicate reverse sorting (last to first)
	 * @return array of FileIdentifiers
	 */
	public function getFilesInFolder($folderIdentifier, $start = 0, $numberOfItems = 0, $recursive = FALSE, array $filenameFilterCallbacks = array(), $sort = '', $sortRev = FALSE) {
		$this->writeLog('getFilesInFolder(' . $folderIdentifier . ',' . $start . ',' . $numberOfItems . ',' . $recursive . ',' . $filenameFilterCallbacks . ',' . $sort . ',' . $sortRev . ')');
		$this->writeLog('	!!! $start, $numberOfItems, $recursive, $filenameFilterCallbacks, $sort and $sortRev not yet implemented');
		$folderInfo = $this->getFolderInfoByIdentifier($folderIdentifier);
		$folderId = intval($folderInfo['id']);
		$files = array();
		$where = 'parent=' . $folderId . ' AND isDirectory=0';
		$dbResult = $this->getDatabaseConnection()->exec_SELECTquery('identifier,path,name', self::TABLE_NAME, $where);
		while ($resultRecord = $this->getDatabaseConnection()->sql_fetch_assoc($dbResult)) {
			$files[$resultRecord['identifier']] = $resultRecord['identifier'];
		}
		return $files;
	}

	/**
	 * Returns a list of folders inside the specified path
	 *
	 * @param string $folderIdentifier
	 * @param int $start
	 * @param int $numberOfItems
	 * @param bool $recursive
	 * @param array $folderNameFilterCallbacks callbacks for filtering the items
	 * @param string $sort Property name used to sort the items.
	 *                     Among them may be: '' (empty, no sorting), name,
	 *                     fileext, size, tstamp and rw.
	 *                     If a driver does not support the given property, it
	 *                     should fall back to "name".
	 * @param bool $sortRev TRUE to indicate reverse sorting (last to first)
	 * @return array of Folder Identifier
	 */
	public function getFoldersInFolder($folderIdentifier, $start = 0, $numberOfItems = 0, $recursive = FALSE, array $folderNameFilterCallbacks = array(), $sort = '', $sortRev = FALSE) {
		$this->writeLog('getFoldersInFolder(' . $folderIdentifier . ',' . $start . ',' . $numberOfItems . ',' . $recursive . ',' . $folderNameFilterCallbacks . ',' . $sort . ',' . $sortRev . ')');
		$this->writeLog('	!!! $start, $numberOfItems, $recursive, $filenameFilterCallbacks, $sort and $sortRev not yet implemented');
		$folderInfo = $this->getFolderInfoByIdentifier($folderIdentifier);
		$folderId = intval($folderInfo['id']);
		$folders = array();
		$where = 'parent=' . $folderId . ' AND isDirectory=1';
		$dbResult = $this->getDatabaseConnection()->exec_SELECTquery('identifier,path,name', self::TABLE_NAME, $where);
		while ($resultRecord = $this->getDatabaseConnection()->sql_fetch_assoc($dbResult)) {
			$folders[$resultRecord['identifier']] = $resultRecord['identifier'];
		}
		return $folders;
	}

	/**
	 * Returns the number of files inside the specified path
	 *
	 * @param string  $folderIdentifier
	 * @param boolean $recursive
	 * @param array   $filenameFilterCallbacks callbacks for filtering the items
	 * @return integer Number of files in folder
	 */
	public function countFilesInFolder($folderIdentifier, $recursive = FALSE, array $filenameFilterCallbacks = array()) {
		$this->writeLog('countFilesInFolder(' . $folderIdentifier . ',' . $recursive . ',' . $filenameFilterCallbacks . ')');
		$folderInfo = $this->getFolderInfoByIdentifier($folderIdentifier);
		$folderId = intval($folderInfo['id']);
		$where = 'parent=' . $folderId . ' AND isDirectory=0';
		$dbResult = $this->getDatabaseConnection()->exec_SELECTquery('identifier,path,name', self::TABLE_NAME, $where);
		return $this->getDatabaseConnection()->sql_num_rows($dbResult);
	}

	/**
	 * Returns the number of folders inside the specified path
	 *
	 * @param string  $folderIdentifier
	 * @param boolean $recursive
	 * @param array   $folderNameFilterCallbacks callbacks for filtering the items
	 * @return integer Number of folders in folder
	 */
	public function countFoldersInFolder($folderIdentifier, $recursive = FALSE, array $folderNameFilterCallbacks = array()) {
		$this->writeLog('countFoldersInFolder(' . $folderIdentifier . ',' . $recursive . ',' . $folderNameFilterCallbacks . ')');
		$folderInfo = $this->getFolderInfoByIdentifier($folderIdentifier);
		$folderId = intval($folderInfo['id']);
		$where = 'parent=' . $folderId . ' AND isDirectory=1';
		$dbResult = $this->getDatabaseConnection()->exec_SELECTquery('identifier,path,name', self::TABLE_NAME, $where);
		return $this->getDatabaseConnection()->sql_num_rows($dbResult);
	}

	/**
	 * Copies a file to a temporary path and returns that path.
	 *
	 * @param string $fileIdentifier
	 * @return string The temporary path
	 * @throws \RuntimeException
	 */
	protected function copyFileToTemporaryPath($fileIdentifier) {
		$fileInfo = $this->getFileInfoByIdentifier($fileIdentifier);
		$sourcePath = $this->getFullPath(intval($fileInfo['id']));
		$temporaryPath = $this->getTemporaryPathForFile($fileIdentifier);
		$result = copy($sourcePath, $temporaryPath);
		touch($temporaryPath, filemtime($sourcePath));
		if ($result === FALSE) {
			throw new \RuntimeException(
				'Copying file "' . $fileIdentifier . '" to temporary path "' . $temporaryPath . '" failed.',
				1320577649
			);
		}
		return $temporaryPath;
	}

	protected function getFullPath($id) {
		$fullPath = '';
		$parentId = $id;
		while ($parentId > 0) {
			$where = 'id=' . $parentId;
			$dbResult = $this->getDatabaseConnection()->exec_SELECTquery('path,parent,id', self::TABLE_NAME, $where);
			$parentRecord = $this->getDatabaseConnection()->sql_fetch_assoc($dbResult);
			if (strlen($fullPath) > 0) {
				$fullPath = $parentRecord['path'] . '/' . $fullPath;
			} else {
				$fullPath = $parentRecord['path'];
			}
			$parentId = $parentRecord['parent'];
		}
		return $fullPath;
	}

	protected function generateUuid() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0x0fff ) | 0x4000,
			mt_rand( 0, 0x3fff ) | 0x8000,
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}

	protected function writeLog($logMessage) {
		$fileName = $this->configuration['logFileName'];
		if ($fileName == '') {
			return;
		}
		if (!is_writable($fileName)) {
			touch($fileName);
		}
		if ($this->fileHandle == NULL) {
			$this->fileHandle = fopen($fileName, "a");
		}
		fwrite($this->fileHandle, $logMessage . "\n");
	}

	/**
	 * Returns the database connection
	 *
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

}
