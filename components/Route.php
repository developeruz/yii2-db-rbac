<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace developeruz\db_rbac\components;

use Yii;
use Exception;
use yii\helpers\Inflector;

/**
 * Description of Route
 *
 */
class Route
{
    //put your code here

    /**
     * Lists all Route models.
     *
     * @return mixed
     */
    public static function getAppRoutes()
    {
        $result = [];
        static::getRouteRecrusive(Yii::$app, $result);

        return $result;
    }

    /**
     * Get route(s) recrusive
     *
     * @param \yii\base\Module $module
     * @param array $result
     */
    private static function getRouteRecrusive($module, &$result)
    {
        foreach ($module->getModules() as $id => $child) {
            if (($child = $module->getModule($id)) !== null) {
                static::getRouteRecrusive($child, $result);
            }
        }
        foreach ($module->controllerMap as $id => $type) {
            static::getControllerActions($type, $id, $module, $result);
        }
        $namespace = trim($module->controllerNamespace, '\\') . '\\';
        static::getControllerFiles($module, $namespace, '', $result);
        $result[]  = ($module->uniqueId === '' ? '' : '/' . $module->uniqueId) . '/*';
    }

    /**
     * Get list controller under module
     *
     * @param \yii\base\Module $module
     * @param string $namespace
     * @param string $prefix
     * @param mixed $result
     *
     * @return mixed
     */
    private static function getControllerFiles($module, $namespace, $prefix,
                                               &$result)
    {
        $path = @Yii::getAlias('@' . str_replace('\\', '/', $namespace));
        if (!is_dir($path)) {
            return;
        }
        foreach (scandir($path) as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (is_dir($path . '/' . $file)) {
                static::getControllerFiles($module, $namespace . $file . '\\', $prefix . $file . '/', $result);
            } elseif (strcmp(substr($file, -14), 'Controller.php') === 0) {
                $id        = Inflector::camel2id(substr(basename($file), 0, -14));
                $className = $namespace . Inflector::id2camel($id) . 'Controller';
                if (strpos($className, '-') === false && class_exists($className)
                    && is_subclass_of($className, 'yii\base\Controller')) {
                    static::getControllerActions($className, $prefix . $id, $module, $result);
                }
            }
        }
    }

    /**
     * Get list action of controller
     *
     * @param mixed $type
     * @param string $id
     * @param \yii\base\Module $module
     * @param string $result
     */
    private static function getControllerActions($type, $id, $module, &$result)
    {
        /* @var $controller \yii\base\Controller */
        $controller = Yii::createObject($type, [$id, $module]);
        static::getActionRoutes($controller, $result);
        $result[]   = '/' . $controller->uniqueId . '/*';
    }

    /**
     * Get route of action
     *
     * @param \yii\base\Controller $controller
     * @param array $result all controller action.
     */
    private static function getActionRoutes($controller, &$result)
    {
        $prefix = '/' . $controller->uniqueId . '/';
        foreach ($controller->actions() as $id => $value) {
            $result[] = $prefix . $id;
        }
        $class = new \ReflectionClass($controller);
        foreach ($class->getMethods() as $method) {
            $name = $method->getName();
            if ($method->isPublic() && !$method->isStatic() && strpos($name, 'action') === 0
                && $name !== 'actions') {
                $result[] = $prefix . Inflector::camel2id(substr($name, 6));
            }
        }
    }
}