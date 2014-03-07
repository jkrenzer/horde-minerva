<?php
$old_invoice=$GLOBALS['minerva_invoices']->getOne($clone_id);unset($old_invoice['taxes'],$old_invoice['currencies'],$old_invoice['total']);switch($clone_type){case Minerva::CLONE_PARTIAL:unset($old_invoice['invoice']['id'],$old_invoice['invoice']['name'],$old_invoice['invoice']['date'],$old_invoice['invoice']['type'],$old_invoice['invoice']['status']);require_once MINERVA_BASE.'/lib/Recurrence.php';$recurrances=new Minerva_Recurrences();$remove=$recurrances->getOne($clone_id,false);if(!is_array($remove)){break;}
if(!$remove['articles']){unset($old_invoice['articles']);}
if(!$remove['client']){unset($old_invoice['client']);}
break;case Minerva::CLONE_NORMAL:$old_invoice['invoice']['id']=$clone_id;break;}
$old_invoice=Horde_String::convertCharset($old_invoice,Horde_Nls::getCharset());array_walk_recursive($old_invoice,'toString');function toString(&$value,$key)
{if(!is_string($value)){$value=(string)$value;$value=str_replace(',','.',$value);}}?><script type="text/javascript">function reloadInvoice(){var old_invoice=<?php echo Horde_Serialize::serialize($old_invoice,Horde_Serialize::JSON,Horde_Nls::getCharset());?>;for(f in old_invoice){if(f=='articles'){count=old_invoice[f].length;for(var article=0;article<count;article++){for(attribute in old_invoice[f][article]){name=f+'_data_'+(article+1)+'_'+attribute;if(!$(name)){continue;}
if(attribute=='price'||attribute=='qt'||attribute=='discount'){value=old_invoice[f][article][attribute];value=value.replace(/\./g,currencies[defaultCurrency]['mon_decimal_point']);$(name).value=value;}else if(name=='name'){MinervaInvoice.enlargeArticleName($(name));}else{$(name).value=old_invoice[f][article][attribute];}}
MinervaInvoice.articleAddFields();}
continue;}
for(field in old_invoice[f]){name=f+'_'+field;if($(name)){$(name).value=old_invoice[f][field];}}}
if(old_invoice['client']['obligated']==1){$('client_obligated').checked=true;}else{$('client_obligated').value=1;}
if(old_invoice['invoice']['id']){$('invoice_id').value=old_invoice['invoice']['id'];$('invoice_date[day]').value=parseInt(old_invoice['invoice']['date'].substring(8,10),10);$('invoice_date[month]').value=parseInt(old_invoice['invoice']['date'].substring(5,7),10);$('invoice_date[year]').value=old_invoice['invoice']['date'].substring(0,4);}
MinervaInvoice.fixExpire($('invoice_expire'));MinervaInvoice.calcPrices();}</script>
