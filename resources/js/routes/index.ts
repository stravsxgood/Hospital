import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    type RouteFormDefinition,
    applyUrlDefaults,
    validateParameters,
} from './../wayfinder';
/**
 * @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
 * @route '/login'
 */
export const login = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(options),
    method: 'get',
});

login.definition = {
    methods: ['get', 'head'],
    url: '/login',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
 * @route '/login'
 */
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options);
};

/**
 * @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
 * @route '/login'
 */
login.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(options),
    method: 'get',
});
/**
 * @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
 * @route '/login'
 */
login.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: login.url(options),
    method: 'head',
});

/**
 * @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
 * @route '/login'
 */
const loginForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: login.url(options),
    method: 'get',
});

/**
 * @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
 * @route '/login'
 */
loginForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: login.url(options),
    method: 'get',
});
/**
 * @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
 * @route '/login'
 */
loginForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: login.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

login.form = loginForm;
/**
 * @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
 * @route '/logout'
 */
export const logout = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
});

logout.definition = {
    methods: ['post'],
    url: '/logout',
} satisfies RouteDefinition<['post']>;

/**
 * @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
 * @route '/logout'
 */
logout.url = (options?: RouteQueryOptions) => {
    return logout.definition.url + queryParams(options);
};

/**
 * @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
 * @route '/logout'
 */
logout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
});

/**
 * @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
 * @route '/logout'
 */
const logoutForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: logout.url(options),
    method: 'post',
});

/**
 * @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
 * @route '/logout'
 */
logoutForm.post = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: logout.url(options),
    method: 'post',
});

logout.form = logoutForm;
/**
 * @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
 * @route '/register'
 */
export const register = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: register.url(options),
    method: 'get',
});

register.definition = {
    methods: ['get', 'head'],
    url: '/register',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
 * @route '/register'
 */
register.url = (options?: RouteQueryOptions) => {
    return register.definition.url + queryParams(options);
};

/**
 * @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
 * @route '/register'
 */
register.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: register.url(options),
    method: 'get',
});
/**
 * @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
 * @route '/register'
 */
register.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: register.url(options),
    method: 'head',
});

/**
 * @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
 * @route '/register'
 */
const registerForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: register.url(options),
    method: 'get',
});

/**
 * @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
 * @route '/register'
 */
registerForm.get = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: register.url(options),
    method: 'get',
});
/**
 * @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
 * @route '/register'
 */
registerForm.head = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: register.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

register.form = registerForm;
/**
 * @see routes/web.php:39
 * @route '/'
 */
export const home = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: home.url(options),
    method: 'get',
});

home.definition = {
    methods: ['get', 'head'],
    url: '/',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see routes/web.php:39
 * @route '/'
 */
home.url = (options?: RouteQueryOptions) => {
    return home.definition.url + queryParams(options);
};

/**
 * @see routes/web.php:39
 * @route '/'
 */
home.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: home.url(options),
    method: 'get',
});
/**
 * @see routes/web.php:39
 * @route '/'
 */
home.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: home.url(options),
    method: 'head',
});

/**
 * @see routes/web.php:39
 * @route '/'
 */
const homeForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: home.url(options),
    method: 'get',
});

/**
 * @see routes/web.php:39
 * @route '/'
 */
homeForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: home.url(options),
    method: 'get',
});
/**
 * @see routes/web.php:39
 * @route '/'
 */
homeForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: home.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

home.form = homeForm;
/**
 * @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/schedule-guest'
 */
export const scheduleGuest = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: scheduleGuest.url(options),
    method: 'get',
});

scheduleGuest.definition = {
    methods: ['get', 'head'],
    url: '/schedule-guest',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/schedule-guest'
 */
scheduleGuest.url = (options?: RouteQueryOptions) => {
    return scheduleGuest.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/schedule-guest'
 */
scheduleGuest.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: scheduleGuest.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/schedule-guest'
 */
scheduleGuest.head = (
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: scheduleGuest.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/schedule-guest'
 */
const scheduleGuestForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: scheduleGuest.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/schedule-guest'
 */
scheduleGuestForm.get = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: scheduleGuest.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/schedule-guest'
 */
scheduleGuestForm.head = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: scheduleGuest.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

scheduleGuest.form = scheduleGuestForm;
/**
 * @see \App\Http\Controllers\AppointmentController::my
 * @see app/Http/Controllers/AppointmentController.php:19
 * @route '/my-appointments'
 */
export const my = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: my.url(options),
    method: 'get',
});

my.definition = {
    methods: ['get', 'head'],
    url: '/my-appointments',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\AppointmentController::my
 * @see app/Http/Controllers/AppointmentController.php:19
 * @route '/my-appointments'
 */
my.url = (options?: RouteQueryOptions) => {
    return my.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\AppointmentController::my
 * @see app/Http/Controllers/AppointmentController.php:19
 * @route '/my-appointments'
 */
my.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: my.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\AppointmentController::my
 * @see app/Http/Controllers/AppointmentController.php:19
 * @route '/my-appointments'
 */
my.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: my.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\AppointmentController::my
 * @see app/Http/Controllers/AppointmentController.php:19
 * @route '/my-appointments'
 */
const myForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: my.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\AppointmentController::my
 * @see app/Http/Controllers/AppointmentController.php:19
 * @route '/my-appointments'
 */
myForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: my.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\AppointmentController::my
 * @see app/Http/Controllers/AppointmentController.php:19
 * @route '/my-appointments'
 */
myForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: my.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

my.form = myForm;
/**
 * @see \App\Http\Controllers\StaffDashboardController::staff
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff'
 */
export const staff = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: staff.url(options),
    method: 'get',
});

staff.definition = {
    methods: ['get', 'head'],
    url: '/staff',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\StaffDashboardController::staff
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff'
 */
staff.url = (options?: RouteQueryOptions) => {
    return staff.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\StaffDashboardController::staff
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff'
 */
staff.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: staff.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\StaffDashboardController::staff
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff'
 */
staff.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: staff.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\StaffDashboardController::staff
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff'
 */
const staffForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: staff.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\StaffDashboardController::staff
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff'
 */
staffForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: staff.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\StaffDashboardController::staff
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff'
 */
staffForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: staff.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

staff.form = staffForm;
/**
 * @see \App\Http\Controllers\DashboardController::__invoke
 * @see app/Http/Controllers/DashboardController.php:12
 * @param current_team - Default: '$currentTeam'
 * @route '/{current_team?}/dashboard'
 */
export const dashboard = (
    args?:
        | { current_team?: string | number }
        | [current_team: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: dashboard.url(args, options),
    method: 'get',
});

dashboard.definition = {
    methods: ['get', 'head'],
    url: '/{current_team?}/dashboard',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\DashboardController::__invoke
 * @see app/Http/Controllers/DashboardController.php:12
 * @param current_team - Default: '$currentTeam'
 * @route '/{current_team?}/dashboard'
 */
dashboard.url = (
    args?:
        | { current_team?: string | number }
        | [current_team: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { current_team: args };
    }

    if (Array.isArray(args)) {
        args = {
            current_team: args[0],
        };
    }

    args = applyUrlDefaults(args);

    validateParameters(args, ['current_team']);

    const parsedArgs = {
        current_team: args?.current_team ?? '$currentTeam',
    };

    return (
        dashboard.definition.url
            .replace(
                '{current_team?}',
                parsedArgs.current_team?.toString() ?? '',
            )
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\DashboardController::__invoke
 * @see app/Http/Controllers/DashboardController.php:12
 * @param current_team - Default: '$currentTeam'
 * @route '/{current_team?}/dashboard'
 */
dashboard.get = (
    args?:
        | { current_team?: string | number }
        | [current_team: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: dashboard.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\DashboardController::__invoke
 * @see app/Http/Controllers/DashboardController.php:12
 * @param current_team - Default: '$currentTeam'
 * @route '/{current_team?}/dashboard'
 */
dashboard.head = (
    args?:
        | { current_team?: string | number }
        | [current_team: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: dashboard.url(args, options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\DashboardController::__invoke
 * @see app/Http/Controllers/DashboardController.php:12
 * @param current_team - Default: '$currentTeam'
 * @route '/{current_team?}/dashboard'
 */
const dashboardForm = (
    args?:
        | { current_team?: string | number }
        | [current_team: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: dashboard.url(args, options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\DashboardController::__invoke
 * @see app/Http/Controllers/DashboardController.php:12
 * @param current_team - Default: '$currentTeam'
 * @route '/{current_team?}/dashboard'
 */
dashboardForm.get = (
    args?:
        | { current_team?: string | number }
        | [current_team: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: dashboard.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\DashboardController::__invoke
 * @see app/Http/Controllers/DashboardController.php:12
 * @param current_team - Default: '$currentTeam'
 * @route '/{current_team?}/dashboard'
 */
dashboardForm.head = (
    args?:
        | { current_team?: string | number }
        | [current_team: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: dashboard.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

dashboard.form = dashboardForm;
