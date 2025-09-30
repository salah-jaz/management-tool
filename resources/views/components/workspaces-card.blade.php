<!-- workspaces -->
@if (is_countable($workspaces) && count($workspaces) > 0)
@php
$visibleColumns = getUserPreferences('workspaces');
$auth_user = getAuthenticatedUser();
@endphp
<div class="card">
    <div class="card-body">
        {{$slot}}
        <div class="row">
            @if(isAdminOrHasAllDataAccess())
            <div class="col-md-6 mb-3">
                <select class="form-select users_select" id="workspace_user_filter" aria-label="Default select example" data-placeholder="<?= get_label('select_users', 'Select Users') ?>" data-consider-workspace="false" multiple>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <select class="form-select clients_select" id="workspace_client_filter" aria-label="Default select example" data-placeholder="<?= get_label('select_clients', 'Select Clients') ?>" data-consider-workspace="false" multiple>
                </select>
            </div>
            @endif
        </div>
        <div class="table-responsive text-nowrap">
            <input type="hidden" id="data_type" value="workspaces">
            <input type="hidden" id="data_reload" value="1">
            <input type="hidden" id="save_column_visibility">
            <input type="hidden" id="multi_select">
            <table id="table" data-toggle="table" data-loading-template="loadingTemplate" data-url="{{ url('/workspaces/list') }}" data-icons-prefix="bx" data-icons="icons" data-show-refresh="true" data-total-field="total" data-trim-on-search="false" data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-side-pagination="server" data-show-columns="true" data-pagination="true" data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true" data-query-params="queryParams">
                <thead>
                    <tr>
                        <th data-checkbox="true"></th>
                        <th data-field="id" data-visible="{{ (in_array('id', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}" data-sortable="true"><?= get_label('id', 'ID') ?></th>
                        <th data-field="title" data-visible="{{ (in_array('title', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}" data-sortable="true"><?= get_label('title', 'Title') ?></th>
                        <th data-field="users" data-visible="{{ (in_array('users', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}">{{ get_label('users', 'Users') }}</th>
                        <th data-field="clients" data-visible="{{ (in_array('clients', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}">{{ get_label('clients', 'Clients') }}</th>
                        <th data-field="is_default" data-visible="{{ (in_array('is_default', $visibleColumns) || empty($is_default)) ? 'true' : 'false' }}">{{ get_label('default', 'Default') }} <i class='bx bx-info-circle text-primary' title="{{get_label('default_workspace_info', 'On login, you will be automatically switched to this workspace by default.')}}"></i></th>
                        <th data-field="created_at" data-visible="{{ (in_array('created_at', $visibleColumns)) ? 'true' : 'false' }}" data-sortable="true"><?= get_label('created_at', 'Created at') ?></th>
                        <th data-field="updated_at" data-visible="{{ (in_array('updated_at', $visibleColumns)) ? 'true' : 'false' }}" data-sortable="true"><?= get_label('updated_at', 'Updated at') ?></th>
                        <th data-field="actions" data-visible="{{ (in_array('actions', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}">{{ get_label('actions', 'Actions') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@else
<?php
$type = 'Workspaces'; ?>
<x-empty-state-card :type="$type" />
@endif