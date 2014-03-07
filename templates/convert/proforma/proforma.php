<table width="90%" summary="invoice data" align="center"> 
<tr valign="top">
<td width="50%" rowspan="2"> 
<br />
<br />
<br />
<table class="striped" style="border-collapse: collapse;" width="330" summary="postal address">
<tr valign="top">
    <td width="20"></td>
    <td style="border: 1px #999999 solid; padding: 10px;" width="200"><?php echo $this->client_postal_address; ?></td>
    <td></td>
</tr>
</table>

</td>
<td>
    <h2><?php echo _('Proforma'); ?>: <?php echo $this->invoice_name; ?></h2>
    <br />
    <br />
</td>
</tr>
<tr valign="top">
<td width="50%">

<table style="border-collapse: collapse;" summary="client data"> 
<tr valign="top">
    <td><?php echo _('Client'); ?>:</td>
    <td>
        <?php echo $this->client_name; ?><br />
        <?php echo $this->client_address; ?>
    </td>
</tr>
<tr valign="top">
    <td><?php echo _('Publish date'); ?>:</td>
    <td><?php echo $this->invoice_date; ?></td>
</tr> 
<tr valign="top"> 
    <td><?php echo _('Vat'); ?>:</td>
    <td><?php echo $this->client_vat; ?></td>
</tr>
<tr valign="top">
    <td><?php echo _('Obligated'); ?>:</td>
    <td>
    <?php if ($this->client_obligated): ?>
        <?php echo _('Yes'); ?>
    <?php else: ?>
        <?php echo _('No'); ?>
    <?php endif; ?>
    </td>
</tr>
<tr valign="top">
    <td><?php echo _('Place'); ?>:</td>
    <td><?php echo $this->invoice_place; ?></td>
</tr>
<tr valign="top">
    <td><?php echo _('Expire'); ?>:</td>
    <td><?php echo $this->invoice_expire; ?> <?php echo _('days'); ?></td>
</tr>
</table>

</td>
</td>
</table>

<br />
<br />
 
<table style="border-collapse: collapse; background-color: #eeeeee" width="100%" summary="articles">
<thead>
<tr style="border-bottom: 1px solid #000000;">
<th><?php echo _('Article name'); ?></th>
<th><?php echo _('Price'); ?></th>
<th><?php echo _('Qy.'); ?></th>
<?php 
if (!empty($this->units)): ?>
    <th><?php echo _('Unit'); ?></th>
<?php endif; ?> 
<th><?php echo _('Sale'); ?></th>
<th><?php echo _('Tax'); ?></th>
<th style="text-align: right;"><?php echo _('Total'); ?></th>
</tr>
</thead>
<tbody>
<?php
if ($this->articles):
foreach ($this->articles as $k1 => $v1):
?>
<tr valign="top" style="border-bottom: 1px solid #cccccc;">
<td><?php echo $v1['name']; ?></td>
<td nowrap="nowrap"><?php echo $v1['price']; ?></td>
<td><?php echo $v1['qt']; ?></td>
<?php 
if (!empty($this->units)): ?>
    <td><?php echo $this->units[$v1['unit']]; ?></td>
<?php endif; ?> 
<td><?php echo $v1['discount']; ?>%</td>
<td><?php echo $v1['tax']; ?>%</td>
<td nowrap="nowrap"  style="text-align: right;"><?php echo $v1['total']; ?></td>
</tr> 
<?php
endforeach;
endif;
?>
</tbody>
</table>

<div style="border-top: 1px black solid;" width="50%"></div> 

<table width="100%"> 
<tr>
<td>

<table style="float: right; border-collapse: collapse;">
<tr valign="top">
    <td><?php echo _('Total'); ?>:</td> 
    <td style="text-align: right;"><?php echo $this->total_bare; ?></td>
</tr> 
<tr valign="top"> 
    <td><?php echo _('Sale'); ?>:</td> 
    <td style="text-align: right;"><?php echo $this->total_discount; ?></td>
</tr> 
<tr valign="top" style="border-bottom: 1px solid #000000;"> 
    <td><?php echo _('Total without texes'); ?>:</td> 
    <td style="text-align: right;"><?php echo $this->invoice_without_tax; ?></td>
</tr>
<?php
if ($this->taxes):
foreach ($this->taxes as $k2 => $v2):
?>
<?php if ($v2['total']): ?>
<tr>
<tr valign="top">
    <td><?php echo $v2['name']; ?></td>
    <td style="text-align: right;"><?php echo $v2['total']; ?></td>
</tr>
<?php
endif;
endforeach;
endif;
?>
<tr valign="top">
    <td><?php echo _('Total tax'); ?>:</td>
    <td style="text-align: right;"><?php echo $this->invoice_tax; ?></td>
</tr>
<tr valign="top" style="border-top: 1px solid #000000;">
    <td><strong><?php echo _('Total'); ?></strong>:</td> 
<td style="text-align: right;">
<?php
if ($this->taxes):
foreach ($this->currencies as $k3 => $v3):
        echo $v3['total'] . '<br />';
endforeach;
endif;
?>
</td>
</tr>
</table>

</td>
</tr>
</table>

<?php if ($this->invoice_comment): ?>
<?php echo _('Comment'); ?>:<br />
<?php echo $this->invoice_comment; ?>
<?php endif; ?>

<p style="text-align: justify;">
<?php if ($this->banks): ?>
<?php echo _('The proforma invoice is payable on the bank account of'); ?>
<?php $i4 = count($this->banks); foreach ($this->banks as $k4 => $v4): ?>
 <?php echo $v4; ?>
<?php if (--$i4 != 0) { echo  _(" or "); }; endforeach; ?>
<br />
<?php endif; ?>
<?php echo _('Use the proforma invoice number for the transaction reference.'); ?> 
</p>
