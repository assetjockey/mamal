@extends('frontend.layouts.pages')
@section('title', lang('Faq'))
@section('content')
    <section class="section">
        <div class="container-lg">
            <div class="section-inner">
                <div class="section-body">
                    <div class="faqs">
                        <div class="accordion-custom v2">
                            <div class="accordion" id="accordionExample">
                                @foreach ($faqs as $faq)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading{{ hashid($faq->id) }}">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapse{{ hashid($faq->id) }}"
                                                aria-expanded="true" aria-controls="collapse{{ hashid($faq->id) }}">
                                                {{ $faq->title }}
                                            </button>
                                        </h2>
                                        <div id="collapse{{ hashid($faq->id) }}" class="accordion-collapse collapse"
                                            aria-labelledby="heading{{ hashid($faq->id) }}"
                                            data-bs-parent="#accordionExample">
                                            <div class="accordion-body">
                                                <div class="mb-0">{!! $faq->content !!}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        {{ $faqs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
