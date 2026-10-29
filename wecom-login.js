(function ($) {
	'use strict';

	function showLoginPanel() {
		if (!window.ww || !window.hetao29WecomLoginConfig || !document.getElementById('login-b')) {
			return;
		}
		window.ww.createWWLoginPanel({
			el: '#login-b',
			params: {
				login_type: 'CorpApp',
				appid: window.hetao29WecomLoginConfig.appid,
				agentid: window.hetao29WecomLoginConfig.agentid,
				redirect_uri: window.hetao29WecomLoginConfig.redirect_uri,
				state: window.hetao29WecomLoginConfig.state,
				redirect_type: 'top',
				panel_size: 'small'
			}
		});
	}

	$(function () {
		$('#login-a').on('click', function (event) {
			event.preventDefault();
			$('#loginform').empty().append('<div id="login-b" style="display:flex;justify-content:center;width:100%"></div>');
			showLoginPanel();
		});
		showLoginPanel();
	});
})(jQuery);
