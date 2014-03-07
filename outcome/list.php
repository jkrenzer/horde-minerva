<?php
/**
 * Outcomes list
 *
 * $Horde: incubator/minerva/outcome/list.php,v 1.43 2009/12/10 17:42:34 jan Exp $
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
require_once HORDE_BASE . '/incubator/Horde_Rdo/Form_Helper.php';

if (!Minerva::hasOutcomePermission(Horde_Perms::SHOW)) {
    $notification->push(_("You don't have permission to view outcomes"), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('index.php'));
    exit;
}

/* Get data. */
$title = _("Outcome list");
$outcomes = new Minerva_OutcomeMapper();
$page = (int)Horde_Util::getFormData('page');
$perpage = $prefs->getValue('outcome_perpage');

$criteria = $outcomes->loadQuery();
$criteria['limit'] = array('from' => $page, 'count' => $perpage);

$vars = Horde_Variables::getDefaultVariables();
$form = new Horde_Rdo_Form_Helper($vars, '', '', array('action' => Horde_Util::getFormData('action', 'search'),
                                                        'mapper' => new Minerva_OutcomeMapper()));
$form->getInfo(null, $criteria['tests']);
$query = $outcomes->getQuery($criteria);
$count = $outcomes->count($query);
$outcomes->saveQuery($criteria);

$total = 0;
$total_tax = 0;
$edit_url = Horde::applicationUrl('outcome/edit.php');
$del_url = Horde::applicationUrl('outcome/delete.php');
$list_url = Horde::applicationUrl('outcome/topay.php');
$view_url = Horde::applicationUrl('outcome/invoice.php');
$img_dir = $registry->getImageDir('horde');

// Loop through available links
$rows = array();
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

$pager = new Horde_Ui_Pager('page',
                            Horde_Variables::getDefaultVariables(),
                            array('num' => $count,
                                  'url' => 'outcome/list.php',
                                  'page_count' => 10,
                                  'perpage' => $perpage));
$pager = $pager->render();

require_once MINERVA_TEMPLATES . '/common-header.inc';
require_once MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render('list');
require MINERVA_TEMPLATES . '/outcome/list.inc';
$form->renderActive(null, null, '', 'post');

require_once $registry->get('templates', 'horde') . '/common-footer.inc';
