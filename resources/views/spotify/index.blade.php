<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Spotify') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- @if (Auth::user()->spotify_access_token != null && Auth::user()->token_last_acquired < now()->modify('-1 hour')) --}}
                    <a href="/spotify/user_auth"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Login with
                        Spotify</a>
                    {{-- @else --}}
                    <a href="/spotify/get_saved_tracks/<?= Auth::user()->spotify_access_token ?>"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Get Saved
                        Tracks</a>
                    {{-- @endif --}}
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
