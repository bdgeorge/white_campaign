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


class Whitepixels_Campaign_Model_Order_Observer {
	
	public function addSubscriber(Varien_Event_Observer $observer)
	{
		/**
		 * If we have an order for an anonymous user then add them to the appropriate list
		 */
		
		/**
		 * @var Mage_Sales_Model_Order
		 */
	    $order = $observer->getEvent()->getOrder();	

			
		
		if($order->getCustomerIsGuest()){
			//If we are coming from the admin we have to be careful about what store scope we use
			//during construction of the subscriber model
			$storeId = $order->getStoreId();
			$list = Mage::getStoreConfig('whitepixels_campaigns/campaignmonitor/guest_list_id', $storeId);
			
			/**
			 * We must provide a list ID here as the default list ID is for the customer subscribers
			 * 
			 * @var Whitepixels_Campaign_Model_Subscriber
			 */		
			$subscriber = Mage::getModel('whitecampaign/cm_subscribers', array('store_id' => $storeId, 'list_id' => $list));
			
			$email = $order->getCustomerEmail();
			$name = $order->getCustomerName();

			try{
				if($subscriber->subscribe($email,$name)){
					Mage::log('Subscribed guest: ' . $email, Zend_Log::DEBUG, 'WhiteCampaign.log');
				} else {
					Mage::log('Failed to subscribe guest: ' . $email, Zend_Log::DEBUG, 'WhiteCampaign.log');
				}			
			} catch (Exception $e) {
	                Mage::log("Error subscribing guest ".$e->getMessage(), Zend_Log::ERR, 'WhiteCampaign.log');
	                return;			
			}						
		}		
				
	}
}