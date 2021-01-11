<?php

declare (strict_types = 1);

namespace Azra\GenericForm\Observer;

use Azra\GenericForm\Helper\Data;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface;
use Magento\ReCaptchaUi\Model\RequestHandlerInterface;

/**
 *
 */
class ContactFormsObserver implements ObserverInterface {

	/**
	 * @var UrlInterface
	 */
	private $url;

	/**
	 * @var IsCaptchaEnabledInterface
	 */
	private $isCaptchaEnabled;

	/**
	 * @var RequestHandlerInterface
	 */
	private $requestHandler;

	/**
	 * @param UrlInterface $url
	 * @param IsCaptchaEnabledInterface $isCaptchaEnabled
	 * @param RequestHandlerInterface $requestHandler
	 */
	public function __construct(
		UrlInterface $url,
		RedirectInterface $redirect,
		IsCaptchaEnabledInterface $isCaptchaEnabled,
		RequestHandlerInterface $requestHandler
	) {
		$this->url = $url;
		$this->redirect = $redirect;
		$this->requestHandler = $requestHandler;
		$this->isCaptchaEnabled = $isCaptchaEnabled;
	}

	/**
	 * @param Observer $observer
	 * @return void
	 * @throws LocalizedException
	 */
	public function execute(Observer $observer): void{
		$key = $this->resolveFormKey($observer);
		if ($this->isCaptchaEnabled->isCaptchaEnabledFor($key)) {
			/** @var Action $controller */
			$controller = $observer->getControllerAction();
			$request = $controller->getRequest();
			$response = $controller->getResponse();
			$redirectOnFailureUrl = $this->redirect->getRedirectUrl();

			$this->requestHandler->execute($key, $request, $response, $redirectOnFailureUrl);
		}
	}

	/**
	 *
	 * @param  Observer $observer
	 *
	 * @return string
	 */
	public function resolveFormKey(Observer $observer) {
		$controller = $observer->getControllerAction();
		if ($controller->isRolexForm()) {
			return Data::RECAPTCHA_FOR_KEY_ROLEX;
		} else if ($controller->isProductForm()) {
			return Data::RECAPTCHA_FOR_KEY_PRODUCT;
		}
		return Data::RECAPTCHA_FOR_KEY_CUSTOM_CONTACT;
	}
}
