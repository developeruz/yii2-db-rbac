<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<h3>Управление ролями пользователя <?= $user->getUserName(); ?></h3>
<?php $form = ActiveForm::begin(['action' => ["/{$moduleName}/user/update", 'id' => $user->getId()]]); ?>

<?= Html::checkboxList('roles', $user_permit, $roles, ['separator' => '<br>']); ?>

<div class="form-group">
    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>

