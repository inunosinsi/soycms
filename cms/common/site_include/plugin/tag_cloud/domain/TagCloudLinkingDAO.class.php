<?php
SOY2::import("site_include.plugin.tag_cloud.domain.TagCloudLinking");
/**
 * @entity TagCloudLinking
 */
abstract class TagCloudLinkingDAO extends SOY2DAO{

	abstract function insert(TagCloudLinking $bean);

	abstract function getByEntryId($entryId);

	abstract function deleteByEntryId($entryId);
}
