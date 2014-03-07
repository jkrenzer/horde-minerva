<?php
/**
 * Rude example how to create and send an invoice using API calls
 * outisde, in a non horde application. Meant for creating invoices
 * from other application.
 *
 * NOTE: It calls save/send directy without any horde stuff like 
 *       permission checking or whatever
 *
 * Usage:
 *
 *  1. Prepare the needed variables:
 *      - $customer_email email to send the email to
 *      - $draft_id       id of Minerva mail draft to use
 *      - $invoice        invoice data, below are the minimal required
 *      - $charset        (optional) charset of input data
 *  2. Include this file in your program, it will return the invoice id
 *     or pear error if someting goes wrong.
 *      $result = require('external.php');
 *
 * $Horde: incubator/minerva/scripts/external.php,v 1.13 2009/07/09 08:18:15 slusarz Exp $
 *
 * Copyright 2006-2007 Duck <duck@obala.net>
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */

// define paths
define('AUTH_HANDLER', true);
define('MINERVA_BASE', dirname(__FILE__) . '/..');

$no_compress = true;
require_once MINERVA_BASE . '/lib/base.php';
require_once MINERVA_BASE . '/lib/api.php';

// Where to send email
if (!isset($customer_email)) {
    $customer_email = Minerva::getFromAddress();
}

// Which draft to use
if (!isset($draft_id)) {
    $draft_id = 1;
}

// Invoice data example
if (!isset($invoice)) {
    $invoice = array();
    $invoice['invoice'] = array('expire' => 8,
                                'place' => 'My city',
                                'service' => date('Y-m-d'));
    $invoice['client'] = array('name' => 'Customer Joe',
                               'address' => 'My village 8');
    $invoice['articles'][] = array('name' => 'Test article',
                                   'price' => 100.10,
                                   'tax' => 18);
}

// Convert charset from your api to horde's
if (isset($charset)) {
    if (is_array($charset)) {
        $c_from = $charset[0];
        $c_to   = $charset[1];
    } else {
        $c_from = $charset;
        $c_to   = Horde_Nls::getCharset();
    }
    $invoice = Horde_String::convertCharset($invoice, $c_from, $c_to);
}

// Save invoice and get the id
$invoice_id = _minerva_save($invoice);
if ($invoice_id instanceof PEAR_Error) {
    return $invoice_id;
}

// Send invoice my mail
$draft = _minerva_getDraft($draft_id);
$send = _minerva_sendInvoice($invoice_id, $customer_email, $draft['subject'], $draft['body']);
if ($send instanceof PEAR_Error) {
    return $send;
}

// return invoice id
return $invoice_id;
