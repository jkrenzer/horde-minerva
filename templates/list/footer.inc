<tr style="text-align: right;">
<td colspan="4"><?php echo Minerva::format_price($total_bare, $invoice['currency'], false); ?></td>
<td><?php echo Minerva::format_price($total_tax, $invoice['currency'], false); ?></td>
<td><strong><?php echo Minerva::format_price(array_sum($totals), $invoice['currency'], false) ?></strong></td>
<td>
<?php 
foreach ($totals as $key => $value) {
    echo $statuses[$key] . ': ' . Minerva::format_price($value, $invoice['currency'], false) . "<br />\n";
}
?>
</td>
<td colspan="3">&nbsp;</td>
</tr>
</tbody>
</table>
