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
    <h2><?php echo _('Late payment notification'); ?>: <?php echo $this->name; ?></h2>
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
        <?php echo $this->client['name']; ?><br />
        <?php echo $this->client['address']; ?>
    </td>
</tr>
<tr valign="top">
    <td><?php echo _('Vat'); ?>:</td>
    <td><?php echo $this->client['vat']; ?></td>
</tr>
<tr valign="top">
    <td><?php echo _('Publish date'); ?>:</td>
    <td><?php echo $this->today; ?></td>
</tr>
<tr valign="top">
    <td><?php echo _('Place'); ?>:</td>
    <td><?php echo $this->city; ?></td>
</tr>
</table>

</td>
</tr>
</table>

<br />
<br />

<p>
<?php echo _('From our evidence on') . ' '; ?>
<strong> <?php echo $this->today; ?></strong>
<?php
echo _('you are in debit with our company for') . ' ';
foreach ($this->sum as $sum ) {
    echo $sum . ' ';
}
echo '. ' . _('Due consists of the following open invoices:');
?>
</p>

<table style="border-collapse: collapse; width: 100%;">
<tr>
    <td style="border-bottom: 1px solid black;"><strong><?php echo _('Invoice'); ?></strong></td>
    <td style="border-bottom: 1px solid black;"><strong><?php echo _('Total'); ?></strong></td>
    <td style="border-bottom: 1px solid black;"><strong><?php echo _('Publish date'); ?></strong></td>
    <td style="border-bottom: 1px solid black;"><strong><?php echo _('Expire'); ?></strong></td>
</tr>

<?php
if ($this->invoices):
foreach ($this->invoices as $k1 => $v1): ?>
<tr>
    <td style="border-bottom: 1px solid gray;"><?php echo $v1['name']; ?></td>
    <td style="border-bottom: 1px solid gray;"><?php echo $v1['price']; ?></td>
    <td style="border-bottom: 1px solid gray;"><?php echo $v1['date']; ?></td>
    <td style="border-bottom: 1px solid gray;"><?php echo $v1['expire']; ?></td>
</tr>
<?php
endforeach;
endif;
?>
</table>

<br />

<table>
<tr valign="top">
    <td><?php echo _('Total'); ?>:</td>
    <td style="text-align: right;">
<?php
if ($this->total):
foreach ($this->total as $t):
    echo $t . '<br />';
endforeach;
endif;
?>
    </td>
</tr>
<tr valign="top">
    <td><?php echo _('Notification cost'); ?>:</td>
    <td style="text-align: right;">
<?php
if ($this->nc):
foreach ($this->nc as $t):
    echo $t . '<br />';
endforeach;
endif;
?>
</td>
</tr>
<tr valign="top" style="border-bottom: 1px solid #000000;">
    <td><strong><?php echo _('Total'); ?>:</strong></td>
    <td style="text-align: right; font-weight: bold;">
<?php
if ($this->sum):
foreach ($this->sum as $t):
    echo $t . '<br />';
endforeach;
endif;
?>
</td>
</tr>
</table>

</td>
</tr>
</table>

<p style="text-align: justify;">
<?php if (!empty($this->banks) || !empty($this->banks)): ?>
<?php echo _('Please, cover the overdue obligations in the next 8 (eight) days on bank account of '); ?>
<?php $i4 = count($this->banks); foreach ($this->banks as $k4 => $v4): ?>
 <?php echo $v4; ?>
<?php if (--$i4 != 0) { echo  _(" or "); }; endforeach; ?>
<br />
<?php endif; ?>
</p>
