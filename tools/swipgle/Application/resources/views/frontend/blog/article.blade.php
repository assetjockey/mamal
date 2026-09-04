@extends('frontend.layouts.pages')
@section('title', $blogArticle->title)
@section('description', $blogArticle->short_description)
@section('og_image', asset($blogArticle->image))
@section('content')
    <section class="section">
        <div class="container-lg">
            <div class="section-inner">
                <div class="section-body">
                    <div class="row g-4">
                        <div class="col-12 col-xl-8">
                            <div class="row row-cols-1 g-4">
                                <div class="col">
                                    <div class="blog-post v2 p-4">
                                        <div class="blog-post-header">
                                            <img class="blog-post-img" src="{{ asset($blogArticle->image) }}"
                                                alt="{{ $blogArticle->title }}" title="{{ $blogArticle->title }}" />
                                        </div>
                                        <div class="blog-post-body px-0">
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
                                            {!! ads_blog_single_article_top() !!}
                                            {!! $blogArticle->content !!}
                                            {!! ads_blog_single_article_bottom() !!}
                                            <div class="share mt-3">
                                                <h6 class="mb-0 me-3">{{ lang('Share On', 'blog') }}:</h6>
                                                @include('frontend.blog.includes.share-buttons')
                                            </div>
                                        </div>
                                        <div class="blog-post-footer px-0">
                                            <div class="comments">
                                                <h5 class="comments-title">
                                                    <i class="far fa-comments me-2"></i>{{ lang('Comments', 'blog') }}
                                                    ({{ $blogArticleComments->count() }})
                                                </h5>
                                                @forelse ($blogArticleComments as $blogArticleComment)
                                                    <div class="comment">
                                                        <div class="comment-img">
                                                            <img src="{{ asset($blogArticleComment->user->avatar) }}"
                                                                alt="{{ $blogArticleComment->user->firstname . ' ' . $blogArticleComment->user->lastname }}" />
                                                        </div>
                                                        <div class="comment-info">
                                                            <div class="d-flex flex-column">
                                                                <h6 class="comment-title mb-1">
                                                                    {{ $blogArticleComment->user->firstname . ' ' . $blogArticleComment->user->lastname }}
                                                                </h6>
                                                                <time class="text-muted mb-2 small">
                                                                    <i class="fa fa-calendar-alt me-1"></i>
                                                                    {{ vDate($blogArticleComment->created_at) }}</time>
                                                            </div>
                                                            <p class="comment-text mb-0 text-muted"> {!! allowBr($blogArticleComment->comment) !!}
                                                            </p>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="alert mb-0 p-1">
                                                        {{ lang('No comments available', 'blog') }}
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="card-v v2">
                                        <div class="card-v-body">
                                            @auth
                                                <h5 class="card-v-title mb-4">{{ lang('Leave a comment', 'blog') }}</h5>
                                                <form action="{{ route('blog.article.comment', $blogArticle->slug) }}"
                                                    method="POST">
                                                    @csrf
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ lang('Your comment', 'blog') }} :
                                                            <span class="red">*</span></label>
                                                        <textarea name="comment" class="form-control" rows="6" required></textarea>
                                                    </div>
                                                    {!! display_captcha() !!}
                                                    <button
                                                        class="btn btn-secondary btn-md px-5">{{ lang('Publish', 'blog') }}</button>
                                                </form>
                                            @else
                                                <div class="alert mb-0 text-center p-1">
                                                    {{ lang('Login or create account to leave comments', 'blog') }}
                                                </div>
                                            @endauth
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @include('frontend.blog.includes.sidebar')
                    </div>
                </div>
            </div>
        </div>
    </section>
    @push('scripts')
        {!! google_captcha() !!}
    @endpush
@endsection
