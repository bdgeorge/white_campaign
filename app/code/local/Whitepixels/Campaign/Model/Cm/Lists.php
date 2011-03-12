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
		$this->_list_base_route = self::BASE_ROUTE . 'lists/' .$this->getListId();		
	}		
	
	/**
	 * Get List details, if no List ID is supplied it will return the list associated with the current store
	 * 
	 * @return array
     * {
     *     'ListID' => The id of the list
     *     'Title' => The title of the list
     *     'UnsubscribePage' => The page which subscribers are redirected to upon unsubscribing
     *     'ConfirmedOptIn' => Whether the list is Double-Opt In
     *     'ConfirmationSuccessPage' => The page which subscribers are
     *         redirected to upon confirming their subscription
     * } 
	 */
	public function get()
	{
		$route = $this->_list_base_route . "." . self::PROTOCOL;		
		return $this->getRequest($route);	
		
	}
    /**
     * Gets statistics for list subscriptions, deletions, bounces and unsubscriptions
     * 
	 * @return array
     * {
     *     'TotalActiveSubscribers'
     *     'NewActiveSubscribersToday'
     *     'NewActiveSubscribersYesterday'
     *     'NewActiveSubscribersThisWeek'
     *     'NewActiveSubscribersThisMonth'
     *     'NewActiveSubscribersThisYeay'
     *     'TotalUnsubscribes'
     *     'UnsubscribesToday'
     *     'UnsubscribesYesterday'
     *     'UnsubscribesThisWeek'
     *     'UnsubscribesThisMonth'
     *     'UnsubscribesThisYear'
     *     'TotalDeleted'
     *     'DeletedToday'
     *     'DeletedYesterday'
     *     'DeletedThisWeek'
     *     'DeletedThisMonth'
     *     'DeletedThisYear'
     *     'TotalBounces'
     *     'BouncesToday'
     *     'BouncesYesterday'
     *     'BouncesThisWeek'
     *     'BouncesThisMonth'
     *     'BouncesThisYear'
     * }
     */ 	
	public function getStats()
	{
		$route = $this->_list_base_route . "/stats." . self::PROTOCOL;
		return $this->getRequest($route);
		
	}	

	/**
     * Gets a list of all custom fields defined for the current list
     * 
	 * @return 
     * array(
     *     {
     *         'FieldName' => The name of the custom field
     *         'Key' => The personalisation tag of the custom field
     *         'DataType' => The data type of the custom field
     *         'FieldOptions' => Valid options for a multi-optioned custom field
     *     }
     * )
	 */	
	public function getCustomFields()
	{
		$route = $this->_list_base_route . "/customfields." .self::PROTOCOL;
		return $this->getRequest($route);
	}
	
    /**
     * Gets a list of all segments defined for the current list
	 *
     * @return CS_REST_Wrapper_Result A successful response will be an object of the form
     * array(
     *     {
     *         'ListID' => The current list id
     *         'SegmentID' => The id of this segment
     *         'Title' => The title of this segment
     *     }
     * )
     */
    public function getSegments() {
		$route = $this->_list_base_route . "/segments." .self::PROTOCOL;
		return $this->getRequest($route);
    }	
}