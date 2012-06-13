<?php
/**
 *  OneStepCheckout main block
 *  @author Jone Eide <mail@onestepcheckout.com>
 *  @copyright Jone Eide <mail@onestepcheckout.com>
 *
 */
class Idev_OneStepCheckout_Block_Checkout extends Mage_Checkout_Block_Onepage_Abstract  {

    public $formErrors;
    public $settings;
    public $log = array();
    public $_rates = 0;

    const SESSION_ADDRESS_CHECK_NAME = 'onestepcheckout_address_check_name';

    private function _loadConfig()
    {
        $this->settings = Mage::helper('onestepcheckout/checkout')->loadConfig();
    }

    private function _checkAddress()
    {
        $quote = $this->getOnepage()->getQuote();
        $shipping = $quote->getShippingAddress();
        $billing = $quote->getBillingAddress();

        // Check if user is logged in and has not received test for billign address
        if($this->isCustomerLoggedIn()) {

            $saved_quote_postcode = $shipping->getPostcode();

            if(!$saved_quote_postcode)  {
                $shipping_postcode = $this->getShippingPostcode();
                $billing_postcode = $this->getPostcode();

                $postcode = $shipping_postcode;

                if(!$shipping_postcode || $shipping_postcode == '') {
                    $postcode = $billing_postcode;
                }

                $shipping->setPostcode($postcode)->setCollectShippingRates(true)->save();
            }
        }
    }

    private function _checkCountry()
    {
        $onepage = $this->getOnepage();
        $quote = $onepage->getQuote();
        $shipping = $quote->getShippingAddress();
        $billing = $quote->getBillingAddress();
        $default_country = false;
        $country_id = $shipping->getCountryId();

        if(!$country_id || $country_id == '')   {
            // No country saved at this point



            $default_country = false;

            if( $this->settings['enable_geoip'] )   {

                $geoip = Net_GeoIP::getInstance($this->settings['geoip_database']);

                try {
                    $default_country = $geoip->lookupCountryCode($_SERVER['REMOTE_ADDR']);
                    $this->log[] = 'Set country based on GeoIP (result: ' . $default_country . ')';
                } catch(Exception $e)   {
                    $default_country = false;
                    $this->log[] = 'GeoIP threw exception: ' . $e->getMessage();
                }
            }

            if(!$default_country)   {
                if( $this->settings['default_country'] )    {
                    $default_country = $this->settings['default_country'];
                    $this->log[] = 'Set country based on default country settings (result: ' . $default_country . ')';
                }
                else    {
                    $default_country = 'US'; // Last resort
                    $this->log[] = 'Set country to US as a last resort';
                }
            }

            if($this->_isLoggedIn() && $this->getQuote()->getCustomer()->getPrimaryBillingAddress())    {
                $country_id = $this->getQuote()->getCustomer()->getPrimaryBillingAddress()->getCountryId();
                if(!empty($country_id)) {
                    $default_country = $country_id;
                }
            }


            if($default_country)    {
                //$shipping->setCountryId($default_country)->save();

                $shipping->setCountryId($default_country)->setCollectShippingRates(true)->save();
                $billing->setCountryId($default_country)->save();
            }

            /* Hack to set same shipping as billing by default */


            $shipping->setSameAsBilling(true)->save();


        }
        else    {
            $this->log[] = 'Country already saved, don\'t touch it';
        }
    }

    public function _getDefaultShippingMethod()
    {
        if($this->settings['default_shipping_method'] != '')    {
            return $this->settings['default_shipping_method'];
        }
        else    {
            $check_single = $this->_checkSingleShippingMethod();
            if($check_single)   {
                return $check_single;
            }
        }
    }

    private function _checkShippingMethod()
    {
        $onepage = $this->getOnepage();
        $quote = $this->getOnepage()->getQuote();
        $method = $quote->getShippingAddress()->getShippingMethod();

        if(!$method || $method == '')   {
            // If no pre-set shipping method
            if( $this->settings['default_shipping_method'] != '' )  {

                Mage::helper('onestepcheckout/checkout')->saveShippingMethod($this->settings['default_shipping_method']);
                //$onepage->saveShippingMethod( $this->settings['default_shipping_method'] );
                $this->log[] = 'Set shipping method based on default settings (set to: ' . $this->settings['default_shipping_method'] . ')';
            }
            else    {
                $method = $this->_checkSingleShippingMethod();

                if($method) {
                    //$onepage->saveShippingMethod($method);
                    Mage::helper('onestepcheckout/checkout')->saveShippingMethod($method);
                    $this->log[] ='Set shipping method to ' . $method . ' because it was only option available';
                }
                else    {
                    $this->log[] = 'No shipping method to set as default, leave blank';
                }
            }
        }
        else    {
            $this->log[] = 'Shipping method already saved, don\'t touch it';
        }
    }

    private function _checkPaymentMethod()
    {

        $onepage = $this->getOnepage();
        $method = $onepage->getQuote()->getPayment()->getMethod();

        if(!$method || $method == '')   {


            if( $this->settings['default_payment_method'] != '' )   {


                $payment = array('method' => $this->settings['default_payment_method'] );

                try {
                    //$result = $onepage->savePayment($payment);
                    $result = Mage::helper('onestepcheckout/checkout')->savePayment($payment);
                }
                catch (Mage_Payment_Exception $e) {
                    if ($e->getFields()) {
                        $result['fields'] = $e->getFields();
                    }
                    $result['error'] = $e->getMessage();
                }
                catch (Exception $e) {
                    $result['error'] = $e->getMessage();
                }



                if(isset($result['error'])) {
                    $this->log[] = 'Unable to set default payment method (' . $this->settings['default_payment_method'] . ') with error: ' . $result['error'];
                }
                else    {
                    $this->log[] = 'Set payment method based on default settings (set to: ' . $this->settings['default_payment_method'] . ')';
                }
            }
            else    {
                $this->log[] = 'No payment method set as default, leave blank';
            }
        }
        else    {
            $this->log[] = 'Payment method set already, don\'t touch';
        }
    }

    private function _checkSingleShippingMethod()
    {
        $rates = $this->getOnepage()->getQuote()->getShippingAddress()->getShippingRatesCollection();
        $rateCodes = array();

        foreach($rates as $rate)    {
            if(!in_array($rate->getCode(), $rateCodes)) {
                $rateCodes[] = $rate->getCode();
            }
        }

        if(count($rateCodes) == 1)  {
            return $rateCodes[0];
        }

        return false;
    }

    private function _isLoggedInWithAddresses()
    {
        $helper = $this->helper('customer');
        if( $helper->isLoggedIn() && $helper->customerHasAddresses() )  {
            return true;
        }

        return false;
    }

    private function _isLoggedIn()
    {
        $helper = $this->helper('customer');
        if( $helper->isLoggedIn() ) {
            return true;
        }

        return false;

    }

    private function _checkLoggedInUser()
    {
        return;

        $helper = $this->helper('customer');

        if( $helper->isLoggedIn() && $helper->customerHasAddresses() )  {



            $customer = $helper->getCustomer();

            // Find the customer address id
            $address = $customer->getDefaultBillingAddress();
            $address_id = $address->getId();

            $data = array(
                'address_id' => $address->getId(),
                'firstname' => $address->getFirstname(),
                'lastname' => $address->getLastname(),
                'company' => $address->getCompany(),
                'street' => array($address->getStreet1(),$address->getStreet2()),
                'city' => $address->getCity(),
                'region_id' => $address->getRegionId(),
                'region' => $address->getRegion(),
                'country_id' => $address->getCountry(),
                'telephone' => $address->getTelephone(),
                'fax' => $address->getFax(),
                'use_for_shipping' => 1
            );

            $result = $this->getOnepage()->saveBilling($data, $address_id);
            $this->log[] = 'Set billing address to default address from account (ID: ' . $address_id . ')';

        }

    }

    public function _construct()
    {
        parent::_construct();

        $rates = $this->getEstimateRates();
        if($this->_isLoggedIn() && empty($rates)){
            if(Mage::helper('onestepcheckout/checkout')->differentShippingAvailable()){
                $primaryAddress = Mage::helper('customer')->getCustomer()->getDefaultShippingAddress();
            } else {
                $primaryAddress = Mage::helper('customer')->getCustomer()->getPrimaryBillingAddress();
            }

            if($primaryAddress) {
                $customerAddressId = $primaryAddress->getId();
                if(!empty($customerAddressId)){
                    $addressData= $primaryAddress->getData();
                    $result = $this->getOnepage()->saveShipping($addressData, $customerAddressId);
                }
            }
        }

        $this->email = false;
        $this->customer_after_place_order = false;

        $this->_loadConfig();

        if($this->settings['enable_geoip']) {
            require_once 'Net/GeoIP.php';
        }

        if($this->_isLoggedIn())    {
            $shippingCountry = $this->getQuote()->getBillingAddress()->getCountry();
            $primaryAddress = Mage::helper('customer')->getCustomer()->getPrimaryBillingAddress();
            if(empty($shippingCountry) && !empty($primaryAddress)){
                $this->getQuote()->getBillingAddress()->addData($primaryAddress->getData());
            }
            $helper = Mage::helper('customer');
            $customer = $helper->getCustomer();
            $this->email = $customer->getEmail();
        }

        //we need to refactor this , not a neat way to make all in constructor
        if($this->getSubTemplate()){
            return true;
        }


        $this->getQuote()->getShippingAddress()->setCollectShippingRates(true)->save();

        $this->_checkAddress();
        $this->_checkShippingMethod();
        $this->_checkCountry();
        $this->_checkPaymentMethod();

        $payment = $this->getQuote()->getPayment();
        if(is_object($payment)){

            try {
                $payment->getMethodInstance();
            } catch (Exception $e) {
            }

            $paymentMethod = $payment->getMethod();
            if ($this->getQuote()->isVirtual()) {
                $this->getQuote()->getBillingAddress()->setPaymentMethod(!empty($paymentMethod) ? $paymentMethod : null);
            } else {
                $this->getQuote()->getShippingAddress()->setPaymentMethod(!empty($paymentMethod) ? $paymentMethod : null);
            }
        }

        try {
            $this->_handlePostData();
        } catch(Exception $e)   {
            die('Error: ' . $e->getMessage());
        }
    }

    public function getEstimateRates()
    {
        if (empty($this->_rates)) {
            $groups = $this->getQuote()->getShippingAddress()->getGroupedAllShippingRates();
            $this->_rates = $groups;
        }
        return $this->_rates;
    }

    public function getAddressesHtmlSelect($type)
    {
        if ($this->isCustomerLoggedIn()) {
            $options = array();
            foreach ($this->getCustomer()->getAddresses() as $address) {
                $options[] = array(
                    'value'=>$address->getId(),
                    'label'=>$address->format('oneline')
                );
            }

            $addressId = '';
            if (empty($addressId)) {
                if ($type=='billing') {
                    $address = $this->getCustomer()->getDefaultBillingAddress();
                } else {
                    $address = $this->getCustomer()->getDefaultShippingAddress();
                }
                if ($address) {
                    $addressId = $address->getId();
                }
            }

            if ($type=='billing') {
                $address = $this->getQuote()->getBillingAddress();
            } else {
                $address = $this->getQuote()->getShippingAddress();
            }
            if ($address) {
                    $addressIde = $address->getCustomerAddressId();
                    if($addressIde){
                        $addressId = $addressIde;
                    }
            }

            $select = $this->getLayout()->createBlock('core/html_select')
                ->setName($type.'_address_id')
                ->setId($type.'-address-select')
                ->setClass('address-select')
                ->setExtraParams('onchange="'.$type.'.newAddress(!this.value)"')
                ->setValue($addressId)
                ->setOptions($options);

            $select->addOption('', Mage::helper('checkout')->__('New Address'));

            $isPost = $this->getRequest()->getPost();
            $isPost = (!empty($isPost));
            $selectedValue = $this->getRequest()->getPost('billing_address_id', false);


            if($this->getNewAddressSelectValueOnError($type)){
                 $select->setValue('');
            }

            return $select->getHtml();
        }
        return '';
    }

    public function getNewAddressSelectValueOnError($type){

        if ($type=='billing') {
            $selectedValue = $this->getRequest()->getPost('billing_address_id', false);
        } else {
            $selectedValue = $this->getRequest()->getPost('shipping_address_id', false);
        }
        $isPost = $this->getRequest()->getPost();
        $isPost = (!empty($isPost));

        if($isPost && $selectedValue == ''){
            return true;
        }

        return false;
    }

    public function hasAjaxSaveBillingField($name)
    {
        $fields = explode(',', $this->settings['ajax_save_billing_fields']);

        if(in_array($name, $fields))    {
            return true;
        }

        return false;
    }

    public function sameAsBilling()
    {
        $return = true;

        if($_SERVER['REQUEST_METHOD'] == 'POST')    {
            if(empty($_POST['billing']['use_for_shipping']))   {
                $return = false;
            } else {
                $return = true;
            }
        }

        $address = $this->getQuote()->getShippingAddress();

        if(!$this->getQuote()->getShippingAddress()->getSameAsBilling()) {
            $return = false;
        } else {
            $return = true;
        }

        $customer = $this->helper('customer');
        if ($customer->isLoggedIn()) {

            $shippingId = $customer->getCustomer()->getDefaultShippingAddress();
            $billingId = $customer->getCustomer()->getPrimaryBillingAddress();

            if(is_object($billingId) && is_object($shippingId) && ($shippingId->getId()) != $billingId->getId()){
                $return = false;
            } else {
                $return = true;
            }
        }
        return $return;
    }

    public function differentShippingAvailable()
    {
        if($this->isVirtual())  {
            return false;
        }

        if($this->settings['enable_different_shipping'])    {
            return true;
        }

        return false;
    }

    public function isVirtual()
    {
        return $this->getOnepage()->getQuote()->isVirtual();
    }

    public function hasFormErrors()
    {
        if($this->hasShippingErrors() || $this->hasBillingErrors() || $this->hasMethodErrors() || $this->hasShipmentErrors()) {
            return true;
        }

        return false;
    }

    public function hasMethodErrors()
    {
        if(isset($this->formErrors['shipping_method']) && $this->formErrors['shipping_method']) {
            return true;
        }

        if(isset($this->formErrors['payment_method']) && $this->formErrors['payment_method'])   {
            return true;
        }

        if(isset($this->formErrors['payment_method_error']))    {
            return true;
        }


        if(isset($this->formErrors['terms_error'])) {
            return true;
        }

        return false;
    }

    public function hasShippingErrors()
    {
        if(isset($this->formErrors['shipping_errors']))  {
            if(count($this->formErrors['shipping_errors']) == 0) {
                return false;
            }
            return true;
        }
        else    {
            return true;
        }
    }

    public function hasBillingErrors()
    {
        if(count($this->formErrors) > 0)   {
            if(isset($this->formErrors['billing_errors']))  {
                if(count($this->formErrors['billing_errors']) == 0) {

                    return false;
                }
                return true;
            }
            else    {
                return true;
            }
        }
        return false;
    }

    public function hasShipmentErrors()
    {
        if(!empty($this->formErrors['shipping_method'])){
            return true;
        }
        return false;
    }

    public function getAvailableRates($rates){
        $return = array();
        if(!empty($rates)){
            foreach ($rates as $_code => $_rates){
                foreach ($_rates as  $rate){
                    $return[] = $rate->getCode();
                }
            }
        }
        return $return;
    }

    public function _handlePostData()
    {
        $this->formErrors = array(
            'billing_errors' => array(),
            'shipping_errors' => array(),
        );

        $post = $this->getRequest()->getPost();




        if(!$post) return;

        // Save billing information
        if( $this->_isLoggedInWithAddresses() && false )    {

            // User is logged in and has addresses

        }
        else    {

            $checkoutHelper = Mage::helper('onestepcheckout/checkout');

            $payment_data = $this->getRequest()->getPost('payment');


            $billing_data = $this->getRequest()->getPost('billing', array());
            $shipping_data = $this->getRequest()->getPost('shipping', array());

            $billing_data = $checkoutHelper->load_exclude_data($billing_data);
            $shipping_data = $checkoutHelper->load_exclude_data($shipping_data);

            if(!empty($billing_data)){
                $this->getQuote()->getBillingAddress()->addData($billing_data);
            }

            if($this->differentShippingAvailable()) {
                $this->getQuote()->getShippingAddress()->setCountryId($shipping_data['country_id'])->setCollectShippingRates(true);
            }

            if(isset($billing_data['email']))   {
                $this->email = $billing_data['email'];
            }

            if(!$this->_isLoggedIn()){
                $registration_mode = $this->settings['registration_mode'];
                if($registration_mode == 'auto_generate_account')   {
                    // Modify billing data to contain password also
                    $password = Mage::helper('onestepcheckout/checkout')->generatePassword();
                    $billing_data['customer_password'] = $password;
                    $billing_data['confirm_password'] = $password;
                    $this->getQuote()->getCustomer()->setData('password', $password);
                    $this->getQuote()->setData('password_hash',Mage::getModel('customer/customer')->encryptPassword($password));
                }


                if($registration_mode == 'require_registration' || $registration_mode == 'allow_guest')   {
                    if(!empty($billing_data['customer_password']) && !empty($billing_data['confirm_password']) && ($billing_data['customer_password'] == $billing_data['confirm_password'])){
                        $password = $billing_data['customer_password'];
                        $this->getQuote()->setCheckoutMethod('register');
                        $this->getQuote()->getCustomer()->setData('password', $password);
                        $this->getQuote()->setData('password_hash',Mage::getModel('customer/customer')->encryptPassword($password));
                    }
                }
            }


            $billingAddressId = $this->getRequest()->getPost('billing_address_id');
            $customerAddressId = (!empty($billingAddressId)) ? $billingAddressId : false ;
            $result = $this->getOnepage()->saveBilling($billing_data, $customerAddressId);

            if(!empty($billing_data['customer_password']) && !empty($billing_data['confirm_password']))   {
                // Trick to allow saving of
                $this->getOnepage()->saveCheckoutMethod('register');
            }

            $customerSession = Mage::getSingleton('customer/session');

            if (!empty($billing_data['dob']) && !$customerSession->isLoggedIn()) {
                $dob = Mage::app()->getLocale()->date($billing_data['dob'], null, null, false)->toString('yyyy-MM-dd');
                $this->getQuote()->setCustomerDob($dob);
                $this->getQuote()->setDob($dob);
                $this->getQuote()->getBillingAddress()->setDob($dob);
            }

            if($customerSession->isLoggedIn() && !empty($billing_data['dob'])){
                $dob = Mage::app()->getLocale()->date($billing_data['dob'], null, null, false)->toString('yyyy-MM-dd');
                $customer = Mage::getModel('customer/customer')
                ->setId($customerSession->getId())
                ->setWebsiteId($customerSession->getCustomer()->getWebsiteId())
                ->setEmail($customerSession->getCustomer()->getEmail())
                ->setDob($dob)
                ->save()
                ;
            }

            // set customer tax/vat number for further usage
            if (!empty($billing_data['taxvat'])) {
                $this->getQuote()->setCustomerTaxvat($billing_data['taxvat']);
                $this->getQuote()->setTaxvat($billing_data['taxvat']);
                $this->getQuote()->getBillingAddress()->setTaxvat($billing_data['taxvat']);
            }

            if($customerSession->isLoggedIn() && !empty($billing_data['taxvat'])){
                $customer = Mage::getModel('customer/customer')
                ->setId($customerSession->getId())
                ->setWebsiteId($customerSession->getCustomer()->getWebsiteId())
                ->setEmail($customerSession->getCustomer()->getEmail())
                ->setTaxvat($billing_data['taxvat'])
                ->save()
                ;
            }

            if(isset($result['error'])) {
                $this->formErrors['billing_error'] = true;
                $this->formErrors['billing_errors'] = $checkoutHelper->_getAddressError($result, $billing_data);
                $this->log[] = 'Error saving billing details: ' . implode(', ', $this->formErrors['billing_errors']);
            }

            // Validate stuff that saveBilling doesn't handle
            if(!$this->_isLoggedIn())   {
                $validator = new Zend_Validate_EmailAddress();
                if(!$billing_data['email'] || $billing_data['email'] == '' || !$validator->isValid($billing_data['email'])) {

                    if(is_array($this->formErrors['billing_errors']))   {
                        $this->formErrors['billing_errors'][] = 'email';
                    }
                    else    {
                        $this->formErrors['billing_errors'] = array('email');
                    }

                    $this->formErrors['billing_error'] = true;

                }
                else    {


                    $allow_guest_create_account_validation = false;

                    if($this->settings['registration_mode'] == 'allow_guest')   {
                        if(isset($_POST['create_account']) && $_POST['create_account'] == '1')  {
                            $allow_guest_create_account_validation = true;
                        }
                    }


                    if($this->settings['registration_mode'] == 'require_registration' || $this->settings['registration_mode'] == 'auto_generate_account' || $allow_guest_create_account_validation)  {
                        if($this->_customerEmailExists($billing_data['email'], Mage::app()->getWebsite()->getId()))   {

                            $allow_without_password = $this->settings['registration_order_without_password'];



                            if(!$allow_without_password)    {
                                if(is_array($this->formErrors['billing_errors']))   {
                                    $this->formErrors['billing_errors'][] = 'email';
                                    $this->formErrors['billing_errors'][] = 'email_registered';
                                }
                                else    {
                                    $this->formErrors['billing_errors'] = array('email','email_registered');
                                }
                            }
                            else    {
                            }
                        }
                        else    {

                            $password_errors = array();

                            if(!isset($billing_data['customer_password']) || $billing_data['customer_password'] == '')    {
                                $password_errors[] = 'password';
                            }

                            if(!isset($billing_data['confirm_password']) || $billing_data['confirm_password'] == '')    {
                                $password_errors[] = 'confirm_password';
                            }
                            else    {
                                if($billing_data['confirm_password'] !== $billing_data['customer_password']) {
                                    $password_errors[] = 'password';
                                    $password_errors[] = 'confirm_password';
                                }
                            }

                            if(count($password_errors) > 0) {
                                if(is_array($this->formErrors['billing_errors']))   {
                                    foreach($password_errors as $error) {
                                        $this->formErrors['billing_errors'][] = $error;
                                    }
                                }
                                else    {
                                    $this->formErrors['billing_errors'] = $password_errors;
                                }
                            }
                        }
                    }


                }
            }


            if($this->settings['enable_terms']) {
                if(!isset($post['accept_terms']) || $post['accept_terms'] != '1')   {
                    $this->formErrors['terms_error'] = true;
                }
            }



            $shippingAddressId = $this->getRequest()->getPost('shipping_address_id', false);

            if($this->differentShippingAvailable()) {
                if(!isset($billing_data['use_for_shipping']) || $billing_data['use_for_shipping'] != '1')   {
                    //$shipping_result = $this->getOnepage()->saveShipping($shipping_data, $shippingAddressId);
                    $shipping_result = Mage::helper('onestepcheckout/checkout')->saveShipping($shipping_data, $shippingAddressId);

                    if(isset($shipping_result['error']))    {
                        $this->formErrors['shipping_error'] = true;
                        $this->formErrors['shipping_errors'] = $checkoutHelper->_getAddressError($shipping_result, $shipping_data, 'shipping');
                    }
                }
                else    {
                    //$shipping_result = $this->getOnepage()->saveShipping($billing_data, $shippingAddressId);
                    $shipping_result = Mage::helper('onestepcheckout/checkout')->saveShipping($billing_data, $customerAddressId);
                }
            }
        }

        // Save shipping method
        $shipping_method = $this->getRequest()->getPost('shipping_method', '');

        if(!$this->isVirtual()){
            //additional checks if the rate is indeed available for chosen shippin address
            $availableRates = $this->getAvailableRates($this->getOnepage()->getQuote()->getShippingAddress()->getGroupedAllShippingRates());
            if(empty($shipping_method) || !in_array($shipping_method,$availableRates)){
                $this->formErrors['shipping_method'] = true;
            }
        }

        if(!$this->isVirtual() )  {
            //$result = $this->getOnepage()->saveShippingMethod($shipping_method);
            $result = Mage::helper('onestepcheckout/checkout')->saveShippingMethod($shipping_method);
            if(isset($result['error']))    {
                $this->formErrors['shipping_method'] = true;
            }
            else    {
                Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method', array('request'=>$this->getRequest(), 'quote'=>$this->getOnepage()->getQuote()));
            }
        }




        // Save payment method
        $payment = $this->getRequest()->getPost('payment', array());
        $paymentRedirect = false;

        /**
         * A fix for common one big form problem
         * we rename the fields in template and iterate over subarrays
         * to see if there's any values and set them to main scope
         */
        foreach($payment as $value){
            if(is_array($value) && !empty($value)){
                foreach($value as $key => $realValue){
                    if(!empty($realValue)){
                        $payment[$key]=$realValue;
                    }
                }
            }
        }

        /**
         * unset unnecessary fields
         */
        foreach ($payment as $key => $value){
            if(is_array($value)){
                unset($payment[$key]);
            }
        }

        //echo '<pre>' . print_r($_POST,1) . '</pre>';
        //echo '<pre>' . print_r($payment,1) . '</pre>';

        try {
            //$result = $this->getOnepage()->savePayment($payment);
            $result = Mage::helper('onestepcheckout/checkout')->savePayment($payment);
            $paymentRedirect = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if ($this->getOnepage()->getQuote()->isVirtual()) {
                $this->getOnepage()->getQuote()->getBillingAddress()->setPaymentMethod(!empty($payment['method']) ? $payment['method'] : null);
            } else {
                $this->getOnepage()->getQuote()->getShippingAddress()->setPaymentMethod(!empty($payment['method']) ? $payment['method'] : null);
            }
        }
        catch (Mage_Payment_Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = $e->getMessage();
        }
        catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }

        if(isset($result['error'])) {

            if($result['error'] == 'Can not retrieve payment method instance')  {
                $this->formErrors['payment_method'] = true;
            }
            else    {
                $this->formErrors['payment_method_error']  = $result['error'];
            }
        }


        if(!$this->hasFormErrors()) {
            // Handle newsletter
            $subscribe_newsletter = $this->getRequest()->getPost('subscribe_newsletter');

            if($this->settings['enable_newsletter'])    {
                if($subscribe_newsletter && $subscribe_newsletter == '1' )  {
                    $model = Mage::getModel('newsletter/subscriber');
                    $result = $model->loadByEmail($this->email);

                    if($result->getId() === NULL)   {
                        // Not subscribed, OK to subscribe
                        Mage::getModel('newsletter/subscriber')->subscribe($this->email);
                    }
                }
            }

            if($paymentRedirect && $paymentRedirect != '')  {
                Header('Location: ' . $paymentRedirect);
                die();
            }

            if( $this->_isLoggedIn() )  {
                // User is logged in
                // Place order as registered customer

                $this->_saveOrder();
                $this->log[] = 'Saving order as a logged in customer';



            }
            else    {

                if( $this->_isEmailRegistered() )   {

                    $registration_mode = $this->settings['registration_mode'];
                    $allow_without_password = $this->settings['registration_order_without_password'];

                    if($registration_mode == 'require_registration' || $registration_mode == 'auto_generate_account')   {

                        if($allow_without_password) {

                            // Place order on the emails account without the password
                            $this->setCustomerAfterPlace($this->_getCustomer());

                            $this->getOnepage()->saveCheckoutMethod('guest');
                            $this->_saveOrder();

                        }
                        else    {
                            // This should not happen, because validation should handle it
                            die('Validation did not handle it');
                        }
                    }
                    else    {
                        $this->getOnepage()->saveCheckoutMethod('guest');
                        $this->_saveOrder();
                    }


                    // Place order as customer with same e-mail address
                    $this->log[] = 'Save order on existing account with email address';




                }
                else    {

                    if($this->settings['registration_mode'] == 'require_registration')  {

                        // Save as register
                        $this->log[] = 'Save order as REGISTER';
                        $this->getOnepage()->saveCheckoutMethod('register');
                        $this->_saveOrder();
                    }
                    elseif($this->settings['registration_mode'] == 'allow_guest')   {
                        if(isset($_POST['create_account']) && $_POST['create_account'] == '1')  {
                            $this->getOnepage()->saveCheckoutMethod('register');
                            $this->_saveOrder();
                        }
                        else    {
                            $this->getOnepage()->saveCheckoutMethod('guest');
                            $this->_saveOrder();
                        }
                    }
                    else    {


                        $registration_mode = $this->settings['registration_mode'];

                        if($registration_mode == 'auto_generate_account')   {
                            $this->getOnepage()->saveCheckoutMethod('register');
                            $this->_saveOrder();
                        }
                        else    {
                            $this->getOnepage()->saveCheckoutMethod('guest');
                            $this->_saveOrder();
                        }
                    }
                }
            }
        }
    }

    protected function setCustomerAfterPlace($customer)
    {
        $this->customer_after_place_order = $customer;
    }

    protected function afterPlaceOrder()
    {
        $customer = $this->customer_after_place_order;
        $order_id = $this->getOnepage()->getLastOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);

        if($customer)   {
            $order->setCustomerId($customer->getId());
            $order->setCustomerIsGuest(false);
            $order->setCustomerGroupId($customer->getGroupId());
            $order->save();
        }
    }

    protected function _customerEmailExists($email, $websiteId = null)
    {
        $customer = Mage::getModel('customer/customer');
        if ($websiteId) {
            $customer->setWebsiteId($websiteId);
        }
        $customer->loadByEmail($email);
        if ($customer->getId()) {
            return $customer;
        }
        return false;
    }

    private function _getCustomer()
    {
        $model = Mage::getModel('customer/customer');
        $model->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($this->email);

        if($model->getId() == NULL) {
            return false;
        }

        return $model;
    }

    private function _isEmailRegistered()
    {
        $model = Mage::getModel('customer/customer');
        $model->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($this->email);

        if($model->getId() == NULL) {
            return false;
        }

        return true;
    }

    public function validateMinimumAmount()
    {
        return $this->getQuote()->validateMinimumAmount();
    }

    public function canCheckout()
    {
        if($this->getQuote()->getItemsSummaryQty() == 0)    {
            return false;
        }

        return true;
    }

    private function _saveOrder()
    {
        // Hack to fix weird Magento payment behaviour
        $payment = $this->getRequest()->getPost('payment', false);
        if($payment) {
            /**
             * A fix for common one big form problem
             * we rename the fields in template and iterate over subarrays
             * to see if there's any values and set them to main scope
             */
            foreach($payment as $value){
                if(is_array($value) && !empty($value)){
                    foreach($value as $key => $realValue){
                        if(!empty($realValue)){
                            $payment[$key]=$realValue;
                        }
                    }
                }
            }

            /**
             * unset unnecessary fields
             */
            foreach ($payment as $key => $value){
                if(is_array($value)){
                    unset($payment[$key]);
                }
            }
            $this->getOnepage()->getQuote()->getPayment()->importData($payment);
        }

        try {
            $order = $this->getOnepage()->saveOrder();
        } catch(Exception $e)   {
            //need to activate
            $this->getOnepage()->getQuote()->setIsActive(true);
            //need to recalculate
            $this->getOnepage()->getQuote()->getShippingAddress()->setCollectShippingRates(true)->collectTotals();
            $error = $e->getMessage();
            $this->formErrors['unknown_source_error'] = $error;
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $error);
            return;
            //die('Error: ' . $e->getMessage());
        }

        $this->afterPlaceOrder();

        $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();

        if($redirectUrl)    {
            $redirect = $redirectUrl;
        } else {
            $this->getOnepage()->getQuote()->setIsActive(false);
            $this->getOnepage()->getQuote()->save();
            $redirect = $this->getUrl('checkout/onepage/success');
            //$this->_redirect('checkout/onepage/success', array('_secure'=>true));
        }

        Header('Location: ' . $redirect);
        exit();
    }

    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    public function isUseBillingAddressForShipping()
    {
        if (($this->getQuote()->getIsVirtual())
        || !$this->getQuote()->getShippingAddress()->getSameAsBilling()) {
            return false;
        }
        return true;
    }

    public function getCountries()
    {
        return Mage::getResourceModel('directory/country_collection')->loadByStore();
    }

    public function getMethod()
    {
        return $this->getQuote()->getCheckoutMethod();
    }

    function isAddressEmpty($address)
    {



    }

    function getAddress() {
        if (!$this->isCustomerLoggedIn()) {
            return $this->getQuote()->getBillingAddress();
        } else {
            return $this->getQuote()->getBillingAddress();

            //return Mage::getModel('sales/quote_address');
            /*
            $address = $this->getQuote()->getCustomer()->getPrimaryBillingAddress();
            if($address == null)    {
            return Mage::getModel('sales/quote_address');
            }
            else    {
            return $address;
            }*/
        }
    }

    public function getFirstname()
    {
        $firstname = $this->getAddress()->getFirstname();
        if (empty($firstname) && $this->getQuote()->getCustomer()) {
            return $this->getQuote()->getCustomer()->getFirstname();
        }
        return $firstname;
    }

    public function getLastname()
    {
        $lastname = $this->getAddress()->getLastname();
        if (empty($lastname) && $this->getQuote()->getCustomer()) {
            return $this->getQuote()->getCustomer()->getLastname();
        }
        return $lastname;
    }

    public function getTelephone()
    {
        $telephone = $this->getAddress()->getTelephone();
        $primary = $this->getQuote()->getCustomer()->getPrimaryBillingAddress();

        if(empty($telephone) && $this->_isLoggedIn() && $primary)   {
            return $this->getQuote()->getCustomer()->getPrimaryBillingAddress()->getTelephone();
        }
        return $telephone;
    }

    public function getCity()
    {
        $city = $this->getAddress()->getCity();
        $primary = $this->getQuote()->getCustomer()->getPrimaryBillingAddress();

        if(empty($city)  && $this->_isLoggedIn() && $primary)   {
            return $this->getQuote()->getCustomer()->getPrimaryBillingAddress()->getCity();
        }
        return $city;
    }

    public function getStreet($i)
    {
        $street = $this->getQuote()->getBillingAddress()->getStreet($i);
        $primary = $this->getQuote()->getCustomer()->getPrimaryBillingAddress();

        if(empty($street) &&  $this->_isLoggedIn() && $primary) {
            return $this->getQuote()->getCustomer()->getPrimaryBillingAddress()->getStreet($i);
        }
        return $street;
    }

    public function postcodeFromCustomer()
    {
        $postcode = $this->getAddress()->getPostcode();
        $primary = $this->getQuote()->getCustomer()->getPrimaryBillingAddress();

        if(empty($postcode) && $this->_isLoggedIn() && $primary)    {
            return true;
        }

        return false;
    }

    public function getPostcode()
    {
        $postcode = $this->getAddress()->getPostcode();
        $primary = $this->getQuote()->getCustomer()->getPrimaryBillingAddress();

        if(empty($postcode) && $this->_isLoggedIn() && $primary)    {
            return $this->getQuote()->getCustomer()->getPrimaryBillingAddress()->getPostcode();
        }
        return $postcode;
    }

    public function getRegion()
    {
        $region = $this->getAddress()->getRegion();
        $primary = $this->getQuote()->getCustomer()->getPrimaryBillingAddress();

        if(empty($region) && $this->_isLoggedIn() && $primary)  {
            return $this->getQuote()->getCustomer()->getPrimaryBillingAddress()->getRegion();
        }
        return $region;
    }

    public function getRegionId()
    {
        $region = $this->getAddress()->getRegionId();
        $primary = $this->getQuote()->getCustomer()->getPrimaryBillingAddress();

        if(empty($region) && $this->_isLoggedIn() && $primary)  {
            return $this->getQuote()->getCustomer()->getPrimaryBillingAddress()->getRegionId();
        }
        return $region;
    }

    public function getCompany()
    {
        $company = $this->getAddress()->getCompany();
        $primary = $this->getQuote()->getCustomer()->getPrimaryBillingAddress();

        if(empty($company) && $this->_isLoggedIn() && $primary) {
            return $this->getQuote()->getCustomer()->getPrimaryBillingAddress()->getCompany();
        }
        return $company;
    }

    public function getFax()
    {
        $fax = $this->getAddress()->getFax();
        $primary = $this->getQuote()->getCustomer()->getPrimaryBillingAddress();

        if(empty($fax)  && $this->_isLoggedIn() && $primary)    {
            return $this->getQuote()->getCustomer()->getPrimaryBillingAddress()->getFax();
        }
        return $fax;
    }

    public function getShippingFirstname()
    {
        $firstname = $this->getQuote()->getShippingAddress()->getFirstname();
        if (empty($firstname) && $this->getQuote()->getCustomer()) {
            return $this->getQuote()->getCustomer()->getFirstname();
        }
        return $firstname;
    }

    public function getShippingLastname()
    {
        $lastname = $this->getQuote()->getShippingAddress()->getLastname();
        if (empty($lastname) && $this->getQuote()->getCustomer()) {
            return $this->getQuote()->getCustomer()->getLastname();
        }
        return $lastname;
    }

    public function getShippingTelephone()
    {
        $telephone = $this->getQuote()->getShippingAddress()->getTelephone();
        $primary = $this->getQuote()->getCustomer()->getPrimaryShippingAddress();

        if(empty($telephone) && $this->_isLoggedIn() && $primary)   {
            return $this->getQuote()->getCustomer()->getPrimaryShippingAddress()->getTelephone();
        }
        return $telephone;
    }

    public function getShippingCity()
    {
        $primary = $this->getQuote()->getCustomer()->getPrimaryShippingAddress();
        $city = $this->getQuote()->getShippingAddress()->getCity();

        if(empty($city)  && $this->_isLoggedIn() && $primary)   {
            return $this->getQuote()->getCustomer()->getPrimaryShippingAddress()->getCity();
        }
        return $city;
    }

    public function getShippingStreet($i)
    {
        $street = $this->getQuote()->getShippingAddress()->getStreet($i);
        $primary = $this->getQuote()->getCustomer()->getPrimaryShippingAddress();

        if(empty($street) && $this->_isLoggedIn() && $primary)  {
            return $primary->getStreet($i);
        }
        return $street;
    }

    public function getShippingPostcode()
    {
        $postcode = $this->getQuote()->getShippingAddress()->getPostcode();
        $primary = $this->getQuote()->getCustomer()->getPrimaryShippingAddress();

        if(empty($postcode) && $this->_isLoggedIn() && $primary)    {
            return $this->getQuote()->getCustomer()->getPrimaryShippingAddress()->getPostcode();
        }
        return $postcode;
    }

    public function getShippingRegion()
    {
        $region = $this->getQuote()->getShippingAddress()->getRegion();
        $primary = $this->getQuote()->getCustomer()->getPrimaryShippingAddress();

        if(empty($region) && $this->_isLoggedIn() && $primary)  {
            return $this->getQuote()->getCustomer()->getPrimaryShippingAddress()->getRegion();
        }
        return $region;
    }

    public function getShippingRegionId()
    {
        $region = $this->getQuote()->getShippingAddress()->getRegionId();
        $primary = $this->getQuote()->getCustomer()->getPrimaryShippingAddress();

        if(empty($region) && $this->_isLoggedIn() && $primary)  {
            return $this->getQuote()->getCustomer()->getPrimaryShippingAddress()->getRegionId();
        }
        return $region;
    }

    public function getShippingCompany()
    {
        $company = $this->getQuote()->getShippingAddress()->getCompany();
        $primary = $this->getQuote()->getCustomer()->getPrimaryShippingAddress();

        if(empty($company) && $this->_isLoggedIn() && $primary) {
            return $this->getQuote()->getCustomer()->getPrimaryShippingAddress()->getCompany();
        }
        return $company;
    }

    public function getShippingFax()
    {
        $fax = $this->getQuote()->getShippingAddress()->getFax();
        $primary = $this->getQuote()->getCustomer()->getPrimaryShippingAddress();

        if(empty($fax)  && $this->_isLoggedIn() && $primary)    {
            return $this->getQuote()->getCustomer()->getPrimaryShippingAddress()->getFax();
        }
        return $fax;
    }

    public function canShip()
    {
        return !$this->getQuote()->isVirtual();
    }

    public function getCountryHtmlSelect($type)
    {
        if($type == 'billing')  {
            $address = $this->getQuote()->getBillingAddress();
            /*
             $address = $this->getQuote()->getCustomer()->getPrimaryBillingAddress();
             if (!$this->isCustomerLoggedIn() || $address == null)
             $address = $this->getQuote()->getBillingAddress();
             */

        }
        else    {
            $address = $this->getQuote()->getShippingAddress();

            /*
             $address = $this->getQuote()->getCustomer()->getPrimaryShippingAddress();
             if (!$this->isCustomerLoggedIn() || $address == null)
             $address = $this->getQuote()->getShippingAddress();
             */
        }

        $countryId = $address->getCountryId();
        if (is_null($countryId)) {
            $countryId = Mage::getStoreConfig('general/country/default');
        }
        $select = $this->getLayout()->createBlock('core/html_select')
        ->setName($type.'[country_id]')
        ->setId($type.':country_id')
        ->setTitle(Mage::helper('checkout')->__('Country'))
        ->setClass('validate-select')
        ->setValue($countryId)
        ->setOptions($this->getCountryOptions());
        if ($type === 'shipping') {
            $select->setExtraParams('onchange="shipping.setSameAsBilling(false);"');
        }

        return $select->getHtml();
    }

    public function getBillingFieldsOrder($fields = array()){

        $fieldsAvailable = array(
            'name' => array('fields' => array('firstname','lastname')),
            'email-phone' => array('fields' =>array('email','telephone')),
            'street' => array(),
            'country_id' => array(),
            'postcode-regionid' => array('fields' =>array('postcode','region_id')),
            'city' => array(),
            'company-fax' => array('fields' => array('company','fax')),
            'taxvat' => array(),
            'dob' => array(),
            'gender' => array(),
            'create_account' => array(),
            'password' => array('has_li' => 1, 'fields' => array('password','confirm_password')),
            'save_in_address_book' => array('has_li' => 1),
            'use_for_shipping_yes' => array(),
        );
        $settings = $this->settings['sortordering_fields'];
        $tmp = array();
        foreach ($fieldsAvailable as $key => $value){
            if(empty($value['fields'])){
                if(!empty($settings[$key]) && !empty($fields[$key]) ){
                    $tmp[$settings[$key]]['fields'][] = $fields[$key];
                    if(!empty($value['has_li'])){
                        $tmp[$settings[$key]]['has_li']=1;
                    }
                }
            } else {
                foreach($value['fields'] as $subfield){
                    if(!empty($settings[$subfield]) && !empty($fields[$subfield]) ){
                        if(empty($placeholder)){
                            $placeholder = $settings[$subfield];
                        }
                        $tmp[$placeholder]['fields'][$settings[$subfield]] = $fields[$subfield];
                    }
                }
                if(!empty($value['has_li']) && !empty($placeholder)){
                        $tmp[$placeholder]['has_li']=1;
                }
                if(!empty($placeholder)){
                    ksort($tmp[$placeholder]['fields']);
                    unset($placeholder);
                }

            }
        }
        ksort($tmp);
        $fields = $tmp ;

        return $fields;
    }

    public function getShippingFieldsOrder($fields = array()){
        $fieldsAvailable = array(
            'name' => array('fields' => array('firstname','lastname')),
            'telephone' => array(),
            'street' => array(),
            'country_id' => array(),
            'postcode-regionid' => array('fields' =>array('postcode','region_id')),
            'city' => array(),
            'company-fax' => array('fields' => array('company','fax')),
            'save_in_address_book' => array('has_li' => 1),
        );
        $settings = $this->settings['sortordering_fields'];
        $tmp = array();
        foreach ($fieldsAvailable as $key => $value){
            if(empty($value['fields'])){
                if(!empty($settings[$key]) && !empty($fields[$key]) ){
                    $tmp[$settings[$key]]['fields'][] = $fields[$key];
                    if(!empty($value['has_li'])){
                        $tmp[$settings[$key]]['has_li']=1;
                    }
                }
            } else {
                foreach($value['fields'] as $subfield){
                    if(!empty($settings[$subfield]) && !empty($fields[$subfield]) ){
                        if(empty($placeholder)){
                            $placeholder = $settings[$subfield];
                        }
                        $tmp[$placeholder]['fields'][$settings[$subfield]] = $fields[$subfield];
                    }
                }
                if(!empty($value['has_li'])){
                        $tmp[$placeholder]['has_li']=1;
                }
                if(!empty($placeholder)){
                    ksort($tmp[$placeholder]['fields']);
                    unset($placeholder);
                }
            }
        }
        ksort($tmp);
        $fields = $tmp ;

        return $fields;
    }
}
