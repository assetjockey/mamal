<?php

namespace App\Livewire\Admin\Frontend\Blogs;

use App\Models\BlogPost;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Blog Post')]
class BlogForm extends Component
{
    use WithFileUploads;

    public ?BlogPost $post = null;

    // Core
    public string $title = '';
    public ?string $slug = null;
    public ?string $excerpt = null;
    public string $content = '';

    // Visuals
    public $featured_image;            // pending upload
    public ?string $featured_image_path = null; // persisted path
    public ?string $featured_image_alt = null;

    // Author
    public string $author_name = '';
    public ?string $author_role = null;

    // Taxonomy
    public ?string $category = null;
    public ?string $tags = null;       // comma separated in the form

    // Publication
    public string $status = 'draft';
    public ?string $published_at = null;
    public bool $is_featured = false;

    // SEO
    public ?string $meta_title = null;
    public ?string $meta_description = null;
    public ?string $meta_keywords = null;
    public ?string $canonical_url = null;

    public function mount(?int $post_id = null): void
    {
        $this->author_name = config('app.name', 'AI Ad Studio') . ' Team';

        if ($post_id) {
            $this->post = BlogPost::findOrFail($post_id);

            $this->title = $this->post->title;
            $this->slug = $this->post->slug;
            $this->excerpt = $this->post->excerpt;
            $this->content = $this->post->content;
            $this->featured_image_path = $this->post->featured_image;
            $this->featured_image_alt = $this->post->featured_image_alt;
            $this->author_name = $this->post->author_name;
            $this->author_role = $this->post->author_role;
            $this->category = $this->post->category;
            $this->tags = is_array($this->post->tags) ? implode(', ', $this->post->tags) : null;
            $this->status = $this->post->status;
            $this->published_at = $this->post->published_at?->format('Y-m-d\TH:i');
            $this->is_featured = (bool) $this->post->is_featured;
            $this->meta_title = $this->post->meta_title;
            $this->meta_description = $this->post->meta_description;
            $this->meta_keywords = $this->post->meta_keywords;
            $this->canonical_url = $this->post->canonical_url;
        }
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|alpha_dash',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:5048',
            'featured_image_alt' => 'nullable|string|max:255',
            'author_name' => 'required|string|max:255',
            'author_role' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'tags' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published,archived',
            'published_at' => 'nullable|date',
            'is_featured' => 'boolean',
            'meta_title' => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|url|max:255',
        ];
    }

    public function updatedFeaturedImage(): void
    {
        $this->validateOnly('featured_image');
    }

    public function removeFeaturedImage(): void
    {
        if (
            filled($this->featured_image_path)
            && ! str_starts_with((string) $this->featured_image_path, 'http')
            && ! str_starts_with((string) $this->featured_image_path, 'data:')
            && Storage::disk('public')->exists($this->featured_image_path)
        ) {
            Storage::disk('public')->delete($this->featured_image_path);
        }

        $this->featured_image_path = null;
    }

    public function save()
    {
        $validated = $this->validate();

        // Store a freshly uploaded cover under /public/uploads/blog/ so it's
        // served directly by the web server (the `public` disk root is
        // public_path(), no storage symlink involved). Keep a readable,
        // unique filename and persist the relative path — BlogPost's
        // featured_image_url accessor resolves it via asset().
        if ($this->featured_image) {
            $base = Str::slug($this->title) ?: 'blog';
            $filename = $base . '-' . Str::lower(Str::random(8)) . '.' . $this->featured_image->getClientOriginalExtension();

            $this->featured_image_path = $this->featured_image->storeAs('uploads/blog', $filename, 'public');
        }

        // Normalize the tags input ("a, b, c") into the array the column casts.
        $tags = collect(explode(',', (string) $this->tags))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->values()
            ->all();

        // Publishing without an explicit date stamps "now" so the published
        // scope (published_at <= now) immediately surfaces it on the frontend.
        $publishedAt = $this->published_at ? Carbon::parse($this->published_at) : null;
        if ($this->status === 'published' && ! $publishedAt) {
            $publishedAt = now();
        }

        $data = [
            'title' => $this->title,
            'slug' => $this->slug ?: null,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'featured_image' => $this->featured_image_path,
            'featured_image_alt' => $this->featured_image_alt,
            'author_name' => $this->author_name,
            'author_role' => $this->author_role,
            'category' => $this->category,
            'tags' => $tags,
            'status' => $this->status,
            'published_at' => $publishedAt,
            'is_featured' => $this->is_featured,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'canonical_url' => $this->canonical_url,
        ];

        if ($this->post) {
            $this->post->update($data);
            toaster()->success(__('Blog post updated successfully'));
        } else {
            // Let the model recompute reading time fresh from content.
            $data['reading_time_minutes'] = null;
            BlogPost::create($data);
            toaster()->success(__('Blog post created successfully'));
        }

        return $this->redirect(route('admin.frontend.blogs'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.frontend.blogs.form');
    }
}
