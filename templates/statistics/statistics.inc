<div class="header"><?php echo $title ?></div>
<br class="space">

<div class="prefsOverview">

<div style="width: 33%">
<div>
<dl>
<dt><a href="<?php echo Horde::applicationUrl('statistics/expired.php') ?>"><?php echo _("Expired invoices") ?></a></dt>
<dd><?php echo _("List and send notifications for expired invoices") ?></dd>
</dl>
</div>
</div>

<div style="width: 33%">
<div>
<dl>
<dt><a href="<?php echo Horde::applicationUrl('statistics/revenue.php') ?>"><?php echo _("Revenue calculation") ?></a></dt>
<dd><?php echo _("Calculate income and outcome in certain period confront the two calculation.") ?></dd>
</dl>
</div>
</div>

<div style="width: 33%">
<div>
<dl>
<dt><a href="<?php echo Horde::applicationUrl('statistics/income.php') ?>"><?php echo _("Income graph") ?></a></dt>
<dd><?php echo _("Show income fluctuation graph between desired dates.") ?></dd>
</dl>
</div>
</div>

<div style="width: 33%">
<div>
<dl>
<dt><a href="<?php echo Horde::applicationUrl('statistics/outcome.php') ?>"><?php echo _("Outcome graph") ?></a></dt>
<dd><?php echo _("Show outcome fluctuation graph between desired dates.") ?></dd>
</dl>
</div>
</div>

<div style="width: 33%">
<div>
<dl>
<dt><a href="<?php echo Horde::applicationUrl('statistics/stability.php') ?>"><?php echo _("Stability graph") ?></a></dt>
<dd><?php echo _("Show income and outcome difference between desired dates.") ?></dd>
</dl>
</div>
</div>

<div style="width: 33%">
<div>
<dl>
<dt><a href="<?php echo Horde::applicationUrl('statistics/statuses.php') ?>"><?php echo _("Statuses by period") ?></a></dt>
<dd><?php echo _("Show invoices grouped by current status between desired dates.") ?></dd>
</dl>
</div>
</div>

<div style="width: 33%">
<div>
<dl>
<dt><a href="<?php echo Horde::applicationUrl('statistics/statuses_pie.php') ?>"><?php echo _("Statuses pie") ?></a></dt>
<dd><?php echo _("Show invoices grouped by current status between desired dates in pie chart.") ?></dd>
</dl>
</div>
</div>

<div style="width: 33%">
<div>
<dl>
<dt><a href="<?php echo Horde::applicationUrl('statistics/clients.php') ?>"><?php echo _("Clients graph") ?></a></dt>
<dd><?php echo _("Show invoices grouped by client and statuses between desired dates.") ?></dd>
</dl>
</div>
</div>

<div style="width: 33%">
<div>
<dl>
<dt><a href="<?php echo Horde::applicationUrl('statistics/tags.php') ?>"><?php echo _("Tags graph") ?></a></dt>
<dd><?php echo _("Show income fluctuation graph by tags between desired dates.") ?></dd>
</dl>
</div>
</div>

<div style="width: 33%">
<div><u></u>
<dl>
<dt><a href="<?php echo Horde::applicationUrl('statistics/tags_pie.php') ?>"><?php echo _("Tags pie") ?></a></dt>
<dd><?php echo _("Show income compared by tags between desired dates.") ?></dd>
</dl>
</div>
</div>

</div>
