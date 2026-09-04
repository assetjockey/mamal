@php
    $steps = [
        ['label' => 'Welcome', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />'],
        ['label' => 'Requirements', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />'],
        ['label' => 'Permissions', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />'],
        ['label' => 'Database', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75" />'],
        ['label' => 'Activation', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />'],
        ['label' => 'Complete', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />'],
    ];
@endphp

<div class="mb-8 w-full">
    <div class="flex items-center justify-between">
        @foreach($steps as $index => $step)
            @php
                $stepNum = $index + 1;
                $isActive = $stepNum === $currentStep;
                $isCompleted = $stepNum < $currentStep;
            @endphp

            {{-- Step circle + label --}}
            <div class="flex flex-col items-center gap-1.5">
                <div @class([
                    'flex h-10 w-10 items-center justify-center rounded-full border-2 transition-all duration-300',
                    'border-indigo-600 bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' => $isActive,
                    'border-emerald-500 bg-emerald-500 text-white' => $isCompleted,
                    'border-slate-200 bg-white text-slate-400 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-500' => !$isActive && !$isCompleted,
                ])>
                    @if($isCompleted)
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    @else
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            {!! $step['icon'] !!}
                        </svg>
                    @endif
                </div>
                <span @class([
                    'text-xs font-medium hidden sm:block',
                    'text-indigo-700 dark:text-indigo-400' => $isActive,
                    'text-emerald-600 dark:text-emerald-400' => $isCompleted,
                    'text-slate-400 dark:text-neutral-500' => !$isActive && !$isCompleted,
                ])>
                    {{ __($step['label']) }}
                </span>
            </div>

            {{-- Connector line --}}
            @if(!$loop->last)
                <div @class([
                    'mx-1 h-0.5 flex-1 rounded-full transition-all duration-300 sm:mx-2',
                    'bg-emerald-500' => $isCompleted,
                    'bg-slate-200 dark:bg-neutral-700' => !$isCompleted,
                ])></div>
            @endif
        @endforeach
    </div>
</div>
