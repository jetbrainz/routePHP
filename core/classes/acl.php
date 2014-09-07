<?php
/**
 * Created by PhpStorm.
 * User: valentin
 * Date: 6/2/14
 * Time: 10:20 PM
 */

class ACL extends Config
{
	const A_READ		= 0b0000000000000000000000000000001;
	const A_WRITE		= 0b0000000000000000000000000000010;
	const A_ACCESS  	= 0b0000000000000000000000000000100;
	const A_EXECUTE		= 0b0000000000000000000000000001000;
	const A_DELETE		= 0b0000000000000000000000000010000;
	const A_OPEN		= 0b0000000000000000000000000100000;
	const A_CLOSE		= 0b0000000000000000000000001000000;
	const A_REPEAT		= 0b0000000000000000000000010000000;
	const A_ACTIVATE	= 0b0000000000000000000000100000000;
	const A_DEACTIVATE	= 0b0000000000000000000001000000000;
	const A_ALL			= 0b1000000000000000000000000000000; // 32bit - 1bit
	
	private
		$group = null, // User's group
		$acl = null; // Access List

	/**
	 * @param $aro
	 */
	public function __construct($group=null)
	{
		parent::__construct();
		$this->group = $group;
	}

	public function check($action, $object='all', $group=null)
	{
		if (!$group) {
			$group = $this->group;
		}
		$acl = $this->getConfig($group);
		if (
			!empty ($acl[$object])
			&& $acl[$object] & ($action | self::A_ALL)
		) {
			return true;
		}
		return false;
	}

	public function getGroups()
	{
		return $this->getConfig('groups');
	}
}