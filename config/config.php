<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * PHP version 5
 * @copyright  Copyright (C) 2012-2013 Kirsten Roschanski
 * @author     Kirsten Roschanski <kat@kirsten-roschanski.de>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @package    IsotopeDeliveryNote 
 * @filesource https://github.com/katgirl/isotope-delivery_note
 */

/**
 * BE-Module
 */
$GLOBALS['BE_MOD']['isotope']['iso_orders']['print_delivery_note'] = array('DeliveryNoteBackend','printDeliveryNote');
$GLOBALS['BE_MOD']['isotope']['iso_orders']['print_delivery_notes'] = array('DeliveryNoteBackend','printDeliveryNotes');

/**
 * Hooks
 */
$GLOBALS['ISO_HOOKS']['generateCollection'][] = array('IsotopeDeliveryNote', 'getGenerateCollection');
