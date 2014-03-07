<?php
/**
 * Minerva Clients Class.
 *
 * $Horde: incubator/minerva/lib/Resellers.php,v 1.13 2009/02/01 21:39:05 duck Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Resellers {

    /**
     * Handle for the driver
     *
     * @var driver
     */
    private $driver;

    /**
     * Creator
     */
    public function __construct()
    {
        $this->driver = &Minerva_Driver::singleton();
    }

    /**
     * Resellers
     *
     * @param boolean   append client ids
     *
     * @return array resellers data
     */
    public function getAll()
    {
        if (empty($GLOBALS['conf']['finance']['resellers'])) {
            return array();
        }

        if ($resellers = Minerva::getCache('resellers')) {
            return $resellers;
        }

        $result = $GLOBALS['registry']->call('clients/searchClients',
                                             array('names'   => array(''),
                                                   'sources' => array($GLOBALS['conf']['finance']['resellers']),
                                                   'fields'  => array('name')));
        if ($result instanceof PEAR_Error) {
            return $result;
        }
        if (!$result) {
            return array();
        }

        $result = current($result);
        $resellers = array();
        foreach ($result as $reseller) {
            $resellers[$reseller['id']] = $reseller;
        }

        foreach ($resellers as $id => $values) {
            $resellers[$id]['clients'] = $this->getClients($id);
        }

        Minerva::setCache('resellers', $resellers);
        return $resellers;
    }

    /**
     * Get reseller client list
     *
     * @param intiger $reseller_id reseller id
     */
    public function getClients($reseller_id)
    {
        if (empty($GLOBALS['conf']['finance']['resellers'])) {
            return true;
        }

        $sql = 'SELECT client_id, percentage FROM minerva_resellers WHERE reseller_id=? ORDER BY client_id';
        return $this->driver->db->getAssoc($sql, false, array($reseller_id), DB_FETCHMODE_ASSOC);
    }

    /**
     * Delete reseller link
     *
     * @param string $reseller_id   string
     * @param string $client_id     string
     *
     * @return array resellers data
     */
    public function delete($reseller_id, $client_id)
    {
        if (empty($GLOBALS['conf']['finance']['resellers'])) {
            return true;
        }

        $sql = 'DELETE FROM minerva_resellers WHERE reseller_id=? AND client_id=?';
        $this->driver->write_db->query($sql, array($reseller_id, $client_id));

        Minerva::expireCache('resellers');
    }

    /**
     * Update reseller data
     *
     * @param array $data reseller link data
     *
     * @return array resellers data
     */
    public function save($info)
    {
        if (empty($GLOBALS['conf']['finance']['resellers'])) {
            return true;
        }

        $this->delete($info['reseller_id'], $info['client_id']);
        Minerva::expireCache('resellers');

        return $this->driver->write_db->query('INSERT INTO minerva_resellers VALUES (?, ?, ?)', $info);
    }

}
