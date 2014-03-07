<?php
/**
 * Opened outcomes list
 *
 * $Horde: incubator/minerva/outcome/topay.php,v 1.38 2009/12/01 12:52:45 jan Exp $
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
require_once MINERVA_BASE . '/outcome/tabs.php';

if (!Minerva::hasOutcomePermission(Horde_Perms::SHOW)) {
    $notification->push(_("You don't have permission to view outcomes"), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('index.php'));
    exit;
}

// Get data
$title = _("Opened outcomes");
$outcomes = new Minerva_OutcomeMapper();
$criteria = array('tests' => array(array('field' => 'paid', 'test' => 'IS', 'value' => null)));
$query = $outcomes->getQuery($criteria);
$count = $outcomes->count($query);

// Save query if we are first time on this page
if (Horde_Util::getGet('outcome') == 'topay') {
    $outcomes->saveQuery($criteria);
}

$total = 0;
$total_tax = 0;
$edit_url = Horde::applicationUrl('outcome/edit.php');
$del_url = Horde::applicationUrl('outcome/delete.php');
$list_url = Horde::applicationUrl('outcome/topay.php');
$view_url = Horde::applicationUrl('outcome/invoice.php');
$img_dir = $registry->getImageDir('horde');

// Loop through available links
foreach (new Horde_Rdo_List($query) as $row) {

    $row = iterator_to_array($row);

    $total += $row['total'];
    $total_tax += $row['total_tax'];
    if ($pos = strpos($row['client_name'], "\n")) {
        $row['client_name'] = substr($row['client_name'], 0, $pos);
    }

    $row['intend'] = nl2br($row['intend']);
    $row['bank'] = @$banks[$row['bank']];
    $row['recived'] = Minerva::format_date($row['recived'], false);
    if ($row['due']) {
        $row['due'] = Minerva::format_date($row['due'], false);
    }
    if ($row['paid'] == '0000-00-00' || is_null($row['paid'])) {
        $row['paid'] = '';
    } else {
        $row['paid'] = Minerva::format_date($row['paid'], false);
    }
    $row['total'] = Minerva::format_price($row['total'], $row['currency']);
    $row['total_tax'] = Minerva::format_price($row['total_tax'], $row['currency']);

    $row['actions'][] = Horde::link(Horde_Util::addParameter($view_url, 'id', $row['id']), _("View")) .
                        Horde::img('map.png', _("View"), '', $img_dir) . '</a>';
    $row['actions'][] = Horde::link(Horde_Util::addParameter($edit_url, array('action' => 'update', 'id' => $row['id'])), _("Edit")) .
                        Horde::img('edit.png', _("Edit"), '', $img_dir) . '</a>';
    $row['actions'][] = Horde::link(Horde_Util::addParameter($del_url, 'id', $row['id']), _("Delete")) .
                        Horde::img('delete.png', _("Delete"), '', $img_dir) . '</a>';

    $rows[] = $row;
}

// Set up the template fields
$rows = $rows;
$title = $title;
$list_url = $list_url;
$edit_url = Horde::applicationUrl('outcome/edit.php');
$export_url = Horde::applicationUrl('outcome/export.php');
$img_dir = $registry->getImageDir('horde');
$total = Minerva::format_price($total);
$total_tax = Minerva::format_price($total_tax);
$pager = '';

require_once MINERVA_TEMPLATES . '/common-header.inc';
require_once MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render('topay');
require MINERVA_TEMPLATES . '/outcome/list.inc';

require_once $registry->get('templates', 'horde') . '/common-footer.inc';
