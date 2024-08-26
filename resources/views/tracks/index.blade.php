<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tracks') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <table id="trackTable" x-data="trackData">
                        <thead>
                            <tr>
                                <th @click="sort('name')">Name</th>
                                <th @click="sort('artist_name')">Artist</th>
                                <th @click="sort('album_name')">Album</th>
                                <th @click="sort('popularity')">Popuplarity</th>
                                <th @click="sort('genres')">Genres</th>
                                <th @click="sort('acousticness')">Acousticness</th>
                                <th @click="sort('valence')">Happiness</th>
                                <th>Play</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="!tracks">
                                <tr>
                                    <td colspan="4"><i>Loading...</i></td>
                                </tr>
                            </template>
                            <template x-for="track in tracks">
                                <tr class="cursor-pointer" x-ref="track.track_id" x-data="{
                                    audio: '<audio id=track.track_id src=track.preview_link></audio>'
                                }"
                                    @click="play(document.getElementById(track.track_id))">
                                    <td x-text="track.name"></td>
                                    <td x-text="track.artist_name"></td>
                                    <td x-text="track.album_name"></td>
                                    <td x-text="track.popularity"></td>
                                    <td x-text="track.genres"></td>
                                    <td x-text="track.acousticness"></td>
                                    <td x-text="track.valence"></td>
                                    <td><i class="fa-solid fa-play icon"><audio :id="track.track_id"
                                                :src="track.preview_link"></audio></i></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    {{-- <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <div class="flex justify-between items">
                                <div class="flex-1">
                                    <h6>Name</h6>
                                </div>
                                <div class="flex-1">
                                    <h6>Artist</h6>
                                </div>
                                <div class="flex-1">
                                    <h6>Album</h6>
                                </div>
                                <div class="flex-1">
                                    <h6>Popularity</h6>
                                </div>
                                <div class="flex-1">
                                    <h6>Genres</h6>
                                </div>
                                <div class="flex-1">
                                    <h6>Acousticness</h6>
                                </div>
                                <div class="flex-1">
                                    <h6>Happiness</h6>
                                </div>
                            </div>

                        </div>
                    </div>
                    @foreach ($tracks as $track)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900 dark:text-gray-100">
                                <div class="flex justify-between items">
                                    <div class="flex-1">{{ $track->name }}</div>
                                    <div class="flex-1">{{ $track->artist_name }}</div>
                                    <div class="flex-1">{{ $track->album_name }}</div>
                                    <div class="flex-1">{{ $track->popularity }}%</div>
                                    <div class="flex-1">{{ $track->genres }}</div>
                                    <div class="flex-1">{{ number_format($track->acousticness * 100, 0) }}%</div>
                                    <div class="flex-1">{{ number_format($track->valence * 100, 0) }}%
                                    </div>
                                </div>
                            </div>
                    @endforeach --}}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function play(audio) {
        if (audio.paused) {
            audio.play();
            $(audio).closest('.icon').removeClass('fa-play').addClass('fa-pause');
        } else {
            audio.pause();
            audio.currentTime = 0;
            $(audio).closest('.icon').removeClass('fa-pause').addClass('fa-play');
        }
    }
    document.addEventListener('alpine:init', () => {
        Alpine.data('trackData', () => ({
            tracks: null,
            sortCol: null,
            sortAsc: false,
            async init() {
                let resp = await fetch(
                    'https://playlistrix.co.uk/tracks/get_tracks', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                    });
                this.tracks = await resp.json();
            },
            sort(col) {
                if (this.sortCol === col) this.sortAsc = !this.sortAsc;
                this.sortCol = col;
                this.tracks.sort((a, b) => {
                    if (a[this.sortCol] < b[this.sortCol]) return this.sortAsc ? 1 : -1;
                    if (a[this.sortCol] > b[this.sortCol]) return this.sortAsc ? -1 : 1;
                    return 0;
                });
            }
        }))
    });
</script>
