<?php

namespace App\Http\Controllers;

use App\ActiveUser;
use App\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\User;
use Illuminate\Support\Facades\Log;
use Yajra\Datatables\Datatables;

class EventController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:su');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function index()
    {
        if (Gate::denies('su-user-group')) {
            abort(403, 'Access denied');
        }

        // Users list
        $usersList = User::orderBy('username')->get()->pluck('username', 'user_id');

        // Dates
        $fromDate = Carbon::now()->subMonth()->format('d-m-Y');
        $toDate = Carbon::now()->format('d-m-Y');

        // Active users list
        $activeUsers = ActiveUser::with('user')
            ->whereNull('expired_at')
            ->orWhere('expired_at', '>', Carbon::now())
            ->get();

        $activeUsersList = [];
        foreach ($activeUsers as $activeUser) {
            try {
                $activeUsersList[] = [
                    'userName'  => $activeUser->user->username,
                    'ipAddress' => long2ip($activeUser->ip_address),
                    'createdAt' => Carbon::parse($activeUser->created_at)->format('d-m-Y H:i'),
                    'updatedAt' => $activeUser->updated_at ? Carbon::parse($activeUser->updated_at)
                        ->format('d-m-Y H:i') : '',
                    'expiredAt' => $activeUser->expired_at ? Carbon::parse($activeUser->expired_at)
                        ->format('d-m-Y H:i') : '',
                ];
            } catch (\Exception $exception){
                Log::warning($exception->getMessage());
            }
        }

        return view('events.index', [
            'users'       => [ '' => 'All' ] + $usersList->toArray(),
            'activeUsers' => $activeUsersList,
            'fromDate'    => $fromDate,
            'toDate'      => $toDate,
        ]);
    }

    // This function is returning json data to display in Grid
    public function json(Request $request)
    {
        if (Gate::denies('su-user-group')) {
            abort(403, 'Access denied');
        }

        $userId = $request->input('user_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $events = DB::table('events as e')
            ->select(
                [
                    'u.username',
                    'e.ip_address',
                    'e.action',
                    'e.created_at',
                ]
            )
            ->leftJoin('users AS u', 'e.user_id', '=', 'u.user_id')
            ->when($userId, function ($query) use ($userId)
            {
                return $query->where('e.user_id', $userId);
            })
            ->when($fromDate, function ($query) use ($fromDate)
            {
                return $query->whereDate('e.created_at', '>=', Carbon::parse($fromDate));
            })
            ->when($toDate, function ($query) use ($toDate)
            {
                return $query->whereDate('e.created_at', '<=', Carbon::parse($toDate));
            });

        return Datatables::of($events)
            ->editColumn('ip_address', function ($event)
            {
                return long2ip($event->ip_address);
            })
            ->filterColumn('e.ip_address', function ($query, $keyword)
            {
                $query->whereRaw("INET_NTOA(e.ip_address) like ?", [ "%$keyword%" ]);
            })
            ->editColumn('e.created_at', function ($event)
            {
                return Carbon::parse($event->created_at)->format('d-m-Y H:i:s');
            })
            ->filterColumn('e.created_at', function ($query, $keyword)
            {
                $query->whereRaw("DATE_FORMAT(e.created_at,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
            })
            ->make();
    }
}
