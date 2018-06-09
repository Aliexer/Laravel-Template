<?php
/**
 * @author          Archie, Disono (webmonsph@gmail.com)
 * @link            https://github.com/disono/Laravel-Template
 * @copyright       Webmons Development Studio. (webmons.com), 2016-2018
 * @license         Apache, 2.0 https://github.com/disono/Laravel-Template/blob/master/LICENSE
 */

namespace App\Http\Controllers\Module\Authentication;

use App\Http\Controllers\Controller;
use App\Models\User;

class VerifyController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Verify email address
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function emailAction()
    {
        return $this->_verify('email');
    }

    /**
     * Check the verification
     *
     * @param $type
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    private function _verify($type)
    {
        try {
            return $this->view('auth.verification.success.' . User::verify($type));
        } catch (\Exception $e) {
            return $this->view('errors.default', [
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Verification form for phone
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function phoneAction()
    {
        return $this->view('auth.verification.phone');
    }

    /**
     * Verify phone
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function processPhoneAction()
    {
        return $this->_verify('phone');
    }

    /**
     * Resend verification code
     *
     * @param $type
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function resendVerificationViewAction($type)
    {
        return $this->view('auth.verification.resend', ['type' => $type]);
    }

    /**
     * Resend verification code
     *
     * @param $type
     * @return bool
     */
    public function resendVerificationProcessAction($type)
    {
        try {
            $value = User::resendVerification($type);

            if ($type == 'email') {
                return $this->redirect()->with('message', 'We already sent you a verification link for ' . $value . '. Thank You.');
            }

            return $this->redirect('verify/' . $type);
        } catch (\Exception $e) {
            return $this->redirect()->with('error', $e->getMessage());
        }
    }
}
