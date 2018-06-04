<?php
/**
 * @author          Archie, Disono (webmonsph@gmail.com)
 * @link            https://github.com/disono/Laravel-Template
 * @copyright       Webmons Development Studio. (webmons.com), 2016-2018
 * @license         Apache, 2.0 https://github.com/disono/Laravel-Template/blob/master/LICENSE
 */

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Page\PageStore;
use App\Http\Requests\Admin\Page\PageUpdate;
use App\Models\Page;
use App\Models\PageCategory;

class PageController extends Controller
{
    protected $viewType = 'admin';

    public function __construct()
    {
        parent::__construct();
        $this->theme = 'page';
    }

    public function indexAction()
    {
        $this->setHeader('title', 'Pages');
        return $this->view('index', [
            'pages' => Page::fetch(requestValues('search|page_category_id')),
            'categories' => PageCategory::fetchAll()
        ]);
    }

    public function createAction()
    {
        $this->setHeader('title', 'Create a New Page');
        return $this->view('create', [
            'categories' => PageCategory::fetchAll()
        ]);
    }

    public function storeAction(PageStore $request)
    {
        $page = Page::store($this->_formInputs($request));
        if (!$page) {
            return $this->json(['name' => 'Failed to crate a new page.'], 422, false);
        }

        return $this->json(['redirect' => '/admin/page/edit/' . $page->id]);
    }

    public function editAction($id)
    {
        $this->setHeader('title', 'Editing Page');
        $this->content['page'] = Page::single($id);
        if (!$this->content['page']) {
            abort(404);
        }

        $this->content['categories'] = PageCategory::fetchAll();
        return $this->view('edit');
    }

    public function updateAction(PageUpdate $request)
    {
        Page::edit($request->get('id'), $this->_formInputs($request));
        return $this->json('Page is successfully updated.');
    }

    public function destroyAction($id)
    {
        Page::remove($id);
        return $this->json('Page is successfully deleted.');
    }

    private function _formInputs($request)
    {
        $inputs = $request->all();
        $inputs['user_id'] = __me()->id;
        $inputs['cover_photo'] = $request->file('cover_photo');

        return $inputs;
    }
}
