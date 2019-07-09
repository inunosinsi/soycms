<?php

/**
 * @entity GravatarAccount
 */
abstract class GravatarAccountDAO extends SOY2DAO{

	abstract function insert(GravatarAccount $bean);

	abstract function update(GravatarAccount $bean);

	abstract function get();

	/**
	 * @return object
	 */
	abstract function getByName($name);

	/**
	 * @return object
	 */
	abstract function getByMailAddress($mailAddress);

	abstract function deleteById($id);
}
