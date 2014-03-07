<?php
/**
 * Export invoice data to XML
 *
 * $Horde: incubator/minerva/invoice/export.php,v 1.15 2009/09/15 15:10:58 duck Exp $
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

$invoice_id = (int)Horde_Util::getGet('invoice_id', 0);
$invoice = $minerva_invoices->getOne($invoice_id);
if ($invoice instanceof PEAR_Error) {
    $notification->push($invoice);
    header('Location: ' . Horde::applicationUrl('list/list.php'));
    exit;
}

if (($charset = Horde_Util::getGet('charset')) !== null) {
    $invoice = Horde_String::convertCharset($invoice, Horde_Nls::getCharset(), $charset);
}

$serializer = new XML_Serializer(array(XML_SERIALIZER_OPTION_INDENT => '    ',
                                       XML_SERIALIZER_OPTION_XML_DECL_ENABLED => true,
                                       XML_SERIALIZER_OPTION_XML_ENCODING => $charset,
                                       XML_SERIALIZER_OPTION_SCALAR_AS_ATTRIBUTES => false,
                                       XML_SERIALIZER_OPTION_MODE => XML_SERIALIZER_MODE_SIMPLEXML,
                                       XML_SERIALIZER_OPTION_ROOT_NAME => 'data',
                                       XML_SERIALIZER_OPTION_RETURN_RESULT => true,
                                       XML_SERIALIZER_OPTION_CDATA_SECTIONS => true,
                                       XML_SERIALIZER_OPTION_DEFAULT_TAG => 'data'));

$data = $serializer->serialize($invoice);
$browser->downloadHeaders(str_replace('/', '-', $invoice['invoice']['name']) . '.xml', 'text/xml', false, strlen($data));
echo $data;
exit;
