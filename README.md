Yii2. Настройки прав доступа через web-интерфейс
============
Модуль для создания прав доступа, ролей и назначения ролей пользователям через web-интерфейс.

##Примечание##
Перед установкой выполнить:
- обновление composer
- подключение приложения к БД

Файлы настроек приложения расположены по адресам:
- `backend/config/main.php`, `frontend/config/main.php` и `common/config/main.php` для advanced
- `config/web.php` и `config/console` для basic

###Установка###

Получаем модуль из репозитория
```bash
$ php composer.phar require twonottwo/yii2-db-rbac "dev-master"
```
либо
```bash
$ composer require twonottwo/yii2-db-rbac "dev-master"
```
Имя ветки может быть отличным от dev-master

В настройках приложения добавляем описание authManager (`common/config/main.php` для advanced, `config/web.php` и `config/console` для basic)
```php
'components' => [
    'authManager' => [
      'class' => 'yii\rbac\DbManager',
    ],
    ...
]
```

Выполняем миграцию создания таблиц для DbManager
```bash
$ yii migrate --migrationPath=@yii/rbac/migrations/
```

Заполняем таблицы первоначальными данными. Заносимые данные необходимо проверить перед выполнением команды
```bash
$ yii migrate --migrationPath=vendor/twonottwo/yii2-db-rbac/migrations/
```

##Подключение модуля к приложению##
В настройках приложения прописываем модуль `permit` (`backend/config/main.php` для advanced и `config/web.php` для basic приложения)

```php
'modules' => [
    'permit' => [
        'class' => 'twonottwo\db_rbac\Yii2DbRbac',
        'params' => [
            'userClass' => 'common\models\User'
        ]
    ],
],
```
Для basic приложения `'userClass' => 'app\models\User'`

Если нужно передать layout, то дописываем таким образом:
```php
'modules' => [
    'permit' => [
        ...
        'layout' => '//admin'
    ],
],
```

Добавляем в класс модели `User` интерфейс `UserRbacInterface` и функцию `getUserName()`
```php
use twonottwo\db_rbac\interfaces\UserRbacInterface;
```

```php
class User extends ActiveRecord implements IdentityInterface, UserRbacInterface {
    public function getUserName()
    {
       return $this->username;
    }
}
```

**URL web-интерфейса модуля**
- /permit/access/permission - права доступа
- /permit/access/role - роли
- /permit/user/view?id=N - назначение ролей пользователю, где N - id записи пользователя

Пример выпадающего списка 'контроль доступа'
```php
$menuItems[] = [
            'label' => Yii::t('db_rbac', 'Контроль доступа'),
            'items' => [
                ['label' => Yii::t('db_rbac', 'Разрешения'), 'url' => '/permit/access/permission'],
                ['label' => Yii::t('db_rbac', 'Роли'), 'url' => '/permit/access/role'],
                ['label' => Yii::t('db_rbac', 'Назначение ролей'), 'url' => '/permit/user/view?id=1'],
            ]
        ];
```

Пример кода Grid со списком пользователей и кнопкой для перехода к назначению ролей
```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],

        'id',
        'username',
        'email:email',
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view}&nbsp;&nbsp;{update}&nbsp;&nbsp;{permit}&nbsp;&nbsp;{delete}',
            'buttons' => [
                'permit' => function ($url, $model) {
                    return Html::a('<span class="glyphicon glyphicon-wrench"></span>', Url::to(['/permit/user/view', 'id' => $model->id]), [
                        'title' => Yii::t('yii', 'Change user role')
                    ]);
                },
            ]
        ],
    ],
]);
```
##Работа с правами доступа в коде##
Присвоить роль пользователю можно в коде, например при создании нового пользователя
```php
$userRole = Yii::$app->authManager->getRole('name_of_role');
Yii::$app->authManager->assign($userRole, $user->getId());
```

Проверить, имеет ли пользователь право на действие можно через метод `can()` компонента User
```php
Yii::$app->user->can($permissionName);
```
$permissionName - может быть как ролью так и правом


##Проверка прав доступа на лету##

Данное поведение позволяет не писать `Yii::$app->user->can($permissionName);` в каждом action

###Подключение поведения###
В настройках того приложения, доступ к которому следует проверять на лету, подключаем поведение
```php
use twonottwo\db_rbac\behaviors\AccessBehavior;

return [
    ...
    'as AccessBehavior' => [
        'class' => AccessBehavior::className(),
    ]
```

С этого момента, после обработки запроса (событие EVENT_AFTER_REQUEST) проверяются права текущего пользователя `(Yii::$app->user)` на выполнение запрашиваемого действия (`Yii::$app->user->can()`).

Действие считается разрешенным, если:
 - пользователю разрешен доступ к конкретному action (правило записано как: module/controller/action)
 - пользователю разрешен доступ к любым action данного контроллера (правило записано как: module/controller)
 - пользователю разрешен доступ к любым action данного модуля (правило записано как: module)

###Настройка прав доступа по умолчанию###

После подключения поведения, доступ становится возможен только авторизованному пользователю, имеющему некие права.
Для исключений из этого правила можно прописать доступы по умолчанию в том же формате AccessControl, что и в контроллере.

При данной настройке ввод логина и пароля обязателен
```php
    'as AccessBehavior' => [
        'class' => AccessBehavior::className(),
        'rules' => [
            'site' => [
                [
                    'actions' => ['error'],
                    'allow' => true,
                ],
                [
                    'actions' => ['login'],
                    'allow' => true,
                    'roles' => ['?'],
                ],
                [
                    'actions' => ['logout', 'index'],
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ]
        ]
    ],
```

А при такой гости смогут увидеть главную страницу
```php
    'as AccessBehavior' => [
        'class' => AccessBehavior::className(),
        'rules' =>[
            'site' => [
                [
                    'actions' => ['index', 'error'],
                    'allow' => true,
                ],
                [
                    'actions' => ['login'],
                    'allow' => true,
                    'roles' => ['?']
                ],
                [
                    'actions' => ['logout'],
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ]
        ]
    ],
```

Правила прописанные в настройках приложения имеют приоритет над динамически настраиваемыми правилами.

##Ограничить доступ к админпанели на премере advanced приложения##
Через модуль:
- Создаем новое правило `adminpanel`
- Создаем новую роль `Administrator` в которую добавляем правило `adminpanel`
- Новую роль присвоим пользователю

В модель `User` добавляем новую функцию `isUserAdmin($user)` она будет проверять есть ли у пользователя доступ к панели администратора
```php
public static function isUserAdmin($user)
{
    return $user->can('adminpanel');

}
```

В модель LoginForm добавляем функцию `loginAdmin`. Функция отличается от стандартной `login` проверкой `User::isUserAdmin`
```php
public function loginAdmin()
{
    if ($this->validate()) {
        Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);

        if (User::isUserAdmin(Yii::$app->user)){
            return true;
        } else {
            Yii::$app->user->logout();
            $this->addError('password', 'Доступ к авторизации закрыт');
            return false;
        }
    } else {
        return false;
    }
}
```

Меняем `actionLogin` контроллера `SiteController` на
```php
public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->loginAdmin()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }
```
Изменение заключается в смене вызываемой функции аутетификации пользователя на `&& $model->loginAdmin()) {`
