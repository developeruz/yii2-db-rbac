<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Links */
/* @var $form yii\widgets\ActiveForm */
$this->title = 'Новое правило';
$this->params['breadcrumbs'][] = ['label' => 'Правила доступа', 'url' => ['permission']];
$this->params['breadcrumbs'][] = 'Новое правило';
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

        <?php $form = ActiveForm::begin(); ?>

        <div class="form-group">
            <?= Html::label('Текстовое описание'); ?>
            <?= Html::textInput('description'); ?>
        </div>

        <div class="form-group">
            <?= Html::label('Разрешенный доступ'); ?>
            <?= Html::textInput('name'); ?>
            * Формат module/controller/action<br>
            site/article - доступ к странице site/article<br>
            site - доступ к любым action контроллера site
        </div>

        <div class="form-group">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>
