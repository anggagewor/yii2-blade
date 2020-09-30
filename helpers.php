<?php
function asset($file){
    return Yii::getAlias('@web/'.$file);
}
function view($view, $params = []){
    return Yii::$app->controller->render($view.'.blade', $params);
}
function csrf_token(){
    return Yii::$app->request->csrfToken;
}
function csrf_field(){
    return '<input type="hidden" name="'.Yii::$app->request->csrfParam.'" value="'.csrf_token().'" />';
}
