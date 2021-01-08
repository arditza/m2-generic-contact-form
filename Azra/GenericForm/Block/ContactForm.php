<?php

namespace Azra\GenericForm\Block;

/**
 * General Block used to render multiple forms
 */
class ContactForm extends \Magento\Framework\View\Element\Template {

	/**
	 * initialize class depdencies
	 *
	 * @param \Magento\Framework\View\Element\Template\Context $context
	 * @param array                                            $data
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		array $data = []
	) {
		parent::__construct($context, $data);
	}

	/**
	 * returns the form action url
	 *
	 * @return string
	 */
	public function getFormAction() {
		return $this->_urlBuilder->getUrl('genericform/index/post');
	}

	/**
	 * Preparing global layout
	 *
	 * You can redefine this method in child classes for changing layout
	 *
	 * @return $this
	 */
	protected function _prepareLayout() {
		parent::_prepareLayout();

		$arguments = [
			"data" => [
				"recaptcha_for" => $this->getData("recaptcha_for") ?: "",
				"jsLayout" => [
					"components" => [
						"recaptcha" => [
							"component" => "Magento_ReCaptchaFrontendUi/js/reCaptcha",
						],
					],
				],
			],
		];
		$recaptchaBlock = $this->getLayout()
			->createBlock(
				'Magento\ReCaptchaUi\Block\ReCaptcha',
				'recaptcha',
				$arguments
			)
			->setTemplate('Magento_ReCaptchaFrontendUi::recaptcha.phtml');
		$this->setChild('recaptcha', $recaptchaBlock);

		return $this;
	}
}
