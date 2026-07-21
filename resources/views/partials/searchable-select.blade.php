@props([
    'model',
    'options',
    'placeholder' => 'Pilih...',
    'emptyLabel' => 'Semua',
    'allowEmpty' => true,
    'id' => null,
])

@php
    $id = $id ?? 'ss-' . $model;
    $items = collect($options)->map(fn ($o) => [
        'slug' => $o->slug,
        'name' => $o->name,
    ])->values();
@endphp

<div
    x-data="{
        open: false,
        search: '',
        selected: @entangle($model).live,
        options: {{ Js::from($items) }},
        emptyLabel: @js($emptyLabel),
        allowEmpty: @js($allowEmpty),
        get filtered() {
            const q = this.search.trim().toLowerCase();
            if (!q) return this.options;
            return this.options.filter(o => o.name.toLowerCase().includes(q));
        },
        get label() {
            if (!this.selected) return this.allowEmpty ? this.emptyLabel : @js($placeholder);
            const hit = this.options.find(o => o.slug === this.selected);
            return hit ? hit.name : @js($placeholder);
        },
        pick(slug) {
            this.selected = slug;
            this.open = false;
            this.search = '';
        },
        clear() {
            this.selected = '';
            this.open = false;
            this.search = '';
        },
        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.$nextTick(() => this.$refs.input?.focus());
            } else {
                this.search = '';
            }
        },
    }"
    @click.outside="open = false; search = ''"
    @keydown.escape.window="open = false; search = ''"
    class="relative"
    :class="open && 'z-50'"
    {{ $attributes }}
>
    <button
        type="button"
        id="{{ $id }}"
        @click="toggle()"
        :aria-expanded="open"
        class="flex w-full items-center justify-between gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-left text-sm font-medium text-slate-900 outline-none transition focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20"
    >
        <span class="truncate" :class="selected ? 'text-slate-900' : 'text-slate-400'" x-text="label"></span>
        <svg class="h-4 w-4 shrink-0 text-slate-400 transition" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute left-0 right-0 z-50 mt-1 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl shadow-slate-900/15"
    >
        <div class="border-b border-slate-100 p-2">
            <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 focus-within:border-brand-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-brand-500/20">
                <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                </svg>
                <input
                    x-ref="input"
                    type="text"
                    x-model="search"
                    @keydown.enter.prevent="filtered[0] && pick(filtered[0].slug)"
                    placeholder="Ketik untuk cari..."
                    class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-slate-900 outline-none ring-0 placeholder:text-slate-400"
                >
            </div>
        </div>
        <ul class="max-h-56 overflow-y-auto py-1" role="listbox">
            <template x-if="allowEmpty">
                <li>
                    <button
                        type="button"
                        @click="clear()"
                        class="flex w-full px-3 py-2 text-left text-sm text-slate-500 hover:bg-brand-50 hover:text-brand-800"
                        :class="!selected && 'bg-brand-50 font-semibold text-brand-800'"
                        x-text="emptyLabel"
                    ></button>
                </li>
            </template>
            <template x-for="opt in filtered" :key="opt.slug">
                <li>
                    <button
                        type="button"
                        @click="pick(opt.slug)"
                        class="flex w-full px-3 py-2 text-left text-sm text-slate-800 hover:bg-brand-50 hover:text-brand-800"
                        :class="selected === opt.slug && 'bg-brand-50 font-semibold text-brand-800'"
                        x-text="opt.name"
                    ></button>
                </li>
            </template>
            <li x-show="filtered.length === 0" class="px-3 py-3 text-center text-sm text-slate-400">
                Tidak ada hasil
            </li>
        </ul>
    </div>
</div>
