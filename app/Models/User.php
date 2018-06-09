<?php
/**
 * @author          Archie, Disono (webmonsph@gmail.com)
 * @link            https://github.com/disono/Laravel-Template
 * @copyright       Webmons Development Studio. (webmons.com), 2016-2018
 * @license         Apache, 2.0 https://github.com/disono/Laravel-Template/blob/master/LICENSE
 */

namespace App\Models;

use App\Models\Vendor\BaseUser;
use App\Notifications\RegisterNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class User extends BaseUser
{
    protected static $tableName = 'users';
    protected static $writableColumns = [
        'first_name', 'last_name', 'middle_name', 'gender',
        'birthday', 'address', 'postal_code', 'country_id', 'city_id',
        'phone', 'role_id',
        'username', 'email', 'password',
        'is_email_verified', 'is_phone_verified', 'is_account_activated', 'is_account_enabled'
    ];

    protected static $files = ['profile_picture'];
    protected static $imageOptions = ['tag' => 'profile_picture'];

    protected static $inputDates = ['birthday'];
    protected static $inputCrypt = ['password'];
    protected static $inputBooleans = ['is_email_verified', 'is_phone_verified', 'is_account_activated', 'is_account_enabled'];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function __construct(array $attributes = [])
    {
        $this->fillable(self::$writableColumns);
        parent::__construct($attributes);
    }

    /**
     * Remove all boolean to update
     */
    public static function clearBoolean()
    {
        self::$inputBooleans = [];
    }

    /**
     * Remove any related data from user
     *
     * @param $query
     * @return bool
     */
    public static function actionRemove($query)
    {
        $user = $query->first();
        if (!$user) {
            return false;
        }

        Page::where('user_id', $user->id)->delete();
        Token::where('user_id', $user->id)->delete();
        UserAddress::where('user_id', $user->id)->delete();
        UserPhone::where('user_id', $user->id)->delete();
        Verification::where('user_id', $user->id)->delete();
        AuthenticationHistory::where('user_id', $user->id)->delete();

        return true;
    }

    /**
     * Register new user
     *
     * @param array $data
     * @return null
     */
    public static function register(array $data)
    {
        // create new user
        $user = self::create([
            'first_name' => ucfirst($data['first_name']),
            'last_name' => ucfirst($data['last_name']),

            'username' => $data['username'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),

            'role_id' => 3,
            'is_account_enabled' => 1,
            'is_account_activated' => 1
        ]);

        self::_resendEmail($user);

        return self::crateToken(self::single($user->id));
    }

    /**
     * Resend verification to email email
     *
     * @param $user
     */
    private static function _resendEmail($user)
    {
        // send email for email verification
        Notification::send($user, new RegisterNotification($user));
    }

    /**
     * Crate a token for user
     *
     * @param $user
     * @return null
     */
    public static function crateToken($user)
    {
        if (!$user) {
            return null;
        }

        $user->token = Token::store([
            'user_id' => $user->id,
            'token' => str_random(64),
            'key' => str_random(64),
            'secret' => str_random(64),
            'source' => 'mobile',
            'expired_at' => expiredAt(21600)
        ]);

        return $user;
    }

    /**
     * Verify phone or email
     *
     * @param $type
     * @return mixed
     * @throws \Exception
     */
    public static function verify($type)
    {
        // verifications
        $verification = Verification::where('token', request('token'))
            ->where('value', request($type))
            ->where('type', $type)
            ->first();

        // check if token is valid
        if (!$verification) {
            throwError('TOKEN_NOT_FOUND');
        }

        // check if token is not expired
        if (strtotime($verification->expired_at) <= time()) {
            throwError('TOKEN_IS_EXPIRED');
        }

        // get user
        $user = self::where($type, $verification->value)->first();
        if (!$user) {
            throwError('INVALID_RAW', $type . '.');
        }

        // update user is verified
        if ($type === 'email') {
            self::where('id', $user->id)->update([
                'is_email_verified' => 1
            ]);
        } else if ($type === 'phone') {
            self::where('id', $user->id)->update([
                'is_phone_verified' => 1
            ]);
        }

        // clean all verification token
        Verification::where('value', $user->$type)->where('type', $type)->delete();

        // login user
        Auth::loginUsingId($user->id);
        initialize_settings();

        return $type;
    }

    /**
     * Resend verification for email and phone
     *
     * @param $type
     * @return array|\Illuminate\Http\Request|string
     * @throws \Exception
     */
    public static function resendVerification($type)
    {
        $value = request('type_value');

        // do we have a value to search for
        if (!$value) {
            throwError('RAW', $type . ' is required.');
        }

        // did the phone or email exists
        $user = User::where($type, $value)->first();
        if (!$user) {
            throwError('RAW', $type . ' is not registered.');
        }

        // did the old verification exists
        $verification = Verification::where('value', $value)->where('type', $type)->first();
        $user->renew_code = true;
        if ($verification) {
            // is expired
            if (strtotime($verification->expired_at) > time()) {
                $user->renew_code = false;
                $user->verification_code = $verification->token;
                self::_resendCode($type, $user);
            } else {
                self::_resendCode($type, $user);
            }
        } else {
            self::_resendCode($type, $user);
        }

        return $value;
    }

    /**
     * Resend the verification code
     *
     * @param $type
     * @param $user
     */
    private static function _resendCode($type, $user)
    {
        if ($type == 'phone') {
            self::_resendPhone($user);
        } else if ($type == 'email') {
            self::_resendEmail($user);
        }
    }

    /**
     * Resend verification to phone
     *
     * @param $user
     */
    private static function _resendPhone($user)
    {
        // clean all verification before saving new
        Verification::where('value', $user->phone)->where('type', 'phone')->delete();

        // create token
        Verification::create([
            'user_id' => $user->id,
            'token' => ucwords(str_random(6)),
            'value' => $user->phone,
            'type' => 'phone',
            'expired_at' => expiredAt(1440)
        ]);
    }

    /**
     * List of select
     *
     * @return array
     */
    protected static function rawQuerySelectList()
    {
        return [
            'full_name' => 'CONCAT(users.first_name, " ", users.last_name)',
            'role' => 'SELECT name FROM roles WHERE users.role_id = roles.id LIMIT 1',
            'country' => 'SELECT name FROM countries WHERE users.country_id = countries.id LIMIT 1',
            'city' => 'SELECT name FROM cities WHERE users.city_id = cities.id LIMIT 1',
        ];
    }

    /**
     * Add formatting to data
     *
     * @param $row
     * @return mixed
     */
    protected static function dataFormatting($row)
    {
        $row->profile_picture = fetchImage(self::_profilePicture($row->id), 'assets/img/placeholders/profile_picture.png');
        $row->birthday = ($row->birthday) ? humanDate($row->birthday, true) : null;
        $row->sever_time = sqlDate();

        self::_unsetHidden($row);

        return $row;
    }

    /**
     * Get profile picture
     *
     * @param $user_id
     * @return null
     */
    private static function _profilePicture($user_id)
    {
        $file = File::where('table_name', 'users')->where('table_id', $user_id)->where('tag', 'profile_picture')
            ->orderBy('created_at', 'DESC')->first();
        return ($file) ? $file->file_name : null;
    }

    /**
     * Unset hidden data
     *
     * @param $row
     */
    private static function _unsetHidden($row)
    {
        $_hidden = new User();
        foreach ($_hidden->hidden as $item) {
            unset($row->$item);
        }
    }

    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }

    public function country()
    {
        return $this->belongsTo('App\Models\Country');
    }

    public function city()
    {
        return $this->belongsTo('App\Models\City');
    }

    public function authenticationHistory()
    {
        return $this->hasMany('App\Models\AuthenticationHistory');
    }

    public function page()
    {
        return $this->hasMany('App\Models\Page');
    }

    public function socialAuthentication()
    {
        return $this->hasMany('App\Models\SocialAuthentication');
    }

    public function token()
    {
        return $this->hasMany('App\Models\Token');
    }

    public function address()
    {
        return $this->hasMany('App\Models\UserAddress');
    }

    public function phone()
    {
        return $this->hasMany('App\Models\UserPhone');
    }

    public function verification()
    {
        return $this->hasMany('App\Models\Verification');
    }

    public function file()
    {
        return $this->hasMany('App\Models\File');
    }
}
