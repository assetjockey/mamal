<?php

namespace App\Livewire\Admin\Frontend;

use App\Models\Testimonial;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Testimonials')]
class Testimonials extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $search = '';

    public ?int $testimonialId = null;
    public string $testimonial = '';
    public int $stars = 5;
    public string $name = '';
    public ?string $role = null;
    public ?string $company = null;
    public bool $featured = false;
    public string $status = 'active';

    public $avatar;                    // pending upload
    public ?string $avatar_path = null; // persisted path

    public bool $showModal = false;

    public ?int $deleteId = null;
    public bool $showDeleteModal = false;

    protected function rules(): array
    {
        return [
            'testimonial' => 'required|string|max:1000',
            'stars' => 'required|integer|min:1|max:5',
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'featured' => 'boolean',
            'status' => 'required|in:active,inactive',
            'avatar' => 'nullable|image|max:2048',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedAvatar(): void
    {
        $this->validateOnly('avatar');
    }

    /**
     * Open the modal in "create" mode with a blank form.
     */
    public function create(): void
    {
        $this->reset(['testimonialId', 'testimonial', 'name', 'role', 'company', 'avatar', 'avatar_path']);
        $this->stars = 5;
        $this->featured = false;
        $this->status = 'active';
        $this->resetValidation();
        $this->showModal = true;
    }

    /**
     * Open the modal in "edit" mode, hydrated from the selected record.
     */
    public function edit(int $id): void
    {
        $record = Testimonial::findOrFail($id);

        $this->testimonialId = $record->id;
        $this->testimonial = $record->testimonial;
        $this->stars = $record->stars;
        $this->name = $record->name;
        $this->role = $record->role;
        $this->company = $record->company;
        $this->featured = (bool) $record->featured;
        $this->status = $record->status;
        $this->avatar_path = $record->avatar;
        $this->avatar = null;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function removeAvatar(): void
    {
        if (
            filled($this->avatar_path)
            && ! str_starts_with((string) $this->avatar_path, 'http')
            && Storage::disk('public')->exists($this->avatar_path)
        ) {
            Storage::disk('public')->delete($this->avatar_path);
        }

        $this->avatar_path = null;
    }

    public function save(): void
    {
        $validated = $this->validate();

        // Store a freshly uploaded avatar under /public/uploads/testimonials/
        // with a readable, unique filename. The model's avatar_url accessor
        // resolves the stored relative path via asset().
        if ($this->avatar) {
            $base = Str::slug($this->name) ?: 'testimonial';
            $filename = $base . '-' . Str::lower(Str::random(8)) . '.' . $this->avatar->getClientOriginalExtension();
            $this->avatar_path = $this->avatar->storeAs('uploads/testimonials', $filename, 'public');
        }

        Testimonial::updateOrCreate(
            ['id' => $this->testimonialId],
            [
                'testimonial' => $validated['testimonial'],
                'stars' => $validated['stars'],
                'name' => $validated['name'],
                'role' => $validated['role'],
                'company' => $validated['company'],
                'featured' => $validated['featured'],
                'status' => $validated['status'],
                'avatar' => $this->avatar_path,
            ]
        );

        $this->showModal = false;

        toaster()->success(
            $this->testimonialId
                ? __('Testimonial updated successfully')
                : __('Testimonial created successfully')
        );

        $this->reset(['testimonialId', 'testimonial', 'name', 'role', 'company', 'avatar', 'avatar_path']);
        $this->stars = 5;
        $this->featured = false;
        $this->status = 'active';
    }

    public function delete(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $record = Testimonial::select('id', 'avatar')->find($this->deleteId);

        if ($record) {
            if (
                filled($record->avatar)
                && ! str_starts_with((string) $record->avatar, 'http')
                && Storage::disk('public')->exists($record->avatar)
            ) {
                Storage::disk('public')->delete($record->avatar);
            }

            $record->delete();
            toaster()->success(__('Testimonial deleted successfully'));
        }

        $this->deleteId = null;
        $this->showDeleteModal = false;
    }

    public function render()
    {
        $testimonials = Testimonial::query()
            ->when($this->search, function ($query) {
                $like = '%' . trim($this->search) . '%';
                $query->where('name', 'like', $like)
                    ->orWhere('company', 'like', $like)
                    ->orWhere('testimonial', 'like', $like);
            })
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.admin.frontend.testimonials', [
            'testimonials' => $testimonials,
        ]);
    }
}
