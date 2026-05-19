@extends('layouts.travelku')

@section('content')
<div
    class="h-dvh flex overflow-hidden bg-slate-100 font-sans"
    x-data="travelPackages({
        packages: @js($packages),
        categories: @js($categories),
        packagesUrl: @js(url('/packages')),
        csrfToken: @js(csrf_token()),
    })"
>
    @include('travelku.partials.toast')

    <div
        x-show="mobileNav"
        x-transition.opacity
        @click="mobileNav = false"
        class="fixed inset-0 bg-black/50 z-40 md:hidden"
        x-cloak
    ></div>

    @include('travelku.partials.sidebar', ['activeMenu' => 'packages'])

    <div class="flex-1 flex flex-col min-w-0 min-h-0 h-dvh">
        <header class="bg-white border-b border-slate-200 px-3 sm:px-5 py-3 flex items-center justify-between gap-2 shrink-0">
            <div class="flex items-center gap-2 min-w-0">
                <button type="button" @click="mobileNav = !mobileNav" class="md:hidden p-2 -ml-1 rounded-lg hover:bg-slate-100 text-slate-600 shrink-0" aria-label="Menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="min-w-0">
                    <h1 class="font-bold text-slate-800 text-sm sm:text-lg truncate">Paket Wisata</h1>
                    <p class="text-slate-400 text-[11px] sm:text-xs truncate hidden sm:block">Kelola daftar paket perjalanan</p>
                </div>
            </div>
            <button type="button" @click="openAdd()"
                class="flex items-center gap-1.5 bg-teal-700 hover:bg-teal-800 text-white px-3 py-2 rounded-xl font-semibold text-sm shadow-sm transition-colors shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span class="hidden sm:inline">Tambah Paket</span>
                <span class="sm:hidden">Tambah</span>
            </button>
        </header>

        <main class="flex-1 flex flex-col min-h-0 gap-3 p-3 sm:p-4 overflow-hidden">
            <div class="shrink-0">
                @include('travelku.packages.partials.summary')
            </div>
            <div class="shrink-0">
                @include('travelku.packages.partials.filters')
            </div>
            <div class="flex-1 min-h-0 flex flex-col">
                @include('travelku.packages.partials.table')
            </div>
        </main>
    </div>

    @include('travelku.packages.partials.modals')
</div>
@endsection
