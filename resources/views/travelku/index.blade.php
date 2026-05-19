@extends('layouts.travelku')

@section('content')
<div
    class="h-dvh flex overflow-hidden bg-slate-100 font-sans"
    x-data="travelKu({
        packages: @js($packages),
        statuses: @js($statuses),
        statusTransitions: @js($statusTransitions),
        bookings: @js($bookings),
        validateUrl: @js(route('bookings.validate')),
        bookingsUrl: @js(url('/bookings')),
        csrfToken: @js(csrf_token()),
    })"
>
    {{-- Toast --}}
    <div
        x-show="toast"
        x-transition
        x-cloak
        class="fixed top-3 right-3 sm:top-4 sm:right-4 z-[9999] flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-sm font-medium max-w-[calc(100vw-1.5rem)]"
        :class="toast?.type === 'success' ? 'bg-emerald-600 text-white' : 'bg-slate-700 text-white'"
    >
        <svg x-show="toast?.type === 'success'" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <svg x-show="toast && toast.type !== 'success'" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <span x-text="toast?.msg"></span>
    </div>

    {{-- Mobile overlay --}}
    <div
        x-show="mobileNav"
        x-transition.opacity
        @click="mobileNav = false"
        class="fixed inset-0 bg-black/50 z-40 md:hidden"
        x-cloak
    ></div>

    {{-- Sidebar --}}
    <aside
        class="w-56 shrink-0 bg-teal-900 flex flex-col fixed md:relative inset-y-0 left-0 z-50 h-dvh transform transition-transform duration-200 md:translate-x-0"
        :class="mobileNav ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
    >
        <div class="px-4 sm:px-5 py-5 border-b border-teal-800 shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-teal-400 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-teal-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-white font-extrabold text-base leading-none tracking-tight truncate">TravelKu</p>
                    <p class="text-teal-400 text-[10px] leading-none mt-0.5 truncate">Sistem Agen Perjalanan</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 min-h-0 px-3 py-3 space-y-1 overflow-y-auto">
            <button type="button" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium bg-teal-700 text-white text-left">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Manajemen Booking
            </button>
            <button type="button" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-teal-300 hover:bg-teal-800 hover:text-white opacity-60 text-left transition-colors">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Data Pelanggan
            </button>
            <button type="button" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-teal-300 hover:bg-teal-800 hover:text-white opacity-60 text-left transition-colors">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Paket Wisata
            </button>
            <button type="button" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-teal-300 hover:bg-teal-800 hover:text-white opacity-60 text-left transition-colors">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Laporan
            </button>
        </nav>

        <div class="px-4 py-3 border-t border-teal-800 shrink-0">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-teal-600 flex items-center justify-center text-xs text-white font-bold shrink-0">ST</div>
                <div class="min-w-0">
                    <p class="text-white text-xs font-medium truncate">Staff Agen</p>
                    <p class="text-teal-400 text-[10px] truncate">Internal System</p>
                </div>
            </div>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex-1 flex flex-col min-w-0 min-h-0 h-dvh">
        <header class="bg-white border-b border-slate-200 px-3 sm:px-5 py-3 flex items-center justify-between gap-2 shrink-0">
            <div class="flex items-center gap-2 min-w-0">
                <button type="button" @click="mobileNav = !mobileNav" class="md:hidden p-2 -ml-1 rounded-lg hover:bg-slate-100 text-slate-600 shrink-0" aria-label="Menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="min-w-0">
                    <h1 class="font-bold text-slate-800 text-sm sm:text-lg truncate">Manajemen Pemesanan</h1>
                    <p class="text-slate-400 text-[11px] sm:text-xs truncate hidden sm:block">Kelola pemesanan paket wisata</p>
                </div>
            </div>
            <button type="button" @click="openAdd()"
                class="flex items-center gap-1.5 bg-teal-700 hover:bg-teal-800 text-white px-3 py-2 rounded-xl font-semibold text-sm shadow-sm transition-colors shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span class="hidden sm:inline">Tambah Pemesanan</span>
                <span class="sm:hidden">Tambah</span>
            </button>
        </header>

        <main class="flex-1 flex flex-col min-h-0 gap-3 p-3 sm:p-4 overflow-hidden">
            <div class="shrink-0">
                @include('travelku.partials.summary')
            </div>
            <div class="shrink-0">
                @include('travelku.partials.filters')
            </div>
            <div class="flex-1 min-h-0 flex flex-col">
                @include('travelku.partials.table')
            </div>
        </main>
    </div>

    @include('travelku.partials.modals')
</div>
@endsection
