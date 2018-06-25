<?php

namespace developeruz\db_rbac\views\access;

use Yii;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\components\Route;

/* @var $this yii\web\View */
/* @var $model common\models\Links */
/* @var $form yii\widgets\ActiveForm */
$this->title                   = Yii::t('db_rbac', 'Новое правило');
$this->params['breadcrumbs'][] = ['label' => Yii::t('db_rbac', 'Правила доступа'),
    'url'   => ['permission']];
$this->params['breadcrumbs'][] = Yii::t('db_rbac', 'Новое правило');
?>
<div class="news-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="links-form">
        <?php
        if (!empty($error)) {
            ?>
            <div class="error-summary">
                <?php
                echo implode('<br>', $error);
                ?>
            </div>
            <?php
        }
        ?>

        <h3>Роутинг приложения</h3>
        <p>
            <code style="height: 300px; display: block; overflow-y: auto">
                <?php
                foreach (Route::getAppRoutes()as $value) {
                    echo $value . '<br/>';
                }
                ?>
            </code>
        </p>

        <?php $form = ActiveForm::begin(); ?>

        <div class="form-group">
            <?= Html::label(Yii::t('db_rbac', 'Текстовое описание')); ?>
            <?= Html::textInput('description'); ?>
        </div>

        <div class="form-group">
            <?= Html::label(Yii::t('db_rbac', 'Разрешенный доступ')); ?>
            <?= Html::textInput('name'); ?>
            <?= Yii::t('db_rbac', '<br>* Формат: <strong>module/controller/action</strong><br><strong>site/article</strong> - доступ к странице "site/article"<br><strong>site</strong> - доступ к любым action контроллера "site"'); ?>
        </div>

        <div class="form-group">
            <?=
            Html::submitButton(Yii::t('db_rbac', 'Сохранить'), [
                'class' => 'btn btn-success'])
            ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>
