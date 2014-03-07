<?php
/**
 * CVS Outcome export
 *
 * $Horde: incubator/minerva/lib/Outcome/Export/CSV.php,v 1.7 2009/01/06 17:51:00 jan Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Outcome_Export_CSV extends Outcome_Export {

    public $contentType = 'text/comma-separated-values';
    public $extension = 'cvs';

    public function process($invoices)
    {
        $csv = Horde_Data::singleton('csv');
        return $csv->exportData($invoices, false, array());
    }
}
