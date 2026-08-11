window.onAmazonLoginReady = function() {
	amazon.Login.setClientId('##CLIENT_ID##');
};
(function(d) {
	var a = d.createElement('script'); a.type = 'text/javascript';
	a.async = true; a.id = 'amazon-login-sdk';
    a.src = 'https://assets.loginwithamazon.com/sdk/na/login1.js';
    d.getElementById('amazon-root').appendChild(a);
})(document);

document.getElementById('LoginWithAmazon').onclick = function() {
    options = {}
    options.scope = 'profile';
    options.scope_data = {
        'profile' : {'essential': false}
    };
    amazon.Login.authorize(options,
        '##LOGIN_URL##');
    return false;
};
