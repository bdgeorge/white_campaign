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

class Whitepixels_Campaign_Model_Cm_Lists extends Whitepixels_Campaign_Model_Cm_Abstract 
{
	protected $_list_base_route;
	
	public function _construct()
	{
		parent::_construct();
		$this->_list_base_route = self::BASE_ROUTE . 'lists/';
	}
	
	/**
	 * Get List details, if no List ID is supplied it will return the list associated with the current store
	 * 
	 * @param string $list_id [optional]
	 * @return array
	 */
	public function details($list_id = NULL)
	{
		if(is_null($list_id)){
			$list_id = Mage::getStoreConfig('whitepixels_campaigns/campaignmonitor/list_id', $this->getStoreId());
		}
		$uri = $this->_list_base_route . $list_id . "." . self::PROTOCOL;
		$this->_transport->setUri($uri);		
		$this->_transport->setMethod(Zend_Http_Client::GET);
		
		/**
		 * @var Zend_Http_Reponse
		 */
		$response = $this->_transport->request();
		if($response->isSuccessful()){
			return Zend_Json::decode($response->getBody());;
		} else {
			$result = Zend_Json::decode($response->getBody());		
			Mage::log("Failed to get list details for " . $list_id . ", Code " . $result['Code'] . ": " . $result['Message'], Zend_Log::ERR, 'WhiteCampaign.log');
			return FALSE;
		}	
		
	}
    /**
     * Gets statistics for list subscriptions, deletions, bounces and unsubscriptions
     * 
	 * @param string $list_id [optional]
	 * @return array
     */ 	
	public function stats($list_id = NULL)
	{
		if(is_null($list_id)){
			$list_id = Mage::getStoreConfig('whitepixels_campaigns/campaignmonitor/list_id', $this->getStoreId());
		}
		$uri = $this->_list_base_route . $list_id . "/stats." . self::PROTOCOL;
		$this->_transport->setUri($uri);				
		$this->_transport->setMethod(Zend_Http_Client::GET);
		
		/**
		 * @var Zend_Http_Reponse
		 */
		$response = $this->_transport->request();
		if($response->isSuccessful()){
			return Zend_Json::decode($response->getBody());
		} else {
			$result = Zend_Json::decode($response->getBody());					
			Mage::log("Failed to get list stats for " . $list_id . ", Code " . $result['Code'] . ": " . $result['Message'], Zend_Log::ERR, 'WhiteCampaign.log');
			return FALSE;
		}	
		
	}	
}