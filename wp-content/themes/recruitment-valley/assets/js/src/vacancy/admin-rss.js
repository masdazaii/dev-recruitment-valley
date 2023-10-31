const rssModule = (function () {
  function initialize() {
    $('*[data-name="rv_rss_select_company"] .acf-input select').on('change', ajaxVacancyOptionValue)
    if ($('#metabox-rv_rss_select_vacancy').length) {
      $('#metabox-rv_rss_select_vacancy').select2()
    }
    // getVacanciesOption()
  }

  function ajaxVacancyOptionValue(e) {
    /** Set selected data */
    if (vacanciesData.rss.selectedCompany !== null && $(e.target).val() == vacanciesData.rss.selectedCompany) {
      if (vacanciesData.rss.selectedVacancies !== null) {
        vacanciesData.rss.selectedVacancies.forEach((option) => {
          var newOption = new Option(option.text, option.id, true, true);
          $('#metabox-rv_rss_select_vacancy').append(newOption).trigger('change');
        })
      }
    } else {
      /** Empty selected vacancies */
      $('#metabox-rv_rss_select_vacancy').val(null).trigger('change');
    }

    $('*#metabox-rv_rss_select_vacancy').select2({
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
        processResults: function(response) {
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

  function getVacanciesOption() {
    $('#metabox-rv_rss_select_vacancy').select2({
      ajax: {
        method: "POST",
        url: vacanciesData.ajaxUrl,
        // dataType: 'json',
        data: {
          action: vacanciesData.rss.action,
          nonce: vacanciesData.rss.nonce,
          company: $('*[data-name="rv_rss_select_company"] .acf-input select').val(),
          result: 'options'
        },
        processResults: function(response) {
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
