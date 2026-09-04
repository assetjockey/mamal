@extends('frontend.layouts.pages')
@section('title', $page->title)
@section('description', $page->short_description)
@section('content')
    <section class="section">
        <div class="container-lg">
            <div class="section-inner">
                <div class="section-body">
                    <div class="card-v v2">
                        <div class="card-v-body">
                            {!! $page->content !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
