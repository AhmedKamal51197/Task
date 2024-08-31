<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class LogUserData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $response = Http::get('https://randomuser.me/api/');
            
            if ($response->successful()) {
                Log::info('Random User API Response:', ['response' => $response->json()]);
            } else {
                Log::error('Random User API request failed.', ['status' => $response->status()]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch data from Random User API.', ['exception' => $e->getMessage()]);
        }
    }
}
