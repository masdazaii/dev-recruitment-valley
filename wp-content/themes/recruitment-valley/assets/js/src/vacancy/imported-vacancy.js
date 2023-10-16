const importedVacancyModule = (function() {
  function initialize () {
    var $ = jQuery
    initDatatable()
    $('#my-table').on( 'length.dt', function ( e, settings, len ) {
      // console.log( 'New page length: '+len )
      // len = 1
      // $('#my-table').DataTable().page(1).len(len).draw()
      // $('#my-table').DataTable().refresh()
      let table = new DataTable('#my-table')
      // Get the current page
      var currentPage = table.page()

      // Check if the number of rows per page is already set to the desired value
      if (table.page.len() !== len) {
        // Reset the pagination to the first page with the new length
        table.page.len(len).draw()

        // Go back to the original page
        table.page(currentPage).draw(false)
      }
    } )
  }

  function initDatatable() {
    var $ = jQuery
    // const newLength = $('input[name="my-table_length"]').val()

    console.log($('input[name="my-table_length"]').val())

    let table = new DataTable('#my-table', {
      // data: data,
      serverSide: true,
      // processing: true,
      ordering: false,
      // pageLength: 1,
      ajax: {
        url: importedVacanciesData.ajaxUrl,
        method: 'GET',
        data: {
          action: importedVacanciesData.action,
        },
        dataSrc: (response) => {
          console.log(response)
          return response.data
        }
      },
      columns: [
        { data : "id" },
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

  function lengthValueChange() {

  }

  return {
    init: initialize
  }
})()

export default importedVacancyModule