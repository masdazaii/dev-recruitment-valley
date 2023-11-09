const vacancyModule = (function () {
  const initialize = () => {
    try {
      var el = {
        "customCompanyCountry" : '*[data-name="rv_vacancy_custom_company_country"] .acf-input select',
        "customCompanyCity" : '*[data-name="rv_vacancy_custom_company_city"] .acf-input select',
        "customCompanyAddress" : '*[data-name="rv_vacancy_custom_company_address"] .acf-input input',
        "customCompanyLongitude" : '*[data-name="rv_vacancy_custom_company_longitude"] .acf-input input',
        "customCompanyLatitude" : '*[data-name="rv_vacancy_custom_company_latitude"] .acf-input input',
      }

      registerEventListener(el)
      setMapsAutocomplete(el)

      if (adminData.screenAction == 'edit') {
        setSelectedVacancyValue(el)
      }
    } catch (exception) {
      console.log(exception)
    }
  }

  const registerEventListener = (el) => {
    $(document).on('change', el.customCompanyCountry, function (e) {
      handleOnChangeCustomCompanyCountry(e, el)
    })
  }

  const handleOnChangeCustomCompanyCountry = (e, el) => {
    /** Get ACF Key */
    const acfKey = $(el.customCompanyCity).attr('id').split('-')[1]

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
    doPrepareCustomCompanyAutocomplete(el)
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

  const setMapsAutocomplete = (el) => {
    doPrepareCustomCompanyAutocomplete(el)
  }

  const ajaxGetCityOption = (country) => {
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
        return null
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

  const doPrepareCustomCompanyAutocomplete = (el) => {
    const center = { lat: 50.064192, lng: -130.605469 }

    // Create a bounding box with sides ~10km away from the center point
    const defaultBounds = {
      north: center.lat + 0.1,
      south: center.lat - 0.1,
      east: center.lng + 0.1,
      west: center.lng - 0.1,
    }

    const input = $(el.customCompanyAddress)[0]

    const options = {
      bounds: defaultBounds,
      fields: ["address_components", "geometry", "icon", "name"],
      strictBounds: false,
    }

    const autocomplete = new google.maps.places.Autocomplete(input, options)

    if ($(el.customCompanyCountry).val() !== null && $(el.customCompanyCountry).val() !== undefined && $(el.customCompanyCountry).val() !== '') {
      const selected = $(el.customCompanyCountry).val()

      autocomplete.setComponentRestrictions({
        country: [adminData.vacancies.countryData[selected].code]
      })
    }

    google.maps.event.addListener(autocomplete, 'place_changed', function () {
      const place = autocomplete.getPlace()

      $(el.customCompanyLongitude).val(place.geometry.location.lng())
      $(el.customCompanyLatitude).val(place.geometry.location.lat())
    })
  }

  return {
    init: initialize
  }
})()

export default vacancyModule