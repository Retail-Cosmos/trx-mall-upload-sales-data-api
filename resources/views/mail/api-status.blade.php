<x-mail::message>
# Dear {{ $name }},

<x-mail::panel>
{{ $status === 'success' ? '✅ Sales sent Successfully' : '❌ Sales failed to send' }}
</x-mail::panel>

{{ $messages }}

</x-mail::message>
