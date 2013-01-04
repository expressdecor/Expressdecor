<?php

class Expressdecor_Poll_Block_ActivePoll extends Mage_Poll_Block_ActivePoll
{
    
 

    /**
     * Get Poll Id to show
     *
     * @return int
     */
    public function getPollToShow()
    {
        if ($this->getPollId()) {
            return $this->getPollId();
        }
        // get last voted poll (from session only)
        $pollId = Mage::getSingleton('core/session')->getJustVotedPoll();
        if (empty($pollId)) {
            // get random not voted yet poll
            // $votedIds = $this->getVotedPollsIds(); Commented by Alex 12/31/2012
            $pollId = $this->_pollModel
                ->setExcludeFilter($votedIds)
                ->setStoreFilter(Mage::app()->getStore()->getId())
                ->getRandomId();
        }
        $this->setPollId($pollId);

        return $pollId;
    }

}
