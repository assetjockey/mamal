<?php

namespace App\Livewire\Admin\Frontend;

use App\Models\PageSetting;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Pages')]
class Pages extends Component
{
    /** Which page is being edited: "privacy" or "terms". */
    public string $tab = 'privacy';

    public ?string $privacy_content = null;
    public ?string $terms_content = null;

    public function mount(): void
    {
        $settings = PageSetting::first();

        if ($settings) {
            $this->privacy_content = $settings->privacy_content;
            $this->terms_content = $settings->terms_content;
        }
    }

    protected function rules(): array
    {
        return [
            'privacy_content' => 'nullable|string',
            'terms_content' => 'nullable|string',
        ];
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['privacy', 'terms'], true)) {
            $this->tab = $tab;
        }
    }

    public function save(): void
    {
        $this->validate();

        $settings = PageSetting::firstOrNew(['id' => 1]);

        // Stamp the "last updated" date only for the page that actually
        // changed, so the frontend can show an accurate revision date.
        if ($this->tab === 'privacy') {
            if ($settings->privacy_content !== $this->privacy_content) {
                $settings->privacy_updated_at = now();
            }
            $settings->privacy_content = $this->privacy_content;
        } else {
            if ($settings->terms_content !== $this->terms_content) {
                $settings->terms_updated_at = now();
            }
            $settings->terms_content = $this->terms_content;
        }

        $settings->save();

        toaster()->success(
            $this->tab === 'privacy'
                ? __('Privacy Policy saved successfully')
                : __('Terms of Service saved successfully')
        );
    }

    public function render()
    {
        return view('livewire.admin.frontend.pages');
    }
}
