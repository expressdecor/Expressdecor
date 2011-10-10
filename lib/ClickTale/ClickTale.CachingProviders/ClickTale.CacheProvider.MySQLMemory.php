<?php
/**
 * ClickTale - PHP Integration Module
 *
 * LICENSE
 *
 * This source file is subject to the ClickTale(R) Integration Module License that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.clicktale.com/Integration/0.2/LICENSE.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@clicktale.com so we can send you a copy immediately.
 *
 */
?>
<?php

require_once(ClickTale_Root."/ClickTale.CachingProviders/ClickTale.CacheProvider.BaseCacheProvider.php");
require_once(ClickTale_Root."/ClickTale.Settings.php");
require_once(ClickTale_Root."/ClickTale.Logger.php");

class ClickTale_CacheProvider_MySQLMemory extends ClickTale_CacheProvider_BaseCacheProvider
{
	private $was_init = false;
	
	private $connection = null;
	
	private $tablename = "";
	
	private $block_size = 255;
	
	/**
	 * Splits a DB URI for it's components
	 * @return array Components for db connection
	 * @param array $config
	 */
	protected function extract_db_config($config)
	{
		$parts = parse_url($config['CacheLocation']);
		$db = substr($parts["path"], 1);
		
		list($db, $tablename) = split("\\.", substr($parts["path"], 1), 2);
		return array(
			"Host" => $parts["host"],
			"Port" => $parts["port"],
			"DatabaseName" => $db,
			"Username" => $parts["user"],
			"Password" => $parts["pass"],
			"TableName" => $tablename
		);
	}
	
	/**
	 * Returns the connection to the database
	 * @return 
	 * @param array $config
	 */
	protected function start_connection($rawConfig) {
		$config = $this->extract_db_config($rawConfig);
		
		$this->tablename = $config["TableName"];

		$mysqli = mysqli_connect($config["Host"], $config["Username"], $config["Password"], $config["DatabaseName"], $config["Port"]);
		if(!$mysqli) {
			throw new Exception("Problem connecting to mysql database: ".mysqli_connect_error());
		}
		
		// create the table is we need to create it
		if(!$this->was_init) {
			
			$q = "
CREATE TABLE IF NOT EXISTS `{$this->tablename}` (
  `cache_key` varchar(20) NOT NULL,
  `block_num` int(10) unsigned NOT NULL,
  `block_contents` varchar({$this->block_size}) NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`cache_key`,`block_num`)
) ENGINE=MEMORY DEFAULT CHARSET=latin1
";
			mysqli_query($mysqli, $q);
			$this->was_init = true;
		}
		
		$this->connection = $mysqli;
	}
	
	/**
	 * Closes the connection
	 */
	private function end_connection() {
		mysqli_close($this->connection);
		$this->connection = null;
	}
	
	// Stores in cache. Overrides existing data.
	public function store($key, $value, $config)
	{
		$this->start_connection($config);
		
		if(!empty($config["MaxCachedSeconds"])) {
			$this->clean($config);
		}
		
		$this->remove_exec($key, $config);
		$result = $this->store_exec($key, $value, $config);
		
		$this->end_connection();
		
		return $result;
	}

	// Returns FALSE if key does not exist. 
	public function pull($key, $config)
	{
		$this->start_connection($config);
		
		if (!$this->exists_exec($key, $config)) {
			throw new Exception("MySQLMemory cache provider was not able to find page with the key: $key ");
		}
		
		$result = $this->pull_exec($key, $config);
		
		if($config["DeleteAfterPull"]) {
			$this->remove_exec($key, $config);
		}
		
		$this->end_connection();
		
		return $result;
	}
	
	public function remove($key, $config)
	{
		$this->start_connection($config);

		$result = $this->remove_exec($key, $config);
		
		$this->end_connection();
		
		return $result;
	}
	
	public function exists($key, $config)
	{
		$this->start_connection($config);

		$result = $this->exists_exec($key, $config);
		
		$this->end_connection();
		
		return $result;
	}
	
	
	//
	//
	// Actual SQL statements
	//
	//
	
	
	
	private function store_exec($key, $value, $config)
	{	
		$result = "";

		$blocksize = $this->block_size;
		
		$timestamp = time();
		
		$q = "
INSERT INTO `{$this->tablename}` 
SELECT ?, BLOCK_NUMBERS.blockNumber + 1, SUBSTR(?,BLOCK_NUMBERS.blockNumber * $blocksize + 1, $blocksize), $timestamp FROM
  (
SELECT
    (HUNDREDS.blockNumber + TENS.blockNumber + ONES.blockNumber) blockNumber
FROM
    (
    SELECT 0  blockNumber
    UNION ALL
    SELECT 1 blockNumber
    UNION ALL
    SELECT 2 blockNumber
    UNION ALL
    SELECT 3 blockNumber
    UNION ALL
    SELECT 4 blockNumber
    UNION ALL
    SELECT 5 blockNumber
    UNION ALL
    SELECT 6 blockNumber
    UNION ALL
    SELECT 7 blockNumber
    UNION ALL
    SELECT 8 blockNumber
    UNION ALL
    SELECT 9 blockNumber
    ) ONES
CROSS JOIN
    (
    SELECT 0 blockNumber
    UNION ALL
    SELECT 10 blockNumber
    UNION ALL
    SELECT 20 blockNumber
    UNION ALL
    SELECT 30 blockNumber
    UNION ALL
    SELECT 40 blockNumber
    UNION ALL
    SELECT 50 blockNumber
    UNION ALL
    SELECT 60 blockNumber
    UNION ALL
    SELECT 70 blockNumber
    UNION ALL
    SELECT 80 blockNumber
    UNION ALL
    SELECT 90 blockNumber
    ) TENS
CROSS JOIN
    (
    SELECT 0 blockNumber
    UNION ALL
    SELECT 100 blockNumber
    UNION ALL
    SELECT 200 blockNumber
    UNION ALL
    SELECT 300 blockNumber
    UNION ALL
    SELECT 400 blockNumber
    UNION ALL
    SELECT 500 blockNumber
    UNION ALL
    SELECT 600 blockNumber
    UNION ALL
    SELECT 700 blockNumber
    UNION ALL
    SELECT 800 blockNumber
    UNION ALL
    SELECT 900 blockNumber
    ) HUNDREDS
) BLOCK_NUMBERS
WHERE blockNumber <= (LENGTH(?) )/$blocksize
";

		if ($stmt = mysqli_prepare($this->connection, $q)) {
			mysqli_stmt_bind_param($stmt, "sss", $key, $value, $value);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_close($stmt);
		} else {
			throw new Exception(mysqli_error($this->connection));
		}
		
		return null;
	}
	
	
	
	
	private function pull_exec($key, $config)
	{	
		$result = "";
		$page = "";

		$q = "SELECT block_contents";
		$q .= " FROM `{$this->tablename}`";
		$q .= " WHERE cache_key=?";
		$q .= " ORDER BY block_num";
		
		if ($stmt = mysqli_prepare($this->connection, $q)) {
			mysqli_stmt_bind_param($stmt, "s", $key);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_bind_result($stmt, $result);
			while(mysqli_stmt_fetch($stmt)) {
				$page .= $result;
			}
			mysqli_stmt_close($stmt);
		} else {
			throw new Exception(mysqli_error($this->connection));
		}
		
		return $page;
	}
	
	private function remove_exec($key, $config)
	{
		$q = "DELETE FROM `{$this->tablename}` WHERE cache_key = ?";
		$result = null;
		
		if ($stmt = mysqli_prepare($this->connection, $q)) {
			mysqli_stmt_bind_param($stmt, "s", $key);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_close($stmt);
		} else {
			throw new Exception(mysqli_error($this->connection));
		}
		return null;
	}
	
	private function exists_exec($key, $config)
	{
		$q = "SELECT COUNT(block_num) FROM `{$this->tablename}` WHERE cache_key = ? AND block_num = 1";
		$result = null;
		
		if ($stmt = mysqli_prepare($this->connection, $q)) {
			mysqli_stmt_bind_param($stmt, "s", $key);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_bind_result($stmt, $result);
			mysqli_stmt_fetch($stmt);
			mysqli_stmt_close($stmt);
		} else {
			throw new Exception(mysqli_error($this->connection));
		}
		return $result > 0;
	}
	
	
	private function clean($config) 
	{
		$maxCachedSeconds = $config["MaxCachedSeconds"];
		// we need to delete all records before this timestamp
		$firstTimestamp = time() - $maxCachedSeconds;

		$q = "DELETE FROM `{$this->tablename}` WHERE timestamp < ?";
		
		if ($stmt = mysqli_prepare($this->connection, $q)) {
			mysqli_stmt_bind_param($stmt, "i", $firstTimestamp);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_close($stmt);
		} else {
			throw new Exception(mysqli_error($this->connection));
		}
		return;
	}
	
	
	
	public function is_config_valid($config)
	{
		try {
			$this->start_connection($config);
		} catch (Exception $e) {
			return false;
		}
		return true;
	}
	
	public function config_validation($config)
	{
		if($this->is_config_valid($config)) {
			return array(
				"Connected to MySQL successfully"
			);
		} else {
			return array(
				"MySQL error: ".mysqli_connect_error()
			);
		}
	}
	
	public function refresh($key, $config) {
		$tablename = $this->tablename;
		$timestamp = time();
		$q = "UPDATE $tablename SET timestamp = ? WHERE cache_key = ?";
		
		try {
			$this->start_connection($config);
		} catch (Exception $e) {
			return false;
		}
		
		if ($stmt = mysqli_prepare($this->connection, $q)) {
			mysqli_stmt_bind_param($stmt, "is", $timestamp, $key);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_close($stmt);
		} else {
			throw new Exception(mysqli_error($this->connection));
		}
		return true;
	}
	
}

?>
