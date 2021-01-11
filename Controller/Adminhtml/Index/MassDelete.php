<?php

namespace Azra\GenericForm\Controller\Adminhtml\Index;

use Azra\GenericForm\Model\ResourceModel\Contact\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends \Magento\Backend\App\Action {
	/**
	 * @var Filter
	 */
	protected $filter;

	/**
	 * @var CollectionFactory
	 */
	protected $collectionFactory;

	/**
	 * @param Context $context
	 * @param Filter $filter
	 * @param CollectionFactory $collectionFactory
	 */
	public function __construct(
		Context $context,
		Filter $filter,
		CollectionFactory $collectionFactory
	) {
		$this->filter = $filter;
		$this->collectionFactory = $collectionFactory;
		parent::__construct($context);
	}

	/**
	 * Dispatch request
	 *
	 * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
	 * @throws \Magento\Framework\Exception\NotFoundException
	 */
	public function execute() {
		$collection = $this->filter->getCollection($this->collectionFactory->create());
		$collectionSize = $collection->getSize();

		foreach ($collection as $item) {
			$item->delete();
		}

		$this->messageManager->addSuccess(__('A total of %1 element(s) have been deleted.', $collectionSize));

		/** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
		$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
		return $resultRedirect->setPath('*/*/');
	}
}