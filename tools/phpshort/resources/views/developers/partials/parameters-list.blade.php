<div class="list-group">
    <div class="list-group-item text-muted">
        <div class="row">
            <div class="col-12 col-lg-3">{{ __('Name') }}</div>
            <div class="col-12 col-lg-2">{{ __('Type') }}</div>
            <div class="col-12 col-lg-7">{{ __('Description') }}</div>
        </div>
    </div>

    @foreach($parameters as $parameter)
        <div class="list-group-item">
            <div class="row">
                <div class="col-12 col-lg-3">
                    <code>{{ $parameter['name'] }}</code>
                </div>
                <div class="col-12 col-lg-2">
                    @if($parameter['type'])
                        <span class="badge badge-danger">{{ mb_strtolower(__('Required')) }}</span>
                    @else
                        <span class="badge badge-primary">{{ mb_strtolower(__('Optional')) }}</span>
                    @endif
                        <span class="badge badge-secondary">{{ $parameter['format'] }}</span></div>
                <div class="col-12 col-lg-7">
                    {!! $parameter['description'] !!}
                </div>
            </div>
        </div>
    @endforeach
</div>