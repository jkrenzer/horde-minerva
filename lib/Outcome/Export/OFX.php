<?php
/**
 * OFX - Open Financial Exchange - Specification 2.1.1
 *
 * @link http://www.ofx.net
 *
 * $Horde: incubator/minerva/lib/Outcome/Export/OFX.php,v 1.9 2009/01/06 17:51:00 jan Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Outcome_Export_OFX extends Outcome_Export {

    public $contentType = 'text/ofx';
    public $extension = 'ofx';

    public function process($invoices)
    {
        $balance = 0;

        $data = '<OFX>' . "\n"
              . '   <BANKMSGSRSV1>' . "\n"
              . '       <STMTTRNRS>' . "\n"
              . '           <STMTRS>' . "\n"
              . '               <CURDEF>' . Minerva::getDefaultCurrency() . '</CURDEF>' . "\n"
              . '                   <BANKTRANLIST>' . "\n";

        foreach ($invoices as $id => $invoice) {
            $invoice['total'] = $invoice['total'] * (-1);
            $posted = strtotime($invoice['due']);
            $balance = $invoice['total'];
            $data .= '                          <STMTTRN>' . "\n"
                   . '                              <TRNTYPE>DEBIT</TRNTYPE>' . "\n"
                   . '                              <DTPOSTED>' . date('Ymdhi', $posted) . '</DTPOSTED>' . "\n"
                   . '                              <TRNAMT>' . $invoice['total'] . '</TRNAMT>' . "\n"
                   . '                              <FITID>' . $id . '</FITID>' . "\n"
                   . '                         </STMTTRN>' . "\n";
        }

        $data .= '                       <DTSTART>' . date('Ymdhi', $_SERVER['REQUEST_TIME']) . '</DTSTART>' . "\n"
               . '                       <DTEND>' . date('Ymdhi', $_SERVER['REQUEST_TIME']) . '</DTEND>' . "\n"
               . '                   </BANKTRANLIST>' . "\n"
               . '                   <AVAILBAL>' . "\n"
               . '                       <BALAMT>' . floor($balance) . '</BALAMT>' . "\n"
               . '                       <DTASOF>' . date('Ymdhi') . '</DTASOF>' . "\n"
               . '                   </AVAILBAL>' . "\n"
               . '           </STMTRS>' . "\n"
               . '       </STMTTRNRS>' . "\n"
               . '   </BANKMSGSRSV1>' . "\n"
               . '</OFX>' . "\n";

        return $data;
    }
}