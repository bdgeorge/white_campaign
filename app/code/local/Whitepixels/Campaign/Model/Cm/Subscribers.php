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

class Whitepixels_Campaign_Model_Cm_Subscribers extends Whitepixels_Campaign_Model_Cm_Abstract 
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
	}
	
	/**
	 * Set the List Id to use for subscribers. 
	 * @param string $listId
	 * @return 
	 */
	public function setListId($listId)
	{
		//This can't be in the abstract class as the base_route differs for each child object.
		$this->setData('list_id', $listId);
		$this->_subscribers_base_route = self::BASE_ROUTE . 'subscribers/' .$this->getListId();		
	}	
	
	/**
	 * Unsubscribe an email address from the list
	 * 
	 * @param string $email
	 * @return boolean TRUE on success
	 */
	public function unsubscribe($email)
	{
		$route = $this->_subscribers_base_route . '/unsubscribe' . "." . self::PROTOCOL;
		
        // We need to build the subscriber data structure.
        $data = array(
		    'EmailAddress' => $email 
        );
		$result = $this->postRequest($route, $data);
		return is_null($result) ? TRUE:FALSE;
		
	}
	
	/**
	 * Add a new subscriber to the list
	 * 
	 * @param string $email
	 * @param string $name [optional]
	 * @param array $customFields [optional] Key/Value coded array of custom fields that this list supports. 
	 * 				These custom fields must already exist in the CM list. 
	 * 				The Keys need to be the CM 'Personalisation tag', not the field name. 
	 * 				Unknown fields are simply ignored.
	 * @return string email address of subscriber just added or FALSE
	 */
	public function subscribe($email, $name = '', $customFields = '', $resubscribe = FALSE)
	{
		$route = $this->_subscribers_base_route . "." . self::PROTOCOL;
		
        // We need to build the subscriber data structure.
        $data = array(
		    'EmailAddress' => $email,
			'Name' => $name, 
			'Resubscribe' => $resubscribe,
        );
		if(is_array($customFields)){
			//Build array of custom fields and append to data array
			//TODO: If we can work out the CM rules for field names we could validate the keys and log any key violations
			foreach($customFields as $key => $value){			
				$tmp[] = array('Key'=>$key, 'Value'=>$value);
			}
			$data['CustomFields'] = $tmp;
		} elseif($customFields instanceof Varian_Object) {
			//TODO: Support _data contents from Varian_Object
		}
		return $this->postRequest($route,$data);
	}
	
	/**
	 * Imports an array or collection of customers into the list
	 * 
	 * @param array $subscribers or Mage_Customer_Model_Entity_Customer_Collection
     *     array (
     *         array (
     *             'EmailAddress' => The new subscribers email address
     *             'Name' => The name of the new subscriber
     *             'CustomFields' => array(
     *                 array(
     *                     'Key' => The custom fields personalisation tag
     *                     'Value' => The value for this subscriber
     *                 )
     *             )
     *         )
     *     )	 * 
	 * @param object $resubscribe
	 * @return 
	 */
	public function import($subscribers, $resubscribe)
	{
		$route = $this->_subscribers_base_route . "/import." . self::PROTOCOL;
		$mageSubscribers = array();			
		
		if($subscribers instanceof Mage_Customer_Model_Entity_Customer_Collection){
			//If we have a Mage Collection loop through it building the correct array structure
			
			//Get the hash of groups and websites
	        $groups = Mage::getResourceModel('customer/group_collection')
	            ->addFieldToFilter('customer_group_id', array('gt'=> 0))
	            ->load()
	            ->toOptionHash();
			$websites = Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(true);				
		
			foreach($subscribers as $customer){				
				$customFields = $customer->getData();

				//Remove the email address and Name from the custom fields structure
				unset($customFields['name']);
				unset($customFields['email']);
				
				//Add the group and website name to the array
				$customFields['group'] = $groups[$customer->getGroupId()];
				$customFields['website'] = $websites[$customer->getWebsiteId()];				
				
				$mageSubscribers[] = array(
										'EmailAddress'=>$customer->getEmail(),
										'Name'=>$customer->getName(),
										'CustomFields'=>$this->_prepareCustomFields($customFields)
									); 
			}
		} elseif(is_array($subscribers)) {
			//Shouldn't have to do anything really
			//TODO: Test this
			$mageSubscribers = $subcribers;
		} else {
			Mage::log("Import failed. Subscribers should be an array or customer collection", Zend_Log::ERR, 'WhiteCampaign.log');
			return FALSE;
		}
        $data = array(
		    'Subscribers' => $mageSubscribers,
		    'Resubscribe' => $resubscribe,			
        );		
		return $this->postRequest($route, $data);
	}
	
	/**
	 * Get subscriber details. Returns an array of details including any custom fields or false.
	 * 
	 * @param string $email
	 * @return array
     * {
     *     'EmailAddress' => The subscriber email address
     *     'Name' => The subscribers name
     *     'Date' => The date the subscriber was added to the list
     *     'State' => The current state of the subscriber
     *     'CustomFields' => array(
     *         {
     *             'Key' => The custom fields personalisation tag
     *             'Value' => The custom field value for this subscriber
     *         }
     *     )
     * } 
	 */
	public function get($email)
	{
		$route = $this->_subscribers_base_route . "." . self::PROTOCOL;
		$query = array('email'=>$email);

		return $this->getRequest($route, $query);
	}
	
	
	/**
	 * Get the sending history for a specific subscriber history.
	 * @param object $email
	 * @return array
     * array(
     *     {
     *         ID => The id of the email which was sent
     *         Type => 'Campaign'
     *         Name => The name of the email
     *         Actions => array(
     *             {
     *                 Event => The type of action (Click, Open, Unsubscribe etc)
     *                 Date => The date the event occurred
     *                 IPAddress => The IP that the event originated from
     *                 Detail => Any available details about the event i.e the URL for clicks
     *             }
     *         )
     *     }
     * )
	 */
	public function getHistory($email)
	{
		$route = $this->_subscribers_base_route . "'/history." . self::PROTOCOL;
		$query = array('email'=>$email);
			
		return $this->getRequest($route, $query);			
		
	}
	
	/**
	 * Utility function to create properly formatted CustomFields array
	 * @param array $fields
	 * @return 
	 */
	private function _prepareCustomFields($fields)
	{
		foreach($fields as $key => $value){			
			$tmp[] = array('Key'=>$key, 'Value'=>$value);
		}	
		return $tmp;	
	}
}