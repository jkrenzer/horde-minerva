<tr style="text-align: right;" id="<?php echo $invoice['invoice_id'] ?>">
<td>
<?php
echo Horde::link(Horde_Util::addParameter($print_url, 'invoice_id', $invoice['invoice_id']), _("Print"), '', '_print');
echo '<img src="' . $imagedir . '/print.png" title="' . _("Print") . '" /></a> ';

echo Horde::link(Horde_Util::addParameter($invoice_url, 'invoice_id', $invoice['invoice_id']), _("Edit"));
echo '<img src="' . $imagedir . '/edit.png" title="' . _("Edit") . '" /></a> ';

echo Horde::link(Horde_Util::addParameter($invoice_url, 'clone_id', $invoice['invoice_id']), sprintf(_("Clone to %s"), _("Invoice")));
echo '<img src="' . $imagedir . '/office.png" title="' . sprintf(_("Clone to %s"), _("Invoice")) . '" /></a> ';

?>
</td>
<td>
<?php echo Horde::link(Horde_Util::addParameter($invoice_url, 'invoice_id', $invoice['invoice_id']), _("View")) .  $invoice['name'] ?></a>
</td>
<td><strong><?php echo $invoice['company'] ?></strong></td>
<td><?php echo Minerva::format_price($invoice['total_bare']); ?></td>
<td><?php echo Minerva::format_price($invoice['tax']) ?></td>
<td><strong><?php echo Minerva::format_price($invoice['total']) ?></strong></td>
<td><?php echo $statuses[$invoice['status']] ?></td>
<td><?php echo Minerva::format_date($invoice['date'], false) ?></td>
<td><?php echo Minerva::expireDate($invoice['expire'], $invoice['date']) ?></td>
</tr>
