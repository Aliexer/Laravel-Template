<?php
/**
 * @author          Archie Disono (webmonsph@gmail.com)
 * @link            https://github.com/disono/Laravel-Template
 * @copyright       Webmons Development Studio. (https://webmons.com), 2016-2019
 * @license         Apache, 2.0 https://github.com/disono/Laravel-Template/blob/master/LICENSE
 */

namespace App\Models;

use App\Models\Vendor\BaseModel;
use Exception;

class PageView extends BaseModel
{
    protected $tableName = 'page_views';
    protected $writableColumns = [
        'page_id', 'user_id',
        'device_id', 'http_referrer', 'current_url', 'ip_address', 'platform', 'browser',
        'expired_at'
    ];

    protected $inputDates = ['birthday'];
    protected $columnHasRelations = ['page_id', 'user_id'];

    public function __construct(array $attributes = [])
    {
        $this->fillable($this->writableColumns);
        parent::__construct($attributes);
    }

    public function actionStoreBefore($tableName, $inputs)
    {
        if ((new PageView())->fetch(['is_expired' => 0, 'device_id' => $inputs['device_id'], 'page_id' => $inputs['page_id']])->count()) {
            return false;
        }

        if ((new PageView())->fetch(['is_expired' => 0, 'user_id' => $inputs['user_id'], 'page_id' => $inputs['page_id']])->count()) {
            return false;
        }

        return true;
    }

    public function log($page)
    {
        try {
            $userAgent = userAgent();
            (new PageView())->store([
                'page_id' => $page->id,
                'user_id' => (authId()) ? authId() : null,
                'device_id' => request()->header('device_id'),
                'http_referrer' => (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : null,
                'current_url' => (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI'])) ? $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] : null,
                'ip_address' => ipAddress(),
                'platform' => $userAgent->platform,
                'browser' => $userAgent->browserName,
                'expired_at' => sqlDate(null, true)
            ]);
        } catch (Exception $e) {
            logErrors($e->getMessage());
        }
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function page()
    {
        return $this->belongsTo('App\Models\Page');
    }

    protected function customQueries($query): void
    {
        $query->join('pages', 'page_views.page_id', '=', 'pages.id');
    }

    protected function customQuerySelectList(): array
    {
        return [
            'is_expired' => 'IF(page_views.expired_at >= DATE(NOW()), 0, 1)',
            'page_name' => 'pages.name',
        ];
    }
}
