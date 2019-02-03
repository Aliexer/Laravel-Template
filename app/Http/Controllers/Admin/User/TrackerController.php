<?php
/**
 * @author          Archie, Disono (webmonsph@gmail.com)
 * @link            https://github.com/disono/Laravel-Template
 * @copyright       Webmons Development Studio. (webmons.com), 2016-2018
 * @license         Apache, 2.0 https://github.com/disono/Laravel-Template/blob/master/LICENSE
 */

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Models\UserTracker;

class TrackerController extends Controller
{
    protected $viewType = 'admin';

    public function __construct()
    {
        parent::__construct();
        $this->theme = 'user.tracker';
    }

    public function indexAction()
    {
        $this->setHeader('title', 'Users Location');
        return $this->view('index', [
            'tracks' => UserTracker::fetch(requestValues('search'))
        ]);
    }
}