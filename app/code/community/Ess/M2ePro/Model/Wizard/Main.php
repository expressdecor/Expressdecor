<?php

/*
* @copyright  Copyright (c) 2011 by  ESS-UA.
*/

class Ess_M2ePro_Model_Wizard_Main extends Ess_M2ePro_Model_Wizard
{
    const TITLE = 'Main';

    // ########################################

    protected $steps = array(
        'cron',
        'license',
        'settings',
        'synchronization'
    );

    // ########################################

    public function getTitle()
    {
        return self::TITLE;
    }

    // ########################################
}