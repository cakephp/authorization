<?php
namespace Authorization\Controller\Component;

use Authorization\BouncerInterface;
use Cake\Authorization\Bouncer;
use Cake\Controller\Component;
use Cake\Network\Exception\MethodNotAllowedException;

class AuthorizationComponent extends Component {

    protected $_defaultConfig = [
        'gateClass' => Bouncer::class,
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
		$bouner =  $controller->request->getAttribute('authorization');
		if ($bouner instanceof BouncerInterface) {
			$this->gate = $bouner;
			return;
		}

        $this->gate = $this->buildGate();
    }

    public function buildGate()
    {
        $controller = $this->getController();
        $request = $controller->request;

        $gate = new $this->getConfig('gateClass');

        $policy = $this->getPolicyForController();
        if ($policy) {
            $gate->addPolicy(get_class($controller), $policy);
        }

        $identity = $request->getAttribute('identity');
        if (!empty($identity)) {
            $gate->setIdentity();
        }

        return $gate;
    }

    protected function getPolicyForController()
    {
        $request = $this->getController()->request;
        $class = $request->getParam('controller');
        $plugin = $request->getParam('plugin');

        if ($plugin) {
            $class = $plugin . '.' . $class;
        }

        $policyClass = App::className($class, 'Policy', 'Policy');
        if (class_exists($policyClass)) {
            return new $policyClass();
        }
    }

    /**
     * startup
     *
     * @return void
     */
    public function startup()
    {
        $this->checkAction();
    }

    /**
     * @throws MethodNotAllowedException
     * @return void
     */
    protected function checkAction()
    {
        $controller = $this->getController();
        $request = $controller->request;

        $redirect = $this->getConfig('redirect');
        if ($redirect) {
            $this->getController()->redirect($redirect);
        }

        if ($this->gate->allows($request->getParam('action'), [$controller])) {
            throw new $this->getConfig('notAllowedException');
        }
    }

    /**
     * Magic call
     *
     * @param $name Name
     * @param array $arguments Arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->gate, $name], $arguments);
    }

}
