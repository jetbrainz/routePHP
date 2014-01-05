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
	
	public function run()
	{
		$task = $this->get();
		
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
		
		if (!$ret) {
			$query = "update queue set skipped=1 where id=:id";

			$st = $this->db()->prepare($query);

			$st->bindValue(':id', $task['id']);

			$st->execute();
			return;
		}
		
		if (isset ($ret['out_params'])) {
			$out_params = $ret['out_params'];
		} elseif (method_exists($o, 'getQueueOutParams')) {
			$out_params = $o->getQueueOutParams();
		} else {
			$out_params = '';
		}
		
		$status = isset($ret['status']) ? $ret['status'] : '';
		
		$query = "update queue set processed=1, status=:status, out_params=:params where id=:id";
		
		$st = $this->db()->prepare($query);
		
		$st->bindValue(':id', $task['id']);
		$st->bindValue(':params', $out_params);
		$st->bindValue(':status', $status);
		
		$st->execute();
	}
	
	public function get($id=null)
	{
		if ($id !== null) {
			$id = intval($id);
			$query = "select * from queue where id=$id";
		} else {
			$query = "select * from queue where processed=0 and skipped=0 and brand=:brand order by created limit 1";
		}
		
		$st = $this->db()->prepare($query);
		$st->bindValue(':brand', BRAND);
		
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
		
		return true;
	}
	
	public function reOpen($id)
	{
		$id = intval($id);
		$manager = LOGGED;
		$query = "update queue set skipped=0 where id=$id and processed=0";
		
		if (!$this->db()->query($query)) {
			return false;
		}
		
		return true;
	}
	
	public function create($task, $params, $owner=null, $owner_id=null)
	{
		$query = "insert into queue (task, params, brand, owner, owner_id) values (:task, :params, :brand, :owner, :owner_id)";
		
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
		
		return $st->execute() ? true : false;
	}
	
	public function getList($task, $processed=false, $owner=null, $owner_id=null, $sorting=null)
	{
		$processed = $processed ? 1 : 0;
		$sort = ($sorting==self::SORT_DESC) ? 'desc' : 'asc';
		
		$query = "select * from queue where task=:task and processed=$processed and brand=:brand ";
		if ($owner) {
			$query .= ' and owner=:owner';
		}
		if ($owner_id) {
			$query .= ' and owner_id=:owner_id';
		}
		$query .= " order by id $sort limit 100";
		
		$st = $this->db()->prepare($query);
		
		$st->bindValue(':task', $task);
		$st->bindValue(':brand', BRAND);
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
