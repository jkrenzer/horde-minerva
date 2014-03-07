<?php
/**
 * Minerva Base Class.
 *
 * $Horde: incubator/minerva/lib/Minerva.php,v 1.136 2009/12/01 12:52:44 jan Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva {

    /**
     * Clone type
     */
    const CLONE_NORMAL = 1;
    const CLONE_PARTIAL = 2;

    /**
     * Get all banks
     */
    static public function getBankAccounts()
    {
        require_once MINERVA_BASE . '/lib/Bank.php';

        return Minerva_BankMapper::getBankAccounts();
    }

    /**
     * Get transaction tags
     */
    static public function getTags()
    {
        require_once MINERVA_BASE . '/lib/Tag.php';

        return Minerva_TagMapper::getTags();
    }

    /**
     * Get article units
     */
    static public function getUnits()
    {
        require_once MINERVA_BASE . '/lib/Unit.php';

        return Minerva_UnitMapper::getUnits();
    }

    /**
     * Get types
     *
     * @param intiger $perm Type permission
     * @param boolean $full Retrive full data or only the name
     *
     * @return  array   types list
     */
    static public function getTypes($perm = Horde_Perms::SHOW, $full = false)
    {
        require_once MINERVA_BASE . '/lib/Type.php';

        $types = Minerva_TypeMapper::getTypes();

        // Check permissions only if not admin and the permissions is set
        $results = array();
        if (!Horde_Auth::isAdmin('minerva:admin') &&
            $GLOBALS['perms']->exists('minerva:types')) {
            foreach ($types as $id => $type) {
                if ($GLOBALS['perms']->hasPermission('minerva:types', Horde_Auth::getAuth(), $perm) ||
                    $GLOBALS['perms']->hasPermission('minerva:types:' . $id, Horde_Auth::getAuth(), $perm)) {
                    $results[$id] = $full ? $type : $type['name'];
                }
            }
        } else {
            foreach ($types as $id => $type) {
                $results[$id] = $full ? $type : $type['name'];
            }
        }

        return $results;
    }

    /**
     * Get Statuses
     *
     * @param intiger $perm Status permission
     * @param string  $type Status permission
     *
     * @return array statuses
     */
    static public function getStatuses($perm = Horde_Perms::SHOW, $type = null)
    {
        require_once MINERVA_BASE . '/lib/Status.php';

        $statuses = Minerva_StatusMapper::getStatuses();

        // Filter them by type
        if (!is_null($type)) {
            $types = self::getTypes($perm, true);
            if (!empty($types[$type]['statuses'])) {
                foreach ($statuses as $id => $name) {
                    if (!in_array($id, $types[$type]['statuses'])) {
                        unset($statuses[$id]);
                    }
                }
            }
        }

        // Check permissions only if not admin and the permissions is set
        if (!Horde_Auth::isAdmin('minerva:admin') &&
            $GLOBALS['perms']->exists('minerva:statuses')) {
            foreach ($statuses as $id => $name) {
                if (!$GLOBALS['perms']->hasPermission('minerva:statuses', Horde_Auth::getAuth(), $perm) &&
                    !$GLOBALS['perms']->hasPermission('minerva:statuses:' . $id, Horde_Auth::getAuth(), $perm)) {
                    unset($statuses[$id]);
                }
            }
        }

        reset($statuses);

        return $statuses;
    }

    /**
     * Check if the current user the requested permission
     *
     * @param string  $status   Status to check
     * @param intiger $perm     Permission level
     * @param string  $type     Invoice Type
     *
     * @return boolean
     */
    static public function hasStatusPermission($status, $perm, $type)
    {
        if (Horde_Auth::isAdmin('minerva:admin') || !$GLOBALS['perms']->exists('minerva:statuses')) {
            return true;
        }

        $statuses = self::getStatuses(Horde_Perms::SHOW, $type);

        if (is_null($status)) {
            return !empty($statuses);
        } else {
            return isset($statuses[$status]);
        }
    }

    /**
     * Check if the current user the requested permission
     *
     * @param string  $status   Type to check
     * @param intiger $perm     permission level
     *
     * @return boolean
     */
    static public function hasTypePermission($type = null, $perm = Horde_Perms::SHOW)
    {
        if (Horde_Auth::isAdmin('minerva:admin') || !$GLOBALS['perms']->exists('minerva:types')) {
            return true;
        }

        $types = self::getTypes($perm);

        return isset($types[$type]) || (is_null($type) && !empty($types));
    }

    /**
     * Check if the current user the requested permission
     *
     * @param intiger $perm     Permission level
     *
     * @return boolean
     */
    static public function hasOutcomePermission($perm)
    {
        if (Horde_Auth::isAdmin('minerva:admin') ||
            !$GLOBALS['perms']->exists('minerva:outcome') ||
            $GLOBALS['perms']->hasPermission('minerva:outcome', Horde_Auth::getAuth(), $perm)) {
            return true;
        }

        return false;
    }

    /**
     * Check if the current user the requested permission
     *
     * @param intiger $perm     Permission level
     *
     * @return boolean
     */
    static public function hasNotifiesPermission($perm)
    {
        if (Horde_Auth::isAdmin('minerva:admin') ||
            !$GLOBALS['perms']->exists('minerva:notifies') ||
            $GLOBALS['perms']->hasPermission('minerva:notifies', Horde_Auth::getAuth(), $perm)) {
            return true;
        }

        return false;
    }

    /**
     * Check if the current user has the requested permission
     *
     * @param intiger $perm     Permission level
     *
     * @return boolean
     */
    static public function hasRecurrencePermission($perm)
    {
        if (Horde_Auth::isAdmin('minerva:admin') ||
            !$GLOBALS['perms']->exists('minerva:recurrence') ||
            $GLOBALS['perms']->hasPermission('minerva:outcome', Horde_Auth::getAuth(), $perm)) {
            return true;
        }

        return false;
    }

    /**
     * Check if the current user is an admin
     *
     * @return boolean
     */
    static public function isAdmin()
    {
        if (Horde_Auth::isAdmin('minerva:admin') || !$GLOBALS['perms']->exists('minerva:admin')) {
            return true;
        }

        return false;
    }

    /**
     * Return status name
     *
     * @param string $status Status to check
     *
     * @return name
     */
    static public function getStatusName($status)
    {
        $statuses = self::getStatuses(Horde_Perms::SHOW);

        return $statuses[$status];
    }

    /**
     * Return type name
     *
     * @param   string $type Status to check
     *
     * @return name
     */
    static public function getTypeName($type)
    {
        $types = self::getTypes();
        return $types[$type];
    }

    /**
     * Calculates expire days
     *
     * @param string $days  starting date
     * @param string $to    ending date
     *
     * @return intiger days to go
     */
    static public function expireDate($days, $from = false)
    {
        $from = ($from) ? strtotime($from) : $_SERVER['REQUEST_TIME'];
        $from = $from + $days * 86400;
        $to = $_SERVER['REQUEST_TIME'];
        $diff = ($from-$to) / 86400;

        return ceil($diff);
    }

    /**
     * Get Taxes
     *
     * @return array taxes data
     */
    static public function getTaxes()
    {
        return Horde_TaxesMapper::getTaxes();
    }

    /**
     * Get Currencies
     *
     * @return array currencies data
     */
    static public function getCurrencies()
    {
        static $data;

        if ($data) {
            return $data;
        }

        $data = Horde_CurrenciesMapper::getCurrencies();
        if (empty($GLOBALS['conf']['finance']['currencies'])) {
            return $data;
        }

        foreach ($data as $currency) {
            if (!isset($_SESSION['minerva']['defaultCurrency']) && (int)$currency['exchange_rate'] == 1) {
                $_SESSION['minerva']['defaultCurrency'] = $currency['int_curr_symbol'];
            }
            if (!in_array($currency['int_curr_symbol'], $GLOBALS['conf']['finance']['currencies'])) {
                unset($data[$currency['int_curr_symbol']]);
            }
        }

        return $data;
    }

    /**
     * Get the default currency
     *
     * @return string   currency code
     */
    static public function getDefaultCurrency()
    {
        if (isset($_SESSION['minerva']['defaultCurrency'])) {
            return $_SESSION['minerva']['defaultCurrency'];
        }

        $currencies = self::getCurrencies();
        if (count($currencies) == 1 && !isset($_SESSION['minerva']['defaultCurrency'])) {
            $_SESSION['minerva']['defaultCurrency'] = key($currencies);
            return $_SESSION['minerva']['defaultCurrency'];
        }

        foreach ($currencies as $currency) {
            if ((int)$currency['exchange_rate'] == 1) {
                $_SESSION['minerva']['defaultCurrency'] = $currency['int_curr_symbol'];
                return $_SESSION['minerva']['defaultCurrency'];
            }
        }
    }

    /**
     * Return the invoice selection criteria array
     */
    static public function getCriteria()
    {
        // Set the defaults, if empty or the invoice type changed
        $type = Horde_Util::getFormData('type');
        if ($type == null) {
            if (isset($_SESSION['minerva']['form_invoices'])) {
                $type = $_SESSION['minerva']['form_invoices']['invoice']['type'];
            } else {
                $type = 'invoice';
            }
        }

        if (!isset($_SESSION['minerva']['form_invoices']) ||
            ($type != 'search' && $type != $_SESSION['minerva']['form_invoices']['invoice']['type'])) {
            $types = self::getTypes();
            if (!isset($types[$type])) {
                $type = key($types);
            }
            $defaults['invoice']['type'] = $type;
            $defaults['invoice']['status'] = array('pending');
            $_SESSION['minerva']['form_invoices'] = $defaults;
        }

        return $_SESSION['minerva']['form_invoices'];
    }

    /**
     * Return the invoice selection criteria array
     */
    static public function getList($criteria = array())
    {
        if (empty($criteria)) {
            $criteria = self::getCriteria();
        }

        $list = $GLOBALS['minerva_invoices']->getList($criteria);
        if ($list instanceof PEAR_Error) {
            return $list;
        }

        $statuses = self::getStatuses();
        $dfm = $GLOBALS['prefs']->getValue('date_format');
        foreach ($list as $id => $invoice) {
            $invoice_id = $list[$id]['invoice_id'];
            $values[$invoice['invoice_id']] = array(
              //  'invoice_id' => $invoice['invoice_id'],
                'name' => $invoice['name'],
                'company' => $invoice['company'],
                'status' => $statuses[$invoice['status']],
                'date' => strftime($dfm, strtotime($invoice['date'])) . ' (' . self::expireDate($invoice['expire'], $invoice['date']) . ')',
                'total' => self::format_price($invoice['total'], $invoice['currency']),
                'tax' => self::format_price($invoice['tax'], $invoice['currency']),
                'total_bare' => self::format_price($invoice['total_bare'], $invoice['currency']));
        }

        return $values;
    }

    /**
     * Fomates time accoring to user prefs
     *
     * @param int $timestamp message timestamp
     *
     * @return string $date fromatted date
     */
    static public function format_date($timestamp, $time = true)
    {
        if (is_object($timestamp)) {
            $timestamp = $timestamp->timestamp();
        } elseif (intval($timestamp)<1000000000) {
            $timestamp = strtotime($timestamp);
        }

        $formatted = strftime($GLOBALS['prefs']->getValue('date_format'), $timestamp);
        if ($time) {
            $formatted .= ' ' . date($GLOBALS['prefs']->getValue('twentyFour') ? 'G:i' : 'g:ia', $timestamp);
        }

        return $formatted;
    }

    /**
     * Format value accoring to currency
     *
     * @param float   $price  The price value to format.
     * @param array   $currency   Currency format parameters.
     * @param boolean $symbol False if don't add the currecy simbol.
     * @param boolean $decimals  Respect currency decimal places
     *
     * @return Currency formatted price string.
     */
    static public function format_price($price, $currency = null, $symbol = true, $decimals = true)
    {
        static $currencies;

        if (is_array($currency)) {
            return Horde_Currencies::formatPrice($price, $currency, $symbol, $decimals);
        }

        if (is_null($currencies)) {
            $currencies = self::getCurrencies();
        }

        if ($currency == null) {
            $currency = self::getDefaultCurrency();
        }

        return Horde_Currencies::formatPrice($price, isset($currencies[$currency]) ? $currencies[$currency] : null, $symbol, $decimals);
    }

    /**
     * Send email with attachments
     *
     * @param string $from       From address
     * @param string $to         To address
     * @param string $subject    Subject of message
     * @param string $body       Body of message
     * @param array  $attaches   Path of file to attach
     *
     * @return true on succes, PEAR_Error on failure
     */
    static public function sendMail($from, $to, $subject, $body, $attaches = array())
    {
        $mail = new Horde_Mime_Mail(array('subject' => $subject,
                                            'body' => $body,
                                            'to' => $to,
                                            'from' => $from,
                                            'charset' => Horde_Nls::getCharset()));
        $mail->addHeader('User-Agent', 'Minerva ' . $GLOBALS['registry']->getVersion());

        if ($GLOBALS['conf']['finance']['send_copy']) {
            $mail->addHeader('Cc', self::getFromAddress());
        }

        foreach ($attaches as $file) {
            if (file_exists($file)) {
                $mail->addAttachment($file, null, null, Horde_Nls::getCharset());
            }
        }

        $mail->send(Horde::getMailerConfig());
    }

    /**
     * Check if the current date is a holiday.
     *
     * @param string  $date to check
     *
     * @return boolean if the date is a holiday or not
     */
    static public function isHoliday($date)
    {
        static $holidays;

        if (!is_array($holidays)) {
            require_once MINERVA_BASE . '/config/holidays.php';
        }

        $curr = new Horde_Date($date . ' 00:00:00');
        if (in_array($curr->dayOfWeek(), $holidays[0]) ||
            (isset($holidays[$curr->month]) && in_array($curr->mday, $holidays[$curr->month]))) {
            return true;
        }

        return false;
    }

    /**
     * Return a next working day from a given date.
     *
     * @param int  $date to check
     *
     * @return int date timestamp
     */
    static public function nextWorkingDay($date)
    {
        $i = 0;
        while (self::isHoliday($date)) {
            $date += ($i++ * 86400);
        }

        return $date;
    }

    /**
     * Get from address
     */
    static public function getFromAddress()
    {
        if (isset($_SESSION['minerva']['from_addr'])) {
            return $_SESSION['minerva']['from_addr'];
        }

        // First try if the user has a special from address
        if ($GLOBALS['prefs']->getValue('from_addr')) {
            $_SESSION['minerva']['from_addr'] = $GLOBALS['prefs']->getValue('from_addr');
            return $_SESSION['minerva']['from_addr'];
        }

        // If not use the company naddress
        $company = self::getCompany();
        if ($company->email) {
            $_SESSION['minerva']['from_addr'] = $company->email;
            return $company->email;
        }

        // ERROR, no email
        return _("ERROR: NO EMAIL SET!");
    }

    /**
     * Get the place where the invoice is published
     */
    static public function getInvoicePlace()
    {
        if (isset($_SESSION['minerva']['invoice_place'])) {
            return $_SESSION['minerva']['invoice_place'];
        }

        // First try if the user has a special palce defined
        if ($GLOBALS['prefs']->getValue('invoice_place')) {
            $_SESSION['minerva']['invoice_place'] = $GLOBALS['prefs']->getValue('invoice_place');
            return $_SESSION['minerva']['invoice_place'];
        }

        // If not use the company address
        $company = self::getCompany();
        if ($company->city) {
            $_SESSION['minerva']['invoice_place'] = $company->city;
            return $company->city;
        }

        return '';
    }

    /**
     * Get company datails
     *
     * @return array company datails
     */
    static public function getCompany()
    {
        static $company;

        if ($company === null) {
            require_once HORDE_BASE . '/incubator/Horde_Company/Horde_Company.php';
            $mapper = new Horde_CompanyMapper();
            $company = $mapper->findOne();
            if ($company->capital_amount > 0) {
                $company->capital = self::format_price($company->capital_amount, $company->capital_currency);
            }
        }

        return $company;
    }
    
    /**
     * Get citeria filter
     *
     * @return array    Filter data
     */
    static public function getInvoiceCriteria()
    {
        $form = new Minerva_Form_List();
        return $form->getCriteria();
    }

    /**
     * Retreive cache
     *
     * @param  string $key Cache key
     *
     * @return mixed    data or false cache key not exists
     */
    static public function getCache($key)
    {
        $data = $GLOBALS['cache']->get("minerva_$key", 1800);
        if ($data) {
            return unserialize($data);
        } else {
            return false;
        }
    }

    /**
     * Store cache
     *
     * @param  string $key  Cache key
     * @param  mixed  $data Data to save
     *
     * @return boolean if the cache was saved
     */
    static public function setCache($key, $data)
    {
        return $GLOBALS['cache']->set("minerva_$key", serialize($data));
    }

    /**
     * Delete cache
     *
     * @param  string $key  Cache key
     *
     * @return boolean if the cache was expired
     */
    static public function expireCache($key)
    {
        return $GLOBALS['cache']->expire("minerva_$key");
    }

    /**
     * Build Minerva's list of menu items.
     *
     * @param string    $returnType Return type of the menu output
     */
    static public function getMenu($returnType = 'object')
    {
        $img = $GLOBALS['registry']->getImageDir('horde');
        $menu = new Horde_Menu(Horde_Menu::MASK_ALL);

        $menu->add(Horde::applicationUrl('list/list.php'), _("Income"), 'lhand.png', $img);
        $menu->add(Horde::applicationUrl('outcome/topay.php'), _("Outcome"), 'rhand.png', $img);
        $menu->add(Horde::applicationUrl('recurrence/list.php'), _("Recurrence"), 'reload.png', $img);

        $menu->add(Horde::applicationUrl('statistics/statistics.php'), _("Statistics"), 'organizing.png', $img);

        $url = Horde::applicationUrl('invoice/invoice.php');
        foreach (self::getTypes(Horde_Perms::EDIT) as $id => $name) {
            $menu->add(Horde_Util::addParameter($url, 'type', $id), $name, 'edit.png', $img, '',  null, '__noselection');
        }

        if ($GLOBALS['conf']['finance']['payment_notifies']) {
            $menu->add(Horde::applicationUrl('notifies/list.php'), _("Notifies"), 'problem.png', $img);
        }

        if (self::isAdmin()) {
            $menu->add(Horde::applicationUrl('admin/locked.php'), _("Admin"), 'administration.png', $img);
        }

        if ($returnType == 'object') {
            return $menu;
        } else {
            return $menu->render();
        }
    }
}
