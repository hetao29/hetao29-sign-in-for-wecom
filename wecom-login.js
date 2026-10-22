(function ($) {
	'use strict';

	function showLoginPanel() {
		if (!window.ww || !window.wecomLoginConfig || !document.getElementById('login-b')) {
			return;
		}
		window.ww.createWWLoginPanel({
			el: '#login-b',
			params: {
				login_type: 'CorpApp',
				appid: window.wecomLoginConfig.appid,
				agentid: window.wecomLoginConfig.agentid,
				redirect_uri: window.wecomLoginConfig.redirect_uri,
				state: 'loginState',
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
