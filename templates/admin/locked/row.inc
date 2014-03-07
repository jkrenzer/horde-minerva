<tr>
<td><?php echo $minerva_invoices->getName($invoice['lock_principal']) ?></td>
<td><?php echo $invoice['lock_owner'] ?></td>
<td><?php echo Minerva::format_date($invoice['lock_origin_timestamp']) ?></td>
<td><?php echo Minerva::format_date($invoice['lock_update_timestamp']) ?></td>
<td><?php echo Minerva::format_date($invoice['lock_expiry_timestamp']) ?></td>
</tr>
