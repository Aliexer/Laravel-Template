<?php
/**
 * @author          Archie Disono (webmonsph@gmail.com)
 * @link            https://github.com/disono/Laravel-Template
 * @copyright       Webmons Development Studio. (https://webmons.com), 2016-2019
 * @license         Apache, 2.0 https://github.com/disono/Laravel-Template/blob/master/LICENSE
 */

namespace App\Http\Controllers\API\V1\Application;

use App\Http\Controllers\API\APIController;
use App\Models\Vendor\Facades\City;
use App\Models\Vendor\Facades\Country;
use App\Models\Vendor\Facades\Setting;

class ApplicationController extends APIController
{
    public function settingsAction()
    {
        return $this->json(Setting::keyValuePair());
    }

    public function countryAction()
    {
        return $this->json(Country::fetchAll());
    }

    public function cityAction($country_id)
    {
        return $this->json(City::fetchAll(['country_id' => $country_id]));
    }
}
