<?php
/**
 * Use horde calendar for publish date
 *
 * $Horde: incubator/minerva/templates/invoice/date.inc,v 1.12 2009/12/10 17:32:58 jan Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */

function _dateSelect($name, $preselected)
{
    static $start, $months;

    if (!$months) {

        $start = $GLOBALS['minerva_invoices']->getMinYear();

        $months = array(1  => _("January"),
                        2  => _("February"),
                        3  => _("March"),
                        4  => _("April"),
                        5  => _("May"),
                        6  => _("June"),
                        7  => _("July"),
                        8  => _("August"),
                        9  => _("September"),
                        10 => _("October"),
                        11 => _("November"),
                        12 => _("December"));
    }

    echo '<select id="' . $name . '[day]" name="' . $name . '[day]" onchange="MinervaInvoice.fixExpire(this)">' . "\n";
    $selected = date('d', $preselected);
    for ($i = 1; $i < 32; $i++) {
        echo '<option value="' . $i . '"';
        if ($selected == $i) {
            echo ' selected="selected" ';
        }
        echo '>' . $i .'</option>' . "\n";
    }
    echo '</select>' . "\n";

    echo '<select id="' . $name . '[month]" name="' . $name . '[month]" onchange="MinervaInvoice.fixExpire(this)">' . "\n";
    $selected = date('m', $preselected);
    for ($i = 1; $i < 13; $i++) {
        echo '<option value="' . $i . '"';
        if ($selected == $i) {
            echo ' selected="selected" ';
        }
        echo '>' . $months[$i] .'</option>' . "\n";
    }
    echo '</select>' . "\n";

    echo '<select id="' . $name . '[year]" name="' . $name . '[year]" onchange="MinervaInvoice.fixExpire(this)">' . "\n";
    $selected = date('Y', $preselected);
    for ($i = $start; $i <= $selected + 1; $i++) {
        echo '<option value="' . $i . '"';
        if ($selected == $i) {
            echo ' selected="selected"';
        }
        echo '>' . $i .'</option>' . "\n";
    }
    echo '</select>' . "\n";
    
    $js_code = '';
    $js_code .= '$(\'' . $name . '[year]\').setValue(data.getYear());';
    $js_code .= '$(\'' . $name . '[day]\').setValue(data.getDate());';
    $js_code .= '$(\'' . $name . '[month]\').setValue(data.getMonth() + 1);';

    Horde_Ui_JsCalendar::init();
    echo Horde::link('#', _("Select a date"), '', '', 'Horde_Calendar.open(\'' . $name . '\', null, function(data) { ' . $js_code . ' })') . 
                Horde::img('calendar.png', _("Calendar"), 'id="' . $name . '"', $GLOBALS['registry']->getImageDir('horde')) . "</a>\n";
}
