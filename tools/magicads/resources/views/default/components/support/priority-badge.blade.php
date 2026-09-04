@props(['priority'])

@php
    $map = [
        'low'    => ['label' => __('Low'),    'class' => 'bg-zinc-100 text-zinc-700 dark:bg-(--default-element-light-bg-color) dark:text-zinc-300'],
        'medium' => ['label' => __('Medium'), 'class' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300'],
        'high'   => ['label' => __('High'),   'class' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300'],
        'urgent' => ['label' => __('Urgent'), 'class' => 'bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300'],
    ];
    $meta = $map[$priority] ?? ['label' => ucfirst($priority), 'class' => 'bg-zinc-100 text-zinc-700 dark:bg-(--default-element-light-bg-color) dark:text-zinc-300'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide rounded-full ' . $meta['class']]) }}>
    {{ $meta['label'] }}
</span>
