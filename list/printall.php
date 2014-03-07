<?php
/**
 * Print all invoceis at once
 *
 * $Horde: incubator/minerva/list/printall.php,v 1.30 2009/11/09 19:58:37 duck Exp $
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
$title = _("Print all invoices at once");
$vars = Horde_Variables::getDefaultVariables();
$form = new Minerva_Form_Invoices($vars, $title, 'printall');
$form->setButtons(array($title, _("Download")), true);

// Fill from
$form->addVariable(_("Invoices"), 'invoices', 'tableset', true, false, false, array($list, Minerva_Invoices::getListHeaders()));

if ($form->validate()) {

    $form->getInfo(null, $info);

    if (empty($info['invoices'])) {
        $notification->push(_("There are no records representing selected criteria."), 'horde.warning');
        header('Location: ' . Horde::applicationUrl('list/printall.php'));
        exit;
    }

    // Get invoces data
    $invoices = $minerva_invoices->getAll($info['invoices']);
    if ($invoices instanceof PEAR_Error) {
        $notification->push($invoices);
        header('Location: ' . Horde::applicationUrl('list/printall.php'));
        exit;
    }

    // Convert invocies
    $convert = Minerva_Convert::factory();
    $files = array();
    foreach ($invoices as $invoice_id => $invoice_data) {
        $files[$invoice_id] = $convert->convert($invoice_id, $invoice_data);
        if ($files[$invoice_id] instanceof PEAR_Error) {
            $notification->push($files[$invoice_id]->getMessage(), 'horde.warning');
            header('Location: ' . Horde::applicationUrl('list/list.php'));
            exit;
        }
    }

    if (Horde_Util::getFormData('submitbutton') == _("Download")) {
        $browser->downloadHeaders(date('Ymd') . '.html', 'text/html', false);
    }

    foreach ($files as $path) {
        readfile($path);
        echo '<p style="page-break-before: always"></p>'; // force page-brake with CSS
    }

    if (Horde_Util::getFormData('submitbutton') != _("Download")) {
        echo '<script type="text/javascript"> window.onload=window.print() </script>';
    }

    $minerva_invoices->log($info['invoices'], 'print');
    exit;

}

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render();
$form->renderActive(null, null, Horde::applicationUrl('list/printall.php'), 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
