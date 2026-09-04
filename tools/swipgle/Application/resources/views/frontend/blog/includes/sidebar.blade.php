<div class="col-12 col-xl-4">
    <div class="card-v v2 mb-4">
        <div class="card-v-body">
            <form action="{{ route('blog.index') }}" method="GET">
                <div class="form-search">
                    <input type="text" name="q" required placeholder="{{ lang('Search..', 'blog') }}"
                        value="{{ request()->input('q') ?? '' }}">
                    <button class="icon">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="card-v v2 mb-4">
        <div class="card-v-body">
            <div class="share justify-content-center">
                @include('frontend.blog.includes.share-buttons')
            </div>
        </div>
    </div>
    @if ($blogCategories->count() > 0)
        <div class="card-v v2 mb-4">
            <div class="card-v-body">
                <h5 class="card-v-title mb-3">{{ lang('Categories', 'blog') }}</h5>
                <div class="categories">
                    @foreach ($blogCategories as $blogCategory)
                        <a href="{{ route('blog.category', $blogCategory->slug) }}" class="category">
                            <span class="category-title">{{ $blogCategory->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    {!! ads_blog_sidebar() !!}
    @if ($recentBlogArticles->count() > 0)
        <div class="card-v v2">
            <div class="card-v-body">
                <h5 class="card-v-title mb-4">{{ lang('Popular articles', 'blog') }}</h5>
                <div class="posts">
                    @foreach ($recentBlogArticles as $recentBlogArticle)
                        <div class="post">
                            <a href="{{ route('blog.article', $recentBlogArticle->slug) }}">
                                <img class="post-img" src="{{ asset($recentBlogArticle->image) }}"
                                    alt="{{ $recentBlogArticle->title }}" title="{{ $recentBlogArticle->title }}" />
                            </a>
                            <div class="post-info">
                                <h6 class="post-title">
                                    <a href="{{ route('blog.article', $recentBlogArticle->slug) }}">
                                        {{ shortertext($recentBlogArticle->title, 50) }}
                                    </a>
                                </h6>
                                <div class="post-meta">
                                    <div class="post-meta-item">
                                        <i class="fa fa-calendar-alt"></i>
                                        <time>{{ vDate($recentBlogArticle->created_at) }}</time>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
