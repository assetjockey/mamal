<?php

namespace App\Livewire\User\Prompts;

use App\Models\Prompt;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class PromptLibrary extends Component
{
    /** Studio context this library was opened from: image | video. Sets the default tab. */
    public string $context = 'image';

    /** Active studio tab inside the modal: image | video. */
    public string $tab = 'image';

    /** Source filter: all | mine | favorites. */
    public string $filter = 'all';

    public string $search = '';

    // Inline create form
    public bool $showCreate = false;
    public string $newTitle = '';
    public string $newBody = '';

    protected function rules(): array
    {
        return [
            'newTitle' => 'required|string|max:160',
            'newBody'  => 'required|string|max:4000',
        ];
    }

    public function mount(string $context = 'image'): void
    {
        $this->context = in_array($context, ['image', 'video'], true) ? $context : 'image';
        $this->tab = $this->context;
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['image', 'video'], true) ? $tab : 'image';
        $this->showCreate = false;
        $this->resetValidation();
    }

    public function setFilter(string $filter): void
    {
        $this->filter = in_array($filter, ['all', 'mine', 'favorites'], true) ? $filter : 'all';
    }

    public function toggleCreate(): void
    {
        $this->showCreate = ! $this->showCreate;
        $this->reset(['newTitle', 'newBody']);
        $this->resetValidation();
    }

    public function saveNew(): void
    {
        $data = $this->validate();

        Prompt::create([
            'user_id'   => auth()->id(),
            'type'      => $this->tab,
            'title'     => $data['newTitle'],
            'body'      => $data['newBody'],
            'is_global' => false,
        ]);

        $this->reset(['newTitle', 'newBody']);
        $this->showCreate = false;
        $this->filter = 'mine';

        Toaster::success(__('Prompt saved to your library.'));
    }

    public function toggleFavorite(int $id): void
    {
        $prompt = Prompt::visibleTo(auth()->id())->findOrFail($id);

        $prompt->favoritedBy()->toggle(auth()->id());
    }

    public function delete(int $id): void
    {
        // Users may only delete their own, non-global prompts.
        $prompt = Prompt::where('user_id', auth()->id())
            ->where('is_global', false)
            ->findOrFail($id);

        $prompt->delete();

        Toaster::success(__('Prompt deleted.'));
    }

    public function use(int $id): void
    {
        $prompt = Prompt::visibleTo(auth()->id())->findOrFail($id);

        $this->dispatch('prompt-selected', body: $prompt->body);
    }

    public function render()
    {
        $userId = auth()->id();

        $favoriteIds = \DB::table('prompt_favorites')
            ->where('user_id', $userId)
            ->pluck('prompt_id')
            ->all();

        $query = Prompt::visibleTo($userId)->ofType($this->tab);

        if ($this->filter === 'mine') {
            $query->where('user_id', $userId)->where('is_global', false);
        } elseif ($this->filter === 'favorites') {
            $query->whereIn('id', $favoriteIds ?: [0]);
        }

        if (trim($this->search) !== '') {
            $like = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($like) {
                $q->where('title', 'like', $like)
                  ->orWhere('body', 'like', $like);
            });
        }

        $prompts = $query
            ->orderByDesc('is_global')
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.user.prompts.prompt-library', [
            'prompts'     => $prompts,
            'favoriteIds' => $favoriteIds,
            'myCount'     => Prompt::where('user_id', $userId)->where('is_global', false)->ofType($this->tab)->count(),
            'favCount'    => count($favoriteIds),
        ]);
    }
}
