setTimeout(function(){
	var head = document.getElementsByTagName('head')[0];
	var script = document.createElement('script');
	script.type = 'text/javascript';
	script.src = 'https://www.google.com/recaptcha/api.js?render=##SITE_KEY##';
	head.appendChild(script);
}, 2000);
