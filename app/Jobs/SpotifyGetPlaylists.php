<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\SpotifyService;
use App\Events\JobCompletedEvent;

class SpotifyGetPlaylists implements ShouldQueue
{
    use Queueable;
    protected $spotifyService;
    protected $user;
    public $timeout = 600;
    /**
     * Create a new job instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
        $this->spotifyService = new SpotifyService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->spotifyService->getPlaylists($this->user);
        broadcast(new JobCompletedEvent([
            'user_id' => $this->user->id,
            'message' => 'Playlists fetched successfully',
        ]));
    }
}
