<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types = 1);

namespace Azra\GenericForm\Model\ResourceModel;

class Contact extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct() {
		$this->_init('genericform_contact', 'entity_id');
	}

	/**
	 * return available fields
	 *
	 * @return array
	 */
	public function getFields() {
		$fields = $this->getConnection()->describeTable($this->getMainTable());
		return $fields ?: [];
	}
}
