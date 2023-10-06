<?php namespace Albrightlabs\Usercognito\Components;

use Flash;
use Config;
use Validator;
use BackendAuth;
use ValidationException;
use Backend\Models\User;
use Illuminate\Http\Request;
use Cms\Classes\ComponentBase;
use Illuminate\Support\Facades\Password;
use BlackBits\LaravelCognitoAuth\CognitoClient;
use BlackBits\LaravelCognitoAuth\Auth\ResetsPasswords;
use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;

/**
 * Login Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class ResetPassword extends ComponentBase
{
    use \BlackBits\LaravelCognitoAuth\Auth\ResetsPasswords;

    /**
     * Register component details
     */
    public function componentDetails()
    {
        return [
            'name'        => 'Login Component',
            'description' => 'Provides a password reset form for AWS Cognito.'
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
     * Resets user password via AWS Cognito
     */
    public function onSubmit()
    {
        try {
            $data = post();

            $rules = [
                'email'             => 'required|between:2,255|email',
                'confirmation_code' => 'required|digits:6',
                'password'          => 'required|between:2,255|confirmed',
            ];
            $validation = Validator::make($data, $rules);
            if ($validation->fails()) {
                throw new ValidationException($validation);
            }

            $request = new Request();
            $request->replace([
                'email'                 => $data['email'],
                'username'              => $data['email'],
                'confirmation_code'     => $data['confirmation_code'],
                'password'              => $data['password'],
                'password_confirmation' => $data['password'],
            ]);

            if(User::where('login', $data['email'])->where('is_cognito_user', 1)->where('is_cognito_user_existing', 1)->first()){
                $response = app()->make(CognitoClient::class)->resetPassword($request->confirmation_code, $request->email, $request->password);

                if($response == Password::PASSWORD_RESET){
                    Flash::success('Password reset!'); // success resetting password
                    return true;
                }
                else{
                    Flash::error('Error resetting password.'); // could not reset password
                    return false;
                }
            }
            else{
                Flash::error('A user with this email does not exist.'); // user does not exist error
                return false;
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
        Flash::error('Error resetting password.'); // any uncaught error
        return false;
    }

    /**
     * Returns the authenticated user
     */
    public function authUser()
    {
        return BackendAuth::getUser();
    }

    /**
     * Returns the URL param email
     */
    public function email()
    {
        if (isset($_GET['email'])) {
            return $_GET['email'];
        }
    }

    /**
     * Returns the request new password page url
     */
    public function requestNewPasswordUrl()
    {
        return Config::get('cognito.request_password_url');
    }

    /**
     * Returns the login page url
     */
    public function loginUrl()
    {
        return Config::get('cognito.login_url');
    }
}
