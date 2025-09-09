<?php

namespace App\Http\Controllers\Api\v2_9\OGM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class M3StatsController extends Controller
{
    public function getCounts(Request $request)
    {
        $request->validate([
            'os_id'  => 'required|integer',
        ]);

        $ogmId =  $request->user()->od;
        $osId  = (int) $request->os_id;

        // Single round-trip:
        // - drafts count via scalar subquery (indexed on ogm_id)
        // - products counts via join + conditional SUMs (indexed on os_id, status_id; statuses.name indexed)
        $row = DB::selectOne("
            SELECT
                (SELECT COUNT(*) FROM drafts d WHERE d.ogm_id = ?) AS drafts,
                COALESCE(SUM(CASE WHEN s.name = 'pre-post'  THEN 1 ELSE 0 END), 0) AS pre_posts,
                COALESCE(SUM(CASE WHEN s.name = 'published' THEN 1 ELSE 0 END), 0) AS published
            FROM products p
            JOIN statuses s ON s.id = p.status_id
            WHERE p.os_id = ?
        ", [$ogmId, $osId]);

        // If there are *no* products for this os_id, SELECT with SUM returns one row with NULLs (handled by COALESCE)
        return response()->json([
            'drafts'    => (int) ($row->drafts ?? 0),
            'pre_posts' => (int) ($row->pre_posts ?? 0),
            'published' => (int) ($row->published ?? 0),
        ]);
    }
}



// public function getCounts(Request $request)
// {
//     $request->validate([
//         'os_id' => 'required|integer',
//     ]);

//     // ✅ pull OGM from the authenticated user (no need to pass it from client)
//     $ogmId = (int) ($request->user()->id ?? 0); // <-- fix your field name here
//     $osId  = (int) $request->integer('os_id');

//     // Resolve status IDs once (cached in query). If you prefer, hardcode IDs.
//     $statusIds = DB::table('statuses')
//         ->whereIn('name', ['pre-post', 'published'])
//         ->pluck('id', 'name');

//     $prePostId  = (int) ($statusIds['pre-post'] ?? 0);
//     $publishedId = (int) ($statusIds['published'] ?? 0);

//     // Use three scalar subqueries — each hits an index directly.
//     $row = DB::selectOne("
//         SELECT
//         (SELECT COUNT(*) FROM drafts d   WHERE d.ogm_id = ?)                                                     AS drafts,
//         (SELECT COUNT(*) FROM products p WHERE p.os_id = ? AND p.status_id = ?)                                  AS pre_posts,
//         (SELECT COUNT(*) FROM products p WHERE p.os_id = ? AND p.status_id = ?)                                  AS published
//     ", [$ogmId, $osId, $prePostId, $osId, $publishedId]);

//     return response()->json([
//         'drafts'    => (int) ($row->drafts ?? 0),
//         'pre_posts' => (int) ($row->pre_posts ?? 0),
//         'published' => (int) ($row->published ?? 0),
//     ]);
// }