<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */
class UploadController extends BaseController
{

    private function error($str)
    {
        return array('files' => array(
            0 => $str
        ));
    }

    public function getIndex()
    {
        return View::make('upload/index')
            ->with('title', 'Upload');
    }

    public function postUpload()
    {
        // For some reason when there are < 5 tags
        // files is empty - the way the UI is it
        // should be kind of safe to comment this out
        //if (@getimagesize(Input::file('files')[0]) == FALSE) {
        //    return $this->error(array('error'=>t('Image File is required')));
        //}

        // Removing tag req - RR 30 Dec 2013
        $v = array(
            'title'    => array('required', 'min:2', 'max:100'),
            'category' => array('required')
            //'tags'     => array('required'),
        );

        $v = Validator::make(Input::all(), $v);
        if ($v->fails()) {
            return $this->error($v->messages()->toArray());
        }
        // check if category exits
        if (DB::table('sitecategories')->where('slug', '=', Str::slug(Input::get('category')))->count() != 1) {
            return $this->error(array('error'=>t('Invalid category')));
        }

        if (Auth::user()->images()->where('created_at', '>=', date('Y-m-d'))->count() >= limitPerDay()) {
            return $this->error(array('error'=>t('You have reached today\'s limit')));
        }

        if (Input::file('files')[0]->getSize() >= (siteSettings('maxImageSize') * 1048576)) {
            return $this->error(array('error'=>t('File is too large, max size allowed is ') . siteSettings('maxImageSize') . 'MB'));
        }

        $imageName = $this->dirName();
        $mimetype = Input::file('files')[0]->getMimeType();
        $mimetype = preg_replace('/image\//', '', $mimetype);
        $file = Input::file('files')[0]->move('uploads/', $imageName . '.' . $mimetype);
        $tags = Input::get('tags');
        $parts = explode(',', $tags, siteSettings('tagsLimit'));

        // Removing tag req - RR 30 Dec 2013
        //if (count($parts) == 0) {
        //    return $this->error(array('error'=>'Tags are required'));
        //}
        //if (strlen($parts[0]) == 0) {
        //    return $this->error(array('error'=>'Tags are required'));
        //}
        $tags = implode(',', array_map('strtolower', $parts));

        $format_description = preg_replace('/\R\R+/u', "\n\n", trim(Input::get('description')));

        $slug = @Str::slug(Input::get('title'));
        if (strlen($slug) <= '1') {
            $slug = str_random(9);
        }
        $allowDownload = 1;
        if (siteSettings('allowDownloadOriginal') == 'leaveToUser') {
            if (preg_match('/\b(0|1)\b/', Input::get('allowDownloadOriginal'))) {
                $allowDownload = Input::get('allowDownloadOriginal');
            } else {
                $allowDownload = 1;
            }
        }
        if(Auth::user()->is_featured == 1) {
            $approve = 1;
        } elseif (siteSettings('autoApprove') == '0') {
            $approve = 0;
        } else {
            $approve = 1;
        }
        $upload = new Images();
        $upload->user_id = Auth::user()->id;
        $upload->image_name = $imageName;
        $upload->title = Input::get('title');
        $upload->slug = $slug;
        $upload->category = Input::get('category');
        $upload->type = $mimetype;
        $upload->tags = $tags;
        $upload->image_description = $format_description;
        $upload->allow_download = $allowDownload;
        $upload->approved = $approve;

        $upload->save();


        Cache::forget('moreFromSite');

        if ((int)siteSettings('autoApprove') == 0) {
            return $this->error(array('success'=>t('Your image is uploaded, require approval please keep patience'),'thumbnail'=>asset(zoomCrop('uploads/'.$upload->image_name. '.' . $upload->type))));
        }
        return $this->error(array('success'=>'Uploaded','successSlug'=>url('image/'.$upload->id.'/'.$upload->slug),'successTitle'=>ucfirst($upload->title),'thumbnail'=>asset(zoomCrop('uploads/'.$upload->image_name. '.' . $upload->type))));
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
