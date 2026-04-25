<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Quickrfq
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\Quickrfq\Model\Attachment;

use Lof\Quickrfq\Model\AttachmentFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Psr\Log\LoggerInterface;

/**
 * Handler for uploading files.
 */
class UploadHandler
{
    /**
     *
     */
    const BASE64_ENCODED_DATA = 'base64_encoded_data';

    /**
     *
     */
    const ATTACHMENTS_FOLDER = 'lof_quickrfq';

    /**
     * File system
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var AttachmentFactory
     */
    protected $attachmentFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * UploadHandler constructor.
     *
     * @param Filesystem        $filesystem
     * @param UploaderFactory   $uploaderFactory
     * @param AttachmentFactory $attachmentFactory
     * @param LoggerInterface   $logger
     */
    public function __construct(
        Filesystem $filesystem,
        \Lof\Quickrfq\Model\Attachment\UploaderFactory $uploaderFactory,
        AttachmentFactory $attachmentFactory,
        LoggerInterface $logger
    ) {
        $this->filesystem        = $filesystem;
        $this->uploaderFactory   = $uploaderFactory;
        $this->attachmentFactory = $attachmentFactory;
        $this->logger            = $logger;
    }

    /**
     * Save file and create attachment for comment.
     */
    public function process($file, $quoteId)
    {
        $fileContent  = base64_decode($file->getData(UploadHandler::BASE64_ENCODED_DATA), true);
        $tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
        $tmpFileName  = substr(md5(rand()), 0, 7) . '.' . $file->getName();
        $tmpDirectory->writeFile($tmpFileName, $fileContent);

        $fileAttributes = [
            'tmp_name' => $tmpDirectory->getAbsolutePath() . $tmpFileName,
            'name'     => $file->getName(),
        ];
        /** @var \Lof\Quickrfq\Model\Attachment\Uploader $uploader */
        $uploader = $this->uploaderFactory->create();
        $uploader->processFileAttributes($fileAttributes);
        $uploader->addValidateCallback('nameLength', $uploader, 'validateNameLength');
        $uploader->addValidateCallback('size', $uploader, 'validateSize');
        $uploader->setAllowRenameFiles(true)
                 ->setFilesDispersion(true)
                 ->setAllowCreateFolders(true);
        $path = $this->filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(UploadHandler::ATTACHMENTS_FOLDER);
        $data = $uploader->save($path);

        if (isset($data['name']) && isset($data['file'])) {
            /** @var \Lof\Quickrfq\Model\AttachmentFactory $attachment */
            $attachment = $this->attachmentFactory->create();
            $attachment->setQuickrfqId($quoteId)
                       ->setFileName($data['name'])
                       ->setFilePath($data['file'])
                       ->setFileType($file->getType())
                       ->save();
        }
    }

    public function processPwa(array $file, $quoteId)
    {
        try {
            $fileName = $file['file_name'] ?? null;
            $fileType = $file['file_type'] ?? null;
            $base64   = $file['base64'] ?? null;

            if (!$fileName || !$base64) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid attachment data')
                );
            }

            $fileContent = base64_decode($base64, true);

            if ($fileContent === false) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid base64 file content')
                );
            }

            $tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
            $tmpFileName  = substr(md5(uniqid()), 0, 7) . '_' . $fileName;

            $tmpDirectory->writeFile($tmpFileName, $fileContent);

            $fileAttributes = [
                'tmp_name' => $tmpDirectory->getAbsolutePath() . $tmpFileName,
                'name'     => $fileName,
            ];

            /** @var \Lof\Quickrfq\Model\Attachment\Uploader $uploader */
            $uploader = $this->uploaderFactory->create();
            $uploader->processFileAttributes($fileAttributes);
            $uploader->addValidateCallback('nameLength', $uploader, 'validateNameLength');
            $uploader->addValidateCallback('size', $uploader, 'validateSize');
            $uploader->setAllowRenameFiles(true)
                    ->setFilesDispersion(true)
                    ->setAllowCreateFolders(true);

            $path = $this->filesystem->getDirectoryRead(
                DirectoryList::MEDIA
            )->getAbsolutePath(self::ATTACHMENTS_FOLDER);

            $data = $uploader->save($path);

            if (!empty($data['name']) && !empty($data['file'])) {
                $attachment = $this->attachmentFactory->create();
                $attachment->setQuickrfqId($quoteId)
                        ->setFileName($data['name'])
                        ->setFilePath($data['file'])
                        ->setFileType($fileType)
                        ->save();
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw $e;
        }
    }
}
