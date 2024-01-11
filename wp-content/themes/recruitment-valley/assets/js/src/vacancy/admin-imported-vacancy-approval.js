const importedVacancyModule = (function() {
  function initialize () {
    var $ = jQuery
    initDatatable()
    $('#admin-imported-vacancy-approval-table').on('length.dt', changeLengthDatatable)
    $('#admin-imported-vacancy-approval-table').on('draw.dt', drawDatatable)
    $("#admin-imported-vacancy-approval-table").on('page.dt', pageChangeDatatable)

    $("#import-vacancy-aproval-input-bulk-checkbox-all").on('change', selectAllToggle)
  }

  function initDatatable () {
    console.log('initDatatable')
    var $ = jQuery

    let table = new DataTable('#admin-imported-vacancy-approval-table', {
      serverSide: true,
      processing: true,
      dom: 'lBfrtip',
      buttons: [
        {
          text: 'Submit',
          action: function ( e, dt, node, config ) {
            $("#approval-list").trigger('submit')
          }
        }
      ],
      // aLengthMenu: [[1, 50, 75, -1], [1, 50, 75, "All"]],
      // ordering: false,
      order: [[8, 'desc']],
      ajax: {
        url: adminData.ajaxUrl,
        method: 'GET',
        data: {
          action: adminData.list.action,
        },
        dataSrc: (response) => {
          return response.data
        }
      },
      columnDefs: [
        {
          "targets": 0,
          "orderable": false,
          "checkboxes": true
        },
        { "targets": 1, "width": "auto" },
        { "targets": 2, "width": "8%" },
        { "targets": 3, "width": "10%" },
        { "targets": 4, "width": "6%" },
        { "targets": 5, "width": "3%" },
        { "targets": 6, "width": "15%" },
        { "targets": 7, "width": "15%" },
        { "targets": 8, "width": "10%" },
        // { "targets": 9, "width": "7%" }, // Disable published date : feedback 13 Dec 2023
        { "targets": 9, "width": "10%" },
      ],
      columns: [
        { data : "id",
          render : (data, type, row, meta) => {
            let output = `<input type="checkbox" id="import-vacancy-aproval-input-bulk-checkbox-${row.id}" name="inputBulkSelected[]" class="import-vacancy-aproval-input-bulk-checkbox" data-id="${row.id}" value="${row.id}">`

            return output
          },
          "sortable" : false
        },
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
            <input type="hidden" name="action" value="${adminData.approval.changeRoleAction}">
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
            //     var newOption = new Option(adminData.approval.options.role[role].text, adminData.approval.options.role[role].id, true, true)
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
            <input type="hidden" name="action" value="${adminData.approval.changeSectorAction}">
            <input type="hidden" name="nonce" value="${row.rowNonce}">
            <select id="admin-approval-sector-${row.id}" class="admin-approval-sector" name="inputSector[]" data-id="${row.id}" style="width: 100%" multiple>
            </select>`

            output += `</div>`

            return output
          },
          "sortable" : false
        },
        { data : "importedDate" },
        // { data : "publishDate" },  // Disable published date : feedback 13 Dec 2023
        { data : "id",
          render : (data, type, row, meta) => {
            let output = `<form method="POST" action="${adminData.postUrl}">
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
          data: Object.values(adminData.approval.options.role)
        })

        $('.admin-approval-sector').select2({
          placeholder: '-- Select vacancy sector --',
          // allowClear: true,
          data: Object.values(adminData.approval.options.sector)
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
                var newOption = new Option(adminData.approval.options.role[role].text, adminData.approval.options.role[role].id, true, true)
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
                var newOption = new Option(adminData.approval.options.sector[sector].text, adminData.approval.options.sector[sector].id, true, true)
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

  /** Event when length is change in Datatable
   * e (event), settings, len : length
   *
   * PLEASE DON'T COMPLAIN ABOUT ABBREVIATION SINCE THIS IS JUST FOLLOWING DATATABLE DOCS.
   *
   * why written e : following datatable docs (and other common js practices),
   * that's why other function using 'e' arguments as a form of uniformity.
  */
  function changeLengthDatatable (e, settings, len) {
    console.log('resetTablePagination')
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

    var datatableApi = new $.fn.dataTable.Api(table)
    var response = datatableApi.ajax.json()

    /** Init select 2 */
    $('.admin-approval-role').select2({
      placeholder: '-- Select vacancy role --',
      // allowClear: true,
      data: Object.values(adminData.approval.options.role)
    })

    $('.admin-approval-sector').select2({
      placeholder: '-- Select vacancy sector --',
      // allowClear: true,
      data: Object.values(adminData.approval.options.sector)
    })

    response.data.forEach((vacancy) => {
      if (vacancy.role && vacancy.role !== undefined && vacancy.role !== null && Array.isArray(vacancy.role)) {
        vacancy.role.forEach((role) => {
          if ($("#admin-approval-role-" + vacancy.id).find("option[value='" + role + "']").length) {
            $("#admin-approval-role-" + vacancy.id).val(vacancy.role).trigger('change')
          } else {
            // Create a DOM Option and pre-select by default
            var newOption = new Option(adminData.approval.options.role[role].text, adminData.approval.options.role[role].id, true, true)
            // Append it to the select
            $("#admin-approval-role-" + vacancy.id).append(newOption).trigger('change')
          }
        })
      }

      if (vacancy.sector && vacancy.sector !== undefined && vacancy.sector !== null && Array.isArray(vacancy.sector)) {
        vacancy.sector.forEach((sector) => {
          if ($("#admin-approval-sector-" + vacancy.id).find("option[value='" + sector + "']").length) {
            $("#admin-approval-sector-" + vacancy.id).val(vacancy.sector).trigger('change')
          } else {
            var newOption = new Option(adminData.approval.options.sector[sector].text, adminData.approval.options.sector[sector].id, true, true)
            $("#admin-approval-sector-" + vacancy.id).append(newOption).trigger('change')
          }
        })
      }
    })
  }

  function drawDatatable (e, settings){
    /** Get datatable json data */
    var datatableApi = new $.fn.dataTable.Api(settings)
    var response = datatableApi.ajax.json()

    /** Init select 2 */
    $('.admin-approval-role').select2({
      placeholder: '-- Select vacancy role --',
      // allowClear: true,
      data: Object.values(adminData.approval.options.role)
    })

    $('.admin-approval-sector').select2({
      placeholder: '-- Select vacancy sector --',
      // allowClear: true,
      data: Object.values(adminData.approval.options.sector)
    })

    response.data.forEach((vacancy) => {
      if (vacancy.role && vacancy.role !== undefined && vacancy.role !== null && Array.isArray(vacancy.role)) {
        vacancy.role.forEach((role) => {
          if ($("#admin-approval-role-" + vacancy.id).find("option[value='" + role + "']").length) {
            $("#admin-approval-role-" + vacancy.id).val(vacancy.role).trigger('change')
          } else {
            // Create a DOM Option and pre-select by default
            var newOption = new Option(adminData.approval.options.role[role].text, adminData.approval.options.role[role].id, true, true)
            // Append it to the select
            $("#admin-approval-role-" + vacancy.id).append(newOption).trigger('change')
          }
        })
      }

      if (vacancy.sector && vacancy.sector !== undefined && vacancy.sector !== null && Array.isArray(vacancy.sector)) {
        vacancy.sector.forEach((sector) => {
          if ($("#admin-approval-sector-" + vacancy.id).find("option[value='" + sector + "']").length) {
            $("#admin-approval-sector-" + vacancy.id).val(vacancy.sector).trigger('change')
          } else {
            var newOption = new Option(adminData.approval.options.sector[sector].text, adminData.approval.options.sector[sector].id, true, true)
            $("#admin-approval-sector-" + vacancy.id).append(newOption).trigger('change')
          }
        })
      }
    })
  }

  function pageChangeDatatable (e, settings) {
    $('input[name="bulkActionAll"]').prop('checked', false)
  }

  function changeRole (e) {
    e.preventDefault()

    let form = $('#change-role-form-' + $(this).attr('data-id')).serializeArray()
    form.push({
      name: "vacancyID",
      value: $(this).attr('data-id'),
    })

    let table = new DataTable('#admin-imported-vacancy-approval-table')
    $.ajax({
      url: adminData.ajaxUrl,
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

        $('#admin-imported-vacancy-approval-table tbody').show()

        let table = new DataTable('#admin-imported-vacancy-approval-table')
        var currentPage = table.page()
        table.ajax.reload().page(currentPage).draw(false)
      })
      .fail((response) => {
        $('.update-nag').after('<div class="error notice is-dismissible"><p>' + response.message || response.statusText +'</p></div>')

        $('#admin-imported-vacancy-approval-table tbody').show()

        let table = new DataTable('#admin-imported-vacancy-approval-table')
        var currentPage = table.page()
        table.ajax.reload().page(currentPage).draw(false)
      })
  }

  function changeSector (e) {
    let form = $('#change-sector-form-' + $(this).attr('data-id')).serializeArray()
    form.push({
      name: "vacancyID",
      value: $(this).attr('data-id'),
    })

    let table = new DataTable('#admin-imported-vacancy-approval-table')
    $.ajax({
      url: adminData.ajaxUrl,
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

        $('#admin-imported-vacancy-approval-table tbody').show()

        let table = new DataTable('#admin-imported-vacancy-approval-table')
        var currentPage = table.page()
        table.ajax.reload().page(currentPage).draw(false)
      })
      .fail((response) => {
        $('.update-nag').after('<div class="error notice is-dismissible"><p>' + response.message || response.statusText +'</p></div>')

        $('#admin-imported-vacancy-approval-table tbody').show()

        let table = new DataTable('#admin-imported-vacancy-approval-table')
        var currentPage = table.page()
        table.ajax.reload().page(currentPage).draw(false)
      })
  }

  function selectAllToggle(e) {
    if ($('input[name="bulkActionAll"]:checked')?.length > 0) {
      $('input[name="inputBulkSelected[]"]').prop("checked", true)
    } else {
      $('input[name="inputBulkSelected[]"]').prop("checked", false)
    }
  }

  return {
    init: initialize
  }
})()

export default importedVacancyModule