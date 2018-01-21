<?php

namespace Teamruhr\TeamruhrFalDriver\Driver;

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

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Resource\Driver\AbstractHierarchicalFilesystemDriver;
use TYPO3\CMS\Core\Resource\Exception;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This is a Driver for testing FAL
 *
 * It uses files outside of the webroot directory
 *
 * It is possible to log all method calls to a log file,
 * to get an overview which methods are called in which order.
 *
 * The identifier, path and name are strictly separated
 * The identifier is not a combination of path and name as in the LocalDriver
 *
 * @author Michael Oehlhof <typo3@oehlhof.de>
 */
class TeamruhrFalDriver extends AbstractHierarchicalFilesystemDriver
{

    const DRIVER_TYPE = 'TeamruhrFalDriver';

    const TABLE_NAME = 'tx_teamruhrfaldriver_files';

    const FILE_FOLDER_PREFIX = 'trfd_';

    const ROOT_IDENTIFIER = '/';

    /**
     * Contains the extension configuration
     * @var array
     */
    protected $configuration;

    /**
     * The storage uid the driver belongs to
     * @var int
     */
    protected $storageUid;

    /**
     * The resource handle of the log file
     * @var resource
     */
    protected $fileHandle = null;

    /**
     * @var QueryBuilder $queryBuilder
     */
    protected $queryBuilder = null;

    /**
     * @var ConnectionPool $connectionPool
     */
    protected $connectionPool = null;

    /**
     * Processes the configuration for this driver.
     * @return void
     */
    public function processConfiguration()
    {
        $this->writeLog('processConfiguration()');
    }

    /**
     * Sets the storage uid the driver belongs to
     *
     * @param int $storageUid The storage uid
     *
     * @return void
     */
    public function setStorageUid($storageUid)
    {
        $this->writeLog('setStorageUid(' . $storageUid . ')');
        $this->storageUid = $storageUid;
    }

    /**
     * Initializes this object.
     * This is called by the storage after the driver has been attached.
     *
     * @return void
     */
    public function initialize()
    {
        $this->capabilities = ResourceStorage::CAPABILITY_BROWSABLE | ResourceStorage::CAPABILITY_WRITABLE;
        $configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['teamruhr_fal_driver']);
        $this->configuration['rootPath'] = $configuration['rootPath'];
        $this->configuration['logFileName'] = $configuration['logFileName'];
        $this->writeLog('initialize()');
        $this->connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $this->queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
        // Check if an enrty for the root record exists in the database table
        // If not, create the root entry
        $row = $this->queryBuilder->select('identifier', 'path', 'name')
            ->from(self::TABLE_NAME)
            ->where($this->queryBuilder->expr()->eq('identifier', $this->queryBuilder->createNamedParameter('/', \PDO::PARAM_STR)))
            ->execute()
            ->fetch();
        if ($row === false) {
            $this->configuration['rootIdentifier'] = self::ROOT_IDENTIFIER;
            $rootRecord = array(
                'identifier' => $this->configuration['rootIdentifier'],
                'path' => $this->configuration['rootPath'],
                'name' => '',
                'parent' => 0,
                'isDirectory' => 1,
                'size' => 0,
                'creation_date' => time(),
                'modification_date' => time()
            );
            $insertQueryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
            $insertQueryBuilder->insert(self::TABLE_NAME)
                ->values($rootRecord)
                ->execute();
        } else {
            $rootRecord = $row;
        }
        $this->configuration['rootIdentifier'] = $rootRecord['identifier'];
        $this->configuration['rootName'] = $rootRecord['name'];
    }

    /**
     * Get a record from the database for a given identifier
     *
     * Returns false if no record exists
     *
     * @param $identifier
     * @return mixed
     */
    protected function getInfoByIdentifier($identifier)
    {
        $record = $this->queryBuilder->select('identifier', 'path', 'name', 'id', 'parent', 'size', 'creation_date', 'modification_date')
            ->from(self::TABLE_NAME)
            ->where($this->queryBuilder->expr()->eq('identifier',
                $this->queryBuilder->createNamedParameter($identifier, \PDO::PARAM_STR)))
            ->execute()
            ->fetch();
        return $record;
    }

    /**
     * Returns the capabilities of this driver.
     *
     * @return int
     * @see Storage::CAPABILITY_* constants
     */
    public function getCapabilities()
    {
        $this->writeLog('getCapabilities()');
        return $this->capabilities;
    }

    /**
     * Merges the capabilities merged by the user at the storage
     * configuration into the actual capabilities of the driver
     * and returns the result.
     *
     * @param int $capabilities The user capabilities
     *
     * @return int
     */
    public function mergeConfigurationCapabilities($capabilities)
    {
        $this->writeLog('mergeConfigurationCapabilities(' . $capabilities . ')');
        $this->capabilities &= $capabilities;
        return $this->capabilities;
    }

    /**
     * Returns TRUE if this driver has the given capability.
     *
     * @param int $capability A capability, as defined in a CAPABILITY_* constant
     *
     * @return bool
     */
    public function hasCapability($capability)
    {
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
    public function isCaseSensitiveFileSystem()
    {
        $this->writeLog('isCaseSensitiveFileSystem()');
        return false;
    }

    /**
     * Cleans a fileName from not allowed characters
     *
     * @param string $fileName The file name to be cleaned
     * @param string $charset Charset of the a fileName
     *                        (defaults to current charset; depending on context)
     *
     * @return string the cleaned filename
     */
    public function sanitizeFileName($fileName, $charset = '')
    {
        $this->writeLog('sanitizeFileName(' . $fileName . ',' . $charset . ')');
        $this->writeLog('	!!! not yet implemented');
        return $fileName;
    }

    /**
     * Hashes a file identifier, taking the case sensitivity of the file system
     * into account. This helps mitigating problems with case-insensitive
     * databases.
     *
     * @param string $identifier The file identifier
     *
     * @return string
     * @throws \TYPO3\CMS\Core\Resource\Exception\InvalidPathException
     */
    public function hashIdentifier($identifier)
    {
        $this->writeLog('hashIdentifier(' . $identifier . ')');
        $identifier = $this->canonicalizeAndCheckFileIdentifier($identifier);
        return sha1($identifier);
    }

    /**
     * Returns the identifier of the root level folder of the storage.
     *
     * @return string
     */
    public function getRootLevelFolder()
    {
        $this->writeLog('getRootLevelFolder()');
        return $this->configuration['rootIdentifier'];
    }

    /**
     * Returns the identifier of the default folder new files should be put into.
     *
     * @return string
     */
    public function getDefaultFolder()
    {
        $this->writeLog('getDefaultFolder()');
        return $this->getRootLevelFolder();
    }

    /**
     * Returns the identifier of the folder the file resides in
     *
     * @param string $fileIdentifier The file identifier
     *
     * @return string
     */
    public function getParentFolderIdentifierOfIdentifier($fileIdentifier)
    {
        $this->writeLog('getParentFolderIdentifierOfIdentifier(' . $fileIdentifier . ')');
        $row = $this->getInfoByIdentifier($fileIdentifier);
        if ($row === false) {
            return '';
        } else {
            $resultRecord = $this->queryBuilder->select('identifier', 'path', 'name', 'id', 'parent')
                ->from(self::TABLE_NAME)
                ->where($this->queryBuilder->expr()->eq('id', intval($row['parent'])))
                ->execute()
                ->fetch();
            if ($resultRecord === false) {
                return $row['identifier'];
            } else {
                return $resultRecord['identifier'];
            }
        }
    }

    /**
     * Returns the public URL to a file.
     * Either fully qualified URL or relative to PATH_site (rawurlencoded).
     *
     * @param string $identifier The identifier
     *
     * @return string
     */
    public function getPublicUrl($identifier)
    {
        $this->writeLog('getPublicUrl(' . $identifier . ')');
        $this->writeLog('	!!! not yet implemented');
        return '';
    }

    /**
     * Creates a folder, within a parent folder.
     * If no parent folder is given, a root level folder will be created
     *
     * @param string $newFolderName The new folder
     * @param string $parentFolderIdentifier The parent folder
     * @param bool $recursive Flag for creating recursive
     *
     * @return string the Identifier of the new folder
     */
    public function createFolder($newFolderName, $parentFolderIdentifier = '', $recursive = false)
    {
        $this->writeLog('createFolder(' . $newFolderName . ',' . $parentFolderIdentifier . ',' . $recursive . ')');
        if ($parentFolderIdentifier == '') {
            $newRecord['parent'] = 1;
        } else {
            $folderInfo = $this->getFolderInfoByIdentifier($parentFolderIdentifier);
            $newRecord['parent'] = $folderInfo['id'];
        }
        $parentPath = $this->getFullPath($newRecord['parent']);
        $newRecord['path'] = uniqid(self::FILE_FOLDER_PREFIX);
        mkdir($parentPath . '/' . $newRecord['path']);
        $newRecord['identifier'] = $this->generateUuid();
        $newRecord['name'] = $newFolderName;
        $newRecord['isDirectory'] = 1;
        $newRecord['size'] = 0;
        $newRecord['creation_date'] = time();
        $newRecord['modification_date'] = time();
        // TODO This has to be removed after fixing the core
        if ($newRecord['name'] == '_processed_') {
            $newRecord['identifier'] = $newRecord['name'];
        }
        $insertQueryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
        $insertQueryBuilder->insert(self::TABLE_NAME)
            ->values($newRecord)
            ->execute();

        if ($newFolderName == '_processed_') {
            // When we have to create the _processed_ folder,
            // we have also to update the driver configuration
            $updateConnection = $this->connectionPool->getConnectionForTable('sys_file_storage');
            $updateConnection->update('sys_file_storage',
                ['processingfolder' => $newRecord['name']],
                ['uid' => (int)$this->storageUid]);
        }
        return $newRecord['identifier'];
    }

    /**
     * Renames a folder in this storage.
     *
     * @param string $folderIdentifier The folder to be renamed
     * @param string $newName The new folder name
     *
     * @return array A map of old to new file identifiers of all affected resources
     */
    public function renameFolder($folderIdentifier, $newName)
    {
        $this->writeLog('renameFolder(' . $folderIdentifier . ',' . $newName . ')');
        $this->writeLog('	!!! not yet implemented');
        return array();
    }

    /**
     * Removes a folder in filesystem.
     *
     * @param string $folderIdentifier The folder to be deleted
     * @param bool $deleteRecursively Flag for deleting recursive
     *
     * @return bool
     */
    public function deleteFolder($folderIdentifier, $deleteRecursively = false)
    {
        $this->writeLog('deleteFolder(' . $folderIdentifier . ',' . $deleteRecursively . ')');
        if (!$this->isFolderEmpty($folderIdentifier)) {
            $fileList = $this->getFilesInFolder($folderIdentifier);
            foreach ($fileList as $fileIdentifier) {
                if (!$this->deleteFile($fileIdentifier)) {
                    return false;
                }
            }
            if ($deleteRecursively === true) {
                $folderList = $this->getFoldersInFolder($folderIdentifier);
                foreach ($folderList as $subFolderIdentifier) {
                    if (!$this->deleteFolder($subFolderIdentifier, true)) {
                        return false;
                    }
                }
            }
            if (!$this->isFolderEmpty($folderIdentifier)) {
                return false;
            }
        }
        $folderInfo = $this->getFolderInfoByIdentifier($folderIdentifier);
        $folderPath = $this->getFullPath($folderInfo['id']);
        $result = rmdir($folderPath);
        if ($result) {
            $deleteQueryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
            $deleteQueryBuilder->delete(self::TABLE_NAME)
                ->where($this->queryBuilder->expr()->eq('uid', intval($folderInfo['id'])))
                ->execute();
            return true;
        }
        return false;
    }

    /**
     * Checks if a file exists.
     *
     * @param string $fileIdentifier The identifier of the file to be checked
     *
     * @return bool
     */
    public function fileExists($fileIdentifier)
    {
        $this->writeLog('fileExists(' . $fileIdentifier . ')');
        $row = $this->getInfoByIdentifier($fileIdentifier);
        if ($row === false) {
            return false;
        } else {
            if ($row['parent'] !== 0) {
                $parentPath = $this->getFullPath($row['parent']);
            } else {
                $parentPath = $this->configuration['rootPath'];
            }
            return is_file($parentPath . '/' . $row['path']);
        }
    }

    /**
     * Checks if a folder exists.
     *
     * @param string $folderIdentifier The identifier of the folder to be checked
     *
     * @return bool
     * @throws Exception\InvalidPathException
     */
    public function folderExists($folderIdentifier)
    {
        $this->writeLog('folderExists(' . $folderIdentifier . ')');
        $row = $this->getInfoByIdentifier($folderIdentifier);
        if ($row === false) {
            return false;
        } else {
            if ($folderIdentifier === self::ROOT_IDENTIFIER) {
                $absoluteFilePath = $row['path'];
            } else {
                $absoluteFilePath = $this->getAbsolutePath($row['path']);
            }
            return is_dir($absoluteFilePath);
        }
    }

    /**
     * Checks if a folder contains files and (if supported) other folders.
     *
     * @param string $folderIdentifier The identifier of the folder to be checked
     *
     * @return bool TRUE if there are no files and folders within $folder
     */
    public function isFolderEmpty($folderIdentifier)
    {
        $this->writeLog('isFolderEmpty(' . $folderIdentifier . ')');
        $folderInfo = $this->getFolderInfoByIdentifier($folderIdentifier);
        $rows = $this->queryBuilder->select('path', 'name')
            ->from(self::TABLE_NAME)
            ->where($this->queryBuilder->expr()->eq('parent', intval($folderInfo['id'])))
            ->execute()
            ->fetchAll();
        return (count($rows) == 0);
    }

    /**
     * Adds a file from the local server hard disk to a given path in TYPO3s
     * virtual file system. This assumes that the local file exists, so no
     * further check is done here! After a successful operation the original
     * file must not exist anymore.
     *
     * @param string $localFilePath The file to be added (within PATH_site)
     * @param string $targetFolderIdentifier The target folder
     * @param string $newFileName Optional, if not given original name is used
     * @param bool $removeOriginal If set the original file will be removed
     *                                after successful operation
     *
     * @return string the identifier of the new file
     */
    public function addFile($localFilePath, $targetFolderIdentifier, $newFileName = '', $removeOriginal = true)
    {
        $this->writeLog('addFile(' . $localFilePath . ',' . $targetFolderIdentifier . ',' . $newFileName . ',' . $removeOriginal . ')');
        $folderInfo = $this->getFolderInfoByIdentifier($targetFolderIdentifier);
        $newRecord['parent'] = $folderInfo['id'];
        $parentPath = $this->getFullPath($newRecord['parent']);
        $newRecord['path'] = uniqid(self::FILE_FOLDER_PREFIX);
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
        $newRecord['size'] = 0; //TODO Get file size
        $newRecord['creation_date'] = time();
        $newRecord['modification_date'] = time();
        $insertQueryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
        $insertQueryBuilder->insert(self::TABLE_NAME)
            ->values($newRecord)
            ->execute();
        return $newRecord['identifier'];
    }

    /**
     * Creates a new (empty) file and returns the identifier.
     *
     * @param string $fileName The file to be created
     * @param string $parentFolderIdentifier The parent folder
     *
     * @return string
     */
    public function createFile($fileName, $parentFolderIdentifier)
    {
        $this->writeLog('createFile(' . $fileName . ',' . $parentFolderIdentifier . ')');
        $folderInfo = $this->getFolderInfoByIdentifier($parentFolderIdentifier);
        $newRecord['parent'] = $folderInfo['id'];
        $parentPath = $this->getFullPath($newRecord['parent']);
        $newRecord['path'] = uniqid(self::FILE_FOLDER_PREFIX);
        touch($parentPath . '/' . $newRecord['path']);
        $newRecord['identifier'] = $this->generateUuid();
        $newRecord['name'] = $fileName;
        $newRecord['isDirectory'] = 0;
        $newRecord['size'] = 0;
        $newRecord['creation_date'] = time();
        $newRecord['modification_date'] = time();
        $insertQueryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
        $insertQueryBuilder->insert(self::TABLE_NAME)
            ->values($newRecord)
            ->execute();
        return $newRecord['identifier'];
    }

    /**
     * Copies a file *within* the current storage.
     * Note that this is only about an inner storage copy action,
     * where a file is just copied to another folder in the same storage.
     *
     * @param string $fileIdentifier The file to be copied
     * @param string $targetFolderIdentifier The target folder
     * @param string $fileName The file name of the copied file
     *
     * @return string the Identifier of the new file
     */
    public function copyFileWithinStorage($fileIdentifier, $targetFolderIdentifier, $fileName)
    {
        $this->writeLog('copyFileWithinStorage(' . $fileIdentifier . ',' . $targetFolderIdentifier . ',' . $fileName . ')');
        $fileInfo = $this->getFileInfoByIdentifier($fileIdentifier);
        $fullPath = $this->getFullPath(intval($fileInfo['id']));
        return $this->addFile($fullPath, $targetFolderIdentifier, $fileName, false);
    }

    /**
     * Renames a file in this storage.
     *
     * @param string $fileIdentifier The file to be renamed
     * @param string $newName The target path (including the file name!)
     *
     * @return string The identifier of the file after renaming
     */
    public function renameFile($fileIdentifier, $newName)
    {
        $this->writeLog('renameFile(' . $fileIdentifier . ',' . $newName . ')');
        $updateConnection = $this->connectionPool->getConnectionForTable(self::TABLE_NAME);
        $updateConnection->update(self::TABLE_NAME,
            ['name' => $newName],
            ['identifier' => $fileIdentifier]);
        return $fileIdentifier;
    }

    /**
     * Replaces a file with file in local file system.
     *
     * @param string $fileIdentifier The file to be replaced
     * @param string $localFilePath The new file
     *
     * @return bool TRUE if the operation succeeded
     */
    public function replaceFile($fileIdentifier, $localFilePath)
    {
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
     * @param string $fileIdentifier The file to be deleted
     *
     * @return bool TRUE if deleting the file succeeded
     */
    public function deleteFile($fileIdentifier)
    {
        $this->writeLog('deleteFile(' . $fileIdentifier . ')');
        $fileInfo = $this->getFileInfoByIdentifier($fileIdentifier);
        $fullPath = $this->getFullPath(intval($fileInfo['id']));
        $result = unlink($fullPath);
        if ($result) {
            $deleteQueryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
            $deleteQueryBuilder->delete(self::TABLE_NAME)
                ->where($this->queryBuilder->expr()->eq('id', intval($fileInfo['id'])))
                ->execute();
            return true;
        }
        return false;
    }

    /**
     * Creates a hash for a file.
     *
     * @param string $fileIdentifier The file
     * @param string $hashAlgorithm The hash algorithm to use
     *
     * @return string
     * @throws \TYPO3\CMS\Core\Resource\Exception\InvalidPathException
     */
    public function hash($fileIdentifier, $hashAlgorithm)
    {
        $this->writeLog('hash(' . $fileIdentifier . ',' . $hashAlgorithm . ')');
        return $this->hashIdentifier($fileIdentifier);
    }


    /**
     * Moves a file *within* the current storage.
     * Note that this is only about an inner-storage move action,
     * where a file is just moved to another folder in the same storage.
     *
     * @param string $fileIdentifier The file to be removed
     * @param string $targetFolderIdentifier The target folder
     * @param string $newFileName The new file name
     *
     * @return string
     */
    public function moveFileWithinStorage($fileIdentifier, $targetFolderIdentifier, $newFileName)
    {
        $this->writeLog('moveFileWithinStorage(' . $fileIdentifier . ',' . $targetFolderIdentifier . ',' . $newFileName . ')');
        $fileInfo = $this->getFileInfoByIdentifier($fileIdentifier);
        $fullPath = $this->getFullPath(intval($fileInfo['id']));
        $result = $this->addFile($fullPath, $targetFolderIdentifier, $newFileName, false);
        $this->deleteFile($fileIdentifier);
        return $result;
    }


    /**
     * Folder equivalent to moveFileWithinStorage().
     *
     * @param string $sourceFolderIdentifier The folder to be moved
     * @param string $targetFolderIdentifier The target folder
     * @param string $newFolderName The new folder name
     *
     * @return array All files which are affected, map of old => new file identifiers
     */
    public function moveFolderWithinStorage($sourceFolderIdentifier, $targetFolderIdentifier, $newFolderName)
    {
        $this->writeLog('moveFolderWithinStorage(' . $sourceFolderIdentifier . ',' . $targetFolderIdentifier .
            ',' . $newFolderName . ')');
        $this->writeLog('	!!! not yet implemented');
        return array();
    }

    /**
     * Folder equivalent to copyFileWithinStorage().
     *
     * @param string $sourceFolderIdentifier The folder to be copied
     * @param string $targetFolderIdentifier The target folder
     * @param string $newFolderName The new folder name
     *
     * @return bool
     */
    public function copyFolderWithinStorage($sourceFolderIdentifier, $targetFolderIdentifier, $newFolderName)
    {
        $this->writeLog('copyFolderWithinStorage(' . $sourceFolderIdentifier . ',' . $targetFolderIdentifier .
            ',' . $newFolderName . ')');
        $this->writeLog('	!!! not yet implemented');
        return false;
    }

    /**
     * Returns the contents of a file. Beware that this requires to load the
     * complete file into memory and also may require fetching the file from an
     * external location. So this might be an expensive operation (both in terms
     * of processing resources and money) for large files.
     *
     * @param string $fileIdentifier The identifier of the file
     *
     * @return string The file contents
     */
    public function getFileContents($fileIdentifier)
    {
        $this->writeLog('getFileContents(' . $fileIdentifier . ')');
        $fileInfo = $this->getFileInfoByIdentifier($fileIdentifier);
        $fullPath = $this->getFullPath(intval($fileInfo['id']));
        return file_get_contents($fullPath);
    }

    /**
     * Sets the contents of a file to the specified value.
     *
     * @param string $fileIdentifier The identifier of the file
     * @param string $contents The new file contents
     *
     * @return int The number of bytes written to the file
     */
    public function setFileContents($fileIdentifier, $contents)
    {
        $this->writeLog('setFileContents(' . $fileIdentifier . ',' . $contents . ')');
        $fileInfo = $this->getFileInfoByIdentifier($fileIdentifier);
        $fullPath = $this->getFullPath(intval($fileInfo['id']));
        // Get the file size and save it to DB
        $fileSize = file_put_contents($fullPath, $contents);
        $updateConnection = $this->connectionPool->getConnectionForTable(self::TABLE_NAME);
        $updateConnection->update(self::TABLE_NAME,
            ['size' => $fileSize, 'modification_date' => time()],
            ['identifier' => $fileIdentifier]);
        return $fileSize;
    }

    /**
     * Checks if a file inside a folder exists
     *
     * @param string $fileName The name of the file to be checked for existence
     * @param string $folderIdentifier The folder to be checked
     *
     * @return bool
     */
    public function fileExistsInFolder($fileName, $folderIdentifier)
    {
        $this->writeLog('fileExistsInFolder(' . $fileName . ',' . $folderIdentifier . ')');
        $folderInfo = $this->getFolderInfoByIdentifier($folderIdentifier);
        $rows = $this->queryBuilder->select('identifier', 'path', 'name', 'id', 'parent')
            ->from(self::TABLE_NAME)
            ->where($this->queryBuilder->expr()->eq('name', $this->queryBuilder->createNamedParameter($fileName, \PDO::PARAM_STR)))
            ->andWhere($this->queryBuilder->expr()->eq('isDirectory', 0))
            ->andWhere($this->queryBuilder->expr()->eq('parent', intval($folderInfo['id'])))
            ->execute()
            ->fetchAll();
        return (count($rows) > 0);
    }

    /**
     * Checks if a folder inside a folder exists.
     *
     * @param string $folderName The name of the folder to be checked for existence
     * @param string $folderIdentifier The folder to be checked
     *
     * @return bool
     */
    public function folderExistsInFolder($folderName, $folderIdentifier)
    {
        $this->writeLog('folderExistsInFolder(' . $folderName . ',' . $folderIdentifier . ')');
        $folderInfo = $this->getFolderInfoByIdentifier($folderIdentifier);
        $rows = $this->queryBuilder->select('identifier', 'path', 'name', 'id', 'parent')
            ->from(self::TABLE_NAME)
            ->where($this->queryBuilder->expr()->eq('name', $this->queryBuilder->createNamedParameter($folderName, \PDO::PARAM_STR)))
            ->andWhere($this->queryBuilder->expr()->eq('isDirectory', 1))
            ->andWhere($this->queryBuilder->expr()->eq('parent', intval($folderInfo['id'])))
            ->execute()
            ->fetchAll();
        return (count($rows) > 0);
    }

    /**
     * Returns a path to a local copy of a file for processing it. When changing the
     * file, you have to take care of replacing the current version yourself!
     *
     * @param string $fileIdentifier The file to get a local copy from
     * @param bool $writable Set this to FALSE if you only need the file for read
     *                       operations. This might speed up things, e.g. by using
     *                       a cached local version. Never modify the file if you
     *                       have set this flag!
     *
     * @return string The path to the file on the local disk
     */
    public function getFileForLocalProcessing($fileIdentifier, $writable = true)
    {
        $this->writeLog('getFileForLocalProcessing(' . $fileIdentifier . ',' . $writable . ')');
        if ($writable === false) {
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
     * @param string $identifier The file or folder
     *
     * @return array
     */
    public function getPermissions($identifier)
    {
        $this->writeLog('getPermissions(' . $identifier . ')');
        return array('r' => true, 'w' => true);
    }

    /**
     * Returns the absolute path of a file or folder.
     *
     * @param string $fileOrFolderIdentifier
     *
     * @return string
     * @throws Exception\InvalidPathException
     * @throws \TYPO3\CMS\Core\Resource\Exception\InvalidPathException
     */
    protected function getAbsolutePath($fileOrFolderIdentifier)
    {
        $relativeFilePath = ltrim($this->canonicalizeAndCheckFileIdentifier($fileOrFolderIdentifier), '/');
        $path = $this->configuration['rootPath'] . '/' . $relativeFilePath;
        return $path;
    }

    /**
     * Directly output the contents of the file to the output
     * buffer. Should not take care of header files or flushing
     * buffer before. Will be taken care of by the Storage.
     *
     * @param string $identifier The identifier of the file to be dumped
     *
     * @return void
     * @throws \TYPO3\CMS\Core\Resource\Exception\InvalidPathException
     */
    public function dumpFileContents($identifier)
    {
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
     * @param string $folderIdentifier The identifier of the folder to be checked
     * @param string $identifier Identifier to be checked against $folderIdentifier
     *
     * @return bool TRUE if $content is within or matches $folderIdentifier
     */
    public function isWithin($folderIdentifier, $identifier)
    {
        $this->writeLog('isWithin(' . $folderIdentifier . ',' . $identifier . ')');
        $folderInfo = $this->getFolderInfoByIdentifier($folderIdentifier);
        $resultRecord = $this->getInfoByIdentifier($folderIdentifier);
        return (($resultRecord !== false) && ($folderInfo['id'] === $resultRecord['parent']));
    }

    /**
     * Returns information about a file.
     *
     * @param string $fileIdentifier     The identifier of the file to get information from
     * @param array $propertiesToExtract Array of properties which are be extracted
     *                                   If empty all will be extracted
     *
     * @return array
     */
    public function getFileInfoByIdentifier($fileIdentifier, array $propertiesToExtract = array())
    {
        $this->writeLog('getFileInfoByIdentifier(' . $fileIdentifier . ',' . $propertiesToExtract . ')');
        $fileRecord = $this->queryBuilder->select('identifier', 'path', 'name', 'id', 'parent', 'size', 'creation_date', 'modification_date')
            ->from(self::TABLE_NAME)
            ->where($this->queryBuilder->expr()->eq('identifier', $this->queryBuilder->createNamedParameter($fileIdentifier, \PDO::PARAM_STR)))
            ->andWhere($this->queryBuilder->expr()->eq('isDirectory', 0))
            ->execute()
            ->fetch();
        if ($fileRecord === false) {
            return array();
        } else {
            return array(
                'identifier' => $fileRecord['identifier'],
                'name' => $fileRecord['name'],
                'path' => $fileRecord['path'],
                'id' => $fileRecord['id'],
                'parent' => $fileRecord['parent'],
                'size' => $fileRecord['size'],
                'creation_date' => $fileRecord['creation_date'],
                'modification_date' => $fileRecord['modification_date'],
                'storage' => $this->storageUid
            );
        }
    }

    /**
     * Returns information about a folder.
     *
     * @param string $folderIdentifier The identifier of the folder to get information from
     *
     * @return array
     */
    public function getFolderInfoByIdentifier($folderIdentifier)
    {
        $this->writeLog('getFolderInfoByIdentifier(' . $folderIdentifier . ')');
        $folderRecord = $this->queryBuilder->select('identifier', 'path', 'name', 'id', 'parent', 'size', 'creation_date', 'modification_date')
            ->from(self::TABLE_NAME)
            ->where($this->queryBuilder->expr()->eq('identifier', $this->queryBuilder->createNamedParameter($folderIdentifier, \PDO::PARAM_STR)))
            ->andWhere($this->queryBuilder->expr()->eq('isDirectory', 1))
            ->execute()
            ->fetch();
        if ($folderRecord === false) {
            return array();
        } else {
            return array(
                'identifier' => $folderRecord['identifier'],
                'name' => $folderRecord['name'],
                'path' => $folderRecord['path'],
                'id' => $folderRecord['id'],
                'parent' => $folderRecord['parent'],
                'size' => $folderRecord['size'],
                'creation_date' => $folderRecord['creation_date'],
                'modification_date' => $folderRecord['modification_date'],
                'storage' => $this->storageUid
            );
        }
    }

    /**
     * Returns the identifier of a file inside the folder
     *
     * @param string $fileName The name of th file to check
     * @param string $folderIdentifier The identifier of the folder to check
     * @return string file identifier
     */
    public function getFileInFolder($fileName, $folderIdentifier)
    {
        $folderInfo = $this->getFolderInfoByIdentifier($folderIdentifier);
        $folderId = intval($folderInfo['id']);
        $fileRecord = $this->queryBuilder->select('identifier', 'path', 'name')
            ->from(self::TABLE_NAME)
            ->where($this->queryBuilder->expr()->eq('parent', $folderId))
            ->andWhere($this->queryBuilder->expr()->eq('isDirectory', 0))
            ->andWhere($this->queryBuilder->expr()->eq('name', $this->queryBuilder->createNamedParameter($fileName, \PDO::PARAM_STR)))
            ->execute()
            ->fetch();
        if ($fileRecord === false) {
            return '';
        } else {
            return $fileRecord['identifier'];
        }
    }

    /**
     * Returns a list of files inside the specified path
     *
     * @param string $folderIdentifier The identifier of the folder to get the file list from
     * @param int $start Index to start with the list
     * @param int $numberOfItems Number of items to get in the list
     * @param bool $recursive Flag to get a recursive list
     * @param array $filenameFilterCallbacks Callbacks for filtering the items
     * @param string $sort Property name used to sort the items.
     *                     Among them may be: '' (empty, no sorting), name,
     *                     fileext, size, tstamp and rw.
     *                     If a driver does not support the given property, it
     *                     should fall back to "name".
     * @param bool $sortRev TRUE to indicate reverse sorting (last to first)
     *
     * @return array of FileIdentifiers
     */
    public function getFilesInFolder(
        $folderIdentifier,
        $start = 0,
        $numberOfItems = 0,
        $recursive = false,
        array $filenameFilterCallbacks = array(),
        $sort = '',
        $sortRev = false
    ) {
        $this->writeLog('getFilesInFolder(' . $folderIdentifier . ',' . $start . ',' . $numberOfItems . ',' .
            $recursive . ',' . $filenameFilterCallbacks . ',' . $sort . ',' . $sortRev . ')');
        $this->writeLog('	!!! $start, $numberOfItems, $recursive, $filenameFilterCallbacks, $sort and $sortRev not yet implemented');
        $folderInfo = $this->getFolderInfoByIdentifier($folderIdentifier);
        $folderId = intval($folderInfo['id']);
        $files = array();
        $fileRecords = $this->queryBuilder->select('identifier', 'path', 'name')
            ->from(self::TABLE_NAME)
            ->where($this->queryBuilder->expr()->eq('parent', $folderId))
            ->andWhere($this->queryBuilder->expr()->eq('isDirectory', 0))
            ->execute()
            ->fetchAll();
        foreach ($fileRecords as $resultRecord) {
            $files[$resultRecord['identifier']] = $resultRecord['identifier'];
        }
        return $files;
    }

    /**
     * Returns the identifier of a folder inside the folder
     *
     * @param string $folderName The name of the target folder
     * @param string $folderIdentifier
     * @return string folder identifier
     */
    public function getFolderInFolder($folderName, $folderIdentifier)
    {
        $folderInfo = $this->getFolderInfoByIdentifier($folderIdentifier);
        $folderId = intval($folderInfo['id']);
        $folderRecord = $this->queryBuilder->select('identifier', 'path', 'name')
            ->from(self::TABLE_NAME)
            ->where($this->queryBuilder->expr()->eq('parent', $folderId))
            ->andWhere($this->queryBuilder->expr()->eq('isDirectory', 1))
            ->andWhere($this->queryBuilder->expr()->eq('name', $folderName))
            ->execute()
            ->fetch();
        if ($folderRecord === false) {
            return '';
        } else {
            return $folderRecord['identifier'];
        }
    }

    /**
     * Returns a list of folders inside the specified path
     *
     * @param string $folderIdentifier The folder to get the folder list from
     * @param int $start Index to start with the list
     * @param int $numberOfItems Number of items to get in the list
     * @param bool $recursive Flag to get a recursive list
     * @param array $folderNameFilterCallbacks Callbacks for filtering the items
     * @param string $sort Property name used to sort the items.
     *                     Among them may be: '' (empty, no sorting), name,
     *                     fileext, size, tstamp and rw.
     *                     If a driver does not support the given property, it
     *                     should fall back to "name".
     * @param bool $sortRev TRUE to indicate reverse sorting (last to first)
     *
     * @return array of Folder Identifier
     */
    public function getFoldersInFolder(
        $folderIdentifier,
        $start = 0,
        $numberOfItems = 0,
        $recursive = false,
        array $folderNameFilterCallbacks = array(),
        $sort = '',
        $sortRev = false
    ) {
        $this->writeLog('getFoldersInFolder(' . $folderIdentifier . ',' . $start . ',' . $numberOfItems . ',' .
            $recursive . ',' . $folderNameFilterCallbacks . ',' . $sort . ',' . $sortRev . ')');
        $this->writeLog('	!!! $start, $numberOfItems, $recursive, $filenameFilterCallbacks, $sort and $sortRev not yet implemented');
        $folderInfo = $this->getFolderInfoByIdentifier($folderIdentifier);
        $folderId = intval($folderInfo['id']);
        $folders = array();
        $folderRecords = $this->queryBuilder->select('identifier', 'path', 'name')
            ->from(self::TABLE_NAME)
            ->where($this->queryBuilder->expr()->eq('parent', $folderId))
            ->andWhere($this->queryBuilder->expr()->eq('isDirectory', 1))
            ->execute()
            ->fetchAll();

        foreach ($folderRecords as $resultRecord) {
            $folders[$resultRecord['identifier']] = $resultRecord['identifier'];
        }
        return $folders;
    }

    /**
     * Returns the number of files inside the specified path
     *
     * @param string $folderIdentifier The identifier of the folder to be checked
     * @param bool $recursive Flag to get a recursive count
     * @param array $filenameFilterCallbacks Callbacks for filtering the items
     *
     * @return int Number of files in folder
     */
    public function countFilesInFolder($folderIdentifier, $recursive = false, array $filenameFilterCallbacks = array())
    {
        $this->writeLog('countFilesInFolder(' . $folderIdentifier . ',' . $recursive . ',' . $filenameFilterCallbacks . ')');
        $folderInfo = $this->getFolderInfoByIdentifier($folderIdentifier);
        $folderId = intval($folderInfo['id']);
        $fileRecords = $this->queryBuilder->select('identifier', 'path', 'name')
            ->from(self::TABLE_NAME)
            ->where($this->queryBuilder->expr()->eq('parent', $folderId))
            ->andWhere($this->queryBuilder->expr()->eq('isDirectory', 0))
            ->execute()
            ->fetchAll();
        return count($fileRecords);
    }

    /**
     * Returns the number of folders inside the specified path
     *
     * @param string $folderIdentifier The identifier of the folder to be checked
     * @param bool $recursive Flag to get a recursive list
     * @param array $folderNameFilterCallbacks Callbacks for filtering the items
     *
     * @return int Number of folders in folder
     */
    public function countFoldersInFolder(
        $folderIdentifier,
        $recursive = false,
        array $folderNameFilterCallbacks = array()
    ) {
        $this->writeLog('countFoldersInFolder(' . $folderIdentifier . ',' . $recursive . ',' . $folderNameFilterCallbacks . ')');
        $folderInfo = $this->getFolderInfoByIdentifier($folderIdentifier);
        $folderId = intval($folderInfo['id']);
        $folderRecords = $this->queryBuilder->select('identifier', 'path', 'name')
            ->from(self::TABLE_NAME)
            ->where($this->queryBuilder->expr()->eq('parent', $folderId))
            ->andWhere($this->queryBuilder->expr()->eq('isDirectory', 1))
            ->execute()
            ->fetchAll();
        return count($folderRecords);
    }

    /**
     * Copies a file to a temporary path and returns that path.
     *
     * @param string $fileIdentifier The file to be copied
     *
     * @return string The temporary path
     *
     * @throws \RuntimeException Thrown if file could not be copied
     */
    protected function copyFileToTemporaryPath($fileIdentifier)
    {
        $fileInfo = $this->getFileInfoByIdentifier($fileIdentifier);
        $sourcePath = $this->getFullPath(intval($fileInfo['id']));
        $temporaryPath = $this->getTemporaryPathForFile($fileIdentifier);
        $result = copy($sourcePath, $temporaryPath);
        touch($temporaryPath, filemtime($sourcePath));
        if ($result === false) {
            throw new \RuntimeException(
                'Copying file "' . $fileIdentifier . '" to temporary path "' . $temporaryPath . '" failed.',
                1320577649
            );
        }
        return $temporaryPath;
    }

    /**
     * Get the full path of a file or folder
     *
     * @param int $id The id of a file or folder to get the full path from
     *
     * @return string The full path to the file or folder
     */
    protected function getFullPath($id)
    {
        $fullPath = '';
        $parentId = $id;
        while ($parentId > 0) {
            $parentRecord = $this->queryBuilder->select('path', 'name', 'id', 'parent')
                ->from(self::TABLE_NAME)
                ->where($this->queryBuilder->expr()->eq('id', $parentId))
                ->execute()
                ->fetch();
            if (strlen($fullPath) > 0) {
                $fullPath = $parentRecord['path'] . '/' . $fullPath;
            } else {
                $fullPath = $parentRecord['path'];
            }
            $parentId = $parentRecord['parent'];
        }
        return $fullPath;
    }

    /**
     * Create a unique identifier
     *
     * @return string A unique identifier
     */
    protected function generateUuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Write a message to the log file, if a log file is set in the configuration
     *
     * @param string $logMessage The message to be logged
     *
     * @return void
     */
    protected function writeLog($logMessage)
    {
        $fileName = $this->configuration['logFileName'];
        if ($fileName == '') {
            return;
        }
        if (!is_writable($fileName)) {
            touch($fileName);
        }
        if ($this->fileHandle == null) {
            $this->fileHandle = fopen($fileName, 'w');
        }
        fwrite($this->fileHandle, $logMessage . "\n");
    }

}
