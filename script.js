jQuery(document).ready(function ($) {
    const $grid = $('#directory-container').isotope({
        itemSelector: '.uw-card',
        layoutMode: 'fitRows',
        fitRows: { gutter: 40 }
    });

    function applySearchFilter() {
        const searchText = $('#s').val().toLowerCase();

        $grid.isotope({
            filter: function () {
                const name = $(this).data('name')?.toLowerCase() || '';
                const email = $(this).data('email')?.toLowerCase() || '';
                const department = $(this).data('department')?.toLowerCase() || '';
                return name.includes(searchText) || email.includes(searchText) || department.includes(searchText);
            }
        });

        $('#directory-table-wrapper tbody tr').each(function () {
            const name = $(this).data('name')?.toLowerCase() || '';
            const email = $(this).data('email')?.toLowerCase() || '';
            const department = $(this).data('department')?.toLowerCase() || '';
            const match = name.includes(searchText) || email.includes(searchText) || department.includes(searchText);
            $(this).toggle(match);
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
        const label = $(this).data('value');

        $('#dropdown-label').text(label);

        $grid.isotope({ filter: filterValue });

        $('#directory-table-wrapper tbody tr').each(function () {
            const rowDept = $(this).data('department-slug');
            const show = filterValue === '*' || rowDept === filterValue.substring(1);
            $(this).toggle(show);
        });
    });

    $(document).on('click', '.open-profile-modal', function () {
        $('#modal-name').text($(this).data('name'));
        $('#modal-title').text($(this).data('title'));
        $('#modal-department').text($(this).data('department'));
        $('#modal-bio').text($(this).data('bio'));
        $('#modal-email').text($(this).data('email'));
        $('#modal-connect-header').text(`Connect with ${$(this).data('name')}`);
        $('#modal-links').text('LinkedIn/Calendly/etc');
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
    } else {
        $('#listViewBtn').addClass('active');
        $('#gridViewBtn').removeClass('active');
        $('#currentViewLabel').text('LIST VIEW');
        $('#currentViewIcon').attr('class', 'bi bi-list');
    }
}
