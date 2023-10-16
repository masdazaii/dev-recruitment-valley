(function($) {
	//your javascript function

	/** Execute only when document is ready */
	$(() => {
		/** Import script for imported vacancy handle */
		if ($('main').hasClass('admin-imported-vacancy-approval')) {
			import('./vacancy/imported-vacancy.js').then((module) => {
				const importedVacancyModule = module.default // Access the default property
				importedVacancyModule.init()
			})
		}
	})
})(jQuery);