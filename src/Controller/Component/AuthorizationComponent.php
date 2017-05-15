<?php
namespace Authorization\Controller\Component;

use Cake\Authorization\Gate;
use Cake\Controller\Component;
use Cake\Network\Exception\MethodNotAllowedException;
use Doctrine\Tests\Common\Annotations\Bar\Name;

class AuthorizationComponent extends Component {

    protected $_defaultConfig = [
        'gateClass' => Gate::class
    ];

    protected $gate;

    public function initilize(array $config)
    {
        parent::initialize($config);
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
        $controller = $this->getController();
        $request = $controller->request;

        $class = $request->getParam('controller');
        $plugin = $request->getParam('plugin');
        if ($plugin) {
            $class = $plugin . '.' . $class;
        }

        $policyClass = App::className($class, 'Authorization/Policy', 'Policy');
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

        if ($this->gate->allows($request->getParam('action'), [$controller])) {
            throw new MethodNotAllowedException();
        };
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
        if (!method_exists($this->gate, $name)) {
            return;
        }

        return call_user_func_array([$this->gate, $name], $arguments);
    }

}
