<tr style="text-align: right;">
<td></td>
<td><?php echo $invoice['name'] ?></td>
<td><strong><?php echo $invoice['company'] ?></strong></td>
<td><?php echo Minerva::format_price($invoice['total_bare']); ?></td>
<td><?php echo Minerva::format_price($invoice['tax']) ?></td>
<td><strong><?php echo Minerva::format_price($invoice['total']) ?></strong></td>
<td><?php echo $statuses[$invoice['status']] ?></td>
<td><?php echo Minerva::format_date($invoice['date'], false) ?></td>
<td><?php echo Minerva::expireDate($invoice['expire'], $invoice['date']) ?></td>
</tr>