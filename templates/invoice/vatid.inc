<?php

echo '<br /><em>' . _("Country") . ':</em> '
    . $result['countryCode'] . '<br /><em>'
    . _("Client vat") . ':</em> ' . $result['vatNumber']
    . '<br />';
if (!empty($result['name'])) {
    echo '<em>' . _("Name") . ':</em> ' . $result['name'] . '<br />';
}
if (!empty($result['address'])) {
    echo '<em>' . _("Address") . ':</em> ' . $result['address'] . '<br />';
    $result['postal_address'] = $result['name'] . "\n" . $result['address'];
}

$result['vat'] = $info['vatid'];
echo '<br /><strong>';
echo Horde::link('#', _("Add as customer"), '', '', "clientUpdate(1)") . _("Add as customer") . '</a>';
?>

<script type="text/javascript">
//<![CDATA[
var clients = <?php echo Horde_Serialize::serialize(array(1 => $result), Horde_Serialize::JSON, Horde_Nls::getCharset()); ?>;
//]]>
</script>
