<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types = 1);

namespace Azra\GenericForm\Model;

use Magento\Framework\Model\AbstractModel;

class Contact extends AbstractModel {

	/**
	 * Define resource model
	 */
	protected function _construct() {
		$this->_init(\Azra\GenericForm\Model\ResourceModel\Contact::class);
	}

	/**
	 * returns all table columns
	 *
	 * @return array
	 */
	public function getFields() {
		$fields = $this->getResource()->getFields();

		return array_keys($fields);
	}
}