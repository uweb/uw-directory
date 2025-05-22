jQuery(document).ready(function ($) {
    const $grid = $('#directory-container').isotope({
        itemSelector: '.uw-card',
        layoutMode: 'fitRows',
        transitionDuration: '0.4s'
    });

    /**
     * Show/hide “No results found.”
     * @param {number} gridMatches
     * @param {number} tableMatches
     */
    function updateNoResults(gridMatches, tableMatches) {
        if (gridMatches === 0 && tableMatches === 0) {
            $('#no-results-message').show();
        } else {
            $('#no-results-message').hide();
        }
    }

    // Listen for when isotope has finished filtering/layout
    $grid.on('arrangeComplete', function(event, filteredItems) {
        const gridMatches  = filteredItems.length;
        const tableMatches = $('#directory-table-wrapper tbody tr:visible').length;
        updateNoResults(gridMatches, tableMatches);
    });

    /**
     * Apply the search filter to both grid & table.
     */
    function applySearchFilter() {
        const searchText = $('#s').val().toLowerCase();

        //filter  table
        $('#directory-table-wrapper tbody tr').each(function () {
            const name  = $(this).data('name')?.toLowerCase()       || '';
            const email = $(this).data('email')?.toLowerCase()      || '';
            const dept  = $(this).data('department')?.toLowerCase() || '';
            const match = name.includes(searchText)
                       || email.includes(searchText)
                       || dept.includes(searchText);
            $(this).toggle(match);
        });

        // trigger isotope filter
        $grid.isotope({
            filter: function () {
                const name  = $(this).data('name')?.toLowerCase()       || '';
                const email = $(this).data('email')?.toLowerCase()      || '';
                const dept  = $(this).data('department')?.toLowerCase() || '';
                return name.includes(searchText)
                    || email.includes(searchText)
                    || dept.includes(searchText);
            }
        });
    }

    $('#s').on('keyup', applySearchFilter);
    $('#searchsubmit').on('click', function (e) {
        e.preventDefault();
        applySearchFilter();
    });

    $('.dropdown-menu .dropdown-item').on('click', function (e) {
        e.preventDefault();
        const filterValue = $(this).data('filter');
        $('#dropdown-label').text($(this).data('value'));

        $('#directory-table-wrapper tbody tr').each(function () {
            const rowDept = $(this).data('department-slug');
            const show   = filterValue === '*' || rowDept === filterValue.substring(1);
            $(this).toggle(show);
        });

        $grid.isotope({ filter: filterValue });
    });

    // modal handlers 
    $(document).on('click', '.open-profile-modal', function () {
        $('#modal-name').text(    $(this).data('name'));
        $('#modal-title').text(   $(this).data('title'));
        $('#modal-department').text($(this).data('department'));
        $('#modal-bio').text(     $(this).data('bio'));
        $('#modal-email').text(   $(this).data('email'));
        $('#modal-connect-header').text(`Connect with ${$(this).data('name')}`);
        $('#modal-links').text(   'LinkedIn/Calendly/etc');
        $('#modal-img').attr('src', $(this).data('img'));
        $('#profile-modal').fadeIn();
    });
    $(document).on('click', '.uw-modal-close', function () {
        $('#profile-modal').fadeOut();
    });

    switchToTab('tab-one');
    setViewButton('grid');

    $('#gridViewBtn').on('click', function () {
        switchToTab('tab-one');
        setViewButton('grid');
    });

    $('#listViewBtn').on('click', function () {
        switchToTab('tab-two');
        setViewButton('list');
    });
});

function switchToTab(tabId) {
    $('.tab-button').removeClass('active');
    $('.tab-content').hide();
    $('#' + tabId).show();

    if (tabId === 'tab-one') {
        $('#directory-container').css('display', 'flex');
        $('#directory-table-wrapper').hide();
    } else {
        $('#directory-container').hide();
        $('#directory-table-wrapper').show();
    }
}

function setViewButton(view) {
    if (view === 'grid') {
        $('#gridViewBtn').addClass('active');
        $('#listViewBtn').removeClass('active');
        $('#currentViewLabel').text('GRID VIEW');
        $('#currentViewIcon').attr('class', 'bi bi-grid-3x3-gap-fill');

        $('#directory-container').isotope('layout');
    } else {
        $('#listViewBtn').addClass('active');
        $('#gridViewBtn').removeClass('active');
        $('#currentViewLabel').text('LIST VIEW');
        $('#currentViewIcon').attr('class', 'bi bi-list');
    }
}
