<?php

namespace Azra\GenericForm\Ui\Component\Contacts\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column {
	/**
	 * @var UrlInterface
	 */
	protected $_urlBuilder;

	/**
	 * @var string
	 */
	protected $_viewUrl;

	/**
	 * Constructor
	 *
	 * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
	 * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
	 * @param \Magento\Framework\UrlInterface $urlBuilder
	 * @param string $viewUrl
	 * @param array $components
	 * @param array $data
	 */
	public function __construct(
		ContextInterface $context,
		UiComponentFactory $uiComponentFactory,
		UrlInterface $urlBuilder,
		$viewUrl = '',
		array $components = [],
		array $data = []
	) {
		$this->_urlBuilder = $urlBuilder;
		$this->_viewUrl = $viewUrl;
		parent::__construct($context, $uiComponentFactory, $components, $data);
	}

	/**
	 * Prepare Data Source
	 *
	 * @param array $dataSource
	 * @return array
	 */
	public function prepareDataSource(array $dataSource) {
		if (isset($dataSource['data']['items'])) {
			$storeId = $this->context->getFilterParam('store_id');
			foreach ($dataSource['data']['items'] as &$item) {
				$name = $this->getData('name');
				if (isset($item['entity_id'])) {
					$item[$name]['view'] = [
						'href' => $this->_urlBuilder->getUrl($this->_viewUrl, ['id' => $item['entity_id'], 'store' => $storeId]),
						'target' => '_blank',
						'label' => __('View'),
					];
				}
			}
		}
		return $dataSource;
	}
}
