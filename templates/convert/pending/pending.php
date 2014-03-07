<table width="100%" summary="invoice data"> 
<tr valign="top"> 
<td width="50%"> 
</td> 
<td> 
    <h2><?php echo _('Pending invoices list'); ?></h2>
</td> 
</tr> 
<tr valign="top"> 
<td width="50%"> 
 
<table class="striped" style="border-collapse: collapse;" summary="postal address"> 
<tr valign="top"> 
    <td width="100"></td> 
    <td><br /><strong><?php echo _('Postal address'); ?></strong></td>
    <td width="10"></td> 
    <td style="border:1px #999 solid; padding:10px;" width="50%"><?php echo $this->client['postal_address']; ?></td>
    <td></td> 
</tr>
</table>

</td>
<td width="50%">

<table class="striped" style="border-collapse: collapse;" summary="client data"> 
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
<?php echo _('From our evidence on'); ?> 
<strong><?php echo $this->date;?></strong>
<?php echo _('you are in debit with our company for'); ?> <?php echo $this->total; ?>.
<?php echo _('Due consists of the following open invoices:'); ?> 
</p> 
 
<table style="border-collapse: collapse; width: 100%;"> 
<tr> 
    <td style="border-bottom: 1px solid black;"><strong><?php echo _('No.'); ?></strong></td> 
    <td style="border-bottom: 1px solid black;"><strong><?php echo _('Invoice'); ?></strong></td> 
    <td style="border-bottom: 1px solid black;"><strong><?php echo _('Date'); ?></strong></td> 
    <td style="border-bottom: 1px solid black;"><strong><?php echo _('Expire'); ?></strong></td> 
    <td style="text-align: right; border-bottom: 1px solid black;"><strong><?php echo _('Value'); ?></td> 
    <td style="text-align: right; border-bottom: 1px solid black;"><strong><?php echo _('Paid'); ?></strong></td> 
    <td style="text-align: right; border-bottom: 1px solid black;"><strong><?php echo _('Total'); ?></strong></td> 
</tr> 
 
<?php
if ($this->invoices):
foreach ($this->invoices as $k1 => $v1): ?>
<tr> 
    <td style="border-bottom: 1px solid gray;"><?php echo $v1['num']; ?></td>
    <td style="border-bottom: 1px solid gray;"><?php echo $v1['name']; ?></td>
    <td style="border-bottom: 1px solid gray;"><?php echo $v1['date']; ?></td>
    <td style="border-bottom: 1px solid gray;"><?php echo $v1['expire']; ?></td>
    <td style="text-align: right; border-bottom: 1px solid gray;"><?php echo $v1['total']; ?></td>
    <td style="text-align: right; border-bottom: 1px solid gray;"><?php echo $this->credit; ?></td>
    <td style="text-align: right; border-bottom: 1px solid gray;"><?php  echo $v1['total']; ?></td>
</tr>
<?php
endforeach;
endif;
?>

<tr>
    <td colspan="5" style="text-align: right;"><?php echo $this->total; ?></td>
    <td style="text-align: right;"><?php echo $this->credit; ?></td>
    <td style="text-align: right;"><strong><?php echo $this->total; ?></strong></td>
</tr> 
 
</table> 
 
<br /> 
 
<ul> 
<li><?php echo _('We agree with the above evidence'); ?></li> 
<li><?php echo _('We completely disagree with the above evidence'); ?></li> 
<li><?php echo _('We agree only with the due value of'); ?> ...................</li> 
</ul> 
 
<?php echo _('Comment'); ?> .................................................<br /> 
................................................................<br /> 
<br /> 
<br /> 
 
<?php echo _('City'); ?>..........., <?php echo _('Date'); ?> ....................<br /> 
<br /> 
<br /> 
<?php echo _('Sing and stamp'); ?>: .......................... 
 
<br /> 
<br /> 
 
<p> 
<?php echo _('Please, return us the confirmed opened invoices lists in the next eight days. Otherwise we will take the evidence as correct.'); ?> 
</p> 
 
<p> 
<?php echo $this->city; ?>, <?php echo $this->today; ?><br />
</p> 
