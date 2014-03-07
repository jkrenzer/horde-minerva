<?php
/**
 * Provides the javascript for loadin invoice data
 *
 * $Horde: incubator/minerva/templates/javascript/src/loadInvoice.js,v 1.23 2009/11/09 19:58:39 duck Exp $
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 */

// Retrive invoice data
$old_invoice = $GLOBALS['minerva_invoices']->getOne($clone_id);

// uneeded - recalculated while editing
unset($old_invoice['taxes'],
      $old_invoice['currencies'],
      $old_invoice['total']);

switch ($clone_type) {
    case Minerva::CLONE_PARTIAL:

        // remove invoice data that should never be cloned
        unset($old_invoice['invoice']['id'],
              $old_invoice['invoice']['name'],
              $old_invoice['invoice']['date'],
              $old_invoice['invoice']['type'],
              $old_invoice['invoice']['status']);

        // check what to clone
        $recurrances = new Minerva_Recurrences();
        $remove = $recurrances->getOne($clone_id, false);
        if (!is_array($remove)) {
            break;
        }
        if (!$remove['articles']) {
            unset($old_invoice['articles']);
        }
        if (!$remove['client']) {
            unset($old_invoice['client']);
        }
        break;

    case Minerva::CLONE_NORMAL:
        $old_invoice['invoice']['id'] = $clone_id;
        break;
}

$old_invoice = Horde_String::convertCharset($old_invoice, Horde_Nls::getCharset());
array_walk_recursive($old_invoice, 'toString');

/**
 * Reformat currencies data
 */
function toString(&$value, $key)
{
    if (!is_string($value)) {
        $value = (string)$value;
        $value = str_replace(',', '.', $value);
    }
}

?>
<script type="text/javascript">

function reloadInvoice() {

    // Store data for js
    var old_invoice = <?php echo Horde_Serialize::serialize($old_invoice, Horde_Serialize::JSON, Horde_Nls::getCharset()); ?>;

    // Fill form
    for (f in old_invoice) {
        if (f == 'articles') {
            count = old_invoice[f].length;
            for (var article=0; article < count; article++) {
                for (attribute in old_invoice[f][article]) {
                    name = f + '_data_' + (article+1) + '_' + attribute;
                    if (!$(name)) {
                        continue;
                    }
                    if (attribute == 'price' || attribute == 'qt' || attribute == 'discount') {
                        value = old_invoice[f][article][attribute];
                        value = value.replace(/\./g, currencies[defaultCurrency]['mon_decimal_point']);
                        $(name).value = value;
                    } else if (name == 'name') {
                        MinervaInvoice.enlargeArticleName($(name));
                    } else {
                        $(name).value = old_invoice[f][article][attribute];
                    }
                }
                MinervaInvoice.articleAddFields();
            }
            continue;
        }
        for (field in old_invoice[f]) {
            name = f + '_' + field;
            if ($(name)) {
                $(name).value = old_invoice[f][field];
            }
        }
    }

    // Set vat obligated check box
    if (old_invoice['client']['obligated'] == 1) {
        $('client_obligated').checked = true;
    } else {
        // The fill loop overrides the checkbox value if the client not an tax obligated subject
        $('client_obligated').value = 1;
    }

    // set date
    if (old_invoice['invoice']['id']) {
        $('invoice_id').value = old_invoice['invoice']['id'];
        $('invoice_date[day]').value = parseInt(old_invoice['invoice']['date'].substring(8,10), 10);
        $('invoice_date[month]').value = parseInt(old_invoice['invoice']['date'].substring(5,7), 10);
        $('invoice_date[year]').value = old_invoice['invoice']['date'].substring(0,4);
    }

    // Set expiration date if exists
    MinervaInvoice.fixExpire($('invoice_expire'));

    // recalculate prices
    MinervaInvoice.calcPrices();
}

</script>
