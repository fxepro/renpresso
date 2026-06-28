
{{ $sender->fullName() }} sent a message about {{ $lease->property->name }} ({{ $lease->property->city }}, {{ $lease->property->country_code }}).

---
{{ $message->body }}
---

This is a copy sent because they chose "Also email a copy". Reply in your {{ config('app.name') }} dashboard under Messages.
