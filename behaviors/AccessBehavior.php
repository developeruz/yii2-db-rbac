<?php
/**
 * AccessBehavior for Yii2
 *
 * @author Elle <elleuz@gmail.com>
 * @version 0.1
 * @package AccessBehavior for Yii2
 *
 */
namespace developeruz\db_rbac\behaviors;

use Yii;
use yii\behaviors\AttributeBehavior;
use yii\di\Instance;
use yii\web\Application;
use yii\web\User;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;

class AccessBehavior extends AttributeBehavior {

    public $rules=[];

    private $_rules;

    public function events()
    {
        return [
            Application::EVENT_AFTER_REQUEST => 'interception',
        ];
    }

    public function interception($event)
    {
        $route = Yii::$app->getRequest()->resolve();

        //Проверяем права по конфигу
        $this->createRule();
        $user = Instance::ensure(Yii::$app->user, User::className());
        $request = Yii::$app->getRequest();
        $action = $event->sender->requestedAction;

        if(!$this->cheсkByRule($action, $user, $request))
        {
            //И по AuthManager
            if(!$this->checkPermission($route))
                throw new BadRequestHttpException('Не достаточно прав');
        }
    }

    protected function createRule()
    {
        foreach($this->rules as $controller => $rule)
        {
            foreach ($rule as $singleRule) {
                if (is_array($singleRule)) {
                    $option = [
                        'controllers' => [$controller],
                        'class' => 'yii\filters\AccessRule'
                    ];
                    $this->_rules[] = Yii::createObject(array_merge($option, $singleRule));
                }
            }
        }
    }

    protected function cheсkByRule($action, $user, $request)
    {
        foreach ($this->_rules as $rule) {
            if ($rule->allows($action, $user, $request))
                return true;
        }
        return false;
    }

    protected function checkPermission($route)
    {
        //max: module/controller/action
        $routeVariant = [];
        $routePathTmp = explode('/', $route[0]);
        if(count($routePathTmp) == 3)
        {
            $routeVariant[] = $routePathTmp[0];
            $routeVariant[] = $routePathTmp[0].'/'.$routePathTmp[1];
            $routeVariant[] = $routePathTmp[0].'/'.$routePathTmp[1].'/'.$routePathTmp[2];
        }
        elseif(count($routePathTmp) == 2) {
            $routeVariant[] = $routePathTmp[0];
            $routeVariant[] = $routePathTmp[0].'/'.$routePathTmp[1];
        }
        else {
            $routeVariant[] = $routePathTmp[0];
        }

        foreach($routeVariant as $r)
        {

            if(Yii::$app->user->can($r, $route[1]))
                return true;
        }
        return false;
    }
}