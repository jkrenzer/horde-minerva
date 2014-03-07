<?php
/**
 * Standard of Public Payments Administration of the Republic of Slovenia (
 * Local abbreviated name: APP.
 *
 * @link http://www.ujp.gov.si/dokumenti/dokument.asp?id=21
 *
 * $Horde: incubator/minerva/lib/Outcome/Export/APP.php,v 1.10 2009/06/10 05:24:24 slusarz Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */

class Outcome_Export_APP extends Outcome_Export {

    public $contentType = 'text/plain';
    public $extension = 'txt';

    public function process($invoices)
    {
        $data = $this->_header($invoices);

        foreach ($invoices as $id => $invoice) {

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
                $invoice[$key] = str_replace('.', '', $invoice[$key]);
            }

            $data .= $this->_format_data($invoice['client_bank_account'], 18)
                   . $this->_format_data($invoice['client_name'], 35)
                   . $this->_format_data($invoice['client_city'], 10)
                   . '0'
                   . $this->_format_data($invoice['refference'], 24)
                   . $this->_format_data($invoice['intend'], 36)
                   . '00000'
                   . ' A3011'
                   . $this->_format_data($invoice['total'], 13, true)
                   . $this->_format_data($invoice['internal_refference'], 24)
                   . $this->_format_data(date('dmy', strtotime($invoice['due'])), 7)
                   . $this->_format_data(1, 1) . "\n";
        }

        return $data;
    }

    /**
    * Add data _header
    */
    private function _header($invoices)
    {
        $total = 0;

        foreach ($invoices as $id => $invoice) {
            $total += $invoice['total'];
        }

        $account = str_replace('-', '', $this->_account['account']);

        $data = $this->_format_data($account, 18)
              . $this->_format_data($this->_account['short_name'], 35)
              . $this->_format_data($this->_account['postal'] . ' ' . $this->_account['city'], 10)
              . $this->_format_data(date('dmy'), 6)
              . $this->_format_data('', 3)
              . $this->_format_data('', 3)
              . $this->_format_data('', 104)
              . $this->_format_data(0, 1)
              . "\n"
              . $this->_format_data($account, 18)
              . $this->_format_data($this->_account['short_name'], 35)
              . $this->_format_data($this->_account['postal'] . ' ' . $this->_account['city'], 10)
              . $this->_format_data($total, 15, true)
              . $this->_format_data(count($invoices), 5, true)
              . $this->_format_data('', 1)
              . $this->_format_data('', 1)
              . $this->_format_data('', 89)
              . $this->_format_data('', 3)
              . $this->_format_data('', 2)
              . $this->_format_data(9, 1)
              . "\n";

        return $data;
    }

    /**
    * Formats data according to standard.
    * Mainly fixed length
    *
    * @param string  $data   Data to convert
    * @param intiger $length Fixed length needed
    * @param boolean $price  If we converting a price value
    */
    private function _format_data($data, $length, $price = false)
    {
        $data = trim($data);
        $data_size = Horde_String::length($data, 'UTF-8');

        // noting to do
        if ($data_size == $length) {
            return $data;
        }

        // cut it
        if ($length < $data_size) {
            return substr($data, 0, $length);
        }

        // fill it
        if ($price) {
            return str_repeat('0', $length-$data_size) . $data;
        } else{
            return $data . str_repeat(' ', $length-$data_size);
        }
    }

}

