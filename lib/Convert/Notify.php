<?php
/**
 * Minerva_Convert_Notify:: defines an API for creating
 * printable late payment notification document.
 *
 * $Horde: incubator/minerva/lib/Convert/Notify.php,v 1.16 2009/01/06 17:50:59 jan Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Convert_Notify extends Minerva_Convert {

    /**
     * Template we are currently using
     *
     * @var template
     */
    protected $_template = 'notify';

    /**
    * Convert invoice
    *
    * @return Pathe wehere the file was saved or PEAR_Error on failure
    */
    public function convert($id, $data)
    {
        $result = parent::_getTemplate();
        if ($result instanceof PEAR_Error) {
            return $result;
        }

        // Set defalut currency
        $d = $this->_setDefaultCurrency($data['currencies']);

        // get current default currency.
        $currencies = Minerva::getCurrencies();

        // Sum total
        $total = $sum = $nc = array();
        foreach ($data['articles'] as $id => $invoice) {
            foreach ($data['currencies'] as $currency) {
                $s = $currency['currency_symbol'];
                if (!isset($total[$s])) {
                    $total[$s] = $sum[$s] = $nc[$s] = 0;
                }
                if ($invoice['id'] == 'nc') {
                    $sum[$s] += $invoice['price'] / $currencies[$d]['exchange_rate'];
                    $nc[$s] = $invoice['price'] / $currencies[$d]['exchange_rate'];
                    unset($data['articles'][$id]);
                } else {
                    $ids[] = $invoice['id'];
                    $sum[$s] += $invoice['price'];
                    $total[$s] += $invoice['price'];
                }
            }
        }

        // Exchange
        foreach ($data['currencies'] as $key => $values) {
            $total[$key] = $total[$key] / $values['exchange_rate'];
            $sum[$key] = $sum[$key] / $values['exchange_rate'];
            $nc[$key] = $nc[$key] / $values['exchange_rate'];
        }

        // Fromat
        array_walk_recursive($data['articles'], array($this, '_formatOutputWalk'));
        array_walk($data['client'], array($this, '_formatOutputWalk'));
        array_walk($total, array($this, 'formatCurrency'));
        array_walk($sum, array($this, 'formatCurrency'));
        array_walk($nc, array($this, 'formatCurrency'));

        // Set invoice expiration dates
        $dfm = $GLOBALS['prefs']->getValue('date_format');
        $dates = $GLOBALS['minerva_invoices']->getDates($ids);
        if ($dates instanceof PEAR_Error) {
            return $dates;
        }
        foreach ($data['articles'] as $id => $invoice) {
            $published = strtotime($dates[$invoice['id']]['date']);
            $data['articles'][$id]['date'] = strftime($dfm, $published);
            $data['articles'][$id]['expire'] = strftime($dfm, $published + $dates[$invoice['id']]['expire'] * 86400);
        }

        $this->name = $data['invoice']['name'];
        $this->title = _("Late payment notification");
        $this->client = $data['client'];
        $this->invoices = $data['articles'];

        $this->total = $total;
        $this->sum = $sum;
        $this->nc = $nc;

        return $this->_saveContent();
    }

    /**
     * Applies default formatting
     *
     * @param mixed  &$value Value to format
     * @param string $key    Index key in the array
     */
    private function formatCurrency(&$value, $key)
    {
        $value = Minerva::format_price($value, $key);
    }
}
