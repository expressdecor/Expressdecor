<?php

mysql_connect("ed.database.master","edadmin","1wvcDW7j");
mysql_select_db("expressdecor");

$sql= "SELECT count(created_at) as totalOrders
	FROM sales_flat_order
	WHERE date_sub( created_at, INTERVAL 4 HOUR ) >= curdate()
	AND date_sub( created_at, INTERVAL 4 HOUR ) < date_add( curdate() , INTERVAL 1 DAY )";
//Alex sql orders for today
//$sql="SELECT count(created_at) as totalOrders
  //      FROM sales_flat_order
//	WHERE
//	created_at >= curdate()";
$data = mysql_query($sql);

$row = mysql_fetch_assoc($data);
echo $row['totalOrders'];

?>
