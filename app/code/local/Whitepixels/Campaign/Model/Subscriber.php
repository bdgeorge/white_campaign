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

class Whitepixels_Campaign_Model_Subscriber extends Whitepixels_Campaign_Model_CM_Abstract 
{
	protected $_subscribers_base_route;
	
	public function _construct()
	{
		parent::_construct();
		
		//We need a List to work with, this can be set in the constructor or default to the configured list for this store
		if(!$this->getListId()){
			$list = Mage::getStoreConfig('whitepixels_campaigns/campaignmonitor/list_id', $this->getStoreId());
			if($list){
				$this->setListId($list);
			} else {
				$this->setListId('No_List_ID_Found'); //TODO how else can we fail gracefully here?
				Mage::log("No List ID Found", Zend_Log::ERR, 'WhiteCampaign.log');
			}
		}	
		
				
		$this->_subscribers_base_route = self::BASE_ROUTE . 'subscribers/' .$this->getListId();
	}
	
	/**
	 * Unsubscribe an email address from the list
	 * 
	 * @param string $email
	 * @return boolean
	 */
	public function unsubscribe($email)
	{
		$uri = $this->_subscribers_base_route . '/unsubscribe' . "." . self::PROTOCOL . '?pretty=true';
		$this->_transport->setUri($uri);
		
        // We need to build the subscriber data structure.
        $email = array(
		    'EmailAddress' => $email 
        );
		
		$this->_transport->setUri($uri);		
		$this->_transport->setMethod(Zend_Http_Client::POST);
		$this->_transport->setRawData(json_encode($email),'text/' . self::PROTOCOL);
		
		/**
		 * @var Zend_Http_Reponse
		 */
		$response = $this->_transport->request();
		if($response->isSuccessful()){
			return TRUE;
		} else {
			$result = Zend_Json::decode($response->getBody());		
			Mage::log("Failed to unsubscribe " . $email . ", Code " . $result['Code'] . ": " . $result['Message'], Zend_Log::ERR, 'WhiteCampaign.log');
			return FALSE;
		}	
		
	}
	
	/**
	 * Add a new subscriber to the list
	 * 
	 * @param string $email
	 * @param string $name [optional]
	 * @param array $customFields [optional][FUTURE] Key/Value coded array of custom fields that this list supports
	 * @return boolean
	 */
	public function subscribe($email, $name = '', $customFields = '', $resubscribe = FALSE)
	{
		$uri = Zend_Uri::factory($this->_subscribers_base_route . "." . self::PROTOCOL);
		
        // We need to build the subscriber data structure.
        $data = array(
		    'EmailAddress' => $email,
			'Name' => $name, 
			'Resubscribe' => $resubscribe,
        );
		if(is_array($customFields)){
			//TODO: build array of custom fields, check it for sanity and append to data array
		}
		
		$this->_transport->setUri($uri);		
		$this->_transport->setMethod(Zend_Http_Client::POST);
		$this->_transport->setRawData(json_encode($data),'text/' . self::PROTOCOL);
		
		/**
		 * @var Zend_Http_Reponse
		 */
		$response = $this->_transport->request();
		if($response->isSuccessful()){
			return TRUE;
		} else {
			$result = Zend_Json::decode($response->getBody());		
			Mage::log("Failed to add subscriber " . $email . ", Code " . $result['Code'] . ": " . $result['Message'], Zend_Log::ERR, 'WhiteCampaign.log');
			return FALSE;
		}			
	}
	
	/**
	 * Get subscriber details. Returns and array of details including any custom fields or false.
	 * 
	 * @param string $email
	 * @return array | FALSE 
	 */
	public function subscriberDetails($email)
	{
		Zend_Debug::dump($this->_subscribers_base_route . "." . self::PROTOCOL);
		$uri = Zend_Uri::factory($this->_subscribers_base_route . "." . self::PROTOCOL);
		$uri->setQuery('email=' . urlencode($email));
		$this->_transport->setUri($uri);		
		$this->_transport->setMethod(Zend_Http_Client::GET);
			
		/**
		 * @var Zend_Http_Reponse
		 */
		$response = $this->_transport->request();
		if($response->isSuccessful()){
			$result = Zend_Json::decode($response->getBody());	
			return $result;
		} else {
			$result = Zend_Json::decode($response->getBody());			
			Mage::log("Failed to retrieve subscriber " . $email . " details, Code " . $result['Code'] . ": " . $result['Message'], Zend_Log::ERR, 'WhiteCampaign.log');
			return FALSE;
		}					
	}
}