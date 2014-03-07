<?php
/**
 * Export Outcome list
 *
 * $Horde: incubator/minerva/outcome/export.php,v 1.33 2009/12/01 12:52:45 jan Exp $
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
require_once MINERVA_BASE . '/lib/Outcome/Export.php';
require_once MINERVA_BASE . '/outcome/tabs.php';

if (!Minerva::hasOutcomePermission(Horde_Perms::READ)) {
    $notification->push(_("You don't have permission to read outcomes"), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('index.php'));
    exit;
}

// Prepare the send from
$title = _("Export");
$vars = Horde_Variables::getDefaultVariables();

// Get outcomes
$outcomes = new Minerva_OutcomeMapper();
$criteria = $outcomes->loadQuery();
$criteria['fields'] = array('id', 'client_name', 'total', 'intend', 'due', 'currency');

$list = array();
$query = $outcomes->getQuery($criteria);
$count = $outcomes->count($query);

$form = new Minerva_Form_Invoices($vars, $title . ' (' . number_format($count) . ')', 'exporttodatafile');
$form->setButtons($title, true);

if ($count < 50) {
    $results = new Horde_Rdo_List($query);
    foreach ($results as $row) {
        $list[$row->id] = array(
                'id' => $row->id,
                'client_name' => $row->client_name,
                'total' => Minerva::format_price($row->total, $row->currency),
                'intend' => $row->intend,
                'due' => Minerva::format_date($row->due, false),
                'currency' => $row->currency,
                );
    }
    $headers = array(_("Name"), _("Company"), _("Total"), _("Intend"), _("Due"), _("Currency"));
    $form->addVariable(_("Invoices"), 'invoices', 'tableset', true, false, false, array($list, $headers));
}

$form->addVariable(_("Bank"), 'bank', 'enum', true, false, false, array(Minerva::getBankAccounts()));
$form->addVariable(_("Format"), 'format', 'enum', true, false, false, array(Outcome_Export::formats()));
$form->addVariable(_("Charset"), 'charset', 'enum', true, false, false, array(Horde_Nls::$config['encodings']));

if ($form->validate()) {

    $form->getInfo(null, $info);

    $rows = array();
    if (empty($info['invoices'])) {
        foreach (new Horde_Rdo_List($query) as $row) {
            $rows[] = iterator_to_array($row);
        }
    } else {
        foreach ($results as $row) {
            if (in_array($row->id, $info['invoices'])) {
                $rows[] = iterator_to_array($row);
            }
        }
    }

    $exporter = Outcome_Export::factory($info['format']);
    $exporter->setBankAccount($info['bank']);
    $data = $exporter->process($rows);
    if ($info['charset'] != strtoupper(Horde_Nls::getCharset())) {
        $data = Horde_String::convertCharset($data, Horde_Nls::getCharset(), $info['charset']);
    }

    $filename = $info['format'] . '-' . date('dmy') . '-' . sizeof($info['invoices']) . '.' . $exporter->extension;
    $browser->downloadHeaders($filename, $exporter->contentType, false, strlen($data));

    echo $data;
    exit;

} elseif (!$form->isSubmitted()) {
    $vars->set('charset', $prefs->getValue('outcome_charset'));
}

require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render('export');
$form->renderActive(null, null, null, 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
