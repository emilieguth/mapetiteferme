<?php
new AdaptativeView('index', function($data, CompanyTemplate $t) {

	$t->tab = 'settings';
	$t->subNav = (new \company\CompanyUi())->getSettingsSubNav($data->eCompany);

	$t->title = s("Configuration pour {value}", $data->eCompany['name']);
	$t->canonical = \company\CompanyUi::urlSettings($data->eCompany);

	$t->package('main')->updateNavSettings($t->canonical);

	$t->mainTitle = '<h1>'.s("Paramétrage").'</h1>';
	$t->mainTitleClass = 'hide-lateral-down';

	$t->footer = '<!-- Brevo Conversations {literal} -->
<script>
    (function(d, w, c) {
        w.BrevoConversationsID = "66b5b5d960b3534e0a061524";
        w[c] = w[c] || function() {
            (w[c].q = w[c].q || []).push(arguments);
        };
        var s = d.createElement("script");
        s.async = true;
        s.src = "https://conversations-widget.brevo.com/brevo-conversations.js";
        if (d.head) d.head.appendChild(s);
    })(document, window, "BrevoConversations");
	</script>
<!-- /Brevo Conversations {/literal} -->';

	echo new \company\CompanyUi()->getSettings($data->eCompany);

});
?>
