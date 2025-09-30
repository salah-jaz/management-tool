'use strict';
$('#status_filter').on('change', function (e) {
    var status = $(this).val();
    location.href = setUrlParameter(location.href, 'status', status);
});
$('#sort').on('change', function (e) {
    var sort = $(this).val();
    location.href = setUrlParameter(location.href, 'sort', sort);
});

$('#filter').click(function () {
    // Get the selected values from status select and other filters
    var statuses = $('#selected_statuses').val(); // Array of selected statuses
    var sort = $('#sort').val();
    // Get selected tags using Select2
    var selectedTags = $('#selected_tags').val(); // Array of selected tags

    // Form the URL with the selected filters
    var url = baseUrl + "/projects/kanban";
    var params = [];

    if (statuses && statuses.length > 0) {
        params.push("statuses[]=" + statuses.join("&statuses[]="));
    }

    if (sort) {
        params.push("sort=" + sort);
    }

    if (selectedTags && selectedTags.length > 0) {
        params.push("tags[]=" + selectedTags.join("&tags[]="));
    }

    if (params.length > 0) {
        url += "?" + params.join("&");
    }

    // Redirect to the URL
    window.location.href = url;
});


function setUrlParameter(url, paramName, paramValue) {
    paramName = paramName.replace(/\s+/g, '-');
    if (paramValue == null || paramValue == '') {
        return url.replace(new RegExp('[?&]' + paramName + '=[^&#]*(#.*)?$'), '$1')
            .replace(new RegExp('([?&])' + paramName + '=[^&]*&'), '$1');
    }
    var pattern = new RegExp('\\b(' + paramName + '=).*?(&|#|$)');
    if (url.search(pattern) >= 0) {
        return url.replace(pattern, '$1' + paramValue + '$2');
    }
    url = url.replace(/[?#]$/, '');
    return url + (url.indexOf('?') > 0 ? '&' : '?') + paramName + '=' + paramValue;
}

function userFormatter(value, row, index) {
    return '<div class="d-flex">' +
        row.profile +
        '</div>';

}

function clientFormatter(value, row, index) {
    return '<div class="d-flex">' +
        row.profile +
        '</div>';

}
document.addEventListener('DOMContentLoaded', function () {
    const columns = Array.from(document.querySelectorAll('.kanban-column-body'));

    // Get the create project button
    const createProjectBtn = document.querySelector('.create-project-btn');

    // Exclude the create project button from drag-and-drop
    const drake = dragula(columns, {
        direction: 'vertical',
        moves: function (el, container, handle) {
            return !el.classList.contains('create-project-btn'); // Exclude the button
        },
        accepts: function (el, target) {
            return !el.classList.contains('create-project-btn'); // Exclude the button
        },
        invalid: function (el, handle) {
            return el.classList.contains('create-project-btn'); // Exclude the button
        }
    });
    // Event when dragging starts
    drake.on('drag', function (el) {
        el.classList.add('dragging'); // Add visual style to the dragged element
    });

    // Event when dragging ends
    drake.on('dragend', function (el) {
        el.classList.remove('dragging'); // Remove visual style from the dragged element
        el.classList.add('dropped'); // Add dropped style
        document.querySelectorAll('.drop-target').forEach(target => {
            target.classList.remove('drop-target'); // Remove highlight from all columns
        });
    });

    // Event when dragging over a container
    drake.on('over', function (el, container) {
        container.classList.add('drop-target'); // Add highlight to the container
    });

    // Event when dragging out of a container
    drake.on('out', function (el, container) {
        container.classList.remove('drop-target'); // Remove highlight from the container
    });
    drake.on('drop', function (el, target, source, sibling) {
        // Get the new status based on the target column's data attribute
        const newStatus = target.closest('.kanban-column').dataset.statusId;

        // Extract card ID from the element
        const cardId = el.dataset.cardId;

        // Update project status in the backend using jQuery AJAX
        $.ajax({
            url: baseUrl + '/update-project-status',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            data: JSON.stringify({
                id: cardId,
                statusId: newStatus
            }),
            success: function (response) {
                if (response.error === false) {
                    toastr.success(response.message);

                    // Optionally, update the frontend to reflect any changes
                    // For example, updating the count of items in the column headers
                    updateColumnCounts();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
            }
        });
    });

    function updateColumnCounts() {
        // Function to update the counts of items in each column header
        document.querySelectorAll('.kanban-column').forEach(column => {
            const statusId = column.dataset.statusId;
            const count = column.querySelectorAll('.kanban-card').length;
            column.querySelector('.column-count').textContent = `${count}/${totalProjectsCount}`;
        });
    }

    // Optionally, calculate the total number of projects if needed
    const totalProjectsCount = document.querySelectorAll('.kanban-card').length;
});

function queryParamsUsersClients(p) {
    return {
        type: $('#type').val(),
        typeId: $('#typeId').val(),
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

