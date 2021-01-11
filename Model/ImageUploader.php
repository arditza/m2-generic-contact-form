<?php

namespace Azra\GenericForm\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 *
 */
class ImageUploader {

	/**
	 *
	 */
	const CONTACTS_IMAGE_UPLOAD_PATH = "genericform";

	/**
	 * @var Magento\Framework\Filesystem\Io\File
	 */
	protected $_ioFile;

	/**
	 * @var Magento\Framework\Filesystem
	 */
	protected $_filesystem;

	/**
	 * @var Magento\Framework\Image\AdapterFactory
	 */
	protected $_adapterFactory;

	/**
	 * @var Magento\MediaStorage\Model\File\UploaderFactory
	 */
	protected $_fileUploaderFactory;

	/**
	 * class constructor
	 *
	 * @param \Magento\Framework\Filesystem                    $filesystem
	 * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
	 * @param \Magento\Framework\Image\AdapterFactory          $adapterFactory
	 * @param \Magento\Framework\Filesystem\Io\File            $ioFile
	 */
	public function __construct(
		\Magento\Framework\Filesystem $filesystem,
		\Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
		\Magento\Framework\Image\AdapterFactory $adapterFactory,
		\Magento\Framework\Filesystem\Io\File $ioFile
	) {
		$this->_ioFile = $ioFile;
		$this->_filesystem = $filesystem;
		$this->_adapterFactory = $adapterFactory;
		$this->_fileUploaderFactory = $fileUploaderFactory;
	}

	/**
	 * uploads contact image to specific path
	 *
	 * @param  array  $file
	 *
	 * @return array
	 */
	public function execute(array $file) {
		$path = $this->_filesystem->getDirectoryRead(
			DirectoryList::MEDIA
		)->getAbsolutePath(
			self::CONTACTS_IMAGE_UPLOAD_PATH
		);
		$this->_ioFile->checkAndCreateFolder($path);

		/** @var $uploader Uploader */
		$uploader = $this->_fileUploaderFactory->create(
			['fileId' => $file]
		);
		$imageAdapter = $this->_adapterFactory->create();
		// extensions , size
		// $uploader->addValidateCallback('catalog_product_image', $imageAdapter, 'validateUploadFile');
		$uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'pdf', 'doc', 'docx']);
		$uploader->setAllowRenameFiles(true);
		$uploader->setFilesDispersion(true);
		$result = $uploader->save($path);

		return $result;
	}
}
