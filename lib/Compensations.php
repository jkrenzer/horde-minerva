<?php
/**
 * Minerva Compensations Class
 *
 * $Horde: incubator/minerva/lib/Compensations.php,v 1.12 2009/07/09 08:18:14 slusarz Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Compensations extends Minerva_Invoices {

    /**
     * Stores invoice
     *
     * @param    array   $data   invoice data
     * @param    intiger $id     invoice id
     *
     * @return   Invoice ID
     */
    function save($data, $invoice_id = 0)
    {
        // Check emtpy
        if (empty($data['invoice'])) {
            return PEAR::raiseError(sprintf(_("No %s data found."), _("invoice")));
        }
        if (empty($data['articles'])) {
            return PEAR::raiseError(sprintf(_("No %s data found."), _("articles")));
        }
        if (empty($data['client'])) {
            return PEAR::raiseError(sprintf(_("No %s data found."), _("client")));
        }
        if (empty($data['articles'])) {
            return PEAR::raiseError(sprintf(_("No %s data found."), 'articles'));
        }

        // Check basic client data
        $client_require = array('name', 'address', 'postal_address');
        foreach ($data['client'] as $key => $value) {
            if (empty($value) && in_array($key, $client_require)) {
                return PEAR::raiseError(sprintf(_("No %s data found."), _("client")));
            }
        }

        // try to get the old name, since it cannot be owerwritten
        if ($invoice_id == 0 || ($invoice_name = $this->getName($invoice_id)) instanceof PEAR_Error) {
            $data['invoice']['name'] = $this->guessName($data['invoice']['type']);
            $invoice_id = $this->driver->write_db->nextId('minerva_invoices');
        } else {
            $data['invoice']['name'] = $invoice_name;
        }

        // Sovnert data to the datavas charset
        $data = Horde_String::convertCharset($data, Horde_Nls::getCharset(), $this->driver->getCharset());

        // Block autocomit
        $this->driver->write_db->autoCommit(false);

        // Delete old invoice
        $this->delete($invoice_id);

        // Add update time
        $data['invoice']['updated'] = $_SERVER['REQUEST_TIME'];

        // Store Invoice data
        $params = array_merge(array('invoice_id' => $invoice_id), $data['invoice']);
        $cols = implode(',', array_keys($params));
        $fields = str_repeat(', ?', count($params)-1);
        $query = 'INSERT INTO minerva_invoices (' . $cols . ') VALUES (?' . $fields . ')';

        $result = $this->driver->write_db->query($query, $params);
        if ($result instanceof PEAR_Error) {
            $this->driver->write_db->rollback();
            return PEAR::raiseError($result->getMessage(), null, null, null, $result->getDebugInfo());
        }

        // Store  Clients data
        $params = array_merge(array('invoice_id' => $invoice_id), $data['client']);
        $cols = implode(',', array_keys($params));
        $fields = str_repeat(', ?', count($params)-1);
        $query = 'INSERT INTO minerva_clients (' . $cols . ') VALUES (?' . $fields . ')';

        $result = $this->driver->write_db->query($query, $params);
        if ($result instanceof PEAR_Error) {
            $this->driver->write_db->rollback();
            return PEAR::raiseError($result->getMessage(), null, null, null, $result->getDebugInfo());
        }

        // Store Articles data
        $cols = implode(',', array_keys(current($data['articles'])));
        $fields = str_repeat(', ?', count(current($data['articles'])));
        $query = 'INSERT INTO minerva_articles (invoice_id, ' . $cols . ') VALUES (? ' . $fields . ')';

        foreach ($data['articles'] as $article) {
            $result = $this->driver->write_db->query($query, array_merge(array('invoice_id' => $invoice_id), $article));
            if ($result instanceof PEAR_Error) {
                $this->driver->write_db->rollback();
                return PEAR::raiseError($result->getMessage(), null, null, null, $result->getDebugInfo());
            }
        }

        // Try the transaction
        $result = $this->driver->write_db->commit();
        if ($result instanceof PEAR_Error) {
            $this->driver->write_db->rollback();
            return $result;
        }

        // OK. Restart autocomit and return invoice id
        $this->driver->write_db->autoCommit(true);

        // Log what we do
        $this->log($invoice_id, 'save');
        Minerva::expireCache('min_year');

        return $invoice_id;
    }
}