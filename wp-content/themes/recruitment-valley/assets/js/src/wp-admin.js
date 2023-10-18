var $ = jQuery

/** Execute only when document is ready */
$(() => {
	/** Import script for imported vacancy handle */
	if ($('main').hasClass('admin-imported-vacancy-approval')) {
		import('./vacancy/admin-imported-vacancy-approval.js').then((module) => {
			const importedVacancyModule = module.default // Access the default property

			importedVacancyModule.init()
		})
	}
})

/** Theme default script */
// (function($) {
	//your javascript function
// })(jQuery);