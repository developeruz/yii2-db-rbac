Dynamic Access Control for Yii2
============================================

##### НА РУССКОМ [ТУТ](https://github.com/developeruz/yii2-db-rbac/blob/master/README.RU.md)

The easiest way to create access control in Yii2 without changes in the code.

This module allows creating roles and rules for Yii role base access (RBAC) via UI.
It also allows assigning roles and rules for user via UI.
Behaviour that checks access by the modules rules.

### Integrations
CMS  | Module
------------ | -------------
EasyiiCMS | https://github.com/developeruz/easyii-rbac-module
*Feel free to request integration with any CMS/Packages which is written on Yii2*

### Installation guide
```bash
$ php composer.phar require developeruz/yii2-db-rbac "*"
```

To work correctly, you must configure the module `authManager` in the application config file (`common/config/main.php` for advanced app 
or `config/web.php` and `config/console` for basic app)
```php
    'components' => [
       'authManager' => [
          'class' => 'yii\rbac\DbManager',
        ],
    ...
    ]
```

Run migration to create `DbManager` table (it means that a connection to the database is already configured for the application)
```bash
$ yii migrate --migrationPath=@yii/rbac/migrations/
```

Add the module
==============

Include module to the config file (`backend/config/main.php` for advanced app or `config/web.php` for basic app)
```php
  'modules' => [
        'permit' => [
            'class' => 'developeruz\db_rbac\Yii2DbRbac',
        ],
    ],
```

If you want to setup layout, put it in the following way
```php
  'modules' => [
        'permit' => [
            'class' => 'developeruz\db_rbac\Yii2DbRbac',
            'layout' => '//admin'
        ],
    ],
```

If you use CNC, be sure that you have correct routing rules for modules
```php
'<module:\w+>/<controller:\w+>/<action:(\w|-)+>' => '<module>/<controller>/<action>',
'<module:\w+>/<controller:\w+>/<action:(\w|-)+>/<id:\d+>' => '<module>/<controller>/<action>',
```

**Adding links**

**/permit/access/role - manage roles**

**/permit/access/permission - manage access**

### Assigning role to a user

The module also has an interface for assigning roles to users.

To work correctly, the module should be specified with `User` class in the module parameters.
```php
'modules' => [
        'permit' => [
            'class' => 'app\modules\db_rbac\Yii2DbRbac',
            'params' => [
                'userClass' => 'app\models\User'
            ]
        ],
    ],
```

User class should implement `developeruz\db_rbac\interfaces\UserRbacInterface`.
In most cases, you have to add function `getUserName()` which should return user's name.

```php
use developeruz\db_rbac\interfaces\UserRbacInterface;

class User extends ActiveRecord implements IdentityInterface, UserRbacInterface
{
...
    public function getUserName()
    {
       return $this->username;
    }
}
```

**For managing role for user with id=1, visit `/permit/user/view/1`**

The easiest way is to add this as a button in `GridView` with users list.
```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],

        'id',
        'username',
        'email:email',

        ['class' => 'yii\grid\ActionColumn',
         'template' => '{view}&nbsp;&nbsp;{update}&nbsp;&nbsp;{permit}&nbsp;&nbsp;{delete}',
         'buttons' =>
             [
                 'permit' => function ($url, $model) {
                     return Html::a('<span class="glyphicon glyphicon-wrench"></span>', Url::to(['/permit/user/view', 'id' => $model->id]), [
                         'title' => Yii::t('yii', 'Change user role')
                     ]); },
             ]
        ],
    ],
]);
```

You can also assign a role to the user in the code, for example when user has been created. 
```php
$userRole = Yii::$app->authManager->getRole('name_of_role');
Yii::$app->authManager->assign($userRole, $user->getId());
```

You also can check if a user has access in code thought `can()` method in User class
```php
Yii::$app->user->can($permissionName);
```
$permissionName - could be a role name or a permission name.

### Configure module's Access Control ###

In the config you can set the list of roles that have access to module functionality.
```php
'modules' => [
        'permit' => [
            'class' => 'app\modules\db_rbac\Yii2DbRbac',
            'params' => [
                'userClass' => 'app\models\User',
                'accessRoles' => ['admin']
            ]
        ],
    ],
```

Behaviour that checks access by the modules rules
=================================================

By using this behaviour you don't need to write `Yii::$app->user->can($permissionName)` in each action. Behaviour will check it automatically.
It is also useful for access control with the third party modules.

### Configure behaviour

You have to include behaviour to the app config file, if you want to check access automatically.

```php
use developeruz\db_rbac\behaviors\AccessBehavior;

 'as AccessBehavior' => [
        'class' => \developeruz\db_rbac\behaviors\AccessBehavior::className(),
 ]
```

On `EVENT_BEFORE_ACTION` behaviour will check access for current user (`Yii::$app->user`) to the action.
Action is allowed if:
 - a user has access to the action (rule: module/controller/action)
 - a user has acceess to any action in the controller (rule: module/controller)
 - a user has access to any action in the module (rule: module)

### Redirection if access denied
By default if a user doesn't have access, behaviour will throw `ForbiddenHttpException`. Application can handle this exception as needed.

You also can configure `login_url` where unauthorized user will be redirected, or `redirect_url` for redirecting a user when access is denied.
```php
    'as AccessBehavior' => [
        'class' => \developeruz\db_rbac\behaviors\AccessBehavior::className(),
        'redirect_url' => '/forbidden',
        'login_url' => Yii::$app->user->loginUrl
    ]
```

### Configure default access rules

After connecting behavior, access is available only to authorized users with certain rights.
You can create default access rights in config file in the same way as you do in controller (`AccessControl`):
```php
    'as AccessBehavior' => [
        'class' => \developeruz\db_rbac\behaviors\AccessBehavior::className(),
        'rules' =>
            ['site' =>
                [
                    [
                        'actions' => ['login', 'index'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['about'],
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ]
            ]
    ]
```

In this example any user has access to `site/login` and `site/index` and only user with role `admin` has access to `site/about`.
The rules described in the configuration take precedence over dynamically configurable rules.

### Configure areas of behavior responsibility
By default, the rule is "all is prohibited unless is allowed." If the behavior is supposed to protect only certain routes, 
and all others should be accessible for all, please set up `protect` parameter
```php
'as AccessBehavior' => [
        'class' => \developeruz\db_rbac\behaviors\AccessBehavior::className(),
        'protect' => ['admin', 'user', 'site/about'],
        'rules' => [
            'user' => [['actions' => ['login'], 'allow' => true ],
                       ['actions' => ['logout'], 'roles' => ['@'], 'allow' => true ]]
        ]
    ],
    
```
In this example, the behavior will check the user's permission to access the page only for paths beginning 
with `admin`,` user` and `site / about`. All other routes are available for all (not verified by the behavior). 
As you can see in the example, the parameter `protect` can be combined with `rules`.

Contributing
============

Contributions are **welcome** and will be fully **credited**.
I accept contributions via Pull Requests. Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

License
=======

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
