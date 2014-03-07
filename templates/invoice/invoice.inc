<br />
<table>
<tr valign="top">
<td>
    <table class="striped">
    <tr valign="top">
        <td colspan="2">
        <?php
        if ($invoice_id && empty($invoice['client']['id'])) {
            $add_link = Horde_Util::addParameter(Horde::applicationUrl('invoice/addcontact.php'), 'invoice_id', $invoice_id);
            echo Horde::link($add_link, _("Add client to addressbook")) . '<img src="' . $registry->getImageDir() . '/addressbook_add.png" /></a>';
        }
        ?>
        <a href="javascript:void(0)" onclick="Horde.popup({ url: '<?php echo Horde::applicationUrl('invoice/vatid.php'); ?>' })" title="<?php echo _("Find client by EU VAT") ?>">
        <img src="<?php echo $imagedir ?>/search.png" /></a>
        <a href="javascript:void(0)" onclick="Horde.popup({ url: '<?php echo Horde::applicationUrl('client_selection.php'); ?>' })" title="<?php echo _("Select client from address book") ?>">
        <img src="<?php echo $imagedir ?>/user.png" /></a>
        <strong><?php echo _("Client") ?></strong>
        </td>
    </tr>
    <tr valign="top"><td><?php echo _("Name") ?>:</td><td><input id="client_name" name="client_name" size="33" onchange="MinervaInvoice.createPostal()" /></td></tr>
    <tr valign="top"><td><?php echo _("Vat") ?>:</td><td>
            <input id="client_vat" name="client_vat" type="text" size="10" maxlength="10" />
            <?php echo _("Obligated") ?>: <input id="client_obligated" name="client_obligated" type="checkbox" value="1" /></td>
            <?php
                $clients = new Horde_UI_Clients();
                echo $clients->getAutocompleter('client_vat', 'vat');
                echo $clients->getAutocompleter('client_name', 'name');
            ?>
    </tr>
    <tr valign="top"><td><?php echo _("Address") ?>:</td><td><textarea id="client_address" name="client_address" rows="3" cols="30" onchange="MinervaInvoice.createPostal()"></textarea></td></tr>
    <tr valign="top"><td><?php echo _("Postal address") ?></td><td><textarea id="client_postal_address" name="client_postal_address" rows="3" cols="30"></textarea></td></tr>
    </table>
</td>
<td>
    <table class="striped">
    <tr valign="top">
        <td><?php echo _("No.") ?>:</td>
        <td><input type="text" size="10" id="invoice_name" name="invoice_name" value="<?php echo $minerva_invoices->guessName($type); ?>" disabled="disabled" style="font-weight: bold; text-align: left;" /></td>
    </tr>
    <tr valign="top">
        <td><?php echo _("Publish date") ?>:</td>
        <td><?php
            require MINERVA_TEMPLATES . '/invoice/date.inc';
            _dateSelect('invoice_date', $_SERVER['REQUEST_TIME']);
            ?>
            </td>
    </tr>
    <tr valign="top">
        <td><?php echo _("Place") ?>:</td>
        <td><input id="invoice_place" name="invoice_place" type="text" size="33" value="<?php echo Minerva::getInvoicePlace() ?>" /></td>
    </tr>
    <tr valign="top">
        <td><?php echo _("Date of service") ?>:</td>
        <td><input id="invoice_service" name="invoice_service" type="text" size="33" value="<?php echo strftime($GLOBALS['prefs']->getValue('date_format')) ?>" /></td>
    </tr>
    <tr valign="top">
        <td><?php echo _("Expire") ?>:</td>
        <td>
        <?php

        switch ($prefs->getValue('invoice_expire_input')) {

        case 'days':
            echo '<input id="invoice_expire" name="invoice_expire" type="text" size="3" maxlength="3"  value="' . $prefs->getValue('invoice_expire') . '" onchange="MinervaInvoice.fixExpire(this)" />' .  _("days");
        break;

        case 'date':
            $expire = Minerva::nextWorkingDay($_SERVER['REQUEST_TIME'] + $prefs->getValue('invoice_expire')*86400);
            _dateSelect('invoice_expire', $expire);
        break;

        default:
            $expire = Minerva::nextWorkingDay($_SERVER['REQUEST_TIME'] + $prefs->getValue('invoice_expire')*86400);
            echo '<input id="invoice_expire" name="invoice_expire" type="text" size="3" maxlength="3"  value="' . ceil(($expire - $_SERVER['REQUEST_TIME']) / 86400) . '" onchange="MinervaInvoice.fixExpire(this)"  /> ' .  _("days") . ' ';
            _dateSelect('invoice_expire_date', $expire);
        break;

        }
        ?>
        </td>
    </tr>
    <tr valign="top">
        <td><?php echo _("Status") ?>:</td>
        <td>
            <select id="invoice_status" name="invoice_status">
            <?php
            foreach (Minerva::getStatuses(Horde_Perms::EDIT, $type) as $key => $value) {
                    echo '<option value="' . $key . '">' . $value . '</option>' . "\n";
            } ?>
            </select>
        </td>
    </tr>
    <tr valign="top">
        <td><?php echo _("Tag") ?>:</td>
        <td>
            <select id="invoice_tag" name="invoice_tag">
            <?php
            $tags = Minerva::getTags();
            foreach ($tags as $key => $value) {
                    echo '<option value="' . $key . '">' . $value . '</option>' . "\n";
            } ?>
            </select>
        </td>
    </tr>
    </table>

</td>
</tr>
</table>

<br />

&#160;
<a href="javascript:void(0)" onClick="Horde.popup({ url: '<?php echo Horde::applicationUrl('invoice/articles.php') ?>' });"> <img src="<?php echo $imagedir ?>/search.png" title="<?php echo _("Import articles") ?>" /> <?php echo _("Import articles") ?></a>
<a href="javascript:void(0)" onClick="MinervaInvoice.articleAddFields();"> <img src="<?php echo $imagedir ?>/tick.png" title="<?php echo _("Add article space") ?>" /> <?php echo _("Add article space") ?></a>
<a href="javascript:void(0)" onClick="MinervaInvoice.calcPrices();"> <img src="<?php echo $imagedir ?>/reload.png" title="<?php echo _("Recalculate values") ?>" /> <?php echo _("Recalculate values") ?></a>

<table id="articles" class="striped">
<thead>
<tr>
<th class="leftAlign"><?php echo _("No.") ?></th>
<th class="leftAlign"><?php echo _("Name") ?></th>
<th class="leftAlign"><?php echo _("Price") ?></th>
<th class="leftAlign"><?php echo _("Qy.") ?></th>
<?php if (!empty($units)): ?>
<th class="leftAlign"><?php echo _("Unit") ?></th>
<?php endif; ?>
<th class="leftAlign"><?php echo _("Sale") ?></th>
<th class="leftAlign"><?php echo _("Tax") ?></th>
<th class="leftAlign"><?php echo _("Total") ?></th>
</tr>
</thead>
<tbody>
<tr id="articles_row_1">
<td><input type="text" id="articles_data_1_id" name="articles_data_1_id" size="15" /></td>
<td><textarea id="articles_data_1_name" name="articles_data_1_name" rows="1" cols="45" onfocus="MinervaInvoice.enlargeArticleName(this)" onkeyup="MinervaInvoice.enlargeArticleName(this)"></textarea></td>
<td><input type="text" id="articles_data_1_price" name="articles_data_1_price" size="12" onchange="MinervaInvoice.calcPrices();" /></td>
<td><input type="text" id="articles_data_1_qt" name="articles_data_1_qt" size="3" value="1" onchange="MinervaInvoice.calcPrices();" /></td>
<?php if (!empty($units)): ?>
<td>
    <select id="articles_data_1_unit" name="articles_data_1_unit">
    <?php
        foreach ($units as $unit_id => $unit_name) {
            echo '<option value="' . $unit_id . '">' . $unit_name . '</option>';
        }
    ?>
    </select>
</td>
<?php endif; ?>
<td><input type="text" id="articles_data_1_discount" name="articles_data_1_discount" value="0" size="3" maxlength="5" onchange="MinervaInvoice.calcPrices();" /></td>
<td><select id="articles_data_1_tax" name="articles_data_1_tax" onchange="MinervaInvoice.calcPrices();">
<?php
foreach ($taxes as $id => $value) {
    echo '<option value="' . $id . '">' . $value['name'] . '</option>' . "\n";
}
?>
</select>
</td>
<td align="right">
    <input type="text" id="articles_data_1_total" name="articles_data_1_total" size="15" disabled="disabled" value="0 <?php echo $currencies[$default_currency]['currency_symbol'] ?>" />
</td>
</tr>

<tr>
<td colspan="<?php echo (empty($units) ? 7 : 8)  ?>" style="background: white;">
<table id="total" class="striped" style="float: right;">
<tr valign="top" id="row_total_bare">
    <td><?php echo _("Total") ?>:</td>
    <td><input type="text" size="33" id="total_bare" name="total_bare" disabled="disabled" value="0 <?php echo $currencies[$default_currency]['currency_symbol'] ?>" /></td>
</tr>
<tr valign="top" id="row_total_discount">
    <td><?php echo _("Sale") ?>:</td>
    <td><input type="text" size="33" id="total_discount" name="total_discount" disabled="disabled" value="0 <?php echo $currencies[$default_currency]['currency_symbol'] ?>" /></td>
</tr>
<tr valign="top" id="row_without_tax">
    <td style="border-bottom: 1px solid #000000;"><?php echo _("Total without texes") ?>:</td>
    <td style="border-bottom: 1px solid #000000;"><input type="text" size="33" id="total_without_tax" name="total_without_tax" disabled="disabled" value="0 <?php echo $currencies[$default_currency]['currency_symbol'] ?>" /></td>
</tr>
<tr valign="top" id="row_tax">
    <td><?php echo _("Total tax") ?>:</td>
    <td><input type="text" size="33" id="tax" name="tax" disabled="disabled" value="0 <?php echo $currencies[$default_currency]['currency_symbol'] ?>" /></td>
</tr>
<tr valign="top" id="row_total_total">
    <td style="border-top: 1px solid #000000;"><strong><?php echo _("Total") ?></strong>:</td>
    <td style="border-top: 1px solid #000000;">
        <?php foreach ($currencies as $id => $value) { ?>
        <input type="text" size="33" id="total_total_<?php echo $id ?>" name="total_total_<?php echo $id ?>" disabled="disabled" value="0 <?php echo $value['currency_symbol'] ?>" /><br />
        <?php } ?>
    </td>
</tr>
</tbody>

</table>
