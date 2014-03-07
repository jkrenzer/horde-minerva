<?php
/**
 * The Minerva_Bank:: class and Minerva_BankMapper:: provides Rdo extension
 * used in for handling Banks.
 *
 * $Horde: incubator/minerva/lib/Bank.php,v 1.14 2009/06/12 21:25:12 duck Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Bank extends Horde_Rdo_Base {
}

class Minerva_BankMapper extends Horde_Rdo_Mapper {

    /**
     * The name of the SQL table
     *
     * @var string
     */
    protected $_table = 'minerva_banks';

    /**
     */
    public function getAdapter()
    {
        $config = Horde::getDriverConfig('storage');
        $config['adapter'] = $config['phptype'];

        return Horde_Db_Adapter::factory($config);
    }

    public function formMeta()
    {
        return Horde::loadConfiguration('bank.php', 'data', 'minerva');
    }

    /**
     * Rerutns cached acccount list
     *
     * @return array Account list
     */
    static public function getBankAccounts()
    {
        static $banks;

        if ($banks) {
            return $banks;
        }

        if (($banks = Minerva::getCache('banks'))) {
            return $banks;
        }

        $mapper = new Minerva_BankMapper();
        $query = new Horde_Rdo_Query($mapper);
        $query->sortBy('sort DESC');

        $banks = array();
        foreach (new Horde_Rdo_List($query) as $account) {
            $banks[$account->account] = $account->name . ' ' . _('no.') . ' ' . $account->account;
        }

        Minerva::setCache('banks', $banks);

        return $banks;
    }

    /**
     * Wrap to expire cache
     */
    public function create($fields)
    {
        parent::create($fields);
        Minerva::expireCache('banks');
    }

    public function update($object, $fields = null)
    {
        parent::update($object, $fields);
        Minerva::expireCache('banks');
    }

    public function delete($object)
    {
        parent::delete($object);
        Minerva::expireCache('banks');
    }
}