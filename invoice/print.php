<?php
/**
 * Print invoice
 *
 * $Horde: incubator/minerva/invoice/print.php,v 1.25 2009/11/09 19:58:36 duck Exp $
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

$invoice_id = Horde_Util::getFormData('invoice_id');
$template = Horde_Util::getFormData('template');

// If no template is passed check for invoice type
if ($template === null) {
    $template = $minerva_invoices->getType($invoice_id);
    if ($template instanceof PEAR_Error) {
        die($template->getMessage());
    }
}

switch ($template) {

case 'notify':
    require_once MINERVA_BASE . '/lib/Notifies.php';
    $notifies = new Minerva_Notifies();
    $invoice = $notifies->getOne($invoice_id);
    break;

default:
    $template = 'invoice';
    $invoice = $minerva_invoices->getOne($invoice_id);
    break;

}

if ($invoice instanceof PEAR_Error) {
    die($invoice->getMessage());
}

// Try to call the driver
$convert = Minerva_Convert::factory($template);
if ($convert instanceof PEAR_Error) {
    die($convert->getMessage());
}

// Convert the document
$filename = $convert->convert($invoice_id, $invoice);
if ($filename instanceof PEAR_Error) {
    die($filename->getMessage());
}

// call the print on load and output the invoice
readfile($filename);

if (Horde_Util::getGet('noprint') === null) {
    echo '<script type="text/javascript"> window.onload=window.print() </script>';
}

$minerva_invoices->log($invoice_id, 'print');
