@props(['label', 'value' => 0, 'icon' => 'square-3-stack-3d', 'color' => 'indigo'])

@php
    $colorClasses = [
        'indigo' => 'text-indigo-600 bg-indigo-50 dark:bg-indigo-900/20 dark:text-indigo-400',
        'red' => 'text-red-600 bg-red-50 dark:bg-red-900/20 dark:text-red-400',
        'blue' => 'text-blue-600 bg-blue-50 dark:bg-blue-900/20 dark:text-blue-400',
        'amber' => 'text-amber-600 bg-amber-50 dark:bg-amber-900/20 dark:text-amber-400',
    ][$color] ?? 'text-zinc-600 bg-zinc-50';
@endphp

<div class="flex flex-col items-center justify-center p-3 rounded-xl border border-zinc-200/60 dark:border-zinc-700/60 bg-white dark:bg-zinc-900 shadow-xs">
    <div class="p-2 rounded-full mb-2 {{ $colorClasses }}">
        <flux:icon :name="$icon" variant="mini" class="size-4" />
    </div>
    <p class="text-[9px] uppercase font-bold text-zinc-400 tracking-tight text-center">{{ $label }}</p>
    <p class="text-lg font-black text-zinc-900 dark:text-white leading-none mt-1">{{ $value }}</p>
</div>
