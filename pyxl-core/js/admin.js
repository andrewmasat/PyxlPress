// Filename: admin.js

// Require.js allows us to configure shortcut alias
// Their usage will become more apparent futher along in the tutorial.
require.config({
	shim: {
		bootstrap: {deps :['jquery']}
	},
	paths: {
		// Core Libraries
		jquery: 'libs/jquery/jquery-min',
		underscore: 'libs/underscore/underscore-min',
		backbone: 'libs/backbone/backbone-min',

		// 3rd Party Plugins
		bootstrap: 'libs/bootstrap/bootstrap-min',
		chart: 'libs/chart/chart.min',
		pace: 'libs/pace/pace.min',

		// Template Location
		templates: '../templates',

		// PyxlPress Plugins
		plugins: '../../pyxl-content/plugins'
	}
	// ,
	// urlArgs: "bust=" + (new Date()).getTime()
});

require([
	// Load our app module and pass it to our definition function
	'adminApp',

	], function(App){
	// The "app" dependency is passed in as "App"
	// Again, the other dependencies passed in are not "AMD" therefore don't pass a parameter to this function
	App.initialize();
});