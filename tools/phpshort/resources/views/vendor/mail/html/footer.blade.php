<tr>
<td>
<table class="footer" align="{{ $align ?? '' }}" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align ?? '' }}">
{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
</table>
</td>
</tr>
