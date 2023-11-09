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

	/** Import script for rss */
	if (adminData.postType == 'rss') {
		import('./vacancy/admin-rss.js').then((module) => {
			const rssModule = module.default // Access the default property

			rssModule.init()
		})
	}

	/** Import script for admin vacancy edit page */
	if (adminData.postType == 'vacancy' && (adminData.screenAction == 'add' || adminData.screenAction == 'edit')) {
		import('./vacancy/admin-vacancy.js').then((module) => {
			const vacancyModule = module.default // Access the default property

			vacancyModule.init()
		})
	}
})

/** Theme default script */
// (function($) {
	//your javascript function
// })(jQuery);