<?php
/**
 * AccessBehavior for Yii2
 *
 * @author Elle <elleuz@gmail.com>
 * @version 0.1
 * @package AccessBehavior for Yii2
 *
 */
namespace twonottwo\db_rbac\behaviors;

use Yii;
use yii\behaviors\AttributeBehavior;
use yii\di\Instance;
use yii\base\Module;
use yii\web\Application;
use yii\web\User;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;

class AccessBehavior extends AttributeBehavior {

    public $rules=[];

    private $_rules = [];

    public function events()
    {
        return [
            Module::EVENT_BEFORE_ACTION => 'interception',
        ];
    }

    public function interception($event)
    {
        if(!isset( Yii::$app->i18n->translations['db_rbac'])){
            Yii::$app->i18n->translations['db_rbac'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'ru-Ru',
                'basePath' => '@twonottwo/db_rbac/messages',
            ];
        }

        $route = Yii::$app->getRequest()->resolve();

        //Проверяем права по настройкам приложения (config/main.php)
        $this->createRule();
        $user = Instance::ensure(Yii::$app->user, User::className());
        $request = Yii::$app->getRequest();
        $action = $event->action;
        if(!$this->cheсkByRule($action, $user, $request)) {
            //проверка прав доступа по записям в БД
            if (!$this->checkPermission($route)) {
                if ($user->getIsGuest()) {
                    $user->loginRequired();
                } else {
                    throw new ForbiddenHttpException(Yii::t('db_rbac', 'Недостаточно прав'));
                }
            }
        }
    }

    /**
     * Заполнит массив $_rules набором правил доступа, описанных в настройках (config/main.php) приложения
     */
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
        //$route[0] - is the route, $route[1] - is the associated parameters

        $routePathTmp = explode('/', $route[0]);
        $routeVariant = array_shift($routePathTmp);
        if(Yii::$app->user->can($routeVariant, $route[1]))
            return true;

        foreach($routePathTmp as $routePart)
        {
            $routeVariant .= '/'.$routePart;
            if(Yii::$app->user->can($routeVariant, $route[1]))
                return true;
        }

        return false;
    }
}