<?php
/**
 * Resellers
 *
 * $Horde: incubator/minerva/admin/resellers.php,v 1.24 2009/11/09 19:58:35 duck Exp $
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
require_once MINERVA_BASE . '/lib/UI/Clients.php';
require_once 'tabs.php';

$resellers = new Minerva_Resellers();

if (!($resellers_list = $resellers->getAll())) {
    $notification->push(_("No resellers found."), 'horde.warning');
    header('Location: ' . Horde::applicationUrl('list/list.php'));
    exit;
}

// Set up varuables
$title = _("Resellers");
$imagedir = $registry->getImageDir('horde');
$resellers_url = Horde::applicationUrl('admin/resellers.php');

// Should we delete someting?
if (($action = Horde_Util::getGet('action')) == 'delete') {
    $resellers->delete(Horde_Util::getGet('reseller_id'), Horde_Util::getGet('client_id'));
    $notification->push(_("Reseller link deleted."), 'horde.succesful');
    header('Location: ' . $resellers_url);
    exit;
}

$vars = Horde_Variables::getDefaultVariables();
$form = new Horde_Form($vars, _("Add a new Reseller Link"), 'resellers');

// List clients
$clients_ui = new Horde_UI_Clients();
$clients = $clients_ui->getAll();
foreach ($clients as $id => $client) {
    $clients[$id] = $client['name'];
}

// List resellers
foreach ($resellers_list as $id => $reseller) {
    $resellers_enum[$id] = $reseller['name'];
}

$form->addVariable(_("Reseller"), 'reseller_id', 'enum', true, false, false, array($resellers_enum, true));
$form->addVariable(_("Client"), 'client_id', 'enum', true, false, false, array($clients, true));
$form->addVariable(_("Percentage"), 'percentage', 'number', true);

// Update reseller data
if ($form->validate()) {
    $form->getInfo(null, $info);
    $result = $resellers->save($info);
    if ($result instanceof PEAR_Error) {
        $notification->push($result);
    } else {
        $notification->push(_("Reseller link deleted."), 'horde.succesful');
    }
    header('Location: ' . $resellers_url);
    exit;
}

Horde::addScriptFile('tables.js', 'horde');
require MINERVA_TEMPLATES . '/common-header.inc';
require MINERVA_TEMPLATES . '/menu.inc';

echo $tabs->render('resellers');

require MINERVA_TEMPLATES . '/admin/resellers/header.inc';
foreach ($resellers_list as $id => $reseller) {
    if (empty($reseller['clients'])) {
        continue;
    }
    require MINERVA_TEMPLATES . '/admin/resellers/row.inc';
}
require MINERVA_TEMPLATES . '/admin/resellers/footer.inc';

$form->renderActive(null, $vars, $resellers_url, 'post');

require $registry->get('templates', 'horde') . '/common-footer.inc';
