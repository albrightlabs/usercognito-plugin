<?php namespace Albrightlabs\UserCognito\Classes;

//use App\Exceptions\InvalidUserModelException;
//use App\Exceptions\NoLocalUserException;
use Aws\Result;
use Illuminate\Auth\SessionGuard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Session\Session;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\StatefulGuard;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Contracts\Auth\Authenticatable;
use Albrightlabs\UserCognito\Classes\CognitoClient;

class CognitoGuard extends SessionGuard implements StatefulGuard
{
    /**
     * @var CognitoClient
     */
    protected $client;

    /**
     * CognitoGuard constructor.
     * @param string $name
     * @param CognitoClient $client
     * @param UserProvider $provider
     * @param Session $session
     * @param null|Request $request

     */
    public function __construct(
        string $name,
        CognitoClient $client,
        UserProvider $provider,
        Session $session,
        ?Request $request = null
    ) {
        $this->client = $client;
        parent::__construct($name, $provider, $session, $request);
    }

    /**
     * @param mixed $user
     * @param array $credentials
     * @return bool
     * @throws InvalidUserModelException
     */
    protected function hasValidCredentials($user, $credentials)
    {
        /** @var Result $response */
        $result = $this->client->authenticate($credentials['email'], $credentials['password']);

        if ($result && $user instanceof Authenticatable) {
            return true;
        }

        return false;
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param  array  $credentials
     * @param  bool   $remember
     * @throws
     * @return bool
     */
    public function attempt(array $credentials = [], $remember = false)
    {
        $this->fireAttemptEvent($credentials, $remember);

        // trace_log($credentials);

        // $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);

        $this->lastAttempted = $user = \Backend\Models\User::where('email', $credentials['email'])->first();

        // trace_log($user->id);

        if ($this->hasValidCredentials($user, $credentials)) {
            $this->login($user, $remember);
            return true;
        }

        $this->fireFailedEvent($user, $credentials);

        return false;
    }
}
