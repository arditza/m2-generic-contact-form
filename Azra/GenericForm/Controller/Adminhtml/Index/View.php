<?php

namespace Azra\GenericForm\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;

class View extends \Magento\Backend\App\Action {

	/**
	 * Core registry
	 *
	 * @var \Magento\Framework\Registry
	 */
	protected $coreRegistry = null;

	/**
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	protected $resultPageFactory;

	/**
	 * @var \Ogilvypennywise\Contactus\Model\Contact
	 */
	protected $contactModel;

	/**
	 * @param Action\Context $context
	 * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
	 * @param \Magento\Framework\Registry $registry
	 */
	public function __construct(
		Action\Context $context,
		\Magento\Framework\Registry $registry,
		\Azra\GenericForm\Model\ContactFactory $contactFactory,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
	) {
		$this->coreRegistry = $registry;
		$this->contactFactory = $contactFactory;
		$this->resultPageFactory = $resultPageFactory;

		parent::__construct($context);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _isAllowed() {
		return $this->_authorization->isAllowed('Azra_GenericForm::genericform');
	}

	/**
	 * Init actions
	 *
	 * @return $this
	 */
	protected function _initAction() {
		/** @var \Magento\Backend\Model\View\Result\Page $resultPage */
		$resultPage = $this->resultPageFactory->create();
		$resultPage->setActiveMenu(
			'Azra_GenericForm::genericform'
		);
		return $resultPage;
	}

	/**
	 * Edit News page
	 *
	 * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 */
	public function execute() {

		$id = $this->getRequest()->getParam('id');
		if (!$id) {
			$this->messageManager->addError(__('You are not allowed to access this page.'));
			$resultRedirect = $this->resultRedirectFactory->create();
			return $resultRedirect->setPath('*/*/');
		}

		$contact = $this->contactFactory->create()->load($id);
		if (!$contact->getId()) {
			$this->messageManager->addError(__('This contact form does no longer exists.'));
			$resultRedirect = $this->resultRedirectFactory->create();
			return $resultRedirect->setPath('*/*/');
		}

		/**
		 * 4. Register model to use later in blocks
		 * */
		$this->coreRegistry->register('contact', $contact);

		/**
		 * 5. Build edit form
		 * */
		/**
		 * @var \Magento\Backend\Model\View\Result\Page $resultPage
		 * */
		$resultPage = $this->_initAction();
		$resultPage->addBreadcrumb(
			$id ? __('View Contact') : __('View Contact'),
			$id ? __('View Contact') : __('View Contact')
		);
		$resultPage->getConfig()->getTitle()
			->prepend(__('Contact View of : \'%1\'', $contact->getData('surname')));

		return $resultPage;
	}
}
