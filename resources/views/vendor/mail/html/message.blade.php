<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="'#'">
{{-- config('app.name') --}}
<img src="https://raw.githubusercontent.com/2amigos/mail-api-service/master/assets/mail-service%402x.png" style="display: block" width="560">
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{{ $slot }}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{{ $subcopy }}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
