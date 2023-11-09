const vacancyModule = (function () {
  const initialize = () => {
    try {
      var el = {
        "customCompanyCountry" : '*[data-name="rv_vacancy_custom_company_country"] .acf-input select',
        "customCompanyCity" : '*[data-name="rv_vacancy_custom_company_city"] .acf-input select'
      }

      registerEventListener(el)

      if (adminData.screenAction == 'edit') {
        setSelectedVacancyValue(el)
      }
    } catch (exception) {
      console.log(exception)
    }
  }

  const registerEventListener = (el) => {
    $(document).on('change', el.customCompanyCountry, handleOnChangeCustomCompanyCountry)
    $(document).on('change', el.customCompanyCity, handleOnChangeCustomCompanyCity)
  }

  const handleOnChangeCustomCompanyCountry = (e) => {
    /** Get ACF Key */
    const acfKey = $('*[data-name="rv_vacancy_custom_company_city"] .acf-input select').attr('id').split('-')[1]

    /** Get acf field */
    const field = acf.getField(acfKey)
    const select = field.$el.find('select')

    /** add loading */
    select.empty()
    let loading = [new Option("Loading option...", "")]
    select.append(loading)
    select.val(null)
    select.trigger('change.select2')

    doPrepareCityOption($(e.target).val(), select)
  }

  const handleOnChangeCustomCompanyCity = (e) => {
    console.log($(e.target).val())
  }

  const setSelectedVacancyValue = (el) => {
    setSelectedCustomCompanyCity(el)
  }

  const setSelectedCustomCompanyCity = (el) => {
    /** Get ACF Key */
    const acfKey = $('*[data-name="rv_vacancy_custom_company_city"] .acf-input select').attr('id').split('-')[1]

    /** Get acf field */
    const field = acf.getField(acfKey)
    const select = field.$el.find('select')

    /** add loading */
    doPrepareCityOption($(el.customCompanyCountry).val(), select, adminData.vacancies.selectedCustomCompanyCity)
  }

  const ajaxGetCityOption = (country) => {
    let cities = [];
    return $.ajax({
      url: adminData.ajaxUrl,
      method: "POST",
      data: {
        'action': adminData.vacancies.optionCityAction,
        'country': country
      },
    })
  }

  const doPrepareCityOption = async (country, select, selected = null) => {
    let cities = await ajaxGetCityOption(country)
      .then((response) => {
        return response.data
      })
      .catch((response) => {
        return null;
      })

    let options = []
    if (cities !== null && cities !== undefined) {
      cities.forEach((element) => {
        options.push(new Option(element.toString(), element.toString()))
      })
    }

    /** Add option to select2
     * First way
     */
    // field.select2.addOption({
    //   id: 12345,
    //   text: 'New Option',
    //   selected: false
    // })

    /** Add option to select2
     * Alternative way
     */
    // Clear previous elements
    select.empty()
    select.select2("close")

    // Add new options to select element
    select.append(options)

    // Set a empty selected option
    if (selected && selected !== null && selected !== undefined && selected !== "") {
      select.val(selected)
    }

    // Refresh select2
    select.trigger('change.select2')
  }

  return {
    init: initialize
  }
})()

export default vacancyModule