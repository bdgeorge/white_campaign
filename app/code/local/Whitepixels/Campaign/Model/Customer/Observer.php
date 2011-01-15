<?php
/**
 * Whitepixels Campaign Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Whitepixels
 * @package    Whitepixels_Campaign
 * @author     Ben George
 * @copyright  Copyright (c) 2011 Whitepixels (http://www.whitepixels.com.au)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Observe the addition or deletion of customers from the system
 */

class Whitepixels_Campaign_Model_Customer_Observer {
	
	public function deleteCustomer($observer)
	{
	    $event = $observer->getEvent();
        $customer = $event->getCustomer();	
		
		$email = $customer->getEmail();

		//If we are coming from the admin we have to be careful about what store scope we use
		//during construction of the subscriber model
		$storeId = Mage::app()->getStore()->getWebsiteId();
		if($customer->getWebsiteId()){			
			$storeId = $customer->getWebsiteId();			
		};		
		
		/**
		 * @var Whitepixels_Campaign_Model_Subscriber
		 */
		$subscriber = Mage::getModel('whitecampaign/cm_subscribers', array('store_id' => $storeId));
				
		try{		
			if($subscriber->unsubscribe($email)){
				Mage::log('Deleted customer and unsubscribed: ' . $email, Zend_Log::DEBUG, 'WhiteCampaign.log');
				//Unsubscribed ok
			} else {
				Mage::log('Deleted customer but failed to unsubscribe: ' . $email, Zend_Log::DEBUG, 'WhiteCampaign.log');
			}
		} catch (Exception $e) {
                Mage::log("Error unsubscribing customer ".$e->getMessage(), Zend_Log::ERR, 'WhiteCampaign.log');
                return;			
		}			
	}
	
	/**
	 * Adds a customer 
	 * @return 
	 */
	public function addCustomer($observer)
	{
	    $event = $observer->getEvent();
        $customer = $event->getCustomer();	
		
        $name = $customer->getFirstname() . " " . $customer->getLastname();
        $email = $customer->getEmail();
		
		//If we are coming from the admin we have to be careful about what store scope we use
		//during construction of the subscriber model
		$storeId = Mage::app()->getStore()->getWebsiteId();
		if($customer->getWebsiteId()){			
			$storeId = $customer->getWebsiteId();			
		};

		/**
		 * @var Whitepixels_Campaign_Model_Subscriber
		 */		
		$subscriber = Mage::getModel('whitecampaign/cm_subscribers', array('store_id' => $storeId));
	
		try{
			if($subscriber->subscribe($email,$name)){
				Mage::log('Subscribed customer: ' . $email, Zend_Log::DEBUG, 'WhiteCampaign.log');
			} else {
				Mage::log('Failed to subscribe customer: ' . $email, Zend_Log::DEBUG, 'WhiteCampaign.log');
			}			
		} catch (Exception $e) {
                Mage::log("Error subscribing customer ".$e->getMessage(), Zend_Log::ERR, 'WhiteCampaign.log');
                return;			
		}
			
	}
	
	//TODO: Should new customer be a separate method to addCustomer?
	//By doing this we could check if email is already on the guest list and move it to customer list only
	//when a new account is created.
	
	public function newCustomer($observer)
	{
		
	}
}
?>