<?php
/**
 * Minerva Invoice Logger
 *
 * $Horde: incubator/minerva/invoice/log.php,v 1.21 2009/12/10 17:42:33 jan Exp $
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

$invoice_id = (int)Horde_Util::getFormData('invoice_id');
if (!$minerva_invoices->exists($invoice_id)) {
    $notification->push(sprintf(_("Invoice id %s dosen't exists."), $invoice_id), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('list/list.php'));
    exit;
}

$title = sprintf(_("Log for %s"), $minerva_invoices->getName($invoice_id));
$logs = $minerva_invoices->getHistory($invoice_id);
if ($logs instanceof PEAR_Error) {
    $notification->push($logs);
    $logs = array();
}

// known types
$types = array(
    'send' => _("Send"),
    'send_expire' => _("Send past due notice"),
    'save' => _("Save"),
    'download' => _("Download"),
    'set_status' => _("Changed status"),
    'set_tag' => _("Changed tag"),
    'print' => _("Print"),
    'notify' => _("Late payment notification")
);

$page = Horde_Util::getGet('page');
$log_url = Horde_Util::addParameter(Horde::applicationUrl('invoice/log.php'), 'invoice_id', $invoice_id);
$pager = new Horde_Ui_Pager('page',
                            Horde_Variables::getDefaultVariables(),
                            array('num' => count($logs),
                                  'url' => 'invoice/log.php',
                                  'page_count' => 20,
                                  'perpage' => 20));
$pager->preserve('invoice_id', $invoice_id);
$logs = array_slice($logs, $page * 20, 20);

Horde::addScriptFile('tables.js', 'horde');
require MINERVA_TEMPLATES . '/common-header.inc';

require MINERVA_TEMPLATES . '/invoice/log/header.inc';
foreach ($logs as $row) {
    require MINERVA_TEMPLATES . '/invoice/log/row.inc';
}
require MINERVA_TEMPLATES . '/invoice/log/footer.inc';

require $registry->get('templates', 'horde') . '/common-footer.inc';
