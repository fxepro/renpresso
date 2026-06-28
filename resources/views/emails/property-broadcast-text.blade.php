{{ $sender->fullName() }} sent a building notice for {{ $property->name }} ({{ $property->city }}, {{ $property->country_code }}).

---
{{ $message->body }}
---

Reply in your {{ config('app.name') }} dashboard under Messages (your unit's lease thread).
