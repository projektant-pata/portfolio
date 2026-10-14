<x-mail::message>
# {{ $fields['name'] }} &lt;{{ $fields['email'] }}&gt;

**Topic:** {{ $fields['topic'] }}
**Timeline:** {{ $fields['timeline'] !== '' ? $fields['timeline'] : '—' }}
**Sent in:** {{ strtoupper($fields['locale']) }}

---

{{ $fields['message'] }}

<x-mail::button :url="'mailto:'.$fields['email']">
Reply
</x-mail::button>
</x-mail::message>
