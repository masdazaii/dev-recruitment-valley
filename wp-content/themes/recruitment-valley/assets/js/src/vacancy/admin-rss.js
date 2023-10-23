const rssModule = (function () {
  function initialize () {
    $(document).on('change, [data-name="rv_rss_select_company"] .acf-input input', ajaxVacancyOptionValue)
    // console.log('zzzz')
  }

  function ajaxVacancyOptionValue(e) {
    console.log($(e.target).val())
  }

  return {
    init: initialize
  }
})()

export default rssModule
