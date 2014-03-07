
<div style="margin: 30px;">
<?php echo _("Comment") ?><br />
<textarea id="invoice_comment" name="invoice_comment" rows="5" cols="40"></textarea>
</div>

</form>

<script type="text/javascript">
//<![CDATA[
var taxes = <?php echo Horde_Serialize::serialize($taxes, Horde_Serialize::JSON, Horde_Nls::getCharset()); ?>;
var currencies = <?php echo Horde_Serialize::serialize($currencies, Horde_Serialize::JSON, Horde_Nls::getCharset()); ?>;
var defaultCurrency = <?php echo Horde_Serialize::serialize($default_currency, Horde_Serialize::JSON, Horde_Nls::getCharset()); ?>;
<?php if (!empty($units)) { ?>
var units = <?php echo Horde_Serialize::serialize($units, Horde_Serialize::JSON, Horde_Nls::getCharset()); ?>;
<?php } ?>
//]]>
</script>
