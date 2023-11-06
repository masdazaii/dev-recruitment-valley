const importedVacancyModule = (function() {
  function initialize () {
    var $ = jQuery
    initDatatable()
    $('#admin-imported-vacancy-approval-table').on('length.dt', resetTablePagination)
    // $('#admin-imported-vacancy-approval-table').on('change', 'td .admin-approval-role', changeRole)
  }

  function initDatatable() {
    var $ = jQuery

    let table = new DataTable('#admin-imported-vacancy-approval-table', {
      serverSide: true,
      processing: true,
      // aLengthMenu: [[1, 50, 75, -1], [1, 50, 75, "All"]],
      // ordering: false,
      columnDefs: [
        { "targets": 0, "width": "auto" },
        { "targets": 1, "width": "8%" },
        { "targets": 2, "width": "10%" },
        { "targets": 3, "width": "6%" },
        { "targets": 4, "width": "5%" },
        { "targets": 5, "width": "15%" },
        { "targets": 5, "width": "15%" },
        { "targets": 6, "width": "15%" },
        { "targets": 7, "width": "10%" },
        { "targets": 7, "width": "8%" },
      ],
      ajax: {
        url: vacanciesData.ajaxUrl,
        method: 'GET',
        data: {
          action: vacanciesData.list.action,
        },
        dataSrc: (response) => {
          return response.data
        }
      },
      columns: [
        { data : "title",
          render : (data, type, row, meta) => `
            <div style="font-weight: bold margin: 0 0 0.125rem 0 font-size: small">
              <a href="${row.editUrl}">${row.title}</a>
            </div>
            <a href="${row.editUrl}">Edit</a>
            <a href="${row.trashUrl}" style="color: red">Trash</a>`,
          "sortable" : true
        },
        { data : "vacancyStatus",
          render : (data, type, row, meta) => {
            let output = ''

            if (row.isExpired) {
              output += `Expired - ` + data
            } else {
              output += data
            }

            return output
          },
          "sortable" : false
        },
        { data : "approvalStatus",
          render : (data, type, row, meta) => {
            return data
          },
          "sortable" : false
        },
        { data : "paidStatus",
          render : (data, type, row, meta) => {
            if (row.paidStatus) {
              return `Paid`
            } else {
              return `Free`
            }
          },
          "sortable" : false
        },
        { data : "isImported",
          render : (data, type, row, meta) => {
            if (row.isImported) {
              return `Imported`
            } else {
              return `-`
            }
          },
          "sortable" : false
        },
        { data : "role",
          render : (data, type, row, meta) => {
            let isChecked = false

            let output = `
            <form id="change-role-form-${row.id}" style="">
            <input type="hidden" name="action" value="${vacanciesData.approval.changeRoleAction}">
            <input type="hidden" name="nonce" value="${row.rowNonce}">
            <select id="admin-approval-role-${row.id}" class="admin-approval-role" name="inputRole[]" data-id="${row.id}" style="width: 100%" multiple></select>`

            output += `</div>`

            /** Set selected role value.
             * same as the function in initComplete
             */
            // data.forEach((role) => {
            //   if ($("#admin-approval-role-" + row.id).find("option[value='" + role + "']").length) {
            //     $("#admin-approval-role-" + row.id).val(role).trigger('change')
            //   } else {
            //     // Create a DOM Option and pre-select by default
            //     var newOption = new Option(vacanciesData.approval.options.role[role].text, vacanciesData.approval.options.role[role].id, true, true)
            //     // Append it to the select
            //     $("#admin-approval-role-" + row.id).append(newOption).trigger('change')
            //   }
            // })

            return output
          },
          "sortable" : false
        },
        { data : "sector",
          render : (data, type, row, meta) => {
            let isChecked = false

            let output = `
            <form id="change-sector-form-${row.id}" style="">
            <input type="hidden" name="action" value="${vacanciesData.approval.changeSectorAction}">
            <input type="hidden" name="nonce" value="${row.rowNonce}">
            <select id="admin-approval-sector-${row.id}" class="admin-approval-sector" name="inputSector[]" data-id="${row.id}" style="width: 100%" multiple>
            </select>`

            output += `</div>`

            return output
          },
          "sortable" : false
        },
        { data : "publishDate" },
        { data : "id",
          render : (data, type, row, meta) => {
            let output = `<form method="POST" action="${vacanciesData.postUrl}">
              <input type="hidden" name="action" value="handle_imported_vacancy_approval">
              <input type="hidden" name="nonce" value="${row.rowNonce}">
              <input type="hidden" name="vacancyID" value="${row.id}">`

            if (row.isExpired) {
              output += `<button disabled>Approve</button>
              <button name="approval" value="rejected">Reject</button>`
            } else {
              output += `<button name="approval" value="approved">Approve</button>
              <button name="approval" value="rejected">Reject</button>`
            }

            output += `</form>`

            return output
          },
          "sortable": false
        },
      ],
      initComplete: function (settings, response) { // response is ajax response data
        /** Init select 2 */
        $('.admin-approval-role').select2({
          placeholder: '-- Select vacancy role --',
          // allowClear: true,
          data: Object.values(vacanciesData.approval.options.role)
        })

        $('.admin-approval-sector').select2({
          placeholder: '-- Select vacancy sector --',
          // allowClear: true,
          data: Object.values(vacanciesData.approval.options.sector)
        })

        /** Set selected value
         * This actualy can set inside coloumn function render, and it worked fine.
         * BUT, coloumn.render function RUN WHEN DATATABLE INITIALIZATION PROCESS,
         * while initComplete RUN WHEN INITIALIZATION IS DONE.
         *
         * I still think in coloumn.render would be easier.
         * CMMIIW
         */
        response.data.forEach((vacancy) => {
          if (vacancy.role && vacancy.role !== undefined && vacancy.role !== null && Array.isArray(vacancy.role)) {
            vacancy.role.forEach((role) => {
              if ($("#admin-approval-role-" + vacancy.id).find("option[value='" + role + "']").length) {
                $("#admin-approval-role-" + vacancy.id).val(vacancy.role).trigger('change')
              } else {
                // Create a DOM Option and pre-select by default
                var newOption = new Option(vacanciesData.approval.options.role[role].text, vacanciesData.approval.options.role[role].id, true, true)
                // Append it to the select
                $("#admin-approval-role-" + vacancy.id).append(newOption).trigger('change')
              }
            })
          }

          if (vacancy.sector && vacancy.sector !== undefined && vacancy.sector !== null && Array.isArray(vacancy.sector)) {
            vacancy.sector.forEach((sector) => {
              if ($("#admin-approval-sector-" + vacancy.id).find("option[value='" + sector + "']").length) {
                $("#admin-approval-sector-" + vacancy.id).val(sector).trigger('change')
              } else {
                var newOption = new Option(vacanciesData.approval.options.sector[sector].text, vacanciesData.approval.options.sector[sector].id, true, true)
                $("#admin-approval-sector-" + vacancy.id).append(newOption).trigger('change')
              }
            })
          }
        })

        /** Set event listener for role and sector,
         * don't put it in module init since when init, multiple "change" event will be triggered.
         */
        $('#admin-imported-vacancy-approval-table').on('select2:select', 'td .admin-approval-role', changeRole)
        $('#admin-imported-vacancy-approval-table').on('select2:unselect', 'td .admin-approval-role', changeRole)
        $('#admin-imported-vacancy-approval-table').on('select2:clear', 'td .admin-approval-role', changeRole)

        $('#admin-imported-vacancy-approval-table').on('select2:select', 'td .admin-approval-sector', changeSector)
        $('#admin-imported-vacancy-approval-table').on('select2:unselect', 'td .admin-approval-sector', changeSector)
        $('#admin-imported-vacancy-approval-table').on('select2:clear', 'td .admin-approval-sector', changeSector)
      },
    })
  }

  function resetTablePagination(e, settings, len) {
    let table = new DataTable('#admin-imported-vacancy-approval-table')
    // Get the current page
    var currentPage = table.page()

    // Check if the number of rows per page is already set to the desired value
    if (table.page.len() !== len) {
      // Reset the pagination to the first page with the new length
      table.page.len(len).draw()

      // Go back to the original page
      table.page(currentPage).draw(false)
    }
  }

  function changeRole(e) {
    e.preventDefault()

    let form = $('#change-role-form-' + $(this).attr('data-id')).serializeArray()
    form.push({
      name: "vacancyID",
      value: $(this).attr('data-id'),
    })

    let table = new DataTable('#admin-imported-vacancy-approval-table')
    $.ajax({
      url: vacanciesData.ajaxUrl,
      method: "POST",
      data: $.param(form),
      beforeSend: function () {
        $('#admin-imported-vacancy-approval-table tbody').hide()
      },
    })
      .done((response) => {
        $('.updated notice').remove()
        $('.update-nag').after(`<div class="updated notice is-dismissible"><p>` + response.message +`</p>
        <button type="button" class="notice-dismiss">
          <span class="screen-reader-text">Dismiss this notice.</span>
        </button>
        </div>`)

        refreshDatatable()
        $('#admin-imported-vacancy-approval-table tbody').show()
      })
      .fail((response) => {
        $('.update-nag').after('<div class="error notice is-dismissible"><p>' + response.message || response.statusText +'</p></div>')
        // var currentPage = table.page()
        // table.ajax.reload().page(currentPage).draw(false)
        refreshDatatable()
        $('#admin-imported-vacancy-approval-table tbody').show()
      })
  }

  function changeSector(e) {
    let form = $('#change-sector-form-' + $(this).attr('data-id')).serializeArray()
    form.push({
      name: "vacancyID",
      value: $(this).attr('data-id'),
    })

    let table = new DataTable('#admin-imported-vacancy-approval-table')
    $.ajax({
      url: vacanciesData.ajaxUrl,
      method: "POST",
      data: $.param(form),
      beforeSend: function () {
        $('#admin-imported-vacancy-approval-table tbody').hide()
      },
    })
      .done((response) => {
        $('.updated notice').remove()
        $('.update-nag').after(`<div class="updated notice is-dismissible"><p>` + response.message +`</p>
        <button type="button" class="notice-dismiss">
          <span class="screen-reader-text">Dismiss this notice.</span>
        </button>
        </div>`)

        refreshDatatable()
        $('#admin-imported-vacancy-approval-table tbody').show()
      })
      .fail((response) => {
        $('.update-nag').after('<div class="error notice is-dismissible"><p>' + response.message || response.statusText +'</p></div>')

        refreshDatatable()
        $('#admin-imported-vacancy-approval-table tbody').show()
      })
  }

  function refreshDatatable() {
    let table = new DataTable('#admin-imported-vacancy-approval-table')
    // Get the current page
    var currentPage = table.page()
    table.ajax.reload().page(currentPage).draw(false)

    table.on('draw.dt', (e, settings, json) => {
      var datatableApi = new $.fn.dataTable.Api(settings)
      var response = datatableApi.ajax.json()

      /** Init select 2 */
      $('.admin-approval-role').select2({
        placeholder: '-- Select vacancy role --',
        // allowClear: true,
        data: Object.values(vacanciesData.approval.options.role)
      })

      $('.admin-approval-sector').select2({
        placeholder: '-- Select vacancy sector --',
        // allowClear: true,
        data: Object.values(vacanciesData.approval.options.sector)
      })

      console.log(response.data)
      response.data.forEach((vacancy) => {
        if (vacancy.role && vacancy.role !== undefined && vacancy.role !== null && Array.isArray(vacancy.role)) {
          vacancy.role.forEach((role) => {
            if ($("#admin-approval-role-" + vacancy.id).find("option[value='" + role + "']").length) {
              $("#admin-approval-role-" + vacancy.id).val(vacancy.role).trigger('change')
            } else {
              // Create a DOM Option and pre-select by default
              var newOption = new Option(vacanciesData.approval.options.role[role].text, vacanciesData.approval.options.role[role].id, true, true)
              // Append it to the select
              $("#admin-approval-role-" + vacancy.id).append(newOption).trigger('change')
            }
          })
        }

        // console.log(vacancy)
        if (vacancy.sector && vacancy.sector !== undefined && vacancy.sector !== null && Array.isArray(vacancy.sector)) {
          console.log(vacancy.sector)
          vacancy.sector.forEach((sector) => {
            if ($("#admin-approval-sector-" + vacancy.id).find("option[value='" + sector + "']").length) {
              $("#admin-approval-sector-" + vacancy.id).val(vacancy.sector).trigger('change')
            } else {
              var newOption = new Option(vacanciesData.approval.options.sector[sector].text, vacanciesData.approval.options.sector[sector].id, true, true)
              $("#admin-approval-sector-" + vacancy.id).append(newOption).trigger('change')
            }
          })
        }
      })
    })
  }

  return {
    init: initialize
  }
})()

export default importedVacancyModule