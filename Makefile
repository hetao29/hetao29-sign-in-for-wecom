pot:
	xgettext --language=PHP --from-code=UTF-8 -o languages/hetao29-sign-in-for-wecom.pot -k__ -k_e -kesc_html__ -kesc_html_e -n --sort-by-file *.php
zh_CN:
	msgfmt -o languages/hetao29-sign-in-for-wecom-zh_CN.mo languages/hetao29-sign-in-for-wecom-zh_CN.po
