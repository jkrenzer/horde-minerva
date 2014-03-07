<?php
/**
 * Find client by EU VAT
 *
 * $Horde: incubator/minerva/invoice/vatid.php,v 1.13 2009/07/09 08:18:14 slusarz Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */

define('MINERVA_BASE', dirname(__FILE__) . '/../');
require_once MINERVA_BASE . '/lib/base.php';
require_once MINERVA_BASE . '/lib/UI/Clients.php';

$title = _("Find client by EU VAT");
$vars = Horde_Variables::getDefaultVariables();
$form = new Horde_Form($vars, $title, 'euvat');
$v = &$form->addVariable(_("Vat"), 'vatid', 'text', true);
$v->setDefault(substr(Horde_Nls::select(), 3));

if ($form->validate()) {

    $form->getInfo(null, $info);
    if (!preg_match('/^([A-Z]{2})([0-9A-Za-z\+\*\.]{2,12})$/', $info['vatid'], $matches)) {
        $notification->push(_("Invalid VAT identification number format."), 'horde.warning');

    } else {

        require 'SOAP/Client.php';
        $client = new SOAP_Client('http://ec.europa.eu/taxation_customs/vies/api/checkVatPort?wsdl', true, false, array(), Horde::getTempDir());
        $params = array('countryCode' => $matches[1], 'vatNumber' => $matches[2]);
        @$result = $client->call('checkVat', $params);
        if ($result instanceof SOAP_Fault) {
            $error = $result->getMessage();
            switch (true) {
            case strpos($error, 'INVALID_INPUT'):
                $error = _("The provided country code is invalid.");
                break;
            case strpos($error, 'SERVICE_UNAVAILABLE'):
                $error = _("The service is currently not available. Try again later.");
                break;
            case strpos($error, 'MS_UNAVAILABLE'):
                $error = _("The member state service is currently not available. Try again later or with a different member state.");
                break;
            case strpos($error, 'TIMEOUT'):
                $error = _("The member state service could not be reached in time. Try again later or with a different member state.");
                break;
            case strpos($error, 'SERVER_BUSY'):
                $error = _("The service is currently too busy. Try again later.");
                break;
            }
            $notification->push($error);
        } else {
            if (!$result['valid']) {
                $notification->push(_("This VAT identification number is invalid."), 'horde.warning');
            } else {
                $found = true;
            }
        }

    }

}

Horde::addScriptFile('client.js', 'minerva', true);
require MINERVA_TEMPLATES . '/common-header.inc';

$notification->notify(array('listeners' => 'status'));
$form->renderActive();

Horde_UI_Clients::js('invoice', 'client_vat');

if (isset($found)) {
    require MINERVA_TEMPLATES . '/invoice/vatid.inc';
}

require $registry->get('templates', 'horde') . '/common-footer.inc';


