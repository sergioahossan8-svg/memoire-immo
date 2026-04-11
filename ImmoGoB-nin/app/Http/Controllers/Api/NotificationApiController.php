<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class NotificationApiController extends Controller
{
    public function index()
    {
        $user          = auth()->user();
        $notifications = $user->notificationsImmogo()->latest()->paginate(20);

        return response()->json([
            'notifications' => $notifications->map(fn($n) => [
                'id'         => $n->id,
                'titre'      => $n->titre,
                'message'    => $n->message,
                'lien'       => $n->lien,
                'lu'         => (bool) $n->lu,
                'created_at' => $n->created_at->format('d/m/Y H:i'),
            ]),
            'unread_count' => $user->notificationsImmogo()->where('lu', false)->count(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'total'        => $notifications->total(),
            ],
        ]);
    }

    public function marquerLues()
    {
        auth()->user()->notificationsImmogo()->where('lu', false)->update(['lu' => true]);
        return response()->json(['message' => 'Notifications marquées comme lues.']);
    }
}
