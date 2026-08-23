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
        class="flex w-full items-center justify-between gap-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3.5 py-2.5 text-left text-xs font-semibold text-zinc-900 dark:text-zinc-100 outline-none transition hover:border-zinc-300 dark:hover:border-zinc-600 focus:border-zinc-900 dark:focus:border-zinc-400 focus:ring-1 focus:ring-zinc-900 dark:focus:ring-zinc-400"
    >
        <span class="truncate" :class="selected ? 'text-zinc-950 dark:text-zinc-100 font-bold' : 'text-zinc-500 dark:text-zinc-400 font-normal'" x-text="label"></span>
        <i class="fa-solid fa-chevron-down text-[10px] text-zinc-400 dark:text-zinc-500 transition-transform duration-200" :class="open && 'rotate-180'"></i>
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
        class="absolute left-0 right-0 z-50 mt-1 overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 shadow-xl shadow-zinc-900/10 dark:shadow-black/40"
    >
        <div class="border-b border-zinc-100 dark:border-zinc-700/80 p-2 bg-zinc-50 dark:bg-zinc-900/60">
            <div class="flex items-center gap-2 rounded-md border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-2.5 py-1.5 focus-within:border-zinc-900 dark:focus-within:border-zinc-400">
                <i class="fa-solid fa-magnifying-glass text-[11px] text-zinc-400 dark:text-zinc-500"></i>
                <input
                    x-ref="input"
                    type="text"
                    x-model="search"
                    @keydown.enter.prevent="filtered[0] && pick(filtered[0].slug)"
                    placeholder="Ketik nama..."
                    class="min-w-0 flex-1 border-0 bg-transparent p-0 text-xs text-zinc-900 dark:text-zinc-100 outline-none ring-0 placeholder:text-zinc-400 dark:placeholder:text-zinc-500"
                >
            </div>
        </div>
        <ul class="max-h-56 overflow-y-auto py-1 text-xs" role="listbox">
            <template x-if="allowEmpty">
                <li>
                    <button
                        type="button"
                        @click="clear()"
                        class="flex w-full items-center justify-between px-3.5 py-2 text-left text-zinc-600 dark:text-zinc-300 transition hover:bg-zinc-50 dark:hover:bg-zinc-700/60"
                        :class="!selected && 'font-bold text-amber-800 dark:text-amber-400 bg-amber-50/50 dark:bg-amber-950/30'"
                    >
                        <span x-text="emptyLabel"></span>
                        <i x-show="!selected" class="fa-solid fa-check text-[11px] text-amber-800 dark:text-amber-400"></i>
                    </button>
                </li>
            </template>
            <template x-for="item in filtered" :key="item.slug">
                <li>
                    <button
                        type="button"
                        @click="pick(item.slug)"
                        class="flex w-full items-center justify-between px-3.5 py-2 text-left text-zinc-800 dark:text-zinc-200 transition hover:bg-zinc-50 dark:hover:bg-zinc-700/60"
                        :class="selected === item.slug && 'font-bold text-amber-800 dark:text-amber-400 bg-amber-50/50 dark:bg-amber-950/30'"
                    >
                        <span x-text="item.name"></span>
                        <i x-show="selected === item.slug" class="fa-solid fa-check text-[11px] text-amber-800 dark:text-amber-400"></i>
                    </button>
                </li>
            </template>
            <li x-show="filtered.length === 0" class="px-3.5 py-3 text-center text-xs text-zinc-400 dark:text-zinc-500">
                Tidak ada pilihan yang cocok
            </li>
        </ul>
    </div>
</div>
