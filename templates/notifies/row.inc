<tr style="text-align: right;">
<td>
<?php

echo Horde::link(Horde_Util::addParameter($print_url, 'invoice_id', $invoice['invoice_id']), _("Print"), '', '_print');
echo '<img src="' . $imagedir . '/print.png" title="' . _("Print") . '" /></a> ';

echo Horde::link(Horde_Util::addParameter($delete_url, 'invoice_id', $invoice['invoice_id']), _("Delete"));
echo '<img src="' . $imagedir . '/delete.png" title="' . _("Delete") . '" /></a> ';

?>
</td>
<td><?php echo $invoice['name'] ?></td>
<td><strong><?php echo $invoice['company'] ?></strong></td>
<td><?php echo Minerva::format_price($invoice['total_bare'], $invoice['currency']); ?></td>
<td><?php echo Minerva::format_price($invoice['tax'], $invoice['currency']) ?></td>
<td><strong><?php echo Minerva::format_price($invoice['total'], $invoice['currency']) ?></strong></td>
<td><?php echo $statuses[$invoice['status']] ?></td>
<td><?php echo Minerva::format_date($invoice['date'], false) ?></td>
<td><?php echo Minerva::expireDate($invoice['expire'], $invoice['date']) ?></td>
</tr>