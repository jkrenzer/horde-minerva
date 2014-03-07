<?php
/**
 * $Horde: incubator/minerva/index.php,v 1.17 2009/07/08 18:29:15 slusarz Exp $
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
$minerva_configured = (is_readable(MINERVA_BASE . '/config/conf.php') &&
                       is_readable(MINERVA_BASE . '/config/prefs.php') &&
                       is_readable(MINERVA_BASE . '/config/outcome.php') &&
                       is_readable(MINERVA_BASE . '/config/bank.php') &&
                       is_readable(MINERVA_BASE . '/config/clientmap.php') &&
                       is_readable(MINERVA_BASE . '/config/holidays.php'));

if (!$minerva_configured) {
    require MINERVA_BASE . '/../../lib/Test.php';
    Horde_Test::configFilesMissing('Minerva', MINERVA_BASE,
        array('conf.php', 'prefs.php'),
        array('bank.php' => 'Bank information',
              'clientmap.php' => 'This file maps address book data into client data.',
              'outcome.php' => 'Outcome information',
              'holidays.php' => 'Holidays information'));
}

/* Redirect admin to the admin page if no types or statuses exists */
require_once MINERVA_BASE . '/lib/base.php';
if (Horde_Auth::isAdmin()) {
    $types = Minerva::getTypes();
    $statuses = Minerva::getStatuses();
    if (empty($types) || empty($statuses)) {
        header('location: ' . Horde::applicationUrl('admin/statuses.php'));
        exit;
    }
}

/* Load the selected inital page */
switch ($prefs->getValue('invoice_initial_page')) {
case 'outcome':
    require MINERVA_BASE . '/outcome/list.php';
    break;
default:
    require MINERVA_BASE . '/list/list.php';
    break;
}
