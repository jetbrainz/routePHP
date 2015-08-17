<?php

/**
 * Description of queue
 *
 ** @author Valentin Balt <valentin.balt@gmail.com>
 */
class Queue extends Base
{
	const SORT_ASC	= 'asc';
	const SORT_DESC	= 'desc';
	
	private $taskMapper = array ();
	
	public function __construct($logger=true)
	{
		parent::__construct($logger);
		$conf = $this->getConfig('tasks');
		if (!empty ($conf)) {
			$this->taskMapper = $conf;
		}
	}

	public function getTaskNames()
	{
		return array_keys($this->taskMapper);
	}

	public function getObjectNameByTask($task)
	{
		if (!isset ($this->taskMapper[$task])) {
			return null;
		}
		return $this->taskMapper[$task];
	}
	
	public function run($task=null)
	{
		$task = $this->get(null, $task);
		
		if (!$task) {
			return null;
		}
		
		if (!isset ($this->taskMapper[$task['task']])) {
			$query = "update queue set skipped=1 where id=:id";

			$st = $this->db()->prepare($query);

			$st->bindValue(':id', $task['id']);

			$st->execute();
			return null;
		}
		
		$o = new $this->taskMapper[$task['task']];
		
		if (!method_exists($o, 'queueRun')) {
			$query = "update queue set skipped=1 where id=:id";

			$st = $this->db()->prepare($query);

			$st->bindValue(':id', $task['id']);

			$st->execute();
			return false;
		}
		
		$ret = $o->queueRun($task);
		
		if (isset ($ret['out_params'])) {
			$out_params = $ret['out_params'];
		} elseif (method_exists($o, 'getQueueOutParams')) {
			$out_params = $o->getQueueOutParams();
		} else {
			$out_params = '';
		}

		if (isset ($ret['status'])) {
			$status = $ret['status'];
		} elseif ($ret === true) {
			$status = 1;
		} elseif ($ret === false) {
			$status = -1;
		} else {
			$status = 0;
		}

		$query = "update queue set status=:status, out_params=:params where id=:id";
		
		$st = $this->db()->prepare($query);
		
		$st->bindValue(':id', $task['id']);
		$st->bindValue(':params', is_array ($out_params)?json_encode($out_params):$out_params);
		$st->bindValue(':status', $status);
		
		$st->execute();

		$this->close($task['id']);
	}
	
	public function get($id=null, $task=null)
	{
		if ($id !== null) {
			$id = intval($id);
			$query = "select * from queue where id=$id";
		} elseif ($task !== null) {
			$query = "select * from queue where processed=0 and skipped=0 and brand=:brand and task=:task order by id limit 1";
		} else {
			$query = "select * from queue where processed=0 and skipped=0 and brand=:brand order by id limit 1";
		}
		
		$st = $this->db()->prepare($query);
		$st->bindValue(':brand', BRAND, PDO::PARAM_STR);
		if ($task !== null) {
			$st->bindValue(':task', $task, PDO::PARAM_STR);
		}
		
		$st->execute();
		if (!$f=$st->fetch(PDO::FETCH_ASSOC)) {
			return false;
		}
		
		return $f;
	}
	
	public function close($id)
	{
		$id = intval($id);
		$manager = LOGGED;

		$query = "update queue set processed=1 where id=$id";
		if (!$this->db()->query($query)) {
			return false;
		}

		$query = "update queue set processed=0 where parent_id=$id and processed=2 and skipped=0";
		if (!$this->db()->query($query)) {
			return false;
		}

		return true;
	}
	
	public function reOpen($id)
	{
		$id = intval($id);
		$manager = LOGGED;
		$query = "update queue set skipped=0, processed=0 where id=$id";
		
		if (!$this->db()->query($query)) {
			return false;
		}
		
		return true;
	}
	
	public function create($task, $params, $owner=null, $owner_id=null, $parent_id=null)
	{
		$query = "insert into queue (task, params, brand, owner, owner_id, parent_id, processed) values (:task, :params, :brand, :owner, :owner_id, :parent_id, :processed)";
		
		$st = $this->db()->prepare($query);
		
		$st->bindValue(':task', $task);
		$st->bindValue(':params', $params);
		$st->bindValue(':brand', BRAND);
		if ($owner) {
			$st->bindValue(':owner', $owner);
		} else {
			$st->bindValue(':owner', null, PDO::PARAM_NULL);
		}
		if ($owner_id) {
			$st->bindValue(':owner_id', $owner_id, PDO::PARAM_INT);
		} else {
			$st->bindValue(':owner_id', null, PDO::PARAM_NULL);
		}
		if ($parent_id) {
			$st->bindValue(':parent_id', $parent_id, PDO::PARAM_INT);
			// Keep for next processing
			$st->bindValue(':processed', 2, PDO::PARAM_INT);
		} else {
			$st->bindValue(':parent_id', null, PDO::PARAM_NULL);
			$st->bindValue(':processed', 0, PDO::PARAM_INT);
		}

		return $st->execute() ? $this->db()->lastInsertId() : false;
	}
	
	public function getList($task, $processed=false, $owner=null, $owner_id=null, $sorting=null, $parent_id=null)
	{
		$processed = $processed ? 1 : 0;
		$sort = ($sorting==self::SORT_DESC) ? 'desc' : 'asc';
		
		$query = "select * from queue where task=:task and processed=$processed and skipped=0 and brand=:brand ";
		if ($owner) {
			$query .= ' and owner=:owner';
		}
		if ($owner_id) {
			$query .= ' and owner_id=:owner_id';
		}
		if ($parent_id) {
			$query .= ' and parent_id=:parent_id';
		}
		$query .= " order by id $sort limit 50";
		
		$st = $this->db()->prepare($query);
		
		$st->bindValue(':task', $task);
		$st->bindValue(':brand', BRAND);
		if ($owner) {
			$st->bindValue(':owner', $owner);
		}
		if ($owner_id) {
			$st->bindValue(':owner_id', $owner_id, PDO::PARAM_INT);
		}
		if ($parent_id) {
			$st->bindValue(':parent_id', $parent_id, PDO::PARAM_INT);
		}
		
		if (!$st->execute()) {
			return null;
		}
		
		$ret = array ();
		
		while ($f=$st->fetch(PDO::FETCH_ASSOC)) {
			$ret[] = $f;
		}
		
		return $ret;
	}

	public function getSearchList($task=null, $processed=null, $skipped=null, $owner=null, $owner_id=null,  $page=1, $lpp=20, $sortField='id', $sortWay=self::SORT_DESC)
	{
		$query = "select * from queue where brand=:brand";

		if ($task) {
			$query .= ' and task=:task';
		}
		if ($owner) {
			$query .= ' and owner=:owner';
		}
		if ($owner_id) {
			$query .= ' and owner_id=:owner_id';
		}
		if ($processed !== null) {
			$query .= ' and processed='.intval($processed);
		}
		if ($skipped !== null) {
			$query .= ' and skipped='.intval($skipped);
		}

		$page = (int) $page;
		$lpp = (int) $lpp;
		$offset = ($page - 1) * $lpp;

		$query .= " order by $sortField $sortWay limit $offset,$lpp";

		$st = $this->db()->prepare($query);

		$st->bindValue(':brand', BRAND);

		if ($task) {
			$st->bindValue(':task', $task);
		}
		if ($owner) {
			$st->bindValue(':owner', $owner);
		}
		if ($owner_id) {
			$st->bindValue(':owner_id', $owner_id, PDO::PARAM_INT);
		}

		if (!$st->execute()) {
			return null;
		}

		$ret = array ();

		while ($f=$st->fetch(PDO::FETCH_ASSOC)) {
			$ret[] = $f;
		}

		return $ret;
	}

}
