<?php
/**
 * @author          Archie, Disono (webmonsph@gmail.com)
 * @link            https://github.com/disono/Laravel-Template
 * @copyright       Webmons Development Studio. (webmons.com), 2016-2018
 * @license         Apache, 2.0 https://github.com/disono/Laravel-Template/blob/master/LICENSE
 */

namespace App\Http\Controllers\Module\Page;

use App\Http\Controllers\Controller;
use App\Models\Chat\ChatGroup;

class DevelopmentController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Development view
     */
    public function applicationAction()
    {
        return $this->json(ChatGroup::fetchAll());
    }
}
