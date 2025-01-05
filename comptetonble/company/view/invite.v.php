<?php

new AdaptativeView('create', function($data, PanelTemplate $t) {
  return (new \company\inviteUi())->create($data->e);
});

new AdaptativeView('update', function($data, PanelTemplate $t) {
  return (new \company\inviteUi())->update($data->e);
});

new JsonView('doUpdate', function($data, AjaxTemplate $t) {
  $t->js()->moveHistory(-1);
});
?>
