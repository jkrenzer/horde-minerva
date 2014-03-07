<?php
/**
 * $Horde: incubator/minerva/show.php,v 1.8 2009/09/15 15:10:58 duck Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */

require_once dirname(__FILE__) . '/lib/base.php';

$type = Horde_Util::getGet('type');
$invoice_id = Horde_Util::getGet('invoice_id');

if ($type == 'outcome') {
    $url = Horde_Util::addParameter(Horde::applicationUrl('outcome/invoice.php'), 'invoice_id', $invoice_id, false);
} else {
    $url = Horde_Util::addParameter(Horde::applicationUrl('invoice/convert.php'),
                              array('invoice_id' => $invoice_id, 'inline' => true), null, false);
}

header('Location: ' . $url);
exit;