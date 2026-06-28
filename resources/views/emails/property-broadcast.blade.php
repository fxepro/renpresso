<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family:system-ui,-apple-system,sans-serif;line-height:1.5;color:#0D1F35;background:#FAF8F3;padding:24px;">
  <p style="margin:0 0 12px">
    <strong>{{ $sender->fullName() }}</strong> sent a notice for <strong>{{ $property->name }}</strong>
    ({{ $property->city }}, {{ $property->country_code }}).
  </p>
  <blockquote style="margin:16px 0;padding:16px;border-left:4px solid #C4622D;background:#fff;border-radius:0 8px 8px 0;">
    {!! nl2br(e($message->body)) !!}
  </blockquote>
  <p style="font-size:14px;color:#8A99AA;margin:24px 0 0">
    Reply in your {{ config('app.name') }} dashboard under <strong>Messages</strong> — your reply goes to your unit&rsquo;s lease thread.
  </p>
</body>
</html>
