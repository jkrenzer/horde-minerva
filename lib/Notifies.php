<?php
/**
 * Notification of Late Payment
 *
 * $Horde: incubator/minerva/lib/Notifies.php,v 1.17 2009/01/06 17:50:58 jan Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Notifies extends Minerva_Invoices {

    /**
     * Get over due invoices
     *
     * @param int $days Number of days the invoice in over due
     *
     * @return   array of invoices grupped by clients
     */
    public function getOverdues($days = 0)
    {
        $params = array('invoice', 'pending', date('Y-m-d', $_SERVER['REQUEST_TIME'] - $days*86400));
        $sql = ' SELECT i.invoice_id, i.name, i.total, i.date, i.expire, cu.currency_symbol, '
             . ' DATE_ADD(date, INTERVAL expire DAY) AS due, c.vat, c.name as client '
             . ' FROM minerva_invoices i, minerva_clients c, minerva_currencies cu '
             . ' WHERE i.invoice_id = c.invoice_id AND i.invoice_id = cu.invoice_id '
             . ' AND i.type = ? AND i.status = ?'
             . ' AND DATE_ADD(date, INTERVAL expire DAY) < ? ORDER BY c.name';

        $invoices = $this->driver->db->getAssoc($sql, true, $params, DB_FETCHMODE_ASSOC);
        if ($invoices instanceof PEAR_Error) {
            return $invoices;
        }

        $dfm = $GLOBALS['prefs']->getValue('date_format');
        foreach ($invoices as $id => $invoice) {
            $invoices[$id]['date'] = strftime($dfm, strtotime($invoice['date']));
            $invoices[$id]['total'] = Minerva::format_price($invoice['total'], $invoice['currency_symbol']);
            $invoices[$id]['due'] = strftime($dfm, strtotime($invoice['due']))
                                  . ' (' . Minerva::expireDate($invoice['expire'], $invoice['date']) . ' ) ';
            unset($invoices[$id]['expire'], $invoices[$id]['currency_symbol']);
        }

        return $invoices;
    }

    /**
     * Get header of invoices list
     *
     * @return   array   of data
     */
    public function getListHeaders()
    {
        return array(_("Invoice"), _("Total"), _("Publish date"), _("Expire"), _("Vat"), _("Company"));
    }

    /**
     * Constrct notify data from invoices list
     *
     * @return   array   of data
     */
    public function create($ids)
    {
        if (empty($ids)) {
            return PEAR::raiseError(_("No invoices are selected"));
        }

        // Get new document ID/NAME
        $invoice_id = $this->driver->write_db->nextId('minerva_invoices');
        $invoice_name = $this->guessName('notify');

        // get invoices
        $query = 'SELECT * FROM minerva_invoices WHERE '
               . ' invoice_id IN (' . implode(',', $ids) . ') ORDER BY invoice_id DESC';
        $invoices = $this->driver->db->getAll($query, array(), DB_FETCHMODE_ASSOC);

        // Construct artiles array from invoices
        $articles = array();
        $total = $total_tax = 0;
        foreach ($invoices as $id => $invoice) {
            $total += $invoice['total'];
            $total_tax += $invoice['tax'];
            $articles[] = array('invoice_id' => $invoice_id,
                                'id' => $invoice['invoice_id'],
                                'name' => $invoice['name'],
                                'price' => $invoice['total'],
                                'qt' => 1,
                                'discount' => 0,
                                'tax' => 0,
                                'total' => $invoice['total']);
        }

        // Add notification cost
        $articles[] = array('invoice_id' => $invoice_id,
                            'id' => 'nc',
                            'name' => _("Notification costs"),
                            'price' => $GLOBALS['prefs']->getValue('notification_cost'),
                            'qt' => 1,
                            'discount' => 0,
                            'tax' => 0,
                            'total' => $GLOBALS['prefs']->getValue('notification_cost'));


        // Save invoice
        $query = 'INSERT INTO minerva_invoices (invoice_id, name, date, expire, type, total, tax, status, place, updated)'
               . ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ';
        $params = array($invoice_id, $invoice_name, date('Y-m-d'), $GLOBALS['prefs']->getValue('invoice_expire'),
                       'notify', $total, $total_tax, 'pending', $GLOBALS['prefs']->getValue('invoice_place'),
                       $_SERVER['REQUEST_TIME']);

        // Save invoice
        $result = $this->driver->write_db->query($query, $params);
        if ($result instanceof PEAR_Error) {
            return $result;
        }

        // Copy old client data from the first invoice to the first one
        $query = 'SELECT * FROM minerva_clients WHERE invoice_id = ? ORDER BY invoice_id';
        $params = $this->driver->db->getRow($query, array($ids[0]), DB_FETCHMODE_ASSOC);

        $query = 'INSERT INTO minerva_clients (invoice_id, id, name, address, postal_address, vat, obligated)'
               . ' VALUES (?, ?, ?, ?, ?, ?, ?) ';
        $params['invoice_id'] = $invoice_id;

        $result = $this->driver->write_db->query($query, $params);
        if ($result instanceof PEAR_Error) {
            return $result;
        }

        // Save articles
        $query = 'INSERT INTO minerva_articles '
                . ' (invoice_id, article_id, article_name, article_price, article_qt, article_discount, article_tax, article_total) '
               . ' VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        $sth = $this->driver->write_db->prepare($query);

        $result = $this->driver->write_db->executeMultiple($sth, $articles);
        if ($result instanceof PEAR_Error) {
            return $result;
        }

        // Copy Currencies from old invoice allows leater default currency check
        $query = 'SELECT * FROM minerva_currencies WHERE invoice_id = ?';
        $currencies = $this->driver->db->getAll($query, array($articles[0]['id']), DB_FETCHMODE_ASSOC);
        $result = $this->saveCurrencies($invoice_id, $currencies);
        if ($result instanceof PEAR_Error) {
            return $result;
        }

        return $invoice_id;
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
        $invoice['total'] = array('bare' => 0, 'discount' => 0, 'total' => 0, 'taxes' => 0);

        foreach ($invoice['articles'] as $key => $article) {
            $price = $article['price'] * $article['qt'];
            $invoice['articles'][$key]['total'] = (float)$invoice['articles'][$key]['total'];
            $invoice['articles'][$key]['price'] = (float)$invoice['articles'][$key]['price'];
            $invoice['total']['bare'] += $price;
        }
        $invoice['invoice']['without_tax'] = $invoice['total']['bare'] - $invoice['total']['discount'];
        $invoice['invoice']['total'] = (float)$invoice['invoice']['total'];
        $invoice['invoice']['tax'] = (float)$invoice['invoice']['tax'];

        return $invoice;
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
        $criteria['invoice']['type'] = 'notify';
        return parent::getList($criteria);
    }
}