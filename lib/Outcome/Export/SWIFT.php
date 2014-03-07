<?php
/**
 * SWIFT
 *
 * $Horde: incubator/minerva/lib/Outcome/Export/SWIFT.php,v 1.9 2009/04/13 17:29:17 duck Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */

class Outcome_Export_SWIFT extends Outcome_Export {

    public $contentType = 'text/plain';
    public $extension = 'txt';

    public function process($invoices)
    {
        $data = '';
        $msgtotal = count($invoices);
        $msgindex = 0;

        $currencies = Minerva::getCurrencies();
        $defaultCur = Minerva::getDefaultCurrency();
        $currency = $currencies[$defaultCur];

        $data .= '{4:' . "\n"
               . ':20:GROUP' . $_SERVER['REQUEST_TIME'] . "\n";

        foreach ($invoices as $id => $invoice) {

            $msgindex++;
            $total = str_replace('.', $currency['mon_decimal_point'], $invoice['total']);

            if (empty($invoice['refference'])) {
                $invoice['refference'] = '00' . date('Ymd');
            }

            if (empty($invoice['internal_refference'])) {
                $invoice['internal_refference'] = $invoice['refference'];
            }

            if (empty($invoice['client_city'])) {
                $invoice['client_city'] = '';
            }

            foreach ($invoice as $key => $value) {
                if ($key == 'client_name' || $key == 'intend') {
                    continue;
                }

                $invoice[$key] = str_replace('-', '', $invoice[$key]);
            }

            $data .= ':28D:' . $msgindex . '/' . $msgtotal . "\n"
                   . ':50H:/' . $this->account['account'] . "\n"
                   . $this->account['short_name'] . "\n"
                   . $this->account['address'] . "\n"
                   . $this->account['postal'] . ' ' . $this->account['city'] . "\n"
                   . ':30:' . date('dmy', strtotime($invoice['due'])) . "\n"
                   . ':21:' . $invoice['internal_refference'] . "\n"
                   . ':32B:' . $defaultCur . '/' . $total . "\n"
                   . ':59:/' . $invoice['client_bank_account'] . "\n"
                   . $invoice['client_name'] . "\n"
                   . ':70:' . $invoice['refference'] . "\n"
                   . $invoice['intend'] . "\n"
                   . ':77B:/SI/A3012' . "\n"
                   . ':71A:OUR' . "\n";
        }

        $data .= '-}' . "\n";

        return $data;
    }

}

