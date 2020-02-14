<?php
/**
 * LaminasUser
 * @author Flávio Gomes da Silva Lisboa <flavio.lisboa@fgsl.eti.br>
 * @license AGPL-3.0 <https://www.gnu.org/licenses/agpl-3.0.en.html>
 */
namespace LaminasUser\Authentication\Adapter;

use Interop\Container\ContainerInterface;
use LaminasUser\Entity\UserInterface;
use LaminasUser\Mapper\UserInterface as UserMapperInterface;
use LaminasUser\Options\ModuleOptions;
use Laminas\Authentication\Result as AuthenticationResult;
use Laminas\Crypt\Password\Bcrypt;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Session\Container as SessionContainer;

class Db extends AbstractAdapter
{
    /**
     * @var UserMapperInterface
     */
    protected $mapper;

    /**
     * @var callable
     */
    protected $credentialPreprocessor;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var ModuleOptions
     */
    protected $options;
    
    /** @var AdapterChainEvent **/
    protected $e;

    /**
     * Called when user id logged out
     * @param AdapterChainEvent $e
     */
    public function logout(AdapterChainEvent $e)
    {
        $this->getStorage()->clear();
    }
    
    /**
     * @param AdapterChainEvent $e
     * @return bool
     */
    public function authenticateEvent(AdapterChainEvent $e)
    {
        $this->e = $e;
        return $this->authenticate();
    }
    
    /**
     * @param AdapterChainEvent $e
     * @return bool
     */
    public function authenticate()
    {
        if ($this->isSatisfied()) {
            $storage = $this->getStorage()->read();
            $this->e->setIdentity($storage['identity'])
              ->setCode(AuthenticationResult::SUCCESS)
              ->setMessages(array('Authentication successful.'));
            return;
        }

        $identity   = $this->e->getRequest()->getPost()->get('identity');
        $credential = $this->e->getRequest()->getPost()->get('credential');
        $credential = $this->preProcessCredential($credential);
        /** @var UserInterface|null $userObject */
        $userObject = null;

        // Cycle through the configured identity sources and test each
        $fields = $this->getOptions()->getAuthIdentityFields();
        while (!is_object($userObject) && count($fields) > 0) {
            $mode = array_shift($fields);
            switch ($mode) {
                case 'username':
                    $userObject = $this->getMapper()->findByUsername($identity);
                    break;
                case 'email':
                    $userObject = $this->getMapper()->findByEmail($identity);
                    break;
            }
        }

        if (!$userObject) {
            $this->e->setCode(AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND)
              ->setMessages(array('A record with the supplied identity could not be found.'));
            $this->setSatisfied(false);
            return false;
        }

        if ($this->getOptions()->getEnableUserState()) {
            // Don't allow user to login if state is not in allowed list
            if (!in_array($userObject->getState(), $this->getOptions()->getAllowedLoginStates())) {
                $this->e->setCode(AuthenticationResult::FAILURE_UNCATEGORIZED)
                  ->setMessages(array('A record with the supplied identity is not active.'));
                $this->setSatisfied(false);
                return false;
            }
        }

        $bcrypt = new Bcrypt();
        $bcrypt->setCost($this->getOptions()->getPasswordCost());
        if (!$bcrypt->verify($credential, $userObject->getPassword())) {
            // Password does not match
            $this->e->setCode(AuthenticationResult::FAILURE_CREDENTIAL_INVALID)
              ->setMessages(array('Supplied credential is invalid.'));
            $this->setSatisfied(false);
            return false;
        }

        // regen the id
        $session = new SessionContainer($this->getStorage()->getNameSpace());
        $session->getManager()->regenerateId();

        // Success!
        $this->e->setIdentity($userObject->getId());
        // Update user's password hash if the cost parameter has changed
        $this->updateUserPasswordHash($userObject, $credential, $bcrypt);
        $this->setSatisfied(true);
        $storage = $this->getStorage()->read();
        $storage['identity'] = $this->e->getIdentity();
        $this->getStorage()->write($storage);
        $this->e->setCode(AuthenticationResult::SUCCESS)
          ->setMessages(array('Authentication successful.'));
    }

    protected function updateUserPasswordHash(UserInterface $userObject, $password, Bcrypt $bcrypt)
    {
        $hash = explode('$', $userObject->getPassword());
        if ($hash[2] === $bcrypt->getCost()) {
            return;
        }
        $userObject->setPassword($bcrypt->create($password));
        $this->getMapper()->update($userObject);
        return $this;
    }

    public function preProcessCredential($credential)
    {
        $processor = $this->getCredentialPreprocessor();
        if (is_callable($processor)) {
            return $processor($credential);
        }

        return $credential;
    }

    /**
     * getMapper
     *
     * @return UserMapperInterface
     */
    public function getMapper()
    {
        if (null === $this->mapper) {
            $this->mapper = $this->getServiceManager()->get('LaminasUser_user_mapper');
        }

        return $this->mapper;
    }

    /**
     * setMapper
     *
     * @param UserMapperInterface $mapper
     * @return Db
     */
    public function setMapper(UserMapperInterface $mapper)
    {
        $this->mapper = $mapper;

        return $this;
    }

    /**
     * Get credentialPreprocessor.
     *
     * @return callable
     */
    public function getCredentialPreprocessor()
    {
        return $this->credentialPreprocessor;
    }

    /**
     * Set credentialPreprocessor.
     *
     * @param callable $credentialPreprocessor
     * @return $this
     */
    public function setCredentialPreprocessor($credentialPreprocessor)
    {
        $this->credentialPreprocessor = $credentialPreprocessor;
        return $this;
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param ContainerInterface $serviceManager
     */
    public function setServiceManager(ContainerInterface $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * @param ModuleOptions $options
     */
    public function setOptions(ModuleOptions $options)
    {
        $this->options = $options;
    }

    /**
     * @return ModuleOptions
     */
    public function getOptions()
    {
        if ($this->options === null) {
            $this->setOptions($this->getServiceManager()->get('LaminasUser_module_options'));
        }

        return $this->options;
    }
}
