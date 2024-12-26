<?php
new AdaptativeView('createCustomer', function($data, PanelTemplate $t) {
	return (new \company\InviteUi())->createCustomer($data->eCustomer);
});

new AdaptativeView('check', function($data, MainTemplate $t) {

	$t->title = s("Accepter une invitation");
	$t->metaNoindex = TRUE;

	echo (new \company\InviteUi())->check($data->eInvite);

});

new AdaptativeView('accept', function($data, MainTemplate $t) {

	$t->title = s("Invitation acceptée !");
	$t->metaNoindex = TRUE;

	echo (new \company\InviteUi())->accept($data->eInvite);

});
?>
