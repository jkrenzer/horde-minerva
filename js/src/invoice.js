/**
 * Javascript code for editing invoice data
 *
 * $Horde: incubator/minerva/js/src/invoice.js,v 1.53 2009/11/09 19:58:37 duck Exp $
 *
 * @author Duck <duck@obala.net>
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 */

var MinervaInvoice = {

    /* Invoice unlock url */
    unlock: 'unlock.php',

    /* Invoice unlock url */
    selected: '',

    /**
     * Add a new article row
     */
    articleAddFields: function(r)
    {
        var id = 1;
        var used = 0;

        // get next id
        $A($('articles').rows).each(function(e) {
                if (e.id == "") {
                    return;
                }
                id = parseInt(e.id.substr(13));
                if ($F('articles_data_' + id + '_name')) {
                    used++;
                }
        });

        // We dont need to add a row as some are still unused
        if ($('articles').rows.length - 2 > used) {
            if (r) {
                return id;
            } else {
                return;
            }
        }

        // add row
        var lastRow = $('articles_row_' + id);
        if (lastRow) {

            id++;
            var newRow = new Element('TR', {id: 'articles_row_' + id});

            MinervaInvoice._articleAddField(newRow, id, 'id', 15);
            MinervaInvoice._articleAddName(newRow, id);
            MinervaInvoice._articleAddField(newRow, id, 'price', 12);
            MinervaInvoice._articleAddField(newRow, id, 'qt', 3);
            MinervaInvoice._articleAddUnits(newRow, id);
            MinervaInvoice._articleAddField(newRow, id, 'discount', 3);
            MinervaInvoice._articleAddTaxes(newRow, id);
            MinervaInvoice._articleAddField(newRow, id, 'total', 10);

            lastRow.parentNode.insertBefore(newRow, lastRow.nextSibling);
        }

        Horde.stripeAllElements.bind(Horde);

        if (r) {
            return r;
        } else {
            return;
        }
    },

    /**
     * Add a article general field
     */
    _articleAddField: function(newRow, usedFields, id, size)
    {
        var td = new Element('TD');
        newRow.appendChild(td);

        var input = new Element('INPUT', {
                type: 'text',
                name: 'articles_data_' + usedFields + '_' + id,
                id: 'articles_data_' + usedFields + '_' + id,
                size: size
            });

        td.appendChild(input);

        switch (id) {
            case 'total':
                input.disabled = true;
                td.align = 'right';
                break

            case 'qt':
                input.size = $('articles_data_1_qt').size;
                input.value = 1;
                break

            case 'discount':
                input.value = 0;
                input.maxlength = 5;
                break
        }

        input.onchange = function() { MinervaInvoice.calcPrices(); };
    },

    /**
     * Enlarge field area
     *
     * @param object e     Event
     */
    enlargeArticleName: function(f)
    {
        f.rows = f.value.split(/\n/).length;
    },

    /**
     * Add article name field
     */
    _articleAddName: function(newRow, usedFields)
    {
        var td = new Element('TD');
        newRow.appendChild(td);

        var input = new Element('textarea', {
            name: 'articles_data_' + usedFields + '_name',
            id: 'articles_data_' + usedFields + '_name',
            rows: 1,
            cols: 45,
            onkeyup: function() { MinervaInvoice.enlargeArticleName(this); },
            onfocus: function() { MinervaInvoice.enlargeArticleName(this); }
        });

        td.appendChild(input);
    },

    /**
     * Add article taxes
     */
    _articleAddTaxes: function(newRow, usedFields)
    {
        var td = new Element('TD');
        newRow.appendChild(td);

        var select = new Element('SELECT', {
            name: 'articles_data_' + usedFields + '_tax',
            id: 'articles_data_' + usedFields + '_tax'
        });

        td.appendChild(select);

        for (tax in taxes) {
            o = new Option(taxes[tax]['name'], tax);
            if (Prototype.Browser.IE) {
                select.options.add(o, tax);
            } else {
                select.options[tax] = o;
            }
            if (select.selectedIndex == 0) {
                select.selectedIndex = tax;
            }
        }

        // Unset empty options? FF bug?
        counter = 0;
        while (counter < select.options.length) {
            if (typeof(taxes[select.options[counter].value]) == 'undefined') {
                select.options[counter].remove();
            }
            counter++;
        }

        select.onchange = function() {
            MinervaInvoice.calcPrices();
        };
    },

    /**
     * Add article unti
     */
    _articleAddUnits: function(newRow, usedFields)
    {
        if (typeof(units) == 'undefined') {
            return;
        };

        // Prepare select
        var select = new Element('SELECT', {
            name: 'articles_data_' + usedFields + '_unit',
            id: 'articles_data_' + usedFields + '_unit'
        });

        // Add options
        for (unit in units) {
            select.options[unit] = new Option(units[unit], unit);
            if (select.selectedIndex == 0) {
                select.selectedIndex = unit;
            }
        }

        // Unset default option addad by browser
        select.options[0] = null;

        // add selection of needed
        var td = new Element('TD');
        newRow.appendChild(td);
        td.appendChild(select);
    },

    /**
     * Add taxes calculation fields
     */
    _taxAddFields: function(tax)
    {
        if ($('row_tax_' + tax)) {
            return true;
        }

        var lastRow = $('row_without_tax');
        if (lastRow) {

            var newRow = new Element('TR', {id: 'row_tax_' + tax});

            var td = new Element('TD');
            newRow.appendChild(td);
            td.innerHTML = taxes[tax]['name'];

            var td = new Element('TD');
            newRow.appendChild(td);

            var input = new Element('INPUT', {
                type: 'text',
                name: 'tax_' + tax,
                id: 'tax_' + tax,
                size: 33,
                value: 0,
                disabled: true
            });

            td.appendChild(input);

            lastRow.parentNode.insertBefore(newRow, lastRow.nextSibling);
        }
    },

    /**
     * Calculate invoice prices
     */
    calcPrices: function()
    {
        var total_bare = 0;
        var total_discount = 0;
        var total_total = 0;
        var decimals = 3;
        var tax = 0;

        for (i in taxes) {
            taxes[i]['total'] = 0;
        }

        $('total_bare').value = 0;
        $('total_discount').value = 0;
        $('total_without_tax').value = 0;
        $('tax').value = 0;

        for (i in currencies) {
            $('total_total_' + i).value = 0;
        }

        $A($('articles').rows).each(function(e) {

            if (e.id == "") {
                return;
            }

            prefix = 'articles_data_' + e.id.substr(13) + '_';

            // check values
            price = MinervaInvoice.parseCurrency($F(prefix + 'price'));
            qt = MinervaInvoice.parseCurrency($F(prefix + 'qt'));
            discount = MinervaInvoice.parseCurrency($F(prefix + 'discount'));

            if (isNaN(price) || isNaN(qt) || isNaN(discount)) {
                return;
            }

            // reformat to currency like
            $(prefix + 'price').value = MinervaInvoice.currencyFormat(price, defaultCurrency, true);
            $(prefix + 'qt').value = MinervaInvoice.currencyFormat(qt, defaultCurrency, true, true);
            $(prefix + 'discount').value = MinervaInvoice.currencyFormat(discount, defaultCurrency, true);
            decimals = $(prefix + 'qt').value.length > decimals ? $(prefix + 'qt').value.length  : decimals;

            // calculate others
            tax = $F(prefix + 'tax');
            if (tax == "") {
                alert("Unknown tax");
                return;
            }

            total_bare += price  * qt;
            total_discount += price  * qt  * (discount/100);
            taxes[tax]['total'] += price  * (1-discount/100)  * (taxes[tax]['value']/100)  * qt;
            $(prefix + 'total').value = MinervaInvoice.currencyFormat(price  * (1+taxes[tax]['value']/100)  * qt  * (1-discount/100));

        });

        // resize decimals
        $A($('articles').rows).each(function(e) {
            if (e.id == "") {
                return;
            }
            prefix = 'articles_data_' + e.id.substr(13) + '_';
            $(prefix + 'qt').size = decimals - 1;
        });

        tax = 0;
        for (i in taxes) {
            if (taxes[i]['total']>0) {
                tax += taxes[i]['total'];
                MinervaInvoice._taxAddFields(i);
                $('tax_' + i).value = MinervaInvoice.currencyFormat(taxes[i]['total']);
            }
        }

        $('total_bare').value = MinervaInvoice.currencyFormat(total_bare);
        $('total_discount').value = MinervaInvoice.currencyFormat(total_discount);
        $('total_without_tax').value = MinervaInvoice.currencyFormat(total_bare - total_discount);
        $('tax').value = MinervaInvoice.currencyFormat(tax);

        total_total = total_bare - total_discount + tax;
        for (i in currencies) {
            $('total_total_' + i).value = MinervaInvoice.currencyFormat(total_total, i);
        }

        MinervaInvoice.articleAddFields();
    },

    /**
     * Format currency data
     */
    currencyFormat: function(price, currency, no_symbol, fromat_only)
    {
        currency = currency || defaultCurrency;

        // Don't limit the number of digits.. ex on qt
        if (fromat_only) {
            decimal_places = 5;
        } else {
            decimal_places = Math.pow(10, currencies[currency]['frac_digits']);
            // Exchange
            price = price / currencies[currency]['exchange_rate'];
            price = Math.round(price * decimal_places) / decimal_places;
        }

        // format
        has_t = (price >= 1000);
        has_m = (price >= 1000000);
        price = price.toString();
        dotat = price.indexOf('.');

        if (dotat == -1) {
            part1 = price;
            part2 = '00';
        } else {
            part1 = price.substr(0, dotat);
            part2 = price.substr(dotat + 1);
        }

        // add zeros to the frac_digits lenght
        for (i = part2.length; i < currencies[currency]['frac_digits']; i++) {
            part2 = part2 + '0';
        }

        if (has_t) {
            plength = part1.length;
            psep = currencies[currency]['mon_thousands_sep'];
            part1 = part1.substr(0, plength-3) + psep + part1.substr(plength-3, plength);
            if (has_m) {
                part1 = part1.substr(0, plength-6) + psep + part1.substr(plength-6, plength);
            }
        }

        fromatted = part1 + currencies[currency]['mon_decimal_point'] + part2;

        // Append symbol
        if (isNaN(no_symbol)) {
            fromatted += ' ' + currencies[currency]['currency_symbol'];
        }

        return fromatted;
    },

    /**
     * Convert an string from curency to float
     */
    parseCurrency: function(num)
    {
        // replace spaces
        num = num.replace(/\s/g, '');

        // replace thousands
        pos = num.indexOf(currencies[defaultCurrency]['mon_thousands_sep']);
        if (pos>0) {
            tmp = num.substr(0, pos) + num.substr(pos + 1);
            num = tmp;
        }

        // replace decimal_point
        if (currencies[defaultCurrency]['mon_decimal_point'] != '.') {
            pos = num.indexOf(currencies[defaultCurrency]['mon_decimal_point']);
            if (pos>0) {
                tmp = num.substr(0, pos) + '.' + num.substr(pos + 1);
                num = tmp;
            }
        }

        return parseFloat(num);
    },

    /**
     * Save an invoice
     */
    save: function()
    {
        // Disable other actions till the invoice is saved
        $A(document.getElementsByTagName('a')).each(function(e) {
            e.disable = true;
            e.cursor = 'pointer';
        });

        // process
        $('postError').style.display = 'block';

        new Ajax.Updater('postError',
                         document.invoice.action,
                        {
                        asynchronous: true,
                        evalScripts: true,
                        parameters:Form.serialize(document.invoice)
                        });

        // Reanable actions
        $A(document.getElementsByTagName('a')).each(function(e) {
            e.disable = false;
            e.cursor = 'hand';
        });
    },

    /**
     * Check if the invoice is saved befor print it
     */
    checkPrint: function()
    {
        MinervaInvoice.save();
        return ($F('invoice_id') > 0);
    },

    /**
     * Sets invoice data after a fresh change
     *
     * @param intiger invoice_id     invoice UID
     * @param string  invoice_name   invoice name
     */
    addInvoiceId: function(invoice_id, invoice_name)
    {
        $('invoice_controls').style.display = 'block';
        $('invoice_id').value = invoice_id;
        $('invoice_name').value = invoice_name;

        $A(document.getElementsByTagName('a')).each(function(e) {
            link = e.href;
            if (link.indexOf('invoice_id') == -1) {
                return;
            }
            s = new String(link);
            link = s.replace(/invoice_id=0/gi, 'invoice_id=' + invoice_id);
            e.href = link;
        });
    },

    /**
     * Sets invoice data after a fresh change
     */
    createPostal: function()
    {
        if ($('client_postal_address').value == '' && $('client_name').value && $('client_address').value) {
            $('client_postal_address').value = $('client_name').value + "\n" + $('client_address').value;
        }
    },

    /**
     * Mark article as selected
     *
     * @param object e     Event
     */
    selectArticle: function(e)
    {
        MinervaInvoice.selected = Event.findElement(e,'tr');

        $A($('articles').rows).each(function(row) {
           if (row != MinervaInvoice.selected) {
                row.removeClassName("selectedRow");
            } else {
                row.addClassName("selectedRow");
            }
        });
    },

    /**
     * Delete article row
     *
     * @param object e     Event
     */
    deleteArticle: function(e)
    {
        if (MinervaInvoice.selected == '') {
            MinervaInvoice.selected = Event.findElement(e,'tr');
        }

        if (MinervaInvoice.selected == undefined) {
            return false;
        }

        MinervaInvoice.selected.remove();
        MinervaInvoice.selected = '';
        MinervaInvoice.calcPrices();
        Horde.stripeAllElements.bind(Horde);
    },

    /**
     * Duplicate article
     *
     * @param object e     Event
     */
    duplicateArticle: function(e)
    {
        if (MinervaInvoice.selected == '') {
            MinervaInvoice.selected = Event.findElement(e,'tr');
        }
        if (MinervaInvoice.selected == undefined) {
            return false;
        }

        to = MinervaInvoice.articleAddFields(true);
        from = MinervaInvoice.selected.id.substr(13);

        $('articles_data_' + to + '_id').value = $F('articles_data_' + from + '_id');
        $('articles_data_' + to + '_name').value = $F('articles_data_' + from + '_name');
        $('articles_data_' + to + '_price').value = $F('articles_data_' + from + '_price');
        $('articles_data_' + to + '_qt').value = $F('articles_data_' + from + '_qt');
        $('articles_data_' + to + '_discount').value = $F('articles_data_' + from + '_discount');
        $('articles_data_' + to + '_tax').selectedIndex = $F('articles_data_' + from + '_tax');

        MinervaInvoice.selected = '';
        MinervaInvoice.calcPrices();
        Horde.stripeAllElements.bind(Horde);
    },

    /**
     * Move article row up or down
     *
     * @param object e     Event
     */
    moveArticle: function(e, direction)
    {
        if (MinervaInvoice.selected == '') {
            MinervaInvoice.selected = Event.findElement(e,'tr');
        }
        if (MinervaInvoice.selected == undefined) {
            return false;
        }

        // Get clicked row
        parentTable = MinervaInvoice.selected.parentNode;
        clickedRowIndex = $A(parentTable.rows).indexOf(MinervaInvoice.selected);
        clickedRow = parentTable.rows[clickedRowIndex];

        // Get row to move to and check if we can move or not
        if (direction == "up") {
            if (clickedRowIndex == 0) {
                return; // This is the uppermost row and connot be moved upwards
            }
            movedRow = parentTable.rows[clickedRowIndex - 1];
        } else {
            if (clickedRowIndex == parentTable.rows.length - 2) {
                return; // This is the bottommost row and cannot be moved downwards
            }
            movedRow = parentTable.rows[clickedRowIndex + 1];
        }

        // we are always loosing text area value and tax selection
        clickedId = clickedRow.id.substr(13);
        movedId = movedRow.id.substr(13);
        clickedTax = $F('articles_data_' + clickedId + '_tax');
        movedTax = $F('articles_data_' + movedId + '_tax');
        clickedName = $F('articles_data_' + clickedId + '_name');
        movedName = $F('articles_data_' + movedId + '_name');

        // clone objects
        clickedRowClone = clickedRow.cloneNode(true);
        movedRowClone = movedRow.cloneNode(true);

        // replace objects
        parentTable.replaceChild(clickedRowClone, movedRow);
        parentTable.replaceChild(movedRowClone, clickedRow);

        // reset tax
        movedTaxField = $('articles_data_' + movedId + '_tax');
        for (i in movedTaxField.options) {
            if (movedTaxField.options[i].value == movedTax) {
                movedTaxField.selectedIndex = i;
                break;
            }
        }
        clickedTaxField = $('articles_data_' + clickedId + '_tax');
        for (i in clickedTaxField.options) {
            if (clickedTaxField.options[i].value == clickedTax) {
                clickedTaxField.selectedIndex = i;
                break;
            }
        }

        // reset name
        $('articles_data_' + clickedId + '_name').value = clickedName;
        $('articles_data_' + movedId + '_name').value = movedName;
    },

    /**
     * Check and fix expiration date
     *
     * @param object c     Changed
     */
    fixExpire: function(c)
    {
        if (!$('invoice_expire_date[day]')) {
            // the expire date selection does not exists (disabled with prefs)
            return;
        }

        // Get invoice date
        var invoiceDate = new Date();
        invoiceDate.setFullYear($F('invoice_date[year]'),
                                                    $F('invoice_date[month]') - 1,
                                                    $F('invoice_date[day]'));

        if (c.type == "text" || c.id.substr(8, 5) == "date[") {
            // We changed the expiration days or publish date
            var s = $F('invoice_expire') * 86400000 + invoiceDate.getTime();
            expireDate = new Date(s);
            $('invoice_expire_date[year]').value = expireDate.getYear();
            $('invoice_expire_date[month]').value = expireDate.getMonth() + 1;
            $('invoice_expire_date[day]').value = expireDate.getDate();
        } else {
            // We changed the expiration date
            var expireDate = new Date();
            expireDate.setFullYear($F('invoice_expire_date[year]'),
                                                        $F('invoice_expire_date[month]') - 1,
                                                        $F('invoice_expire_date[day]'));

            if (expireDate.getTime() < invoiceDate.getTime()) {
                alert('Expiration date is before invoice publish date');
            } else {
                $('invoice_expire').value = Math.round((expireDate.getTime() - invoiceDate.getTime()) / 86400000);
            }
        }
    }
}

/**
 * Unolck when exit invoiceediting page
 */
 /*
window.onbeforeunload = function()
{
    if (document.invoice['invoice_id'].value != '0') {
        new Ajax.Request(MinervaInvoice.unlock,
                        {method:'post', postBody:'invoice_id=' + $F('invoice_id')});
    }

    return true;
};
*/

/* Stuff to do immediately when page is ready. */
document.observe('dom:loaded', function() {

    // load invoice if defined
    if (typeof(reloadInvoice) != 'undefined') {
        reloadInvoice();
    };
        
    // start articles right click (content) menu
    new Proto.Menu({
        selector: '#articles',
        className: 'context firefox',
        menuItems: articlesMenuItems,
        beforeShow: MinervaInvoice.selectArticle,
        beforeHide: MinervaInvoice.selectArticle
    });
    
});

