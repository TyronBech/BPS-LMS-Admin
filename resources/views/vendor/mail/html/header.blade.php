@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img
    src="{{ (($settings ?? null)?->org_logo_base64) ?? asset('img/BPSLogo.png') }}"
    class="logo"
    alt="{{ (($settings ?? null)?->org_name) ?? 'BPS' }} Logo"
>
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
