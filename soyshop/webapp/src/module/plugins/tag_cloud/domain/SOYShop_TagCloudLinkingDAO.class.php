<?php
SOY2::import("module.plugins.tag_cloud.domain.SOYShop_TagCloudLinking");
/**
 * @entity SOYShop_TagCloudLinking
 */
abstract class SOYShop_TagCloudLinkingDAO extends SOY2DAO{

	abstract function insert(SOYShop_TagCloudLinking $bean);

	abstract function getByItemId($itemId);

	abstract function deleteByItemId($itemId);
}
