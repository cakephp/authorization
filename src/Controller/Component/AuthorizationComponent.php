<?php
namespace Authorization\Controller\Component;

use Authorization\BouncerInterface;
use Cake\Authorization\Bouncer;
use Cake\Controller\Component;
use Cake\Network\Exception\MethodNotAllowedException;

/**
 * Authorization component
 */
class AuthorizationComponent extends Component {

    /**
     * Default config
     *
     * @var array
     */
    protected $_defaultConfig = [
        'gateClass' => Bouncer::class,
        'policyClass' => null,
        'redirect' => false,
        'redirectMessage' => '',
        'notAllowedException' => MethodNotAllowedException::class
    ];

    /**
     * Gate Instance
     *
     * @var \Cake\Authorization\Gate
     */
    protected $gate;

    /**
     * @inheritDoc
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        $controller = $this->getController();
        $bouncer =  $controller->request->getAttribute('authorization');
        if ($bouncer instanceof BouncerInterface) {
            $this->gate = $bouncer;
            return;
        }

        $this->gate = $this->buildGate();
    }

    /**
     * Builds the gate object
     *
     * @return \Authorization\BouncerInterface
     */
    public function buildGate()
    {
        $controller = $this->getController();
        $request = $controller->request;

        $gate = new $this->getConfig('gateClass');

        $policy = $this->getPolicyClassForController();
        if ($policy) {
            $gate->addPolicy(get_class($controller), $policy);
        }

        $identity = $request->getAttribute('identity');
        if (!empty($identity)) {
            $gate->setIdentity($identity);
        }

        return $gate;
    }

    /**
     * Gets the policy class for the controller
     *
     * @return bool|string
     */
    protected function getPolicyClassForController()
    {
        $policyClass = $this->getConfig('policyClass');
        if (!empty($policyClass)) {
            return $policyClass;
        }

        $request = $this->getController()->request;
        $class = $request->getParam('controller');
        $plugin = $request->getParam('plugin');

        if ($plugin) {
            $class = $plugin . '.' . $class;
        }

        $policyClass = App::className($class, 'Policy/Controller', 'Policy');
        if (class_exists($policyClass)) {
            return new $policyClass();
        }

        return false;
    }

    /**
     * Startup
     *
     * @return void
     */
    public function startup()
    {
        $this->checkAction();
    }

    /**
     * Checks if the current action is allowed for the user
     *
     * @throws MethodNotAllowedException
     * @return void
     */
    protected function checkAction()
    {
        $controller = $this->getController();
        $request = $controller->request;
        $config = $this->$this->getConfig();

        $granted = $this->gate->allows(
            $request->getParam('action'), 
            [$controller]
        );
        
        if (!$granted && !empty($config['notAllowedException'])) {
            throw new $config['notAllowedException']();
        }

        if (!$granted && !empty($config['redirect'])) {
            $this->_registry->get('Flash')->error($config['redirectMessage']);
            $this->getController()->redirect($config['redirect']);
        }
    }

    /**
     * Magic call, delegates the method calls to the gate object
     *
     * @param $name Method name
     * @param array $arguments Arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->gate, $name], $arguments);
    }

}
