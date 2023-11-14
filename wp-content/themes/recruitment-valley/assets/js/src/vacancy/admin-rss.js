const rssModule = (function () {
  function initialize() {
    $('*[data-name="rv_rss_select_company"] .acf-input select').on(
      "change",
      ajaxVacancyOptionValueCompany
    );

    $('*[data-name="rv_rss_select_language"] .acf-input select').on(
      "change",
      ajaxVacancyOptionValueLanguage
    );

    if ($("#metabox-rv_rss_select_vacancy").length) {
      $("#metabox-rv_rss_select_vacancy").select2();
    }
    getVacanciesOption()
  }

  function ajaxVacancyOptionValueCompany(e) {
    ajaxVacancyOptionValue($(e.target).val(), $('*[data-name="rv_rss_select_language"] .acf-input select').val())
  }

  function ajaxVacancyOptionValueLanguage(e) {
    ajaxVacancyOptionValue($('*[data-name="rv_rss_select_company"] .acf-input select').val(), $(e.target).val())
  }

  function ajaxVacancyOptionValue(company, language) {
    /** Set selected data */
      if (adminData.rss.selectedCompany !== null) {
        adminData.rss.selectedCompany.find((value) => {
          if (company.indexOf(value.toString()) !== -1) {
            if (adminData.rss.selectedVacancies !== null) {
              adminData.rss.selectedVacancies.forEach((option) => {
                if (value.toString() == option.company) {
                  // var newOption = new Option(option.text, option.id, true, true)
                  // $('#metabox-rv_rss_select_vacancy').append(newOption).trigger('change')
                  if ($('#metabox-rv_rss_select_vacancy').find("option[value='" + option.id + "']").length) {
                    $('#metabox-rv_rss_select_vacancy').val(option.id).trigger('change');
                  } else {
                      // Create a DOM Option and pre-select by default
                      var newOption = new Option(option.text, option.id, true, true);
                      // Append it to the select
                      $('#metabox-rv_rss_select_vacancy').append(newOption).trigger('change');
                  }
                }
              })
            }
          }
        })
      } else if (adminData.rss.selectedLanguage == language) {
        if (adminData.rss.selectedVacancies !== null) {
          adminData.rss.selectedVacancies.forEach((option) => {
            if ($('#metabox-rv_rss_select_vacancy').find("option[value='" + option.id + "']").length) {
              $('#metabox-rv_rss_select_vacancy').val(option.id).trigger('change');
            } else {
                // Create a DOM Option and pre-select by default
                var newOption = new Option(option.text, option.id, true, true);
                // Append it to the select
                $('#metabox-rv_rss_select_vacancy').append(newOption).trigger('change');
            }
          })
        }
      } else {
        /** Empty selected vacancies */
        $('#metabox-rv_rss_select_vacancy').val(null).trigger('change')
      }

      $("*#metabox-rv_rss_select_vacancy").select2({
        ajax: {
          method: "POST",
          url: adminData.ajaxUrl,
          // dataType: 'json',
          data: {
            action: adminData.rss.action,
            nonce: adminData.rss.nonce,
            company: company || $('*[data-name="rv_rss_select_company"] .acf-input select').val(),
            language: language || $('*[data-name="rv_rss_select_language"] .acf-input select').val(),
            result: "options",
          },
          processResults: function (response) {
            // console.log(response);
            /** Format the response */
            let options = []
            let i = 1
            for (let [key, value] of Object.entries(response.data)) { // This only work on > ES 6
              options.push({
                id: key,
                text: value,
              });
              i++;
            }

            return {
              results: options
            }
          }
        }
      })
  }

  function getVacanciesOption() {
    $("#metabox-rv_rss_select_vacancy").select2({
      ajax: {
        method: "POST",
        url: adminData.ajaxUrl,
        // dataType: 'json',
        data: {
          action: adminData.rss.action,
          nonce: adminData.rss.nonce,
          company: $(
            '*[data-name="rv_rss_select_company"] .acf-input select'
          ).val(),
          result: "options",
        },
        processResults: function (response) {
          /** Format the response */
          let options = []
          let i = 1
          for (let [key, value] of Object.entries(response.data)) { // This only work on > ES 6
            options.push({
              id: key,
              text: value,
            });
            i++;
          }

          return {
            results: options
          }
        }
      }
    })
  }

  return {
    init: initialize,
  };

  var current = 0;
})();

export default rssModule;
