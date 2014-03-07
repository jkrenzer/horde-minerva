<div class="header" style="float: right;">
<a href="<?php echo $list_url; ?>"><?php echo _('Reload'); ?></a>
| <a href="<?php echo $edit_url; ?>"><?php echo _('Create'); ?></a>
| <a href="<?php echo $export_url; ?>"><?php echo _('Export'); ?></a>
</div>
<div class="header"><?php echo $title; ?> (<?php echo number_format($count) ?>)</div>
<table id="outcome_invoices" style="width: 100%;" class="striped sortable">
<thead>
<tr style="text-align: right;">
<th><?php echo _('Actions'); ?></th>
<th><?php echo _('Id'); ?></th>
<th><?php echo _('Recived'); ?></th>
<th><?php echo _('Due'); ?></th>
<th><?php echo _('Paid'); ?></th>
<th><?php echo _('Company'); ?></th>
<th><?php echo _('Total'); ?></th>
<th><?php echo _('Bank'); ?></th>
<th><?php echo _('Intend'); ?></th>
</tr>
</thead>
<?php foreach ($rows as $k1 => $v1): ?>
    <tr style="text-align: right;">
    <td nowrap="nowrap"><?php foreach ($v1['actions'] as $k2 => $v2): ?> <?php if (isset($v2)) { echo is_array($v2) ? $k2 : $v2; } elseif (isset($v1['actions'])) { echo $v1['actions']; } ?> <?php endforeach; ?></td>
    <td><?php echo $v1['id']; ?></td>
    <td><?php echo $v1['recived']; ?></td>
    <td><?php echo $v1['due']; ?></td>
    <td><?php echo $v1['paid']; ?></td>
    <td><?php echo $v1['client_name']; ?></td>
    <td><?php echo $v1['total']; ?></td>
    <td><?php echo $v1['bank']; ?></td>
    <td><?php echo $v1['intend'];?></td>
    </tr>
<?php endforeach; ?>
</table>
<div class="control">
    <?php echo _('Total'); ?>: <?php echo $total; ?>
    <?php echo _('Total tax'); ?>: <?php echo $total_tax; ?>
</div>
<?php echo $pager;?>