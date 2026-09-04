@extends('frontend.layouts.pages')
@section('title', $blogCategory ?? lang('Blog', 'blog'))
@section('content')
    <section class="section">
        <div class="container-lg">
            <div class="section-inner">
                <div class="section-body">
                    <div class="row g-4">
                        <div class="col-12 col-xl-8">
                            <div class="row row-cols-1 row-cols-md-1 g-4 mb-4">
                                @forelse ($blogArticles as $blogArticle)
                                    <div class="col">
                                        <div class="blog-post">
                                            <div class="blog-post-header">
                                                <a href="{{ route('blog.article', $blogArticle->slug) }}">
                                                    <img class="blog-post-img" src="{{ asset($blogArticle->image) }}"
                                                        alt="{{ $blogArticle->title }}" title="{{ $blogArticle->title }}" />
                                                </a>
                                                <a href="{{ route('blog.category', $blogArticle->blogCategory->slug) }}"
                                                    class="blog-post-cate">{{ $blogArticle->blogCategory->name }}</a>
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
                                                    <div class="post-meta-item">
                                                        <i class="fas fa-comments"></i>
                                                        <span>{{ $blogArticle->comments_count }}</span>
                                                    </div>
                                                </div>
                                                <a href="{{ route('blog.article', $blogArticle->slug) }}"
                                                    class="blog-post-title">
                                                    <h6>{{ $blogArticle->title }}</h6>
                                                </a>
                                                <p class="blog-post-text">
                                                    {{ shortertext($blogArticle->short_description, 150) }}</p>
                                            </div>
                                            <div class="blog-post-footer">
                                                <a href="{{ route('blog.article', $blogArticle->slug) }}"
                                                    class="btn btn-secondary">{{ lang('Read More', 'blog') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                    @if ($loop->first)
                                        {!! ads_blog_articles_center() !!}
                                    @endif
                                @empty
                                    <div class="card-v p-0 shadow-sm border-0 text-center p-5 text-muted">
                                        <h3>{{ lang('No data found', 'blog') }}</h3>
                                        <p class="mb-0">
                                            {{ lang('It looks like there is no articles or your search did not return any results', 'blog') }}
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                            {{ $blogArticles->links() }}
                        </div>
                        @include('frontend.blog.includes.sidebar')
                    </div>
                </div>
            </div>
            {!! ads_blog_articles_bottom() !!}
        </div>
    </section>
@endsection
