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
 * @copyright  Kirsten Roschanski (C) 2012 
 * @author     Kirsten Roschanski 
 * @package    Isotope 
 * @license    LGPL 
 * @filesource
 */

/**
 * Table tl_iso_orders
 */

$GLOBALS['TL_DCA']['tl_iso_orders']['list']['operations']['print_delivery_note'] = array
(
		'label'      => &$GLOBALS['TL_LANG']['tl_iso_orders']['print_delivery_note'],
		'href'       => 'key=print_delivery_note',
		'icon'       => 'system/modules/isotope/html/document-pdf-text.png'
);

$GLOBALS['TL_DCA']['tl_iso_orders']['list']['global_operations']['print_delivery_notes'] = array
(
		'label'      => &$GLOBALS['TL_LANG']['tl_iso_orders']['print_delivery_notes'],
		'href'       => 'key=print_delivery_notes',
		'class'      => 'header_print_invoices isotope-tools',
		'attributes' => 'onclick="Backend.getScrollOffset();"'
);

/**
 * Class tl_iso_orders
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class DeliveryNoteBackend extends tl_iso_orders
{

	/**
	 * Provide a select menu to choose orders by status and print PDF
	 */
	public function printDeliveryNotes()
	{
		$objOrders = $this->Database->prepare("SELECT id FROM tl_iso_orders WHERE status=?")->execute($this->Input->post('status'));

		if ($objOrders->numRows)
		{
			$this->generateDeliveryNote($objOrders->fetchEach('id'));
		}
		else
		{
			$strMessage = '<p class="tl_gerror">'.$GLOBALS['TL_LANG']['MSC']['noOrders'].'</p>';
		}

    $strMessage = '';

    $strReturn = '
<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=print_delivery_notes', '', $this->Environment->request)).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.$GLOBALS['TL_LANG']['tl_iso_orders']['print_delivery_notes'][0].'</h2>
<form action="'.$this->Environment->request.'"  id="tl_print_delivery_notes" class="tl_form" method="post">
<input type="hidden" name="FORM_SUBMIT" value="tl_print_delivery_notes">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">
<div class="tl_formbody_edit">
<div class="tl_tbox block">';

    $objWidget = new SelectMenu($this->prepareForWidget($GLOBALS['TL_DCA']['tl_iso_orders']['fields']['status'], 'status'));

    if ($this->Input->post('FORM_SUBMIT') == 'tl_print_delivery_notes')
    {
      $objOrders = $this->Database->prepare("SELECT id FROM tl_iso_orders WHERE status=?")->execute($this->Input->post('status'));

      if ($objOrders->numRows)
      {
        $this->generateDeliveryNote($objOrders->fetchEach('id'));
      }
      else
      {
        $strMessage = '<p class="tl_gerror">'.$GLOBALS['TL_LANG']['MSC']['noOrders'].'</p>';
      }
    }

    return $strReturn . $strMessage . $objWidget->parse() . '
</div>
</div>
<div class="tl_formbody_submit">
<div class="tl_submit_container">
<input type="submit" name="print_delivery_notes" id="ctrl_print_delivery_notes" value="'.$GLOBALS['TL_LANG']['MSC']['labelSubmit'].'">
</div>
</div>
</form>
</div>';
	}


	/**
	 * Print one order as PDF
	 * @param DataContainer
	 */
	public function printDeliveryNote(DataContainer $dc)
	{
      $this->generateDeliveryNote(array($dc->id));
	}


	/**
	 * Generate one or multiple PDFs by order ID
	 * @param array
	 * @return void
	 */
	public function generateDeliveryNote(array $arrIds)
	{
		$this->import('Isotope');

		if (!count($arrIds))
		{
			$this->log('No order IDs passed to method.', __METHOD__, TL_ERROR);
			$this->redirect($this->Environment->script . '?act=error');
		}

		$pdf = null;

		foreach ($arrIds as $intId)
		{
			$objOrder = new IsotopeDeliveryNote();

			if ($objOrder->findBy('id', $intId))
			{
				$pdf = $objOrder->generatePDF(null, $pdf, false);
			}
		}

		if (!$pdf)
		{
			$this->log('No order IDs passed to method.', __METHOD__, TL_ERROR);
			$this->redirect($this->Environment->script . '?act=error');
		}

		// Close and output PDF document
		$pdf->lastPage();

		// @todo make things like this configurable in a further version of Isotope
		$strDeliveryNoteTitle = 'delivery_note_' . $objOrder->order_id;
		$pdf->Output(standardize(ampersand($strDeliveryNoteTitle, false), true) . '.pdf', 'D');

		// Set config back to default
		// @todo do we need that? The PHP session is ended anyway...
		$this->Isotope->resetConfig(true);

		// Stop script execution
		exit;
	}
}
	
