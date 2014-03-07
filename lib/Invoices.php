<?php
/**
 * Minerva Main Invoices Class
 *
 * $Horde: incubator/minerva/lib/Invoices.php,v 1.96 2009/11/09 19:58:37 duck Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Invoices {

    /**
     * Handle for the driver
     *
     * @var driver
     */
    protected $driver;

    /**
     * Creator
     */
    public function __construct()
    {
        $this->driver = Minerva_Driver::singleton();
    }

    /**
     * Get locked invoices
     */
    public function getLocked()
    {
        if (!isset($GLOBALS['conf']['lock']['driver']) ||
            $GLOBALS['conf']['lock']['driver'] == 'none') {
            return false;
        }

        $lock = Horde_Lock::singleton($GLOBALS['conf']['lock']['driver']);
        $locks = $lock->getLocks('minerva');
        if (empty($locks)) {
            return array();
        }

        return $locks;
    }

    /**
     * Check if the invoice is locked
     *
     * @param integer $id Invoice ID
     *
     * @return user who locked the invoice, false if noone
     */
    public function isLocked($id)
    {
        if (Horde_Auth::isAdmin('minerva:admin')) {
            return false;
        }

        if (!isset($GLOBALS['conf']['lock']['driver']) ||
            $GLOBALS['conf']['lock']['driver'] == 'none') {
            return false;
        }

        $lock = Horde_Lock::singleton($GLOBALS['conf']['lock']['driver']);
        $locks = $lock->getLocks('minerva', $id);

        if (empty($locks)) {
            return false;
        }

        $locks = current($locks);

        return $locks['lock_owner'] != Horde_Auth::getAuth();
    }

    /**
     * Delete lock
     *
     * @param integer $id Invoice ID
     *
     * @return   string invoice status
     */
    public function removeLock($id)
    {
        if (!isset($GLOBALS['conf']['lock']['driver']) ||
            $GLOBALS['conf']['lock']['driver'] == 'none') {
            return false;
        }

        $lock = Horde_Lock::singleton($GLOBALS['conf']['lock']['driver']);
        $locks = $lock->getLocks('minerva', $id);

        if (!empty($locks)) {
            return $lock->clearLock(key($locks));
        }
    }

    /**
     * Set the invoice lock
     *
     * @param integer $id Invoice ID
     * @param int $lifetime      Time (in seconds) for which the lock will be
     *                           considered valid.
     */
    public function setLocked($id, $lifetime = 300)
    {
        if (!isset($GLOBALS['conf']['lock']['driver']) ||
            $GLOBALS['conf']['lock']['driver'] == 'none') {
            return false;
        }

        $lock = Horde_Lock::singleton($GLOBALS['conf']['lock']['driver']);
        return $lock->setLock(Horde_Auth::getAuth(), 'minerva', $id, $lifetime, Horde_Lock::TYPE_EXCLUSIVE);
    }

    /**
     * Reset invoice lock
     *
     * @param integer $id     Invoice ID
     * @param int $extend     Extend lock this many seconds from now.
     */
    public function resetLock($id, $extend = 300)
    {
        if (!isset($GLOBALS['conf']['lock']['driver']) ||
            $GLOBALS['conf']['lock']['driver'] == 'none') {
            return false;
        }

        $lock = Horde_Lock::singleton($GLOBALS['conf']['lock']['driver']);
        $locks = $lock->getLocks('minerva', $id);

        return $lock->resetLock(key($locks), $extend);
    }

    /**
     * Check if invoice exists
     *
     * @param integer $id Invoice ID
     *
     * @return   boolean true if the invoice exits, or false if not
     */
    public function exists($id)
    {
        return (boolean)$this->driver->db->getOne('SELECT COUNT(invoice_id) FROM minerva_invoices WHERE id=?', array($id));
    }

    /**
     * Check if exists a newer invoice of this type
     *
     * @param    string  $typeid invoice id
     * @param    string  $date
     *
     * @return   boolean true if the invoice exits, or false if not
     */
    public function existsNewer($type, $date)
    {
        $query = 'SELECT COUNT(invoice_id) FROM minerva_invoices WHERE type=? AND date>?';
        return (boolean)$this->driver->db->getOne($query, array($type, $date));
    }

    /**
     * Get invoice date creation and expiration
     *
     * @param array $idd Invoice IDs
     *
     * @return array invoice dates
     */
    public function getDates($ids)
    {
        $sql = 'SELECT invoice_id, date, expire FROM minerva_invoices WHERE invoice_id IN (' . implode(',', $ids) . ')';
        return $this->driver->db->getAssoc($sql, true, array(), DB_FETCHMODE_ASSOC);
    }

    /**
     * Get invoice status
     *
     * @param    integer $id     invoice id
     *
     * @return   string invoice status
     */
    public function getStatus($id)
    {
        $status = $this->driver->db->getOne('SELECT status FROM minerva_invoices WHERE invoice_id = ?', array($id));

        if ($status instanceof PEAR_Error) {
            return $status;
        } elseif (empty($status)) {
            return PEAR::raiseError(sprintf(_("Invoice id %s don't exists."), $id));
        }

        return $status;
    }

    /**
     * Get invoice type
     *
     * @param    integer $id     invoice id
     *
     * @return   string invoice type
     */
    public function getType($id)
    {
        $type = $this->driver->db->getOne('SELECT type FROM minerva_invoices WHERE invoice_id = ?', array($id));

        if ($type instanceof PEAR_Error) {
            return $type;
        } elseif (empty($type)) {
            return PEAR::raiseError(sprintf(_("Invoice id %s don't exists."), $id));
        }

        return $type;
    }

    /**
     * Change status of invoice(s)
     *
     * @param    integer $id     invoice id
     * @param    string  $status id of status to change
     *
     * @return   true of PEAR_Error on failure
     */
    public function setStatus($id, $status)
    {
        if (!is_array($id)) {
            $id = array($id);
        }

        $result = $this->log($id, 'set_status', array($status));
        if ($result instanceof PEAR_Error) {
            return $result;
        }

        $query = 'UPDATE minerva_invoices SET status = ? WHERE invoice_id ';
        return $this->driver->write_db->query($query . ' IN (' . implode(',', $id) . ')', array($status));
    }

    /**
     * Change tag of invoice(s)
     *
     * @param    integer $id     invoice id
     * @param    integer  $tag  tag id
     *
     * @return   true of PEAR_Error on failure
     */
    public function setTag($id, $tag)
    {
        if (!is_array($id)) {
            $id = array($id);
        }

        $result = $this->log($id, 'set_tag', array($tag));
        if ($result instanceof PEAR_Error) {
            return $result;
        }

        $query = 'UPDATE minerva_invoices SET tag = ? WHERE invoice_id ';
        return $this->driver->write_db->query($query . ' IN (' . implode(',', $id) . ')', array($tag));
    }

    /**
     * Get invoice status
     *
     * @param    integer $id     invoice id
     *
     * @return   string invoice status
     */
    public function mTime($id)
    {
        if (!$this->exists($id)) {
            return PEAR::raiseError(sprintf(_("Invoice id %s don't exists."), $id));
        }

        return $this->driver->db->getOne('SELECT updated FROM minerva_invoices WHERE invoice_id = ?', array($id));
    }

    /**
     * Get invoice name
     *
     * @param    integer $id     invoice id
     *
     * @return   string invoice status
     */
    public function getName($id)
    {
        // Use write_db for selection, since directly after saving invoice,
        // the slave can be out of sync resulting as error in /invoices/post.php.
        $name = $this->driver->write_db->getOne('SELECT name FROM minerva_invoices WHERE invoice_id = ?', array($id));
        if (empty($name)) {
            return PEAR::raiseError(sprintf(_("Invoice id %s don't exists."), $id));
        }

        return $name;
    }

    /**
     * Get invoice id from its name
     *
     * @param    string $name    invoice name
     * @param    string $type    type of invoice
     *
     * @return   string invoice status
     */
    public function getId($name, $type)
    {
        $id = $this->driver->db->getOne('SELECT invoice_id FROM minerva_invoices WHERE name=? AND type=?', array($name, $type));

        if (!$id) {
            return PEAR::raiseError(sprintf(_("Invoice id %s don't exists."), $name));
        } else {
            return $name;
        }
    }

    /**
     * Guess future invoce name
     */
    public function guessName($type)
    {
        $criteria = array('invoice' => array('type' => $type));
        if ($GLOBALS['conf']['finance']['reset']) {
            $criteria['invoice']['year'] = (int)date('Y');
        }

        $invoices = $this->count($criteria);
        if ($invoices instanceof PEAR_Error) {
            return $invoices;
        }

        require_once MINERVA_BASE . '/lib/Type.php';
        $types = Minerva_TypeMapper::getTypes();

        $offset = isset($types[$type]) ? $types[$type]['offset'] : 0;
        $name = sprintf($GLOBALS['prefs']->getValue('invoice_name'),
                        ($invoices + $offset + 1));

        return $name;
    }

    /**
     * Get all invoices at once
     *
     * @param    integer $cid ids of invoices to get
     *
     * @return   array   of data
     */
    public function getAll($cid)
    {
        if (empty($cid)) {
            return PEAR::raiseError(_("No invoices are selected"));
        }

        $where = 'WHERE invoice_id IN (' . implode(',', $cid) . ')';

        // Invoices
        $query = 'SELECT invoice_id, name, date, expire, type, service, total, tax, (total-tax) as total_bare, status, place, updated, comment ' .
                 'FROM minerva_invoices ' . $where . ' ORDER BY invoice_id DESC';
        $main = $this->driver->db->getAssoc($query, true, array(), DB_FETCHMODE_ASSOC);
        if ($main instanceof PEAR_Error) {
            return $main;
        }

        // Clients
        $query = 'SELECT invoice_id, id, name, address, postal_address, vat, obligated ' .
                 'FROM minerva_clients ' . $where . ' ORDER BY invoice_id DESC';
        $clients = $this->driver->db->getAssoc($query, true, array(), DB_FETCHMODE_ASSOC);
        if ($clients instanceof PEAR_Error) {
            return $clients;
        }

        // Articles
        $query = 'SELECT invoice_id, article_id as id, article_name as name, article_price as price, '
                 . ' article_qt as qt, article_discount as discount, article_tax as tax, article_total as total'
                 . ' FROM minerva_articles ' . $where . ' ORDER BY article_order';
        $articles = $this->driver->db->getAssoc($query, true, array(), DB_FETCHMODE_ASSOC, true);
        if ($articles instanceof PEAR_Error) {
            return $articles;
        }

        // Currencies
        $query = 'SELECT invoice_id, int_curr_symbol, int_curr_symbol, exchange_rate, decimal_point, thousands_sep, currency_symbol,' .
                 'mon_decimal_point, mon_thousands_sep, positive_sign, negative_sign, int_frac_digits,' .
                 'frac_digits, p_cs_precedes, p_sep_by_space, n_cs_precedes, n_sep_by_space, p_sign_posn, n_sign_posn, ' .
                 '0 AS total FROM minerva_currencies ' . $where . ' ORDER BY invoice_id DESC';
        $currencies = $this->driver->db->getAssoc($query, true, array(), DB_FETCHMODE_ASSOC, true);
        if ($currencies instanceof PEAR_Error) {
            return $currencies;
        }

        // Taxes
        $query = 'SELECT invoice_id, id, name, value, 0 AS total ' .
                 'FROM minerva_taxes ' . $where . ' ORDER BY invoice_id DESC';
        $taxes = $this->driver->db->getAssoc($query, true, array(), DB_FETCHMODE_ASSOC, true);

        // Combine arrays
        $invoices = array();
        foreach ($cid as $id) {
            $invoices[$id] = array('invoice' => $main[$id],
                                   'client' => $clients[$id],
                                   'articles' => $articles[$id],
                                   'currencies' => array(),
                                   'taxes' => array());

            foreach ($currencies[$id] as $val) {
                $invoices[$id]['currencies'][$val['currency_symbol']] = $val;
            }

            if (!empty($taxes)) {
                foreach ($taxes[$id] as $val) {
                    $invoices[$id]['taxes'][$val['id']] = $val;
                }
            }

            $invoices[$id] = $this->format($invoices[$id]);
        }

        return Horde_String::convertCharset($invoices, $this->driver->getCharset(), Horde_Nls::getCharset());
    }

    /**
     * Get invoice data
     *
     * @param    integer $id invoice id
     *
     * @return   array   of data
     */
    public function getOne($id)
    {
        $invoice = array();
        $params = array($id);

        // Invoice
        $query = 'SELECT name, date, expire, type, service, total, tax, (total-tax) as total_bare, status, place, ' .
                 ' comment, tag FROM minerva_invoices WHERE invoice_id = ? ORDER BY invoice_id DESC';
        $invoice['invoice'] = $this->driver->db->getRow($query, $params, DB_FETCHMODE_ASSOC);

        if ($invoice['invoice'] instanceof PEAR_Error) {
            return $invoice['invoice'];
        } elseif (empty($invoice['invoice'])) {
            return PEAR::raiseError(sprintf(_("Invoice id %s don't exists."), $id));
        }

        // Client
        $query = 'SELECT id, name, address, postal_address, vat, obligated ' .
                 'FROM minerva_clients WHERE invoice_id = ? ORDER BY invoice_id DESC';
        $invoice['client'] = $this->driver->db->getRow($query, $params, DB_FETCHMODE_ASSOC);

        // Articles
        $query = 'SELECT article_id as id, article_name as name, article_price as price, article_qt as qt,'
                . ' article_unit as unit, article_discount as discount, article_tax as tax, article_total as total'
                . ' FROM minerva_articles WHERE invoice_id = ? ORDER BY article_order';
        $invoice['articles'] = $this->driver->db->getAll($query, $params, DB_FETCHMODE_ASSOC);

        // Currencies
        $invoice['currencies'] = $this->getCurrencies($id);

        // Taxes
        $query = 'SELECT id, name, value, 0 AS total FROM minerva_taxes WHERE invoice_id = ? ORDER BY invoice_id DESC';
        $invoice['taxes'] = $this->driver->db->getAssoc($query, true, $params, DB_FETCHMODE_ASSOC);

        return Horde_String::convertCharset($this->format($invoice), $this->driver->getCharset(), Horde_Nls::getCharset());
    }


    /**
     * Get invoice currency data
     *
     * @param    integer $id   Invoice id
     *
     * @return   array   of data
     */
    public function getCurrencies($id)
    {
        // Currencies
        $query = 'SELECT int_curr_symbol, int_curr_symbol, exchange_rate, decimal_point, thousands_sep, currency_symbol,' .
                 'mon_decimal_point, mon_thousands_sep, positive_sign, negative_sign, int_frac_digits,' .
                 'frac_digits, p_cs_precedes, p_sep_by_space, n_cs_precedes, n_sep_by_space, p_sign_posn, n_sign_posn, ' .
                 '0 AS total FROM minerva_currencies WHERE invoice_id = ? ORDER BY invoice_id DESC';
        return $this->driver->db->getAssoc($query, true, array($id), DB_FETCHMODE_ASSOC);
    }

    /**
     * Fromat invoice data
     *
     * @param    array   $invoice   invoice data
     *
     * @return   array   of data
     */
    public function format($invoice)
    {
        // Reformt and recalculate
        $invoice['invoice']['tax'] = $invoice['invoice']['tax'];
        $invoice['total'] = array('bare' => 0, 'discount' => 0, 'total' => 0, 'taxes' => 0);

        foreach ($invoice['articles'] as $key => $article) {
            $tax = $invoice['taxes'][$article['tax']]['value'];
            $price = $article['price'] * $article['qt'];
            $invoice['articles'][$key]['total'] = (float)$invoice['articles'][$key]['total'];
            $invoice['articles'][$key]['price'] = (float)$invoice['articles'][$key]['price'];
            $invoice['taxes'][$article['tax']]['total'] += $price * ($tax/100) * (1-$article['discount']/100);
            $invoice['total']['bare'] += $price;
            $invoice['total']['discount'] += $price * ($article['discount']/100);
            $invoice['total']['taxes'] += $price * ($tax/100) * (1-$article['discount']/100);
            $invoice['total']['total'] += $price * (1+$tax/100) * (1-$article['discount']/100);
        }

        $invoice['total']['discount'] = (float)$invoice['total']['discount'];
        $invoice['total']['bare'] = (float)$invoice['total']['bare'];
        $invoice['invoice']['without_tax'] = $invoice['total']['bare'] - $invoice['total']['discount'];
        $invoice['invoice']['total'] = (float)$invoice['invoice']['total'];
        $invoice['invoice']['tax'] = (float)$invoice['invoice']['tax'];

        foreach ($invoice['currencies'] as $key => $value) {
            $invoice['currencies'][$key]['int_curr_symbol'] = $key;
            $invoice['currencies'][$key]['total'] = $invoice['total']['total'] / $value['exchange_rate'];
        }

        return $invoice;
    }

    /**
     * Delete Invoice
     *
     * @param    integer $id     invoice id
     *
     * @return   array   of data
     */
    public function delete($id)
    {
        if (!$this->exists($id)) {
            return PEAR::raiseError(sprintf(_("Invoice id %s dosen't exists."), $id));
        }

        $params = array($id);
        $tables = array('minerva_articles',
                        'minerva_clients',
                        'minerva_currencies',
                        'minerva_invoices',
                        'minerva_recurrences',
                        'minerva_taxes');

        // 'minerva_log' is cleaned up intentionally, to allow recover of deleted invoices

        foreach ($tables as $table) {
            $result = $this->driver->write_db->query('DELETE FROM ' . $table . ' WHERE invoice_id = ?', $params);
            if ($result instanceof PEAR_Error) {
                return $result;
            }
        }

        return true;
    }

    /**
     * Prepare client data for insert
     *
     * @param    array   $client  Client Data
     *
     * @return   array of client data or PEAR_Error on failure
     */
    private function _fixClientData($client)
    {
        if (empty($client)) {
            return PEAR::raiseError(sprintf(_("No %s data found."), _("client")));
        }

        // Fetch client data if only id exists
        if (!empty($client['id']) && empty($client['name'])) {
            require_once MINERVA_BASE . '/lib/UI/Clients.php';
            $clients_ui = new Horde_UI_Clients();
            $client = $clients_ui->getOne($client['id']);
            if ($client instanceof PEAR_Error) {
                return $client;
            }
        }

        // Fix for new turba client
        if (empty($client['name']) && !empty($client['firstname'])) {
            $client['name'] = $client['firstname'] . ' ' . $client['lastname'];
        }

        // Append client postal address
        if (!isset($client['postal_address'])) {
            $client['postal_address'] = $client['name'] . "\n" . $client['address'];
        }

        // Check basic client data
        $known = array('id', 'name', 'address', 'postal_address', 'vat', 'obligated');
        $client_require = array('name', 'address', 'postal_address');
        foreach ($client as $key => $value) {
            if (empty($value) && in_array($key, $client_require)) {
                return PEAR::raiseError(_("No client data found."));
            }
            if (!in_array($key, $known)) {
                unset($client[$key]);
            }
        }

        return $client;
    }

    /**
     * Stores invoice
     *
     * @param    array   $data   invoice data
     * @param    integer $id     invoice id
     *
     * @return   Invoice ID
     */
    public function save($data, $invoice_id = 0)
    {
        // Check invoice data
        if (empty($data['invoice'])) {
            return PEAR::raiseError(_("No invoice data found."));
        }

        // Check articles
        if (empty($data['articles'])) {
            return PEAR::raiseError(_("No articles data found."));
        }

        // Check client
        $data['client'] = $this->_fixClientData($data['client']);
        if ($data['client'] instanceof PEAR_Error) {
            return $data['client'];
        }

        // Check currencies
        if (empty($data['currencies'])) {
            $data['currencies'] = Minerva::getCurrencies();
        }

        // try to get the old name, since it cannot be owerwritten and set the taxes/currencies
        $invoice_id = intval($invoice_id);
        if ($invoice_id == 0 || ($invoice_name = $this->getName($invoice_id)) instanceof PEAR_Error) {

            $invoice_id = $this->driver->write_db->nextId('minerva_invoices');
            if ($invoice_id instanceof PEAR_Error) {
                return $invoice_id;
            }
            $new_invoice = $invoice_id;
            $data['invoice']['name'] = $this->guessName($data['invoice']['type']);

            $taxes = Minerva::getTaxes();

        } else {

            $new_invoice = false;
            $invoice = $this->getOne($invoice_id);
            $data['invoice']['name'] = $invoice_name;
            if (!isset($data['currencies'])) {
                $data['currencies'] = $invoice['currencies'];
            }
            $taxes = $invoice['taxes'];
            unset($invoice);

        }

        $invoice_filter = array('invoice_id' => $invoice_id);

        // unset passive fields
        unset($data['invoice']['total_bare'],
              $data['invoice']['without_tax'],
              $data['currencies']['total_bare']);

        // Get taxes
        if (empty($data['taxes'])) {
            $data['taxes'] = Minerva::getTaxes();
        } else {
            foreach ($data['taxes'] as $key => $value) {
                unset($data['taxes'][$key]['total']);
            }
        }

        // Recalculate and delete empty articles fields and append used taxes/currencies
        $data['invoice']['total'] = 0;
        $data['invoice']['tax'] = 0;

        $article_order = 0;
        foreach ($data['articles'] as $key => $article) {

            if (empty($article['price'])) {
                if (empty($article['name'])) {
                    unset($data['articles'][$key]);
                    continue;
                }

                $article['price'] = 0;
                Horde::logMessage('INVOICE SAVE ERROR: ' . $invoice_id, __FILE__, __LINE__, PEAR_LOG_DEBUG);
            } else {
                $article['price'] = Horde_Currencies::toFloat($article['price']);
            }
            $data['articles'][$key]['order'] = $article_order++;
            $data['articles'][$key]['price'] = $article['price'];
            $data['articles'][$key]['invoice_id'] = $invoice_id;

            if (!isset($article['qt'])) {
                $data['articles'][$key]['qt'] = $article['qt'] = 1;
            } else {
                $data['articles'][$key]['qt'] = (float)trim($article['qt']);
            }

            if (!isset($article['discount'])) {
                $data['articles'][$key]['discount'] = $article['discount'] = 0;
            } else {
                $data['articles'][$key]['discount'] = (float)trim($article['discount']);
            }

            if (!isset($article['tax'])) {
                $data['articles'][$key]['tax'] = $article['tax'] = key($data['taxes']);
            } else {
                $data['articles'][$key]['tax'] = (int)$article['tax'];
            }

            if (!isset($taxes[$article['tax']])) {
                return PEAR::raiseError(_("Invalid tax id."));
            } else {
                $data['taxes'][$article['tax']] = $taxes[$article['tax']];
            }

            $data['articles'][$key]['name'] = trim(stripslashes($article['name']));
            $tax = $taxes[$article['tax']]['value'];
            $bare = $article['price'] * $article['qt'] * (1-$article['discount']/100);
            $data['articles'][$key]['total'] = $bare * (1+$tax/100);
            $data['invoice']['tax'] += $bare * ($tax/100);
            $data['invoice']['total'] += $bare * (1+$tax/100);
        }

        // Check empty articles
        if (empty($data['articles'])) {
            return PEAR::raiseError(_("No articles data found."));
        }

        // Log what we would like to do
        $result = $this->log($invoice_id, 'save', $data);
        if ($result instanceof PEAR_Error) {
            return $result;
        }

        // Covnert data to db charset
        $data = Horde_String::convertCharset($data, Horde_Nls::getCharset(), $this->driver->getCharset());

        // Block autocomit
        if ($this->driver->write_db->provides('transactions')) {
            $this->driver->write_db->autoCommit(false);
        }

        // Delete old invoice data
        if (!$new_invoice) {
            $query = 'DELETE FROM minerva_invoices WHERE invoice_id = ?';
            $result = $this->driver->write_db->query($query, $invoice_filter);
            if ($result instanceof PEAR_Error) {
                return $result;
            }
        }

        // Store Invoice data
        $data['invoice']['updated'] = $_SERVER['REQUEST_TIME'];
        $params = array_merge($invoice_filter, $data['invoice']);

        $query = 'INSERT INTO minerva_invoices ('
                        . implode(',', array_keys($params))
                        . ') VALUES (?'
                        . str_repeat(', ?', count($params)-1) . ')';

        $result = $this->_handleQuery($query, $params, $new_invoice);
        if ($result instanceof PEAR_Error) {
            return $result;
        }

        // Delete old client data
        if (!$new_invoice) {
            $query = 'DELETE FROM minerva_clients WHERE invoice_id = ?';
            $result = $this->driver->write_db->query($query, $invoice_filter);
            if ($result instanceof PEAR_Error) {
                return $result;
            }
        }

        // Store clients data
        $params = array_merge($invoice_filter, $data['client']);
        $query = 'INSERT INTO minerva_clients ('
                        . implode(',', array_keys($params))
                        . ') VALUES (?'
                        . str_repeat(', ?', count($params)-1) . ')';

        $result = $this->_handleQuery($query, $params, $new_invoice);
        if ($result instanceof PEAR_Error) {
            return $result;
        }

        // Delete old articles data
        if (!$new_invoice) {
            $query = 'DELETE FROM minerva_articles WHERE invoice_id = ?';
            $result = $this->driver->write_db->query($query, $invoice_filter);
            if ($result instanceof PEAR_Error) {
                return $result;
            }
        }

        // Construct query from the firsta article data
        $query = 'INSERT INTO minerva_articles (';
        foreach (current($data['articles']) as $key => $value) {
            if ($key == 'invoice_id') {
                $query .= $key . ', ';
            } else {
                $query .= 'article_' . $key . ', ';
            }
        }
        $query = substr($query, 0, - 2)
                        . ') VALUES (? '
                        .  str_repeat(', ?', count(current($data['articles'])) - 1) . ')';
        $sth = $this->driver->write_db->prepare($query);
        $result = $this->_handleQuery($sth, $data['articles'], $new_invoice);
        if ($result instanceof PEAR_Error) {
            return $result;
        }

        // Delete old Taxes
        if (!$new_invoice) {
            $query = 'DELETE FROM minerva_taxes WHERE invoice_id = ?';
            $result = $this->driver->write_db->query($query, $invoice_filter);
            if ($result instanceof PEAR_Error) {
                return $result;
            }
        }

        // Store Taxes
        $params = array();
        foreach ($data['taxes'] as $key => $value) {
            $params[] = array($invoice_id, $key, $value['name'], $value['value']);
        }
        $query = 'INSERT INTO minerva_taxes VALUES (?, ?, ?, ?)';
        $sth = $this->driver->write_db->prepare($query);
        $result = $this->_handleQuery($sth, $params, $new_invoice);
        if ($result instanceof PEAR_Error) {
            return $result;
        }

        // Store Currencies
        $result = $this->saveCurrencies($invoice_id, $data['currencies'], $new_invoice);
        if ($result instanceof PEAR_Error) {
            return $result;
        }

        // Try the transaction
        if ($this->driver->write_db->provides('transactions')) {
            $result = $this->driver->write_db->commit();
            if ($result instanceof PEAR_Error) {
                $this->driver->write_db->rollback();
                return $result;
            }

            // OK. Restart autocomit
            $this->driver->write_db->autoCommit(true);
        }

        Minerva::expireCache('min_year');

        return $invoice_id;
    }

    /**
     * Save invoice currencies
     *
     * @param    integer $invoice_id Invoice to save to
     * @param    array   $currencies Currencies data
     * @param    integer $new_invoice Delete old data?
     *
     * @return   True on success | PEAR_Error on failure
     */
    protected function saveCurrencies($invoice_id, $currencies, $new_invoice = false)
    {
        if (!$new_invoice) {
            $this->driver->write_db->query('DELETE FROM minerva_currencies WHERE invoice_id = ?', array($invoice_id));
        }

        foreach ($currencies as $key => $currency) {
            $currencies[$key]['invoice_id'] = $invoice_id;
            unset($currencies[$key]['updated'],
                        $currencies[$key]['created'],
                        $currencies[$key]['sort'],
                        $currencies[$key]['total']);
        }

        $query = 'INSERT INTO minerva_currencies ('
                            .  implode(',', array_keys(current($currencies)))
                            . ') VALUES (? '
                            . str_repeat(', ?', count(current($currencies)) - 1)
                            . ')';

        $sth = $this->driver->write_db->prepare($query);

        return $this->driver->write_db->executeMultiple($sth, $currencies);
    }

    /**
     * Save invoice currencies
     *
     * @param    integer $invoice_id Invoice to save to
     * @param    array   $currencies Currencies data
     * @param    integer $new_invoice Is a new invoice?
     *
     * @return   True on success | PEAR_Error on failure
     */
    protected function _handleQuery($query, $params, $new_invoice)
    {
        if (is_int($query)) {
            $result = $this->driver->write_db->executeMultiple($query, $params);
        } else {
            $result = $this->driver->write_db->query($query, $params);
        }
        if ($result instanceof PEAR_Error) {
            Horde::logMessage($result, __FILE__, __LINE__, PEAR_LOG_DEBUG);
            if ($this->driver->write_db->provides('transactions')) {
                $this->driver->write_db->rollback();
                $this->driver->write_db->autoCommit(true);
            } elseif ($new_invoice) {
                $this->delete($new_invoice);
            }
            return $result;
        } else {
            return true;
        }
    }

    /**
     * Get oldest year
     *
     * @return   string year
     */
    public function getMinYear()
    {
        if (($year = Minerva::getCache('min_year'))) {
            return $year;
        }

        $year = $this->driver->db->getOne('SELECT MIN(YEAR(date)) FROM minerva_invoices');
        $year = min($year, date('Y'));

        Minerva::setCache('min_year', $year);
        return $year;
    }

    /**
     * Store invoice history event
     *
     * @param integer $id    Invoice id
     * @param string  $type  What we are logging
     * @param array   $info  Array of attributes to save
     *
     * @return boolean  True.
     */
    public function log($id, $type, $info = array())
    {
        $data = array('id' => 0,
                        'invoice' => 0,
                        'user' => Horde_Auth::getAuth(),
                        'type' => $type,
                        'host' => $_SERVER['REMOTE_ADDR'],
                        'log' => $_SERVER['REQUEST_TIME'],
                        'data' => serialize($info));

        $sql = 'INSERT INTO minerva_log '
                . '(log_id, invoice_id, horde_uid, log_type, log_host, log_time, log_data)'
                . ' VALUES (?, ?, ?, ?, ?, ?, ?)';

        $sth = $this->driver->write_db->prepare($sql);
        $mdata = array();

        if (!is_array($id)) {

            $data['id'] = $this->driver->write_db->nextId('minerva_log');
            $data['invoice'] = $id;
            $mdata[] = $data;

        } else {

            foreach ($id as $invoice) {
                $data['id'] = $this->driver->write_db->nextId('minerva_log');
                $data['invoice'] = $invoice;
                $mdata[] = $data;
            }

        }

        return $this->driver->write_db->executeMultiple($sth, $mdata);
    }

    /**
     * Get invoice history log
     *
     * @param    integer $invoice_id Invoice to check
     *
     * @return   $result array fo history data
     */
    public function getHistory($invoice_id)
    {
        $sql = 'SELECT horde_uid, log_time, log_host, log_type FROM minerva_log WHERE invoice_id = ? ORDER BY log_time DESC';
        return $this->driver->db->getAll($sql, array($invoice_id), DB_FETCHMODE_ASSOC);
    }

    /**
     * Get clients for a selected criteria
     *
     * @param    array $criteria filter
     *
     * @return   array   of clents data
     */
    public function getClients($criteria)
    {
        $binds = $this->buildInvoicesQuery($criteria);

        $binds[0] = 'SELECT DISTINCT c.vat  AS vat, c.name as NAME, COUNT(c.vat) AS invoices, SUM(i.total) AS total ' .
                     $binds[0] . ' GROUP BY c.vat ORDER BY c.name DESC';

        return $this->driver->db->getAssoc($binds[0], false, $binds[1], DB_FETCHMODE_ASSOC);
    }

    /**
     * Get invoices grouped by clients
     *
     * @param    array $criteria filter
     *
     * @return   array of clents data
     */
    public function groupByClients($criteria)
    {
        $binds = $this->buildInvoicesQuery($criteria);

        $binds[0] = 'SELECT i.invoice_id, c.name, c.address, c.postal_address, c.vat, c.obligated  ' .
                     $binds[0] . ' GROUP BY i.invoice_id ORDER BY c.name ASC';

        $result = $this->driver->db->query($binds[0], $binds[1]);
        if ($result instanceof PEAR_Error) {
            return $result;
        }

        $clients = array();
        while ($invoice = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
            $id = $this->findClientOwner($invoice['vat'], $invoice['name'], $clients);
            if ($id !== false) {
                $clients[$id]['invoices'][] = $invoice['invoice_id'];
            } else {
                $clients[] = array_merge($invoice, array('invoices' => array($invoice['invoice_id'])));
            }
        }

        return $clients;
    }

    /**
     * Find to which client invoice belongs.
     * Needed to catch non VAT customers (like persons)
     *
     * @param string $vat Client VAT number
     * @param string $name Client name
     * @param array $clients Already processed clients
     *
     * @param mixed Id of know client, false if client is new
     */
    public function findClientOwner($vat, $name, &$clients)
    {
        if (empty($clients)) {
            return false;
        }

        $vat = trim($vat);
        $name = Horde_String::lower(trim($name));

        foreach ($clients as $id => &$data) {
            if (!isset($data['__name'])) {
                $data['__name'] = Horde_String::lower(trim($data['name']));
            }
            if (!empty($data['vat']) && $data['vat'] == $vat ||
                !empty($data['name']) && $data['__name'] == $name) {
                return $id;
            }
        }

        return false;
    }

    /**
     * Get invoices list
     *
     * @param    array $criteria filter
     *
     * @return   array   of data
     */
    public function getList($criteria)
    {
        $binds = $this->buildInvoicesQuery($criteria);

        $binds[0] = 'SELECT i.invoice_id, i.name, i.type, i.status, i.total, i.tax, (i.total-i.tax) as total_bare, ' .
                    'i.date, i.expire, c.name company, c.id company_id, cu.currency_symbol AS currency ' .
                     $binds[0] . ' GROUP BY i.invoice_id ORDER BY i.invoice_id DESC';

        if (isset($criteria['limit'])) {
            $query = $this->driver->db->modifyLimitQuery($binds[0], $criteria['limit']['from'], $criteria['limit']['count']);
            if (!($query instanceof PEAR_Error)) {
                $binds[0] = $query;
            }
        }

        return $this->driver->db->getAll($binds[0], $binds[1], DB_FETCHMODE_ASSOC);
    }

    /**
     * Get header of invoices list
     *
     * @return   array   of data
     */
    public function getListHeaders()
    {
        return array(_("Name"), _("Company"), _("Status"), _("Expire"), _("Total"), _("Tax"), _("Without tax"));
    }

    /**
     * Count invoices
     *
     * @param    array $criteria filter
     */
    public function count($criteria)
    {
        // Use write_db for selection, since directly after saving invoice,
        // the slave can be out of sync resulting as duplicate key for invoice names.
        $binds = $this->buildInvoicesQuery($criteria, true);
        $binds[0] = 'SELECT COUNT(DISTINCT(i.invoice_id)) ' . $binds[0];

        return $this->driver->write_db->getOne($binds[0], $binds[1]);
    }

    /**
     * Build binds
     *
     * @param array   $criteria filter
     * @param boolean $count build count or select query
     */
    public function buildInvoicesQuery($criteria, $count = false)
    {
        $params = array();
        $query  = 'FROM minerva_invoices i, minerva_clients c, minerva_articles a, minerva_currencies cu ' .
                  'WHERE i.invoice_id = c.invoice_id AND i.invoice_id = a.invoice_id ' .
                  'AND i.invoice_id = cu.invoice_id ';

        // invoice criteria
        if (isset($criteria['invoice'])) {
            if (isset($criteria['invoice']['name'])) {
                $query .= 'AND i.name LIKE ? ';
                $params[] = '%' . $criteria['invoice']['name'] . '%';
            }
            if (isset($criteria['invoice']['comment'])) {
                $query .= 'AND i.comment LIKE ? ';
                $params[] = '%' . $criteria['invoice']['comment'] . '%';
            }
            if (isset($criteria['invoice']['type'])) {
                $query .= 'AND i.type=? ';
                $params[] = $criteria['invoice']['type'];
            }
            if (!empty($criteria['invoice']['status'])) {
                $query .= 'AND i.status IN ("' . implode('","', $criteria['invoice']['status']) . '") ';
            }
            if (isset($criteria['invoice']['datefrom'])) {
                $query .= 'AND i.date>=? ';
                $params[] = $criteria['invoice']['datefrom'];
            }
            if (isset($criteria['invoice']['dateto'])) {
                $query .= 'AND i.date<=? ';
                $params[] = $criteria['invoice']['dateto'];
            }
            if (isset($criteria['invoice']['tag'])) {
                $query .= 'AND tag = ? ';
                $params[] = (int)$criteria['invoice']['tag'];
            }
            if (isset($criteria['invoice']['expire'])) {
                $query .= 'AND DATE_ADD(date, INTERVAL expire DAY) ';
                $query .= '< DATE_ADD(NOW(), INTERVAL ' . $criteria['invoice']['expire'] . ' DAY) ';
            }
            if (isset($criteria['invoice']['expireto'])) {
                $query .= 'AND (UNIX_TIMESTAMP(i.date)+expire*86400)<=? ';
                $params[] = strtotime($criteria['invoice']['expireto']);
            }
            if (isset($criteria['invoice']['expirefrom'])) {
                $query .= 'AND (UNIX_TIMESTAMP(i.date)+expire*86400)<=? ';
                $params[] = strtotime($criteria['invoice']['expirefrom']);
            }
            if (isset($criteria['invoice']['year'])) {
                $query .= 'AND YEAR(i.date)=? ';
                $params[] = $criteria['invoice']['year'];
            }
        }

        // client criteria
        if (isset($criteria['clients'])) {

            if (isset($criteria['clients']['name'])) {
                $query .= 'AND c.name LIKE ? ';
                $params[] = '%' . $criteria['clients']['name'] . '%';
            }

            if (isset($criteria['clients']['vat'])) {
                $query .= 'AND c.vat LIKE ? ';
                $params[] = '%' . $criteria['clients']['vat'] . '%';
            }

            if (isset($criteria['clients']['has_vat'])) {
                if ($criteria['clients']['has_vat'] == 'companies') {
                    $query .= 'AND c.vat <> ? ';
                    $params[] = '';
                } elseif ($criteria['clients']['has_vat'] == 'persons') {
                    $query .= 'AND c.vat = ? ';
                    $params[] = '';
                }
            }

        }

        if (isset($criteria['resellers'])) {
            $clients = array();
            $resellers = new Minerva_Resellers();
            foreach ($criteria['resellers'] as $reseller) {
                $clients += $resellers->getClients($reseller);
            }
            $query .= 'AND c.id IN ("' . implode('","', array_keys($clients)) . '") ';
        }

        if (isset($criteria['articles'])) {
            foreach ($criteria['articles'] as $key => $value) {
                $query .= 'AND a.article_' . $key . ' LIKE ? ';
                $params[] = '%' . $value . '%';
            }
        }

        if (isset($criteria['taxes'])) {
            $query .= 'AND a.tax IN (' . implode(',', $criteria['taxes']) . ') ';
        }

        if (isset($criteria['currency'])) {
            $query .= 'AND cu.currency IN ("' . implode('","', $criteria['currency']) . '") ';
        }

        return array($query, $params);
    }

    /**
     * Export this invoice in iCalendar format.
     *
     * @param array $data                 Event data
     * @param Horde_iCalendar &$calendar  A Horde_iCalendar object that acts as
     *                                    a container.
     *
     * @return Horde_iCalendar_vinvoice  The vEvent object for this invoice.
     */
    public function toiCalendar($invoice, &$iCal)
    {
        static $url;

        if (is_null($url)) {
            $url = Horde::applicationUrl('invoice/print.php', true, -1);
        }

        $vEvent = &Horde_iCalendar::newComponent('vevent', $iCal);
        $time = strtotime($invoice['date']) + $invoice['expire']*86400;
        $vEvent->setAttribute('DTSTART', $time);
        $vEvent->setAttribute('DTEND', $time + 1);
        $vEvent->setAttribute('DTSTAMP', $_SERVER['REQUEST_TIME']);
        $vEvent->setAttribute('UID', $invoice['invoice_id']);
        $vEvent->setAttribute('SUMMARY', _("Invoice") . ': ' . $invoice['name'] . ' ' .  $invoice['company']);
        $vEvent->setAttribute('DESCRIPTION', $invoice['currency']);
        $vEvent->setAttribute('TRANSP', 'OPAQUE');
        $vEvent->setAttribute('URL', Horde_Util::addParameter($url, array('invoice_id' => $invoice['invoice_id'], 'noprint' => 1)));

        return $vEvent;
    }
}
