<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $emailSubject }}</title>
<style>
  body  { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f4f0; margin: 0; padding: 0; }
  .wrap { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.07); }
  .header { background: #b5451b; padding: 28px 36px; }
  .header img { height: 28px; }
  .header-name { color: #fff; font-size: 20px; font-weight: 700; letter-spacing: -.3px; }
  .body { padding: 36px; color: #1a1a1a; font-size: 15px; line-height: 1.65; }
  .footer { padding: 20px 36px; background: #f5f4f0; border-top: 1px solid #e8e4de; font-size: 12px; color: #888; }
  .footer a { color: #b5451b; text-decoration: none; }
  h1,h2,h3 { color: #1a1a1a; margin-top: 0; }
  a { color: #b5451b; }
  .btn { display: inline-block; background: #b5451b; color: #fff !important; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; margin: 16px 0; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div class="header-name">{{ $platformName }}</div>
  </div>
  <div class="body">
    {!! $bodyHtml !!}
  </div>
  <div class="footer">
    <p>
      You received this email because you have an active lease on {{ $platformName }}.
      If you have questions, contact your landlord directly through the platform.
    </p>
    <p style="margin:0">© {{ date('Y') }} {{ $platformName }} · <a href="#">Unsubscribe</a></p>
  </div>
</div>
</body>
</html>
