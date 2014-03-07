<?php
/**
 * Export new recurrences to iCalendar file
 *
 * $Horde: incubator/minerva/recurrence/ics.php,v 1.10 2010/02/01 10:32:06 jan Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */

define('AUTH_HANDLER', true);
$session_control = 'none';

require_once dirname(__FILE__) . '/../lib/base.php';

// Authenticate.
$auth = Horde_Auth::singleton($conf['auth']['driver']);
if (!isset($_SERVER['PHP_AUTH_USER'])
    || !$auth->authenticate($_SERVER['PHP_AUTH_USER'],
                            array('password' => isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : null))) {
    header('WWW-Authenticate: Basic realm="Minerva iCalendar Interface"');
    header('HTTP/1.0 401 Unauthorized');
    echo '401 Unauthorized';
    exit;
}

$cache = $GLOBALS['injector']->getInstance('Horde_Cache');
$ics = $cache->get('recurrances.ics', $conf['cache']['default_lifetime']);
if (!$ics) {

    require_once MINERVA_BASE . '/lib/Recurrence.php';

    $iCal = new Horde_iCalendar();
    $iCal->setAttribute('X-WR-CALNAME', _("Outcomes"));

    $recurrances = new Minerva_Recurrences();
    foreach ($recurrances->getAll() as $invoice) {
        $event = $recurrances->toiCalendar($invoice, $iCal);
        $iCal->addComponent($event);
    }

    $ics = $iCal->exportvCalendar();
    $cache->set('recurrances.ics', $ics);
}

$browser->downloadHeaders('recurrances.ics', 'text/calendar', true, strlen($ics));
echo $ics;
