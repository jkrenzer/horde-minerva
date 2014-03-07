<?php
/**
 * Minerva Invoce
 *
 * $Horde: incubator/minerva/invoice/invoice.php,v 1.56 2009/12/01 12:52:46 jan Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */

require_once dirname(__FILE__) . '/../lib/base.php';

$result = Horde::loadConfiguration('mime_drivers.php', array('mime_drivers', 'mime_drivers_map'), 'horde');
extract($result);

require_once MINERVA_BASE . '/lib/UI/Clients.php';

// Set up variables
$imagedir = $registry->getImageDir('horde');

// Check if the invoice exists and load financial data
$invoice_id = (int)Horde_Util::getFormData('invoice_id', 0);
if ($invoice_id > 0) {

    $invoice = $minerva_invoices->getOne($invoice_id);
    if ($invoice instanceof PEAR_Error) {
        $notification->push($invoice);
        header('Location: ' . Horde::applicationUrl('list/list.php'));
        exit;
    }

    $type = $invoice['invoice']['type'];
    $title = Minerva::getTypeName($invoice['invoice']['type']);

    $taxes = $invoice['taxes'];
    foreach ($taxes as $tax => $value) {
        unset($taxes[$tax]['total']);
    }

    $currencies = $invoice['currencies'];
    foreach ($currencies as $currency => $currenly_info) {
        if ($currenly_info['exchange_rate'] == 1) {
            $default_currency = $currency;
        }
        unset($currencies[$currency]['total']);
    }

    // Check currency
    if (empty($default_currency)) {
        $invoice_id = 0;
        $default_currency = Minerva::getDefaultCurrency();
        $notification->push(_("Invoice has no default currency."), 'horde.error');
    }

} else {
    $type = Horde_Util::getFormData('type', 'invoice');
    $title = Minerva::getTypeName($type);
    $taxes = Minerva::getTaxes();
    $currencies = Minerva::getCurrencies();
    $default_currency = Minerva::getDefaultCurrency();
}

// Check statuses premission
if (!Minerva::hasStatusPermission(null, Horde_Perms::EDIT, $type)) {
    $notification->push(sprintf(_("You don't have permisson to access status %s."), 'any'), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('list/list.php'));
    exit;
}

// Check types premission
if (!Minerva::hasTypePermission($type, Horde_Perms::EDIT)) {
    $notification->push(sprintf(_("You don't have permisson to access type %s."), Minerva::getTypeName($type)), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('list/list.php'));
    exit;
}

// Check if the invoice is locked by another user, otherwise lock if for us
$user = $minerva_invoices->isLocked($invoice_id);
if ($user && $user != Horde_Auth::getAuth()) {
    $notification->push(sprintf(_("Invoice id %s is already being edited by user %s."), $invoice_id, $user), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('list/list.php'));
    exit;
} else {
    $minerva_invoices->setLocked($invoice_id);
}

// Load article units
$units = Minerva::getUnits();

Horde::addScriptFile('prototype.js', 'horde');
Horde::addScriptFile('popup.js', 'horde');
Horde::addScriptFile('stripe.js', 'horde');
Horde::addScriptFile('invoice.js', 'minerva');
Horde::addScriptFile('protomenu.js', 'minerva');

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';
require MINERVA_TEMPLATES . '/invoice/header.inc';
require MINERVA_TEMPLATES . '/invoice/invoice.inc';
require MINERVA_TEMPLATES . '/invoice/footer.inc';

// Load or clone?
if ($clone_id = Horde_Util::getGet('clone_id', $invoice_id)) {
    $clone_type = Horde_Util::getGet('clone_id', false) ? Minerva::CLONE_PARTIAL : Minerva::CLONE_NORMAL;
    require MINERVA_TEMPLATES . '/javascript/src/loadInvoice.js';
}

require $registry->get('templates', 'horde') . '/common-footer.inc';
