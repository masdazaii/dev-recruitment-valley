const vacancyModule = (function () {
  const initialize = () => {
    try {
      var el = {
        "vacancyCountry"      : '*[data-name="rv_vacancy_country"] .acf-input select',
        "vacancyCountryCode"  : '*[data-name="rv_vacancy_country_code"] .acf-input input',

        "vacancyCity"           : '*[data-name="placement_city"] .acf-input input',
        "vacancyCityLongitude"  : '*[data-name="city_longitude"] .acf-input input',
        "vacancyCityLatitude"   : '*[data-name="city_latitude"] .acf-input input',

        "vacancyPlacementAddress"   : '*[data-name="placement_address"] .acf-input input',
        "vacancyPlacementLongitude" : '*[data-name="placement_address_longitude"] .acf-input input',
        "vacancyPlacementLatitude"  : '*[data-name="placement_address_latitude"] .acf-input input',

        "customCompanyCountry"      : '*[data-name="rv_vacancy_custom_company_country"] .acf-input select',
        "customCompanyCity"         : '*[data-name="rv_vacancy_custom_company_city"] .acf-input select',
        "customCompanyAddress"      : '*[data-name="rv_vacancy_custom_company_address"] .acf-input input',
        "customCompanyLongitude"    : '*[data-name="rv_vacancy_custom_company_longitude"] .acf-input input',
        "customCompanyLatitude"     : '*[data-name="rv_vacancy_custom_company_latitude"] .acf-input input',
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
    $(document).on('change', el.vacancyCountry, function (e) {
      handleOnChangeVacancyCountry(e, el)
    })

    $(document).on('change', el.customCompanyCountry, function (e) {
      handleOnChangeCustomCompanyCountry(e, el)
    })
  }

  const handleOnChangeVacancyCountry = (e, el) => {
    /** Get ACF Key */
    const acfVacancyCityKey = $(el.vacancyCity).attr('id').split('-')[1]

    /** Get acf field */
    const vacancyCityField = acf.getField(acfVacancyCityKey)
    const vacancyCitySelect = vacancyCityField.$el.find('select')

    doPrepareCityOption($(e.target).val(), vacancyCitySelect)
    doPrepareVacancyAddressAutocomplete(el, $(e.target).val())
    setCountryCodeValue(el, $(e.target).val())
  }

  const handleOnChangeCustomCompanyCountry = (e, el) => {
    /** Get ACF Custom Company City Key */
    const acfCustomCompanyCityKey = $(el.customCompanyCity).attr('id').split('-')[1]

    /** Get ACF Custom Company City field */
    const customCompanyCityField = acf.getField(acfCustomCompanyCityKey)
    const customCompanyCitySelect = customCompanyCityField.$el.find('select')

    /** add loading */
    customCompanyCitySelect.empty()
    let loading = [new Option("Loading option...", "")]
    customCompanyCitySelect.append(loading)
    customCompanyCitySelect.val(null)
    customCompanyCitySelect.trigger('change.select2')

    doPrepareCityOption($(e.target).val(), customCompanyCitySelect)
    doPrepareCustomCompanyAddressAutocomplete(el, $(e.target).val())
  }

  const handleOnChangeCustomCompanyCity = (e) => {
    console.log($(e.target).val())
  }

  const setCountryCodeValue = (el, country) => {
    let countryCode = adminData.vacancies.countryData[country].code
    $(el.vacancyCountryCode).val(countryCode)
  }

  const setSelectedVacancyValue = (el) => {
    setSelectedCustomCompanyCity(el)
    setSelectedVacancyCity(el)
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

  const setSelectedVacancyCity = (el) => {
    /** Get ACF Key */
    const acfVacancyCityKey = $(el.vacancyCity).attr('id').split('-')[1]

    /** Get acf field */
    const vacancyCityField = acf.getField(acfVacancyCityKey)
    const vacancyCitySelect = vacancyCityField.$el.find('select')

    /** add loading */
    doPrepareCityOption($(el.vacancyCountry).val(), vacancyCitySelect, adminData.vacancies.selectedVacancyCity)
  }

  const setMapsAutocomplete = (el) => {
    doPrepareCustomCompanyAddressAutocomplete(el)
    doPrepareVacancyAddressAutocomplete(el)
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

  const doPrepareCustomCompanyAddressAutocomplete = (el) => {
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

  const doPrepareVacancyAddressAutocomplete = (el) => {
    const center = { lat: 50.064192, lng: -130.605469 }

    // Create a bounding box with sides ~10km away from the center point
    const defaultBounds = {
      north: center.lat + 0.1,
      south: center.lat - 0.1,
      east: center.lng + 0.1,
      west: center.lng - 0.1,
    }

    const input = $(el.vacancyPlacementAddress)[0]

    const options = {
      bounds: defaultBounds,
      fields: ["address_components", "geometry", "icon", "name"],
      strictBounds: false,
    }

    const autocomplete = new google.maps.places.Autocomplete(input, options)

    if ($(el.vacancyCountry).val() !== null && $(el.vacancyCountry).val() !== undefined && $(el.vacancyCountry).val() !== '') {
      const selected = $(el.vacancyCountry).val()

      console.log(adminData.vacancies.countryData[selected].code)
      autocomplete.setComponentRestrictions({
        country: adminData.vacancies.countryData[selected].code.toString()
      })
    }

    google.maps.event.addListener(autocomplete, 'place_changed', function () {
      const place = autocomplete.getPlace()

      $(el.vacancyPlacementLongitude).val(place.geometry.location.lng())
      $(el.vacancyPlacementLatitude).val(place.geometry.location.lat())
    })
  }

  return {
    init: initialize
  }
})()

export default vacancyModule