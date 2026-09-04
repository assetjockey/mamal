<?php

namespace App\Http\Controllers;

use App\Models\BlogComment;
use App\Models\BlogPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class BlogController extends Controller
{
    /**
     * Public blog index — paginated grid with category filter, tag filter,
     * and free-text search. All filters are reflected in the URL so they're
     * crawlable and shareable.
     */
    public function index(Request $request): View
    {
        $perPage = 9;

        $posts = BlogPost::query()
            ->published()
            ->when($request->filled('category'), fn ($q) => $q->category((string) $request->query('category')))
            ->when($request->filled('tag'),      fn ($q) => $q->withTag((string) $request->query('tag')))
            ->when($request->filled('q'),        fn ($q) => $q->search((string) $request->query('q')))
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->paginate($perPage)
            ->withQueryString();

        // Sidebar / filter data — sourced from the published set so we never
        // surface a category that has no live posts.
        $categories = BlogPost::query()
            ->published()
            ->whereNotNull('category')
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $popularTags = BlogPost::query()
            ->published()
            ->whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->countBy()
            ->sortDesc()
            ->take(20)
            ->keys()
            ->values();

        return view('pages.blog.index', [
            'posts'        => $posts,
            'categories'   => $categories,
            'popularTags'  => $popularTags,
            'activeCategory' => (string) $request->query('category', ''),
            'activeTag'      => (string) $request->query('tag', ''),
            'searchTerm'     => (string) $request->query('q', ''),
        ]);
    }

    /**
     * Single post page. Increments view count atomically, loads approved
     * comments + replies in two queries (post + grouped replies), and
     * computes a related-posts list (same category, then fall back to any).
     */
    public function show(string $slug): View
    {
        $post = BlogPost::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        // Atomic increment, doesn't trigger model events
        $post->incrementViews();

        // Approved top-level comments + their approved replies in one trip
        $comments = BlogComment::query()
            ->where('blog_post_id', $post->id)
            ->where('status', 'approved')
            ->whereNull('parent_id')
            ->with(['replies' => fn ($q) => $q->where('status', 'approved')->orderBy('created_at')])
            ->orderByDesc('created_at')
            ->get();

        $relatedPosts = BlogPost::query()
            ->published()
            ->where('id', '!=', $post->id)
            ->when($post->category, fn ($q) => $q->where('category', $post->category))
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        // If the same-category list is too thin, top up from anywhere
        if ($relatedPosts->count() < 3) {
            $extra = BlogPost::query()
                ->published()
                ->where('id', '!=', $post->id)
                ->whereNotIn('id', $relatedPosts->pluck('id'))
                ->orderByDesc('published_at')
                ->take(3 - $relatedPosts->count())
                ->get();
            $relatedPosts = $relatedPosts->concat($extra);
        }

        return view('pages.blog.show', [
            'post'         => $post,
            'comments'     => $comments,
            'relatedPosts' => $relatedPosts,
        ]);
    }

    /**
     * Public comment submission. Pending by default — a moderator approves
     * before it appears on the post. Rate-limited per IP to slow spam.
     */
    public function storeComment(Request $request, string $slug): RedirectResponse
    {
        $post = BlogPost::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        // 5 comments per IP per 10 minutes
        $rateKey = 'blog-comment:' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            return back()
                ->with('comment_error', __('You\'re posting too quickly. Please try again in a few minutes.'))
                ->withInput();
        }
        RateLimiter::hit($rateKey, 600);

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:120'],
            'email'     => ['required', 'email', 'max:200'],
            'website'   => ['nullable', 'url', 'max:255'],
            'content'   => ['required', 'string', 'min:3', 'max:4000'],
            'parent_id' => ['nullable', 'integer', 'exists:blog_comments,id'],

            // Honeypot — bots will fill this; humans never see it.
            'website_url_2' => ['nullable', 'size:0'],
        ]);

        // If parent_id is set, it must belong to this same post
        if (filled($validated['parent_id'] ?? null)) {
            $parentBelongs = BlogComment::query()
                ->whereKey($validated['parent_id'])
                ->where('blog_post_id', $post->id)
                ->exists();

            if (! $parentBelongs) {
                $validated['parent_id'] = null;
            }
        }

        BlogComment::query()->create([
            'blog_post_id' => $post->id,
            'parent_id'    => $validated['parent_id'] ?? null,
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'website'      => $validated['website'] ?? null,
            'content'      => strip_tags($validated['content']),
            'status'       => 'pending',
            'ip_address'   => $request->ip(),
            'user_agent'   => substr((string) $request->userAgent(), 0, 255),
        ]);

        return redirect()
            ->to(route('blog.show', ['slug' => $post->slug]) . '#comments')
            ->with('comment_success', __('Thanks! Your comment is awaiting moderation.'));
    }
}
