<table width="90%" summary="invoice data" align="center">
<tr valign="top">
<td width="50%" rowspan="2">
<br />
<br />
<br />

<table class="striped" style="border-collapse: collapse;" width="330" summary="postal address">
<tr valign="top">
    <td width="20"></td>
    <td width="100" style="text-align: right;" nowrap="nowrap"><br /><strong><?php echo _("Postal address") ?></strong></td>
    <td width="10"></td>
    <td style="border: 1px #999999 solid; padding: 10px;" width="200"><?php echo $this->client_postal_address; ?></td>
    <td></td>
</tr>
</table>

</td>
</tr>
</table>

<br />
<br />

<div style="float: right">
<?php echo $this->today; ?>
</div>

<h1><?php echo $this->subject; ?></h1>

<?php echo $this->body; ?>