const rssModule = (function () {
  const el = {
    rssSelectCompany  : '*[data-name="rv_rss_select_company"] .acf-input select',
    rssSelectLanguage : '*[data-name="rv_rss_select_language"] .acf-input select',
    rssSelectPaidStatus : '*[data-name="rv_rss_select_paid_status"] .acf-input select',
    rssMetaboxSelectVacancy : '#metabox-rv_rss_select_vacancy',
  }

  const initialize = () => {
    try {
      registerEventListener(el)

      /** Initiate select2 */
      if ($(el.rssMetaboxSelectVacancy).length) {
        // $(el.rssMetaboxSelectVacancy).select2();
        doPrepareVacancyOption($(el.rssSelectCompany).val(), $(el.rssSelectLanguage).val(), $(el.rssSelectPaidStatus).val(), el)
      }
    } catch (exception) {
      console.log(exception)
    }
  }

  const registerEventListener = (el) => {
    $(document).on('change', el.rssSelectCompany, function (e) {
      handleOnChangeSelectCompany(e, el)
    })

    $(document).on('change', el.rssSelectLanguage, function (e) {
      handleOnChangeSelectLanguage(e, el)
    })

    $(document).on('change', el.rssSelectPaidStatus, function (e) {
      handleOnChangeSelectPaidStatus(e)
    })
  }

  const handleOnChangeSelectCompany = (e, el) => {
    doPrepareVacancyOption($(e.target).val(), $(el.rssSelectLanguage).val(), $(el.rssSelectPaidStatus).val())
  }

  const handleOnChangeSelectLanguage = (e, el) => {
    doPrepareVacancyOption($(el.rssSelectCompany).val(), $(e.target).val(), $(el.rssSelectPaidStatus).val())
  }

  const handleOnChangeSelectPaidStatus = (e) => {
    doPrepareVacancyOption($(el.rssSelectCompany).val(), $(el.rssSelectLanguage).val(), $(e.target).val())
  }

  const doPrepareVacancyOption = (company, language, paidStatus) => {
    /** If you want to use ACF Select instead Metabox :
     * Check admin-vacancy script as reference,
     * that's page using acf select2, and change the option based on another field or so called chaind dropdown.
     */

    /** Set selected data */
    $(el.rssMetaboxSelectVacancy).val(null).trigger('change')

    if (adminData.rss.selectedCompany !== null && adminData.rss.selectedCompany != false && adminData.rss.selectedCompany !== undefined) {
      adminData.rss.selectedCompany.find((value) => {
        if (company.indexOf(value.toString()) !== -1) {
          if (adminData.rss.selectedVacancies !== null) {
            adminData.rss.selectedVacancies.forEach((option) => {
              if (value.toString() == option.company) {
                // var newOption = new Option(option.text, option.id, true, true)
                // $(el.rssMetaboxSelectVacancy).append(newOption).trigger('change')
                if ($(el.rssMetaboxSelectVacancy).find("option[value='" + option.id + "']").length) {
                  $(el.rssMetaboxSelectVacancy).val(option.id).trigger('change');
                } else {
                    // Create a DOM Option and pre-select by default
                    var newOption = new Option(option.text, option.id, true, true);
                    // Append it to the select
                    $(el.rssMetaboxSelectVacancy).append(newOption).trigger('change');
                }
              }
            })
          }
        }
      })
    } else {
      $(el.rssMetaboxSelectVacancy).val(null).trigger('change')
    }

    if (adminData.rss.selectedLanguage != false && adminData.rss.selectedLanguage !== undefined && adminData.rss.selectedLanguage.value == language) {
      if (adminData.rss.selectedVacancies !== null) {
        adminData.rss.selectedVacancies.forEach((option) => {
          if (option.language == language) {
            if ($(el.rssMetaboxSelectVacancy).find("option[value='" + option.id + "']").length) {
              $(el.rssMetaboxSelectVacancy).val(option.id).trigger('change');
            } else {
                // Create a DOM Option and pre-select by default
                var newOption = new Option(option.text, option.id, true, true);
                // Append it to the select
                $(el.rssMetaboxSelectVacancy).append(newOption).trigger('change');
            }
          }
        })
      }
    } else {
      $(el.rssMetaboxSelectVacancy).val(null).trigger('change')
    }

    if (adminData.rss.selectedPaidStatus != false && adminData.rss.selectedLanguage !== undefined && (adminData.rss.selectedPaidStatus.value == paidStatus || paidStatus == 'both')) {
      if (adminData.rss.selectedPaidStatus.value !== null) {
        adminData.rss.selectedVacancies.forEach((option) => {
          if (adminData.rss.selectedPaidStatus.value !== 'both') {
            if (adminData.rss.selectedPaidStatus.value == 'paid') {
              if (option.isPaid) {
                if ($(el.rssMetaboxSelectVacancy).find("option[value='" + option.id + "']").length) {
                  $(el.rssMetaboxSelectVacancy).val(option.id).trigger('change');
                } else {
                    // Create a DOM Option and pre-select by default
                    var newOption = new Option(option.text, option.id, true, true);
                    // Append it to the select
                    $(el.rssMetaboxSelectVacancy).append(newOption).trigger('change');
                }
              }
            } else if (adminData.rss.selectedPaidStatus.value == 'free') {
              if (!option.isPaid) {
                if ($(el.rssMetaboxSelectVacancy).find("option[value='" + option.id + "']").length) {
                  $(el.rssMetaboxSelectVacancy).val(option.id).trigger('change');
                } else {
                    // Create a DOM Option and pre-select by default
                    var newOption = new Option(option.text, option.id, true, true);
                    // Append it to the select
                    $(el.rssMetaboxSelectVacancy).append(newOption).trigger('change');
                }
              }
            } else {
              if ($(el.rssMetaboxSelectVacancy).find("option[value='" + option.id + "']").length) {
                $(el.rssMetaboxSelectVacancy).val(option.id).trigger('change');
              } else {
                  // Create a DOM Option and pre-select by default
                  var newOption = new Option(option.text, option.id, true, true);
                  // Append it to the select
                  $(el.rssMetaboxSelectVacancy).append(newOption).trigger('change');
              }
            }
          }
        })
      }
    } else {
      $(el.rssMetaboxSelectVacancy).val(null).trigger('change')
    }

    $(el.rssMetaboxSelectVacancy).select2({
      ajax: {
        method: "POST",
        url: adminData.ajaxUrl,
        // dataType: 'json',
        data: {
          action: adminData.rss.action,
          nonce: adminData.rss.nonce,
          company: company || $(el.rssSelectCompany).val(),
          language: language || $(el.rssSelectLanguage).val(),
          paidStatus: paidStatus || $(el.rssSelectPaidStatus).val(),
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

  return {
    init: initialize,
  };
})();

export default rssModule;
