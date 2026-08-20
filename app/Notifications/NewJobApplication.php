<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Notifications\Notification;

/**
 * A candidate applied. Lands in the Activity center bell in the admin shell,
 * which polls for it — see public/js/admin-notifications.js.
 *
 * Database channel only: the delivery the back office actually reads. Adding
 * mail or a broadcast later means adding a channel here, nothing else.
 */
class NewJobApplication extends Notification
{
    public function __construct(private JobApplication $application)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * The row is written once and read for as long as it exists, so the copy
     * is frozen here rather than rebuilt from the application later: an
     * application that is deleted still leaves a readable history entry.
     */
    public function toArray(object $notifiable): array
    {
        $post = $this->application->jobPost;

        return [
            'kind' => 'job-application',
            'application_id' => $this->application->id,
            'job_post_id' => $post?->id,
            'candidate' => $this->application->full_name,
            'job_title' => $post?->title ?? 'a job post',
            'company' => $post?->company,
            'has_cv' => $this->application->hasCv(),
            'text' => sprintf(
                '%s applied for %s.',
                $this->application->full_name,
                $post?->title ?? 'a job post'
            ),
            'url' => $post ? route('job-posts.applications', $post->id) : route('job-posts.index'),
        ];
    }
}
