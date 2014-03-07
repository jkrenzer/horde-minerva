<tr>
<td><?php echo $reseller['name'] . ' (' . @$reseller['company'] . ')' ?></td>
<td><?php echo $reseller['email'] ?></td>
<td>
<?php 
foreach ($reseller['clients'] as $client => $percentage) {

    $url = Horde_Util::addParameter($resellers_url, array('reseller_id' => $reseller['id'], 'client_id' => $client, 'percentage' => $percentage));

    echo Horde::link(Horde_Util::addParameter($url, 'actionID', 'edit'), sprintf(_("Edit reseller %s"), $reseller['name']));
    echo '<img src="' . $imagedir . '/edit.png" title="' . _("Edit") . '" /></a>';

    echo Horde::link(Horde_Util::addParameter($url, 'actionID', 'delete'), sprintf(_("Delete reseller %s"), $reseller['name']));
    echo '<img src="' . $imagedir . '/delete.png" title="' . _("Delete") . '" /></a>';

    echo ' ' . $clients[$client] . ' (' . $percentage . '%)<br />';
}
 ?>
</td>
</tr>
