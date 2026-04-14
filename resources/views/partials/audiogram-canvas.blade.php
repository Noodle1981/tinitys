<div class="audiogram-wrapper bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-4 shadow-sm" x-data="audiogramEntry(@js($initialData))">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
        <div class="flex items-center gap-2">
            <button type="button" 
                @click="setEar('right')" 
                :class="activeEar === 'right' ? 'bg-red-50 text-red-700 border-red-200 ring-2 ring-red-100' : 'bg-zinc-50 text-zinc-600 border-zinc-200'"
                class="flex items-center gap-2 px-4 py-2 rounded-lg border text-sm font-semibold transition-all">
                <svg width="14" height="14" viewBox="0 0 13 13"><circle cx="6.5" cy="6.5" r="5" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                Oído derecho
            </button>
            <button type="button" 
                @click="setEar('left')" 
                :class="activeEar === 'left' ? 'bg-blue-50 text-blue-700 border-blue-200 ring-2 ring-blue-100' : 'bg-zinc-50 text-zinc-600 border-zinc-200'"
                class="flex items-center gap-2 px-4 py-2 rounded-lg border text-sm font-semibold transition-all">
                <svg width="14" height="14" viewBox="0 0 13 13"><line x1="1" y1="1" x2="12" y2="12" stroke="currentColor" stroke-width="2"/><line x1="12" y1="1" x2="1" y2="12" stroke="currentColor" stroke-width="2"/></svg>
                Oído izquierdo
            </button>
        </div>

        <div class="flex items-center gap-2" x-show="!isReadOnly">
            <button type="button" @click="undoLast()" class="px-3 py-1.5 rounded-lg border border-zinc-200 text-xs font-medium text-zinc-600 hover:bg-zinc-50">↩ Deshacer</button>
            <button type="button" @click="clearEar()" class="px-3 py-1.5 rounded-lg border border-zinc-200 text-xs font-medium text-zinc-600 hover:bg-zinc-50">Limpiar oído</button>
            <button type="button" @click="clearAll()" class="px-3 py-1.5 rounded-lg border border-zinc-200 text-xs font-medium text-zinc-600 hover:bg-zinc-50">Limpiar todo</button>
        </div>
    </div>

    <div class="relative bg-white border border-zinc-100 rounded-lg overflow-hidden mb-4" wire:ignore>
        <canvas x-ref="audiogramCanvas" @click="onClick" @mousemove="onMove" @mouseleave="hov = null; draw()" class="block w-full cursor-crosshair"></canvas>
    </div>

    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-zinc-500">
        <div class="flex items-center gap-1.5">
            <div class="w-2.5 h-2.5 rounded-full border-2 border-[#c0392b]"></div>
            <span>Derecho (CA)</span>
        </div>
        <div class="flex items-center gap-1.5">
            <div class="relative w-3 h-3 flex items-center justify-center">
                <div class="absolute w-full h-0.5 bg-[#2471a3] rotate-45"></div>
                <div class="absolute w-full h-0.5 bg-[#2471a3] -rotate-45"></div>
            </div>
            <span>Izquierdo (CA)</span>
        </div>
        <div class="flex items-center gap-1.5">
            <div class="w-4 h-3 bg-[rgba(80,160,100,0.12)] border border-[rgba(80,160,100,0.30)] rounded-sm"></div>
            <span>Banana del habla</span>
        </div>
        <div class="ml-auto italic opacity-75">Clic para registrar umbral · Clic sobre punto para eliminar</div>
    </div>

    @include('partials.audiogram-scripts')

    {{-- Botón de guardado oculto o integrado, se llamará desde el componente padre o directamente --}}
    <div class="mt-8 flex justify-end" x-show="!isReadOnly">
        <button type="button" @click="save()" class="px-6 py-2.5 bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 rounded-lg font-bold shadow-sm hover:opacity-90 transition-all flex items-center gap-2">
            <flux:icon.check-circle variant="mini" />
            Guardar Resultados de Audiometría
        </button>
    </div>
</div>
