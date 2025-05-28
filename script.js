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

    $grid.on('arrangeComplete', function(event, filteredItems) {
        const gridMatches  = filteredItems.length;
        const tableMatches = $('#directory-table-wrapper tbody tr:visible').length;
        updateNoResults(gridMatches, tableMatches);
    });

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
    $('#searchsubmit').on('click', function(e) {
        e.preventDefault();
        applySearchFilter();
    });

    // helper to hide the current dropdown option
    function hideDropdownOption(filterValue) {
        const $lis = $('.custom-dropdown .dropdown-menu li');
        $lis.show();
        $lis.filter(function() {
            return $(this).find('.dropdown-item').data('filter') === filterValue;
        }).hide();
    }

    // dropdown: filter 
    $('.custom-dropdown .dropdown-menu').on('click', '.dropdown-item', function(e) {
        e.preventDefault();
        const filterValue = $(this).data('filter');
        const labelText   = $(this).data('value');

        // update label
        $('#dropdown-label').text(labelText);

        // table filter
        $('#directory-table-wrapper tbody tr').each(function () {
            const rowDept = $(this).data('department-slug');
            const show   = filterValue === '*' || rowDept === filterValue.substring(1);
            $(this).toggle(show);
        });

        // isotope filter
        $grid.isotope({ filter: filterValue });

        hideDropdownOption(filterValue);

        $('#dropdownMenuButton').dropdown('toggle');
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
        $('#profile-modal').fadeIn(function(){
            $(this).find('.uw-modal-close').focus();
        });
    });

    $(document).on('click', '.uw-modal-close', function () {
        $('#profile-modal').fadeOut();
    });

    $(document).on('keydown', '.open-profile-modal', function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            $(this).click();
        }
    });
    switchToTab('tab-one');
    setViewButton('grid');
    hideDropdownOption('*');

    $('#gridViewBtn').on('click', function () {
        switchToTab('tab-one');
        setViewButton('grid');
    });

    $('#listViewBtn').on('click', function () {
        switchToTab('tab-two');
        setViewButton('list');
    });
});
// keep focus in modal
$(document).on('keydown', '#profile-modal', function(e) {
    if (e.key === 'Tab' || e.keyCode === 9) {
        e.preventDefault();
        $(this).find('.uw-modal-close').focus();
    }
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
    const $container = $('#directory-container');
    const $tableBody = $('#directory-table-wrapper tbody');

    if (view === 'grid') {
        $('#gridViewBtn').addClass('active');
        $('#listViewBtn').removeClass('active');
        $('#currentViewLabel').text('GRID VIEW');
        $('#currentViewIcon').attr('class', 'bi bi-grid-3x3-gap-fill');

        $container.attr('aria-atomic', 'true');
        $tableBody.attr('aria-atomic', 'false');

        $container.isotope('layout');
    } else {
        $('#listViewBtn').addClass('active');
        $('#gridViewBtn').removeClass('active');
        $('#currentViewLabel').text('LIST VIEW');
        $('#currentViewIcon').attr('class', 'bi bi-list');

        $container.attr('aria-atomic', 'false');
        $tableBody.attr('aria-atomic', 'true');
    }
}
