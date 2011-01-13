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
 * Same as the standard Campaign Monitor result object, except typically
 * initialised after instantiation as it will be loaded by a Mage::helper call
 */
class Whitepixels_Campaign_Model_CS_Result {
	
    /**
     * The deserialised result of the API call
     * @var mixed
     */
	public $response;

    /**
     * The http status code of the API call
     * @var int
     */	
	public $http_status_code;
	
	public function _init($response, $code)
	{
        $this->response = $response;
        $this->http_status_code = $code;		
	}
	
    /**
     * Can be used to check if a call to the api resulted in a successful response.
     * @return boolean False if the call failed. Check the response property for the failure reason.
     * @access public
     */
    public function was_successful() {
        return $this->http_status_code >= 200 && $this->http_status_code < 300;
    }	
}
?>