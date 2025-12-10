jQuery(document).ready(function ($) {
 /* ----------  Initialization ---------- */
   const $grid = $("#directory-container").isotope({
    itemSelector: ".uw-card",
    layoutMode: "fitRows",
    transitionDuration: "0.4s",
  });

  let currentDeptFilter = "*"; 
  
 /* ----------  Utility functions ---------- */
// setEqualCardHeights
function setEqualCardHeights () {
 if (window.innerWidth < 730) {
    // Reset any inline height styles
    $('.uw-card, .uw-card-img').css('height', '');
    return;
  }

  let tallest = 0;

  $('.uw-card').each(function () {
    const h = $(this).outerHeight();
    if (h > tallest) tallest = h;
  });

  $('.uw-card').css('height', tallest + 'px');
  $('.uw-card-img').css('height', '100%'); 
}
// updateNoResults
function updateNoResults(gridMatches, tableMatches) {
    const noResults = gridMatches === 0 && tableMatches === 0;

    if ($("#tab-two").is(":visible")) {
      $(".table-instruction, .table-headers").toggle(!noResults);
    }

    $("#no-results-message").toggle(noResults);
  }
// updateResultsCount

  function updateResultsCount() {
    const gridCount = $grid.data("isotope").filteredItems.length;
    const tableCount = $("#directory-table-wrapper tbody tr:visible").length;
    const totalCount = Math.max(gridCount, tableCount);
    updateNoResults(gridCount, tableCount);

    $("#results-count").text(
      `${totalCount} result${totalCount === 1 ? "" : "s"} found`
    );
  }
// restyleTableStripes
 function restyleTableStripes() {
    $("#directory-table-wrapper tbody tr:visible")
      .removeClass("odd even")
      .each(function (i) {
        $(this).addClass(i % 2 ? "even" : "odd");
      });
  }

// hideDropdownOption
  function hideDropdownOption(filterValue) {
    const $lis = $(".custom-dropdown .dropdown-menu li");
    $lis
      .show()
      .filter(function () {
        return $(this).find(".dropdown-item").data("filter") === filterValue;
      })
      .hide();
  }
// updateSearchButtonState
 function updateSearchButtonState() {
    const searchText = $("#searchbar").val().trim();
    $("#searchsubmit").prop("disabled", searchText === "");
  }
// updateClearFiltersButton
function updateClearFiltersButton() {
    const searchText = $("#searchbar").val().trim();
    const clearBtn = $(".clear-filters");

    if (searchText || currentDeptFilter !== "*") {
      clearBtn.show(); // Show button when filters are active
    } else {
      clearBtn.hide(); // Hide button when no filters are active
    }
  }
// trapFocus                (modal a11y)
  function trapFocus(modalEl) {
    const focusableElements = modalEl.querySelectorAll(
      'a[href]:not([tabindex="-1"]), button:not([disabled]):not([tabindex="-1"]), [tabindex]:not([tabindex="-1"])'
    );
    const first = focusableElements[0];
    const last = focusableElements[focusableElements.length - 1];

    modalEl.addEventListener("keydown", function (e) {
      if (e.key === "Tab") {
        if (focusableElements.length === 0) return;

        if (e.shiftKey) {
          if (document.activeElement === first) {
            e.preventDefault();
            last.focus();
          }
        } else {
          if (document.activeElement === last) {
            e.preventDefault();
            first.focus();
          }
        }
      }
    });
  }


/* ----------  Core filtering logic ---------- */
function applyFilters() {
    const searchText = $("#searchbar").val().toLowerCase();
    updateSearchButtonState();
    /* ----  Table rows  --------- */
    $("#directory-table-wrapper tbody tr").each(function () {
      const rowDept = $(this).data("department-slug");
      const deptMatch =
        currentDeptFilter === "*" || rowDept === currentDeptFilter.substring(1);

      const name = $(this).data("name")?.toLowerCase() || "";
      const email = $(this).data("email")?.toLowerCase() || "";
      const dept = $(this).data("department")?.toLowerCase() || "";
      const role = $(this).data("title")?.toLowerCase() || "";

      const searchMatch =
        name.includes(searchText) ||
        email.includes(searchText) ||
        role.includes(searchText) ||
        dept.includes(searchText);
      $(this).toggle(deptMatch && searchMatch);
    });

    /* ----  Grid cards ---------- */
    $grid.isotope({
      filter: function () {
        const cardDeptMatch =
          currentDeptFilter === "*" ||
          $(this).hasClass(currentDeptFilter.substring(1));

        const name = $(this).data("name")?.toLowerCase() || "";
        const email = $(this).data("email")?.toLowerCase() || "";
        const dept = $(this).data("department")?.toLowerCase() || "";
        const role = $(this).data("title")?.toLowerCase() || "";

        const searchMatch =
          name.includes(searchText) ||
          email.includes(searchText) ||
          role.includes(searchText) ||
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
    updateClearFiltersButton();
 

  }
 /* ----------  Search / category / clear events ---------- */
  $("#searchbar").on("input", function () {
    updateSearchButtonState();
    applyFilters();
  });


    $(".custom-dropdown .custom-btn").on(
    "click",
    function (e) {
      e.preventDefault();
      const dropdownMenu = $('.custom-dropdown .dropdown-menu')
      dropdownMenu.show();
      $('.dropdown-item').on("click",  function(j){
          j.preventDefault();        
          dropdownMenu.hide();  
          currentDeptFilter = $(this).attr("data-filter");
          $("#dropdown-label").text($(this).data("value"));
          hideDropdownOption(currentDeptFilter);
          applyFilters();
        }
      )      
    }
  );

  $(document).on("click", ".clear-filters", function () {
    $("#searchbar").val("");
    $('.custom-dropdown .dropdown-item[data-filter="*"]').trigger("click");
    updateSearchButtonState();
    $("#results-count").text( 
      ''
    )
  });

$(".searchbox").on("submit", function (e) {
  e.preventDefault();

  const btn = $("#searchsubmit");
  btn.addClass("clicked");      
  applyFilters();            

  setTimeout(() => {
    btn.removeClass("clicked"); 
    btn.blur();                
  }, 150);
});

/* ----------  Grid ↔ List view-toggle events ---------- */
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

/* ----------  Isotope + resize bindings ---------- */
  $grid.on("arrangeComplete", (_, filteredItems) => {
    updateNoResults(
      filteredItems.length,
      $("#directory-table-wrapper tbody tr:visible").length
    );
  });

  $grid.on('arrangeComplete', setEqualCardHeights);
   $(window).on('resize', () => {
  $grid.isotope('layout');  
});
  $(window).on('load', setEqualCardHeights);

/* ----------  Modal logic & events ---------- */
  $(document).on("click", ".open-profile-modal", function () {
    const $this = $(this);
    const email = $this.data("email");
    const linkedin = $this.data("linkedin");
    const website = $this.data("website");
    const name = $this.data("name");
    const pronouns = $this.data("pronouns");
    const title = $this.data("title");
    const department = $this.data("department");
    const bio = $this.data("bio");
    const img = $this.data("img");
    // Fill basic info
    $("#modal-name").text(name);
    $("#modal-pronouns")
      .text(pronouns ? `(${pronouns})` : "")
      .toggle(!!pronouns);
    $("#modal-title").text(title);
    $("#modal-department").text(department);
    $("#modal-bio").html(bio);
    $("#modal-img").attr("src", img);
    $("#modal-img").attr("alt", `Profile image of ${name}`);

    $(".modal-email").attr("href", `mailto:${email}`);
    $(".modal-email .email-text").text(email || "Email");
    $(".modal-contact .contact-item").remove();

    if (email) {
      $(".modal-contact").append(`
        <div class="contact-item">
            <i class="fa-solid fa-envelope"></i>
            <a class="modal-email" href="mailto:${email}" target="_blank">
                <span class="email-text">${email}</span>
            </a>
        </div>
    `);
    }

    if (linkedin) {
      $(".modal-contact").append(`
        <div class="contact-item">
            <i class="fa-brands fa-linkedin"></i>
            <a class="modal-linkedin" href="https://linkedin.com/in/${linkedin}" target="_blank">
                <span>LinkedIn</span>
            </a>
        </div>
    `);
    }

    if (website) {
      $(".modal-contact").append(`
        <div class="contact-item">
            <i class="fa-solid fa-globe"></i>
            <a class="modal-website" href="${website}" target="_blank">
                <span>Website</span>
            </a>
        </div>
    `);
    }


    // Handle toggle visibility and bio toggle

    const bioEl = document.getElementById("modal-bio");
    const toggle = document.getElementById("bio-toggle");

    // Reset bio toggle
    bioEl.classList.remove("expanded");
    toggle.textContent = "See more";
    $(".horizontal-modal-footer").hide();
    $(".vertical-modal-footer").show();

    requestAnimationFrame(() => {
      const overflowing = bioEl.scrollHeight > bioEl.clientHeight + 2;
      toggle.hidden = !overflowing;
      if (overflowing) {
        bioEl.classList.add('clamped');
      } else {
        bioEl.classList.remove('clamped');
      }
    });

    $("#bio-toggle")
      .off("click")
      .on("click", function () {
        const expanded = $("#modal-bio")
          .toggleClass("expanded")
          .hasClass("expanded");
        this.textContent = expanded ? "See less" : "See more";

        $(".horizontal-modal-footer").toggle(expanded);
        $(".vertical-modal-footer").toggle(!expanded);
      });

    if (window.Calendly?.initInlineWidgets) {
      Calendly.initInlineWidgets();
    }
    $(".modal-contact").each(function () {
      const hasItems = $(this).find(".contact-item").length > 0;
      $(this).find("h3").toggle(hasItems);   // show connect text only when linkiend/email/website is present
    });
    $(document).on('keydown', '#bio-toggle', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        $(this).click();
      }
    });


    $("#profile-modal").fadeIn(() => {
      const modal = document.getElementById("profile-modal");
      modal.focus();
      trapFocus(modal);

      const firstFocusable = modal.querySelector(
        'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])'
      );
      if (firstFocusable) firstFocusable.focus();
    });

  });

  function closeModal() {
  $("#profile-modal").fadeOut();
}

$(document).on("click", "#profile-modal", function (e) {
  if (e.target === this) {
    closeModal();
  }
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
 /* ----------  Default state on first load ---------- */
  
  updateSearchButtonState()
  switchToTab("tab-one");
  setViewButton("grid");
  hideDropdownOption("*");
  applyFilters();
   $("#results-count").text('');


});      // end ready()

/* ---- switchToTab + setViewButton helpers (outside ready if they’re reused) ---- */

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