<?php
/**
 * Recurrence list
 *
 * $Horde: incubator/minerva/recurrence/list.php,v 1.25 2009/12/01 12:52:45 jan Exp $
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

// check permissions
if (!Minerva::hasRecurrencePermission(Horde_Perms::SHOW)) {
    $notification->push(_("You don't have permission to perform this action"), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('recurrence/list.php'));
    exit;
}

$title = _("Recurrence");

/* Get recurrences */
$recurrances = new Minerva_Recurrences();
$rows = $recurrances->getAll();
if ($rows instanceof PEAR_Error) {
    $notification->push($rows);
    $rows = array();
}

/* Reformat */
$t = $_SERVER['REQUEST_TIME'];
$t1 = $_SERVER['REQUEST_TIME'] + 86400;
$dfm = $GLOBALS['prefs']->getValue('date_format');
$total = $total_tax = $total_bare = 0;

foreach ($rows as $id => $row) {
    $last = '';

    $next = $recurrances->getNext($row['rstart'], $row['rend'], $row['rinterval']);
    if ($next == 0) {
        $next = '';
    } else {

        // Format next roeccurence
        if ($recurrances->recurrsOn($next, $t)) {
            $next = '<span style="color: red; font-weight: bold; background: #cccccc;">'  . strftime($dfm, $next) . '</span>';
        } elseif ($recurrances->recurrsOn($next, $t1)) {
            $next = '<span style="color: green; font-weight: bold; background: #cccccc;">'  . strftime($dfm, $next) . '</span>';
        } else {
            $next = strftime($dfm, $next);
        }

        // Get & Format last roeccurence
        $last = $recurrances->getLast($row['rstart'], $row['rend'], $row['rinterval']);
        if ($last) {
            $last = strftime($dfm, $last);
        }
    }
    $rows[$id]['next'] = $next;
    $rows[$id]['last'] = $last;
    $rows[$id]['rstart'] = strftime($dfm, strtotime($row['rstart']));
    $rows[$id]['client'] = $row['client'] ? _("Client") : '';
    $rows[$id]['articles'] = $row['articles'] ? _("Articles") : '';
    $rows[$id]['total'] = Minerva::format_price($rows[$id]['total']);
    $total += $rows[$id]['total'];
    $total_bare += $rows[$id]['total_bare'];
    $total_tax += $rows[$id]['tax'];
}

/* Set up the template fields. */
$img_dir = $registry->getImageDir('horde');
$invoice_url = Horde_Util::addParameter(Horde::applicationUrl('invoice/invoice.php'), 'clone_id', '');
$edit_url = Horde_Util::addParameter(Horde::applicationUrl('recurrence/edit.php'), 'invoice_id', '');
$delete_url = Horde_Util::addParameter(Horde::applicationUrl('recurrence/delete.php'), 'invoice_id', '');
$total = Minerva::format_price($total);
$total_bare = Minerva::format_price($total_bare);
$total_tax = Minerva::format_price($total_tax);
$count = count($rows);

Horde::addScriptFile('tables.js', 'horde');
require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

require MINERVA_TEMPLATES . '/recurrence/list.inc';

require $registry->get('templates', 'horde') . '/common-footer.inc';
