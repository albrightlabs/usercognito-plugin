# User Cognito Plugin

## Purpose
This plugin adds support for authenticating users via AWS Cognito on the front-end of an October CMS application. The Backend\User model is used as the authenticated user object in the system.

## What to expect
* Upon creation of a Backend user with the option "Allow Front-end Login" checked, the user will be added to the associated AWS Cognito account with the provided email address and password.
* Three new components will be available: 1) Login, 2) RequestPasswordReset, and 3) ResetPassword.

## Components
1. Login: Provides a login form.
2. RequestPasswordReset: Provides a request password reset form.
3. ResetPassword: Provides a reset password form.

## Installation
1. Add the source code of this plugin to `/plugins/albrightlabs` in a directory titled `usercognito`.
2. Run `composer update` after adding the source code as described in step 1.
3. Open `/bootstrap/app.php` and add `$app->register('Illuminate\Auth\AuthServiceProvider');` before the return statement.
4. Create file `/config/auth.php` and paste in the below code block.
```
<?php
return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session", "token"
    |
    */

    'guards' => [
        'web' => [
                'driver' => 'cognito',
                'provider' => 'users',
        ],
        'api' => [
            'driver' => 'token',
            'provider' => 'users',
        ],
    ],
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => Backend\Models\User::class
        ]
    ],
];
```
5. Copy file `/plugins/albrightlabs/usercognito/config/cognito.php` to `/config`.
6. Update the `.env` file to include the below code block and set the appropriate values for each.
```
AWS_COGNITO_KEY=
AWS_COGNITO_SECRET=
AWS_COGNITO_CLIENT_ID=
AWS_COGNITO_CLIENT_SECRET=
AWS_COGNITO_USER_POOL_ID=
AWS_COGNITO_REGION=
AWS_COGNITO_VERSION=
USE_SSO=
AWS_COGNITO_DELETE_USER=
```
7. Ensure that the `.env` file includes a setting for the backend URL and has the `Cms` module enabled.
```
BACKEND_URI=/backend
LOAD_MODULES="System,Backend,Cms"
```
8. If using a front-end theme for the first time, install the theme and set the active_theme variable in the `.env` file.
```
ACTIVE_THEME=bootstrap
```
9. Add the "Login", "RequestPasswordReset", and "ResetPassword" components to the front-end pages that you wish to add the forms to.
10. Update the `routing configuration` section in `/config/cognito.php` to include the URLs of the pages which the components were added.
11. Test the registration functionality by creating a user via the Backend user management with the option "Allow Front-end Login" checked and ensure they're also created in the AWS Cognito account.
12. Test the login functionality by using the login form added to a page in step 9. Also test the reset password process.

## Tips
To add a logout link to a page, add `[Login]` to the page's or layout's configuration section then create a link that calls the `Login::onLogout` method in the component. Successful logout will redirect to the login page.

## Support
Need help or have suggestions for this plugin?
Email support@albrightlabs.com or call (610) 756-5060.
