<?php
/**
 * Minerva base application file.
 *
 * $Horde: incubator/minerva/lib/base.php,v 1.33 2010/02/01 10:32:06 jan Exp $
 *
 * This file brings in all of the dependencies that every Minerva
 * script will need, and sets up objects that all scripts use.
 */

// Check for a prior definition of HORDE_BASE (perhaps by an
// auto_prepend_file definition for site customization).
if (!defined('HORDE_BASE')) {
    define('HORDE_BASE', dirname(__FILE__) . '/../../..');
}

// Load the Horde Framework core, and set up inclusion paths and autoloading.
require_once HORDE_BASE . '/lib/core.php';

// Registry.
$registry = Horde_Registry::singleton();
try {
    $registry->pushApp('minerva');
} catch (Horde_Exception $e) {
    if ($e->getCode() == Horde_Registry::PERMISSION_DENIED) {
        Horde_Auth::authenticateFailure('minerva', $e);
    }
    Horde::fatal($e, __FILE__, __LINE__, false);
}
$conf = &$GLOBALS['conf'];
define('MINERVA_TEMPLATES', $registry->get('templates'));

// Notification system.
$notification = Horde_Notification::singleton();
$notification->attach('status');

// Define the base file path of Minerva.
if (!defined('MINERVA_BASE')) {
    define('MINERVA_BASE', dirname(__FILE__) . '/..');
}

// Cache
$GLOBALS['cache'] = $GLOBALS['injector']->getInstance('Horde_Cache');

// Taxes and Currencies helpers.
require_once dirname(__FILE__) . '/../../Horde_Currencies/Currencies.php';
require_once dirname(__FILE__) . '/../../Horde_Taxes/Taxes.php';

// Minerva base library
$GLOBALS['minerva_invoices'] = new Minerva_Invoices();

// Start output compression.
if (!Horde_Util::nonInputVar('no_compress')) {
    Horde::compressOutput();
}
