<?php
/**
 * Send custom text to clients
 *
 * $Horde: incubator/minerva/list/custom.php,v 1.9 2009/11/09 19:58:37 duck Exp $
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

// Get filter
$criteria = Minerva::getInvoiceCriteria();

// Prepare the send from
$title = _("Create custom content documents");
$vars = Horde_Variables::getDefaultVariables();
$form = new Minerva_Form_Invoices($vars, $title);
$form->setButtons(array($title, _("Download")), true);

$form->addVariable(_("Subject"), 'subject', 'text', true);
$form->addVariable(_("Body"), 'body', 'longtext', true, false, null, array(20));

if ($prefs->getValue('richtext') != 'none') {
    Horde_Editor::factory($prefs->getValue('richtext'), array('id' => 'body'));
}

if ($form->validate()) {

    $form->getInfo(null, $info);

    // Get clients from invoices
    $invoices = $minerva_invoices->groupByClients($criteria);
    if ($invoices instanceof PEAR_Error) {
        $notification->push($invoices);
        header('Location: ' . Horde::applicationUrl('list/custom.php'));
        exit;
    }

    // Convert documents
    $files = array();
    $convert = Minerva_Convert::factory('custom');
    foreach ($invoices as $invoice_id => $invoice_data) {
        $invoice_data['custom'] = array('subject' => $info['subject'],
                                        'body' => $info['body']);
        $files[$invoice_id] = $convert->convert($invoice_data['invoices'][0], $invoice_data);
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

    if (Horde_Util::getFormData('submitbutton') != _("Print")) {
        echo '<script type="text/javascript"> window.onload=window.print() </script>';
    }

    exit;

}

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render();
$form->renderActive(null, null, Horde::applicationUrl('list/custom.php'), 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
