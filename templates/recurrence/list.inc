<div style="float: right;" class="header">
<a href="<?php echo $edit_url; ?>0"><?php echo _('Create'); ?></a>
</div>
<h1 class="header"><?php echo $title; ?></h1>
<table style="width: 100%;" class="striped sortable" id="recurrence_list"> 
<thead>
    <tr style="text-align: left;">
    <th><?php echo _('Actions'); ?></th>
    <th><?php echo _('Invoice'); ?></th>
    <th><?php echo _('Client'); ?></th>
    <th><?php echo _('Description'); ?></th>
    <th><?php echo _('Value'); ?></th>
    <th><?php echo _('Copy'); ?></th>
    <th><?php echo _('Start'); ?></th>
    <th><?php echo _('Interval'); ?></th>
    <th><?php echo _('Next'); ?></th>
    <th><?php echo _('Last'); ?></th>
    <th><?php echo _('User'); ?></th>
    </tr>
</thead>
<tbody>
<?php foreach ($rows as $k1 => $v1): ?>
<tr>
    <td class="nowrap"> 
    <a href="<?php echo $edit_url . $v1['invoice_id']; ?>"><img src="<?php echo $img_dir; ?>/edit.png" alt="<?php echo _('Edit'); ?>" title="<?php echo _('Edit'); ?>" /></a>
    <a href="<?php echo $delete_url . $v1['invoice_id']; ?>"><img src="<?php echo $img_dir; ?>/delete.png" alt="<?php echo _('Delete'); ?>" title="<?php echo _('Delete'); ?>" /></a>
    <a href="<?php echo $invoice_url . $v1['invoice_id']; ?>"><img src="<?php echo $img_dir; ?>/tick.png" alt="<?php echo _('Compose'); ?>" title="<?php echo _('Compose'); ?>" /></a>
    </td> 
    <td><?php echo $v1['invoice_name']; ?></td>
    <td><?php echo $v1['name']; ?></td>
    <td><?php  echo $v1['description']; ?></td>
    <td style="text-align: right;"><?php echo $v1['total']; ?></td>
    <td>
        <?php echo $v1['articles']; ?>
        <?php echo $v1['client']; ?>
    </td>
    <td><?php echo $v1['rstart']; ?></td>
    <td><?php echo $v1['rinterval']; ?></td>
    <td><?php echo $v1['next']; ?></td>
    <td><?php echo $v1['last']; ?></td>
    <td><?php echo $v1['horde_uid']; ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<br />
<br />

<?php echo _('Invoices'); ?>: <?php echo $count; ?><br />
<?php echo _('Total bare'); ?>: <?php echo $total_bare; ?><br />
<?php echo _('Total tax'); ?>: <?php  echo $total_tax;?><br />
<strong><?php echo _('Total'); ?>: <?php echo $total; ?></strong>