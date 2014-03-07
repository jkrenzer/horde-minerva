<?php
/**
 * Minerva Invoce Converison to PDF/DOC/RTF...
 *
 * $Horde: incubator/minerva/invoice/convert.php,v 1.21 2009/11/09 19:58:36 duck Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */

$no_compress = true;

require_once dirname(__FILE__) . '/../lib/base.php';

$invoice_id = Horde_Util::getFormData('invoice_id');
$type = Horde_Util::getFormData('type', 'html');
$template = Horde_Util::getFormData('template');
$inline = Horde_Util::getFormData('inline', false);

$convert = Minerva_Convert::factory($template);
$filename = $convert->convert($invoice_id, array(), null, $type);

if ($filename instanceof PEAR_Error) {
    echo $filename->getMessage();
    exit;
}

if (!$inline) {
    $browser->downloadHeaders(basename($filename));
}

readfile($filename);
