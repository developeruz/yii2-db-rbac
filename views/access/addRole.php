<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Links */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Новая роль';
$this->params['breadcrumbs'][] = ['label' => 'Управление ролями', 'url' => ['role']];
$this->params['breadcrumbs'][] = 'Новая роль';
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
            <?= Html::label('Название роли'); ?>
            <?= Html::textInput('name'); ?>
            * только латинские буквы, цифры и _ -
        </div>

        <div class="form-group">
            <?= Html::label('Текстовое описание'); ?>
            <?= Html::textInput('description'); ?>
        </div>

        <div class="form-group">
            <?= Html::label('Разрешенные доступы'); ?>
            <?= Html::checkboxList('permissions', null, $permissions, ['separator' => '<br>']); ?>
        </div>

        <div class="form-group">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>