<?php
namespace developeruz\db_rbac\views\access;

use Yii;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\grid\DataColumn;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

$this->title = Yii::t('db_rbac', 'Управление ролями');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="news-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('db_rbac', 'Добавить роль'), ['add-role'], ['class' => 'btn btn-success']) ?>
    </p>
<?php
$dataProvider = new ArrayDataProvider([
      'allModels' => Yii::$app->authManager->getRoles(),
      'sort' => [
          'attributes' => ['name', 'description'],
      ],
      'pagination' => [
          'pageSize' => 10,
      ],
 ]);
?>

<?=GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'class'     => DataColumn::className(),
            'attribute' => 'name',
            'label'     => Yii::t('db_rbac', 'Роль')
        ],
        [
            'class'     => DataColumn::className(),
            'attribute' => 'description',
            'label'     => Yii::t('db_rbac', 'Описание')
        ],
        [
            'class'     => DataColumn::className(),
            'label'     => Yii::t('db_rbac', 'Разрешенные доступы'),
            'format'    => ['html'],
            'value'     => function($data) { return implode('<br>',array_keys(ArrayHelper::map(Yii::$app->authManager->getPermissionsByRole($data->name), 'description', 'description')));}
        ],
        ['class' => 'yii\grid\ActionColumn',
            'template' => '{update} {delete}',
            'buttons' =>
                [
                    'update' => function ($url, $model) {
                                return Html::a('<span class="glyphicon glyphicon-pencil"></span>', Url::toRoute(['update-role', 'name' => $model->name]), [
                                        'title' => Yii::t('yii', 'Update'),
                                        'data-pjax' => '0',
                                    ]); },
                    'delete' => function ($url, $model) {
                                return Html::a('<span class="glyphicon glyphicon-trash"></span>', Url::toRoute(['delete-role','name' => $model->name]), [
                                        'title' => Yii::t('yii', 'Delete'),
                                        'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                        'data-method' => 'post',
                                        'data-pjax' => '0',
                                    ]);
                        }
                ]
        ],
        ]
    ]);
?>
</div>