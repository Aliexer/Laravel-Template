<?php
/**
 * @author          Archie Disono (webmonsph@gmail.com)
 * @link            https://github.com/disono/Laravel-Template
 * @copyright       Webmons Development Studio. (https://webmons.com), 2016-2019
 * @license         Apache, 2.0 https://github.com/disono/Laravel-Template/blob/master/LICENSE
 */

namespace App\Http\Controllers\Module\Page;

use App\Http\Controllers\Controller;

class DevelopmentController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function applicationAction()
    {

    }

    public function documentationAction()
    {
        return $this->view('docs.web');
    }
}
