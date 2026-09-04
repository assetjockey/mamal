<?php

namespace App\Livewire\Admin\General;

use App\Models\Prompt;
use Livewire\Component;
use Livewire\WithPagination;

class PromptsManager extends Component
{
    use WithPagination;

    /** Active studio tab: image | video. */
    public string $tab = 'image';

    public string $search = '';

    // Editor form
    public bool $showModal = false;
    public ?int $promptId = null;
    public string $title = '';
    public string $body = '';
    public string $type = 'image';

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:160',
            'body'  => 'required|string|max:4000',
            'type'  => 'required|in:image,video',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['image', 'video'], true) ? $tab : 'image';
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['promptId', 'title', 'body']);
        $this->type = $this->tab;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        // Admins manage global prompts they authored.
        $prompt = Prompt::where('is_global', true)->findOrFail($id);

        $this->promptId = $prompt->id;
        $this->title = $prompt->title;
        $this->body = $prompt->body;
        $this->type = $prompt->type;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->promptId) {
            $prompt = Prompt::where('is_global', true)->findOrFail($this->promptId);
            $prompt->update([
                'title' => $data['title'],
                'body'  => $data['body'],
                'type'  => $data['type'],
            ]);
        } else {
            Prompt::create([
                'user_id'   => auth()->id(),
                'title'     => $data['title'],
                'body'      => $data['body'],
                'type'      => $data['type'],
                'is_global' => true,
            ]);
        }

        $this->tab = $data['type'];
        $this->showModal = false;
        $this->reset(['promptId', 'title', 'body']);

        toaster()->success(
            $this->promptId ? __('Prompt updated successfully') : __('Prompt created successfully')
        );
    }

    public function delete(int $id): void
    {
        Prompt::where('is_global', true)->whereKey($id)->delete();
        toaster()->success(__('Prompt deleted successfully'));
    }

    public function render()
    {
        $prompts = Prompt::query()
            ->where('is_global', true)
            ->where('type', $this->tab)
            ->when($this->search, function ($query) {
                $like = '%' . trim($this->search) . '%';
                $query->where(function ($q) use ($like) {
                    $q->where('title', 'like', $like)
                      ->orWhere('body', 'like', $like);
                });
            })
            ->orderByDesc('id')
            ->paginate(8);

        return view('livewire.admin.general.prompts-manager', [
            'prompts'     => $prompts,
            'imageCount'  => Prompt::where('is_global', true)->where('type', 'image')->count(),
            'videoCount'  => Prompt::where('is_global', true)->where('type', 'video')->count(),
        ]);
    }
}
