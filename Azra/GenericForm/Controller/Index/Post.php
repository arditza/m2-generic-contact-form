<?php

namespace Azra\GenericForm\Controller\Index;

use Azra\GenericForm\Model\ImageUploader;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Psr\Log\LoggerInterface;

class Post extends \Magento\Framework\App\Action\Action {

	/**
	 * @var \Magento\Framework\Mail\Template\TransportBuilder
	 */
	protected $transportBuilder;

	/**
	 * @var \Magento\Framework\Controller\ResultFactory
	 */
	protected $resultFactory;

	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	private $storeManager;

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	private $logger;

	/**
	 * @var Azra\GenericForm\Helper\Data
	 */
	protected $helperData;

	/**
	 * @var null
	 */
	protected $_isProductForm = null;

	/**
	 * @var null
	 */
	protected $_isRolexForm = null;

	/**
	 * initialize class depedencies
	 *
	 * @param Context                                           $context
	 * @param \Psr\Log\LoggerInterface                          $logger
	 * @param StateInterface                                    $inlineTranslation
	 * @param \Azra\GenericForm\Helper\Data                   $helperData
	 * @param \Magento\Store\Model\StoreManagerInterface        $storeManager
	 * @param \Azra\GenericForm\Model\ContactFactory          $contactFactory
	 * @param \Magento\Framework\Controller\ResultFactory       $resultFactory
	 * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
	 */
	public function __construct(
		Context $context,
		RemoteAddress $remoteAddress,
		\Psr\Log\LoggerInterface $logger,
		StateInterface $inlineTranslation,
		\Azra\GenericForm\Helper\Data $helperData,
		\Azra\GenericForm\Model\ImageUploader $imageUploader,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Azra\GenericForm\Model\ContactFactory $contactFactory,
		\Magento\Framework\Controller\ResultFactory $resultFactory,
		\Magento\Framework\Serialize\SerializerInterface $serializer,
		\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
	) {
		$this->logger = $logger;
		$this->serializer = $serializer;
		$this->helperData = $helperData;
		$this->storeManager = $storeManager;
		$this->resultFactory = $resultFactory;
		$this->imageUploader = $imageUploader;
		$this->remoteAddress = $remoteAddress;
		$this->contactFactory = $contactFactory;
		$this->transportBuilder = $transportBuilder;
		$this->inlineTranslation = $inlineTranslation;

		parent::__construct($context);
	}

	/**
	 * send generic contact form email
	 *
	 * @return Redirect
	 */
	public function execute() {
		if (!$this->getRequest()->isPost()) {
			return $this->resultRedirectFactory->create()->setPath('*/*/');
		}
		$postData = $this->getRequest()->getPostValue();
		if (!$postData) {
			return $this->resultRedirectFactory->create()->setRefererOrBaseUrl();
		}
		if (!$this->_redirect->getRefererUrl()) {
			$this->messageManager->addErrorMessage(__('An error occurred while processing your form. Please try again later.'));
			return $this->resultRedirectFactory->create()->setRefererOrBaseUrl();
		}
		try {
			$postData = $this->validateParams($postData);
			$postData = $this->saveUploadedImages($postData);
			$this->saveFormData($postData);

			$templateOptions = [
				'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
				'store' => $this->storeManager->getStore()->getId(),
			];

			/* $from = [
				'email' => $postData['form_email'],
				'name' => $postData['form_name'],
			]; */

			$from = $this->resolveEmailSender();
			$to = $this->resolveRecieptEmail();
			$emailTemlateIdentifier = $this->resolveEmailTemplateIdentifier();
			try {
				$this->inlineTranslation->suspend();
				$transport = $this->transportBuilder
					->setTemplateIdentifier($emailTemlateIdentifier)
					->setTemplateOptions($templateOptions)
					->setTemplateVars($postData)
					->setFromByScope($from)
					->addTo($to)
					->setReplyTo($postData['form_email'], $postData['form_name'] . " " . $postData['form_surname'])
					->getTransport();

				$transport->sendMessage();
			} finally {
				$this->inlineTranslation->resume();
			}

			$this->messageManager->addSuccessMessage(__('Form submitted successfully!'));

		} catch (LocalizedException $e) {
			$this->messageManager->addErrorMessage($e->getMessage());
		} catch (\Exception $e) {
			$this->logger->critical($e);
			$this->messageManager->addErrorMessage(__('An error occurred while processing your form. Please try again later.'));
		}
		// Redirect to the form page
		return $this->resultRedirectFactory->create()->setRefererOrBaseUrl();
	}

	/**
	 * saves uploaded images
	 *
	 * @param  array $postData
	 * @return array
	 */
	public function saveUploadedImages(array $postData) {
		$files = $this->getRequest()->getFiles("form_attachment");
		if (!$this->helperData->enableAttachemntsOnContactForm() || !$files) {
			return $postData;
		}
		$result = [];
		foreach ($files as $key => $file) {
			if (UPLOAD_ERR_OK == $file['error']) {
				$uploadedImageResult = $this->imageUploader->execute($file);
				if ($fileUrl = $this->getUploadedFileUrl($uploadedImageResult)) {
					$result[] = $fileUrl;
				}
			}
		}
		if ($result) {
			$postData["form_attachment"] = join("\n", $result);
		}
		return $postData;
	}

	/**
	 * returns uploaded file url
	 *
	 * @param  array $uploadedImageResult
	 * @return boolean|string
	 */
	public function getUploadedFileUrl(array $uploadedImageResult) {
		if (!isset($uploadedImageResult["file"])) {
			return false;
		}

		$mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		return $mediaUrl . ImageUploader::CONTACTS_IMAGE_UPLOAD_PATH . $uploadedImageResult["file"];
	}

	/**
	 * save form data
	 *
	 * @param  array $postData
	 * ,
	 * @return [type]
	 */
	protected function saveFormData(array $postData) {
		$data = [];
		$exesiveFormData = [
			"accept_privacy",
			"accept_privacy_text",
			"hideit",
			"product_sku",
			"key",
			"g-recaptcha-response",
			"recaptcha-validate-",
		];
		foreach ($postData as $key => $value) {
			$key = str_replace("form_", "", $key);
			$data[$key] = $value;
		}
		$data["store_id"] = $this->storeManager->getStore()->getId();
		$data["referer_url"] = $this->_redirect->getRefererUrl();
		$data["customer_ip"] = $this->remoteAddress->getRemoteAddress();
		$data = array_diff_key($data, array_flip($exesiveFormData));
		$contactModel = $this->contactFactory->create()->setData($data);

		$other_info = array_diff_key($data, array_flip($contactModel->getFields()));
		$contactModel->setData("other_info", $this->serializer->serialize($other_info));
		$contactModel->save();

		return $contactModel;
	}

	/**
	 * validate post params
	 *
	 * @param  array $postData
	 * @return array
	 */
	protected function validateParams($postData) {

		if (trim($postData['form_name']) === '') {
			throw new LocalizedException(__('Enter the Name and try again.'));
		}
		if (trim($postData['form_surname']) === '') {
			throw new LocalizedException(__('Enter the Surname and try again.'));
		}
		if (false === \strpos($postData['form_email'], '@')) {
			throw new LocalizedException(__('The email address is invalid. Verify the email address and try again.'));
		}
		if (trim($postData['form_comment']) === '') {
			throw new LocalizedException(__('Enter the Comment and try again.'));
		}
		if (isset($postData['form_product']) && trim($postData['form_product']) === '' && $this->isProductForm()) {
			throw new LocalizedException(__('Refresh the page and try again.'));
		}
		if ($this->isRolexForm()) {
			if (!isset($postData['form_telephone']) || trim($postData['form_telephone']) === '') {
				throw new LocalizedException(__('Enter phone number information and try again'));
			}
			if (!isset($postData['form_phone_prefix']) || trim($postData['form_phone_prefix']) === '') {
				throw new LocalizedException(__('Enter phone number information and try again'));
			}
		}
		if (trim($postData['form_accept_privacy']) === '') {
			throw new LocalizedException(__('Authorization for data processing must be checked'));
		}
		if (trim($postData['hideit']) !== '') {
			throw new \Exception();
		}

		return $postData;
	}

	/**
	 * checks wether the data were posted from the product form
	 *
	 * @return boolean
	 */
	public function isProductForm() {
		if (null === $this->_isProductForm) {
			$this->_isProductForm = (bool) ($this->getRequest()->getParam("product_sku") || $this->getRequest()->getParam("form_product"));
		}

		return $this->_isProductForm;
	}

	/**
	 * checks wether the data were posted from the product form
	 *
	 * @return boolean
	 */
	public function isRolexForm() {
		if (null === $this->_isRolexForm) {
			$this->_isRolexForm = (bool) ($this->getRequest()->getParam("rlx-is-contact-retailer") || $this->getRequest()->getParam("rlx-dealer-id"));
		}

		return $this->_isRolexForm;
	}

	/**
	 * resolves email template identifier
	 *
	 * @return string
	 */
	protected function resolveEmailTemplateIdentifier() {
		if ($this->isProductForm()) {
			return \Azra\GenericForm\Helper\Data::PRODUCT_FORM_EMAIL_TEMPLATE;
		} elseif ($this->isRolexForm()) {
			return \Azra\GenericForm\Helper\Data::ROLEX_FORM_EMAIL_TEMPLATE;
		} else {
			return \Azra\GenericForm\Helper\Data::CONTACT_FORM_EMAIL_TEMPLATE;
		}
	}

	/**
	 * resolves the email recepient
	 *
	 * @param  array $postData
	 * @return mixed
	 */
	protected function resolveRecieptEmail() {
		if ($this->isProductForm()) {
			return $this->helperData->getProductEmailRecipient();
		} elseif ($this->isRolexForm()) {
			return $this->helperData->getRolexEmailRecipient();
		} else {
			return $this->helperData->getContactEmailRecipient();
		}
	}

	/**
	 * returns email sender
	 *
	 * @return mixed
	 */
	protected function resolveEmailSender() {
		if ($this->isProductForm()) {
			return $this->helperData->getProductEmailSender();
		} elseif ($this->isRolexForm()) {
			return $this->helperData->getRolexEmailSender();
		} else {
			return $this->helperData->getContactEmailSender();
		}
	}
}
