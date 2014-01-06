<?php
/**
 * @author RickR <rick@rudelinux.org>
 */
class DonateController extends BaseController
{
    public function getDonate()
    {
        return View::make('donate/index')
            ->with('title', 'Donate');
    }

    public function postDonate()
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
            return Redirect::to('donate')->withErrors($v)->withInput();
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

        return View::make('donate/index')
            ->with('title', 'Donate')
            ->with('success', "Your message has been sent!");
    }
}
