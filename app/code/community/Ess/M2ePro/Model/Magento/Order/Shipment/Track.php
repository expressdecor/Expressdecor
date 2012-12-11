<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Magento_Order_Shipment_Track
{
    /** @var $shipment Mage_Sales_Model_Order_Shipment */
    private $shipment = NULL;

    private $supportedCarriers = array();

    private $trackingDetails = array();

    private $tracks = array();

    // ########################################

    public function setShipment(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $this->shipment = $shipment;
        return $this;
    }

    // ########################################

    public function setTrackingDetails(array $trackingDetails)
    {
        $this->trackingDetails = $trackingDetails;
        return $this;
    }

    // ########################################

    public function setSupportedCarriers(array $supportedCarriers)
    {
        $this->supportedCarriers = $supportedCarriers;
        return $this;
    }

    // ########################################

    public function getTracks()
    {
        return $this->tracks;
    }

    // ########################################

    public function buildTracks()
    {
        $this->prepareTracks();
    }

    // ########################################

    private function prepareTracks()
    {
        $trackingDetails = $this->getFilteredTrackingDetails();
        if (count($trackingDetails) == 0) {
            return NULL;
        }

        // Skip shipment observer
        // -----------------
        Mage::helper('M2ePro')->unsetGlobalValue('skip_shipment_observer');
        Mage::helper('M2ePro')->setGlobalValue('skip_shipment_observer', true);
        // -----------------

        foreach ($trackingDetails as $trackingDetail) {
            /** @var $track Mage_Sales_Model_Order_Shipment_Track */
            $track = Mage::getModel('sales/order_shipment_track');
            $track->setNumber($trackingDetail['number'])
                  ->setTitle($trackingDetail['title'])
                  ->setCarrierCode($this->getCarrierCode($trackingDetail['title']));
            $this->shipment->addTrack($track)->save();

            $this->tracks[] = $track;
        }
    }

    // ----------------------------------------

    private function getFilteredTrackingDetails()
    {
        if ($this->shipment->getTracksCollection()->getSize() <= 0) {
            return $this->trackingDetails;
        }

        // Filter exist tracks
        // ------------------------
        foreach ($this->shipment->getTracksCollection() as $track) {

            foreach ($this->trackingDetails as $key => $trackingDetail) {
                if ($track->getData('number') == $trackingDetail['number']) {
                    unset($this->trackingDetails[$key]);
                }
            }

        }
        // ------------------------

        return $this->trackingDetails;
    }

    // ----------------------------------------

    private function getCarrierCode($title)
    {
        $carrierCode = strtolower($title);

        return isset($this->supportedCarriers[$carrierCode]) ? $carrierCode : 'custom';
    }

    // ########################################
}