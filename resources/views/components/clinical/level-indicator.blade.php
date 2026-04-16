@props(['label', 'value' => 0, 'color' => 'indigo'])

@php
    $safeValue = (int) ($value ?? 0);
    $percentage = ($safeValue / 10) * 100;
    $colorClasses = [
        'indigo' => 'bg-indigo-500',
        'blue' => 'bg-blue-500',
        'orange' => 'bg-orange-500',
        'amber' => 'bg-amber-500',
        'red' => 'bg-red-500',
        'emerald' => 'bg-emerald-500',
    ][$color] ?? 'bg-zinc-500';
@endphp

<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    <div class="flex justify-between items-end">
        <span class="text-[10px] uppercase font-bold text-zinc-500 tracking-tight">{{ $label }}</span>
        <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300">{{ $value }}/10</span>
    </div>
    <div class="h-1.5 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden flex">
        <div 
            x-data 
            :style="{ width: '{{ $percentage }}%' }" 
            class="{{ $colorClasses }} h-full transition-all duration-500 shadow-xs"
        ></div>
    </div>
</div>
