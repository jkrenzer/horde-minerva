<tr style="text-align: right;">
<td><?php echo $invoice['name']; ?></td>
<td><?php echo $invoice['company']; ?></td>
<td><?php echo Minerva::format_price($invoice['total_bare']); ?></td>
<td><?php echo Minerva::format_price($invoice['tax']); ?></td>
<td><?php echo Minerva::format_price($invoice['total']); ?></td>
<td><?php echo $resellers_list[$reseller]['clients'][$invoice['company_id']]; ?>%</td>
<td><?php echo Minerva::format_price($revenue); ?></td>
</tr>
