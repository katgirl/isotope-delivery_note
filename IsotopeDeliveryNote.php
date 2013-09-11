<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * PHP version 5
 * @copyright  Copyright (C) 2012-2013 Kirsten Roschanski
 * @author     Kirsten Roschanski <kat@kirsten-roschanski.de>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 
/**
 * Class IsotopeDeliveryNote
 *
 * @copyright  Copyright (C) 2012-2013 Kirsten Roschanski
 * @author     Kirsten Roschanski <kat@kirsten-roschanski.de>
 * @package    IsotopeDeliveryNote 
 * @filesource https://github.com/katgirl/isotope-delivery_note
 */ 
 
class IsotopeDeliveryNote extends IsotopeOrder
{
  
  /**
   * Template
   * @var string
   */
  protected $strTemplate = 'iso_delivery_note';
    
    
  /**
   * New parameter for TemplateObject
   */
  public function getGenerateCollection(&$objTemplate, $arrItems, IsotopeProductCollection $objProductCollection) 
  { 

    if ( ! preg_match("/iso_delivery_note/", $objTemplate->getName() ) )
    { 
      return;
    }
    
    $objTemplate->deliveryNoteTitle  = $GLOBALS['TL_LANG']['isoDeliveryNote']['iso_delivery_note_title']; 
    $objTemplate->orderIdLabel       = $GLOBALS['TL_LANG']['isoDeliveryNote']['orderIdLabel'];
    $objTemplate->orderId            = $objProductCollection->order_id;
    $objTemplate->orderDateLabel     = $GLOBALS['TL_LANG']['isoDeliveryNote']['orderDateLabel'];
    $objTemplate->orderDate          = date($GLOBALS['TL_CONFIG']['dateFormat'], $objProductCollection->date);
    $objTemplate->arrBillingAddress  = $objProductCollection->billing_address;
    $objTemplate->arrShippingAddress = $objProductCollection->shipping_address ? $objProductCollection->shipping_address : $objProductCollection->billing_address;
  }   
}

