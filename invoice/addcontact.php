<?php
/**
 * Add contact from invoice to address book
 *
 * $Horde: incubator/minerva/invoice/addcontact.php,v 1.15 2009/09/15 15:10:58 duck Exp $
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
require_once MINERVA_BASE . '/config/clientmap.php';

$invoice_id = Horde_Util::getFormData('invoice_id');
$invoice = $minerva_invoices->getOne($invoice_id);
$invoice_url = Horde_Util::addParameter(Horde::applicationUrl('invoice/invoice.php'), 'invoice_id', $invoice_id, false);

if ($invoice instanceof PEAR_Error) {
    $notification->push($invoice);
    header('Location: ' . $invoice_url);
    exit;
}

// map back client attributes
$client = $invoice['client'];
foreach ($clientmap as $key => $value) {
    if (is_string($value)) {
        $client[$value] = $client[$key];
    }
}

// get client source
$source = $registry->call('clients/getClientSource');
if (!$source) {
    $notification->push(_("Client source is not defined"), 'horde.warning');
    header('Location: ' . $invoice_url);
    exit;
}

// import contact
$client_uid = $registry->call('contacts/import', array($client, 'array', $source));
if ($client_uid instanceof PEAR_Error) {
    $notification->push($client_uid);
    header('Location: ' . $invoice_url);
    exit;
}

// notify user
$result = $registry->call('contacts/sources');
$notification->push(sprintf(_("Client imported in %s"), $result[$source]), 'horde.success');

// get client object_id from client_uid
$search = $GLOBALS['registry']->call('clients/searchClients',
                                     array('addresses' => array($client_uid),
                                           'addressbooks' => array($source),
                                           'fields' => array($source => array('__uid'))));

if ($search instanceof PEAR_Error) {
    $notification->push($search);
    header('Location: ' . $invoice_url);
    exit;
}

// save client id in invoice data
$object_id = $search[$client_uid][0]['__key'];
$invoice['client']['id'] = $object_id;
$result = $minerva_invoices->save($invoice, $invoice_id);
if ($result instanceof PEAR_Error) {
    $notification->push($result);
    header('Location: ' . $invoice_url);
    exit;
}

// redirect to contact
$contact_link = $registry->link('contacts/show', array('key' => $object_id, 'uid' => $client_uid, 'source' => $source));
if ($contact_link instanceof PEAR_Error) {
    $notification->push($contact_link);
    header('Location: ' . $invoice_url);
    exit;
}

header('Location: ' . Horde::url($contact_link));
exit;
