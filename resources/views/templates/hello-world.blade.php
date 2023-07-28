<x-mail::message>

{{ __('mailing.hello world') }}

{{ __('mailing.mail body content') }}

<x-mail::panel>
{{ __('mailing.panel usage') }}
</x-mail::panel>

{{ __('mailing.with embedded variable', ['name' => $receiver]) }}
</x-mail::message>
