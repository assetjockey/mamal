{{--
    Flash message renderer for the language manager.

    Action results from the language-manager routes (publish all, generate
    JSON, reinstall, set default language) are flashed to the session under
    the keys below. The package's own success key is configurable via
    config/elseyyid-location.php (`message_success_variable`) — we read that
    too so vendor-controller actions keep working.

    Why we surface these as JS toasts instead of inline Flux callouts:
    those actions redirect with a full page reload, and the server-side
    Masmerise relay / callout rendering was not reliably showing on these
    plain-route pages. The dashboard-wide toaster (`<x-toaster-hub />` from
    the sidebar layout) listens for the `toaster:received` browser event —
    the same mechanism the language-toggle checkbox uses successfully — so
    we re-emit every flashed message through it. Validation errors stay as
    an inline callout since they read better next to the forms that produced
    them.
--}}

@php
    /** Map session flash keys -> toaster types. Includes the package's configurable success key. */
    $successKey = config('elseyyid-location.message_success_variable', 'flash_success');

    $flashMap = [
        $successKey      => 'success',
        'flash_success'  => 'success',
        'flash_danger'   => 'error',
        'flash_warning'  => 'warning',
        'flash_info'     => 'info',
        'flash_message'  => 'info',
    ];

    $toasts = [];
    foreach ($flashMap as $key => $type) {
        if ($message = session()->get($key)) {
            $toasts[] = [
                'type'    => $type,
                'message' => is_string($message) ? $message : json_encode($message),
            ];
        }
    }
@endphp

@if ($errors->any())
    <flux:callout variant="danger" icon="x-circle" class="mb-4">
        <flux:callout.heading>{{ __('We could not save your changes') }}</flux:callout.heading>
        <flux:callout.text>
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </flux:callout.text>
    </flux:callout>
@endif

@if (! empty($toasts))
    <script>
        /*
         * Re-emit server-flashed status messages through the dashboard toaster.
         * We wait for window `load` and add a short delay so the toaster hub's
         * Alpine listener is guaranteed to be attached before we dispatch — the
         * messages only exist right after a full-page action redirect, so a
         * hard `load` event always fires here.
         */
        (function () {
            const toasts = @json($toasts);
            const emit = function () {
                toasts.forEach(function (toast) {
                    document.dispatchEvent(new CustomEvent('toaster:received', {
                        detail: { type: toast.type, message: toast.message },
                    }));
                });
            };
            if (document.readyState === 'complete') {
                setTimeout(emit, 250);
            } else {
                window.addEventListener('load', function () { setTimeout(emit, 250); });
            }
        })();
    </script>
@endif
