<?php
namespace Azra\GenericForm\Ui\DataProvider;

use Azra\GenericForm\Model\ResourceModel\Contact\CollectionFactory;

class Contact extends \Magento\Ui\DataProvider\AbstractDataProvider {

	protected $serializer;

	/**
	 * initialize class depedencies
	 *
	 * @param string            $name
	 * @param string            $primaryFieldName
	 * @param string            $requestFieldName
	 * @param CollectionFactory $collectionFactory
	 * @param array             $meta
	 * @param array             $data
	 */
	public function __construct(
		$name,
		$primaryFieldName,
		$requestFieldName,
		CollectionFactory $collectionFactory,
		\Magento\Framework\Serialize\SerializerInterface $serializer,
		array $meta = [],
		array $data = []
	) {
		$this->serializer = $serializer;
		$this->collection = $collectionFactory->create();

		parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
	}

	/**
	 * Get data
	 *
	 * @return array
	 */
	public function getData() {
		if (isset($this->loadedData)) {
			return $this->loadedData;
		}
		$items = $this->collection->getItems();
		$this->loadedData = [];

		foreach ($items as $contact) {
			$data = $contact->getData();
			$data = $this->processData($data);
			$this->loadedData[$contact->getId()] = ["contact" => $data];
		}

		return $this->loadedData;
	}

	protected function processData($data) {

		$data["other_info"] = $this->serializer->unserialize($data["other_info"]);
		$other_info = [];
		foreach ($data['other_info'] as $key => $value) {
			$key = str_replace("form_", "", $key);
			$other_info[] = ucwords($key) . ": " . $value;
		}
		$data["other_info"] = implode("\n", $other_info);

		return $data;
	}
}
