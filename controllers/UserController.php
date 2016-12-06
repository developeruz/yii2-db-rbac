<?php
/**
 * Controller to attache role for user for Yii2
 *
 * @author Elle <elleuz@gmail.com>
 * @version 0.1
 * @package UserController for Yii2
 *
 */
namespace developeruz\db_rbac\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\BadRequestHttpException;
use developeruz\db_rbac\interfaces\UserRbacInterface;
use yii\web\NotFoundHttpException;

class UserController extends Controller
{
    public $moduleName = 'permit';

    public function beforeAction($action)
    {
        if (empty(Yii::$app->controller->module->params['userClass'])) {
            throw new BadRequestHttpException(Yii::t('db_rbac', 'Необходимо указать класс User в настройках модуля'));
        }

        $user = new Yii::$app->controller->module->params['userClass']();

        if (!$user instanceof UserRbacInterface) {
            throw new BadRequestHttpException(Yii::t('db_rbac',
                'UserClass должен реализовывать интерфейс developeruz\db_rbac\UserRbacInterface'));
        }

        return parent::beforeAction($action);
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function behaviors()
    {
        $behavior = [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'update' => ['post'],
                    '*' => ['get'],
                ],
            ]
        ];

        if (!empty(Yii::$app->controller->module->params['accessRoles'])) {
            $behavior['access'] = [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => Yii::$app->controller->module->params['accessRoles'],
                    ],
                ],
            ];
        }

        return $behavior;
    }

    public function actionView($id)
    {
        $roles = ArrayHelper::map(Yii::$app->authManager->getRoles(), 'name', 'description');
        $user_permit = array_keys(Yii::$app->authManager->getRolesByUser($id));
        $user = $this->findUser($id);
        return $this->render('view', [
            'user' => $user,
            'roles' => $roles,
            'user_permit' => $user_permit,
            'moduleName' => Yii::$app->controller->module->id
        ]);
    }

    public function actionUpdate($id)
    {
        $user = $this->findUser($id);
        Yii::$app->authManager->revokeAll($user->getId());
        if (Yii::$app->request->post('roles')) {
            foreach (Yii::$app->request->post('roles') as $role) {
                $new_role = Yii::$app->authManager->getRole($role);
                Yii::$app->authManager->assign($new_role, $user->getId());
            }
        }
        return $this->redirect(Url::to([
            "/" . Yii::$app->controller->module->id . "/user/view",
            'id' => $user->getId()
        ]));
    }

    private function findUser($id)
    {
        $class = new Yii::$app->controller->module->params['userClass']();
        $user = $class::findIdentity($id);
        if (empty($user)) {
            throw new NotFoundHttpException(Yii::t('db_rbac', 'Пользователь не найден'));
        } else {
            return $user;
        }
    }
}
