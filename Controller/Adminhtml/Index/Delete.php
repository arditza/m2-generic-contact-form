<?php

namespace Azra\GenericForm\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;

class Delete extends Action {

	/**
	 * @var \Azra\GenericForm\Model\ContactFactory
	 */
	protected $contactFactory;

	/**
	 * @param Action\Context $context
	 */
	public function __construct(
		Action\Context $context,
		\Azra\GenericForm\Model\ContactFactory $contactFactory
	) {
		$this->contactFactory = $contactFactory;

		parent::__construct($context);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _isAllowed() {
		return $this->_authorization->isAllowed('Azra_GenericForm::manage_delete');
	}

	/**
	 * Delete action
	 *
	 * @return void
	 */
	public function execute() {
		$id = $this->getRequest()->getParam('contact_id');
		/** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
		$resultRedirect = $this->resultRedirectFactory->create();
		if ($id) {
			$contact = $this->contactFactory->create()->load($id);
			if (!$contact || !$contact->getId()) {
				$this->messageManager->addError(__('This contact no longer exists.'));
				return $resultRedirect->setPath('*/*/');
			}
			try {
				$contact->delete();
				$this->messageManager->addSuccess(__('The contact has been deleted.'));
				return $resultRedirect->setPath('*/*/');
			} catch (\Exception $e) {
				$this->messageManager->addError($e->getMessage());
				return $resultRedirect->setPath('*/*/view', ['page_id' => $id]);
			}
		}

		$this->messageManager->addError(__('We can\'t find a contact to delete.'));
		return $resultRedirect->setPath('*/*/');
	}
}
