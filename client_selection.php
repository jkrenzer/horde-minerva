<?php
/**
 * Minerva intractive client list
 *
 * $Horde: incubator/minerva/client_selection.php,v 1.14 2009/07/09 08:18:13 slusarz Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
define('MINERVA_BASE', dirname(__FILE__));
require_once MINERVA_BASE . '/lib/base.php';
require_once MINERVA_BASE . '/lib/UI/Clients.php';

$method = Horde_Util::getFormData('method');
$clients = new Horde_UI_Clients();

switch ($method) {
case 'auto':
    $input = current($_POST);
    if (empty($input)) {
        break;
    }
    $field = Horde_Util::getFormData('field', 'name');

    $c = $clients->search($input, array($field));
    if (!empty($c)) {
        echo $clients->renderAutocomplete($c, $field);
    }
    break;

case 'get':
    $id = Horde_Util::getFormData('key');
    $client = $clients->getOne($id);
    if (!empty($client)) {
        echo Horde_Serialize::serialize($client, Horde_Serialize::JSON, Horde_Nls::getCharset());
    }
    break;

default:
    require MINERVA_TEMPLATES . '/common-header.inc';
    $clients->render('invoice', 'articles_data_1_name');
    require $registry->get('templates', 'horde') . '/common-footer.inc';
    break;
}
