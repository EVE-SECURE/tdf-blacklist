<?php
// Functions / Classes

//
// MySQL Database connection
//
function mysqlConnect($host, $user, $password, $database) {
	$mysql = mysql_connect($host, $user, $password);
	if (!$mysql) die(mysql_error());
	mysql_select_db($database, $mysql);
}

// MySQL Query
// usage:
//    $query such as "SELECT * FROM users WHERE nick = '%s' AND pass = '%s'"
//      where %s are the placeholders for the parameters
// call:
//    $__->mysqlQuery( $query, arg0, arg1, arg2, ... )
// description:
//    The parameters given to the function are escaped through mysql_real_escape_string(),
//    merged with the query and then given to the mysql_query function.
// returns:
//    Array of lines returned by the MySQL Database
//
function mysqlQuery($query) {
	$numParams = func_num_args();
	$params = func_get_args();
	
	if ($numParams > 1) {
		for ($i = 1; $i < $numParams; $i++){
			$params[$i] = mysql_real_escape_string($params[$i]);
		}
	
		$query = call_user_func_array('sprintf', $params);
	}
	
	$result = mysql_query($query);
	if (!$result) die(mysql_error());
	if(is_bool($result) == true) {
		$ret = $result;
	} else {
		$ret = array();
		while ($row = mysql_fetch_assoc($result)) {
			$ret[] = $row;
		}
		mysql_free_result($result);
	}
	return $ret;
}
?>