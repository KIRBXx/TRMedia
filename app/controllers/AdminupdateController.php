<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */

class AdminupdateController extends BaseController
{
    public function updateSiteCategory()
    {
        $category = new Sitecategories();
        $category->category = ucfirst(Input::get('addnew'));
        $category->slug = Str::slug(Input::get('addnew'));
        $category->save();
        Cache::forget('siteCategories');
        return Redirect::to('admin/sitecategory')->with('flashSuccess', 'New Category Is Added');
    }

    public function updateSettings()
    {
        DB::table('sitesettings')
            ->where('option', 'siteName')
            ->update(array('value' => Input::get('siteName')));
        DB::table('sitesettings')
            ->where('option', 'description')
            ->update(array('value' => Input::get('description')));
        DB::table('sitesettings')
            ->where('option', 'favIcon')
            ->update(array('value' => Input::get('favIcon')));
        DB::table('sitesettings')
            ->where('option', 'privacy')
            ->update(array('value' => Input::get('privacy')));
        DB::table('sitesettings')
            ->where('option', 'faq')
            ->update(array('value' => Input::get('faq')));
        DB::table('sitesettings')
            ->where('option', 'tos')
            ->update(array('value' => Input::get('tos')));
        DB::table('sitesettings')
            ->where('option', 'about')
            ->update(array('value' => Input::get('about')));

        Cache::forget('siteName');
        Cache::forget('description');
        Cache::forget('favIcon');
        Cache::forget('faq');
        Cache::forget('privacy');
        Cache::forget('tos');
        Cache::forget('about');

        return Redirect::to('admin/sitesettings')->with('flashSuccess', 'Site Details Updated');
    }

    public function updateImage($id)
    {
        Cache::forget('featuredImage');
        $input = Input::all();
        if (!empty($input['make-featured'])) {
            $image = Images::find($id);
            $image->is_featured = 1;
            $image->save();
            return Redirect::to('admin/images')->with('flashSuccess', 'Image is now featured Image');
        }
        if (!empty($input['remove-featured'])) {
            $image = Images::find($id);
            $image->is_featured = NULL;
            $image->save();
            return Redirect::to('admin/images')->with('flashSuccess', 'Image removed from featured Image');
        }
        if (!empty($input['delete'])) {
            $image = Images::find($id);
            File::delete('uploads/' . $image->image_name . '.' . $image->type);
            $image->delete();
            return Redirect::to('admin/images')->with('flashSuccess', 'Image is now deleted permanently');
        }
        if (strlen(Input::get('title')) > 1) {
            $slug = @Str::slug(Input::get('title'));
            if (!$slug) {
                $slug = Str::rand(9);
            }
            $image = Images::find($id);
            $image->title = Input::get('title');
            $image->slug = $slug;
            $image->image_description = Input::get('description');
            $image->save();
            return Redirect::to('admin')->with('flashSuccess', 'Image is now updated');
        }
        return Redirect::to('admin/images')->with('flashError', 'Something went wrong try again');
    }


    public function updateUser($username)
    {
        $user = User::withTrashed()->find(Input::get('userid'));
        if (Input::get('ban') == 'TRUE') {
            $user->permission = 'ban';
            $user->save();
            return Redirect::to('admin')->with('flashSuccess', 'User is banned now');
        }
        $user->fullname = Input::get('fullname');
        $user->email = Input::get('email');
        $user->about_me = Input::get('aboutme');
        $user->blogurl = Input::get('blogurl');
        $user->country = Input::get('country');
        if (Input::get('featured') == 'TRUE') {
            $user->is_featured = 1;
        } else {
            $user->is_featured = NULL;
        }
        if (Input::get('confirmed') == '1') {
            $user->confirmed = '1';
        }
        if (Input::get('ban') == 'TRUE') {
            $user->permission = 'ban';
        }
        $user->save();

        return Redirect::to('admin')->with('flashSuccess', 'User "' . $user->username . '" is updated');
    }

    public function updateImagesApproval()
    {
        $image = Images::find(Input::get('id'));
        $image->approved = 1;
        $image->save();
        return 'Approved';
    }

    public function postLimitSettings()
    {
        DB::table('sitesettings')
            ->where('option', 'numberOfImagesInGallery')
            ->update(array('value' => Input::get('numberOfImages')));
        DB::table('sitesettings')
            ->where('option', 'autoApprove')
            ->update(array('value' => Input::get('autoApprove')));
        DB::table('sitesettings')
            ->where('option', 'limitPerDay')
            ->update(array('value' => Input::get('limitPerDay')));
        DB::table('sitesettings')
            ->where('option', 'tagsLimit')
            ->update(array('value' => Input::get('tagsLimit')));
        DB::table('sitesettings')
            ->where('option', 'allowDownloadOriginal')
            ->update(array('value' => Input::get('allowDownloadOriginal')));
        DB::table('sitesettings')
            ->where('option', 'maxImageSize')
            ->update(array('value' => Input::get('maxImageSize')));
        Cache::forget('numberOfImagesInGallery');
        Cache::forget('autoApprove');
        Cache::forget('limitPerDay');
        Cache::forget('tagsLimit');
        Cache::forget('allowDownloadOriginal');
        Cache::forget('maxImageSize');
        return Redirect::to('admin')->with('flashSuccess', 'You limits are now saved');
    }

    public function addUser()
    {
        $v = array(
            'username' => array('required', 'unique:users'),
            'email'    => array('required', 'unique:users'),
            'fullname' => array('required'),
            'password' => array('required'),
        );
        $v = Validator::make(Input::all(), $v);
        if ($v->fails()) {
            return Redirect::to('admin/adduser')->withErrors($v);
        }
        $user = new User();
        $user->username = Input::get('username');
        $user->fullname = Input::get('fullname');
        $user->email = Input::get('email');
        $user->password = Hash::make(Input::get('password'));
        $user->confirmed = 1;
        $user->save();
        return Redirect::to('admin')->with('flashSuccess', 'New user is created');
    }

    public function postBulkUpload()
    {
        // check if category exits
        if (DB::table('sitecategories')->where('slug', '=', Str::slug(Input::get('category')))->count() != 1) {
            return $this->error(array('error' => t('Invalid category')));
        }

        $imageName = $this->dirName();
        $mimetype = Input::file('files')[0]->getMimeType();
        $mimetype = preg_replace('/image\//', '', $mimetype);
        $title = Input::file('files')[0]->getClientOriginalName();
        $title = str_replace(array('.jpg', '.gif', '.png', '.jpeg', '.JPG', '.GIF', '.PNG', '.JPEG'), '', $title);
        $file = Input::file('files')[0]->move('uploads/', $imageName . '.' . $mimetype);
        $tags = Input::get('tags');
        $parts = explode(',', $tags, siteSettings('tagsLimit'));

        if (count($parts) == 0) {
            return $this->error(array('error' => 'Tags are required'));
        }
        if (strlen($parts[0]) == 0) {
            return $this->error(array('error' => 'Tags are required'));
        }
        $tags = implode(',', array_map('strtolower', $parts));

        $slug = @Str::slug($title);
        if (strlen($slug) <= '1') {
            $slug = str_random(9);
        }

        $upload = new Images();
        $upload->user_id = Auth::user()->id;
        $upload->image_name = $imageName;
        $upload->title = $title;
        $upload->slug = $slug;
        $upload->category = Input::get('category');
        $upload->type = $mimetype;
        $upload->tags = $tags;
        $upload->allow_download = 1;
        $upload->approved = 1;

        $upload->save();

        return $this->error(array('success' => 'Uploaded', 'successSlug' => url('image/' . $upload->id . '/' . $upload->slug), 'successTitle' => ucfirst($upload->title), 'thumbnail' => asset(zoomCrop('uploads/' . $upload->image_name . '.' . $upload->type))));
    }

    private function error($str)
    {
        return array('files' => array(
            0 => $str
        ));
    }

    private function dirName()
    {
        $str = str_random(9);
        if (file_exists(public_path() . '/uploads/' . $str)) {
            $str = $this->dirName();
        }
        return $str;
    }
}