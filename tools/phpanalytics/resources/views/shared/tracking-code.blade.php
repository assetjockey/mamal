<label for="i-tracking-code" class="text-muted">{!! __('Include this code in the :head or :body section of your website.', ['head' => '<code>&lt;head&gt;</code>', 'body' => '<code>&lt;body&gt;</code>']) !!}</label>
<div class="position-relative">
    <textarea name="tracking_code" class="form-control" id="i-tracking-code" rows="3" onclick="this.select();" readonly>&lt;script data-host=&quot;{{ config('app.url') }}&quot; data-dnt=&quot;{{ config('settings.do_not_track') ? 'true' : 'false' }}&quot; src=&quot;{{ !empty(config('settings.cdn_url')) ? config('settings.cdn_url') : asset('js/script.js') }}&quot; id=&quot;ZwSg9rf6GA&quot; async defer&gt;&lt;/script&gt;</textarea>

    <div class="position-absolute top-0 right-0">
        <div class="btn btn-sm btn-primary m-2" data-tooltip-copy="true" title="{{ __('Copy') }}" data-text-copy="{{ __('Copy') }}" data-text-copied="{{ __('Copied') }}" data-clipboard="true" data-clipboard-target="#i-tracking-code">{{ __('Copy') }}</div>
    </div>
</div>