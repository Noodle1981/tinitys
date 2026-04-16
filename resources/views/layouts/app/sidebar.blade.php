<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="desktop" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header class="flex items-center gap-2">
                <x-app-logo :sidebar="true" href="{{ route('patients.index') }}" wire:navigate />
                <flux:sidebar.collapse />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Gestión Clínica')">
                    <flux:sidebar.item icon="users" :href="route('patients.index')" :current="request()->routeIs('patients.index')" wire:navigate>
                        {{ __('Pacientes') }}
                    </flux:sidebar.item>



                    @if(auth()->user()->role === 'patient')
                        <flux:sidebar.item icon="layout-grid" :href="route('my-tinnitus')" :current="request()->routeIs('my-tinnitus')" wire:navigate>
                            {{ __('Mi Tinnitus') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Sistema')" class="mt-4">
                    <flux:sidebar.item icon="arrow-path" href="#">
                        {{ __('Gemelo Digital') }}
                        <flux:badge size="sm" color="indigo" class="ml-auto text-[8px] uppercase">Alpha</flux:badge>
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="cog" :href="route('profile.edit')" :current="request()->routeIs('profile.edit')" wire:navigate>
                    {{ __('Configuración') }}
                </flux:sidebar.item>
                
                <flux:sidebar.item icon="question-mark-circle" href="#">
                    {{ __('Ayuda') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <flux:separator />

            {{-- Perfil de Usuario en Desktop --}}
            <div class="p-3 hidden lg:block">
                <flux:dropdown position="right" align="bottom">
                    <flux:button variant="ghost" class="w-full px-2! flex items-center gap-3">
                        <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" size="sm" />
                        <div class="flex-1 text-left truncate">
                            <p class="text-xs font-bold truncate">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-zinc-500 truncate">{{ auth()->user()->email }}</p>
                        </div>
                        <flux:icon.chevron-up-down variant="mini" class="size-4 text-zinc-400" />
                    </flux:button>

                    <flux:menu class="w-64">
                        <flux:menu.item :href="route('profile.edit')" icon="user" wire:navigate>{{ __('Mi Perfil') }}</flux:menu.item>
                        <flux:menu.separator />
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" color="red">
                                {{ __('Cerrar Sesión') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </div>
        </flux:sidebar>

        {{-- Mobile Header --}}
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />
            <x-app-logo href="{{ route('patients.index') }}" wire:navigate />
            <flux:spacer />
            <x-desktop-user-menu />
        </flux:header>

        {{-- Main Content Wrap --}}
        <flux:main class="px-4 py-6 md:px-8">
            {{ $slot }}
        </flux:main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>

