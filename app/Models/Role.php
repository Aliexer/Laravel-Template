<?php
/**
 * @author          Archie Disono (webmonsph@gmail.com)
 * @link            https://github.com/disono/Laravel-Template
 * @copyright       Webmons Development Studio. (https://webmons.com), 2016-2019
 * @license         Apache, 2.0 https://github.com/disono/Laravel-Template/blob/master/LICENSE
 */

namespace App\Models;

use App\Models\Vendor\BaseModel;

class Role extends BaseModel
{
    protected static $tableName = 'roles';
    protected static $writableColumns = [
        'name', 'slug', 'description'
    ];

    public function __construct(array $attributes = [])
    {
        $this->fillable(self::$writableColumns);
        parent::__construct($attributes);
    }

    public function user()
    {
        return $this->hasMany('App\Models\User');
    }

    public function authorizationRole()
    {
        return $this->hasMany('App\Models\AuthorizationRole');
    }
}
