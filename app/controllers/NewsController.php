<?php
/**
 * @author RickR <rick@rudelinux.org>
 */
class NewsController extends BaseController
{
    public function getnews()
    {
        return View::make('news/index')
            ->with('title', 'News');
    }

    public function postnews()
    {

        $input = Input::all();

        $v = array(
            'email'                    => array('required', 'email'),
            'name'                     => array('required'),
            'subject'                  => array('required'),
            'message'                  => array('required'),
            'recaptcha_response_field' => array('required', 'recaptcha')
        );

        $v = Validator::make($input, $v);
        if ($v->fails()) {
            return Redirect::to('news')->withErrors($v)->withInput();
        }


        $data = array(
            'name'    => Input::get('name'),
            'email'   => Input::get('email'),
            'message' => Input::get('message'),
            'subject' => Input::get('subject')
        );

        Mail::send('emails.contact.index', $data, function ($message) use ($data) {
            $message->to('jameskirby1993@gmail.com', 'James Kirby')->subject($data['subject']);
        });

        return View::make('news/index')
            ->with('title', 'News')
            ->with('success', "Your message has been sent!");
    }
}
