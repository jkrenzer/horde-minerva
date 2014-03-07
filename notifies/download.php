<?php
/**
 * Download all files in zipfile
 *
 * $Horde: incubator/minerva/notifies/download.php,v 1.14 2009/11/09 19:58:37 duck Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */

$no_compress = true;
require_once dirname(__FILE__) . '/../lib/base.php';
require_once MINERVA_BASE . '/notifies/tabs.php';

// Get selected invoices data
$criteria = Minerva::getCriteria();
$criteria['invoice']['type'] = 'notify';
$list = Minerva::getList($criteria);

// Form
$title = _("Download invoices in a zipfile");
$vars = Horde_Variables::getDefaultVariables();
$form = new Minerva_Form_Invoices($vars, $title, 'download');

// Fill from
$form->addVariable(_("Invoices"), 'invoices', 'tableset', true, false, false, array('values' => $list, 'headers' => Minerva_Invoices::getListHeaders()));

if ($form->validate()) {

    $form->getInfo(null, $info);

    if (empty($info['invoices'])) {
        $notification->push(_("There are no records representing selected criteria."), 'horde.warning');
        header('Location: ' . Horde::applicationUrl('notifies/download.php'));
        exit;
    }

    // Get invoces data
    $invoices = $notifies->getAll($info['invoices']);
    if ($invoices instanceof PEAR_Error) {
        $notification->push($invoices);
        header('Location: ' . Horde::applicationUrl('notifies/download.php'));
        exit;
    }

    // Convert invocies
    $convert = Minerva_Convert::factory('notify');
    $files = array();
    foreach ($invoices as $invoice_id => $invoice_data) {
        $files[$invoice_id] = $convert->convert($invoice_id, $invoice_data);
        if ($files[$invoice_id] instanceof PEAR_Error) {
            $notification->push($files[$invoice_id]);
            header('Location: ' . Horde::applicationUrl('notifies/list.php'));
            exit;
        }
    }

    // Prepare array
    $zipfiles = array();
    foreach ($files as $path) {
        $zipfiles[] = array('data' => file_get_contents($path),
                            'name' => basename($path));
    }

    // create and send zip
    $zip = Horde_Compress::factory('zip');
    $body = $zip->compress($zipfiles);

    $filename = date('Ymd') . '-' . count($zipfiles) . '.zip';
    $browser->downloadHeaders($filename, 'application/zip', true, strlen($body));
    echo $body;
    exit;
}

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render();
$form->renderActive(null, null, Horde::applicationUrl('notifies/download.php'), 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
