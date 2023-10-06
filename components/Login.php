<?php namespace Albrightlabs\Usercognito\Components;

use Flash;
use Config;
use Redirect;
use Validator;
use BackendAuth;
use ValidationException;
use Backend\Models\User;
use Illuminate\Http\Request;
use Cms\Classes\ComponentBase;
use Backend\Classes\AuthManager;
use BlackBits\LaravelCognitoAuth\CognitoClient;
use BlackBits\LaravelCognitoAuth\Auth\AuthenticatesUsers;
use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;

/**
 * Login Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class Login extends ComponentBase
{
    use \BlackBits\LaravelCognitoAuth\Auth\AuthenticatesUsers;

    /**
     * Register component details
     */
    public function componentDetails()
    {
        return [
            'name'        => 'Login Component',
            'description' => 'Provides a login form for AWS Cognito authentication.'
        ];
    }

    /**
     * @link https://docs.octobercms.com/3.x/element/inspector-types.html
     * Register component properties
     */
    public function defineProperties()
    {
        return [];
    }

    /**
     * Authenticate user via AWS Cognito and log the Backend\User in
     */
    public function onSubmit(Request $request)
    {
        $data = post();

        $rules = [
            'email'    => 'required|between:2,255|email',
            'password' => 'required|between:8,256',
        ];
        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        $request = new Request();
        $request->replace([
            'email'    => $data['email'],
            'password' => $data['password'],
        ]);

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            Flash::error('Too many login attempts!'); // too many login attempts
            return $this->sendLockoutResponse($request);
        }

        try {
            if (app()->make(CognitoClient::class)->authenticate($request->email, $request->password, [])) {
                // check if the user exists in backend users
                if (!$backendUser = User::where('email', $data['email'])->where('is_cognito_user', 1)->where('is_cognito_user_existing', 1)->first()) {
                    Flash::error('No user with provided email address found.'); // no user found by email with front-end access
                    return false;
                }
                BackendAuth::login($backendUser);
                return Redirect::to(Config::get('cognito.login_success_url'));
            }
        }
        catch (CognitoIdentityProviderException $c) {
            if (isset($c['message'])) {
                Flash::error($c['message']); // authentication error
            }
            return false;
        }
        catch (\Exception $e) {
            if (null != $e->getMessage()) {
                Flash::error($e->getMessage()); // any type of error
            }
            return false;
        }
        Flash::error('Authentication failed.'); // any uncaught error
        return false;
    }

    /**
     * Logs authenticated user out
     */
    public function onLogout()
    {
        AuthManager::instance()->logout();

        return Redirect::to(Config::get('cognito.login_url'));
    }

    /**
     * Returns the authenticated user
     */
    public function authUser()
    {
        return BackendAuth::getUser();
    }

    /**
     * Returns the request new password page url
     */
    public function requestNewPasswordUrl()
    {
        return Config::get('cognito.request_password_url');
    }

    /**
     * Returns the app name
     */
    public function appName()
    {
        return Config::get('cognito.app_name');
    }
}
