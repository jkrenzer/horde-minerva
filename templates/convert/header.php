<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $this->title; ?></title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<style type="text/css">
body, td, p {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 12px;
}

th {
    font-weight: bold;
    text-align: left;
    border-bottom: 1px #999 solid;
    font-size: 12px;
}
table {
    border-collapse: collapse;
}
.control {
    font-weight: bold;
}
</style>
</head>
<body bgcolor="white" text="black">

<table style="border-bottom: 1px solid black;" width="100%" summary="header">
<tr>
<td>

<?php if ($this->company->logo): ?>
<img src="<?php echo $this->view_url ?>/logo.jpg" />
<?php else: ?>
<span style="margin-left:30px; font-family: Staccato222BT; font-size:300%; color:#280099;"><?php echo $this->company->short_name ?></span>
<br />
<span class="small">
<?php echo $this->company->long_name ?>
</span>
<?php endif; ?>

</td>
<td>
<p style="text-align: right;">
<?php echo $this->company->short_name ?><br />
<?php echo $this->company->address ?>, <?php echo $this->company->postalcode ?> <?php echo $this->company->city ?><br />
<?php echo $this->company->url ?> - <?php echo $this->company->email ?><br />
<br />

<?php echo _("Corporate Registration Number") ?>: <?php echo $this->company->crn ?><br />
<?php echo _("Vat") ?>: <?php echo $this->company->vat ?><br />
<?php if ($this->company->capital): ?>
<?php echo _("Basic capital") ?>: <?php echo $this->company->capital ?><br />
<?php endif; ?>
<?php echo _("Registration unit") ?>: <?php echo $this->company->registration_unit ?><br />

</p>
</td>
</tr>
</table>

<br />
