<?php
/**
 * Export Invoices list
 *
 * $Horde: incubator/minerva/list/spread.php,v 1.4 2009/11/09 19:58:37 duck Exp $
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

if (!$registry->hasMethod('spread/import')) {
    $notification->push(_("Spreads is not installed"), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('list/list.php'));
    exit;
}

// Get invoices
$list = Minerva::getList();

// Prepare the send from
$title = sprintf(_("Export invoice list into %s"), $registry->get('name', 'spread'));
$vars = Horde_Variables::getDefaultVariables();
$form = new Minerva_Form_Invoices($vars, $title, 'exporttospread');
$form->setButtons($title, true);

// Fill from
$form->addVariable(_("Invoices"), 'invoices', 'tableset', true, false, false, array('values' => $list, 'headers' => Minerva_Invoices::getListHeaders()));

$workbooks = $registry->callByPackage('spread', 'list', array(true));
$form->addVariable(_("Workbook"), 'w', 'enum', false, false, false, array($workbooks, _("Create new")));

$form->addVariable(_("Formatted"), 'formated', 'enum', false, false, false, array(array(_("Bare data"), _("Fromatted values"))));

if ($form->validate()) {

    $form->getInfo(null, $info);

    if (empty($info['invoices'])) {
        $notification->push(_("There are no records representing selected criteria."), 'horde.warning');
        header('Location: ' . Horde::applicationUrl('list/spread.php'));
        exit;
    }

    $statuses = Minerva::getStatuses();

    $data = array();
    foreach ($info['invoices'] as $id) {
        if (!$info['formated']) {
            $data[$id] = $list[$id];
            continue;
        }
        $data[$id] = array('name' => $list[$id]['name'],
                            'company' => $list[$id]['company'],
                            'total_bare' => Minerva::format_price($list[$id]['total_bare']),
                            'tax' => Minerva::format_price($list[$id]['tax']),
                            'total' => Minerva::format_price($list[$id]['total']),
                            'status' => $list[$id]['status'],
                            'date' => Minerva::format_date($list[$id]['date'], false));
    }

    $result = $workbooks = $registry->callByPackage('spread', 'import', array($data, 'array', $info['w']));
    if ($result instanceof PEAR_Error) {
        $notification->push($result);
    } else {
        $notification->push(_("Invoice list was successfully imported in you spreadsheet."), 'horde.sucess');
        header('Location: ' . Horde::applicationUrl('list/list.php'));
        exit;
    }
}

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render();
$form->renderActive(null, null, Horde::applicationUrl('list/spread.php'), 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
