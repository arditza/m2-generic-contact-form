<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Azra\GenericForm\Helper;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Contact base helper
 *
 * @deprecated 100.2.0
 * @see \Magento\Contact\Model\ConfigInterface
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper {

	const ROLEX_FORM_EMAIL_TEMPLATE = "rolex_form_email_template";
	const CONTACT_FORM_EMAIL_TEMPLATE = "contact_form_email_template";
	const PRODUCT_FORM_EMAIL_TEMPLATE = "product_form_email_template";

	const XML_PATH_GENERICFORM = 'genericform/';

	const XML_PATH_ROLEX_CONFIG = 'rolex/';
	const XML_PATH_CONTACT_CONFIG = 'contact/';
	const XML_PATH_PRODUCT_CONFIG = 'product/';

	const XML_PATH_PRIVACY_TEXT = 'forms_privacy_text';
	const XML_PATH_EMAIL_RECIPIENT = 'email_recipient';
	const XML_PATH_EMAIL_SENDER = 'sender_email_identity';
	const XML_PATH_ENABLE_ATTACHMENT = 'enable_attachments';

	const RECAPTCHA_FOR_KEY_ROLEX = 'rolex';
	const RECAPTCHA_FOR_KEY_PRODUCT = 'product';
	const RECAPTCHA_FOR_KEY_CUSTOM_CONTACT = 'custom_contact';
	const XML_PATH_ENABLE_CAPTCHA_FOR = 'recaptcha_frontend/type_for/';

	/**
	 * Customer session
	 *
	 * @var \Magento\Customer\Model\Session
	 */
	protected $_customerSession;

	/**
	 * Magento Registry
	 *
	 * @var Magento\Framework\Registry
	 */
	protected $_registry;

	/**
	 * Magento Store Manager
	 *
	 * @var \Magento\Store\Model\StoreManager
	 */
	protected $_storeManager;

	/**
	 * initialize class depdencies
	 *
	 * @param \Magento\Framework\Registry            $registry
	 * @param \Magento\Framework\App\Helper\Context  $context
	 * @param \Magento\Customer\Model\SessionFactory $customerSession
	 */
	public function __construct(
		\Magento\Framework\Registry $registry,
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Customer\Model\SessionFactory $customerSession,
		\Magento\Store\Model\StoreManagerInterface $storeManager
	) {
		$this->_registry = $registry;
		$this->_storeManager = $storeManager;
		$this->_customerSession = $customerSession->create();
		parent::__construct($context);
	}

	/**
	 * Get user name
	 *
	 * @return string
	 */
	public function getUserName() {
		if (!$this->_customerSession->isLoggedIn()) {
			return '';
		}
		/**
		 * @var \Magento\Customer\Api\Data\CustomerInterface $customer
		 */
		$customer = $this->_customerSession->getCustomerDataObject();

		return $customer->getFirstname();
	}

	/**
	 * Get user email
	 *
	 * @return string
	 */
	public function getUserEmail() {
		if (!$this->_customerSession->isLoggedIn()) {
			return '';
		}
		/**
		 * @var CustomerInterface $customer
		 */
		$customer = $this->_customerSession->getCustomerDataObject();

		return $customer->getEmail();
	}

	/**
	 * Get user email
	 *
	 * @return string
	 */
	public function getUserLastName() {
		if (!$this->_customerSession->isLoggedIn()) {
			return '';
		}
		/**
		 * @var CustomerInterface $customer
		 */
		$customer = $this->_customerSession->getCustomerDataObject();

		return $customer->getLastname();
	}

	/**
	 * returns the config value of specific path
	 *
	 * @param  string $field
	 * @param  int|string|null $storeId
	 *
	 * @return mixed
	 */
	public function getConfigValue($field, $storeId = null) {

		return $this->scopeConfig->getValue(
			$field, ScopeInterface::SCOPE_STORE, $storeId
		);
	}

	/**
	 * returns the configurations of general group
	 *
	 * @param  string $code
	 * @param  mixed $storeId
	 *
	 * @return string|null
	 */
	public function getGeneralConfig($code, $storeId = null) {

		return $this->getConfigValue(self::XML_PATH_GENERICFORM . 'general/' . $code, $storeId);
	}

	/**
	 * returns the privacy text
	 *
	 * @return string|null
	 */
	public function getPrivacyText() {
		return $this->getGeneralConfig(self::XML_PATH_PRIVACY_TEXT);
	}

	/**
	 * returns contact form email recipient
	 *
	 * @return string | null
	 */
	public function getContactEmailRecipient() {
		return $this->getConfigValue(self::XML_PATH_GENERICFORM . self::XML_PATH_CONTACT_CONFIG . self::XML_PATH_EMAIL_RECIPIENT);
	}

	/**
	 * returns product form email recipient
	 *
	 * @return string | null
	 */
	public function getProductEmailRecipient() {
		return $this->getConfigValue(self::XML_PATH_GENERICFORM . self::XML_PATH_PRODUCT_CONFIG . self::XML_PATH_EMAIL_RECIPIENT);
	}

	/**
	 * returns rolex form email recipient
	 *
	 * @return string | null
	 */
	public function getRolexEmailRecipient() {
		return $this->getConfigValue(self::XML_PATH_GENERICFORM . self::XML_PATH_ROLEX_CONFIG . self::XML_PATH_EMAIL_RECIPIENT);
	}

	/**
	 * returns contact form email sender
	 *
	 * @return string | null
	 */
	public function getContactEmailSender() {
		return $this->getConfigValue(self::XML_PATH_GENERICFORM . self::XML_PATH_CONTACT_CONFIG . self::XML_PATH_EMAIL_SENDER) ?: 'general';
	}

	/**
	 * returns product form email sender
	 *
	 * @return string | null
	 */
	public function getProductEmailSender() {
		return $this->getConfigValue(self::XML_PATH_GENERICFORM . self::XML_PATH_PRODUCT_CONFIG . self::XML_PATH_EMAIL_SENDER) ?: 'general';
	}

	/**
	 * returns product form email sender
	 *
	 * @return string | null
	 */
	public function getRolexEmailSender() {
		return $this->getConfigValue(self::XML_PATH_GENERICFORM . self::XML_PATH_ROLEX_CONFIG . self::XML_PATH_EMAIL_SENDER) ?: 'general';
	}

	/**
	 * checks if google recaptcha is enabled for contact form
	 *
	 * @return boolean
	 */
	public function isRecaptchaEnabledForContactForm() {
		return $this->getConfigValue(self::XML_PATH_ENABLE_CAPTCHA_FOR . self::RECAPTCHA_FOR_KEY_CUSTOM_CONTACT);
	}

	/**
	 * checks if google recaptcha is enabled for contact form
	 *
	 * @return boolean
	 */
	public function isRecaptchaEnabledForProductForm() {
		return $this->getConfigValue(self::XML_PATH_ENABLE_CAPTCHA_FOR . self::RECAPTCHA_FOR_KEY_PRODUCT);
	}

	/**
	 * checks if google recaptcha is enabled for contact form
	 *
	 * @return boolean
	 */
	public function isRecaptchaEnabledForRolexForm() {
		return $this->getConfigValue(self::XML_PATH_ENABLE_CAPTCHA_FOR . self::RECAPTCHA_FOR_KEY_ROLEX);
	}

	/**
	 * checks if file input is enabled for contact form
	 *
	 * @return boolean
	 */
	public function enableAttachemntsOnContactForm() {
		return $this->getConfigValue(self::XML_PATH_GENERICFORM . self::XML_PATH_CONTACT_CONFIG . self::XML_PATH_ENABLE_ATTACHMENT);
	}

	/**
	 * returns current registered product
	 *
	 * @return \Magento\Catalog\Model\Product|null
	 */
	public function getCurrentProduct() {
		return $this->_registry->registry('current_product');
	}

	/**
	 * rturns current productName
	 *
	 * @return string
	 */
	public function getCurrentProductName() {
		return $this->getCurrentProduct()->getName();
	}

	/**
	 * returns current store code
	 *
	 * @return string
	 */
	public function getStoreCode() {
		return $this->_storeManager->getStore()->getCode();
	}
	/**
	 * returns customer prefixes
	 *
	 * @return array
	 */
	public function getCustomerPrefixes() {
		$prefixes = [];
		$prefixes[] = __("Mr");
		$prefixes[] = __("Mrs");
		if ($this->getStoreCode() != "it") {
			$prefixes[] = __("Ms");
		}

		return $prefixes;
	}
}
