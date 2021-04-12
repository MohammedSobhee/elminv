<?php

namespace App\Jobs;

use App\Assignment;
use App\Mail\SendServerMessageEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;
use Spatie\Browsershot\Browsershot;
use Throwable;

class ScreenshotAssignment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $assignment;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Assignment $assignment) {
        $this->assignment = $assignment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $assignment = $this->assignment;
        $stored_name = time() . \Str::random(5);
        $stored_name = date('Y') . '/' . $stored_name . '.png';
        try { // Use try/catch to prevent error from reporting to slack
            Browsershot::url($assignment->file_location)
                ->dismissDialogs()
                ->windowSize(1200, 800)
                ->waitUntilNetworkIdle()
                //->timeout(60)
                ->optimize()
                ->save(public_path('uploads') . '/assignments/' . $stored_name);
                $assignment->file_screenshot = $stored_name;
                $assignment->save();
        } catch(Throwable $e) {
            $this->failed($e);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception) {
        \Log::debug($exception->getMessage());
        Mail::to(config('mail.admin'))->send(new SendServerMessageEmail($exception->getMessage(), $exception->getFile()));
        $this->assignment->file_screenshot = 'default/screenshot_unavailable.png';
        $this->assignment->save();
    }
}
