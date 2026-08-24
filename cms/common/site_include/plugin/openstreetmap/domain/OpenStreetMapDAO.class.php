<?php
SOY2::import("site_include.plugin.openstreetmap.domain.OpenStreetMap");
/**
 * @entity OpenStreetMap
 */
abstract class OpenStreetMapDAO extends SOY2DAO {

	abstract function insert(OpenStreetMap $bean);

	/**
	 * @query entry_id = :entryId
	 */
	abstract function update(OpenStreetMap $bean);

	/**
	 * @return object
	 */
	abstract function getByEntryId(int $entryId);

	abstract function deleteByEntryId(int $entryId);
}
