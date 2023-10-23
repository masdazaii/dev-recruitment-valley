const rssModule = (function () {
  function initialize() {
    $('*[data-name="rv_rss_select_company"] .acf-input select').on('change', ajaxVacancyOptionValue)
  }

  function ajaxVacancyOptionValue(e) {
    $('*[data-name="rv_rss_select_vacancy"] .acf-input select').select2({
      ajax: {
        method: "POST",
        url: vacanciesData.ajaxUrl,
        // dataType: 'json',
        data: {
          action: vacanciesData.rss.action,
          nonce: vacanciesData.rss.nonce,
          company: $(e.target).val() || $('*[data-name="rv_rss_select_company"] .acf-input select').val(),
          result: 'options'
        },
        beforeSend: () => {
          console.log(vacanciesData.rss)
        },
        processResults: function(response) {
          console.log(response)
          /** Format the response */
          let options = [];
          let i = 1;
          for (let [key, value] of Object.entries(response.data)) { // This only work on > ES 6
            options.push({
              id: key,
              text: value
            })
            i++
          }

          return {
            results: options
          };
        }
      }
    })
  }

  return {
    init: initialize
  }

  var current = 0
})()

export default rssModule
