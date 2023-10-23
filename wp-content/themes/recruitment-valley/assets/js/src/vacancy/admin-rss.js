const rssModule = (function () {
  function initialize() {
    $(document).on(
      "change",
      '[data-name="rv_rss_select_company"] .acf-input input',
      ajaxVacancyOptionValue
    );
  }

  function ajaxVacancyOptionValue(e) {
    $.ajax({
      method: "POST",
      url: vacanciesData.ajaxUrl,
      data: {
        action: vacanciesData.rss.action,
        nonce: vacanciesData.rss.nonce,
        company_id: e.target.val(),
      },
    })
      .done((res) => {
        console.log(res);
      })
      .fail(() => {
        console.log("fail");
      });
  }

  return {
    init: initialize,
  };
})();

export default rssModule;
