<?php

namespace App\Livewire\Admin\Backend;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailSetting;

#[Title('SMTP Settings')]
class Smtp extends Component
{
    public $host;
    public $port;
    public $username;
    public $password;
    public $sender;
    public $name;
    public $encryption = 'tls';

    public $testEmail;
    public $testSubject;
    public $testMessage;

    protected $rules = [
        'host' => 'required|string',
        'port' => 'required|integer',
        'username' => 'required|string',
        'password' => 'required|string',
        'sender' => 'required|email',
        'name' => 'required|string',
        'encryption' => 'required|in:tls,ssl',
    ];

    public function mount()
    {
        $settings = EmailSetting::first();
        
        if ($settings) {
            $this->host = $settings->host;
            $this->port = $settings->port;
            $this->username = $settings->username;
            $this->password = $settings->password;
            $this->sender = $settings->from_address;
            $this->name = $settings->from_name;
            $this->encryption = $settings->encryption ?? 'tls';
        }
    }

    public function save()
    {
        $validated = $this->validate();

        // Normalize away stray whitespace before persisting so a pasted leading
        // space in the host (or elsewhere) can't break DNS resolution at send
        // time. AppServiceProvider::configureMailFromSettings() reads this row
        // on every boot for real sends.
        EmailSetting::updateOrCreate(
            ['id' => 1],
            [
                'driver' => 'smtp',
                'host' => trim($validated['host']),
                'port' => (int) trim((string) $validated['port']),
                'username' => trim($validated['username']),
                'password' => trim($validated['password']),
                'encryption' => $validated['encryption'],
                'from_address' => trim($validated['sender']),
                'from_name' => trim($validated['name']),
            ]
        );

        toaster()->success(__('SMTP settings saved successfully'));
    }


    public function checkConnection()
    {
        // Validate the test fields AND the SMTP credentials themselves, so the
        // test always runs against a complete config.
        $this->validate(array_merge($this->rules, [
            'testEmail' => 'required|email',
            'testSubject' => 'required|string',
            'testMessage' => 'required|string|max:500',
        ]));

        // Apply the values *currently entered on the form* to the runtime mail
        // config, so the test reflects exactly what's on screen — even before
        // the admin has hit "Save". This mirrors the mapping in
        // AppServiceProvider::configureMailFromSettings() (panel stores
        // tls/ssl; Laravel 12's SMTP transport keys off `scheme`).
        $this->applyMailConfigFromForm();

        try {
            Mail::mailer('smtp')->raw($this->testMessage, function ($message) {
                $message->to($this->testEmail)
                        ->subject($this->testSubject);
            });

            toaster()->success(__('Test email successfully sent'));
            $this->reset(['testEmail', 'testSubject', 'testMessage']);
        } catch (\Throwable $e) {
            // Log the full exception and surface the real reason to the admin —
            // "auth failed", "connection refused", "certificate verify failed",
            // etc. — instead of a generic "settings are wrong" message that
            // hides genuinely-correct credentials failing on transport/TLS.
            report($e);

            toaster()->error(__('SMTP test failed:') . ' ' . $e->getMessage());
        }
    }

    /**
     * Push the form's SMTP values into the runtime mail config and reset any
     * already-resolved mailer so the next send picks them up. Kept in sync with
     * AppServiceProvider::configureMailFromSettings().
     */
    protected function applyMailConfigFromForm(): void
    {
        $scheme = strtolower((string) $this->encryption) === 'ssl' ? 'smtps' : 'smtp';

        // Trim stray whitespace (a leading/trailing space pasted into the host
        // is a classic cause of "getaddrinfo ... Host unknown"). Livewire's
        // data binding bypasses the TrimStrings HTTP middleware, so we normalize
        // here and in save() rather than relying on the request pipeline.
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.scheme' => $scheme,
            'mail.mailers.smtp.host' => trim((string) $this->host),
            'mail.mailers.smtp.port' => (int) trim((string) $this->port),
            'mail.mailers.smtp.username' => trim((string) $this->username),
            'mail.mailers.smtp.password' => trim((string) $this->password),
            'mail.from.address' => trim((string) $this->sender),
            'mail.from.name' => trim((string) $this->name),
        ]);

        // Force the MailManager to rebuild the "smtp" mailer with the config
        // above; otherwise a mailer resolved earlier in this request would keep
        // stale credentials.
        app()->forgetInstance('mail.manager');
        Mail::clearResolvedInstances();
    }

    public function render()
    {
        return view('livewire.admin.backend.smtp');
    }

}
