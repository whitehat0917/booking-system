<link rel="stylesheet" type="text/css" href="<?php echo base_url('/assets/ext/jquery-fullcalendar/jquery.fullcalendar.css'); ?>">

<script src="<?php echo base_url('assets/ext/jquery-fullcalendar/jquery.fullcalendar.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/ext/jquery-sticky-table-headers/jquery.stickytableheaders.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/ext/jquery-ui/jquery-ui-timepicker-addon.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/backend_calendar.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/backend_calendar_default_view.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/backend_calendar_table_view.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/backend_calendar_google_sync.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/backend_calendar_appointments_modal.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/backend_calendar_unavailabilities_modal.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/backend_calendar_api.js'); ?>"></script>
<script>
    var GlobalVariables = {
        'csrfToken'             : <?php echo json_encode($this->security->get_csrf_hash()); ?>,
        'availableProviders'    : <?php echo json_encode($available_providers); ?>,
        'availableServices'     : <?php echo json_encode($available_services); ?>,
        'baseUrl'               : <?php echo json_encode($base_url); ?>,
        'bookAdvanceTimeout'    : <?php echo $book_advance_timeout; ?>,
        'dateFormat'            : <?php echo json_encode($date_format); ?>,
        'editAppointment'       : <?php echo json_encode($edit_appointment); ?>,
        'customers'             : <?php echo json_encode($customers); ?>,
        'secretaryProviders'    : <?php echo json_encode($secretary_providers); ?>,
        'calendarView'          : <?php echo json_encode($calendar_view); ?>,
        'user'                  : {
            'id'        : <?php echo $user_id; ?>,
            'email'     : <?php echo json_encode($user_email); ?>,
            'role_slug' : <?php echo json_encode($role_slug); ?>,
            'privileges': <?php echo json_encode($privileges); ?>
        }
    };

    $(document).ready(function() {
        BackendCalendar.initialize(GlobalVariables.calendarView);
    });
</script>

<div id="calendar-page" class="container-fluid">
    <div id="calendar-toolbar">
        <div id="calendar-filter" class="form-inline col-xs-12 col-md-5">
            <div class="form-group">
                <label for="select-filter-item">
                    <?php echo lang('display_calendar'); ?>
                </label>
                <select id="select-filter-item" class="form-control"
                        title="<?php echo lang('select_filter_item_hint'); ?>">
                </select>
            </div>
        </div>

        <div id="calendar-actions" class="col-xs-12 col-md-7">
            <?php if (($role_slug == DB_SLUG_ADMIN || $role_slug == DB_SLUG_PROVIDER)
                    && Config::GOOGLE_SYNC_FEATURE == TRUE): ?>
                <button id="google-sync" class="btn btn-primary"
                        title="<?php echo lang('trigger_google_sync_hint'); ?>">
                    <span class="glyphicon glyphicon-refresh"></span>
                    <span><?php echo lang('synchronize'); ?></span>
                </button>

                <button id="enable-sync" class="btn btn-default" data-toggle="button"
                        title="<?php echo lang('enable_appointment_sync_hint'); ?>">
                    <span class="glyphicon glyphicon-calendar"></span>
                    <span><?php echo lang('enable_sync'); ?></span>
                </button>
            <?php endif ?>

            <?php if ($privileges[PRIV_APPOINTMENTS]['add'] == TRUE): ?>
                <button id="insert-appointment" class="btn btn-default"
                        title="<?php echo lang('new_appointment_hint'); ?>">
                    <span class="glyphicon glyphicon-plus"></span>
                    <?php echo lang('appointment'); ?>
                </button>

                <button id="insert-unavailable" class="btn btn-default"
                        title="<?php echo lang('unavailable_periods_hint'); ?>">
                    <span class="glyphicon glyphicon-plus"></span>
                    <?php echo lang('unavailable'); ?>
                </button>
            <?php endif ?>

            <button id="reload-appointments" class="btn btn-default"
                    title="<?php echo lang('reload_appointments_hint'); ?>">
                <span class="glyphicon glyphicon-repeat"></span>
                <?php echo lang('reload'); ?>
            </button>

            <button id="toggle-fullscreen" class="btn btn-default">
                <span class="glyphicon glyphicon-fullscreen"></span>
            </button>
        </div>
    </div>

    <div id="calendar"></div> <?php // Main calendar container ?>
</div>

<!-- MANAGE APPOINTMENT MODAL -->

<div id="manage-appointment" class="modal fade full-screen" data-keyboard="true" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="wrapper">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">&times;</button>
                    <h3 class="modal-title"><?php echo lang('edit_appointment_title'); ?></h3>
                </div>

                <div class="modal-body">
                    <div class="modal-message alert hidden"></div>

                    <form class="form-horizontal">
                        <fieldset class="container">
                            <legend><?php echo lang('appointment_details_title'); ?></legend>

                            <input id="appointment-id" type="hidden" />

                            <div class="form-group">
                                <label for="select-service" class="col-sm-3 control-label"><?php echo lang('service'); ?> *</label>
                                <div class="col-sm-7">
                                    <select id="select-service" class="required form-control">
                                        <?php
                                            // Group services by category, only if there is at least one service
                                            // with a parent category.
                                            $has_category = FALSE;
                                            foreach($available_services as $service) {
                                                if ($service['category_id'] != NULL) {
                                                    $has_category = TRUE;
                                                    break;
                                                }
                                            }

                                            if ($has_category) {
                                                $grouped_services = array();

                                                foreach($available_services as $service) {
                                                    if ($service['category_id'] != NULL) {
                                                        if (!isset($grouped_services[$service['category_name']])) {
                                                            $grouped_services[$service['category_name']] = array();
                                                        }

                                                        $grouped_services[$service['category_name']][] = $service;
                                                    }
                                                }

                                                // We need the uncategorized services at the end of the list so
                                                // we will use another iteration only for the uncategorized services.
                                                $grouped_services['uncategorized'] = array();
                                                foreach($available_services as $service) {
                                                    if ($service['category_id'] == NULL) {
                                                        $grouped_services['uncategorized'][] = $service;
                                                    }
                                                }

                                                foreach($grouped_services as $key => $group) {
                                                    $group_label = ($key != 'uncategorized')
                                                            ? $group[0]['category_name'] : 'Uncategorized';

                                                    if (count($group) > 0) {
                                                        echo '<optgroup label="' . $group_label . '">';
                                                        foreach($group as $service) {
                                                            echo '<option value="' . $service['id'] . '">'
                                                                . $service['name'] . '</option>';
                                                        }
                                                        echo '</optgroup>';
                                                    }
                                                }
                                            }  else {
                                                foreach($available_services as $service) {
                                                    echo '<option value="' . $service['id'] . '">'
                                                                . $service['name'] . '</option>';
                                                }
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="select-provider" class="col-sm-3 control-label"><?php echo lang('provider'); ?> *</label>
                                <div class="col-sm-7">
                                    <select id="select-provider" class="required form-control"></select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="start-datetime" class="col-sm-3 control-label" ><?php echo lang('start_date_time'); ?></label>
                                <div class="col-sm-7">
                                    <input type="text" id="start-datetime" class="form-control" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="end-datetime" class="control-label col-sm-3" ><?php echo lang('end_date_time'); ?></label>
                                <div class="col-sm-7">
                                    <input type="text" id="end-datetime" class="form-control" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="appointment-notes" class="control-label col-sm-3" ><?php echo lang('notes'); ?></label>
                                <div class="col-sm-7">
                                    <textarea id="appointment-notes" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="container">
                            <legend>
                                <?php echo lang('customer_details_title'); ?>
                                <button id="new-customer" class="btn btn-default btn-xs"
                                        title="<?php echo lang('clear_fields_add_existing_customer_hint'); ?>"
                                        type="button"><?php echo lang('new'); ?>
                                </button>
                                <button id="select-customer" class="btn btn-primary btn-xs"
                                        title="<?php echo lang('pick_existing_customer_hint'); ?>"
                                        type="button"><?php echo lang('select'); ?>
                                </button>
                                <input type="text" id="filter-existing-customers"
                                       placeholder="<?php echo lang('type_to_filter_customers'); ?>"
                                       style="display: none;" class="input-sm"/>
                                <div id="existing-customers-list" style="display: none;"></div>
                            </legend>

                            <input id="customer-id" type="hidden" />
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="first-name" class="control-label col-sm-2">
                                            <?php echo lang('first_name'); ?> *</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="first-name" class="required form-control" />
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="last-name" class="control-label col-sm-2">
                                            <?php echo lang('last_name'); ?>*</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="last-name" class="required form-control" />
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="email" class="control-label col-sm-2">
                                            <?php echo lang('email'); ?>*</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="email" class="required form-control" />
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="phone-number" class="control-label col-sm-3">
                                            <?php echo lang('phone_number'); ?>*</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="phone-number" class="required form-control" />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="address" class="control-label col-sm-3">
                                            <?php echo lang('address'); ?></label>
                                        <div class="col-sm-8">
                                            <input type="text" id="address" class="form-control" />
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="city" class="control-label col-sm-3">
                                            <?php echo lang('city'); ?></label>
                                        <div class="col-sm-8">
                                            <input type="text" id="city" class="form-control" />
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="zip-code" class="control-label col-sm-3">
                                            <?php echo lang('zip_code'); ?></label>
                                        <div class="col-sm-8">
                                            <input type="text" id="zip-code" class="form-control" />
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="customer-notes" class="control-label col-sm-3">
                                            <?php echo lang('notes'); ?></label>
                                        <div class="col-sm-8">
                                            <textarea id="customer-notes" rows="3" class="form-control"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>

                <div class="modal-push"></div>
            </div>

            <div class="modal-footer footer">
                <button id="save-appointment" class="btn btn-primary">
                    <?php echo lang('save'); ?>
                </button>
                <button id="cancel-appointment" class="btn btn-default" data-dismiss="modal">
                    <?php echo lang('cancel'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MANAGE UNAVAILABLE MODAL -->

<div id="manage-unavailable" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">&times;</button>
                <h3 class="modal-title"><?php echo lang('new_unavailable_title'); ?></h3>
            </div>

            <div class="modal-body">
                <div class="modal-message alert hidden"></div>

                <form class="form-horizontal">
                    <fieldset>
                        <input id="unavailable-id" type="hidden" />
                        
                        <div class="form-group">
                            <label for="unavailable-provider" class="control-label col-sm-3">
                                <?php echo lang('provider'); ?>
                            </label>
                            <div class="col-sm-8">
                                <select type="text" id="unavailable-provider" class="form-control"></select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="unavailable-start" class="control-label col-sm-3">
                                <?php echo lang('start'); ?>
                            </label>
                            <div class="col-sm-8">
                                <input type="text" id="unavailable-start" class="form-control" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="unavailable-end" class="control-label col-sm-3">
                                <?php echo lang('end'); ?>
                            </label>
                            <div class="col-sm-8">
                                <input type="text" id="unavailable-end" class="form-control" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="unavailable-notes" class="control-label col-sm-3">
                                <?php echo lang('notes'); ?>
                            </label>
                            <div class="col-sm-8">
                                <textarea id="unavailable-notes" rows="3" class="form-control"></textarea>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>

            <div class="modal-footer">
                <button id="save-unavailable" class="btn btn-primary">
                    <?php echo lang('save'); ?>
                </button>
                <button id="cancel-unavailable" class="btn btn-default" data-dismiss="modal">
                    <?php echo lang('cancel'); ?>
                </button>
            </div>

        </div>
    </div>
</div>

<!-- SELECT GOOGLE CALENDAR MODAL -->

<div id="select-google-calendar" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">&times;</button>
                <h3 class="modal-title"><?php echo lang('select_google_calendar'); ?></h3>
            </div>

            <div class="modal-body">
                <p>
                    <?php echo lang('select_google_calendar_prompt'); ?>
                </p>
                <select id="google-calendar"></select>
            </div>

            <div class="modal-footer">
                <button id="select-calendar" class="btn btn-primary">
                    <?php echo lang('select'); ?>
                </button>
                <button id="close-calendar" class="btn btn-default" data-dismiss="modal">
                    <?php echo lang('close'); ?>
                </button>
            </div>

        </div>
    </div>
</div>
