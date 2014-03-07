/**
 * Add article data from article selection popup
 *
 * $Horde: incubator/minerva/js/src/article.js,v 1.10 2009/03/03 13:08:42 duck Exp $
 */

function articleAdd(id) {

    for (var i = 0; i < items.length; i++) {
        if (items[i]['id'] == id) {
            data = items[i];
            break;
        }
    }

    nextId = opener.MinervaInvoice.articleAddFields(true);
    var fieldPrefix = 'articles_data_' + nextId + '_';

    elements = opener.document.invoice.elements;
    for (var i = 0; i < elements.length; i++) {
        if (elements[i].name.substr(0, fieldPrefix.length) == fieldPrefix &&
            data[elements[i].name.substr(fieldPrefix.length)]) {
            if (elements[i].name == 'tax') {
                alert(elements[i].value);
            }
            if (elements[i].name.substr(fieldPrefix.length) == 'id' && data['model']) {
                elements[i].value = data['model'];
            } else {
                elements[i].value = data[elements[i].name.substr(fieldPrefix.length)];
            }
        }
    }

    opener.MinervaInvoice.calcPrices();
    opener.MinervaInvoice.articleAddFields();

    lastname = 'articles_data_' + nextId + '_name';
    opener.document.getElementById(lastname).focus();

    window.close();
}

