<?php

class Expressdecor_Adminhtml_Block_Sales_Order_View_Tab_History extends Mage_Adminhtml_Block_Sales_Order_View_Tab_History
{
public function getFullHistory()
{

	$order = $this->getOrder();
	$history = array();
	foreach ($order->getAllStatusHistory() as $orderComment){
		$history[$orderComment->getEntityId()] = $this->_prepareHistoryItem(
		$orderComment->getStatusLabel(),
		$orderComment->getIsCustomerNotified(),
		$orderComment->getCreatedAtDate(),
		$orderComment->getComment(),
		$orderComment->getTrackUser(),
		$orderComment->getTrackUserName()
		);
	}

	foreach ($order->getCreditmemosCollection() as $_memo){
		$history[$_memo->getEntityId()] =
		$this->_prepareHistoryItem($this->__('Credit Memo #%s created', $_memo->getIncrementId()),
		$_memo->getEmailSent(), $_memo->getCreatedAtDate());

		foreach ($_memo->getCommentsCollection() as $_comment){
			$history[$_comment->getEntityId()] =
			$this->_prepareHistoryItem($this->__('Credit Memo #%s comment added', $_memo->getIncrementId()),
			$_comment->getIsCustomerNotified(), $_comment->getCreatedAtDate(), $_comment->getComment(),$_comment->getTrackUser(),$_comment->getTrackUserName());
		}
	}

	foreach ($order->getShipmentsCollection() as $_shipment){
		$history[$_shipment->getEntityId()] =
		$this->_prepareHistoryItem($this->__('Shipment #%s created', $_shipment->getIncrementId()),
		$_shipment->getEmailSent(), $_shipment->getCreatedAtDate());

		foreach ($_shipment->getCommentsCollection() as $_comment){
			$history[$_comment->getEntityId()] =
			$this->_prepareHistoryItem($this->__('Shipment #%s comment added', $_shipment->getIncrementId()),
			$_comment->getIsCustomerNotified(), $_comment->getCreatedAtDate(), $_comment->getComment(),$_comment->getTrackUser(),$_comment->getTrackUserName());
		}
	}

	foreach ($order->getInvoiceCollection() as $_invoice){
		$history[$_invoice->getEntityId()] =
		$this->_prepareHistoryItem($this->__('Invoice #%s created', $_invoice->getIncrementId()),
		$_invoice->getEmailSent(), $_invoice->getCreatedAtDate());

		foreach ($_invoice->getCommentsCollection() as $_comment){
			$history[$_comment->getEntityId()] =
			$this->_prepareHistoryItem($this->__('Invoice #%s comment added', $_invoice->getIncrementId()),
			$_comment->getIsCustomerNotified(), $_comment->getCreatedAtDate(), $_comment->getComment(),$_comment->getTrackUser(),$_comment->getTrackUserName());
		}
	}

	foreach ($order->getTracksCollection() as $_track){
		$history[$_track->getEntityId()] =
		$this->_prepareHistoryItem($this->__('Tracking number %s for %s assigned', $_track->getNumber(), $_track->getTitle()),
		false, $_track->getCreatedAtDate());
	}

	krsort($history);
	return $history;
}

protected function _prepareHistoryItem($label, $notified, $created, $comment = '' , $trackUser = '' , $trackUserName ='')
{
	return array(
	'title'      => $label,
	'notified'   => $notified,
	'track_user' => $trackUser,
	'track_user_name' => $trackUserName,
	'comment'    => $comment,
	'created_at' => $created
	);
}

}