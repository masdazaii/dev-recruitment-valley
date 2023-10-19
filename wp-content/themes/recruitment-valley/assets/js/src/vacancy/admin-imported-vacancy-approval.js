const importedVacancyModule = (function() {
  function initialize () {
    var $ = jQuery
    initDatatable()
    $('#admin-imported-vacancy-approval-table').on('length.dt', resetTablePagination)
    $('#admin-imported-vacancy-approval-table').on('click', 'td .import-vacancy-aproval-input-role-checkbox', changeRole)
  }

  function initDatatable() {
    var $ = jQuery

    let table = new DataTable('#admin-imported-vacancy-approval-table', {
      serverSide: true,
      processing: true,
      // ordering: false,
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
            <div style="font-weight: bold; margin: 0 0 0.125rem 0; font-size: small;">
              <a href="${row.editUrl}">${row.title}</a>
            </div>
            <a href="${row.editUrl}">Edit</a>
            <a href="${row.trashUrl}" style="font-size: small; color: red">Trash</a>`,
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
        { data : "approvalStatus" },
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
            let isChecked = false;

            let output = `
            <form id="change-role-form-${row.id}" style="">
            <input type="hidden" name="action" value="${vacanciesData.approval.changeRoleAction}">
            <input type="hidden" name="nonce" value="${row.rowNonce}">`

            vacanciesData.list.role.forEach(element => {
              if (Array.isArray(row.role)) {
                if (row.role.length !== 0) {
                  isChecked = row.role.indexOf(element.term_id) > -1 ? true : false
                }
              }

              output += `<div class="input-group">
                <input type="checkbox" id="${element.slug}" name="inputRole[]" class="import-vacancy-aproval-input-role-checkbox" data-id="${row.id}" value="${element.term_id}" `
                + ( Array.isArray(row.role) && row.role.length !== 0 && row.role.indexOf(element.term_id) > -1 ? 'checked="checked"' : '') +`>
                <label for="${element.name}">${element.name}</label>
              </div>`
            })

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
      ]
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
        table.ajax.reload()
      },
    })
      .done((response) => {
        $('.updated notice').remove()
        $('.update-nag').after(`<div class="updated notice is-dismissible"><p>` + response.message +`</p>
        <button type="button" class="notice-dismiss">
          <span class="screen-reader-text">Dismiss this notice.</span>
        </button>
        </div>`)
        table.ajax.reload()
      })
      .fail((response) => {
        $('.update-nag').after('<div class="error notice is-dismissible"><p>' + response.message || response.statusText +'</p></div>')
        table.ajax.reload()
      })
  }

  return {
    init: initialize
  }
})()

export default importedVacancyModule