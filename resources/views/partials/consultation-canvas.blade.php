<div class="audiogram-wrapper bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm" 
     x-data="consultationViewer(@js($this->getConsultationData()))">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-lg font-bold text-zinc-900 dark:text-white flex items-center gap-2">
                <flux:icon.presentation-chart-line variant="mini" class="text-indigo-500" />
                Visualización Clínica / Gemelo Digital
            </h3>
            <p class="text-xs text-zinc-500">Superposición de umbrales clínicos y zonas de enmascaramiento por tinnitus.</p>
        </div>

        <div class="flex flex-wrap items-center gap-4 text-[10px] uppercase tracking-wider font-bold">
            <div class="flex items-center gap-1.5 text-red-600">
                <div class="w-3 h-3 rounded-full border-2 border-red-600 bg-white"></div>
                <span>Derecho (O)</span>
            </div>
            <div class="flex items-center gap-1.5 text-blue-600">
                <div class="relative w-3 h-3 flex items-center justify-center">
                    <div class="absolute w-full h-0.5 bg-blue-600 rotate-45"></div>
                    <div class="absolute w-full h-0.5 bg-blue-600 -rotate-45"></div>
                </div>
                <span>Izquierdo (X)</span>
            </div>
            <div class="flex items-center gap-1.5 text-amber-500/70">
                <div class="w-4 h-2 bg-amber-500/20 border border-amber-500/30 rounded-sm"></div>
                <span>Banana del habla</span>
            </div>
            <div class="flex items-center gap-1.5 text-red-500/70">
                <div class="w-3 h-3 bg-red-500/10 border border-red-500/30 rounded-sm"></div>
                <span>Tinnitus OD</span>
            </div>
            <div class="flex items-center gap-1.5 text-blue-500/70">
                <div class="w-3 h-3 bg-blue-500/10 border border-blue-100 dark:border-blue-800 rounded-sm"></div>
                <span>Tinnitus OI</span>
            </div>
        </div>
    </div>

    <div class="relative bg-white border border-zinc-100 rounded-lg overflow-hidden" wire:ignore>
        <canvas x-ref="consultationCanvas" class="block w-full"></canvas>
    </div>

    @include('partials.consultation-canvas-scripts')
</div>
