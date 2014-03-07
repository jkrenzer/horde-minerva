<?php
/**
 * Minerva_Convert_Invoice:: defines an API for creating printable invoice document.
 *
 * $Horde: incubator/minerva/lib/Convert/Invoice.php,v 1.27 2009/03/20 10:11:06 duck Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Convert_Invoice extends Minerva_Convert {

    /**
     * Current invoice data
     *
     * @var invoice
     */
    private $invoice = array();

    /**
     * ID of the generated invoice
     *
     * @var invoice
     */
    private $invoice_id = 0;

    /**
     * Invoice data update timestamp
     *
     * @var invoice_mtime
     */
    private $invoice_mtime = 0;

    /**
     * Invoice article units
     *
     * @var units
     */
    public $units = array();

    /**
    * Set the current inovice data
    *
    * @param intiger    $invoice_id Invoice id
    * @param array      $data Invoice data
    * @param string     $template Template to use
    * @param string     $mime Mime type to convert to
    *
    * @return true or pear error or faulre
    */
    public function setInvoice($invoice_id, $data = array(), $template = null, $mime = 'html')
    {
        // Set up invoice data
        if (!empty($data)) {
            $this->invoice = $data;
        } else {
            $this->invoice = $GLOBALS['minerva_invoices']->getOne($invoice_id);
        }
        if ($this->invoice instanceof PEAR_Error) {
            return $this->invoice;
        }

        // Get invoice mTime
        if (!empty($data['invoice']['mtime'])) {
            $this->invoice_mtime = $data['invoice']['mtime'];
        } else {
            $this->invoice_mtime = $GLOBALS['minerva_invoices']->mTime($invoice_id);
            if ($this->invoice_mtime instanceof PEAR_Error) {
                return $this->invoice_mtime;
            }
        }

        // Set template
        if ($template !== null) {
            $this->_template = $template;
        } elseif (!empty($data['invoice']['type'])) {
            $this->_template = $data['invoice']['type'];
        } elseif (!empty($this->invoice['invoice']['type'])) {
            $this->_template = $this->invoice['invoice']['type'];
        } else {
            return PEAR::raiseError(_("No template selected."));
        }
        if (is_null($template)) {
            $this->_template = $this->invoice['invoice']['type'];
        }

        // Set defalut currency
        $this->_setDefaultCurrency($this->invoice['currencies']);

        $this->invoice_id = $invoice_id;
        $this->mime = $mime;

        return true;
    }

    /**
    * Convert invoice
    *
    * @param intiger    $invoice Invoice id
    * @param array      $data Invoice data
    * @param string     $template Template to use
    * @param string     $mime Mime type to convert to
    *
    * @return Pathe wehere the file was saved or PEAR_Error on failure
    */
    public function convert($invoice_id, $data = array(), $template = null, $mime = 'html')
    {
        $result = $this->setInvoice($invoice_id, $data, $template, $mime);
        if ($result instanceof PEAR_Error) {
            return $result;
        }

        if (($path = $this->isInvoiceCached($invoice_id)) !== false) {
            return $path;
        }

        $result = $this->save();
        if ($result instanceof PEAR_Error) {
            return $result;
        }

        if ($this->mime != 'html') {
            return $this->_convertToMime($result, $this->mime);
        }

        return $result;
    }

    /**
     * Check if the invoice should be regenerated
     *
     * @param int       $invoice_id         Invoice ID
     *
     * @return Pathe wehere the file was saved or PEAR_Error on failure
     */
    private function isInvoiceCached($invoice_id)
    {
        $path = $this->_savePath($invoice_id);
        if ($this->mime != 'html' ||
            !file_exists($path) ||
            filesize($path) < 100 ||
            $this->invoice_mtime > filemtime($path)) {
            return false;
        }

        return $path;
    }

    /**
     * Parse the invoce template
     *
     * @return  $output The parsed template or PEAR_Error on failure
     */
    private function save()
    {
        $result = parent::_getTemplate();
        if ($result instanceof PEAR_Error) {
            return $result;
        }

        $title = sprintf(_("%s no. %s"), Minerva::getTypeName($this->invoice['invoice']['type']), $this->invoice['invoice']['name']);
        $this->title = $title;

        // set invoice data
        $invoice = $this->formatOutput($this->invoice);
        foreach ($invoice as $group => $values) {
            if ($group == 'articles' || $group == 'taxes') {
                $this->$group = $values;
                continue;
            }
            if ($group == 'currencies') {
                $this->currencies = $values;
                foreach ($values as $key => $value) {
                    $key = 'sub_' . $key;
                    $this->$key = $value;
                }
                continue;
            }

            foreach ($values as $key => $value) {
                $key = $group . '_' . $key;
                $this->$key = $value;
            }
        }

        return $this->_saveContent();
    }

    /**
     * Format data getted from Minerva::getInvoice
     *
     * @param int       $invoice         Invoice data
     */
    private function formatOutput($invoice)
    {
        array_walk($invoice['invoice'], array($this, '_formatOutputWalk'));
        array_walk($invoice['client'], array($this, '_formatOutputWalk'));
        array_walk($invoice['total'], array($this, '_formatOutputWalk'));
        array_walk_recursive($invoice['taxes'], array($this, '_formatOutputWalk'));
        array_walk_recursive($invoice['articles'], array($this, '_formatOutputWalk'));

        foreach ($invoice['articles'] as $key => $value) {
            $invoice['articles'][$key]['tax'] = $invoice['taxes'][$invoice['articles'][$key]['tax']]['value'];
            $invoice['articles'][$key]['qt'] = Minerva::format_price($invoice['articles'][$key]['qt'], null, false, false);

            // Load units if we use it this invoice
            if (!empty($invoice['articles'][$key]['unit']) && empty($this->units)) {
                $this->units = Minerva::getUnits();
            }
        }

        foreach ($invoice['currencies'] as $key => $value) {
            $invoice['currencies'][$key]['total'] = Minerva::format_price($value['total'], $key);
        }

        return $invoice;
    }

    /**
     * Path where to save invoice
     *
     * @param int       $invoice_id         Invoice ID
     *
     * @return string path
     */
    protected function _savePath($invoice_id = null)
    {
        if (isset($this->invoice['invoice']['name'])) {
            $invoice_name = $this->invoice['invoice']['name'];
        } else {
            $invoice_name = $this->invoice = $GLOBALS['minerva_invoices']->getName($invoice_id);
        }

        return $this->_params['save_path'] . '/' .
               $this->_template  . '/' .
               str_replace('/', '-', $invoice_name) .
               '.html';
    }
}
