<?php
/**
 * Minerva post Invoce
 *
 * $Horde: incubator/minerva/invoice/post.php,v 1.47 2009/12/01 12:52:46 jan Exp $
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

/**
 * from http://www.php.net/urldecode
 * rosty dot kerei at gmail dot com
 * 19-Apr-2006 06:40
 */
function _unicode_urldecode(&$url, $key)
{
   preg_match_all('/%u([[:alnum:]]{4})/', $url, $a);
   foreach ($a[1] as $uniord) {
       $dec = hexdec($uniord);
       $utf = '';

       if ($dec < 128) {
           $utf = chr($dec);
       } else if ($dec < 2048) {
           $utf = chr(192 + (($dec - ($dec % 64)) / 64));
           $utf .= chr(128 + ($dec % 64));
       } else {
           $utf = chr(224 + (($dec - ($dec % 4096)) / 4096));
           $utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64));
           $utf .= chr(128 + ($dec % 64));
       }
       $url = str_replace('%u'.$uniord, $utf, $url);
   }
   return urldecode($url);
}

/**
 * Notify users
 *
 * @param string Messaget to honor
 * @param string Type of message
 */
function _notifyUser($msg, $type)
{
    static $img_dir;

    if (is_null($img_dir)) {
        $img_dir = $GLOBALS['registry']->getImageDir('horde');
    }

    echo '<ul id="_notifyUser" onclick="this.remove(); $(\'postError\').style.display=\'none\'"><li>'
       . '<img src="' . $img_dir . '/alerts/' . $type . '.png" alt="' . $type . '" /> '
       . $msg . ' [<a href="javascript:void(0)" title="' . _("Close") . '">x</a>]</li></ul>';
}

/**
 * Create a date sting from posted form date
 *
 * @param array Date parts
 */
 function _combineDate($d)
{
    if (strlen($d['month']) == 1) {
        $d['month'] = '0' . $d['month'];
    }
    if (strlen($d['day']) == 1) {
        $d['day'] = '0' . $d['day'];
    }
    return $d['year'] . '-'  . $d['month'] . '-' . $d['day'];
}

// Decode post vars
array_walk_recursive($_POST, '_unicode_urldecode');

// Check invoice id
if (isset($_POST['invoice_id'])) {
    $invoice_id = (int)$_POST['invoice_id'];
    unset($_POST['invoice_id']);
}

// Check and combine date
$data = array();
if (isset($_POST['invoice_date'])) {
    $data['invoice']['date'] = _combineDate($_POST['invoice_date']);
    unset($_POST['invoice_date']);
}

// Check and calculate expiration date
unset($_POST['invoice_expire_date']);
if (!isset($_POST['invoice_expire'])) {

    // Get the default expiration date
    $_POST['invoice_expire'] = (int)$prefs->getValue('invoice_expire');

} elseif (is_array($_POST['invoice_expire'])) {

    $expire = strtotime(_combineDate($_POST['invoice_expire']));
    $date = strtotime($data['invoice']['date']);

    // You cannot set an expiration befor the invoice issue date
    if ($date > $expire) {
        _notifyUser(_("Expiration date is before invoice publish date"), 'error');
        exit;
    }

    // This can be valid for offers in proformas. Just notfy the user.
    if ($date == $expire) {
        _notifyUser(_("Expiration date is same that invoice publish date"), 'warning');
    }

    $_POST['invoice_expire'] = ceil(($expire - $date) / 86400);

    // Move invoice expire date to a working date
    if (Minerva::isHoliday($expire)) {
        _notifyUser(sprintf(_("The invoice expiration date %s is a holiday."),
                           Minerva::format_date($expire, false)), 'warning');
        $expire = date('Y-m-d', Minerva::nextWorkingDay($expire));

        _notifyUser(sprintf(_("The expiration date was automatically changed to %s."),
                           Minerva::format_date($expire, false)), 'warning');

        $_POST['invoice_expire'] = ceil(($expire - $date) / 86400);
    }

} else {

    // Get the integer value of expiration days
    $_POST['invoice_expire'] = (int)$_POST['invoice_expire'];

}

// Get default currency
$currencies = Minerva::getCurrencies();
$currency = $currencies[Minerva::getDefaultCurrency()];

// Set up array
foreach ($_POST as $key => $value) {
    $value = strip_tags($value);
    $value = trim($value);

    $sub = strpos($key, '_');
    $group = substr($key, 0, $sub);
    switch ($group) {
        case 'articles':
            $key = explode('_', $key);
            if ($key[3] == 'price' || $key[3] == 'discount' || $key[3] == 'qt') {
                // remove currency chars to make it look like a php number
                $value = str_replace($currency['mon_thousands_sep'], '', $value);
                $value = str_replace($currency['mon_decimal_point'], '.', $value);
            }
            $data['articles'][$key[2]][$key[3]] = $value;
            break;
        default:
            $key1 = substr($key, 0, $sub);
            $key2 = substr($key, $sub+1);
            $data[$key1][$key2] = $value;
        break;
    }
}

// Check data consistency
if (empty($data['invoice']['date'])) {
    _notifyUser(_("Date is missing"), 'error');
    exit;
}

// Check data consistency
if (empty($data['invoice']['status'])) {
    _notifyUser(_("Status is missing"), 'error');
    exit;
}

// Check data consistency
if (empty($data['invoice']['date'])) {
    _notifyUser(_("Type is missing"), 'error');
    exit;
}

// Check statuses premission
if (!Minerva::hasStatusPermission($data['invoice']['status'], Horde_Perms::EDIT, $data['invoice']['type'])) {
    _notifyUser(sprintf(_("You don't have permisson to access status %s."), Minerva::getStatusName($data['invoice']['status'])), 'error');
    exit;
}

// Check types premission
if (!Minerva::hasTypePermission($data['invoice']['type'], Horde_Perms::EDIT)) {
    _notifyUser(sprintf(_("You don't have permisson to access type %s."), Minerva::getTypeName($data['invoice']['type'])), 'error');
    exit;
}

// Check if the date is on a holiday
if (Minerva::isHoliday($data['invoice']['date'])) {

    _notifyUser(sprintf(_("The invoice date %s is a holiday."),
                       Minerva::format_date($data['invoice']['date'], false)), 'warning');

    if ($conf['recurrence']['skip_holiday']) {
        _notifyUser(_("Please select a business day."), 'error');
        exit;
    }

    $data['invoice']['date'] = date('Y-m-d', Minerva::nextWorkingDay(strtotime($data['invoice']['date'])));

    _notifyUser(sprintf(_("The invoice date was automatically changed to %s."),
                       Minerva::format_date($data['invoice']['date'], false)), 'warning');
}

// Check if a newer invoice type exits, since we must have incremetal dates
// like the invoice names are
if (!$invoice_id && $minerva_invoices->existsNewer($data['invoice']['type'], $data['invoice']['date'])) {
    $mgs = sprintf(_("A more recent %s exists."), Minerva::getTypeName($data['invoice']['type']));
    _notifyUser($mgs, 'warning');
    exit;
}

// Try to save invoice
$result = $minerva_invoices->save($data, $invoice_id);
if ($result instanceof PEAR_Error) {
    _notifyUser($result->getMessage() . ': ' . $result->getDebugInfo(), 'error');
    exit;
}

// reset id and name for new invoices
if ($invoice_id == 0) {
    $name = $minerva_invoices->getName($result);
    if ($name instanceof PEAR_Error) {
        _notifyUser($name->getMessage() . ': ' . $name->getDebugInfo(), 'error');
        exit;
    }
    echo '<script type="text/javascript">' . "\n";
    echo 'MinervaInvoice.addInvoiceId(' . (int)$result . ', "' . $name . '");';
    echo "</script>\n";

    // lock this invoice
    $minerva_invoices->setLocked($result);
} else {
    // now we can reset the lock
    $minerva_invoices->resetLock($invoice_id);
}

// tell us that is oky
_notifyUser(sprintf(_("%s successfuly saved."), Minerva::getTypeName($data['invoice']['type'])), 'success');

