<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */
class ReportController extends BaseController
{
    public function postReportUser($username)
    {
        $validate = Report::validate(Input::all());
        if ($validate->fails()) {
            return Redirect::to('report/user/' . $username)->withErrors($validate);
        }
        $report = new Report();
        $report->report = $username;
        $report->type = 'user';
        $report->user_id = Auth::user()->id;
        $report->description = Input::get('report');
        $report->save();

        return Redirect::to('gallery')->with('flashSuccess', 'Thanks, user is now reported we will take quick actions');
    }

    public function postReportImage($id)
    {
        $validate = Report::validate(Input::all());
        if ($validate->fails()) {
            return Redirect::to('report/image/' . $id)->withErrors($validate);
        }
        $report = new Report();
        $report->report = $id;
        $report->user_id = Auth::user()->id;
        $report->type = 'image';
        $report->description = Input::get('report');
        $report->save();
        return Redirect::to('gallery')->with('flashSuccess', 'Thanks, Image is now reported we will take quick actions');
    }

    public function getReport($id)
    {
        return View::make('report/index')
            ->with('title', 'Report');
    }
}