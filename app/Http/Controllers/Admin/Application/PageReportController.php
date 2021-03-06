<?php
/**
 * @author          Archie Disono (webmonsph@gmail.com)
 * @link            https://github.com/disono/Laravel-Template
 * @copyright       Webmons Development Studio. (https://webmons.com), 2016-2019
 * @license         Apache, 2.0 https://github.com/disono/Laravel-Template/blob/master/LICENSE
 */

namespace App\Http\Controllers\Admin\Application;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Application\Report\ReportMessageStore;
use App\Models\Vendor\Facades\PageReport;
use App\Models\Vendor\Facades\PageReportMessage;
use App\Models\Vendor\Facades\PageReportReason;

class PageReportController extends Controller
{
    protected $viewType = 'admin';
    private $pageReport = NULL;
    private $pageReportMessage = NULL;

    public function __construct()
    {
        parent::__construct();
        $this->theme = 'application.report.submitted';
        $this->pageReport = PageReport::self();
        $this->pageReportMessage = PageReportMessage::self();
    }

    public function indexAction()
    {
        $this->setHeader('title', 'Page Reports');
        $this->pageReport->enableSearch = true;
        $this->pageReport->setWritableColumn('created_at');

        return $this->view('index', [
            'reports' => $this->pageReport->fetch(requestValues(
                'search|pagination_show|page_report_reason_id|responded_by_id|user_id|status|created_at'
            )),
            'reasons' => PageReportReason::all(),
            'statuses' => PageReport::statuses()
        ]);
    }

    public function searchSubmittedByAction()
    {
        return $this->json(PageReport::fetchAll(requestValues('search', ['groupBy' => 'user_id'])));
    }

    public function searchProcessedByAction()
    {
        return $this->json(PageReport::fetchAll(requestValues('search', ['groupBy' => 'responded_by_id'])));
    }

    public function showAction($id)
    {
        $data = $this->pageReport->single($id);
        if (!$data) {
            abort(404);
        }

        $this->setHeader('title', $data->page_report_reason_name);
        return $this->view('show', [
            'report' => $data,
            'statuses' => $this->pageReport->statuses(),
            'messages' => $this->pageReportMessage->fetch(['page_report_id' => $data->id])
        ]);
    }

    public function messageAction(ReportMessageStore $request)
    {
        $inputs = $request->all();
        $inputs['user_id'] = __me()->id;
        $this->pageReportMessage->store($inputs);
        return $this->json(['redirect' => '/admin/page-report/show/' . $request->get('page_report_id')]);
    }

    public function statusAction($id, $status)
    {
        $report = $this->pageReport->single($id);
        if (!$report) {
            abort(404);
        }

        $inputs = ['status' => $status];
        if (!$report->responded_by_id) {
            $inputs['responded_by_id'] = __me()->id;
        }

        $this->pageReport->edit($id, $inputs);
        return $this->redirect();
    }

    public function destroyAction($id)
    {
        $this->pageReport->remove($id);
        return $this->json('Report is successfully deleted.');
    }
}
