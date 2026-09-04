@props(['status'])

@php
    $map = [
        'open'        => ['label' => __('Open'),        'class' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300'],
        'in_progress' => ['label' => __('In Progress'), 'class' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300'],
        'resolved'    => ['label' => __('Resolved'),    'class' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300'],
        'closed'      => ['label' => __('Closed'),      'class' => 'bg-zinc-100 text-zinc-700 dark:bg-(--default-element-light-bg-color) dark:text-zinc-300'],
    ];
    $meta = $map[$status] ?? ['label' => ucfirst(str_replace('_', ' ', $status)), 'class' => 'bg-zinc-100 text-zinc-700 dark:bg-(--default-element-light-bg-color) dark:text-zinc-300'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-3 py-1 text-xs font-medium rounded-xl ' . $meta['class']]) }}>
    {{ $meta['label'] }}
</span>
