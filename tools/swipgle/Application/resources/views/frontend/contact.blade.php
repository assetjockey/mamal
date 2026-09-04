@extends('frontend.layouts.pages')
@section('hide_header', true)
@section('title', lang('Contact Us', 'contact us'))
@section('content')
    <section class="section">
        <div class="container-lg">
            <div class="section-inner">
                <div class="section-body">
                    <div class="contact-us">
                        <div class="card-v v2">
                            <div class="card-v-body">
                                @include('frontend.includes.contact-form')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
