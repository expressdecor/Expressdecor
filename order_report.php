<?php
 header("Content-type: text/csv");
 header("Cache-Control: no-store, no-cache");
header('Content-Disposition: attachment; filename="report_orders.csv"');

require_once 'app/Mage.php';
Mage::app ( 'default' );

$days=$_GET['days'];
 
if (!isset($days)){
$days="35";
}

$date_from = date ( 'Y-m-d', strtotime ( '-'.$days.' days 4 hours' ) ); // 2011-10-04 19:56:16
 

$orders=Mage::getModel('sales/order')
			->getCollection()
 			->addAttributeToFilter('created_at', array('from' => $date_from,  'date'=>true  )) 
			->addAttributeToSort ( 'created_at', 'DESC' );
//echo "Total: ".count($orders)." from ".$date_from;
//echo "<br/>";
 

$orders_users=array();

$arr_data = array();

foreach ($orders as $order) {

	$order_increment_id=$order->getIncrementId();
	$orders_history=Mage::getModel('sales/order_status_history')->getCollection()->addAttributeToFilter('parent_id',array('eq'=>$order->getId()));

	foreach ($orders_history as $order_history){
		//echo $order_increment_id.' '.$order->getCreatedAt()." ".$order_history->getCreatedAt()." ".$order_history->getTrackUser()."<br/>";
		//print_r($orders_history->getData());
		 
// 		if ($order->getCreatedAt()==$order_history->getCreatedAt()){			
			$orders_users[$order_increment_id]['user']=$order_history->getTrackUser();
			$orders_users[$order_increment_id]['date']=$order->getCreatedAt();
			$orders_users[$order_increment_id]['sold_to']=$order->getCustomerFirstname()." ".$order->getCustomerLastname();
			$orders_users[$order_increment_id]['amount']=$order->getGrandTotal();
			$orders_users[$order_increment_id]['tax']=$order->getTaxAmount();
			$orders_users[$order_increment_id]['status']=$order->getStatus();
			$order_date=Mage::app()->getLocale()->date(strtotime($order->getCreatedAt()));
			$skus_array=array();
			foreach ($order->getAllItems() as $item) {
				 array_push($skus_array, $item->getSku());
			}
			$skus=implode(',', $skus_array);
			$orders_users[$order_increment_id]['skus']=$skus;
			// for excel
			$arr_data_row=array($order_increment_id,$order_date,$orders_users[$order_increment_id]['sold_to'],$order_history->getTrackUser(),$skus,$order->getStatusLabel(),$order->getGrandTotal(),$order->getTaxAmount(),$order->getChannel());
			array_push ( $arr_data, $arr_data_row );
// 		}
		break;
	}
	//die();
}

asort($orders_users);
//$collection=new Varien_Data_Collection();

//print_r($order_history);

$arr_columns = array ('Order #', 'Created Date', 'Customer', ' Created By', 'Items', 'Status', 'Amount', 'Tax', 'Channel');

exportCSV ( $arr_data, $arr_columns );

function exportCSV($data, $col_headers = array(), $return_string = false) {
	$stream = ($return_string) ? fopen ( 'php://temp/maxmemory', 'w+' ) : fopen ( 'php://output', 'w' );
	/*to file*/
	//$myFile = "g.tsv";
	//$fh = fopen($myFile, 'w+') or die("can't open file");
	 
	if (! empty ( $col_headers )) {
				fputcsv ( $stream, $col_headers );
		/*to file*/
		//fputcsv($fh, $col_headers);
	}
	foreach ( $data as $record ) {
			fputcsv ( $stream, $record );
		/*to file*/
		//fputcsv($fh, $record);
	}
	if ($return_string) {
				rewind ( $stream );
				$retVal = stream_get_contents ( $stream );
		/*to file*/
		//fclose($fh);

			fclose ( $stream );
		return $retVal;
	} else {
		/*to file*/
		//fclose($fh);
				fclose ( $stream );
	}
}
