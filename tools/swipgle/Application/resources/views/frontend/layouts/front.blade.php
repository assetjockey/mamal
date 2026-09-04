<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('frontend.includes.head')
    @push('styles_libs')
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert/sweetalert2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/aos/aos.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/swiper/swiper-bundle.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/odometer/odometer-theme-default.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/simplebar/simplebar.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/vironeer/vironeer-icons.min.css') }}">
    @endpush
    @include('frontend.includes.styles')
    {!! head_code() !!}
</head>

<body>
    <div class="page-slider bg-primary">
        @if ($slideshows->count() > 0)
            <div class="swiper">
                <div class="swiper-wrapper">
                    @foreach ($slideshows as $slideshow)
                        @if ($slideshow->type == 1)
                            <div class="swiper-slide" data-swiper-autoplay="{{ $slideshow->duration * 1000 }}">
                                <div class="swiper-bg"
                                    style="background-image: url({{ $slideshow->source == 1 ? asset($slideshow->file) : $slideshow->file }})">
                                </div>
                            </div>
                        @elseif ($slideshow->type == 2)
                            <div class="swiper-slide swiper-video"
                                data-swiper-autoplay="{{ $slideshow->duration * 1000 }}">
                                <div class="swiper-video-container">
                                    <video loop muted>
                                        <source
                                            src="{{ $slideshow->source == 1 ? asset($slideshow->file) : $slideshow->file }}">
                                    </video>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
        <header class="header">
            @include('frontend.includes.navbar')
            @yield('content')
        </header>
    </div>
    @if ($settings['counter_status'])
        <section id="counter" class="section v3">
            <div class="container-lg">
                <div class="section-inner">
                    <div class="section-body">
                        <div
                            class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 row-cols-xxl-4 justify-content-center gx-4 gy-5">
                            <div class="col">
                                <div class="counter">
                                    <div class="counter-icon">
                                        <i class="fa-solid fa-user-group"></i>
                                    </div>
                                    <div class="counter-info">
                                        <p class="counter-number odometer"
                                            data-number="{{ $settings['active_users_counter'] }}"></p>
                                        <p class="counter-title">{{ lang('Active Users', 'home page') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="counter">
                                    <div class="counter-icon">
                                        <i class="fa-solid fa-paper-plane"></i>
                                    </div>
                                    <div class="counter-info">
                                        <p class="counter-number odometer"
                                            data-number="{{ $settings['transferred_files_counter'] }}"></p>
                                        <p class="counter-title">{{ lang('Transferred files', 'home page') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="counter">
                                    <div class="counter-icon">
                                        <i class="fa-regular fa-eye"></i>
                                    </div>
                                    <div class="counter-info">
                                        <p class="counter-number odometer"
                                            data-number="{{ $settings['daily_visitors_couner'] }}"></p>
                                        <p class="counter-title">{{ lang('Daily visitors', 'home page') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="counter">
                                    <div class="counter-icon">
                                        <i class="fas fa-download"></i>
                                    </div>
                                    <div class="counter-info">
                                        <p class="counter-number odometer"
                                            data-number="{{ $settings['all_time_downloads_couner'] }}"></p>
                                        <p class="counter-title">{{ lang('All-Time Downloads', 'home page') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
    {!! ads_home_page_top() !!}
    @if ($features->count() > 0)
        <section id="features" class="section v2 pattern">
            <div class="container-lg">
                <div class="section-inner">
                    <div class="section-header">
                        <div class="section-title">
                            <h5>{{ lang('Features', 'home page') }}</h5>
                        </div>
                        <div class="section-text text-center col-lg-7 mx-auto">
                            <p>{{ lang('Features description', 'home page') }}</p>
                        </div>
                    </div>
                    <div class="section-body">
                        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 justify-content-center g-3">
                            @foreach ($features as $feature)
                                <div class="col" data-aos="fade-up" data-aos-duration="1000">
                                    <div class="card-v v2 h-100">
                                        <div class="card-v-body">
                                            <div class="feat">
                                                <div class="feat-icon">
                                                    <img src="{{ asset($feature->image) }}"
                                                        alt="{{ $feature->title }}" title="{{ $feature->title }}" />
                                                </div>
                                                <div class="feat-info">
                                                    <p class="feat-title">{{ $feature->title }}</p>
                                                    <p class="feat-text">{{ $feature->content }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
    @if ($monthlyPlans->count() > 0 || $lifetimePlans->count() > 0 || $yearlyPlans->count() > 0)
        <section id="prices" class="section bg-white">
            <div class="container-lg">
                <div class="section-inner">
                    <div class="section-header">
                        <div class="section-title">
                            <h5>{{ lang('Pricing', 'home page') }}</h5>
                        </div>
                        <div class="section-text text-center col-lg-7 mx-auto">
                            <p>{{ lang('Pricing description', 'home page') }}</p>
                        </div>
                    </div>
                    <div class="section-body">
                        @if ($lifetimePlans->count() > 0 || $yearlyPlans->count() > 0)
                            <div class="d-flex justify-content-center mb-5">
                                <div class="plan-switcher">
                                    <span class="plan-switcher-item active">{{ lang('Monthly', 'plans') }}</span>
                                    <span class="plan-switcher-item">{{ lang('Yearly', 'plans') }}</span>
                                    @if ($lifetimePlans->count() > 0)
                                        <span class="plan-switcher-item">{{ lang('Lifetime', 'plans') }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                        @include('frontend.includes.plans')
                    </div>
                </div>
            </div>
        </section>
    @endif
    @if ($settings['website_blog_status'] && $blogArticles->count() > 0)
        <section id="blog" class="section">
            <div class="container-lg">
                <div class="section-inner">
                    <div class="section-header">
                        <div class="section-title">
                            <h5>{{ lang('Blog', 'home page') }}</h5>
                        </div>
                        <div class="section-text text-center col-lg-7 mx-auto">
                            <p>{{ lang('Blog description', 'home page') }}</p>
                        </div>
                    </div>
                    <div class="section-body">
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 justify-content-center g-3">
                            @foreach ($blogArticles as $blogArticle)
                                <div class="col" data-aos="fade-up" data-aos-duration="1000">
                                    <div class="blog-post">
                                        <div class="blog-post-header">
                                            <a href="{{ route('blog.article', $blogArticle->slug) }}">
                                                <img src="{{ asset($blogArticle->image) }}" class="blog-post-img"
                                                    alt="{{ $blogArticle->title }}"
                                                    title="{{ $blogArticle->title }}" />
                                            </a>
                                            <a class="blog-post-cate"
                                                href="{{ route('blog.category', $blogArticle->blogCategory->slug) }}">
                                                {{ $blogArticle->blogCategory->name }}
                                            </a>
                                        </div>
                                        <div class="blog-post-body">
                                            <div class="post-meta mb-2">
                                                <div class="post-meta-item">
                                                    <i class="fas fa-user"></i>
                                                    <span>{{ $blogArticle->admin->firstname . ' ' . $blogArticle->admin->lastname }}</span>
                                                </div>
                                                <div class="post-meta-item">
                                                    <i class="fa fa-calendar-alt"></i>
                                                    <time>{{ vDate($blogArticle->created_at) }}</time>
                                                </div>
                                            </div>
                                            <a href="{{ route('blog.article', $blogArticle->slug) }}"
                                                class="blog-post-title">{{ shortertext($blogArticle->title, 60) }}</a>
                                            <p class="blog-post-text">
                                                {{ shortertext($blogArticle->short_description, 120) }}</p>
                                        </div>
                                        <div class="blog-post-footer">
                                            <a href="{{ route('blog.article', $blogArticle->slug) }}"
                                                class="btn btn-secondary">{{ lang('Read More', 'blog') }}</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-center mt-5">
                            <a href="{{ route('blog.index') }}"
                                class="btn btn-primary-icon btn-md">{{ lang('View More', 'home page') }}<i
                                    class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
    {!! ads_home_page_bottom() !!}
    {!! ads_download_page_down_bottom() !!}
    @if ($faqs->count() > 0 && $settings['website_faq_status'])
        <section id="faq" class="section bg-white">
            <div class="container-lg">
                <div class="section-inner">
                    <div class="section-header">
                        <div class="section-title">
                            <h5>{{ lang('FAQ', 'home page') }}</h5>
                        </div>
                        <div class="section-text text-center col-lg-7 mx-auto">
                            <p>{{ lang('FAQ description', 'home page') }}</p>
                        </div>
                    </div>
                    <div class="section-body">
                        <div class="faqs" data-aos="zoom-in" data-aos-duration="1000">
                            <div class="accordion-custom">
                                <div class="accordion" id="accordionExample">
                                    @foreach ($faqs as $faq)
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="heading{{ hashid($faq->id) }}">
                                                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}"
                                                    type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapse{{ hashid($faq->id) }}"
                                                    aria-expanded="true"
                                                    aria-controls="collapse{{ hashid($faq->id) }}">
                                                    {{ $faq->title }}
                                                </button>
                                            </h2>
                                            <div id="collapse{{ hashid($faq->id) }}"
                                                class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
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
                        </div>
                        <div class="d-flex justify-content-center mt-5">
                            <a href="{{ route('faq') }}"
                                class="btn btn-primary-icon btn-md">{{ lang('Find out more answers on our FAQ', 'home page') }}<i
                                    class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
    @if ($settings['website_contact_form_status'])
        <section id="contact" class="section">
            <div class="container-lg">
                <div class="section-inner">
                    <div class="section-header">
                        <div class="section-title">
                            <h5>{{ lang('Contact Us', 'home page') }}</h5>
                        </div>
                        <div class="section-text text-center col-lg-7 mx-auto">
                            <p>{{ lang('Contact Us description', 'home page') }}</p>
                        </div>
                    </div>
                    <div class="section-body">
                        <div class="contact-us" data-aos="zoom-in" data-aos-duration="1000">
                            @include('frontend.includes.contact-form')
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
    @push('scripts_libs')
        <script src="{{ asset('assets/vendor/libs/clipboard/clipboard.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/sweetalert/sweetalert2.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/aos/aos.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/swiper/swiper-bundle.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/odometer/odometer.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/simplebar/simplebar.min.js') }}"></script>
    @endpush
    @include('frontend.includes.footer')
    @include('frontend.configurations.config')
    @include('frontend.configurations.widgets')
    @include('frontend.includes.scripts')
</body>

</html>
