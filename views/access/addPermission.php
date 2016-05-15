<?php
namespace twonottwo\db_rbac\views\access;

use Yii;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('db_rbac', 'Новое разрешение');
$this->params['breadcrumbs'][] = ['label' => Yii::t('db_rbac', 'Разрешения'), 'url' => ['permission']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="permit-add-permission">
    <h3><?= Html::encode($this->title) ?></h3>

    <div class="row">
        <div class="col-lg-5">
            <?php ActiveForm::begin([ 'id' => 'add-permission-form']); ?>
            <div class="form-group">
                <?= Html::label(Yii::t('db_rbac', 'Название разрешения',['class' => 'form-control '])); ?>
                <?= Html::textInput('name', '', ['class' => 'form-control']);  ?>

            </div>

            <div class="form-group">
                <?= Html::label(Yii::t('db_rbac', 'Описание', ['class' => 'form-control'])); ?>
                <?= Html::textInput('description', '', ['class' => 'form-control']); ?>
            </div>



            <div class="form-group">
                <?= Yii::t('db_rbac', '
                * Формат module/controller/action<br>
                site/article - доступ к странице site/article<br>
                site - доступ к любым action контроллера site');?>
            </div>

            <div class="form-group">
                <?= Html::submitButton(Yii::t('db_rbac', 'Сохранить'), ['class' => 'btn btn-success']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>


