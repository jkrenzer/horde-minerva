`<?php
/**
 * Unlock invoice - used with ajax berforme page unload
 *
 * $Horde: incubator/minerva/invoice/unlock.php,v 1.11 2009/09/15 15:10:58 duck Exp $
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

$invoice_id = (int)Horde_Util::getFormData('invoice_id', 0);
if ($invoice_id > 0) {
        $minerva_invoices->removeLock($invoice_id);
}
