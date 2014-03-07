<?php
/**
 * Export Invoices list
 *
 * $Horde: incubator/minerva/list/clients.php,v 1.11 2009/11/09 19:58:37 duck Exp $
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

// Get invoices
$list = Minerva::getList();

// Prepare the send from
$title = _("Export clients list in a datafile");
$vars = Horde_Variables::getDefaultVariables();
$form = new Minerva_Form_Invoices($vars, $title, 'exportclients');
$form->setButtons($title, true);

// Fill from
$form->addVariable(_("Invoices"), 'invoices', 'tableset', true, false, false, array('values' => $list, 'headers' => Minerva_Invoices::getListHeaders()));
$form->addVariable(_("Charset"), 'charset', 'enum', false, false, false, array(Horde_Nls::$config['encodings'], true));
$form->addVariable(_("Format"), 'exportID', 'enum', true, false, false, array(array(Horde_Data::EXPORT_CSV => _("Comma separated values (CSV)"),
                                                                                    Horde_Data::EXPORT_TSV => _("Tab separated values (TSV)"))));

if ($form->validate()) {

    $form->getInfo(null, $info);

    if (empty($info['invoices'])) {
        $notification->push(_("There are no records representing selected criteria."), 'horde.warning');
        header('Location: ' . Horde::applicationUrl('list/clients.php'));
        exit;
    }

    $invoices = $minerva_invoices->getAll($info['invoices']);
    if ($invoices instanceof PEAR_Error) {
        $notification->push($invoices);
        header('Location: ' . Horde::applicationUrl('list/clients.php'));
        exit;
    }

    // Need to convert charset?
    if ($info['charset'] !== Horde_Nls::getCharset()) {
        $invoices = Horde_String::convertCharset($invoices, Horde_Nls::getCharset(), $info['charset']);
    }

    $data = array();
    foreach ($invoices as $invoice_id => $invoice) {
        $data[] = array_merge($invoice['client'], array('invoice_id' => $invoice_id,
                                                        'invoice_name' => $invoice['invoice']['name'],
                                                        'invoice_date' => $invoice['invoice']['date'],
                                                        'invoice_comment' => $invoice['invoice']['comment'],
                                                        'invoice_total' => Minerva::format_price($invoice['invoice']['total'])));
        unset($invoices[$invoice_id]);
    }

    switch (Horde_Util::getFormData('exportID')) {
    case Horde_Data::EXPORT_CSV:
        $csv = Horde_Data::singleton('csv');
        $csv->exportFile(_("invoices.csv"), $data, true);
        exit;

    case Horde_Data::EXPORT_TSV:
        $tsv = Horde_Data::singleton('tsv');
        $tsv->exportFile(_("invoices.tsv"), $data, true);
        exit;

    }

} elseif (!$form->isSubmitted()) {
    $vars->set('charset', strtoupper(Horde_Nls::getCharset()));
}

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render();
$form->renderActive(null, null, null, 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
