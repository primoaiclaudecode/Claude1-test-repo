<?php

namespace App\Http\Controllers;

use App\ActiveUser;
use App\Event;
use App\Libraries\MenuHelper;
use App\Menu;
use App\UserMenuLink;
use App\UserProfileSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\User;
use Illuminate\Validation\Rule;
use Yajra\Datatables\Datatables;

class ProfileSettingsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show Profile Settings page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function index()
    {
        $rootDirNames = MenuHelper::getRootDirNames();
        $menu = MenuHelper::getMenuList();

        $userProfileSettings = UserProfileSetting::where('user_id', auth()->user()->user_id)->first();

        return view('profile-settings.index', [
            'showSidebar' => $userProfileSettings && $userProfileSettings->show_sidebar,
            'rootDirNames' => $rootDirNames,
            'menu' => $menu,
        ]);
    }

    /**
     * Get items for the user's Favourites menu
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMenuLinks(Request $request)
    {
        $menuLinkTitles = MenuHelper::getFavouritesList();

        $menuLinks = UserMenuLink::where('user_id', auth()->user()->user_id)
            ->orderBy('position')
            ->get(
                [
                    'id',
                    'link',
                    'position',
                ]
            );

        $linksList = [];

        foreach ($menuLinks as $menuLink) {
            $linksList[] = [
                'id' => $menuLink->id,
                'link' => $menuLink->link,
                'position' => $menuLink->position,
                'title' => isset($menuLinkTitles[$menuLink->link]) ? $menuLinkTitles[$menuLink->link] : $menuLink->link,
            ];
        }

        return response()->json($linksList);
    }

    /**
     * Add item to the user's Favourites menu
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addLink(Request $request)
    {
        $this->validate($request, [
            'link' => 'required|string',
            'position' => 'required|integer|min:0',
        ]);

        $userId = auth()->user()->user_id;

        // Check for unique
        $menuLink = UserMenuLink::where('user_id', $userId)
            ->where('link', $request->link)
            ->first();

        if (!is_null($menuLink)) {
            return response()->json(
                [
                    'errorMessage' => 'This link has already been added to the menu.',
                ],
                500
            );
        }

        // Update positions for existing links
        $menuLinks = UserMenuLink::where('user_id', auth()->user()->user_id)
            ->where('position', '>=', $request->position)
            ->orderBy('position')
            ->get();

        $position = $request->position;
        foreach ($menuLinks as $menuLink) {
            $menuLink->position = ++$position;
            $menuLink->save();
        }

        // Insert new link
        UserMenuLink::create(
            [
                'user_id' => $userId,
                'link' => $request->link,
                'position' => $request->position,
            ]
        );
    }

    /**
     * Delete item from the user's Favourites menu
     *
     * @param Request $request
     *
     * @return void
     */
    public function deleteLink(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer',
        ]);

        // Delete link
        UserMenuLink::destroy($request->id);

        // Update positions for existing links
        $menuLinks = UserMenuLink::where('user_id', auth()->user()->user_id)
            ->orderBy('position')
            ->get();

        $position = 0;
        foreach ($menuLinks as $menuLink) {
            $menuLink->position = ++$position;
            $menuLink->save();
        }
    }

    /**
     * Change position for the the user's Favourites menu item
     *
     * @param Request $request
     *
     * @return void
     */
    public function changeLinkPosition(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer',
            'position' => 'required|integer|min:0',
        ]);

        // Update positions for other links
        $menuLinks = UserMenuLink::where('user_id', auth()->user()->user_id)
            ->where('id', '!=', $request->id)
            ->orderBy('position')
            ->get();

        $position = 1;
        foreach ($menuLinks as $menuLink) {
            $menuLink->position = $position++;
            $menuLink->save();

            if ($position == $request->position) {
                $position++;
            }
        }

        // Update position of the current link
        $menuLink = UserMenuLink::findOrFail($request->id);
        $menuLink->position = $request->position;
        $menuLink->save();
    }

    /**
     * Toggle sidebar visibility
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleSidebar()
    {
        $userId = auth()->user()->user_id;

        $userProfileSettings = UserProfileSetting::where('user_id', $userId)->first();

        if (!$userProfileSettings) {
            UserProfileSetting::create(
                [
                    'user_id' => $userId,
                    'show_sidebar' => 1,
                ]
            );
        } else {
            $userProfileSettings->show_sidebar = !$userProfileSettings->show_sidebar;
            $userProfileSettings->save();
        }
    }
}
