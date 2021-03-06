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
 * Based on the Create Send v3 API PHP wrapper but implemented in a more Zend/Magento way
 */

class Whitepixels_Campaign_Model_Cm_Abstract extends Varien_Object {
	
	const BASE_ROUTE = "http://api.createsend.com/api/v3/";
	const PROTOCOL = 'json'; //TODO: only supports JSON currently, refactor to allow xml in the response as well
	
	/**
	 * @var Zend_Http_Client
	 */
	protected $_transport;
	
	/**
	 * All campaign monitor API methods require this authentication.
	 * Use the same list for each CM Method - ie only one list per Magento store
	 * 
	 * Note: We Override the Varien _construct() method, not the PHP __construct()
	 */
	public function _construct()
	{
		/**
		 * During construction the store ID should to be passed in
		 * Whilst the store can be determined from Frontend actions it can't so readily be 
		 * determined for Admin area operations 
		 */
		if(is_null($this->getStoreId())){
			$this->setStoreId(Mage::app()->getStore()->getWebsiteId());
		}
		
		//We may have been passed a different API Key during construction
		if(!$this->getApiKey()){
			$key = Mage::getStoreConfig('whitepixels_campaigns/campaignmonitor/api_key', $this->getStoreId());
			if($key){
				$this->setData('api_key',$key);	
			} else {
				$this->setData('api_key','No_Api_Key_Found'); //TODO how else can we fail gracefully here?
				Mage::log("No API Key found", Zend_Log::ERR, 'WhiteCampaign.log');
			}
			
		}	
		
		//Prepare an authenticated client
		$client = new Zend_Http_Client();
		$client->setAuth($this->getApiKey(),'',Zend_Http_Client::AUTH_BASIC);
		
		//Setup our basic transport object
		$this->_transport = $client;

	}
	
	//TODO: Replace this with an explicit setApiKey() method rather than using the setApiKey() magic method
	/**
	 * Set the API Key and update the transport layer AUTH appropriately
	 * 
	 * @param object $newKey
	 * @return 
	 */
	public function setApiKey($apiKey)
	{
		$this->setData('api_key', $apiKey);
		$this->_transport->setAuth($this->getApiKey(),'',Zend_Http_Client::AUTH_BASIC);//TODO: Test this
	}

	/**
	 * Generic GET Request
	 * 
	 * @param object $route The URI string for the particular GET
	 * @param object $query [optional] Any Query string parameters as a string or array. 
	 * 						This method will URL encode the string if required
	 * @return Array/Boolean Array of data or False on failure
	 */	
	public function getRequest($route, $query="null")
	{
		$uri = Zend_Uri::factory($route);
		if(!is_null($query)){
			$uri->setQuery($query);			
		}
	
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
			Mage::log("GET failed, Code " . $result['Code'] . ": " . $result['Message'], Zend_Log::ERR, 'WhiteCampaign.log');
			return FALSE;
		}		
	}
	
	/**
	 * Generic POST Request
	 * 
	 * @param object $route The URI string for the particular POST
	 * @param object $data Array/Object to POST
	 * @return Array/Boolean Array of data or False on failure
	 */
	public function postRequest($route, $data)
	{
		$uri = Zend_Uri::factory($route);
		
		$this->_transport->setUri($uri);		
		$this->_transport->setMethod(Zend_Http_Client::POST);
		$this->_transport->setRawData(json_encode($data),'text/' . self::PROTOCOL);	

		/**
		 * @var Zend_Http_Reponse
		 */
		$response = $this->_transport->request();
		if($response->isSuccessful()){
			return Zend_Json::decode($response->getBody());	
		} else {				
			$result = Zend_Json::decode($response->getBody());		
			Mage::log("POST Failed, Code " . $result['Code'] . ": " . $result['Message'], Zend_Log::ERR, 'WhiteCampaign.log');
			return FALSE;
		}					
	}
}
?>