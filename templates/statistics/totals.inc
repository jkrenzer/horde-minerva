<table>
<tr>
<td>
<?php echo $graph_image ?>
</td>
</tr>
<tr>
<td>

<table class="striped sortable" id="statics_data">
<thead>
<tr>
<th><?php echo _("Data") ?></th>
<th><?php echo _("Value") ?></th>
</tr>
</thead>
<?php
foreach ($data as $id => $total) {
    echo '<tr style="text-align: right;"><td>' . $id . '</td>' .
         '<td style="padding-left: 10px;">' . Minerva::format_price($total) . '</td></tr>' . "\n";
}

echo '<tr style="text-align: right; font-weight: bold;"><td>' . _("Total") . '</td>' .
         '<td style="padding-left: 10px; ">' . Minerva::format_price(array_sum($data)) . '</td></tr>' . "\n";
?>
</table>

</td>
</tr>
</table>