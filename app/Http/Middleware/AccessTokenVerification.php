<?php

namespace App\Http\Middleware;

use App\Models\AccessToken;
use Closure;

class AccessTokenVerification
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->whiteListApi($request->path())) {
            return $next($request);
        }

        $access_token = $request->header('X-Access-Token');

        if (!$access_token) {
            return response()->json($this->errorMsgs('access_token_required'), 400);
        }


        $user = AccessToken::where('access_token', $access_token)
            ->where('expiry_time', '>', time())
            ->first();

        if (empty($user)) {
            return response()->json($this->errorMsgs('access_token_expired'), 403);
        }


        $request->merge(['user_id' => $user->user_id]);
        return $next($request);
    }

    private function whiteListApi($route)
    {
        $whiteListRoutes = [
            'api/user-sign-up',
            'api/user-login',
            'api/user_social_login',
            'api/user_forgot_password',
            'api/get_page',
            'api/check-email',
            'api/check-business',
            'api/resend-code',
            'api/forgot-password',
            'api/industries',
            'api/get-user',
            'api/countries',
            'api/redirect_url',
            'api/payment_url',
            'api/get-page'
            // 'api/subscriptions-get'
        ];
        return in_array($route, $whiteListRoutes);
    }

    private function errorMsgs($type)
    {
        switch ($type) {
            case 'access_token_required':
                $error = ['message' => 'X-Access-Token is required'];
                break;

            case 'access_token_expired':
                $error = ['message' => trans('auth.token_has_been_expired')];
                break;

            default:
                $error = ['message' => 'Token mismatched'];
                break;
        }

        return $error;
    }
}
