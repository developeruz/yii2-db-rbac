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
    protected $pattern4Role = '/^[a-zA-Z0-9_-]+$/';
    protected $pattern4Permission = '/^[a-zA-Z0-9_\/-]+$/';

    public function behaviors()
    {
        if (!empty(Yii::$app->controller->module->params['accessRoles'])) {
            return [
                'access' => [
                    'class' => \yii\filters\AccessControl::className(),
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => Yii::$app->controller->module->params['accessRoles'],
                        ],
                    ],
                ],
            ];
        } else return [];
    }

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
            && $this->isUnique(Yii::$app->request->post('name'))
        ) {
            $role = Yii::$app->authManager->createRole(Yii::$app->request->post('name'));
            $role->description = Yii::$app->request->post('description');
            Yii::$app->authManager->add($role);
            $this->setPermissions(Yii::$app->request->post('permissions', []), $role);
            return $this->redirect(Url::toRoute([
                'update-role',
                'name' => $role->name
            ]));
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

        $permissions = ArrayHelper::map(Yii::$app->authManager->getPermissions(), 'name', 'description');
        $role_permit = array_keys(Yii::$app->authManager->getPermissionsByRole($name));

        if ($role instanceof Role) {
            if (Yii::$app->request->post('name')
                && $this->validate(Yii::$app->request->post('name'), $this->pattern4Role)
            ) {
                if (Yii::$app->request->post('name') != $name && !$this->isUnique(Yii::$app->request->post('name'), 'role')) {
                    return $this->render(
                        'updateRole',
                        [
                            'role' => $role,
                            'permissions' => $permissions,
                            'role_permit' => $role_permit,
                            'error' => $this->error
                        ]
                    );
                }
                $role = $this->setAttribute($role, Yii::$app->request->post());
                Yii::$app->authManager->update($name, $role);
                $this->updatePermissions($permissions, Yii::$app->request->post('permissions', []), $role);
                return $this->redirect(Url::toRoute([
                    'update-role',
                    'name' => $role->name
                ]));
            }

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
        $permission = $this->clear(Yii::$app->request->post('name'));
        if ($permission
            && $this->validate($permission, $this->pattern4Permission)
            && $this->isUnique($permission, 'permission')
        ) {
            $permit = Yii::$app->authManager->createPermission($permission);
            $permit->description = Yii::$app->request->post('description', '');
            Yii::$app->authManager->add($permit);
            return $this->redirect(Url::toRoute([
                'update-permission',
                'name' => $permit->name
            ]));
        }

        return $this->render('addPermission', ['error' => $this->error]);
    }

    public function actionUpdatePermission($name)
    {
        $permit = Yii::$app->authManager->getPermission($name);
        if ($permit instanceof Permission) {
            $permission = $this->clear(Yii::$app->request->post('name'));
            if ($permission && $this->validate($permission, $this->pattern4Permission)
            ) {
                if ($permission != $name && !$this->isUnique($permission)) {
                    return $this->render('updatePermission', [
                        'permit' => $permit,
                        'error' => $this->error
                    ]);
                }

                $permit->name = $permission;
                $permit->description = Yii::$app->request->post('description', '');
                Yii::$app->authManager->update($name, $permit);
                return $this->redirect(Url::toRoute([
                    'update-permission',
                    'name' => $permit->name
                ]));
            }

            return $this->render('updatePermission', [
                'permit' => $permit,
                'error' => $this->error
            ]);
        } else {
            throw new BadRequestHttpException(Yii::t('db_rbac', 'Страница не найдена'));
        }
    }

    public function actionDeletePermission($name)
    {
        $permit = Yii::$app->authManager->getPermission($name);
        if ($permit) {
            Yii::$app->authManager->remove($permit);
        }
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

    protected function updatePermissions($allPermissions, $selectedPermissions, $role)
    {
        foreach ($allPermissions as $permit => $description) {
            $permission = Yii::$app->authManager->getPermission($permit);
            if (in_array($permit, $selectedPermissions)) {
                if (!Yii::$app->authManager->hasChild($role, $permission)) {
                    Yii::$app->authManager->addChild($role, $permission);
                }
            } elseif (Yii::$app->authManager->hasChild($role, $permission)) {
                Yii::$app->authManager->removeChild($role, $permission);
            }
        }
    }

    protected function validate($field, $regex)
    {
        $validator = new RegularExpressionValidator(['pattern' => $regex]);
        if ($validator->validate($field)) {
            return true;
        } else {
            $this->error[] = Yii::t('db_rbac', 'Значение "{field}" содержит недопустимые символы', ['field' => $field]);
            return false;
        }
    }

    protected function isUnique($name)
    {
        $role = Yii::$app->authManager->getRole($name);
        $permission = Yii::$app->authManager->getPermission($name);
        if ($permission instanceof Permission) {
            $this->error[] = Yii::t('db_rbac', 'Разрешение с таким именем уже существует') . ':' . $name;
            return false;
        }
        if ($role instanceof Role) {
            $this->error[] = Yii::t('db_rbac', 'Роль с таким именем уже существует') . ':' . $name;
            return false;
        }
        return true;
    }

    protected function clear($value)
    {
        if (!empty($value)) {
            $value = trim($value, "/ \t\n\r\0\x0B");
        }

        return $value;
    }
}
