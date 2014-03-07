<?php
/**
 * Test minerva enviroment
 *
 * $Horde: incubator/minerva/test.php,v 1.21 2009/06/10 05:24:21 slusarz Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */

/* Include Horde's core.php file. */
include_once '../../lib/core.php';

/* We should have loaded the String class, from the Horde_Util
 * package, in core.php. If Horde_String:: isn't defined, then we're not
 * finding some critical libraries. */
if (!class_exists('String')) {
    echo '<br /><h2 style="color:red">The Horde_Util package was not found. If PHP\'s error_reporting setting is high enough and display_errors is on, there should be error messages printed above that may help you in debugging the problem. If you are simply missing these files, then you need to get the <a href="http://cvs.horde.org/cvs.php/framework">framework</a> module from <a href="http://www.horde.org/source/">Horde CVS</a>, and install the packages in it with the install-packages.php script.</h2>';
    exit;
}

/* Initialize the Horde_Test:: class. */
if (!(is_readable('../../lib/Test.php'))) {
    echo 'ERROR: You must install Horde before running this script.';
    exit;
}
require_once '../../lib/Test.php';
$horde_test = &new Horde_Test;

/* MINERVA version. */
$module = 'MINERVA';
require_once './lib/version.php';
$module_version = MINERVA_VERSION;

require TEST_TEMPLATES . 'header.inc';
require TEST_TEMPLATES . 'version.inc';

/* Display versions of other Horde applications. */
$app_list = array(
    'turba' => array(
        'error' => 'Turba provides clients data.',
        'version' => '2.0',
    ),
    'agora' => array(
        'error' => 'Agora log invoices actions.',
        'version' => '0.1',
    )
);
$app_output = $horde_test->requiredAppCheck($app_list);

?>
<h1>Other Horde Applications</h1>
<ul>
    <?php echo $app_output ?>
</ul>
<?php

/* Display PHP Version informaton. */
$php_info = $horde_test->getPhpVersionInformation('5.1');
require TEST_TEMPLATES . 'php_version.inc';

/* Minerva configuration files. */
$file_list = array(
    'config/conf.php' => null,
    'config/prefs.php' => null,
    'config/clientmap.php' => null,
    'config/holidays.php' => null,
    'config/outcome.php' => null,
    'config/bank.php' => null
);

/* PEAR modules. */
$pear_list = array(
    'Horde_Taxes' => array(
        'path' => HORDE_BASE . '/incubator/Horde_Taxes/Taxes.php',
        'error' => 'Provides and manage taxes.'
    ),
    'Horde_Currencies' => array(
        'path' => HORDE_BASE . '/incubator/Horde_Currencies/Currencies.php',
        'error' => 'Provides and manage currencies.'
    ),
    'Horde_Company' => array(
        'path' => HORDE_BASE . '/incubator/Horde_Company/Horde_Company.php',
        'error' => 'Provides common company data.'
    ),
    'XML_Serializer' => array(
        'path' => 'XML/Serializer.php',
        'error' => 'XML_Serializer is used for exporting invoice data.'
    ),
    'Image_Graph' => array(
        'path' => 'Image/Graph.php',
        'error' => 'Image_Graph is used to design statistical data graph.'
    )
);

/* Get the status output now. */
$file_output = $horde_test->requiredFileCheck($file_list);
$pear_output = $horde_test->PEARModuleCheck($pear_list);

?>

<h1>Required Minerva Configuration Files</h1>
<ul>
    <?php echo $file_output ?>
</ul>

<h1>PEAR</h1>
<ul>
    <?php echo $pear_output ?>
</ul>

<?php
require TEST_TEMPLATES . 'footer.inc';
