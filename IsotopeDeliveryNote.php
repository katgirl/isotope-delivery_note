<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Kirsten Roschanski &#40;2012&#41; 
 * @author     Kirsten Roschanski 
 * @package    Isotope
 * @license    LGPL 
 * @filesource
 */
 
class IsotopeDeliveryNote extends IsotopeProductCollection
{

	/**
	 * Name of the current table
	 * @var string
	 */
	protected $strTable = 'tl_iso_orders';

	/**
	 * Name of the child table
	 * @var string
	 */
	protected $ctable = 'tl_iso_order_items';

	/**
	 * This current order's unique ID with eventual prefix
	 * @param string
	 */
	protected $strOrderId = '';

	/**
	 * Template
	 * @var string
	 */
  protected $strTemplate = 'iso_delivery_note';

	/**
	 * Return a value
	 * @param string
	 * @return mixed
	 */
	public function __get($strKey)
	{
		switch ($strKey)
		{
			case 'order_id':
				return $this->strOrderId;

			case 'billingAddress':
				return deserialize($this->arrData['billing_address'], true);

			case 'shippingAddress':
				return deserialize($this->arrData['shipping_address'], true);
			
			case 'paid':
				return (((int) $this->date_paid) >= time() && $this->status == 'complete');

			default:
				return parent::__get($strKey);
		}
	}

	/**
	 * Find a record by its reference field and return true if it has been found
	 * @param string
	 * @param mixed
	 * @return boolean
	 */
	public function findBy($strRefField, $varRefId)
	{
		if (parent::findBy($strRefField, $varRefId))
		{
			$this->Shipping = null;
			$this->Payment = null;
			$this->strTitle = $GLOBALS['TL_LANG']['MSC']['iso_delivery_note_title'];

			$objPayment = $this->Database->execute("SELECT * FROM tl_iso_payment_modules WHERE id=" . $this->payment_id);

			if ($objPayment->numRows)
			{
				$strClass = $GLOBALS['ISO_PAY'][$objPayment->type];

				try
				{
					$this->Payment = new $strClass($objPayment->row());
				}
				catch (Exception $e) {}
			}

			if ($this->shipping_id > 0)
			{
				$objShipping = $this->Database->execute("SELECT * FROM tl_iso_shipping_modules WHERE id=" . $this->shipping_id);

				if ($objShipping->numRows)
				{
					$strClass = $GLOBALS['ISO_SHIP'][$objShipping->type];

					try
					{
						$this->Shipping = new $strClass($objShipping->row());
					}
					catch (Exception $e) {}
				}
			}

			// The order_id must not be stored in arrData, or it would overwrite the database on save().
			$this->strOrderId = $this->arrData['order_id'];
			unset($this->arrData['order_id']);

			return true;
		}

		return false;
	}

	/**
	 * Return current surcharges as array
	 * @return array
	 */
	public function getSurcharges()
	{
		$arrSurcharges = deserialize($this->arrData['surcharges']);
		return is_array($arrSurcharges) ? $arrSurcharges : array();
	}

	/**
	 * Inject transform date
	 * @param int, string
	 * @return string
	 */	
	public function generateCollection($objTemplate, $objProductCollection)
	{		
	
	  $arrProducts = $objProductCollection->getProducts();
	  
	  foreach ($arrProducts as $objProduct)
		{
      if (strlen($this->Input->post('depot', true)) && $this->Input->post('depot', true) != $objProduct->depot)
      { 
          next;       
      }
      else
      {		
			  $arrItems[] = array
			  (
				  'raw'				        => $objProduct->getData(),
				  'product_options' 	=> $objProduct->getOptions(),
				  'name'				      => $objProduct->name,			
				  'depot'				      => $objProduct->depot,
				  'quantity'			    => $objProduct->quantity_requested,
				  'price'				      => $objProduct->formatted_price,
				  'total'				      => $objProduct->formatted_total_price,
				  'tax_id'			      => $objProduct->tax_id,
			  );
			}
		}
		
		$objTemplate->items = $arrItems;
	  $objTemplate->orderID = $objProductCollection->order_id;
	}
}

