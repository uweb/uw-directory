jQuery(document).ready(function ($) {
  const $grid = $("#directory-container").isotope({
    itemSelector: ".uw-card",
    layoutMode: "fitRows",
    transitionDuration: "0.4s",
  });

  let currentDeptFilter = "*";

  function updateNoResults(gridMatches, tableMatches) {
    $("#no-results-message").toggle(gridMatches === 0 && tableMatches === 0);
  }
  function updateResultsCount() {
    const gridCount = $grid.data("isotope").filteredItems.length;
    const tableCount = $("#directory-table-wrapper tbody tr:visible").length;
    const totalCount = Math.max(gridCount, tableCount);

    $("#results-count").text(
      `${totalCount} result${totalCount === 1 ? "" : "s"} found!`
    );
  }

  function restyleTableStripes() {
    $("#directory-table-wrapper tbody tr:visible")
      .removeClass("odd even")
      .each(function (i) {
        $(this).addClass(i % 2 ? "even" : "odd");
      });
  }

  function hideDropdownOption(filterValue) {
    const $lis = $(".custom-dropdown .dropdown-menu li");
    $lis
      .show()
      .filter(function () {
        return $(this).find(".dropdown-item").data("filter") === filterValue;
      })
      .hide();
  }

  function applyFilters() {
    const searchText = $("#s").val().toLowerCase();

    /* ----  TABLE ROWS  ----------------------------------------- */
    $("#directory-table-wrapper tbody tr").each(function () {
      const rowDept = $(this).data("department-slug");
      const deptMatch =
        currentDeptFilter === "*" || rowDept === currentDeptFilter.substring(1);

      const name = $(this).data("name")?.toLowerCase() || "";
      const email = $(this).data("email")?.toLowerCase() || "";
      const dept = $(this).data("department")?.toLowerCase() || "";

      const searchMatch =
        name.includes(searchText) ||
        email.includes(searchText) ||
        dept.includes(searchText);
      $(this).toggle(deptMatch && searchMatch);
    });

    /* ----  GRID CARDS  ----------------------------------------- */
    $grid.isotope({
      filter: function () {
        const cardDeptMatch =
          currentDeptFilter === "*" ||
          $(this).hasClass(currentDeptFilter.substring(1));

        const name = $(this).data("name")?.toLowerCase() || "";
        const email = $(this).data("email")?.toLowerCase() || "";
        const dept = $(this).data("department")?.toLowerCase() || "";

        const searchMatch =
          name.includes(searchText) ||
          email.includes(searchText) ||
          dept.includes(searchText);
        return cardDeptMatch && searchMatch;
      },
    });

    if ($("#directory-container").is(":visible")) {
      $grid.isotope("layout");
    }

    updateNoResults(
      $grid.data("isotope").filteredItems.length,
      $("#directory-table-wrapper tbody tr:visible").length
    );

    restyleTableStripes();
    updateResultsCount();
  }

  $("#s").on("keyup", applyFilters);
  $("#searchsubmit").on("click", (e) => {
    e.preventDefault();
    applyFilters();
  });

  $(".custom-dropdown .dropdown-menu").on(
    "click",
    ".dropdown-item",
    function (e) {
      e.preventDefault();

      currentDeptFilter = $(this).data("filter"); // store slug or “*”
      $("#dropdown-label").text($(this).data("value"));
      hideDropdownOption(currentDeptFilter);
      applyFilters();

      bootstrap.Dropdown.getOrCreateInstance(
        document.getElementById("dropdownMenuButton")
      ).toggle();
    }
  );
  $(document).on("click", ".clear-filters", function () {
    $("#s").val("");
    $('.custom-dropdown .dropdown-item[data-filter="*"]').trigger("click");
  });

  $(document).on("keydown", ".clear-filters", function (e) {
    if (e.key === "Enter" || e.key === " ") {
      e.preventDefault();
      $("#s").val("");
      $('.custom-dropdown .dropdown-item[data-filter="*"]').trigger("click");
    }
  });

  $("#gridViewBtn").on("click", () => {
    switchToTab("tab-one");
    setViewButton("grid");
    applyFilters();
  });
  $("#listViewBtn").on("click", () => {
    switchToTab("tab-two");
    setViewButton("list");
    applyFilters();
  });

  $grid.on("arrangeComplete", (_, filteredItems) => {
    updateNoResults(
      filteredItems.length,
      $("#directory-table-wrapper tbody tr:visible").length
    );
  });
  $(".open-profile-modal").on("click", function () {
    const email = $(this).data("email");
    const linkedin = $(this).data("linkedin");

    // Set both versions of email
    $("#modal-email-1, #modal-email-2")
      .attr("href", `mailto:${email}`)
      .text(email);

    // Set both versions of LinkedIn
    $("#modal-linkedin-1, #modal-linkedin-2").attr("href", linkedin);

    // Reset views
    $(".toggle1").show().attr("aria-hidden", "false");
    $(".toggle2").hide().attr("aria-hidden", "true");
  });

  $(document).on("click", ".open-profile-modal", function () {
    $("#modal-name").text($(this).data("name"));
    const pronouns = $(this).data("pronouns");

    $("#modal-pronouns")
      .text(pronouns ? `(${pronouns})` : "")
      .toggle(!!pronouns);
    $("#modal-title").text($(this).data("title"));
    $("#modal-department").text($(this).data("department"));
    $("#modal-bio").html($(this).data("bio"));
    $(".modal-email").text($(this).data("email"));
    $("#modal-img").attr("src", $(this).data("img"));
    $(".toggle1").hide();
    // show link only if bio overflows 8 lines
    const bioEl = document.getElementById("modal-bio");
    const toggle = document.getElementById("bio-toggle");

    // Reset state on open
    bioEl.classList.remove("expanded");
    toggle.textContent = "See more";
    $(".toggle1").hide(); // Hidden when collapsed
    $(".toggle2").show(); // Visible when collapsed

    // Check if bio is overflowing and show toggle only if needed
    requestAnimationFrame(() => {
      const overflowing = bioEl.scrollHeight > bioEl.clientHeight + 2;
      toggle.hidden = !overflowing;
    });

    // ----- toggle handler -----
    $("#bio-toggle")
      .off("click")
      .on("click", function () {
        const expanded = $("#modal-bio")
          .toggleClass("expanded")
          .hasClass("expanded");
        this.textContent = expanded ? "See less" : "See more";

        $(".toggle1").toggle(expanded);
        $(".toggle2").toggle(!expanded);
      });

    if (window.Calendly?.initInlineWidgets) {
      Calendly.initInlineWidgets();
    }

    const website = $(this).data("website");
    const linkedin = $(this).data("linkedin");

    let linksHTML = "";
    if (website) {
      linksHTML += `<p><a href="${website}" target="_blank" rel="noopener">
                    <i class="fa-solid fa-globe" aria-hidden="true"></i> Website</a></p>`;
    }
    if (linkedin) {
      linksHTML += `<p><a href="https://linkedin.com/in/${linkedin}" target="_blank" rel="noopener">
                    <i class="fa-brands fa-linkedin" aria-hidden="true"></i> LinkedIn</a></p>`;
    }

    $(".toggle1 #modal-links, .toggle2 #modal-links").html(linksHTML);

    $("#profile-modal").fadeIn(() => {
      $("#profile-modal").find(".folklore-modal-close").focus();
    });
  });

  $(document).on("click", ".folklore-modal-close", () =>
    $("#profile-modal").fadeOut()
  );
  $(document).on("keydown", ".open-profile-modal", function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
      $(this).click();
    }
  });

  $(document).on("keydown", (e) => {
    if (e.key === "Escape") $("#profile-modal").fadeOut();
  });

  switchToTab("tab-one");
  setViewButton("grid");
  hideDropdownOption("*");
  applyFilters();
});

$(document).on("keydown", "#profile-modal", function (e) {
  if (e.key === "Tab") {
    e.preventDefault();
    $(this).find(".folklore-modal-close").focus();
  }
});

function switchToTab(tabId) {
  $(".tab-button").removeClass("active");
  $(".tab-content").hide();
  $("#" + tabId).show();

  if (tabId === "tab-one") {
    $("#directory-container").css("display", "flex");
    $("#directory-table-wrapper").hide();
  } else {
    $("#directory-container").hide();
    $("#directory-table-wrapper").show();
  }
}

function setViewButton(view) {
  const $container = $("#directory-container");
  const $tableBody = $("#directory-table-wrapper tbody");

  if (view === "grid") {
    $("#gridViewBtn").addClass("active");
    $("#listViewBtn").removeClass("active");
    $("#currentViewLabel").text("GRID VIEW");
    $("#currentViewIcon").attr("class", "bi bi-grid-3x3-gap-fill");

    $container.attr("aria-atomic", "true");
    $tableBody.attr("aria-atomic", "false");
  } else {
    $("#listViewBtn").addClass("active");
    $("#gridViewBtn").removeClass("active");
    $("#currentViewLabel").text("LIST VIEW");
    $("#currentViewIcon").attr("class", "bi bi-list");

    $container.attr("aria-atomic", "false");
    $tableBody.attr("aria-atomic", "true");
  }
}
