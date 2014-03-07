<?php
/**
 * Script for sending reocurences invoices
 *
 * $Horde: incubator/minerva/scripts/recurrence.php,v 1.24 2009/12/01 12:52:45 jan Exp $
 *
 * Copyright Duck <duck@obala.net>
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author Duck <duck@obala.net>
 * @package Minerva
 */

define('AUTH_HANDLER', true);
define('HORDE_BASE', dirname(__FILE__) . '/../../../');
define('MINERVA_BASE', HORDE_BASE . '/incubator/minerva');

// Do CLI checks and environment setup first.
require_once HORDE_BASE . '/lib/core.php';

// Make sure no one runs this from the web.
if (!Horde_Cli::runningFromCLI()) {
    exit("Must be run from the command line\n");
}

// Load the CLI environment.
Horde_Cli::init();
$cli = &Horde_Cli::singleton();

// We accept the user name on the command-line.
require_once 'Console/Getopt.php';
$ret = Console_Getopt::getopt(Console_Getopt::readPHPArgv(), 'hu:p:lc:g:a:d:t:',
                              array('help', 'username=', 'password='));

if ($ret instanceof PEAR_Error) {
    $error = _("Couldn't read command-line options.");
    Horde::logMessage($error, __FILE__, __LINE__, PEAR_LOG_DEBUG);
    $cli->fatal($error);
}

// Show help and exit if no arguments were set.
list($opts, $args) = $ret;
if (!$opts) {
    showHelp();
    exit;
}

foreach ($opts as $opt) {
    list($optName, $optValue) = $opt;
    switch ($optName) {
    case 'u':
    case '--username':
        $username = $optValue;
        break;

    case 'p':
    case '--password':
        $password = $optValue;
        break;

    case 'h':
    case '--help':
        showHelp();
        exit;
    }
}

// Login to horde if username & password are set.
if (!empty($username) && !empty($password)) {
    $auth = Horde_Auth::singleton($conf['auth']['driver']);
    if (!$auth->authenticate($username, array('password' => $password))) {
        $error = _("Login is incorrect.");
        Horde::logMessage($error, __FILE__, __LINE__, PEAR_LOG_ERR);
        $cli->fatal($error);
    } else {
        $msg = sprintf(_("Logged in successfully as \"%s\"."), $username);
        Horde::logMessage($msg, __FILE__, __LINE__, PEAR_LOG_DEBUG);
        $cli->message($msg, 'cli.success');
    }
}

/**
 * Check permissions.
 */
if (!Minerva::hasRecurrencePermission(Horde_Perms::DELETE)) {
    echo _("You don't have permission to perform this action");
    exit;
}

/**
 * Load Minerva and create needed objects.
 */
require_once MINERVA_BASE . '/lib/base.php';
require_once MINERVA_BASE . '/lib/Email.php';

$drafts = new Minerva_EmailMapper();
$convert = Minerva_Convert::factory();
$recurrances = new Minerva_Recurrences();

/**
 * Check if the current day is a holiday.
 */
$invoice_date = date('Y-m-d');
if (Minerva::isHoliday($invoice_date)) {
    if ($conf['recurrence']['skip_holiday']) {
        echo _("The invoice date is a holiday.");
        exit;
    }
    $invoice_date = date('Y-m-d', Minerva::nextWorkingDay($invoice_date));
    printf(_("The invoice date was automatically changed to %s"), $invoice_date);
}

/**
* Get recurrances
*/
$invoices = $recurrances->getRecurrences();
if ($invoices instanceof PEAR_Error) {
    echo $invoices->getMessage() . ': ' . $invoices->getDebugInfo();
    exit;
}

/**
* Pass by invoices
*/
foreach ($invoices as $invoice_id => $data) {

    // Check the interval
    $next = $recurrances->getNext($data['rstart'], $data['rend'], $data['rinterval']);
    if (!$recurrances->recurrsOn($next)) {
        continue;
    }

    // get original invoice data
    $invoice = $minerva_invoices->getOne($invoice_id);
    if ($invoices instanceof PEAR_Error) {
        echo $invoices->getMessage() . ': ' . $invoices->getDebugInfo();
        continue;
    }

    // remove invoice name and id
    unset($invoice['invoice']['id'], $invoice['invoice']['name']);

    // set invoice date from the holiday check
    $invoice['invoice']['date'] = $invoice_date;

    // set status
    $invoice['invoice']['status'] = $data['rstatus'];

    // calcualte the service period
    $oldtime = $_SERVER['REQUEST_TIME'] - $data['rinterval'] * 86400;
    $invoice['invoice']['service'] = strftime($prefs->getValue('date_format'), $oldtime)  . ' - ' .
                                     strftime($prefs->getValue('date_format'));

    // save the new invoice
    $newid = $minerva_invoices->save($invoice);
    if ($newid instanceof PEAR_Error) {
        echo $newid->getMessage() . ': ' . $newid->getDebugInfo();
        continue;
    }

    // remember that we made it
    $recurrances->updateRecurrence($invoice_id);

    // always send the invoice to ourselfs
    sendInvoice($newid,
                Minerva::getFromAddress(),
                _("Autogenerated recurrence invoice"),
                sprintf(_("Created on %s from invoice %s"), date('Y-m-d H:i:s'), $invoice_id));

    // We would like to send to any other?
    if ($data['draft'] && $data['sento']) {
        $draft = $drafts->find($id);
        sendInvoice($newid, $data['sento'], $draft['subject'], $draft['body']);
    }
}

/**
 * Send an email
 */
function sendInvoice($invoice_id, $id, $to, $subject, $body)
{
    static $filename;

    if ($filename !== null) {
       $filename = $convert->convert($invoice_id);
    }

    try {
        Minerva::sendMail($GLOBALS['conf']['finance']['from_addr'],
                            $to, $subject, $body, array($filename));
    } catch (Horde_Exception $e) { }
}

/**
 * Show the command line arguments that the script accepts.
 */
function showHelp()
{
    global $cli;

    $cli->writeln(sprintf(_("Usage: %s [OPTIONS]..."), basename(__FILE__)));
    $cli->writeln();
    $cli->writeln(_("Mandatory arguments to long options are mandatory for short options too."));
    $cli->writeln();
    $cli->writeln(_("-h, --help                   Show this help"));
    $cli->writeln(_("-u, --username[=username]    Horde login username"));
    $cli->writeln(_("-p, --password[=password]    Horde login password"));
    $cli->writeln();
}
