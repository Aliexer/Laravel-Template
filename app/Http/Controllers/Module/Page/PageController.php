<?php
/**
 * @author          Archie Disono (webmonsph@gmail.com)
 * @link            https://github.com/disono/Laravel-Template
 * @copyright       Webmons Development Studio. (https://webmons.com), 2016-2019
 * @license         Apache, 2.0 https://github.com/disono/Laravel-Template/blob/master/LICENSE
 */

namespace App\Http\Controllers\Module\Page;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PageController extends Controller
{
    public function __construct()
    {
        $this->theme = 'page';
        parent::__construct();
    }

    /**
     * Home page
     *
     * @return JsonResponse|Response
     */
    public function homeAction()
    {
        $this->setHeader('title', 'Home');
        return $this->view('home');
    }

    /**
     * List by category page
     *
     * @param $category
     * @return JsonResponse|Response
     */
    public function categoryAction($category)
    {
        return $this->view('list', ['pages' => Page::fetch(['page_category_slug' => $category])]);
    }

    /**
     * List old pages
     *
     * @param $year
     * @param $month
     * @return JsonResponse|Response
     */
    public function archiveAction($year, $month)
    {
        return $this->view('list', ['pages' => Page::fetch(['raw_year' => $year, 'raw_month' => $month])]);
    }

    /**
     * Show page details
     *
     * @param $slug
     * @return JsonResponse|Response|void
     */
    public function showAction($slug)
    {
        $view = 'show';
        $page = Page::single($slug, 'slug');
        if (!$page) {
            return $this->error(404, exceptionMessages('PAGE_NOT_FOUND'));
        }

        // custom views
        if ($page->template) {
            $view = 'templates.' . $page->template;
        }

        // save page view per device or user
        $this->_savePageView($page);

        return $this->view($view, ['page' => $page]);
    }

    private function _savePageView($page)
    {
        PageView::log($page);
    }
}
