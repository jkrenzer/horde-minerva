<?php
/**
 * Export invoices in XML
 *
 * $Horde: incubator/minerva/list/export.php,v 1.29 2009/11/09 19:58:37 duck Exp $
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
require_once MINERVA_BASE . '/list/tabs.php';

// Get selected invoices data
$list = Minerva::getList();

// Prepare the send from
$title = _("Export invoices in XML");
$vars = Horde_Variables::getDefaultVariables();
$form = new Minerva_Form_Invoices($vars, $title, 'exporttoxml');
$form->setButtons($title, true);

// Fill from
$form->addVariable(_("Invoices"), 'invoices', 'tableset', true, false, false, array('values' => $list, 'headers' => Minerva_Invoices::getListHeaders()));
$form->addVariable(_("Charset"), 'charset', 'enum', false, false, false, array(Horde_Nls::$config['encodings'], true));

if ($form->validate()) {

    $form->getInfo($vars, $info);

    if (empty($info['invoices'])) {
        $notification->push(_("There are no records representing selected criteria."), 'horde.warning');
        header('Location: ' . Horde::applicationUrl('list/export.php'));
        exit;
    }

    $invoices = $minerva_invoices->getAll($info['invoices']);
    if ($invoices instanceof PEAR_Error) {
        $notification->push($invoices);
        header('Location: ' . Horde::applicationUrl('list/export.php'));
        exit;
    }

    if ($info['charset'] != strtoupper(Horde_Nls::getCharset())) {
        $invoices = Horde_String::convertCharset($invoices, Horde_Nls::getCharset(), $info['charset']);
    }

    $serializer = new XML_Serializer(array(XML_SERIALIZER_OPTION_INDENT => '    ',
                                           XML_SERIALIZER_OPTION_XML_DECL_ENABLED => true,
                                           XML_SERIALIZER_OPTION_XML_ENCODING => $info['charset'],
                                           XML_SERIALIZER_OPTION_SCALAR_AS_ATTRIBUTES => false,
                                           XML_SERIALIZER_OPTION_MODE => XML_SERIALIZER_MODE_SIMPLEXML,
                                           XML_SERIALIZER_OPTION_ROOT_NAME => 'invoices',
                                           XML_SERIALIZER_OPTION_RETURN_RESULT => true,
                                           XML_SERIALIZER_OPTION_CDATA_SECTIONS => true,
                                           XML_SERIALIZER_OPTION_DEFAULT_TAG => 'invoices'));

    $data = $serializer->serialize(array('invoice' => $invoices));
    $browser->downloadHeaders(date('Ymd') . '.xml', 'text/xml', false, strlen($data));
    echo $data;
    exit;

} elseif (!$form->isSubmitted()) {

    $vars->set('charset', strtoupper(Horde_Nls::getCharset()));

}

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render();
$form->renderActive(null, null, Horde::applicationUrl('list/export.php'), 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
