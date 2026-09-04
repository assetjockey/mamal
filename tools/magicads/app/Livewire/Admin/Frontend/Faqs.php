<?php

namespace App\Livewire\Admin\Frontend;

use App\Models\Faq;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('FAQs Manager')]
class Faqs extends Component
{
    use WithPagination;

    public $search = '';

    public ?int $faqId = null;
    public $question = '';
    public $answer = '';
    public $status = 'active';

    public bool $showModal = false;
    public ?int $deleteId = null;

    protected function rules(): array
    {
        return [
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Open the modal in "create" mode with a blank form.
     */
    public function create(): void
    {
        $this->reset(['faqId', 'question', 'answer']);
        $this->status = 'active';
        $this->resetValidation();
        $this->showModal = true;
    }

    /**
     * Open the modal in "edit" mode, hydrated from the selected record.
     */
    public function edit(int $id): void
    {
        $faq = Faq::findOrFail($id);

        $this->faqId = $faq->id;
        $this->question = $faq->question;
        $this->answer = $faq->answer;
        $this->status = $faq->status;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        Faq::updateOrCreate(
            ['id' => $this->faqId],
            $data
        );

        $this->showModal = false;
        $this->reset(['faqId', 'question', 'answer']);
        $this->status = 'active';

        toaster()->success(
            $this->faqId
                ? __('FAQ updated successfully')
                : __('FAQ created successfully')
        );
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            Faq::whereKey($this->deleteId)->delete();
            toaster()->success(__('FAQ deleted successfully'));
        }

        $this->deleteId = null;
    }

    public function render()
    {
        $faqs = Faq::query()
            ->when($this->search, function ($query) {
                $like = '%' . trim($this->search) . '%';
                $query->where('question', 'like', $like)
                    ->orWhere('answer', 'like', $like);
            })
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.admin.frontend.faqs', [
            'faqs' => $faqs,
        ]);
    }
}
