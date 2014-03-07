<?php
/**
 * Object processing invoices selection
 *
 * $Horde: incubator/minerva/lib/Form/List.php,v 1.1 2009/11/09 20:00:04 duck Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Form_List extends Horde_Form {

    /**
     * Handle if the invoice was _cleaned
     *
     * @var string
     */
    private $_cleaned = false;

    /**
     * Creator
     */
    public function __construct()
    {
        $this->_name = 'minerva_form_invoices';
        $this->_vars = Horde_Variables::getDefaultVariables();

        $this->_checkFormType();
        $this->_addVariables();
    }

    /**
     * Return the invoice selection criteria array
     */
    public function getCriteria()
    {
        // Save data if needed
        if ($this->isSubmitted()) {
            $this->_saveVariables();
        }

        return Minerva::getCriteria();
    }

    /**
     * Render form
     */
    public function render($url)
    {
        // Set the title for the basic searach
        if (!isset($_SESSION['minerva']['form_invoices_advanced'])) {
            $this->_title = _("Basic Search");
        }

        // Fill data
        foreach ($_SESSION['minerva']['form_invoices'] as $group => $values) {
            foreach ($values as $key => $val) {
                $this->_vars->set($group . '[' .  $key. ']', $val);
            }
        }

        // Render it
        $url = Horde_Util::addParameter($url, 'type', $_SESSION['minerva']['form_invoices']['invoice']['type'], false);
        $this->renderActive(new Horde_Form_Renderer(), null, $url, 'post');
    }

    /**
     * Clean form criteria
     *
     * @return   array   of data
     */
    private function _cleanCriteria($criteria)
    {
        foreach ($criteria as $group => $members) {
            if (empty($members)) {
                unset($criteria[$group]);
                continue;
            }
            foreach ($members as $key => $value) {
                if (empty($value)) {
                    unset($criteria[$group][$key]);
                }
            }
            if (empty($criteria[$group])) {
                unset($criteria[$group]);
            }
        }

        return $criteria;
    }

    /**
     * Save search params in session
     */
    private function _checkFormType()
    {
        $submitbutton = Horde_Util::getFormData('submitbutton');
        switch ($submitbutton) {
        case _("Advanced Search"):
            $_SESSION['minerva']['form_invoices_advanced'] = true;
            break;

        case _("Basic Search"):
            unset($_SESSION['minerva']['form_invoices_advanced']);
            $this->_cleaned = true;
            break;

        case _("Clean form"):
            $type = $_SESSION['minerva']['form_invoices']['invoice']['type'];
            unset($_SESSION['minerva']['form_invoices']);
            $_SESSION['minerva']['form_invoices']['invoice']['type'] = $type;
            $this->_cleaned = true;
            break;
        }
    }

    /**
     * Save session and save in session
     */
    private function _saveVariables()
    {
        if ($this->_cleaned) {
            return true;
        }

        $this->getInfo($this->_vars, $criteria);
        $_SESSION['minerva']['form_invoices'] = $this->_cleanCriteria($criteria);
    }

    /**
     * Add variables corresponding to advanced or basic mode
     */
    private function _addVariables()
    {
        global $minerva_invoices;

        $types = Minerva::getTypes();
        $statuses = Minerva::getStatuses();
        $dparam = array('start_year' => $minerva_invoices->getMinYear(),
                        'end_year' => date('Y'),
                        'picker' => true,
                        'format_in' => '%Y-%m-%d');

        // Add type selection
        $this->addHidden('', 'invoice[type]', 'text', 'invoice');

        if (isset($_SESSION['minerva']['form_invoices_advanced'])) {

            // Invoices
            $types = Minerva::getTypes();
            $this->addVariable(_("No."), 'invoice[name]', 'text', false);
            $this->addVariable(_("Comment"), 'invoice[comment]', 'text', false);

            // Tag
            $this->addVariable(_("Tag"), 'invoice[tag]', 'enum', false, false, false, array(Minerva::getTags(), true));

            $this->addVariable(_("Date from"), 'invoice[datefrom]', 'monthdayyear', false, false, false, $dparam);
            $this->addVariable(_("Date to"), 'invoice[dateto]', 'monthdayyear', false, false, false, $dparam);
            $this->addVariable(_("Expire from"), 'invoice[expirefrom]', 'monthdayyear', false, false, false, $dparam);
            $this->addVariable(_("Expire to"), 'invoice[expireto]', 'monthdayyear', false, false, false, $dparam);
            $this->addVariable(_("Expire in days"), 'invoice[expire]', 'int', false);
            $this->addVariable(_("Status"), 'invoice[status]', 'set', false, false, false, array('enum' => $statuses));

            // Client
            $this->addVariable(_("Client name"), 'clients[name]', 'text', false);
            $this->addVariable(_("Client vat"), 'clients[vat]', 'text', false);

            $enum = array('companies' => _("Companies"), 'persons' => _("Persons"));
            $this->addVariable(_("Vat"), 'clients[has_vat]', 'enum', false, false, null, array($enum, _("Companies & Persons")));

            // Articles
            $this->addVariable(_("Item ID"), 'articles[id]', 'text', false);
            $this->addVariable(_("Item Name"), 'articles[name]', 'text', false);

            // Taxes
            $taxes = Minerva::getTaxes();
            foreach ($taxes as $key => $value) {
                $taxes[$key] = $value['name'];
            }

            $this->addVariable(_("Tax"), 'taxes', 'set', false, false, false, array('enum' => $taxes));

            // Currencies
            $currencies = Minerva::getCurrencies();
            foreach ($currencies as $key => $value) {
                $currencies[$key] = $value['currency_symbol'];
            }

            $this->addVariable(_("Currencies"), 'currency', 'set', false, false, false, array('enum' => $currencies));

            // Resllers
            $data = array();
            $resellers = new Minerva_Resellers();
            foreach ($resellers->getAll() as $key => $value) {
                $data[$key] = $value['name'];
            }

            if ($GLOBALS['conf']['finance']['resellers'] && !empty($data)) {
                $this->addVariable(_("Reseller"), 'resellers', 'set', false, false, false, array('enum' => $data));
            }

            // Search swich
            $this->setButtons(array(_("Search"), _("Clean form"), _("Basic Search")), true);

        } else {

            // Basic invocie data
            $this->addVariable(_("No."), 'invoice[name]', 'text', false);
            $this->addVariable(_("Date from"), 'invoice[datefrom]', 'monthdayyear', false, false, false, $dparam);
            $this->addVariable(_("Date to"), 'invoice[dateto]', 'monthdayyear', false, false, false, $dparam);
            $this->addVariable(_("Status"), 'invoice[status]', 'set', false, false, false, array('enum' => $statuses));

            // Clients
            $this->addVariable(_("Client name"), 'clients[name]', 'text', false);
            $this->addVariable(_("Client vat"), 'clients[vat]', 'text', false);

            // Items
            $this->addVariable(_("Item ID"), 'articles[id]', 'text', false);
            $this->addVariable(_("Item Name"), 'articles[name]', 'text', false);

            // Search swich
            $this->setButtons(array(_("Search"), _("Clean form"),  _("Advanced Search")), true);
        }
    }

}

