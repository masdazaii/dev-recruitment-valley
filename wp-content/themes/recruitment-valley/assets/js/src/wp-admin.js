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
	console.log(vacanciesData.postType)
	if (vacanciesData.postType == 'rss') {
		import('./vacancy/admin-rss.js').then((module) => {
			console.log(vacanciesData.postType == 'rss' ? 'a' : 'b')
			const rssModule = module.default // Access the default property

			rssModule.init()
		})
	}
})

/** Theme default script */
// (function($) {
	//your javascript function
// })(jQuery);