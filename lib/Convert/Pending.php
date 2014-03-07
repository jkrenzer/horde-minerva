<?php
/**
 * Minerva_Convert_Pending:: defines an API for creating printable pending list document.
 *
 * $Horde: incubator/minerva/lib/Convert/Pending.php,v 1.16 2009/01/06 17:50:59 jan Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Convert_Pending extends Minerva_Convert {

    /**
     * Template we are currently using
     *
     * @var template
     */
    protected $_template = 'pending';

    /**
    * Convert invoice
    *
    * @return Pathe wehere the file was saved or PEAR_Error on failure
    */
    public function convert($client, $invoices, $date)
    {
        $result = parent::_getTemplate();
        if ($result instanceof PEAR_Error) {
            return $result;
        }

        $i = 0;
        $total = 0;
        foreach ($invoices as $invoice_id => $invoice) {
            $invoices[$invoice_id]['num'] = ++$i;
            $total += $invoices[$invoice_id]['total'];
            $invoices[$invoice_id]['total'] = Minerva::format_price($invoice['total'], $invoice['currency']);
            $invoices[$invoice_id]['date'] = Minerva::format_date($invoice['date'], false);
            $invoices[$invoice_id]['expire'] = Minerva::format_date(strtotime($invoice['date']) + $invoice['expire'] * 86400, false) .
                                               ' (' . Minerva::expireDate($invoice['expire'], $invoice['date']) . ')';
        }

        array_walk($client, array($this, '_formatOutputWalk'));

        $this->title = _("Pending");
        $this->invoices = $invoices;
        $this->client = $client;
        $this->date = Minerva::format_date($date, false);
        $this->total = Minerva::format_price($total, $invoice['currency']);
        $this->credit = Minerva::format_price(0, $invoice['currency']);

        return $this->_saveContent();
    }
}
