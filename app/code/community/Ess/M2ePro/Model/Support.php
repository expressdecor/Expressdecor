<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 * Shipping method with custom title and price
 */

class Ess_M2ePro_Model_Support
{
    //#############################################

    public function getUserVoiceData($query) {

        $userVoiceEnabled = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/support/uservoice/', 'mode'
        );

        if (!$userVoiceEnabled) {
            return json_encode(array());
        }

        if (!is_null($query)) {

            $query = strip_tags($query);

            $userVoiceApiUrl = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
                '/support/uservoice/', 'baseurl'
            );
            $action = 'articles/search.json';
            $client = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/uservoice/', 'client_key');
            $params = array(
                'client' => $client,
                'query' => $query,
                'page' => 1,
                'per_page' => 10
            );

            $response = $this->sendRequestAsGet($userVoiceApiUrl, $action, $params);

            if ($response === false) {
                return json_encode(array());
            }

            return $response;
        }

        $articlesBackupKey = Mage::helper('M2ePro/Module')->getName().'_BACKUP_USERVOICE_ARTICLES';
        $articlesBackup = Mage::app()->getCache()->load($articlesBackupKey);

        if ($articlesBackup === false) {

            $userVoiceApiUrl = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
                '/support/uservoice/', 'baseurl'
            );
            $action = 'articles.json';
            $client = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/uservoice/', 'client_key');
            $params = array(
                'client' => $client,
                'page' => 1,
                'per_page' => 10
            );

            $response = $this->sendRequestAsGet($userVoiceApiUrl, $action, $params);

            if ($response === false) {
                return json_encode(array());
            }

            Mage::app()->getCache()->save(serialize($response), $articlesBackupKey, array(), 60*60*24);
            return $response;
        }

        return unserialize($articlesBackup);
    }

    //---------------------------------------------

    private function sendRequestAsGet($baseUrl, $action, $params)
    {
        $curlObject = curl_init();

        //set the server we are using
        curl_setopt($curlObject, CURLOPT_URL, $baseUrl . $action . '?'.http_build_query($params,'','&'));

        // stop CURL from verifying the peer's certificate
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYHOST, false);

        // disable http headers
        curl_setopt($curlObject, CURLOPT_HEADER, false);
        curl_setopt($curlObject, CURLOPT_POST, false);

        // set it to return the transfer as a string from curl_exec
        curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlObject, CURLOPT_CONNECTTIMEOUT, 300);

        $response = curl_exec($curlObject);
        curl_close($curlObject);

        return $response;
    }

    //#############################################

    public function send($data)
    {
        $toEmail   = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/form/', $data['type'].'_mail');
        $fromEmail = $data['contact_mail'];
        $fromName  = $data['contact_name'];
        $subject   = $data['subject'];

        $component = 'None';
        if ($data['component'] == Ess_M2ePro_Helper_Component_Ebay::NICK) {
            $component = Ess_M2ePro_Helper_Component_Ebay::TITLE;
        }
        if ($data['component'] == Ess_M2ePro_Helper_Component_Amazon::NICK) {
            $component = Ess_M2ePro_Helper_Component_Amazon::TITLE;
        }

        $body = $this->createBody($data['type'],$data['subject'],$component,$data['description']);

        $attachments = array();

        if (isset($_FILES['files'])) {
            foreach ($_FILES['files']['name'] as $key => $uploadFileName) {
                if ('' == $uploadFileName) {
                    continue;
                }

                $realName = $uploadFileName;
                $tempPath = $_FILES['files']['tmp_name'][$key];
                $mimeType = $_FILES['files']['type'][$key];

                $attachment = new Zend_Mime_Part(file_get_contents($tempPath));
                $attachment->type        = $mimeType;
                $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
                $attachment->encoding    = Zend_Mime::ENCODING_BASE64;
                $attachment->filename    = $realName;

                $attachments[] = $attachment;
            }
        }

        $this->sendMail($toEmail, $fromEmail, $fromName, $subject, $body, $attachments);
    }

    //---------------------------------------------

    private function createBody($type, $subject, $component, $description)
    {
        $currentDate = Mage::helper('M2ePro')->getCurrentGmtDate();

        $body = <<<DATA

{$description}

-------------------------------- GENERAL -----------------------------------------
Date: {$currentDate}
Type: {$type}
Component: {$component}
Subject: {$subject}


DATA;

        $body .= Mage::helper('M2ePro/Exception')->getGeneralSummaryInfo();

        return $body;
    }

    //---------------------------------------------

    private function sendMail($toEmail, $fromEmail, $fromName, $subject, $body, array $attachments = array())
    {
        $mail = new Zend_Mail('UTF-8');

        $mail->addTo($toEmail)
             ->setFrom($fromEmail, $fromName)
             ->setSubject($subject)
             ->setBodyText($body, null, Zend_Mime::ENCODING_8BIT);

        foreach ($attachments as $attachment) {
            $mail->addAttachment($attachment);
        }

        $mail->send();
    }

    //#############################################
}