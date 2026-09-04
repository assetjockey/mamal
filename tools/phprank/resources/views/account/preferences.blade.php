@section('site_title', formatTitle([__('Preferences'), config('settings.title')]))

@include('shared.breadcrumbs', ['breadcrumbs' => [
    ['url' => route('dashboard'), 'title' => __('Home')],
    ['url' => route('account'), 'title' => __('Account')],
    ['title' => __('Preferences')]
]])

<div class="d-flex"><h1 class="h2 mb-3 text-break">{{ __('Preferences') }}</h1></div>

<div class="card border-0 shadow-sm">
    <div class="card-header">
        <div class="font-weight-medium py-1">
            {{ __('Preferences') }}
        </div>
    </div>
    <div class="card-body">
        <ul class="nav nav-pills d-flex flex-fill flex-column flex-md-row mb-3" id="pills-tab" role="tablist">
            <li class="nav-item flex-grow-1 text-center">
                <a class="nav-link active" id="pills-brand-tab" data-toggle="pill" href="#pills-brand" role="tab" aria-controls="pills-brand" aria-selected="true">{{ __('Brand') }}</a>
            </li>

            <li class="nav-item flex-grow-1 text-center">
                <a class="nav-link" id="pills-reports-tab" data-toggle="pill" href="#pills-reports" role="tab" aria-controls="pills-reports" aria-selected="false">{{ __('Reports') }}</a>
            </li>

            <li class="nav-item flex-grow-1 text-center">
                <a class="nav-link" id="pills-export-tab" data-toggle="pill" href="#pills-export" role="tab" aria-controls="pills-export" aria-selected="false">{{ __('Export') }}</a>
            </li>
        </ul>

        @include('shared.message')

        <form action="{{ route('account.preferences') }}" method="post" enctype="multipart/form-data">
            @csrf

            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="pills-brand" role="tabpanel" aria-labelledby="pills-brand-tab">
                    <div class="alert alert-info">
                        {{ __('By branding a report, your information will show up when someone prints, or saves as PDF a report.') }}
                    </div>

                    <div class="form-row form-group">
                        <div class="col-12"><label for="i-brand-logo">{{ __('Logo') }}</label></div>
                        <div class="col">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text py-1 px-2"><img src="{{ Auth::user()->brandLogoUrl }}" class="max-height-6.5"></span>
                                </div>
                                <div class="custom-file">
                                    <input type="file" name="brand_logo" id="i-brand-logo" class="custom-file-input{{ $errors->has('brand_logo') ? ' is-invalid' : '' }} cursor-pointer" accept="{{ config('settings.user_avatar_format') }}">
                                    <label class="custom-file-label" for="i-brand-logo" data-browse="{{ __('Browse') }}">{{ __('Choose file') }}</label>
                                </div>
                            </div>
                        </div>
                        @if (Auth::user()->avatar)
                            <div class="col-auto">
                                <div class="btn-group-toggle input-group-prepend" data-toggle="buttons">
                                    <label class="btn btn-outline-danger">
                                        <input type="checkbox" name="remove_brand_logo" value="1" data-disable-input="i-brand-logo"> {{ __('Remove') }}
                                    </label>
                                </div>
                                @if ($errors->has('remove_brand_logo'))
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $errors->first('remove_brand_logo') }}</strong>
                                    </span>
                                @endif
                            </div>
                        @endif

                        @if ($errors->has('brand_logo'))
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $errors->first('brand_logo') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="i-brand-name">{{ __('Name') }}</label>
                        <input type="text" name="brand_name" id="i-brand-name" class="form-control{{ $errors->has('brand_name') ? ' is-invalid' : '' }}" value="{{ old('brand_name') ?? (Auth::user()->brand_information->name ?? null) }}">
                        @if ($errors->has('brand_name'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('brand_name') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="i-brand-url">{{ __('URL') }}</label>
                        <input type="text" name="brand_url" id="i-brand-url" class="form-control{{ $errors->has('brand_url') ? ' is-invalid' : '' }}" value="{{ old('brand_url') ?? (Auth::user()->brand_information->url ?? null) }}" placeholder="https://example.com">
                        @if ($errors->has('brand_url'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('brand_url') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="i-brand-email">{{ __('Email') }}</label>
                        <input type="text" name="brand_email" id="i-brand-email" class="form-control{{ $errors->has('brand_email') ? ' is-invalid' : '' }}" value="{{ old('brand_email') ?? (Auth::user()->brand_information->email ?? null) }}">
                        @if ($errors->has('brand_email'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('brand_email') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="i-brand-phone">{{ __('Phone') }}</label>
                        <input type="text" name="brand_phone" id="i-brand-phone" class="form-control{{ $errors->has('brand_phone') ? ' is-invalid' : '' }}" value="{{ old('brand_phone') ?? (Auth::user()->brand_information->phone ?? null) }}">
                        @if ($errors->has('brand_phone'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('brand_phone') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="tab-pane fade" id="pills-reports" role="tabpanel" aria-labelledby="pills-reports-tab">
                    <div class="form-group">
                        <label for="i-default-privacy" class="d-inline-flex flex-wrap align-items-center"><span class="{{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">{{ __('Privacy') }}</span><span class="badge badge-secondary">{{ __('Default') }}</span></label>
                        <select name="default_privacy" id="i-default-privacy" class="custom-select{{ $errors->has('default_privacy') ? ' is-invalid' : '' }}">
                            @foreach([0 => __('Public'), 1 => __('Private')] as $key => $value)
                                <option value="{{ $key }}" @if((Auth::user()->default_privacy == $key && old('default_privacy') == null) || (old('default_privacy') !== null && old('default_privacy') == $key)) selected @endif>{{ $value }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('default_privacy'))
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $errors->first('default_privacy') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="tab-pane fade" id="pills-export" role="tabpanel" aria-labelledby="pills-export-tab">
                    <div class="form-group">
                        <label for="i-default-export-detailed" class="d-inline-flex flex-wrap align-items-center"><span class="{{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">{{ __('Detailed') }}</span><span class="badge badge-secondary">{{ __('Default') }}</span></label>
                        <select name="default_export_detailed" id="i-default-export-detailed" class="custom-select{{ $errors->has('default_export_detailed') ? ' is-invalid' : '' }}">
                            @foreach([0 => __('No'), 1 => __('Yes')] as $key => $value)
                                <option value="{{ $key }}" @if ((old('default_export_detailed') !== null && old('default_export_detailed') == $key) || (Auth::user()->default_export_detailed == $key && old('default_export_detailed') == null)) selected @endif>{{ $value }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('default_export_detailed'))
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $errors->first('default_export_detailed') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col">
                    <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
                </div>
                <div class="col-auto">
                </div>
            </div>
        </form>
    </div>
</div>
