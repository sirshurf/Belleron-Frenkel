<?php
class Bf_View_Helper_Breadcrumbs extends Zend_View_Helper_Navigation_Breadcrumbs {

	/**
	 * Determines whether a page should be accepted by ACL when iterating
	 *
	 * Rules:
	 * - If helper has no ACL, page is accepted
	 * - If page has a resource or privilege defined, page is accepted
	 * if the ACL allows access to it using the helper's role
	 * - If page has no resource or privilege, page is accepted
	 *
	 * @param  Zend_Navigation_Page $page  page to check
	 * @return bool                        whether page is accepted by ACL
	 */
	protected function _acceptAcl (Zend_Navigation_Page $page)
	{
		if (! $acl = $this->getAcl()) {
			// no acl registered means don't use acl
			return true;
		}
		$resource = $page->getResource();
		$privilege = $page->getPrivilege();
		if ($resource) {
			return $this->_acl->checkPermissionsById($resource, $privilege);
		}
		return true;
	}
}