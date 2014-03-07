<div class="header"><?php echo $title ?></div>

<table class="striped" style="width: 100%;">

<tr style="text-align: right;">
<th class="control"><?php echo _("Type") ?></th>
<th class="control"><?php echo _("Number") ?></th>
<th class="control"><?php echo _("Total bare") ?></th>
<th class="control"><?php echo _("Total tax") ?></th>
<th class="control"><?php echo _("Total") ?></th>
</tr>

<tr style="text-align: right;">
<td><?php echo _("Outcome") ?></td>
<td><?php echo $out_count; ?></td>
<td><?php echo Minerva::format_price($out_total_bare) ?></td>
<td><?php echo Minerva::format_price($out_total_tax) ?></td>
<td><?php echo Minerva::format_price($out_total) ?></td>
</tr>

<tr style="text-align: right;">
<td><?php echo _("Income") ?></td>
<td><?php echo $inc_count; ?></td>
<td><?php echo Minerva::format_price($inc_total_bare) ?></td>
<td><?php echo Minerva::format_price($inc_total_tax) ?></td>
<td><?php echo Minerva::format_price($inc_total) ?></td>
</tr>

<tr style="text-align: right;">
<td><?php echo _("Calculation") ?></td>
<td></td>
<td><?php echo Minerva::format_price($inc_total_bare - $out_total_bare) ?></td>
<td><?php echo Minerva::format_price($inc_total_tax - $out_total_tax) ?></td>
<td><?php echo Minerva::format_price($inc_total - $out_total) ?></td>
</tr>

</table>

<br class="space">