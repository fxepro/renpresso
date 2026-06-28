<!DOCTYPE html>
<html>
<body style="font-family: system-ui, sans-serif; color: #0D1F35; line-height: 1.6; max-width: 560px;">
  <p>Hi {{ $entry->first_name ?: 'there' }},</p>
  <p>Thanks for joining the {{ config('app.name') }} waitlist. We saved your details and will email you when landlord accounts open in your market.</p>
  <p>Your first month will be free when we launch — plus early access to the founding team.</p>
  <p style="color: #4A5A6A; font-size: 14px;">No action needed. We'll be in touch.</p>
  <p>— The {{ config('app.name') }} team</p>
</body>
</html>
