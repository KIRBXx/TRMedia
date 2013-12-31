<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */

class AdminController extends BaseController
{

    public function getIndex()
    {
//       MONTHLY SIGNUP DETAILS
        $signUpDetails = DB::select('SELECT MONTH(created_at) as month ,YEAR(created_at) as year,COUNT(id) as number
FROM users
GROUP BY YEAR(created_at), MONTH(created_at)');

        for ($i = date('n'); $i >= 0; $i--) {
            if (isset($signUpDetails[$i]->month)) {
                $newSingupDetails[$signUpDetails[$i]->month] = $signUpDetails[$i];
            }
        }

        $imageDetails = DB::select('SELECT MONTH(created_at) as month ,YEAR(created_at) as year,COUNT(id) as number
FROM images
GROUP BY YEAR(created_at), MONTH(created_at)');

        for ($i = date('n'); $i >= 0; $i--) {
            if (isset($imageDetails[$i]->month)) {
                $newImageDetails[$imageDetails[$i]->month] = $imageDetails[$i];
            }
        }

        if (!isset($newSingupDetails)) {
            $newSingupDetails = NULL;
        }
        if (!isset($newImageDetails)) {
            $newImageDetails = NULL;
        }
        return View::make('admin/sitedetails/index')
            ->with('signUpDetails', $newSingupDetails)
            ->with('imageDetails', $newImageDetails);
    }

    public function getSiteSettings()
    {
        $settings = DB::table('sitesettings')->get();
        return View::make('admin/sitesettings/index')
            ->with('settings', $settings);

    }

    public function getRemoveCache()
    {
        File::cleanDirectory(public_path() . '/cache');
        File::cleanDirectory(storage_path() . '/cache');
        File::cleanDirectory(storage_path() . '/views');
        Cache::forget('siteName');
        Cache::forget('description');
        Cache::forget('favIcon');
        Cache::forget('faq');
        Cache::forget('privacy');
        Cache::forget('tos');
        Cache::forget('featuredImage');
        Cache::forget('featuredAuthor');
        return Redirect::to('admin')->with('flashSuccess', 'All cached files are deleted');
    }

    private function rrmdir($dir)
    {
        if (@is_dir($dir)) {
            $objects = @scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir")
                        $this->rrmdir($dir . "/" . $object);
                    else @unlink($dir . "/" . $object);
                }
            }
            @reset($objects);
            @rmdir($dir);
        }
    }

    public function getUsersList()
    {
        $users = User::withTrashed()->paginate(50);
        return View::make('admin/userslist/index')
            ->with('users', $users)
            ->with('title', 'List of all users');
    }

    public function getSiteCategory()
    {
        return View::make('admin/sitecategory/index')
            ->with('title', 'Site Category');
    }

    public function getFeaturedUserList()
    {
        $users = User::withTrashed()->where('is_featured', '=', '1')->paginate(50);
        return View::make('admin/userslist/index')
            ->with('users', $users)
            ->with('title', 'List of featured users');
    }

    public function getBannedUserList()
    {
        $users = User::withTrashed()->where('permission', '=', 'ban')->paginate(50);
        return View::make('admin/userslist/index')
            ->with('users', $users)
            ->with('title', 'List Of Banned Users');
    }

    public function getEditUser($user)
    {
        $user = User::withTrashed()->where('username', '=', $user)->first();
        return View::make('admin/edituser/index')
            ->with('user', $user);
    }

    public function getAllComments()
    {
        $comments = Comment::orderBy('created_at', 'desc')
            ->with('user', 'image')->paginate(50);
        return View::make('admin/allcomments/index')
            ->with('comments', $comments);
    }

    public function getImagesList()
    {
        $images = Images::where('deleted_at', '=', NULL)->where('approved', '=', 1)->with('user', 'favorite')->paginate(50);

        return View::make('admin/images/index')
            ->with('images', $images)
            ->with('title', 'List of all images');
    }

    public function getImagesApproval()
    {
        $images = Images::where('approved', '=', 0)->where('deleted_at', '=', NULL)->with('user', 'favorite')->paginate(50);

        return View::make('admin/approval/index')
            ->with('images', $images)
            ->with('title', 'Images Required Approval');
    }

    public function featuredImagesList()
    {
        $images = Images::withTrashed()->where('is_featured', '=', '1')->paginate(50);
        return View::make('admin/images/index')
            ->with('images', $images)
            ->with('title', 'List of featured Images');
    }

    public function getEditImage($id)
    {
        $image = Images::where('id', '=', $id)->with('favorite', 'user')->get();
        return View::make('admin/editimage/index')->with('image', $image);
    }

    public function getReports()
    {
        $reports = Report::with('user')->get();
        return View::make('admin/reports/index')
            ->with('reports', $reports)
            ->with('title', 'Latest Reports');
    }

    public function getReadReport($id)
    {
        $report = Report::with('user')->find($id);
        $report->solved = '1';
        $report->save();
        return View::make('admin/reports/read')
            ->with('title', 'Full Report')
            ->with('report', $report);
    }

    public function updateSiteMap()
    {
        $sitemap = App::make("sitemap");
        $posts = Images::orderBy('created_at', 'desc')->get();
        foreach ($posts as $post) {
            $sitemap->add(url('image/' . $post->id . '/' . $post->slug), $post->updated_at, '0.9');
        }
        $sitemap->store('xml', 'sitemap');
        return Redirect::to('admin')->with('flashSuccess', 'sitemap.xml is now updated');
    }

    public function getLimitSettings()
    {
        return View::make('admin/limitsettings/index');
    }

    public function getAddUser()
    {
        return View::make('admin/adduser/index');
    }

    public function getBulkUpload()
    {
        return View::make('admin/bulkupload/index');
    }
}