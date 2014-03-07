<?php
/**
 * Wrapper araounf Image_Graph to chek if is installed and set default params.
 *
 * $Horde: incubator/minerva/lib/Graph.php,v 1.22 2009/11/09 19:58:37 duck Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Graph {

    /**
     * Construct a standar from to select statistic period
     */
    static function getDateRangeForm($total = true, $from = 'Y-01-01', $to = 'Y-m-t')
    {
        $vars = Horde_Variables::getDefaultVariables();
        $form = new Horde_Form($vars, _("Select period"), 'period');

        $enum = array(
            'total' => _("Total with tax"),
            'tax' => _("Tax only"),
            'total_bare' => _("Total without texes")
        );
        $v = &$form->addVariable(_("Tax"), 'tax', 'enum', true, false, false, array($enum));
        $v->setDefault('total');

        $dparam = array('start_year' => $GLOBALS['minerva_invoices']->getMinYear(),
                        'end_year' => date('Y'),
                        'picker' => true,
                        'format_in' => '%Y-%m-%d');

        $v = &$form->addVariable(_("Date from"), 'from', 'monthdayyear', true, false, false, $dparam);
        $v->setDefault(date($from));

        $v = &$form->addVariable(_("Date to"), 'to', 'monthdayyear', true, false, false, $dparam);
        $v->setDefault(date($to));

        return $form;
    }

    /**
     * Create image graph object
     *
     * @return Image_Graph or PEAR_Error if Image_Graph is not installed
     */
    static function getCanvas($title)
    {
        global $Plotarea, $Marker;

        require_once 'Image/Graph.php';
        $graph = &Image_Graph::factory('graph', array(700, 350));

        $font =& $graph->addNew('font', $GLOBALS['conf']['graph']['font_file']);
        $font->setSize(8);
        $graph->setFont($font);

        // create the layout
        $graph->add(
            Image_Graph::vertical(
            Image_Graph::factory('title', array($title, 12)),
                Image_Graph::vertical(
                    $Plotarea = Image_Graph::factory('plotarea', array('category', 'axis', 'horizontal')),
                    $Legend = Image_Graph::factory('legend'),
                    80
                ),
            5
            )
        );

        // add grids
        $grid =& $Plotarea->addNew('bar_grid', IMAGE_GRAPH_AXIS_Y);
        $grid->setLineColor('silver');

        // Append legend
        $Legend->setPlotarea($Plotarea);

        return $graph;
    }

    /**
     * Wrap image to be displayed by horde_cache in templates
     */
    static function render($graph)
    {
        $cache_key = microtime(true);
        $image = Horde_Util::bufferOutput(array($graph, 'done'));
        if ($image instanceof PEAR_Error) {
            return $image;
        }

        $GLOBALS['cache']->set($cache_key, serialize(array('data' => $image,
                                                           'ctype' => 'image/png')));

        $url = Horde::url($GLOBALS['registry']->get('webroot', 'horde') . '/services/cacheview.php');
        $url = Horde_Util::addParameter($url, 'cid', $cache_key, false);

        return '<img src="' . $url . '" />';

    }

    /**
     */
    static function getOutcomes($criteria, $sum_total = 'total')
    {
        /* Get Outcomes */
        $outcomes = new Minerva_OutcomeMapper();
        $default_currency = Minerva::getDefaultCurrency();
        $currencies = Minerva::getCurrencies();

        $outcome_months = array();
        $query = $outcomes->getQuery($criteria);
        foreach (new Horde_Rdo_List($query) as $invoice) {
            $key = substr($invoice->recived, 0, 7);
            if ($sum_total == 'tax') {
                $value = $invoice->total_tax;
            } elseif($sum_total == 'total_bare') {
                $value = $invoice->total - $invoice->total_tax;
            } else {
                $value = $invoice->total;
            }
            if ($default_currency != $invoice->currency) {
                $value = $value * $currencies[$invoice->currency]['exchange_rate'];
            }
            $value = round($value, 2);
            if ($value>0) {
                if (isset($outcome_months[$key])) {
                    $outcome_months[$key] += $value;
                } else {
                    $outcome_months[$key] = $value;
                }
            }
        }

        ksort($outcome_months);

        return $outcome_months;
    }

    /**
     * Get invoices list and convert it current values currency
     *
     * @param    array $criteria filter
     *
     * @return   array   of data
     */
    static function getList($criteria)
    {
        $driver = Minerva_Driver::singleton();
        $binds = $GLOBALS['minerva_invoices']->buildInvoicesQuery($criteria);

        $binds[0] = 'SELECT i.type, i.status, i.total, i.tax, (i.total-i.tax) as total_bare, ' .
                    'i.date, i.tag, c.name company, cu.currency_symbol AS currency ' .
                     $binds[0] . ' GROUP BY i.invoice_id ORDER BY i.invoice_id DESC';

        $i = 0;
        $invoices = array();
        $currecy = Minerva::getDefaultCurrency();
        $result =& $driver->db->query($binds[0], $binds[1]);
        while (DB_OK === $result->fetchInto($invoices[++$i], DB_FETCHMODE_ASSOC)) {
            if ($invoices[$i]['currency'] != $currecy) {
                if (!isset($currencies)) {
                    $currencies = Minerva::getCurrencies();
                }
                if (!isset($currencies[$invoices[$i]['currency']])) {
                    return PEAR::RaiseError(sprintf(_("Currency %s in not present in the currency list"), $invoices[$i]['currency']));
                }
                $invoices[$i]['tax'] = $invoices[$i]['tax'] * $currencies[$invoices[$i]['currency']]['exchange_rate'];
                $invoices[$i]['total'] = $invoices[$i]['total'] * $currencies[$invoices[$i]['currency']]['exchange_rate'];
                $invoices[$i]['total_bare'] = $invoices[$i]['total_bare'] * $currencies[$invoices[$i]['currency']]['exchange_rate'];
            }
        }

        unset($invoices[$i]);

        return $invoices;
    }
}
