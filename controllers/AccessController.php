<?php
/**
 * AccessController for Yii2
 *
 * @author Elle <elleuz@gmail.com>
 * @version 0.1
 * @package AccessController for Yii2
 *
 */
namespace developeruz\db_rbac\controllers;

use Yii;
use yii\web\Controller;
use yii\web\BadRequestHttpException;
use yii\rbac\Role;
use yii\rbac\Permission;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\validators\RegularExpressionValidator;

class AccessController extends Controller
{
    protected $error;
    protected $pattern4Role = '/^[a-zA-Z0-9-_]+$/';
    protected $pattern4Permission = '/^[a-zA-Z0-9_\/-]+$/';

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionRole()
    {
        return $this->render('role');
    }

    public function actionAddRole()
    {
        if (Yii::$app->request->post('name')
            && $this->validate(Yii::$app->request->post('name'), $this->pattern4Role)
            && $this->isUnique(Yii::$app->request->post('name'), 'role')
        ) {
            $role = Yii::$app->authManager->createRole(Yii::$app->request->post('name'));
            $role->description = Yii::$app->request->post('description');
            Yii::$app->authManager->add($role);
            $this->setPermissions(Yii::$app->request->post('permissions', []), $role);
            return $this->redirect(Url::toRoute(['update-role', 'name' => $role->name]));
        }

        $permissions = ArrayHelper::map(Yii::$app->authManager->getPermissions(), 'name', 'description');
        return $this->render(
            'addRole',
            [
                'permissions' => $permissions,
                'error' => $this->error
            ]
        );
    }

    public function actionUpdateRole($name)
    {
        $role = Yii::$app->authManager->getRole($name);

        if ($role instanceof Role) {
            if (Yii::$app->request->post('name')
                && $this->validate(Yii::$app->request->post('name'), $this->pattern4Role)
            ) {
                $role = $this->setAttribute($role, Yii::$app->request->post());
                Yii::$app->authManager->update($name, $role);
                Yii::$app->authManager->removeChildren($role);
                $this->setPermissions(Yii::$app->request->post('permissions', []), $role);
                return $this->redirect(Url::toRoute(['update-role', 'name' => $role->name]));
            }

            $permissions = ArrayHelper::map(Yii::$app->authManager->getPermissions(), 'name', 'description');
            $role_permit = array_keys(Yii::$app->authManager->getPermissionsByRole($name));

            return $this->render(
                'updateRole',
                [
                    'role' => $role,
                    'permissions' => $permissions,
                    'role_permit' => $role_permit,
                    'error' => $this->error
                ]
            );
        } else {
            throw new BadRequestHttpException(Yii::t('db_rbac', 'Страница не найдена'));
        }
    }

    public function actionDeleteRole($name)
    {
        $role = Yii::$app->authManager->getRole($name);
        if ($role) {
            Yii::$app->authManager->removeChildren($role);
            Yii::$app->authManager->remove($role);
        }
        return $this->redirect(Url::toRoute(['role']));
    }


    public function actionPermission()
    {
        return $this->render('permission');
    }

    public function actionAddPermission()
    {
        if (Yii::$app->request->post('name')
            && $this->validate(Yii::$app->request->post('name'), $this->pattern4Permission)
            && $this->isUnique(Yii::$app->request->post('name'), 'permission')
        ) {
            $permit = Yii::$app->authManager->createPermission(Yii::$app->request->post('name'));
            $permit->description = Yii::$app->request->post('description', '');
            Yii::$app->authManager->add($permit);
            return $this->redirect(Url::toRoute(['update-permission', 'name' => $permit->name]));
        }

        return $this->render('addPermission', ['error' => $this->error]);
    }

    public function actionUpdatePermission($name)
    {
        $permit = Yii::$app->authManager->getPermission($name);
        if ($permit instanceof Permission) {
            if (Yii::$app->request->post('name')
                && $this->validate(Yii::$app->request->post('name'), $this->pattern4Permission)
            ) {
                $permit = $this->setAttribute($permit, Yii::$app->request->post());
                Yii::$app->authManager->update($name, $permit);
                return $this->redirect(Url::toRoute(['update-permission', 'name' => $permit->name]));
            }

            return $this->render('updatePermission', ['permit' => $permit, 'error' => $this->error]);
        } else throw new BadRequestHttpException(Yii::t('db_rbac','Страница не найдена'));
    }

    public function actionDeletePermission($name)
    {
        $permit = Yii::$app->authManager->getPermission($name);
        if ($permit)
            Yii::$app->authManager->remove($permit);
        return $this->redirect(Url::toRoute(['permission']));
    }

    protected function setAttribute($object, $data)
    {
        $object->name = $data['name'];
        $object->description = $data['description'];
        return $object;
    }

    protected function setPermissions($permissions, $role)
    {
        foreach ($permissions as $permit) {
            $new_permit = Yii::$app->authManager->getPermission($permit);
            Yii::$app->authManager->addChild($role, $new_permit);
        }
    }

    protected function validate($field, $regex)
    {
        $validator = new RegularExpressionValidator(['pattern' => $regex]);
        if ($validator->validate($field, $error))
            return true;
        else {
            $this->error[] = Yii::t('db_rbac','Значение "{field}" содержит не допустимые символы', ['field' => $field]);
            return false;
        };
    }

    protected function isUnique($name, $type)
    {
        if ($type == 'role') {
            $role = Yii::$app->authManager->getRole($name);
            if ($role instanceof Role) {
                $this->error[] = Yii::t('db_rbac','Роль с таким именем уже существует: ') . $name;
                return false;
            } else return true;
        } elseif ($type == 'permission') {
            $permission = Yii::$app->authManager->getPermission($name);
            if ($permission instanceof Permission) {
                $this->error[] = Yii::t('db_rbac','Правило с таким именем уже существует: ') . $name;
                return false;
            } else return true;
        }
    }
}
