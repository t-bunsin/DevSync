<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The Activity center bell in the admin shell.
 *
 * The dropdown is rendered server-side on page load and re-rendered from this
 * feed every few seconds, so the markup lives in one partial that both the
 * layout and this controller render — see partials/notification-items.
 */
class NotificationController extends Controller
{
    /** How many the dropdown shows at once. */
    public const LIMIT = 6;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * What the poller asks for: the unread count for the badge, and the
     * rendered list. HTML rather than JSON rows, so the two renderings of the
     * dropdown cannot drift apart.
     */
    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'unread' => $user->unreadNotifications()->count(),
            'html' => view('partials.notification-items', [
                'notifications' => static::latestFor($user),
            ])->render(),
        ]);
    }

    /** Opening the dropdown clears the badge; the items stay listed. */
    public function markRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['unread' => 0]);
    }

    /** The newest few for one user, for the layout and the feed alike. */
    public static function latestFor($user)
    {
        return $user
            ? $user->notifications()->latest()->take(self::LIMIT)->get()
            : collect();
    }
}
