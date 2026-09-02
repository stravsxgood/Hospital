import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    type RouteFormDefinition,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/schedule-guest'
 */
const DoctorSchedulePageControllerd1a082d0076e91371edd2dded6a0b496 = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: DoctorSchedulePageControllerd1a082d0076e91371edd2dded6a0b496.url(
        options,
    ),
    method: 'get',
});

DoctorSchedulePageControllerd1a082d0076e91371edd2dded6a0b496.definition = {
    methods: ['get', 'head'],
    url: '/schedule-guest',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/schedule-guest'
 */
DoctorSchedulePageControllerd1a082d0076e91371edd2dded6a0b496.url = (
    options?: RouteQueryOptions,
) => {
    return (
        DoctorSchedulePageControllerd1a082d0076e91371edd2dded6a0b496.definition
            .url + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/schedule-guest'
 */
DoctorSchedulePageControllerd1a082d0076e91371edd2dded6a0b496.get = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: DoctorSchedulePageControllerd1a082d0076e91371edd2dded6a0b496.url(
        options,
    ),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/schedule-guest'
 */
DoctorSchedulePageControllerd1a082d0076e91371edd2dded6a0b496.head = (
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: DoctorSchedulePageControllerd1a082d0076e91371edd2dded6a0b496.url(
        options,
    ),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/schedule-guest'
 */
const DoctorSchedulePageControllerd1a082d0076e91371edd2dded6a0b496Form = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: DoctorSchedulePageControllerd1a082d0076e91371edd2dded6a0b496.url(
        options,
    ),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/schedule-guest'
 */
DoctorSchedulePageControllerd1a082d0076e91371edd2dded6a0b496Form.get = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: DoctorSchedulePageControllerd1a082d0076e91371edd2dded6a0b496.url(
        options,
    ),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/schedule-guest'
 */
DoctorSchedulePageControllerd1a082d0076e91371edd2dded6a0b496Form.head = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: DoctorSchedulePageControllerd1a082d0076e91371edd2dded6a0b496.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

DoctorSchedulePageControllerd1a082d0076e91371edd2dded6a0b496.form =
    DoctorSchedulePageControllerd1a082d0076e91371edd2dded6a0b496Form;
/**
 * @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/doctor-schedules'
 */
const DoctorSchedulePageController453cfec436c13ad3b6b263cdb6dfe111 = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: DoctorSchedulePageController453cfec436c13ad3b6b263cdb6dfe111.url(
        options,
    ),
    method: 'get',
});

DoctorSchedulePageController453cfec436c13ad3b6b263cdb6dfe111.definition = {
    methods: ['get', 'head'],
    url: '/doctor-schedules',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/doctor-schedules'
 */
DoctorSchedulePageController453cfec436c13ad3b6b263cdb6dfe111.url = (
    options?: RouteQueryOptions,
) => {
    return (
        DoctorSchedulePageController453cfec436c13ad3b6b263cdb6dfe111.definition
            .url + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/doctor-schedules'
 */
DoctorSchedulePageController453cfec436c13ad3b6b263cdb6dfe111.get = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: DoctorSchedulePageController453cfec436c13ad3b6b263cdb6dfe111.url(
        options,
    ),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/doctor-schedules'
 */
DoctorSchedulePageController453cfec436c13ad3b6b263cdb6dfe111.head = (
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: DoctorSchedulePageController453cfec436c13ad3b6b263cdb6dfe111.url(
        options,
    ),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/doctor-schedules'
 */
const DoctorSchedulePageController453cfec436c13ad3b6b263cdb6dfe111Form = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: DoctorSchedulePageController453cfec436c13ad3b6b263cdb6dfe111.url(
        options,
    ),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/doctor-schedules'
 */
DoctorSchedulePageController453cfec436c13ad3b6b263cdb6dfe111Form.get = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: DoctorSchedulePageController453cfec436c13ad3b6b263cdb6dfe111.url(
        options,
    ),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/doctor-schedules'
 */
DoctorSchedulePageController453cfec436c13ad3b6b263cdb6dfe111Form.head = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: DoctorSchedulePageController453cfec436c13ad3b6b263cdb6dfe111.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

DoctorSchedulePageController453cfec436c13ad3b6b263cdb6dfe111.form =
    DoctorSchedulePageController453cfec436c13ad3b6b263cdb6dfe111Form;

/**
 * Multiple routes resolve to \App\Http\Controllers\DoctorSchedulePageController::DoctorSchedulePageController, so this export is a
 * dictionary keyed by URI rather than a callable. Call a specific route with `DoctorSchedulePageController['<uri>'](...)`,
 * or import the route by name from your generated `routes/` directory.
 */
const DoctorSchedulePageController = {
    '/schedule-guest':
        DoctorSchedulePageControllerd1a082d0076e91371edd2dded6a0b496,
    '/doctor-schedules':
        DoctorSchedulePageController453cfec436c13ad3b6b263cdb6dfe111,
};

export default DoctorSchedulePageController;
