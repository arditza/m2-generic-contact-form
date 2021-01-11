<?php

namespace Azra\GenericForm\Controller\Adminhtml\Index;

class Index extends \Magento\Backend\App\Action {

	/**
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	protected $resultPageFactory;

	/**
	 * @param \Magento\Backend\App\Action\Context $context
	 * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
	 */
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
	) {
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
	}

	/**
	 * @return bool
	 */
	protected function _isAllowed() {
		return $this->_authorization->isAllowed('Azra_GenericForm::genericform');
	}

	/**
	 * @return \Magento\Backend\Model\View\Result\Page
	 */
	public function execute() {
		/** @var \Magento\Backend\Model\View\Result\Page $resultPage */
		$resultPage = $this->resultPageFactory->create();
		$resultPage->setActiveMenu(
			'Azra_GenericForm::genericform'
		)->addBreadcrumb(
			__('Contact Us'),
			__('Contact Us')
		);
		$resultPage->getConfig()->getTitle()->prepend(__('Contact Managment'));
		return $resultPage;
	}
}
