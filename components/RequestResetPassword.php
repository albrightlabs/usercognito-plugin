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
use BlackBits\LaravelCognitoAuth\CognitoClient;
use BlackBits\LaravelCognitoAuth\Auth\SendsPasswordResetEmails;
use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;

/**
 * Login Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class RequestResetPassword extends ComponentBase
{
    use \BlackBits\LaravelCognitoAuth\Auth\SendsPasswordResetEmails;

    /**
     * Register component details
     */
    public function componentDetails()
    {
        return [
            'name'        => 'Login Component',
            'description' => 'Provides a request password reset form for AWS Cognito.'
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
     * Requests user password reset email via AWS Cognito
     */
    public function onSubmit()
    {
        $data = post();

        $rules = [
            'email' => 'required|between:2,255|email',
        ];
        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        $request = new Request();
        $request->replace([
            'email'    => $data['email'],
        ]);

        try {
            // check if the user exists in backend users
            if (!User::where('login', $data['email'])->where('is_cognito_user', 1)->where('is_cognito_user_existing', 1)->first()) {
                Flash::error('A user with this email does not exist.'); // user does not exist error
                return false;
            }
            // attempt sending of reset email
            if (app()->make(CognitoClient::class)->sendResetLink($request->email)) {
                return Redirect::to(Config::get('cognito.reset_password_url').'?email='.$request->email);
            }
        }
        catch (CognitoIdentityProviderException $c) {
            if (isset($c['message'])) {
                Flash::error($c['message']); // authentication error
            }
            return false;
        }
        catch (\Exception $e) {
            Flash::error('Reset password email sending failed.'); // could not send email error
            return false;
        }
        Flash::error('Failed to request password reset.'); // any uncaught error
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
     * Returns the login page url
     */
    public function loginUrl()
    {
        return Config::get('cognito.login_url');
    }
}
