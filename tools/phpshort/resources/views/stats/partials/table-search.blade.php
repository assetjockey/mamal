<form method="GET" action="{{ route(Route::currentRouteName(), ['id' => $link->id, 'from' => $dateRange['from'], 'to' => $dateRange['to']]) }}" class="d-md-flex">
    <div class="input-group input-group-sm">
        <input class="form-control max-w-32 max-w-sm-full" name="search" placeholder="{{ __('Search') }}" value="{{ request()->input('search') }}">
        <div class="input-group-append">
            <button type="button" class="btn btn-outline-primary d-flex align-items-center dropdown-toggle dropdown-toggle-split reset-after" data-tooltip="true" title="{{ __('Filters') }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.filter', ['class' => 'fill-current w-4 h-4'])&#8203;</button>
            <div class="dropdown-menu {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu' : 'dropdown-menu-right') }} border-0 shadow w-64 p-0" id="search-filters">
                <div class="dropdown-header py-4">
                    <div class="row">
                        <div class="col"><div class="fw-medium m-0 text-body">{{ __('Filters') }}</div></div>
                        <div class="col-auto">
                            @if(request()->input('per_page'))
                                <a href="{{ route(Route::currentRouteName(), ['id' => $link->id, 'from' => $dateRange['from'], 'to' => $dateRange['to']]) }}" class="text-secondary">{{ __('Reset') }}</a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="dropdown-divider my-0"></div>

                <input name="from" type="hidden" value="{{ $dateRange['from'] }}">
                <input name="to" type="hidden" value="{{ $dateRange['to'] }}">

                <div class="max-h-96 overflow-auto pt-4">
                    <div class="form-group px-6">
                        <label for="i-search-by" class="small">{{ __('Search by') }}</label>
                        <select name="search_by" id="i-search-by" class="custom-select custom-select-sm rounded-sm">
                            @foreach(['value' => $name] as $key => $value)
                                <option value="{{ $key }}" @if(request()->input('search_by') == $key || !request()->input('search_by') && $key == 'name') selected @endif>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group px-6">
                        <label for="i-sort-by" class="small">{{ __('Sort by') }}</label>
                        <select name="sort_by" id="i-sort-by" class="custom-select custom-select-sm rounded-sm">
                            @foreach(['count' => $count, 'value' => $name] as $key => $value)
                                <option value="{{ $key }}" @if(request()->input('sort_by') == $key) selected @endif>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group px-6">
                        <label for="i-sort" class="small">{{ __('Sort') }}</label>
                        <select name="sort" id="i-sort" class="custom-select custom-select-sm rounded-sm">
                            @foreach(['desc' => __('Descending'), 'asc' => __('Ascending')] as $key => $value)
                                <option value="{{ $key }}" @if(request()->input('sort') == $key) selected @endif>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group px-6">
                        <label for="i-per-page" class="small">{{ __('Results per page') }}</label>
                        <select name="per_page" id="i-per-page" class="custom-select custom-select-sm rounded-sm">
                            @foreach([10, 25, 50, 100] as $value)
                                <option value="{{ $value }}" @if(request()->input('per_page') == $value || request()->input('per_page') == null && $value == config('settings.paginate')) selected @endif>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="dropdown-divider my-0"></div>

                <div class="px-6 py-4">
                    <button type="submit" class="btn btn-primary btn-sm btn-block">{{ __('Search') }}</button>
                </div>
            </div>
        </div>
    </div>
</form>