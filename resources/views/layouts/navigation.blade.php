<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <x-nav-link :href="route('leaderboard.index')" :active="request()->routeIs('leaderboard.index')">
                        {{ __('Leaderboard') }}
                    </x-nav-link>

                    <x-nav-link :href="route('mailbox.index')" :active="request()->routeIs('mailbox.*')">
                        {{ __('Mailbox') }}
                    </x-nav-link>

                    <x-nav-link :href="route('partners.index')" :active="request()->routeIs('partners.*')">
                        {{ __('Data Mitra') }}
                    </x-nav-link>
                    @if(Auth::user()->role === 'mitra')
                    <x-nav-link :href="route('vendor.reports.index')" :active="request()->routeIs('vendor.reports.*')">
                        {{ __('QC Tracker') }}
                    </x-nav-link>
                    <x-nav-link :href="route('vendor.payments.index')" :active="request()->routeIs('vendor.payments.*')">
                        {{ __('Pembayaran') }}
                    </x-nav-link>
                    @endif
                    @if(in_array(Auth::user()->role ?? '', ['superadmin', 'admin']))
                    <x-nav-link :href="route('rekruter.index')" :active="request()->routeIs('rekruter.*')">
                        {{ __('Rekruter') }}
                    </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Language Switcher -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-language-switcher />
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div class="flex items-center">
                                {{ Auth::user()->name }}
                                @if(isset(Auth::user()->partner) && Auth::user()->partner->is_vip)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold tracking-wide text-white bg-gradient-to-r from-[#4CA5FF] to-[#60E0FF] border-0 ml-2 uppercase shadow-[0_4px_12px_rgba(76,165,255,0.4)]">
                                    <svg class="w-3 h-3 text-white drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.381z" clip-rule="evenodd"/></svg>
                                    VIP MEMBER
                                </span>
                                @endif
                            </div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('common.logout') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('leaderboard.index')" :active="request()->requestIs('leaderboard.index')">
                {{ __('Leaderboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('mailbox.index')" :active="request()->routeIs('mailbox.*')">
                {{ __('Mailbox') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('partners.index')" :active="request()->routeIs('partners.*')">
                {{ __('Data Mitra') }}
            </x-responsive-nav-link>
            @if(Auth::user()->role === 'mitra')
            <x-responsive-nav-link :href="route('vendor.reports.index')" :active="request()->routeIs('vendor.reports.*')">
                {{ __('QC Tracker') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('vendor.payments.index')" :active="request()->routeIs('vendor.payments.*')">
                {{ __('Pembayaran') }}
            </x-responsive-nav-link>
            @endif
            @if(in_array(Auth::user()->role ?? '', ['superadmin', 'admin']))
            <x-responsive-nav-link :href="route('rekruter.index')" :active="request()->routeIs('rekruter.*')">
                {{ __('Rekruter') }}
            </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 flex items-center">
                    {{ Auth::user()->name }}
                    @if(isset(Auth::user()->partner) && Auth::user()->partner->is_vip)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold tracking-wide text-white bg-gradient-to-r from-[#4CA5FF] to-[#60E0FF] border-0 ml-2 uppercase shadow-[0_4px_12px_rgba(76,165,255,0.4)]">
                        <svg class="w-3 h-3 text-white drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.381z" clip-rule="evenodd"/></svg>
                        VIP MEMBER
                    </span>
                    @endif
                </div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Mobile Language Switcher (Simple Form) -->
                <div class="px-4 py-2">
                    <form method="POST" action="{{ route('locale.switch') }}" class="flex space-x-2">
                        @csrf
                        <input type="hidden" name="locale" value="{{ app()->getLocale() === 'id' ? 'en' : 'id' }}">
                        <button type="submit" class="text-sm font-medium text-gray-500 hover:text-gray-700">
                            {{ app()->getLocale() === 'id' ? '🇬🇧 Switch to English' : '🇮🇩 Ganti ke Bahasa Indonesia' }}
                        </button>
                    </form>
                </div>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('common.logout') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
