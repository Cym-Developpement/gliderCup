<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Wassmer Cup</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<style>
{!! file_get_contents(resource_path('views/vendor/mail/html/themes/wassmer.css')) !!}
</style>
<style>
@media only screen and (max-width: 600px) {
.inner-body {
width: 100% !important;
}

.footer {
width: 100% !important;
}
}

@media only screen and (max-width: 500px) {
.button {
width: 100% !important;
}
}
</style>
</head>
<body>

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<!-- Header -->
<tr>
<td class="header" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); padding: 30px 0; text-align: center;">
<a href="{{ config('app.url') }}" style="display: block; color: #ffffff; text-decoration: none;">
<h1 style="color: #ffffff; font-size: 28px; font-weight: bold; margin: 0; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">Wassmer Cup</h1>
<p style="color: #e8f4f8; font-size: 16px; font-weight: 500; margin: 10px 0 0 0;">Compétition de Planeur</p>
</a>
</td>
</tr>

<!-- Email Body -->
<tr>
<td class="body" width="100%" cellpadding="0" cellspacing="0" style="border: hidden !important;">
<table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<!-- Body content -->
<tr>
<td class="content-cell">
{!! $slot !!}
</td>
</tr>
</table>
</td>
</tr>

<!-- Footer -->
<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="center">
<p style="color: #5d4e37; font-size: 12px; text-align: center; margin: 0;">
© {{ date('Y') }} Wassmer Cup - Compétition de Planeur. Tous droits réservés.
</p>
</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>
