const importedVacancyModule = (function() {
  function initialize () {
    var $ = jQuery
    initDatatable()
    $('#admin-imported-vacancy-approval-table').on('length.dt', resetTablePagination)
  }

  function initDatatable() {
    var $ = jQuery

    let table = new DataTable('#admin-imported-vacancy-approval-table', {
      serverSide: true,
      processing: true,
      ordering: false,
      ajax: {
        url: importedVacanciesData.ajaxUrl,
        method: 'GET',
        data: {
          action: importedVacanciesData.action,
        },
        dataSrc: (response) => {
          return response.data
        }
      },
      columns: [
        { data : "no" },
        { data : "title" },
        { data : "vacancyStatus" },
        { data : "approvalStatus" },
        { data : "publishDate" },
        { data : "id",
          render : (data, type, row, meta) => `
            <form method="POST" action="${importedVacanciesData.postUrl}">
              <input type="hidden" name="action" value="handle_imported_vacancy_approval">
              <input type="hidden" name="nonce" value="${row.rowNonce}">
              <input type="hidden" name="vacancyID" value="${row.id}">
              <button name="approval" value="approved">Approve</button>
              <button name="approval" value="rejected">Reject</button>
            </form>`,
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

  return {
    init: initialize
  }
})()

export default importedVacancyModule